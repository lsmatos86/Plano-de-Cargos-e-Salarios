<?php
// Arquivo: relatorios/cargo_individual.php (Visualização HTML com Barra de Navegação e Cards)

require_once '../config.php';
require_once '../includes/functions.php';
require_once 'pdf_generator.php'; 

if (!isUserLoggedIn()) {
    die("Acesso Negado.");
}

$pdo = getDbConnection();
$cargo_id = (int)($_GET['id'] ?? 0);
$output_format = $_GET['format'] ?? 'html'; // Modo padrão é HTML

$data = getCargoReportData($pdo, $cargo_id);

if (!$data) {
    die("Erro: Cargo ID {$cargo_id} não encontrado ou dados insuficientes. Verifique a integridade das FKs (CBO/Escolaridade).");
}

$cargo = $data['cargo'];

// Preparação dos dados para as colunas
$soft_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Softskill');
$hard_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Hardskill');

// ----------------------------------------------------
// Lógica de Paginação (Simulação para a Barra de Navegação)
// ----------------------------------------------------
try {
    $stmt_ids = $pdo->query("SELECT cargoId FROM cargos ORDER BY cargoId ASC");
    $all_cargo_ids = $stmt_ids->fetchAll(PDO::FETCH_COLUMN);
    $current_index = array_search($cargo_id, $all_cargo_ids);
    $prev_id = ($current_index !== false && $current_index > 0) ? $all_cargo_ids[$current_index - 1] : null;
    $next_id = ($current_index !== false && $current_index < count($all_cargo_ids) - 1) ? $all_cargo_ids[$current_index + 1] : null;
    $total_cargos = count($all_cargo_ids);

} catch (Exception $e) {
    $prev_id = null;
    $next_id = null;
    $total_cargos = 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório - Cargo: <?php echo htmlspecialchars($cargo['cargoNome']); ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .cargo-nome-principal { font-size: 1.75rem; color: #007bff; font-weight: bold; margin-bottom: 0.5rem; }
        .h2-custom { font-size: 1.3rem; color: #198754; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 1.5rem; margin-bottom: 1rem; font-weight: bold; }
        .h2-custom::before { content: ""; display: block; width: 100%; height: 1px; background: #e9ecef; margin-bottom: 15px; }
        .h5-custom { font-size: 1.1rem; font-weight: bold; color: #333; margin-bottom: 0.5rem; }
        .data-label { font-weight: bold; width: 120px; color: #444; }
        .wysiwyg-content { padding: 10px; background-color: #f8f9fa; border-radius: 5px; margin-top: 5px; }
        .list-header { font-weight: bold; margin-bottom: 5px; margin-top: 10px; color: #333; }
        
        /* Estilos de Lista */
        .habilidade-list { list-style: none; padding-left: 0; }
        .hardskill-item::before { content: "■"; color: #333; font-size: 0.8em; display: inline-block; width: 1em; margin-left: -1em; }
        .softskill-item::before { content: "•"; color: #888; font-size: 1em; display: inline-block; width: 1em; margin-left: -1em; }
        .simple-list { list-style: disc; padding-left: 20px; margin-top: 0; }
        .habilidade-nome { font-weight: bold; font-size: 10pt; } 
        .habilidade-descricao { font-size: 8.5pt; color: #555; padding-top: 0; padding-bottom: 8px; line-height: 1.3; }
    </style>
</head>
<body>

    <div class="container mt-3 mb-4">
        <div class="d-flex justify-content-center align-items-center bg-light p-2 border rounded">
            
            <a href="?id=<?php echo $all_cargo_ids[0]; ?>&format=html" class="btn btn-sm btn-outline-secondary <?php echo ($cargo_id == $all_cargo_ids[0]) ? 'disabled' : ''; ?> me-1" title="Primeiro Cargo">
                <i class="fas fa-angle-double-left"></i>
            </a>
            <a href="?id=<?php echo $prev_id; ?>&format=html" class="btn btn-sm btn-outline-secondary <?php echo empty($prev_id) ? 'disabled' : ''; ?> me-3" title="Anterior">
                <i class="fas fa-angle-left"></i>
            </a>
            
            <form method="GET" class="d-flex align-items-center" onsubmit="window.location.href = 'cargo_individual.php?id=' + document.getElementById('goto_id').value + '&format=html'; return false;">
                <span class="text-muted small me-2">Cargo Atual: </span>
                <input type="number" id="goto_id" name="id" class="form-control form-control-sm text-center" style="width: 70px;" value="<?php echo $cargo_id; ?>" min="<?php echo $all_cargo_ids[0]; ?>" max="<?php echo end($all_cargo_ids); ?>">
                <input type="hidden" name="format" value="html">
                <button type="submit" class="btn btn-sm btn-success ms-2 me-3" title="Ir para o ID digitado">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <a href="?id=<?php echo $next_id; ?>&format=html" class="btn btn-sm btn-outline-secondary <?php echo empty($next_id) ? 'disabled' : ''; ?> me-1" title="Próximo">
                <i class="fas fa-angle-right"></i>
            </a>
            <a href="?id=<?php echo end($all_cargo_ids); ?>&format=html" class="btn btn-sm btn-outline-secondary <?php echo ($cargo_id == end($all_cargo_ids)) ? 'disabled' : ''; ?>" title="Último Cargo">
                <i class="fas fa-angle-double-right"></i>
            </a>
        </div>
        
        <div class="mt-3 text-center">
             <a href="../views/cargos.php" class="btn btn-sm btn-secondary">
                <i class="fas fa-th-list"></i> Voltar para Lista de Cargos
            </a>
            <a href="gerador_pdf.php?id=<?php echo $cargo_id; ?>" class="btn btn-sm btn-danger ms-2" target="_blank">
                <i class="fas fa-file-pdf"></i> Gerar PDF
            </a>
        </div>
    </div>


    <div class="container my-5"> <div class="report-header-final text-center">
            <span class="cargo-nome-principal"><?php echo htmlspecialchars($cargo['cargoNome']); ?></span>
            <p class="cbo-detail">
                <strong style="color: #444;">CBO:</strong> <?php echo htmlspecialchars($cargo['cboCod'] ?? 'N/A'); ?> - 
                <?php echo htmlspecialchars($cargo['cboTituloOficial'] ?? 'Título Oficial Não Disponível'); ?>
            </p>
        </div>
        
        <div class="generated-date text-end">
            Emitido em: <?php echo date('d/m/Y H:i:s'); ?>
        </div>


        <h2 class="h2-custom"><i class="fas fa-id-card"></i> 1. Informações Essenciais</h2>
        
        <div class="card shadow-sm mb-3">
            <div class="card-body p-3">
                <h5 class="h5-custom"><i class="fas fa-file-alt"></i> Descrição Sumária</h5>
                <div class="wysiwyg-content border">
                    <?php echo $cargo['cargoResumo'] ?? 'N/A'; ?> 
                </div>
                
                <table class="table table-sm data-list mt-3">
                    <tr><td class="data-label"><strong>Escolaridade</strong>:</td><td><?php echo htmlspecialchars($cargo['escolaridadeTitulo'] ?? 'N/A'); ?></td></tr>
                    <tr><td class="data-label"><strong>Sinônimos</strong>:</td><td><?php echo empty($data['sinonimos']) ? 'Nenhum' : implode(', ', $data['sinonimos']); ?></td></tr>
                </table>
            </div>
        </div>


        <h2 class="h2-custom"><i class="fas fa-cogs"></i> 2. Habilidades e Competências</h2>
        
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-3">
                        <h5 class="h5-custom"><i class="fas fa-toolbox"></i> Habilidades Técnicas (HARD SKILLS)</h5>
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
            
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-3">
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
            </div>
        </div>


        <h2 class="h2-custom"><i class="fas fa-handshake"></i> 3. Qualificação e Caráter</h2>
        
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-3">
                        <h5 class="h5-custom"><i class="fas fa-certificate"></i> Cursos e Treinamentos</h5>
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
            
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-3">
                        <h5 class="h5-custom"><i class="fas fa-user-tag"></i> Características Pessoais Desejáveis</h5>
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
        </div>


        <h2 class="h2-custom"><i class="fas fa-toolbox"></i> 4. Recursos e Riscos de Exposição</h2>
        
        <div class="row g-3">
            
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-3">
                        <h5 class="h5-custom"><i class="fas fa-wrench"></i> Grupos de Recursos Utilizados</h5>
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
            
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-3">
                        <h5 class="h5-custom"><i class="fas fa-radiation-alt"></i> Riscos de Exposição</h5>
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
        </div> <h2 class="h2-custom"><i class="fas fa-book-open"></i> 5. Responsabilidades e Complexidade</h2>
        
        <div class="card shadow-sm mb-3">
            <div class="card-body p-3">
                <p class="list-header"><i class="fas fa-clipboard-list"></i> Responsabilidades Detalhadas:</p>
                <div class="wysiwyg-content border">
                    <?php echo $cargo['cargoResponsabilidades'] ?? 'N/A'; ?>
                </div>
                
                <p class="list-header"><i class="fas fa-cloud-sun"></i> Condições Gerais:</p>
                <div class="wysiwyg-content border">
                    <?php echo $cargo['cargoCondicoes'] ?? 'N/A'; ?>
                </div>
                
                <p class="list-header"><i class="fas fa-layer-group"></i> Complexidade do Cargo:</p>
                <div class="wysiwyg-content border">
                    <?php echo $cargo['cargoComplexidade'] ?? 'N/A'; ?>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Fim do buffer HTML
$html = ob_get_clean();

// ----------------------------------------------------
// 2. DECISÃO DE SAÍDA (HTML ou PDF)
// ----------------------------------------------------

if ($output_format === 'pdf') {
    // LÓGICA DE NOME DO ARQUIVO PERSONALIZADO
    $cargo_name_cleaned = preg_replace('/[^A-Za-z0-9_]/', '_', strtoupper($cargo['cargoNome']));
    $cargo_name_cleaned = substr($cargo_name_cleaned, 0, 30);
    $cbo_code = $cargo['cboId'] ?? '000000'; 
    $timestamp = date('YmdHi'); 
    $filename_final = "{$cargo_name_cleaned}_{$cbo_code}_{$timestamp}";

    // Chamada à função generatePdfFromHtml
    generatePdfFromHtml($html, $filename_final, true);
    exit;

} else {
    // Exibe o HTML diretamente no navegador (para visualização e debug)
    echo $html;
}