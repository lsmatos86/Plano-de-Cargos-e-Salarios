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
// NOVO: Habilidades agrupadas para o OPTGROUP
$habilidadesAgrupadas = getHabilidadesGrouped($pdo);
// Lookups simples (ID => Nome) para outros grids
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


// ----------------------------------------------------
// 2. BUSCA DADOS PARA EDIÇÃO OU DUPLICAÇÃO
// ----------------------------------------------------
if ($isEditing || $isDuplicating) {
    try {
        // 2.1 Busca dados principais do cargo
        $stmt = $pdo->prepare("SELECT * FROM cargos WHERE cargoId = ?");
        $stmt->execute([$cargoId]); 
        $cargo = $stmt->fetch();

        if (!$cargo) {
            $message = "Cargo não encontrado.";
            $message_type = 'danger';
            $isEditing = false;
        }

        if ($cargo) {
            // 2.2 Busca Relacionamentos N:M (AGORA BUSCA ID E NOME PARA POPULAR GRIDS)
            
            // RISCOS (COMPLEX N:M)
            $stmt = $pdo->prepare("SELECT rc.riscoId, rc.riscoDescricao FROM riscos_cargo rc WHERE rc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoRiscos = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            
            // ÁREAS DE ATUAÇÃO (SIMPLE N:M)
            $stmt = $pdo->prepare("SELECT ca.areaId, a.areaNome FROM cargos_area ca JOIN areas_atuacao a ON a.areaId = ca.areaId WHERE ca.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoAreas = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            
            // HABILIDADES (SIMPLE N:M) - AGORA BUSCA O TIPO PARA AGRUPAMENTO NA GRID
            $stmt = $pdo->prepare("SELECT hc.habilidadeId, h.habilidadeNome, h.habilidadeTipo FROM habilidades_cargo hc JOIN habilidades h ON h.habilidadeId = hc.habilidadeId WHERE hc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoHabilidades = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            
            // CARACTERÍSTICAS (SIMPLE N:M)
            $stmt = $pdo->prepare("SELECT cc.caracteristicaId, c.caracteristicaNome FROM caracteristicas_cargo cc JOIN caracteristicas c ON c.caracteristicaId = cc.caracteristicaId WHERE cc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoCaracteristicas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // CURSOS (SIMPLE N:M)
            $stmt = $pdo->prepare("SELECT curc.cursoId, cur.cursoNome FROM cursos_cargo curc JOIN cursos cur ON cur.cursoId = curc.cursoId WHERE curc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoCursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // GRUPOS DE RECURSOS (SIMPLE N:M)
            $stmt = $pdo->prepare("SELECT rgc.recursoGrupoId, rg.recursoGrupoNome FROM recursos_grupos_cargo rgc JOIN recursos_grupos rg ON rg.recursoGrupoId = rgc.recursoGrupoId WHERE rgc.cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoRecursosGrupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2.3 Lógica Específica de DUPLICAÇÃO:
            if ($isDuplicating) {
                $cargo['cargoNome'] = ($cargo['cargoNome'] ?? 'Cargo Duplicado') . ' (CÓPIA)';
                unset($cargo['cargoId']); 
            }
        }


    } catch (PDOException $e) {
        $message = "Erro ao carregar dados para " . ($isDuplicating ? 'duplicação' : 'edição') . ": " . $e->getMessage();
        $message_type = 'danger';
        $isEditing = false;
        $isDuplicating = false;
    }
}


// ----------------------------------------------------
// 3. LÓGICA DE SALVAMENTO (POST) - Transacional e Completa
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

    // Relacionamentos N:M SIMPLES (usando a nova estrutura de POST dos Grids)
    $relacionamentosSimples = [
        'cargos_area' => ['coluna' => 'areaId', 'valores' => (array)($_POST['areaId'] ?? [])],
        'habilidades_cargo' => ['coluna' => 'habilidadeId', 'valores' => (array)($_POST['habilidadeId'] ?? [])],
        'caracteristicas_cargo' => ['coluna' => 'caracteristicaId', 'valores' => (array)($_POST['caracteristicaId'] ?? [])],
        'cursos_cargo' => ['coluna' => 'cursoId', 'valores' => (array)($_POST['cursoId'] ?? [])],
        'recursos_grupos_cargo' => ['coluna' => 'recursoGrupoId', 'valores' => (array)($_POST['recursoGrupoId'] ?? [])],
    ];
    
    // Relacionamentos N:M COMPLEXOS (Riscos)
    $riscosInput = [
        'riscoId' => (array)($_POST['riscoId'] ?? []),
        'riscoDescricao' => (array)($_POST['riscoDescricao'] ?? []),
    ];
    
    // 3.2 Validação Mínima (omitido para brevidade)
    if (empty($data['cargoNome']) || $data['cboId'] <= 0 || $data['escolaridadeId'] <= 0) {
        $message = "Os campos Nome do Cargo, CBO e Escolaridade são obrigatórios.";
        $message_type = 'danger';
        // Recarrega os dados do POST para manter os campos preenchidos
        $cargo = array_merge($cargo, $_POST);
        $cargoId = $cargoIdSubmissao;
    } else {

        try {
            $pdo->beginTransaction();

            // 3.3 PREPARAÇÃO DA QUERY PRINCIPAL (UPDATE/CREATE) (omitido para brevidade)
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
            
            // 3.4 SALVAMENTO DOS RELACIONAMENTOS N:M SIMPLES (Todas as Grades Simples)
            foreach ($relacionamentosSimples as $tableName => $rel) {
                $column = $rel['coluna'];
                $valores = $rel['valores'];
                
                $pdo->prepare("DELETE FROM {$tableName} WHERE cargoId = ?")->execute([$novoCargoId]);
                
                if (!empty($valores)) {
                    $placeholders = implode(',', array_fill(0, count($valores), '(?, ?)'));
                    $insert_sql = "INSERT INTO {$tableName} (cargoId, {$column}) VALUES {$placeholders}";
                    
                    $bindings_rel = [];
                    foreach ($valores as $valorId) {
                        $bindings_rel[] = $novoCargoId;
                        $bindings_rel[] = (int)$valorId;
                    }
                    
                    $stmt_rel = $pdo->prepare($insert_sql);
                    $stmt_rel->execute($bindings_rel);
                }
            }
            
            // 3.5 SALVAMENTO DOS RELACIONAMENTOS N:M COMPLEXOS (RISCOS)
            $pdo->prepare("DELETE FROM riscos_cargo WHERE cargoId = ?")->execute([$novoCargoId]);
            if (!empty($riscosInput['riscoId'])) {
                $count_riscos = count($riscosInput['riscoId']);
                $placeholders = implode(',', array_fill(0, $count_riscos, '(?, ?, ?)'));
                $sql_risco = "INSERT INTO riscos_cargo (cargoId, riscoId, riscoDescricao) VALUES {$placeholders}";
                
                $bindings_risco = [];
                for ($i = 0; $i < $count_riscos; $i++) {
                    $bindings_risco[] = $novoCargoId;
                    $bindings_risco[] = (int)$riscosInput['riscoId'][$i];
                    $bindings_risco[] = trim($riscosInput['riscoDescricao'][$i] ?? '');
                }
                
                $stmt_risco = $pdo->prepare($sql_risco);
                $stmt_risco->execute($bindings_risco);
            }

            $pdo->commit();
            
            $message = "Cargo salvo com sucesso! ID: {$novoCargoId}";
            $message_type = 'success';
            
            // Redireciona para a versão de edição/visualização do cargo salvo
            header("Location: cargos_form.php?id={$novoCargoId}&message=" . urlencode($message) . "&type={$message_type}");
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Erro fatal ao salvar. Verifique se os dados obrigatórios ou chaves estrangeiras estão corretos. Erro: " . $e->getMessage();
            $message_type = 'danger';
            // Recarrega os dados do POST para manter os campos preenchidos
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

// --- Fim da lógica PHP ---
// ----------------------------------------------------

// ----------------------------------------------------
// 4. PREPARAÇÃO DO HTML E BOTÕES
// ----------------------------------------------------
// Função auxiliar para pré-seleção
function isSelected($id, $list) {
    return in_array((string)$id, $list) ? 'selected' : '';
}

// Carrega os Níveis Hierárquicos já ordenados 
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>
        textarea { resize: vertical; }
        .grid-header { background-color: #f8f9fa; border-top: 1px solid #dee2e6; padding-top: 10px; }
        .grid-body tr:last-child td { border-bottom: none; }
        .grid-action-cell { width: 50px; }
        .grid-risco-desc textarea { width: 100%; resize: vertical; min-height: 40px; border: 1px solid #ced4da; padding: 5px; }
        /* A classe .searchable-select será inicializada pelo Select2 */
        .searchable-select { width: 100%; } 
        /* Estilos adicionais para Select2 funcionar melhor com Bootstrap */
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
            height: 38px;
            padding: 5px 0 5px 5px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
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
                <div class="row grid-header">
                    <div class="col-10 mb-3">
                        <label for="newAreaId" class="form-label">Adicionar Área de Atuação</label>
                        <select class="form-select searchable-select" id="newAreaId" aria-label="Selecione a Área">
                            <option value="">--- Selecione uma Área ---</option>
                            <?php foreach ($areasAtuacao as $id => $nomeHierarquico): ?>
                                <option value="<?php echo $id; ?>">
                                    <?php echo htmlspecialchars($nomeHierarquico); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-2 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="addAreaBtn">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
                <div class="card p-0 mt-2">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="30%"></th> <th>Área de Atuação (Hierárquica)</th>
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
                <div class="row grid-header">
                    <div class="col-10 mb-3">
                        <label for="newHabilidadeId" class="form-label">Adicionar Habilidade</label>
                        <select class="form-select searchable-select" id="newHabilidadeId" aria-label="Selecione a Habilidade">
                            <option value="">--- Selecione uma Habilidade ---</option>
                            <?php foreach ($habilidadesAgrupadas as $grupoNome => $habilidadesGrupo): ?>
                                <optgroup label="<?php echo htmlspecialchars($grupoNome); ?>">
                                    <?php foreach ($habilidadesGrupo as $id => $nome): ?>
                                        <option value="<?php echo $id; ?>" data-group="<?php echo htmlspecialchars($grupoNome); ?>">
                                            <?php echo htmlspecialchars($nome); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-2 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="addHabilidadeBtn">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
                <div class="card p-0 mt-2 mb-4">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="30%">Tipo</th>
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
                <div class="row grid-header">
                    <div class="col-10 mb-3">
                        <label for="newCaracteristicaId" class="form-label">Adicionar Característica</label>
                        <select class="form-select searchable-select" id="newCaracteristicaId" aria-label="Selecione a Característica">
                            <option value="">--- Selecione uma Característica ---</option>
                            <?php foreach ($caracteristicas as $id => $nome): ?>
                                <option value="<?php echo $id; ?>">
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-2 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="addCaracteristicaBtn">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
                <div class="card p-0 mt-2 mb-4">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="30%"></th>
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
                <div class="row grid-header">
                    <div class="col-10 mb-3">
                        <label for="newCursoId" class="form-label">Adicionar Curso/Certificação</label>
                        <select class="form-select searchable-select" id="newCursoId" aria-label="Selecione o Curso">
                            <option value="">--- Selecione um Curso ---</option>
                            <?php foreach ($cursos as $id => $nome): ?>
                                <option value="<?php echo $id; ?>">
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-2 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="addCursoBtn">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
                <div class="card p-0 mt-2 mb-4">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="30%"></th>
                                    <th>Curso</th>
                                    <th class="grid-action-cell text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="cursosGridBody">
                                </tbody>
                        </table>
                    </div>
                </div>
                
                <h4 class="mb-3"><i class="fas fa-wrench"></i> Grupos de Recursos</h4>
                <div class="row grid-header">
                    <div class="col-10 mb-3">
                        <label for="newRecursoGrupoId" class="form-label">Adicionar Grupo de Recurso</label>
                        <select class="form-select searchable-select" id="newRecursoGrupoId" aria-label="Selecione o Grupo de Recurso">
                            <option value="">--- Selecione um Grupo ---</option>
                            <?php foreach ($recursosGrupos as $id => $nome): ?>
                                <option value="<?php echo $id; ?>">
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-2 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="addRecursoGrupoBtn">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
                <div class="card p-0 mt-2 mb-4">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="30%"></th>
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
                <div class="row grid-header mx-0 p-2">
                    <div class="col-5">
                        <select class="form-select form-select-sm searchable-select" id="newRiscoId">
                            <option value="">--- Risco ---</option>
                            <?php foreach ($riscos as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" data-name="<?php echo htmlspecialchars($nome); ?>">
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-5">
                        <input type="text" class="form-control form-control-sm" id="newRiscoDescricao" placeholder="Descrição Específica (Ex: Ruído acima de 85dB)">
                    </div>
                    <div class="col-2 text-center">
                        <button type="button" class="btn btn-primary btn-sm w-100" id="addRiscoBtn"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    
    // --- 1. CONFIGURAÇÃO INICIAL E DADOS ---
    
    // Dados de relacionamento N:M pré-carregados (convertidos para JS)
    var currentData = {
        habilidades: <?php echo json_encode($cargoHabilidades); ?>,
        caracteristicas: <?php echo json_encode($cargoCaracteristicas); ?>,
        cursos: <?php echo json_encode($cargoCursos); ?>,
        recursosGrupos: <?php echo json_encode($cargoRecursosGrupos); ?>,
        riscos: <?php echo json_encode($cargoRiscos); ?>, 
        areasAtuacao: <?php echo json_encode($cargoAreas); ?>
    };

    // Mapeamento dos nomes para IDs (Lookups simples e hierárquicos)
    var lookups = {
        riscos: <?php echo json_encode($riscos); ?>,
        habilidades: <?php echo json_encode($habilidades); ?>,
        caracteristicas: <?php echo json_encode($caracteristicas); ?>,
        cursos: <?php echo json_encode($cursos); ?>,
        recursosGrupos: <?php echo json_encode($recursosGrupos); ?>,
        areasAtuacao: <?php echo json_encode($areasAtuacao); ?>
    };
    
    // Mapeamento explícito de IDs e Nomes de Colunas para Grids Simples
    var simpleGridMaps = {
        'habilidades': {idCol: 'habilidadeId', nameCol: 'habilidadeNome'},
        'caracteristicas': {idCol: 'caracteristicaId', nameCol: 'caracteristicaNome'},
        'cursos': {idCol: 'cursoId', nameCol: 'cursoNome'},
        'recursosGrupos': {idCol: 'recursoGrupoId', nameCol: 'recursoGrupoNome'},
        'areasAtuacao': {idCol: 'areaId', nameCol: 'areaNome'}
    };
    
    // --- 2. FUNÇÕES GENÉRICAS ---

    function removeRow(event) {
        event.target.closest('tr').remove();
    }
    
    /**
     * Adiciona uma linha simples para relacionamentos N:M (apenas ID).
     */
    function addSimpleGridRow(entityName, itemId, itemName, groupName = '') {
        var gridBody = document.getElementById(entityName + 'GridBody');
        var map = simpleGridMaps[entityName];
        var inputName = map.idCol + '[]'; 
        
        // 1. Verifica se o item já foi adicionado
        var existingItem = gridBody.querySelector('tr[data-id="' + itemId + '"]');
        if (existingItem) {
            return; // Já existe, ignora na inicialização e previne na adição manual.
        }

        // 2. Adiciona a nova linha
        var newRow = gridBody.insertRow();
        newRow.setAttribute('data-id', itemId);
        
        var tipoCol = '';
        var nomeCol = '';

        // Lógica para Habilidades (Agrupamento no Grid)
        if (entityName === 'habilidades') {
            tipoCol = `<td><strong>${groupName}</strong></td>`;
            nomeCol = `<td>${itemName} <input type="hidden" name="${inputName}" value="${itemId}"></td>`;
        } else {
            // Outras grades simples (ocupam 2 colunas no HTML para alinhar com Habilidades/Riscos)
            tipoCol = `<td></td>`; // Célula vazia para alinhar
            nomeCol = `<td colspan="2">${itemName} <input type="hidden" name="${inputName}" value="${itemId}"></td>`;
        }
        
        newRow.innerHTML = `
            ${tipoCol}
            ${nomeCol}
            <td class="text-center grid-action-cell">
                <button type="button" class="btn btn-sm btn-danger remove-btn"><i class="fas fa-trash-alt"></i></button>
            </td>
        `;
        // Adiciona evento de remoção
        newRow.querySelector('.remove-btn').addEventListener('click', removeRow);
    }
    
    // --- 3. GESTÃO DE GRADES SIMPLES (Função de inicialização) ---

    function initSimpleGrid(entityName, data, selectId, buttonId) {
        var map = simpleGridMaps[entityName];
        var idCol = map.idCol;
        var nameCol = map.nameCol;
        var gridBody = document.getElementById(entityName + 'GridBody');
        
        // Inicializa com dados existentes (vindos do PHP)
        data.forEach(function(item) {
            var itemId = item[idCol];
            var itemName;
            var groupName = '';

            // Mapeamento explícito do nome e grupo (CORRIGIDO)
            if (entityName === 'areasAtuacao') {
                 // Usa o nome hierárquico completo do lookup (que é o que está no SELECT)
                 itemName = lookups.areasAtuacao[itemId] || item.areaNome; 
                 groupName = 'Área';
            } else if (entityName === 'habilidades') {
                itemName = item.habilidadeNome;
                groupName = item.habilidadeTipo.replace('skill', ' Skill'); // Hard Skill / Soft Skill
            } else {
                 itemName = item[nameCol]; // Para Cursos, Caracteristicas, Grupos de Recursos
            }
            
            // Verifica se o nome existe e faz o mapeamento do lookup
            if (itemId && itemName) { 
                 addSimpleGridRow(entityName, itemId, itemName, groupName);
            }
        });

        // Evento de Adicionar
        document.getElementById(buttonId).addEventListener('click', function() {
            var select = document.getElementById(selectId);
            var itemId = select.value;
            var itemName;
            var groupName = '';
            
            if (!itemId) {
                 alert('Selecione um item para adicionar.');
                 return;
            }

            // Mapeamento do nome de exibição (pega o texto do optgroup se for o caso)
            var selectedOption = select.options[select.selectedIndex];
            var optgroupLabel = selectedOption.closest('optgroup')?.label;
            
            if (optgroupLabel) {
                itemName = selectedOption.text;
                groupName = optgroupLabel; // Usa o label do grupo como tipo
            } else {
                itemName = selectedOption.text.replace(/--- Selecione.*---/, '').trim();
                if (entityName === 'areasAtuacao') {
                    // Para áreas, usa o nome hierárquico completo do SELECT
                    itemName = lookups.areasAtuacao[itemId]; 
                    groupName = 'Área';
                }
            }
            
            // Checagem de duplicidade no evento de adição
            var existingItem = gridBody.querySelector('tr[data-id="' + itemId + '"]');

            if (existingItem) {
                 alert(itemName + ' já foi adicionado.');
                 return;
            }
            
            addSimpleGridRow(entityName, itemId, itemName, groupName);
            select.value = ''; // Limpa o select
            
            // Força a limpeza do Select2
            $(select).val(null).trigger('change');
        });
    }

    // Inicialização de todas as grades simples
    initSimpleGrid('habilidades', currentData.habilidades, 'newHabilidadeId', 'addHabilidadeBtn');
    initSimpleGrid('caracteristicas', currentData.caracteristicas, 'newCaracteristicaId', 'addCaracteristicaBtn');
    initSimpleGrid('cursos', currentData.cursos, 'newCursoId', 'addCursoBtn');
    initSimpleGrid('recursosGrupos', currentData.recursosGrupos, 'newRecursoGrupoId', 'addRecursoGrupoBtn');
    initSimpleGrid('areasAtuacao', currentData.areasAtuacao, 'newAreaId', 'addAreaBtn');
    
    // --- 4. GESTÃO DA GRADE COMPLEXA (RISCOS) ---
    
    var riscosGridBody = document.getElementById('riscosGridBody');
    var riscoNamesMap = {};
    for (var id in lookups.riscos) {
        riscoNamesMap[id] = lookups.riscos[id];
    }

    /**
     * Adiciona uma linha para o relacionamento Complexo de Riscos.
     */
    function addRiscoRow(riscoId, riscoNome, descricao) {
        if (!riscoId) return;

        // Verifica se o risco já foi adicionado
        var existingRisco = riscosGridBody.querySelector('tr[data-id="' + riscoId + '"]');
        if (existingRisco) {
            return;
        }

        var newRow = riscosGridBody.insertRow();
        newRow.setAttribute('data-id', riscoId);
        // Usa a textarea para permitir edição direta na grade
        newRow.innerHTML = `
            <td>
                ${riscoNome}
                <input type="hidden" name="riscoId[]" value="${riscoId}">
            </td>
            <td class="grid-risco-desc">
                <textarea name="riscoDescricao[]" placeholder="Descreva a exposição específica" class="form-control form-control-sm">${descricao}</textarea>
            </td>
            <td class="text-center grid-action-cell">
                <button type="button" class="btn btn-sm btn-danger remove-btn"><i class="fas fa-trash-alt"></i></button>
            </td>
        `;
        newRow.querySelector('.remove-btn').addEventListener('click', removeRow);
    }
    
    // Carrega Riscos existentes na inicialização
    currentData.riscos.forEach(function(risco) {
        var riscoNome = riscoNamesMap[risco.riscoId] || 'ID Desconhecido';
        addRiscoRow(risco.riscoId, riscoNome, risco.riscoDescricao);
    });

    // Evento de Adicionar Risco
    document.getElementById('addRiscoBtn').addEventListener('click', function() {
        var select = document.getElementById('newRiscoId');
        var inputDesc = document.getElementById('newRiscoDescricao');
        var riscoId = select.value;
        var riscoNome = select.options[select.selectedIndex].text.replace('--- Risco ---', '').trim();
        var descricao = inputDesc.value.trim();

        if (riscoId) {
            // A checagem de duplicidade é feita dentro de addRiscoRow
            addRiscoRow(riscoId, riscoNome, descricao);
            select.value = ''; // Limpa o select
            inputDesc.value = ''; // Limpa a descrição
        } else {
            alert('Selecione um Tipo de Risco.');
        }
        
        // Força a limpeza do Select2
        $(select).val(null).trigger('change');
    });

    // --- 5. Ativação da primeira aba ---
    var firstTab = document.querySelector('#basicas-tab');
    if (firstTab) {
        new bootstrap.Tab(firstTab).show();
    }
    
    // --- 6. Ativação de Seletor com Busca (Select2) ---
    
    if (typeof jQuery !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
        $('.searchable-select').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: "Buscar e selecionar...",
            minimumInputLength: 2, 
            language: {
                // Ajusta a mensagem de "Digite 2 ou mais caracteres" para Select2 (Requisito)
                inputTooShort: function(args) {
                    var remainingChars = args.minimum - args.input.length;
                    return "Digite " + remainingChars + " ou mais caracteres para buscar.";
                }
            },
            templateResult: function (data, container) {
                // Se for um optgroup (Habilidades), exibe o agrupamento com o nome do item
                if (data.element && data.element.closest('optgroup')) {
                    // Adiciona o nome do grupo ao resultado para seletos que usam optgroup
                    return $('<span>' + data.element.closest('optgroup').label + ' > ' + data.text + '</span>');
                }
                return data.text;
            }
        });
    }
});
</script>
</body>
</html>