-- <?php header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;?>
--------------------------------------------------------
-- SQL-Import-Struct-File
-- generated with ConvertDump Version 0.2.1
-- WebsiteBaker Edition
-- Creation time: Tue, 03 Feb 2015 11:25:46 +0100
-- --------------------------------------------------------
-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 03. Feb 2015 um 11:14
-- Server Version: 5.5.27
-- PHP-Version: 5.4.19
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
--
-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `{TABLE_PREFIX}mod_form_fields`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_form_fields`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_form_fields` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  `type` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  `required` int(11) NOT NULL DEFAULT '0',
  `value` text{FIELD_COLLATION} NOT NULL,
  `extra` text{FIELD_COLLATION} NOT NULL,
  PRIMARY KEY (`field_id`)
){TABLE_ENGINE};
ALTER TABEL `{TABLE_PREFIX}mod_form_fields` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_form_fields` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_form_fields` MODIFY `title` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_fields` MODIFY `type` VARCHAR(200) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_fields` MODIFY `value` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_form_fields` MODIFY `extra` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_form_fields` ADD `active` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `{TABLE_PREFIX}mod_form_fields` ADD `layout` VARCHAR(200) {FIELD_COLLATION} NOT NULL DEFAULT '' AFTER `page_id`;
ALTER TABLE `{TABLE_PREFIX}mod_form_fields` MODIFY `active` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `{TABLE_PREFIX}mod_form_fields` ENABLE KEYS;
-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `{TABLE_PREFIX}mod_form_settings`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_form_settings`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_form_settings` (
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `header` text{FIELD_COLLATION} NOT NULL,
  `field_loop` text{FIELD_COLLATION} NOT NULL,
  `footer` text{FIELD_COLLATION} NOT NULL,
  `email_to` text{FIELD_COLLATION} NOT NULL,
  `email_from` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  `email_fromname` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  `email_subject` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  `success_page`  int(11) NOT NULL DEFAULT '0',
  `success_email_to` text{FIELD_COLLATION} NOT NULL,
  `success_email_from` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  `success_email_fromname` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  `success_email_text` text{FIELD_COLLATION} NOT NULL,
  `success_email_subject` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  `stored_submissions` int(11) NOT NULL DEFAULT '0',
  `max_submissions` int(11) NOT NULL DEFAULT '0',
  `perpage_submissions` int(11) NOT NULL DEFAULT '10',
  `use_captcha` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`section_id`)
){TABLE_ENGINE};
ALTER TABEL `{TABLE_PREFIX}mod_form_settings` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `extra` text {FIELD_COLLATION} NOT NULL AFTER `field_loop`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `success_page` int(11) NOT NULL DEFAULT '0' AFTER `email_subject`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `captcha_action` VARCHAR(40) {FIELD_COLLATION} NOT NULL DEFAULT 'all' AFTER `use_captcha`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `captcha_style` text {FIELD_COLLATION} NOT NULL AFTER `captcha_action`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `subject_email` VARCHAR(128) {FIELD_COLLATION} NOT NULL DEFAULT '' AFTER `use_captcha`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `divider` VARCHAR(128){FIELD_COLLATION} NOT NULL DEFAULT '' AFTER `captcha_action`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `layout` varchar(200){FIELD_COLLATION} NOT NULL DEFAULT '' AFTER `captcha_style`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `description` VARCHAR(512) {FIELD_COLLATION} NOT NULL DEFAULT '' AFTER `layout`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `data_protection_link` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `use_data_protection` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `use_captcha_auth` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `prevent_user_confirmation` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `info_dsgvo_in_mail` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `title_placeholder` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `form_required` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `delete_submissions` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `spam_submissions` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ADD `frontend_css` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `form_required` int(11) NOT NULL DEFAULT '0' AFTER `title_placeholder`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `frontend_css` int(11) NOT NULL DEFAULT '0' AFTER `form_required`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `header` text {FIELD_COLLATION} NOT NULL AFTER `frontend_css`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `field_loop` text {FIELD_COLLATION} NOT NULL AFTER `header`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `extra` text {FIELD_COLLATION} NOT NULL AFTER `field_loop`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `footer` text {FIELD_COLLATION} NOT NULL AFTER `extra`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `email_to` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `email_from` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `email_fromname` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `email_subject` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `success_page` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `success_email_to` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `success_email_from` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `success_email_fromname` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `success_email_text` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `success_email_subject` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `delete_submissions` int(11) NOT NULL DEFAULT '0' AFTER `stored_submissions`;

ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `captcha_action` VARCHAR(40) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `captcha_style` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `layout` VARCHAR(200) {FIELD_COLLATION} NOT NULL DEFAULT '' AFTER `page_id`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `description` VARCHAR(512) {FIELD_COLLATION} NOT NULL DEFAULT '' AFTER `layout`;
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` MODIFY `data_protection_link` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `{TABLE_PREFIX}mod_form_settings` ENABLE KEYS;
-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `{TABLE_PREFIX}mod_form_submissions`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_form_submissions`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_form_submissions` (
  `submission_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `submitted_when` int(11) NOT NULL DEFAULT '0',
  `submitted_by` int(11) NOT NULL DEFAULT '0',
  `body` text{FIELD_COLLATION} NOT NULL,
  PRIMARY KEY (`submission_id`)
){TABLE_ENGINE};
ALTER TABEL `{TABLE_PREFIX}mod_form_submissions` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_form_submissions` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_form_submissions` MODIFY `body` text {XFIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_form_submissions` ADD `form_ip` VARCHAR(40){FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_submissions` MODIFY `form_ip` VARCHAR(40){FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_form_submissions` ENABLE KEYS;
--

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- --------------------------------------------------------
-- END OF SQL-Import-Struct-File
-- --------------------------------------------------------
