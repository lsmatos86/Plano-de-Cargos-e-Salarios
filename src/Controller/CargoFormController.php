<?php
// Arquivo: src/Controller/CargoFormController.php

namespace App\Controller;

// Importa as classes necessárias
use App\Service\AuthService;
use PDO;
use PDOException;
use Exception; // Garante que a classe Exception base está disponível

class CargoFormController
{
    private PDO $pdo;
    private AuthService $authService;

    public function __construct(PDO $pdo, AuthService $authService)
    {
        $this->pdo = $pdo;
        $this->authService = $authService;
    }

    /**
     * Ponto de entrada principal. Lida com a requisição e retorna dados para a View.
     */
    public function handleRequest(array $getData, array $postData, string $requestMethod): array
    {
        // 1. SEGURANÇA: A permissão é verificada primeiro
        // Usando a permissão granular que encontramos na depuração anterior
        $this->authService->checkAndFail('cargos:manage', '../index.php?error=Acesso+negado');

        $message = $getData['message'] ?? '';
        $message_type = $getData['type'] ?? 'info';

        // 2. VARIÁVEIS DE CONTROLE (lógica de GET)
        $originalId = (int)($getData['id'] ?? 0);
        $action = $getData['action'] ?? '';

        $isDuplicating = $action === 'duplicate' && $originalId > 0;
        $isEditing = !$isDuplicating && $originalId > 0;
        $currentFormId = $isEditing ? $originalId : 0; 
        $cargoId = $originalId; // ID usado para buscar os dados

        if ($isDuplicating) {
            $page_title = 'Duplicar Cargo (Novo Registro)';
        } elseif ($isEditing) {
            $page_title = 'Editar Cargo';
        } else {
            $page_title = 'Novo Cargo';
        }

        // 3. LÓGICA DE SALVAMENTO (POST)
        try {
            if ($requestMethod === 'POST' && isset($postData['cargoNome'])) {
                // Chama o método de salvamento
                $novoCargoId = $this->saveCargo($postData);
                
                $message = "Cargo salvo com sucesso! ID: {$novoCargoId}";
                $message_type = 'success';
                
                // Redireciona para evitar reenvio do formulário
                header("Location: cargos_form.php?id={$novoCargoId}&message=" . urlencode($message) . "&type={$message_type}");
                exit;
            }
        } catch (Exception $e) {
            $message = "Erro fatal ao salvar. Erro: " . $e->getMessage();
            $message_type = 'danger';
            // Se falhar, recarrega os dados do POST para preencher o formulário
            $cargo = $postData; 
            $cargoId = (int)($postData['cargoId'] ?? 0);
        }

        // 4. CARREGAMENTO DE DADOS (LOOKUPS E DADOS DO CARGO)
        
        // --- Lookups Mestres ---
        // (Nota: A lógica de getLookupData, getHabilidadesGrouped, etc. está em functions.php)
        $cbos = getLookupData($this->pdo, 'cbos', 'cboId', 'cboCod', 'cboTituloOficial'); 
        $escolaridades = getLookupData($this->pdo, 'escolaridades', 'escolaridadeId', 'escolaridadeTitulo');
        $habilidadesAgrupadas = getHabilidadesGrouped($this->pdo);
        $habilidades = getLookupData($this->pdo, 'habilidades', 'habilidadeId', 'habilidadeNome'); 
        $caracteristicas = getLookupData($this->pdo, 'caracteristicas', 'caracteristicaId', 'caracteristicaNome');
        $riscos = getLookupData($this->pdo, 'riscos', 'riscoId', 'riscoNome'); 
        $cursos = getLookupData($this->pdo, 'cursos', 'cursoId', 'cursoNome');
        $recursosGrupos = getLookupData($this->pdo, 'recursos_grupos', 'recursoGrupoId', 'recursoGrupoNome');
        $faixasSalariais = getLookupData($this->pdo, 'faixas_salariais', 'faixaId', 'faixaNivel');
        $areasAtuacao = getAreaHierarchyLookup($this->pdo); 
        $cargosSupervisor = getLookupData($this->pdo, 'cargos', 'cargoId', 'cargoNome');

        // Níveis Hierárquicos (lógica customizada)
        $niveisOrdenados = [];
        foreach (getLookupData($this->pdo, 'nivel_hierarquico', 'nivelId', 'nivelOrdem') as $id => $ordem) {
            $stmt = $this->pdo->prepare("SELECT nivelOrdem, nivelDescricao FROM nivel_hierarquico WHERE nivelId = ?");
            $stmt->execute([$id]);
            $nivelData = $stmt->fetch();
            if ($nivelData) {
                $niveisOrdenados[$id] = "{$nivelData['nivelOrdem']}º - " . ($nivelData['nivelDescricao'] ?? 'N/A');
            }
        }
        arsort($niveisOrdenados); 

        // --- Variáveis de estado do Formulário ---
        $cargo = [];
        $cargoAreas = [];
        $cargoHabilidades = [];
        $cargoCaracteristicas = [];
        $cargoRiscos = []; 
        $cargoCursos = [];
        $cargoRecursosGrupos = [];
        $cargoSinonimos = [];

        // --- Busca dados para Edição ou Duplicação ---
        if (($isEditing || $isDuplicating) && empty($postData)) { // Só busca se não for um POST falho
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM cargos WHERE cargoId = ?");
                $stmt->execute([$cargoId]); 
                $cargo = $stmt->fetch();

                if ($cargo) {
                    // Carrega todas as tabelas N:M associadas
                    $cargoSinonimos = $this->findRelatedData('cargo_sinonimos', 'cargoSinonimoId AS id, cargoSinonimoNome AS nome', $cargoId);
                    $cargoRiscos = $this->findRelatedData('riscos_cargo rc JOIN riscos r ON r.riscoId = rc.riscoId', 'rc.riscoId AS id, r.riscoNome AS nome, rc.riscoDescricao AS descricao', $cargoId);
                    $cargoAreas = $this->findRelatedData('cargos_area ca JOIN areas_atuacao a ON a.areaId = ca.areaId', 'ca.areaId AS id, a.areaNome AS nome', $cargoId);
                    $cargoHabilidades = $this->findRelatedData('habilidades_cargo hc JOIN habilidades h ON h.habilidadeId = hc.habilidadeId', 'hc.habilidadeId AS id, h.habilidadeNome AS nome, h.habilidadeTipo AS tipo', $cargoId);
                    $cargoCaracteristicas = $this->findRelatedData('caracteristicas_cargo cc JOIN caracteristicas c ON c.caracteristicaId = cc.caracteristicaId', 'cc.caracteristicaId AS id, c.caracteristicaNome AS nome', $cargoId);
                    $cargoRecursosGrupos = $this->findRelatedData('recursos_grupos_cargo rgc JOIN recursos_grupos rg ON rg.recursoGrupoId = rgc.recursoGrupoId', 'rgc.recursoGrupoId AS id, rg.recursoGrupoNome AS nome', $cargoId);
                    
                    // Cursos (com lógica de boolean)
                    $stmtCursos = $this->pdo->prepare("SELECT curc.cursoId AS id, cur.cursoNome AS nome, curc.cursoCargoObrigatorio AS obrigatorio, curc.cursoCargoObs AS obs FROM cursos_cargo curc JOIN cursos cur ON cur.cursoId = curc.cursoId WHERE curc.cargoId = ?");
                    $stmtCursos->execute([$cargoId]);
                    $cargoCursos = array_map(function($curso) {
                        $curso['obrigatorio'] = (bool)$curso['obrigatorio'];
                        return $curso;
                    }, $stmtCursos->fetchAll(PDO::FETCH_ASSOC));

                    if ($isDuplicating) {
                        $cargo['cargoNome'] = ($cargo['cargoNome'] ?? 'Cargo Duplicado') . ' (CÓPIA)';
                        unset($cargo['cargoId']); 
                    }
                } else {
                    $message = "Cargo não encontrado.";
                    $message_type = 'danger';
                    $isEditing = false;
                }

            } catch (PDOException $e) {
                $message = "Erro ao carregar dados: " . $e->getMessage();
                $message_type = 'danger';
            }
        }

