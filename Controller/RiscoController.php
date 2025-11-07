<?php
// Arquivo: Controller/RiscoController.php (Controller)

// O Controller é o ponto de entrada da lógica e deve cuidar de todas as dependências

// 1. Inclusão de arquivos básicos e autoload (USANDO __DIR__ PARA CAMINHOS ROBUSTOS)
require_once __DIR__ . '/../vendor/autoload.php'; 
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php'; 

// 2. Importa o Repositório
use App\Repository\RiscoRepository;
use App\Service\AuthService; 

// 3. Segurança e Definições
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isUserLoggedIn()) {
    header('Location: ../login.php'); // Redireciona para o login na raiz
    exit;
}

// Verifica permissão
$authService = new AuthService();
$authService->checkAndFail('riscos:manage', '../index.php?error=Acesso+negado'); // Redireciona para o index na raiz


// 4. Definições e Inicialização
$id_column = 'riscoId';
$name_column = 'riscoNome';
$message = '';
$message_type = '';

$repo = new RiscoRepository();

// ----------------------------------------------------
// A. LÓGICA DE TRATAMENTO DE MENSAGENS E REDIRECIONAMENTO
// ----------------------------------------------------
// Mensagens vindas de um redirecionamento (ex: após delete ou save)
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// ----------------------------------------------------
// B. LÓGICA DE CRUD (CREATE/DELETE)
// ----------------------------------------------------
try {
    // 1. Lógica de CREATE (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        
        $titulo = trim($_POST[$name_column] ?? '');
        
        if ($_POST['action'] === 'insert') {
            // Nota: Assumindo que $repo->save($_POST) está implementado e funcional
            $repo->save($_POST); 
            $message = "Risco '{$titulo}' cadastrado com sucesso!";
            $message_type = 'success';
        }
        
        // Redireciona para a View de listagem
        header("Location: ../views/riscos.php?message=" . urlencode($message) . "&type={$message_type}");
        exit;
    }

    // 2. Lógica de DELETE (GET)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $deleted = $repo->delete($id);
        
        if ($deleted) {
            $message = "Risco ID {$id} excluído com sucesso!";
            $message_type = 'success';
        } else {
            $message = "Erro: Risco ID {$id} não encontrado ou já excluído.";
            $message_type = 'danger';
        }
        
        // Redireciona para a View de listagem
        header("Location: ../views/riscos.php?message=" . urlencode($message) . "&type={$message_type}");
        exit;
    }

} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $message = "Erro: O risco '{$titulo}' já está cadastrado.";
        $message_type = 'danger';
    } else {
        $message = $e->getMessage();
        $message_type = 'danger';
    }
    
    // Redireciona para a View com a mensagem de erro
    header("Location: ../views/riscos.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// C. LÓGICA DE LEITURA (READ)
// ----------------------------------------------------
// Parâmetros de Filtro e Ordenação
$id_column = 'riscoId';
$params = [
    'term' => $_GET['term'] ?? '',
    'sort_col' => $_GET['sort_col'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC',
    'page' => $_GET['page'] ?? 1,
    'limit' => 10
];

try {
    $repoParams = [
        'term' => $params['term'],
        'order_by' => $params['sort_col'], 
        'sort_dir' => $params['sort_dir'],
        'page' => $params['page'],
        'limit' => $params['limit']
    ];

    $result = $repo->findAllPaginated($repoParams);
    
    $registros = $result['data'];
    $totalRecords = $result['total'];
    $totalPages = $result['totalPages'];
    $currentPage = $result['currentPage'];

} catch (Exception $e) {
    $registros = [];
    $totalRecords = 0;
    $totalPages = 1;
    $currentPage = 1;
    
    if (empty($message)) {
        $message = "Erro ao carregar dados: " . $e->getMessage();
        $message_type = 'danger';
    }
}

// Lista de riscos (ENUM) para o <select> do modal
$tipos_risco_enum = [
    'Físico', 'Químico', 'Ergonômico', 'Psicossocial', 'Acidental', 'Biológico'
];
?>