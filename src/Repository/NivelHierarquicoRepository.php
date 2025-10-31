<?php
// Arquivo: src/Repository/NivelHierarquicoRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Lida com todas as operações de banco de dados para a entidade NivelHierarquico.
 */
class NivelHierarquicoRepository
{
    private PDO $pdo;
    private string $tableName = 'nivel_hierarquico';
    private string $idColumn = 'nivelId';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Salva (cria ou atualiza) um registro de nível hierárquico.
     * (Migrado de views/nivel_hierarquico.php)
     *
     * @param array $data Dados vindos do formulário ($_POST)
     * @return int O número de linhas afetadas.
     * @throws Exception Se os campos obrigatórios estiverem vazios.
     */
    public function save(array $data): int
    {
        // Campos do formulário
        $id = (int)($data[$this->idColumn] ?? 0);
        $ordem = (int)($data['nivelOrdem'] ?? 0);
        $descricao = trim($data['nivelDescricao'] ?? '');
        $tipoId = (int)($data['tipoId'] ?? 0);
        $atribuicoes = trim($data['nivelAtribuicoes'] ?? null);
        $autonomia = trim($data['nivelAutonomia'] ?? null);
        $quandoUtilizar = trim($data['nivelQuandoUtilizar'] ?? null);
        
        // No formulário original, a ação não era enviada, então determinamos aqui
        $action = ($id > 0 ? 'update' : 'insert');

        // Validação
        if (empty($ordem) || empty($descricao) || empty($tipoId)) {
            throw new Exception("Os campos Ordem, Descrição e Tipo de Hierarquia são obrigatórios.");
        }

        try {
            if ($action === 'insert') {
                $sql = "INSERT INTO {$this->tableName} 
                            (nivelOrdem, nivelDescricao, tipoId, nivelAtribuicoes, nivelAutonomia, nivelQuandoUtilizar, nivelDataCadastro) 
                        VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP())";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$ordem, $descricao, $tipoId, $atribuicoes, $autonomia, $quandoUtilizar]);
                return $stmt->rowCount();

            } elseif ($action === 'update' && $id > 0) {
                $sql = "UPDATE {$this->tableName} SET 
                            nivelOrdem = ?, 
                            nivelDescricao = ?, 
                            tipoId = ?, 
                            nivelAtribuicoes = ?, 
                            nivelAutonomia = ?, 
                            nivelQuandoUtilizar = ?,
                            nivelDataAtualizacao = CURRENT_TIMESTAMP() 
                        WHERE {$this->idColumn} = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$ordem, $descricao, $tipoId, $atribuicoes, $autonomia, $quandoUtilizar, $id]);
                return $stmt->rowCount();
            }
            
            return 0; // Nenhuma ação válida

        } catch (\PDOException $e) {
            error_log("Erro ao salvar Nível Hierárquico: " . $e->getMessage());
            if ($e->getCode() == 23000) { // Erro de duplicidade (provavelmente na Ordem)
                 throw new Exception("Erro: A ordem '{$ordem}' já está em uso.");
            }
            throw new Exception("Erro de banco de dados ao salvar. " . $e->getMessage());
        }
    }

    /**
     * Exclui um registro de nível hierárquico.
     * (Migrado de views/nivel_hierarquico.php)
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
            error_log("Erro ao excluir Nível Hierárquico: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                // Erro de chave estrangeira (FK)
                throw new Exception("Erro: Este Nível Hierárquico não pode ser excluído pois está sendo utilizado por um ou mais Cargos.");
            }
            throw new Exception("Erro de banco de dados ao excluir. " . $e->getMessage());
        }
    }

    /**
     * Busca registros de forma paginada, com filtro e ordenação.
     * (Migrado de views/nivel_hierarquico.php)
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
        $mainAlias = 'n';
        $joinAlias = 't';

        // 2. Query para Contagem Total (com JOIN)
        $count_sql = "SELECT COUNT({$mainAlias}.{$this->idColumn}) 
                      FROM {$this->tableName} {$mainAlias}
                      LEFT JOIN tipo_hierarquia {$joinAlias} ON {$mainAlias}.tipoId = {$joinAlias}.tipoId";
        if (!empty($term)) {
            $count_sql .= " WHERE {$mainAlias}.nivelDescricao LIKE ? OR {$joinAlias}.tipoNome LIKE ?";
            $count_bindings[] = $sqlTerm;
            $count_bindings[] = $sqlTerm;
        }

        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($count_bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar Níveis Hierárquicos: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 3. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 4. Query Principal (com JOIN)
        $sql = "SELECT {$mainAlias}.*, {$joinAlias}.tipoNome 
                FROM {$this->tableName} {$mainAlias}
                LEFT JOIN tipo_hierarquia {$joinAlias} ON {$mainAlias}.tipoId = {$joinAlias}.tipoId";
        
        if (!empty($term)) {
            $sql .= " WHERE {$mainAlias}.nivelDescricao LIKE ? OR {$joinAlias}.tipoNome LIKE ?";
            $all_bindings[] = $sqlTerm;
            $all_bindings[] = $sqlTerm;
        }

        // 5. Ordenação
        $orderBy = $params['order_by'] ?? "{$mainAlias}.nivelOrdem";
        $sortDir = $params['sort_dir'] ?? 'DESC';
        
        $validColumns = [
            "{$mainAlias}.nivelId", "{$mainAlias}.nivelOrdem", "{$mainAlias}.nivelDescricao", 
            "{$joinAlias}.tipoNome", "{$mainAlias}.nivelDataCadastro", "{$mainAlias}.nivelDataAtualizacao"
        ];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : "{$mainAlias}.nivelOrdem";
        $sortDir = in_array(strtoupper($sortDir), ['ASC', 'DESC']) ? strtoupper($sortDir) : 'DESC';

        $sql .= " ORDER BY {$orderBy} {$sortDir}";
        $sql .= " LIMIT {$itemsPerPage} OFFSET {$offset}";

        // 6. Executa a query principal
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($all_bindings);
            $registros = $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Níveis Hierárquicos: " . $e->getMessage());
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