CREATE TABLE IF NOT EXISTS `amendements_ia` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `legislature` INT NOT NULL,
  `voteNumero` VARCHAR(20) NOT NULL,
  `resume_ia` TEXT NULL,
  `simplicite_ia` TINYINT UNSIGNED NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_leg_vote` (`legislature`, `voteNumero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
