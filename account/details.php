<?php
/**
 *
 * @category        frontend
 * @package         account
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: details.php 352 2019-05-13 12:34:35Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/details.php $
 * @lastmodified    $Date: 2019-05-13 14:34:35 +0200 (Mo, 13. Mai 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

// Must include code to stop this file being access directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

    $oReg     = WbAdaptor::getInstance();
    $oDB      = $oReg->getDatabase();
    $database = $oDB;
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();


    if (!SecureTokens::checkFTAN ()) {
        throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }
//    $oRequest = \bin\requester\HttpRequester::getInstance();

// sanitize entered values
    $display_name = ($oRequest->issetParam('display_name'))
                  ? Sanitize::StripFromText($oRequest->getParam('display_name'), Sanitize::REMOVE_DEFAULT)
                  : $wb->get_display_name();

    $display_name = \filter_var(
        $display_name,
        \FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => '/^[\w\d\x{0020}\x{002E}\x{0040}-\x{007E}\x{86c3}-\x{86c3}]+$/sui', 'default' => '']]
    );

    $language = strtoupper($oRequest->getParam(
        'language',
        \FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => '/^[a-z]{2}$/si', 'default' => 'EN']]
    ));
    $user_time = true;
    $timezone = $oRequest->getParam(
        'timezone',
        \FILTER_VALIDATE_INT,
        ['options' => ['min_range' => -12, 'max_range' => 13, 'default' => (DEFAULT_TIMEZONE/3600)]]
    ) * 3600;

// date_format must be a key from /interface/date_formats
    include( ADMIN_PATH.'/interface/date_formats.php' );
    $date_format = $oRequest->getParam('date_format');
    $date_format = ($date_format ?? DEFAULT_DATE_FORMAT);
    $date_format = (array_key_exists(str_replace(' ', '|', $date_format), $DATE_FORMATS)) ? $date_format : DEFAULT_DATE_FORMAT;
    $date_format = (($date_format !== 'system_default') ? $date_format : DEFAULT_DATE_FORMAT);
    unset($DATE_FORMATS);

// time_format must be a key from /interface/time_formats
    include( ADMIN_PATH.'/interface/time_formats.php' );
    $time_format = $oRequest->getParam('time_format');
    $time_format = ($time_format ?? DEFAULT_TIME_FORMAT);
    $time_format = (array_key_exists(str_replace(' ', '|', $time_format), $TIME_FORMATS)) ? $time_format : DEFAULT_TIME_FORMAT;
    $time_format = (($time_format !== 'system_default') ? $time_format : DEFAULT_TIME_FORMAT);
    unset($TIME_FORMATS);

    // check that display_name is unique in whoole system (prevents from User-faking)
    $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'users` '
          . 'WHERE `user_id` <> '.(int) $wb->getUserId().' '
          .   'AND `display_name` LIKE \''.$database->escapeString($display_name).'\'';
    if ($database->get_one($sql)) {
        $error[] = $oTrans->MESSAGE_USERS_DISPLAYNAME_TAKEN;
    } elseif(empty($display_name)){
        $error[] = $oTrans->MESSAGE_MEDIA_CANNOT_RENAME.' ('.$oTrans->TEXT_DISPLAY_NAME.')';
    } else {
        // Update the database
        $sql = 'UPDATE `'.TABLE_PREFIX.'users` '
             . 'SET `display_name` = \''.$database->escapeString($display_name).'\', '
             .     '`language` = \''.$database->escapeString($language).'\', '
             .     '`timezone` = '.(int)$timezone.', '
             .     '`date_format` = \''.$database->escapeString($date_format).'\', '
             .     '`time_format` = \''.$database->escapeString($time_format).'\' '
             . 'WHERE `user_id` = '.(int) $wb->getUserId();
        if (!$database->query($sql)) {
            $error[] = $database->get_error();
        } else {
            $aSuccess[] = $oTrans->MESSAGE_PREFERENCES_DETAILS_SAVED;
            $_SESSION['DISPLAY_NAME'] = $display_name;
            $_SESSION['LANGUAGE']     = $language;
            $_SESSION['TIMEZONE']     = $timezone;
            $_SESSION['DATE_FORMAT']  = $date_format;
            $_SESSION['TIME_FORMAT']  = $time_format;
            // Update date format
              if($date_format !== '') {
                  $_SESSION['DATE_FORMAT'] = $date_format;
                  if(isset($_SESSION['USE_DEFAULT_DATE_FORMAT'])) { unset($_SESSION['USE_DEFAULT_DATE_FORMAT']); }
              } else {
                  $_SESSION['USE_DEFAULT_DATE_FORMAT'] = true;
                  if(isset($_SESSION['DATE_FORMAT'])) { unset($_SESSION['DATE_FORMAT']); }
              }
            // Update time format
                if($time_format !== '') {
                    $_SESSION['TIME_FORMAT'] = $time_format;
                    if(isset($_SESSION['USE_DEFAULT_TIME_FORMAT'])) { unset($_SESSION['USE_DEFAULT_TIME_FORMAT']); }
                } else {
                    $_SESSION['USE_DEFAULT_TIME_FORMAT'] = true;
                    if(isset($_SESSION['TIME_FORMAT'])) { unset($_SESSION['TIME_FORMAT']); }
                }
        }
    }
    $sHeading = sprintf('<%1$s>%2$s</%1$s> ','h3',$oTrans->HEADING_MY_SETTINGS);
    $sHeading = ((sizeof($error) || sizeof($aSuccess)) ? $sHeading : '');
