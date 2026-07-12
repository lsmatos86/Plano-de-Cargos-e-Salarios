<?php
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';
use App\Core\Database;
use App\Service\AuthService;
use Dompdf\Dompdf;
use Dompdf\Options;

if (!isUserLoggedIn()) { die('Acesso Negado.'); }
$authService = new AuthService();
if (!$authService->hasPermission('cargos:view')) { die('Acesso Negado.'); }

$pdo = Database::getConnection();

// ── DADOS DE SUPERVISÃO ──────────────────────────────────────────────────────
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
        if (isset($mapaCargos[$c['cargoSupervisorId']])) {
            $idsNaArvore[$c['cargoSupervisorId']] = true;
        }
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

$levelColors = [
    99 => '#495057', 7 => '#1a237e', 6 => '#283593',
    5  => '#0277bd', 4 => '#00838f', 3 => '#558b2f',
    2  => '#ef6c00', 1 => '#616161', 0 => '#9e9e9e',
];

function levelColor(array $levelColors, ?int $ordem): string {
    return $levelColors[$ordem ?? 0] ?? $levelColors[0];
}

function renderTreePdf(array $mapaCargos, array $levelColors, ?int $parentId, int $depth, array &$visitados): string {
    $html = '';
    $filhos = array_filter($mapaCargos, fn($c) => $c['cargoSupervisorId'] == $parentId);
    usort($filhos, fn($a, $b) => ($b['nivelOrdem'] ?? 0) <=> ($a['nivelOrdem'] ?? 0) ?: strcmp($a['cargoNome'], $b['cargoNome']));
    foreach ($filhos as $c) {
        $cid = $c['cargoId'];
        if (isset($visitados[$cid])) continue;
        $visitados[$cid] = true;
        $indent = $depth * 18;
        $color  = levelColor($levelColors, (int)($c['nivelOrdem'] ?? 0));
        $nivel  = htmlspecialchars($c['nivelDescricao'] ?? 'Sem Nível');
        $nome   = htmlspecialchars($c['cargoNome']);
        $html .= '<div style="margin-left:' . $indent . 'px;margin-bottom:4px;display:flex;align-items:center;">';
        if ($depth > 0) {
            $html .= '<span style="color:#aaa;font-size:9px;margin-right:4px;">' . str_repeat('&nbsp;&nbsp;', $depth) . '└</span>';
        }
        $html .= '<span style="display:inline-block;background:' . $color . ';color:white;border-radius:4px;padding:3px 8px;font-size:9px;font-weight:bold;">' . $nome . '</span>';
        $html .= '<span style="margin-left:6px;font-size:8px;color:#666;">' . $nivel . '</span>';
        $html .= '</div>';
        $html .= renderTreePdf($mapaCargos, $levelColors, $cid, $depth + 1, $visitados);
    }
    return $html;
}

$visitados = [];
$raizes = [];
foreach ($todosCargos as $c) {
    if (!isset($idsNaArvore[$c['cargoId']])) continue;
    $supId = $c['cargoSupervisorId'];
    if ($supId === null || !isset($mapaCargos[$supId]) || !isset($idsNaArvore[$supId])) {
        if (!isset($visitados[$c['cargoId']])) $raizes[] = $c;
    }
}
usort($raizes, fn($a, $b) => ($b['nivelOrdem'] ?? 0) <=> ($a['nivelOrdem'] ?? 0) ?: strcmp($a['cargoNome'], $b['cargoNome']));

$visitadosPdf = [];
$treePdfHtml  = '';
foreach ($raizes as $raiz) {
    $cid = $raiz['cargoId'];
    if (isset($visitadosPdf[$cid])) continue;
    $visitadosPdf[$cid] = true;
    $color = levelColor($levelColors, (int)($raiz['nivelOrdem'] ?? 0));
    $nivel = htmlspecialchars($raiz['nivelDescricao'] ?? 'Sem Nível');
    $nome  = htmlspecialchars($raiz['cargoNome']);
    $treePdfHtml .= '<div style="margin-bottom:6px;">';
    $treePdfHtml .= '<span style="display:inline-block;background:' . $color . ';color:white;border-radius:4px;padding:4px 10px;font-size:9.5px;font-weight:bold;">' . $nome . '</span>';
    $treePdfHtml .= '<span style="margin-left:6px;font-size:8px;color:#666;">' . $nivel . '</span>';
    $treePdfHtml .= '</div>';
    $treePdfHtml .= renderTreePdf($mapaCargos, $levelColors, $cid, 1, $visitadosPdf);
}

