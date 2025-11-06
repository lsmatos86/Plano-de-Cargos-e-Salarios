<?php
// Arquivo: config.php

// ----------------------------------------------------
// 1. CONFIGURAÇÃO DO BANCO DE DADOS (PostgreSQL)
// ----------------------------------------------------
// IMPORTANTE: No Replit, use as variáveis de ambiente do PostgreSQL
// Verifique se DATABASE_URL está disponível (ambiente Replit)
if (isset($_ENV['DATABASE_URL'])) {
    define('DB_URL', $_ENV['DATABASE_URL']);
    define('DB_HOST', $_ENV['PGHOST'] ?? 'localhost');
    define('DB_NAME', $_ENV['PGDATABASE'] ?? 'azukicom_kopplaita');
    define('DB_USER', $_ENV['PGUSER'] ?? 'root');
    define('DB_PASS', $_ENV['PGPASSWORD'] ?? '');
    define('DB_PORT', $_ENV['PGPORT'] ?? '5432');
    define('DB_TYPE', 'pgsql');
} else {
    define('DB_URL', null);
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'azukicom_kopplaita');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', '5432');
    define('DB_TYPE', 'pgsql');
}
define('DB_CHARSET', 'utf8');

// Nota: As funções de conexão (getDbConnection) e autenticação (authenticateUser, isUserLoggedIn)
// foram movidas para 'includes/functions.php' para manter este arquivo limpo e focado em constantes.

// ----------------------------------------------------
// 2. CONFIGURAÇÃO DE FUSO HORÁRIO (Timezone)
// ----------------------------------------------------
// Garante que o PHP utilize o fuso horário correto para todas as operações com data e hora.
date_default_timezone_set('America/Bahia'); // AJUSTADO PARA O FUSO HORÁRIO DA BAHIA

// Nota: As funções de conexão (getDbConnection) e autenticação (authenticateUser, isUserLoggedIn)
// foram movidas para 'includes/functions.php' para manter este arquivo limpo e focado em constantes.