<?php
/**
 *
 * @category        frontend
 * @package         search
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2019, Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.6 and higher
 * @version         $Id: index.php 278 2019-03-22 00:42:42Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/search/index.php $
 * @lastmodified    $Date: 2019-03-22 01:42:42 +0100 (Fr, 22. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use bin\requester\HttpRequester;

//  Include the config file
    try {
        if (!\defined('SYSTEM_RUN')) {
            $sConfigFile = (dirname((__DIR__))).'/config.php';
            if (is_readable($sConfigFile) === false){
                \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;
//                $sMessage = sprintf('Missing config file');
//                throw new \Exception ($sMessage);
            }
            require $sConfigFile;
        }
        $sMessage = 'unkown error';
    //  Create new frontend object
        if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
        $redirect = $oRequest->getParam('redirect',FILTER_VALIDATE_URL);
        $redirect  = ((isset($_SERVER['HTTP_REFERER']) && empty($redirect)) ?  $_SERVER['HTTP_REFERER'] : $redirect);
        $_SESSION['HTTP_REFERER'] = str_replace(WB_URL,'',$redirect);
    //  Required page details
        $page_id = $oRequest->getParam('page_id',FILTER_VALIDATE_INT);
        $page_id = $wb->getDefaultPageId();
        $page_id = isset($_SESSION['PAGE_ID']) ? $_SESSION['PAGE_ID'] : $page_id;
        $page_description = '';
        $page_keywords = '';
        define('PAGE_ID', (int)$page_id);
        define('ROOT_PARENT', 0);
        define('PARENT', 0);
        define('LEVEL', 0);
        define('PAGE_TITLE', $TEXT['SEARCH']);
        define('MENU_TITLE', $TEXT['SEARCH']);
        define('MODULE', '');
        define('VISIBILITY', 'public');
        define('PAGE_CONTENT', 'search.php');
    //  Find out what the search template is
/*
        $query_template = $database->query("SELECT `value` FROM `".TABLE_PREFIX."search` WHERE `name` = 'template'");
        $fetch_template = $query_template->fetchRow(MYSQLI_ASSOC);
        $template = ($fetch_template['value'] ?: DEFAULT_TEMPLATE);
        if ($template != '') {
//            define('TEMPLATE', $template);
        }
        unset($template);
*/
    //  Get the referrer page ID if it exists
        if (isset($_REQUEST['referrer']) && is_numeric($_REQUEST['referrer']) && intval($_REQUEST['referrer']) > 0) {
            define('REFERRER_ID', intval($_REQUEST['referrer']));
        } else {
            define('REFERRER_ID', 0);
        }
    //  Include index (wrapper) file
        require(WB_PATH.'/index.php');
    } catch (\Exception $ex) {
        $sErrMsg = Precheck::xnl2br(\sprintf('[%04d] %s', $ex->getLine(), $ex->getMessage()));
        $wb->ShowMaintainScreen('error',$sErrMsg);
        exit;
    }

