// Arquivo: scripts/riscos.js
// (Este código foi movido de views/riscos.php)

document.addEventListener('DOMContentLoaded', function() {
    
    const modalElement = document.getElementById('cadastroModal');
    
    if (modalElement) {
        // Campos do Modal
        const modalTitle = document.getElementById('modalLabel');
        const modalAction = document.getElementById('modalAction');
        const modalNome = document.getElementById('modalNome'); // O <select>
        const btnSalvar = document.getElementById('btnSalvar');
        const btnNovo = document.getElementById('btnNovoCadastro');

        // 1. Lógica para abrir o modal no modo INSERIR
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalTitle.textContent = 'Cadastrar Novo Risco';
                modalAction.value = 'insert';
                modalNome.value = ''; // Reseta o select
                btnSalvar.textContent = 'Salvar Cadastro';
                
                // Garante que o header esteja na cor correta
                document.querySelector('.modal-header').classList.remove('bg-info');
                document.querySelector('.modal-header').classList.add('bg-primary');
            });
        }
        
        // Esta página não possui modo 'Editar' no modal,
        // pois o campo principal (riscoNome) é um ENUM.
    }
});