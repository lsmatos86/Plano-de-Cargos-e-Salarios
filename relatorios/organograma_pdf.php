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

// Identifica quais fazem parte da árvore
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

// Mapa de cores por nível
$levelColors = [
    99 => '#495057', 7 => '#1a237e', 6 => '#283593',
    5  => '#0277bd', 4 => '#00838f', 3 => '#558b2f',
    2  => '#ef6c00', 1 => '#616161', 0 => '#9e9e9e',
];

// Função para obter cor do nível
function levelColor(array $levelColors, ?int $ordem): string {
    return $levelColors[$ordem] ?? $levelColors[0];
}

// Função recursiva que renderiza a árvore como HTML para PDF
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
            $html .= '<span style="color:#aaa;font-size:9px;margin-right:4px;">'.str_repeat('&nbsp;&nbsp;', $depth).'└</span>';
        }
        $html .= '<span style="display:inline-block;background:' . $color . ';color:white;border-radius:4px;padding:3px 8px;font-size:9px;font-weight:bold;">';
        $html .= $nome;
        $html .= '</span>';
        $html .= '<span style="margin-left:6px;font-size:8px;color:#666;">' . $nivel . '</span>';
        $html .= '</div>';

        // Filhos recursivos
        $html .= renderTreePdf($mapaCargos, $levelColors, $cid, $depth + 1, $visitados);
    }
    return $html;
}

// Encontra raízes da árvore
$visitados = [];
$raizes = [];
foreach ($todosCargos as $c) {
    if (!isset($idsNaArvore[$c['cargoId']])) continue;
    $supId = $c['cargoSupervisorId'];
    if ($supId === null || !isset($mapaCargos[$supId]) || !isset($idsNaArvore[$supId])) {
        if (!isset($visitados[$c['cargoId']])) {
            $raizes[] = $c;
        }
    }
}
usort($raizes, fn($a, $b) => ($b['nivelOrdem'] ?? 0) <=> ($a['nivelOrdem'] ?? 0) ?: strcmp($a['cargoNome'], $b['cargoNome']));

$visitadosPdf = [];
$treePdfHtml = '';
foreach ($raizes as $raiz) {
    $cid = $raiz['cargoId'];
    if (isset($visitadosPdf[$cid])) continue;
    $visitadosPdf[$cid] = true;
    $color = levelColor($levelColors, (int)($raiz['nivelOrdem'] ?? 0));
    $nivel = htmlspecialchars($raiz['nivelDescricao'] ?? 'Sem Nível');
    $nome  = htmlspecialchars($raiz['cargoNome']);
    $treePdfHtml .= '<div style="margin-bottom:6px;">';
    $treePdfHtml .= '<span style="display:inline-block;background:' . $color . ';color:white;border-radius:4px;padding:4px 10px;font-size:9.5px;font-weight:bold;">';
    $treePdfHtml .= $nome . '</span>';
    $treePdfHtml .= '<span style="margin-left:6px;font-size:8px;color:#666;">' . $nivel . '</span>';
    $treePdfHtml .= '</div>';
    $treePdfHtml .= renderTreePdf($mapaCargos, $levelColors, $cid, 1, $visitadosPdf);
}

$dataGeracao = date('d/m/Y \à\s H:i');
$totalVinculados = count($idsNaArvore);
$totalSemVinculo = count($naoVinculados);
$totalGeral = count($todosCargos);

