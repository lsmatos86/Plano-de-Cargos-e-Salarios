<?php
// Arquivo: src/Repository/NivelHierarquicoRepository.php (Atualizado com Auditoria)

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;  // <-- PASSO 1: Incluir
use App\Service\AuthService;   // <-- PASSO 1: Incluir
use PDO;
use Exception;

class NivelHierarquicoRepository
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
     * Busca um Nível pelo ID.
     */
    public function find(int $id)
    {
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail('estruturas:manage');

        $stmt = $this->pdo->prepare("SELECT * FROM nivel_hierarquico WHERE nivelId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca todos os Tipos de Hierarquia (para o <select>).
     */
    public function findAllTipos(): array
    {
        $stmt = $this->pdo->query("SELECT tipoId, tipoNome FROM tipo_hierarquia ORDER BY tipoNome ASC");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Salva (cria ou atualiza) um Nível Hierárquico.
     */
    public function save(array $data): int
    {
        $tableName = 'nivel_hierarquico';

        // 1. Coleta de Dados
        $id = (int)($data['nivelId'] ?? 0);
        $isUpdating = $id > 0;
        
        $params = [
            ':tipoId' => (int)($data['tipoId'] ?? 0),
            ':nivelOrdem' => (int)($data['nivelOrdem'] ?? 0),
            ':nivelDescricao' => trim($data['nivelDescricao'] ?? ''),
            ':nivelAtribuicoes' => trim($data['nivelAtribuicoes'] ?? null),
            ':nivelAutonomia' => trim($data['nivelAutonomia'] ?? null),
            ':nivelQuandoUtilizar' => trim($data['nivelQuandoUtilizar'] ?? null),
        ];

        // 2. Validação de Permissão e Dados
        $permissionNeeded = $isUpdating ? 'estruturas:manage' : 'estruturas:manage';
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail($permissionNeeded);
        
        if (empty($params[':tipoId']) || empty($params[':nivelDescricao'])) {
            throw new Exception("Tipo de Hierarquia e Descrição são obrigatórios.");
        }

        // 3. SQL
        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET 
                            tipoId = :tipoId, 
                            nivelOrdem = :nivelOrdem, 
                            nivelDescricao = :nivelDescricao, 
                            nivelAtribuicoes = :nivelAtribuicoes, 
                            nivelAutonomia = :nivelAutonomia, 
                            nivelQuandoUtilizar = :nivelQuandoUtilizar 
                        WHERE nivelId = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE UPDATE
                // ======================================================
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
                
            } else {
                $sql = "INSERT INTO {$tableName} (tipoId, nivelOrdem, nivelDescricao, nivelAtribuicoes, nivelAutonomia, nivelQuandoUtilizar) 
                        VALUES (:tipoId, :nivelOrdem, :nivelDescricao, :nivelAtribuicoes, :nivelAutonomia, :nivelQuandoUtilizar)";
                $this->pdo->prepare($sql)->execute($params);
                $savedId = (int)$this->pdo->lastInsertId();
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE CREATE
                // ======================================================
                $this->auditService->log('CREATE', $tableName, $savedId, $data);
            }
            
            return $savedId;

        } catch (Exception $e) {
            throw $e; // Propaga outros erros
        }
    }

    /**
     * Exclui um Nível Hierárquico.
     */
    public function delete(int $id): bool
    {
        $tableName = 'nivel_hierarquico';
        
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail('estruturas:manage');

        try {
            // 1. Verifica se o nível está sendo usado por um cargo
            //
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM cargos WHERE nivelHierarquicoId = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Este nível não pode ser excluído pois está associado a um ou mais cargos.");
            }

            // 2. Exclui
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE nivelId = ?");
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
                 throw new Exception("Este nível não pode ser excluído pois está em uso.");
            }
            throw $e; // Propaga outros erros
        }
    }
    
    /**
     * Busca Níveis de forma paginada, com filtro.
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
            $where[] = "(n.nivelDescricao LIKE :term OR t.tipoNome LIKE :term)";
            $bindings[':term'] = $sqlTerm;
        }
        
        $sqlJoin = " FROM nivel_hierarquico n LEFT JOIN tipo_hierarquia t ON n.tipoId = t.tipoId";
        $sqlWhere = "";
        if (!empty($where)) {
            $sqlWhere = " WHERE " . implode(" AND ", $where);
        }

        // 3. Query para Contagem Total
        $count_sql = "SELECT COUNT(n.nivelId)" . $sqlJoin . $sqlWhere;
        
        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar níveis hierárquicos: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 4. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 5. Query Principal
        $sql = "SELECT n.*, t.tipoNome" . $sqlJoin . $sqlWhere;
        
        // Validação de Colunas de Ordenação
        $sort_col = $params['sort_col'] ?? 'n.nivelOrdem';
        $sort_dir = $params['sort_dir'] ?? 'ASC';
        //
        $validColumns = ['n.nivelId', 'n.nivelOrdem', 'n.nivelDescricao', 't.tipoNome', 'n.nivelDataAtualizacao'];
        $orderBy = in_array($sort_col, $validColumns) ? $sort_col : 'n.nivelOrdem';
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
            error_log("Erro ao buscar níveis hierárquicos: " . $e->getMessage() . " SQL: " . $sql);
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