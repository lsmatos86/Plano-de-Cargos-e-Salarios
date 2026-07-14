<?php
// Arquivo: views/cargos_form.php (VIEW: Arquivo Completo e Unificado Sem Cortes)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'unlock') {
    header('Content-Type: application/json');
    try {
        $pdoAjax = \App\Core\Database::getConnection();
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $userIdToCheck = null;

        if (empty($email)) {
            $userIdToCheck = $_SESSION['usuario_id'] ?? 0;
        } else {
            $stmt = $pdoAjax->prepare("SELECT usuarioId, senha, ativo FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $userCheck = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($userCheck && password_verify($senha, $userCheck['senha']) && $userCheck['ativo'] == 1) {
                $userIdToCheck = $userCheck['usuarioId'];
            } else {
                echo json_encode(['success' => false, 'message' => 'E-mail ou senha do administrador incorretos.']);
                exit;
            }
        }

        if ($userIdToCheck > 0) {
            if (empty($email)) {
                $stmt = $pdoAjax->prepare("SELECT senha FROM usuarios WHERE usuarioId = ? AND ativo = 1");
                $stmt->execute([$userIdToCheck]);
                $currentUser = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (!$currentUser || !password_verify($senha, $currentUser['senha'])) {
                    echo json_encode(['success' => false, 'message' => 'A sua senha está incorreta.']);
                    exit;
                }
            }

            $stmtPerm = $pdoAjax->prepare("
                SELECT COUNT(*) FROM user_roles ur
                JOIN role_permissions rp ON ur.roleId = rp.roleId
                JOIN permissions p ON rp.permissionId = p.permissionId
                WHERE ur.usuarioId = ? AND p.permissionName IN ('cargos:edit', 'cadastros:manage')
            ");
            $stmtPerm->execute([$userIdToCheck]);
            $hasPerm = $stmtPerm->fetchColumn() > 0;
            
            if ($userIdToCheck == 1) $hasPerm = true;

            if ($hasPerm) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Este utilizador não possui permissão de Administrador para desbloquear.']);
            }
        } else {
             echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
        }
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro interno de servidor.']);
    }
    exit;
}

// Inicializa variáveis
$message = '';
$message_type = '';

// Variáveis de Controle
$originalId = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$isDuplicating = $action === 'duplicate' && $originalId > 0;
$isEditing = !$isDuplicating && $originalId > 0;
$currentFormId = $isEditing ? $originalId : 0;
$cargoId = $originalId;

// ======================================================
// Definições de Página para o header.php
// ======================================================
$page_title = $isDuplicating ? 'Duplicar Cargo (Novo Registro)' : ($isEditing ? 'Editar Cargo' : 'Novo Cargo');
$root_path = '../'; 
$page_title = "Carregando..."; 
$breadcrumb_items = [
    'Dashboard' => $root_path . 'index.php',
    'Gerenciamento de Cargos' => 'cargos.php',
    $page_title => null // Página ativa
];

// ----------------------------------------------------
// 1. CARREGAMENTO DOS LOOKUPS MESTRES (MANTIDO)
// ----------------------------------------------------
$lookupRepo = new LookupRepository();
$habilidadeRepo = new HabilidadeRepository();
$areaRepo = new AreaRepository();
$cargoRepo = new CargoRepository(); 

// Lookups
$cbos = array_column($lookupRepo->findCbos(), 'display_name', 'cboId');
$escolaridades = array_column($lookupRepo->findEscolaridades(), 'escolaridadeTitulo', 'escolaridadeId');
$habilidadesAgrupadas = $habilidadeRepo->getGroupedLookup();

// Cria um mapa plano que inclui o 'tipo' para repopulamento pós-erro
$habilidadesMap = [];
foreach ($habilidadesAgrupadas as $grupoNome => $habilidadesGrupo) {
    foreach ($habilidadesGrupo as $id => $nome) {
        $habilidadesMap[(int)$id] = [
            'nome' => $nome, 
            'tipo' => $grupoNome
        ];
    }
}

