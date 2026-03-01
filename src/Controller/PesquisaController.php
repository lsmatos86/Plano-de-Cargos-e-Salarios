<?php
// Arquivo: src/Controller/PesquisaController.php

namespace App\Controller;

use App\Repository\PesquisaRepository;
use App\Service\AuthService;
use Exception;

class PesquisaController
{
    private PesquisaRepository $repository;
    private AuthService $authService;

    public function __construct(PesquisaRepository $repository, AuthService $authService)
    {
        $this->repository = $repository;
        $this->authService = $authService;
    }

    public function handleRequest(array $get, array $post, string $method): array
    {
        // Exige permissão de gestão de cadastros para aceder a esta área
        $this->authService->checkAndFail('cadastros:manage'); 

        $message = '';
        $message_type = 'success';

        // Processa as ações do formulário
        if ($method === 'POST' && isset($post['action'])) {
            try {
                if ($post['action'] === 'nova_campanha') {
                    $this->repository->createCampanha(trim($post['titulo']), $post['data_abertura']);
                    $message = "Nova campanha de pesquisa aberta com sucesso!";
                } elseif ($post['action'] === 'encerrar_campanha') {
                    $this->repository->encerrarCampanha((int)$post['campanhaId']);
                    $message = "Pesquisa encerrada e dados trancados para análise.";
                    $message_type = 'warning';
                } elseif ($post['action'] === 'nova_empresa') {
                    $this->repository->createEmpresa(trim($post['nome']), trim($post['setor']), $post['porte']);
                    $message = "Empresa concorrente cadastrada com sucesso!";
                }
            } catch (Exception $e) {
                $message = "Erro ao processar solicitação: " . $e->getMessage();
                $message_type = 'danger';
            }
        }

        // Retorna o pacote de dados para a View exibir
        return [
            'page_title' => 'Gestão de Pesquisa Salarial',
            'message' => $message,
            'message_type' => $message_type,
            'campanhas' => $this->repository->findAllCampanhas(),
            'empresas' => $this->repository->findAllEmpresas()
        ];
    }
}