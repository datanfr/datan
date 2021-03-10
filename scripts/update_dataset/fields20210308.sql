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
-- Structure de la table `fields`
--

CREATE TABLE IF NOT EXISTS `fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `libelle` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Contenu de la table `fields`
--

INSERT INTO `fields` (`id`, `name`, `slug`, `libelle`) VALUES
(1, 'Culture', 'culture', 'la culture'),
(2, 'Éducation', 'education', 'l\'éducation'),
(3, 'Affaires sociales', 'affaires-sociales', 'les affaires sociales'),
(4, 'Économie', 'economie', 'l\'économie'),
(5, 'Environnement', 'environnement', 'l\'environnement'),
(6, 'Institutions', 'institutions', 'les institutions'),
(7, 'Europe', 'europe', 'l\'Europe'),
(8, 'Affaires étrangères', 'affaires-etrangeres', 'les affaires étrangères'),
(9, 'Universités et recherche', 'universites-recherche', 'l\'université et la recherche'),
(10, 'Justice', 'justice', 'la justice'),
(11, 'Santé et solidarités', 'sante-solidarite', 'la santé et les solidarités'),
(12, 'Sports', 'sports', 'les sports'),
(13, 'Agriculture', 'agriculture', 'l\'agriculture'),
(14, 'Défense et armée', 'defense-armee', 'la défence et l\'armée');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
