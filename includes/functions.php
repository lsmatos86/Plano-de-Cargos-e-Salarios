<?php
// Arquivo: includes/functions.php
// Este arquivo DEVE ser incluído APÓS o config.php

// Lista de tabelas mestras permitidas para operações genéricas de leitura/exclusão.
// Usado para evitar injeção SQL no nome da tabela.
const ALLOWED_TABLES = [
    'cargos', 'escolaridades', 'cbos', 'caracteristicas', 'cursos', 'riscos', 
    'habilidades', 'usuarios', 'familia_cbo', 'recursos', 'recursos_grupos',
    'faixas_salariais',             // <-- TABELA NOVA (FAIXAS SALARIAIS)
    'areas_atuacao',                // <-- TABELA NOVA (ÁREAS)
    'cargos_area',                  // <-- TABELA NOVA (JUNÇÃO DE CARGOS X ÁREAS)
    'tipo_hierarquia',              // <-- TABELA NOVA (TIPO HIERÁRQUICO)
    'nivel_hierarquico',            // <-- TABELA NOVA (NÍVEL HIERÁRQUICO)
    'habilidades_cargo', 'caracteristicas_cargo', 'riscos_cargo', 'cursos_cargo', 
    'cargo_sinonimos', 'recursos_grupos_cargo', 'recurso_grupo_recurso'
];

/**
 * Valida se um nome de tabela é seguro.
 */
function isValidTableName(string $tableName): bool {
    return in_array(strtolower($tableName), ALLOWED_TABLES);
}

// ----------------------------------------------------
// 1. FUNÇÃO: CONEXÃO COM O BANCO DE DADOS (PDO)
// ----------------------------------------------------
/**
 * Retorna uma nova instância de conexão PDO com o banco de dados.
 * Depende das constantes definidas em config.php.
 * @return PDO A conexão PDO ativa.
 */
function getDbConnection(): PDO {
    // Variáveis definidas em config.php
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (\PDOException $e) {
        // Exibe erro de conexão e encerra o script
        die("Erro de Conexão com o Banco de Dados: " . $e->getMessage());
    }
}

// ----------------------------------------------------
// 2. FUNÇÃO: INSERIR REGISTRO SIMPLES (CREATE) - Aprimorada com validação
// ----------------------------------------------------
/**
 * Insere um novo registro em uma tabela simples (que recebe apenas um campo de texto/nome).
 * @param PDO $pdo A conexão PDO ativa.
 * @param string $tableName O nome da tabela (ex: 'escolaridades').
 * @param string $columnName O nome da coluna a ser preenchida (ex: 'escolaridadeTitulo').
 * @param string $value O valor a ser inserido.
 * @return bool True em caso de sucesso, false em caso de falha.
 */
function insertSimpleRecord(PDO $pdo, string $tableName, string $columnName, string $value): bool
{
    // Validação de nomes de tabela e coluna via Whitelist ou Regex
    if (!isValidTableName($tableName) || !preg_match('/^[a-zA-Z0-9_]+$/', $columnName)) {
        error_log("Tentativa de insert em tabela ou coluna inválida: {$tableName}.{$columnName}");
        return false;
    }
    
    // Usa Prepared Statement para o valor ($value)
    $sql = "INSERT INTO {$tableName} ({$columnName}) VALUES (?)";
    
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$value]);
    } catch (PDOException $e) {
        error_log("Erro de INSERT em {$tableName}: " . $e->getMessage());
        return false;
    }
}

// ----------------------------------------------------
// 3. FUNÇÃO: LISTAR REGISTROS COM FILTRO, ORDENAÇÃO E PAGINAÇÃO (READ)
// ----------------------------------------------------
/**
 * Retorna todos os registros de uma tabela, aplicando filtros, ordenação e limites.
 * @param PDO $pdo A conexão PDO ativa.
 * @param string $tableName O nome da tabela.
 * @param string $idColumn O nome da coluna ID (PK).
 * @param string $nameColumn O nome da coluna de texto principal (para filtro/ordem).
 * @param array $params Parâmetros (term, order_by, sort_dir, limit, offset).
 * @return array Uma lista de registros.
 */
