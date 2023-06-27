CREATE TABLE IF NOT EXISTS `j3x_crampon` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `date_en_ligne` date NOT NULL,
  `fichier` varchar(255) NOT NULL DEFAULT '',
  `couverture` tinyint(1) NOT NULL DEFAULT '1',
  `titre_couverture` varchar(200) NOT NULL DEFAULT '',
  `auteur_couverture` varchar(200) NOT NULL DEFAULT '',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__crampon_articles` (
  `no` int(3) NOT NULL,
  `item` int(2) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `alias` varchar(200) NOT NULL,
  `auteur` varchar(200) NOT NULL DEFAULT '',
  `no_page` tinyint(2) NOT NULL DEFAULT '0',
  `nb_pages` tinyint(2) NOT NULL DEFAULT '0',
  `fichier` varchar(200) NOT NULL,
  `reserve` tinyint(1) NOT NULL DEFAULT '0',
  `vues` int(6) NOT NULL DEFAULT '0',
PRIMARY KEY (`no`, `item`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

