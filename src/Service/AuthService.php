<?php
// Arquivo: src/Service/AuthService.php

namespace App\Service;

use App\Core\Database;
use PDO;

/**
 * Classe para gerenciar Autenticação e Autorização (Permissões).
 */
class AuthService
{
    private ?PDO $db;
    private ?array $userPermissions = null; // Cache de permissões do usuário

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Verifica se o usuário logado atualmente tem uma permissão específica.
     *
     * @param string $permissionName O nome da permissão (ex: 'cargos:edit')
     * @return bool
     */
    public function userCan(string $permissionName): bool
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null) {
            return false; // Não logado, não pode fazer nada
        }

        // 1. Carrega as permissões do usuário (com cache)
        if ($this->userPermissions === null) {
            $this->loadUserPermissions($userId);
        }

        // 2. Verifica se a permissão existe no array
        // Usamos array_key_exists para performance
        return isset($this->userPermissions[$permissionName]);
    }

    /**
     * Carrega todas as permissões do usuário logado e as armazena
     * na propriedade $userPermissions para cache.
     */
    private function loadUserPermissions(int $userId): void
    {
        $this->userPermissions = [];
        
        $sql = "SELECT DISTINCT p.permissionName
                FROM permissions p
                JOIN role_permissions rp ON p.permissionId = rp.permissionId
                JOIN user_roles ur ON rp.roleId = ur.roleId
                WHERE ur.userId = :userId";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Transforma ['cargos:edit', 'cargos:view']
            // em ['cargos:edit' => true, 'cargos:view' => true]
            // para buscas rápidas com isset()
            $this->userPermissions = array_flip($permissions);

        } catch (\Exception $e) {
            error_log('Falha ao carregar permissões: ' . $e->getMessage());
            $this->userPermissions = []; // Falha segura (sem permissões)
        }
    }

    /**
     * Força o recarregamento das permissões (ex: após mudar o papel do usuário)
     */
    public function refreshPermissions(): void
    {
        $this->userPermissions = null;
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $this->loadUserPermissions($userId);
        }
    }

    /**
     * Função helper para verificar a permissão e, se falhar,
     * redirecionar ou lançar uma exceção.
     *
     * @param string $permissionName
     * @param string|null $redirectUrl (Opcional) URL para redirecionar
     */
    public function checkAndFail(string $permissionName, ?string $redirectUrl = null): void
    {
        if ($this->userCan($permissionName)) {
            return; // OK
        }

        // Falhou
        if ($redirectUrl) {
            header("Location: $redirectUrl?error=" . urlencode('Acesso negado'));
            exit;
        } else {
            // Lança uma exceção que pode ser tratada
            throw new \Exception('Acesso negado. Você não tem permissão para esta ação.');
        }
    }
}