// ── DADOS DE SETOR ───────────────────────────────────────────────────────────
$stmtAreas = $pdo->query('SELECT "areaId", "areaNome", "areaPaiId" FROM areas_atuacao ORDER BY "areaNome"');
$todasAreas = $stmtAreas->fetchAll(PDO::FETCH_ASSOC);
$mapaAreas  = [];
foreach ($todasAreas as $a) $mapaAreas[$a['areaId']] = $a;

$stmtCA = $pdo->query('SELECT "cargoId", "areaId" FROM cargos_area');
$relCA  = $stmtCA->fetchAll(PDO::FETCH_ASSOC);
$cargosPorArea = [];
$cargosComAreaSet = [];
foreach ($relCA as $r) {
    $cargosPorArea[$r['areaId']][] = $r['cargoId'];
    $cargosComAreaSet[$r['cargoId']] = true;
}

function renderAreaTreePdf(
    array $mapaAreas, array $cargosPorArea, array $mapaCargos, array $levelColors,
    int $areaId, int $depth, array &$visited
): string {
    if (isset($visited[$areaId])) return '';
    $visited[$areaId] = true;
    $area = $mapaAreas[$areaId] ?? null;
    if (!$area) return '';

    $indent  = $depth * 14;
    $bgSize  = $depth === 0 ? '10' : '9';
    $html    = '';

    // Cabeçalho da área
    $html .= '<div style="margin-left:' . $indent . 'px;margin-bottom:3px;margin-top:' . ($depth === 0 ? '10' : '4') . 'px;">';
    $html .= '<span style="display:inline-block;background:#546e7a;color:white;border-radius:3px;padding:2px 8px;font-size:' . $bgSize . 'px;font-weight:bold;">';
    $html .= htmlspecialchars($area['areaNome']);
    $html .= '</span></div>';

    // Sub-áreas recursivas
    $subAreas = array_filter($mapaAreas, fn($a) => $a['areaPaiId'] == $areaId);
    usort($subAreas, fn($a, $b) => strcmp($a['areaNome'], $b['areaNome']));
    foreach ($subAreas as $sa) {
        $html .= renderAreaTreePdf($mapaAreas, $cargosPorArea, $mapaCargos, $levelColors, $sa['areaId'], $depth + 1, $visited);
    }

    // Cargos desta área
    $cids = $cargosPorArea[$areaId] ?? [];
    $cargosArea = array_filter(array_map(fn($cid) => $mapaCargos[$cid] ?? null, $cids));
    usort($cargosArea, fn($a, $b) => ($b['nivelOrdem'] ?? 0) <=> ($a['nivelOrdem'] ?? 0) ?: strcmp($a['cargoNome'], $b['cargoNome']));
    foreach ($cargosArea as $c) {
        $cor   = levelColor($levelColors, (int)($c['nivelOrdem'] ?? 0));
        $nivel = htmlspecialchars($c['nivelDescricao'] ?? 'Sem Nível');
        $html .= '<div style="margin-left:' . ($indent + 14) . 'px;margin-bottom:2px;display:flex;align-items:center;">';
        $html .= '<span style="color:#aaa;font-size:8px;margin-right:4px;">└</span>';
        $html .= '<span style="display:inline-block;background:' . $cor . ';color:white;border-radius:3px;padding:2px 6px;font-size:8.5px;font-weight:bold;">';
        $html .= htmlspecialchars($c['cargoNome']);
        $html .= '</span>';
        $html .= '<span style="margin-left:5px;font-size:8px;color:#666;">' . $nivel . '</span>';
        $html .= '</div>';
    }

    return $html;
}

