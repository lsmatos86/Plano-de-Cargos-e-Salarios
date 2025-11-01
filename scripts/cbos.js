// Arquivo: scripts/cbos.js
// (Este código foi movido de views/cbos.php)

document.addEventListener('DOMContentLoaded', function() {

    // --- Lógica para o Modal 1: FAMÍLIA CBO ---
    const modalFamilia = document.getElementById('modalFamilia');
    if (modalFamilia) {
        const modalLabel = document.getElementById('modalFamiliaLabel');
        const modalAction = document.getElementById('familiaAction');
        const modalId = document.getElementById('familiaId');
        const modalNome = document.getElementById('familiaNome');
        const btnSalvar = document.getElementById('btnSalvarFamilia');
        const btnNovo = document.getElementById('btnNovaFamilia');

        // 1.1. Abrir modal para INSERIR Família
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalLabel.textContent = 'Cadastrar Nova Família CBO';
                modalAction.value = 'insert';
                modalId.value = '';
                modalNome.value = '';
                btnSalvar.textContent = 'Salvar Família';
                
                document.querySelector('#modalFamilia .modal-header').classList.remove('bg-info');
                document.querySelector('#modalFamilia .modal-header').classList.add('bg-success');
            });
        }

        // 1.2. Abrir modal para EDITAR Família
        modalFamilia.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (button && button.classList.contains('btn-edit-familia')) {
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                
                modalLabel.textContent = 'Editar Família CBO (ID: ' + id + ')';
                modalAction.value = 'update';
                modalId.value = id;
                modalNome.value = nome;
                btnSalvar.textContent = 'Atualizar';

                document.querySelector('#modalFamilia .modal-header').classList.remove('bg-success');
                document.querySelector('#modalFamilia .modal-header').classList.add('bg-info');
            }
        });
    }

    // --- Lógica para o Modal 2: CBO ---
    const modalCBO = document.getElementById('modalCBO');
    if (modalCBO) {
        const modalLabel = document.getElementById('modalCBOLabel');
        const modalAction = document.getElementById('cboAction');
        const modalId = document.getElementById('cboId');
        const modalCod = document.getElementById('cboCod');
        const modalTitulo = document.getElementById('cboTituloOficial');
        const modalFamiliaId = document.getElementById('cboFamiliaId');
        const btnSalvar = document.getElementById('btnSalvarCBO');
        const btnNovo = document.getElementById('btnNovoCBO');

        // 2.1. Abrir modal para INSERIR CBO
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalLabel.textContent = 'Cadastrar Novo CBO';
                modalAction.value = 'insert';
                modalId.value = '';
                modalCod.value = '';
                modalTitulo.value = '';
                modalFamiliaId.value = ''; // Reseta o select
                btnSalvar.textContent = 'Salvar CBO';
                
                document.querySelector('#modalCBO .modal-header').classList.remove('bg-info');
                document.querySelector('#modalCBO .modal-header').classList.add('bg-primary');
            });
        }

        // 2.2. Abrir modal para EDITAR CBO
        modalCBO.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (button && button.classList.contains('btn-edit-cbo')) {
                const id = button.getAttribute('data-id');
                const cod = button.getAttribute('data-cod');
                const titulo = button.getAttribute('data-titulo');
                const familiaId = button.getAttribute('data-familia-id');
                
                modalLabel.textContent = 'Editar CBO (ID: ' + id + ')';
                modalAction.value = 'update';
                modalId.value = id;
                modalCod.value = cod;
                modalTitulo.value = titulo;
                modalFamiliaId.value = familiaId; // Define o <select>
                btnSalvar.textContent = 'Atualizar';

                document.querySelector('#modalCBO .modal-header').classList.remove('bg-primary');
                document.querySelector('#modalCBO .modal-header').classList.add('bg-info');
            }
        });
    }

});