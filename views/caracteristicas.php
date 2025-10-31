<?php
// Arquivo: views/caracteristicas.php (REFATORADO)

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // (Ainda necessário para isUserLoggedIn e getSortDirection)

// 2. Importa o novo Repositório
use App\Repository\CaracteristicaRepository;

// Redireciona para o login se o usuário não estiver autenticado
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Configurações específicas desta tabela
$id_column = 'caracteristicaId';
$name_column = 'caracteristicaNome';
$desc_column = 'caracteristicaDescricao';
$page_title = 'Gestão de Características Pessoais';

$message = '';
$message_type = '';

// Instancia o Repositório
$repo = new CaracteristicaRepository();

// ----------------------------------------------------
// LÓGICA DE CRUD (CREATE/UPDATE/DELETE) - REFATORADO
// ----------------------------------------------------
try {
    // 1. Lógica de CREATE/UPDATE (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $nome = trim($_POST[$name_column] ?? '');
        
        $repo->save($_POST); // O repositório lida com insert/update e validação
        
        $action_desc = ($_POST['action'] === 'insert') ? 'cadastrada' : 'atualizada';
        $message = "Característica '{$nome}' {$action_desc} com sucesso!";
        $message_type = 'success';
    }

    // 2. Lógica de DELETE (GET)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $deleted = $repo->delete($id);
        
        if ($deleted) {
            $message = "Característica ID {$id} excluída com sucesso!";
            $message_type = 'success';
        } else {
            $message = "Erro: Característica ID {$id} não encontrada ou já excluída.";
            $message_type = 'danger';
        }
        
        // Redireciona para limpar a URL após a ação
        header("Location: caracteristicas.php?message=" . urlencode($message) . "&type={$message_type}");
        exit;
    }

} catch (Exception $e) {
    // Captura qualquer exceção do Repositório (validação, FK, DB)
    $message = $e->getMessage();
    $message_type = 'danger';
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
    'order_by' => $_GET['order_by'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC',
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
    <style>
        .short-text { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
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
                <i class="fas fa-plus"></i> Nova Característica
            </button>
        </div>
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por nome ou descrição..." value="<?php echo htmlspecialchars($params['term']); ?>">
                <input type="hidden" name="order_by" value="<?php echo htmlspecialchars($params['order_by']); ?>">
                <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
                
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                <?php if (!empty($params['term'])): ?>
                    <a href="caracteristicas.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i> Limpar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <span class="fw-bold">Registros Encontrados: </span> <?php echo $totalRecords; ?> (Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>)
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <?php 
                        // Esta função ainda está em functions.php
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
                        <th><?php echo createSortLink($id_column, 'ID', $params); ?></th>
                        <th><?php echo createSortLink($name_column, 'Nome', $params); ?></th>
                        <th><?php echo createSortLink($desc_column, 'Descrição', $params); ?></th>
                        <th width="150px" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row[$id_column]); ?></td>
                                <td><strong><?php echo htmlspecialchars($row[$name_column]); ?></strong></td>
                                <td class="short-text" title="<?php echo htmlspecialchars($row[$desc_column]); ?>">
                                    <?php echo htmlspecialchars($row[$desc_column]); ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white btn-edit" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#cadastroModal"
                                            data-id="<?php echo $row[$id_column]; ?>"
                                            data-nome="<?php echo htmlspecialchars($row[$name_column]); ?>"
                                            data-descricao="<?php echo htmlspecialchars($row[$desc_column]); ?>"
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <a href="caracteristicas.php?action=delete&id=<?php echo $row[$id_column]; ?>" 
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
                            <td colspan="4" class="text-center">Nenhum registro encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

<div class="modal fade" id="cadastroModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLabel">Cadastrar Nova Característica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="modalAction" value="insert">
                    <input type="hidden" name="<?php echo $id_column; ?>" id="modalId" value="">

                    <div class="mb-3">
                        <label for="modalNome" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="modalNome" name="<?php echo $name_column; ?>" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="modalDescricao" class="form-label">Descrição (Opcional)</label>
                        <textarea class="form-control" id="modalDescricao" name="<?php echo $desc_column; ?>" rows="3"></textarea>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const modalElement = document.getElementById('cadastroModal');
    const modalTitle = document.getElementById('modalLabel');
    const modalAction = document.getElementById('modalAction');
    const modalId = document.getElementById('modalId');
    const inputNome = document.getElementById('modalNome');
    const inputDescricao = document.getElementById('modalDescricao');
    const btnSalvar = document.getElementById('btnSalvar');

    const resetModal = () => {
        modalTitle.textContent = 'Cadastrar Nova Característica';
        modalAction.value = 'insert';
        modalId.value = '';
        inputNome.value = '';
        inputDescricao.value = '';
        btnSalvar.textContent = 'Salvar Cadastro';
        document.querySelector('.modal-header').classList.remove('bg-info');
        document.querySelector('.modal-header').classList.add('bg-primary');
    };

    // 1. Lógica para abrir o modal no modo INSERIR
    document.getElementById('btnNovoCadastro').addEventListener('click', resetModal);

    // 2. Lógica para abrir o modal no modo EDITAR
    modalElement.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (button && button.classList.contains('btn-edit')) {
            const id = button.getAttribute('data-id');
            const nome = button.getAttribute('data-nome');
            const descricao = button.getAttribute('data-descricao');
            
            // Preenche os campos para Edição
            modalTitle.textContent = 'Editar Característica (ID: ' + id + ')';
            modalAction.value = 'update';
            modalId.value = id;
            inputNome.value = nome;
            inputDescricao.value = descricao; 
            btnSalvar.textContent = 'Atualizar';

            // Altera a cor do modal para sinalizar o modo Edição
            document.querySelector('.modal-header').classList.remove('bg-primary');
            document.querySelector('.modal-header').classList.add('bg-info');
        } else {
            // Se o modal for aberto sem ser pelo botão de editar, reseta
            resetModal();
        }
    });
</script>

</body>
</html>