<?php
// Arquivo: relatorios/cargo_total.php (Relatório Consolidado de Todos os Cargos)

// 1. Inclua o autoload do Composer
require_once '../vendor/autoload.php';
require_once '../config.php'; 

// 2. Importe a classe
use App\Repository\CargoRepository;

// 3. Inclua functions.php (para login e helpers de ícones)
require_once '../includes/functions.php'; 

// 4. Segurança
if (!isUserLoggedIn()) {
    die("Acesso Negado.");
}

// 5. Aumentar limites de execução (necessário para relatórios longos)
set_time_limit(300); // 5 minutos
ini_set('memory_limit', '512M'); // Aumenta limite de memória

// 6. Buscar os dados
try {
    $cargoRepository = new CargoRepository(); 
    
    // 6.1. Busca TODOS os IDs e Nomes
    //
    $cargos = $cargoRepository->findAllIdsAndNames(); 

} catch (\Exception $e) {
    die("Erro ao carregar dados do repositório: " . $e->getMessage());
}

if (empty($cargos)) {
    die("Erro: Nenhum cargo encontrado.");
}

$totalCargos = count($cargos);
$data_emissao_consolidada = date('d/m/Y H:i:s');

// ----------------------------------------------------
// 7. GERAÇÃO DO HTML (Bufferizado)
// ----------------------------------------------------

ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Consolidado de Cargos</title>
    
    <style>
        <?php 
        // Carrega o CSS que já criamos
        $css_path = '../css/relatorio_style.css';
        if (file_exists($css_path)) {
            echo file_get_contents($css_path);
        } else {
            echo "/* ERRO: Não foi possível carregar o CSS */";
        }
        ?>
        
        /* CSS Específico para este relatório */
        .page-break {
            page-break-after: always;
        }
        body {
            font-size: 9pt; /* Reduz um pouco a fonte para o consolidado */
        }
    </style>
    
    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $font_size = 7; // Menor que 8pt
            $y = $pdf->get_height() - 35; // Posição Y (Dentro da margem inferior de 2cm)

            // Texto da Esquerda (Data de Emissão)
            $text_left = "Emitido em: <?php echo $data_emissao_consolidada; ?>";
            $width_left = $fontMetrics->get_text_width($text_left, $font, $font_size);
            $x_left = 85.0395; // Margem esquerda 3cm
            $pdf->text($x_left, $y, $text_left, $font, $font_size);

            // Texto da Direita (Paginação)
            $text_right = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $width_right = $fontMetrics->get_text_width($text_right, $font, $font_size);
            $x_right = $pdf->get_width() - 56.693 - $width_right; // Largura - Margem Direita (2cm) - Largura do texto
            $pdf->text($x_right, $y, $text_right, $font, $font_size);
        }
    </script>
</head>
<body class="pdf-render"> <div class="report-header-final" style="margin-top: 200px;">
        <span class="cargo-nome-principal">Relatório Consolidado de Cargos</span>
        <p class="cbo-detail" style="font-size: 14pt;">Total de Cargos: <?php echo $totalCargos; ?></p>
        <p class="cbo-detail" style="font-size: 11pt;">Emitido em: <?php echo $data_emissao_consolidada; ?></p>
    </div>
    
    <div class="page-break"></div>

    <?php
    // 8. Loop sobre os cargos
    $counter = 0;
    foreach ($cargos as $cargoItem):
        
        $counter++;
        
        // 8.1. Busca os dados completos para este cargo
        //
        $data = $cargoRepository->findReportData($cargoItem['cargoId']);
        
        if (!$data) continue; // Pula se o cargo não tiver dados
        
        // 8.2. Prepara as variáveis para o template
        $cargo = $data['cargo'];
        $soft_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Softskill');
        $hard_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Hardskill');
        
        // 8.3. Define a variável de controlo (NÃO MOSTRAR HIERARQUIA)
        $show_hierarquia = false; 
        
        // Inicializa o contador de secção DENTRO do loop
        $section_counter = 1;

        // 8.4. Inclui o template
        include '../includes/templates/_template_cargo.php'; 
    ?>
        
        <?php
        // Adiciona quebra de página, exceto no último item
        if ($counter < $totalCargos):
        ?>
            <div class="page-break"></div>
        <?php endif; ?>
        
    <?php endforeach; ?>

</body>
</html>
<?php
// Fim do buffer HTML
$html = ob_get_clean();

// ----------------------------------------------------
// 9. GERAÇÃO DO PDF
// ----------------------------------------------------

$timestamp = date('Ymd'); 
$filename_final = "RELATORIO_CONSOLIDADO_CARGOS_{$timestamp}";

// Opções do Dompdf
$options = new \Dompdf\Options();
$options->set('isRemoteEnabled', true); 
$options->set('defaultFont', 'Helvetica');

try {
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Abre o PDF no navegador (Attachment = false)
    $dompdf->stream($filename_final . ".pdf", ["Attachment" => false]); 
    
} catch (Exception $e) {
    die("Erro ao gerar PDF: " . $e->getMessage());
}

exit;
?>