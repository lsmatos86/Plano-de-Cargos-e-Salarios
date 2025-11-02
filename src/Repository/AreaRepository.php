<?php
// Arquivo: src/Repository/AreaRepository.php (Atualizado com Auditoria)

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;  // <-- PASSO 1: Incluir
use App\Service\AuthService;   // <-- PASSO 1: Incluir (Boa prática)
use PDO;
use Exception;

class AreaRepository
{
    private PDO $pdo;
    private AuditService $auditService; // <-- PASSO 2: Adicionar propriedade
    private AuthService $authService;   // <-- PASSO 2: Adicionar propriedade

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        // ======================================================
        // PASSO 2: Inicializar os serviços
        // ======================================================
        $this->auditService = new AuditService();
        $this->authService = new AuthService();
    }

    // ... (métodos find, findAll, getHierarchyLookup não mudam) ...
    
    public function find(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM areas_atuacao WHERE areaId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM areas_atuacao ORDER BY areaNome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHierarchyLookup(): array
    {
        $areas = $this->findAll();
        $map = [];
        foreach ($areas as $area) {
            $map[$area['areaId']] = $area;
        }

        $lookup = [];
        foreach ($areas as $area) {
            $path = $this->getAreaPath($area, $map);
            $lookup[$area['areaId']] = $path;
        }
        asort($lookup);
        return $lookup;
    }

    private function getAreaPath(array $area, array $map): string
    {
        $path = $area['areaNome'];
        $current = $area;
        while ($current['areaPaiId'] !== null && isset($map[$current['areaPaiId']])) {
            $parent = $map[$current['areaPaiId']];
            $path = $parent['areaNome'] . ' > ' . $path;
            $current = $parent;
        }
        return $path;
    }

    /**
     * Salva (cria ou atualiza) uma Área de Atuação.
     */
    public function save(array $data): int
    {
        // Define o nome da tabela para a auditoria
        $tableName = 'areas_atuacao';

        // 1. Coleta de Dados
        $id = (int)($data['areaId'] ?? 0);
        $nome = trim($data['areaNome'] ?? '');
        $descricao = trim($data['areaDescricao'] ?? null);
        $areaPaiId = empty($data['areaPaiId']) ? null : (int)$data['areaPaiId'];
        $isUpdating = $id > 0;

        // 2. Validação de Permissão e Dados
        $permissionNeeded = $isUpdating ? 'estruturas:edit' : 'estruturas:create';
        $this->authService->checkAndFail($permissionNeeded);

        if (empty($nome)) {
            throw new Exception("O nome da área é obrigatório.");
        }
        
        // Proteção contra auto-referência
        if ($isUpdating && $id === $areaPaiId) {
             throw new Exception("Uma área não pode ser pai dela mesma.");
        }

        // 3. SQL
        $params = [
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':areaPaiId' => $areaPaiId
        ];

        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET areaNome = :nome, areaDescricao = :descricao, areaPaiId = :areaPaiId WHERE areaId = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE UPDATE
                // ======================================================
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
                
            } else {
                $sql = "INSERT INTO {$tableName} (areaNome, areaDescricao, areaPaiId) VALUES (:nome, :descricao, :areaPaiId)";
                $this->pdo->prepare($sql)->execute($params);
                $savedId = (int)$this->pdo->lastInsertId();
                
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE CREATE
                // ======================================================
                $this->auditService->log('CREATE', $tableName, $savedId, $data);
            }
            
            return $savedId;

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                 throw new Exception("A área '$nome' já existe.");
            }
            throw $e; // Propaga outros erros
        }
    }

    /**
     * Exclui uma Área de Atuação.
     */
    public function delete(int $id): bool
    {
        // Define o nome da tabela para a auditoria
        $tableName = 'areas_atuacao';
        
        $this->authService->checkAndFail('estruturas:delete');

        try {
            // 1. Verifica se a área está sendo usada como pai
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM {$tableName} WHERE areaPaiId = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Esta área não pode ser excluída pois é usada como 'Área Pai' por outras áreas.");
            }
            
            // 2. Verifica se a área está sendo usada por um cargo
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM cargos_area WHERE areaId = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Esta área não pode ser excluída pois está associada a um ou mais cargos.");
            }

            // 3. Exclui
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE areaId = ?");
            $stmt->execute([$id]);
            
            $success = $stmt->rowCount() > 0;
            
            if ($success) {
                // ======================================================
                // PASSO 3: REGISTRAR O LOG DE DELETE
                // ======================================================
                $this->auditService->log('DELETE', $tableName, $id, ['deletedId' => $id]);
            }
            
            return $success;

        } catch (Exception $e) {
            // Se for erro de FK (mesmo que tenhamos verificado, por segurança)
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                 throw new Exception("Esta área não pode ser excluída pois está em uso em outra parte do sistema.");
            }
            throw $e; // Propaga outros erros
        }
    }
}