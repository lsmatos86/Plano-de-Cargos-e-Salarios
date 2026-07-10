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
            $userIdToCheck = $_SESSION['user_id'] ?? 0;
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

use App\Controller\CargoFormController; 
use App\Service\AuthService;
use App\Core\Database;

$root_path = '../'; 
$page_title = "Carregando..."; 
$breadcrumb_items = [
    'Dashboard' => $root_path . 'index.php',
    'Gerenciamento de Cargos' => 'cargos.php',
    'Formulário' => null 
];
$page_scripts = [$root_path . 'scripts/cargos_form.js']; 

try {
    $pdo = Database::getConnection(); 
    $authService = new AuthService(); 
    $controller = new CargoFormController($pdo, $authService);
    
    $data = $controller->handleRequest($_GET, $_POST, $_SERVER['REQUEST_METHOD']);

    extract($data);
    $breadcrumb_items['Formulário'] = $page_title;
    
    $temPermissaoEdicao = false;
    try {
        $authService->checkAndFail('cargos:edit');
        $temPermissaoEdicao = true;
    } catch (\Exception $e) {
        $temPermissaoEdicao = false;
    }

    $adjacentIds = ['prev_id' => null, 'next_id' => null];
    if ($isEditing && $currentFormId > 0) {
        $cargoRepoNav = new \App\Repository\CargoRepository();
        $nav_sort_col = $_GET['sort_col'] ?? 'c.cargoId';
        $nav_sort_dir = $_GET['sort_dir'] ?? 'ASC';
        $nav_term = $_GET['term'] ?? '';
        $adjacentIds = $cargoRepoNav->findAdjacentCargoIds($currentFormId, $nav_sort_col, $nav_sort_dir, $nav_term);
    }

} catch (Exception $e) {
    $page_title = 'Erro';
    $message = $e->getMessage();
    $message_type = 'danger';
    
    $cargo = []; $cbos = []; $escolaridades = []; $habilidadesAgrupadas = [];
    $habilidades = []; $caracteristicas = []; $riscos = []; $cursos = [];
    $recursosGrupos = []; $faixasSalariais = []; $areasAtuacao = [];
    $cargosSupervisor = []; $niveisOrdenados = []; $cargoAreas = [];
    $cargoHabilidades = []; $cargoCaracteristicas = []; $cargoRiscos = [];
    $cargoCursos = []; $cargoRecursosGrupos = []; $cargoSinonimos = [];
    $originalId = 0; $isEditing = false; $isDuplicating = false; $currentFormId = 0;
    $adjacentIds = ['prev_id' => null, 'next_id' => null];
}
?>
<script>
    const normalizeState = (data) => data.map(item => {
        if (item.id && !isNaN(item.id) && typeof item.id === 'string' && !String(item.id).startsWith('new-')) {
            item.id = parseInt(item.id);
        }
        return item;
    });

    window.habilidadesAssociadas = normalizeState(<?php echo json_encode($cargoHabilidades); ?>);
    window.caracteristicasAssociadas = normalizeState(<?php echo json_encode($cargoCaracteristicas); ?>);
    window.riscosAssociados = normalizeState(<?php echo json_encode($cargoRiscos); ?>);
    window.cursosAssociados = normalizeState(<?php echo json_encode($cargoCursos); ?>);
    window.recursosGruposAssociados = normalizeState(<?php echo json_encode($cargoRecursosGrupos); ?>);
    window.areasAssociadas = normalizeState(<?php echo json_encode($cargoAreas); ?>);
    window.sinonimosAssociados = normalizeState(<?php echo json_encode($cargoSinonimos); ?>);
    window.supervisoresAssociados = normalizeState(<?php echo json_encode($supervisores ?? []); ?>);
</script>

