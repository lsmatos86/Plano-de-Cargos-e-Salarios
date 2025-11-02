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

// ----------------------------------------------------
// 9. GERAÇÃO DO HTML (Bufferizado)
// ----------------------------------------------------
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
            $size = 9;
            $y = $pdf->get_height() - 30; // Posição Y (inferior)
            $x = $pdf->get_width() - 100 - $pdf->get_margin_right(); // Posição X (canto direito)
            $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size);
        }
    </script>
</head>
<body class="pdf-render"> <div class="container">

    <?php
    // Define a variável de controlo e inclui o template
    $show_hierarquia = false; // <-- AQUI ESTÁ A MUDANÇA
    
    // ======================================================
    // ATUALIZAÇÃO DO CAMINHO DO INCLUDE
    // ======================================================
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