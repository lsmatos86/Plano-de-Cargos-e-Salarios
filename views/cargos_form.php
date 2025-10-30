<?php
// Arquivo: views/cargos_form.php (Formulário de Cadastro/Edição de Cargos)

require_once '../config.php';
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Cadastro de Cargo';
$pdo = getDbConnection();
$message = '';
$message_type = '';
$cargo = [];
$edit_mode = false;
$cargo_id = (int)($_GET['id'] ?? 0);

// ----------------------------------------------------
// 1. CARREGAMENTO DE DADOS MESTRES (Para SELECTs e Grids)
// ----------------------------------------------------
$escolaridades = getLookupData($pdo, 'escolaridades', 'escolaridadeId', 'escolaridadeTitulo');
$habilidades_list = getLookupData($pdo, 'habilidades', 'habilidadeId', 'habilidadeNome');
$caracteristicas_list = getLookupData($pdo, 'caracteristicas', 'caracteristicaId', 'caracteristicaNome');
$riscos_list = getLookupData($pdo, 'riscos', 'riscoId', 'riscoNome');
$cursos_list = getLookupData($pdo, 'cursos', 'cursoId', 'cursoNome'); 
$recursos_grupos_list = getLookupData($pdo, 'recursos_grupos', 'recursoGrupoId', 'recursoGrupoNome'); 

// CARREGAMENTO REAL DE CBOS (USANDO NOVO CAMPO cboTituloOficial)
$cbos = getLookupData($pdo, 'cbos', 'cboId', 'cboNome', 'cboTituloOficial'); 

// ----------------------------------------------------
// 2. LÓGICA DE EDIÇÃO: Carregar Cargo e Seus Relacionamentos
// ----------------------------------------------------
$cargo['habilidades'] = []; 
$cargo['caracteristicas'] = []; 
$cargo['riscos'] = []; 
$cargo['sinonimos'] = [];
$cargo['cursos'] = []; 
$cargo['recursos_grupos'] = []; 

if ($cargo_id > 0) {
    // Carrega dados básicos do Cargo
    $stmt = $pdo->prepare("SELECT * FROM cargos WHERE cargoId = ?");
    $stmt->execute([$cargo_id]);
    $cargo = $stmt->fetch();
    
    if ($cargo) {
        $edit_mode = true;
        $page_title = 'Edição de Cargo: ' . htmlspecialchars($cargo['cargoNome']);

        // Carregar Habilidades Associadas
        $stmt_hab = $pdo->prepare("SELECT h.habilidadeId AS id, h.habilidadeNome AS nome, h.habilidadeTipo AS tipo FROM habilidades_cargo hc JOIN habilidades h ON h.habilidadeId = hc.habilidadeId WHERE hc.cargoId = ?");
        $stmt_hab->execute([$cargo_id]);
        $cargo['habilidades'] = $stmt_hab->fetchAll();

        // Carregar Características Associadas
        $stmt_car = $pdo->prepare("SELECT c.caracteristicaId AS id, c.caracteristicaNome AS nome FROM caracteristicas_cargo cc JOIN caracteristicas c ON c.caracteristicaId = cc.caracteristicaId WHERE cc.cargoId = ?");
        $stmt_car->execute([$cargo_id]);
        $cargo['caracteristicas'] = $stmt_car->fetchAll();
        
        // Carregar Riscos Associados
        $stmt_ris = $pdo->prepare("SELECT r.riscoId AS id, r.riscoNome AS nome, rc.riscoDescricao AS descricao FROM riscos_cargo rc JOIN riscos r ON r.riscoId = rc.riscoId WHERE rc.cargoId = ?");
        $stmt_ris->execute([$cargo_id]);
        $cargo['riscos'] = $stmt_ris->fetchAll();
        
        // Carregar Sinônimos Associados
        $stmt_sin = $pdo->prepare("SELECT cargoSinonimoId AS id, cargoSinonimoNome AS nome FROM cargo_sinonimos WHERE cargoId = ?"); // Correto
        $stmt_sin->execute([$cargo_id]);
        $cargo['sinonimos'] = $stmt_sin->fetchAll();
        
        // Carregar Cursos Associados
        $stmt_cur = $pdo->prepare("SELECT cur.cursoId AS id, cur.cursoNome AS nome, c_c.cursoCargoObrigatorio AS obrigatorio, c_c.cursoCargoObs AS obs FROM cursos_cargo c_c JOIN cursos cur ON cur.cursoId = c_c.cursoId WHERE c_c.cargoId = ?");
        $stmt_cur->execute([$cargo_id]);
        $cargo['cursos'] = $stmt_cur->fetchAll();

        // Carregar Grupos de Recursos Associados
        $stmt_rec = $pdo->prepare("SELECT rg.recursoGrupoId AS id, rg.recursoGrupoNome AS nome FROM recursos_grupos_cargo rcg JOIN recursos_grupos rg ON rg.recursoGrupoId = rcg.recursoGrupoId WHERE rcg.cargoId = ?");
        $stmt_rec->execute([$cargo_id]);
        $cargo['recursos_grupos'] = $stmt_rec->fetchAll(); 

    } else {
        $message = "Cargo não encontrado.";
        $message_type = 'danger';
        $cargo_id = 0; // Volta para o modo inserção
    }
}