<?php 
require_once $root_path . 'includes/header.php'; 
?>

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

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">
            <?php 
            if ($isEditing && !empty($cargo['cargoNome'])) {
                echo htmlspecialchars($cargo['cargoNome']);
                echo ' <small class="text-muted fs-6" style="font-weight: 500;">(Editando) <i class="fas fa-pencil-alt fa-xs"></i></small>';
            } else {
                echo htmlspecialchars($page_title); 
            }
            ?>
        </h1>
        
        <div class="d-flex gap-2 align-items-center">
            <?php if ($isEditing && $currentFormId > 0): ?>
                <div class="btn-group shadow-sm me-2" role="group">
                    <?php 
                    $navParamsPrev = http_build_query(array_merge($_GET, ['id' => $adjacentIds['prev_id']]));
                    $navParamsNext = http_build_query(array_merge($_GET, ['id' => $adjacentIds['next_id']]));
                    ?>
                    <a href="<?php echo empty($adjacentIds['prev_id']) ? '#' : 'cargos_form.php?' . $navParamsPrev; ?>" 
                       class="btn btn-outline-primary btn-sm btn-nav-smart <?php echo empty($adjacentIds['prev_id']) ? 'disabled' : ''; ?>" 
                       title="Ir para o Cargo Anterior">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                    <a href="<?php echo empty($adjacentIds['next_id']) ? '#' : 'cargos_form.php?' . $navParamsNext; ?>" 
                       class="btn btn-outline-primary btn-sm btn-nav-smart <?php echo empty($adjacentIds['next_id']) ? 'disabled' : ''; ?>" 
                       title="Ir para o Próximo Cargo">
                        Próximo <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            <?php endif; ?>

            <a href="cargos.php" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left"></i> Voltar para Lista
            </a>

            <?php if ($isEditing && $currentFormId > 0): ?>
                 <a href="cargos_form.php?id=<?php echo $currentFormId; ?>&action=duplicate" 
                    class="btn btn-warning btn-sm shadow-sm" 
                    title="Criar um novo registro com base neste.">
                    <i class="fas fa-copy"></i> Duplicar
                 </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?> 
    
    <form method="POST" action="cargos_form.php" id="cargoForm">
        <input type="hidden" name="cargoId" value="<?php echo htmlspecialchars($currentFormId); ?>">
        <?php if ($isDuplicating): ?>
            <input type="hidden" name="originalId" value="<?php echo htmlspecialchars($originalId); ?>">
        <?php endif; ?>

        <?php 
        $revisado = (int)($cargo['is_revisado'] ?? 0);
        $dataRevisao = !empty($cargo['data_revisao']) ? date('d/m/Y H:i', strtotime($cargo['data_revisao'])) : '';
        ?>
        <input type="hidden" id="hidden_original_revisado" value="<?php echo $revisado; ?>">

        <div class="card mb-4 border-<?php echo $revisado ? 'success' : 'warning'; ?>" id="cardRevisao">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center py-3 bg-light">
                <div class="form-check form-switch mb-2 mb-md-0 fs-5">
                    <input class="form-check-input cursor-pointer" type="checkbox" id="is_revisado" name="is_revisado" value="1" <?php echo $revisado ? 'checked' : ''; ?>>
                    <label class="form-check-label fw-bold text-<?php echo $revisado ? 'success' : 'dark'; ?>" for="is_revisado">
                        <i class="fas fa-check-double"></i> Cargo Revisado e Aprovado
                    </label>
                </div>
                
                <div class="d-flex align-items-center">
                    <?php if ($revisado && $dataRevisao): ?>
                        <span class="badge bg-success fs-6 me-3">
                            <i class="fas fa-calendar-check"></i> Revisado em: <?php echo $dataRevisao; ?>
                        </span>
                        <button type="button" class="btn btn-sm btn-danger" id="btnDesbloquearEdicao">
                            <i class="fas fa-unlock"></i> Desbloquear Edição
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

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
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cargoSupervisorId" class="form-label">Reporta-se a (Supervisor/Líder)</label>
                        <select class="form-select searchable-select" id="cargoSupervisorId" name="cargoSupervisorId[]" multiple="multiple" data-placeholder="--- Nenhum ou Múltiplos ---">
                            <?php 
                            $supervisoresAtuais = isset($supervisores) ? array_column($supervisores, 'id') : [];
                            foreach ($cargosSupervisor as $id => $nome): 
                                if ($isEditing && (int)($originalId) === (int)$id) continue; 
                                $selected = in_array($id, $supervisoresAtuais) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $id; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <hr>
                <h4 class="mb-3"><i class="fas fa-building"></i> Áreas de Atuação</h4>
                <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoAreasAtuacao">
                    <i class="fas fa-plus"></i> Adicionar Área
                </button>
                <div class="card p-0 mt-2">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Área de Atuação (Hierárquica)</th>
                                <th class="grid-action-cell text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="areasAtuacaoGridBody"></tbody>
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
                        <tbody id="recursosGruposGridBody"></tbody>
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

        <input type="hidden" id="motivo_alteracao" name="motivo_alteracao" value="">

        <button type="button" id="btnDispararSalvar" class="btn btn-lg btn-success w-100 mt-3">
            <i class="fas fa-check-circle"></i> SALVAR CARGO
        </button>
    </form>
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
require_once $root_path . 'includes/footer.php';
?>