

CREATE TABLE "areas_atuacao" (
  "areaId" INTEGER NOT NULL,
  "areaPaiId" INTEGER DEFAULT NULL,
  "areaNome" VARCHAR(100) NOT NULL,
  "areaDescricao" TEXT DEFAULT NULL,
  "areaDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "areaDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "audit_log" (
  "logId" INTEGER NOT NULL,
  "usuarioId" INTEGER DEFAULT NULL COMMENT 'ID do usuário (da tabela usuarios)',
  "nomeUsuario" VARCHAR(100) DEFAULT NULL COMMENT 'Nome do usuário (para referência rápida)',
  "acao" VARCHAR(50) NOT NULL COMMENT 'Ex: CREATE, UPDATE, DELETE, LOGIN_SUCCESS, LOGIN_FAIL',
  "nomeTabela" VARCHAR(100) DEFAULT NULL COMMENT 'Tabela afetada (ex: cargos, usuarios)',
  "idRegistro" INTEGER DEFAULT NULL COMMENT 'ID do registro afetado',
  "dadosJson" TEXT COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Dados antigos ou novos (JSON)' ,
  "dataHora" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "caracteristicas" (
  "caracteristicaId" INTEGER NOT NULL,
  "caracteristicaNome" VARCHAR(64) NOT NULL,
  "caracteristicaDescricao" TEXT DEFAULT NULL,
  "caracteristicaDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "caracteristicaDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "caracteristicas_cargo" (
  "característicaCargoId" INTEGER NOT NULL,
  "cargoId" INTEGER DEFAULT NULL,
  "caracteristicaId" INTEGER DEFAULT NULL
);

CREATE TABLE "cargos" (
  "cargoId" INTEGER NOT NULL,
  "cargoNome" VARCHAR(64) NOT NULL,
  "cargoDescricao" TEXT DEFAULT NULL,
  "cboId" INTEGER NOT NULL,
  "cargoResumo" TEXT DEFAULT NULL,
  "escolaridadeId" INTEGER NOT NULL,
  "faixaId" INTEGER DEFAULT NULL,
  "nivelHierarquicoId" INTEGER DEFAULT NULL,
  "cargoSupervisorId" INTEGER DEFAULT NULL,
  "cargoExperiencia" TEXT DEFAULT NULL,
  "cargoCondicoes" TEXT DEFAULT NULL,
  "cargoComplexidade" TEXT DEFAULT NULL,
  "cargoResponsabilidades" TEXT DEFAULT NULL,
  "cargoDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "cargoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "cargos_area" (
  "cargoAreaId" INTEGER NOT NULL,
  "cargoId" INTEGER NOT NULL,
  "areaId" INTEGER NOT NULL
);

CREATE TABLE "cargo_sinonimos" (
  "cargoSinonimoId" INTEGER NOT NULL,
  "cargoId" INTEGER NOT NULL,
  "cargoSinonimoNome" VARCHAR(64) NOT NULL
);

CREATE TABLE "cbos" (
  "cboId" INTEGER NOT NULL,
  "familiaCboId" INTEGER NOT NULL,
  "cboCod" VARCHAR(64) NOT NULL,
  "cboTituloOficial" VARCHAR(255) DEFAULT NULL,
  "cboDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "cboDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "cursos" (
  "cursoId" INTEGER NOT NULL,
  "cursoNome" VARCHAR(64) NOT NULL,
  "cursoDescricao" TEXT DEFAULT NULL,
  "cursoDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "cursoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "cursos_cargo" (
  "cursoCargoId" INTEGER NOT NULL,
  "cursoId" INTEGER NOT NULL,
  "cargoId" INTEGER NOT NULL,
  "cursoCargoObrigatorio" tinyINTEGER DEFAULT NULL,
  "cursoCargoObs" TEXT DEFAULT NULL
);

CREATE TABLE "escolaridades" (
  "escolaridadeId" INTEGER NOT NULL,
  "escolaridadeTitulo" VARCHAR(64) NOT NULL,
  "escolaridadeDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "escolaridadeDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "faixas_salariais" (
  "faixaId" INTEGER NOT NULL,
  "faixaNivel" VARCHAR(64) NOT NULL,
  "faixaSalarioMinimo" decimal(10,2) DEFAULT NULL,
  "faixaSalarioMaximo" decimal(10,2) DEFAULT NULL,
  "faixaDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "faixaDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "familia_cbo" (
  "familiaCboId" INTEGER NOT NULL,
  "familiaCboNome" VARCHAR(64) NOT NULL,
  "familiaCboDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "familiaCboDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "habilidades" (
  "habilidadeId" INTEGER NOT NULL,
  "habilidadeTipo" enum('Hardskill','Softskill') NOT NULL,
  "habilidadeNome" VARCHAR(64) NOT NULL,
  "habilidadeDescricao" TEXT DEFAULT NULL,
  "habilidadeDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "habilidadeDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "habilidades_cargo" (
  "habilidadeCargoId" INTEGER NOT NULL,
  "cargoId" INTEGER NOT NULL,
  "habilidadeId" INTEGER NOT NULL
);

CREATE TABLE "nivel_hierarquico" (
  "nivelId" INTEGER NOT NULL,
  "tipoId" INTEGER NOT NULL,
  "nivelOrdem" INTEGER NOT NULL,
  "nivelDescricao" VARCHAR(100) DEFAULT NULL,
  "nivelDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "nivelDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "nivelAtribuicoes" TEXT DEFAULT NULL,
  "nivelAutonomia" TEXT DEFAULT NULL,
  "nivelQuandoUtilizar" TEXT DEFAULT NULL
);

CREATE TABLE "permissions" (
  "permissionId" INTEGER NOT NULL,
  "permissionName" VARCHAR(100) NOT NULL COMMENT 'Ex: cargos:create, cargos:edit, usuarios:manage',
  "permissionDescription" TEXT DEFAULT NULL
);

CREATE TABLE "recursos" (
  "recursoId" INTEGER NOT NULL,
  "recursoNome" VARCHAR(64) NOT NULL,
  "recursoDescricao" TEXT DEFAULT NULL,
  "recursoDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "recursoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "recursos_cargo" (
  "recursoCargoId" INTEGER NOT NULL,
  "cargoId" INTEGER NOT NULL,
  "recursoId" INTEGER NOT NULL
);

CREATE TABLE "recursos_grupos" (
  "recursoGrupoId" INTEGER NOT NULL,
  "recursoGrupoNome" VARCHAR(64) NOT NULL,
  "recursoGrupoDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "recursoGrupoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "recursos_grupos_cargo" (
  "recursoGrupoCargoId" INTEGER NOT NULL,
  "cargoId" INTEGER NOT NULL,
  "recursoGrupoId" INTEGER NOT NULL
);

CREATE TABLE "recurso_grupo_recurso" (
  "recursoGrupoRecursoId" INTEGER NOT NULL,
  "recursoGrupoId" INTEGER NOT NULL,
  "recursoId" INTEGER NOT NULL,
  "recursoGrupoRecursoDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "recursoGrupoRecuraoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "riscos" (
  "riscoId" INTEGER NOT NULL,
  "riscoNome" enum('Físico','Químico','Ergonômico','Psicossocial','Acidental','Biológico') NOT NULL
);

CREATE TABLE "riscos_cargo" (
  "riscoCargoId" INTEGER NOT NULL,
  "riscoId" INTEGER NOT NULL,
  "cargoId" INTEGER NOT NULL,
  "riscoDescricao" VARCHAR(255) DEFAULT NULL,
  "riscoDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "riscoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "roles" (
  "roleId" INTEGER NOT NULL,
  "roleName" VARCHAR(100) NOT NULL,
  "roleDescription" TEXT DEFAULT NULL
);

CREATE TABLE "role_permissions" (
  "roleId" INTEGER NOT NULL,
  "permissionId" INTEGER NOT NULL
);

CREATE TABLE "tipo_hierarquia" (
  "tipoId" INTEGER NOT NULL,
  "tipoNome" VARCHAR(64) NOT NULL,
  "tipoDescricao" VARCHAR(255) DEFAULT NULL,
  "tipoDataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "tipoDataAtualizacao" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "user_roles" (
  "usuarioId" INTEGER NOT NULL COMMENT 'Chave estrangeira de usuarios.usuarioId',
  "roleId" INTEGER NOT NULL
);

CREATE TABLE "usuarios" (
  "usuarioId" INTEGER NOT NULL,
  "nome" VARCHAR(100) NOT NULL,
  "email" VARCHAR(100) NOT NULL,
  "senha" VARCHAR(255) NOT NULL,
  "ativo" tinyINTEGER DEFAULT 1,
  "dataCadastro" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE "areas_atuacao"
  ADD PRIMARY KEY ("areaId"),
  ADD UNIQUE KEY "areaNome" ("areaNome"),
  ADD KEY "fk_area_pai" ("areaPaiId");

ALTER TABLE "audit_log"
  ADD PRIMARY KEY ("logId"),
  ADD KEY "idx_userId" ("usuarioId"),
  ADD KEY "idx_tableName_recordId" ("nomeTabela","idRegistro"),
  ADD KEY "idx_timestamp" ("dataHora");

ALTER TABLE "caracteristicas"
  ADD PRIMARY KEY ("caracteristicaId");

ALTER TABLE "caracteristicas_cargo"
  ADD PRIMARY KEY ("característicaCargoId"),
  ADD KEY "fk_caracteristicas_cargo_caracteristica" ("caracteristicaId"),
  ADD KEY "fk_caracteristicas_cargo_cargo" ("cargoId");

ALTER TABLE "cargos"
  ADD PRIMARY KEY ("cargoId"),
  ADD KEY "fk_cargos_cbo" ("cboId"),
  ADD KEY "fk_cargos_escolaridade" ("escolaridadeId"),
  ADD KEY "fk_cargos_faixa_salario" ("faixaId"),
  ADD KEY "fk_cargos_nivel_hierarquico" ("nivelHierarquicoId"),
  ADD KEY "fk_cargos_supervisor" ("cargoSupervisorId");

ALTER TABLE "cargos_area"
  ADD PRIMARY KEY ("cargoAreaId"),
  ADD UNIQUE KEY "uk_cargo_area" ("cargoId","areaId"),
  ADD KEY "fk_cargos_area_cargo" ("cargoId"),
  ADD KEY "fk_cargos_area_area" ("areaId");

ALTER TABLE "cargo_sinonimos"
  ADD PRIMARY KEY ("cargoSinonimoId"),
  ADD KEY "fk_sinonimos_cargo" ("cargoId");

ALTER TABLE "cbos"
  ADD PRIMARY KEY ("cboId"),
  ADD KEY "fk_cbos_familia_cbo" ("familiaCboId");

ALTER TABLE "cursos"
  ADD PRIMARY KEY ("cursoId");

ALTER TABLE "cursos_cargo"
  ADD PRIMARY KEY ("cursoCargoId"),
  ADD KEY "fk_cursos_cargo_curso" ("cursoId"),
  ADD KEY "fk_cursos_cargo_cargo" ("cargoId");

ALTER TABLE "escolaridades"
  ADD PRIMARY KEY ("escolaridadeId");

ALTER TABLE "faixas_salariais"
  ADD PRIMARY KEY ("faixaId"),
  ADD UNIQUE KEY "faixaNivel" ("faixaNivel");

ALTER TABLE "familia_cbo"
  ADD PRIMARY KEY ("familiaCboId");

ALTER TABLE "habilidades"
  ADD PRIMARY KEY ("habilidadeId");

ALTER TABLE "habilidades_cargo"
  ADD PRIMARY KEY ("habilidadeCargoId"),
  ADD KEY "fk_habilidades_cargo_habilidade" ("habilidadeId"),
  ADD KEY "fk_habilidades_cargo_cargo" ("cargoId");

ALTER TABLE "nivel_hierarquico"
  ADD PRIMARY KEY ("nivelId"),
  ADD KEY "fk_nivel_tipo" ("tipoId");

ALTER TABLE "permissions"
  ADD PRIMARY KEY ("permissionId"),
  ADD UNIQUE KEY "permissionName" ("permissionName");

ALTER TABLE "recursos"
  ADD PRIMARY KEY ("recursoId");

ALTER TABLE "recursos_cargo"
  ADD PRIMARY KEY ("recursoCargoId"),
  ADD KEY "fk_recursos_cargo_recurso" ("recursoId"),
  ADD KEY "fk_recursos_cargo_cargo" ("cargoId");

ALTER TABLE "recursos_grupos"
  ADD PRIMARY KEY ("recursoGrupoId");

ALTER TABLE "recursos_grupos_cargo"
  ADD PRIMARY KEY ("recursoGrupoCargoId"),
  ADD UNIQUE KEY "uk_cargo_grupo" ("cargoId","recursoGrupoId"),
  ADD KEY "fk_rgc_grupo" ("recursoGrupoId");

ALTER TABLE "recurso_grupo_recurso"
  ADD PRIMARY KEY ("recursoGrupoRecursoId"),
  ADD KEY "fk_recurso_grupo_recurso_grupo" ("recursoGrupoId"),
  ADD KEY "fk_recurso_grupo_recurso_recurso" ("recursoId");

ALTER TABLE "riscos"
  ADD PRIMARY KEY ("riscoId");

ALTER TABLE "riscos_cargo"
  ADD PRIMARY KEY ("riscoCargoId"),
  ADD KEY "fk_riscos_cargo_risco" ("riscoId"),
  ADD KEY "fk_riscos_cargo_cargo" ("cargoId");

ALTER TABLE "roles"
  ADD PRIMARY KEY ("roleId"),
  ADD UNIQUE KEY "roleName" ("roleName");

ALTER TABLE "role_permissions"
  ADD PRIMARY KEY ("roleId","permissionId"),
  ADD KEY "permissionId" ("permissionId");

ALTER TABLE "tipo_hierarquia"
  ADD PRIMARY KEY ("tipoId"),
  ADD UNIQUE KEY "tipoNome" ("tipoNome");

ALTER TABLE "user_roles"
  ADD PRIMARY KEY ("usuarioId","roleId"),
  ADD KEY "roleId" ("roleId");

ALTER TABLE "usuarios"
  ADD PRIMARY KEY ("usuarioId"),
  ADD UNIQUE KEY "email" ("email");

ALTER TABLE "areas_atuacao"
  MODIFY "areaId" SERIAL;

ALTER TABLE "audit_log"
  MODIFY "logId" SERIAL;

ALTER TABLE "caracteristicas"
  MODIFY "caracteristicaId" SERIAL;

ALTER TABLE "caracteristicas_cargo"
  MODIFY "característicaCargoId" SERIAL;

ALTER TABLE "cargos"
  MODIFY "cargoId" SERIAL;

ALTER TABLE "cargos_area"
  MODIFY "cargoAreaId" SERIAL;

ALTER TABLE "cargo_sinonimos"
  MODIFY "cargoSinonimoId" SERIAL;

ALTER TABLE "cbos"
  MODIFY "cboId" SERIAL;

ALTER TABLE "cursos"
  MODIFY "cursoId" SERIAL;

ALTER TABLE "cursos_cargo"
  MODIFY "cursoCargoId" SERIAL;

ALTER TABLE "escolaridades"
  MODIFY "escolaridadeId" SERIAL;

ALTER TABLE "faixas_salariais"
  MODIFY "faixaId" SERIAL;

ALTER TABLE "familia_cbo"
  MODIFY "familiaCboId" SERIAL;

ALTER TABLE "habilidades"
  MODIFY "habilidadeId" SERIAL;

ALTER TABLE "habilidades_cargo"
  MODIFY "habilidadeCargoId" SERIAL;

ALTER TABLE "nivel_hierarquico"
  MODIFY "nivelId" SERIAL;

ALTER TABLE "permissions"
  MODIFY "permissionId" SERIAL;

ALTER TABLE "recursos"
  MODIFY "recursoId" SERIAL;

ALTER TABLE "recursos_cargo"
  MODIFY "recursoCargoId" SERIAL;

ALTER TABLE "recursos_grupos"
  MODIFY "recursoGrupoId" SERIAL;

ALTER TABLE "recursos_grupos_cargo"
  MODIFY "recursoGrupoCargoId" SERIAL;

ALTER TABLE "recurso_grupo_recurso"
  MODIFY "recursoGrupoRecursoId" SERIAL;

ALTER TABLE "riscos"
  MODIFY "riscoId" SERIAL;

ALTER TABLE "riscos_cargo"
  MODIFY "riscoCargoId" SERIAL;

ALTER TABLE "roles"
  MODIFY "roleId" SERIAL;

ALTER TABLE "tipo_hierarquia"
  MODIFY "tipoId" SERIAL;

ALTER TABLE "usuarios"
  MODIFY "usuarioId" SERIAL;

ALTER TABLE "areas_atuacao"
  ADD CONSTRAINT "fk_area_pai" FOREIGN KEY ("areaPaiId") REFERENCES "areas_atuacao" ("areaId") ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE "caracteristicas_cargo"
  ADD CONSTRAINT "fk_caracteristicas_cargo_caracteristica" FOREIGN KEY ("caracteristicaId") REFERENCES "caracteristicas" ("caracteristicaId"),
  ADD CONSTRAINT "fk_caracteristicas_cargo_cargo" FOREIGN KEY ("cargoId") REFERENCES "cargos" ("cargoId");

ALTER TABLE "cargos"
  ADD CONSTRAINT "fk_cargos_cbo" FOREIGN KEY ("cboId") REFERENCES "cbos" ("cboId"),
  ADD CONSTRAINT "fk_cargos_escolaridade" FOREIGN KEY ("escolaridadeId") REFERENCES "escolaridades" ("escolaridadeId"),
  ADD CONSTRAINT "fk_cargos_faixa_salario" FOREIGN KEY ("faixaId") REFERENCES "faixas_salariais" ("faixaId"),
  ADD CONSTRAINT "fk_cargos_nivel_hierarquico" FOREIGN KEY ("nivelHierarquicoId") REFERENCES "nivel_hierarquico" ("nivelId") ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT "fk_cargos_supervisor" FOREIGN KEY ("cargoSupervisorId") REFERENCES "cargos" ("cargoId") ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE "cargos_area"
  ADD CONSTRAINT "fk_cargos_area_area" FOREIGN KEY ("areaId") REFERENCES "areas_atuacao" ("areaId") ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT "fk_cargos_area_cargo" FOREIGN KEY ("cargoId") REFERENCES "cargos" ("cargoId") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "cargo_sinonimos"
  ADD CONSTRAINT "fk_sinonimos_cargo" FOREIGN KEY ("cargoId") REFERENCES "cargos" ("cargoId");

ALTER TABLE "cbos"
  ADD CONSTRAINT "fk_cbos_familia_cbo" FOREIGN KEY ("familiaCboId") REFERENCES "familia_cbo" ("familiaCboId");

ALTER TABLE "cursos_cargo"
  ADD CONSTRAINT "fk_cursos_cargo_cargo" FOREIGN KEY ("cargoId") REFERENCES "cargos" ("cargoId"),
  ADD CONSTRAINT "fk_cursos_cargo_curso" FOREIGN KEY ("cursoId") REFERENCES "cursos" ("cursoId");

ALTER TABLE "habilidades_cargo"
  ADD CONSTRAINT "fk_habilidades_cargo_cargo" FOREIGN KEY ("cargoId") REFERENCES "cargos" ("cargoId"),
  ADD CONSTRAINT "fk_habilidades_cargo_habilidade" FOREIGN KEY ("habilidadeId") REFERENCES "habilidades" ("habilidadeId");

ALTER TABLE "nivel_hierarquico"
  ADD CONSTRAINT "fk_nivel_tipo" FOREIGN KEY ("tipoId") REFERENCES "tipo_hierarquia" ("tipoId") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "recursos_cargo"
  ADD CONSTRAINT "fk_recursos_cargo_cargo" FOREIGN KEY ("cargoId") REFERENCES "cargos" ("cargoId"),
  ADD CONSTRAINT "fk_recursos_cargo_recurso" FOREIGN KEY ("recursoId") REFERENCES "recursos" ("recursoId");

ALTER TABLE "recursos_grupos_cargo"
  ADD CONSTRAINT "fk_rgc_cargo" FOREIGN KEY ("cargoId") REFERENCES "cargos" ("cargoId"),
  ADD CONSTRAINT "fk_rgc_grupo" FOREIGN KEY ("recursoGrupoId") REFERENCES "recursos_grupos" ("recursoGrupoId");

ALTER TABLE "recurso_grupo_recurso"
  ADD CONSTRAINT "fk_recurso_grupo_recurso_grupo" FOREIGN KEY ("recursoGrupoId") REFERENCES "recursos_grupos" ("recursoGrupoId"),
  ADD CONSTRAINT "fk_recurso_grupo_recurso_recurso" FOREIGN KEY ("recursoId") REFERENCES "recursos" ("recursoId");

ALTER TABLE "riscos_cargo"
  ADD CONSTRAINT "fk_riscos_cargo_cargo" FOREIGN KEY ("cargoId") REFERENCES "cargos" ("cargoId"),
  ADD CONSTRAINT "fk_riscos_cargo_risco" FOREIGN KEY ("riscoId") REFERENCES "riscos" ("riscoId");

ALTER TABLE "role_permissions"
  ADD CONSTRAINT "role_permissions_ibfk_1" FOREIGN KEY ("roleId") REFERENCES "roles" ("roleId") ON DELETE CASCADE,
  ADD CONSTRAINT "role_permissions_ibfk_2" FOREIGN KEY ("permissionId") REFERENCES "permissions" ("permissionId") ON DELETE CASCADE;

ALTER TABLE "user_roles"
  ADD CONSTRAINT "user_roles_ibfk_1" FOREIGN KEY ("usuarioId") REFERENCES "usuarios" ("usuarioId") ON DELETE CASCADE,
  ADD CONSTRAINT "user_roles_ibfk_2" FOREIGN KEY ("roleId") REFERENCES "roles" ("roleId") ON DELETE CASCADE;
COMMIT;

