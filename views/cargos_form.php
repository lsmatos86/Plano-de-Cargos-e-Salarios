<?php
// Arquivo: views/cargos_form.php (Formulário de Cadastro e Edição de Cargos)

require_once '../config.php';
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$pdo = getDbConnection();
$message = '';
$message_type = '';

// Variáveis de Controle
$originalId = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

// Definindo o modo da página
$isDuplicating = $action === 'duplicate' && $originalId > 0;
$isEditing = !$isDuplicating && $originalId > 0;

// O ID que será enviado no formulário (0 para novo/duplicação, ou o ID existente para edição)
$currentFormId = $isEditing ? $originalId : 0; 
$cargoId = $originalId; // ID usado para buscar os dados

$page_title = $isDuplicating ? 'Duplicar Cargo (Novo Registro)' : ($isEditing ? 'Editar Cargo' : 'Novo Cargo');

// ----------------------------------------------------
// 1. CARREGAMENTO DOS LOOKUPS MESTRES
// ----------------------------------------------------
// Lookups existentes
$cbos = getLookupData($pdo, 'cbos', 'cboId', 'cboNome', 'cboTituloOficial');
$escolaridades = getLookupData($pdo, 'escolaridades', 'escolaridadeId', 'escolaridadeTitulo');
$habilidades = getLookupData($pdo, 'habilidades', 'habilidadeId', 'habilidadeNome');
$caracteristicas = getLookupData($pdo, 'caracteristicas', 'caracteristicaId', 'caracteristicaNome');
$riscos = getLookupData($pdo, 'riscos', 'riscoId', 'riscoNome');
$cursos = getLookupData($pdo, 'cursos', 'cursoId', 'cursoNome');
$recursosGrupos = getLookupData($pdo, 'recursos_grupos', 'recursoGrupoId', 'recursoGrupoNome');

// Novos lookups de Hierarquia e Salário
$faixasSalariais = getLookupData($pdo, 'faixas_salariais', 'faixaId', 'faixaNivel');
$areasAtuacao = getAreaHierarchyLookup($pdo); 
$cargosSupervisor = getLookupData($pdo, 'cargos', 'cargoId', 'cargoNome');

// --- Variáveis de estado do Formulário ---
$cargo = [];
$cargoAreas = [];
$cargoHabilidades = [];
$cargoCaracteristicas = [];
$cargoRiscos = [];
$cargoCursos = [];
$cargoRecursosGrupos = [];


