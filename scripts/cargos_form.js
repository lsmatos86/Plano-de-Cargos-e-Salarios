// Arquivo: scripts/cargos_form.js
// Depende das seguintes variáveis globais inicializadas em views/cargos_form.php:
// window.habilidadesAssociadas, window.caracteristicasAssociadas, etc.

$(document).ready(function() {
    
    // --- 1. FUNÇÕES GENÉRICAS E MAPAS DE ESTADO ---
    
    // Função auxiliar para buscar o array de estado global correto de forma segura
    const getEntityMap = (entityName) => {
        const globalVar = window[`${entityName}sAssociadas`];
        return Array.isArray(globalVar) ? globalVar : [];
    };
    
    /**
     * Adiciona um item SIMPLES (ID/Nome) e o input oculto à grade.
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
        if (typeof tipo !== 'string') return 'Outros Tipos'; 
        if (tipo === 'Hardskill' || tipo === 'Hard Skills') return 'Hard Skills';
        if (tipo === 'Softskill' || tipo === 'Soft Skills') return 'Soft Skills';
        return 'Outros Tipos';
    };

    const renderHabilidadesGrid = () => {
        const gridBody = document.getElementById('habilidadesGridBody');
        let html = '';
        
        const habilidadesAssociadas = getEntityMap('habilidade'); // Usa getEntityMap para segurança

        if (habilidadesAssociadas.length === 0) {
             gridBody.innerHTML = '<tr><td colspan="2" class="text-muted">Nenhuma Habilidade associada.</td></tr>';
             return;
        }
        
        // Ordenação defensiva
        habilidadesAssociadas.sort((a, b) => {
             const nomeA = a.nome || '';
             const nomeB = b.nome || '';
             return nomeA.localeCompare(nomeB);
        }); 

        const habilidadesAgrupadas = habilidadesAssociadas.reduce((acc, item) => {
            const tipo = normalizeTipo(item.tipo); 
            if (!acc[tipo]) acc[tipo] = [];
            acc[tipo].push(item);
            return acc;
        }, {});

        const gruposOrdenados = ['Hard Skills', 'Soft Skills', 'Outros Tipos'];
        let hasContent = false;
        
        gruposOrdenados.forEach(tipo => {
            const grupoItens = habilidadesAgrupadas[tipo];
            
            if (grupoItens && grupoItens.length > 0) {
                html += `<tr class="table-group-separator"><td colspan="2" class="fw-bold"><i class="fas fa-tag me-2"></i> ${tipo}</td></tr>`;
                hasContent = true;
                
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
        if (!hasContent) { // Fallback para caso não haja grupos, embora o primeiro if já trate isso
             gridBody.innerHTML = '<tr><td colspan="2" class="text-muted">Nenhuma Habilidade associada.</td></tr>';
        }
        attachEditListeners('habilidade');
    };
    
    // FUNÇÃO GERAL PARA RENDERS SIMPLES
    const renderSimpleGrid = (entityName, hasEditButton = false, cols = 2) => {
        const gridBody = document.getElementById(`${entityName}sGridBody`);
        gridBody.innerHTML = '';
        
        const dataArray = getEntityMap(entityName);

        if (dataArray.length === 0) {
             gridBody.innerHTML = `<tr><td colspan="${cols}" class="text-muted">Nenhuma ${entityName.charAt(0).toUpperCase() + entityName.slice(1)} associada.</td></tr>`;
             return;
        }

        dataArray.forEach(item => {
            addSimpleGridRow(`${entityName}sGridBody`, item.id, item.nome, `${entityName}Id`, hasEditButton, entityName);
        });
        
        if (hasEditButton) {
            attachEditListeners(entityName);
        }
    };
    
    // MAPEAMENTO DAS FUNÇÕES SIMPLES
    const renderCaracteristicasGrid = () => renderSimpleGrid('caracteristica', true);
    const renderRecursosGruposGrid = () => renderSimpleGrid('recursoGrupo', true);
    const renderAreasAtuacaoGrid = () => renderSimpleGrid('area', false);

    
    const renderRiscosGrid = () => {
        const gridBody = document.getElementById('riscosGridBody');
        gridBody.innerHTML = '';
        
        const riscosAssociados = getEntityMap('risco');

        if (riscosAssociados.length === 0) {
             gridBody.innerHTML = '<tr><td colspan="3" class="text-muted">Nenhum Risco de Exposição associado.</td></tr>';
             return;
        }

        // LEITURA da variável global
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
        attachEditListeners('risco');
    };

    const renderCursosGrid = () => {
        const gridBody = document.getElementById('cursosGridBody');
        gridBody.innerHTML = '';
        
        const cursosAssociados = getEntityMap('curso');

        if (cursosAssociados.length === 0) {
             gridBody.innerHTML = '<tr><td colspan="3" class="text-muted">Nenhum Curso associado.</td></tr>';
             return;
        }

        // LEITURA da variável global
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
        attachEditListeners('curso');
    };
    
    // SINÔNIMOS
    const renderSinonimosGrid = () => {
        const gridBody = document.getElementById('sinonimosGridBody');
        gridBody.innerHTML = '';
        
        const sinonimosAssociados = getEntityMap('sinonimo');

        if (sinonimosAssociados.length === 0) {
             gridBody.innerHTML = '<tr><td colspan="2" class="text-muted">Nenhum Sinônimo associado.</td></tr>';
             return;
        }

        // LEITURA da variável global
        sinonimosAssociados.forEach(item => {
            const itemId = item.id ? item.id.toString() : 'new-' + item.nome.replace(/\s/g, '-'); 
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
    };

    const renderMaps = {
        habilidade: renderHabilidadesGrid, caracteristica: renderCaracteristicasGrid, 
        risco: renderRiscosGrid, curso: renderCursosGrid, 
        recursoGrupo: renderRecursosGruposGrid, area: renderAreasAtuacaoGrid,
        sinonimo: renderSinonimosGrid
    };


    // --- 4. FUNÇÕES DE EDIÇÃO EM MODAL ---
    
    const attachEditListeners = (entityName) => {
        const gridBodySelector = `#${entityName}sGridBody`;
        const selector = `.btn-edit-${entityName}`;

        $(gridBodySelector).off('click', selector);

        $(gridBodySelector).on('click', selector, function(e) {
            e.preventDefault();
            const itemId = parseInt($(this).data('id'));

            // Lê o array global correto para encontrar o item
            const stateArray = getEntityMap(entityName);
            if (stateArray.length === 0) return;

            const item = stateArray.find(i => i.id === itemId);
            if (!item) return;

            if (entityName === 'curso') {
                setupEditCursoModal(item);
            } else if (entityName === 'risco') {
                setupEditRiscoModal(item);
            } else if (entityName === 'habilidade') {
                setupEditHabilidadeModal(item);
            } else if (entityName === 'caracteristica') {
                setupEditCaracteristicaModal(item);
            } else if (entityName === 'recursoGrupo') {
                setupEditRecursoGrupoModal(item);
            }
        });
    };

    // 4.1. SETUP MODAL CURSO
    const setupEditCursoModal = (item) => {
        $('#cursoEditNome').text(item.nome);
        $('#cursoEditId').val(item.id);
        $('#cursoEditObrigatorio').prop('checked', item.obrigatorio === 1 || item.obrigatorio === true);
        $('#cursoEditObs').val(item.obs || '');
        
        const modalEl = document.getElementById('modalEdicaoCurso');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    // 4.2. SALVAR EDIÇÃO CURSO
    document.getElementById('btnSalvarEdicaoCurso').onclick = function() {
        const id = parseInt($('#cursoEditId').val());
        const isObrigatorio = $('#cursoEditObrigatorio').prop('checked');
        const obs = $('#cursoEditObs').val().trim();

        const item = getEntityMap('curso').find(i => i.id === id); // Atualiza o array global
        if (item) {
            item.obrigatorio = isObrigatorio ? 1 : 0;
            item.obs = obs;
            renderCursosGrid(); // Re-renderiza lendo o array global
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoCurso')).hide();
        }
    };
    
    // 4.3. SETUP MODAL RISCO
    const setupEditRiscoModal = (item) => {
        $('#riscoEditNome').text(item.nome);
        $('#riscoEditId').val(item.id);
        $('#riscoEditDescricao').val(item.descricao || '');
        
        const modalEl = document.getElementById('modalEdicaoRisco');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };
    
    // 4.4. SALVAR EDIÇÃO RISCO
    document.getElementById('btnSalvarEdicaoRisco').onclick = function() {
        const id = parseInt($('#riscoEditId').val());
        const descricao = $('#riscoEditDescricao').val().trim();

        if (descricao) {
            const item = getEntityMap('risco').find(i => i.id === id); // Atualiza o array global
            if (item) {
                item.descricao = descricao;
                renderRiscosGrid(); // Re-renderiza lendo o array global
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoRisco')).hide();
            }
        } else {
            alert('A descrição do risco é obrigatória.');
        }
    };
    
    // 4.5. SETUP MODAL HABILIDADE (VIEW-ONLY)
    const setupEditHabilidadeModal = (item) => {
        $('#habilidadeEditNome').text(item.nome);
        $('#habilidadeEditId').val(item.id);
        $('#habilidadeEditNomeInput').val(item.nome);
        $('#habilidadeEditTipo').val(normalizeTipo(item.tipo));

        const modalEl = document.getElementById('modalEdicaoHabilidade');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    // 4.6. SETUP MODAL CARACTERÍSTICA (VIEW-ONLY)
    const setupEditCaracteristicaModal = (item) => {
        $('#caracteristicaEditNome').text(item.nome);
        $('#caracteristicaEditId').val(item.id);
        $('#caracteristicaEditNomeInput').val(item.nome);

        const modalEl = document.getElementById('modalEdicaoCaracteristica');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };
    
    // 4.7. SETUP MODAL RECURSO GRUPO (VIEW-ONLY)
    const setupEditRecursoGrupoModal = (item) => {
        $('#recursoGrupoEditNome').text(item.nome);
        $('#recursoGrupoEditId').val(item.id);
        $('#recursoGrupoEditNomeInput').val(item.nome);

        const modalEl = document.getElementById('modalEdicaoRecursoGrupo');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
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
    
    const handleMultiSelectAssociation = (selectId, entityName, renderFunction) => {
        const selectedItems = getSelectedOptionsData(selectId);
        const stateArray = getEntityMap(entityName);
        let addedCount = 0;

        selectedItems.forEach(data => {
            const isDuplicate = stateArray.some(item => item.id === data.id);
            if (!isDuplicate) {
                const newItem = { id: data.id, nome: data.nome, ...(data.tipo && { tipo: data.tipo }) };
                stateArray.push(newItem); // Modifica o array global por referência
                addedCount++;
            }
        });

        if (addedCount > 0) {
            renderFunction();
        }
    };
    
    document.getElementById('btnAssociarHabilidade').onclick = function() {
        handleMultiSelectAssociation('habilidadeSelect', 'habilidade', renderHabilidadesGrid);
        $('#habilidadeSelect').val(null).trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoHabilidades')).hide();
    };
    
    document.getElementById('btnAssociarCaracteristica').onclick = function() {
        handleMultiSelectAssociation('caracteristicaSelect', 'caracteristica', renderCaracteristicasGrid);
        $('#caracteristicaSelect').val(null).trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoCaracteristicas')).hide();
    };

    document.getElementById('btnAssociarRecursosGrupos').onclick = function() {
        handleMultiSelectAssociation('recursosGruposSelect', 'recursoGrupo', renderRecursosGruposGrid);
        $('#recursosGruposSelect').val(null).trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoRecursosGrupos')).hide();
    };

    document.getElementById('btnAssociarAreasAtuacao').onclick = function() {
        handleMultiSelectAssociation('areasAtuacaoSelect', 'area', renderAreasAtuacaoGrid);
        $('#areasAtuacaoSelect').val(null).trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoAreasAtuacao')).hide();
    };

    document.getElementById('btnAssociarRisco').onclick = function() {
        const data = getSelectedOptionsData('riscoSelect')[0];
        const descricao = document.getElementById('riscoDescricaoInput').value.trim();
        const riscosAssociados = getEntityMap('risco');

        if (data && descricao) {
            const isDuplicate = riscosAssociados.some(item => item.id === data.id);
            if (!isDuplicate) {
                riscosAssociados.push({ id: data.id, nome: data.nome, descricao: descricao }); // Modifica o array global
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
        const cursosAssociados = getEntityMap('curso');
        let addedCount = 0;

        selectedItems.forEach(data => {
            const isDuplicate = cursosAssociados.some(item => item.id === data.id);
            
            if (!isDuplicate) {
                cursosAssociados.push({ // Modifica o array global
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
        const sinonimosAssociados = getEntityMap('sinonimo');

        if (nome) {
            const isDuplicate = sinonimosAssociados.some(item => item.nome.toLowerCase() === nome.toLowerCase());

            if (!isDuplicate) {
                sinonimosAssociados.push({ id: null, nome: nome }); // Modifica o array global
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
            dropdownParent: $('body'),
            language: {
                inputTooShort: (args) => `Digite ${args.minimum - args.input.length} ou mais caracteres para buscar.`,
                noResults: () => "Nenhum resultado encontrado",
                searching: () => "Buscando..."
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
    
    initSelect2();

    var firstTab = document.querySelector('#basicas-tab');
    if (firstTab) {
        new bootstrap.Tab(firstTab).show();
    }
    
    // Chamadas de renderização inicial (leem os globais)
    renderHabilidadesGrid();
    renderCaracteristicasGrid();
    renderRiscosGrid();
    renderCursosGrid();
    renderRecursosGruposGrid();
    renderAreasAtuacaoGrid();
    renderSinonimosGrid(); 
    
    
    // --- 7. EVENT DELEGATION PARA REMOÇÃO (CORRIGIDO) ---
    $(document).on('click', '#cargoForm .btn-remove-entity', function() {
        
        const entityName = $(this).data('entity');
        const itemId = $(this).data('id'); 

        if (!entityName || itemId === undefined) {
            console.error('Botão de remoção sem data-entity or data-id');
            return;
        }

        // Pega o array de estado global CORRETO
        const stateArray = getEntityMap(entityName);
        if (stateArray.length === 0) {
            console.error('ERRO: Mapa de estado não encontrado ou vazio para:', entityName);
            return;
        }

        let novoArray;
        
        // Checa se o itemId é um sinônimo recém-adicionado 
        const isNewSinonimo = (entityName === 'sinonimo' && isNaN(itemId));

        if (isNewSinonimo) {
            // Lógica APENAS para sinônimos novos (comparação de string)
            novoArray = stateArray.filter(item => {
                const tempId = item.id ? item.id.toString() : 'new-' + item.nome.replace(/\s/g, '-');
                return tempId !== itemId.toString();
            });
        } else {
            // Lógica para TODOS os IDs numéricos (risco, curso, E sinonimos do DB)
            const numericId = parseInt(itemId);
            if (isNaN(numericId)) {
                 console.error('ERRO: ID inválido para remoção:', itemId);
                 return;
            }
            novoArray = stateArray.filter(item => {
                return parseInt(item.id) !== numericId;
            });
        }
        
        // 1. Limpa o array original (mantendo a referência)
        stateArray.length = 0; 
        
        // 2. Adiciona os itens do novo array (filtrado) DENTRO do array original
        Array.prototype.push.apply(stateArray, novoArray);

        // Agora, a função de renderização lerá a variável global atualizada
        if (renderMaps[entityName]) {
            renderMaps[entityName]();
        } else {
            console.error('ERRO: Função de renderização não encontrada para:', entityName);
        }
    });

    console.log("cargos_form.js (VERSÃO FINAL) carregado e pronto.");
});