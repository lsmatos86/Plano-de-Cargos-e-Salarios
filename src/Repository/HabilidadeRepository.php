<?php
// Arquivo: src/Repository/HabilidadeRepository.php (Atualizado com Auditoria)

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;  // <-- PASSO 1: Incluir
use App\Service\AuthService;   // <-- PASSO 1: Incluir
use PDO;
use Exception;

class HabilidadeRepository
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
     * Busca uma habilidade pelo ID.
     */
    public function find(int $id)
    {
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail('cadastros:manage');

        $stmt = $this->pdo->prepare("SELECT * FROM habilidades WHERE habilidadeId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca habilidades de forma paginada, com filtro.
     */
    public function findAllPaginated(array $params = []): array
    {
        // 1. Configuração da Paginação e Filtros
        $itemsPerPage = (int)($params['limit'] ?? 15);
        $currentPage = (int)($params['page'] ?? 1);
        $currentPage = max(1, $currentPage); 
        $term = $params['term'] ?? '';
        $sqlTerm = "%{$term}%";
        $tipo = $params['tipo'] ?? ''; // Filtro de tipo
        
        $where = [];
        $bindings = [];

        // 2. Montagem dos Filtros
        if (!empty($term)) {
            $where[] = "(habilidadeNome LIKE :term OR habilidadeDescricao LIKE :term)";
            $bindings[':term'] = $sqlTerm;
        }
        if (!empty($tipo)) {
            $where[] = "habilidadeTipo = :tipo";
            $bindings[':tipo'] = $tipo;
        }
        
        $sqlWhere = "";
        if (!empty($where)) {
            $sqlWhere = " WHERE " . implode(" AND ", $where);
        }

        // 3. Query para Contagem Total
        $count_sql = "SELECT COUNT(*) FROM habilidades" . $sqlWhere;
        
        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar habilidades: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 4. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 5. Query Principal
        $sql = "SELECT * FROM habilidades" . $sqlWhere;
        
        // Validação de Colunas de Ordenação
        $sort_col = $params['sort_col'] ?? 'habilidadeNome';
        $sort_dir = $params['sort_dir'] ?? 'ASC';
        $validColumns = ['habilidadeId', 'habilidadeNome', 'habilidadeTipo', 'habilidadeDataAtualizacao'];
        $orderBy = in_array($sort_col, $validColumns) ? $sort_col : 'habilidadeNome';
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
            error_log("Erro ao buscar habilidades: " . $e->getMessage() . " SQL: " . $sql);
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
    
    /**
     * Busca Habilidades agrupadas por Tipo (para o formulário de cargos).
     */
    public function getGroupedLookup(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT habilidadeId, habilidadeNome, habilidadeTipo FROM habilidades ORDER BY habilidadeTipo, habilidadeNome ASC");
            $habilidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $grouped = [
                'Hardskill' => [],
                'Softskill' => []
            ];

            foreach ($habilidades as $h) {
                if (isset($grouped[$h['habilidadeTipo']])) {
                    $grouped[$h['habilidadeTipo']][$h['habilidadeId']] = $h['habilidadeNome'];
                }
            }
            
            // Renomeia chaves para exibição amigável
            $finalGrouped = [];
            if (!empty($grouped['Hardskill'])) {
                $finalGrouped['Hard Skills (Técnicas)'] = $grouped['Hardskill'];
            }
            if (!empty($grouped['Softskill'])) {
                $finalGrouped['Soft Skills (Comportamentais)'] = $grouped['Softskill'];
            }

            return $finalGrouped;

        } catch (\PDOException $e) {
            error_log("Erro ao buscar lookup agrupado de habilidades: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva (cria ou atualiza) uma Habilidade.
     */
    public function save(array $data): int
    {
        $tableName = 'habilidades';

        // 1. Coleta de Dados
        $id = (int)($data['habilidadeId'] ?? 0);
        $nome = trim($data['habilidadeNome'] ?? '');
        $tipo = trim($data['habilidadeTipo'] ?? '');
        $descricao = trim($data['habilidadeDescricao'] ?? null);
        $isUpdating = $id > 0;

        // 2. Validação de Permissão e Dados
        $permissionNeeded = $isUpdating ? 'cadastros:manage' : 'cadastros:manage'; 
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail($permissionNeeded);

        if (empty($nome) || empty($tipo)) {
            throw new Exception("Nome e Tipo da Habilidade são obrigatórios.");
        }
        if (!in_array($tipo, ['Hardskill', 'Softskill'])) {
            throw new Exception("Tipo de habilidade inválido.");
        }
        
        // 3. SQL
        $params = [
            ':nome' => $nome,
            ':tipo' => $tipo,
            ':descricao' => $descricao,
        ];

        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET habilidadeNome = :nome, habilidadeTipo = :tipo, habilidadeDescricao = :descricao WHERE habilidadeId = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE UPDATE
                // ======================================================
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
                
            } else {
                $sql = "INSERT INTO {$tableName} (habilidadeNome, habilidadeTipo, habilidadeDescricao) VALUES (:nome, :tipo, :descricao)";
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
                 throw new Exception("A habilidade '$nome' já existe.");
            }
            throw $e; // Propaga outros erros
        }
    }

    /**
     * Exclui uma Habilidade.
     */
    public function delete(int $id): bool
    {
        $tableName = 'habilidades';
        
        // ======================================================
        // PASSO 3: Adicionar verificação de permissão
        // ======================================================
        $this->authService->checkAndFail('cadastros:manage');

        try {
            // 1. Verifica se a habilidade está sendo usada por um cargo
            //
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM habilidades_cargo WHERE habilidadeId = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Esta habilidade não pode ser excluída pois está associada a um ou mais cargos.");
            }

            // 2. Exclui
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE habilidadeId = ?");
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
                 throw new Exception("Esta habilidade não pode ser excluída pois está em uso.");
            }
            throw $e; // Propaga outros erros
        }
    }
}