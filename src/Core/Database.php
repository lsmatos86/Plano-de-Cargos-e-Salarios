<?php
// Arquivo: src/Core/Database.php

namespace App\Core; // O namespace que definimos no composer.json

use PDO;
use PDOException;

/**
 * Gerencia a conexão com o banco de dados.
 * Utiliza as constantes definidas em config.php
 */
class Database
{
    private static ?PDO $pdo = null; // Conexão estática (Singleton)

    /**
     * Retorna uma instância única da conexão PDO.
     */
    public static function getConnection(): PDO
    {
        // Se a conexão ainda não foi criada, cria agora.
        if (self::$pdo === null) {
            // A lógica exata da sua função getDbConnection()
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // REPARO DE SEGURANÇA: Logar o erro completo e exibir uma mensagem genérica para o usuário.
                error_log("Erro Crítico de Conexão com o Banco de Dados: " . $e->getMessage());
                // Importante: Não expor a mensagem original $e->getMessage() para o usuário final.
                die("Erro de Conexão com o Banco de Dados. Por favor, tente novamente mais tarde."); 
            }
        }

        return self::$pdo;
    }
}