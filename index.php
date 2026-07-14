<?php
// Arquivo: index.php

// Define caminhos absolutos baseados no diretório atual da aplicação
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

use App\Repository\LookupRepository;
use App\Service\AuthService;

// Executa a checagem oficial de sessão do repositório
if (class_exists('App\Service\AuthService') && method_exists('App\Service\AuthService', 'checkAuth')) {
    if (!AuthService::checkAuth()) {
        header("Location: login.php");
        exit;
    }
} else if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$lookupRepository = new LookupRepository();

// Captura as contagens estatísticas reais da base de dados
$totalCargos = $lookupRepository->countRecords('cargos');
$totalUsuarios = $lookupRepository->countRecords('usuarios');
$totalAreas = $lookupRepository->countRecords('areas_atuacao');
$totalEscolaridades = $lookupRepository->countRecords('escolaridades');

$page_title = 'Dashboard';
$is_dashboard = true;
$main_container_class = 'container-fluid px-3 px-lg-4';

$desktopItems = [
    ['label' => 'Cargos', 'description' => 'Matriz de cargos', 'icon' => 'fa-briefcase', 'color' => 'primary', 'url' => BASE_URL . 'views/cargos.php'],
    ['label' => 'Organograma', 'description' => 'Estrutura organizacional', 'icon' => 'fa-sitemap', 'color' => 'success', 'url' => BASE_URL . 'views/organograma.php'],
    ['label' => 'Áreas', 'description' => 'Áreas de atuação', 'icon' => 'fa-layer-group', 'color' => 'info', 'url' => BASE_URL . 'views/areas_atuacao.php'],
    ['label' => 'Hierarquia', 'description' => 'Níveis hierárquicos', 'icon' => 'fa-level-up-alt', 'color' => 'purple', 'url' => BASE_URL . 'views/nivel_hierarquico.php'],
    ['label' => 'Faixas Salariais', 'description' => 'Matriz salarial', 'icon' => 'fa-money-check-dollar', 'color' => 'success', 'url' => BASE_URL . 'views/faixas_salariais.php'],
    ['label' => 'Pesquisa Salarial', 'description' => 'Dados de mercado', 'icon' => 'fa-chart-line', 'color' => 'warning', 'url' => BASE_URL . 'views/pesquisa_salarial.php'],
    ['label' => 'Habilidades', 'description' => 'Hard e soft skills', 'icon' => 'fa-lightbulb', 'color' => 'warning', 'url' => BASE_URL . 'views/habilidades.php'],
    ['label' => 'Cursos', 'description' => 'Cursos e capacitações', 'icon' => 'fa-book-open', 'color' => 'primary', 'url' => BASE_URL . 'views/cursos.php'],
    ['label' => 'Escolaridades', 'description' => 'Formação acadêmica', 'icon' => 'fa-graduation-cap', 'color' => 'info', 'url' => BASE_URL . 'views/escolaridades.php'],
    ['label' => 'CBOs', 'description' => 'Ocupações e famílias', 'icon' => 'fa-address-card', 'color' => 'purple', 'url' => BASE_URL . 'views/cbos.php'],
    ['label' => 'Riscos', 'description' => 'Riscos ocupacionais', 'icon' => 'fa-triangle-exclamation', 'color' => 'danger', 'url' => BASE_URL . 'views/riscos.php'],
    ['label' => 'Características', 'description' => 'Características dos cargos', 'icon' => 'fa-list-check', 'color' => 'secondary', 'url' => BASE_URL . 'views/caracteristicas.php'],
    ['label' => 'Recursos', 'description' => 'Recursos e grupos', 'icon' => 'fa-toolbox', 'color' => 'secondary', 'url' => BASE_URL . 'views/recursos.php'],
    ['label' => 'Usuários', 'description' => 'Usuários e acessos', 'icon' => 'fa-users-gear', 'color' => 'purple', 'url' => BASE_URL . 'views/usuarios.php'],
    ['label' => 'Papéis', 'description' => 'Papéis e permissões', 'icon' => 'fa-user-shield', 'color' => 'danger', 'url' => BASE_URL . 'views/roles.php'],
    ['label' => 'Auditoria', 'description' => 'Histórico de atividades', 'icon' => 'fa-clock-rotate-left', 'color' => 'secondary', 'url' => BASE_URL . 'views/auditoria.php'],
    ['label' => 'Relatório Geral', 'description' => 'Relatório consolidado', 'icon' => 'fa-file-pdf', 'color' => 'danger', 'url' => BASE_URL . 'relatorios/cargo_total.php'],
];

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .dashboard-shell {
        max-width: 1600px;
        margin: 0 auto;
    }
    .dashboard-heading {
        color: #24324a;
        font-weight: 700;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }
    .stat-tile {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.15rem;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: .8rem;
        box-shadow: 0 .2rem .7rem rgba(35, 50, 74, .07);
    }
    .stat-tile .stat-icon {
        display: grid;
        place-items: center;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        border-radius: 12px;
        background: #eef4ff;
        color: #0d6efd;
        font-size: 1.25rem;
    }
    .stat-value { font-size: 1.45rem; line-height: 1; font-weight: 750; color: #24324a; }
    .stat-label { margin-top: .3rem; color: #6c757d; font-size: .82rem; }
    .desktop-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
        gap: 1rem;
    }
    .desktop-shortcut {
        display: flex;
        min-height: 165px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.15rem .8rem;
        color: #24324a;
        text-align: center;
        text-decoration: none;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: .9rem;
        box-shadow: 0 .2rem .65rem rgba(35, 50, 74, .06);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .desktop-shortcut:hover,
    .desktop-shortcut:focus-visible {
        color: #24324a;
        transform: translateY(-4px);
        border-color: #b9c8dd;
        box-shadow: 0 .65rem 1.3rem rgba(35, 50, 74, .14);
    }
    .shortcut-icon {
        display: grid;
        place-items: center;
        width: 68px;
        height: 68px;
        margin-bottom: .8rem;
        border-radius: 18px;
        background: color-mix(in srgb, currentColor 13%, white);
        font-size: 2rem;
    }
    .shortcut-title { font-size: .96rem; font-weight: 700; }
    .shortcut-description { margin-top: .25rem; color: #7a8493; font-size: .76rem; line-height: 1.25; }
    .shortcut-primary { color: #0d6efd; } .shortcut-success { color: #198754; }
    .shortcut-info { color: #0aa2c0; } .shortcut-warning { color: #d58b00; }
    .shortcut-danger { color: #dc3545; } .shortcut-secondary { color: #6c757d; }
    .shortcut-purple { color: #6f42c1; }
    .desktop-shortcut .shortcut-title { color: #24324a; }
    @media (max-width: 991.98px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575.98px) {
        .stats-grid { grid-template-columns: 1fr; }
        .desktop-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .7rem; }
        .desktop-shortcut { min-height: 145px; padding: .9rem .55rem; }
        .shortcut-icon { width: 56px; height: 56px; font-size: 1.65rem; }
    }
</style>

<div class="dashboard-shell mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 dashboard-heading mb-1">Área de trabalho</h1>
            <p class="text-muted mb-0">Selecione um módulo para começar.</p>
        </div>
        <span class="badge rounded-pill text-bg-light border px-3 py-2">
            <i class="fas fa-calendar-day me-1 text-primary"></i><?= date('d/m/Y'); ?>
        </span>
    </div>

    <section class="stats-grid mb-4" aria-label="Indicadores gerais">
        <div class="stat-tile"><span class="stat-icon"><i class="fas fa-briefcase"></i></span><div><div class="stat-value"><?= (int)$totalCargos; ?></div><div class="stat-label">Cargos cadastrados</div></div></div>
        <div class="stat-tile"><span class="stat-icon"><i class="fas fa-users"></i></span><div><div class="stat-value"><?= (int)$totalUsuarios; ?></div><div class="stat-label">Usuários ativos</div></div></div>
        <div class="stat-tile"><span class="stat-icon"><i class="fas fa-layer-group"></i></span><div><div class="stat-value"><?= (int)$totalAreas; ?></div><div class="stat-label">Áreas de atuação</div></div></div>
        <div class="stat-tile"><span class="stat-icon"><i class="fas fa-graduation-cap"></i></span><div><div class="stat-value"><?= (int)$totalEscolaridades; ?></div><div class="stat-label">Escolaridades</div></div></div>
    </section>

    <section aria-labelledby="modulos-title">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="fas fa-grip text-primary"></i>
            <h2 id="modulos-title" class="h5 dashboard-heading mb-0">Módulos do sistema</h2>
        </div>
        <div class="desktop-grid">
            <?php foreach ($desktopItems as $item): ?>
                <a class="desktop-shortcut" href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="shortcut-icon shortcut-<?= htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fas <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                    </span>
                    <span class="shortcut-title"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="shortcut-description"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
