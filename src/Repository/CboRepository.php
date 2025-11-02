<?php
// Arquivo: src/Repository/CboRepository.php (Atualizado com Auditoria)

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;  // <-- PASSO 1: Incluir
use App\Service\AuthService;   // <-- PASSO 1: Incluir
use PDO;
use Exception;

class CboRepository
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
     * Busca um CBO pelo ID, incluindo o nome da família.
     */
    public function find(int $id)
    {
        // Apenas quem pode gerenciar pode buscar os dados
        $this->authService->checkAndFail('cadastros:manage');
        
        $stmt = $this->pdo->prepare("SELECT c.*, f.familiaCboNome FROM cbos c LEFT JOIN familia_cbo f ON f.familiaCboId = c.familiaCboId WHERE c.cboId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Salva (cria ou atualiza) um CBO.
     */
    public function save(array $data): int
    {
        $tableName = 'cbos';
        
        $id = (int)($data['cboId'] ?? 0);
        $isUpdating = $id > 0;

        // 1. Validação de Permissão
        $permissionNeeded = $isUpdating ? 'cadastros:manage' : 'cadastros:manage';
        $this->authService->checkAndFail($permissionNeeded);

        // 2. Coleta de Dados
        $params = [
            ':cboCod' => $data['cboCod'] ?? '',
            ':cboTituloOficial' => $data['cboTituloOficial'] ?? '',
            ':familiaCboId' => $data['familiaCboId'] ?? null
        ];

        if (empty($params[':cboCod']) || empty($params[':cboTituloOficial']) || empty($params[':familiaCboId'])) {
            throw new Exception("Código CBO, Título Oficial e Família CBO são obrigatórios.");
        }

        // 3. Execução
        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET cboCod = :cboCod, cboTituloOficial = :cboTituloOficial, familiaCboId = :familiaCboId WHERE cboId = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE UPDATE
                // ======================================================
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
                
            } else {
                $sql = "INSERT INTO {$tableName} (cboCod, cboTituloOficial, familiaCboId) VALUES (:cboCod, :cboTituloOficial, :familiaCboId)";
                $this->pdo->prepare($sql)->execute($params);
                $savedId = (int)$this->pdo->lastInsertId();
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE CREATE
                // ======================================================
                $this->auditService->log('CREATE', $tableName, $savedId, $data);
            }
            
            return $savedId;

        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                throw new Exception("O código CBO '{$params[':cboCod']}' já está cadastrado.");
            }
            throw $e;
        }
    }

    /**
     * Exclui um CBO.
     */
    public function delete(int $id): bool
    {
        $tableName = 'cbos';
        
        $this->authService->checkAndFail('cadastros:manage');

        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE cboId = ?");
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
                // (tabela 'cargos' usa 'cboId')
                throw new Exception("Este CBO não pode ser excluído pois está sendo utilizado em um ou mais Cargos.");
            }
            throw $e;
        }
    }

    /**
     * Busca CBOs de forma paginada, com filtro.
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
            $where = " WHERE c.cboCod LIKE :term OR c.cboTituloOficial LIKE :term OR f.familiaCboNome LIKE :term";
            $bindings[':term'] = $sqlTerm;
        }
        
        // Count total
        $countSql = "SELECT COUNT(c.cboId) FROM cbos c LEFT JOIN familia_cbo f ON f.familiaCboId = c.familiaCboId" . $where;
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($bindings);
        $totalRecords = (int)$countStmt->fetchColumn();
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;

        // Data query
        $dataSql = "SELECT c.*, f.familiaCboNome FROM cbos c LEFT JOIN familia_cbo f ON f.familiaCboId = c.familiaCboId" . $where;
        
        // Order by
        $sort_col = $params['sort_col'] ?? 'cboTituloOficial';
        $sort_dir = $params['sort_dir'] ?? 'ASC';
        $validColumns = ['cboId', 'cboCod', 'cboTituloOficial', 'familiaCboNome'];
        $orderBy = in_array($sort_col, $validColumns) ? $sort_col : 'cboTituloOficial';
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