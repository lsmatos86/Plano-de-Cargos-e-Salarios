// Arquivo: scripts/cargos_form.js
// Depende das seguintes variáveis globais inicializadas em views/cargos_form.php:
// habilidadesAssociadas, caracteristicasAssociadas, riscosAssociados, 
// cursosAssociados, recursosGruposAssociados, areasAssociadas, sinonimosAssociados.

$(document).ready(function() {
    
    // --- 1. VARIÁVEIS DE ESTADO (Inicializadas pelo PHP no escopo global) ---
    // Acessando variáveis globais definidas via PHP
    
    // --- 2. FUNÇÕES GENÉRICAS E MAPAS DE ESTADO ---
    
    const entityMaps = {
        habilidade: habilidadesAssociadas, caracteristica: caracteristicasAssociadas, 
        risco: riscosAssociados, curso: cursosAssociados, 
        recursoGrupo: recursosGruposAssociados, area: areasAssociadas,
        sinonimo: sinonimosAssociados
    };

    const attachRemoveListeners = (entityName) => {
        // CORREÇÃO: Re-anexar listeners de exclusão para garantir que funcionem após a manipulação da DOM
        document.querySelectorAll(`[data-entity="${entityName}"]`).forEach(button => {
            // Remove listeners antigos
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            // Adiciona listener novo
            newButton.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                const isNumericId = !isNaN(itemId) && itemId !== null && itemId !== '';

                if (entityName === 'risco' || entityName === 'curso' || entityName === 'habilidade' || entityName === 'caracteristica' || entityName === 'recursoGrupo' || entityName === 'area') {
                    // Remoção baseada no ID numérico
                    entityMaps[entityName] = entityMaps[entityName].filter(item => item.id !== parseInt(itemId));
                } else if (entityName === 'sinonimo') {
                    // Lógica para IDs temporários/Sinônimos
                    entityMaps[entityName] = entityMaps[entityName].filter(item => {
                        const tempId = 'new-' + item.nome.replace(/\s/g, '-');
                        return tempId !== itemId;
                    });
                }
                
                renderMaps[entityName]();
            });
        });
    };
    
    /**
     * Adiciona um item SIMPLES (ID/Nome) e o input oculto à grade.
     * Usado por Característica, RecursoGrupo e Área.
     */
    const addSimpleGridRow = (gridBodyId, itemId, itemName, inputName, hasEditButton = false, entityName) => {
        const gridBody = document.getElementById(gridBodyId);
        
        // Checa por duplicidade
        const existingItem = gridBody.querySelector(`tr[data-id="${itemId}"]`);
        if (existingItem) {
            return;
        }

        const newRow = gridBody.insertRow();
        newRow.setAttribute('data-id', itemId);
        
        // Adiciona me-1 (margin-end: 1) para separação horizontal dos botões
        const actionHtml = hasEditButton ? 
            `<button type="button" class="btn btn-sm btn-info text-white btn-edit-${entityName} me-1" 
                data-id="${itemId}" data-bs-toggle="modal" data-bs-target="#modalEdicao${entityName.charAt(0).toUpperCase() + entityName.slice(1)}" title="Visualizar">
                <i class="fas fa-eye"></i>
            </button>` : '';

        newRow.innerHTML = `
            <td>
                ${itemName}
                <input type="hidden" name="${inputName}[]" value="${itemId}">
            </td>
            <td class="text-center grid-action-cell">
                <div class="d-flex justify-content-center"> ${actionHtml}
                    <button type="button" class="btn btn-sm btn-danger btn-remove-entity" data-id="${itemId}" data-entity="${entityName}" title="Remover">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `;
        return newRow;
    };
    
    // --- 3. FUNÇÕES DE RENDERIZAÇÃO DE GRADES ---

    const normalizeTipo = (tipo) => {
        if (tipo === 'Hardskill' || tipo === 'Hard Skills') return 'Hard Skills';
        if (tipo === 'Softskill' || tipo === 'Soft Skills') return 'Soft Skills';
        return 'Outros Tipos';
    };

    const renderHabilidadesGrid = () => {
        const gridBody = document.getElementById('habilidadesGridBody');
        let html = '';
        
        habilidadesAssociadas.sort((a, b) => a.nome.localeCompare(b.nome)); 

        const habilidadesAgrupadas = habilidadesAssociadas.reduce((acc, item) => {
            const tipo = normalizeTipo(item.tipo); 
            if (!acc[tipo]) acc[tipo] = [];
            acc[tipo].push(item);
            return acc;
        }, {});

        const gruposOrdenados = ['Hard Skills', 'Soft Skills', 'Outros Tipos'];
        
        gruposOrdenados.forEach(tipo => {
            const grupoItens = habilidadesAgrupadas[tipo];
            
            if (grupoItens && grupoItens.length > 0) {
                html += `<tr class="table-group-separator"><td colspan="2" class="fw-bold"><i class="fas fa-tag me-2"></i> ${tipo}</td></tr>`;
                
                grupoItens.forEach(item => {
                    const itemId = item.id;
                    const itemName = item.nome;

                    html += `
                        <tr data-id="${itemId}" data-type="habilidade">
                            <td>
                                ${itemName}
                                <input type="hidden" name="habilidadeId[]" value="${itemId}">
                            </td>
                            <td class="text-center grid-action-cell">
                                <div class="d-flex justify-content-center"> <button type="button" class="btn btn-sm btn-info text-white btn-edit-habilidade me-1" 
                                        data-id="${itemId}" data-bs-toggle="modal" data-bs-target="#modalEdicaoHabilidade" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-entity" data-id="${itemId}" data-entity="habilidade" title="Remover">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }
        });
        
        gridBody.innerHTML = html;
        attachRemoveListeners('habilidade');
        attachEditListeners('habilidade');
    };
    
    const renderCaracteristicasGrid = () => {
        const gridBody = document.getElementById('caracteristicasGridBody');
        gridBody.innerHTML = '';
        caracteristicasAssociadas.forEach(item => {
            addSimpleGridRow('caracteristicasGridBody', item.id, item.nome, 'caracteristicaId', true, 'caracteristica');
        });
        attachRemoveListeners('caracteristica');
        attachEditListeners('caracteristica');
    };

    const renderRecursosGruposGrid = () => {
        const gridBody = document.getElementById('recursosGruposGridBody');
        gridBody.innerHTML = '';
        recursosGruposAssociados.forEach(item => {
            addSimpleGridRow('recursosGruposGridBody', item.id, item.nome, 'recursoGrupoId', true, 'recursoGrupo');
        });
        attachRemoveListeners('recursoGrupo');
        attachEditListeners('recursoGrupo');
    };

    const renderAreasAtuacaoGrid = () => {
        const gridBody = document.getElementById('areasAtuacaoGridBody');
        gridBody.innerHTML = '';
        areasAssociadas.forEach(item => {
            addSimpleGridRow('areasAtuacaoGridBody', item.id, item.nome, 'areaId');
        });
        attachRemoveListeners('area');
    };
    
    const renderRiscosGrid = () => {
        const gridBody = document.getElementById('riscosGridBody');
        gridBody.innerHTML = '';
        
        riscosAssociados.forEach(item => {
            const newRow = gridBody.insertRow();
            newRow.setAttribute('data-id', item.id);
            
            const itemDescricao = item.descricao || '';
            const trimmedDesc = itemDescricao.length > 50 ? itemDescricao.substring(0, 50) + '...' : itemDescricao;

            newRow.innerHTML = `
                <td>
                    ${item.nome}
                    <input type="hidden" name="riscoId[]" value="${item.id}">
                </td>
                <td>
                    <span title="${itemDescricao}">${trimmedDesc}</span>
                    <input type="hidden" name="riscoDescricao[]" value="${itemDescricao}">
                </td>
                <td class="text-center grid-action-cell">
                    <div class="d-flex justify-content-center"> <button type="button" class="btn btn-sm btn-info text-white btn-edit-risco me-1" 
                            data-id="${item.id}" data-bs-toggle="modal" data-bs-target="#modalEdicaoRisco" title="Editar">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger btn-remove-entity" data-id="${item.id}" data-entity="risco" title="Remover">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;
        });
        attachRemoveListeners('risco');
        attachEditListeners('risco'); // Anexa listeners para o botão de edição
    };

    const renderCursosGrid = () => {
        const gridBody = document.getElementById('cursosGridBody');
        gridBody.innerHTML = '';
        
        cursosAssociados.forEach(item => {
            const isObrigatorio = item.obrigatorio === true || item.obrigatorio === 1;
            const badgeClass = isObrigatorio ? 'bg-danger' : 'bg-secondary';
            
            const newRow = gridBody.insertRow();
            newRow.setAttribute('data-id', item.id);
            
            const itemObs = item.obs || '';
            const trimmedObs = itemObs.length > 30 ? itemObs.substring(0, 30) + '...' : itemObs;

            newRow.innerHTML = `
                <td>
                    ${item.nome}
                    <input type="hidden" name="cursoId[]" value="${item.id}">
                </td>
                <td>
                    <span class="badge ${badgeClass}">${isObrigatorio ? 'OBRIGATÓRIO' : 'DESEJÁVEL'}</span>
                    <small class="d-block text-muted" title="${itemObs}">${trimmedObs}</small>
                    <input type="hidden" name="cursoCargoObrigatorio[]" value="${isObrigatorio ? 1 : 0}">
                    <input type="hidden" name="cursoCargoObs[]" value="${item.obs || ''}">
                </td>
                <td class="text-center grid-action-cell">
                    <div class="d-flex justify-content-center"> <button type="button" class="btn btn-sm btn-info text-white btn-edit-curso me-1" 
                            data-id="${item.id}" data-bs-toggle="modal" data-bs-target="#modalEdicaoCurso" title="Editar">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger btn-remove-entity" data-id="${item.id}" data-entity="curso" title="Remover">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;
        });
        attachRemoveListeners('curso');
        attachEditListeners('curso'); // Anexa listeners para o botão de edição
    };
    
    // SINÔNIMOS
    const renderSinonimosGrid = () => {
        const gridBody = document.getElementById('sinonimosGridBody');
        gridBody.innerHTML = '';
        
        sinonimosAssociados.forEach(item => {
            const itemId = item.id || 'new-' + item.nome.replace(/\s/g, '-'); 
            const newRow = gridBody.insertRow();
            newRow.setAttribute('data-id', itemId);
            
            newRow.innerHTML = `
                <td>
                    ${item.nome}
                    <input type="hidden" name="sinonimoNome[]" value="${item.nome}">
                </td>
                <td class="text-center grid-action-cell">
                    <div class="d-flex justify-content-center"> <button type="button" class="btn btn-sm btn-danger btn-remove-entity" data-id="${itemId}" data-entity="sinonimo" title="Remover">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;
        });
        attachRemoveListeners('sinonimo');
    };

    const renderMaps = {
        habilidade: renderHabilidadesGrid, caracteristica: renderCaracteristicasGrid, 
        risco: renderRiscosGrid, curso: renderCursosGrid, 
        recursoGrupo: renderRecursosGruposGrid, area: renderAreasAtuacaoGrid,
        sinonimo: renderSinonimosGrid
    };


    // --- 4. FUNÇÕES DE EDIÇÃO EM MODAL ---
    
    const attachEditListeners = (entityName) => {
        const gridBody = document.getElementById(entityName + 'sGridBody');
        const selector = `.btn-edit-${entityName}`;
        
        // Remove listeners antigos para evitar execução duplicada
        gridBody.querySelectorAll(selector).forEach(oldButton => {
            const newButton = oldButton.cloneNode(true);
            oldButton.parentNode.replaceChild(newButton, oldButton);
        });

        // Adiciona listeners novos
        gridBody.querySelectorAll(selector).forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const itemId = parseInt(this.getAttribute('data-id'));

                if (entityName === 'curso') {
                    setupEditCursoModal(itemId);
                } else if (entityName === 'risco') {
                    setupEditRiscoModal(itemId);
                } else if (entityName === 'habilidade') {
                    setupEditHabilidadeModal(itemId);
                } else if (entityName === 'caracteristica') {
                    setupEditCaracteristicaModal(itemId);
                } else if (entityName === 'recursoGrupo') {
                    setupEditRecursoGrupoModal(itemId);
                }
            });
        });
    };

    // 4.1. SETUP MODAL CURSO
    const setupEditCursoModal = (id) => {
        const item = cursosAssociados.find(i => i.id === id);
        if (!item) return;

        $('#cursoEditNome').text(item.nome);
        $('#cursoEditId').val(item.id);
        $('#cursoEditObrigatorio').prop('checked', item.obrigatorio === 1 || item.obrigatorio === true);
        $('#cursoEditObs').val(item.obs || '');
        
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoCurso'));
        modal.show();
    };

    // 4.2. SALVAR EDIÇÃO CURSO
    document.getElementById('btnSalvarEdicaoCurso').onclick = function() {
        const id = parseInt($('#cursoEditId').val());
        const isObrigatorio = $('#cursoEditObrigatorio').prop('checked');
        const obs = $('#cursoEditObs').val().trim();

        const item = cursosAssociados.find(i => i.id === id);
        if (item) {
            item.obrigatorio = isObrigatorio ? 1 : 0;
            item.obs = obs;
            renderCursosGrid();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoCurso')).hide();
        }
    };
    
    // 4.3. SETUP MODAL RISCO
    const setupEditRiscoModal = (id) => {
        const item = riscosAssociados.find(i => i.id === id);
        if (!item) return;

        $('#riscoEditNome').text(item.nome);
        $('#riscoEditId').val(item.id);
        $('#riscoEditDescricao').val(item.descricao || '');
        
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoRisco'));
        modal.show();
    };
    
    // 4.4. SALVAR EDIÇÃO RISCO
    document.getElementById('btnSalvarEdicaoRisco').onclick = function() {
        const id = parseInt($('#riscoEditId').val());
        const descricao = $('#riscoEditDescricao').val().trim();

        if (descricao) {
            const item = riscosAssociados.find(i => i.id === id);
            if (item) {
                item.descricao = descricao;
                renderRiscosGrid();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoRisco')).hide();
            }
        } else {
            alert('A descrição do risco é obrigatória.');
        }
    };
    
    // 4.5. SETUP MODAL HABILIDADE (VIEW-ONLY)
    const setupEditHabilidadeModal = (id) => {
        const item = habilidadesAssociadas.find(i => i.id === id);
        if (!item) return;
        
        $('#habilidadeEditNome').text(item.nome);
        $('#habilidadeEditId').val(item.id);
        $('#habilidadeEditNomeInput').val(item.nome);
        $('#habilidadeEditTipo').val(normalizeTipo(item.tipo));

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoHabilidade'));
        modal.show();
    };

    // 4.6. SETUP MODAL CARACTERÍSTICA (VIEW-ONLY)
    const setupEditCaracteristicaModal = (id) => {
        const item = caracteristicasAssociadas.find(i => i.id === id);
        if (!item) return;

        $('#caracteristicaEditNome').text(item.nome);
        $('#caracteristicaEditId').val(item.id);
        $('#caracteristicaEditNomeInput').val(item.nome);

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoCaracteristica'));
        modal.show();
    };
    
    // 4.7. SETUP MODAL RECURSO GRUPO (VIEW-ONLY)
    const setupEditRecursoGrupoModal = (id) => {
        const item = recursosGruposAssociados.find(i => i.id === id);
        if (!item) return;

        $('#recursoGrupoEditNome').text(item.nome);
        $('#recursoGrupoEditId').val(item.id);
        $('#recursoGrupoEditNomeInput').val(item.nome);

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoRecursoGrupo'));
        modal.show();
    };


    // --- 5. LISTENERS DE ADIÇÃO (MANTIDOS) ---
    
    const getSelectedOptionsData = (selectId) => {
        const selectedValues = $(`#${selectId}`).val();
        if (!selectedValues) return [];
        
        const data = [];
        const selectElement = document.getElementById(selectId);
        const values = Array.isArray(selectedValues) ? selectedValues : [selectedValues];
        
        values.forEach(value => {
            const option = selectElement.querySelector(`option[value="${value}"]`);
            if (option) {
                data.push({
                    id: parseInt(value),
                    nome: option.getAttribute('data-nome'),
                    tipo: option.getAttribute('data-tipo')
                });
            }
        });
        return data;
    };
    
    const handleMultiSelectAssociation = (selectId, stateArray, renderFunction) => {
        const selectedItems = getSelectedOptionsData(selectId);
        let addedCount = 0;

        selectedItems.forEach(data => {
            const isDuplicate = stateArray.some(item => item.id === data.id);
            if (!isDuplicate) {
                const newItem = { id: data.id, nome: data.nome, ...(data.tipo && { tipo: data.tipo }) };
                stateArray.push(newItem);
                addedCount++;
            }
        });

        if (addedCount > 0) {
            renderFunction();
        }
    };
    
    document.getElementById('btnAssociarHabilidade').onclick = function() {
        handleMultiSelectAssociation('habilidadeSelect', habilidadesAssociadas, renderHabilidadesGrid);
        $('#habilidadeSelect').val(null).trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoHabilidades')).hide();
    };
    
    document.getElementById('btnAssociarCaracteristica').onclick = function() {
        handleMultiSelectAssociation('caracteristicaSelect', caracteristicasAssociadas, renderCaracteristicasGrid);
        $('#caracteristicaSelect').val(null).trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoCaracteristicas')).hide();
    };

    document.getElementById('btnAssociarRecursosGrupos').onclick = function() {
        handleMultiSelectAssociation('recursosGruposSelect', recursosGruposAssociados, renderRecursosGruposGrid);
        $('#recursosGruposSelect').val(null).trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoRecursosGrupos')).hide();
    };

    document.getElementById('btnAssociarAreasAtuacao').onclick = function() {
        handleMultiSelectAssociation('areasAtuacaoSelect', areasAssociadas, renderAreasAtuacaoGrid);
        $('#areasAtuacaoSelect').val(null).trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoAreasAtuacao')).hide();
    };

    document.getElementById('btnAssociarRisco').onclick = function() {
        const data = getSelectedOptionsData('riscoSelect')[0];
        const descricao = document.getElementById('riscoDescricaoInput').value.trim();

        if (data && descricao) {
            const isDuplicate = riscosAssociados.some(item => item.id === data.id);
            if (!isDuplicate) {
                riscosAssociados.push({ id: data.id, nome: data.nome, descricao: descricao });
                renderRiscosGrid();
                
                document.getElementById('riscoDescricaoInput').value = '';
                $('#riscoSelect').val(null).trigger('change');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoRiscos')).hide();
            } else {
                alert('Este tipo de risco já foi associado.');
            }
        } else {
            alert('Por favor, selecione um Risco e preencha a Descrição Específica.');
        }
    };
    
    document.getElementById('btnAssociarCurso').onclick = function() {
        const selectedItems = getSelectedOptionsData('cursoSelect');
        const isObrigatorio = document.getElementById('cursoObrigatorioInput').checked;
        const obs = document.getElementById('cursoObsInput').value.trim();
        let addedCount = 0;

        selectedItems.forEach(data => {
            const isDuplicate = cursosAssociados.some(item => item.id === data.id);
            
            if (!isDuplicate) {
                cursosAssociados.push({
                    id: data.id,
                    nome: data.nome,
                    obrigatorio: isObrigatorio ? 1 : 0, 
                    obs: obs
                });
                addedCount++;
            }
        });

        if (addedCount > 0) {
            renderCursosGrid();
        }
        
        document.getElementById('cursoObsInput').value = '';
        document.getElementById('cursoObrigatorioInput').checked = false;
        $('#cursoSelect').val(null).trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoCursos')).hide();
    };
    
    document.getElementById('btnAddSinonimo').onclick = function() {
        const input = document.getElementById('sinonimoInput');
        const nome = input.value.trim();

        if (nome) {
            const isDuplicate = sinonimosAssociados.some(item => item.nome.toLowerCase() === nome.toLowerCase());

            if (!isDuplicate) {
                sinonimosAssociados.push({ id: null, nome: nome }); 
                renderSinonimosGrid();
                input.value = ''; 
            } else {
                alert('Sinônimo já adicionado.');
            }
        } else {
            alert('Digite um nome válido.');
        }
    };


    // --- 6. INICIALIZAÇÃO GERAL ---

    function initSelect2() {
        $('.searchable-select').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: "Buscar e selecionar...",
            minimumInputLength: 2, 
            dropdownParent: $('body'),
            language: {
                inputTooShort: (args) => `Digite ${args.minimum - args.input.length} ou mais caracteres para buscar.`,
            },
            templateResult: (data, container) => {
                if (data.element && data.element.closest('optgroup')) {
                    return $('<span>' + data.element.closest('optgroup').label + ' > ' + data.text + '</span>');
                }
                return data.text;
            }
        });
        
        const initModalSelect2 = (selector, parentId) => {
             $(selector).select2({ 
                theme: "bootstrap-5", 
                width: '100%', 
                placeholder: "Buscar ou selecionar...",
                dropdownParent: $(parentId),
                allowClear: true
            });
        };
        
        initModalSelect2('#habilidadeSelect', '#modalAssociacaoHabilidades');
        initModalSelect2('#caracteristicaSelect', '#modalAssociacaoCaracteristicas');
        initModalSelect2('#riscoSelect', '#modalAssociacaoRiscos');
        initModalSelect2('#cursoSelect', '#modalAssociacaoCursos');
        initModalSelect2('#recursosGruposSelect', '#modalAssociacaoRecursosGrupos');
        initModalSelect2('#areasAtuacaoSelect', '#modalAssociacaoAreasAtuacao');
    }
    
    // Executa Select2
    initSelect2();

    // Ativação da primeira aba
    var firstTab = document.querySelector('#basicas-tab');
    if (firstTab) {
        new bootstrap.Tab(firstTab).show();
    }
    
    // Chamadas de renderização final
    renderHabilidadesGrid();
    renderCaracteristicasGrid();
    renderRiscosGrid();
    renderCursosGrid();
    renderRecursosGruposGrid();
    renderAreasAtuacaoGrid();
    renderSinonimosGrid(); 
});