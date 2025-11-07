<?php
// Arquivo: views/cargos_form.php (Formulário de Cadastro e Edição de Cargos)

// INCLUSÕES CRÍTICAS: Ordem FINAL e ROBUSTA para resolver classes, constantes e funções.

// 1. Iniciar a sessão (DEVE ser a primeira instrução PHP para evitar "headers already sent")
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Autoload do Composer (Resolve classes com namespace, ex: App\Core\Database)
require_once '../vendor/autoload.php'; 

// 3. Configurações (Define constantes de DB, ex: DB_HOST, usadas pelas classes/funções)
require_once '../config.php';

// 4. Funções (Define getDbConnection e isUserLoggedIn. A função deve estar definida aqui.)
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
// AJUSTE: Renomeado cboNome para cboCod na chamada da função
$cbos = getLookupData($pdo, 'cbos', 'cboId', 'cboCod', 'cboTituloOficial'); 
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
$cargoSinonimos = [];


// ----------------------------------------------------
// 2. BUSCA DADOS PARA EDIÇÃO OU DUPLICAÇÃO
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

            // RISCOS (COMPLEX N:M): Busca ID, Nome, Descricao
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
        'cboId' => trim($_POST['cboId'] ?? 0), 
        'cargoResumo' => trim($_POST['cargoResumo'] ?? null),
        'escolaridadeId' => (int)($_POST['escolaridadeId'] ?? 0), 
        'cargoExperiencia' => trim($_POST['cargoExperiencia'] ?? null),
        'cargoCondicoes' => trim($_POST['cargoCondicoes'] ?? null), 
        'cargoComplexidade' => trim($_POST['cargoComplexidade'] ?? null),
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

    $sinonimosInput = (array)($_POST['sinonimoNome'] ?? []); // Sinônimos
    
    if (empty($data['cargoNome']) || empty($data['cboId']) || $data['escolaridadeId'] <= 0) {
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
                        $stmt_rel->execute([$novoCargoId, $valorId]);
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
                // Itera sobre o array de IDs de cursos
                for ($i = 0; $i < count($cursosInput['cursoId']); $i++) {
                    $obrigatorio = (int)($cursosInput['cursoCargoObrigatorio'][$i] ?? 0);
                    $obs = $cursosInput['cursoCargoObs'][$i] ?? '';
                    $stmt_curso->execute([$novoCargoId, (int)$cursosInput['cursoId'][$i], $obrigatorio, $obs]);
                }
            }
            
            // 3.7 SALVAMENTO DOS SINÔNIMOS
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

// ----------------------------------------------------
// 4. PREPARAÇÃO DOS DADOS JS (Global Scope)
// ----------------------------------------------------
?>

<script>
    // Função utilitária para garantir que IDs numéricos sejam inteiros (melhor prática)
    // Isso resolve o problema de incompatibilidade de tipo no JS sem remapear a estrutura.
    const normalizeState = (data) => data.map(item => {
        // Tenta converter ID para inteiro se for string numérica e não tiver o prefixo 'new-' (para sinônimos temporários)
        if (item.id && !isNaN(item.id) && typeof item.id === 'string' && !String(item.id).startsWith('new-')) {
            item.id = parseInt(item.id);
        }
        return item;
    });

    // Inicializa as variáveis globais diretamente com o estado do PHP
    window.habilidadesAssociadas = normalizeState(<?php echo json_encode($cargoHabilidades); ?>);
    window.caracteristicasAssociadas = normalizeState(<?php echo json_encode($cargoCaracteristicas); ?>);
    window.riscosAssociados = normalizeState(<?php echo json_encode($cargoRiscos); ?>);
    window.cursosAssociados = normalizeState(<?php echo json_encode($cargoCursos); ?>);
    window.recursosGruposAssociados = normalizeState(<?php echo json_encode($cargoRecursosGrupos); ?>);
    window.areasAssociadas = normalizeState(<?php echo json_encode($cargoAreas); ?>);
    window.sinonimosAssociados = normalizeState(<?php echo json_encode($cargoSinonimos); ?>);
