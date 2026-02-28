<?php
// Arquivo: views/faixas_salariais.php

require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';
use App\Core\Database;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = Database::getConnection();
$mensagem = '';
$tipo_mensagem = 'success';

// --- PROCESSAMENTO DO REAJUSTE GLOBAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reajuste_global') {
    $percentual = floatval(str_replace(',', '.', $_POST['percentual_reajuste'] ?? 0));
    $tipo = $_POST['tipo_reajuste']; 
    $dataVigencia = $_POST['data_vigencia'];
    $numeroLei = trim($_POST['numero_lei'] ?? '');

    if ($percentual > 0) {
        $fator = 1 + ($percentual / 100);
        try {
            $pdo->beginTransaction();
            
            // 1. Atualiza todas as faixas salariais multiplicando pelo fator
            $pdo->exec("UPDATE faixas_salariais SET 
                        step_a = ROUND(step_a * $fator, 2),
                        step_b = ROUND(step_b * $fator, 2),
                        step_c = ROUND(step_c * $fator, 2),
                        step_d = ROUND(step_d * $fator, 2),
                        step_e = ROUND(step_e * $fator, 2)");
            
            // 2. Regista no histórico de reajustes
            $stmt = $pdo->prepare("INSERT INTO reajustes_salariais (tipo_reajuste, percentual, data_vigencia, numero_lei_convencao) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tipo, $percentual, $dataVigencia, $numeroLei]);
            
            $pdo->commit();
            $mensagem = "Reajuste de " . number_format($percentual, 2, ',', '.') . "% aplicado com sucesso a todas as faixas e steps!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensagem = "Erro ao aplicar reajuste: " . $e->getMessage();
            $tipo_mensagem = 'danger';
        }
    } else {
        $mensagem = "O percentual de reajuste deve ser maior que zero.";
        $tipo_mensagem = 'warning';
    }
}

// --- PROCESSAMENTO DA EDIÇÃO DE FAIXA INDIVIDUAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'salvar_faixa') {
    $faixaId = (int)$_POST['faixaId'];
    $faixaNivel = trim($_POST['faixaNivel']); // <--- CORRIGIDO AQUI
    // Converte de formato BR (1.500,00) para formato SQL (1500.00)
    $cleanDecimal = function($val) { return floatval(str_replace(',', '.', str_replace('.', '', $val))); };
    
    $step_a = $cleanDecimal($_POST['step_a']);
    $step_b = $cleanDecimal($_POST['step_b']);
    $step_c = $cleanDecimal($_POST['step_c']);
    $step_d = $cleanDecimal($_POST['step_d']);
    $step_e = $cleanDecimal($_POST['step_e']);

    if ($faixaId > 0) {
        $stmt = $pdo->prepare("UPDATE faixas_salariais SET faixaNivel = ?, step_a = ?, step_b = ?, step_c = ?, step_d = ?, step_e = ? WHERE faixaId = ?"); // <--- CORRIGIDO AQUI
        $stmt->execute([$faixaNivel, $step_a, $step_b, $step_c, $step_d, $step_e, $faixaId]);
        $mensagem = "Faixa Salarial atualizada com sucesso.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO faixas_salariais (faixaNivel, step_a, step_b, step_c, step_d, step_e) VALUES (?, ?, ?, ?, ?, ?)"); // <--- CORRIGIDO AQUI
        $stmt->execute([$faixaNivel, $step_a, $step_b, $step_c, $step_d, $step_e]);
        $mensagem = "Nova Faixa Salarial criada com sucesso.";
    }
}

// --- BUSCAR DADOS PARA EXIBIÇÃO ---
$stmtFaixas = $pdo->query("SELECT * FROM faixas_salariais ORDER BY faixaNivel ASC"); // <--- CORRIGIDO AQUI
$faixas = $stmtFaixas->fetchAll(PDO::FETCH_ASSOC);

