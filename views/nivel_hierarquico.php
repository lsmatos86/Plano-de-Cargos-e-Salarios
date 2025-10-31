<?php
// Arquivo: views/nivel_hierarquico.php (Gerenciamento de Níveis Hierárquicos)

require_once '../config.php';
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Gerenciamento de Níveis Hierárquicos';
$pdo = getDbConnection();
$message = '';
$message_type = '';
$table_name = 'nivel_hierarquico';
$id_column = 'nivelId';

// Variáveis de Coluna
$order_column = 'nivelOrdem';
$name_column = 'nivelDescricao'; // Nome descritivo (ex: Junior, Pleno)
$fk_column = 'tipoId';           // Chave Estrangeira para tipo_hierarquia
$date_update_column = 'nivelDataAtualizacao';
// NOVOS CAMPOS ADICIONADOS
$attributions_column = 'nivelAtribuicoes'; 
$autonomy_column = 'nivelAutonomia';     
$creation_date_column = 'nivelDataCadastro'; 
$when_to_use_column = 'nivelQuandoUtilizar'; // NOVO CAMPO

// Carrega os Tipos de Hierarquia para o SELECT do formulário
// NOTE: TipoId e TipoNome são os campos usados na tabela 'tipo_hierarquia'
$tiposHierarquia = getLookupData($pdo, 'tipo_hierarquia', 'tipoId', 'tipoNome'); 

// ----------------------------------------------------
// 1. LÓGICA DE CADASTRO/EDIÇÃO (CREATE/UPDATE)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST[$id_column] ?? 0);
    $ordem = (int)($_POST[$order_column] ?? 0);
    $descricao = trim($_POST[$name_column] ?? '');
    $tipoId = (int)($_POST[$fk_column] ?? 0);
    // CAPTURA DOS NOVOS CAMPOS
    $atribuicoes = trim($_POST[$attributions_column] ?? null);
    $autonomia = trim($_POST[$autonomy_column] ?? null);
    $quandoUtilizar = trim($_POST[$when_to_use_column] ?? null); // CAPTURA DO NOVO CAMPO

    if ($ordem <= 0 || empty($descricao) || $tipoId <= 0) {
        $message = "Os campos Ordem (Nível), Descrição e Tipo de Hierarquia são obrigatórios.";
        $message_type = 'danger';
    } else {
        try {
            if ($id > 0) {
                // UPDATE - Inclui todos os campos
                $sql = "UPDATE {$table_name} SET {$order_column} = ?, {$name_column} = ?, {$fk_column} = ?, {$attributions_column} = ?, {$autonomy_column} = ?, {$when_to_use_column} = ?, {$date_update_column} = CURRENT_TIMESTAMP() WHERE {$id_column} = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$ordem, $descricao, $tipoId, $atribuicoes, $autonomia, $quandoUtilizar, $id]);
                $message = "Nível Hierárquico atualizado com sucesso!";
            } else {
                // CREATE - Inclui todos os campos
                $sql = "INSERT INTO {$table_name} ({$order_column}, {$name_column}, {$fk_column}, {$attributions_column}, {$autonomy_column}, {$when_to_use_column}, {$creation_date_column}) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$ordem, $descricao, $tipoId, $atribuicoes, $autonomia, $quandoUtilizar]);
                $message = "Novo Nível Hierárquico cadastrado com sucesso!";
            }
            $message_type = 'success';
        } catch (PDOException $e) {
            $error_info = $e->errorInfo[1] ?? 0;
            
            // MENSAGEM DE ERRO AJUSTADA (Para refletir a possível causa e o feedback do usuário)
            if ($error_info == 1062) { 
                $message = "Erro ao salvar: Existe uma violação de unicidade. Verifique se o Nome/Descrição já existe, ou se a combinação Ordem-Tipo está duplicada.";
            } else {
                $message = "Erro ao salvar: " . $e->getMessage();
            }
            $message_type = 'danger';
        }
    }
    // Redireciona para evitar re-submissão do formulário
    header("Location: nivel_hierarquico.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 2. LÓGICA DE EXCLUSÃO (DELETE)
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Utiliza a função de exclusão segura
    $deleted = deleteRecord($pdo, $table_name, $id_column, $id);
    
    if ($deleted) {
        $message = "Nível Hierárquico ID {$id} excluído com sucesso!";
        $message_type = 'success';
    } else {
        // Geralmente falha por FK, pois cargos depende desta tabela
        $message = "Erro ao excluir: O registro ID {$id} não foi encontrado ou está sendo usado por um Cargo.";
        $message_type = 'danger';
    }

    header("Location: nivel_hierarquico.php?message=" . urlencode($message) . "&type={$message_type}");
    exit;
}

