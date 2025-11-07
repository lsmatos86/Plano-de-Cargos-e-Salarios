<?php
// Arquivo: views/recursos.php (REFATORADO COM HEADER/FOOTER)

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // Para login e helpers

// 2. Importa os Repositórios
use App\Repository\RecursoRepository;
use App\Repository\GrupoRecursoRepository; // (Assumindo que este é o nome)

// 3. Segurança
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
// (OPCIONAL - Verificação de permissão)
$authService->checkAndFail('config:view', '../index.php?error=Acesso+negado');


// 4. Definições da Página (para o header.php)
$page_title = 'Gestão de Recursos e Grupos de Recursos';
$root_path = '../'; 
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Gestão de Recursos' => null // Página ativa
];
// NOVO: Informa ao footer.php qual script JS carregar
$page_scripts = ['../scripts/recursos.js'];


$message = '';
$message_type = '';

// Instancia os Repositórios
$recursoRepo = new RecursoRepository();
$grupoRepo = new GrupoRecursoRepository();

// ----------------------------------------------------
// LÓGICA DE CRUD (GRUPOS e RECURSOS)
// ----------------------------------------------------
try {
    // 1. Ação de Salvar (POST) - Cobre CREATE/UPDATE para ambos
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        
        // Salva GRUPO
        if ($_POST['form_type'] === 'grupo') {
            $nome = trim($_POST['recursoGrupoNome'] ?? '');
            $grupoRepo->save($_POST);
            $action_desc = ($_POST['action'] === 'insert') ? 'cadastrado' : 'atualizado';
            $message = "Grupo '{$nome}' {$action_desc} com sucesso!";
            $message_type = 'success';
        } 
        
        // Salva RECURSO
        elseif ($_POST['form_type'] === 'recurso') {
            $nome = trim($_POST['recursoNome'] ?? '');
            $recursoRepo->save($_POST);
            $action_desc = ($_POST['action'] === 'insert') ? 'cadastrado' : 'atualizado';
            $message = "Recurso '{$nome}' {$action_desc} com sucesso!";
            $message_type = 'success';
        }
    }

    // 2. Ação de Excluir (GET)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $type = $_GET['type'] ?? '';

        if ($type === 'grupo') {
            $deleted = $grupoRepo->delete($id);
            $message = $deleted ? "Grupo ID {$id} excluído." : "Erro ao excluir Grupo ID {$id}.";
        } elseif ($type === 'recurso') {
            $deleted = $recursoRepo->delete($id);
            $message = $deleted ? "Recurso ID {$id} excluído." : "Erro ao excluir Recurso ID {$id}.";
        }
        
        $message_type = $deleted ? 'success' : 'danger';
        
        // Redireciona para limpar a URL
        header("Location: recursos.php?message=" . urlencode($message) . "&type={$message_type}");
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

// 1. Busca Grupos (para o <select> e para a tabela)
$todosGrupos = $grupoRepo->findAllSimple(); // Retorna [id => ..., nome => ...]

// 2. Parâmetros de Filtro e Paginação (para Recursos)
$params = [
    'term' => $_GET['term'] ?? '',
    'sort_col' => $_GET['sort_col'] ?? 'r.recursoId',
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

    // O método RecursoRepository::findAllPaginated precisa ser implementado
    // Assumindo que retorna 'recursoNome', 'recursoId', 'recursoDescricao', 'recursoGrupoId'
    $result = $recursoRepo->findAllPaginated($repoParams); 
    
    $registrosRecursos = $result['data'];
    $totalRecords = $result['total'];
    $totalPages = $result['totalPages'];
    $currentPage = $result['currentPage'];

} catch (Exception $e) {
    $registrosRecursos = [];
    $totalRecords = 0;
    $totalPages = 1;
    $currentPage = 1;
    $message = "Erro ao carregar dados dos Recursos: " . $e->getMessage();
    $message_type = 'danger';
}


// 7. Inclui o Header
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><?php echo $page_title; ?></h1>
    <div>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalGrupo" id="btnNovoGrupo">
            <i class="fas fa-plus"></i> Novo Grupo
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRecurso" id="btnNovoRecurso">
            <i class="fas fa-plus"></i> Novo Recurso
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
                <h5 class="mb-0">Grupos de Recursos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Nome do Grupo</th>
                                <th width="100px" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($todosGrupos) > 0): ?>
                                <?php foreach ($todosGrupos as $grupo): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($grupo['nome']); ?></strong></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info text-white btn-edit-grupo" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalGrupo"
                                                    data-id="<?php echo $grupo['id']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($grupo['nome']); ?>"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="recursos.php?action=delete&type=grupo&id=<?php echo $grupo['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Excluir"
                                               onclick="return confirm('Deseja realmente excluir este Grupo?');">
                                               <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center">Nenhum grupo cadastrado.</td></tr>
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
                    <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por nome do recurso..." value="<?php echo htmlspecialchars($params['term']); ?>">
                    <input type="hidden" name="sort_col" value="<?php echo htmlspecialchars($params['sort_col']); ?>">
                    <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
                    
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if (!empty($params['term'])): ?>
                        <a href="recursos.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th><?php echo createSortLink('r.recursoNome', 'Nome do Recurso', $params); ?></th>
                                <th width="100px" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registrosRecursos) > 0): ?>
                                <?php foreach ($registrosRecursos as $row): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['recursoNome']); ?></strong></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info text-white btn-edit-recurso" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalRecurso"
                                                    data-id="<?php echo $row['recursoId']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($row['recursoNome']); ?>"
                                                    data-descricao="<?php echo htmlspecialchars($row['recursoDescricao'] ?? ''); ?>"
                                                    data-grupo-id="<?php echo htmlspecialchars($row['recursoGrupoId'] ?? ''); ?>"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="recursos.php?action=delete&type=recurso&id=<?php echo $row['recursoId']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Excluir"
                                               onclick="return confirm('Deseja realmente excluir este Recurso?');">
                                               <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center p-4">
                                    <i class="fas fa-info-circle fa-2x text-muted mb-2"></i><br>
                                    Nenhum recurso encontrado com os filtros atuais.
                                </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($totalRecords > 0): ?>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <span class="text-muted">
                    Total: <strong><?php echo $totalRecords; ?></strong> recurso(s)
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

<div class="modal fade" id="modalGrupo" tabindex="-1" aria-labelledby="modalGrupoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalGrupoLabel">Cadastrar Novo Grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="form_type" value="grupo">
                    <input type="hidden" name="action" id="grupoAction" value="insert">
                    <input type="hidden" name="recursoGrupoId" id="grupoId" value="">

                    <div class="mb-3">
                        <label for="grupoNome" class="form-label">Nome do Grupo *</label>
                        <input type="text" class="form-control" id="grupoNome" name="recursoGrupoNome" required maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success" id="btnSalvarGrupo">Salvar Grupo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRecurso" tabindex="-1" aria-labelledby="modalRecursoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalRecursoLabel">Cadastrar Novo Recurso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="form_type" value="recurso">
                    <input type="hidden" name="action" id="recursoAction" value="insert">
                    <input type="hidden" name="recursoId" id="recursoId" value="">

                    <div class="mb-3">
                        <label for="recursoNome" class="form-label">Nome do Recurso *</label>
                        <input type="text" class="form-control" id="recursoNome" name="recursoNome" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="recursoDescricao" class="form-label">Descrição (Opcional)</label>
                        <textarea class="form-control" id="recursoDescricao" name="recursoDescricao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvarRecurso">Salvar Recurso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// 8. Inclui o Footer
include '../includes/footer.php';
?>