<?php
// Arquivo: src/Controller/PesquisaLancamentoController.php

namespace App\Controller;

use App\Repository\PesquisaRepository;
use App\Service\AuthService;
use Exception;

class PesquisaLancamentoController
{
    private PesquisaRepository $repository;
    private AuthService $authService;
    private \PDO $pdo;

    public function __construct(PesquisaRepository $repository, AuthService $authService, \PDO $pdo)
    {
        $this->repository = $repository;
        $this->authService = $authService;
        $this->pdo = $pdo; 
    }

    public function handleRequest(array $get, array $post, array $files, string $method): array
    {
        $this->authService->checkAndFail('cadastros:manage');

        $message = '';
        $message_type = 'success';
        
        $campanhaId = (int)($get['id'] ?? 0);
        $campanha = $this->repository->findCampanhaById($campanhaId);

        if (!$campanha) {
            header("Location: pesquisa_salarial.php?error=Campanha não encontrada");
            exit;
        }

        // --- PROCESSAMENTO DOS FORMULÁRIOS ---
        if ($method === 'POST' && isset($post['action'])) {
            try {
                if ($campanha['status'] !== 'Aberta') {
                    throw new Exception("Esta campanha está encerrada. Não são permitidas alterações.");
                }

                // 1. LANÇAMENTO MANUAL ÚNICO
                if ($post['action'] === 'novo_lancamento') {
                    $salario = floatval(str_replace(',', '.', str_replace('.', '', $post['salario_base'])));
                    $this->repository->insertLancamento($campanhaId, (int)$post['empresaId'], (int)$post['cboId'], trim($post['cargo_nome_mercado']), $salario);
                    $message = "Salário registrado com sucesso!";
                } 
                // 2. EXCLUSÃO DE LANÇAMENTO
                elseif ($post['action'] === 'excluir_lancamento') {
                    $this->repository->deleteLancamento((int)$post['valorId']);
                    $message = "Lançamento removido.";
                    $message_type = 'warning';
                }
                // 3. IMPORTAÇÃO INTELIGENTE DE CSV (CAGED)
                elseif ($post['action'] === 'importar_csv' && isset($files['arquivo_csv'])) {
                    $this->processCsvImport($post, $files['arquivo_csv'], $campanhaId, $message, $message_type);
                }
            } catch (Exception $e) {
                $message = "Atenção: " . $e->getMessage();
                $message_type = 'danger';
            }
        }

        // --- BUSCAS DE DADOS PARA PREENCHER A TELA ---
        $empresas = $this->repository->findAllEmpresas();
        $cbos = getLookupData($this->pdo, 'cbos', 'cboId', 'cboCod', 'cboTituloOficial');
        $lancamentos = $this->repository->findLancamentosByCampanha($campanhaId);

        // --- CÁLCULO ESTATÍSTICO (AGRUPADO POR CBO) ---
        $estatisticas = [];
        $agrupamento = [];
        
        foreach ($lancamentos as $l) {
            $agrupamento[$l['cboId']]['titulo'] = $l['cboCod'] . ' - ' . $l['cboTituloOficial'];
            $agrupamento[$l['cboId']]['salarios'][] = (float)$l['salario_base'];
        }

        foreach ($agrupamento as $cboId => $dados) {
            $salarios = $dados['salarios'];
            sort($salarios); 
            
            $qtd = count($salarios);
            $min = $salarios[0];
            $max = $salarios[$qtd - 1];
            $media = array_sum($salarios) / $qtd;
            
            $meio = floor(($qtd - 1) / 2);
            if ($qtd % 2) {
                $mediana = $salarios[$meio]; 
            } else {
                $mediana = ($salarios[$meio] + $salarios[$meio + 1]) / 2.0; 
            }

            $estatisticas[$cboId] = [
                'titulo' => $dados['titulo'],
                'amostras' => $qtd,
                'minimo' => $min,
                'maximo' => $max,
                'media' => $media,
                'mediana' => $mediana
            ];
        }

        return [
            'page_title' => 'Lançamentos: ' . $campanha['titulo'],
            'message' => $message,
            'message_type' => $message_type,
            'campanha' => $campanha,
            'empresas' => $empresas,
            'cbos' => $cbos,
            'lancamentos' => $lancamentos,
            'estatisticas' => $estatisticas
        ];
    }