function getRecords(PDO $pdo, string $tableName, string $idColumn, string $nameColumn, array $params): array
{
    if (!isValidTableName($tableName)) {
        error_log("Tentativa de SELECT em tabela inválida: {$tableName}");
        return [];
    }

    $term = $params['term'] ?? '';
    $orderBy = $params['order_by'] ?? $idColumn;
    $sortDir = $params['sort_dir'] ?? 'ASC';
    $limit = (int)($params['limit'] ?? 0);
    $offset = (int)($params['offset'] ?? 0);

    // 1. Validar e sanitizar a ordenação
    $validDirs = ['ASC', 'DESC'];
    $sortDir = in_array(strtoupper($sortDir), $validDirs) ? strtoupper($sortDir) : 'ASC';
    
    // 2. Definir colunas válidas (aprimorado para segurança)
    $validColumns = [$idColumn, $nameColumn, $idColumn.'DataCadastro', $idColumn.'DataAtualizacao', 'recursoDescricao', 'nome', 'email', 'ativo'];
    
    if (!in_array($orderBy, $validColumns)) {
        $orderBy = $idColumn;
    }
    
    // 3. Montar a query com o filtro
    $sql = "SELECT * FROM {$tableName}";
    $bindings = [];
    
    if (!empty($term)) {
        $sql .= " WHERE {$nameColumn} LIKE ?";
        // Se a tabela for 'usuarios', adiciona filtro por e-mail também
        if ($tableName === 'usuarios') {
            $sql .= " OR email LIKE ?";
            $bindings[] = "%{$term}%";
        }
        $bindings[] = "%{$term}%";
    }
    
    // 4. Conclui a ordenação e PAGINAÇÃO
    $sql .= " ORDER BY {$orderBy} {$sortDir}";

    if ($limit > 0) {
        $sql .= " LIMIT {$limit} OFFSET {$offset}";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erro na query de {$tableName}: " . $e->getMessage() . " SQL: " . $sql);
        return [];
    }
}

/**
 * Retorna a contagem total de registros de uma tabela, aplicando filtro se houver.
 */
