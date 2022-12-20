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
 * @version         $Id: logout.php 267 2019-03-21 16:44:22Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/logout.php $
 * @lastmodified    $Date: 2019-03-21 17:44:22 +0100 (Do, 21. Mrz 2019) $
 *
 */


use bin\{WbAdaptor,Login,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};


if (!\defined('SYSTEM_RUN')) {require( ((dirname((__DIR__)))).'/config.php');}

    if (isset($_COOKIE['REMEMBER_KEY'])) {
        setcookie('REMEMBER_KEY', '', time()-3600, '/');
    }
    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
    $oReg     = WbAdaptor::getInstance();
    $database = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
//    $wb       = $oReg->getApplication();
    $page_id      = $oRequest->getParam('page_id',FILTER_VALIDATE_INT);
    $page_id = (isset($_SESSION['PAGE_ID']) && ($_SESSION['PAGE_ID'] > 0) ? $_SESSION['PAGE_ID'] : $page_id);
    $redirect_url = ((isset($_SESSION['HTTP_REFERER']) && $_SESSION['HTTP_REFERER'] != '') ? $_SESSION['HTTP_REFERER'] : WB_URL );
    $redirect_url = (isset($redirect) && ($redirect!='') ? $redirect : $redirect_url);

    $_SESSION['USER_ID'] = null;
    $_SESSION['GROUP_ID'] = null;
    $_SESSION['GROUPS_ID'] = null;
    $_SESSION['USERNAME'] = null;
    $_SESSION['PAGE_PERMISSIONS'] = null;
    $_SESSION['SYSTEM_PERMISSIONS'] = null;
    $_SESSION = [];

    session_unset();
    unset($_COOKIE[session_name()]);
    session_destroy();

    if (!FRONTEND_LOGIN && INTRO_PAGE) {
        header('Location: '.WB_URL.'/index.php');
        exit;
    } else {
        $no_intro = true;
        require(WB_PATH.'/index.php');
    }

