// Arquivo: scripts/cursos.js
// (Este código é usado em views/cursos.php)

document.addEventListener('DOMContentLoaded', function() {
    
    const modalElement = document.getElementById('cadastroModal');
    
    if (modalElement) {
        // Campos do Modal
        const modalTitle = document.getElementById('modalLabel');
        const modalAction = document.getElementById('modalAction');
        const modalNome = document.getElementById('modalNome');
        const modalDescricao = document.getElementById('modalDescricao');
        const modalPeriodicidade = document.getElementById('modalPeriodicidade');
        const modalId = document.getElementById('modalId');
        const btnSalvar = document.getElementById('btnSalvar');
        const btnNovo = document.getElementById('btnNovoCadastro');

        // 1. Lógica para abrir o modal no modo INSERIR
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalTitle.textContent = 'Cadastrar Novo Curso';
                modalAction.value = 'insert'; // Alinhado com a validação do views/cursos.php
                modalId.value = ''; 
                modalNome.value = '';
                if (modalDescricao) modalDescricao.value = '';
                if (modalPeriodicidade) modalPeriodicidade.value = '';
                btnSalvar.textContent = 'Salvar Cadastro';
                
                // Garante que o header esteja na cor correta
                document.querySelector('.modal-header').classList.remove('bg-info');
                document.querySelector('.modal-header').classList.add('bg-primary');
            });
        }
        
        // 2. Lógica para abrir o modal no modo EDITAR (via delegação de eventos)
        const tableElement = document.querySelector('.table');
        if (tableElement) {
            tableElement.addEventListener('click', function(e) {
                const editButton = e.target.closest('.btn-edit');
                
                if (editButton) {
                    const id = editButton.getAttribute('data-id');
                    const nome = editButton.getAttribute('data-nome');
                    const descricao = editButton.getAttribute('data-descricao') || '';
                    const periodicidade = editButton.getAttribute('data-periodicidade') || '';

                    modalTitle.textContent = 'Editar Curso: ' + nome;
                    modalAction.value = 'update'; // Alinhado com a validação do views/cursos.php
                    modalId.value = id;
                    modalNome.value = nome;
                    if (modalDescricao) modalDescricao.value = descricao;
                    if (modalPeriodicidade) modalPeriodicidade.value = periodicidade;
                    btnSalvar.textContent = 'Salvar Alterações';
                    
                    document.querySelector('.modal-header').classList.remove('bg-primary');
                    document.querySelector('.modal-header').classList.add('bg-info');
                    
                    // Exibe o modal usando a instância do Bootstrap
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modalInstance.show();
                }
            });
        }

        // 3. Lógica para focar de volta no botão "Novo" ao fechar (Acessibilidade)
        modalElement.addEventListener('hidden.bs.modal', function () {
            if (btnNovo) {
                btnNovo.focus();
            }
        });
    }
});