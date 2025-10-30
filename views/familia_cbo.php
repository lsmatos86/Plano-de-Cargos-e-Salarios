<?php
// Arquivo: views/familia_cbo.php

// Inclusão de arquivos na pasta superior (../)
require_once '../config.php';
require_once '../includes/functions.php';

// Redireciona para o login se o usuário não estiver autenticado
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Configurações específicas desta tabela
$table_name = 'familia_cbo';
$id_column = 'familiaCboId';
$name_column = 'familiaCboNome';
$page_title = 'Gestão de Famílias CBO';

$pdo = getDbConnection();
$message = '';
$message_type = '';

// ----------------------------------------------------
// LÓGICA DE CRUD (CREATE/UPDATE/DELETE)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $nome = trim($_POST[$name_column] ?? '');
    $id = (int)($_POST[$id_column] ?? 0);
    $action = $_POST['action'];

    if (!empty($nome)) {
        try {
            if ($action === 'insert') {
                if (insertSimpleRecord($pdo, $table_name, $name_column, $nome)) {
                    $message = "Família CBO '{$nome}' cadastrada com sucesso!";
                    $message_type = 'success';
                } else {
                    $message = "Falha ao cadastrar no banco de dados.";
                    $message_type = 'danger';
                }
            } elseif ($action === 'update' && $id > 0) {
                $stmt = $pdo->prepare("UPDATE {$table_name} SET {$name_column} = ? WHERE {$id_column} = ?");
                $stmt->execute([$nome, $id]);
                $message = "Registro ID {$id} atualizado com sucesso para '{$nome}'!";
                $message_type = 'success';
            }
        } catch (Exception $e) {
            $message = "Erro ao processar a ação: " . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = "O nome não pode ser vazio.";
        $message_type = 'warning';
    }
    
    header("Location: familia_cbo.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if (deleteRecord($pdo, $table_name, $id_column, $id)) {
        $message = "Registro ID {$id} excluído com sucesso!";
        $message_type = 'success';
    } else {
        $message = "Erro ao excluir: O registro ID {$id} não foi encontrado ou está vinculado a um CBO (Chave Estrangeira).";
        $message_type = 'danger';
    }

    header("Location: familia_cbo.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// LÓGICA DE LEITURA E FILTRO (READ)
// ----------------------------------------------------
$params = [
    'term' => $_GET['term'] ?? '',
    'order_by' => $_GET['order_by'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC'
];

$registros = getRecords($pdo, $table_name, $id_column, $name_column, $params);

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cadastroModal" id="btnNovoCadastro">
                <i class="fas fa-plus"></i> Inserir Nova Família CBO
            </button>
        </div>
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por Nome da Família CBO" value="<?php echo htmlspecialchars($params['term']); ?>">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                <?php if (!empty($params['term'])): ?>
                    <a href="familia_cbo.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <span class="fw-bold">Registros Encontrados: </span> <?php echo count($registros); ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>
                            <a href="?order_by=<?php echo $id_column; ?>&sort_dir=<?php echo getSortDirection($params['order_by'], $id_column); ?>&term=<?php echo urlencode($params['term']); ?>" class="text-decoration-none text-dark">
                                ID 
                                <?php if ($params['order_by'] === $id_column): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?order_by=<?php echo $name_column; ?>&sort_dir=<?php echo getSortDirection($params['order_by'], $name_column); ?>&term=<?php echo urlencode($params['term']); ?>" class="text-decoration-none text-dark">
                                Nome da Família
                                <?php if ($params['order_by'] === $name_column): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                            </a>
                        </th>
                        <th>Cadastro</th>
                        <th>Atualização</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row[$id_column]); ?></td>
                                <td><?php echo htmlspecialchars($row[$name_column]); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['familiaCboDataCadastro'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['familiaCboDataAtualizacao'])); ?></td>
                                <td>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info text-white btn-edit" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#cadastroModal"
                                        data-id="<?php echo $row[$id_column]; ?>"
                                        data-nome="<?php echo htmlspecialchars($row[$name_column]); ?>"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <a href="familia_cbo.php?action=delete&id=<?php echo $row[$id_column]; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Excluir"
                                       onclick="return confirm('ATENÇÃO: Este registro pode ter CBOs vinculados. Deseja realmente excluir?');">
                                       <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhum registro encontrado. <?php if($params['term']) echo "(Filtro: " . htmlspecialchars($params['term']) . ")"; ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="cadastroModal" tabindex="-1" aria-labelledby="cadastroModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formCadastro">
                <input type="hidden" name="action" id="modalAction" value="insert">
                <input type="hidden" name="<?php echo $id_column; ?>" id="modalId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="cadastroModalLabel">Cadastrar Nova Família CBO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputNome" class="form-label">Nome da Família CBO</label>
                        <input type="text" class="form-control" id="inputNome" name="<?php echo $name_column; ?>" placeholder="Ex: Trabalhadores Agropecuários" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success" id="btnSalvar">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('cadastroModal');
    const modalTitle = document.getElementById('cadastroModalLabel');
    const modalAction = document.getElementById('modalAction');
    const modalId = document.getElementById('modalId');
    const inputNome = document.getElementById('inputNome');
    const btnSalvar = document.getElementById('btnSalvar');

    // Função para resetar o modal para Inserção
    const resetModal = () => {
        modalTitle.textContent = 'Cadastrar Nova Família CBO';
        modalAction.value = 'insert';
        modalId.value = '';
        inputNome.value = ''; // Limpa o campo
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
            
            // Preenche os campos para Edição
            modalTitle.textContent = 'Editar Família CBO (ID: ' + id + ')';
            modalAction.value = 'update';
            modalId.value = id;
            inputNome.value = nome;
            btnSalvar.textContent = 'Atualizar';

            // Altera a cor do modal para sinalizar o modo Edição
            document.querySelector('.modal-header').classList.remove('bg-primary');
            document.querySelector('.modal-header').classList.add('bg-info');
        } else {
            // Se o modal for aberto sem ser pelo botão de editar, reseta
            resetModal();
        }
    });
});
</script>
</body>
</html>