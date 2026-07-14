<?php
namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;
use App\Service\AuthService;
use PDO;
use Exception;

class FaixaSalarialRepository
{
    private PDO $pdo;
    private AuthService $authService;
    private AuditService $auditService;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->authService = new AuthService();
        $this->auditService = new AuditService();
    }

    public function find(int $id)
    {
        $this->authService->checkAndFail('cadastros:manage');
        $stmt = $this->pdo->prepare('SELECT * FROM faixas_salariais WHERE "faixaId" = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save(array $data): int
    {
        $tableName = 'faixas_salariais';
        $id = (int)($data['faixaId'] ?? 0);
        $isUpdating = $id > 0;
        $this->authService->checkAndFail('cadastros:manage');

        $nivel = trim($data['faixaNivel'] ?? '');
        if (empty($nivel)) {
            throw new Exception("O nível da faixa salarial é obrigatório.");
        }

        $salMin = !empty($data['faixaSalarioMinimo']) ? (float)$data['faixaSalarioMinimo'] : null;
        $salMax = !empty($data['faixaSalarioMaximo']) ? (float)$data['faixaSalarioMaximo'] : null;

        $params = [
            ':nivel'  => $nivel,
            ':salMin' => $salMin,
            ':salMax' => $salMax,
        ];

        try {
            if ($isUpdating) {
                $sql = 'UPDATE faixas_salariais SET "faixaNivel" = :nivel, "faixaSalarioMinimo" = :salMin, "faixaSalarioMaximo" = :salMax WHERE "faixaId" = :id';
                $params[':id'] = $id;
                $this->pdo->prepare($sql)->execute($params);
                $savedId = $id;
                $this->auditService->log('UPDATE', $tableName, $savedId, $data);
            } else {
                $sql = 'INSERT INTO faixas_salariais ("faixaNivel", "faixaSalarioMinimo", "faixaSalarioMaximo") VALUES (:nivel, :salMin, :salMax)';
                $this->pdo->prepare($sql)->execute($params);
                $savedId = (int)$this->pdo->lastInsertId();
                $this->auditService->log('CREATE', $tableName, $savedId, $data);
            }
            return $savedId;
        } catch (Exception $e) {
            if ($e->getCode() == '23505') {
                throw new Exception("A faixa salarial '{$nivel}' já existe.");
            }
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $tableName = 'faixas_salariais';
        $this->authService->checkAndFail('cadastros:manage');

        try {
            $stmtCheck = $this->pdo->prepare('SELECT COUNT(*) FROM cargos WHERE "faixaId" = ?');
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Esta faixa salarial não pode ser excluída pois está associada a um ou mais cargos.");
            }

            $stmt = $this->pdo->prepare('DELETE FROM faixas_salariais WHERE "faixaId" = ?');
            $stmt->execute([$id]);
            $success = $stmt->rowCount() > 0;
            if ($success) {
                $this->auditService->log('DELETE', $tableName, $id, ['deletedId' => $id]);
            }
            return $success;
        } catch (Exception $e) {
            if ($e->getCode() == '23503') {
                throw new Exception("Esta faixa salarial não pode ser excluída pois está associada a um ou mais cargos.");
            }
            throw $e;
        }
    }

    public function findAllPaginated(array $params = []): array
    {
        $itemsPerPage = (int)($params['limit'] ?? 15);
        $currentPage  = max(1, (int)($params['page'] ?? 1));
        $term         = $params['term'] ?? '';
        $sqlTerm      = "%{$term}%";

        $where    = '';
        $bindings = [];

        if (!empty($term)) {
            $where = ' WHERE unaccent(COALESCE("faixaNivel"::text, \'\')) ILIKE unaccent(:term)';
            $bindings[':term'] = $sqlTerm;
        }

        $totalRecords = (int)$this->pdo->prepare("SELECT COUNT(*) FROM faixas_salariais" . $where)->execute($bindings)
            ?: 0;
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM faixas_salariais" . $where);
        $countStmt->execute($bindings);
        $totalRecords = (int)$countStmt->fetchColumn();
        $totalPages   = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) $currentPage = $totalPages;
        $offset = ($currentPage - 1) * $itemsPerPage;

        $validColumns = ['faixaId', 'faixaNivel', 'faixaSalarioMinimo', 'faixaSalarioMaximo', 'faixaDataAtualizacao'];
        $orderBy  = in_array($params['order_by'] ?? '', $validColumns) ? $params['order_by'] : 'faixaId';
        $sortDir  = in_array(strtoupper($params['sort_dir'] ?? ''), ['ASC', 'DESC']) ? strtoupper($params['sort_dir']) : 'ASC';

        $sql = "SELECT * FROM faixas_salariais" . $where . ' ORDER BY "' . $orderBy . '" ' . $sortDir . " LIMIT :limit OFFSET :offset";
        $bindings[':limit']  = $itemsPerPage;
        $bindings[':offset'] = $offset;

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($bindings as $key => &$val) {
                $type = ($key === ':limit' || $key === ':offset') ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindParam($key, $val, $type);
            }
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar faixas salariais: " . $e->getMessage());
            $registros = [];
        }

        return [
            'data'        => $registros,
            'total'       => $totalRecords,
            'totalPages'  => $totalPages,
            'currentPage' => $currentPage,
        ];
    }
}
