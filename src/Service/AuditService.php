<?php
// Arquivo: src/Service/AuditService.php

namespace App\Service;

use App\Core\Database;
use PDO;

class AuditService
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection(); 
    }

    public function log(string $acao, ?string $nomeTabela = null, ?int $idRegistro = null, ?array $dadosJson = null, ?string $nomeUsuarioOverride = null): void
    {
        $usuarioId   = $_SESSION['usuario_id'] ?? null; 
        $nomeUsuario = $nomeUsuarioOverride ?? $_SESSION['usuario_nome'] ?? 'System';

        $json = null;
        if ($dadosJson !== null) {
            unset($dadosJson['password'], $dadosJson['confirm_password'], $dadosJson['senha']);
            $json = json_encode($dadosJson, JSON_UNESCAPED_UNICODE);
        }

        try {
            $sql = 'INSERT INTO audit_log ("usuarioId", "nomeUsuario", acao, "nomeTabela", "idRegistro", "dadosJson", "dataHora") 
                    VALUES (:usuarioId, :nomeUsuario, :acao, :nomeTabela, :idRegistro, :dadosJson, NOW())';
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuarioId',   $usuarioId,   PDO::PARAM_INT);
            $stmt->bindParam(':nomeUsuario', $nomeUsuario, PDO::PARAM_STR);
            $stmt->bindParam(':acao',        $acao,        PDO::PARAM_STR);
            $stmt->bindParam(':nomeTabela',  $nomeTabela,  PDO::PARAM_STR);
            $stmt->bindParam(':idRegistro',  $idRegistro,  PDO::PARAM_INT);
            $stmt->bindParam(':dadosJson',   $json,        PDO::PARAM_STR);
            $stmt->execute();

        } catch (\Exception $e) {
            error_log('Falha ao registrar log de auditoria: ' . $e->getMessage());
        }
    }
}
