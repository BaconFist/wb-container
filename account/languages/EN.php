<?php
/**
 *
 * @category        backend
 * @package         language
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.6.0 and higher
 * @version         $Id: EN.php 267 2019-03-21 16:44:22Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/languages/EN.php $
 * @lastmodified    $Date: 2019-03-21 17:44:22 +0100 (Do, 21. Mrz 2019) $ rn
 *
 */
 /* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
$MESSAGE['SIGNUP2_ADMIN_INFO'] = "\r\nA new user was registered.\r\n\r\nLogin Name: {LOGIN_NAME}\r\n{DISPLAY_NAME}: {LOGIN_ID}\r\nE-Mail: {LOGIN_EMAIL}\r\nRegistration date: {SIGNUP_DATE}\r\n----------------------------------------\r\nThis message was automatically generated!\r\n\r\n";
$MESSAGE['SIGNUP2_BODY_LOGIN_FORGOT'] = "\r\nHello {LOGIN_DISPLAY_NAME},\r\n\r\nThis mail was sent because the 'forgot password' function has been applied to your account.\r\n\r\nYour new '{LOGIN_WEBSITE_TITLE}' login details are:\r\n\r\nLogin name: xxxxx\r\nPassword: {LOGIN_PASSWORD}\r\n\r\nYour password has been reset to the one above.\r\nThis means that your old password will no longer work!\r\nIf you have any questions or problems concerning your new login data\r\nyou should contact the website team or the admin of '{LOGIN_WEBSITE_TITLE}'.\r\nPlease remember to clean you browser cache before using the new one to avoid unexpected fails.\r\n\r\nRegards\r\n------------------------------------\r\nThis message was automatically generated\r\n\r\n";
$MESSAGE['SIGNUP2_BODY_LOGIN_INFO'] = "\r\nHello {LOGIN_DISPLAY_NAME},\r\n\r\nWelcome to '{LOGIN_WEBSITE_TITLE}'.\r\n\r\nYour '{LOGIN_WEBSITE_TITLE}' login details are:\r\nLogin name: xxxxx\r\nPassword: {LOGIN_PASSWORD}\r\n\r\nRegards\r\n\r\nPlease:\r\nif you have received this message in error, please delete it immediately!\r\n-------------------------------------\r\nThis message was automatically generated!\r\n";
$MESSAGE['SIGNUP2_NEW_USER'] = "A new user for {WB_URL} has been registered";
$MESSAGE['SIGNUP2_SUBJECT_LOGIN_INFO'] = 'Your login details...';
$MESSAGE['SIGNUP2_SUBJECT_NEW_USER'] = 'Thanks for registering';
$MESSAGE['SIGNUP_NO_EMAIL'] = 'You must enter an email address';
$MESSAGE['SIGNUP2_SUBJECT_LOGIN_INFO'] = 'Your login details for {{WB_URL}}...';
$MESSAGE['SIGNUP_NO_EMAIL'] = 'You must enter an email address';
$MESSAGE['MOD_FORM_INCORRECT_CAPTCHA'] = 'The verification number (also known as Captcha) that you entered is incorrect. If you are having problems reading the Captcha, please email: <a href="mailto:{SERVER_EMAIL}">Webmaster</a>';
$MESSAGE['FORGOT_PASS_ALREADY_RESET'] = 'The Password can only be reset once per hour';
$MESSAGE['FORGOT_PASS_CANNOT_EMAIL'] = 'Unable to email password, please contact system administrator';
$MESSAGE['FORGOT_PASS_EMAIL_NOT_FOUND'] = 'The email that you entered cannot be found in the database';
$MESSAGE['FORGOT_PASS_NO_DATA'] = 'Please enter your email address below';
$MESSAGE['FORGOT_PASS_PASSWORD_RESET'] = 'Your password has been sent to your email address';
$MESSAGE['USERS_DISPLAYNAME_TAKEN'] = 'The display name you entered is already taken';
$MESSAGE['LOGIN_BOTH_BLANK'] = 'Please enter your login name and password';
$MESSAGE['INCORRECT_CAPTCHA'] = 'The verification number (also known as Captcha) that you entered is incorrect. If you are having problems reading the Captcha, please email: <a href="mailto:{SERVER_EMAIL}">Webmaster</a>';
$MESSAGE['LOGIN_FAILED'] = 'UPS...Login failed!';
$MESSAGE['USERS_NAME_INVALID_CHARS'] = 'Empty or invalid characters were found for the login name';
$MESSAGE['USERS_DISPLAYNAME_INVALID_CHARS'] = 'Empty or invalid characters were found for the display name';

$MESSAGE['DSGVO'] = 'I confirm, that i have read and agree to the <a href="%s" target="_blank" rel="noopener">Data Protection Directive</a>  by submitting the form';
$MESSAGE['DSGVO_ERROR'] = 'Missing confirmation and agreement to the Data Protection Directive';

$MESSAGE['DSGVO_ENABLED'] = 'Consent to the Privacy Policy %s';
$MESSAGE['DSGVO_DIABLED'] = 'Lack of consent to the Privacy Policye %s';
$MESSAGE['DSGVO_NOT_INUSE'] = 'Privacy policy consent is disabled %s';

$TEXT['FORGOT_DETAILS'] = 'Forgot Login Details?';
$TEXT['RESET_INPUTS'] = 'Reset Inputs';
$TEXT['PAGE_RELOAD'] = 'Reload page';
