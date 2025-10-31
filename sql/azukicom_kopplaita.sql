-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 31/10/2025 às 21:26
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `azukicom_kopplaita`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `areas_atuacao`
--

CREATE TABLE `areas_atuacao` (
  `areaId` int(5) NOT NULL,
  `areaPaiId` int(5) DEFAULT NULL,
  `areaNome` varchar(100) NOT NULL,
  `areaDescricao` text DEFAULT NULL,
  `areaDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `areaDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `caracteristicas`
--

CREATE TABLE `caracteristicas` (
  `caracteristicaId` int(5) NOT NULL,
  `caracteristicaNome` varchar(64) NOT NULL,
  `caracteristicaDescricao` text DEFAULT NULL,
  `caracteristicaDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `caracteristicaDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `caracteristicas_cargo`
--

CREATE TABLE `caracteristicas_cargo` (
  `característicaCargoId` int(5) NOT NULL,
  `cargoId` int(5) DEFAULT NULL,
  `caracteristicaId` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargos`
--

CREATE TABLE `cargos` (
  `cargoId` int(5) NOT NULL,
  `cargoNome` varchar(64) NOT NULL,
  `cargoDescricao` text DEFAULT NULL,
  `cboId` int(5) NOT NULL,
  `cargoResumo` text DEFAULT NULL,
  `escolaridadeId` int(5) NOT NULL,
  `faixaId` int(5) DEFAULT NULL,
  `nivelHierarquicoId` int(5) DEFAULT NULL,
  `cargoSupervisorId` int(5) DEFAULT NULL,
  `cargoExperiencia` text DEFAULT NULL,
  `cargoCondicoes` text DEFAULT NULL,
  `cargoComplexidade` text DEFAULT NULL,
  `cargoResponsabilidades` text DEFAULT NULL,
  `cargoDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `cargoDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargos_area`
--

CREATE TABLE `cargos_area` (
  `cargoAreaId` int(5) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `areaId` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargo_sinonimos`
--

CREATE TABLE `cargo_sinonimos` (
  `cargoSinonimoId` int(5) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `cargoSinonimoNome` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cbos`
--

CREATE TABLE `cbos` (
  `cboId` int(5) NOT NULL,
  `familiaCboId` int(5) NOT NULL,
  `cboCod` varchar(64) NOT NULL,
  `cboTituloOficial` varchar(255) DEFAULT NULL,
  `cboDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `cboDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `cursoId` int(5) NOT NULL,
  `cursoNome` varchar(64) NOT NULL,
  `cursoDescricao` text DEFAULT NULL,
  `cursoDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `cursoDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos_cargo`
--

CREATE TABLE `cursos_cargo` (
  `cursoCargoId` int(5) NOT NULL,
  `cursoId` int(5) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `cursoCargoObrigatorio` tinyint(1) DEFAULT NULL,
  `cursoCargoObs` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `escolaridades`
--

CREATE TABLE `escolaridades` (
  `escolaridadeId` int(5) NOT NULL,
  `escolaridadeTitulo` varchar(64) NOT NULL,
  `escolaridadeDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `escolaridadeDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `faixas_salariais`
--

CREATE TABLE `faixas_salariais` (
  `faixaId` int(5) NOT NULL,
  `faixaNivel` varchar(64) NOT NULL,
  `faixaSalarioMinimo` decimal(10,2) DEFAULT NULL,
  `faixaSalarioMaximo` decimal(10,2) DEFAULT NULL,
  `faixaDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `faixaDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `familia_cbo`
--

CREATE TABLE `familia_cbo` (
  `familiaCboId` int(5) NOT NULL,
  `familiaCboNome` varchar(64) NOT NULL,
  `familiaCboDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `familiaCboDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `habilidades`
--

CREATE TABLE `habilidades` (
  `habilidadeId` int(5) NOT NULL,
  `habilidadeTipo` enum('Hardskill','Softskill') NOT NULL,
  `habilidadeNome` varchar(64) NOT NULL,
  `habilidadeDescricao` text DEFAULT NULL,
  `habilidadeDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `habilidadeDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `habilidades_cargo`
--

CREATE TABLE `habilidades_cargo` (
  `habilidadeCargoId` int(5) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `habilidadeId` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `nivel_hierarquico`
--

CREATE TABLE `nivel_hierarquico` (
  `nivelId` int(5) NOT NULL,
  `tipoId` int(5) NOT NULL,
  `nivelOrdem` int(5) NOT NULL,
  `nivelDescricao` varchar(100) DEFAULT NULL,
  `nivelDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `nivelDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nivelAtribuicoes` text DEFAULT NULL,
  `nivelAutonomia` text DEFAULT NULL,
  `nivelQuandoUtilizar` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recursos`
--

CREATE TABLE `recursos` (
  `recursoId` int(5) NOT NULL,
  `recursoNome` varchar(64) NOT NULL,
  `recursoDescricao` text DEFAULT NULL,
  `recursoDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `recursoDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recursos_cargo`
--

CREATE TABLE `recursos_cargo` (
  `recursoCargoId` int(5) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `recursoId` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recursos_grupos`
--

CREATE TABLE `recursos_grupos` (
  `recursoGrupoId` int(5) NOT NULL,
  `recursoGrupoNome` varchar(64) NOT NULL,
  `recursoGrupoDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `recursoGrupoDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recursos_grupos_cargo`
--

CREATE TABLE `recursos_grupos_cargo` (
  `recursoGrupoCargoId` int(11) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `recursoGrupoId` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recurso_grupo_recurso`
--

CREATE TABLE `recurso_grupo_recurso` (
  `recursoGrupoRecursoId` int(5) NOT NULL,
  `recursoGrupoId` int(5) NOT NULL,
  `recursoId` int(5) NOT NULL,
  `recursoGrupoRecursoDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `recursoGrupoRecuraoDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `riscos`
--

CREATE TABLE `riscos` (
  `riscoId` int(5) NOT NULL,
  `riscoNome` enum('Físico','Químico','Ergonômico','Psicossocial','Acidental','Biológico') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `riscos_cargo`
--

CREATE TABLE `riscos_cargo` (
  `riscoCargoId` int(5) NOT NULL,
  `riscoId` int(5) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `riscoDescricao` varchar(255) DEFAULT NULL,
  `riscoDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `riscoDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_hierarquia`
--

CREATE TABLE `tipo_hierarquia` (
  `tipoId` int(5) NOT NULL,
  `tipoNome` varchar(64) NOT NULL,
  `tipoDescricao` varchar(255) DEFAULT NULL,
  `tipoDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipoDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `usuarioId` int(5) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `dataCadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `areas_atuacao`
--
ALTER TABLE `areas_atuacao`
  ADD PRIMARY KEY (`areaId`),
  ADD UNIQUE KEY `areaNome` (`areaNome`),
  ADD KEY `fk_area_pai` (`areaPaiId`);

--
-- Índices de tabela `caracteristicas`
--
ALTER TABLE `caracteristicas`
  ADD PRIMARY KEY (`caracteristicaId`);

--
-- Índices de tabela `caracteristicas_cargo`
--
ALTER TABLE `caracteristicas_cargo`
  ADD PRIMARY KEY (`característicaCargoId`),
  ADD KEY `fk_caracteristicas_cargo_caracteristica` (`caracteristicaId`),
  ADD KEY `fk_caracteristicas_cargo_cargo` (`cargoId`);

--
-- Índices de tabela `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`cargoId`),
  ADD KEY `fk_cargos_cbo` (`cboId`),
  ADD KEY `fk_cargos_escolaridade` (`escolaridadeId`),
  ADD KEY `fk_cargos_faixa_salario` (`faixaId`),
  ADD KEY `fk_cargos_nivel_hierarquico` (`nivelHierarquicoId`),
  ADD KEY `fk_cargos_supervisor` (`cargoSupervisorId`);

--
-- Índices de tabela `cargos_area`
--
ALTER TABLE `cargos_area`
  ADD PRIMARY KEY (`cargoAreaId`),
  ADD UNIQUE KEY `uk_cargo_area` (`cargoId`,`areaId`),
  ADD KEY `fk_cargos_area_cargo` (`cargoId`),
  ADD KEY `fk_cargos_area_area` (`areaId`);

--
-- Índices de tabela `cargo_sinonimos`
--
ALTER TABLE `cargo_sinonimos`
  ADD PRIMARY KEY (`cargoSinonimoId`),
  ADD KEY `fk_sinonimos_cargo` (`cargoId`);

--
-- Índices de tabela `cbos`
--
ALTER TABLE `cbos`
  ADD PRIMARY KEY (`cboId`),
  ADD KEY `fk_cbos_familia_cbo` (`familiaCboId`);

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`cursoId`);

--
-- Índices de tabela `cursos_cargo`
--
ALTER TABLE `cursos_cargo`
  ADD PRIMARY KEY (`cursoCargoId`),
  ADD KEY `fk_cursos_cargo_curso` (`cursoId`),
  ADD KEY `fk_cursos_cargo_cargo` (`cargoId`);

--
-- Índices de tabela `escolaridades`
--
ALTER TABLE `escolaridades`
  ADD PRIMARY KEY (`escolaridadeId`);

--
-- Índices de tabela `faixas_salariais`
--
ALTER TABLE `faixas_salariais`
  ADD PRIMARY KEY (`faixaId`),
  ADD UNIQUE KEY `faixaNivel` (`faixaNivel`);

--
-- Índices de tabela `familia_cbo`
--
ALTER TABLE `familia_cbo`
  ADD PRIMARY KEY (`familiaCboId`);

--
-- Índices de tabela `habilidades`
--
ALTER TABLE `habilidades`
  ADD PRIMARY KEY (`habilidadeId`);

--
-- Índices de tabela `habilidades_cargo`
--
ALTER TABLE `habilidades_cargo`
  ADD PRIMARY KEY (`habilidadeCargoId`),
  ADD KEY `fk_habilidades_cargo_habilidade` (`habilidadeId`),
  ADD KEY `fk_habilidades_cargo_cargo` (`cargoId`);

--
-- Índices de tabela `nivel_hierarquico`
--
ALTER TABLE `nivel_hierarquico`
  ADD PRIMARY KEY (`nivelId`),
  ADD KEY `fk_nivel_tipo` (`tipoId`);

--
-- Índices de tabela `recursos`
--
ALTER TABLE `recursos`
  ADD PRIMARY KEY (`recursoId`);

--
-- Índices de tabela `recursos_cargo`
--
ALTER TABLE `recursos_cargo`
  ADD PRIMARY KEY (`recursoCargoId`),
  ADD KEY `fk_recursos_cargo_recurso` (`recursoId`),
  ADD KEY `fk_recursos_cargo_cargo` (`cargoId`);

--
-- Índices de tabela `recursos_grupos`
--
ALTER TABLE `recursos_grupos`
  ADD PRIMARY KEY (`recursoGrupoId`);

--
-- Índices de tabela `recursos_grupos_cargo`
--
ALTER TABLE `recursos_grupos_cargo`
  ADD PRIMARY KEY (`recursoGrupoCargoId`),
  ADD UNIQUE KEY `uk_cargo_grupo` (`cargoId`,`recursoGrupoId`),
  ADD KEY `fk_rgc_grupo` (`recursoGrupoId`);

--
-- Índices de tabela `recurso_grupo_recurso`
--
ALTER TABLE `recurso_grupo_recurso`
  ADD PRIMARY KEY (`recursoGrupoRecursoId`),
  ADD KEY `fk_recurso_grupo_recurso_grupo` (`recursoGrupoId`),
  ADD KEY `fk_recurso_grupo_recurso_recurso` (`recursoId`);

--
-- Índices de tabela `riscos`
--
ALTER TABLE `riscos`
  ADD PRIMARY KEY (`riscoId`);

--
-- Índices de tabela `riscos_cargo`
--
ALTER TABLE `riscos_cargo`
  ADD PRIMARY KEY (`riscoCargoId`),
  ADD KEY `fk_riscos_cargo_risco` (`riscoId`),
  ADD KEY `fk_riscos_cargo_cargo` (`cargoId`);

--
-- Índices de tabela `tipo_hierarquia`
--
ALTER TABLE `tipo_hierarquia`
  ADD PRIMARY KEY (`tipoId`),
  ADD UNIQUE KEY `tipoNome` (`tipoNome`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`usuarioId`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `areas_atuacao`
--
ALTER TABLE `areas_atuacao`
  MODIFY `areaId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `caracteristicas`
--
ALTER TABLE `caracteristicas`
  MODIFY `caracteristicaId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `caracteristicas_cargo`
--
ALTER TABLE `caracteristicas_cargo`
  MODIFY `característicaCargoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cargos`
--
ALTER TABLE `cargos`
  MODIFY `cargoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cargos_area`
--
ALTER TABLE `cargos_area`
  MODIFY `cargoAreaId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cargo_sinonimos`
--
ALTER TABLE `cargo_sinonimos`
  MODIFY `cargoSinonimoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cbos`
--
ALTER TABLE `cbos`
  MODIFY `cboId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `cursoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos_cargo`
--
ALTER TABLE `cursos_cargo`
  MODIFY `cursoCargoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `escolaridades`
--
ALTER TABLE `escolaridades`
  MODIFY `escolaridadeId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `faixas_salariais`
--
ALTER TABLE `faixas_salariais`
  MODIFY `faixaId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `familia_cbo`
--
ALTER TABLE `familia_cbo`
  MODIFY `familiaCboId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `habilidades`
--
ALTER TABLE `habilidades`
  MODIFY `habilidadeId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `habilidades_cargo`
--
ALTER TABLE `habilidades_cargo`
  MODIFY `habilidadeCargoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `nivel_hierarquico`
--
ALTER TABLE `nivel_hierarquico`
  MODIFY `nivelId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recursos`
--
ALTER TABLE `recursos`
  MODIFY `recursoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recursos_cargo`
--
ALTER TABLE `recursos_cargo`
  MODIFY `recursoCargoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recursos_grupos`
--
ALTER TABLE `recursos_grupos`
  MODIFY `recursoGrupoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recursos_grupos_cargo`
--
ALTER TABLE `recursos_grupos_cargo`
  MODIFY `recursoGrupoCargoId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recurso_grupo_recurso`
--
ALTER TABLE `recurso_grupo_recurso`
  MODIFY `recursoGrupoRecursoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `riscos`
--
ALTER TABLE `riscos`
  MODIFY `riscoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `riscos_cargo`
--
ALTER TABLE `riscos_cargo`
  MODIFY `riscoCargoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tipo_hierarquia`
--
ALTER TABLE `tipo_hierarquia`
  MODIFY `tipoId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `usuarioId` int(5) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `areas_atuacao`
--
ALTER TABLE `areas_atuacao`
  ADD CONSTRAINT `fk_area_pai` FOREIGN KEY (`areaPaiId`) REFERENCES `areas_atuacao` (`areaId`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `caracteristicas_cargo`
--
ALTER TABLE `caracteristicas_cargo`
  ADD CONSTRAINT `fk_caracteristicas_cargo_caracteristica` FOREIGN KEY (`caracteristicaId`) REFERENCES `caracteristicas` (`caracteristicaId`),
  ADD CONSTRAINT `fk_caracteristicas_cargo_cargo` FOREIGN KEY (`cargoId`) REFERENCES `cargos` (`cargoId`);

--
-- Restrições para tabelas `cargos`
--
ALTER TABLE `cargos`
  ADD CONSTRAINT `fk_cargos_cbo` FOREIGN KEY (`cboId`) REFERENCES `cbos` (`cboId`),
  ADD CONSTRAINT `fk_cargos_escolaridade` FOREIGN KEY (`escolaridadeId`) REFERENCES `escolaridades` (`escolaridadeId`),
  ADD CONSTRAINT `fk_cargos_faixa_salario` FOREIGN KEY (`faixaId`) REFERENCES `faixas_salariais` (`faixaId`),
  ADD CONSTRAINT `fk_cargos_nivel_hierarquico` FOREIGN KEY (`nivelHierarquicoId`) REFERENCES `nivel_hierarquico` (`nivelId`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cargos_supervisor` FOREIGN KEY (`cargoSupervisorId`) REFERENCES `cargos` (`cargoId`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `cargos_area`
--
ALTER TABLE `cargos_area`
  ADD CONSTRAINT `fk_cargos_area_area` FOREIGN KEY (`areaId`) REFERENCES `areas_atuacao` (`areaId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cargos_area_cargo` FOREIGN KEY (`cargoId`) REFERENCES `cargos` (`cargoId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `cargo_sinonimos`
--
ALTER TABLE `cargo_sinonimos`
  ADD CONSTRAINT `fk_sinonimos_cargo` FOREIGN KEY (`cargoId`) REFERENCES `cargos` (`cargoId`);

--
-- Restrições para tabelas `cbos`
--
ALTER TABLE `cbos`
  ADD CONSTRAINT `fk_cbos_familia_cbo` FOREIGN KEY (`familiaCboId`) REFERENCES `familia_cbo` (`familiaCboId`);

--
-- Restrições para tabelas `cursos_cargo`
--
ALTER TABLE `cursos_cargo`
  ADD CONSTRAINT `fk_cursos_cargo_cargo` FOREIGN KEY (`cargoId`) REFERENCES `cargos` (`cargoId`),
  ADD CONSTRAINT `fk_cursos_cargo_curso` FOREIGN KEY (`cursoId`) REFERENCES `cursos` (`cursoId`);

--
-- Restrições para tabelas `habilidades_cargo`
--
ALTER TABLE `habilidades_cargo`
  ADD CONSTRAINT `fk_habilidades_cargo_cargo` FOREIGN KEY (`cargoId`) REFERENCES `cargos` (`cargoId`),
  ADD CONSTRAINT `fk_habilidades_cargo_habilidade` FOREIGN KEY (`habilidadeId`) REFERENCES `habilidades` (`habilidadeId`);

--
-- Restrições para tabelas `nivel_hierarquico`
--
ALTER TABLE `nivel_hierarquico`
  ADD CONSTRAINT `fk_nivel_tipo` FOREIGN KEY (`tipoId`) REFERENCES `tipo_hierarquia` (`tipoId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `recursos_cargo`
--
ALTER TABLE `recursos_cargo`
  ADD CONSTRAINT `fk_recursos_cargo_cargo` FOREIGN KEY (`cargoId`) REFERENCES `cargos` (`cargoId`),
  ADD CONSTRAINT `fk_recursos_cargo_recurso` FOREIGN KEY (`recursoId`) REFERENCES `recursos` (`recursoId`);

--
-- Restrições para tabelas `recursos_grupos_cargo`
--
ALTER TABLE `recursos_grupos_cargo`
  ADD CONSTRAINT `fk_rgc_cargo` FOREIGN KEY (`cargoId`) REFERENCES `cargos` (`cargoId`),
  ADD CONSTRAINT `fk_rgc_grupo` FOREIGN KEY (`recursoGrupoId`) REFERENCES `recursos_grupos` (`recursoGrupoId`);

--
-- Restrições para tabelas `recurso_grupo_recurso`
--
ALTER TABLE `recurso_grupo_recurso`
  ADD CONSTRAINT `fk_recurso_grupo_recurso_grupo` FOREIGN KEY (`recursoGrupoId`) REFERENCES `recursos_grupos` (`recursoGrupoId`),
  ADD CONSTRAINT `fk_recurso_grupo_recurso_recurso` FOREIGN KEY (`recursoId`) REFERENCES `recursos` (`recursoId`);

--
-- Restrições para tabelas `riscos_cargo`
--
ALTER TABLE `riscos_cargo`
  ADD CONSTRAINT `fk_riscos_cargo_cargo` FOREIGN KEY (`cargoId`) REFERENCES `cargos` (`cargoId`),
  ADD CONSTRAINT `fk_riscos_cargo_risco` FOREIGN KEY (`riscoId`) REFERENCES `riscos` (`riscoId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
