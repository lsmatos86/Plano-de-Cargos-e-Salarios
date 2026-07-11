#!/usr/bin/env python3
"""
Corrige compatibilidade PostgreSQL em arquivos PHP.
- Adiciona aspas duplas em identificadores camelCase dentro de strings SQL
- Corrige funções específicas do MySQL
- Corrige checagens de erros MySQL
"""
import re
import os
import glob

SQL_KEYWORDS = re.compile(
    r'\b(SELECT|INSERT|UPDATE|DELETE|FROM|WHERE|ORDER\s+BY|GROUP\s+BY|'
    r'LEFT\s+JOIN|RIGHT\s+JOIN|INNER\s+JOIN|OUTER\s+JOIN|\bJOIN\b|'
    r'\bSET\b|\bINTO\b|LIMIT|OFFSET|RETURNING|COUNT\s*\(|SUM\s*\(|'
    r'MAX\s*\(|MIN\s*\(|HAVING|DISTINCT|ON\s+\w|LIKE\s+[:\?]|'
    r'IN\s*\(|IS\s+NULL|IS\s+NOT\s+NULL|LAG\s*\(|LEAD\s*\(|'
    r'STRING_AGG|OVER\s*\(|WITH\s+\w|CONCAT\s*\(|COALESCE)\b',
    re.IGNORECASE
)

# Padrão camelCase: começa com minúscula, tem pelo menos uma maiúscula
CAMEL_RE = re.compile(r'(?<![\$:])(?<!\w)([a-z][a-zA-Z0-9]*[A-Z][a-zA-Z0-9]*)(?!\w)')

def string_has_sql(s):
    return bool(SQL_KEYWORDS.search(s))

def quote_camels(sql_content, escape=False):
    """Adiciona aspas em identificadores camelCase no conteúdo SQL.
    
    escape=True: usa \\"col\\" (para strings PHP com aspas duplas)
    escape=False: usa "col" (para strings PHP com aspas simples)
    """
    q_open = '\\"' if escape else '"'
    q_close = '\\"' if escape else '"'

    # Salva params nomeados (:param) como placeholders
    params = {}
    pi = [0]
    def save_param(m):
        k = f'__P{pi[0]}__'
        pi[0] += 1
        params[k] = m.group(0)
        return k
    content = re.sub(r':[a-zA-Z_][a-zA-Z0-9_]*', save_param, sql_content)

    # Salva identificadores já entre aspas (escaped ou não)
    quoted = {}
    qi = [0]
    def save_quoted(m):
        k = f'__Q{qi[0]}__'
        qi[0] += 1
        quoted[k] = m.group(0)
        return k

    if escape:
        # Protege \\"identifier\\"
        content = re.sub(r'\\\\"[^"]*\\\\"', save_quoted, content)
    else:
        # Protege "identifier"
        content = re.sub(r'"[^"]*"', save_quoted, content)

    # Salva interpolações PHP {$var} e $var
    interps = {}
    ii = [0]
    def save_interp(m):
        k = f'__I{ii[0]}__'
        ii[0] += 1
        interps[k] = m.group(0)
        return k
    content = re.sub(r'\{\$[^}]+\}|\$[a-zA-Z_][a-zA-Z0-9_]*', save_interp, content)

    # Aplica quoting em camelCase
    def do_quote(m):
        word = m.group(1)
        return f'{q_open}{word}{q_close}'

    content = CAMEL_RE.sub(do_quote, content)

    # Restaura tudo
    for k, v in interps.items():
        content = content.replace(k, v)
    for k, v in quoted.items():
        content = content.replace(k, v)
    for k, v in params.items():
        content = content.replace(k, v)

    return content


