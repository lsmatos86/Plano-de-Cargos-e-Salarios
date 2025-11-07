<?php
// Arquivo: src/Service/AuthService.php

namespace App\Service;

use App\Core\Database;
use PDO;

/**
 * Classe para gerenciar Autenticação e Autorização (Permissões).
 * (Versão corrigida com nomes de colunas em Português e lógica de URL)
 */
class AuthService
{
    private ?PDO $db;
    private ?array $userPermissions = null; // Cache de permissões do usuário

    public function __construct()
    {
        // Pega a conexão PDO da nossa classe de Database
        $this->db = Database::getConnection();
        
        // ==================================================================
        // CORREÇÃO 1: Garante que a sessão esteja sempre iniciada
        // ==================================================================
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Verifica se o usuário logado atualmente tem uma permissão específica.
     *
     * @param string $permissionName O nome da permissão (ex: 'cargos:edit')
     * @return bool
     */
    public function userCan(string $permissionName): bool
    {
        // Usa o 'user_id' da sessão, como definido em functions.php
        $usuarioId = $_SESSION['user_id'] ?? null;
        if ($usuarioId === null) {
            return false; // Não logado, não pode fazer nada
        }

        // 1. Carrega as permissões do usuário (com cache)
        if ($this->userPermissions === null) {
            $this->loadUserPermissions($usuarioId);
        }

        // 2. Verifica se a permissão existe no array (formato ['perm' => true])
        return isset($this->userPermissions[$permissionName]);
    }

    /**
     * Carrega todas as permissões do usuário logado e as armazena
     * na propriedade $userPermissions para cache.
     */
    private function loadUserPermissions(int $usuarioId): void
    {
        $this->userPermissions = [];
        
        // SQL CORRIGIDO: Usa 'ur.usuarioId' para corresponder à tabela user_roles
        $sql = "SELECT DISTINCT p.permissionName
                FROM permissions p
                JOIN role_permissions rp ON p.permissionId = rp.permissionId
                JOIN user_roles ur ON rp.roleId = ur.roleId
                WHERE ur.usuarioId = :usuarioId"; //
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT); //
            $stmt->execute();
            
            $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Transforma ['cargos:edit', 'cargos:view']
            // em ['cargos:edit' => true, 'cargos:view' => true]
            $this->userPermissions = array_flip($permissions);

        } catch (\Exception $e) {
            error_log('Falha ao carregar permissões: ' . $e->getMessage());
            $this->userPermissions = []; // Falha segura (sem permissões)
        }
    }

    /**
     * Força o recarregamento das permissões (ex: após mudar o papel do usuário)
     */
    // ==================================================================
    // CORREÇÃO 2: Removido o "publicS public" duplicado
    // ==================================================================
    public function refreshPermissions(): void
    {
        $this->userPermissions = null;
        $usuarioId = $_SESSION['user_id'] ?? null;
        if ($usuarioId) {
            $this->loadUserPermissions($usuarioId);
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

        // ==================================================================
        // CORREÇÃO 3: Lógica de redirecionamento corrigida
        // ==================================================================
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
            // Lança uma exceção que pode ser tratada
            throw new \Exception('Acesso negado. Você não tem permissão para esta ação.');
        }
    }
}