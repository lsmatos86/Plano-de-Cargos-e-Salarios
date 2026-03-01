<?php
// Arquivo: src/Repository/PesquisaRepository.php

namespace App\Repository;

use App\Core\Database;
use PDO;
use Exception;

class PesquisaRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findAllCampanhas(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM campanhas_pesquisa ORDER BY data_abertura DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllEmpresas(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM empresas_mercado ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCampanha(string $titulo, string $data_abertura): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO campanhas_pesquisa (titulo, data_abertura, status) VALUES (?, ?, 'Aberta')");
        $stmt->execute([$titulo, $data_abertura]);
        return (int)$this->pdo->lastInsertId();
    }

    public function encerrarCampanha(int $campanhaId): bool
    {
        // Usa CURRENT_DATE nativo do SQL para garantir a data do servidor
        $stmt = $this->pdo->prepare("UPDATE campanhas_pesquisa SET status = 'Encerrada', data_fechamento = CURRENT_DATE WHERE campanhaId = ?");
        return $stmt->execute([$campanhaId]);
    }

    public function createEmpresa(string $nome, string $setor, string $porte): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO empresas_mercado (nome, setor, porte) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $setor, $porte]);
        return (int)$this->pdo->lastInsertId();
        }
// --- NOVAS FUNÇÕES PARA OS LANÇAMENTOS E ESTATÍSTICAS ---

    public function findCampanhaById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM campanhas_pesquisa WHERE campanhaId = ?");
        $stmt->execute([$id]);
        $campanha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $campanha ?: null;
    }

    public function insertLancamento(int $campanhaId, int $empresaId, int $cboId, string $cargoNomeMercado, float $salarioBase, int $anoRef = null, float $salarioOriginal = null, int $foiReajustado = 0): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO pesquisa_valores (campanhaId, empresaId, cboId, cargo_nome_mercado, salario_base, ano_referencia, salario_original, foi_reajustado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$campanhaId, $empresaId, $cboId, $cargoNomeMercado, $salarioBase, $anoRef, $salarioOriginal, $foiReajustado]);
        return (int)$this->pdo->lastInsertId();
    }

    public function deleteLancamento(int $valorId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM pesquisa_valores WHERE valorId = ?");
        return $stmt->execute([$valorId]);
    }

    public function findLancamentosByCampanha(int $campanhaId): array
    {
        // Traz os lançamentos cruzando com o nome da empresa e o CBO
        $sql = "SELECT pv.*, e.nome AS empresa_nome, c.cboTituloOficial, c.cboCod 
                FROM pesquisa_valores pv
                JOIN empresas_mercado e ON e.empresaId = pv.empresaId
                JOIN cbos c ON c.cboId = pv.cboId
                WHERE pv.campanhaId = ?
                ORDER BY c.cboTituloOficial ASC, pv.salario_base DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$campanhaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
