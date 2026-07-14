<?php
    // Arquivo: config.php

    /**
     * Configurações globais do sistema de Plano de Cargos e Salários
     * Adaptado para ambiente local (XAMPP + PostgreSQL)
     */

    // Se estiver rodando no Replit ou em nuvem usando uma string de conexão completa
    if (getenv("DATABASE_URL")) {
        $db_url = parse_url(getenv("DATABASE_URL"));
        
        define('DB_HOST', $db_url["host"] ?? 'localhost');
        define('DB_PORT', $db_url["port"] ?? '5432');
        define('DB_USER', $db_url["user"] ?? 'postgres');
        define('DB_PASS', $db_url["pass"] ?? '');
        define('DB_NAME', ltrim($db_url["path"], '/') ?? 'koppla_itacitrus');
    } else {
        // =========================================================================
        // CONFIGURAÇÃO LOCAL (XAMPP / WINDOWS)
        // =========================================================================
        
        // Endereço do servidor do banco de dados (geralmente localhost ou 127.0.0.1)
        define('DB_HOST', 'localhost');
        
        // Porta padrão do PostgreSQL (certifique-se de usar a do Postgres: 5432)
        define('DB_PORT', '5432');
        
        // Nome do banco de dados ajustado para o correto
        define('DB_NAME', 'koppla_itacitrus');
        
        // Usuário padrão do PostgreSQL (geralmente 'postgres')
        define('DB_USER', 'postgres');
        
        // Senha ajustada para o seu banco local
        define('DB_PASS', '123'); 
    }

    // Configurações de URL base e caminhos do projeto
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $appDir = realpath(__DIR__);
    $basePath = '/';

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $scriptFilename = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');

    if ($appDir && $scriptFilename && strpos(str_replace('\\', '/', $scriptFilename), str_replace('\\', '/', $appDir)) === 0) {
        $relativePath = '/' . ltrim(str_replace('\\', '/', substr($scriptFilename, strlen($appDir))), '/');
        $scriptDir = str_replace('\\', '/', dirname($scriptName));

        if ($relativePath !== '' && $relativePath !== '/' && $relativePath !== false) {
            $relativeDir = dirname($relativePath);
            if ($relativeDir === '.' || $relativeDir === '/') {
                $basePath = $scriptDir;
            } elseif (substr($scriptDir, -strlen($relativeDir)) === $relativeDir) {
                $basePath = substr($scriptDir, 0, strlen($scriptDir) - strlen($relativeDir));
            } else {
                $basePath = $scriptDir;
            }
        } else {
            $basePath = $scriptDir;
        }
    }

    $basePath = '/' . trim($basePath, '/') . '/';
    if ($basePath === '//') {
        $basePath = '/';
    }

    define('BASE_URL', $protocol . $host . $basePath);

    // Configurações de exibição de erros para desenvolvimento local
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
