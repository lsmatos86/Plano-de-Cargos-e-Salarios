<?php
// Arquivo: includes/functions.php
// Este arquivo DEVE ser incluído APÓS o config.php e o vendor/autoload.php

// Importa a classe de Database para ser usada na autenticação
use App\Core\Database;

// Lista de tabelas mestras permitidas para operações genéricas de leitura/exclusão.
// Usado para evitar injeção SQL no nome da tabela.
// (Mantido para os módulos que AINDA NÃO foram refatorados)
const ALLOWED_TABLES = [
    'cargos', 'escolaridades', 'cbos', 'caracteristicas', 'cursos', 'riscos', 
    'habilidades', 'usuarios', 'familia_cbo', 'recursos', 'recursos_grupos',
    'faixas_salariais',             
    'areas_atuacao',                
    'cargos_area',                  
    'tipo_hierarquia',              
    'nivel_hierarquico',            
    'habilidades_cargo', 'caracteristicas_cargo', 'riscos_cargo', 'cursos_cargo', 
    'cargo_sinonimos', 'recursos_grupos_cargo', 'recurso_grupo_recurso'
];

/**
 * Valida se um nome de tabela é seguro.
 * (Mantido, pois é usado pelos Repositórios e funções genéricas)
 */
function isValidTableName(string $tableName): bool {
    return in_array(strtolower($tableName), ALLOWED_TABLES);
}

// ----------------------------------------------------
// 1. FUNÇÃO: CONEXÃO COM O BANCO DE DADOS (PDO)
// ----------------------------------------------------
/**
 * REMOVIDO: getDbConnection()
 * Esta função foi substituída pela classe App\Core\Database
 * e seu método estático ::getConnection()
 */
// function getDbConnection(): PDO { ... }


// ----------------------------------------------------
// 2. FUNÇÃO: INSERIR REGISTRO SIMPLES (CREATE) - Mantida
// ----------------------------------------------------
/**
 * (Mantido para os módulos simples como Escolaridade, Cursos, etc.)
 */
function insertSimpleRecord(PDO $pdo, string $tableName, string $columnName, string $value): bool
{
    if (!isValidTableName($tableName) || !preg_match('/^[a-zA-Z0-9_]+$/', $columnName)) {
        error_log("Tentativa de insert em tabela ou coluna inválida: {$tableName}.{$columnName}");
        return false;
    }
    
    $sql = "INSERT INTO {$tableName} ({$columnName}) VALUES (?)";
    
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$value]);
    } catch (PDOException $e) {
        error_log("Erro de INSERT em {$tableName}: " . $e->getMessage());
        return false;
    }
}

// ----------------------------------------------------
// 3. FUNÇÃO: LISTAR REGISTROS (READ) - Mantida
// ----------------------------------------------------
/**
 * (Mantido para os módulos simples)
 */
function getRecords(PDO $pdo, string $tableName, string $idColumn, string $nameColumn, array $params): array
{
    if (!isValidTableName($tableName)) {
        error_log("Tentativa de SELECT em tabela inválida: {$tableName}");
        return [];
    }

    $term = $params['term'] ?? '';
    $orderBy = $params['order_by'] ?? $idColumn;
    $sortDir = $params['sort_dir'] ?? 'ASC';
    $limit = (int)($params['limit'] ?? 0);
    $offset = (int)($params['offset'] ?? 0);

    $validDirs = ['ASC', 'DESC'];
    $sortDir = in_array(strtoupper($sortDir), $validDirs) ? strtoupper($sortDir) : 'ASC';
    
    $validColumns = [$idColumn, $nameColumn, $idColumn.'DataCadastro', $idColumn.'DataAtualizacao', 'recursoDescricao', 'nome', 'email', 'ativo'];
    
    if (!in_array($orderBy, $validColumns)) {
        $orderBy = $idColumn;
    }
    
    $sql = "SELECT * FROM {$tableName}";
    $bindings = [];
    
    if (!empty($term)) {
        $sql .= " WHERE {$nameColumn} LIKE ?";
        if ($tableName === 'usuarios') {
            $sql .= " OR email LIKE ?";
            $bindings[] = "%{$term}%";
        }
        $bindings[] = "%{$term}%";
    }
    
    $sql .= " ORDER BY {$orderBy} {$sortDir}";

    if ($limit > 0) {
        $sql .= " LIMIT {$limit} OFFSET {$offset}";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erro na query de {$tableName}: " . $e->getMessage() . " SQL: " . $sql);
        return [];
    }
}

/**
 * (Mantido para os módulos simples)
 */
