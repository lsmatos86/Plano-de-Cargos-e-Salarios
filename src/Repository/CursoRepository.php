<?php
// Arquivo: src/Repository/CursoRepository.php

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;
use App\Service\AuthService;
use PDO;
use Exception;

class CursoRepository
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

    public function find(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM cursos WHERE cursoId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM cursos ORDER BY cursoNome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna lookups simples (id => nome) para SELECTs.
     */
    public function findAllLookup(): array
    {
        $stmt = $this->pdo->query("SELECT cursoId, cursoNome FROM cursos ORDER BY cursoNome ASC");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Busca dados dos cursos de forma paginada incluindo a nova coluna de periodicidade.
     */
    public function findAllPaginated(array $params = []): array
    {
        $itemsPerPage = (int)($params['limit'] ?? 10);
        $currentPage = (int)($params['page'] ?? 1);
        $term = $params['term'] ?? '';
        
        $sortCol = in_array($params['order_by'] ?? 'cursoNome', ['cursoId', 'cursoNome', 'cursoPeriodicidade']) ? $params['order_by'] : 'cursoNome';
        $sortDir = in_array(strtoupper($params['sort_dir'] ?? 'ASC'), ['ASC', 'DESC']) ? $params['sort_dir'] : 'ASC';

        $termParam = "%{$term}%";

        try {
            $countSql = "SELECT COUNT(*) FROM cursos WHERE cursoNome LIKE ?";
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute([$termParam]);
            $totalRecords = (int)$countStmt->fetchColumn();

            $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
            $currentPage = max(1, min($currentPage, $totalPages));
            $offset = ($currentPage - 1) * $itemsPerPage;

            $sql = "
                SELECT cursoId, cursoNome, cursoDescricao, cursoPeriodicidade
                FROM cursos
                WHERE cursoNome LIKE ?
                ORDER BY {$sortCol} {$sortDir}
                LIMIT ? OFFSET ?
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $termParam);
            $stmt->bindParam(2, $itemsPerPage, PDO::PARAM_INT);
            $stmt->bindParam(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $data,
                'total' => $totalRecords,
                'totalPages' => $totalPages,
                'currentPage' => $currentPage
            ];

        } catch (\PDOException $e) {
            error_log("Erro em CursoRepository::findAllPaginated: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'totalPages' => 1, 'currentPage' => 1];
        }
    }

    /**
     * Salva (cria ou atualiza) um curso incluindo a periodicidade padrão em meses.
     */
    public function save(array $data): int
    {
        $tableName = 'cursos';

        $id = (int)($data['cursoId'] ?? 0);
        $nome = trim($data['cursoNome'] ?? '');
        $descricao = trim($data['cursoDescricao'] ?? '');
        $descricao = $descricao === '' ? null : $descricao;
        
        // Nova coluna: Periodicidade em meses (Ex: 12 para anual, 24 para bienal, null se não houver obrigação preventiva)
        $periodicidade = (empty($data['cursoPeriodicidade']) || (int)$data['cursoPeriodicidade'] <= 0) 
            ? null 
            : (int)$data['cursoPeriodicidade'];

        $isUpdating = $id > 0;

        $permissionNeeded = $isUpdating ? 'cadastros:manage' : 'cadastros:manage';
        $this->authService->checkAndFail($permissionNeeded);

        if (empty($nome)) {
            throw new Exception("O nome do curso é obrigatório.");
        }

        $params = [
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':periodicidade' => $periodicidade
        ];

        try {
            if ($isUpdating) {
                $sql = "UPDATE {$tableName} SET cursoNome = :nome, cursoDescricao = :descricao, cursoPeriodicidade = :periodicidade WHERE cursoId = :id";
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
            } else {
                $sql = "INSERT INTO {$tableName} (cursoNome, cursoDescricao, cursoPeriodicidade) VALUES (:nome, :descricao, :periodicidade)";
                $this->pdo->prepare($sql)->execute($params);
                $savedId = (int)$this->pdo->lastInsertId();
                
                $this->auditService->log('CREATE', $tableName, $savedId, $data);
            }
            
            return $savedId;

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                throw new Exception("O curso '$nome' já está cadastrado.");
            }
            throw $e;
        }
    }

    /**
     * Exclui um curso se não houver vínculos ativos com cargos.
     */
    public function delete(int $id): bool
    {
        $tableName = 'cursos';
        $this->authService->checkAndFail('cadastros:manage');

        try {
            // Verifica se o curso está associado a algum cargo antes de remover
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM cursos_cargo WHERE cursoId = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Este curso não pode ser excluído pois está associado a um ou mais cargos.");
            }

            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE cursoId = ?");
            $stmt->execute([$id]);
            $success = $stmt->rowCount() > 0;
            
            if ($success) {
                $this->auditService->log('DELETE', $tableName, $id, ['deletedId' => $id]);
            }
            
            return $success;

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                throw new Exception("Este curso não pode ser excluído pois está em uso no sistema.");
            }
            throw $e;
        }
    }
}