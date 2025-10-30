<?php
// Arquivo: views/cargos.php (Listagem e Gerenciamento de Cargos)

require_once '../config.php';
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Gerenciamento de Cargos';
$pdo = getDbConnection();
$message = '';
$message_type = '';
$table_name = 'cargos';
$id_column = 'cargoId';
$name_column = 'cargoNome';

// ----------------------------------------------------
// 1. LÓGICA DE EXCLUSÃO (DELETE) - Mantida
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $pdo->beginTransaction();
        
        // 1. Limpa todas as referências nas tabelas de junção
        $pdo->exec("DELETE FROM habilidades_cargo WHERE cargoId = {$id}");
        $pdo->exec("DELETE FROM caracteristicas_cargo WHERE cargoId = {$id}");
        $pdo->exec("DELETE FROM riscos_cargo WHERE cargoId = {$id}");
        $pdo->exec("DELETE FROM cargo_sinonimos WHERE cargoId = {$id}");
        $pdo->exec("DELETE FROM cursos_cargo WHERE cargoId = {$id}");
        $pdo->exec("DELETE FROM recursos_grupos_cargo WHERE cargoId = {$id}");

        // 2. Exclui o cargo principal
        $deleted = deleteRecord($pdo, $table_name, $id_column, $id);
        
        $pdo->commit();
        
        if ($deleted) {
            $message = "Cargo ID {$id} excluído com sucesso!";
            $message_type = 'success';
        } else {
            $message = "Erro: Cargo ID {$id} não encontrado.";
            $message_type = 'danger';
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Erro fatal ao excluir: " . $e->getMessage();
        $message_type = 'danger';
    }

    // Redireciona para limpar a URL
    header("Location: cargos.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 2. LÓGICA DE LEITURA E FILTRO (READ All) - Mantida
// ----------------------------------------------------
$params = [
    'term' => $_GET['term'] ?? '',
    'order_by' => $_GET['order_by'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC'
];

// Query com JOINs para dados mestres
$sql = "
    SELECT 
        c.cargoId, c.cargoNome, c.cargoResumo, c.cargoDataAtualizacao,
        e.escolaridadeTitulo,
        b.cboNome
    FROM cargos c
    LEFT JOIN escolaridades e ON e.escolaridadeId = c.escolaridadeId
    LEFT JOIN cbos b ON b.cboId = c.cboId
";
$bindings = [];

if (!empty($params['term'])) {
    $sql .= " WHERE c.cargoNome LIKE ? OR c.cargoResumo LIKE ?";
    $bindings[] = "%{$params['term']}%";
    $bindings[] = "%{$params['term']}%";
}

$validColumns = ['c.cargoId', 'c.cargoNome', 'e.escolaridadeTitulo', 'b.cboNome', 'c.cargoDataAtualizacao'];
$orderBy = in_array($params['order_by'], $validColumns) ? $params['order_by'] : 'c.cargoId';
$sortDir = in_array(strtoupper($params['sort_dir']), ['ASC', 'DESC']) ? $params['sort_dir'] : 'ASC';

$sql .= " ORDER BY {$orderBy} {$sortDir}";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindings);
    $registros = $stmt->fetchAll();
} catch (\PDOException $e) {
    $registros = [];
    $message = "Erro ao carregar dados: Verifique a integridade das FKs. Erro: " . $e->getMessage();
    $message_type = 'danger';
}

// Verifica e exibe mensagens após redirecionamento
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
        .action-cell { width: 220px; } /* Ajuste de largura para a coluna de ações para caber 4 botões + espaço */
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
            <a href="cargos_form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Cargo
            </a>
        </div>
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por Nome ou Resumo do Cargo" value="<?php echo htmlspecialchars($params['term']); ?>">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                <?php if (!empty($params['term'])): ?>
                    <a href="cargos.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i> Limpar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <span class="fw-bold">Cargos Encontrados: </span> <?php echo count($registros); ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th><a href="?order_by=c.cargoId" class="text-decoration-none text-dark"><i class="fas fa-hashtag me-1"></i> ID</a></th>
                        <th><a href="?order_by=c.cargoNome" class="text-decoration-none text-dark"><i class="fas fa-briefcase me-1"></i> Cargo</a></th>
                        <th><i class="fas fa-tag me-1"></i> CBO</th>
                        <th><a href="?order_by=e.escolaridadeTitulo" class="text-decoration-none text-dark"><i class="fas fa-graduation-cap me-1"></i> Escolaridade</a></th>
                        <th>Resumo</th>
                        <th class="action-cell text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['cargoId']); ?></td>
                                <td><strong class="text-primary"><?php echo htmlspecialchars($row['cargoNome']); ?></strong></td> <td><?php echo htmlspecialchars($row['cboNome'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['escolaridadeTitulo'] ?? 'N/A'); ?></td>
                                <td><div class="short-text" title="<?php echo htmlspecialchars($row['cargoResumo']); ?>"><?php echo htmlspecialchars($row['cargoResumo']); ?></div></td>
                                <td class="action-cell text-center">
                                    
                                    <a href="../relatorios/cargo_individual.php?id=<?php echo $row['cargoId']; ?>&format=html" 
                                       class="btn btn-sm btn-outline-secondary" 
                                       title="Visualizar HTML" 
                                       target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="../relatorios/cargo_individual.php?id=<?php echo $row['cargoId']; ?>&format=pdf" 
                                       class="btn btn-sm btn-secondary" 
                                       title="Gerar PDF" 
                                       target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    
                                    <span class="mx-1"></span> 

                                    <a href="cargos_form.php?id=<?php echo $row['cargoId']; ?>" 
                                        class="btn btn-sm btn-info text-white" 
                                        title="Editar Configurações">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <a href="cargos.php?action=delete&id=<?php echo $row['cargoId']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Excluir Cargo"
                                       onclick="return confirm('ATENÇÃO: Excluir este cargo removerá todos os seus requisitos associados (Habilidades, Riscos, etc.). Deseja realmente excluir?');">
                                       <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Nenhum cargo encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>