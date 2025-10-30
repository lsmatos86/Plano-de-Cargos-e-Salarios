<?php
// Arquivo: views/riscos.php

// Inclusão de arquivos na pasta superior (../)
require_once '../config.php';
require_once '../includes/functions.php';

// Redireciona para o login se o usuário não estiver autenticado
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Configurações específicas desta tabela
$table_name = 'riscos';
$id_column = 'riscoId';
$name_column = 'riscoNome'; // Coluna ENUM
$page_title = 'Gestão de Tipos de Riscos';

$pdo = getDbConnection();
$message = '';
$message_type = '';

// 1. Obtém as opções ENUM do banco para usar no formulário SELECT
$enum_options = getEnumOptions($pdo, $table_name, $name_column);


// ----------------------------------------------------
// LÓGICA DE CRUD (CREATE/UPDATE/DELETE)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $riscoNome = trim($_POST[$name_column] ?? ''); // Pega o valor ENUM selecionado
    $id = (int)($_POST[$id_column] ?? 0);
    $action = $_POST['action'];

    // Garante que o valor enviado seja um valor ENUM válido
    if (!in_array($riscoNome, $enum_options)) {
        $message = "Valor de Risco inválido ou vazio.";
        $message_type = 'warning';
    } else {
        try {
            if ($action === 'insert') {
                if (insertSimpleRecord($pdo, $table_name, $name_column, $riscoNome)) {
                    $message = "Tipo de Risco '{$riscoNome}' cadastrado com sucesso!";
                    $message_type = 'success';
                }
            } elseif ($action === 'update' && $id > 0) {
                $stmt = $pdo->prepare("UPDATE {$table_name} SET {$name_column} = ? WHERE {$id_column} = ?");
                $stmt->execute([$riscoNome, $id]);
                $message = "Registro ID {$id} atualizado com sucesso para '{$riscoNome}'!";
                $message_type = 'success';
            }
        } catch (Exception $e) {
            $message = "Erro ao processar a ação: Este risco já existe ou houve falha na query.";
            $message_type = 'danger';
        }
    }
    
    header("Location: riscos.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if (deleteRecord($pdo, $table_name, $id_column, $id)) {
        $message = "Registro ID {$id} excluído com sucesso!";
        $message_type = 'success';
    } else {
        $message = "Erro ao excluir: O registro ID {$id} não foi encontrado ou está vinculado à tabela Riscos_Cargo.";
        $message_type = 'danger';
    }

    header("Location: riscos.php?message=" . urlencode($message) . "&type={$message_type}");
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
                <i class="fas fa-plus"></i> Inserir Novo Risco
            </button>
        </div>
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por Risco (Físico, Ergonômico, etc.)" value="<?php echo htmlspecialchars($params['term']); ?>">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                <?php if (!empty($params['term'])): ?>
                    <a href="riscos.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
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
                        <th><a href="?order_by=<?php echo $id_column; ?>&sort_dir=<?php echo getSortDirection($params['order_by'], $id_column); ?>&term=<?php echo urlencode($params['term']); ?>" class="text-decoration-none text-dark">ID 
                            <?php if ($params['order_by'] === $id_column): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                        </a></th>
                        <th><a href="?order_by=<?php echo $name_column; ?>&sort_dir=<?php echo getSortDirection($params['order_by'], $name_column); ?>&term=<?php echo urlencode($params['term']); ?>" class="text-decoration-none text-dark">Tipo de Risco
                            <?php if ($params['order_by'] === $name_column): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                        </a></th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row[$id_column]); ?></td>
                                <td><?php echo htmlspecialchars($row[$name_column]); ?></td>
                                <td>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info text-white btn-edit" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#cadastroModal"
                                        data-id="<?php echo $row[$id_column]; ?>"
                                        data-risco="<?php echo htmlspecialchars($row[$name_column]); ?>"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <a href="riscos.php?action=delete&id=<?php echo $row[$id_column]; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Excluir"
                                       onclick="return confirm('ATENÇÃO: Este risco pode ter Cargos vinculados. Deseja realmente excluir?');">
                                       <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">Nenhum registro encontrado.</td>
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
                    <h5 class="modal-title" id="cadastroModalLabel">Cadastrar Novo Tipo de Risco</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputRiscoNome" class="form-label">Tipo de Risco</label>
                        <select class="form-select" id="inputRiscoNome" name="<?php echo $name_column; ?>" required>
                            <option value="" disabled selected>Selecione o Tipo de Risco</option>
                            <?php foreach ($enum_options as $option): ?>
                                <option value="<?php echo htmlspecialchars($option); ?>">
                                    <?php echo htmlspecialchars($option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
    const inputRiscoNome = document.getElementById('inputRiscoNome');
    const btnSalvar = document.getElementById('btnSalvar');

    // Função para resetar o modal para Inserção
    const resetModal = () => {
        modalTitle.textContent = 'Cadastrar Novo Tipo de Risco';
        modalAction.value = 'insert';
        modalId.value = '';
        inputRiscoNome.selectedIndex = 0; // Reseta a seleção
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
            const risco = button.getAttribute('data-risco');
            
            // Preenche os campos para Edição
            modalTitle.textContent = 'Editar Risco (ID: ' + id + ')';
            modalAction.value = 'update';
            modalId.value = id;
            inputRiscoNome.value = risco; // Define o valor selecionado do SELECT
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