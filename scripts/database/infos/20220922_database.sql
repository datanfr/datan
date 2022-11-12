/*  This is a summy of all tables in the Datan database */


-- Généré le : jeu. 22 sep. 2022 à 08:43

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `Datan`
--

-- --------------------------------------------------------

--
-- Structure de la table `amendements`
--

CREATE TABLE `amendements` (
  `id` varchar(55) CHARACTER SET utf8 NOT NULL,
  `dossier` varchar(25) CHARACTER SET utf8 NOT NULL,
  `legislature` int(5) NOT NULL,
  `texteLegislatifRef` varchar(35) CHARACTER SET utf8 NOT NULL,
  `num` varchar(55) CHARACTER SET utf8 NOT NULL,
  `numOrdre` varchar(15) CHARACTER SET utf8 NOT NULL,
  `seanceRef` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `expose` text CHARACTER SET utf8 DEFAULT NULL,
  `dateMaj` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `amendements_auteurs`
--

CREATE TABLE `amendements_auteurs` (
  `id` varchar(55) CHARACTER SET utf8 NOT NULL,
  `type` varchar(15) CHARACTER SET utf8 NOT NULL,
  `acteurRef` varchar(15) CHARACTER SET utf8 NOT NULL,
  `groupeId` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `auteurOrgane` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `dateMaj` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `candidate_full`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `candidate_full` (
`mpId` varchar(15)
,`election` tinyint(4)
,`district` varchar(5)
,`candidature` int(5)
,`position` varchar(50)
,`nuance` varchar(25)
,`source` text
,`link` varchar(1000)
,`visible` tinyint(1)
,`secondRound` tinyint(1)
,`elected` tinyint(1)
,`legislature` int(5)
,`nameUrl` varchar(255)
,`civ` varchar(15)
,`nameFirst` text
,`nameLast` text
,`age` int(3)
,`dptSlug` varchar(255)
,`departementNom` text
,`departementCode` varchar(10)
,`circo` varchar(5)
,`mandatId` varchar(25)
,`depute_libelle` text
,`depute_libelleAbrev` text
,`groupeId` varchar(25)
,`groupeMandat` varchar(15)
,`couleurAssociee` varchar(25)
,`dateFin` date
,`datePriseFonction` date
,`causeFin` text
,`img` tinyint(1)
,`imgOgp` tinyint(1)
,`dateMaj` date
,`libelle_1` varchar(255)
,`libelle_2` varchar(255)
,`active` int(1)
,`election_id` int(11)
,`election_libelle` varchar(255)
,`election_libelleAbrev` varchar(255)
,`dateYear` year(4)
,`dateFirstRound` date
,`dateSecondRound` date
,`modified_at` datetime
);

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
  `dpt_nom` varchar(45) DEFAULT NULL,
  `commune` varchar(12) DEFAULT NULL,
  `insee` varchar(15) DEFAULT NULL,
  `commune_nom` varchar(45) DEFAULT NULL,
  `circo` varchar(21) DEFAULT NULL,
  `canton` varchar(11) DEFAULT NULL,
  `canton_nom` varchar(38) DEFAULT NULL,
  `commune_slug` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cities_adjacentes`
--

CREATE TABLE `cities_adjacentes` (
  `insee` varchar(25) NOT NULL,
  `adjacente` varchar(25) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  `libelle_dpt` text DEFAULT NULL,
  `insee` varchar(6) DEFAULT NULL,
  `libelle_commune` text NOT NULL,
  `nameLast` text DEFAULT NULL,
  `nameFirst` text DEFAULT NULL,
  `gender` varchar(2) DEFAULT NULL,
  `birthDate` date DEFAULT NULL,
  `profession` smallint(6) DEFAULT NULL,
  `libelle_profession` text DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `class_groups`
--

CREATE TABLE `class_groups` (
  `organeRef` varchar(15) NOT NULL,
  `legislature` int(5) NOT NULL,
  `active` int(1) NOT NULL DEFAULT 0,
  `stat` varchar(25) NOT NULL,
  `value` decimal(6,3) DEFAULT NULL,
  `votes` bigint(21) DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `class_groups_proximite`
--

CREATE TABLE `class_groups_proximite` (
  `organeRef` varchar(15) CHARACTER SET utf8 NOT NULL,
  `legislature` tinyint(2) NOT NULL,
  `prox_group` varchar(15) CHARACTER SET utf8 NOT NULL,
  `score` decimal(7,4) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `class_loyaute`
--

CREATE TABLE `class_loyaute` (
  `mpId` varchar(50) CHARACTER SET utf8 NOT NULL,
  `score` double(20,3) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL,
  `legislature` int(5) NOT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `class_majorite`
--

CREATE TABLE `class_majorite` (
  `mpId` varchar(50) CHARACTER SET utf8 NOT NULL,
  `legislature` int(5) NOT NULL,
  `score` double(20,3) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL DEFAULT 0,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `class_participation`
--

CREATE TABLE `class_participation` (
  `mpId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `legislature` int(15) NOT NULL,
  `score` decimal(13,2) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL DEFAULT 0,
  `index` decimal(21,0) DEFAULT NULL,
  `active` int(1) NOT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `class_participation_commission`
--

CREATE TABLE `class_participation_commission` (
  `mpId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `legislature` int(15) NOT NULL,
  `score` decimal(13,2) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL DEFAULT 0,
  `index` decimal(21,0) DEFAULT NULL,
  `active` int(1) NOT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `class_participation_six`
--

CREATE TABLE `class_participation_six` (
  `classement` double DEFAULT NULL,
  `mpId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `score` decimal(13,2) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL,
  `index` decimal(21,0) DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `class_participation_solennels`
--

CREATE TABLE `class_participation_solennels` (
  `mpId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `legislature` int(15) NOT NULL,
  `score` decimal(13,2) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL DEFAULT 0,
  `index` decimal(21,0) DEFAULT NULL,
  `active` int(1) NOT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `departement`
--

CREATE TABLE `departement` (
  `departement_id` int(11) NOT NULL,
  `departement_code` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `departement_nom` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `departement_slug` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `departement_nom_soundex` varchar(20) DEFAULT NULL,
  `libelle_1` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `libelle_2` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `region` varchar(255) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `deputes`
--

CREATE TABLE `deputes` (
  `mpId` varchar(35) NOT NULL,
  `civ` text DEFAULT NULL,
  `nameFirst` text DEFAULT NULL,
  `nameLast` text DEFAULT NULL,
  `nameUrl` varchar(50) DEFAULT NULL,
  `birthDate` date DEFAULT NULL,
  `birthCity` text DEFAULT NULL,
  `birthCountry` text DEFAULT NULL,
  `job` text DEFAULT NULL,
  `catSocPro` text DEFAULT NULL,
  `famSocPro` varchar(255) DEFAULT NULL,
  `hatvp` text DEFAULT NULL,
  `dateMaj` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_accord`
--

CREATE TABLE `deputes_accord` (
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
  `legislature` int(3) NOT NULL,
  `organeRef` varchar(10) CHARACTER SET utf8 NOT NULL,
  `accord` decimal(14,0) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_all`
--

CREATE TABLE `deputes_all` (
  `mpId` varchar(25) NOT NULL,
  `legislature` int(5) NOT NULL,
  `nameUrl` varchar(255) NOT NULL,
  `civ` varchar(15) NOT NULL,
  `nameFirst` text NOT NULL,
  `nameLast` text NOT NULL,
  `age` int(3) DEFAULT NULL,
  `job` varchar(255) DEFAULT NULL,
  `catSocPro` varchar(255) DEFAULT NULL,
  `famSocPro` varchar(255) DEFAULT NULL,
  `dptSlug` varchar(255) NOT NULL,
  `departementNom` text NOT NULL,
  `departementCode` varchar(10) NOT NULL,
  `circo` varchar(5) NOT NULL,
  `mandatId` varchar(25) NOT NULL,
  `libelle` text DEFAULT NULL,
  `libelleAbrev` text DEFAULT NULL,
  `groupeId` varchar(25) DEFAULT NULL,
  `groupeMandat` varchar(15) DEFAULT NULL,
  `couleurAssociee` varchar(25) DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `datePriseFonction` date NOT NULL,
  `causeFin` text DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_last`
--

CREATE TABLE `deputes_last` (
  `mpId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `legislature` int(5) NOT NULL,
  `nameUrl` varchar(255) CHARACTER SET utf8 NOT NULL,
  `civ` varchar(15) CHARACTER SET utf8 NOT NULL,
  `nameFirst` text CHARACTER SET utf8 NOT NULL,
  `nameLast` text CHARACTER SET utf8 NOT NULL,
  `age` int(3) DEFAULT NULL,
  `job` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `catSocPro` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `famSocPro` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `dptSlug` varchar(255) CHARACTER SET utf8 NOT NULL,
  `departementNom` text CHARACTER SET utf8 NOT NULL,
  `departementCode` varchar(10) CHARACTER SET utf8 NOT NULL,
  `circo` varchar(5) CHARACTER SET utf8 NOT NULL,
  `mandatId` varchar(25) CHARACTER SET utf8 NOT NULL,
  `libelle` text CHARACTER SET utf8 DEFAULT NULL,
  `libelleAbrev` text CHARACTER SET utf8 DEFAULT NULL,
  `groupeId` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `groupeMandat` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `couleurAssociee` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `datePriseFonction` date NOT NULL,
  `causeFin` text CHARACTER SET utf8 DEFAULT NULL,
  `img` tinyint(1) NOT NULL,
  `imgOgp` tinyint(1) NOT NULL,
  `dateMaj` date NOT NULL,
  `libelle_1` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `libelle_2` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `active` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `deputes_loyaute`
--

CREATE TABLE `deputes_loyaute` (
  `mpId` varchar(50) CHARACTER SET utf8 NOT NULL,
  `mandatId` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `score` double(20,3) DEFAULT NULL,
  `votesN` bigint(21) NOT NULL,
  `legislature` int(5) NOT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `documents_legislatifs`
--

CREATE TABLE `documents_legislatifs` (
  `id` varchar(55) CHARACTER SET utf8 NOT NULL,
  `dossierId` varchar(15) CHARACTER SET utf8 NOT NULL,
  `numNotice` int(10) DEFAULT NULL,
  `titre` text CHARACTER SET utf8 NOT NULL,
  `titreCourt` text CHARACTER SET utf8 NOT NULL,
  `dateMaj` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
  `procedureParlementaireLibelle` text DEFAULT NULL,
  `commissionFond` varchar(25) CHARACTER SET utf8mb4 DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `dossiers_acteurs`
--

CREATE TABLE `dossiers_acteurs` (
  `id` varchar(25) CHARACTER SET utf8 NOT NULL,
  `legislature` int(5) NOT NULL,
  `etape` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `value` varchar(25) CHARACTER SET utf8 NOT NULL,
  `type` varchar(25) CHARACTER SET utf8 NOT NULL,
  `ref` varchar(25) CHARACTER SET utf8 NOT NULL,
  `mandate` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `dateMaj` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
  `id_n` text DEFAULT NULL,
  `id_pct` text DEFAULT NULL,
  `name` text DEFAULT NULL,
  `tete` text DEFAULT NULL,
  `parti` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `elect_deputes_candidats`
--

CREATE TABLE `elect_deputes_candidats` (
  `mpId` varchar(15) NOT NULL,
  `election` tinyint(4) NOT NULL,
  `candidature` int(5) DEFAULT 1,
  `district` varchar(5) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `nuance` varchar(25) DEFAULT NULL,
  `source` text DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 0,
  `secondRound` tinyint(1) DEFAULT NULL,
  `elected` tinyint(1) DEFAULT NULL,
  `link` varchar(1000) DEFAULT NULL,
  `modified_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `elect_legislatives_cities`
--

CREATE TABLE `elect_legislatives_cities` (
  `year` double DEFAULT NULL,
  `tour` double DEFAULT NULL,
  `dpt` varchar(3) DEFAULT NULL,
  `circo` int(11) DEFAULT NULL,
  `commune` varchar(3) DEFAULT NULL,
  `insee` varchar(5) DEFAULT NULL,
  `inscrits` int(11) DEFAULT NULL,
  `abs` int(11) DEFAULT NULL,
  `votants` int(11) DEFAULT NULL,
  `blancs` int(11) DEFAULT NULL,
  `nuls` int(11) DEFAULT NULL,
  `exprimes` int(11) DEFAULT NULL,
  `candidate` varchar(3) DEFAULT NULL,
  `sexe` varchar(1) DEFAULT NULL,
  `nom` varchar(21) DEFAULT NULL,
  `prenom` varchar(16) DEFAULT NULL,
  `nuance` varchar(3) DEFAULT NULL,
  `voix` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `elect_legislatives_infos`
--

CREATE TABLE `elect_legislatives_infos` (
  `dpt` varchar(3) DEFAULT NULL,
  `dpt_url` varchar(3) DEFAULT NULL,
  `circo` int(11) DEFAULT NULL,
  `circo_url` varchar(2) DEFAULT NULL,
  `tour` int(11) DEFAULT NULL,
  `inscrits` int(11) DEFAULT NULL,
  `abstentions` int(11) DEFAULT NULL,
  `votants` int(11) DEFAULT NULL,
  `blancs` int(11) DEFAULT NULL,
  `nuls` int(11) DEFAULT NULL,
  `exprimes` int(11) DEFAULT NULL,
  `year` double DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `elect_legislatives_results`
--

CREATE TABLE `elect_legislatives_results` (
  `year` double DEFAULT NULL,
  `dpt` varchar(3) DEFAULT NULL,
  `dpt_url` varchar(3) DEFAULT NULL,
  `circo` int(11) DEFAULT NULL,
  `circo_url` varchar(2) DEFAULT NULL,
  `tour` double DEFAULT NULL,
  `nuance` varchar(3) DEFAULT NULL,
  `candidat` varchar(35) DEFAULT NULL,
  `voix` int(11) DEFAULT NULL,
  `pct_inscrits` double DEFAULT NULL,
  `pct_exprimes` double DEFAULT NULL,
  `elected` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `elect_libelle`
--

CREATE TABLE `elect_libelle` (
  `id` int(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `libelleAbrev` varchar(255) NOT NULL,
  `dateYear` year(4) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `dateFirstRound` date NOT NULL,
  `dateSecondRound` date DEFAULT NULL,
  `candidates` tinyint(1) DEFAULT NULL,
  `resultsUrl` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `elect_pres_2`
--

CREATE TABLE `elect_pres_2` (
  `dpt` varchar(11) DEFAULT NULL,
  `commune` varchar(11) DEFAULT NULL,
  `abs_pct` double DEFAULT NULL,
  `votants` int(11) DEFAULT NULL,
  `candidate` varchar(25) DEFAULT NULL,
  `voix` int(11) DEFAULT NULL,
  `share` double DEFAULT NULL,
  `election` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `famsocpro`
--

CREATE TABLE `famsocpro` (
  `id` int(11) NOT NULL,
  `famille` varchar(49) DEFAULT NULL,
  `population` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `faq_categories`
--

CREATE TABLE `faq_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `faq_posts`
--

CREATE TABLE `faq_posts` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `slug` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `category` int(11) NOT NULL,
  `state` varchar(15) NOT NULL,
  `created_by` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_by` varchar(10) DEFAULT NULL,
  `modified_at` timestamp NULL DEFAULT NULL
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
  `voteNumero` int(6) NOT NULL,
  `legislature` tinyint(2) NOT NULL,
  `organeRef` varchar(15) CHARACTER SET utf8 NOT NULL,
  `organeRefAccord` varchar(15) CHARACTER SET utf8 NOT NULL,
  `accord` tinyint(2) DEFAULT NULL,
  `dateMaj` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `groupes_cohesion`
--

CREATE TABLE `groupes_cohesion` (
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
  `effectif` bigint(21) NOT NULL DEFAULT 0,
  `legislature` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `groupes_stats`
--

CREATE TABLE `groupes_stats` (
  `organeRef` varchar(15) CHARACTER SET utf8 NOT NULL,
  `womenPct` decimal(4,2) DEFAULT NULL,
  `womenN` int(3) DEFAULT NULL,
  `age` decimal(4,2) DEFAULT NULL,
  `rose_index` decimal(4,3) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `hatvp`
--

CREATE TABLE `hatvp` (
  `mpId` varchar(10) NOT NULL,
  `url` text NOT NULL,
  `category` varchar(35) NOT NULL,
  `value` text NOT NULL,
  `valueCleaned` text DEFAULT NULL,
  `employeur` text NOT NULL,
  `conservee` tinyint(1) NOT NULL,
  `dateDebut` date DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `dateMaj` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `history_mps_average`
--

CREATE TABLE `history_mps_average` (
  `id` tinyint(4) NOT NULL,
  `legislature` tinyint(4) NOT NULL,
  `length` decimal(4,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `history_per_mps_average`
--

CREATE TABLE `history_per_mps_average` (
  `mpId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `mpLength` decimal(28,0) DEFAULT NULL,
  `mandatesN` bigint(21) NOT NULL DEFAULT 0,
  `lengthEdited` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `organeRef` varchar(30) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `libelleAbrev` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `legislatureNumber` tinyint(1) NOT NULL,
  `dateDebut` date NOT NULL,
  `dateFin` date DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
-- Structure de la table `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `general` tinyint(1) NOT NULL DEFAULT 1,
  `votes` tinyint(1) NOT NULL DEFAULT 1,
  `depute` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `departement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `organes`
--

CREATE TABLE `organes` (
  `uid` varchar(50) NOT NULL,
  `coteType` text DEFAULT NULL,
  `libelle` text NOT NULL,
  `libelleEdition` text NOT NULL,
  `libelleAbrev` text NOT NULL,
  `libelleAbrege` text NOT NULL,
  `dateDebut` date DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `regime` text DEFAULT NULL,
  `legislature` varchar(20) DEFAULT NULL,
  `positionPolitique` varchar(25) DEFAULT NULL,
  `preseance` varchar(5) DEFAULT NULL,
  `couleurAssociee` varchar(20) DEFAULT NULL,
  `dateMaj` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `parrainages`
--

CREATE TABLE `parrainages` (
  `id` int(11) NOT NULL,
  `civ` varchar(5) NOT NULL,
  `nameLast` varchar(75) NOT NULL,
  `nameFirst` varchar(75) NOT NULL,
  `mandat` varchar(100) NOT NULL,
  `circo` text CHARACTER SET utf8 DEFAULT NULL,
  `dpt` varchar(100) NOT NULL,
  `candidat` varchar(100) NOT NULL,
  `datePublication` date NOT NULL,
  `year` int(11) NOT NULL,
  `mpId` varchar(35) DEFAULT NULL,
  `dateMaj` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `parties`
--

CREATE TABLE `parties` (
  `uid` varchar(50) CHARACTER SET utf8 NOT NULL,
  `libelleAbrev` text CHARACTER SET utf8 NOT NULL,
  `libelle` text CHARACTER SET utf8 NOT NULL,
  `dateFin` date DEFAULT NULL,
  `effectifTotal` bigint(21) NOT NULL DEFAULT 0,
  `effectif` bigint(21) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL,
  `state` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `quizz`
--

CREATE TABLE `quizz` (
  `id` int(11) NOT NULL,
  `quizz` smallint(5) UNSIGNED NOT NULL,
  `voteNumero` smallint(5) UNSIGNED NOT NULL,
  `legislature` tinyint(3) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `explication` text DEFAULT NULL,
  `for1` text DEFAULT NULL,
  `for2` text DEFAULT NULL,
  `for3` text DEFAULT NULL,
  `against1` text DEFAULT NULL,
  `against2` text DEFAULT NULL,
  `against3` text DEFAULT NULL,
  `context` text DEFAULT NULL,
  `category` tinyint(3) UNSIGNED DEFAULT NULL,
  `swap` tinyint(1) NOT NULL DEFAULT 0,
  `state` varchar(15) NOT NULL,
  `created_by` smallint(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_by` smallint(6) DEFAULT NULL,
  `modified_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `readings`
--

CREATE TABLE `readings` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `regions`
--

CREATE TABLE `regions` (
  `id` tinyint(3) DEFAULT NULL,
  `cheflieu` varchar(8) DEFAULT NULL,
  `tncc` varchar(4) DEFAULT NULL,
  `ncc` varchar(26) DEFAULT NULL,
  `nccner` varchar(45) DEFAULT NULL,
  `libelle` varchar(45) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `sujets`
--

CREATE TABLE `sujets` (
  `id` int(11) NOT NULL,
  `libelleAbrev` varchar(50) NOT NULL,
  `sujet` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `table_history`
--

CREATE TABLE `table_history` (
  `id` int(11) NOT NULL,
  `table` varchar(50) CHARACTER SET utf8 NOT NULL,
  `col` varchar(50) CHARACTER SET utf8 NOT NULL,
  `value_old` text CHARACTER SET utf8 NOT NULL,
  `value_new` text CHARACTER SET utf8 NOT NULL,
  `user` int(11) NOT NULL,
  `modified_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `users_mp`
--

CREATE TABLE `users_mp` (
  `user` int(11) NOT NULL,
  `mpId` varchar(11) CHARACTER SET utf8 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `users_mp_link`
--

CREATE TABLE `users_mp_link` (
  `id` int(11) NOT NULL,
  `mpId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `token` varchar(255) CHARACTER SET utf8 NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `votes`
--

CREATE TABLE `votes` (
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
  `legislature` tinyint(4) NOT NULL,
  `voteNumero` int(11) NOT NULL,
  `vote_id` varchar(50) NOT NULL,
  `title` text NOT NULL,
  `slug` varchar(160) NOT NULL,
  `category` int(11) NOT NULL,
  `reading` varchar(25) DEFAULT NULL,
  `description` text NOT NULL,
  `state` varchar(25) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `modified_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(10) DEFAULT NULL,
  `modified_by` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_datan_requested`
--

CREATE TABLE `votes_datan_requested` (
  `id` int(11) NOT NULL,
  `legislature` int(11) NOT NULL,
  `vote` int(11) NOT NULL,
  `email` text DEFAULT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_dossiers`
--

CREATE TABLE `votes_dossiers` (
  `id` int(11) NOT NULL,
  `offset_num` int(11) NOT NULL,
  `legislature` tinyint(4) NOT NULL,
  `voteNumero` int(11) NOT NULL,
  `href` text DEFAULT NULL,
  `dossier` varchar(300) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_groupes`
--

CREATE TABLE `votes_groupes` (
  `voteId` varchar(50) NOT NULL,
  `voteNumero` int(20) NOT NULL,
  `legislature` int(20) NOT NULL,
  `organeRef` varchar(50) NOT NULL,
  `nombreMembresGroupe` int(50) NOT NULL,
  `positionMajoritaire` text DEFAULT NULL,
  `nombrePours` int(50) NOT NULL,
  `nombreContres` int(50) NOT NULL,
  `nombreAbstentions` int(50) NOT NULL,
  `nonVotants` int(10) DEFAULT NULL,
  `nonVotantsVolontaires` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_info`
--

CREATE TABLE `votes_info` (
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
  `sortCode` varchar(100) DEFAULT NULL,
  `titre` text DEFAULT NULL,
  `demandeur` text DEFAULT NULL,
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
  `legislature` int(15) NOT NULL,
  `voteNumero` int(15) NOT NULL,
  `mpId` varchar(25) NOT NULL,
  `participation` int(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `votes_scores`
--

CREATE TABLE `votes_scores` (
  `voteNumero` int(5) NOT NULL,
  `legislature` int(5) NOT NULL,
  `mpId` varchar(50) CHARACTER SET utf8 NOT NULL,
  `vote` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `mandatId` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `sortCode` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `positionGroup` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `scoreLoyaute` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `scoreGagnant` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `scoreParticipation` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `positionGvt` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `scoreGvt` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `dateMaj` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la vue `candidate_full`
--
DROP TABLE IF EXISTS `candidate_full`;

CREATE ALGORITHM=UNDEFINED DEFINER=``@`` SQL SECURITY DEFINER VIEW `candidate_full`  AS SELECT `edc`.`mpId` AS `mpId`, `edc`.`election` AS `election`, `edc`.`district` AS `district`, `edc`.`candidature` AS `candidature`, `edc`.`position` AS `position`, `edc`.`nuance` AS `nuance`, `edc`.`source` AS `source`, `edc`.`link` AS `link`, `edc`.`visible` AS `visible`, `edc`.`secondRound` AS `secondRound`, `edc`.`elected` AS `elected`, `dl`.`legislature` AS `legislature`, `dl`.`nameUrl` AS `nameUrl`, `dl`.`civ` AS `civ`, `dl`.`nameFirst` AS `nameFirst`, `dl`.`nameLast` AS `nameLast`, `dl`.`age` AS `age`, `dl`.`dptSlug` AS `dptSlug`, `dl`.`departementNom` AS `departementNom`, `dl`.`departementCode` AS `departementCode`, `dl`.`circo` AS `circo`, `dl`.`mandatId` AS `mandatId`, `dl`.`libelle` AS `depute_libelle`, `dl`.`libelleAbrev` AS `depute_libelleAbrev`, `dl`.`groupeId` AS `groupeId`, `dl`.`groupeMandat` AS `groupeMandat`, `dl`.`couleurAssociee` AS `couleurAssociee`, `dl`.`dateFin` AS `dateFin`, `dl`.`datePriseFonction` AS `datePriseFonction`, `dl`.`causeFin` AS `causeFin`, `dl`.`img` AS `img`, `dl`.`imgOgp` AS `imgOgp`, `dl`.`dateMaj` AS `dateMaj`, `dl`.`libelle_1` AS `libelle_1`, `dl`.`libelle_2` AS `libelle_2`, `dl`.`active` AS `active`, `el`.`id` AS `election_id`, `el`.`libelle` AS `election_libelle`, `el`.`libelleAbrev` AS `election_libelleAbrev`, `el`.`dateYear` AS `dateYear`, `el`.`dateFirstRound` AS `dateFirstRound`, `el`.`dateSecondRound` AS `dateSecondRound`, `edc`.`modified_at` AS `modified_at` FROM ((`elect_deputes_candidats` `edc` left join `deputes_last` `dl` on(`edc`.`mpId` = `dl`.`mpId`)) left join `elect_libelle` `el` on(`edc`.`election` = `el`.`id`)) ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `amendements`
--
ALTER TABLE `amendements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dossier` (`dossier`),
  ADD KEY `idx_numOrdre` (`numOrdre`),
  ADD KEY `idx_seanceRef` (`seanceRef`);

--
-- Index pour la table `amendements_auteurs`
--
ALTER TABLE `amendements_auteurs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_acteurRef` (`acteurRef`),
  ADD KEY `idx_groupeId` (`groupeId`);

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
  ADD KEY `idx_commune_nom` (`commune_nom`) USING BTREE,
  ADD KEY `idx_insee` (`insee`);

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
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `class_groups_proximite`
--
ALTER TABLE `class_groups_proximite`
  ADD KEY `idx_organeRef` (`organeRef`),
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `class_loyaute`
--
ALTER TABLE `class_loyaute`
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_legislature` (`legislature`);

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
  ADD KEY `idx_legislature` (`legislature`);

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
-- Index pour la table `class_participation_solennels`
--
ALTER TABLE `class_participation_solennels`
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_active` (`active`);

--
-- Index pour la table `departement`
--
ALTER TABLE `departement`
  ADD PRIMARY KEY (`departement_id`),
  ADD KEY `departement_slug` (`departement_slug`),
  ADD KEY `departement_code` (`departement_code`),
  ADD KEY `departement_nom_soundex` (`departement_nom_soundex`);

--
-- Index pour la table `deputes`
--
ALTER TABLE `deputes`
  ADD PRIMARY KEY (`mpId`);

--
-- Index pour la table `deputes_accord`
--
ALTER TABLE `deputes_accord`
  ADD PRIMARY KEY (`legislature`,`voteNumero`,`mpId`,`organeRef`),
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `deputes_accord_cleaned`
--
ALTER TABLE `deputes_accord_cleaned`
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `deputes_all`
--
ALTER TABLE `deputes_all`
  ADD PRIMARY KEY (`mpId`,`legislature`),
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
  ADD PRIMARY KEY (`mpId`,`legislature`),
  ADD KEY `idx_mp` (`nameUrl`),
  ADD KEY `idx_dptSlug` (`dptSlug`),
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `documents_legislatifs`
--
ALTER TABLE `documents_legislatifs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_titreChemin` (`titreChemin`),
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `dossiers_acteurs`
--
ALTER TABLE `dossiers_acteurs`
  ADD KEY `idx_id` (`id`),
  ADD KEY `idx_ref` (`ref`);

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
-- Index pour la table `elect_deputes_candidats`
--
ALTER TABLE `elect_deputes_candidats`
  ADD PRIMARY KEY (`mpId`,`election`);

--
-- Index pour la table `elect_libelle`
--
ALTER TABLE `elect_libelle`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `famsocpro`
--
ALTER TABLE `famsocpro`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `faq_categories`
--
ALTER TABLE `faq_categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `faq_posts`
--
ALTER TABLE `faq_posts`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `groupes_accord`
--
ALTER TABLE `groupes_accord`
  ADD PRIMARY KEY (`legislature`,`voteNumero`,`organeRef`,`organeRefAccord`),
  ADD KEY `idx_organeRef` (`organeRef`),
  ADD KEY `idx_organeRefAccord` (`organeRefAccord`),
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `groupes_cohesion`
--
ALTER TABLE `groupes_cohesion`
  ADD PRIMARY KEY (`legislature`,`voteNumero`,`organeRef`),
  ADD KEY `idx_legislature_voteNumero` (`voteNumero`,`legislature`);

--
-- Index pour la table `groupes_effectif`
--
ALTER TABLE `groupes_effectif`
  ADD KEY `idx_organeRef` (`organeRef`);

--
-- Index pour la table `hatvp`
--
ALTER TABLE `hatvp`
  ADD KEY `idx_mpId` (`mpId`);

--
-- Index pour la table `history_mps_average`
--
ALTER TABLE `history_mps_average`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_legislature` (`legislature`);

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
  ADD KEY `idx_insee` (`insee`);

--
-- Index pour la table `legislature`
--
ALTER TABLE `legislature`
  ADD PRIMARY KEY (`legislatureNumber`);

--
-- Index pour la table `mandat_groupe`
--
ALTER TABLE `mandat_groupe`
  ADD PRIMARY KEY (`mandatId`),
  ADD KEY `idx_organeRef` (`organeRef`),
  ADD KEY `idx_mandatId` (`mandatId`) USING BTREE,
  ADD KEY `idx_mpId` (`mpId`) USING BTREE;

--
-- Index pour la table `mandat_principal`
--
ALTER TABLE `mandat_principal`
  ADD PRIMARY KEY (`mandatId`),
  ADD KEY `idx_typeOrgane` (`typeOrgane`),
  ADD KEY `idx_legislature` (`legislature`),
  ADD KEY `idx_mpId` (`mpId`) USING BTREE;

--
-- Index pour la table `mandat_secondaire`
--
ALTER TABLE `mandat_secondaire`
  ADD PRIMARY KEY (`mandatId`),
  ADD KEY `idx_typeOrgane` (`typeOrgane`),
  ADD KEY `idx_mpId` (`mpId`) USING BTREE;

--
-- Index pour la table `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `organes`
--
ALTER TABLE `organes`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `idx_uid` (`uid`);

--
-- Index pour la table `parrainages`
--
ALTER TABLE `parrainages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nameLast` (`nameLast`,`nameFirst`,`dpt`,`datePublication`,`mandat`),
  ADD KEY `mpId_idx` (`mpId`);

--
-- Index pour la table `parties`
--
ALTER TABLE `parties`
  ADD KEY `idx_uid` (`uid`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Index pour la table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `quizz`
--
ALTER TABLE `quizz`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `readings`
--
ALTER TABLE `readings`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sujets`
--
ALTER TABLE `sujets`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `table_history`
--
ALTER TABLE `table_history`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD UNIQUE KEY `username_3` (`username`),
  ADD UNIQUE KEY `username_4` (`username`),
  ADD UNIQUE KEY `username_5` (`username`),
  ADD UNIQUE KEY `username_6` (`username`),
  ADD UNIQUE KEY `username_7` (`username`);

--
-- Index pour la table `users_mp`
--
ALTER TABLE `users_mp`
  ADD KEY `user_idx` (`user`);

--
-- Index pour la table `users_mp_link`
--
ALTER TABLE `users_mp_link`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Index pour la table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`legislature`,`voteNumero`,`mpId`,`voteType`),
  ADD KEY `idx_voteNumero` (`voteNumero`),
  ADD KEY `idx_voteId` (`voteId`) USING BTREE,
  ADD KEY `idx_mpId` (`mpId`) USING BTREE,
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `votes_datan`
--
ALTER TABLE `votes_datan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vote_id` (`vote_id`);

--
-- Index pour la table `votes_datan_requested`
--
ALTER TABLE `votes_datan_requested`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `votes_dossiers`
--
ALTER TABLE `votes_dossiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_voteNumero` (`voteNumero`) USING BTREE,
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `votes_groupes`
--
ALTER TABLE `votes_groupes`
  ADD PRIMARY KEY (`legislature`,`voteNumero`,`organeRef`),
  ADD KEY `idx_organeRef` (`organeRef`),
  ADD KEY `idx_voteNumero` (`voteNumero`) USING BTREE,
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `votes_info`
--
ALTER TABLE `votes_info`
  ADD PRIMARY KEY (`legislature`,`voteNumero`),
  ADD KEY `idx_voteNumero` (`voteNumero`),
  ADD KEY `idx_legislature` (`legislature`),
  ADD KEY `idx_voteId` (`voteId`) USING BTREE,
  ADD KEY `idx_voteType` (`voteType`) USING BTREE;

--
-- Index pour la table `votes_participation`
--
ALTER TABLE `votes_participation`
  ADD PRIMARY KEY (`legislature`,`voteNumero`,`mpId`),
  ADD KEY `idx_mpId` (`mpId`),
  ADD KEY `idx_voteNumero` (`voteNumero`),
  ADD KEY `idx_legislature` (`legislature`);

--
-- Index pour la table `votes_participation_commission`
--
ALTER TABLE `votes_participation_commission`
  ADD PRIMARY KEY (`legislature`,`voteNumero`,`mpId`),
  ADD KEY `idx_mpId` (`mpId`);

--
-- Index pour la table `votes_scores`
--
ALTER TABLE `votes_scores`
  ADD PRIMARY KEY (`legislature`,`voteNumero`,`mpId`),
  ADD KEY `idx_loyaute` (`scoreLoyaute`),
  ADD KEY `idx_deputeId_numero` (`mpId`,`voteNumero`),
  ADD KEY `idx_mpId` (`mpId`) USING BTREE,
  ADD KEY `idx_legislature_voteNumero` (`voteNumero`,`legislature`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `circos`
--
ALTER TABLE `circos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `class_loyaute_six`
--
ALTER TABLE `class_loyaute_six`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `departement`
--
ALTER TABLE `departement`
  MODIFY `departement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossiers`
--
ALTER TABLE `dossiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `elect_libelle`
--
ALTER TABLE `elect_libelle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `famsocpro`
--
ALTER TABLE `famsocpro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `faq_categories`
--
ALTER TABLE `faq_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `faq_posts`
--
ALTER TABLE `faq_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `history_mps_average`
--
ALTER TABLE `history_mps_average`
  MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `insee`
--
ALTER TABLE `insee`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `parrainages`
--
ALTER TABLE `parrainages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quizz`
--
ALTER TABLE `quizz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sujets`
--
ALTER TABLE `sujets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `table_history`
--
ALTER TABLE `table_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users_mp_link`
--
ALTER TABLE `users_mp_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `votes_datan`
--
ALTER TABLE `votes_datan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `votes_datan_requested`
--
ALTER TABLE `votes_datan_requested`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `votes_dossiers`
--
ALTER TABLE `votes_dossiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
