-- <?php \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;?>
-- phpMyAdmin SQL Dump
-- version 4.5.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 01. Feb 2016 um 19:54
-- Server-Version: 5.6.24
-- PHP-Version: 7.0.1
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
-- --------------------------------------------------------
-- Database structure for module 'captcha_control'
--
-- Replacements: {TABLE_PREFIX}, {TABLE_ENGINE}, {FIELD_COLLATION}
--
-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `{TABLE_PREFIX}mod_captcha_control`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_captcha_control`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_captcha_control` (
  `captcha_type` varchar(128) NOT NULL DEFAULT 'calc_text',
  `enabled_captcha` int(11) NOT NULL DEFAULT '0',
  `enabled_asp` int(11) NOT NULL DEFAULT '1',
  `asp_session_min_age` int(11) NOT NULL DEFAULT '20',
  `asp_view_min_age` int(11) NOT NULL DEFAULT '10',
  `asp_input_min_age` int(11) NOT NULL DEFAULT '5',
  `ct_text` text COLLATE utf8_unicode_ci NOT NULL,
  `ct_color` int(11) NOT NULL DEFAULT '1',
  `use_sec_type` int(11) NOT NULL DEFAULT '-1',
  `code_length` int(11) NOT NULL DEFAULT '5',
  `image_width` int(11) NOT NULL DEFAULT '225',
  `image_height` int(11) NOT NULL DEFAULT '85',
  `num_lines` int(11) NOT NULL DEFAULT '3',
  `noise_level` int(11) NOT NULL DEFAULT '5',
  `captcha_expiration` int(11) NOT NULL DEFAULT '900',
  `image_bg_dir` varchar(128){FIELD_COLLATION} NOT NULL DEFAULT 'backgrounds/',
  `image_bg_color` varchar(8){FIELD_COLLATION} NOT NULL DEFAULT 'F2F2F2',
  `ttf_file` varchar(128){FIELD_COLLATION} NOT NULL DEFAULT 'fonts/AHGBold.ttf',
  `text_color` varchar(8){FIELD_COLLATION} NOT NULL DEFAULT '7D7D7D',
  `line_color` varchar(8){FIELD_COLLATION} NOT NULL DEFAULT '7D7D7D',
  `noise_color` varchar(8){FIELD_COLLATION} NOT NULL DEFAULT '7D7D7D',
  `signature_color` varchar(8) NOT NULL DEFAULT '777777',
  `image_signature` varchar(128) {FIELD_COLLATION} NOT NULL DEFAULT ''
) {TABLE_ENGINE};

ALTER TABEL `{TABLE_PREFIX}mod_captcha_control` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` MODIFY `captcha_type` VARCHAR(128){FIELD_COLLATION} NOT NULL DEFAULT 'calc_text';
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` MODIFY `enabled_captcha` int(11) NOT NULL DEFAULT '0' AFTER `captcha_type`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` MODIFY `enabled_asp` int(11) NOT NULL DEFAULT '1' AFTER `enabled_captcha`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` MODIFY `ct_text` text {FIELD_COLLATION} NOT NULL AFTER `asp_input_min_age`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `ct_color` int(11) NOT NULL DEFAULT '1' AFTER `ct_text`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `use_sec_type` int(11) NOT NULL DEFAULT '-1' AFTER `ct_color`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `code_length` int(11) NOT NULL DEFAULT '5' AFTER `use_sec_type`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `image_width` int(11) NOT NULL DEFAULT '225' AFTER `code_length`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `image_height` int(11) NOT NULL DEFAULT '85' AFTER `image_width`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `num_lines` int(11) NOT NULL DEFAULT '3' AFTER `image_height`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `noise_level` int(11) NOT NULL DEFAULT '5' AFTER `num_lines`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `captcha_expiration` int(11) NOT NULL DEFAULT '900' AFTER `noise_level`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `image_bg_dir` varchar(128) NOT NULL DEFAULT 'backgrounds/' AFTER `captcha_expiration`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` MODIFY `image_bg_dir` varchar(128){FIELD_COLLATION} NOT NULL DEFAULT 'backgrounds/';
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `image_bg_color` varchar(16){FIELD_COLLATION} NOT NULL DEFAULT 'F2F2F2' AFTER `image_bg_dir`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `ttf_file` varchar(128){FIELD_COLLATION} NOT NULL DEFAULT 'LLBi_cond.ttf' AFTER `image_bg_color`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` MODIFY `ttf_file` varchar(128){FIELD_COLLATION} NOT NULL DEFAULT 'fonts/LLBi_cond.ttf';
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `text_color` varchar(16){FIELD_COLLATION} NOT NULL DEFAULT '384C7D' AFTER `ttf_file`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `line_color` varchar(16){FIELD_COLLATION} NOT NULL DEFAULT '7D7D7D' AFTER `text_color`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `noise_color` varchar(16){FIELD_COLLATION} NOT NULL DEFAULT '7D7D7D' AFTER `line_color`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `signature_color` varchar(16){FIELD_COLLATION} NOT NULL DEFAULT '777777' AFTER `noise_color`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ADD `image_signature` varchar(128){FIELD_COLLATION} NOT NULL DEFAULT '' AFTER `signature_color`;
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` MODIFY `image_signature` varchar(128){FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` MODIFY `captcha_expiration` int(11) NOT NULL DEFAULT '1440';
ALTER TABLE `{TABLE_PREFIX}mod_captcha_control` ENABLE KEYS;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
