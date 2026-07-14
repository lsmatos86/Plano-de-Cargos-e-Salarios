-- Fluxo de revisão, aprovação e bloqueio dos cargos.
ALTER TABLE cargos
    ADD COLUMN IF NOT EXISTS is_aprovado SMALLINT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS data_aprovacao TIMESTAMP WITHOUT TIME ZONE NULL,
    ADD COLUMN IF NOT EXISTS revisado_por_usuario_id INTEGER NULL,
    ADD COLUMN IF NOT EXISTS aprovado_por_usuario_id INTEGER NULL;

-- Preserva o estado dos documentos que já possuíam o selo antigo de revisão.
UPDATE cargos
   SET is_aprovado = 1,
       data_aprovacao = COALESCE(data_aprovacao, data_revisao)
 WHERE COALESCE(is_revisado, 0) = 1
   AND COALESCE(is_aprovado, 0) = 0;

CREATE INDEX IF NOT EXISTS idx_cargos_homologacao
    ON cargos (is_aprovado, data_aprovacao);

-- Registra uma única vez os cargos aprovados antes da implantação deste fluxo.
INSERT INTO audit_log ("usuarioId", "nomeUsuario", acao, "nomeTabela", "idRegistro", "dadosJson", "dataHora")
SELECT NULL,
       'Migração do sistema',
       'CARGO_APROVACAO',
       'cargos_homologacao',
       c."cargoId",
       '{"categoria":"homologacao_cargos","origem":"migracao_estado_legado","cargo_bloqueado":true}',
       COALESCE(c.data_aprovacao, c.data_revisao, CURRENT_TIMESTAMP)
  FROM cargos c
 WHERE c.is_aprovado = 1
   AND NOT EXISTS (
       SELECT 1
         FROM audit_log a
        WHERE a."nomeTabela" = 'cargos_homologacao'
          AND a."idRegistro" = c."cargoId"
          AND a.acao = 'CARGO_APROVACAO'
   );
