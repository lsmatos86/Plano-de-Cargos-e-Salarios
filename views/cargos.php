<?php
// Arquivo: views/cargos.php (Listagem - REFATORADO COM HEADER/FOOTER)

// 1. Incluir Autoload e Config
require_once '../vendor/autoload.php';
require_once '../config.php';

// 2. Importar as classes
use App\Repository\CargoRepository;

// 3. Incluir functions.php (agora inclui login E os helpers de ordenação)
//
require_once '../includes/functions.php';

// 4. Segurança
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
// Verificação de permissão (descomente se quiser ativar)
// $authService->checkAndFail('cargos:view', '../index.php?error=Acesso+negado');

// 5. Definições da Página (para o header.php)
$page_title = 'Gerenciamento de Cargos';
$root_path = '../'; // Define o caminho para a raiz
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Gerenciamento de Cargos' => null // Página ativa
];
// NOTA: $is_dashboard não é definida, por isso o header assume 'false' (e mostra o menu cascata)

// --- Início da Lógica da Página ---

$message = '';
$message_type = '';
$cargoRepo = new CargoRepository(); // Instancia o repositório

// ----------------------------------------------------
// 1. LÓGICA DE EXCLUSÃO (DELETE)
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // (Adicionar verificação de permissão de exclusão se necessário)
    // $authService->checkAndFail('cargos:delete', 'cargos.php?error=Acesso+negado');

    try {
        //
        $deleted = $cargoRepo->delete($id);
        
        if ($deleted) {
            $message = "Cargo ID {$id} excluído com sucesso!";
            $message_type = 'success';
        } else {
            $message = "Erro: Cargo ID {$id} não encontrado.";
            $message_type = 'danger';
        }

    } catch (Exception $e) {
        // Verifica se é um erro de chave estrangeira (ex: cargo usado por um usuário)
        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            $message = "Erro: Este cargo não pode ser excluído pois está em uso em outra parte do sistema.";
            $message_type = 'danger';
        } else {
            $message = "Erro fatal ao excluir: " . $e->getMessage();
            $message_type = 'danger';
        }
    }

    // Redireciona para limpar a URL
    header("Location: cargos.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 2. LÓGICA DE LEITURA, FILTRO E PAGINAÇÃO (READ All)
// ----------------------------------------------------

// 2.1. Parâmetros de Filtro e Ordenação (Alinhado com functions.php)
$params = [
    'term' => $_GET['term'] ?? '',
    'sort_col' => $_GET['sort_col'] ?? 'c.cargoId', // Usar 'sort_col' para createSortLink
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC',
    'page' => $_GET['page'] ?? 1,
    'limit' => 10 // Define o limite de itens por página
];

// 2.2. Busca os dados usando o Repositório
try {
    // "Traduz" 'sort_col' para 'order_by' que o Repositório espera
    $repoParams = [
        'term' => $params['term'],
        'order_by' => $params['sort_col'], // Repositório espera 'order_by'
        'sort_dir' => $params['sort_dir'],
        'page' => $params['page'],
        'limit' => $params['limit']
    ];
    
    //
    $result = $cargoRepo->findAllPaginated($repoParams);
    
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


// Verifica e exibe mensagens (vindas da exclusão, por ex.)
if (isset($_GET['message']) && empty($message)) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// --- Fim da Lógica da Página ---

// 6. Inclui o Header (HTML, <head>, <nav>, etc.)
//
include '../includes/header.php';

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><?php echo $page_title; ?></h1>
    <a href="cargos_form.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Cargo
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="d-flex">
            <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por Nome, Resumo ou CBO Oficial" value="<?php echo htmlspecialchars($params['term']); ?>">
            
            <input type="hidden" name="sort_col" value="<?php echo htmlspecialchars($params['sort_col']); ?>">
            <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
            
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            <?php if (!empty($params['term'])): ?>
                <a href="cargos.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <style> /* Estilos locais para a tabela */
                           .short-text { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                           .action-cell { width: 220px; white-space: nowrap; } 
                        </style>
                        
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
                                    
                                    <?php
                                    // Prepara os parâmetros de navegação (para o visualizador)
                                    $nav_params = http_build_query([
                                        'id' => $row['cargoId'],
                                        'sort_col' => $params['sort_col'],
                                        'sort_dir' => $params['sort_dir'],
                                        'term' => $params['term']
                                    ]);
                                    ?>

                                    <a href="../relatorios/cargo_individual.php?<?php echo $nav_params; ?>" 
                                       class="btn btn-sm btn-outline-secondary" 
                                       title="Visualizar HTML" 
                                       target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="../relatorios/cargo_pdf.php?id=<?php echo $row['cargoId']; ?>" 
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
                            <td colspan="4" class="text-center p-4">
                                <i class="fas fa-info-circle fa-2x text-muted mb-2"></i><br>
                                Nenhum cargo encontrado com os filtros atuais.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($totalRecords > 0): ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted">
            Total: <strong><?php echo $totalRecords; ?></strong> registo(s)
        </span>

        <?php if ($totalPages > 1): ?>
        <nav aria-label="Navegação de página">
            <ul class="pagination mb-0">
                
                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                    <?php $prev_query = http_build_query(array_merge($params, ['page' => $currentPage - 1])); ?>
                    <a class="page-link" href="?<?php echo $prev_query; ?>">Anterior</a>
                </li>

                <?php 
                // Lógica para mostrar no máximo 5 botões de página
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

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
    <?php endif; ?>
</div>

<?php
// 7. Inclui o Footer (Scripts JS, </body>, </html>)
//
include '../includes/footer.php';
?>