// Arquivo: scripts/cursos.js
// (Este código foi movido de views/cursos.php)

document.addEventListener('DOMContentLoaded', function() {
    
    const modalElement = document.getElementById('cadastroModal');
    
    if (modalElement) {
        // Campos do Modal
        const modalTitle = document.getElementById('modalLabel');
        const modalAction = document.getElementById('modalAction');
        const modalId = document.getElementById('modalId');
        const modalNome = document.getElementById('modalNome');
        const modalDescricao = document.getElementById('modalDescricao');
        const btnSalvar = document.getElementById('btnSalvar');
        const btnNovo = document.getElementById('btnNovoCadastro');

        // 1. Lógica para abrir o modal no modo INSERIR
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalTitle.textContent = 'Cadastrar Novo Curso';
                modalAction.value = 'insert';
                modalId.value = '';
                modalNome.value = '';
                modalDescricao.value = '';
                btnSalvar.textContent = 'Salvar Cadastro';
                
                document.querySelector('.modal-header').classList.remove('bg-info');
                document.querySelector('.modal-header').classList.add('bg-primary');
            });
        }

        // 2. Lógica para abrir o modal no modo EDITAR
        modalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            if (button && button.classList.contains('btn-edit')) {
                // Pega os dados dos atributos data-* do botão
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                const descricao = button.getAttribute('data-descricao');
                
                // Preenche os campos para Edição
                modalTitle.textContent = 'Editar Curso (ID: ' + id + ')';
                modalAction.value = 'update';
                modalId.value = id;
                modalNome.value = nome;
                modalDescricao.value = descricao;
                btnSalvar.textContent = 'Atualizar';

                // Altera a cor do modal para sinalizar o modo Edição
                document.querySelector('.modal-header').classList.remove('bg-primary');
                document.querySelector('.modal-header').classList.add('bg-info');
            }
        });
    }
});