$visitadosArea = [];
$setorPdfHtml  = '';
$raizesAreas   = array_filter($todasAreas, fn($a) => $a['areaPaiId'] === null || !isset($mapaAreas[$a['areaPaiId']]));
usort($raizesAreas, fn($a, $b) => strcmp($a['areaNome'], $b['areaNome']));
foreach ($raizesAreas as $ra) {
    $setorPdfHtml .= renderAreaTreePdf($mapaAreas, $cargosPorArea, $mapaCargos, $levelColors, $ra['areaId'], 0, $visitadosArea);
}

// Cargos sem área
$cargosSemSetor = array_filter($todosCargos, fn($c) => !isset($cargosComAreaSet[$c['cargoId']]));
usort($cargosSemSetor, fn($a, $b) => ($b['nivelOrdem'] ?? 0) <=> ($a['nivelOrdem'] ?? 0) ?: strcmp($a['cargoNome'], $b['cargoNome']));

// ── MÉTRICAS ─────────────────────────────────────────────────────────────────
$dataGeracao   = date('d/m/Y \à\s H:i');
$totalVinculados = count($idsNaArvore);
$totalSemVinculo = count($naoVinculados);
$totalGeral    = count($todosCargos);
$totalAreas    = count($todasAreas);
$totalComSetor = count($cargosComAreaSet);
$totalSemSetor = count($cargosSemSetor);

