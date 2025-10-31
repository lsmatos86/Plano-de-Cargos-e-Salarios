<?php
// Arquivo: views/areas_atuacao.php (REFATORADO)

// 1. Inclusão de arquivos
require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php'; // (Ainda necessário para isUserLoggedIn)

// 2. Importa o novo Repositório
use App\Repository\AreaRepository;

// Redireciona para o login se o usuário não estiver autenticado
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Configurações
$page_title = 'Gerenciamento de Áreas de Atuação';
$id_column = 'areaId';
$name_column = 'areaNome';
$message = '';
$message_type = '';
$error_data = []; // Armazena dados do POST em caso de erro

// Instancia o Repositório
$repo = new AreaRepository();

// ----------------------------------------------------
// LÓGICA DE CRUD (CREATE/UPDATE/DELETE) - REFATORADO
// ----------------------------------------------------
try {
    // 1. Lógica de CREATE/UPDATE (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['action'])) {
        $nome = trim($_POST[$name_column] ?? '');
        $id = (int)($_POST[$id_column] ?? 0);
        
        $repo->save($_POST); // O repositório lida com insert/update e validação
        
        $action_desc = ($id > 0) ? 'atualizada' : 'cadastrada';
        $message = "Área '{$nome}' {$action_desc} com sucesso!";
        $message_type = 'success';
    }

    // 2. Lógica de DELETE (GET)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $deleted = $repo->delete($id);
        
        if ($deleted) {
            $message = "Área ID {$id} excluída com sucesso! Áreas filhas (se existiam) foram movidas para o Nível Raiz.";
            $message_type = 'success';
        } else {
            $message = "Erro: Área ID {$id} não encontrada ou já excluída.";
            $message_type = 'danger';
        }
        
        // Redireciona para limpar a URL após a ação
        header("Location: areas_atuacao.php?message=" . urlencode($message) . "&type={$message_type}");
        exit;
    }

} catch (Exception $e) {
    // Captura qualquer exceção do Repositório (validação, FK, DB)
    $message = $e->getMessage();
    $message_type = 'danger';
    $error_data = $_POST; // Salva dados para repopular o formulário
}

