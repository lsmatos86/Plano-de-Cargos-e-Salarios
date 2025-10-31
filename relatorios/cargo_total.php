<?php
// Arquivo: relatorios/cargo_total.php (REFATORADO)

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // Para isUserLoggedIn e getRiscoIcon
require_once 'pdf_generator.php'; 

// 2. Importa o Repositório
use App\Repository\CargoRepository;

if (!isUserLoggedIn()) {
    die("Acesso Negado.");
}

// 3. AUMENTAR LIMITE DE EXECUÇÃO E MEMÓRIA (Mantido)
ini_set('max_execution_time', 300); // 5 minutos
ini_set('memory_limit', '512M'); 

// 4. Instancia o Repositório
$cargoRepo = new CargoRepository();

$output_format = $_GET['format'] ?? 'html'; // 'html' ou 'pdf'
$start_generation = isset($_GET['generate']) && $_GET['generate'] === 'true';

// ----------------------------------------------------
// LÓGICA DE CARREGAMENTO DOS DADOS BASE (REFATORADO)
// ----------------------------------------------------
try {
    // Busca apenas IDs e Nomes usando o repositório
    $cargos_base = $cargoRepo->findAllIdsAndNames();
    $total_cargos = count($cargos_base);
} catch (Exception $e) {
    die("Erro fatal ao carregar lista de cargos: " . $e->getMessage());
}


