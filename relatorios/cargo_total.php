<?php
// Arquivo: relatorios/cargo_total.php (Gera um PDF/HTML com todos os cargos)

require_once '../config.php';
require_once '../includes/functions.php';
// NOTA: Assumindo que 'pdf_generator.php' lida com a inclusão do Dompdf (vendor/autoload.php)
require_once 'pdf_generator.php'; 

if (!isUserLoggedIn()) {
    die("Acesso Negado.");
}

// 1. AUMENTAR LIMITE DE EXECUÇÃO E MEMÓRIA (CRUCIAL PARA RELATÓRIOS GRANDES)
ini_set('max_execution_time', 300); // 5 minutos
ini_set('memory_limit', '512M'); 

$pdo = getDbConnection();
$output_format = $_GET['format'] ?? 'html'; // 'html' ou 'pdf'
$start_generation = isset($_GET['generate']) && $_GET['generate'] === 'true';

// ----------------------------------------------------
// LÓGICA DE CARREGAMENTO DOS DADOS BASE
// ----------------------------------------------------
try {
    // Busca apenas IDs e Nomes para a iteração (mais leve)
    $stmt = $pdo->query("SELECT cargoId, cargoNome FROM cargos ORDER BY cargoNome ASC");
    $cargos_base = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_cargos = count($cargos_base);
} catch (Exception $e) {
    die("Erro ao buscar cargos: " . $e->getMessage());
}

if ($total_cargos === 0) {
    die("<h1 style='text-align:center; margin-top:50px;'>Nenhum cargo encontrado para gerar o relatório.</h1>");
}

// ----------------------------------------------------
// 2. FUNÇÃO AUXILIAR PARA RENDERIZAR O HTML DE UM CARGO
//    (Baseado na estrutura de cargo_individual.php para consistência)
// ----------------------------------------------------
/**
 * Renderiza o HTML completo para um único cargo no formato de relatório.
 * @param array $data Os dados completos do cargo (retorno de getCargoReportData).
 * @param int $current_index O índice atual no loop (para o contador 1/N).
 * @param int $total_cargos O número total de cargos (N).
 * @return string O bloco HTML do cargo.
 */
