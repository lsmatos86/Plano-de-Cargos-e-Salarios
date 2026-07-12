<?php
// Arquivo: src/Service/AuthService.php

namespace App\Service;

use App\Core\Database;
use PDO;

<<<<<<< HEAD
=======
/**
 * Classe para gerenciar Autenticação e Autorização (Permissões).
 * (Versão corrigida com nomes de colunas em Português e lógica de URL)
 */
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
class AuthService
{
    private ?PDO $db;
    private ?array $userPermissions = null;

    public function __construct()
    {
        $this->db = Database::getConnection();
        
        // ==================================================================
        // CORREÇÃO 1: Garante que a sessão esteja sempre iniciada
        // ==================================================================
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function userCan(string $permissionName): bool
    {
        $usuarioId = $_SESSION['user_id'] ?? null;
        if ($usuarioId === null) {
            return false;
        }

        if ($this->userPermissions === null) {
            $this->loadUserPermissions($usuarioId);
        }

<<<<<<< HEAD
=======
        // 2. Verifica se a permissão existe no array (formato ['perm' => true])
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
        return isset($this->userPermissions[$permissionName]);
    }

    private function loadUserPermissions(int $usuarioId): void
    {
        $this->userPermissions = [];
        
        $sql = 'SELECT DISTINCT p."permissionName"
                FROM permissions p
<<<<<<< HEAD
                JOIN role_permissions rp ON p."permissionId" = rp."permissionId"
                JOIN user_roles ur ON rp."roleId" = ur."roleId"
                WHERE ur."usuarioId" = :usuarioId';
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
=======
                JOIN role_permissions rp ON p.permissionId = rp.permissionId
                JOIN user_roles ur ON rp.roleId = ur.roleId
                WHERE ur.usuarioId = :usuarioId"; //
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT); //
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $stmt->execute();
            
            $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->userPermissions = array_flip($permissions);

        } catch (\Exception $e) {
            error_log('Falha ao carregar permissões: ' . $e->getMessage());
            $this->userPermissions = [];
        }
    }

<<<<<<< HEAD
=======
    /**
     * Força o recarregamento das permissões (ex: após mudar o papel do usuário)
     */
    // ==================================================================
    // CORREÇÃO 2: Removido o "publicS public" duplicado
    // ==================================================================
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
    public function refreshPermissions(): void
    {
        $this->userPermissions = null;
        $usuarioId = $_SESSION['user_id'] ?? null;
        if ($usuarioId) {
            $this->loadUserPermissions($usuarioId);
        }
    }

    public function checkAndFail(string $permissionName, ?string $redirectUrl = null): void
    {
        if ($this->userCan($permissionName)) {
            return;
        }

<<<<<<< HEAD
=======
        // ==================================================================
        // CORREÇÃO 3: Lógica de redirecionamento corrigida
        // ==================================================================
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
        if ($redirectUrl) {
            
            // Mensagem de erro padrão que os Controllers esperam
            $errorMessage = urlencode("Acesso negado. Você não tem permissão para esta ação.");
            
            // Limpa o 'error=Acesso+negado' antigo se ele existir na URL base
            $redirectUrl = str_replace("?error=Acesso+negado", "", $redirectUrl);
            $redirectUrl = str_replace("&error=Acesso+negado", "", $redirectUrl);
            
            // Determina o separador correto ('?' ou '&')
            $separator = (strpos($redirectUrl, '?') === false) ? '?' : '&';
            
            // Constrói a URL final corretamente (usando message e type)
            $location = "{$redirectUrl}{$separator}message={$errorMessage}&type=danger";
            
            header("Location: $location");
            exit;
        } else {
            throw new \Exception('Acesso negado. Você não tem permissão para esta ação.');
        }
    }
}
