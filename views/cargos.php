<?php
// Arquivo: views/cargos.php (Listagem e Gerenciamento de Cargos - REFATORADO)

// 1. Incluir Autoload e Config
require_once '../vendor/autoload.php';
require_once '../config.php';

// 2. Importar as classes
use App\Repository\CargoRepository;

// 3. Incluir functions.php (ainda necessário para login e getSortDirection)
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Gerenciamento de Cargos';
$message = '';
$message_type = '';
$cargoRepo = new CargoRepository(); // Instancia o repositório

// ----------------------------------------------------
// 1. LÓGICA DE EXCLUSÃO (DELETE) - (Já refatorado no passo anterior)
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $deleted = $cargoRepo->delete($id);
        
        if ($deleted) {
            $message = "Cargo ID {$id} excluído com sucesso!";
            $message_type = 'success';
        } else {
            $message = "Erro: Cargo ID {$id} não encontrado.";
            $message_type = 'danger';
        }

    } catch (Exception $e) {
        $message = "Erro fatal ao excluir: " . $e->getMessage();
        $message_type = 'danger';
    }

    // Redireciona para limpar a URL
    header("Location: cargos.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 2. LÓGICA DE LEITURA, FILTRO E PAGINAÇÃO (READ All) - REFATORADO
// ----------------------------------------------------

// 2.1. Parâmetros de Filtro e Ordenação
$params = [
    'term' => $_GET['term'] ?? '',
    'order_by' => $_GET['order_by'] ?? 'c.cargoId',
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC',
    'page' => $_GET['page'] ?? 1,
    'limit' => 10 // Define o limite de itens por página
];

// 2.2. Busca os dados usando o Repositório
try {
    $result = $cargoRepo->findAllPaginated($params);
    
    $registros = $result['data'];
    $totalRecords = $result['total'];
    $totalPages = $result['totalPages'];
    $currentPage = $result['currentPage'];

} catch (Exception $e) {
    $registros = [];
    $totalRecords = 0;
    $totalPages = 1;
    $currentPage = 1;
    $message = "Erro ao carregar dados: " . $e->getMessage();
    $message_type = 'danger';
}


// Verifica e exibe mensagens após redirecionamento
if (isset($_GET['message']) && empty($message)) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .short-text { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .action-cell { width: 220px; } 
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid container">
        <a class="navbar-brand" href="../index.php">ITACITRUS | Início</a>
        <div class="d-flex">
            <span class="navbar-text me-3 text-white">Olá, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuário'); ?></span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-outline-secondary btn-sm" onclick="history.back()">
            <i class="fas fa-arrow-left"></i> Voltar
        </button>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Página Inicial</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $page_title; ?></li>
            </ol>
        </nav>
    </div>
    
    <h1 class="mb-4"><?php echo $page_title; ?></h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-4">
            <a href="cargos_form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Cargo
            </a>
        </div>
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por Nome, Resumo ou CBO Oficial" value="<?php echo htmlspecialchars($params['term']); ?>">
                <input type="hidden" name="order_by" value="<?php echo htmlspecialchars($params['order_by']); ?>">
                <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
                
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                <?php if (!empty($params['term'])): ?>
                    <a href="cargos.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i> Limpar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <span class="fw-bold">Cargos Encontrados: </span> <?php echo $totalRecords; ?> (Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>)
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <?php 
                        // Função auxiliar para criar o link de ordenação
                        function createSortLink($column, $text, $params) {
                            $new_dir = getSortDirection($params['order_by'], $column);
                            $icon = 'fa-sort';
                            if ($params['order_by'] === $column) {
                                $icon = $new_dir === 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
                            }
                            // Mantém o filtro 'term' e define a página como 1 ao ordenar
                            $query_params = http_build_query(array_merge($params, ['order_by' => $column, 'sort_dir' => $new_dir, 'page' => 1]));
                            return '<a href="?' . $query_params . '" class="text-decoration-none text-dark"><i class="fas ' . $icon . ' me-1"></i> ' . $text . '</a>';
                        }
                        ?>
                        <th><?php echo createSortLink('c.cargoId', 'ID', $params); ?></th>
                        <th><?php echo createSortLink('c.cargoNome', 'Cargo', $params); ?></th>
                        <th><?php echo createSortLink('b.cboTituloOficial', 'CBO Oficial', $params); ?></th>
                        <th class="action-cell text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['cargoId']); ?></td>
                                <td><strong class="text-primary"><?php echo htmlspecialchars($row['cargoNome']); ?></strong></td> 
                                <td><?php echo htmlspecialchars($row['cboTituloOficial'] ?? 'N/A'); ?></td>
                                <td class="action-cell text-center">
                                    
                                    <a href="../relatorios/cargo_individual.php?id=<?php echo $row['cargoId']; ?>&format=html" 
                                       class="btn btn-sm btn-outline-secondary" 
                                       title="Visualizar HTML" 
                                       target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="../relatorios/cargo_individual.php?id=<?php echo $row['cargoId']; ?>&format=pdf" 
                                       class="btn btn-sm btn-secondary" 
                                       title="Gerar PDF" 
                                       target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    
                                    <span class="mx-1"></span> 

                                    <a href="cargos_form.php?id=<?php echo $row['cargoId']; ?>" 
                                        class="btn btn-sm btn-info text-white" 
                                        title="Editar Configurações">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <a href="cargos.php?action=delete&id=<?php echo $row['cargoId']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Excluir Cargo"
                                       onclick="return confirm('ATENÇÃO: Excluir este cargo removerá todos os seus requisitos associados (Habilidades, Riscos, etc.). Deseja realmente excluir?');">
                                       <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum cargo encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Navegação de página" class="mt-4">
        <ul class="pagination justify-content-center">
            
            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                <?php $prev_query = http_build_query(array_merge($params, ['page' => $currentPage - 1])); ?>
                <a class="page-link" href="?<?php echo $prev_query; ?>">Anterior</a>
            </li>

            <?php 
            // Lógica para mostrar no máximo 5 botões de página
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);

            // Ajusta o intervalo para garantir 5 páginas se possível
            if ($endPage - $startPage < 4) {
                $startPage = max(1, $endPage - 4);
            }
            if ($endPage - $startPage < 4) {
                $endPage = min($totalPages, $startPage + 4);
            }

            for ($i = $startPage; $i <= $endPage; $i++): 
                $page_query = http_build_query(array_merge($params, ['page' => $i]));
            ?>
                <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                    <a class="page-link" href="?<?php echo $page_query; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                <?php $next_query = http_build_query(array_merge($params, ['page' => $currentPage + 1])); ?>
                <a class="page-link" href="?<?php echo $next_query; ?>">Próxima</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>