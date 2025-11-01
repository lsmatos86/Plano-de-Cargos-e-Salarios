<?php
// Arquivo: src/Repository/UsuarioRepository.php

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;
use App\Service\AuthService;
use PDO;
use Exception;

class UsuarioRepository
{
    private PDO $pdo;
    private AuthService $authService;
    private AuditService $auditService;
    private RoleRepository $roleRepo; // Repositório de Papéis

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->authService = new AuthService();
        $this->auditService = new AuditService();
        $this->roleRepo = new RoleRepository(); // Para buscar a lista de papéis
    }

    /**
     * Busca um usuário pelo ID.
     * @param int $usuarioId
     * @return array|false
     */
    public function find(int $usuarioId)
    {
        // Apenas quem pode gerenciar usuários pode buscar os dados
        $this->authService->checkAndFail('usuarios:manage');
        
        $stmt = $this->pdo->prepare("SELECT usuarioId, nome, email, ativo FROM usuarios WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca os IDs dos papéis associados a um usuário.
     * @param int $usuarioId
     * @return array
     */
    public function findRoleIds(int $usuarioId): array
    {
        $this->authService->checkAndFail('usuarios:manage');
        
        $stmt = $this->pdo->prepare("SELECT roleId FROM user_roles WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Busca todos os papéis disponíveis (para o formulário).
     * @return array
     */
    public function getAllRoles(): array
    {
        // Reutiliza o método do RoleRepository
        return $this->roleRepo->findAll();
    }

    /**
     * Salva (cria ou atualiza) um usuário e seus papéis.
     * @param array $data ($_POST)
     * @return int ID do usuário salvo
     * @throws Exception
     */
    public function save(array $data): int
    {
        // 1. Permissão
        $this->authService->checkAndFail('usuarios:manage');

        // 2. Validação e Coleta de Dados
        $usuarioId = (int)($data['usuarioId'] ?? 0);
        $isEditing = $usuarioId > 0;
        $nome = trim($data['nome'] ?? '');
        $email = trim($data['email'] ?? '');
        $senha = trim($data['senha'] ?? '');
        $ativo = (int)(isset($data['ativo'])); // 1 se 'on', 0 se ausente
        $roleIds = $data['roleIds'] ?? []; // Papéis selecionados

        if (empty($nome) || empty($email)) {
            throw new Exception("Nome e E-mail são obrigatórios.");
        }
        
        // Validação de senha: obrigatória apenas se for um NOVO usuário
        if (!$isEditing && empty($senha)) {
            throw new Exception("A senha é obrigatória para novos usuários.");
        }

        $this->pdo->beginTransaction();
        try {
            // 3. Prepara os dados para o SQL
            $params = [
                'nome' => $nome,
                'email' => $email,
                'ativo' => $ativo
            ];

            if ($isEditing) {
                // UPDATE
                $sql = "UPDATE usuarios SET nome = :nome, email = :email, ativo = :ativo";
                // Só atualiza a senha se uma nova foi fornecida
                if (!empty($senha)) {
                    $params['senha'] = password_hash($senha, PASSWORD_DEFAULT);
                    $sql .= ", senha = :senha";
                }
                $sql .= " WHERE usuarioId = :usuarioId";
                $params['usuarioId'] = $usuarioId;
                
                $this->pdo->prepare($sql)->execute($params);
                
                // Log de Auditoria
                $this->auditService->log('UPDATE', 'usuarios', $usuarioId, $data);
            } else {
                // INSERT
                $params['senha'] = password_hash($senha, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuarios (nome, email, senha, ativo) VALUES (:nome, :email, :senha, :ativo)";
                $this->pdo->prepare($sql)->execute($params);
                $usuarioId = (int)$this->pdo->lastInsertId();
                
                // Log de Auditoria
                $this->auditService->log('CREATE', 'usuarios', $usuarioId, $data);
            }

            // 4. Sincroniza os Papéis na tabela user_roles
            
            // 4.1. Remove papéis antigos
            $this->pdo->prepare("DELETE FROM user_roles WHERE usuarioId = ?")->execute([$usuarioId]);

            // 4.2. Insere os novos papéis
            if (!empty($roleIds)) {
                $sql_role = "INSERT INTO user_roles (usuarioId, roleId) VALUES (?, ?)";
                $stmt_role = $this->pdo->prepare($sql_role);
                foreach ($roleIds as $roleId) {
                    $stmt_role->execute([$usuarioId, (int)$roleId]);
                }
            }

            $this->pdo->commit();
            return $usuarioId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Verifica se é erro de e-mail duplicado
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                 throw new Exception("O e-mail '$email' já está cadastrado.");
            }
            throw new Exception("Erro ao salvar usuário: " . $e->getMessage());
        }
    }

    /**
     * Exclui um usuário.
     * @param int $usuarioId
     * @return bool
     * @throws Exception
     */
    public function delete(int $usuarioId): bool
    {
        // 1. Permissão
        $this->authService->checkAndFail('usuarios:manage');
        
        // 2. Proteção: Não deixa excluir o usuário ID 1 (admin)
        if ($usuarioId === 1) {
            throw new Exception("Não é possível excluir o usuário Administrador principal.");
        }
        
        // 3. Proteção: Não deixa o usuário excluir a si mesmo
        $loggedUserId = $_SESSION['user_id'] ?? 0;
        if ($usuarioId === $loggedUserId) {
            throw new Exception("Você não pode excluir sua própria conta.");
        }

        $this->pdo->beginTransaction();
        try {
            // 4. Remove associações de papéis
            $this->pdo->prepare("DELETE FROM user_roles WHERE usuarioId = ?")->execute([$usuarioId]);
            
            // 5. Remove o usuário
            $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE usuarioId = ?");
            $stmt->execute([$usuarioId]);
            
            $success = $stmt->rowCount() > 0;
            $this->pdo->commit();
            
            if ($success) {
                // Log de Auditoria
                $this->auditService->log('DELETE', 'usuarios', $usuarioId, ['deletedUserId' => $usuarioId]);
            }
            
            return $success;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erro ao excluir usuário: " . $e->getMessage());
        }
    }

    /**
     * Busca usuários de forma paginada (modificado para incluir papéis).
     * @param array $params (term, page, limit)
     * @return array
     */
    public function findAllPaginated(array $params = []): array
    {
        $this->authService->checkAndFail('usuarios:manage');

        $itemsPerPage = (int)($params['limit'] ?? 10);
        $currentPage = (int)($params['page'] ?? 1);
        $offset = ($currentPage - 1) * $itemsPerPage;
        $term = $params['term'] ?? '';
        $sqlTerm = "%{$term}%";
        
        $bindings = [];
        
        // Query de Contagem
        $countSql = "SELECT COUNT(DISTINCT u.usuarioId) FROM usuarios u";
        if (!empty($term)) {
            $countSql .= " WHERE u.nome LIKE ? OR u.email LIKE ?";
            $bindings[] = $sqlTerm;
            $bindings[] = $sqlTerm;
        }
        
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($bindings);
        $totalRecords = (int)$countStmt->fetchColumn();
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;

        // Query Principal (com JOIN para papéis)
        $dataSql = "
            SELECT 
                u.usuarioId, u.nome, u.email, u.ativo,
                (SELECT GROUP_CONCAT(r.roleName SEPARATOR ', ') 
                 FROM roles r
                 JOIN user_roles ur ON r.roleId = ur.roleId
                 WHERE ur.usuarioId = u.usuarioId) AS papeis
            FROM usuarios u
        ";
        
        if (!empty($term)) {
            $dataSql .= " WHERE u.nome LIKE ? OR u.email LIKE ?";
        }
        
        $dataSql .= " ORDER BY u.nome ASC LIMIT ? OFFSET ?";
        $bindings[] = $itemsPerPage;
        $bindings[] = $offset;
        
        $stmt = $this->pdo->prepare($dataSql);
        
        // Bind dos parâmetros por tipo
        $index = 1;
        foreach ($bindings as $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($index++, $value, $type);
        }
        
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $registros,
            'total' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage
        ];
    }
}