// ----------------------------------------------------
// 3. LÓGICA DE LEITURA E FILTRO (READ All)
// ----------------------------------------------------
$params = [
    'term' => $_GET['term'] ?? '',
    'order_by' => $_GET['order_by'] ?? $order_column,
    'sort_dir' => $_GET['sort_dir'] ?? 'ASC'
];

// Query com JOIN e NOVOS CAMPOS
$sql = "
    SELECT 
        nh.nivelId, nh.nivelOrdem, nh.nivelDescricao, nh.nivelDataCadastro, nh.nivelDataAtualizacao,
        nh.{$attributions_column}, nh.{$autonomy_column}, nh.{$when_to_use_column}, 
        th.tipoNome
    FROM {$table_name} nh
    LEFT JOIN tipo_hierarquia th ON th.tipoId = nh.tipoId
";
$bindings = [];

if (!empty($params['term'])) {
    $sql .= " WHERE nh.nivelDescricao LIKE ? OR th.tipoNome LIKE ?";
    $bindings[] = "%{$params['term']}%";
    $bindings[] = "%{$params['term']}%";
}

$validColumns = ['nivelId', 'nivelOrdem', 'nivelDescricao', 'tipoNome', 'nivelDataAtualizacao', 'nivelDataCadastro'];
$orderBy = in_array($params['order_by'], $validColumns) ? $params['order_by'] : $order_column;
$sortDir = in_array(strtoupper($params['sort_dir']), ['ASC', 'DESC']) ? $params['sort_dir'] : 'ASC';

$sql .= " ORDER BY {$orderBy} {$sortDir}";

$stmt = $pdo->prepare($sql);
$stmt->execute($bindings);
$registros = $stmt->fetchAll();

// 4. Carrega dados para o formulário de edição
$nivelToEdit = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM {$table_name} WHERE {$id_column} = ?");
    $stmt->execute([$_GET['id']]);
    $nivelToEdit = $stmt->fetch();
}

