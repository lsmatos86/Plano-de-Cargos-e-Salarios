<?php
// Arquivo: painel.php (Painel Principal)

// 1. INCLUIR AUTOLOAD PRIMEIRO
// Isso carrega o Composer e permite que as classes (ex: AuthService) sejam encontradas.
require_once 'vendor/autoload.php';

// 2. INCLUIR CONFIG
require_once 'config.php';

// 3. INCLUIR FUNCTIONS
// Agora, functions.php pode usar as classes do Composer
require_once 'includes/functions.php';

// 4. SEGURANÇA
// A função isUserLoggedIn() está em functions.php
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

// $authService foi inicializado dentro de functions.php
$page_title = "Painel de Controle";
$username = $_SESSION['username'] ?? 'Usuário';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Estilo para o ícone do desktop */
        .desktop-icon-container {
            text-decoration: none;
            color: inherit;
        }
        .desktop-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            background-color: #fff;
            height: 100%;
            min-height: 140px; /* Altura mínima para alinhar */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .desktop-icon:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        .desktop-icon i {
            font-size: 3rem; /* Ícone grande */
            margin-bottom: 0.75rem;
        }
        .desktop-icon h5 {
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            margin: 0;
        }
        
        /* Cores específicas para os ícones */
        .icon-cargos { color: #198754; } /* Verde (Success) */
        .icon-relatorios { color: #dc3545; } /* Vermelho (Danger) */
        .icon-usuarios { color: #0d6efd; } /* Azul (Primary) */
        .icon-roles { color: #6f42c1; } /* Roxo (Indigo) */
        .icon-logs { color: #6c757d; } /* Cinza (Secondary) */
        .icon-aux { color: #0dcaf0; } /* Ciano (Info) */
        
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; // Inclui o menu de navegação que criamos ?>

<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Painel de Controle</h1>
        <span class="text-muted">Olá, <?php echo htmlspecialchars($username); ?>!</span>
    </div>

    <h3 class="mb-3">Gestão de Cargos</h3>
    <div class="row">
        <?php if ($authService->userCan('cargos:view')): ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="views/cargos.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-briefcase icon-cargos"></i>
                    <h5>Gerenciar Cargos</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($authService->userCan('cargos:view')): // Usando a mesma permissão ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="relatorios/cargo_total.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-file-pdf icon-relatorios"></i>
                    <h5>Relatórios de Cargos</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <hr class="my-4">

    <h3 class="mb-3">Administração</h3>
    <div class="row">
        <?php if ($authService->userCan('usuarios:manage')): ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="views/usuarios.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-users icon-usuarios"></i>
                    <h5>Usuários</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($authService->userCan('usuarios:manage')): ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="views/roles.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-shield-alt icon-roles"></i>
                    <h5>Papéis e Permissões</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($authService->userCan('logs:view')): ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="views/auditoria.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-history icon-logs"></i>
                    <h5>Logs de Auditoria</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <hr class="my-4">

    <h3 class="mb-3">Tabelas Auxiliares</h3>
    <div class="row">
        <?php if ($authService->userCan('habilidades:manage')): ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="views/habilidades.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-lightbulb icon-aux"></i>
                    <h5>Habilidades</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($authService->userCan('cursos:manage')): ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="views/cursos.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-certificate icon-aux"></i>
                    <h5>Cursos</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($authService->userCan('riscos:manage')): ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="views/riscos.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-radiation-alt icon-aux"></i>
                    <h5>Riscos</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($authService->userCan('caracteristicas:manage')): ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="views/caracteristicas.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-user-tag icon-aux"></i>
                    <h5>Características</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>
        
        <?php if ($authService->userCan('areas:manage')): ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="views/areas_atuacao.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-sitemap icon-aux"></i>
                    <h5>Áreas de Atuação</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($authService->userCan('config:view')): // Permissão genérica para configs ?>
        <div class="col-lg-2 col-md-3 col-4 mb-4">
            <a href="views/escolaridades.php" class="desktop-icon-container">
                <div class="desktop-icon shadow-sm">
                    <i class="fas fa-graduation-cap icon-aux"></i>
                    <h5>Escolaridades</h5>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>