// ----------------------------------------------------
// FASE 2: GERAÇÃO DO PDF/HTML (Inicia se ?generate=true)
// ----------------------------------------------------
if ($start_generation) {
    
    // Inicia o buffer de saída para capturar todo o HTML
    ob_start(); 
    
    // --- Início do HTML ---
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Relatório Consolidado de Cargos</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        
        <style>
            /* ESTILOS ... (Mantidos do arquivo original) */
            /* BASE GERAL */
            body { font-family: 'Helvetica', sans-serif; font-size: 10pt; padding: 0; margin: 0; line-height: 1.4; }
            .container { width: 90%; margin: 0 auto; }
            
            /* CABEÇALHO E RODAPÉ */
            .report-header-final { text-align: center; margin-bottom: 25px; }
            .cargo-nome-principal { font-size: 16pt; color: #000; font-weight: bold; display: block; margin-bottom: 5px; }
            .cbo-detail { font-size: 10pt; color: #555; margin-bottom: 20px; text-align: center; } 
            
            /* NOVO: Quebra de página */
            .page-break { page-break-after: always; }
            
            /* SEÇÕES DO RELATÓRIO */
            .h2-custom { font-size: 13pt; color: #198754; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 25px; margin-bottom: 10px; font-weight: bold; page-break-after: avoid; }
            .h5-custom { font-size: 11pt; font-weight: bold; color: #333; margin-top: 0; margin-bottom: 5px; page-break-after: avoid; }
            .data-label { font-weight: bold; width: 120px; color: #444; }
            .h2-custom::before { content: ""; display: block; width: 100%; height: 1px; background: #e9ecef; margin-bottom: 15px; }

            /* Estrutura de Bloco */
            .grid-section { border: none; margin-bottom: 20px; page-break-inside: avoid; }
            .grid-content { padding: 5px 10px; }
            .block-wrapper { width: 100%; margin-bottom: 5px; display: block; } 
            
            /* GRIDS E TABELAS INTERNAS */
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 6px 8px; text-align: left; vertical-align: top; font-size: 9pt; border-bottom: 1px solid #f2f2f2; }
            th { background-color: #f2ffef; font-weight: bold; }
            
            /* Estilos de Conteúdo */
            .habilidade-list { list-style: none; padding-left: 15px; margin-top: 0; }
            .hardskill-item::before { content: "\25A0"; color: #333; font-size: 0.8em; display: inline-block; width: 1em; margin-left: -1em; }
            .softskill-item::before { content: "\2022"; color: #888; font-size: 1em; display: inline-block; width: 1em; margin-left: -1em; }
            .simple-list { list-style: disc; padding-left: 20px; margin-top: 0; }
            .wysiwyg-content { margin-top: 5px; padding: 10px; background-color: #fff; border: 1px solid #eee; }
        </style>
    </head>
    <body>

    <script type="text/php">
        // Paginação (mantida do arquivo original)
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $size = 9;
            $y = 35;
            $x = 510; 
            $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size);
        }
    </script>
    
    <div class="container">
    <?php
    // --- Fim do Cabeçalho HTML ---
    
    // ----------------------------------------------------
    // LOOP DE GERAÇÃO (REFATORADO)
    // ----------------------------------------------------
    $count = 0;
    foreach ($cargos_base as $cargo_info) {
        $count++;
        $cargo_id = (int)$cargo_info['cargoId'];
        
        // Chamada REFATORADA: Usa o repositório
        $data = $cargoRepo->findReportData($cargo_id);

        // Se falhar em buscar este cargo, pula para o próximo
        if (!$data || !isset($data['cargo'])) {
            echo "<div class='page-break'><h1>Erro ao processar Cargo ID {$cargo_id}: " . htmlspecialchars($cargo_info['cargoNome']) . "</h1><p>Cargo não encontrado ou dados insuficientes.</p></div>";
            continue;
        }

        // Prepara dados locais (lógica mantida)
        $cargo = $data['cargo'];
        $soft_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Softskill');
        $hard_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Hardskill');
        
        // --- Início do HTML do Cargo Individual ---
        // (Este é o mesmo layout HTML de 'gerador_pdf.php')
        ?>
        <div class="report-header-final">
            <span class="cargo-nome-principal"><?php echo htmlspecialchars($cargo['cargoNome']); ?></span>
            <p class="cbo-detail">
                <strong style="color: #444;">CBO:</strong> <?php echo htmlspecialchars($cargo['cboCod'] ?? 'N/A'); ?> - 
                <?php echo htmlspecialchars($cargo['cboTituloOficial'] ?? 'N/A'); ?>
            </p>
        </div>

        <h2 class="h2-custom">1. Informações Essenciais</h2>
        <div class="grid-section">
            <h5 class="h5-custom">Descrição Sumária</h5>
            <div class="grid-content wysiwyg-content">
                <?php echo $cargo['cargoResumo'] ?? 'N/A'; ?> 
            </div>
        </div>
        <table>
            <tr><td class="data-label"><strong>Escolaridade</strong>:</td><td><?php echo htmlspecialchars($cargo['escolaridadeTitulo'] ?? 'N/A'); ?></td></tr>
            <tr><td class="data-label"><strong>Sinônimos</strong>:</td><td><?php echo empty($data['sinonimos']) ? 'Nenhum' : implode(', ', $data['sinonimos']); ?></td></tr>
        </table>

        <h2 class="h2-custom">2. Habilidades e Competências</h2>
        <div class="block-wrapper">
            <h5 class="h5-custom">Habilidades Técnicas (HARD SKILLS)</h5>
            <ul class="habilidade-list">
            <?php foreach ($hard_skills as $h): ?>
                <li class="hardskill-item"><?php echo htmlspecialchars($h['habilidadeNome']); ?></li>
            <?php endforeach; if (empty($hard_skills)) echo "<li>Nenhuma.</li>"; ?>
            </ul>
        </div>
        <div class="block-wrapper">
            <h5 class="h5-custom">Competências Comportamentais (SOFT SKILLS)</h5>
             <ul class="habilidade-list">
            <?php foreach ($soft_skills as $h): ?>
                <li class="softskill-item"><?php echo htmlspecialchars($h['habilidadeNome']); ?></li>
            <?php endforeach; if (empty($soft_skills)) echo "<li>Nenhuma.</li>"; ?>
            </ul>
        </div>
        
        <?php
        // --- Fim do HTML do Cargo Individual ---

        // Adiciona quebra de página se não for o último cargo
        if ($count < $total_cargos) {
            echo '<div class="page-break"></div>';
        }
    } // Fim do loop foreach

    // --- Início do HTML de Rodapé ---
    ?>
    </div> </body>
    </html>
    <?php
    // --- Fim do HTML de Rodapé ---

    // 5. CAPTURA O HTML E GERA A SAÍDA
    $html_content = ob_get_clean(); 

    if ($output_format === 'pdf') {
        $filename = "RELATORIO_CONSOLIDADO_CARGOS_" . date('Ymd_His');
        generatePdfFromHtml($html_content, $filename, true); // Força o download
        exit;
    } else {
        // Se for HTML, apenas exibe
        echo $html_content;
        exit;
    }
} // Fim do if($start_generation)


// ----------------------------------------------------
// FASE 1: PÁGINA DE PRELOADER (HTML)
// ----------------------------------------------------
// (Esta parte é exibida se ?generate=true NÃO estiver na URL)

$pdf_generation_url = "cargo_total.php?generate=true&format=pdf";
$html_generation_url = "cargo_total.php?generate=true&format=html";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerando Relatório Consolidado...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* (Estilos do modal mantidos do arquivo original) */
        #preloaderModal .modal-dialog { transform: translateY(80px); }
        #preloaderModal .icon-container { font-size: 4rem; color: #0d6efd; }
        #preloaderModal .completed .icon-container { color: #198754; }
        #preloaderModal .completed .spinner-border { display: none; }
        #preloaderModal .final-message { display: none; }
        #preloaderModal .completed .final-message { display: block; }
        #preloaderModal .completed .progress-container { display: none; }
    </style>
</head>
<body class="bg-light">

<div class="modal fade" id="preloaderModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                
                <div class="progress-container">
                    <div class="icon-container mb-3">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h4 class="mb-3">Gerando Relatório Consolidado</h4>
                    <p class="text-muted">Isso pode levar alguns minutos, dependendo da quantidade de cargos (<?php echo $total_cargos; ?> cargos).</p>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0 progress-text">Aguarde, processando...</p>
                    <p class="text-muted small">Por favor, não feche esta janela.</p>
                </div>

                <div class="final-message">
                    <div class="icon-container mb-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4 class="mb-3 text-success">Relatório Iniciado!</h4>
                    <p class="text-muted">O download do seu relatório consolidado deve ter iniciado na aba/janela separada.</p>
                    <button class="btn btn-primary mt-3" onclick="window.close();">
                         <i class="fas fa-times-circle"></i> Fechar Janela
                    </button>
                    <p class="mt-3 text-muted small">Se o download não iniciou, verifique se seu navegador bloqueou pop-ups ou downloads automáticos.</p>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalElement = document.getElementById('preloaderModal');
        var modal = new bootstrap.Modal(modalElement);
        
        // 1. Decide qual URL usar (PDF ou HTML)
        var urlParams = new URLSearchParams(window.location.search);
        var format = urlParams.get('format') || 'pdf';
        var generationUrl = (format === 'pdf') ? '<?php echo $pdf_generation_url; ?>' : '<?php echo $html_generation_url; ?>';
        
        var progressText = modalElement.querySelector('.progress-text');
        
        // 2. Mostra o modal
        modal.show();

        // 3. Abre a nova janela para a geração (que força o download)
        progressText.innerHTML = 'Iniciando geração... (Download em nova aba)';
        var newWindow = window.open(generationUrl, '_blank');

        if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
             // Se o Pop-up foi bloqueado
             progressText.innerHTML = '<strong class="text-danger">ERRO: Seu navegador bloqueou a abertura da nova janela. Por favor, habilite pop-ups para este site e tente novamente.</strong>';
             modalElement.querySelector('.spinner-border').style.display = 'none';
        } else {
             // 4. Exibe a mensagem de conclusão
            setTimeout(function() {
                modalElement.classList.add('completed');
            }, 1500); // Delay para UX
        }
    });
</script>

</body>
</html>