$stmtCargos = $pdo->query("SELECT cargoId, cargoNome, f.faixaNivel, f.step_a, c.tem_piso_salarial, c.piso_valor, c.piso_lei_numero 
                           FROM cargos c 
                           LEFT JOIN faixas_salariais f ON c.faixaId = f.faixaId 
                           ORDER BY cargoNome ASC"); // <--- CORRIGIDO AQUI
$cargosLista = $stmtCargos->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Matriz Salarial";
require_once '../includes/header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-money-check-alt text-success"></i> Matriz Salarial (Faixas e Steps)</h1>
        <div>
            <button class="btn btn-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#modalReajusteGlobal">
                <i class="fas fa-chart-line"></i> Aplicar Reajuste (Dissídio/Mínimo)
            </button>
            <button class="btn btn-primary shadow-sm ms-2" onclick="abrirModalFaixa()">
                <i class="fas fa-plus"></i> Nova Faixa
            </button>
        </div>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($mensagem); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4 border-info shadow-sm">
        <div class="card-header bg-info text-white fw-bold">
            <i class="fas fa-search"></i> Consultar Remuneração por Cargo
        </div>
        <div class="card-body bg-light">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <select class="form-select" id="buscaCargoSelect" data-placeholder="Digite o nome do cargo para pesquisar...">
                        <option value=""></option>
                        <?php foreach ($cargosLista as $c): ?>
                            <option value="<?php echo $c['cargoId']; ?>" 
                                    data-faixa="<?php echo htmlspecialchars($c['faixaNivel'] ?? 'Sem Faixa'); ?>"
                                    data-base="<?php echo number_format($c['step_a'] ?? 0, 2, ',', '.'); ?>"
                                    data-tempiso="<?php echo $c['tem_piso_salarial']; ?>"
                                    data-pisovalor="<?php echo number_format($c['piso_valor'] ?? 0, 2, ',', '.'); ?>"
                                    data-pisolei="<?php echo htmlspecialchars($c['piso_lei_numero'] ?? ''); ?>">
                                <?php echo htmlspecialchars($c['cargoNome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6" id="resultadoBuscaCargo" style="display: none;">
                    <div class="p-3 border rounded bg-white" id="infoCargoPadrão">
                        <span class="text-muted small">Enquadramento Geral:</span><br>
                        <strong>Faixa:</strong> <span id="resFaixa" class="text-primary"></span> &nbsp;|&nbsp; 
                        <strong>Step A (Base):</strong> R$ <span id="resBase" class="text-success fw-bold"></span>
                    </div>
                    <div class="p-3 border rounded bg-warning bg-opacity-10 mt-2 border-warning" id="infoCargoPiso" style="display: none;">
                        <span class="text-danger fw-bold"><i class="fas fa-balance-scale"></i> ATENÇÃO: Este cargo possui Piso Salarial Legal isolado da matriz.</span><br>
                        <strong>Valor do Piso:</strong> R$ <span id="resPisoValor" class="text-dark fw-bold"></span> &nbsp;|&nbsp; 
                        <strong>Lei/Convenção:</strong> <span id="resPisoLei"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-start ps-3">Faixa / Nível</th>
                            <th>Step A <br><small class="fw-normal">(Base / 0 meses)</small></th>
                            <th>Step B <br><small class="fw-normal">(12 meses)</small></th>
                            <th>Step C <br><small class="fw-normal">(24 meses)</small></th>
                            <th>Step D <br><small class="fw-normal">(36 meses)</small></th>
                            <th>Step E <br><small class="fw-normal">(Sénior)</small></th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($faixas)): ?>
                            <tr><td colspan="7" class="py-4 text-muted">Nenhuma faixa salarial configurada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($faixas as $f): ?>
                                <tr>
                                    <td class="text-start ps-3 fw-bold text-primary"><?php echo htmlspecialchars($f['faixaNivel']); ?></td> <td class="text-success fw-bold">R$ <?php echo number_format($f['step_a'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($f['step_b'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($f['step_c'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($f['step_d'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($f['step_e'], 2, ',', '.'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick='editarFaixa(<?php echo json_encode($f); ?>)'>
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoFaixa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="faixas_salariais.php" class="modal-content">
            <input type="hidden" name="action" value="salvar_faixa">
            <input type="hidden" name="faixaId" id="editFaixaId" value="0">
            
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-layer-group"></i> Configurar Faixa Salarial e Steps</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label fw-bold">Nível da Faixa (Ex: Faixa 01 - Operacional)</label>
                    <input type="text" class="form-control" name="faixaNivel" id="editFaixaNivel" required> </div>
                
                <h6 class="border-bottom pb-2 mb-3 text-secondary">Degraus Salariais (Steps)</h6>
                <div class="alert alert-light border small">
                    <i class="fas fa-lightbulb text-warning"></i> <strong>Dica de RH:</strong> Digite o valor do <strong>Step A</strong> e use os botões azuis abaixo para calcular os restantes Steps automaticamente (+5% ou +10% de progressão).
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-success fw-bold">Step A (Base Inicial)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control valor-monetario" name="step_a" id="editStepA" required>
                        </div>
                    </div>
                    <div class="col-md-8 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm mb-1" onclick="autoCalcularSteps(1.05)">Gerar +5% por Step</button>
                        <button type="button" class="btn btn-outline-primary btn-sm mb-1" onclick="autoCalcularSteps(1.10)">Gerar +10% por Step</button>
                    </div>
                </div>
                
                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label small">Step B</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control valor-monetario" name="step_b" id="editStepB">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Step C</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control valor-monetario" name="step_c" id="editStepC">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Step D</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control valor-monetario" name="step_d" id="editStepD">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Step E</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control valor-monetario" name="step_e" id="editStepE">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar Matriz</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalReajusteGlobal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="faixas_salariais.php" class="modal-content border-warning">
            <input type="hidden" name="action" value="reajuste_global">
            
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-chart-line"></i> Reajuste Salarial em Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger small">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Atenção:</strong> Esta ação aplicará o aumento percentual a <strong>todas</strong> as Faixas e Steps da Matriz Salarial atual. Esta ação não afeta os Cargos com Piso Salarial isolado.
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Motivo / Tipo de Reajuste</label>
                    <select class="form-select" name="tipo_reajuste" required>
                        <option value="dissidio_sindical">Acordo / Dissídio Sindical</option>
                        <option value="salario_minimo">Aumento do Salário Mínimo Nacional</option>
                        <option value="merito_geral">Reajuste / Correção Interna</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold">Percentual de Aumento</label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" name="percentual_reajuste" placeholder="Ex: 5,50" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Data de Vigência</label>
                        <input type="date" class="form-control" name="data_vigencia" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Nº da Lei ou Convenção Coletiva (Opcional)</label>
                    <input type="text" class="form-control" name="numero_lei" placeholder="Ex: CCT 2026/2027">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning fw-bold" onclick="return confirm('Tem certeza que deseja reajustar TODA a matriz salarial?');">
                    <i class="fas fa-check"></i> Aplicar Reajuste a Tudo
                </button>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Converte número do JS para formato R$ com vírgula
    function formatarDinheiro(valor) {
        return valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Converte string "1.500,00" para float do JS (1500.00)
    function parseDinheiro(stringValor) {
        if(!stringValor) return 0;
        return parseFloat(stringValor.toString().replace(/\./g, '').replace(',', '.'));
    }

    // Lógica para calcular +5% ou +10% rapidamente para poupar tempo de digitação
    function autoCalcularSteps(multiplicador) {
        let stepA = parseDinheiro($('#editStepA').val());
        if(stepA > 0) {
            $('#editStepB').val(formatarDinheiro(stepA * Math.pow(multiplicador, 1)));
            $('#editStepC').val(formatarDinheiro(stepA * Math.pow(multiplicador, 2)));
            $('#editStepD').val(formatarDinheiro(stepA * Math.pow(multiplicador, 3)));
            $('#editStepE').val(formatarDinheiro(stepA * Math.pow(multiplicador, 4)));
        } else {
            alert('Por favor, introduza um valor no Step A primeiro.');
        }
    }

    // Abre o modal de edição de Faixa e preenche os campos
    function abrirModalFaixa() {
        $('#editFaixaId').val('0');
        $('#editFaixaNivel, #editStepA, #editStepB, #editStepC, #editStepD, #editStepE').val(''); // <--- CORRIGIDO AQUI
        new bootstrap.Modal(document.getElementById('modalEdicaoFaixa')).show();
    }

    function editarFaixa(faixa) {
        $('#editFaixaId').val(faixa.faixaId);
        $('#editFaixaNivel').val(faixa.faixaNivel); // <--- CORRIGIDO AQUI
        $('#editStepA').val(formatarDinheiro(parseFloat(faixa.step_a)));
        $('#editStepB').val(formatarDinheiro(parseFloat(faixa.step_b)));
        $('#editStepC').val(formatarDinheiro(parseFloat(faixa.step_c)));
        $('#editStepD').val(formatarDinheiro(parseFloat(faixa.step_d)));
        $('#editStepE').val(formatarDinheiro(parseFloat(faixa.step_e)));
        new bootstrap.Modal(document.getElementById('modalEdicaoFaixa')).show();
    }

    $(document).ready(function() {
        // Inicializa a barra de pesquisa
        $('#buscaCargoSelect').select2({
            theme: "bootstrap-5",
            width: '100%',
            allowClear: true
        });

        // Quando selecionar um cargo na barra de pesquisa
        $('#buscaCargoSelect').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            
            if (selectedOption.val() === "") {
                $('#resultadoBuscaCargo').hide();
                return;
            }

            var faixa = selectedOption.data('faixa');
            var base = selectedOption.data('base');
            var temPiso = parseInt(selectedOption.data('tempiso'));
            var pisoValor = selectedOption.data('pisovalor');
            var pisoLei = selectedOption.data('pisolei');

            $('#resFaixa').text(faixa);
            $('#resBase').text(base);
            
            if (temPiso === 1) {
                $('#resPisoValor').text(pisoValor);
                $('#resPisoLei').text(pisoLei || 'Não informada');
                $('#infoCargoPiso').show();
            } else {
                $('#infoCargoPiso').hide();
            }

            $('#resultadoBuscaCargo').fadeIn();
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>