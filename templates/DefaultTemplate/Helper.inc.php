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
 */

/**
 * SimpleCommandDispatcher.inc.php
 *
 * @category     Addons
 * @package      Addons_Dispatcher
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      3.0.1
 * @lastmodified $Date: 2019-03-27 12:01:55 +0100 (Mi, 27. Mrz 2019) $
 * @since        File available since 17.12.2015
 * @description  xyz
 */

    if (is_readable(dirname(dirname(__DIR__)).'/modules/SimpleRegister.php')){
      require dirname(dirname(__DIR__)).'/modules/SimpleRegister.php';
    } else {
        throw new Exception('Call of an invalid WebsiteBaker Version ['.WB_VERSION.' '.WB_SP.'] failed!');
    }

    // detect if system running backend or frontend is already set by SimpleRegister
//    $oApp = (isset($GLOBALS['admin']) ? $GLOBALS['admin'] : $GLOBALS['wb']);.$sAddonName
    $bIsBackend = ($oReg->App instanceof admin);
    // set addon depending path / url
    $sAddonPath = $oReg->AppPath.'';
    $sAddonUrl  = $oReg->AppUrl.'';
    $sAddonRel = '/';
    // define the theme to use -----------------------------------------------------------
    if (is_readable($sAddonPath.'themes/default')) {
    // first set fallback to system default theme
        $sAddonThemePath = $sAddonPath.'themes/default';
        $sAddonThemeUrl  = $sAddonUrl.'themes/default';
    }
    if (is_readable($sAddonPath.'themes/'.$oReg->DefaultTheme)) {
    // overload with the selected theme if accessible
        $sAddonThemePath = $sAddonPath.'themes/'.$oReg->DefaultTheme;
        $sAddonThemeUrl  = $sAddonUrl.'themes/'.$oReg->DefaultTheme;
    }
    // define the template to use --------------------------------------------------------
    if (is_readable($sAddonPath.'templates/default')) {
        // first set fallback to system default template
        $sAddonTemplatePath = $sAddonPath.'templates/default';
        $sAddonTemplateUrl  = $sAddonUrl.'templates/default';
    }
    if (is_readable($sAddonPath.'templates/'.$oReg->DefaultTemplate)) {
        // try setting to the template of global settings
        $sAddonTemplatePath = $sAddonPath.'templates/'.$oReg->DefaultTemplate;
        $sAddonTemplateUrl  = $sAddonUrl.'templates/'.$oReg->DefaultTemplate;
    }
    if (!$bIsBackend && is_readable($sAddonPath.'/templates/'.$oReg->Template)) {
        // try setting to the template of page depending settings
        $sAddonTemplatePath = $sAddonPath.'templates/'.$oReg->Template;
        $sAddonTemplateUrl  = $sAddonUrl.'templates/'.$oReg->Template;
    }
    // load addon depending language file ------------------------------------------------
    if (is_readable($sAddonPath.'/languages/EN.php')) {
        // first load fallback to system default language (EN)
        include $sAddonPath.'/languages/EN.php';
    }
    if (is_readable($sAddonPath.'templates/languages/'.$oReg->DefaultLanguage.'.php')) {
        // try loading language of global settings
        include $sAddonPath.'templates/languages/'.$oReg->DefaultLanguage.'.php';
    }
    if (is_readable($sAddonPath.'templates/languages/'.$oReg->Language.'.php')) {
        // try loading language of user (backend) or page (frontend) defined settings
        include $sAddonPath.'templates/languages/'.$oReg->Language.'.php';
    }
    // load addon Theme/Template depending language file ---------------------------------
    $sTmp = ($bIsBackend ? $sAddonThemePath : $sAddonTemplatePath).'/languages/';
    if (is_readable($sTmp.'EN.php')) {
        // first load fallback to system default language (EN)
        include $sTmp.'EN.php';
    }
    if (is_readable($sTmp.$oReg->DefaultLanguage.'.php')) {
        // try loading language of global settings
        include $sTmp.$oReg->DefaultLanguage.'.php';
    }
    if (is_readable($sTmp.$oReg->Language.'.php')) {
        // try loading language of user (backend) or page (frontend) defined settings
        include $sTmp.$oReg->Language.'.php';
    }
    // Simple Command Dispatcher ---------------------------------------------------------

//    Translate::getInstance ()->enableAddon ('modules\\'.$sAddonName);
    // sanitize command from compatibility file

    $sClassDatabaseFile = dirname(__DIR__).'/framework/SP7Database.php';
    if (!class_exists('database', false) && is_readable($sClassDatabaseFile)){require $sClassDatabaseFile;}
    $sCommand = (isset($sCommand) ? strtolower($sCommand) : '');
    // sanitize/validate request var 'cmd'
    $sCmd = preg_replace(
        '/[^a-z\/0-1]/siu',
        '',
        (isset($_REQUEST['cmd']) ? strtolower($_REQUEST['cmd']) : '')
    );
    // build valid sCommand string
    if (($sCommand && $sCmd)) {
        if (!preg_match('/^'.$sCommand.'/si', $sCmd)) {
            // concate both arguments if needed
            $sCommand .= '/'.$sCmd;
        } else {
            $sCommand = $sCmd;
        }
    $sCmd = '';
    }
    $sCommand = str_replace( // remove spaces and add prefix 'cmd'
        ' ', '',
        ucfirst( // make first char of every word to uppercase
            str_replace( // change '/' to space
                '/', ' ',
                preg_replace( // change leading 'add/' to 'modify/'
                    '/^add\//s',
                    'modify/',
                    trim(($sCommand ?: $sCmd), '/') // remove leading and trailing slashes
                )
            )
        )
    );

/*--------------------------------------------------------------------------------------------------------*/
    $unixPath = (function ($string){
      return str_replace('\\', '/', $string);
    });
/*--------------------------------------------------------------------------------------------------------*/

// end of file
