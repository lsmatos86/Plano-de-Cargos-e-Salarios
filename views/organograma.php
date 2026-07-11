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

// Carrega todos os cargos com info de nível
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

// Mapa por ID para lookup rápido
$mapaCargos = [];
foreach ($todosCargos as $c) $mapaCargos[$c['cargoId']] = $c;

// IDs que são supervisores de alguém
$supervisoresIds = array_filter(array_column($todosCargos, 'cargoSupervisorId'));

// IDs de cargos que têm subordinados OU têm supervisor (fazem parte da árvore)
$idsNaArvore = [];
foreach ($todosCargos as $c) {
    if ($c['cargoSupervisorId'] !== null) {
        $idsNaArvore[$c['cargoId']] = true;
        $idsNaArvore[$c['cargoSupervisorId']] = true;
    }
}

// Cargos não vinculados (fora da árvore)
$naoVinculados = array_filter($todosCargos, fn($c) => !isset($idsNaArvore[$c['cargoId']]));

// Agrupa não-vinculados por nível
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

// Função recursiva para construir a árvore D3
function buildD3Node(array $mapaCargos, int $cargoId, array &$visitados): ?array {
    if (isset($visitados[$cargoId])) return null; // evita ciclos
    $visitados[$cargoId] = true;
    $c = $mapaCargos[$cargoId] ?? null;
    if (!$c) return null;

    $node = [
        'id'        => $c['cargoId'],
        'name'      => $c['cargoNome'],
        'level'     => $c['nivelDescricao'] ?? '',
        'nivelOrdem'=> (int)($c['nivelOrdem'] ?? 0),
        'editUrl'   => 'cargos_form.php?id=' . $c['cargoId'],
    ];

    // Filhos
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

// Identifica raízes da árvore (na árvore, mas sem supervisor próprio, ou com supervisor inexistente)
$visitados = [];
$arvoreRaizes = [];
foreach ($todosCargos as $c) {
    $cid = $c['cargoId'];
    if (!isset($idsNaArvore[$cid])) continue;
    if (isset($visitados[$cid])) continue;
    $supId = $c['cargoSupervisorId'];
    // É raiz se não tem supervisor OU seu supervisor está fora da árvore
    $ehRaiz = ($supId === null) || (!isset($mapaCargos[$supId])) || (!isset($idsNaArvore[$supId]) && !isset($visitados[$supId]));
    if ($ehRaiz) {
        $node = buildD3Node($mapaCargos, $cid, $visitados);
        if ($node) $arvoreRaizes[] = $node;
    }
}

// Arvore D3: nó virtual raiz só se tiver mais de uma raiz
if (count($arvoreRaizes) === 1) {
    $d3Data = $arvoreRaizes[0];
} elseif (count($arvoreRaizes) > 1) {
    $d3Data = ['id' => 0, 'name' => 'ITACITRUS', 'level' => 'Organização', 'nivelOrdem' => 99, 'children' => $arvoreRaizes];
} else {
    $d3Data = null;
}

$totalNaArvore = count($idsNaArvore);
$totalSemVinculo = count($naoVinculados);

$page_title = 'Organograma';
$root_path = '../';
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><i class="fas fa-sitemap me-2"></i>Organograma</h1>
    <div class="d-flex gap-2">
        <span class="badge bg-success fs-6 py-2 px-3"><?php echo $totalNaArvore; ?> vinculados</span>
        <span class="badge bg-warning text-dark fs-6 py-2 px-3"><?php echo $totalSemVinculo; ?> sem vínculo</span>
        <a href="../relatorios/organograma_pdf.php" target="_blank" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i> Exportar PDF
        </a>
    </div>
</div>

<?php if (empty($idsNaArvore)): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Nenhuma relação hierárquica definida.</strong>
        Para construir o organograma, abra cada cargo e defina o campo <strong>"Reporta-se a"</strong> na aba Hierarquia.
    </div>
<?php else: ?>

<!-- ÁRVORE INTERATIVA -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-project-diagram me-1"></i> Árvore Hierárquica (<?php echo $totalNaArvore; ?> cargos)</span>
        <small class="opacity-75">Arraste para mover · Scroll para zoom · Clique no cargo para editar</small>
    </div>
    <div class="card-body p-0" style="background:#f0f4f8;">
        <div id="org-container" style="width:100%;height:620px;overflow:hidden;position:relative;">
            <svg id="org-svg" style="width:100%;height:100%;"></svg>
        </div>
        <div class="p-2 border-top d-flex gap-3 flex-wrap" style="font-size:.8rem;">
            <?php
            $levelColors = [
                99 => ['#495057','Organização'], 7 => ['#1a237e','Diretor'],
                6  => ['#283593','Gerente'],     5 => ['#0277bd','Coordenador'],
                4  => ['#00838f','Supervisor/Analista'], 3 => ['#558b2f','Encarregado'],
                2  => ['#ef6c00','Assistente'],  1 => ['#616161','Auxiliar/Operacional'],
                0  => ['#9e9e9e','Sem Nível'],
            ];
            // Coleta quais ordens existem na árvore
            $ordensUsadas = array_unique(array_map(fn($c) => (int)($c['nivelOrdem'] ?? 0), array_filter($todosCargos, fn($c) => isset($idsNaArvore[$c['cargoId']]))));
            rsort($ordensUsadas);
            foreach ($ordensUsadas as $ord):
                $info = $levelColors[$ord] ?? $levelColors[0];
            ?>
            <span><span style="display:inline-block;width:14px;height:14px;background:<?php echo $info[0];?>;border-radius:3px;vertical-align:middle;"></span> <?php echo htmlspecialchars($info[1]); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- CARGOS SEM VÍNCULO -->
<?php if (!empty($naoVinculados)): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <span><i class="fas fa-unlink me-1"></i> Cargos sem vínculo hierárquico (<?php echo $totalSemVinculo; ?>)</span>
        <small>Defina o campo "Reporta-se a" no cadastro do cargo para incluí-los no organograma.</small>
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

<?php if (!empty($idsNaArvore) && $d3Data): ?>
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
(function() {
    const orgData = <?php echo json_encode($d3Data, JSON_UNESCAPED_UNICODE); ?>;
    const VIRTUAL_ROOT = <?php echo count($arvoreRaizes) > 1 ? 'true' : 'false'; ?>;

    const levelColors = {
        99:'#495057', 7:'#1a237e', 6:'#283593', 5:'#0277bd',
        4:'#00838f', 3:'#558b2f', 2:'#ef6c00', 1:'#616161', 0:'#9e9e9e'
    };

    const container = document.getElementById('org-container');
    const W = container.clientWidth;
    const H = container.clientHeight;

    const svg = d3.select('#org-svg');
    const g = svg.append('g');

    // Zoom & Pan
    const zoom = d3.zoom()
        .scaleExtent([0.08, 4])
        .on('zoom', (event) => g.attr('transform', event.transform));
    svg.call(zoom);

    // Build hierarchy
    const root = d3.hierarchy(orgData);
    const nodeH = 56, nodeW = 200, gap = 18;
    const treeLayout = d3.tree().nodeSize([nodeH + gap, nodeW + 60]);
    treeLayout(root);

    // Filter out virtual root visually
    const visibleNodes = root.descendants().filter(d => !(VIRTUAL_ROOT && d.data.id === 0));
    const visibleLinks = root.links().filter(d => !(VIRTUAL_ROOT && (d.source.data.id === 0 || d.target.data.id === 0)));

    // Draw curved links
    g.selectAll('.link')
        .data(visibleLinks)
        .join('path')
        .attr('class', 'org-link')
        .attr('d', d3.linkHorizontal().x(d => d.y).y(d => d.x))
        .attr('fill', 'none')
        .attr('stroke', '#9ca3af')
        .attr('stroke-width', 1.5)
        .attr('stroke-dasharray', 'none');

    // If virtual root: draw lines from container center to each root child
    if (VIRTUAL_ROOT) {
        // Lines from virtual top level
        const rootChildren = root.children || [];
        if (rootChildren.length > 0) {
            const minX = d3.min(rootChildren, d => d.x);
            const maxX = d3.max(rootChildren, d => d.x);
            const midX = (minX + maxX) / 2;
        }
    }

    // Draw nodes
    const node = g.selectAll('.org-node')
        .data(visibleNodes)
        .join('g')
        .attr('class', 'org-node')
        .attr('transform', d => `translate(${d.y},${d.x})`)
        .style('cursor', 'pointer')
        .on('click', (event, d) => {
            window.open(d.data.editUrl, '_self');
        });

    // Node background card
    node.append('rect')
        .attr('x', -nodeW/2)
        .attr('y', -nodeH/2)
        .attr('width', nodeW)
        .attr('height', nodeH)
        .attr('rx', 8)
        .attr('ry', 8)
        .attr('fill', d => levelColors[d.data.nivelOrdem] ?? '#6c757d')
        .attr('stroke', 'rgba(255,255,255,0.3)')
        .attr('stroke-width', 1.5)
        .attr('filter', 'drop-shadow(0 2px 4px rgba(0,0,0,0.25))');

    // Cargo name (word wrap simulation)
    node.append('text')
        .attr('text-anchor', 'middle')
        .attr('dominant-baseline', 'middle')
        .attr('y', d => d.data.level ? -8 : 0)
        .attr('fill', 'white')
        .attr('font-size', '10.5px')
        .attr('font-weight', '700')
        .attr('font-family', 'system-ui, sans-serif')
        .each(function(d) {
            const text = d3.select(this);
            const words = d.data.name.split(' ');
            const maxChars = 22;
            let line = '';
            let lines = [];
            for (const w of words) {
                const test = line ? line + ' ' + w : w;
                if (test.length > maxChars && line) {
                    lines.push(line);
                    line = w;
                } else {
                    line = test;
                }
            }
            if (line) lines.push(line);
            // Max 2 lines
            if (lines.length > 2) {
                lines = [lines.slice(0, 2).join(' ').substring(0, 24) + '…'];
            }
            const yStart = d.data.level ? (lines.length === 1 ? -8 : -13) : (lines.length === 1 ? 0 : -7);
            text.attr('y', null);
            lines.forEach((l, i) => {
                text.append('tspan')
                    .attr('x', 0)
                    .attr('dy', i === 0 ? `${yStart}px` : '13px')
                    .text(l);
            });
        });

    // Level badge
    node.filter(d => !!d.data.level)
        .append('text')
        .attr('text-anchor', 'middle')
        .attr('dominant-baseline', 'middle')
        .attr('y', d => {
            const lines = Math.ceil(d.data.name.length / 22);
            return lines <= 1 ? 12 : 18;
        })
        .attr('fill', 'rgba(255,255,255,0.75)')
        .attr('font-size', '9px')
        .attr('font-family', 'system-ui, sans-serif')
        .text(d => d.data.level);

    // Tooltip on hover
    node.append('title')
        .text(d => `${d.data.name}\n${d.data.level}\nClique para editar`);

    // Center view
    const allX = visibleNodes.map(d => d.y);
    const allY = visibleNodes.map(d => d.x);
    const minX = d3.min(allX) - nodeW/2 - 20;
    const maxX = d3.max(allX) + nodeW/2 + 20;
    const minY = d3.min(allY) - nodeH/2 - 20;
    const maxY = d3.max(allY) + nodeH/2 + 20;
    const treeW = maxX - minX;
    const treeH = maxY - minY;
    const scale = Math.min(W / treeW, H / treeH, 1.2) * 0.9;
    const tx = (W - treeW * scale) / 2 - minX * scale;
    const ty = (H - treeH * scale) / 2 - minY * scale;
    svg.call(zoom.transform, d3.zoomIdentity.translate(tx, ty).scale(scale));

    // Controls
    const controlDiv = document.createElement('div');
    controlDiv.style.cssText = 'position:absolute;top:12px;right:12px;display:flex;gap:6px;';
    controlDiv.innerHTML = `
        <button onclick="fitView()" class="btn btn-sm btn-light border" title="Centralizar"><i class="fas fa-expand-arrows-alt"></i></button>
        <button onclick="zoomIn()" class="btn btn-sm btn-light border" title="Zoom +"><i class="fas fa-search-plus"></i></button>
        <button onclick="zoomOut()" class="btn btn-sm btn-light border" title="Zoom -"><i class="fas fa-search-minus"></i></button>
    `;
    container.appendChild(controlDiv);

    window.fitView = () => svg.transition().duration(500).call(zoom.transform, d3.zoomIdentity.translate(tx, ty).scale(scale));
    window.zoomIn = () => svg.transition().duration(300).call(zoom.scaleBy, 1.4);
    window.zoomOut = () => svg.transition().duration(300).call(zoom.scaleBy, 0.7);
})();
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
