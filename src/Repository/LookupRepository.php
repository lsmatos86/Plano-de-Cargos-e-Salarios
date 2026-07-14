<?php
// Arquivo: src/Repository/LookupRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;

/**
 * Repositório genérico para tabelas de busca simples (Lookups).
 */
class LookupRepository {
    
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Retorna um lookup no formato [id => nome] para tabelas auxiliares.
     */
    public function getLookup(string $table, string $keyColumn, string $valueColumn): array {
        $allowedTables = [
            'areas_atuacao', 'caracteristicas', 'cargos', 'cbos', 'cursos',
            'escolaridades', 'faixas_salariais', 'familia_cbo', 'habilidades',
            'nivel_hierarquico', 'recursos', 'recursos_grupos', 'riscos',
            'tipo_hierarquia',
        ];

        if (!in_array($table, $allowedTables, true)) {
            throw new \InvalidArgumentException('Tabela de lookup não permitida: ' . $table);
        }

        foreach ([$keyColumn, $valueColumn] as $column) {
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column)) {
                throw new \InvalidArgumentException('Coluna de lookup inválida.');
            }
        }

        $quotedTable = Database::quoteIdent($table);
        $quotedKey = Database::quoteIdent($keyColumn);
        $quotedValue = Database::quoteIdent($valueColumn);
        $sql = "SELECT {$quotedKey}, {$quotedValue} FROM {$quotedTable} ORDER BY {$quotedValue} ASC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Retorna a quantidade total de registros em uma determinada tabela.
     * Método adicionado para atender a geração de indicadores da index/dashboard.
     * * @param string $table Nome da tabela
     * @return int
     */
    public function countRecords(string $table): int {
        // Lista de tabelas permitidas para evitar SQL Injection dinâmico
        $allowedTables = [
            'escolaridades', 'areas_atuacao', 'niveis_hierarquicos', 
            'tipos_hierarquia', 'riscos', 'cursos', 'recursos',
            'cargos', 'usuarios', 'pesquisas'
        ];

        if (!in_array($table, $allowedTables)) {
            return 0;
        }

        $sql = "SELECT COUNT(*) FROM {$table}";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca todos os registros de uma tabela ordenada por uma coluna.
     * * @param string $table Nome da tabela
     * @param string $orderBy Coluna para ordenação
     * @return array
     */
    public function getAll(string $table, string $orderBy = 'nome'): array {
        // Validação básica para evitar SQL Injection no nome da tabela/coluna dinâmicos
        $allowedTables = [
            'escolaridades', 'areas_atuacao', 'niveis_hierarquicos', 
            'tipos_hierarquia', 'riscos', 'cursos', 'recursos',
            'cargos', 'usuarios', 'pesquisas'
        ];
        
        if (!in_array($table, $allowedTables)) {
            return [];
        }

        // Garante que a coluna de ordenação seja minimamente segura
        $orderBy = preg_match('/^[a-zA-Z0-9_]+$/', $orderBy) ? $orderBy : 'nome';

        $sql = "SELECT * FROM {$table} ORDER BY {$orderBy} ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um registro específico por ID em uma tabela informada.
     * * @param string $table Nome da tabela
     * @param string $pkName Nome da chave primária (ex: 'id_escolaridade')
     * @param int $id Valor do ID
     * @return array|bool
     */
    public function getById(string $table, string $pkName, int $id) {
        $allowedTables = [
            'escolaridades', 'areas_atuacao', 'niveis_hierarquicos', 
            'tipos_hierarquia', 'riscos', 'cursos', 'recursos',
            'cargos', 'usuarios', 'pesquisas'
        ];

        if (!in_array($table, $allowedTables) || !preg_match('/^[a-zA-Z0-9_]+$/', $pkName)) {
            return false;
        }

        $sql = "SELECT * FROM {$table} WHERE {$pkName} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