$habilidades = array_column($lookupRepo->findHabilidades(), 'nome', 'id');
$caracteristicas = array_column($lookupRepo->findCaracteristicas(), 'nome', 'id');
$riscos = array_column($lookupRepo->findRiscos(), 'nome', 'id');
$cursos = array_column($lookupRepo->findCursos(), 'nome', 'id');
$recursosGrupos = array_column($lookupRepo->findRecursosGrupos(), 'nome', 'id');
$faixasSalariais = array_column($lookupRepo->findFaixas(), 'faixaNivel', 'faixaId');
$cargosSupervisor = array_column($lookupRepo->findCargosForSelect(), 'nome', 'id');
$areasAtuacao = $areaRepo->getHierarchyLookup(); 

// Níveis Hierárquicos
$niveisHierarquicosData = $lookupRepo->findNivelHierarquico();
$niveisOrdenados = [];
foreach ($niveisHierarquicosData as $n) {
    $niveisOrdenados[$n['nivelId']] = $n['nivelOrdem'] . 'º - ' . $n['tipoHierarquiaNome'] . ' (' . $n['nivelNome'] . ')';
}

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
        $cargoData = $cargoRepo->findFormData($cargoId);

        if ($cargoData) {
            $cargo = $cargoData['cargo'];
            $cargoSinonimos = $cargoData['sinonimos'];
            $cargoRiscos = $cargoData['riscos'];
            $cargoAreas = $cargoData['areas'];
            $cargoHabilidades = $cargoData['habilidades'];
            $cargoCaracteristicas = $cargoData['caracteristicas'];
            $cargoCursos = $cargoData['cursos'];
            $cargoRecursosGrupos = $cargoData['recursos_grupos'];

            if ($isDuplicating) {
                $cargo['cargoNome'] = ($cargo['cargoNome'] ?? 'Cargo Duplicado') . ' (CÓPIA)';
                unset($cargo['cargoId']);
            }
        } else {
            $message = "Cargo não encontrado.";
            $message_type = 'danger';
            $isEditing = false;
        }

    } catch (Exception $e) { 
        $message = "Erro ao carregar dados: " . $e->getMessage();
        $message_type = 'danger';
    }
}


// ----------------------------------------------------
// 3. LÓGICA DE SALVAMENTO (POST)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cargoNome'])) {
    
    try {
        $novoCargoId = $cargoRepo->save($_POST);
        $message = "Cargo salvo com sucesso! ID: {$novoCargoId}";
        $message_type = 'success';
        // Redireciona para o formulário no modo de edição do item recém-salvo
        header("Location: cargos_form.php?id={$novoCargoId}&message=" . urlencode($message) . "&type={$message_type}");
        exit;

    } catch (Exception $e) {
        $message = "Erro fatal ao salvar. Erro: " . $e->getMessage();
        $message_type = 'danger';
        
        // Repopulamento mais robusto com os lookups corretos
        $cargo = array_merge($cargo, $_POST);
        $currentFormId = (int)($_POST['cargoId'] ?? 0);
        
        // Repopulando listas de relacionamento (Aplicando (int) CAST para garantir chave correta)
        $cargoAreas = array_map(fn($id) => ['id' => (int)$id, 'nome' => $areasAtuacao[(int)$id] ?? 'N/A'], $_POST['areaId'] ?? []);
        
        $cargoHabilidades = array_map(function($id) use ($habilidadesMap) {
            $id = (int)$id;
            $data = $habilidadesMap[$id] ?? ['nome' => 'N/A', 'tipo' => 'N/A'];
            return [
                'id' => $id, 
                'nome' => $data['nome'], 
                'tipo' => $data['tipo'] 
            ];
        }, $_POST['habilidadeId'] ?? []);
        
        $cargoCaracteristicas = array_map(fn($id) => ['id' => (int)$id, 'nome' => $caracteristicas[(int)$id] ?? 'N/A'], $_POST['caracteristicaId'] ?? []);
        $cargoRecursosGrupos = array_map(fn($id) => ['id' => (int)$id, 'nome' => $recursosGrupos[(int)$id] ?? 'N/A'], $_POST['recursoGrupoId'] ?? []);
        $cargoSinonimos = array_map(fn($nome) => ['id' => $nome, 'nome' => $nome], $_POST['sinonimoNome'] ?? []);
        
        // Repopulando listas complexas
        if(isset($_POST['riscoId'])) {
            foreach($_POST['riscoId'] as $index => $id) {
                $id = (int)$id;
                $cargoRiscos[] = [
                    'id' => $id, 
                    'nome' => $riscos[$id] ?? 'N/A', 
                    'descricao' => $_POST['riscoDescricao'][$index] ?? ''
                ];
            }
        }
        if(isset($_POST['cursoId'])) {
            foreach($_POST['cursoId'] as $index => $id) {
                $id = (int)$id;
                $cargoCursos[] = [
                    'id' => $id, 
                    'nome' => $cursos[$id] ?? 'N/A', 
                    'obrigatorio' => (bool)($_POST['cursoCargoObrigatorio'][$index] ?? 0), 
                    'obs' => $_POST['cursoCargoObs'][$index] ?? ''
                ];
            }
        }
    }
}

