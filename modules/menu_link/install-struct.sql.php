-- <?php header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;?>
-- phpMyAdmin SQL Dump
-- version 4.5.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 31. Jan 2016 um 22:35
-- Server-Version: 5.6.24
-- PHP-Version: 7.0.1
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
--
-- Datenbank: `dw283-sp3db1`
--
-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `mod_menu_link`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_menu_link`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_menu_link` (
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `target_page_id` int(11) NOT NULL DEFAULT '0',
  `redirect_type` int(11) NOT NULL DEFAULT '301',
  `anchor` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '0',
  `extern` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  PRIMARY KEY (`section_id`)
){TABLE_ENGINE=InnoDB};

ALTER TABEL `{TABLE_PREFIX}mod_menu_link` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_menu_link` {TABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_menu_link` MODIFY `anchor` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_menu_link` MODIFY `extern` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_menu_link` ENABLE KEYS;

