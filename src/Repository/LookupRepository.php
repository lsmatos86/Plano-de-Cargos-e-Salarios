<?php
// Arquivo: src/Repository/LookupRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;

/**
 * Lida com operações genéricas de busca de dados para Lookups (Selects).
 */
class LookupRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * NOVO MÉTODO (Passo 24)
     * Retorna a contagem de registros de uma tabela.
     * (Migrado de index.php)
     *
     * @param string $tableName O nome da tabela.
     * @return int A contagem total.
     */
    public function countRecords(string $tableName): int
    {
        // Validação simples para segurança
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            error_log("Tentativa de contagem em tabela inválida: {$tableName}");
            return 0;
        }

        try {
            // Usamos isValidTableName que ainda está em functions.php
            // Se a tabela não for permitida, retorna 0
            if (!isValidTableName($tableName)) {
                 error_log("Contagem bloqueada para tabela não permitida: {$tableName}");
                 return 0;
            }

            $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$tableName}");
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("Erro ao contar registros da tabela {$tableName}: " . $e->getMessage());
            return 0;
        }
    }


    /**
     * Retorna todos os registros de uma tabela formatados como array [id => nome/concatenado].
     * (Criado no Passo 9)
     */
    public function getLookup(string $tableName, string $idColumn, string $nameColumn, string $concatColumn = null): array
    {
        // ... (código do getLookup que já migrámos) ...
        if (!isValidTableName($tableName)) {
            error_log("Tentativa de Lookup em tabela inválida: {$tableName}");
            return [];
        }
        try {
            $selectFields = $idColumn . ', ' . $nameColumn;
            if ($concatColumn && $tableName === 'cbos') {
                $selectFields = "{$idColumn}, CONCAT({$nameColumn}, ' - ', {$concatColumn}) AS display_name";
                $nameColumn = 'display_name'; 
            }
            $stmt = $this->pdo->query("SELECT {$selectFields} FROM {$tableName} ORDER BY {$nameColumn} ASC");
            if ($concatColumn && $tableName === 'cbos') {
                $results = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results[$row[$idColumn]] = $row['display_name'];
                }
                return $results;
            }
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\PDOException $e) {
            error_log("Erro no Lookup da tabela {$tableName}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna os níveis hierárquicos formatados para o select
     * (Criado no Passo 9)
     */
    public function getNivelHierarquicoLookup(): array
    {
        // ... (código do getNivelHierarquicoLookup que já migrámos) ...
        $niveisOrdenados = [];
        $niveis = $this->getLookup('nivel_hierarquico', 'nivelId', 'nivelOrdem');
        foreach ($niveis as $id => $ordem) {
            $stmt = $this->pdo->prepare("SELECT nivelOrdem, nivelDescricao FROM nivel_hierarquico WHERE nivelId = ?");
            $stmt->execute([$id]);
            $nivelData = $stmt->fetch();
            if ($nivelData) {
                $niveisOrdenados[$id] = "{$nivelData['nivelOrdem']}º - " . ($nivelData['nivelDescricao'] ?? 'N/A');
            }
        }
        arsort($niveisOrdenados);
        return $niveisOrdenados;
    }
}