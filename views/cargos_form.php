<?php
// Arquivo: views/cargos_form.php (Refatorado com Header/Footer)

// (Dados JS injetados no <head> através de $extra_head_content para evitar duplicação)

// Inicializa variáveis
$message = '';
$message_type = '';

// Variáveis de Controle
$originalId = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$isDuplicating = $action === 'duplicate' && $originalId > 0;
$isEditing = !$isDuplicating && $originalId > 0;
$currentFormId = $isEditing ? $originalId : 0;
$cargoId = $originalId;

// ======================================================
// Definições de Página para o header.php
// ======================================================
$page_title = $isDuplicating ? 'Duplicar Cargo (Novo Registro)' : ($isEditing ? 'Editar Cargo' : 'Novo Cargo');
$root_path = '../'; 
$breadcrumb_items = [
    'Dashboard' => '../index.php',
    'Gerenciamento de Cargos' => 'cargos.php',
    $page_title => null // Página ativa
];

// ----------------------------------------------------
// 0. AUTOLOAD, CONFIG E HELPERS (assegura classes/funcs disponíveis)
// ----------------------------------------------------
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

use App\Repository\LookupRepository;
use App\Repository\HabilidadeRepository;
use App\Repository\AreaRepository;
use App\Repository\CargoRepository;

// ----------------------------------------------------
// 1. CARREGAMENTO DOS LOOKUPS MESTRES (MANTIDO)
// ----------------------------------------------------
$lookupRepo = new LookupRepository();
$habilidadeRepo = new HabilidadeRepository();
$areaRepo = new AreaRepository();
$cargoRepo = new CargoRepository(); 

// Lookups
$cbos = array_column($lookupRepo->findCbos(), 'display_name', 'cboId');
$escolaridades = array_column($lookupRepo->findEscolaridades(), 'escolaridadeTitulo', 'escolaridadeId');
$habilidadesAgrupadas = $habilidadeRepo->getGroupedLookup();

// Cria um mapa plano que inclui o 'tipo' para repopulamento pós-erro
$habilidadesMap = [];
foreach ($habilidadesAgrupadas as $grupoNome => $habilidadesGrupo) {
    foreach ($habilidadesGrupo as $id => $nome) {
        $habilidadesMap[(int)$id] = [
            'nome' => $nome, 
            'tipo' => $grupoNome
        ];
    }
}

$habilidades = array_column($lookupRepo->findHabilidades(), 'nome', 'id');
$caracteristicas = array_column($lookupRepo->findCaracteristicas(), 'nome', 'id');
$riscos = array_column($lookupRepo->findRiscos(), 'nome', 'id');
$cursos = array_column($lookupRepo->findCursos(), 'nome', 'id');
$recursosGrupos = array_column($lookupRepo->findRecursosGrupos(), 'nome', 'id');
$faixasSalariais = array_column($lookupRepo->findFaixas(), 'faixaNivel', 'faixaId');
$cargosSupervisor = array_column($lookupRepo->findCargosForSelect(), 'nome', 'id');
$areasAtuacao = $areaRepo->getHierarchyLookup(); 

// Níveis Hierárquicos
$niveisHierarquicosData = $lookupRepo->findNivelHierarquico();
$niveisOrdenados = [];
foreach ($niveisHierarquicosData as $n) {
    $niveisOrdenados[$n['nivelId']] = $n['nivelOrdem'] . 'º - ' . $n['tipoHierarquiaNome'] . ' (' . $n['nivelNome'] . ')';
}

// --- Variáveis de estado do Formulário ---
$cargo = [];
$cargoAreas = [];
$cargoHabilidades = [];
$cargoCaracteristicas = [];
$cargoRiscos = [];
$cargoCursos = [];
$cargoRecursosGrupos = [];
$cargoSinonimos = [];