        // 5. RETORNA O PACOTE DE DADOS PARA A VIEW
        return [
            'page_title' => $page_title,
            'message' => $message,
            'message_type' => $message_type,
            'originalId' => $originalId,
            'isEditing' => $isEditing,
            'isDuplicating' => $isDuplicating,
            'currentFormId' => $currentFormId,
            'cbos' => $cbos,
            'escolaridades' => $escolaridades,
            'habilidadesAgrupadas' => $habilidadesAgrupadas,
            'habilidades' => $habilidades,
            'caracteristicas' => $caracteristicas,
            'riscos' => $riscos,
            'cursos' => $cursos,
            'recursosGrupos' => $recursosGrupos,
            'faixasSalariais' => $faixasSalariais,
            'areasAtuacao' => $areasAtuacao,
            'cargosSupervisor' => $cargosSupervisor,
            'niveisOrdenados' => $niveisOrdenados,
            'cargo' => $cargo,
            'cargoAreas' => $cargoAreas,
            'cargoHabilidades' => $cargoHabilidades,
            'cargoCaracteristicas' => $cargoCaracteristicas,
            'cargoRiscos' => $cargoRiscos,
            'cargoCursos' => $cargoCursos,
            'cargoRecursosGrupos' => $cargoRecursosGrupos,
            'cargoSinonimos' => $cargoSinonimos,
        ];
    }

    /**
     * Helper para buscar dados N:M
     */
    private function findRelatedData(string $tableAndJoins, string $selectFields, int $cargoId): array
    {
        $sql = "SELECT $selectFields FROM $tableAndJoins WHERE cargoId = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cargoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lógica de Salvamento (extraída do arquivo original)
     */
    private function saveCargo(array $postData): int
    {
        $cargoIdSubmissao = (int)($postData['cargoId'] ?? 0);
        $isUpdating = $cargoIdSubmissao > 0;

        // 3.1 Captura dos Dados Principais
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

        // Validação
        if (empty($data['cargoNome']) || empty($data['cboId']) || $data['escolaridadeId'] <= 0) {
            throw new Exception("Os campos Nome do Cargo, CBO e Escolaridade são obrigatórios.");
        }

        $this->pdo->beginTransaction();
        try {
            // 3.3 PREPARAÇÃO DA QUERY PRINCIPAL (UPDATE/CREATE)
            $fields = array_keys($data); $bindings = array_values($data);
            if ($isUpdating) {
                $sql_fields = implode(' = ?, ', $fields) . ' = ?';
                $sql = "UPDATE cargos SET {$sql_fields}, cargoDataAtualizacao = CURRENT_TIMESTAMP() WHERE cargoId = ?";
                $bindings[] = $cargoIdSubmissao;
                $stmt = $this->pdo->prepare($sql); $stmt->execute($bindings);
                $novoCargoId = $cargoIdSubmissao;
            } else {
                $sql_fields = implode(', ', $fields); $placeholders = implode(', ', array_fill(0, count($fields), '?'));
                $sql = "INSERT INTO cargos ({$sql_fields}) VALUES ({$placeholders})";
                $stmt = $this->pdo->prepare($sql); $stmt->execute($bindings);
                $novoCargoId = (int)$this->pdo->lastInsertId();
            }
            
            // 3.4 SALVAMENTO DOS RELACIONAMENTOS N:M SIMPLES
            $relacionamentosSimples = [
                'cargos_area' => ['coluna' => 'areaId', 'valores' => (array)($postData['areaId'] ?? [])],
                'habilidades_cargo' => ['coluna' => 'habilidadeId', 'valores' => (array)($postData['habilidadeId'] ?? [])],
                'caracteristicas_cargo' => ['coluna' => 'caracteristicaId', 'valores' => (array)($postData['caracteristicaId'] ?? [])],
                'recursos_grupos_cargo' => ['coluna' => 'recursoGrupoId', 'valores' => (array)($postData['recursoGrupoId'] ?? [])],
            ];
            
            foreach ($relacionamentosSimples as $tableName => $rel) {
                $this->syncRelationship($tableName, 'cargoId', $rel['coluna'], $novoCargoId, $rel['valores']);
            }
            
            // 3.5 SALVAMENTO DOS RISCOS (COMPLEX)
            $this->pdo->prepare("DELETE FROM riscos_cargo WHERE cargoId = ?")->execute([$novoCargoId]);
            $riscosInput = [
                'riscoId' => (array)($postData['riscoId'] ?? []),
                'riscoDescricao' => (array)($postData['riscoDescricao'] ?? []),
            ];
            if (!empty($riscosInput['riscoId'])) {
                $sql_risco = "INSERT INTO riscos_cargo (cargoId, riscoId, riscoDescricao) VALUES (?, ?, ?)";
                $stmt_risco = $this->pdo->prepare($sql_risco);
                for ($i = 0; $i < count($riscosInput['riscoId']); $i++) {
                    $stmt_risco->execute([$novoCargoId, (int)$riscosInput['riscoId'][$i], $riscosInput['riscoDescricao'][$i] ?? '']);
                }
            }

            // 3.6 SALVAMENTO DOS CURSOS (COMPLEX)
            $this->pdo->prepare("DELETE FROM cursos_cargo WHERE cargoId = ?")->execute([$novoCargoId]);
            $cursosInput = [
                'cursoId' => (array)($postData['cursoId'] ?? []),
                'cursoCargoObrigatorio' => (array)($postData['cursoCargoObrigatorio'] ?? []),
                'cursoCargoObs' => (array)($postData['cursoCargoObs'] ?? []),
            ];
            if (!empty($cursosInput['cursoId'])) {
                $sql_curso = "INSERT INTO cursos_cargo (cargoId, cursoId, cursoCargoObrigatorio, cursoCargoObs) VALUES (?, ?, ?, ?)";
                $stmt_curso = $this->pdo->prepare($sql_curso);
                for ($i = 0; $i < count($cursosInput['cursoId']); $i++) {
                    $obrigatorio = (int)($cursosInput['cursoCargoObrigatorio'][$i] ?? 0);
                    $obs = $cursosInput['cursoCargoObs'][$i] ?? '';
                    $stmt_curso->execute([$novoCargoId, (int)$cursosInput['cursoId'][$i], $obrigatorio, $obs]);
                }
            }
            
            // 3.7 SALVAMENTO DOS SINÔNIMOS
            $sinonimosInput = (array)($postData['sinonimoNome'] ?? []);
            $this->syncRelationship('cargo_sinonimos', 'cargoId', 'cargoSinonimoNome', $novoCargoId, $sinonimosInput);

            $this->pdo->commit();
            return $novoCargoId;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // Lança a exceção para ser tratada pelo handleRequest
            throw new Exception("Erro de Banco de Dados: " . $e->getMessage()); 
        }
    }
    
    /**
     * Helper para sincronizar tabelas N:M simples
     */
    private function syncRelationship(string $tableName, string $fkColumn, string $valueColumn, int $fkId, array $values): void
    {
        $this->pdo->prepare("DELETE FROM {$tableName} WHERE {$fkColumn} = ?")->execute([$fkId]);
        if (!empty($values)) {
            $insert_sql = "INSERT INTO {$tableName} ({$fkColumn}, {$valueColumn}) VALUES (?, ?)";
            $stmt_rel = $this->pdo->prepare($insert_sql);
            foreach ($values as $valor) {
                // Remove espaços em branco se for string (para sinônimos)
                $valor = is_string($valor) ? trim($valor) : $valor; 
                if (!empty($valor)) {
                    $stmt_rel->execute([$fkId, $valor]);
                }
            }
        }
    }
}