function renderSingleCargoHtml(array $data, int $current_index, int $total_cargos): string 
{
    $cargo = $data['cargo'];
    $soft_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Softskill');
    $hard_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Hardskill');
    
    // Inicia o buffer para capturar o HTML da seção
    ob_start();
    ?>
    <div class="cargo-section-wrapper">
    
        <h1 class="cargo-section-title">
            <span class="cargo-nome-principal"><?php echo htmlspecialchars($cargo['cargoNome']); ?></span>
            <span class="cargo-progress">(Cargo <?php echo $current_index; ?> de <?php echo $total_cargos; ?>)</span>
        </h1>
        
        <p class="cbo-detail">
            <strong style="color: #444;">CBO:</strong> <?php echo htmlspecialchars($cargo['cboNome'] ?? 'N/A'); ?> - 
            <?php echo htmlspecialchars($cargo['cboTituloOficial'] ?? 'Título Oficial Não Disponível'); ?>
        </p>
    
        <h2 class="h2-custom"><i class="fas fa-id-card"></i> 1. Informações Essenciais</h2>
        
        <div class="grid-section">
            <h5 class="h5-custom"><i class="fas fa-file-alt"></i> Descrição Sumária</h5>
            <div class="grid-content wysiwyg-content">
                <?php echo $cargo['cargoResumo'] ?? 'N/A'; ?> 
            </div>
        </div>
        
        <table class="data-list w-100 mb-4">
            <tr><td class="data-label"><strong>Escolaridade</strong>:</td><td><?php echo htmlspecialchars($cargo['escolaridadeTitulo'] ?? 'N/A'); ?></td></tr>
            <tr><td class="data-label"><strong>Sinônimos</strong>:</td><td><?php echo empty($data['sinonimos']) ? 'Nenhum' : implode(', ', $data['sinonimos']); ?></td></tr>
        </table>

        <h2 class="h2-custom"><i class="fas fa-cogs"></i> 2. Habilidades e Competências</h2>
        
        <div class="block-wrapper">
            <div class="grid-section">
                <h5 class="h5-custom"><i class="fas fa-toolbox"></i> Habilidades Técnicas (HARD SKILLS)</h5>
                <div class="grid-content">
                    <ul class="habilidade-list">
                    <?php if (empty($hard_skills)): ?>
                        <li>Nenhuma Hard Skill associada.</li>
                    <?php endif; ?>
                    <?php foreach ($hard_skills as $h): 
                        $h_desc = htmlspecialchars($h['habilidadeDescricao'] ?? '');
                    ?>
                        <li class="hardskill-item">
                            <span class="habilidade-nome"><?php echo htmlspecialchars($h['habilidadeNome']); ?></span>
                            <?php if (!empty($h_desc)): ?>
                                <p class="habilidade-descricao"> - <?php echo $h_desc; ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="block-wrapper">
            <div class="grid-section">
                <h5 class="h5-custom"><i class="fas fa-users"></i> Competências Comportamentais (SOFT SKILLS)</h5>
                 <ul class="habilidade-list">
                <?php if (empty($soft_skills)): ?>
                    <li>Nenhuma Soft Skill associada.</li>
                <?php endif; ?>
                <?php foreach ($soft_skills as $h): 
                    $h_desc = htmlspecialchars($h['habilidadeDescricao'] ?? '');
                ?>
                    <li class="softskill-item">
                        <span class="habilidade-nome"><?php echo htmlspecialchars($h['habilidadeNome']); ?></span>
                        <?php if (!empty($h_desc)): ?>
                            <p class="habilidade-descricao"> - <?php echo $h_desc; ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <h2 class="h2-custom"><i class="fas fa-handshake"></i> 3. Qualificação e Caráter</h2>
        
        <div class="block-wrapper">
            <div class="grid-section">
                <h5 class="h5-custom"><i class="fas fa-certificate"></i> Cursos e Treinamentos</h5>
                <div class="grid-content">
                    <ul class="simple-list">
                        <?php if (empty($data['cursos'])): ?>
                            <li>Nenhum curso associado.</li>
                        <?php endif; ?>
                        <?php foreach ($data['cursos'] as $cur): ?>
                            <li class="recurso-item">
                                <strong style="font-size: 10pt;"><?php echo htmlspecialchars($cur['cursoNome']); ?></strong>
                                <span style="color: <?php echo $cur['cursoCargoObrigatorio'] ? 'red' : 'green'; ?>; font-size: 9pt;">
                                    (<?php echo $cur['cursoCargoObrigatorio'] ? 'OBRIGATÓRIO' : 'Recomendado'; ?>)
                                </span>
                                <?php echo !empty($cur['cursoCargoObs']) ? '<br><span style="font-size: 8pt; color: #555;">Observação: '. htmlspecialchars($cur['cursoCargoObs']) . '</span>' : ''; ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                </div>
            </div>
        </div>
        
        <div class="block-wrapper">
            <div class="grid-section">
                <h5 class="h5-custom"><i class="fas fa-user-tag"></i> Características Pessoais Desejáveis</h5>
                <div class="grid-content">
                    <ul class="simple-list">
                    <?php if (empty($data['caracteristicas'])): ?>
                        <li>Nenhuma característica associada.</li>
                    <?php endif; ?>
                    <?php foreach ($data['caracteristicas'] as $c): ?>
                        <li class="caracteristica-item">
                            <strong style="color: #333;"><?php echo htmlspecialchars($c['caracteristicaNome']); ?></strong>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <h2 class="h2-custom"><i class="fas fa-toolbox"></i> 4. Recursos e Riscos de Exposição</h2>
        
        <div class="block-wrapper">
            
            <div class="grid-section">
                <h5 class="h5-custom"><i class="fas fa-wrench"></i> Grupos de Recursos Utilizados</h5>
                <div class="grid-content">
                    <ul class="simple-list">
                        <?php if (empty($data['recursos_grupos'])): ?>
                            <li>Nenhum grupo de recurso associado.</li>
                        <?php endif; ?>
                        <?php foreach ($data['recursos_grupos'] as $rg): ?>
                            <li class="recurso-item"><?php echo htmlspecialchars($rg); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="block-wrapper">
            <div class="grid-section">
                <h5 class="h5-custom"><i class="fas fa-radiation-alt"></i> Riscos de Exposição</h5>
                <div class="grid-content">
                    <table>
                        <thead>
                            <tr>
                                <th width="30%">Tipo</th>
                                <th width="70%">Detalhe Específico</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['riscos'])): ?>
                                <tr><td colspan="2">Nenhum risco de exposição registrado.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($data['riscos'] as $r): ?>
                                <tr>
                                    <td><?php echo getRiscoIcon($r['riscoNome']); ?> <?php echo htmlspecialchars($r['riscoNome']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($r['riscoDescricao'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <h2 class="h2-custom"><i class="fas fa-book-open"></i> 5. Responsabilidades e Complexidade</h2>
        
        <div class="grid-section">
            <div class="grid-content">
                <p class="list-header"><i class="fas fa-clipboard-list"></i> Responsabilidades Detalhadas:</p>
                <div class="wysiwyg-content"><?php echo $cargo['cargoResponsabilidades'] ?? 'N/A'; ?></div>
                
                <p class="list-header"><i class="fas fa-cloud-sun"></i> Condições Gerais:</p>
                <div class="wysiwyg-content"><?php echo $cargo['cargoCondicoes'] ?? 'N/A'; ?></div>
                
                <p class="list-header"><i class="fas fa-layer-group"></i> Complexidade do Cargo:</p>
                <div class="wysiwyg-content"><?php echo $cargo['cargoComplexidade'] ?? 'N/A'; ?></div>
            </div>
        </div>

    </div>
    
    <?php // Adiciona quebra de página antes do próximo cargo, se não for o último
    if ($current_index < $total_cargos): ?>
        <div class="page-break"></div>
    <?php endif; 

    return ob_get_clean();
}


// ----------------------------------------------------
// 3. LÓGICA DO PDF (EXECUÇÃO PESADA)
// ----------------------------------------------------
if ($start_generation && $output_format === 'pdf') {
    
    $full_report_html = '';
    
    // Loop principal para geração de cada cargo
    foreach ($cargos_base as $index => $c_base) {
        $cargo_index = $index + 1;
        
        // **CHAMADA DE DADOS PESADA ACONTECE AQUI**
        $data = getCargoReportData($pdo, $c_base['cargoId']);
        
        if ($data) {
            // Renderiza o bloco HTML do cargo individual
            $full_report_html .= renderSingleCargoHtml($data, $cargo_index, $total_cargos);
        }
    }

    // Inicia o buffer para capturar o HTML final do PDF
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Relatório Consolidado de Cargos</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <style>
            /* BASE GERAL (CSS RÍGIDO DO PDF) */
            body { font-family: 'Helvetica', sans-serif; font-size: 10pt; padding: 0; margin: 0; line-height: 1.4; }
            .container { width: 90%; margin: 0 auto; }
            
            /* CABEÇALHO E RODAPÉ */
            .report-header-final { text-align: center; margin-bottom: 25px; }
            .cargo-section-title { font-size: 16pt; color: #000; font-weight: bold; display: block; margin-bottom: 5px; text-align: center; }
            .cargo-progress { font-size: 10pt; color: #198754; font-weight: normal; display: block; margin-top: 5px; }
            .cbo-detail { font-size: 10pt; color: #555; margin-bottom: 20px; text-align: center; } 
            .report-footer { position: fixed; bottom: 0; width: 90%; text-align: right; font-size: 7pt; color: #777; border-top: 1px solid #ccc; padding-top: 5px; }
            
            /* SEÇÕES DO RELATÓRIO */
            .h2-custom { font-size: 13pt; color: #198754; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 25px; margin-bottom: 10px; font-weight: bold; page-break-after: avoid; }
            .h5-custom { font-size: 11pt; font-weight: bold; color: #333; margin-top: 0; margin-bottom: 5px; page-break-after: avoid; }
            .data-label { font-weight: bold; width: 120px; color: #444; }
            .h2-custom::before { content: ""; display: block; width: 100%; height: 1px; background: #e9ecef; margin-bottom: 15px; }

            /* Estrutura de Bloco (FORÇA 100% DE LARGURA) */
            .grid-section { border: none; margin-bottom: 20px; page-break-inside: avoid; }
            .grid-content { padding: 5px 10px; }
            .block-wrapper { width: 100%; margin-bottom: 5px; }
            
            /* GRIDS E LISTAS */
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 6px 8px; text-align: left; vertical-align: top; font-size: 9pt; border-bottom: 1px solid #f2f2f2; }
            th { background-color: #f2ffef; font-weight: bold; }
            
            /* Estilos de Conteúdo */
            .habilidade-list, .simple-list { list-style: none; padding-left: 20px; margin-top: 0; }
            .hardskill-item::before, .softskill-item::before, .simple-list li::before { content: "\2022"; color: #198754; font-weight: bold; display: inline-block; width: 1em; margin-left: -1em; }
            .wysiwyg-content { margin-top: 5px; padding: 10px; background-color: #fff; border: 1px solid #eee; }
            .list-header { font-weight: bold; margin-bottom: 5px; margin-top: 10px; color: #333; }
            .habilidade-nome { font-weight: bold; font-size: 10pt; } 
            .habilidade-descricao { font-size: 8.5pt; color: #555; padding-top: 0; padding-bottom: 8px; line-height: 1.3; }
            .page-break { page-break-before: always; }
        </style>
    </head>
    <body>

        <script type="text/php">
            if ( isset($pdf) ) {
                $font = $fontMetrics->get_font("Helvetica", "normal");
                $size = 9;
                $y = 35; 
                $x = 510; 
                $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size);
            }
        </script>
        
        <div class="container">

            <div class="report-header-final" style="margin-top: 20px;">
                <h1>ITACITRUS | Relatório Consolidado</h1>
                <h3>Total de Cargos: <?php echo $total_cargos; ?></h3>
            </div>
        
            <?php echo $full_report_html; ?>

            <div class="report-footer">
                Relatório Consolidado Emitido em: <?php echo date('d/m/Y H:i:s'); ?>
            </div>

        </div>

    </body>
    </html>
    <?php
    $html = ob_get_clean();

    // 4. GERAÇÃO DO ARQUIVO PDF E DOWNLOAD
    $filename_final = "Relatorio_Cargos_Consolidado_" . date('YmdHi');
    generatePdfFromHtml($html, $filename_final, true);
    exit;

} 
// ----------------------------------------------------
// 4. LÓGICA DO HTML (MODAL DE CARREGAMENTO) - PRÉ-GERAÇÃO
// ----------------------------------------------------
else {
    $pdf_generation_url = '?format=pdf&generate=true';
    $total_cargos_text = $total_cargos == 1 ? "1 Cargo" : "{$total_cargos} Cargos";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Relatório Consolidado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .preloader-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            z-index: 1050;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        .progress-text {
            margin-top: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            color: #198754;
        }
    </style>
</head>
<body>

<div class="preloader-modal" id="preloaderModal">
    <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="progress-text">
        Preparando para gerar o Relatório Consolidado...
    </div>
    <p class="mt-3 text-muted">Total de Seções a processar: **<?php echo $total_cargos_text; ?>**</p>
    <p class="text-danger fw-bold">A geração do PDF pode levar alguns minutos. Não feche esta janela.</p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('preloaderModal');
        var progressText = modal.querySelector('.progress-text');
        
        progressText.innerHTML = 'Iniciando geração do PDF... (Iremos redirecionar)';
        
        // Simula o preloader por 2 segundos antes de iniciar o processo pesado
        setTimeout(function() {
            // Inicia o processo de geração do PDF
            window.location.href = '<?php echo $pdf_generation_url; ?>';
        }, 2000); 
    });
</script>

</body>
</html>
<?php
}