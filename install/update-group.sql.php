-- <?php header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;?>
-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 14. Aug 2014 um 10:47
-- Server Version: 5.5.32
-- PHP-Version: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
--
-- Daten für Tabelle `groups`
--
UPDATE `{TABLE_PREFIX}groups` SET `name` = 'Administrators',`system_permissions` = 'access,addons,admin,admin_basic,admin_advanced,admintools,admintools_view,admintools_advanced,groups,groups_view,groups_add,groups_delete,groups_modify,languages,languages_view,languages_install,languages_uninstall,media,media_view,media_create,media_delete,media_rename,media_upload,modules,modules_view,modules_advanced,modules_install,modules_uninstall,modules_settings,pages,pages_view,pages_add,pages_add_l0,pages_delete,pages_intro,pages_modify,pages_settings,preferences,preferences_view,settings,settings_view,settings_basic,settings_advanced,templates,templates_view,templates_install,templates_uninstall,users,users_view,users_add,users_delete,users_modify',`module_permissions` = '',`template_permissions` = '' WHERE `group_id` = 1;
