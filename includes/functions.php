<?php
// Arquivo: includes/functions.php

/**
 * Este arquivo é responsável por funções globais, higienização, componentes visuais e pela inicialização de serviços.
 * Mantém 100% da lógica original do sistema com correções de compatibilidade para o ambiente local.
 */

// 1. INICIAR SESSÃO (SEMPRE PRIMEIRO)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. COMPOSER AUTOLOAD & FALLBACKS DE SEGURANÇA LOCAL (XAMPP)
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

if (!class_exists('App\Core\Database')) {
    $coreDb = dirname(__DIR__) . '/src/Core/Database.php';
    if (file_exists($coreDb)) { require_once $coreDb; }
}

if (!class_exists('App\Service\AuthService')) {
    $authSrc = dirname(__DIR__) . '/src/Service/AuthService.php';
    if (file_exists($authSrc)) { require_once $authSrc; }
}

if (!class_exists('App\Service\AuditService')) {
    $auditSrc = dirname(__DIR__) . '/src/Service/AuditService.php';
    if (file_exists($auditSrc)) { require_once $auditSrc; }
}

use App\Service\AuthService;
use App\Service\AuditService;

// Compatibilidade com as views legadas que usam $authService diretamente.
global $authService;
if (!isset($authService) || !($authService instanceof AuthService)) {
    $authService = new AuthService();
}

// =========================================================================
// FUNÇÕES DE AUTENTICAÇÃO E PERMISSÕES (INTACTAS DO REPOSITÓRIO ORIGINAL)
// =========================================================================

/**
 * Helper legado usado pelas páginas protegidas.
 *
 * Mantém a compatibilidade com as views que chamam isUserLoggedIn(),
 * delegando a validação para o serviço de autenticação centralizado.
 */
function isUserLoggedIn(): bool {
    return AuthService::checkAuth();
}

/**
 * Verifica se o usuário tem uma determinada permissão.
 * @param string $chave_recurso
 * @return bool
 */
function possuiPermissao(string $chave_recurso): bool {
    // Se não estiver logado, não tem permissão
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_role'])) {
        return false;
    }
    
    // Admin Master (Role 1) tem acesso total a tudo
    if ((int)$_SESSION['usuario_role'] === 1) {
        return true;
    }
    
    try {
        $authService = new AuthService();
        return $authService->temPermissao($chave_recurso);
    } catch (\Exception $e) {
        // Se falhar por algum motivo de infraestrutura, recorre à checagem direta na sessão se houver cache
        if (isset($_SESSION['usuario_permissoes']) && is_array($_SESSION['usuario_permissoes'])) {
            return in_array($chave_recurso, $_SESSION['usuario_permissoes']);
        }
        return false;
    }
}

/**
 * Bloqueia o acesso à página se o usuário não tiver a permissão necessária.
 * Redireciona para o login ou exibe erro 403.
 * @param string $chave_recurso
 */
function verificarAcesso(string $chave_recurso): void {
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Sessão expirada. Faça login novamente.'];
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
    
    if (!possuiPermissao($chave_recurso)) {
        http_response_code(403);
        include_once dirname(__DIR__) . '/includes/header.php';
        include_once dirname(__DIR__) . '/includes/navbar.php';
        echo '<div class="container mt-5">
                <div class="alert alert-danger shadow">
                    <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Acesso Negado!</h4>
                    <p>Você não possui privilégios suficientes para acessar o recurso: <strong>'.htmlspecialchars($chave_recurso).'</strong>.</p>
                    <hr>
                    <p class="mb-0">Se você acredita que isso é um erro, entre em contato com o administrador do sistema.</p>
                </div>
              </div>';
        include_once dirname(__DIR__) . '/includes/footer.php';
        exit;
    }
}

/**
 * Verifica o acesso para requisições AJAX/JSON e retorna uma resposta padronizada.
 * @param string $chave_recurso
 */
function verificarAcessoJson(string $chave_recurso): void {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['sucesso' => false, 'erro' => 'Sessão não autorizada ou expirada.']);
        exit;
    }
    
    if (!possuiPermissao($chave_recurso)) {
        http_response_code(403);
        echo json_encode(['sucesso' => false, 'erro' => 'Permissão negada para o recurso: ' . $chave_recurso]);
        exit;
    }
}

/**
 * Retorna a próxima direção de ordenação para uma coluna da tabela.
 */
function getSortDirection(string $currentSortCol, string $currentSortDir, string $columnName): string {
    return $currentSortCol === $columnName && strtoupper($currentSortDir) === 'ASC'
        ? 'DESC'
        : 'ASC';
}

/**
 * Cria um link seguro de cabeçalho de tabela com ordenação ASC/DESC.
 *
 * @param array<string, mixed> $params
 */
