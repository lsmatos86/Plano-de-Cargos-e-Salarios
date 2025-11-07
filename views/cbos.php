<?php
// Arquivo: views/cbos.php (REFATORADO COM HEADER/FOOTER)

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // Para login e helpers

// 2. Importa os Repositórios
use App\Repository\CboRepository;
use App\Repository\FamiliaCboRepository;

// 3. Segurança
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
// (OPCIONAL - Verificação de permissão)
$authService->checkAndFail('config:view', '../index.php?error=Acesso+negado');


// 4. Definições da Página (para o header.php)
$page_title = 'Gestão de CBOs e Famílias CBO';
$root_path = '../'; 
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Gestão de CBOs' => null // Página ativa
];
// NOVO: Informa ao footer.php qual script JS carregar
$page_scripts = ['../scripts/cbos.js'];


$message = '';
$message_type = '';

// Instancia os Repositórios
$cboRepo = new CboRepository();
$familiaRepo = new FamiliaCboRepository();

// ----------------------------------------------------
// LÓGICA DE CRUD (FAMÍLIAS e CBOs)
// ----------------------------------------------------
try {
    // 1. Ação de Salvar (POST) - Cobre CREATE/UPDATE para ambos
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        
        // Salva FAMÍLIA CBO
        if ($_POST['form_type'] === 'familia') {
            $nome = trim($_POST['familiaCboNome'] ?? '');
            $familiaRepo->save($_POST);
            $action_desc = ($_POST['action'] === 'insert') ? 'cadastrada' : 'atualizada';
            $message = "Família CBO '{$nome}' {$action_desc} com sucesso!";
            $message_type = 'success';
        } 
        
        // Salva CBO
        elseif ($_POST['form_type'] === 'cbo') {
            $nome = trim($_POST['cboTituloOficial'] ?? '');
            $cboRepo->save($_POST);
            $action_desc = ($_POST['action'] === 'insert') ? 'cadastrado' : 'atualizado';
            $message = "CBO '{$nome}' {$action_desc} com sucesso!";
            $message_type = 'success';
        }
    }

    // 2. Ação de Excluir (GET)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $type = $_GET['type'] ?? '';

        if ($type === 'familia') {
            $deleted = $familiaRepo->delete($id);
            $message = $deleted ? "Família CBO ID {$id} excluída." : "Erro ao excluir Família CBO ID {$id}.";
        } elseif ($type === 'cbo') {
            $deleted = $cboRepo->delete($id);
            $message = $deleted ? "CBO ID {$id} excluído." : "Erro ao excluir CBO ID {$id}.";
        }
        
        $message_type = $deleted ? 'success' : 'danger';
        
        // Redireciona para limpar a URL
        header("Location: cbos.php?message=" . urlencode($message) . "&type={$message_type}");
        exit;
    }

} catch (Exception $e) {
    $message = $e->getMessage();
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

// 1. Busca Famílias (para o <select> e para a tabela)
$todasFamilias = $familiaRepo->findAllSimple();

// 2. Parâmetros de Filtro e Paginação (para CBOs)
$params = [
    'term' => $_GET['term'] ?? '',
    'sort_col' => $_GET['sort_col'] ?? 'c.cboId',
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC',
    'page' => $_GET['page'] ?? 1,
    'limit' => 10
];

try {
    // "Traduz" 'sort_col' para 'order_by' que o Repositório espera
    $repoParams = [
        'term' => $params['term'],
        'order_by' => $params['sort_col'],
        'sort_dir' => $params['sort_dir'],
        'page' => $params['page'],
        'limit' => $params['limit']
    ];

    $result = $cboRepo->findAllPaginated($repoParams);
    
    $registrosCBO = $result['data'];
    $totalRecords = $result['total'];
    $totalPages = $result['totalPages'];
    $currentPage = $result['currentPage'];

} catch (Exception $e) {
    $registrosCBO = [];
    $totalRecords = 0;
    $totalPages = 1;
    $currentPage = 1;
    $message = "Erro ao carregar dados dos CBOs: " . $e->getMessage();
    $message_type = 'danger';
}


// 7. Inclui o Header
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><?php echo $page_title; ?></h1>
    <div>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalFamilia" id="btnNovaFamilia">
            <i class="fas fa-plus"></i> Nova Família CBO
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCBO" id="btnNovoCBO">
            <i class="fas fa-plus"></i> Novo CBO
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Famílias CBO</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Nome</th>
                                <th width="100px" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($todasFamilias) > 0): ?>
                                <?php foreach ($todasFamilias as $familia): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($familia['nome']); ?></strong></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info text-white btn-edit-familia" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalFamilia"
                                                    data-id="<?php echo $familia['id']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($familia['nome']); ?>"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="cbos.php?action=delete&type=familia&id=<?php echo $familia['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Excluir"
                                               onclick="return confirm('Deseja realmente excluir esta Família? Se houver CBOs ligados a ela, a exclusão falhará.');">
                                               <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center">Nenhuma família CBO cadastrada.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <form method="GET" class="d-flex">
                    <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por Cód. CBO ou Título Oficial..." value="<?php echo htmlspecialchars($params['term']); ?>">
                    <input type="hidden" name="sort_col" value="<?php echo htmlspecialchars($params['sort_col']); ?>">
                    <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
                    
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if (!empty($params['term'])): ?>
                        <a href="cbos.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th><?php echo createSortLink('c.cboCod', 'Cód. CBO', $params); ?></th>
                                <th><?php echo createSortLink('c.cboTituloOficial', 'Título Oficial', $params); ?></th>
                                <th><?php echo createSortLink('f.familiaCboNome', 'Família', $params); ?></th>
                                <th width="100px" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registrosCBO) > 0): ?>
                                <?php foreach ($registrosCBO as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['cboCod']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['cboTituloOficial']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['familiaCboNome'] ?? 'N/A'); ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info text-white btn-edit-cbo" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalCBO"
                                                    data-id="<?php echo $row['cboId']; ?>"
                                                    data-cod="<?php echo htmlspecialchars($row['cboCod']); ?>"
                                                    data-titulo="<?php echo htmlspecialchars($row['cboTituloOficial']); ?>"
                                                    data-familia-id="<?php echo htmlspecialchars($row['familiaCboId']); ?>"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="cbos.php?action=delete&type=cbo&id=<?php echo $row['cboId']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Excluir"
                                               onclick="return confirm('Deseja realmente excluir este CBO?');">
                                               <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center p-4">
                                    <i class="fas fa-info-circle fa-2x text-muted mb-2"></i><br>
                                    Nenhum CBO encontrado com os filtros atuais.
                                </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($totalRecords > 0): ?>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <span class="text-muted">
                    Total: <strong><?php echo $totalRecords; ?></strong> CBO(s)
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
    </div>
