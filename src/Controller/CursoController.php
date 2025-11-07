<?php
// Arquivo: src/Controller/CursoController.php

namespace App\Controller;

use App\Repository\CursoRepository;
use App\Service\AuthService;
use Exception;

class CursoController
{
    private CursoRepository $repo;
    private AuthService $authService;

    // Colunas padrão
    private string $id_column = 'cursoId';
    private string $name_column = 'cursoNome';

    public function __construct()
    {
        $this->repo = new CursoRepository();
        $this->authService = new AuthService();
    }

    /**
     * Ponto de entrada principal. Lida com a requisição e retorna dados para a View.
     */
    public function handleRequest(array $getData, array $postData, string $requestMethod): array
    {
        // 1. Segurança e Sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isUserLoggedIn()) {
            header('Location: ../login.php'); 
            exit;
        }
        
        // ==================================================================
        // CORREÇÃO APLICADA AQUI: Verifica a permissão correta 'cursos:manage'
        // ==================================================================
        $this->authService->checkAndFail('cursos:manage', '../index.php?error=Acesso+negado');

        // 2. Inicializa variáveis de estado
        $message = $getData['message'] ?? '';
        $message_type = $getData['type'] ?? 'info';
        $titulo = ''; 
        $editData = null; // Para o formulário de edição

        try {
            // 3. Lógica de Ação (POST ou GET com 'action')
            
            // AÇÃO: CREATE ou UPDATE (POST)
            if ($requestMethod === 'POST' && isset($postData['action'])) {
                $titulo = trim($postData[$this->name_column] ?? '');
                
                if ($postData['action'] === 'save') {
                    // O repositório (abaixo) também fará a verificação
                    $this->repo->save($postData); 
                    $message = "Curso '{$titulo}' salvo com sucesso!";
                    $message_type = 'success';
                }
                
                header("Location: cursos.php?message=" . urlencode($message) . "&type={$message_type}");
                exit;
            }

            // AÇÃO: DELETE (GET)
            if ($requestMethod === 'GET' && isset($getData['action']) && $getData['action'] === 'delete') {
                $id = (int)($getData['id'] ?? 0);
                $deleted = $this->repo->delete($id); 
                
                if ($deleted) {
                    $message = "Curso ID {$id} excluído com sucesso!";
                    $message_type = 'success';
                } else {
                    $message = "Erro: Curso ID {$id} não encontrado ou já excluído.";
                    $message_type = 'danger';
                }
                
                header("Location: cursos.php?message=" . urlencode($message) . "&type={$message_type}");
                exit;
            }

            // AÇÃO: EDIT (GET) - Carrega dados para o formulário
            if ($requestMethod === 'GET' && isset($getData['edit'])) {
                $editId = (int)$getData['edit'];
                $editData = $this->repo->find($editId);
            }

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $message = "Erro: O curso '{$titulo}' já está cadastrado.";
            } else {
                $message = $e->getMessage();
            }
            $message_type = 'danger';
            
            // Redireciona com a mensagem de erro
            header("Location: cursos.php?message=" . urlencode($message) . "&type={$message_type}");
            exit;
        }

        // 4. Lógica de Leitura (READ - Padrão)
        
        $params = [
            'term' => $getData['term'] ?? '',
            'sort_col' => $getData['sort_col'] ?? $this->name_column,
            'sort_dir' => $getData['sort_dir'] ?? 'ASC',
            'page' => $getData['page'] ?? 1,
            'limit' => 10
        ];

        $registros = [];
        $totalRecords = 0;
        $totalPages = 1;
        $currentPage = 1;

        try {
            $result = $this->repo->findAllPaginated($params); 
            
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
            'editData' => $editData // Dados para o formulário de edição
        ];
    }
}