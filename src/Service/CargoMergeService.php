<?php
// Arquivo: src/Service/CargoMergeService.php

namespace App\Service;

use App\Core\Database;
use Exception;
use PDO;

class CargoMergeService
{
    private PDO $pdo;
    private AuditService $auditService;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->auditService = new AuditService();
    }

    /**
     * Fundo o Cargo Origem no Cargo Destino e apaga a Origem.
     */
    public function mergeCargos(int $origemId, int $destinoId): bool
    {
        if ($origemId === $destinoId) {
            throw new Exception("O cargo de origem e o cargo de destino não podem ser o mesmo.");
        }

        if ($origemId <= 0 || $destinoId <= 0) {
            throw new Exception("IDs de cargo inválidos para unificação.");
        }

        $this->pdo->beginTransaction();

        try {
            // 1. Listagem de todas as tabelas N:M onde o cargoId é chave estrangeira
            $tabelasRelacionamento = [
                'habilidades_cargo', 
                'caracteristicas_cargo', 
                'riscos_cargo',
                'cursos_cargo', 
                'recursos_grupos_cargo', 
                'cargos_area',
                'cargo_sinonimos', 
                'cargos_supervisores' // Inclui a nova tabela de chefes múltiplos
            ];

            foreach ($tabelasRelacionamento as $tabela) {
                // UPDATE IGNORE transfere as relações. 
                // Se o cargo de destino já tiver o mesmo curso/risco, o IGNORE evita o erro de duplicado.
                $sql = "UPDATE IGNORE {$tabela} SET cargoId = ? WHERE cargoId = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$destinoId, $origemId]);

                // As linhas que sobraram são os duplicados exatos (que o IGNORE rejeitou). Podemos apagá-las em segurança.
                $sqlDel = "DELETE FROM {$tabela} WHERE cargoId = ?";
                $stmtDel = $this->pdo->prepare($sqlDel);
                $stmtDel->execute([$origemId]);
            }

            // 2. Atualizar caso o cargo de origem fosse CHEFE de alguém na tabela cargos_supervisores
            $sqlSup = "UPDATE IGNORE cargos_supervisores SET supervisorId = ? WHERE supervisorId = ?";
            $stmtSup = $this->pdo->prepare($sqlSup);
            $stmtSup->execute([$destinoId, $origemId]);
            
            $this->pdo->prepare("DELETE FROM cargos_supervisores WHERE supervisorId = ?")->execute([$origemId]);

            // 3. Log de Auditoria antes de apagar
            $this->auditService->log('MERGE', 'cargos', $destinoId, [
                'acao' => 'Unificação de Cargos',
                'cargo_apagado_id' => $origemId,
                'cargo_mantido_id' => $destinoId
            ]);

            // 4. Apagar o cargo de origem da tabela principal
            $stmtDelCargo = $this->pdo->prepare("DELETE FROM cargos WHERE cargoId = ?");
            $stmtDelCargo->execute([$origemId]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erro no Merge de Cargos: " . $e->getMessage());
            throw new Exception("Falha ao unificar os cargos. Verifique os registos do sistema.");
        }
    }
}