</script>

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
        .grid-action-cell { width: 80px; } 
        .grid-risco-desc textarea { width: 100%; resize: vertical; min-height: 40px; border: 1px solid #ced4da; padding: 5px; }
        .table-group-separator { background-color: #e9ecef; }
        .grid-container { max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px; }
        /* Estilos para Select2 no modal */
        .select2-container--bootstrap-5 .select2-dropdown { z-index: 1060; }
        .form-control-sm { min-height: calc(1.5em + 0.5rem + 2px); }
    </style>
</head>
<body>

<?php 
// CORREÇÃO: Substitui o navbar hardcoded pela inclusão do navbar padrão.
$root_path = '../'; 
require_once $root_path . 'includes/navbar.php'; 
?>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="cargos.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar para Cargos
        </a>
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
    <?php endif; ?> <form method="POST" action="cargos_form.php" id="cargoForm">
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
                                <option value="<?php echo $id; ?>" <?php echo (isset($cargo['cboId']) && $cargo['cboId'] == $id) ? 'selected' : ''; ?>>
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
                                    <th width="35%">Curso</th>
                                    <th width="50%">Obrigatoriedade e Observação</th>
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

<div class="modal fade" id="modalEdicaoHabilidade" tabindex="-1" aria-labelledby="modalEdicaoHabilidadeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEdicaoHabilidadeLabel">Editar Habilidade: <span id="habilidadeEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="habilidadeEditId">
                <div class="mb-3">
                    <label for="habilidadeEditNomeInput" class="form-label">Nome da Habilidade</label>
                    <input type="text" class="form-control" id="habilidadeEditNomeInput" readonly>
                </div>
                <div class="mb-3">
                    <label for="habilidadeEditTipo" class="form-label">Tipo da Habilidade</label>
                    <input type="text" class="form-control" id="habilidadeEditTipo" readonly>
                </div>
                <div class="alert alert-warning">
                    Para trocar a Habilidade, você deve remover a atual e adicionar a nova. Este modal só permite ver os detalhes.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoCaracteristica" tabindex="-1" aria-labelledby="modalEdicaoCaracteristicaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEdicaoCaracteristicaLabel">Editar Característica: <span id="caracteristicaEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="caracteristicaEditId">
                <div class="mb-3">
                    <label for="caracteristicaEditNomeInput" class="form-label">Nome da Característica</label>
                    <input type="text" class="form-control" id="caracteristicaEditNomeInput" readonly>
                </div>
                <div class="alert alert-warning">
                    Para trocar a Característica, você deve remover a atual e adicionar a nova. Este modal só permite ver os detalhes.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoRecursoGrupo" tabindex="-1" aria-labelledby="modalEdicaoRecursoGrupoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEdicaoRecursoGrupoLabel">Editar Grupo de Recurso: <span id="recursoGrupoEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="recursoGrupoEditId">
                <div class="mb-3">
                    <label for="recursoGrupoEditNomeInput" class="form-label">Nome do Grupo de Recurso</label>
                    <input type="text" class="form-control" id="recursoGrupoEditNomeInput" readonly>
                </div>
                 <div class="alert alert-warning">
                    Para trocar o Grupo de Recurso, você deve remover o atual e adicionar o novo. Este modal só permite ver os detalhes.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoCurso" tabindex="-1" aria-labelledby="modalEdicaoCursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEdicaoCursoLabel">Editar Curso: <span id="cursoEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cursoEditId">
                 <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="cursoEditObrigatorio">
                        <label class="form-check-label" for="cursoEditObrigatorio">Curso Obrigatório?</label>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="cursoEditObs" class="form-label">Observação (Periodicidade, Requisito)</label>
                    <textarea class="form-control" id="cursoEditObs" rows="3" placeholder="Ex: Deve ser refeito anualmente; Recomendado para certificação."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info text-white" id="btnSalvarEdicaoCurso">Salvar Edição</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoRisco" tabindex="-1" aria-labelledby="modalEdicaoRiscoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEdicaoRiscoLabel">Editar Risco: <span id="riscoEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="riscoEditId">
                <div class="mb-3">
                    <label for="riscoEditDescricao" class="form-label">Descrição da Exposição Específica</label>
                    <textarea class="form-control" id="riscoEditDescricao" rows="4" placeholder="Ex: Exposição prolongada ao sol acima de 30ºC e poeira por deslocamentos." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info text-white" id="btnSalvarEdicaoRisco">Salvar Edição</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../scripts/cargos_form.js"></script>
</body>
</html>