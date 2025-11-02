<?php
// Arquivo: src/Repository/AuditRepository.php

namespace App\Repository;

use App\Core\Database;
use App\Service\AuthService;
use PDO;
use Exception;

/**
 * Lida com a leitura da tabela de logs de auditoria.
 */
class AuditRepository
{
    private PDO $pdo;
    private AuthService $authService;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->authService = new AuthService();
    }

    /**
     * Busca os logs de forma paginada, com filtro.
     *
     * @param array $params Parâmetros de busca (term, acao, nomeTabela, page, limit)
     * @return array Contendo ['data', 'total', 'totalPages', 'currentPage']
     */
    public function findAllPaginated(array $params = []): array
    {
        // 1. Configuração da Paginação e Filtros
        $itemsPerPage = (int)($params['limit'] ?? 20); // Logs podem ser muitos, 20 por pág.
        $currentPage = (int)($params['page'] ?? 1);
        $currentPage = max(1, $currentPage); 
        
        $term = $params['term'] ?? '';
        $sqlTerm = "%{$term}%";
        $acao = $params['acao'] ?? '';
        $nomeTabela = $params['nomeTabela'] ?? '';
        
        $where = [];
        $bindings = [];

        // 2. Montagem dos Filtros
        if (!empty($term)) {
            $where[] = "(a.nomeUsuario LIKE :term OR a.dadosJson LIKE :term OR a.idRegistro LIKE :term)";
            $bindings[':term'] = $sqlTerm;
        }
        if (!empty($acao)) {
            $where[] = "a.acao = :acao";
            $bindings[':acao'] = $acao;
        }
        if (!empty($nomeTabela)) {
            $where[] = "a.nomeTabela = :nomeTabela";
            $bindings[':nomeTabela'] = $nomeTabela;
        }
        
        $sqlWhere = "";
        if (!empty($where)) {
            $sqlWhere = " WHERE " . implode(" AND ", $where);
        }

        // 3. Query para Contagem Total
        $count_sql = "SELECT COUNT(*) FROM audit_log a" . $sqlWhere;
        
        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar logs: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 4. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 5. Query Principal (Ordena pelos mais recentes primeiro)
        $sql = "SELECT a.* FROM audit_log a" . $sqlWhere;
        $sql .= " ORDER BY a.dataHora DESC";
        $sql .= " LIMIT :limit OFFSET :offset";

        $bindings[':limit'] = $itemsPerPage;
        $bindings[':offset'] = $offset;

        // 6. Executa a query principal
        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Bind dos parâmetros por tipo
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
            error_log("Erro ao buscar logs: " . $e->getMessage() . " SQL: " . $sql);
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
     * Busca todas as Ações distintas para os filtros.
     * @return array
     */
    public function getDistinctAcoes(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT acao FROM audit_log ORDER BY acao ASC");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            return [];
        }
    }
    
    /**
     * Busca todas as Tabelas distintas para os filtros.
     * @return array
     */
    public function getDistinctTabelas(): array
    {
         try {
            $stmt = $this->pdo->query("SELECT DISTINCT nomeTabela FROM audit_log WHERE nomeTabela IS NOT NULL ORDER BY nomeTabela ASC");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            return [];
        }
    }
}