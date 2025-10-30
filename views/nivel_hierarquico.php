<?php
// Arquivo: views/nivel_hierarquico.php (Gerenciamento de Níveis Hierárquicos)

require_once '../config.php';
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Gerenciamento de Níveis Hierárquicos';
$pdo = getDbConnection();
$message = '';
$message_type = '';
$table_name = 'nivel_hierarquico';
$id_column = 'nivelId';
$name_column = 'nivelOrdem'; // Coluna principal para ordenação/busca

// Carrega os Tipos de Hierarquia para o SELECT do formulário
$tiposHierarquia = getLookupData($pdo, 'tipo_hierarquia', 'tipoId', 'tipoNome');

// ----------------------------------------------------
// 1. LÓGICA DE CADASTRO/EDIÇÃO (CREATE/UPDATE)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ordem = (int)($_POST['nivelOrdem'] ?? 0);
    $tipoId = (int)($_POST['tipoId'] ?? 0);
    $descricao = trim($_POST['nivelDescricao'] ?? '');
    $id = (int)($_POST[$id_column] ?? 0);

    if ($ordem <= 0 || $tipoId <= 0) {
        $message = "A Ordem e o Tipo de Hierarquia são obrigatórios.";
        $message_type = 'danger';
    } else {
        try {
            if ($id > 0) {
                // UPDATE
                $sql = "UPDATE {$table_name} SET nivelOrdem = ?, tipoId = ?, nivelDescricao = ? WHERE {$id_column} = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$ordem, $tipoId, $descricao, $id]);
                $message = "Nível Hierárquico atualizado com sucesso!";
            } else {
                // CREATE
                $sql = "INSERT INTO {$table_name} (nivelOrdem, tipoId, nivelDescricao) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$ordem, $tipoId, $descricao]);
                $message = "Novo Nível Hierárquico cadastrado com sucesso!";
            }
            $message_type = 'success';
        } catch (PDOException $e) {
            // Este catch pega erros de chave duplicada (nivelOrdem é UNIQUE) ou FK inválida
            $message = "Erro ao salvar: A Ordem Hierárquica pode já estar em uso (deve ser única). " . $e->getMessage();
            $message_type = 'danger';
        }
    }
    // Redireciona para evitar re-submissão do formulário
    header("Location: nivel_hierarquico.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 2. LÓGICA DE EXCLUSÃO (DELETE)
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Utiliza a função de exclusão segura. Cargos relacionados terão o campo SET NULL
    $deleted = deleteRecord($pdo, $table_name, $id_column, $id);
    
    if ($deleted) {
        $message = "Nível Hierárquico ID {$id} excluído com sucesso!";
        $message_type = 'success';
    } else {
        $message = "Erro ao excluir: O registro ID {$id} não foi encontrado. Verifique se há cargos vinculados antes de excluir.";
        $message_type = 'danger';
    }

    header("Location: nivel_hierarquico.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 3. LÓGICA DE LEITURA E FILTRO (READ All)
// ----------------------------------------------------
$params = [
    'term' => $_GET['term'] ?? '',
    'order_by' => $_GET['order_by'] ?? 'n.nivelOrdem',
    'sort_dir' => $_GET['sort_dir'] ?? 'DESC' // Ordem decrescente é mais intuitiva para hierarquia
];

// Query com JOIN para exibir o nome do Tipo de Hierarquia
$sql = "
    SELECT 
        n.*, 
        t.tipoNome
    FROM {$table_name} n
    JOIN tipo_hierarquia t ON t.tipoId = n.tipoId
";
$bindings = [];

// O filtro de busca (term) será aplicado na Descrição ou no TipoNome
if (!empty($params['term'])) {
    $sql .= " WHERE n.nivelDescricao LIKE ? OR t.tipoNome LIKE ?";
    $bindings[] = "%{$params['term']}%";
    $bindings[] = "%{$params['term']}%";
}

$validColumns = ['n.nivelId', 'n.nivelOrdem', 't.tipoNome', 'n.nivelDataAtualizacao'];
$orderBy = in_array($params['order_by'], $validColumns) ? $params['order_by'] : 'n.nivelOrdem';
$sortDir = in_array(strtoupper($params['sort_dir']), ['ASC', 'DESC']) ? $params['sort_dir'] : 'DESC';

$sql .= " ORDER BY {$orderBy} {$sortDir}";

$stmt = $pdo->prepare($sql);
$stmt->execute($bindings);
$registros = $stmt->fetchAll();

// 4. Carrega dados para o formulário de edição
$nivelToEdit = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM {$table_name} WHERE {$id_column} = ?");
    $stmt->execute([$_GET['id']]);
    $nivelToEdit = $stmt->fetch();
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
                    <?php echo $nivelToEdit ? 'Editar Nível: ' . htmlspecialchars($nivelToEdit['nivelOrdem']) : 'Novo Nível Hierárquico'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="nivel_hierarquico.php">
                        <input type="hidden" name="<?php echo $id_column; ?>" value="<?php echo htmlspecialchars($nivelToEdit[$id_column] ?? 0); ?>">

                        <div class="mb-3">
                            <label for="nivelOrdem" class="form-label">Ordem (Valor Numérico)</label>
                            <input type="number" class="form-control" id="nivelOrdem" name="nivelOrdem" min="1" value="<?php echo htmlspecialchars($nivelToEdit['nivelOrdem'] ?? ''); ?>" required>
                            <div class="form-text">Ex: 7 (Diretor), 6 (Gerente), 1 (Auxiliar). Deve ser um valor único.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipoId" class="form-label">Tipo de Hierarquia *</label>
                            <select class="form-select" id="tipoId" name="tipoId" required>
                                <option value="">--- Selecione um Tipo ---</option>
                                <?php foreach ($tiposHierarquia as $id => $nome): ?>
                                    <option value="<?php echo $id; ?>" 
                                            <?php echo (int)($nivelToEdit['tipoId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($nome); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text"><a href="tipo_hierarquia.php" target="_blank">Gerenciar Tipos de Hierarquia</a></div>
                        </div>

                        <div class="mb-3">
                            <label for="nivelDescricao" class="form-label">Descrição (Opcional)</label>
                            <textarea class="form-control" id="nivelDescricao" name="nivelDescricao" rows="2"><?php echo htmlspecialchars($nivelToEdit['nivelDescricao'] ?? ''); ?></textarea>
                            <div class="form-text">Ex: Direção Geral, Gestão Intermediária.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> <?php echo $nivelToEdit ? 'Salvar Alterações' : 'Cadastrar Nível'; ?>
                        </button>
                        <?php if ($nivelToEdit): ?>
                             <a href="nivel_hierarquico.php" class="btn btn-outline-secondary w-100 mt-2">
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
                    <span class="fw-bold">Níveis Cadastrados: </span> <?php echo count($registros); ?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Ordem</th>
                                <th>Tipo de Hierarquia</th>
                                <th>Descrição</th>
                                <th><i class="fas fa-calendar-alt"></i> Atualização</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registros) > 0): ?>
                                <?php foreach ($registros as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row[$id_column]); ?></td>
                                        <td><strong class="text-primary"><?php echo htmlspecialchars($row['nivelOrdem']); ?>º</strong></td>
                                        <td><?php echo htmlspecialchars($row['tipoNome']); ?></td>
                                        <td><div class="short-text" title="<?php echo htmlspecialchars($row['nivelDescricao'] ?? 'N/A'); ?>"><?php echo htmlspecialchars($row['nivelDescricao'] ?? 'N/A'); ?></div></td>
                                        <td><?php echo (new DateTime($row[$id_column.'DataAtualizacao']))->format('d/m/Y H:i'); ?></td>
                                        <td class="text-center">
                                            <a href="nivel_hierarquico.php?id=<?php echo $row[$id_column]; ?>" 
                                                class="btn btn-sm btn-info text-white" 
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="nivel_hierarquico.php?action=delete&id=<?php echo $row[$id_column]; ?>" 
                                                class="btn btn-sm btn-danger" 
                                                title="Excluir"
                                                onclick="return confirm('ATENÇÃO: A exclusão deste Nível pode afetar Cargos. Cargos vinculados ficarão sem Nível. Deseja realmente excluir?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum Nível Hierárquico cadastrado.</td>
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