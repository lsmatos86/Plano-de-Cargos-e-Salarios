<?php
// Arquivo: views/pesquisa_salarial.php

require_once '../vendor/autoload.php';
require_once '../config.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Repository\PesquisaRepository;
use App\Controller\PesquisaController;
use App\Service\AuthService;

// Instancia as classes da arquitetura
$repository = new PesquisaRepository();
$authService = new AuthService();
$controller = new PesquisaController($repository, $authService);

// Processa a requisição e extrai as variáveis ($campanhas, $empresas, $message, etc.)
$data = $controller->handleRequest($_GET, $_POST, $_SERVER['REQUEST_METHOD']);
extract($data);

// Define o script JS específico desta página
$page_scripts = ['../scripts/pesquisa_salarial.js'];

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0"><i class="fas fa-search-dollar text-primary"></i> Pesquisa Salarial de Mercado</h1>
            <p class="text-muted">Gerencie campanhas, colete dados de empresas concorrentes e crie sua Matriz Salarial baseada na mediana do mercado.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs" id="pesquisaTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="campanhas-tab" data-bs-toggle="tab" data-bs-target="#campanhas" type="button" role="tab">
                <i class="fas fa-folder-open"></i> Campanhas de Pesquisa
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="empresas-tab" data-bs-toggle="tab" data-bs-target="#empresas" type="button" role="tab">
                <i class="fas fa-building"></i> Empresas Consultadas
            </button>
        </li>
    </ul>

    <div class="tab-content border border-top-0 p-4 bg-white shadow-sm rounded-bottom" id="pesquisaTabsContent">
        
        <div class="tab-pane fade show active" id="campanhas" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-secondary">Ciclos de Pesquisa</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaCampanha">
                    <i class="fas fa-plus"></i> Abrir Nova Pesquisa
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Título da Campanha</th>
                            <th>Data Abertura</th>
                            <th>Data Fechamento</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($campanhas)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Nenhuma campanha registrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($campanhas as $c): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($c['titulo']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($c['data_abertura'])); ?></td>
                                    <td><?php echo $c['data_fechamento'] ? date('d/m/Y', strtotime($c['data_fechamento'])) : '-'; ?></td>
                                    <td>
                                        <?php if($c['status'] == 'Aberta'): ?>
                                            <span class="badge bg-success"><i class="fas fa-lock-open"></i> Aberta</span>
                                        <?php elseif($c['status'] == 'Encerrada'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-lock"></i> Encerrada</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary"><i class="fas fa-check-double"></i> Aplicada na Matriz</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="pesquisa_lancamentos.php?id=<?php echo $c['campanhaId']; ?>" class="btn btn-sm btn-info text-white fw-bold">
                                            <i class="fas fa-table"></i> Acessar Lançamentos
                                        </a>
                                        
                                        <?php if($c['status'] == 'Aberta'): ?>
                                            <form method="POST" action="pesquisa_salarial.php" class="d-inline btn-encerrar-form">
                                                <input type="hidden" name="action" value="encerrar_campanha">
                                                <input type="hidden" name="campanhaId" value="<?php echo $c['campanhaId']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Encerrar Campanha">
                                                    <i class="fas fa-power-off"></i> Encerrar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="empresas" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-secondary">Catálogo de Concorrentes / Referências</h4>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovaEmpresa">
                    <i class="fas fa-plus"></i> Cadastrar Empresa
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nome da Empresa</th>
                            <th>Setor / Ramo de Atuação</th>
                            <th>Porte</th>
                            <th>Data de Cadastro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($empresas)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Nenhuma empresa cadastrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($empresas as $e): ?>
                                <tr>
                                    <td class="text-muted">#<?php echo $e['empresaId']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($e['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($e['setor'] ?: '-'); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($e['porte'] ?: '-'); ?></span></td>
                                    <td><?php echo date('d/m/Y', strtotime($e['data_cadastro'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNovaCampanha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="pesquisa_salarial.php" class="modal-content">
            <input type="hidden" name="action" value="nova_campanha">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-folder-plus"></i> Abrir Nova Pesquisa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Título da Pesquisa</label>
                    <input type="text" class="form-control" name="titulo" placeholder="Ex: Pesquisa Salarial Mercado Região X - 2026" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data de Abertura</label>
                    <input type="date" class="form-control" name="data_abertura" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Iniciar Pesquisa</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalNovaEmpresa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="pesquisa_salarial.php" class="modal-content border-success">
            <input type="hidden" name="action" value="nova_empresa">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-building"></i> Cadastrar Empresa Referência</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nome da Empresa</label>
                    <input type="text" class="form-control" name="nome" placeholder="Ex: Fazenda Progresso S/A" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Setor / Ramo</label>
                    <input type="text" class="form-control" name="setor" placeholder="Ex: Agronegócio / Exportação">
                </div>
                <div class="mb-3">
                    <label class="form-label">Porte da Empresa</label>
                    <select class="form-select" name="porte">
                        <option value="Pequeno">Pequeno Porte</option>
                        <option value="Médio" selected>Médio Porte</option>
                        <option value="Grande">Grande Porte</option>
                        <option value="Multinacional">Multinacional</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar Empresa</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>