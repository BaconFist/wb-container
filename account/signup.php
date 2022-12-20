<?php
/**
 *
 * @category        frontend
 * @package         account
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: signup.php 267 2019-03-21 16:44:22Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/signup.php $
 * @lastmodified    $Date: 2019-03-21 17:44:22 +0100 (Do, 21. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,Login,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

if (!defined('SYSTEM_RUN') ){ require(dirname(__DIR__).'/config.php'); }

    if (!(int)FRONTEND_SIGNUP || (int)(isset($_SESSION['USER_ID']) ? $_SESSION['USER_ID'] : 0)) {
        if (INTRO_PAGE) {
            $no_intro = true;
        }
        include dirname(__DIR__).'/index.php';
    }
    $oReg = WbAdaptor::getInstance();
    $aRequestVars = [];
    $aVars = $oReg->Request->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oReg->Request->getParam($sName);
    }
/**/
    $aSession = [
      'display_form' => ($_SESSION['display_form'] ?? 0),
      'PAGE_ID' => ($_SESSION['PAGE_ID'] ?? 0),
      'HTTP_REFERER' => ($_SESSION['HTTP_REFERER'] ?? WB_URL),
      'submitted_when' => ($_SESSION['submitted_when'] ?? time()),
      'ENABLED_ASP' => ENABLED_ASP,
      'POST' =>$aRequestVars,
    ];

    if (ENABLED_ASP && isset($aRequestVars['username']) && ( // form faked? Check the honeypot-fields.
        (!isset($aRequestVars['submitted_when']) || !isset($_SESSION['submitted_when'])) ||
        ($aRequestVars['submitted_when'] != $_SESSION['submitted_when']) ||
        (!isset($aRequestVars['email-address']) || ($aRequestVars['email-address'] ?? '')) ||
        (!isset($aRequestVars['name']) ||($aRequestVars['name'] ?? '')) ||
        (!isset($aRequestVars['full_name']) || ($aRequestVars['full_name']) ?? '')
    )) {
/* */
        \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
        <html><head><title>404 Not Found</title></head><body><h1>Not Found</h1>
        <p>The You do not have permission to view the requested URL '.$oRequest->getServerVar('SCRIPT_NAME').'
        .</p><hr>'.$_SERVER['SERVER_SIGNATURE'].'</body></html>';
        \flush(); exit;

    }

// Load the language file
    if (!file_exists(WB_PATH.'/languages/'.DEFAULT_LANGUAGE.'.php')) {
        exit('Error loading language file '.DEFAULT_LANGUAGE.', please check configuration');
    } else {
        require_once(WB_PATH.'/languages/'.DEFAULT_LANGUAGE.'.php');
        $load_language = false;
    }

    $_SESSION['display_form'] = ($_SESSION['display_form'] ?? false);
    $page_id = $oRequest->getParam('page_id',FILTER_VALIDATE_INT);
    $page_id = (isset($_SESSION['PAGE_ID']) && ($_SESSION['PAGE_ID'] > 0) ? $_SESSION['PAGE_ID'] : $page_id);
    $action = $oRequest->getParam('action',FILTER_SANITIZE_FULL_SPECIAL_CHARS);

//  Required page details
    $page_description = '';
    $page_keywords = '';
    define('PAGE_ID', $page_id);
    define('ROOT_PARENT', 0);
    define('PARENT', 0);
    define('LEVEL', 0);
    define('PAGE_TITLE', $TEXT['SIGNUP']);
    define('MENU_TITLE', $TEXT['SIGNUP']);
    define('MODULE', '');
    define('VISIBILITY', 'public');

//  Set the page content include file
    define('PAGE_CONTENT', WB_PATH.'/account/signup_form.php');

//  Set auto authentication to false
    $auto_auth = false;
//  Include the index (wrapper) file
    require(WB_PATH.'/index.php');
