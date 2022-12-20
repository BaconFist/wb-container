<?php


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
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
 * cmdModify.php
 *
 * @category     Addons
 * @package      Addons_wrapper
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      https://www.gnu.org/licenses/gpl.html   GPL License
 * @version      3.0.1
 * @lastmodified $Date: 2018-09-28 21:29:06 +0200 (Fr, 28 Sep 2018) $
 * @since        File available since 17.12.2015
 * @description  xyz
 */

/* -------------------------------------------------------- */

    // set default values for a new one or if no matching record found
    $aDefault = [
        'section_id' => $section_id,
        'page_id'    => $page_id,
        'url'        => '',
        'height'     => 400,
        'attributes' => 'w3-show'
    ];

    // Get page content
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'mod_'.$sAddonName.'` '
         . 'WHERE `section_id` = '.$section_id;
    if (($oInstances = $database->query($sql)))
    {
    // try to load an existing record
        if (!is_null($aRecord = $oInstances->fetchRow(MYSQLI_ASSOC)))
        {
            $aRecord['url'] = str_replace(
                                  '{SYSVAR:AppUrl}',
                                  str_replace('\\', '/', WB_URL).'/',
                                  $aRecord['url']
                              );
        }// record
    }// end get data from table
            $aRecord = ($aRecord ?? $aDefault);
            $aTplData = \array_change_key_case($aRecord, CASE_UPPER);
            // complete array with replacements
            $aTplData['WB_URL']      = WB_URL;
            $aTplData['THEME_URL']   = $sAddonThemeUrl;
        //    $aTplData['CANCEL_URL']  = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'#wb_'.$section_id;
            $aTplData['SAVE_URL']    = ADMIN_URL.'/pages/save.php';
            $aTplData['CANCEL_URL']  = ADMIN_URL.'/pages/index.php';
            $aTplData['TEXT_URL']    = $TEXT['URL'];
            $aTplData['TEXT_HEIGHT'] = $TEXT['HEIGHT'];
            $aTplData['TEXT_MIN_HEIGHT'] = $TEXT['MIN_HEIGHT'];
            $aTplData['TEXT_SAVE']   = $TEXT['SAVE'];
            $aTplData['TEXT_BACK']   = $TEXT['BACK'];
            $aTplData['TEXT_CANCEL'] = $TEXT['CANCEL'];
            $aTplData['TEXT_CLOSE']  = $TEXT['CLOSE'];
            $aTplData['TEXT_CLASS_ATTRIBUTES'] = $MOD_WRAPPER['CLASS_ATTRIBUTES'];
            $aTplData['TEXT_ADD_CLASS_ATTRIBUTES'] = $MOD_WRAPPER['ADD_CLASS_ATTRIBUTES'];
            $aTplData['FTAN']        = $admin->getFTAN();
            $aTplData['IDKEY']       = $admin->getIDKEY($section_id);
            if (is_readable($sAddonThemePath.'/modify.twig'))
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
                echo $twig->render('modify.twig', $aTplData);
            } else {
                 // create template object
                $oTpl = new Template($sAddonThemePath);
                $oTpl->set_file('page', 'modify.htt');
                $oTpl->set_block('page', 'main_block', 'main');
                // add array of replacements
                $oTpl->set_var($aTplData);
                // Parse template object
                $oTpl->parse('main', 'main_block', false);
                $oTpl->pparse('output', 'page');
            }

// end of file
