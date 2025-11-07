// Arquivo: scripts/cursos.js
// (Este código é usado em views/cursos.php)

document.addEventListener('DOMContentLoaded', function() {
    
    const modalElement = document.getElementById('cadastroModal');
    
    if (modalElement) {
        // Campos do Modal
        const modalTitle = document.getElementById('modalLabel');
        const modalAction = document.getElementById('modalAction');
        const modalNome = document.getElementById('modalNome');
        const modalId = document.getElementById('modalId'); //
        const btnSalvar = document.getElementById('btnSalvar');
        const btnNovo = document.getElementById('btnNovoCadastro');

        // 1. Lógica para abrir o modal no modo INSERIR
        if (btnNovo) {
            btnNovo.addEventListener('click', function() {
                modalTitle.textContent = 'Cadastrar Novo Curso';
                modalAction.value = 'save';
                modalId.value = '0'; 
                modalNome.value = '';
                btnSalvar.textContent = 'Salvar Cadastro';
                
                // Garante que o header esteja na cor correta
                document.querySelector('.modal-header').classList.remove('bg-info');
                document.querySelector('.modal-header').classList.add('bg-primary');
            });
        }
        
        // 2. Lógica para abrir o modal no modo EDITAR (via delegação de eventos)
        document.querySelector('.table').addEventListener('click', function(e) {
            const editButton = e.target.closest('.btn-edit');
            
            if (editButton) {
                const id = editButton.getAttribute('data-id');
                const nome = editButton.getAttribute('data-nome');

                modalTitle.textContent = 'Editar Curso: ' + nome;
                modalAction.value = 'save'; 
                modalId.value = id;
                modalNome.value = nome;
                btnSalvar.textContent = 'Salvar Edição';
                
                document.querySelector('.modal-header').classList.remove('bg-primary');
                document.querySelector('.modal-header').classList.add('bg-info');
                
                new bootstrap.Modal(modalElement).show();
            }
        });

        // ==================================================================
        // INÍCIO DA CORREÇÃO: Gerenciamento de Foco (Acessibilidade)
        // ==================================================================
        
        // 3. Lógica para focar de volta no botão "Novo" ao fechar
        // Isso corrige o erro de acessibilidade "Blocked aria-hidden"
        modalElement.addEventListener('hidden.bs.modal', function () {
            // Se o botão "Novo" existir, foca nele
            if (btnNovo) {
                btnNovo.focus();
            }
        });
        
        // ==================================================================
        // FIM DA CORREÇÃO
        // ==================================================================
    }
}); 