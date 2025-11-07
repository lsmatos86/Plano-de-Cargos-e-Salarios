// Arquivo: scripts/cargos_form.js
// Versão Definitiva: Inclui try/catch em todas as funções de renderização para forçar a depuração de erros.

$(document).ready(function() {
    
    console.log("--- DEBUG START DOM READY ---: Inicializando rotina de renderização das grids.");

    // Mapeamento explícito das entidades (opção A)
    // Defina aqui o nome da variável global injetada pelo PHP e o ID do <tbody> correspondente
    const ENTITY_CONFIG = {
        habilidade:   { global: 'habilidadesAssociadas',    tbody: 'habilidadesGridBody' },
        caracteristica: { global: 'caracteristicasAssociadas', tbody: 'caracteristicasGridBody' },
        risco:        { global: 'riscosAssociados',          tbody: 'riscosGridBody' },
        curso:        { global: 'cursosAssociados',          tbody: 'cursosGridBody' },
        recursoGrupo: { global: 'recursosGruposAssociadas',  tbody: 'recursosGruposGridBody' },
        area:         { global: 'areasAssociadas',           tbody: 'areasAtuacaoGridBody' },
        sinonimo:     { global: 'sinonimosAssociados',       tbody: 'sinonimosGridBody' }
    };

    // DEBUG: lista de entidades que esperamos encontrar (vai logar o que foi detectado)
    const logDetectedGlobals = () => {
        const found = {};
        Object.keys(ENTITY_CONFIG).forEach(e => {
            const cfg = ENTITY_CONFIG[e];
            const val = window[cfg.global];
            let len = 0;
            let type = typeof val;
            let sample = null;
            if (Array.isArray(val)) {
                len = val.length;
                type = 'array';
                sample = val.length > 0 ? { id: val[0].id, nome: val[0].nome } : null;
            } else if (val && typeof val === 'object') {
                // possible associative array encoded as object
                const keys = Object.keys(val);
                if (keys.length > 0 && keys.every(k => /^\d+$/.test(k))) {
                    len = keys.length;
                    type = 'assoc_object';
                    sample = val[keys[0]];
                } else {
                    len = keys.length;
                    type = 'object';
                }
            }
            found[e] = { length: len, type, sample };
        });

        // Além disso, reporte quaisquer variáveis globais encontradas que não estiverem no mapa
        const extras = {};
        Object.keys(window).forEach(k => {
            if (k.endsWith('Associadas') || k.endsWith('Associados')) {
                if (!Object.values(ENTITY_CONFIG).some(cfg => cfg.global === k)) {
                    const val = window[k];
                    extras[k] = Array.isArray(val) ? { length: val.length, sample: val[0] } : typeof val;
                }
            }
        });
        console.log('DEBUG: Detected globals counts:', found, 'extras:', extras);
    };

    // chama o logger de detecção (ajuda no debug quando nomes não batem)
    // NOTE: a invocação real será feita mais abaixo, depois das definições auxiliares (getEntityMap/resoveGridBodyId)

    // --- 1. FUNÇÕES GENÉRICAS E MAPAS DE ESTADO ---
    
    // Função auxiliar para buscar o array de estado global de forma segura
    // Usa o mapa explícito ENTITY_CONFIG como primeiro caminho, com fallback heurístico
    const getEntityMap = (entityName) => {
        const cfg = ENTITY_CONFIG[entityName];
        if (cfg) {
            const direct = window[cfg.global];
            if (Array.isArray(direct)) return direct;
            if (direct && typeof direct === 'object') {
                // convert associative object with numeric keys to array
                const keys = Object.keys(direct);
                if (keys.length > 0 && keys.every(k => /^\d+$/.test(k))) {
                    return keys.map(k => direct[k]);
                }
            }
        }

        // fallback heurístico: procura por algumas convenções conhecidas (plural/masculine/feminine)
        const candidates = [
            `${entityName}sAssociadas`,
            `${entityName}Associadas`,
            `${entityName}sAssociados`,
            `${entityName}Associados`,
            `${entityName}s`,
            `${entityName}`,
            'recursosGruposAssociados',
            'recursosGruposAssociadas',
            'recursosAssociadas',
            'recursosAssociados'
        ];
        for (const name of candidates) {
            const val = window[name];
            if (Array.isArray(val)) return val;
            if (val && typeof val === 'object') {
                const keys = Object.keys(val);
                if (keys.length > 0 && keys.every(k => /^\d+$/.test(k))) {
                    return keys.map(k => val[k]);
                }
            }
        }

        // heurística avançada: procurar por chaves que contenham todas as palavras do entityName
        const parts = entityName.replace(/([A-Z])/g, ' $1').split(/[^a-zA-Z0-9]+/).filter(Boolean).map(p => p.toLowerCase());
        for (const k of Object.keys(window)) {
            const kl = k.toLowerCase();
            if (parts.every(p => kl.includes(p))) {
                const val = window[k];
                if (Array.isArray(val)) return val;
                if (val && typeof val === 'object') {
                    const keys = Object.keys(val);
                    if (keys.length > 0 && keys.every(key => /^\d+$/.test(key))) return keys.map(key => val[key]);
                }
            }
        }

        return [];
    };

    // chama o logger de detecção (ajuda no debug quando nomes não batem)
    try { logDetectedGlobals(); } catch (err) { console.warn('DEBUG: falha ao rodar logDetectedGlobals', err); }

    // Resolve o ID do <tbody> para um dado entityName usando ENTITY_CONFIG como fonte de verdade
    const resolveGridBodyId = (entityName) => {
        const cfg = ENTITY_CONFIG[entityName];
        if (cfg && cfg.tbody && document.getElementById(cfg.tbody)) return cfg.tbody;

        const candidates = [
            `${entityName}sGridBody`,
            `${entityName}GridBody`,
            'areasAtuacaoGridBody',
            'recursosGruposGridBody'
        ];

        for (const id of candidates) {
            if (document.getElementById(id)) return id;
        }

        // fallback para convenção padrão
        return `${entityName}sGridBody`;
    };

    // Helper: limpa todos os filhos de um elemento
    const clearElementChildren = (el) => {
        while (el && el.firstChild) {
            el.removeChild(el.firstChild);
        }
    };

    // Helper: cria uma linha de 'nenhum registro' de forma segura
    const createEmptyRow = (gridBody, colspan, text) => {
        clearElementChildren(gridBody);
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.setAttribute('colspan', String(colspan));
        td.className = 'text-muted';
        td.textContent = text;
        tr.appendChild(td);
        gridBody.appendChild(tr);
    };
    
    /**
     * Adiciona um item SIMPLES (ID/Nome) e o input oculto à grade.
     */
    const addSimpleGridRow = (gridBodyId, itemId, itemName, inputName, hasEditButton = false, entityName) => {
        console.log(`DEBUG: Adding row to ${gridBodyId}:`, { itemId, itemName, inputName });
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
            btnEdit.setAttribute('data-bs-toggle', 'modal');
            btnEdit.setAttribute('data-bs-target', `#modalEdicao${entityName.charAt(0).toUpperCase() + entityName.slice(1)}`);
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
            console.log('DEBUG: renderHabilidadesGrid starting...');
            const gridBody = document.getElementById('habilidadesGridBody');
            if (!gridBody) {
                console.warn('Grid body habilidadesGridBody not found');
                return;
            }
            const habilidadesAssociadas = getEntityMap('habilidade');
            console.log('DEBUG: habilidadesAssociadas mapped to:', habilidadesAssociadas);
            console.log('DEBUG: Sample habilidade item:', habilidadesAssociadas[0]);
            console.log('DEBUG: Expected properties:', {
                id: typeof habilidadesAssociadas[0]?.id,
                nome: typeof habilidadesAssociadas[0]?.nome,
                tipo: typeof habilidadesAssociadas[0]?.tipo
            });

            if (habilidadesAssociadas.length === 0) {
                createEmptyRow(gridBody, 2, 'Nenhuma Habilidade associada.');
                return;
            }
            
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
                        btnView.title = 'Visualizar';
                        const iView = document.createElement('i'); iView.className = 'fas fa-eye'; btnView.appendChild(iView);

                        const btnDel = document.createElement('button');
                        btnDel.type = 'button'; btnDel.className = 'btn btn-sm btn-danger btn-remove-entity';
                        btnDel.setAttribute('data-id', String(itemId)); btnDel.setAttribute('data-entity', 'habilidade'); btnDel.title = 'Remover';
                        const iDel = document.createElement('i'); iDel.className = 'fas fa-trash-alt'; btnDel.appendChild(iDel);

                        divAct.appendChild(btnView); divAct.appendChild(btnDel);
                        tdAction.appendChild(divAct);

                        row.appendChild(tdNome); row.appendChild(tdAction);
                        gridBody.appendChild(row);
                    });
                }
            });

            if (!hasContent) {
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
                addSimpleGridRow(gridBodyId, item.id, item.nome, `${entityName}Id`, hasEditButton, entityName);
            });
            
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
    
    // MAPEAMENTO DAS FUNÇÕES SIMPLES
    const renderCaracteristicasGrid = () => renderSimpleGrid('caracteristica', true);
    const renderRecursosGruposGrid = () => renderSimpleGrid('recursoGrupo', true);
    const renderAreasAtuacaoGrid = () => renderSimpleGrid('area', false);

    
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
});