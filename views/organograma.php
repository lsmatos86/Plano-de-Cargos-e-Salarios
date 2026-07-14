<?php
// Arquivo: views/organograma.php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

use App\Core\Database;
use App\Service\AuthService;

// Fallback de compatibilidade para checagem de sessão no XAMPP local
if (class_exists('App\Service\AuthService') && method_exists('App\Service\AuthService', 'checkAuth')) {
    if (!AuthService::checkAuth()) { 
        header('Location: ../login.php'); 
        exit; 
    }
} else if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$authService = new AuthService();
$authService->checkAndFail('cargos_visualizar');

$db = Database::getConnection();

// Áreas disponíveis para o filtro, usando aliases esperados pelo HTML legado.
$areas = $db->query(
    'SELECT "areaId" AS id_area_atuacao, "areaNome" AS nome
     FROM areas_atuacao
     ORDER BY "areaNome" ASC'
)->fetchAll(PDO::FETCH_ASSOC);

// Filtro de Área
$area_filtro = isset($_GET['area']) ? $_GET['area'] : '';
$modo_visualizacao = isset($_GET['modo']) ? $_GET['modo'] : 'cargo'; // 'cargo' ou 'setor'
$cargos_por_area = [];

if ($modo_visualizacao === 'setor') {
    $cargosAreaRows = $db->query(
        'SELECT ca."areaId" AS area_id,
                c."cargoId" AS id,
                c."cargoNome" AS nome,
                COALESCE(n."nivelDescricao", \'Sem nível definido\') AS nivel
           FROM cargos_area ca
           JOIN cargos c ON c."cargoId" = ca."cargoId"
      LEFT JOIN nivel_hierarquico n ON n."nivelId" = c."nivelHierarquicoId"
       ORDER BY ca."areaId", n."nivelOrdem" ASC NULLS LAST, c."cargoNome" ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cargosAreaRows as $cargoArea) {
        $areaId = (int)$cargoArea['area_id'];
        $cargos_por_area[$areaId][] = [
            'id' => (int)$cargoArea['id'],
            'nome' => (string)$cargoArea['nome'],
            'nivel' => (string)$cargoArea['nivel'],
        ];
    }
}

// Query principal
if ($modo_visualizacao === 'setor') {
    $sql = "SELECT 
                a.\"areaId\" AS id,
                a.\"areaNome\" AS nome,
                a.\"areaPaiId\" AS pai_id,
                'setor' AS tipo,
                (SELECT COUNT(DISTINCT ca.\"cargoId\")
                   FROM cargos_area ca
                  WHERE ca.\"areaId\" = a.\"areaId\") AS total_cargos,
                0::INTEGER AS total_colaboradores
            FROM areas_atuacao a
            WHERE 1=1";
    if (!empty($area_filtro)) {
        $areaId = (int)$area_filtro;
        $sql .= " AND (a.\"areaId\" = {$areaId} OR a.\"areaPaiId\" = {$areaId})";
    }
    $sql .= ' ORDER BY a."areaNome" ASC';
} else {
    $sql = "SELECT 
                c.\"cargoId\" AS id,
                c.\"cargoNome\" AS nome,
                c.\"cargoSupervisorId\" AS pai_id,
                'cargo' AS tipo,
                COALESCE(n.\"nivelDescricao\", 'Sem nível definido') AS nivel_nome,
                COALESCE(area_info.area_nome, 'Sem área definida') AS area_nome,
                COALESCE(c.piso_valor, f.\"faixaSalarioMinimo\", 0) AS salario_base,
                0::INTEGER AS total_colaboradores
            FROM cargos c
            LEFT JOIN nivel_hierarquico n
                   ON n.\"nivelId\" = c.\"nivelHierarquicoId\"
            LEFT JOIN faixas_salariais f
                   ON f.\"faixaId\" = c.\"faixaId\"
            LEFT JOIN LATERAL (
                SELECT STRING_AGG(a.\"areaNome\", ', ' ORDER BY a.\"areaNome\") AS area_nome
                  FROM cargos_area ca
                  JOIN areas_atuacao a ON a.\"areaId\" = ca.\"areaId\"
                 WHERE ca.\"cargoId\" = c.\"cargoId\"
            ) area_info ON TRUE
            WHERE 1=1";
    if (!empty($area_filtro)) {
        $areaId = (int)$area_filtro;
        $sql .= " AND EXISTS (
                    SELECT 1 FROM cargos_area ca_filter
                     WHERE ca_filter.\"cargoId\" = c.\"cargoId\"
                       AND ca_filter.\"areaId\" = {$areaId}
                  )";
    }
    $sql .= ' ORDER BY n."nivelOrdem" ASC NULLS LAST, c."cargoNome" ASC';
}

$itens = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Montar a árvore
$itens_by_id = [];
foreach ($itens as $item) {
    $item['children'] = [];
    $itens_by_id[$item['id']] = $item;
}

$tree = [];
foreach ($itens_by_id as $id => &$node) {
    if (empty($node['pai_id']) || !isset($itens_by_id[$node['pai_id']])) {
        $tree[] = &$node;
    } else {
        $itens_by_id[$node['pai_id']]['children'][] = &$node;
    }
}
unset($node);

function renderTree($nodes, $modo = 'cargo') {
    echo '<ul>';
    foreach ($nodes as $node) {
        echo '<li>';
        
        if ($modo === 'setor') {
            echo '<div class="organograma-node sector-node card shadow-sm" data-id="'.$node['id'].'" data-tipo="setor">';
            echo '<div class="card-body p-2 text-center">';
            echo '<div class="node-icon mb-1"><i class="fas fa-layer-group text-info"></i></div>';
            echo '<div class="font-weight-bold text-dark small text-truncate" title="'.htmlspecialchars($node['nome']).'">'.htmlspecialchars($node['nome']).'</div>';
            echo '<div class="text-muted text-xs mt-1">'.$node['total_cargos'].' Cargos</div>';
            echo '<div class="badge badge-secondary text-xs font-weight-normal mt-1">'.$node['total_colaboradores'].' Colaboradores</div>';
            echo '</div>';
            echo '</div>';
        } else {
            $salario_fmt = 'R$ ' . number_format($node['salario_base'], 2, ',', '.');
            echo '<div class="organograma-node cargo-node card shadow-sm" 
                       data-id="'.$node['id'].'" 
                       data-tipo="cargo"
                       data-nivel="'.htmlspecialchars($node['nivel_nome']).'"
                       data-area="'.htmlspecialchars($node['area_nome']).'"
                       data-salario="'.$salario_fmt.'"
                       data-colaboradores="'.$node['total_colaboradores'].'">';
            echo '<div class="card-body p-2 text-center">';
            echo '<div class="badge badge-primary text-xs font-weight-normal mb-1">'.htmlspecialchars($node['nivel_nome']).'</div>';
            echo '<div class="font-weight-bold text-dark small text-truncate" title="'.htmlspecialchars($node['nome']).'">'.htmlspecialchars($node['nome']).'</div>';
            echo '<div class="text-muted text-xs text-truncate">'.htmlspecialchars($node['area_nome']).'</div>';
            echo '<div class="text-success font-weight-bold text-xs mt-1">'.$salario_fmt.'</div>';
            if ($node['total_colaboradores'] > 0) {
                echo '<div class="mt-1"><span class="badge badge-pill badge-secondary" style="font-size: 10px;">'.$node['total_colaboradores'].' em atividade</span></div>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        if (!empty($node['children'])) {
            renderTree($node['children'], $modo);
        }
        echo '</li>';
    }
    echo '</ul>';
}

// ⚠️ ESSENCIAL: Define as variáveis de controle que o header e navbar originais utilizam para caminhos locais
$page_title = "Organograma Institucional";
$root_path = '../'; 
$main_container_class = 'container-fluid px-2 px-md-4';

include_once dirname(__DIR__) . '/includes/header.php';
?>

<style>
    .organograma-wrapper {
        position: relative;
        width: 100%;
        height: clamp(460px, calc(100vh - 290px), 760px);
        overflow: hidden;
        padding: 20px;
        background: #f8f9fc;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
        cursor: grab;
        user-select: none;
        touch-action: none;
        overscroll-behavior: contain;
    }
    .organograma-wrapper.is-dragging {
        cursor: grabbing;
    }
    .organograma-tree {
        display: inline-block;
        min-width: 100%;
        text-align: center;
        transform-origin: 0 0;
        will-change: transform;
    }
    .organograma-tree ul {
        padding-top: 20px; 
        position: relative;
        transition: all 0.5s;
        display: inline-flex;
    }
    .organograma-tree li {
        float: left; 
        text-align: center;
        list-style-type: none;
        position: relative;
        padding: 20px 10px 0 10px;
        transition: all 0.5s;
    }
    .organograma-tree li::before, .organograma-tree li::after {
        content: '';
        position: absolute; 
        top: 0; 
        right: 50%;
        border-top: 2px solid #b7b9cc;
        width: 50%; 
        height: 20px;
    }
    .organograma-tree li::after {
        right: auto; 
        left: 50%;
        border-left: 2px solid #b7b9cc;
    }
    .organograma-tree li:only-child::after, .organograma-tree li:only-child::before {
        display: none;
    }
    .organograma-tree li:only-child { 
        padding-top: 0;
    }
    .organograma-tree li:first-child::before, .organograma-tree li:last-child::after {
        border: 0 none;
    }
    .organograma-tree li:last-child::before {
        border-right: 2px solid #b7b9cc;
        border-radius: 0 5px 0 0;
    }
    .organograma-tree li:first-child::after {
        border-radius: 5px 0 0 0;
    }
    .organograma-tree ul ul::before {
        content: '';
        position: absolute; 
        top: 0; 
        left: 50%;
        border-left: 2px solid #b7b9cc;
        width: 0; 
        height: 20px;
    }
    .organograma-node {
        min-width: 180px;
        max-width: 220px;
        display: inline-block;
        border-radius: 8px;
        border: 1px solid #d1d3e2;
        transition: all 0.3s;
        background: #fff;
        cursor: pointer;
        z-index: 10;
        position: relative;
    }
    .organograma-node:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        border-color: #4e73df;
    }
    .sector-node { border-left: 4px solid #36b9cc; }
    .cargo-node { border-left: 4px solid #4e73df; }
    .node-icon { font-size: 20px; }
    .zoom-controls {
        position: absolute;
        bottom: 16px;
        right: 16px;
        z-index: 100;
    }
    .zoom-level {
        min-width: 52px;
        pointer-events: none;
    }
    .organograma-help {
        position: absolute;
        left: 16px;
        bottom: 16px;
        z-index: 90;
        background: rgba(255, 255, 255, .92);
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 5px 9px;
        color: #6c757d;
        font-size: .75rem;
        pointer-events: none;
    }
    @media (max-width: 767.98px) {
        .organograma-wrapper {
            height: 62vh;
            min-height: 420px;
            padding: 12px;
        }
        .organograma-node {
            min-width: 155px;
            max-width: 180px;
        }
        .organograma-help {
            display: none;
        }
    }
</style>

<div class="container-fluid mt-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold"><i class="fas fa-sitemap text-primary mr-2"></i>Organograma Institucional</h1>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm"><i class="fas fa-print fa-sm text-white-50 mr-1"></i> Imprimir</button>
            <a href="../relatorios/organograma_pdf.php<?php echo !empty($area_filtro) ? '?area='.$area_filtro : ''; ?>" target="_blank" class="btn btn-sm btn-danger shadow-sm"><i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> Exportar PDF</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="" class="form-inline row">
                <div class="form-group col-md-4 mb-2 mb-md-0">
                    <label class="mr-2 font-weight-bold small text-dark">Área/Setor:</label>
                    <select name="area" class="form-control form-control-sm w-70">
                        <option value="">-- Todas as Áreas --</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?php echo $a['id_area_atuacao']; ?>" <?php echo $area_filtro == $a['id_area_atuacao'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($a['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4 mb-2 mb-md-0">
                    <label class="mr-2 font-weight-bold small text-dark">Visualizar por:</label>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-sm btn-outline-primary <?php echo $modo_visualizacao === 'cargo' ? 'active' : ''; ?>">
                            <input type="radio" name="modo" value="cargo" onchange="this.form.submit()" <?php echo $modo_visualizacao === 'cargo' ? 'checked' : ''; ?>> <i class="fas fa-briefcase mr-1"></i> Cargos
                        </label>
                        <label class="btn btn-sm btn-outline-primary <?php echo $modo_visualizacao === 'setor' ? 'active' : ''; ?>">
                            <input type="radio" name="modo" value="setor" onchange="this.form.submit()" <?php echo $modo_visualizacao === 'setor' ? 'checked' : ''; ?>> <i class="fas fa-layer-group mr-1"></i> Setores
                        </label>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <button type="submit" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-filter mr-1"></i> Filtrar</button>
                    <a href="organograma.php" class="btn btn-sm btn-light border ml-1">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm position-relative overflow-hidden">
        <div class="card-body p-0">
            <div class="organograma-wrapper" id="organogramaWrapper">
                <div class="organograma-tree" id="organogramaTree">
                    <?php if (!empty($tree)): ?>
                        <?php renderTree($tree, $modo_visualizacao); ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-network-wired fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">Nenhum dado encontrado para a estrutura hierárquica atual.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="zoom-controls btn-group-vertical shadow-sm">
                <button type="button" class="btn btn-light btn-sm border" id="btnZoomIn" title="Aproximar"><i class="fas fa-plus text-dark"></i></button>
                <span class="btn btn-light btn-sm border zoom-level" id="zoomLevel">100%</span>
                <button type="button" class="btn btn-light btn-sm border" id="btnZoomReset" title="Centralizar e restaurar"><i class="fas fa-sync-alt text-dark"></i></button>
                <button type="button" class="btn btn-light btn-sm border" id="btnZoomOut" title="Afastar"><i class="fas fa-minus text-dark"></i></button>
            </div>
            <div class="organograma-help"><i class="fas fa-mouse-pointer me-1"></i> Arraste para mover · use a roda para ampliar</div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalhesNode" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold text-dark" id="modalTitle"><i class="fas fa-info-circle text-primary mr-2"></i>Detalhes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body py-3" id="modalBody"></div>
            <div class="modal-footer bg-light py-2">
                <a href="#" id="btnEditarNode" class="btn btn-sm btn-warning font-weight-bold d-none"><i class="fas fa-edit mr-1"></i> Editar Registro</a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const sectorCargos = <?php echo json_encode(
        $cargos_por_area,
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ); ?>;
    let currentZoom = 1;
    let panX = 0;
    let panY = 20;
    let isDragging = false;
    let dragMoved = false;
    let suppressNodeClick = false;
    let lastPointerX = 0;
    let lastPointerY = 0;
    const step = 0.12;
    const maxZoom = 2.5;
    const minZoom = 0.35;
    const wrapper = document.getElementById('organogramaWrapper');
    const treeElement = document.getElementById('organogramaTree');

    function applyTransform() {
        treeElement.style.transform = `translate(${panX}px, ${panY}px) scale(${currentZoom})`;
        document.getElementById('zoomLevel').textContent = `${Math.round(currentZoom * 100)}%`;
    }

    function setZoom(nextZoom, clientX, clientY) {
        const boundedZoom = Math.min(maxZoom, Math.max(minZoom, nextZoom));
        if (boundedZoom === currentZoom) return;

        const rect = wrapper.getBoundingClientRect();
        const focusX = (clientX ?? (rect.left + rect.width / 2)) - rect.left;
        const focusY = (clientY ?? (rect.top + rect.height / 2)) - rect.top;
        const contentX = (focusX - panX) / currentZoom;
        const contentY = (focusY - panY) / currentZoom;

        panX = focusX - contentX * boundedZoom;
        panY = focusY - contentY * boundedZoom;
        currentZoom = boundedZoom;
        applyTransform();
    }

    function resetView() {
        currentZoom = window.innerWidth < 768 ? 0.7 : 1;
        panX = 0;
        panY = 20;
        applyTransform();
    }

    wrapper.addEventListener('wheel', function(event) {
        event.preventDefault();
        setZoom(currentZoom + (event.deltaY < 0 ? step : -step), event.clientX, event.clientY);
    }, { passive: false });

    wrapper.addEventListener('pointerdown', function(event) {
        if (event.button !== 0) return;
        isDragging = true;
        dragMoved = false;
        lastPointerX = event.clientX;
        lastPointerY = event.clientY;
        wrapper.classList.add('is-dragging');
        wrapper.setPointerCapture(event.pointerId);
    });

    wrapper.addEventListener('pointermove', function(event) {
        if (!isDragging) return;
        const deltaX = event.clientX - lastPointerX;
        const deltaY = event.clientY - lastPointerY;
        if (Math.abs(deltaX) + Math.abs(deltaY) > 1) dragMoved = true;
        panX += deltaX;
        panY += deltaY;
        lastPointerX = event.clientX;
        lastPointerY = event.clientY;
        applyTransform();
    });

    function stopDragging(event) {
        if (!isDragging) return;
        isDragging = false;
        wrapper.classList.remove('is-dragging');
        if (wrapper.hasPointerCapture(event.pointerId)) wrapper.releasePointerCapture(event.pointerId);
        if (dragMoved) {
            suppressNodeClick = true;
            window.setTimeout(() => { suppressNodeClick = false; }, 0);
        }
    }

    wrapper.addEventListener('pointerup', stopDragging);
    wrapper.addEventListener('pointercancel', stopDragging);
    wrapper.addEventListener('dblclick', resetView);

    $('#btnZoomIn').click(function() { setZoom(currentZoom + step); });
    $('#btnZoomOut').click(function() { setZoom(currentZoom - step); });
    $('#btnZoomReset').click(resetView);
    resetView();

    $('.organograma-node').click(function() {
        if (suppressNodeClick) return;
        const id = $(this).data('id');
        const tipo = $(this).data('tipo');
        
        if (tipo === 'cargo') {
            const nome = $(this).find('.font-weight-bold').text();
            const nivel = $(this).data('nivel');
            const area = $(this).data('area');
            const salario = $(this).data('salario');
            const colaboradores = $(this).data('colaboradores');

            $('#modalTitle').html('<i class="fas fa-briefcase text-primary mr-2"></i> Ficha do Cargo');
            let html = `
                <table class="table table-sm table-striped mb-0 small">
                    <tr><td class="font-weight-bold" width="35%">Cargo:</td><td>${nome}</td></tr>
                    <tr><td class="font-weight-bold">Nível Hierárquico:</td><td><span class="badge badge-primary font-weight-normal">${nivel}</span></td></tr>
                    <tr><td class="font-weight-bold">Área/Setor:</td><td>${area}</td></tr>
                    <tr><td class="font-weight-bold">Salário Base:</td><td class="text-success font-weight-bold">${salario}</td></tr>
                    <tr><td class="font-weight-bold">Colaboradores Alocados:</td><td><span class="badge badge-secondary">${colaboradores}</span></td></tr>
                </table>
            `;
            $('#modalBody').html(html);
            $('#btnEditarNode').attr('href', 'cargos_form.php?id=' + id).removeClass('d-none');
        } else {
            const nome = $(this).find('.font-weight-bold').text();
            const cargos = sectorCargos[String(id)] || sectorCargos[id] || [];
            const $modalTitle = $('#modalTitle').empty();
            $modalTitle.append('<i class="fas fa-layer-group text-info me-2"></i>');
            $modalTitle.append(document.createTextNode(nome));

            const $modalBody = $('#modalBody').empty();
            $modalBody.append(
                $('<p>', { class: 'text-muted mb-3' }).text(
                    `${cargos.length} cargo(s) vinculado(s) diretamente a este setor.`
                )
            );

            if (cargos.length === 0) {
                $modalBody.append(
                    $('<div>', { class: 'alert alert-info mb-0' }).text(
                        'Nenhum cargo está vinculado diretamente a este setor.'
                    )
                );
            } else {
                const $table = $('<table>', { class: 'table table-sm table-striped table-hover align-middle mb-0' });
                $table.append(
                    '<thead class="table-light"><tr><th>Cargo</th><th>Nível hierárquico</th><th class="text-end">Ação</th></tr></thead>'
                );
                const $tbody = $('<tbody>');

                cargos.forEach(function(cargo) {
                    const $row = $('<tr>');
                    $row.append($('<td>', { class: 'fw-semibold' }).text(cargo.nome));
                    $row.append($('<td>').append($('<span>', { class: 'badge bg-primary' }).text(cargo.nivel)));
                    $row.append(
                        $('<td>', { class: 'text-end' }).append(
                            $('<a>', {
                                class: 'btn btn-sm btn-outline-primary',
                                href: 'cargos_form.php?id=' + encodeURIComponent(cargo.id),
                                title: 'Abrir cargo'
                            }).append('<i class="fas fa-external-link-alt me-1"></i> Abrir')
                        )
                    );
                    $tbody.append($row);
                });

                $table.append($tbody);
                $modalBody.append($('<div>', { class: 'table-responsive' }).append($table));
            }
            $('#btnEditarNode').addClass('d-none');
        }

        const modalElement = document.getElementById('modalDetalhesNode');
        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
        } else if ($.fn.modal) {
            $('#modalDetalhesNode').modal('show');
        }
    });
});
</script>

<?php
require_once dirname(__DIR__) . '/includes/footer.php';
?>
