<?php
// Arquivo: src/Repository/HabilidadeRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;
use Exception;

class HabilidadeRepository
{
    private PDO $pdo;
    private string $tableName = 'habilidades';
    private string $idColumn = 'habilidadeId';
    private string $nameColumn = 'habilidadeNome';
    private string $typeColumn = 'habilidadeTipo';
    private string $descColumn = 'habilidadeDescricao';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Retorna as opções válidas para o campo ENUM 'habilidadeTipo'.
     * (Migrado de functions.php/getEnumOptions)
     *
     * @return array Uma lista de strings com os valores ENUM.
     */
    public function getEnumOptions(): array
    {
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM {$this->tableName} LIKE '{$this->typeColumn}'");
            $row = $stmt->fetch();

            if (isset($row['Type']) && strpos($row['Type'], 'enum') !== false) {
                $enum_string = $row['Type'];
                $matches = [];
                preg_match_all('/\'([^\']+)\'/', $enum_string, $matches);
                return $matches[1];
            }
        } catch (\PDOException $e) {
            error_log("Erro ao buscar ENUM para {$this->tableName}: " . $e->getMessage());
        }
        return [];
    }

    /**
     * Salva (cria ou atualiza) um registro de habilidade.
     * (Migrado de views/habilidades.php)
     *
     * @param array $data Dados vindos do formulário ($_POST)
     * @return int O número de linhas afetadas.
     * @throws Exception Se os campos obrigatórios estiverem vazios ou inválidos.
     */
    public function save(array $data): int
    {
        $id = (int)($data[$this->idColumn] ?? 0);
        $nome = trim($data[$this->nameColumn] ?? '');
        $tipo = trim($data[$this->typeColumn] ?? '');
        $descricao = trim($data[$this->descColumn] ?? null);
        $action = $data['action'] ?? ($id > 0 ? 'update' : 'insert');

        // Validação
        if (empty($nome)) {
            throw new Exception("O nome da habilidade é obrigatório.");
        }
        $enum_options = $this->getEnumOptions();
        if (empty($tipo) || !in_array($tipo, $enum_options)) {
            throw new Exception("O tipo de habilidade selecionado não é válido.");
        }

        try {
            if ($action === 'insert') {
                $sql = "INSERT INTO {$this->tableName} ({$this->nameColumn}, {$this->typeColumn}, {$this->descColumn}, habilidadeDataCadastro) 
                        VALUES (?, ?, ?, CURRENT_TIMESTAMP())";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nome, $tipo, $descricao]);
                return $stmt->rowCount();

            } elseif ($action === 'update' && $id > 0) {
                $sql = "UPDATE {$this->tableName} SET 
                            {$this->nameColumn} = ?, 
                            {$this->typeColumn} = ?, 
                            {$this->descColumn} = ?,
                            habilidadeDataAtualizacao = CURRENT_TIMESTAMP() 
                        WHERE {$this->idColumn} = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nome, $tipo, $descricao, $id]);
                return $stmt->rowCount();
            }
            
            return 0; // Nenhuma ação válida

        } catch (\PDOException $e) {
            error_log("Erro ao salvar Habilidade: " . $e->getMessage());
            throw new Exception("Erro de banco de dados ao salvar. " . $e->getMessage());
        }
    }

    /**
     * Exclui um registro de habilidade.
     * (Migrado de views/habilidades.php)
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
            error_log("Erro ao excluir Habilidade: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                // Erro de chave estrangeira (FK)
                throw new Exception("Erro: Esta habilidade não pode ser excluída pois está sendo utilizada por um ou mais cargos.");
            }
            throw new Exception("Erro de banco de dados ao excluir. " . $e->getMessage());
        }
    }

    /**
     * Busca registros de forma paginada, com filtro e ordenação.
     * (Migrado de views/habilidades.php)
     *
     * @param array $params Parâmetros de busca (term, page, limit, order_by, sort_dir)
     * @return array Contendo ['data', 'total', 'totalPages', 'currentPage']
     */
    public function findAllPaginated(array $params = []): array
    {
        // 1. Configuração
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
            $count_sql .= " WHERE {$this->nameColumn} LIKE ? OR {$this->descColumn} LIKE ? OR {$this->typeColumn} LIKE ?";
            $count_bindings = [$sqlTerm, $sqlTerm, $sqlTerm];
        }

        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($count_bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar Habilidades: " . $e->getMessage());
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
            $sql .= " WHERE {$this->nameColumn} LIKE ? OR {$this->descColumn} LIKE ? OR {$this->typeColumn} LIKE ?";
            $all_bindings = [$sqlTerm, $sqlTerm, $sqlTerm];
        }

        // 5. Ordenação
        $orderBy = $params['order_by'] ?? $this->idColumn;
        $sortDir = $params['sort_dir'] ?? 'ASC';
        
        $validColumns = [$this->idColumn, $this->nameColumn, $this->typeColumn, $this->descColumn, 'habilidadeDataCadastro', 'habilidadeDataAtualizacao'];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : $this->idColumn;
        $sortDir = in_array(strtoupper($sortDir), ['ASC', 'DESC']) ? strtoupper($sortDir) : 'ASC';

        $sql .= " ORDER BY {$orderBy} {$sortDir}";
        $sql .= " LIMIT {$itemsPerPage} OFFSET {$offset}";

        // 6. Executa
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($all_bindings);
            $registros = $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Habilidades: " . $e->getMessage());
            $registros = [];
        }

        // 7. Retorna
        return [
            'data' => $registros,
            'total' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage
        ];
    }

    /**
     * Retorna as habilidades agrupadas por Hardskill e Softskill.
     * (Criado no Passo 9)
     * @return array Um array aninhado [tipo => [id => nome]]
     */
    public function getGroupedLookup(): array
    {
        try {
            // Busca todas as habilidades, ordenando pelo tipo (DESC para garantir Hardskill primeiro)
            $stmt = $this->pdo->query("SELECT habilidadeId, habilidadeNome, habilidadeTipo FROM habilidades ORDER BY habilidadeTipo DESC, habilidadeNome ASC");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $grouped = [];
            foreach ($results as $row) {
                $tipo = $row['habilidadeTipo'];
                // Se o campo for nulo/vazio, padroniza para "Outros"
                $tipo = empty($tipo) ? 'Outros' : $tipo; 
                
                if (!isset($grouped[$tipo])) {
                    $grouped[$tipo] = [];
                }
                $grouped[$tipo][$row['habilidadeId']] = $row['habilidadeNome'];
            }
            
            // Garante a ordem Hard Skills, Soft Skills, Outros
            $final_grouped = [];
            if (isset($grouped['Hardskill'])) { $final_grouped['Hard Skills'] = $grouped['Hardskill']; }
            if (isset($grouped['Softskill'])) { $final_grouped['Soft Skills'] = $grouped['Softskill']; }
            if (isset($grouped['Outros'])) { $final_grouped['Outros Tipos'] = $grouped['Outros']; }
            
            return $final_grouped;
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar habilidades agrupadas: " . $e->getMessage());
            return [];
        }
    }
}