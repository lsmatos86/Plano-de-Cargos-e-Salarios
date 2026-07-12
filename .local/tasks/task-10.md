---
title: Organograma: modo de visualização por setor
---
# Organograma: Modo "Por Setor"

## What & Why
O organograma atual exibe apenas a hierarquia de supervisão (quem reporta a quem pelo campo `cargoSupervisorId`). Isso não representa a realidade quando o mesmo nome de cargo (ex: "Auxiliar de Escritório") existe em setores distintos com supervisores diferentes. Um modo "Por Setor" constrói a árvore a partir das Áreas de Atuação (`areas_atuacao` com hierarquia via `areaPaiId`) e pendura os cargos em cada área via `cargos_area`, tornando imediatamente visível qual cargo pertence a qual setor e quem é seu supervisor dentro daquele setor.

## Done looks like
- Na página do organograma há dois botões de alternância: **"Por Supervisão"** (modo atual) e **"Por Setor"**
- No modo Por Setor, a árvore D3 exibe os nós de Área (pastas) e os cargos vinculados como filhos de cada área
- Áreas com sub-áreas mostram a hierarquia completa (ex: Operações → Packing House → cargos)
- Cargos vinculados a mais de uma área aparecem em cada área correspondente (duplicação intencional por setor)
- Cargos sem nenhuma área vinculada aparecem numa seção separada "Sem Setor" abaixo da árvore, igual aos "Não Vinculados" do modo atual
- Os nós de Área têm cor/ícone distinto dos nós de Cargo para diferenciá-los visualmente
- A alternância entre modos preserva zoom e posição da tela (ou faz reset suave)
- O PDF do organograma (`relatorios/organograma_pdf.php`) ganha uma segunda seção "Por Setor" ao final do documento

## Out of scope
- Editar a área de um cargo diretamente pelo organograma (permanece no formulário de cargo)
- Filtrar por área específica dentro do organograma (pode ser follow-up)
- Alterar o modo Por Supervisão existente

## Steps
1. **Query PHP para árvore por setor** — Carregar toda a hierarquia de áreas (`areas_atuacao` com `areaPaiId`) e os cargos vinculados a cada área via `cargos_area`, incluindo nome do cargo e nível hierárquico. Serializar como JSON para o frontend, estruturado como árvore recursiva (áreas como nós internos, cargos como folhas).
2. **Botões de alternância no HTML** — Adicionar dois botões acima do SVG ("Por Supervisão" / "Por Setor") que trocam o dataset do D3 e redesenham a árvore sem recarregar a página.
3. **Renderização D3 do modo setor** — Adaptar a função de build do D3 para aceitar os dois formatos de dados. Nós de Área recebem cor diferente (ex: azul-acinzentado) e ícone de pasta; nós de Cargo mantêm as cores atuais por nível hierárquico.
4. **Seção "Sem Setor"** — Cargos sem nenhuma área vinculada aparecem abaixo da árvore num grid de cards, no mesmo estilo da seção "Não Vinculados" do modo atual.
5. **PDF: seção Por Setor** — No `organograma_pdf.php`, adicionar após a seção de supervisão uma nova seção que lista as áreas em ordem hierárquica com os cargos de cada área em sub-listas indentadas.

## Relevant files
- `views/organograma.php`
- `relatorios/organograma_pdf.php`