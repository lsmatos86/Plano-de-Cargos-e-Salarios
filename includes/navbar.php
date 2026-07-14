<?php
// Arquivo: includes/navbar.php

/**
 * Menu de navegação lateral (Sidebar) e barra superior unificada da aplicação.
 * Versão completa restaurada do repositório com tratamento dinâmico de BASE_URL.
 */

$navBaseUrl = defined('BASE_URL') ? BASE_URL : '/ita/';
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo $navBaseUrl; ?>index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-network-wired"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Koppla RH</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item active">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Estrutura Organizacional
    </div>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/organograma.php">
            <i class="fas fa-fw fa-sitemap"></i>
            <span>Organograma</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/areas_atuacao.php">
            <i class="fas fa-fw fa-layer-group"></i>
            <span>Áreas e Setores</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Cargos e Salários
    </div>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/cargos.php">
            <i class="fas fa-fw fa-briefcase"></i>
            <span>Matriz de Cargos</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/nivel_hierarquico.php">
            <i class="fas fa-fw fa-level-up-alt"></i>
            <span>Níveis Hierárquicos</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/faixas_salariais.php">
            <i class="fas fa-fw fa-table"></i>
            <span>Tabelas Salariais</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/cbos.php">
            <i class="fas fa-fw fa-address-card"></i>
            <span>Mapeamento CBO</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Pesquisas e Indicadores
    </div>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/pesquisa_salarial.php">
            <i class="fas fa-fw fa-chart-bar"></i>
            <span>Pesquisas de Mercado</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>relatorios/cargo_total.php">
            <i class="fas fa-fw fa-file-invoice-dollar"></i>
            <span>Relatórios Estratégicos</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Administração
    </div>

    <?php if (function_exists('possuiPermissao') && possuiPermissao('usuarios_listar')): ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/usuarios.php">
            <i class="fas fa-fw fa-users-cog"></i>
            <span>Usuários e Acessos</span>
        </a>
    </li>
    <?php endif; ?>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/tabelas_apoio.php">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Configurações Globais</span>
        </a>
    </li>

    <?php if (function_exists('possuiPermissao') && possuiPermissao('auditoria_visualizar')): ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/auditoria.php">
            <i class="fas fa-fw fa-history"></i>
            <span>Logs de Auditoria</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo $navBaseUrl; ?>views/relatorio_homologacoes.php">
            <i class="fas fa-fw fa-shield-alt"></i>
            <span>Homologações de Cargos</span>
        </a>
    </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<div id="content-wrapper" class="d-flex flex-column">

    <div id="content">

        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm">

            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">

                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small font-weight-bold">
                            <?php echo isset($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Usuário Autenticado'; ?>
                        </span>
                        <div class="img-profile rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold" style="width: 32px; height: 32px; font-size: 13px;">
                            <?php echo isset($_SESSION['usuario_nome']) ? strtoupper(substr($_SESSION['usuario_nome'], 0, 2)) : 'RH'; ?>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="<?php echo $navBaseUrl; ?>views/perfil.php">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Meu Perfil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger font-weight-bold" href="<?php echo $navBaseUrl; ?>logout.php">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
                            Encerrar Sessão
                        </a>
                    </div>
                </li>

            </ul>

        </nav>
        <div class="main-body-content container-fluid">
