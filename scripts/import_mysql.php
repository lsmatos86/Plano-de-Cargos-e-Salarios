<?php
/**
 * Importa dump MySQL para PostgreSQL.
 * Uso: php scripts/import_mysql.php
 */
set_time_limit(600);
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

// ── Configuração ──────────────────────────────────────────────────────────────
$SQL_FILE = __DIR__ . '/../attached_assets/azukicom_kopplaita_(9)_1783795541328.sql';

// Tabelas que existem no PostgreSQL (em ordem respeitando FKs)
$PG_TABLES = [
    'roles', 'permissions', 'usuarios',
    'user_roles', 'role_permissions',
    'familia_cbo', 'cbos',
    'tipo_hierarquia', 'nivel_hierarquico',
    'escolaridades', 'faixas_salariais',
    'areas_atuacao',
    'caracteristicas',
    'habilidades',
    'cursos',
    'recursos', 'recursos_grupos',
    'riscos',
    'cargos',
    'cargos_area', 'cargo_sinonimos',
    'cursos_cargo', 'habilidades_cargo',
    'recursos_cargo', 'recursos_grupos_cargo', 'recurso_grupo_recurso',
    'riscos_cargo',
    'caracteristicas_cargo',
    'audit_log',
];

// Tabelas do MySQL que não existem no PostgreSQL → ignorar
$SKIP_TABLES = [
    'campanhas_pesquisa', 'empresas_mercado', 'historico_inpc',
    'historico_salario_minimo', 'pesquisa_valores', 'reajustes_salariais',
    'cargos_supervisores',
];

// ── Funções auxiliares ────────────────────────────────────────────────────────

function convertInsert(string $sql): string
{
    // Substitui backticks por aspas duplas em identificadores
    $sql = preg_replace('/`([^`]+)`/', '"$1"', $sql);
    // Remove comentário MySQL inline
    $sql = preg_replace('/\/\*![0-9]+.*?\*\//', '', $sql);

    // Corrige escape MySQL (\') para PostgreSQL ('')
    // Dentro de strings delimitadas por aspas simples, \' → ''
    $result = '';
    $i = 0; $len = strlen($sql);
    $inStr = false;
    while ($i < $len) {
        $c = $sql[$i];
        if (!$inStr && $c === "'") { $inStr = true; $result .= $c; $i++; continue; }
        if ($inStr) {
            if ($c === '\\' && $i + 1 < $len && $sql[$i+1] === "'") {
                $result .= "''"; $i += 2; continue;
            }
            if ($c === "'") { $inStr = false; }
        }
        $result .= $c; $i++;
    }
    $sql = $result;

    // Mapeia coluna com acento do MySQL → nome sem acento no PostgreSQL
    $sql = str_replace('"característicaCargoId"', '"caracteristicaCargoId"', $sql);

    return $sql;
}

