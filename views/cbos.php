<?php
// Arquivo: views/cbos.php (REFATORADO)

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // (Ainda necessário para isUserLoggedIn e getSortDirection)

// 2. Importa os Repositórios
use App\Repository\CboRepository;
use App\Repository\LookupRepository;

// Redireciona para o login se o usuário não estiver autenticado
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Configurações
$page_title = 'Gestão de Códigos CBO';
$id_column = 'cboId'; // PK
$name_column = 'cboCod';
$title_column = 'cboTituloOficial';
$fk_column = 'familiaCboId';
$message = '';
$message_type = '';

// Instancia os Repositórios
$repo = new CboRepository();
$lookupRepo = new LookupRepository();

// Carrega os dados FK para o SELECT do formulário
try {
    $familias_cbo = $lookupRepo->getLookup('familia_cbo', 'familiaCboId', 'familiaCboNome');
} catch (Exception $e) {
    $familias_cbo = [];
    $message = "Erro ao carregar famílias CBO: " . $e->getMessage();
    $message_type = 'danger';
}

// ----------------------------------------------------
// LÓGICA DE CRUD (CREATE/UPDATE/DELETE) - REFATORADO
// ----------------------------------------------------
$error_data = []; // Armazena dados do POST em caso de erro

try {
    // 1. Lógica de CREATE/UPDATE (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        
        $repo->save($_POST); // O repositório lida com insert/update e validação
        
        $cod = trim($_POST[$name_column] ?? '');
        $action_desc = ($_POST['action'] === 'insert') ? 'cadastrado' : 'atualizado';
        $message = "CBO '{$cod}' {$action_desc} com sucesso!";
        $message_type = 'success';
    }

    // 2. Lógica de DELETE (GET)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $deleted = $repo->delete($id);
        
        if ($deleted) {
            $message = "CBO ID {$id} excluído com sucesso!";
            $message_type = 'success';
        } else {
            $message = "Erro: CBO ID {$id} não encontrado ou já excluído.";
            $message_type = 'danger';
        }
        
        // Redireciona para limpar a URL após a ação
        header("Location: cbos.php?message=" . urlencode($message) . "&type={$message_type}");
        exit;
    }

} catch (Exception $e) {
    // Captura qualquer exceção do Repositório (validação, FK, DB)
    $message = $e->getMessage();
    $message_type = 'danger';
    // Salva os dados do POST para repopular o modal em caso de erro
    $error_data = $_POST;
}

