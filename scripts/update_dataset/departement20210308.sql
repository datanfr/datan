-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Mer 10 Mars 2021 à 12:19
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
-- Structure de la table `departement`
--

CREATE TABLE IF NOT EXISTS `departement` (
  `departement_id` int(11) NOT NULL AUTO_INCREMENT,
  `departement_code` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `departement_nom` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `departement_slug` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `libelle_1` varchar(255) DEFAULT NULL,
  `libelle_2` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`departement_id`),
  KEY `departement_slug` (`departement_slug`),
  KEY `departement_code` (`departement_code`)
) ENGINE=MyISAM AUTO_INCREMENT=108 DEFAULT CHARSET=latin1;

--
-- Contenu de la table `departement`
--

INSERT INTO `departement` (`departement_id`, `departement_code`, `departement_nom`, `departement_slug`, `libelle_1`, `libelle_2`, `slug`, `region`) VALUES
(1, '01', 'Ain', 'ain', 'dans l\'', 'de l\'', 'ain-01', 'Auvergne-Rhône-Alpes'),
(2, '02', 'Aisne', 'aisne', 'dans l\'', 'de l\'', 'aisne-02', 'Hauts-de-France'),
(3, '03', 'Allier', 'allier', 'dans l\'', 'de l\'', 'allier-03', 'Auvergne-Rhône-Alpes'),
(5, '05', 'Hautes-Alpes', 'hautes-alpes', 'dans les ', 'des ', 'hautes-alpes-05', 'Provence-Alpes-Côte d\'Azur'),
(4, '04', 'Alpes-de-Haute-Provence', 'alpes-de-haute-provence', 'dans les ', 'des ', 'alpes-de-haute-provence-04', 'Provence-Alpes-Côte d\'Azur'),
(6, '06', 'Alpes-Maritimes', 'alpes-maritimes', 'dans les ', 'des ', 'alpes-maritimes-06', 'Provence-Alpes-Côte d\'Azur'),
(7, '07', 'Ardèche', 'ardeche', 'dans l\'', 'de l\'', 'ardeche-07', 'Auvergne-Rhône-Alpes'),
(8, '08', 'Ardennes', 'ardennes', 'dans les ', 'des ', 'ardennes-08', 'Grand Est'),
(9, '09', 'Ariège', 'ariege', 'dans l\'', 'de l\'', 'ariege-09', 'Occitanie'),
(10, '10', 'Aube', 'aube', 'dans l\'', 'de l\'', 'aube-10', 'Grand Est'),
(11, '11', 'Aude', 'aude', 'dans l\'', 'de l\'', 'aude-11', 'Occitanie'),
(12, '12', 'Aveyron', 'aveyron', 'dans l\'', 'de l\'', 'aveyron-12', 'Occitanie'),
(13, '13', 'Bouches-du-Rhône', 'bouches-du-rhone', 'dans les ', 'des ', 'bouches-du-rhone-13', 'Provence-Alpes-Côte d\'Azur'),
(14, '14', 'Calvados', 'calvados', 'dans le ', 'du ', 'calvados-14', 'Normandie'),
(15, '15', 'Cantal', 'cantal', 'dans le ', 'du ', 'cantal-15', 'Auvergne-Rhône-Alpes'),
(16, '16', 'Charente', 'charente', 'dans la ', 'de la ', 'charente-16', 'Nouvelle-Aquitaine'),
(17, '17', 'Charente-Maritime', 'charente-maritime', 'dans la ', 'de la ', 'charente-maritime-17', 'Nouvelle-Aquitaine'),
(18, '18', 'Cher', 'cher', 'dans le ', 'du ', 'cher-18', 'Centre-Val de Loire'),
(19, '19', 'Corrèze', 'correze', 'en ', 'de la ', 'correze-19', 'Nouvelle-Aquitaine'),
(20, '2a', 'Corse-du-sud', 'corse-du-sud', 'en ', 'de la ', 'corse-du-sud-2a', 'Corse'),
(21, '2b', 'Haute-corse', 'haute-corse', 'en ', 'de la ', 'haute-corse-2b', 'Corse'),
(22, '21', 'Côte-d\'or', 'cote-dor', 'en ', 'de la ', 'cote-dor-21', 'Bourgogne-Franche-Comté'),
(23, '22', 'Côtes-d\'armor', 'cotes-darmor', 'dans les ', 'des ', 'cotes-darmor-22', 'Bretagne'),
(24, '23', 'Creuse', 'creuse', 'dans la ', 'de la ', 'creuse-23', 'Nouvelle-Aquitaine'),
(25, '24', 'Dordogne', 'dordogne', 'dans la ', 'de la ', 'dordogne-24', 'Nouvelle-Aquitaine'),
(26, '25', 'Doubs', 'doubs', 'dans le ', 'du ', 'doubs-25', 'Bourgogne-Franche-Comté'),
(27, '26', 'Drôme', 'drome', 'dans la ', 'de la ', 'drome-26', 'Auvergne-Rhône-Alpes'),
(28, '27', 'Eure', 'eure', 'dans l\'', 'de l\'', 'eure-27', 'Normandie'),
(29, '28', 'Eure-et-Loir', 'eure-et-loir', 'dans l\'', 'de l\'', 'eure-et-loir-28', 'Centre-Val de Loire'),
(30, '29', 'Finistère', 'finistere', 'dans le ', 'du ', 'finistere-29', 'Bretagne'),
(31, '30', 'Gard', 'gard', 'dans le ', 'du ', 'gard-30', 'Occitanie'),
(32, '31', 'Haute-Garonne', 'haute-garonne', 'dans la ', 'de l\'', 'haute-garonne-31', 'Occitanie'),
(33, '32', 'Gers', 'gers', 'dans le ', 'du ', 'gers-32', 'Occitanie'),
(34, '33', 'Gironde', 'gironde', 'en ', 'du ', 'gironde-33', 'Nouvelle-Aquitaine'),
(35, '34', 'Hérault', 'herault', 'dans l\'', 'de l\'', 'herault-34', 'Occitanie'),
(36, '35', 'Ille-et-Vilaine', 'ille-et-vilaine', 'en ', 'de l\'', 'ille-et-vilaine-35', 'Bretagne'),
(37, '36', 'Indre', 'indre', 'dans l\'', 'de l\'', 'indre-36', 'Centre-Val de Loire'),
(38, '37', 'Indre-et-Loire', 'indre-et-loire', 'dans l', 'de l\'', 'indre-et-loire-37', 'Centre-Val de Loire'),
(39, '38', 'Isère', 'isere', 'dans l\'', 'de l\'', 'isere-38', 'Auvergne-Rhône-Alpes'),
(40, '39', 'Jura', 'jura', 'dans le ', 'du ', 'jura-39', 'Bourgogne-Franche-Comté'),
(41, '40', 'Landes', 'landes', 'dans les ', 'des', 'landes-40', 'Nouvelle-Aquitaine'),
(42, '41', 'Loir-et-Cher', 'loir-et-cher', 'dans le ', 'du ', 'loir-et-cher-41', 'Centre-Val de Loire'),
(43, '42', 'Loire', 'loire', 'dans la ', 'de la ', 'loire-42', 'Auvergne-Rhône-Alpes'),
(44, '43', 'Haute-Loire', 'haute-loire', 'dans la ', 'de la ', 'haute-loire-43', 'Auvergne-Rhône-Alpes'),
(45, '44', 'Loire-Atlantique', 'loire-atlantique', 'dans la ', 'de la ', 'loire-atlantique-44', 'Pays de la Loire'),
(46, '45', 'Loiret', 'loiret', 'dans le ', 'du ', 'loiret-45', 'Centre-Val de Loire'),
(47, '46', 'Lot', 'lot', 'dans le ', 'du ', 'lot-46', 'Occitanie'),
(48, '47', 'Lot-et-Garonne', 'lot-et-garonne', 'dans le ', 'de le', 'lot-et-garonne-47', 'Nouvelle-Aquitaine'),
(49, '48', 'Lozère', 'lozere', 'dans la ', 'de la ', 'lozere-48', 'Occitanie'),
(50, '49', 'Maine-et-Loire', 'maine-et-loire', 'dans le ', 'du ', 'maine-et-loire-49', 'Pays de la Loire'),
(51, '50', 'Manche', 'manche', 'dans la ', 'de la ', 'manche-50', 'Normandie'),
(52, '51', 'Marne', 'marne', 'dans la ', 'de la ', 'marne-51', 'Grand Est'),
(53, '52', 'Haute-Marne', 'haute-marne', 'dans la ', 'de la ', 'haute-marne-52', 'Grand Est'),
(54, '53', 'Mayenne', 'mayenne', 'dans la ', 'de la ', 'mayenne-53', 'Pays de la Loire'),
(55, '54', 'Meurthe-et-Moselle', 'meurthe-et-moselle', 'dans la ', 'de la ', 'meurthe-et-moselle-54', 'Grand Est'),
(56, '55', 'Meuse', 'meuse', 'dans la ', 'de la ', 'meuse-55', 'Grand Est'),
(57, '56', 'Morbihan', 'morbihan', 'dans le ', 'du ', 'morbihan-56', 'Bretagne'),
(58, '57', 'Moselle', 'moselle', 'dans la ', 'de la ', 'moselle-57', 'Grand Est'),
(59, '58', 'Nièvre', 'nievre', 'dans la ', 'de la ', 'nievre-58', 'Bourgogne-Franche-Comté'),
(60, '59', 'Nord', 'nord', 'dans le ', 'du ', 'nord-59', 'Hauts-de-France'),
(61, '60', 'Oise', 'oise', 'dans l\'', 'de l\'', 'oise-60', 'Hauts-de-France'),
(62, '61', 'Orne', 'orne', 'dans l\'', 'de l\'', 'orne-61', 'Normandie'),
(63, '62', 'Pas-de-Calais', 'pas-de-calais', 'dans le ', 'du ', 'pas-de-calais-62', 'Hauts-de-France'),
(64, '63', 'Puy-de-Dôme', 'puy-de-dome', 'dans le ', 'du ', 'puy-de-dome-63', 'Auvergne-Rhône-Alpes'),
(65, '64', 'Pyrénées-Atlantiques', 'pyrenees-atlantiques', 'dans les ', 'des ', 'pyrenees-atlantiques-64', 'Nouvelle-Aquitaine'),
(66, '65', 'Hautes-Pyrénées', 'hautes-pyrenees', 'dans les ', 'des ', 'hautes-pyrenees-65', 'Occitanie'),
(67, '66', 'Pyrénées-Orientales', 'pyrenees-orientales', 'dans les ', 'des ', 'pyrenees-orientales-66', 'Occitanie'),
(68, '67', 'Bas-Rhin', 'bas-rhin', 'dans le ', 'du ', 'bas-rhin-67', 'Grand Est'),
(69, '68', 'Haut-Rhin', 'haut-rhin', 'dans le ', 'du ', 'haut-rhin-68', 'Grand Est'),
(70, '69', 'Rhône', 'rhone', 'dans le ', 'du ', 'rhone-69', 'Auvergne-Rhône-Alpes'),
(71, '70', 'Haute-Saône', 'haute-saone', 'dans la ', 'de la ', 'haute-saone-70', 'Bourgogne-Franche-Comté'),
(72, '71', 'Saône-et-Loire', 'saone-et-loire', 'dans la ', 'de la ', 'saone-et-loire-71', 'Bourgogne-Franche-Comté'),
(73, '72', 'Sarthe', 'sarthe', 'dans la ', 'de la ', 'sarthe-72', 'Pays de la Loire'),
(74, '73', 'Savoie', 'savoie', 'dans la ', 'de la ', 'savoie-73', 'Auvergne-Rhône-Alpes'),
(75, '74', 'Haute-Savoie', 'haute-savoie', 'dans la ', 'de la ', 'haute-savoie-74', 'Auvergne-Rhône-Alpes'),
(76, '75', 'Paris', 'paris', 'à ', 'de ', 'paris-75', 'Île-de-France'),
(77, '76', 'Seine-Maritime', 'seine-maritime', 'dans la ', 'de la ', 'seine-maritime-76', 'Normandie'),
(78, '77', 'Seine-et-Marne', 'seine-et-marne', 'dans la ', 'de la ', 'seine-et-marne-77', 'Île-de-France'),
(79, '78', 'Yvelines', 'yvelines', 'dans les ', 'des ', 'yvelines-78', 'Île-de-France'),
(80, '79', 'Deux-Sèvres', 'deux-sevres', 'dans les ', 'des ', 'deux-sevres-79', 'Nouvelle-Aquitaine'),
(81, '80', 'Somme', 'somme', 'dans la ', 'de la ', 'somme-80', 'Hauts-de-France'),
(82, '81', 'Tarn', 'tarn', 'dans le ', 'du ', 'tarn-81', 'Occitanie'),
(83, '82', 'Tarn-et-Garonne', 'tarn-et-garonne', 'dans le ', 'du ', 'tarn-et-garonne-82', 'Occitanie'),
(84, '83', 'Var', 'var', 'dans le ', 'du ', 'var-83', 'Provence-Alpes-Côte d\'Azur'),
(85, '84', 'Vaucluse', 'vaucluse', 'dans le ', 'du ', 'vaucluse-84', 'Provence-Alpes-Côte d\'Azur'),
(86, '85', 'Vendée', 'vendee', 'dans la ', 'de la ', 'vendee-85', 'Pays de la Loire'),
(87, '86', 'Vienne', 'vienne', 'dans la ', 'de la ', 'vienne-86', 'Nouvelle-Aquitaine'),
(88, '87', 'Haute-Vienne', 'haute-vienne', 'dans la ', 'de la ', 'haute-vienne-87', 'Nouvelle-Aquitaine'),
(89, '88', 'Vosges', 'vosges', 'dans les ', 'des ', 'vosges-88', 'Grand Est'),
(90, '89', 'Yonne', 'yonne', 'dans l\'', 'de l\'', 'yonne-89', 'Bourgogne-Franche-Comté'),
(91, '90', 'Territoire de Belfort', 'territoire-de-belfort', 'dans le ', 'du ', 'territoire-de-belfort-90', 'Bourgogne-Franche-Comté'),
(92, '91', 'Essonne', 'essonne', 'dans l\'', 'de l\'', 'essonne-91', 'Île-de-France'),
(93, '92', 'Hauts-de-Seine', 'hauts-de-seine', 'dans les ', 'des ', 'hauts-de-seine-92', 'Île-de-France'),
(94, '93', 'Seine-Saint-Denis', 'seine-saint-denis', 'dans la ', 'des ', 'seine-saint-denis-93', 'Île-de-France'),
(95, '94', 'Val-de-Marne', 'val-de-marne', 'dans le ', 'du ', 'val-de-marne-94', 'Île-de-France'),
(96, '95', 'Val-d\'oise', 'val-doise', 'dans le ', 'du ', 'val-doise-95', 'Île-de-France'),
(97, '976', 'Mayotte', 'mayotte', 'à ', 'de ', 'mayotte-976', 'Mayotte'),
(98, '971', 'Guadeloupe', 'guadeloupe', 'en ', 'de la ', 'guadeloupe-971', 'Guadeloupe'),
(99, '973', 'Guyane', 'guyane', 'en ', 'de la ', 'guyane-973', 'Guyane'),
(100, '972', 'Martinique', 'martinique', 'en ', 'de la ', 'martinique-972', 'Martinique'),
(101, '974', 'Réunion', 'reunion', 'à la ', 'de la ', 'reunion-974', 'La Réunion'),
(102, '099', 'Français établis hors de France', 'francais-de-letranger', 'dans la circonscription des ', 'de la circonscription des ', 'francais-de-letranger', NULL),
(103, '975', 'Saint-Pierre-et-Miquelon', 'saint-pierre-et-miquelon', 'à ', 'de ', 'saint-pierre-et-miquelon-975', NULL),
(104, '977', 'Saint-Barthélemy et Saint-Martin', 'saint-barthélemy et saint-martin', 'à ', 'de ', 'saint-barthelemy-et-saint-martin', NULL),
(105, '986', 'Wallis-et-Futuna', 'wallis-et-futuna', 'à ', 'de ', 'wallis-et-futuna-986', NULL),
(106, '987', 'Polynésie française', 'polynésie française', 'en ', 'de la ', 'polynesie-francaise-987', NULL),
(107, '988', 'Nouvelle-Calédonie', 'nouvelle-calédonie', 'en ', 'de la ', 'nouvelle-caledonie-988', NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