// ----------------------------------------------------
// 2. BUSCA DADOS PARA EDIÇÃO OU DUPLICAÇÃO
// ----------------------------------------------------
if ($isEditing || $isDuplicating) {
    // use the repository helper that returns the full payload for the form
    $formData = $cargoRepo->findFormData($originalId);
    if ($formData) {
        // Assign the pieces returned by findFormData()
        $cargo = $formData['cargo'] ?? [];
        $cargoSinonimos = $formData['sinonimos'] ?? [];
        $cargoRiscos = $formData['riscos'] ?? [];
        $cargoAreas = $formData['areas'] ?? [];
        $cargoHabilidades = $formData['habilidades'] ?? [];
        $cargoCaracteristicas = $formData['caracteristicas'] ?? [];
        $cargoCursos = $formData['cursos'] ?? [];
        // note: repository returns recursos_grupos key name
        $cargoRecursosGrupos = $formData['recursos_grupos'] ?? [];
    }
}

// Prepara os dados para o JavaScript
$jsonData = [
    'cargo' => $cargo,
    'habilidades' => array_map(function($h) use ($habilidadesMap) {
        return [
            'id' => $h['id'],
            'nome' => $h['nome'],
            'tipo' => $h['tipo'] ?? $habilidadesMap[$h['id']]['tipo'] ?? 'Outros'
        ];
    }, $cargoHabilidades),
    'caracteristicas' => array_map(function($c) {
        return ['id' => $c['id'], 'nome' => $c['nome']];
    }, $cargoCaracteristicas),
    'riscos' => $cargoRiscos,
    'cursos' => $cargoCursos,
    'recursosGrupos' => $cargoRecursosGrupos,
    'areas' => $cargoAreas,
    'sinonimos' => $cargoSinonimos
];
?>

<script>
    // Dados do servidor injetados no escopo global
    window.DEBUG_PHP_WINDOW_FULL = <?php echo json_encode($jsonData, JSON_PRETTY_PRINT); ?>;

    // Mapeamento para nomes esperados pelo cargos_form.js
    window.habilidadesAssociadas = window.DEBUG_PHP_WINDOW_FULL.habilidades || [];
    window.caracteristicasAssociadas = window.DEBUG_PHP_WINDOW_FULL.caracteristicas || [];
    window.riscosAssociados = window.DEBUG_PHP_WINDOW_FULL.riscos || [];
    window.cursosAssociados = window.DEBUG_PHP_WINDOW_FULL.cursos || [];
    window.recursosGruposAssociados = window.DEBUG_PHP_WINDOW_FULL.recursosGrupos || [];
    window.areasAssociadas = window.DEBUG_PHP_WINDOW_FULL.areas || [];
    window.sinonimosAssociados = window.DEBUG_PHP_WINDOW_FULL.sinonimos || [];
</script>

