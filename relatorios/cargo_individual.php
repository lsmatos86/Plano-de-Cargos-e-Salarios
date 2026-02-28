<?php
// Arquivo: relatorios/cargo_individual.php (Visualizador HTML Completo)

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
$format = 'html'; // Este ficheiro agora só serve HTML

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

    // Busca de navegação
    //
    $adjacentIds = $cargoRepository->findAdjacentCargoIds($cargo_id, $sort_col, $sort_dir, $term);
    //
    $extremityIds = $cargoRepository->findFirstAndLastCargoIds($sort_col, $sort_dir, $term);

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
    'sort_col' => $sort_col,
    'sort_dir' => $sort_dir,
    'term' => $term
]);

// Links de navegação
$first_link = $first_id ? "cargo_individual.php?id={$first_id}&{$nav_params_base}" : null;
$prev_link  = $prev_id  ? "cargo_individual.php?id={$prev_id}&{$nav_params_base}" : null;
$next_link  = $next_id  ? "cargo_individual.php?id={$next_id}&{$nav_params_base}" : null;
$last_link  = $last_id  ? "cargo_individual.php?id={$last_id}&{$nav_params_base}" : null;

// Parâmetros do PDF (para o botão)
$pdf_params = http_build_query([
    'id' => $cargo_id,
    'sort_col' => $sort_col, // Passa o contexto de ordenação
    'sort_dir' => $sort_dir,
    'term' => $term
]);


// ----------------------------------------------------
// 9. GERAÇÃO DO HTML (Bufferizado)
// ----------------------------------------------------

// ======================================================
// ATUALIZAÇÃO: Inicializa o contador de secção
// ======================================================
$section_counter = 1;

ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório - Cargo: <?php echo htmlspecialchars($cargo['cargoNome']); ?></title>
    
    <link rel="stylesheet" href="../css/relatorio_style.css">
    
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
            flex-wrap: wrap; 
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
        
</head>
<body class="html-render">
<div class="container">

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
            <input type="hidden" name="sort_col" value="<?php echo htmlspecialchars($sort_col); ?>">
            <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($sort_dir); ?>">
            <input type="hidden" name="term" value="<?php echo htmlspecialchars($term); ?>">
            
            <input type="number" class="form-control form-control-sm" name="id" placeholder="Ir para ID..." value="<?php echo $cargo_id; ?>" required>
            <button type="submit" class="btn btn-outline-primary btn-sm">Ir</button>
        </form>
        
        <div class="btn-group nav-group" role="group">
            <a href="cargo_pdf.php?<?php echo $pdf_params; ?>" class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <button onclick="window.print()" class="btn btn-success btn-sm">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>
<?php if (!empty($cargo['is_revisado'])): ?>
        <div style="text-align: right; margin-bottom: -15px;">
            <div style="display: inline-block; border: 2px solid #198754; color: #198754; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 12px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;">
                <i class="fas fa-check-double"></i> Documento Revisado e Aprovado
            </div>
        </div>
    <?php endif; ?>
    <?php
    // Define a variável de controlo e inclui o template
    $show_hierarquia = true;
    include '../includes/templates/_template_cargo.php';
    ?>

</div> 
<div style="margin-top: 40px; padding-top: 10px; border-top: 1px solid #ddd; text-align: center; color: #777; font-size: 9px; font-family: Arial, sans-serif;">
    Documento oficial gerado pelo Sistema de Gestão de Cargos e Salários | Itacitrus<br>
    
    Criado em: <?php echo !empty($cargo['cargoDataCadastro']) ? date('d/m/Y H:i', strtotime($cargo['cargoDataCadastro'])) : 'N/D'; ?> 
    &nbsp;|&nbsp; 
    Última Revisão: <?php echo !empty($cargo['data_revisao']) ? date('d/m/Y H:i', strtotime($cargo['data_revisao'])) : 'Sem revisão registada'; ?>
</div>
</body>
</html>
<?php
// Fim do buffer HTML
echo ob_get_clean();
exit;
?>