// Mensagens após redirecionamento
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// ----------------------------------------------------
// 4. PREPARAÇÃO DOS DADOS JS (Global Scope) - CORREÇÃO FINAL
// ----------------------------------------------------

// Adiciona os links CSS e JS específicos desta página ANTES de fechar o </head>
$extra_head_content = '
    <script>
        // Usando o operador ?? [] para garantir que as variáveis sejam arrays JSON válidos
        window.habilidadesAssociadas = ' . json_encode($cargoHabilidades ?? []) . ';
        window.caracteristicasAssociadas = ' . json_encode($cargoCaracteristicas ?? []) . ';
        window.riscosAssociados = ' . json_encode($cargoRiscos ?? []) . ';
        window.cursosAssociados = ' . json_encode($cargoCursos ?? []) . ';
        window.recursosGruposAssociados = ' . json_encode($cargoRecursosGrupos ?? []) . ';
        window.areasAssociadas = ' . json_encode($cargoAreas ?? []) . ';
        window.sinonimosAssociados = ' . json_encode($cargoSinonimos ?? []) . ';
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        textarea { resize: vertical; }
        .grid-header { background-color: #f8f9fa; border-top: 1px solid #dee2e6; padding-top: 10px; }
        .grid-body tr:last-child td { border-bottom: none; }
        .grid-action-cell { width: 80px; } 
        .grid-risco-desc textarea { width: 100%; resize: vertical; min-height: 40px; border: 1px solid #ced4da; padding: 5px; }
        .table-group-separator { background-color: #e9ecef; }
        .grid-container { max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px; }
        .select2-container--bootstrap-5 .select2-dropdown { z-index: 1060; }
        .form-control-sm { min-height: calc(1.5em + 0.5rem + 2px); }
    </style>
';

//
include '../includes/header.php';

// ======================================================
// AJUSTE: O <nav> manual foi REMOVIDO
// ======================================================
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">
        <?php if ($isEditing && isset($cargo['cargoNome'])): ?>
            <?php echo htmlspecialchars($originalId); ?> - <?php echo htmlspecialchars($cargo['cargoNome']); ?>
            <small class="text-primary fw-normal ms-2">
                (editando)
                <i class="fas fa-pencil-alt ms-1"></i>
            </small>
        <?php else: ?>
            <?php echo $page_title; ?>
        <?php endif; ?>
    </h1>
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
                <button class="nav-link" id="remuneracao-tab" data-bs-toggle="tab" data-bs-target="#remuneracao" type="button" role="tab" aria-controls="remuneracao" aria-selected="false">
                    <i class="fas fa-money-bill-wave"></i> Remuneração e Piso
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

            <div class="tab-pane fade" id="remuneracao" role="tabpanel" aria-labelledby="remuneracao-tab">
                <h4 class="mb-3"><i class="fas fa-wallet text-success"></i> Enquadramento na Matriz Salarial</h4>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="faixaId" class="form-label">Faixa/Nível Salarial (Padrão)</label>
                        <select class="form-select searchable-select" id="faixaId" name="faixaId">
                            <option value="">--- Não Definido ---</option>
                            <?php foreach ($faixasSalariais as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['faixaId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <hr>
                <h4 class="mb-3"><i class="fas fa-balance-scale text-primary"></i> Piso Salarial Legal / Acordo Sindical</h4>
                <div class="alert alert-light border">
                    <div class="form-check form-switch fs-5 mb-3">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="tem_piso_salarial" name="tem_piso_salarial" value="1" <?php echo (!empty($cargo['tem_piso_salarial'])) ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold" for="tem_piso_salarial">Este cargo possui um Piso Salarial obrigatório?</label>
                    </div>
                    <div id="blocoPisoSalarial" style="<?php echo (!empty($cargo['tem_piso_salarial'])) ? 'display: block;' : 'display: none;'; ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="piso_valor" class="form-label text-danger fw-bold">Valor do Piso Legal (R$)</label>
                                <input type="number" step="0.01" class="form-control" id="piso_valor" name="piso_valor" value="<?php echo htmlspecialchars($cargo['piso_valor'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="piso_lei_numero" class="form-label">Nº da Lei / CCT</label>
                                <input type="text" class="form-control" id="piso_lei_numero" name="piso_lei_numero" value="<?php echo htmlspecialchars($cargo['piso_lei_numero'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="piso_data_base" class="form-label">Data-Base (Mês do Reajuste)</label>
                                <input type="date" class="form-control" id="piso_data_base" name="piso_data_base" value="<?php echo htmlspecialchars($cargo['piso_data_base'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="requisitos" role="tabpanel" aria-labelledby="requisitos-tab">
                <h4 class="mb-3"><i class="fas fa-lightbulb"></i> Habilidades</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoHabilidades">
                    <i class="fas fa-plus"></i> Adicionar Habilidade
                </button>
                <div class="card p-0 mb-4">
                    <table class="table table-sm mb-0">
                        <tbody id="habilidadesGridBody"></tbody>
                    </table>
                </div>

                <h4 class="mb-3"><i class="fas fa-user-tag"></i> Características</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoCaracteristicas">
                    <i class="fas fa-plus"></i> Adicionar Característica
                </button>
                <div class="card p-0 mb-4">
                    <table class="table table-sm mb-0">
                        <tbody id="caracteristicasGridBody"></tbody>
                    </table>
                </div>

                <h4 class="mb-3"><i class="fas fa-certificate"></i> Cursos</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoCursos">
                    <i class="fas fa-plus"></i> Adicionar Curso
                </button>
                <div class="card p-0 mb-4">
                    <table class="table table-sm mb-0">
                        <tbody id="cursosGridBody"></tbody>
                    </table>
                </div>
                
                <h4 class="mb-3"><i class="fas fa-wrench"></i> Grupos de Recursos</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoRecursosGrupos">
                    <i class="fas fa-plus"></i> Adicionar Grupo
                </button>
                <div class="card p-0 mb-4">
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

                <h4 class="mb-3"><i class="fas fa-radiation-alt"></i> Riscos de Exposição</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoRiscos">
                    <i class="fas fa-plus"></i> Adicionar Risco
                </button>
                <div class="card p-0">
                    <table class="table table-sm mb-0">
                        <tbody id="riscosGridBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="sinonimos" role="tabpanel" aria-labelledby="sinonimos-tab">
                <h4 class="mb-3"><i class="fas fa-tags"></i> Sinônimos</h4>
                <div class="row mb-3">
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="sinonimoInput" placeholder="Digite um nome alternativo...">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100" id="btnAddSinonimo">Adicionar</button>
                    </div>
                </div>
                <div class="card p-0">
                    <table class="table table-sm mb-0">
                        <tbody id="sinonimosGridBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="descricoes" role="tabpanel" aria-labelledby="descricoes-tab">
                <h4 class="mb-3">Responsabilidades Detalhadas</h4>
                <textarea class="form-control mb-3" id="cargoResponsabilidades" name="cargoResponsabilidades" rows="4"><?php echo htmlspecialchars($cargo['cargoResponsabilidades'] ?? ''); ?></textarea>
                <h4>Complexidade do Cargo</h4>
                <textarea class="form-control mb-3" id="cargoComplexidade" name="cargoComplexidade" rows="4"><?php echo htmlspecialchars($cargo['cargoComplexidade'] ?? ''); ?></textarea>
                <h4>Condições Gerais</h4>
                <textarea class="form-control" id="cargoCondicoes" name="cargoCondicoes" rows="4"><?php echo htmlspecialchars($cargo['cargoCondicoes'] ?? ''); ?></textarea>
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

<div class="modal fade" id="modalEdicaoCurso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Editar Curso: <span id="cursoEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cursoEditId">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="cursoEditObrigatorio">
                    <label class="form-check-label" for="cursoEditObrigatorio">Curso Obrigatório?</label>
                </div>
                <div class="mb-3">
                    <label for="cursoEditObs" class="form-label">Observação</label>
                    <textarea class="form-control" id="cursoEditObs" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info text-white" id="btnSalvarEdicaoCurso">Salvar Edição</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoRisco" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Editar Risco: <span id="riscoEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="riscoEditId">
                <div class="mb-3">
                    <label for="riscoEditDescricao" class="form-label">Descrição da Exposição Específica</label>
                    <textarea class="form-control" id="riscoEditDescricao" rows="4" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info text-white" id="btnSalvarEdicaoRisco">Salvar Edição</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoHabilidade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalhes da Habilidade: <span id="habilidadeEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="habilidadeEditId">
                <div class="mb-3">
                    <label class="form-label">Nome da Habilidade</label>
                    <input type="text" class="form-control" id="habilidadeEditNomeInput" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <input type="text" class="form-control" id="habilidadeEditTipo" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoCaracteristica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalhes da Característica: <span id="caracteristicaEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="caracteristicaEditId">
                <div class="mb-3">
                    <label class="form-label">Nome da Característica</label>
                    <input type="text" class="form-control" id="caracteristicaEditNomeInput" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoRecursoGrupo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalhes do Recurso: <span id="recursoGrupoEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="recursoGrupoEditId">
                <div class="mb-3">
                    <label class="form-label">Grupo de Recurso</label>
                    <input type="text" class="form-control" id="recursoGrupoEditNomeInput" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalJustificativaAlteracao" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-info shadow">
            <div class="modal-header bg-info text-white fw-bold">
                <h5 class="modal-title"><i class="fas fa-history"></i> Justificativa do Ajuste</h5>
            </div>
            <div class="modal-body py-3">
                <div class="alert alert-warning small py-2 mb-3">
                    <i class="fas fa-exclamation-circle"></i> <strong>Atenção:</strong> Este cargo já se encontra homologado, revisado e ativo. Qualquer alteração gerará uma notificação no log de auditoria.
                </div>
                <label for="txtJustificativaModal" class="form-label small fw-bold text-muted">Informe detalhadamente o motivo destas alterações para fins de relatório:</label>
                <textarea class="form-control" id="txtJustificativaModal" rows="3" placeholder="Ex: Ajuste de nomenclatura do CBO e refinamento das Hard Skills após auditoria interna." required></textarea>
                <div id="erroJustificativaModal" class="text-danger small mt-1 fw-bold" style="display:none;">Por favor, insira uma justificativa válida de pelo menos 10 caracteres.</div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar e Revisar</button>
                <button type="button" class="btn btn-success btn-sm fw-bold" id="btnConfirmarSalvarComJustificativa">
                    <i class="fas fa-save"></i> Confirmar e Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoHabilidades" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-success text-white"><h5>Habilidades</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><select class="form-select" id="habilidadeSelect" multiple="multiple"><?php foreach ($habilidadesAgrupadas as $gn => $hg): ?><optgroup label="<?php echo $gn; ?>"><?php foreach ($hg as $id => $n): ?><option value="<?php echo $id; ?>" data-nome="<?php echo $n; ?>" data-tipo="<?php echo $gn; ?>"><?php echo $n; ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button><button type="button" class="btn btn-success" id="btnAssociarHabilidade">Adicionar</button></div></div></div></div>
<div class="modal fade" id="modalAssociacaoCaracteristicas" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-success text-white"><h5>Características</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><select class="form-select" id="caracteristicaSelect" multiple="multiple"><?php foreach ($caracteristicas as $id => $n): ?><option value="<?php echo $id; ?>" data-nome="<?php echo $n; ?>"><?php echo $n; ?></option><?php endforeach; ?></select></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button><button type="button" class="btn btn-success" id="btnAssociarCaracteristica">Adicionar</button></div></div></div></div>
<div class="modal fade" id="modalAssociacaoRiscos" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-success text-white"><h5>Riscos</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><select class="form-select mb-3" id="riscoSelect"><?php foreach ($riscos as $id => $n): ?><option value="<?php echo $id; ?>" data-nome="<?php echo $n; ?>"><?php echo $n; ?></option><?php endforeach; ?></select><textarea class="form-control" id="riscoDescricaoInput" rows="2" placeholder="Descrição..."></textarea></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button><button type="button" class="btn btn-success" id="btnAssociarRisco">Adicionar</button></div></div></div></div>
<div class="modal fade" id="modalAssociacaoCursos" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-success text-white"><h5>Cursos</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><select class="form-select mb-3" id="cursoSelect" multiple="multiple"><?php foreach ($cursos as $id => $n): ?><option value="<?php echo $id; ?>" data-nome="<?php echo $n; ?>"><?php echo $n; ?></option><?php endforeach; ?></select><div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="cursoObrigatorioInput"><label class="form-check-label" for="cursoObrigatorioInput">Obrigatório?</label></div><textarea class="form-control" id="cursoObsInput" rows="2" placeholder="Observações..."></textarea></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button><button type="button" class="btn btn-success" id="btnAssociarCurso">Adicionar</button></div></div></div></div>
<div class="modal fade" id="modalAssociacaoRecursosGrupos" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-success text-white"><h5>Recursos</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><select class="form-select" id="recursosGruposSelect" multiple="multiple"><?php foreach ($recursosGrupos as $id => $n): ?><option value="<?php echo $id; ?>" data-nome="<?php echo $n; ?>"><?php echo $n; ?></option><?php endforeach; ?></select></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button><button type="button" class="btn btn-success" id="btnAssociarRecursosGrupos">Adicionar</button></div></div></div></div>
<div class="modal fade" id="modalAssociacaoAreasAtuacao" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-success text-white"><h5>Áreas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><select class="form-select" id="areasAtuacaoSelect" multiple="multiple"><?php foreach ($areasAtuacao as $id => $n): ?><option value="<?php echo $id; ?>" data-nome="<?php echo $n; ?>"><?php echo $n; ?></option><?php endforeach; ?></select></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button><button type="button" class="btn btn-success" id="btnAssociarAreasAtuacao">Adicionar</button></div></div></div></div>
<div class="modal fade" id="modalNavegacaoInteligente" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-warning"><div class="modal-header bg-warning text-dark"><h5>Alterações Pendentes</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">Deseja descartar as alterações realizadas?</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ficar</button><button type="button" class="btn btn-danger" id="btnConfirmarNavegacao">Avançar</button></div></div></div></div>
<div class="modal fade" id="modalDesbloqueioSenha" tabindex="-1" data-bs-backdrop="static" aria-hidden="true"><div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content border-danger"><div class="modal-header bg-danger text-white"><h5>Autorizar Edição</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><?php if ($temPermissaoEdicao): ?><p class="small text-muted mb-3">Confirme a palavra-passe:</p><input type="hidden" id="emailDesbloqueioInput" value=""><?php else: ?><p class="small text-muted mb-3">Insira as credenciais do Administrador:</p><div class="mb-2"><input type="email" class="form-control text-center" id="emailDesbloqueioInput" placeholder="E-mail"></div><?php endif; ?><div><input type="password" class="form-control text-center" id="senhaDesbloqueioInput" placeholder="Palavra-passe"><div id="erroSenhaDesbloqueio" class="text-danger small text-center mt-2 fw-bold" style="display: none;"></div></div></div><div class="modal-footer justify-content-center"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-danger btn-sm" id="btnConfirmarDesbloqueio">Autorizar</button></div></div></div></div>

<?php
// ======================================================
// AJUSTE: Inclui os scripts JS específicos desta página ANTES de incluir o footer
// ======================================================
$extra_scripts = '
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="../scripts/cargos_form.js?v=4"></script>
';

//
include '../includes/footer.php';
?>