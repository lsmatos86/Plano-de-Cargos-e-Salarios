<?php
// Arquivo: index.php (Dashboard - Refatorado com Header/Footer)

// 1. Inclusão de arquivos
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'includes/functions.php'; // Para autenticação e $authService

// 2. Importa o Repositório
use App\Repository\LookupRepository;

// 3. Segurança (Redireciona para o login se não estiver logado)
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

// 4. Definições da Página (PAINEL)
$page_title = 'Dashboard';
$root_path = './'; // Define o caminho para a raiz
$breadcrumb_items = ['Dashboard' => null]; // 'null' indica a página ativa

// 5. LÓGICA: Obter Contagens (Estatísticas)
$lookupRepo = new LookupRepository();
$stats = [
    'cargos' => $lookupRepo->countRecords('cargos'),
    'habilidades' => $lookupRepo->countRecords('habilidades'),
    'caracteristicas' => $lookupRepo->countRecords('caracteristicas'),
    'riscos' => $lookupRepo->countRecords('riscos'),
    'usuarios' => $lookupRepo->countRecords('usuarios'),
    'cursos' => $lookupRepo->countRecords('cursos'),
    'areas_atuacao' => $lookupRepo->countRecords('areas_atuacao'),
    'niveis_hierarquia' => $lookupRepo->countRecords('nivel_hierarquico'),
    'faixas_salariais' => $lookupRepo->countRecords('faixas_salariais'),
];

// 6. Inclui o Header
include 'includes/header.php';

?>

<div class="p-5 mb-4 bg-white rounded-3 shadow-sm border">
    <div class="container-fluid py-3">
        <h1 class="display-6 fw-bold">Visão Geral do Sistema</h1>
        <p class="fs-5 text-muted">Estatísticas e acessos rápidos aos principais módulos.</p>
    </div>
</div>

<h2 class="mb-4">Estatísticas Chave</h2>
<div class="row g-4 mb-5">
    <div class="col-md-6 col-lg-3">
        <div class="card card-stat border-primary h-100 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title text-primary">Cargos Cadastrados</h5>
                    <h1 class="display-4 fw-bold"><?php echo $stats['cargos']; ?></h1>
                </div>
                <i class="fas fa-hard-hat stat-icon text-primary opacity-50"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card card-stat border-info h-100 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title text-info">Áreas de Atuação</h5>
                    <h1 class="display-4 fw-bold"><?php echo $stats['areas_atuacao']; ?></h1>
                </div>
                <i class="fas fa-building stat-icon text-info opacity-50"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card card-stat border-warning h-100 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title text-warning">Níveis Hierárquicos</h5>
                    <h1 class="display-4 fw-bold"><?php echo $stats['niveis_hierarquia']; ?></h1>
                </div>
                <i class="fas fa-sitemap stat-icon text-warning opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card card-stat border-success h-100 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title text-success">Total de Usuários</h5>
                    <h1 class="display-4 fw-bold"><?php echo $stats['usuarios']; ?></h1>
                </div>
                <i class="fas fa-users stat-icon text-success opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<h2 class="mb-4">Menu Principal de Acesso</h2>

<h3 class="mb-3">Gestão Central</h3>
<div class="row row-cols-4 row-cols-md-5 row-cols-lg-6 g-4">
    <div class="col mb-4">
        <a href="views/cargos.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-briefcase icon-gestao"></i>
                <h5>Gerenciar Cargos</h5>
            </div>
        </a>
    </div>
</div>

<hr class="my-4">

<h3 class="mb-3">Estrutura e Salário</h3>
<div class="row row-cols-4 row-cols-md-5 row-cols-lg-6 g-4">
     <div class="col mb-4">
        <a href="views/areas_atuacao.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-building icon-estrutura"></i>
                <h5>Áreas de Atuação</h5>
            </div>
        </a>
    </div>
    <div class="col mb-4">
        <a href="views/tipo_hierarquia.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-tag icon-estrutura"></i>
                <h5>Tipos de Hierarquia</h5>
            </div>
        </a>
    </div>
    <div class="col mb-4">
        <a href="views/nivel_hierarquico.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-sort-numeric-down icon-estrutura"></i>
                <h5>Níveis Hierárquicos</h5>
            </div>
        </a>
    </div>
    <div class="col mb-4">
        <a href="views/faixas_salariais.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-wallet icon-estrutura"></i>
                <h5>Faixas Salariais</h5>
            </div>
        </a>
    </div>
</div>

<hr class="my-4">

<h3 class="mb-3">Cadastros Mestra e Config.</h3>
<div class="row row-cols-4 row-cols-md-5 row-cols-lg-6 g-4">
    <div class="col mb-4">
        <a href="views/habilidades.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-lightbulb icon-cadastros"></i>
                <h5>Habilidades</h5>
            </div>
        </a>
    </div>
    <div class="col mb-4">
        <a href="views/cursos.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-certificate icon-cadastros"></i>
                <h5>Cursos</h5>
            </div>
        </a>
    </div>
    <div class="col mb-4">
        <a href="views/riscos.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-exclamation-triangle icon-cadastros"></i>
                <h5>Riscos</h5>
            </div>
        </a>
    </div>
    <div class="col mb-4">
        <a href="views/caracteristicas.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-user-tag icon-cadastros"></i>
                <h5>Características</h5>
            </div>
        </a>
    </div>
    <div class="col mb-4">
        <a href="views/escolaridades.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm"> 
                <i class="fas fa-graduation-cap icon-cadastros"></i>
                <h5>Escolaridades</h5>
            </div>
        </a>
    </div>
     <div class="col mb-4">
        <a href="views/cbos.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-id-badge icon-cadastros"></i>
                <h5>CBOs e Família</h5>
            </div>
        </a>
    </div>
     <div class="col mb-4">
        <a href="views/recursos.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-wrench icon-cadastros"></i>
                <h5>Recursos e Grupos</h5>
            </div>
        </a>
    </div>
</div>

<hr class="my-4">

<h3 class="mb-3">Administração e Relatórios</h3>
<div class="row row-cols-4 row-cols-md-5 row-cols-lg-6 g-4">
    <div class="col mb-4">
        <a href="views/usuarios.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-users icon-admin"></i>
                <h5>Usuários</h5>
            </div>
        </a>
    </div>
    <div class="col mb-4">
        <a href="views/roles.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-shield-alt icon-admin"></i>
                <h5>Papéis e Permissões</h5>
            </div>
        </a>
    </div>
    <div class="col mb-4">
        <a href="views/auditoria.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-history icon-admin"></i>
                <h5>Logs de Auditoria</h5>
            </div>
        </a>
    </div>
     <div class="col mb-4">
        <a href="relatorios/cargo_total.php" class="desktop-icon-container">
            <div class="desktop-icon shadow-sm">
                <i class="fas fa-file-pdf icon-relatorios"></i>
                <h5>Relatório Consolidado</h5>
            </div>
        </a>
    </div>
    <div class="col mb-4">
        <a href="#" class="desktop-icon-container" onclick="event.preventDefault(); alert('Funcionalidade em desenvolvimento.');">
            <div class="desktop-icon shadow-sm" style="opacity: 0.6;">
                <i class="fas fa-project-diagram icon-relatorios"></i>
                <h5>Organograma</h5>
            </div>
        </a>
    </div>
</div>

<?php
// 7. Inclui o Footer
include 'includes/footer.php';
?>