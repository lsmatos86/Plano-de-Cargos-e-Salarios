-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/05/2026 às 14:33
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

--
-- Despejando dados para a tabela `areas_atuacao`
--

INSERT INTO `areas_atuacao` (`areaId`, `areaPaiId`, `areaNome`, `areaDescricao`, `areaDataCadastro`, `areaDataAtualizacao`) VALUES
(1, NULL, 'ADMINISTRATIVO', '', '2025-10-31 12:11:13', '2025-10-31 12:11:13'),
(2, NULL, 'PACKING', '', '2025-10-31 12:11:19', '2025-10-31 12:11:19'),
(3, NULL, 'CAMPO', '', '2025-10-31 12:11:30', '2025-10-31 12:11:30'),
(4, 1, 'FINANCEIRO', '', '2025-10-31 12:11:46', '2025-10-31 12:11:46'),
(5, 1, 'CONTABILIDADE', '', '2025-10-31 12:12:07', '2025-10-31 12:12:07'),
(6, 1, 'CERTIFICAÇÃO', '', '2025-10-31 12:12:19', '2025-10-31 12:12:19'),
(7, 1, 'EXPORTAÇÃO', '', '2025-10-31 12:12:25', '2025-10-31 12:12:25'),
(8, 1, 'FISCAL', '', '2025-10-31 12:12:30', '2025-10-31 12:12:30'),
(9, 1, 'COMPRAS', '', '2025-10-31 12:12:40', '2025-10-31 12:12:40'),
(10, 1, 'RECURSOS HUMANOS', '', '2025-10-31 12:12:56', '2025-10-31 12:12:56'),
(11, 1, 'T.I.', '', '2025-10-31 12:13:05', '2025-10-31 12:13:05'),
(12, 10, 'SEGURANÇA E SAÚDE', '', '2025-10-31 12:14:03', '2025-10-31 12:14:03'),
(13, 10, 'DEPARTAMENTO PESSOAL', '', '2025-10-31 12:14:20', '2025-10-31 12:14:20'),
(14, 22, 'PORTARIA', '', '2025-10-31 12:14:30', '2025-10-31 12:24:07'),
(15, 17, 'EMBALAGEM', '', '2025-10-31 12:14:47', '2025-10-31 12:16:41'),
(16, 18, 'PRÉ-SELEÇÃO', '', '2025-10-31 12:15:11', '2025-10-31 12:17:38'),
(17, 2, 'MERCADO EXTERNO', '', '2025-10-31 12:15:27', '2025-10-31 12:16:08'),
(18, 2, 'MERCADO INTERNO', '', '2025-10-31 12:16:32', '2025-10-31 12:16:32'),
(19, 17, 'CONTROLE DE QUALIDADE', '', '2025-10-31 12:17:08', '2025-10-31 12:17:08'),
(20, 39, 'IRRIGAÇÃO', '', '2025-10-31 12:22:01', '2025-10-31 13:35:59'),
(21, 39, 'PODA E ROÇAGEM', '', '2025-10-31 12:22:40', '2025-10-31 14:02:26'),
(22, 1, 'SERVIÇOS GERAIS', '', '2025-10-31 12:23:39', '2025-10-31 12:23:39'),
(23, 22, 'OBRAS E MANUTENÇÃO', '', '2025-10-31 12:23:58', '2025-10-31 12:23:58'),
(24, 22, 'REFEITÓRIO', '', '2025-10-31 12:24:46', '2025-10-31 12:24:46'),
(25, 22, 'TRANSPORTES', '', '2025-10-31 12:25:23', '2025-10-31 12:25:23'),
(26, 1, 'ELÉTRICA', '', '2025-10-31 12:26:09', '2025-10-31 12:26:09'),
(27, NULL, 'VIVEIRO', '', '2025-10-31 12:26:28', '2025-10-31 13:16:26'),
(28, 39, 'FERTIRRIGAÇÃO E PULVERIZAÇÃO', '', '2025-10-31 12:26:50', '2025-10-31 13:35:50'),
(29, 39, 'OFICINA', '', '2025-10-31 12:29:44', '2025-10-31 13:35:26'),
(30, 33, 'MONITORAMENTO DE PRAGAS', '', '2025-10-31 12:30:19', '2025-10-31 12:31:32'),
(31, 29, 'ABASTECIMENTO E LUBRIFICAÇÃO', '', '2025-10-31 12:30:53', '2025-10-31 12:30:53'),
(32, 39, 'COLHEITA', '', '2025-10-31 12:31:15', '2025-10-31 13:35:41'),
(33, 39, 'SERVIÇOS GERAIS DE CAMPO', '', '2025-10-31 12:31:22', '2025-10-31 13:36:17'),
(34, 39, 'MÁQUINAS E IMPLEMENTOS', '', '2025-10-31 12:32:27', '2025-10-31 13:36:38'),
(35, 39, 'ALMOXARIFADO', '', '2025-10-31 12:33:55', '2025-10-31 13:35:32'),
(36, 39, 'LABORATÓRIO DE CAMPO', '', '2025-10-31 12:34:34', '2025-10-31 13:36:31'),
(37, 33, 'COMPOSTAGEM', '', '2025-10-31 12:34:59', '2025-10-31 12:34:59'),
(38, 33, 'LIMPEZA DE CAMPO', '', '2025-10-31 12:35:14', '2025-10-31 12:35:14'),
(39, 3, 'SUBCAMPO 01', '', '2025-10-31 13:32:26', '2025-10-31 13:32:26'),
(40, 3, 'SUBCAMPO 02', '', '2025-10-31 13:32:36', '2025-10-31 13:32:36'),
(41, NULL, 'Mecânica', NULL, '2026-03-10 14:56:42', '2026-03-10 14:56:42'),
(54, NULL, 'Orgânico', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `audit_log`
--

CREATE TABLE `audit_log` (
  `logId` int(11) NOT NULL,
  `usuarioId` int(11) DEFAULT NULL COMMENT 'ID do usuário (da tabela usuarios)',
  `nomeUsuario` varchar(100) DEFAULT NULL COMMENT 'Nome do usuário (para referência rápida)',
  `acao` varchar(50) NOT NULL COMMENT 'Ex: CREATE, UPDATE, DELETE, LOGIN_SUCCESS, LOGIN_FAIL',
  `nomeTabela` varchar(100) DEFAULT NULL COMMENT 'Tabela afetada (ex: cargos, usuarios)',
  `idRegistro` int(11) DEFAULT NULL COMMENT 'ID do registro afetado',
  `dadosJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Dados antigos ou novos (JSON)' CHECK (json_valid(`dadosJson`)),
  `dataHora` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `audit_log`
--

INSERT INTO `audit_log` (`logId`, `usuarioId`, `nomeUsuario`, `acao`, `nomeTabela`, `idRegistro`, `dadosJson`, `dataHora`) VALUES
(1, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-01 18:30:22'),
(2, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-01 18:30:36'),
(3, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-01 19:07:16'),
(4, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-01 19:07:16'),
(5, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-01 19:07:16'),
(6, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-01 19:15:10'),
(7, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-02 01:15:42'),
(8, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-02 10:35:43'),
(9, 1, 'Leandro Matos', 'UPDATE', 'usuarios', 2, '{\"usuarioId\":\"2\",\"nome\":\"Administrador ITACITRUS\",\"email\":\"admin@itacitrus.com.br\",\"roleIds\":[\"1\"],\"ativo\":\"on\"}', '2025-11-02 10:36:22'),
(10, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-02 11:05:59'),
(11, 2, 'Administrador ITACITRUS', 'LOGIN_SUCCESS', 'usuarios', 2, NULL, '2025-11-02 11:15:16'),
(12, 2, 'Administrador ITACITRUS', 'LOGOUT', 'usuarios', 2, NULL, '2025-11-02 12:03:06'),
(13, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-06 19:14:28'),
(14, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-06 22:28:41'),
(15, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-06 22:28:53'),
(16, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-07 07:56:57'),
(17, 1, 'Leandro Matos', 'UPDATE', 'usuarios', 2, '{\"usuarioId\":\"2\",\"nome\":\"Administrador ITACITRUS\",\"email\":\"admin@itacitrus.com.br\",\"roleIds\":[\"1\"],\"ativo\":\"1\"}', '2025-11-07 08:42:20'),
(18, 1, 'Leandro Matos', 'UPDATE', 'roles', 1, '{\"roleId\":\"1\",\"roleName\":\"Admin\",\"roleDescription\":\"Administrador com acesso total ao sistema.\",\"permissionIds\":[\"6\",\"5\",\"9\",\"7\",\"3\",\"2\",\"8\",\"4\",\"1\"]}', '2025-11-07 08:49:14'),
(19, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-07 08:58:49'),
(20, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-07 08:59:03'),
(21, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-07 09:18:36'),
(22, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-07 09:18:56'),
(23, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-07 09:25:00'),
(24, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-07 09:25:09'),
(25, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-07 09:39:57'),
(26, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-07 09:40:11'),
(27, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-07 09:47:41'),
(28, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-07 09:47:52'),
(29, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-07 09:58:15'),
(30, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-07 09:58:28'),
(31, 1, 'Leandro Matos', 'CREATE', 'cursos', 33, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Manejo de Solo\"}', '2025-11-07 12:01:10'),
(32, 1, 'Leandro Matos', 'UPDATE', 'roles', 1, '{\"roleId\":\"1\",\"roleName\":\"Admin\",\"roleDescription\":\"Administrador com acesso total ao sistema.\",\"permissionIds\":[\"6\",\"5\",\"11\",\"9\",\"7\",\"3\",\"2\",\"8\",\"4\",\"1\"]}', '2025-11-07 12:16:57'),
(33, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2025-11-07 12:16:59'),
(34, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-11-07 12:17:19'),
(35, 1, 'Leandro Matos', 'CREATE', 'cursos', 34, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Elétrica de Veículos\"}', '2025-11-07 13:31:46'),
(36, 1, 'Leandro Matos', 'CREATE', 'cursos', 35, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Soldagem\"}', '2025-11-07 13:46:04'),
(37, 1, 'Leandro Matos', 'UPDATE', 'cursos', 35, '{\"action\":\"save\",\"cursoId\":\"35\",\"cursoNome\":\"Soldagem MIG\\/TIG em estruturas metálicas\"}', '2025-11-07 13:47:18'),
(38, 1, 'Leandro Matos', 'CREATE', 'cursos', 36, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Operador de Motoniveladora\"}', '2025-11-07 14:13:12'),
(39, 1, 'Leandro Matos', 'CREATE', 'cursos', 37, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Operador de Retroescavadeira\"}', '2025-11-07 14:13:23'),
(40, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2025-12-16 15:54:25'),
(41, NULL, 'System', 'LOGIN_FAIL', 'usuarios', NULL, '{\"motivo\":\"Credenciais inválidas\"}', '2026-02-27 20:53:15'),
(42, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2026-02-27 20:53:23'),
(43, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2026-02-27 21:25:51'),
(44, 1, 'Leandro Matos', 'UPDATE', 'roles', 1, '{\"roleId\":\"1\",\"roleName\":\"Admin\",\"roleDescription\":\"Administrador com acesso total ao sistema.\",\"permissionIds\":[\"6\",\"5\",\"11\",\"9\",\"7\",\"3\",\"2\",\"8\",\"4\",\"1\"]}', '2026-02-27 22:36:07'),
(45, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2026-02-27 22:36:09'),
(46, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2026-02-27 22:36:33'),
(47, 1, 'Leandro Matos', 'UPDATE', 'roles', 1, '{\"roleId\":\"1\",\"roleName\":\"Admin\",\"roleDescription\":\"Administrador com acesso total ao sistema.\",\"permissionIds\":[\"6\",\"12\",\"5\",\"11\",\"9\",\"7\",\"3\",\"2\",\"8\",\"4\",\"1\"]}', '2026-02-27 22:40:40'),
(48, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 2, '{\"action\":\"update\",\"escolaridadeId\":\"2\",\"escolaridadeTitulo\":\"Ensino Fundamental Completo\",\"peso_pontuacao\":\"10\"}', '2026-02-27 22:40:51'),
(49, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 1, '{\"action\":\"update\",\"escolaridadeId\":\"1\",\"escolaridadeTitulo\":\"Ensino Fundamental Incompleto\",\"peso_pontuacao\":\"20\"}', '2026-02-27 22:40:58'),
(50, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 1, '{\"action\":\"update\",\"escolaridadeId\":\"1\",\"escolaridadeTitulo\":\"Ensino Fundamental Incompleto\",\"peso_pontuacao\":\"10\"}', '2026-02-27 22:41:05'),
(51, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 2, '{\"action\":\"update\",\"escolaridadeId\":\"2\",\"escolaridadeTitulo\":\"Ensino Fundamental Completo\",\"peso_pontuacao\":\"20\"}', '2026-02-27 22:41:10'),
(52, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 9, '{\"action\":\"update\",\"escolaridadeId\":\"9\",\"escolaridadeTitulo\":\"Pós Graduação - Doutorado\",\"peso_pontuacao\":\"90\"}', '2026-02-27 22:41:48'),
(53, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 8, '{\"action\":\"update\",\"escolaridadeId\":\"8\",\"escolaridadeTitulo\":\"Pós Graduação - Mestrado\",\"peso_pontuacao\":\"80\"}', '2026-02-27 22:42:04'),
(54, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 7, '{\"action\":\"update\",\"escolaridadeId\":\"7\",\"escolaridadeTitulo\":\"Pós Graduação - MBA\",\"peso_pontuacao\":\"80\"}', '2026-02-27 22:42:14'),
(55, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 6, '{\"action\":\"update\",\"escolaridadeId\":\"6\",\"escolaridadeTitulo\":\"Pós Graduação - Especialização\",\"peso_pontuacao\":\"70\"}', '2026-02-27 22:42:19'),
(56, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 5, '{\"action\":\"update\",\"escolaridadeId\":\"5\",\"escolaridadeTitulo\":\"Ensino Superior Completo\",\"peso_pontuacao\":\"60\"}', '2026-02-27 22:42:27'),
(57, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 4, '{\"action\":\"update\",\"escolaridadeId\":\"4\",\"escolaridadeTitulo\":\"Ensino Médio Completo com Técnico\\/Superior\",\"peso_pontuacao\":\"50\"}', '2026-02-27 22:42:46'),
(58, 1, 'Leandro Matos', 'UPDATE', 'escolaridades', 3, '{\"action\":\"update\",\"escolaridadeId\":\"3\",\"escolaridadeTitulo\":\"Ensino Médio Completo\",\"peso_pontuacao\":\"40\"}', '2026-02-27 22:42:57'),
(59, 1, 'Leandro Matos', 'CREATE', 'escolaridades', 10, '{\"action\":\"insert\",\"escolaridadeId\":\"\",\"escolaridadeTitulo\":\"Ensino Médio Incompleto\",\"peso_pontuacao\":\"30\"}', '2026-02-27 22:43:41'),
(60, NULL, 'System', 'LOGIN_FAIL', 'usuarios', NULL, '{\"motivo\":\"Credenciais inválidas\"}', '2026-03-13 12:48:24'),
(61, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2026-03-13 12:48:36'),
(62, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2026-03-13 13:17:20'),
(63, 1, 'Leandro Matos', 'DELETE', 'escolaridades', 15, '{\"deletedId\":15}', '2026-03-13 13:24:51'),
(64, 1, 'Leandro Matos', 'DELETE', 'escolaridades', 18, '{\"deletedId\":18}', '2026-03-13 13:24:55'),
(65, 1, 'Leandro Matos', 'DELETE', 'escolaridades', 19, '{\"deletedId\":19}', '2026-03-13 13:25:00'),
(66, 1, 'Leandro Matos', 'DELETE', 'escolaridades', 13, '{\"deletedId\":13}', '2026-03-13 13:25:02'),
(67, 1, 'Leandro Matos', 'DELETE', 'escolaridades', 16, '{\"deletedId\":16}', '2026-03-13 13:25:05'),
(68, 1, 'Leandro Matos', 'DELETE', 'escolaridades', 12, '{\"deletedId\":12}', '2026-03-13 13:25:09'),
(69, 1, 'Leandro Matos', 'DELETE', 'escolaridades', 11, '{\"deletedId\":11}', '2026-03-13 13:25:16'),
(70, 1, 'Leandro Matos', 'DELETE', 'escolaridades', 17, '{\"deletedId\":17}', '2026-03-13 13:25:21'),
(71, 1, 'Leandro Matos', 'DELETE', 'escolaridades', 14, '{\"deletedId\":14}', '2026-03-13 13:25:29'),
(72, 1, 'Leandro Matos', 'CREATE', 'cursos', 38, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Hidráulica e Irrigação\"}', '2026-03-13 13:37:06'),
(73, 1, 'Leandro Matos', 'CREATE', 'cursos', 39, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Fisiologia Vegetal\"}', '2026-03-13 13:37:58'),
(74, 1, 'Leandro Matos', 'CREATE', 'cursos', 40, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"NR12 - Segurança em Máquinas e Equipamentos\"}', '2026-03-13 13:51:30'),
(75, 1, 'Leandro Matos', 'UPDATE', 'cursos', 40, '{\"action\":\"save\",\"cursoId\":\"40\",\"cursoNome\":\"NR12 (Segurança em Máquinas e Equipamentos)\"}', '2026-03-13 13:51:43'),
(76, 1, 'Leandro Matos', 'UPDATE', 'cursos', 40, '{\"action\":\"save\",\"cursoId\":\"40\",\"cursoNome\":\"NR-12 (Segurança em Máquinas e Equipamentos)\"}', '2026-03-13 13:52:13'),
(77, 1, 'Leandro Matos', 'CREATE', 'cursos', 41, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Mecânica Básica\"}', '2026-03-13 13:52:47'),
(78, 1, 'Leandro Matos', 'CREATE', 'cursos', 42, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Tratorista\"}', '2026-03-13 14:38:21'),
(79, 1, 'Leandro Matos', 'CREATE', 'cbos', 620141, '{\"form_type\":\"cbo\",\"action\":\"insert\",\"cboId\":\"\",\"familiaCboId\":\"1\",\"cboCod\":\"6133-05\",\"cboTituloOficial\":\"Apicultor\"}', '2026-03-13 14:45:49'),
(80, 1, 'Leandro Matos', 'DELETE', 'cbos', 620131, '{\"deletedId\":620131}', '2026-03-13 14:46:01'),
(81, 1, 'Leandro Matos', 'DELETE', 'cbos', 620139, '{\"deletedId\":620139}', '2026-03-13 14:46:04'),
(82, 1, 'Leandro Matos', 'DELETE', 'cbos', 620123, '{\"deletedId\":620123}', '2026-03-13 14:46:06'),
(83, 1, 'Leandro Matos', 'DELETE', 'cbos', 620116, '{\"deletedId\":620116}', '2026-03-13 14:46:11'),
(84, 1, 'Leandro Matos', 'CREATE', 'habilidades', 159, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Domínio no uso do fumegador e ferramentas de maneio\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-03-13 14:54:47'),
(85, 1, 'Leandro Matos', 'CREATE', 'habilidades', 160, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Técnicas de captura de enxames e troca de rainhas.\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-03-13 14:54:58'),
(86, 1, 'Leandro Matos', 'CREATE', 'habilidades', 161, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Conhecimento sobre o comportamento das abelhas face ao uso de defensivos agrícolas (compatibilidade)\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-03-13 14:55:09'),
(87, 1, 'Leandro Matos', 'CREATE', 'habilidades', 162, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Manutenção de caixas, quadros e ceras.\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-03-13 14:55:15'),
(88, 1, 'Leandro Matos', 'CREATE', 'cursos', 43, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Apicultura\"}', '2026-03-13 15:08:49'),
(89, 1, 'Leandro Matos', 'UPDATE', 'cursos', 43, '{\"action\":\"save\",\"cursoId\":\"43\",\"cursoNome\":\"Manejo Produtivo na Apicultura\"}', '2026-03-13 15:10:01'),
(90, 1, 'Leandro Matos', 'CREATE', 'cursos', 44, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"NR-6 (Equipamentos de Proteção Individual)\"}', '2026-03-13 16:19:58'),
(91, 1, 'Leandro Matos', 'UPDATE', 'cbos', 620115, '{\"form_type\":\"cbo\",\"action\":\"update\",\"cboId\":\"620115\",\"familiaCboId\":\"1\",\"cboCod\":\"7632-10\",\"cboTituloOficial\":\"Costureira de Peças Sob Encomenda\"}', '2026-03-13 16:44:55'),
(92, 1, 'Leandro Matos', 'CREATE', 'habilidades', 163, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Domínio de diferentes pontos de costura\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-03-13 16:46:33'),
(93, 1, 'Leandro Matos', 'CREATE', 'habilidades', 164, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Conhecimento de tipos de tecidos, malhas e linhas\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-03-13 16:46:55'),
(94, 1, 'Leandro Matos', 'CREATE', 'habilidades', 165, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Habilidade em máquinas industriais\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-03-13 16:47:09'),
(95, 1, 'Leandro Matos', 'CREATE', 'habilidades', 166, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Técnicas de Corte e Costura.\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-03-13 16:47:27'),
(96, 1, 'Leandro Matos', 'CREATE', 'cursos', 45, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Corte e Costura\"}', '2026-03-13 16:47:52'),
(97, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2026-03-13 17:24:17'),
(98, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2026-04-13 09:27:51'),
(99, 1, 'Leandro Matos', 'CREATE', 'cursos', 46, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Bacharelado em Administração\"}', '2026-04-13 10:29:31'),
(100, 1, 'Leandro Matos', 'UPDATE', 'cursos', 46, '{\"action\":\"save\",\"cursoId\":\"46\",\"cursoNome\":\"Bacharelado em Administração (ou áreas afins)\"}', '2026-04-13 10:30:28'),
(101, 1, 'Leandro Matos', 'CREATE', 'cbos', 620142, '{\"form_type\":\"cbo\",\"action\":\"insert\",\"cboId\":\"\",\"familiaCboId\":\"1\",\"cboCod\":\"1210-10\",\"cboTituloOficial\":\"Diretor Geral\"}', '2026-04-13 10:45:27'),
(102, 1, 'Leandro Matos', 'CREATE', 'habilidades', 167, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Solicitação Orçamento\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 10:57:03'),
(103, 1, 'Leandro Matos', 'CREATE', 'cursos', 47, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Design Gráfico\"}', '2026-04-13 10:58:08'),
(104, 1, 'Leandro Matos', 'CREATE', 'cursos', 48, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"CNH - Carteira Nacional de Habilitação\"}', '2026-04-13 11:20:25'),
(105, 1, 'Leandro Matos', 'CREATE', 'cursos', 49, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Técnico em Enfermagem\"}', '2026-04-13 11:41:45'),
(106, 1, 'Leandro Matos', 'UPDATE', 'cursos', 30, '{\"action\":\"save\",\"cursoId\":\"30\",\"cursoNome\":\"Técnico em Segurança do Trabalho\"}', '2026-04-13 11:41:57'),
(107, 1, 'Leandro Matos', 'CREATE', 'cursos', 50, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Enfermagem no Trabalho\"}', '2026-04-13 11:42:12'),
(108, 1, 'Leandro Matos', 'UPDATE', 'cursos', 50, '{\"action\":\"save\",\"cursoId\":\"50\",\"cursoNome\":\"Técnico em Enfermagem no Trabalho\"}', '2026-04-13 11:44:40'),
(109, 1, 'Leandro Matos', 'UPDATE', 'cbos', 45, '{\"form_type\":\"cbo\",\"action\":\"update\",\"cboId\":\"45\",\"familiaCboId\":\"1\",\"cboCod\":\"5174-15\",\"cboTituloOficial\":\"Agente de Portaria\"}', '2026-04-13 13:46:25'),
(110, 1, 'Leandro Matos', 'DELETE', 'cbos', 620135, '{\"deletedId\":620135}', '2026-04-13 13:46:31'),
(111, 1, 'Leandro Matos', 'UPDATE', 'habilidades', 108, '{\"action\":\"update\",\"habilidadeId\":\"108\",\"habilidadeNome\":\"Interpretação de Normas de Qualidade\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"ISO, HACCP, BPF, GLOBAL GAP..etc\"}', '2026-04-13 14:14:29'),
(112, 1, 'Leandro Matos', 'CREATE', 'escolaridades', 20, '{\"action\":\"insert\",\"escolaridadeId\":\"\",\"escolaridadeTitulo\":\"Ensino Superior Incompleto\",\"peso_pontuacao\":\"50\"}', '2026-04-13 14:42:41'),
(113, 1, 'Leandro Matos', 'CREATE', 'cursos', 51, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Gestão de Frota\"}', '2026-04-13 14:49:51'),
(114, 1, 'Leandro Matos', 'CREATE', 'cursos', 52, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Relações Internacionais\"}', '2026-04-13 14:50:10'),
(115, 1, 'Leandro Matos', 'CREATE', 'cursos', 53, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Gestão de Terminais Portuários e Logística Internacional\"}', '2026-04-13 14:50:25'),
(116, 1, 'Leandro Matos', 'CREATE', 'habilidades', 168, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Instalação e manutenção de hardware e software\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:08:14'),
(117, 1, 'Leandro Matos', 'CREATE', 'habilidades', 169, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Configuração e manutenção de redes com e sem fio\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:08:22'),
(118, 1, 'Leandro Matos', 'CREATE', 'habilidades', 170, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Instalação e manutenção de sistemas de segurança eletrônica e CFTV\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:08:31'),
(119, 1, 'Leandro Matos', 'CREATE', 'habilidades', 171, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Diagnóstico e correção de falhas em hardware e software\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:08:39'),
(120, 1, 'Leandro Matos', 'CREATE', 'habilidades', 172, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Gestão e configuração de servidores e sistemas operacionais\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:08:49'),
(121, 1, 'Leandro Matos', 'CREATE', 'habilidades', 173, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Execução de backups e segurança de dados\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:08:59'),
(122, 1, 'Leandro Matos', 'CREATE', 'habilidades', 174, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Atendimento técnico a clientes internos e externos\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:09:08'),
(123, 1, 'Leandro Matos', 'CREATE', 'habilidades', 175, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Administração de chamados e suporte técnico\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:09:14'),
(124, 1, 'Leandro Matos', 'CREATE', 'habilidades', 176, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Planejamento e execução de manutenções preventivas e corretivas\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:09:23'),
(125, 1, 'Leandro Matos', 'CREATE', 'habilidades', 177, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Organização de cabeamento, controle térmico e disposição de equipamentos\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:09:32'),
(126, 1, 'Leandro Matos', 'CREATE', 'habilidades', 178, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Gerenciamento de incidentes, contas de usuários e permissões de acesso\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:09:43'),
(127, 1, 'Leandro Matos', 'CREATE', 'habilidades', 179, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Capacidade analítica, organização e agilidade no atendimento\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:09:50'),
(128, 1, 'Leandro Matos', 'CREATE', 'cursos', 54, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Rede de Computadores\"}', '2026-04-13 15:17:17'),
(129, 1, 'Leandro Matos', 'CREATE', 'cursos', 55, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Sistemas Operacionais (Windows Server)\"}', '2026-04-13 15:17:29'),
(130, 1, 'Leandro Matos', 'CREATE', 'cursos', 56, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"CFTV (Circuito Fechado de TV)\"}', '2026-04-13 15:18:08'),
(131, 1, 'Leandro Matos', 'CREATE', 'cursos', 57, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Manutenção de Computadores\"}', '2026-04-13 15:18:27'),
(132, 1, 'Leandro Matos', 'CREATE', 'cursos', 58, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Manutenção de Notebooks\"}', '2026-04-13 15:18:36'),
(133, 1, 'Leandro Matos', 'CREATE', 'cursos', 59, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Manutenção de Redes\"}', '2026-04-13 15:18:52'),
(134, 1, 'Leandro Matos', 'CREATE', 'cursos', 60, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Lógica de Programação\"}', '2026-04-13 15:34:58'),
(135, 1, 'Leandro Matos', 'CREATE', 'cursos', 61, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Banco de Dados (SQL)\"}', '2026-04-13 15:35:12'),
(136, 1, 'Leandro Matos', 'CREATE', 'cursos', 62, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Desenvolvimento WEB (HTML, CSS, JavaScript)\"}', '2026-04-13 15:35:38'),
(137, 1, 'Leandro Matos', 'CREATE', 'cursos', 63, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Linguagem de Programação (Python)\"}', '2026-04-13 15:36:03'),
(138, 1, 'Leandro Matos', 'UPDATE', 'cursos', 5, '{\"action\":\"save\",\"cursoId\":\"5\",\"cursoNome\":\"Informática Básica e Pacote Office (Word, Excel, Power Point)\"}', '2026-04-13 15:36:25'),
(139, 1, 'Leandro Matos', 'CREATE', 'habilidades', 180, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Desenvolvimento de interfaces gráficas e relatórios\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:39:18'),
(140, 1, 'Leandro Matos', 'CREATE', 'habilidades', 181, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Aplicação de critérios ergonômicos de navegação\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:39:30'),
(141, 1, 'Leandro Matos', 'CREATE', 'habilidades', 182, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Codificação, compilação e teste de programas e aplicativos\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:39:39'),
(142, 1, 'Leandro Matos', 'CREATE', 'habilidades', 183, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Manutenção e melhoria de sistemas ERP\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:39:46'),
(143, 1, 'Leandro Matos', 'CREATE', 'habilidades', 184, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Modelagem e administração de banco de dados\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:39:53'),
(144, 1, 'Leandro Matos', 'CREATE', 'habilidades', 185, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Documentação e homologação de sistemas\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:39:59'),
(145, 1, 'Leandro Matos', 'CREATE', 'habilidades', 186, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Coleta e análise de requisitos de usuários\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:40:10'),
(146, 1, 'Leandro Matos', 'CREATE', 'habilidades', 187, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Definição de metodologias, linguagens e ferramentas de desenvolvimento\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:40:17'),
(147, 1, 'Leandro Matos', 'CREATE', 'habilidades', 188, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"•\\t• Capacidade de raciocínio lógico, análise crítica e solução de problemas\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:40:30'),
(148, 1, 'Leandro Matos', 'CREATE', 'habilidades', 189, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Pesquisa e aplicação de novas tecnologias\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-13 15:40:38'),
(149, 1, 'Leandro Matos', 'LOGOUT', 'usuarios', 1, NULL, '2026-04-13 17:12:13'),
(150, 1, 'Leandro Matos', 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '2026-04-17 08:34:10'),
(151, 1, 'Leandro Matos', 'CREATE', 'habilidades', 190, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Cotrole de Fluxo de Pessoas e Veiculos\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-17 09:13:40'),
(152, 1, 'Leandro Matos', 'CREATE', 'habilidades', 191, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Identificação e Encaminhamento de Pessoas\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-17 09:14:00'),
(153, 1, 'Leandro Matos', 'CREATE', 'habilidades', 192, '{\"action\":\"insert\",\"habilidadeId\":\"\",\"habilidadeNome\":\"Receber e Conferir Documentos e Materiais\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-17 09:14:22'),
(154, 1, 'Leandro Matos', 'UPDATE', 'habilidades', 190, '{\"action\":\"update\",\"habilidadeId\":\"190\",\"habilidadeNome\":\"Controle de Fluxo de Pessoas e Veículos\",\"habilidadeTipo\":\"Hardskill\",\"habilidadeDescricao\":\"\"}', '2026-04-17 09:15:43'),
(155, 1, 'Leandro Matos', 'CREATE', 'cursos', 64, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"NR-11 (Transporte, Movimentação, Armazenagem e Manuseio de Materiais)\"}', '2026-04-17 10:14:59'),
(156, 1, 'Leandro Matos', 'UPDATE', 'cursos', 64, '{\"action\":\"save\",\"cursoId\":\"64\",\"cursoNome\":\"NR-11 (Transporte, Movimentação, Armazenagem e Manuseio de Materiais)\"}', '2026-04-17 10:18:41'),
(157, 1, 'Leandro Matos', 'CREATE', 'cursos', 65, '{\"action\":\"save\",\"cursoId\":\"0\",\"cursoNome\":\"Qualidade de Alimentos\"}', '2026-04-17 11:09:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `campanhas_pesquisa`
--

CREATE TABLE `campanhas_pesquisa` (
  `campanhaId` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL COMMENT 'Ex: Pesquisa de Mercado 2026',
  `data_abertura` date NOT NULL,
  `data_fechamento` date DEFAULT NULL,
  `status` enum('Aberta','Encerrada','Aplicada') DEFAULT 'Aberta',
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `campanhas_pesquisa`
--

INSERT INTO `campanhas_pesquisa` (`campanhaId`, `titulo`, `data_abertura`, `data_fechamento`, `status`, `observacoes`) VALUES
(1, 'Pesquisa Salarial 2025/2026', '2026-02-28', NULL, 'Aberta', NULL);

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

--
-- Despejando dados para a tabela `caracteristicas`
--

INSERT INTO `caracteristicas` (`caracteristicaId`, `caracteristicaNome`, `caracteristicaDescricao`, `caracteristicaDataCadastro`, `caracteristicaDataAtualizacao`) VALUES
(1, 'Caráter (Fundamentado em Princípios Éticos)', 'Fundamentado em princípios éticos, respeito, compromisso e confiança.', '2025-10-17 16:06:32', '2025-10-17 16:06:32'),
(2, 'Felicidade e Bom Humor no Ambiente de Trabalho', 'Valorização de um ambiente harmonioso e saudável; desejo de contribuir com leveza e bom humor.', '2025-10-17 16:06:32', '2025-10-17 16:06:32'),
(3, 'Ambição Saudável e Crescimento por Mérito', 'Desejo de crescer profissionalmente com base no mérito, dedicação e contribuição ao coletivo.', '2025-10-17 16:06:32', '2025-10-17 16:06:32'),
(4, 'Honestidade e Transparência nas Relações', 'Agir com transparência e retidão em todas as relações.', '2025-10-17 16:06:32', '2025-10-17 16:06:32'),
(5, 'Espiritualidade ou Senso de Propósito', 'Valorização de princípios internos (como gratidão, fé, respeito pela vida).', '2025-10-17 16:06:32', '2025-10-17 16:06:32'),
(6, 'Cuidado com a Saúde e Bem-Estar', 'Interesse por uma vida saudável e equilibrada, em linha com os valores da empresa.', '2025-10-17 16:06:32', '2025-10-17 16:06:32'),
(7, 'Alinhamento com Princípios de Comércio Justo (Fairtrade)', 'Interesse em práticas que promovem a justiça social, equidade e o desenvolvimento humano.', '2025-10-17 16:06:32', '2025-10-17 16:06:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `caracteristicas_cargo`
--

CREATE TABLE `caracteristicas_cargo` (
  `característicaCargoId` int(5) NOT NULL,
  `cargoId` int(5) DEFAULT NULL,
  `caracteristicaId` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `caracteristicas_cargo`
--

INSERT INTO `caracteristicas_cargo` (`característicaCargoId`, `cargoId`, `caracteristicaId`) VALUES
(85, 13, 1),
(86, 13, 2),
(87, 13, 3),
(88, 13, 4),
(89, 13, 5),
(90, 13, 6),
(91, 13, 7),
(127, 19, 1),
(128, 19, 2),
(129, 19, 3),
(130, 19, 4),
(131, 19, 5),
(132, 19, 6),
(133, 19, 7),
(141, 21, 1),
(142, 21, 2),
(143, 21, 3),
(144, 21, 4),
(145, 21, 5),
(146, 21, 6),
(147, 21, 7),
(148, 22, 1),
(149, 22, 2),
(150, 22, 3),
(151, 22, 4),
(152, 22, 5),
(153, 22, 6),
(154, 22, 7),
(190, 28, 1),
(191, 28, 2),
(192, 28, 3),
(193, 28, 4),
(194, 28, 5),
(195, 28, 6),
(196, 28, 7),
(204, 30, 1),
(205, 30, 2),
(206, 30, 3),
(207, 30, 4),
(208, 30, 5),
(209, 30, 6),
(210, 30, 7),
(218, 32, 1),
(219, 32, 2),
(220, 32, 3),
(221, 32, 4),
(222, 32, 5),
(223, 32, 6),
(224, 32, 7),
(253, 37, 1),
(254, 37, 2),
(255, 37, 3),
(256, 37, 4),
(257, 37, 5),
(258, 37, 6),
(259, 37, 7),
(645, 27, 1),
(646, 27, 2),
(647, 27, 3),
(648, 27, 4),
(649, 27, 5),
(650, 27, 6),
(651, 27, 7),
(736, 58, 1),
(737, 58, 2),
(738, 58, 3),
(739, 58, 4),
(740, 58, 5),
(741, 58, 6),
(742, 58, 7),
(806, 41, 1),
(807, 41, 2),
(808, 41, 3),
(809, 41, 4),
(810, 41, 5),
(811, 41, 6),
(812, 41, 7),
(890, 36, 1),
(891, 36, 2),
(892, 36, 3),
(893, 36, 4),
(894, 36, 5),
(895, 36, 6),
(896, 36, 7),
(911, 10, 1),
(912, 10, 2),
(913, 10, 3),
(914, 10, 4),
(915, 10, 5),
(916, 10, 6),
(917, 10, 7),
(946, 31, 1),
(947, 31, 2),
(948, 31, 3),
(949, 31, 4),
(950, 31, 5),
(951, 31, 6),
(952, 31, 7),
(981, 59, 1),
(982, 59, 2),
(983, 59, 3),
(984, 59, 4),
(985, 59, 5),
(986, 59, 6),
(987, 59, 7),
(988, 23, 1),
(989, 23, 2),
(990, 23, 3),
(991, 23, 4),
(992, 23, 5),
(993, 23, 6),
(994, 23, 7),
(995, 5, 1),
(996, 5, 2),
(997, 5, 3),
(998, 5, 4),
(999, 5, 5),
(1000, 5, 6),
(1001, 5, 7),
(1051, 46, 1),
(1052, 46, 2),
(1053, 46, 3),
(1054, 46, 4),
(1055, 46, 5),
(1056, 46, 6),
(1057, 46, 7),
(1177, 15, 1),
(1178, 15, 2),
(1179, 15, 3),
(1180, 15, 4),
(1181, 15, 5),
(1182, 15, 6),
(1183, 15, 7),
(1184, 39, 1),
(1185, 39, 2),
(1186, 39, 3),
(1187, 39, 4),
(1188, 39, 5),
(1189, 39, 6),
(1190, 39, 7),
(1198, 45, 1),
(1199, 45, 2),
(1200, 45, 3),
(1201, 45, 4),
(1202, 45, 5),
(1203, 45, 6),
(1204, 45, 7),
(1345, 53, 1),
(1346, 53, 2),
(1347, 53, 3),
(1348, 53, 4),
(1349, 53, 5),
(1350, 53, 6),
(1351, 53, 7),
(1387, 55, 1),
(1388, 55, 2),
(1389, 55, 3),
(1390, 55, 4),
(1391, 55, 5),
(1392, 55, 6),
(1393, 55, 7),
(1436, 3, 1),
(1437, 3, 2),
(1438, 3, 3),
(1439, 3, 4),
(1440, 3, 5),
(1441, 3, 6),
(1442, 3, 7),
(1520, 26, 1),
(1521, 26, 2),
(1522, 26, 3),
(1523, 26, 4),
(1524, 26, 5),
(1525, 26, 6),
(1526, 26, 7),
(1569, 7, 1),
(1570, 7, 2),
(1571, 7, 3),
(1572, 7, 4),
(1573, 7, 5),
(1574, 7, 6),
(1575, 7, 7),
(1653, 14, 1),
(1654, 14, 2),
(1655, 14, 3),
(1656, 14, 4),
(1657, 14, 5),
(1658, 14, 6),
(1659, 14, 7),
(1702, 60, 1),
(1703, 60, 2),
(1704, 60, 3),
(1705, 60, 4),
(1706, 60, 5),
(1707, 60, 6),
(1708, 60, 7),
(2087, 1, 1),
(2088, 1, 2),
(2089, 1, 3),
(2090, 1, 4),
(2091, 1, 5),
(2092, 1, 6),
(2093, 1, 7),
(2171, 65, 7),
(2172, 65, 3),
(2173, 65, 1),
(2174, 65, 6),
(2175, 65, 5),
(2176, 65, 2),
(2177, 65, 4),
(2213, 38, 1),
(2214, 38, 2),
(2215, 38, 3),
(2216, 38, 4),
(2217, 38, 5),
(2218, 38, 6),
(2219, 38, 7),
(2241, 54, 1),
(2242, 54, 2),
(2243, 54, 3),
(2244, 54, 4),
(2245, 54, 5),
(2246, 54, 6),
(2247, 54, 7),
(2283, 11, 1),
(2284, 11, 2),
(2285, 11, 3),
(2286, 11, 4),
(2287, 11, 5),
(2288, 11, 6),
(2289, 11, 7),
(2381, 33, 1),
(2382, 33, 2),
(2383, 33, 3),
(2384, 33, 4),
(2385, 33, 5),
(2386, 33, 6),
(2387, 33, 7),
(2458, 24, 1),
(2459, 24, 2),
(2460, 24, 3),
(2461, 24, 4),
(2462, 24, 5),
(2463, 24, 6),
(2464, 24, 7),
(2542, 52, 1),
(2543, 52, 2),
(2544, 52, 3),
(2545, 52, 4),
(2546, 52, 5),
(2547, 52, 6),
(2548, 52, 7),
(2556, 44, 1),
(2557, 44, 2),
(2558, 44, 3),
(2559, 44, 4),
(2560, 44, 5),
(2561, 44, 6),
(2562, 44, 7),
(2584, 50, 1),
(2585, 50, 2),
(2586, 50, 3),
(2587, 50, 4),
(2588, 50, 5),
(2589, 50, 6),
(2590, 50, 7),
(2668, 47, 1),
(2669, 47, 2),
(2670, 47, 3),
(2671, 47, 4),
(2672, 47, 5),
(2673, 47, 6),
(2674, 47, 7),
(2703, 8, 1),
(2704, 8, 2),
(2705, 8, 3),
(2706, 8, 4),
(2707, 8, 5),
(2708, 8, 6),
(2709, 8, 7),
(2731, 4, 1),
(2732, 4, 2),
(2733, 4, 3),
(2734, 4, 4),
(2735, 4, 5),
(2736, 4, 6),
(2737, 4, 7),
(2780, 12, 1),
(2781, 12, 2),
(2782, 12, 3),
(2783, 12, 4),
(2784, 12, 5),
(2785, 12, 6),
(2786, 12, 7),
(2836, 51, 1),
(2837, 51, 2),
(2838, 51, 3),
(2839, 51, 4),
(2840, 51, 5),
(2841, 51, 6),
(2842, 51, 7),
(2892, 43, 1),
(2893, 43, 2),
(2894, 43, 3),
(2895, 43, 4),
(2896, 43, 5),
(2897, 43, 6),
(2898, 43, 7),
(2920, 48, 1),
(2921, 48, 2),
(2922, 48, 3),
(2923, 48, 4),
(2924, 48, 5),
(2925, 48, 6),
(2926, 48, 7),
(2955, 64, 1),
(2956, 64, 2),
(2957, 64, 3),
(2958, 64, 4),
(2959, 64, 5),
(2960, 64, 6),
(2961, 64, 7),
(2983, 9, 1),
(2984, 9, 2),
(2985, 9, 3),
(2986, 9, 4),
(2987, 9, 5),
(2988, 9, 6),
(2989, 9, 7),
(3004, 62, 1),
(3005, 62, 2),
(3006, 62, 3),
(3007, 62, 4),
(3008, 62, 5),
(3009, 62, 6),
(3010, 62, 7),
(3018, 6, 1),
(3019, 6, 2),
(3020, 6, 3),
(3021, 6, 4),
(3022, 6, 5),
(3023, 6, 6),
(3024, 6, 7),
(3032, 63, 1),
(3033, 63, 2),
(3034, 63, 3),
(3035, 63, 4),
(3036, 63, 5),
(3037, 63, 6),
(3038, 63, 7),
(3053, 34, 1),
(3054, 34, 2),
(3055, 34, 3),
(3056, 34, 4),
(3057, 34, 5),
(3058, 34, 6),
(3059, 34, 7),
(3081, 61, 1),
(3082, 61, 2),
(3083, 61, 3),
(3084, 61, 4),
(3085, 61, 5),
(3086, 61, 6),
(3087, 61, 7),
(3095, 20, 1),
(3096, 20, 2),
(3097, 20, 3),
(3098, 20, 4),
(3099, 20, 5),
(3100, 20, 6),
(3101, 20, 7),
(3116, 25, 1),
(3117, 25, 2),
(3118, 25, 3),
(3119, 25, 4),
(3120, 25, 5),
(3121, 25, 6),
(3122, 25, 7),
(3130, 78, 7),
(3131, 78, 3),
(3132, 78, 1),
(3133, 78, 6),
(3134, 78, 5),
(3135, 78, 2),
(3136, 78, 4),
(3186, 42, 1),
(3187, 42, 2),
(3188, 42, 3),
(3189, 42, 4),
(3190, 42, 5),
(3191, 42, 6),
(3192, 42, 7),
(3249, 56, 1),
(3250, 56, 2),
(3251, 56, 3),
(3252, 56, 4),
(3253, 56, 5),
(3254, 56, 6),
(3255, 56, 7),
(3368, 17, 1),
(3369, 17, 2),
(3370, 17, 3),
(3371, 17, 4),
(3372, 17, 5),
(3373, 17, 6),
(3374, 17, 7),
(3375, 16, 1),
(3376, 16, 2),
(3377, 16, 3),
(3378, 16, 4),
(3379, 16, 5),
(3380, 16, 6),
(3381, 16, 7),
(3424, 40, 1),
(3425, 40, 2),
(3426, 40, 3),
(3427, 40, 4),
(3428, 40, 5),
(3429, 40, 6),
(3430, 40, 7),
(3487, 49, 1),
(3488, 49, 2),
(3489, 49, 3),
(3490, 49, 4),
(3491, 49, 5),
(3492, 49, 6),
(3493, 49, 7),
(3494, 2, 1),
(3495, 2, 2),
(3496, 2, 3),
(3497, 2, 4),
(3498, 2, 5),
(3499, 2, 6),
(3500, 2, 7),
(3543, 35, 1),
(3544, 35, 2),
(3545, 35, 3),
(3546, 35, 4),
(3547, 35, 5),
(3548, 35, 6),
(3549, 35, 7),
(3592, 18, 1),
(3593, 18, 2),
(3594, 18, 3),
(3595, 18, 4),
(3596, 18, 5),
(3597, 18, 6),
(3598, 18, 7),
(3613, 84, 1),
(3614, 84, 2),
(3615, 84, 3),
(3616, 84, 4),
(3617, 84, 5),
(3618, 84, 6),
(3619, 84, 7),
(3641, 29, 1),
(3642, 29, 2),
(3643, 29, 3),
(3644, 29, 4),
(3645, 29, 5),
(3646, 29, 6),
(3647, 29, 7);

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
  `is_revisado` tinyint(1) DEFAULT 0,
  `data_revisao` datetime DEFAULT NULL,
  `cargoComplexidade` text DEFAULT NULL,
  `cargoResponsabilidades` text DEFAULT NULL,
  `cargoDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `cargoDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tem_piso_salarial` tinyint(1) DEFAULT 0,
  `piso_valor` decimal(10,2) DEFAULT NULL,
  `piso_lei_numero` varchar(100) DEFAULT NULL,
  `piso_data_base` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cargos`
--

INSERT INTO `cargos` (`cargoId`, `cargoNome`, `cargoDescricao`, `cboId`, `cargoResumo`, `escolaridadeId`, `faixaId`, `nivelHierarquicoId`, `cargoSupervisorId`, `cargoExperiencia`, `cargoCondicoes`, `is_revisado`, `data_revisao`, `cargoComplexidade`, `cargoResponsabilidades`, `cargoDataCadastro`, `cargoDataAtualizacao`, `tem_piso_salarial`, `piso_valor`, `piso_lei_numero`, `piso_data_base`) VALUES
(1, 'ENCARREGADO DE COLHEITA', '', 1, 'Supervisiona equipes na colheita, controla produtividade e qualidade, e garante o uso de EPIs e normas de segurança.', 3, NULL, 3, NULL, 'Experiência prática em atividades de colheita é essencial.', 'Trabalha predominantemente em campo aberto, exposto a sol, chuva, poeira e variações térmicas. Exige deslocamentos frequentes, supervisão contínua da equipe, atenção a riscos operacionais e tomada de decisões sob pressão. O uso de EPIs é obrigatório.', 1, '2026-02-27 23:43:37', 'Exige domínio de técnicas agrícolas, capacidade de liderança, decisão rápida e gerenciamento de pessoas em ambiente adverso. A qualidade do trabalho impacta diretamente a produtividade, o aproveitamento da colheita e o clima organizacional.', 'Supervisionar e orientar a equipe, controlar produtividade e qualidade, acompanhar uso de EPIs, realizar planejamento de higienização, participar de capacitação, identificar falhas e realizar registros operacionais.', '2025-10-17 15:57:40', '2026-02-28 02:43:37', 0, NULL, NULL, NULL),
(2, 'SUPERVISOR DE TRATOS CULTURAIS', '', 2, 'Supervisiona e planeja tratos culturais, coordenando equipes e controlando a qualidade das operações e o uso de insumos.', 4, NULL, 4, NULL, 'Experiência mínima de 1 a 2 anos em atividades agrícolas, com vivência em liderança de equipes e conhecimento em práticas de tratos culturais, irrigação e uso de defensivos.', 'Atua majoritariamente em campo, exposto às condições climáticas e ambientais. Realiza deslocamentos frequentes, lida com variações de solo, clima e operação de máquinas. Exige uso constante de EPIs, atenção a riscos operacionais e capacidade para tomada de decisão em situações adversas.', 0, NULL, 'Exige conhecimento técnico em práticas agrícolas, habilidades de liderança e planejamento operacional. Requer tomada de decisões rápidas em campo, controle rigoroso da execução das atividades e capacidade de adaptação às condições ambientais e produtivas. Possui impacto direto sobre a qualidade e produtividade da lavoura.', 'Supervisionar e orientar equipes, distribuir tarefas e elaborar escalas, controlar o uso e consumo de insumos/defensivos, avaliar produtividade e qualidade, identificar e corrigir falhas, garantir manutenção básica dos equipamentos e preencher relatórios de campo.', '2025-10-17 15:57:40', '2026-04-17 13:50:23', 1, 2000.00, NULL, NULL),
(3, 'ALMOXARIFE', '', 3, 'Responsável pelo recebimento, conferência, armazenamento e distribuição de materiais e insumos, mantendo a organização e controle do estoque físico e sistêmico.', 3, NULL, 12, 33, 'Experiência mínima recomendada de 6 meses a 1 ano em controle de estoque, almoxarifado ou áreas correlatas.', 'Trabalho predominantemente operacional, realizado em ambientes internos e externos, com exposição a variações térmicas, movimentação de carga, esforço físico e interação com diferentes setores. Requer atenção contínua, cumprimento de metas e uso de EPIs.', 0, NULL, 'Exige conhecimento técnico-operacional, atenção constante aos processos de entrada e saída de materiais e capacidade de interação com diferentes áreas. Erros podem gerar prejuízos financeiros, atrasos na produção e comprometimento da segurança.', 'Receber, conferir e armazenar materiais, separar e distribuir insumos, codificar, endereçar e registrar movimentações, verificar prazos de validade, realizar inventários, organizar o almoxarifado e emitir requisições de reposição.', '2025-10-17 15:57:40', '2025-11-07 17:40:10', 0, NULL, NULL, NULL),
(4, 'ANALISTA DE EXPORTAÇÃO', '', 4, 'Supervisiona e coordena atividades logísticas e comerciais relacionadas à exportação de produtos. Negocia condições de compra e venda, elabora propostas comerciais, compatibiliza cronogramas de produção com contratos de venda, e assegura a elaboração e controle da documentação necessária à exportação. Interage com clientes, fornecedores e prestadores de serviço para garantir o cumprimento dos prazos, qualidade dos serviços prestados e rastreabilidade das operações.', 5, NULL, 8, NULL, '2 a 3 anos', 'Trabalho dinâmico, com grande atenção a prazos e detalhes operacionais. Pode ser realizado em escritório, home office ou in loco, exigindo acompanhamento da programação de navios, liberação de carga e logística. A comunicação com agentes internacionais exige domínio técnico e cultural.', 0, NULL, 'Trata-se de um cargo técnico e estratégico que exige domínio de comércio exterior, conhecimento em logística internacional, legislação e processos aduaneiros. Requer capacidade de decisão rápida, comunicação assertiva e atenção aos detalhes. A complexidade está no gerenciamento de múltiplas etapas e riscos associados à exportação, incluindo aspectos legais, financeiros e logísticos.', 'Negociar condições de compra e venda com clientes nacionais e internacionais. Elaborar propostas comerciais conforme especificações técnicas e contratuais. Coordenar cronogramas de produção com os prazos de exportação. Elaborar e conferir documentação de exportação. Contratar e acompanhar execução de serviços de terceiros (frete, despachante, seguro). Assegurar o recebimento de pagamentos (câmbio) e emissão de documentos fiscais. Acompanhar a execução da logística de exportação até a liberação da carga. Prestar assistência técnica-operacional ao cliente. Controlar prazos, indicadores e qualidade dos serviços prestados. Desenvolver clientes, fornecedores e rotas de exportação', '2025-10-17 15:57:40', '2026-04-13 17:37:28', 0, NULL, NULL, NULL),
(5, 'MONITOR DE PRAGAS', '', 5, 'Monitora pragas e doenças em plantações através de inspeções visuais e registro de dados, colaborando com o controle fitossanitário e executando tratos culturais.', 3, NULL, NULL, NULL, 'Experiência prática em campo é essencial. Tempo de aprendizado para desempenho pleno varia entre 6 meses a 1 ano.', 'Trabalho realizado integralmente em campo aberto, sob diferentes condições climáticas como sol intenso e chuva. O profissional percorre longas distâncias a pé diariamente, exigindo preparo físico, atenção constante e disposição para atuar em diferentes áreas da propriedade.', 0, NULL, 'A função exige conhecimento técnico sobre pragas e doenças agrícolas, atenção aos detalhes e precisão no registro de dados. Erros no monitoramento podem resultar em prejuízos significativos devido ao uso incorreto ou desnecessário de insumos. O profissional deve tomar decisões rápidas e trabalhar com autonomia.', 'Monitorar pragas e doenças, realizar inspeções diárias por amostragem, registrar e encaminhar dados, colaborar com a tomada de decisão para controle fitossanitário e executar tratos culturais conforme orientação técnica. Realizar coletas de amostra de solo e folhas para análise.', '2025-10-17 15:57:40', '2025-11-07 10:57:52', 0, NULL, NULL, NULL),
(6, 'ASSISTENTE FISCAL', '', 6, 'Executa rotinas fiscais, como emissão de notas fiscais, apuração de impostos e controle de obrigações acessórias, garantindo a conformidade legal.', 4, NULL, 10, NULL, '1 ano', 'Atua em ambiente administrativo, em escritório climatizado, utilizando intensivamente computador e sistemas de gestão contábil. Trabalho realizado em horário comercial, com prazos rígidos e interação com equipes fiscal, contábil e financeira.', 0, NULL, 'O cargo apresenta complexidade técnica e administrativa, exigindo atenção a detalhes, domínio das normas fiscais e contábeis, capacidade de lidar com múltiplas demandas e prazos curtos. Requer raciocínio lógico e atualização constante para evitar penalidades.', 'Emitir notas fiscais e conferir documentos, organizar e arquivar documentos, apurar impostos, controlar e enviar obrigações acessórias, elaborar relatórios gerenciais, transmitir informações fiscais para a contabilidade e acompanhar atualizações da legislação tributária.', '2025-10-17 15:57:40', '2026-04-13 19:31:42', 0, NULL, NULL, NULL),
(7, 'AUXILIAR DE BIOPROCESSOS', '', 7, 'Executa tarefas técnicas no laboratório de campo, como manipulação de microrganismos, preparo de meios de cultura, envase e limpeza, garantindo o cumprimento das normas sanitárias e de biossegurança.', 4, NULL, 11, 26, 'Experiência mínima de 6 meses em ambiente de biofábrica, laboratório ou agroindústria.', 'Atua em biofábricas, laboratórios ou setores técnicos operacionais, com exposição a agentes biológicos, variações de temperatura e umidade. Utiliza EPIs e segue normas rígidas de higiene. Exige concentração, repetitividade e cumprimento de prazos de produção.', 0, NULL, 'A função exige domínio de rotinas operacionais de biossegurança, manipulação de insumos biológicos e controle de processos. Erros podem comprometer a qualidade do produto e causar contaminações. Requer concentração, disciplina e adesão a procedimentos padronizados.', 'Preparar e esterilizar materiais e meios de cultura, coletar, identificar e manipular microrganismos, realizar envase e armazenamento de produtos biológicos, efetuar a limpeza das áreas de trabalho, auxiliar no controle de qualidade e seguir normas de biossegurança.', '2025-10-17 15:57:40', '2025-11-07 18:11:15', 0, NULL, NULL, NULL),
(8, 'ASSISTENTE DE CERTIFICAÇÃO', '', 28, 'Executa atividades administrativas e operacionais relacionadas ao controle documental e à certificação, incluindo organização e arquivamento físico e digital, preenchimento e atualização de planilhas e bancos de dados, conferência de documentos e apoio às auditorias internas e externas. Presta suporte logístico e administrativo ao setor de certificação, garantindo a conformidade de processos e a rastreabilidade exigida.', 3, NULL, NULL, NULL, '1 a 6 meses.', 'Atua predominantemente em ambiente administrativo, utilizando computador e recursos de escritório. Interage frequentemente com setores diversos, com prazos e exigências relacionados a auditorias e conformidade documental.', 0, NULL, 'O cargo apresenta complexidade moderada, envolvendo controle rigoroso de dados e documentos, interação com diferentes setores e suporte direto a auditorias. Erros podem impactar a conformidade e a manutenção de certificações, exigindo atenção constante, responsabilidade e cumprimento rigoroso de prazos e padrões.', 'Organizar e arquivar documentos físicos e digitais. Registrar, atualizar e conferir planilhas e bancos de dados. Conferir, analisar e corrigir documentos de setores diversos. Controlar planilhas de rastreabilidade, colheita, pesagem e produtividade. Enviar análises laboratoriais.  Preencher e revisar formulários e relatórios administrativos. Apoiar auditorias internas e externas com informações e documentos.  Controlar materiais de escritório e solicitar compras. Dar suporte logístico a eventos e reuniões internas.', '2025-10-17 15:57:40', '2026-04-13 17:22:11', 0, NULL, NULL, NULL),
(9, 'AUXILIAR DE COMPRAS', '', 9, 'Auxilia em cotações com fornecedores, emite e encaminha pedidos de compra garantindo o cumprimento dos prazos e a conformidade dos processos sob supervisão.', 3, NULL, 11, NULL, '3 a 6 meses', 'Atua predominantemente  em ambiente de escritório, podendo fazer uso de computador e sistemas de compras. Interação constante com fornecedores, almoxarifado, setor fiscal e transporte. Demanda atenção concentrada e capacidade de lidar com múltiplas tarefas simultaneamente sob supervisão.', 0, NULL, 'O cargo exige atenção às rotinas de compras e organização. As decisões geralmente são supervisionadas, mas erros podem impactar diretamente os fluxos logísticos e operacionais.', 'Auxiliar nas requisições de cotações e compras, organização e alimentação de planilhas comparativas, auxiliar na emissão e acompanhamento de pedidos, encaminhar documentos fiscais para o setor contábil/fiscal. Em conformidade com as orientações da supervisão.', '2025-10-17 15:57:40', '2026-04-13 19:21:16', 0, NULL, NULL, NULL),
(10, 'AUXILIAR DE ESCRITÓRIO', '', 8, 'Executa rotinas administrativas de apoio (documentos, planilhas, notas fiscais) e presta suporte aos diversos departamentos e controle de materiais.', 3, NULL, 12, NULL, 'Experiência mínima de 3 meses a 1 ano, dependendo da complexidade das rotinas executadas.', 'Atua em ambiente de escritório climatizado e organizado, com boa iluminação e conforto. Interage com colegas, colaboradores e setores diversos, mantendo postura cordial. O trabalho exige atenção contínua e confidencialidade.', 0, NULL, 'O cargo exige atenção contínua, capacidade de organização, discrição e conhecimento básico das rotinas administrativas. Erros em lançamentos ou falhas na comunicação podem gerar impactos em processos internos.', 'Registrar entrada e saída de documentos, conferir notas fiscais/faturas/boletos, preencher formulários e planilhas, atualizar e organizar informações cadastrais, protocolar, digitalizar e arquivar documentos, dar suporte às rotinas de pessoal e solicitar e controlar materiais de expediente. Elaborar documentação não-técnica e correspondências', '2025-10-17 15:57:40', '2025-10-31 16:30:48', 0, NULL, NULL, NULL),
(11, 'AUXILIAR DE ALMOXARIFE', '', 10, 'Responsável por prestar apoio operacional nas rotinas de armazenamento, auxiliando no recebimento, conferência, estocagem e distribuição de materiais, insumos agrícolas, peças e equipamentos. Atua na organização física do depósito, rotulagem de itens, preenchimento de registros manuais ou em sistema, e suporte à documentação de certificação, visando a continuidade dos processos produtivos e a acuracidade do estoque.', 3, NULL, 12, NULL, '6 meses', 'Trabalho realizado em ambientes internos (galpões frescos ou quentes) e externos. O profissional deve estar preparado para atividades físicas moderadas, posturas prolongadas, movimentação de cargas (com auxílio de paleteiras) e exposição eventual a poeira. Exige interação frequente com equipes de Campo, Oficina,  Escritório e Fiscal.', 0, NULL, 'O cargo exige atenção constante aos detalhes para evitar erros de conferência que possam paralisar a produção. A complexidade reside na diversidade de itens gerenciados (de pequenos parafusos a defensivos químicos) e na necessidade de seguir protocolos rígidos de organização e rastreabilidade para auditorias.', 'Auxiliar na descarga de produtos e confrontar Notas Fiscais com pedidos de compra, verificando quantidades e integridade das embalagens.\r\nRealizar o endereçamento de materiais em prateleiras ou paletes, mantendo o layout organizado por categorias (peças, químicos, embalagens, etc) e zelando pela limpeza do local.\r\nSeparar insumos e ferramentas conforme requisições aprovadas, entregando-os aos colaboradores e colhendo assinaturas de protocolo.\r\nMonitorar prazos de validade de produtos e auxiliar na aplicação do método PEPS (Primeiro que Entra, Primeiro que Sai).\r\nAuxiliar na elaboração e organização de docuumentos de certificação, etiquetas de identificação e lançamento de dados básicos em sistemas ERP ou planilhas de Excel.\r\nParticipar de contagens cíclicas e auditorias de estoque para identificação de divergências.\r\nUtilizar corretamente os EPIs e auxiliar na higienização de ambientes, ferramentas e vestimentas de aplicação (conforme normas de segurança).', '2025-10-17 15:57:40', '2026-03-13 19:40:26', 0, NULL, NULL, NULL),
(12, 'ASSITENTE DE EXPORTAÇÃO', '', 11, 'Executa atividades de apoio à exportação, incluindo registro, controle e acompanhamento de processos logísticos e documentais. Responsável por coletar e distribuir documentos, emitir notas fiscais, supervisionar motoristas e expedição de mercadorias, negociar prazos e fretes com transportadoras, responder a e-mails de clientes e resolver problemas logísticos relacionados à coleta e entrega de contêineres. Atua em estreita colaboração com os setores logísticos e administrativos, garantindo conformidade com as regulamentações de exportação.', 11, NULL, NULL, NULL, '1 ano', 'Atua em ambiente administrativo/logístico confortável, com boas relações interpessoais e autonomia de execução. Trabalha em equipe sob supervisão direta, participando de atividades de rotina operacional e suporte ao transporte e logística.', 0, NULL, 'O cargo exige atenção aos detalhes, precisão na documentação, capacidade de negociação e gestão de recursos. A complexidade aumenta devido à necessidade de resolver problemas logísticos e garantir conformidade com as regulamentações de exportação, atuando sob prazos e demandas variáveis.', 'Coletar e distribuir documentos da área logística. Monitorar indicadores e documentos fiscais para exportação. Acompanhar e dar suporte às rotinas operacionais. Emitir notas fiscais e documentos de transporte. Negociar fretes e prazos de pagamento com transportadoras. Atualizar cadastros e dados de planejamento. Acompanhar pedidos e prazos de entrega. Responder e-mails de clientes e solucionar dúvidas. Supervisionar motoristas e expedição de mercadorias. Resolver problemas relacionados à coleta e entrega de contêineres.', '2025-10-17 15:57:40', '2026-04-13 18:03:05', 0, NULL, NULL, NULL),
(13, 'Trabalhador na Agricultura', 'Executa atividades operacionais relacionadas à irrigação de culturas, incluindo instalação, manutenção e acompanhamento dos sistemas de irrigação, verificação de bombas, reparos em mangueiras, ajustes em aspersores e monitoramento de cronogramas.', 18, 'Executa a instalação, manutenção e acompanhamento de sistemas de irrigação (bombas, mangueiras, aspersores), garantindo a eficiência hídrica.', 2, NULL, NULL, NULL, 'Experiência prática de até 6 meses para domínio das rotinas operacionais.', 'Atua em campo aberto, exposto a intempéries (sol, chuva, variações de temperatura). Trabalho exige esforço físico constante, locomoção por grandes áreas e uso contínuo de ferramentas manuais. Utiliza EPIs conforme as normas de segurança.', 0, NULL, 'O cargo exige conhecimentos técnicos básicos sobre irrigação e manutenção, bem como atenção constante e capacidade de seguir planos operacionais. A atuação direta com o campo requer resistência física e habilidade para lidar com problemas operacionais em tempo real.', 'Instalar e manter sistemas de irrigação, executar reparos e substituir peças danificadas, monitorar a operação das bombas e vazão da água, apoiar o controle do consumo hídrico, cumprir cronogramas de irrigação e comunicar falhas ou irregularidades aos superiores.', '2025-10-17 15:57:40', '2026-03-10 16:49:00', 0, NULL, NULL, NULL),
(14, 'AUXILIAR DE LABORATÓRIO', '', 13, 'Apoia as rotinas do laboratório com preparo e coleta de amostras, registros, limpeza de materiais e organização do ambiente, seguindo normas de biossegurança.', 3, NULL, 11, 26, 'Tempo de experiência desejado: 6 meses a 1 ano.', 'Atua em ambiente de laboratório fechado e em áreas de produção, com controle de temperatura, iluminação e biossegurança. Requer o uso de EPIs, atenção a riscos biológicos e químicos e cuidados rigorosos com a organização e descarte de resíduos.', 0, NULL, 'A função exige atenção constante, habilidade manual, cumprimento rigoroso de normas de biossegurança e apoio operacional às rotinas de laboratório. Pequenos erros podem comprometer a integridade de análises e o ambiente de trabalho.', 'Auxiliar na rotina laboratorial, coleta, registra e organiza dados e amostras, higienizar materiais e bancadas, apoiar processos de análises e zelar pela segurança e organização do ambiente.', '2025-10-17 15:57:40', '2025-11-07 18:22:54', 0, NULL, NULL, NULL),
(15, 'AUXILIAR DE MECÂNICO', '', 14, 'Auxilia na manutenção preventiva e corretiva de máquinas e veículos, realizando lubrificação, limpeza e apoio técnico em desmontagens, sob orientação.', 2, NULL, 11, 39, 'Experiência de 6 meses a 1 ano em manutenção automotiva ou agrícola.', 'Trabalho executado em oficinas, galpões ou campo aberto. Envolve esforço físico, contato com graxas, óleos e peças metálicas. Uso obrigatório de EPIs e cumprimento de normas de segurança. Atuação com ruído e risco de acidentes leves a moderados.', 0, NULL, 'A função exige conhecimentos básicos de mecânica, capacidade de seguir orientações técnicas, atenção a riscos e uso adequado de EPIs. Atua como suporte essencial às atividades de manutenção.', 'Apoiar mecânicos em serviços de manutenção, organizar oficina e ferramentas, limpar e preparar peças e componentes, auxiliar em inspeções visuais e testes simples e cumprir rotinas de segurança e controle de materiais.', '2025-10-17 15:57:40', '2025-11-07 16:44:38', 0, NULL, NULL, NULL),
(16, 'AUXILIAR DE PRODUÇÃO', '', 15, 'Executa atividades operacionais ao longo da linha de produção, incluindo pré-seleção, tombamento, aplicação de tratamentos e lavagem, montagem de carga e paletes, rastreabilidade e carregamento de produtos. Colabora para o controle de qualidade e conformidade dos insumos, assegurando o cumprimento de normas de higiene, segurança e rastreabilidade. Atua em ambientes internos e externos, sob supervisão direta.', 1, NULL, NULL, NULL, '1 mês', 'Atua em ambientes diversos: linha de produção, armazém refrigerado, área de expedição e pátios externos. Submetido a ruídos, variações térmicas, tarefas repetitivas, turnos alternados e prazos operacionais. Utiliza EPIs e segue protocolos rígidos de higiene, rastreabilidade e segurança do trabalho.', 0, NULL, 'O cargo exige atenção contínua, habilidades manuais, organização e comprometimento com a qualidade e segurança alimentar. A execução inadequada de tarefas como pesagem, rastreabilidade e montagem de carga pode comprometer a integridade dos produtos e a conformidade com normas legais e comerciais. A função requer flexibilidade, polivalência e responsabilidade em ambiente produtivo dinâmico e exigente.', 'Realizar a triagem e pré-seleção de frutos conforme critérios de qualidade. Efetuar tombamento, aplicação de banho e pesagem de produtos. Montar paletes e cargas, realizar amarrações e conferência de volumes. Emitir e aplicar etiquetas de rastreabilidade. Preencher planilhas manuais e digitais de produção e controle. Auxiliar no carregamento e descarregamento de caminhões. Inspecionar condições de higiene, temperatura e segurança dos processos. Zelar pela limpeza dos equipamentos e ambiente de trabalho. Comunicar falhas e não conformidades à supervisão. Cumprir normas técnicas, operacionais e de segurança.', '2025-10-17 15:57:40', '2026-04-17 13:09:31', 0, NULL, NULL, NULL),
(17, 'AUXILIAR DE SELEÇÃO E EMBALAGEM', '', 16, 'Executa atividades relacionadas à triagem, seleção, classificação e embalagem de frutas, seguindo padrões de qualidade definidos pela empresa. Realiza inspeção visual para detectar danos, contaminações ou não conformidades, assegurando que os produtos atendam às especificações exigidas. Prepara e organiza embalagens conforme procedimentos específicos, visando à integridade do produto e à conformidade com normas de segurança e higiene.', 1, NULL, NULL, NULL, '1 mês', 'Trabalho realizado em ambiente de Packing House ou setor de produção com temperatura controlada e alta umidade. Exige postura em pé durante longos períodos e tarefas repetitivas. Uso obrigatório de EPIs. Exposição a variações térmicas e umidade.', 0, NULL, 'Trata-se de uma função de baixa complexidade técnica, mas que exige atenção contínua, agilidade, precisão e comprometimento com padrões de qualidade. Erros podem comprometer a apresentação e integridade do produto final, afetando a imagem da empresa e a satisfação do cliente. As condições operacionais e psicossociais demandam resiliência e disciplina do trabalhador.', 'Selecionar, classificar e embalar frutas conforme padrões de qualidade. Identificar, separar e etiquetar corretamente os produtos. Conferir e organizar os materiais e embalagens utilizados. Arrumar embalagens em paletes e realizar transporte para locais designados. Realizar inspeção visual para detectar produtos fora de especificação. Limpar e organizar a área de trabalho e equipamentos utilizados. Cumprir normas operacionais, de higiene, segurança e rastreabilidade. Colaborar com a equipe e reportar irregularidades à supervisão.', '2025-10-17 15:57:40', '2026-04-17 13:06:54', 0, NULL, NULL, NULL),
(18, 'AUXILIAR DE SERVIÇOS GERAIS', '', 17, 'Executa atividades de manutenção e organização rotineiras como: limpeza, higienização e organização de ambientes internos e externos da empresa, incluindo áreas operacionais, administrativas e sanitárias. Realiza coleta e descarte de resíduos, abastecimento de materiais de higiene. Aplica procedimentos de limpeza leve e pesada, observando padrões de segurança, higiene e conservação.', 1, NULL, 12, NULL, '1 mes', 'Atua em ambientes internos e externos, com variações térmicas  e de umidade. A rotina pode envolver turnos diurnos e noturnos, com esforço físico moderado, postura em pé prolongada, movimentação de cargas leves e execução de tarefas repetitivas. Requer o uso obrigatório de EPIs, sinalização adequada de limpeza e atenção ao manuseio de produtos químicos e equipamentos de limpeza.', 0, NULL, 'Trata-se de um cargo operacional com execução padronizada de tarefas de limpeza, conservação e apoio à rotina interna. Apesar da baixa complexidade técnica, o cargo exige responsabilidade, atenção a normas de segurança e higiene, capacidade física e disposição para realizar atividades diversas e contínuas. O desempenho impacta diretamente a imagem, salubridade e bem-estar dos demais colaboradores e visitantes.', 'Executar a limpeza leve e pesada de áreas administrativas, operacionais, banheiros e copa. Coletar resíduos e descartar conforme orientação técnica e ambiental. Repor materiais de higiene (papel, sabão, álcool gel). Verificar validade e diluição correta dos produtos de limpeza. Utilizar corretamente os EPIs e zelar pelo uso adequado dos produtos e equipamentos. Controlar o estoque de materiais de limpeza e comunicar necessidades de reposição. Sinalizar as áreas em higienização e seguir rotinas operacionais de limpeza. Executar tarefas noturnas conforme escala de revezamento. Participar de capacitações e seguir normas internas de segurança e higiene.', '2025-10-17 15:57:40', '2026-04-17 14:45:11', 0, NULL, NULL, NULL),
(19, 'Trabalhador na Agricultura', 'Executa atividades de campo nas etapas de capina, poda, condução de frutíferas, irrigação e manutenção das culturas.', 18, 'Executa capina, poda, irrigação e manutenção das culturas, operando ferramentas manuais e motorizadas.', 1, NULL, NULL, NULL, 'Experiência prática desejável.', 'Trabalho ao ar livre com esforço físico contínuo e exposição climática. Uso obrigatório de EPIs.', 0, NULL, 'Baixa a média complexidade técnica, com foco em execução e segurança.', 'Capina, poda e irrigação, aplicação segura de insumos, conservação de ferramentas e cuidado com as plantas.', '2025-10-17 15:57:40', '2026-03-10 16:49:00', 0, NULL, NULL, NULL),
(20, 'AUXILIAR FINANCEIRO', '', 620124, 'Auxilia nas rotinas financeiras e administrativas, apoiando contas a pagar/receber, conciliação bancária e organização de documentos sob supervisão.', 3, NULL, 11, NULL, '6 meses a 1 ano.', 'Ambiente administrativo, uso constante de computador. Exige discrição, foco em prazos e integração com equipe multidisciplinar.', 0, NULL, 'Exige conhecimento básico na área financeira como contas a pagar e a receber, sistema financeiro e bancário. Exige atenção a prazos e informações exatas.', 'Auxilio financeiro e contábil, organização de arquivos, conciliações e lançamentos, suporte à equipe e manutenção de cadastros.', '2025-10-17 15:57:40', '2026-04-13 19:51:46', 0, NULL, NULL, NULL),
(21, 'CAMINHONEIRO', 'Atua na operação e gestão de transporte de cargas, conduzindo caminhões e outros veículos de grande porte, executando atividades de coleta e entrega de mercadorias, inspeção e manutenção veicular básica, controle de carga, atendimento ao cliente e suporte à logística da empresa.', 19, 'Conduz caminhões, gerencia o transporte de cargas (coleta e entrega), realiza inspeção e manutenção básica veicular e controla documentação.', 2, NULL, NULL, NULL, 'Exigida experiência prévia entre 1 a 2 anos.', 'Trabalho externo, com exposição a intempéries, esforço físico moderado, e rotinas de viagem.', 0, NULL, 'Alta exigência de responsabilidade e coordenação com setores diversos.', 'Coleta e entrega de mercadorias, inspeção de veículos, controle de documentos, preservação do veículo e planejamento de rotas.', '2025-10-17 15:57:40', '2025-10-17 15:57:40', 0, NULL, NULL, NULL),
(22, 'CHEFE DE COZINHA', 'Coordena e executa atividades de preparo, finalização e apresentação de refeições, aplicando boas práticas de manipulação e segurança alimentar.', 20, 'Coordena, planeja e executa o preparo de refeições, controlando estoque, custos e garantindo a segurança alimentar.', 5, NULL, NULL, NULL, 'Experiência de pelo menos 1 ano em cozinha profissional.', 'Ambiente interno com calor, utensílios cortantes, pisos molhados e pressão por prazos.', 0, NULL, 'Alta responsabilidade e domínio técnico, com impacto direto no serviço e reputação da empresa.', 'Supervisão da equipe, planejamento de refeições, controle de qualidade, higienização e organização.', '2025-10-17 15:57:40', '2025-10-17 15:57:40', 0, NULL, NULL, NULL),
(23, 'Trabalhador na Agricultura', '', 18, 'Executa a colheita manual de frutas, selecionando frutos e utilizando ferramentas e técnicas específicas de colheita.', 1, NULL, 12, 1, 'Sem exigência formal. Experiência prática é adquirida em até 3 meses.', 'Campo aberto com exposição climática e esforço físico contínuo.', 0, NULL, 'Função operacional com foco em qualidade e metas de produção.', 'Seleção e colheita de frutos, transporte e limpeza de ferramentas.', '2025-10-17 15:57:40', '2026-03-10 16:49:00', 0, NULL, NULL, NULL),
(24, 'DESIGNER E COMUNICAÇÃO VISUAL', '', 21, 'Desenvolve projetos de design de produtos e comunicação visual, utilizando softwares vetoriais,  2D,  CAD/3D e aplicando análise técnica/estética.', 3, NULL, 12, NULL, '1 a 3 anos.', 'Ambientes internos, home office, interação com áreas diversas.', 0, NULL, 'Requer conhecimento técnico especializado e habilidades multidisciplinares no setor.', 'Desenvolve projetos de design de produtos industriais, embalagens, placas de identificação e comunicação visual interna, aplicando conhecimentos de estética, ergonomia, materiais, processos produtivos, viabilidade técnica e estratégias de marketing. Atua na concepção e desenvolvimento de novos produtos e materiais gráficos, adaptações e melhorias, considerando demandas mercadológicas, funcionais e técnicas, incluindo a produção de materiais para comunicação em mídias digitais e impressas.', '2025-10-17 15:57:40', '2026-04-13 14:04:07', 0, NULL, NULL, NULL),
(25, 'ELETRICISTA', '', 22, 'Executa serviços de manutenção preventiva, preditiva e corretiva em instalações e equipamentos elétricos utilizados na empresa, incluindo motores, sistemas elétricos e componentes eletroeletrônicos. Atua com foco na segurança, confiabilidade e funcionamento adequado dos equipamentos, conforme normas técnicas e procedimentos operacionais.', 2, NULL, NULL, NULL, '1 a 2 anos.', 'O trabalho é realizado em todas as áreas da empresa, internas e externas, incluindo ambientes administrativos. Requer comunicação eficaz e feedback constante para garantir segurança e eficiência. A ausência de comunicação pode gerar desgaste físico, emocional e operacional.', 0, NULL, 'O cargo exige domínio técnico, precisão e tomada de decisão em diferentes níveis de complexidade. As atividades vão desde manutenções simples até a resolução de falhas críticas em sistemas elétricos. Falhas operacionais podem comprometer o funcionamento da fazenda, sendo essencial atenção a normas, comunicação e segurança.', 'Interpretar ordens de serviço, diagnosticar falhas, realizar manutenção corretiva e preventiva, executar medições elétricas, conectar cabos, liberar equipamentos para teste, aplicar primeiros socorros quando necessário, utilizar EPIs e registrar ocorrências técnicas com foco na segurança e no funcionamento adequado dos sistemas.', '2025-10-17 15:57:40', '2026-04-13 20:08:28', 0, NULL, NULL, NULL),
(26, 'SUPERVISOR DE BIOPROCESSOS', '', 23, 'Supervisiona a produção biotecnológica, controla a qualidade, gerencia protocolos técnicos e implementa boas práticas e melhorias contínuas.', 5, NULL, 4, 46, 'Experiência de 3 a 5 anos.', 'Ambiente laboratorial e de produção com equipe reduzida, exigindo atuação técnica e estratégica.', 0, NULL, 'Alta complexidade técnica e gestão multidisciplinar.', 'Supervisão da produção, controle de qualidade, elaboração de relatórios, implementação de boas práticas e liderança de equipe.', '2025-10-17 15:57:40', '2025-11-07 17:59:03', 0, NULL, NULL, NULL),
(27, 'SUPERVISOR DE COLHEITA', '', 23, 'Planeja, organiza e supervisiona a colheita, controlando a produção, gerindo a equipe e garantindo a segurança operacional.', 3, NULL, 4, 46, 'Experiência mínima de 3 anos em campo.', 'Campo aberto, sob condições climáticas diversas e tomada de decisão rápida.', 0, NULL, 'Média a alta, com foco operacional e liderança de equipe.', 'Supervisão de colheita, gestão de equipe (frequência, férias), garantia da segurança.', '2025-10-17 15:57:40', '2025-10-31 13:12:03', 0, NULL, NULL, NULL),
(28, 'ENCARREGADO DE IRRIGAÇÃO', 'Coordena e executa atividades de irrigação e fertirrigação, realiza manutenção e controle dos sistemas, gestão de insumos e equipe.', 12, 'Coordena e executa a irrigação e fertirrigação, gerencia a manutenção dos sistemas e a equipe, e controla o uso de insumos.', 2, NULL, NULL, NULL, 'Experiência de 1 a 2 anos.', 'Ambiente agrícola, sujeito a variações climáticas e exigência de uso de EPIs.', 0, NULL, 'Alta, com responsabilidade técnica e ambiental.', 'Planejamento da irrigação, supervisão da equipe, avaliação técnica e preenchimento de relatórios.', '2025-10-17 15:57:40', '2025-10-17 15:57:40', 0, NULL, NULL, NULL),
(29, 'ENCARREGADO DE MANUTENÇÃO DE PACKING', '', 24, 'Responsável por realizar e supervisionar atividades de manutenção em máquinas e equipamentos do setor de packing, garantindo o bom funcionamento, a continuidade da produção e a minimização de paradas. Atua na identificação e solução de problemas técnicos, além de implementar melhorias nos processos e equipamentos, conforme normas de segurança e qualidade.', 4, NULL, 4, NULL, '1 ano', 'Atua em ambiente industrial, realizando manutenção em máquinas e equipamentos do setor de packing. Exige atenção a normas de segurança, uso de equipamentos de proteção individual e capacidade de lidar com riscos físicos e químicos. Trabalho sujeito a pressão por prazos e necessidade de disponibilidade para intervenções emergenciais.', 0, NULL, 'O cargo exige conhecimentos técnicos aprofundados em mecânica e eletroeletrônica, habilidades de supervisão e gestão de equipes, capacidade de identificar e solucionar problemas técnicos, implementar melhorias e garantir a continuidade da produção. Demanda resiliência, atenção aos detalhes e habilidade para trabalhar sob pressão.', 'Realizar manutenção preventiva, corretiva e preditiva em máquinas e equipamentos. Supervisionar e orientar equipes de manutenção. Interpretar ordens de serviço e manuais técnicos. Diagnosticar falhas e propor soluções. Elaborar e solicitar orçamentos de peças e serviços. Realizar testes e medições em equipamentos. Garantir limpeza e organização do local de trabalho. Seguir normas técnicas e de segurança. Administrar conflitos e tomar decisões operacionais. Assegurar que as máquinas permaneçam operacionais e sem paradas prolongadas.', '2025-10-17 15:57:40', '2026-04-17 15:11:42', 0, NULL, NULL, NULL),
(30, 'ENCARREGADO DE PEDREIRO', 'Supervisiona equipes de construção civil, interpreta projetos, distribui tarefas e garante conformidade com normas técnicas.', 25, 'Supervisiona equipes de pedreiros, interpreta projetos, planeja tarefas, calcula materiais e garante a segurança da obra.', 1, NULL, NULL, NULL, 'Experiência consolidada.', 'Ambientes externos de obra, com riscos físicos, necessidade de EPIs e trabalho sob calor, poeira e altura.', 0, NULL, 'Coordenação técnica e operativa de equipes de obra.', 'Distribuição de tarefas, controle de produtividade, requisição de materiais e garantia da segurança.', '2025-10-17 15:57:40', '2025-10-17 15:57:40', 0, NULL, NULL, NULL),
(31, 'COORDENADOR DE VIVEIRO', '', 23, 'Coordena, planeja e supervisiona as atividades do viveiro, gerindo equipes, insumos e garantindo a qualidade das mudas e práticas sustentáveis.', 4, NULL, 3, NULL, 'Experiência mínima de 2 anos como líder em viveiros.', 'Trabalho em campo, com exposição a intempéries. Alta responsabilidade técnica e gestão de equipe.', 0, NULL, 'Alta complexidade com foco em liderança técnica e múltiplas frentes operacionais.', 'Coordenar atividades no viveiro, gerir equipes, controlar produtividade, supervisionar compostagem, EPIs e manutenção.', '2025-10-17 15:57:40', '2025-10-31 16:43:05', 0, NULL, NULL, NULL),
(32, 'ENCARREGADO DE ORGÂNICO', 'Coordena e supervisiona atividades agrícolas orgânicas, garantindo produtividade, qualidade, conformidade com normas e gestão da equipe.', 23, 'Supervisiona e coordena a agricultura orgânica, garantindo a conformidade com normas de certificação, a gestão da equipe e o controle de composto orgânico.', 3, NULL, NULL, NULL, 'Experiência: 1 a 3 anos.', 'Campo aberto com exposição ao clima. Exige decisão rápida, liderança e visão sistêmica.', 0, NULL, 'Média a alta. Exige conhecimento técnico e alinhamento com certificação orgânica.', 'Planejamento e execução de tarefas orgânicas, treinamento e gestão de equipe, controle de produtividade, manutenção de áreas comuns e supervisão de compostagem e aplicação.', '2025-10-17 15:57:40', '2025-10-17 15:57:40', 0, NULL, NULL, NULL),
(33, 'GERENTE ADMINISTRATIVO', '', 26, 'Gerencia as áreas administrativa, operacional e financeira, suporta a diretoria na estratégia e é responsável pela gestão de contratos e resolução de conflitos.', 5, NULL, 2, 1, '3 a 5 anos', 'Ambiente fechado, sob supervisão eventual, com rotina de prazos e decisões estratégicas.', 0, NULL, 'Alta. Impacto direto nos resultados e demandas intersetoriais. O cargo demanda alta capacidade de gestão, liderança e tomada de decisões estratégicas, envolvendo múltiplas áreas da organização com impacto direto nos resultados financeiros e operacionais. Requer conhecimento técnico especializado e habilidades multidisciplinares para lidar com demandas diversas e simultâneas, incluindo negociações complexas e gestão de conflitos.', 'Exerce a gerência das atividades administrativas, operacionais e financeiras da organização, incluindo gestão de equipes, supervisão de processos, elaboração de orçamentos, gerenciamento de contratos, análise de riscos organizacionais e suporte à diretoria na tomada de decisões estratégicas. Atua também no contato com clientes, resolução de conflitos e acompanhamento de processos de produção e qualidade.', '2025-10-17 15:57:40', '2026-04-13 13:47:18', 0, NULL, NULL, NULL),
(34, 'SUPERVISOR FINANCEIRO', '', 620124, 'Responsável pela supervisão e gestão financeira da empresa, atuando no controle e planejamento financeiro, incluindo o acompanhamento do fluxo de caixa, elaboração de relatórios, coordenação da equipe do setor, análise de custos e despesas, e proposição de melhorias para garantir a saúde financeira e a sustentabilidade econômica do negócio. Atua como elo entre a diretoria e o setor financeiro, assegurando a eficiência nos processos e o cumprimento das obrigações legais.', 5, NULL, 4, NULL, '1 a 2 anos', 'Atua em ambiente corporativo, com uso intensivo de computador, planilhas, softwares financeiros e sistemas de gestão integrada. Exige habilidade para lidar com dados confidenciais e atuar sob pressão para cumprimento de prazos e metas. Interage com diretoria, setor contábil, jurídico e instituições financeiras.', 0, NULL, 'O cargo demanda elevada capacidade analítica, visão estratégica, conhecimentos técnicos em finanças, legislação e contabilidade, além de habilidade para gerenciar equipes, estabelecer metas e atuar sob pressão. Tem impacto direto nos resultados da empresa e requer confidencialidade e precisão.', 'Planejar e controlar o orçamento financeiro da empresa. Gerenciar o fluxo de caixa diário e projeções futuras. Conduzir negociações bancárias e financeiras. Supervisionar o processo de contas a pagar e receber. Emitir relatórios de desempenho financeiro para diretoria. Analisar custos e despesas operacionais. Conduzir auditorias internas e auxiliar auditorias externas. Orientar e desenvolver a equipe financeira. Assegurar o cumprimento de obrigações fiscais e tributárias. Apoiar decisões estratégicas com base em análises financeiras.', '2025-10-17 15:57:40', '2026-04-13 19:42:12', 0, NULL, NULL, NULL),
(35, 'INSPETOR DE QUALIDADE', '', 28, 'Responsável por inspecionar e garantir a conformidade dos processos e produtos no setor de embalagem e expedição. Executa atividades de inspeção visual, verificação de padrões de qualidade, controle de especificações e preenchimento de relatórios técnicos, contribuindo para a entrega de produtos dentro dos critérios estabelecidos.', 3, NULL, NULL, NULL, '6 meses.', 'Atua em ambientes de embalagem, expedição e escritórios de controle de qualidade. Trabalha em pé por longos períodos, lida com variações de temperatura e contato com produtos agrícolas. Requer uso constante de EPIs e atenção contínua durante a execução das inspeções.', 0, NULL, 'O cargo requer atenção concentrada, domínio das normas de qualidade e agilidade para lidar com não conformidades. A precisão nos registros e decisões do inspetor impacta diretamente a imagem da empresa e a aceitação dos produtos no mercado. Envolve interface com produção, expedição, liderança e clientes.', 'Inspecionar frutas e embalagens quanto a aparência, peso, rotulagem e integridade. Verificar padrões de qualidade no recebimento, seleção, embalagem e carregamento.  Realizar a conferência de temperatura e integridade dos contêineres. Registrar não conformidades e comunicar ao setor responsável. Emitir relatórios de inspeção e liberar cargas para embarque. Monitorar higiene e condições operacionais dos ambientes inspecionados. Realizar amostragem de frutas conforme procedimentos técnicos. Garantir rastreabilidade dos lotes inspecionados. Atuar de forma preventiva para evitar reprovações e devoluções de produtos.', '2025-10-17 15:57:40', '2026-04-17 14:14:53', 0, NULL, NULL, NULL),
(36, 'JARDINEIRO', '', 29, 'Executa atividades de jardinagem (plantio, poda, irrigação, controle de pragas) e manutenção estética das áreas verdes, operando roçadeira e ferramentas manuais.', 1, NULL, 12, 2, 'Experiência de 1 a 3 anos.', 'Trabalho ao ar livre com exposição a intempéries e esforço físico.', 0, NULL, 'Exige domínio prático com protocolos definidos e supervisão eventual.', 'Manter áreas verdes, realizar podas e roçagens, supervisionar auxiliares, comunicar irregularidades e contribuir com a estética do local.', '2025-10-17 15:57:40', '2025-10-31 16:21:41', 0, NULL, NULL, NULL),
(37, 'LÍDER DE COZINHA', 'Coordena equipe do refeitório, supervisiona preparo e qualidade das refeições, controla insumos, organiza tarefas e apoia o planejamento do cardápio.', 30, 'Coordena a equipe do refeitório, supervisiona o preparo e a qualidade das refeições, controla insumos e planeja cardápios.', 2, NULL, NULL, NULL, 'Experiência mínima de 1 ano.', 'Ambiente quente e úmido, exige atenção, agilidade e cooperação.', 0, NULL, 'Exige liderança técnica e operacional com impacto direto na saúde e satisfação dos usuários.', 'Supervisionar equipe, controlar qualidade e higiene, planejar cardápio, treinar equipe e manter equipamentos.', '2025-10-17 15:57:40', '2025-10-17 15:57:40', 0, NULL, NULL, NULL),
(38, 'LUBRIFICADOR', '', 31, 'Executa a lubrificação de máquinas e equipamentos, abastecimento de veículos terrestres e lavagem de máquinas e implementos agrícolas, realizando inspeções e registrando as ações para garantir o funcionamento seguro e eficiente.', 3, NULL, 12, NULL, 'Experiência mínima de 8 meses.', 'Ambiente rural ou oficina, com esforço físico, exposição a ruídos e agentes químicos e inflamáveis.', 1, '2026-03-13 14:27:00', 'Baixa a média. Exige precisão e zelo com impacto na durabilidade dos equipamentos.', 'Lubrificação, higienização e abastecimento de máquinas e equipamentos agrícolas, registrar ações, realizar inspeções e apoiar na manutenção.', '2025-10-17 15:57:40', '2026-03-13 17:27:00', 0, NULL, NULL, NULL),
(39, 'MECÂNICO', '', 32, 'Realiza manutenção (preventiva e corretiva) em máquinas industriais e agrícolas, diagnostica falhas, substitui peças e opera torno e solda.', 3, NULL, 12, 46, 'Experiência: 3 a 5 anos.', 'Ambientes industriais ou oficinas, com ruído, calor, poeira e contato com agentes químicos.', 0, NULL, 'Alta. Requer tomada de decisão sob pressão, domínio técnico e atualização constante.', 'Realizar manutenção, controlar cronogramas, elaborar relatórios e propor melhorias.', '2025-10-17 15:57:40', '2025-11-07 16:44:59', 0, NULL, NULL, NULL),
(40, 'OPERADOR DE EMPILHADEIRA', '', 33, 'Responsável por operar empilhadeiras para movimentação, elevação e armazenamento de cargas em ambientes internos e externos, como armazéns e áreas de expedição. Realiza carregamento, descarregamento, inspeções visuais, conferências de carga e zela pela conservação do equipamento. Atua como elo essencial na cadeia de abastecimento e expedição, seguindo normas de segurança e boas práticas operacionais.', 2, NULL, NULL, NULL, '1 a 2 anos', 'Trabalha em ambientes internos e externos, operando empilhadeiras sob diferentes condições de temperatura, ruído e trepidação. Está sujeito a riscos físicos, químicos e acidentais, exigindo atenção redobrada e uso constante de EPIs. Interage com equipes de expedição, logística e almoxarifado, cumprindo turnos e prazos definidos.', 0, NULL, 'Função de nível técnico-operacional que exige formação específica, prática regular e atenção constante a riscos. A movimentação inadequada de cargas pode causar danos a produtos, estruturas ou pessoas. Requer domínio técnico da máquina, coordenação com outros setores e cumprimento rigoroso das normas de segurança e eficiência logística.', 'Operar empilhadeira de forma segura para carregar, descarregar e movimentar cargas. Realizar inspeções visuais nas cargas e no equipamento. Conferir conteúdo, peso e volume das cargas transportadas. Selecionar os equipamentos de movimentação adequados ao tipo de carga. Monitorar o funcionamento do equipamento durante a operação. Zelar pela conservação da empilhadeira e comunicar falhas de funcionamento. Organizar e higienizar o ambiente de trabalho. Cumprir as normas de segurança e utilizar os EPIs exigidos. Participar de treinamentos de capacitação e atualização técnica. Trabalhar em colaboração com a equipe de expedição e logística.', '2025-10-17 15:57:40', '2026-04-17 13:24:11', 0, NULL, NULL, NULL),
(41, 'OPERADOR DE ROÇADEIRA E MOTOSSERRA', '', 34, 'Opera roçadeira e motosserra para limpeza, manutenção e poda de áreas verdes e pomares, seguindo normas de segurança e ambientais.', 1, NULL, 12, NULL, 'Experiência prática mínima de até 1 ano é recomendada para ambas as funções.', 'Trabalho ao ar livre, trabalho em altura, sujeito a diferentes condições climáticas. Exige esforço físico contínuo, exposição a ruído, resíduos vegetais e manipulação de ferramentas cortantes. Uso obrigatório de EPIs.', 0, NULL, 'Exige atenção constante, resistência física e habilidade técnica com equipamentos cortantes.', 'Manter áreas limpas, executar poda com precisão, limpar áreas diversas, zelar por equipamentos e seguir normas ambientais.', '2025-10-17 15:57:40', '2025-10-31 14:32:20', 0, NULL, NULL, NULL);
INSERT INTO `cargos` (`cargoId`, `cargoNome`, `cargoDescricao`, `cboId`, `cargoResumo`, `escolaridadeId`, `faixaId`, `nivelHierarquicoId`, `cargoSupervisorId`, `cargoExperiencia`, `cargoCondicoes`, `is_revisado`, `data_revisao`, `cargoComplexidade`, `cargoResponsabilidades`, `cargoDataCadastro`, `cargoDataAtualizacao`, `tem_piso_salarial`, `piso_valor`, `piso_lei_numero`, `piso_data_base`) VALUES
(42, 'PEDREIRO', '', 25, 'Executa obras, reformas e manutenções prediais, estruturais e hidráulicas em ambientes internos e externos da empresa. Realiza serviços de alvenaria, concretagem, assentamento de revestimentos, carpintaria, pintura, aplicação de chapisco e argamassa, montagem de andaimes, instalação de telhados e estruturas, seguindo projetos e orientações técnicas, com foco na qualidade, segurança e conservação das instalações.', 1, NULL, NULL, NULL, '1 a 2 anos', 'Atua em ambientes internos e externos, sob diferentes condições climáticas, com exposição a calor, poeira, umidade e agentes químicos. Exige esforço físico intenso, trabalho em altura, deslocamentos frequentes e uso constante de EPIs. Pode operar em locais de difícil acesso e em situações emergenciais. Ambiente de trabalho colaborativo, sujeito a riscos operacionais típicos da construção civil.', 0, NULL, 'Envolve múltiplas áreas técnicas como alvenaria, hidráulica, carpintaria e pintura. Exige experiência prática consolidada, capacidade de análise de problemas, improviso e domínio técnico em diversas etapas da obra. Falhas na execução podem comprometer a segurança estrutural, gerar prejuízos financeiros e afetar diretamente a operação da empresa. É uma função de média a alta complexidade com exigência constante de atenção, precisão e responsabilidade.', 'Executar serviços de construção, reforma e manutenção predial e estrutural. Realizar serviços de alvenaria, concretagem, reboco, pintura e assentamento de revestimentos. Montar e desmontar andaimes, escoramentos e estruturas temporárias. Executar manutenção hidráulica e reparos diversos em edificações. Aplicar chapisco, preparar e aplicar argamassa, tintas e selantes. Conservar ferramentas, organizar e limpar o ambiente de trabalho. Sinalizar áreas de risco e cumprir normas de segurança. Seguir instruções orais e cronogramas operacionais. Preencher laudos e registrar ocorrências quando necessário. Contribuir para a continuidade das operações da empresa com manutenções preventivas e corretivas.', '2025-10-17 15:57:40', '2026-04-17 12:02:07', 0, NULL, NULL, NULL),
(43, 'PROGRAMADOR DE SISTEMAS', '', 37, 'Desenvolve, implementa e mantém sistemas e aplicações, incluindo rotinas automatizadas, relatórios, interfaces e dashboards. Realiza análise técnica, codificação, testes, implantação, suporte e treinamento aos usuários. Documenta processos, define metodologias, avalia desempenho de sistemas e propõe melhorias para atender às demandas da empresa.', 4, NULL, 12, NULL, '1 a 3 anos', 'Atua em ambiente de escritório climatizado ou em regime de home office, com estrutura ergonômica. Trabalho exige concentração prolongada, atenção a detalhes, e eventual atendimento a demandas urgentes dos usuários. O ambiente é predominantemente calmo, sem pressão constante, com possibilidade de reuniões técnicas e treinamentos.', 0, NULL, 'Função de alta complexidade técnica, que exige capacidade analítica, raciocínio lógico, domínio de linguagens e ferramentas de programação, além de atualização constante em novas tecnologias. O impacto de falhas pode comprometer a operação de diversos setores, exigindo atenção, responsabilidade e senso crítico no desenvolvimento de soluções eficazes e seguras.', 'Desenvolver, testar e implantar sistemas, aplicações e dashboards. Prestar suporte técnico a usuários e registrar demandas de melhorias. Documentar processos, fluxos, sistemas e alterações. Avaliar desempenho, segurança e confiabilidade dos sistemas. Adaptar sistemas a novas plataformas, linguagens ou ambientes. Instalar programas, realizar backup, atualização e versionamento. Elaborar materiais de treinamento e capacitar usuários. Realizar reuniões com usuários, equipes e áreas solicitantes. Pesquisar e aplicar novas tecnologias e metodologias de desenvolvimento. Zelar pelo funcionamento contínuo do sistema ERP e ferramentas integradas', '2025-10-17 15:57:40', '2026-04-13 18:57:48', 0, NULL, NULL, NULL),
(44, 'RECEPCIONISTA', '', 38, 'Recepciona visitantes, atende o PABX, registra entradas/saídas e executa rotinas administrativas básicas com cordialidade e discrição.', 3, NULL, 12, NULL, '6 meses', 'Ambiente interno, com carga horária definida. Exige postura profissional, atenção constante e discrição.', 0, NULL, 'O cargo exige boa comunicação, discrição e agilidade no atendimento a múltiplas demandas. Erros ou falhas podem comprometer a imagem institucional, a fluidez das rotinas administrativas e a segurança da empresa. Requer responsabilidade, empatia e organização.', 'Responsável por recepcionar visitantes e colaboradores, atender ligações, prestar informações, encaminhar demandas internas e apoiar atividades administrativas. Organiza o ambiente de recepção, realiza controle de entradas e saídas e garante um atendimento cordial, eficiente e em conformidade com as normas institucionais.', '2025-10-17 15:57:40', '2026-04-13 14:40:44', 0, NULL, NULL, NULL),
(45, 'SOLDADOR', '', 39, 'Executa soldagem em peças e estruturas metálicas (eletrodo, TIG/MIG), interpreta desenhos técnicos e apoia a manutenção de peças prioritariamente na oficina e em diversos setores da empresa.', 2, NULL, 12, 39, 'Experiência mínima de 3 meses.', 'Ambiente de oficina semiaberto, com exposição a calor, poeiras, fumaça metálica, esforço físico e riscos da função.', 0, NULL, 'Domínio técnico de soldagem, leitura de projetos, atuação com autonomia e responsabilidade.', 'Realizar soldas, preservar ferramentas e EPIs, manter organização, realizar cortes e inspeções, apoiar manutenção de peças.', '2025-10-17 15:57:40', '2025-11-07 16:48:33', 0, NULL, NULL, NULL),
(46, 'GERENTE DE CAMPO', '', 2, 'Planeja e supervisiona atividades agrícolas operacionais, coordenando equipes, controlando insumos, metas e a qualidade das práticas no campo.', 5, NULL, 2, NULL, 'Experiência mínima de 2 anos.', 'Ambiente externo, sujeito a variações climáticas, metas e prazos.', 0, NULL, 'Alta complexidade técnica e de liderança em múltiplas frentes operacionais.', 'Supervisionar atividades agrícolas, coordenar equipe, controlar insumos, garantir normas e metas.', '2025-10-17 15:57:40', '2025-11-07 15:57:33', 0, NULL, NULL, NULL),
(47, 'SUPERVISOR DE CERTIFICAÇÃO', '', 28, 'Responsável por supervisionar, planejar e executar atividades ligadas à certificação e controle da qualidade dos produtos e processos da organização, assegurando conformidade com normas técnicas, critérios regulatórios e requisitos de clientes. Atua desde o recebimento de insumos até a entrega dos produtos, coordenando auditorias, atualizando políticas e promovendo melhorias contínuas.  Garantindo a conformidade com as normas nacionais e internacionais.', 3, NULL, NULL, NULL, '1 a 3 anos.', 'Ambientes industriais e agrícolas, com uso de EPIs e contato com vários setores.', 0, NULL, 'Função de alta complexidade. Exige domínio técnico e normativo, capacidade de liderar processos e equipes, tomada de decisão estratégica e constante atualização com padrões regulatórios nacionais e internacionais. Impacta diretamente na imagem da empresa, conformidade legal e satisfação do cliente.', 'Assegurar conformidade de insumos, processos e produtos. Supervisionar e acompanhar auditorias de certificação. Atualizar e implementar políticas e critérios de qualidade e certificação. Controlar documentos, laudos e formulários de rastreabilidade. Interagir com clientes, auditores, fornecedores e equipes internas. Elaborar relatórios de não conformidade, propor ações corretivas e preventivas. Orientar a organização, armazenamento e acondicionamento de insumos e produtos. Treinar colaboradores em normas e procedimentos de qualidade. Apoiar a comissão de trabalhadores e demais comissões técnicas. Promover e implementar melhorias contínuas. Envio e acompanhamento de relatórios de análises de laboratório.', '2025-10-17 15:57:40', '2026-04-13 17:16:52', 0, NULL, NULL, NULL),
(48, 'SUPERVISOR DE COMPRAS', '', 9, 'Responsável por planejar, coordenar e supervisionar os processos de aquisição de materiais e serviços, assegurando o abastecimento eficiente e com qualidade para os diversos setores da empresa. Também é responsável por supervisionar atividades de manutenção, obras e reformas, quando aplicável, garantindo o cumprimento de prazos, contratos e políticas internas.', 3, NULL, 4, NULL, '2 a 3 anos', 'A função é exercida predominantemente em ambiente administrativo, mas pode exigir visitas a campo, fornecedores, obras e feiras. O cargo envolve constante interação com áreas internas e externas, exigindo comunicação clara, ética, responsabilidade técnica e visão estratégica.', 0, NULL, 'Função de alta complexidade, exigindo domínio técnico de processos de compras, habilidades interpessoais, visão estratégica, capacidade analítica e liderança. A responsabilidade inclui decisões com impacto direto sobre orçamentos, contratos e a sustentabilidade das operações da empresa.', 'Supervisionar e executar processos de compras de materiais e serviços. Controlar orçamentos e verificar disponibilidade de verbas. Negociar preços, prazos e condições com fornecedores. 	Emitir e acompanhar pedidos de compras e entregas.	Avaliar fornecedores, realizar visitas técnicas e participar de feiras. Controlar processos de concorrência e contratos. Supervisionar equipe de compras e promover treinamentos. Participar do planejamento orçamentário.	Garantir o cumprimento das normas de compras e políticas internas.	Realizar reuniões de alinhamento e controle de performance da equipe. Racionalizar procedimentos e propor melhorias.', '2025-10-17 15:57:40', '2026-04-13 19:07:45', 0, NULL, NULL, NULL),
(49, 'SUPERVISOR DE PACKING HOUSE', '', 40, 'Supervisiona e coordena as atividades de recebimento, seleção, lavagem, classificação, embalagem, etiquetagem, armazenagem e expedição de frutas no Packing House, assegurando o cumprimento de normas técnicas, padrões de qualidade, segurança do trabalho e prazos operacionais. Garante a rastreabilidade dos processos, coordena equipes, promove melhorias contínuas e zela pela integridade dos produtos para mercados interno e externo.', 4, NULL, NULL, NULL, '1 a 2 anos', 'Atua em ambiente industrial (Packing House), sujeito a ruídos, umidade e variações térmicas (incluindo câmaras frias). Opera sob pressão, com picos de produção durante as safras, exigindo flexibilidade de horário e coordenação simultânea de múltiplas frentes operacionais. Interage com setores como produção, qualidade, logística e comercial, seguindo normas de segurança e exigências de mercado.', 0, NULL, 'Trata-se de um cargo de alta complexidade operacional e gerencial. Exige conhecimento técnico em cadeia de produção, logística e exportação, capacidade de liderança e tomada de decisões sob pressão. O desempenho inadequado pode impactar diretamente a qualidade do produto final, a satisfação do cliente e a certificação da empresa. Requer domínio de processos, senso de urgência, controle de indicadores e habilidades de gestão de pessoas.', 'Planejar, supervisionar e coordenar atividades de embalagem e expedição de frutas. Garantir o cumprimento das metas de produção e especificações dos clientes. Inspecionar qualidade, quantidade e conformidade das embalagens e produtos. Gerenciar equipe operacional, planejar férias e realizar treinamentos. Coordenar fluxo de entrada, saída e armazenamento dos produtos. Controlar perdas, desperdícios e condições de higiene. Preencher relatórios, planilhas e analisar indicadores de desempenho. Realizar auditorias internas e garantir rastreabilidade dos lotes. Requisitar materiais, controlar estoque de embalagens e insumos. Assegurar cumprimento das normas de segurança e boas práticas de fabricação.', '2025-10-17 15:57:40', '2026-04-17 13:46:01', 0, NULL, NULL, NULL),
(50, 'TÉCNICA EM ENFERMAGEM', '', 41, 'Executa atividades técnicas de enfermagem, prestando cuidados aos colaboradores, realizando procedimentos de saúde preventiva, controle de exames ocupacionais, administração de medicamentos, acompanhamento em exames, orientações de saúde e apoio em campanhas educativas, conforme normas técnicas, éticas e de biossegurança.', 4, NULL, 12, NULL, '6 a 12 meses de experiência prática.', 'Atua sob supervisão em ambientes internos e externos (áreas produtivas da empresa), com deslocamentos para visitas. Exige preparo psicológico, atenção constante, disponibilidade para turnos e uso contínuo de EPIs.', 0, NULL, 'Cargo de complexidade intermediária, requer conhecimento técnico especializado, habilidade para tomada de decisão em situações de urgência, domínio de protocolos de saúde ocupacional, capacidade de atuação integrada e competências comunicacionais.', 'Prestar cuidados diretos, executar procedimentos de enfermagem, administrar medicações, controlar exames admissionais, demissionais e periódicos, organizar campanhas de saúde,  treinamentos preventivos e registrar informações em prontuários. Prestar cuidados diretos ao colaborador.  Executar procedimentos de enfermagem conforme protocolo. Administrar medicações prescritas pelo médico ou profissional qualificado.  Promover ações educativas de saúde ocupacional. Monitorar condições de saúde e realizar visitas domiciliares a colaboradores quando necessário. Zelar pela organização e limpeza dos materiais e equipamentos utilizados.', '2025-10-17 15:57:40', '2026-04-13 14:51:15', 0, NULL, NULL, NULL),
(51, 'TÉCNICO DE INFORMÁTICA', '', 42, 'Responsável pela instalação, manutenção e suporte de hardware, software, redes de comunicação e sistemas de segurança. Gerencia servidores, configura redes com e sem fio, administra sistemas operacionais e realiza atendimento técnico aos usuários internos e externos. Executa atividades críticas para a infraestrutura de TI, garantindo disponibilidade, desempenho e segurança dos sistemas.', 4, NULL, NULL, NULL, '3 a 5 anos', 'O trabalho é realizado em salas de TI, oficinas, ambientes administrativos e áreas de instalação externa, podendo incluir acesso a locais de difícil alcance. Exige disponibilidade para intervenções emergenciais, plantões técnicos, interação com usuários de diversos setores e atuação em demandas simultâneas. Necessário uso de EPIs e cuidados com instalações elétricas, rede lógica e segurança de dados.', 0, NULL, 'Cargo técnico de média a alta complexidade que exige domínio de múltiplas áreas da informática, capacidade de análise e resolução de problemas, e responsabilidade sobre a segurança da informação e disponibilidade de sistemas críticos. Impactos decorrentes de falhas incluem perda de dados, interrupções operacionais e quebra de sigilo. Requer constante atualização e proatividade na prevenção de falhas e evolução da infraestrutura tecnológica.', 'Instalar, configurar e manter hardware, software e redes. Diagnosticar e corrigir falhas em equipamentos e sistemas. Executar manutenção preventiva e corretiva em equipamentos de TI e CFTV. Gerenciar contas de usuários, permissões e acessos. Executar rotinas de backup e restaurar dados quando necessário. Eliminar vírus e garantir uso de softwares homologados. Prestar suporte técnico presencial e remoto. Registrar e acompanhar chamados e incidentes. Realizar controle térmico, elétrico e de cabeamento. Desenvolver soluções para melhoria da infraestrutura de TI. Zelar pela integridade de dados e confidencialidade de informações sensíveis.', '2025-10-17 15:57:40', '2026-04-13 18:27:14', 0, NULL, NULL, NULL),
(52, 'TÉCNICO EM SEGURANÇA DO TRABALHO', '', 43, 'Executa e planeja ações preventivas de segurança, inspeciona ambientes, controla o uso de EPIs, ministra treinamentos e garante o atendimento às NRs e requisitos legais.', 4, NULL, 12, NULL, '6 meses a 2 anos em empresas agrícolas ou industriais.', 'Atuação em campo e ambientes administrativos. Exposição a intempéries, produtos químicos e situações de risco. Necessidade de deslocamento frequente entre unidades.', 0, NULL, 'Atua sob regime de supervisão, com atividades desenvolvidas tanto em ambientes internos quanto externos. Interage com diversos departamentos, exigindo presença frequente em áreas operacionais. O ambiente de trabalho é considerado adequado, podendo demandar atuação em campo.', 'O Técnico em Segurança do Trabalho é responsável por planejar, implantar e monitorar políticas e ações voltadas à saúde e segurança ocupacional. Atua de forma preventiva e corretiva na identificação e controle de riscos, coordena treinamentos, inspeciona ambientes e equipamentos, e assegura o cumprimento das normas regulamentadoras, visando garantir um ambiente de trabalho seguro, saudável e em conformidade com a legislação. Realiza vistorias, inspeções e análises de riscos.\r\nImplanta programas de prevenção de acidentes e doenças. Coordena treinamentos e ações educativas em SST. Gerencia e fiscaliza a aplicação das políticas e normas de segurança. Elabora relatórios técnicos, normas e procedimentos.	Acompanha auditorias e participa de perícias e fiscalizações. Controla o uso de EPIs e a destinação de resíduos. 	Atua em situações emergenciais, como simulações e evacuações. Participa do sistema de gestão de SST e ambiental. Promove melhorias contínuas no ambiente de trabalho.', '2025-10-17 15:57:40', '2026-04-13 14:39:59', 0, NULL, NULL, NULL),
(53, 'OPERADOR DE MÁQUINAS PESADAS', '', 44, 'Opera tratores com potência superior a 125cv e inferiores além de implementos agrícolas, sendo responsável pela regulagem e conservação dos equipamentos.', 2, NULL, 12, NULL, 'Experiência de 1 a 2 anos em operação de tratores com implementos diversos.', 'Atividades ao ar livre em condições climáticas variáveis, exposição a poeira, ruído, produtos químicos e biológicos. Exige atenção e capacidade física.', 0, NULL, 'Complexidade operacional intermediária com exigência de atenção, precisão e responsabilidade direta sobre maquinário.', 'Executar operações agrícolas com tratores e implementos com potência superior a 125cv e inferiores, equipamentos agrícolas e de precisão (pulverização, adubação, poda), motoniveladora, retroescavadeira, tratores de tração por esteira. Zelar pelos equipamentos, preencher relatórios, comunicar anomalias e cumprir protocolos de segurança.', '2025-10-17 15:57:40', '2025-11-07 17:20:54', 0, NULL, NULL, NULL),
(54, 'ENCARREGADO DE MAQUINAS E IMPLEMENTOS', '', 44, 'Responsável pela supervisão direta e acompanhamento diário das atividades mecanizadas em campo. Atua na distribuição de tarefas aos tratoristas, orienta a execução técnica das operações (plantio, pulverização, transporte), realiza o check-list imediato dos implementos e garante que as metas de produção diárias sejam cumpridas com segurança e zelo pelo maquinário.', 2, NULL, 9, NULL, '6 meses', 'Trabalho executado prioritariamente ao ar livre (100% campo). Exige grande esforço de supervisão itinerante, deslocando-se continuamente entre os tratores (geralmente de moto). O ambiente envolve exposição direta a ruído de motores, vibrações, poeira, sol e chuva. Requer disponibilidade para jornadas flexíveis em períodos de safra e plantio.', 0, NULL, 'A complexidade reside na gestão operacional imediata. O encarregado precisa ter agilidade para resolver problemas de percurso (ex: um pneu furado, uma mangueira rompida ou uma mudança repentina no clima) que impedem a operação de continuar. Exige domínio prático absoluto da operação e habilidade para liderar equipes que trabalham sob pressão por rendimento.', 'Supervisionar a operação em cada talhão conforme o cronograma recebido da coordenação. \r\nVerificar no campo se a profundidade do implemento, o alinhamento do plantio e a velocidade da máquina estão corretos.\r\nSupervisionar o estado de limpeza, lubrificação e conservação diária das máquinas e implementos antes e depois da jornada.\r\nAuxilar o superior na gestão de ponto, escalas de folga e comportamento da equipe de operadores sob sua supervisão direta.\r\nSupervisionar a chegada do comboio de combustível e insumos (caldas/adubos) para que os tratores não fiquem parados.\r\nOrientar operadores menos experientes sobre manobras seguras, uso de EPIs e operação correta de implementos específicos.\r\nPreencher diários de campo com horímetros de início e fim, relatando qualquer intercorrência mecânica à oficina.', '2025-10-17 15:57:40', '2026-03-13 19:06:40', 0, NULL, NULL, NULL),
(55, 'OPERADOR DE MÁQUINAS LEVES', '', 44, 'Responsável pela operação de tratores e implementos agrícolas com potência inferior a 125cv (pulverização, adubação, cultivo), garantindo eficiência, segurança e qualidade operacional.', 2, NULL, 12, NULL, 'Experiência prévia na função ou em atividades similares é valorizada.', 'Atua predominantemente em campo, exposto a condições climáticas adversas e riscos associados ao manuseio de produtos químicos e máquinas agrícolas. O trabalho envolve esforço físico moderado e alta responsabilidade.', 0, NULL, 'O cargo exige alto nível de responsabilidade e competência técnica para operar diferentes tipos de máquinas e implementos agrícolas, realizar ajustes e manutenções básicas, garantindo a segurança e a eficiência das atividades.', 'Programar e executar atividades com tratores, controlar painéis de comando, realizar preparo de calda de pulverização, aplicação de fertilizantes, transporte de produção, verificar níveis e realizar manutenções básica de equipamentos e ferramentas.', '2025-10-17 15:57:40', '2025-11-07 17:28:14', 0, NULL, NULL, NULL),
(56, 'PORTEIRO', '', 45, 'O profissional deve monitorar a entrada e saída de pessoas, controlar o acesso de veículos, seguir o regimento da empresa e zelar pelo cumprimento das normas de segurança.', 2, NULL, NULL, NULL, '2 a 3 meses.', 'Atua principalmente na guarita, em ambiente coberto e arejado, com acesso a recursos de comunicação e controle. O trabalho envolve atendimento ao público, abertura e fechamento de portões, monitoramento da movimentação de pessoas e veículos, com encaminhamento de visitantes e prestadores de serviço para a Recepção.', 0, NULL, 'O cargo exige atenção constante, capacidade de tomar decisões rápidas e eficazes, habilidade para lidar com situações de emergência e controle de acesso. Implica seguir normas internas e procedimentos de segurança.', 'Garantir controle de entrada e saída de pessoas e veículos, identificar visitantes, auxiliar no cumprimento com as normas de segurança. Receber materiais e mercadorias. Receber documentos e encaminhar para o administrativo. Manter atualizados os registros no livro de ocorrências.  Monitorar o circuito fechado de TV. Checar e solicitar manutenção de equipamentos de segurança.', '2025-10-17 15:57:40', '2026-04-17 12:29:44', 0, NULL, NULL, NULL),
(58, 'COORDENADOR DE CAMPO', '', 2, 'Planeja e supervisiona atividades agrícolas operacionais, coordenando equipes, controlando insumos, metas e a qualidade das práticas no campo.', 4, NULL, 3, 46, 'Experiência mínima de 2 anos.', 'Ambiente externo, sujeito a variações climáticas, metas e prazos.', 0, NULL, 'Alta complexidade técnica e de liderança em múltiplas frentes operacionais.', 'Supervisionar atividades agrícolas, coordenar equipe, controlar insumos, garantir normas e metas.', '2025-10-31 13:17:48', '2025-10-31 14:14:32', 0, NULL, NULL, NULL),
(59, 'GERENTE ADMINISTRATIVO (CÓPIA)', '', 26, 'Gerencia as áreas administrativa, operacional e financeira, suporta a diretoria na estratégia e é responsável pela gestão de contratos e resolução de conflitos.', 5, NULL, NULL, NULL, 'Experiência: 3 a 5 anos em gestão. Fluência em inglês valorizada.', 'Ambiente fechado, sob supervisão eventual, com rotina de prazos e decisões estratégicas.', 0, NULL, 'Alta. Impacto direto nos resultados e demandas intersetoriais.', 'Gerenciar processos administrativos, apoiar diretoria, controlar orçamento e contratos, liderar equipe e garantir conformidade institucional.', '2025-10-31 17:58:10', '2025-10-31 17:58:10', 0, NULL, NULL, NULL),
(60, 'AUXILIAR DE SERVIÇOS GERAIS LABORATÓRIO', '', 17, 'Realiza limpeza, higienização e organização em ambientes do laboratório de campo e do packing. Além de prestar auxilio em demais atividades do laborátorio.', 3, NULL, 11, 26, 'Experiência prática é um diferencial.', 'Ambientes internos e externos com esforço físico, exposição a produtos químicos de limpeza, fungos e bactérias (entomopatogênicas), trabalho em turnos alternados.', 0, NULL, 'Cargo operacional com tarefas padronizadas, exige responsabilidade e atenção às normas de biossegurança.', 'Limpeza, higienização, organização e apoio ao laboratório e ambientes afins, reposição de materiais de higiene e coleta e descarte de resíduos.', '2025-11-07 18:26:17', '2025-11-07 18:31:42', 0, NULL, NULL, NULL),
(61, 'ASSISTENTE FINANCEIRO', '', 620124, 'Executa rotinas financeiras e administrativas de contas a pagar/receber, conciliação bancária e organização de documentos financeiros.', 3, NULL, 10, NULL, '6 meses a 1 ano.', 'Atua em ambiente corporativo, com uso intensivo de computador, planilhas, softwares financeiros e sistemas de gestão integrada. Exige habilidade para lidar com dados confidenciais e atuar sob pressão para cumprimento de prazos e metas. Interage com diretoria, setor contábil, jurídico e instituições financeiras.', 0, NULL, 'O cargo exige conhecimento técnico em rotinas financeiras e administrativas, atenção aos detalhes e cumprimento de prazos. Embora seja considerado nível operacional, a exatidão e confiabilidade das informações processadas impactam diretamente nos resultados da empresa. Requer comprometimento, ética e boa capacidade de organização.', 'Registrar e controlar documentos administrativos e financeiros. Realizar lançamentos contábeis e financeiros. Apoiar nos processos de contas a pagar e a receber. Emitir e preencher relatórios, planilhas e formulários. Realizar conciliação bancária e fluxo de caixa. Organizar arquivos físicos e digitais do setor financeiro. Solicitar e digitalizar documentos conforme necessidade.   Manter atualizado o cadastro de fornecedores e clientes.  Prestar informações e suporte à equipe administrativa. Zelar pela organização e confidencialidade das informações tratadas', '2025-11-07 18:57:47', '2026-04-13 19:48:11', 0, NULL, NULL, NULL),
(62, 'SUPERVISOR FISCAL', '', 6, 'Supervisiona atividades contábeis e fiscais, garantindo que a empresa opere dentro das normas tributárias e contábeis. Responsável por emitir notas fiscais, organizar, conferir e registrar documentos, apurar impostos (ICMS, PIS, Cofins, diferencial de alíquota), controlar obrigações acessórias e transmitir informações para a contabilidade, contribuindo para a conformidade legal e a gestão tributária da empresa.', 5, NULL, 4, NULL, '1 ano', 'Atua em ambiente administrativo, em escritório climatizado, utilizando intensivamente computador, sistemas de gestão contábil e materiais de escritório. O trabalho é realizado em horário comercial, com prazos rígidos e interação com equipes fiscal, contábil e financeira, estando sujeito a demandas de auditorias e fiscalizações.', 0, NULL, 'O cargo apresenta complexidade técnica e administrativa, exigindo atenção a detalhes, domínio das normas fiscais e contábeis, capacidade de lidar com múltiplas demandas simultâneas e prazos curtos. Requer comunicação eficaz, raciocínio lógico e atualização constante para evitar penalidades e garantir a conformidade legal da empresa.', 'Emitir notas fiscais e conferir documentos fiscais. Organizar e arquivar documentos fiscais e contábeis. Apurar impostos e garantir a correta escrituração contábil. Controlar e enviar obrigações acessórias. Elaborar relatórios gerenciais e demonstrativos de resultados. Transmitir informações fiscais para a contabilidade. Acompanhar atualizações da legislação tributária aplicável. Supervisionar colegas de trabalho e promover ambiente colaborativo. Garantir o controle de drawback para exportação. Evitar multas por cálculos errados de impostos. Atender auditorias e fiscalizações com informações precisas.', '2025-11-07 19:18:17', '2026-04-13 19:27:38', 0, NULL, NULL, NULL),
(63, 'AUXILIAR FISCAL', '', 6, 'Auxilia nas rotinas fiscais, como emissão de notas fiscais, apuração de impostos e controle de obrigações acessórias, garantindo a conformidade legal. Sob supervisão.', 3, NULL, 11, NULL, '3 meses', 'Atua sob supervisão em ambiente administrativo, em escritório climatizado, podendo utilizar computador e sistemas de gestão contábil. Trabalho realizado em horário comercial, com prazos rígidos e interação com equipes fiscal, contábil e financeira.', 0, NULL, 'O cargo apresenta baixa complexidade técnica e administrativa, exige atenção a detalhes e as normas fiscais, capacidade de lidar com múltiplas demandas e prazos curtos. Desejável raciocínio lógico e habilidade com sistemas informatizados.', 'Auxliar na emissão de notas fiscais, conferir documentos, organizar e arquivar documentos, controlar e enviar obrigações acessórias, editar e ajustar relatórios, transmitir informações fiscais para a contabilidade. E demais atividaes administrativas seguindo a orientação do supervisor.', '2025-11-07 19:24:34', '2026-04-13 19:33:28', 0, NULL, NULL, NULL),
(64, 'COMPRADOR', '', 9, 'Realiza cotações, negocia com fornecedores e emite pedidos de compra, garantindo o cumprimento dos prazos e a conformidade dos processos.', 3, NULL, 12, NULL, '1 a 2 anos', 'Atua predominantemente em ambiente de escritório, com uso de computador e sistemas de compras. Interage constantemente com fornecedores, almoxarifado, setor fiscal e transporte. Demanda atenção concentrada e capacidade de lidar com múltiplas tarefas simultaneamente.', 0, NULL, 'O cargo exige domínio técnico-administrativo das rotinas de compras, com capacidade de análise crítica, negociação e organização. Erros podem impactar diretamente os fluxos logísticos e operacionais.', 'Receber requisições e validar especificações, realizar cotações e montar planilhas comparativas, negociar preços/prazos/condições, emitir e acompanhar pedidos, conferir mercadorias recebidas e encaminhar documentos fiscais para o setor contábil/fiscal.', '2025-11-07 19:43:57', '2026-04-13 19:15:39', 0, NULL, NULL, NULL),
(65, 'Agrônomo', '', 620111, 'Responsável técnico por planejar, organizar e supervisionar todas as etapas da produção agrícola. Atua na gestão do manejo de solo, controle fitossanitário, nutrição de plantas e colheita, visando a otimização da produtividade, a qualidade do fruto e a sustentabilidade econômica e ambiental da operação', 5, NULL, NULL, NULL, '2 anos', 'Atuação mista (escritório e campo), com exposição direta a condições climáticas (sol/chuva) e deslocamentos frequentes entre talhões.', 0, NULL, 'Autonomia plena para decisões técnicas de alto impacto financeiro e biológico.', 'Elaborar e supervisionar cronogramas de tratos culturais (poda, adubação, pulverização).\r\nRealizar diagnósticos de pragas e doenças, prescrevendo o receituário agronômico necessário.\r\nProjetar e gerir sistemas de irrigação e fertirrigação para garantir a eficiência hídrica.\r\nCoordenar experimentos de campo para teste de novas variedades ou insumos.\r\nMonitorar indicadores de produtividade e qualidade do solo (análises químicas e físicas).\r\nGarantir a conformidade com certificações (Global G.A.P., Orgânicos, etc.) e normas de segurança (NR-31).', '2026-03-10 15:02:15', '2026-03-13 16:41:25', 0, NULL, NULL, NULL),
(66, 'COORDENADOR DE CAMPO (COPIAR DADOS DO AGRONOMO)', '', 1, 'O Coordenador de Campo é o elo estratégico entre o Engenheiro Agrônomo e as frentes operacionais. É responsável por gerenciar múltiplos encarregados e turmas (12 a 13 pessoas por frente), garantindo o cumprimento do cronograma de colheita e tratos culturais. Garante a produtividade, a qualidade técnica dos serviços e a segurança das equipes, além de realizar a gestão imediata de recursos e conflitos no campo.', 4, NULL, NULL, NULL, '1 ano', '', 0, NULL, '', '', '2026-03-10 15:02:21', '2026-03-13 16:46:16', 0, NULL, NULL, NULL),
(67, 'MECÂNICO DE MOTOR 2 TEMPOS', '', 620121, 'Responsável pela manutenção preventiva e corretiva de máquinas e equipamentos equipados com motores de 2 tempos (como sopradores e atomizadores costais, principalmente roçadeiras e motosserras). Atua no diagnóstico de falhas, limpeza técnica, regulagem de carburação, substituição de componentes desgastados e garantia da vida útil dos equipamentos essenciais à produção.', 3, NULL, NULL, NULL, '6 meses', 'Trabalho em oficina mecânica, com constante deslocamentos para socorro mecânico no campo.', 0, NULL, 'Trabalho técnico manual que exige concentração e uso constante de equipamentos de proteção individual. A complexidade reside na especificidade dos motores de 2 tempos, que operam em altas rotações e dependem de uma química precisa de lubrificação. O erro no diagnóstico ou na mistura de combustível pode resultar na perda total do equipamento (motor fundido) e na interrupção de cronogramas críticos de aplicação no campo.', 'Manutenção Corretiva: Desmontar, diagnosticar e reparar motores de 2 tempos, realizando a troca de pistões, anéis, juntas e retentores.\r\n\r\nRegulagem Técnica: Ajustar carburação e sistemas de ignição para garantir o desempenho máximo com menor consumo de combustível.\r\n\r\nManutenção de Equipamentos Costais: Revisar sistemas de bombeamento e mangueiras de atomizadores utilizados na aplicação de defensivos.\r\n\r\nGestão de Insumos: Orientar a equipe operacional sobre a mistura correta de óleo 2 tempos e combustível para evitar o travamento (\"colagem\") dos motores.\r\n\r\nControlo de Inventário: Solicitar peças de reposição e manter a organização de ferramentas e bancadas da oficina.\r\n\r\nSegurança: Garantir que todos os equipamentos reparados tenham seus itens de proteção e segurança (travas, protetores) em perfeito estado.', '2026-03-10 15:02:21', '2026-03-13 17:01:06', 0, NULL, NULL, NULL),
(68, 'MECÂNICO CHEFE', '', 620129, 'Líder técnico da oficina mecânica, responsável por garantir a disponibilidade e a confiabilidade da frota de tratores, máquinas pesadas e equipamentos agrícolas. Atua no diagnóstico de falhas complexas (hidráulica, transmissão e motores), coordena a equipa de mecânicos e auxiliares, e planeja as manutenções preventivas e corretivas para minimizar o tempo de máquina parada no campo.', 4, NULL, 9, NULL, '2 anos', '', 0, NULL, 'A complexidade é elevada devido à responsabilidade sobre ativos de alto valor e ao impacto direto na produção. Um erro de diagnóstico do Mecânico Chefe pode resultar numa quebra catastrófica de motor ou transmissão, gerando prejuízos financeiros significativos e paralisando operações críticas como o plantio ou a pulverização.', '', '2026-03-10 15:02:21', '2026-03-13 17:11:39', 0, NULL, NULL, NULL),
(69, 'PREPARADOR DE CALDA', '', 18, 'Responsável pelo preparo técnico das misturas de defensivos agrícolas, fertilizantes foliares e bioinsumos (caldas) que serão aplicadas nas culturas. Atua na dosagem precisa dos produtos conforme a receita agronômica, opera sistemas de mistura e bombagem, e garante a correta higienização dos equipamentos e embalagens, seguindo rigorosamente as normas de segurança e proteção ambiental.', 2, NULL, NULL, NULL, '6 meses', 'Estação de mistura (ponto de captação de água), ambiente aberto, exposto a odores químicos e humidade.', 0, NULL, 'A complexidade reside na periculosidade do manuseio de químicos e na precisão necessária para a mistura. Um erro do Preparador de Calda pode invalidar todo o investimento de uma aplicação mecanizada ou, em casos graves, causar a morte de talhões inteiros por excesso de concentração de produto.', 'Trabalho técnico-operacional que exige rigoroso seguimento de protocolos e horários para não atrasar a frota de pulverização.', '2026-03-10 16:43:26', '2026-03-13 17:47:50', 0, NULL, NULL, NULL),
(70, 'APICULTOR', '', 620141, 'Responsável por manejar abelhas (apis e/ou melíponas), de forma segura em meio as culturas, mantendo-as longe dos pomares produtivos ou em áreas seguras para os colaboradores. Atua no monitoramento e manejo dos enxames, colheita de produtos apícolas e manutenção preventiva do apiário e equipamentos.', 1, NULL, 12, NULL, '1 ano', 'Áreas de reserva e talhões de produção. Trabalho executado a céu aberto.', 0, NULL, 'A complexidade reside na biologia sensível das abelhas e na necessidade de coordenar o manejo apícola de acordo com as necessidade das equipes de campo. O Apicultor deve garantir a segurança dos colaboradore ao tempo que deve permitir que as abelhas sobrevivam e polinizem a cultura, o que exige um planejamento técnico e refinado com a equipe de campo.', 'Pode exigir horários diferenciados (início da manhã ou final da tarde) para deslocamento de colmeias com menor stress térmico. Deslocamentos emergênciais para deslocamento de colméia.', '2026-03-10 16:43:26', '2026-03-13 18:14:13', 0, NULL, NULL, NULL),
(71, 'COORDENADOR DE MAQUINAS E IMPLEMENTOS', '', 24, 'Responsável pela gestão estratégica e operacional de toda a frota de tratores, máquinas e implementos agrícolas da unidade. Coordena a Oficina, atua no planejamento da logística de máquinas entre as frentes de trabalho, coordena os encarregados de tratoristas, supervisiona os indicadores de rendimento operacional e garante a execução rigorosa do plano de manutenção preventiva e corretiva, visando a máxima disponibilidade e longevidade dos ativos.', 4, NULL, 3, NULL, '2 anos', 'O trabalho é exercido em regime misto, com forte presença em campo e atividades administrativas de controle em escritório. Exige mobilidade constante para deslocamento entre as frentes de produção. O ambiente de campo expõe o profissional a ruídos, vibrações, poeira e variações climáticas. Requer alta responsabilidade, autonomia para interrupção de operações inseguras e disponibilidade para períodos de safra e plantio.', 0, NULL, 'Nível: Alto\r\nO cargo exige visão sistêmica para equilibrar a urgência da produção agrícola com a necessidade técnica de preservação das máquinas. A complexidade reside na coordenação de múltiplas frentes de trabalho simultâneas e na tomada de decisão sobre investimentos em reparos de alto valor. O erro na coordenação pode gerar gargalos logísticos severos, quebra prematura de ativos caros ou atrasos irreversíveis em janelas de plantio e pulverização.', 'Coordenar a movimentação e o posicionamento estratégico de máquinas e implementos nos talhões para otimizar o tempo de operação.\r\nSupervisionar o trabalho dos Encarregados de Tratorista, garantindo que as diretrizes técnicas e de segurança sejam repassadas aos operadores.\r\nMonitorar horímetros, consumo de combustível e rendimento (hectares/hora), elaborando relatórios consolidados para a gerência.\r\nValidar as necessidades de paradas para manutenção, priorizando ordens de serviço críticas junto ao Mecânico Chefe.\r\nVerificar periodicamente a conservação dos implementos (lubrificação, estado de desgaste de pontas, regulagens) e o cumprimento do check-list pelos operadores.\r\nGarantir que todos os operadores estejam habilitados e treinados nas normas de segurança (NR-31) e no uso de tecnologias embarcadas (GPS, pilotos automáticos).\r\nCoordenar o comboio de abastecimento e a logística de entrega de caldas/fertilizantes para evitar interrupções no campo.\r\nSupervisionar Operações da Oficina Mecânica garantindo o devido andamento das manutenções para evitar paradas no campo.', '2026-03-10 16:43:26', '2026-03-13 18:43:50', 0, NULL, NULL, NULL),
(72, 'SUPERVISOR DE MÁQUINAS E IMPLEMENTOS', '', 44, 'Responsável por supervisão e planejamento operacional de toda a frota de tratores, máquinas e implementos agrícolas das unidades. Atua no planejamento da logística de máquinas entre as frentes de trabalho, supervisona os encarregados de tratoristas, supervisiona os indicadores de rendimento operacional e garante a execução rigorosa do plano de manutenção preventiva e corretiva, visando a máxima disponibilidade e longevidade dos ativos. Presta suporte operacional e logístico ao superior.', 3, NULL, NULL, NULL, '1 anos', 'O trabalho é exercido em regime misto, com forte presença em campo e atividades administrativas de controle em escritório. Exige mobilidade constante para deslocamento entre as frentes de produção (uso de moto ou veículo de apoio). O ambiente de campo expõe o profissional a ruídos, vibrações, poeira e variações climáticas. Requer alta responsabilidade, autonomia para interrupção de operações inseguras e total disponibilidade para períodos de safra.', 0, NULL, 'O cargo exige visão sistémica para equilibrar a urgência da produção agrícola com a necessidade técnica de preservação do patrimônio. A complexidade reside na supervisão de múltiplas frentes de trabalho simultâneas. Coleta e Revisão de Apontamentos Agrícolas. O erro na supervisão pode gerar gargalos logísticos severos, quebra prematura de ativos caros ou atrasos irreversíveis em janelas de plantio e pulverização.', 'Supervisionar a movimentação e o posicionamento estratégico de tratores e implementos nos talhões para garantir a otimização e o tempo de operação.\r\nValidar paradas para manutenção preventiva e corretiva, priorizando ordens de serviço críticas junto ao superior.\r\nMonitorizar horímetros, consumo de combustível e rendimento operacional (hectares/hora), elaborando relatórios para a gerência.\r\nVerificar periodicamente a conservação dos implementos (lubrificação, desgaste de pontas, regulagens) e o cumprimento do check-list pelos operadores.\r\nSupervisionar o comboio de abastecimento em campo e a logística de entrega de caldas/fertilizantes para evitar que as máquinas parem por falta de produto.\r\nGarantir que todos os operadores estejam habilitados e treinados nas normas de segurança (NR-31) e no uso de tecnologias embarcadas (GPS, pilotos automáticos).\r\nSupervisionar os Encarregados de Tratorista, garantindo que as diretrizes técnicas sejam repassadas corretamente às equipes de campo.', '2026-03-10 16:43:26', '2026-03-13 18:54:20', 0, NULL, NULL, NULL),
(73, 'COSTUREIRA', '', 620115, 'Responsável por executar serviços de costura, reparo e ajuste de uniformes, vestimentas de proteção e materiais têxteis utilizados nas operações de campo e packing. Atua na operação de máquinas de costura (reta, overloque, etc.), realizando produção, acabamentos, substituição de aviamentos e reforço de costuras em materiais diversos incluindo de alta resistência, visando garantir o conforto e a segurança dos colaboradores.', 1, NULL, NULL, NULL, '6 meses', 'O trabalho é exercido em ambiente fechado (ateliê ou sala de costura), geralmente sentado por longos períodos. O ambiente exige boa iluminação e ventilação. A profissional interage com o setor de Almoxarifado para recebimento de materiais e com os diversos setores da empresa para entrega. Requer paciência, detalhismo e organização.', 0, NULL, 'A complexidade reside na habilidade manual e na diversidade de tecidos gerenciados (desde tecidos leves de uniformes até brim pesado ou lonas). Exige precisão no acabamento para evitar desperdícios de material e sensibilidade técnica para identificar quando uma peça deve ser recuperada ou descartada por desgaste excessivo.', 'Realizar costuras de união, acabamento e reparos em uniformes (calças, camisas, jalecos) e acessórios têxteis.\r\nExecutar consertos em vestimentas de aplicação e proteções de tecido que não comprometam a segurança técnica do item.\r\nOperar e zelar pela manutenção básica de máquinas de costura industriais e domésticas, realizando a troca de agulhas e lubrificação.\r\nRiscar e cortar tecidos conforme moldes ou medidas específicas para ajustes e reformas.\r\nControlar o estoque de linhas, botões, zíperes e elásticos, solicitando reposição conforme a necessidade.\r\nInspecionar as peças finalizadas para garantir que as costuras estão reforçadas e sem defeitos que possam causar desconforto ou rasgos prematuros.', '2026-03-10 16:43:26', '2026-03-13 19:56:40', 0, NULL, NULL, NULL),
(74, '[*] Agrônomo', NULL, 620111, 'Gestão técnica e supervisão de culturas.', 4, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-03-10 16:49:01', '2026-03-10 16:49:01', 0, NULL, NULL, NULL),
(75, '[*] Mecânico de Motor 2Tempos', NULL, 620121, NULL, 3, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-03-10 16:49:01', '2026-03-10 16:49:01', 0, NULL, NULL, NULL),
(76, '[*] Mecânico Chefe (Sênior)', NULL, 620121, NULL, 3, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-03-10 16:49:01', '2026-03-10 16:49:01', 0, NULL, NULL, NULL),
(77, 'ENCARREGADO DE VIVEIRO', '', 1, 'Responsável pela supervisão direta e acompanhamento das atividades rotineiras do viveiro, coordenando equipes operacionais para garantir a produtividade e a qualidade técnica dos serviços realizados. Atua na execução do planejamento semanal, orientando o manejo de mudas e a manutenção das instalações.', 3, NULL, NULL, NULL, '6 meses', 'Trabalho realizado em estufa, com alta responsabilidade e cobrança por resultados de qualidade. O exercício da função exige supervisão contínua das equipes e planejamento operacional diário. Requer resistência física para atuação sob diversas condições climáticas e mobilidade constante entre as casas de vegetação.', 0, NULL, 'A complexidade reside na necessidade de conciliar a gestão de pessoas com o rigor técnico exigido para a nutrição e proteção das plantas. O encarregado deve ter agilidade para identificar pragas ou falhas nutricionais e capacidade de liderança para manter o ritmo de trabalho da equipe sob condições de estufa.', 'Orientar e acompanhar as frentes de trabalho nas atividades diárias do viveiro.\r\nDesignar as atividades para os auxiliares e operadores conforme o cronograma de trabalho.\r\nEnsinar técnicas de trabalho, manejo de plantas e normas de segurança para a equipe.\r\nRealizar o controle de qualidade agropecuária nas mudas e monitorar os índices de produção.\r\nSupervisionar a higienização das instalações, ferramentas e a lavagem de EPIs de aplicação.\r\nAuxiliar o superior na gestão de ponto, escalas de folga e comportamento da equipe de operadores sob sua supervisão direta.\r\nCoordenar a preparação de substrato, caldas para pulverização e os tratos culturais no viveiro.\r\nEfetuar o preenchimento detalhado do diário de campo com as atividades realizadas.', '2026-03-10 16:49:01', '2026-03-13 20:19:45', 0, NULL, NULL, NULL),
(78, 'SUPERVISOR DE OBRAS E MANUTENCAO', '', 1, 'Responsável pela supervisão de obras e serviços de manutenção e construção civil, coordenando equipes de pedreiros e auxiliares, organizando tarefas, acompanhando cronogramas, controlando materiais e garantindo a qualidade e segurança das atividades. Executa atividades manuais, interpreta projetos e ordens de serviço, e assegura que os processos atendam às normas técnicas e regulamentadoras.', 3, NULL, NULL, NULL, '6 meses', 'O trabalho é realizado em ambientes de construção, sob diferentes condições climáticas, incluindo calor, poeira e umidade. Há exposição a áreas de risco e necessidade de trabalho em altura. Exige deslocamento entre frentes de serviço, uso constante de EPIs e cumprimento de normas técnicas, de saúde, segurança e meio ambiente.', 0, NULL, 'O cargo exige domínio técnico-operacional e capacidade de liderança simultânea. A complexidade está na coordenação de equipes, leitura de projetos, execução de serviços manuais, e garantia do cumprimento de normas de qualidade e segurança. A tomada de decisão rápida em situações de risco, o controle de prazos e a administração de conflitos fazem parte da rotina. Erros podem comprometer a segurança da obra, gerar prejuízos financeiros e afetar a continuidade das atividades empresariais.', 'Interpretar ordens de serviço, projetos e especificações técnicas.  Planejar e distribuir tarefas à equipe de pedreiros e auxiliares. Especificar e requisitar materiais e insumos para as obras. Acompanhar execução e produtividade das frentes de trabalho. Operar máquinas, preparar concreto e argamassa, executar acabamentos. Registrar atividades realizadas e preencher relatórios operacionais.  Garantir o descarte correto de resíduos conforme normas ambientais. Supervisionar montagem e desmontagem de andaimes, chapisco, reboco, revestimento, construções e demolições. Manter a organização, segurança e limpeza dos canteiros de obra. Controlar prazos, custos operacionais e conformidade com normas de segurança e qualidade.', '2026-03-10 16:49:01', '2026-04-17 11:51:47', 0, NULL, NULL, NULL),
(79, '[*] Encarregado de Tratos Culturais', NULL, 1, NULL, 3, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-03-10 16:49:01', '2026-03-10 16:49:01', 0, NULL, NULL, NULL),
(80, '[*] ENCARREGADO DE TRATORISTA', NULL, 1, NULL, 3, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-03-10 16:49:01', '2026-03-10 16:49:01', 0, NULL, NULL, NULL);
INSERT INTO `cargos` (`cargoId`, `cargoNome`, `cargoDescricao`, `cboId`, `cargoResumo`, `escolaridadeId`, `faixaId`, `nivelHierarquicoId`, `cargoSupervisorId`, `cargoExperiencia`, `cargoCondicoes`, `is_revisado`, `data_revisao`, `cargoComplexidade`, `cargoResponsabilidades`, `cargoDataCadastro`, `cargoDataAtualizacao`, `tem_piso_salarial`, `piso_valor`, `piso_lei_numero`, `piso_data_base`) VALUES
(81, 'SUPERVISOR DE ALMOXARIFE', '', 3, 'Responsável por planejar, coordenar e supervisionar todas as operações do almoxarifado da unidade. Garante a integridade e acuracidade do inventário (insumos agrícolas, peças de reposição e EPIs), coordena a equipe de almoxarifes, valida processos de recepção e expedição, e assegura o suporte logístico eficiente para as áreas de Campo, Oficina e Produção, mantendo o rigoroso cumprimento das normas de segurança e certificações de qualidade.', 3, NULL, NULL, NULL, '1 ano', 'O trabalho é realizado em regime misto, com atividades administrativas em escritório e supervisão direta no galpão de armazenamento. O ambiente pode apresentar variações de temperatura e poeira. Exige alta responsabilidade com informações confidenciais (valores de notas fiscais e custos de fornecedores) e autonomia para aprovação de pedidos de reposição e liberação de materiais.', 0, NULL, 'A complexidade reside na gestão de uma grande diversidade de itens com diferentes requisitos de armazenamento (químicos, peças mecânicas, materiais de escritório). Exige visão sistêmica para não permitir interrupções na produção por falta de materiais e agilidade na resolução de falhas em notas fiscais ou divergências de valores. O erro na supervisão pode gerar prejuízos financeiros diretos por perdas de produtos ou paradas das operações no campo.', 'Supervisionar inventários cíclicos e anuais, garantindo divergência zero entre o estoque físico e o sistema ERP.\r\nCoordenar e orientar os almoxarifes, distribuindo tarefas, monitorando metas de organização e promovendo o treinamento contínuo.\r\nAssegurar que toda a documentação de insumos (rastreabilidade, notas fiscais, certificados) esteja organizada para auditorias.\r\nValidar requisições de compra baseadas no ponto de equilíbrio do estoque e intermediar o contato com o setor de Compras e fornecedores.\r\nDefinir o layout de armazenamento e o endereçamento de materiais para otimizar a movimentação (Método PEPS – Primeiro que Entra, Primeiro que Sai).\r\nFiscalizar o uso de EPIs, o manuseio seguro de produtos químicos/pesados e o descarte correto de embalagens vazias e resíduos.\r\nGarantir o sigilo de informações estratégicas (custos de fornecedores e valores de NF) e a precisão técnica nos lançamentos do sistema.\r\nGarantir o controle e preenchimento adequado das fichas de EPI\'s dos colaboradores, afim de manter a organanização e o cumprimento de requisistos legais e de auditorias.', '2026-03-10 16:49:01', '2026-03-13 19:24:05', 0, NULL, NULL, NULL),
(82, '[*] Costureira', NULL, 620115, NULL, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-03-10 16:49:01', '2026-03-10 16:49:01', 0, NULL, NULL, NULL),
(83, 'CEO (Chief Executive Officer)', '', 620132, '', 5, NULL, NULL, NULL, '', '', 0, NULL, '', '', '2026-04-13 13:44:31', '2026-04-13 13:44:41', 0, NULL, NULL, NULL),
(84, 'AUXILIAR DE DESCONTAMINAÇÃO AGRÍCOLA', '', 17, 'Executa atividades de manutenção e organização rotineiras como: limpeza, higienização e organização de ambientes internos e externos da empresa, incluindo áreas operacionais, administrativas e sanitárias, incluindo manusear, lavar e descontaminar equipamentos, vestimentas e locais, utilizados na aplicação de agrotóxicos e defensivos agrícolas. Realiza coleta e descarte de resíduos, abastecimento de materiais de higiene. Aplica procedimentos de limpeza leve e pesada, observando padrões de segurança, higiene e conservação.', 10, NULL, 12, NULL, '6 meses', 'Atua em ambientes internos e externos, com variações térmicas  e de umidade. A rotina pode envolver turnos diurnos e noturnos, com esforço físico moderado, postura em pé prolongada, movimentação de cargas leves e execução de tarefas repetitivas. Requer o uso obrigatório de EPIs, sinalização adequada de limpeza e atenção ao manuseio de produtos químicos e equipamentos de limpeza.', 0, NULL, 'Trata-se de um cargo operacional com execução padronizada de tarefas de limpeza, conservação e apoio à rotina interna. Apesar da baixa complexidade técnica, o cargo exige responsabilidade, atenção a normas de segurança e higiene, capacidade física e disposição para realizar atividades diversas e contínuas. O desempenho impacta diretamente a imagem, salubridade e bem-estar dos demais colaboradores e visitantes.', 'Executar a limpeza leve e pesada de áreas operacionais e banheiros, além de manusear, lavar e descontaminar equipamentos, vestimentas e locais, utilizados na aplicação de agrotóxicos e defensivos agrícolas. Coletar resíduos e descartar conforme orientação técnica e ambiental. Repor materiais de higiene (papel, sabão, álcool gel). Verificar validade e diluição correta dos produtos de limpeza. Utilizar corretamente os EPIs e zelar pelo uso adequado dos produtos e equipamentos. Controlar o estoque de materiais de limpeza e comunicar necessidades de reposição. Sinalizar as áreas em higienização e seguir rotinas operacionais de limpeza. Executar tarefas noturnas conforme escala de revezamento. Participar de capacitações e seguir normas internas de segurança e higiene.', '2026-04-17 14:49:01', '2026-04-17 14:54:48', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargos_area`
--

CREATE TABLE `cargos_area` (
  `cargoAreaId` int(5) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `areaId` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cargos_area`
--

INSERT INTO `cargos_area` (`cargoAreaId`, `cargoId`, `areaId`) VALUES
(208, 1, 32),
(852, 2, 20),
(853, 2, 21),
(854, 2, 28),
(855, 2, 33),
(95, 3, 1),
(96, 3, 3),
(97, 3, 29),
(628, 4, 1),
(629, 4, 7),
(630, 4, 17),
(722, 6, 1),
(723, 6, 8),
(115, 7, 36),
(616, 8, 1),
(617, 8, 2),
(618, 8, 6),
(711, 9, 1),
(712, 9, 9),
(713, 9, 35),
(34, 10, 1),
(35, 10, 6),
(36, 10, 10),
(38, 10, 12),
(37, 10, 13),
(39, 10, 39),
(347, 11, 2),
(348, 11, 35),
(643, 12, 1),
(644, 12, 7),
(645, 12, 17),
(234, 13, 27),
(128, 14, 2),
(73, 15, 29),
(811, 16, 2),
(812, 16, 15),
(813, 16, 16),
(809, 17, 2),
(810, 17, 15),
(897, 18, 2),
(898, 18, 24),
(899, 18, 33),
(744, 20, 1),
(745, 20, 4),
(45, 23, 32),
(529, 24, 1),
(530, 24, 10),
(108, 26, 36),
(8, 27, 32),
(908, 29, 1),
(909, 29, 2),
(40, 31, 27),
(493, 33, 1),
(494, 33, 2),
(495, 33, 4),
(496, 33, 6),
(497, 33, 7),
(498, 33, 8),
(499, 33, 9),
(500, 33, 10),
(501, 33, 11),
(502, 33, 12),
(503, 33, 13),
(504, 33, 14),
(505, 33, 16),
(506, 33, 23),
(507, 33, 24),
(508, 33, 25),
(509, 33, 26),
(510, 33, 35),
(732, 34, 1),
(733, 34, 4),
(874, 35, 1),
(875, 35, 2),
(876, 35, 15),
(877, 35, 16),
(878, 35, 17),
(879, 35, 18),
(33, 36, 33),
(260, 38, 31),
(74, 39, 29),
(829, 40, 2),
(830, 40, 3),
(831, 40, 35),
(30, 41, 21),
(756, 42, 1),
(757, 42, 3),
(682, 43, 1),
(683, 43, 11),
(570, 44, 1),
(76, 45, 29),
(56, 46, 3),
(601, 47, 1),
(602, 47, 2),
(603, 47, 6),
(690, 48, 1),
(691, 48, 9),
(847, 49, 2),
(848, 49, 15),
(849, 49, 16),
(850, 49, 17),
(851, 49, 18),
(580, 50, 1),
(581, 50, 10),
(582, 50, 12),
(666, 51, 1),
(667, 51, 2),
(668, 51, 3),
(669, 51, 10),
(564, 52, 1),
(565, 52, 3),
(566, 52, 22),
(567, 52, 24),
(568, 52, 35),
(323, 54, 3),
(324, 54, 34),
(86, 55, 3),
(770, 56, 1),
(771, 56, 14),
(18, 58, 39),
(136, 60, 36),
(740, 61, 1),
(741, 61, 4),
(718, 62, 1),
(719, 62, 8),
(726, 63, 1),
(727, 63, 8),
(700, 64, 1),
(701, 64, 9),
(702, 64, 35),
(245, 67, 3),
(246, 67, 21),
(254, 68, 29),
(275, 69, 3),
(276, 69, 28),
(293, 70, 3),
(294, 70, 33),
(315, 71, 3),
(316, 71, 34),
(361, 73, 35),
(748, 78, 1),
(749, 78, 3),
(334, 81, 1),
(335, 81, 3),
(336, 81, 35),
(902, 84, 33);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargos_supervisores`
--

CREATE TABLE `cargos_supervisores` (
  `cargoId` int(11) NOT NULL,
  `supervisorId` int(11) NOT NULL
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

--
-- Despejando dados para a tabela `cargo_sinonimos`
--

INSERT INTO `cargo_sinonimos` (`cargoSinonimoId`, `cargoId`, `cargoSinonimoNome`) VALUES
(61, 13, 'Operador de Sistemas Hídricos'),
(62, 13, 'Mantenedor de Redes de Irrigação'),
(63, 13, 'Técnico de Reparo de Aspersores'),
(64, 13, 'Instalador de Tubulações Agrícolas'),
(65, 13, 'Auxiliar de Monitoramento Hídrico'),
(91, 19, 'Capinador e Podador'),
(92, 19, 'Ajudante de Lavoura'),
(93, 19, 'Operador de Ferramentas Agrícolas'),
(94, 19, 'Auxiliar de Poda e Condução'),
(95, 19, 'Trabalhador de Manutenção de Culturas'),
(101, 21, 'Motorista de Veículos Pesados'),
(102, 21, 'Transportador de Cargas'),
(103, 21, 'Operador de Logística de Coleta'),
(104, 21, 'Conferente de Carga (Rodoviário)'),
(105, 21, 'Vistoriador Veicular (Frota)'),
(106, 22, 'Coordenador de Produção de Refeições'),
(107, 22, 'Chef de Gastronomia Coletiva'),
(108, 22, 'Supervisor de Cozinha Industrial'),
(109, 22, 'Gerente de Alimentos e Bebidas (Cozinha)'),
(110, 22, 'Especialista em Segurança Alimentar (Cozinha)'),
(136, 28, 'Supervisor de Sistemas de Irrigação'),
(137, 28, 'Líder de Fertirrigação'),
(138, 28, 'Técnico de Manutenção Hidráulica Agrícola'),
(139, 28, 'Coordenador de Operações Hídricas'),
(140, 28, 'Gestor de Uso e Consumo Hídrico'),
(146, 30, 'Supervisor de Obras Civis'),
(147, 30, 'Mestre de Obras (Construção Civil)'),
(148, 30, 'Líder de Equipe de Construção'),
(149, 30, 'Fiscal de Projetos de Alvenaria'),
(150, 30, 'Coordenador de Frente de Serviço Civil'),
(156, 32, 'Supervisor de Agricultura Orgânica'),
(157, 32, 'Coordenador de Compostagem e Insumos Orgânicos'),
(158, 32, 'Líder de Produção Certificada'),
(159, 32, 'Gestor de Conformidade Orgânica'),
(160, 32, 'Supervisor de Tratos Culturais Orgânicos'),
(181, 37, 'Supervisor de Refeitório'),
(182, 37, 'Coordenador de Equipe de Alimentos'),
(183, 37, 'Técnico de Boas Práticas de Manipulação (Cozinha)'),
(184, 37, 'Encarregado de Cozinha'),
(185, 37, 'Planejador de Cardápios Coletivos'),
(381, 27, 'Gerente de Frente de Colheita'),
(382, 27, 'Coordenador Operacional de Campo'),
(383, 27, 'Líder de Produção Agrícola (Colheita)'),
(384, 27, 'Supervisor de Colheita e Produtividade'),
(385, 27, 'Chefe de Turma de Safra'),
(386, 27, 'Encarregado de Colheita'),
(437, 58, 'Gerente de Operações Agrícolas'),
(438, 58, 'Coordenador de Rotinas de Lavoura'),
(439, 58, 'Líder de Equipes de Manejo'),
(440, 58, 'Gestor de Produção e Insumos'),
(441, 58, 'Supervisor Técnico Agrícola'),
(487, 41, 'Podador Profissional (Motosserra)'),
(488, 41, 'Operador de Manutenção de Vegetação'),
(489, 41, 'Técnico de Corte e Poda (Campo)'),
(490, 41, 'Agente de Limpeza de Áreas (Motorizado)'),
(491, 41, 'Roçador Mecanizado'),
(547, 36, 'Trabalhador de Manutenção de Áreas Verdes'),
(548, 36, 'Operador de Roçadeira (Manutenção)'),
(549, 36, 'Técnico de Paisagismo (Básico)'),
(550, 36, 'Agente de Controle Estético e Poda'),
(551, 36, 'Auxiliar de Arborização'),
(562, 10, 'Apoio Administrativo Multifuncional'),
(563, 10, 'Assistente de Rotinas Internas'),
(564, 10, 'Auxiliar de Departamento Pessoal Júnior'),
(565, 10, 'Técnico de Arquivo e Protocolo'),
(566, 10, 'Apoio à Coordenação Administrativa'),
(587, 31, 'Supervisor de Produção de Mudas'),
(588, 31, 'Líder de Práticas Sustentáveis em Viveiros'),
(589, 31, 'Técnico Agrícola de Viveiro'),
(590, 31, 'Coordenador de Multiplicação Vegetal'),
(591, 31, 'Gestor de Qualidade de Mudas'),
(612, 59, 'Diretor de Operações e Estratégia'),
(613, 59, 'Gestor de Contratos e Conflitos'),
(614, 59, 'Gerente Executivo'),
(615, 59, 'Head de Planejamento e Controle'),
(616, 59, 'Coordenador Geral Administrativo'),
(617, 23, 'Trabalhador Agrícola (Colheita)'),
(618, 23, 'Operador de Colheita Manual'),
(619, 23, 'Panhador de Frutas'),
(620, 23, 'Apanhador Agrícola'),
(621, 23, 'Operário de Campo'),
(622, 5, 'Monitor de Pragas e Doenças'),
(623, 5, 'Fiscal Fitossanitário'),
(624, 5, 'Técnico de Inspeção Agrícola'),
(625, 5, 'Agente de Controle Biológico'),
(626, 5, 'Auxiliar de Fitossanidade'),
(657, 46, 'Gerente de Operações Agrícolas'),
(658, 46, 'Coordenador de Rotinas de Lavoura'),
(659, 46, 'Líder de Equipes de Manejo'),
(660, 46, 'Gestor de Produção e Insumos'),
(661, 46, 'Supervisor Técnico Agrícola'),
(747, 15, 'Ajudante de Manutenção Veicular'),
(748, 15, 'Técnico de Lubrificação'),
(749, 15, 'Auxiliar de Desmontagem de Motores'),
(750, 15, 'Assistente de Oficina Agrícola'),
(751, 15, 'Montador de Componentes Mecânicos'),
(752, 39, 'Mecânico de Máquinas Agrícolas e Industriais'),
(753, 39, 'Técnico de Diagnóstico Eletromecânico'),
(754, 39, 'Especialista em Sistemas Hidráulicos e Elétricos'),
(755, 39, 'Mecânico Soldador'),
(756, 39, 'Chefe de Manutenção de Frota e Equipamentos'),
(762, 45, 'Técnico de Soldagem TIG/MIG'),
(763, 45, 'Operador de Eletrodo Revestido'),
(764, 45, 'Especialista em Reparo de Peças Metálicas'),
(765, 45, 'Técnico de Caldeiraria (Básico)'),
(766, 45, 'Montador Soldador'),
(862, 53, 'Operador de Trator (Pesado/Precisão)'),
(863, 53, 'Técnico de Aplicação de Defensivos (Trator)'),
(864, 53, 'Motorista de Máquinas Agrícolas (Pleno)'),
(865, 53, 'Operador de Poda Mecanizada'),
(866, 53, 'Controlador de Implementos Agrícolas'),
(892, 55, 'Operador de Pulverização e Adubação (Especializado)'),
(893, 55, 'Tratorista de Cultivo e Manutenção'),
(894, 55, 'Técnico de Operação de Implementos (Qualidade)'),
(895, 55, 'Especialista em Regulagem de Máquinas Agrícolas'),
(896, 55, 'Motorista de Trator (Sênior)'),
(927, 3, 'Técnico de Armazenamento'),
(928, 3, 'Controlador de Estoque'),
(929, 3, 'Assistente de Recebimento e Expedição'),
(930, 3, 'Técnico de Logística Interna'),
(931, 3, 'Encarregado de Materiais'),
(987, 26, 'Supervisor de Produção Biotecnológica'),
(988, 26, 'Líder de Processos Fermentativos'),
(989, 26, 'Gerente de Controle de Qualidade Biológico'),
(990, 26, 'Especialista em Normas Regulatórias (Bioinsumos)'),
(991, 26, 'Coordenador de Biofábrica'),
(1028, 7, 'Técnico em Produção de Bioinsumos'),
(1029, 7, 'Operador de Laboratório de Bioprocessos'),
(1030, 7, 'Manipulador de Microrganismos'),
(1031, 7, 'Auxiliar de Esterilização e Envase'),
(1032, 7, 'Técnico de Assepsia (Biofábrica)'),
(1033, 7, 'Auxiliar de Biofábrica'),
(1089, 14, 'Técnico de Apoio Laboratorial'),
(1090, 14, 'Preparador de Amostras'),
(1091, 14, 'Auxiliar de Vidraria e Esterilização'),
(1092, 14, 'Técnico de Higienização de Laboratório'),
(1093, 14, 'Auxiliar de Análises Químicas (Básico)'),
(1124, 60, 'Agente de Limpeza e Conservação'),
(1125, 60, 'Apoio à Higienização'),
(1126, 60, 'Zelador'),
(1127, 60, 'Auxiliar de Copa e Cozinha (Apoio)'),
(1128, 60, 'Técnico de Resíduos e Descarte'),
(1399, 1, 'Líder de Equipe de Colheita'),
(1400, 1, 'Fiscal de Colheita'),
(1401, 1, 'Supervisor Operacional de Colheita'),
(1402, 1, 'Encarregado de Frente de Colheita'),
(1403, 1, 'Coordenador de Pessoal de Campo'),
(1447, 65, 'Engenheiro Agrônomo'),
(1448, 68, 'Encarregado de Mecânico'),
(1477, 38, 'Técnico em Lubrificação Industrial e Agrícola'),
(1478, 38, 'Mecânico de Manutenção Preventiva (Lubrificação)'),
(1479, 38, 'Operador de Compressores e Bombas'),
(1480, 38, 'Auxiliar de Manutenção Mecânica'),
(1481, 38, 'Frentista'),
(1485, 71, 'COORDENADOR DE TRATORISTA'),
(1501, 54, 'Operador de Trator (Leve)'),
(1502, 54, 'Auxiliar de Manutenção de Lavoura (Trator)'),
(1503, 54, 'Operador de Insumos Localizados'),
(1504, 54, 'Motorista de Máquinas Agrícolas (Júnior)'),
(1505, 54, 'Técnico de Apoio com Tratores'),
(1536, 11, 'Organizador de Materiais de Embalagem'),
(1537, 11, 'Técnico de Controle de Inventário (Packing)'),
(1538, 11, 'Auxiliar de Abastecimento'),
(1539, 11, 'Expedidor de Insumos'),
(1540, 11, 'Conferente de Materiais (Embalagem)'),
(1541, 11, 'Auxiliar de Estoque'),
(1548, 73, 'Operador de Máquina de Costura'),
(1614, 33, 'Diretor de Operações e Estratégia'),
(1615, 33, 'Gestor de Contratos e Conflitos'),
(1616, 33, 'Gerente Executivo'),
(1617, 33, 'Head de Planejamento e Controle'),
(1618, 33, 'Coordenador Geral Administrativo'),
(1668, 24, 'Designer de Produto'),
(1669, 24, 'Técnico em Prototipagem e Modelagem 3D'),
(1670, 24, 'Especialista em CAD e Design Gráfico'),
(1671, 24, 'Analista de Viabilidade Técnica e Estética'),
(1727, 52, 'Agente de Prevenção de Riscos'),
(1728, 52, 'Especialista em Normas Regulamentadoras (NRs)'),
(1729, 52, 'Coordenador de Treinamentos de Segurança'),
(1730, 52, 'Técnico SESMT'),
(1731, 52, 'Fiscal de Uso de EPIs'),
(1737, 44, 'Atendente de PABX e Público'),
(1738, 44, 'Assistente de Atendimento e Informações'),
(1739, 44, 'Secretário(a) de Recepção'),
(1740, 44, 'Controlador de Acesso e Visitantes'),
(1741, 44, 'Auxiliar de Protocolo e Agenda'),
(1757, 50, 'Enfermeira do Trabalho Júnior'),
(1758, 50, 'Técnica de Saúde Ocupacional'),
(1759, 50, 'Auxiliar de Ambulatório Médico'),
(1760, 50, 'Técnica de Triagem e Primeiros Socorros'),
(1761, 50, 'Técnica de Enfermagem (Saúde Corporativa)'),
(1817, 47, 'Gerente de Qualidade e Conformidade'),
(1818, 47, 'Coordenador de Auditorias'),
(1819, 47, 'Especialista em Rastreabilidade'),
(1820, 47, 'Supervisor de Normas Técnicas'),
(1821, 47, 'Analista de Gestão da Qualidade (Líder)'),
(1842, 8, 'Assistente de Controle de Qualidade Documental'),
(1843, 8, 'Auxiliar de Rastreabilidade'),
(1844, 8, 'Técnico de Suporte a Auditorias'),
(1845, 8, 'Organizador de Documentos de Conformidade'),
(1846, 8, 'Assistente de Gestão da Qualidade'),
(1862, 4, 'Especialista em Comércio Exterior'),
(1863, 4, 'Analista de Logística Internacional'),
(1864, 4, 'Coordenador de Documentação de Embarque'),
(1865, 4, 'Analista de Vendas Internacionais'),
(1866, 4, 'Consultor de Processos Aduaneiros'),
(1897, 12, 'Assistente de Logística de Exportação'),
(1898, 12, 'Controlador de Documentos Aduaneiros'),
(1899, 12, 'Técnico de Expedição Internacional'),
(1900, 12, 'Assistente de Fretes e Prazos'),
(1901, 12, 'Auxiliar de Comex'),
(1937, 51, 'Analista de Suporte Técnico'),
(1938, 51, 'Técnico de Redes e Telecomunicações'),
(1939, 51, 'Especialista em Manutenção de Hardware e Software'),
(1940, 51, 'Técnico de Infraestrutura de TI'),
(1941, 51, 'Administrador de Servidores Júnior'),
(1977, 43, 'Desenvolvedor Full-Stack (Júnior/Pleno)'),
(1978, 43, 'Analista de Sistemas e Suporte'),
(1979, 43, 'Engenheiro de Software'),
(1980, 43, 'Técnico de Banco de Dados e Integração'),
(1981, 43, 'Especialista em Desenvolvimento Web'),
(1997, 48, 'Gerente de Suprimentos'),
(1998, 48, 'Coordenador de Aquisições'),
(1999, 48, 'Especialista em Negociação e Fornecedores'),
(2000, 48, 'Líder de Logística de Compras'),
(2001, 48, 'Supervisor de Cotação e Contratos'),
(2022, 64, 'Assistente de Suprimentos'),
(2023, 64, 'Comprador Júnior'),
(2024, 64, 'Auxiliar de Cotação e Negociação'),
(2025, 64, 'Técnico de Aquisição de Materiais'),
(2026, 64, 'Assistente de Procurement'),
(2042, 9, 'Assistente de Suprimentos'),
(2043, 9, 'Comprador Júnior'),
(2044, 9, 'Auxiliar de Cotação e Negociação'),
(2045, 9, 'Técnico de Aquisição de Materiais'),
(2046, 9, 'Assistente de Procurement'),
(2057, 62, 'Auxiliar de Escrituração Fiscal'),
(2058, 62, 'Técnico em Lançamento de Notas Fiscais'),
(2059, 62, 'Analista de Apuração de Impostos Jr.'),
(2060, 62, 'Assistente de Obrigações Acessórias'),
(2061, 62, 'Auxiliar Contábil Fiscal'),
(2067, 6, 'Auxiliar de Escrituração Fiscal'),
(2068, 6, 'Técnico em Lançamento de Notas Fiscais'),
(2069, 6, 'Analista de Apuração de Impostos Jr.'),
(2070, 6, 'Assistente de Obrigações Acessórias'),
(2071, 6, 'Auxiliar Contábil Fiscal'),
(2077, 63, 'Auxiliar de Escrituração Fiscal'),
(2078, 63, 'Técnico em Lançamento de Notas Fiscais'),
(2079, 63, 'Analista de Apuração de Impostos Jr.'),
(2080, 63, 'Assistente de Obrigações Acessórias'),
(2081, 63, 'Auxiliar Contábil Fiscal'),
(2092, 34, 'Controller Financeiro'),
(2093, 34, 'Head de Tesouraria e Fluxo de Caixa'),
(2094, 34, 'Gerente de Auditoria e Riscos Financeiros'),
(2095, 34, 'Especialista em Planejamento Financeiro'),
(2096, 34, 'Líder de Contabilidade e Finanças'),
(2112, 61, 'Assistente de Contas a Pagar e Receber'),
(2113, 61, 'Técnico de Conciliação Bancária'),
(2114, 61, 'Auxiliar de Tesouraria'),
(2115, 61, 'Assistente de Rotinas Financeiras'),
(2116, 61, 'Apoio Administrativo Financeiro'),
(2122, 20, 'Assistente de Contas a Pagar e Receber'),
(2123, 20, 'Técnico de Conciliação Bancária'),
(2124, 20, 'Auxiliar de Tesouraria'),
(2125, 20, 'Assistente de Rotinas Financeiras'),
(2126, 20, 'Apoio Administrativo Financeiro'),
(2137, 25, 'Técnico em Eletroeletrônica'),
(2138, 25, 'Eletricista de Manutenção Industrial'),
(2139, 25, 'Instalador Eletricista'),
(2140, 25, 'Especialista em Diagnóstico Elétrico'),
(2141, 25, 'Mantenedor de Comandos Elétricos'),
(2177, 42, 'Oficial de Alvenaria e Acabamento'),
(2178, 42, 'Técnico em Construção Civil'),
(2179, 42, 'Assentador de Blocos e Revestimentos'),
(2180, 42, 'Mecânico de Construção'),
(2181, 42, 'Executante de Reformas Civis'),
(2222, 56, 'Controlador de Acesso Patrimonial'),
(2223, 56, 'Fiscal de Vigilância'),
(2224, 56, 'Monitor de Segurança (Guarita)'),
(2225, 56, 'Agente de Prevenção de Perdas'),
(2226, 56, 'Rondante de Segurança'),
(2307, 17, 'Classificador de Frutas'),
(2308, 17, 'Operador de Embalagem'),
(2309, 17, 'Seleção e Padrão de Qualidade'),
(2310, 17, 'Empacotador de Frutas'),
(2311, 17, 'Auxiliar de Pós-Colheita'),
(2312, 16, 'Operador de Linha de Beneficiamento'),
(2313, 16, 'Ajudante Geral de Indústria'),
(2314, 16, 'Operador de Triagem e Embalagem'),
(2315, 16, 'Auxiliar de Carregamento e Expedição'),
(2316, 16, 'Operador de Tombamento de Frutas'),
(2347, 40, 'Motorista de Empilhadeira'),
(2348, 40, 'Operador de Movimentação de Cargas'),
(2349, 40, 'Estoquista Operacional'),
(2350, 40, 'Manuseador de Paletes'),
(2351, 40, 'Técnico de Conferência de Carga (Empilhadeira)'),
(2392, 49, 'Gerente de Pós-Colheita e Expedição'),
(2393, 49, 'Coordenador de Linha de Produção (Frutas)'),
(2394, 49, 'Líder de Qualidade e Logística de Embalagem'),
(2395, 49, 'Supervisor de Beneficiamento de Frutas'),
(2396, 49, 'Chefe de Expedição e Armazenamento'),
(2397, 2, 'Líder de Operações Agrícolas'),
(2398, 2, 'Encarregado de Manejo de Culturas'),
(2399, 2, 'Técnico de Tratos de Campo'),
(2400, 2, 'Supervisor de Aplicação Agrícola'),
(2401, 2, 'Coordenador de Práticas Agrícolas'),
(2432, 35, 'Técnico de Controle de Qualidade (Embalagem)'),
(2433, 35, 'Fiscal de Padrões de Frutas'),
(2434, 35, 'Auditor Interno de Processos'),
(2435, 35, 'Técnico de Rastreabilidade e Conformidade'),
(2436, 35, 'Verificador de Especificações Técnicas'),
(2467, 18, 'Agente de Limpeza e Conservação'),
(2468, 18, 'Apoio à Higienização'),
(2469, 18, 'Zelador'),
(2470, 18, 'Auxiliar de Copa e Cozinha (Apoio)'),
(2471, 18, 'Técnico de Resíduos e Descarte'),
(2482, 84, 'Agente de Limpeza e Conservação'),
(2483, 84, 'Apoio à Higienização'),
(2484, 84, 'Zelador'),
(2485, 84, 'Auxiliar de Copa e Cozinha (Apoio)'),
(2486, 84, 'Técnico de Resíduos e Descarte'),
(2502, 29, 'Supervisor de Manutenção Industrial (Packing)'),
(2503, 29, 'Técnico Eletromecânico Líder'),
(2504, 29, 'Coordenador de Confiabilidade de Máquinas'),
(2505, 29, 'Chefe de Reparos de Linha de Embalagem'),
(2506, 29, 'Especialista em Diagnóstico de Packing House');

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

--
-- Despejando dados para a tabela `cbos`
--

INSERT INTO `cbos` (`cboId`, `familiaCboId`, `cboCod`, `cboTituloOficial`, `cboDataCadastro`, `cboDataAtualizacao`) VALUES
(1, 1, '6201-10', 'Coordenador de Turma de Colheita', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(2, 1, '6201-05', 'Supervisor de Tratos Culturais (Geral)', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(3, 1, '4141-05', 'Almoxarife', '2025-10-17 15:56:43', '2025-10-17 20:05:48'),
(4, 1, '3543-05', 'Analista de Comércio Exterior', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(5, 1, '6220-20', 'Monitor de Qualidade e Pragas', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(6, 1, '4131-10', 'Assistente Fiscal e Financeiro', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(7, 1, '3253-05', 'Técnico de Laboratório Industrial', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(8, 1, '4110-05', 'Auxiliar de Escritório e Administrativo', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(9, 1, '3542-10', 'Auxiliar de Compras e Suprimentos', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(10, 1, '4141-25', 'Auxiliar de Logística e Estoque (Embalagem)', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(11, 1, '4110-45', 'Auxiliar de Serviços de Apoio à Exportação', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(12, 1, '6430-05', 'Auxiliar de Irrigação (Manutenção e Operação)', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(13, 1, '3011-05', 'Auxiliar de Laboratório Agrícola', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(14, 1, '9131-05', 'Auxiliar de Mecânica', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(15, 1, '7842-05', 'Operador de Máquinas de Beneficiamento', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(16, 1, '7841-05', 'Auxiliar de Produção (Packing House)', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(17, 1, '7170-20', 'Auxiliar de Limpeza (Serviços Gerais)', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(18, 1, '6210-05', 'Trabalhador Agropecuário (Geral)', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(19, 1, '7825-10', 'Caminhoneiro', '2025-10-17 15:56:43', '2025-10-17 20:07:16'),
(20, 1, '2711-05', 'Chef de Cozinha', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(21, 1, '2624-10', 'Designer Industrial', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(22, 1, '9511-05', 'Eletricista de Manutenção', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(23, 1, '6201-10', 'Encarregado de Setor (Supervisão)', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(24, 1, '9101-05', 'Mecânico de Manutenção Industrial', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(25, 1, '7152-10', 'Pedreiro', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(26, 1, '1421-05', 'Gerente Administrativo', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(27, 1, '4101-05', 'Gerente Financeiro', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(28, 1, '3912-11', 'Inspetor de Qualidade', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(29, 1, '6220-10', 'Jardineiro', '2025-10-17 15:56:43', '2025-10-17 20:07:16'),
(30, 1, '5132-05', 'Líder de Cozinha (Supervisor)', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(31, 1, '9191-10', 'Lubrificador', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(32, 1, '9113-05', 'Mecânico de Manutenção Agrícola', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(33, 1, '7822-20', 'Operador de Empilhadeira', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(34, 1, '9192-05', 'Operador de Máquinas Florestais', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(35, 1, '6321-20', 'Trabalhador de Manutenção Civil', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(36, 1, '7152-30', 'Soldador', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(37, 1, '3171-10', 'Programador de Sistemas', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(38, 1, '4221-05', 'Recepcionista', '2025-10-17 15:56:43', '2025-10-17 20:07:16'),
(39, 1, '7243-15', 'Mecânico de Manutenção de Máquinas', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(40, 1, '7801-05', 'Supervisor de Produção', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(41, 1, '3222-05', 'Técnico em Enfermagem do Trabalho', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(42, 1, '3172-10', 'Técnico de Suporte de TI', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(43, 1, '3516-05', 'Técnico em Segurança do Trabalho', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(44, 1, '6410-15', 'Tratorista', '2025-10-17 15:56:43', '2025-10-18 10:37:31'),
(45, 1, '5174-15', 'Agente de Portaria', '2025-10-17 15:56:43', '2026-04-13 16:46:25'),
(620111, 1, '2221-10', 'Engenheiro Agrônomo', '2026-03-10 14:56:42', '2026-03-10 14:56:42'),
(620112, 1, '6210-05', 'Trabalhador Volante na Agricultura', '2026-03-10 14:56:42', '2026-03-10 14:56:42'),
(620113, 1, '6201-10', 'Supervisor de Exploração Agrícola', '2026-03-10 14:56:42', '2026-03-10 14:56:42'),
(620114, 1, '6430-05', 'Trabalhador na Irrigação', '2026-03-10 14:56:42', '2026-03-10 14:56:42'),
(620115, 1, '7632-10', 'Costureira de Peças Sob Encomenda', '2026-03-10 14:56:42', '2026-03-13 19:44:55'),
(620117, 1, '2221-10', 'Engenheiro agrônomo', '2026-03-10 14:57:53', '2026-03-10 14:57:53'),
(620118, 1, '6201-10', 'Supervisor de exploração agrícola (Encarregados/Coordenadores)', '2026-03-10 14:57:53', '2026-03-10 14:57:53'),
(620119, 1, '6430-05', 'Trabalhador na irrigação', '2026-03-10 14:57:53', '2026-03-10 14:57:53'),
(620120, 1, '6210-05', 'Trabalhador volante na agricultura', '2026-03-10 14:57:53', '2026-03-10 14:57:53'),
(620121, 1, '9144-05', 'Mecânico de manutenção de veículos a motor (2 tempos)', '2026-03-10 14:57:53', '2026-03-10 14:57:53'),
(620122, 1, '7632-15', 'Costureira de peças sob encomenda', '2026-03-10 14:57:53', '2026-03-10 14:57:53'),
(620124, 1, '1411-15', 'Gerente financeiro (Ajuste Supervisor)', '2026-03-10 14:57:53', '2026-03-10 14:57:53'),
(620125, 1, '2221-10', 'Engenheiro Agrônomo', '2026-03-10 15:02:11', '2026-03-10 15:02:11'),
(620126, 1, '6201-10', 'Supervisor de Exploração Agrícola', '2026-03-10 15:02:11', '2026-03-10 15:02:11'),
(620127, 1, '6210-05', 'Trabalhador Volante na Agricultura', '2026-03-10 15:02:11', '2026-03-10 15:02:11'),
(620128, 1, '6430-05', 'Trabalhador na Irrigação', '2026-03-10 15:02:11', '2026-03-10 15:02:11'),
(620129, 1, '9144-05', 'Mecânico de Manutenção de Veículos a Motor', '2026-03-10 15:02:11', '2026-03-10 15:02:11'),
(620130, 1, '7632-15', 'Costureira de Peças Sob Encomenda', '2026-03-10 15:02:11', '2026-03-10 15:02:11'),
(620132, 1, '1411-15', 'Gerente Financeiro', '2026-03-10 15:02:11', '2026-03-10 15:02:11'),
(620133, 1, '2221-10', 'Engenheiro Agrônomo', '2026-03-10 16:49:00', '2026-03-10 16:49:00'),
(620134, 1, '6201-10', 'Supervisor de Exploração Agrícola', '2026-03-10 16:49:00', '2026-03-10 16:49:00'),
(620136, 1, '6430-05', 'Trabalhador na Irrigação', '2026-03-10 16:49:00', '2026-03-10 16:49:00'),
(620137, 1, '9144-05', 'Mecânico de Manutenção de Motores (2 Tempos)', '2026-03-10 16:49:00', '2026-03-10 16:49:00'),
(620138, 1, '7632-15', 'Costureira de Peças Sob Encomenda', '2026-03-10 16:49:00', '2026-03-10 16:49:00'),
(620140, 1, '1411-15', 'Gerente Financeiro', '2026-03-10 16:49:00', '2026-03-10 16:49:00'),
(620141, 1, '6133-05', 'Apicultor', '2026-03-13 17:45:49', '2026-03-13 17:45:49'),
(620142, 1, '1210-10', 'Diretor Geral', '2026-04-13 13:45:27', '2026-04-13 13:45:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `cursoId` int(5) NOT NULL,
  `cursoNome` varchar(128) NOT NULL,
  `cursoDescricao` text DEFAULT NULL,
  `cursoDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `cursoDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cursos`
--

INSERT INTO `cursos` (`cursoId`, `cursoNome`, `cursoDescricao`, `cursoDataCadastro`, `cursoDataAtualizacao`) VALUES
(1, 'Fruticultura e Manejo Agrícola', 'Cursos relacionados a técnicas de cultivo, poda e colheita.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(2, 'Liderança e Gestão de Pessoas', 'Cursos focados em habilidades de liderança, motivação e gestão de equipes.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(3, 'Técnico em Agropecuária ou Agroindústria', 'Formação técnica de nível médio em áreas agropecuárias ou correlatas.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(4, 'Almoxarife e Logística de Estoque', 'Cursos básicos ou avançados sobre gestão de armazéns, inventário e logística.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(5, 'Informática Básica e Pacote Office (Word, Excel, Power Point)', 'Domínio de ferramentas de escritório e planilhas eletrônicas.', '2025-10-17 18:46:50', '2026-04-13 18:36:25'),
(6, 'Comércio Exterior e Processos Aduaneiros', 'Formação superior ou tecnológica essencial para a função.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(7, 'Inglês Técnico e Comercial', 'Habilidade de comunicação na língua estrangeira para negociações e documentação.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(8, 'Monitoramento Integrado de Pragas (MIP) e Fitopatologia', 'Conhecimento especializado em identificação e controle de pragas e doenças.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(9, 'Formação/Graduação em Contabilidade ou Fiscal', 'Cursos técnicos, tecnólogos ou bacharelado em áreas contábeis/fiscais.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(10, 'Perícias Contábeis e Auditoria', 'Especialização desejável na área contábil/fiscal.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(11, 'Biotecnologia, Química ou Bioprocessos', 'Cursos técnicos ou profissionalizantes relacionados à manipulação biológica e química.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(12, 'Rotinas Administrativas e Organização de Escritório', 'Cursos de aperfeiçoamento em rotinas de escritório, arquivo e organização.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(13, 'Boas Práticas de Fabricação (BPF)', 'Cursos de higiene, qualidade e segurança alimentar em ambientes de produção.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(14, 'Formação em Administração ou Auxiliar de Escritório', 'Formação básica ou técnica em rotinas administrativas.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(15, 'Mecânica Básica e Manutenção Veicular', 'Cursos de introdução à mecânica e reparo de veículos e máquinas.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(16, 'Gastronomia ou Cozinha Profissional', 'Formação técnica ou superior exigida para o planejamento e preparo de refeições.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(17, 'NR-10 (Segurança em Eletricidade)', 'Norma Regulamentadora obrigatória para serviços com eletricidade.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(18, 'Nutrição de Plantas e Manejo de Pragas', 'Cursos específicos para o manejo e sanidade vegetal em viveiros.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(19, 'Curso Técnico Agrícola', 'Formação técnica de nível médio voltada para o setor agropecuário.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(20, 'Agrotóxicos e Aplicação de Defensivos', 'Cursos sobre manuseio e aplicação segura de produtos químicos agrícolas.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(21, 'Boas Práticas Agrícolas', 'Cursos sobre métodos e normas para produção agrícola sustentável e segura.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(22, 'Operador de Empilhadeira (Certificado)', 'Curso de operação e certificação obrigatória para manuseio de empilhadeiras.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(23, 'Operador de Roçadeira e Motosserra', 'Cursos para operação segura de equipamentos de corte motorizados.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(24, 'Qualificação em Construção Civil', 'Cursos básicos de aperfeiçoamento em alvenaria, reboco e construção.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(25, 'Análise e Desenvolvimento de Sistemas (Técnico/Superior)', 'Formação essencial para as funções de Programação.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(26, 'Programação (PHP, JS, SQL)', 'Cursos de linguagens de programação e banco de dados.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(27, 'Comandos Elétricos e Eletroeletrônica', 'Cursos focados em instalação e diagnóstico de comandos e sistemas elétricos.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(28, 'NR-31 (Segurança no Trabalho Rural)', 'Norma Regulamentadora para segurança e saúde no trabalho na agricultura.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(29, 'NR-35 (Trabalho em Altura)', 'Norma Regulamentadora obrigatória para serviços em altura.', '2025-10-17 18:46:50', '2025-10-17 18:46:50'),
(30, 'Técnico em Segurança do Trabalho', 'Formação técnica essencial para a função de TST.', '2025-10-17 18:46:50', '2026-04-13 14:41:57'),
(31, 'Mecanização Agrícola', 'Capacitação para Operação e Manutenção Eficiente de máquinas e implementos agrícola com foco em tratores e implementos, seguindo normas de segurança e procedimentos técnicos', '2025-10-31 12:42:46', '2025-10-31 12:47:59'),
(32, 'NR-20 (Trabalho com Inflamáveis e Combustíveis)', '', '2025-10-31 16:55:14', '2025-10-31 16:55:57'),
(33, 'Manejo de Solo', NULL, '2025-11-07 15:01:10', '2025-11-07 15:01:10'),
(34, 'Elétrica de Veículos', NULL, '2025-11-07 16:31:46', '2025-11-07 16:31:46'),
(35, 'Soldagem MIG/TIG em estruturas metálicas', NULL, '2025-11-07 16:46:04', '2025-11-07 16:47:18'),
(36, 'Operador de Motoniveladora', NULL, '2025-11-07 17:13:12', '2025-11-07 17:13:12'),
(37, 'Operador de Retroescavadeira', NULL, '2025-11-07 17:13:23', '2025-11-07 17:13:23'),
(38, 'Hidráulica e Irrigação', NULL, '2026-03-13 16:37:06', '2026-03-13 16:37:06'),
(39, 'Fisiologia Vegetal', NULL, '2026-03-13 16:37:58', '2026-03-13 16:37:58'),
(40, 'NR-12 (Segurança em Máquinas e Equipamentos)', NULL, '2026-03-13 16:51:30', '2026-03-13 16:52:13'),
(41, 'Mecânica Básica', NULL, '2026-03-13 16:52:47', '2026-03-13 16:52:47'),
(42, 'Tratorista', NULL, '2026-03-13 17:38:21', '2026-03-13 17:38:21'),
(43, 'Manejo Produtivo na Apicultura', NULL, '2026-03-13 18:08:49', '2026-03-13 18:10:01'),
(44, 'NR-6 (Equipamentos de Proteção Individual)', NULL, '2026-03-13 19:19:58', '2026-03-13 19:19:58'),
(45, 'Corte e Costura', NULL, '2026-03-13 19:47:52', '2026-03-13 19:47:52'),
(46, 'Bacharelado em Administração (ou áreas afins)', NULL, '2026-04-13 13:29:31', '2026-04-13 13:30:28'),
(47, 'Design Gráfico', NULL, '2026-04-13 13:58:08', '2026-04-13 13:58:08'),
(48, 'CNH - Carteira Nacional de Habilitação', NULL, '2026-04-13 14:20:25', '2026-04-13 14:20:25'),
(49, 'Técnico em Enfermagem', NULL, '2026-04-13 14:41:45', '2026-04-13 14:41:45'),
(50, 'Técnico em Enfermagem no Trabalho', NULL, '2026-04-13 14:42:12', '2026-04-13 14:44:40'),
(51, 'Gestão de Frota', NULL, '2026-04-13 17:49:51', '2026-04-13 17:49:51'),
(52, 'Relações Internacionais', NULL, '2026-04-13 17:50:10', '2026-04-13 17:50:10'),
(53, 'Gestão de Terminais Portuários e Logística Internacional', NULL, '2026-04-13 17:50:25', '2026-04-13 17:50:25'),
(54, 'Rede de Computadores', NULL, '2026-04-13 18:17:17', '2026-04-13 18:17:17'),
(55, 'Sistemas Operacionais (Windows Server)', NULL, '2026-04-13 18:17:29', '2026-04-13 18:17:29'),
(56, 'CFTV (Circuito Fechado de TV)', NULL, '2026-04-13 18:18:08', '2026-04-13 18:18:08'),
(57, 'Manutenção de Computadores', NULL, '2026-04-13 18:18:27', '2026-04-13 18:18:27'),
(58, 'Manutenção de Notebooks', NULL, '2026-04-13 18:18:36', '2026-04-13 18:18:36'),
(59, 'Manutenção de Redes', NULL, '2026-04-13 18:18:52', '2026-04-13 18:18:52'),
(60, 'Lógica de Programação', NULL, '2026-04-13 18:34:58', '2026-04-13 18:34:58'),
(61, 'Banco de Dados (SQL)', NULL, '2026-04-13 18:35:12', '2026-04-13 18:35:12'),
(62, 'Desenvolvimento WEB (HTML, CSS, JavaScript)', NULL, '2026-04-13 18:35:38', '2026-04-13 18:35:38'),
(63, 'Linguagem de Programação (Python)', NULL, '2026-04-13 18:36:03', '2026-04-13 18:36:03'),
(64, 'NR-11 (Transporte, Movimentação, Armazenagem e Manuseio de Materiais)', NULL, '2026-04-17 13:14:59', '2026-04-17 13:18:41'),
(65, 'Qualidade de Alimentos', NULL, '2026-04-17 14:09:51', '2026-04-17 14:09:51');

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

--
-- Despejando dados para a tabela `cursos_cargo`
--

INSERT INTO `cursos_cargo` (`cursoCargoId`, `cursoId`, `cargoId`, `cursoCargoObrigatorio`, `cursoCargoObs`) VALUES
(278, 12, 13, 0, NULL),
(282, 21, 19, 0, NULL),
(283, 2, 21, 1, NULL),
(284, 4, 21, 0, NULL),
(285, 15, 21, 1, NULL),
(286, 16, 22, 1, NULL),
(294, 12, 28, 0, NULL),
(298, 2, 32, 1, NULL),
(299, 20, 32, 0, NULL),
(300, 21, 32, 0, NULL),
(302, 2, 37, 1, NULL),
(303, 13, 37, 0, NULL),
(360, 2, 27, 1, ''),
(361, 13, 27, 0, ''),
(362, 21, 27, 0, ''),
(363, 31, 27, 1, ''),
(394, 23, 41, 1, 'Periodicidade: Anual'),
(395, 29, 41, 1, 'Periodicidade: Bi-anual'),
(406, 23, 36, 1, ''),
(411, 5, 10, 1, ''),
(412, 14, 10, 0, ''),
(425, 2, 31, 1, ''),
(426, 18, 31, 0, ''),
(427, 19, 31, 1, ''),
(430, 3, 5, 0, ''),
(431, 8, 5, 1, ''),
(453, 8, 46, 0, ''),
(454, 33, 46, 0, ''),
(455, 2, 46, 1, ''),
(456, 18, 46, 0, ''),
(457, 19, 46, 0, ''),
(476, 15, 15, 0, ''),
(477, 15, 39, 0, ''),
(478, 31, 39, 0, ''),
(479, 34, 39, 0, ''),
(481, 35, 45, 1, ''),
(531, 20, 53, 1, ''),
(532, 31, 53, 1, ''),
(533, 36, 53, 0, ''),
(534, 37, 53, 0, ''),
(544, 20, 55, 1, ''),
(545, 31, 55, 1, ''),
(555, 4, 3, 0, ''),
(556, 5, 3, 1, ''),
(572, 11, 26, 1, ''),
(573, 2, 26, 1, ''),
(574, 13, 26, 1, ''),
(584, 11, 7, 0, ''),
(585, 20, 7, 1, ''),
(601, 13, 14, 1, ''),
(602, 20, 14, 1, ''),
(603, 11, 14, 0, ''),
(613, 13, 60, 1, ''),
(614, 20, 60, 1, ''),
(615, 11, 60, 0, ''),
(694, 1, 1, 0, ''),
(695, 2, 1, 1, ''),
(696, 13, 1, 0, ''),
(697, 31, 1, 1, 'Anual'),
(720, 20, 65, 0, ''),
(721, 8, 65, 0, ''),
(722, 38, 65, 0, ''),
(723, 33, 65, 0, ''),
(724, 39, 65, 0, ''),
(736, 23, 67, 1, ''),
(737, 40, 67, 1, ''),
(738, 41, 67, 1, ''),
(763, 27, 68, 0, ''),
(764, 15, 68, 0, ''),
(765, 31, 68, 1, ''),
(766, 40, 68, 1, ''),
(767, 28, 68, 0, ''),
(768, 36, 68, 0, ''),
(769, 37, 68, 0, ''),
(770, 35, 68, 0, ''),
(780, 32, 38, 1, 'Renovação Anual'),
(781, 40, 38, 1, 'Renovação Anual'),
(795, 28, 69, 1, 'Renovação Anual'),
(796, 42, 69, 1, 'Renovação Anual'),
(797, 20, 69, 1, 'Renovação Anual'),
(798, 21, 69, 0, ''),
(806, 21, 70, 1, 'Renovação Anual'),
(807, 13, 70, 0, 'Renovação Anual'),
(808, 28, 70, 1, 'Renovação Anual'),
(809, 43, 70, 1, ''),
(835, 15, 71, 1, ''),
(836, 31, 71, 1, ''),
(837, 40, 71, 1, ''),
(838, 28, 71, 1, ''),
(839, 21, 71, 0, ''),
(846, 28, 54, 1, 'Renovação Anual'),
(847, 15, 54, 0, ''),
(848, 40, 54, 1, 'Renovação Anual'),
(849, 42, 54, 1, 'Renovação Anual'),
(853, 4, 81, 1, ''),
(854, 21, 81, 0, ''),
(855, 13, 81, 0, ''),
(856, 44, 81, 1, ''),
(857, 2, 81, 0, ''),
(883, 4, 11, 0, ''),
(884, 13, 11, 0, ''),
(885, 5, 11, 0, ''),
(886, 44, 11, 0, ''),
(887, 22, 11, 1, 'Renovação Anual'),
(895, 45, 73, 1, ''),
(896, 28, 77, 1, 'Renovação Anual'),
(897, 13, 77, 0, ''),
(898, 21, 77, 0, ''),
(899, 5, 77, 0, ''),
(917, 7, 33, 0, ''),
(918, 46, 33, 0, ''),
(932, 7, 24, 0, ''),
(933, 47, 24, 0, ''),
(960, 30, 52, 1, ''),
(961, 20, 52, 0, ''),
(962, 13, 52, 0, ''),
(963, 28, 52, 1, ''),
(964, 48, 52, 1, ''),
(967, 5, 44, 1, ''),
(968, 12, 44, 0, ''),
(975, 49, 50, 1, ''),
(976, 50, 50, 1, ''),
(977, 5, 50, 0, ''),
(1005, 6, 47, 0, ''),
(1006, 7, 47, 0, ''),
(1007, 5, 47, 1, ''),
(1008, 13, 47, 1, ''),
(1025, 5, 8, 1, ''),
(1026, 7, 8, 0, ''),
(1027, 12, 8, 0, ''),
(1028, 13, 8, 1, ''),
(1040, 5, 4, 1, ''),
(1041, 6, 4, 1, ''),
(1042, 7, 4, 1, ''),
(1043, 13, 4, 1, ''),
(1062, 7, 12, 1, ''),
(1063, 13, 12, 1, ''),
(1064, 5, 12, 1, ''),
(1065, 12, 12, 0, ''),
(1066, 51, 12, 0, ''),
(1067, 53, 12, 0, ''),
(1068, 52, 12, 0, ''),
(1079, 29, 51, 1, ''),
(1080, 13, 51, 1, ''),
(1081, 56, 51, 0, ''),
(1082, 57, 51, 0, ''),
(1083, 58, 51, 0, ''),
(1084, 59, 51, 0, ''),
(1085, 54, 51, 0, ''),
(1086, 55, 51, 0, ''),
(1116, 25, 43, 0, ''),
(1117, 26, 43, 0, ''),
(1118, 61, 43, 0, ''),
(1119, 62, 43, 0, ''),
(1120, 63, 43, 0, ''),
(1121, 13, 43, 1, ''),
(1122, 5, 43, 1, ''),
(1132, 13, 48, 1, ''),
(1133, 5, 48, 1, ''),
(1134, 2, 48, 1, ''),
(1141, 5, 64, 1, ''),
(1142, 13, 64, 1, ''),
(1143, 12, 64, 0, ''),
(1150, 5, 9, 1, ''),
(1151, 13, 9, 1, ''),
(1159, 9, 62, 1, ''),
(1160, 10, 62, 0, ''),
(1161, 2, 62, 1, ''),
(1162, 5, 62, 1, ''),
(1167, 9, 6, 0, ''),
(1168, 10, 6, 0, ''),
(1169, 13, 6, 1, ''),
(1170, 5, 6, 1, ''),
(1172, 5, 63, 1, ''),
(1173, 13, 63, 1, ''),
(1182, 14, 34, 0, ''),
(1183, 9, 34, 0, ''),
(1184, 2, 34, 1, ''),
(1185, 5, 34, 1, ''),
(1186, 13, 34, 1, ''),
(1194, 5, 61, 1, ''),
(1195, 13, 61, 1, ''),
(1196, 12, 61, 0, ''),
(1200, 5, 20, 1, ''),
(1201, 13, 20, 1, ''),
(1202, 12, 20, 0, ''),
(1211, 17, 25, 1, ''),
(1212, 27, 25, 0, ''),
(1213, 29, 25, 1, ''),
(1214, 2, 25, 1, ''),
(1215, 13, 25, 1, ''),
(1221, 24, 78, 0, ''),
(1222, 2, 78, 0, ''),
(1223, 17, 78, 0, ''),
(1224, 29, 78, 0, ''),
(1225, 44, 78, 0, ''),
(1239, 24, 42, 0, ''),
(1240, 17, 42, 0, ''),
(1241, 29, 42, 1, ''),
(1242, 44, 42, 0, ''),
(1252, 13, 56, 0, ''),
(1253, 5, 56, 0, ''),
(1280, 13, 17, 1, ''),
(1281, 5, 16, 0, ''),
(1282, 13, 16, 1, ''),
(1296, 22, 40, 1, ''),
(1297, 13, 40, 1, ''),
(1298, 64, 40, 1, ''),
(1307, 13, 49, 1, ''),
(1308, 5, 49, 1, ''),
(1309, 2, 49, 0, ''),
(1310, 3, 49, 0, ''),
(1311, 2, 2, 1, ''),
(1312, 3, 2, 0, ''),
(1313, 5, 2, 1, ''),
(1325, 13, 35, 1, ''),
(1326, 5, 35, 1, ''),
(1327, 65, 35, 0, ''),
(1334, 13, 18, 1, ''),
(1338, 13, 84, 1, ''),
(1339, 20, 84, 1, ''),
(1354, 41, 29, 0, ''),
(1355, 31, 29, 0, ''),
(1356, 13, 29, 1, ''),
(1357, 40, 29, 1, ''),
(1358, 17, 29, 1, ''),
(1359, 27, 29, 0, ''),
(1360, 35, 29, 0, '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas_mercado`
--

CREATE TABLE `empresas_mercado` (
  `empresaId` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `setor` varchar(100) DEFAULT NULL,
  `porte` enum('Pequeno','Médio','Grande','Multinacional') DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresas_mercado`
--

INSERT INTO `empresas_mercado` (`empresaId`, `nome`, `setor`, `porte`, `data_cadastro`) VALUES
(1, 'Teste', 'Agronegócio', 'Pequeno', '2026-03-01 17:23:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `escolaridades`
--

CREATE TABLE `escolaridades` (
  `escolaridadeId` int(5) NOT NULL,
  `escolaridadeTitulo` varchar(64) NOT NULL,
  `peso_pontuacao` int(11) NOT NULL DEFAULT 0,
  `escolaridadeDataCadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `escolaridadeDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `escolaridades`
--

INSERT INTO `escolaridades` (`escolaridadeId`, `escolaridadeTitulo`, `peso_pontuacao`, `escolaridadeDataCadastro`, `escolaridadeDataAtualizacao`) VALUES
(1, 'Ensino Fundamental Incompleto', 10, '2025-10-17 15:49:24', '2026-02-28 01:41:05'),
(2, 'Ensino Fundamental Completo', 20, '2025-10-17 15:49:24', '2026-02-28 01:41:10'),
(3, 'Ensino Médio Completo', 40, '2025-10-17 15:49:24', '2026-02-28 01:42:57'),
(4, 'Ensino Médio Completo com Técnico/Superior', 50, '2025-10-17 15:49:24', '2026-02-28 01:42:46'),
(5, 'Ensino Superior Completo', 60, '2025-10-17 15:49:24', '2026-02-28 01:42:27'),
(6, 'Pós Graduação - Especialização', 70, '2025-11-01 22:52:00', '2026-02-28 01:42:19'),
(7, 'Pós Graduação - MBA', 80, '2025-11-01 22:53:13', '2026-02-28 01:42:14'),
(8, 'Pós Graduação - Mestrado', 80, '2025-11-01 22:55:02', '2026-02-28 01:42:04'),
(9, 'Pós Graduação - Doutorado', 90, '2025-11-01 22:55:24', '2026-02-28 01:41:48'),
(10, 'Ensino Médio Incompleto', 30, '2026-02-28 01:43:41', '2026-02-28 01:43:41'),
(11, 'Ensino Superior Incompleto', 50, '2026-04-13 17:42:41', '2026-04-13 17:43:48');

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
  `faixaDataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `step_a` decimal(10,2) DEFAULT 0.00,
  `step_b` decimal(10,2) DEFAULT 0.00,
  `step_c` decimal(10,2) DEFAULT 0.00,
  `step_d` decimal(10,2) DEFAULT 0.00,
  `step_e` decimal(10,2) DEFAULT 0.00
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

--
-- Despejando dados para a tabela `familia_cbo`
--

INSERT INTO `familia_cbo` (`familiaCboId`, `familiaCboNome`, `familiaCboDataCadastro`, `familiaCboDataAtualizacao`) VALUES
(1, '6201', '2025-10-07 14:18:47', '2025-10-07 14:18:47');

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

--
-- Despejando dados para a tabela `habilidades`
--

INSERT INTO `habilidades` (`habilidadeId`, `habilidadeTipo`, `habilidadeNome`, `habilidadeDescricao`, `habilidadeDataCadastro`, `habilidadeDataAtualizacao`) VALUES
(1, 'Softskill', 'Ética, Integridade e Honestidade', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(2, 'Softskill', 'Proatividade e Iniciativa', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(3, 'Softskill', 'Atitude Positiva e Otimismo', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(4, 'Softskill', 'Trabalho em Equipe e Colaboração', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(5, 'Softskill', 'Comunicação Eficaz e Assertiva', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(6, 'Softskill', 'Resiliência e Adaptabilidade', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(7, 'Softskill', 'Espírito de Pertencimento', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(8, 'Softskill', 'Senso de Responsabilidade e Comprometimento', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(9, 'Softskill', 'Empatia e Respeito Humano', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(10, 'Softskill', 'Senso de Comunidade e Bem-Estar Coletivo', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(11, 'Hardskill', 'Capacidade de Aprendizado Contínuo', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(12, 'Hardskill', 'Atenção à Qualidade do Produto e/ou Serviço', '', '2025-10-17 15:41:42', '2025-10-31 14:26:32'),
(13, 'Hardskill', 'Compromisso com Normas de Segurança e Sustentabilidade', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(14, 'Hardskill', 'Uso Consciente e Racional de Recursos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(15, 'Hardskill', 'Conformidade com Normas e Boas Práticas (BP)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(16, 'Hardskill', 'Supervisão de Colheita e Controle de Produtividade', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(17, 'Hardskill', 'Administração de Equipe e Intermediação de Relações', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(18, 'Hardskill', 'Manutenção Básica de Equipamentos Agrícolas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(19, 'Hardskill', 'Planejamento e Organização de Atividades de Campo', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(20, 'Hardskill', 'Registro de Dados Operacionais (Diário de Campo)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(21, 'Softskill', 'Capacidade de Decisão e Resolução de Conflitos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(22, 'Softskill', 'Sensibilidade e Acessibilidade com Colaboradores', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(23, 'Softskill', 'Criatividade na Produção e Respeito ao Meio Ambiente', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(24, 'Hardskill', 'Supervisão Técnica de Campo e Tratos Culturais', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(25, 'Hardskill', 'Distribuição de Tarefas e Controle de Frequência', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(26, 'Hardskill', 'Conhecimento em Fertilização, Irrigação e Controle de Pragas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(27, 'Hardskill', 'Administração de Insumos, Equipamentos e Recursos Logísticos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(28, 'Softskill', 'Gestão de Pessoas e Desenvolvimento de Equipe', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(29, 'Hardskill', 'Recebimento e Conferência de Materiais e Notas Fiscais', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(30, 'Hardskill', 'Organização e Controle de Estoque (Físico e Sistêmico)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(31, 'Hardskill', 'Utilização de Sistemas Informatizados de Gestão (ERP)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(32, 'Hardskill', 'Separação, Expedição e Inventário de Materiais', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(33, 'Softskill', 'Atenção a Detalhes e Cumprimento de Prazos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(34, 'Softskill', 'Agilidade, Disciplina e Tomada de Decisão Lógica', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(35, 'Hardskill', 'Negociação Comercial e Logística Internacional', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(36, 'Hardskill', 'Elaboração de Propostas Comerciais', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(37, 'Hardskill', 'Comunicação em Língua Estrangeira (Inglês)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(38, 'Hardskill', 'Coordenação de Cronogramas de Produção', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(39, 'Hardskill', 'Elaboração e Conferência de Documentos para Exportação', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(40, 'Hardskill', 'Contratação e Acompanhamento de Serviços de Terceiros', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(41, 'Hardskill', 'Controle de Recebimentos (Câmbio) e Análise de Risco', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(42, 'Hardskill', 'Atualização Profissional e Conhecimento em Globalização', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(43, 'Hardskill', 'Boa Acuidade Visual', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(44, 'Hardskill', 'Execução de Tratos Culturais e Preparo do Solo', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(45, 'Hardskill', 'Preparo de Mudas e Sementes', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(46, 'Softskill', 'Sensibilidade com as Plantas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(47, 'Softskill', 'Capacidade de Observação e Atenção a Detalhes', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(48, 'Softskill', 'Autonomia na Realização de Inspeções', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(49, 'Softskill', 'Raciocínio Lógico e Habilidade em Cálculos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(50, 'Hardskill', 'Organização e Arquivamento de Documentos Fiscais e Contábeis', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(51, 'Hardskill', 'Apoio na Escrituração Contábil e Lançamento de Notas Fiscais', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(52, 'Hardskill', 'Controle e Envio de Obrigações Acessórias', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(53, 'Hardskill', 'Apuração de Tributos (ICMS, PIS, Cofins, Diferencial de Alíquota', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(54, 'Hardskill', 'Elaboração de Relatórios Gerenciais e Demonstrações de Resultado', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(55, 'Hardskill', 'Conhecimento Básico de Legislação Fiscal e Tributária', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(56, 'Hardskill', 'Manipulação Segura de Microrganismos e Materiais Biológicos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(57, 'Hardskill', 'Preparo e Envase de Soluções e Meios de Cultura', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(58, 'Hardskill', 'Higiene e Sanitização de Equipamentos (Biossegurança)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(59, 'Hardskill', 'Organização Documental Física e Eletrônica', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(60, 'Hardskill', 'Controle de Fluxo de Documentos e Planilhas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(61, 'Hardskill', 'Análise Crítica e Conferência de Documentos e Informações', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(62, 'Hardskill', 'Negociação de Preços, Prazos e Condições Comerciais', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(63, 'Hardskill', 'Conhecimento Básico de Cálculos Financeiros', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(64, 'Hardskill', 'Atualização sobre Mercado e Itens de Consumo', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(65, 'Hardskill', 'Conferência de Notas Fiscais, Faturas e Boletos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(66, 'Hardskill', 'Preenchimento de Formulários, Cadastros e Planilhas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(67, 'Hardskill', 'Registro de Documentos e Correspondências', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(68, 'Hardskill', 'Gestão de Terminais Portuários', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(69, 'Hardskill', 'Instalação e Manutenção de Sistemas de Irrigação', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(70, 'Hardskill', 'Verificação de Pressão e Funcionamento de Bombas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(71, 'Hardskill', 'Reparos em Tubulações e Aspersores', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(72, 'Hardskill', 'Leitura e Interpretação de Cronogramas e Mapas de Irrigação', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(73, 'Hardskill', 'Coleta e Preparo de Amostras Laboratoriais', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(74, 'Hardskill', 'Lavagem e Esterilização de Vidrarias', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(75, 'Hardskill', 'Apoio a Análises Laboratoriais Simples', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(76, 'Hardskill', 'Lubrificação e Troca de Peças', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(77, 'Hardskill', 'Apoio em Desmontagens e Montagem de Conjuntos Mecânicos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(78, 'Hardskill', 'Operação Básica de Ferramentas Manuais e Pneumáticas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(79, 'Hardskill', 'Execução Técnica (Triagem, Pesagem, Montagem, Etiquetagem, Banho', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(80, 'Hardskill', 'Habilidades Manuais e Visão Espacial', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(81, 'Hardskill', 'Agilidade e Destreza Manual', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(82, 'Hardskill', 'Conhecimento de Produtos e Técnicas de Limpeza', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(83, 'Hardskill', 'Operação de Ferramentas Manuais e Motorizadas (Agrícolas)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(84, 'Hardskill', 'Zelo Ambiental e Práticas Sustentáveis', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(85, 'Hardskill', 'Contas a Pagar e Receber e Conciliação Bancária', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(86, 'Hardskill', 'Domínio de Sistemas ERP (Específico Financeiro)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(87, 'Hardskill', 'Manutenção Básica de Veículos (Caminhões)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(88, 'Hardskill', 'Planejamento de Viagens e Rotas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(89, 'Hardskill', 'Controle de Carga e Documentação (Logística)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(90, 'Hardskill', 'Supervisão de Frota e Condução Econômica', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(91, 'Hardskill', 'Planejamento de Cardápios e Execução de Receitas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(92, 'Hardskill', 'Controle de Estoque e Gestão de Custos (Cozinha)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(93, 'Hardskill', 'Softwares CAD/3D e Design Gráfico', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(94, 'Hardskill', 'Diagnóstico de Falhas e Montagem de Instalações Elétricas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(95, 'Hardskill', 'Leitura de Esquemas Elétricos e Medições Técnicas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(96, 'Hardskill', 'Desenvolvimento de Processos Biotecnológicos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(97, 'Hardskill', 'Análise Laboratorial (Biossensores) e Gestão de Resíduos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(98, 'Hardskill', 'Planejamento de Colheita e Elaboração de Cronogramas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(99, 'Hardskill', 'Verificação de Pressão e Vazão em Irrigação', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(100, 'Hardskill', 'Leitura de Projetos e Cálculo de Materiais (Construção Civil)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(101, 'Hardskill', 'Operação de Máquinas (Construção Civil)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(102, 'Hardskill', 'Gestão de Insumos (Viveiro) e Nutrição de Plantas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(103, 'Hardskill', 'Gestão de Composto Orgânico e Treinamento Técnico', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(104, 'Hardskill', 'Análise de Riscos e Controle Financeiro (Gerencial)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(105, 'Hardskill', 'Planejamento Estratégico e Gestão de Recursos', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(106, 'Hardskill', 'Inglês Fluente (Comercial e Técnico)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(107, 'Hardskill', 'Fluxo de Caixa e Análise de Demonstrativos (Gerencial)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(108, 'Hardskill', 'Interpretação de Normas de Qualidade', 'ISO, HACCP, BPF, GLOBAL GAP..etc', '2025-10-17 15:41:42', '2026-04-13 17:14:29'),
(109, 'Hardskill', 'Inspeção Visual e Instrumentos de Medição (Qualidade)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(110, 'Hardskill', 'Operação de Roçadeira e Motosserra', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(111, 'Hardskill', 'Poda, Plantio, Adubação e Controle de Pragas (Jardinagem)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(112, 'Hardskill', 'Controle de Qualidade Alimentar e Desperdícios (Cozinha)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(113, 'Hardskill', 'Interpretação Técnica e Análise de Vibração (Mecânica)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(114, 'Hardskill', 'Operação de Torno e Solda (Mecânica)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(115, 'Hardskill', 'Operação Segura de Empilhadeiras e Acessórios', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(116, 'Hardskill', 'Inspeção Visual, Conferência e Manutenção Preventiva (Empilhadei', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(117, 'Hardskill', 'Conhecimento Técnico de Poda e Corte de Vegetação', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(118, 'Hardskill', 'Leitura de Plantas, Assentamento de Blocos e Reboco (Pedreiro)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(119, 'Hardskill', 'Programação Web e Integração com Banco de Dados', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(120, 'Hardskill', 'Testes Automatizados e Controle de Versão (Git)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(121, 'Hardskill', 'Atendimento ao Público e Operação de PABX', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(122, 'Hardskill', 'Conhecimento Técnico em Solda (Eletrodo, TIG) e Desenhos Técnico', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(123, 'Hardskill', 'Gestão de Fornecedores e Análise de Especificações', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(124, 'Hardskill', 'Logística de Embalagem e Análise de Indicadores (Packing)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(125, 'Hardskill', 'Realização de Procedimentos de Enfermagem e Controle de Sinais V', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(126, 'Hardskill', 'Administração de Medicações e Organização de Materiais de Saúde', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(127, 'Hardskill', 'Instalação, Configuração e Manutenção de Redes (TI)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(128, 'Hardskill', 'Gestão de Servidores, Backups e Segurança de Dados', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(129, 'Hardskill', 'Conhecimento em NRs e Investigação de Acidentes', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(130, 'Hardskill', 'Ministrar Treinamentos de Segurança e Elaborar Relatórios Técnic', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(131, 'Hardskill', 'Operação de Tratores e Implementos Agrícolas (Geral)', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(132, 'Hardskill', 'Leitura e Interpretação de Mapas de Manejo', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(133, 'Hardskill', 'Ajustes e Manutenções Básicas em Máquinas Agrícolas', NULL, '2025-10-17 15:41:42', '2025-10-17 15:41:42'),
(134, 'Softskill', 'Liderança de Equipes', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26'),
(135, 'Softskill', 'Gestão de Conflitos', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26'),
(136, 'Hardskill', 'Manutenção de Motores 2 Tempos', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26'),
(137, 'Hardskill', 'Operação de Tratores', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26'),
(138, 'Hardskill', 'Manejo de Pragas e Doenças', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26'),
(139, 'Hardskill', 'Corte e Costura Industrial', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26'),
(140, 'Hardskill', 'Hidráulica e Irrigação', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26'),
(141, 'Hardskill', 'Controle de Estoque (PEPS)', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26'),
(142, 'Hardskill', 'Certificação Orgânica', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26'),
(143, 'Hardskill', 'Planejamento Agronômico', NULL, '2026-03-10 16:43:26', '2026-03-10 16:43:26'),
(159, 'Hardskill', 'Domínio no uso do fumegador e ferramentas de maneio', '', '2026-03-13 17:54:47', '2026-03-13 17:54:47'),
(160, 'Hardskill', 'Técnicas de captura de enxames e troca de rainhas.', '', '2026-03-13 17:54:58', '2026-03-13 17:54:58'),
(161, 'Hardskill', 'Conhecimento sobre o comportamento das abelhas face ao uso de de', '', '2026-03-13 17:55:09', '2026-03-13 17:55:09'),
(162, 'Hardskill', 'Manutenção de caixas, quadros e ceras.', '', '2026-03-13 17:55:15', '2026-03-13 17:55:15'),
(163, 'Hardskill', 'Domínio de diferentes pontos de costura', '', '2026-03-13 19:46:33', '2026-03-13 19:46:33'),
(164, 'Hardskill', 'Conhecimento de tipos de tecidos, malhas e linhas', '', '2026-03-13 19:46:55', '2026-03-13 19:46:55'),
(165, 'Hardskill', 'Habilidade em máquinas industriais', '', '2026-03-13 19:47:09', '2026-03-13 19:47:09'),
(166, 'Hardskill', 'Técnicas de Corte e Costura.', '', '2026-03-13 19:47:27', '2026-03-13 19:47:27'),
(167, 'Hardskill', 'Solicitação Orçamento', '', '2026-04-13 13:57:03', '2026-04-13 13:57:03'),
(168, 'Hardskill', 'Instalação e manutenção de hardware e software', '', '2026-04-13 18:08:14', '2026-04-13 18:08:14'),
(169, 'Hardskill', 'Configuração e manutenção de redes com e sem fio', '', '2026-04-13 18:08:22', '2026-04-13 18:08:22'),
(170, 'Hardskill', 'Instalação e manutenção de sistemas de segurança eletrônica e CF', '', '2026-04-13 18:08:31', '2026-04-13 18:08:31'),
(171, 'Hardskill', 'Diagnóstico e correção de falhas em hardware e software', '', '2026-04-13 18:08:39', '2026-04-13 18:08:39'),
(172, 'Hardskill', 'Gestão e configuração de servidores e sistemas operacionais', '', '2026-04-13 18:08:49', '2026-04-13 18:08:49'),
(173, 'Hardskill', 'Execução de backups e segurança de dados', '', '2026-04-13 18:08:59', '2026-04-13 18:08:59'),
(174, 'Hardskill', 'Atendimento técnico a clientes internos e externos', '', '2026-04-13 18:09:08', '2026-04-13 18:09:08'),
(175, 'Hardskill', 'Administração de chamados e suporte técnico', '', '2026-04-13 18:09:14', '2026-04-13 18:09:14'),
(176, 'Hardskill', 'Planejamento e execução de manutenções preventivas e corretivas', '', '2026-04-13 18:09:23', '2026-04-13 18:09:23'),
(177, 'Hardskill', 'Organização de cabeamento, controle térmico e disposição de equi', '', '2026-04-13 18:09:31', '2026-04-13 18:09:31'),
(178, 'Hardskill', 'Gerenciamento de incidentes, contas de usuários e permissões de ', '', '2026-04-13 18:09:43', '2026-04-13 18:09:43'),
(179, 'Hardskill', 'Capacidade analítica, organização e agilidade no atendimento', '', '2026-04-13 18:09:50', '2026-04-13 18:09:50'),
(180, 'Hardskill', 'Desenvolvimento de interfaces gráficas e relatórios', '', '2026-04-13 18:39:18', '2026-04-13 18:39:18'),
(181, 'Hardskill', 'Aplicação de critérios ergonômicos de navegação', '', '2026-04-13 18:39:30', '2026-04-13 18:39:30'),
(182, 'Hardskill', 'Codificação, compilação e teste de programas e aplicativos', '', '2026-04-13 18:39:39', '2026-04-13 18:39:39'),
(183, 'Hardskill', 'Manutenção e melhoria de sistemas ERP', '', '2026-04-13 18:39:46', '2026-04-13 18:39:46'),
(184, 'Hardskill', 'Modelagem e administração de banco de dados', '', '2026-04-13 18:39:53', '2026-04-13 18:39:53'),
(185, 'Hardskill', 'Documentação e homologação de sistemas', '', '2026-04-13 18:39:59', '2026-04-13 18:39:59'),
(186, 'Hardskill', 'Coleta e análise de requisitos de usuários', '', '2026-04-13 18:40:10', '2026-04-13 18:40:10'),
(187, 'Hardskill', 'Definição de metodologias, linguagens e ferramentas de desenvolv', '', '2026-04-13 18:40:17', '2026-04-13 18:40:17'),
(188, 'Hardskill', '•	• Capacidade de raciocínio lógico, análise crítica e solução d', '', '2026-04-13 18:40:30', '2026-04-13 18:40:30'),
(189, 'Hardskill', 'Pesquisa e aplicação de novas tecnologias', '', '2026-04-13 18:40:38', '2026-04-13 18:40:38'),
(190, 'Hardskill', 'Controle de Fluxo de Pessoas e Veículos', '', '2026-04-17 12:13:40', '2026-04-17 12:15:43'),
(191, 'Hardskill', 'Identificação e Encaminhamento de Pessoas', '', '2026-04-17 12:14:00', '2026-04-17 12:14:00'),
(192, 'Hardskill', 'Receber e Conferir Documentos e Materiais', '', '2026-04-17 12:14:22', '2026-04-17 12:14:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `habilidades_cargo`
--

CREATE TABLE `habilidades_cargo` (
  `habilidadeCargoId` int(5) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `habilidadeId` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `habilidades_cargo`
--

INSERT INTO `habilidades_cargo` (`habilidadeCargoId`, `cargoId`, `habilidadeId`) VALUES
(182, 13, 1),
(183, 13, 2),
(184, 13, 3),
(185, 13, 4),
(186, 13, 5),
(187, 13, 6),
(188, 13, 7),
(189, 13, 8),
(190, 13, 9),
(191, 13, 10),
(192, 13, 11),
(193, 13, 12),
(194, 13, 13),
(195, 13, 14),
(196, 13, 15),
(272, 19, 1),
(273, 19, 2),
(274, 19, 3),
(275, 19, 4),
(276, 19, 5),
(277, 19, 6),
(278, 19, 7),
(279, 19, 8),
(280, 19, 9),
(281, 19, 10),
(282, 19, 11),
(283, 19, 12),
(284, 19, 13),
(285, 19, 14),
(286, 19, 15),
(302, 21, 1),
(303, 21, 2),
(304, 21, 3),
(305, 21, 4),
(306, 21, 5),
(307, 21, 6),
(308, 21, 7),
(309, 21, 8),
(310, 21, 9),
(311, 21, 10),
(312, 21, 11),
(313, 21, 12),
(314, 21, 13),
(315, 21, 14),
(316, 21, 15),
(317, 22, 1),
(318, 22, 2),
(319, 22, 3),
(320, 22, 4),
(321, 22, 5),
(322, 22, 6),
(323, 22, 7),
(324, 22, 8),
(325, 22, 9),
(326, 22, 10),
(327, 22, 11),
(328, 22, 12),
(329, 22, 13),
(330, 22, 14),
(331, 22, 15),
(407, 28, 1),
(408, 28, 2),
(409, 28, 3),
(410, 28, 4),
(411, 28, 5),
(412, 28, 6),
(413, 28, 7),
(414, 28, 8),
(415, 28, 9),
(416, 28, 10),
(417, 28, 11),
(418, 28, 12),
(419, 28, 13),
(420, 28, 14),
(421, 28, 15),
(437, 30, 1),
(438, 30, 2),
(439, 30, 3),
(440, 30, 4),
(441, 30, 5),
(442, 30, 6),
(443, 30, 7),
(444, 30, 8),
(445, 30, 9),
(446, 30, 10),
(447, 30, 11),
(448, 30, 12),
(449, 30, 13),
(450, 30, 14),
(451, 30, 15),
(467, 32, 1),
(468, 32, 2),
(469, 32, 3),
(470, 32, 4),
(471, 32, 5),
(472, 32, 6),
(473, 32, 7),
(474, 32, 8),
(475, 32, 9),
(476, 32, 10),
(477, 32, 11),
(478, 32, 12),
(479, 32, 13),
(480, 32, 14),
(481, 32, 15),
(542, 37, 1),
(543, 37, 2),
(544, 37, 3),
(545, 37, 4),
(546, 37, 5),
(547, 37, 6),
(548, 37, 7),
(549, 37, 8),
(550, 37, 9),
(551, 37, 10),
(552, 37, 11),
(553, 37, 12),
(554, 37, 13),
(555, 37, 14),
(556, 37, 15),
(914, 13, 69),
(915, 13, 70),
(916, 13, 71),
(917, 13, 72),
(918, 13, 33),
(938, 19, 44),
(939, 19, 83),
(940, 19, 84),
(941, 19, 47),
(947, 21, 87),
(948, 21, 88),
(949, 21, 89),
(950, 21, 90),
(951, 21, 21),
(952, 22, 91),
(953, 22, 92),
(954, 22, 21),
(973, 28, 69),
(974, 28, 99),
(975, 28, 27),
(976, 28, 25),
(977, 28, 21),
(982, 30, 100),
(983, 30, 25),
(984, 30, 49),
(985, 30, 21),
(986, 30, 101),
(993, 32, 19),
(994, 32, 18),
(995, 32, 24),
(996, 32, 12),
(997, 32, 103),
(998, 32, 21),
(1018, 37, 23),
(1019, 37, 3),
(1020, 37, 1),
(1484, 27, 12),
(1485, 27, 11),
(1486, 27, 13),
(1487, 27, 15),
(1488, 27, 18),
(1489, 27, 98),
(1490, 27, 16),
(1491, 27, 14),
(1492, 27, 3),
(1493, 27, 21),
(1494, 27, 5),
(1495, 27, 9),
(1496, 27, 7),
(1497, 27, 1),
(1498, 27, 2),
(1499, 27, 6),
(1500, 27, 10),
(1501, 27, 8),
(1502, 27, 4),
(1745, 58, 12),
(1746, 58, 11),
(1747, 58, 13),
(1748, 58, 15),
(1749, 58, 14),
(1750, 58, 3),
(1751, 58, 5),
(1752, 58, 5),
(1753, 58, 9),
(1754, 58, 7),
(1755, 58, 7),
(1756, 58, 1),
(1757, 58, 1),
(1758, 58, 2),
(1759, 58, 2),
(1760, 58, 6),
(1761, 58, 10),
(1762, 58, 8),
(1763, 58, 4),
(1938, 41, 12),
(1939, 41, 11),
(1940, 41, 13),
(1941, 41, 15),
(1942, 41, 117),
(1943, 41, 110),
(1944, 41, 14),
(1945, 41, 3),
(1946, 41, 47),
(1947, 41, 5),
(1948, 41, 9),
(1949, 41, 7),
(1950, 41, 1),
(1951, 41, 2),
(1952, 41, 6),
(1953, 41, 46),
(1954, 41, 10),
(1955, 41, 8),
(1956, 41, 4),
(2169, 36, 12),
(2170, 36, 11),
(2171, 36, 13),
(2172, 36, 15),
(2173, 36, 110),
(2174, 36, 111),
(2175, 36, 14),
(2176, 36, 3),
(2177, 36, 5),
(2178, 36, 9),
(2179, 36, 9),
(2180, 36, 7),
(2181, 36, 1),
(2182, 36, 1),
(2183, 36, 2),
(2184, 36, 6),
(2185, 36, 10),
(2186, 36, 8),
(2187, 36, 4),
(2228, 10, 12),
(2229, 10, 11),
(2230, 10, 13),
(2231, 10, 65),
(2232, 10, 15),
(2233, 10, 59),
(2234, 10, 66),
(2235, 10, 67),
(2236, 10, 14),
(2237, 10, 3),
(2238, 10, 47),
(2239, 10, 5),
(2240, 10, 9),
(2241, 10, 7),
(2242, 10, 1),
(2243, 10, 2),
(2244, 10, 6),
(2245, 10, 10),
(2246, 10, 8),
(2247, 10, 4),
(2332, 31, 12),
(2333, 31, 12),
(2334, 31, 11),
(2335, 31, 13),
(2336, 31, 15),
(2337, 31, 102),
(2338, 31, 18),
(2339, 31, 62),
(2340, 31, 19),
(2341, 31, 14),
(2342, 31, 3),
(2343, 31, 21),
(2344, 31, 5),
(2345, 31, 9),
(2346, 31, 7),
(2347, 31, 1),
(2348, 31, 2),
(2349, 31, 6),
(2350, 31, 10),
(2351, 31, 8),
(2352, 31, 4),
(2429, 59, 104),
(2430, 59, 12),
(2431, 59, 11),
(2432, 59, 13),
(2433, 59, 15),
(2434, 59, 106),
(2435, 59, 62),
(2436, 59, 105),
(2437, 59, 14),
(2438, 59, 3),
(2439, 59, 21),
(2440, 59, 5),
(2441, 59, 9),
(2442, 59, 7),
(2443, 59, 1),
(2444, 59, 2),
(2445, 59, 6),
(2446, 59, 10),
(2447, 59, 8),
(2448, 59, 4),
(2449, 23, 81),
(2450, 23, 12),
(2451, 23, 11),
(2452, 23, 13),
(2453, 23, 15),
(2454, 23, 83),
(2455, 23, 14),
(2456, 23, 3),
(2457, 23, 47),
(2458, 23, 5),
(2459, 23, 9),
(2460, 23, 7),
(2461, 23, 1),
(2462, 23, 2),
(2463, 23, 6),
(2464, 23, 10),
(2465, 23, 8),
(2466, 23, 4),
(2467, 5, 12),
(2468, 5, 43),
(2469, 5, 11),
(2470, 5, 13),
(2471, 5, 15),
(2472, 5, 44),
(2473, 5, 14),
(2474, 5, 3),
(2475, 5, 48),
(2476, 5, 47),
(2477, 5, 5),
(2478, 5, 9),
(2479, 5, 7),
(2480, 5, 1),
(2481, 5, 2),
(2482, 5, 6),
(2483, 5, 46),
(2484, 5, 10),
(2485, 5, 8),
(2486, 5, 4),
(2622, 46, 12),
(2623, 46, 11),
(2624, 46, 13),
(2625, 46, 15),
(2626, 46, 14),
(2627, 46, 3),
(2628, 46, 5),
(2629, 46, 5),
(2630, 46, 9),
(2631, 46, 7),
(2632, 46, 7),
(2633, 46, 1),
(2634, 46, 1),
(2635, 46, 2),
(2636, 46, 2),
(2637, 46, 6),
(2638, 46, 10),
(2639, 46, 8),
(2640, 46, 4),
(2984, 15, 77),
(2985, 15, 12),
(2986, 15, 11),
(2987, 15, 13),
(2988, 15, 15),
(2989, 15, 76),
(2990, 15, 78),
(2991, 15, 14),
(2992, 15, 3),
(2993, 15, 5),
(2994, 15, 9),
(2995, 15, 7),
(2996, 15, 1),
(2997, 15, 2),
(2998, 15, 6),
(2999, 15, 10),
(3000, 15, 8),
(3001, 15, 4),
(3002, 39, 17),
(3003, 39, 27),
(3004, 39, 12),
(3005, 39, 11),
(3006, 39, 13),
(3007, 39, 15),
(3008, 39, 25),
(3009, 39, 14),
(3010, 39, 3),
(3011, 39, 5),
(3012, 39, 5),
(3013, 39, 9),
(3014, 39, 7),
(3015, 39, 7),
(3016, 39, 1),
(3017, 39, 1),
(3018, 39, 2),
(3019, 39, 6),
(3020, 39, 10),
(3021, 39, 8),
(3022, 39, 4),
(3023, 39, 4),
(3042, 45, 27),
(3043, 45, 12),
(3044, 45, 11),
(3045, 45, 13),
(3046, 45, 15),
(3047, 45, 14),
(3048, 45, 3),
(3049, 45, 5),
(3050, 45, 9),
(3051, 45, 7),
(3052, 45, 7),
(3053, 45, 1),
(3054, 45, 1),
(3055, 45, 2),
(3056, 45, 6),
(3057, 45, 10),
(3058, 45, 8),
(3059, 45, 4),
(3420, 53, 12),
(3421, 53, 11),
(3422, 53, 13),
(3423, 53, 15),
(3424, 53, 14),
(3425, 53, 3),
(3426, 53, 5),
(3427, 53, 5),
(3428, 53, 9),
(3429, 53, 7),
(3430, 53, 1),
(3431, 53, 1),
(3432, 53, 2),
(3433, 53, 2),
(3434, 53, 6),
(3435, 53, 10),
(3436, 53, 8),
(3437, 53, 4),
(3528, 55, 12),
(3529, 55, 11),
(3530, 55, 13),
(3531, 55, 15),
(3532, 55, 14),
(3533, 55, 3),
(3534, 55, 5),
(3535, 55, 5),
(3536, 55, 9),
(3537, 55, 7),
(3538, 55, 7),
(3539, 55, 1),
(3540, 55, 1),
(3541, 55, 2),
(3542, 55, 6),
(3543, 55, 10),
(3544, 55, 8),
(3545, 55, 4),
(3663, 3, 12),
(3664, 3, 11),
(3665, 3, 13),
(3666, 3, 15),
(3667, 3, 30),
(3668, 3, 29),
(3669, 3, 32),
(3670, 3, 14),
(3671, 3, 31),
(3672, 3, 34),
(3673, 3, 33),
(3674, 3, 3),
(3675, 3, 5),
(3676, 3, 9),
(3677, 3, 7),
(3678, 3, 1),
(3679, 3, 2),
(3680, 3, 6),
(3681, 3, 10),
(3682, 3, 8),
(3683, 3, 4),
(3885, 26, 17),
(3886, 26, 97),
(3887, 26, 12),
(3888, 26, 11),
(3889, 26, 13),
(3890, 26, 15),
(3891, 26, 96),
(3892, 26, 25),
(3893, 26, 14),
(3894, 26, 3),
(3895, 26, 5),
(3896, 26, 9),
(3897, 26, 7),
(3898, 26, 1),
(3899, 26, 2),
(3900, 26, 6),
(3901, 26, 10),
(3902, 26, 8),
(3903, 26, 4),
(4024, 7, 12),
(4025, 7, 11),
(4026, 7, 13),
(4027, 7, 15),
(4028, 7, 58),
(4029, 7, 56),
(4030, 7, 30),
(4031, 7, 57),
(4032, 7, 14),
(4033, 7, 33),
(4034, 7, 3),
(4035, 7, 5),
(4036, 7, 9),
(4037, 7, 7),
(4038, 7, 1),
(4039, 7, 2),
(4040, 7, 6),
(4041, 7, 10),
(4042, 7, 8),
(4043, 7, 4),
(4238, 14, 12),
(4239, 14, 11),
(4240, 14, 73),
(4241, 14, 13),
(4242, 14, 15),
(4243, 14, 19),
(4244, 14, 14),
(4245, 14, 3),
(4246, 14, 5),
(4247, 14, 9),
(4248, 14, 7),
(4249, 14, 1),
(4250, 14, 1),
(4251, 14, 2),
(4252, 14, 6),
(4253, 14, 10),
(4254, 14, 8),
(4255, 14, 4),
(4364, 60, 12),
(4365, 60, 11),
(4366, 60, 13),
(4367, 60, 15),
(4368, 60, 82),
(4369, 60, 30),
(4370, 60, 14),
(4371, 60, 34),
(4372, 60, 3),
(4373, 60, 5),
(4374, 60, 9),
(4375, 60, 7),
(4376, 60, 1),
(4377, 60, 2),
(4378, 60, 6),
(4379, 60, 10),
(4380, 60, 8),
(4381, 60, 4),
(5481, 1, 17),
(5482, 1, 12),
(5483, 1, 11),
(5484, 1, 13),
(5485, 1, 15),
(5486, 1, 18),
(5487, 1, 19),
(5488, 1, 20),
(5489, 1, 16),
(5490, 1, 14),
(5491, 1, 3),
(5492, 1, 21),
(5493, 1, 5),
(5494, 1, 23),
(5495, 1, 9),
(5496, 1, 7),
(5497, 1, 1),
(5498, 1, 2),
(5499, 1, 6),
(5500, 1, 22),
(5501, 1, 10),
(5502, 1, 8),
(5503, 1, 4),
(5690, 65, 17),
(5691, 65, 21),
(5692, 65, 5),
(5693, 65, 26),
(5694, 65, 25),
(5695, 65, 140),
(5696, 65, 138),
(5705, 67, 81),
(5706, 67, 77),
(5707, 67, 33),
(5708, 67, 12),
(5709, 67, 13),
(5710, 67, 76),
(5711, 67, 78),
(5712, 67, 83),
(5731, 68, 17),
(5732, 68, 27),
(5733, 68, 135),
(5734, 68, 28),
(5735, 68, 78),
(5736, 68, 83),
(5737, 68, 114),
(5738, 68, 137),
(5739, 68, 131),
(5816, 38, 12),
(5817, 38, 3),
(5818, 38, 11),
(5819, 38, 13),
(5820, 38, 5),
(5821, 38, 15),
(5822, 38, 9),
(5823, 38, 7),
(5824, 38, 1),
(5825, 38, 2),
(5826, 38, 6),
(5827, 38, 10),
(5828, 38, 8),
(5829, 38, 4),
(5830, 38, 14),
(5850, 69, 33),
(5851, 69, 12),
(5852, 69, 13),
(5853, 69, 82),
(5854, 69, 102),
(5855, 69, 131),
(5856, 69, 4),
(5861, 70, 81),
(5862, 70, 11),
(5863, 70, 13),
(5864, 70, 19),
(5865, 70, 4),
(5866, 70, 14),
(5867, 70, 84),
(5936, 71, 17),
(5937, 71, 27),
(5938, 71, 133),
(5939, 71, 12),
(5940, 71, 11),
(5941, 71, 13),
(5942, 71, 5),
(5943, 71, 38),
(5944, 71, 1),
(5945, 71, 28),
(5946, 71, 134),
(5947, 71, 105),
(5948, 71, 10),
(5949, 71, 4),
(5950, 71, 14),
(5951, 71, 84),
(5997, 54, 12),
(5998, 54, 3),
(5999, 54, 11),
(6000, 54, 13),
(6001, 54, 5),
(6002, 54, 15),
(6003, 54, 9),
(6004, 54, 7),
(6005, 54, 1),
(6006, 54, 2),
(6007, 54, 6),
(6008, 54, 10),
(6009, 54, 8),
(6010, 54, 4),
(6011, 54, 14),
(6012, 81, 17),
(6013, 81, 27),
(6014, 81, 13),
(6015, 81, 1),
(6016, 81, 28),
(6017, 81, 10),
(6018, 81, 8),
(6019, 81, 4),
(6020, 81, 31),
(6021, 81, 84),
(6122, 11, 34),
(6123, 11, 12),
(6124, 11, 3),
(6125, 11, 11),
(6126, 11, 21),
(6127, 11, 13),
(6128, 11, 5),
(6129, 11, 15),
(6130, 11, 9),
(6131, 11, 7),
(6132, 11, 1),
(6133, 11, 30),
(6134, 11, 2),
(6135, 11, 49),
(6136, 11, 6),
(6137, 11, 10),
(6138, 11, 8),
(6139, 11, 4),
(6140, 11, 14),
(6141, 11, 31),
(6196, 73, 33),
(6197, 73, 47),
(6198, 73, 164),
(6199, 73, 139),
(6200, 73, 163),
(6201, 73, 1),
(6202, 73, 165),
(6203, 73, 2),
(6204, 77, 17),
(6205, 77, 27),
(6206, 77, 81),
(6207, 77, 13),
(6208, 77, 129),
(6209, 77, 28),
(6210, 77, 134),
(6211, 77, 138),
(6212, 77, 19),
(6213, 77, 84),
(6461, 33, 17),
(6462, 33, 104),
(6463, 33, 12),
(6464, 33, 3),
(6465, 33, 11),
(6466, 33, 21),
(6467, 33, 13),
(6468, 33, 5),
(6469, 33, 15),
(6470, 33, 9),
(6471, 33, 7),
(6472, 33, 1),
(6473, 33, 135),
(6474, 33, 28),
(6475, 33, 106),
(6476, 33, 62),
(6477, 33, 105),
(6478, 33, 2),
(6479, 33, 6),
(6480, 33, 10),
(6481, 33, 8),
(6482, 33, 4),
(6483, 33, 14),
(6664, 24, 12),
(6665, 24, 3),
(6666, 24, 11),
(6667, 24, 13),
(6668, 24, 5),
(6669, 24, 15),
(6670, 24, 9),
(6671, 24, 7),
(6672, 24, 1),
(6673, 24, 2),
(6674, 24, 49),
(6675, 24, 6),
(6676, 24, 10),
(6677, 24, 8),
(6678, 24, 93),
(6679, 24, 167),
(6680, 24, 4),
(6681, 24, 14),
(6876, 52, 12),
(6877, 52, 3),
(6878, 52, 11),
(6879, 52, 13),
(6880, 52, 5),
(6881, 52, 15),
(6882, 52, 129),
(6883, 52, 60),
(6884, 52, 9),
(6885, 52, 7),
(6886, 52, 1),
(6887, 52, 135),
(6888, 52, 108),
(6889, 52, 66),
(6890, 52, 2),
(6891, 52, 6),
(6892, 52, 10),
(6893, 52, 8),
(6894, 52, 4),
(6895, 52, 14),
(6896, 52, 31),
(6916, 44, 33),
(6917, 44, 12),
(6918, 44, 121),
(6919, 44, 3),
(6920, 44, 11),
(6921, 44, 13),
(6922, 44, 5),
(6923, 44, 15),
(6924, 44, 60),
(6925, 44, 9),
(6926, 44, 7),
(6927, 44, 1),
(6928, 44, 2),
(6929, 44, 6),
(6930, 44, 10),
(6931, 44, 8),
(6932, 44, 4),
(6933, 44, 14),
(6934, 44, 31),
(6995, 50, 27),
(6996, 50, 126),
(6997, 50, 12),
(6998, 50, 3),
(6999, 50, 11),
(7000, 50, 13),
(7001, 50, 5),
(7002, 50, 15),
(7003, 50, 9),
(7004, 50, 7),
(7005, 50, 1),
(7006, 50, 130),
(7007, 50, 66),
(7008, 50, 2),
(7009, 50, 6),
(7010, 50, 10),
(7011, 50, 8),
(7012, 50, 4),
(7013, 50, 14),
(7014, 50, 31),
(7229, 47, 12),
(7230, 47, 3),
(7231, 47, 11),
(7232, 47, 142),
(7233, 47, 13),
(7234, 47, 5),
(7235, 47, 15),
(7236, 47, 60),
(7237, 47, 38),
(7238, 47, 23),
(7239, 47, 54),
(7240, 47, 9),
(7241, 47, 7),
(7242, 47, 1),
(7243, 47, 108),
(7244, 47, 134),
(7245, 47, 59),
(7246, 47, 2),
(7247, 47, 6),
(7248, 47, 10),
(7249, 47, 8),
(7250, 47, 16),
(7251, 47, 4),
(7252, 47, 14),
(7253, 47, 31),
(7342, 8, 61),
(7343, 8, 12),
(7344, 8, 3),
(7345, 8, 11),
(7346, 8, 47),
(7347, 8, 13),
(7348, 8, 5),
(7349, 8, 15),
(7350, 8, 60),
(7351, 8, 9),
(7352, 8, 7),
(7353, 8, 1),
(7354, 8, 108),
(7355, 8, 59),
(7356, 8, 66),
(7357, 8, 2),
(7358, 8, 6),
(7359, 8, 10),
(7360, 8, 8),
(7361, 8, 4),
(7362, 8, 14),
(7363, 8, 31),
(7437, 4, 12),
(7438, 4, 3),
(7439, 4, 42),
(7440, 4, 11),
(7441, 4, 13),
(7442, 4, 5),
(7443, 4, 37),
(7444, 4, 15),
(7445, 4, 40),
(7446, 4, 41),
(7447, 4, 38),
(7448, 4, 36),
(7449, 4, 39),
(7450, 4, 9),
(7451, 4, 7),
(7452, 4, 1),
(7453, 4, 108),
(7454, 4, 35),
(7455, 4, 2),
(7456, 4, 6),
(7457, 4, 10),
(7458, 4, 8),
(7459, 4, 4),
(7460, 4, 14),
(7461, 4, 31),
(7585, 12, 12),
(7586, 12, 3),
(7587, 12, 11),
(7588, 12, 21),
(7589, 12, 13),
(7590, 12, 5),
(7591, 12, 15),
(7592, 12, 60),
(7593, 12, 39),
(7594, 12, 9),
(7595, 12, 7),
(7596, 12, 1),
(7597, 12, 68),
(7598, 12, 108),
(7599, 12, 66),
(7600, 12, 2),
(7601, 12, 6),
(7602, 12, 10),
(7603, 12, 8),
(7604, 12, 4),
(7605, 12, 14),
(7606, 12, 31),
(7743, 51, 27),
(7744, 51, 12),
(7745, 51, 174),
(7746, 51, 3),
(7747, 51, 11),
(7748, 51, 13),
(7749, 51, 5),
(7750, 51, 169),
(7751, 51, 15),
(7752, 51, 171),
(7753, 51, 9),
(7754, 51, 7),
(7755, 51, 1),
(7756, 51, 173),
(7757, 51, 178),
(7758, 51, 128),
(7759, 51, 168),
(7760, 51, 2),
(7761, 51, 6),
(7762, 51, 10),
(7763, 51, 8),
(7764, 51, 4),
(7765, 51, 14),
(7875, 43, 188),
(7876, 43, 181),
(7877, 43, 12),
(7878, 43, 3),
(7879, 43, 11),
(7880, 43, 182),
(7881, 43, 13),
(7882, 43, 5),
(7883, 43, 15),
(7884, 43, 187),
(7885, 43, 180),
(7886, 43, 185),
(7887, 43, 9),
(7888, 43, 7),
(7889, 43, 183),
(7890, 43, 184),
(7891, 43, 2),
(7892, 43, 6),
(7893, 43, 10),
(7894, 43, 8),
(7895, 43, 4),
(7896, 43, 14),
(7897, 43, 31),
(7954, 48, 27),
(7955, 48, 12),
(7956, 48, 3),
(7957, 48, 11),
(7958, 48, 13),
(7959, 48, 5),
(7960, 48, 15),
(7961, 48, 89),
(7962, 48, 9),
(7963, 48, 7),
(7964, 48, 1),
(7965, 48, 135),
(7966, 48, 123),
(7967, 48, 28),
(7968, 48, 124),
(7969, 48, 62),
(7970, 48, 2),
(7971, 48, 6),
(7972, 48, 10),
(7973, 48, 8),
(7974, 48, 167),
(7975, 48, 4),
(7976, 48, 14),
(7977, 48, 31),
(8062, 64, 61),
(8063, 64, 33),
(8064, 64, 12),
(8065, 64, 3),
(8066, 64, 64),
(8067, 64, 11),
(8068, 64, 13),
(8069, 64, 5),
(8070, 64, 15),
(8071, 64, 63),
(8072, 64, 9),
(8073, 64, 7),
(8074, 64, 1),
(8075, 64, 62),
(8076, 64, 2),
(8077, 64, 6),
(8078, 64, 10),
(8079, 64, 8),
(8080, 64, 4),
(8081, 64, 14),
(8082, 64, 31),
(8146, 9, 61),
(8147, 9, 33),
(8148, 9, 12),
(8149, 9, 3),
(8150, 9, 64),
(8151, 9, 11),
(8152, 9, 13),
(8153, 9, 5),
(8154, 9, 15),
(8155, 9, 63),
(8156, 9, 9),
(8157, 9, 7),
(8158, 9, 1),
(8159, 9, 62),
(8160, 9, 2),
(8161, 9, 6),
(8162, 9, 10),
(8163, 9, 8),
(8164, 9, 4),
(8165, 9, 14),
(8166, 9, 31),
(8216, 62, 17),
(8217, 62, 51),
(8218, 62, 53),
(8219, 62, 12),
(8220, 62, 3),
(8221, 62, 11),
(8222, 62, 13),
(8223, 62, 5),
(8224, 62, 15),
(8225, 62, 55),
(8226, 62, 52),
(8227, 62, 25),
(8228, 62, 54),
(8229, 62, 9),
(8230, 62, 7),
(8231, 62, 1),
(8232, 62, 50),
(8233, 62, 2),
(8234, 62, 49),
(8235, 62, 6),
(8236, 62, 10),
(8237, 62, 8),
(8238, 62, 4),
(8239, 62, 14),
(8240, 62, 31),
(8263, 6, 51),
(8264, 6, 12),
(8265, 6, 3),
(8266, 6, 11),
(8267, 6, 13),
(8268, 6, 5),
(8269, 6, 15),
(8270, 6, 55),
(8271, 6, 52),
(8272, 6, 54),
(8273, 6, 9),
(8274, 6, 7),
(8275, 6, 1),
(8276, 6, 50),
(8277, 6, 2),
(8278, 6, 49),
(8279, 6, 6),
(8280, 6, 10),
(8281, 6, 8),
(8282, 6, 4),
(8283, 6, 14),
(8284, 6, 31),
(8307, 63, 51),
(8308, 63, 53),
(8309, 63, 12),
(8310, 63, 3),
(8311, 63, 11),
(8312, 63, 13),
(8313, 63, 5),
(8314, 63, 15),
(8315, 63, 55),
(8316, 63, 52),
(8317, 63, 54),
(8318, 63, 9),
(8319, 63, 7),
(8320, 63, 1),
(8321, 63, 50),
(8322, 63, 2),
(8323, 63, 49),
(8324, 63, 6),
(8325, 63, 10),
(8326, 63, 8),
(8327, 63, 4),
(8328, 63, 14),
(8376, 34, 17),
(8377, 34, 104),
(8378, 34, 12),
(8379, 34, 3),
(8380, 34, 11),
(8381, 34, 21),
(8382, 34, 13),
(8383, 34, 5),
(8384, 34, 15),
(8385, 34, 85),
(8386, 34, 86),
(8387, 34, 54),
(8388, 34, 9),
(8389, 34, 7),
(8390, 34, 1),
(8391, 34, 107),
(8392, 34, 2),
(8393, 34, 49),
(8394, 34, 6),
(8395, 34, 10),
(8396, 34, 8),
(8397, 34, 4),
(8398, 34, 14),
(8399, 34, 31),
(8462, 61, 33),
(8463, 61, 12),
(8464, 61, 3),
(8465, 61, 11),
(8466, 61, 13),
(8467, 61, 5),
(8468, 61, 15),
(8469, 61, 85),
(8470, 61, 86),
(8471, 61, 9),
(8472, 61, 7),
(8473, 61, 1),
(8474, 61, 50),
(8475, 61, 66),
(8476, 61, 2),
(8477, 61, 6),
(8478, 61, 10),
(8479, 61, 8),
(8480, 61, 4),
(8481, 61, 14),
(8482, 61, 31),
(8504, 20, 33),
(8505, 20, 12),
(8506, 20, 3),
(8507, 20, 11),
(8508, 20, 13),
(8509, 20, 5),
(8510, 20, 15),
(8511, 20, 85),
(8512, 20, 86),
(8513, 20, 9),
(8514, 20, 7),
(8515, 20, 1),
(8516, 20, 50),
(8517, 20, 66),
(8518, 20, 2),
(8519, 20, 6),
(8520, 20, 10),
(8521, 20, 8),
(8522, 20, 4),
(8523, 20, 14),
(8524, 20, 31),
(8570, 25, 17),
(8571, 25, 33),
(8572, 25, 12),
(8573, 25, 3),
(8574, 25, 48),
(8575, 25, 11),
(8576, 25, 13),
(8577, 25, 5),
(8578, 25, 15),
(8579, 25, 94),
(8580, 25, 9),
(8581, 25, 7),
(8582, 25, 1),
(8583, 25, 116),
(8584, 25, 170),
(8585, 25, 95),
(8586, 25, 176),
(8587, 25, 19),
(8588, 25, 105),
(8589, 25, 2),
(8590, 25, 49),
(8591, 25, 6),
(8592, 25, 10),
(8593, 25, 8),
(8594, 25, 4),
(8595, 25, 14),
(8603, 78, 21),
(8604, 78, 13),
(8605, 78, 38),
(8606, 78, 95),
(8607, 78, 100),
(8608, 78, 101),
(8609, 78, 19),
(8721, 42, 12),
(8722, 42, 3),
(8723, 42, 11),
(8724, 42, 21),
(8725, 42, 13),
(8726, 42, 5),
(8727, 42, 15),
(8728, 42, 9),
(8729, 42, 1),
(8730, 42, 95),
(8731, 42, 118),
(8732, 42, 100),
(8733, 42, 2),
(8734, 42, 6),
(8735, 42, 10),
(8736, 42, 8),
(8737, 42, 4),
(8738, 42, 14),
(8872, 56, 12),
(8873, 56, 3),
(8874, 56, 11),
(8875, 56, 13),
(8876, 56, 5),
(8877, 56, 15),
(8878, 56, 190),
(8879, 56, 9),
(8880, 56, 7),
(8881, 56, 1),
(8882, 56, 191),
(8883, 56, 2),
(8884, 56, 192),
(8885, 56, 6),
(8886, 56, 10),
(8887, 56, 8),
(8888, 56, 4),
(8889, 56, 14),
(8890, 56, 84),
(9194, 17, 81),
(9195, 17, 12),
(9196, 17, 3),
(9197, 17, 43),
(9198, 17, 11),
(9199, 17, 47),
(9200, 17, 13),
(9201, 17, 5),
(9202, 17, 15),
(9203, 17, 9),
(9204, 17, 7),
(9205, 17, 1),
(9206, 17, 2),
(9207, 17, 6),
(9208, 17, 10),
(9209, 17, 8),
(9210, 17, 4),
(9211, 17, 14),
(9212, 16, 81),
(9213, 16, 12),
(9214, 16, 3),
(9215, 16, 43),
(9216, 16, 11),
(9217, 16, 13),
(9218, 16, 5),
(9219, 16, 15),
(9220, 16, 9),
(9221, 16, 7),
(9222, 16, 1),
(9223, 16, 79),
(9224, 16, 80),
(9225, 16, 78),
(9226, 16, 30),
(9227, 16, 2),
(9228, 16, 6),
(9229, 16, 10),
(9230, 16, 8),
(9231, 16, 4),
(9232, 16, 14),
(9335, 40, 27),
(9336, 40, 12),
(9337, 40, 3),
(9338, 40, 11),
(9339, 40, 13),
(9340, 40, 5),
(9341, 40, 15),
(9342, 40, 9),
(9343, 40, 7),
(9344, 40, 1),
(9345, 40, 108),
(9346, 40, 115),
(9347, 40, 2),
(9348, 40, 6),
(9349, 40, 10),
(9350, 40, 8),
(9351, 40, 4),
(9352, 40, 14),
(9503, 49, 17),
(9504, 49, 81),
(9505, 49, 61),
(9506, 49, 12),
(9507, 49, 3),
(9508, 49, 43),
(9509, 49, 11),
(9510, 49, 13),
(9511, 49, 5),
(9512, 49, 15),
(9513, 49, 60),
(9514, 49, 9),
(9515, 49, 7),
(9516, 49, 1),
(9517, 49, 135),
(9518, 49, 28),
(9519, 49, 109),
(9520, 49, 108),
(9521, 49, 134),
(9522, 49, 2),
(9523, 49, 6),
(9524, 49, 10),
(9525, 49, 8),
(9526, 49, 4),
(9527, 49, 14),
(9528, 49, 31),
(9529, 2, 27),
(9530, 2, 12),
(9531, 2, 3),
(9532, 2, 11),
(9533, 2, 21),
(9534, 2, 13),
(9535, 2, 5),
(9536, 2, 15),
(9537, 2, 25),
(9538, 2, 9),
(9539, 2, 7),
(9540, 2, 1),
(9541, 2, 28),
(9542, 2, 19),
(9543, 2, 2),
(9544, 2, 6),
(9545, 2, 10),
(9546, 2, 8),
(9547, 2, 24),
(9548, 2, 4),
(9549, 2, 14),
(9664, 35, 12),
(9665, 35, 3),
(9666, 35, 43),
(9667, 35, 11),
(9668, 35, 47),
(9669, 35, 13),
(9670, 35, 5),
(9671, 35, 15),
(9672, 35, 9),
(9673, 35, 7),
(9674, 35, 1),
(9675, 35, 109),
(9676, 35, 108),
(9677, 35, 2),
(9678, 35, 6),
(9679, 35, 10),
(9680, 35, 8),
(9681, 35, 4),
(9682, 35, 14),
(9683, 35, 31),
(9792, 18, 34),
(9793, 18, 12),
(9794, 18, 3),
(9795, 18, 11),
(9796, 18, 13),
(9797, 18, 5),
(9798, 18, 15),
(9799, 18, 82),
(9800, 18, 9),
(9801, 18, 7),
(9802, 18, 1),
(9803, 18, 30),
(9804, 18, 2),
(9805, 18, 6),
(9806, 18, 10),
(9807, 18, 8),
(9808, 18, 4),
(9809, 18, 14),
(9846, 84, 34),
(9847, 84, 12),
(9848, 84, 3),
(9849, 84, 11),
(9850, 84, 13),
(9851, 84, 5),
(9852, 84, 15),
(9853, 84, 82),
(9854, 84, 9),
(9855, 84, 7),
(9856, 84, 1),
(9857, 84, 30),
(9858, 84, 2),
(9859, 84, 6),
(9860, 84, 10),
(9861, 84, 8),
(9862, 84, 4),
(9863, 84, 14),
(9932, 29, 175),
(9933, 29, 17),
(9934, 29, 27),
(9935, 29, 81),
(9936, 29, 133),
(9937, 29, 77),
(9938, 29, 12),
(9939, 29, 174),
(9940, 29, 3),
(9941, 29, 11),
(9942, 29, 13),
(9943, 29, 5),
(9944, 29, 15),
(9945, 29, 122),
(9946, 29, 94),
(9947, 29, 25),
(9948, 29, 9),
(9949, 29, 7),
(9950, 29, 1),
(9951, 29, 95),
(9952, 29, 2),
(9953, 29, 6),
(9954, 29, 10),
(9955, 29, 8),
(9956, 29, 4),
(9957, 29, 14);

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_inpc`
--

CREATE TABLE `historico_inpc` (
  `ano` int(11) NOT NULL,
  `acumulado_ano` decimal(5,2) NOT NULL COMMENT 'Percentual acumulado da inflação no ano (%)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `historico_inpc`
--

INSERT INTO `historico_inpc` (`ano`, `acumulado_ano`) VALUES
(2019, 4.48),
(2020, 5.45),
(2021, 10.16),
(2022, 5.93),
(2023, 3.71),
(2024, 4.50),
(2025, 4.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_salario_minimo`
--

CREATE TABLE `historico_salario_minimo` (
  `id` int(11) NOT NULL,
  `data_vigencia` date NOT NULL,
  `valor` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `historico_salario_minimo`
--

INSERT INTO `historico_salario_minimo` (`id`, `data_vigencia`, `valor`) VALUES
(1, '2021-01-01', 1100.00),
(2, '2022-01-01', 1212.00),
(3, '2023-01-01', 1302.00),
(4, '2023-05-01', 1320.00),
(5, '2024-01-01', 1412.00),
(6, '2025-01-01', 1502.00),
(7, '2026-01-01', 1621.00);

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

--
-- Despejando dados para a tabela `nivel_hierarquico`
--

INSERT INTO `nivel_hierarquico` (`nivelId`, `tipoId`, `nivelOrdem`, `nivelDescricao`, `nivelDataCadastro`, `nivelDataAtualizacao`, `nivelAtribuicoes`, `nivelAutonomia`, `nivelQuandoUtilizar`) VALUES
(1, 1, 7, 'Diretor', '2025-10-31 11:46:19', '2025-10-31 11:58:15', 'Define estratégias organizacionais, políticas e metas institucionais.', 'Muito alta – decisões estratégicas e institucionais.', 'Liderança geral da organização ou unidade de negócio.'),
(2, 2, 6, 'Gerente', '2025-10-31 11:46:44', '2025-10-31 11:58:03', 'Gerencia áreas e projetos, elabora planos e acompanha indicadores.', 'Alta – decisões táticas e operacionais amplas.', 'Gestão de departamentos com foco em resultados e eficiência.'),
(3, 2, 5, 'Coordenador', '2025-10-31 11:46:58', '2025-10-31 11:57:51', 'Coordena equipes, alinha metas operacionais e acompanha resultados.', 'Alta – responsabilidade sobre processos e metas.', 'Integração de áreas e supervisão de equipes distintas.'),
(4, 3, 4, 'Supervisor', '2025-10-31 11:47:20', '2025-10-31 11:57:32', 'Supervisiona processos e equipes, assegura cumprimento de metas.', 'Média a alta – acompanhamento e controle direto.', 'Coordenação de times operacionais e rotinas de produção.'),
(8, 2, 4, 'Analista', '2025-10-31 12:00:24', '2025-10-31 12:00:24', 'Realiza análises técnicas, elabora relatórios e propõe soluções para problemas específicos da área.', 'Alta – responsabilidade técnica com foco em melhoria e inovação.', 'Quando há necessidade de aprofundamento técnico e suporte à tomada de decisão.'),
(9, 3, 3, 'Encarregado', '2025-10-31 12:00:58', '2025-10-31 12:00:58', 'Lidera diretamente a equipe, orienta execução de tarefas práticas.', 'Média – orientação técnica e liderança prática.', 'Liderança prática de pequenos grupos e execução direta.'),
(10, 3, 2, 'Assistente', '2025-10-31 12:01:19', '2025-10-31 12:04:51', 'Presta apoio em atividades administrativas ou operacionais.', 'Baixa a média – executa com supervisão geral.', 'Apoio técnico-administrativo em processos de rotina.'),
(11, 3, 1, 'Auxiliar', '2025-10-31 12:05:17', '2025-10-31 12:05:17', 'Executa tarefas simples e rotineiras com acompanhamento direto.', 'Baixa – executa sob supervisão constante.', 'Tarefas simples de apoio, sem exigência de especialização.'),
(12, 3, 1, 'Operacional', '2025-10-31 12:06:11', '2025-10-31 12:06:11', 'Opera máquinas, equipamentos ou processos com domínio técnico.', 'Média – autonomia técnica sobre operação específica.', 'Funções técnicas que demandam habilidade com máquinas ou sistemas.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissions`
--

CREATE TABLE `permissions` (
  `permissionId` int(11) NOT NULL,
  `permissionName` varchar(100) NOT NULL COMMENT 'Ex: cargos:create, cargos:edit, usuarios:manage',
  `permissionDescription` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `permissions`
--

INSERT INTO `permissions` (`permissionId`, `permissionName`, `permissionDescription`) VALUES
(1, 'usuarios:manage', 'Gerenciar usuários, papéis e permissões'),
(2, 'habilidades:manage', 'Criar, editar e excluir habilidades'),
(3, 'cursos:manage', 'Criar, editar e excluir cursos'),
(4, 'riscos:manage', 'Criar, editar e excluir riscos'),
(5, 'caracteristicas:manage', 'Criar, editar e excluir características'),
(6, 'areas:manage', 'Gerenciar a hierarquia de áreas de atuação'),
(7, 'config:view', 'Visualizar páginas de configuração (ex: escolaridade)'),
(8, 'logs:view', 'Visualizar logs de auditoria do sistema'),
(9, 'cargos:view', 'Visualizar e gerenciar cargos'),
(11, 'cargos:manage', 'Permite criar, editar e excluir cargos'),
(12, 'cadastros:manage', 'Gerir Cadastros Básicos (Escolaridades, Cursos, Riscos, etc.)'),
(13, 'cargos:edit', 'Editar Cargos Existentes e Desbloquear Revisão');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pesquisa_valores`
--

CREATE TABLE `pesquisa_valores` (
  `valorId` int(11) NOT NULL,
  `campanhaId` int(11) NOT NULL,
  `empresaId` int(11) NOT NULL,
  `cboId` int(11) NOT NULL COMMENT 'Usamos o CBO para garantir que estamos comparando cargos iguais',
  `cargo_nome_mercado` varchar(255) NOT NULL COMMENT 'Como a outra empresa chama o cargo',
  `salario_base` decimal(10,2) NOT NULL,
  `ano_referencia` int(11) DEFAULT NULL,
  `salario_original` decimal(10,2) DEFAULT NULL,
  `foi_reajustado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pesquisa_valores`
--

INSERT INTO `pesquisa_valores` (`valorId`, `campanhaId`, `empresaId`, `cboId`, `cargo_nome_mercado`, `salario_base`, `ano_referencia`, `salario_original`, `foi_reajustado`) VALUES
(1, 1, 1, 26, 'Gerente', 2000.00, NULL, NULL, 0),
(2, 1, 1, 26, 'Gerente de Loja', 2500.00, NULL, NULL, 0),
(3, 1, 1, 26, 'Gerente Geral', 2450.00, NULL, NULL, 0),
(4, 1, 1, 26, 'Geee', 2550.00, NULL, NULL, 0),
(5, 1, 1, 26, 'Geee', 2550.00, NULL, NULL, 0),
(6, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1694.61, 2024, 1476.12, 1),
(7, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1951.63, 2024, 1700.00, 1),
(8, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 2050.53, 2025, 1900.00, 1),
(9, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1638.27, 2025, 1518.00, 1),
(10, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1638.27, 2025, 1518.00, 1),
(11, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1638.27, 2025, 1518.00, 1),
(12, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1662.01, 2025, 1540.00, 1),
(13, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1621.00, 2024, 1412.00, 1),
(14, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1621.00, 2024, 1412.00, 1),
(15, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1722.03, 2024, 1500.00, 1),
(16, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1722.03, 2024, 1500.00, 1),
(17, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1662.01, 2025, 1540.00, 1),
(18, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1711.56, 2024, 1490.88, 1),
(19, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1722.03, 2024, 1500.00, 1),
(20, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 1767.95, 2024, 1540.00, 1),
(21, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 2296.03, 2024, 2000.00, 1),
(22, 1, 1, 21, 'Desenhista Industrial Gráfico (Designer Gráfico)', 2158.46, 2025, 2000.00, 1),
(23, 1, 1, 20, 'Chefe De Cozinha', 3069.12, 2024, 2824.00, 1),
(24, 1, 1, 20, 'Chefe De Cozinha', 3069.12, 2024, 2824.00, 1),
(25, 1, 1, 20, 'Chefe De Cozinha', 1626.09, 2024, 1496.22, 1),
(26, 1, 1, 20, 'Chefe De Cozinha', 2175.85, 2025, 2092.16, 1),
(27, 1, 1, 20, 'Chefe De Cozinha', 2488.61, 2025, 2392.89, 1),
(28, 1, 1, 20, 'Chefe De Cozinha', 3157.44, 2025, 3036.00, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `reajustes_salariais`
--

CREATE TABLE `reajustes_salariais` (
  `reajusteId` int(11) NOT NULL,
  `tipo_reajuste` enum('salario_minimo','dissidio_sindical','merito_geral') NOT NULL,
  `percentual` decimal(5,2) DEFAULT NULL COMMENT 'Ex: 5.50 para 5,5%',
  `valor_fixo` decimal(10,2) DEFAULT NULL COMMENT 'Ex: Novo Salário Mínimo 1412.00',
  `data_vigencia` date NOT NULL,
  `numero_lei_convencao` varchar(100) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp()
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

--
-- Despejando dados para a tabela `recursos`
--

INSERT INTO `recursos` (`recursoId`, `recursoNome`, `recursoDescricao`, `recursoDataCadastro`, `recursoDataAtualizacao`) VALUES
(1, 'Equipamento de Proteção Individual (EPI)', 'Abrange todos os equipamentos de proteção individual como luvas, máscaras, toucas, botas, óculos de proteção, coletes e capacetes.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(2, 'Equipamentos de Comunicação (Rádio, Celular, Telefone)', 'Equipamentos para comunicação interna e externa, incluindo rádios comunicadores, celulares corporativos, telefones fixos (PABX) e interfones.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(3, 'Material de Escritório (Papelaria, Fichários)', 'Materiais básicos para atividades administrativas e de registro: pranchetas, papel, canetas, borracha, grampeador, furador, etiquetas e demais itens de papelaria.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(4, 'Instrumentos de Medição Agrícola (pHmetro, Termômetro, Balança)', 'Instrumentos usados para aferições no campo ou laboratório: pHmetro, termômetro, balança, GPS, e outros medidores de pressão, vazão e qualidade de frutos.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(5, 'Máquinas Agrícolas e Implementos (Tratores, Pulverizadores)', 'Veículos motorizados e equipamentos pesados utilizados nas operações agrícolas, como tratores, roçadeiras, pulverizadores, plantadeiras, adubadeiras e carretas agrícolas.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(6, 'Veículos e Meios de Locomoção', 'Veículos de pequeno e médio porte para deslocamento e transporte de pessoal e cargas leves: carros, motocicletas e veículos utilitários.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(7, 'Ferramentas Manuais Diversas (Chaves, Alicates, Estilete)', 'Ferramentas de uso geral para reparos e atividades manuais, incluindo chaves de fenda, alicates, tesouras, estiletes, martelos e ferramentas de manutenção leve.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(8, 'Equipamentos de Refrigeração e Controle Climático', 'Equipamentos para controle de temperatura e conservação de produtos, como equipamentos de refrigeração, freezers e sistemas de controle climático.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(9, 'Defensivos Agrícolas e Fertilizantes', 'Insumos utilizados no manejo agrícola, como defensivos, fertilizantes, agrotóxicos e medicamentos veterinários.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(10, 'Material de Limpeza e Embalagem', 'Produtos e materiais destinados à higienização, organização e embalagem, como materiais de limpeza, sabões especiais, caixas, fitas e embalagens diversas.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(11, 'Mudas e Sementes', 'Insumos vegetais utilizados no plantio e manejo das culturas, incluindo mudas e sementes.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(12, 'Equipamentos de Controle de Ponto e Produtividade (Pulseiras, Ga', 'Dispositivos eletrônicos ou físicos para monitoramento da jornada e da performance dos trabalhadores, como pulseiras e ganchos de colheita.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(13, 'Softwares e Sistemas de Gestão (ERP, Pacote Office)', 'Softwares e hardwares de processamento de dados e gestão: computadores, notebooks, sistemas ERP, Pacote Office (Excel, Word, Outlook) e softwares de controle.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(14, 'Coletor de Dados e Leitor de Código de Barras', 'Dispositivos eletrônicos para captura rápida e precisa de dados e códigos, utilizados no estoque, almoxarifado ou expedição.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(15, 'Empilhadeira e Paleteira', 'Máquinas utilizadas na movimentação e elevação de cargas em armazéns e pátios, como empilhadeiras, paleteiras manuais e carrinhos mecânicos.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(16, 'Documentação Legal e Manuais de Referência', 'Materiais escritos ou digitais que estabelecem regras e procedimentos: manuais de referência, publicações legais, normas tributárias e documentos de certificação.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(17, 'Equipamentos Laboratoriais (Autoclave, Microscópio)', 'Equipamentos de alta precisão usados em análises biológicas e químicas, como autoclaves, centrífugas, capelas de fluxo laminar e câmaras de crescimento.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(18, 'Utensílios e Vidrarias de Laboratório', 'Recipientes e instrumentos de vidro ou plástico utilizados em ensaios laboratoriais: tubos de ensaio, béqueres, erlenmeyers, pipetas e similares.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(19, 'Reagentes e Meios de Cultura', 'Substâncias e compostos para análises e cultivo de microrganismos, incluindo meios de cultura sólidos e líquidos, e reagentes químicos.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(20, 'Recursos Audiovisuais (Projetor, Quadro)', 'Dispositivos para apresentações e reuniões, como projetores, quadros brancos e equipamentos audiovisuais.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(21, 'Ferramentas de Construção Civil (Betoneira, Colher de pedreiro)', 'Ferramentas específicas para construção, reforma e acabamento de estruturas civis: betoneira, prumo, nível, andaimes, colheres de pedreiro e EPIs específicos para obra.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(22, 'Ferramentas de Jardinagem e Corte (Roçadeira, Motosserra)', 'Equipamentos motorizados ou manuais para manutenção de jardins e áreas verdes, incluindo roçadeiras laterais, motosserras, tesouras de poda e rastelos.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(23, 'Utensílios e Equipamentos de Cozinha/Refeitório', 'Utensílios, máquinas e mobiliário para o preparo e conservação de alimentos: fogão, forno, geladeira, estufa, panelas, talheres e eletrodomésticos de cozinha.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(24, 'Equipamentos de Segurança Eletrônica (CFTV, Rastreamento)', 'Sistemas e dispositivos para monitoramento da segurança do patrimônio e controle de acesso, como CFTV, sistemas de rastreamento e controle de crachás.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(25, 'Ferramentas e Equipamentos Elétricos (Multímetro, Alicate Amperí', 'Instrumentos específicos para diagnóstico e reparo de sistemas elétricos: multímetro, megômetro, alicate amperímetro, chaves isoladas e furadeiras.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(26, 'Equipamentos Pneumáticos e Hidráulicos (Compressor, Macaco hidrá', 'Equipamentos que utilizam ar comprimido ou fluido hidráulico para operar: compressores, bombas, macacos hidráulicos e ferramentas pneumáticas.', '2025-10-17 15:31:23', '2025-10-17 15:32:55'),
(27, 'Máquina de Solda e Acessórios', 'Máquinas e consumíveis para união de materiais metálicos: máquina de solda (MIG, TIG, Eletrodo), maçarico, arames, eletrodos e acessórios de corte.', '2025-10-17 15:31:23', '2025-10-17 15:32:55');

-- --------------------------------------------------------

--
-- Estrutura para tabela `recursos_cargo`
--

CREATE TABLE `recursos_cargo` (
  `recursoCargoId` int(5) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `recursoId` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `recursos_cargo`
--

INSERT INTO `recursos_cargo` (`recursoCargoId`, `cargoId`, `recursoId`) VALUES
(1, 1, 2),
(2, 1, 3),
(3, 1, 7),
(4, 1, 9),
(5, 1, 10),
(6, 1, 5),
(7, 1, 8),
(8, 1, 11),
(9, 1, 4),
(10, 1, 1),
(11, 1, 12),
(12, 1, 6),
(13, 2, 5),
(14, 2, 7),
(15, 2, 4),
(16, 2, 1),
(17, 2, 3),
(18, 2, 13),
(19, 2, 6),
(20, 3, 13),
(21, 3, 14),
(22, 3, 4),
(23, 3, 15),
(24, 3, 1),
(25, 3, 3),
(26, 3, 2),
(27, 4, 13),
(28, 4, 16),
(29, 4, 2),
(30, 4, 20),
(31, 4, 3),
(32, 4, 6),
(33, 5, 7),
(34, 5, 22),
(35, 5, 1),
(36, 5, 3),
(37, 6, 13),
(38, 6, 3),
(39, 6, 16),
(40, 6, 2),
(41, 7, 17),
(42, 7, 18),
(43, 7, 19),
(44, 7, 1),
(45, 7, 12),
(46, 7, 10),
(47, 8, 13),
(48, 8, 3),
(49, 8, 6),
(50, 8, 1),
(51, 9, 13),
(52, 9, 2),
(53, 9, 3),
(54, 10, 13),
(55, 10, 3),
(56, 10, 16),
(57, 10, 2),
(58, 10, 1),
(59, 11, 13),
(60, 11, 4),
(61, 11, 15),
(62, 11, 3),
(63, 11, 7),
(64, 11, 1),
(65, 11, 2),
(66, 12, 13),
(67, 12, 2),
(68, 13, 7),
(69, 13, 1),
(70, 14, 18),
(71, 14, 17),
(72, 14, 19),
(73, 14, 1),
(74, 15, 7),
(75, 15, 26),
(76, 15, 1),
(77, 15, 8),
(78, 16, 7),
(79, 16, 4),
(80, 16, 1),
(81, 16, 13),
(82, 16, 9),
(83, 17, 9),
(84, 17, 7),
(85, 17, 1),
(86, 18, 10),
(87, 18, 1),
(88, 19, 7),
(89, 19, 5),
(90, 19, 1),
(91, 20, 13),
(92, 20, 3),
(93, 20, 2),
(94, 21, 2),
(95, 21, 5),
(96, 21, 7),
(97, 21, 1),
(98, 22, 23),
(99, 22, 13),
(100, 23, 7),
(101, 23, 1),
(102, 23, 12),
(103, 23, 9),
(104, 24, 13),
(105, 24, 20),
(106, 24, 7),
(107, 25, 25),
(108, 25, 7),
(109, 25, 1),
(110, 26, 17),
(111, 26, 13),
(112, 26, 1),
(113, 26, 7),
(114, 27, 5),
(115, 27, 1),
(116, 27, 2),
(117, 27, 7),
(118, 28, 5),
(119, 28, 4),
(120, 28, 6),
(121, 28, 7),
(122, 28, 1),
(123, 29, 7),
(124, 29, 25),
(125, 29, 1),
(126, 29, 13),
(127, 30, 7),
(128, 30, 4),
(129, 30, 1),
(130, 30, 6),
(131, 30, 21),
(132, 31, 5),
(133, 31, 7),
(134, 31, 13),
(135, 31, 2),
(136, 31, 1),
(137, 31, 4),
(138, 32, 5),
(139, 32, 7),
(140, 32, 1),
(141, 32, 2),
(142, 32, 15),
(143, 32, 13),
(144, 33, 13),
(145, 33, 2),
(146, 33, 20),
(147, 33, 16),
(148, 34, 13),
(149, 34, 20),
(150, 34, 16),
(151, 35, 4),
(152, 35, 1),
(153, 35, 13),
(154, 35, 3),
(155, 36, 22),
(156, 36, 7),
(157, 36, 9),
(158, 36, 1),
(159, 37, 23),
(160, 37, 3),
(161, 37, 1),
(162, 38, 7),
(163, 38, 26),
(164, 38, 13),
(165, 38, 1),
(166, 39, 7),
(167, 39, 4),
(168, 39, 5),
(169, 39, 1),
(170, 39, 27),
(171, 40, 15),
(172, 40, 1),
(173, 40, 6),
(174, 41, 22),
(175, 41, 7),
(176, 41, 1),
(177, 42, 21),
(178, 42, 1),
(179, 42, 7),
(180, 43, 13),
(181, 43, 1),
(182, 44, 2),
(183, 44, 13),
(184, 44, 3),
(185, 44, 16),
(186, 45, 1),
(187, 45, 27),
(188, 45, 7),
(189, 45, 25),
(190, 45, 17),
(191, 46, 5),
(192, 46, 7),
(193, 46, 1),
(194, 46, 2),
(195, 47, 13),
(196, 47, 4),
(197, 47, 16),
(198, 48, 13),
(199, 48, 16),
(200, 48, 6),
(201, 49, 5),
(202, 49, 4),
(203, 49, 13),
(204, 49, 1),
(205, 50, 1),
(206, 50, 4),
(207, 50, 2),
(208, 50, 16),
(209, 50, 3),
(210, 51, 13),
(211, 51, 7),
(212, 51, 25),
(213, 51, 24),
(214, 51, 2),
(215, 52, 1),
(216, 52, 13),
(217, 52, 4),
(218, 52, 6),
(219, 52, 2),
(220, 53, 5),
(221, 53, 1),
(222, 53, 2),
(223, 54, 5),
(224, 54, 7),
(225, 54, 1),
(226, 55, 5),
(227, 55, 7),
(228, 55, 1),
(229, 56, 24),
(230, 56, 6),
(231, 56, 2),
(232, 56, 3);

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

--
-- Despejando dados para a tabela `recursos_grupos`
--

INSERT INTO `recursos_grupos` (`recursoGrupoId`, `recursoGrupoNome`, `recursoGrupoDataCadastro`, `recursoGrupoDataAtualizacao`) VALUES
(1, 'TI e Comunicação', '2025-10-17 15:34:35', '2025-10-17 15:34:35'),
(2, 'Material de Escritório', '2025-10-17 15:34:35', '2025-10-17 15:34:35'),
(3, 'Documentação e Registros', '2025-10-17 15:34:35', '2025-10-17 15:34:35'),
(4, 'Ferramentas e Manutenção', '2025-10-17 15:34:35', '2025-10-17 15:34:35'),
(5, 'Maquinário e Veículos', '2025-10-17 15:34:35', '2025-10-17 15:34:35'),
(6, 'Medição e Testes', '2025-10-17 15:34:35', '2025-10-17 15:34:35'),
(7, 'Laboratório e Biofábrica', '2025-10-17 15:34:35', '2025-10-17 15:34:35'),
(8, 'Insumos e Materiais', '2025-10-17 15:34:35', '2025-10-17 15:34:35'),
(9, 'Processo Produtivo', '2025-10-17 15:34:35', '2025-10-17 15:34:35'),
(10, 'Segurança e Proteção', '2025-10-17 15:34:35', '2025-10-17 15:34:35');

-- --------------------------------------------------------

--
-- Estrutura para tabela `recursos_grupos_cargo`
--

CREATE TABLE `recursos_grupos_cargo` (
  `recursoGrupoCargoId` int(11) NOT NULL,
  `cargoId` int(5) NOT NULL,
  `recursoGrupoId` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `recursos_grupos_cargo`
--

INSERT INTO `recursos_grupos_cargo` (`recursoGrupoCargoId`, `cargoId`, `recursoGrupoId`) VALUES
(1028, 1, 1),
(1029, 1, 2),
(1030, 1, 4),
(1031, 1, 5),
(1032, 1, 6),
(1033, 1, 8),
(1034, 1, 9),
(1035, 1, 10),
(1832, 2, 1),
(1833, 2, 2),
(1834, 2, 4),
(1835, 2, 5),
(1836, 2, 6),
(1837, 2, 10),
(757, 3, 1),
(758, 3, 2),
(759, 3, 5),
(760, 3, 6),
(761, 3, 10),
(1463, 4, 1),
(1464, 4, 2),
(1465, 4, 3),
(1466, 4, 5),
(543, 5, 2),
(544, 5, 4),
(545, 5, 10),
(1579, 6, 1),
(1580, 6, 2),
(1581, 6, 3),
(836, 7, 7),
(837, 7, 8),
(838, 7, 9),
(839, 7, 10),
(1447, 8, 1),
(1448, 8, 2),
(1449, 8, 5),
(1450, 8, 10),
(1564, 9, 1),
(1565, 9, 2),
(1566, 9, 5),
(496, 10, 1),
(497, 10, 2),
(498, 10, 3),
(499, 10, 10),
(1232, 11, 1),
(1233, 11, 2),
(1234, 11, 4),
(1235, 11, 5),
(1236, 11, 6),
(1237, 11, 10),
(1479, 12, 1),
(1480, 12, 2),
(1481, 12, 3),
(1482, 12, 8),
(119, 13, 4),
(9, 13, 10),
(862, 14, 7),
(863, 14, 10),
(639, 15, 4),
(640, 15, 9),
(641, 15, 10),
(1771, 16, 1),
(1772, 16, 4),
(1773, 16, 6),
(1774, 16, 8),
(1775, 16, 10),
(1767, 17, 4),
(1768, 17, 8),
(1769, 17, 9),
(1770, 17, 10),
(1884, 18, 5),
(1885, 18, 8),
(1886, 18, 10),
(123, 19, 4),
(96, 19, 5),
(15, 19, 10),
(1612, 20, 1),
(1613, 20, 2),
(1614, 20, 8),
(52, 21, 1),
(124, 21, 4),
(97, 21, 5),
(16, 21, 10),
(159, 22, 1),
(185, 22, 9),
(539, 23, 4),
(540, 23, 8),
(541, 23, 9),
(542, 23, 10),
(1308, 24, 1),
(1309, 24, 4),
(1622, 25, 4),
(1623, 25, 5),
(1624, 25, 6),
(1625, 25, 8),
(1626, 25, 10),
(808, 26, 1),
(809, 26, 4),
(810, 26, 7),
(811, 26, 10),
(361, 27, 1),
(362, 27, 4),
(363, 27, 5),
(364, 27, 10),
(130, 28, 4),
(99, 28, 5),
(85, 28, 6),
(21, 28, 10),
(1907, 29, 1),
(1908, 29, 4),
(1909, 29, 8),
(1910, 29, 10),
(132, 30, 4),
(110, 30, 5),
(86, 30, 6),
(23, 30, 10),
(520, 31, 1),
(521, 31, 4),
(522, 31, 5),
(523, 31, 6),
(524, 31, 10),
(55, 32, 1),
(134, 32, 4),
(101, 32, 5),
(25, 32, 10),
(1284, 33, 1),
(1285, 33, 2),
(1286, 33, 3),
(1287, 33, 9),
(1594, 34, 1),
(1595, 34, 2),
(1596, 34, 3),
(1597, 34, 8),
(1862, 35, 1),
(1863, 35, 2),
(1864, 35, 6),
(1865, 35, 10),
(485, 36, 4),
(486, 36, 8),
(487, 36, 10),
(76, 37, 2),
(186, 37, 9),
(28, 37, 10),
(1133, 38, 1),
(1134, 38, 4),
(1135, 38, 10),
(642, 39, 4),
(643, 39, 5),
(644, 39, 6),
(645, 39, 10),
(1791, 40, 4),
(1792, 40, 5),
(1793, 40, 8),
(1794, 40, 9),
(1795, 40, 10),
(451, 41, 4),
(452, 41, 10),
(1655, 42, 4),
(1656, 42, 5),
(1657, 42, 8),
(1658, 42, 10),
(1525, 43, 1),
(1526, 43, 2),
(1527, 43, 8),
(1528, 43, 10),
(1365, 44, 1),
(1366, 44, 2),
(1367, 44, 3),
(649, 45, 4),
(650, 45, 7),
(651, 45, 10),
(576, 46, 1),
(577, 46, 4),
(578, 46, 5),
(579, 46, 10),
(1428, 47, 1),
(1429, 47, 3),
(1430, 47, 6),
(1540, 48, 1),
(1541, 48, 2),
(1542, 48, 3),
(1543, 48, 5),
(1544, 48, 8),
(1828, 49, 1),
(1829, 49, 5),
(1830, 49, 6),
(1831, 49, 10),
(1383, 50, 1),
(1384, 50, 2),
(1385, 50, 3),
(1386, 50, 6),
(1387, 50, 10),
(1505, 51, 1),
(1506, 51, 4),
(1507, 51, 5),
(1508, 51, 10),
(1358, 52, 1),
(1359, 52, 5),
(1360, 52, 6),
(1361, 52, 10),
(712, 53, 1),
(713, 53, 5),
(714, 53, 10),
(1190, 54, 1),
(1191, 54, 2),
(1192, 54, 3),
(1193, 54, 4),
(1194, 54, 5),
(1195, 54, 10),
(730, 55, 4),
(731, 55, 5),
(732, 55, 10),
(1691, 56, 1),
(1692, 56, 2),
(1693, 56, 5),
(1694, 56, 10),
(425, 58, 1),
(426, 58, 4),
(427, 58, 5),
(428, 58, 10),
(537, 59, 1),
(538, 59, 3),
(882, 60, 5),
(883, 60, 8),
(884, 60, 10),
(1606, 61, 1),
(1607, 61, 2),
(1608, 61, 8),
(1573, 62, 1),
(1574, 62, 2),
(1575, 62, 3),
(1585, 63, 1),
(1586, 63, 2),
(1587, 63, 3),
(1554, 64, 1),
(1555, 64, 2),
(1556, 64, 5),
(1090, 67, 4),
(1091, 67, 5),
(1092, 67, 10),
(1113, 68, 1),
(1114, 68, 4),
(1115, 68, 5),
(1116, 68, 6),
(1117, 68, 10),
(1171, 71, 1),
(1172, 71, 2),
(1173, 71, 3),
(1174, 71, 4),
(1175, 71, 5),
(1176, 71, 8),
(1177, 71, 10),
(1241, 73, 4),
(1242, 73, 8),
(1243, 73, 10),
(1632, 78, 4),
(1633, 78, 5),
(1634, 78, 6),
(1635, 78, 8),
(1636, 78, 10),
(1201, 81, 1),
(1199, 81, 2),
(1196, 81, 3),
(1198, 81, 5),
(1197, 81, 8),
(1200, 81, 10),
(1893, 84, 5),
(1894, 84, 8),
(1895, 84, 10);

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

--
-- Despejando dados para a tabela `recurso_grupo_recurso`
--

INSERT INTO `recurso_grupo_recurso` (`recursoGrupoRecursoId`, `recursoGrupoId`, `recursoId`, `recursoGrupoRecursoDataCadastro`, `recursoGrupoRecuraoDataAtualizacao`) VALUES
(28, 10, 1, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(29, 1, 2, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(30, 2, 3, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(31, 6, 4, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(32, 5, 5, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(33, 5, 6, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(34, 4, 7, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(35, 9, 8, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(36, 8, 9, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(37, 8, 10, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(38, 8, 11, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(39, 9, 12, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(40, 1, 13, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(41, 1, 14, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(42, 5, 15, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(43, 3, 16, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(44, 7, 17, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(45, 7, 18, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(46, 7, 19, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(47, 1, 20, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(48, 4, 21, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(49, 4, 22, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(50, 9, 23, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(51, 10, 24, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(52, 4, 25, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(53, 4, 26, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(54, 4, 27, '2025-10-17 15:34:57', '2025-10-17 15:34:57'),
(55, 10, 1, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(56, 1, 2, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(57, 2, 3, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(58, 6, 4, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(59, 5, 5, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(60, 5, 6, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(61, 4, 7, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(62, 9, 8, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(63, 8, 9, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(64, 8, 10, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(65, 8, 11, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(66, 9, 12, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(67, 1, 13, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(68, 1, 14, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(69, 5, 15, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(70, 3, 16, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(71, 7, 17, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(72, 7, 18, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(73, 7, 19, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(74, 1, 20, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(75, 4, 21, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(76, 4, 22, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(77, 9, 23, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(78, 10, 24, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(79, 4, 25, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(80, 4, 26, '2025-10-17 16:18:08', '2025-10-17 16:18:08'),
(81, 4, 27, '2025-10-17 16:18:08', '2025-10-17 16:18:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `riscos`
--

CREATE TABLE `riscos` (
  `riscoId` int(5) NOT NULL,
  `riscoNome` enum('Físico','Químico','Ergonômico','Psicossocial','Acidental','Biológico') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `riscos`
--

INSERT INTO `riscos` (`riscoId`, `riscoNome`) VALUES
(1, 'Físico'),
(2, 'Químico'),
(3, 'Ergonômico'),
(4, 'Psicossocial'),
(5, 'Acidental'),
(6, 'Biológico');

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

--
-- Despejando dados para a tabela `riscos_cargo`
--

INSERT INTO `riscos_cargo` (`riscoCargoId`, `riscoId`, `cargoId`, `riscoDescricao`, `riscoDataCadastro`, `riscoDataAtualizacao`) VALUES
(234, 1, 13, 'Exposição a sol intenso e condições climáticas adversas, risco de queda em terreno irregular', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(235, 3, 13, 'Riscos ergonômicos e de esforço repetitivo', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(236, 2, 13, 'Contato com produtos químicos em casos de fertirrigação', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(258, 1, 19, 'Riscos físicos (trabalho ao ar livre, exposição climática, esforço físico contínuo)', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(259, 3, 19, 'Riscos ergonômicos', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(260, 5, 19, 'Riscos acidentais (uso de ferramentas)', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(261, 2, 19, 'Riscos químicos', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(262, 4, 19, 'Riscos psicossociais', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(265, 1, 21, 'Exposição a intempéries, ruído, vibração, carga manual', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(266, 5, 21, 'Acidentes em trânsito', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(267, 4, 21, 'Estresse por prazos', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(268, 1, 22, 'Calor, queimaduras, pisos molhados', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(269, 5, 22, 'Cortes (utensílios cortantes)', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(270, 3, 22, 'Esforço repetitivo', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(271, 4, 22, 'Pressão por prazos', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(288, 1, 28, 'Exposição ao sol, esforço físico', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(289, 2, 28, 'Fertilizantes (manuseio)', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(290, 4, 28, 'Pressão por resultados', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(294, 5, 30, 'Quedas, altura', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(295, 1, 30, 'Ruído, exposição a calor, poeira', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(296, 2, 30, 'Exposição a produtos químicos (argamassa, cimento)', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(297, 3, 30, 'Esforço físico', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(302, 2, 32, 'Riscos químicos e biológicos', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(303, 1, 32, 'Riscos físicos e ergonômicos', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(304, 4, 32, 'Riscos psicossociais (pressão por metas, liderança)', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(316, 1, 37, 'Calor, queimaduras, ambiente quente e úmido', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(317, 5, 37, 'Cortes (utensílios)', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(318, 2, 37, 'Produtos químicos (limpeza)', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(319, 4, 37, 'Pressão por prazos', '2025-10-17 17:03:21', '2025-10-17 17:03:21'),
(453, 1, 27, 'Exposição solar, poeira', '2025-10-31 13:12:04', '2025-10-31 13:12:04'),
(454, 4, 27, 'Pressão por metas', '2025-10-31 13:12:04', '2025-10-31 13:12:04'),
(455, 3, 27, 'Movimentos repetitivos, caminhadas extensas', '2025-10-31 13:12:04', '2025-10-31 13:12:04'),
(456, 5, 27, 'Uso de ferramentas e máquinas no campo, queda', '2025-10-31 13:12:04', '2025-10-31 13:12:04'),
(457, 6, 27, 'Animais Peçonhentos', '2025-10-31 13:12:04', '2025-10-31 13:12:04'),
(520, 2, 58, 'Defensivos químicos', '2025-10-31 14:14:32', '2025-10-31 14:14:32'),
(521, 5, 58, 'Maquinário (acidentes)', '2025-10-31 14:14:32', '2025-10-31 14:14:32'),
(522, 3, 58, 'Esforço físico', '2025-10-31 14:14:32', '2025-10-31 14:14:32'),
(523, 1, 58, 'Clima (variações climáticas)', '2025-10-31 14:14:32', '2025-10-31 14:14:32'),
(524, 4, 58, 'Pressão por metas', '2025-10-31 14:14:32', '2025-10-31 14:14:32'),
(567, 1, 41, 'Exposição a ruído, esforço físico contínuo, condições climáticas adversas', '2025-10-31 14:32:20', '2025-10-31 14:32:20'),
(568, 3, 41, 'Riscos ergonômicos', '2025-10-31 14:32:20', '2025-10-31 14:32:20'),
(569, 4, 41, 'Riscos psicossociais', '2025-10-31 14:32:20', '2025-10-31 14:32:20'),
(570, 5, 41, 'Ferramentas cortantes', '2025-10-31 14:32:20', '2025-10-31 14:32:20'),
(571, 6, 41, 'Resíduos vegetais', '2025-10-31 14:32:20', '2025-10-31 14:32:20'),
(625, 5, 36, 'Ferramentas cortantes (roçadeira, facão)', '2025-10-31 16:21:41', '2025-10-31 16:21:41'),
(626, 1, 36, 'Condições climáticas, esforço físico', '2025-10-31 16:21:41', '2025-10-31 16:21:41'),
(627, 6, 36, 'Animais Peçonhentos', '2025-10-31 16:21:41', '2025-10-31 16:21:41'),
(628, 2, 36, 'Produtos Químicos (pulverizador)', '2025-10-31 16:21:41', '2025-10-31 16:21:41'),
(635, 3, 10, 'Uso prolongado de computador, postura inadequada', '2025-10-31 16:30:48', '2025-10-31 16:30:48'),
(636, 4, 10, 'Pressão por prazos e volume de tarefas', '2025-10-31 16:30:48', '2025-10-31 16:30:48'),
(637, 1, 10, 'Riscos físicos leves (manuseio de documentos e materiais de escritório)', '2025-10-31 16:30:48', '2025-10-31 16:30:48'),
(656, 1, 31, 'Riscos físicos (trabalho em campo, intempéries)', '2025-10-31 16:43:05', '2025-10-31 16:43:05'),
(657, 3, 31, 'Riscos ergonômicos', '2025-10-31 16:43:05', '2025-10-31 16:43:05'),
(658, 2, 31, 'Riscos químicos', '2025-10-31 16:43:05', '2025-10-31 16:43:05'),
(659, 6, 31, 'Animais Peçonhentos, Resíduos Vegetais', '2025-10-31 16:43:05', '2025-10-31 16:43:05'),
(660, 4, 31, 'Gestão de equipe, pressão por metas', '2025-10-31 16:43:05', '2025-10-31 16:43:05'),
(677, 4, 59, 'Estresse ocupacional, demandas simultâneas, decisões estratégicas críticas', '2025-10-31 17:58:10', '2025-10-31 17:58:10'),
(678, 6, 23, 'Animais peçonhentos', '2025-11-01 00:17:55', '2025-11-01 00:17:55'),
(679, 5, 23, 'Uso de ferramentas e máquinas no campo', '2025-11-01 00:17:55', '2025-11-01 00:17:55'),
(680, 3, 23, 'Movimentos repetitivos, caminhadas extensas', '2025-11-01 00:17:55', '2025-11-01 00:17:55'),
(681, 1, 23, 'Campo aberto com exposição climática e esforço físico contínuo', '2025-11-01 00:17:55', '2025-11-01 00:17:55'),
(682, 1, 5, 'Quedas, acidentes com ferramentas, exposição ao sol', '2025-11-07 10:57:52', '2025-11-07 10:57:52'),
(683, 3, 5, 'Esforço físico repetitivo, longas caminhadas', '2025-11-07 10:57:52', '2025-11-07 10:57:52'),
(684, 4, 5, 'Trabalho solitário e sob pressão por resultados', '2025-11-07 10:57:52', '2025-11-07 10:57:52'),
(685, 6, 5, 'Contato com defensivos agrícolas', '2025-11-07 10:57:52', '2025-11-07 10:57:52'),
(686, 2, 5, 'Contato com defensivos agrícolas', '2025-11-07 10:57:52', '2025-11-07 10:57:52'),
(722, 2, 46, 'Defensivos químicos', '2025-11-07 15:57:33', '2025-11-07 15:57:33'),
(723, 5, 46, 'Queda', '2025-11-07 15:57:33', '2025-11-07 15:57:33'),
(724, 1, 46, 'Clima (variações climáticas)', '2025-11-07 15:57:33', '2025-11-07 15:57:33'),
(725, 4, 46, 'Pressão por metas', '2025-11-07 15:57:33', '2025-11-07 15:57:33'),
(797, 1, 15, 'Exposição a ruído', '2025-11-07 16:44:38', '2025-11-07 16:44:38'),
(798, 2, 15, 'Agentes químicos, oléos e combustíveis', '2025-11-07 16:44:38', '2025-11-07 16:44:38'),
(799, 5, 15, 'Riscos de cortes, queimaduras e esmagamento', '2025-11-07 16:44:38', '2025-11-07 16:44:38'),
(800, 3, 15, 'Riscos ergonômicos e esforço físico prolongado', '2025-11-07 16:44:38', '2025-11-07 16:44:38'),
(801, 1, 39, 'Ruído, calor, poeira', '2025-11-07 16:44:59', '2025-11-07 16:44:59'),
(802, 2, 39, 'Agentes químicos, oléos e combustíveis', '2025-11-07 16:44:59', '2025-11-07 16:44:59'),
(803, 3, 39, 'Riscos ergonômicos e esforço físico prolongado', '2025-11-07 16:44:59', '2025-11-07 16:44:59'),
(804, 4, 39, 'pressão por decisão e prazos', '2025-11-07 16:44:59', '2025-11-07 16:44:59'),
(805, 5, 39, 'Riscos de cortes, queimaduras e esmagamento', '2025-11-07 16:44:59', '2025-11-07 16:44:59'),
(809, 1, 45, 'Ruído, calor, poeiras, choques elétricos, queimaduras, esforço físico', '2025-11-07 16:48:33', '2025-11-07 16:48:33'),
(810, 2, 45, 'Fumos metálicos, produtos químicos', '2025-11-07 16:48:33', '2025-11-07 16:48:33'),
(811, 3, 45, 'postura, esforço', '2025-11-07 16:48:33', '2025-11-07 16:48:33'),
(887, 5, 53, 'Acidentes com máquinas', '2025-11-07 17:20:54', '2025-11-07 17:20:54'),
(888, 2, 53, 'Exposição a defensivos agrícolas e produtos químicos', '2025-11-07 17:20:54', '2025-11-07 17:20:54'),
(889, 1, 53, 'Vibração, ruído, poeira, calor', '2025-11-07 17:20:54', '2025-11-07 17:20:54'),
(890, 6, 53, 'Fungos e Bactérias (entomopatogênicas)', '2025-11-07 17:20:54', '2025-11-07 17:20:54'),
(909, 1, 55, 'Vibração, ruído, poeira, calor', '2025-11-07 17:28:15', '2025-11-07 17:28:15'),
(910, 5, 55, 'Acidentes com máquinas', '2025-11-07 17:28:15', '2025-11-07 17:28:15'),
(911, 6, 55, 'Fungos e Bactérias (entomopatogênicas)', '2025-11-07 17:28:15', '2025-11-07 17:28:15'),
(912, 2, 55, 'Exposição a defensivos agrícolas e produtos químicos', '2025-11-07 17:28:15', '2025-11-07 17:28:15'),
(934, 1, 3, 'Exposição a variações térmicas, calor, poeira, umidade', '2025-11-07 17:40:10', '2025-11-07 17:40:10'),
(935, 5, 3, 'Movimentação de cargas pesadas, empilhadeiras, quedas, cortes, colisões com equipamentos', '2025-11-07 17:40:10', '2025-11-07 17:40:10'),
(936, 3, 3, 'Postura inadequada, esforço repetitivo', '2025-11-07 17:40:10', '2025-11-07 17:40:10'),
(937, 4, 3, 'Pressão por produtividade, conflitos operacionais', '2025-11-07 17:40:10', '2025-11-07 17:40:10'),
(981, 2, 26, 'Agentes químicos', '2025-11-07 17:59:03', '2025-11-07 17:59:03'),
(982, 5, 26, 'Queimaduras (equipamentos de laboratório)', '2025-11-07 17:59:03', '2025-11-07 17:59:03'),
(983, 3, 26, 'Postura prolongada', '2025-11-07 17:59:03', '2025-11-07 17:59:03'),
(984, 4, 26, 'Estresse ocupacional', '2025-11-07 17:59:03', '2025-11-07 17:59:03'),
(985, 6, 26, 'Fungos e Bactérias (entomopatogênicas)', '2025-11-07 17:59:03', '2025-11-07 17:59:03'),
(1005, 2, 7, 'Exposição a produtos químicos controlados', '2025-11-07 18:11:15', '2025-11-07 18:11:15'),
(1006, 5, 7, 'acidentes com vidrarias e equipamentos aquecidos, falhas operacionais', '2025-11-07 18:11:15', '2025-11-07 18:11:15'),
(1007, 3, 7, 'Repetitividade de movimentos e posturas prolongadas', '2025-11-07 18:11:15', '2025-11-07 18:11:15'),
(1008, 6, 7, 'Fungos e Bactérias (entomopatogênico)', '2025-11-07 18:11:15', '2025-11-07 18:11:15'),
(1044, 2, 14, 'Exposição a produtos químicos', '2025-11-07 18:22:54', '2025-11-07 18:22:54'),
(1045, 5, 14, 'Riscos de queimaduras ou cortes com equipamentos', '2025-11-07 18:22:54', '2025-11-07 18:22:54'),
(1046, 3, 14, 'Posturas inadequadas ou repetitivas', '2025-11-07 18:22:54', '2025-11-07 18:22:54'),
(1047, 6, 14, 'Fungos e Bactérias (entomopatogênicas)', '2025-11-07 18:22:54', '2025-11-07 18:22:54'),
(1077, 1, 60, 'Esforço físico', '2025-11-07 18:31:42', '2025-11-07 18:31:42'),
(1078, 3, 60, 'Repetição', '2025-11-07 18:31:42', '2025-11-07 18:31:42'),
(1079, 2, 60, 'Exposição a produtos químicos (limpeza)', '2025-11-07 18:31:42', '2025-11-07 18:31:42'),
(1080, 6, 60, 'Resíduos Sanitários, Fungos e Bactérias (entomopatogênico)', '2025-11-07 18:31:42', '2025-11-07 18:31:42'),
(1216, 3, 1, 'Movimentos repetitivos, caminhadas extensas', '2026-02-28 02:43:37', '2026-02-28 02:43:37'),
(1217, 5, 1, 'Uso de ferramentas e máquinas no campo', '2026-02-28 02:43:37', '2026-02-28 02:43:37'),
(1218, 4, 1, 'Pressão por metas, responsabilidade sobre equipe', '2026-02-28 02:43:37', '2026-02-28 02:43:37'),
(1219, 6, 1, 'Animais Peçonhentos', '2026-02-28 02:43:37', '2026-02-28 02:43:37'),
(1220, 1, 1, 'Campo aberto com exposição climática', '2026-02-28 02:43:37', '2026-02-28 02:43:37'),
(1263, 1, 65, 'Radiação solar, calor e poeira', '2026-03-13 16:41:25', '2026-03-13 16:41:25'),
(1264, 2, 65, 'Contato/exposição a defensivos agrícolas e fertilizantes.', '2026-03-13 16:41:25', '2026-03-13 16:41:25'),
(1265, 6, 65, 'Animais peçonhentos e microrganismos do solo.', '2026-03-13 16:41:25', '2026-03-13 16:41:25'),
(1272, 1, 67, 'Ruído excessivo (testes de motores) e vibrações.', '2026-03-13 17:01:06', '2026-03-13 17:01:06'),
(1273, 2, 67, 'Exposição a hidrocarbonetos (gasolina, óleos, solventes) e gases de combustão.', '2026-03-13 17:01:06', '2026-03-13 17:01:06'),
(1274, 5, 67, 'Risco de cortes em partes móveis, queimaduras em superfícies quentes e projeção de partículas.', '2026-03-13 17:01:06', '2026-03-13 17:01:06'),
(1287, 1, 68, 'Ruído contínuo, vibrações e calor.', '2026-03-13 17:11:39', '2026-03-13 17:11:39'),
(1288, 2, 68, 'Manuseio constante de óleos lubrificantes, massas, solventes e combustível.', '2026-03-13 17:11:39', '2026-03-13 17:11:39'),
(1289, 5, 68, 'Risco de esmagamento (trabalho com cargas suspensas), queimaduras e projeção de detritos.', '2026-03-13 17:11:39', '2026-03-13 17:11:39'),
(1310, 3, 38, 'Riscos ergonômicos', '2026-03-13 17:27:00', '2026-03-13 17:27:00'),
(1311, 2, 38, 'Óleos, graxas, combustíveis', '2026-03-13 17:27:00', '2026-03-13 17:27:00'),
(1312, 1, 38, 'Ruído, Esforço físico', '2026-03-13 17:27:00', '2026-03-13 17:27:00'),
(1313, 5, 38, 'Uso de ferramentas, máquinas, explosão', '2026-03-13 17:27:00', '2026-03-13 17:27:00'),
(1342, 1, 69, 'Umidade constante e ruído de motobombas', '2026-03-13 17:47:50', '2026-03-13 17:47:50'),
(1343, 2, 69, 'Exposição direta a concentrados de defensivos agrícolas e fertilizantes.', '2026-03-13 17:47:50', '2026-03-13 17:47:50'),
(1344, 3, 69, 'Levantamento de peso (galões) e postura em pé', '2026-03-13 17:47:50', '2026-03-13 17:47:50'),
(1345, 6, 69, 'Exposição devido ao manejo de bioinsumos', '2026-03-13 17:47:50', '2026-03-13 17:47:50'),
(1346, 5, 69, 'Queda', '2026-03-13 17:47:50', '2026-03-13 17:47:50'),
(1354, 6, 70, 'Risco de picadas e reações alérgicas', '2026-03-13 18:14:13', '2026-03-13 18:14:13'),
(1355, 1, 70, 'Exposição prolongada ao sol e calor.', '2026-03-13 18:14:13', '2026-03-13 18:14:13'),
(1356, 3, 70, 'Peso (transporte de melgueiras).', '2026-03-13 18:14:13', '2026-03-13 18:14:13'),
(1357, 2, 70, 'Eventual contacto com fumo ou resíduos de tratamentos das colmeias.', '2026-03-13 18:14:13', '2026-03-13 18:14:13'),
(1382, 1, 71, 'Exposição prolongada ao sol, calor e ruído de motores.', '2026-03-13 18:43:50', '2026-03-13 18:43:50'),
(1383, 5, 71, 'Risco de colisões, tombamentos de máquinas e acidentes em terrenos irregulares durante a supervisão.', '2026-03-13 18:43:50', '2026-03-13 18:43:50'),
(1384, 3, 71, 'Fadiga por deslocamentos constantes e postura sentada prolongada em veículos de apoio.', '2026-03-13 18:43:50', '2026-03-13 18:43:50'),
(1385, 4, 71, 'Elevada pressão por cumprimento de metas de rendimento e responsabilidade sobre patrimônio de alto custo.', '2026-03-13 18:43:50', '2026-03-13 18:43:50'),
(1395, 1, 54, 'Ruído, calor', '2026-03-13 19:06:40', '2026-03-13 19:06:40'),
(1396, 2, 54, 'Defensivos (exposição a agentes químicos)', '2026-03-13 19:06:40', '2026-03-13 19:06:40'),
(1397, 3, 54, 'Esforço físico', '2026-03-13 19:06:40', '2026-03-13 19:06:40'),
(1398, 5, 81, 'Queda, Corte', '2026-03-13 19:24:05', '2026-03-13 19:24:05'),
(1399, 1, 81, 'Calor, Poeira', '2026-03-13 19:24:05', '2026-03-13 19:24:05'),
(1400, 2, 81, 'Tintas, Abrasivos', '2026-03-13 19:24:05', '2026-03-13 19:24:05'),
(1401, 3, 81, 'Esforço repetitivo, Postura, Peso', '2026-03-13 19:24:05', '2026-03-13 19:24:05'),
(1417, 5, 11, 'Manuseio de ferramentas cortantes (estiletes) e risco de quedas.', '2026-03-13 19:40:26', '2026-03-13 19:40:26'),
(1418, 2, 11, 'Contato eventual com embalagens de defensivos ou produtos de limpeza.', '2026-03-13 19:40:26', '2026-03-13 19:40:26'),
(1419, 3, 11, 'Levantamento de peso e atividades repetitivas.', '2026-03-13 19:40:26', '2026-03-13 19:40:26'),
(1420, 1, 11, 'Exposição ao calor, poeira e movimentação de equipamentos de transporte.', '2026-03-13 19:40:26', '2026-03-13 19:40:26'),
(1448, 1, 73, 'Ruído contínuo de máquinas e riscos de perfuração acidental (agulhas).', '2026-03-13 19:56:40', '2026-03-13 19:56:40'),
(1449, 3, 73, 'Postura sentada prolongada, movimentos repetitivos dos membros superiores e esforço visual constante.', '2026-03-13 19:56:40', '2026-03-13 19:56:40'),
(1450, 5, 73, 'Risco de lesões por manuseio de tesouras e equipamentos de corte.', '2026-03-13 19:56:40', '2026-03-13 19:56:40'),
(1455, 6, 77, 'Contato com agentes biológicos presentes no manejo de plantas e compostagem', '2026-03-13 20:19:45', '2026-03-13 20:19:45'),
(1456, 1, 77, 'Manuseio de equipamentos e ferramentas manuais ou pesadas. Calor proviniente da Estufa', '2026-03-13 20:19:45', '2026-03-13 20:19:45'),
(1457, 4, 77, 'Estresse decorrente da pressão por metas de produção e gestão de equipe', '2026-03-13 20:19:45', '2026-03-13 20:19:45'),
(1458, 3, 77, 'Esforço físico e permanência em posições prolongadas', '2026-03-13 20:19:45', '2026-03-13 20:19:45'),
(1482, 4, 33, 'Estresse ocupacional, demandas simultâneas, decisões estratégicas críticas', '2026-04-13 13:47:18', '2026-04-13 13:47:18'),
(1483, 3, 33, 'Uso prolongado de computadores', '2026-04-13 13:47:18', '2026-04-13 13:47:18'),
(1507, 3, 24, 'Esforço visual, postura prolongada', '2026-04-13 14:04:07', '2026-04-13 14:04:07'),
(1508, 5, 24, 'Manuseio de estilete (cortes)', '2026-04-13 14:04:07', '2026-04-13 14:04:07'),
(1509, 4, 24, 'Pressão por resultados e prazos', '2026-04-13 14:04:07', '2026-04-13 14:04:07'),
(1554, 4, 52, 'Pressão por cumprimento de normas e prazos', '2026-04-13 14:39:59', '2026-04-13 14:39:59'),
(1555, 1, 52, 'Ambientes perigosos, exposição a intempéries e riscos', '2026-04-13 14:39:59', '2026-04-13 14:39:59'),
(1556, 2, 52, 'Exposição a produtos químicos', '2026-04-13 14:39:59', '2026-04-13 14:39:59'),
(1557, 5, 52, 'Deslocamentos frequentes', '2026-04-13 14:39:59', '2026-04-13 14:39:59'),
(1560, 4, 44, 'Estresse, exposição a conflitos interpessoais', '2026-04-13 14:40:44', '2026-04-13 14:40:44'),
(1561, 3, 44, 'Sobrecarga visual', '2026-04-13 14:40:44', '2026-04-13 14:40:44'),
(1574, 3, 50, 'Postura prolongada, atividades repetitivas', '2026-04-13 14:51:15', '2026-04-13 14:51:15'),
(1575, 5, 50, 'Manuseio de materiais perfurocortantes', '2026-04-13 14:51:15', '2026-04-13 14:51:15'),
(1576, 4, 50, 'Exposição a situações de emergência e sofrimento, resiliência emocional e senso de urgência', '2026-04-13 14:51:15', '2026-04-13 14:51:15'),
(1577, 6, 50, 'Contato com material infectante', '2026-04-13 14:51:15', '2026-04-13 14:51:15'),
(1609, 3, 47, 'Postura prolongada, uso intenso de computador', '2026-04-13 17:16:52', '2026-04-13 17:16:52'),
(1610, 4, 47, 'Pressão por prazos, exigências externas e múltiplas tarefas simultâneas', '2026-04-13 17:16:52', '2026-04-13 17:16:52'),
(1619, 3, 8, 'Postura prolongada, uso intenso de computador', '2026-04-13 17:22:11', '2026-04-13 17:22:11'),
(1620, 4, 8, 'Pressão por prazos, exigências externas e múltiplas tarefas simultâneas', '2026-04-13 17:22:11', '2026-04-13 17:22:11'),
(1628, 4, 4, 'Pressão por prazos, tomada de decisões sob estresse', '2026-04-13 17:37:28', '2026-04-13 17:37:28'),
(1629, 3, 4, 'Uso prolongado de computador', '2026-04-13 17:37:28', '2026-04-13 17:37:28'),
(1647, 3, 12, 'Uso prolongado de computador', '2026-04-13 18:03:05', '2026-04-13 18:03:05'),
(1648, 4, 12, 'Pressão para evitar erros na documentação e logística', '2026-04-13 18:03:05', '2026-04-13 18:03:05'),
(1665, 1, 51, 'Manuseio de ferramentas, calor de equipamentos, campo eletromagnético', '2026-04-13 18:27:14', '2026-04-13 18:27:14'),
(1666, 5, 51, 'Quedas, choques, falhas em redes elétricas', '2026-04-13 18:27:14', '2026-04-13 18:27:14'),
(1667, 4, 51, 'Pressão por tempo, carga mental elevada, plantões emergenciais', '2026-04-13 18:27:14', '2026-04-13 18:27:14'),
(1668, 3, 51, 'Postura prolongada, esforço repetitivo, uso prolongado de computadores', '2026-04-13 18:27:14', '2026-04-13 18:27:14'),
(1683, 3, 43, 'Postura prolongada, uso contínuo de computador...sobrecarga visual', '2026-04-13 18:57:48', '2026-04-13 18:57:48'),
(1684, 4, 43, 'Estresse por prazos, sobrecarga mental', '2026-04-13 18:57:48', '2026-04-13 18:57:48'),
(1694, 4, 48, 'Pressão por prazos e metas, decisões estratégicas e interações intensas.', '2026-04-13 19:07:45', '2026-04-13 19:07:45'),
(1695, 3, 48, 'Tempo prolongado sentado e uso contínuo de computador.', '2026-04-13 19:07:45', '2026-04-13 19:07:45'),
(1696, 5, 48, 'Riscos em visitas a campo, obras e trajetos', '2026-04-13 19:07:45', '2026-04-13 19:07:45'),
(1705, 3, 64, 'Postura sentada prolongada, uso de computador', '2026-04-13 19:15:39', '2026-04-13 19:15:39'),
(1706, 4, 64, 'Pressão por prazos e negociações frequentes', '2026-04-13 19:15:39', '2026-04-13 19:15:39'),
(1713, 3, 9, 'Postura sentada prolongada, uso de computador', '2026-04-13 19:21:16', '2026-04-13 19:21:16'),
(1714, 4, 9, 'Pressão por prazos e negociações frequentes', '2026-04-13 19:21:16', '2026-04-13 19:21:16'),
(1719, 3, 62, 'Uso prolongado de computadores, postura estática', '2026-04-13 19:27:38', '2026-04-13 19:27:38'),
(1720, 4, 62, 'Exposição a prazos rígidos e pressão por acuracidade de dados', '2026-04-13 19:27:38', '2026-04-13 19:27:38'),
(1723, 3, 6, 'Uso prolongado de computadores, postura estática', '2026-04-13 19:31:42', '2026-04-13 19:31:42'),
(1724, 4, 6, 'Exposição a prazos rígidos e pressão por acuracidade de dados', '2026-04-13 19:31:42', '2026-04-13 19:31:42'),
(1727, 3, 63, 'Uso prolongado de computadores, postura estática', '2026-04-13 19:33:28', '2026-04-13 19:33:28'),
(1728, 4, 63, 'Exposição a prazos rígidos e pressão por acuracidade de dados', '2026-04-13 19:33:28', '2026-04-13 19:33:28'),
(1733, 3, 34, 'Tempo prolongado em frente ao computador', '2026-04-13 19:42:12', '2026-04-13 19:42:12'),
(1734, 4, 34, 'Pressão psicológica por metas e prazos', '2026-04-13 19:42:12', '2026-04-13 19:42:12'),
(1741, 3, 61, 'Tempo prolongado em frente ao computador', '2026-04-13 19:48:11', '2026-04-13 19:48:11'),
(1742, 4, 61, 'Pressão por prazos e sigilo de dados', '2026-04-13 19:48:11', '2026-04-13 19:48:11'),
(1745, 3, 20, 'Tempo prolongado em frente ao computador', '2026-04-13 19:51:46', '2026-04-13 19:51:46'),
(1746, 4, 20, 'Pressão psicológica por metas e prazos', '2026-04-13 19:51:46', '2026-04-13 19:51:46'),
(1756, 1, 25, 'Ruído, calor, vibração', '2026-04-13 20:08:28', '2026-04-13 20:08:28'),
(1757, 3, 25, 'Atividades repetitivas, postura inadequada', '2026-04-13 20:08:28', '2026-04-13 20:08:28'),
(1758, 5, 25, 'Ferramentas manuais, quedas, choques', '2026-04-13 20:08:28', '2026-04-13 20:08:28'),
(1759, 4, 25, 'Estresse ocupacional, falha de comunicação', '2026-04-13 20:08:28', '2026-04-13 20:08:28'),
(1765, 1, 78, 'Quedas, cortes, ruído, calor, impacto de objetos, trabalho em altura', '2026-04-17 11:51:47', '2026-04-17 11:51:47'),
(1766, 2, 78, 'Cimento, tintas, solventes, graxas, selantes', '2026-04-17 11:51:47', '2026-04-17 11:51:47'),
(1767, 3, 78, 'Esforço repetitivo, levantamento de peso, posturas forçadas', '2026-04-17 11:51:47', '2026-04-17 11:51:47'),
(1768, 4, 78, 'Pressão por produtividade, estresse, sobrecarga', '2026-04-17 11:51:47', '2026-04-17 11:51:47'),
(1769, 5, 78, 'Máquinas, ferramentas e eletricidade', '2026-04-17 11:51:47', '2026-04-17 11:51:47'),
(1790, 5, 42, 'Ferramentas manuais e elétricas', '2026-04-17 12:02:07', '2026-04-17 12:02:07'),
(1791, 1, 42, 'Quedas, cortes, impacto de objetos, calor, poeira, ruído', '2026-04-17 12:02:07', '2026-04-17 12:02:07'),
(1792, 2, 42, 'Cimento, solventes, tintas, óleo desmoldante', '2026-04-17 12:02:07', '2026-04-17 12:02:07'),
(1793, 3, 42, 'Esforço repetitivo, posturas forçadas, movimentação de cargas', '2026-04-17 12:02:07', '2026-04-17 12:02:07'),
(1794, 4, 42, 'Pressão por prazos, sobrecarga em manutenções urgentes.', '2026-04-17 12:02:07', '2026-04-17 12:02:07'),
(1819, 5, 56, 'Uso de veículos', '2026-04-17 12:29:44', '2026-04-17 12:29:44'),
(1820, 4, 56, 'Baixa exposição a riscos psicossociais (tensão no trabalho)', '2026-04-17 12:29:44', '2026-04-17 12:29:44'),
(1821, 3, 56, 'Postural e uso prolongado de computadores', '2026-04-17 12:29:44', '2026-04-17 12:29:44'),
(1896, 3, 17, 'Atividades repetitivas, permanência prolongada em pé, levantamento de peso', '2026-04-17 13:06:54', '2026-04-17 13:06:54'),
(1897, 1, 17, 'Umidade, variações de temperatura', '2026-04-17 13:06:54', '2026-04-17 13:06:54'),
(1898, 4, 17, 'Pressão por metas, estresse, trabalho em turnos', '2026-04-17 13:06:54', '2026-04-17 13:06:54'),
(1899, 5, 17, 'Manuseio de ferramentas e equipamentos de corte e vedação', '2026-04-17 13:06:54', '2026-04-17 13:06:54'),
(1900, 1, 16, 'Ruídos, calor, frio, choque térmico', '2026-04-17 13:09:31', '2026-04-17 13:09:31'),
(1901, 3, 16, 'Atividades repetitivas, levantamento de peso, postura inadequada', '2026-04-17 13:09:31', '2026-04-17 13:09:31'),
(1902, 2, 16, 'Produtos de limpeza, caldas durante o banho', '2026-04-17 13:09:31', '2026-04-17 13:09:31'),
(1903, 5, 16, 'Colisões, cortes, escorregamento, uso de máquinas', '2026-04-17 13:09:31', '2026-04-17 13:09:31'),
(1904, 4, 16, 'Pressão por metas, estresse, trabalho em turnos', '2026-04-17 13:09:31', '2026-04-17 13:09:31'),
(1920, 1, 40, 'Ruído, calor, vibração', '2026-04-17 13:24:11', '2026-04-17 13:24:11'),
(1921, 5, 40, 'Atropelamento, falha de operação, operação em ambientes estreitos, colisões, tombamentos.', '2026-04-17 13:24:11', '2026-04-17 13:24:11'),
(1922, 3, 40, 'Posturas inadequadas, esforço repetitivo', '2026-04-17 13:24:11', '2026-04-17 13:24:11'),
(1923, 4, 40, 'Pressão por produtividade, estresse, turnos alternados', '2026-04-17 13:24:11', '2026-04-17 13:24:11'),
(1924, 2, 40, 'Exposição a combustíveis, gases ou lubrificantes.', '2026-04-17 13:24:11', '2026-04-17 13:24:11'),
(1952, 1, 49, 'Ruído, umidade, frio', '2026-04-17 13:46:01', '2026-04-17 13:46:01'),
(1953, 2, 49, 'Contato com produtos sanitizantes', '2026-04-17 13:46:01', '2026-04-17 13:46:01'),
(1954, 4, 49, 'Pressão por resultados, conflitos de equipe, gestão sob estresse', '2026-04-17 13:46:01', '2026-04-17 13:46:01'),
(1955, 3, 49, 'Postura inadequada, deslocamentos frequentes', '2026-04-17 13:46:01', '2026-04-17 13:46:01'),
(1956, 5, 49, 'Máquinas em movimento', '2026-04-17 13:46:01', '2026-04-17 13:46:01'),
(1957, 1, 2, 'Exposição prolongada ao sol, calor, poeira, ruído', '2026-04-17 13:50:23', '2026-04-17 13:50:23'),
(1958, 2, 2, 'Manuseio de defensivos e fertilizantes', '2026-04-17 13:50:23', '2026-04-17 13:50:23'),
(1959, 3, 2, 'Postura inadequada, esforço físico', '2026-04-17 13:50:23', '2026-04-17 13:50:23'),
(1960, 4, 2, 'Pressão por metas, conflitos interpessoais', '2026-04-17 13:50:23', '2026-04-17 13:50:23'),
(1961, 5, 2, 'Uso de máquinas e ferramentas agrícolas', '2026-04-17 13:50:23', '2026-04-17 13:50:23'),
(1962, 6, 2, 'Contato com pragas e Animais Peçonhentos', '2026-04-17 13:50:23', '2026-04-17 13:50:23'),
(1988, 1, 35, 'Ruído, temperatura, umidade', '2026-04-17 14:14:53', '2026-04-17 14:14:53'),
(1989, 3, 35, 'Postura inadequada, esforço repetitivo', '2026-04-17 14:14:53', '2026-04-17 14:14:53'),
(1990, 5, 35, 'Manuseio de ferramentas, contato com superfícies molhadas', '2026-04-17 14:14:53', '2026-04-17 14:14:53'),
(1991, 4, 35, 'Pressão por prazos e precisão nos registros', '2026-04-17 14:14:53', '2026-04-17 14:14:53'),
(1992, 6, 35, 'Contato com microrganismos (fungos e bactérias comuns)', '2026-04-17 14:14:53', '2026-04-17 14:14:53'),
(2023, 1, 18, 'Exposição ao calor e umidade', '2026-04-17 14:45:11', '2026-04-17 14:45:11'),
(2024, 3, 18, 'Esforço repetitivo, levantamento de peso leves.', '2026-04-17 14:45:11', '2026-04-17 14:45:11'),
(2025, 4, 18, 'Pressão por tempo, turnos alternados', '2026-04-17 14:45:11', '2026-04-17 14:45:11'),
(2026, 2, 18, 'Exposição a produtos químicos (limpeza)', '2026-04-17 14:45:11', '2026-04-17 14:45:11'),
(2027, 6, 18, 'Contato com resíduos orgânicos, sanitários, vírus e bactérias (comuns)', '2026-04-17 14:45:11', '2026-04-17 14:45:11'),
(2038, 1, 84, 'Exposição ao calor e umidade', '2026-04-17 14:54:48', '2026-04-17 14:54:48'),
(2039, 3, 84, 'Esforço repetitivo, levantamento de peso leves.', '2026-04-17 14:54:48', '2026-04-17 14:54:48'),
(2040, 4, 84, 'Pressão por tempo, turnos alternados', '2026-04-17 14:54:48', '2026-04-17 14:54:48'),
(2041, 2, 84, 'Exposição a produtos químicos (limpeza) e agropecuários.', '2026-04-17 14:54:48', '2026-04-17 14:54:48'),
(2042, 6, 84, 'Contato com resíduos orgânicos, sanitários, vírus e bactérias (comuns)', '2026-04-17 14:54:48', '2026-04-17 14:54:48'),
(2056, 2, 29, 'Lubrificantes, produtos de soldagem', '2026-04-17 15:11:42', '2026-04-17 15:11:42'),
(2057, 1, 29, 'Ambiente industrial, ruído', '2026-04-17 15:11:42', '2026-04-17 15:11:42'),
(2058, 4, 29, 'Pressão e estresse por prazos e resolução de problemas', '2026-04-17 15:11:42', '2026-04-17 15:11:42'),
(2059, 3, 29, 'Movimentos repetitivos, posições desconfortáveis', '2026-04-17 15:11:42', '2026-04-17 15:11:42'),
(2060, 5, 29, 'Ferramentas e equipamentos pesados', '2026-04-17 15:11:42', '2026-04-17 15:11:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `roles`
--

CREATE TABLE `roles` (
  `roleId` int(11) NOT NULL,
  `roleName` varchar(100) NOT NULL,
  `roleDescription` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `roles`
--

INSERT INTO `roles` (`roleId`, `roleName`, `roleDescription`) VALUES
(1, 'Admin', 'Administrador com acesso total ao sistema.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `role_permissions`
--

CREATE TABLE `role_permissions` (
  `roleId` int(11) NOT NULL,
  `permissionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `role_permissions`
--

INSERT INTO `role_permissions` (`roleId`, `permissionId`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 11),
(1, 12);

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

--
-- Despejando dados para a tabela `tipo_hierarquia`
--

INSERT INTO `tipo_hierarquia` (`tipoId`, `tipoNome`, `tipoDescricao`, `tipoDataCadastro`, `tipoDataAtualizacao`) VALUES
(1, 'Estratégico', 'define a visão e metas de longo prazo para toda a empresa', '2025-10-31 11:29:53', '2025-10-31 11:42:41'),
(2, 'Tático', 'o tático desdobra essas metas em planos específicos e de médio prazo para cada área', '2025-10-31 11:31:11', '2025-10-31 11:42:57'),
(3, 'Operacional', 'executa as ações diárias e de curto prazo, convertendo os planos táticos em tarefas concretas.', '2025-10-31 11:31:18', '2025-10-31 11:43:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_roles`
--

CREATE TABLE `user_roles` (
  `usuarioId` int(11) NOT NULL COMMENT 'Chave estrangeira de usuarios.usuarioId',
  `roleId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `user_roles`
--

INSERT INTO `user_roles` (`usuarioId`, `roleId`) VALUES
(1, 1),
(2, 1);

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
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`usuarioId`, `nome`, `email`, `senha`, `ativo`, `dataCadastro`) VALUES
(1, 'Leandro Matos', 'leandro.matos@koppla.com.br', '$2y$10$aQ3TOEQoY3FqZYqHnTGFZ.0rvZymYtZ/VUeTOWvInNBorqJeEShxq', 1, '2025-10-07 03:05:19'),
(2, 'Administrador ITACITRUS', 'admin@itacitrus.com.br', '$2y$10$fl3WZG/6VnwP9HasDeAyqeohbsDFJ/xTVwVY0vk59narCbB0L0JWC', 1, '2025-10-07 03:16:02');

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
-- Índices de tabela `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`logId`),
  ADD KEY `idx_userId` (`usuarioId`),
  ADD KEY `idx_tableName_recordId` (`nomeTabela`,`idRegistro`),
  ADD KEY `idx_timestamp` (`dataHora`);

--
-- Índices de tabela `campanhas_pesquisa`
--
ALTER TABLE `campanhas_pesquisa`
  ADD PRIMARY KEY (`campanhaId`);

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
-- Índices de tabela `cargos_supervisores`
--
ALTER TABLE `cargos_supervisores`
  ADD PRIMARY KEY (`cargoId`,`supervisorId`),
  ADD KEY `supervisorId` (`supervisorId`);

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
-- Índices de tabela `empresas_mercado`
--
ALTER TABLE `empresas_mercado`
  ADD PRIMARY KEY (`empresaId`);

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
-- Índices de tabela `historico_inpc`
--
ALTER TABLE `historico_inpc`
  ADD PRIMARY KEY (`ano`);

--
-- Índices de tabela `historico_salario_minimo`
--
ALTER TABLE `historico_salario_minimo`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `nivel_hierarquico`
--
ALTER TABLE `nivel_hierarquico`
  ADD PRIMARY KEY (`nivelId`),
  ADD KEY `fk_nivel_tipo` (`tipoId`);

--
-- Índices de tabela `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permissionId`),
  ADD UNIQUE KEY `permissionName` (`permissionName`);

--
-- Índices de tabela `pesquisa_valores`
--
ALTER TABLE `pesquisa_valores`
  ADD PRIMARY KEY (`valorId`),
  ADD KEY `campanhaId` (`campanhaId`),
  ADD KEY `empresaId` (`empresaId`),
  ADD KEY `cboId` (`cboId`);

--
-- Índices de tabela `reajustes_salariais`
--
ALTER TABLE `reajustes_salariais`
  ADD PRIMARY KEY (`reajusteId`);

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
-- Índices de tabela `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`roleId`),
  ADD UNIQUE KEY `roleName` (`roleName`);

--
-- Índices de tabela `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`roleId`,`permissionId`),
  ADD KEY `permissionId` (`permissionId`);

--
-- Índices de tabela `tipo_hierarquia`
--
ALTER TABLE `tipo_hierarquia`
  ADD PRIMARY KEY (`tipoId`),
  ADD UNIQUE KEY `tipoNome` (`tipoNome`);

--
-- Índices de tabela `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`usuarioId`,`roleId`),
  ADD KEY `roleId` (`roleId`);

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
  MODIFY `areaId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de tabela `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `logId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT de tabela `campanhas_pesquisa`
--
ALTER TABLE `campanhas_pesquisa`
  MODIFY `campanhaId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `caracteristicas`
--
ALTER TABLE `caracteristicas`
  MODIFY `caracteristicaId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `caracteristicas_cargo`
--
ALTER TABLE `caracteristicas_cargo`
  MODIFY `característicaCargoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3648;

--
-- AUTO_INCREMENT de tabela `cargos`
--
ALTER TABLE `cargos`
  MODIFY `cargoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT de tabela `cargos_area`
--
ALTER TABLE `cargos_area`
  MODIFY `cargoAreaId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=910;

--
-- AUTO_INCREMENT de tabela `cargo_sinonimos`
--
ALTER TABLE `cargo_sinonimos`
  MODIFY `cargoSinonimoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2507;

--
-- AUTO_INCREMENT de tabela `cbos`
--
ALTER TABLE `cbos`
  MODIFY `cboId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=620143;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `cursoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de tabela `cursos_cargo`
--
ALTER TABLE `cursos_cargo`
  MODIFY `cursoCargoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1361;

--
-- AUTO_INCREMENT de tabela `empresas_mercado`
--
ALTER TABLE `empresas_mercado`
  MODIFY `empresaId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `escolaridades`
--
ALTER TABLE `escolaridades`
  MODIFY `escolaridadeId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `faixas_salariais`
--
ALTER TABLE `faixas_salariais`
  MODIFY `faixaId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `familia_cbo`
--
ALTER TABLE `familia_cbo`
  MODIFY `familiaCboId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `habilidades`
--
ALTER TABLE `habilidades`
  MODIFY `habilidadeId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT de tabela `habilidades_cargo`
--
ALTER TABLE `habilidades_cargo`
  MODIFY `habilidadeCargoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9958;

--
-- AUTO_INCREMENT de tabela `historico_salario_minimo`
--
ALTER TABLE `historico_salario_minimo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `nivel_hierarquico`
--
ALTER TABLE `nivel_hierarquico`
  MODIFY `nivelId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permissionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `pesquisa_valores`
--
ALTER TABLE `pesquisa_valores`
  MODIFY `valorId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `reajustes_salariais`
--
ALTER TABLE `reajustes_salariais`
  MODIFY `reajusteId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recursos`
--
ALTER TABLE `recursos`
  MODIFY `recursoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `recursos_cargo`
--
ALTER TABLE `recursos_cargo`
  MODIFY `recursoCargoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=233;

--
-- AUTO_INCREMENT de tabela `recursos_grupos`
--
ALTER TABLE `recursos_grupos`
  MODIFY `recursoGrupoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `recursos_grupos_cargo`
--
ALTER TABLE `recursos_grupos_cargo`
  MODIFY `recursoGrupoCargoId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1911;

--
-- AUTO_INCREMENT de tabela `recurso_grupo_recurso`
--
ALTER TABLE `recurso_grupo_recurso`
  MODIFY `recursoGrupoRecursoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT de tabela `riscos`
--
ALTER TABLE `riscos`
  MODIFY `riscoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `riscos_cargo`
--
ALTER TABLE `riscos_cargo`
  MODIFY `riscoCargoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2061;

--
-- AUTO_INCREMENT de tabela `roles`
--
ALTER TABLE `roles`
  MODIFY `roleId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `tipo_hierarquia`
--
ALTER TABLE `tipo_hierarquia`
  MODIFY `tipoId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `usuarioId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Restrições para tabelas `cargos_supervisores`
--
ALTER TABLE `cargos_supervisores`
  ADD CONSTRAINT `cargos_supervisores_ibfk_1` FOREIGN KEY (`cargoId`) REFERENCES `cargos` (`cargoId`) ON DELETE CASCADE,
  ADD CONSTRAINT `cargos_supervisores_ibfk_2` FOREIGN KEY (`supervisorId`) REFERENCES `cargos` (`cargoId`) ON DELETE CASCADE;

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
-- Restrições para tabelas `pesquisa_valores`
--
ALTER TABLE `pesquisa_valores`
  ADD CONSTRAINT `pesquisa_valores_ibfk_1` FOREIGN KEY (`campanhaId`) REFERENCES `campanhas_pesquisa` (`campanhaId`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesquisa_valores_ibfk_2` FOREIGN KEY (`empresaId`) REFERENCES `empresas_mercado` (`empresaId`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesquisa_valores_ibfk_3` FOREIGN KEY (`cboId`) REFERENCES `cbos` (`cboId`) ON DELETE CASCADE;

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

--
-- Restrições para tabelas `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `roles` (`roleId`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permissionId`) REFERENCES `permissions` (`permissionId`) ON DELETE CASCADE;

--
-- Restrições para tabelas `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`usuarioId`) REFERENCES `usuarios` (`usuarioId`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`roleId`) REFERENCES `roles` (`roleId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
