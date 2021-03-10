-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Mer 10 Mars 2021 à 11:10
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
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `circos`
--

CREATE TABLE `circos` (
  `id` int(11) NOT NULL,
  `dpt` varchar(8) DEFAULT NULL,
  `dpt_nom` varchar(31) DEFAULT NULL,
  `commune` varchar(12) DEFAULT NULL,
  `commune_nom` varchar(45) DEFAULT NULL,
  `circo` varchar(21) DEFAULT NULL,
  `canton` varchar(11) DEFAULT NULL,
  `canton_nom` varchar(38) DEFAULT NULL,
  `commune_slug` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cities_infos`
--

CREATE TABLE `cities_infos` (
  `insee` varchar(25) DEFAULT NULL,
  `pop2017` int(11) DEFAULT NULL,
  `pop2012` int(11) DEFAULT NULL,
  `pop2007` int(11) DEFAULT NULL,
  `pop1999` int(11) DEFAULT NULL,
  `pop1990` int(11) DEFAULT NULL,
  `pop1982` int(11) DEFAULT NULL,
  `pop1975` int(11) DEFAULT NULL,
  `pop1968` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cities_mayors`
--

CREATE TABLE `cities_mayors` (
  `dpt` varchar(5) DEFAULT NULL,
  `libelle_dpt` text,
  `insee` smallint(6) DEFAULT NULL,
  `libelle_commune` text NOT NULL,
  `nameLast` text,
  `nameFirst` text,
  `gender` varchar(2) DEFAULT NULL,
  `birthDate` date DEFAULT NULL,
  `profession` smallint(6) DEFAULT NULL,
  `libelle_profession` text,
  `dateMaj` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `class_groups`
--

CREATE TABLE `class_groups` (
  `organeRef` varchar(15) CHARACTER SET utf8 NOT NULL,
  `legislature` int(5) NOT NULL,
  `cohesion` decimal(6,3) DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `participation` decimal(59,8) DEFAULT NULL,
  `majoriteAccord` decimal(14,4) DEFAULT NULL,
  `votesN` bigint(21) DEFAULT '0',
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `class_groups_proximite`
--

CREATE TABLE `class_groups_proximite` (
  `organeRef` varchar(15) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `prox_group` varchar(8) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `score` decimal(14,4) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL DEFAULT '0',
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `class_loyaute`
--

CREATE TABLE `class_loyaute` (
  `mpId` varchar(15) CHARACTER SET utf8 NOT NULL,
  `score` decimal(4,3) NOT NULL,
  `votesN` int(10) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `class_loyaute_six`
--

CREATE TABLE `class_loyaute_six` (
  `id` int(5) NOT NULL,
  `classement` int(5) NOT NULL,
  `mpId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `score` decimal(4,3) NOT NULL,
  `votesN` int(15) NOT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `class_majorite`
--

CREATE TABLE `class_majorite` (
  `mpId` varchar(50) CHARACTER SET utf8 NOT NULL,
  `score` double(20,3) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL DEFAULT '0',
  `legislature` int(5),
  `active` int(1) NOT NULL DEFAULT '0',
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `class_participation`
--

CREATE TABLE `class_participation` (
  `mpId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `score` decimal(13,2) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL DEFAULT '0',
  `index` decimal(21,0) DEFAULT NULL,
  `legislature` int(5),
  `active` int(1) NOT NULL DEFAULT '0',
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `class_participation_commission`
--

CREATE TABLE `class_participation_commission` (
  `mpId` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `score` decimal(13,2) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL DEFAULT '0',
  `index` decimal(21,0) DEFAULT NULL,
  `legislature` int(5),
  `active` int(1) NOT NULL DEFAULT '0',
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `class_participation_six`
--

CREATE TABLE `class_participation_six` (
  `id` int(5) NOT NULL,
  `classement` int(5) NOT NULL,
  `mpId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `score` decimal(3,2) NOT NULL,
  `votesN` int(15) NOT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `departement`
--

CREATE TABLE `departement` (
  `departement_id` int(11) NOT NULL,
  `departement_code` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `departement_nom` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `departement_slug` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `libelle_1` varchar(255) DEFAULT NULL,
  `libelle_2` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `deputes`
--

CREATE TABLE `deputes` (
  `mpId` varchar(35) NOT NULL,
  `civ` text,
  `nameFirst` text,
  `nameLast` text,
  `nameUrl` varchar(50) DEFAULT NULL,
  `birthDate` date DEFAULT NULL,
  `birthCity` text,
  `birthCountry` text,
  `job` text,
  `catSocPro` text,
  `dateMaj` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_accord`
--

CREATE TABLE `deputes_accord` (
  `uid` int(5) NOT NULL,
  `voteNumero` int(5) NOT NULL,
  `legislature` int(3) NOT NULL,
  `mpId` varchar(10) NOT NULL,
  `organeRef` varchar(10) NOT NULL,
  `accord` int(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_accord_cleaned`
--

CREATE TABLE `deputes_accord_cleaned` (
  `mpId` varchar(10) CHARACTER SET utf8 NOT NULL,
  `organeRef` varchar(10) CHARACTER SET utf8 NOT NULL,
  `accord` decimal(14,0) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL DEFAULT '0',
  `libelle` text CHARACTER SET utf8,
  `libelleAbrege` text CHARACTER SET utf8,
  `libelleAbrev` text CHARACTER SET utf8,
  `positionPolitique` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `ended` int(1) NOT NULL DEFAULT '0',
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_accord_old`
--

CREATE TABLE `deputes_accord_old` (
  `uid` int(5) NOT NULL,
  `voteNumero` int(5) NOT NULL,
  `legislature` int(3) NOT NULL,
  `mpId` varchar(10) NOT NULL,
  `vote` varchar(10) NOT NULL,
  `organeRef` varchar(10) NOT NULL,
  `accord` int(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_all`
--

CREATE TABLE `deputes_all` (
  `id` int(11) NOT NULL,
  `mpId` varchar(25) NOT NULL,
  `legislature` int(5) NOT NULL,
  `nameUrl` varchar(255) NOT NULL,
  `civ` varchar(15) NOT NULL,
  `nameFirst` text NOT NULL,
  `nameLast` text NOT NULL,
  `age` int(3) DEFAULT NULL,
  `dptSlug` varchar(255) NOT NULL,
  `departementNom` text NOT NULL,
  `departementCode` varchar(10) NOT NULL,
  `circo` varchar(5) NOT NULL,
  `mandatId` varchar(25) NOT NULL,
  `libelle` text,
  `libelleAbrev` text,
  `groupeId` varchar(25) DEFAULT NULL,
  `groupeMandat` varchar(15) DEFAULT NULL,
  `couleurAssociee` varchar(25) DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `datePriseFonction` date NOT NULL,
  `causeFin` text,
  `img` tinyint(1) NOT NULL,
  `imgOgp` tinyint(1) NOT NULL,
  `dateMaj` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_contacts`
--

CREATE TABLE `deputes_contacts` (
  `mpId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `mailAn` varchar(255) DEFAULT NULL,
  `mailPerso` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_last`
--

CREATE TABLE `deputes_last` (
  `id` int(11) NOT NULL DEFAULT '0',
  `mpId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `legislature` int(5) NOT NULL,
  `nameUrl` varchar(255) CHARACTER SET utf8 NOT NULL,
  `civ` varchar(15) CHARACTER SET utf8 NOT NULL,
  `nameFirst` text CHARACTER SET utf8 NOT NULL,
  `nameLast` text CHARACTER SET utf8 NOT NULL,
  `age` int(3) DEFAULT NULL,
  `dptSlug` varchar(255) CHARACTER SET utf8 NOT NULL,
  `departementNom` text CHARACTER SET utf8 NOT NULL,
  `departementCode` varchar(10) CHARACTER SET utf8 NOT NULL,
  `circo` varchar(5) CHARACTER SET utf8 NOT NULL,
  `mandatId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `libelle` text CHARACTER SET utf8,
  `libelleAbrev` text CHARACTER SET utf8,
  `groupeId` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `groupeMandat` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `couleurAssociee` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `datePriseFonction` date NOT NULL,
  `causeFin` text CHARACTER SET utf8,
  `img` tinyint(1) NOT NULL,
  `imgOgp` tinyint(1) NOT NULL,
  `dateMaj` date NOT NULL,
  `libelle_1` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `libelle_2` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_loyaute`
--

CREATE TABLE `deputes_loyaute` (
  `id` int(5) NOT NULL,
  `mpId` varchar(15) CHARACTER SET utf8 NOT NULL,
  `mandatId` varchar(15) CHARACTER SET utf8 NOT NULL,
  `score` decimal(4,3) NOT NULL,
  `votesN` int(10) NOT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `dossiers`
--

CREATE TABLE `dossiers` (
  `id` int(11) NOT NULL,
  `dossierId` varchar(100) NOT NULL,
  `legislature` int(5) NOT NULL,
  `titre` text NOT NULL,
  `titreChemin` varchar(300) NOT NULL,
  `senatChemin` varchar(500) DEFAULT NULL,
  `procedureParlementaireCode` int(5) DEFAULT NULL,
  `procedureParlementaireLibelle` text,
  `commissionFond` varchar(25) CHARACTER SET utf8mb4 DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `election2017_results`
--

CREATE TABLE `election2017_results` (
  `dpt` varchar(15) DEFAULT NULL,
  `circo` varchar(50) DEFAULT NULL,
  `nom` text,
  `nuance` text,
  `score` decimal(5,4) DEFAULT NULL,
  `sortant` text,
  `perso` text,
  `profession` text,
  `tour` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `elect_2017_pres_2`
--

CREATE TABLE `elect_2017_pres_2` (
  `dpt` varchar(5) DEFAULT NULL,
  `commune` int(11) DEFAULT NULL,
  `abs_pct` double DEFAULT NULL,
  `macron_n` int(11) DEFAULT NULL,
  `macron_pct` double DEFAULT NULL,
  `lePen_n` int(11) DEFAULT NULL,
  `lePen_pct` double DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `elect_2019_europe`
--

CREATE TABLE `elect_2019_europe` (
  `dpt` varchar(10) NOT NULL,
  `commune` varchar(10) NOT NULL,
  `abs_pct` varchar(10) DEFAULT NULL,
  `1_n` varchar(10) DEFAULT NULL,
  `1_pct` varchar(10) DEFAULT NULL,
  `2_n` varchar(10) DEFAULT NULL,
  `2_pct` varchar(10) DEFAULT NULL,
  `3_n` varchar(10) DEFAULT NULL,
  `3_pct` varchar(10) DEFAULT NULL,
  `4_n` varchar(10) DEFAULT NULL,
  `4_pct` varchar(10) DEFAULT NULL,
  `5_n` varchar(10) DEFAULT NULL,
  `5_pct` varchar(10) DEFAULT NULL,
  `6_n` varchar(10) DEFAULT NULL,
  `6_pct` varchar(10) DEFAULT NULL,
  `7_n` varchar(10) DEFAULT NULL,
  `7_pct` varchar(10) DEFAULT NULL,
  `8_n` varchar(10) DEFAULT NULL,
  `8_pct` varchar(10) DEFAULT NULL,
  `9_n` varchar(10) DEFAULT NULL,
  `9_pct` varchar(10) DEFAULT NULL,
  `10_n` varchar(10) DEFAULT NULL,
  `10_pct` varchar(10) DEFAULT NULL,
  `11_n` varchar(10) DEFAULT NULL,
  `11_pct` varchar(10) DEFAULT NULL,
  `12_n` varchar(10) DEFAULT NULL,
  `12_pct` varchar(10) DEFAULT NULL,
  `13_n` varchar(10) DEFAULT NULL,
  `13_pct` varchar(10) DEFAULT NULL,
  `14_n` varchar(10) DEFAULT NULL,
  `14_pct` varchar(10) DEFAULT NULL,
  `15_n` varchar(10) DEFAULT NULL,
  `15_pct` varchar(10) DEFAULT NULL,
  `16_n` varchar(10) DEFAULT NULL,
  `16_pct` varchar(10) DEFAULT NULL,
  `17_n` varchar(10) DEFAULT NULL,
  `17_pct` varchar(10) DEFAULT NULL,
  `18_n` varchar(10) DEFAULT NULL,
  `18_pct` varchar(10) DEFAULT NULL,
  `19_n` varchar(10) DEFAULT NULL,
  `19_pct` varchar(10) DEFAULT NULL,
  `20_n` varchar(10) DEFAULT NULL,
  `20_pct` varchar(10) DEFAULT NULL,
  `21_n` varchar(10) DEFAULT NULL,
  `21_pct` varchar(10) DEFAULT NULL,
  `22_n` varchar(10) DEFAULT NULL,
  `22_pct` varchar(10) DEFAULT NULL,
  `23_n` varchar(10) DEFAULT NULL,
  `23_pct` varchar(10) DEFAULT NULL,
  `24_n` varchar(10) DEFAULT NULL,
  `24_pct` varchar(10) DEFAULT NULL,
  `25_n` varchar(10) DEFAULT NULL,
  `25_pct` varchar(10) DEFAULT NULL,
  `26_n` varchar(10) DEFAULT NULL,
  `26_pct` varchar(10) DEFAULT NULL,
  `27_n` varchar(10) DEFAULT NULL,
  `27_pct` varchar(10) DEFAULT NULL,
  `28_n` varchar(10) DEFAULT NULL,
  `28_pct` varchar(10) DEFAULT NULL,
  `29_n` varchar(10) DEFAULT NULL,
  `29_pct` varchar(10) DEFAULT NULL,
  `30_n` varchar(10) DEFAULT NULL,
  `30_pct` varchar(10) DEFAULT NULL,
  `31_n` varchar(10) DEFAULT NULL,
  `31_pct` varchar(10) DEFAULT NULL,
  `32_n` varchar(10) DEFAULT NULL,
  `32_pct` varchar(10) DEFAULT NULL,
  `33_n` varchar(10) DEFAULT NULL,
  `33_pct` varchar(10) DEFAULT NULL,
  `34_n` varchar(10) DEFAULT NULL,
  `34_pct` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `elect_2019_europe_clean`
--

CREATE TABLE `elect_2019_europe_clean` (
  `dpt` varchar(10) NOT NULL DEFAULT '',
  `commune` varchar(10) NOT NULL DEFAULT '',
  `party` varchar(2) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `value` decimal(5,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `elect_2019_europe_listes`
--

CREATE TABLE `elect_2019_europe_listes` (
  `id` int(11) DEFAULT NULL,
  `id_n` text,
  `id_pct` text,
  `name` text,
  `tete` text,
  `parti` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `fields`
--

CREATE TABLE `fields` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `libelle` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `groupes_accord`
--

CREATE TABLE `groupes_accord` (
  `id` int(3) UNSIGNED NOT NULL,
  `voteNumero` int(6) NOT NULL,
  `legislature` tinyint(2) NOT NULL,
  `organeRef` varchar(15) CHARACTER SET utf8 NOT NULL,
  `organeRefAccord` varchar(15) CHARACTER SET utf8 NOT NULL,
  `accord` tinyint(2) DEFAULT NULL,
  `dateMaj` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `groupes_cohesion`
--

CREATE TABLE `groupes_cohesion` (
  `id` int(5) NOT NULL,
  `voteNumero` int(5) NOT NULL,
  `legislature` int(5) NOT NULL,
  `organeRef` varchar(15) NOT NULL,
  `cohesion` decimal(5,3) DEFAULT NULL,
  `positionGroupe` varchar(5) NOT NULL,
  `voteSort` int(10) DEFAULT NULL,
  `scoreGagnant` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `groupes_effectif`
--

CREATE TABLE `groupes_effectif` (
  `classement` double DEFAULT NULL,
  `organeRef` varchar(255) CHARACTER SET utf8 NOT NULL,
  `libelle` text CHARACTER SET utf8,
  `effectif` bigint(21) NOT NULL DEFAULT '0',
  `legislature` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `groupes_stats`
--

CREATE TABLE `groupes_stats` (
  `organeRef` varchar(15) CHARACTER SET utf8 NOT NULL,
  `womenPct` decimal(4,2) DEFAULT NULL,
  `womenN` int(3) DEFAULT NULL,
  `age` decimal(4,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `history_mps_average`
--

CREATE TABLE `history_mps_average` (
  `length` decimal(29,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `history_per_mps_average`
--

CREATE TABLE `history_per_mps_average` (
  `mpId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `mpLength` decimal(28,0) DEFAULT NULL,
  `mandatesN` bigint(21) NOT NULL DEFAULT '0',
  `lengthEdited` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `insee`
--

CREATE TABLE `insee` (
  `id` int(6) UNSIGNED NOT NULL,
  `insee` varchar(30) NOT NULL,
  `postal` varchar(30) NOT NULL,
  `dpt` varchar(5) DEFAULT NULL,
  `region` varchar(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `legislature`
--

CREATE TABLE `legislature` (
  `id` int(3) UNSIGNED NOT NULL,
  `organeRef` varchar(30) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `libelleAbrev` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `legislatureNumber` tinyint(1) NOT NULL,
  `dateDebut` date NOT NULL,
  `dateFin` date DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `mandat_groupe`
--

CREATE TABLE `mandat_groupe` (
  `mandatId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `mpId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `legislature` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `typeOrgane` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `dateDebut` date NOT NULL,
  `dateFin` date DEFAULT NULL,
  `preseance` varchar(255) CHARACTER SET utf8 NOT NULL,
  `nominPrincipale` varchar(255) CHARACTER SET utf8 NOT NULL,
  `codeQualite` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `libQualiteSex` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `organeRef` varchar(255) CHARACTER SET utf8 NOT NULL,
  `dateMaj` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `mandat_principal`
--

CREATE TABLE `mandat_principal` (
  `mandatId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `mpId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `legislature` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `typeOrgane` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `dateDebut` date NOT NULL,
  `dateFin` date DEFAULT NULL,
  `preseance` varchar(255) CHARACTER SET utf8 NOT NULL,
  `nominPrincipale` varchar(255) CHARACTER SET utf8 NOT NULL,
  `codeQualite` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `libQualiteSex` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `organe` varchar(255) CHARACTER SET utf8 NOT NULL,
  `electionType` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `electionRegion` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `electionRegionType` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `electionDepartement` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `electionDepartementNumero` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `electionCirco` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `datePriseFonction` date DEFAULT NULL,
  `causeFin` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `premiereElection` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `placeHemicyle` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `dateMaj` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `mandat_secondaire`
--

CREATE TABLE `mandat_secondaire` (
  `mandatId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `mpId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `legislature` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `typeOrgane` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `dateDebut` date NOT NULL,
  `dateFin` date DEFAULT NULL,
  `preseance` varchar(255) CHARACTER SET utf8 NOT NULL,
  `nominPrincipale` varchar(255) CHARACTER SET utf8 NOT NULL,
  `codeQualite` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `libQualiteSex` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `organeRef` varchar(255) CHARACTER SET utf8 NOT NULL,
  `dateMaj` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `organes`
--

CREATE TABLE `organes` (
  `uid` varchar(50) NOT NULL,
  `coteType` text,
  `libelle` text NOT NULL,
  `libelleEdition` text NOT NULL,
  `libelleAbrev` text NOT NULL,
  `libelleAbrege` text NOT NULL,
  `dateDebut` date DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `regime` text,
  `legislature` varchar(20) DEFAULT NULL,
  `positionPolitique` varchar(25) DEFAULT NULL,
  `preseance` varchar(5) DEFAULT NULL,
  `couleurAssociee` varchar(20) DEFAULT NULL,
  `dateMaj` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `parties`
--

CREATE TABLE `parties` (
  `uid` varchar(50) CHARACTER SET utf8 NOT NULL,
  `libelleAbrev` text CHARACTER SET utf8 NOT NULL,
  `libelle` text CHARACTER SET utf8 NOT NULL,
  `dateFin` date DEFAULT NULL,
  `effectifTotal` bigint(21) NOT NULL DEFAULT '0',
  `effectif` bigint(21) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,
  `state` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `regions_old_new`
--

CREATE TABLE `regions_old_new` (
  `new_code` int(11) DEFAULT NULL,
  `former_code` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `zipcode` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `mpId` varchar(20) NOT NULL,
  `vote` varchar(50) DEFAULT NULL,
  `voteNumero` int(20) NOT NULL,
  `voteId` varchar(30) NOT NULL,
  `legislature` int(20) NOT NULL,
  `mandatId` varchar(15) NOT NULL,
  `parDelegation` varchar(10) DEFAULT NULL,
  `causePosition` varchar(10) DEFAULT NULL,
  `voteType` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_datan`
--

CREATE TABLE `votes_datan` (
  `id` int(11) NOT NULL,
  `vote_id` varchar(50) NOT NULL,
  `title` text NOT NULL,
  `slug` varchar(160) NOT NULL,
  `category` text NOT NULL,
  `description` text NOT NULL,
  `contexte` text NOT NULL,
  `state` varchar(25) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `modified_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(10) DEFAULT NULL,
  `modified_by` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_dossiers`
--

CREATE TABLE `votes_dossiers` (
  `id` int(11) NOT NULL,
  `offset_num` int(11) NOT NULL,
  `voteNumero` int(11) NOT NULL,
  `href` text,
  `dossier` varchar(300) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_groupes`
--

CREATE TABLE `votes_groupes` (
  `id` int(11) NOT NULL,
  `voteId` varchar(50) NOT NULL,
  `voteNumero` int(20) NOT NULL,
  `legislature` int(20) NOT NULL,
  `organeRef` varchar(50) NOT NULL,
  `nombreMembresGroupe` int(50) NOT NULL,
  `positionMajoritaire` text NOT NULL,
  `nombrePours` int(50) NOT NULL,
  `nombreContres` int(50) NOT NULL,
  `nombreAbstentions` int(50) NOT NULL,
  `nonVotants` int(10) NOT NULL,
  `nonVotantsVolontaires` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_info`
--

CREATE TABLE `votes_info` (
  `id` int(11) NOT NULL,
  `voteId` varchar(50) NOT NULL,
  `voteNumero` int(50) NOT NULL,
  `organeRef` varchar(50) NOT NULL,
  `legislature` int(50) NOT NULL,
  `sessionREF` varchar(100) NOT NULL,
  `seanceRef` varchar(255) NOT NULL,
  `dateScrutin` date NOT NULL,
  `quantiemeJourSeance` int(50) NOT NULL,
  `codeTypeVote` varchar(50) NOT NULL,
  `libelleTypeVote` varchar(255) NOT NULL,
  `typeMajorite` varchar(350) NOT NULL,
  `sortCode` varchar(100) NOT NULL,
  `titre` text NOT NULL,
  `demandeur` text NOT NULL,
  `modePublicationDesVotes` text NOT NULL,
  `nombreVotants` int(50) NOT NULL,
  `suffragesExprimes` int(50) NOT NULL,
  `nbrSuffragesRequis` int(50) NOT NULL,
  `decomptePour` int(50) NOT NULL,
  `decompteContre` int(50) NOT NULL,
  `decompteAbs` int(50) NOT NULL,
  `decompteNv` int(50) NOT NULL,
  `voteType` varchar(250) DEFAULT NULL,
  `amdt` varchar(25) DEFAULT NULL,
  `article` varchar(25) DEFAULT NULL,
  `bister` varchar(15) DEFAULT NULL,
  `posArticle` varchar(15) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_participation`
--

CREATE TABLE `votes_participation` (
  `id` int(11) NOT NULL,
  `legislature` int(15) NOT NULL,
  `voteNumero` int(15) NOT NULL,
  `mpId` varchar(25) NOT NULL,
  `participation` int(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_participation_commission`
--

CREATE TABLE `votes_participation_commission` (
  `id` int(11) NOT NULL,
  `legislature` int(15) NOT NULL,
  `voteNumero` int(15) NOT NULL,
  `mpId` varchar(25) DEFAULT NULL,
  `participation` int(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_scores`
--

CREATE TABLE `votes_scores` (
  `id` int(5) NOT NULL,
  `voteId` varchar(50) CHARACTER SET utf8 NOT NULL,
  `voteNumero` int(5) NOT NULL,
  `legislature` int(5) NOT NULL,
  `mpId` varchar(50) CHARACTER SET utf8 NOT NULL,
  `vote` varchar(5) CHARACTER SET utf8 NOT NULL,
  `typeVote` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `organeId` varchar(50) CHARACTER SET utf8 NOT NULL,
  `dateVote` date NOT NULL,
  `positionGroup` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `scoreLoyaute` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `scoreGagnant` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `scoreParticipation` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `positionGvt` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `scoreGvt` varchar(5) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `circos`
--
ALTER TABLE `circos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dpt` (`dpt`),
  ADD KEY `idx_commune_nom` (`commune_nom`) USING BTREE;

--
-- Index pour la table `cities_infos`
--
ALTER TABLE `cities_infos`
  ADD KEY `idx_insee` (`insee`);

--
-- Index pour la table `cities_mayors`
--
ALTER TABLE `cities_mayors`
  ADD KEY `idx_dpt` (`dpt`),
  ADD KEY `idx_insee` (`insee`);

--
-- Index pour la table `class_groups`
--
ALTER TABLE `class_groups`
  ADD KEY `idx_organeRef` (`organeRef`),
  ADD KEY `idx_active` (`active`);

--
-- Index pour la table `class_groups_proximite`
--
ALTER TABLE `class_groups_proximite`
  ADD KEY `idx_organeRef` (`organeRef`);

--
-- Index pour la table `class_loyaute`
--
ALTER TABLE `class_loyaute`
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_active` (`active`);

--
-- Index pour la table `class_loyaute_six`
--
ALTER TABLE `class_loyaute_six`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mpId` (`mpId`);

--
-- Index pour la table `class_majorite`
--
ALTER TABLE `class_majorite`
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_active` (`active`);

--
-- Index pour la table `class_participation`
--
ALTER TABLE `class_participation`
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_active` (`active`);

--
-- Index pour la table `class_participation_commission`
--
ALTER TABLE `class_participation_commission`
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_active` (`active`);

--
-- Index pour la table `class_participation_six`
--
ALTER TABLE `class_participation_six`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mpId` (`mpId`);

--
-- Index pour la table `departement`
--
ALTER TABLE `departement`
  ADD PRIMARY KEY (`departement_id`),
  ADD KEY `departement_slug` (`departement_slug`),
  ADD KEY `departement_code` (`departement_code`);

--
-- Index pour la table `deputes_accord`
--
ALTER TABLE `deputes_accord`
  ADD PRIMARY KEY (`uid`);

--
-- Index pour la table `deputes_accord_cleaned`
--
ALTER TABLE `deputes_accord_cleaned`
  ADD KEY `idx_mpId` (`mpId`);

--
-- Index pour la table `deputes_accord_old`
--
ALTER TABLE `deputes_accord_old`
  ADD PRIMARY KEY (`uid`);

--
-- Index pour la table `deputes_all`
--
ALTER TABLE `deputes_all`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ids_legislature` (`legislature`),
  ADD KEY `idx_mpId` (`mpId`);

--
-- Index pour la table `deputes_contacts`
--
ALTER TABLE `deputes_contacts`
  ADD PRIMARY KEY (`mpId`);

--
-- Index pour la table `deputes_last`
--
ALTER TABLE `deputes_last`
  ADD KEY `idx_mp` (`nameUrl`),
  ADD KEY `idx_dptSlug` (`dptSlug`),
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `deputes_loyaute`
--
ALTER TABLE `deputes_loyaute`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_mandatId` (`mandatId`);

--
-- Index pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_titreChemin` (`titreChemin`);

--
-- Index pour la table `election2017_results`
--
ALTER TABLE `election2017_results`
  ADD KEY `idx_dpt` (`dpt`),
  ADD KEY `idx_circo` (`circo`);

--
-- Index pour la table `elect_2017_pres_2`
--
ALTER TABLE `elect_2017_pres_2`
  ADD KEY `idx_dpt` (`dpt`),
  ADD KEY `idx_commune` (`commune`);

--
-- Index pour la table `elect_2019_europe_clean`
--
ALTER TABLE `elect_2019_europe_clean`
  ADD KEY `idx_dpt` (`dpt`),
  ADD KEY `idx_commune` (`commune`);

--
-- Index pour la table `elect_2019_europe_listes`
--
ALTER TABLE `elect_2019_europe_listes`
  ADD KEY `idx_id` (`id`);

--
-- Index pour la table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `groupes_accord`
--
ALTER TABLE `groupes_accord`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_organeRef` (`organeRef`),
  ADD KEY `idx_organeRefAccord` (`organeRefAccord`);

--
-- Index pour la table `groupes_cohesion`
--
ALTER TABLE `groupes_cohesion`
  ADD KEY `idx_id` (`id`);

--
-- Index pour la table `groupes_effectif`
--
ALTER TABLE `groupes_effectif`
  ADD KEY `idx_organeRef` (`organeRef`);

--
-- Index pour la table `history_per_mps_average`
--
ALTER TABLE `history_per_mps_average`
  ADD KEY `idx_mpId` (`mpId`);

--
-- Index pour la table `insee`
--
ALTER TABLE `insee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_insee` (`id`);

--
-- Index pour la table `legislature`
--
ALTER TABLE `legislature`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mandat_groupe`
--
ALTER TABLE `mandat_groupe`
  ADD KEY `idx_organeRef` (`organeRef`),
  ADD KEY `idx_mandatId` (`mandatId`) USING BTREE,
  ADD KEY `idx_mpId` (`mpId`) USING BTREE;

--
-- Index pour la table `mandat_principal`
--
ALTER TABLE `mandat_principal`
  ADD KEY `idx_typeOrgane` (`typeOrgane`),
  ADD KEY `idx_legislature` (`legislature`),
  ADD KEY `idx_mpId` (`mpId`) USING BTREE;

--
-- Index pour la table `mandat_secondaire`
--
ALTER TABLE `mandat_secondaire`
  ADD KEY `idx_typeOrgane` (`typeOrgane`),
  ADD KEY `idx_mpId` (`mpId`) USING BTREE;

--
-- Index pour la table `organes`
--
ALTER TABLE `organes`
  ADD KEY `idx_uid` (`uid`);

--
-- Index pour la table `parties`
--
ALTER TABLE `parties`
  ADD KEY `idx_uid` (`uid`);

--
-- Index pour la table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `regions_old_new`
--
ALTER TABLE `regions_old_new`
  ADD KEY `idx_former_code` (`former_code`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `votes`
--
ALTER TABLE `votes`
  ADD KEY `i1` (`id`),
  ADD KEY `idx_voteNumero` (`voteNumero`),
  ADD KEY `idx_voteId` (`voteId`) USING BTREE,
  ADD KEY `idx_mpId` (`mpId`) USING BTREE;

--
-- Index pour la table `votes_datan`
--
ALTER TABLE `votes_datan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vote_id` (`vote_id`);

--
-- Index pour la table `votes_dossiers`
--
ALTER TABLE `votes_dossiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_voteNumero` (`voteNumero`) USING BTREE;

--
-- Index pour la table `votes_groupes`
--
ALTER TABLE `votes_groupes`
  ADD KEY `idx_id` (`id`),
  ADD KEY `idx_organeRef` (`organeRef`),
  ADD KEY `idx_voteNumero` (`voteNumero`) USING BTREE;

--
-- Index pour la table `votes_info`
--
ALTER TABLE `votes_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_voteNumero` (`voteNumero`),
  ADD KEY `idx_legislature` (`legislature`),
  ADD KEY `idx_voteId` (`voteId`) USING BTREE,
  ADD KEY `idx_voteType` (`voteType`) USING BTREE;

--
-- Index pour la table `votes_participation`
--
ALTER TABLE `votes_participation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_voteNumero` (`voteNumero`);

--
-- Index pour la table `votes_participation_commission`
--
ALTER TABLE `votes_participation_commission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mpId` (`mpId`);

--
-- Index pour la table `votes_scores`
--
ALTER TABLE `votes_scores`
  ADD KEY `idx_id` (`id`),
  ADD KEY `idx_loyaute` (`scoreLoyaute`),
  ADD KEY `idx_deputeId_numero` (`mpId`,`voteNumero`),
  ADD KEY `idx_mpId` (`mpId`) USING BTREE,
  ADD KEY `idx_voteNumero` (`voteNumero`) USING BTREE;

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT pour la table `circos`
--
ALTER TABLE `circos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36468;
--
-- AUTO_INCREMENT pour la table `class_loyaute_six`
--
ALTER TABLE `class_loyaute_six`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=416;
--
-- AUTO_INCREMENT pour la table `class_participation_six`
--
ALTER TABLE `class_participation_six`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;
--
-- AUTO_INCREMENT pour la table `departement`
--
ALTER TABLE `departement`
  MODIFY `departement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;
--
-- AUTO_INCREMENT pour la table `deputes_accord`
--
ALTER TABLE `deputes_accord`
  MODIFY `uid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3201272;
--
-- AUTO_INCREMENT pour la table `deputes_accord_old`
--
ALTER TABLE `deputes_accord_old`
  MODIFY `uid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3285263;
--
-- AUTO_INCREMENT pour la table `deputes_all`
--
ALTER TABLE `deputes_all`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2527;
--
-- AUTO_INCREMENT pour la table `deputes_loyaute`
--
ALTER TABLE `deputes_loyaute`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=698;
--
-- AUTO_INCREMENT pour la table `dossiers`
--
ALTER TABLE `dossiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3755;
--
-- AUTO_INCREMENT pour la table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT pour la table `groupes_accord`
--
ALTER TABLE `groupes_accord`
  MODIFY `id` int(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=277169;
--
-- AUTO_INCREMENT pour la table `groupes_cohesion`
--
ALTER TABLE `groupes_cohesion`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59588;
--
-- AUTO_INCREMENT pour la table `insee`
--
ALTER TABLE `insee`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36742;
--
-- AUTO_INCREMENT pour la table `legislature`
--
ALTER TABLE `legislature`
  MODIFY `id` int(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT pour la table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=710549;
--
-- AUTO_INCREMENT pour la table `votes_datan`
--
ALTER TABLE `votes_datan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
--
-- AUTO_INCREMENT pour la table `votes_dossiers`
--
ALTER TABLE `votes_dossiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3401;
--
-- AUTO_INCREMENT pour la table `votes_groupes`
--
ALTER TABLE `votes_groupes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59588;
--
-- AUTO_INCREMENT pour la table `votes_info`
--
ALTER TABLE `votes_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6709;
--
-- AUTO_INCREMENT pour la table `votes_participation`
--
ALTER TABLE `votes_participation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1636934;
--
-- AUTO_INCREMENT pour la table `votes_participation_commission`
--
ALTER TABLE `votes_participation_commission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248816;
--
-- AUTO_INCREMENT pour la table `votes_scores`
--
ALTER TABLE `votes_scores`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=878652;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
