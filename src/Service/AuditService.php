<?php
// Arquivo: src/Service/AuditService.php

namespace App\Service;

use App\Core\Database;
use PDO;

/**
 * Classe para gerenciar o registro de logs de auditoria.
 * (Versão corrigida com nomes de colunas em Português)
 */
class AuditService
{
    private ?PDO $db;

    public function __construct()
    {
        // Pega a instância do banco de dados
        // (Assumindo que sua classe Database tem um método estático getConnection ou getInstance)
        // Vou usar o padrão do seu CargoRepository.php original:
        $this->db = Database::getConnection(); 
    }

    /**
     * Registra um evento de auditoria no banco de dados.
     *
     * @param string $acao Ação realizada (ex: CREATE, UPDATE, DELETE)
     * @param string|null $nomeTabela A tabela afetada (ex: 'cargos')
     * @param int|null $idRegistro O ID do registro afetado
     * @param array|null $dadosJson Dados contextuais (ex: $_POST) para salvar como JSON
     */
    public function log(string $acao, ?string $nomeTabela = null, ?int $idRegistro = null, ?array $dadosJson = null): void
    {
        // Usa os nomes de sessão do seu functions.php
        $usuarioId = $_SESSION['user_id'] ?? null; 
        $nomeUsuario = $_SESSION['username'] ?? 'System';

        // Converte os dados para JSON, se existirem
        $json = null;
        if ($dadosJson !== null) {
            // Remove dados sensíveis (como senhas) antes de logar
            unset($dadosJson['password']);
            unset($dadosJson['confirm_password']);
            unset($dadosJson['senha']); // Garantia
            
            $json = json_encode($dadosJson, JSON_UNESCAPED_UNICODE);
        }

        try {
            // SQL ATUALIZADO com colunas em Português
            $sql = "INSERT INTO audit_log (usuarioId, nomeUsuario, acao, nomeTabela, idRegistro, dadosJson, dataHora) 
                    VALUES (:usuarioId, :nomeUsuario, :acao, :nomeTabela, :idRegistro, :dadosJson, NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind dos parâmetros ATUALIZADO
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
            $stmt->bindParam(':nomeUsuario', $nomeUsuario, PDO::PARAM_STR);
            $stmt->bindParam(':acao', $acao, PDO::PARAM_STR);
            $stmt->bindParam(':nomeTabela', $nomeTabela, PDO::PARAM_STR);
            $stmt->bindParam(':idRegistro', $idRegistro, PDO::PARAM_INT);
            $stmt->bindParam(':dadosJson', $json, PDO::PARAM_STR);
            
            $stmt->execute();

        } catch (\Exception $e) {
            // Em um sistema real, você deveria logar este erro em um arquivo
            // Por enquanto, vamos apenas ignorar para não quebrar a aplicação principal.
            error_log('Falha ao registrar log de auditoria: ' . $e->getMessage());
        }
    }
}