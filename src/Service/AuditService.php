<?php
// Arquivo: src/Service/AuditService.php

namespace App\Service;

use App\Core\Database;
use PDO;

/**
 * Classe para gerenciar o registro de logs de auditoria.
 */
class AuditService
{
    private ?PDO $db;

    public function __construct()
    {
        // Pega a instância do banco de dados
        $this->db = Database::getInstance();
    }

    /**
     * Registra um evento de auditoria no banco de dados.
     *
     * @param string $action Ação realizada (ex: CREATE, UPDATE, DELETE)
     * @param string|null $tableName A tabela afetada (ex: 'cargos')
     * @param int|null $recordId O ID do registro afetado
     * @param array|null $jsonData Dados contextuais (ex: $_POST) para salvar como JSON
     */
    public function log(string $action, ?string $tableName = null, ?int $recordId = null, ?array $jsonData = null): void
    {
        // Tenta obter o usuário da sessão
        $userId = $_SESSION['user_id'] ?? null; // Assumindo que você armazena 'user_id' na sessão
        $username = $_SESSION['username'] ?? 'System'; // Assumindo que você armazena 'username'

        // Converte os dados para JSON, se existirem
        $json = null;
        if ($jsonData !== null) {
            // Remove dados sensíveis (como senhas) antes de logar
            unset($jsonData['password']);
            unset($jsonData['confirm_password']);
            
            $json = json_encode($jsonData, JSON_UNESCAPED_UNICODE);
        }

        try {
            $sql = "INSERT INTO audit_log (userId, username, action, tableName, recordId, jsonData, timestamp) 
                    VALUES (:userId, :username, :action, :tableName, :recordId, :jsonData, NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':action', $action, PDO::PARAM_STR);
            $stmt->bindParam(':tableName', $tableName, PDO::PARAM_STR);
            $stmt->bindParam(':recordId', $recordId, PDO::PARAM_INT);
            $stmt->bindParam(':jsonData', $json, PDO::PARAM_STR);
            
            $stmt->execute();

        } catch (\Exception $e) {
            // Em um sistema real, você deveria logar este erro em um arquivo
            // em vez de interromper a execução.
            // Por enquanto, vamos apenas ignorar para não quebrar a aplicação principal.
            error_log('Falha ao registrar log de auditoria: ' . $e->getMessage());
        }
    }
}