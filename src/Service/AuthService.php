<?php
// Arquivo: src/Service/AuthService.php

namespace App\Service;

use App\Core\Database;
use PDO;

/**
 * Classe para gerenciar Autenticação e Autorização (Permissões).
 * Versão corrigida para o esquema PostgreSQL local da Koppla (Tabela de ligação user_roles).
 */
class AuthService {
    
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Tenta realizar o login do usuário
     * @param string $email
     * @param string $password
     * @return array|bool Retorna os dados do usuário ou false se falhar
     */
    public function login(string $email, string $password) {
        // CORREÇÃO POSTGRESQL: A tabela usuarios não possui id_role diretamente.
        // Buscamos o usuário primeiro apenas pelo e-mail ativo de forma segura.
        $sql = "SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica a senha usando o algoritmo nativo password_verify
        if ($user && password_verify($password, $user['senha'])) {
            // Remove o hash de senha do array por segurança antes de transitar os dados
            unset($user['senha']);
            
            // Busca dinamicamente a Role/Perfil vinculada a este usuário na tabela de ligação (user_roles)
            $sqlRole = "SELECT r.id_role, r.nome as role_nome 
                        FROM user_roles ur
                        JOIN roles r ON ur.id_role = r.id_role
                        WHERE ur.id_usuario = :id_usuario 
                        LIMIT 1";
            
            $stmtRole = $this->db->prepare($sqlRole);
            $stmtRole->execute([':id_usuario' => $user['id_usuario']]);
            $roleData = $stmtRole->fetch(PDO::FETCH_ASSOC);

            // Injeta os dados da Role recuperados de forma relacional dentro do array de usuário
            $user['id_role'] = $roleData ? $roleData['id_role'] : null;
            $user['role_nome'] = $roleData ? $roleData['role_nome'] : 'Usuário';

            return $user;
        }

        return false;
    }

    /**
     * Inicia a sessão oficial do usuário no PHP populando a memória global global
     */
    public function loginSession(array $user): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['usuario_id'] = $user['usuarioId'];
        $_SESSION['usuario_nome'] = $user['nome'] ?? $user['login'] ?? '';
        $_SESSION['usuario_email'] = $user['email'];
        $_SESSION['usuario_role'] = $user['id_role'];
        $_SESSION['usuario_role_nome'] = $user['role_nome'] ?? 'Usuário';
        
        // Regenera o ID da sessão para mitigar ataques de Session Fixation
        session_regenerate_id(true);
    }

    /**
     * Verifica de forma rápida se o usuário está logado
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
            if (!file_exists($redirectUrl) && file_exists('../' . $redirectUrl)) {
                $redirectUrl = '../' . $redirectUrl;
            }
            header("Location: " . $redirectUrl);
            exit;
        }
    }

    /**
     * Método Legado / Compatibilidade: Valida autenticação e barra o acesso se falhar.
     */
    public static function checkAndFail(string $redirectUrl = 'login.php'): void {
        self::requireAuth($redirectUrl);
    }

    /**
     * Método Legado / Compatibilidade: Retorna se o usuário está autenticado
     */
    public static function checkUrl(): bool {
        return self::checkAuth();
    }

    /**
     * Verifica se o usuário logado possui uma permissão (recurso) específica
     * @param string $recursoChave O slug ou chave do recurso (ex: 'cargos_editar')
     */
    public function temPermissao(string $recursoChave): bool {
        if (!self::checkAuth()) {
            return false;
        }

        $idRole = $_SESSION['usuario_role'] ?? null;
        if (!$idRole) {
            return false;
        }

        // Se for administrador master (id_role = 1), concede acesso irrestrito
        if ((int)$idRole === 1) {
            return true;
        }

        // Consulta se a Role mapeada na sessão possui vínculo com a chave do privilégio
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
     * Exige uma permissão específica. Se não tiver, bloqueia o acesso imediatamente.
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
     * Desloga o usuário limpando as variáveis globais e destruindo o cookie ativo
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
}