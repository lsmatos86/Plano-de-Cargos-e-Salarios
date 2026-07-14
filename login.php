<?php
// Arquivo: login.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'includes/functions.php';

use App\Core\Database;
use App\Service\AuthService;

// Se o usuário já estiver logado, direciona para o painel principal
if (AuthService::checkAuth()) {
    header("Location: index.php");
    exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        try {
            $db = Database::getConnection();
            
            // Busca o usuário na tabela local
            $sql = "SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($senha, $user['senha'])) {
                unset($user['senha']);
                
                // Trata mapeamento condicional de segurança de perfis para compatibilidade
                $roleId = $user['id_role'] ?? $user['id_perfil'] ?? 2;
                
                $_SESSION['usuario_id'] = $user['usuarioId'];
                $_SESSION['usuario_nome'] = $user['nome'] ?? $user['login'] ?? '';
                $_SESSION['usuario_email'] = $user['email'];
                $_SESSION['usuario_role'] = $roleId;
                
                session_regenerate_id(true);
                
                header("Location: index.php");
                exit;
            } else {
                $erro = "E-mail ou senha incorretos, ou usuário inativo.";
            }
        } catch (\Exception $e) {
            $erro = "Erro de autenticação no servidor: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso ao Sistema - Plano de Cargos e Salários</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL; ?>css/fa/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL; ?>css/estilo.css">
    <style>
        body {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            background-color: #f8f9fc;
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid #e3e6f0;
        }
        .btn-login {
            background-color: #4e73df;
            color: white;
            font-weight: 700;
        }
        .btn-login:hover {
            background-color: #2e59d9;
            color: white;
        }
    </style>
</head>
<body>

<div class="login-card shadow-lg animate-up">
    <div class="login-header">
        <h3 class="text-gray-900 font-weight-bold mb-1"><i class="fas fa-shield-alt text-primary mr-2"></i>Koppla RH</h3>
        <span class="text-muted small">Plano de Cargos e Salários</span>
    </div>
    <div class="card-body p-4">
        
        <?php if ($erro): ?>
            <div class="alert alert-danger shadow-sm small border-left-danger">
                <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email" class="text-dark font-weight-bold small">E-mail Principal</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                    </div>
                    <input type="email" name="email" id="email" class="form-control form-control-sm" placeholder="nome@empresa.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>

            <div class="form-group mb-4">
                <label for="senha" class="text-dark font-weight-bold small">Senha de Acesso</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                    </div>
                    <input type="password" name="senha" id="senha" class="form-control form-control-sm" placeholder="Digite sua senha" required>
                </div>
            </div>

            <button type="submit" class="btn btn-login btn-block btn-sm shadow-sm py-2 text-white">
                <i class="fas fa-sign-in-alt mr-2"></i>Entrar no Sistema
            </button>
        </form>
    </div>
    <div class="card-footer bg-light text-center py-2 border-top-0">
        <small class="text-muted">Koppla &copy; <?= date('Y'); ?></small>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>