<?php
/**
 *
 * @category        frontend
 * @package         account
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2022, Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 7.4 and higher
 * @version         $Id: forgot.php 267 2019-03-21 16:44:22Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/forgot.php $
 * @lastmodified    $Date: 2019-03-21 17:44:22 +0100 (Do, 21. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,Login,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};


if (!defined( 'SYSTEM_RUN')){require(dirname(__DIR__).'/config.php');}
//  Create new frontend object
    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
    $oReg     = WbAdaptor::getInstance();
    $database = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
    $oApp     = $oReg->getApplication();
// get post requests
    $aRequestVars  = $oApp->getRequestVars();
    $page_id = $oRequest->getParam('page_id',FILTER_VALIDATE_INT);
    $page_id = (isset($_SESSION['PAGE_ID']) && ($_SESSION['PAGE_ID'] > 0) ? $_SESSION['PAGE_ID'] : $page_id);

//    $page_id = ($page_id ?? $wb->getLangPageId(LANGUAGE));
    $_SESSION['display_form'] = (!isset($_SESSION['display_form']) ? true : $_SESSION['display_form']);
/*
print '<pre  class="mod-pre" style="margin-left:30px;">function <span>'.__FUNCTION__.'( '.''.' );</span>  filename: <span>'.basename(__FILE__).'</span>  line: '.__LINE__.' -> '."\n";
print_r( [$page_id,$sCallingScript,$redirect,$_SESSION['HTTP_REFERER']] ); print '</pre>'; \flush (); //  sleep(10); die();
*/
    if (!FRONTEND_LOGIN) {
    //    header('Location: '.WB_URL.'/index.php');
        require($oReg->AppPath.'index.php');
        exit(0);
    }

//  Required page details
    $page_description = '';
    $page_keywords = '';
    define('PAGE_ID', $page_id);
    define('ROOT_PARENT', 0);
    define('PARENT', 0);
    define('LEVEL', 0);
    define('PAGE_TITLE', $MENU['FORGOT']);
    define('MENU_TITLE', $MENU['FORGOT']);
    define('VISIBILITY', 'public');
//  Set the page content include file
    define('PAGE_CONTENT', $oReg->AppPath.'account/forgot_form.php');
//  Set auto authentication to false
    $auto_auth = false;
//  Include the index (wrapper) file
    require($oReg->AppPath.'index.php');
