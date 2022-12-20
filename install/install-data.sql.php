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
INSERT INTO `{TABLE_PREFIX}groups` SET `name` = 'Administrators',`system_permissions` = 'access,addons,admin,admin_basic,admin_advanced,admintools,admintools_view,admintools_advanced,groups,groups_view,groups_add,groups_delete,groups_modify,languages,languages_view,languages_install,languages_uninstall,media,media_view,media_create,media_delete,media_rename,media_upload,modules,modules_view,modules_advanced,modules_install,modules_uninstall,modules_settings,pages,pages_view,pages_add,pages_add_l0,pages_delete,pages_intro,pages_modify,pages_settings,preferences,preferences_view,settings,settings_view,settings_basic,settings_advanced,templates,templates_view,templates_install,templates_uninstall,users,users_view,users_add,users_delete,users_modify',`module_permissions` = '',`template_permissions` = '';
--
-- Daten für Tabelle `search`
--
INSERT INTO `{TABLE_PREFIX}search` (`search_id`, `name`, `value`, `extra`) VALUES
(1, 'header', '\n<h1>[TEXT_SEARCH]</h1>\n\n<form id="searchpage" action="[WB_URL]/search/index.php" method="get">\n<table>\n<tr>\n<td>\n<input type="hidden" name="search_path" value="[SEARCH_PATH]" />\n<input type="text" name="string" value="[SEARCH_STRING]" style="width: 100%;" />\n</td>\n<td width="150">\n<input type="submit" value="[TEXT_SEARCH]" style="width: 100%;cursor: pointer;" />\n</td>\n</tr>\n<tr>\n<td colspan="2">\n<input type="radio" name="match" id="match_all" value="all"[ALL_CHECKED] />\n<label for="match_all">[TEXT_ALL_WORDS]</label>\n<input type="radio" name="match" id="match_any" value="any"[ANY_CHECKED] />\n<label for="match_any">[TEXT_ANY_WORDS]</label>\n<input type="radio" name="match" id="match_exact" value="exact"[EXACT_CHECKED] />\n<label for="match_exact">[TEXT_EXACT_MATCH]</label>\n</td>\n</tr>\n</table>\n\n</form>\n\n<hr />\n    ', ''),
(2, 'footer', '', ''),
(3, 'results_header', '[TEXT_RESULTS_FOR] ''<b>[SEARCH_STRING]</b>'':\n<table style="padding-top: 10px;width: 100%;">', ''),
(4, 'results_loop', '<tr style="background-color: #F0F0F0;">\n<td><a href="[LINK]">[TITLE]</a></td>\n<td style="float: right;">[TEXT_LAST_UPDATED_BY] [DISPLAY_NAME] [TEXT_ON] [DATE]</td>\n</tr>\n<tr><td colspan="2" style="text-align: justify; padding-bottom: 5px;">[DESCRIPTION]</td></tr>\n<tr><td colspan="2" style="text-align: justify; padding-bottom: 10px;">[EXCERPT]</td></tr>', ''),
(5, 'results_footer', '</table>', ''),
(6, 'no_results', '<tr><td><p>[TEXT_NO_RESULTS]</p></td></tr>', ''),
(7, 'module_order', 'faqbaker,manual,wysiwyg', ''),
(8, 'max_excerpt', '15', ''),
(9, 'time_limit', '0', ''),
(10, 'cfg_enable_old_search', 'true', ''),
(11, 'cfg_search_keywords', 'true', ''),
(12, 'cfg_search_description', 'true', ''),
(13, 'cfg_show_description', 'true', ''),
(14, 'cfg_enable_flush', 'false', ''),
(15, 'template', '', '');
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
( 'rename_files_on_upload', 'ph.*?,cgi,pl,pm,exe,com,bat,pif,cmd,src,asp,aspx,js'),
( 'media_width', '0'),
( 'media_height', '0'),
( 'media_compress', '85'),
( 'string_dir_mode', '0755'),
( 'string_file_mode', '0644'),
( 'twig_version', '3'),
( 'jquery_version', '1.9.1'),
( 'jquery_cdn_link', ''),
( 'wbmailer_routine', 'phpmail'),
( 'wbmailer_default_sendername', 'WB Mailer'),
( 'wbmailer_smtp_debug', '0'),
( 'wbmailer_smtp_host', ''),
( 'wbmailer_smtp_auth', ''),
( 'wbmailer_smtp_username', ''),
( 'wbmailer_smtp_password', ''),
( 'sec_token_fingerprint', 'true'),
( 'sec_token_netmask4', '24'),
( 'sec_token_netmask6', '64'),
( 'sec_token_life_time', '1800'),
( 'show_start_datetime', 'false'),
( 'debug', 'false'),
( 'dev_infos', 'false'),
( 'sgc_execute', 'false'),
( 'system_locked', 'false'),
( 'user_login', 'true'),
( 'wbmailer_smtp_port', '25'),
( 'wbmailer_smtp_secure', 'TLS'),
( 'wbmailer_low_security', 'false'),
( 'mediasettings', ''),
( 'dsgvo_settings', 'a:3:{s:19:"use_data_protection";b:1;s:2:"DE";i:0;s:2:"EN";i:0;}'),
( 'media_version', '1.0.0');

