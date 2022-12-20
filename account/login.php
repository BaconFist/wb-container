<?php
/**
 *
 * @category        frontend
 * @package         account
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 7.4 and higher
 * @version         $Id: login.php 267 2019-03-21 16:44:22Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/login.php $
 * @lastmodified    $Date: 2019-03-21 17:44:22 +0100 (Do, 21. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,Login,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,ParentList};

    try {
        if (!\defined('SYSTEM_RUN')) {
            $sConfigFile = (dirname((__DIR__))).'/config.php';
            if (is_readable($sConfigFile) === false){
                \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;
            }
            require $sConfigFile;
        }
        $sMessage = 'unkown error';

    //  Create new frontend object
        if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}

        $oReg     = WbAdaptor::getInstance();
        $database = $oReg->getDatabase();
        $oTrans   = $oReg->getTranslate();
        $oApp     = $oReg->getApplication();

        $action = ($oRequest->getParam('action') ?? 'show');
// get POST or GET requests, never both at once
        $aRequestVars  = $oApp->getRequestVars();
        //$sCallingScript = ($oReg->Request->getServerVar('HTTP_REFERER') ?? $oReg->AppUrl);

        $_SESSION['display_form'] = ($_SESSION['display_form'] ?? false);
        $oTrans->enableAddon('\\account');

        $tmpPageId = $oRequest->getParam('page_id',FILTER_VALIDATE_INT);
        $page_id = ($_SESSION['PAGE_ID'] ?? $tmpPageId);

        $tmpRedirect = $oRequest->getParam('redirect',\FILTER_VALIDATE_URL, ["default"=>$oReg->AppUrl]);
        $redirect  = ((isset($_SERVER['HTTP_REFERER']) && empty($tmpRedirect)) ?  $_SERVER['HTTP_REFERER'] : $tmpRedirect);
        $_SESSION['HTTP_REFERER'] = str_replace($oReg->AppUrl,'',$redirect);
        $page_id = ($_SESSION['PAGE_ID'] ?? 0);

//        $_SESSION['display_form'] = (!isset($_SESSION['display_form']) ? true : $_SESSION['display_form']);
        if ((isset($page_id) && $page_id == 0) || \is_null($page_id)){
            $page_id = $wb->getDefaultPageId();
            $_SESSION['PAGE_ID'] = $page_id;
            $_SESSION['display_form'] = true;
        }

        if ((isset($page_id) && $page_id == 0) || \is_null($page_id)){
            $sMessage = sprintf('Invalid page_id %d',$page_id);
            throw new \Exception ($sMessage);
        }

    //  Make sure the login is enabled
        if (!FRONTEND_LOGIN) {
            if (INTRO_PAGE) {
                $oApp->send_header($oReg->AppUrl.'index.php');
                exit(0);
            } else {
                $oApp->send_header($oReg->AppUrl.'index.php');
                exit(0);
                if ($oApp->getUserId() && $oApp->ami_group_member('1')) {
                } else {
                    $oApp->print_missing_frontend_login();
                }
                exit(0);
            }
        }
//      Required page details
        $page_description = '';
        $page_keywords = '';
        define('PAGE_ID', $page_id);
        define('ROOT_PARENT', 0);
        define('PARENT', 0);
        define('LEVEL', 0);
        define('PAGE_TITLE', $TEXT['PLEASE_LOGIN']);
        define('MENU_TITLE', $TEXT['PLEASE_LOGIN']);
        define('VISIBILITY', 'public');
//      Set the page content include file
        define('PAGE_CONTENT', $oReg->AppPath.'account/login_form.php');
    //  Create new login app
        $tmpRedirect = $oRequest->getParam('redirect',\FILTER_VALIDATE_URL);
        $redirect  = ((isset($_SERVER['HTTP_REFERER']) && empty($tmpRedirect)) ?  $_SERVER['HTTP_REFERER'] : $tmpRedirect);
        //
        $_SESSION['HTTP_REFERER'] = str_replace($oReg->AppUrl,'',$redirect);
        $loginUrl  = $oReg->AppUrl.'account/login.php';
        //$loginUrl .= (!empty($redirect) ? '?redirect=' .$_SESSION['HTTP_REFERER'] : '');
        $ThemeUrl   = $oReg->AppUrl.str_replace($oReg->AppPath, '', $oApp->correct_theme_source('warning.html.php'));
        //$sTemplateFile =
    //  Setup template object, parse vars to it, then parse it
/*
        if (is_readable($oReg->AppPath.'templates/'.$oReg->Template.'templates/login_form.htt'))
        {
            $TemplateFile = 'templates/'.$oReg->Template.'templates/login_form.htt';
            $ThemePath = ($oApp->correct_theme_source($oReg->AppPath.$TemplateFile));
        }
*/
        if (is_readable($oReg->AppPath.'account/templates/login_form.htt'))
        {
            $TemplateFile = 'account/templates/login_form.htt';
        }
        $ThemePath = ($oApp->correct_theme_source('login_form.htt'));
        $aLoginConfig =[
                        "MAX_ATTEMPS" => "3",
                        "WARNING_URL" => $ThemeUrl,
                        "USERNAME_FIELDNAME" => 'username',
                        "PASSWORD_FIELDNAME" => 'password',
                        "REMEMBER_ME_OPTION" => SMART_LOGIN,
                        "MIN_USERNAME_LEN" => "2",
                        "MIN_PASSWORD_LEN" => "2",
                        "MAX_USERNAME_LEN" => "30",
                        "MAX_PASSWORD_LEN" => "30",
                        "LOGIN_URL" => $loginUrl,
                        "DEFAULT_URL" => $oReg->AppUrl."index.php",
                        "TEMPLATE_DIR" => $ThemePath,
                        "TEMPLATE_FILE" => 'login.htt',
                        "FRONTEND" => true,
                        "FORGOTTEN_DETAILS_APP" => $oReg->AppUrl."account/forgot.php",
                        "USERS_TABLE" => $oReg->TablePrefix."users",
                        "GROUPS_TABLE" => $oReg->TablePrefix."groups",
                        "PAGE_ID" => $page_id,
                        "URL" => $redirect,
                        "REDIRECT" => $redirect,
                        "REDIRECT_URL" => $redirect,
                        "REQUEST_VARS" => $aRequestVars,
                ];
        $thisApp = new Login($aLoginConfig);
//  Set extra outsider var
        $globals[] = 'thisApp';

//  Include the index (wrapper) file
        require(WB_PATH.'/index.php');
    } catch (\Exception $ex) {
        $sErrMsg = PreCheck::xnl2br(\sprintf('[%04d] %s', $ex->getLine(), $ex->getMessage()));
        $oApp->ShowMaintainScreen('error',$sErrMsg);
        exit;
    }
