<?php
// Arquivo: relatorios/tabela_salarial_gerencial.php

require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';
use App\Core\Database;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteção: Apenas quem tem permissão de ver relatórios ou gerir cargos
// $authService = new \App\Service\AuthService();
// $authService->checkAndFail('relatorios:view'); 

$pdo = Database::getConnection();

// --- BUSCAR DADOS COMPLETOS (CARGOS + FAIXAS + PISO + NÍVEIS) ---
$sql = "
    SELECT 
        c.cargoId, c.cargoNome, 
        n.nivelDescricao, n.nivelOrdem,
        f.faixaNivel, f.step_a, f.step_b, f.step_c, f.step_d, f.step_e,
        c.tem_piso_salarial, c.piso_valor, c.piso_lei_numero, c.piso_data_base
    FROM cargos c
    LEFT JOIN faixas_salariais f ON c.faixaId = f.faixaId
    LEFT JOIN nivel_hierarquico n ON c.nivelHierarquicoId = n.nivelId
    ORDER BY n.nivelOrdem ASC, c.cargoNome ASC
";
$stmt = $pdo->query($sql);
$cargosLista = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Relatório Gerencial - Tabela Salarial";
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<style>
    .dt-buttons .btn { margin-right: 5px; margin-bottom: 15px; }
    .table-gerencial th { background-color: #2c3e50; color: white; vertical-align: middle; text-align: center; }
    .table-gerencial td { vertical-align: middle; }
    .val-piso { background-color: #fff3cd !important; font-weight: bold; color: #856404; }
    .val-matriz { color: #198754; font-weight: 500; }
</style>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0"><i class="fas fa-chart-bar text-primary"></i> Relatório de Tabela Salarial</h1>
            <p class="text-muted">Visão global de remuneração da Itacitrus (Matriz Padrão vs. Pisos Legais)</p>
        </div>
        <div>
            <a href="../views/faixas_salariais.php" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-cog"></i> Gerenciar Matriz
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelaGerencial" class="table table-hover table-bordered table-gerencial w-100 align-middle">
                    <thead>
                        <tr>
                            <th class="text-start">Cargo</th>
                            <th>Nível Hierárquico</th>
                            <th>Enquadramento</th>
                            <th>Base / Step A <br><small>(ou Piso Legal)</small></th>
                            <th>Step B <br><small>12m</small></th>
                            <th>Step C <br><small>24m</small></th>
                            <th>Step D <br><small>36m</small></th>
                            <th>Step E <br><small>Sênior</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cargosLista as $c): ?>
                            <?php 
                                $temPiso = (int)$c['tem_piso_salarial'] === 1;
                                $nomeCargo = htmlspecialchars($c['cargoNome']);
                                $nivel = $c['nivelDescricao'] ? htmlspecialchars($c['nivelDescricao']) : 'Não Definido';
                            ?>
                            <tr>
                                <td class="text-start fw-bold">
                                    <a href="../views/cargos_form.php?id=<?php echo $c['cargoId']; ?>" class="text-decoration-none text-dark" target="_blank">
                                        <?php echo $nomeCargo; ?>
                                    </a>
                                </td>
                                <td class="text-center"><?php echo $nivel; ?></td>
                                
                                <?php if ($temPiso): ?>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark border border-warning"><i class="fas fa-balance-scale"></i> Piso Legal</span><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($c['piso_lei_numero'] ?: 'Sem Lei Info'); ?></small>
                                    </td>
                                    <td class="text-center val-piso">
                                        R$ <?php echo number_format($c['piso_valor'] ?? 0, 2, ',', '.'); ?>
                                    </td>
                                    <td class="text-center text-muted" colspan="4">
                                        <em>Valor fixado por Lei/Acordo. (Não usa steps da matriz)</em>
                                    </td>
                                <?php else: ?>
                                    <td class="text-center">
                                        <?php if (!empty($c['faixaNivel'])): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($c['faixaNivel']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Sem Faixa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center val-matriz">
                                        R$ <?php echo number_format($c['step_a'] ?? 0, 2, ',', '.'); ?>
                                    </td>
                                    <td class="text-center">R$ <?php echo number_format($c['step_b'] ?? 0, 2, ',', '.'); ?></td>
                                    <td class="text-center">R$ <?php echo number_format($c['step_c'] ?? 0, 2, ',', '.'); ?></td>
                                    <td class="text-center">R$ <?php echo number_format($c['step_d'] ?? 0, 2, ',', '.'); ?></td>
                                    <td class="text-center">R$ <?php echo number_format($c['step_e'] ?? 0, 2, ',', '.'); ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#tabelaGerencial').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json',
        },
        pageLength: 25,
        order: [], // Mantém a ordem do SQL (Nível Hierárquico)
        dom: '<"row"<"col-md-6"B><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Exportar Excel',
                className: 'btn btn-success btn-sm',
                title: 'Itacitrus - Tabela Salarial Gerencial',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf"></i> Exportar PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Itacitrus - Tabela Salarial Gerencial',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn btn-secondary btn-sm',
                title: 'Itacitrus - Tabela Salarial'
            }
        ]
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>