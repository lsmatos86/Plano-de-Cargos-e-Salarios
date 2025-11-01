// Arquivo: scripts/nivel_hierarquico.js
// (Este código foi movido de views/nivel_hierarquico.php)

document.addEventListener('DOMContentLoaded', function() {
    
    // Seleciona todos os elementos do formulário
    const formTitle = document.getElementById('formTitle');
    const formId = document.getElementById('formId');
    const formOrdem = document.getElementById('formOrdem');
    const formTipo = document.getElementById('formTipo');
    const formDescricao = document.getElementById('formDescricao');
    const formAtribuicoes = document.getElementById('formAtribuicoes');
    const formAutonomia = document.getElementById('formAutonomia');
    const formQuandoUtilizar = document.getElementById('formQuandoUtilizar');
    const btnSalvar = document.getElementById('btnSalvar');
    const form = document.getElementById('cadastroForm');
    const header = document.getElementById('formHeader'); // ID adicionado ao card-header
    const btnLimpar = document.getElementById('btnLimpar');

    // Função para limpar e resetar o formulário (modo "Novo")
    function resetForm() {
        if (!form) return; // Se o formulário não existir, para
        
        formTitle.innerHTML = '<i class="fas fa-plus"></i> Novo Nível';
        formId.value = '';
        formOrdem.value = '';
        formTipo.selectedIndex = 0;
        formDescricao.value = '';
        formAtribuicoes.value = '';
        formAutonomia.value = '';
        formQuandoUtilizar.value = '';
        
        btnSalvar.innerHTML = '<i class="fas fa-check"></i> Salvar';
        btnSalvar.classList.remove('btn-info');
        btnSalvar.classList.add('btn-primary');
        header.classList.remove('bg-info');
        header.classList.add('bg-primary');
        form.action = 'nivel_hierarquico.php'; // Garante que a ação é a padrão
    }

    // Listener para o botão Limpar
    if (btnLimpar) {
        btnLimpar.addEventListener('click', resetForm);
    }

    // Adiciona listener para todos os botões de edição
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); 
            
            const data = this.dataset;

            // Preenche o formulário com os dados do botão
            formTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Nível (ID: ' + data.id + ')';
            formId.value = data.id;
            formOrdem.value = data.ordem;
            formTipo.value = data.tipoid;
            formDescricao.value = data.descricao;
            formAtribuicoes.value = data.atribuicoes;
            formAutonomia.value = data.autonomia;
            formQuandoUtilizar.value = data.quando;
            
            // Muda a aparência do botão e do cabeçalho
            btnSalvar.innerHTML = '<i class="fas fa-check"></i> Atualizar';
            btnSalvar.classList.remove('btn-primary');
            btnSalvar.classList.add('btn-info');
            header.classList.remove('bg-primary');
            header.classList.add('bg-info');
            
            formOrdem.focus(); // Foca no primeiro campo
            window.scrollTo(0, 0); // Rola a página para o topo
        });
    });
});