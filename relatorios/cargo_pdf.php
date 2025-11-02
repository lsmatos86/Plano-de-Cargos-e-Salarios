<?php
// Arquivo: relatorios/cargo_pdf.php (Gerador de PDF Simples)

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

// 6. Obter Parâmetros
$cargo_id = (int)($_GET['id'] ?? 0);
if ($cargo_id <= 0) {
    die("Erro: ID de cargo inválido.");
}

// 7. Buscar os dados
try {
    $cargoRepository = new CargoRepository(); 
    //
    $data = $cargoRepository->findReportData($cargo_id); 
} catch (\Exception $e) {
    die("Erro ao carregar dados do repositório: " . $e->getMessage());
}

if (!$data) {
    die("Erro: Cargo ID {$cargo_id} não encontrado ou dados insuficientes.");
}

// 8. Preparar variáveis
$cargo = $data['cargo'];
$soft_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Softskill');
$hard_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Hardskill');

// Prepara as variáveis para o rodapé
$data_emissao = date('d/m/Y H:i:s');
$data_atualizacao = !empty($cargo['cargoDataAtualizacao']) ? (new DateTime($cargo['cargoDataAtualizacao']))->format('d/m/Y') : 'N/A';


// ----------------------------------------------------
// 9. GERAÇÃO DO HTML (Bufferizado)
// ----------------------------------------------------
$section_counter = 1;
ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório - Cargo: <?php echo htmlspecialchars($cargo['cargoNome']); ?></title>
    
    <style>
        <?php 
        $css_path = '../css/relatorio_style.css';
        if (file_exists($css_path)) {
            echo file_get_contents($css_path);
        } else {
            echo "/* ERRO: Não foi possível carregar o CSS */";
        }
        ?>
    </style>
    
    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $font_size = 7; // Menor que 8pt
            
            // Margem inferior (definida no @page) = 2cm. 
            // (2cm * 28.3465) = 56.693 pts. Posição Y será (altura - margem) + (margem/2)
            $y = $pdf->get_height() - 35; // Posição Y (Dentro da margem inferior de 2cm)

            // Texto da Esquerda (Data de Atualização)
            $text_left = "Última Atualização do Registo: <?php echo $data_atualizacao; ?>";
            $width_left = $fontMetrics->get_text_width($text_left, $font, $font_size);
            // Posição X (Margem esquerda = 3cm = 85.0395 pts)
            $x_left = 85.0395; 
            $pdf->text($x_left, $y, $text_left, $font, $font_size);

            // Texto do Meio (Data de Emissão)
            $text_center = "Emitido em: <?php echo $data_emissao; ?>";
            $width_center = $fontMetrics->get_text_width($text_center, $font, $font_size);
            $x_center = ($pdf->get_width() - $width_center) / 2; // Centralizado
            $pdf->text($x_center, $y, $text_center, $font, $font_size);
            
            // Texto da Direita (Paginação)
            $text_right = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $width_right = $fontMetrics->get_text_width($text_right, $font, $font_size);
            // Posição X (Largura - Margem Direita (56.693 pts) - Largura do texto)
            $x_right = $pdf->get_width() - 56.693 - $width_right; 
            $pdf->text($x_right, $y, $text_right, $font, $font_size);
        }
    </script>
</head>
<body class="pdf-render"> 
<div class="container">

    <?php
    // Define a variável de controlo e inclui o template
    $show_hierarquia = false; 
    include '../includes/templates/_template_cargo.php';
    ?>

</div> 
</body>
</html>
<?php
// Fim do buffer HTML
$html = ob_get_clean();

// ----------------------------------------------------
// 10. GERAÇÃO DO PDF
// ----------------------------------------------------

// LÓGICA DE NOME DO ARQUIVO
$cargo_name_cleaned = preg_replace('/[^A-Za-z0-9_]/', '_', strtoupper($cargo['cargoNome']));
$cargo_name_cleaned = substr($cargo_name_cleaned, 0, 30);
$cbo_code = $cargo['cboCod'] ?? '000000';
$timestamp = date('Ymd'); 
$filename_final = "{$cargo_name_cleaned}_{$cbo_code}_{timestamp}";

// Opções do Dompdf
$options = new \Dompdf\Options();
$options->set('isRemoteEnabled', true); 
$options->set('defaultFont', 'Helvetica');

try {
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($filename_final . ".pdf", ["Attachment" => true]); // Força download
    
} catch (Exception $e) {
    die("Erro ao gerar PDF: " . $e->getMessage());
}

exit;
?>