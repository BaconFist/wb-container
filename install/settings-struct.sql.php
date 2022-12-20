-- <?php header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;?>
-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 14. Aug 2014 um 10:46
-- Server Version: 5.5.32
-- PHP-Version: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- --------------------------------------------------------
-- Database structure for module 'news'
--
-- Replacements: {TABLE_PREFIX}, {TABLE_ENGINE}, {FIELD_COLLATION}
--
-- --------------------------------------------------------
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}settings`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}settings` (
  `name` varchar(160){FIELD_COLLATION} NOT NULL DEFAULT '',
  `value` text{FIELD_COLLATION} NOT NULL,
  PRIMARY KEY (`name`)
){TABLE_ENGINE};
ALTER TABLE `{TABLE_PREFIX}settings` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}settings` MODIFY  `name` VARCHAR(160) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}settings` MODIFY `value` longtext {XFIELD_COLLATION} NOT NULL;
-- ALTER TABLE `{TABLE_PREFIX}settings` ADD UNIQUE `ident` ( `name` ) USING BTREE;

--
