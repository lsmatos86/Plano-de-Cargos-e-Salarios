<?php
// Arquivo: views/usuarios.php (Refatorado com Header/Footer)

// 1. INCLUDES E INICIALIZAÇÃO
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // Inclui o $authService

// 2. IMPORTAÇÕES
use App\Repository\UsuarioRepository;

// 3. SEGURANÇA: Verifica o login e a permissão
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
// $authService foi instanciado em functions.php
// Apenas quem pode gerenciar usuários pode ver esta página
$authService->checkAndFail('usuarios:manage', '../index.php');

// 4. INICIALIZAÇÃO DE VARIÁVEIS
$message = $_GET['message'] ?? '';
$message_type = $_GET['type'] ?? 'info';
$repo = new UsuarioRepository();

// Variáveis para o formulário
$editUser = [
    'usuarioId' => 0,
    'nome' => '',
    'email' => '',
    'ativo' => 1
];
$userRoles = []; // Papéis que este usuário JÁ POSSUI

// 5. PROCESSAMENTO DE AÇÕES (POST/GET)
try {
    // AÇÃO: SALVAR (CREATE ou UPDATE)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $savedId = $repo->save($_POST);
        $action = (int)($_POST['usuarioId'] ?? 0) > 0 ? 'atualizado' : 'criado';
        header("Location: usuarios.php?message=Usuário {$action} com sucesso.&type=success");
        exit;
    }
    
    // AÇÃO: DELETAR (GET)
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $repo->delete($id);
        header("Location: usuarios.php?message=Usuário excluído com sucesso.&type=success");
        exit;
    }
    
    // AÇÃO: EDITAR (GET)
    if (isset($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        $editUser = $repo->find($id);
        if ($editUser) {
            $userRoles = $repo->findRoleIds($id);
        } else {
            // Se o usuário não for encontrado, redireciona
            header("Location: usuarios.php?message=Usuário não encontrado.&type=danger");
            exit;
        }
    }
} catch (Exception $e) {
    $message = "Erro: " . $e->getMessage();
    $message_type = 'danger';
    
    // Repopula o formulário em caso de erro no POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $editUser = [
            'usuarioId' => (int)($_POST['usuarioId'] ?? 0),
            'nome' => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'ativo' => isset($_POST['ativo']) ? 1 : 0
        ];
        $userRoles = $_POST['roleIds'] ?? [];
    }
}

// 6. BUSCA DE DADOS PARA PREENCHER O FORMULÁRIO E A TABELA
$allRoles = $repo->getAllRoles(); // Papéis disponíveis para o <select>
$loggedUserId = (int)($_SESSION['user_id'] ?? 0); // ID do usuário logado

// Parâmetros de Paginação/Filtro
$params = [
    'term' => $_GET['term'] ?? '',
    'page' => $_GET['page'] ?? 1,
    'limit' => 10
];

$result = $repo->findAllPaginated($params);
$registros = $result['data'];
$totalRecords = $result['total'];
$totalPages = $result['totalPages'];
$currentPage = $result['currentPage'];

// ======================================================
// 7. DEFINIÇÕES DA PÁGINA (PARA HEADER.PHP)
// ======================================================
$page_title = "Gestão de Usuários";
$root_path = "../";
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Gestão de Usuários' => null // Página ativa
];

// Define CSS e JS específicos para esta página
$extra_head_content = '
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .form-control-sm { min-height: calc(1.5em + 0.5rem + 2px); }
        .select2-container--bootstrap-5 .select2-selection--multiple {
            min-height: calc(1.5em + 0.5rem + 2px) !important;
            padding: 0.25rem 0.5rem;
        }
    </style>
';

// ======================================================
// 8. INCLUI O HEADER PADRONIZADO
// ======================================================
include '../includes/header.php';

// ======================================================
// O <!DOCTYPE html>, <head>, <nav> manual foi REMOVIDO
// ======================================================
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <?php echo $editUser['usuarioId'] > 0 ? '<i class="fas fa-edit me-2"></i>Editar Usuário' : '<i class="fas fa-plus me-2"></i>Novo Usuário'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="usuarios.php">
                    <input type="hidden" name="usuarioId" value="<?php echo $editUser['usuarioId']; ?>">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($editUser['nome']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($editUser['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha <?php echo $editUser['usuarioId'] > 0 ? '(Deixe em branco para não alterar)' : '*'; ?></label>
                        <input type="password" class="form-control" id="senha" name="senha" <?php echo $editUser['usuarioId'] == 0 ? 'required' : ''; ?>>
                    </div>
                    
                    <div class="mb-3">
                        <label for="roleIds" class="form-label">Papéis (Permissões) *</label>
                        <select class="form-select" id="roleIds" name="roleIds[]" multiple="multiple" required>
                            <?php foreach ($allRoles as $role): ?>
                                <option value="<?php echo $role['roleId']; ?>" <?php echo in_array($role['roleId'], $userRoles) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['roleName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" <?php echo $editUser['ativo'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="ativo">Usuário Ativo</label>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-2"></i> Salvar
                        </button>
                        <?php if ($editUser['usuarioId'] > 0): ?>
                            <a href="usuarios.php" class="btn btn-outline-secondary">
                                Cancelar Edição
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <form method="GET" class="d-flex">
                    <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por nome ou e-mail..." value="<?php echo htmlspecialchars($params['term']); ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if (!empty($params['term'])): ?>
                        <a href="usuarios.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Papéis</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $user): ?>
                                <?php
                                $isSelf = ($user['usuarioId'] == $loggedUserId);
                                $isAdmin1 = ($user['usuarioId'] == 1);
                                ?>
                                <tr>
                                    <td><?php echo $user['usuarioId']; ?></td>
                                    <td><?php echo htmlspecialchars($user['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['papeis'] ?? 'Nenhum'); ?></td>
                                    <td class="text-center">
                                        <?php if ($user['ativo']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="usuarios.php?edit=<?php echo $user['usuarioId']; ?>" class="btn btn-sm btn-info text-white" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="usuarios.php?delete=<?php echo $user['usuarioId']; ?>" 
                                           class="btn btn-sm btn-danger <?php echo ($isSelf || $isAdmin1) ? 'disabled' : ''; ?>" 
                                           title="<?php echo ($isSelf || $isAdmin1) ? 'Não pode ser excluído' : 'Excluir'; ?>"
                                           onclick="return <?php echo ($isSelf || $isAdmin1) ? 'false' : "confirm('Tem certeza que deseja excluir este usuário?');"; ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
// ======================================================
// 9. INCLUI O FOOTER PADRONIZADO
// ======================================================

// Define scripts JS específicos para esta página
$extra_scripts = '
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        // Inicializa o Select2 para o campo de papéis
        $(document).ready(function() {
            $(\'#roleIds\').select2({
                theme: "bootstrap-5",
                width: \'100%\',
                placeholder: "Selecione um ou mais papéis",
                allowClear: true
            });
        });
    </script>
';

include '../includes/footer.php';
?>