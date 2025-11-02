<?php
// Arquivo: src/Repository/CargoRepository.php

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;
use App\Service\AuthService;
use PDO;
use Exception; // Importa a classe Exception global

/**
 * Lida com todas as operações de banco de dados para a entidade Cargo.
 */
class CargoRepository
{
    private PDO $pdo;
    private AuditService $auditService;
    private AuthService $authService;

    public function __construct()
    {
        // Pega a conexão PDO da nossa classe de Database
        $this->pdo = Database::getConnection();
        
        // INICIALIZAR OS SERVIÇOS
        $this->auditService = new AuditService();
        $this->authService = new AuthService();
    }

    /**
     * Busca os IDs e Nomes de todos os cargos para o relatório consolidado.
     * (Usado por relatorios/cargo_total.php)
     *
     * @return array
     */
    public function findAllIdsAndNames(): array
    {
        try {
            // Busca apenas IDs e Nomes para a iteração (mais leve)
            $stmt = $this->pdo->query("SELECT cargoId, cargoNome FROM cargos ORDER BY cargoNome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar todos os IDs de cargos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva (cria ou atualiza) um cargo e todas as suas relações N:M.
     * (Usado por cargos_form.php)
     *
     * @param array $postData Os dados vindo diretamente do $_POST.
     * @return int O ID do cargo salvo.
     * @throws Exception Se a validação falhar ou o salvamento falhar.
     */
    public function save(array $postData): int
    {
        $cargoIdSubmissao = (int)($postData['cargoId'] ?? 0);
        $isUpdating = $cargoIdSubmissao > 0;

        // --- VERIFICAÇÃO DE PERMISSÃO (AuthService) ---
        $permissionNeeded = $isUpdating ? 'cargos:edit' : 'cargos:create';
        // Lança uma exceção se o usuário não tiver permissão
        $this->authService->checkAndFail($permissionNeeded); 

        // 1. Captura dos Dados Principais
        $data = [
            'cargoNome' => trim($postData['cargoNome'] ?? ''),
            'cargoDescricao' => trim($postData['cargoDescricao'] ?? null),
            'cboId' => trim($postData['cboId'] ?? 0),
            'cargoResumo' => trim($postData['cargoResumo'] ?? null),
            'escolaridadeId' => (int)($postData['escolaridadeId'] ?? 0),
            'cargoExperiencia' => trim($postData['cargoExperiencia'] ?? null),
            'cargoCondicoes' => trim($postData['cargoCondicoes'] ?? null),
            'cargoComplexidade' => trim($postData['cargoComplexidade'] ?? null),
            'cargoResponsabilidades' => trim($postData['cargoResponsabilidades'] ?? null),
            'faixaId' => empty($postData['faixaId']) ? null : (int)$postData['faixaId'],
            'nivelHierarquicoId' => empty($postData['nivelHierarquicoId']) ? null : (int)$postData['nivelHierarquicoId'],
            'cargoSupervisorId' => empty($postData['cargoSupervisorId']) ? null : (int)$postData['cargoSupervisorId'],
        ];

        // 2. Validação
        if (empty($data['cargoNome']) || empty($data['cboId']) || $data['escolaridadeId'] <= 0) {
            throw new Exception("Os campos Nome do Cargo, CBO e Escolaridade são obrigatórios.");
        }

        // 3. Captura dos Dados de Relacionamento
        $relacionamentosSimples = [
            'cargos_area' => ['coluna' => 'areaId', 'valores' => (array)($postData['areaId'] ?? [])],
            'habilidades_cargo' => ['coluna' => 'habilidadeId', 'valores' => (array)($postData['habilidadeId'] ?? [])],
            'caracteristicas_cargo' => ['coluna' => 'caracteristicaId', 'valores' => (array)($postData['caracteristicaId'] ?? [])],
            'recursos_grupos_cargo' => ['coluna' => 'recursoGrupoId', 'valores' => (array)($postData['recursoGrupoId'] ?? [])],
        ];

        $riscosInput = [
            'riscoId' => (array)($postData['riscoId'] ?? []),
            'riscoDescricao' => (array)($postData['riscoDescricao'] ?? []),
        ];

        $cursosInput = [
            'cursoId' => (array)($postData['cursoId'] ?? []),
            'cursoCargoObrigatorio' => (array)($postData['cursoCargoObrigatorio'] ?? []),
            'cursoCargoObs' => (array)($postData['cursoCargoObs'] ?? []),
        ];

        $sinonimosInput = (array)($postData['sinonimoNome'] ?? []);

        // 4. Inicia a Transação
        $this->pdo->beginTransaction();
        try {
            // 5. Salva o Cargo Principal (UPDATE ou CREATE)
            $fields = array_keys($data);
            $bindings = array_values($data);

            if ($isUpdating) {
                $sql_fields = implode(' = ?, ', $fields) . ' = ?';
                $sql = "UPDATE cargos SET {$sql_fields}, cargoDataAtualizacao = CURRENT_TIMESTAMP() WHERE cargoId = ?";
                $bindings[] = $cargoIdSubmissao;
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($bindings);
                $novoCargoId = $cargoIdSubmissao;

                // --- LOG DE AUDITORIA (UPDATE) ---
                $this->auditService->log('UPDATE', 'cargos', $novoCargoId, $postData);

            } else {
                $sql_fields = implode(', ', $fields);
                $placeholders = implode(', ', array_fill(0, count($fields), '?'));
                $sql = "INSERT INTO cargos ({$sql_fields}) VALUES ({$placeholders})";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($bindings);
                $novoCargoId = $this->pdo->lastInsertId();
                
                // --- LOG DE AUDITORIA (CREATE) ---
                $this->auditService->log('CREATE', 'cargos', $novoCargoId, $postData);
            }

            // 6. Salva Relacionamentos N:M Simples
            foreach ($relacionamentosSimples as $tableName => $rel) {
                $column = $rel['coluna'];
                $valores = $rel['valores'];
                $this->pdo->prepare("DELETE FROM {$tableName} WHERE cargoId = ?")->execute([$novoCargoId]);
                if (!empty($valores)) {
                    $insert_sql = "INSERT INTO {$tableName} (cargoId, {$column}) VALUES (?, ?)";
                    $stmt_rel = $this->pdo->prepare($insert_sql);
                    foreach ($valores as $valorId) {
                        $stmt_rel->execute([$novoCargoId, $valorId]);
                    }
                }
            }

            // 7. Salva Riscos (Complexo)
            $this->pdo->prepare("DELETE FROM riscos_cargo WHERE cargoId = ?")->execute([$novoCargoId]);
            if (!empty($riscosInput['riscoId'])) {
                $sql_risco = "INSERT INTO riscos_cargo (cargoId, riscoId, riscoDescricao) VALUES (?, ?, ?)";
                $stmt_risco = $this->pdo->prepare($sql_risco);
                for ($i = 0; $i < count($riscosInput['riscoId']); $i++) {
                    $stmt_risco->execute([$novoCargoId, (int)$riscosInput['riscoId'][$i], $riscosInput['riscoDescricao'][$i] ?? '']);
                }
            }

            // 8. Salva Cursos (Complexo)
            $this->pdo->prepare("DELETE FROM cursos_cargo WHERE cargoId = ?")->execute([$novoCargoId]);
            if (!empty($cursosInput['cursoId'])) {
                $sql_curso = "INSERT INTO cursos_cargo (cargoId, cursoId, cursoCargoObrigatorio, cursoCargoObs) VALUES (?, ?, ?, ?)";
                $stmt_curso = $this->pdo->prepare($sql_curso);
                for ($i = 0; $i < count($cursosInput['cursoId']); $i++) {
                    $obrigatorio = (int)($cursosInput['cursoCargoObrigatorio'][$i] ?? 0);
                    $obs = $cursosInput['cursoCargoObs'][$i] ?? '';
                    $stmt_curso->execute([$novoCargoId, (int)$cursosInput['cursoId'][$i], $obrigatorio, $obs]);
                }
            }

            // 9. Salva Sinônimos
            $this->pdo->prepare("DELETE FROM cargo_sinonimos WHERE cargoId = ?")->execute([$novoCargoId]);
            if (!empty($sinonimosInput)) {
                $sql_sin = "INSERT INTO cargo_sinonimos (cargoId, cargoSinonimoNome) VALUES (?, ?)";
                $stmt_sin = $this->pdo->prepare($sql_sin);
                foreach ($sinonimosInput as $sinonimoNome) {
                    $stmt_sin->execute([$novoCargoId, trim($sinonimoNome)]);
                }
            }

            // 10. Commita a Transação
            $this->pdo->commit();
            return $novoCargoId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Propaga a exceção para a View (Controller) tratar
            throw new Exception("Erro fatal ao salvar no banco: " . $e->getMessage());
        }
    }

    /**
     * Busca todos os dados de um cargo e suas relações para popular o formulário.
     * (Usado por cargos_form.php)
     *
     * @param int $cargoId O ID do cargo a ser buscado.
     * @return array|null Um array com ['cargo' => [], 'sinonimos' => [], ...] ou null se não encontrado.
     */
    public function findFormData(int $cargoId): ?array
    {
        $data = [
            'cargo' => null,
            'sinonimos' => [],
            'riscos' => [],
            'areas' => [],
            'habilidades' => [],
            'caracteristicas' => [],
            'cursos' => [],
            'recursos_grupos' => []
        ];

        try {
            // 1. Busca Cargo Principal
            $stmt = $this->pdo->prepare("SELECT * FROM cargos WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargo = $stmt->fetch(PDO::FETCH_ASSOC); 

            if (!$cargo) {
                return null; // Cargo não existe
            }
            $data['cargo'] = $cargo;

            // 2. SINÔNIMOS
            $stmt = $this->pdo->prepare("SELECT cargoSinonimoId AS id, cargoSinonimoNome AS nome FROM cargo_sinonimos WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['sinonimos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. RISCOS (COMPLEX N:M)
            $stmt = $this->pdo->prepare("SELECT rc.riscoId AS id, r.riscoNome AS nome, rc.riscoDescricao AS descricao FROM riscos_cargo rc JOIN riscos r ON r.riscoId = rc.riscoId WHERE rc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['riscos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. ÁREAS DE ATUAÇÃO (SIMPLE N:M)
            $stmt = $this->pdo->prepare("SELECT ca.areaId AS id, a.areaNome AS nome FROM cargos_area ca JOIN areas_atuacao a ON a.areaId = ca.areaId WHERE ca.cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['areas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 5. HABILIDADES (SIMPLE N:M)
            $stmt = $this->pdo->prepare("SELECT hc.habilidadeId AS id, h.habilidadeNome AS nome, h.habilidadeTipo AS tipo FROM habilidades_cargo hc JOIN habilidades h ON h.habilidadeId = hc.habilidadeId WHERE hc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['habilidades'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. CARACTERÍSTICAS (SIMPLE N:M)
            $stmt = $this->pdo->prepare("SELECT cc.caracteristicaId AS id, c.caracteristicaNome AS nome FROM caracteristicas_cargo cc JOIN caracteristicas c ON c.caracteristicaId = cc.caracteristicaId WHERE cc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['caracteristicas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 7. CURSOS (COMPLEX N:M)
            $stmt = $this->pdo->prepare("SELECT curc.cursoId AS id, cur.cursoNome AS nome, curc.cursoCargoObrigatorio AS obrigatorio, curc.cursoCargoObs AS obs FROM cursos_cargo curc JOIN cursos cur ON cur.cursoId = curc.cursoId WHERE curc.cargoId = ?");
            $stmt->execute([$cargoId]);
            
            $data['cursos'] = array_map(function ($curso) {
                $curso['obrigatorio'] = (bool)$curso['obrigatorio'];
                return $curso;
            }, $stmt->fetchAll(PDO::FETCH_ASSOC));

            // 8. GRUPOS DE RECURSOS (SIMPLE N:M)
            $stmt = $this->pdo->prepare("SELECT rgc.recursoGrupoId AS id, rg.recursoGrupoNome AS nome FROM recursos_grupos_cargo rgc JOIN recursos_grupos rg ON rg.recursoGrupoId = rgc.recursoGrupoId WHERE rgc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['recursos_grupos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $data;

        } catch (\PDOException $e) {
            error_log("Erro ao carregar dados do formulário para o Cargo ID {$cargoId}: " . $e->getMessage());
            return null; // Retorna null em caso de erro de banco
        }
    }


    /**
     * Busca cargos de forma paginada, com filtro e ordenação.
     * (Usado por cargos.php)
     *
     * @param array $params Parâmetros de busca (term, page, limit, order_by, sort_dir)
     * @return array Contendo ['data', 'total', 'totalPages', 'currentPage']
     */
    public function findAllPaginated(array $params = []): array
    {
        // 1. Configuração da Paginação e Filtros
        $itemsPerPage = (int)($params['limit'] ?? 10);
        $currentPage = (int)($params['page'] ?? 1);
        $currentPage = max(1, $currentPage); 
        
        $term = $params['term'] ?? '';
        $sqlTerm = "%{$term}%";
        
        $all_bindings = [];

        // 2. Query para Contagem Total
        $count_sql = "
            SELECT COUNT(c.cargoId)
            FROM cargos c
            LEFT JOIN cbos b ON b.cboId = c.cboId
        ";
        $count_bindings = [];

        if (!empty($term)) {
            $count_sql .= " WHERE c.cargoNome LIKE :term1 OR c.cargoResumo LIKE :term2 OR b.cboTituloOficial LIKE :term3";
            $count_bindings[':term1'] = $sqlTerm;
            $count_bindings[':term2'] = $sqlTerm;
            $count_bindings[':term3'] = $sqlTerm;
        }

        try {
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($count_bindings);
            $totalRecords = (int)$count_stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar cargos: " . $e->getMessage());
            $totalRecords = 0;
        }

        // 3. Ajuste de Página
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

        // 4. Query Principal
        $sql = "
            SELECT 
                c.cargoId, c.cargoNome, c.cargoResumo, c.cargoDataAtualizacao,
                b.cboTituloOficial
            FROM cargos c
            LEFT JOIN cbos b ON b.cboId = c.cboId
        ";

        if (!empty($term)) {
            $sql .= " WHERE c.cargoNome LIKE :term1 OR c.cargoResumo LIKE :term2 OR b.cboTituloOficial LIKE :term3";
            $all_bindings[':term1'] = $sqlTerm;
            $all_bindings[':term2'] = $sqlTerm;
            $all_bindings[':term3'] = $sqlTerm;
        }

        // 5. Validação de Colunas de Ordenação
        $orderBy = $params['order_by'] ?? 'c.cargoId';
        $sortDir = $params['sort_dir'] ?? 'ASC';
        
        $validColumns = ['c.cargoId', 'c.cargoNome', 'b.cboTituloOficial', 'c.cargoDataAtualizacao'];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'c.cargoId';
        $sortDir = in_array(strtoupper($sortDir), ['ASC', 'DESC']) ? strtoupper($sortDir) : 'ASC';

        $sql .= " ORDER BY {$orderBy} {$sortDir}";
        $sql .= " LIMIT :limit OFFSET :offset";

        $all_bindings[':limit'] = $itemsPerPage;
        $all_bindings[':offset'] = $offset;

        // 6. Executa a query principal
        try {
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindParam(':limit', $all_bindings[':limit'], PDO::PARAM_INT);
            $stmt->bindParam(':offset', $all_bindings[':offset'], PDO::PARAM_INT);
            if (!empty($term)) {
                $stmt->bindParam(':term1', $all_bindings[':term1']);
                $stmt->bindParam(':term2', $all_bindings[':term2']);
                $stmt->bindParam(':term3', $all_bindings[':term3']);
            }
            
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        } catch (\PDOException $e) {
            error_log("Erro ao buscar cargos: " . $e->getMessage() . " SQL: " . $sql);
            $registros = [];
        }

        // 7. Retorna o pacote completo
        return [
            'data' => $registros,
            'total' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage
        ];
    }


    /**
     * Remove todas as referências de um Cargo em suas tabelas de junção N:M.
     * (Usado por delete() e save())
     *
     * @param int $cargoId O ID do cargo.
     * @return bool True se a limpeza for bem-sucedida.
     */
    public function clearRelationships(int $cargoId): bool
    {
        $joinTables = [
            'habilidades_cargo', 'caracteristicas_cargo', 'riscos_cargo',
            'cargo_sinonimos', 'cursos_cargo', 'recursos_grupos_cargo',
            'cargos_area'
        ];
        $success = true;

        foreach ($joinTables as $table) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM {$table} WHERE cargoId = ?");
                $stmt->execute([$cargoId]);
            } catch (\PDOException $e) {
                error_log("Falha ao limpar relacionamento N:M na tabela {$table} para Cargo ID {$cargoId}: " . $e->getMessage());
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Exclui um cargo e todas as suas relações.
     * (Usado por cargos.php)
     *
     * @param int $id O ID do cargo a ser excluído.
     * @return int O número de linhas afetadas (0 ou 1).
     * @throws \Exception Se a limpeza das relações falhar.
     */
    public function delete(int $id): int
    {
        // --- VERIFICAÇÃO DE PERMISSÃO (AuthService) ---
        $this->authService->checkAndFail('cargos:delete');
        
        $this->pdo->beginTransaction();
        try {
            // 1. Limpa todas as referências
            $cleaned = $this->clearRelationships($id);

            if (!$cleaned) {
                throw new \Exception("Falha ao limpar relacionamentos N:M para o Cargo ID {$id}.");
            }

            // 2. Exclui o cargo principal
            $stmt = $this->pdo->prepare("DELETE FROM cargos WHERE cargoId = ?");
            $stmt->execute([$id]);
            $rowCount = $stmt->rowCount();
            
            // --- LOG DE AUDITORIA (DELETE) ---
            if ($rowCount > 0) {
                $this->auditService->log('DELETE', 'cargos', $id, ['deletedCargoId' => $id]);
            }

            $this->pdo->commit();
            return $rowCount;

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            // Propaga a exceção para que o controller possa tratá-la
            throw $e;
        }
    }

    /**
     * Carrega todos os dados de um cargo para relatórios.
     * (Usado por gerador_pdf.php e cargo_individual.php)
     *
     * @param int $cargoId O ID do cargo.
     * @return array|null Os dados completos do cargo ou null se não encontrado.
     */
    public function findReportData(int $cargoId): ?array
    {
        if ($cargoId <= 0) return null;

        $data = [];

        try {
            // 1. DADOS BÁSICOS
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.*, e.escolaridadeTitulo, b.cboCod, b.cboTituloOficial,
                    f.faixaNivel, f.faixaSalarioMinimo, f.faixaSalarioMaximo,
                    n.nivelOrdem, t.tipoNome AS tipoHierarquiaNome,
                    sup.cargoNome AS cargoSupervisorNome
                FROM cargos c
                JOIN escolaridades e ON e.escolaridadeId = c.escolaridadeId  
                JOIN cbos b ON b.cboId = c.cboId                          
                LEFT JOIN faixas_salariais f ON f.faixaId = c.faixaId
                LEFT JOIN nivel_hierarquico n ON n.nivelId = c.nivelHierarquicoId 
                LEFT JOIN tipo_hierarquia t ON t.tipoId = n.tipoId              
                LEFT JOIN cargos sup ON sup.cargoId = c.cargoSupervisorId        
                WHERE c.cargoId = ?
            ");
            $stmt->execute([$cargoId]);
            $cargo = $stmt->fetch(PDO::FETCH_ASSOC); 

            if (!$cargo) return null;
            $data['cargo'] = $cargo;
            
            // 2. BUSCA DE RELACIONAMENTOS N:M
            
            // 2.1. HABILIDADES
            $stmt_hab = $this->pdo->prepare("SELECT h.habilidadeNome, h.habilidadeTipo, h.habilidadeDescricao FROM habilidades_cargo hc JOIN habilidades h ON h.habilidadeId = hc.habilidadeId WHERE hc.cargoId = ? ORDER BY h.habilidadeTipo DESC, h.habilidadeNome ASC");
            $stmt_hab->execute([$cargoId]);
            $data['habilidades'] = $stmt_hab->fetchAll(PDO::FETCH_ASSOC); 

            // 2.2. CARACTERÍSTICAS
            $stmt_car = $this->pdo->prepare("SELECT c.caracteristicaNome, c.caracteristicaDescricao FROM caracteristicas_cargo cc JOIN caracteristicas c ON c.caracteristicaId = cc.caracteristicaId WHERE cc.cargoId = ? ORDER BY c.caracteristicaNome ASC");
            $stmt_car->execute([$cargoId]);
            $data['caracteristicas'] = $stmt_car->fetchAll(PDO::FETCH_ASSOC); 
            
            // 2.3. RISCOS
            $stmt_ris = $this->pdo->prepare("SELECT r.riscoNome, rc.riscoDescricao FROM riscos_cargo rc JOIN riscos r ON r.riscoId = rc.riscoId WHERE rc.cargoId = ? ORDER BY r.riscoNome ASC");
            $stmt_ris->execute([$cargoId]);
            $data['riscos'] = $stmt_ris->fetchAll(PDO::FETCH_ASSOC); 
            
            // 2.4. CURSOS
            $stmt_cur = $this->pdo->prepare("SELECT cur.cursoNome, c_c.cursoCargoObrigatorio, c_c.cursoCargoObs FROM cursos_cargo c_c JOIN cursos cur ON cur.cursoId = c_c.cursoId WHERE c_c.cargoId = ? ORDER BY c_c.cursoCargoObrigatorio DESC, cur.cursoNome ASC");
            $stmt_cur->execute([$cargoId]);
            $data['cursos'] = $stmt_cur->fetchAll(PDO::FETCH_ASSOC); 

            // 2.5. SINÔNIMOS
            $stmt_sin = $this->pdo->prepare("SELECT cargoSinonimoNome FROM cargo_sinonimos WHERE cargoId = ?"); 
            $stmt_sin->execute([$cargoId]);
            $data['sinonimos'] = $stmt_sin->fetchAll(PDO::FETCH_COLUMN);
            
            // 2.6. GRUPOS DE RECURSOS
            $stmt_rec = $this->pdo->prepare("SELECT rg.recursoGrupoNome FROM recursos_grupos_cargo rcg JOIN recursos_grupos rg ON rg.recursoGrupoId = rcg.recursoGrupoId WHERE rcg.cargoId = ? ORDER BY rg.recursoGrupoNome ASC");
            $stmt_rec->execute([$cargoId]);
            $data['recursos_grupos'] = $stmt_rec->fetchAll(PDO::FETCH_COLUMN);
            
            // 2.7. ÁREAS DE ATUAÇÃO
            $stmt_areas = $this->pdo->prepare("SELECT a.areaNome FROM cargos_area ca JOIN areas_atuacao a ON a.areaId = ca.areaId WHERE ca.cargoId = ? ORDER BY a.areaNome ASC");
            $stmt_areas->execute([$cargoId]);
            $data['areas_atuacao'] = $stmt_areas->fetchAll(PDO::FETCH_COLUMN);

            return $data;

        } catch (\Exception $e) {
            error_log("Erro FATAL (falha de QUERY/DB) ao carregar Cargo ID {$cargoId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca os IDs do cargo anterior e próximo, com base no filtro e ordenação.
     * (Usado por cargo_individual.php para navegação)
     *
     * @param int $currentId O ID do cargo atual
     * @param string $orderBy A coluna de ordenação
     * @param string $sortDir A direção (ASC/DESC)
     * @param string $term O termo de busca (opcional)
     * @return array ['prev_id' => int|null, 'next_id' => int|null]
     */
    public function findAdjacentCargoIds(int $currentId, string $orderBy, string $sortDir, string $term = ''): array
    {
        // 1. Validação de Colunas de Ordenação
        $validColumns = ['c.cargoId', 'c.cargoNome', 'b.cboTituloOficial', 'c.cargoDataAtualizacao'];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'c.cargoId';
        $sortDir = in_array(strtoupper($sortDir), ['ASC', 'DESC']) ? strtoupper($sortDir) : 'ASC';

        // 2. Parâmetros de Filtro
        $sqlTerm = "%{$term}%";
        $bindings = [];
        
        $whereClause = "";
        if (!empty($term)) {
            $whereClause = " WHERE c.cargoNome LIKE :term1 OR c.cargoResumo LIKE :term2 OR b.cboTituloOficial LIKE :term3";
            $bindings[':term1'] = $sqlTerm;
            $bindings[':term2'] = $sqlTerm;
            $bindings[':term3'] = $sqlTerm;
        }

        // 3. Query com Funções de Janela (LEAD/LAG)
        $sql = "
            WITH OrderedCargos AS (
                SELECT 
                    c.cargoId,
                    LAG(c.cargoId) OVER (ORDER BY {$orderBy} {$sortDir}) AS prev_id,
                    LEAD(c.cargoId) OVER (ORDER BY {$orderBy} {$sortDir}) AS next_id
                FROM cargos c
                LEFT JOIN cbos b ON b.cboId = c.cboId
                {$whereClause}
            )
            SELECT prev_id, next_id
            FROM OrderedCargos
            WHERE cargoId = :currentId
        ";

        $bindings[':currentId'] = $currentId;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: ['prev_id' => null, 'next_id' => null];

        } catch (\PDOException $e) {
            error_log("Erro ao buscar IDs adjacentes: " . $e->getMessage());
            return ['prev_id' => null, 'next_id' => null];
        }
    }

    /**
     * NOVO MÉTODO (Adicionado para navegação "Primeiro/Último")
     * Busca os IDs do primeiro e último cargo, com base no filtro e ordenação.
     *
     * @param string $orderBy A coluna de ordenação
     * @param string $sortDir A direção (ASC/DESC)
     * @param string $term O termo de busca (opcional)
     * @return array ['first_id' => int|null, 'last_id' => int|null]
     */
    public function findFirstAndLastCargoIds(string $orderBy, string $sortDir, string $term = ''): array
    {
        // 1. Validação de Colunas de Ordenação
        $validColumns = ['c.cargoId', 'c.cargoNome', 'b.cboTituloOficial', 'c.cargoDataAtualizacao'];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'c.cargoId';
        $sortDir = in_array(strtoupper($sortDir), ['ASC', 'DESC']) ? strtoupper($sortDir) : 'ASC';

        // 2. Parâmetros de Filtro
        $sqlTerm = "%{$term}%";
        $bindings = [];
        
        $whereClause = "";
        if (!empty($term)) {
            $whereClause = " WHERE c.cargoNome LIKE :term1 OR c.cargoResumo LIKE :term2 OR b.cboTituloOficial LIKE :term3";
            $bindings[':term1'] = $sqlTerm;
            $bindings[':term2'] = $sqlTerm;
            $bindings[':term3'] = $sqlTerm;
        }

        // 3. Query com Funções de Janela (FIRST_VALUE/LAST_VALUE)
        // Usamos ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING para obter o valor real da janela inteira
        $sql = "
            WITH OrderedCargos AS (
                SELECT 
                    c.cargoId,
                    FIRST_VALUE(c.cargoId) OVER (ORDER BY {$orderBy} {$sortDir} ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) AS first_id,
                    LAST_VALUE(c.cargoId) OVER (ORDER BY {$orderBy} {$sortDir} ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) AS last_id
                FROM cargos c
                LEFT JOIN cbos b ON b.cboId = c.cboId
                {$whereClause}
            )
            SELECT first_id, last_id
            FROM OrderedCargos
            LIMIT 1
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: ['first_id' => null, 'last_id' => null];

        } catch (\PDOException $e) {
            error_log("Erro ao buscar first/last IDs: " . $e->getMessage());
            return ['first_id' => null, 'last_id' => null];
        }
    }
}