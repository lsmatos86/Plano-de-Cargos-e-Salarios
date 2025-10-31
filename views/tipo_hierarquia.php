<?php
// Arquivo: views/tipo_hierarquia.php (REFATORADO)

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // (Ainda necessário para isUserLoggedIn e getSortDirection)

// 2. Importa o novo Repositório
use App\Repository\TipoHierarquiaRepository;

// Redireciona para o login se o usuário não estiver autenticado
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Configurações específicas desta tabela
$id_column = 'tipoId';
$name_column = 'tipoNome';
$description_column = 'tipoDescricao'; // Corrigido
$creation_date_column = 'tipoDataCadastro';
$date_update_column = 'tipoDataAtualizacao';
$page_title = 'Gerenciamento de Tipos de Hierarquia';

$message = '';
$message_type = '';

// Instancia o Repositório
$repo = new TipoHierarquiaRepository();

// ----------------------------------------------------
// LÓGICA DE CRUD (CREATE/UPDATE/DELETE) - REFATORADO
// ----------------------------------------------------
try {
    // 1. Lógica de CREATE/UPDATE (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST[$name_column] ?? '');
        
        // O repositório lida com insert/update e validação
        $repo->save($_POST); 
        
        $id = (int)($_POST[$id_column] ?? 0);
        $action_desc = ($id > 0) ? 'atualizado' : 'cadastrado';
        $message = "Tipo de Hierarquia '{$nome}' {$action_desc} com sucesso!";
        $message_type = 'success';
    }

    // 2. Lógica de DELETE (GET)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $deleted = $repo->delete($id);
        
        if ($deleted) {
            $message = "Tipo de Hierarquia ID {$id} excluído com sucesso!";
            $message_type = 'success';
        } else {
            $message = "Erro: Tipo de Hierarquia ID {$id} não encontrado ou já excluído.";
            $message_type = 'danger';
        }
        
        // Redireciona para limpar a URL após a ação
        header("Location: tipo_hierarquia.php?message=" . urlencode($message) . "&type={$message_type}");
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

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0" id="formTitle"><i class="fas fa-plus"></i> Novo Tipo</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="tipo_hierarquia.php" id="cadastroForm">
                        <input type="hidden" name="<?php echo $id_column; ?>" id="formId" value="">
                        
                        <div class="mb-3">
                            <label for="formNome" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="formNome" name="<?php echo $name_column; ?>" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="formDescricao" class="form-label">Descrição (Opcional)</label>
                            <textarea class="form-control" id="formDescricao" name="<?php echo $description_column; ?>" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" id="btnSalvar">
                            <i class="fas fa-check"></i> Salvar
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100 mt-2" id="btnLimpar" onclick="resetForm()">
                            Limpar Formulário
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <form method="GET" class="d-flex mb-3">
                <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por nome ou descrição..." value="<?php echo htmlspecialchars($params['term']); ?>">
                <input type="hidden" name="order_by" value="<?php echo htmlspecialchars($params['order_by']); ?>">
                <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
                
                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($params['term'])): ?>
                    <a href="tipo_hierarquia.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <span class="fw-bold">Registros Encontrados: </span> <?php echo $totalRecords; ?> (Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>)
                </div>
                <div class="card-body p-0">
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
                                <th><?php echo createSortLink($id_column, 'ID', $params); ?></th>
                                <th><?php echo createSortLink($name_column, 'Nome', $params); ?></th>
                                <th><?php echo createSortLink($description_column, 'Descrição', $params); ?></th>
                                <th width="120px" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registros) > 0): ?>
                                <?php foreach ($registros as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row[$id_column]); ?></td>
                                        <td><strong><?php echo htmlspecialchars($row[$name_column]); ?></strong></td>
                                        <td class="short-text" title="<?php echo htmlspecialchars($row[$description_column]); ?>">
                                            <?php echo htmlspecialchars($row[$description_column]); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="btn btn-sm btn-info text-white btn-edit"
                                                data-id="<?php echo $row[$id_column]; ?>"
                                                data-nome="<?php echo htmlspecialchars($row[$name_column]); ?>"
                                                data-descricao="<?php echo htmlspecialchars($row[$description_column]); ?>"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="tipo_hierarquia.php?action=delete&id=<?php echo $row[$id_column]; ?>" 
                                                class="btn btn-sm btn-danger" 
                                                title="Excluir"
                                                onclick="return confirm('ATENÇÃO: A exclusão desta categoria pode afetar os Níveis Hierárquicos. Deseja realmente excluir?');">
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const formTitle = document.getElementById('formTitle');
    const formId = document.getElementById('formId');
    const formNome = document.getElementById('formNome');
    const formDescricao = document.getElementById('formDescricao');
    const btnSalvar = document.getElementById('btnSalvar');
    const form = document.getElementById('cadastroForm');
    const header = document.querySelector('.card-header');

    function resetForm() {
        formTitle.innerHTML = '<i class="fas fa-plus"></i> Novo Tipo';
        formId.value = '';
        formNome.value = '';
        formDescricao.value = '';
        btnSalvar.innerHTML = '<i class="fas fa-check"></i> Salvar';
        btnSalvar.classList.remove('btn-info');
        btnSalvar.classList.add('btn-primary');
        header.classList.remove('bg-info');
        header.classList.add('bg-primary');
        form.action = 'tipo_hierarquia.php';
    }

    // Adiciona listener para todos os botões de edição
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); 
            
            const id = this.getAttribute('data-id');
            const nome = this.getAttribute('data-nome');
            const descricao = this.getAttribute('data-descricao');

            formTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Tipo (ID: ' + id + ')';
            formId.value = id;
            formNome.value = nome;
            formDescricao.value = descricao;
            btnSalvar.innerHTML = '<i class="fas fa-check"></i> Atualizar';
            btnSalvar.classList.remove('btn-primary');
            btnSalvar.classList.add('btn-info');
            header.classList.remove('bg-primary');
            header.classList.add('bg-info');
            
            // Foca no campo de nome
            formNome.focus();
        });
    });
</script>

</body>
</html>