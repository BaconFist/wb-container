<?php
/**
 *
 * @category        frontend
 * @package         account
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.2.2 and higher
 * @version         $Id: email.php 267 2019-03-21 16:44:22Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/email.php $
 * @lastmodified    $Date: 2019-03-21 17:44:22 +0100 (Do, 21. Mrz 2019) $
 *
 */

// Must include code to stop this file being access directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
    $sHeading = "";

    if (!\bin\SecureTokens::checkFTAN ()) {
        throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

// Get entered values
    $password = preg_replace('/[^\x20-\x7E]+$]/', '',$wb->StripCodeFromText($wb->get_post('current_password')));
    $email = $wb->StripCodeFromText($wb->get_post('email'));
// validate password
    $sql  = 'SELECT `user_id` FROM `'.TABLE_PREFIX.'users` '
          . 'WHERE `user_id` = '.$wb->getUserId().' AND `password` = \''.md5($password).'\'';
    $rowset = $database->query($sql);
// Validate values
    if ($rowset->numRows() == 0) {
        $error[] = $MESSAGE['PREFERENCES_CURRENT_PASSWORD_INCORRECT'];
    } else {
        if(!$wb->validate_email($email)) {
            $error[] = $MESSAGE['USERS_INVALID_EMAIL'];
        }else {
            $email = $wb->add_slashes($email);
// Update the database
            $sql  = 'UPDATE `'.TABLE_PREFIX.'users` '
                  . 'SET `email` = \''.$database->escapeString($email).'\' '
                  . 'WHERE `user_id` = \''.$wb->getUserId().'\'';
             $database->query($sql);
            if($database->is_error()) {
                $error[] = $database->get_error();
            } else {
                $aSuccess[] = $MESSAGE['PREFERENCES_EMAIL_UPDATED'];
                $_SESSION['EMAIL'] = $email;
            }
        }
    }
    $sHeading = sprintf('<%1$s>%2$s</%1$s> ','h3',$oTrans->HEADING_MY_EMAIL);
    $sHeading = ((sizeof($error) || sizeof($aSuccess)) ? $sHeading : '');
