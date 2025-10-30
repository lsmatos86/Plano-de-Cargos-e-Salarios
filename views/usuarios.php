<?php
// Arquivo: views/usuarios.php (Gestão de Usuários)

require_once '../config.php';
require_once '../includes/functions.php';

// Redireciona para o login se o usuário não estiver autenticado
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Gestão de Usuários do Sistema';
$pdo = getDbConnection();
$message = '';
$message_type = '';
$table_name = 'usuarios';
$id_column = 'usuarioId';
$name_column = 'nome';

// ----------------------------------------------------
// 1. LÓGICA DE SALVAMENTO (INSERT/UPDATE)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $data = $_POST;
    $id = (int)($data[$id_column] ?? 0);
    $action = $data['action'];
    $nome = trim($data['nome'] ?? '');
    $email = trim($data['email'] ?? '');
    $senha = $data['senha'] ?? '';
    $ativo = isset($data['ativo']) ? 1 : 0;
    $senha_confirmacao = $data['senha_confirmacao'] ?? '';
    $redirect_url = 'usuarios.php';

    // Validação
    if (empty($nome) || empty($email)) {
        $message = "Nome e E-mail são obrigatórios.";
        $message_type = 'warning';
    } elseif (($action === 'insert' || !empty($senha)) && $senha !== $senha_confirmacao) {
        $message = "As senhas não coincidem.";
        $message_type = 'warning';
    } elseif (($action === 'insert' || !empty($senha)) && strlen($senha) < 5 && !empty($senha)) {
        $message = "A senha deve ter pelo menos 5 caracteres.";
        $message_type = 'warning';
    } else {
        try {
            $senha_hashed = null;
            if (!empty($senha)) {
                $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);
            }

            if ($action === 'insert') {
                // INSERT
                if (empty($senha_hashed)) throw new Exception("A senha é obrigatória para novos usuários.");
                $sql = "INSERT INTO {$table_name} (nome, email, senha, ativo) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $email, $senha_hashed, $ativo]);
                $message_action = 'cadastrado';

            } elseif ($action === 'update' && $id > 0) {
                // UPDATE
                $message_action = 'atualizado';
                
                if ($senha_hashed) {
                    // Atualiza TUDO, incluindo a senha
                    $sql = "UPDATE {$table_name} SET nome = ?, email = ?, senha = ?, ativo = ? WHERE {$id_column} = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nome, $email, $senha_hashed, $ativo, $id]);
                    $message_action .= " (e senha)";
                } else {
                    // Atualiza sem mexer na senha
                    $sql = "UPDATE {$table_name} SET nome = ?, email = ?, ativo = ? WHERE {$id_column} = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nome, $email, $ativo, $id]);
                }
            }
            
            $message = "Usuário foi {$message_action} com sucesso!";
            $message_type = 'success';

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $message = "Erro: O e-mail '{$email}' já está em uso (violação de chave única).";
            } else {
                $message = "Erro na transação: " . $e->getMessage();
            }
            $message_type = 'danger';
        } catch (Exception $e) {
            $message = "Erro: " . $e->getMessage();
            $message_type = 'danger';
        }
    }
    
    // Redireciona para exibir a mensagem e limpar o POST
    header("Location: {$redirect_url}?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 2. LÓGICA DE EXCLUSÃO (DELETE)
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Prevenção de auto-exclusão
    if ($id === (int)$_SESSION['user_id']) {
        $message = "Erro: Você não pode excluir seu próprio usuário enquanto estiver logado.";
        $message_type = 'danger';
    } elseif (deleteRecord($pdo, $table_name, $id_column, $id)) {
        $message = "Usuário ID {$id} excluído com sucesso!";
        $message_type = 'success';
    } else {
        $message = "Erro ao excluir: Usuário ID {$id} não encontrado.";
        $message_type = 'danger';
    }

    // Redireciona para limpar a URL
    header("Location: usuarios.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 3. LÓGICA DE LEITURA E FILTRO (READ All)
// ----------------------------------------------------
$params = [
    'term' => $_GET['term'] ?? '',
    'order_by' => $_GET['order_by'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC'
];

$registros = getRecords($pdo, $table_name, $id_column, $name_column, $params);

// Verifica e exibe mensagens após redirecionamento
if (isset($_GET['message'])) {
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cadastroModal" id="btnNovoCadastro">
                <i class="fas fa-user-plus"></i> Inserir Novo Usuário
            </button>
        </div>
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por Nome ou E-mail" value="<?php echo htmlspecialchars($params['term']); ?>">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                <?php if (!empty($params['term'])): ?>
                    <a href="usuarios.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <span class="fw-bold">Usuários Registrados: </span> <?php echo count($registros); ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th><a href="?order_by=<?php echo $id_column; ?>" class="text-decoration-none text-dark">ID</a></th>
                        <th><a href="?order_by=nome" class="text-decoration-none text-dark">Nome</a></th>
                        <th><a href="?order_by=email" class="text-decoration-none text-dark">E-mail (Login)</a></th>
                        <th><a href="?order_by=ativo" class="text-decoration-none text-dark">Status</a></th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['usuarioId']); ?></td>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['ativo'] ? 'success' : 'danger'; ?>">
                                        <?php echo $row['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info text-white btn-edit" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#cadastroModal"
                                        data-id="<?php echo htmlspecialchars($row['usuarioId']); ?>"
                                        data-nome="<?php echo htmlspecialchars($row['nome']); ?>"
                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                        data-ativo="<?php echo htmlspecialchars($row['ativo']); ?>"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <a href="usuarios.php?action=delete&id=<?php echo $row['usuarioId']; ?>" 
                                       class="btn btn-sm btn-danger <?php echo ($row['usuarioId'] === (int)$_SESSION['user_id']) ? 'disabled' : ''; ?>" 
                                       title="Excluir"
                                       onclick="return confirm('ATENÇÃO: Deseja realmente excluir o usuário <?php echo htmlspecialchars($row['nome']); ?>?');">
                                       <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhum usuário encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="cadastroModal" tabindex="-1" aria-labelledby="cadastroModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="formCadastro">
                <input type="hidden" name="action" id="modalAction" value="insert">
                <input type="hidden" name="usuarioId" id="modalId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="cadastroModalLabel">Cadastrar Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5 class="mb-3">Informações Básicas</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="inputNome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="inputNome" name="nome" required>
                        </div>
                        <div class="col-md-6">
                            <label for="inputEmail" class="form-label">E-mail (Login)</label>
                            <input type="email" class="form-control" id="inputEmail" name="email" required>
                            <small class="form-text text-muted" id="email-warning"></small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3">Redefinir Senha</h5>

                    <div class="alert alert-info small" id="senha-aviso" role="alert">
                        </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="inputSenha" class="form-label" id="labelSenha">Senha</label>
                            <input type="password" class="form-control" id="inputSenha" name="senha">
                        </div>
                        <div class="col-md-6">
                            <label for="inputSenhaConfirmacao" class="form-label" id="labelConfirmacao">Confirme a Senha</label>
                            <input type="password" class="form-control" id="inputSenhaConfirmacao" name="senha_confirmacao">
                        </div>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="inputAtivo" name="ativo" value="1" checked>
                        <label class="form-check-label" for="inputAtivo">Usuário Ativo (Pode fazer login)</label>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success" id="btnSalvar">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('cadastroModal');
    const modalTitle = document.getElementById('cadastroModalLabel');
    const modalAction = document.getElementById('modalAction');
    const modalId = document.getElementById('modalId');
    const inputNome = document.getElementById('inputNome');
    const inputEmail = document.getElementById('inputEmail');
    const inputSenha = document.getElementById('inputSenha');
    const inputSenhaConfirmacao = document.getElementById('inputSenhaConfirmacao');
    const inputAtivo = document.getElementById('inputAtivo');
    const senhaAviso = document.getElementById('senha-aviso');
    const btnSalvar = document.getElementById('btnSalvar');

    // Função para resetar o modal para Inserção
    const resetModal = () => {
        modalTitle.textContent = 'Cadastrar Novo Usuário';
        modalAction.value = 'insert';
        modalId.value = '';
        inputNome.value = ''; 
        inputEmail.value = '';
        inputSenha.value = '';
        inputSenhaConfirmacao.value = '';
        inputAtivo.checked = true;

        // Avisos e requerimentos para Inserção
        senhaAviso.innerHTML = 'A senha é **obrigatória** para o cadastro.';
        document.getElementById('labelSenha').classList.add('required');
        document.getElementById('labelConfirmacao').classList.add('required');
        
        btnSalvar.textContent = 'Salvar Cadastro';
        document.querySelector('.modal-header').classList.remove('bg-info');
        document.querySelector('.modal-header').classList.add('bg-primary');
    };

    // 1. Lógica para abrir o modal no modo INSERIR
    document.getElementById('btnNovoCadastro').addEventListener('click', resetModal);

    // 2. Lógica para abrir o modal no modo EDITAR
    modalElement.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (button && button.classList.contains('btn-edit')) {
            const id = button.getAttribute('data-id');
            const nome = button.getAttribute('data-nome');
            const email = button.getAttribute('data-email');
            const ativo = button.getAttribute('data-ativo') === '1';

            // Preenche os campos para Edição
            modalTitle.textContent = 'Editar Usuário (ID: ' + id + ')';
            modalAction.value = 'update';
            modalId.value = id;
            inputNome.value = nome;
            inputEmail.value = email;
            inputAtivo.checked = ativo;
            inputSenha.value = ''; 
            inputSenhaConfirmacao.value = '';

            // Avisos e requerimentos para Edição
            senhaAviso.innerHTML = 'Deixe os campos de senha vazios para **manter a senha atual**. Preencha apenas para redefinir.';
            document.getElementById('labelSenha').classList.remove('required');
            document.getElementById('labelConfirmacao').classList.remove('required');

            btnSalvar.textContent = 'Atualizar';
            document.querySelector('.modal-header').classList.remove('bg-primary');
            document.querySelector('.modal-header').classList.add('bg-info');
        } else {
            // Se o modal for aberto sem ser pelo botão de editar, reseta
            resetModal();
        }
    });

    // Lógica para limpar o campo de senha ao fechar o modal
    modalElement.addEventListener('hidden.bs.modal', function () {
        inputSenha.value = '';
        inputSenhaConfirmacao.value = '';
    });
});
</script>
</body>
</html>