// Arquivo: scripts/tipo_hierarquia.js
// (Este código foi movido de views/tipo_hierarquia.php)

document.addEventListener('DOMContentLoaded', function() {
    
    // Script local para controlar o Modal
    const modalElement = document.getElementById('cadastroModal');
    
    if (modalElement) {
        const modalTitle = document.getElementById('modalLabel');
        const modalAction = document.getElementById('modalAction');
        const modalId = document.getElementById('modalId');
        const inputNome = document.getElementById('modalNome');
        const inputDescricao = document.getElementById('modalDescricao');
        const btnSalvar = document.getElementById('btnSalvar');
        const btnNovo = document.getElementById('btnNovoCadastro');

        // 1. Lógica para abrir o modal no modo INSERIR
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalTitle.textContent = 'Cadastrar Novo Tipo';
                modalAction.value = 'insert';
                modalId.value = '';
                inputNome.value = ''; // Limpa o campo
                inputDescricao.value = ''; // Limpa o campo
                btnSalvar.textContent = 'Salvar Cadastro';
                document.querySelector('.modal-header').classList.remove('bg-info');
                document.querySelector('.modal-header').classList.add('bg-primary');
            });
        }

        // 2. Lógica para abrir o modal no modo EDITAR
        modalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (button && button.classList.contains('btn-edit')) {
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                const descricao = button.getAttribute('data-descricao');
                
                // Preenche os campos para Edição
                modalTitle.textContent = 'Editar Tipo (ID: ' + id + ')';
                modalAction.value = 'update';
                modalId.value = id;
                inputNome.value = nome;
                inputDescricao.value = descricao;
                btnSalvar.textContent = 'Atualizar';

                // Altera a cor do modal para sinalizar o modo Edição
                document.querySelector('.modal-header').classList.remove('bg-primary');
                document.querySelector('.modal-header').classList.add('bg-info');
            }
        });
    }
});