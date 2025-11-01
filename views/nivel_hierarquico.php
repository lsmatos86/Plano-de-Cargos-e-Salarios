<?php
// Arquivo: views/nivel_hierarquico.php (REFATORADO - CORRIGIDO)

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // Para login e helpers

// 2. Importa os Repositórios
use App\Repository\NivelHierarquicoRepository;
use App\Repository\LookupRepository;

// 3. AVISA AO SCRIPT PARA USAR A VARIÁVEL GLOBAL
global $authService; 

// 4. Segurança
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
// (OPCIONAL - Verificação de permissão)
// $authService->checkAndFail('config:view', '../index.php?error=Acesso+negado');


// 5. Definições da Página (para o header.php)
$page_title = 'Gerenciamento de Níveis Hierárquicos';
$root_path = '../'; 
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Níveis Hierárquicos' => null // Página ativa
];
// NOVO: Informa ao footer.php qual script JS carregar
$page_scripts = ['../scripts/nivel_hierarquico.js'];


// Configurações
$id_column = 'nivelId';
$message = '';
$message_type = '';

// Instancia os Repositórios
$repo = new NivelHierarquicoRepository();
$lookupRepo = new LookupRepository();

// Carrega os Tipos de Hierarquia para o SELECT do formulário
try {
    $tiposHierarquia = $lookupRepo->getLookup('tipo_hierarquia', 'tipoId', 'tipoNome');
} catch (Exception $e) {
    $tiposHierarquia = [];
    $message = "Erro ao carregar tipos de hierarquia: " . $e->getMessage();
    $message_type = 'danger';
}


// ----------------------------------------------------
// LÓGICA DE CRUD (CREATE/UPDATE/DELETE)
// ----------------------------------------------------
try {
    // 1. Lógica de CREATE/UPDATE (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $repo->save($_POST); // O repositório lida com insert/update e validação
        
        $ordem = (int)($_POST['nivelOrdem'] ?? 0);
        $id = (int)($_POST[$id_column] ?? 0);
        $action_desc = ($id > 0) ? 'atualizado' : 'cadastrado';
        $message = "Nível Hierárquico (Ordem: {$ordem}) {$action_desc} com sucesso!";
        $message_type = 'success';
    }

    // 2. Lógica de DELETE (GET)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $deleted = $repo->delete($id);
        
        if ($deleted) {
            $message = "Nível Hierárquico ID {$id} excluído com sucesso!";
            $message_type = 'success';
        } else {
            $message = "Erro: Nível Hierárquico ID {$id} não encontrado ou já excluído.";
            $message_type = 'danger';
        }
        
        // Redireciona para limpar a URL após a ação
        header("Location: nivel_hierarquico.php?message=" . urlencode($message) . "&type={$message_type}");
        exit;
    }

} catch (Exception $e) {
    // Captura qualquer exceção do Repositório (validação, FK, DB)
    $message = $e->getMessage();
    $message_type = 'danger';
}

