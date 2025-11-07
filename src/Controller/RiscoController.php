<?php
// Arquivo: src/Controller/RiscoController.php
// (Este arquivo substitui o antigo Controller/RiscoController.php na raiz)

namespace App\Controller;

// Importa as classes necessárias
use App\Repository\RiscoRepository;
use App\Service\AuthService;
use Exception;

/**
 * RiscoController
 * * Esta classe gerencia todas as requisições (GET, POST) para a página de Riscos.
 * Ela é instanciada pela view (riscos.php) e executa a lógica de CRUD
 * antes de retornar os dados para exibição.
 */
class RiscoController
{
    private RiscoRepository $repo;
    private AuthService $authService;

    // Colunas padrão para ordenação e nome
    private string $id_column = 'riscoId';
    private string $name_column = 'riscoNome';

    public function __construct()
    {
        // Instancia as dependências
        $this->repo = new RiscoRepository();
        $this->authService = new AuthService();
    }

    /**
     * Ponto de entrada principal. Lida com a requisição e retorna dados para a View.
     * * @param array $getData Os dados de $_GET
     * @param array $postData Os dados de $_POST
     * @param string $requestMethod O método (ex: 'GET' ou 'POST')
     * @return array Um array de dados para a view (ex: ['registros' => ..., 'params' => ...])
     */
    public function handleRequest(array $getData, array $postData, string $requestMethod): array
    {
        // 1. Segurança e Sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isUserLoggedIn()) {
            header('Location: ../login.php'); // Volta para o login na raiz
            exit;
        }
        $this->authService->checkAndFail('riscos:manage', '../index.php?error=Acesso+negado');

        // 2. Inicializa variáveis de estado
        $message = $getData['message'] ?? '';
        $message_type = $getData['type'] ?? 'info';
        $titulo = ''; // Para mensagens de erro

        try {
            // 3. Lógica de Ação (POST ou GET com 'action')
            
            // AÇÃO: CREATE (POST)
            if ($requestMethod === 'POST' && isset($postData['action'])) {
                $titulo = trim($postData[$this->name_column] ?? '');
                
                if ($postData['action'] === 'insert') {
                    $this->repo->save($postData); // O repo 'save' não foi fornecido, mas é mantido
                    $message = "Risco '{$titulo}' cadastrado com sucesso!";
                    $message_type = 'success';
                }
                
                // Redireciona para a própria view (método GET) para evitar reenvio do form
                header("Location: riscos.php?message=" . urlencode($message) . "&type={$message_type}");
                exit;
            }

            // AÇÃO: DELETE (GET)
            if ($requestMethod === 'GET' && isset($getData['action']) && $getData['action'] === 'delete') {
                $id = (int)($getData['id'] ?? 0);
                $deleted = $this->repo->delete($id); //
                
                if ($deleted) {
                    $message = "Risco ID {$id} excluído com sucesso!";
                    $message_type = 'success';
                } else {
                    $message = "Erro: Risco ID {$id} não encontrado ou já excluído.";
                    $message_type = 'danger';
                }
                
                header("Location: riscos.php?message=" . urlencode($message) . "&type={$message_type}");
                exit;
            }

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $message = "Erro: O risco '{$titulo}' já está cadastrado.";
            } else {
                $message = $e->getMessage();
            }
            $message_type = 'danger';
            
            // Redireciona com a mensagem de erro
            header("Location: riscos.php?message=" . urlencode($message) . "&type={$message_type}");
            exit;
        }

        // 4. Lógica de Leitura (READ - Padrão se não houver ação)
        
        // Parâmetros de Filtro e Ordenação
        $params = [
            'term' => $getData['term'] ?? '',
            'sort_col' => $getData['sort_col'] ?? $this->id_column,
            'sort_dir' => $getData['sort_dir'] ?? 'ASC',
            'page' => $getData['page'] ?? 1,
            'limit' => 10
        ];

        $registros = [];
        $totalRecords = 0;
        $totalPages = 1;
        $currentPage = 1;

        try {
            $repoParams = [
                'term' => $params['term'],
                'order_by' => $params['sort_col'],
                'sort_dir' => $params['sort_dir'],
                'page' => $params['page'],
                'limit' => $params['limit']
            ];

            $result = $this->repo->findAllPaginated($repoParams); //
            
            $registros = $result['data'];
            $totalRecords = $result['total'];
            $totalPages = $result['totalPages'];
            $currentPage = $result['currentPage'];

        } catch (Exception $e) {
            if (empty($message)) {
                $message = "Erro ao carregar dados: " . $e->getMessage();
                $message_type = 'danger';
            }
        }

        // Lista de riscos (ENUM) para o <select> do modal
        $tipos_risco_enum = [
            'Físico', 'Químico', 'Ergonômico', 'Psicossocial', 'Acidental', 'Biológico'
        ];

        // 5. Retorna todos os dados para a View
        return [
            'registros' => $registros,
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'params' => $params,
            'message' => $message,
            'message_type' => $message_type,
            'id_column' => $this->id_column,
            'name_column' => $this->name_column,
            'tipos_risco_enum' => $tipos_risco_enum
        ];
    }
}