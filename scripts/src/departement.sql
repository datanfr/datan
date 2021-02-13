-- phpMyAdmin SQL Dump
-- version 4.0.6deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 09, 2013 at 06:10 PM
-- Server version: 5.5.34-0ubuntu0.13.10.1
-- PHP Version: 5.5.3-1ubuntu2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `villes_fr`
--

-- --------------------------------------------------------

--
-- Table structure for table `departement`
--

DROP TABLE IF EXISTS `departement` ;

CREATE TABLE IF NOT EXISTS `departement` (
  `departement_id` int(11) NOT NULL AUTO_INCREMENT,
  `departement_code` varchar(3) CHARACTER SET utf8 DEFAULT NULL,
  `departement_nom` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `departement_nom_uppercase` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `departement_slug` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `departement_nom_soundex` varchar(20) DEFAULT NULL,
  `libelle_1` varchar(255) DEFAULT NULL,
  `libelle_2` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`departement_id`),
  KEY `departement_slug` (`departement_slug`),
  KEY `departement_code` (`departement_code`),
  KEY `departement_nom_soundex` (`departement_nom_soundex`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=102;

--
-- Dumping data for table `departement`
--

INSERT INTO `departement` (`departement_id`, `departement_code`, `departement_nom`, `departement_nom_uppercase`, `departement_slug`, `departement_nom_soundex`, `libelle_1`, `libelle_2`) VALUES
(1, "01", "Ain", "AIN", "ain", "A500", "dans l'", "de l'"),
(2, "02", "Aisne", "AISNE", "aisne", "A250", "dans l'", "de l'"),
(3, "03", "Allier", "ALLIER", "allier", "A460", "dans l'", "de l'"),
(5, "05", "Hautes-Alpes", "HAUTES-ALPES", "hautes-alpes", "H32412", "dans les ", "des "),
(4, "04", "Alpes-de-Haute-Provence", "ALPES-DE-HAUTE-PROVENCE", "alpes-de-haute-provence", "A412316152", "dans les ", "des "),
(6, "06", "Alpes-Maritimes", "ALPES-MARITIMES", "alpes-maritimes", "A41256352", "dans les ", "des "),
(7, "07", "Ardèche", "ARDÈCHE", "ardeche", "A632", "dans l'", "de l'"),
(8, "08", "Ardennes", "ARDENNES", "ardennes", "A6352", "dans les ", "des "),
(9, "09", "Ariège", "ARIÈGE", "ariege", "A620", "dans l'", "de l'"),
(10, "10", "Aube", "AUBE", "aube", "A100", "dans l'", "de l'"),
(11, "11", "Aude", "AUDE", "aude", "A300", "dans l'", "de l'"),
(12, "12", "Aveyron", "AVEYRON", "aveyron", "A165", "dans l'", "de l'"),
(13, "13", "Bouches-du-Rhône", "BOUCHES-DU-RHÔNE", "bouches-du-rhone", "B2365", "dans les ", "des "),
(14, "14", "Calvados", "CALVADOS", "calvados", "C4132", "dans le ", "du "),
(15, "15", "Cantal", "CANTAL", "cantal", "C534", "dans le ", "du "),
(16, "16", "Charente", "CHARENTE", "charente", "C653", "dans la ", "de la "),
(17, "17", "Charente-Maritime", "CHARENTE-MARITIME", "charente-maritime", "C6535635", "dans la ", "de la "),
(18, "18", "Cher", "CHER", "cher", "C600", "dans le ", "du "),
(19, "19", "Corrèze", "CORRÈZE", "correze", "C620", "en ", "de la "),
(20, "2a", "Corse-du-sud", "CORSE-DU-SUD", "corse-du-sud", "C62323", "en ", "de la "),
(21, "2b", "Haute-corse", "HAUTE-CORSE", "haute-corse", "H3262", "en ", "de la "),
(22, "21", "Côte-d'or", "CÔTE-D'OR", "cote-dor", "C360", "en ", "de la "),
(23, "22", "Côtes-d'armor", "CÔTES-D'ARMOR", "cotes-darmor", "C323656", "dans les ", "des "),
(24, "23", "Creuse", "CREUSE", "creuse", "C620", "dans la ", "de la "),
(25, "24", "Dordogne", "DORDOGNE", "dordogne", "D6325", "dans la ", "de la "),
(26, "25", "Doubs", "DOUBS", "doubs", "D120", "dans le ", "du "),
(27, "26", "Drôme", "DRÔME", "drome", "D650", "dans la ", "de la "),
(28, "27", "Eure", "EURE", "eure", "E600", "dans l'", "de l'"),
(29, "28", "Eure-et-Loir", "EURE-ET-LOIR", "eure-et-loir", "E6346", "dans l'", "de l'"),
(30, "29", "Finistère", "FINISTÈRE", "finistere", "F5236", "dans le ", "du "),
(31, "30", "Gard", "GARD", "gard", "G630", "dans le ", "du "),
(32, "31", "Haute-Garonne", "HAUTE-GARONNE", "haute-garonne", "H3265", "dans la ", "de l'"),
(33, "32", "Gers", "GERS", "gers", "G620", "dans le ", "du "),
(34, "33", "Gironde", "GIRONDE", "gironde", "G653", "en ", "du "),
(35, "34", "Hérault", "HÉRAULT", "herault", "H643", "dans l'", "de l'"),
(36, "35", "Ile-et-Vilaine", "ILE-ET-VILAINE", "ile-et-vilaine", "I43145", "en ", "de l'"),
(37, "36", "Indre", "INDRE", "indre", "I536", "dans l'", "de l'"),
(38, "37", "Indre-et-Loire", "INDRE-ET-LOIRE", "indre-et-loire", "I536346", "dans l", "de l'"),
(39, "38", "Isère", "ISÈRE", "isere", "I260", "dans l'", "de l'"),
(40, "39", "Jura", "JURA", "jura", "J600", "dans le ", "du "),
(41, "40", "Landes", "LANDES", "landes", "L532", "dans les ", "des"),
(42, "41", "Loir-et-Cher", "LOIR-ET-CHER", "loir-et-cher", "L6326", "dans le ", "du "),
(43, "42", "Loire", "LOIRE", "loire", "L600", "dans la ", "de la "),
(44, "43", "Haute-Loire", "HAUTE-LOIRE", "haute-loire", "H346", "dans la ", "de la "),
(45, "44", "Loire-Atlantique", "LOIRE-ATLANTIQUE", "loire-atlantique", "L634532", "dans la ", "de la "),
(46, "45", "Loiret", "LOIRET", "loiret", "L630", "dans le ", "du "),
(47, "46", "Lot", "LOT", "lot", "L300", "dans le ", "du "),
(48, "47", "Lot-et-Garonne", "LOT-ET-GARONNE", "lot-et-garonne", "L3265", "dans le ", "de le"),
(49, "48", "Lozère", "LOZÈRE", "lozere", "L260", "dans la ", "de la "),
(50, "49", "Maine-et-Loire", "MAINE-ET-LOIRE", "maine-et-loire", "M346", "dans le ", "du "),
(51, "50", "Manche", "MANCHE", "manche", "M200", "dans la ", "de la "),
(52, "51", "Marne", "MARNE", "marne", "M650", "dans la ", "de la "),
(53, "52", "Haute-Marne", "HAUTE-MARNE", "haute-marne", "H3565", "dans la ", "de la "),
(54, "53", "Mayenne", "MAYENNE", "mayenne", "M000", "dans la ", "de la "),
(55, "54", "Meurthe-et-Moselle", "MEURTHE-ET-MOSELLE", "meurthe-et-moselle", "M63524", "dans la ", "de la "),
(56, "55", "Meuse", "MEUSE", "meuse", "M200", "dans la ", "de la "),
(57, "56", "Morbihan", "MORBIHAN", "morbihan", "M615", "dans le ", "du "),
(58, "57", "Moselle", "MOSELLE", "moselle", "M240", "dans la ", "de la "),
(59, "58", "Nièvre", "NIÈVRE", "nievre", "N160", "dans la ", "de la "),
(60, "59", "Nord", "NORD", "nord", "N630", "dans le ", "du "),
(61, "60", "Oise", "OISE", "oise", "O200", "dans l'", "de l'"),
(62, "61", "Orne", "ORNE", "orne", "O650", "dans l'", "de l'"),
(63, "62", "Pas-de-Calais", "PAS-DE-CALAIS", "pas-de-calais", "P23242", "dans le ", "du "),
(64, "63", "Puy-de-Dôme", "PUY-DE-DÔME", "puy-de-dome", "P350", "dans le ", "du "),
(65, "64", "Pyrénées-Atlantiques", "PYRÉNÉES-ATLANTIQUES", "pyrenees-atlantiques", "P65234532", "dans les ", "des "),
(66, "65", "Hautes-Pyrénées", "HAUTES-PYRÉNÉES", "hautes-pyrenees", "H321652", "dans les ", "des "),
(67, "66", "Pyrénées-Orientales", "PYRÉNÉES-ORIENTALES", "pyrenees-orientales", "P65265342", "dans les ", "des "),
(68, "67", "Bas-Rhin", "BAS-RHIN", "bas-rhin", "B265", "dans le ", "du "),
(69, "68", "Haut-Rhin", "HAUT-RHIN", "haut-rhin", "H365", "dans le ", "du "),
(70, "69", "Rhône", "RHÔNE", "rhone", "R500", "dans le ", "du "),
(71, "70", "Haute-Saône", "HAUTE-SAÔNE", "haute-saone", "H325", "dans la ", "de la "),
(72, "71", "Saône-et-Loire", "SAÔNE-ET-LOIRE", "saone-et-loire", "S5346", "dans la ", "de la "),
(73, "72", "Sarthe", "SARTHE", "sarthe", "S630", "dans la ", "de la "),
(74, "73", "Savoie", "SAVOIE", "savoie", "S100", "dans la ", "de la "),
(75, "74", "Haute-Savoie", "HAUTE-SAVOIE", "haute-savoie", "H321", "dans la ", "de la "),
(76, "75", "Paris", "PARIS", "paris", "P620", "à", "de "),
(77, "76", "Seine-Maritime", "SEINE-MARITIME", "seine-maritime", "S5635", "dans la ", "de la "),
(78, "77", "Seine-et-Marne", "SEINE-ET-MARNE", "seine-et-marne", "S53565", "dans la ", "de la "),
(79, "78", "Yvelines", "YVELINES", "yvelines", "Y1452", "dans les ", "des "),
(80, "79", "Deux-Sèvres", "DEUX-SÈVRES", "deux-sevres", "D2162", "dans les ", "des "),
(81, "80", "Somme", "SOMME", "somme", "S500", "dans la ", "de la "),
(82, "81", "Tarn", "TARN", "tarn", "T650", "dans le ", "du "),
(83, "82", "Tarn-et-Garonne", "TARN-ET-GARONNE", "tarn-et-garonne", "T653265", "dans le ", "du "),
(84, "83", "Var", "VAR", "var", "V600", "dans le ", "du "),
(85, "84", "Vaucluse", "VAUCLUSE", "vaucluse", "V242", "dans le ", "du "),
(86, "85", "Vendée", "VENDÉE", "vendee", "V530", "dans la ", "de la "),
(87, "86", "Vienne", "VIENNE", "vienne", "V500", "dans la ", "de la "),
(88, "87", "Haute-Vienne", "HAUTE-VIENNE", "haute-vienne", "H315", "dans la ", "de la "),
(89, "88", "Vosges", "VOSGES", "vosges", "V200", "dans les ", "des "),
(90, "89", "Yonne", "YONNE", "yonne", "Y500", "dans l'", "de l'"),
(91, "90", "Territoire de Belfort", "TERRITOIRE DE BELFORT", "territoire-de-belfort", "T636314163", "dans le ", "du "),
(92, "91", "Essonne", "ESSONNE", "essonne", "E250", "dans l'", "de l'"),
(93, "92", "Hauts-de-Seine", "HAUTS-DE-SEINE", "hauts-de-seine", "H32325", "dans les ", "des "),
(94, "93", "Seine-Saint-Denis", "SEINE-SAINT-DENIS", "seine-saint-denis", "S525352", "dans la ", "des "),
(95, "94", "Val-de-Marne", "VAL-DE-MARNE", "val-de-marne", "V43565", "dans le ", "du "),
(96, "95", "Val-d'oise", "VAL-D'OISE", "val-doise", "V432", "dans le ", "du "),
(97, "976", "Mayotte", "MAYOTTE", "mayotte", "M300", "à", "de "),
(98, "971", "Guadeloupe", "GUADELOUPE", "guadeloupe", "G341", "en ", "de la "),
(99, "973", "Guyane", "GUYANE", "guyane", "G500", "en ", "de la "),
(100, "972", "Martinique", "MARTINIQUE", "martinique", "M6352", "en ", "de la "),
(101, "974", "Réunion", "RÉUNION", "reunion", "R500", "à la", "de la ");

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
