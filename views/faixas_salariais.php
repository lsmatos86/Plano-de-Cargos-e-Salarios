<?php
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';

use App\Repository\FaixaSalarialRepository;

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
$authService->checkAndFail('config:view', '../index.php?error=Acesso+negado');

$page_title = 'Faixas Salariais';
$root_path = '../';
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Faixas Salariais' => null
];

$id_column   = 'faixaId';
$name_column = 'faixaNivel';
$message      = '';
$message_type = '';

$repo = new FaixaSalarialRepository();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $repo->save($_POST);
        $action_desc  = ($_POST['action'] === 'insert') ? 'cadastrada' : 'atualizada';
        $nivel        = htmlspecialchars($_POST[$name_column] ?? '');
        $message      = "Faixa salarial '{$nivel}' {$action_desc} com sucesso!";
        $message_type = 'success';
    }

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id      = (int)$_GET['id'];
        $deleted = $repo->delete($id);
        if ($deleted) {
            $message      = "Faixa salarial ID {$id} excluída com sucesso!";
            $message_type = 'success';
        } else {
            $message      = "Faixa salarial ID {$id} não encontrada ou já excluída.";
            $message_type = 'danger';
        }
        header("Location: faixas_salariais.php?message=" . urlencode($message) . "&type={$message_type}");
        exit;
    }
} catch (Exception $e) {
    $message      = $e->getMessage();
    $message_type = 'danger';
}

if (empty($message) && isset($_GET['message'])) {
    $message      = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

$params = [
    'term'     => $_GET['term'] ?? '',
    'sort_col' => $_GET['sort_col'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC',
    'page'     => $_GET['page'] ?? 1,
    'limit'    => 15,
];

try {
    $repoParams = [
        'term'     => $params['term'],
        'order_by' => $params['sort_col'],
        'sort_dir' => $params['sort_dir'],
        'page'     => $params['page'],
        'limit'    => $params['limit'],
    ];
    $result       = $repo->findAllPaginated($repoParams);
    $registros    = $result['data'];
    $totalRecords = $result['total'];
    $totalPages   = $result['totalPages'];
    $currentPage  = $result['currentPage'];
} catch (Exception $e) {
    $registros    = [];
    $totalRecords = 0;
    $totalPages   = 1;
    $currentPage  = 1;
    $message      = "Erro ao carregar dados: " . $e->getMessage();
    $message_type = 'danger';
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><?php echo $page_title; ?></h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cadastroModal">
        <i class="fas fa-plus"></i> Nova Faixa
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message ?? ''); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="d-flex">
            <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por nível..." value="<?php echo htmlspecialchars($params['term']); ?>">
            <input type="hidden" name="sort_col" value="<?php echo htmlspecialchars($params['sort_col']); ?>">
            <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            <?php if (!empty($params['term'])): ?>
                <a href="faixas_salariais.php" class="btn btn-outline-danger ms-2" title="Limpar"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th><?php echo createSortLink('faixaId', 'ID', $params); ?></th>
                        <th><?php echo createSortLink('faixaNivel', 'Nível', $params); ?></th>
                        <th><?php echo createSortLink('faixaSalarioMinimo', 'Salário Mínimo', $params); ?></th>
                        <th><?php echo createSortLink('faixaSalarioMaximo', 'Salário Máximo', $params); ?></th>
                        <th class="text-center" width="120px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo $row['faixaId']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['faixaNivel']); ?></strong></td>
                                <td>
                                    <?php echo $row['faixaSalarioMinimo'] !== null
                                        ? 'R$ ' . number_format((float)$row['faixaSalarioMinimo'], 2, ',', '.')
                                        : '<span class="text-muted">—</span>'; ?>
                                </td>
                                <td>
                                    <?php echo $row['faixaSalarioMaximo'] !== null
                                        ? 'R$ ' . number_format((float)$row['faixaSalarioMaximo'], 2, ',', '.')
                                        : '<span class="text-muted">—</span>'; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white btn-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#cadastroModal"
                                            data-id="<?php echo $row['faixaId']; ?>"
                                            data-nivel="<?php echo htmlspecialchars($row['faixaNivel']); ?>"
                                            data-salmin="<?php echo $row['faixaSalarioMinimo'] ?? ''; ?>"
                                            data-salmax="<?php echo $row['faixaSalarioMaximo'] ?? ''; ?>"
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="faixas_salariais.php?action=delete&id=<?php echo $row['faixaId']; ?>"
                                       class="btn btn-sm btn-danger"
                                       title="Excluir"
                                       onclick="return confirm('Deseja realmente excluir esta faixa salarial?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-4">
                                <i class="fas fa-info-circle fa-2x text-muted mb-2"></i><br>
                                Nenhuma faixa salarial cadastrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalRecords > 0): ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted">Total: <strong><?php echo $totalRecords; ?></strong> registro(s)</span>
        <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($params, ['page' => $currentPage - 1])); ?>">Anterior</a>
                </li>
                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                    <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($params, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($params, ['page' => $currentPage + 1])); ?>">Próxima</a>
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
                <h5 class="modal-title" id="modalLabel">Cadastrar Nova Faixa Salarial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="modalAction" value="insert">
                    <input type="hidden" name="faixaId" id="modalId" value="">

                    <div class="mb-3">
                        <label for="modalNivel" class="form-label">Nível / Nome da Faixa *</label>
                        <input type="text" class="form-control" id="modalNivel" name="faixaNivel" required maxlength="64">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modalSalMin" class="form-label">Salário Mínimo (R$)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="modalSalMin" name="faixaSalarioMinimo" placeholder="0,00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modalSalMax" class="form-label">Salário Máximo (R$)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="modalSalMax" name="faixaSalarioMaximo" placeholder="0,00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvar">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('cadastroModal');
    const modalLabel = document.getElementById('modalLabel');
    const modalAction = document.getElementById('modalAction');
    const modalId = document.getElementById('modalId');
    const modalNivel = document.getElementById('modalNivel');
    const modalSalMin = document.getElementById('modalSalMin');
    const modalSalMax = document.getElementById('modalSalMax');

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (btn && btn.classList.contains('btn-edit')) {
            modalLabel.textContent = 'Editar Faixa Salarial';
            modalAction.value = 'update';
            modalId.value     = btn.dataset.id;
            modalNivel.value  = btn.dataset.nivel;
            modalSalMin.value = btn.dataset.salmin || '';
            modalSalMax.value = btn.dataset.salmax || '';
        } else {
            modalLabel.textContent = 'Cadastrar Nova Faixa Salarial';
            modalAction.value = 'insert';
            modalId.value     = '';
            modalNivel.value  = '';
            modalSalMin.value = '';
            modalSalMax.value = '';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
