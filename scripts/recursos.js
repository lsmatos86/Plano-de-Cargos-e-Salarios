// Arquivo: scripts/recursos.js
// (Este código foi movido de views/recursos.php)

document.addEventListener('DOMContentLoaded', function() {

    // --- Lógica para o Modal 1: GRUPO ---
    const modalGrupo = document.getElementById('modalGrupo');
    if (modalGrupo) {
        const modalLabel = document.getElementById('modalGrupoLabel');
        const modalAction = document.getElementById('grupoAction');
        const modalId = document.getElementById('grupoId');
        const modalNome = document.getElementById('grupoNome');
        const btnSalvar = document.getElementById('btnSalvarGrupo');
        const btnNovo = document.getElementById('btnNovoGrupo');

        // 1.1. Abrir modal para INSERIR Grupo
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalLabel.textContent = 'Cadastrar Novo Grupo';
                modalAction.value = 'insert';
                modalId.value = '';
                modalNome.value = '';
                btnSalvar.textContent = 'Salvar Grupo';
                
                document.querySelector('#modalGrupo .modal-header').classList.remove('bg-info');
                document.querySelector('#modalGrupo .modal-header').classList.add('bg-success');
            });
        }

        // 1.2. Abrir modal para EDITAR Grupo
        modalGrupo.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (button && button.classList.contains('btn-edit-grupo')) {
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                
                modalLabel.textContent = 'Editar Grupo (ID: ' + id + ')';
                modalAction.value = 'update';
                modalId.value = id;
                modalNome.value = nome;
                btnSalvar.textContent = 'Atualizar';

                document.querySelector('#modalGrupo .modal-header').classList.remove('bg-success');
                document.querySelector('#modalGrupo .modal-header').classList.add('bg-info');
            }
        });
    }

    // --- Lógica para o Modal 2: RECURSO ---
    const modalRecurso = document.getElementById('modalRecurso');
    if (modalRecurso) {
        const modalLabel = document.getElementById('modalRecursoLabel');
        const modalAction = document.getElementById('recursoAction');
        const modalId = document.getElementById('recursoId');
        const modalNome = document.getElementById('recursoNome');
        const modalDescricao = document.getElementById('recursoDescricao');
        // const modalGrupoId = document.getElementById('recursoGrupoId'); // Descomente se voltar a usar o select de grupo
        const btnSalvar = document.getElementById('btnSalvarRecurso');
        const btnNovo = document.getElementById('btnNovoRecurso');

        // 2.1. Abrir modal para INSERIR Recurso
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalLabel.textContent = 'Cadastrar Novo Recurso';
                modalAction.value = 'insert';
                modalId.value = '';
                modalNome.value = '';
                modalDescricao.value = '';
                // modalGrupoId.value = ''; // Reseta o select
                btnSalvar.textContent = 'Salvar Recurso';
                
                document.querySelector('#modalRecurso .modal-header').classList.remove('bg-info');
                document.querySelector('#modalRecurso .modal-header').classList.add('bg-primary');
            });
        }

        // 2.2. Abrir modal para EDITAR Recurso
        modalRecurso.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (button && button.classList.contains('btn-edit-recurso')) {
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                const descricao = button.getAttribute('data-descricao');
                // const grupoId = button.getAttribute('data-grupo-id');
                
                modalLabel.textContent = 'Editar Recurso (ID: ' + id + ')';
                modalAction.value = 'update';
                modalId.value = id;
                modalNome.value = nome;
                modalDescricao.value = descricao;
                // modalGrupoId.value = grupoId; // Define o <select>
                btnSalvar.textContent = 'Atualizar';

                document.querySelector('#modalRecurso .modal-header').classList.remove('bg-primary');
                document.querySelector('#modalRecurso .modal-header').classList.add('bg-info');
            }
        });
    }

});