// Mensagens após redirecionamento
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
    <style>
        .short-text { max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block;}
        .description-cell { max-width: 150px; } /* Ajuste para melhor visualização da descrição */
        textarea { resize: vertical; }
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
                <div class="card-header bg-primary text-white">
                    <?php echo $nivelToEdit ? 'Editar: ' . htmlspecialchars($nivelToEdit[$name_column] ?? '') : 'Novo Nível Hierárquico'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="nivel_hierarquico.php">
                        <input type="hidden" name="<?php echo $id_column; ?>" value="<?php echo htmlspecialchars($nivelToEdit[$id_column] ?? 0); ?>">

                        <div class="mb-3">
                            <label for="<?php echo $order_column; ?>" class="form-label">Ordem (Nível Numérico) *</label>
                            <input type="number" class="form-control" id="<?php echo $order_column; ?>" name="<?php echo $order_column; ?>" value="<?php echo htmlspecialchars($nivelToEdit[$order_column] ?? ''); ?>" required min="1" max="99">
                            <div class="form-text">Quanto maior o número, maior o nível hierárquico. Ex: 1=Estagiário, 10=Diretor.</div>
                        </div>

                        <div class="mb-3">
                            <label for="<?php echo $name_column; ?>" class="form-label">Descrição do Nível *</label>
                            <input type="text" class="form-control" id="<?php echo $name_column; ?>" name="<?php echo $name_column; ?>" value="<?php echo htmlspecialchars($nivelToEdit[$name_column] ?? ''); ?>" required>
                            <div class="form-text">Ex: Analista Júnior, Coordenador, Gerente Executivo.</div>
                        </div>

                        <div class="mb-3">
                            <label for="<?php echo $fk_column; ?>" class="form-label">Tipo de Hierarquia *</label>
                            <select class="form-select" id="<?php echo $fk_column; ?>" name="<?php echo $fk_column; ?>" required>
                                <option value="">--- Selecione o Tipo ---</option>
                                <?php foreach ($tiposHierarquia as $id => $nome): ?>
                                    <option value="<?php echo $id; ?>" <?php echo (int)($nivelToEdit[$fk_column] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($nome); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Ex: Estratégico, Tático. <a href="tipo_hierarquia.php" target="_blank">Gerenciar Tipos</a></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="<?php echo $attributions_column; ?>" class="form-label">Distribuição das Atribuições</label>
                            <textarea class="form-control" id="<?php echo $attributions_column; ?>" name="<?php echo $attributions_column; ?>" rows="3"><?php echo htmlspecialchars($nivelToEdit[$attributions_column] ?? ''); ?></textarea>
                            <div class="form-text">Detalhamento das responsabilidades chave neste nível.</div>
                        </div>

                        <div class="mb-3">
                            <label for="<?php echo $autonomy_column; ?>" class="form-label">Autonomia / Responsabilidade</label>
                            <textarea class="form-control" id="<?php echo $autonomy_column; ?>" name="<?php echo $autonomy_column; ?>" rows="3"><?php echo htmlspecialchars($nivelToEdit[$autonomy_column] ?? ''); ?></textarea>
                            <div class="form-text">Grau de liberdade para tomada de decisão e impacto dos resultados.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="<?php echo $when_to_use_column; ?>" class="form-label">Quando Utilizar</label>
                            <textarea class="form-control" id="<?php echo $when_to_use_column; ?>" name="<?php echo $when_to_use_column; ?>" rows="3"><?php echo htmlspecialchars($nivelToEdit[$when_to_use_column] ?? ''); ?></textarea>
                            <div class="form-text">Critérios específicos ou contexto para a aplicação deste nível na estrutura.</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> <?php echo $nivelToEdit ? 'Salvar Alterações' : 'Cadastrar'; ?>
                        </button>
                        <?php if ($nivelToEdit): ?>
                             <a href="nivel_hierarquico.php" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="fas fa-plus"></i> Novo Cadastro
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <span class="fw-bold">Registros Encontrados: </span> <?php echo count($registros); ?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th><i class="fas fa-sort-numeric-down"></i> Ordem</th>
                                <th>Descrição</th>
                                <th>Tipo de Hierarquia</th>
                                <th>Atribuições</th> 
                                <th>Autonomia/Resp.</th> 
                                <th>Quando Utilizar</th> 
                                <th><i class="fas fa-calendar-alt"></i> Atualização</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registros) > 0): ?>
                                <?php foreach ($registros as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row[$id_column]); ?></td>
                                        <td><strong class="text-secondary"><?php echo htmlspecialchars($row[$order_column]); ?>º</strong></td>
                                        <td><strong class="text-primary"><?php echo htmlspecialchars($row[$name_column]); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['tipoNome'] ?? 'N/A'); ?></td>
                                        
                                        <td class="description-cell">
                                            <span class="short-text" title="<?php echo htmlspecialchars($row[$attributions_column] ?? ''); ?>">
                                                <?php echo htmlspecialchars($row[$attributions_column] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        
                                        <td class="description-cell">
                                            <span class="short-text" title="<?php echo htmlspecialchars($row[$autonomy_column] ?? ''); ?>">
                                                <?php echo htmlspecialchars($row[$autonomy_column] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        
                                        <td class="description-cell">
                                            <span class="short-text" title="<?php echo htmlspecialchars($row[$when_to_use_column] ?? ''); ?>">
                                                <?php echo htmlspecialchars($row[$when_to_use_column] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        
                                        <td><?php echo (new DateTime($row[$date_update_column] ?? $row['nivelDataCadastro'] ?? 'now'))->format('d/m/Y H:i'); ?></td>
                                        
                                        <td class="text-center">
                                            <a href="nivel_hierarquico.php?id=<?php echo $row[$id_column]; ?>" 
                                                class="btn btn-sm btn-info text-white" 
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
                                    <td colspan="9">Nenhum registro encontrado.</td>
                                </tr>
                            <?php endif; ?>
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