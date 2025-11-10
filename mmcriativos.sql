-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 07/11/2025 às 16:18
-- Versão do servidor: 8.4.3
-- Versão do PHP: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mmcriativos`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `aboutus`
--

CREATE TABLE `aboutus` (
  `id` bigint UNSIGNED NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `aboutus`
--

INSERT INTO `aboutus` (`id`, `photo`, `cover`, `title`, `subtitle`, `description`, `created_at`, `updated_at`) VALUES
(1, 'assets/images/resources/marcusm.jpg', 'storage/layout/aboutus-cover-20251103-041736.jpg', 'Mais do que sites, criamos presença', 'Sobre a MM Criativos', '<p>A MM Criativos une design e desenvolvimento para construir a vitrine digital que sua marca merece.\r\n                Criamos sites que refletem o profissionalismo do seu negócio e aproximam seus clientes de você,\r\n                com identidade, propósito e um clique de distância.</p>\r\n\r\n                <p>Desenvolvemos de forma independente, sem amarras a plataformas ou mensalidades.\r\n                Aqui, o site é realmente seu! Você pode hospedá-lo conosco ou onde preferir, com total liberdade e propriedade.</p>\r\n\r\n                <p>Fugimos de padrões prontos. Cada projeto é único, feito à mão, com estética moderna e foco na personalidade da sua marca.\r\n                Nossa equipe combina técnica e sensibilidade para entregar resultados que superam expectativas e colocam sua empresa em destaque no digital.</p>', '2025-11-03 05:44:01', '2025-11-03 07:17:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `budgets`
--

CREATE TABLE `budgets` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED DEFAULT NULL,
  `plan_id` bigint UNSIGNED DEFAULT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `base_price_snapshot` decimal(10,2) NOT NULL DEFAULT '0.00',
  `client_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BRL',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_percent` decimal(5,2) DEFAULT NULL,
  `subtotal_one_time` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal_monthly` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal_yearly` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_one_time` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_monthly` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_yearly` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `valid_until` date DEFAULT NULL,
  `public_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `budgets`
--