// Mensagens vindas de um redirecionamento
if (empty($message) && isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// ----------------------------------------------------
// LÓGICA DE LEITURA (READ) - REFATORADO
// ----------------------------------------------------
// 1. Parâmetros de Filtro e Ordenação
$params = [
    'term' => $_GET['term'] ?? '',
    'order_by' => $_GET['order_by'] ?? 'c.cboCod', // Padrão
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC',    // Padrão
    'page' => $_GET['page'] ?? 1,
    'limit' => 10
];

// 2. Busca os dados usando o Repositório
try {
    $result = $repo->findAllPaginated($params);
    
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

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        .short-text { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        /* Corrige o z-index do select2 no modal */
        .select2-container--bootstrap-5 .select2-dropdown { z-index: 1060; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid container">
        <a class="navbar-brand" href="../index.php">ITACITRUS | Início</a>
        <div class="d-flex">
            <span class="navbar-text me-3 text-white">Olá, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuário'); ?></span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-outline-secondary btn-sm" onclick="history.back()">
            <i class="fas fa-arrow-left"></i> Voltar
        </button>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Página Inicial</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $page_title; ?></li>
            </ol>
        </nav>
    </div>

    <h1 class="mb-4"><?php echo $page_title; ?></h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-4">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cadastroModal" id="btnNovoCadastro">
                <i class="fas fa-plus"></i> Novo CBO
            </button>
        </div>
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por Cód, Título ou Família..." value="<?php echo htmlspecialchars($params['term']); ?>">
                <input type="hidden" name="order_by" value="<?php echo htmlspecialchars($params['order_by']); ?>">
                <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
                
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                <?php if (!empty($params['term'])): ?>
                    <a href="cbos.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i> Limpar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <span class="fw-bold">Registros Encontrados: </span> <?php echo $totalRecords; ?> (Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <?php 
                            function createSortLink($column, $text, $params) {
                                $new_dir = getSortDirection($params['order_by'], $column);
                                $icon = 'fa-sort';
                                if ($params['order_by'] === $column) {
                                    $icon = $new_dir === 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
                                }
                                $query_params = http_build_query(array_merge($params, ['order_by' => $column, 'sort_dir' => $new_dir, 'page' => 1]));
                                return '<a href="?' . $query_params . '" class="text-decoration-none text-dark"><i class="fas ' . $icon . ' me-1"></i> ' . $text . '</a>';
                            }
                            ?>
                            <th><?php echo createSortLink('c.cboCod', 'Cód. CBO', $params); ?></th>
                            <th><?php echo createSortLink('c.cboTituloOficial', 'Título Oficial', $params); ?></th>
                            <th><?php echo createSortLink('f.familiaCboNome', 'Família CBO', $params); ?></th>
                            <th width="120px" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($registros) > 0): ?>
                            <?php foreach ($registros as $row): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['cboCod']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['cboTituloOficial']); ?></td>
                                    <td><?php echo htmlspecialchars($row['familiaCboNome'] ?? 'N/A'); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info text-white btn-edit" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#cadastroModal"
                                                data-id="<?php echo $row[$id_column]; ?>"
                                                data-cod="<?php echo htmlspecialchars($row['cboCod']); ?>"
                                                data-titulo="<?php echo htmlspecialchars($row['cboTituloOficial']); ?>"
                                                data-familia="<?php echo htmlspecialchars($row[$fk_column]); ?>"
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="cbos.php?action=delete&id=<?php echo $row[$id_column]; ?>" 
                                            class="btn btn-sm btn-danger" 
                                            title="Excluir"
                                            onclick="return confirm('ATENÇÃO: A exclusão deste CBO pode afetar os Cargos. Deseja realmente excluir?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="text-center">
                                <td colspan="4">Nenhum registro encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Navegação de página" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                <?php $prev_query = http_build_query(array_merge($params, ['page' => $currentPage - 1])); ?>
                <a class="page-link" href="?<?php echo $prev_query; ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): 
                $page_query = http_build_query(array_merge($params, ['page' => $i])); ?>
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

<div class="modal fade" id="cadastroModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLabel">Novo Código CBO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="modalAction" value="insert">
                    <input type="hidden" name="<?php echo $id_column; ?>" id="modalId" value="">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="modalCod" class="form-label">Cód. CBO (ex: 1234-56) *</label>
                            <input type="text" class="form-control" id="modalCod" name="<?php echo $name_column; ?>" required maxlength="7">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="modalFamiliaId" class="form-label">Família CBO *</label>
                            <select class="form-select select2-modal" id="modalFamiliaId" name="<?php echo $fk_column; ?>" required style="width: 100%;">
                                <option value="">--- Selecione uma Família ---</option>
                                <?php foreach ($familias_cbo as $id => $nome): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($nome); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modalTitulo" class="form-label">Título Oficial *</label>
                        <input type="text" class="form-control" id="modalTitulo" name="<?php echo $title_column; ?>" required maxlength="255">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    // Armazena os dados do POST em caso de erro de validação
    const errorData = <?php echo json_encode($error_data); ?>;
    
    // Inicializa o Select2
    $(document).ready(function() {
        $('.select2-modal').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#cadastroModal') // Garante que o dropdown apareça sobre o modal
        });
    });

    const modalElement = document.getElementById('cadastroModal');
    const modalTitle = document.getElementById('modalLabel');
    const modalAction = document.getElementById('modalAction');
    const modalId = document.getElementById('modalId');
    const modalCod = document.getElementById('modalCod');
    const modalTitulo = document.getElementById('modalTitulo');
    const modalFamiliaId = document.getElementById('modalFamiliaId'); // O <select>
    const btnSalvar = document.getElementById('btnSalvar');

    const resetModal = () => {
        modalTitle.textContent = 'Novo Código CBO';
        modalAction.value = 'insert';
        modalId.value = '';
        modalCod.value = '';
        modalTitulo.value = '';
        $('#modalFamiliaId').val(null).trigger('change'); // Reseta o Select2
        
        btnSalvar.textContent = 'Salvar Cadastro';
        document.querySelector('.modal-header').classList.remove('bg-info', 'bg-danger');
        document.querySelector('.modal-header').classList.add('bg-primary');
    };

    // 1. Lógica para abrir o modal no modo INSERIR
    document.getElementById('btnNovoCadastro').addEventListener('click', resetModal);

    // 2. Lógica para abrir o modal no modo EDITAR
    modalElement.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        // Se houver dados de erro, repopula o formulário com eles
        if (Object.keys(errorData).length > 0) {
            modalTitle.textContent = 'Corrija os dados (ID: ' + (errorData.cboId || 'Novo') + ')';
            modalAction.value = errorData.action || 'insert';
            modalId.value = errorData.cboId || '';
            modalCod.value = errorData.cboCod || '';
            modalTitulo.value = errorData.cboTituloOficial || '';
            $('#modalFamiliaId').val(errorData.familiaCboId || null).trigger('change');

            btnSalvar.textContent = 'Salvar Correções';
            document.querySelector('.modal-header').classList.remove('bg-primary', 'bg-info');
            document.querySelector('.modal-header').classList.add('bg-danger');

        } else if (button && button.classList.contains('btn-edit')) {
            // Se for um clique normal de "Editar"
            const id = button.getAttribute('data-id');
            const cod = button.getAttribute('data-cod');
            const titulo = button.getAttribute('data-titulo');
            const familiaId = button.getAttribute('data-familia');
            
            modalTitle.textContent = 'Editar CBO (ID: ' + id + ')';
            modalAction.value = 'update';
            modalId.value = id;
            modalCod.value = cod;
            modalTitulo.value = titulo;
            $('#modalFamiliaId').val(familiaId).trigger('change'); // Define o valor do Select2

            btnSalvar.textContent = 'Atualizar';
            document.querySelector('.modal-header').classList.remove('bg-primary', 'bg-danger');
            document.querySelector('.modal-header').classList.add('bg-info');
        } else {
            // Se abriu de qualquer outra forma (como o botão "Novo")
            resetModal();
        }
    });

    // Se houver dados de erro, abre o modal automaticamente
    if (Object.keys(errorData).length > 0) {
        var modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
</script>

</body>
</html>