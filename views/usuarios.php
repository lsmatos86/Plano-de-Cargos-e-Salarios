<?php
// Arquivo: views/usuarios.php (Gestão de Usuários e Papéis)

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
$page_title = "Gestão de Usuários";
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
        $userId = $repo->save($_POST);
        header("Location: usuarios.php?message=Usuário salvo com sucesso. ID: $userId&type=success");
        exit;
    }

    // AÇÃO: EDITAR (Carregar dados para o formulário)
    if (isset($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        $editUser = $repo->find($id);
        if (!$editUser) {
            throw new Exception("Usuário não encontrado.");
        }
        // Busca os IDs dos papéis que este usuário já tem
        $userRoles = $repo->findRoleIds($id);
        $page_title = "Editando Usuário: " . htmlspecialchars($editUser['nome']);
    }
    
    // AÇÃO: DELETAR
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $repo->delete($id);
        header("Location: usuarios.php?message=Usuário excluído com sucesso.&type=success");
        exit;
    }

} catch (Exception $e) {
    $message = $e->getMessage();
    $message_type = 'danger';
}

// 6. BUSCAR DADOS PARA EXIBIÇÃO
$params = [
    'page' => $_GET['page'] ?? 1,
    'limit' => 10,
    'term' => $_GET['term'] ?? ''
];
$pagination = $repo->findAllPaginated($params);
$allRoles = $repo->getAllRoles(); // Lista de papéis para o formulário

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>
<body>

<?php include '../includes/navbar.php'; // Inclui o menu de navegação ?>

<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0"><?php echo $page_title; ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../painel.php">Painel</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gestão de Usuários</li>
            </ol>
        </nav>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <?php echo $editUser['usuarioId'] > 0 ? 'Editar Usuário' : 'Novo Usuário'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="usuarios.php">
                        <input type="hidden" name="usuarioId" value="<?php echo $editUser['usuarioId']; ?>">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?php echo htmlspecialchars($editUser['nome']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($editUser['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha *</label>
                            <input type="password" class="form-control" id="senha" name="senha" 
                                   <?php echo $editUser['usuarioId'] == 0 ? 'required' : ''; ?>>
                            <?php if ($editUser['usuarioId'] > 0): ?>
                                <div class="form-text">Deixe em branco para não alterar a senha.</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Papéis (Roles) *</label>
                            <select class="form-select" id="roleIds" name="roleIds[]" multiple required>
                                <?php foreach ($allRoles as $role): ?>
                                    <?php
                                    // Verifica se o papel atual está na lista de papéis do usuário
                                    $isSelected = in_array($role['roleId'], $userRoles);
                                    ?>
                                    <option value="<?php echo $role['roleId']; ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role['roleName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Selecione um ou mais papéis (Ctrl/Cmd + Clique).</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" 
                                   <?php echo $editUser['ativo'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ativo">Usuário Ativo</label>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Salvar Usuário
                        </button>
                        <?php if ($editUser['usuarioId'] > 0): ?>
                            <a href="usuarios.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar Edição
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Usuários Cadastrados</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Nome</th>
                                <th scope="col">E-mail</th>
                                <th scope="col">Papéis</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagination['data'])): ?>
                                <tr>
                                    <td colspan="5" class="text-center p-4">Nenhum usuário encontrado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($pagination['data'] as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <small class="text-muted"><?php echo htmlspecialchars($user['papeis'] ?? 'Nenhum'); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($user['ativo']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="usuarios.php?edit=<?php echo $user['usuarioId']; ?>" class="btn btn-sm btn-info" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <?php 
                                        // Proteção contra autoexclusão e exclusão do admin ID 1
                                        $isSelf = ($_SESSION['user_id'] == $user['usuarioId']);
                                        $isAdmin1 = ($user['usuarioId'] == 1);
                                        ?>
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
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    // Inicializa o Select2 para o campo de papéis
    $(document).ready(function() {
        $('#roleIds').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: "Selecione um ou mais papéis",
            allowClear: true
        });
    });
</script>
</body>
</html>