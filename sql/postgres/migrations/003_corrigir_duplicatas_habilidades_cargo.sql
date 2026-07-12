-- =============================================================
-- Migração 003: Corrigir duplicatas em habilidades_cargo
--               e adicionar constraint UNIQUE (cargoId, habilidadeId)
-- =============================================================
-- Esta migração é idempotente: pode ser executada mais de uma vez
-- sem causar erros ou efeitos colaterais.
--
-- O que faz:
--   1. Remove linhas duplicadas de habilidades_cargo, mantendo
--      apenas a linha com o menor habilidadeCargoId de cada par
--      (cargoId, habilidadeId).
--   2. Adiciona a constraint UNIQUE uq_habilidades_cargo se ela
--      ainda não existir.
-- =============================================================

-- PASSO 1: Remover duplicatas, mantendo o registro mais antigo
-- (menor habilidadeCargoId) de cada par (cargoId, habilidadeId).
DELETE FROM habilidades_cargo
WHERE "habilidadeCargoId" IN (
    SELECT "habilidadeCargoId"
    FROM (
        SELECT
            "habilidadeCargoId",
            ROW_NUMBER() OVER (
                PARTITION BY "cargoId", "habilidadeId"
                ORDER BY "habilidadeCargoId" ASC
            ) AS rn
        FROM habilidades_cargo
    ) ranked
    WHERE rn > 1
);

-- PASSO 2: Adicionar constraint UNIQUE se ainda não existir
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'uq_habilidades_cargo'
          AND conrelid = 'habilidades_cargo'::regclass
    ) THEN
        ALTER TABLE habilidades_cargo
            ADD CONSTRAINT uq_habilidades_cargo UNIQUE ("cargoId", "habilidadeId");
        RAISE NOTICE 'Constraint uq_habilidades_cargo adicionada com sucesso.';
    ELSE
        RAISE NOTICE 'Constraint uq_habilidades_cargo já existe. Nenhuma ação necessária.';
    END IF;
END;
$$;
