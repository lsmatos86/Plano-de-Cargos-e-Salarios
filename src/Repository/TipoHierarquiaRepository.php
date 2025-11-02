<?php
// Arquivo: src/Repository/TipoHierarquiaRepository.php (Atualizado com Auditoria)

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;  // <-- PASSO 1: Incluir
use App\Service\AuthService;   // <-- PASSO 1: Incluir
use PDO;
use Exception;

class TipoHierarquiaRepository
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
     * Busca um Tipo pelo ID.
     */
    public function find(int $id)
    {
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail('estruturas:manage');

        $stmt = $this->pdo->prepare("SELECT * FROM tipo_hierarquia WHERE tipoId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Salva (cria ou atualiza) um Tipo de Hierarquia.
     */
    public function save(array $data): int
    {
        $tableName = 'tipo_hierarquia';

        // 1. Coleta de Dados
        $id = (int)($data['tipoId'] ?? 0);
        $nome = trim($data['tipoNome'] ?? '');
        $descricao = trim($data['tipoDescricao'] ?? null);
        $isUpdating = $id > 0;
        
        // 2. Validação de Permissão e Dados
        $permissionNeeded = $isUpdating ? 'estruturas:manage' : 'estruturas:manage';
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail($permissionNeeded);
        
        if (empty($nome)) {
            throw new Exception("O nome do tipo de hierarquia é obrigatório.");
        }
        
        // 3. SQL
        $params = [
            ':nome' => $nome,
            ':descricao' => $descricao
        ];

        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET tipoNome = :nome, tipoDescricao = :descricao WHERE tipoId = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE UPDATE
                // ======================================================
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
                
            } else {
                $sql = "INSERT INTO {$tableName} (tipoNome, tipoDescricao) VALUES (:nome, :descricao)";
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
                 throw new Exception("O tipo de hierarquia '$nome' já existe.");
            }
            throw $e; // Propaga outros erros
        }
    }

    /**
     * Exclui um Tipo de Hierarquia.
     */
    public function delete(int $id): bool
    {
        $tableName = 'tipo_hierarquia';
        
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail('estruturas:manage');

        try {
            // 1. Verifica se o tipo está sendo usado por um nível hierárquico
            //
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM nivel_hierarquico WHERE tipoId = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Este tipo não pode ser excluído pois está associado a um ou mais Níveis Hierárquicos.");
            }

            // 2. Exclui
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE tipoId = ?");
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
                 throw new Exception("Este tipo não pode ser excluído pois está em uso.");
            }
            throw $e; // Propaga outros erros
        }
    }
    
    /**
     * Busca Tipos de forma paginada, com filtro.
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
            $where[] = "(tipoNome LIKE :term OR tipoDescricao LIKE :term)";
            $bindings[':term'] = $sqlTerm;
        }
        
        $sqlWhere = "";
        if (!empty($where)) {
            $sqlWhere = " WHERE " . implode(" AND ", $where);
        }

        // 3. Query para Contagem Total
        $count_sql = "SELECT COUNT(*) FROM tipo_hierarquia" . $sqlWhere;
        
        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar tipos de hierarquia: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 4. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 5. Query Principal
        $sql = "SELECT * FROM tipo_hierarquia" . $sqlWhere;
        
        // Validação de Colunas de Ordenação
        $sort_col = $params['sort_col'] ?? 'tipoNome';
        $sort_dir = $params['sort_dir'] ?? 'ASC';
        $validColumns = ['tipoId', 'tipoNome', 'tipoDataAtualizacao'];
        $orderBy = in_array($sort_col, $validColumns) ? $sort_col : 'tipoNome';
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
            error_log("Erro ao buscar tipos de hierarquia: " . $e->getMessage() . " SQL: " . $sql);
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