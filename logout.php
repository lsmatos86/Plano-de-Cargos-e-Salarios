<?php
// Arquivo: logout.php

/**
 * Script de encerramento de sessão, auditoria e limpeza.
 * Mantém 100% da lógica de negócio e auditoria do repositório original.
 */

require_once 'config.php';
require_once 'includes/functions.php';

use App\Service\AuthService;

// Se o usuário estiver logado, registra a ação no log de auditoria antes de destruir a sessão
if (isset($_SESSION['usuario_id'])) {
    try {
        registrarAuditoria('logout', 'usuarios', $_SESSION['usuario_id'], 'Usuário efetuou logout voluntário do sistema.');
    } catch (\Exception $e) {
        // Fallback silencioso se o serviço de auditoria falhar localmente
        error_log("Erro ao registrar auditoria de logout: " . $e->getMessage());
    }
}

// Executa a limpeza estruturada de cookies e destrói os dados usando o AuthService oficial
AuthService::logout();

// Define a mensagem flash original de sucesso para ser renderizada na tela de login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
setAlerta('Sua sessão foi encerrada com sucesso. Até logo!', 'success');

// Redireciona de forma limpa para a tela de autenticação na raiz do subdiretório /ita/
header("Location: login.php");
exit;