// Monta HTML para o dompdf
$html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#222; padding:20px; }

  .cabecalho { border-bottom:3px solid #1a237e; padding-bottom:10px; margin-bottom:18px; }
  .cabecalho h1 { font-size:18px; color:#1a237e; margin-bottom:2px; }
  .cabecalho p { font-size:9px; color:#666; }

  .secao-titulo {
    background:#1a237e; color:white; padding:6px 10px;
    font-size:11px; font-weight:bold; border-radius:4px;
    margin-bottom:10px; margin-top:16px;
  }
  .stats { display:flex; gap:16px; margin-bottom:14px; }
  .stat-box {
    border:1px solid #ddd; border-radius:6px; padding:8px 14px;
    text-align:center; flex:1;
  }
  .stat-box .num { font-size:22px; font-weight:bold; color:#1a237e; }
  .stat-box .lab { font-size:8px; color:#666; }

  .tree-section { page-break-inside:avoid; }

  .nivel-header {
    border-left:4px solid;
    padding:3px 8px; font-size:9px;
    font-weight:bold; margin-top:10px; margin-bottom:4px;
    background:#f8f9fa;
  }
  .cargo-row {
    padding:2px 8px; font-size:9px; border-bottom:1px solid #f0f0f0;
  }
  .cargo-row:nth-child(even) { background:#fafafa; }

  .rodape { margin-top:20px; border-top:1px solid #ddd; padding-top:8px; font-size:8px; color:#999; text-align:center; }
</style>
</head>
<body>

<div class="cabecalho">
  <h1><span style="color:#1a237e;">&#128194;</span> ORGANOGRAMA — ITACITRUS</h1>
  <p>Gerado em ' . $dataGeracao . ' &nbsp;|&nbsp; Total de cargos: <strong>' . $totalGeral . '</strong></p>
</div>

<div class="stats">
  <div class="stat-box"><div class="num" style="color:#1a237e;">' . $totalVinculados . '</div><div class="lab">Cargos na Árvore</div></div>
  <div class="stat-box"><div class="num" style="color:#ef6c00;">' . $totalSemVinculo . '</div><div class="lab">Sem Vínculo</div></div>
  <div class="stat-box"><div class="num" style="color:#558b2f;">' . $totalGeral . '</div><div class="lab">Total Geral</div></div>
</div>';

// Legenda de cores
$html .= '<div style="font-size:8px;color:#555;margin-bottom:12px;">
<strong>Legenda de níveis:</strong>&nbsp;&nbsp;';
$legendas = [7=>'Diretor',6=>'Gerente',5=>'Coordenador',4=>'Supervisor/Analista',3=>'Encarregado',2=>'Assistente',1=>'Auxiliar/Operacional'];
foreach ($legendas as $ord => $nome) {
    $cor = $levelColors[$ord];
    $html .= '<span style="background:'.$cor.';color:white;border-radius:3px;padding:1px 5px;margin-right:4px;">'.$nome.'</span>';
}
$html .= '</div>';

if (!empty($treePdfHtml)):
    $html .= '<div class="secao-titulo">&#128200; ESTRUTURA HIERÁRQUICA</div>';
    $html .= '<div class="tree-section">' . $treePdfHtml . '</div>';
endif;

if (!empty($naoVinculadosPorNivel)):
    $html .= '<div class="secao-titulo">&#9888; CARGOS SEM VÍNCULO HIERÁRQUICO (' . $totalSemVinculo . ')</div>';
    $html .= '<p style="font-size:8px;color:#888;margin-bottom:8px;">Estes cargos precisam ter o campo &quot;Reporta-se a&quot; preenchido no cadastro.</p>';
    foreach ($naoVinculadosPorNivel as $nivel => $dados) {
        $cor = $levelColors[$dados['ordem']] ?? $levelColors[0];
        $html .= '<div class="nivel-header" style="border-color:' . $cor . ';color:' . $cor . ';">' . htmlspecialchars($nivel) . ' (' . count($dados['cargos']) . ')</div>';
        foreach ($dados['cargos'] as $c) {
            $html .= '<div class="cargo-row">' . htmlspecialchars($c['cargoNome']) . '</div>';
        }
    }
endif;

$html .= '<div class="rodape">ITACITRUS · Plano de Cargos e Salários · Documento gerado automaticamente em ' . $dataGeracao . '</div>';
$html .= '</body></html>';

// Gera PDF
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