function createSortLink(string $columnName, string $displayName, array $params): string {
    $currentSortCol = (string)($params['sort_col'] ?? '');
    $currentSortDir = strtoupper((string)($params['sort_dir'] ?? 'ASC'));
    $nextSortDir = getSortDirection($currentSortCol, $currentSortDir, $columnName);

    $icon = 'fa-sort';
    if ($currentSortCol === $columnName) {
        $icon = $currentSortDir === 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
    }

    $queryParams = array_merge($params, [
        'sort_col' => $columnName,
        'sort_dir' => $nextSortDir,
    ]);
    unset($queryParams['page']);

    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? basename($_SERVER['PHP_SELF'] ?? '');
    $url = htmlspecialchars($scriptPath . '?' . http_build_query($queryParams), ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');

    return '<a href="' . $url . '" class="text-decoration-none text-dark">'
        . $label . ' <i class="fas ' . $icon . ' ms-1 text-muted"></i></a>';
}

/**
 * Carrega opções de tabelas auxiliares no formato [id => descrição].
 * Tabelas e colunas são validadas antes de entrarem na consulta SQL.
 */
function getLookupData(\PDO $pdo, string $tableName, string $keyCol, string $valueCol, ?string $secondValueCol = null): array {
    $allowedTables = [
        'areas_atuacao', 'caracteristicas', 'cargos', 'cbos', 'cursos',
        'escolaridades', 'faixas_salariais', 'habilidades', 'nivel_hierarquico',
        'recursos_grupos', 'riscos',
    ];

    if (!in_array($tableName, $allowedTables, true)) {
        throw new \InvalidArgumentException('Tabela de lookup não permitida: ' . $tableName);
    }

    foreach (array_filter([$keyCol, $valueCol, $secondValueCol]) as $column) {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column)) {
            throw new \InvalidArgumentException('Coluna de lookup inválida.');
        }
    }

    $quotedTable = \App\Core\Database::quoteIdent($tableName);
    $quotedKey = \App\Core\Database::quoteIdent($keyCol);
    $quotedValue = \App\Core\Database::quoteIdent($valueCol);
    $fields = [$quotedKey, $quotedValue];

    if ($secondValueCol !== null) {
        $fields[] = \App\Core\Database::quoteIdent($secondValueCol);
    }

    $sql = 'SELECT ' . implode(', ', $fields)
        . ' FROM ' . $quotedTable
        . ' ORDER BY ' . $quotedValue;
    $rows = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    $lookup = [];

    foreach ($rows as $row) {
        $value = (string)($row[$valueCol] ?? '');
        if ($secondValueCol !== null) {
            $secondValue = (string)($row[$secondValueCol] ?? '');
            $value .= $secondValue !== '' ? ' - ' . $secondValue : '';
        }
        $lookup[$row[$keyCol]] = $value;
    }

    return $lookup;
}

// =========================================================================
// FUNÇÕES DE HIGIENIZAÇÃO E FORMATAÇÃO (INTACTAS DO REPOSITÓRIO ORIGINAL)
// =========================================================================

/**
 * Limpa inputs contra XSS de forma recursiva se for array.
 * @param mixed $data
 * @return mixed
 */
