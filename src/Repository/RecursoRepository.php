<?php
// Arquivo: src/Repository/RecursoRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Lida com todas as operações de banco de dados para a entidade Recurso.
 */
class RecursoRepository
{
    private PDO $pdo;
    private string $tableName = 'recursos';
    private string $idColumn = 'recursoId';
    private string $nameColumn = 'recursoNome';
    private string $descColumn = 'recursoDescricao';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Salva (cria ou atualiza) um registro de recurso.
     * (Migrado de views/recursos.php)
     *
     * @param array $data Dados vindos do formulário ($_POST)
     * @return int O número de linhas afetadas.
     * @throws Exception Se o nome estiver vazio.
     */
    public function save(array $data): int
    {
        $nome = trim($data[$this->nameColumn] ?? '');
        $descricao = trim($data[$this->descColumn] ?? null);
        $id = (int)($data[$this->idColumn] ?? 0);
        $action = $data['action'] ?? ($id > 0 ? 'update' : 'insert');

        if (empty($nome)) {
            throw new Exception("O nome do recurso não pode estar vazio.");
        }

        try {
            if ($action === 'insert') {
                $sql = "INSERT INTO {$this->tableName} ({$this->nameColumn}, {$this->descColumn}, recursoDataCadastro) 
                        VALUES (?, ?, CURRENT_TIMESTAMP())";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nome, $descricao]);
                return $stmt->rowCount();

            } elseif ($action === 'update' && $id > 0) {
                $sql = "UPDATE {$this->tableName} SET 
                            {$this->nameColumn} = ?, 
                            {$this->descColumn} = ?,
                            recursoDataAtualizacao = CURRENT_TIMESTAMP() 
                        WHERE {$this->idColumn} = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nome, $descricao, $id]);
                return $stmt->rowCount();
            }
            
            return 0; // Nenhuma ação válida

        } catch (\PDOException $e) {
            error_log("Erro ao salvar Recurso: " . $e->getMessage());
            throw new Exception("Erro de banco de dados ao salvar. " . $e->getMessage());
        }
    }

    /**
     * Exclui um registro de recurso.
     * (Migrado de views/recursos.php)
     *
     * @param int $id O ID a ser excluído.
     * @return int O número de linhas afetadas.
     * @throws Exception Em caso de falha.
     */
    public function delete(int $id): int
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->tableName} WHERE {$this->idColumn} = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Erro ao excluir Recurso: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                // Erro de chave estrangeira (FK)
                throw new Exception("Erro: Este recurso não pode ser excluído pois está sendo utilizado (provavelmente por um 'Grupo de Recursos').");
            }
            throw new Exception("Erro de banco de dados ao excluir. " . $e->getMessage());
        }
    }

    /**
     * Busca registros de forma paginada, com filtro e ordenação.
     * (Migrado de views/recursos.php)
     *
     * @param array $params Parâmetros de busca (term, page, limit, order_by, sort_dir)
     * @return array Contendo ['data', 'total', 'totalPages', 'currentPage']
     */
    public function findAllPaginated(array $params = []): array
    {
        // 1. Configuração da Paginação e Filtros
        $itemsPerPage = (int)($params['limit'] ?? 10);
        $currentPage = (int)($params['page'] ?? 1);
        $currentPage = max(1, $currentPage);
        $term = $params['term'] ?? '';
        $sqlTerm = "%{$term}%";
        $count_bindings = [];
        $all_bindings = [];

        // 2. Query para Contagem Total (Filtra por nome OU descrição)
        $count_sql = "SELECT COUNT(*) FROM {$this->tableName}";
        if (!empty($term)) {
            $count_sql .= " WHERE {$this->nameColumn} LIKE ? OR {$this->descColumn} LIKE ?";
            $count_bindings[] = $sqlTerm;
            $count_bindings[] = $sqlTerm;
        }

        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($count_bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar Recursos: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 3. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 4. Query Principal
        $sql = "SELECT * FROM {$this->tableName}";
        if (!empty($term)) {
            $sql .= " WHERE {$this->nameColumn} LIKE ? OR {$this->descColumn} LIKE ?";
            $all_bindings[] = $sqlTerm;
            $all_bindings[] = $sqlTerm;
        }

        // 5. Ordenação
        $orderBy = $params['order_by'] ?? $this->idColumn;
        $sortDir = $params['sort_dir'] ?? 'ASC';
        
        $validColumns = [$this->idColumn, $this->nameColumn, $this->descColumn, 'recursoDataCadastro', 'recursoDataAtualizacao'];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : $this->idColumn;
        $sortDir = in_array(strtoupper($sortDir), ['ASC', 'DESC']) ? strtoupper($sortDir) : 'ASC';

        $sql .= " ORDER BY {$orderBy} {$sortDir}";
        $sql .= " LIMIT {$itemsPerPage} OFFSET {$offset}";

        // 6. Executa a query principal
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($all_bindings);
            $registros = $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Recursos: " . $e->getMessage());
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