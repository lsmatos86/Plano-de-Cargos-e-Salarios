<?php
// Arquivo: views/roles.php (Gestão de Papéis e Permissões)

// 1. INCLUDES E INICIALIZAÇÃO
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // Inclui o $authService

// 2. IMPORTAÇÕES
use App\Repository\RoleRepository;

// 3. SEGURANÇA: Verifica o login e a permissão
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
// $authService foi instanciado em functions.php
// Apenas quem pode gerenciar usuários pode ver esta página
$authService->checkAndFail('usuarios:manage', '../painel.php');

// 4. INICIALIZAÇÃO DE VARIÁVEIS
$page_title = "Gestão de Papéis e Permissões";
$message = $_GET['message'] ?? '';
$message_type = $_GET['type'] ?? 'info';
$repo = new RoleRepository();

// Variáveis para o formulário
$editRole = [
    'roleId' => 0,
    'roleName' => '',
    'roleDescription' => ''
];
$rolePermissions = []; // Permissões que este papel JÁ POSSUI

// 5. PROCESSAMENTO DE AÇÕES (POST/GET)
try {
    // AÇÃO: SALVAR (CREATE ou UPDATE)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['roleName'])) {
        $roleId = $repo->save($_POST);
        header("Location: roles.php?message=Papel salvo com sucesso. ID: $roleId&type=success");
        exit;
    }

    // AÇÃO: EDITAR (Carregar dados para o formulário)
    if (isset($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        $editRole = $repo->find($id);
        if (!$editRole) {
            throw new Exception("Papel não encontrado.");
        }
        // Busca os IDs das permissões que este papel já tem
        $rolePermissions = $repo->getPermissionIdsForRole($id);
        $page_title = "Editando Papel: " . htmlspecialchars($editRole['roleName']);
    }
    
    // AÇÃO: DELETAR
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $repo->delete($id);
        header("Location: roles.php?message=Papel excluído com sucesso.&type=success");
        exit;
    }

} catch (Exception $e) {
    $message = $e->getMessage();
    $message_type = 'danger';
}

// 6. BUSCAR DADOS PARA EXIBIÇÃO
$allRoles = $repo->findAll(); // Lista de papéis para a tabela
$allPermissions = $repo->getAllPermissions(); // Lista de permissões para o formulário

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
        .permission-group {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; // Inclui o menu de navegação ?>

<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0"><?php echo $page_title; ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../painel.php">Painel</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gestão de Papéis</li>
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
                        <?php echo $editRole['roleId'] > 0 ? 'Editar Papel' : 'Novo Papel'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="roles.php">
                        <input type="hidden" name="roleId" value="<?php echo $editRole['roleId']; ?>">
                        
                        <div class="mb-3">
                            <label for="roleName" class="form-label">Nome do Papel *</label>
                            <input type="text" class="form-control" id="roleName" name="roleName" 
                                   value="<?php echo htmlspecialchars($editRole['roleName']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="roleDescription" class="form-label">Descrição</label>
                            <textarea class="form-control" id="roleDescription" name="roleDescription" rows="2"><?php echo htmlspecialchars($editRole['roleDescription']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Permissões *</label>
                            <div class="permission-group">
                                <?php foreach ($allPermissions as $perm): ?>
                                    <?php
                                    // Verifica se a permissão atual está na lista de permissões do papel
                                    $isChecked = in_array($perm['permissionId'], $rolePermissions);
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissionIds[]" 
                                               value="<?php echo $perm['permissionId']; ?>" 
                                               id="perm_<?php echo $perm['permissionId']; ?>"
                                               <?php echo $isChecked ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="perm_<?php echo $perm['permissionId']; ?>">
                                            <strong><?php echo htmlspecialchars($perm['permissionName']); ?></strong>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($perm['permissionDescription']); ?></small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Salvar Papel
                        </button>
                        <?php if ($editRole['roleId'] > 0): ?>
                            <a href="roles.php" class="btn btn-secondary">
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
                    <h5 class="mb-0">Papéis Cadastrados</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nome</th>
                                <th scope="col">Descrição</th>
                                <th scope="col" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allRoles)): ?>
                                <tr>
                                    <td colspan="4" class="text-center p-4">Nenhum papel cadastrado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($allRoles as $role): ?>
                                <tr>
                                    <td><?php echo $role['roleId']; ?></td>
                                    <td><?php echo htmlspecialchars($role['roleName']); ?></td>
                                    <td><?php echo htmlspecialchars($role['roleDescription']); ?></td>
                                    <td class="text-center">
                                        <a href="roles.php?edit=<?php echo $role['roleId']; ?>" class="btn btn-sm btn-info" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <?php if ($role['roleId'] != 1): // Não deixa excluir o Admin ?>
                                            <a href="roles.php?delete=<?php echo $role['roleId']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Excluir"
                                               onclick="return confirm('Atenção: Excluir um papel irá removê-lo de todos os usuários. Deseja continuar?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Não é possível excluir o Administrador">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>