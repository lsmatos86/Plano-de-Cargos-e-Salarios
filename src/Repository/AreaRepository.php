<?php
// Arquivo: src/Repository/AreaRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;
use Exception;

class AreaRepository
{
    private PDO $pdo;
    private string $tableName = 'areas_atuacao';
    private string $idColumn = 'areaId';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Salva (cria ou atualiza) um registro de área.
     * (Migrado de views/areas_atuacao.php)
     *
     * @param array $data Dados vindos do formulário ($_POST)
     * @return int O número de linhas afetadas.
     * @throws Exception Se o nome estiver vazio.
     */
    public function save(array $data): int
    {
        $id = (int)($data[$this->idColumn] ?? 0);
        $nome = trim($data['areaNome'] ?? '');
        $descricao = trim($data['areaDescricao'] ?? null);
        $codigo = trim($data['areaCodigo'] ?? null);
        // Converte string vazia para NULL para o banco de dados
        $areaPaiId = empty($data['areaPaiId']) ? null : (int)$data['areaPaiId'];
        
        $action = $data['action'] ?? ($id > 0 ? 'update' : 'insert');

        // Validação
        if (empty($nome)) {
            throw new Exception("O nome da Área é obrigatório.");
        }
        
        // Evita que uma área seja "pai" dela mesma
        if ($id > 0 && $id === $areaPaiId) {
             throw new Exception("Uma área não pode ser hierarquicamente superior a ela mesma.");
        }

        try {
            if ($action === 'insert') {
                $sql = "INSERT INTO {$this->tableName} (areaNome, areaDescricao, areaCodigo, areaPaiId, areaDataCadastro) 
                        VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP())";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nome, $descricao, $codigo, $areaPaiId]);
                return $stmt->rowCount();

            } elseif ($action === 'update' && $id > 0) {
                $sql = "UPDATE {$this->tableName} SET 
                            areaNome = ?, 
                            areaDescricao = ?, 
                            areaCodigo = ?, 
                            areaPaiId = ?,
                            areaDataAtualizacao = CURRENT_TIMESTAMP() 
                        WHERE {$this->idColumn} = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nome, $descricao, $codigo, $areaPaiId, $id]);
                return $stmt->rowCount();
            }
            
            return 0; // Nenhuma ação válida

        } catch (\PDOException $e) {
            error_log("Erro ao salvar Área de Atuação: " . $e->getMessage());
            throw new Exception("Erro de banco de dados ao salvar. " . $e->getMessage());
        }
    }

    /**
     * Exclui um registro de área.
     * (Migrado de views/areas_atuacao.php)
     *
     * @param int $id O ID a ser excluído.
     * @return int O número de linhas afetadas.
     * @throws Exception Em caso de falha.
     */
    public function delete(int $id): int
    {
        try {
            // Antes de deletar, atualiza áreas filhas para não terem pai (Nível Raiz)
            // Isso evita o erro de FK e órfãos
            $stmtUpdate = $this->pdo->prepare("UPDATE {$this->tableName} SET areaPaiId = NULL WHERE areaPaiId = ?");
            $stmtUpdate->execute([$id]);

            // Agora deleta a área
            $stmt = $this->pdo->prepare("DELETE FROM {$this->tableName} WHERE {$this->idColumn} = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount();

        } catch (\PDOException $e) {
            error_log("Erro ao excluir Área de Atuação: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                throw new Exception("Erro: Esta Área não pode ser excluída pois está sendo utilizada por um ou mais Cargos.");
            }
            throw new Exception("Erro de banco de dados ao excluir. " . $e->getMessage());
        }
    }

    /**
     * Busca todas as áreas e as retorna em uma árvore hierárquica.
     * (Combina o SELECT e a função buildAreaHierarchy)
     *
     * @return array A árvore hierárquica de áreas.
     */
    public function findAllHierarchical(): array
    {
        try {
            // 1. Busca a lista "flat"
            $stmt = $this->pdo->query("SELECT * FROM {$this->tableName} ORDER BY {$this->idColumn}");
            $flatList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Constrói a árvore
            return $this->buildAreaHierarchy($flatList, null);

        } catch (\PDOException $e) {
            error_log("Erro ao buscar Áreas de Atuação: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Converte a lista de áreas (com ID Pai) para uma estrutura hierárquica aninhada.
     * (Migrado de functions.php e tornado privado)
     *
     * @param array $flatList Lista de áreas (flat array)
     * @param int|null $parentId ID da área pai para começar
     * @return array Árvore hierárquica
     */
    private function buildAreaHierarchy(array $flatList, $parentId = null): array 
    {
        $branch = [];
        foreach ($flatList as $area) {
            $areaPaiId = ($area['areaPaiId'] === null) ? null : (int)$area['areaPaiId']; 
            
            if ($areaPaiId === $parentId) {
                $children = $this->buildAreaHierarchy($flatList, (int)$area['areaId']);
                if ($children) {
                    $area['children'] = $children;
                }
                $branch[] = $area;
            }
        }
        return $branch;
    }

    /**
     * Retorna as áreas para o Lookup de seleção, formatadas para mostrar o caminho hierárquico.
     * (Criado no Passo 9)
     *
     * @return array Um array formatado [areaId => 'Pai > Filho > Nome da Área'].
     */
    public function getHierarchyLookup(): array 
    {
        try {
            $stmt = $this->pdo->query("SELECT areaId, areaPaiId, areaNome FROM areas_atuacao");
            $flatList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar áreas para lookup: " . $e->getMessage());
            return [];
        }

        $lookup = [];
        $allAreas = array_column($flatList, null, 'areaId');

        // Mapeia para exibir o caminho completo (ex: Diretoria > Financeiro)
        foreach ($flatList as $area) {
            $path = [$area['areaNome']];
            $currentId = $area['areaPaiId'];
            
            // Constrói o caminho reverso até a raiz
            while ($currentId !== null && isset($allAreas[$currentId])) {
                array_unshift($path, $allAreas[$currentId]['areaNome']);
                $currentId = $allAreas[$currentId]['areaPaiId'];
            }
            
            $lookup[$area['areaId']] = implode(' > ', $path);
        }
        
        // Ordena pelo caminho completo para exibição no SELECT
        asort($lookup);
        return $lookup;
    }
}