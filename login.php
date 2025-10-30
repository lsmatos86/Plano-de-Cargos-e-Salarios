<?php
// Arquivo: login.php (Localização: Raiz do Projeto)

// 1. Inclui o arquivo de constantes do banco de dados (na raiz)
require_once 'config.php';

// 2. Inclui o arquivo de funções (essencial para autenticação, localizado em includes/)
require_once 'includes/functions.php'; 

// Verifica se o usuário JÁ está logado. Se sim, redireciona para a página inicial.
if (isUserLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? ''; 
    $password = $_POST['password'] ?? '';

    // Tenta autenticar o usuário usando o e-mail e a senha bruta no banco de dados
    if (authenticateUser($email, $password)) { 
        header('Location: index.php');
        exit;
    } else {
        $error_message = 'E-mail ou senha inválidos.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ITACITRUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f8f9fa;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffe4c4 100%); 
        }
        .login-container { 
            max-width: 400px; 
            margin-top: 100px; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            background-color: #fff; 
        }
        .logo-text {
            color: #198754; 
            font-weight: bold;
            font-size: 1.5rem;
            margin-bottom: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-container mx-auto">
        <div class="text-center mb-4">
            <p class="logo-text">ITACITRUS | Gestão</p>
            <small class="text-muted">Acesso ao Sistema</small>
        </div>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg">Entrar</button>
            </div>
            <p class="mt-3 text-center text-muted small">Use as credenciais de teste: E-mail: **admin@itacitrus.com** / Senha: **minhasenha**</p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>