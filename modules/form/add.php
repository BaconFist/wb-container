<?php
/**
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category        addons
 * @package         form
 * @subpackage      add
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.13.x
 * @requirements    PHP 7.4.x and higher
 * @version         0.0.1
 * @revision        $Id: $
 * @since           File available since 12.11.2017
 * @deprecated      no / since 0000/00/00
 * @description     xxx
 *
 */
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!defined('SYSTEM_RUN')) {header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;
/* -------------------------------------------------------- */
} else {
    $table_name = TABLE_PREFIX.'mod_form_settings';
    $field_name = 'perpage_submissions';
    $description = "INT NOT NULL DEFAULT '10' AFTER `max_submissions`";
    if(!$database->field_exists($table_name,$field_name)) {
        $database->field_add($table_name, $field_name, $description);
    }
    if (is_readable(__DIR__.'/data/layouts/Layout_Default.inc.php')){require (__DIR__.'/data/layouts/Layout_Default.inc.php');}
    $email_to = '';
    $email_from = '';
    $email_fromname = '';
    $email_subject = '';
    $success_page = 0;
    $success_email_to = '';
    $success_email_from = '';
    $success_email_fromname = '';
    $success_email_text = '';
    // $success_email_text = addslashes($success_email_text);
    $success_email_subject = '';
    $max_submissions = 50;
    $stored_submissions = 0;
    $delete_submissions = 0;
    $perpage_submissions = 10;
    $use_captcha = true;
    $captcha_action = 'all';
    $captcha_style = '';
    $layout = '';
    // Insert settings
    $sql  = 'INSERT INTO  `'.TABLE_PREFIX.'mod_form_settings` SET '
          . '`section_id` = '.(int)($section_id).', '
          . '`page_id` = '.(int)($page_id).', '
          . '`header` = \''.$database->escapeString($header).'\', '
          . '`field_loop` = \''.$database->escapeString($field_loop).'\', '
          . '`extra` = \''.$database->escapeString($extra).'\', '
          . '`footer` = \''.$database->escapeString($footer).'\', '
          . '`email_to` = \''.$database->escapeString($email_to).'\', '
          . '`email_from` = \''.$database->escapeString($email_from).'\', '
          . '`email_fromname` = \''.$database->escapeString($email_fromname).'\', '
          . '`email_subject` = \''.$database->escapeString($email_subject).'\', '
          . '`success_page` = '.(int)($success_page).', '
          . '`success_email_to` = \''.$database->escapeString($success_email_to).'\', '
          . '`success_email_from` = \''.$database->escapeString($success_email_from).'\', '
          . '`success_email_fromname` = \''.$database->escapeString($success_email_fromname).'\', '
          . '`success_email_text` = \''.$database->escapeString($success_email_text).'\', '
          . '`success_email_subject` = \''.$database->escapeString($success_email_subject).'\', '
          . '`max_submissions` = '.(int)($max_submissions).', '
          . '`stored_submissions` = '.(int)($stored_submissions).', '
          . '`delete_submissions` = '.(int)($delete_submissions).', '
          . '`perpage_submissions` = '.(int)($perpage_submissions).', '
          . '`use_captcha` = '.(int)($use_captcha).', '
          . '`captcha_action` = \''.$database->escapeString($captcha_action).'\', '
          . '`captcha_style` = \''.$database->escapeString($captcha_style).'\', '
          . '`layout` = \''.$database->escapeString($layout).'\' ';
   if (!$database->query($sql)) { }
}
