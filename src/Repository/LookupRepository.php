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
     * Retorna todos os registros de uma tabela formatados como array [id => nome/concatenado].
     * (Migrado de getLookupData em functions.php)
     *
     * @param string $tableName O nome da tabela.
     * @param string $idColumn O ID da coluna.
     * @param string $nameColumn A coluna de nome.
     * @param string|null $concatColumn Coluna opcional para concatenar (usado em CBOs).
     * @return array Um array formatado [id => nome].
     */
    public function getLookup(string $tableName, string $idColumn, string $nameColumn, string $concatColumn = null): array
    {
        // NOTA: A função original isValidTableName() ainda está em functions.php.
        // Em uma refatoração completa, isso também seria movido.
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
     * (Lógica específica de cargos_form.php)
     */
    public function getNivelHierarquicoLookup(): array
    {
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