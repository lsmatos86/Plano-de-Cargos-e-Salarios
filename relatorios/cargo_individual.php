<?php
// Arquivo: relatorios/cargo_individual.php (REFATORADO E CORRIGIDO)

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // Para login e helpers (incluindo getRiscoIcon)

// 2. Importa os Repositórios
use App\Repository\CargoRepository;
use App\Core\Database; // Para a navegação Próximo/Anterior

// 3. Segurança
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
// (OPCIONAL - Verificação de permissão)
// $authService->checkAndFail('cargos:view', '../index.php?error=Acesso+negado');


// 4. Obter Dados
$cargoId = (int)($_GET['id'] ?? 0);
if ($cargoId === 0) {
    // Redireciona de volta para a lista se nenhum ID for fornecido
    header('Location: ../views/cargos.php?message=ID do cargo não fornecido.&type=danger');
    exit;
}

$repo = new CargoRepository();

// ---- INÍCIO DA CORREÇÃO ----
// O método 'getFullCargoDetails' não existe.
// Vamos usar os métodos reais do CargoRepository.

try {
    // 1. Busca os detalhes principais (JOINs com CBO, Nível, Faixa, etc.)
    $details = $repo->find($cargoId);

    if (!$details) {
        throw new Exception("Cargo com ID $cargoId não encontrado.");
    }

    // 2. Busca os dados das tabelas de ligação (Muitos-para-Muitos)
    $habilidades = $repo->findHabilidadesByCargoId($cargoId);
    $cursos = $repo->findCursosByCargoId($cargoId);
    $riscos = $repo->findRiscosByCargoId($cargoId);
    // (Adicione aqui outras buscas se necessário, ex: caracteristicas)

} catch (Exception $e) {
    // Redireciona de volta para a lista com a mensagem de erro
    header('Location: ../views/cargos.php?message=' . urlencode($e->getMessage()) . '&type=danger');
    exit;
}
// ---- FIM DA CORREÇÃO ----


// 5. Definições da Página (para o header.php)
$page_title = "Cargo: " . htmlspecialchars($details['cargoNome']);
$root_path = '../'; 
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Gerenciamento de Cargos' => '../views/cargos.php',
    'Relatório Individual' => null // Página ativa
];
// NOTA: $is_dashboard não é definida (mostra o menu cascata)


// 6. LÓGICA DE NAVEGAÇÃO (Próximo/Anterior)
try {
    $db = Database::getConnection();
    $stmtPrev = $db->prepare("SELECT MAX(cargoId) as prevId FROM cargos WHERE cargoId < :id");
    $stmtPrev->execute([':id' => $cargoId]);
    $prevCargoId = $stmtPrev->fetchColumn();

    $stmtNext = $db->prepare("SELECT MIN(cargoId) as nextId FROM cargos WHERE cargoId > :id");
    $stmtNext->execute([':id' => $cargoId]);
    $nextCargoId = $stmtNext->fetchColumn();
} catch (Exception $e) {
    $prevCargoId = null;
    $nextCargoId = null;
    // Não é um erro fatal, apenas desativa os botões
}


// 7. Inclui o Header
include '../includes/header.php';

/*
 * NOTA: A lógica de GERAÇÃO DE PDF (format=pdf) foi movida 
 * para um ficheiro separado (ex: /relatorios/gerador_pdf.php)
 * para não conflitar com o layout HTML do header.php.
 *
 * Este ficheiro (cargo_individual.php) agora é APENAS para a
 * visualização HTML.
 */

?>