</div>

<div class="modal fade" id="modalFamilia" tabindex="-1" aria-labelledby="modalFamiliaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalFamiliaLabel">Cadastrar Nova Família CBO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="form_type" value="familia">
                    <input type="hidden" name="action" id="familiaAction" value="insert">
                    <input type="hidden" name="familiaCboId" id="familiaId" value="">

                    <div class="mb-3">
                        <label for="familiaNome" class="form-label">Nome da Família *</label>
                        <input type="text" class="form-control" id="familiaNome" name="familiaCboNome" required maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success" id="btnSalvarFamilia">Salvar Família</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCBO" tabindex="-1" aria-labelledby="modalCBOLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCBOLabel">Cadastrar Novo CBO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="form_type" value="cbo">
                    <input type="hidden" name="action" id="cboAction" value="insert">
                    <input type="hidden" name="cboId" id="cboId" value="">

                    <div class="mb-3">
                        <label for="cboFamiliaId" class="form-label">Família CBO *</label>
                        <select class="form-select" id="cboFamiliaId" name="familiaCboId" required>
                            <option value="">-- Selecione a Família --</option>
                            <?php foreach ($todasFamilias as $familia): ?>
                                <option value="<?php echo $familia['id']; ?>">
                                    <?php echo htmlspecialchars($familia['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="cboCod" class="form-label">Código CBO (ex: 1234-56) *</label>
                        <input type="text" class="form-control" id="cboCod" name="cboCod" required maxlength="64">
                    </div>

                    <div class="mb-3">
                        <label for="cboTituloOficial" class="form-label">Título Oficial *</label>
                        <input type="text" class="form-control" id="cboTituloOficial" name="cboTituloOficial" required maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvarCBO">Salvar CBO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// 8. Inclui o Footer
include '../includes/footer.php';
?>