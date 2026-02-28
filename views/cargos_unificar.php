<?php
// Arquivo: views/cargos_unificar.php

require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';

use App\Repository\CargoRepository;
use App\Service\CargoMergeService;

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$cargoRepo = new CargoRepository();
$cargosLista = $cargoRepo->findAllIdsAndNames(); // Usa o método leve que já temos

$mensagem = '';
$tipoMensagem = '';

// Processamento do Formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origemId = (int)($_POST['cargoOrigemId'] ?? 0);
    $destinoId = (int)($_POST['cargoDestinoId'] ?? 0);

    if ($origemId > 0 && $destinoId > 0) {
        try {
            $mergeService = new CargoMergeService();
            $mergeService->mergeCargos($origemId, $destinoId);
            $mensagem = "Cargos unificados com sucesso! O cargo de origem foi apagado.";
            $tipoMensagem = "success";
            
            // Recarrega a lista para tirar o cargo apagado do select
            $cargosLista = $cargoRepo->findAllIdsAndNames(); 
        } catch (\Exception $e) {
            $mensagem = $e->getMessage();
            $tipoMensagem = "danger";
        }
    } else {
        $mensagem = "Por favor, selecione os dois cargos.";
        $tipoMensagem = "warning";
    }
}

$page_title = 'Unificar Cargos';
$root_path = '../';
include '../includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="fas fa-object-group me-2"></i> Ferramenta de Unificação de Cargos</h4>
                </div>
                <div class="card-body p-4">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Atenção:</strong> Esta ação é irreversível. Todas as habilidades, riscos e cursos do Cargo de Origem serão transferidos para o Cargo de Destino. O Cargo de Origem será permanentemente apagado.
                    </div>

                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?php echo $tipoMensagem; ?> alert-dismissible fade show">
                            <?php echo htmlspecialchars($mensagem); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="cargos_unificar.php" id="formUnificar">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-danger">1. Cargo de Origem (A Apagar)</label>
                                <select name="cargoOrigemId" id="cargoOrigemId" class="form-select" required>
                                    <option value="">-- Selecione o cargo duplicado --</option>
                                    <?php foreach ($cargosLista as $c): ?>
                                        <option value="<?php echo $c['cargoId']; ?>"><?php echo htmlspecialchars($c['cargoNome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Este cargo desaparecerá do sistema.</div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-success">2. Cargo de Destino (A Manter)</label>
                                <select name="cargoDestinoId" id="cargoDestinoId" class="form-select" required>
                                    <option value="">-- Selecione o cargo principal --</option>
                                    <?php foreach ($cargosLista as $c): ?>
                                        <option value="<?php echo $c['cargoId']; ?>"><?php echo htmlspecialchars($c['cargoNome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Este cargo receberá todos os dados da origem.</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="cargos.php" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza absoluta que deseja fundir estes cargos? O cargo de origem será apagado.');">
                                <i class="fas fa-compress-arrows-alt me-1"></i> Confirmar Unificação
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Um pequeno script para impedir que o utilizador selecione o mesmo cargo nas duas caixas
document.getElementById('formUnificar').addEventListener('submit', function(e) {
    var origem = document.getElementById('cargoOrigemId').value;
    var destino = document.getElementById('cargoDestinoId').value;
    
    if (origem === destino && origem !== "") {
        e.preventDefault();
        alert('Erro: O Cargo de Origem e o Cargo de Destino não podem ser o mesmo!');
    }
});
</script>

<?php include '../includes/footer.php'; ?>