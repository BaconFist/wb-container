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
 * Description of /access/index.php
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
    $admin  = new admin('Access', 'access');
    $sAddonTheme     = $oReg->Theme;
    $sAddonThemeUrl  = $oReg->ThemeUrl.'templates/';
    $sAddonThemePath = \dirname($admin->correct_theme_source($sAddonName.'.twig'));
//
    $oTrans->enableAddon($sAcpDir.'\\'.$sAddonName);
    $aTwigData = [];
    $aTwigData['aLang'] = $oTrans->getLangArray();
// get addon rights
    $bUsersRights  = ($admin->get_permission('users')  == true);
    $bGroupsRights = ($admin->get_permission('groups') == true);
    $aSystemUrl = [
        'ADMIN_URL' => $oReg->AcpUrl,
        'THEME_URL' => $oReg->ThemeUrl,
        'WB_URL' => $oReg->WB_URL
    ];
//
    $aTwigData['user_block']  = $bUsersRights;
    $aTwigData['group_block'] = $bGroupsRights;
    $aTwigData['system']      = $aSystemUrl;
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
// Setup template phplib object, parse vars to it, then parse it
// Create new template object
        $template = new Template(\dirname($admin->correct_theme_source('access.htt')));
// $template->debug = true;
        $template->set_file('page', 'access.htt');
        $template->set_block('page', 'main_block', 'main');

// Insert values into the template object
        $template->set_var($aSystemUrl);

/**
 *    Insert permission values into the template object
 *    Deprecated - as we are using blocks.
 */
        $display_none = 'style="display: none;"';
        if (!$bUsersRights) { $template->set_var('DISPLAY_USERS', $display_none);}
        if (!$bGroupsRights){ $template->set_var('DISPLAY_GROUPS', $display_none);}

        $template->set_var($aTwigData['aLang']);
        $template->set_block('main_block', 'users_block', 'user');
        if ($bUsersRights){
// Insert section names and descriptions
/* deprecated
    $template->set_var([
                'USERS' => $MENU['USERS'],
                'USERS_OVERVIEW' => $OVERVIEW['USERS'],
                'ACCESS' => $MENU['ACCESS'],
            ]
        );
*/
            $template->parse('main_block', "users_block", true);
        } else {
            $template->set_block('users', '');
        }

        $template->set_block('main_block', 'groups_block', 'group');
        if ( $admin->get_permission('groups') == true ){
/* deprecated
        $template->set_var([
                'GROUPS' => $MENU['GROUPS'],
                'ACCESS' => $MENU['ACCESS'],
                'GROUPS_OVERVIEW' => $OVERVIEW['GROUPS'],
            ]
        );
*/
            $template->parse('main_block', "groups_block", true);
        } else {
            $template->set_block('groups', '');
        }
// Parse template object
        $template->parse('main', 'main_block', false);
        $template->pparse('output', 'page');
    } // end phplib
// disable addon languages
    $oTrans->disableAddon($sAcpDir.'\\'.$sAddonName);
// Print admin footer
    $admin->print_footer();