<?php
            echo json_encode([
                'habilidades' => $cargoHabilidades,
                'caracteristicas' => $cargoCaracteristicas,
                'riscos' => $cargoRiscos,
                'cursos' => $cargoCursos,
                'recursosGrupos' => $cargoRecursosGrupos,
                'areas' => $cargoAreas,
                'sinonimos' => $cargoSinonimos,
                'cargo' => $cargo
            ], JSON_PRETTY_PRINT);
        ?>;

        // Mapeamento para variáveis globais esperadas pelo cargos_form.js
        window.habilidadesAssociadas = window.DEBUG_PHP_WINDOW_FULL.habilidades || [];
        window.caracteristicasAssociadas = window.DEBUG_PHP_WINDOW_FULL.caracteristicas || [];
        window.riscosAssociados = window.DEBUG_PHP_WINDOW_FULL.riscos || [];
        window.cursosAssociados = window.DEBUG_PHP_WINDOW_FULL.cursos || [];
        window.recursosGruposAssociados = window.DEBUG_PHP_WINDOW_FULL.recursosGrupos || [];
        window.areasAssociadas = window.DEBUG_PHP_WINDOW_FULL.areas || [];
        window.sinonimosAssociados = window.DEBUG_PHP_WINDOW_FULL.sinonimos || [];
            "cargoId": 4,
            "isEditing": true,
            "cargo": {
                "cargoId": 4,
                "cargoNome": "ANALISTA DE EXPORTAÇÃO",
                "cargoDescricao": "Supervisiona e coordena atividades logísticas e comerciais relacionadas à exportação de produtos. Negocia condições de compra e venda, elabora propostas, compatibiliza cronogramas de produção com contratos, e assegura a elaboração e controle da documentação necessária. Interage com clientes, fornecedores e prestadores de serviço.",
                "cboId": 4,
                "cargoResumo": "Coordena logística e comércio exterior, negocia vendas, elabora propostas, compatibiliza produção e garante a conformidade da documentação de exportação.",
                "escolaridadeId": 5,
                "faixaId": null,
                "nivelHierarquicoId": null,
                "cargoSupervisorId": null,
                "cargoExperiencia": "Experiência prática de 2 a 3 anos é considerada essencial para domínio das rotinas e exigências da função.",
                "cargoCondicoes": "Trabalho dinâmico, com grande atenção a prazos e detalhes operacionais. Pode ser realizado em escritório, home office ou in loco, exigindo acompanhamento da programação de navios, liberação de carga e logística. A comunicação com agentes internacionais exige domínio técnico e cultural.",
                "cargoComplexidade": "Trata-se de um cargo técnico e estratégico que exige domínio de comércio exterior, conhecimento em logística internacional, legislação e processos aduaneiros. Requer capacidade de decisão rápida, comunicação assertiva e atenção aos detalhes. A complexidade está no gerenciamento de múltiplas etapas e riscos associados à exportação.",
                "cargoResponsabilidades": "Negociar condições de compra e venda, elaborar propostas comerciais, coordenar cronogramas de produção, elaborar e conferir documentação de exportação, contratar e acompanhar serviços de terceiros, assegurar o recebimento de pagamentos (câmbio) e acompanhar a logística de exportação.",
                "cargoDataCadastro": "2025-10-17 12:57:40",
                "cargoDataAtualizacao": "2025-10-17 12:57:40"
            },
            "habilidades": [
                { "id": 1, "nome": "Ética, Integridade e Honestidade", "tipo": "Softskill" },
                { "id": 2, "nome": "Proatividade e Iniciativa", "tipo": "Softskill" },
                { "id": 3, "nome": "Atitude Positiva e Otimismo", "tipo": "Softskill" },
                { "id": 4, "nome": "Trabalho em Equipe e Colaboração", "tipo": "Softskill" },
                { "id": 5, "nome": "Comunicação Eficaz e Assertiva", "tipo": "Softskill" },
                { "id": 6, "nome": "Resiliência e Adaptabilidade", "tipo": "Softskill" },
                { "id": 7, "nome": "Espírito de Pertencimento", "tipo": "Softskill" },
                { "id": 8, "nome": "Senso de Responsabilidade e Comprometimento", "tipo": "Softskill" },
                { "id": 9, "nome": "Empatia e Respeito Humano", "tipo": "Softskill" },
                { "id": 10, "nome": "Senso de Comunidade e Bem-Estar Coletivo", "tipo": "Softskill" },
                { "id": 11, "nome": "Capacidade de Aprendizado Contínuo", "tipo": "Hardskill" },
                { "id": 12, "nome": "Atenção à Qualidade do Produto e/ou Serviço", "tipo": "Hardskill" },
                { "id": 13, "nome": "Compromisso com Normas de Segurança e Sustentabilidade", "tipo": "Hardskill" },
                { "id": 14, "nome": "Uso Consciente e Racional de Recursos", "tipo": "Hardskill" },
                { "id": 15, "nome": "Conformidade com Normas e Boas Práticas (BP)", "tipo": "Hardskill" },
                { "id": 35, "nome": "Negociação Comercial e Logística Internacional", "tipo": "Hardskill" },
                { "id": 36, "nome": "Elaboração de Propostas Comerciais", "tipo": "Hardskill" },
                { "id": 37, "nome": "Comunicação em Língua Estrangeira (Inglês)", "tipo": "Hardskill" },
                { "id": 38, "nome": "Coordenação de Cronogramas de Produção", "tipo": "Hardskill" },
                { "id": 39, "nome": "Elaboração e Conferência de Documentos para Exportação", "tipo": "Hardskill" },
                { "id": 40, "nome": "Contratação e Acompanhamento de Serviços de Terceiros", "tipo": "Hardskill" },
                { "id": 41, "nome": "Controle de Recebimentos (Câmbio) e Análise de Risco", "tipo": "Hardskill" },
                { "id": 42, "nome": "Atualização Profissional e Conhecimento em Globalização", "tipo": "Hardskill" }
            ],
            "caracteristicas": [
                { "id": 1, "nome": "Caráter (Fundamentado em Princípios Éticos)" },
                { "id": 2, "nome": "Felicidade e Bom Humor no Ambiente de Trabalho" },
                { "id": 3, "nome": "Ambição Saudável e Crescimento por Mérito" },
            ]
        };
        
        // Mapeamento para nomes esperados pelo cargos_form.js
        window.habilidadesAssociadas = window.DEBUG_PHP_WINDOW_FULL.habilidades || [];
        window.caracteristicasAssociadas = window.DEBUG_PHP_WINDOW_FULL.caracteristicas || [];
        window.riscosAssociados = window.DEBUG_PHP_WINDOW_FULL.riscos || [];
        window.cursosAssociados = window.DEBUG_PHP_WINDOW_FULL.cursos || [];
        window.recursosGruposAssociados = window.DEBUG_PHP_WINDOW_FULL.recursosGrupos || [];
        window.sinonimosAssociados = window.DEBUG_PHP_WINDOW_FULL.sinonimos || [];
                { "id": 4, "nome": "Honestidade e Transparência nas Relações" },
                { "id": 5, "nome": "Espiritualidade ou Senso de Propósito" },
                { "id": 6, "nome": "Cuidado com a Saúde e Bem-Estar" },
                { "id": 7, "nome": "Alinhamento com Princípios de Comércio Justo (Fairtrade)" }
            ],
            "riscos": [
                { "id": 4, "nome": "Psicossocial", "descricao": "Pressão por prazos, tomada de decisões sob estresse" },
                { "id": 5, "nome": "Acidental", "descricao": "Risco operacional: erro na documentação, atrasos logísticos" },
                { "id": 4, "nome": "Psicossocial", "descricao": "Riscos econômicos: liberação de documentos sem recebimento, variação cambial" }
            ],
            "cursos": [
                { "id": 5, "nome": "Informática Básica e Pacote Office (Excel)", "obrigatorio": true, "obs": null },
                { "id": 6, "nome": "Comércio Exterior e Processos Aduaneiros", "obrigatorio": true, "obs": null },
                { "id": 7, "nome": "Inglês Técnico e Comercial", "obrigatorio": false, "obs": null }
            ],
            "recursos_grupos": [
                { "id": 1, "nome": "TI e Comunicação" },
                { "id": 2, "nome": "Material de Escritório" },
                { "id": 3, "nome": "Documentação e Registros" },
                { "id": 5, "nome": "Maquinário e Veículos" }
            ],
            "areas": [],
            "sinonimos": [
                { "id": 16, "nome": "Especialista em Comércio Exterior" },
                { "id": 17, "nome": "Analista de Logística Internacional" },
                { "id": 18, "nome": "Coordenador de Documentação de Embarque" },
                { "id": 19, "nome": "Analista de Vendas Internacionais" },
                { "id": 20, "nome": "Consultor de Processos Aduaneiros" }
            ]
        };

        // Map to the global names the client script expects
        window.habilidadesAssociadas = window.DEBUG_PHP_WINDOW_FULL.habilidades || [];
        window.caracteristicasAssociadas = window.DEBUG_PHP_WINDOW_FULL.caracteristicas || [];
        window.riscosAssociados = window.DEBUG_PHP_WINDOW_FULL.riscos || [];
        window.cursosAssociados = window.DEBUG_PHP_WINDOW_FULL.cursos || [];
        window.recursosGruposAssociadas = window.DEBUG_PHP_WINDOW_FULL.recursos_grupos || [];
        window.areasAssociadas = window.DEBUG_PHP_WINDOW_FULL.areas || [];
        window.sinonimosAssociados = window.DEBUG_PHP_WINDOW_FULL.sinonimos || [];
    </script>
                        <?php foreach ($escolaridades as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" <?php echo (isset($cargo['escolaridadeId']) && $cargo['escolaridadeId'] == $id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="cargoExperiencia" class="form-label">Experiência Necessária</label>
                    <input type="text" class="form-control" id="cargoExperiencia" name="cargoExperiencia" value="<?php echo htmlspecialchars($cargo['cargoExperiencia'] ?? ''); ?>">
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="hierarquia" role="tabpanel" aria-labelledby="hierarquia-tab">
            <h4 class="mb-3"><i class="fas fa-level-up-alt"></i> Hierarquia de Comando</h4>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nivelHierarquicoId" class="form-label">Nível Hierárquico</label>
                    <select class="form-select searchable-select" id="nivelHierarquicoId" name="nivelHierarquicoId">
                        <option value="">--- Selecione o Nível ---</option>
                        <?php foreach ($niveisOrdenados as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" <?php echo (isset($cargo['nivelHierarquicoId']) && $cargo['nivelHierarquicoId'] == $id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text"><a href="nivel_hierarquico.php" target="_blank">Gerenciar Níveis</a></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="cargoSupervisorId" class="form-label">Reporta-se a (Supervisor)</label>
                    <select class="form-select searchable-select" id="cargoSupervisorId" name="cargoSupervisorId">
                        <option value="">--- Nível Superior / Nenhum ---</option>
                        <?php foreach ($cargosSupervisor as $id => $nome): 
                            if ($isEditing && (int)($originalId) === (int)$id): continue; endif; 
                        ?>
                            <option value="<?php echo $id; ?>" <?php echo (int)($cargo['cargoSupervisorId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Define a linha de comando para o Organograma.</div>
                </div>
            </div>
            <hr>
            <h4 class="mb-3"><i class="fas fa-wallet"></i> Faixa Salarial</h4>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="faixaId" class="form-label">Faixa/Nível Salarial</label>
                    <select class="form-select searchable-select" id="faixaId" name="faixaId">
                        <option value="">--- Não Definido ---</option>
                        <?php foreach ($faixasSalariais as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" <?php echo (int)($cargo['faixaId'] ?? 0) === (int)$id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Lembre-se de cadastrar as faixas salariais.</div>
                </div>
            </div>
            <hr>
            <h4 class="mb-3"><i class="fas fa-building"></i> Áreas de Atuação</h4>
            <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoAreasAtuacao">
                <i class="fas fa-plus"></i> Adicionar Área
            </button>
            <div class="card p-0 mt-2">
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Área de Atuação (Hierárquica)</th>
                                <th class="grid-action-cell text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="areasAtuacaoGridBody">
                            </tbody>
                    </table>
                </div>
            </div>
            <div class="form-text mt-3"><a href="areas_atuacao.php" target="_blank">Gerenciar Estrutura de Áreas</a></div>
        </div>
        <div class="tab-pane fade" id="requisitos" role="tabpanel" aria-labelledby="requisitos-tab">
            <h4 class="mb-3"><i class="fas fa-lightbulb"></i> Habilidades</h4>
            <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoHabilidades">
                <i class="fas fa-plus"></i> Adicionar Habilidade
            </button>
            <div class="card p-0 mt-2 mb-4">
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Habilidade</th>
                                <th class="grid-action-cell text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="habilidadesGridBody">
                            </tbody>
                    </table>
                </div>
            </div>
            <h4 class="mb-3"><i class="fas fa-user-tag"></i> Características</h4>
            <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoCaracteristicas">
                <i class="fas fa-plus"></i> Adicionar Característica
            </button>
            <div class="card p-0 mt-2 mb-4">
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Característica</th>
                                <th class="grid-action-cell text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="caracteristicasGridBody">
                            </tbody>
                    </table>
                </div>
            </div>
            <h4 class="mb-3"><i class="fas fa-certificate"></i> Cursos</h4>
            <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoCursos">
                <i class="fas fa-plus"></i> Adicionar Curso
            </button>
            <div class="card p-0 mt-2 mb-4">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th width="35%">Curso</th>
                                <th width="50%">Obrigatoriedade e Observação</th>
                                <th class="grid-action-cell text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="cursosGridBody">
                            </tbody>
                    </table>
            </div>
            <h4 class="mb-3"><i class="fas fa-wrench"></i> Grupos de Recursos</h4>
            <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoRecursosGrupos">
                <i class="fas fa-plus"></i> Adicionar Grupo de Recurso
            </button>
            <div class="card p-0 mt-2 mb-4">
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Grupo de Recurso</th>
                                <th class="grid-action-cell text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="recursosGruposGridBody">
                            </tbody>
                    </table>
                </div>
            </div>
            <h4 class="mb-3"><i class="fas fa-radiation-alt"></i> Riscos de Exposição</h4>
            <button type="button" class="btn btn-sm btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAssociacaoRiscos">
                <i class="fas fa-plus"></i> Adicionar Risco
            </button>
            <div class="card p-0 mt-2">
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th width="30%">Tipo</th>
                                <th>Descrição Específica</th>
                                <th class="grid-action-cell text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="riscosGridBody">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="sinonimos" role="tabpanel" aria-labelledby="sinonimos-tab">
            <h4 class="mb-3"><i class="fas fa-tags"></i> Sinônimos e Nomes Alternativos</h4>
            <p class="text-muted">Inclua nomes alternativos usados para este cargo.</p>
            <div class="row mb-3">
                <div class="col-md-9">
                    <input type="text" class="form-control" id="sinonimoInput" placeholder="Digite um nome alternativo...">
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary w-100" id="btnAddSinonimo">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
            </div>
            <div class="card p-0 mt-2">
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Nome Alternativo</th>
                                <th class="grid-action-cell text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="sinonimosGridBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="descricoes" role="tabpanel" aria-labelledby="descricoes-tab">
            <h4 class="mb-3"><i class="fas fa-clipboard-list"></i> Responsabilidades Detalhadas</h4>
            <div class="mb-3">
                <textarea class="form-control" id="cargoResponsabilidades" name="cargoResponsabilidades" rows="5"><?php echo htmlspecialchars($cargo['cargoResponsabilidades'] ?? ''); ?></textarea>
            </div>
            <h4 class="mb-3"><i class="fas fa-layer-group"></i> Complexidade do Cargo</h4>
            <div class="mb-3">
                <textarea class="form-control" id="cargoComplexidade" name="cargoComplexidade" rows="5"><?php echo htmlspecialchars($cargo['cargoComplexidade'] ?? ''); ?></textarea>
            </div>
            <h4 class="mb-3"><i class="fas fa-cloud-sun"></i> Condições Gerais</h4>
            <div class="mb-3">
                <textarea class="form-control" id="cargoCondicoes" name="cargoCondicoes" rows="5"><?php echo htmlspecialchars($cargo['cargoCondicoes'] ?? ''); ?></textarea>
            </div>
        </div>
        
    </div>
    
    <button type="submit" class="btn btn-lg btn-success w-100 mt-3">
        <i class="fas fa-check-circle"></i> SALVAR CARGO
    </button>
    <?php if ($isEditing || $isDuplicating): ?>
        <a href="cargos.php" class="btn btn-link text-secondary w-100 mt-2">
            <i class="fas fa-arrow-left"></i> Voltar sem salvar
        </a>
    <?php endif; ?>

</form>

<div class="modal fade" id="modalAssociacaoHabilidades" tabindex="-1" aria-labelledby="modalHabilidadesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalHabilidadesLabel">Adicionar Habilidade (Hard/Soft Skill)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione uma ou mais habilidades (Ctrl/Shift) para incluir na lista do cargo.</p>
                <div class="mb-3">
                    <label for="habilidadeSelect" class="form-label">Selecione a Habilidade:</label>
                    <select class="form-select searchable-select" id="habilidadeSelect" multiple="multiple" size="10" data-placeholder="Buscar Habilidade..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($habilidadesAgrupadas as $grupoNome => $habilidadesGrupo): ?>
                            <optgroup label="<?php echo htmlspecialchars($grupoNome); ?>">
                                <?php foreach ($habilidadesGrupo as $id => $nome): ?>
                                    <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nome); ?>" data-tipo="<?php echo htmlspecialchars($grupoNome); ?>">
                                        <?php echo htmlspecialchars($nome); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarHabilidade">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoCaracteristicas" tabindex="-1" aria-labelledby="modalCaracteristicasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCaracteristicasLabel">Adicionar Características</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione uma ou mais características (Ctrl/Shift) para incluir na lista do cargo.</p>
                <div class="mb-3">
                    <label for="caracteristicaSelect" class="form-label">Selecione a Característica:</label>
                    <select class="form-select searchable-select" id="caracteristicaSelect" multiple="multiple" size="10" data-placeholder="Buscar Característica..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($caracteristicas as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarCaracteristica">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoRiscos" tabindex="-1" aria-labelledby="modalRiscosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRiscosLabel">Adicionar Risco e Detalhes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione o tipo de risco e detalhe a exposição do cargo. Adicione um por vez.</p>
                <div class="mb-3">
                    <label for="riscoSelect" class="form-label">Tipo de Risco:</label>
                    <select class="form-select searchable-select" id="riscoSelect" data-placeholder="Buscar Risco..." style="width: 100%;">
                        <option value="">--- Selecione um Risco ---</option>
                        <?php foreach ($riscos as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="mb-3">
                    <label for="riscoDescricaoInput" class="form-label">Descrição da Exposição Específica</label>
                    <textarea class="form-control" id="riscoDescricaoInput" rows="3" placeholder="Ex: Exposição prolongada ao sol acima de 30ºC e poeira por deslocamentos." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarRisco">Adicionar à Lista</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoCursos" tabindex="-1" aria-labelledby="modalCursosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCursosLabel">Adicionar Curso e Detalhes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione um ou mais cursos. Os detalhes de obrigatoriedade e observação serão aplicados a todos os cursos selecionados.</p>
                <div class="mb-3">
                    <label for="cursoSelect" class="form-label">Selecione o Curso:</label>
                    <select class="form-select searchable-select" id="cursoSelect" multiple="multiple" size="8" data-placeholder="Buscar Curso..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($cursos as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="cursoObrigatorioInput">
                        <label class="form-check-label" for="cursoObrigatorioInput">Curso Obrigatório?</label>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="cursoObsInput" class="form-label">Observação (Periodicidade, Requisito)</label>
                    <textarea class="form-control" id="cursoObsInput" rows="2" placeholder="Ex: Deve ser refeito anualmente; Recomendado para certificação."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarCurso">Adicionar à Lista</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoRecursosGrupos" tabindex="-1" aria-labelledby="modalRecursosGruposLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRecursosGruposLabel">Adicionar Grupos de Recursos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione um ou mais grupos de recursos (Ctrl/Shift) utilizados pelo cargo.</p>
                <div class="mb-3">
                    <label for="recursosGruposSelect" class="form-label">Grupos de Recursos:</label>
                    <select class="form-select searchable-select" id="recursosGruposSelect" multiple="multiple" size="8" data-placeholder="Buscar Grupo..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($recursosGrupos as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nome); ?>">
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarRecursosGrupos">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssociacaoAreasAtuacao" tabindex="-1" aria-labelledby="modalAreasAtuacaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAreasAtuacaoLabel">Adicionar Áreas de Atuação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione uma ou mais áreas (Ctrl/Shift) em que o cargo atua.</p>
                <div class="mb-3">
                    <label for="areasAtuacaoSelect" class="form-label">Áreas:</label>
                    <select class="form-select searchable-select" id="areasAtuacaoSelect" multiple="multiple" size="8" data-placeholder="Buscar Área..." style="width: 100%;">
                        <option value=""></option>
                        <?php foreach ($areasAtuacao as $id => $nomeHierarquico): ?>
                            <option value="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($nomeHierarquico); ?>">
                                <?php echo htmlspecialchars($nomeHierarquico); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAssociarAreasAtuacao">Adicionar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoHabilidade" tabindex="-1" aria-labelledby="modalEdicaoHabilidadeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEdicaoHabilidadeLabel">Editar Habilidade: <span id="habilidadeEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="habilidadeEditId">
                <div class="mb-3">
                    <label for="habilidadeEditNomeInput" class="form-label">Nome da Habilidade</label>
                    <input type="text" class="form-control" id="habilidadeEditNomeInput" readonly>
                </div>
                <div class="mb-3">
                    <label for="habilidadeEditTipo" class="form-label">Tipo da Habilidade</label>
                    <input type="text" class="form-control" id="habilidadeEditTipo" readonly>
                </div>
                <div class="alert alert-warning">
                    Para trocar a Habilidade, você deve remover a atual e adicionar a nova. Este modal só permite ver os detalhes.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoCaracteristica" tabindex="-1" aria-labelledby="modalEdicaoCaracteristicaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEdicaoCaracteristicaLabel">Editar Característica: <span id="caracteristicaEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="caracteristicaEditId">
                <div class="mb-3">
                    <label for="caracteristicaEditNomeInput" class="form-label">Nome da Característica</label>
                    <input type="text" class="form-control" id="caracteristicaEditNomeInput" readonly>
                </div>
                <div class="alert alert-warning">
                    Para trocar a Característica, você deve remover a atual e adicionar a nova. Este modal só permite ver os detalhes.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoRecursoGrupo" tabindex="-1" aria-labelledby="modalEdicaoRecursoGrupoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEdicaoRecursoGrupoLabel">Editar Grupo de Recurso: <span id="recursoGrupoEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="recursoGrupoEditId">
                <div class="mb-3">
                    <label for="recursoGrupoEditNomeInput" class="form-label">Nome do Grupo de Recurso</label>
                    <input type="text" class="form-control" id="recursoGrupoEditNomeInput" readonly>
                </div>
                 <div class="alert alert-warning">
                    Para trocar o Grupo de Recurso, você deve remover o atual e adicionar o novo. Este modal só permite ver os detalhes.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoCurso" tabindex="-1" aria-labelledby="modalEdicaoCursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEdicaoCursoLabel">Editar Curso: <span id="cursoEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cursoEditId">
                 <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="cursoEditObrigatorio">
                        <label class="form-check-label" for="cursoEditObrigatorio">Curso Obrigatório?</label>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="cursoEditObs" class="form-label">Observação (Periodicidade, Requisito)</label>
                    <textarea class="form-control" id="cursoEditObs" rows="3" placeholder="Ex: Deve ser refeito anualmente; Recomendado para certificação."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info text-white" id="btnSalvarEdicaoCurso">Salvar Edição</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoRisco" tabindex="-1" aria-labelledby="modalEdicaoRiscoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEdicaoRiscoLabel">Editar Risco: <span id="riscoEditNome"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="riscoEditId">
                <div class="mb-3">
                    <label for="riscoEditDescricao" class="form-label">Descrição da Exposição Específica</label>
                    <textarea class="form-control" id="riscoEditDescricao" rows="4" placeholder="Ex: Exposição prolongada ao sol acima de 30ºC e poeira por deslocamentos." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info text-white" id="btnSalvarEdicaoRisco">Salvar Edição</button>
            </div>
        </div>
    </div>
</div>

<?php
// ======================================================
// AJUSTE: Lista de scripts específicos desta página (o footer imprime $page_scripts)
// ======================================================
$page_scripts = [
    // jQuery first (Select2 and our script depend on it)
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
    '../scripts/cargos_form.js'
];

include '../includes/footer.php';
?>  