<?php
// Arquivo: src/Repository/CursoRepository.php

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;
use App\Service\AuthService;
use PDO;
use Exception;
use PDOException; 

class CursoRepository
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

    /**
     * Busca um curso pelo ID.
     */
    public function find(int $id)
    {
        // ==================================================================
        // CORREÇÃO APLICADA AQUI:
        // ==================================================================
        $this->authService->checkAndFail('cursos:manage', '../index.php?error=Acesso+negado');
        
        $stmt = $this->pdo->prepare("SELECT * FROM cursos WHERE cursoId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca todos os cursos de forma paginada.
     */
    public function findAllPaginated(array $params = []): array
    {
        // A listagem (leitura) não deve exigir permissão de 'manage'
        
        $itemsPerPage = (int)($params['limit'] ?? 10);
        $currentPage = (int)($params['page'] ?? 1);
        $term = $params['term'] ?? '';
        
        $valid_cols = ['cursoId', 'cursoNome'];
        $sort_col = in_array($params['sort_col'] ?? 'cursoNome', $valid_cols) ? $params['sort_col'] : 'cursoNome';
        $sort_dir = in_array(strtoupper($params['sort_dir'] ?? 'ASC'), ['ASC', 'DESC']) ? strtoupper($params['sort_dir']) : 'ASC';

        $offset = ($currentPage - 1) * $itemsPerPage;
        
        $where = '';
        $bindings = [];
        if (!empty($term)) {
            $where = " WHERE cursoNome LIKE ?";
            $bindings[] = "%{$term}%";
        }

        $count_sql = "SELECT COUNT(*) FROM cursos" . $where;
        $count_stmt = $this->pdo->prepare($count_sql);
        $count_stmt->execute($bindings);
        $totalRecords = (int)$count_stmt->fetchColumn();

        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
            $offset = ($currentPage - 1) * $itemsPerPage;
        }

        $sql = "SELECT * FROM cursos" . $where . " ORDER BY $sort_col $sort_dir LIMIT $itemsPerPage OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $registros,
            'total' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage
        ];
    }

    /**
     * Salva (cria ou atualiza) um curso.
     */
    public function save(array $data)
    {
        // ==================================================================
        // CORREÇÃO APLICADA AQUI:
        // ==================================================================
        $this->authService->checkAndFail('cursos:manage');

        $id = (int)($data['cursoId'] ?? $data['id'] ?? 0); 
        $nome = trim($data['cursoNome'] ?? $data['nome'] ?? '');
        
        if (empty($nome)) {
            throw new Exception("O nome do curso é obrigatório.");
        }

        try {
            if ($id > 0) {
                // Update
                $sql = "UPDATE cursos SET cursoNome = ? WHERE cursoId = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nome, $id]);
                
                $this->auditService->log('UPDATE', 'cursos', $id, $data);
            } else {
                // Create
                $sql = "INSERT INTO cursos (cursoNome) VALUES (?)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nome]);
                $id = (int)$this->pdo->lastInsertId();
                
                $this->auditService->log('CREATE', 'cursos', $id, $data);
            }
            return $id;
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                 throw new Exception("O curso '{$nome}' já está cadastrado.");
            }
            throw new Exception("Erro ao salvar o curso: " . $e->getMessage());
        }
    }

    /**
     * Exclui um curso.
     */
    public function delete(int $id): bool
    {
        // ==================================================================
        // CORREÇÃO APLICADA AQUI:
        // ==================================================================
        $this->authService->checkAndFail('cursos:manage');

        if ($id <= 0) {
            throw new Exception("ID inválido para exclusão.");
        }

        try {
            // Verifica se o curso está sendo usado
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM cursos_cargo WHERE cursoId = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Este curso não pode ser excluído pois está associado a um ou mais cargos.");
            }

            $stmt = $this->pdo->prepare("DELETE FROM cursos WHERE cursoId = ?");
            $stmt->execute([$id]);
            
            $success = $stmt->rowCount() > 0;
            if ($success) {
                $this->auditService->log('DELETE', 'cursos', $id, ['deletedId' => $id]);
            }
            return $success;

        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                 throw new Exception("Este curso não pode ser excluído pois está em uso.");
            }
            throw new Exception("Erro ao excluir o curso: " . $e->getMessage());
        }
    }
}