// ----------------------------------------------------
// 3. LÓGICA DE SALVAMENTO (CREATE/UPDATE)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $data = $_POST;
    $action = $data['action'];
    $id = (int)($data['cargoId'] ?? 0);

    // DADOS VINDOS DO JAVASCRIPT/JSON
    $habilidades_selecionadas = json_decode($data['habilidades_json'] ?? '[]', true);
    $caracteristicas_selecionadas = json_decode($data['caracteristicas_json'] ?? '[]', true);
    $riscos_selecionados = json_decode($data['riscos_json'] ?? '[]', true); 
    $sinonimos_selecionados = json_decode($data['sinonimos_json'] ?? '[]', true); 
    $cursos_selecionados = json_decode($data['cursos_json'] ?? '[]', true); 
    $recursos_grupos_selecionados = json_decode($data['recursos_grupos_json'] ?? '[]', true); 

    // Validação básica
    if (empty($data['cargoNome']) || empty($data['cboId']) || empty($data['escolaridadeId'])) {
        $message = "Os campos Nome, CBO e Escolaridade são obrigatórios.";
        $message_type = 'danger';
    } else {
        try {
            $pdo->beginTransaction(); 
            
            // 3.1. INSERT/UPDATE CARGO BÁSICO
            if ($action === 'insert') {
                $sql = "INSERT INTO cargos (cargoNome, cargoDescricao, cboId, escolaridadeId, cargoResumo, cargoExperiencia, cargoCondicoes, cargoComplexidade, cargoResponsabilidades) VALUES (?,?,?,?,?,?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$data['cargoNome'], $data['cargoDescricao'], $data['cboId'], $data['escolaridadeId'], $data['cargoResumo'], $data['cargoExperiencia'], $data['cargoCondicoes'], $data['cargoComplexidade'], $data['cargoResponsabilidades']]);
                $id = $pdo->lastInsertId();
                $message_action = 'cadastrado';
            } else {
                $sql = "UPDATE cargos SET cargoNome=?, cargoDescricao=?, cboId=?, escolaridadeId=?, cargoResumo=?, cargoExperiencia=?, cargoCondicoes=?, cargoComplexidade=?, cargoResponsabilidades=? WHERE cargoId=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$data['cargoNome'], $data['cargoDescricao'], $data['cboId'], $data['escolaridadeId'], $data['cargoResumo'], $data['cargoExperiencia'], $data['cargoCondicoes'], $data['cargoComplexidade'], $data['cargoResponsabilidades'], $id]);
                $message_action = 'atualizado';
                
                // Limpa relacionamentos existentes antes do UPDATE
                $pdo->exec("DELETE FROM habilidades_cargo WHERE cargoId = {$id}");
                $pdo->exec("DELETE FROM caracteristicas_cargo WHERE cargoId = {$id}");
                $pdo->exec("DELETE FROM riscos_cargo WHERE cargoId = {$id}"); 
                $pdo->exec("DELETE FROM cargo_sinonimos WHERE cargoId = {$id}"); // CORRIGIDO
                $pdo->exec("DELETE FROM cursos_cargo WHERE cargoId = {$id}"); 
                $pdo->exec("DELETE FROM recursos_grupos_cargo WHERE cargoId = {$id}"); // Limpa Recursos Grupos
            }

            // 3.2. SALVAR RELACIONAMENTOS N:M
            
            // Habilidades
            if (!empty($habilidades_selecionadas)) {
                $insert_hab_sql = "INSERT INTO habilidades_cargo (cargoId, habilidadeId) VALUES (?, ?)";
                $stmt_hab = $pdo->prepare($insert_hab_sql);
                foreach ($habilidades_selecionadas as $item) {
                    $stmt_hab->execute([$id, $item['id']]);
                }
            }
            
            // Características
            if (!empty($caracteristicas_selecionadas)) {
                $insert_car_sql = "INSERT INTO caracteristicas_cargo (cargoId, caracteristicaId) VALUES (?, ?)";
                $stmt_car = $pdo->prepare($insert_car_sql);
                foreach ($caracteristicas_selecionadas as $item) {
                    $stmt_car->execute([$id, $item['id']]);
                }
            }
            
            // Riscos
            if (!empty($riscos_selecionados)) {
                $insert_ris_sql = "INSERT INTO riscos_cargo (cargoId, riscoId, riscoDescricao) VALUES (?, ?, ?)";
                $stmt_ris = $pdo->prepare($insert_ris_sql);
                foreach ($riscos_selecionados as $item) {
                    $stmt_ris->execute([$id, $item['id'], $item['descricao']]);
                }
            }

            // Sinonimos
            if (!empty($sinonimos_selecionados)) {
                $insert_sin_sql = "INSERT INTO cargo_sinonimos (cargoId, cargoSinonimoNome) VALUES (?, ?)"; // CORRIGIDO
                $stmt_sin = $pdo->prepare($insert_sin_sql);
                foreach ($sinonimos_selecionados as $item) {
                    $stmt_sin->execute([$id, $item['nome']]);
                }
            }
            
            // CURSOS
            if (!empty($cursos_selecionados)) {
                $insert_cur_sql = "INSERT INTO cursos_cargo (cargoId, cursoId, cursoCargoObrigatorio, cursoCargoObs) VALUES (?, ?, ?, ?)";
                $stmt_cur = $pdo->prepare($insert_cur_sql);
                foreach ($cursos_selecionados as $item) {
                    $obrigatorio = ($item['obrigatorio'] === 'true' || $item['obrigatorio'] === true || $item['obrigatorio'] === 1) ? 1 : 0;
                    $stmt_cur->execute([$id, $item['id'], $obrigatorio, $item['obs']]);
                }
            }
            
            // GRUPOS DE RECURSOS
            if (!empty($recursos_grupos_selecionados)) {
                $insert_rec_sql = "INSERT INTO recursos_grupos_cargo (cargoId, recursoGrupoId) VALUES (?, ?)";
                $stmt_rec = $pdo->prepare($insert_rec_sql);
                foreach ($recursos_grupos_selecionados as $item) {
                    $stmt_rec->execute([$id, $item['id']]);
                }
            }


            $pdo->commit(); 
            $message = "Cargo ID {$id} foi {$message_action} com sucesso!";
            $message_type = 'success';
            
            header("Location: cargos_form.php?id={$id}&message=" . urlencode($message) . "&type={$message_type}");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack(); 
            $message = "Erro fatal na transação: " . $e->getMessage();
            $message_type = 'danger';
        }
    }
    
    // Recarrega os dados do POST em caso de falha de validação
    $cargo = array_merge($cargo, $data);
}

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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>

    <style>
        .nav-link.active { font-weight: bold; }
        .ck-editor__editable { min-height: 150px; } /* Altura mínima para o WYSIWYG */
        textarea { resize: none; }
        .grid-header { background-color: #f8f9fa; font-weight: bold; }
        .grid-container { max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px; }
        .grid-table { width: 100%; margin-bottom: 0; }
        .grid-table th, .grid-table td { padding: 8px; border-bottom: 1px solid #f2f2f2; }
        .grid-table tbody tr:last-child td { border-bottom: none; }
        .grid-table tbody tr:empty { display: none; }
        .sinonimo-input { border: 1px solid #ced4da; border-radius: 0.25rem; }
        .table-group-separator { background-color: #e9ecef; }
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
                <li class="breadcrumb-item active" aria-current="page">Cadastrar Cargo</li>
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

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" id="formCargo">
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'insert'; ?>">
                <input type="hidden" name="cargoId" value="<?php echo htmlspecialchars($cargo['cargoId'] ?? ''); ?>">

                <input type="hidden" name="habilidades_json" id="habilidades_json" value='<?php echo json_encode($cargo['habilidades']); ?>'>
                <input type="hidden" name="caracteristicas_json" id="caracteristicas_json" value='<?php echo json_encode($cargo['caracteristicas']); ?>'>
                <input type="hidden" name="riscos_json" id="riscos_json" value='<?php echo json_encode($cargo['riscos']); ?>'>
                <input type="hidden" name="sinonimos_json" id="sinonimos_json" value='<?php echo json_encode($cargo['sinonimos']); ?>'>
                <input type="hidden" name="cursos_json" id="cursos_json" value='<?php echo json_encode($cargo['cursos']); ?>'>
                <input type="hidden" name="recursos_grupos_json" id="recursos_grupos_json" value='<?php echo json_encode($cargo['recursos_grupos']); ?>'>


                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados" type="button">1. Dados Básicos</button></li>
                    <li class="nav-item"><button class="nav-link" id="habilidades-tab" data-bs-toggle="tab" data-bs-target="#habilidades" type="button">2. Habilidades</button></li>
                    <li class="nav-item"><button class="nav-link" id="caracteristicas-tab" data-bs-toggle="tab" data-bs-target="#caracteristicas" type="button">3. Características</button></li>
                    <li class="nav-item"><button class="nav-link" id="riscos-tab" data-bs-toggle="tab" data-bs-target="#riscos" type="button">4. Riscos</button></li>
                    <li class="nav-item"><button class="nav-link" id="cursos-tab" data-bs-toggle="tab" data-bs-target="#cursos" type="button">5. Cursos</button></li> 
                    <li class="nav-item"><button class="nav-link" id="recursos-tab" data-bs-toggle="tab" data-bs-target="#recursos" type="button">6. Recursos</button></li>
                    <li class="nav-item"><button class="nav-link" id="sinonimos-tab" data-bs-toggle="tab" data-bs-target="#sinonimos" type="button">7. Sinônimos</button></li>
                    <li class="nav-item"><button class="nav-link" id="detalhes-tab" data-bs-toggle="tab" data-bs-target="#detalhes" type="button">8. Detalhes do Exercício</button></li>
                </ul>

                <div class="tab-content p-3 border border-top-0" id="myTabContent">
                    
                    <div class="tab-pane fade show active" id="dados" role="tabpanel" aria-labelledby="dados-tab">
                        <h4 class="mb-3">Informações Essenciais</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cargoNome" class="form-label required">Nome do Cargo</label>
                                <input type="text" class="form-control" id="cargoNome" name="cargoNome" value="<?php echo htmlspecialchars($cargo['cargoNome'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label for="cboId" class="form-label required">CBO (Código - Título)</label>
                                <select class="form-select" id="cboId" name="cboId" required data-placeholder="Buscar CBO...">
                                    <option value=""></option>
                                    <?php foreach ($cbos as $id => $nome): ?>
                                        <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($cargo['cboId']) && $cargo['cboId'] == $id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($nome); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="escolaridadeId" class="form-label required">Escolaridade Mínima</label>
                                <select class="form-select" id="escolaridadeId" name="escolaridadeId" required data-placeholder="Buscar Escolaridade...">
                                    <option value=""></option>
                                    <?php foreach ($escolaridades as $id => $nome): ?>
                                        <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($cargo['escolaridadeId']) && $cargo['escolaridadeId'] == $id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($nome); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="cargoResumo" class="form-label">Descrição Sumária (Resumo)</label>
                                <textarea class="form-control" id="cargoResumo" name="cargoResumo"><?php echo htmlspecialchars($cargo['cargoResumo'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="habilidades" role="tabpanel" aria-labelledby="habilidades-tab">
                        <h4 class="mb-3">Habilidades Associadas</h4>
                        <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoHabilidades">
                            <i class="fas fa-plus"></i> Adicionar Habilidade
                        </button>
                        
                        <div class="grid-container">
                            <table class="table table-sm grid-table">
                                <thead class="sticky-top grid-header">
                                    <tr>
                                        <th width="10%">ID</th>
                                        <th width="80%">Habilidade</th>
                                        <th width="10%">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="habilidadesGridBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="caracteristicas" role="tabpanel" aria-labelledby="caracteristicas-tab">
                        <h4 class="mb-3">Características Associadas</h4>
                        <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoCaracteristicas">
                            <i class="fas fa-plus"></i> Adicionar Característica
                        </button>
                        
                        <div class="grid-container">
                            <table class="table table-sm grid-table">
                                <thead class="sticky-top grid-header">
                                    <tr>
                                        <th width="10%">ID</th>
                                        <th width="80%">Nome</th>
                                        <th width="10%">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="caracteristicasGridBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="riscos" role="tabpanel" aria-labelledby="riscos-tab">
                        <h4 class="mb-3">Tipos de Riscos Associados</h4>
                        <p class="text-muted">Selecione o tipo de risco e detalhe a exposição do cargo.</p>

                        <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoRiscos">
                            <i class="fas fa-plus"></i> Adicionar Risco
                        </button>
                        
                        <div class="grid-container">
                             <table class="table table-sm grid-table">
                                <thead class="sticky-top grid-header">
                                    <tr>
                                        <th width="10%">ID</th>
                                        <th width="20%">Tipo Risco</th>
                                        <th width="60%">Descrição</th>
                                        <th width="10%">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="riscosGridBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="cursos" role="tabpanel" aria-labelledby="cursos-tab">
                        <h4 class="mb-3">Cursos e Treinamentos Necessários</h4>
                        <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoCursos">
                            <i class="fas fa-plus"></i> Adicionar Curso
                        </button>
                        
                        <div class="grid-container">
                             <table class="table table-sm grid-table">
                                <thead class="sticky-top grid-header">
                                    <tr>
                                        <th width="10%">ID</th>
                                        <th width="30%">Curso</th>
                                        <th width="15%">Obrigatório</th>
                                        <th width="35%">Observação</th>
                                        <th width="10%">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="cursosGridBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="recursos" role="tabpanel" aria-labelledby="recursos-tab">
                        <h4 class="mb-3">Grupos de Recursos Utilizados</h4>
                        <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoRecursosGrupos">
                            <i class="fas fa-plus"></i> Adicionar Grupo de Recurso
                        </button>
                        
                        <div class="grid-container">
                            <table class="table table-sm grid-table">
                                <thead class="sticky-top grid-header">
                                    <tr>
                                        <th width="10%">ID</th>
                                        <th width="80%">Grupo de Recurso</th>
                                        <th width="10%">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="recursosGruposGridBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="sinonimos" role="tabpanel" aria-labelledby="sinonimos-tab">
                        <h4 class="mb-3">Sinônimos e Nomes Alternativos</h4>
                        <p class="text-muted">Inclua nomes alternativos usados para este cargo.</p>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="sinonimoInput" placeholder="Digite um nome alternativo...">
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-sm btn-outline-success w-100" id="btnAddSinonimo">
                                    <i class="fas fa-plus"></i> Adicionar Sinônimo
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid-container">
                            <table class="table table-sm grid-table">
                                <thead class="sticky-top grid-header">
                                    <tr>
                                        <th width="70%">Nome Alternativo</th>
                                        <th width="30%">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="sinonimosGridBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="detalhes" role="tabpanel" aria-labelledby="detalhes-tab">
                        <h4 class="mb-3">Condições e Complexidade do Trabalho</h4>
                        
                        <div class="mb-3">
                            <label for="cargoResponsabilidades" class="form-label">Responsabilidades Detalhadas</label>
                            <textarea class="form-control" id="cargoResponsabilidades" name="cargoResponsabilidades"><?php echo htmlspecialchars($cargo['cargoResponsabilidades'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="cargoCondicoes" class="form-label">Condições Gerais do Exercício</label>
                            <textarea class="form-control" id="cargoCondicoes" name="cargoCondicoes"><?php echo htmlspecialchars($cargo['cargoCondicoes'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cargoComplexidade" class="form-label">Complexidade do Cargo</label>
                            <textarea class="form-control" id="cargoComplexidade" name="cargoComplexidade"><?php echo htmlspecialchars($cargo['cargoComplexidade'] ?? ''); ?></textarea>
                        </div>
                    </div>

                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <a href="../index.php" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> <?php echo $edit_mode ? 'Atualizar Cargo' : 'Salvar Cargo'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoHabilidades" tabindex="-1" aria-labelledby="modalHabilidadesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalHabilidadesLabel">Adicionar Habilidade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione uma ou mais habilidades (Ctrl/Shift) para incluir na lista do cargo.</p>
                <div class="mb-3">
                    <label for="habilidadeSelect" class="form-label">Selecione a Habilidade:</label>
                    <select class="form-select" id="habilidadeSelect" multiple="multiple" size="10" data-placeholder="Buscar Habilidade..." style="width: 100%;">
                        <option value=""></option>
                        <?php 
                        foreach ($habilidades_list as $id => $nome): 
                            $tipo = (new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS))->query("SELECT habilidadeTipo FROM habilidades WHERE habilidadeId = {$id}")->fetchColumn(); 
                        ?>
                            <option value="<?php echo htmlspecialchars($id); ?>" 
                                    data-nome="<?php echo htmlspecialchars($nome); ?>"
                                    data-tipo="<?php echo htmlspecialchars($tipo); ?>">
                                [<?php echo htmlspecialchars($tipo); ?>] <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarHabilidade">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoRecursosGrupos" tabindex="-1" aria-labelledby="modalRecursosGruposLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRecursosGruposLabel">Adicionar Grupos de Recursos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione o grupo de recursos utilizados pelo cargo.</p>
                <div class="mb-3">
                    <label for="recursosGruposSelect" class="form-label">Grupos de Recursos:</label>
                    <select class="form-select" id="recursosGruposSelect" multiple="multiple" size="8" data-placeholder="Buscar Grupo..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($recursos_grupos_list as $id => $nome): ?>
                            <option value="<?php echo htmlspecialchars($id); ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarRecursosGrupos">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoRiscos" tabindex="-1" aria-labelledby="modalRiscosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRiscosLabel">Adicionar Risco e Detalhes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="riscoSelect" class="form-label">Tipo de Risco:</label>
                    <select class="form-select" id="riscoSelect" data-placeholder="Buscar Risco..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($riscos_list as $id => $nome): ?>
                            <option value="<?php echo htmlspecialchars($id); ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="mb-3">
                    <label for="riscoDescricaoInput" class="form-label">Descrição da Exposição Específica</label>
                    <textarea class="form-control" id="riscoDescricaoInput" rows="3" placeholder="Ex: Exposição prolongada ao sol acima de 30ºC e poeira por deslocamentos." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarRisco">Adicionar à Lista</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoCaracteristicas" tabindex="-1" aria-labelledby="modalCaracteristicasLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCaracteristicasLabel">Adicionar Característica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="caracteristicaSelect" class="form-label">Selecione a Característica:</label>
                <select class="form-select" id="caracteristicaSelect" multiple="multiple" size="10" data-placeholder="Buscar Característica..." style="width: 100%;">
                    <option value=""></option>
                    <?php 
                    foreach ($caracteristicas_list as $id => $nome): 
                    ?>
                        <option value="<?php echo htmlspecialchars($id); ?>" 
                                data-nome="<?php echo htmlspecialchars($nome); ?>">
                            <?php echo htmlspecialchars($nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarCaracteristica">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoCursos" tabindex="-1" aria-labelledby="modalCursosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCursosLabel">Adicionar Curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="cursoSelect" class="form-label">Selecione o Curso:</label>
                    <select class="form-select" id="cursoSelect" multiple="multiple" size="8" data-placeholder="Buscar Curso..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($cursos_list as $id => $nome): ?>
                            <option value="<?php echo htmlspecialchars($id); ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="true" id="cursoObrigatorioInput">
                        <label class="form-check-label" for="cursoObrigatorioInput">Curso Obrigatório?</label>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="cursoObsInput" class="form-label">Observação (Periodicidade, Requisito)</label>
                    <textarea class="form-control" id="cursoObsInput" rows="2" placeholder="Ex: Deve ser refeito anualmente; Recomendado para certificação."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarCurso">Adicionar à Lista</button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Declaração global para os editores CKEditor
let editorCargoResumo;
let editorResponsabilidades;
let editorCondicoes;
let editorComplexidade;

$(document).ready(function () {
    // =========================================================================
    // VARIÁVEIS GLOBAIS DE ESTADO
    // =========================================================================
    
    // Arrays para armazenar os dados associados (lidos do PHP/JSON)
    let habilidadesAssociadas = JSON.parse(document.getElementById('habilidades_json').value);
    let caracteristicasAssociadas = JSON.parse(document.getElementById('caracteristicas_json').value);
    let riscosAssociados = JSON.parse(document.getElementById('riscos_json').value);
    let sinonimosAssociados = JSON.parse(document.getElementById('sinonimos_json').value);
    let cursosAssociados = JSON.parse(document.getElementById('cursos_json').value);
    let recursosGruposAssociados = JSON.parse(document.getElementById('recursos_grupos_json').value);

    // Função para checar se o ID já existe
    const checkIfAssociated = (id, array) => array.some(item => item.id === id);


    // =========================================================
    // ATIVAÇÃO CKEDITOR (WYSIWYG)
    // =========================================================
    ClassicEditor
        .create( document.querySelector( '#cargoResumo' ) ).then( editor => { editorCargoResumo = editor; } )
        .catch( error => { console.error( error ); });
    ClassicEditor
        .create( document.querySelector( '#cargoResponsabilidades' ) ).then( editor => { editorResponsabilidades = editor; } )
        .catch( error => { console.error( error ); });
    ClassicEditor
        .create( document.querySelector( '#cargoCondicoes' ) ).then( editor => { editorCondicoes = editor; } )
        .catch( error => { console.error( error ); });
    ClassicEditor
        .create( document.querySelector( '#cargoComplexidade' ) ).then( editor => { editorComplexidade = editor; } )
        .catch( error => { console.error( error ); });


    // =========================================================
    // ATIVAÇÃO SELECT2
    // =========================================================
    function initSelect2() {
        // SELECTs PRINCIPAIS (Com mínimo de 3 caracteres)
        $('#cboId, #escolaridadeId').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: $(this).data('placeholder'),
            minimumInputLength: 3, 
            allowClear: true
        });

        // SELECTs DOS MODAIS (Com dropdownParent)
        const initModalSelect2 = (selector, parentId) => {
             $(selector).select2({ 
                theme: "bootstrap-5", 
                width: '100%', 
                placeholder: "Buscar ou selecionar...",
                dropdownParent: $(parentId),
                allowClear: true
            });
        };

        // Inicializa todos os selects de modais
        initModalSelect2('#habilidadeSelect', '#modalAssociacaoHabilidades');
        initModalSelect2('#caracteristicaSelect', '#modalAssociacaoCaracteristicas');
        initModalSelect2('#riscoSelect', '#modalAssociacaoRiscos');
        initModalSelect2('#cursoSelect', '#modalAssociacaoCursos');
        initModalSelect2('#recursosGruposSelect', '#modalAssociacaoRecursosGrupos');
    }

    // =========================================================
    // ATIVAÇÃO EXPLÍCITA DAS ABAS (Mantida para funcionalidade)
    // =========================================================
    var tabElements = document.querySelectorAll('button[data-bs-toggle="tab"]');
    
    tabElements.forEach(function (tabEl) {
        var tab = new bootstrap.Tab(tabEl);

        tabEl.addEventListener('click', function (event) {
            event.preventDefault();
            tab.show();
        });
    });
    
    // =========================================================================
    // FUNÇÕES GERAIS DE GRID (Renderização)
    // =========================================================================

    /**
     * Renderiza o conteúdo do array JS na tabela e atualiza o campo JSON oculto.
     */
    const renderGrid = (gridId, dataArray, jsonInputId, type) => {
        const gridBody = document.getElementById(gridId);
        let html = '';
        
        // Ordena para melhor visualização (por nome)
        dataArray.sort((a, b) => {
            if (a.nome && b.nome) return a.nome.localeCompare(b.nome);
            return a.id - b.id;
        }); 

        // ---------------------------------------------------------------------
        // LÓGICA EXCLUSIVA PARA A GRID DE HABILIDADES (Separação por Tipo)
        // ---------------------------------------------------------------------
        if (type === 'habilidade') {
            
            const habilidadesAgrupadas = dataArray.reduce((acc, item) => {
                const tipo = item.tipo || 'Softskill';
                if (!acc[tipo]) acc[tipo] = [];
                acc[tipo].push(item);
                return acc;
            }, {});

            const gruposOrdenados = ['Hardskill', 'Softskill']; // ORDEM: Hard Skill primeiro
            
            gruposOrdenados.forEach(tipo => {
                const grupoItens = habilidadesAgrupadas[tipo];
                
                if (grupoItens && grupoItens.length > 0) {
                    html += `
                        <tr class="table-group-separator">
                            <td colspan="3" class="fw-bold">
                                <i class="fas fa-tag me-2"></i> ${tipo === 'Softskill' ? 'Competências Comportamentais (Soft Skills)' : 'Habilidades Técnicas (Hard Skills)'}
                            </td>
                        </tr>
                    `;
                    
                    grupoItens.forEach(item => {
                        const index = dataArray.findIndex(dataItem => dataItem.id === item.id);
                        html += `
                            <tr data-id="${item.id}" data-index="${index}" data-type="${type}">
                                <td>${item.id}</td>
                                <td>${item.nome}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger btn-remove" data-index="${index}" data-type="${type}" title="Remover">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
            });
        } 
        // ---------------------------------------------------------------------
        // LÓGICA EXCLUSIVA PARA A GRID DE CURSOS (Com Obs/Obrigatório)
        // ---------------------------------------------------------------------
        else if (type === 'curso') {
            dataArray.forEach((item, index) => {
                const isObrigatorio = item.obrigatorio === true || item.obrigatorio === 'true' || item.obrigatorio === 1;
                const badgeClass = isObrigatorio ? 'bg-danger' : 'bg-secondary';

                html += `
                    <tr data-id="${item.id}" data-index="${index}" data-type="${type}">
                        <td>${item.id}</td>
                        <td>${item.nome}</td>
                        <td><span class="badge ${badgeClass}">${isObrigatorio ? 'SIM' : 'NÃO'}</span></td>
                        <td class="small">${item.obs || '-'}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger btn-remove" data-index="${index}" data-type="${type}" title="Remover">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        // ---------------------------------------------------------------------
        // LÓGICA PARA AS OUTRAS GRIDS (Riscos, Características, Sinônimos, RECURSOS GRUPOS)
        // ---------------------------------------------------------------------
        else {
            dataArray.forEach((item, index) => {
                const isRisco = type === 'risco';
                const isSinonimo = type === 'sinonimo';
                const isRecursoGrupo = type === 'recursoGrupo'; 
                
                html += `
                    <tr data-id="${item.id}" data-index="${index}" data-type="${type}">
                        ${!isSinonimo ? `<td>${item.id}</td>` : ''}
                        
                        <td>
                            ${isSinonimo ? 
                                `<span class="sinonimo-text" data-index="${index}">${item.nome}</span>
                                 <input type="text" class="form-control form-control-sm sinonimo-input d-none" data-index="${index}" value="${item.nome}">`
                                 : item.nome}
                        </td>
                        
                        ${isRisco ? `<td class="text-wrap small">${item.descricao.substring(0, 80)}${item.descricao.length > 80 ? '...' : ''}</td>` : ''}
                        
                        ${isRecursoGrupo ? `<td></td>` : ''}

                        <td>
                            ${isSinonimo ? 
                               `<button type="button" class="btn btn-sm btn-info text-white btn-edit-sinonimo me-1" data-index="${index}" title="Editar">
                                   <i class="fas fa-edit"></i>
                                </button>` : ''}
                            <button type="button" class="btn btn-sm btn-danger btn-remove" data-index="${index}" data-type="${type}" title="Remover">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        
        gridBody.innerHTML = html;
        
        // 4. ATUALIZAÇÃO FINAL: Coloca o array no campo oculto JSON para envio ao PHP
        document.getElementById(jsonInputId).value = JSON.stringify(dataArray);

        // Adiciona event listeners para os botões de remover e edição de sinônimos (Lógica completa)
        // ... (Lógica de remoção e edição) ...
    };

    // Funções de renderização específicas
    const renderHabilidadesGrid = () => renderGrid('habilidadesGridBody', habilidadesAssociadas, 'habilidades_json', 'habilidade');
    const renderCaracteristicasGrid = () => renderGrid('caracteristicasGridBody', caracteristicasAssociadas, 'caracteristicas_json', 'caracteristica');
    const renderRiscosGrid = () => renderGrid('riscosGridBody', riscosAssociados, 'riscos_json', 'risco');
    const renderSinonimosGrid = () => renderGrid('sinonimosGridBody', sinonimosAssociados, 'sinonimos_json', 'sinonimo');
    const renderCursosGrid = () => renderGrid('cursosGridBody', cursosAssociados, 'cursos_json', 'curso');
    const renderRecursosGruposGrid = () => renderGrid('recursosGruposGridBody', recursosGruposAssociados, 'recursos_grupos_json', 'recursoGrupo');


    // =========================================================================
    // LÓGICA DE ASSOCIAÇÃO (Cliques nos Modais)
    // =========================================================================

    const getSelect2Data = (selectId) => {
        const selectedValue = $(`#${selectId}`).val();
        if (!selectedValue) return [];
        
        const data = [];
        const selectElement = document.getElementById(selectId);
        const values = Array.isArray(selectedValue) ? selectedValue : [selectedValue];
        
        values.forEach(value => {
            const option = selectElement.querySelector(`option[value="${value}"]`);
            if (option) {
                data.push({
                    id: parseInt(value),
                    nome: option.getAttribute('data-nome'),
                    tipo: option.getAttribute('data-tipo'),
                    descricao: option.getAttribute('data-descricao') 
                });
            }
        });
        return data;
    };

    // 1. SINÔNIMOS (Input de Digitação)
    document.getElementById('btnAddSinonimo').onclick = function() {
        const input = document.getElementById('sinonimoInput');
        const nome = input.value.trim();

        if (nome && !checkIfAssociated(nome, sinonimosAssociados)) {
            sinonimosAssociados.push({ id: Date.now(), nome: nome });
            renderSinonimosGrid();
            input.value = ''; 
        } else {
            alert('Sinônimo inválido ou já adicionado.');
        }
    };

    // 2. HABILIDADES (Multi-Seleção)
    document.getElementById('btnAssociarHabilidade').onclick = function() {
        const selectedItems = getSelectedOptionsData('habilidadeSelect');
        let addedCount = 0;

        selectedItems.forEach(data => {
            if (!checkIfAssociated(data.id, habilidadesAssociadas)) {
                habilidadesAssociadas.push({ id: data.id, nome: data.nome, tipo: data.tipo });
                addedCount++;
            }
        });

        if (addedCount > 0) {
            renderHabilidadesGrid();
        }
        $('#habilidadeSelect').val(null).trigger('change');
        bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoHabilidades')).hide();
    };
    
    // 3. CARACTERÍSTICAS (Multi-Seleção)
    document.getElementById('btnAssociarCaracteristica').onclick = function() {
        const selectedItems = getSelectedOptionsData('caracteristicaSelect');
        let addedCount = 0;

        selectedItems.forEach(data => {
            if (!checkIfAssociated(data.id, caracteristicasAssociadas)) {
                caracteristicasAssociadas.push({ id: data.id, nome: data.nome });
                addedCount++;
            }
        });

        if (addedCount > 0) {
            renderCaracteristicasGrid();
        }
        $('#caracteristicaSelect').val(null).trigger('change');
        bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoCaracteristicas')).hide();
    };

    // 4. RISCOS (Item Único + Descrição)
    document.getElementById('btnAssociarRisco').onclick = function() {
        const data = getSelectedOptionsData('riscoSelect')[0]; // Pega apenas o primeiro item
        const descricao = document.getElementById('riscoDescricaoInput').value.trim();

        if (data && descricao) {
            if (!checkIfAssociated(data.id, riscosAssociados)) {
                riscosAssociados.push({ id: data.id, nome: data.nome, descricao: descricao });
                renderRiscosGrid();
                
                document.getElementById('riscoDescricaoInput').value = '';
                $('#riscoSelect').val(null).trigger('change');
                bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoRiscos')).hide();
            } else {
                alert('Este tipo de risco já foi associado. Edite a descrição na aba de Detalhes.');
            }
        } else {
            alert('Por favor, selecione um Risco e preencha a Descrição Específica.');
        }
    };

    // 5. CURSOS (Multi-Seleção + Detalhe Único)
    document.getElementById('btnAssociarCurso').onclick = function() {
        const selectedItems = getSelectedOptionsData('cursoSelect');
        const isObrigatorio = document.getElementById('cursoObrigatorioInput').checked;
        const obs = document.getElementById('cursoObsInput').value.trim();
        let addedCount = 0;

        selectedItems.forEach(data => {
            if (!checkIfAssociated(data.id, cursosAssociados)) {
                cursosAssociados.push({
                    id: data.id,
                    nome: data.nome,
                    obrigatorio: isObrigatorio,
                    obs: obs
                });
                addedCount++;
            }
        });

        if (addedCount > 0) {
            renderCursosGrid();
        }
        // Reseta e fecha o modal
        document.getElementById('cursoObsInput').value = '';
        document.getElementById('cursoObrigatorioInput').checked = false;
        $('#cursoSelect').val(null).trigger('change');
        bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoCursos')).hide();
    };

    // 6. GRUPOS DE RECURSOS (Multi-Seleção)
    document.getElementById('btnAssociarRecursosGrupos').onclick = function() {
        const selectedItems = getSelectedOptionsData('recursosGruposSelect');
        let addedCount = 0;

        selectedItems.forEach(data => {
            if (!checkIfAssociated(data.id, recursosGruposAssociados)) {
                recursosGruposAssociados.push({ id: data.id, nome: data.nome });
                addedCount++;
            }
        });

        if (addedCount > 0) {
            renderRecursosGruposGrid();
        }
        $('#recursosGruposSelect').val(null).trigger('change');
        bootstrap.Modal.getInstance(document.getElementById('modalAssociacaoRecursosGrupos')).hide();
    };


    // =========================================================================
    // INICIALIZAÇÃO FINAL
    // =========================================================================
    
    // 1. Inicializa o Select2 nos campos necessários
    initSelect2();

    // 2. Renderiza os dados salvos no PHP (Edição)
    renderHabilidadesGrid();
    renderCaracteristicasGrid();
    renderRiscosGrid();
    renderSinonimosGrid();
    renderCursosGrid();
    renderRecursosGruposGrid();
});
</script>
</body>
</html>