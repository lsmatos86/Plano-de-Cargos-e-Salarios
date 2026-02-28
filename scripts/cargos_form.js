// Arquivo: scripts/cargos_form.js

$(document).ready(function() {

    // 1. INICIALIZAÇÃO DO SELECT2
    $('.searchable-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('body') // Permite funcionar dentro e fora de modals
    });

    // Corrige o z-index do Select2 quando aberto dentro de um Modal do Bootstrap
    $('.modal').on('shown.bs.modal', function () {
        $(this).find('.searchable-select').select2({
            theme: 'bootstrap-5',
            dropdownParent: $(this)
        });
    });

    // 2. GESTÃO DE ESTADO (Lê as variáveis globais injetadas pelo PHP)
    const state = {
        habilidade: window.habilidadesAssociadas || [],
        caracteristica: window.caracteristicasAssociadas || [],
        risco: window.riscosAssociados || [],
        curso: window.cursosAssociados || [],
        recursoGrupo: window.recursosGruposAssociados || [],
        area: window.areasAssociadas || [],
        sinonimo: window.sinonimosAssociados || []
    };

    const getEntityMap = (entityName) => state[entityName];

    // 3. FUNÇÕES AUXILIARES
    
    // Captura os dados selecionados no Select2, ignorando placeholders vazios (Bug Fix)
    const getSelectedOptionsData = (selectId) => {
        const selectedValues = $(`#${selectId}`).val();
        if (!selectedValues || selectedValues.length === 0) return [];
        
        const data = [];
        const selectElement = document.getElementById(selectId);
        const values = Array.isArray(selectedValues) ? selectedValues : [selectedValues];
        
        values.forEach(value => {
            if (!value || isNaN(parseInt(value))) return; // Ignora o vazio
            const option = selectElement.querySelector(`option[value="${value}"]`);
            if (option) {
                data.push({
                    id: parseInt(value),
                    nome: option.getAttribute('data-nome') || option.text,
                    tipo: option.getAttribute('data-tipo') || null
                });
            }
        });
        return data;
    };

    // Gera um ID temporário para novos sinónimos
    const generateTempId = () => 'new-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

    // Renderiza genérico para grelhas simples (Habilidades, Características, Áreas, Recursos)
    const renderSimpleGrid = (entityName, gridBodyId) => {
        const tbody = document.getElementById(gridBodyId);
        tbody.innerHTML = '';
        const items = getEntityMap(entityName);
        
        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="2" class="text-center text-muted small py-3">Nenhum item associado.</td></tr>`;
            return;
        }

        items.forEach(item => {
            const tr = document.createElement('tr');
            let nomeDisplay = item.nome;
            if (entityName === 'habilidade' && item.tipo) {
                nomeDisplay = `<span class="badge bg-secondary me-2">${item.tipo}</span> ${item.nome}`;
            }
            tr.innerHTML = `
                <td class="align-middle">${nomeDisplay}</td>
                <td class="grid-action-cell text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-entity" data-entity="${entityName}" data-id="${item.id}" title="Remover"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    };

    // Renderiza a Grelha de Cursos
    const renderCursosGrid = () => {
        const tbody = document.getElementById('cursosGridBody');
        tbody.innerHTML = '';
        if (state.curso.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted small py-3">Nenhum curso associado.</td></tr>`;
            return;
        }
        state.curso.forEach(curso => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="align-middle fw-bold">${curso.nome}</td>
                <td class="align-middle">
                    ${curso.obrigatorio ? '<span class="badge bg-danger me-2">Obrigatório</span>' : '<span class="badge bg-secondary me-2">Desejável</span>'}
                    <span class="small text-muted">${curso.obs || ''}</span>
                </td>
                <td class="grid-action-cell text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-info btn-edit-curso" data-id="${curso.id}" title="Editar Detalhes"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-entity" data-entity="curso" data-id="${curso.id}" title="Remover"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    };

    // Renderiza a Grelha de Riscos
    const renderRiscosGrid = () => {
        const tbody = document.getElementById('riscosGridBody');
        tbody.innerHTML = '';
        if (state.risco.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted small py-3">Nenhum risco associado.</td></tr>`;
            return;
        }
        state.risco.forEach(risco => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="align-middle fw-bold">${risco.nome}</td>
                <td class="align-middle small">${risco.descricao || ''}</td>
                <td class="grid-action-cell text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-info btn-edit-risco" data-id="${risco.id}" title="Editar Descrição"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-entity" data-entity="risco" data-id="${risco.id}" title="Remover"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    };


    // 4. LÓGICA DE ADIÇÃO (Com Proteção contra Vazios/Duplicados)

    // Adicionar Habilidades
    $('#btnAssociarHabilidade').on('click', function() {
        const selectedItems = getSelectedOptionsData('habilidadeSelect');
        if (selectedItems.length === 0) { alert('Por favor, selecione uma Habilidade válida.'); return; }
        
        let addedCount = 0;
        selectedItems.forEach(item => {
            if (!state.habilidade.some(h => h.id === item.id)) {
                state.habilidade.push(item);
                addedCount++;
            }
        });
        if (addedCount > 0) renderSimpleGrid('habilidade', 'habilidadesGridBody');
        $('#habilidadeSelect').val(null).trigger('change');
        $('#modalAssociacaoHabilidades').modal('hide');
    });

    // Adicionar Características
    $('#btnAssociarCaracteristica').on('click', function() {
        const selectedItems = getSelectedOptionsData('caracteristicaSelect');
        if (selectedItems.length === 0) { alert('Por favor, selecione uma Característica.'); return; }

        let addedCount = 0;
        selectedItems.forEach(item => {
            if (!state.caracteristica.some(c => c.id === item.id)) {
                state.caracteristica.push(item);
                addedCount++;
            }
        });
        if (addedCount > 0) renderSimpleGrid('caracteristica', 'caracteristicasGridBody');
        $('#caracteristicaSelect').val(null).trigger('change');
        $('#modalAssociacaoCaracteristicas').modal('hide');
    });

    // Adicionar Áreas de Atuação
    $('#btnAssociarAreasAtuacao').on('click', function() {
        const selectedItems = getSelectedOptionsData('areasAtuacaoSelect');
        if (selectedItems.length === 0) { alert('Por favor, selecione uma Área.'); return; }

        let addedCount = 0;
        selectedItems.forEach(item => {
            if (!state.area.some(a => a.id === item.id)) {
                state.area.push(item);
                addedCount++;
            }
        });
        if (addedCount > 0) renderSimpleGrid('area', 'areasAtuacaoGridBody');
        $('#areasAtuacaoSelect').val(null).trigger('change');
        $('#modalAssociacaoAreasAtuacao').modal('hide');
    });

    // Adicionar Grupos de Recursos
    $('#btnAssociarRecursosGrupos').on('click', function() {
        const selectedItems = getSelectedOptionsData('recursosGruposSelect');
        if (selectedItems.length === 0) { alert('Por favor, selecione um Grupo de Recurso.'); return; }

        let addedCount = 0;
        selectedItems.forEach(item => {
            if (!state.recursoGrupo.some(r => r.id === item.id)) {
                state.recursoGrupo.push(item);
                addedCount++;
            }
        });
        if (addedCount > 0) renderSimpleGrid('recursoGrupo', 'recursosGruposGridBody');
        $('#recursosGruposSelect').val(null).trigger('change');
        $('#modalAssociacaoRecursosGrupos').modal('hide');
    });

    // Adicionar Cursos
    $('#btnAssociarCurso').on('click', function() {
        const selectedItems = getSelectedOptionsData('cursoSelect');
        if (selectedItems.length === 0) { alert('Por favor, selecione um Curso.'); return; }

        const isObrigatorio = $('#cursoObrigatorioInput').is(':checked');
        const obs = $('#cursoObsInput').val().trim();
        let addedCount = 0;

        selectedItems.forEach(item => {
            if (!state.curso.some(c => c.id === item.id)) {
                state.curso.push({ id: item.id, nome: item.nome, obrigatorio: isObrigatorio, obs: obs });
                addedCount++;
            }
        });
        if (addedCount > 0) renderCursosGrid();
        $('#cursoSelect').val(null).trigger('change');
        $('#cursoObrigatorioInput').prop('checked', false);
        $('#cursoObsInput').val('');
        $('#modalAssociacaoCursos').modal('hide');
    });

    // Adicionar Riscos
    $('#btnAssociarRisco').on('click', function() {
        const selectedItems = getSelectedOptionsData('riscoSelect');
        if (selectedItems.length === 0) { alert('Por favor, selecione um Risco.'); return; }

        const descricao = $('#riscoDescricaoInput').val().trim();
        if (descricao === '') { alert('A descrição da exposição é obrigatória.'); return; }

        let addedCount = 0;
        selectedItems.forEach(item => {
            if (!state.risco.some(r => r.id === item.id)) {
                state.risco.push({ id: item.id, nome: item.nome, descricao: descricao });
                addedCount++;
            }
        });
        if (addedCount > 0) renderRiscosGrid();
        $('#riscoSelect').val(null).trigger('change');
        $('#riscoDescricaoInput').val('');
        $('#modalAssociacaoRiscos').modal('hide');
    });

    // Adicionar Sinónimos (Texto Livre)
    $('#btnAddSinonimo').on('click', function() {
        const nome = $('#sinonimoInput').val().trim();
        if (nome === '') { alert('Digite um nome alternativo.'); return; }
        
        if (!state.sinonimo.some(s => s.nome.toLowerCase() === nome.toLowerCase())) {
            state.sinonimo.push({ id: generateTempId(), nome: nome });
            renderSimpleGrid('sinonimo', 'sinonimosGridBody');
        }
        $('#sinonimoInput').val('').focus();
    });
    $('#sinonimoInput').on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); $('#btnAddSinonimo').click(); } });


    // 5. LÓGICA DE REMOÇÃO (Delegação de Eventos)
    $(document).on('click', '.btn-remove-entity', function() {
        const entityName = $(this).data('entity');
        const idToRemove = String($(this).data('id'));
        
        state[entityName] = state[entityName].filter(item => String(item.id) !== idToRemove);
        
        if (entityName === 'curso') renderCursosGrid();
        else if (entityName === 'risco') renderRiscosGrid();
        else renderSimpleGrid(entityName, entityName + 'sGridBody' + (entityName === 'recursoGrupo' ? '' : (entityName === 'area' ? 'AtuacaoGridBody' : 'sGridBody')).replace('ss','s').replace('esG','eG').replace('asGrid','asGrid').replace('sinonimosGrid','sinonimosGrid')); // Fallbacks
        
        if (entityName === 'habilidade') renderSimpleGrid('habilidade', 'habilidadesGridBody');
        else if (entityName === 'caracteristica') renderSimpleGrid('caracteristica', 'caracteristicasGridBody');
        else if (entityName === 'area') renderSimpleGrid('area', 'areasAtuacaoGridBody');
        else if (entityName === 'recursoGrupo') renderSimpleGrid('recursoGrupo', 'recursosGruposGridBody');
        else if (entityName === 'sinonimo') renderSimpleGrid('sinonimo', 'sinonimosGridBody');
    });


    // 6. EDIÇÃO DE CURSOS E RISCOS NAS GRELHAS
    $(document).on('click', '.btn-edit-curso', function() {
        const id = parseInt($(this).data('id'));
        const curso = state.curso.find(c => c.id === id);
        if (curso) {
            $('#cursoEditId').val(curso.id);
            $('#cursoEditNome').text(curso.nome);
            $('#cursoEditObrigatorio').prop('checked', curso.obrigatorio);
            $('#cursoEditObs').val(curso.obs);
            $('#modalEdicaoCurso').modal('show');
        }
    });

    $('#btnSalvarEdicaoCurso').on('click', function() {
        const id = parseInt($('#cursoEditId').val());
        const curso = state.curso.find(c => c.id === id);
        if (curso) {
            curso.obrigatorio = $('#cursoEditObrigatorio').is(':checked');
            curso.obs = $('#cursoEditObs').val().trim();
            renderCursosGrid();
            $('#modalEdicaoCurso').modal('hide');
        }
    });

    $(document).on('click', '.btn-edit-risco', function() {
        const id = parseInt($(this).data('id'));
        const risco = state.risco.find(r => r.id === id);
        if (risco) {
            $('#riscoEditId').val(risco.id);
            $('#riscoEditNome').text(risco.nome);
            $('#riscoEditDescricao').val(risco.descricao);
            $('#modalEdicaoRisco').modal('show');
        }
    });

    $('#btnSalvarEdicaoRisco').on('click', function() {
        const id = parseInt($('#riscoEditId').val());
        const risco = state.risco.find(r => r.id === id);
        if (risco) {
            risco.descricao = $('#riscoEditDescricao').val().trim();
            renderRiscosGrid();
            $('#modalEdicaoRisco').modal('hide');
        }
    });


    // 7. MONTAGEM DO FORMULÁRIO PARA ENVIO (Cria os inputs ocultos)
    $('#cargoForm').on('submit', function() {
        $('.dynamic-hidden-input').remove();
        const form = $(this);
        
        const createHiddenInput = (name, value) => {
            $('<input>').attr({ type: 'hidden', name: name, value: value, class: 'dynamic-hidden-input' }).appendTo(form);
        };

        state.habilidade.forEach(h => createHiddenInput('habilidadeId[]', h.id));
        state.caracteristica.forEach(c => createHiddenInput('caracteristicaId[]', c.id));
        state.area.forEach(a => createHiddenInput('areaId[]', a.id));
        state.recursoGrupo.forEach(rg => createHiddenInput('recursoGrupoId[]', rg.id));
        state.sinonimo.forEach(s => createHiddenInput('sinonimoNome[]', s.nome));

        state.curso.forEach(c => {
            createHiddenInput('cursoId[]', c.id);
            createHiddenInput('cursoCargoObrigatorio[]', c.obrigatorio ? 1 : 0);
            createHiddenInput('cursoCargoObs[]', c.obs || '');
        });

        state.risco.forEach(r => {
            createHiddenInput('riscoId[]', r.id);
            createHiddenInput('riscoDescricao[]', r.descricao || '');
        });

        // Reseta a flag de formulário alterado para permitir o submit
        formFoiAlterado = false;
        return true;
    });


    // 8. RENDERIZAÇÃO INICIAL DAS GRELHAS
    renderSimpleGrid('habilidade', 'habilidadesGridBody');
    renderSimpleGrid('caracteristica', 'caracteristicasGridBody');
    renderSimpleGrid('area', 'areasAtuacaoGridBody');
    renderSimpleGrid('recursoGrupo', 'recursosGruposGridBody');
    renderSimpleGrid('sinonimo', 'sinonimosGridBody');
    renderCursosGrid();
    renderRiscosGrid();


    // =========================================================
    // 9. LÓGICA DE BLOQUEIO E REVISÃO DE CARGOS COM MODAL
    // =========================================================
    const bloquearFormulario = () => {
        $('#cargoForm').find('input, select, textarea, button')
            .not('#btnDesbloquearEdicao').not('#is_revisado').prop('disabled', true);
        $('.searchable-select').prop('disabled', true);
        $('[data-bs-target^="#modalAssociacao"]').hide();
        $('#btnAddSinonimo').hide();
        $('.btn-remove-entity, .btn-edit-curso, .btn-edit-risco').prop('disabled', true);
    };

    const desbloquearFormulario = () => {
        $('#cargoForm').find('input, select, textarea, button').prop('disabled', false);
        $('.searchable-select').prop('disabled', false);
        $('[data-bs-target^="#modalAssociacao"]').show();
        $('#btnAddSinonimo').show();
        $('#is_revisado').prop('checked', false); 
        $('#btnDesbloquearEdicao').hide();
    };

    // Verifica bloqueio inicial
    if ($('#is_revisado').is(':checked') && parseInt($('input[name="cargoId"]').val()) > 0) {
        bloquearFormulario();
    }

    // Modal de Senha
    $('#btnDesbloquearEdicao').on('click', function(e) {
        e.preventDefault();
        $('#senhaDesbloqueioInput').val('');
        $('#erroSenhaDesbloqueio').hide();
        var myModal = new bootstrap.Modal(document.getElementById('modalDesbloqueioSenha'));
        myModal.show();
    });

    $('#modalDesbloqueioSenha').on('shown.bs.modal', function () {
        $('#senhaDesbloqueioInput').focus();
    });

    $('#btnConfirmarDesbloqueio').on('click', function() {
        const senhaMaster = 'admin123'; // Senha de segurança
        const senhaDigitada = $('#senhaDesbloqueioInput').val();

        if (senhaDigitada === senhaMaster) {
            $('#modalDesbloqueioSenha').modal('hide');
            desbloquearFormulario();
        } else {
            $('#erroSenhaDesbloqueio').show();
            $('#senhaDesbloqueioInput').val('').focus();
        }
    });

    $('#senhaDesbloqueioInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btnConfirmarDesbloqueio').click();
        }
    });


    // =========================================================
    // 10. LÓGICA DE NAVEGAÇÃO INTELIGENTE (DIRTY FORM)
    // =========================================================
    let formFoiAlterado = false;

    // Escuta mudanças nos inputs
    $('#cargoForm').on('change input', 'input, select, textarea', function() {
        formFoiAlterado = true;
    });

    // Interceta os botões de navegação e voltar
    $('.btn-nav-smart').on('click', function(e) {
        if ($(this).hasClass('disabled') || $(this).attr('href') === '#') {
            e.preventDefault();
            return;
        }
        
        if (formFoiAlterado) {
            const desejaSair = confirm('Fez alterações neste cargo que ainda não foram salvas.\\n\\nTem certeza que deseja mudar de cargo e PERDER as alterações?');
            if (!desejaSair) {
                e.preventDefault();
            }
        }
    });

});