<?php
// Arquivo: src/Service/AuthService.php

namespace App\Service;

use App\Core\Database;
use PDO;

class AuthService
{
    private ?PDO $db;
    private ?array $userPermissions = null;

    public function __construct()
    {
        $this->db = Database::getConnection();
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

        return isset($this->userPermissions[$permissionName]);
    }

    private function loadUserPermissions(int $usuarioId): void
    {
        $this->userPermissions = [];
        
        $sql = 'SELECT DISTINCT p."permissionName"
                FROM permissions p
                JOIN role_permissions rp ON p."permissionId" = rp."permissionId"
                JOIN user_roles ur ON rp."roleId" = ur."roleId"
                WHERE ur."usuarioId" = :usuarioId';
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            
            $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->userPermissions = array_flip($permissions);

        } catch (\Exception $e) {
            error_log('Falha ao carregar permissões: ' . $e->getMessage());
            $this->userPermissions = [];
        }
    }

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

        if ($redirectUrl) {
            header("Location: $redirectUrl?error=" . urlencode('Acesso negado'));
            exit;
        } else {
            throw new \Exception('Acesso negado. Você não tem permissão para esta ação.');
        }
    }
}
