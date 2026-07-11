---
name: Schema VARCHAR limits
description: Colunas de nomes no schema original MySQL eram VARCHAR(64), já expandidas para 255 no PostgreSQL.
---

As seguintes colunas foram expandidas de VARCHAR(64) para VARCHAR(255) em 2026-07-11:
- habilidades.habilidadeNome
- caracteristicas.caracteristicaNome
- recursos.recursoNome
- recursos_grupos.recursoGrupoNome
- cargo_sinonimos.cargoSinonimoNome
- tipo_hierarquia.tipoNome
- escolaridades.escolaridadeTitulo
- familia_cbo.familiaCboNome
- faixas_salariais.faixaNivel → VARCHAR(100)

**Why:** O schema MySQL original usava VARCHAR(64) para campos de nome, causando truncamento silencioso ao salvar registros com nomes longos.

**How to apply:** Ao criar novas tabelas com colunas de texto livre (nomes, títulos, descrições), usar pelo menos VARCHAR(255). Verificar novas colunas ao adicionar funcionalidades.
