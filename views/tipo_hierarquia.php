<?php
// Arquivo: views/tipo_hierarquia.php (Gerenciamento de Tipos de Hierarquia)

require_once '../config.php';
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Gerenciamento de Tipos de Hierarquia';
$pdo = getDbConnection();
$message = '';
$message_type = '';
$table_name = 'tipo_hierarquia';
$id_column = 'tipoId';
$name_column = 'tipoNome';

// ----------------------------------------------------
// 1. LÓGICA DE CADASTRO/EDIÇÃO (CREATE/UPDATE)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST[$name_column] ?? '');
    $id = (int)($_POST[$id_column] ?? 0);

    if (empty($nome)) {
        $message = "O nome do Tipo de Hierarquia é obrigatório.";
        $message_type = 'danger';
    } else {
        try {
            if ($id > 0) {
                // UPDATE
                $sql = "UPDATE {$table_name} SET {$name_column} = ? WHERE {$id_column} = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $id]);
                $message = "Tipo de Hierarquia atualizado com sucesso!";
            } else {
                // CREATE - Utilizando a função genérica que usa Prepared Statements
                $success = insertSimpleRecord($pdo, $table_name, $name_column, $nome);
                
                if ($success) {
                    $message = "Novo Tipo de Hierarquia cadastrado com sucesso!";
                } else {
                     // Esta mensagem capturará o erro de chave duplicada
                     throw new PDOException("Falha ao inserir o registro.");
                }
            }
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = "Erro ao salvar: O nome do Tipo de Hierarquia pode já existir. " . $e->getMessage();
            $message_type = 'danger';
        }
    }
    // Redireciona para evitar re-submissão do formulário
    header("Location: tipo_hierarquia.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 2. LÓGICA DE EXCLUSÃO (DELETE)
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Utiliza a função de exclusão segura
    $deleted = deleteRecord($pdo, $table_name, $id_column, $id);
    
    if ($deleted) {
        $message = "Tipo de Hierarquia ID {$id} excluído com sucesso!";
        $message_type = 'success';
    } else {
        // Geralmente falha por FK, pois nível_hierarquico depende desta tabela
        $message = "Erro ao excluir: O registro ID {$id} não foi encontrado ou está sendo usado por um Nível Hierárquico.";
        $message_type = 'danger';
    }

    header("Location: tipo_hierarquia.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 3. LÓGICA DE LEITURA E FILTRO (READ All)
// ----------------------------------------------------
$params = [
    'term' => $_GET['term'] ?? '',
    'order_by' => $_GET['order_by'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC'
];

$sql = "SELECT * FROM {$table_name}";
$bindings = [];

if (!empty($params['term'])) {
    $sql .= " WHERE {$name_column} LIKE ?";
    $bindings[] = "%{$params['term']}%";
}

$validColumns = [$id_column, $name_column, $id_column.'DataCadastro', $id_column.'DataAtualizacao'];
$orderBy = in_array($params['order_by'], $validColumns) ? $params['order_by'] : $name_column;
$sortDir = in_array(strtoupper($params['sort_dir']), ['ASC', 'DESC']) ? $params['sort_dir'] : 'ASC';

$sql .= " ORDER BY {$orderBy} {$sortDir}";

$stmt = $pdo->prepare($sql);
$stmt->execute($bindings);
$registros = $stmt->fetchAll();

// 4. Carrega dados para o formulário de edição
$tipoToEdit = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM {$table_name} WHERE {$id_column} = ?");
    $stmt->execute([$_GET['id']]);
    $tipoToEdit = $stmt->fetch();
}

// Mensagens após redirecionamento
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
    <style>
        .short-text { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
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
                    <?php echo $tipoToEdit ? 'Editar: ' . htmlspecialchars($tipoToEdit[$name_column]) : 'Novo Tipo de Hierarquia'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="tipo_hierarquia.php">
                        <input type="hidden" name="<?php echo $id_column; ?>" value="<?php echo htmlspecialchars($tipoToEdit[$id_column] ?? 0); ?>">

                        <div class="mb-3">
                            <label for="<?php echo $name_column; ?>" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="<?php echo $name_column; ?>" name="<?php echo $name_column; ?>" value="<?php echo htmlspecialchars($tipoToEdit[$name_column] ?? ''); ?>" required>
                            <div class="form-text">Ex: Estratégico, Tático, Operacional.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> <?php echo $tipoToEdit ? 'Salvar Alterações' : 'Cadastrar'; ?>
                        </button>
                        <?php if ($tipoToEdit): ?>
                             <a href="tipo_hierarquia.php" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="fas fa-plus"></i> Novo Cadastro
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <span class="fw-bold">Registros Encontrados: </span> <?php echo count($registros); ?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th><i class="fas fa-calendar-alt"></i> Atualização</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registros) > 0): ?>
                                <?php foreach ($registros as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row[$id_column]); ?></td>
                                        <td><strong class="text-primary"><?php echo htmlspecialchars($row[$name_column]); ?></strong></td>
                                        <td><?php echo (new DateTime($row[$id_column.'DataAtualizacao']))->format('d/m/Y H:i'); ?></td>
                                        <td class="text-center">
                                            <a href="tipo_hierarquia.php?id=<?php echo $row[$id_column]; ?>" 
                                                class="btn btn-sm btn-info text-white" 
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
                                <tr>
                                    <td colspan="4" class="text-center">Nenhum registro encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>