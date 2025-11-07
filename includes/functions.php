<?php
// Arquivo: includes/functions.php (Completo e Corrigido)

/**
 * Este arquivo é responsável por funções globais e pela inicialização de serviços.
 */

// 1. INICIAR SESSÃO (SEMPRE PRIMEIRO)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. IMPORTAR CLASSES GLOBAIS
use App\Core\Database;
use App\Service\AuthService;
use App\Service\AuditService;
// PDO é uma classe global e não precisa ser importada com 'use'.

// 3. INICIALIZAR SERVIÇOS GLOBAIS
global $authService, $auditService;

try {
    $authService = new AuthService(); 
    $auditService = new AuditService();
} catch (Exception $e) {
    error_log('Erro ao inicializar serviços globais: ' . $e->getMessage());
    die("Erro crítico na inicialização da aplicação. Verifique o log.");
}


// 4. FUNÇÃO DE VERIFICAÇÃO DE LOGIN
/**
 * Verifica se o usuário está logado.
 * @return bool
 */
function isUserLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}


// 5. FUNÇÃO DE AUTENTICAÇÃO
/**
 * Tenta autenticar um usuário com base no e-mail e senha.
 */
function authenticateUser(string $email, string $password): bool
{
    global $auditService; 

    try {
        // Uso direto da classe global PDO, sem necessidade de 'use PDO;'
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT usuarioId, nome, email, senha, ativo FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['senha'])) {
            
            if ($user['ativo'] != 1) {
                // (Correção de argumentos aplicada)
                $auditService->log(
                    'LOGIN_FAIL',
                    'usuarios',
                    $user['usuarioId'],
                    ['motivo' => 'Usuário inativo'], // Arg 4
                    $user['nome']                    // Arg 5
                );
                return false;
            }

            session_regenerate_id(true); 
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = (int)$user['usuarioId'];
            $_SESSION['username'] = $user['nome'];
            $_SESSION['email'] = $user['email'];

            // (Correção de argumentos aplicada)
            $auditService->log(
                'LOGIN_SUCCESS',
                'usuarios',
                $user['usuarioId'],
                null,           // Arg 4
                $user['nome']   // Arg 5
            );
            
            return true;
        }

        // (Correção de argumentos aplicada)
        $auditService->log(
            'LOGIN_FAIL',
            'usuarios',
            null,
            ['motivo' => 'Credenciais inválidas'], // Arg 4
            $email                                 // Arg 5
        );
        
        return false;

    } catch (PDOException $e) {
        error_log('Erro em authenticateUser: ' . $e->getMessage());
        
        // (Correção de argumentos aplicada)
        $auditService->log(
            'LOGIN_FAIL',
            'usuarios',
            null,
            ['motivo' => 'Erro interno (DB)', 'error' => $e->getMessage()], // Arg 4
            $email                                                        // Arg 5
        );
        
        return false;
    }
}

// 6. FUNÇÃO DE LOGOUT
/**
 * Destrói a sessão do usuário (Logout).
 */
function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

// 7. FUNÇÃO DE CONEXÃO COM O BANCO DE DADOS
/**
 * Retorna a conexão PDO com o banco de dados.
 * (Wrapper para App\Core\Database::getConnection)
 * @return PDO
 */
function getDbConnection(): PDO
{
    return Database::getConnection();
}


// 8. FUNÇÃO DE VALIDAÇÃO
/**
 * Valida se um nome de tabela está em uma "lista segura".
 */
function isValidTableName(string $tableName): bool
{
    $allowedTables = [
        'cargos', 'habilidades', 'caracteristicas', 'riscos', 'usuarios',
        'cursos', 'areas_atuacao', 'nivel_hierarquico', 'faixas_salariais',
        'cbos', 'escolaridades', 'recursos_grupos', // Adicionados para lookup
    ];
    return in_array($tableName, $allowedTables);
}


// 9. FUNÇÕES AUXILIARES DE LOOKUP DE DADOS
// use PDO; // LINHA REMOVIDA (ANTIGA LINHA 164)

/**
 * Retorna dados de uma tabela simples como um array associativo [keyCol => value].
 * Suporta a concatenação de duas colunas para o valor (e.g., CBO: código - título).
 * @param PDO $pdo Conexão PDO.
 * @param string $tableName Nome da tabela para lookup.
 * @param string $keyCol Coluna a ser usada como chave (e.g., 'cboId').
 * @param string $valueCol Coluna principal para o valor (e.g., 'cboCod').
 * @param string|null $secondValueCol Coluna secundária para concatenar ao valor (e.g., 'cboTituloOficial').
 * @return array
 */
function getLookupData(PDO $pdo, string $tableName, string $keyCol, string $valueCol, string $secondValueCol = null): array
{
    // Verifica se o nome da tabela é seguro antes de construir a query
    if (!isValidTableName($tableName)) {
        error_log("Tentativa de lookup em tabela não permitida: {$tableName}");
        return []; 
    }

    $fields = $keyCol . ', ' . $valueCol;
    if ($secondValueCol) {
        $fields .= ', ' . $secondValueCol;
    }

    $sql = "SELECT {$fields} FROM {$tableName} ORDER BY {$valueCol}";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lookupArray = [];
    foreach ($results as $row) {
        $key = $row[$keyCol];
        $value = $row[$valueCol];

        if ($secondValueCol) {
            // Concatena as duas colunas
            $value = $row[$valueCol] . ' - ' . ($row[$secondValueCol] ?? '');
        }

        $lookupArray[$key] = $value;
    }

    return $lookupArray;
}

