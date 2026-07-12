<?php
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';
use App\Core\Database;
use App\Service\AuthService;

if (!isUserLoggedIn()) { header('Location: ../login.php'); exit; }
$authService = new AuthService();
$authService->checkAndFail('cargos:view', '../index.php?error=Acesso+negado');
$pdo = Database::getConnection();

// ── MODO SUPERVISÃO ──────────────────────────────────────────────────────────
$stmt = $pdo->query('
    SELECT c."cargoId", c."cargoNome", c."cargoSupervisorId",
           nh."nivelId", nh."nivelDescricao", nh."nivelOrdem",
           th."tipoNome"
    FROM cargos c
    LEFT JOIN nivel_hierarquico nh ON nh."nivelId" = c."nivelHierarquicoId"
    LEFT JOIN tipo_hierarquia th ON th."tipoId" = nh."tipoId"
    ORDER BY nh."nivelOrdem" DESC NULLS LAST, c."cargoNome"
');
$todosCargos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mapaCargos = [];
foreach ($todosCargos as $c) $mapaCargos[$c['cargoId']] = $c;

$idsNaArvore = [];
foreach ($todosCargos as $c) {
    if ($c['cargoSupervisorId'] !== null) {
        $idsNaArvore[$c['cargoId']] = true;
        $idsNaArvore[$c['cargoSupervisorId']] = true;
    }
}

$naoVinculados = array_filter($todosCargos, fn($c) => !isset($idsNaArvore[$c['cargoId']]));
$naoVinculadosPorNivel = [];
foreach ($naoVinculados as $c) {
    $nivel = $c['nivelDescricao'] ?? 'Sem Nível';
    $ordem = $c['nivelOrdem'] ?? 0;
    if (!isset($naoVinculadosPorNivel[$nivel])) {
        $naoVinculadosPorNivel[$nivel] = ['ordem' => $ordem, 'cargos' => []];
    }
    $naoVinculadosPorNivel[$nivel]['cargos'][] = $c;
}
uasort($naoVinculadosPorNivel, fn($a, $b) => $b['ordem'] <=> $a['ordem']);

function buildD3Node(array $mapaCargos, int $cargoId, array &$visitados): ?array {
    if (isset($visitados[$cargoId])) return null;
    $visitados[$cargoId] = true;
    $c = $mapaCargos[$cargoId] ?? null;
    if (!$c) return null;
    $node = [
        'id' => $c['cargoId'], 'name' => $c['cargoNome'], 'type' => 'cargo',
        'level' => $c['nivelDescricao'] ?? '', 'nivelOrdem' => (int)($c['nivelOrdem'] ?? 0),
        'editUrl' => 'cargos_form.php?id=' . $c['cargoId'],
    ];
    $filhos = array_filter($mapaCargos, fn($x) => $x['cargoSupervisorId'] == $cargoId);
    usort($filhos, fn($a, $b) => ($b['nivelOrdem'] ?? 0) <=> ($a['nivelOrdem'] ?? 0) ?: strcmp($a['cargoNome'], $b['cargoNome']));
    $children = [];
    foreach ($filhos as $f) {
        $child = buildD3Node($mapaCargos, $f['cargoId'], $visitados);
        if ($child) $children[] = $child;
    }
    if (!empty($children)) $node['children'] = $children;
    return $node;
}

$visitados = [];
$arvoreRaizes = [];
foreach ($todosCargos as $c) {
    $cid = $c['cargoId'];
    if (!isset($idsNaArvore[$cid]) || isset($visitados[$cid])) continue;
    $supId = $c['cargoSupervisorId'];
    $ehRaiz = ($supId === null) || !isset($mapaCargos[$supId]) || (!isset($idsNaArvore[$supId]) && !isset($visitados[$supId]));
    if ($ehRaiz) {
        $node = buildD3Node($mapaCargos, $cid, $visitados);
        if ($node) $arvoreRaizes[] = $node;
    }
}

if (count($arvoreRaizes) === 1) {
    $d3Data = $arvoreRaizes[0];
} elseif (count($arvoreRaizes) > 1) {
    $d3Data = ['id' => 0, 'name' => 'ITACITRUS', 'type' => 'cargo', 'level' => 'Organização', 'nivelOrdem' => 99, 'children' => $arvoreRaizes];
} else {
    $d3Data = null;
}
$supervisaoVR = count($arvoreRaizes) > 1;

// ── MODO SETOR ───────────────────────────────────────────────────────────────
$stmtAreas = $pdo->query('SELECT "areaId", "areaNome", "areaPaiId" FROM areas_atuacao ORDER BY "areaNome"');
$todasAreas = $stmtAreas->fetchAll(PDO::FETCH_ASSOC);
$mapaAreas = [];
foreach ($todasAreas as $a) $mapaAreas[$a['areaId']] = $a;

$stmtCA = $pdo->query('SELECT "cargoId", "areaId" FROM cargos_area');
$relCA = $stmtCA->fetchAll(PDO::FETCH_ASSOC);
$cargosPorArea = [];
$cargosComAreaSet = [];
foreach ($relCA as $r) {
    $cargosPorArea[$r['areaId']][] = $r['cargoId'];
    $cargosComAreaSet[$r['cargoId']] = true;
}

function buildD3AreaNode(array $mapaAreas, array $cargosPorArea, array $mapaCargos, int $areaId, array &$visitadosAreas): ?array {
    if (isset($visitadosAreas[$areaId])) return null;
    $visitadosAreas[$areaId] = true;
    $area = $mapaAreas[$areaId] ?? null;
    if (!$area) return null;

    $node = ['id' => 'area_' . $areaId, 'name' => $area['areaNome'], 'type' => 'area', 'editUrl' => null];
    $children = [];

    $subAreas = array_filter($mapaAreas, fn($a) => $a['areaPaiId'] == $areaId);
    usort($subAreas, fn($a, $b) => strcmp($a['areaNome'], $b['areaNome']));
    foreach ($subAreas as $sa) {
        $child = buildD3AreaNode($mapaAreas, $cargosPorArea, $mapaCargos, $sa['areaId'], $visitadosAreas);
        if ($child) $children[] = $child;
    }

    $cidsArea = $cargosPorArea[$areaId] ?? [];
    $cargosArea = array_filter(array_map(fn($cid) => $mapaCargos[$cid] ?? null, $cidsArea));
    usort($cargosArea, fn($a, $b) => ($b['nivelOrdem'] ?? 0) <=> ($a['nivelOrdem'] ?? 0) ?: strcmp($a['cargoNome'], $b['cargoNome']));
    foreach ($cargosArea as $c) {
        $children[] = [
            'id' => 'cargo_' . $c['cargoId'], 'name' => $c['cargoNome'], 'type' => 'cargo',
            'level' => $c['nivelDescricao'] ?? '', 'nivelOrdem' => (int)($c['nivelOrdem'] ?? 0),
            'editUrl' => 'cargos_form.php?id=' . $c['cargoId'],
        ];
    }

    if (!empty($children)) $node['children'] = $children;
    return $node;
}

$visitadosAreas = [];
$setorRaizes = [];
foreach ($todasAreas as $a) {
    $aId = $a['areaId'];
    if (isset($visitadosAreas[$aId])) continue;
    if ($a['areaPaiId'] === null || !isset($mapaAreas[$a['areaPaiId']])) {
        $node = buildD3AreaNode($mapaAreas, $cargosPorArea, $mapaCargos, $aId, $visitadosAreas);
        if ($node) $setorRaizes[] = $node;
    }
}
usort($setorRaizes, fn($a, $b) => strcmp($a['name'], $b['name']));

if (count($setorRaizes) === 1) {
    $d3SetorData = $setorRaizes[0];
} elseif (count($setorRaizes) > 1) {
    $d3SetorData = ['id' => 'org_root', 'name' => 'ITACITRUS', 'type' => 'area', 'editUrl' => null, 'children' => $setorRaizes];
} else {
    $d3SetorData = null;
}
$setorVR = count($setorRaizes) > 1;

$cargosSemSetor = array_filter($todosCargos, fn($c) => !isset($cargosComAreaSet[$c['cargoId']]));
$semSetorPorNivel = [];
foreach ($cargosSemSetor as $c) {
    $nivel = $c['nivelDescricao'] ?? 'Sem Nível';
    $ordem = $c['nivelOrdem'] ?? 0;
    if (!isset($semSetorPorNivel[$nivel])) {
        $semSetorPorNivel[$nivel] = ['ordem' => $ordem, 'cargos' => []];
    }
    $semSetorPorNivel[$nivel]['cargos'][] = $c;
}
uasort($semSetorPorNivel, fn($a, $b) => $b['ordem'] <=> $a['ordem']);

$totalNaArvore  = count($idsNaArvore);
$totalSemVinculo = count($naoVinculados);
$totalComSetor  = count($cargosComAreaSet);
$totalSemSetor  = count($cargosSemSetor);
$totalAreas     = count($todasAreas);

$levelColors = [
    99 => ['#495057','Organização'], 7 => ['#1a237e','Diretor'],
    6  => ['#283593','Gerente'],     5 => ['#0277bd','Coordenador'],
    4  => ['#00838f','Supervisor/Analista'], 3 => ['#558b2f','Encarregado'],
    2  => ['#ef6c00','Assistente'],  1 => ['#616161','Auxiliar/Operacional'],
    0  => ['#9e9e9e','Sem Nível'],
];

$page_title = 'Organograma';
$root_path  = '../';
include '../includes/header.php';
?>

<!-- CABEÇALHO DA PÁGINA -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><i class="fas fa-sitemap me-2"></i>Organograma</h1>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <span class="badge bg-success fs-6 py-2 px-3 stat-sup"><?php echo $totalNaArvore; ?> vinculados</span>
        <span class="badge bg-warning text-dark fs-6 py-2 px-3 stat-sup"><?php echo $totalSemVinculo; ?> sem vínculo</span>
        <span class="badge bg-info fs-6 py-2 px-3 stat-set d-none"><?php echo $totalComSetor; ?> com área</span>
        <span class="badge bg-secondary fs-6 py-2 px-3 stat-set d-none"><?php echo $totalSemSetor; ?> sem área</span>
        <a href="../relatorios/organograma_pdf.php" target="_blank" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i> Exportar PDF
        </a>
    </div>
</div>

<!-- ALTERNÂNCIA DE MODO -->
<div class="d-flex align-items-center gap-3 mb-3">
    <div class="btn-group" role="group" aria-label="Modo de visualização">
        <button type="button" class="btn btn-primary active" id="btn-supervisao" onclick="setMode('supervisao')">
            <i class="fas fa-sitemap me-1"></i> Por Supervisão
        </button>
        <button type="button" class="btn btn-outline-primary" id="btn-setor" onclick="setMode('setor')">
            <i class="fas fa-building me-1"></i> Por Setor
        </button>
    </div>
    <span class="text-muted small" id="mode-hint">Hierarquia de supervisão — quem reporta a quem</span>
</div>

<!-- CARD DA ÁRVORE -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span id="tree-title"><i class="fas fa-project-diagram me-1"></i> Árvore Hierárquica (<?php echo $totalNaArvore; ?> cargos)</span>
        <small class="opacity-75">Arraste para mover · Scroll para zoom · Clique no cargo para editar</small>
    </div>
    <div class="card-body p-0" style="background:#f0f4f8;">
        <!-- Estados vazios -->
        <div id="empty-supervisao" class="text-center p-5 d-none">
            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3 d-block"></i>
            <strong>Nenhuma relação hierárquica definida.</strong><br>
            <span class="text-muted">Abra cada cargo e defina o campo <strong>"Reporta-se a"</strong> na aba Hierarquia.</span>
        </div>
        <div id="empty-setor" class="text-center p-5 d-none">
            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3 d-block"></i>
            <strong>Nenhum cargo vinculado a áreas.</strong><br>
            <span class="text-muted">Abra cada cargo e vincule-o a uma <strong>Área de Atuação</strong>.</span>
        </div>
        <!-- SVG D3 -->
        <div id="org-container" style="width:100%;height:620px;overflow:hidden;position:relative;">
            <svg id="org-svg" style="width:100%;height:100%;"></svg>
        </div>
        <!-- Legenda -->
        <div class="p-2 border-top d-flex gap-3 flex-wrap align-items-center" style="font-size:.8rem;" id="legend-bar">
            <span id="legend-area" class="d-none">
                <span style="display:inline-block;width:14px;height:14px;background:#546e7a;border-radius:3px;vertical-align:middle;"></span>
                Área/Setor
            </span>
            <?php
            $ordensUsadas = array_unique(array_map(fn($c) => (int)($c['nivelOrdem'] ?? 0), $todosCargos));
            rsort($ordensUsadas);
            foreach ($ordensUsadas as $ord):
                $info = $levelColors[$ord] ?? $levelColors[0];
            ?>
            <span>
                <span style="display:inline-block;width:14px;height:14px;background:<?php echo $info[0]; ?>;border-radius:3px;vertical-align:middle;"></span>
                <?php echo htmlspecialchars($info[1]); ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- CARGOS SEM VÍNCULO (modo Supervisão) -->
<?php if (!empty($naoVinculados)): ?>
<div class="card shadow-sm mb-4" id="section-nao-vinculados">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <span><i class="fas fa-unlink me-1"></i> Cargos sem vínculo hierárquico (<?php echo $totalSemVinculo; ?>)</span>
        <small>Defina o campo "Reporta-se a" no cadastro do cargo para incluí-los.</small>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
        <?php foreach ($naoVinculadosPorNivel as $nivel => $dados): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 bg-light h-100">
                    <div class="card-header py-1 px-2 text-white" style="font-size:.75rem;font-weight:bold;background:<?php echo ($levelColors[$dados['ordem']] ?? $levelColors[0])[0]; ?>;">
                        <?php echo htmlspecialchars($nivel); ?> (<?php echo count($dados['cargos']); ?>)
                    </div>
                    <div class="card-body p-2">
                        <ul class="list-unstyled mb-0" style="font-size:.8rem;">
                        <?php foreach ($dados['cargos'] as $c): ?>
                            <li class="mb-1">
                                <a href="cargos_form.php?id=<?php echo $c['cargoId']; ?>" class="text-decoration-none text-dark">
                                    <i class="fas fa-chevron-right text-muted me-1" style="font-size:.65rem;"></i>
                                    <?php echo htmlspecialchars($c['cargoNome']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- CARGOS SEM ÁREA (modo Setor) -->
<?php if (!empty($cargosSemSetor)): ?>
<div class="card shadow-sm mb-4 d-none" id="section-sem-setor">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-folder-open me-1"></i> Cargos sem área de atuação (<?php echo $totalSemSetor; ?>)</span>
        <small>Abra o cadastro do cargo e vincule-o a uma Área de Atuação.</small>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
        <?php foreach ($semSetorPorNivel as $nivel => $dados): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 bg-light h-100">
                    <div class="card-header py-1 px-2 text-white" style="font-size:.75rem;font-weight:bold;background:<?php echo ($levelColors[$dados['ordem']] ?? $levelColors[0])[0]; ?>;">
                        <?php echo htmlspecialchars($nivel); ?> (<?php echo count($dados['cargos']); ?>)
                    </div>
                    <div class="card-body p-2">
                        <ul class="list-unstyled mb-0" style="font-size:.8rem;">
                        <?php foreach ($dados['cargos'] as $c): ?>
                            <li class="mb-1">
                                <a href="cargos_form.php?id=<?php echo $c['cargoId']; ?>" class="text-decoration-none text-dark">
                                    <i class="fas fa-chevron-right text-muted me-1" style="font-size:.65rem;"></i>
                                    <?php echo htmlspecialchars($c['cargoNome']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
(function () {
    const supervisaoData = <?php echo json_encode($d3Data,       JSON_UNESCAPED_UNICODE); ?>;
    const setorData      = <?php echo json_encode($d3SetorData,  JSON_UNESCAPED_UNICODE); ?>;
    const supervisaoVR   = <?php echo $supervisaoVR ? 'true' : 'false'; ?>;
    const setorVR        = <?php echo $setorVR      ? 'true' : 'false'; ?>;

    const LEVEL_COLORS = {
        99:'#495057', 7:'#1a237e', 6:'#283593', 5:'#0277bd',
        4:'#00838f',  3:'#558b2f', 2:'#ef6c00', 1:'#616161', 0:'#9e9e9e'
    };
    const AREA_COLOR = '#546e7a';

    const container = document.getElementById('org-container');
    const svg = d3.select('#org-svg');
    const g   = svg.append('g');

    const zoom = d3.zoom()
        .scaleExtent([0.08, 4])
        .on('zoom', e => g.attr('transform', e.transform));
    svg.call(zoom);

    // Controles de zoom (montados uma vez)
    const ctrlDiv = document.createElement('div');
    ctrlDiv.style.cssText = 'position:absolute;top:12px;right:12px;display:flex;gap:6px;z-index:10;';
    ctrlDiv.innerHTML = `
        <button onclick="fitView()" class="btn btn-sm btn-light border" title="Centralizar"><i class="fas fa-expand-arrows-alt"></i></button>
        <button onclick="zoomIn()"  class="btn btn-sm btn-light border" title="Zoom +"><i class="fas fa-search-plus"></i></button>
        <button onclick="zoomOut()" class="btn btn-sm btn-light border" title="Zoom -"><i class="fas fa-search-minus"></i></button>
    `;
    container.appendChild(ctrlDiv);

    let fitParams = { tx: 0, ty: 0, scale: 1 };

    function wrapText(textSel, nameStr, hasLabel) {
        const words = nameStr.split(' ');
        const maxChars = 22;
        let line = '', lines = [];
        for (const w of words) {
            const test = line ? line + ' ' + w : w;
            if (test.length > maxChars && line) { lines.push(line); line = w; }
            else line = test;
        }
        if (line) lines.push(line);
        if (lines.length > 2) lines = [lines.slice(0, 2).join(' ').substring(0, 24) + '…'];
        const yStart = hasLabel ? (lines.length === 1 ? -8 : -13) : (lines.length === 1 ? 0 : -7);
        lines.forEach((l, i) => {
            textSel.append('tspan')
                .attr('x', 0)
                .attr('dy', i === 0 ? `${yStart}px` : '13px')
                .text(l);
        });
    }

    function drawTree(data, isVR) {
        g.selectAll('*').remove();
        if (!data) return;

        const W = container.clientWidth;
        const H = container.clientHeight;
        const nodeH = 56, nodeW = 200, gap = 18;

        const root       = d3.hierarchy(data);
        const treeLayout = d3.tree().nodeSize([nodeH + gap, nodeW + 60]);
        treeLayout(root);

        const visNodes = root.descendants().filter(d => !(isVR && d.depth === 0));
        const visLinks = root.links().filter(d =>        !(isVR && d.source.depth === 0));

        // Links
        g.selectAll('.org-link')
            .data(visLinks)
            .join('path')
            .attr('class', 'org-link')
            .attr('fill', 'none')
            .attr('stroke', '#9ca3af')
            .attr('stroke-width', 1.5)
            .attr('d', d3.linkHorizontal().x(d => d.y).y(d => d.x));

        // Nodes
        const node = g.selectAll('.org-node')
            .data(visNodes)
            .join('g')
            .attr('class', 'org-node')
            .attr('transform', d => `translate(${d.y},${d.x})`)
            .style('cursor', d => d.data.editUrl ? 'pointer' : 'default')
            .on('click', (e, d) => { if (d.data.editUrl) window.open(d.data.editUrl, '_self'); });

        // Fundo do nó
        node.append('rect')
            .attr('x', -nodeW / 2).attr('y', -nodeH / 2)
            .attr('width', nodeW).attr('height', nodeH)
            .attr('rx', 8).attr('ry', 8)
            .attr('fill', d => d.data.type === 'area' ? AREA_COLOR : (LEVEL_COLORS[d.data.nivelOrdem] ?? '#6c757d'))
            .attr('stroke', 'rgba(255,255,255,0.3)').attr('stroke-width', 1.5)
            .attr('filter', 'drop-shadow(0 2px 4px rgba(0,0,0,0.25))');

        // Texto do nome (com quebra de linha)
        node.append('text')
            .attr('text-anchor', 'middle')
            .attr('fill', 'white')
            .attr('font-size', '10.5px').attr('font-weight', '700')
            .attr('font-family', 'system-ui, sans-serif')
            .each(function (d) {
                const hasLabel = (d.data.type === 'cargo' && !!d.data.level)
                              || d.data.type === 'area';
                wrapText(d3.select(this), d.data.name, hasLabel);
            });

        // Badge de nível (apenas cargos com nível)
        node.filter(d => d.data.type === 'cargo' && !!d.data.level)
            .append('text')
            .attr('text-anchor', 'middle')
            .attr('fill', 'rgba(255,255,255,0.75)')
            .attr('font-size', '9px').attr('font-family', 'system-ui, sans-serif')
            .attr('y', d => Math.ceil(d.data.name.length / 22) <= 1 ? 12 : 18)
            .text(d => d.data.level);

        // Badge "Setor" (apenas áreas)
        node.filter(d => d.data.type === 'area')
            .append('text')
            .attr('text-anchor', 'middle')
            .attr('fill', 'rgba(255,255,255,0.65)')
            .attr('font-size', '9px').attr('font-family', 'system-ui, sans-serif')
            .attr('y', d => Math.ceil(d.data.name.length / 22) <= 1 ? 12 : 18)
            .text('Área');

        // Tooltip
        node.append('title').text(d =>
            d.data.type === 'area'
                ? d.data.name + '\nÁrea de Atuação'
                : d.data.name + (d.data.level ? '\n' + d.data.level : '') + '\nClique para editar'
        );

        // Centraliza vista
        const allX = visNodes.map(d => d.y);
        const allY = visNodes.map(d => d.x);
        const minX = d3.min(allX) - nodeW / 2 - 20, maxX = d3.max(allX) + nodeW / 2 + 20;
        const minY = d3.min(allY) - nodeH / 2 - 20, maxY = d3.max(allY) + nodeH / 2 + 20;
        const treeW = maxX - minX, treeH = maxY - minY;
        const scale = Math.min(W / treeW, H / treeH, 1.2) * 0.9;
        const tx = (W - treeW * scale) / 2 - minX * scale;
        const ty = (H - treeH * scale) / 2 - minY * scale;
        fitParams = { tx, ty, scale };
        svg.call(zoom.transform, d3.zoomIdentity.translate(tx, ty).scale(scale));
    }

    window.fitView  = () => svg.transition().duration(500).call(zoom.transform, d3.zoomIdentity.translate(fitParams.tx, fitParams.ty).scale(fitParams.scale));
    window.zoomIn   = () => svg.transition().duration(300).call(zoom.scaleBy, 1.4);
    window.zoomOut  = () => svg.transition().duration(300).call(zoom.scaleBy, 0.7);

    window.setMode = function (mode) {
        // Botões
        const btnSup = document.getElementById('btn-supervisao');
        const btnSet = document.getElementById('btn-setor');
        btnSup.className = 'btn ' + (mode === 'supervisao' ? 'btn-primary active' : 'btn-outline-primary');
        btnSet.className = 'btn ' + (mode === 'setor'      ? 'btn-primary active' : 'btn-outline-primary');

        // Badges de estatística
        document.querySelectorAll('.stat-sup').forEach(el => el.classList.toggle('d-none', mode !== 'supervisao'));
        document.querySelectorAll('.stat-set').forEach(el => el.classList.toggle('d-none', mode !== 'setor'));

        // Dica de modo + título do card
        const hints = {
            supervisao: 'Hierarquia de supervisão — quem reporta a quem',
            setor:      'Áreas de atuação com seus cargos vinculados'
        };
        const titles = {
            supervisao: '<i class="fas fa-project-diagram me-1"></i> Árvore Hierárquica (<?php echo $totalNaArvore; ?> cargos)',
            setor:      '<i class="fas fa-building me-1"></i> Organograma por Setor (<?php echo $totalAreas; ?> áreas)'
        };
        document.getElementById('mode-hint').textContent = hints[mode];
        document.getElementById('tree-title').innerHTML  = titles[mode];

        // Legenda: mostra/oculta item de área
        document.getElementById('legend-area').classList.toggle('d-none', mode !== 'setor');

        // Seções inferiores
        const nvEl = document.getElementById('section-nao-vinculados');
        const ssEl = document.getElementById('section-sem-setor');
        if (nvEl) nvEl.classList.toggle('d-none', mode !== 'supervisao');
        if (ssEl) ssEl.classList.toggle('d-none', mode !== 'setor');

        // Reset empty states
        document.getElementById('empty-supervisao').classList.add('d-none');
        document.getElementById('empty-setor').classList.add('d-none');
        document.getElementById('org-container').style.display = '';

        // Desenha árvore ou mostra estado vazio
        if (mode === 'supervisao') {
            if (!supervisaoData) {
                document.getElementById('org-container').style.display = 'none';
                document.getElementById('empty-supervisao').classList.remove('d-none');
            } else {
                drawTree(supervisaoData, supervisaoVR);
            }
        } else {
            if (!setorData) {
                document.getElementById('org-container').style.display = 'none';
                document.getElementById('empty-setor').classList.remove('d-none');
            } else {
                drawTree(setorData, setorVR);
            }
        }
    };

    // Renderização inicial
    setMode('supervisao');
})();
</script>

<?php include '../includes/footer.php'; ?>
