<?php
// Arquivo: relatorios/cargo_individual.php (Versão HTML)
// REFATORADO PARA USAR O REPOSITÓRIO

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // Para isUserLoggedIn e getRiscoIcon

// 2. Importa o Repositório
use App\Repository\CargoRepository;

// Segurança
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    die("Acesso Negado.");
}

$cargo_id = (int)($_GET['id'] ?? 0);
if ($cargo_id <= 0) {
    die("Erro: ID do cargo inválido.");
}

// ----------------------------------------------------
// 3. LÓGICA DE BUSCA DE DADOS (REFATORADO)
// ----------------------------------------------------
try {
    $repo = new CargoRepository();
    // Usamos o mesmo método que o PDF para garantir consistência
    $data = $repo->findReportData($cargo_id); 
} catch (Exception $e) {
    die("Erro ao carregar dados do cargo: " . $e->getMessage());
}

if (!$data || !isset($data['cargo'])) {
    die("Erro: Cargo ID {$cargo_id} não encontrado ou dados insuficientes.");
}

// 4. Prepara os dados para a view (mesma lógica de antes)
$cargo = $data['cargo'];
$soft_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Softskill');
$hard_skills = array_filter($data['habilidades'], fn($h) => $h['habilidadeTipo'] == 'Hardskill');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório: <?php echo htmlspecialchars($cargo['cargoNome']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f4f4f4; }
        .report-container {
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .report-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1.5rem 2rem;
            border-radius: 8px 8px 0 0;
        }
        .report-header .cargo-nome { color: #198754; } /* Cor do tema */
        .report-header .cbo-detail { font-size: 0.9rem; color: #6c757d; }
        
        .report-section {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #eee;
        }
        .report-section:last-child { border-bottom: none; }
        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #198754;
            padding-bottom: 8px;
        }
        .section-subtitle {
            font-weight: 600;
            color: #495057;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        .wysiwyg-content {
            padding: 1rem;
            background-color: #fdfdfd;
            border: 1px solid #eee;
            border-radius: 5px;
            min-height: 80px;
        }
        .habilidade-list { list-style: none; padding-left: 0.5rem; }
        .hardskill-item::before { content: "\\f0e7"; font-family: "Font Awesome 6 Free"; font-weight: 900; color: #0d6efd; margin-right: 10px; }
        .softskill-item::before { content: "\\f007"; font-family: "Font Awesome 6 Free"; font-weight: 900; color: #198754; margin-right: 10px; }
        .habilidade-descricao {
            font-size: 0.85rem;
            color: #6c757d;
            padding-left: 1.7rem;
            margin-top: -5px;
        }
        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        @media print {
            body { background-color: #fff; }
            .report-container {
                box-shadow: none;
                border: none;
                margin: 0;
                max-width: 100%;
            }
            .print-button { display: none; }
        }
    </style>
</head>
<body>

    <div class="print-button d-print-none">
        <a href="cargo_individual.php?id=<?php echo $cargo_id; ?>&format=pdf" class="btn btn-danger shadow-sm me-2" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> Gerar PDF
        </a>
        <button class="btn btn-primary shadow-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimir
        </button>
    </div>

    <div class="report-container" id="report">
        
        <div class="report-header text-center">
            <h1 class="cargo-nome"><?php echo htmlspecialchars($cargo['cargoNome']); ?></h1>
            <p class="cbo-detail mb-0">
                <strong>CBO:</strong> <?php echo htmlspecialchars($cargo['cboCod'] ?? 'N/A'); ?> - 
                <?php echo htmlspecialchars($cargo['cboTituloOficial'] ?? 'Título Oficial Não Disponível'); ?>
            </p>
        </div>

        <div class="report-section">
            <h2 class="section-title"><i class="fas fa-id-card me-2 text-secondary"></i> 1. Informações Essenciais</h2>
            
            <h5 class="section-subtitle">Descrição Sumária</h5>
            <div class="wysiwyg-content mb-3">
                <?php echo $cargo['cargoResumo'] ?? '<em class="text-muted">N/A</em>'; ?> 
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h5 class="section-subtitle">Escolaridade</h5>
                    <p><?php echo htmlspecialchars($cargo['escolaridadeTitulo'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-md-6">
                    <h5 class="section-subtitle">Sinônimos</h5>
                    <p><?php echo empty($data['sinonimos']) ? '<em class="text-muted">Nenhum</em>' : implode(', ', $data['sinonimos']); ?></p>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h2 class="section-title"><i class="fas fa-cogs me-2 text-secondary"></i> 2. Habilidades e Competências</h2>
            <div class="row">
                <div class="col-md-6">
                    <h5 class="section-subtitle">Habilidades Técnicas (HARD SKILLS)</h5>
                    <ul class="habilidade-list">
                        <?php if (empty($hard_skills)): ?>
                            <li><em class="text-muted">Nenhuma Hard Skill associada.</em></li>
                        <?php endif; ?>
                        <?php foreach ($hard_skills as $h): ?>
                            <li class="hardskill-item mb-2">
                                <strong><?php echo htmlspecialchars($h['habilidadeNome']); ?></strong>
                                <?php if (!empty($h['habilidadeDescricao'])): ?>
                                    <p class="habilidade-descricao"><?php echo htmlspecialchars($h['habilidadeDescricao']); ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="section-subtitle">Competências Comportamentais (SOFT SKILLS)</h5>
                    <ul class="habilidade-list">
                        <?php if (empty($soft_skills)): ?>
                            <li><em class="text-muted">Nenhuma Soft Skill associada.</em></li>
                        <?php endif; ?>
                        <?php foreach ($soft_skills as $h): ?>
                            <li class="softskill-item mb-2">
                                <strong><?php echo htmlspecialchars($h['habilidadeNome']); ?></strong>
                                <?php if (!empty($h['habilidadeDescricao'])): ?>
                                    <p class="habilidade-descricao"><?php echo htmlspecialchars($h['habilidadeDescricao']); ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h2 class="section-title"><i class="fas fa-handshake me-2 text-secondary"></i> 3. Qualificação e Caráter</h2>
            <div class="row">
                <div class="col-md-6">
                    <h5 class="section-subtitle">Cursos e Treinamentos</h5>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($data['cursos'])): ?>
                            <li class="list-group-item"><em class="text-muted">Nenhum curso associado.</em></li>
                        <?php endif; ?>
                        <?php foreach ($data['cursos'] as $cur): ?>
                            <li class="list-group-item">
                                <?php echo htmlspecialchars($cur['cursoNome']); ?>
                                <span class="badge <?php echo $cur['cursoCargoObrigatorio'] ? 'bg-danger' : 'bg-success'; ?> float-end">
                                    <?php echo $cur['cursoCargoObrigatorio'] ? 'OBRIGATÓRIO' : 'Recomendado'; ?>
                                </span>
                                <?php if (!empty($cur['cursoCargoObs'])): ?>
                                    <small class="d-block text-muted mt-1">Obs: <?php echo htmlspecialchars($cur['cursoCargoObs']); ?></small>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="section-subtitle">Características Pessoais Desejáveis</h5>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($data['caracteristicas'])): ?>
                             <li class="list-group-item"><em class="text-muted">Nenhuma característica associada.</em></li>
                        <?php endif; ?>
                        <?php foreach ($data['caracteristicas'] as $c): ?>
                            <li class="list-group-item"><?php echo htmlspecialchars($c['caracteristicaNome']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="report-section">
            <h2 class="section-title"><i class="fas fa-toolbox me-2 text-secondary"></i> 4. Recursos e Riscos de Exposição</h2>
            <div class="row">
                <div class="col-md-6">
                    <h5 class="section-subtitle">Grupos de Recursos Utilizados</h5>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($data['recursos_grupos'])): ?>
                             <li class="list-group-item"><em class="text-muted">Nenhum grupo de recurso associado.</em></li>
                        <?php endif; ?>
                        <?php foreach ($data['recursos_grupos'] as $rg): ?>
                            <li class="list-group-item"><?php echo htmlspecialchars($rg); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="section-subtitle">Riscos de Exposição</h5>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Detalhe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['riscos'])): ?>
                                <tr><td colspan="2"><em class="text-muted">Nenhum risco de exposição registrado.</em></td></tr>
                            <?php endif; ?>
                            <?php foreach ($data['riscos'] as $r): ?>
                                <tr>
                                    <td class="text-nowrap"><?php echo getRiscoIcon($r['riscoNome']); ?> <?php echo htmlspecialchars($r['riscoNome']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($r['riscoDescricao'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h2 class="section-title"><i class="fas fa-book-open me-2 text-secondary"></i> 5. Responsabilidades e Complexidade</h2>
            
            <h5 class="section-subtitle">Responsabilidades Detalhadas</h5>
            <div class="wysiwyg-content mb-3">
                <?php echo $cargo['cargoResponsabilidades'] ?? '<em class="text-muted">N/A</em>'; ?>
            </div>
            
            <h5 class="section-subtitle">Condições Gerais</h5>
            <div class="wysiwyg-content mb-3">
                <?php echo $cargo['cargoCondicoes'] ?? '<em class="text-muted">N/A</em>'; ?>
            </div>

            <h5 class="section-subtitle">Complexidade do Cargo</h5>
            <div class="wysiwyg-content mb-3">
                <?php echo $cargo['cargoComplexidade'] ?? '<em class="text-muted">N/A</em>'; ?>
            </div>
        </div>

    </div>
</body>
</html>