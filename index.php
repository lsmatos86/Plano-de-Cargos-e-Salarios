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

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container-fluid mt-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Dashboard</h1>
        <a href="relatorios/cargo_total.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50 mr-1"></i> Gerar Relatório Geral
        </a>
    </div>

    <div class="row">

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total de Cargos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalCargos; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Colaboradores Ativos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalUsuarios; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Áreas de Atuação</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalAreas; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-layer-group fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Requisitos de Escolaridade</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalEscolaridades; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tasks mr-2"></i>Ações Rápidas do Sistema</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="views/cargos.php" class="btn btn-primary btn-icon-split btn-block text-left p-0 shadow-sm">
                                <span class="icon text-white-50"><i class="fas fa-briefcase"></i></span>
                                <span class="text font-weight-bold">Gerenciar Matriz de Cargos</span>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="views/organograma.php" class="btn btn-info btn-icon-split btn-block text-left p-0 shadow-sm">
                                <span class="icon text-white-50"><i class="fas fa-sitemap"></i></span>
                                <span class="text font-weight-bold">Visualizar Organograma</span>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="views/pesquisa_salarial.php" class="btn btn-success btn-icon-split btn-block text-left p-0 shadow-sm">
                                <span class="icon text-white-50"><i class="fas fa-chart-bar"></i></span>
                                <span class="text font-weight-bold">Pesquisas de Mercado</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>