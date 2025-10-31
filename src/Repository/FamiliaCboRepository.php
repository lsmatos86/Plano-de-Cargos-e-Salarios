<?php
// Arquivo: src/Repository/FamiliaCboRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Lida com todas as operações de banco de dados para a entidade FamiliaCbo.
 */
class FamiliaCboRepository
{
    private PDO $pdo;
    private string $tableName = 'familia_cbo';
    private string $idColumn = 'familiaCboId';
    private string $nameColumn = 'familiaCboNome';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Salva (cria ou atualiza) um registro de família cbo.
     * (Migrado de views/familia_cbo.php)
     *
     * @param array $data Dados vindos do formulário ($_POST)
     * @return int O número de linhas afetadas.
     * @throws Exception Se o nome estiver vazio.
     */
    public function save(array $data): int
    {
        $nome = trim($data[$this->nameColumn] ?? '');
        $id = (int)($data[$this->idColumn] ?? 0);
        $action = $data['action'] ?? '';

        if (empty($nome)) {
            throw new Exception("O nome da Família CBO não pode estar vazio.");
        }

        try {
            if ($action === 'insert') {
                // Lógica de insertSimpleRecord
                $sql = "INSERT INTO {$this->tableName} ({$this->nameColumn}) VALUES (?)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nome]);
                return $stmt->rowCount();

            } elseif ($action === 'update' && $id > 0) {
                // Lógica de update manual
                $sql = "UPDATE {$this->tableName} SET {$this->nameColumn} = ? WHERE {$this->idColumn} = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nome, $id]);
                return $stmt->rowCount();
            }
            
            return 0; // Nenhuma ação válida

        } catch (\PDOException $e) {
            error_log("Erro ao salvar Família CBO: " . $e->getMessage());
            throw new Exception("Erro de banco de dados ao salvar. " . $e->getMessage());
        }
    }

    /**
     * Exclui um registro de família cbo.
     * (Migrado de views/familia_cbo.php)
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
            error_log("Erro ao excluir Família CBO: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                // Erro de chave estrangeira (FK)
                throw new Exception("Erro: Esta Família CBO não pode ser excluída pois está sendo utilizada por um ou mais CBOs.");
            }
            throw new Exception("Erro de banco de dados ao excluir. " . $e->getMessage());
        }
    }

    /**
     * Busca registros de forma paginada, com filtro e ordenação.
     * (Migrado de views/familia_cbo.php)
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

        // 2. Query para Contagem Total
        $count_sql = "SELECT COUNT(*) FROM {$this->tableName}";
        if (!empty($term)) {
            $count_sql .= " WHERE {$this->nameColumn} LIKE ?";
            $count_bindings[] = $sqlTerm;
        }

        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($count_bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar Famílias CBO: " . $e->getMessage());
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
            $sql .= " WHERE {$this->nameColumn} LIKE ?";
            $all_bindings[] = $sqlTerm;
        }

        // 5. Ordenação
        $orderBy = $params['order_by'] ?? $this->idColumn;
        $sortDir = $params['sort_dir'] ?? 'ASC';
        
        $validColumns = [$this->idColumn, $this->nameColumn, $this->idColumn.'DataCadastro', $this->idColumn.'DataAtualizacao'];
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
            error_log("Erro ao buscar Famílias CBO: " . $e->getMessage());
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