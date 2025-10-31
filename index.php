<?php
// Arquivo: index.php (Dashboard de Estatísticas + Menu Organizado)

// 1. Inclusão de arquivos
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'includes/functions.php'; // Apenas para autenticação

// 2. Importa o Repositório de Lookup
use App\Repository\LookupRepository;

// Redireciona para o login se o usuário não estiver autenticado (Segurança)
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

// ----------------------------------------------------
// LÓGICA: Obter Contagens (REFATORADO)
// ----------------------------------------------------

// Instancia o repositório
$lookupRepo = new LookupRepository();

// A função local 'countRecords' foi removida.
// Agora usamos o método do repositório.
$stats = [
    'cargos' => $lookupRepo->countRecords('cargos'),
    'habilidades' => $lookupRepo->countRecords('habilidades'),
    'caracteristicas' => $lookupRepo->countRecords('caracteristicas'),
    'riscos' => $lookupRepo->countRecords('riscos'),
    'usuarios' => $lookupRepo->countRecords('usuarios'),
    'cursos' => $lookupRepo->countRecords('cursos'),
    'areas_atuacao' => $lookupRepo->countRecords('areas_atuacao'),
    'niveis_hierarquia' => $lookupRepo->countRecords('nivel_hierarquico'),
    'faixas_salariais' => $lookupRepo->countRecords('faixas_salariais'),
];

// Obtém o nome do usuário da sessão (mantido)
$username = $_SESSION['username'] ?? 'Usuário';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ITACITRUS - Plano de Cargos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .card-stat { border-left: 5px solid; transition: transform 0.2s; }
        .card-stat:hover { transform: translateY(-3px); }
        .stat-icon { font-size: 2.5rem; }
        .list-group-item { transition: background-color 0.15s; }
        .list-group-item i { width: 25px; text-align: center; }
        .config-card .list-group-item { font-size: 0.95rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid container">
        <a class="navbar-brand" href="index.php">ITACITRUS | Dashboard</a>
        <div class="d-flex">
            <span class="navbar-text me-3 text-white">Olá, <?php echo htmlspecialchars($username); ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="p-5 mb-4 bg-light rounded-3 shadow-sm">
        <div class="container-fluid py-3">
            <h1 class="display-6 fw-bold">Visão Geral do Sistema</h1>
            <p class="fs-5">Estatísticas e acessos rápidos aos principais módulos.</p>
        </div>
    </div>

    <h2 class="mb-4">Estatísticas Chave</h2>

    <div class="row g-4 mb-5">
        
        <div class="col-md-6 col-lg-3">
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
        
        <div class="col-md-6 col-lg-3">
            <div class="card card-stat border-info h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-info">Áreas de Atuação</h5>
                        <h1 class="display-4 fw-bold"><?php echo $stats['areas_atuacao']; ?></h1>
                    </div>
                    <i class="fas fa-building stat-icon text-info opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card card-stat border-warning h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-warning">Níveis Hierárquicos</h5>
                        <h1 class="display-4 fw-bold"><?php echo $stats['niveis_hierarquia']; ?></h1>
                    </div>
                    <i class="fas fa-sitemap stat-icon text-warning opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card card-stat border-success h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-success">Total de Usuários</h5>
                        <h1 class="display-4 fw-bold"><?php echo $stats['usuarios']; ?></h1>
                    </div>
                    <i class="fas fa-users stat-icon text-success opacity-50"></i>
                </div>
            </div>
        </div>

    </div>
    
    <h2 class="mb-4">Menu Principal de Acesso</h2>

    <div class="row g-4">
        
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg h-100">
                <div class="card-header bg-primary text-white fs-5"><i class="fas fa-cogs me-2"></i> Gestão Central</div>
                <div class="list-group list-group-flush">
                    <a href="views/cargos.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-briefcase me-2 text-primary"></i> Gerenciar Cargos
                    </a>
                     <a href="views/usuarios.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-shield me-2 text-primary"></i> Gerenciar Usuários
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg h-100">
                <div class="card-header bg-info text-white fs-5"><i class="fas fa-sitemap me-2"></i> Estrutura e Salário</div>
                <div class="list-group list-group-flush config-card">
                    <a href="views/areas_atuacao.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-building me-2 text-info"></i> Gerenciar Áreas de Atuação
                    </a>
                    <a href="views/tipo_hierarquia.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tag me-2 text-info"></i> Tipos de Hierarquia (Estratégico/Tático)
                    </a>
                    <a href="views/nivel_hierarquico.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-sort-numeric-down me-2 text-info"></i> Níveis Hierárquicos (Ordem)
                    </a>
                    <a href="views/faixas_salariais.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-wallet me-2 text-info"></i> Faixas Salariais (Min/Máx)
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg h-100">
                <div class="card-header bg-secondary text-white fs-5"><i class="fas fa-database me-2"></i> Cadastros Mestra e Config.</div>
                <div class="list-group list-group-flush config-card">
                    <a href="views/escolaridades.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-graduation-cap me-2 text-secondary"></i> Escolaridades
                    </a>
                    <a href="views/cbos.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-id-badge me-2 text-secondary"></i> CBOs e Família CBO
                    </a>
                    <a href="views/habilidades.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-lightbulb me-2 text-secondary"></i> Habilidades (Hard/Soft)
                    </a>
                    <a href="views/caracteristicas.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-tag me-2 text-secondary"></i> Características Pessoais
                    </a>
                     <a href="views/cursos.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-certificate me-2 text-secondary"></i> Cursos e Treinamentos
                    </a>
                     <a href="views/riscos.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-exclamation-triangle me-2 text-secondary"></i> Riscos de Exposição
                    </a>
                    <a href="views/recursos.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-wrench me-2 text-secondary"></i> Recursos e Grupos
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mt-4">
            <div class="card shadow-lg h-100">
                <div class="card-header bg-danger text-white fs-5"><i class="fas fa-file-pdf me-2"></i> Relatórios e Análise</div>
                <div class="list-group list-group-flush">
                    <a href="relatorios/cargo_total.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-print me-2 text-danger"></i> Relatório Consolidado (Todos os Cargos - PDF)
                    </a>
                    <a href="#" class="list-group-item list-group-item-action disabled" title="Funcionalidade a ser implementada">
                        <i class="fas fa-project-diagram me-2 text-danger"></i> Organograma de Cargos (Mapa Hierárquico)
                    </a>
                     <a href="views/cargos.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-search me-2 text-danger"></i> Buscar Cargos Individuais (HTML/PDF)
                    </a>
                </div>
            </div>
        </div>

    </div> 
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>