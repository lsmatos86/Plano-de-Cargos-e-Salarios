-- Migração: Cria tabela de configuração de softskills de liderança
-- Execute este script em bancos de dados existentes que já possuem o schema base.
-- Para novas instalações, use sql/postgres/schema.sql (já inclui esta tabela).

CREATE TABLE IF NOT EXISTS leadership_softskills_config (
  "configId"     SERIAL PRIMARY KEY,
  "tipoId"       INTEGER NOT NULL,
  "habilidadeId" INTEGER NOT NULL,
  UNIQUE ("tipoId", "habilidadeId"),
  CONSTRAINT fk_lsc_tipo       FOREIGN KEY ("tipoId")       REFERENCES tipo_hierarquia ("tipoId") ON DELETE CASCADE,
  CONSTRAINT fk_lsc_habilidade FOREIGN KEY ("habilidadeId") REFERENCES habilidades ("habilidadeId") ON DELETE CASCADE
);

-- Instruções para popular com os dados originais hardcoded:
--
-- Os IDs abaixo eram os valores fixos no código. Substitua pelos IDs reais
-- dos seus tipos hierárquicos e habilidades no banco.
--
-- Exemplo (ajuste conforme seus dados):
--
-- -- Softskills base (ex: IDs 28, 134, 135) para "Supervisor"
-- INSERT INTO leadership_softskills_config ("tipoId", "habilidadeId")
-- SELECT t."tipoId", h."habilidadeId"
-- FROM tipo_hierarquia t, habilidades h
-- WHERE LOWER(t."tipoNome") LIKE '%supervisor%'
--   AND h."habilidadeId" IN (28, 134, 135)
-- ON CONFLICT DO NOTHING;
--
-- -- Softskills base + extra (ex: IDs 28, 134, 135, 5, 21) para "Gerente"
-- INSERT INTO leadership_softskills_config ("tipoId", "habilidadeId")
-- SELECT t."tipoId", h."habilidadeId"
-- FROM tipo_hierarquia t, habilidades h
-- WHERE LOWER(t."tipoNome") LIKE '%gerente%'
--   AND h."habilidadeId" IN (28, 134, 135, 5, 21)
-- ON CONFLICT DO NOTHING;
--
-- -- Softskills base + extra (ex: IDs 28, 134, 135, 5, 21) para "Coordenador"
-- INSERT INTO leadership_softskills_config ("tipoId", "habilidadeId")
-- SELECT t."tipoId", h."habilidadeId"
-- FROM tipo_hierarquia t, habilidades h
-- WHERE LOWER(t."tipoNome") LIKE '%coordenador%'
--   AND h."habilidadeId" IN (28, 134, 135, 5, 21)
-- ON CONFLICT DO NOTHING;
