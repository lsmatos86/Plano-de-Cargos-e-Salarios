<?php
// Arquivo: logout.php (Localização: Raiz do Projeto)

// Inicia a sessão (necessário para acessar a sessão atual)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Remove todas as variáveis de sessão
$_SESSION = array();

// 2. Destrói a sessão completamente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// 3. Redireciona o usuário de volta para a página de login
header('Location: login.php');
exit;