<?php
// Arquivo: includes/navbar.php
// (Este arquivo assume que 'functions.php' já foi incluído pela página pai)

// Busca o nome do usuário da sessão
$username = $_SESSION['username'] ?? 'Usuário';

// Define o caminho raiz com base em uma variável (se definida), ou assume caminhos relativos
// Ex: index.php define $root_path = './';
// Ex: views/cargos.php define $root_path = '../';
if (!isset($root_path)) {
    $root_path = './'; // Padrão seguro
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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

<div class="container mt-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <?php if (isset($breadcrumb_items) && is_array($breadcrumb_items)): ?>
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
            <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            <?php endif; ?>
        </ol>
    </nav>
</div>