    /**
     * Motor de Importação e Limpeza de Dados do CAGED
     */
    /**
     * Motor de Importação e Limpeza de Dados do CAGED com Reajuste Histórico
     */
    /**
     * Motor de Importação e Limpeza de Dados do CAGED com Duplo Reajuste Histórico
     */
    /**
     * Motor de Importação Inteligente (Mês/Ano Exato de Vigência)
     */
    private function processCsvImport(array $post, array $file, int $campanhaId, &$message, &$message_type): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Falha ao enviar o arquivo.");
        }

        $handle = fopen($file['tmp_name'], "r");
        if (!$handle) throw new Exception("Não foi possível ler o CSV.");

        $linhaTeste = fgets($handle);
        $separador = strpos($linhaTeste, ';') !== false ? ';' : ',';
        rewind($handle);

        $header = fgetcsv($handle, 10000, $separador);
        $idxCbo = array_search('cbo_2002', $header);
        $idxDesc = array_search('cbo_2002_descricao', $header);
        $idxSalario = array_search('salario_mensal', $header);
        $idxAno = array_search('ano', $header);
        $idxMes = array_search('mes', $header); // <- AGORA BUSCAMOS O MÊS DO CAGED!

        if ($idxCbo === false || $idxSalario === false) {
            throw new Exception("Estrutura inválida. Faltam as colunas 'cbo_2002' ou 'salario_mensal'.");
        }

        $empresaId = (int)$post['empresaIdCsv'];
        $salarioMin = !empty($post['salario_minimo']) ? (float)$post['salario_minimo'] : 0;
        $salarioMax = !empty($post['salario_maximo']) ? (float)$post['salario_maximo'] : 9999999;
        $indiceCorrecao = $post['indice_correcao'] ?? 'nenhum';
        $anoAtual = (int)date('Y');
        $mesAtual = (int)date('m');
        $dataAtualizacaoBase = date('Y-m-01'); // Dia 1 do mês atual

        // 1. Carrega o Histórico de SM ordenado da data mais recente para a mais antiga
        $stmtSM = $this->pdo->query("SELECT data_vigencia, valor FROM historico_salario_minimo ORDER BY data_vigencia DESC");
        $historicoSM = $stmtSM->fetchAll(\PDO::FETCH_ASSOC);

        // Função interna para achar o SM de uma data específica
        $getSmVigente = function($dataReferencia) use ($historicoSM) {
            foreach ($historicoSM as $sm) {
                if ($dataReferencia >= $sm['data_vigencia']) return (float)$sm['valor'];
            }
            return 0.0;
        };

        $smAtualCalculado = $getSmVigente($dataAtualizacaoBase);

        // 2. Carrega Inflação
        $stmtInpc = $this->pdo->query("SELECT ano, acumulado_ano FROM historico_inpc");
        $historicoInpc = [];
        while ($r = $stmtInpc->fetch(\PDO::FETCH_ASSOC)) {
            $historicoInpc[$r['ano']] = (float)$r['acumulado_ano'];
        }

        // 3. Mapa CBO
        $stmtCbo = $this->pdo->query(
            'SELECT "cboId", REPLACE("cboCod", \'-\', \'\') AS "cboCodLimpo" FROM cbos'
        );
        $cboMap = [];
        while ($row = $stmtCbo->fetch(\PDO::FETCH_ASSOC)) {
            $cboMap[$row['cboCodLimpo']] = $row['cboId'];
        }

        $importados = 0; $ignoradosFiltro = 0; $ignoradosCbo = 0; $reajustados = 0;

        while (($data = fgetcsv($handle, 10000, $separador)) !== false) {
            if (count($data) < 3) continue;

            $cboCodCsv = str_replace('-', '', trim($data[$idxCbo]));
            $salarioOriginalCsv = (float)$data[$idxSalario];
            $salarioFinal = $salarioOriginalCsv;
            $cargoDesc = ($idxDesc !== false) ? trim($data[$idxDesc]) : 'Cargo CAGED';
            
            // Monta a data do CSV (Ex: '2023-04-01')
            $anoRef = ($idxAno !== false && is_numeric($data[$idxAno])) ? (int)$data[$idxAno] : $anoAtual;
            $mesRef = ($idxMes !== false && is_numeric($data[$idxMes])) ? (int)$data[$idxMes] : 1;
            $dataCsv = sprintf('%04d-%02d-01', $anoRef, $mesRef);
            
            $foiReajustado = 0;

            // --- LÓGICA DE REAJUSTE TEMPORAL PRECISA ---
            // Só ajusta se a data do CSV for de um mês/ano anterior ao mês/ano atual
            if ($dataCsv < $dataAtualizacaoBase) {
                
                if ($indiceCorrecao === 'sm') {
                    $smDaEpoca = $getSmVigente($dataCsv);
                    
                    if ($smDaEpoca > 0 && $smAtualCalculado > 0) {
                        $proporcaoSM = $salarioOriginalCsv / $smDaEpoca;
                        $salarioFinal = $proporcaoSM * $smAtualCalculado;
                        $foiReajustado = 1;
                        $reajustados++;
                    }
                } 
                elseif ($indiceCorrecao === 'inpc') {
                    $multiplicador = 1.0;
                    for ($anoCalc = $anoRef; $anoCalc < $anoAtual; $anoCalc++) {
                        $taxaINPC = $historicoInpc[$anoCalc] ?? 0; 
                        $multiplicador *= (1 + ($taxaINPC / 100));
                    }
                    if ($multiplicador > 1.0) {
                        $salarioFinal = $salarioOriginalCsv * $multiplicador;
                        $foiReajustado = 1;
                        $reajustados++;
                    }
                }
            }

            if ($salarioOriginalCsv < $salarioMin || $salarioOriginalCsv > $salarioMax) {
                $ignoradosFiltro++;
                continue;
            }

            if (isset($cboMap[$cboCodCsv])) {
                $this->repository->insertLancamento($campanhaId, $empresaId, $cboMap[$cboCodCsv], $cargoDesc, $salarioFinal, $anoRef, $salarioOriginalCsv, $foiReajustado);
                $importados++;
            } else {
                $ignoradosCbo++;
            }
        }
        fclose($handle);

        $message = "<strong>Importação Inteligente Concluída!</strong><br>
                    ✅ $importados salários inseridos.<br>
                    📈 $reajustados salários antigos foram atualizados (" . strtoupper($indiceCorrecao) . " considerando a data exata da vigência).<br>
                    ❌ $ignoradosFiltro ignorados pelos filtros de corte.";
        $message_type = 'success';
    }
}
