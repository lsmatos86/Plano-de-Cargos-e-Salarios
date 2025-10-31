<?php
// Arquivo: includes/functions.php
// Este arquivo DEVE ser incluído APÓS o config.php e o vendor/autoload.php

// Importa a classe de Database para ser usada na autenticação
use App\Core\Database;

// Lista de tabelas mestras permitidas.
// Mantida pois os Repositórios (LookupRepository, etc.) ainda a utilizam para validação.
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
 * (Mantido, pois é usado pelos Repositórios)
 */
function isValidTableName(string $tableName): bool {
    return in_array(strtolower($tableName), ALLOWED_TABLES);
}

// ----------------------------------------------------
// FUNÇÕES DE BANCO DE DADOS REMOVIDAS
// ----------------------------------------------------
//
// REMOVIDO: getDbConnection() (Substituído por App\Core\Database::getConnection())
// REMOVIDO: insertSimpleRecord() (Substituído pelos métodos save() dos Repositórios)
// REMOVIDO: getRecords() (Substituído pelos métodos findAllPaginated() dos Repositórios)
// REMOVIDO: countRecordsWithFilter() (Substituído pelos métodos findAllPaginated() dos Repositórios)
// REMOVIDO: deleteRecord() (Substituído pelos métodos delete() dos Repositórios)
// REMOVIDO: clearCargoRelationships() (Movido para CargoRepository)
// REMOVIDO: getLookupData() (Movido para LookupRepository)
// REMOVIDO: getEnumOptions() (Movido para os Repositórios específicos, ex: RiscoRepository)
// REMOVIDO: getCargoReportData() (Movido para CargoRepository)
// REMOVIDO: getAreaHierarchyLookup() (Movido para AreaRepository)
// REMOVIDO: buildAreaHierarchy() (Movido para AreaRepository)
// REMOVIDO: getHabilidadesGrouped() (Movido para HabilidadeRepository)
//
// ----------------------------------------------------


// ----------------------------------------------------
// 7. FUNÇÕES DE AUTENTICAÇÃO E SESSÃO (MANTIDAS)
// ----------------------------------------------------
/**
 * Inicia a sessão se ainda não estiver ativa.
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Autentica o usuário usando email e verifica o hash da senha no banco de dados.
 * (MODIFICADA para usar App\Core\Database)
 *
 * @param string $email O email/username fornecido.
 * @param string $password A senha bruta fornecida.
 * @return bool True se o login for bem-sucedido.
 */
function authenticateUser($email, $password) {
    startSession();

    try {
        // Usa a nova classe de Database
        $pdo = Database::getConnection(); 
        
        $stmt = $pdo->prepare("SELECT usuarioId, nome, email, senha, ativo FROM usuarios WHERE email = ? AND ativo = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }

    // Verifica se o usuário existe e se a senha corresponde ao hash armazenado
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
 * Verifica se o usuário está logado.
 * @return bool
 */
function isUserLoggedIn() {
    startSession();
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// ----------------------------------------------------
// 8. FUNÇÃO AUXILIAR DE ORDENAÇÃO (MANTIDA)
// ----------------------------------------------------
/**
 * Alterna a direção da ordenação para um link de coluna na Datagrid.
 */
function getSortDirection($current_order, $column, $default_dir = 'ASC') {
    if ($current_order === $column) {
        // Pega o sort_dir atual da URL (ou o padrão) e inverte
        return ($_GET['sort_dir'] ?? $default_dir) === 'ASC' ? 'DESC' : 'ASC';
    }
    return $default_dir;
}

// ----------------------------------------------------
// 9. FUNÇÃO AUXILIAR DE VISUALIZAÇÃO (MANTIDA)
// ----------------------------------------------------
/**
 * Mapeia o nome do risco para um ícone Font Awesome com estilo.
 * @param string $riscoNome
 * @return string HTML do ícone.
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