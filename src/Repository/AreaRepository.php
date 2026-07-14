<?php
// Arquivo: src/Repository/AreaRepository.php (Atualizado com Auditoria, Paginação e Correção de Hierarquia)

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;
use App\Service\AuthService;
use PDO;
use Exception;

class AreaRepository
{
    private PDO $pdo;
    private AuditService $auditService;
    private AuthService $authService;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->auditService = new AuditService();
        $this->authService = new AuthService();
    }

    // ======================================================
    // MÉTODOS DE BUSCA E LISTAGEM
    // ======================================================

    public function find(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM areas_atuacao WHERE \"areaId\" = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM areas_atuacao ORDER BY \"areaNome\" ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findAllSimple(): array
    {
        $stmt = $this->pdo->query("SELECT \"areaId\", \"areaNome\" FROM areas_atuacao ORDER BY \"areaNome\" ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllPaginated(array $params = []): array
    {
        $itemsPerPage = (int)($params['limit'] ?? 10);
        $currentPage  = max(1, (int)($params['page'] ?? 1));
        $term         = $params['term'] ?? '';
        $sqlTerm      = "%{$term}%";

        $whereClause  = '';
        $bindings     = [];

        if (!empty($term)) {
            $whereClause = ' WHERE a."areaNome" ILIKE :term';
            $bindings[':term'] = $sqlTerm;
        }

        try {
            $countStmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM areas_atuacao a" . $whereClause
            );
            $countStmt->execute($bindings);
            $totalRecords = (int)$countStmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar áreas: " . $e->getMessage());
            $totalRecords = 0;
        }

        $totalPages  = $totalRecords > 0 ? (int)ceil($totalRecords / $itemsPerPage) : 1;
        $currentPage = min($currentPage, $totalPages);
        $offset      = ($currentPage - 1) * $itemsPerPage;

        $orderBy  = $params['order_by'] ?? 'areaId';
        $sortDir  = in_array(strtoupper($params['sort_dir'] ?? 'ASC'), ['ASC', 'DESC'])
                    ? strtoupper($params['sort_dir'] ?? 'ASC') : 'ASC';
        $validCols = ['areaId', 'areaNome', 'areaPaiNome'];
        if (!in_array($orderBy, $validCols)) $orderBy = 'areaId';

        $orderSql = $orderBy === 'areaPaiNome'
            ? "p.\"areaNome\" {$sortDir}"
            : "a.\"{$orderBy}\" {$sortDir}";

        $sql = "
            SELECT a.\"areaId\", a.\"areaNome\", a.\"areaDescricao\", a.\"areaPaiId\",
                   p.\"areaNome\" AS \"areaPaiNome\"
            FROM areas_atuacao a
            LEFT JOIN areas_atuacao p ON p.\"areaId\" = a.\"areaPaiId\"
            {$whereClause}
            ORDER BY {$orderSql}
            LIMIT :limit OFFSET :offset
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            if (!empty($term)) {
                $stmt->bindParam(':term', $bindings[':term']);
            }
            $stmt->bindValue(':limit',  $itemsPerPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset,       PDO::PARAM_INT);
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar áreas paginadas: " . $e->getMessage());
            $registros = [];
        }

        return [
            'data'        => $registros,
            'total'       => $totalRecords,
            'totalPages'  => $totalPages,
            'currentPage' => $currentPage,
        ];
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
        $visited = []; // Proteção contra loop infinito em dados corrompidos
        
        while ($current['areaPaiId'] !== null && isset($map[$current['areaPaiId']])) {
            if (in_array($current['areaPaiId'], $visited)) {
                break; // Quebra o loop se detetar circularidade antiga
            }
            $visited[] = $current['areaPaiId'];
            $parent = $map[$current['areaPaiId']];
            $path = $parent['areaNome'] . ' > ' . $path;
            $current = $parent;
        }
        return $path;
    }

    /**
     * Verifica recursivamente se um potencial pai é descendente do ID atual (evita loop circular).
     */
    private function isDescendant(int $currentId, ?int $potentialParentId): bool
    {
        if ($potentialParentId === null) {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT areaPaiId FROM areas_atuacao WHERE areaId = ?");
        $stmt->execute([$potentialParentId]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$parent) {
            return false;
        }

        if ((int)$parent['areaPaiId'] === $currentId) {
            return true;
        }

        return $this->isDescendant($currentId, $parent['areaPaiId'] ? (int)$parent['areaPaiId'] : null);
    }

    /**
     * Salva (cria ou atualiza) uma Área de Atuação.
     */
    public function save(array $data): int
    {
        $tableName = 'areas_atuacao';

        // 1. Coleta e sanitização de Dados
        $id = (int)($data['areaId'] ?? 0);
        $nome = trim($data['areaNome'] ?? '');
        $descricao = trim($data['areaDescricao'] ?? '');
        $areaPaiId = empty($data['areaPaiId']) ? null : (int)$data['areaPaiId'];
        $isUpdating = $id > 0;

        // 2. Validação de Permissão e Dados
        $permissionNeeded = 'areas:manage';
        $this->authService->checkAndFail($permissionNeeded);

        if (empty($nome)) {
            throw new Exception("O nome da área é obrigatório.");
        }
        
        if ($isUpdating) {
            // Proteção contra auto-referência direta
            if ($id === $areaPaiId) {
                 throw new Exception("Uma área não pode ser pai dela mesma.");
            }
            // Proteção contra referência circular complexa (Pai se tornar filho do próprio filho)
            if ($areaPaiId !== null && $this->isDescendant($id, $areaPaiId)) {
                throw new Exception("Referência circular: a área pai selecionada já é uma sub-área desta área.");
            }
        }

        // 3. SQL e Parâmetros explicitamente limpos
        $params = [
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':areaPaiId' => $areaPaiId
        ];

        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET \"areaNome\" = :nome, \"areaDescricao\" = :descricao, \"areaPaiId\" = :areaPaiId WHERE \"areaId\" = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
                
            } else {
                $sql = "INSERT INTO {$tableName} (\"areaNome\", \"areaDescricao\", \"areaPaiId\") VALUES (:nome, :descricao, :areaPaiId)";
                $this->pdo->prepare($sql)->execute($params);
                $savedId = (int)$this->pdo->lastInsertId();
                
                $this->auditService->log('CREATE', $tableName, $savedId, $data);
            }
            
            return $savedId;

        } catch (Exception $e) {
            if ($e->getCode() == '23505') {
                 throw new Exception("A área '$nome' já existe.");
            }
            throw $e;
        }
    }

    /**
     * Exclui uma Área de Atuação.
     */
    public function delete(int $id): bool
    {
        $tableName = 'areas_atuacao';
        
        $this->authService->checkAndFail('estruturas:delete');

        try {
            // 1. Verifica se a área está sendo usada como pai
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM {$tableName} WHERE \"areaPaiId\" = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Esta área não pode ser excluída pois é usada como 'Área Pai' por outras áreas.");
            }
            
            // 2. Verifica se a área está sendo usada por um cargo
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM cargos_area WHERE \"areaId\" = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Esta área não pode ser excluída pois está associada a um ou mais cargos.");
            }

            // 3. Exclui
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE \"areaId\" = ?");
            $stmt->execute([$id]);
            
            $success = $stmt->rowCount() > 0;
            
            if ($success) {
                $this->auditService->log('DELETE', $tableName, $id, ['deletedId' => $id]);
            }
            
            return $success;

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                 throw new Exception("Esta área não pode ser excluída pois está em uso em outra parte do sistema.");
            }
            throw $e;
        }
    }
}
