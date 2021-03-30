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

--
DROP TABLE IF EXISTS `elect_deputes_candidats`;
CREATE TABLE IF NOT EXISTS `elect_deputes_candidats` (
  `mpId` varchar(15) NOT NULL,
  `election` tinyint(4) NOT NULL,
  `district` int(5) NOT NULL,
  `position` varchar(50) NOT NULL,
  `nuance` varchar(25) DEFAULT NULL,
  `source` text NOT NULL,
  `visible` boolean NOT NULL DEFAULT 0,
  `modified_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mpId`, `election`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `elect_deputes_candidats`
--

-- Example data
INSERT INTO `elect_deputes_candidats` (`mpId`, `election`, `district`, `position`, `nuance`, `source`) VALUES
('PA720278', 1, 52, 'Tête de liste', NULL, 'https://www.ouest-france.fr/elections/regionales/elections-regionales-en-pays-de-la-loire-qui-seront-les-candidats-en-juin-2021-7190091');

--
DROP VIEW IF EXISTS `candidate_full`;
-- --------------------------------------------------------
-- --------------------------------------------------------
CREATE VIEW candidate_full AS SELECT
  edc.mpId as mpId, `election`, `district`, `position`, `nuance`, `source`, `visible`,
  `legislature`, `nameUrl`, `civ`, `nameFirst`, `nameLast`, `age`, `dptSlug`, `departementNom`, `departementCode`, `circo`, `mandatId`, dl.`libelle` as "depute_libelle", dl.`libelleAbrev` as "depute_libelleAbrev", `groupeId`, `groupeMandat`, `couleurAssociee`, `dateFin`, `datePriseFonction`, `causeFin`, `img`, `imgOgp`, `dateMaj`, `libelle_1`, `libelle_2`, `active`,
  el.`id` as "election_id", el.`libelle` as "election_libelle", el.`libelleAbrev` as "election_libelleAbrev", `dateYear`, `dateFirstRound`, `dateSecondRound`, edc.`modified_at`
  FROM elect_deputes_candidats edc
  LEFT JOIN deputes_last dl ON edc.mpId = dl.mpId
  LEFT JOIN elect_libelle el ON edc.election = el.id;

--
-- Structure de la table `elect_libelle`
--

--
DROP TABLE IF EXISTS `elect_libelle`;
CREATE TABLE IF NOT EXISTS `elect_libelle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `libelleAbrev` varchar(255) NOT NULL,
  `dateYear` year(4) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `dateFirstRound` date NOT NULL,
  `dateSecondRound` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `elect_libelle`
--

-- Example data
INSERT INTO `elect_libelle` (`id`, `libelle`, `libelleAbrev`, `dateYear`, `slug`, `dateFirstRound`, `dateSecondRound`) VALUES
(1, 'Élections régionales', 'Régionales', 2021, 'regionales-2021', '2021-06-13', '2021-06-20');
--- Insert départementales
INSERT INTO `elect_libelle` (`id`, `libelle`, `libelleAbrev`, `dateYear`, `slug`, `dateFirstRound`, `dateSecondRound`) VALUES
(2, 'Élections départementales', 'départementales', 2021, 'departementales-2021', '2021-06-13', '2021-06-20');
-- COMMIT;

--
-- Structure de la table `regions`
--

--
DROP TABLE IF EXISTS `regions`;
CREATE TABLE IF NOT EXISTS `regions` (
  `id` tinyint(3) DEFAULT NULL,
  `cheflieu` varchar(8) DEFAULT NULL,
  `tncc` varchar(4) DEFAULT NULL,
  `ncc` varchar(26) DEFAULT NULL,
  `nccner` varchar(26) DEFAULT NULL,
  `libelle` varchar(26) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `regions`
--

INSERT INTO `regions` (`id`, `cheflieu`, `tncc`, `ncc`, `nccner`, `libelle`) VALUES
('01', '97105', '3', 'GUADELOUPE', 'Guadeloupe', 'Guadeloupe'),
('02', '97209', '3', 'MARTINIQUE', 'Martinique', 'Martinique'),
('03', '97302', '3', 'GUYANE', 'Guyane', 'Guyane'),
('04', '97411', '0', 'LA REUNION', 'La Réunion', 'La Réunion'),
('06', '97608', '0', 'MAYOTTE', 'Mayotte', 'Mayotte'),
('11', '75056', '1', 'ILE DE FRANCE', 'Île-de-France', 'Île-de-France'),
('24', '45234', '2', 'CENTRE VAL DE LOIRE', 'Centre-Val de Loire', 'Centre-Val de Loire'),
('27', '21231', '0', 'BOURGOGNE FRANCHE COMTE', 'Bourgogne-Franche-Comté', 'Bourgogne-Franche-Comté'),
('28', '76540', '0', 'NORMANDIE', 'Normandie', 'Normandie'),
('32', '59350', '4', 'HAUTS DE FRANCE', 'Hauts-de-France', 'Hauts-de-France'),
('44', '67482', '2', 'GRAND EST', 'Grand Est', 'Grand Est'),
('52', '44109', '4', 'PAYS DE LA LOIRE', 'Pays de la Loire', 'Pays de la Loire'),
('53', '35238', '0', 'BRETAGNE', 'Bretagne', 'Bretagne'),
('75', '33063', '3', 'NOUVELLE AQUITAINE', 'Nouvelle-Aquitaine', 'Nouvelle-Aquitaine'),
('76', '31555', '1', 'OCCITANIE', 'Occitanie', 'Occitanie'),
('84', '69123', '1', 'AUVERGNE RHONE ALPES', 'Auvergne-Rhône-Alpes', 'Auvergne-Rhône-Alpes'),
('93', '13055', '0', 'PROVENCE ALPES COTE D AZUR', 'Provence-Alpes-Côte d\'Azur', 'Provence-Alpes-Côte d\'Azur'),
('94', '2A004', '0', 'CORSE', 'Corse', 'Corse');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
