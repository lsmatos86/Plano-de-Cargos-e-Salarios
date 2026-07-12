<?php
// Arquivo: src/Repository/CargoRepository.php

namespace App\Repository;

use App\Core\Database;
use App\Service\AuditService;
use App\Service\AuthService;
use PDO;
use Exception;

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
        $this->pdo = Database::getConnection();
        $this->auditService = new AuditService();
        $this->authService = new AuthService();
    }

    public function findAllIdsAndNames(): array
    {
        try {
<<<<<<< HEAD
            // Busca apenas IDs e Nomes para a iteração (mais leve)
            $stmt = $this->pdo->query("SELECT \"cargoId\", \"cargoNome\" FROM cargos ORDER BY \"cargoNome\" ASC");
=======
            $stmt = $this->pdo->query("SELECT cargoId, cargoNome FROM cargos ORDER BY cargoNome ASC");
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar todos os IDs de cargos: " . $e->getMessage());
            return [];
        }
    }

<<<<<<< HEAD
    /**
     * Salva (cria ou atualiza) um cargo e todas as suas relações N:M.
     * (Criado no Passo 11 - Usado por cargos_form.php)
     *
     * @param array $postData Os dados vindo diretamente do $_POST.
     * @return array{cargoId: int, novasSoftskillsLideranca: string[]} O ID do cargo salvo e os nomes das softskills de liderança adicionadas automaticamente.
     * @throws Exception Se a validação falhar ou o salvamento falhar.
     */
    public function save(array $postData): array
