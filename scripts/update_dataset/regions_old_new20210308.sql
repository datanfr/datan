-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Mer 10 Mars 2021 à 13:08
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
-- Structure de la table `regions_old_new`
--

CREATE TABLE IF NOT EXISTS `regions_old_new` (
  `new_code` int(11) DEFAULT NULL,
  `former_code` int(11) DEFAULT NULL,
  KEY `idx_former_code` (`former_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `regions_old_new`
--

INSERT INTO `regions_old_new` (`new_code`, `former_code`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(6, 6),
(11, 11),
(24, 24),
(27, 26),
(27, 43),
(28, 23),
(28, 25),
(32, 31),
(32, 22),
(44, 41),
(44, 42),
(44, 21),
(52, 52),
(53, 53),
(75, 72),
(75, 54),
(75, 74),
(76, 73),
(76, 91),
(84, 82),
(84, 83),
(93, 93),
(94, 94);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
