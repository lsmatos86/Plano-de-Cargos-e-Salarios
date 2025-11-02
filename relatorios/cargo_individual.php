<?php
// Arquivo: relatorios/cargo_individual.php (Ajustes de Ícones e Negrito)

// 1. Inclua o autoload do Composer
require_once '../vendor/autoload.php';
require_once '../config.php'; 

// 2. Importe a classe
use App\Repository\CargoRepository;

// 3. Inclua functions.php (para login e helpers de ícones)
require_once '../includes/functions.php'; 

// 4. Inclua o gerador de PDF
require_once 'pdf_generator.php';

// 5. Segurança
if (!isUserLoggedIn()) {
    die("Acesso Negado.");
}

// 6. Obter Parâmetros
$cargo_id = (int)($_GET['id'] ?? 0);
$format = strtolower($_GET['format'] ?? 'html');

// Parâmetros de navegação
$sort_col = $_GET['sort_col'] ?? 'c.cargoId';
$sort_dir = $_GET['sort_dir'] ?? 'ASC';
$term = $_GET['term'] ?? '';

if ($cargo_id <= 0) {
    die("Erro: ID de cargo inválido.");
}

// 7. Buscar os dados
try {
    $cargoRepository = new CargoRepository(); 
    $data = $cargoRepository->findReportData($cargo_id); 
    
    $adjacentIds = ['prev_id' => null, 'next_id' => null];
    $extremityIds = ['first_id' => null, 'last_id' => null];

    // Só buscamos navegação se for HTML
    if ($format === 'html') {
        //
        $adjacentIds = $cargoRepository->findAdjacentCargoIds($cargo_id, $sort_col, $sort_dir, $term);
        //
        $extremityIds = $cargoRepository->findFirstAndLastCargoIds($sort_col, $sort_dir, $term);
    }

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

// IDs de Navegação
$prev_id = $adjacentIds['prev_id'];
$next_id = $adjacentIds['next_id'];
$first_id = $extremityIds['first_id'];
$last_id = $extremityIds['last_id'];

// Monta os parâmetros de navegação para os links
$nav_params_base = http_build_query([
    'format' => 'html',
    'sort_col' => $sort_col,
    'sort_dir' => $sort_dir,
    'term' => $term
]);

// Links de navegação
$first_link = $first_id ? "cargo_individual.php?id={$first_id}&{$nav_params_base}" : null;
$prev_link  = $prev_id  ? "cargo_individual.php?id={$prev_id}&{$nav_params_base}" : null;
$next_link  = $next_id  ? "cargo_individual.php?id={$next_id}&{$nav_params_base}" : null;
$last_link  = $last_id  ? "cargo_individual.php?id={$last_id}&{$nav_params_base}" : null;


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
    
    <link rel="stylesheet" href="../css/relatorio_style.css">

    <?php if ($format === 'html'): ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        
        <style>
            .btn-nav-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding: 15px;
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                flex-wrap: wrap; /* Permite quebrar linha em telas menores */
            }
            .nav-group {
                margin: 5px;
            }
            .nav-go-form {
                display: flex;
            }
            .nav-go-form input[type="number"] {
                width: 90px;
                margin-right: 5px;
                text-align: center;
            }
        </style>
    <?php endif; ?>
    
    <?php if ($format === 'pdf'): ?>
    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $size = 9;
            $y = $pdf->get_height() - 30; // Posição Y (inferior)
            $x = $pdf->get_width() - 100 - $pdf->get_margin_right(); // Posição X (canto direito)
            $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size);
        }
    </script>
    <?php endif; ?>
