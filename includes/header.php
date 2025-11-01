<?php
// Arquivo: includes/header.php (Corrigido)
// (Este arquivo assume que 'functions.php' já foi incluído pela página que o chama)

// --- Variáveis definidas pela página que inclui este header ---
// $page_title: O título da página (Obrigatório)
// $root_path: O caminho para a raiz (ex: './' ou '../') (Obrigatório)
// $breadcrumb_items: O array de breadcrumbs (Opcional)
// $is_dashboard: (Novo) true se for o index.php, false ou nulo caso contrário

$username = $_SESSION['username'] ?? 'Usuário';

if (!isset($page_title)) {
    $page_title = 'Sistema de Cargos';
}

// Define o padrão para $is_dashboard se não for fornecido
$is_dashboard = $is_dashboard ?? false;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | ITACITRUS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa; /* bg-light */
            
            /* * Ajuste dinâmico do padding-top
             * Se for dashboard (só 1 nav), padding menor.
             * Se for pág. interna (2 navs), padding maior.
            */
            <?php
            if ($is_dashboard) {
                echo "padding-top: 56px;"; // Altura da navbar principal
            } else {
                echo "padding-top: 104px;"; // Altura das duas navbars (aprox. 56px + 48px)
            }
            ?>
        }
        .main-content {
            flex: 1; 
        }
        .footer {
            background-color: #343a40; /* bg-dark */
            color: white;
            padding: 1rem 0;
            margin-top: auto; 
        }
        
        /* Estilos dos Ícones (globais) */
        .desktop-icon-container { text-decoration: none; color: inherit; }
        .desktop-icon {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 1.5rem 1rem; border: 1px solid #dee2e6; border-radius: 0.5rem;
            background-color: #fff; height: 100%; min-height: 140px; 
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .desktop-icon:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        .desktop-icon i { font-size: 3rem; margin-bottom: 0.75rem; }
        .desktop-icon h5 { font-size: 1rem; font-weight: 600; text-align: center; margin: 0; }
        .icon-gestao { color: #0d6efd; } .icon-relatorios { color: #dc3545; }
        .icon-admin { color: #6f42c1; } .icon-estrutura { color: #198754; }
        .icon-cadastros { color: #6c757d; } 

        /* Garante que o menu cascata seja slim */
        .navbar-cascata {
            min-height: 48px;
        }
    </style>
</head>
<body>

<header class="fixed-top">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid container">
            <a class="navbar-brand" href="<?php echo htmlspecialchars($root_path); ?>index.php">
                <i class="fas fa-briefcase me-2"></i> ITACITRUS | Gestão de Cargos
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3 text-white">
                    <i class="fas fa-user-circle me-1"></i> Olá, <?php echo htmlspecialchars($username); ?>
                </span>
                <a href="<?php echo htmlspecialchars($root_path); ?>logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Sair
                </a>
            </div>
        </div>
    </nav>

    <?php if (!$is_dashboard): ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm py-0 navbar-cascata">
        <div class="container-fluid container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#submenuCascata" aria-controls="submenuCascata" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="submenuCascata">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navGestao" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-briefcase me-1"></i> Gestão
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navGestao">
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/cargos.php">Gerenciar Cargos</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navEstrutura" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-sitemap me-1"></i> Estrutura
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navEstrutura">
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/areas_atuacao.php">Áreas de Atuação</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/tipo_hierarquia.php">Tipos de Hierarquia</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/nivel_hierarquico.php">Níveis Hierárquicos</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/faixas_salariais.php">Faixas Salariais</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navCadastros" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-database me-1"></i> Cadastros
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navCadastros">
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/habilidades.php">Habilidades</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/cursos.php">Cursos</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/riscos.php">Riscos</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/caracteristicas.php">Características</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/escolaridades.php">Escolaridades</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/cbos.php">CBOs e Família</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/recursos.php">Recursos e Grupos</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-1"></i> Administração
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navAdmin">
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/usuarios.php">Usuários</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/roles.php">Papéis e Permissões</a></li>
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>views/auditoria.php">Logs de Auditoria</a></li>
                        </ul>
                    </li>

                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navRelatorios" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-alt me-1"></i> Relatórios
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navRelatorios">
                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars($root_path); ?>relatorios/cargo_total.php">Relatório Consolidado</a></li>
                            <li><a class="dropdown-item disabled" href="#">Organograma (em breve)</a></li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

</header> <main class="container mb-5 main-content">

    <?php if (isset($breadcrumb_items) && is_array($breadcrumb_items)): ?>
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <?php foreach ($breadcrumb_items as $name => $link): ?>
                    <?php if ($link === null): // A página ativa não tem link ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo htmlspecialchars($name); ?>
                        </li>
                    <?php else: // Páginas anteriores no caminho ?>
                        <li class="breadcrumb-item">
                            <a href="<?php echo htmlspecialchars($link); ?>"><?php echo htmlspecialchars($name); ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    <?php endif; ?>

<?php 