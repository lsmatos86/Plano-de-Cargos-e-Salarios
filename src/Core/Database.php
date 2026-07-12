<?php
// Arquivo: src/Core/Database.php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Classe para gerenciamento de conexões com o banco de dados.
 */
class Database {
    
    private static $connection = null;

    /**
     * Retorna a conexão ativa com o banco de dados.
     * Caso não exista, cria uma nova baseado nas configurações.
     * 
     * @return PDO
     */
    public static function getConnection(): PDO {
        if (self::$connection === null) {
            try {
                // Carrega as configurações globais se as constantes não existirem
                if (!defined('DB_HOST')) {
                    $configFile = dirname(__DIR__, 2) . '/config.php';
                    if (file_exists($configFile)) {
                        require_once $configFile;
                    }
                }

                $host = defined('DB_HOST') ? DB_HOST : 'localhost';
                $port = defined('DB_PORT') ? DB_PORT : '5432';
                $dbname = defined('DB_NAME') ? DB_NAME : 'ita';
                $user = defined('DB_USER') ? DB_USER : 'postgres';
                $pass = defined('DB_PASS') ? DB_PASS : '';

                // Monta o DSN para o driver PostgreSQL (padrão do projeto)
                $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
                
                // Opções de segurança e tratamento de dados do PDO
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$connection = new PDO($dsn, $user, $pass, $options);

            } catch (PDOException $e) {
                // EXIBIÇÃO EM MODO DEBUG ATIVADA: Mostra o erro real na tela para sabermos o que corrigir
                $mensagemErro = "<h3>Falha Crítica na Conexão Local</h3>";
                $mensagemErro .= "<p><strong>Erro Real do PHP:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                $mensagemErro .= "<p><em>Verifique se o PostgreSQL está rodando, se as extensões 'pdo_pgsql' estão ativas no php.ini ou se as credenciais no arquivo config.php estão corretas para o XAMPP.</em></p>";
                
                die($mensagemErro);
            }
        }

        return self::$connection;
    }
}