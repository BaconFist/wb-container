-- <?php header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;?>
-- --------------------------------------------------------
-- SQL-Import-Struct-File
-- generated with ConvertDump Version 0.2.1
-- WebsiteBaker Edition
-- Creation time: Fri, 29 Jan 2016 14:11:55 +0100
-- --------------------------------------------------------
-- phpMyAdmin SQL Dump
-- version 4.5.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 29. Jan 2016 um 14:10
-- Server-Version: 5.6.24
-- PHP-Version: 7.0.1
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
--
-- Datenbank: `dw283-sp3db1`
--
-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `mod_jsadmin`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_jsadmin`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_jsadmin` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  `value` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
){TABLE_ENGINE};

ALTER TABEL `{TABLE_PREFIX}mod_jsadmin` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_jsadmin` {TABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_jsadmin` {FIELD_COLLATION};
ALTER TABLE `{TABLE_PREFIX}mod_jsadmin` MODIFY `name` VARCHAR(250) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_jsadmin` ENABLE KEYS;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- --------------------------------------------------------
-- END OF SQL-Import-Struct-File
-- --------------------------------------------------------
