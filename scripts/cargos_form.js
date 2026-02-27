// Arquivo: scripts/cargos_form.js
// Versão Definitiva: Inclui try/catch em todas as funções de renderização para forçar a depuração de erros.

$(document).ready(function() {
    
    console.log("--- DEBUG START DOM READY ---: Inicializando rotina de renderização das grids.");

    // Mapeamento explícito das entidades (opção A)
    const ENTITY_CONFIG = {
        habilidade:   { global: 'habilidadesAssociadas',    tbody: 'habilidadesGridBody' },
        caracteristica: { global: 'caracteristicasAssociadas', tbody: 'caracteristicasGridBody' },
        risco:        { global: 'riscosAssociados',          tbody: 'riscosGridBody' },
        curso:        { global: 'cursosAssociados',          tbody: 'cursosGridBody' },
        recursoGrupo: { global: 'recursosGruposAssociados',  tbody: 'recursosGruposGridBody' },
        area:         { global: 'areasAssociadas',           tbody: 'areasAtuacaoGridBody' },
        sinonimo:     { global: 'sinonimosAssociados',       tbody: 'sinonimosGridBody' }
    };

    // Função auxiliar para buscar o array de estado global de forma segura
    const getEntityMap = (entityName) => {
        const cfg = ENTITY_CONFIG[entityName];
        if (cfg) {
            const globalVar = window[cfg.global];
            // Garante que é um array ou tenta converter o objeto associativo (se PHP não fez)
            if (Array.isArray(globalVar)) return globalVar;
            if (globalVar && typeof globalVar === 'object') {
                return Object.values(globalVar); 
            }
        }
        return [];
    };

    // Resolve o ID do <tbody> para um dado entityName usando ENTITY_CONFIG como fonte de verdade
    const resolveGridBodyId = (entityName) => {
        const cfg = ENTITY_CONFIG[entityName];
        if (cfg && cfg.tbody && document.getElementById(cfg.tbody)) return cfg.tbody;
        return `${entityName}sGridBody`;
    };

    // Helper: limpa todos os filhos de um elemento (AGORA CONSISTENTE COM JQUERY)
    const clearElementChildren = (el) => {
        if (el) {
            $(el).empty();
        }
    };

    // Helper: cria uma linha de 'nenhum registro' de forma segura (AGORA CONSISTENTE COM JQUERY)
    const createEmptyRow = (gridBody, colspan, text) => {
        if (!gridBody) return;
        $(gridBody).empty(); 
        const tr = `
            <tr>
                <td colspan="${String(colspan)}" class="text-muted">
                    ${text}
                </td>
            </tr>
        `;
        $(gridBody).append(tr);
    };
    
    /**
     * Adiciona um item SIMPLES (ID/Nome) e o input oculto à grade.
     */
    const addSimpleGridRow = (gridBodyId, itemId, itemName, inputName, hasEditButton = false, entityName) => {
        const gridBody = document.getElementById(gridBodyId);
        if (!gridBody) {
            console.warn(`Grid body ${gridBodyId} not found`);
            return null;
        }

        // Checa por duplicidade
        const existingItem = gridBody.querySelector(`tr[data-id="${itemId}"]`);
        if (existingItem) {
            return existingItem;
        }

        const tr = document.createElement('tr');
        tr.setAttribute('data-id', String(itemId));

        // Coluna do nome + input hidden
        const tdName = document.createElement('td');
        tdName.textContent = itemName || '';
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = `${inputName}[]`;
        hidden.value = String(itemId);
        tdName.appendChild(hidden);

        // Coluna de ações
        const tdAction = document.createElement('td');
        tdAction.className = 'text-center grid-action-cell';
        const divActions = document.createElement('div');
        divActions.className = 'd-flex justify-content-center';

        if (hasEditButton) {
            const btnEdit = document.createElement('button');
            btnEdit.type = 'button';
            btnEdit.className = `btn btn-sm btn-info text-white btn-edit-${entityName} me-1`;
            btnEdit.setAttribute('data-id', String(itemId));
            // Nota: O nome da entidade no modal deve ser em CamelCase
            const modalEntityName = entityName.charAt(0).toUpperCase() + entityName.slice(1);
            btnEdit.setAttribute('data-bs-toggle', 'modal');
            btnEdit.setAttribute('data-bs-target', `#modalEdicao${modalEntityName}`);
            btnEdit.title = 'Visualizar';
            const iconEye = document.createElement('i'); iconEye.className = 'fas fa-eye';
            btnEdit.appendChild(iconEye);
            divActions.appendChild(btnEdit);
        }

        const btnRemove = document.createElement('button');
        btnRemove.type = 'button';
        btnRemove.className = 'btn btn-sm btn-danger btn-remove-entity';
        btnRemove.setAttribute('data-id', String(itemId));
        btnRemove.setAttribute('data-entity', entityName);
        btnRemove.title = 'Remover';
        const iconTrash = document.createElement('i'); iconTrash.className = 'fas fa-trash-alt';
        btnRemove.appendChild(iconTrash);
        divActions.appendChild(btnRemove);

        tdAction.appendChild(divActions);

        tr.appendChild(tdName);
        tr.appendChild(tdAction);
        gridBody.appendChild(tr);

        return tr;
    };
    
    // --- 3. FUNÇÕES DE RENDERIZAÇÃO DE GRADES ---

    const normalizeTipo = (tipo) => {
        if (typeof tipo !== 'string') return 'Outros Tipos'; 
        if (tipo === 'Hardskill' || tipo === 'Hard Skills') return 'Hard Skills';
        if (tipo === 'Softskill' || tipo === 'Soft Skills') return 'Soft Skills';
        return 'Outros Tipos';
    };

    const renderHabilidadesGrid = () => {
        try {
            const gridBody = document.getElementById('habilidadesGridBody');
            if (!gridBody) return;
            
            const habilidadesAssociadas = getEntityMap('habilidade'); 

            if (habilidadesAssociadas.length === 0) {
                createEmptyRow(gridBody, 2, 'Nenhuma Habilidade associada.');
                return;
            }
            
            clearElementChildren(gridBody); // Limpa antes de renderizar

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
                    // separator row
                    const sep = document.createElement('tr');
                    sep.className = 'table-group-separator';
                    const td = document.createElement('td');
                    td.setAttribute('colspan', '2');
                    td.className = 'fw-bold';
                    const icon = document.createElement('i'); icon.className = 'fas fa-tag me-2';
                    td.appendChild(icon);
                    td.appendChild(document.createTextNode(' ' + tipo));
                    sep.appendChild(td);
                    gridBody.appendChild(sep);
                    hasContent = true;

                    grupoItens.forEach(item => {
                        const itemId = item.id;
                        const itemName = item.nome;
                        const row = document.createElement('tr');
                        row.setAttribute('data-id', String(itemId));
                        row.setAttribute('data-type', 'habilidade');

                        const tdNome = document.createElement('td');
                        tdNome.textContent = itemName || '';
                        const hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = 'habilidadeId[]'; hidden.value = String(itemId);
                        tdNome.appendChild(hidden);

                        const tdAction = document.createElement('td'); tdAction.className = 'text-center grid-action-cell';
                        const divAct = document.createElement('div'); divAct.className = 'd-flex justify-content-center';

                        const btnView = document.createElement('button');
                        btnView.type = 'button';
                        btnView.className = 'btn btn-sm btn-info text-white btn-edit-habilidade me-1';
                        btnView.setAttribute('data-id', String(itemId));
                        btnView.setAttribute('data-bs-toggle', 'modal');
                        btnView.setAttribute('data-bs-target', '#modalEdicaoHabilidade');
                        const iView = document.createElement('i'); iView.className = 'fas fa-eye'; btnView.appendChild(iView);

                        const btnDel = document.createElement('button');
                        btnDel.type = 'button'; btnDel.className = 'btn btn-sm btn-danger btn-remove-entity';
                        btnDel.setAttribute('data-id', String(itemId)); btnDel.setAttribute('data-entity', 'habilidade');
                        const iDel = document.createElement('i'); iDel.className = 'fas fa-trash-alt'; btnDel.appendChild(iDel);

                        divAct.appendChild(btnView); divAct.appendChild(btnDel);
                        tdAction.appendChild(divAct);

                        row.appendChild(tdNome); row.appendChild(tdAction);
                        gridBody.appendChild(row);
                    });
                }
            });

            if (!hasContent && habilidadesAssociadas.length > 0) { 
                // Fallback caso o loop falhe mas haja dados
                createEmptyRow(gridBody, 2, 'Erro interno de agrupamento de Habilidades.');
            } else if (!hasContent && habilidadesAssociadas.length === 0) {
                 createEmptyRow(gridBody, 2, 'Nenhuma Habilidade associada.');
            }

            attachEditListeners('habilidade');
        } catch (e) {
            console.error("ERRO CRÍTICO [Habilidades]:", e);
            const gb = document.getElementById('habilidadesGridBody');
            if (gb) {
                createEmptyRow(gb, 2, 'ERRO CRÍTICO DE RENDERIZAÇÃO JS.');
                gb.querySelector('td').classList.add('text-danger','fw-bold');
            }
        }
    };
    
    // FUNÇÃO GERAL PARA RENDERS SIMPLES
    const renderSimpleGrid = (entityName, hasEditButton = false, cols = 2) => {
        try {
            // RecursoGrupo não utiliza mais esta função, mas é mantido para outras entidades.
            if (entityName === 'recursoGrupo') {
                renderRecursosGruposGrid();
                return;
            }

            const gridBodyId = resolveGridBodyId(entityName);
            const gridBody = document.getElementById(gridBodyId);

            if (!gridBody) {
                console.warn(`renderSimpleGrid: tbody not found for entity '${entityName}', expected id '${gridBodyId}'.`);
                return;
            }

            clearElementChildren(gridBody);
            const dataArray = getEntityMap(entityName);

            if (dataArray.length === 0) {
              createEmptyRow(gridBody, cols, `Nenhuma ${entityName.charAt(0).toUpperCase() + entityName.slice(1)} associada.`);
                 return;
            }

            dataArray.forEach(item => {
                // CLÁUSULA DE GUARDA: Ignora itens com dados incompletos ou nulos.
                if (item.id === null || item.id === undefined || item.nome === null || item.nome === undefined) {
                     console.error(`Item inválido encontrado em ${entityName}, pulando.`, item);
                     return; 
                }
                
                addSimpleGridRow(gridBodyId, item.id, item.nome, `${entityName}Id`, hasEditButton, entityName);
            });
            
            // VERIFICAÇÃO ADICIONAL
            if (dataArray.length > 0 && gridBody.children.length === 0) {
                 console.error(`ERRO LÓGICO: Havia ${dataArray.length} itens para ${entityName}, mas nenhum foi renderizado.`);
                 createEmptyRow(gridBody, cols, `ERRO LÓGICO. Verifique console para ${entityName}.`);
            }
            
            if (hasEditButton) {
                attachEditListeners(entityName);
            }
        } catch (e) {
            console.error(`ERRO CRÍTICO [${entityName}]:`, e);
            const id = resolveGridBodyId(entityName);
            const el = document.getElementById(id);
            if (el) {
                createEmptyRow(el, cols, 'ERRO CRÍTICO DE RENDERIZAÇÃO JS.');
                el.querySelector('td').classList.add('text-danger','fw-bold');
            }
        }
    };
    
    // MAPEAMENTO DAS FUNÇÕES SIMPLES (Funções que usam o generic renderer)
    const renderCaracteristicasGrid = () => renderSimpleGrid('caracteristica', true);
    const renderAreasAtuacaoGrid = () => renderSimpleGrid('area', false);

    // FUNÇÃO AUTÔNOMA E OTIMIZADA PARA RECURSOS GRUPOS (Usa jQuery para construção HTML)
    const renderRecursosGruposGrid = () => {
        try {
            const entityName = 'recursoGrupo';
            const gridBody = document.getElementById('recursosGruposGridBody');
            const dataArray = getEntityMap(entityName);
            
            if (!gridBody) return; 

            clearElementChildren(gridBody); // Novo método de limpeza com jQuery

            if (dataArray.length === 0) {
              createEmptyRow(gridBody, 2, `Nenhum Grupo de Recurso associado.`);
                 return;
            }
            
            const rows = dataArray.map(item => {
                if (!item.id || !item.nome) return '';

                const itemId = String(item.id);
                const itemName = item.nome;
                
                // Constrói a linha usando template string e HTML
                return `
                    <tr data-id="${itemId}">
                        <td>
                            <input type="hidden" name="recursoGrupoId[]" value="${itemId}">
                            ${itemName}
                        </td>
                        <td class="text-center grid-action-cell">
                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-sm btn-info text-white btn-edit-recursoGrupo me-1" 
                                    data-id="${itemId}" data-bs-toggle="modal" data-bs-target="#modalEdicaoRecursoGrupo" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-remove-entity" 
                                    data-id="${itemId}" data-entity="${entityName}" title="Remover">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            // Usa jQuery para anexar todas as linhas de uma vez
            $(gridBody).append(rows);

            attachEditListeners(entityName); 
        } catch (e) {
            console.error(`ERRO CRÍTICO [${entityName}]:`, e);
            const el = document.getElementById('recursosGruposGridBody');
            if (el) {
                createEmptyRow(el, 2, 'ERRO CRÍTICO DE RENDERIZAÇÃO JS.');
                el.querySelector('td').classList.add('text-danger','fw-bold');
            }
        }
    };
    
    const renderRiscosGrid = () => {
        try {
            const gridBody = document.getElementById('riscosGridBody');
            clearElementChildren(gridBody);
            
            const riscosAssociados = getEntityMap('risco');

            if (riscosAssociados.length === 0) {
                createEmptyRow(gridBody, 3, 'Nenhum Risco de Exposição associado.');
                return;
            }

            riscosAssociados.forEach(item => {
                const tr = document.createElement('tr');
                tr.setAttribute('data-id', String(item.id));

                const itemDescricao = item.descricao || '';
                const trimmedDesc = itemDescricao.length > 50 ? itemDescricao.substring(0, 50) + '...' : itemDescricao;

                const tdNome = document.createElement('td');
                tdNome.textContent = item.nome || '';
                const hidId = document.createElement('input'); hidId.type = 'hidden'; hidId.name = 'riscoId[]'; hidId.value = String(item.id);
                tdNome.appendChild(hidId);

                const tdDesc = document.createElement('td');
                const span = document.createElement('span'); span.title = itemDescricao; span.textContent = trimmedDesc;
                tdDesc.appendChild(span);
                const hidDesc = document.createElement('input'); hidDesc.type = 'hidden'; hidDesc.name = 'riscoDescricao[]'; hidDesc.value = itemDescricao;
                tdDesc.appendChild(hidDesc);

                const tdAction = document.createElement('td'); tdAction.className = 'text-center grid-action-cell';
                const divAct = document.createElement('div'); divAct.className = 'd-flex justify-content-center';

                const btnEdit = document.createElement('button'); btnEdit.type = 'button'; btnEdit.className = 'btn btn-sm btn-info text-white btn-edit-risco me-1';
                btnEdit.setAttribute('data-id', String(item.id)); btnEdit.setAttribute('data-bs-toggle', 'modal'); btnEdit.setAttribute('data-bs-target', '#modalEdicaoRisco'); btnEdit.title = 'Editar';
                const iPen = document.createElement('i'); iPen.className = 'fas fa-pen'; btnEdit.appendChild(iPen);

                const btnDel = document.createElement('button'); btnDel.type = 'button'; btnDel.className = 'btn btn-sm btn-danger btn-remove-entity';
                btnDel.setAttribute('data-id', String(item.id)); btnDel.setAttribute('data-entity', 'risco'); btnDel.title = 'Remover';
                const iTrash = document.createElement('i'); iTrash.className = 'fas fa-trash-alt'; btnDel.appendChild(iTrash);

                divAct.appendChild(btnEdit); divAct.appendChild(btnDel);
                tdAction.appendChild(divAct);

                tr.appendChild(tdNome); tr.appendChild(tdDesc); tr.appendChild(tdAction);
                gridBody.appendChild(tr);
            });
            attachEditListeners('risco');
        } catch (e) {
            console.error("ERRO CRÍTICO [Risco]:", e);
            const gb = document.getElementById('riscosGridBody');
            if (gb) { createEmptyRow(gb, 3, 'ERRO CRÍTICO DE RENDERIZAÇÃO JS.'); gb.querySelector('td').classList.add('text-danger','fw-bold'); }
        }
    };

    const renderCursosGrid = () => {
        try {
            const gridBody = document.getElementById('cursosGridBody');
            clearElementChildren(gridBody);
            
            const cursosAssociados = getEntityMap('curso');

            if (cursosAssociados.length === 0) {
                createEmptyRow(gridBody, 3, 'Nenhum Curso associado.');
                return;
            }

            cursosAssociados.forEach(item => {
                const isObrigatorio = item.obrigatorio === true || item.obrigatorio === 1;
                const badgeClass = isObrigatorio ? 'bg-danger' : 'bg-secondary';
                
                const tr = document.createElement('tr');
                tr.setAttribute('data-id', String(item.id));

                const itemObs = item.obs || '';
                const trimmedObs = itemObs.length > 30 ? itemObs.substring(0, 30) + '...' : itemObs;

                const tdNome = document.createElement('td');
                tdNome.textContent = item.nome || '';
                const hid = document.createElement('input'); hid.type = 'hidden'; hid.name = 'cursoId[]'; hid.value = String(item.id);
                tdNome.appendChild(hid);

                const tdInfo = document.createElement('td');
                const spanBadge = document.createElement('span'); spanBadge.className = `badge ${badgeClass}`; spanBadge.textContent = isObrigatorio ? 'OBRIGATÓRIO' : 'DESEJÁVEL';
                const small = document.createElement('small'); small.className = 'd-block text-muted'; small.title = itemObs; small.textContent = trimmedObs;
                const hidReq = document.createElement('input'); hidReq.type = 'hidden'; hidReq.name = 'cursoCargoObrigatorio[]'; hidReq.value = isObrigatorio ? '1' : '0';
                const hidObs = document.createElement('input'); hidObs.type = 'hidden'; hidObs.name = 'cursoCargoObs[]'; hidObs.value = item.obs || '';
                tdInfo.appendChild(spanBadge); tdInfo.appendChild(small); tdInfo.appendChild(hidReq); tdInfo.appendChild(hidObs);

                const tdAction = document.createElement('td'); tdAction.className = 'text-center grid-action-cell';
                const divAct = document.createElement('div'); divAct.className = 'd-flex justify-content-center';
                const btnEdit = document.createElement('button'); btnEdit.type = 'button'; btnEdit.className = 'btn btn-sm btn-info text-white btn-edit-curso me-1';
                btnEdit.setAttribute('data-id', String(item.id)); btnEdit.setAttribute('data-bs-toggle', 'modal'); btnEdit.setAttribute('data-bs-target', '#modalEdicaoCurso'); btnEdit.title = 'Editar';
                const iPen = document.createElement('i'); iPen.className = 'fas fa-pen'; btnEdit.appendChild(iPen);
                const btnDel = document.createElement('button'); btnDel.type = 'button'; btnDel.className = 'btn btn-sm btn-danger btn-remove-entity';
                btnDel.setAttribute('data-id', String(item.id)); btnDel.setAttribute('data-entity', 'curso'); btnDel.title = 'Remover';
                const iTrash = document.createElement('i'); iTrash.className = 'fas fa-trash-alt'; btnDel.appendChild(iTrash);
                divAct.appendChild(btnEdit); divAct.appendChild(btnDel); tdAction.appendChild(divAct);

                tr.appendChild(tdNome); tr.appendChild(tdInfo); tr.appendChild(tdAction);
                gridBody.appendChild(tr);
            });
            attachEditListeners('curso');
        } catch (e) {
            console.error("ERRO CRÍTICO [Cursos]:", e);
            const gb = document.getElementById('cursosGridBody');
            if (gb) { createEmptyRow(gb, 3, 'ERRO CRÍTICO DE RENDERIZAÇÃO JS.'); gb.querySelector('td').classList.add('text-danger','fw-bold'); }
        }
    };
    
    // SINÔNIMOS
    const renderSinonimosGrid = () => {
        try {
            const gridBody = document.getElementById('sinonimosGridBody');
            clearElementChildren(gridBody);
            
            const sinonimosAssociados = getEntityMap('sinonimo');

            if (sinonimosAssociados.length === 0) {
                createEmptyRow(gridBody, 2, 'Nenhum Sinônimo associado.');
                return;
            }

            sinonimosAssociados.forEach(item => {
                const itemId = item.id ? String(item.id) : 'new-' + String(item.nome).replace(/\s/g, '-'); 
                const tr = document.createElement('tr'); tr.setAttribute('data-id', itemId);
                const tdNome = document.createElement('td'); tdNome.textContent = item.nome || '';
                const hid = document.createElement('input'); hid.type = 'hidden'; hid.name = 'sinonimoNome[]'; hid.value = item.nome || '';
                tdNome.appendChild(hid);
                const tdAction = document.createElement('td'); tdAction.className = 'text-center grid-action-cell';
                const divAct = document.createElement('div'); divAct.className = 'd-flex justify-content-center';
                const btnDel = document.createElement('button'); btnDel.type = 'button'; btnDel.className = 'btn btn-sm btn-danger btn-remove-entity';
                btnDel.setAttribute('data-id', itemId); btnDel.setAttribute('data-entity', 'sinonimo'); btnDel.title = 'Remover';
                const iTrash = document.createElement('i'); iTrash.className = 'fas fa-trash-alt'; btnDel.appendChild(iTrash);
                divAct.appendChild(btnDel); tdAction.appendChild(divAct);
                tr.appendChild(tdNome); tr.appendChild(tdAction); gridBody.appendChild(tr);
            });
        } catch (e) {
            console.error("ERRO CRÍTICO [Sinônimos]:", e);
            const gb = document.getElementById('sinonimosGridBody');
            if (gb) { createEmptyRow(gb, 2, 'ERRO CRÍTICO DE RENDERIZAÇÃO JS.'); gb.querySelector('td').classList.add('text-danger','fw-bold'); }
        }
    };

    const renderMaps = {
        habilidade: renderHabilidadesGrid, caracteristica: renderCaracteristicasGrid, 
        risco: renderRiscosGrid, curso: renderCursosGrid, 
        recursoGrupo: renderRecursosGruposGrid, area: renderAreasAtuacaoGrid,
        sinonimo: renderSinonimosGrid
    };


    // --- 4. FUNÇÕES DE EDIÇÃO EM MODAL (MANTIDAS) ---
    
    const attachEditListeners = (entityName) => {
        const gridBodyId = resolveGridBodyId(entityName);
        const gridBodySelector = `#${gridBodyId}`;
        const selector = `.btn-edit-${entityName}`;

        if (!document.getElementById(gridBodyId)) {
            // nada a fazer se o tbody não existir
            return;
        }

        $(gridBodySelector).off('click', selector);

        $(gridBodySelector).on('click', selector, function(e) {
            e.preventDefault();
            const rawId = $(this).data('id');
            const itemId = Number(rawId);

            const stateArray = getEntityMap(entityName);
            if (!Array.isArray(stateArray) || stateArray.length === 0) return;

            const item = stateArray.find(i => Number(i.id) === itemId || String(i.id) === String(rawId));
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

    const setupEditCursoModal = (item) => {
        $('#cursoEditNome').text(item.nome);
        $('#cursoEditId').val(item.id);
        $('#cursoEditObrigatorio').prop('checked', item.obrigatorio === 1 || item.obrigatorio === true);
        $('#cursoEditObs').val(item.obs || '');
        
        const modalEl = document.getElementById('modalEdicaoCurso');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    (function(){
        const btn = document.getElementById('btnSalvarEdicaoCurso');
        if (!btn) return;
        btn.onclick = function() {
            const id = Number($('#cursoEditId').val());
            const isObrigatorio = $('#cursoEditObrigatorio').prop('checked');
            const obs = $('#cursoEditObs').val().trim();

            const item = getEntityMap('curso').find(i => Number(i.id) === id || String(i.id) === String($('#cursoEditId').val())); 
            if (item) {
                item.obrigatorio = isObrigatorio ? 1 : 0;
                item.obs = obs;
                renderCursosGrid();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoCurso')).hide();
            }
        };
    })();
    
    const setupEditRiscoModal = (item) => {
        $('#riscoEditNome').text(item.nome);
        $('#riscoEditId').val(item.id);
        $('#riscoEditDescricao').val(item.descricao || '');
        
        const modalEl = document.getElementById('modalEdicaoRisco');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };
    
    (function(){
        const btn = document.getElementById('btnSalvarEdicaoRisco');
        if (!btn) return;
        btn.onclick = function() {
            const id = Number($('#riscoEditId').val());
            const descricao = $('#riscoEditDescricao').val().trim();

            if (descricao) {
                const item = getEntityMap('risco').find(i => Number(i.id) === id || String(i.id) === String($('#riscoEditId').val())); 
                if (item) {
                    item.descricao = descricao;
                    renderRiscosGrid();
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdicaoRisco')).hide();
                }
            } else {
                alert('A descrição do risco é obrigatória.');
            }
        };
    })();
    
    const setupEditHabilidadeModal = (item) => {
        $('#habilidadeEditNome').text(item.nome);
        $('#habilidadeEditId').val(item.id);
        $('#habilidadeEditNomeInput').val(item.nome);
        $('#habilidadeEditTipo').val(normalizeTipo(item.tipo));

        const modalEl = document.getElementById('modalEdicaoHabilidade');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    const setupEditCaracteristicaModal = (item) => {
        $('#caracteristicaEditNome').text(item.nome);
        $('#caracteristicaEditId').val(item.id);
        $('#caracteristicaEditNomeInput').val(item.nome);

        const modalEl = document.getElementById('modalEdicaoCaracteristica');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };
    
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
        if (!selectedValues || selectedValues.length === 0) return [];
        
        const data = [];
        const selectElement = document.getElementById(selectId);
        const values = Array.isArray(selectedValues) ? selectedValues : [selectedValues];
        
        values.forEach(value => {
            // CORREÇÃO: Ignora o placeholder vazio e evita IDs quebrados (NaN)
            if (!value || isNaN(parseInt(value))) return; 

            const option = selectElement.querySelector(`option[value="${value}"]`);
            if (option) {
                data.push({
                    id: parseInt(value),
                    nome: option.getAttribute('data-nome') || option.text, // Fallback de segurança
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
                stateArray.push(newItem);
                addedCount++;
            }
        });

        if (addedCount > 0) {
            renderFunction();
        }
    };
    
    (function(){
        const btn = document.getElementById('btnAssociarHabilidade');
        if (!btn) return;
        btn.onclick = function() {
            handleMultiSelectAssociation('habilidadeSelect', 'habilidade', renderHabilidadesGrid);
            $('#habilidadeSelect').val(null).trigger('change');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoHabilidades')).hide();
        };
    })();
    
    (function(){
        const btn = document.getElementById('btnAssociarCaracteristica');
        if (!btn) return;
        btn.onclick = function() {
            handleMultiSelectAssociation('caracteristicaSelect', 'caracteristica', renderCaracteristicasGrid);
            $('#caracteristicaSelect').val(null).trigger('change');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoCaracteristicas')).hide();
        };
    })();

    (function(){
        const btn = document.getElementById('btnAssociarRecursosGrupos');
        if (!btn) return;
        btn.onclick = function() {
            handleMultiSelectAssociation('recursosGruposSelect', 'recursoGrupo', renderRecursosGruposGrid);
            $('#recursosGruposSelect').val(null).trigger('change');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoRecursosGrupos')).hide();
        };
    })();

    (function(){
        const btn = document.getElementById('btnAssociarAreasAtuacao');
        if (!btn) return;
        btn.onclick = function() {
            handleMultiSelectAssociation('areasAtuacaoSelect', 'area', renderAreasAtuacaoGrid);
            $('#areasAtuacaoSelect').val(null).trigger('change');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoAreasAtuacao')).hide();
        };
    })();

    (function(){
        const btn = document.getElementById('btnAssociarRisco');
        if (!btn) return;
        btn.onclick = function() {
            const data = getSelectedOptionsData('riscoSelect')[0];
            const descricao = document.getElementById('riscoDescricaoInput').value.trim();
            const riscosAssociados = getEntityMap('risco');

            if (data && descricao) {
                const isDuplicate = riscosAssociados.some(item => Number(item.id) === Number(data.id));
                if (!isDuplicate) {
                    riscosAssociados.push({ id: Number(data.id), nome: data.nome, descricao: descricao }); 
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
    })();
    
    (function(){
        const btn = document.getElementById('btnAssociarCurso');
        if (!btn) return;
        btn.onclick = function() {
            const selectedItems = getSelectedOptionsData('cursoSelect');
            if (selectedItems.length === 0) {
                alert('Por favor, selecione um Curso válido na lista.');
                return;
            }
            const isObrigatorio = document.getElementById('cursoObrigatorioInput').checked;
            const obs = document.getElementById('cursoObsInput').value.trim();
            const cursosAssociados = getEntityMap('curso');
            let addedCount = 0;

            selectedItems.forEach(data => {
                const isDuplicate = cursosAssociados.some(item => Number(item.id) === Number(data.id));
                
                if (!isDuplicate) {
                    cursosAssociados.push({
                        id: Number(data.id),
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
            
            // --- CORREÇÃO ADICIONADA AQUI ---
            // Remove o foco do botão ANTES de fechar o modal
            $(this).trigger('blur'); 
            // ---------------------------------
            
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAssociacaoCursos')).hide();
        };
    })();
    
    (function(){
        const btn = document.getElementById('btnAddSinonimo');
        if (!btn) return;
        btn.onclick = function() {
            const input = document.getElementById('sinonimoInput');
            const nome = input.value.trim();
            const sinonimosAssociados = getEntityMap('sinonimo');

            if (nome) {
                const isDuplicate = sinonimosAssociados.some(item => String(item.nome).toLowerCase() === nome.toLowerCase());

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
    })();


    // --- 6. INICIALIZAÇÃO GERAL ---

    function initSelect2() {
        // Inicialização dos Select2
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
    
    // Tenta inicializar Select2 (potencial ponto de falha no DOM)
    try {
        initSelect2();
    } catch (e) {
        console.error("ERRO CRÍTICO: Falha na inicialização do Select2. Verifique se o Select2, Bootstrap e jQuery estão carregados.", e);
    }

    var firstTab = document.querySelector('#basicas-tab');
    if (firstTab) {
        new bootstrap.Tab(firstTab).show();
    }
    
    // Chamadas de renderização inicial
    renderHabilidadesGrid();
    renderCaracteristicasGrid();
    renderRiscosGrid();
    renderCursosGrid();
    renderRecursosGruposGrid(); 
    renderAreasAtuacaoGrid();
    renderSinonimosGrid(); 
    
    console.log("--- DEBUG END DOM READY ---: Todas as funções de renderização foram chamadas.");

    
    // --- 7. EVENT DELEGATION PARA REMOÇÃO (MANTIDO) ---
    $(document).on('click', '#cargoForm .btn-remove-entity', function() {
        
        const entityName = $(this).data('entity');
        const itemId = $(this).data('id'); 

        if (!entityName || itemId === undefined) {
            console.error('Botão de remoção sem data-entity or data-id');
            return;
        }

        const stateArray = getEntityMap(entityName);
        if (stateArray.length === 0) {
            console.error('ERRO: Mapa de estado não encontrado ou vazio para:', entityName);
            return;
        }

        let novoArray;
        
        const isNewSinonimo = (entityName === 'sinonimo' && isNaN(itemId));

        if (isNewSinonimo) {
            novoArray = stateArray.filter(item => {
                const tempId = item.id ? item.id.toString() : 'new-' + item.nome.replace(/\s/g, '-');
                return tempId !== itemId.toString();
            });
        } else {
            const numericId = parseInt(itemId);
            if (isNaN(numericId)) {
                 console.error('ERRO: ID inválido para remoção:', itemId);
                 return;
            }
            novoArray = stateArray.filter(item => {
                return parseInt(item.id) !== numericId;
            });
        }
        
        stateArray.length = 0; 
        Array.prototype.push.apply(stateArray, novoArray);

        if (renderMaps[entityName]) {
            renderMaps[entityName]();
        } else {
            console.error('ERRO: Função de renderização não encontrada para:', entityName);
        }
    });

    console.log("cargos_form.js (VERSÃO FINAL) carregado e pronto.");

    // --- LÓGICA DE BLOQUEIO E REVISÃO DE CARGOS ---
    
    const checkStatusRevisao = () => {
        const isRevisado = $('#is_revisado').is(':checked');
        const cargoId = $('input[name="cargoId"]').val();

        // Só bloqueia se for um cargo que já existe (ID > 0) e está marcado como revisado
        if (isRevisado && parseInt(cargoId) > 0) {
            bloquearFormulario();
        }
    };

    const bloquearFormulario = () => {
        // Desabilita todos os inputs, selects, textareas e botões dentro do form, EXCETO o botão de desbloquear e o checkbox
        $('#cargoForm').find('input, select, textarea, button')
            .not('#btnDesbloquearEdicao')
            .not('#is_revisado')
            .prop('disabled', true);
        
        // Desabilita as interações do Select2
        $('.searchable-select').prop('disabled', true);
        
        // Esconde botões de adicionar nas grids
        $('[data-bs-target^="#modalAssociacao"]').hide();
        $('#btnAddSinonimo').hide();
        
        // Bloqueia as ações das grids (excluir/editar)
        $('.btn-remove-entity, .btn-edit-habilidade, .btn-edit-caracteristica, .btn-edit-recursoGrupo, .btn-edit-curso, .btn-edit-risco').prop('disabled', true);
    };

    const desbloquearFormulario = () => {
        // Reabilita tudo
        $('#cargoForm').find('input, select, textarea, button').prop('disabled', false);
        $('.searchable-select').prop('disabled', false);
        $('[data-bs-target^="#modalAssociacao"]').show();
        $('#btnAddSinonimo').show();
        $('#is_revisado').prop('checked', false); // Tira o check para exigir que o admin marque de novo se quiser salvar
        $('#btnDesbloquearEdicao').hide();
        
        // Mostra um aviso
        alert('Edição desbloqueada. Lembre-se de salvar as alterações no final da página.');
    };

    // Ação do botão de Desbloquear (Exigindo Senha)
    $('#btnDesbloquearEdicao').on('click', function() {
        // Substitua 'senha123' pela senha master desejada ou integre com uma chamada AJAX ao AuthController
        const senhaMaster = 'admin123'; 
        const senhaDigitada = prompt('Este cargo já foi revisado e bloqueado.\n\nDigite a senha do Administrador Geral para liberar a edição:');
        
        if (senhaDigitada === null) {
            return; // Cancelou o prompt
        }
        
        if (senhaDigitada === senhaMaster) {
            desbloquearFormulario();
        } else {
            alert('Senha incorreta! Acesso negado.');
        }
    });

    // Chama a verificação logo que a página carrega
    checkStatusRevisao();
});