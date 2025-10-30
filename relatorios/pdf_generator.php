<?php
// Arquivo: relatorios/pdf_generator.php

// Incluir o autoloader gerado pelo Composer (IMPORTANTE: Subir duas pastas, pois estamos em relatorios/)
require_once '../vendor/autoload.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Gera um PDF a partir do conteúdo HTML usando Dompdf.
 */
function generatePdfFromHtml(string $htmlContent, string $fileName, bool $stream = true) {
    if (!class_exists(Dompdf::class)) {
        die("ERRO: Dompdf está instalado, mas a classe não foi encontrada. Verifique o path do autoload.");
    }

    $options = new Options();
    $options->set('defaultFont', 'Helvetica');
    $options->set('isRemoteEnabled', TRUE); // Permite carregar CSS/Imagens via CDN (Bootstrap)
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($htmlContent);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    if ($stream) {
        // Envia o PDF diretamente para o navegador
        $dompdf->stream("{$fileName}.pdf", ["Attachment" => true]);
    } else {
        // Retorna o conteúdo binário do PDF
        return $dompdf->output();
    }
}
?>