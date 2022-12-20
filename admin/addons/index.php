<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of /addons/index.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: index.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;

if (!defined('SYSTEM_RUN') ){ require( dirname(dirname((__DIR__))).'/config.php' ); }

    $oReg       = WbAdaptor::getInstance();
    $oDb        = $oReg->getDatabase();
    $oTrans     = $oReg->getTranslate();
    $oRequest   = $oReg->getRequester();
//    $oApp       = $oReg->getApplication();
    $sAcpDir    = \trim($oReg->AcpDir,'/');
    $sAddonName = \basename(__DIR__);
    $sAddonRel  = $oReg->AcpRel.$sAddonName;
    $sAddonUrl  = $oReg->AppUrl.\ltrim($sAddonRel,'/');
    $sAddonPath = $oReg->AppPath.\ltrim($sAddonRel,'/');
//
    $admin = new admin('Addons', 'addons');
    $sAddonTheme     = $oReg->Theme;
    $sAddonThemeUrl  = $oReg->ThemeUrl.'templates/';
    $sAddonThemePath = \dirname($admin->correct_theme_source($sAddonName.'.twig'));
//
    $oTrans->enableAddon($sAcpDir.'\\'.$sAddonName);
    $aTwigData = [];
    $aTwigData['aLang'] = $oTrans->getLangArray();
//
    $bAdvanced = $oRequest->issetParam('advanced');
// get addon rights
    $bModulesRights    = ($admin->get_permission('modules')  == true);
    $bTemplatesRights  = ($admin->get_permission('templates') == true);
    $bLanguagesRights  = ($admin->get_permission('languages')  == true);
    $bAdmintoolsRights = ($admin->get_permission('admintools') == true);
    $bReloadRights     = $bAdmintoolsRights && $bAdvanced;
    $sFtan             = \bin\SecureTokens::getFTAN();
    $DefaultUrl       = ($bAdmintoolsRights ? $oReg->AcpUrl.'addons/index.php' : '');
    $AdvancedUrl      = ($bAdmintoolsRights ? $oReg->AcpUrl.'addons/index.php?advanced' : '');
    $aSystemUrl = [
        'ADMIN_URL'    => $oReg->AcpUrl,
        'THEME_URL'    => $oReg->ThemeUrl,
        'WB_URL'       => $oReg->AppUrl,
        'RELOAD_URL'   => $oReg->AcpUrl.'/addons/reload.php',
        'ADVANCED_URL' => (!$bAdvanced ? $AdvancedUrl : $DefaultUrl),
        'DEFAULT_URL'  => $DefaultUrl,
        'FTAN'         => $sFtan,
        'FTAN_NAME'    => $sFtan['name'],
        'FTAN_VALUE'   => $sFtan['value'],
    ];
    $aTwigData['modules_block']   = $bModulesRights;
    $aTwigData['templates_block'] = $bTemplatesRights;
    $aTwigData['languages_block'] = $bLanguagesRights;
    $aTwigData['reload_block']    = $bReloadRights;
    $aTwigData['system']          = $aSystemUrl;
//
    if (\class_exists('\Twig\Environment') && \is_readable($sAddonThemePath.'/'.$sAddonName.'.twig'))
    {
        // create twig template object
        $loader = new \Twig\Loader\FilesystemLoader($sAddonThemePath);
        $twig   = new \Twig\Environment($loader, [
            'autoescape'       => false,
            'cache'            => false,
            'strict_variables' => false,
            'debug'            => false,
            'auto_reload'      => true,
        ]);
        echo $twig->render($sAddonName.'.twig', $aTwigData);
    } else
    {
// Setup template object, parse vars to it, then parse it
// Create new template object
        $template = new Template(dirname($admin->correct_theme_source('addons.htt')));
        $template->set_file('page', 'addons.htt');
        $template->set_block('page', 'main_block', 'main');
// Insert values into the template object
        $template->set_var($aSystemUrl );
/**
 *    Setting up the blocks
 */
        $template->set_block('main_block', "modules_block", "modules");
        $template->set_block('main_block', "templates_block", "templates");
        $template->set_block('main_block', "languages_block", "languages");
        $template->set_block('main_block', "reload_block", "reload");
/**
 *    Insert permission values into the template object
 *    Obsolete as we are using blocks ... see "parsing the blocks" section
 */
        $display_none = 'style="display: none;"';
        if (!$bModulesRights)   { $template->set_var('DISPLAY_MODULES', $display_none); }
        if (!$bTemplatesRights) { $template->set_var('DISPLAY_TEMPLATES', $display_none); }
        if (!$bLanguagesRights) { $template->set_var('DISPLAY_LANGUAGES', $display_none); }
        if (!$bReloadRights){ $template->set_var('DISPLAY_ADVANCED', $display_none); }
//
        if (!$bAdmintoolsRights || !$bAdvanced){
            $template->set_var('DISPLAY_RELOAD', $display_none);
        }
/**
 *    Insert section names and descriptions
 */
        $template->set_var($aTwigData['aLang']);

/**
 *    Parsing the blocks ...
 */
        if ( $admin->get_permission('modules') == true){
        $template->parse('main_block', "modules_block", true);}
        if ( $admin->get_permission('templates') == true){
        $template->parse('main_block', "templates_block", true);}
        if ( $admin->get_permission('languages') == true){
        $template->parse('main_block', "languages_block", true);}
        if ($bAdvanced && $bAdmintoolsRights){
        $template->parse('main_block', "reload_block", true);}

/**
 *    Parse template object
 */
        $template->parse('main', 'main_block', false);
        $template->pparse('output', 'page');
    }  // end phplib
// disable addon languages
    $oTrans->disableAddon($sAcpDir.'\\'.$sAddonName);
/**
 *    Print admin footer
 */
// Print admin footer
    $admin->print_footer();
