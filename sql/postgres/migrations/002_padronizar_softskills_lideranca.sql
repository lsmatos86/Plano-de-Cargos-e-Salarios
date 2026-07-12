-- =============================================================
-- Migração: Padronizar Softskills de Liderança
-- Cargos alvo: Supervisor, Coordenador, Gerente
-- =============================================================
-- As softskills são resolvidas por nome (não por ID fixo),
-- garantindo compatibilidade independente do ambiente.
--
-- Softskills base (todos os níveis de liderança):
--   'Gestão de Pessoas e Desenvolvimento de Equipe'
--   'Gestão de Conflitos'
--   'Liderança de Equipes'
--
-- Softskills adicionais (Coordenador e Gerente):
--   'Comunicação Eficaz e Assertiva'
--   'Capacidade de Decisão e Resolução de Conflitos'
--
-- Cargos Supervisores: identificados por nivelDescricao = 'Supervisor'
--   OU pelo nome do cargo contendo 'Supervisor'.
-- Cargos Coordenadores: identificados por nivelDescricao = 'Coordenador'
--   OU pelo nome do cargo contendo 'Coordenador'.
-- Cargos Gerentes: identificados por nivelDescricao = 'Gerente'
--   OU pelo nome do cargo contendo 'Gerente'.
--
-- A migração é segura para re-execução (idempotente via NOT EXISTS).
-- Nenhuma softskill existente é removida.
-- =============================================================

DO $$
DECLARE
    v_gestao_pessoas   INTEGER;
    v_gestao_conflitos INTEGER;
    v_lideranca        INTEGER;
    v_comunicacao      INTEGER;
    v_decisao          INTEGER;
BEGIN

    -- -------------------------------------------------------
    -- 1. Resolve IDs das softskills por nome e tipo
    -- -------------------------------------------------------
    SELECT "habilidadeId" INTO v_gestao_pessoas
      FROM habilidades
     WHERE "habilidadeNome" = 'Gestão de Pessoas e Desenvolvimento de Equipe'
       AND "habilidadeTipo" = 'Softskill'
     LIMIT 1;

    SELECT "habilidadeId" INTO v_gestao_conflitos
      FROM habilidades
     WHERE "habilidadeNome" = 'Gestão de Conflitos'
       AND "habilidadeTipo" = 'Softskill'
     LIMIT 1;

    SELECT "habilidadeId" INTO v_lideranca
      FROM habilidades
     WHERE "habilidadeNome" = 'Liderança de Equipes'
       AND "habilidadeTipo" = 'Softskill'
     LIMIT 1;

    SELECT "habilidadeId" INTO v_comunicacao
      FROM habilidades
     WHERE "habilidadeNome" = 'Comunicação Eficaz e Assertiva'
       AND "habilidadeTipo" = 'Softskill'
     LIMIT 1;

    SELECT "habilidadeId" INTO v_decisao
      FROM habilidades
     WHERE "habilidadeNome" = 'Capacidade de Decisão e Resolução de Conflitos'
       AND "habilidadeTipo" = 'Softskill'
     LIMIT 1;

    -- -------------------------------------------------------
    -- 2. Verifica pré-requisitos (falha com mensagem clara)
    -- -------------------------------------------------------
    IF v_gestao_pessoas IS NULL THEN
        RAISE EXCEPTION 'Softskill "Gestão de Pessoas e Desenvolvimento de Equipe" não encontrada na tabela habilidades.';
    END IF;
    IF v_gestao_conflitos IS NULL THEN
        RAISE EXCEPTION 'Softskill "Gestão de Conflitos" não encontrada na tabela habilidades.';
    END IF;
    IF v_lideranca IS NULL THEN
        RAISE EXCEPTION 'Softskill "Liderança de Equipes" não encontrada na tabela habilidades.';
    END IF;
    IF v_comunicacao IS NULL THEN
        RAISE EXCEPTION 'Softskill "Comunicação Eficaz e Assertiva" não encontrada na tabela habilidades.';
    END IF;
    IF v_decisao IS NULL THEN
        RAISE EXCEPTION 'Softskill "Capacidade de Decisão e Resolução de Conflitos" não encontrada na tabela habilidades.';
    END IF;

    -- -------------------------------------------------------
    -- 3. Identifica cargos por nível hierárquico OU nome
    -- -------------------------------------------------------

    -- FASE 1: Softskills base para TODOS os cargos de liderança
    -- (Supervisores + Coordenadores + Gerentes)
    INSERT INTO habilidades_cargo ("cargoId", "habilidadeId")
    SELECT c."cargoId", s."habilidadeId"
    FROM cargos c
    CROSS JOIN (
        SELECT v_gestao_pessoas   AS "habilidadeId"
        UNION ALL
        SELECT v_gestao_conflitos
        UNION ALL
        SELECT v_lideranca
    ) s
    WHERE (
        -- por nível hierárquico cadastrado
        EXISTS (
            SELECT 1 FROM nivel_hierarquico n
            JOIN tipo_hierarquia t ON t."tipoId" = n."tipoId"
            WHERE n."nivelId" = c."nivelHierarquicoId"
              AND (
                  n."nivelDescricao" ILIKE '%supervisor%'
               OR n."nivelDescricao" ILIKE '%coordenador%'
               OR n."nivelDescricao" ILIKE '%gerente%'
               OR t."tipoNome"       ILIKE '%supervisor%'
               OR t."tipoNome"       ILIKE '%coordenador%'
               OR t."tipoNome"       ILIKE '%gerente%'
              )
        )
        -- ou pelo nome do próprio cargo (cobre cargos sem nível definido)
        OR c."cargoNome" ILIKE '%supervisor%'
        OR c."cargoNome" ILIKE '%coordenador%'
        OR c."cargoNome" ILIKE '%gerente%'
    )
    AND NOT EXISTS (
        SELECT 1 FROM habilidades_cargo hc
        WHERE hc."cargoId"      = c."cargoId"
          AND hc."habilidadeId" = s."habilidadeId"
    );

    -- FASE 2: Softskills adicionais apenas para COORDENADORES e GERENTES
    INSERT INTO habilidades_cargo ("cargoId", "habilidadeId")
    SELECT c."cargoId", s."habilidadeId"
    FROM cargos c
    CROSS JOIN (
        SELECT v_comunicacao AS "habilidadeId"
        UNION ALL
        SELECT v_decisao
    ) s
    WHERE (
        EXISTS (
            SELECT 1 FROM nivel_hierarquico n
            JOIN tipo_hierarquia t ON t."tipoId" = n."tipoId"
            WHERE n."nivelId" = c."nivelHierarquicoId"
              AND (
                  n."nivelDescricao" ILIKE '%coordenador%'
               OR n."nivelDescricao" ILIKE '%gerente%'
               OR t."tipoNome"       ILIKE '%coordenador%'
               OR t."tipoNome"       ILIKE '%gerente%'
              )
        )
        OR c."cargoNome" ILIKE '%coordenador%'
        OR c."cargoNome" ILIKE '%gerente%'
    )
    AND NOT EXISTS (
        SELECT 1 FROM habilidades_cargo hc
        WHERE hc."cargoId"      = c."cargoId"
          AND hc."habilidadeId" = s."habilidadeId"
    );

    RAISE NOTICE 'Migração 002 concluída com sucesso.';
END;
$$;
