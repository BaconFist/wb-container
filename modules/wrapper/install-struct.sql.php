-- <?php \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;?>
-- phpMyAdmin SQL Dump
-- Erstellungszeit: 17. Dezember 2015 um 12:37
-- Server Version: 5.1.41
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
-- --------------------------------------------------------
-- Database structure for module 'wrapper'
--
-- Replacements: {TABLE_PREFIX}, {TABLE_ENGINE}, {TABLE_COLLATION}
--
-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `mod_wrapper`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wrapper`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_wrapper` (
  `section_id` INT NOT NULL DEFAULT '0',
  `page_id` INT NOT NULL DEFAULT '0',
  `url` VARCHAR(512){FIELD_COLLATION} NOT NULL DEFAULT '',
  `height` INT NOT NULL DEFAULT '0',
  PRIMARY KEY ( `section_id` )
){TABLE_ENGINE};
ALTER TABEL `{TABLE_PREFIX}mod_wrapper` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_wrapper` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_wrapper` CHANGE `url` `url` VARCHAR(512) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_wrapper` ADD `min_height` INT NOT NULL DEFAULT '400' AFTER `height`;
ALTER TABLE `{TABLE_PREFIX}mod_wrapper` ADD `attribute` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '' AFTER `height`;
ALTER TABLE `{TABLE_PREFIX}mod_wrapper` MODIFY `attribute` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '' AFTER `min_height`;
ALTER TABLE `{TABLE_PREFIX}mod_wrapper` MODIFY `height` INT NOT NULL DEFAULT '400';
ALTER TABLE `{TABLE_PREFIX}mod_wrapper` ENABLE KEYS;

-- EndOfFile
