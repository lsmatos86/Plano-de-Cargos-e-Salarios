<?php
// Arquivo: src/Repository/RoleRepository.php

namespace App\Repository;

use App\Core\Database;
use App\Service\AuthService;
use App\Service\AuditService;
use PDO;
use Exception;

/**
 * Lida com as operações de banco de dados para Papéis (Roles) e Permissões.
 */
class RoleRepository
{
    private PDO $pdo;
    private AuthService $authService;
    private AuditService $auditService;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->authService = new AuthService();
        $this->auditService = new AuditService();
    }

    /**
     * Busca todos os papéis cadastrados.
     * @return array
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM roles ORDER BY roleName ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um papel específico pelo ID.
     * @param int $roleId
     * @return array|false
     */
    public function find(int $roleId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM roles WHERE roleId = ?");
        $stmt->execute([$roleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca todas as permissões disponíveis no sistema.
     * @return array
     */
    public function getAllPermissions(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM permissions ORDER BY permissionName ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca os IDs das permissões associadas a um papel específico.
     * @param int $roleId
     * @return array Um array simples de IDs [1, 5, 12]
     */
    public function getPermissionIdsForRole(int $roleId): array
    {
        $stmt = $this->pdo->prepare("SELECT permissionId FROM role_permissions WHERE roleId = ?");
        $stmt->execute([$roleId]);
        // Retorna um array "flat" [1, 2, 3] em vez de [[id=>1], [id=>2]]
        return $stmt->fetchAll(PDO::FETCH_COLUMN); 
    }

    /**
     * Salva (cria ou atualiza) um papel e suas permissões.
     * @param array $data ($_POST)
     * @return int ID do papel salvo
     * @throws Exception
     */
    public function save(array $data): int
    {
        // 1. Permissão: Apenas quem pode gerenciar usuários pode salvar papéis
        $this->authService->checkAndFail('usuarios:manage');

        // 2. Validação
        $roleName = trim($data['roleName'] ?? '');
        $roleDescription = trim($data['roleDescription'] ?? '');
        $roleId = (int)($data['roleId'] ?? 0);
        $permissionIds = $data['permissionIds'] ?? []; // Array de IDs de permissão

        if (empty($roleName)) {
            throw new Exception("O nome do papel é obrigatório.");
        }

        $this->pdo->beginTransaction();
        try {
            // 3. Salva o Papel (INSERT ou UPDATE)
            if ($roleId > 0) {
                // UPDATE
                $sql = "UPDATE roles SET roleName = ?, roleDescription = ? WHERE roleId = ?";
                $this->pdo->prepare($sql)->execute([$roleName, $roleDescription, $roleId]);
                
                // Log de Auditoria
                $this->auditService->log('UPDATE', 'roles', $roleId, $data);
            } else {
                // INSERT
                $sql = "INSERT INTO roles (roleName, roleDescription) VALUES (?, ?)";
                $this->pdo->prepare($sql)->execute([$roleName, $roleDescription]);
                $roleId = (int)$this->pdo->lastInsertId();
                
                // Log de Auditoria
                $this->auditService->log('CREATE', 'roles', $roleId, $data);
            }

            // 4. Sincroniza as Permissões na tabela role_permissions
            
            // 4.1. Remove permissões antigas
            $stmtDel = $this->pdo->prepare("DELETE FROM role_permissions WHERE roleId = ?");
            $stmtDel->execute([$roleId]);

            // 4.2. Insere as novas permissões
            if (!empty($permissionIds)) {
                $sql_perm = "INSERT INTO role_permissions (roleId, permissionId) VALUES (?, ?)";
                $stmt_perm = $this->pdo->prepare($sql_perm);
                foreach ($permissionIds as $permId) {
                    $stmt_perm->execute([$roleId, (int)$permId]);
                }
            }

            $this->pdo->commit();
            return $roleId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erro ao salvar papel: " . $e->getMessage());
        }
    }

    /**
     * Exclui um papel.
     * @param int $roleId
     * @return bool
     * @throws Exception
     */
    public function delete(int $roleId): bool
    {
        // 1. Permissão
        $this->authService->checkAndFail('usuarios:manage');
        
        // Não permitir excluir o Papel de Administrador (ID 1)
        if ($roleId === 1) {
            throw new Exception("Não é possível excluir o papel de Administrador.");
        }

        $this->pdo->beginTransaction();
        try {
            // 2. Remove associações com usuários
            $this->pdo->prepare("DELETE FROM user_roles WHERE roleId = ?")->execute([$roleId]);
            
            // 3. Remove associações com permissões
            $this->pdo->prepare("DELETE FROM role_permissions WHERE roleId = ?")->execute([$roleId]);
            
            // 4. Remove o papel
            $stmt = $this->pdo->prepare("DELETE FROM roles WHERE roleId = ?");
            $stmt->execute([$roleId]);
            
            $success = $stmt->rowCount() > 0;
            
            $this->pdo->commit();
            
            if ($success) {
                // Log de Auditoria
                $this->auditService->log('DELETE', 'roles', $roleId, ['deletedRoleId' => $roleId]);
            }
            
            return $success;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erro ao excluir papel: " . $e->getMessage());
        }
    }
}