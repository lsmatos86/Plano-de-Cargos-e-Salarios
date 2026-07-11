---
name: PostgreSQL quirks neste projeto
description: Comportamentos específicos do PostgreSQL que diferem do MySQL original.
---

**lastInsertId():** Em PostgreSQL com PDO, lastInsertId() sem argumento retorna string vazia. Sempre passar o nome da sequência: lastInsertId('tabela_colunaId_seq').

**Colunas camelCase:** Todas as colunas têm nomes camelCase (ex: cargoId, nivelHierarquicoId) e precisam de aspas duplas em SQL: "cargoId". Sem aspas o PostgreSQL converte para minúsculas e falha.

**Why:** O schema foi convertido diretamente do MySQL, preservando convenção camelCase, mas PostgreSQL trata identificadores sem aspas como lowercase.

**How to apply:** Em qualquer nova query SQL, sempre usar aspas duplas em nomes de colunas. Em PDO, usar lastInsertId('tabela_coluna_seq') para INSERT.
