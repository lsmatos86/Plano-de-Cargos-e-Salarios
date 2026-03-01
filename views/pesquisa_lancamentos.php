<?php
// Arquivo: views/pesquisa_lancamentos.php

require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Repository\PesquisaRepository;
use App\Controller\PesquisaLancamentoController;
use App\Service\AuthService;
use App\Core\Database;

$pdo = Database::getConnection();
$repository = new PesquisaRepository();
$authService = new AuthService();
$controller = new PesquisaLancamentoController($repository, $authService, $pdo);

// AGORA PASSAMOS O $_FILES PARA O CONTROLADOR
$data = $controller->handleRequest($_GET, $_POST, $_FILES, $_SERVER['REQUEST_METHOD']);
extract($data);

$isAberta = $campanha['status'] === 'Aberta';

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0"><i class="fas fa-table text-info"></i> Lançamentos de Mercado</h1>
            <p class="text-muted fw-bold mb-0">Campanha: <?php echo htmlspecialchars($campanha['titulo']); ?></p>
            <span class="badge <?php echo $isAberta ? 'bg-success' : 'bg-danger'; ?>">
                Status: <?php echo $campanha['status']; ?>
            </span>
        </div>
        <div class="d-flex gap-2">
            <?php if ($isAberta): ?>
                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportarCsv">
                    <i class="fas fa-file-csv"></i> Importar Dados (CAGED / CSV)
                </button>
            <?php endif; ?>
            <a href="pesquisa_salarial.php" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; // Renderiza HTML do aviso de importação ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-info h-100">
                <div class="card-header bg-info text-white fw-bold">
                    <i class="fas fa-keyboard"></i> Digitação Manual
                </div>
                <div class="card-body bg-light">
                    <?php if (!$isAberta): ?>
                        <div class="alert alert-warning">Esta campanha está encerrada. Não é possível fazer novos lançamentos.</div>
                    <?php else: ?>
                        <form method="POST" action="pesquisa_lancamentos.php?id=<?php echo $campanha['campanhaId']; ?>">
                            <input type="hidden" name="action" value="novo_lancamento">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">1. Empresa Consultada *</label>
                                <select class="form-select searchable-select" name="empresaId" required>
                                    <option value="">Selecione a empresa...</option>
                                    <?php foreach ($empresas as $e): ?>
                                        <option value="<?php echo $e['empresaId']; ?>"><?php echo htmlspecialchars($e['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">2. Equivalência (CBO) *</label>
                                <select class="form-select searchable-select" name="cboId" required>
                                    <option value="">Selecione o CBO equivalente...</option>
                                    <?php foreach ($cbos as $id => $nome): ?>
                                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($nome); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nome do Cargo na Empresa</label>
                                <input type="text" class="form-control" name="cargo_nome_mercado" placeholder="Ex: Auxiliar de Limpeza I" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-success fw-bold">Salário Base Mensal (R$) *</label>
                                <input type="text" class="form-control valor-monetario fs-5" name="salario_base" placeholder="0,00" required>
                            </div>

                            <button type="submit" class="btn btn-info text-white w-100 fw-bold">
                                <i class="fas fa-check"></i> Registrar Salário
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            
            <div class="card shadow-sm border-success mb-4">
                <div class="card-header bg-success text-white fw-bold d-flex justify-content-between">
                    <span><i class="fas fa-chart-line"></i> Análise Estatística (Por CBO)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-striped table-hover mb-0 text-center align-middle">
                            <thead class="table-dark" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="text-start ps-3">Família / CBO</th>
                                    <th title="Quantidade de salários analisados">Amostras</th>
                                    <th>Mínimo</th>
                                    <th>Média</th>
                                    <th class="text-warning fw-bold fs-6">Mediana (Mercado)</th>
                                    <th>Máximo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($estatisticas)): ?>
                                    <tr><td colspan="6" class="py-3 text-muted">Aguardando lançamentos para calcular estatísticas...</td></tr>
                                <?php else: ?>
                                    <?php foreach ($estatisticas as $cboId => $est): ?>
                                        <tr>
                                            <td class="text-start ps-3 fw-bold small text-primary"><?php echo htmlspecialchars($est['titulo']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $est['amostras']; ?></span></td>
                                            <td class="small">R$ <?php echo number_format($est['minimo'], 2, ',', '.'); ?></td>
                                            <td class="small">R$ <?php echo number_format($est['media'], 2, ',', '.'); ?></td>
                                            <td class="text-warning fw-bold bg-dark bg-opacity-10">R$ <?php echo number_format($est['mediana'], 2, ',', '.'); ?></td>
                                            <td class="small">R$ <?php echo number_format($est['maximo'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white fw-bold">
                    <i class="fas fa-list"></i> Salários Lançados (Dados Brutos)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light" style="position: sticky; top: 0;">
                                <tr>
                                    <th class="ps-3">Empresa / Fonte</th>
                                    <th>Cargo Original</th>
                                    <th>Equivalência (CBO)</th>
                                    <th class="text-end">Salário Base</th>
                                    <?php if ($isAberta): ?><th class="text-center">Ação</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($lancamentos)): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum salário lançado.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($lancamentos as $l): ?>
                                        <tr>
                                            <td class="ps-3 text-muted fw-bold"><?php echo htmlspecialchars($l['empresa_nome']); ?></td>
                                            <td><?php echo htmlspecialchars($l['cargo_nome_mercado']); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($l['cboTituloOficial']); ?></td>
                                            <td class="text-end text-success fw-bold">R$ <?php echo number_format($l['salario_base'], 2, ',', '.'); ?></td>
                                            
                                            <?php if ($isAberta): ?>
                                            <td class="text-center">
                                                <form method="POST" action="pesquisa_lancamentos.php?id=<?php echo $campanha['campanhaId']; ?>">
                                                    <input type="hidden" name="action" value="excluir_lancamento">
                                                    <input type="hidden" name="valorId" value="<?php echo $l['valorId']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalImportarCsv" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="pesquisa_lancamentos.php?id=<?php echo $campanha['campanhaId']; ?>" enctype="multipart/form-data" class="modal-content border-primary">
            <input type="hidden" name="action" value="importar_csv">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-csv"></i> Assistente de Importação e Limpeza (CAGED)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    <i class="fas fa-info-circle"></i> O arquivo deve estar no formato CSV oficial do CAGED. O sistema cruzará automaticamente as colunas <strong>cbo_2002</strong> e <strong>salario_mensal</strong> com a sua base de dados.
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">1. Atribuir dados a qual Empresa/Fonte? *</label>
                    <select class="form-select searchable-select" name="empresaIdCsv" required style="width: 100%;">
                        <option value="">Selecione...</option>
                        <?php foreach ($empresas as $e): ?>
                            <option value="<?php echo $e['empresaId']; ?>"><?php echo htmlspecialchars($e['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Dica: Crie uma empresa chamada "Dados CAGED - Ministério do Trabalho" na tela anterior.</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-danger">Filtro: Cortar Salários Mínimos (R$)</label>
                        <input type="number" step="0.01" class="form-control" name="salario_minimo" value="1412.00">
                        <div class="form-text">Qualquer salário abaixo deste valor (carga horária parcial ou jovem aprendiz) será descartado.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-danger">Filtro: Cortar Salários Extremos (R$)</label>
                        <input type="number" step="0.01" class="form-control" name="salario_maximo" placeholder="Ex: 20000.00">
                        <div class="form-text">Qualquer salário acima deste teto (super faturado) será descartado para não inflar a média.</div>
                    </div>
                </div>
                 <hr>
                <div class="mb-3">
                    <label class="form-label fw-bold text-primary"><i class="fas fa-sync-alt"></i> Atualização Histórica</label>
                    <p class="small text-muted mb-2">Se o arquivo tiver salários de anos passados, como o sistema deve trazer o valor para a realidade atual?</p>
                    
                    <select class="form-select border-primary" name="indice_correcao" required>
                        <option value="nenhum">Manter valor original (Não reajustar)</option>
                        <option value="sm">Reajustar pela evolução do Salário Mínimo</option>
                        <option value="inpc">Reajustar pela Inflação Acumulada (INPC)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Selecione o arquivo .CSV do seu computador *</label>
                    <input type="file" class="form-control" name="arquivo_csv" accept=".csv" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-cloud-upload-alt"></i> Processar e Importar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // 1. Inicializa os selects da tela principal (SEM prender a nenhum modal)
    $('select[name="empresaId"], select[name="cboId"]').select2({ 
        theme: "bootstrap-5", 
        width: '100%' 
    });
    
    // 2. Inicializa APENAS o select do CSV quando o Modal abrir, prendendo-o lá dentro
    $('#modalImportarCsv').on('shown.bs.modal', function () {
        $('select[name="empresaIdCsv"]').select2({ 
            theme: "bootstrap-5", 
            width: '100%',
            dropdownParent: $('#modalImportarCsv') 
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>