<?php
// Arquivo: src/Repository/FamiliaCboRepository.php (Atualizado com Auditoria)

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;  // <-- PASSO 1: Incluir
use App\Service\AuthService;   // <-- PASSO 1: Incluir
use PDO;
use Exception;

class FamiliaCboRepository
{
    private PDO $pdo;
    private AuditService $auditService; // <-- PASSO 2: Adicionar propriedade
    private AuthService $authService;   // <-- PASSO 2: Adicionar propriedade

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        // ======================================================
        // PASSO 2: Inicializar os serviços
        // ======================================================
        $this->auditService = new AuditService();
        $this->authService = new AuthService();
    }

    /**
     * Busca uma família pelo ID.
     */
    public function find(int $id)
    {
        // Apenas quem pode gerenciar pode buscar os dados
        $this->authService->checkAndFail('cadastros:manage');
        
        $stmt = $this->pdo->prepare("SELECT * FROM familia_cbo WHERE familiaCboId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca todos as famílias (para o <select> no form de CBO).
     */
    public function findAllForLookup(): array
    {
        $stmt = $this->pdo->query("SELECT familiaCboId, familiaCboNome FROM familia_cbo ORDER BY familiaCboNome ASC");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Salva (cria ou atualiza) uma Família de CBO.
     */
    public function save(array $data): int
    {
        $tableName = 'familia_cbo';

        // 1. Coleta de Dados
        $id = (int)($data['familiaCboId'] ?? 0);
        $nome = trim($data['familiaCboNome'] ?? '');
        $isUpdating = $id > 0;
        
        // 2. Validação de Permissão e Dados
        $permissionNeeded = $isUpdating ? 'cadastros:manage' : 'cadastros:manage';
        $this->authService->checkAndFail($permissionNeeded);
        
        if (empty($nome)) {
            throw new Exception("O nome da família é obrigatório.");
        }
        
        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET familiaCboNome = :nome WHERE familiaCboId = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':nome' => $nome, ':id' => $id]);
                $savedId = $id;
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE UPDATE
                // ======================================================
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
                
            } else {
                $sql = "INSERT INTO {$tableName} (familiaCboNome) VALUES (:nome)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':nome' => $nome]);
                $savedId = (int)$this->pdo->lastInsertId();
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE CREATE
                // ======================================================
                $this->auditService->log('CREATE', $tableName, $savedId, $data);
            }
            
            return $savedId;

        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                throw new Exception("A família '$nome' já existe.");
            }
            throw $e;
        }
    }

    /**
     * Exclui uma Família de CBO.
     */
    public function delete(int $id): bool
    {
        $tableName = 'familia_cbo';
        
        $this->authService->checkAndFail('cadastros:manage');
        
        try {
            // Nota: A verificação de FK (uso na tabela 'cbos')
            //
            // é tratada pelo catch (PDOException) abaixo.
            
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE familiaCboId = ?");
            $stmt->execute([$id]);
            
            $success = $stmt->rowCount() > 0;

            if ($success) {
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE DELETE
                // ======================================================
                $this->auditService->log('DELETE', $tableName, $id, ['deletedId' => $id]);
            }
            
            return $success;

        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1451) { // Foreign key constraint
                throw new Exception("Esta família não pode ser excluída pois está sendo utilizada em um ou mais CBOs.");
            }
            throw $e;
        }
    }

    /**
     * Busca famílias de forma paginada, com filtro.
     */
    public function findAllPaginated(array $params = []): array
    {
        $itemsPerPage = (int)($params['limit'] ?? 15);
        $currentPage = (int)($params['page'] ?? 1);
        $offset = ($currentPage - 1) * $itemsPerPage;
        $term = $params['term'] ?? '';
        $sqlTerm = "%{$term}%";
        
        $where = "";
        $bindings = [];

        if (!empty($term)) {
            $where = " WHERE familiaCboNome LIKE :term";
            $bindings[':term'] = $sqlTerm;
        }
        
        // Count total
        $countSql = "SELECT COUNT(*) FROM familia_cbo" . $where;
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($bindings);
        $totalRecords = (int)$countStmt->fetchColumn();
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;

        // Data query
        $dataSql = "SELECT * FROM familia_cbo" . $where;
        
        // Order by
        $sort_col = $params['sort_col'] ?? 'familiaCboNome';
        $sort_dir = $params['sort_dir'] ?? 'ASC';
        $validColumns = ['familiaCboId', 'familiaCboNome', 'familiaCboDataAtualizacao'];
        $orderBy = in_array($sort_col, $validColumns) ? $sort_col : 'familiaCboNome';
        $sortDir = in_array(strtoupper($sort_dir), ['ASC', 'DESC']) ? strtoupper($sort_dir) : 'ASC';
        $dataSql .= " ORDER BY $orderBy $sortDir";
        
        $dataSql .= " LIMIT :limit OFFSET :offset";
        $bindings[':limit'] = $itemsPerPage;
        $bindings[':offset'] = $offset;

        $stmt = $this->pdo->prepare($dataSql);
        
        foreach ($bindings as $key => &$val) {
            if ($key == ':limit' || $key == ':offset') {
                $stmt->bindParam($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindParam($key, $val);
            }
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