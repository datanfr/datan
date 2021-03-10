-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Mer 10 Mars 2021 à 13:07
-- Version du serveur :  5.7.33-0ubuntu0.18.04.1
-- Version de PHP :  7.2.34-18+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `datan`
--

-- --------------------------------------------------------

--
-- Structure de la table `elect_2019_europe_listes`
--

CREATE TABLE IF NOT EXISTS `elect_2019_europe_listes` (
  `id` int(11) DEFAULT NULL,
  `id_n` text,
  `id_pct` text,
  `name` text,
  `tete` text,
  `parti` text,
  KEY `idx_id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `elect_2019_europe_listes`
--

INSERT INTO `elect_2019_europe_listes` (`id`, `id_n`, `id_pct`, `name`, `tete`, `parti`) VALUES
(1, '1_n', '1_pct', 'La France insoumise', 'Manon Aubry', 'La France insoumise'),
(2, '2_n', '2_pct', 'Une France royale', 'Robert De Prevoisin', 'Alliance royale'),
(3, '3_n', '3_pct', 'La ligne claire', 'Renaud Camus', 'Souveraineté, identité et libertés'),
(4, '4_n', '4_pct', 'Parti pirate', 'Florie Marie', 'Parti pirate'),
(5, '5_n', '5_pct', 'Renaissance', 'Nathalie Loiseau', 'La République en Marche'),
(6, '6_n', '6_pct', 'Démocratie représentative', 'Hamada Traoré', 'Démocratie représentative'),
(7, '7_n', '7_pct', 'Ensemble patriotes', 'Florian Philippot', 'Les Patriotes'),
(8, '8_n', '8_pct', 'PACE', 'Audric Alexandre', 'Parti des citoyens européens'),
(9, '9_n', '9_pct', 'Urgence écologie', 'Dominique Bourg', 'Génération écologie'),
(10, '10_n', '10_pct', 'Liste de la reconquête', 'Vincent Vauclin', 'Dissidence française'),
(11, '11_n', '11_pct', 'Les Européens', 'Jean-Christophe Lagarde', 'Union des démocrates européens'),
(12, '12_n', '12_pct', 'Envie d\'Europe', 'Raphaël Glucksmann', 'Parti socialiste'),
(13, '13_n', '13_pct', 'Parti fédéraliste européen', 'Yves Gernigon', 'Parti fédéraliste européen'),
(14, '14_n', '14_pct', 'Initiative citoyenne', 'Gilles Helgen', 'Mouvement pour l\'initiative citoyenne'),
(15, '15_n', '15_pct', 'Debout la France', 'Nicolas Dupont-Aignan', 'Debout la France'),
(16, '16_n', '16_pct', 'Allons enfants', 'Sophie Caillaud', 'Allons enfants'),
(17, '17_n', '17_pct', 'Décroissance 2019', 'Thérèse Delfel', 'Parti pour la décroissance'),
(18, '18_n', '18_pct', 'Lutte ouvrière', 'Nathalie Arthaud', 'Lutte ouvrière'),
(19, '19_n', '19_pct', 'Pour l\'Europe des gens', 'Ian Brossat', 'Parti comuniste français'),
(20, '20_n', '20_pct', 'Ensemble pour le frexit', 'François Asselineau', 'Union populaire républicaine'),
(21, '21_n', '21_pct', 'Liste citoyenne', 'Benoît Hamon', 'Génération.s'),
(22, '22_n', '22_pct', 'À voix égales', 'Nathalie Tomasini', 'À voix égales'),
(23, '23_n', '23_pct', 'Prenez le pouvoir', 'Jordan Bardella', 'Rassemblement national'),
(24, '24_n', '24_pct', 'Neutre et actif', 'Cathy Corbet', 'Neutre et actif'),
(25, '25_n', '25_pct', 'Révolutionnaire', 'Antonio Sanchez', 'Parti révolutionnaire Communistes'),
(26, '26_n', '26_pct', 'Esperanto', 'Pierre Dieumegard', 'Europe Démocratie Espéranto'),
(27, '27_n', '27_pct', 'Evolution citoyenne', 'Christophe Chalençon', 'Evolution citoyenne'),
(28, '28_n', '28_pct', 'Alliance jaune', 'Francis Lalanne', 'Alliance jaune'),
(29, '29_n', '29_pct', 'Union droite-centre', 'François-Xavier Bellamy', 'Les Républicains'),
(30, '30_n', '30_pct', 'Europe écologie', 'Yannick Jadot', 'Europe Ecologie Les Verts'),
(31, '31_n', '31_pct', 'Parti animaliste', 'Hélène Thouy', 'Parti animaliste'),
(32, '32_n', '32_pct', 'Les oubliés de l\'Europe', 'Olivier Bidou', 'Les oubliés de l\'Europe'),
(33, '33_n', '33_pct', 'UDLEF', 'Christian Person', 'Union démocratique pour la liberté, égalité, fraternité'),
(34, '34_n', '34_pct', 'Europe au service des peuples', 'Nagib Azergui', 'Union des démocrates musulmans français');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
