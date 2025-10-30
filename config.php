<?php
// Arquivo: config.php

// ----------------------------------------------------
// 1. CONFIGURAÇÃO DO BANCO DE DADOS (MySQL/MariaDB)
// ----------------------------------------------------
// IMPORTANTE: Substitua os valores abaixo com as credenciais do seu banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'azukicom_kopplaita'); // Nome do banco que contém as tabelas criadas
define('DB_USER', 'root');         // Usuário do banco
define('DB_PASS', '');    // Senha do banco
define('DB_CHARSET', 'utf8mb4');

// Nota: As funções de conexão (getDbConnection) e autenticação (authenticateUser, isUserLoggedIn)
// foram movidas para 'includes/functions.php' para manter este arquivo limpo e focado em constantes.