function resetSequences(PDO $pdo, array $tables): void
{
    // Mapeamento tabela → coluna PK → nome da sequence
    $pks = [
        'roles'                  => ['col' => 'roleId'],
        'permissions'            => ['col' => 'permissionId'],
        'usuarios'               => ['col' => 'usuarioId'],
        'familia_cbo'            => ['col' => 'familiaCboId'],
        'cbos'                   => ['col' => 'cboId'],
        'tipo_hierarquia'        => ['col' => 'tipoId'],
        'nivel_hierarquico'      => ['col' => 'nivelId'],
        'escolaridades'          => ['col' => 'escolaridadeId'],
        'faixas_salariais'       => ['col' => 'faixaId'],
        'areas_atuacao'          => ['col' => 'areaId'],
        'caracteristicas'        => ['col' => 'caracteristicaId'],
        'habilidades'            => ['col' => 'habilidadeId'],
        'cursos'                 => ['col' => 'cursoId'],
        'recursos'               => ['col' => 'recursoId'],
        'recursos_grupos'        => ['col' => 'recursoGrupoId'],
        'riscos'                 => ['col' => 'riscoId'],
        'cargos'                 => ['col' => 'cargoId'],
        'cargos_area'            => ['col' => 'cargoAreaId'],
        'cargo_sinonimos'        => ['col' => 'cargoSinonimoId'],
        'cursos_cargo'           => ['col' => 'cursoCargoId'],
        'habilidades_cargo'      => ['col' => 'habilidadeCargoId'],
        'recursos_cargo'         => ['col' => 'recursoCargoId'],
        'recursos_grupos_cargo'  => ['col' => 'recursoGrupoCargoId'],
        'recurso_grupo_recurso'  => ['col' => 'recursoGrupoRecursoId'],
        'riscos_cargo'           => ['col' => 'riscoCargoId'],
        'caracteristicas_cargo'  => ['col' => 'caracteristicaCargoId'],
        'audit_log'              => ['col' => 'logId'],
    ];

    foreach ($tables as $table) {
        if (!isset($pks[$table])) continue;
        $col = $pks[$table]['col'];
        try {
            $pdo->exec("SELECT setval(
                pg_get_serial_sequence('\"$table\"', '$col'),
                COALESCE((SELECT MAX(\"$col\") FROM \"$table\"), 1)
            )");
            echo "  ↺ Sequence de $table resetada.\n";
        } catch (\Exception $e) {
            echo "  ⚠ Sequence de $table: " . $e->getMessage() . "\n";
        }
    }
}

// ── Início ────────────────────────────────────────────────────────────────────

if (!file_exists($SQL_FILE)) {
    die("ERRO: Arquivo SQL não encontrado: $SQL_FILE\n");
}

echo "Lendo arquivo SQL...\n";
$content = file_get_contents($SQL_FILE);
echo "Arquivo lido: " . round(strlen($content) / 1024) . " KB\n\n";

$pdo = Database::getConnection();

// Desabilita verificação de FK temporariamente
$pdo->exec("SET session_replication_role = 'replica'");

// ── Passo 1: Truncar tabelas em ordem reversa ─────────────────────────────────
echo "Truncando tabelas existentes...\n";
$pdo->beginTransaction();
try {
    foreach (array_reverse($PG_TABLES) as $table) {
        $pdo->exec("TRUNCATE TABLE \"$table\" RESTART IDENTITY CASCADE");
        echo "  ✓ TRUNCATE $table\n";
    }
    $pdo->commit();
    echo "\n";
} catch (\Exception $e) {
    $pdo->rollBack();
    die("ERRO ao truncar: " . $e->getMessage() . "\n");
}

// ── Passo 2: Parsear INSERT statements do dump ────────────────────────────────
echo "Parseando INSERT statements...\n";

// Extrai INSERT completos (podem ser multi-linha terminando em ;)
$insertsByTable = [];

// Processo linha por linha acumulando statements
$lines = explode("\n", $content);
$currentStmt  = '';
$currentTable = '';
$inInsert     = false;

foreach ($lines as $line) {
    $trimmed = rtrim($line);

    // Detecta início de INSERT
    if (preg_match('/^INSERT INTO `([^`]+)`/i', $trimmed, $m)) {
        $table = $m[1];
        if (in_array($table, $SKIP_TABLES)) {
            // Ignora tabelas não mapeadas
            $inInsert    = false;
            $currentStmt = '';
            continue;
        }
        $currentTable = $table;
        $currentStmt  = $trimmed;
        $inInsert     = true;

        // Se termina com ; na mesma linha
        if (str_ends_with(rtrim($trimmed, ';'), ')') && substr_count($trimmed, ';') > 0) {
            $insertsByTable[$currentTable][] = $currentStmt;
            $currentStmt  = '';
            $inInsert     = false;
        }
        continue;
    }

    if ($inInsert) {
        $currentStmt .= "\n" . $trimmed;
        // Fim do statement
        if (str_ends_with(rtrim($trimmed), ';') || $trimmed === ';') {
            $insertsByTable[$currentTable][] = $currentStmt;
            $currentStmt  = '';
            $inInsert     = false;
        }
    }
}

$totalStmts = array_sum(array_map('count', $insertsByTable));
echo "Encontrados $totalStmts INSERT statements em " . count($insertsByTable) . " tabelas.\n\n";

// ── Passo 3: Importar em ordem de FK ─────────────────────────────────────────
$totalOk   = 0;
$totalErro = 0;

foreach ($PG_TABLES as $table) {
    if (!isset($insertsByTable[$table])) {
        continue;
    }

    $stmts = $insertsByTable[$table];
    echo "Importando $table (" . count($stmts) . " stmt)... ";
    $ok = 0;
    $err = 0;

    $pdo->beginTransaction();
    try {
        foreach ($stmts as $rawSql) {
            $pgSql = convertInsert($rawSql);
            // Remove ponto-e-vírgula final (PDO não precisa)
            $pgSql = rtrim(trim($pgSql), ';');

            try {
                $pdo->exec($pgSql);
                $ok++;
            } catch (\PDOException $e) {
                // Tenta linha por linha se o batch falhou
                $err++;
                error_log("Erro em $table: " . $e->getMessage() . "\nSQL: " . substr($pgSql, 0, 200));
            }
        }
        $pdo->commit();
    } catch (\Exception $e) {
        $pdo->rollBack();
        echo "ROLLBACK: " . $e->getMessage() . "\n";
        $err++;
    }

    echo "OK=$ok ERR=$err\n";
    $totalOk   += $ok;
    $totalErro += $err;
}

// ── Passo 4: Reabilitar FK e resetar sequences ────────────────────────────────
$pdo->exec("SET session_replication_role = 'origin'");

echo "\nResetando sequences...\n";
resetSequences($pdo, $PG_TABLES);

// ── Resumo ────────────────────────────────────────────────────────────────────
echo "\n══════════════════════════════════\n";
echo "IMPORTAÇÃO CONCLUÍDA\n";
echo "Statements OK  : $totalOk\n";
echo "Statements ERR : $totalErro\n";
echo "══════════════════════════════════\n";

if ($totalErro > 0) {
    echo "⚠ Verifique o error_log do PHP para detalhes dos erros.\n";
}
