<?php
// Arquivo: views/areas_atuacao.php (Gerenciamento de Áreas de Atuação)

require_once '../config.php';
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Gerenciamento de Áreas de Atuação';
$pdo = getDbConnection();
$message = '';
$message_type = '';
$table_name = 'areas_atuacao';
$id_column = 'areaId';
$name_column = 'areaNome';

// LÓGICA DE CADASTRO/EDIÇÃO (CREATE/UPDATE) - Simplificada para demonstração
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['action'])) {
    $nome = trim($_POST[$name_column] ?? '');
    $descricao = trim($_POST['areaDescricao'] ?? null);
    $areaPaiId = empty($_POST['areaPaiId']) ? null : (int)$_POST['areaPaiId'];
    $id = (int)($_POST[$id_column] ?? 0);

    if (empty($nome)) {
        $message = "O nome da Área é obrigatório.";
        $message_type = 'danger';
    } else {
        try {
            if ($id > 0) {
                // UPDATE
                $sql = "UPDATE {$table_name} SET {$name_column} = ?, areaDescricao = ?, areaPaiId = ?, areaDataAtualizacao = CURRENT_TIMESTAMP() WHERE {$id_column} = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $descricao, $areaPaiId, $id]);
                $message = "Área atualizada com sucesso!";
            } else {
                // CREATE
                $sql = "INSERT INTO {$table_name} ({$name_column}, areaDescricao, areaPaiId, areaDataCadastro) VALUES (?, ?, ?, CURRENT_TIMESTAMP())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $descricao, $areaPaiId]);
                $message = "Nova Área cadastrada com sucesso!";
            }
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = "Erro ao salvar: O nome da Área pode já existir ou há um problema na hierarquia. " . $e->getMessage();
            $message_type = 'danger';
        }
    }
    // Redireciona para evitar re-submissão do formulário
    header("Location: areas_atuacao.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// LÓGICA DE EXCLUSÃO (DELETE)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // A exclusão está protegida pela FK (ON DELETE SET NULL para PAI, ON DELETE CASCADE para CARGO)
    $deleted = deleteRecord($pdo, $table_name, $id_column, $id);
    
    if ($deleted) {
        $message = "Área ID {$id} excluída com sucesso! Cargos relacionados e áreas filhas foram atualizados.";
        $message_type = 'success';
    } else {
        $message = "Erro ao excluir: A Área ID {$id} não foi encontrada ou existem dependências (verifique se há cargos ou áreas filhas).";
        $message_type = 'danger';
    }

    header("Location: areas_atuacao.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// LÓGICA DE LEITURA E FILTRO (READ All)
$params = [
    'term' => $_GET['term'] ?? '', // CAPTURA DO TERMO DE BUSCA
    'order_by' => $_GET['order_by'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC'
];

$sql = "
    SELECT 
        a.areaId, a.areaNome, a.areaDescricao, 
        a.areaDataAtualizacao, a_pai.areaNome AS areaPaiNome
    FROM {$table_name} a
    LEFT JOIN {$table_name} a_pai ON a_pai.areaId = a.areaPaiId
";
$bindings = [];

// LÓGICA DE BUSCA APLICADA
if (!empty($params['term'])) {
    $sql .= " WHERE a.areaNome LIKE ? OR a.areaDescricao LIKE ?";
    $bindings[] = "%{$params['term']}%";
    $bindings[] = "%{$params['term']}%";
}

$validColumns = ['a.areaId', 'a.areaNome', 'a_pai.areaNome', 'a.areaDataAtualizacao'];
$orderBy = in_array($params['order_by'], $validColumns) ? $params['order_by'] : 'a.areaNome';
$sortDir = in_array(strtoupper($params['sort_dir']), ['ASC', 'DESC']) ? $params['sort_dir'] : 'ASC';

$sql .= " ORDER BY {$orderBy} {$sortDir}";

$stmt = $pdo->prepare($sql);
$stmt->execute($bindings);
$registros = $stmt->fetchAll();

// Carrega dados para o formulário de edição/criação
$areaToEdit = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM {$table_name} WHERE {$id_column} = ?");
    $stmt->execute([$_GET['id']]);
    $areaToEdit = $stmt->fetch();
}

// Carrega todas as áreas para o dropdown Pai/Filho (Lookup)
$areasLookup = getAreaHierarchyLookup($pdo); 

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
                    <?php echo $areaToEdit ? 'Editar Área: ' . htmlspecialchars($areaToEdit['areaNome']) : 'Nova Área de Atuação'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="areas_atuacao.php">
                        <input type="hidden" name="areaId" value="<?php echo htmlspecialchars($areaToEdit['areaId'] ?? 0); ?>">

                        <div class="mb-3">
                            <label for="areaNome" class="form-label">Nome da Área *</label>
                            <input type="text" class="form-control" id="areaNome" name="areaNome" value="<?php echo htmlspecialchars($areaToEdit['areaNome'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="areaPaiId" class="form-label">Área Pai (Hierarquia)</label>
                            <select class="form-select" id="areaPaiId" name="areaPaiId">
                                <option value="">--- Nível Raiz (Top) ---</option>
                                <?php foreach ($areasLookup as $id => $nomeHierarquico): 
                                    // Evita que uma área seja seu próprio pai ou pai de seus descendentes
                                    if ((int)($areaToEdit['areaId'] ?? 0) !== $id):
                                ?>
                                    <option value="<?php echo $id; ?>" 
                                            <?php echo (int)($areaToEdit['areaPaiId'] ?? 0) === $id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($nomeHierarquico); ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="areaDescricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="areaDescricao" name="areaDescricao" rows="3"><?php echo htmlspecialchars($areaToEdit['areaDescricao'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> <?php echo $areaToEdit ? 'Salvar Alterações' : 'Cadastrar Área'; ?>
                        </button>
                        <?php if ($areaToEdit): ?>
                             <a href="areas_atuacao.php" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="fas fa-plus"></i> Novo Cadastro
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <form method="GET" action="areas_atuacao.php" class="mb-3">
                <div class="input-group">
                    <input type="text" 
                           name="term" 
                           class="form-control" 
                           placeholder="Buscar por Nome ou Descrição da Área..." 
                           value="<?php echo htmlspecialchars($params['term']); ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($params['term'])): ?>
                        <a href="areas_atuacao.php" class="btn btn-outline-danger" title="Limpar Busca">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <span class="fw-bold">Áreas Cadastradas: </span> <?php echo count($registros); ?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Nome da Área</th>
                                <th>Área Pai</th>
                                <th><i class="fas fa-calendar-alt"></i> Atualização</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registros) > 0): ?>
                                <?php foreach ($registros as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['areaId']); ?></td>
                                        <td><strong class="text-primary"><?php echo htmlspecialchars($row['areaNome']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['areaPaiNome'] ?? 'Nível Raiz'); ?></td>
                                        <td><?php echo (new DateTime($row['areaDataAtualizacao']))->format('d/m/Y H:i'); ?></td>
                                        <td class="text-center">
                                            <a href="areas_atuacao.php?id=<?php echo $row['areaId']; ?>" 
                                                class="btn btn-sm btn-info text-white" 
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="areas_atuacao.php?action=delete&id=<?php echo $row['areaId']; ?>" 
                                                class="btn btn-sm btn-danger" 
                                                title="Excluir"
                                                onclick="return confirm('ATENÇÃO: A exclusão desta área irá remover seus vínculos com os cargos e tornará áreas filhas em Nível Raiz. Deseja realmente excluir?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Nenhuma Área de Atuação cadastrada.</td>
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