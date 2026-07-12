<?php
// Arquivo: src/Service/AuthService.php

namespace App\Service;

use App\Core\Database;
use PDO;

/**
 * Classe para gerenciar Autenticação e Autorização (Permissões).
 * (Versão corrigida com nomes de colunas em Português e lógica de URL)
 */
class AuthService {
    
    private $db;

    public function __construct() {
        // Correção para o padrão correto da sua classe Database
        $this->db = Database::getConnection();
    }

    /**
     * Tenta realizar o login do usuário
     * * @param string $email
     * @param string $password
     * @return array|bool Retorna os dados do usuário ou false se falhar
     */
    public function login(string $email, string $password) {
        // Busca o usuário pelo e-mail principal ativo
        $sql = "SELECT u.*, r.nome as role_nome 
                FROM usuarios u 
                LEFT JOIN roles r ON u.id_role = r.id_role 
                WHERE u.email_principal = :email AND u.status = 'ativo' 
                LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica a senha usando password_verify
        if ($user && password_verify($password, $user['senha'])) {
            // Remove a senha do array por segurança antes de salvar na sessão
            unset($user['senha']);
            return $user;
        }

        return false;
    }

    /**
     * Inicia a sessão oficial do usuário no PHP
     */
    public function loginSession(array $user): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['usuario_id'] = $user['id_usuario'];
        $_SESSION['usuario_nome'] = $user['nome_completo'] ?? $user['login'];
        $_SESSION['usuario_email'] = $user['email_principal'];
        $_SESSION['usuario_role'] = $user['id_role'];
        $_SESSION['usuario_role_nome'] = $user['role_nome'] ?? 'Usuário';
        
        // Regenera o ID da sessão para prevenir Session Fixation
        session_regenerate_id(true);
    }

    /**
     * Verifica se o usuário está logado
     */
    public static function checkAuth(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['usuario_id']);
    }

    /**
     * Exige que o usuário esteja logado para ver a página, caso contrário redireciona
     */
    public static function requireAuth(string $redirectUrl = 'login.php'): void {
        if (!self::checkAuth()) {
            header("Location: " . $redirectUrl);
            exit;
        }
    }

    /**
     * Verifica se o usuário logado possui uma permissão (recurso) específica
     * * @param string $recursoChave O slug ou chave do recurso (ex: 'usuarios_listar', 'cargos_criar')
     */
    public function temPermissao(string $recursoChave): bool {
        if (!self::checkAuth()) {
            return false;
        }

        $idRole = $_SESSION['usuario_role'] ?? null;
        if (!$idRole) {
            return false;
        }

        // Se for administrador master (id_role = 1), costuma ter acesso total
        if ($idRole == 1) {
            return true;
        }

        // Consulta se a Role do usuário tem vínculo com a chave do recurso solicitado
        $sql = "SELECT COUNT(*) FROM role_recursos rr
                JOIN recursos r ON rr.id_recurso = r.id_recurso
                WHERE rr.id_role = :id_role AND r.chave = :recurso_chave";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_role' => $idRole,
            ':recurso_chave' => $recursoChave
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Exige uma permissão específica. Se não tiver, bloqueia o acesso.
     */
    public function requirePermissao(string $recursoChave): void {
        self::requireAuth();
        
        if (!$this->temPermissao($recursoChave)) {
            http_response_code(403);
            echo "<h1>403 - Acesso Negado</h1>";
            echo "<p>Você não tem permissão para acessar este recurso ({$recursoChave}).</p>";
            exit;
        }
    }

    /**
     * Desloga o usuário limpando a sessão
     */
    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
    catch (\PDOException $e) {
    // Comente temporariamente a linha amigável que esconde o erro
    // die("Erro de Conexão com o Banco de Dados. Por favor, tente novamente mais tarde.");
    
    // Adicione isto para imprimir o erro verdadeiro:
    die("Erro Real: " . $e->getMessage());
}
}