function countRecordsWithFilter(PDO $pdo, string $tableName, string $nameColumn, string $term = ''): int {
    if (!isValidTableName($tableName)) {
        return 0;
    }
    
    $sql = "SELECT COUNT(*) FROM {$tableName}";
    $bindings = [];
    
    if (!empty($term)) {
        $sql .= " WHERE {$nameColumn} LIKE ?";
        // Se a tabela for 'usuarios', adiciona filtro por e-mail também
        if ($tableName === 'usuarios') {
            $sql .= " OR email LIKE ?";
            $bindings[] = "%{$term}%";
        }
        $bindings[] = "%{$term}%";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}


// ----------------------------------------------------
// 4. FUNÇÃO: EXCLUIR REGISTRO (DELETE) - Aprimorada com validação
// ----------------------------------------------------
/**
 * Exclui um registro da tabela pelo ID, com segurança de nome de tabela.
 * @param PDO $pdo A conexão PDO ativa.
 * @param string $tableName O nome da tabela.
 * @param string $idColumn O nome da coluna ID.
 * @param int|string $id O ID do registro a ser excluído.
 * @return int O número de linhas afetadas (0 ou 1).
 */
function deleteRecord(PDO $pdo, string $tableName, string $idColumn, $id): int
{
    // 1. Validação de nome de tabela e coluna
    if (!isValidTableName($tableName) || !preg_match('/^[a-zA-Z0-9_]+$/', $idColumn)) {
        error_log("Tentativa de DELETE em tabela ou coluna inválida: {$tableName}.{$idColumn}");
        return 0;
    }

    try {
        // 2. Uso de Prepared Statement para o valor do ID
        $stmt = $pdo->prepare("DELETE FROM {$tableName} WHERE {$idColumn} = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        // Retorna 0 se houver falha na exclusão (geralmente por causa de FOREIGN KEY)
        return 0; 
    }
}

// ----------------------------------------------------
// NOVO: FUNÇÃO PARA LIMPAR RELACIONAMENTOS N:M DE CARGO (Melhoria na Exclusão)
// ----------------------------------------------------
/**
 * Remove todas as referências de um Cargo em suas tabelas de junção N:M.
 * Uso obrigatório em operações de exclusão de cargo.
 * @param PDO $pdo A conexão PDO ativa.
 * @param int $cargoId O ID do cargo.
 * @return bool True se a limpeza for bem-sucedida, false caso contrário.
 */
function clearCargoRelationships(PDO $pdo, int $cargoId): bool
{
    $joinTables = [
        'habilidades_cargo', 'caracteristicas_cargo', 'riscos_cargo', 
        'cargo_sinonimos', 'cursos_cargo', 'recursos_grupos_cargo',
        'cargos_area' // <-- NOVA TABELA DE JUNÇÃO INCLUÍDA
    ];
    $success = true;

    foreach ($joinTables as $table) {
        try {
            // Usa prepared statement para garantir segurança e tratar o cargoId
            $stmt = $pdo->prepare("DELETE FROM {$table} WHERE cargoId = ?");
            $stmt->execute([$cargoId]);
        } catch (PDOException $e) {
            error_log("Falha ao limpar relacionamento N:M na tabela {$table} para Cargo ID {$cargoId}: " . $e->getMessage());
            $success = false;
        }
    }
    return $success;
}

// ----------------------------------------------------
// 5. FUNÇÃO: OBTER DADOS PARA LOOKUP/SELECTS (AJUSTADO PARA CONCATENAÇÃO CBO)
// ----------------------------------------------------
/**
 * Retorna todos os registros de uma tabela formatados como array [id => nome/concatenado].
 * Usado para popular SELECTs.
 * @return array Um array formatado [id => nome].
 */
function getLookupData(PDO $pdo, string $tableName, string $idColumn, string $nameColumn, string $concatColumn = null): array
{
    if (!isValidTableName($tableName)) {
        error_log("Tentativa de Lookup em tabela inválida: {$tableName}");
        return [];
    }

    try {
        $selectFields = $idColumn . ', ' . $nameColumn;
        
        // Se for a tabela CBO, concatena o código (cboNome) e o título oficial
        if ($concatColumn && $tableName === 'cbos') {
            $selectFields = "{$idColumn}, CONCAT({$nameColumn}, ' - ', {$concatColumn}) AS display_name";
            $nameColumn = 'display_name'; // Usa o alias para o fetch
        }

        $stmt = $pdo->query("SELECT {$selectFields} FROM {$tableName} ORDER BY {$nameColumn} ASC");
        
        // Se a query foi alterada para concatenação, busca pelo alias 'display_name'
        if ($concatColumn && $tableName === 'cbos') {
            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[$row[$idColumn]] = $row['display_name'];
            }
            return $results;
        }

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna array formatado [id => nome]

    } catch (PDOException $e) {
        error_log("Erro no Lookup da tabela {$tableName}: " . $e->getMessage());
        return [];
    }
}

// ----------------------------------------------------
// 6. FUNÇÃO: OBTER OPÇÕES ENUM (Para a tabela 'riscos' e 'habilidades')
// ----------------------------------------------------
/**
 * Retorna as opções válidas para um campo ENUM do MySQL.
 * @param PDO $pdo O conexão PDO ativa.
 * @param string $tableName O nome da tabela.
 * @param string $columnName O nome da coluna ENUM.
 * @return array Uma lista de strings com os valores ENUM.
 */
function getEnumOptions(PDO $pdo, string $tableName, string $columnName): array
{
    if (!isValidTableName($tableName)) {
        return [];
    }

    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM {$tableName} LIKE '{$columnName}'");
        $row = $stmt->fetch();

        if (isset($row['Type']) && strpos($row['Type'], 'enum') !== false) {
            $enum_string = $row['Type'];
            $matches = [];
            preg_match_all('/\'([^\']+)\'/', $enum_string, $matches);
            return $matches[1];
        }
    } catch (PDOException $e) {
        // Falha silenciosa
    }
    return [];
}


// ----------------------------------------------------
// 7. FUNÇÕES DE AUTENTICAÇÃO E SESSÃO (USANDO TABELA USUARIOS)
// ----------------------------------------------------
/**
 * Inicia a sessão se ainda não estiver ativa.
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Autentica o usuário usando email e verifica o hash da senha no banco de dados.
 * @param string $email O email/username fornecido.
 * @param string $password A senha bruta fornecida.
 * @return bool True se o login for bem-sucedido.
 */
