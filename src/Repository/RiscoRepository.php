<?php
// Arquivo: src/Repository/RiscoRepository.php (Atualizado com Auditoria)

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;  // <-- PASSO 1: Incluir
use App\Service\AuthService;   // <-- PASSO 1: Incluir
use PDO;
use Exception;

class RiscoRepository
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
     * Busca um risco pelo ID. (Método adicionado para consistência)
     */
    public function find(int $id)
    {
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail('cadastros:manage');
        
        $stmt = $this->pdo->prepare("SELECT * FROM riscos WHERE riscoId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Exclui um Risco.
     */
    public function delete(int $id): bool
    {
        $tableName = 'riscos';
        
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail('cadastros:manage');

        try {
            // 1. Verifica se o risco está sendo usado por um cargo
            //
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM riscos_cargo WHERE riscoId = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Este risco não pode ser excluído pois está associado a um ou mais cargos.");
            }

            // 2. Exclui
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE riscoId = ?");
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
                 throw new Exception("Este risco não pode ser excluído pois está em uso.");
            }
            throw $e; // Propaga outros erros
        }
    }
    
    /**
     * Busca riscos de forma paginada, com filtro.
     * (Método já existia, agora apenas padronizado)
     */
    public function findAllPaginated(array $params = []): array
    {
        // 1. Configuração da Paginação e Filtros
        $itemsPerPage = (int)($params['limit'] ?? 15);
        $currentPage = (int)($params['page'] ?? 1);
        $currentPage = max(1, $currentPage); 
        $term = $params['term'] ?? ''; // O termo de busca é o próprio nome do risco (enum)
        
        $where = [];
        $bindings = [];

        // 2. Montagem dos Filtros
        if (!empty($term)) {
            $where[] = "(riscoNome LIKE :term)";
            $bindings[':term'] = "%{$term}%"; // Permitir busca parcial no enum
        }
        
        $sqlWhere = "";
        if (!empty($where)) {
            $sqlWhere = " WHERE " . implode(" AND ", $where);
        }

        // 3. Query para Contagem Total
        $count_sql = "SELECT COUNT(*) FROM riscos" . $sqlWhere;
        
        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar riscos: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 4. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 5. Query Principal
        $sql = "SELECT * FROM riscos" . $sqlWhere;
        
        // Validação de Colunas de Ordenação
        $sort_col = $params['sort_col'] ?? 'riscoNome';
        $sort_dir = $params['sort_dir'] ?? 'ASC';
        $validColumns = ['riscoId', 'riscoNome'];
        $orderBy = in_array($sort_col, $validColumns) ? $sort_col : 'riscoNome';
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
            error_log("Erro ao buscar riscos: " . $e->getMessage() . " SQL: " . $sql);
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