<?php
// Arquivo: views/cbos.php

// Inclusão de arquivos na pasta superior (../)
require_once '../config.php';
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Configurações específicas desta tabela
$table_name = 'cbos';
$id_column = 'cboId'; // PK - Agora armazena o código XXXX-YY
$name_column = 'cboNome'; // Nome da Ocupação (Curto)
$title_column = 'cboTituloOficial'; // NOVO: Título Completo do Ministério do Trabalho
$fk_column = 'familiaCboId';
$page_title = 'Gestão de Códigos CBO';

$pdo = getDbConnection();
$message = '';
$message_type = '';

// 1. CARREGAR DADOS FK: Famílias CBO para o SELECT
$familias_cbo = getLookupData($pdo, 'familia_cbo', 'familiaCboId', 'familiaCboNome');


// ----------------------------------------------------
// 2. LÓGICA DE CRUD (CREATE/UPDATE/DELETE)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $nome = trim($_POST[$name_column] ?? ''); // Nome Curto
    $tituloOficial = trim($_POST[$title_column] ?? ''); // Título Oficial
    $id = trim($_POST[$id_column] ?? 0); // Código CBO (XXXX-YY)
    $familiaId = (int)($_POST[$fk_column] ?? 0);
    $action = $_POST['action'];
    
    // NOVO: Função de validação de formato XXXX-YY
    $is_valid_format = (bool)preg_match('/^\d{4}-\d{2}$/', $id);
    
    // Validação
    if (empty($nome) || empty($tituloOficial) || empty($id) || $familiaId === 0) {
        $message = "Todos os campos (Código CBO, Nome, Título Oficial e Família) são obrigatórios.";
        $message_type = 'warning';
    } elseif (!$is_valid_format) {
        $message = "O Código CBO deve estar no formato XXXX-YY (Ex: 6201-10).";
        $message_type = 'warning';
    } else {
        try {
            if ($action === 'insert') {
                // Lógica de Inserção
                // Ajustada para incluir o novo campo cboTituloOficial
                $stmt = $pdo->prepare("INSERT INTO {$table_name} ({$id_column}, {$name_column}, {$title_column}, {$fk_column}) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $nome, $tituloOficial, $familiaId]);
                
                $message = "CBO '{$nome}' cadastrado com sucesso!";
                $message_type = 'success';
                
            } elseif ($action === 'update') {
                // Lógica de Atualização. A PK (cboId) não deve ser alterada.
                // Ajustada para incluir o novo campo cboTituloOficial
                $stmt = $pdo->prepare("UPDATE {$table_name} SET {$name_column} = ?, {$title_column} = ?, {$fk_column} = ? WHERE {$id_column} = ?");
                $stmt->execute([$nome, $tituloOficial, $familiaId, $id]);

                $message = "CBO Código {$id} atualizado com sucesso!";
                $message_type = 'success';
            }
        } catch (Exception $e) {
            // Pode falhar por UNIQUE KEY (CBO ID já existe) ou FOREIGN KEY (Família ID não existe)
            $message = "Erro ao processar a ação: O código CBO já existe ou a Família é inválida. Erro: " . $e->getMessage();
            $message_type = 'danger';
        }
    }
    
    // Se houve erro de validação/SQL, mantém o POST e preenche os campos do modal na próxima abertura
    if ($message_type === 'warning' || $message_type === 'danger') {
        // Redireciona com dados de erro para serem exibidos
        header("Location: cbos.php?message=" . urlencode($message) . "&type={$message_type}&error_id=" . urlencode($id) . "&error_nome=" . urlencode($nome) . "&error_titulo=" . urlencode($tituloOficial) . "&error_familia=" . urlencode($familiaId));
        exit;
    }
    
    // Redireciona em caso de sucesso
    header("Location: cbos.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 3. LÓGICA DE EXCLUSÃO (DELETE - Via GET)
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = trim($_GET['id']);
    
    if (deleteRecord($pdo, $table_name, $id_column, $id)) {
        $message = "Registro CBO {$id} excluído com sucesso!";
        $message_type = 'success';
    } else {
        $message = "Erro ao excluir: O CBO está vinculado a um Cargo.";
        $message_type = 'danger';
    }

    header("Location: cbos.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 4. LÓGICA DE LEITURA E FILTRO (READ All)
// ----------------------------------------------------
$params = [
    'term' => $_GET['term'] ?? '',
    'order_by' => $_GET['order_by'] ?? $id_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC'
];

// Query mais complexa para juntar com o nome da Família CBO e o novo campo
$sql = "
    SELECT 
        t.*, 
        f.familiaCboNome 
    FROM {$table_name} t
    JOIN familia_cbo f ON f.familiaCboId = t.familiaCboId
";
$bindings = [];

if (!empty($params['term'])) {
    // Filtra pelo código, nome curto ou nome oficial
    $sql .= " WHERE t.{$name_column} LIKE ? OR t.{$id_column} LIKE ? OR t.{$title_column} LIKE ?";
    $bindings[] = "%{$params['term']}%";
    $bindings[] = "%{$params['term']}%";
    $bindings[] = "%{$params['term']}%";
}

$validColumns = [$id_column, $name_column, $title_column, 'familiaCboNome', $id_column.'DataCadastro', $id_column.'DataAtualizacao'];
$orderBy = in_array($params['order_by'], $validColumns) ? $params['order_by'] : $id_column;
$sortDir = in_array(strtoupper($params['sort_dir']), ['ASC', 'DESC']) ? $params['sort_dir'] : 'ASC';

$sql .= " ORDER BY {$orderBy} {$sortDir}";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindings);
    $registros = $stmt->fetchAll();
} catch (\PDOException $e) {
    $registros = [];
    $message = "Erro ao carregar dados: " . $e->getMessage();
    $message_type = 'danger';
}

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// Verifica se houve dados de erro de submissão (para preencher o modal)
$error_data = [
    'id' => $_GET['error_id'] ?? '',
    'nome' => $_GET['error_nome'] ?? '',
    'titulo' => $_GET['error_titulo'] ?? '',
    'familia' => $_GET['error_familia'] ?? ''
];

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
                <i class="fas fa-plus"></i> Inserir Novo CBO
            </button>
        </div>
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="search" name="term" class="form-control me-2" placeholder="Filtrar por Nome, Título ou Código CBO" value="<?php echo htmlspecialchars($params['term']); ?>">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                <?php if (!empty($params['term'])): ?>
                    <a href="cbos.php" class="btn btn-outline-danger ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <span class="fw-bold">Registros Encontrados: </span> <?php echo count($registros); ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width:10%;">
                            <a href="?order_by=<?php echo $id_column; ?>&sort_dir=<?php echo getSortDirection($params['order_by'], $id_column); ?>&term=<?php echo urlencode($params['term']); ?>" class="text-decoration-none text-dark">
                                Código CBO
                                <?php if ($params['order_by'] === $id_column): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                            </a>
                        </th>
                        <th style="width:25%;">
                            <a href="?order_by=<?php echo $name_column; ?>&sort_dir=<?php echo getSortDirection($params['order_by'], $name_column); ?>&term=<?php echo urlencode($params['term']); ?>" class="text-decoration-none text-dark">
                                Nome Curto
                                <?php if ($params['order_by'] === $name_column): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                            </a>
                        </th>
                         <th style="width:35%;">
                            <a href="?order_by=<?php echo $title_column; ?>&sort_dir=<?php echo getSortDirection($params['order_by'], $title_column); ?>&term=<?php echo urlencode($params['term']); ?>" class="text-decoration-none text-dark">
                                Título Oficial (MTE)
                                <?php if ($params['order_by'] === $title_column): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                            </a>
                        </th>
                         <th style="width:20%;">
                            <a href="?order_by=familiaCboNome&sort_dir=<?php echo getSortDirection($params['order_by'], 'familiaCboNome'); ?>&term=<?php echo urlencode($params['term']); ?>" class="text-decoration-none text-dark">
                                Família CBO
                                <?php if ($params['order_by'] === 'familiaCboNome'): ?><i class="fas fa-sort-<?php echo strtolower($params['sort_dir']); ?>"></i><?php endif; ?>
                            </a>
                        </th>
                        <th style="width:10%;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row[$id_column]); ?></td>
                                <td><?php echo htmlspecialchars($row[$name_column]); ?></td>
                                <td><?php echo htmlspecialchars($row[$title_column] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['familiaCboNome']); ?></td>
                                <td>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info text-white btn-edit" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#cadastroModal"
                                        data-id="<?php echo htmlspecialchars($row[$id_column]); ?>"
                                        data-nome="<?php echo htmlspecialchars($row[$name_column]); ?>"
                                        data-titulo="<?php echo htmlspecialchars($row[$title_column] ?? ''); ?>"
                                        data-familiaid="<?php echo htmlspecialchars($row[$fk_column]); ?>"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <a href="cbos.php?action=delete&id=<?php echo htmlspecialchars($row[$id_column]); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Excluir"
                                       onclick="return confirm('ATENÇÃO: Este CBO está vinculado a Cargos. Deseja realmente excluir?');">
                                       <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhum registro encontrado. <?php if($params['term']) echo "(Filtro: " . htmlspecialchars($params['term']) . ")"; ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="cadastroModal" tabindex="-1" aria-labelledby="cadastroModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formCadastro">
                <input type="hidden" name="action" id="modalAction" value="insert">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="cadastroModalLabel">Cadastrar Novo CBO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputId" class="form-label">Código CBO (XXXX-YY)</label>
                        <input type="text" class="form-control" id="inputId" name="<?php echo $id_column; ?>" placeholder="Ex: 6201-10" pattern="\d{4}-\d{2}" title="Formato exigido: XXXX-YY" required>
                        <small class="form-text text-muted">Este é o código único no formato XXXX-YY e não pode ser alterado após o cadastro.</small>
                    </div>
                    <div class="mb-3">
                        <label for="inputNome" class="form-label">Nome Curto da Ocupação</label>
                        <input type="text" class="form-control" id="inputNome" name="<?php echo $name_column; ?>" placeholder="Ex: Coordenador de Colheita" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputTituloOficial" class="form-label">Título Oficial (MTE)</label>
                        <input type="text" class="form-control" id="inputTituloOficial" name="<?php echo $title_column; ?>" placeholder="Ex: Coordenador(a) de equipe de colheita e transporte" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputFamiliaId" class="form-label">Família CBO</label>
                        <select class="form-select" id="inputFamiliaId" name="<?php echo $fk_column; ?>" required data-placeholder="Selecione a Família CBO...">
                             <option value=""></option>
                            <?php foreach ($familias_cbo as $id => $nome): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>">
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
    const modalElement = document.getElementById('cadastroModal');
    const modalTitle = document.getElementById('cadastroModalLabel');
    const modalAction = document.getElementById('modalAction');
    const inputId = document.getElementById('inputId');
    const inputNome = document.getElementById('inputNome');
    const inputTituloOficial = document.getElementById('inputTituloOficial');
    const inputFamiliaId = document.getElementById('inputFamiliaId');
    const btnSalvar = document.getElementById('btnSalvar');
    
    // Dados de erro de submissão do PHP (se houver)
    const errorData = {
        id: "<?php echo $error_data['id']; ?>",
        nome: "<?php echo $error_data['nome']; ?>",
        titulo: "<?php echo $error_data['titulo']; ?>",
        familia: "<?php echo $error_data['familia']; ?>"
    };

    // Inicializa Select2 para o campo Família CBO
    $('#inputFamiliaId').select2({
        theme: "bootstrap-5",
        width: '100%',
        placeholder: "Selecione a Família CBO...",
        dropdownParent: $('#cadastroModal'),
        allowClear: true
    });

    // Função para resetar o modal para Inserção
    const resetModal = () => {
        modalTitle.textContent = 'Cadastrar Novo CBO';
        modalAction.value = 'insert';
        inputId.value = '';
        inputNome.value = '';
        inputTituloOficial.value = '';
        inputId.readOnly = false; // Permite edição do ID na inserção
        $('#inputFamiliaId').val(null).trigger('change'); // Reseta Select2
        btnSalvar.textContent = 'Salvar Cadastro';
        document.querySelector('.modal-header').classList.remove('bg-info', 'bg-danger');
        document.querySelector('.modal-header').classList.add('bg-primary');
    };
    
    // Função para preencher o modal em modo Edição
    const setupEditModal = (button) => {
        const id = button.getAttribute('data-id');
        const nome = button.getAttribute('data-nome');
        const titulo = button.getAttribute('data-titulo');
        const familiaid = button.getAttribute('data-familiaid');
        
        modalTitle.textContent = 'Editar CBO (Código: ' + id + ')';
        modalAction.value = 'update';
        inputId.value = id;
        inputNome.value = nome;
        inputTituloOficial.value = titulo;
        inputId.readOnly = true; // Bloqueia edição do ID (PK)
        btnSalvar.textContent = 'Atualizar';

        $('#inputFamiliaId').val(familiaid).trigger('change');

        document.querySelector('.modal-header').classList.remove('bg-primary', 'bg-danger');
        document.querySelector('.modal-header').classList.add('bg-info');
    };

    // 1. Lógica para abrir o modal no modo INSERIR
    document.getElementById('btnNovoCadastro').addEventListener('click', resetModal);

    // 2. Lógica para abrir o modal no modo EDITAR OU ERRO DE SUBMISSÃO
    modalElement.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        // Verifica se é o botão de edição
        if (button && button.classList.contains('btn-edit')) {
            setupEditModal(button);
        } else if (errorData.id || errorData.nome || errorData.titulo || errorData.familia) {
            // Modo Erro de Submissão (reabre o modal com dados pré-preenchidos)
            modalTitle.textContent = 'Erro de Submissão: ' + (errorData.id ? 'CBO ' + errorData.id : 'Novo Cadastro');
            modalAction.value = 'insert'; // Assume inserção
            
            // Preenche com os dados que causaram o erro
            inputId.value = errorData.id;
            inputNome.value = errorData.nome;
            inputTituloOficial.value = errorData.titulo;
            
            // Tenta preencher a família
            if (errorData.familia) {
                $('#inputFamiliaId').val(errorData.familia).trigger('change');
            } else {
                $('#inputFamiliaId').val(null).trigger('change');
            }
            
            inputId.readOnly = false;
            btnSalvar.textContent = 'Corrigir e Salvar';
            
            document.querySelector('.modal-header').classList.remove('bg-primary', 'bg-info');
            document.querySelector('.modal-header').classList.add('bg-danger');

        } else {
            // Caso padrão (abertura via Novo CBO)
            resetModal();
        }
    });

    // 3. Remove os dados de erro da URL após carregar o modal
    if (errorData.id || errorData.nome || errorData.titulo || errorData.familia) {
        // Usa timeout pequeno para garantir que o modal seja processado antes de tentar abrir
        setTimeout(() => {
            if (!$(modalElement).hasClass('show')) {
                 $(modalElement).modal('show'); 
            }
            
            // Limpa os parâmetros de erro da URL
            const url = new URL(window.location.href);
            url.searchParams.delete('error_id');
            url.searchParams.delete('error_nome');
            url.searchParams.delete('error_titulo');
            url.searchParams.delete('error_familia');
            window.history.replaceState({}, document.title, url.toString());
        }, 100);
    }
});
</script>
</body>
</html>