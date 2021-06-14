<?php
  include('../bdd-connexion.php');


  // Table faq_categories

  $bdd->query('DROP TABLE IF EXISTS `faq_categories`');

  $bdd->query('CREATE TABLE IF NOT EXISTS `faq_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `slug` varchar(50) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

  $bdd->query("INSERT INTO `faq_categories` (`name`, `slug`) VALUES
    ('Datan', 'datan'),
    ('Nos statistiques', 'statistiques'),
    ('Assemblée nationale', 'assemblee-nationale'),
    ('Députés', 'deputes'),
    ('Groupes parlementaires', 'groupes'),
    ('Votes', 'votes')
  ");

  // Table faq_posts

  $bdd->query("DROP TABLE IF EXISTS `faq_posts`");

  $bdd->query("CREATE TABLE IF NOT EXISTS `faq_posts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` text NOT NULL,
    `text` text NOT NULL,
    `category` int(11) NOT NULL,
    `state` varchar(15) NOT NULL,
    `created_by` varchar(10) NOT NULL,
    `created_at` timestamp NOT NULL,
    `modified_by` varchar(10) NOT NULL,
    `modified_at` timestamp NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
  ");

?>