// ── HTML DO PDF ───────────────────────────────────────────────────────────────
$html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#222; padding:20px; }
  .cabecalho { border-bottom:3px solid #1a237e; padding-bottom:10px; margin-bottom:18px; }
  .cabecalho h1 { font-size:18px; color:#1a237e; margin-bottom:2px; }
  .cabecalho p  { font-size:9px; color:#666; }
  .secao-titulo {
    color:white; padding:6px 10px; font-size:11px; font-weight:bold;
    border-radius:4px; margin-bottom:10px; margin-top:16px;
  }
  .secao-sup  { background:#1a237e; }
  .secao-set  { background:#546e7a; }
  .secao-warn { background:#e65100; }
  .stats { display:flex; gap:16px; margin-bottom:14px; }
  .stat-box { border:1px solid #ddd; border-radius:6px; padding:8px 14px; text-align:center; flex:1; }
  .stat-box .num { font-size:22px; font-weight:bold; color:#1a237e; }
  .stat-box .lab { font-size:8px; color:#666; }
  .tree-section { page-break-inside:avoid; }
  .nivel-header { border-left:4px solid; padding:3px 8px; font-size:9px; font-weight:bold; margin-top:10px; margin-bottom:4px; background:#f8f9fa; }
  .cargo-row { padding:2px 8px; font-size:9px; border-bottom:1px solid #f0f0f0; }
  .cargo-row:nth-child(even) { background:#fafafa; }
  .rodape { margin-top:20px; border-top:1px solid #ddd; padding-top:8px; font-size:8px; color:#999; text-align:center; }
  .page-break { page-break-before:always; }
</style>
</head>
<body>

<div class="cabecalho">
  <h1>&#128194; ORGANOGRAMA — ITACITRUS</h1>
  <p>Gerado em ' . $dataGeracao . ' &nbsp;|&nbsp; Total de cargos: <strong>' . $totalGeral . '</strong></p>
</div>

<div class="stats">
  <div class="stat-box"><div class="num" style="color:#1a237e;">' . $totalVinculados . '</div><div class="lab">Cargos na Hierarquia</div></div>
  <div class="stat-box"><div class="num" style="color:#546e7a;">' . $totalAreas . '</div><div class="lab">Áreas de Atuação</div></div>
  <div class="stat-box"><div class="num" style="color:#558b2f;">' . $totalComSetor . '</div><div class="lab">Cargos com Área</div></div>
  <div class="stat-box"><div class="num" style="color:#9e9e9e;">' . $totalGeral . '</div><div class="lab">Total Geral</div></div>
</div>';

// Legenda de cores
$html .= '<div style="font-size:8px;color:#555;margin-bottom:12px;">
<strong>Legenda de níveis:</strong>&nbsp;&nbsp;';
$legendas = [7=>'Diretor',6=>'Gerente',5=>'Coordenador',4=>'Supervisor/Analista',3=>'Encarregado',2=>'Assistente',1=>'Auxiliar/Operacional'];
foreach ($legendas as $ord => $nome) {
    $cor = $levelColors[$ord];
    $html .= '<span style="background:'.$cor.';color:white;border-radius:3px;padding:1px 5px;margin-right:4px;">'.$nome.'</span>';
}
$html .= '&nbsp;&nbsp;<span style="background:#546e7a;color:white;border-radius:3px;padding:1px 5px;">Área/Setor</span>';
$html .= '</div>';

// ── SEÇÃO 1: HIERARQUIA DE SUPERVISÃO ──────────────────────────────────────
if (!empty($treePdfHtml)):
    $html .= '<div class="secao-titulo secao-sup">&#128200; ESTRUTURA HIERÁRQUICA DE SUPERVISÃO</div>';
    $html .= '<div class="tree-section">' . $treePdfHtml . '</div>';
endif;

if (!empty($naoVinculadosPorNivel)):
    $html .= '<div class="secao-titulo secao-warn">&#9888; CARGOS SEM VÍNCULO HIERÁRQUICO (' . $totalSemVinculo . ')</div>';
    $html .= '<p style="font-size:8px;color:#888;margin-bottom:8px;">Estes cargos precisam ter o campo &quot;Reporta-se a&quot; preenchido no cadastro.</p>';
    foreach ($naoVinculadosPorNivel as $nivel => $dados) {
        $cor = $levelColors[$dados['ordem']] ?? $levelColors[0];
        $html .= '<div class="nivel-header" style="border-color:' . $cor . ';color:' . $cor . ';">' . htmlspecialchars($nivel) . ' (' . count($dados['cargos']) . ')</div>';
        foreach ($dados['cargos'] as $c) {
            $html .= '<div class="cargo-row">' . htmlspecialchars($c['cargoNome']) . '</div>';
        }
    }
endif;

// ── SEÇÃO 2: ORGANIZAÇÃO POR SETOR ────────────────────────────────────────
$html .= '<div class="page-break"></div>';
$html .= '<div class="secao-titulo secao-set">&#128193; ORGANIZAÇÃO POR ÁREA DE ATUAÇÃO</div>';

if (!empty($setorPdfHtml)):
    $html .= '<div class="tree-section">' . $setorPdfHtml . '</div>';
else:
    $html .= '<p style="font-size:9px;color:#888;">Nenhum cargo vinculado a áreas de atuação.</p>';
endif;

if (!empty($cargosSemSetor)):
    $html .= '<div class="secao-titulo secao-warn" style="margin-top:14px;">&#9888; CARGOS SEM ÁREA DE ATUAÇÃO (' . $totalSemSetor . ')</div>';
    $html .= '<p style="font-size:8px;color:#888;margin-bottom:8px;">Estes cargos precisam ser vinculados a uma Área de Atuação no cadastro do cargo.</p>';
    foreach ($cargosSemSetor as $c) {
        $cor   = $levelColors[(int)($c['nivelOrdem'] ?? 0)] ?? $levelColors[0];
        $nivel = htmlspecialchars($c['nivelDescricao'] ?? 'Sem Nível');
        $html .= '<div class="cargo-row"><span style="background:' . $cor . ';color:white;border-radius:3px;padding:1px 5px;font-size:8px;">' . htmlspecialchars($c['cargoNome']) . '</span> <span style="color:#888;font-size:8px;">' . $nivel . '</span></div>';
    }
endif;

$html .= '<div class="rodape">ITACITRUS · Plano de Cargos e Salários · Documento gerado automaticamente em ' . $dataGeracao . '</div>';
$html .= '</body></html>';

// ── GERA PDF ─────────────────────────────────────────────────────────────────
$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Organograma_ITACITRUS_' . date('Ymd_Hi') . '.pdf';
$dompdf->stream($filename, ['Attachment' => false]);
