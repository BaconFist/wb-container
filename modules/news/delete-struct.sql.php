-- <?php \header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden'); echo '403 Forbidden'; \flush(); exit;?>
-- phpMyAdmin SQL Dump
-- Erstellungszeit: 20. Januar 2012 um 12:37
-- Server Version: 5.1.41
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
-- --------------------------------------------------------
-- Database structure for module 'news'
--
-- Replacements: {TABLE_PREFIX}, {TABLE_ENGINE}, {FIELD_COLLATION}
--
-- --------------------------------------------------------
-- Tabellenstruktur für Tabelle `mod_news_settings`
--
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP `header`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP `post_loop`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP `footer`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP `post_header`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP `post_footer`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP `comments_header`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP `comments_loop`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP `comments_footer`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP `comments_page`;

-- EndOfFile
