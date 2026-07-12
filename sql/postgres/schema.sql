-- Schema PostgreSQL para Plano de Cargos e Salários
-- Convertido do MySQL original

-- Tipos ENUM
CREATE TYPE habilidade_tipo AS ENUM ('Hardskill', 'Softskill');
CREATE TYPE risco_nome AS ENUM ('Físico', 'Químico', 'Ergonômico', 'Psicossocial', 'Acidental', 'Biológico');

-- Tabela: areas_atuacao
CREATE TABLE areas_atuacao (
  "areaId"              SERIAL PRIMARY KEY,
  "areaPaiId"           INTEGER DEFAULT NULL,
  "areaNome"            VARCHAR(100) NOT NULL UNIQUE,
  "areaDescricao"       TEXT DEFAULT NULL,
  "areaDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "areaDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: audit_log
CREATE TABLE audit_log (
  "logId"        SERIAL PRIMARY KEY,
  "usuarioId"    INTEGER DEFAULT NULL,
  "nomeUsuario"  VARCHAR(100) DEFAULT NULL,
  "acao"         VARCHAR(50) NOT NULL,
  "nomeTabela"   VARCHAR(100) DEFAULT NULL,
  "idRegistro"   INTEGER DEFAULT NULL,
  "dadosJson"    TEXT DEFAULT NULL,
  "dataHora"     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_audit_userId    ON audit_log ("usuarioId");
CREATE INDEX idx_audit_table     ON audit_log ("nomeTabela", "idRegistro");
CREATE INDEX idx_audit_timestamp ON audit_log ("dataHora");

-- Tabela: caracteristicas
CREATE TABLE caracteristicas (
  "caracteristicaId"              SERIAL PRIMARY KEY,
  "caracteristicaNome"            VARCHAR(64) NOT NULL,
  "caracteristicaDescricao"       TEXT DEFAULT NULL,
  "caracteristicaDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "caracteristicaDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: escolaridades
CREATE TABLE escolaridades (
  "escolaridadeId"              SERIAL PRIMARY KEY,
  "escolaridadeTitulo"          VARCHAR(64) NOT NULL,
  "escolaridadeDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "escolaridadeDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: faixas_salariais
CREATE TABLE faixas_salariais (
  "faixaId"              SERIAL PRIMARY KEY,
  "faixaNivel"           VARCHAR(64) NOT NULL UNIQUE,
  "faixaSalarioMinimo"   DECIMAL(10,2) DEFAULT NULL,
  "faixaSalarioMaximo"   DECIMAL(10,2) DEFAULT NULL,
  "faixaDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "faixaDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: familia_cbo
CREATE TABLE familia_cbo (
  "familiaCboId"              SERIAL PRIMARY KEY,
  "familiaCboNome"            VARCHAR(64) NOT NULL,
  "familiaCboDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "familiaCboDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: cbos
CREATE TABLE cbos (
  "cboId"              SERIAL PRIMARY KEY,
  "familiaCboId"       INTEGER NOT NULL,
  "cboCod"             VARCHAR(64) NOT NULL,
  "cboTituloOficial"   VARCHAR(255) DEFAULT NULL,
  "cboDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "cboDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cbos_familia FOREIGN KEY ("familiaCboId") REFERENCES familia_cbo ("familiaCboId")
);

-- Tabela: tipo_hierarquia
CREATE TABLE tipo_hierarquia (
  "tipoId"              SERIAL PRIMARY KEY,
  "tipoNome"            VARCHAR(64) NOT NULL UNIQUE,
  "tipoDescricao"       VARCHAR(255) DEFAULT NULL,
  "tipoDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "tipoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: nivel_hierarquico
CREATE TABLE nivel_hierarquico (
  "nivelId"              SERIAL PRIMARY KEY,
  "tipoId"               INTEGER NOT NULL,
  "nivelOrdem"           INTEGER NOT NULL,
  "nivelDescricao"       VARCHAR(100) DEFAULT NULL,
  "nivelDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "nivelDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "nivelAtribuicoes"     TEXT DEFAULT NULL,
  "nivelAutonomia"       TEXT DEFAULT NULL,
  "nivelQuandoUtilizar"  TEXT DEFAULT NULL,
  CONSTRAINT fk_nivel_tipo FOREIGN KEY ("tipoId") REFERENCES tipo_hierarquia ("tipoId")
);

-- Tabela: cargos
CREATE TABLE cargos (
  "cargoId"                SERIAL PRIMARY KEY,
  "cargoNome"              VARCHAR(64) NOT NULL,
  "cargoDescricao"         TEXT DEFAULT NULL,
  "cboId"                  INTEGER NOT NULL,
  "cargoResumo"            TEXT DEFAULT NULL,
  "escolaridadeId"         INTEGER NOT NULL,
  "faixaId"                INTEGER DEFAULT NULL,
  "nivelHierarquicoId"     INTEGER DEFAULT NULL,
  "cargoSupervisorId"      INTEGER DEFAULT NULL,
  "cargoExperiencia"       TEXT DEFAULT NULL,
  "cargoCondicoes"         TEXT DEFAULT NULL,
  "cargoComplexidade"      TEXT DEFAULT NULL,
  "cargoResponsabilidades" TEXT DEFAULT NULL,
  "cargoDataCadastro"      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "cargoDataAtualizacao"   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cargos_cbo        FOREIGN KEY ("cboId")              REFERENCES cbos ("cboId"),
  CONSTRAINT fk_cargos_escol      FOREIGN KEY ("escolaridadeId")     REFERENCES escolaridades ("escolaridadeId"),
  CONSTRAINT fk_cargos_faixa      FOREIGN KEY ("faixaId")            REFERENCES faixas_salariais ("faixaId"),
  CONSTRAINT fk_cargos_nivel      FOREIGN KEY ("nivelHierarquicoId") REFERENCES nivel_hierarquico ("nivelId"),
  CONSTRAINT fk_cargos_supervisor FOREIGN KEY ("cargoSupervisorId")  REFERENCES cargos ("cargoId")
);

-- Tabela: cargos_area
CREATE TABLE cargos_area (
  "cargoAreaId" SERIAL PRIMARY KEY,
  "cargoId"     INTEGER NOT NULL,
  "areaId"      INTEGER NOT NULL,
  UNIQUE ("cargoId", "areaId"),
  CONSTRAINT fk_cargos_area_cargo FOREIGN KEY ("cargoId") REFERENCES cargos ("cargoId"),
  CONSTRAINT fk_cargos_area_area  FOREIGN KEY ("areaId")  REFERENCES areas_atuacao ("areaId")
);

-- Tabela: cargo_sinonimos
CREATE TABLE cargo_sinonimos (
  "cargoSinonimoId"   SERIAL PRIMARY KEY,
  "cargoId"           INTEGER NOT NULL,
  "cargoSinonimoNome" VARCHAR(64) NOT NULL,
  CONSTRAINT fk_sinonimos_cargo FOREIGN KEY ("cargoId") REFERENCES cargos ("cargoId")
);

-- Tabela: cursos
CREATE TABLE cursos (
  "cursoId"              SERIAL PRIMARY KEY,
  "cursoNome"            VARCHAR(64) NOT NULL,
  "cursoDescricao"       TEXT DEFAULT NULL,
  "cursoDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "cursoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: cursos_cargo
CREATE TABLE cursos_cargo (
  "cursoCargoId"          SERIAL PRIMARY KEY,
  "cursoId"               INTEGER NOT NULL,
  "cargoId"               INTEGER NOT NULL,
  "cursoCargoObrigatorio" SMALLINT DEFAULT NULL,
  "cursoCargoObs"         TEXT DEFAULT NULL,
  CONSTRAINT fk_cursos_cargo_curso FOREIGN KEY ("cursoId") REFERENCES cursos ("cursoId"),
  CONSTRAINT fk_cursos_cargo_cargo FOREIGN KEY ("cargoId") REFERENCES cargos ("cargoId")
);

-- Tabela: habilidades
CREATE TABLE habilidades (
  "habilidadeId"              SERIAL PRIMARY KEY,
  "habilidadeTipo"            habilidade_tipo NOT NULL,
  "habilidadeNome"            VARCHAR(64) NOT NULL,
  "habilidadeDescricao"       TEXT DEFAULT NULL,
  "habilidadeDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "habilidadeDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: habilidades_cargo
CREATE TABLE habilidades_cargo (
  "habilidadeCargoId" SERIAL PRIMARY KEY,
  "cargoId"           INTEGER NOT NULL,
  "habilidadeId"      INTEGER NOT NULL,
  CONSTRAINT uq_habilidades_cargo            UNIQUE ("cargoId", "habilidadeId"),
  CONSTRAINT fk_habilidades_cargo_cargo      FOREIGN KEY ("cargoId")      REFERENCES cargos ("cargoId"),
  CONSTRAINT fk_habilidades_cargo_habilidade FOREIGN KEY ("habilidadeId") REFERENCES habilidades ("habilidadeId")
);

-- Tabela: recursos
CREATE TABLE recursos (
  "recursoId"              SERIAL PRIMARY KEY,
  "recursoNome"            VARCHAR(64) NOT NULL,
  "recursoDescricao"       TEXT DEFAULT NULL,
  "recursoDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "recursoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: recursos_cargo
CREATE TABLE recursos_cargo (
  "recursoCargoId" SERIAL PRIMARY KEY,
  "cargoId"        INTEGER NOT NULL,
  "recursoId"      INTEGER NOT NULL,
  CONSTRAINT fk_recursos_cargo_cargo   FOREIGN KEY ("cargoId")   REFERENCES cargos ("cargoId"),
  CONSTRAINT fk_recursos_cargo_recurso FOREIGN KEY ("recursoId") REFERENCES recursos ("recursoId")
);

-- Tabela: recursos_grupos
CREATE TABLE recursos_grupos (
  "recursoGrupoId"              SERIAL PRIMARY KEY,
  "recursoGrupoNome"            VARCHAR(64) NOT NULL,
  "recursoGrupoDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "recursoGrupoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: recursos_grupos_cargo
CREATE TABLE recursos_grupos_cargo (
  "recursoGrupoCargoId" SERIAL PRIMARY KEY,
  "cargoId"             INTEGER NOT NULL,
  "recursoGrupoId"      INTEGER NOT NULL,
  UNIQUE ("cargoId", "recursoGrupoId"),
  CONSTRAINT fk_rgc_cargo FOREIGN KEY ("cargoId")        REFERENCES cargos ("cargoId"),
  CONSTRAINT fk_rgc_grupo FOREIGN KEY ("recursoGrupoId") REFERENCES recursos_grupos ("recursoGrupoId")
);

-- Tabela: recurso_grupo_recurso
CREATE TABLE recurso_grupo_recurso (
  "recursoGrupoRecursoId"              SERIAL PRIMARY KEY,
  "recursoGrupoId"                     INTEGER NOT NULL,
  "recursoId"                          INTEGER NOT NULL,
  "recursoGrupoRecursoDataCadastro"    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "recursoGrupoRecuraoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rgr_grupo   FOREIGN KEY ("recursoGrupoId") REFERENCES recursos_grupos ("recursoGrupoId"),
  CONSTRAINT fk_rgr_recurso FOREIGN KEY ("recursoId")      REFERENCES recursos ("recursoId")
);

-- Tabela: riscos
CREATE TABLE riscos (
  "riscoId"   SERIAL PRIMARY KEY,
  "riscoNome" risco_nome NOT NULL
);

-- Tabela: riscos_cargo
CREATE TABLE riscos_cargo (
  "riscoCargoId"        SERIAL PRIMARY KEY,
  "riscoId"             INTEGER NOT NULL,
  "cargoId"             INTEGER NOT NULL,
  "riscoDescricao"      VARCHAR(255) DEFAULT NULL,
  "riscoDataCadastro"   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "riscoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_riscos_cargo_risco FOREIGN KEY ("riscoId") REFERENCES riscos ("riscoId"),
  CONSTRAINT fk_riscos_cargo_cargo FOREIGN KEY ("cargoId") REFERENCES cargos ("cargoId")
);

-- Tabela: roles
CREATE TABLE roles (
  "roleId"          SERIAL PRIMARY KEY,
  "roleName"        VARCHAR(100) NOT NULL UNIQUE,
  "roleDescription" TEXT DEFAULT NULL
);

-- Tabela: permissions
CREATE TABLE permissions (
  "permissionId"          SERIAL PRIMARY KEY,
  "permissionName"        VARCHAR(100) NOT NULL UNIQUE,
  "permissionDescription" TEXT DEFAULT NULL
);

-- Tabela: role_permissions
CREATE TABLE role_permissions (
  "roleId"       INTEGER NOT NULL,
  "permissionId" INTEGER NOT NULL,
  PRIMARY KEY ("roleId", "permissionId"),
  CONSTRAINT fk_rp_role       FOREIGN KEY ("roleId")       REFERENCES roles ("roleId"),
  CONSTRAINT fk_rp_permission FOREIGN KEY ("permissionId") REFERENCES permissions ("permissionId")
);

-- Tabela: usuarios
CREATE TABLE usuarios (
  "usuarioId"    SERIAL PRIMARY KEY,
  "nome"         VARCHAR(100) NOT NULL,
  "email"        VARCHAR(100) NOT NULL UNIQUE,
  "senha"        VARCHAR(255) NOT NULL,
  "ativo"        SMALLINT DEFAULT 1,
  "dataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: user_roles
CREATE TABLE user_roles (
  "usuarioId" INTEGER NOT NULL,
  "roleId"    INTEGER NOT NULL,
  PRIMARY KEY ("usuarioId", "roleId"),
  CONSTRAINT fk_ur_usuario FOREIGN KEY ("usuarioId") REFERENCES usuarios ("usuarioId"),
  CONSTRAINT fk_ur_role    FOREIGN KEY ("roleId")    REFERENCES roles ("roleId")
);

-- Tabela: caracteristicas_cargo
CREATE TABLE caracteristicas_cargo (
  "caracteristicaCargoId" SERIAL PRIMARY KEY,
  "cargoId"               INTEGER DEFAULT NULL,
  "caracteristicaId"      INTEGER DEFAULT NULL,
  CONSTRAINT fk_cc_cargo          FOREIGN KEY ("cargoId")          REFERENCES cargos ("cargoId"),
  CONSTRAINT fk_cc_caracteristica FOREIGN KEY ("caracteristicaId") REFERENCES caracteristicas ("caracteristicaId")
);

-- Self-reference para areas_atuacao (área pai)
ALTER TABLE areas_atuacao
  ADD CONSTRAINT fk_area_pai FOREIGN KEY ("areaPaiId") REFERENCES areas_atuacao ("areaId");

-- Tabela: leadership_softskills_config
-- Associa tipos hierárquicos às softskills que devem ser herdadas automaticamente.
-- Administradores podem editar via interface sem alterar código.
CREATE TABLE leadership_softskills_config (
  "configId"     SERIAL PRIMARY KEY,
  "tipoId"       INTEGER NOT NULL,
  "habilidadeId" INTEGER NOT NULL,
  UNIQUE ("tipoId", "habilidadeId"),
  CONSTRAINT fk_lsc_tipo       FOREIGN KEY ("tipoId")       REFERENCES tipo_hierarquia ("tipoId") ON DELETE CASCADE,
  CONSTRAINT fk_lsc_habilidade FOREIGN KEY ("habilidadeId") REFERENCES habilidades ("habilidadeId") ON DELETE CASCADE
);