// Mensagens vindas de um redirecionamento
if (empty($message) && isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// ----------------------------------------------------
// LÓGICA DE LEITURA (READ)
// ----------------------------------------------------
// 1. Parâmetros de Filtro e Ordenação
$params = [
    'term' => $_GET['term'] ?? '',
    'sort_col' => $_GET['sort_col'] ?? 'n.nivelOrdem', // Padrão de ordenação
    'sort_dir' => $_GET['sort_dir'] ?? 'DESC',       // Padrão de ordenação
    'page' => $_GET['page'] ?? 1,
    'limit' => 10
];

// 2. Busca os dados
try {
    $repoParams = [
        'term' => $params['term'],
        'order_by' => $params['sort_col'], 
        'sort_dir' => $params['sort_dir'],
        'page' => $params['page'],
        'limit' => $params['limit']
    ];
    
    $result = $repo->findAllPaginated($repoParams);
    
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


// 7. Inclui o Header
include '../includes/header.php';
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white" id="formHeader">
                <h5 class="mb-0" id="formTitle"><i class="fas fa-plus"></i> Novo Nível</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="nivel_hierarquico.php" id="cadastroForm">
                    <input type="hidden" name="<?php echo $id_column; ?>" id="formId" value="">
                    
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="formOrdem" class="form-label">Ordem (ex: 1) *</label>
                            <input type="number" class="form-control" id="formOrdem" name="nivelOrdem" required min="1" max="99">
                        </div>
                        <div class="col-md-7 mb-3">
                            <label for="formTipo" class="form-label">Tipo *</label>
                            <select class="form-select" id="formTipo" name="tipoId" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tiposHierarquia as $id => $nome): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($nome); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="formDescricao" class="form-label">Descrição (ex: Diretoria, Gerência) *</label>
                        <input type="text" class="form-control" id="formDescricao" name="nivelDescricao" required maxlength="100">
                    </div>
                    
                    <div class="mb-3">
                        <label for="formAtribuicoes" class="form-label">Atribuições Gerais</label>
                        <textarea class="form-control" id="formAtribuicoes" name="nivelAtribuicoes" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="formAutonomia" class="form-label">Autonomia</label>
                        <textarea class="form-control" id="formAutonomia" name="nivelAutonomia" rows="3"></textarea>
                    </div>
                     <div class="mb-3">
                        <label for="formQuandoUtilizar" class="form-label">Quando Utilizar</label>
                        <textarea class="form-control" id="formQuandoUtilizar" name="nivelQuandoUtilizar" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" id="btnSalvar">
                        <i class="fas fa-check"></i> Salvar
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100 mt-2" id="btnLimpar">
                        Limpar Formulário
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <form method="GET" class="d-flex mb-3">
            <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por descrição ou tipo..." value="<?php echo htmlspecialchars($params['term']); ?>">
            <input type="hidden" name="sort_col" value="<?php echo htmlspecialchars($params['sort_col']); ?>">
            <input type="hidden" name="sort_dir" value="<?php echo htmlspecialchars($params['sort_dir']); ?>">
            
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            <?php if (!empty($params['term'])): ?>
                <a href="nivel_hierarquico.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <span class="fw-bold">Registros Encontrados: </span> <?php echo $totalRecords; ?> (Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <style> /* Estilo local para a tabela */
                                    .short-text { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                                </style>
                                
                                <th><?php echo createSortLink('n.nivelOrdem', 'Ordem', $params); ?></th>
                                <th><?php echo createSortLink('n.nivelDescricao', 'Descrição', $params); ?></th>
                                <th><?php echo createSortLink('t.tipoNome', 'Tipo', $params); ?></th>
                                <th><?php echo createSortLink('n.nivelAtribuicoes', 'Atribuições', $params); ?></th>
                                <th width="120px" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registros) > 0): ?>
                                <?php foreach ($registros as $row): ?>
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($row['nivelOrdem']); ?>º</span>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($row['nivelDescricao']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['tipoNome'] ?? 'N/A'); ?></td>
                                        <td class="short-text" title="<?php echo htmlspecialchars($row['nivelAtribuicoes']); ?>">
                                            <?php echo htmlspecialchars($row['nivelAtribuicoes']); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="btn btn-sm btn-info text-white btn-edit"
                                                data-id="<?php echo $row['nivelId']; ?>"
                                                data-ordem="<?php echo htmlspecialchars($row['nivelOrdem']); ?>"
                                                data-descricao="<?php echo htmlspecialchars($row['nivelDescricao']); ?>"
                                                data-tipoid="<?php echo htmlspecialchars($row['tipoId']); ?>"
                                                data-atribuicoes="<?php echo htmlspecialchars($row['nivelAtribuicoes']); ?>"
                                                data-autonomia="<?php echo htmlspecialchars($row['nivelAutonomia']); ?>"
                                                data-quando="<?php echo htmlspecialchars($row['nivelQuandoUtilizar']); ?>"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="nivel_hierarquico.php?action=delete&id=<?php echo $row[$id_column]; ?>" 
                                                class="btn btn-sm btn-danger" 
                                                title="Excluir"
                                                onclick="return confirm('ATENÇÃO: A exclusão deste nível pode afetar os Cargos. Deseja realmente excluir?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="text-center">
                                    <td colspan="5" class="p-4">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i><br>
                                        Nenhum registro encontrado.
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
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        if ($endPage - $startPage < 4) { $startPage = max(1, $endPage - 4); }
                        if ($endPage - $startPage < 4) { $endPage = min($totalPages, $startPage + 4); }
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
        </div>
</div>

<?php
// 8. Inclui o Footer
include '../includes/footer.php';
?>