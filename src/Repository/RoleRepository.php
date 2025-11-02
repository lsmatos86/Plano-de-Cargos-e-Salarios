<?php
// Arquivo: src/Repository/RoleRepository.php
//

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;
use App\Service\AuthService;
use PDO;
use Exception;

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
     * Busca um papel pelo ID.
     */
    public function find(int $roleId)
    {
        $this->authService->checkAndFail('usuarios:manage');
        
        $stmt = $this->pdo->prepare("SELECT * FROM roles WHERE roleId = ?");
        $stmt->execute([$roleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca todos os papéis (para lookups).
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM roles ORDER BY roleName ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca todas as permissões disponíveis no sistema.
     */
    public function getAllPermissions(): array
    {
        $this->authService->checkAndFail('usuarios:manage');
        
        $stmt = $this->pdo->query("SELECT * FROM permissions ORDER BY permissionName ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca os IDs das permissões associadas a um papel.
     */
    public function findPermissionIds(int $roleId): array
    {
        $this->authService->checkAndFail('usuarios:manage');
        
        $stmt = $this->pdo->prepare("SELECT permissionId FROM role_permissions WHERE roleId = ?");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Salva (cria ou atualiza) um papel e suas permissões.
     */
    public function save(array $data): int
    {
        $this->authService->checkAndFail('usuarios:manage');

        $roleId = (int)($data['roleId'] ?? 0);
        $isEditing = $roleId > 0;
        $nome = trim($data['roleName'] ?? '');
        $descricao = trim($data['roleDescription'] ?? null);
        $permissionIds = $data['permissionIds'] ?? [];

        if (empty($nome)) {
            throw new Exception("O nome do papel é obrigatório.");
        }

        $this->pdo->beginTransaction();
        try {
            // 1. Salva o Papel (Role)
            if ($isEditing) {
                $sql = "UPDATE roles SET roleName = :nome, roleDescription = :descricao WHERE roleId = :id";
                $this->pdo->prepare($sql)->execute([
                    ':nome' => $nome,
                    ':descricao' => $descricao,
                    ':id' => $roleId
                ]);
                
                $this->auditService->log('UPDATE', 'roles', $roleId, $data);
            } else {
                $sql = "INSERT INTO roles (roleName, roleDescription) VALUES (:nome, :descricao)";
                $this->pdo->prepare($sql)->execute([
                    ':nome' => $nome,
                    ':descricao' => $descricao
                ]);
                $roleId = (int)$this->pdo->lastInsertId();
                
                $this->auditService->log('CREATE', 'roles', $roleId, $data);
            }

            // 2. Sincroniza as Permissões
            // 2.1. Remove permissões antigas
            $this->pdo->prepare("DELETE FROM role_permissions WHERE roleId = ?")->execute([$roleId]);

            // 2.2. Insere as novas permissões
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
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                 throw new Exception("O papel '$nome' já está cadastrado.");
            }
            throw new Exception("Erro ao salvar o papel: " . $e->getMessage());
        }
    }

    /**
     * Exclui um papel.
     */
    public function delete(int $roleId): bool
    {
        $this->authService->checkAndFail('usuarios:manage');
        
        // Proteção: Não deixa excluir os papéis padrão
        if ($roleId === 1 || $roleId === 2) { // Ex: 1=Admin, 2=Usuário
            throw new Exception("Não é possível excluir os papéis padrão do sistema.");
        }

        $this->pdo->beginTransaction();
        try {
            // 1. Remove associações de permissões
            $this->pdo->prepare("DELETE FROM role_permissions WHERE roleId = ?")->execute([$roleId]);
            
            // 2. Remove associações de usuários (importante!)
            //
            $this->pdo->prepare("DELETE FROM user_roles WHERE roleId = ?")->execute([$roleId]);

            // 3. Remove o papel
            $stmt = $this->pdo->prepare("DELETE FROM roles WHERE roleId = ?");
            $stmt->execute([$roleId]);
            
            $success = $stmt->rowCount() > 0;
            $this->pdo->commit();
            
            if ($success) {
                $this->auditService->log('DELETE', 'roles', $roleId, ['deletedRoleId' => $roleId]);
            }
            
            return $success;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erro ao excluir o papel: " . $e->getMessage());
        }
    }
}