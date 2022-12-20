<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of \admin\addons\EN
 * @package      CoreTranslation
 * @copyright    Manuela v.d.Decken
 * @author       Manuela v.d.Decken
 * @license      GNU General Public License 2.0
 * @version      4.0.0
 * @revision     $Id: EN.php 252 2019-03-17 17:58:36Z Luisehahne $
 * @since        File available since 18.10.2017
 * @deprecated   no
 * @description  xxx
 */

//declare(strict_types = 1);

//declare(encoding = 'UTF-8');

if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

// Set the language information
$language_code = 'EN';
$language_name = 'English';
$language_version = '4.0.0';
$language_platform = '2.11.0';
$language_author = 'Manuela v.d.Decken, Dietmar Wöllbrink';
$language_license = 'GNU General Public License 2.0';

$TEXT['PAGE_LANGUAGES'] = 'Multilingualism';
$TEXT['PAGES_ACCESSSTYLE'] = 'Pages Access Files';
$TEXT['TWIG_VERSION'] = 'Twig Version';
$TEXT['JQUERY_VERSION'] = 'jQuery Version';
$TEXT['JQUERY_CDN_LINK'] = 'Extern Url (CDN)';
$TEXT['CDN_EXTERN'] = 'Extern (CDN)';
$TEXT['RENAME_FILES_ON_UPLOAD'] = 'Forbidden Filetypes';
$TEXT['MEDIA_RESIZE'] = 'max. Media Images Size';
$TEXT['MEDIA_WIDTH'] = 'px width';
$TEXT['MEDIA_HEIGHT'] = 'px height';
$TEXT['MEDIA_COMPRESS'] = 'JPEG compression';

$TEXT['EDITOR_ENABLE'] = 'Enable Editor';
$TEXT['EDITOR_DISABLE'] = 'Disable Editor';
$TEXT['PAGES_OLDSTYLE'] = 'Retro Format';
$TEXT['PAGES_NEWSTYLE'] = 'New Format';

$HEADING['LOGIN_SETTINGS'] = 'Frontend Login, Signup, General Data Protection Regulation';

$TEXT['DSGVO'] = 'Data Protection Directive';
$TEXT['DSGVO_LINK'] = 'Data Protection Url';
$TEXT['DSGVO_DISABLED'] = 'Reset Data Protection Directive';
$TEXT['TOGGLE'] = 'Enable or disable Data Protection Url';
$TEXT['WBMAILER_DEBUG'] = 'Mail Debug';
$TEXT['WBMAILER_SENDMAIL'] = 'Sendmail';
$TEXT['WBMAILER_NOTICE'] = "In order for WebsiteBaker to send notifications via email, you need a valid email account. Whether it is a mailbox on your own server or any other provider does not matter. The only condition is that the mailbox/account allows sending mail via SMTP. The setting of the access data is similar to other email clients like Outlook, Thunderbird, Evolution, etc.. This mailbox is basically used for any email dispatch from WebsiteBaker incl. all additional modules";

$TEXT['WBMAILER_DEFAULT_SENDER_MAIL'] = 'Address of the default recipient';
$TEXT['WBMAILER_DEFAULT_SENDER_NAME'] = 'Name of the default recipient';
$TEXT['WBMAILER_DEFAULT_SETTINGS_NOTICE'] = 'Messages by e-mail';
$TEXT['WBMAILER_FUNCTION'] = 'Sending method';
$TEXT['WBMAILER_PHP'] = 'PHP MAIL';
$TEXT['WBMAILER_SMTP'] = 'SMTP';
$TEXT['WBMAILER_SMTP_AUTH'] = 'SMTP authentication';
$TEXT['WBMAILER_SMTP_HOST'] = 'Url of the mail server';
$TEXT['WBMAILER_SMTP_SECURE'] = 'SMTP encryption';
$TEXT['WBMAILER_ENABLE_SSL_SECURE'] = 'Turn on certificate check';
$TEXT['WBMAILER_DISABLE_SSL_SECURE'] = 'Turn off certificate checking';

$INFO = [
    'DSGVO' => 'Multiple selection, several entries can be selected. Select a privacy notice for each language!',
    'ENABLED_SIGNUP' => 'Activate for registration form',
    'LABEL_ENABLED_SIGNUP' => 'GDPR: Activate General Data Protection on the registration form',
    'ENABLED_LOGINFORM' => 'Activate for login form',
    'LABEL_ENABLED_LOGINFORM' => 'GDPR: Activate  General Data Protection on the login form',
    'ENABLED_LOSTPASSWORD' => 'Activate the Forgot Password form',
    'LABEL_ENABLED_LOSTPASSWORD' => 'GDPR: Activate  General Data Protection on the password reset form.',
    'SMTP_DEBUG_OFF' => "<code>SMTP::DEBUG_OFF</code> (0): Normal production setting; no debug output.",
    'SMTP_DEBUG_CLIENT' => "<code>SMTP::DEBUG_CLIENT</code> (1): show client -&gt; server messages only. Don't use this - it's very unlikely to tell you anything useful.",
    'SMTP_DEBUG_SERVER' => "<code>SMTP::DEBUG_SERVER</code> (2): show client -&gt; server and server -&gt; client messages - this is usually the setting you want",
    'SMTP_DEBUG_CONNECTION' => "<code>SMTP::DEBUG_CONNECTION</code> (3): As 2, but also show details about the initial connection; only use this if you're having trouble connecting (e.g. connection timing out)",
    'SMTP_DEBUG_LOWLEVEL' => "<code>SMTP::DEBUG_LOWLEVEL</code> (4): As 3, but also shows detailed low-level traffic. Only really useful for analyzing protocol-level bugs, very verbose, probably not what you need.",
    'SMTP_GMAIL' => 'Please make sure that sending via SMTP from this server is not classified as SPAM by your e-mail provider!',
    'SHOW_DATETIME' => 'Date display start page',
];
