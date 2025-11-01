<?php
// Arquivo: includes/functions.php
// Este arquivo DEVE ser incluído APÓS o config.php e o vendor/autoload.php

// Importa as classes necessárias
use App\Core\Database;
use App\Service\AuthService;
use App\Service\AuditService;

// --- 1. INICIALIZAÇÃO DA SESSÃO E SERVIÇOS GLOBAIS ---

// Inicia a sessão no início de todos os arquivos protegidos
startSession();

/** @var AuthService|null $authService */
$authService = null;
if (isUserLoggedIn()) { // Verifica se o usuário já está logado
    // Se estiver logado, cria a instância do serviço de autorização
    // para ser usada em toda a aplicação (ex: cargos_form.php)
    $authService = new AuthService();
}


// --- 2. CONSTANTES E VALIDAÇÕES (Mantidas) ---

// Lista de tabelas mestras permitidas.
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
 */
function isValidTableName(string $tableName): bool {
    return in_array(strtolower($tableName), ALLOWED_TABLES);
}

// ----------------------------------------------------
// FUNÇÕES DE BANCO DE DADOS REMOVIDAS (Como no original)
// ----------------------------------------------------
// ... (comentários de funções removidas) ...
// ----------------------------------------------------


// ----------------------------------------------------
// 7. FUNÇÕES DE AUTENTICAÇÃO E SESSÃO (AJUSTADAS)
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
 * Autentica o usuário e REGISTRA LOG de sucesso ou falha.
 *
 * @param string $email O email/username fornecido.
 * @param string $password A senha bruta fornecida.
 * @return bool True se o login for bem-sucedido.
 */
function authenticateUser($email, $password) {
    // A sessão já foi iniciada no topo do arquivo.
    $pdo = null;
    try {
        $pdo = Database::getConnection(); 
        $stmt = $pdo->prepare("SELECT usuarioId, nome, email, senha, ativo FROM usuarios WHERE email = ? AND ativo = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        // Erro de banco de dados
        return false;
    }

    // Instancia o serviço de auditoria para registrar a tentativa
    $auditService = new AuditService();

    // Verifica se o usuário existe e se a senha corresponde
    if ($user && password_verify($password, $user['senha'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['usuarioId'];
        $_SESSION['username'] = $user['nome']; 
        $_SESSION['user_email'] = $user['email'];
        
        // --- LOG DE AUDITORIA (SUCESSO) ---
        $auditService->log('LOGIN_SUCCESS', 'usuarios', $user['usuarioId'], ['email' => $email]);
        
        return true;
    }
    
    // --- LOG DE AUDITORIA (FALHA) ---
    // Registra a tentativa falha (usuário não encontrado ou senha errada)
    $auditService->log('LOGIN_FAIL', 'usuarios', null, ['email' => $email]);

    return false;
}

/**
 * Verifica se o usuário está logado.
 * @return bool
 */
function isUserLoggedIn() {
    // startSession(); // REMOVIDO - Já é chamado no topo do arquivo.
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