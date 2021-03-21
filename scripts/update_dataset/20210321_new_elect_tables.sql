-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  Dim 21 mars 2021 à 13:00
-- Version du serveur :  5.7.26
-- Version de PHP :  7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
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
-- Structure de la table `elect_deputes_candidats`
--

DROP TABLE IF EXISTS `elect_deputes_candidats`;
CREATE TABLE IF NOT EXISTS `elect_deputes_candidats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mpId` varchar(15) NOT NULL,
  `election` tinyint(4) NOT NULL,
  `district` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `nuance` varchar(25) DEFAULT NULL,
  `source` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `elect_deputes_candidats`
--

INSERT INTO `elect_deputes_candidats` (`id`, `mpId`, `election`, `district`, `position`, `nuance`, `source`) VALUES
(1, 'PA720278', 1, 'Pays de la Loire', 'Tête de liste', NULL, 'https://www.ouest-france.fr/elections/regionales/elections-regionales-en-pays-de-la-loire-qui-seront-les-candidats-en-juin-2021-7190091');

-- --------------------------------------------------------

--
-- Structure de la table `elect_libelle`
--

DROP TABLE IF EXISTS `elect_libelle`;
CREATE TABLE IF NOT EXISTS `elect_libelle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `libelleAbrev` varchar(255) NOT NULL,
  `dateYear` year(4) NOT NULL,
  `dateFirstRound` date NOT NULL,
  `dateSecondRound` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `elect_libelle`
--

INSERT INTO `elect_libelle` (`id`, `libelle`, `libelleAbrev`, `dateYear`, `dateFirstRound`, `dateSecondRound`) VALUES
(1, 'Élections régionales', 'Régionales', 2021, '2021-06-13', '2021-06-20');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
