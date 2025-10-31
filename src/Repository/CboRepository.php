<?php
// Arquivo: src/Repository/CboRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Lida com todas as operações de banco de dados para a entidade Cbo.
 */
class CboRepository
{
    private PDO $pdo;
    private string $tableName = 'cbos';
    private string $idColumn = 'cboId'; // PK

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Salva (cria ou atualiza) um registro de CBO.
     * (Migrado de views/cbos.php)
     *
     * @param array $data Dados vindos do formulário ($_POST)
     * @return int O número de linhas afetadas.
     * @throws Exception Se os campos obrigatórios estiverem vazios.
     */
    public function save(array $data): int
    {
        // Campos do formulário
        $id = (int)($data[$this->idColumn] ?? 0);
        $cod = trim($data['cboCod'] ?? '');
        $titulo = trim($data['cboTituloOficial'] ?? '');
        $familiaId = (int)($data['familiaCboId'] ?? 0);
        
        $action = $data['action'] ?? ($id > 0 ? 'update' : 'insert');

        // Validação
        if (empty($cod) || empty($titulo) || empty($familiaId)) {
            throw new Exception("Os campos Código CBO, Título Oficial e Família CBO são obrigatórios.");
        }

        try {
            if ($action === 'insert') {
                $sql = "INSERT INTO {$this->tableName} 
                            (cboCod, cboTituloOficial, familiaCboId, cboDataCadastro) 
                        VALUES (?, ?, ?, CURRENT_TIMESTAMP())";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$cod, $titulo, $familiaId]);
                return $stmt->rowCount();

            } elseif ($action === 'update' && $id > 0) {
                $sql = "UPDATE {$this->tableName} SET 
                            cboCod = ?, 
                            cboTituloOficial = ?, 
                            familiaCboId = ?, 
                            cboDataAtualizacao = CURRENT_TIMESTAMP() 
                        WHERE {$this->idColumn} = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$cod, $titulo, $familiaId, $id]);
                return $stmt->rowCount();
            }
            
            return 0; // Nenhuma ação válida

        } catch (\PDOException $e) {
            error_log("Erro ao salvar CBO: " . $e->getMessage());
            if ($e->getCode() == 23000) { // Erro de duplicidade
                 throw new Exception("Erro: O Código CBO '{$cod}' já está cadastrado.");
            }
            throw new Exception("Erro de banco de dados ao salvar. " . $e->getMessage());
        }
    }

    /**
     * Exclui um registro de CBO.
     * (Migrado de views/cbos.php)
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
            error_log("Erro ao excluir CBO: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                // Erro de chave estrangeira (FK)
                throw new Exception("Erro: Este CBO não pode ser excluído pois está sendo utilizado por um ou mais Cargos.");
            }
            throw new Exception("Erro de banco de dados ao excluir. " . $e->getMessage());
        }
    }

    /**
     * Busca registros de forma paginada, com filtro e ordenação.
     * (Migrado de views/cbos.php)
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

        // Alias
        $mainAlias = 'c';
        $joinAlias = 'f';

        // 2. Query para Contagem Total (com JOIN)
        $count_sql = "SELECT COUNT({$mainAlias}.{$this->idColumn}) 
                      FROM {$this->tableName} {$mainAlias}
                      LEFT JOIN familia_cbo {$joinAlias} ON {$mainAlias}.familiaCboId = {$joinAlias}.familiaCboId";
        if (!empty($term)) {
            $count_sql .= " WHERE {$mainAlias}.cboCod LIKE ? OR {$mainAlias}.cboTituloOficial LIKE ? OR {$joinAlias}.familiaCboNome LIKE ?";
            $count_bindings[] = $sqlTerm;
            $count_bindings[] = $sqlTerm;
            $count_bindings[] = $sqlTerm;
        }

        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($count_bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar CBOs: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 3. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 4. Query Principal (com JOIN)
        $sql = "SELECT {$mainAlias}.*, {$joinAlias}.familiaCboNome 
                FROM {$this->tableName} {$mainAlias}
                LEFT JOIN familia_cbo {$joinAlias} ON {$mainAlias}.familiaCboId = {$joinAlias}.familiaCboId";
        
        if (!empty($term)) {
            $sql .= " WHERE {$mainAlias}.cboCod LIKE ? OR {$mainAlias}.cboTituloOficial LIKE ? OR {$joinAlias}.familiaCboNome LIKE ?";
            $all_bindings[] = $sqlTerm;
            $all_bindings[] = $sqlTerm;
            $all_bindings[] = $sqlTerm;
        }

        // 5. Ordenação
        $orderBy = $params['order_by'] ?? "{$mainAlias}.cboCod";
        $sortDir = $params['sort_dir'] ?? 'ASC';
        
        $validColumns = [
            "{$mainAlias}.cboId", "{$mainAlias}.cboCod", "{$mainAlias}.cboTituloOficial", 
            "{$joinAlias}.familiaCboNome", "{$mainAlias}.cboDataCadastro", "{$mainAlias}.cboDataAtualizacao"
        ];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : "{$mainAlias}.cboCod";
        $sortDir = in_array(strtoupper($sortDir), ['ASC', 'DESC']) ? strtoupper($sortDir) : 'ASC';

        $sql .= " ORDER BY {$orderBy} {$sortDir}";
        $sql .= " LIMIT {$itemsPerPage} OFFSET {$offset}";

        // 6. Executa a query principal
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($all_bindings);
            $registros = $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Erro ao buscar CBOs: " . $e->getMessage());
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