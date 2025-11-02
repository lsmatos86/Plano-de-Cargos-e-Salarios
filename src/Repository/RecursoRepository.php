<?php
// Arquivo: src/Repository/RecursoRepository.php (Atualizado com Auditoria e Paginação)

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;  // <-- PASSO 1: Incluir
use App\Service\AuthService;   // <-- PASSO 1: Incluir
use PDO;
use Exception;

/**
 * Lida com as operações de CRUD para a entidade Recurso.
 */
class RecursoRepository
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
     * Busca um recurso pelo ID.
     */
    public function find(int $id)
    {
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail('cadastros:manage');
        
        $stmt = $this->pdo->prepare("SELECT * FROM recursos WHERE recursoId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Salva (cria ou atualiza) um Recurso.
     */
    public function save(array $data): int
    {
        $tableName = 'recursos';

        // 1. Coleta de Dados
        $id = (int)($data['recursoId'] ?? 0);
        $nome = trim($data['recursoNome'] ?? '');
        $descricao = trim($data['recursoDescricao'] ?? null);
        $isUpdating = $id > 0;

        // 2. Validação de Permissão e Dados
        $permissionNeeded = $isUpdating ? 'cadastros:manage' : 'cadastros:manage'; 
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail($permissionNeeded);

        if (empty($nome)) {
            throw new Exception("O nome do recurso é obrigatório.");
        }
        
        // 3. SQL
        $params = [
            ':nome' => $nome,
            ':descricao' => $descricao,
        ];

        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET recursoNome = :nome, recursoDescricao = :descricao WHERE recursoId = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE UPDATE
                // ======================================================
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
                
            } else {
                $sql = "INSERT INTO {$tableName} (recursoNome, recursoDescricao) VALUES (:nome, :descricao)";
                $this->pdo->prepare($sql)->execute($params);
                $savedId = (int)$this->pdo->lastInsertId();
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE CREATE
                // ======================================================
                $this->auditService->log('CREATE', $tableName, $savedId, $data);
            }
            
            return $savedId;

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                 throw new Exception("O recurso '$nome' já existe.");
            }
            throw $e; // Propaga outros erros
        }
    }

    /**
     * Exclui um Recurso.
     */
    public function delete(int $id): bool
    {
        $tableName = 'recursos';
        
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail('cadastros:manage');

        try {
            // 1. Verifica se o recurso está sendo usado por um cargo
            //
            $stmtCheck1 = $this->pdo->prepare("SELECT COUNT(*) FROM recursos_cargo WHERE recursoId = ?");
            $stmtCheck1->execute([$id]);
            if ($stmtCheck1->fetchColumn() > 0) {
                throw new Exception("Este recurso não pode ser excluído pois está associado a um ou mais cargos.");
            }
            
            // 2. Verifica se o recurso está sendo usado por um grupo
            //
            $stmtCheck2 = $this->pdo->prepare("SELECT COUNT(*) FROM recurso_grupo_recurso WHERE recursoId = ?");
            $stmtCheck2->execute([$id]);
            if ($stmtCheck2->fetchColumn() > 0) {
                throw new Exception("Este recurso não pode ser excluído pois está associado a um ou mais grupos de recursos.");
            }

            // 3. Exclui
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE recursoId = ?");
            $stmt->execute([$id]);
            
            $success = $stmt->rowCount() > 0;
            
            if ($success) {
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE DELETE
                // ======================================================
                $this->auditService->log('DELETE', $tableName, $id, ['deletedId' => $id]);
            }
            
            return $success;

        } catch (Exception $e) {
            // Se for erro de FK (mesmo que tenhamos verificado, por segurança)
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                 throw new Exception("Este recurso não pode ser excluído pois está em uso.");
            }
            throw $e; // Propaga outros erros
        }
    }
    
    /**
     * Busca recursos de forma paginada, com filtro.
     */
    public function findAllPaginated(array $params = []): array
    {
        // 1. Configuração da Paginação e Filtros
        $itemsPerPage = (int)($params['limit'] ?? 15);
        $currentPage = (int)($params['page'] ?? 1);
        $currentPage = max(1, $currentPage); 
        $term = $params['term'] ?? '';
        $sqlTerm = "%{$term}%";
        
        $where = [];
        $bindings = [];

        // 2. Montagem dos Filtros
        if (!empty($term)) {
            $where[] = "(recursoNome LIKE :term OR recursoDescricao LIKE :term)";
            $bindings[':term'] = $sqlTerm;
        }
        
        $sqlWhere = "";
        if (!empty($where)) {
            $sqlWhere = " WHERE " . implode(" AND ", $where);
        }

        // 3. Query para Contagem Total
        $count_sql = "SELECT COUNT(*) FROM recursos" . $sqlWhere;
        
        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar recursos: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 4. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 5. Query Principal
        $sql = "SELECT * FROM recursos" . $sqlWhere;
        
        // Validação de Colunas de Ordenação
        $sort_col = $params['sort_col'] ?? 'recursoNome';
        $sort_dir = $params['sort_dir'] ?? 'ASC';
        $validColumns = ['recursoId', 'recursoNome', 'recursoDataAtualizacao'];
        $orderBy = in_array($sort_col, $validColumns) ? $sort_col : 'recursoNome';
        $sortDir = in_array(strtoupper($sort_dir), ['ASC', 'DESC']) ? strtoupper($sort_dir) : 'ASC';

        $sql .= " ORDER BY {$orderBy} {$sortDir}";
        $sql .= " LIMIT :limit OFFSET :offset";

        $bindings[':limit'] = $itemsPerPage;
        $bindings[':offset'] = $offset;

        // 6. Executa a query principal
        try {
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($bindings as $key => &$val) {
                if ($key == ':limit' || $key == ':offset') {
                    $stmt->bindParam($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($key, $val);
                }
            }
            
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar recursos: " . $e->getMessage() . " SQL: " . $sql);
            $registros = [];
        }

        // 7. Retorna o pacote completo
        return [
            'data' => $registros,
            'total' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage
        ];
    }
}