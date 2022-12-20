<?php

use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
?><?php
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
 * cmdInstall.php
 *
 * @category     Addons
 * @package      Addons_wrapper
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      3.0.1
 * @lastmodified $Date: 2018-09-28 21:29:06 +0200 (Fr, 28 Sep 2018) $
 * @since        File available since 17.12.2015
 * @description  xyz
 */

/* -------------------------------------------------------- */

    // get content
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'mod_wrapper` '
         . 'WHERE `section_id` = '.intval($section_id);
    if (($oInstances = $database->query($sql))) {
        if (($aInstance = $oInstances->fetchRow(MYSQLI_ASSOC)))
        {
            $aInstance['url'] = str_replace('{SYSVAR:AppUrl}', WB_URL.'/', $aInstance['url']);
            $aTplData = array_change_key_case($aInstance, CASE_UPPER);
            $aTplData['NOTICE'] = $MOD_WRAPPER['NOTICE'];
            if (class_exists('\Twig\Environment') && is_readable($sAddonTemplatePath.'/view.twig'))
            {
                // create twig template object
                $loader = new \Twig\Loader\FilesystemLoader($sAddonTemplatePath);
                $twig   = new \Twig\Environment($loader, [
                    'autoescape'       => false,
                    'cache'            => false,
                    'strict_variables' => false,
                    'debug'            => false,
                    'auto_reload'      => true,
                ]);
                echo $twig->render('view.twig', $aTplData);
            } else {
                // create phplib template object
                $oTpl = new Template($sAddonTemplatePath);
                $oTpl->set_file('page', 'view.htt');
                $oTpl->set_block('page', 'main_block', 'main');
                $oTpl->set_var($aTplData);
                // Parse template object
                $oTpl->parse('main', 'main_block', false);
                $oTpl->pparse('output', 'page');
            }
        }
    }
// end of file

