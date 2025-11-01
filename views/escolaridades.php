<?php
// Arquivo: views/escolaridades.php (REFATORADO COM HEADER/FOOTER)

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // Para login e helpers de ordenação

// 2. Importa o novo Repositório
use App\Repository\EscolaridadeRepository;

// 3. Segurança
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
// (OPCIONAL - Verificação de permissão)
$authService->checkAndFail('config:view', '../index.php?error=Acesso+negado');


// 4. Definições da Página (para o header.php)
$page_title = 'Gestão de Níveis de Escolaridade';
$root_path = '../'; 
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Gestão de Escolaridades' => null // Página ativa
];
// NOVO: Informa ao footer.php qual script JS carregar
$page_scripts = ['../scripts/escolaridades.js'];


// Configurações específicas desta tabela
$id_column = 'escolaridadeId';
$name_column = 'escolaridadeTitulo';

$message = '';
$message_type = '';

// Instancia o Repositório
$repo = new EscolaridadeRepository();

// ----------------------------------------------------
// LÓGICA DE CRUD (CREATE/UPDATE/DELETE)
// ----------------------------------------------------
try {
    // 1. Lógica de CREATE/UPDATE (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $titulo = trim($_POST[$name_column] ?? '');
        
        $repo->save($_POST); // O repositório lida com insert/update e validação
        
        $action_desc = ($_POST['action'] === 'insert') ? 'cadastrada' : 'atualizada';
        $message = "Escolaridade '{$titulo}' {$action_desc} com sucesso!";
        $message_type = 'success';
    }

    // 2. Lógica de DELETE (GET)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $deleted = $repo->delete($id);
        
        if ($deleted) {
            $message = "Escolaridade ID {$id} excluída com sucesso!";
            $message_type = 'success';
        } else {
            $message = "Erro: Escolaridade ID {$id} não encontrada ou já excluída.";
            $message_type = 'danger';
        }
        
        // Redireciona para limpar a URL após a ação
        header("Location: escolaridades.php?message=" . urlencode($message) . "&type={$message_type}");
        exit;
    }

} catch (Exception $e) {
    // Captura qualquer exceção do Repositório (validação, FK, DB)
    $message = $e.getMessage();
    $message_type = 'danger';
}

// Mensagens vindas de um redirecionamento (ex: após delete)
if (empty($message) && isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// ----------------------------------------------------
// LÓGICA DE LEITURA (READ)
// ----------------------------------------------------
// 1. Parâmetros de Filtro e Ordenação (Alinhado com functions.php)
$params = [
    'term' => $_GET['term'] ?? '',
    'sort_col' => $_GET['sort_col'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC',
    'page' => $_GET['page'] ?? 1,
    'limit' => 10
];

// 2. Busca os dados usando o Repositório
try {
    // "Traduz" 'sort_col' para 'order_by' que o Repositório espera
    $repoParams = [
        'term' => $params['term'],
        'order_by' => $params['sort_col'], // Repositório espera 'order_by'
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
    $message = "Erro ao carregar dados: " . $e->getMessage();
    $message_type = 'danger';
}


// 7. Inclui o Header
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><?php echo $page_title; ?></h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cadastroModal" id="btnNovoCadastro">
        <i class="fas fa-plus"></i> Novo Nível
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="d-flex">
            <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por título..." value="<?php echo htmlspecialchars($params['term']); ?>">
            <input type="hidden" name="sort_col" value="<?php echo htmlspecialchars($params['sort_col']); ?>">
            <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
            
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            <?php if (!empty($params['term'])): ?>
                <a href="escolaridades.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th><?php echo createSortLink($id_column, 'ID', $params); ?></th>
                        <th><?php echo createSortLink($name_column, 'Título', $params); ?></th>
                        <th width="150px" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row[$id_column]); ?></td>
                                <td><strong><?php echo htmlspecialchars($row[$name_column]); ?></strong></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white btn-edit" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#cadastroModal"
                                            data-id="<?php echo $row[$id_column]; ?>"
                                            data-titulo="<?php echo htmlspecialchars($row[$name_column]); ?>"
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <a href="escolaridades.php?action=delete&id=<?php echo $row[$id_column]; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Excluir"
                                       onclick="return confirm('Deseja realmente excluir este item? Esta ação não pode ser desfeita.');">
                                       <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center p-4">
                                <i class="fas fa-info-circle fa-2x text-muted mb-2"></i><br>
                                Nenhum registro encontrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($totalRecords > 0): ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted">
            Total: <strong><?php echo $totalRecords; ?></strong> registo(s)
        </span>
        
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Navegação de página">
            <ul class="pagination mb-0">
                
                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                    <?php $prev_query = http_build_query(array_merge($params, ['page' => $currentPage - 1])); ?>
                    <a class="page-link" href="?<?php echo $prev_query; ?>">Anterior</a>
                </li>

                <?php 
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                if ($endPage - $startPage < 4) { $startPage = max(1, $endPage - 4); }
                if ($endPage - $startPage < 4) { $endPage = min($totalPages, $startPage + 4); }

                for ($i = $startPage; $i <= $endPage; $i++): 
                    $page_query = http_build_query(array_merge($params, ['page' => $i]));
                ?>
                    <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo $page_query; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                    <?php $next_query = http_build_query(array_merge($params, ['page' => $currentPage + 1])); ?>
                    <a class="page-link" href="?<?php echo $next_query; ?>">Próxima</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<div class="modal fade" id="cadastroModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLabel">Cadastrar Nova Escolaridade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="modalAction" value="insert">
                    <input type="hidden" name="<?php echo $id_column; ?>" id="modalId" value="">

                    <div class="mb-3">
                        <label for="modalTitulo" class="form-label">Título da Escolaridade *</label>
                        <input type="text" class="form-control" id="modalTitulo" name="<?php echo $name_column; ?>" required maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvar">Salvar Cadastro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// 8. Inclui o Footer
include '../includes/footer.php';
?>