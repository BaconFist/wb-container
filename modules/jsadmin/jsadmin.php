<?php
/**
 *
 * @category        modules
 * @package         JsAdmin
 * @author          WebsiteBaker Project, modified by Swen Uth for Website Baker 2.7
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http:/websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: jsadmin.php 288 2019-03-26 15:14:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/jsadmin/jsadmin.php $
 * @lastmodified    $Date: 2019-03-26 16:14:03 +0100 (Di, 26. Mrz 2019) $
 *
*/

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;}
/* -------------------------------------------------------- */
    function get_setting($name, $default = '') {
        global $database;
        $retVal = $default;
        $sql  = 'SELECT `value` FROM `'.TABLE_PREFIX.'mod_jsadmin` '
              . 'WHERE `name` = \''.$database->escapeString($name).'\'';
        $retVal = (int)filter_var($database->get_one($sql),FILTER_VALIDATE_BOOLEAN);
        return $retVal;
    }

    function save_setting($name, $value) {
        global $database;
        $bRetVal = false;
        $prev_value = get_setting ( $name, '' );
        $sSqlWhere = '';
    // no longer needed, is alreaded inserted need only update
    /*
        if($prev_value === false) {
            $sql  = 'INSERT INTO `'.TABLE_PREFIX.'mod_jsadmin` SET '
                 .  '`name`  = \''.$database->escapeString($name).'\', ';
        } else {
        }
    */
        $sSqlWhere = 'WHERE `name` = \''.$database->escapeString($name).'\'';
        $sql   = 'UPDATE `'.TABLE_PREFIX.'mod_jsadmin` SET ';
  //    $sql  .=  '`name`  = \''.$database->escapeString($name).'\', '
        $sql  .= '`value`  = '.(int)$value.' '.$sSqlWhere;
        if ($database->query($sql)){
            $bRetVal = true;
        }
        return $bRetVal;
    }

// the follwing variables to use and check existing the YUI
    $WB_MAIN_RELATIVE_PATH="../..";
    $YUI_PATH = '/include/yui';
    $js_yui_min = "-debug";  // option for debug modus
    $js_yui_min = "-min";  // option for smaller code so faster
    $js_yui_scripts = [];
    $js_yui_scripts[] = $YUI_PATH.'/yahoo/yahoo'.$js_yui_min.'.js';
    $js_yui_scripts[] = $YUI_PATH.'/event/event'.$js_yui_min.'.js';
    $js_yui_scripts[] = $YUI_PATH.'/dom/dom'.$js_yui_min.'.js';
    $js_yui_scripts[] = $YUI_PATH.'/connection/connection'.$js_yui_min.'.js';
    $js_yui_scripts[] = $YUI_PATH.'/dragdrop/dragdrop'.$js_yui_min.'.js';
