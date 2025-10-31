<?php
// Arquivo: relatorios/gerador_pdf.php (Refatorado)

// 1. Inclua o autoload do Composer
require_once '../vendor/autoload.php';
require_once '../config.php'; // (Ainda precisamos das constantes de config)

// 2. Importe a classe que você quer usar
use App\Repository\CargoRepository;

// 3. Inclua functions.php APENAS para a autenticação (por enquanto)
require_once '../includes/functions.php'; 
require_once 'pdf_generator.php';

if (!isUserLoggedIn()) { // <-- Esta função ainda está em functions.php
    die("Acesso Negado.");
}

$cargo_id = (int)($_GET['id'] ?? 0);

// 4. Use a nova classe!
try {
    $cargoRepository = new CargoRepository(); // <-- OOP
    $data = $cargoRepository->findReportData($cargo_id); // <-- OOP

} catch (\Exception $e) {
    die("Erro ao gerar relatório: " . $e->getMessage());
}

if (!$data) {
    die("Erro: Cargo ID {$cargo_id} não encontrado ou dados insuficientes.");
}

$cargo = $data['cargo'];
$soft_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Softskill');
$hard_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Hardskill');

// ----------------------------------------------------
// 1. GERAÇÃO DO HTML RÍGIDO (BUFFERED)
// ----------------------------------------------------

ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório - Cargo: <?php echo htmlspecialchars($cargo['cargoNome']); ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* BASE GERAL */
        body { font-family: 'Helvetica', sans-serif; font-size: 10pt; padding: 0; margin: 0; line-height: 1.4; }
        .container { width: 90%; margin: 0 auto; }
        
        /* CABEÇALHO E RODAPÉ */
        .report-header-final { text-align: center; margin-bottom: 25px; }
        .cargo-nome-principal { font-size: 16pt; color: #000; font-weight: bold; display: block; margin-bottom: 5px; }
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
        .block-wrapper { width: 100%; margin-bottom: 5px; display: block; } /* Garante quebras de bloco */
        
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
        .list-header { font-weight: bold; margin-bottom: 5px; margin-top: 10px; color: #333; }
        .habilidade-nome { font-weight: bold; font-size: 10pt; } 
        .habilidade-descricao { font-size: 8.5pt; color: #555; padding-top: 0; padding-bottom: 8px; line-height: 1.3; }

    </style>
</head>
<body>
<div class="container">

    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $size = 9;
            $y = 35; // Posição vertical no topo
            $x = 510; // Posição horizontal (canto superior direito da página)
            
            // Renderiza o texto no topo de cada página
            $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size);
        }
    </script>


    <div class="report-header-final">
        <span class="cargo-nome-principal"><?php echo htmlspecialchars($cargo['cargoNome']); ?></span>
        <p class="cbo-detail">
            <strong style="color: #444;">CBO:</strong> <?php echo htmlspecialchars($cargo['cboCod'] ?? 'N/A'); ?> - 
            <?php echo htmlspecialchars($cargo['cboTituloOficial'] ?? 'Título Oficial Não Disponível'); ?>
        </p>
    </div>

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

<div class="report-footer">
    Emitido em: <?php echo date('d/m/Y H:i:s'); ?>
</div>

</body>
</html>
<?php
// Fim do buffer HTML
$html = ob_get_clean();

// ----------------------------------------------------
// 2. GERAÇÃO DO ARQUIVO PDF E DOWNLOAD
// ----------------------------------------------------

// LÓGICA DE NOME DO ARQUIVO PERSONALIZADO
$cargo_name_cleaned = preg_replace('/[^A-Za-z0-9_]/', '_', strtoupper($cargo['cargoNome']));
$cargo_name_cleaned = substr($cargo_name_cleaned, 0, 30);
$cbo_code = $cargo['cboId'] ?? '000000'; 
$timestamp = date('YmdHi'); 
$filename_final = "{$cargo_name_cleaned}_{$cbo_code}_{$timestamp}";

// CHAMA A FUNÇÃO DE GERAÇÃO E FORÇA O DOWNLOAD
generatePdfFromHtml($html, $filename_final, true);
exit;