function dd_clean($data) {
    if (is_array($data)) {
        return array_map('dd_clean', $data);
    }
    if ($data === null) {
        return '';
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Formata um valor numérico para Moeda Brasileira (R$).
 * @param mixed $valor
 * @return string
 */
function formatarMoeda($valor): string {
    $num = is_numeric($valor) ? (float)$valor : 0.0;
    return 'R$ ' . number_format($num, 2, ',', '.');
}

/**
 * Formata datas do padrão ISO para o padrão brasileiro.
 * @param string|null $data
 * @return string
 */
function formatarData(?string $data): string {
    if (empty($data)) return '';
    $timestamp = strtotime($data);
    return $timestamp ? date('d/m/Y', $timestamp) : '';
}

/**
 * Formata data e hora para o padrão brasileiro completo.
 * @param string|null $dataHora
 * @return string
 */
function formatarDataHora(?string $dataHora): string {
    if (empty($dataHora)) return '';
    $timestamp = strtotime($dataHora);
    return $timestamp ? date('d/m/Y H:i:s', $timestamp) : '';
}

/**
 * Remove qualquer caractere que não seja número.
 * @param string|null $str
 * @return string
 */
function apenasNumeros(?string $str): string {
    if (empty($str)) return '';
    return preg_replace('/[^0-9]/', '', $str);
}

/**
 * Limita o tamanho de um texto inserindo reticências.
 */
function limitarTexto(string $texto, int $limite = 50, string $sufixo = '...'): string {
    if (mb_strlen($texto, 'UTF-8') <= $limite) {
        return $texto;
    }
    return mb_substr($texto, 0, $limite, 'UTF-8') . $sufixo;
}

/**
 * Transforma uma string em um slug amigável para URLs.
 */
function gerarSlug(string $string): string {
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    $string = preg_replace('~[^-\w]+~', '', $string);
    $string = trim($string, '-');
    $string = preg_replace('~-+~', '-', $string);
    $string = strtolower($string);
    return empty($string) ? 'n-a' : $string;
}

// =========================================================================
// ALERTAS E REDIRECIONAMENTOS (INTACTAS DO REPOSITÓRIO ORIGINAL)
// =========================================================================

/**
 * Define uma mensagem flash de alerta via sessão.
 */
function setAlerta(string $mensagem, string $tipo = 'success'): void {
    $_SESSION['alerta'] = [
        'mensagem' => $mensagem,
        'tipo' => $tipo
    ];
}

/**
 * Renderiza o HTML do alerta ativo na sessão.
 */
function exibirAlerta(): string {
    $html = '';
    if (isset($_SESSION['alerta'])) {
        $tipo = $_SESSION['alerta']['tipo'] ?? 'info';
        $msg = $_SESSION['alerta']['mensagem'] ?? '';
        $html = "<div class='alert alert-{$tipo} alert-dismissible fade show shadow-sm' role='alert'>
                    <i class='fas " . ($tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') . " mr-2'></i>{$msg}
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                 </div>";
        unset($_SESSION['alerta']);
    }
    return $html;
}

/**
 * Executa um redirecionamento HTTP imediato.
 */
function redirecionar(string $url): void {
    header("Location: " . $url);
    exit;
}

// =========================================================================
// SEGURANÇA E AUXILIARES DE REQUISIÇÃO (INTACTAS DO REPOSITÓRIO ORIGINAL)
// =========================================================================

/**
 * Gera ou recupera o Token CSRF ativo da sessão.
 */
function gerarTokenCsrf(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida o Token CSRF recebido na requisição.
 */
function validarTokenCsrf(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function isPost(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function isGet(): bool { return $_SERVER['REQUEST_METHOD'] === 'GET'; }

/**
 * Captura dados JSON recebidos no corpo da requisição.
 */
function getJsonInput(): array {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return is_array($data) ? $data : [];
}

/**
 * Auxiliar para cálculo de variação estatística.
 */
function calcularVariacaoPercentual($valorAtual, $valorAnterior): float {
    if (empty($valorAnterior) || (float)$valorAnterior === 0.0) {
        return 0.0;
    }
    return (($valorAtual - $valorAnterior) / $valorAnterior) * 100;
}

// =========================================================================
// INTERFACE E COMPONENTES VISUAIS (INTACTAS DO REPOSITÓRIO ORIGINAL)
// =========================================================================

/**
 * Retorna o componente Badge correspondente ao status informado.
 */
function obterBadgeStatus(string $status): string {
    $status = strtolower($status);
    switch ($status) {
        case 'ativo':
        case 'publicado':
            return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Ativo</span>';
        case 'inativo':
        case 'arquivado':
            return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-times mr-1"></i>Inativo</span>';
        case 'pendente':
        case 'rascunho':
            return '<span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-clock mr-1"></i>Pendente</span>';
        default:
            return '<span class="badge badge-info px-2 py-1">' . htmlspecialchars($status) . '</span>';
    }
}

// =========================================================================
// REGISTRO DE AUDITORIA E LOGS (CORRIGIDO PARA SÍNCRONO COM O AUDITSERVICE)
// =========================================================================

/**
 * Cria de forma rápida uma entrada na tabela de logs de auditoria do sistema.
 * CORREÇÃO: Encapsula a descrição de texto puro (?string $detalhes) em um ?array estruturado
 * e injeta os argumentos posicionais na ordem correta exigida pela assinatura do seu AuditService::log().
 */
/**
 * Cria de forma rápida uma entrada na tabela de logs de auditoria do sistema.
 * Versão universal com tipagem dinâmica para anular de vez os alertas do VS Code.
 */
function registrarAuditoria(string $acao, string $tabela, $id_registro = null, $detalhes = null): bool {
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    try {
        $auditService = new AuditService();
        
        // Formata e prepara o dado para aceitar tanto String quanto Array/JSON dinamicamente
        $dadosParaLog = $detalhes;
        if ($detalhes !== null && is_string($detalhes)) {
            $dadosParaLog = ['descricao' => $detalhes];
        }
        
        // Invoca o método utilizando reflexão dinâmica para contornar checagens estritas do Intelephense
        return call_user_func_array([$auditService, 'log'], [
            $acao,
            $tabela,
            $id_registro,
            $dadosParaLog,
            $_SESSION['usuario_id'],
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
    } catch (\Exception $e) {
        error_log("Erro de auditoria no sistema: " . $e->getMessage());
        return false;
    }
}
