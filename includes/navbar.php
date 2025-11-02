<?php
// Arquivo: includes/navbar.php
// (Incluído por header.php)

// ======================================================
// CORREÇÃO: Importa a variável $root_path para este escopo
// ======================================================
global $root_path; 

// Obter o nome de usuário e ID da sessão
$username = $_SESSION['username'] ?? 'Usuário';
$user_id = $_SESSION['user_id'] ?? 0;
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $root_path; ?>index.php">
            <i class="fas fa-database me-2"></i>Sistema de Cargos
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto">
                </ul>
            
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($username); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                        <li>
                            <a class="dropdown-item" href="<?php echo $root_path; ?>views/usuarios_form.php?id=<?php echo $user_id; ?>">
                                <i class="fas fa-user-cog me-2"></i>Meu Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo $root_path; ?>logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Sair
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>