function authenticateUser($email, $password) {
    startSession();

    try {
        $pdo = getDbConnection();
        // A query busca o usuário por email e garante que ele esteja ativo
        $stmt = $pdo->prepare("SELECT usuarioId, nome, email, senha, ativo FROM usuarios WHERE email = ? AND ativo = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }

    // Verifica se o usuário existe e se a senha corresponde ao hash armazenado
    if ($user && password_verify($password, $user['senha'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['usuarioId'];
        $_SESSION['username'] = $user['nome']; 
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}

/**
 * Verifica se o usuário está logado.
 * @return bool
 */
function isUserLoggedIn() {
    startSession();
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// ----------------------------------------------------
// 8. FUNÇÃO AUXILIAR DE ORDENAÇÃO (para as views)
// ----------------------------------------------------
/**
 * Alterna a direção da ordenação para um link de coluna na Datagrid.
 */
function getSortDirection($current_order, $column, $default_dir = 'ASC') {
    if ($current_order === $column) {
        // Pega o sort_dir atual da URL (ou o padrão) e inverte
        return ($_GET['sort_dir'] ?? $default_dir) === 'ASC' ? 'DESC' : 'ASC';
    }
    return $default_dir;
}

// ----------------------------------------------------
// 9. FUNÇÃO PARA CARREGAR DADOS COMPLETOS DO CARGO (RELATÓRIOS) - ATUALIZADA
// ----------------------------------------------------
/**
 * Carrega todos os dados de um cargo, incluindo seus relacionamentos N:M, para relatórios.
 * (Inclui Faixa Salarial, Áreas de Atuação e Hierarquia de Cargos)
 * @param PDO $pdo A conexão PDO ativa.
 * @param int $cargoId O ID do cargo.
 * @return array|null Os dados completos do cargo ou null se não encontrado.
 */
function getCargoReportData(PDO $pdo, int $cargoId): ?array
{
    if ($cargoId <= 0) return null;

    $data = [];

    try {
        // 1. DADOS BÁSICOS, FAIXA SALARIAL, NÍVEL HIERÁRQUICO e SUPERVISOR (NOVOS JOINS)
        $stmt = $pdo->prepare("
            SELECT 
                c.*, 
                e.escolaridadeTitulo,
                b.cboNome,
                b.cboTituloOficial,
                f.faixaNivel,               
                f.faixaSalarioMinimo,       
                f.faixaSalarioMaximo,
                n.nivelOrdem,                       -- NOVO CAMPO NÍVEL
                t.tipoNome AS tipoHierarquiaNome,   -- NOVO CAMPO TIPO
                sup.cargoNome AS cargoSupervisorNome -- NOVO CAMPO SUPERVISOR
            FROM cargos c
            LEFT JOIN escolaridades e ON e.escolaridadeId = c.escolaridadeId
            LEFT JOIN cbos b ON b.cboId = c.cboId
            LEFT JOIN faixas_salariais f ON f.faixaId = c.faixaId
            LEFT JOIN nivel_hierarquico n ON n.nivelId = c.nivelHierarquicoId 
            LEFT JOIN tipo_hierarquia t ON t.tipoId = n.tipoId                
            LEFT JOIN cargos sup ON sup.cargoId = c.cargoSupervisorId         
            WHERE c.cargoId = ?
        ");
        $stmt->execute([$cargoId]);
        $cargo = $stmt->fetch();

        // Se o Cargo principal não existe, retorna null
        if (!$cargo) return null;
        $data['cargo'] = $cargo;
        
        // ------------------------------------------------
        // 2. BUSCA DE RELACIONAMENTOS N:M 
        // ------------------------------------------------

        // 2.1. HABILIDADES
        try {
            $stmt_hab = $pdo->prepare("SELECT h.habilidadeNome, h.habilidadeTipo, h.habilidadeDescricao FROM habilidades_cargo hc JOIN habilidades h ON h.habilidadeId = hc.habilidadeId WHERE hc.cargoId = ? ORDER BY h.habilidadeTipo DESC, h.habilidadeNome ASC");
            $stmt_hab->execute([$cargoId]);
            $data['habilidades'] = $stmt_hab->fetchAll();
        } catch (Exception $e) { $data['habilidades'] = []; error_log("Erro em Habilidades para Cargo ID {$cargoId}: " . $e->getMessage()); }

        // 2.2. CARACTERÍSTICAS
        try {
            $stmt_car = $pdo->prepare("SELECT c.caracteristicaNome, c.caracteristicaDescricao FROM caracteristicas_cargo cc JOIN caracteristicas c ON c.caracteristicaId = cc.caracteristicaId WHERE cc.cargoId = ? ORDER BY c.caracteristicaNome ASC");
            $stmt_car->execute([$cargoId]);
            $data['caracteristicas'] = $stmt_car->fetchAll();
        } catch (Exception $e) { $data['caracteristicas'] = []; error_log("Erro em Características para Cargo ID {$cargoId}: " . $e->getMessage()); }
        
        // 2.3. RISCOS
        try {
            $stmt_ris = $pdo->prepare("SELECT r.riscoNome, rc.riscoDescricao FROM riscos_cargo rc JOIN riscos r ON r.riscoId = rc.riscoId WHERE rc.cargoId = ? ORDER BY r.riscoNome ASC");
            $stmt_ris->execute([$cargoId]);
            $data['riscos'] = $stmt_ris->fetchAll();
        } catch (Exception $e) { $data['riscos'] = []; error_log("Erro em Riscos para Cargo ID {$cargoId}: " . $e->getMessage()); }
        
        // 2.4. CURSOS
        try {
            $stmt_cur = $pdo->prepare("SELECT cur.cursoNome, c_c.cursoCargoObrigatorio, c_c.cursoCargoObs FROM cursos_cargo c_c JOIN cursos cur ON cur.cursoId = c_c.cursoId WHERE c_c.cargoId = ? ORDER BY c_c.cursoCargoObrigatorio DESC, cur.cursoNome ASC");
            $stmt_cur->execute([$cargoId]);
            $data['cursos'] = $stmt_cur->fetchAll();
        } catch (Exception $e) { $data['cursos'] = []; error_log("Erro em Cursos para Cargo ID {$cargoId}: " . $e->getMessage()); }


        // 2.5. SINÔNIMOS
        try {
            $stmt_sin = $pdo->prepare("SELECT cargoSinonimoNome FROM cargo_sinonimos WHERE cargoId = ?"); 
            $stmt_sin->execute([$cargoId]);
            $data['sinonimos'] = $stmt_sin->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) { $data['sinonimos'] = []; error_log("Erro em Sinônimos para Cargo ID {$cargoId}: " . $e->getMessage()); }
        
        // 2.6. GRUPOS DE RECURSOS
        try {
            $stmt_rec = $pdo->prepare("SELECT rg.recursoGrupoNome FROM recursos_grupos_cargo rcg JOIN recursos_grupos rg ON rg.recursoGrupoId = rcg.recursoGrupoId WHERE rcg.cargoId = ? ORDER BY rg.recursoGrupoNome ASC");
            $stmt_rec->execute([$cargoId]);
            $data['recursos_grupos'] = $stmt_rec->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) { $data['recursos_grupos'] = []; error_log("Erro em Recursos Grupos para Cargo ID {$cargoId}: " . $e->getMessage()); }
        
        // 2.7. ÁREAS DE ATUAÇÃO
        try {
            $stmt_areas = $pdo->prepare("SELECT a.areaNome FROM cargos_area ca JOIN areas_atuacao a ON a.areaId = ca.areaId WHERE ca.cargoId = ? ORDER BY a.areaNome ASC");
            $stmt_areas->execute([$cargoId]);
            $data['areas_atuacao'] = $stmt_areas->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) { $data['areas_atuacao'] = []; error_log("Erro em Áreas de Atuação para Cargo ID {$cargoId}: " . $e->getMessage()); }


        return $data;

    } catch (Exception $e) {
        // Captura o erro fatal da query principal (se a conexão falhar ou se o SELECT base tiver problema)
        error_log("Erro fatal na função principal do relatório Cargo ID {$cargoId}: " . $e->getMessage());
        return null;
    }
}
/**
 * Mapeia o nome do risco para um ícone Font Awesome com estilo.
 * @param string $riscoNome
 * @return string HTML do ícone.
 */
function getRiscoIcon(string $riscoNome): string {
    $map = [
        'Físico' => '<i class="fas fa-sun" style="color:#f90;"></i>',
        'Químico' => '<i class="fas fa-flask" style="color:#09f;"></i>',
        'Ergonômico' => '<i class="fas fa-chair" style="color:#888;"></i>',
        'Psicossocial' => '<i class="fas fa-brain" style="color:#e66;"></i>',
        'Acidental' => '<i class="fas fa-exclamation-triangle" style="color:#f00;"></i>'
    ];
    return $map[$riscoNome] ?? '<i class="fas fa-dot-circle" style="color:#999;"></i>';
}

// ----------------------------------------------------
// 10. FUNÇÕES AUXILIARES PARA HIERARQUIA DE ÁREAS
// ----------------------------------------------------

/**
 * Retorna as áreas para o Lookup de seleção, formatadas para mostrar o caminho hierárquico completo.
 * @param PDO $pdo A conexão PDO ativa.
 * @return array Um array formatado [areaId => 'Pai > Filho > Nome da Área'].
 */
function getAreaHierarchyLookup(PDO $pdo): array 
{
    try {
        $stmt = $pdo->query("SELECT areaId, areaPaiId, areaNome FROM areas_atuacao");
        $flatList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar áreas para lookup: " . $e->getMessage());
        return [];
    }

    $lookup = [];
    $allAreas = array_column($flatList, null, 'areaId');

    // Mapeia para exibir o caminho completo (ex: Diretoria > Financeiro)
    foreach ($flatList as $area) {
        $path = [$area['areaNome']];
        $currentId = $area['areaPaiId'];
        
        // Constrói o caminho reverso até a raiz
        while ($currentId !== null && isset($allAreas[$currentId])) {
            array_unshift($path, $allAreas[$currentId]['areaNome']);
            $currentId = $allAreas[$currentId]['areaPaiId'];
        }
        
        $lookup[$area['areaId']] = implode(' > ', $path);
    }
    
    // Ordena pelo caminho completo para exibição no SELECT
    asort($lookup);
    return $lookup;
}

/**
 * Converte a lista de áreas (com ID Pai) para uma estrutura hierárquica aninhada (Árvore).
 * Utilizada principalmente para a visualização do Organograma de Áreas.
 * @param array $flatList Lista de áreas (flat array)
 * @param int|null $parentId ID da área pai para começar
 * @return array Árvore hierárquica
 */
function buildAreaHierarchy(array $flatList, $parentId = null): array 
{
    $branch = [];
    foreach ($flatList as $area) {
        // Coerção para garantir comparação correta
        $areaPaiId = ($area['areaPaiId'] === null) ? null : (int)$area['areaPaiId']; 
        
        if ($areaPaiId === $parentId) {
            $children = buildAreaHierarchy($flatList, (int)$area['areaId']);
            if ($children) {
                $area['children'] = $children;
            }
            $branch[] = $area;
        }
    }
    return $branch;
}

// ----------------------------------------------------
// 11. FUNÇÃO AUXILIAR PARA AGRUPAR HABILIDADES (NOVO)
// ----------------------------------------------------

/**
 * Retorna as habilidades agrupadas por Hardskill e Softskill.
 * Usado para popular o SELECT com OPTGROUP.
 * Assume que a tabela 'habilidades' tem a coluna 'habilidadeTipo'.
 * @param PDO $pdo A conexão PDO ativa.
 * @return array Um array aninhado [tipo => [id => nome]]
 */
function getHabilidadesGrouped(PDO $pdo): array
{
    try {
        // Busca todas as habilidades, ordenando pelo tipo (DESC para garantir Hardskill primeiro)
        $stmt = $pdo->query("SELECT habilidadeId, habilidadeNome, habilidadeTipo FROM habilidades ORDER BY habilidadeTipo DESC, habilidadeNome ASC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $grouped = [];
        foreach ($results as $row) {
            $tipo = $row['habilidadeTipo'];
            // Se o campo for nulo/vazio, padroniza para "Outros"
            $tipo = empty($tipo) ? 'Outros' : $tipo; 
            
            if (!isset($grouped[$tipo])) {
                $grouped[$tipo] = [];
            }
            $grouped[$tipo][$row['habilidadeId']] = $row['habilidadeNome'];
        }
        
        // Garante a ordem Hard Skills, Soft Skills, Outros
        $final_grouped = [];
        if (isset($grouped['Hardskill'])) { $final_grouped['Hard Skills'] = $grouped['Hardskill']; }
        if (isset($grouped['Softskill'])) { $final_grouped['Soft Skills'] = $grouped['Softskill']; }
        if (isset($grouped['Outros'])) { $final_grouped['Outros Tipos'] = $grouped['Outros']; }
        
        return $final_grouped;
        
    } catch (PDOException $e) {
        error_log("Erro ao buscar habilidades agrupadas: " . $e->getMessage());
        return [];
    }
}