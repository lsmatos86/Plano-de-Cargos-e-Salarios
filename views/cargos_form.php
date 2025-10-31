<?php
// Arquivo: views/cargos_form.php (Formulário de Cadastro e Edição de Cargos)

require_once '../config.php';
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$pdo = getDbConnection();
$message = '';
$message_type = '';

// Variáveis de Controle
$originalId = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

// Definindo o modo da página
$isDuplicating = $action === 'duplicate' && $originalId > 0;
$isEditing = !$isDuplicating && $originalId > 0;

// O ID que será enviado no formulário (0 para novo/duplicação, ou o ID existente para edição)
$currentFormId = $isEditing ? $originalId : 0; 
$cargoId = $originalId; // ID usado para buscar os dados

$page_title = $isDuplicating ? 'Duplicar Cargo (Novo Registro)' : ($isEditing ? 'Editar Cargo' : 'Novo Cargo');

// ----------------------------------------------------
// 1. CARREGAMENTO DOS LOOKUPS MESTRES
// ----------------------------------------------------
$cbos = getLookupData($pdo, 'cbos', 'cboId', 'cboNome', 'cboTituloOficial');
$escolaridades = getLookupData($pdo, 'escolaridades', 'escolaridadeId', 'escolaridadeTitulo');
// Habilidades agrupadas e simples (para select e lookup)
$habilidadesAgrupadas = getHabilidadesGrouped($pdo);
$habilidades = getLookupData($pdo, 'habilidades', 'habilidadeId', 'habilidadeNome'); 
$caracteristicas = getLookupData($pdo, 'caracteristicas', 'caracteristicaId', 'caracteristicaNome');
$riscos = getLookupData($pdo, 'riscos', 'riscoId', 'riscoNome'); 
$cursos = getLookupData($pdo, 'cursos', 'cursoId', 'cursoNome');
$recursosGrupos = getLookupData($pdo, 'recursos_grupos', 'recursoGrupoId', 'recursoGrupoNome');

// Novos lookups de Hierarquia e Salário
$faixasSalariais = getLookupData($pdo, 'faixas_salariais', 'faixaId', 'faixaNivel');
$areasAtuacao = getAreaHierarchyLookup($pdo); 
$cargosSupervisor = getLookupData($pdo, 'cargos', 'cargoId', 'cargoNome');

// --- Variáveis de estado do Formulário ---
$cargo = [];
$cargoAreas = [];
$cargoHabilidades = [];
$cargoCaracteristicas = [];
$cargoRiscos = []; 
$cargoCursos = [];
$cargoRecursosGrupos = [];
$cargoSinonimos = []; // NOVO: Sinônimos


