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
--
-- Tabellenstruktur für Tabelle `mod_news_comments`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_news_comments`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_news_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `post_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255){FIELD_COLLATION} NOT NULL DEFAULT '',
  `comment` text{FIELD_COLLATION} NOT NULL,
  `commented_when` int(11) NOT NULL DEFAULT '0',
  `commented_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`)
){TABLE_ENGINE};
ALTER TABEL `{TABLE_PREFIX}mod_news_comments` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_news_comments` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_news_comments` ADD `active` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `{TABLE_PREFIX}mod_news_comments` MODIFY `title` VARCHAR(255) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_news_comments` MODIFY `comment` text {XFIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_comments` ENABLE KEYS;
-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `mod_news_groups`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_news_groups`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_news_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `title` varchar(150){FIELD_COLLATION} NOT NULL DEFAULT '',
  PRIMARY KEY (`group_id`)
){TABLE_ENGINE};
ALTER TABEL `{TABLE_PREFIX}mod_news_groups` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_news_groups` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_news_groups` DROP INDEX `ident_news`;
ALTER TABLE `{TABLE_PREFIX}mod_news_groups` MODIFY `title` VARCHAR(150) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_news_groups` ADD UNIQUE `ident_news` ( `section_id`,`title` ) USING BTREE;
ALTER TABLE `{TABLE_PREFIX}mod_news_groups` ENABLE KEYS;
-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `mod_news_posts`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_news_posts`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_news_posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `title` varchar(250){FIELD_COLLATION} NOT NULL DEFAULT '',
  `link` text{FIELD_COLLATION} NOT NULL,
  `content_short` text{FIELD_COLLATION} NOT NULL,
  `content_long` text{FIELD_COLLATION} NOT NULL,
  `commenting` varchar(7){FIELD_COLLATION} NOT NULL DEFAULT '',
  `created_when` int(11) NOT NULL DEFAULT '0',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `published_when` int(11) NOT NULL DEFAULT '0',
  `published_until` int(11) NOT NULL DEFAULT '0',
  `posted_when` int(11) NOT NULL DEFAULT '0',
  `posted_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`)
){TABLE_ENGINE};
ALTER TABEL `{TABLE_PREFIX}mod_news_posts` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` DROP INDEX `ident`;
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` DROP INDEX `ident_post`;
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` ADD `modified_when` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` ADD `modified_by` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` MODIFY `title` VARCHAR(250) {XFIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` MODIFY `link` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` MODIFY `content_short` text {XFIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` MODIFY `content_long` text {XFIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` MODIFY `commenting` VARCHAR(7) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` ADD `moderated` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` ADD UNIQUE `ident_post` ( `post_id`, `title` ) USING BTREE;
ALTER TABLE `{TABLE_PREFIX}mod_news_posts` ENABLE KEYS;

-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `mod_news_layouts`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_news_layouts`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_news_layouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `layout` varchar(128){FIELD_COLLATION} NOT NULL DEFAULT '',
  `header` mediumtext {FIELD_COLLATION} NOT NULL,
  `post_loop` mediumtext {FIELD_COLLATION} NOT NULL,
  `footer` mediumtext {FIELD_COLLATION} NOT NULL,
  `post_header` mediumtext {FIELD_COLLATION} NOT NULL,
  `post_footer` mediumtext {FIELD_COLLATION} NOT NULL,
  `comments_header` mediumtext {FIELD_COLLATION} NOT NULL,
  `comments_loop` mediumtext {FIELD_COLLATION} NOT NULL,
  `comments_footer` mediumtext {FIELD_COLLATION} NOT NULL,
  `comments_page` mediumtext {FIELD_COLLATION} NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `layout_name` (`id`,`layout`)
) {TABLE_ENGINE};

ALTER TABEL `{TABLE_PREFIX}mod_news_layouts` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_news_layouts` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_news_layouts` DROP INDEX `layout_name`;
ALTER TABLE `{TABLE_PREFIX}mod_news_layouts` DROP INDEX `ident_layout`;
ALTER TABLE `{TABLE_PREFIX}mod_news_layouts` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `{TABLE_PREFIX}mod_news_layouts` ADD INDEX `ident_layout` ( `id`,`layout`) USING BTREE;
ALTER TABLE `{TABLE_PREFIX}mod_news_layouts` ENABLE KEYS;

-- --------------------------------------------------------
-- Tabellenstruktur für Tabelle `mod_news_settings`
--
DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_news_settings`;
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}mod_news_settings` (
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `header` text{FIELD_COLLATION} NOT NULL,
  `post_loop` text{FIELD_COLLATION} NOT NULL,
  `footer` text{FIELD_COLLATION} NOT NULL,
  `posts_per_page` int(11) NOT NULL DEFAULT '5',
  `post_header` text{FIELD_COLLATION} NOT NULL,
  `post_footer` text{FIELD_COLLATION} NOT NULL,
  `comments_header` text{FIELD_COLLATION} NOT NULL,
  `comments_loop` text{FIELD_COLLATION} NOT NULL,
  `comments_footer` text{FIELD_COLLATION} NOT NULL,
  `comments_page` text{FIELD_COLLATION} NOT NULL,
  `commenting` varchar(7){FIELD_COLLATION} NOT NULL DEFAULT '',
  `resize` int(11) NOT NULL DEFAULT '0',
  `use_captcha` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`section_id`)
){TABLE_ENGINE};

ALTER TABEL `{TABLE_PREFIX}mod_news_settings` DISABLE KEYS;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` {XTABLE_ENGINE=InnoDB};
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP INDEX `ident_news`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP INDEX `ident_settings`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` DROP INDEX `layout`;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `header` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `post_loop` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `footer` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `post_header` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `post_footer` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `comments_header` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `comments_loop` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `comments_footer` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `comments_page` text {FIELD_COLLATION} NOT NULL;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `order` varchar(7){FIELD_COLLATION} NOT NULL DEFAULT 'DESC';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `layout_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `layout` varchar(50){FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `order_field` varchar(50){FIELD_COLLATION} NOT NULL DEFAULT 'published_when';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `data_protection_link` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `use_data_protection` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD `commenting` varchar(7){FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` MODIFY `order_field` varchar(50){FIELD_COLLATION} NOT NULL DEFAULT 'published_when';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` MODIFY `layout` varchar(128) {FIELD_COLLATION} NOT NULL DEFAULT '';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` MODIFY `commenting` varchar(14){FIELD_COLLATION} NOT NULL DEFAULT 'none';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` MODIFY `data_protection_link` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` MODIFY `layout_id` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` MODIFY `layout` varchar(64){FIELD_COLLATION} NOT NULL DEFAULT 'default_layout';
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ADD INDEX `layout` ( `layout_id`) USING BTREE;
ALTER TABLE `{TABLE_PREFIX}mod_news_settings` ENABLE KEYS;


-- EndOfFile
