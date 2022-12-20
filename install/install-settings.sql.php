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
-- Tabellenstruktur für Tabelle `settings`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}settings`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}settings` (
  `name` varchar(250){FIELD_COLLATION} NOT NULL DEFAULT '',
  `value` text{FIELD_COLLATION} NOT NULL,
  PRIMARY KEY (`name`)
){TABLE_ENGINE};
ALTER TABEL `{TABLE_PREFIX}settings` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}settings` {FIELD_COLLATION};
ALTER TABLE `{TABLE_PREFIX}settings` MODIFY `name` VARCHAR(250) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}settings` MODIFY `value` longtext {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}settings` ENABLE KEYS;

--
-- Daten für Tabelle `settings`
--
INSERT INTO `{TABLE_PREFIX}settings` ( `name`, `value`) VALUES
( 'website_description', ''),
( 'website_keywords', ''),
( 'website_header', ''),
( 'website_footer', ''),
( 'website_signature', ''),
( 'wysiwyg_style', 'font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;'),
( 'er_level', '32767'),
( 'sec_anchor', 'Sec'),
( 'default_date_format', 'm-d-Y'),
( 'default_time_format', 'g:i|A'),
( 'redirect_timer', '1000'),
( 'home_folders', 'false'),
( 'warn_page_leave', '1'),
( 'confirmed_registration', 'false'),
( 'default_template', 'DefaultTemplate'),
( 'default_theme', 'DefaultTheme'),
( 'default_charset', 'utf-8'),
( 'multiple_menus', 'true'),
( 'page_level_limit', '4'),
( 'intro_page', 'false'),
( 'page_trash', 'inline'),
( 'homepage_redirection', 'false'),
( 'page_languages', 'true'),
( 'wysiwyg_editor', 'ckeditor'),
( 'manage_sections', 'true'),
( 'section_blocks', 'true'),
( 'smart_login', 'true'),
( 'frontend_login', 'false'),
( 'frontend_signup', 'false'),
( 'search', 'public'),
( 'page_oldstyle', 'false'),
( 'page_newstyle', 'true'),
( 'page_extension', '.php'),
( 'page_spacer', '-'),
( 'pages_directory', '/pages'),
( 'page_icon_dir', '/templates/*/title_images'),
( 'media_directory', '/media'),
( 'rename_files_on_upload', 'ph.*?,cgi,pl,pm,exe,com,bat,pif,cmd,src,asp,aspx,js,inc'),
( 'media_width', '0'),
( 'media_height', '0'),
( 'media_compress', '85'),
( 'twig_version', '3'),
( 'jquery_version', '1.9.1'),
( 'jquery_cdn_link', ''),
( 'wbmailer_routine', 'phpmail'),
( 'wbmailer_default_sendername', 'WB Mailer'),
( 'wbmailer_smtp_debug', '0'),
( 'wbmailer_smtp_host', ''),
( 'wbmailer_smtp_auth', 'true'),
( 'wbmailer_smtp_username', ''),
( 'wbmailer_smtp_password', ''),
( 'sec_token_fingerprint', 'true'),
( 'sec_token_netmask4', '24'),
( 'sec_token_netmask6', '64'),
( 'sec_token_life_time', '1800'),
( 'show_start_datetime', 'false'),
( 'debug', 'false'),
( 'dev_infos', '0'),
( 'system_locked', 'false'),
( 'user_login', 'true'),
( 'wbmailer_smtp_port', '25'),
( 'wbmailer_smtp_secure', 'TLS'),
( 'wbmailer_low_security', 'false'),
( 'mediasettings', ''),
( 'media_version', '1.0.0'),
( 'wb_version', '2.13.1'),
( 'wb_revision', '103'),
( 'wb_sp', ''),
( 'patch_revision', '103'),
( 'patch_update', '0'),
( 'website_title', 'Enter your Website Title'),
( 'default_language', ''),
--( 'app_name', {APP_NAME}),
( 'default_timezone', ''),
( 'operating_system', 'UNIX'),
( 'string_dir_mode', '0755'),
( 'string_file_mode', '0644'),
( 'dsgvo_settings', 'a:3:{s:19:"use_data_protection";b:1;s:2:"DE";i:0;s:2:"EN";i:0;}'),
( 'server_email', 'admin@example.com');