// ----------------------------------------------------
// 2. BUSCA DADOS PARA EDIÇÃO OU DUPLICAÇÃO
// ----------------------------------------------------
if ($isEditing || $isDuplicating) {
    try {
        // 2.1 Busca dados principais do cargo (usando o ID original)
        $stmt = $pdo->prepare("SELECT * FROM cargos WHERE cargoId = ?");
        $stmt->execute([$cargoId]); 
        $cargo = $stmt->fetch();

        if (!$cargo) {
            $message = "Cargo não encontrado.";
            $message_type = 'danger';
            $isEditing = false;
        }

        if ($cargo) {
            // 2.2 Busca Relacionamentos N:M (usando o ID original)
            
            $stmt = $pdo->prepare("SELECT areaId FROM cargos_area WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoAreas = $stmt->fetchAll(PDO::FETCH_COLUMN); 
            
            $stmt = $pdo->prepare("SELECT habilidadeId FROM habilidades_cargo WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoHabilidades = $stmt->fetchAll(PDO::FETCH_COLUMN); 
            
            $stmt = $pdo->prepare("SELECT caracteristicaId FROM caracteristicas_cargo WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoCaracteristicas = $stmt->fetchAll(PDO::FETCH_COLUMN); 
            
            $stmt = $pdo->prepare("SELECT riscoId FROM riscos_cargo WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoRiscos = $stmt->fetchAll(PDO::FETCH_COLUMN); 
            
            $stmt = $pdo->prepare("SELECT cursoId FROM cursos_cargo WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoCursos = $stmt->fetchAll(PDO::FETCH_COLUMN); 
            
            $stmt = $pdo->prepare("SELECT recursoGrupoId FROM recursos_grupos_cargo WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
            $cargoRecursosGrupos = $stmt->fetchAll(PDO::FETCH_COLUMN); 

            // 2.3 Lógica Específica de DUPLICAÇÃO:
            if ($isDuplicating) {
                // 1. Modificar o nome
                $cargo['cargoNome'] = ($cargo['cargoNome'] ?? 'Cargo Duplicado') . ' (CÓPIA)';
                // 2. O ID é automaticamente 0 (via $currentFormId), forçando o INSERT.
                // Remove o ID do cargo carregado para não afetar o POST
                unset($cargo['cargoId']); 
            }
        }


    } catch (PDOException $e) {
        $message = "Erro ao carregar dados para " . ($isDuplicating ? 'duplicação' : 'edição') . ": " . $e->getMessage();
        $message_type = 'danger';
        $isEditing = false;
        $isDuplicating = false;
    }
}


// ----------------------------------------------------
// 3. LÓGICA DE SALVAMENTO (POST) - Transacional e Completa
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cargoNome'])) {
    
    // O ID de submissão (0 para INSERT, ID > 0 para UPDATE)
    $cargoIdSubmissao = (int)($_POST['cargoId'] ?? 0);
    $isUpdating = $cargoIdSubmissao > 0;
    
    // 3.1 Captura e Sanitização dos Dados
    $data = [
        'cargoNome' => trim($_POST['cargoNome'] ?? ''),
        'cargoDescricao' => trim($_POST['cargoDescricao'] ?? null),
        'cboId' => (int)($_POST['cboId'] ?? 0),
        'cargoResumo' => trim($_POST['cargoResumo'] ?? null),
        'escolaridadeId' => (int)($_POST['escolaridadeId'] ?? 0),
        'cargoExperiencia' => trim($_POST['cargoExperiencia'] ?? null),
        'cargoCondicoes' => trim($_POST['cargoCondicoes'] ?? null),
        'cargoComplexidade' => trim($_POST['cargoComplexidade'] ?? null),
        'cargoResponsabilidades' => trim($_POST['cargoResponsabilidades'] ?? null),
        // Novos campos
        'faixaId' => empty($_POST['faixaId']) ? null : (int)$_POST['faixaId'],
        'nivelHierarquicoId' => empty($_POST['nivelHierarquicoId']) ? null : (int)$_POST['nivelHierarquicoId'],
        'cargoSupervisorId' => empty($_POST['cargoSupervisorId']) ? null : (int)$_POST['cargoSupervisorId'],
    ];

    $relacionamentos = [
        'cargos_area' => ['coluna' => 'areaId', 'valores' => (array)($_POST['areasAtuacao'] ?? [])],
        'habilidades_cargo' => ['coluna' => 'habilidadeId', 'valores' => (array)($_POST['habilidades'] ?? [])],
        'caracteristicas_cargo' => ['coluna' => 'caracteristicaId', 'valores' => (array)($_POST['caracteristicas'] ?? [])],
        'riscos_cargo' => ['coluna' => 'riscoId', 'valores' => (array)($_POST['riscos'] ?? [])],
        'cursos_cargo' => ['coluna' => 'cursoId', 'valores' => (array)($_POST['cursos'] ?? [])],
        'recursos_grupos_cargo' => ['coluna' => 'recursoGrupoId', 'valores' => (array)($_POST['recursosGrupos'] ?? [])],
        // Adicione outros relacionamentos N:M aqui (Ex: sinonimos, etc.)
    ];
    
    // 3.2 Validação Mínima
    if (empty($data['cargoNome']) || $data['cboId'] <= 0 || $data['escolaridadeId'] <= 0) {
        $message = "Os campos Nome do Cargo, CBO e Escolaridade são obrigatórios.";
        $message_type = 'danger';
        // Recarrega os dados do POST para manter os campos preenchidos
        $cargo = array_merge($cargo, $_POST);
        $cargoAreas = $relacionamentos['cargos_area']['valores'];
        // Define o ID para o formulário no caso de erro
        $cargoId = $cargoIdSubmissao;
    } else {

        try {
            $pdo->beginTransaction();

            // 3.3 PREPARAÇÃO DA QUERY PRINCIPAL
            $fields = array_keys($data);
            $bindings = array_values($data);

            if ($isUpdating) {
                // UPDATE
                $sql_fields = implode(' = ?, ', $fields) . ' = ?';
                $sql = "UPDATE cargos SET {$sql_fields}, cargoDataAtualizacao = CURRENT_TIMESTAMP() WHERE cargoId = ?";
                $bindings[] = $cargoIdSubmissao;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($bindings);
                $novoCargoId = $cargoIdSubmissao;
            } else {
                // CREATE
                $sql_fields = implode(', ', $fields);
                $placeholders = implode(', ', array_fill(0, count($fields), '?'));
                $sql = "INSERT INTO cargos ({$sql_fields}) VALUES ({$placeholders})";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($bindings);
                
                $novoCargoId = $pdo->lastInsertId();
            }

            // 3.4 SALVAMENTO DOS RELACIONAMENTOS N:M
            foreach ($relacionamentos as $tableName => $rel) {
                $column = $rel['coluna'];
                $valores = $rel['valores'];
                
                // Limpa todos os vínculos antigos
                $pdo->prepare("DELETE FROM {$tableName} WHERE cargoId = ?")->execute([$novoCargoId]);
                
                if (!empty($valores)) {
                    // Insere novos vínculos
                    $placeholders = implode(',', array_fill(0, count($valores), '(?, ?)'));
                    $insert_sql = "INSERT INTO {$tableName} (cargoId, {$column}) VALUES {$placeholders}";
                    
                    $bindings_rel = [];
                    foreach ($valores as $valorId) {
                        $bindings_rel[] = $novoCargoId;
                        $bindings_rel[] = (int)$valorId;
                    }
                    
                    $stmt_rel = $pdo->prepare($insert_sql);
                    $stmt_rel->execute($bindings_rel);
                }
            }

            $pdo->commit();
            
            $message = "Cargo salvo com sucesso! ID: {$novoCargoId}";
            $message_type = 'success';
            
            // Redireciona para a versão de edição/visualização do cargo salvo
            header("Location: cargos_form.php?id={$novoCargoId}&message=" . urlencode($message) . "&type={$message_type}");
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Erro fatal ao salvar. Verifique se os dados obrigatórios ou chaves estrangeiras estão corretos. Erro: " . $e->getMessage();
            $message_type = 'danger';
            // Recarrega os dados do POST para manter os campos preenchidos
            $cargo = array_merge($cargo, $_POST); 
            $cargoAreas = $relacionamentos['cargos_area']['valores'];
            $cargoId = $cargoIdSubmissao; // Mantém o ID para o campo oculto em caso de falha
        }
    }
}

// Mensagens após redirecionamento
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// --- Fim da lógica PHP ---
// ----------------------------------------------------

// ----------------------------------------------------
// 4. PREPARAÇÃO DO HTML E BOTÕES
// ----------------------------------------------------
// Função auxiliar para pré-seleção
function isSelected($id, $list) {
    return in_array((string)$id, $list) ? 'selected' : '';
}

// Carrega os Níveis Hierárquicos já ordenados (movido para o escopo do HTML)
$niveisOrdenados = [];
foreach (getLookupData($pdo, 'nivel_hierarquico', 'nivelId', 'nivelOrdem') as $id => $ordem) {
    $stmt = $pdo->prepare("SELECT nivelOrdem, nivelDescricao FROM nivel_hierarquico WHERE nivelId = ?");
    $stmt->execute([$id]);
    $nivelData = $stmt->fetch();
    if ($nivelData) {
        $niveisOrdenados[$id] = "{$nivelData['nivelOrdem']}º - " . ($nivelData['nivelDescricao'] ?? 'N/A');
    }
}
arsort($niveisOrdenados); // Ordena de forma decrescente pela chave (Ordem)

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

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-outline-secondary btn-sm" onclick="history.back()">
            <i class="fas fa-arrow-left"></i> Voltar
        </button>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Página Inicial</a></li>
                <li class="breadcrumb-item"><a href="cargos.php">Gerenciamento de Cargos</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $page_title; ?></li>
            </ol>
        </nav>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><?php echo $page_title; ?></h1>
        
        <?php if ($isEditing && $originalId > 0): ?>
             <a href="cargos_form.php?id=<?php echo $originalId; ?>&action=duplicate" 
                class="btn btn-warning btn-sm" 
                title="Criar um novo registro com base neste.">
                <i class="fas fa-copy"></i> Duplicar Cadastro
            </a>
        <?php endif; ?>
    </div>


    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="cargos_form.php">
        <input type="hidden" name="cargoId" value="<?php echo htmlspecialchars($currentFormId); ?>">

        <ul class="nav nav-tabs" id="cargoTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basicas-tab" data-bs-toggle="tab" data-bs-target="#basicas" type="button" role="tab" aria-controls="basicas" aria-selected="true">
                    <i class="fas fa-info-circle"></i> Dados Básicos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="hierarquia-tab" data-bs-toggle="tab" data-bs-target="#hierarquia" type="button" role="tab" aria-controls="hierarquia" aria-selected="false">
                    <i class="fas fa-sitemap"></i> Hierarquia e Áreas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="requisitos-tab" data-bs-toggle="tab" data-bs-target="#requisitos" type="button" role="tab" aria-controls="requisitos" aria-selected="false">
                    <i class="fas fa-list-alt"></i> Requisitos e Riscos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="descricoes-tab" data-bs-toggle="tab" data-bs-target="#descricoes" type="button" role="tab" aria-controls="descricoes" aria-selected="false">
                    <i class="fas fa-book"></i> Descrições Longas
                </button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 p-3 mb-4" id="cargoTabsContent">
            
            <div class="tab-pane fade show active" id="basicas" role="tabpanel" aria-labelledby="basicas-tab">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cargoNome" class="form-label">Nome do Cargo *</label>
                        <input type="text" class="form-control" id="cargoNome" name="cargoNome" value="<?php echo htmlspecialchars($cargo['cargoNome'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cboId" class="form-label">CBO *</label>
                        <select class="form-select" id="cboId" name="cboId" required>
                            <option value="">--- Selecione o CBO ---</option>
                            <?php foreach ($cbos as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['cboId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="cargoResumo" class="form-label">Resumo do Cargo</label>
                    <textarea class="form-control" id="cargoResumo" name="cargoResumo" rows="3"><?php echo htmlspecialchars($cargo['cargoResumo'] ?? ''); ?></textarea>
                    <div class="form-text">Descrição sumária das responsabilidades (máximo 250 caracteres, idealmente).</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="escolaridadeId" class="form-label">Escolaridade Mínima *</label>
                        <select class="form-select" id="escolaridadeId" name="escolaridadeId" required>
                            <option value="">--- Selecione a Escolaridade ---</option>
                            <?php foreach ($escolaridades as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['escolaridadeId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cargoExperiencia" class="form-label">Experiência Necessária</label>
                        <input type="text" class="form-control" id="cargoExperiencia" name="cargoExperiencia" value="<?php echo htmlspecialchars($cargo['cargoExperiencia'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="hierarquia" role="tabpanel" aria-labelledby="hierarquia-tab">
                
                <h4 class="mb-3"><i class="fas fa-level-up-alt"></i> Hierarquia de Comando</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nivelHierarquicoId" class="form-label">Nível Hierárquico</label>
                        <select class="form-select" id="nivelHierarquicoId" name="nivelHierarquicoId">
                            <option value="">--- Selecione o Nível ---</option>
                            <?php foreach ($niveisOrdenados as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['nivelHierarquicoId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><a href="nivel_hierarquico.php" target="_blank">Gerenciar Níveis</a></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="cargoSupervisorId" class="form-label">Reporta-se a (Supervisor)</label>
                        <select class="form-select" id="cargoSupervisorId" name="cargoSupervisorId">
                            <option value="">--- Nível Superior / Nenhum ---</option>
                            <?php foreach ($cargosSupervisor as $id => $nome): 
                                // Evita que o cargo seja seu próprio supervisor
                                if ($isEditing && (int)($originalId) === (int)$id): continue; endif; 
                            ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['cargoSupervisorId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Define a linha de comando para o Organograma.</div>
                    </div>
                </div>

                <hr>

                <h4 class="mb-3"><i class="fas fa-wallet"></i> Faixa Salarial</h4>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="faixaId" class="form-label">Faixa/Nível Salarial</label>
                        <select class="form-select" id="faixaId" name="faixaId">
                            <option value="">--- Não Definido ---</option>
                            <?php foreach ($faixasSalariais as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo (int)($cargo['faixaId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Lembre-se de cadastrar as faixas salariais.</div>
                    </div>
                </div>
                
                <hr>

                <h4 class="mb-3"><i class="fas fa-building"></i> Áreas de Atuação (Relacionamento N:M)</h4>
                <div class="mb-3">
                    <label for="areasAtuacao" class="form-label">Áreas de Atuação (Selecione uma ou mais)</label>
                    <select class="form-select" id="areasAtuacao" name="areasAtuacao[]" multiple size="7">
                        <?php foreach ($areasAtuacao as $id => $nomeHierarquico): ?>
                            <option value="<?php echo $id; ?>" 
                                    <?php echo isSelected($id, $cargoAreas); ?>>
                                <?php echo htmlspecialchars($nomeHierarquico); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Use Ctrl ou Cmd para selecionar múltiplas áreas. <a href="areas_atuacao.php" target="_blank">Gerenciar Áreas</a></div>
                </div>

            </div>

            <div class="tab-pane fade" id="requisitos" role="tabpanel" aria-labelledby="requisitos-tab">
                
                <h4 class="mb-3"><i class="fas fa-lightbulb"></i> Habilidades (N:M)</h4>
                <div class="mb-3">
                    <label for="habilidades" class="form-label">Habilidades Associadas</label>
                    <select class="form-select" id="habilidades" name="habilidades[]" multiple size="5">
                        <?php foreach ($habilidades as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" <?php echo isSelected($id, $cargoHabilidades); ?>>
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hard Skills e Soft Skills necessárias.</div>
                </div>
                
                <h4 class="mb-3 mt-4"><i class="fas fa-user-tag"></i> Características (N:M)</h4>
                <div class="mb-3">
                    <label for="caracteristicas" class="form-label">Características Pessoais Desejáveis</label>
                    <select class="form-select" id="caracteristicas" name="caracteristicas[]" multiple size="5">
                        <?php foreach ($caracteristicas as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" <?php echo isSelected($id, $cargoCaracteristicas); ?>>
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h4 class="mb-3 mt-4"><i class="fas fa-certificate"></i> Cursos (N:M)</h4>
                <div class="mb-3">
                    <label for="cursos" class="form-label">Cursos e Certificações</label>
                    <select class="form-select" id="cursos" name="cursos[]" multiple size="5">
                        <?php foreach ($cursos as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" <?php echo isSelected($id, $cargoCursos); ?>>
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <h4 class="mb-3 mt-4"><i class="fas fa-wrench"></i> Recursos e Riscos (N:M)</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="recursosGrupos" class="form-label">Grupos de Recursos</label>
                        <select class="form-select" id="recursosGrupos" name="recursosGrupos[]" multiple size="5">
                            <?php foreach ($recursosGrupos as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo isSelected($id, $cargoRecursosGrupos); ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="riscos" class="form-label">Riscos de Exposição</label>
                        <select class="form-select" id="riscos" name="riscos[]" multiple size="5">
                            <?php foreach ($riscos as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo isSelected($id, $cargoRiscos); ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

            </div>
            
            <div class="tab-pane fade" id="descricoes" role="tabpanel" aria-labelledby="descricoes-tab">
                
                <h4 class="mb-3"><i class="fas fa-clipboard-list"></i> Responsabilidades Detalhadas</h4>
                <div class="mb-3">
                    <textarea class="form-control" id="cargoResponsabilidades" name="cargoResponsabilidades" rows="5"><?php echo htmlspecialchars($cargo['cargoResponsabilidades'] ?? ''); ?></textarea>
                </div>
                
                <h4 class="mb-3"><i class="fas fa-layer-group"></i> Complexidade do Cargo</h4>
                <div class="mb-3">
                    <textarea class="form-control" id="cargoComplexidade" name="cargoComplexidade" rows="5"><?php echo htmlspecialchars($cargo['cargoComplexidade'] ?? ''); ?></textarea>
                </div>
                
                <h4 class="mb-3"><i class="fas fa-cloud-sun"></i> Condições Gerais</h4>
                <div class="mb-3">
                    <textarea class="form-control" id="cargoCondicoes" name="cargoCondicoes" rows="5"><?php echo htmlspecialchars($cargo['cargoCondicoes'] ?? ''); ?></textarea>
                </div>
                
            </div>
            
        </div>
        
        <button type="submit" class="btn btn-lg btn-success w-100 mt-3">
            <i class="fas fa-check-circle"></i> SALVAR CARGO
        </button>
        <?php if ($isEditing || $isDuplicating): ?>
            <a href="cargos.php" class="btn btn-link text-secondary w-100 mt-2">
                <i class="fas fa-arrow-left"></i> Voltar sem salvar
            </a>
        <?php endif; ?>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var firstTab = document.querySelector('#basicas-tab');
        if (firstTab) {
            new bootstrap.Tab(firstTab).show();
        }
    });
</script>
</body>
</html>