<?php
/**
 *
 * @category        frontend
 * @package         account
 * @author          WebsiteBaker Project
 * @copyright       2009-2012, Website Baker Org. e.V.
 * @link            https://www.websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.6 and higher
 * @version         $Id: password.php 267 2019-03-21 16:44:22Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/password.php $
 * @lastmodified    $Date: 2019-03-21 17:44:22 +0100 (Do, 21. Mrz 2019) $
 *
 */

// Must include code to stop this file being access directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

    if (!\bin\SecureTokens::checkFTAN ()) {
        throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

// Get entered values
    $iMinPassLength = 6;
    $sCurrentPassword = preg_replace('/[^\x20-\x7E]+$]/', '',$wb->StripCodeFromText($wb->get_post('current_password')));
    $sCurrentPassword = (is_null($sCurrentPassword) ? '' : $sCurrentPassword);
    $sNewPassword = preg_replace('/[^\x20-\x7E]+$]/', '',$wb->StripCodeFromText($wb->get_post('new_password_1')));
    $sNewPassword = is_null($sNewPassword) ? '' : $sNewPassword;
    $sNewPasswordRetyped = preg_replace('/[^\x20-\x7E]+$]/', '',$wb->StripCodeFromText($wb->get_post('new_password_2')));
    $sNewPasswordRetyped= is_null($sNewPasswordRetyped) ? '' : $sNewPasswordRetyped;
// Check existing password
    $sql  = 'SELECT `password` ';
    $sql .= 'FROM `'.TABLE_PREFIX.'users` ';
    $sql .= 'WHERE `user_id` = '.$wb->getUserId();
// Validate values
    if (md5($sCurrentPassword) != $database->get_one($sql)) {
        $error[] = $MESSAGE['PREFERENCES_CURRENT_PASSWORD_INCORRECT'];
    }else {
        if(strlen($sNewPassword) < $iMinPassLength) {
            $error[] = $MESSAGE['USERS_PASSWORD_TOO_SHORT'];
        }else {
            if($sNewPassword != $sNewPasswordRetyped) {
                $error[] = $MESSAGE['USERS_PASSWORD_MISMATCH'];
            }else {
                $pattern = '/[^'.$wb->password_chars.']/';
                if (preg_match($pattern, $sNewPassword)) {
                    $error[] = $MESSAGE['PREFERENCES_INVALID_CHARS'];
                }else {
// generate new password hash
                    $sPwHashNew = md5($sNewPassword);
// Update the database
                    $sql  = 'UPDATE `'.TABLE_PREFIX.'users` '
                          . 'SET `password`=\''.$database->escapeString($sPwHashNew).'\' '
                          . 'WHERE `user_id`='.$wb->getUserId();
                    if ($database->query($sql)) {
                        $aSuccess[] = $MESSAGE['PREFERENCES_PASSWORD_CHANGED'];
                    }else {
                        $error[] = $database->get_error();
                    }
                }
            }
        }
    }
    $sHeading =((sizeof($error) || sizeof($aSuccess)) ? '<h3>'.$oTrans->HEADING_MY_PASSWORD.'</h3>' : '');
