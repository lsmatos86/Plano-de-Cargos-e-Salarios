<?php
// Arquivo: login.php (Página de Login)

// 1. Inclui os arquivos necessários
// vendor/autoload.php e config.php SÃO necessários para a classe Database
// functions.php é necessário para a função authenticateUser()
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'includes/functions.php'; // Inclui authenticateUser() e isUserLoggedIn()

// 2. Lógica de Login
$error_message = '';

// Se o usuário já estiver logado, redireciona para o painel
if (isUserLoggedIn()) {
    header('Location: painel.php');
    exit;
}

// Se o formulário foi enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 3. Tenta autenticar
    // A função authenticateUser() agora usa App\Core\Database e registra os logs de auditoria
    if (authenticateUser($email, $password)) {
        // Sucesso: Redireciona para o painel
        header('Location: painel.php');
        exit;
    } else {
        // Falha: Define mensagem de erro
        // O log de falha já foi registrado dentro de authenticateUser()
        $error_message = 'E-mail ou senha inválidos, ou usuário inativo.';
    }
}

// 4. Mensagem de Logout (vinda do logout.php)
if (isset($_GET['message'])) {
    $error_message = htmlspecialchars($_GET['message']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistema de Cargos e Salários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 5rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-briefcase fa-3x text-success"></i>
                <h2 class="mt-3">Sistema de Cargos</h2>
                <p class="text-muted">Faça login para continuar</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>