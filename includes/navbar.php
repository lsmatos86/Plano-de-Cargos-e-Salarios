<?php
// Arquivo: includes/navbar.php
// Este arquivo deve ser incluído DEPOIS de 'functions.php'

// Garante que $authService exista, mesmo que o usuário não esteja logado
// (embora esta navbar só deva ser usada em páginas protegidas)
global $authService;
if (!isset($authService) || !($authService instanceof \App\Service\AuthService)) {
    // Cria um "dummy" auth service para evitar erros fatais
    // se esta navbar for incluída acidentalmente antes de functions.php
    $authService = new class {
        public function userCan(string $perm): bool { return false; }
    };
}

// Pega o nome de usuário da sessão
$username = $_SESSION['username'] ?? 'Usuário';

/* * Determina o caminho base (Path Prefix)
 * Se o script atual estiver dentro de /views/, o base path é '../'
 * Caso contrário (ex: painel.php), o base path é './'
 */
$basePath = (strpos($_SERVER['SCRIPT_NAME'], '/views/') !== false) ? '../' : './';

?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $basePath; ?>painel.php">
            <i class="fas fa-briefcase me-2"></i>
            Sistema de Cargos
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $basePath; ?>painel.php">Painel</a>
                </li>
                
                <?php if ($authService->userCan('cargos:view')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $basePath; ?>views/cargos.php">Cargos</a>
                </li>
                <?php endif; ?>
                
                <?php if ($authService->userCan('usuarios:manage')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Administração
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="<?php echo $basePath; ?>views/usuarios.php">Gestão de Usuários</a></li>
                        <li><a class="dropdown-item" href="<?php echo $basePath; ?>views/roles.php">Gestão de Papéis</a></li>
                        
                        <?php if ($authService->userCan('logs:view')): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo $basePath; ?>views/auditoria.php">Logs de Auditoria</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
            </ul>
            <div class="d-flex">
                <span class="navbar-text me-3 text-white">
                    Olá, <?php echo htmlspecialchars($username); ?>
                </span>
                <a href="<?php echo $basePath; ?>logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </div>
</nav>