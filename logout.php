<?php
// Arquivo: logout.php
// Este arquivo agora inclui o autoload e functions para registrar o log de auditoria.

// 1. Incluir Autoload, Config e Functions
// É necessário para carregar o AuditService e verificar os dados da sessão.
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'includes/functions.php'; // Isso também chama startSession()

// 2. Importar o AuditService
use App\Service\AuditService;

// 3. Registrar o Log de Auditoria
// Deve ser feito ANTES de destruir a sessão
if (isUserLoggedIn()) {
    try {
        $auditService = new AuditService();
        $usuarioId = $_SESSION['user_id'] ?? null;
        
        // Registra o evento de logout usando os nomes de coluna em português
        $auditService->log('LOGOUT', 'usuarios', $usuarioId);
        
    } catch (Exception $e) {
        // Se o log falhar, não impede o logout.
        // Apenas registra o erro no log do PHP.
        error_log("Falha ao registrar log de LOGOUT: " . $e->getMessage());
    }
}

// 4. Limpar e Destruir a Sessão
$_SESSION = []; // Limpa todas as variáveis de sessão
session_unset(); // Libera as variáveis
session_destroy(); // Destrói os dados da sessão no servidor

// 5. Redirecionar para a página de login
header('Location: login.php?message=Logout efetuado com sucesso.');
exit;