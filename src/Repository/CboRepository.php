<?php
// Arquivo: src/Repository/CboRepository.php (Atualizado com Correção Definitiva de Parâmetros PDO)

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;  
use App\Service\AuthService;   
use PDO;
use Exception;

class CboRepository
{
    private PDO $pdo;
    private AuditService $auditService; 
    private AuthService $authService;   

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->auditService = new AuditService();
        $this->authService = new AuthService();
    }

    /**
     * Busca um CBO pelo ID, incluindo o nome da família.
     */
    public function find(int $id)
    {
        $this->authService->checkAndFail('cadastros:manage');
        
        $stmt = $this->pdo->prepare("SELECT c.*, f.\"familiaCboNome\" FROM cbos c LEFT JOIN familia_cbo f ON f.\"familiaCboId\" = c.\"familiaCboId\" WHERE c.\"cboId\" = ?");
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
        $this->authService->checkAndFail('cadastros:manage');

        // 2. Coleta de Dados
        $params = [
            ':cboCod' => $data['cboCod'] ?? '',
            ':cboTituloOficial' => $data['cboTituloOficial'] ?? '',
            ':familiaCboId' => !empty($data['familiaCboId']) ? (int)$data['familiaCboId'] : null
        ];

        if (empty($params[':cboCod']) || empty($params[':cboTituloOficial']) || empty($params[':familiaCboId'])) {
            throw new Exception("Código CBO, Título Oficial e Família CBO são obrigatórios.");
        }

        // 3. Execução
        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET \"cboCod\" = :cboCod, \"cboTituloOficial\" = :cboTituloOficial, \"familiaCboId\" = :familiaCboId WHERE \"cboId\" = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
                
            } else {
                $sql = "INSERT INTO {$tableName} (\"cboCod\", \"cboTituloOficial\", \"familiaCboId\") VALUES (:cboCod, :cboTituloOficial, :familiaCboId)";
                $this->pdo->prepare($sql)->execute($params);
                $savedId = (int)$this->pdo->lastInsertId();
                
                $this->auditService->log('CREATE', $tableName, $savedId, $data);
            }
            
            return $savedId;

        } catch (\PDOException $e) {
<<<<<<< HEAD
            if ($e->getCode() == '23505') { // Duplicate entry
=======
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) { 
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
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
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE \"cboId\" = ?");
            $stmt->execute([$id]);
            
            $success = $stmt->rowCount() > 0;
            
            if ($success) {
                $this->auditService->log('DELETE', $tableName, $id, ['deletedId' => $id]);
            }
            
            return $success;
            
        } catch (\PDOException $e) {
<<<<<<< HEAD
            if ($e->getCode() == '23503') { // Foreign key constraint
                // (tabela 'cargos' usa 'cboId')
=======
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451) { 
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
                throw new Exception("Este CBO não pode ser excluído pois está sendo utilizado em um ou mais Cargos.");
            }
            throw $e;
        }
    }

    /**
     * Busca CBOs de forma paginada, blindado contra erros de contagem de parâmetros (HY093).
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

        // Identificadores exclusivos com numeração para evitar o conflito do PDO
        if (!empty($term)) {
<<<<<<< HEAD
            $where = " WHERE c.\"cboCod\" ILIKE :term OR c.\"cboTituloOficial\" ILIKE :term OR f.\"familiaCboNome\" ILIKE :term";
            $bindings[':term'] = $sqlTerm;
        }
        
        // Count total
        $countSql = "SELECT COUNT(c.\"cboId\") FROM cbos c LEFT JOIN familia_cbo f ON f.\"familiaCboId\" = c.\"familiaCboId\"" . $where;
=======
            $where = " WHERE c.cboCod LIKE :term1 OR c.cboTituloOficial LIKE :term2 OR f.familiaCboNome LIKE :term3";
            $bindings[':term1'] = $sqlTerm;
            $bindings[':term2'] = $sqlTerm;
            $bindings[':term3'] = $sqlTerm;
        }
        
        // 1. Count total seguro
        $countSql = "SELECT COUNT(c.cboId) FROM cbos c LEFT JOIN familia_cbo f ON f.familiaCboId = c.familiaCboId" . $where;
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($bindings);
        $totalRecords = (int)$countStmt->fetchColumn();
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;

<<<<<<< HEAD
        // Data query
        $dataSql = "SELECT c.*, f.\"familiaCboNome\" FROM cbos c LEFT JOIN familia_cbo f ON f.\"familiaCboId\" = c.\"familiaCboId\"" . $where;
=======
        // 2. Query de dados principal
        $dataSql = "SELECT c.*, f.familiaCboNome FROM cbos c LEFT JOIN familia_cbo f ON f.familiaCboId = c.familiaCboId" . $where;
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
        
        // 3. Tratamento explícito de colunas para afastar o erro de ambiguidade
        $sort_col = $params['order_by'] ?? 'c.cboTituloOficial';
        $sort_dir = $params['sort_dir'] ?? 'ASC';
<<<<<<< HEAD
        $validColumns = ['cboId' => 'c', 'cboCod' => 'c', 'cboTituloOficial' => 'c', 'familiaCboNome' => 'f'];
        $orderBy = array_key_exists($sort_col, $validColumns) ? $sort_col : 'cboTituloOficial';
        $tableAlias = $validColumns[$orderBy];
        $sortDir = in_array(strtoupper($sort_dir), ['ASC', 'DESC']) ? strtoupper($sort_dir) : 'ASC';
        $dataSql .= ' ORDER BY ' . $tableAlias . '."' . $orderBy . '" ' . $sortDir;
=======
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
        
        $validColumns = [
            'c.cboId'            => 'c.cboId',
            'c.cboCod'           => 'c.cboCod',
            'c.cboTituloOficial' => 'c.cboTituloOficial',
            'f.familiaCboNome'   => 'f.familiaCboNome'
        ];
        
        $orderBy = $validColumns[$sort_col] ?? 'c.cboTituloOficial';
        $sortDir = in_array(strtoupper($sort_dir), ['ASC', 'DESC']) ? strtoupper($sort_dir) : 'ASC';
        
        $dataSql .= " ORDER BY {$orderBy} {$sortDir}";
        $dataSql .= " LIMIT :limit OFFSET :offset";
        
        $bindings[':limit'] = $itemsPerPage;
        $bindings[':offset'] = $offset;

        $stmt = $this->pdo->prepare($dataSql);
        
        foreach ($bindings as $key => &$val) {
            if ($key === ':limit' || $key === ':offset') {
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