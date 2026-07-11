<?php
/**
 * Corrige compatibilidade PostgreSQL em arquivos PHP.
 */

$PHP_FILES = [];
foreach (['src', 'includes', 'views', 'relatorios'] as $dir) {
    if (is_dir($dir)) {
        $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iter as $file) {
            if ($file->getExtension() === 'php') {
                $PHP_FILES[] = $file->getPathname();
            }
        }
    }
}
// Root PHP files
foreach (glob('*.php') as $f) {
    $PHP_FILES[] = $f;
}

$SQL_KEYWORDS = '/\b(SELECT|INSERT|UPDATE|DELETE|FROM|WHERE|ORDER\s+BY|GROUP\s+BY|'
    . 'LEFT\s+JOIN|RIGHT\s+JOIN|INNER\s+JOIN|OUTER\s+JOIN|JOIN|'
    . 'SET\s|INTO\s|LIMIT|OFFSET|RETURNING|COUNT\s*\(|SUM\s*\(|MAX\s*\(|MIN\s*\(|'
    . 'HAVING|DISTINCT|ON\s+\w|LIKE\s+[:\?]|IN\s*\(|IS\s+NULL|IS\s+NOT\s+NULL|'
    . 'LAG\s*\(|LEAD\s*\(|STRING_AGG|WITH\s+\w)\b/i';

function hasSql(string $s): bool {
    global $SQL_KEYWORDS;
    return (bool) preg_match($SQL_KEYWORDS, $s);
}

function quoteCamels(string $content, bool $escape): string {
    // Salva params nomeados
    $params = [];
    $content = preg_replace_callback('/:([a-zA-Z_][a-zA-Z0-9_]*)/', function($m) use (&$params) {
        $k = '__P' . count($params) . '__';
        $params[$k] = $m[0];
        return $k;
    }, $content);

    // Salva identificadores já entre aspas
    $quoted = [];
    if ($escape) {
        $pattern = '/\\\\"[^"]*\\\\"/';
    } else {
        $pattern = '/"[^"]*"/';
    }
    $content = preg_replace_callback($pattern, function($m) use (&$quoted) {
        $k = '__Q' . count($quoted) . '__';
        $quoted[$k] = $m[0];
        return $k;
    }, $content);

    // Salva interpolações PHP
    $interps = [];
    $content = preg_replace_callback('/\{\$[^}]+\}|\$[a-zA-Z_][a-zA-Z0-9_]*/', function($m) use (&$interps) {
        $k = '__I' . count($interps) . '__';
        $interps[$k] = $m[0];
        return $k;
    }, $content);

    // Aplica quoting camelCase
    // Padrão: começa com minúscula, tem pelo menos uma maiúscula
    $q = $escape ? '\\"' : '"';
    $content = preg_replace_callback(
        '/(?<![:\$])(?<!\w)([a-z][a-zA-Z0-9]*[A-Z][a-zA-Z0-9]*)(?!\w)/',
        function($m) use ($q) {
            return $q . $m[1] . $q;
        },
        $content
    );

    // Restaura
    foreach ($interps as $k => $v) $content = str_replace($k, $v, $content);
    foreach ($quoted as $k => $v) $content = str_replace($k, $v, $content);
    foreach ($params as $k => $v) $content = str_replace($k, $v, $content);

    return $content;
}

function processPhp(string $source): string {
    // Correções globais
    $source = preg_replace('/\bCURRENT_TIMESTAMP\(\)/', 'CURRENT_TIMESTAMP', $source);

    // GROUP_CONCAT → STRING_AGG
    $source = preg_replace_callback(
        '/GROUP_CONCAT\(([^)]+?)\s+SEPARATOR\s+([\'"][^\'"]*[\'"])\)/',
        function($m) {
            return 'STRING_AGG(' . trim($m[1]) . ', ' . $m[2] . ')';
        },
        $source
    );

    // Duplicate entry → 23505
    $source = preg_replace(
        "/str_contains\(\s*\\\$e->getMessage\(\)\s*,\s*'Duplicate entry'\s*\)/",
        "\$e->getCode() == '23505'",
        $source
    );
    $source = preg_replace(
        '/\$e->errorInfo\[1\]\s*==\s*1062/',
        "\$e->getCode() == '23505'",
        $source
    );
    $source = preg_replace(
        '/\$e->errorInfo\[1\]\s*==\s*1451/',
        "\$e->getCode() == '23503'",
        $source
    );

    // Processa strings PHP char por char
    $result = '';
    $i = 0;
    $n = strlen($source);

    while ($i < $n) {
        $c = $source[$i];

        // Comentário //
        if ($c === '/' && $i + 1 < $n && $source[$i+1] === '/') {
            $j = $i;
            while ($j < $n && $source[$j] !== "\n") $j++;
            $result .= substr($source, $i, $j - $i);
            $i = $j;
            continue;
        }

        // Comentário bloco
        if ($c === '/' && $i + 1 < $n && $source[$i+1] === '*') {
            $end = strpos($source, '*/', $i + 2);
            if ($end === false) { $result .= substr($source, $i); break; }
            $result .= substr($source, $i, $end + 2 - $i);
            $i = $end + 2;
            continue;
        }

        // String aspas simples
        if ($c === "'") {
            $j = $i + 1;
            while ($j < $n) {
                if ($source[$j] === '\\' && $j + 1 < $n) { $j += 2; }
                elseif ($source[$j] === "'") break;
                else $j++;
            }
            $content = substr($source, $i + 1, $j - $i - 1);
            if (hasSql($content)) {
                $result .= "'" . quoteCamels($content, false) . "'";
            } else {
                $result .= substr($source, $i, $j - $i + 1);
            }
            $i = $j + 1;
            continue;
        }

        // String aspas duplas - evita array keys como $row["col"]
        if ($c === '"') {
            $prev = $i > 0 ? $source[$i-1] : '';
            if ($prev === '[') {
                $result .= $c;
                $i++;
                continue;
            }
            $j = $i + 1;
            while ($j < $n) {
                if ($source[$j] === '\\' && $j + 1 < $n) { $j += 2; }
                elseif ($source[$j] === '"') break;
                else $j++;
            }
            $content = substr($source, $i + 1, $j - $i - 1);
            if (hasSql($content)) {
                $result .= '"' . quoteCamels($content, true) . '"';
            } else {
                $result .= substr($source, $i, $j - $i + 1);
            }
            $i = $j + 1;
            continue;
        }

        $result .= $c;
        $i++;
    }

    return $result;
}

$modified = 0;
$errors = 0;

foreach ($PHP_FILES as $fpath) {
    try {
        $original = file_get_contents($fpath);
        if ($original === false) continue;
        $fixed = processPhp($original);
        if ($fixed !== $original) {
            file_put_contents($fpath, $fixed);
            echo "✓ $fpath\n";
            $modified++;
        }
    } catch (Exception $e) {
        echo "✗ $fpath: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\nTotal modificados: $modified | Erros: $errors\n";
