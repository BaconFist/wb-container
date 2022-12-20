<?php
/**
 *
 * @category        admin
 * @package         logout
 * @author          Ryan Djurovich, WebsiteBaker Project
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.3
 * @requirements    PHP 7.2 and higher
 * @version         $Id: index.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/logout/index.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 */

use bin\{WbAdaptor,Login,frontend,SecureTokens,Sanitize};

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName   = basename($sModulesPath);
    $sAddonName    = basename($sModulesPath);
    $sAddonRel     = '/'.$sModuleName.'/'.$sAddonPath;
    // \basename(__DIR__).'/'.\basename(__FILE__);
    $sPattern = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment if you have to load config.php
    if (! defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {
        require($sAppPath.'config.php');
    }
    /* else {
        \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;
    }*/

    $oReg     = Wbadaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();
//    $oApp     = $oReg->getApplication();

    $sLoginUrl    = ($oReg->AcpUrl.'login/index.php');
    $sFrontendUrl = ($oReg->AppUrl.'index.php');
//    $sRedirectUrl = ($_SESSION['HTTP_REFERER'] ?? $oReg->AppUrl);
    $sRedirectUrl = ($oRequest->issetParam('frontend') ? $sFrontendUrl : $sLoginUrl);
//        \trigger_error(\sprintf('%s',$sRedirectUrl),E_USER_NOTICE);
// delete remember key of current user from database
    if (isset($_SESSION['USER_ID']) && isset($database)) {
        $table = TABLE_PREFIX . 'users';
        $sql = "UPDATE `$table` SET `remember_key` = '' WHERE `user_id` = '" . (int) $_SESSION['USER_ID'] . "'";
        $database->query($sql);
    }

// delete remember key cookie if set
    if (isset($_COOKIE['REMEMBER_KEY'])) {
        setcookie('REMEMBER_KEY', '', time() - 3600, '/');
    }

    $oldId = $oRequest->getParam('old_id');
    $newId = session_name();
/* */
    // delete most critical session variables manually
    $_SESSION['USER_ID'] = null;
    $_SESSION['GROUP_ID'] = null;
    $_SESSION['GROUPS_ID'] = null;
    $_SESSION['USERNAME'] = null;
    $_SESSION['PAGE_PERMISSIONS'] = null;
    $_SESSION['SYSTEM_PERMISSIONS'] = null;
// overwrite session array
    $_SESSION = [];

    // delete the session itself
    if (session_status() === \PHP_SESSION_ACTIVE ) {
        session_unset();
        session_destroy();
    }

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        if (isset($_COOKIE[$oldId])) {
//            setcookie($oldId, '', time() - 42000, '/');
            setcookie(
              $oldId
              , ''
              , time() - 42000
              , $params[ "path"     ]
              , $params[ "domain"   ]
              , $params[ "secure"   ]
              , $params[ "httponly" ]
            );
        }
//      \trigger_error(\sprintf('$oldId = %s, $newId = %s',$oldId,$newId),E_USER_NOTICE);
//      delete session cookie if set
      if (isset($_COOKIE[$newId])) {
  //        setcookie($newId, '', time() - 42000, '/');
          setcookie(
            $newId
            , ''
            , time() - 42000
            , $params[ "path"     ]
            , $params[ "domain"   ]
            , $params[ "secure"   ]
            , $params[ "httponly" ]
          );
      }
    }

// redirect to backend/frontend
    die(header('Location: '.$sRedirectUrl));