</head>
<body>
<div class="container">

    <?php if ($format === 'html'): ?>
    <div class="btn-nav-container no-print">
        
        <div class="btn-group nav-group" role="group">
            <a href="../views/cargos.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <div class="btn-group nav-group" role="group">
            <a href="<?php echo $first_link; ?>" class="btn btn-outline-primary btn-sm <?php echo $first_id && $first_id != $cargo_id ? '' : 'disabled'; ?>" title="Primeiro Registro">
                <i class="fas fa-angle-double-left"></i>
            </a>
            <a href="<?php echo $prev_link; ?>" class="btn btn-outline-primary btn-sm <?php echo $prev_id ? '' : 'disabled'; ?>" title="Anterior">
                <i class="fas fa-chevron-left"></i>
            </a>
            <a href="<?php echo $next_link; ?>" class="btn btn-outline-primary btn-sm <?php echo $next_id ? '' : 'disabled'; ?>" title="Próximo">
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="<?php echo $last_link; ?>" class="btn btn-outline-primary btn-sm <?php echo $last_id && $last_id != $cargo_id ? '' : 'disabled'; ?>" title="Último Registro">
                <i class="fas fa-angle-double-right"></i>
            </a>
        </div>
        
        <form class="nav-go-form nav-group" method="GET" action="cargo_individual.php" id="navGoForm">
            <input type="hidden" name="format" value="html">
            <input type="hidden" name="sort_col" value="<?php echo htmlspecialchars($sort_col); ?>">
            <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($sort_dir); ?>">
            <input type="hidden" name="term" value="<?php echo htmlspecialchars($term); ?>">
            
            <input type="number" class="form-control form-control-sm" name="id" placeholder="Ir para ID..." value="<?php echo $cargo_id; ?>" required>
            <button type="submit" class="btn btn-outline-primary btn-sm">Ir</button>
        </form>
        
        <div class="btn-group nav-group" role="group">
            <a href="cargo_individual.php?id=<?php echo $cargo_id; ?>&<?php echo $nav_params_base; ?>&format=pdf" class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <button onclick="window.print()" class="btn btn-success btn-sm">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>
    <?php endif; ?>

    <div class="report-header-final">
        <span class="cargo-nome-principal"><?php echo htmlspecialchars($cargo['cargoNome']); ?></span>
        <p class="cbo-detail">
            <strong>CBO:</strong> <?php echo htmlspecialchars($cargo['cboCod'] ?? 'N/A'); ?> - 
            <?php echo htmlspecialchars($cargo['cboTituloOficial'] ?? 'Título Oficial Não Disponível'); ?>
        </p>
    </div>

    <h2 class="h2-custom"><i class="fas fa-id-card"></i> 1. Informações Essenciais</h2>
    <table class="data-list">
        <tr>
            <th><i class="fas fa-file-alt"></i> Descrição Sumária</th>
            <td><?php echo nl2br(htmlspecialchars($cargo['cargoResumo'] ?? 'N/A')); ?></td>
        </tr>
        <tr>
            <th><i class="fas fa-graduation-cap"></i> Escolaridade</th>
            <td><?php echo htmlspecialchars($cargo['escolaridadeTitulo'] ?? 'N/A'); ?></td>
        </tr>
        <tr>
            <th><i class="fas fa-clock"></i> Experiência</th>
            <td><?php echo htmlspecialchars($cargo['cargoExperiencia'] ?? 'N/A'); ?></td>
        </tr>
         <tr>
            <th><i class="fas fa-tags"></i> Sinônimos</th>
            <td><?php echo empty($data['sinonimos']) ? 'Nenhum' : implode(', ', array_map('htmlspecialchars', $data['sinonimos'])); ?></td>
        </tr>
    </table>

    <h2 class="h2-custom"><i class="fas fa-sitemap"></i> 2. Hierarquia e Estrutura</h2>
     <table class="data-list">
        <tr>
            <th><i class="fas fa-level-up-alt"></i> Nível Hierárquico</th>
            <td>
                <?php 
                echo htmlspecialchars($cargo['tipoHierarquiaNome'] ?? 'N/A'); 
                if (!empty($cargo['nivelOrdem'])) {
                    echo ' (Ordem: ' . htmlspecialchars($cargo['nivelOrdem']) . ')';
                }
                ?>
            </td>
        </tr>
         <tr>
            <th><i class="fas fa-user-tie"></i> Reporta-se a</th>
            <td><?php echo htmlspecialchars($cargo['cargoSupervisorNome'] ?? 'N/A'); ?></td>
        </tr>
         <tr>
            <th><i class="fas fa-building"></i> Áreas de Atuação</th>
            <td><?php echo empty($data['areas_atuacao']) ? 'Nenhuma' : implode(', ', array_map('htmlspecialchars', $data['areas_atuacao'])); ?></td>
        </tr>
         <tr>
            <th><i class="fas fa-wallet"></i> Faixa Salarial</th>
            <td>
                <?php 
                echo htmlspecialchars($cargo['faixaNivel'] ?? 'Não definida'); 
                if (!empty($cargo['faixaSalarioMinimo'])) {
                    echo ' (R$ ' . htmlspecialchars(number_format($cargo['faixaSalarioMinimo'], 2, ',', '.')) . ' - R$ ' . htmlspecialchars(number_format($cargo['faixaSalarioMaximo'], 2, ',', '.')) . ')';
                }
                ?>
            </td>
        </tr>
    </table>

    <h2 class="h2-custom"><i class="fas fa-cogs"></i> 3. Habilidades e Competências</h2>
    
    <div class="skill-container">
        
        <div class="skill-column">
            <h5 class="h5-custom skill-type-header"><i class="fas fa-toolbox"></i> Habilidades Técnicas (HARD SKILLS)</h5>
            <ul class="habilidade-list">
            <?php if (empty($hard_skills)): ?>
                <li>Nenhuma Hard Skill associada.</li>
            <?php else: ?>
                <?php foreach ($hard_skills as $h): ?>
                    <li>
                        <i class="fas fa-chevron-right"></i> <div class="item-content"> <span class="habilidade-nome"><?php echo htmlspecialchars($h['habilidadeNome']); ?></span>
                            <?php if (!empty($h['habilidadeDescricao'])): ?>
                                <div class="habilidade-descricao"> - <?php echo htmlspecialchars($h['habilidadeDescricao']); ?></div>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
            </ul>
        </div>
        
        <div class="skill-column">
            <h5 class="h5-custom skill-type-header"><i class="fas fa-users"></i> Competências Comportamentais (SOFT SKILLS)</h5>
            <ul class="habilidade-list">
            <?php if (empty($soft_skills)): ?>
                <li>Nenhuma Soft Skill associada.</li>
            <?php else: ?>
                <?php foreach ($soft_skills as $h): ?>
                     <li>
                        <i class="fas fa-chevron-right"></i> <div class="item-content"> <span class="habilidade-nome"><?php echo htmlspecialchars($h['habilidadeNome']); ?></span>
                            <?php if (!empty($h['habilidadeDescricao'])): ?>
                                <div class="habilidade-descricao"> - <?php echo htmlspecialchars($h['habilidadeDescricao']); ?></div>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
            </ul>
        </div>
        
    </div>
    <h2 class="h2-custom"><i class="fas fa-handshake"></i> 4. Qualificação e Recursos</h2>
    <h5 class="h5-custom"><i class="fas fa-certificate"></i> Cursos e Treinamentos</h5>
    <ul class="curso-list">
        <?php if (empty($data['cursos'])): ?>
            <li>Nenhum curso associado.</li>
        <?php else: ?>
            <?php foreach ($data['cursos'] as $cur): ?>
                <li>
                    <i class="fas fa-check-circle" style="color: <?php echo $cur['cursoCargoObrigatorio'] ? '#dc3545' : '#198754'; ?>;"></i>
                    <div class="item-content">
                        <?php echo htmlspecialchars($cur['cursoNome']); // NEGRITO REMOVIDO ?>
                        <span style="color: <?php echo $cur['cursoCargoObrigatorio'] ? '#dc3545' : '#555'; ?>; font-size: 0.9em;">
                            (<?php echo $cur['cursoCargoObrigatorio'] ? 'OBRIGATÓRIO' : 'Recomendado'; ?>)
                        </span>
                        <?php echo !empty($cur['cursoCargoObs']) ? '<br><small style="color: #555;">Observação: '. htmlspecialchars($cur['cursoCargoObs']) . '</small>' : '' ?>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <h5 class="h5-custom"><i class="fas fa-user-tag"></i> Características Pessoais Desejáveis</h5>
    <ul class="caracteristica-list">
    <?php if (empty($data['caracteristicas'])): ?>
        <li>Nenhuma característica associada.</li>
    <?php else: ?>
        <?php foreach ($data['caracteristicas'] as $c): ?>
            <li>
                <i class="fas fa-chevron-right"></i>
                <div class="item-content">
                    <?php echo htmlspecialchars($c['caracteristicaNome']); // Negrito já havia sido removido ?>
                </div>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
    </ul>
    
    <h5 class="h5-custom"><i class="fas fa-wrench"></i> Grupos de Recursos Utilizados</h5>
     <ul class="caracteristica-list">
        <?php if (empty($data['recursos_grupos'])): ?>
            <li>Nenhum grupo de recurso associado.</li>
        <?php else: ?>
            <?php foreach ($data['recursos_grupos'] as $rg): ?>
                <li>
                    <i class="fas fa-chevron-right"></i>
                    <div class="item-content">
                        <?php echo htmlspecialchars($rg); ?>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>


    <h2 class="h2-custom"><i class="fas fa-radiation-alt"></i> 5. Riscos de Exposição</h2>
    <table class="riscos-table">
        <thead class="bg-light">
            <tr>
                <th style="width: 30%;">Tipo de Risco</th>
                <th>Detalhe Específico da Exposição</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['riscos'])): ?>
                <tr><td colspan="2" style="text-align: center;">Nenhum risco de exposição registrado.</td></tr>
            <?php else: ?>
                <?php foreach ($data['riscos'] as $r): ?>
                    <tr>
                        <td>
                            <i class="<?php echo getRiscoIcon($r['riscoNome']); // ?>"></i> 
                            <?php echo htmlspecialchars($r['riscoNome']); ?>
                        </td>
                        <td><?php echo nl2br(htmlspecialchars($r['riscoDescricao'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <h2 class="h2-custom"><i class="fas fa-book-open"></i> 6. Descrições Detalhadas</h2>
    
    <h5 class="h5-custom"><i class="fas fa-clipboard-list"></i> Responsabilidades Detalhadas:</h5>
    <div class="wysiwyg-content"><?php echo nl2br(htmlspecialchars($cargo['cargoResponsabilidades'] ?? 'N/A')); ?></div>
    
    <h5 class="h5-custom"><i class="fas fa-cloud-sun"></i> Condições Gerais de Trabalho:</h5>
    <div class="wysiwyg-content"><?php echo nl2br(htmlspecialchars($cargo['cargoCondicoes'] ?? 'N/A')); ?></div>
    
    <h5 class="h5-custom"><i class="fas fa-layer-group"></i> Complexidade do Cargo:</h5>
    <div class="wysiwyg-content"><?php echo nl2br(htmlspecialchars($cargo['cargoComplexidade'] ?? 'N/A')); ?></div>

</div> 
</body>
</html>
<?php
// Fim do buffer HTML
$html = ob_get_clean();

// ----------------------------------------------------
// 10. ROTEAMENTO DE SAÍDA (HTML ou PDF)
// ----------------------------------------------------

if ($format === 'pdf') {
    // LÓGICA DE NOME DO ARQUIVO
    $cargo_name_cleaned = preg_replace('/[^A-Za-z0-9_]/', '_', strtoupper($cargo['cargoNome']));
    $cargo_name_cleaned = substr($cargo_name_cleaned, 0, 30);
    $cbo_code = $cargo['cboCod'] ?? '000000';
    $timestamp = date('Ymd'); 
    $filename_final = "{$cargo_name_cleaned}_{$cbo_code}_{$timestamp}";

    // Opções do Dompdf
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true); // Necessário para carregar o CSS
    $options->set('defaultFont', 'Helvetica');
    
    // Define o chroot para a raiz do projeto (um nível acima de /relatorios)
    $options->set('chroot', realpath(__DIR__ . '/..')); 

    try {
        $dompdf = new \Dompdf\Dompdf($options);
        
        // Carrega o HTML. Dompdf usará o chroot para encontrar ../css/relatorio_style.css
        $dompdf->loadHtml($html);
        
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream($filename_final . ".pdf", ["Attachment" => true]); // Força download
        
    } catch (Exception $e) {
        die("Erro ao gerar PDF: " . $e->getMessage());
    }
    
    exit;
    
} else {
    // Se for 'html', apenas exibe o HTML
    echo $html;
    exit;
}
?>