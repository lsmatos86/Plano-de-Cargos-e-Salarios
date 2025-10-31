<?php
// Arquivo: src/Repository/AreaRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;

class AreaRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Retorna as áreas para o Lookup de seleção, formatadas para mostrar o caminho hierárquico.
     * (Migrado de getAreaHierarchyLookup em functions.php)
     * @return array Um array formatado [areaId => 'Pai > Filho > Nome da Área'].
     */
    public function getHierarchyLookup(): array 
    {
        try {
            $stmt = $this->pdo->query("SELECT areaId, areaPaiId, areaNome FROM areas_atuacao");
            $flatList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar áreas para lookup: " . $e->getMessage());
            return [];
        }

        $lookup = [];
        $allAreas = array_column($flatList, null, 'areaId');

        foreach ($flatList as $area) {
            $path = [$area['areaNome']];
            $currentId = $area['areaPaiId'];
            
            while ($currentId !== null && isset($allAreas[$currentId])) {
                array_unshift($path, $allAreas[$currentId]['areaNome']);
                $currentId = $allAreas[$currentId]['areaPaiId'];
            }
            
            $lookup[$area['areaId']] = implode(' > ', $path);
        }
        
        asort($lookup);
        return $lookup;
    }
}