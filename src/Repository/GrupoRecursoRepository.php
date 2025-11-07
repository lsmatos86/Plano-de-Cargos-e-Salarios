<?php
// Arquivo: src/Repository/GrupoRecursoRepository.php

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;
use App\Service\AuthService;
use PDO;
use Exception;

class GrupoRecursoRepository
{
    private PDO $pdo;
    private AuditService $auditService;
    private AuthService $authService;
    private $tableName = 'recursos_grupos';
    private $idColumn = 'recursoGrupoId';
    private $nameColumn = 'recursoGrupoNome';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->auditService = new AuditService();
        $this->authService = new AuthService();
    }

    // ======================================================
    // MÉTODOS DE BUSCA E LISTAGEM
    // ======================================================

    /**
     * Retorna todos os grupos de recurso de forma simples (id, nome) para SELECTs.
     */
    public function findAllSimple(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT {$this->idColumn} AS id, {$this->nameColumn} AS nome FROM {$this->tableName} ORDER BY {$this->nameColumn} ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Grupos de Recurso (simples): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca Grupos de Recurso de forma paginada para a listagem principal.
     */
    public function findAllPaginated(array $params = []): array
    {
        $itemsPerPage = (int)($params['limit'] ?? 10);
        $currentPage = (int)($params['page'] ?? 1);
        $term = $params['term'] ?? '';
        
        $sortCol = in_array($params['order_by'] ?? $this->nameColumn, [$this->nameColumn, $this->idColumn]) ? $params['order_by'] : $this->nameColumn;
        $sortDir = in_array(strtoupper($params['sort_dir'] ?? 'ASC'), ['ASC', 'DESC']) ? $params['sort_dir'] : 'ASC';

        $termParam = "%{$term}%";
        $bindings = [$termParam];
        $where = " WHERE t.{$this->nameColumn} LIKE ?";

        // 1. Count total
        $countSql = "SELECT COUNT(t.{$this->idColumn}) FROM {$this->tableName} t" . $where;

        try {
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute($bindings);
            $totalRecords = (int)$countStmt->fetchColumn();

            $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
            $currentPage = max(1, min($currentPage, $totalPages));
            $offset = ($currentPage - 1) * $itemsPerPage;
            
            // 2. Data query
            $sql = "
                SELECT 
                    t.recursoGrupoId, t.recursoGrupoNome, t.recursoGrupoDescricao
                FROM {$this->tableName} t
                {$where}
                ORDER BY {$sortCol} {$sortDir} 
                LIMIT ? OFFSET ?
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $termParam);
            $stmt->bindParam(2, $itemsPerPage, PDO::PARAM_INT);
            $stmt->bindParam(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $data,
                'total' => $totalRecords,
                'totalPages' => $totalPages,
                'currentPage' => $currentPage
            ];

        } catch (\PDOException $e) {
            error_log("Erro em GrupoRecursoRepository::findAllPaginated: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'totalPages' => 1, 'currentPage' => 1];
        }
    }
    
    public function find(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->tableName} WHERE {$this->idColumn} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // ======================================================
    // MÉTODOS DE CRUD
    // ======================================================

    public function save(array $data): int
    {
        $id = (int)($data[$this->idColumn] ?? 0);
        $nome = trim($data[$this->nameColumn] ?? '');
        $descricao = trim($data['recursoGrupoDescricao'] ?? null);
        $isUpdating = $id > 0;

        $permissionNeeded = $isUpdating ? 'cadastros:manage' : 'cadastros:create';
        $this->authService->checkAndFail($permissionNeeded);

        if (empty($nome)) {
            throw new Exception("O nome do Grupo de Recurso é obrigatório.");
        }

        $params = [
            ':nome' => $nome,
            ':descricao' => $descricao
        ];

        try {
            if ($isUpdating) {
                $sql = "UPDATE {$this->tableName} SET {$this->nameColumn} = :nome, recursoGrupoDescricao = :descricao WHERE {$this->idColumn} = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                $this->auditService->log('UPDATE', $this->tableName, $savedId, $data);
                
            } else {
                $sql = "INSERT INTO {$this->tableName} ({$this->nameColumn}, recursoGrupoDescricao) VALUES (:nome, :descricao)";
                $this->pdo->prepare($sql)->execute($params);
                $savedId = (int)$this->pdo->lastInsertId();
                
                $this->auditService->log('CREATE', $this->tableName, $savedId, $data);
            }
            
            return $savedId;

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                 throw new Exception("O Grupo de Recurso '$nome' já existe.");
            }
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $this->authService->checkAndFail('cadastros:manage');

        try {
            // Verifica se o grupo está sendo usado em um cargo
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM recursos_grupos_cargo WHERE recursoGrupoId = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Este Grupo de Recurso não pode ser excluído pois está associado a um ou mais Cargos.");
            }

            // Exclui
            $stmt = $this->pdo->prepare("DELETE FROM {$this->tableName} WHERE {$this->idColumn} = ?");
            $stmt->execute([$id]);
            
            $success = $stmt->rowCount() > 0;
            
            if ($success) {
                $this->auditService->log('DELETE', $this->tableName, $id, ['deletedId' => $id]);
            }
            
            return $success;

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                 throw new Exception("Este Grupo de Recurso não pode ser excluído pois está em uso em outra parte do sistema.");
            }
            throw $e;
        }
    }
}