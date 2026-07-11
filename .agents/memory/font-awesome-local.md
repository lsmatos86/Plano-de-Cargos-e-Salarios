---
name: Font Awesome local hosting
description: FA 6.5.2 está hospedado localmente para evitar falhas de CDN no ambiente Replit.
---

Font Awesome 6.5.2 instalado localmente via npm em 2026-07-11:
- CSS: /css/fa/all.min.css (servido pelo PHP em /css/fa/all.min.css)
- Fontes: /css/webfonts/*.woff2 e *.ttf
- header.php usa: <link rel="stylesheet" href="/css/fa/all.min.css">

**Why:** O CDN (cdnjs.cloudflare.com) pode ser lento ou indisponível no ambiente Replit, causando ícones invisíveis.

**How to apply:** Ao atualizar a versão do FA, rodar npm install @fortawesome/fontawesome-free@<versao> e copiar css/ e webfonts/ para /css/fa/ e /css/webfonts/.

Ícones FA 6 a observar: fa-trash-alt (alias de fa-trash-can), fa-comment-alt (alias de fa-message), fa-edit (alias de fa-pen-to-square). Todos funcionam via alias no FA 6.5.2 free.