<style>
    .report-header {
        text-align: center;
        border-bottom: 2px solid #000;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }
    .report-header h1 {
        margin: 0;
        font-size: 2rem;
    }
    .report-header h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: normal;
        color: #555;
    }
    .section-title {
        background-color: #f0f0f0;
        padding: 10px;
        font-size: 1.2rem;
        font-weight: bold;
        border-top: 2px solid #ddd;
        margin-top: 20px;
        margin-bottom: 15px;
    }
    .list-group-item {
        border-bottom: 1px solid #eee !important;
    }
    .list-group-item strong {
        display: inline-block;
        width: 150px;
        color: #333;
    }
    .badge-hardskill { background-color: #0d6efd; }
    .badge-softskill { background-color: #198754; }

    /* Oculta elementos na impressão */
    @media print {
        body {
            padding-top: 0 !important; /* Remove o padding do header */
        }
        header.fixed-top, footer.footer, .breadcrumb, .action-bar {
            display: none !important; /* Esconde header, footer e barras de ação */
        }
        main.main-content {
            margin-bottom: 0 !important;
        }
        .card-report {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>

<div class="card shadow-sm mb-4 action-bar">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
        
        <div>
            <a href="../views/cargos.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar à Lista
            </a>
        </div>
        
        <div class="my-2 my-md-0">
            <a href="cargo_individual.php?id=<?php echo $prevCargoId; ?>" 
               class="btn btn-outline-primary <?php echo $prevCargoId ? '' : 'disabled'; ?>" 
               title="Cargo Anterior">
                <i class="fas fa-chevron-left"></i> Anterior
            </a>
            <a href="cargo_individual.php?id=<?php echo $nextCargoId; ?>" 
               class="btn btn-outline-primary <?php echo $nextCargoId ? '' : 'disabled'; ?>" 
               title="Próximo Cargo">
                Próximo <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        
        <div>
            <a href="javascript:window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Imprimir
            </a>
            <a href="gerador_pdf.php?id=<?php echo $cargoId; ?>" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Gerar PDF
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm card-report" id="reportContent">
    <div class="card-body p-4 p-md-5">

        <div class="report-header mb-4">
            <h1><?php echo htmlspecialchars($details['cargoNome']); ?></h1>
            <h2><?php echo htmlspecialchars($details['areaNome'] ?? 'Área não definida'); ?></h2>
        </div>

        <div class="section-title">Informações Básicas</div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <strong>CBO Oficial:</strong> <?php echo htmlspecialchars($details['cboTituloOficial'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($details['cboCod'] ?? 'N/A'); ?>)
            </li>
            <li class="list-group-item">
                <strong>Família CBO:</strong> <?php echo htmlspecialchars($details['familiaCboNome'] ?? 'N/A'); ?>
            </li>
            <li class="list-group-item">
                <strong>Nível Hierárquico:</strong> <?php echo htmlspecialchars($details['nivelDescricao'] ?? 'N/A'); ?> (Ordem: <?php echo htmlspecialchars($details['nivelOrdem'] ?? 'N/A'); ?>)
            </li>
            <li class="list-group-item">
                <strong>Tipo de Nível:</strong> <?php echo htmlspecialchars($details['tipoNome'] ?? 'N/A'); ?>
            </li>
            <li class="list-group-item">
                <strong>Faixa Salarial:</strong> <?php echo htmlspecialchars($details['faixaNivel'] ?? 'N/A'); ?> (R$ <?php echo number_format($details['faixaSalarioMinimo'] ?? 0, 2, ',', '.'); ?> - R$ <?php echo number_format($details['faixaSalarioMaximo'] ?? 0, 2, ',', '.'); ?>)
            </li>
            <li class="list-group-item">
                <strong>Supervisor Direto:</strong> <?php echo htmlspecialchars($details['supervisorNome'] ?? 'N/A'); ?>
            </li>
        </ul>

        <div class="section-title">Descrição do Cargo</div>
        <div class="p-2">
            <strong>Resumo:</strong>
            <p><?php echo nl2br(htmlspecialchars($details['cargoResumo'] ?? 'N/A')); ?></p>
            <strong>Descrição Detalhada:</strong>
            <p><?php echo nl2br(htmlspecialchars($details['cargoDescricao'] ?? 'N/A')); ?></p>
        </div>

        <div class="section-title">Requisitos Obrigatórios</div>
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-graduation-cap text-primary me-2"></i> Escolaridade Mínima</h6>
                <p class="ms-4"><?php echo htmlspecialchars($details['escolaridadeTitulo'] ?? 'N/A'); ?></p>
                
                <h6><i class="fas fa-certificate text-primary me-2"></i> Cursos e Treinamentos</h6>
                <ul class="list-unstyled ms-4">
                    <?php if (empty($cursos)): ?>
                        <li>Nenhum curso específico.</li>
                    <?php else: ?>
                        <?php foreach ($cursos as $item): ?>
                            <li>
                                <i class="fas fa-check-circle text-success me-1"></i>
                                <?php echo htmlspecialchars($item['cursoNome']); ?>
                                <?php if ($item['cursoCargoObrigatorio']): ?>
                                    <span class="badge bg-danger ms-1">Obrigatório</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-lightbulb text-primary me-2"></i> Habilidades</h6>
                <ul class="list-unstyled ms-4">
                    <?php if (empty($habilidades)): ?>
                        <li>Nenhuma habilidade específica.</li>
                    <?php else: ?>
                        <?php foreach ($habilidades as $item): ?>
                            <li>
                                <?php
                                $badge_class = $item['habilidadeTipo'] === 'Hardskill' ? 'badge-hardskill' : 'badge-softskill';
                                ?>
                                <span class="badge <?php echo $badge_class; ?> me-1"><?php echo $item['habilidadeTipo']; ?></span>
                                <?php echo htmlspecialchars($item['habilidadeNome']); ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="section-title">Riscos de Exposição</div>
        <div class="row p-2">
            <?php if (empty($riscos)): ?>
                <div class="col">Nenhum risco de exposição associado.</div>
            <?php else: ?>
                <?php foreach ($riscos as $item): ?>
                    <div class="col-md-4 mb-2">
                        <i class="<?php echo getRiscoIcon($item['riscoNome']); ?> text-danger me-2"></i>
                        <strong><?php echo htmlspecialchars($item['riscoNome']); ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section-title">Outros Detalhes</div>
        <div class="p-2">
            <strong>Experiência Requerida:</strong>
            <p><?php echo nl2br(htmlspecialchars($details['cargoExperiencia'] ?? 'N/A')); ?></p>
            <strong>Condições de Trabalho:</strong>
            <p><?php echo nl2br(htmlspecialchars($details['cargoCondicoes'] ?? 'N/A')); ?></p>
            <strong>Complexidade:</strong>
            <p><?php echo nl2br(htmlspecialchars($details['cargoComplexidade'] ?? 'N/A')); ?></p>
            <strong>Responsabilidades:</strong>
            <p><?php echo nl2br(htmlspecialchars($details['cargoResponsabilidades'] ?? 'N/A')); ?></p>
        </div>
        
    </div>
</div>


<?php
// 8. Inclui o Footer
include '../includes/footer.php';
?>