INSERT INTO `budgets` (`id`, `service_id`, `plan_id`, `client_id`, `base_price_snapshot`, `client_name`, `client_email`, `client_phone`, `currency`, `discount_amount`, `total_discount_amount`, `tax_percent`, `subtotal_one_time`, `subtotal_monthly`, `subtotal_yearly`, `total_one_time`, `total_monthly`, `total_yearly`, `status`, `valid_until`, `public_token`, `notes`, `created_at`, `updated_at`) VALUES
(12, 4, 4, 2, 15000.00, 'Marcus', 'marcusmultini@gmail.com', '11958469546', 'BRL', 0.00, 0.00, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'accepted', '2025-12-10', 'YH9qXFQq9SfqrFWjHXWEcWkU9RZAqartOub2QFqJNHEbPLCu4Jm5JtMiBufzZsyJ', NULL, '2025-11-06 10:16:50', '2025-11-06 10:17:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `budget_events`
--

CREATE TABLE `budget_events` (
  `id` bigint UNSIGNED NOT NULL,
  `budget_id` bigint UNSIGNED NOT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` json DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `budget_events`
--

INSERT INTO `budget_events` (`id`, `budget_id`, `event`, `meta`, `user_id`, `user_type`, `created_at`, `updated_at`) VALUES
(60, 12, 'sent', '{\"currency\": \"BRL\", \"rate_to_brl\": 1, \"total_one_time\": 0, \"total_one_time_brl\": 0}', NULL, NULL, '2025-11-06 10:16:56', '2025-11-06 10:16:56'),
(61, 12, 'opened', '{\"currency\": \"BRL\", \"rate_to_brl\": 1, \"total_one_time\": 0, \"total_one_time_brl\": 0}', NULL, NULL, '2025-11-06 10:17:02', '2025-11-06 10:17:02'),
(62, 12, 'accepted', '{\"currency\": \"BRL\", \"rate_to_brl\": 1, \"interest_rate\": 0, \"total_one_time\": 0, \"installment_value\": 15000, \"total_one_time_brl\": 0, \"total_with_interest\": 15000, \"selected_installments\": 1, \"total_with_interest_brl\": 15000}', NULL, NULL, '2025-11-06 10:17:08', '2025-11-06 10:17:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `budget_items`
--

CREATE TABLE `budget_items` (
  `id` bigint UNSIGNED NOT NULL,
  `budget_id` bigint UNSIGNED NOT NULL,
  `line_type` enum('base','extra') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'extra',
  `ref_id` bigint UNSIGNED DEFAULT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `qty` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_type` enum('fixed','percent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `billing_period` enum('one_time','monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'one_time',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sort` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `budget_payments`
--

CREATE TABLE `budget_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `budget_id` bigint UNSIGNED NOT NULL,
  `installments` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `interest_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `total_with_interest` decimal(10,2) NOT NULL DEFAULT '0.00',
  `installment_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_selected` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `budget_payments`
--

INSERT INTO `budget_payments` (`id`, `budget_id`, `installments`, `interest_rate`, `total_with_interest`, `installment_value`, `is_selected`, `created_at`, `updated_at`) VALUES
(9, 12, 1, 0.00, 15000.00, 15000.00, 1, '2025-11-06 10:17:08', '2025-11-06 10:17:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `business_hours`
--

CREATE TABLE `business_hours` (
  `id` bigint UNSIGNED NOT NULL,
  `days` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Seg a Sex',
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `business_hours`
--

INSERT INTO `business_hours` (`id`, `days`, `open_time`, `close_time`, `is_closed`, `created_at`, `updated_at`) VALUES
(1, 'Seg a Sex', '08:00:00', '19:00:00', 0, NULL, NULL),
(2, 'Sab e Dom', NULL, NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `classes`
--

CREATE TABLE `classes` (
  `id` bigint UNSIGNED NOT NULL,
  `hierarquia` int NOT NULL DEFAULT '1',
  `classe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `skills` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `classes`
--

INSERT INTO `classes` (`id`, `hierarquia`, `classe`, `description`, `skills`, `created_at`, `updated_at`) VALUES
(1, 1, 'Designer de Interfaces', 'Especialista em transformar conceitos visuais em interfaces funcionais e esteticamente equilibradas. Une design e código para criar experiências intuitivas e atraentes.', '[\"HTML5\", \"CSS3\", \"Tailwind\", \"Figma\", \"Design Responsivo\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(2, 1, 'Animador de Interfaces', 'Profissional focado em dar vida às interações visuais. Trabalha com animações, transições e microinterações que tornam o digital mais envolvente e dinâmico.', '[\"JavaScript\", \"Vue.js\", \"React\", \"GSAP\", \"Motion Design\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(3, 1, 'Arquiteto de Experiência (UX)', 'Entende pessoas, jornadas e comportamentos. Cria fluxos claros e experiências digitais simples, eficientes e humanas.', '[\"UX Research\", \"Wireframes\", \"Usabilidade\", \"Prototipagem\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(4, 1, 'Desenvolvedor Back-End', 'Responsável pela estrutura lógica e pela inteligência do sistema. Cria bases sólidas, seguras e escaláveis que sustentam o produto digital.', '[\"PHP\", \"Laravel\", \"SQL\", \"APIs\", \"Segurança\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(5, 1, 'Engenheiro de Inteligência Digital (IA)', 'Conecta tecnologia e automação. Cria soluções baseadas em IA e fluxos inteligentes que potencializam o trabalho humano.', '[\"OpenAI API\", \"n8n\", \"Automação\", \"Prompt Engineering\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(6, 1, 'Especialista em SEO e Performance', 'Cuida para que o projeto seja rápido, acessível e visível. Trabalha a otimização técnica e estratégica que melhora o alcance e o desempenho online.', '[\"SEO Técnico\", \"Core Web Vitals\", \"Otimização\", \"Google Analytics\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(7, 1, 'Designer de Identidade Visual', 'Profissional criativo que traduz valores e propósito em imagens, tipografias e cores. Dá forma à essência das marcas.', '[\"Branding\", \"Direção de Arte\", \"Paleta de Cores\", \"Tipografia\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(8, 1, 'Automatizador Digital', 'Focado em integrações e automações entre ferramentas e sistemas. Otimiza processos e elimina tarefas repetitivas.', '[\"n8n\", \"Zapier\", \"APIs\", \"Webhooks\", \"Integrações SaaS\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(9, 2, 'Construtor Visual', 'Une o olhar criativo do designer à precisão técnica do desenvolvedor. Transforma ideias em experiências completas, belas e funcionais.', '[\"Front-end\", \"UX/UI\", \"Figma\", \"JavaScript\", \"CSS\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(10, 2, 'Engenheiro Criativo', 'Junta lógica e design em equilíbrio. Constrói sistemas inteligentes com foco em usabilidade, performance e inovação.', '[\"Back-end\", \"Front-end\", \"Laravel\", \"Vue.js\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(11, 2, 'Estrategista de Presença Digital', 'Integra branding, conteúdo e SEO para criar presença digital consistente e memorável.', '[\"Branding\", \"SEO\", \"UX Writing\", \"Marketing Digital\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(12, 2, 'Orquestrador de Sistemas', 'Conecta automação, dados e inteligência artificial para criar ecossistemas digitais autônomos e eficientes.', '[\"n8n\", \"OpenAI\", \"Integrações\", \"API Design\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(13, 2, 'Diretor de Experiência Digital', 'Supervisiona a jornada completa do usuário, garantindo coerência entre design, tecnologia e propósito.', '[\"UX\", \"UI\", \"Front-end\", \"Usabilidade\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(14, 2, 'Desenvolvedor Full Spectrum', 'Atua em todas as camadas do desenvolvimento. Entrega soluções completas, do design à lógica do servidor.', '[\"Full Stack\", \"Laravel\", \"Vue.js\", \"APIs\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(15, 2, 'Cientista de Interfaces', 'Une dados e comportamento. Cria interfaces inteligentes que aprendem e evoluem conforme o uso.', '[\"UX\", \"IA\", \"Analytics\", \"Prototipagem Avançada\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(16, 2, 'Arquiteto de Performance Digital', 'Otimiza todo o ecossistema técnico — código, automação e SEO — para máxima eficiência e resultados.', '[\"SEO\", \"Performance\", \"Automação\", \"Backend\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(17, 3, 'Arquiteto Digital', 'Profissional que une design, tecnologia e estratégia. Cria soluções completas, do conceito à execução, com visão técnica e criativa.', '[\"Front-end\", \"Back-end\", \"UX/UI\", \"Automação\", \"SEO\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(18, 3, 'Diretor Criativo Digital', 'Lidera processos criativos e técnicos. Atua na direção de arte, experiência e tecnologia, entregando consistência.', '[\"UX/UI\", \"Branding\", \"Front-end\", \"Automação\"]', '2025-11-03 04:04:12', '2025-11-03 05:28:16'),
(19, 3, 'Engenheiro de Produto Digital', 'Constrói plataformas, sistemas e experiências integradas com base em dados, IA e automação.', '[\"Full Stack\", \"IA\", \"Automação\", \"Arquitetura de Sistemas\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(20, 3, 'Estrategista de Inovação', 'Pensa o futuro do digital. Integra IA, automação e design para criar soluções visionárias e sustentáveis.', '[\"Automação\", \"IA\", \"UX\", \"Estratégia\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12'),
(21, 3, 'Criador Multidisciplinar', 'Profissional completo, que domina arte, código e estratégia, transformando ideias em ecossistemas digitais inteligentes.', '[\"Design\", \"Desenvolvimento\", \"Automação\", \"Branding\", \"IA\"]', '2025-11-03 04:04:12', '2025-11-03 04:04:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `class_user`
--

CREATE TABLE `class_user` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `class_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `class_user`
--

INSERT INTO `class_user` (`id`, `user_id`, `class_id`, `created_at`, `updated_at`) VALUES
(1, 1, 17, '2025-11-03 04:59:04', '2025-11-03 04:59:04'),
(2, 1, 18, '2025-11-03 04:59:04', '2025-11-03 04:59:04'),
(3, 1, 19, '2025-11-03 04:59:04', '2025-11-03 04:59:04'),
(4, 1, 20, '2025-11-03 04:59:04', '2025-11-03 04:59:04');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clients`
--

CREATE TABLE `clients` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `clients`
--

INSERT INTO `clients` (`id`, `name`, `slug`, `logo`, `website`, `sector`, `description`, `created_at`, `updated_at`) VALUES
(2, 'MM Criativos', 'mm-criativos', 'clients/logos/client-mm-criativos-logo-20251031-080657.png', 'https://www.mmcriativos.com.br', 'Tecnologia', 'Empresa especializada na criação de sites e soluções online.', '2025-10-21 05:52:29', '2025-10-31 11:06:57'),
(3, 'Popfy', 'popfy', 'clients/logos/client-popfy-logo-20251031-080739.png', 'https://popfy.io', 'Entretenimento', 'Portal com rede social, multitenant, editor e criador de conteúdo. O SaaS mais completo do Brasil, desenvolvido por MM Criativos.', '2025-10-21 06:05:55', '2025-10-31 11:07:39'),
(4, 'Talk Nerd', 'talk-nerd', 'clients/logos/client-talk-nerd-logo-20251031-080810.png', NULL, 'Cultura Pop', NULL, '2025-10-29 02:02:26', '2025-10-31 11:08:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clients_info`
--

CREATE TABLE `clients_info` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `cep` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `neighborhood` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Brasil',
  `email_commercial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `client_social_media`
--

CREATE TABLE `client_social_media` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `social_media_id` bigint UNSIGNED NOT NULL,
  `user` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `client_testimonials`
--

CREATE TABLE `client_testimonials` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `contact_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `testimonial` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint UNSIGNED DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_visible` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `contacts`
--

INSERT INTO `contacts` (`id`, `client_id`, `name`, `role`, `photo`, `email`, `phone`, `linkedin`, `website`, `is_primary`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 2, 'Marcus Vinicius Multini', 'CEO', 'storage/clients/contacts/o0gy7i0xkrwcZTSyEPgDhyc6PUFWvrb6yE8L3cX1.png', 'marcusmultini@gmail.com', '11958469546', NULL, 'https://www.mmcriativos.com.br', 1, 1, '2025-10-21 05:56:32', '2025-10-21 05:56:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `footer` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `email_templates`
--

INSERT INTO `email_templates` (`id`, `key`, `name`, `subject`, `body`, `footer`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'budget_sent', 'Envio de Orçamento', 'Seu orçamento está pronto! 🚀', '<p>Olá <strong>{{client_name}}</strong>,</p>\r\n\r\n<p>Esperamos que esteja bem!<br>\r\nSeu orçamento nº <strong>{{budget_id}}</strong> foi preparado com base nas necessidades da sua empresa.</p>\r\n\r\n<p>Você pode visualizar todos os detalhes, valores e condições clicando no botão abaixo:</p>\r\n\r\n<p style=\"text-align:center;\">\r\n    <a href=\"{{public_link}}\" style=\"background-color:#ff8800;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;\">\r\n        Ver Orçamento\r\n    </a>\r\n</p>\r\n\r\n<p>Qualquer dúvida, estamos à disposição.<br>\r\nEquipe <strong>MM Criativos</strong></p>', '<p style=\"font-size:13px;color:#777;\">E-mail automático — não responda diretamente.</p>', 1, '2025-11-04 16:08:21', '2025-11-06 08:07:02'),
(2, 'budget_opened', 'Orçamento Visualizado', 'Seu orçamento foi visualizado 👀', '<p>Olá equipe <strong>MM Criativos</strong>,</p>\r\n\r\n<p>O cliente <strong>{{client_name}}</strong> acabou de abrir o orçamento nº <strong>{{budget_id}}</strong>.</p>\r\n\r\n<p>Isso significa que o cliente visualizou a proposta enviada e pode entrar em contato em breve.</p>\r\n\r\n<p><a href=\"{{admin_link}}\">Acessar orçamento no painel</a></p>', NULL, 1, '2025-11-04 16:08:21', '2025-11-04 16:08:21'),
(3, 'budget_accepted', 'Confirmação de Aceite', 'Orçamento aprovado 🎉', '<p>Olá <strong>{{client_name}}</strong>,</p>\r\n\r\n<p>Perfeito! Seu orçamento nº <strong>{{budget_id}}</strong> foi <strong>aprovado</strong> em <strong>{{accepted_at}}</strong>.</p>\r\n\r\n<p>Agradecemos pela confiança em nossa equipe!<br>\r\nEm breve, entraremos em contato para alinhar os próximos passos do projeto.</p>\r\n\r\n<p>Você pode consultar este orçamento a qualquer momento em:</p>\r\n<p><a href=\"{{public_link}}\">{{public_link}}</a></p>\r\n\r\n<p>Atenciosamente,<br>\r\nEquipe <strong>MM Criativos</strong></p>', '<p style=\"font-size:13px;color:#777;\">Estamos animados para começar!</p>', 1, '2025-11-04 16:08:21', '2025-11-04 16:08:21'),
(4, 'budget_declined', 'Orçamento Recusado / Expirado', 'Seu orçamento expirou ou foi recusado 😔', '<p>Olá <strong>{{client_name}}</strong>,</p>\r\n\r\n<p>Percebemos que o orçamento nº <strong>{{budget_id}}</strong> foi <strong>recusado</strong> ou expirou em <strong>{{valid_until}}</strong>.</p>\r\n\r\n<p>Caso queira revisar valores, condições ou ajustar algo na proposta, é só responder este e-mail ou entrar em contato conosco.</p>\r\n\r\n<p>Estamos à disposição para encontrar a melhor solução!</p>\r\n\r\n<p>Atenciosamente,<br>\r\nEquipe <strong>MM Criativos</strong></p>', '<p style=\"font-size:13px;color:#777;\">Esperamos te ver em um próximo projeto 💡</p>', 1, '2025-11-04 16:08:21', '2025-11-06 08:42:43');

-- --------------------------------------------------------

--
-- Estrutura para tabela `exchange_rates`
--

CREATE TABLE `exchange_rates` (
  `id` bigint UNSIGNED NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate_to_brl` decimal(12,6) NOT NULL,
  `fetched_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `extras`
--

CREATE TABLE `extras` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_type` enum('fixed','percent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `billing_period` enum('one_time','monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'one_time',
  `default_discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `extras`
--

INSERT INTO `extras` (`id`, `name`, `description`, `price`, `price_type`, `billing_period`, `default_discount`, `category`, `is_active`, `sort`, `created_at`, `updated_at`) VALUES
(1, 'Hospedagem anual', 'Servidor dedicado ou cloud para manter o site ou sistema online.', 600.00, 'fixed', 'yearly', 0.00, 'Infraestrutura', 1, 1, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(2, 'Deploy e configuração', 'Publicação e configuração do projeto em ambiente de produção.', 300.00, 'fixed', 'one_time', 0.00, 'Infraestrutura', 1, 2, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(3, 'Backup automatizado', 'Configuração de backup diário e armazenamento em nuvem.', 300.00, 'fixed', 'yearly', 0.00, 'Infraestrutura', 1, 3, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(4, 'E-mails corporativos', 'Criação e hospedagem de contas @suaempresa.com.br com configuração de DNS e suporte técnico.', 250.00, 'fixed', 'yearly', 0.00, 'Infraestrutura', 1, 4, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(5, 'Certificado SSL', 'Instalação e configuração de certificado SSL para navegação segura (https).', 200.00, 'fixed', 'yearly', 0.00, 'Infraestrutura', 1, 5, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(6, 'Registro de domínio', 'Registro e configuração de domínio personalizado (ex: www.suaempresa.com.br).', 120.00, 'fixed', 'yearly', 0.00, 'Infraestrutura', 1, 6, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(7, 'Animações e microinterações', 'Aplicação de efeitos GSAP, Lottie e transições suaves para uma experiência premium.', 600.00, 'fixed', 'one_time', 0.00, 'Design & UX', 1, 7, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(8, 'Versões A/B Test', 'Criação de variações de layout e textos para otimizar conversão de visitantes.', 400.00, 'fixed', 'one_time', 0.00, 'Design & UX', 1, 8, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(9, 'Design responsivo premium', 'Aprimoramento visual e adaptação minuciosa para tablets e smartphones.', 500.00, 'fixed', 'one_time', 0.00, 'Design & UX', 1, 9, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(10, 'SEO Avançado', 'Configuração de sitemap, schema, metatags e otimização técnica.', 500.00, 'fixed', 'one_time', 100.00, 'Conteúdo & SEO', 1, 10, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(11, 'Copywriting estratégico', 'Criação de textos otimizados para conversão e SEO.', 400.00, 'fixed', 'one_time', 0.00, 'Conteúdo & SEO', 1, 11, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(12, 'Tradução multilíngue', 'Adaptação completa do site ou sistema para outro idioma.', 600.00, 'fixed', 'one_time', 0.00, 'Conteúdo & SEO', 1, 12, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(13, 'Integração com API externa', 'Integração com sistemas terceiros como RD Station, Hubspot ou ERPs.', 800.00, 'fixed', 'one_time', 0.00, 'Integrações & Automação', 1, 13, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(14, 'Automação via n8n ou Zapier', 'Criação de fluxos automatizados de e-mails, leads e tarefas integradas.', 700.00, 'fixed', 'one_time', 0.00, 'Integrações & Automação', 1, 14, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(15, 'Login social (Google/LinkedIn)', 'Implementação de autenticação por redes sociais.', 400.00, 'fixed', 'one_time', 0.00, 'Integrações & Automação', 1, 15, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(16, 'Dashboard analítico', 'Painel com gráficos interativos e KPIs personalizados.', 1200.00, 'fixed', 'one_time', 0.00, 'Relatórios & BI', 1, 16, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(17, 'Integração com Google Data Studio ou Power BI', 'Conexão de dados e relatórios dinâmicos com plataformas externas.', 800.00, 'fixed', 'one_time', 0.00, 'Relatórios & BI', 1, 17, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(18, 'Integração com gateway de pagamento', 'Configuração de pagamentos automáticos e planos de assinatura.', 1500.00, 'fixed', 'one_time', 0.00, 'Comercial & SaaS', 1, 18, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(19, 'Domínio customizado (CNAME)', 'Configuração de domínio próprio por cliente (ex: app.empresa.com).', 500.00, 'fixed', 'yearly', 0.00, 'Comercial & SaaS', 1, 19, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(20, 'White Label completo', 'Personalização de marca, logotipo e domínio para cada cliente.', 2500.00, 'fixed', 'one_time', 0.00, 'Comercial & SaaS', 1, 20, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(21, 'Suporte mensal', 'Manutenção, correções e pequenas melhorias contínuas.', 300.00, 'fixed', 'monthly', 0.00, 'Suporte', 1, 21, '2025-11-04 15:51:45', '2025-11-04 15:51:45'),
(22, 'Treinamento do cliente', 'Sessão de treinamento para uso do painel ou CMS.', 400.00, 'fixed', 'one_time', 0.00, 'Suporte', 1, 22, '2025-11-04 15:51:45', '2025-11-04 15:51:45');

-- --------------------------------------------------------

--
-- Estrutura para tabela `extra_service`
--

CREATE TABLE `extra_service` (
  `id` bigint UNSIGNED NOT NULL,
  `extra_id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `custom_price` decimal(10,2) DEFAULT NULL,
  `custom_discount` decimal(10,2) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `extra_service`
--

INSERT INTO `extra_service` (`id`, `extra_id`, `service_id`, `custom_price`, `custom_discount`, `is_default`, `sort`, `created_at`, `updated_at`) VALUES
(58, 1, 1, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(59, 2, 1, NULL, 100.00, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(60, 5, 1, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(61, 6, 1, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(62, 10, 1, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(63, 11, 1, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(64, 7, 1, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(65, 9, 1, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(66, 4, 1, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(67, 1, 2, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(68, 2, 2, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(69, 10, 2, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(70, 11, 2, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(71, 7, 2, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(72, 3, 2, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(73, 5, 2, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(74, 1, 3, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(75, 3, 3, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(76, 2, 3, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(77, 10, 3, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(78, 11, 3, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(79, 12, 3, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(80, 7, 3, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(81, 9, 3, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(82, 1, 4, 800.00, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(83, 3, 4, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(84, 2, 4, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(85, 13, 4, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(86, 14, 4, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(87, 15, 4, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(88, 16, 4, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(89, 17, 4, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(90, 21, 4, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(91, 22, 4, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(92, 1, 5, 1000.00, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(93, 3, 5, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(94, 2, 5, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(95, 13, 5, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(96, 14, 5, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(97, 16, 5, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(98, 17, 5, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(99, 18, 5, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(100, 21, 5, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(101, 22, 5, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(102, 1, 6, 1200.00, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(103, 3, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(104, 2, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(105, 13, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(106, 14, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(107, 15, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(108, 16, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(109, 17, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(110, 18, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(111, 19, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(112, 20, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(113, 21, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35'),
(114, 22, 6, NULL, NULL, 0, 1, '2025-11-04 16:02:35', '2025-11-04 16:02:35');

-- --------------------------------------------------------

--
-- Estrutura para tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `global_pages`
--

CREATE TABLE `global_pages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `global_pages`
--

INSERT INTO `global_pages` (`id`, `name`, `slug`, `service_id`, `description`, `meta`, `created_at`, `updated_at`) VALUES
(1, 'Home', 'home', 1, 'Página inicial. Apresenta identidade visual, proposta de valor e chamada principal.', '{\"layout\": \"hero+proof+cta\", \"primary_layer\": \"identidade\"}', NULL, NULL),
(2, 'Sobre', 'sobre', 1, 'Página institucional que narra a história, missão, visão e valores da marca.', '{\"layout\": \"timeline+statement\", \"primary_layer\": \"proposito\"}', NULL, NULL),
(3, 'Equipe', 'equipe', 3, 'Apresenta o time, suas funções e cultura de trabalho.', '{\"layout\": \"cards+bio\", \"primary_layer\": \"identidade\"}', NULL, NULL),
(4, 'Carreiras', 'carreiras', 5, 'Divulga oportunidades e apresenta a cultura da empresa para novos talentos.', '{\"layout\": \"list+cta\", \"primary_layer\": \"identidade\"}', NULL, NULL),
(5, 'Missão & Valores', 'missao-valores', 3, 'Expande o propósito e valores centrais da marca, com storytelling visual.', '{\"layout\": \"statement+visual\", \"primary_layer\": \"proposito\"}', NULL, NULL),
(6, 'Manifesto Visual', 'manifesto-visual', 2, 'Bloco criativo que une design, propósito e narrativa da marca.', '{\"layout\": \"video+text\", \"primary_layer\": \"proposito\"}', NULL, NULL),
(7, 'Serviços', 'servicos', 1, 'Apresenta o que a marca oferece e como entrega valor ao cliente.', '{\"layout\": \"grid+feature\", \"primary_layer\": \"prova_de_valor\"}', NULL, NULL),
(8, 'Detalhe do Serviço', 'servico-detalhe', 3, 'Página individual detalhando cada serviço com foco em benefícios e diferenciais.', '{\"layout\": \"hero+details\", \"primary_layer\": \"prova_de_valor\"}', NULL, NULL),
(9, 'Planos e Preços', 'planos', 6, 'Tabela comparativa de planos e preços, com destaque para o plano recomendado.', '{\"layout\": \"pricing+cta\", \"primary_layer\": \"prova_de_valor\"}', NULL, NULL),
(10, 'Funcionalidades', 'funcionalidades', 6, 'Demonstra as principais funcionalidades de um sistema ou SaaS.', '{\"layout\": \"icons+list\", \"primary_layer\": \"prova_de_valor\"}', NULL, NULL),
(11, 'Integrações', 'integracoes', 6, 'Apresenta integrações disponíveis com outras plataformas e APIs.', '{\"layout\": \"logos+text\", \"primary_layer\": \"prova_de_valor\"}', NULL, NULL),
(12, 'Recursos', 'recursos', 4, 'Mostra o ecossistema de recursos oferecidos pela solução.', '{\"layout\": \"grid+details\", \"primary_layer\": \"prova_de_valor\"}', NULL, NULL),
(13, 'Cases', 'cases', 1, 'Exibe projetos concluídos, destacando resultados e impacto gerado.', '{\"layout\": \"cards+testimonial\", \"primary_layer\": \"validacao\"}', NULL, NULL),
(14, 'Depoimentos', 'depoimentos', 1, 'Reúne falas e citações de clientes e parceiros.', '{\"layout\": \"slider\", \"primary_layer\": \"validacao\"}', NULL, NULL),
(15, 'Clientes e Parceiros', 'clientes-parceiros', 4, 'Lista de marcas e empresas que confiam na solução.', '{\"layout\": \"logos\", \"primary_layer\": \"validacao\"}', NULL, NULL),
(16, 'Blog', 'blog', 4, 'Publicações e conteúdos de autoridade que reforçam a marca.', '{\"layout\": \"grid+single\", \"primary_layer\": \"validacao\"}', NULL, NULL),
(17, 'Prêmios e Reconhecimentos', 'premios', 5, 'Exibe selos, prêmios e certificações conquistadas.', '{\"layout\": \"gallery+text\", \"primary_layer\": \"validacao\"}', NULL, NULL),
(18, 'Contato', 'contato', 1, 'Página de contato com formulário e informações principais.', '{\"layout\": \"form+map\", \"primary_layer\": \"conversao\"}', NULL, NULL),
(19, 'Orçamento', 'orcamento', 1, 'Formulário detalhado para solicitação de orçamento personalizado.', '{\"layout\": \"form+cta\", \"primary_layer\": \"conversao\"}', NULL, NULL),
(20, 'Download / Material Rico', 'download', 2, 'Landing de material gratuito para captação de leads.', '{\"layout\": \"cta+form\", \"primary_layer\": \"conversao\"}', NULL, NULL),
(21, 'Login', 'login', 6, 'Página de autenticação de usuários do sistema ou SaaS.', '{\"layout\": \"auth+form\", \"primary_layer\": \"conversao\"}', NULL, NULL),
(22, 'FAQ e Suporte', 'faq', 6, 'Central de ajuda e dúvidas frequentes.', '{\"layout\": \"accordion+search\", \"primary_layer\": \"conversao\"}', NULL, NULL),
(23, 'Política de Privacidade', 'politica-privacidade', 1, 'Página institucional obrigatória de compliance e LGPD.', '{\"layout\": \"text\", \"primary_layer\": \"conversao\"}', NULL, NULL),
(24, 'Termos de Uso', 'termos-uso', 1, 'Termos legais para uso do site, app ou plataforma.', '{\"layout\": \"text\", \"primary_layer\": \"conversao\"}', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"9a850191-5f31-44e5-9d44-f97d2ba2616c\",\"displayName\":\"App\\\\Listeners\\\\SendProjectPerceptionBriefingMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Events\\\\CallQueuedListener\",\"command\":\"O:36:\\\"Illuminate\\\\Events\\\\CallQueuedListener\\\":22:{s:5:\\\"class\\\";s:47:\\\"App\\\\Listeners\\\\SendProjectPerceptionBriefingMail\\\";s:6:\\\"method\\\";s:6:\\\"handle\\\";s:4:\\\"data\\\";a:1:{i:0;O:35:\\\"App\\\\Events\\\\ProjectCreatedFromBudget\\\":2:{s:7:\\\"project\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Project\\\";s:2:\\\"id\\\";i:12;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"budget\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:17:\\\"App\\\\Models\\\\Budget\\\";s:2:\\\"id\\\";i:11;s:9:\\\"relations\\\";a:4:{i:0;s:5:\\\"items\\\";i:1;s:7:\\\"project\\\";i:2;s:7:\\\"service\\\";i:3;s:6:\\\"client\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}}s:5:\\\"tries\\\";N;s:13:\\\"maxExceptions\\\";N;s:7:\\\"backoff\\\";N;s:10:\\\"retryUntil\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"failOnTimeout\\\";b:0;s:17:\\\"shouldBeEncrypted\\\";b:0;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"},\"createdAt\":1762412968,\"delay\":10}', 0, NULL, 1762412978, 1762412968),
(2, 'default', '{\"uuid\":\"265b4e51-dfaa-460d-a704-0f744de0b7ec\",\"displayName\":\"App\\\\Listeners\\\\SendProjectPerceptionBriefingMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Events\\\\CallQueuedListener\",\"command\":\"O:36:\\\"Illuminate\\\\Events\\\\CallQueuedListener\\\":22:{s:5:\\\"class\\\";s:47:\\\"App\\\\Listeners\\\\SendProjectPerceptionBriefingMail\\\";s:6:\\\"method\\\";s:6:\\\"handle\\\";s:4:\\\"data\\\";a:1:{i:0;O:35:\\\"App\\\\Events\\\\ProjectCreatedFromBudget\\\":2:{s:7:\\\"project\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Project\\\";s:2:\\\"id\\\";i:12;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"budget\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:17:\\\"App\\\\Models\\\\Budget\\\";s:2:\\\"id\\\";i:11;s:9:\\\"relations\\\";a:4:{i:0;s:5:\\\"items\\\";i:1;s:7:\\\"project\\\";i:2;s:7:\\\"service\\\";i:3;s:6:\\\"client\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}}s:5:\\\"tries\\\";N;s:13:\\\"maxExceptions\\\";N;s:7:\\\"backoff\\\";N;s:10:\\\"retryUntil\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"failOnTimeout\\\";b:0;s:17:\\\"shouldBeEncrypted\\\";b:0;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"},\"createdAt\":1762412968,\"delay\":10}', 0, NULL, 1762412978, 1762412968),
(3, 'default', '{\"uuid\":\"1624c598-b5e0-47a2-940f-39fe0aa65c73\",\"displayName\":\"App\\\\Listeners\\\\SendProjectPerceptionBriefingMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Events\\\\CallQueuedListener\",\"command\":\"O:36:\\\"Illuminate\\\\Events\\\\CallQueuedListener\\\":22:{s:5:\\\"class\\\";s:47:\\\"App\\\\Listeners\\\\SendProjectPerceptionBriefingMail\\\";s:6:\\\"method\\\";s:6:\\\"handle\\\";s:4:\\\"data\\\";a:1:{i:0;O:35:\\\"App\\\\Events\\\\ProjectCreatedFromBudget\\\":2:{s:7:\\\"project\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Project\\\";s:2:\\\"id\\\";i:12;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"budget\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:17:\\\"App\\\\Models\\\\Budget\\\";s:2:\\\"id\\\";i:11;s:9:\\\"relations\\\";a:4:{i:0;s:5:\\\"items\\\";i:1;s:7:\\\"project\\\";i:2;s:7:\\\"service\\\";i:3;s:6:\\\"client\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}}s:5:\\\"tries\\\";N;s:13:\\\"maxExceptions\\\";N;s:7:\\\"backoff\\\";N;s:10:\\\"retryUntil\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"failOnTimeout\\\";b:0;s:17:\\\"shouldBeEncrypted\\\";b:0;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"},\"createdAt\":1762412970,\"delay\":10}', 0, NULL, 1762412980, 1762412970),
(4, 'default', '{\"uuid\":\"61e5f015-2c6e-42a2-ba15-60d04b63e514\",\"displayName\":\"App\\\\Listeners\\\\SendProjectPerceptionBriefingMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Events\\\\CallQueuedListener\",\"command\":\"O:36:\\\"Illuminate\\\\Events\\\\CallQueuedListener\\\":22:{s:5:\\\"class\\\";s:47:\\\"App\\\\Listeners\\\\SendProjectPerceptionBriefingMail\\\";s:6:\\\"method\\\";s:6:\\\"handle\\\";s:4:\\\"data\\\";a:1:{i:0;O:35:\\\"App\\\\Events\\\\ProjectCreatedFromBudget\\\":2:{s:7:\\\"project\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Project\\\";s:2:\\\"id\\\";i:12;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"budget\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:17:\\\"App\\\\Models\\\\Budget\\\";s:2:\\\"id\\\";i:11;s:9:\\\"relations\\\";a:4:{i:0;s:5:\\\"items\\\";i:1;s:7:\\\"project\\\";i:2;s:7:\\\"service\\\";i:3;s:6:\\\"client\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}}s:5:\\\"tries\\\";N;s:13:\\\"maxExceptions\\\";N;s:7:\\\"backoff\\\";N;s:10:\\\"retryUntil\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"failOnTimeout\\\";b:0;s:17:\\\"shouldBeEncrypted\\\";b:0;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"},\"createdAt\":1762412970,\"delay\":10}', 0, NULL, 1762412980, 1762412970);

-- --------------------------------------------------------

--
-- Estrutura para tabela `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `lines`
--

CREATE TABLE `lines` (
  `id` bigint UNSIGNED NOT NULL,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `lines`
--

INSERT INTO `lines` (`id`, `text`, `created_at`, `updated_at`) VALUES
(1, 'SUA MARCA COM PRESENÇA DIGITAL', '2025-10-31 13:24:33', '2025-10-31 13:24:33'),
(2, 'FEITO COM SUA IDENTIDADE', '2025-10-31 13:24:33', '2025-10-31 13:24:33'),
(3, 'ESTRUTURA LIMPA E PROFISSIONAL', '2025-10-31 13:24:33', '2025-10-31 13:24:33'),
(4, 'MAIS DO QUE IDENTIDADE, FUNCIONALIDADE', '2025-10-31 13:24:33', '2025-10-31 13:24:33'),
(5, 'A PORTA DIGITAL DO SEU NEGÓCIO', '2025-10-31 13:24:33', '2025-10-31 13:24:33'),
(6, 'DESIGN QUE RESPEITA SUA MARCA', '2025-10-31 13:24:33', '2025-10-31 13:24:33'),
(7, 'SEU SITE SERÁ SEMPRE SEU', '2025-10-31 13:24:33', '2025-10-31 13:24:33'),
(8, 'TECNOLOGIA QUE TRABALHA POR VOCÊ', '2025-10-31 13:24:33', '2025-10-31 13:24:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_10_19_063917_create_services_table', 1),
(5, '2025_10_19_064127_create_skills_table', 1),
(6, '2025_10_19_065435_create_skill_competencies_table', 1),
(7, '2025_10_19_072308_create_clients_table', 1),
(8, '2025_10_19_072408_create_contacts_table', 1),
(9, '2025_10_19_072528_create_social_medias_table', 1),
(10, '2025_10_19_073144_create_client_social_media_table', 1),
(11, '2025_10_19_074013_create_projects_table', 1),
(12, '2025_10_19_075237_create_project_challenges_table', 1),
(13, '2025_10_19_075243_create_project_solutions_table', 1),
(14, '2025_10_19_213512_create_processes_table', 1),
(15, '2025_10_19_213909_create_project_process_table', 1),
(16, '2025_10_19_214244_create_project_images_table', 1),
(17, '2025_10_19_221643_create_project_skill_competency_table', 1),
(18, '2025_10_20_020924_create_service_infos_table', 1),
(19, '2025_10_20_032800_create_service_benefits_table', 1),
(20, '2025_10_20_034616_create_service_features_table', 1),
(21, '2025_10_20_040757_create_service_processes_table', 1),
(22, '2025_10_20_045021_create_settings_table', 1),
(23, '2025_10_20_045211_create_service_ctas_table', 1),
(24, '2025_10_20_212402_create_client_testimonials_table', 1),
(25, '2025_10_21_170554_create_clients_info_table', 2),
(26, '2025_10_21_230124_add_role_and_approval_to_users_table', 3),
(27, '2025_10_22_164022_add_thumb_to_projects_table', 4),
(28, '2025_10_23_030814_add_skill_cover_to_projects_table', 5),
(29, '2025_10_23_083610_add_icon_and_description_to_skill_competencies_table', 6),
(30, '2025_10_27_221827_create_skill_infos_table', 7),
(31, '2025_10_31_085858_create_sliders_table', 8),
(32, '2025_10_31_101122_create_lines_table', 9),
(33, '2025_11_02_213453_add_cargo_to_users_table', 10),
(34, '2025_11_02_213854_add_photo_to_users_table', 10),
(35, '2025_11_02_215332_create_social_media_user_table', 11),
(36, '2025_11_02_230956_add_description_to_users_table', 12),
(37, '2025_11_03_003844_create_classes_table', 13),
(38, '2025_11_03_010745_create_class_user_table', 14),
(39, '2025_11_03_023826_create_aboutus_table', 15),
(40, '2025_11_03_040511_add_cover_to_aboutus_table', 16),
(41, '2025_11_03_043816_create_business_hours_table', 17),
(42, '2025_11_03_211034_create_plans_table', 18),
(43, '2025_11_03_211037_create_plan_advantages_table', 18),
(44, '2025_11_04_112631_create_budgets_table', 19),
(45, '2025_11_04_114842_create_budgets_table', 20),
(46, '2025_11_04_114947_create_budget_items_table', 21),
(47, '2025_11_04_115339_create_extras_table', 22),
(48, '2025_11_04_115911_create_extra_service_table', 23),
(49, '2025_11_04_120212_create_budget_events_table', 24),
(50, '2025_11_04_120624_create_email_templates_table', 25),
(51, '2025_11_04_122127_update_extra_service_add_custom_fields', 26),
(52, '2025_11_04_130000_create_exchange_rates_table', 27),
(53, '2025_11_04_190038_create_budget_payments_table', 27),
(54, '2025_11_04_233501_create_project_plannings_table', 28),
(55, '2025_11_04_233538_create_planning_briefing_reguas_table', 28),
(56, '2025_11_04_234841_create_planning_briefing_responses_table', 28),
(57, '2025_11_05_012551_create_qualitative_templates_table', 29),
(58, '2025_11_05_014956_create_qualitative_templates_table', 30),
(59, '2025_11_05_020016_create_planning_briefing_qualitatives_table', 31),
(60, '2025_11_05_020031_create_planning_briefing_qualitative_responses_table', 32),
(61, '2025_11_05_021248_create_planning_interpretacoes_table', 33),
(62, '2025_11_05_022033_create_planning_kickoffs_table', 33),
(63, '2025_11_05_230000_add_template_id_to_planning_briefing_qualitatives_table', 34),
(64, '2025_11_06_000000_add_budget_id_to_projects_table', 35),
(65, '2025_11_06_153315_create_global_pages_table', 36),
(66, '2025_11_06_153411_create_storytelling_components_table', 36),
(67, '2025_11_06_164428_create_global_page_component_table', 37),
(68, '2025_11_06_165208_create_project_pages_table', 38),
(69, '2025_11_06_165440_create_project_page_component_table', 38),
(70, '2025_11_06_204906_create_project_tasks_table', 39),
(71, '2025_11_07_120140_update_project_tasks_add_planned_remove_notes', 40),
(72, '2025_11_07_121709_create_project_task_items_table', 41);

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planning_briefing_qualitatives`
--

CREATE TABLE `planning_briefing_qualitatives` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `template_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Briefing Qualitativo',
  `status` enum('draft','in_progress','completed','approved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `selected_templates` json DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planning_briefing_qualitative_responses`
--

CREATE TABLE `planning_briefing_qualitative_responses` (
  `id` bigint UNSIGNED NOT NULL,
  `briefing_id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `template_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `answer` longtext COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `answered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planning_briefing_reguas`
--

CREATE TABLE `planning_briefing_reguas` (
  `id` bigint UNSIGNED NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label_left` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label_right` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emoji_left` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emoji_right` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min` int NOT NULL DEFAULT '0',
  `max` int NOT NULL DEFAULT '10',
  `step` int NOT NULL DEFAULT '1',
  `default_value` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `planning_briefing_reguas`
--

INSERT INTO `planning_briefing_reguas` (`id`, `category`, `question`, `label_left`, `label_right`, `emoji_left`, `emoji_right`, `min`, `max`, `step`, `default_value`, `created_at`, `updated_at`) VALUES
(1, 'Personalidade da Marca / Site', 'O estilo deve ser mais tradicional ou inovador?', 'Tradicional', 'Inovador', '🏛️', '🚀', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(2, 'Personalidade da Marca / Site', 'A comunicação deve ser mais formal ou descontraída?', 'Formal', 'Descontraído', '🧳', '😎', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(3, 'Personalidade da Marca / Site', 'O layout deve ser mais minimalista ou detalhado?', 'Minimalista', 'Detalhado', '⚪', '🎨', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(4, 'Personalidade da Marca / Site', 'A identidade visual deve tender ao masculino ou feminino?', 'Masculino', 'Feminino', '🖤', '💖', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(5, 'Personalidade da Marca / Site', 'As cores devem ser mais frias ou quentes?', 'Frias', 'Quentes', '❄️', '🔥', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(6, 'Personalidade da Marca / Site', 'O estilo deve ser mais simples ou sofisticado?', 'Simples', 'Sofisticado', '💡', '💎', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(7, 'Personalidade da Marca / Site', 'O tom da marca deve ser mais sério ou divertido?', 'Sério', 'Divertido', '⚖️', '🎉', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(8, 'Público e Comunicação', 'Seu público é mais jovem ou maduro?', 'Jovem', 'Maduro', '🧒', '👨‍💼', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(9, 'Público e Comunicação', 'Seu público é mais técnico ou leigo?', 'Técnico', 'Leigo', '💻', '💬', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(10, 'Público e Comunicação', 'Seu público é mais local ou nacional?', 'Local', 'Nacional', '📍', '🌎', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(11, 'Público e Comunicação', 'O tom da comunicação deve ser mais informativo ou inspiracional?', 'Informativo', 'Inspiracional', '📄', '✨', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(12, 'Público e Comunicação', 'O público é mais masculino ou feminino?', 'Masculino', 'Feminino', '🖤', '💖', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(13, 'Público e Comunicação', 'O público tem perfil mais popular ou premium?', 'Popular', 'Premium', '💸', '💎', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(14, 'Objetivos', 'O foco principal é institucional ou comercial?', 'Institucional', 'Comercial', '🏢', '🛍️', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(15, 'Objetivos', 'O site deve priorizar vendas diretas ou geração de leads?', 'Vendas diretas', 'Geração de leads', '💳', '📩', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(16, 'Objetivos', 'A prioridade é performance ou estilo visual?', 'Performance', 'Estilo visual', '⚡', '🎨', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(17, 'Objetivos', 'O conteúdo deve focar em texto ou imagens/vídeos?', 'Texto', 'Imagens e vídeos', '✍️', '📷', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(18, 'Objetivos', 'O foco geral é mais imagem institucional ou resultado prático?', 'Imagem institucional', 'Resultado / Conversão', '🏛️', '💰', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(19, 'Estilo Visual', 'O design deve ter aparência mais escura ou clara?', 'Escuro', 'Claro', '🌑', '☀️', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(20, 'Estilo Visual', 'Prefere um visual mais simples ou mais realista/profundo?', 'Simples / Minimalista', 'Realista / Profundo', '⚪', '🧊', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(21, 'Estilo Visual', 'Prefere elementos ilustrados ou fotográficos?', 'Ilustrado', 'Fotográfico', '✏️', '📸', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(22, 'Estilo Visual', 'Os elementos devem ser mais geométricos ou orgânicos?', 'Geométrico', 'Orgânico', '⬜', '🌿', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01'),
(23, 'Estilo Visual', 'A estética deve remeter ao retrô ou futurista?', 'Retrô', 'Futurista', '📺', '🤖', -2, 2, 1, 0, '2025-11-05 04:11:01', '2025-11-05 04:11:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `planning_briefing_responses`
--

CREATE TABLE `planning_briefing_responses` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `briefing_regua_id` bigint UNSIGNED NOT NULL,
  `value` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planning_interpretacoes`
--

CREATE TABLE `planning_interpretacoes` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `analise_publico` longtext COLLATE utf8mb4_unicode_ci,
  `analise_concorrencia` longtext COLLATE utf8mb4_unicode_ci,
  `diretrizes_visuais` longtext COLLATE utf8mb4_unicode_ci,
  `definicao_escopo` longtext COLLATE utf8mb4_unicode_ci,
  `observacoes_tecnicas` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','review','approved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planning_kickoffs`
--

CREATE TABLE `planning_kickoffs` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Reunião de Kickoff',
  `objetivo` text COLLATE utf8mb4_unicode_ci,
  `resumo_alinhamento` longtext COLLATE utf8mb4_unicode_ci,
  `tarefas_iniciais` longtext COLLATE utf8mb4_unicode_ci,
  `responsaveis` longtext COLLATE utf8mb4_unicode_ci,
  `materiais_apresentados` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('agendado','realizado','aprovado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'agendado',
  `data_reuniao` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `plans`
--

CREATE TABLE `plans` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `plans`
--

INSERT INTO `plans` (`id`, `service_id`, `category`, `price`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Presença Digital', 3000.00, 'Foco em venda imediata, conversão de campanhas', '2025-11-04 01:02:17', '2025-11-04 01:02:17'),
(2, 2, 'Presença Digital', 4000.00, 'Foco em autoridade e apresentação compacta', '2025-11-04 01:02:17', '2025-11-04 01:02:17'),
(3, 3, 'Presença Digital', 5000.00, 'Foco em profundidade e conteúdo contínuo', '2025-11-04 01:02:17', '2025-11-04 01:02:17'),
(4, 4, 'Soluções Inteligentes', 15000.00, 'Foco em gestão dinâmica e atualização contínua de conteúdo', '2025-11-04 01:02:17', '2025-11-04 01:02:17'),
(5, 5, 'Soluções Inteligentes', 40000.00, 'Foco em automatizar processos e centralizar operações internas', '2025-11-04 01:02:17', '2025-11-04 01:02:17'),
(6, 6, 'Soluções Inteligentes', 100000.00, 'Foco em criar sua plataforma como um produto digital escalável', '2025-11-04 01:02:17', '2025-11-04 01:02:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `plan_advantages`
--

CREATE TABLE `plan_advantages` (
  `id` bigint UNSIGNED NOT NULL,
  `plan_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `plan_advantages`
--

INSERT INTO `plan_advantages` (`id`, `plan_id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Design direcionado à conversão', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(2, 1, 'Navegação fluida e suave', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(3, 1, 'Storytelling estruturado e CTA estratégico', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(4, 1, 'Integração com WhatsApp, formulários e redes sociais', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(5, 1, 'SEO básico e carregamento otimizado', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(6, 2, 'Design moderno e responsivo', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(7, 2, 'Navegação fluida e suave', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(8, 2, 'Integração com WhatsApp, formulários e redes sociais', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(9, 2, 'SEO técnico leve e otimização de performance', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(10, 2, 'Hospedagem + domínio configurado', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(11, 3, 'Conteúdo dividido em páginas', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(12, 3, 'Conteúdo mais aprofundado', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(13, 3, 'SEO avançado com sitemap e URLs otimizadas', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(14, 3, 'Integração com WhatsApp, formulários e redes sociais', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(15, 3, 'Otimização para navegação fluida entre páginas', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(16, 4, 'Painel administrativo', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(17, 4, 'Site público e dashboard do administrador', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(18, 4, 'Banco de dados integrado', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(19, 4, 'Controle de tarefas e gerenciamento de conteúdo', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(20, 4, 'Layout exclusivo para camada administrativa', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(21, 5, 'Desenvolvimento de soluções personalizadas', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(22, 5, 'Automação de processos e tarefas repetitivas', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(23, 5, 'Painéis otimizados com métricas inteligentes e estratégicas', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(24, 5, 'Relatórios dinâmicos', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(25, 5, 'Integração com APIs', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(26, 6, 'Autenticação multiusuário e gerenciamento de permissões', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(27, 6, 'Integração com APIs, automações e gateways de pagamento', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(28, 6, 'Infraestrutura otimizada com hospedagem dedicada', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12'),
(29, 6, 'Atualizações contínuas', NULL, '2025-11-04 01:05:12', '2025-11-04 01:05:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `processes`
--

CREATE TABLE `processes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `processes`
--

INSERT INTO `processes` (`id`, `name`, `slug`, `icon`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Wireframe', 'wireframe', '<i class=\"fa-solid fa-table-layout\"></i>', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(2, 'Prototype', 'prototype', '<i class=\"fa-solid fa-pen-ruler\"></i>', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(3, 'Moodboard', 'moodboard', '<i class=\"fa-solid fa-gears\"></i>', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `projects`
--

CREATE TABLE `projects` (
  `id` bigint UNSIGNED NOT NULL,
  `cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skill_cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `client_id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `budget_id` bigint UNSIGNED DEFAULT NULL,
  `video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finished_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `projects`
--

INSERT INTO `projects` (`id`, `cover`, `thumb`, `skill_cover`, `name`, `slug`, `summary`, `client_id`, `service_id`, `budget_id`, `video`, `finished_at`, `created_at`, `updated_at`) VALUES
(1, 'storage/projects/PnwXOciWgNxR0wPNL0dgY5RsnCF3rG1DkOch1yWR.mp4', 'storage/projects/thumbs/PT3v7iDcwggR2DtwCqR6MXu7xx0unyeb1oaHEzg7.png', 'storage/projects/skills/QRaQzQsTLGaT5xqGCks4BzCcjkNajIB9RRLB7mX8.jpg', 'Portal MM Criativos', 'portal-mm-criativos', 'Criação do portal da MM Criativos com dashboard administrativa para edição de projetos e informações dinâmicas no site.', 2, 4, NULL, 'https://www.youtube.com/watch?v=OWhtFllgQ5E&t=7s', '2025-11-07', '2025-10-21 20:49:17', '2025-11-07 19:09:55');

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_challenges`
--

CREATE TABLE `project_challenges` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `project_challenges`
--

INSERT INTO `project_challenges` (`id`, `project_id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(5, 1, 'Equilibrar criatividade e performance técnica', 'Criar um site que mostrasse o lado artístico e conceitual da MM Criativos, sem abrir mão da velocidade, SEO e estrutura técnica sólida.', '2025-10-22 20:38:23', '2025-10-22 20:38:23'),
(6, 1, 'Transformar o portfólio em uma vitrine dinâmica', 'Construir um sistema onde cada projeto pudesse contar sua história — com desafios, soluções, processos e resultados, sem depender de código.', '2025-10-22 20:38:57', '2025-10-22 20:38:57'),
(8, 1, 'Desenvolvimento modular em Laravel + Blade Components', 'Cada parte do site (serviços, skills, projetos, processos) foi estruturada como componente reutilizável e gerida dinamicamente via banco.', '2025-10-22 21:05:45', '2025-10-22 21:05:45');

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_images`
--

CREATE TABLE `project_images` (
  `id` bigint UNSIGNED NOT NULL,
  `project_process_id` bigint UNSIGNED NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `solution` text COLLATE utf8mb4_unicode_ci,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `project_images`
--

INSERT INTO `project_images` (`id`, `project_process_id`, `image`, `title`, `description`, `solution`, `order`, `created_at`, `updated_at`) VALUES
(20, 6, 'storage/projects/processes/HOsubXI2fnQoCfsjx8OCVtjw4hHfslW167DjlUoi.png', 'Teste 1', 'Teste 1', 'Desenvolvimento modular em Laravel + Blade Components', 1, '2025-10-23 00:24:05', '2025-10-23 00:25:00'),
(21, 6, 'storage/projects/processes/3RNHlrb46DGGpNWp3lZy5hPf49s1PEVmtkfFBwtK.png', 'Teste 2', 'Teste 2', 'Desenvolvimento modular em Laravel + Blade Components', 2, '2025-10-23 00:24:05', '2025-10-23 00:25:28'),
(22, 6, 'storage/projects/processes/AoFAuuIrmUoSWXWC93YQkFzrGlFv2JVDwEjDOnLM.png', 'Teste 3', 'Teste 3', 'Desenvolvimento modular em Laravel + Blade Components', 3, '2025-10-23 00:24:05', '2025-10-23 00:25:39'),
(23, 6, 'storage/projects/processes/TwOQOHcjSHZ9MKS9CbCbwHMHZmSO5AWIk5EShFO6.png', 'Teste 4', 'Teste 4', 'Desenvolvimento modular em Laravel + Blade Components', 1, '2025-10-23 00:24:05', '2025-10-23 00:25:51'),
(24, 6, 'storage/projects/processes/Wjl38fexX2HBBrmXwrc3OfzAWZBqTlix0H1z2esp.png', 'Teste 5', 'Teste 5', 'Desenvolvimento modular em Laravel + Blade Components', 5, '2025-10-23 00:24:05', '2025-10-23 00:26:04'),
(25, 6, 'storage/projects/processes/eEnJlWGrAfC2EfFc4QjLDverkKabu947rOLUVwkp.png', 'Teste 6', 'Teste 6', 'Desenvolvimento modular em Laravel + Blade Components', 6, '2025-10-23 00:24:05', '2025-10-23 00:26:13'),
(26, 7, 'storage/projects/processes/tzaZLJBL3DAfD5RlDj4GzIg4ORjzY9PoUkcyQEdS.png', 'Teste A', 'Teste A', 'Sistema de portfólio interativo e narrativo', 1, '2025-10-23 00:29:38', '2025-10-23 00:29:51'),
(27, 7, 'storage/projects/processes/kFVWbx01mQ8TAUfaCItRXr3hOBfg2tXkqoZOLZWR.png', 'Teste B', 'Teste B', 'Sistema de portfólio interativo e narrativo', 2, '2025-10-23 00:29:38', '2025-10-23 00:30:03'),
(28, 7, 'storage/projects/processes/6J5SOPNYPsW6Pk3BAtsZ1IAPaqUzvcbLZjHxDlLO.png', 'Teste C', 'Teste C', 'Sistema de portfólio interativo e narrativo', 3, '2025-10-23 00:29:38', '2025-10-23 00:30:14'),
(29, 7, 'storage/projects/processes/ddJkdS6CUCf9pEURuQXRvaj6xmcPGgQ4wkFTVGSF.png', 'Teste D', 'Teste D', 'Sistema de portfólio interativo e narrativo', 4, '2025-10-23 00:29:38', '2025-10-23 00:30:32'),
(30, 7, 'storage/projects/processes/2XvjHy1C9UAIRyoLbNn40354cRJgurrYTFVvUzlP.png', 'Teste E', 'Teste E', 'Sistema de portfólio interativo e narrativo', 5, '2025-10-23 00:29:38', '2025-10-23 00:30:46'),
(31, 7, 'storage/projects/processes/vbXVOFXl8I24cmPtDUPnUk8DUx8axeq8y8QkdF1Q.png', 'Teste F', 'Teste F', 'Sistema de portfólio interativo e narrativo', 6, '2025-10-23 00:29:38', '2025-10-23 00:31:03');

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_pages`
--

CREATE TABLE `project_pages` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `global_page_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_page_component`
--

CREATE TABLE `project_page_component` (
  `id` bigint UNSIGNED NOT NULL,
  `project_page_id` bigint UNSIGNED NOT NULL,
  `component_id` bigint UNSIGNED NOT NULL,
  `global_component_id` bigint UNSIGNED DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `settings` json DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_plannings`
--

CREATE TABLE `project_plannings` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `status` enum('not_started','in_progress','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_started',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_process`
--

CREATE TABLE `project_process` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `process_id` bigint UNSIGNED NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `project_process`
--

INSERT INTO `project_process` (`id`, `project_id`, `process_id`, `description`, `order`, `created_at`, `updated_at`) VALUES
(6, 1, 1, 'Organização visual e hierarquia de conteúdo.', 3, '2025-10-23 00:23:10', '2025-10-23 00:26:19'),
(7, 1, 2, 'Cores, tipografias e componentes visuais do projeto.', 2, '2025-10-23 00:28:54', '2025-10-23 00:44:20'),
(8, 1, 3, 'Modelagem e estrutura técnica que sustentam a aplicação', 1, '2025-10-23 00:31:11', '2025-10-23 00:44:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_skill_competency`
--

CREATE TABLE `project_skill_competency` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `skill_id` bigint UNSIGNED NOT NULL,
  `skill_competency_id` bigint UNSIGNED NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `project_skill_competency`
--

INSERT INTO `project_skill_competency` (`id`, `project_id`, `skill_id`, `skill_competency_id`, `order`, `created_at`, `updated_at`) VALUES
(15, 1, 1, 10, 1, '2025-10-23 00:26:58', '2025-10-23 00:26:58'),
(16, 1, 1, 7, 2, '2025-10-23 00:26:58', '2025-10-23 00:26:58'),
(17, 1, 1, 2, 3, '2025-10-23 00:26:58', '2025-10-23 00:26:58'),
(18, 1, 1, 1, 4, '2025-10-23 00:26:58', '2025-10-23 00:26:58'),
(19, 1, 1, 3, 5, '2025-10-23 00:26:58', '2025-10-23 00:26:58'),
(20, 1, 1, 8, 6, '2025-10-23 00:26:58', '2025-10-23 00:26:58'),
(21, 1, 1, 6, 7, '2025-10-23 00:26:58', '2025-10-23 00:26:58'),
(22, 1, 2, 14, 8, '2025-10-23 00:27:21', '2025-10-23 00:27:21'),
(23, 1, 2, 18, 9, '2025-10-23 00:27:21', '2025-10-23 00:27:21'),
(24, 1, 2, 15, 10, '2025-10-23 00:27:21', '2025-10-23 00:27:21'),
(25, 1, 2, 17, 11, '2025-10-23 00:27:21', '2025-10-23 00:27:21'),
(26, 1, 2, 12, 12, '2025-10-23 00:27:21', '2025-10-23 00:27:21'),
(27, 1, 2, 11, 13, '2025-10-23 00:27:21', '2025-10-23 00:27:21'),
(28, 1, 2, 13, 14, '2025-10-23 00:27:21', '2025-10-23 00:27:21'),
(29, 1, 3, 26, 15, '2025-10-23 00:27:39', '2025-10-23 00:27:39'),
(30, 1, 3, 21, 16, '2025-10-23 00:27:39', '2025-10-23 00:27:39'),
(31, 1, 3, 22, 17, '2025-10-23 00:27:39', '2025-10-23 00:27:39'),
(32, 1, 3, 24, 18, '2025-10-23 00:27:39', '2025-10-23 00:27:39'),
(33, 1, 3, 28, 19, '2025-10-23 00:27:39', '2025-10-23 00:27:39'),
(34, 1, 3, 23, 20, '2025-10-23 00:27:39', '2025-10-23 00:27:39'),
(35, 1, 3, 29, 21, '2025-10-23 00:27:39', '2025-10-23 00:27:39'),
(36, 1, 3, 27, 22, '2025-10-23 00:27:39', '2025-10-23 00:27:39'),
(37, 1, 3, 30, 23, '2025-10-23 00:27:39', '2025-10-23 00:27:39'),
(38, 1, 3, 25, 24, '2025-10-23 00:27:39', '2025-10-23 00:27:39'),
(39, 1, 4, 36, 25, '2025-10-23 00:27:58', '2025-10-23 00:27:58'),
(40, 1, 4, 40, 26, '2025-10-23 00:27:58', '2025-10-23 00:27:58'),
(41, 1, 4, 38, 27, '2025-10-23 00:27:58', '2025-10-23 00:27:58'),
(42, 1, 4, 35, 28, '2025-10-23 00:27:58', '2025-10-23 00:27:58'),
(43, 1, 4, 32, 29, '2025-10-23 00:27:58', '2025-10-23 00:27:58'),
(44, 1, 4, 31, 30, '2025-10-23 00:27:58', '2025-10-23 00:27:58'),
(45, 1, 4, 33, 31, '2025-10-23 00:27:58', '2025-10-23 00:27:58'),
(46, 1, 5, 47, 32, '2025-10-23 00:28:43', '2025-10-23 00:28:43'),
(47, 1, 5, 41, 33, '2025-10-23 00:28:43', '2025-10-23 00:28:43'),
(48, 1, 5, 45, 34, '2025-10-23 00:28:43', '2025-10-23 00:28:43'),
(49, 1, 5, 48, 35, '2025-10-23 00:28:43', '2025-10-23 00:28:43'),
(50, 1, 5, 43, 36, '2025-10-23 00:28:43', '2025-10-23 00:28:43');

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_solutions`
--

CREATE TABLE `project_solutions` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `project_solutions`
--

INSERT INTO `project_solutions` (`id`, `project_id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(4, 1, 'Desenvolvimento modular em Laravel + Blade Components', 'Cada parte do site (serviços, skills, projetos, processos) foi estruturada como componente reutilizável e gerida dinamicamente via banco.', '2025-10-22 21:06:22', '2025-10-22 21:06:22'),
(5, 1, 'Sistema de portfólio interativo e narrativo', 'Implementação de seções com resumo, desafios, soluções, processos (wireframes, UI, lógica) e vídeo final — criando storytelling visual e técnico.', '2025-10-22 21:06:36', '2025-10-22 21:06:36'),
(6, 1, 'Otimização para SEO, performance e responsividade', 'Uso de HTML semântico, lazy loading, minificação, e adaptação completa para desktop e mobile, garantindo leveza e consistência visual.', '2025-10-22 21:06:50', '2025-10-22 21:06:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_tasks`
--

CREATE TABLE `project_tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `skill_id` bigint UNSIGNED DEFAULT NULL,
  `skill_competency_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','in_progress','done') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `planned_at` timestamp NULL DEFAULT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `project_task_items`
--

CREATE TABLE `project_task_items` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `project_task_id` bigint UNSIGNED NOT NULL,
  `skill_id` bigint UNSIGNED DEFAULT NULL,
  `skill_competency_id` bigint UNSIGNED DEFAULT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_done` tinyint(1) NOT NULL DEFAULT '0',
  `done_at` timestamp NULL DEFAULT NULL,
  `order` tinyint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `qualitative_templates`
--

CREATE TABLE `qualitative_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('text','textarea','choice','multi_choice','file') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'textarea',
  `options` json DEFAULT NULL,
  `placeholder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `qualitative_templates`
--

INSERT INTO `qualitative_templates` (`id`, `service_type`, `category`, `question`, `type`, `options`, `placeholder`, `is_required`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'global', 'Público e Segmento', 'Quem é o público principal que você deseja atingir?', 'textarea', NULL, 'Ex: jovens entre 20 e 30 anos, interessados em cultura pop.', 0, 1, 1, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(2, 'global', 'Público e Segmento', 'Há outros públicos secundários importantes?', 'textarea', NULL, 'Ex: profissionais do setor, imprensa, parceiros.', 0, 1, 2, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(3, 'global', 'Público e Segmento', 'O público já conhece a sua marca?', 'choice', '[\"Sim\", \"Parcialmente\", \"Não\"]', NULL, 0, 1, 3, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(4, 'global', 'Público e Segmento', 'Onde o público encontra sua marca atualmente?', 'multi_choice', '[\"Instagram\", \"Google\", \"Indicação\", \"YouTube\", \"Outro\"]', NULL, 0, 1, 4, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(5, 'global', 'Público e Segmento', 'Quais hábitos de consumo esse público possui?', 'multi_choice', '[\"Compra online\", \"Prefere lojas físicas\", \"Assina serviços mensais\", \"Compra por impulso\", \"Pesquisa antes de comprar\"]', NULL, 0, 1, 5, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(6, 'global', 'Público e Segmento', 'Quais redes sociais seu público mais utiliza?', 'multi_choice', '[\"Instagram\", \"TikTok\", \"LinkedIn\", \"Facebook\", \"X (Twitter)\", \"YouTube\"]', NULL, 0, 1, 6, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(7, 'global', 'Público e Segmento', 'Seu público é mais sensível a preço ou qualidade?', 'choice', '[\"Preço\", \"Equilíbrio\", \"Qualidade\"]', NULL, 0, 1, 7, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(8, 'global', 'Público e Segmento', 'Como você descreveria o estilo de vida do seu público?', 'textarea', NULL, 'Ex: prático, conectado, familiar, aspiracional...', 0, 1, 8, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(9, 'global', 'Público e Segmento', 'Quais problemas o seu público enfrenta que o seu produto/serviço resolve?', 'textarea', NULL, NULL, 0, 1, 9, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(10, 'global', 'Público e Segmento', 'Existe um público que você não quer atingir?', 'textarea', NULL, 'Ex: público de baixa renda, pessoas que não usam tecnologia...', 0, 1, 10, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(11, 'global', 'Concorrentes e Diferenciais', 'Liste até 3 concorrentes diretos da sua marca.', 'textarea', NULL, 'Ex: Apple, Spotify, Notion...', 0, 1, 1, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(12, 'global', 'Concorrentes e Diferenciais', 'O que você admira nesses concorrentes?', 'textarea', NULL, NULL, 0, 1, 2, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(13, 'global', 'Concorrentes e Diferenciais', 'O que você gostaria de evitar ou fazer diferente?', 'textarea', NULL, NULL, 0, 1, 3, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(14, 'global', 'Concorrentes e Diferenciais', 'Envie links de sites ou marcas que você considera referência.', 'file', NULL, 'Envie links, PDFs ou imagens de referência.', 0, 1, 4, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(15, 'global', 'Concorrentes e Diferenciais', 'O que torna sua marca única em relação aos concorrentes?', 'textarea', NULL, NULL, 0, 1, 5, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(16, 'global', 'Concorrentes e Diferenciais', 'Se pudesse se inspirar em uma empresa famosa, qual seria e por quê?', 'textarea', NULL, NULL, 0, 1, 6, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(17, 'global', 'Concorrentes e Diferenciais', 'Você acompanha seus concorrentes nas redes sociais?', 'choice', '[\"Sim\", \"Às vezes\", \"Não\"]', NULL, 0, 1, 7, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(18, 'global', 'Concorrentes e Diferenciais', 'Quais elementos do site dos concorrentes você gostaria de incorporar?', 'multi_choice', '[\"Layout\", \"Cores\", \"Navegação\", \"Textos\", \"Imagens\"]', NULL, 0, 1, 8, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(19, 'global', 'Concorrentes e Diferenciais', 'Você sente que está à frente ou atrás da concorrência digitalmente?', 'choice', '[\"À frente\", \"Equilibrado\", \"Atrás\"]', NULL, 0, 1, 9, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(20, 'global', 'Concorrentes e Diferenciais', 'O que você acredita que seu público valoriza mais em você?', 'textarea', NULL, NULL, 0, 1, 10, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(21, 'global', 'Estilo Visual / Referências', 'Quais estilos de site ou design você gosta?', 'file', NULL, 'Envie links, imagens ou prints de sites que você admira.', 0, 1, 1, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(22, 'global', 'Estilo Visual / Referências', 'Há algo que você não quer ver de jeito nenhum no seu site?', 'textarea', NULL, 'Ex: sites escuros, fontes serifadas, animações exageradas...', 0, 1, 2, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(23, 'global', 'Estilo Visual / Referências', 'Como você gostaria que as pessoas se sentissem ao visitar o site?', 'textarea', NULL, 'Ex: inspiradas, acolhidas, impactadas, seguras...', 0, 1, 3, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(24, 'global', 'Estilo Visual / Referências', 'Prefere um estilo mais moderno ou tradicional?', 'choice', '[\"Moderno\", \"Tradicional\", \"Intermediário\"]', NULL, 0, 1, 4, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(25, 'global', 'Estilo Visual / Referências', 'Que tipo de cores você mais se identifica?', 'choice', '[\"Cores frias\", \"Cores quentes\", \"Cores neutras\", \"Mistura\"]', NULL, 0, 1, 5, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(26, 'global', 'Estilo Visual / Referências', 'Há alguma marca cuja estética você gostaria de seguir como referência?', 'textarea', NULL, 'Ex: Netflix, Apple, Discord...', 0, 1, 6, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(27, 'global', 'Estilo Visual / Referências', 'Prefere um visual mais ilustrado ou mais fotográfico?', 'choice', '[\"Ilustrado\", \"Fotográfico\", \"Ambos\"]', NULL, 0, 1, 7, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(28, 'global', 'Estilo Visual / Referências', 'Você deseja usar muitas animações ou algo mais estático?', 'choice', '[\"Dinâmico\", \"Equilibrado\", \"Minimalista\"]', NULL, 0, 1, 8, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(29, 'global', 'Estilo Visual / Referências', 'Há elementos visuais que representam bem a sua marca?', 'textarea', NULL, 'Ex: triângulos, linhas, ondas, luzes, etc.', 0, 1, 9, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(30, 'global', 'Estilo Visual / Referências', 'Envie arquivos de identidade visual, logotipos ou guias de marca, se tiver.', 'file', NULL, 'Arquivos .png, .pdf, .zip...', 0, 1, 10, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(31, 'global', 'Funcionalidades Desejadas', 'Quais funcionalidades principais você precisa?', 'textarea', NULL, 'Ex: blog, login, galeria de imagens, formulário de contato...', 0, 1, 1, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(32, 'global', 'Funcionalidades Desejadas', 'Seu projeto precisará de área restrita (usuários, login, painel)?', 'choice', '[\"Sim\", \"Não\", \"Talvez futuramente\"]', NULL, 0, 1, 2, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(33, 'global', 'Funcionalidades Desejadas', 'Deseja integração com algum serviço externo?', 'multi_choice', '[\"Redes sociais\", \"Pagamentos\", \"Google Maps\", \"E-mail Marketing\", \"Outro\"]', NULL, 0, 1, 3, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(34, 'global', 'Funcionalidades Desejadas', 'Você deseja um painel administrativo?', 'choice', '[\"Sim\", \"Não\", \"Não sei ainda\"]', NULL, 0, 1, 4, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(35, 'global', 'Funcionalidades Desejadas', 'Há recursos obrigatórios para o lançamento?', 'textarea', NULL, 'Ex: login, área de notícias, busca interna...', 0, 1, 5, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(36, 'global', 'Funcionalidades Desejadas', 'Você precisa que o projeto seja multiidioma?', 'choice', '[\"Sim\", \"Não\", \"Talvez no futuro\"]', NULL, 0, 1, 6, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(37, 'global', 'Funcionalidades Desejadas', 'Quais dispositivos são prioridade?', 'multi_choice', '[\"Desktop\", \"Mobile\", \"Tablet\"]', NULL, 0, 1, 7, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(38, 'global', 'Funcionalidades Desejadas', 'Você precisa que o site tenha modo escuro?', 'choice', '[\"Sim\", \"Não\", \"Indiferente\"]', NULL, 0, 1, 8, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(39, 'global', 'Funcionalidades Desejadas', 'O projeto deve permitir comentários, curtidas ou avaliações?', 'choice', '[\"Sim\", \"Não\", \"Talvez futuramente\"]', NULL, 0, 1, 9, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(40, 'global', 'Funcionalidades Desejadas', 'Envie exemplos de sites que possuem as funcionalidades que você deseja.', 'file', NULL, 'Arquivos, prints ou links de referência.', 0, 1, 10, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(41, 'global', 'Tonalidade da Comunicação', 'Como você descreveria o tom ideal da sua marca?', 'choice', '[\"Formal\", \"Descontraído\", \"Inspirador\", \"Divertido\", \"Técnico\"]', NULL, 0, 1, 1, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(42, 'global', 'Tonalidade da Comunicação', 'Há alguma frase, slogan ou lema que represente sua marca?', 'text', NULL, 'Ex: “Transformando ideias em experiências digitais.”', 0, 1, 2, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(43, 'global', 'Tonalidade da Comunicação', 'Como você quer que sua marca soe ao público?', 'textarea', NULL, 'Ex: acolhedora, confiante, moderna...', 0, 1, 3, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(44, 'global', 'Tonalidade da Comunicação', 'Seu público entende melhor comunicações curtas ou detalhadas?', 'choice', '[\"Curtas e diretas\", \"Detalhadas e explicativas\"]', NULL, 0, 1, 4, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(45, 'global', 'Tonalidade da Comunicação', 'Há palavras ou expressões que você quer evitar?', 'textarea', NULL, 'Ex: jargões técnicos, termos antiquados...', 0, 1, 5, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(46, 'global', 'Tonalidade da Comunicação', 'Você deseja que o texto use emojis, gírias ou linguagem mais casual?', 'choice', '[\"Sim\", \"Apenas de leve\", \"Não\"]', NULL, 0, 1, 6, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(47, 'global', 'Tonalidade da Comunicação', 'A comunicação deve transmitir mais emoção ou racionalidade?', 'choice', '[\"Mais emoção\", \"Equilíbrio\", \"Mais racionalidade\"]', NULL, 0, 1, 7, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(48, 'global', 'Tonalidade da Comunicação', 'Como você se refere ao público (você, cliente, parceiro...)?', 'text', NULL, 'Ex: clientes, usuários, membros...', 0, 1, 8, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(49, 'global', 'Tonalidade da Comunicação', 'Quais marcas você acha que se comunicam de forma parecida com o que deseja?', 'textarea', NULL, 'Ex: Nubank, Apple, Notion, Airbnb...', 0, 1, 9, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(50, 'global', 'Tonalidade da Comunicação', 'Há alguma cor, som ou símbolo que represente sua forma de se comunicar?', 'textarea', NULL, 'Ex: laranja vibrante, som de notificação, raio, coração...', 0, 1, 10, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(51, 'global', 'Conteúdo e Atualização', 'Você já possui os textos e imagens do site prontos?', 'choice', '[\"Sim\", \"Parcialmente\", \"Não\"]', NULL, 0, 1, 1, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(52, 'global', 'Conteúdo e Atualização', 'Quem será responsável por atualizar o conteúdo do site após o lançamento?', 'choice', '[\"Minha equipe\", \"MM Criativos\", \"Outro parceiro\"]', NULL, 0, 1, 2, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(53, 'global', 'Conteúdo e Atualização', 'Com que frequência você pretende atualizar o site?', 'choice', '[\"Diariamente\", \"Semanalmente\", \"Mensalmente\", \"Pouco frequentemente\"]', NULL, 0, 1, 3, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(54, 'global', 'Conteúdo e Atualização', 'Você precisa de uma área de blog ou notícias?', 'choice', '[\"Sim\", \"Não\", \"Talvez futuramente\"]', NULL, 0, 1, 4, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(55, 'global', 'Conteúdo e Atualização', 'Quais tipos de conteúdo serão mais frequentes?', 'multi_choice', '[\"Artigos\", \"Vídeos\", \"Produtos\", \"Portfólios\", \"Notícias\"]', NULL, 0, 1, 5, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(56, 'global', 'Conteúdo e Atualização', 'Você possui redatores ou produtores de conteúdo?', 'choice', '[\"Sim\", \"Não\", \"Pretendo contratar\"]', NULL, 0, 1, 6, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(57, 'global', 'Conteúdo e Atualização', 'Há conteúdos obrigatórios para o lançamento?', 'textarea', NULL, 'Ex: sobre, portfólio, blog, contato...', 0, 1, 7, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(58, 'global', 'Conteúdo e Atualização', 'Você deseja que o site tenha suporte para vídeos ou podcasts?', 'choice', '[\"Sim\", \"Não\", \"Talvez futuramente\"]', NULL, 0, 1, 8, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(59, 'global', 'Conteúdo e Atualização', 'Há conteúdos que precisam ser protegidos ou acessíveis apenas a usuários logados?', 'choice', '[\"Sim\", \"Não\", \"Não sei ainda\"]', NULL, 0, 1, 9, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(60, 'global', 'Conteúdo e Atualização', 'Envie arquivos ou textos que já deseja usar no site.', 'file', NULL, 'Arquivos .docx, .txt, .pdf, imagens...', 0, 1, 10, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(61, 'global', 'Expectativas e Medos', 'O que você mais espera alcançar com este projeto?', 'textarea', NULL, 'Ex: aumentar as vendas, reforçar a marca, digitalizar o negócio...', 0, 1, 1, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(62, 'global', 'Expectativas e Medos', 'Qual seria o principal sinal de sucesso pra você após o lançamento?', 'textarea', NULL, 'Ex: receber elogios, mais visitas, novos leads...', 0, 1, 2, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(63, 'global', 'Expectativas e Medos', 'Você tem alguma meta numérica em mente?', 'text', NULL, 'Ex: 1000 visitas/mês, 10 vendas por dia...', 0, 1, 3, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(64, 'global', 'Expectativas e Medos', 'O que você mais teme que aconteça durante o projeto?', 'textarea', NULL, 'Ex: atrasos, dificuldade de comunicação, resultado insatisfatório...', 0, 1, 4, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(65, 'global', 'Expectativas e Medos', 'Qual foi sua pior experiência anterior com um projeto ou agência?', 'textarea', NULL, 'Ex: não cumpriram prazos, não entenderam a proposta...', 0, 1, 5, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(66, 'global', 'Expectativas e Medos', 'O que te deixaria mais tranquilo durante o processo?', 'textarea', NULL, 'Ex: atualizações semanais, acesso ao progresso, comunicação direta...', 0, 1, 6, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(67, 'global', 'Expectativas e Medos', 'Como você define sucesso para este projeto?', 'choice', '[\"Resultado financeiro\", \"Reconhecimento da marca\", \"Facilidade de uso\", \"Experiência visual\"]', NULL, 0, 1, 7, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(68, 'global', 'Expectativas e Medos', 'Há algo que você teme perder com a mudança ou novo projeto?', 'textarea', NULL, 'Ex: identidade antiga, posicionamento, público atual...', 0, 1, 8, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(69, 'global', 'Expectativas e Medos', 'Você deseja participar ativamente do processo de criação?', 'choice', '[\"Sim, quero acompanhar tudo\", \"Prefiro só aprovar etapas\", \"Confio na equipe para decidir\"]', NULL, 0, 1, 9, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(70, 'global', 'Expectativas e Medos', 'Há algo que você acredita que possa dificultar o andamento do projeto?', 'textarea', NULL, 'Ex: falta de tempo, equipe pequena, indefinição de conteúdo...', 0, 1, 10, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(71, 'global', 'Prazos e Entregáveis Esperados', 'Existe uma data importante relacionada ao lançamento?', 'text', NULL, 'Ex: evento, campanha, feriado, lançamento de produto...', 0, 1, 1, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(72, 'global', 'Prazos e Entregáveis Esperados', 'Qual seria o prazo ideal para entrega do projeto?', 'text', NULL, 'Ex: 30 dias, 2 meses, até o final do trimestre...', 0, 1, 2, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(73, 'global', 'Prazos e Entregáveis Esperados', 'Você tem algum prazo limite inadiável?', 'text', NULL, 'Ex: lançamento de produto em 10/12...', 0, 1, 3, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(74, 'global', 'Prazos e Entregáveis Esperados', 'Como você prefere acompanhar o andamento do projeto?', 'choice', '[\"Atualizações semanais\", \"Painel online\", \"Reuniões quinzenais\", \"Somente ao final de cada etapa\"]', NULL, 0, 1, 4, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(75, 'global', 'Prazos e Entregáveis Esperados', 'Você prefere ver versões parciais (protótipos) ou apenas o produto final?', 'choice', '[\"Prefiro acompanhar versões\", \"Apenas o final\", \"Depende do projeto\"]', NULL, 0, 1, 5, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(76, 'global', 'Prazos e Entregáveis Esperados', 'Quais entregáveis você espera ao final do projeto?', 'multi_choice', '[\"Site completo\", \"Design no Figma\", \"Painel administrativo\", \"Documentação\", \"Relatório SEO\"]', NULL, 0, 1, 6, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(77, 'global', 'Prazos e Entregáveis Esperados', 'Você precisa de treinamento sobre como usar o site/sistema?', 'choice', '[\"Sim\", \"Não\", \"Talvez\"]', NULL, 0, 1, 7, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(78, 'global', 'Prazos e Entregáveis Esperados', 'Após o projeto, você deseja contratar manutenção mensal?', 'choice', '[\"Sim\", \"Não\", \"Talvez futuramente\"]', NULL, 0, 1, 8, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(79, 'global', 'Prazos e Entregáveis Esperados', 'Você precisa que o projeto esteja publicado e hospedado por nós?', 'choice', '[\"Sim\", \"Não\", \"Ainda vou decidir\"]', NULL, 0, 1, 9, '2025-11-05 04:55:07', '2025-11-05 04:55:07'),
(80, 'global', 'Prazos e Entregáveis Esperados', 'Há alguma dependência externa que possa impactar o prazo?', 'textarea', NULL, 'Ex: aprovação de terceiros, fotos, textos, integração com sistemas...', 0, 1, 10, '2025-11-05 04:55:07', '2025-11-05 04:55:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `services`
--

CREATE TABLE `services` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `services`
--

INSERT INTO `services` (`id`, `name`, `slug`, `icon`, `thumb`, `cover`, `description`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Landing Page', 'landing', 'fa-light fa-bullseye-arrow', 'storage/services/service-landing-thumb-20251031-070219.jpg', 'storage/services/wNSYPI8pcFRa64Cnyu9rTZUQDyjGZUxMCxalmw0Z.mp4', 'Feita para conversão. Ideal para campanhas e lançamentos.', 1, '2025-10-21 00:48:22', '2025-10-31 10:09:26'),
(2, 'Site Single Page', 'single', 'fa-light fa-window-flip', 'storage/services/service-single-thumb-20251031-071209.jpg', 'storage/services/6n0qsytg0hsXA2qCGyChESxhnLO7f2UGWQaa6XOL.mp4', 'Tudo em uma única página, direto ao ponto, com design impactante.', 2, '2025-10-21 00:48:22', '2025-10-31 10:12:10'),
(3, 'Site Multipage', 'multi', 'fa-light fa-browsers', 'storage/services/service-multi-thumb-20251031-071413.jpg', 'storage/services/XNbS3USi9lb4vabHfWVVoKM4N4bDkLesv2Tx8yPp.mp4', 'Estrutura completa para apresentar sua marca com profundidade.', 3, '2025-10-21 00:48:22', '2025-10-31 10:14:13'),
(4, 'Portal', 'portal', 'fa-light fa-network-wired', 'storage/services/service-portal-thumb-20251031-071429.jpg', 'storage/services/6FGvuJHy22poLuww1eJZKpziN1XcU9uxzEPZxMXI.mp4', 'Soluções sob medida para portais com gerenciamento online.', 4, '2025-10-21 00:48:22', '2025-10-31 10:14:29'),
(5, 'Sistema Personalizado', 'sistema', 'fa-light fa-pen-paintbrush', 'storage/services/service-sistema-thumb-20251031-071444.jpg', 'storage/services/cjj2QoUVXtL8IZc4y7sFix3x4mC9LXE5u6hc5Ogq.mp4', 'Desenvolvemos sistemas únicos, feitos para o fluxo do seu negócio.', 5, '2025-10-21 00:48:22', '2025-10-31 10:14:44'),
(6, 'SaaS e Integrações', 'saas', 'fa-light fa-chart-network', 'storage/services/service-saas-thumb-20251031-071458.jpg', 'storage/services/kx0ujzHPlsAtHbMP8sjt0SF8JhBhqqX3KvAU2rtT.mp4', 'Conectamos automações e serviços em um ecossistema inteligente.', 6, '2025-10-21 00:48:22', '2025-10-31 10:14:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `service_benefits`
--

CREATE TABLE `service_benefits` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `service_benefits`
--

INSERT INTO `service_benefits` (`id`, `service_id`, `title`, `subtitle`, `order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Alta Conversão', 'Estrutura pensada para transformar cliques em resultados reais.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(2, 1, 'Design Estratégico', 'Visual moderno criado para guiar o usuário até a ação principal.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(3, 1, 'Performance Rápida', 'Carregamento rápido para maior retenção do usuário.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(4, 1, 'Experiência Clara', 'Navegação intuitiva e conteúdo direto ao ponto.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(5, 2, 'Design Linear', 'Layout fluido que conduz o visitante por uma jornada contínua e envolvente.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(6, 2, 'Fluxo Intuitivo', 'Navegação simples e funcional, com seções conectadas de forma natural.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(7, 2, 'Identidade Focada', 'Cada elemento visual comunica sua essência com clareza e propósito.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(8, 2, 'Performance Leve', 'Código otimizado e rápido, garantindo carregamento fluido em qualquer tela.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(9, 3, 'Presença Estruturada', 'Organiza suas informações em páginas dedicadas e aprofunda sua história.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(10, 3, 'Expansão Contínua', 'Estrutura flexível que cresce junto com o seu negócio, pronta para novos conteúdos e seções.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(11, 3, 'Autoridade Digital', 'Aprofunda sua comunicação, fortalecendo o posicionamento da marca no mercado.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(12, 3, 'Experiência Completa', 'Melhor maneira de apresentar sua marca e seus produtos ao público.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(13, 4, 'Presença Estruturada', 'Organização clara do conteúdo em páginas únicas que reforçam a credibilidade da sua marca.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(14, 4, 'Expansão Contínua', 'Estrutura adaptável que permite adicionar novas seções, categorias e funcionalidades.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(15, 4, 'Autonomia Total', 'Painel administrativo intuitivo para cadastrar, editar e atualizar conteúdos.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(16, 4, 'Experiência Completa', 'Oferece ao visitante uma jornada imersiva, com páginas que se conectam.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(17, 5, 'Automação Inteligente', 'Reduz tarefas manuais com processos automatizados que otimizam tempo e eficiência.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(18, 5, 'Gestão Centralizada', 'Controle informações, pedidos e relatórios em um único painel, de forma clara e organizada.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(19, 5, 'Produtividade Escalada', 'Ganhe velocidade nas entregas e libere sua equipe para focar no que realmente gera resultado.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(20, 5, 'Controle Estratégico', 'Acompanhe dados em tempo real e tome decisões baseadas em métricas.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(21, 6, 'Escalabilidade Ilimitada', 'Cresça com sistemas preparados para múltiplos usuários e empresas.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(22, 6, 'Integrações Inteligentes', 'Conecte o SaaS a APIs e automações externas para manter um fluxo digital eficiente.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(23, 6, 'Monetização Sustentável', 'Gere receita recorrente com planos, assinaturas e controle de acesso.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(24, 6, 'Operação Automatizada', 'Elimine tarefas repetitivas com rotinas automáticas para processos e tarefas.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `service_ctas`
--

CREATE TABLE `service_ctas` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `service_ctas`
--

INSERT INTO `service_ctas` (`id`, `service_id`, `image`, `title`, `phone`, `created_at`, `updated_at`) VALUES
(1, 1, 'assets/images/resources/cta-1-1.jpg', 'Pronto para lançar sua Landing Page? Fale agora com nossa equipe no WhatsApp!', 'https://wa.me/5511958469546?text=Olá%2C+gostaria+de+criar+minha+Landing+Page+com+a+MM+Criativos!', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(2, 2, 'assets/images/resources/cta-1-1.jpg', 'Pronto para apresentar sua marca ao mundo? Fale com nossa equipe agora!', 'https://wa.me/5511958469546?text=Olá%2C+quero+construir+meu+site+Single+Page+com+a+MM+Criativos!', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(3, 3, 'assets/images/resources/cta-1-1.jpg', 'Fale com nossa equipe. Aprofunde a história da sua marca no digital agora mesmo!', 'https://wa.me/5511958469546?text=Olá%2C+quero+construir+meu+site+Multipage+com+a+MM+Criativos!', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(4, 4, 'assets/images/resources/cta-1-1.jpg', 'Fale com nossa equipe. Construa um portal completo e sob medida para o seu negócio!', 'https://wa.me/5511958469546?text=Olá%2C+quero+construir+meu+Portal+com+a+MM+Criativos!', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(5, 5, 'assets/images/resources/cta-1-1.jpg', 'Otimize sua operação com um sistema feito para o seu negócio. Fale com nossa equipe!', 'https://wa.me/5511958469546?text=Olá%2C+quero+criar+meu+Sistema+Empresarial+com+a+MM+Criativos!', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(6, 6, 'assets/images/resources/cta-1-1.jpg', 'Transforme sua ideia em um SaaS escalável e pronto para crescer com a MM Criativos.', 'https://wa.me/5511958469546?text=Olá%2C+quero+criar+meu+SaaS+com+a+MM+Criativos!', '2025-10-21 00:48:22', '2025-10-21 00:48:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `service_features`
--

CREATE TABLE `service_features` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `service_features`
--

INSERT INTO `service_features` (`id`, `service_id`, `title`, `subtitle`, `order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Desempenho Otimizado', 'Velocidade e estabilidade em todas as telas, garantindo navegação fluida e sem travamentos.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(2, 1, 'Storytelling de Conversão', 'Cada seção da página é planejada como uma narrativa visual que conduz o visitante à ação.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(3, 1, 'Integrações Inteligentes', 'Conecte com Google Analytics, Meta Pixel, WhatsApp e automações para medir resultados reais.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(4, 1, 'Pronta para Campanhas', 'Implantação rápida e ideal para lançamentos, captações e campanhas temporárias.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(5, 1, 'Otimização Contínua', 'Aprimoramento contínuo com base em métricas reais, garantindo que ela performe mesmo após o lançamento.', 5, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(6, 2, 'Apresentação de Marca', 'Estrutura contínua e envolvente que apresenta a identidade, história e proposta de valor da sua marca.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(7, 2, 'Conteúdo Direcionado', 'Cada seção é construída com propósito, destacando informações essenciais e guiando o visitante com clareza.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(8, 2, 'Estrutura Simplificada', 'Menos páginas, mais objetividade. Tudo o que importa em uma única navegação, sem excessos ou distrações.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(9, 2, 'Gestão Prática', 'Fácil de atualizar e manter, permitindo ajustes rápidos de conteúdo e visual sem depender de grandes revisões.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(10, 2, 'Escalabilidade Visual', 'Pode evoluir com sua marca, recebendo novas seções, elementos e recursos sem perder leveza ou consistência.', 5, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(11, 3, 'Arquitetura Modular', 'Cada página tem função própria, criando um ecossistema digital coeso e fácil de navegar.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(12, 3, 'SEO Avançado', 'Estrutura otimizada para buscadores, com páginas indexáveis e conteúdo estratégico para ranqueamento.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(13, 3, 'Gestão Escalável', 'Permite incluir novos produtos, serviços e áreas do site sem comprometer performance ou design.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(14, 3, 'Conteúdo Profundo', 'Espaço para explorar detalhes sobre sua empresa, cases e diferenciais com clareza e propósito.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(15, 3, 'Integração Total', 'Suporte a ferramentas de automação, análise e contato, criando um fluxo completo de comunicação digital.', 5, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(16, 4, 'Arquitetura Modular', 'Cada página tem função própria, criando um ecossistema digital organizado, intuitivo e escalável.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(17, 4, 'SEO Avançado', 'Estrutura otimizada para buscadores, com URLs amigáveis, metadados e hierarquia de conteúdo.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(18, 4, 'Gestão Escalável', 'Painel administrativo leve e adaptável que cresce junto com o volume de dados e conteúdos do site.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(19, 4, 'Conteúdo Profundo', 'Permite explorar detalhes, projetos e histórias da marca com maior densidade e narrativa envolvente.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(20, 4, 'Integração Total', 'Conecte formulários, automações, analytics e APIs externas para centralizar a gestão digital.', 5, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(21, 5, 'Fluxos Automatizados', 'Criação de rotinas automáticas para cadastros, relatórios e comunicações internas.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(22, 5, 'Controle Personalizado', 'Cada módulo é moldado às operações da sua empresa, priorizando agilidade e praticidade.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(23, 5, 'Segurança de Dados', 'Armazenamento seguro, autenticação de usuários e camadas extras de proteção de informações.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(24, 5, 'Integrações Corporativas', 'Conecte o sistema a ERPs, CRMs, gateways e APIs externas para uma gestão totalmente unificada.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(25, 5, 'Escalabilidade Técnica', 'Estrutura preparada para receber novos usuários, módulos e dados sem perda de desempenho.', 5, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(26, 6, 'Arquitetura Multitenant', 'Permite que múltiplos clientes usem o mesmo sistema com segurança, dados isolados e gestão independente.', 1, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(27, 6, 'APIs Conectadas', 'Integração com gateways, CRMs, automações e serviços externos para maximizar o potencial do SaaS.', 2, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(28, 6, 'Painel Inteligente', 'Administração centralizada com métricas, relatórios e insights de performance em tempo real.', 3, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(29, 6, 'Segurança Avançada', 'Controle de acesso, autenticação, criptografia e monitoramento contínuo para proteção total de dados.', 4, '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(30, 6, 'Infraestrutura Escalável', 'Sistema preparado para expansão constante, suportando alto volume de usuários e integrações simultâneas.', 5, '2025-10-21 00:48:22', '2025-10-21 00:48:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `service_infos`
--

CREATE TABLE `service_infos` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `service_infos`
--

INSERT INTO `service_infos` (`id`, `service_id`, `subtitle`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'transforme cliques em resultados', 'Por que investir em uma Landing Page?', 'Uma Landing Page é o formato ideal para quem precisa gerar resultado rápido e mensurável, seja captar leads, divulgar um produto ou validar uma ideia.\r\n\r\nDiferente de um site institucional, ela é direta, estratégica e centrada em conversão.\r\nCada detalhe é pensado para conduzir o visitante até a ação que importa: clicar, preencher, comprar ou entrar em contato.\r\n\r\nSe o seu objetivo é vender mais, captar contatos ou dar destaque a uma campanha, esse é o serviço certo.\r\nA Landing Page da MM Criativos combina design de impacto, copy estratégica e performance técnica, tudo voltado para o que realmente interessa: converter visitantes em clientes.', '2025-10-21 00:48:22', '2025-10-22 04:23:16'),
(2, 2, 'tudo no lugar certo', 'Por que escolher uma Single Page?', 'Uma Single Page é o formato ideal para quem busca apresentar sua marca, serviço ou produto de forma clara, fluida e impactante, tudo em uma única página.\r\n                Ela oferece uma experiência de navegação contínua, em que cada rolagem conduz o visitante por uma história visual bem estruturada, sem distrações.\r\n                <br><br>\r\n                É a escolha perfeita para portfólios, perfis profissionais, lançamentos e pequenas empresas que desejam unir agilidade, design moderno e conteúdo direto ao ponto.\r\n                <br><br>\r\n                As Single Pages da <strong>MM Criativos</strong> são projetadas com foco em identidade visual, narrativa coerente e performance otimizada, entregando uma presença digital leve, objetiva e memorável.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(3, 3, 'presença completa e expansível', 'Por que escolher um Site Multipage?', 'Um Site Multipage é a escolha ideal para marcas e empresas que precisam de uma presença digital robusta, com páginas dedicadas a cada parte da sua comunicação — produtos, serviços, equipe, contato e muito mais.\r\n                <br><br>\r\n                Diferente da Single Page, ele permite aprofundar informações, trabalhar estratégias de SEO com mais precisão e criar jornadas de navegação detalhadas, conduzindo o usuário por diferentes seções de forma lógica e envolvente.\r\n                <br><br>\r\n                É o formato indicado para negócios em crescimento, que buscam consolidar autoridade, melhorar posicionamento no Google e oferecer uma experiência completa a seus visitantes.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(4, 4, 'autonomia e expansão contínua', 'Por que escolher um Portal?', 'Um <strong>Portal</strong> é o formato ideal para quem precisa de um site com estrutura profissional e liberdade para atualizar o conteúdo sempre que quiser.\r\n                <br><br>\r\n                Ele une o design e a experiência de um site tradicional com o poder de um painel administrativo, permitindo que você adicione novos produtos, projetos, artigos ou coleções com facilidade — sem depender de suporte técnico.\r\n                <br><br>\r\n                Diferente de um sistema corporativo, é leve, intuitivo e feito sob medida para negócios criativos, lojas especializadas, estúdios e profissionais que desejam autonomia, performance e crescimento contínuo.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(5, 5, 'eficiência que impulsiona o seu negócio', 'Por que escolher um Sistema Empresarial?', 'Um <strong>Sistema Empresarial</strong> é a solução ideal para quem precisa otimizar tarefas, centralizar processos e ganhar tempo na operação do dia a dia.\r\n                <br><br>\r\n                Ele conecta setores, automatiza rotinas e organiza informações em um só lugar, reduzindo erros e aumentando a produtividade da equipe.\r\n                <br><br>\r\n                O sistema se adapta à sua realidade e cresce junto com o seu negócio, oferecendo controle total, relatórios personalizados e integrações inteligentes para um fluxo de trabalho muito mais eficiente.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(6, 6, 'tecnologia que escala o seu negócio', 'Por que investir em um SaaS e Integrações?', 'Um <strong>SaaS</strong> é o modelo ideal para quem deseja transformar uma ideia em um produto digital real, acessível e escalável.\r\n                <br><br>\r\n                Ele permite que sua solução funcione online, com login, painéis, planos e recursos automatizados, tudo disponível para múltiplos usuários simultaneamente.\r\n                <br><br>\r\n                O sistema se conecta a APIs, automações e serviços externos, criando um ecossistema que trabalha por você.\r\n                <br><br>\r\n                É a escolha certa para startups, empresas de tecnologia e negócios que querem transformar processos em produtos e gerar receita recorrente.', '2025-10-21 00:48:22', '2025-10-21 00:48:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `service_processes`
--

CREATE TABLE `service_processes` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `service_processes`
--

INSERT INTO `service_processes` (`id`, `service_id`, `title`, `description`, `image`, `order`, `created_at`, `updated_at`) VALUES
(2, 1, 'Direção Visual', 'Criamos um conceito visual único, alinhado à sua identidade e voltado à conversão do visitante em ação.', 'storage/services/processes/service-landing-process-direcao-visual-image-20251031-074204.png', 2, '2025-10-21 00:48:22', '2025-10-31 10:42:04'),
(3, 1, 'Lançamento e Performance', 'Publicamos, configuramos as integrações e otimizamos a página para garantir resultados desde o primeiro dia.', 'storage/services/processes/service-landing-process-lancamento-e-performance-image-20251031-074204.png', 3, '2025-10-21 00:48:22', '2025-10-31 10:42:04'),
(4, 2, 'Imersão de Marca', 'Coletamos e refinamos informações sobre sua marca, história e valores para construir uma base sólida de identidade.', 'storage/services/processes/service-single-process-imersao-de-marca-image-20251031-075129.png', 1, '2025-10-21 00:48:22', '2025-10-31 10:51:29'),
(5, 2, 'Construção Visual', 'Desenvolvemos uma estrutura única que apresenta a essência da marca e seus diferenciais com clareza e impacto.', 'storage/services/processes/service-single-process-construcao-visual-image-20251031-075129.png', 2, '2025-10-21 00:48:22', '2025-10-31 10:51:29'),
(6, 2, 'Publicação Estruturada', 'Realizamos a publicação com integrações técnicas e SEO base, garantindo uma presença digital sólida e duradoura.', 'storage/services/processes/service-single-process-publicacao-estruturada-image-20251031-075130.png', 3, '2025-10-21 00:48:22', '2025-10-31 10:51:30'),
(7, 3, 'Diagnóstico e Planejamento', 'Analisamos marca, público e objetivos para definir estrutura e propósito de cada página.', 'storage/services/processes/service-multi-process-diagnostico-e-planejamento-image-20251031-075224.png', 1, '2025-10-21 00:48:22', '2025-10-31 10:52:24'),
(8, 3, 'Arquitetura e Conteúdo', 'Mapa do site, organização de seções e profundidade de comunicação.', 'storage/services/processes/service-multi-process-arquitetura-e-conteudo-image-20251031-075225.png', 2, '2025-10-21 00:48:22', '2025-10-31 10:52:25'),
(9, 3, 'Design e Desenvolvimento', 'Interfaces únicas com tecnologias atuais para navegação fluida e responsiva.', 'storage/services/processes/service-multi-process-design-e-desenvolvimento-image-20251031-075225.png', 3, '2025-10-21 00:48:22', '2025-10-31 10:52:25'),
(10, 3, 'Publicação e Otimização', 'SEO avançado, integrações e acompanhamento técnico de performance.', 'storage/services/processes/service-multi-process-publicacao-e-otimizacao-image-20251031-075226.png', 4, '2025-10-21 00:48:22', '2025-10-31 10:52:26'),
(11, 4, 'Mapeamento de Necessidades', 'Identificamos o que sua marca precisa gerenciar e como cada parte do conteúdo se conecta.', 'storage/services/processes/service-portal-process-mapeamento-de-necessidades-image-20251031-075331.png', 1, '2025-10-21 00:48:22', '2025-10-31 10:53:31'),
(12, 4, 'Arquitetura do Portal', 'Estruturamos o painel administrativo, definindo fluxos, áreas e cadastros essenciais.', 'storage/services/processes/service-portal-process-arquitetura-do-portal-image-20251031-075332.png', 2, '2025-10-21 00:48:22', '2025-10-31 10:53:32'),
(13, 4, 'Design de Interface', 'Criamos o visual do portal, garantindo harmonia entre estética, clareza e usabilidade.', 'storage/services/processes/service-portal-process-design-de-interface-image-20251031-075332.png', 3, '2025-10-21 00:48:22', '2025-10-31 10:53:32'),
(14, 4, 'Desenvolvimento Funcional', 'Implementamos os recursos dinâmicos, integrações e cadastros do painel administrativo.', 'storage/services/processes/service-portal-process-desenvolvimento-funcional-image-20251031-075333.png', 4, '2025-10-21 00:48:22', '2025-10-31 10:53:33'),
(15, 4, 'Implantação e Treinamento', 'Publicamos o portal, configuramos integrações e treinamos você para total autonomia.', 'storage/services/processes/service-portal-process-implantacao-e-treinamento-image-20251031-075333.png', 5, '2025-10-21 00:48:22', '2025-10-31 10:53:33'),
(16, 5, 'Diagnóstico e Mapeamento', 'Entendemos os fluxos e desafios do negócio para definir o que o sistema precisa resolver.', 'storage/services/processes/service-sistema-process-diagnostico-e-mapeamento-image-20251031-075508.png', 1, '2025-10-21 00:48:22', '2025-10-31 10:55:08'),
(17, 5, 'Planejamento de Módulos', 'Definimos as áreas, permissões e funcionalidades de cada módulo com base na rotina da empresa.', 'storage/services/processes/service-sistema-process-planejamento-de-modulos-image-20251031-075508.png', 2, '2025-10-21 00:48:22', '2025-10-31 10:55:08'),
(18, 5, 'Arquitetura Técnica', 'Estruturamos banco de dados, rotas e integrações que sustentam o funcionamento do sistema.', 'storage/services/processes/service-sistema-process-arquitetura-tecnica-image-20251031-075509.png', 3, '2025-10-21 00:48:22', '2025-10-31 10:55:09'),
(19, 5, 'Design de Interface', 'Criamos interfaces claras e funcionais que facilitam o uso diário pelos colaboradores.', 'storage/services/processes/service-sistema-process-design-de-interface-image-20251031-075509.png', 4, '2025-10-21 00:48:22', '2025-10-31 10:55:09'),
(20, 5, 'Desenvolvimento e Integrações', 'Implementamos os módulos, APIs e automações que conectam o sistema ao seu ecossistema digital.', 'storage/services/processes/service-sistema-process-desenvolvimento-e-integracoes-image-20251031-075510.png', 5, '2025-10-21 00:48:22', '2025-10-31 10:55:10'),
(21, 5, 'Validação e Treinamento', 'Realizamos testes práticos e treinamos a equipe para garantir uso eficiente e autônomo.', 'storage/services/processes/service-sistema-process-validacao-e-treinamento-image-20251031-075510.png', 6, '2025-10-21 00:48:22', '2025-10-31 10:55:10'),
(22, 5, 'Monitoramento e Evolução', 'Acompanhamos métricas e feedbacks para aprimorar recursos e garantir performance contínua.', 'storage/services/processes/service-sistema-process-monitoramento-e-evolucao-image-20251031-075511.png', 7, '2025-10-21 00:48:22', '2025-10-31 10:55:11'),
(23, 6, 'Descoberta e Validação', 'Entendemos o propósito do produto, público e diferenciais para validar a proposta de valor do SaaS.', 'storage/services/processes/service-saas-process-descoberta-e-validacao-image-20251031-075619.png', 1, '2025-10-21 00:48:22', '2025-10-31 10:56:19'),
(24, 6, 'Mapeamento de Fluxos', 'Desenhamos as jornadas de usuário e o fluxo de dados para definir uma experiência clara e funcional.', 'storage/services/processes/service-saas-process-mapeamento-de-fluxos-image-20251031-075619.png', 2, '2025-10-21 00:48:22', '2025-10-31 10:56:19'),
(25, 6, 'Modelagem do Produto', 'Estruturamos módulos, planos, níveis de acesso e estratégias de monetização do sistema SaaS.', 'storage/services/processes/service-saas-process-modelagem-do-produto-image-20251031-075619.png', 3, '2025-10-21 00:48:22', '2025-10-31 10:56:19'),
(26, 6, 'Arquitetura Técnica', 'Definimos o ambiente multitenant, banco de dados e integrações que sustentam o funcionamento do SaaS.', 'storage/services/processes/service-saas-process-arquitetura-tecnica-image-20251031-075620.png', 4, '2025-10-21 00:48:22', '2025-10-31 10:56:20'),
(27, 6, 'Design de Interface', 'Criamos uma interface intuitiva e responsiva que une estética, usabilidade e conversão para o usuário final.', 'storage/services/processes/service-saas-process-design-de-interface-image-20251031-075620.png', 5, '2025-10-21 00:48:22', '2025-10-31 10:56:20'),
(28, 6, 'Desenvolvimento e Integrações', 'Implementamos funcionalidades, APIs e automações que conectam o sistema a serviços externos e internos.', 'storage/services/processes/service-saas-process-desenvolvimento-e-integracoes-image-20251031-075621.png', 6, '2025-10-21 00:48:22', '2025-10-31 10:56:21'),
(29, 6, 'Testes e Segurança', 'Realizamos testes de performance, autenticação e integridade para garantir confiabilidade total da plataforma.', 'storage/services/processes/service-saas-process-testes-e-seguranca-image-20251031-075621.png', 7, '2025-10-21 00:48:22', '2025-10-31 10:56:21'),
(30, 6, 'Deploy e Onboarding', 'Publicamos o sistema, configuramos o ambiente em nuvem e criamos um onboarding otimizado para novos usuários.', 'storage/services/processes/service-saas-process-deploy-e-onboarding-image-20251031-075622.png', 8, '2025-10-21 00:48:22', '2025-10-31 10:56:22'),
(31, 6, 'Evolução Contínua', 'Monitoramos métricas, feedbacks e uso real para aprimorar recursos, desempenho e novas integrações.', 'storage/services/processes/service-saas-process-evolucao-continua-image-20251031-075622.png', 9, '2025-10-21 00:48:22', '2025-10-31 10:56:22'),
(33, 1, 'Imersão Estratégica', 'Entendemos o seu produto, público e objetivos para definir uma direção clara e eficaz de comunicação.', 'storage/services/processes/service-landing-process-imersao-estrategica-image-20251031-074203.png', 1, '2025-10-27 19:20:29', '2025-10-31 10:42:03');

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('JSKirsDawoYBiY5m0zUWH5oxACoMcLSDg4QvZ3fd', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUnE2TDBOcENvN2ZoVWJ5TFB5bmY0TThLRExkM1Z0TDJXUXEzcGJUaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9tbWNyaWF0aXZvcy50ZXN0L2Rhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1762532080);

-- --------------------------------------------------------

--
-- Estrutura para tabela `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `address_street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_complement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_neighborhood` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_zipcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_support` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_commercial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `x` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `behance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dribbble` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `settings`
--

INSERT INTO `settings` (`id`, `address_street`, `address_number`, `address_complement`, `address_neighborhood`, `address_city`, `address_state`, `address_country`, `address_zipcode`, `phone`, `email_support`, `email_contact`, `email_commercial`, `facebook`, `instagram`, `tiktok`, `x`, `linkedin`, `youtube`, `behance`, `dribbble`, `github`, `whatsapp`, `created_at`, `updated_at`) VALUES
(1, 'Rua Doutor Oscar Monteiro de Barros', '477', 'Apto 154', 'Vila Suzana', 'São Paulo', 'SP', 'Brasil', '05641010', '11958469546', 'suporte@mmcriativos.com.br', 'contato@mmcriativos.com.br', 'comercial@mmcriativos.com.br', 'https://www.facebook.com/mmcriativos/?locale=pt_BR', 'https://www.instagram.com/mmcriativos/', NULL, NULL, 'https://www.linkedin.com/company/109986044/admin/dashboard/', NULL, 'https://www.behance.net/mmcriativos', NULL, 'https://github.com/MM-Criativos', 'https://wa.me/5511958469546', '2025-10-22 01:47:52', '2025-11-03 22:23:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `skills`
--

CREATE TABLE `skills` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `thumb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `skills`
--

INSERT INTO `skills` (`id`, `name`, `slug`, `icon`, `description`, `thumb`, `cover`, `created_at`, `updated_at`) VALUES
(1, 'Layout e Interface', 'frontend', 'fa-light fa-code', 'Interfaces modernas e responsivas que destacam sua marca e encantam o usuário.', 'storage/skills/skill-frontend-thumb-20251031-080124.jpg', 'storage/skills/i8AtfRefNJbOUIXTWfDq7yMz4oM5dUSikK0oO2tn.mp4', '2025-10-21 00:48:22', '2025-10-31 11:01:25'),
(2, 'Estrutura e Lógica', 'backend', 'fa-light fa-database', 'Códigos limpos e estáveis garantem performance, segurança e escalabilidade.', 'storage/skills/skill-backend-thumb-20251031-080210.jpg', 'storage/skills/LbBYdX0RvCJ8h03JRzBvk2ArmYWFnaYR8nvmKAcx.mp4', '2025-10-21 00:48:22', '2025-10-31 11:02:10'),
(3, 'Experiência do Usuário', 'uxui', 'fa-light fa-bezier-curve', 'Estética e funcionalidade unidas para uma navegação fluida e envolvente.', 'storage/skills/skill-uxui-thumb-20251031-080310.jpg', 'storage/skills/ziOS7Wpx8754Cupik7LGmFCYhtFxo9qVlQJsKeRO.mp4', '2025-10-21 00:48:22', '2025-10-31 11:03:11'),
(4, 'SEO e Performance', 'seo', 'fa-light fa-rocket', 'Sites otimizados para carregamento rápido e recursos para melhor o SEO do google.', 'storage/skills/skill-seo-thumb-20251031-080348.jpg', 'storage/skills/r5DAAl1qZfW6jbVPrUiVUZht022kr8RXe2snxudu.mp4', '2025-10-21 00:48:22', '2025-10-31 11:03:48'),
(5, 'Automatização e IA', 'automacao', 'fa-light fa-robot', 'Conectamos ferramentas para otimizar rotinas e impulsionar resultados.', 'storage/skills/skill-automacao-thumb-20251031-080426.jpg', 'storage/skills/exCiIhRl0rETbhGvd6yTTjmz7pTRFaCCoPG4Qz4O.mp4', '2025-10-21 00:48:22', '2025-10-31 11:04:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `skill_competencies`
--

CREATE TABLE `skill_competencies` (
  `id` bigint UNSIGNED NOT NULL,
  `skill_id` bigint UNSIGNED NOT NULL,
  `competency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `skill_competencies`
--

INSERT INTO `skill_competencies` (`id`, `skill_id`, `competency`, `icon`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'HTML5', 'fa-brands fa-html5', 'Estrutura base de todo site moderno, o HTML5 organiza o conteúdo de forma semântica e otimizada, garantindo legibilidade e melhor indexação em buscadores.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(2, 1, 'CSS3', 'fa-brands fa-css3-alt', 'Define o estilo, cores e tipografia de cada projeto. O CSS3 garante responsividade e identidade visual coesa em qualquer resolução de tela.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(3, 1, 'JavaScript (ES6+)', 'fa-brands fa-js', 'Responsável pela interatividade e dinamismo da página. O JavaScript moderno traz vida aos elementos e melhora significativamente a experiência do usuário.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(4, 1, 'Vue.js', 'fa-brands fa-vuejs', 'Framework progressivo utilizado para construir interfaces reativas e modulares. O Vue.js oferece leveza, escalabilidade e fácil manutenção em projetos web complexos.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(5, 1, 'React', 'fa-brands fa-react', 'Biblioteca JavaScript voltada para criação de componentes reutilizáveis. O React simplifica o desenvolvimento de interfaces dinâmicas com excelente desempenho.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(6, 1, 'Tailwind CSS', 'fa-solid fa-wind', 'Framework utilitário que acelera o design front-end. O TailwindCSS permite construir layouts responsivos e consistentes com código limpo e modular.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(7, 1, 'Bootstrap', 'fa-brands fa-bootstrap', 'Biblioteca clássica para prototipagem e interfaces rápidas. O Bootstrap oferece componentes prontos e responsivos que agilizam o desenvolvimento visual.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(8, 1, 'Design Responsivo', 'fa-solid fa-display', 'Planeja e adapta o design a diferentes dispositivos, resoluções e contextos. O Responsive Design garante acessibilidade e usabilidade em qualquer tela.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(9, 1, 'Acessibilidade (WCAG)', 'fa-solid fa-universal-access', 'Conjunto de diretrizes que garante acessibilidade digital. Aplicamos os padrões WCAG para interfaces inclusivas, legíveis e navegáveis por todos.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(10, 1, 'Animações (CSS / GSAP)', 'fa-solid fa-bolt', 'Aplicação de transições suaves e micro-animações em CSS e GSAP. As animações aprimoram a percepção de fluidez e a interação com a interface.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(11, 2, 'PHP (Laravel, Lumen)', 'fa-brands fa-php', 'Frameworks PHP como Laravel e Lumen fornecem a base sólida para sistemas robustos, seguros e escaláveis, com código limpo e de fácil manutenção.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(12, 2, 'Node.js / Express.js', 'fa-brands fa-node-js', 'Ambiente moderno para APIs e serviços escaláveis. O Node.js com Express.js garante performance, modularidade e comunicação eficiente entre sistemas.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(13, 2, 'APIs RESTful', 'fa-solid fa-link', 'APIs RESTful estruturam a comunicação entre front-end e back-end, garantindo integração limpa, segura e independente de plataforma ou linguagem.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(14, 2, 'Autenticação (Sanctum, JWT)', 'fa-solid fa-user-shield', 'Autenticação segura e confiável através de Sanctum e JWT. Protege dados e controla acessos em ambientes com múltiplos níveis de permissão.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(15, 2, 'Eloquent ORM', 'fa-solid fa-database', 'Camada de abstração que facilita o uso de bancos relacionais. O Eloquent ORM simplifica consultas, relacionamentos e manipulação de dados.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(16, 2, 'Filas e Agendadores', 'fa-solid fa-clock', 'Gerencia processos automáticos e tarefas recorrentes. Filas e agendadores otimizam performance e mantêm o sistema sempre responsivo.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(17, 2, 'Upload e Armazenamento de Arquivos', 'fa-solid fa-upload', 'Gerencia o envio, armazenamento e versionamento de arquivos com segurança, integrando-se a drivers locais e em nuvem para maior flexibilidade.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(18, 2, 'Migrações e Seeders de Banco de Dados', 'fa-solid fa-code-branch', 'Controla estrutura e versionamento do banco de dados. Migrations e Seeders garantem consistência e rastreabilidade em qualquer ambiente.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(19, 2, 'Integração com APIs Externas', 'fa-solid fa-plug', 'Cria pontes entre plataformas externas e o sistema. Integrações via API conectam serviços, ampliando as funcionalidades do projeto.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(20, 2, 'Boas Práticas de Segurança (OWASP)', 'fa-solid fa-shield-halved', 'Implementa boas práticas de segurança OWASP para proteger o sistema contra vulnerabilidades, injeções e ataques de autenticação.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(21, 3, 'Design Systems', 'fa-solid fa-layer-group', 'Conjunto de padrões visuais e componentes reutilizáveis. Um Design System mantém consistência e agilidade no desenvolvimento de interfaces.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(22, 3, 'Figma', 'fa-brands fa-figma', 'Ferramenta colaborativa para design de interfaces digitais. O Figma facilita prototipagem, validação visual e integração com equipes multidisciplinares.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(23, 3, 'Prototipagem e Wireframing', 'fa-solid fa-vector-square', 'Processo de esboço e prototipagem de fluxos antes da codificação. Permite validar ideias e aprimorar a experiência do usuário rapidamente.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(24, 3, 'Arquitetura da Informação', 'fa-solid fa-diagram-project', 'Organiza o conteúdo e define hierarquias de navegação. A arquitetura da informação cria jornadas lógicas e intuitivas dentro do produto.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(25, 3, 'Hierarquia Visual', 'fa-solid fa-sitemap', 'Define pesos visuais, contrastes e foco de atenção. A hierarquia visual conduz o olhar do usuário e melhora a compreensão da interface.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(26, 3, 'Design de Acessibilidade', 'fa-solid fa-eye', 'Inclui princípios de design acessível para garantir usabilidade por todos os públicos, respeitando limitações visuais, motoras e cognitivas.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(27, 3, 'Tipografia e Teoria das Cores', 'fa-solid fa-palette', 'Combinações equilibradas de tipografia e cores definem a identidade e a legibilidade, criando harmonia estética e clareza em cada tela.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(28, 3, 'Microinterações', 'fa-solid fa-wand-magic-sparkles', 'Micro-animações e feedbacks visuais refinam a interação, tornando o uso mais intuitivo e satisfatório para o usuário final.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(29, 3, 'Design de Interface Responsiva', 'fa-solid fa-display', 'Cria interfaces fluidas e adaptáveis a múltiplos dispositivos. O design responsivo garante consistência visual e usabilidade contínua.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(30, 3, 'Testes de Usabilidade', 'fa-solid fa-flask', 'Etapa essencial de validação de usabilidade. Testes reais avaliam navegação, clareza e eficiência da experiência proposta.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(31, 4, 'HTML Semântico', 'fa-solid fa-code', 'Estrutura semântica e bem organizada que melhora a indexação e acessibilidade, tornando o conteúdo compreensível para buscadores e leitores de tela.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(32, 4, 'Meta Tags e Snippets Enriquecidos', 'fa-solid fa-tags', 'Otimização de metadados e snippets enriquecidos para ampliar a visibilidade do site e melhorar o desempenho nas buscas orgânicas.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(33, 4, 'Sitemap e Robots.txt', 'fa-solid fa-map', 'Criação e manutenção de arquivos Sitemap e Robots.txt, orientando mecanismos de busca na navegação e indexação eficiente do conteúdo.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(34, 4, 'Otimização de Core Web Vitals', 'fa-solid fa-gauge-high', 'Aprimoramento dos Core Web Vitals com foco em velocidade, estabilidade visual e interatividade, elevando métricas de experiência do usuário.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(35, 4, 'Lazy Loading e Otimização de Recursos', 'fa-solid fa-rocket', 'Carregamento inteligente de imagens e scripts com Lazy Loading e compressão de ativos para desempenho superior em qualquer rede.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(36, 4, 'Estratégias de Cache (HTTP, Laravel)', 'fa-solid fa-box-archive', 'Uso de cache HTTP e de aplicação para reduzir requisições e acelerar o carregamento de páginas em visitas subsequentes.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(37, 4, 'Implementação Técnica de SEO', 'fa-solid fa-magnifying-glass-chart', 'Implementação técnica de SEO, estruturando títulos, descrições e links para maximizar o alcance orgânico e a relevância de conteúdo.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(38, 4, 'Otimização de Links Internos', 'fa-solid fa-link', 'Estratégia de links internos que reforça hierarquia e relevância das páginas, aprimorando o rastreamento por mecanismos de busca.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(39, 4, 'Análise de PageSpeed Insights', 'fa-solid fa-chart-line', 'Análise contínua de desempenho via PageSpeed Insights para identificar gargalos e aplicar melhorias técnicas mensuráveis.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(40, 4, 'Google Analytics e Search Console', 'fa-brands fa-google', 'Monitoramento de tráfego e comportamento do usuário por meio das ferramentas Google Analytics e Search Console, baseando decisões em dados reais.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(41, 5, 'Automação com n8n', 'fa-solid fa-network-wired', 'Criação de fluxos automatizados e integrações complexas com o n8n, conectando serviços para otimizar tarefas repetitivas e aumentar eficiência operacional.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(42, 5, 'Zapier e Make (Integromat)', 'fa-solid fa-diagram-next', 'Uso de plataformas Zapier e Make para automatizar processos entre aplicações, reduzindo erros humanos e acelerando rotinas de trabalho.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(43, 5, 'Integração via Webhooks', 'fa-solid fa-plug', 'Integração via Webhooks que conecta sistemas em tempo real, garantindo comunicação rápida e sincronizada entre plataformas diferentes.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(44, 5, 'Geração de Conteúdo com IA', 'fa-solid fa-robot', 'Aplicação de inteligência artificial para gerar textos e ideias criativas com base em contexto, tom e intenção definidos pelo projeto.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(45, 5, 'Engenharia de Prompts', 'fa-solid fa-terminal', 'Engenharia de prompts que aprimora a comunicação com modelos de IA, garantindo respostas precisas e alinhadas ao objetivo do usuário.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(46, 5, 'Classificação e Moderação de Texto', 'fa-solid fa-filter', 'Classificação automática e moderação de conteúdo textual, garantindo segurança, organização e conformidade com políticas de uso.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(47, 5, 'Integrações com APIs de IA', 'fa-solid fa-code-merge', 'Integrações com APIs de IA como OpenAI e HuggingFace, expandindo funcionalidades e conectando modelos avançados ao sistema.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(48, 5, 'Integrações SaaS', 'fa-solid fa-cloud', 'Conexão entre diferentes serviços SaaS, integrando sistemas e automatizando fluxos que otimizam tempo e reduzem custos operacionais.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(49, 5, 'Sistemas de Automação de Fluxo', 'fa-solid fa-gears', 'Sistemas de automação que interligam processos e plataformas, garantindo continuidade operacional e redução de tarefas manuais.', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(50, 5, 'Treinamento Básico de Modelos', 'fa-solid fa-brain', 'Treinamento básico de modelos customizados para IA, adaptando comportamentos e resultados conforme as necessidades do projeto.', '2025-10-21 00:48:22', '2025-10-21 00:48:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `skill_infos`
--

CREATE TABLE `skill_infos` (
  `id` bigint UNSIGNED NOT NULL,
  `skill_id` bigint UNSIGNED NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `skill_infos`
--

INSERT INTO `skill_infos` (`id`, `skill_id`, `image`, `title`, `subtitle`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'storage/skills/30c0c963-3641-42da-a8c7-b616452f5b7d-20251031-080458.png', 'Transformando ideias em experiências visuais', 'A camada visível do digital', 'O front-end é o ponto onde o design ganha vida e o conceito se torna tangível. É o espaço onde a imaginação se transforma em forma, cor e movimento, criando interfaces reais, intuitivas e responsivas.\r\n\r\nCada linha de código, cada transição e cada microanimação são cuidadosamente planejadas para transmitir propósito e emoção.\r\n\r\nMais do que estética, o front-end é sobre experiência, ele define como as pessoas interagem com a marca, como percebem o valor do conteúdo e como navegam entre o visual e o funcional de forma fluida.\r\n\r\nÉ nesse equilíbrio entre beleza e usabilidade que nasce a verdadeira identidade digital de um projeto.', '2025-10-28 02:35:55', '2025-10-31 11:04:58'),
(2, 2, 'storage/skills/2bf3d3db-0214-4aa7-a974-3c651aadf6e4-20251031-080517.png', 'A base invisível que mantém tudo em movimento', 'O núcleo que sustenta o digital', 'Enquanto o front-end dá forma à experiência, o back-end é o que a torna possível.\r\nÉ aqui que dados se organizam, lógicas se conectam e cada ação ganha propósito.\r\nEssa camada invisível garante que tudo funcione como deve: processando informações, controlando fluxos e mantendo o sistema estável, seguro e eficiente.\r\n\r\nCada linha de código no back-end carrega responsabilidade, ela define como o site pensa, responde e se adapta.\r\nÉ o elo que conecta o usuário à informação, transformando cliques em resultados e interações em valor.\r\n\r\nMais do que infraestrutura, o back-end é o coração pulsante do projeto.\r\nSem ele, nada se move. Com ele, o digital respira com inteligência, consistência e poder.', '2025-10-28 03:20:00', '2025-10-31 11:05:17'),
(3, 3, 'storage/skills/7b1d32a8-6988-435e-aba2-ad051ca39ff6-20251031-080556.png', 'Projetando jornadas que unem emoção e funcionalidade', 'Entender pessoas, criar sentido', 'A experiência do usuário é o ponto onde tecnologia e empatia se encontram.\r\n\r\nÉ o estudo de como as pessoas pensam, sentem e se comportam ao interagir com o digital, e como transformar isso em caminhos intuitivos e agradáveis.\r\n\r\nCada fluxo, botão e microinteração é resultado de observação e propósito.\r\nTudo é planejado para guiar o usuário sem esforço, mantendo o equilíbrio entre estética e eficiência.\r\nA boa experiência não se impõe: ela se revela de forma natural, tornando o uso tão fluido que o design quase desaparece.\r\n\r\nMais do que criar telas bonitas, UX e UI moldam conexões humanas.\r\nSão elas que transformam um simples clique em confiança, uma navegação em descoberta e um site em algo memorável.', '2025-10-28 05:23:45', '2025-10-31 11:05:56'),
(4, 4, 'storage/skills/70470a3f-06b3-4ca4-9ee4-66d9e8365bd1-20251031-080620.png', 'Otimização inteligente para resultados que aparecem', 'Velocidade, alcance e relevância', 'SEO e performance são o motor invisível que impulsiona um site rumo à visibilidade e ao sucesso.\r\nNão basta ter um projeto bonito, ele precisa ser encontrado, rápido e eficiente.\r\nÉ aqui que a técnica se alia à estratégia para garantir que cada página carregue com agilidade, cada palavra tenha propósito e cada detalhe conte pontos nos mecanismos de busca.\r\n\r\nPor trás das métricas, há engenharia: estrutura limpa, código otimizado e experiência fluida em qualquer dispositivo.\r\nCada segundo economizado, cada clique conquistado e cada posição alcançada refletem o cuidado com o desempenho.\r\n\r\nMais do que posicionamento, SEO e performance são sobre credibilidade.\r\nEles constroem confiança, fortalecem a presença digital e fazem a diferença entre ser visto, ou ser esquecido.', '2025-10-28 05:35:55', '2025-10-31 11:06:20'),
(5, 5, 'storage/skills/349fb934-048e-433d-8409-95b5945dacb2-20251031-080442.png', 'Quando o código aprende, o digital evolui', 'O futuro que trabalha ao seu lado', 'A automatização e a inteligência artificial transformam o modo como criamos, otimizamos e escalamos soluções.\r\nSão sistemas que observam, aprendem e executam tarefas complexas com precisão e velocidade, liberando tempo para o que realmente importa: a criatividade e a estratégia.\r\n\r\nPor trás de cada processo automatizado há lógica, integração e aprendizado contínuo.\r\nSão fluxos inteligentes que analisam padrões, antecipam demandas e mantêm o funcionamento do digital em ritmo constante, sem falhas e sem pausas.\r\n\r\nA IA não substitui o humano, ela amplia suas possibilidades.\r\nÉ a ponte entre eficiência e inovação, entre dados e decisão, entre o presente e o que vem depois.\r\n\r\nAutomatização e IA são o estágio em que o digital deixa de reagir e começa a pensar.\r\nE é nesse ponto que a tecnologia se torna realmente viva.', '2025-10-28 05:39:35', '2025-10-31 11:04:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `sliders`
--

CREATE TABLE `sliders` (
  `id` bigint UNSIGNED NOT NULL,
  `video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `sliders`
--

INSERT INTO `sliders` (`id`, `video`, `text_1`, `text_2`, `text_3`, `created_at`, `updated_at`) VALUES
(1, 'storage/layout/dNDFJBzDu48OEacnIdUblAuF49vwypmmm0rXymn5.mp4', 'Transformamos ideias em presenças digitais de alto padrão.', 'Unimos design, tecnologia e propósito para criar experiências únicas.', 'Representando sua marca e elevando o seu negócio no digital.', '2025-10-31 12:17:39', '2025-10-31 13:05:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `social_medias`
--

CREATE TABLE `social_medias` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `social_medias`
--

INSERT INTO `social_medias` (`id`, `name`, `slug`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'Facebook', 'facebook', 'fa-brands fa-facebook-f', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(2, 'Instagram', 'instagram', 'fa-brands fa-instagram', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(3, 'TikTok', 'tiktok', 'fa-brands fa-tiktok', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(4, 'X', 'x', 'fa-brands fa-x-twitter', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(5, 'LinkedIn', 'linkedin', 'fa-brands fa-linkedin-in', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(6, 'YouTube', 'youtube', 'fa-brands fa-youtube', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(7, 'Behance', 'behance', 'fa-brands fa-behance', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(8, 'Dribbble', 'dribbble', 'fa-brands fa-dribbble', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(9, 'GitHub', 'github', 'fa-brands fa-github', '2025-10-21 00:48:22', '2025-10-21 00:48:22'),
(10, 'WhatsApp', 'whatsapp', 'fa-brands fa-whatsapp', '2025-10-21 00:48:22', '2025-10-21 00:48:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `social_media_user`
--

CREATE TABLE `social_media_user` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `social_media_id` bigint UNSIGNED NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `social_media_user`
--

INSERT INTO `social_media_user` (`id`, `user_id`, `social_media_id`, `url`, `created_at`, `updated_at`) VALUES
(1, 1, 9, 'https://github.com/MarcusMultini', '2025-11-03 02:04:37', '2025-11-03 04:59:04'),
(2, 1, 2, 'https://www.instagram.com/marcusvmultini/', '2025-11-03 02:04:37', '2025-11-03 04:59:04'),
(3, 1, 5, 'https://www.linkedin.com/in/marcus-vinicius-multini-869632206/', '2025-11-03 02:04:37', '2025-11-03 04:59:04'),
(4, 1, 10, 'https://wa.me/5511958469546', '2025-11-03 02:16:51', '2025-11-03 04:59:04');

-- --------------------------------------------------------

--
-- Estrutura para tabela `storytelling_components`
--

CREATE TABLE `storytelling_components` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `layer` enum('identidade','proposito','prova_de_valor','validacao','conversao') COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `props` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `storytelling_components`
--

INSERT INTO `storytelling_components` (`id`, `name`, `slug`, `layer`, `component_type`, `description`, `props`, `created_at`, `updated_at`) VALUES
(1, 'Hero Header', 'hero-header', 'identidade', 'blade', 'Bloco principal de abertura, com vídeo ou imagem de destaque, título e chamada inicial.', '{\"title\": \"Transformamos ideias em código\", \"cta_text\": \"Começar projeto\", \"subtitle\": \"Design, tecnologia e estratégia reunidos para impulsionar seu negócio.\"}', NULL, NULL),
(2, 'Intro Manifesto', 'intro-manifesto', 'identidade', 'blade', 'Apresenta a essência da marca em uma frase de impacto com breve texto de apoio.', '{\"text\": \"A MM Criativos acredita em transformar visão em presença digital com propósito.\", \"headline\": \"Criar é a nossa forma de viver.\"}', NULL, NULL),
(3, 'Logo Showcase', 'logo-showcase', 'identidade', 'vue', 'Carrossel animado com logotipos ou símbolos da marca.', '{\"logos\": []}', NULL, NULL),
(4, 'About Summary', 'about-summary', 'identidade', 'blade', 'Resumo da história ou missão da marca, usado em seções “Sobre nós”.', '{\"paragraph\": \"Fundada para unir estética e performance em cada linha de código.\"}', NULL, NULL),
(5, 'Value Highlights', 'value-highlights', 'identidade', 'vue', 'Apresenta valores centrais em ícones e palavras-chave.', '{\"values\": [\"Inovação\", \"Transparência\", \"Excelência\"]}', NULL, NULL),
(6, 'Mission Statement', 'mission-statement', 'proposito', 'blade', 'Bloco textual apresentando missão e visão da empresa.', '{\"vision\": \"Ser referência em soluções digitais criativas e escaláveis.\", \"mission\": \"Transformar criatividade em resultados tangíveis.\"}', NULL, NULL),
(7, 'Timeline Story', 'timeline-story', 'proposito', 'vue', 'Linha do tempo mostrando marcos e evolução da marca.', '{\"events\": []}', NULL, NULL),
(8, 'Founder Quote', 'founder-quote', 'proposito', 'blade', 'Citação inspiradora com foto e nome do fundador ou líder da marca.', '{\"quote\": \"Ideias só ganham vida quando encontram propósito.\", \"author\": \"Marcus Multini\"}', NULL, NULL),
(9, 'Purpose Grid', 'purpose-grid', 'proposito', 'vue', 'Grade visual que representa pilares do propósito da empresa.', '{\"items\": [\"Criar\", \"Conectar\", \"Evoluir\"]}', NULL, NULL),
(10, 'Service Grid', 'service-grid', 'prova_de_valor', 'vue', 'Grade responsiva exibindo serviços com ícones e descrições curtas.', '{\"columns\": 3}', NULL, NULL),
(11, 'Feature List', 'feature-list', 'prova_de_valor', 'blade', 'Lista de recursos e funcionalidades principais com ícones.', '{\"features\": []}', NULL, NULL),
(12, 'Comparison Table', 'comparison-table', 'prova_de_valor', 'vue', 'Tabela comparativa entre planos, serviços ou produtos.', '{\"columns\": [\"Básico\", \"Pro\", \"Premium\"]}', NULL, NULL),
(13, 'Interactive Demo', 'interactive-demo', 'prova_de_valor', 'vue', 'Bloco interativo demonstrando produto ou sistema em ação.', '{\"demo_url\": null}', NULL, NULL),
(14, 'Tech Stack', 'tech-stack', 'prova_de_valor', 'blade', 'Mostra tecnologias usadas nos projetos (ícones animados ou grade).', '{\"stacks\": [\"Laravel\", \"Vue\", \"Tailwind\", \"GSAP\"]}', NULL, NULL),
(15, 'Case Showcase', 'case-showcase', 'validacao', 'blade', 'Exibe cases e projetos realizados com resultados mensuráveis.', '{\"limit\": 6}', NULL, NULL),
(16, 'Testimonial Slider', 'testimonial-slider', 'validacao', 'vue', 'Carrossel de depoimentos com nome, foto e citação de clientes.', '{\"autoplay\": true}', NULL, NULL),
(17, 'Client Logos', 'client-logos', 'validacao', 'blade', 'Apresenta logos de clientes e parceiros em carrossel horizontal.', '{\"count\": 10}', NULL, NULL),
(18, 'Metrics Counter', 'metrics-counter', 'validacao', 'vue', 'Bloco de estatísticas animadas, como número de projetos e clientes.', '{\"metrics\": {\"Clientes\": 80, \"Projetos\": 120}}', NULL, NULL),
(19, 'Awards Gallery', 'awards-gallery', 'validacao', 'blade', 'Galeria de prêmios, selos e certificações recebidas.', '{\"awards\": []}', NULL, NULL),
(20, 'CTA Block', 'cta-block', 'conversao', 'blade', 'Bloco de chamada à ação com botão e frase persuasiva.', '{\"title\": \"Pronto para começar?\", \"button_text\": \"Solicitar orçamento\"}', NULL, NULL),
(21, 'Contact Form', 'contact-form', 'conversao', 'blade', 'Formulário de contato com campos básicos.', '{\"fields\": [\"nome\", \"email\", \"mensagem\"]}', NULL, NULL),
(22, 'Newsletter Signup', 'newsletter-signup', 'conversao', 'vue', 'Bloco para captura de leads com campo de e-mail e botão de inscrição.', '{\"placeholder\": \"Seu e-mail\"}', NULL, NULL),
(23, 'Pricing CTA', 'pricing-cta', 'conversao', 'blade', 'Chamada à ação dentro da página de preços ou planos.', '{\"cta_text\": \"Escolha o plano ideal e comece agora.\"}', NULL, NULL),
(24, 'FAQ Accordion', 'faq-accordion', 'conversao', 'vue', 'Seção de perguntas frequentes com interação de abrir e fechar.', '{\"items\": []}', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cargo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `cargo`, `description`, `photo`, `email`, `role`, `is_approved`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Marcus Multini', 'Fundador e Desenvolver Criativo', 'Unindo design, código e propósito, desenvolvo experiências digitais que transformam ideias em presença real.\r\n\r\nÀ frente da MM Criativos, crio interfaces, sistemas e identidades que conectam estética, funcionalidade e inovação.', 'storage/users/user-marcus-multini-photo-20251102-232122.jpg', 'marcusmultini@gmail.com', 'admin', 1, NULL, '$2y$12$Xouo6t8HQPlTrcFt3pRXSOs8JHnLXEpgW.KRlTpx6vLDQO5NjcGsa', 'BIKFfeDZFm12uKuQ3CmRO2lmtrKGvTuRvSqBtThDhgI2qqF2OqcdqqhcbTpO', '2025-10-21 00:49:57', '2025-11-03 02:21:22');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `aboutus`
--
ALTER TABLE `aboutus`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `budgets_public_token_unique` (`public_token`),
  ADD KEY `budgets_service_id_foreign` (`service_id`),
  ADD KEY `budgets_plan_id_foreign` (`plan_id`),
  ADD KEY `budgets_client_id_foreign` (`client_id`);

--
-- Índices de tabela `budget_events`
--
ALTER TABLE `budget_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_events_budget_id_foreign` (`budget_id`);

--
-- Índices de tabela `budget_items`
--
ALTER TABLE `budget_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_items_budget_id_foreign` (`budget_id`);

--
-- Índices de tabela `budget_payments`
--
ALTER TABLE `budget_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_payments_budget_id_foreign` (`budget_id`);

--
-- Índices de tabela `business_hours`
--
ALTER TABLE `business_hours`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Índices de tabela `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Índices de tabela `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `class_user`
--
ALTER TABLE `class_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_user_user_id_class_id_unique` (`user_id`,`class_id`),
  ADD KEY `class_user_class_id_foreign` (`class_id`);

--
-- Índices de tabela `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_slug_unique` (`slug`);

--
-- Índices de tabela `clients_info`
--
ALTER TABLE `clients_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clients_info_client_id_foreign` (`client_id`);

--
-- Índices de tabela `client_social_media`
--
ALTER TABLE `client_social_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_social_media_client_id_foreign` (`client_id`),
  ADD KEY `client_social_media_social_media_id_foreign` (`social_media_id`);

--
-- Índices de tabela `client_testimonials`
--
ALTER TABLE `client_testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_testimonials_client_id_foreign` (`client_id`),
  ADD KEY `client_testimonials_contact_id_foreign` (`contact_id`);

--
-- Índices de tabela `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_client_id_foreign` (`client_id`);

--
-- Índices de tabela `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_templates_key_unique` (`key`);

--
-- Índices de tabela `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exchange_rates_currency_fetched_at_index` (`currency`,`fetched_at`);

--
-- Índices de tabela `extras`
--
ALTER TABLE `extras`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `extra_service`
--
ALTER TABLE `extra_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `extra_service_extra_id_foreign` (`extra_id`),
  ADD KEY `extra_service_service_id_foreign` (`service_id`);

--
-- Índices de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices de tabela `global_pages`
--
ALTER TABLE `global_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `global_pages_slug_unique` (`slug`),
  ADD KEY `global_pages_service_id_foreign` (`service_id`);

--
-- Índices de tabela `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Índices de tabela `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `lines`
--
ALTER TABLE `lines`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices de tabela `planning_briefing_qualitatives`
--
ALTER TABLE `planning_briefing_qualitatives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planning_briefing_qualitatives_project_id_foreign` (`project_id`),
  ADD KEY `planning_briefing_qualitatives_client_id_foreign` (`client_id`),
  ADD KEY `planning_briefing_qualitatives_template_id_foreign` (`template_id`);

--
-- Índices de tabela `planning_briefing_qualitative_responses`
--
ALTER TABLE `planning_briefing_qualitative_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pbq_briefing_fk` (`briefing_id`),
  ADD KEY `planning_briefing_qualitative_responses_project_id_foreign` (`project_id`),
  ADD KEY `planning_briefing_qualitative_responses_client_id_foreign` (`client_id`),
  ADD KEY `planning_briefing_qualitative_responses_template_id_foreign` (`template_id`);

--
-- Índices de tabela `planning_briefing_reguas`
--
ALTER TABLE `planning_briefing_reguas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `planning_briefing_responses`
--
ALTER TABLE `planning_briefing_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planning_briefing_responses_project_id_foreign` (`project_id`),
  ADD KEY `planning_briefing_responses_client_id_foreign` (`client_id`),
  ADD KEY `planning_briefing_responses_briefing_regua_id_foreign` (`briefing_regua_id`);

--
-- Índices de tabela `planning_interpretacoes`
--
ALTER TABLE `planning_interpretacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planning_interpretacoes_project_id_foreign` (`project_id`),
  ADD KEY `planning_interpretacoes_client_id_foreign` (`client_id`);

--
-- Índices de tabela `planning_kickoffs`
--
ALTER TABLE `planning_kickoffs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planning_kickoffs_project_id_foreign` (`project_id`),
  ADD KEY `planning_kickoffs_client_id_foreign` (`client_id`);

--
-- Índices de tabela `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plans_service_id_foreign` (`service_id`);

--
-- Índices de tabela `plan_advantages`
--
ALTER TABLE `plan_advantages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_advantages_plan_id_foreign` (`plan_id`);

--
-- Índices de tabela `processes`
--
ALTER TABLE `processes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `processes_slug_unique` (`slug`);

--
-- Índices de tabela `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `projects_slug_unique` (`slug`),
  ADD UNIQUE KEY `projects_budget_id_unique` (`budget_id`),
  ADD KEY `projects_client_id_foreign` (`client_id`),
  ADD KEY `projects_service_id_foreign` (`service_id`);

--
-- Índices de tabela `project_challenges`
--
ALTER TABLE `project_challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_challenges_project_id_foreign` (`project_id`);

--
-- Índices de tabela `project_images`
--
ALTER TABLE `project_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_images_project_process_id_foreign` (`project_process_id`);

--
-- Índices de tabela `project_pages`
--
ALTER TABLE `project_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_pages_slug_unique` (`slug`),
  ADD KEY `project_pages_project_id_foreign` (`project_id`),
  ADD KEY `project_pages_global_page_id_foreign` (`global_page_id`);

--
-- Índices de tabela `project_page_component`
--
ALTER TABLE `project_page_component`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_page_component_project_page_id_foreign` (`project_page_id`),
  ADD KEY `project_page_component_component_id_foreign` (`component_id`),
  ADD KEY `project_page_component_global_component_id_foreign` (`global_component_id`);

--
-- Índices de tabela `project_plannings`
--
ALTER TABLE `project_plannings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_plannings_project_id_foreign` (`project_id`),
  ADD KEY `project_plannings_client_id_foreign` (`client_id`);

--
-- Índices de tabela `project_process`
--
ALTER TABLE `project_process`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_process_project_id_foreign` (`project_id`),
  ADD KEY `project_process_process_id_foreign` (`process_id`);

--
-- Índices de tabela `project_skill_competency`
--
ALTER TABLE `project_skill_competency`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_skill_competency_project_id_foreign` (`project_id`),
  ADD KEY `project_skill_competency_skill_id_foreign` (`skill_id`),
  ADD KEY `project_skill_competency_skill_competency_id_foreign` (`skill_competency_id`);

--
-- Índices de tabela `project_solutions`
--
ALTER TABLE `project_solutions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_solutions_project_id_foreign` (`project_id`);

--
-- Índices de tabela `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_tasks_project_id_foreign` (`project_id`),
  ADD KEY `project_tasks_skill_id_foreign` (`skill_id`),
  ADD KEY `project_tasks_skill_competency_id_foreign` (`skill_competency_id`),
  ADD KEY `fk_project_tasks_assigned_to_users` (`assigned_to`);

--
-- Índices de tabela `project_task_items`
--
ALTER TABLE `project_task_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_task_items_project_id_foreign` (`project_id`),
  ADD KEY `project_task_items_project_task_id_foreign` (`project_task_id`),
  ADD KEY `project_task_items_skill_id_foreign` (`skill_id`),
  ADD KEY `project_task_items_skill_competency_id_foreign` (`skill_competency_id`),
  ADD KEY `project_task_items_assigned_to_foreign` (`assigned_to`);

--
-- Índices de tabela `qualitative_templates`
--
ALTER TABLE `qualitative_templates`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_slug_unique` (`slug`);

--
-- Índices de tabela `service_benefits`
--
ALTER TABLE `service_benefits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_benefits_service_id_foreign` (`service_id`);

--
-- Índices de tabela `service_ctas`
--
ALTER TABLE `service_ctas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_ctas_service_id_foreign` (`service_id`);

--
-- Índices de tabela `service_features`
--
ALTER TABLE `service_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_features_service_id_foreign` (`service_id`);

--
-- Índices de tabela `service_infos`
--
ALTER TABLE `service_infos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_infos_service_id_foreign` (`service_id`);

--
-- Índices de tabela `service_processes`
--
ALTER TABLE `service_processes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_processes_service_id_foreign` (`service_id`);

--
-- Índices de tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Índices de tabela `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `skills_slug_unique` (`slug`);

--
-- Índices de tabela `skill_competencies`
--
ALTER TABLE `skill_competencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `skill_competencies_skill_id_foreign` (`skill_id`);

--
-- Índices de tabela `skill_infos`
--
ALTER TABLE `skill_infos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `skill_infos_skill_id_foreign` (`skill_id`);

--
-- Índices de tabela `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `social_medias`
--
ALTER TABLE `social_medias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `social_medias_slug_unique` (`slug`);

--
-- Índices de tabela `social_media_user`
--
ALTER TABLE `social_media_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `social_media_user_user_id_social_media_id_unique` (`user_id`,`social_media_id`),
  ADD KEY `social_media_user_social_media_id_foreign` (`social_media_id`);

--
-- Índices de tabela `storytelling_components`
--
ALTER TABLE `storytelling_components`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `storytelling_components_slug_unique` (`slug`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aboutus`
--
ALTER TABLE `aboutus`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `budget_events`
--
ALTER TABLE `budget_events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de tabela `budget_items`
--
ALTER TABLE `budget_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `budget_payments`
--
ALTER TABLE `budget_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `business_hours`
--
ALTER TABLE `business_hours`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `class_user`
--
ALTER TABLE `class_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `clients_info`
--
ALTER TABLE `clients_info`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `client_social_media`
--
ALTER TABLE `client_social_media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `client_testimonials`
--
ALTER TABLE `client_testimonials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `exchange_rates`
--
ALTER TABLE `exchange_rates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `extras`
--
ALTER TABLE `extras`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `extra_service`
--
ALTER TABLE `extra_service`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `global_pages`
--
ALTER TABLE `global_pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `lines`
--
ALTER TABLE `lines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de tabela `planning_briefing_qualitatives`
--
ALTER TABLE `planning_briefing_qualitatives`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `planning_briefing_qualitative_responses`
--
ALTER TABLE `planning_briefing_qualitative_responses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `planning_briefing_reguas`
--
ALTER TABLE `planning_briefing_reguas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `planning_briefing_responses`
--
ALTER TABLE `planning_briefing_responses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT de tabela `planning_interpretacoes`
--
ALTER TABLE `planning_interpretacoes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `planning_kickoffs`
--
ALTER TABLE `planning_kickoffs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `plan_advantages`
--
ALTER TABLE `plan_advantages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `processes`
--
ALTER TABLE `processes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `project_challenges`
--
ALTER TABLE `project_challenges`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `project_images`
--
ALTER TABLE `project_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de tabela `project_pages`
--
ALTER TABLE `project_pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `project_page_component`
--
ALTER TABLE `project_page_component`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `project_plannings`
--
ALTER TABLE `project_plannings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `project_process`
--
ALTER TABLE `project_process`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `project_skill_competency`
--
ALTER TABLE `project_skill_competency`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de tabela `project_solutions`
--
ALTER TABLE `project_solutions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `project_tasks`
--
ALTER TABLE `project_tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `project_task_items`
--
ALTER TABLE `project_task_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `qualitative_templates`
--
ALTER TABLE `qualitative_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT de tabela `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `service_benefits`
--
ALTER TABLE `service_benefits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `service_ctas`
--
ALTER TABLE `service_ctas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `service_features`
--
ALTER TABLE `service_features`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `service_infos`
--
ALTER TABLE `service_infos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `service_processes`
--
ALTER TABLE `service_processes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de tabela `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `skills`
--
ALTER TABLE `skills`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `skill_competencies`
--
ALTER TABLE `skill_competencies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `skill_infos`
--
ALTER TABLE `skill_infos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `social_medias`
--
ALTER TABLE `social_medias`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `social_media_user`
--
ALTER TABLE `social_media_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `storytelling_components`
--
ALTER TABLE `storytelling_components`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budgets_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budgets_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `budget_events`
--
ALTER TABLE `budget_events`
  ADD CONSTRAINT `budget_events_budget_id_foreign` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `budget_items`
--
ALTER TABLE `budget_items`
  ADD CONSTRAINT `budget_items_budget_id_foreign` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `budget_payments`
--
ALTER TABLE `budget_payments`
  ADD CONSTRAINT `budget_payments_budget_id_foreign` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `class_user`
--
ALTER TABLE `class_user`
  ADD CONSTRAINT `class_user_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `clients_info`
--
ALTER TABLE `clients_info`
  ADD CONSTRAINT `clients_info_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `client_social_media`
--
ALTER TABLE `client_social_media`
  ADD CONSTRAINT `client_social_media_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_social_media_social_media_id_foreign` FOREIGN KEY (`social_media_id`) REFERENCES `social_medias` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `client_testimonials`
--
ALTER TABLE `client_testimonials`
  ADD CONSTRAINT `client_testimonials_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_testimonials_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `extra_service`
--
ALTER TABLE `extra_service`
  ADD CONSTRAINT `extra_service_extra_id_foreign` FOREIGN KEY (`extra_id`) REFERENCES `extras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `extra_service_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `global_pages`
--
ALTER TABLE `global_pages`
  ADD CONSTRAINT `global_pages_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `planning_briefing_qualitatives`
--
ALTER TABLE `planning_briefing_qualitatives`
  ADD CONSTRAINT `planning_briefing_qualitatives_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `planning_briefing_qualitatives_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `planning_briefing_qualitatives_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `qualitative_templates` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `planning_briefing_qualitative_responses`
--
ALTER TABLE `planning_briefing_qualitative_responses`
  ADD CONSTRAINT `pbq_briefing_fk` FOREIGN KEY (`briefing_id`) REFERENCES `planning_briefing_qualitatives` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `planning_briefing_qualitative_responses_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `planning_briefing_qualitative_responses_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `planning_briefing_qualitative_responses_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `qualitative_templates` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `planning_briefing_responses`
--
ALTER TABLE `planning_briefing_responses`
  ADD CONSTRAINT `planning_briefing_responses_briefing_regua_id_foreign` FOREIGN KEY (`briefing_regua_id`) REFERENCES `planning_briefing_reguas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `planning_briefing_responses_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `planning_briefing_responses_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `planning_interpretacoes`
--
ALTER TABLE `planning_interpretacoes`
  ADD CONSTRAINT `planning_interpretacoes_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `planning_interpretacoes_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `planning_kickoffs`
--
ALTER TABLE `planning_kickoffs`
  ADD CONSTRAINT `planning_kickoffs_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `planning_kickoffs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `plans`
--
ALTER TABLE `plans`
  ADD CONSTRAINT `plans_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `plan_advantages`
--
ALTER TABLE `plan_advantages`
  ADD CONSTRAINT `plan_advantages_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_budget_id_foreign` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `project_challenges`
--
ALTER TABLE `project_challenges`
  ADD CONSTRAINT `project_challenges_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `project_images`
--
ALTER TABLE `project_images`
  ADD CONSTRAINT `project_images_project_process_id_foreign` FOREIGN KEY (`project_process_id`) REFERENCES `project_process` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `project_pages`
--
ALTER TABLE `project_pages`
  ADD CONSTRAINT `project_pages_global_page_id_foreign` FOREIGN KEY (`global_page_id`) REFERENCES `global_pages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_pages_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `project_page_component`
--
ALTER TABLE `project_page_component`
  ADD CONSTRAINT `project_page_component_component_id_foreign` FOREIGN KEY (`component_id`) REFERENCES `storytelling_components` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_page_component_global_component_id_foreign` FOREIGN KEY (`global_component_id`) REFERENCES `storytelling_components` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_page_component_project_page_id_foreign` FOREIGN KEY (`project_page_id`) REFERENCES `project_pages` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `project_plannings`
--
ALTER TABLE `project_plannings`
  ADD CONSTRAINT `project_plannings_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_plannings_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `project_process`
--
ALTER TABLE `project_process`
  ADD CONSTRAINT `project_process_process_id_foreign` FOREIGN KEY (`process_id`) REFERENCES `processes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_process_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `project_skill_competency`
--
ALTER TABLE `project_skill_competency`
  ADD CONSTRAINT `project_skill_competency_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_skill_competency_skill_competency_id_foreign` FOREIGN KEY (`skill_competency_id`) REFERENCES `skill_competencies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_skill_competency_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `project_solutions`
--
ALTER TABLE `project_solutions`
  ADD CONSTRAINT `project_solutions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD CONSTRAINT `fk_project_tasks_assigned_to_users` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_tasks_skill_competency_id_foreign` FOREIGN KEY (`skill_competency_id`) REFERENCES `skill_competencies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_tasks_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `project_task_items`
--
ALTER TABLE `project_task_items`
  ADD CONSTRAINT `project_task_items_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_task_items_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_task_items_project_task_id_foreign` FOREIGN KEY (`project_task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_task_items_skill_competency_id_foreign` FOREIGN KEY (`skill_competency_id`) REFERENCES `skill_competencies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_task_items_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `service_benefits`
--
ALTER TABLE `service_benefits`
  ADD CONSTRAINT `service_benefits_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `service_ctas`
--
ALTER TABLE `service_ctas`
  ADD CONSTRAINT `service_ctas_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `service_features`
--
ALTER TABLE `service_features`
  ADD CONSTRAINT `service_features_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `service_infos`
--
ALTER TABLE `service_infos`
  ADD CONSTRAINT `service_infos_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `service_processes`
--
ALTER TABLE `service_processes`
  ADD CONSTRAINT `service_processes_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `skill_competencies`
--
ALTER TABLE `skill_competencies`
  ADD CONSTRAINT `skill_competencies_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `skill_infos`
--
ALTER TABLE `skill_infos`
  ADD CONSTRAINT `skill_infos_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `social_media_user`
--
ALTER TABLE `social_media_user`
  ADD CONSTRAINT `social_media_user_social_media_id_foreign` FOREIGN KEY (`social_media_id`) REFERENCES `social_medias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `social_media_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
