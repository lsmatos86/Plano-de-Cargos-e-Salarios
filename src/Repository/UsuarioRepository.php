<?php
// Arquivo: src/Repository/UsuarioRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Lida com todas as operações de banco de dados para a entidade Usuario.
 */
class UsuarioRepository
{
    private PDO $pdo;
    private string $tableName = 'usuarios';
    private string $idColumn = 'usuarioId';
    private string $nameColumn = 'nome';
    private string $emailColumn = 'email';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Verifica se um e-mail já existe, opcionalmente ignorando um ID.
     */
    private function emailExists(string $email, int $ignoreId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE {$this->emailColumn} = ?";
        $bindings = [$email];
        
        if ($ignoreId > 0) {
            $sql .= " AND {$this->idColumn} != ?";
            $bindings[] = $ignoreId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Salva (cria ou atualiza) um registro de usuário.
     * (Migrado de views/usuarios.php)
     *
     * @param array $data Dados vindos do formulário ($_POST)
     * @return int O número de linhas afetadas.
     * @throws Exception Se a validação falhar.
     */
    public function save(array $data): int
    {
        $id = (int)($data[$this->idColumn] ?? 0);
        $action = $data['action'] ?? ($id > 0 ? 'update' : 'insert');
        $nome = trim($data['nome'] ?? '');
        $email = trim($data['email'] ?? '');
        $senha = $data['senha'] ?? '';
        $senha_confirmacao = $data['senha_confirmacao'] ?? '';
        $ativo = isset($data['ativo']) ? 1 : 0;

        // --- Validação ---
        if (empty($nome) || empty($email)) {
            throw new Exception("Nome e E-mail são obrigatórios.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("O e-mail fornecido não é válido.");
        }
        if ($this->emailExists($email, $id)) {
            throw new Exception("O e-mail '{$email}' já está em uso por outro usuário.");
        }

        // --- Lógica de Senha ---
        $senhaSql = "";
        $bindings = [];

        if ($action === 'insert') {
            if (empty($senha)) {
                throw new Exception("A senha é obrigatória para novos usuários.");
            }
            if ($senha !== $senha_confirmacao) {
                throw new Exception("As senhas não conferem.");
            }
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
            $senhaSql = ", senha = ?";
            $bindings[] = $hashSenha;

        } elseif ($action === 'update' && !empty($senha)) {
            // Só atualiza a senha se ela foi fornecida
            if ($senha !== $senha_confirmacao) {
                throw new Exception("As senhas não conferem.");
            }
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
            $senhaSql = ", senha = ?";
            $bindings[] = $hashSenha;
        }
        // Se for 'update' e a senha estiver vazia, $senhaSql permanece vazio e a senha não é alterada.

        // --- Execução ---
        try {
            if ($action === 'insert') {
                $sql = "INSERT INTO {$this->tableName} (nome, email, ativo, dataCadastro {$senhaSql}) 
                        VALUES (?, ?, ?, CURRENT_TIMESTAMP() {$senhaSql})";
                
                // Adiciona os dados principais aos bindings
                array_unshift($bindings, $nome, $email, $ativo);
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($bindings);
                return $stmt->rowCount();

            } elseif ($action === 'update' && $id > 0) {
                $sql = "UPDATE {$this->tableName} SET 
                            nome = ?, 
                            email = ?, 
                            ativo = ?,
                            dataAtualizacao = CURRENT_TIMESTAMP() 
                            {$senhaSql}
                        WHERE {$this->idColumn} = ?";
                
                // Adiciona os dados principais aos bindings
                array_unshift($bindings, $nome, $email, $ativo);
                // Adiciona o ID no final
                $bindings[] = $id;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($bindings);
                return $stmt->rowCount();
            }
            
            return 0; // Nenhuma ação válida

        } catch (\PDOException $e) {
            error_log("Erro ao salvar Usuário: " . $e->getMessage());
            throw new Exception("Erro de banco de dados ao salvar. " . $e->getMessage());
        }
    }

    /**
     * Exclui um registro de usuário.
     *
     * @param int $id O ID a ser excluído.
     * @param int $currentUserId O ID do usuário logado (para evitar auto-exclusão).
     * @return int O número de linhas afetadas.
     * @throws Exception Em caso de falha.
     */
    public function delete(int $id, int $currentUserId): int
    {
        if ($id === $currentUserId) {
            throw new Exception("Você não pode excluir seu próprio usuário.");
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->tableName} WHERE {$this->idColumn} = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Erro ao excluir Usuário: " . $e->getMessage());
            throw new Exception("Erro de banco de dados ao excluir. " . $e->getMessage());
        }
    }

    /**
     * Busca registros de forma paginada, com filtro e ordenação.
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
            $count_sql .= " WHERE {$this->nameColumn} LIKE ? OR {$this->emailColumn} LIKE ?";
            $count_bindings = [$sqlTerm, $sqlTerm];
        }

        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($count_bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar Usuários: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 3. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 4. Query Principal
        $sql = "SELECT {$this->idColumn}, {$this->nameColumn}, {$this->emailColumn}, ativo, dataCadastro, dataAtualizacao 
                FROM {$this->tableName}";
        if (!empty($term)) {
            $sql .= " WHERE {$this->nameColumn} LIKE ? OR {$this->emailColumn} LIKE ?";
            $all_bindings = [$sqlTerm, $sqlTerm];
        }

        // 5. Ordenação
        $orderBy = $params['order_by'] ?? $this->idColumn;
        $sortDir = $params['sort_dir'] ?? 'ASC';
        
        $validColumns = [$this->idColumn, $this->nameColumn, $this->emailColumn, 'ativo', 'dataCadastro', 'dataAtualizacao'];
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
            error_log("Erro ao buscar Usuários: " . $e->getMessage());
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
}