<?php
// Arquivo: views/cbos.php

// Inclusão de arquivos na pasta superior (../)
require_once '../config.php';
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Configurações específicas desta tabela
$table_name = 'cbos';
$id_column = 'cboId';
$name_column = 'cboNome';
$fk_column = 'familiaCboId';
$page_title = 'Gestão de Códigos CBO';

$pdo = getDbConnection();
$message = '';
$message_type = '';

// 1. CARREGAR DADOS FK: Famílias CBO para o SELECT
$familias_cbo = getLookupData($pdo, 'familia_cbo', 'familiaCboId', 'familiaCboNome');


// ----------------------------------------------------
// 2. LÓGICA DE CRUD (CREATE/UPDATE/DELETE)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $nome = trim($_POST[$name_column] ?? '');
    $id = trim($_POST[$id_column] ?? 0); // CBO é o código INT, é a PK
    $familiaId = (int)($_POST[$fk_column] ?? 0);
    $action = $_POST['action'];

    // Validação
    if (empty($nome) || empty($id) || $familiaId === 0) {
        $message = "Todos os campos (Código CBO, Nome e Família) são obrigatórios.";
        $message_type = 'warning';
    } elseif (!is_numeric($id) || strlen($id) > 6) {
        $message = "O Código CBO deve ser numérico e ter no máximo 6 dígitos (Ex: 620110).";
        $message_type = 'warning';
    } else {
        try {
            if ($action === 'insert') {
                // Lógica de Inserção
                $stmt = $pdo->prepare("INSERT INTO {$table_name} ({$id_column}, {$name_column}, {$fk_column}) VALUES (?, ?, ?)");
                $stmt->execute([$id, $nome, $familiaId]);
                
                $message = "CBO '{$nome}' cadastrado com sucesso!";
                $message_type = 'success';
                
            } elseif ($action === 'update' && $id > 0) {
                // Lógica de Atualização (UPDATE). A PK (cboId) não deve ser alterada.
                $stmt = $pdo->prepare("UPDATE {$table_name} SET {$name_column} = ?, {$fk_column} = ? WHERE {$id_column} = ?");
                $stmt->execute([$nome, $familiaId, $id]);

                $message = "CBO ID {$id} atualizado com sucesso!";
                $message_type = 'success';
            }
        } catch (Exception $e) {
            // Pode falhar por UNIQUE KEY (CBO ID já existe) ou FOREIGN KEY (Família ID não existe)
            $message = "Erro ao processar a ação: O código CBO já existe ou a Família é inválida.";
            $message_type = 'danger';
        }
    }
    
    // Redireciona
    header("Location: cbos.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 3. LÓGICA DE EXCLUSÃO (DELETE - Via GET)
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = trim($_GET['id']);
    
    if (deleteRecord($pdo, $table_name, $id_column, $id)) {
        $message = "Registro CBO {$id} excluído com sucesso!";
        $message_type = 'success';
    } else {
        $message = "Erro ao excluir: O CBO está vinculado a um Cargo.";
        $message_type = 'danger';
    }

    header("Location: cbos.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 4. LÓGICA DE LEITURA E FILTRO (READ All)
// ----------------------------------------------------
$params = [
    'term' => $_GET['term'] ?? '',
    'order_by' => $_GET['order_by'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC'
];

// Query mais complexa para juntar com o nome da Família CBO
$sql = "
    SELECT 
        t.*, 
        f.familiaCboNome 
    FROM {$table_name} t
    JOIN familia_cbo f ON f.familiaCboId = t.familiaCboId
";
$bindings = [];

if (!empty($params['term'])) {
    $sql .= " WHERE t.{$name_column} LIKE ? OR t.{$id_column} LIKE ?";
    $bindings[] = "%{$params['term']}%";
    $bindings[] = "%{$params['term']}%";
}

$validColumns = [$id_column, $name_column, 'familiaCboNome', $id_column.'DataCadastro', $id_column.'DataAtualizacao'];
$orderBy = in_array($params['order_by'], $validColumns) ? $params['order_by'] : $id_column;
$sortDir = in_array(strtoupper($params['sort_dir']), ['ASC', 'DESC']) ? $params['sort_dir'] : 'ASC';

$sql .= " ORDER BY {$orderBy} {$sortDir}";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindings);
    $registros = $stmt->fetchAll();
} catch (\PDOException $e) {
    $registros = [];
    $message = "Erro ao carregar dados: " . $e->getMessage();
    $message_type = 'danger';
}

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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
                <i class="fas fa-plus"></i> Inserir Novo CBO
            </button>
        </div>
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por Nome ou Código CBO" value="<?php echo htmlspecialchars($params['term']); ?>">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                <?php if (!empty($params['term'])): ?>
                    <a href="cbos.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
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
                                Código CBO
                                <?php if ($params['order_by'] === $id_column): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?order_by=<?php echo $name_column; ?>&sort_dir=<?php echo getSortDirection($params['order_by'], $name_column); ?>&term=<?php echo urlencode($params['term']); ?>" class="text-decoration-none text-dark">
                                Nome da Ocupação
                                <?php if ($params['order_by'] === $name_column): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                            </a>
                        </th>
                         <th>
                            <a href="?order_by=familiaCboNome&sort_dir=<?php echo getSortDirection($params['order_by'], 'familiaCboNome'); ?>&term=<?php echo urlencode($params['term']); ?>" class="text-decoration-none text-dark">
                                Família CBO
                                <?php if ($params['order_by'] === 'familiaCboNome'): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                            </a>
                        </th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row[$id_column]); ?></td>
                                <td><?php echo htmlspecialchars($row[$name_column]); ?></td>
                                <td><?php echo htmlspecialchars($row['familiaCboNome']); ?></td>
                                <td>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info text-white btn-edit" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#cadastroModal"
                                        data-id="<?php echo htmlspecialchars($row[$id_column]); ?>"
                                        data-nome="<?php echo htmlspecialchars($row[$name_column]); ?>"
                                        data-familiaid="<?php echo htmlspecialchars($row[$fk_column]); ?>"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <a href="cbos.php?action=delete&id=<?php echo htmlspecialchars($row[$id_column]); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Excluir"
                                       onclick="return confirm('ATENÇÃO: Este CBO está vinculado a Cargos. Deseja realmente excluir?');">
                                       <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum registro encontrado. <?php if($params['term']) echo "(Filtro: " . htmlspecialchars($params['term']) . ")"; ?></td>
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
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="cadastroModalLabel">Cadastrar Novo CBO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputId" class="form-label">Código CBO (PK)</label>
                        <input type="number" class="form-control" id="inputId" name="<?php echo $id_column; ?>" placeholder="Ex: 620110" required>
                        <small class="form-text text-muted">Este é o código único e não pode ser alterado após o cadastro.</small>
                    </div>
                    <div class="mb-3">
                        <label for="inputNome" class="form-label">Nome da Ocupação</label>
                        <input type="text" class="form-control" id="inputNome" name="<?php echo $name_column; ?>" placeholder="Ex: Coordenador de Colheita" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputFamiliaId" class="form-label">Família CBO</label>
                        <select class="form-select" id="inputFamiliaId" name="<?php echo $fk_column; ?>" required data-placeholder="Selecione a Família CBO...">
                             <option value=""></option>
                            <?php foreach ($familias_cbo as $id => $nome): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>">
                                    <?php echo htmlspecialchars($nome); ?>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
    const modalElement = document.getElementById('cadastroModal');
    const modalTitle = document.getElementById('cadastroModalLabel');
    const modalAction = document.getElementById('modalAction');
    const inputId = document.getElementById('inputId');
    const inputNome = document.getElementById('inputNome');
    const inputFamiliaId = document.getElementById('inputFamiliaId');
    const btnSalvar = document.getElementById('btnSalvar');

    // Inicializa Select2 para o campo Família CBO
    $('#inputFamiliaId').select2({
        theme: "bootstrap-5",
        width: '100%',
        placeholder: "Selecione a Família CBO...",
        dropdownParent: $('#cadastroModal'),
        allowClear: true
    });

    // Função para resetar o modal para Inserção
    const resetModal = () => {
        modalTitle.textContent = 'Cadastrar Novo CBO';
        modalAction.value = 'insert';
        inputId.value = '';
        inputNome.value = '';
        inputId.readOnly = false; // Permite edição do ID na inserção
        $('#inputFamiliaId').val(null).trigger('change'); // Reseta Select2
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
            const familiaid = button.getAttribute('data-familiaid');
            
            // Preenche os campos para Edição
            modalTitle.textContent = 'Editar CBO (Código: ' + id + ')';
            modalAction.value = 'update';
            inputId.value = id;
            inputNome.value = nome;
            inputId.readOnly = true; // Bloqueia edição do ID (PK)
            btnSalvar.textContent = 'Atualizar';

            // Define o valor do Select2
            $('#inputFamiliaId').val(familiaid).trigger('change');

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