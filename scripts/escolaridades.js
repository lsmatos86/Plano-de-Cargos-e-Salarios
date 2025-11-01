// Arquivo: scripts/escolaridades.js
// (Este código foi movido de views/escolaridades.php)

// Espera que o DOM esteja pronto
document.addEventListener('DOMContentLoaded', function() {
    
    const modalElement = document.getElementById('cadastroModal');
    
    // Certifica-se de que o modal existe nesta página
    if (modalElement) {
        const modalTitle = document.getElementById('modalLabel');
        const modalAction = document.getElementById('modalAction');
        const modalId = document.getElementById('modalId');
        const inputTitulo = document.getElementById('modalTitulo');
        const btnSalvar = document.getElementById('btnSalvar');
        const btnNovo = document.getElementById('btnNovoCadastro');

        // 1. Lógica para abrir o modal no modo INSERIR
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalTitle.textContent = 'Cadastrar Nova Escolaridade';
                modalAction.value = 'insert';
                modalId.value = '';
                inputTitulo.value = ''; // Limpa o campo
                btnSalvar.textContent = 'Salvar Cadastro';
                document.querySelector('.modal-header').classList.remove('bg-info');
                document.querySelector('.modal-header').classList.add('bg-primary');
            });
        }

        // 2. Lógica para abrir o modal no modo EDITAR
        modalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            // Verifica se o botão que abriu o modal é um botão de edição
            if (button && button.classList.contains('btn-edit')) {
                const id = button.getAttribute('data-id');
                const titulo = button.getAttribute('data-titulo');
                
                // Preenche os campos para Edição
                modalTitle.textContent = 'Editar Escolaridade (ID: ' + id + ')';
                modalAction.value = 'update';
                modalId.value = id;
                inputTitulo.value = titulo;
                btnSalvar.textContent = 'Atualizar';

                // Altera a cor do modal para sinalizar o modo Edição
                document.querySelector('.modal-header').classList.remove('bg-primary');
                document.querySelector('.modal-header').classList.add('bg-info');
            }
        });
    }
});