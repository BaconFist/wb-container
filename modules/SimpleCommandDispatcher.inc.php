<?php
/*
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * SimpleCommandDispatcher.inc.php
 *
 * @category     Addons
 * @package      Addons_Dispatcher
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      3.0.1
 * @lastmodified $Date: 2018-11-14 19:31:50 +0100 (Mi, 14. Nov 2018) $
 * @since        File available since 17.12.2015
 * @description  xyz
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue,ParentList};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

    $sAddonPath = ($sAddonPath ?? \str_replace('\\','/',__DIR__).'/'.$sAddonName.'/');
    $bExcecuteCommand = ($bExcecuteCommand ?? true);
    $bExcecuteDefault = true;
    $bExcecuteDefault = ($bExcecuteDefault ?? (!is_readable(__DIR__.'/.setDebug')));
    $oReg     = WbAdaptor::getInstance();
    $oRequest = $oReg->getRequester();
    $oDb      = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
    $oApp     = ($oReg->getApplication() ?? null);
    $sDomain  = $oApp->getDirNamespace($sAddonPath);

/* may be not needed */
    if (empty($oApp->page)) {
        $oApp0 = ($GLOBALS['wb'] ?? null);
        $oApp1 = ((isset($GLOBALS['admin']) && $oApp0==null) ? $GLOBALS['admin'] : $oApp0);
        $oReg->setApplication($oApp1);
        $oApp  = ($oReg->getApplication());
    }
//echo nl2br(sprintf("<b>function/class/file %s [%d] with instance of %s</b>\n",(!empty(__CLASS__) ? __CLASS__ : basename(__FILE__)),__LINE__,get_class ($oApp)));
    $isAuth         = ($oApp->is_authenticated() ?? false);
    $bIsBackend     = $oReg->getApplication()->isBackend();
    $bIsFrontend    = $oReg->getApplication()->isFrontend();
    $sCallingScript = ($oRequest->getServerVar("SCRIPT_NAME") ?? null);

    $sAddonType = (!isset($sAddonType) ? 'module' : rtrim($sAddonType,'s/')).'s/';
    $aMsg       = [];
    $aTemplates = [];

    // set addon depending path / url
    $sAddonPath = $oReg->AppPath.$sAddonType.$sAddonName.'/';
    $sAddonRel  = $sAddonType.$sAddonName;
    $sAddonUrl  = $oReg->AppUrl.$sAddonType.$sAddonName.'/';
    $aRequestVars = [];
    // getter $_REQUESTS GET or POST
    $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
    $aVars = $oRequest->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oRequest->getParam($sName);
    }
    $module_dir    = \basename(\dirname($oReg->Request->getServerVar("SCRIPT_NAME")));

    $sAddonDefaultThemeRel    = 'themes/default/';
    $sNewThemeRel    = 'themes/'.((($oReg->Theme=='DefaultTheme') || !$bExcecuteDefault) ? 'default' : $oReg->Theme).'';
    $sAddonThemeRel  = $sNewThemeRel;
    $sAddonThemePath = $sAddonPath.$sAddonThemeRel;
    $sAddonThemeUrl  = $sAddonUrl.$sAddonThemeRel;
// DebugVars
    $aTemplates['themes']['NewTheme'] = $sNewThemeRel;
    $aTemplates['themes']['AddonTheme'] = $sAddonDefaultThemeRel;
/* ---------------------------------------------------------------------------- */
    $sAddonDefaultTemplateRel = 'templates/default';
    $sNewTemplateRel    = 'templates/'.((($oReg->Template=='DefaultTemplate') || !$bExcecuteDefault) ? 'default' : $oReg->Template).'/';
    $sAddonTemplateRel  = $sNewTemplateRel;
    $sAddonTemplatePath = $sAddonPath.$sNewTemplateRel; //'/templates/'.
    $sAddonTemplateUrl  = $sAddonUrl.$sNewTemplateRel;
// DebugVars
    $aTemplates['templates']['NewTemplate'] = $sNewTemplateRel;
    $aTemplates['templates']['AddonTemplate'] = $sAddonDefaultTemplateRel;
//echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] %s</div>\n",__LINE__,$sNewTemplateRel ));

    // define the theme to use -----------------------------------------------------------
    if (!\is_readable($sAddonPath.$sNewThemeRel) && $bExcecuteDefault) {
    // overload with the selected theme if accessible
        $aMsg['themes'] = PreCheck::createFillDir($sAddonThemePath,$sAddonPath.$sNewThemeRel);
    }

    if (\is_readable($sAddonPath.$sNewThemeRel)) {
    // first set fallback to system default theme
        $sAddonThemeRel  = $sNewThemeRel;
        $sAddonThemePath = $sAddonPath.$sNewThemeRel;
        $sAddonThemeUrl  = $sAddonUrl.$sNewThemeRel;
    }

    // define the template to use --------------------------------------------------------
    if (!\is_readable($sAddonPath.$sNewTemplateRel) && $bExcecuteDefault) {
    // overload with the selected theme if accessible
        $aMsg['templates'] = PreCheck::createFillDir($sAddonPath.$sAddonDefaultTemplateRel,$sAddonPath.$sNewTemplateRel);
    }
    // define the theme to use --------------------------------------------------------
    if (!\is_readable($sAddonPath.$sNewThemeRel) && $bExcecuteDefault) {
    // overload with the selected theme if accessible
        $aMsg['templates'] = PreCheck::createFillDir($sAddonPath.$sAddonDefaultThemeRel,$sAddonPath.$sNewThemeRel);
    }

    if (\is_readable($sAddonPath.$sNewThemeRel)) {
    // first set fallback to system default theme
        $sAddonThemeRel  = $sNewThemeRel;
        $sAddonThemePath = $sAddonPath.$sNewThemeRel;
        $sAddonThemeUrl  = $sAddonUrl.$sNewThemeRel;
    }
    // load addon depending language file ------------------------------------------------
        if (\is_readable($sAddonPath.'/languages/EN.php')) {
            // first load fallback to system default language (EN)
            include $sAddonPath.'/languages/EN.php';
        }
        if (\is_readable($sAddonPath.'/languages/'.$oReg->DefaultLanguage.'.php')) {
            // try loading language of global settings
            include $sAddonPath.'/languages/'.$oReg->DefaultLanguage.'.php';
        }
        if (\is_readable($sAddonPath.'/languages/'.$oReg->Language.'.php')) {
            // try loading language of user (backend) or page (frontend) defined settings
            include $sAddonPath.'/languages/'.$oReg->Language.'.php';
        }
        //$oTrans->enableAddon ($sDomain);// 'modules\\'.$sAddonName
        $sFolder =  '';
        // load addon Theme/Template depending language file ---------------------------------
        $sTmp = ($bIsBackend ? $sAddonThemePath : $sAddonTemplatePath).'/languages/';
        if (\is_readable($sTmp.'EN.php')) {
            // first load fallback to system default language (EN)
            include $sTmp.'EN.php';
            if (\is_readable($sTmp.$oReg->DefaultLanguage.'.php')) {
                // try loading language of global settings
                include $sTmp.$oReg->DefaultLanguage.'.php';
            }
            if (\is_readable($sTmp.$oReg->Language.'.php')) {
                // try loading language of user (backend) or page (frontend) defined settings
                include $sTmp.$oReg->Language.'.php';
            }
            // enable languages in themes/default/languages
           $sFolder = '/'.$sAddonThemeRel;
        } else {
        }
       $oTrans->enableAddon ($sDomain.$sFolder);
        ${'a'.$sAddonName.'Lang'} = $oTrans->getLangArray();

    // Simple Command Dispatcher ---------------------------------------------------------
     // Include the ordering class

    // sanitize command from compatibility file
    $sCommand = (isset($sCommand) ? ($sCommand) : ''); //  strtolower
    // sanitize/validate request var 'cmd'
    $sCmd = \preg_replace(
        '/[^a-z\/0-1]/siu',
        '',
//        (isset($_REQUEST['cmd']) ? \strtolower($_REQUEST['cmd']) : '')
        \strtolower($oRequest->getParam('cmd') ?? '')
    );
    // build valid sCommand string
    if (($sCommand && $sCmd)) {
        if (!\preg_match('/^'.$sCommand.'/si', $sCmd)) {
            // concate both arguments if needed
            $sCommand .= '/'.$sCmd;
//echo nl2br(sprintf("[%03d] %s \n",__LINE__,$sCommand));
        } else {
            $sCommand = $sCmd;
        }
        $sCmd = '';
    }
    $sCommand = \str_replace( // remove spaces and add prefix 'cmd'
        ' ', '',
        \ucfirst( // make first char of every word to uppercase
            \str_replace( // change '/' to space
                '/', ' ',
                \preg_replace( // change leading 'add/' to 'modify/'
                    '/^add\//s',
                    'modify/',
                    \trim(($sCommand ?: $sCmd), '/') // remove leading and trailing slashes
                )
            )
        )
    );
    $aDispatcher = [
          'AddonRel'           => $sAddonRel,
          'AddonPath'          => $sAddonPath,
          'AddonUrl'           => $sAddonUrl,
          'AddonThemeRel'      => $sAddonThemeRel,
          'AddonTemplateRel'   => $sAddonTemplateRel,
          'Requestvars'        => $aRequestVars,
          'cmd'                => $sCmd,
          'Command'            => $sCommand,
//          'Adaptor'            => $oReg,
    ];

/*
    $oDispatcher = (object)compact(array_keys(get_defined_vars()));
*/
// execute command -------------------------------------------------------------------
    if ($bExcecuteCommand){
        if (\is_readable($sAddonPath.'/cmd/'.$sCommand.'.php')) {
            include($sAddonPath.'/cmd/'.$sCommand.'.php');
        } elseif (\is_readable($sAddonPath.'/cmd/'.$sCommand.'.inc')) { // backward compability
            include($sAddonPath.'/cmd/'.$sCommand.'.inc');
        } elseif (\is_readable($sAddonPath.'/cmd/'.$sCommand.'.inc.php')) { // backward compability
            include($sAddonPath.'/cmd/'.$sCommand.'.inc.php');
        } else {
            throw new \Exception('call of invalid command ['.$sCommand.'] for [modules/'.$sAddonName.'] failed!');
        }
    }



// end of file