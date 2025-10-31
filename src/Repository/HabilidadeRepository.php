<?php
// Arquivo: src/Repository/HabilidadeRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;

class HabilidadeRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Retorna as habilidades agrupadas por Hardskill e Softskill.
     * (Migrado de getHabilidadesGrouped em functions.php)
     * @return array Um array aninhado [tipo => [id => nome]]
     */
    public function getGroupedLookup(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT habilidadeId, habilidadeNome, habilidadeTipo FROM habilidades ORDER BY habilidadeTipo DESC, habilidadeNome ASC");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $grouped = [];
            foreach ($results as $row) {
                $tipo = $row['habilidadeTipo'];
                $tipo = empty($tipo) ? 'Outros' : $tipo; 
                
                if (!isset($grouped[$tipo])) {
                    $grouped[$tipo] = [];
                }
                $grouped[$tipo][$row['habilidadeId']] = $row['habilidadeNome'];
            }
            
            $final_grouped = [];
            if (isset($grouped['Hardskill'])) { $final_grouped['Hard Skills'] = $grouped['Hardskill']; }
            if (isset($grouped['Softskill'])) { $final_grouped['Soft Skills'] = $grouped['Softskill']; }
            if (isset($grouped['Outros'])) { $final_grouped['Outros Tipos'] = $grouped['Outros']; }
            
            return $final_grouped;
            
        } catch (\PDOException $e) {
            error_log("Erro ao buscar habilidades agrupadas: " . $e->getMessage());
            return [];
        }
    }
}