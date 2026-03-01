// Arquivo: scripts/pesquisa_salarial.js

document.addEventListener("DOMContentLoaded", function() {
    // 1. Manter a aba correta aberta ao recarregar a página
    let hash = window.location.hash;
    if (hash) {
        let tabElement = document.querySelector(hash + '-tab');
        if (tabElement) {
            let tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }

    // Ao clicar numa aba, atualiza a URL para guardar a posição
    let tabLinks = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabLinks.forEach(function(el) {
        el.addEventListener('shown.bs.tab', function (e) {
            window.location.hash = e.target.getAttribute('data-bs-target');
        });
    });

    // 2. Confirmação de encerramento
    let btnEncerraForms = document.querySelectorAll('.btn-encerrar-form');
    btnEncerraForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja encerrar esta pesquisa? Os dados serão trancados para análise estatística.')) {
                e.preventDefault();
            }
        });
    });
});