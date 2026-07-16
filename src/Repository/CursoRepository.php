<?php
// Arquivo: src/Repository/CursoRepository.php

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;
use App\Service\AuthService;
use PDO;
use Exception;

class CursoRepository
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

    public function find(int $id)
    {
        // Apenas quem pode gerenciar pode buscar os dados
        $this->authService->checkAndFail('cadastros:manage');
        
        $stmt = $this->pdo->prepare("SELECT * FROM cursos WHERE \"cursoId\" = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM cursos ORDER BY cursoNome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna lookups simples (id => nome) para SELECTs.
     */
    public function findAllLookup(): array
    {
        $stmt = $this->pdo->query("SELECT cursoId, cursoNome FROM cursos ORDER BY cursoNome ASC");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Salva (cria ou atualiza) um curso incluindo a periodicidade padrão em meses.
     */
    public function save(array $data): int
    {
        $tableName = 'cursos';

        $id = (int)($data['cursoId'] ?? 0);
        $nome = trim($data['cursoNome'] ?? '');
        $descricao = trim($data['cursoDescricao'] ?? '');
        $periodicidade = isset($data['cursoPeriodicidade']) && $data['cursoPeriodicidade'] !== '' ? (int)$data['cursoPeriodicidade'] : null;
        $isUpdating = $id > 0;

        $permissionNeeded = $isUpdating ? 'cadastros:manage' : 'cadastros:manage';
        $this->authService->checkAndFail($permissionNeeded);

        if (empty($nome)) {
            throw new Exception("O nome do curso é obrigatório.");
        }

        $params = [
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':periodicidade' => $periodicidade
        ];

        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET \"cursoNome\" = :nome, \"cursoDescricao\" = :descricao, \"cursoPeriodicidade\" = :periodicidade WHERE \"cursoId\" = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
            } else {
                $sql = "INSERT INTO {$tableName} (\"cursoNome\", \"cursoDescricao\", \"cursoPeriodicidade\") VALUES (:nome, :descricao, :periodicidade)";
                $this->pdo->prepare($sql)->execute($params);
                $savedId = (int)$this->pdo->lastInsertId();
                
                $this->auditService->log('CREATE', $tableName, $savedId, $data);
            }
            
            return $savedId;

        } catch (Exception $e) {
            if ($e->getCode() == '23505') {
                 throw new Exception("O curso '$nome' já existe.");
            }
            throw $e;
        }
    }

    /**
     * Exclui um curso se não houver vínculos ativos com cargos.
     */
    public function delete(int $id): bool
    {
        $tableName = 'cursos';
        $this->authService->checkAndFail('cadastros:manage');

        try {
            // 1. Verifica se o curso está sendo usado por um cargo
            //
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM cursos_cargo WHERE \"cursoId\" = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Este curso não pode ser excluído pois está associado a um ou mais cargos.");
            }

            // 2. Exclui
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE \"cursoId\" = ?");
            $stmt->execute([$id]);
            $success = $stmt->rowCount() > 0;
            
            if ($success) {
                $this->auditService->log('DELETE', $tableName, $id, ['deletedId' => $id]);
            }
            
            return $success;

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                throw new Exception("Este curso não pode ser excluído pois está em uso no sistema.");
            }
            throw $e;
        }
    }
    
    /**
     * Busca cursos de forma paginada, com filtro.
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
            $where[] = "(unaccent(COALESCE(\"cursoNome\"::text, '')) ILIKE unaccent(:term) OR unaccent(COALESCE(\"cursoDescricao\"::text, '')) ILIKE unaccent(:term))";
            $bindings[':term'] = $sqlTerm;
        }
        
        $sqlWhere = "";
        if (!empty($where)) {
            $sqlWhere = " WHERE " . implode(" AND ", $where);
        }

        // 3. Query para Contagem Total
        $count_sql = "SELECT COUNT(*) FROM cursos" . $sqlWhere;
        
        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar cursos: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 4. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 5. Query Principal
        $sql = "SELECT * FROM cursos" . $sqlWhere;
        
        // Validação de Colunas de Ordenação
        $sort_col = $params['sort_col'] ?? 'cursoNome';
        $sort_dir = $params['sort_dir'] ?? 'ASC';
        $validColumns = ['cursoId', 'cursoNome', 'cursoPeriodicidade', 'cursoDataAtualizacao'];
        $orderBy = in_array($sort_col, $validColumns) ? $sort_col : 'cursoNome';
        $sortDir = in_array(strtoupper($sort_dir), ['ASC', 'DESC']) ? strtoupper($sort_dir) : 'ASC';

        $sql .= ' ORDER BY "' . $orderBy . '" ' . $sortDir;
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
            error_log("Erro ao buscar cursos: " . $e->getMessage() . " SQL: " . $sql);
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
