<?php
// Arquivo: views/riscos.php (VIEW: Ponto de Entrada)

// 1. INCLUDES GLOBAIS
require_once '../vendor/autoload.php'; // Carrega o autoload do Composer
require_once '../config.php';
require_once '../includes/functions.php'; // Necessário para isUserLoggedIn() e createSortLink()

// 2. IMPORTA O CONTROLLER
use App\Controller\RiscoController;

// 3. DEFINIÇÕES DA PÁGINA 
$page_title = 'Gestão de Riscos de Exposição';
$root_path = '../'; 
$breadcrumb_items = [
    'Dashboard' => $root_path . 'index.php',
    'Gestão de Riscos' => null // Página ativa
];
$page_scripts = [$root_path . 'scripts/riscos.js'];

// 4. INSTANCIA O CONTROLLER E PROCESSA A REQUISIÇÃO
// O Controller fará a segurança, o CRUD (e redirecionará se necessário),
// ou buscará os dados para a listagem.
$controller = new RiscoController();
$data = $controller->handleRequest($_GET, $_POST, $_SERVER['REQUEST_METHOD']);

// 5. EXTRAI AS VARIÁVEIS PARA A VIEW
// Isso cria $registros, $params, $message, $id_column, etc.
extract($data);

// 6. Inclui o Header (HTML)
// (O header.php agora aplica o padding-top globalmente)
include $root_path . 'includes/header.php';
?>

<div class="container mt-4 mb-5">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><?php echo $page_title; ?></h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cadastroModal" id="btnNovoCadastro">
        <i class="fas fa-plus"></i> Novo Risco
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="d-flex">
            <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por nome do risco..." value="<?php echo htmlspecialchars($params['term']); ?>">
            <input type="hidden" name="sort_col" value="<?php echo htmlspecialchars($params['sort_col']); ?>">
            <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
            
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            <?php if (!empty($params['term'])): ?>
                <a href="riscos.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th><?php echo createSortLink($id_column, 'ID', $params); ?></th>
                        <th><?php echo createSortLink($name_column, 'Nome do Risco', $params); ?></th>
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
                                    <a href="riscos.php?action=delete&id=<?php echo $row[$id_column]; ?>" 
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
                
                <?php 
                $prev_query = http_build_query(array_merge($params, ['page' => $currentPage - 1]));
                $next_query = http_build_query(array_merge($params, ['page' => $currentPage + 1]));
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                if ($endPage - $startPage < 4) { $startPage = max(1, $endPage - 4); }
                if ($endPage - $startPage < 4) { $endPage = min($totalPages, $startPage + 4); }
                ?>

                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?<?php echo $prev_query; ?>">Anterior</a>
                </li>

                <?php for ($i = $startPage; $i <= $endPage; $i++): 
                    $page_query = http_build_query(array_merge($params, ['page' => $i]));
                ?>
                    <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo $page_query; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?<?php echo $next_query; ?>">Próxima</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div> </div> <div class="modal fade" id="cadastroModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLabel">Cadastrar Novo Risco</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="riscos.php"> 
                <div class="modal-body">
                    <input type="hidden" name="action" id="modalAction" value="insert">

                    <div class="mb-3">
                        <label for="modalNome" class="form-label">Nome do Risco (ENUM) *</label>
                        <select class="form-select" id="modalNome" name="<?php echo $name_column; ?>" required>
                            <option value="">Selecione um tipo de risco...</option>
                            <?php foreach ($tipos_risco_enum as $tipo): ?>
                                <option value="<?php echo $tipo; ?>"><?php echo $tipo; ?></option>
                            <?php endforeach; ?>
                        </select>
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
// 7. Inclui o Footer
include $root_path . 'includes/footer.php';
?>