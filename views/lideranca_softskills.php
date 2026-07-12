<?php
// Arquivo: views/lideranca_softskills.php

require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';

use App\Core\Database;

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
$authService->checkAndFail('cargos:manage', '../index.php?error=Acesso+negado');

$page_title = 'Softskills de Liderança (Configuração)';
$root_path = '../';
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Softskills de Liderança' => null,
];
$page_scripts = [];

$pdo = Database::getConnection();
$message = '';
$message_type = '';

// -------------------------------------------------------
// LÓGICA DE CRUD
// -------------------------------------------------------
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action      = $_POST['action'] ?? '';
        $tipoId      = (int)($_POST['tipoId'] ?? 0);
        $habilidadeId = (int)($_POST['habilidadeId'] ?? 0);

        if ($action === 'insert') {
            if ($tipoId <= 0 || $habilidadeId <= 0) {
                throw new Exception('Selecione um tipo hierárquico e uma habilidade válidos.');
            }
            $stmt = $pdo->prepare(
                'INSERT INTO leadership_softskills_config ("tipoId", "habilidadeId")
                 VALUES (?, ?)
                 ON CONFLICT ("tipoId", "habilidadeId") DO NOTHING'
            );
            $stmt->execute([$tipoId, $habilidadeId]);
            if ($stmt->rowCount() === 0) {
                $message = 'Essa associação já existe.';
                $message_type = 'warning';
            } else {
                $message = 'Associação adicionada com sucesso!';
                $message_type = 'success';
            }
        }

        if ($action === 'delete') {
            $configId = (int)($_POST['configId'] ?? 0);
            if ($configId <= 0) {
                throw new Exception('ID inválido para exclusão.');
            }
            $stmt = $pdo->prepare('DELETE FROM leadership_softskills_config WHERE "configId" = ?');
            $stmt->execute([$configId]);
            $message = 'Associação removida com sucesso!';
            $message_type = 'success';
        }
    }
} catch (Exception $e) {
    $message = $e->getMessage();
    $message_type = 'danger';
}

// Mensagem de redirecionamento
if (empty($message) && isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

// -------------------------------------------------------
// LEITURA DOS DADOS
// -------------------------------------------------------

// Configurações existentes (com joins para exibir nomes)
$configRows = $pdo->query(
    'SELECT lsc."configId",
            t."tipoId", t."tipoNome",
            h."habilidadeId", h."habilidadeNome", h."habilidadeTipo"
     FROM leadership_softskills_config lsc
     JOIN tipo_hierarquia t ON t."tipoId" = lsc."tipoId"
     JOIN habilidades h     ON h."habilidadeId" = lsc."habilidadeId"
     ORDER BY t."tipoNome", h."habilidadeNome"'
)->fetchAll(PDO::FETCH_ASSOC);

// Tipos hierárquicos disponíveis
$tipos = $pdo->query(
    'SELECT "tipoId", "tipoNome" FROM tipo_hierarquia ORDER BY "tipoNome"'
)->fetchAll(PDO::FETCH_ASSOC);

// Softskills disponíveis (apenas tipo Softskill)
$softskills = $pdo->query(
    'SELECT "habilidadeId", "habilidadeNome"
     FROM habilidades
     WHERE "habilidadeTipo" = \'Softskill\'
     ORDER BY "habilidadeNome"'
)->fetchAll(PDO::FETCH_ASSOC);

// Agrupa as configs por tipo para a tabela
$configByTipo = [];
foreach ($configRows as $row) {
    $configByTipo[$row['tipoNome']][] = $row;
}

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-1"><i class="fas fa-star me-2 text-warning"></i>Softskills de Liderança</h2>
            <p class="text-muted mb-4">
                Defina quais softskills são herdadas automaticamente ao salvar um cargo, com base no tipo hierárquico do nível selecionado.
                As alterações aqui têm efeito imediato na próxima vez que um cargo for salvo.
            </p>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- FORMULÁRIO DE ADIÇÃO -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-plus-circle me-1"></i> Adicionar Associação
                        </div>
                        <div class="card-body">
                            <?php if (empty($tipos)): ?>
                                <div class="alert alert-warning mb-0">
                                    Nenhum tipo hierárquico cadastrado. Cadastre tipos em
                                    <a href="tipo_hierarquia.php">Tipos de Hierarquia</a> primeiro.
                                </div>
                            <?php elseif (empty($softskills)): ?>
                                <div class="alert alert-warning mb-0">
                                    Nenhuma softskill cadastrada. Cadastre habilidades do tipo <em>Softskill</em> em
                                    <a href="habilidades.php">Habilidades</a> primeiro.
                                </div>
                            <?php else: ?>
                                <form method="POST" action="lideranca_softskills.php">
                                    <input type="hidden" name="action" value="insert">

                                    <div class="mb-3">
                                        <label for="tipoId" class="form-label fw-semibold">Tipo Hierárquico</label>
                                        <select name="tipoId" id="tipoId" class="form-select" required>
                                            <option value="">-- Selecione --</option>
                                            <?php foreach ($tipos as $tipo): ?>
                                                <option value="<?php echo (int)$tipo['tipoId']; ?>">
                                                    <?php echo htmlspecialchars($tipo['tipoNome']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="habilidadeId" class="form-label fw-semibold">Softskill</label>
                                        <select name="habilidadeId" id="habilidadeId" class="form-select" required>
                                            <option value="">-- Selecione --</option>
                                            <?php foreach ($softskills as $sk): ?>
                                                <option value="<?php echo (int)$sk['habilidadeId']; ?>">
                                                    <?php echo htmlspecialchars($sk['habilidadeNome']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-1"></i> Adicionar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- TABELA DE CONFIGURAÇÕES ATUAIS -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light fw-semibold">
                            <i class="fas fa-list me-1"></i> Configurações Atuais
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($configRows)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Nenhuma softskill de liderança configurada ainda.
                                    Use o formulário ao lado para adicionar associações.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0 align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Tipo Hierárquico</th>
                                                <th>Softskill</th>
                                                <th class="text-center" style="width: 90px;">Remover</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($configByTipo as $tipoNome => $rows): ?>
                                                <?php foreach ($rows as $i => $row): ?>
                                                    <tr>
                                                        <?php if ($i === 0): ?>
                                                            <td rowspan="<?php echo count($rows); ?>" class="fw-semibold align-middle">
                                                                <i class="fas fa-layer-group me-1 text-primary"></i>
                                                                <?php echo htmlspecialchars($tipoNome); ?>
                                                            </td>
                                                        <?php endif; ?>
                                                        <td>
                                                            <span class="badge bg-secondary me-1">Softskill</span>
                                                            <?php echo htmlspecialchars($row['habilidadeNome']); ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <form method="POST" action="lideranca_softskills.php"
                                                                  onsubmit="return confirm('Remover esta associação?');">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="configId" value="<?php echo (int)$row['configId']; ?>">
                                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Remover">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