// ----------------------------------------------------
// 2. BUSCA DADOS PARA EDIÇÃO OU DUPLICAÇÃO (Adaptado para o novo formato)
// ----------------------------------------------------
if ($isEditing || $isDuplicating) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM cargos WHERE cargoId = ?");
        $stmt->execute([$cargoId]); 
        $cargo = $stmt->fetch();

        if ($cargo) {
            
            // SINÔNIMOS (Livre texto)
            $stmt = $pdo->prepare("SELECT cargoSinonimoId AS id, cargoSinonimoNome AS nome FROM cargo_sinonimos WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoSinonimos = $stmt->fetchAll(PDO::FETCH_ASSOC); 

            // RISCOS (COMPLEX N:M): Busca ID, Descricao
            $stmt = $pdo->prepare("SELECT rc.riscoId AS id, r.riscoNome AS nome, rc.riscoDescricao AS descricao FROM riscos_cargo rc JOIN riscos r ON r.riscoId = rc.riscoId WHERE rc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoRiscos = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            
            // ÁREAS DE ATUAÇÃO (SIMPLE N:M): Busca ID, Nome
            $stmt = $pdo->prepare("SELECT ca.areaId AS id, a.areaNome AS nome FROM cargos_area ca JOIN areas_atuacao a ON a.areaId = ca.areaId WHERE ca.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoAreas = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            
            // HABILIDADES (SIMPLE N:M): Busca ID, Nome, Tipo
            $stmt = $pdo->prepare("SELECT hc.habilidadeId AS id, h.habilidadeNome AS nome, h.habilidadeTipo AS tipo FROM habilidades_cargo hc JOIN habilidades h ON h.habilidadeId = hc.habilidadeId WHERE hc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoHabilidades = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            
            // CARACTERÍSTICAS (SIMPLE N:M): Busca ID, Nome
            $stmt = $pdo->prepare("SELECT cc.caracteristicaId AS id, c.caracteristicaNome AS nome FROM caracteristicas_cargo cc JOIN caracteristicas c ON c.caracteristicaId = cc.caracteristicaId WHERE cc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoCaracteristicas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // CURSOS (COMPLEX N:M): Busca ID, Nome, Obrigatório, Observação
            $stmt = $pdo->prepare("SELECT curc.cursoId AS id, cur.cursoNome AS nome, curc.cursoCargoObrigatorio AS obrigatorio, curc.cursoCargoObs AS obs FROM cursos_cargo curc JOIN cursos cur ON cur.cursoId = curc.cursoId WHERE curc.cargoId = ?");
            $stmt->execute([$cargoId]);
            // Converte o campo 'obrigatorio' (0/1) para boolean para o JS
            $cargoCursos = array_map(function($curso) {
                $curso['obrigatorio'] = (bool)$curso['obrigatorio'];
                return $curso;
            }, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // GRUPOS DE RECURSOS (SIMPLE N:M): Busca ID, Nome
            $stmt = $pdo->prepare("SELECT rgc.recursoGrupoId AS id, rg.recursoGrupoNome AS nome FROM recursos_grupos_cargo rgc JOIN recursos_grupos rg ON rg.recursoGrupoId = rgc.recursoGrupoId WHERE rgc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoRecursosGrupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


// ----------------------------------------------------
// 3. LÓGICA DE SALVAMENTO (POST)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cargoNome'])) {
    
    $cargoIdSubmissao = (int)($_POST['cargoId'] ?? 0);
    $isUpdating = $cargoIdSubmissao > 0;
    
    // 3.1 Captura dos Dados Principais (omitido para brevidade)
    $data = [
        'cargoNome' => trim($_POST['cargoNome'] ?? ''), 'cargoDescricao' => trim($_POST['cargoDescricao'] ?? null),
        'cboId' => (int)($_POST['cboId'] ?? 0), 'cargoResumo' => trim($_POST['cargoResumo'] ?? null),
        'escolaridadeId' => (int)($_POST['escolaridadeId'] ?? 0), 'cargoExperiencia' => trim($_POST['cargoExperiencia'] ?? null),
        'cargoCondicoes' => trim($_POST['cargoCondicoes'] ?? null), 'cargoComplexidade' => trim($_POST['cargoComplexidade'] ?? null),
        'cargoResponsabilidades' => trim($_POST['cargoResponsabilidades'] ?? null),
        'faixaId' => empty($_POST['faixaId']) ? null : (int)$_POST['faixaId'],
        'nivelHierarquicoId' => empty($_POST['nivelHierarquicoId']) ? null : (int)$_POST['nivelHierarquicoId'],
        'cargoSupervisorId' => empty($_POST['cargoSupervisorId']) ? null : (int)$_POST['cargoSupervisorId'],
    ];

    // Relacionamentos N:M SIMPLES
    $relacionamentosSimples = [
        'cargos_area' => ['coluna' => 'areaId', 'valores' => (array)($_POST['areaId'] ?? [])],
        'habilidades_cargo' => ['coluna' => 'habilidadeId', 'valores' => (array)($_POST['habilidadeId'] ?? [])],
        'caracteristicas_cargo' => ['coluna' => 'caracteristicaId', 'valores' => (array)($_POST['caracteristicaId'] ?? [])],
        'recursos_grupos_cargo' => ['coluna' => 'recursoGrupoId', 'valores' => (array)($_POST['recursoGrupoId'] ?? [])],
    ];
    
    // Relacionamentos N:M COMPLEXOS (Riscos e Cursos)
    $riscosInput = [
        'riscoId' => (array)($_POST['riscoId'] ?? []),
        'riscoDescricao' => (array)($_POST['riscoDescricao'] ?? []),
    ];
    
    $cursosInput = [
        'cursoId' => (array)($_POST['cursoId'] ?? []),
        'cursoCargoObrigatorio' => (array)($_POST['cursoCargoObrigatorio'] ?? []), // Array de 0 ou 1
        'cursoCargoObs' => (array)($_POST['cursoCargoObs'] ?? []),
    ];

    $sinonimosInput = (array)($_POST['sinonimoNome'] ?? []); // NOVO: Sinônimos
    
    if (empty($data['cargoNome']) || $data['cboId'] <= 0 || $data['escolaridadeId'] <= 0) {
        $message = "Os campos Nome do Cargo, CBO e Escolaridade são obrigatórios.";
        $message_type = 'danger';
        $cargo = array_merge($cargo, $_POST);
        $cargoId = $cargoIdSubmissao;
    } else {

        try {
            $pdo->beginTransaction();

            // 3.3 PREPARAÇÃO DA QUERY PRINCIPAL (UPDATE/CREATE)
            $fields = array_keys($data); $bindings = array_values($data);
            if ($isUpdating) {
                $sql_fields = implode(' = ?, ', $fields) . ' = ?';
                $sql = "UPDATE cargos SET {$sql_fields}, cargoDataAtualizacao = CURRENT_TIMESTAMP() WHERE cargoId = ?";
                $bindings[] = $cargoIdSubmissao;
                $stmt = $pdo->prepare($sql); $stmt->execute($bindings);
                $novoCargoId = $cargoIdSubmissao;
            } else {
                $sql_fields = implode(', ', $fields); $placeholders = implode(', ', array_fill(0, count($fields), '?'));
                $sql = "INSERT INTO cargos ({$sql_fields}) VALUES ({$placeholders})";
                $stmt = $pdo->prepare($sql); $stmt->execute($bindings);
                $novoCargoId = $pdo->lastInsertId();
            }
            
            // 3.4 SALVAMENTO DOS RELACIONAMENTOS N:M SIMPLES
            foreach ($relacionamentosSimples as $tableName => $rel) {
                $column = $rel['coluna'];
                $valores = $rel['valores'];
                $pdo->prepare("DELETE FROM {$tableName} WHERE cargoId = ?")->execute([$novoCargoId]);
                if (!empty($valores)) {
                    $insert_sql = "INSERT INTO {$tableName} (cargoId, {$column}) VALUES (?, ?)";
                    $stmt_rel = $pdo->prepare($insert_sql);
                    foreach ($valores as $valorId) {
                        $stmt_rel->execute([$novoCargoId, (int)$valorId]);
                    }
                }
            }
            
            // 3.5 SALVAMENTO DOS RISCOS (COMPLEX)
            $pdo->prepare("DELETE FROM riscos_cargo WHERE cargoId = ?")->execute([$novoCargoId]);
            if (!empty($riscosInput['riscoId'])) {
                $sql_risco = "INSERT INTO riscos_cargo (cargoId, riscoId, riscoDescricao) VALUES (?, ?, ?)";
                $stmt_risco = $pdo->prepare($sql_risco);
                for ($i = 0; $i < count($riscosInput['riscoId']); $i++) {
                    $stmt_risco->execute([$novoCargoId, (int)$riscosInput['riscoId'][$i], $riscosInput['riscoDescricao'][$i] ?? '']);
                }
            }

            // 3.6 SALVAMENTO DOS CURSOS (COMPLEX)
            $pdo->prepare("DELETE FROM cursos_cargo WHERE cargoId = ?")->execute([$novoCargoId]);
            if (!empty($cursosInput['cursoId'])) {
                $sql_curso = "INSERT INTO cursos_cargo (cargoId, cursoId, cursoCargoObrigatorio, cursoCargoObs) VALUES (?, ?, ?, ?)";
                $stmt_curso = $pdo->prepare($sql_curso);
                for ($i = 0; $i < count($cursosInput['cursoId']); $i++) {
                    $obrigatorio = (int)($cursosInput['cursoCargoObrigatorio'][$i] ?? 0);
                    $obs = $cursosInput['cursoCargoObs'][$i] ?? '';
                    $stmt_curso->execute([$novoCargoId, (int)$cursosInput['cursoId'][$i], $obrigatorio, $obs]);
                }
            }
            
            // 3.7 SALVAMENTO DOS SINÔNIMOS (NOVO)
            $pdo->prepare("DELETE FROM cargo_sinonimos WHERE cargoId = ?")->execute([$novoCargoId]);
            if (!empty($sinonimosInput)) {
                $sql_sin = "INSERT INTO cargo_sinonimos (cargoId, cargoSinonimoNome) VALUES (?, ?)";
                $stmt_sin = $pdo->prepare($sql_sin);
                foreach ($sinonimosInput as $sinonimoNome) {
                     $stmt_sin->execute([$novoCargoId, trim($sinonimoNome)]);
                }
            }

            $pdo->commit();
            
            $message = "Cargo salvo com sucesso! ID: {$novoCargoId}";
            $message_type = 'success';
            
            header("Location: cargos_form.php?id={$novoCargoId}&message=" . urlencode($message) . "&type={$message_type}");
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Erro fatal ao salvar. Erro: " . $e->getMessage();
            $message_type = 'danger';
            $cargo = array_merge($cargo, $_POST); 
            $cargoId = $cargoIdSubmissao; 
        }
    }
}

// Mensagens após redirecionamento
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// Carrega os Níveis Hierárquicos já ordenados (mantido)
$niveisOrdenados = [];
foreach (getLookupData($pdo, 'nivel_hierarquico', 'nivelId', 'nivelOrdem') as $id => $ordem) {
    $stmt = $pdo->prepare("SELECT nivelOrdem, nivelDescricao FROM nivel_hierarquico WHERE nivelId = ?");
    $stmt->execute([$id]);
    $nivelData = $stmt->fetch();
    if ($nivelData) {
        $niveisOrdenados[$id] = "{$nivelData['nivelOrdem']}º - " . ($nivelData['nivelDescricao'] ?? 'N/A');
    }
}
arsort($niveisOrdenados); 
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>
        textarea { resize: vertical; }
        .grid-header { background-color: #f8f9fa; border-top: 1px solid #dee2e6; padding-top: 10px; }
        .grid-body tr:last-child td { border-bottom: none; }
        .grid-action-cell { width: 50px; }
        .grid-risco-desc textarea { width: 100%; resize: vertical; min-height: 40px; border: 1px solid #ced4da; padding: 5px; }
        .table-group-separator { background-color: #e9ecef; }
        .grid-container { max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px; }
        /* Estilos para Select2 no modal */
        .select2-container--bootstrap-5 .select2-dropdown { z-index: 1060; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid container">
        <a class="navbar-brand" href="../index.php">ITACITRUS | Início</a>
        <div class="d-flex">
            <span class="navbar-text me-3 text-white">Olá, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuário'); ?></span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-outline-secondary btn-sm" onclick="history.back()">
            <i class="fas fa-arrow-left"></i> Voltar
        </button>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Página Inicial</a></li>
                <li class="breadcrumb-item"><a href="cargos.php">Gerenciamento de Cargos</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $page_title; ?></li>
            </ol>
        </nav>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><?php echo $page_title; ?></h1>
        
        <?php if ($isEditing && $originalId > 0): ?>
             <a href="cargos_form.php?id=<?php echo $originalId; ?>&action=duplicate" 
                class="btn btn-warning btn-sm" 
                title="Criar um novo registro com base neste.">
                <i class="fas fa-copy"></i> Duplicar Cadastro
            </a>
        <?php endif; ?>
    </div>


    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="cargos_form.php" id="cargoForm">
        <input type="hidden" name="cargoId" value="<?php echo htmlspecialchars($currentFormId); ?>">

        <ul class="nav nav-tabs" id="cargoTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basicas-tab" data-bs-toggle="tab" data-bs-target="#basicas" type="button" role="tab" aria-controls="basicas" aria-selected="true">
                    <i class="fas fa-info-circle"></i> Dados Básicos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="hierarquia-tab" data-bs-toggle="tab" data-bs-target="#hierarquia" type="button" role="tab" aria-controls="hierarquia" aria-selected="false">
                    <i class="fas fa-sitemap"></i> Hierarquia e Áreas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="requisitos-tab" data-bs-toggle="tab" data-bs-target="#requisitos" type="button" role="tab" aria-controls="requisitos" aria-selected="false">
                    <i class="fas fa-list-alt"></i> Requisitos e Riscos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sinonimos-tab" data-bs-toggle="tab" data-bs-target="#sinonimos" type="button" role="tab" aria-controls="sinonimos" aria-selected="false">
                    <i class="fas fa-tags"></i> Sinônimos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="descricoes-tab" data-bs-toggle="tab" data-bs-target="#descricoes" type="button" role="tab" aria-controls="descricoes" aria-selected="false">
                    <i class="fas fa-book"></i> Descrições Longas
                </button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 p-3 mb-4" id="cargoTabsContent">
            
            <div class="tab-pane fade show active" id="basicas" role="tabpanel" aria-labelledby="basicas-tab">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cargoNome" class="form-label">Nome do Cargo *</label>
                        <input type="text" class="form-control" id="cargoNome" name="cargoNome" value="<?php echo htmlspecialchars($cargo['cargoNome'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cboId" class="form-label">CBO *</label>
                        <select class="form-select searchable-select" id="cboId" name="cboId" required>
                            <option value="">--- Selecione o CBO ---</option>
                            <?php foreach ($cbos as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['cboId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="cargoResumo" class="form-label">Resumo do Cargo</label>
                    <textarea class="form-control" id="cargoResumo" name="cargoResumo" rows="3"><?php echo htmlspecialchars($cargo['cargoResumo'] ?? ''); ?></textarea>
                    <div class="form-text">Descrição sumária das responsabilidades (máximo 250 caracteres, idealmente).</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="escolaridadeId" class="form-label">Escolaridade Mínima *</label>
                        <select class="form-select searchable-select" id="escolaridadeId" name="escolaridadeId" required>
                            <option value="">--- Selecione a Escolaridade ---</option>
                            <?php foreach ($escolaridades as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['escolaridadeId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cargoExperiencia" class="form-label">Experiência Necessária</label>
                        <input type="text" class="form-control" id="cargoExperiencia" name="cargoExperiencia" value="<?php echo htmlspecialchars($cargo['cargoExperiencia'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="hierarquia" role="tabpanel" aria-labelledby="hierarquia-tab">
                
                <h4 class="mb-3"><i class="fas fa-level-up-alt"></i> Hierarquia de Comando</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nivelHierarquicoId" class="form-label">Nível Hierárquico</label>
                        <select class="form-select searchable-select" id="nivelHierarquicoId" name="nivelHierarquicoId">
                            <option value="">--- Selecione o Nível ---</option>
                            <?php foreach ($niveisOrdenados as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['nivelHierarquicoId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><a href="nivel_hierarquico.php" target="_blank">Gerenciar Níveis</a></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="cargoSupervisorId" class="form-label">Reporta-se a (Supervisor)</label>
                        <select class="form-select searchable-select" id="cargoSupervisorId" name="cargoSupervisorId">
                            <option value="">--- Nível Superior / Nenhum ---</option>
                            <?php foreach ($cargosSupervisor as $id => $nome): 
                                if ($isEditing && (int)($originalId) === (int)$id): continue; endif; 
                            ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['cargoSupervisorId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Define a linha de comando para o Organograma.</div>
                    </div>
                </div>

                <hr>

                <h4 class="mb-3"><i class="fas fa-wallet"></i> Faixa Salarial</h4>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="faixaId" class="form-label">Faixa/Nível Salarial</label>
                        <select class="form-select searchable-select" id="faixaId" name="faixaId">
                            <option value="">--- Não Definido ---</option>
                            <?php foreach ($faixasSalariais as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['faixaId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Lembre-se de cadastrar as faixas salariais.</div>
                    </div>
                </div>
                
                <hr>

                <h4 class="mb-3"><i class="fas fa-building"></i> Áreas de Atuação</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoAreasAtuacao">
                    <i class="fas fa-plus"></i> Adicionar Área
                </button>
                <div class="card p-0 mt-2">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Área de Atuação (Hierárquica)</th>
                                    <th class="grid-action-cell text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="areasAtuacaoGridBody">
                                </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-text mt-3"><a href="areas_atuacao.php" target="_blank">Gerenciar Estrutura de Áreas</a></div>

            </div>

            <div class="tab-pane fade" id="requisitos" role="tabpanel" aria-labelledby="requisitos-tab">
                
                <h4 class="mb-3"><i class="fas fa-lightbulb"></i> Habilidades</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoHabilidades">
                    <i class="fas fa-plus"></i> Adicionar Habilidade
                </button>
                <div class="card p-0 mt-2 mb-4">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Habilidade</th>
                                    <th class="grid-action-cell text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="habilidadesGridBody">
                                </tbody>
                        </table>
                    </div>
                </div>
                
                <h4 class="mb-3"><i class="fas fa-user-tag"></i> Características</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoCaracteristicas">
                    <i class="fas fa-plus"></i> Adicionar Característica
                </button>
                <div class="card p-0 mt-2 mb-4">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Característica</th>
                                    <th class="grid-action-cell text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="caracteristicasGridBody">
                                </tbody>
                        </table>
                    </div>
                </div>

                <h4 class="mb-3"><i class="fas fa-certificate"></i> Cursos</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoCursos">
                    <i class="fas fa-plus"></i> Adicionar Curso
                </button>
                <div class="card p-0 mt-2 mb-4">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="50%">Curso</th>
                                    <th width="30%">Obrigatoriedade</th>
                                    <th class="grid-action-cell text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="cursosGridBody">
                                </tbody>
                        </table>
                    </div>
                </div>
                
                <h4 class="mb-3"><i class="fas fa-wrench"></i> Grupos de Recursos</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoRecursosGrupos">
                    <i class="fas fa-plus"></i> Adicionar Grupo de Recurso
                </button>
                <div class="card p-0 mt-2 mb-4">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Grupo de Recurso</th>
                                    <th class="grid-action-cell text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="recursosGruposGridBody">
                                </tbody>
                        </table>
                    </div>
                </div>

                <h4 class="mb-3"><i class="fas fa-radiation-alt"></i> Riscos de Exposição</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoRiscos">
                    <i class="fas fa-plus"></i> Adicionar Risco
                </button>
                <div class="card p-0 mt-2">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="30%">Tipo</th>
                                    <th>Descrição Específica</th>
                                    <th class="grid-action-cell text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="riscosGridBody">
                                </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="tab-pane fade" id="sinonimos" role="tabpanel" aria-labelledby="sinonimos-tab">
                <h4 class="mb-3"><i class="fas fa-tags"></i> Sinônimos e Nomes Alternativos</h4>
                <p class="text-muted">Inclua nomes alternativos usados para este cargo.</p>

                <div class="row mb-3">
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="sinonimoInput" placeholder="Digite um nome alternativo...">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100" id="btnAddSinonimo">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>
                </div>
                
                <div class="card p-0 mt-2">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Nome Alternativo</th>
                                    <th class="grid-action-cell text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="sinonimosGridBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="descricoes" role="tabpanel" aria-labelledby="descricoes-tab">
                
                <h4 class="mb-3"><i class="fas fa-clipboard-list"></i> Responsabilidades Detalhadas</h4>
                <div class="mb-3">
                    <textarea class="form-control" id="cargoResponsabilidades" name="cargoResponsabilidades" rows="5"><?php echo htmlspecialchars($cargo['cargoResponsabilidades'] ?? ''); ?></textarea>
                </div>
                
                <h4 class="mb-3"><i class="fas fa-layer-group"></i> Complexidade do Cargo</h4>
                <div class="mb-3">
                    <textarea class="form-control" id="cargoComplexidade" name="cargoComplexidade" rows="5"><?php echo htmlspecialchars($cargo['cargoComplexidade'] ?? ''); ?></textarea>
                </div>
                
                <h4 class="mb-3"><i class="fas fa-cloud-sun"></i> Condições Gerais</h4>
                <div class="mb-3">
                    <textarea class="form-control" id="cargoCondicoes" name="cargoCondicoes" rows="5"><?php echo htmlspecialchars($cargo['cargoCondicoes'] ?? ''); ?></textarea>
                </div>
                
            </div>
            
        </div>
        
        <button type="submit" class="btn btn-lg btn-success w-100 mt-3">
            <i class="fas fa-check-circle"></i> SALVAR CARGO
        </button>
        <?php if ($isEditing || $isDuplicating): ?>
            <a href="cargos.php" class="btn btn-link text-secondary w-100 mt-2">
                <i class="fas fa-arrow-left"></i> Voltar sem salvar
            </a>
        <?php endif; ?>

    </form>
</div>

<div class="modal fade" id="modalAssociacaoHabilidades" tabindex="-1" aria-labelledby="modalHabilidadesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalHabilidadesLabel">Adicionar Habilidade (Hard/Soft Skill)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione uma ou mais habilidades (Ctrl/Shift) para incluir na lista do cargo.</p>
                <div class="mb-3">
                    <label for="habilidadeSelect" class="form-label">Selecione a Habilidade:</label>
                    <select class="form-select searchable-select" id="habilidadeSelect" multiple="multiple" size="10" data-placeholder="Buscar Habilidade..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($habilidadesAgrupadas as $grupoNome => $habilidadesGrupo): ?>
                            <optgroup label="<?php echo htmlspecialchars($grupoNome); ?>">
                                <?php foreach ($habilidadesGrupo as $id => $nome): ?>
                                    <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nome); ?>" data-tipo="<?php echo htmlspecialchars($grupoNome); ?>">
                                        <?php echo htmlspecialchars($nome); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarHabilidade">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoCaracteristicas" tabindex="-1" aria-labelledby="modalCaracteristicasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCaracteristicasLabel">Adicionar Características</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione uma ou mais características (Ctrl/Shift) para incluir na lista do cargo.</p>
                <div class="mb-3">
                    <label for="caracteristicaSelect" class="form-label">Selecione a Característica:</label>
                    <select class="form-select searchable-select" id="caracteristicaSelect" multiple="multiple" size="10" data-placeholder="Buscar Característica..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($caracteristicas as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarCaracteristica">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoRiscos" tabindex="-1" aria-labelledby="modalRiscosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRiscosLabel">Adicionar Risco e Detalhes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione o tipo de risco e detalhe a exposição do cargo. Adicione um por vez.</p>
                <div class="mb-3">
                    <label for="riscoSelect" class="form-label">Tipo de Risco:</label>
                    <select class="form-select searchable-select" id="riscoSelect" data-placeholder="Buscar Risco..." style="width: 100%;">
                        <option value="">--- Selecione um Risco ---</option>
                        <?php foreach ($riscos as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="mb-3">
                    <label for="riscoDescricaoInput" class="form-label">Descrição da Exposição Específica</label>
                    <textarea class="form-control" id="riscoDescricaoInput" rows="3" placeholder="Ex: Exposição prolongada ao sol acima de 30ºC e poeira por deslocamentos." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarRisco">Adicionar à Lista</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoCursos" tabindex="-1" aria-labelledby="modalCursosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCursosLabel">Adicionar Curso e Detalhes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione um ou mais cursos. Os detalhes de obrigatoriedade e observação serão aplicados a todos os cursos selecionados.</p>
                <div class="mb-3">
                    <label for="cursoSelect" class="form-label">Selecione o Curso:</label>
                    <select class="form-select searchable-select" id="cursoSelect" multiple="multiple" size="8" data-placeholder="Buscar Curso..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($cursos as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="cursoObrigatorioInput">
                        <label class="form-check-label" for="cursoObrigatorioInput">Curso Obrigatório?</label>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="cursoObsInput" class="form-label">Observação (Periodicidade, Requisito)</label>
                    <textarea class="form-control" id="cursoObsInput" rows="2" placeholder="Ex: Deve ser refeito anualmente; Recomendado para certificação."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarCurso">Adicionar à Lista</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoRecursosGrupos" tabindex="-1" aria-labelledby="modalRecursosGruposLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRecursosGruposLabel">Adicionar Grupos de Recursos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione um ou mais grupos de recursos (Ctrl/Shift) utilizados pelo cargo.</p>
                <div class="mb-3">
                    <label for="recursosGruposSelect" class="form-label">Grupos de Recursos:</label>
                    <select class="form-select searchable-select" id="recursosGruposSelect" multiple="multiple" size="8" data-placeholder="Buscar Grupo..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($recursosGrupos as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarRecursosGrupos">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoAreasAtuacao" tabindex="-1" aria-labelledby="modalAreasAtuacaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAreasAtuacaoLabel">Adicionar Áreas de Atuação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione uma ou mais áreas (Ctrl/Shift) em que o cargo atua.</p>
                <div class="mb-3">
                    <label for="areasAtuacaoSelect" class="form-label">Áreas:</label>
                    <select class="form-select searchable-select" id="areasAtuacaoSelect" multiple="multiple" size="8" data-placeholder="Buscar Área..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($areasAtuacao as $id => $nomeHierarquico): ?>
                            <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nomeHierarquico); ?>">
                                <?php echo htmlspecialchars($nomeHierarquico); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarAreasAtuacao">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    
    // --- 1. VARIÁVEIS DE ESTADO (Inicializadas pelo PHP) ---
    
    const mapToSimpleState = (data) => data.map(item => ({id: item.id ? parseInt(item.id) : null, nome: item.nome, tipo: item.tipo, descricao: item.descricao, obrigatorio: item.obrigatorio, obs: item.obs}));

    let habilidadesAssociadas = mapToSimpleState(<?php echo json_encode($cargoHabilidades); ?>);
    let caracteristicasAssociadas = mapToSimpleState(<?php echo json_encode($cargoCaracteristicas); ?>);
    let riscosAssociados = mapToSimpleState(<?php echo json_encode($cargoRiscos); ?>);
    let cursosAssociados = mapToSimpleState(<?php echo json_encode($cargoCursos); ?>);
    let recursosGruposAssociados = mapToSimpleState(<?php echo json_encode($cargoRecursosGrupos); ?>);
    let areasAssociadas = mapToSimpleState(<?php echo json_encode($cargoAreas); ?>);
    let sinonimosAssociados = mapToSimpleState(<?php echo json_encode($cargoSinonimos); ?>); // NOVO


    // --- 2. FUNÇÕES GENÉRICAS E MAPAS DE ESTADO ---
    
    const entityMaps = {
        habilidade: habilidadesAssociadas, caracteristica: caracteristicasAssociadas, 
        risco: riscosAssociados, curso: cursosAssociados, 
        recursoGrupo: recursosGruposAssociados, area: areasAssociadas,
        sinonimo: sinonimosAssociados
    };

    const attachRemoveListeners = (entityName) => {
        document.querySelectorAll(`[data-entity="${entityName}"]`).forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                
                // Se o ID for numérico, usa parseInt. Se for 'new-...', usa o nome
                const isNumericId = !isNaN(itemId) && itemId !== null && itemId !== '';

                if (isNumericId) {
                    entityMaps[entityName] = entityMaps[entityName].filter(item => item.id !== parseInt(itemId));
                } else {
                    // Para Sinônimos (que podem ter IDs temporários baseados no nome)
                    const itemNome = entityMaps[entityName].find(item => item.id === null && ('new-' + item.nome.replace(/\s/g, '-') === itemId))?.nome;
                    if (itemNome) {
                        entityMaps[entityName] = entityMaps[entityName].filter(item => item.nome !== itemNome);
                    }
                }
                
                renderMaps[entityName]();
            });
        });
    };
    
    const addSimpleGridRow = (gridBodyId, itemId, itemName, inputName, isComplex = false) => {
        const gridBody = document.getElementById(gridBodyId);
        
        const existingItem = gridBody.querySelector(`tr[data-id="${itemId}"]`);
        if (existingItem) {
            return;
        }

        const newRow = gridBody.insertRow();
        newRow.setAttribute('data-id', itemId);
        
        // Input Name format
        const entityName = inputName.replace('Id', '');

        newRow.innerHTML = `
            <td>
                ${itemName}
                <input type="hidden" name="${inputName}[]" value="${itemId}">
            </td>
            <td class="text-center grid-action-cell">
                <button type="button" class="btn btn-sm btn-danger btn-remove-entity" data-id="${itemId}" data-entity="${entityName}" title="Remover">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        return newRow;
    };
    
    // --- 3. FUNÇÕES DE RENDERIZAÇÃO DE GRADES ---

    const normalizeTipo = (tipo) => {
        if (tipo === 'Hardskill' || tipo === 'Hard Skills') return 'Hard Skills';
        if (tipo === 'Softskill' || tipo === 'Soft Skills') return 'Soft Skills';
        return 'Outros Tipos';
    };

    const renderHabilidadesGrid = () => {
        const gridBody = document.getElementById('habilidadesGridBody');
        let html = '';
        
        habilidadesAssociadas.sort((a, b) => a.nome.localeCompare(b.nome)); 

        const habilidadesAgrupadas = habilidadesAssociadas.reduce((acc, item) => {
            const tipo = normalizeTipo(item.tipo); 
            if (!acc[tipo]) acc[tipo] = [];
            acc[tipo].push(item);
            return acc;
        }, {});

        const gruposOrdenados = ['Hard Skills', 'Soft Skills', 'Outros Tipos'];
        
        gruposOrdenados.forEach(tipo => {
            const grupoItens = habilidadesAgrupadas[tipo];
            
            if (grupoItens && grupoItens.length > 0) {
                html += `<tr class="table-group-separator"><td colspan="2" class="fw-bold"><i class="fas fa-tag me-2"></i> ${tipo}</td></tr>`;
                
                grupoItens.forEach(item => {
                    const itemId = item.id;
                    const itemName = item.nome;

                    html += `
                        <tr data-id="${itemId}" data-type="habilidade">
                            <td>
                                ${itemName}
                                <input type="hidden" name="habilidadeId[]" value="${itemId}">
                            </td>
                            <td class="text-center grid-action-cell">
                                <button type="button" class="btn btn-sm btn-danger btn-remove-entity" data-id="${itemId}" data-entity="habilidade" title="Remover">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
        });
        
        gridBody.innerHTML = html;
        attachRemoveListeners('habilidade');
    };
    
    const renderCaracteristicasGrid = () => {
        const gridBody = document.getElementById('caracteristicasGridBody');
        gridBody.innerHTML = '';
        caracteristicasAssociadas.forEach(item => {
            addSimpleGridRow('caracteristicasGridBody', item.id, item.nome, 'caracteristicaId');
        });
        attachRemoveListeners('caracteristica');
    };

    const renderRecursosGruposGrid = () => {
        const gridBody = document.getElementById('recursosGruposGridBody');
        gridBody.innerHTML = '';
        recursosGruposAssociados.forEach(item => {
            addSimpleGridRow('recursosGruposGridBody', item.id, item.nome, 'recursoGrupoId');
        });
        attachRemoveListeners('recursoGrupo');
    };

    const renderAreasAtuacaoGrid = () => {
        const gridBody = document.getElementById('areasAtuacaoGridBody');
        gridBody.innerHTML = '';
        areasAssociadas.forEach(item => {
            addSimpleGridRow('areasAtuacaoGridBody', item.id, item.nome, 'areaId');
        });
        attachRemoveListeners('area');
    };
    
    const renderRiscosGrid = () => {
        const gridBody = document.getElementById('riscosGridBody');
        gridBody.innerHTML = '';
        
        riscosAssociados.forEach(item => {
            const newRow = gridBody.insertRow();
            newRow.setAttribute('data-id', item.id);
            
            newRow.innerHTML = `
                <td>
                    ${item.nome}
                    <input type="hidden" name="riscoId[]" value="${item.id}">
                </td>
                <td>
                    <textarea name="riscoDescricao[]" placeholder="Descreva a exposição específica" class="form-control form-control-sm" rows="1">${item.descricao}</textarea>
                </td>
                <td class="text-center grid-action-cell">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-entity" data-id="${item.id}" data-entity="risco" title="Remover">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
        });
        attachRemoveListeners('risco');
    };

    const renderCursosGrid = () => {
        const gridBody = document.getElementById('cursosGridBody');
        gridBody.innerHTML = '';
        
        cursosAssociados.forEach(item => {
            const isObrigatorio = item.obrigatorio === true || item.obrigatorio === 1;
            const badgeClass = isObrigatorio ? 'bg-danger' : 'bg-secondary';
            
            const newRow = gridBody.insertRow();
            newRow.setAttribute('data-id', item.id);
            
            newRow.innerHTML = `
                <td>
                    ${item.nome}
                    <input type="hidden" name="cursoId[]" value="${item.id}">
                </td>
                <td>
                    <span class="badge ${badgeClass}">${isObrigatorio ? 'OBRIGATÓRIO' : 'DESEJÁVEL'}</span>
                    <small class="d-block text-muted">${item.obs || ''}</small>
                    <input type="hidden" name="cursoCargoObrigatorio[]" value="${isObrigatorio ? 1 : 0}">
                    <input type="hidden" name="cursoCargoObs[]" value="${item.obs || ''}">
                </td>
                <td class="text-center grid-action-cell">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-entity" data-id="${item.id}" data-entity="curso" title="Remover">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
        });
        attachRemoveListeners('curso');
    };
    
    // 3.7. SINÔNIMOS (NOVO)
    const renderSinonimosGrid = () => {
        const gridBody = document.getElementById('sinonimosGridBody');
        gridBody.innerHTML = '';
        
        sinonimosAssociados.forEach(item => {
            // Usa o ID do banco ou um ID temporário baseado no nome para exclusão no frontend
            const itemId = item.id || 'new-' + item.nome.replace(/\s/g, '-'); 
            const newRow = gridBody.insertRow();
            newRow.setAttribute('data-id', itemId);
            
            newRow.innerHTML = `
                <td>
                    ${item.nome}
                    <input type="hidden" name="sinonimoNome[]" value="${item.nome}">
                </td>
                <td class="text-center grid-action-cell">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-entity" data-id="${itemId}" data-entity="sinonimo" title="Remover">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
        });
        attachRemoveListeners('sinonimo');
    };

    const renderMaps = {
        habilidade: renderHabilidadesGrid, caracteristica: renderCaracteristicasGrid, 
        risco: renderRiscosGrid, curso: renderCursosGrid, 
        recursoGrupo: renderRecursosGruposGrid, area: renderAreasAtuacaoGrid,
        sinonimo: renderSinonimosGrid
    };


    // --- 4. LISTENERS DOS MODAIS E INPUTS ---
    
    // Função auxiliar para obter dados de select2 (usada nos modais)
    const getSelectedOptionsData = (selectId) => {
        const selectedValues = $(`#${selectId}`).val();
        if (!selectedValues) return [];
        
        const data = [];
        const selectElement = document.getElementById(selectId);
        const values = Array.isArray(selectedValues) ? selectedValues : [selectedValues];
        
        values.forEach(value => {
            const option = selectElement.querySelector(`option[value="${value}"]`);
            if (option) {
                data.push({
                    id: parseInt(value),
                    nome: option.getAttribute('data-nome'),
                    tipo: option.getAttribute('data-tipo')
                });
            }
        });
        return data;
    };
    
    const handleMultiSelectAssociation = (selectId, stateArray, renderFunction) => {
        const selectedItems = getSelectedOptionsData(selectId);
        let addedCount = 0;

        selectedItems.forEach(data => {
            const isDuplicate = stateArray.some(item => item.id === data.id);
            if (!isDuplicate) {
                const newItem = { id: data.id, nome: data.nome, ...(data.tipo && { tipo: data.tipo }) };
                stateArray.push(newItem);
                addedCount++;
            }
        });

        if (addedCount > 0) {
            renderFunction();
        }
    };
    
    // 4.1. HABILIDADES
    document.getElementById('btnAssociarHabilidade').onclick = function() {
        handleMultiSelectAssociation('habilidadeSelect', habilidadesAssociadas, renderHabilidadesGrid);
        $('#habilidadeSelect').val(null).trigger('change');
        bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoHabilidades')).hide();
    };
    
    // 4.2. CARACTERÍSTICAS
    document.getElementById('btnAssociarCaracteristica').onclick = function() {
        handleMultiSelectAssociation('caracteristicaSelect', caracteristicasAssociadas, renderCaracteristicasGrid);
        $('#caracteristicaSelect').val(null).trigger('change');
        bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoCaracteristicas')).hide();
    };

    // 4.3. GRUPOS DE RECURSOS
    document.getElementById('btnAssociarRecursosGrupos').onclick = function() {
        handleMultiSelectAssociation('recursosGruposSelect', recursosGruposAssociados, renderRecursosGruposGrid);
        $('#recursosGruposSelect').val(null).trigger('change');
        bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoRecursosGrupos')).hide();
    };

    // 4.4. ÁREAS DE ATUAÇÃO
    document.getElementById('btnAssociarAreasAtuacao').onclick = function() {
        handleMultiSelectAssociation('areasAtuacaoSelect', areasAssociadas, renderAreasAtuacaoGrid);
        $('#areasAtuacaoSelect').val(null).trigger('change');
        bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoAreasAtuacao')).hide();
    };

    // 4.5. RISCOS
    document.getElementById('btnAssociarRisco').onclick = function() {
        const data = getSelectedOptionsData('riscoSelect')[0];
        const descricao = document.getElementById('riscoDescricaoInput').value.trim();

        if (data && descricao) {
            const isDuplicate = riscosAssociados.some(item => item.id === data.id);
            if (!isDuplicate) {
                riscosAssociados.push({ id: data.id, nome: data.nome, descricao: descricao });
                renderRiscosGrid();
                
                document.getElementById('riscoDescricaoInput').value = '';
                $('#riscoSelect').val(null).trigger('change');
                bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoRiscos')).hide();
            } else {
                alert('Este tipo de risco já foi associado. Para editar a descrição, altere o campo na grade abaixo.');
            }
        } else {
            alert('Por favor, selecione um Risco e preencha a Descrição Específica.');
        }
    };
    
    // 4.6. CURSOS
    document.getElementById('btnAssociarCurso').onclick = function() {
        const selectedItems = getSelectedOptionsData('cursoSelect');
        const isObrigatorio = document.getElementById('cursoObrigatorioInput').checked;
        const obs = document.getElementById('cursoObsInput').value.trim();
        let addedCount = 0;

        selectedItems.forEach(data => {
            const isDuplicate = cursosAssociados.some(item => item.id === data.id);
            
            if (!isDuplicate) {
                cursosAssociados.push({
                    id: data.id,
                    nome: data.nome,
                    obrigatorio: isObrigatorio ? 1 : 0, 
                    obs: obs
                });
                addedCount++;
            }
        });

        if (addedCount > 0) {
            renderCursosGrid();
        }
        
        document.getElementById('cursoObsInput').value = '';
        document.getElementById('cursoObrigatorioInput').checked = false;
        $('#cursoSelect').val(null).trigger('change');
        bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoCursos')).hide();
    };
    
    // 4.7. SINÔNIMOS (NOVO)
    document.getElementById('btnAddSinonimo').onclick = function() {
        const input = document.getElementById('sinonimoInput');
        const nome = input.value.trim();

        if (nome) {
            const isDuplicate = sinonimosAssociados.some(item => item.nome.toLowerCase() === nome.toLowerCase());

            if (!isDuplicate) {
                // ID temporário para exclusão no frontend (se for um novo item)
                const tempId = 'new-' + nome.replace(/\s/g, '-'); 
                sinonimosAssociados.push({ id: null, nome: nome }); 
                renderSinonimosGrid();
                input.value = ''; 
            } else {
                alert('Sinônimo já adicionado.');
            }
        } else {
            alert('Digite um nome válido.');
        }
    };


    // --- 5. INICIALIZAÇÃO GERAL ---

    function initSelect2() {
        $('.searchable-select').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: "Buscar e selecionar...",
            minimumInputLength: 2, 
            dropdownParent: $('body'),
            language: {
                inputTooShort: (args) => `Digite ${args.minimum - args.input.length} ou mais caracteres para buscar.`,
            },
            templateResult: (data, container) => {
                if (data.element && data.element.closest('optgroup')) {
                    return $('<span>' + data.element.closest('optgroup').label + ' > ' + data.text + '</span>');
                }
                return data.text;
            }
        });
        
        const initModalSelect2 = (selector, parentId) => {
             $(selector).select2({ 
                theme: "bootstrap-5", 
                width: '100%', 
                placeholder: "Buscar ou selecionar...",
                dropdownParent: $(parentId),
                allowClear: true
            });
        };
        
        initModalSelect2('#habilidadeSelect', '#modalAssociacaoHabilidades');
        initModalSelect2('#caracteristicaSelect', '#modalAssociacaoCaracteristicas');
        initModalSelect2('#riscoSelect', '#modalAssociacaoRiscos');
        initModalSelect2('#cursoSelect', '#modalAssociacaoCursos');
        initModalSelect2('#recursosGruposSelect', '#modalAssociacaoRecursosGrupos');
        initModalSelect2('#areasAtuacaoSelect', '#modalAssociacaoAreasAtuacao');
    }
    
    // Renderiza dados existentes
    renderHabilidadesGrid();
    renderCaracteristicasGrid();
    renderRiscosGrid();
    renderCursosGrid();
    renderRecursosGruposGrid();
    renderAreasAtuacaoGrid();
    renderSinonimosGrid(); // NOVO
    
    // Executa Select2
    initSelect2();

    // Ativação da primeira aba
    var firstTab = document.querySelector('#basicas-tab');
    if (firstTab) {
        new bootstrap.Tab(firstTab).show();
    }
});
</script>
</body>
</html>