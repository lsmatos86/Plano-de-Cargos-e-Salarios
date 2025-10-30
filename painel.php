<?php
// Arquivo: index.php (Localização: Raiz do Projeto)

// 1. Inclui o arquivo de constantes do banco de dados
require_once 'config.php';

// 2. Inclui o arquivo de funções
require_once 'includes/functions.php'; 

// Redireciona para o login se o usuário não estiver autenticado (Segurança)
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Obtém o nome do usuário para personalização
$username = $_SESSION['username'] ?? 'Usuário';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ITACITRUS - Gestão de Competências</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .card-link-icon { font-size: 2.5rem; color: #198754; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0, 128, 0, 0.2); transition: all 0.3s; }
        .card-active a .card {
            border: 2px solid #198754;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid container">
        <a class="navbar-brand" href="index.php">ITACITRUS | Gestão</a>
        <div class="d-flex">
            <span class="navbar-text me-3 text-white">Olá, <?php echo htmlspecialchars($username); ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="p-5 mb-4 bg-light rounded-3 shadow-sm">
        <div class="container-fluid py-3">
            <h1 class="display-6 fw-bold">Bem-vindo(a) ao Painel de Controle!</h1>
            <p class="fs-5">Gerencie os dados mestres e as competências necessárias para os cargos da ITACITRUS.</p>
        </div>
    </div>

    <h2 class="mb-4">Gerenciamento de Cargos e Competências</h2>
    <div class="row g-4 mb-5">
        
        <div class="col-md-6 col-lg-4">
            <a href="views/cargos.php" class="text-decoration-none">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body">
                        <i class="fas fa-hard-hat float-end fs-1"></i>
                        <h5 class="card-title">Cadastrar Cargos</h5>
                        <p class="card-text">Gestão completa de requisitos, riscos e associações N:M.</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <a href="views/habilidades.php" class="text-decoration-none">
                <div class="card text-white bg-warning h-100">
                    <div class="card-body">
                        <i class="fas fa-lightbulb float-end fs-1 text-dark"></i>
                        <h5 class="card-title text-dark">Habilidades (Hard/Soft)</h5>
                        <p class="card-text text-dark">Gerenciar Hard Skills e Soft Skills para associação.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <hr class="my-5">

    <h2 class="mb-4">Cadastros de Dados Mestres</h2>

    <div class="row g-4">
        
        <div class="col-md-4 col-lg-3 card-active">
            <a href="views/escolaridades.php" class="text-decoration-none">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-graduation-cap card-link-icon mb-2"></i>
                        <h5 class="card-title text-success">Escolaridades</h5>
                        <p class="card-text text-muted small">Níveis de formação requeridos.</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4 col-lg-3 card-active">
            <a href="views/familia_cbo.php" class="text-decoration-none">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-sitemap card-link-icon mb-2"></i>
                        <h5 class="card-title text-success">Famílias CBO</h5>
                        <p class="card-text text-muted small">Gerenciar categorias base do CBO.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-lg-3 card-active">
            <a href="views/cbos.php" class="text-decoration-none">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-tag card-link-icon mb-2"></i>
                        <h5 class="card-title text-success">Códigos CBO</h5>
                        <p class="card-text text-muted small">Códigos de Ocupação (FK de Família).</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4 col-lg-3 card-active">
            <a href="views/riscos.php" class="text-decoration-none">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle card-link-icon mb-2"></i>
                        <h5 class="card-title text-success">Tipos de Riscos</h5>
                        <p class="card-text text-muted small">Físicos, Ergonômicos, Psicossociais, etc.</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4 col-lg-3 card-active">
            <a href="views/recursos.php" class="text-decoration-none">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-tools card-link-icon mb-2"></i>
                        <h5 class="card-title text-success">Recursos</h5>
                        <p class="card-text text-muted small">Equipamentos e insumos utilizados.</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4 col-lg-3 card-active">
            <a href="views/cursos.php" class="text-decoration-none">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-certificate card-link-icon mb-2"></i>
                        <h5 class="card-title text-success">Cursos</h5>
                        <p class="card-text text-muted small">Cursos de capacitação e treinamento.</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4 col-lg-3 card-active">
            <a href="views/caracteristicas.php" class="text-decoration-none">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-user-tag card-link-icon mb-2"></i>
                        <h5 class="card-title text-success">Características</h5>
                        <p class="card-text text-muted small">Traços pessoais e princípios desejados.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <hr class="my-5">
    
    <h2 class="mb-4">Gestão do Sistema</h2>

    <div class="row g-4">
        <div class="col-md-4 col-lg-3">
            <a href="views/usuarios.php" class="text-decoration-none">
                <div class="card text-center h-100 bg-info text-white">
                    <div class="card-body">
                        <i class="fas fa-users card-link-icon mb-2 text-white"></i>
                        <h5 class="card-title text-white">Gestão de Usuários</h5>
                        <p class="card-text small">Criar, editar e gerenciar acessos (administradores).</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="#" class="text-decoration-none">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-cogs card-link-icon mb-2"></i>
                        <h5 class="card-title text-muted">Configurações</h5>
                        <p class="card-text text-muted small">Ajustes gerais do sistema.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>