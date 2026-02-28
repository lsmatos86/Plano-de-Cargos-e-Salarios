<?php
// Arquivo: views/cargos_form.php (VIEW: Ponto de Entrada)

// 1. INCLUDES GLOBAIS E INICIALIZAÇÃO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // Para getDbConnection e lookups

// 2. IMPORTA O CONTROLLER E DEPENDÊNCIAS
use App\Controller\CargoFormController; 
use App\Service\AuthService;
use App\Core\Database;

// 3. DEFINIÇÕES DA PÁGINA (para o header e footer)
$root_path = '../'; 
$page_title = "Carregando..."; 
$breadcrumb_items = [
    'Dashboard' => $root_path . 'index.php',
    'Gerenciamento de Cargos' => 'cargos.php',
    'Formulário' => null 
];
$page_scripts = [$root_path . 'scripts/cargos_form.js']; 

// 4. INSTANCIA O CONTROLLER E PROCESSA A REQUISIÇÃO
try {
    $pdo = Database::getConnection(); 
    $authService = new AuthService(); 
    $controller = new CargoFormController($pdo, $authService);
    
    $data = $controller->handleRequest($_GET, $_POST, $_SERVER['REQUEST_METHOD']);

    // 5. EXTRAI AS VARIÁVEIS PARA A VIEW
    extract($data);
    $breadcrumb_items['Formulário'] = $page_title;

    // --- NOVA LÓGICA DE NAVEGAÇÃO INTERNA ---
    $adjacentIds = ['prev_id' => null, 'next_id' => null];
    if ($isEditing && $currentFormId > 0) {
        $cargoRepoNav = new \App\Repository\CargoRepository();
        $nav_sort_col = $_GET['sort_col'] ?? 'c.cargoId';
        $nav_sort_dir = $_GET['sort_dir'] ?? 'ASC';
        $nav_term = $_GET['term'] ?? '';
        $adjacentIds = $cargoRepoNav->findAdjacentCargoIds($currentFormId, $nav_sort_col, $nav_sort_dir, $nav_term);
    }
    // -----------------------------------------

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


// 6. PREPARAÇÃO DOS DADOS JS (Global Scope)
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
            <?php if ($isEditing && $originalId > 0): ?>
                 <a href="cargos_form.php?id=<?php echo $originalId; ?>&action=duplicate" 
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
                        <div class="form-text">Você pode selecionar mais de um supervisor para atuações em áreas diferentes.</div>
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
                        <div class="form-text"><a href="faixas_salariais.php" target="_blank">Gerenciar Faixas Salariais</a></div>
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
<div class="modal fade" id="modalDesbloqueioSenha" tabindex="-1" aria-labelledby="modalDesbloqueioSenhaLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalDesbloqueioSenhaLabel"><i class="fas fa-lock"></i> Desbloquear Edição</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Este cargo foi revisto e bloqueado. Digite a senha do Administrador para libertar a edição:</p>
                <div class="mb-2">
                    <input type="password" class="form-control text-center" id="senhaDesbloqueioInput" placeholder="Palavra-passe">
                    <div id="erroSenhaDesbloqueio" class="text-danger small text-center mt-2 fw-bold" style="display: none;">Senha incorreta! Tente novamente.</div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-sm" id="btnConfirmarDesbloqueio">
                    <i class="fas fa-unlock"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
<?php 
// 8. INCLUSÃO DO FOOTER
require_once $root_path . 'includes/footer.php';
?>