// Mensagens vindas de um redirecionamento
if (empty($message) && isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// ----------------------------------------------------
// LÓGICA DE LEITURA (READ) - REFATORADO
// ----------------------------------------------------
// Carrega a árvore hierárquica
$areaTree = $repo->findAllHierarchical();

// Carrega o lookup para o <select> do formulário
// (Exclui a área atual da lista de pais em caso de edição)
$id_para_editar = (int)($_GET['edit_id'] ?? $error_data[$id_column] ?? 0);
$areasLookup = $repo->getHierarchyLookup();
if ($id_para_editar > 0 && isset($areasLookup[$id_para_editar])) {
    // Remove a própria área da lista de pais disponíveis
    unset($areasLookup[$id_para_editar]);
}

// Dados para preencher o formulário (em caso de edição ou erro)
$formData = [];
if (!empty($error_data)) {
    $formData = $error_data; // Repopula com dados do POST que falhou
} elseif (isset($_GET['edit_id']) && $id_para_editar > 0) {
    // Busca dados para edição
    try {
        $stmt = Database::getConnection()->prepare("SELECT * FROM areas_atuacao WHERE areaId = ?");
        $stmt->execute([$id_para_editar]);
        $formData = $stmt->fetch() ?: [];
    } catch (Exception $e) {
        $message = "Erro ao carregar dados para edição: " . $e->getMessage();
        $message_type = 'danger';
    }
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
        .short-text { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .table-hierarquia .area-level-1 { font-weight: bold; }
        .table-hierarquia .area-level-2 { padding-left: 2rem; }
        .table-hierarquia .area-level-3 { padding-left: 4rem; font-style: italic; }
        .table-hierarquia .area-level-4 { padding-left: 6rem; font-style: italic; color: #555; }
        .table-hierarquia .area-level-default { padding-left: 8rem; font-size: 0.9em; }
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

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header <?php echo ($id_para_editar > 0) ? 'bg-info text-white' : 'bg-primary text-white'; ?>">
                    <h5 class="mb-0" id="formTitle">
                        <?php if ($id_para_editar > 0): ?>
                            <i class="fas fa-edit"></i> Editar Área (ID: <?php echo $id_para_editar; ?>)
                        <?php else: ?>
                            <i class="fas fa-plus"></i> Nova Área
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="areas_atuacao.php" id="cadastroForm">
                        <input type="hidden" name="<?php echo $id_column; ?>" id="formId" value="<?php echo htmlspecialchars($formData[$id_column] ?? ''); ?>">
                        
                        <div class="mb-3">
                            <label for="formNome" class="form-label">Nome da Área *</label>
                            <input type="text" class="form-control" id="formNome" name="<?php echo $name_column; ?>" 
                                   value="<?php echo htmlspecialchars($formData[$name_column] ?? ''); ?>" required maxlength="150">
                        </div>

                        <div class="mb-3">
                            <label for="formPai" class="form-label">Hierarquia (Área Pai)</label>
                            <select class="form-select" id="formPai" name="areaPaiId">
                                <option value="">--- Nível Raiz (Nenhuma) ---</option>
                                <?php 
                                $selectedPaiId = $formData['areaPaiId'] ?? null;
                                foreach ($areasLookup as $id => $nomeHierarquico): 
                                    // Não exibe a própria área como opção de pai
                                    if ($id == $id_para_editar) continue; 
                                ?>
                                    <option value="<?php echo $id; ?>" <?php echo ($id == $selectedPaiId) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($nomeHierarquico); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formCodigo" class="form-label">Código (Opcional)</label>
                            <input type="text" class="form-control" id="formCodigo" name="areaCodigo" 
                                   value="<?php echo htmlspecialchars($formData['areaCodigo'] ?? ''); ?>" maxlength="50">
                        </div>

                        <div class="mb-3">
                            <label for="formDescricao" class="form-label">Descrição (Opcional)</label>
                            <textarea class="form-control" id="formDescricao" name="areaDescricao" rows="3"><?php echo htmlspecialchars($formData['areaDescricao'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn <?php echo ($id_para_editar > 0) ? 'btn-info' : 'btn-primary'; ?> w-100" id="btnSalvar">
                            <i class="fas fa-check"></i> <?php echo ($id_para_editar > 0) ? 'Atualizar' : 'Salvar'; ?>
                        </button>
                        
                        <?php if ($id_para_editar > 0 || !empty($error_data)): ?>
                        <a href="areas_atuacao.php" class="btn btn-outline-secondary w-100 mt-2">
                            Cancelar Edição
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <span class="fw-bold">Estrutura de Áreas</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 table-hierarquia">
                            <thead class="bg-light">
                                <tr>
                                    <th>Nome da Área</th>
                                    <th>Código</th>
                                    <th>Descrição</th>
                                    <th width="120px" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Função recursiva para renderizar as linhas da tabela
                                function renderAreaRow($area, $level = 1) {
                                    $levelClass = 'area-level-' . min($level, 4);
                                    if ($level > 4) $levelClass = 'area-level-default';
                                    
                                    $icon = ($level == 1) ? '<i class="fas fa-building fa-fw me-2 text-primary"></i>' : '<i class="fas fa-level-up-alt fa-fw me-2 text-muted" style="transform: rotate(90deg);"></i>';
                                    
                                    echo '<tr>';
                                    echo '<td class="align-middle ' . $levelClass . '">' . $icon . htmlspecialchars($area['areaNome']) . '</td>';
                                    echo '<td class="align-middle">' . htmlspecialchars($area['areaCodigo'] ?? '') . '</td>';
                                    echo '<td class="align-middle short-text" title="' . htmlspecialchars($area['areaDescricao'] ?? '') . '">' . htmlspecialchars($area['areaDescricao'] ?? '') . '</td>';
                                    echo '<td class="text-center align-middle">';
                                    
                                    // Botão Editar
                                    echo '<a href="areas_atuacao.php?edit_id=' . $area['areaId'] . '" class="btn btn-sm btn-info text-white" title="Editar"><i class="fas fa-edit"></i></a> ';
                                    
                                    // Botão Excluir
                                    echo '<a href="areas_atuacao.php?action=delete&id=' . $area['areaId'] . '" 
                                           class="btn btn-sm btn-danger" 
                                           title="Excluir"
                                           onclick="return confirm(\'ATENÇÃO: A exclusão desta área irá remover seus vínculos com os cargos e tornará áreas filhas em Nível Raiz. Deseja realmente excluir?\');">
                                           <i class="fas fa-trash-alt"></i>
                                         </a>';
                                    echo '</td>';
                                    echo '</tr>';

                                    // Renderiza filhos
                                    if (!empty($area['children'])) {
                                        foreach ($area['children'] as $child) {
                                            renderAreaRow($child, $level + 1);
                                        }
                                    }
                                }

                                if (count($areaTree) > 0):
                                    foreach ($areaTree as $areaRaiz) {
                                        renderAreaRow($areaRaiz, 1);
                                    }
                                else:
                                    echo '<tr><td colspan="4" class="text-center">Nenhuma Área de Atuação cadastrada.</td></tr>';
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    <?php if (!empty($error_data) && $message_type === 'danger'): ?>
    // Se houve um erro no POST, o formulário já foi repopulado pelo PHP.
    // Apenas garantimos que o select "Área Pai" também seja preenchido.
    document.addEventListener('DOMContentLoaded', function() {
        const selectedPaiId = <?php echo json_encode($error_data['areaPaiId'] ?? null); ?>;
        if (selectedPaiId) {
            document.getElementById('formPai').value = selectedPaiId;
        }
    });
    <?php endif; ?>
</script>

</body>
</html>