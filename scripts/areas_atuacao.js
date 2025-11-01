// Arquivo: scripts/areas_atuacao.js
// (Este código foi movido de views/areas_atuacao.php)

document.addEventListener('DOMContentLoaded', function() {
    
    const modalElement = document.getElementById('cadastroModal');
    
    if (modalElement) {
        // Campos do Modal
        const modalTitle = document.getElementById('modalLabel');
        const modalAction = document.getElementById('modalAction');
        const modalId = document.getElementById('modalId');
        const modalNome = document.getElementById('modalNome');
        const modalPaiId = document.getElementById('modalPaiId');
        const modalDescricao = document.getElementById('modalDescricao');
        const btnSalvar = document.getElementById('btnSalvar');
        const btnNovo = document.getElementById('btnNovoCadastro');

        // 1. Lógica para abrir o modal no modo INSERIR
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalTitle.textContent = 'Cadastrar Nova Área';
                modalAction.value = 'insert';
                modalId.value = '';
                modalNome.value = '';
                modalPaiId.value = ''; // Reseta o select
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
                const paiId = button.getAttribute('data-pai-id');
                
                // Preenche os campos para Edição
                modalTitle.textContent = 'Editar Área (ID: ' + id + ')';
                modalAction.value = 'update';
                modalId.value = id;
                modalNome.value = nome;
                modalPaiId.value = paiId; // Define o <select>
                modalDescricao.value = descricao;
                btnSalvar.textContent = 'Atualizar';

                // Altera a cor do modal para sinalizar o modo Edição
                document.querySelector('.modal-header').classList.remove('bg-primary');
                document.querySelector('.modal-header').classList.add('bg-info');
            }
        });
    }
});