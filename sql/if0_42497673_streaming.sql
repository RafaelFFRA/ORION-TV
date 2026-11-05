-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql101.infinityfree.com
-- Tempo de geração: 25/10/2026 às 15:25
-- Versão do servidor: 11.4.13-MariaDB
-- Versão do PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `if0_42497673_streaming`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administrador`
--

CREATE TABLE `administrador` (
  `id_administrador` int(11) NOT NULL COMMENT 'ID identificador do administrador',
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `nome_admin` varchar(100) NOT NULL COMMENT 'Nome do administrador',
  `FK_administrador_id_usuario` int(11) NOT NULL COMMENT 'ID identificador do usuário'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `administrador`
--

INSERT INTO `administrador` (`id_administrador`, `status`, `nome_admin`, `FK_administrador_id_usuario`) VALUES
(1, 'Inativo', 'Rafael', 1),
(3, 'Ativo', 'ORION TV', 29);

-- --------------------------------------------------------

--
-- Estrutura para tabela `assinatura`
--

CREATE TABLE `assinatura` (
  `id_assinatura` int(11) NOT NULL COMMENT 'ID identificador da assinatura',
  `tipo_plano_assinatura` enum('Anual','Mensal') NOT NULL COMMENT 'Tipo do plano de assinatura',
  `data_inicio_assinatura` date NOT NULL COMMENT 'Data de início da assinatura',
  `data_fim_assinatura` date NOT NULL COMMENT 'Data de fim da assinatura',
  `status_assinatura` enum('Ativa','Inativa','Vencida') NOT NULL COMMENT 'Status da Assinatura: Ativa; Inativa',
  `forma_pagamento_assinatura` enum('Cartão','Boleto','Pix') NOT NULL COMMENT 'Forma de pagamento da Assinatura: Cartão; Boleto; Pix',
  `FK_assinatura_id_cliente` int(11) NOT NULL COMMENT 'ID identificador do cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `assinatura`
--

INSERT INTO `assinatura` (`id_assinatura`, `tipo_plano_assinatura`, `data_inicio_assinatura`, `data_fim_assinatura`, `status_assinatura`, `forma_pagamento_assinatura`, `FK_assinatura_id_cliente`) VALUES
(1, 'Anual', '2026-08-23', '2027-08-23', 'Inativa', 'Pix', 1),
(2, 'Anual', '2026-08-31', '2027-08-31', 'Inativa', 'Pix', 2),
(3, 'Anual', '2026-08-31', '2027-08-31', 'Inativa', 'Pix', 3),
(6, 'Anual', '2026-09-07', '2027-09-07', 'Inativa', 'Pix', 6),
(8, 'Anual', '2026-09-08', '2027-09-08', 'Inativa', 'Boleto', 8),
(10, 'Anual', '2026-09-08', '2027-09-08', 'Inativa', 'Pix', 10),
(11, 'Anual', '2026-09-18', '2027-09-18', 'Inativa', 'Pix', 11),
(12, 'Anual', '2026-09-21', '2027-09-21', 'Ativa', 'Pix', 14),
(13, 'Anual', '2026-09-23', '2027-09-23', 'Inativa', 'Pix', 15),
(14, 'Mensal', '2026-09-24', '2026-10-24', 'Inativa', 'Pix', 16),
(15, 'Mensal', '2026-09-25', '2026-10-25', 'Inativa', 'Cartão', 17),
(16, 'Mensal', '2026-10-19', '2026-11-19', 'Inativa', 'Pix', 18),
(17, 'Mensal', '2026-10-20', '2026-11-20', 'Inativa', 'Pix', 19),
(18, 'Anual', '2026-10-21', '2027-10-21', 'Ativa', 'Cartão', 20);

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacao`
--

