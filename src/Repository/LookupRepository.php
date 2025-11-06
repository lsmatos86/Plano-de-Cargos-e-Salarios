<?php
// Arquivo: src/Repository/LookupRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;

/**
 * Lida com operações genéricas de busca de dados para Lookups (Selects).
 */
class LookupRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Retorna a contagem de registros de uma tabela.
     *
     * @param string $tableName O nome da tabela.
     * @return int A contagem total.
     */
    public function countRecords(string $tableName): int
    {
        // Validação simples para segurança (idealmente, isValidTableName deveria estar em um Helper)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            error_log("Tentativa de contagem em tabela inválida: {$tableName}");
            return 0;
        }

        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$tableName}");
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("Erro ao contar registros da tabela {$tableName}: " . $e->getMessage());
            return 0;
        }
    }

    // ====================================================================
    // MÉTODOS PÚBLICOS PARA BUSCA DE LOOKUPS (REPARO DO ERRO FATAL)
    // ====================================================================

    /**
     * Busca CBOs, formatando para exibição (Código - Título).
     */
    public function findCbos(): array
    {
        try {
            $sql = "SELECT cboId, cboCod, cboTituloOficial, CONCAT(cboCod, ' - ', cboTituloOficial) AS display_name
                    FROM cbos
                    ORDER BY cboCod ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar CBOs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca Escolaridades.
     */
    public function findEscolaridades(): array
    {
        try {
            $sql = "SELECT escolaridadeId, escolaridadeTitulo FROM escolaridades ORDER BY escolaridadeOrdem ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Escolaridades: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca Faixas Salariais.
     */
    public function findFaixas(): array
    {
        try {
            $sql = "SELECT faixaId, faixaNivel FROM faixas_salariais ORDER BY faixaOrdem ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Faixas Salariais: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca Níveis Hierárquicos com o nome do Tipo (Substituindo o antigo getNivelHierarquicoLookup).
     */
    public function findNivelHierarquico(): array
    {
        try {
            $sql = "SELECT n.nivelId, n.nivelOrdem, n.nivelNome, t.tipoNome AS tipoHierarquiaNome
                    FROM nivel_hierarquico n
                    JOIN tipo_hierarquia t ON t.tipoId = n.tipoId
                    ORDER BY n.nivelOrdem DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Níveis Hierárquicos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca Cargos para uso como Supervisor (Select).
     */
    public function findCargosForSelect(): array
    {
        try {
            $sql = "SELECT cargoId AS id, cargoNome AS nome FROM cargos ORDER BY cargoNome ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Cargos para Select: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca Habilidades (incluindo o tipo).
     */
    public function findHabilidades(): array
    {
        try {
            $sql = "SELECT habilidadeId AS id, habilidadeNome AS nome, habilidadeTipo AS tipo FROM habilidades ORDER BY habilidadeTipo DESC, habilidadeNome ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Habilidades: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca Características (Comportamentais).
     */
    public function findCaracteristicas(): array
    {
        try {
            $sql = "SELECT caracteristicaId AS id, caracteristicaNome AS nome FROM caracteristicas ORDER BY caracteristicaNome ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Características: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca Riscos Ocupacionais.
     */
    public function findRiscos(): array
    {
        try {
            $sql = "SELECT riscoId AS id, riscoNome AS nome FROM riscos ORDER BY riscoNome ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Riscos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca Cursos e Certificações.
     */
    public function findCursos(): array
    {
        try {
            $sql = "SELECT cursoId AS id, cursoNome AS nome FROM cursos ORDER BY cursoNome ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Cursos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca Grupos de Recursos.
     */
    public function findRecursosGrupos(): array
    {
        try {
            $sql = "SELECT recursoGrupoId AS id, recursoGrupoNome AS nome FROM recursos_grupos ORDER BY recursoGrupoNome ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Grupos de Recursos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca Áreas de Atuação.
     */
    public function findAreasAtuacao(): array
    {
        try {
            $sql = "SELECT areaId AS id, areaNome AS nome FROM areas_atuacao ORDER BY areaNome ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar Áreas de Atuação: " . $e->getMessage());
            return [];
        }
    }
}