<?php
$dbChangesets = array(
/*
  "3" => array(
    "INSERT INTO `tbl_servers` (`internal_server_id`, `server_name`, `server_region`, `server_setting`, `language_id`)
      VALUES
      (NULL, 'Avatus', 'NA', 'PvE', 1),
      (NULL, 'Bloodsworn', 'NA', 'PvE', 1),
      (NULL, 'Caretaker', 'NA', 'PvE', 1),
      (NULL, 'Mikros', 'NA', 'PvP', 1),
      (NULL, 'Orias', 'NA', 'PvE', 1),
      (NULL, 'Stormtalon', 'NA', 'PvE', 1),
      (NULL, 'Thunderfoot', 'NA', 'PvE', 1),
      (NULL, 'Pago', 'NA', 'PvP', 1),
      (NULL, 'Pergo', 'NA', 'PvP', 1),
      (NULL, 'Warbringer', 'NA', 'PvP', 1),
      (NULL, 'Widow', 'NA', 'PvP', 1),
      (NULL, 'Evindra', 'NA', 'RP-PvE', 1),
      (NULL, 'Myrcalus', 'NA', 'PvE', 1),
      (NULL, 'Rowsdower', 'NA', 'PvP', 1),
      (NULL, 'Archon', 'EU', 'PvE', 1),
      (NULL, 'Ascendancy', 'EU', 'PvE', 1),
      (NULL, 'Eko', 'EU', 'PvE', 1),
      (NULL, 'Contagion', 'EU', 'PvP', 1),
      (NULL, 'Hazak', 'EU', 'PvP', 1),
      (NULL, 'Ravenous', 'EU', 'PvP', 1),
      (NULL, 'Zhur', 'EU', 'PvP', 1),
      (NULL, 'Lightspire', 'EU', 'RP-PvE', 1),
      (NULL, 'Deadstar', 'EU', 'PvP', 2),
      (NULL, 'Ikthia', 'EU', 'PvE', 2),
      (NULL, 'Kazor', 'EU', 'PvE', 2),
      (NULL, 'Progenitor', 'EU', 'PvP', 2),
      (NULL, 'Toria', 'EU', 'RP-PvE', 2),
      (NULL, 'Gaius', 'EU', 'PvP', 3),
      (NULL, 'Stormfather', 'EU', 'PvE', 3),
      (NULL, 'Triton', 'EU', 'RP-PvE', 3);
    ",
  ),
  "2" => array(
    "INSERT INTO `tbl_languages` (`language_id`, `name`, `abbreviation`, `code`, `label`) VALUES ('1', 'English', 'en', 'en_US', 'english');",
    "INSERT INTO `tbl_languages` (`language_id`, `name`, `abbreviation`, `code`, `label`) VALUES ('2', 'Deutsch', 'de', 'de_DE', 'german');",
    "INSERT INTO `tbl_languages` (`language_id`, `name`, `abbreviation`, `code`, `label`) VALUES ('3', 'Français', 'fr', 'fr_FR', 'french');",
    "INSERT INTO `tbl_tradeskills` (`internal_tradeskill_id`, `external_tradeskill_id`, `language_string`)
      VALUES
      (NULL, '17', 'TRADESKILL_17'),
      (NULL, '12', 'TRADESKILL_12'),
      (NULL, '1', 'TRADESKILL_1'),
      (NULL, '2', 'TRADESKILL_2'),
      (NULL, '14', 'TRADESKILL_14'),
      (NULL, '16', 'TRADESKILL_16'),
      (NULL, '21', 'TRADESKILL_21'),
      (NULL, '22', 'TRADESKILL_22');
    ",
    "INSERT INTO `tbl_language_strings` (`language_string_id`, `language_id`, `language_string`, `translation`)
      VALUES
      (NULL, '1', 'TRADESKILL_17', 'Architect'),
      (NULL, '1', 'TRADESKILL_12', 'Armorer'),
      (NULL, '1', 'TRADESKILL_1', 'Weaponsmith'),
      (NULL, '1', 'TRADESKILL_2', 'Cooking'),
      (NULL, '1', 'TRADESKILL_14', 'Outfitter'),
      (NULL, '1', 'TRADESKILL_16', 'Technologist'),
      (NULL, '1', 'TRADESKILL_21', 'Tailor'),
      (NULL, '1', 'TRADESKILL_22', 'Runecrafting');
    ",
  ),
*/
  "1" => array(
    "DROP TABLE IF EXISTS `tbl_characters`;",
    "CREATE TABLE `tbl_characters` (
      `internal_character_id` int(11) NOT NULL AUTO_INCREMENT,
      `character_name` varchar(240) NOT NULL,
      `internal_server_id` tinyint(1) NOT NULL,
      `faction_id` int(11) NOT NULL,
      `last_import` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `account_id` int(11) NOT NULL COMMENT 'Internal Account ID',
      `server` varchar(80) NULL DEFAULT NULL COMMENT 'Server Name',
      `race` varchar(80) NULL DEFAULT NULL COMMENT 'Character Race',
      `class` varchar(80) NULL DEFAULT NULL COMMENT 'Character Class',
      `level` int(11) NOT NULL DEFAULT 1 COMMENT 'Character Level',
      PRIMARY KEY (`internal_character_id`),
      UNIQUE KEY `character_name` (`character_name`,`internal_server_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
    ",
    "DROP TABLE IF EXISTS `tbl_servers`;",
    "CREATE TABLE `tbl_servers` (
      `internal_server_id` tinyint(1) NOT NULL AUTO_INCREMENT,
      `server_name` varchar(120) NOT NULL,
      `server_setting` varchar(6) DEFAULT 'PvE' NOT NULL,
      `language_id` tinyint(1) NOT NULL,
      PRIMARY KEY (`internal_server_id`),
      UNIQUE KEY `server_name` (`server_name`)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
    ",
    "INSERT INTO `tbl_servers` (`internal_server_id`, `server_name`, `server_setting`, `language_id`)
      VALUES
      (NULL, 'Lockjaw', 'TLP', 1),
      (NULL, 'Phinigel', 'TLP', 1),
      (NULL, 'Ragefire', 'TLP', 1);
    ",
    "DROP TABLE IF EXISTS `tbl_items`;",
    "CREATE TABLE `tbl_items` (
      `internal_item_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal Item ID',
      `external_item_id` int(11) NOT NULL COMMENT 'Item ID from EQ',
      `item_name` varchar(120) NOT NULL COMMENT 'EQ Item Name',
      `item_location` varchar(120) NOT NULL COMMENT 'EQ Item Location',
      `internal_slot_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Internal Item Slot ID',
      `internal_character_id` int(11) NOT NULL COMMENT 'Internal Character ID the item belongs to',
      `item_count` tinyint(20) NOT NULL DEFAULT 1 COMMENT 'Item Count',
      `language_id` tinyint(1) NOT NULL DEFAULT 1,
      PRIMARY KEY (`internal_item_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
    ",
  )
);

?>