CREATE TABLE `avaliacao` (
  `id_avaliacao` int(11) NOT NULL COMMENT 'ID identificador da avaliação ',
  `comentario_avaliacao` varchar(500) DEFAULT NULL COMMENT 'Comentário da avaliação feita pelo cliente',
  `relevante_avaliacao` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Relevância da avaliação, 0: Irrelevante / 1: Relevante',
  `avaliacao_status` enum('Ativo','Inativo') NOT NULL COMMENT 'Status da avaliação',
  `FK_avaliacao_id_cliente` int(11) NOT NULL COMMENT 'ID identificador do cliente',
  `FK_avaliacao_id_conteudo` int(11) NOT NULL COMMENT 'ID identificador do conteudo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `avaliacao`
--

INSERT INTO `avaliacao` (`id_avaliacao`, `comentario_avaliacao`, `relevante_avaliacao`, `avaliacao_status`, `FK_avaliacao_id_cliente`, `FK_avaliacao_id_conteudo`) VALUES
(1, 'ola filme bao', 0, 'Ativo', 1, 8),
(2, 'muito bom esse filme, bom demais mesmo.', 0, 'Ativo', 1, 6),
(3, 'EU acho que o filme faltou o Addam Sanddler', 0, 'Ativo', 8, 6),
(5, 'Quati ou Quaiaiaiti\r\n<3', 0, 'Ativo', 8, 6),
(6, 'Achei muito tuff, 67 resenha', 0, 'Ativo', 8, 9),
(7, 'First', 0, 'Ativo', 8, 9),
(8, 'filme maravilhoso, gostei bastante', 0, 'Ativo', 10, 9),
(9, 'nota 10', 0, 'Ativo', 10, 1),
(10, 'colocar filtro nos comentários rapeize - não comentem besteiras até lá', 0, 'Ativo', 1, 9),
(14, 'teste de comentário chulo: merd@', 0, 'Inativo', 1, 9),
(16, 'm3rd@', 0, 'Inativo', 1, 9),
(17, 'porra', 0, 'Inativo', 1, 9),
(18, 'cu', 0, 'Inativo', 1, 9),
(20, 'Não vou comentar porque nao6assti o filme', 0, 'Ativo', 11, 9),
(21, 'teste', 0, 'Inativo', 1, 9),
(24, 'eu gosto muito de it', 0, 'Ativo', 14, 1),
(25, 'melhor filme do mundo', 0, 'Ativo', 14, 12),
(26, 'quem ler esse comentário vai flutuar também', 0, 'Ativo', 1, 12),
(27, 'melhores heróis', 0, 'Ativo', 14, 13),
(28, 'quarteto prateado e o surfista fantástico', 0, 'Ativo', 1, 13),
(29, 'Não gostei do filme', 0, 'Ativo', 15, 4),
(30, 'Fiquei com medo, não era o filme do Bozo??', 0, 'Ativo', 15, 12),
(31, 'Pq o capitão América tá pegando fogo nesse filme ????', 0, 'Ativo', 15, 13),
(32, 'Não entendi… a hérmione não é mais uma bruxa ?', 0, 'Ativo', 15, 6),
(33, 'Eu caí no clickbait de novo… pensei q era o segundo filme do Bozo!', 0, 'Ativo', 15, 1),
(34, 'O que aconteceu depois do pôr do sol ?', 0, 'Ativo', 15, 3),
(35, 'ué, não era o tom holland o ator do homem aranha?????', 0, 'Ativo', 15, 8),
(36, 'muito bom', 0, 'Ativo', 18, 17);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente`
--

CREATE TABLE `cliente` (
  `nome_cliente` varchar(100) NOT NULL COMMENT 'Nome do cliente',
  `cpf_cliente` varchar(14) NOT NULL COMMENT 'CPF do cliente',
  `status_conta_cliente` enum('Ativo','Inativo') NOT NULL COMMENT 'Status da conta do Cliente: Ativo; Inativo; Suspenso',
  `FK_cliente_id_cliente` int(11) NOT NULL COMMENT 'ID identificador do cliente',
  `FK_cliente_id_usuario` int(11) NOT NULL COMMENT 'ID identificador do usuário'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `cliente`
--

INSERT INTO `cliente` (`nome_cliente`, `cpf_cliente`, `status_conta_cliente`, `FK_cliente_id_cliente`, `FK_cliente_id_usuario`) VALUES
('Rafael', '12345678910', 'Inativo', 1, 4),
('Caio Cabral Cavalcante', '54796084332', 'Inativo', 2, 7),
('Marilia', '67676767676', 'Inativo', 3, 8),
('Rafael Ferreira', '33333333333', 'Inativo', 6, 11),
('Sérgio Padilla', '21662176218', 'Inativo', 8, 13),
('Felício', '27628262826', 'Inativo', 10, 15),
('Gabriel Pereira', '88888888888', 'Inativo', 11, 16),
('Rafael', '123.342.412-34', 'Ativo', 12, 17),
('Rafael 3', '214.354.652-34', 'Ativo', 13, 20),
('felisio', '12345678912', 'Ativo', 14, 21),
('Neymar junior', '28299291919', 'Inativo', 15, 22),
('Rafael teste 34', '21364357656', 'Inativo', 16, 23),
('José', '12435342142', 'Inativo', 17, 24),
('roger', '12345678900', 'Inativo', 18, 26),
('wfeeeeeeeeee', '32111111111', 'Inativo', 19, 27),
('Rafael Ferreira da Fonseca Rodrigues de Almeida', '21345685965', 'Ativo', 20, 28);

-- --------------------------------------------------------

--
-- Estrutura para tabela `conteudo`
--

CREATE TABLE `conteudo` (
  `id_conteudo` int(11) NOT NULL COMMENT 'ID identificador do conteúdo',
  `titulo_conteudo` varchar(100) NOT NULL COMMENT 'Título do conteúdo',
  `sinopse_conteudo` varchar(500) NOT NULL COMMENT 'Sinopse do conteúdo',
  `elenco_conteudo` varchar(255) NOT NULL COMMENT 'Elenco do conteúdo',
  `diretores_conteudo` varchar(100) NOT NULL COMMENT 'Diretores do conteúdo',
  `genero_conteudo` varchar(100) NOT NULL COMMENT 'Genêro do conteúdo',
  `classe_etaria_conteudo` int(11) NOT NULL COMMENT 'Classe etária do conteúdo:',
  `janela_exib_inicio_conteudo` date NOT NULL COMMENT 'Janela de exibição do conteúdo: Início',
  `janela_exib_fim_conteudo` date NOT NULL COMMENT 'Janela de exibição do conteúdo: Fim',
  `thumb_conteudo_vertical` varchar(255) NOT NULL COMMENT 'Caminho da thumbnail vertical',
  `banner_conteudo_horizontal` varchar(255) NOT NULL COMMENT 'Caminho do banner vertical',
  `video_conteudo` varchar(255) NOT NULL COMMENT 'Caminho do arquivo de vídeo',
  `bloqueio_admin` enum('Sim','Não') NOT NULL DEFAULT 'Não' COMMENT 'Bloqueio do filme pela administração',
  `conteudo_status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo' COMMENT 'Status da conta do Conteúdo: Ativo; Inativo',
  `FK_conteudo_id_distribuidor` int(11) DEFAULT NULL COMMENT 'ID identificador do distribuidor',
  `duracao_conteudo` varchar(10) NOT NULL COMMENT 'Duração do conteudo',
  `FK_conteudo_id_administrador` int(11) DEFAULT NULL COMMENT 'ID do admin - permite enviar os filmes, como administrador'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `conteudo`
--

INSERT INTO `conteudo` (`id_conteudo`, `titulo_conteudo`, `sinopse_conteudo`, `elenco_conteudo`, `diretores_conteudo`, `genero_conteudo`, `classe_etaria_conteudo`, `janela_exib_inicio_conteudo`, `janela_exib_fim_conteudo`, `thumb_conteudo_vertical`, `banner_conteudo_horizontal`, `video_conteudo`, `bloqueio_admin`, `conteudo_status`, `FK_conteudo_id_distribuidor`, `duracao_conteudo`, `FK_conteudo_id_administrador`) VALUES
(1, 'IT: Capítulo Dois', 'Vinte e sete anos após os acontecimentos do primeiro filme, Pennywise retorna à cidade de Derry. Mike Hanlon reúne novamente os antigos integrantes do Clube dos Perdedores, agora adultos, para cumprir a promessa feita na infância e enfrentar a criatura. Ao voltar para Derry, eles precisam confrontar os traumas e medos do passado para tentar derrotar Pennywise de uma vez por todas.', 'Bill Skarsgård, James McAvoy, Jessica Chastain, Bill Hader, Isaiah Mustafa, Jay Ryan, James Ransone, Andy Bean', 'Andy Muschietti', 'terror,suspense', 16, '2026-09-02', '2028-09-02', 'thumbs/verticais/filme_6a98c83c32e125.55741130_vertical.jpg', 'thumbs/horizontais/filme_6a98c83c32e125.55741130_horizontal.jpg', 'vids/filme_6a98c83c32e125.55741130_video.mp4', 'Não', 'Ativo', 1, '2h49min', NULL),
(2, 'Antes do Amanhecer', 'Jesse, um jovem americano viajando pela Europa, conhece Céline, uma estudante francesa, em um trem com destino a Paris. Encantado pela jovem, Jesse a convence a descer com ele em Viena e passar a noite explorando a cidade. Enquanto caminham e conversam, os dois compartilham seus pensamentos, experiências e sentimentos, desenvolvendo uma conexão inesperada antes que precisem seguir caminhos diferentes.', 'Ethan Hawke, Julie Delpy, Andrea Eckert, Hanno Pöschl, Karl Bruckschwaiger, Tex Rubinowitz, Erni Mangold, Dominik Castell e Haymon Maria Buttinger.', 'Richard Linklater', 'drama,romance', 14, '2026-09-02', '2028-09-02', 'thumbs/verticais/filme_6a98cdd3a18062.21388615_vertical.jpg', 'thumbs/horizontais/filme_6a98cdd3a18062.21388615_horizontal.jpg', 'vids/filme_6a98cdd3a18062.21388615_video.mp4', 'Não', 'Ativo', 1, '1h41min', NULL),
(3, 'Antes do Pôr-do-Sol (2004)', 'Jesse (Ethal Hawke) e Celine (Julie Delpy) se conheceram por acaso em uma viagem de trem que ia de Budapeste a Viena, passando o dia juntos e se separando no início do dia seguinte. Nove anos depois eles se reencontram, novamente por acaso. Jesse agora é um conhecido escritor, enquanto que Celine trabalha para uma organização de proteção ao meio-ambiente. Jesse agora está em Paris para promover seu mais novo livro e, após reencontrar Celine, passa com ela algumas horas, onde discutem o que acont', 'Ethan Hawke, Julie Delpy, Vernon Dobtcheff.', 'Richard Linklater', 'romance', 12, '2026-09-03', '2028-09-03', 'thumbs/verticais/filme_6a996bd60caa02.07236623_vertical.webp', 'thumbs/horizontais/filme_6a996bd60caa02.07236623_horizontal.jpg', 'vids/filme_6a996bd60caa02.07236623_video.mp4', 'Não', 'Ativo', 1, '1h20min', NULL),
(4, 'Antes da Meia-Noite (2013)', 'Nove anos após os eventos de Antes do Pôr-do-sol, Jesse (Ethan Hawke) e Celine (Julie Delpy) vivem juntos em Paris, ao lado das filhas gêmeas que tiveram. Ele busca sempre manter contato com Hank (Seamus Davey-Fitzpatrick), o filho adolescente que teve com a ex-esposa e que vive em Chicago com a mãe. Quando o casal resolve ir à Grécia com as filhas, Jesse decide também convidar Hank para a viagem. Neste contexto, Jesse segue tentando se tornar um romancista de sucesso, enquanto que Celine consid', 'Julie Delpy, Ethan Hawke, Seamus Davey-Fitzpatrick', 'Richard Linklater', 'romance', 14, '2026-09-03', '2028-09-03', 'thumbs/verticais/filme_6a996db3349045.81026412_vertical.jpg', 'thumbs/horizontais/filme_6a996db3349045.81026412_horizontal.jpg', 'vids/filme_6a996db3349045.81026412_video.mp4', 'Não', 'Ativo', 1, '1h48min', NULL),
(6, 'As Vantagens de Ser Invisível', 'Charlie (Logan Lerman) é um jovem que tem dificuldades para interagir em sua nova escola. Com os nervos à flor da pele, ele se sente deslocado no ambiente. Seu professor de literatura, no entanto, acredita nele e o vê como um gênio. Mas Charlie continua a pensar pouco de si... até o dia em que dois amigos, Patrick (Ezra Miller) e Sam (Emma Watson), passam a andar com ele.', 'Logan Lerman, Emma Watson, Ezra Miller', 'Stephen Chbosky', 'drama,romance', 16, '2026-09-03', '2028-09-30', 'thumbs/verticais/filme_6a9971e85e50e1.55365540_vertical.webp', 'thumbs/horizontais/filme_6a9971e85e50e1.55365540_horizontal.jpg', 'vids/filme_6a9971e85e50e1.55365540_video.mp4', 'Não', 'Ativo', 1, '1h43min', NULL),
(7, 'Jogos Vorazes (2012)', 'Em um futuro distópico, a população de Panem é controlada pela Capital, que todos os anos realiza os Jogos Vorazes, uma competição em que jovens de diferentes distritos lutam pela sobrevivência. Quando sua irmã é escolhida para participar, Katniss Everdeen se voluntaria para ocupar seu lugar. Ao lado de Peeta Mellark, ela enfrenta desafios, perigos e outros competidores enquanto tenta sobreviver à arena. Durante os Jogos, Katniss começa a desafiar as regras impostas pela Capital, tornando-se um.', 'Jennifer Lawrence, Josh Hutcherson, Liam Hemsworth, Woody Harrelson e Elizabeth Banks.', 'Gary Ross', 'acao,drama,ficcao', 14, '2026-09-03', '2028-09-03', 'thumbs/verticais/filme_6a99b34a4da3b1.95922949_vertical.jpg', 'thumbs/horizontais/filme_6a99b34a4da3b1.95922949_horizontal.jpg', 'vids/filme_6a99b34a4da3b1.95922949_video.mp4', 'Não', 'Ativo', 1, '2h22min', NULL),
(8, 'Homem Aranha (2002)', 'Peter Parker é um jovem tímido que vive com seus tios e é apaixonado por sua amiga Mary Jane. Após ser picado por uma aranha geneticamente modificada, ele desenvolve poderes extraordinários e decide usá-los para combater o crime. Enquanto aprende a lidar com suas novas habilidades e responsabilidades, Peter precisa enfrentar o perigoso Duende Verde, colocando em risco sua própria vida e as pessoas que ama.', 'Tobey Maguire, Kirsten Dunst, James Franco, Willem Dafoe e J.K. Simmons.', 'Sam Raimi', 'acao,aventura,ficcao', 12, '2026-09-03', '2028-09-03', 'thumbs/verticais/filme_6a99b48a2259c6.56401067_vertical.jpg', 'thumbs/horizontais/filme_6a99b48a2259c6.56401067_horizontal.jpg', 'vids/filme_6a99b48a2259c6.56401067_video.mp4', 'Não', 'Ativo', 1, '2h1min', NULL),
(9, 'Quarteto Fantástico', 'Em Quarteto Fantástico (2005), um grupo de viajantes espaciais ganha superpoderes após uma tempestade de radiação cósmica e precisa deter seu ex-financiador transformado em vilão.', 'Ioan Gruffudd; Jessica Alba; Chris Evans; Michael Chiklis; Julian McMahon', 'Tim Story; Matt Shakman', 'acao,aventura,ficcao', 12, '2026-09-08', '2027-09-08', 'thumbs/verticais/filme_6a9ff85d449fa1.86535313_vertical.jpg', 'thumbs/horizontais/filme_6a9ff85d449fa1.86535313_horizontal.webp', 'vids/filme_6a9ff85d449fa1.86535313_video.mp4', 'Não', 'Ativo', 1, '1h46m', NULL),
(12, 'It: A Coisa', 'Na pequena cidade de Derry, no Maine, o desaparecimento do pequeno Georgie e de outras crianças abala a comunidade. Seu irmão mais velho, Bill, une-se a um grupo de adolescentes marginalizados, o \"Clube dos Perdedores\", para investigar os sumiços por conta própria. Eles logo descobrem que a cidade é assombrada por Pennywise, uma criatura milenar disfarçada de palhaço que se alimenta do medo e assume a forma dos piores pesadelos de suas vítimas. Para sobreviver e derrotar o monstro, esses amigos', 'Bill Skarsgård;  Jaeden Martell;  James McAvoy;  Sophia Lillis;  Jessica Chastain;  Finn Wolfhard;  Bill Hader;  Jeremy Ray Taylor;  Jay Ryan;  Chosen Jacobs;  Isaiah Mustafa;  Jack Dylan Grazer;  James Ransone;  Wyatt Oleff;  Andy Bean', 'Andy Muschietti;  Tommy Lee Wallace;  Jason Fuchs', 'drama,terror,suspense', 16, '2026-09-21', '2028-09-21', 'thumbs/verticais/filme_6ab169735e1fe0.95946665_vertical.webp', 'thumbs/horizontais/filme_6ab169735e1fe0.95946665_horizontal.jpg', 'vids/filme_6ab169735e1fe0.95946665_video.mp4', 'Não', 'Ativo', 1, '2h15min', NULL),
(13, 'Quarteto Fantástico e o Surfista Prateado', 'Enquanto preparam o casamento de Reed Richards e Sue Storm, o Quarteto Fantástico descobre uma nova ameaça cósmica: o Surfista Prateado, um ser misterioso que está provocando anomalias e destruição por toda a Terra. Conforme o grupo investiga o ser intergaláctico, eles descobrem que ele é o arauto de Galactus, uma entidade colossal devoradora de mundos que está prestes a destruir o planeta. Para salvar a humanidade, os heróis se veem forçados a unir forças com as autoridades e até mesmo com o se', 'Ioan Gruffudd; Jessica Alba; Chris Evans; Michael Chiklis; Julian McMahon; Doug Jones; Laurence Fishburne; Kerry Washington; Beau Garrett', 'Tim Story.', 'acao,aventura,ficcao', 10, '2026-09-22', '2028-09-22', 'thumbs/verticais/filme_6ab269e2ccc526.63348935_vertical.webp', 'thumbs/horizontais/filme_6ab26ab4963765.99924700_horizontal.webp', 'vids/filme_6ab269e2ccc526.63348935_video.mp4', 'Não', 'Ativo', 1, '1h32min', NULL),
(14, 'Karatê Kid (2010)', 'Dre Parker, de 12 anos, muda-se com a mãe de Detroit para Pequim, na China. Lá, enfrenta dificuldades de adaptação e passa a sofrer bullying de Cheng, um jovem praticante de kung fu. Dre encontra ajuda no Sr. Han, zelador de seu prédio e mestre de kung fu, que passa a treiná-lo e a ensiná-lo sobre disciplina, maturidade e autocontrole.', 'Jaden Smith, Jackie Chan, Taraji P. Henson, Wenwen Han e Zhenwei Wang.', 'Harald Zwart', 'acao,aventura', 10, '2026-09-30', '2028-09-20', 'thumbs/verticais/filme_6abd9627752ce2.75600473_vertical.jpg', 'thumbs/horizontais/filme_6abd9627752ce2.75600473_horizontal.jpg', 'vids/filme_6abd9627752ce2.75600473_video.mp4', 'Não', 'Ativo', 3, '2h20min', NULL),
(15, 'Caça-Fantasmas (1984)', 'Após perderem seus empregos como pesquisadores universitários, Peter Venkman, Ray Stantz e Egon Spengler decidem abrir uma empresa especializada em investigar e capturar fantasmas. O negócio começa a crescer quando acontecimentos sobrenaturais começam a se espalhar por Nova York, incluindo uma ameaça ligada ao prédio onde vive Dana Barrett.', 'Bill Murray, Dan Aykroyd, Harold Ramis, Ernie Hudson e Sigourney Weaver', 'Ivan Reitman', 'aventura,comedia,ficcao', 0, '2026-09-29', '2028-09-29', 'thumbs/verticais/filme_6abd97615abb81.89913757_vertical.jpg', 'thumbs/horizontais/filme_6abd97615abb81.89913757_horizontal.jpg', 'vids/filme_6abd97615abb81.89913757_video.mp4', 'Não', 'Ativo', 3, '1h45min', NULL),
(16, 'Jumanji (1995)', 'Um misterioso jogo de tabuleiro chamado Jumanji possui o poder de trazer seus elementos à realidade. Anos depois de um garoto desaparecer dentro do jogo, duas crianças encontram o tabuleiro e acabam libertando-o. Para colocar tudo de volta ao normal, eles precisam terminar a partida, enfrentando animais selvagens, plantas perigosas e outras ameaças que surgem pela cidade.', 'Robin Williams, Bonnie Hunt, Kirsten Dunst, Bradley Pierce e Jonathan Hyde.', 'Joe Johnston', 'aventura,comedia', 0, '2026-09-29', '2028-09-29', 'thumbs/verticais/filme_6abd98265a10a9.00783039_vertical.jpg', 'thumbs/horizontais/filme_6abd98265a10a9.00783039_horizontal.jpg', 'vids/filme_6abd98265a10a9.00783039_video.mp4', 'Não', 'Ativo', 3, '1h44min', NULL),
(17, 'MIB: Homens de Preto (1997)', 'ames Edwards, um policial de Nova York, é recrutado pelo agente Kay para trabalhar em uma organização secreta que monitora e controla a presença de alienígenas na Terra. Juntos, eles precisam impedir uma ameaça extraterrestre que pode colocar o planeta em perigo.', 'Will Smith, Tommy Lee Jones, Linda Fiorentino, Vincent D\'Onofrio, Rip Torn e Tony Shalhoub.', 'Barry Sonnenfeld', 'acao,comedia', 12, '2026-10-08', '2028-10-08', 'thumbs/verticais/filme_6ac7904e82f748.43753801_vertical.jpg', 'thumbs/horizontais/filme_6ac7904e82f748.43753801_horizontal.jpg', 'vids/filme_6ac7904e82f748.43753801_video.mp4', 'Não', 'Ativo', 3, '1h38min', NULL),
(18, 'Uncharted: Fora do Mapa (2022)', 'Nathan Drake, um jovem habilidoso e aventureiro, é recrutado pelo caçador de tesouros Victor “Sully” Sullivan para encontrar uma enorme fortuna perdida há 500 anos. A busca leva os dois por diversos lugares do mundo enquanto enfrentam rivais perigosos e tentam solucionar pistas relacionadas ao tesouro.', 'Tom Holland, Mark Wahlberg, Sophia Ali, Tati Gabrielle e Antonio Banderas.', 'Ruben Fleischer', 'acao,aventura', 12, '2026-10-08', '2028-10-08', 'thumbs/verticais/filme_6ac79174ec0d50.30357480_vertical.jpg', 'thumbs/horizontais/filme_6ac79174ec0d50.30357480_horizontal.jpg', 'vids/filme_6ac79174ec0d50.30357480_video.mp4', 'Não', 'Ativo', 3, '1h56min', NULL),
(19, 'Click (2006)', 'Michael Newman é um arquiteto viciado em trabalho que luta para equilibrar sua carreira exigente e a vida familiar. Frustrado, ele encontra um inventor excêntrico chamado Morty, que lhe entrega um controle remoto universal mágico capaz de controlar o tempo de sua própria realidade. Michael passa a adiantar os momentos chatos da vida (como discussões e trânsito), mas o controle começa a automatizar as suas escolhas sozinho, fazendo com que ele veja anos passarem num piscar de olhos.', 'Adam Sandler (Michael Newman), Kate Beckinsale (Donna Newman), Christopher Walken (Morty), David Hasselhoff (Sr. Ammer), Henry Winkler (Ted Newman), Julie Kavner (Trudy Newman) e Sean Astin (Bill).', 'Frank Coraci', 'comedia,ficcao', 10, '2026-10-22', '2027-10-22', 'thumbs/verticais/filme_6ada2fc7a86024.72125978_vertical.jpg', 'thumbs/horizontais/filme_6ada2fc7a86024.72125978_horizontal.jpg', 'vids/filme_6ada2fc7a86024.72125978_video.mp4', 'Não', 'Ativo', 3, '1h47min', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `distribuidor`
--

CREATE TABLE `distribuidor` (
  `id_distribuidor` int(11) NOT NULL,
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `empresa_distribuidor` varchar(100) NOT NULL,
  `cnpj_empresa_distribuidor` varchar(18) NOT NULL,
  `FK_distribuidor_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `distribuidor`
--

INSERT INTO `distribuidor` (`id_distribuidor`, `status`, `empresa_distribuidor`, `cnpj_empresa_distribuidor`, `FK_distribuidor_id_usuario`) VALUES
(1, 'Ativo', 'filmes TV', '12345678910123', 5),
(3, 'Ativo', 'Columbia Pictures', '00.979.601/0001-98', 25);

-- --------------------------------------------------------

--
-- Estrutura para tabela `recuperacao_senha`
--

CREATE TABLE `recuperacao_senha` (
  `id_recuperacao` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `codigo_hash` char(64) NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `recuperacao_senha`
--

INSERT INTO `recuperacao_senha` (`id_recuperacao`, `email`, `codigo_hash`, `criado_em`) VALUES
(1, 'rafaelffr2010@gmail.com', '0cee22898b75802360a68a32909fc512649841e999710e718ea11e9eac860a80', '2026-10-21 15:26:58'),
(2, 'rafaelffr2010@gmail.com', 'e93cff36ad4536bda0a14d99443068f15cbf638fc52787239c1973430939be14', '2026-10-21 15:27:44'),
(3, 'rafaelffr2010@gmail.com', '68e121d3b7968ed5151380ea7c0463b42b344f50f88982657aedeeebc2d69f67', '2026-10-21 15:27:59'),
(4, 'rafaelffr2010@gmail.com', '807adb98a750f033e95f879b46f2c97289c913d6e7224310c54785f24e4c87dc', '2026-10-21 15:28:19'),
(5, 'rafaelffr2010@gmail.com', '28d3d4b64fc6b5085020a1b868045368881532990a9b66d43dac34c9561220d5', '2026-10-21 15:28:21'),
(6, 'rafaelffr2010@gmail.com', '8cdee3250626f03a1356058d0630ee41f1bb50622d4553a5c715f2bf7f99e2cb', '2026-10-21 17:22:37'),
(7, 'rafaelffr2010@gmail.com', 'cd46a271928f28340e77121029dc7e912218a078767639862293f8b6f28ef729', '2026-10-21 17:30:48'),
(8, 'rafaelffr2010@gmail.com', '817c859924e22cefd07556c9d4bbada5fe56e0038eb1b91ac0c878099bc77b6f', '2026-10-21 17:31:06'),
(9, 'rafaelffr2010@gmail.com', 'ee0e63df81c1120ead2735892d28260567102d2f55bae0d840b0496aca4ca019', '2026-10-21 17:31:28'),
(10, 'rafaelffr2010@gmail.com', 'd57569c6bbb06dfa44a00bfe93ce97506d802fea21b3f6f29b5ab2ca71fd43b6', '2026-10-21 17:36:49'),
(11, 'rafaelffr2010@gmail.com', '3dc250ac6f420b08c47f8dc883833544bc4e83ea6edcd05371e45b097566d691', '2026-10-21 20:16:12'),
(12, 'rafaelffr2010@gmail.com', 'b9d43a770df35a5d3c987053f6c7e1490ae74f4418fac1a409bc701ac4e0eb88', '2026-10-22 11:09:40'),
(13, 'rafaelffr2010@gmail.com', 'd691e852b2991328ef3e2bd35fc85b72ed46b7b3731670073b02bad2d46d1884', '2026-10-22 19:06:10'),
(14, 'rafaelffr2010@gmail.com', 'e590901fdc8f53bdcba4f1ccc28e128d278d3d27b0a4b2125afaaea9e8a62a8f', '2026-10-22 19:11:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `senha` varchar(255) NOT NULL COMMENT 'Senha do usuário',
  `email` varchar(100) NOT NULL COMMENT 'E-mail do usuário',
  `aniversario` date DEFAULT NULL COMMENT 'Data de Nascimento do Usuário',
  `tipo_usuario` enum('Administrador','Distribuidor','Cliente') NOT NULL COMMENT 'Tipo de usuário: Administrador; Distribuidor e Cliente',
  `id_usuario` int(11) NOT NULL COMMENT 'ID identificador de usuário'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`senha`, `email`, `aniversario`, `tipo_usuario`, `id_usuario`) VALUES
('$2y$12$N53AXDZzIKPzYaYeaKkJauPkF3l8tXieNEI4j.nz/rV050gfoBByK', 'streaming@gmail.com', '2005-08-23', 'Administrador', 1),
('$2y$12$xO89LG1udW21ewiFoJYIHexvJMzix0rSpfDZbYJ3CuFXyqbRWMooq', 'rafael@gmail.com', '2005-03-09', 'Cliente', 4),
('$2y$12$J9uQiPm7i9SSp0N6Ze7FN.vIpT59/OEYzCWXLY2tGX9aokUQrxCnq', 'distribuidor@gmail.com', NULL, 'Distribuidor', 5),
('$2y$12$GR6RRac5Dp9IRXbdSj8ihuiDbDxVCIhQU.ZYWRTyx0ZtGyqO/V8oq', 'caiocabral6767@gmail.com', '2008-08-07', 'Cliente', 7),
('$2y$12$fwPOFwRXXrabpTkUflojne.xHfysJ0RlphncyW9WxSIY42bGgF4U.', 'ola@gmail.com', '2000-03-08', 'Cliente', 8),
('$2y$12$m4kVR61b/ZNvepYM3QZ/w.NrM26VL4nNMR0Q71CJ6NLCuGRZ1gRTi', 'kaka@gmail.com', '2005-03-09', 'Cliente', 11),
('$2y$12$vQgdIalj/od00gRibCZ2wuivF1ZFEv./sRQhkqZm2e5ItYfEyIArC', 'sergio@gmail.com', '2004-10-08', 'Cliente', 13),
('$2y$12$2iiHfK8HZz0F4ezWwLFl3.mRexlM7fJeTOHkmd1rQO8sUiQISPGAi', 'felicio@gmail.com', '2002-07-02', 'Cliente', 15),
('$2y$12$phAq513DyzrKrD8QJh6a3OZ/70VJcrJiFxv6B9fcm5sDBWhrcBj5S', 'gabriel@gmail.com', '2007-09-18', 'Cliente', 16),
('$2y$12$tkSdjOxbKDk/P84jsq3klO3y.Pmeu/MFtFiSZnTfnc7w8XOiBaUdW', 'rafael2@gmail.com', NULL, 'Cliente', 17),
('$2y$12$F0y4TlF3DMI.VN.y9pANBuS2xWmAjqMgxE3GHOztFqzYQ3mrST722', 'rafael3@gmail.com', NULL, 'Cliente', 20),
('$2y$12$wpTSP5fx1s.ssabS0UHXY.XOHSL9.M6FeHP774yDEZyWCGNvyYTIO', 'felisio@gmail.com', '2002-07-02', 'Cliente', 21),
('$2y$12$dA2L9FW3BnkvG3jxYBR5r.U1NaRXoo6cmp2GVL6svqFzv6qqnDSqu', 'SENADORFLAVIOBOLSONARO@gmail.com', '2005-09-23', 'Cliente', 22),
('$2y$12$d4vHugfdrjlEQMxlV8Hbo.v/dtCOXNq86ItrKVH4nRYB90pj6y8fK', 'rafael_teste34@gmail.com', '2001-03-09', 'Cliente', 23),
('$2y$12$OjoiFHicvzqfPO10X20kEuUxDYQx8IzZ.cZc.l2maV26ylSOpdTK.', 'jose@gmail.com', '2001-09-25', 'Cliente', 24),
('$2y$12$QWBDhrakTBH4nzeZuyW1eOzowjhqiB7OEcsRxfQuS1zvJyntbD2yu', 'columbia_pictures@gmail.com', NULL, 'Distribuidor', 25),
('$2y$12$BZ9vlkQU3v2rE2Ys5.TRjOaxJzxnJ.ERGg6777/4tF14bkRX1aLa2', 'vogevo3335@goldlicai.com', '2002-02-02', 'Cliente', 26),
('$2y$12$VwV0O3Bkf9.YquuaObbP9eguW9zazmUYfQmXLtf6gad9U/M1J1/q.', 'streaminggg@gmail.com', '2006-02-07', 'Cliente', 27),
('$2y$12$T3ETnEn9ntSoNevzzetd2.X0FQjMsHmtV0j8.3E3NCNTgXdcN4cYa', 'rafaelffr2010@gmail.com', '2005-03-09', 'Cliente', 28),
('$2y$12$4sqs08sls2rsNwd.4mpty.aZYP/9uufZI.kWCB3QZ3oBN68rxR6PC', 'streaming.orion.tv@gmail.com', NULL, 'Administrador', 29);

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `id_usuario` (`FK_administrador_id_usuario`) USING BTREE;

--
-- Índices de tabela `assinatura`
--
ALTER TABLE `assinatura`
  ADD PRIMARY KEY (`id_assinatura`),
  ADD KEY `FK_assinatura_cliente` (`FK_assinatura_id_cliente`) USING BTREE;

--
-- Índices de tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD PRIMARY KEY (`id_avaliacao`),
  ADD KEY `FK_avaliacao_cliente` (`FK_avaliacao_id_cliente`) USING BTREE,
  ADD KEY `FK_avaliacao_conteudo` (`FK_avaliacao_id_conteudo`) USING BTREE;

--
-- Índices de tabela `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`FK_cliente_id_cliente`) USING BTREE,
  ADD UNIQUE KEY `cpf_cliente` (`cpf_cliente`),
  ADD UNIQUE KEY `id_usuario` (`FK_cliente_id_usuario`) USING BTREE;

--
-- Índices de tabela `conteudo`
--
ALTER TABLE `conteudo`
  ADD PRIMARY KEY (`id_conteudo`),
  ADD KEY `FK_conteudo_distribuidor` (`FK_conteudo_id_distribuidor`) USING BTREE,
  ADD KEY `FK_conteudo_administrador` (`FK_conteudo_id_administrador`);

--
-- Índices de tabela `distribuidor`
--
ALTER TABLE `distribuidor`
  ADD PRIMARY KEY (`id_distribuidor`),
  ADD UNIQUE KEY `cnpj_empresa_distribuidor` (`cnpj_empresa_distribuidor`),
  ADD UNIQUE KEY `id_usuario` (`FK_distribuidor_id_usuario`) USING BTREE;

--
-- Índices de tabela `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  ADD PRIMARY KEY (`id_recuperacao`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID identificador do administrador', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `assinatura`
--
ALTER TABLE `assinatura`
  MODIFY `id_assinatura` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID identificador da assinatura', AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  MODIFY `id_avaliacao` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID identificador da avaliação ', AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `FK_cliente_id_cliente` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID identificador do cliente', AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `conteudo`
--
ALTER TABLE `conteudo`
  MODIFY `id_conteudo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID identificador do conteúdo', AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `distribuidor`
--
ALTER TABLE `distribuidor`
  MODIFY `id_distribuidor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  MODIFY `id_recuperacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID identificador de usuário', AUTO_INCREMENT=30;

--
-- Restrições para dumps de tabelas
--

--
-- Restrições para tabelas `administrador`
--
ALTER TABLE `administrador`
  ADD CONSTRAINT FOREIGN KEY (`FK_administrador_id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Restrições para tabelas `assinatura`
--
ALTER TABLE `assinatura`
  ADD CONSTRAINT `FK_assinatura_cliente` FOREIGN KEY (`FK_assinatura_id_cliente`) REFERENCES `cliente` (`FK_cliente_id_cliente`);

--
-- Restrições para tabelas `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD CONSTRAINT `FK_avaliacao_cliente` FOREIGN KEY (`FK_avaliacao_id_cliente`) REFERENCES `cliente` (`FK_cliente_id_cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_avaliacao_conteudo` FOREIGN KEY (`FK_avaliacao_id_conteudo`) REFERENCES `conteudo` (`id_conteudo`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `FK_cliente_usuario` FOREIGN KEY (`FK_cliente_id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Restrições para tabelas `conteudo`
--
ALTER TABLE `conteudo`
  ADD CONSTRAINT `FK_conteudo_administrador` FOREIGN KEY (`FK_conteudo_id_administrador`) REFERENCES `administrador` (`id_administrador`),
  ADD CONSTRAINT `FK_conteudo_distribuidor` FOREIGN KEY (`FK_conteudo_id_distribuidor`) REFERENCES `distribuidor` (`id_distribuidor`);

--
-- Restrições para tabelas `distribuidor`
--
ALTER TABLE `distribuidor`
  ADD CONSTRAINT `FK_distribuidor_usuario` FOREIGN KEY (`FK_distribuidor_id_usuario`) REFERENCES `usuario` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