/**
 * Retorna as habilidades agrupadas por tipo (Hard/Soft Skill).
 * @param PDO $pdo Conexão PDO.
 * @return array Um array aninhado [tipo => [habilidadeId => habilidadeNome]].
 */
function getHabilidadesGrouped(PDO $pdo): array
{
    // Assumindo a tabela 'habilidades' possui 'habilidadeId', 'habilidadeNome' e 'habilidadeTipo'
    $sql = "SELECT habilidadeId, habilidadeNome, habilidadeTipo FROM habilidades ORDER BY habilidadeTipo, habilidadeNome";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $groupedArray = [];
    foreach ($results as $row) {
        // Agrupa por habilidadeTipo
        $groupedArray[$row['habilidadeTipo']][$row['habilidadeId']] = $row['habilidadeNome'];
    }
    return $groupedArray;
}

/**
 * Retorna uma lista plana de áreas de atuação, com o nome possivelmente formatado 
 * para refletir a hierarquia (e.g., 'Setor > Área').
 * @param PDO $pdo Conexão PDO.
 * @return array Um array associativo [areaId => areaNomeHierarquico].
 */
function getAreaHierarchyLookup(PDO $pdo): array
{
    // Usamos uma consulta que busca o nome e o ID, assumindo que a lógica de formatação hierárquica 
    // será implementada posteriormente (ou já existe em um Repositório que não vemos).
    $sql = "SELECT areaId, areaNome FROM areas_atuacao ORDER BY areaNome";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $lookupArray = [];
    foreach ($results as $row) {
        // Por enquanto, apenas retorna o nome simples.
        $lookupArray[$row['areaId']] = $row['areaNome'];
    }
    return $lookupArray;
}


// 10. FUNÇÕES HELPER PARA ORDENAÇÃO DE TABELAS

/**
 * Determina a próxima direção de ordenação (ASC/DESC).
 */
function getSortDirection(string $currentSortCol, string $currentSortDir, string $columnName): string
{
    if ($currentSortCol === $columnName && $currentSortDir === 'ASC') {
        return 'DESC';
    }
    return 'ASC';
}

/**
 * Cria um link de cabeçalho de tabela <a> com ícone de ordenação.
 */
function createSortLink(string $columnName, string $displayName, array $params): string
{
    $currentSortCol = $params['sort_col'] ?? '';
    $currentSortDir = $params['sort_dir'] ?? 'ASC';

    // 1. Determina a próxima direção de ordenação
    $nextSortDir = getSortDirection($currentSortCol, $currentSortDir, $columnName);
    
    // 2. Define o ícone a ser exibido
    $icon = 'fa-sort'; // Ícone padrão
    if ($currentSortCol === $columnName) {
        $icon = ($currentSortDir === 'ASC') ? 'fa-sort-up' : 'fa-sort-down';
    }

    // 3. Monta os parâmetros da URL, mesclando os existentes com os novos
    $queryParams = array_merge($params, [
        'sort_col' => $columnName,
        'sort_dir' => $nextSortDir
    ]);

    // Remove 'page' para voltar à primeira página ao reordenar
    unset($queryParams['page']);

    // 4. Constrói a URL
    $url = htmlspecialchars(basename($_SERVER['PHP_SELF'])) . '?' . http_build_query($queryParams);

    // 5. Retorna o link HTML
    return '<a href="' . $url . '" class="text-decoration-none text-dark">' .
           htmlspecialchars($displayName) . 
           ' <i class="fas ' . $icon . ' ms-1 text-muted"></i>' .
           '</a>';
}


// 11. FUNÇÃO HELPER PARA RELATÓRIOS (NOVA)

/**
 * Retorna a classe do ícone Font Awesome para um tipo de risco.
 * Usado em relatorios/cargo_individual.php
 *
 * @param string $riscoNome O nome do risco (ex: 'Físico', 'Químico')
 * @return string A classe do ícone (ex: 'fas fa-flask')
 */
function getRiscoIcon(string $riscoNome): string
{
    switch ($riscoNome) {
        case 'Físico':
            return 'fas fa-thermometer-half'; // Ícone para ruído, calor, vibração
        case 'Químico':
            return 'fas fa-flask'; // Ícone para gases, poeira
        case 'Ergonômico':
            return 'fas fa-chair'; // Ícone para postura, LER/DORT
        case 'Psicossocial':
            return 'fas fa-brain'; // Ícone para stress, ansiedade
        case 'Acidental':
            return 'fas fa-exclamation-triangle'; // Ícone para perigo, queda
        case 'Biológico':
            return 'fas fa-bacterium'; // Ícone para vírus, bactérias
        default:
            return 'fas fa-question-circle'; // Ícone padrão
    }
}