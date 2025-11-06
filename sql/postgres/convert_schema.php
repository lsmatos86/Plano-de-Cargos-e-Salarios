<?php
// Simple MySQL to PostgreSQL converter for this project

$mysqlFile = __DIR__ . '/../azukicom_kopplaita.sql';
$postgresFile = __DIR__ . '/schema.sql';

$content = file_get_contents($mysqlFile);

// Remove MySQL specific comments
$content = preg_replace('/^--.*$/m', '', $content);
$content = preg_replace('/\/\*!.*?\*\/;/s', '', $content);

// Replace backticks with double quotes
$content = str_replace('`', '"', $content);

// Replace AUTO_INCREMENT with SERIAL
$content = preg_replace('/int\(\d+\)\s+NOT NULL AUTO_INCREMENT/i', 'SERIAL', $content);
$content = preg_replace('/int\(\d+\)\s+AUTO_INCREMENT/i', 'SERIAL', $content);

// Replace int(n) with INTEGER
$content = preg_replace('/int\(\d+\)/i', 'INTEGER', $content);

// Replace varchar sizes
$content = preg_replace('/varchar\((\d+)\)/i', 'VARCHAR($1)', $content);

// Replace text with TEXT
$content = preg_replace('/\btext\b/i', 'TEXT', $content);
$content = preg_replace('/\blongtext\b/i', 'TEXT', $content);

// Replace timestamp with TIMESTAMP
$content = preg_replace('/timestamp\s+NOT NULL\s+DEFAULT\s+current_timestamp\(\)\s+ON UPDATE current_timestamp\(\)/i', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP', $content);
$content = preg_replace('/timestamp\s+NOT NULL\s+DEFAULT\s+current_timestamp\(\)/i', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP', $content);

// Replace datetime
$content = preg_replace('/datetime\s+NOT NULL\s+DEFAULT\s+current_timestamp\(\)/i', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP', $content);

// Remove ENGINE and CHARSET clauses
$content = preg_replace('/\s*ENGINE\s*=\s*\w+/i', '', $content);
$content = preg_replace('/\s*DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $content);
$content = preg_replace('/\s*COLLATE\s*=\s*[\w_]+/i', '', $content);
$content = preg_replace('/\s*CHARACTER SET\s+\w+/i', '', $content);

// Replace CHECK constraints
$content = preg_replace('/CHECK\s*\(json_valid\([^)]+\)\)/i', '', $content);

// Remove SET commands
$content = preg_replace('/^SET\s+.*?;$/m', '', $content);
$content = preg_replace('/^START TRANSACTION;$/m', '', $content);

// Clean up extra whitespace
$content = preg_replace('/\n{3,}/', "\n\n", $content);

file_put_contents($postgresFile, $content);
echo "Conversion complete! PostgreSQL schema saved to: $postgresFile\n";