function countRecordsWithFilter(PDO $pdo, string $tableName, string $nameColumn, string $term = ''): int {
    if (!isValidTableName($tableName)) {
        return 0;
    }
    
    $sql = "SELECT COUNT(*) FROM {$tableName}";
    $bindings = [];
    
    if (!empty($term)) {
        $sql .= " WHERE {$nameColumn} LIKE ?";
        if ($tableName === 'usuarios') {
            $sql .= " OR email LIKE ?";
            $bindings[] = "%{$term}%";
        }
        $bindings[] = "%{$term}%";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}


// ----------------------------------------------------
// 4. FUNÇÃO: EXCLUIR REGISTRO (DELETE) - Mantida
// ----------------------------------------------------
/**
 * (Mantido para os módulos simples)
 */
function deleteRecord(PDO $pdo, string $tableName, string $idColumn, $id): int
{
    if (!isValidTableName($tableName) || !preg_match('/^[a-zA-Z0-9_]+$/', $idColumn)) {
        error_log("Tentativa de DELETE em tabela ou coluna inválida: {$tableName}.{$idColumn}");
        return 0;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM {$tableName} WHERE {$idColumn} = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        return 0; 
    }
}

// ----------------------------------------------------
// (REMOVIDO: clearCargoRelationships)
// - Lógica movida para CargoRepository->clearRelationships()
// ----------------------------------------------------
// function clearCargoRelationships(PDO $pdo, int $cargoId): bool { ... }


// ----------------------------------------------------
// 5. FUNÇÃO: OBTER DADOS PARA LOOKUP/SELECTS
// ----------------------------------------------------
/**
 * REMOVIDO: getLookupData()
 * Esta função foi substituída pela classe App\Repository\LookupRepository
 * e seu método ->getLookup()
 */
// function getLookupData(PDO $pdo, string $tableName, ...): array { ... }


// ----------------------------------------------------
// 6. FUNÇÃO: OBTER OPÇÕES ENUM - Mantida
// ----------------------------------------------------
/**
 * (Mantido para módulos como 'riscos' e 'habilidades' forms)
 */
function getEnumOptions(PDO $pdo, string $tableName, string $columnName): array
{
    if (!isValidTableName($tableName)) {
        return [];
    }

    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM {$tableName} LIKE '{$columnName}'");
        $row = $stmt->fetch();

        if (isset($row['Type']) && strpos($row['Type'], 'enum') !== false) {
            $enum_string = $row['Type'];
            $matches = [];
            preg_match_all('/\'([^\']+)\'/', $enum_string, $matches);
            return $matches[1];
        }
    } catch (PDOException $e) {
        // Falha silenciosa
    }
    return [];
}


// ----------------------------------------------------
// 7. FUNÇÕES DE AUTENTICAÇÃO E SESSÃO (MODIFICADA)
// ----------------------------------------------------
/**
 * (Mantida)
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * (MODIFICADA: Agora usa App\Core\Database)
 */
function authenticateUser($email, $password) {
    startSession();

    try {
        // MODIFICADO: Usa a nova classe de Database
        $pdo = Database::getConnection(); 
        
        $stmt = $pdo->prepare("SELECT usuarioId, nome, email, senha, ativo FROM usuarios WHERE email = ? AND ativo = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }

    if ($user && password_verify($password, $user['senha'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['usuarioId'];
        $_SESSION['username'] = $user['nome']; 
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}

/**
 * (Mantida)
 */
function isUserLoggedIn() {
    startSession();
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// ----------------------------------------------------
// 8. FUNÇÃO AUXILIAR DE ORDENAÇÃO - Mantida
// ----------------------------------------------------
/**
 * (Mantida para as views)
 */
function getSortDirection($current_order, $column, $default_dir = 'ASC') {
    if ($current_order === $column) {
        return ($_GET['sort_dir'] ?? $default_dir) === 'ASC' ? 'DESC' : 'ASC';
    }
    return $default_dir;
}

// ----------------------------------------------------
// 9. FUNÇÃO PARA CARREGAR DADOS COMPLETOS DO CARGO (RELATÓRIOS)
// ----------------------------------------------------
/**
 * REMOVIDO: getCargoReportData()
 * Esta função foi substituída pela classe App\Repository\CargoRepository
 * e seu método ->findReportData()
 */
// function getCargoReportData(PDO $pdo, int $cargoId): ?array { ... }


/**
 * (Mantida, é um helper de visualização para relatórios)
 */
function getRiscoIcon(string $riscoNome): string {
    $map = [
        'Físico' => '<i class="fas fa-sun" style="color:#f90;"></i>',
        'Químico' => '<i class="fas fa-flask" style="color:#09f;"></i>',
        'Ergonômico' => '<i class="fas fa-chair" style="color:#888;"></i>',
        'Psicossocial' => '<i class="fas fa-brain" style="color:#e66;"></i>',
        'Acidental' => '<i class="fas fa-exclamation-triangle" style="color:#f00;"></i>',
        'Biológico' => '<i class="fas fa-biohazard" style="color:#228B22;"></i>' 
    ];
    return $map[$riscoNome] ?? '<i class="fas fa-dot-circle" style="color:#999;"></i>';
}

// ----------------------------------------------------
// 10. FUNÇÕES AUXILIARES PARA HIERARQUIA DE ÁREAS
// ----------------------------------------------------

/**
 * REMOVIDO: getAreaHierarchyLookup()
 * Esta função foi substituída pela classe App\Repository\AreaRepository
 * e seu método ->getHierarchyLookup()
 */
// function getAreaHierarchyLookup(PDO $pdo): array { ... }

/**
 * (Mantida para o módulo 'areas_atuacao')
 */
function buildAreaHierarchy(array $flatList, $parentId = null): array 
{
    $branch = [];
    foreach ($flatList as $area) {
        $areaPaiId = ($area['areaPaiId'] === null) ? null : (int)$area['areaPaiId']; 
        
        if ($areaPaiId === $parentId) {
            $children = buildAreaHierarchy($flatList, (int)$area['areaId']);
            if ($children) {
                $area['children'] = $children;
            }
            $branch[] = $area;
        }
    }
    return $branch;
}

// ----------------------------------------------------
// 11. FUNÇÃO AUXILIAR PARA AGRUPAR HABILIDADES
// ----------------------------------------------------

/**
 * REMOVIDO: getHabilidadesGrouped()
 * Esta função foi substituída pela classe App\Repository\HabilidadeRepository
 * e seu método ->getGroupedLookup()
 */
// function getHabilidadesGrouped(PDO $pdo): array { ... }

?>