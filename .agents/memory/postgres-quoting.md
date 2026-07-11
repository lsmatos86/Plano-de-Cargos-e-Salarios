---
name: PostgreSQL camelCase quoting
description: Rules and helpers for quoting camelCase SQL identifiers in this PHP app
---

# PostgreSQL camelCase quoting

**Rule:** Every camelCase column name (e.g. `usuarioId`, `cargoNome`) must be double-quoted in SQL strings. Without quotes, PostgreSQL lowercases identifiers and fails with "column does not exist".

**Why:** The schema was converted from MySQL where identifiers are case-insensitive. PostgreSQL preserves case only for quoted identifiers.

**How to apply:**
- Single-quoted PHP strings: use literal `"col"` → `'SELECT "usuarioId" FROM usuarios'`
- Double-quoted PHP strings: escape → `"SELECT \"usuarioId\" FROM usuarios"`
- Dynamic ORDER BY / column names: use `\App\Core\Database::quoteIdent($col)` — handles `alias.column` format too
- Dynamic INSERT/UPDATE field arrays: wrap with `array_map(fn($f) => '"'.$f.'"', $fields)`
- Re-run `scripts/fix_postgres.php` after adding new SQL queries to auto-quote static strings

**Known wrong column names fixed:**
- `escolaridadeOrdem` → does not exist → use `"escolaridadeId"`
- `faixaOrdem` → does not exist → use `"faixaId"`
- `nivelNome` → does not exist → use `"nivelDescricao"` (aliased as `"nivelNome"` in SELECT)