=======
    public function save(array $postData): int
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
    {
        $cargoIdSubmissao = (int)($postData['cargoId'] ?? 0);
        $isUpdating = $cargoIdSubmissao > 0;

<<<<<<< HEAD
        // --- 6. VERIFICAÇÃO DE PERMISSÃO (AuthService) ---
        $permissionNeeded = 'cargos:manage';
        // Lança uma exceção se o usuário não tiver permissão
=======
        $permissionNeeded = $isUpdating ? 'cargos:edit' : 'cargos:create';
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
        $this->authService->checkAndFail($permissionNeeded); 

        // Captura da checagem de Revisão
        $isRevisado = isset($postData['is_revisado']) && $postData['is_revisado'] == '1' ? 1 : 0;

        // 1. Captura dos Dados Principais (COM AS NOVAS COLUNAS DO PISO SALARIAL)
        $data = [
            'cargoNome' => trim($postData['cargoNome'] ?? ''),
            'cargoDescricao' => trim($postData['cargoDescricao'] ?? ''),
            'cboId' => trim($postData['cboId'] ?? ''),
            'cargoResumo' => trim($postData['cargoResumo'] ?? ''),
            'escolaridadeId' => (int)($postData['escolaridadeId'] ?? 0),
            'cargoExperiencia' => trim($postData['cargoExperiencia'] ?? ''),
            'cargoCondicoes' => trim($postData['cargoCondicoes'] ?? ''),
            'cargoComplexidade' => trim($postData['cargoComplexidade'] ?? ''),
            'cargoResponsabilidades' => trim($postData['cargoResponsabilidades'] ?? ''),
            'faixaId' => empty($postData['faixaId']) ? null : (int)$postData['faixaId'],
            'nivelHierarquicoId' => empty($postData['nivelHierarquicoId']) ? null : (int)$postData['nivelHierarquicoId'],
            'is_revisado' => $isRevisado,
            'data_revisao' => $isRevisado ? date('Y-m-d H:i:s') : null,
            
            // --- BLOCO FINANCEIRO ADICIONADO AQUI ---
            'tem_piso_salarial' => isset($postData['tem_piso_salarial']) && $postData['tem_piso_salarial'] == '1' ? 1 : 0,
            'piso_valor' => !empty($postData['piso_valor']) ? floatval(str_replace(',', '.', $postData['piso_valor'])) : null,
            'piso_lei_numero' => !empty($postData['piso_lei_numero']) ? trim($postData['piso_lei_numero']) : null,
            'piso_data_base' => !empty($postData['piso_data_base']) ? trim($postData['piso_data_base']) : null,
            // ----------------------------------------
        ];

        // 2. Validação
        if (empty($data['cargoNome']) || empty($data['cboId']) || $data['escolaridadeId'] <= 0) {
            throw new Exception("Os campos Nome do Cargo, CBO e Escolaridade são obrigatórios.");
        }

<<<<<<< HEAD
        // 3. Captura dos Dados de Relacionamento
        $habilidadeIdsOriginais = array_values(array_unique(array_map('intval', (array)($postData['habilidadeId'] ?? []))));
        $baseSoftskills = $this->getBaseSoftskillsForNivel((int)($data['nivelHierarquicoId'] ?? 0));
        $novasSoftskillsIds = [];
        if (!empty($baseSoftskills)) {
            $novasSoftskillsIds = array_values(array_diff($baseSoftskills, $habilidadeIdsOriginais));
        }
        $habilidadeIds = !empty($baseSoftskills)
            ? array_values(array_unique(array_merge($habilidadeIdsOriginais, $baseSoftskills)))
            : $habilidadeIdsOriginais;

        $relacionamentosSimples = [
            'cargos_area' => ['coluna' => 'areaId', 'valores' => array_values(array_unique(array_map('intval', (array)($postData['areaId'] ?? []))))],
            'habilidades_cargo' => ['coluna' => 'habilidadeId', 'valores' => $habilidadeIds],
            'caracteristicas_cargo' => ['coluna' => 'caracteristicaId', 'valores' => array_values(array_unique(array_map('intval', (array)($postData['caracteristicaId'] ?? []))))],
            'recursos_grupos_cargo' => ['coluna' => 'recursoGrupoId', 'valores' => array_values(array_unique(array_map('intval', (array)($postData['recursoGrupoId'] ?? []))))],
=======
        // 3. Captura dos Dados de Relacionamento (INCLUI MÚLTIPLOS SUPERVISORES)
        $relacionamentosSimples = [
            'cargos_area' => ['coluna' => 'areaId', 'valores' => (array)($postData['areaId'] ?? [])],
            'habilidades_cargo' => ['coluna' => 'habilidadeId', 'valores' => (array)($postData['habilidadeId'] ?? [])],
            'caracteristicas_cargo' => ['coluna' => 'caracteristicaId', 'valores' => (array)($postData['caracteristicaId'] ?? [])],
            'recursos_grupos_cargo' => ['coluna' => 'recursoGrupoId', 'valores' => (array)($postData['recursoGrupoId'] ?? [])],
            'cargos_supervisores' => ['coluna' => 'supervisorId', 'valores' => (array)($postData['cargoSupervisorId'] ?? [])],
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
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
            // 5. Salva o Cargo Principal
            $fields = array_keys($data);
            $bindings = array_values($data);
            // Adiciona aspas em cada coluna para compatibilidade PostgreSQL
            $quotedFields = array_map(fn($f) => '"' . $f . '"', $fields);

            if ($isUpdating) {
                $sql_fields = implode(' = ?, ', $quotedFields) . ' = ?';
                $sql = "UPDATE cargos SET {$sql_fields}, \"cargoDataAtualizacao\" = CURRENT_TIMESTAMP WHERE \"cargoId\" = ?";
                $bindings[] = $cargoIdSubmissao;
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($bindings);
                $novoCargoId = $cargoIdSubmissao;

<<<<<<< HEAD
                // --- 7. LOG DE AUDITORIA (UPDATE) ---
                $motivo = trim($postData['motivoAlteracao'] ?? '');
                $dadosLog = $postData;
                if (!empty($motivo)) $dadosLog['_motivo'] = $motivo;
                $this->auditService->log('UPDATE', 'cargos', $novoCargoId, $dadosLog);

=======
                // SISTEMA DE LOG: Captura a justificativa enviada e anexa ao Log de Auditoria
                $auditPayload = $postData;
                $auditPayload['audit_motivo_justificativa'] = trim($postData['motivo_alteracao'] ?? 'Nenhuma justificativa textual fornecida.');
                $this->auditService->log('UPDATE', 'cargos', $novoCargoId, $auditPayload);
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            } else {
                $sql_fields = implode(', ', $quotedFields);
                $placeholders = implode(', ', array_fill(0, count($fields), '?'));
                $sql = "INSERT INTO cargos ({$sql_fields}) VALUES ({$placeholders})";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($bindings);
                $novoCargoId = (int)$this->pdo->lastInsertId('cargos_cargoId_seq');
                
<<<<<<< HEAD
                // --- 8. LOG DE AUDITORIA (CREATE) ---
                $motivo = trim($postData['motivoAlteracao'] ?? '');
                $dadosLog = $postData;
                if (!empty($motivo)) $dadosLog['_motivo'] = $motivo;
                $this->auditService->log('CREATE', 'cargos', $novoCargoId, $dadosLog);
=======
                $this->auditService->log('CREATE', 'cargos', $novoCargoId, $postData);
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            }

            // 6. Salva Relacionamentos N:M Simples (Agora salva múltiplos chefes na cargos_supervisores)
            foreach ($relacionamentosSimples as $tableName => $rel) {
                $column = $rel['coluna'];
                $valores = $rel['valores'];
                $this->pdo->prepare("DELETE FROM {$tableName} WHERE \"cargoId\" = ?")->execute([$novoCargoId]);
                if (!empty($valores)) {
                    $insert_sql = "INSERT INTO {$tableName} (\"cargoId\", \"{$column}\") VALUES (?, ?)";
                    $stmt_rel = $this->pdo->prepare($insert_sql);
                    foreach ($valores as $valorId) {
                        if ((int)$valorId > 0) {
                            $stmt_rel->execute([$novoCargoId, (int)$valorId]);
                        }
                    }
                }
            }

<<<<<<< HEAD
            // 7. Salva Riscos (Complexo)
            $this->pdo->prepare("DELETE FROM riscos_cargo WHERE \"cargoId\" = ?")->execute([$novoCargoId]);
=======
            // 7. Salva Riscos 
            $this->pdo->prepare("DELETE FROM riscos_cargo WHERE cargoId = ?")->execute([$novoCargoId]);
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            if (!empty($riscosInput['riscoId'])) {
                $sql_risco = "INSERT INTO riscos_cargo (\"cargoId\", \"riscoId\", \"riscoDescricao\") VALUES (?, ?, ?)";
                $stmt_risco = $this->pdo->prepare($sql_risco);
                for ($i = 0; $i < count($riscosInput['riscoId']); $i++) {
                    $stmt_risco->execute([$novoCargoId, (int)$riscosInput['riscoId'][$i], $riscosInput['riscoDescricao'][$i] ?? '']);
                }
            }

<<<<<<< HEAD
            // 8. Salva Cursos (Complexo)
            $this->pdo->prepare("DELETE FROM cursos_cargo WHERE \"cargoId\" = ?")->execute([$novoCargoId]);
=======
            // 8. Salva Cursos 
            $this->pdo->prepare("DELETE FROM cursos_cargo WHERE cargoId = ?")->execute([$novoCargoId]);
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            if (!empty($cursosInput['cursoId'])) {
                $sql_curso = "INSERT INTO cursos_cargo (\"cargoId\", \"cursoId\", \"cursoCargoObrigatorio\", \"cursoCargoObs\") VALUES (?, ?, ?, ?)";
                $stmt_curso = $this->pdo->prepare($sql_curso);
                for ($i = 0; $i < count($cursosInput['cursoId']); $i++) {
                    $obrigatorio = (int)($cursosInput['cursoCargoObrigatorio'][$i] ?? 0);
                    $obs = $cursosInput['cursoCargoObs'][$i] ?? '';
                    $stmt_curso->execute([$novoCargoId, (int)$cursosInput['cursoId'][$i], $obrigatorio, $obs]);
                }
            }

            // 9. Salva Sinônimos
            $this->pdo->prepare("DELETE FROM cargo_sinonimos WHERE \"cargoId\" = ?")->execute([$novoCargoId]);
            if (!empty($sinonimosInput)) {
                $sql_sin = "INSERT INTO cargo_sinonimos (\"cargoId\", \"cargoSinonimoNome\") VALUES (?, ?)";
                $stmt_sin = $this->pdo->prepare($sql_sin);
                foreach ($sinonimosInput as $sinonimoNome) {
                    $stmt_sin->execute([$novoCargoId, trim($sinonimoNome)]);
                }
            }

<<<<<<< HEAD
            // 10. Busca os nomes das softskills de liderança recém-adicionadas
            $novasSoftskillsNomes = [];
            if (!empty($novasSoftskillsIds)) {
                $placeholders = implode(', ', array_fill(0, count($novasSoftskillsIds), '?'));
                $stmt_names = $this->pdo->prepare(
                    "SELECT \"habilidadeNome\" FROM habilidades WHERE \"habilidadeId\" IN ({$placeholders}) ORDER BY \"habilidadeNome\" ASC"
                );
                $stmt_names->execute($novasSoftskillsIds);
                $novasSoftskillsNomes = $stmt_names->fetchAll(PDO::FETCH_COLUMN);
            }

            // 11. Commita a Transação
=======
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $this->pdo->commit();
            return [
                'cargoId' => $novoCargoId,
                'novasSoftskillsLideranca' => $novasSoftskillsNomes,
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('Falha fatal ao salvar Cargo: ' . $e->getMessage());
            throw new Exception("Erro fatal ao salvar o Cargo no banco. Tente novamente ou contate o suporte.");
        }
    }

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
            'recursos_grupos' => [],
            'supervisores' => []
        ];

        try {
<<<<<<< HEAD
            // 1. Busca Cargo Principal
            $stmt = $this->pdo->prepare("SELECT * FROM cargos WHERE \"cargoId\" = ?");
=======
            $stmt = $this->pdo->prepare("SELECT * FROM cargos WHERE cargoId = ?");
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $stmt->execute([$cargoId]);
            $cargo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cargo) return null;
            $data['cargo'] = $cargo;

<<<<<<< HEAD
            // 2. SINÔNIMOS
            // Corrigido: cargoSinonimoNome AS nome (para consistência com o JS)
            $stmt = $this->pdo->prepare("SELECT \"cargoSinonimoId\" AS id, \"cargoSinonimoNome\" AS nome FROM cargo_sinonimos WHERE \"cargoId\" = ?");
            $stmt->execute([$cargoId]);
            $data['sinonimos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. RISCOS (COMPLEX N:M)
            $stmt = $this->pdo->prepare("SELECT rc.\"riscoId\" AS id, r.\"riscoNome\" AS nome, rc.\"riscoDescricao\" AS descricao FROM riscos_cargo rc JOIN riscos r ON r.\"riscoId\" = rc.\"riscoId\" WHERE rc.\"cargoId\" = ?");
            $stmt->execute([$cargoId]);
            $data['riscos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. ÁREAS DE ATUAÇÃO (SIMPLE N:M)
            $stmt = $this->pdo->prepare("SELECT ca.\"areaId\" AS id, a.\"areaNome\" AS nome FROM cargos_area ca JOIN areas_atuacao a ON a.\"areaId\" = ca.\"areaId\" WHERE ca.\"cargoId\" = ?");
            $stmt->execute([$cargoId]);
            $data['areas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 5. HABILIDADES (SIMPLE N:M)
            $stmt = $this->pdo->prepare("SELECT hc.\"habilidadeId\" AS id, h.\"habilidadeNome\" AS nome, h.\"habilidadeTipo\" AS tipo FROM habilidades_cargo hc JOIN habilidades h ON h.\"habilidadeId\" = hc.\"habilidadeId\" WHERE hc.\"cargoId\" = ?");
            $stmt->execute([$cargoId]);
            $data['habilidades'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. CARACTERÍSTICAS (SIMPLE N:M)
            $stmt = $this->pdo->prepare("SELECT cc.\"caracteristicaId\" AS id, c.\"caracteristicaNome\" AS nome FROM caracteristicas_cargo cc JOIN caracteristicas c ON c.\"caracteristicaId\" = cc.\"caracteristicaId\" WHERE cc.\"cargoId\" = ?");
            $stmt->execute([$cargoId]);
            $data['caracteristicas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 7. CURSOS (COMPLEX N:M)
            $stmt = $this->pdo->prepare("SELECT curc.\"cursoId\" AS id, cur.\"cursoNome\" AS nome, curc.\"cursoCargoObrigatorio\" AS obrigatorio, curc.\"cursoCargoObs\" AS obs FROM cursos_cargo curc JOIN cursos cur ON cur.\"cursoId\" = curc.\"cursoId\" WHERE curc.\"cargoId\" = ?");
=======
            $stmt = $this->pdo->prepare("SELECT cargoSinonimoId AS id, cargoSinonimoNome AS nome FROM cargo_sinonimos WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['sinonimos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT rc.riscoId AS id, r.riscoNome AS nome, rc.riscoDescricao AS descricao FROM riscos_cargo rc JOIN riscos r ON r.riscoId = rc.riscoId WHERE rc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['riscos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT ca.areaId AS id, a.areaNome AS nome FROM cargos_area ca JOIN areas_atuacao a ON a.areaId = ca.areaId WHERE ca.cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['areas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT hc.habilidadeId AS id, h.habilidadeNome AS nome, h.habilidadeTipo AS tipo FROM habilidades_cargo hc JOIN habilidades h ON h.habilidadeId = hc.habilidadeId WHERE hc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['habilidades'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT cc.caracteristicaId AS id, c.caracteristicaNome AS nome FROM caracteristicas_cargo cc JOIN caracteristicas c ON c.caracteristicaId = cc.caracteristicaId WHERE cc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['caracteristicas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT curc.cursoId AS id, cur.cursoNome AS nome, curc.cursoCargoObrigatorio AS obrigatorio, curc.cursoCargoObs AS obs FROM cursos_cargo curc JOIN cursos cur ON cur.cursoId = curc.cursoId WHERE curc.cargoId = ?");
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $stmt->execute([$cargoId]);
            $data['cursos'] = array_map(function ($curso) {
                $curso['obrigatorio'] = (bool)$curso['obrigatorio'];
                return $curso;
            }, $stmt->fetchAll(PDO::FETCH_ASSOC));

<<<<<<< HEAD
            // 8. GRUPOS DE RECURSOS (SIMPLE N:M)
            $stmt = $this->pdo->prepare("SELECT rgc.\"recursoGrupoId\" AS id, rg.\"recursoGrupoNome\" AS nome FROM recursos_grupos_cargo rgc JOIN recursos_grupos rg ON rg.\"recursoGrupoId\" = rgc.\"recursoGrupoId\" WHERE rgc.\"cargoId\" = ?");
=======
            $stmt = $this->pdo->prepare("SELECT rgc.recursoGrupoId AS id, rg.recursoGrupoNome AS nome FROM recursos_grupos_cargo rgc JOIN recursos_grupos rg ON rg.recursoGrupoId = rgc.recursoGrupoId WHERE rgc.cargoId = ?");
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $stmt->execute([$cargoId]);
            $data['recursos_grupos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT cs.supervisorId AS id, c.cargoNome AS nome FROM cargos_supervisores cs JOIN cargos c ON c.cargoId = cs.supervisorId WHERE cs.cargoId = ?");
            $stmt->execute([$cargoId]);
            $data['supervisores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $data;

        } catch (\PDOException $e) {
            error_log("Erro ao carregar dados do formulário para o Cargo ID {$cargoId}: " . $e->getMessage());
            return null;
        }
    }

    public function findAllPaginated(array $params = []): array
    {
        $itemsPerPage = (int)($params['limit'] ?? 10);
        $currentPage = (int)($params['page'] ?? 1);
        $currentPage = max(1, $currentPage); 
        $term = $params['term'] ?? '';
        $sqlTerm = "%{$term}%";
        $all_bindings = [];

<<<<<<< HEAD
        // 2. Query para Contagem Total
        $count_sql = "
            SELECT COUNT(c.\"cargoId\")
            FROM cargos c
            LEFT JOIN cbos b ON b.\"cboId\" = c.\"cboId\"
        ";
=======
        $count_sql = "SELECT COUNT(c.cargoId) FROM cargos c LEFT JOIN cbos b ON b.cboId = c.cboId";
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
        $count_bindings = [];

        if (!empty($term)) {
            $count_sql .= " WHERE c.\"cargoNome\" ILIKE :term1 OR c.\"cargoResumo\" ILIKE :term2 OR b.\"cboTituloOficial\" ILIKE :term3";
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

        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $itemsPerPage) : 1;
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $itemsPerPage;

<<<<<<< HEAD
        // 4. Query Principal
        $sql = "
            SELECT 
                c.\"cargoId\", c.\"cargoNome\", c.\"cargoResumo\", c.\"cargoDataAtualizacao\",
                b.\"cboTituloOficial\"
            FROM cargos c
            LEFT JOIN cbos b ON b.\"cboId\" = c.\"cboId\"
        ";
=======
        $sql = "SELECT c.cargoId, c.cargoNome, c.cargoResumo, c.cargoDataCadastro, c.cargoDataAtualizacao, c.is_revisado, b.cboTituloOficial FROM cargos c LEFT JOIN cbos b ON b.cboId = c.cboId";
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a

        if (!empty($term)) {
            $sql .= " WHERE c.\"cargoNome\" ILIKE :term1 OR c.\"cargoResumo\" ILIKE :term2 OR b.\"cboTituloOficial\" ILIKE :term3";
            $all_bindings[':term1'] = $sqlTerm;
            $all_bindings[':term2'] = $sqlTerm;
            $all_bindings[':term3'] = $sqlTerm;
        }

        $orderBy = $params['order_by'] ?? 'c.cargoId';
        $sortDir = $params['sort_dir'] ?? 'ASC';
        $validColumns = ['c.cargoId', 'c.cargoNome', 'b.cboTituloOficial', 'c.cargoDataAtualizacao'];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'c.cargoId';
        $sortDir = in_array(strtoupper($sortDir), ['ASC', 'DESC']) ? strtoupper($sortDir) : 'ASC';
        $quotedOrder = \App\Core\Database::quoteIdent($orderBy);

<<<<<<< HEAD
        $sql .= " ORDER BY {$quotedOrder} {$sortDir}";
        $sql .= " LIMIT :limit OFFSET :offset";

=======
        $sql .= " ORDER BY {$orderBy} {$sortDir} LIMIT :limit OFFSET :offset";
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
        $all_bindings[':limit'] = $itemsPerPage;
        $all_bindings[':offset'] = $offset;

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

        return [
            'data' => $registros,
            'total' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage
        ];
    }

    public function clearRelationships(int $cargoId): bool
    {
        $joinTables = [
            'habilidades_cargo', 'caracteristicas_cargo', 'riscos_cargo',
            'cargo_sinonimos', 'cursos_cargo', 'recursos_grupos_cargo',
            'cargos_area', 'cargos_supervisores'
        ];
        $success = true;

        foreach ($joinTables as $table) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM {$table} WHERE \"cargoId\" = ?");
                $stmt->execute([$cargoId]);
            } catch (\PDOException $e) {
                error_log("Falha ao limpar relacionamento N:M na tabela {$table} para Cargo ID {$cargoId}: " . $e->getMessage());
                $success = false;
            }
        }
        return $success;
    }

    public function delete(int $id): int
    {
        $this->authService->checkAndFail('cargos:delete');
        $this->pdo->beginTransaction();
        try {
            $cleaned = $this->clearRelationships($id);
            if (!$cleaned) {
                throw new \Exception("Falha ao limpar relacionamentos N:M para o Cargo ID {$id}.");
            }
<<<<<<< HEAD

            // 2. Exclui o cargo principal
            $stmt = $this->pdo->prepare("DELETE FROM cargos WHERE \"cargoId\" = ?");
=======
            $stmt = $this->pdo->prepare("DELETE FROM cargos WHERE cargoId = ?");
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $stmt->execute([$id]);
            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
                $this->auditService->log('DELETE', 'cargos', $id, ['deletedCargoId' => $id]);
            }
            $this->pdo->commit();
            return $rowCount;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function findReportData(int $cargoId): ?array
    {
        if ($cargoId <= 0) return null;
        $data = [];

        try {
            $stmt = $this->pdo->prepare("
                SELECT 
<<<<<<< HEAD
                    c.*, e.\"escolaridadeTitulo\", b.\"cboCod\", b.\"cboTituloOficial\",
                    f.\"faixaNivel\", f.\"faixaSalarioMinimo\", f.\"faixaSalarioMaximo\",
                    n.\"nivelOrdem\", t.\"tipoNome\" AS \"tipoHierarquiaNome\",
                    sup.\"cargoNome\" AS \"cargoSupervisorNome\"
                FROM cargos c
                JOIN escolaridades e ON e.\"escolaridadeId\" = c.\"escolaridadeId\"  
                JOIN cbos b ON b.\"cboId\" = c.\"cboId\"                          
                LEFT JOIN faixas_salariais f ON f.\"faixaId\" = c.\"faixaId\"
                LEFT JOIN nivel_hierarquico n ON n.\"nivelId\" = c.\"nivelHierarquicoId\" 
                LEFT JOIN tipo_hierarquia t ON t.\"tipoId\" = n.\"tipoId\"              
                LEFT JOIN cargos sup ON sup.\"cargoId\" = c.\"cargoSupervisorId\"        
                WHERE c.\"cargoId\" = ?
=======
                    c.*, e.escolaridadeTitulo, b.cboCod, b.cboTituloOficial,
                    f.faixaNivel, f.faixaSalarioMinimo, f.faixaSalarioMaximo,
                    n.nivelOrdem, n.nivelDescricao, t.tipoNome AS tipoHierarquiaNome
                FROM cargos c
                JOIN escolaridades e ON e.escolaridadeId = c.escolaridadeId  
                JOIN cbos b ON b.cboId = c.cboId                          
                LEFT JOIN faixas_salariais f ON f.faixaId = c.faixaId
                LEFT JOIN nivel_hierarquico n ON n.nivelId = c.nivelHierarquicoId 
                LEFT JOIN tipo_hierarquia t ON t.tipoId = n.tipoId              
                WHERE c.cargoId = ?
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            ");
            $stmt->execute([$cargoId]);
            $cargo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cargo) return null;
            $data['cargo'] = $cargo;
            
<<<<<<< HEAD
            // 2. BUSCA DE RELACIONAMENTOS N:M
            
            // 2.1. HABILIDADES
            $stmt_hab = $this->pdo->prepare("SELECT DISTINCT h.\"habilidadeNome\", h.\"habilidadeTipo\", h.\"habilidadeDescricao\" FROM habilidades_cargo hc JOIN habilidades h ON h.\"habilidadeId\" = hc.\"habilidadeId\" WHERE hc.\"cargoId\" = ? ORDER BY h.\"habilidadeTipo\" DESC, h.\"habilidadeNome\" ASC");
=======
            $stmt_hab = $this->pdo->prepare("SELECT h.habilidadeNome, h.habilidadeTipo, h.habilidadeDescricao FROM habilidades_cargo hc JOIN habilidades h ON h.habilidadeId = hc.habilidadeId WHERE hc.cargoId = ? ORDER BY h.habilidadeTipo DESC, h.habilidadeNome ASC");
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $stmt_hab->execute([$cargoId]);
            $data['habilidades'] = $stmt_hab->fetchAll(PDO::FETCH_ASSOC);

<<<<<<< HEAD
            // 2.2. CARACTERÍSTICAS
            $stmt_car = $this->pdo->prepare("SELECT c.\"caracteristicaNome\", c.\"caracteristicaDescricao\" FROM caracteristicas_cargo cc JOIN caracteristicas c ON c.\"caracteristicaId\" = cc.\"caracteristicaId\" WHERE cc.\"cargoId\" = ? ORDER BY c.\"caracteristicaNome\" ASC");
=======
            $stmt_car = $this->pdo->prepare("SELECT c.caracteristicaNome, c.caracteristicaDescricao FROM caracteristicas_cargo cc JOIN caracteristicas c ON c.caracteristicaId = cc.caracteristicaId WHERE cc.cargoId = ? ORDER BY c.caracteristicaNome ASC");
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $stmt_car->execute([$cargoId]);
            $data['caracteristicas'] = $stmt_car->fetchAll(PDO::FETCH_ASSOC);
            
<<<<<<< HEAD
            // 2.3. RISCOS
            $stmt_ris = $this->pdo->prepare("SELECT r.\"riscoNome\", rc.\"riscoDescricao\" FROM riscos_cargo rc JOIN riscos r ON r.\"riscoId\" = rc.\"riscoId\" WHERE rc.\"cargoId\" = ? ORDER BY r.\"riscoNome\" ASC");
=======
            $stmt_ris = $this->pdo->prepare("SELECT r.riscoNome, rc.riscoDescricao FROM riscos_cargo rc JOIN riscos r ON r.riscoId = rc.riscoId WHERE rc.cargoId = ? ORDER BY r.riscoNome ASC");
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $stmt_ris->execute([$cargoId]);
            $data['riscos'] = $stmt_ris->fetchAll(PDO::FETCH_ASSOC);
            
<<<<<<< HEAD
            // 2.4. CURSOS
            // >>> REPARO CRÍTICO: Adiciona AS nome, AS obrigatorio, AS obs para evitar erro Undefined array key "obrigatorio"
            $stmt_cur = $this->pdo->prepare("SELECT cur.\"cursoNome\" AS nome, c_c.\"cursoCargoObrigatorio\" AS obrigatorio, c_c.\"cursoCargoObs\" AS obs FROM cursos_cargo c_c JOIN cursos cur ON cur.\"cursoId\" = c_c.\"cursoId\" WHERE c_c.\"cargoId\" = ? ORDER BY obrigatorio DESC, nome ASC");
=======
            $stmt_cur = $this->pdo->prepare("SELECT cur.cursoNome AS nome, c_c.cursoCargoObrigatorio AS obrigatorio, c_c.cursoCargoObs AS obs FROM cursos_cargo c_c JOIN cursos cur ON cur.cursoId = c_c.cursoId WHERE c_c.cargoId = ? ORDER BY obrigatorio DESC, nome ASC");
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $stmt_cur->execute([$cargoId]);
            $data['cursos'] = array_map(function ($curso) {
                $curso['obrigatorio'] = (bool)$curso['obrigatorio'];
                return $curso;
            }, $stmt_cur->fetchAll(PDO::FETCH_ASSOC));

<<<<<<< HEAD
            // 2.5. SINÔNIMOS
            $stmt_sin = $this->pdo->prepare("SELECT \"cargoSinonimoNome\" FROM cargo_sinonimos WHERE \"cargoId\" = ?"); 
            $stmt_sin->execute([$cargoId]);
            $data['sinonimos'] = $stmt_sin->fetchAll(PDO::FETCH_COLUMN);
            
            // 2.6. GRUPOS DE RECURSOS
            $stmt_rec = $this->pdo->prepare("SELECT rg.\"recursoGrupoNome\" FROM recursos_grupos_cargo rcg JOIN recursos_grupos rg ON rg.\"recursoGrupoId\" = rcg.\"recursoGrupoId\" WHERE rcg.\"cargoId\" = ? ORDER BY rg.\"recursoGrupoNome\" ASC");
            $stmt_rec->execute([$cargoId]);
            $data['recursos_grupos'] = $stmt_rec->fetchAll(PDO::FETCH_COLUMN);
            
            // 2.7. ÁREAS DE ATUAÇÃO
            $stmt_areas = $this->pdo->prepare("SELECT a.\"areaNome\" FROM cargos_area ca JOIN areas_atuacao a ON a.\"areaId\" = ca.\"areaId\" WHERE ca.\"cargoId\" = ? ORDER BY a.\"areaNome\" ASC");
=======
            $stmt_sin = $this->pdo->prepare("SELECT cargoSinonimoNome FROM cargo_sinonimos WHERE cargoId = ?"); 
            $stmt_sin->execute([$cargoId]);
            $data['sinonimos'] = $stmt_sin->fetchAll(PDO::FETCH_COLUMN);
            
            $stmt_rec = $this->pdo->prepare("SELECT rg.recursoGrupoNome FROM recursos_grupos_cargo rcg JOIN recursos_grupos rg ON rg.recursoGrupoId = rcg.recursoGrupoId WHERE rcg.cargoId = ? ORDER BY rg.recursoGrupoNome ASC");
            $stmt_rec->execute([$cargoId]);
            $data['recursos_grupos'] = $stmt_rec->fetchAll(PDO::FETCH_COLUMN);
            
            $stmt_areas = $this->pdo->prepare("SELECT a.areaNome FROM cargos_area ca JOIN areas_atuacao a ON a.areaId = ca.areaId WHERE ca.cargoId = ? ORDER BY a.areaNome ASC");
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a
            $stmt_areas->execute([$cargoId]);
            $data['areas_atuacao'] = $stmt_areas->fetchAll(PDO::FETCH_COLUMN);

            $stmt_sup = $this->pdo->prepare("SELECT c.cargoNome FROM cargos_supervisores cs JOIN cargos c ON c.cargoId = cs.supervisorId WHERE cs.cargoId = ? ORDER BY c.cargoNome ASC");
            $stmt_sup->execute([$cargoId]);
            $data['supervisores'] = $stmt_sup->fetchAll(PDO::FETCH_COLUMN);

            return $data;

        } catch (\Exception $e) {
            error_log("Erro FATAL (falha de QUERY/DB) ao carregar Cargo ID {$cargoId}: " . $e->getMessage());
            return null;
        }
    }

    public function findAdjacentCargoIds(int $currentId, string $orderBy, string $sortDir, string $term = ''): array
    {
        $validColumns = ['c.cargoId', 'c.cargoNome', 'b.cboTituloOficial', 'c.cargoDataAtualizacao'];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'c.cargoId';
        $sortDir = in_array(strtoupper($sortDir), ['ASC', 'DESC']) ? strtoupper($sortDir) : 'ASC';
        $quotedOrderAdj = \App\Core\Database::quoteIdent($orderBy);

        $sqlTerm = "%{$term}%";
        $bindings = [];
        $whereClause = "";
        if (!empty($term)) {
            $whereClause = " WHERE c.\"cargoNome\" ILIKE :term1 OR c.\"cargoResumo\" ILIKE :term2 OR b.\"cboTituloOficial\" ILIKE :term3";
            $bindings[':term1'] = $sqlTerm;
            $bindings[':term2'] = $sqlTerm;
            $bindings[':term3'] = $sqlTerm;
        }

<<<<<<< HEAD
        // 3. Query com Funções de Janela (LEAD/LAG)
        $sql = "
            WITH OrderedCargos AS (
                SELECT 
                    c.\"cargoId\",
                    LAG(c.\"cargoId\") OVER (ORDER BY {$quotedOrderAdj} {$sortDir}) AS prev_id,
                    LEAD(c.\"cargoId\") OVER (ORDER BY {$quotedOrderAdj} {$sortDir}) AS next_id
                FROM cargos c
                LEFT JOIN cbos b ON b.\"cboId\" = c.\"cboId\"
                {$whereClause}
            )
            SELECT prev_id, next_id
            FROM OrderedCargos
            WHERE \"cargoId\" = :currentId
        ";
=======
        $sql = "WITH OrderedCargos AS (
                SELECT c.cargoId, LAG(c.cargoId) OVER (ORDER BY {$orderBy} {$sortDir}) AS prev_id, LEAD(c.cargoId) OVER (ORDER BY {$orderBy} {$sortDir}) AS next_id
                FROM cargos c LEFT JOIN cbos b ON b.cboId = c.cboId {$whereClause}
            ) SELECT prev_id, next_id FROM OrderedCargos WHERE cargoId = :currentId";
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a

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

    public function findFirstAndLastCargoIds(string $orderBy, string $sortDir, string $term = ''): array
    {
        $validColumns = ['c.cargoId', 'c.cargoNome', 'b.cboTituloOficial', 'c.cargoDataAtualizacao'];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'c.cargoId';
        $sortDir = in_array(strtoupper($sortDir), ['ASC', 'DESC']) ? strtoupper($sortDir) : 'ASC';
        $quotedOrderFL = \App\Core\Database::quoteIdent($orderBy);

        $sqlTerm = "%{$term}%";
        $bindings = [];
        $whereClause = "";
        if (!empty($term)) {
            $whereClause = " WHERE c.\"cargoNome\" ILIKE :term1 OR c.\"cargoResumo\" ILIKE :term2 OR b.\"cboTituloOficial\" ILIKE :term3";
            $bindings[':term1'] = $sqlTerm;
            $bindings[':term2'] = $sqlTerm;
            $bindings[':term3'] = $sqlTerm;
        }

<<<<<<< HEAD
        // 3. Query com Funções de Janela (FIRST_VALUE/LAST_VALUE)
        $sql = "
            WITH OrderedCargos AS (
                SELECT 
                    c.\"cargoId\",
                    FIRST_VALUE(c.\"cargoId\") OVER (ORDER BY {$quotedOrderFL} {$sortDir} ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) AS first_id,
                    LAST_VALUE(c.\"cargoId\") OVER (ORDER BY {$quotedOrderFL} {$sortDir} ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) AS last_id
                FROM cargos c
                LEFT JOIN cbos b ON b.\"cboId\" = c.\"cboId\"
                {$whereClause}
            )
            SELECT first_id, last_id
            FROM OrderedCargos
            LIMIT 1
        ";
=======
        $sql = "WITH OrderedCargos AS (
                SELECT c.cargoId, FIRST_VALUE(c.cargoId) OVER (ORDER BY {$orderBy} {$sortDir} ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) AS first_id, LAST_VALUE(c.cargoId) OVER (ORDER BY {$orderBy} {$sortDir} ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) AS last_id
                FROM cargos c LEFT JOIN cbos b ON b.cboId = c.cboId {$whereClause}
            ) SELECT first_id, last_id FROM OrderedCargos LIMIT 1";
>>>>>>> bb884dcf3453295c611e83f375ba02211d8cbd0a

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

    /**
     * Retorna os IDs de softskills base que devem ser herdadas automaticamente
     * com base no tipo hierárquico do nível selecionado.
     *
     * Os IDs são lidos dinamicamente da tabela `leadership_softskills_config`,
     * permitindo configuração via interface administrativa sem alterar código.
     *
     * @param int $nivelHierarquicoId
     * @return array IDs de habilidades a incluir (vazio se não aplicável)
     */
    private function getBaseSoftskillsForNivel(int $nivelHierarquicoId): array
    {
        if ($nivelHierarquicoId <= 0) {
            return [];
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT lsc."habilidadeId"
                 FROM nivel_hierarquico n
                 JOIN leadership_softskills_config lsc ON lsc."tipoId" = n."tipoId"
                 WHERE n."nivelId" = ?
                 ORDER BY lsc."habilidadeId"'
            );
            $stmt->execute([$nivelHierarquicoId]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_map('intval', $ids);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar softskills de liderança para nivelId {$nivelHierarquicoId}: " . $e->getMessage());
            return [];
        }
    }
}