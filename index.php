<?php
// Arquivo: index.php (Novo Dashboard de Estatísticas)

require_once 'config.php';
require_once 'includes/functions.php'; 

// Redireciona para o login se o usuário não estiver autenticado (Segurança)
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getDbConnection();
$username = $_SESSION['username'] ?? 'Usuário';

// ----------------------------------------------------
// LÓGICA: Obter Contagens
// ----------------------------------------------------
function countRecords(PDO $pdo, string $tableName): int {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM {$tableName}");
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

$stats = [
    'cargos' => countRecords($pdo, 'cargos'),
    'habilidades' => countRecords($pdo, 'habilidades'),
    'caracteristicas' => countRecords($pdo, 'caracteristicas'),
    'riscos' => countRecords($pdo, 'riscos'),
    'usuarios' => countRecords($pdo, 'usuarios'),
    'cursos' => countRecords($pdo, 'cursos'),
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ITACITRUS - Estatísticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .card-stat { border-left: 5px solid; }
        .stat-icon { font-size: 2.5rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid container">
        <a class="navbar-brand" href="index.php">ITACITRUS | Dashboard</a>
        <div class="d-flex">
            <a href="painel.php" class="btn btn-outline-light btn-sm me-3">
                <i class="fas fa-list"></i> Ir para o Painel de Cadastros
            </a>
            <span class="navbar-text me-3 text-white">Olá, <?php echo htmlspecialchars($username); ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="p-5 mb-4 bg-light rounded-3 shadow-sm">
        <div class="container-fluid py-3">
            <h1 class="display-6 fw-bold">Visão Geral do Sistema</h1>
            <p class="fs-5">Estatísticas e contagens em tempo real dos dados mestres.</p>
        </div>
    </div>

    <h2 class="mb-4">Estatísticas de Competências e Cargos</h2>

    <div class="row g-4">
        
        <div class="col-md-6 col-lg-4">
            <div class="card card-stat border-primary h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-primary">Cargos Cadastrados</h5>
                        <h1 class="display-4 fw-bold"><?php echo $stats['cargos']; ?></h1>
                    </div>
                    <i class="fas fa-hard-hat stat-icon text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card card-stat border-warning h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-warning">Habilidades (Hard/Soft)</h5>
                        <h1 class="display-4 fw-bold"><?php echo $stats['habilidades']; ?></h1>
                    </div>
                    <i class="fas fa-lightbulb stat-icon text-warning opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card card-stat border-danger h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-danger">Tipos de Riscos</h5>
                        <h1 class="display-4 fw-bold"><?php echo $stats['riscos']; ?></h1>
                    </div>
                    <i class="fas fa-exclamation-triangle stat-icon text-danger opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card card-stat border-info h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-info">Características Pessoais</h5>
                        <h1 class="display-4 fw-bold"><?php echo $stats['caracteristicas']; ?></h1>
                    </div>
                    <i class="fas fa-user-tag stat-icon text-info opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card card-stat border-secondary h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-secondary">Cursos Cadastrados</h5>
                        <h1 class="display-4 fw-bold"><?php echo $stats['cursos']; ?></h1>
                    </div>
                    <i class="fas fa-certificate stat-icon text-secondary opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card card-stat border-success h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-success">Usuários do Sistema</h5>
                        <h1 class="display-4 fw-bold"><?php echo $stats['usuarios']; ?></h1>
                    </div>
                    <i class="fas fa-users stat-icon text-success opacity-50"></i>
                </div>
            </div>
        </div>

    </div>

    <div class="text-center mt-5">
        <a href="painel.php" class="btn btn-lg btn-success">
            <i class="fas fa-arrow-right"></i> Gerenciar Cadastros Mestra
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>