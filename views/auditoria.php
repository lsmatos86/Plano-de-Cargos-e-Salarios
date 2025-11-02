<?php
// Arquivo: views/auditoria.php (Listagem de Logs de Auditoria)

// 1. Incluir Autoload e Config
require_once '../vendor/autoload.php';
require_once '../config.php';

// 2. Importar as classes
use App\Repository\AuditRepository;

// 3. Incluir functions.php (para login e helpers)
require_once '../includes/functions.php';

// 4. Segurança
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
// Verificação de permissão (Ex: Apenas admin pode ver)
// $authService->checkAndFail('auditoria:view', '../index.php?error=Acesso+negado');

// 5. Definições da Página (para o header.php)
$page_title = 'Logs de Auditoria';
$root_path = '../'; 
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Logs de Auditoria' => null // Página ativa
];

// --- Início da Lógica da Página ---

$auditRepo = new AuditRepository(); 

// ----------------------------------------------------
// 1. LÓGICA DE LEITURA E FILTRO
// ----------------------------------------------------

// 1.1. Buscar opções para os filtros
$acoesDisponiveis = $auditRepo->getDistinctAcoes();
$tabelasDisponiveis = $auditRepo->getDistinctTabelas();

// 1.2. Parâmetros de Filtro e Paginação
$params = [
    'term' => $_GET['term'] ?? '',
    'acao' => $_GET['acao'] ?? '',
    'nomeTabela' => $_GET['nomeTabela'] ?? '',
    'page' => $_GET['page'] ?? 1,
    'limit' => 20 // 20 logs por página
];

// 1.3. Busca os dados usando o Repositório
try {
    $result = $auditRepo->findAllPaginated($params);
    
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

// --- Fim da Lógica da Página ---

// 6. Inclui o Header (HTML, <head>, <nav>, etc.)
include '../includes/header.php';

?>

<style>
    /* Estilos locais para a página de auditoria */
    .badge-acao {
        font-size: 0.8em;
        font-weight: bold;
        padding: 0.3em 0.6em;
        border-radius: 0.25rem;
    }
    .badge-create { background-color: #d1e7dd; color: #0f5132; }
    .badge-update { background-color: #cff4fc; color: #055160; }
    .badge-delete { background-color: #f8d7da; color: #842029; }
    .badge-login-success { background-color: #d3d3d4; color: #146c43; }
    .badge-login-fail { background-color: #fff3cd; color: #664d03; }
    
    .details-json {
        max-height: 200px;
        overflow-y: auto;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 10px;
        border-radius: 5px;
        font-size: 0.8em;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><?php echo $page_title; ?></h1>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-3">
                <label for="acao" class="visually-hidden">Ação</label>
                <select name="acao" id="acao" class="form-select form-select-sm">
                    <option value="">-- Todas as Ações --</option>
                    <?php foreach ($acoesDisponiveis as $acao): ?>
                        <option value="<?php echo htmlspecialchars($acao); ?>" <?php echo ($params['acao'] == $acao) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($acao); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="nomeTabela" class="visually-hidden">Tabela</label>
                <select name="nomeTabela" id="nomeTabela" class="form-select form-select-sm">
                    <option value="">-- Todas as Tabelas --</option>
                    <?php foreach ($tabelasDisponiveis as $tabela): ?>
                        <option value="<?php echo htmlspecialchars($tabela); ?>" <?php echo ($params['nomeTabela'] == $tabela) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tabela); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                 <label for="term" class="visually-hidden">Termo</label>
                <input type="search" name="term" id="term" class="form-control form-control-sm" placeholder="Buscar em Usuário, ID ou Detalhes..." value="<?php echo htmlspecialchars($params['term']); ?>">
            </div>
            <div class="col-md-2 d-flex">
                <button class="btn btn-primary btn-sm w-100" type="submit">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="auditoria.php" class="btn btn-outline-secondary btn-sm ms-2" title="Limpar Filtros">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 15%;">Data/Hora</th>
                        <th style="width: 15%;">Usuário</th>
                        <th style="width: 15%;">Ação</th>
                        <th style="width: 10%;">Tabela</th>
                        <th style="width: 5%;">ID Reg.</th>
                        <th style="width: 40%;">Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <?php
                            // Formata a Ação para o CSS
                            $badge_class = 'badge-' . strtolower(str_replace('_', '-', $row['acao']));
                            ?>
                            <tr>
                                <td><?php echo (new DateTime($row['dataHora']))->format('d/m/Y H:i:s'); ?></td>
                                <td><?php echo htmlspecialchars($row['nomeUsuario'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge-acao <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($row['acao']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['nomeTabela'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['idRegistro'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if (!empty($row['dadosJson']) && $row['dadosJson'] != 'null'): ?>
                                        <details>
                                            <summary style="cursor: pointer;">Ver detalhes</summary>
                                            <pre class="details-json"><?php echo htmlspecialchars(json_encode(json_decode($row['dadosJson']), JSON_PRETTY_PRINT)); ?></pre>
                                        </details>
                                    <?php else: ?>
                                        <em class="text-muted">Nenhum detalhe</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center p-4">
                                <i class="fas fa-info-circle fa-2x text-muted mb-2"></i><br>
                                Nenhum log encontrado com os filtros atuais.
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
            <ul class="pagination pagination-sm mb-0">
                
                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                    <?php $prev_query = http_build_query(array_merge($params, ['page' => $currentPage - 1])); ?>
                    <a class="page-link" href="?<?php echo $prev_query; ?>">Anterior</a>
                </li>

                <?php 
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
include '../includes/footer.php';
?>