def process_php(source):
    """Processa arquivo PHP e corrige SQL strings."""

    # Correções globais no arquivo (fora de strings)
    # CURRENT_TIMESTAMP() → CURRENT_TIMESTAMP
    source = re.sub(r'\bCURRENT_TIMESTAMP\(\)', 'CURRENT_TIMESTAMP', source)

    # GROUP_CONCAT(col SEPARATOR 'sep') → STRING_AGG(col, 'sep')
    source = re.sub(
        r'GROUP_CONCAT\(([^)]+?)\s+SEPARATOR\s+([\'"][^\'"]*[\'"])\)',
        lambda m: f"STRING_AGG({m.group(1).strip()}, {m.group(2)})",
        source
    )

    # Duplicate entry (MySQL) → código 23505 (PostgreSQL)
    source = re.sub(
        r"str_contains\(\s*\$e->getMessage\(\)\s*,\s*'Duplicate entry'\s*\)",
        "$e->getCode() == '23505'",
        source
    )
    source = re.sub(
        r'str_contains\(\s*\$e->getMessage\(\)\s*,\s*"Duplicate entry"\s*\)',
        "$e->getCode() == '23505'",
        source
    )

    # Erro MySQL errorInfo[1] == 1062 → PostgreSQL SQLSTATE 23505
    source = re.sub(
        r'\$e->errorInfo\[1\]\s*==\s*1062',
        "$e->getCode() == '23505'",
        source
    )
    # Erro MySQL errorInfo[1] == 1451 (FK) → PostgreSQL SQLSTATE 23503
    source = re.sub(
        r'\$e->errorInfo\[1\]\s*==\s*1451',
        "$e->getCode() == '23503'",
        source
    )

    # Agora processa strings PHP
    result = []
    i = 0
    n = len(source)

    while i < n:
        c = source[i]

        # Comentário de linha //
        if c == '/' and i + 1 < n and source[i+1] == '/':
            j = i
            while j < n and source[j] != '\n':
                j += 1
            result.append(source[i:j])
            i = j
            continue

        # Comentário de linha #
        if c == '#' and (i == 0 or source[i-1] == '\n' or source[i-1] == ' '):
            j = i
            while j < n and source[j] != '\n':
                j += 1
            result.append(source[i:j])
            i = j
            continue

        # Comentário de bloco /* ... */
        if c == '/' and i + 1 < n and source[i+1] == '*':
            j = source.find('*/', i + 2)
            if j == -1:
                result.append(source[i:])
                break
            result.append(source[i:j+2])
            i = j + 2
            continue

        # String PHP com aspas simples '...'
        if c == "'":
            j = i + 1
            while j < n:
                if source[j] == '\\' and j + 1 < n:
                    j += 2
                elif source[j] == "'":
                    break
                else:
                    j += 1
            content = source[i+1:j]
            if string_has_sql(content):
                fixed = quote_camels(content, escape=False)
                result.append("'" + fixed + "'")
            else:
                result.append(source[i:j+1])
            i = j + 1
            continue

        # String PHP com aspas duplas "..."
        # Evita confundir com $obj["key"] (array access)
        if c == '"':
            # Verifica se não é precedida por [ para evitar array keys como $row["col"]
            prev = source[i-1] if i > 0 else ''
            if prev == '[':
                # É um array key, não é SQL - pula
                result.append(c)
                i += 1
                continue

            j = i + 1
            while j < n:
                if source[j] == '\\' and j + 1 < n:
                    j += 2
                elif source[j] == '"':
                    break
                else:
                    j += 1
            content = source[i+1:j]
            if string_has_sql(content):
                fixed = quote_camels(content, escape=True)
                result.append('"' + fixed + '"')
            else:
                result.append(source[i:j+1])
            i = j + 1
            continue

        result.append(c)
        i += 1

    return ''.join(result)


# ============================================================
# Encontra e processa arquivos PHP (exceto vendor)
# ============================================================
patterns = ['src/**/*.php', 'includes/*.php', 'views/**/*.php',
            '*.php', 'relatorios/**/*.php', 'relatorios/*.php']

php_files = []
for pat in patterns:
    php_files.extend(glob.glob(pat, recursive=True))

php_files = sorted(set(
    f for f in php_files
    if 'vendor' not in f.split(os.sep) and '.git' not in f
))

modified = []
errors = []

for fpath in php_files:
    try:
        with open(fpath, 'r', encoding='utf-8', errors='replace') as f:
            original = f.read()
        new_content = process_php(original)
        if new_content != original:
            with open(fpath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            modified.append(fpath)
    except Exception as e:
        errors.append((fpath, str(e)))

print(f"Modificados: {len(modified)} arquivos")
for f in modified:
    print(f"  ✓ {f}")

if errors:
    print(f"\nErros ({len(errors)}):")
    for f, e in errors:
        print(f"  ✗ {f}: {e}")
