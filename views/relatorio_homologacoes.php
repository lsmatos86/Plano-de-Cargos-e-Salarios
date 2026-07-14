<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';

use App\Repository\AuditRepository;
use App\Core\Database;

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

if ((int)($_SESSION['usuario_role'] ?? 0) !== 1 && !possuiPermissao('auditoria_visualizar')) {
    http_response_code(403);
    exit('Acesso negado.');
}

$acoesHomologacao = [
    'CARGO_REVISAO' => 'Revisão concluída',
    'CARGO_APROVACAO' => 'Aprovação e bloqueio',
    'CARGO_DESBLOQUEIO' => 'Desbloqueio autorizado',
    'CARGO_ALTERACAO_APROVADA' => 'Alteração após aprovação',
];

$params = [
    'term' => trim($_GET['term'] ?? ''),
    'acao' => array_key_exists($_GET['acao'] ?? '', $acoesHomologacao) ? $_GET['acao'] : '',
    'nomeTabela' => 'cargos_homologacao',
    'page' => max(1, (int)($_GET['page'] ?? 1)),
    'limit' => isset($_GET['export']) ? 5000 : 25,
];

$result = (new AuditRepository())->findAllPaginated($params);
$registros = $result['data'];
$cargoNomes = [];
$cargoIds = array_values(array_unique(array_filter(array_map('intval', array_column($registros, 'idRegistro')))));
if ($cargoIds) {
    $placeholders = implode(',', array_fill(0, count($cargoIds), '?'));
    $stmtCargos = Database::getConnection()->prepare(
        "SELECT \"cargoId\", \"cargoNome\" FROM cargos WHERE \"cargoId\" IN ({$placeholders})"
    );
    $stmtCargos->execute($cargoIds);
    $cargoNomes = $stmtCargos->fetchAll(PDO::FETCH_KEY_PAIR);
}

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="relatorio_homologacoes_' . date('Y-m-d_H-i') . '.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Data/Hora', 'Categoria', 'Cargo ID', 'Usuário', 'Justificativa/Detalhes'], ';');
    foreach ($registros as $row) {
        $detalhes = json_decode($row['dadosJson'] ?? '', true) ?: [];
        fputcsv($out, [
            $row['dataHora'],
            $acoesHomologacao[$row['acao']] ?? $row['acao'],
            '#' . $row['idRegistro'] . ' - ' . ($cargoNomes[(int)$row['idRegistro']] ?? 'Cargo não encontrado'),
            $row['nomeUsuario'],
            $detalhes['justificativa'] ?? json_encode($detalhes, JSON_UNESCAPED_UNICODE),
        ], ';');
    }
    fclose($out);
    exit;
}

$page_title = 'Relatório de Homologações de Cargos';
$root_path = '../';
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Relatórios' => null,
    $page_title => null,
];
include '../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-shield-alt text-success me-2"></i><?php echo htmlspecialchars($page_title); ?></h1>
        <p class="text-muted mb-0">Revisões, aprovações, desbloqueios administrativos e alterações justificadas.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-success" href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv', 'page' => 1])); ?>">
            <i class="fas fa-file-csv me-1"></i> Exportar CSV
        </a>
        <button class="btn btn-outline-secondary" type="button" onclick="window.print()"><i class="fas fa-print me-1"></i> Imprimir</button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <select class="form-select" name="acao">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($acoesHomologacao as $acao => $rotulo): ?>
                        <option value="<?php echo $acao; ?>" <?php echo $params['acao'] === $acao ? 'selected' : ''; ?>><?php echo htmlspecialchars($rotulo); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <input class="form-control" type="search" name="term" value="<?php echo htmlspecialchars($params['term']); ?>" placeholder="Buscar por usuário, cargo ID ou justificativa...">
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i> Filtrar</button></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Data/Hora</th><th>Categoria</th><th>Cargo</th><th>Usuário</th><th>Justificativa/Detalhes</th></tr></thead>
            <tbody>
            <?php if (!$registros): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Nenhum evento de homologação encontrado.</td></tr>
            <?php else: foreach ($registros as $row): $detalhes = json_decode($row['dadosJson'] ?? '', true) ?: []; ?>
                <tr>
                    <td class="text-nowrap"><?php echo date('d/m/Y H:i:s', strtotime($row['dataHora'])); ?></td>
                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($acoesHomologacao[$row['acao']] ?? $row['acao']); ?></span></td>
                    <td><a href="cargos_form.php?id=<?php echo (int)$row['idRegistro']; ?>">#<?php echo (int)$row['idRegistro']; ?> — <?php echo htmlspecialchars($cargoNomes[(int)$row['idRegistro']] ?? 'Cargo não encontrado'); ?></a></td>
                    <td><?php echo htmlspecialchars($row['nomeUsuario'] ?? 'Sistema'); ?></td>
                    <td>
                        <?php if (!empty($detalhes['justificativa'])): ?>
                            <strong><?php echo htmlspecialchars($detalhes['justificativa']); ?></strong>
                        <?php else: ?>
                            <small><?php echo htmlspecialchars(json_encode($detalhes, JSON_UNESCAPED_UNICODE)); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($result['totalPages'] > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Total: <?php echo (int)$result['total']; ?></span>
            <div class="btn-group">
                <?php $base = array_filter(['term' => $params['term'], 'acao' => $params['acao']]); ?>
                <a class="btn btn-sm btn-outline-secondary <?php echo $result['currentPage'] <= 1 ? 'disabled' : ''; ?>" href="?<?php echo http_build_query(array_merge($base, ['page' => $result['currentPage'] - 1])); ?>">Anterior</a>
                <span class="btn btn-sm btn-light disabled"><?php echo $result['currentPage']; ?> / <?php echo $result['totalPages']; ?></span>
                <a class="btn btn-sm btn-outline-secondary <?php echo $result['currentPage'] >= $result['totalPages'] ? 'disabled' : ''; ?>" href="?<?php echo http_build_query(array_merge($base, ['page' => $result['currentPage'] + 1])); ?>">Próxima</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
