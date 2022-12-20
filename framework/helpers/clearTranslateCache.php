<?php
/**
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
 * @category        core
 * @package         test
 * @subpackage      test
 * @author          Dietmar Wöllbrink
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.4 and higher
 * @version         $Id: clearTranslateCache.php 68 2018-09-17 16:26:08Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/framework/helpers/clearTranslateCache.php $
 * @lastmodified    $Date: 2018-09-17 18:26:08 +0200 (Mo, 17. Sep 2018) $
 *
 */

declare(strict_types=1);

use bin\{WbAdaptor,SecureTokens,Sanitize,requester};
use bin\helpers\{PreCheck};
//use addon\WBLingual\Lingual;

// BEGIN this part helps to prevent direct access
    $sAddonPath   = rtrim(str_replace('\\','/',(__DIR__)),'/').'/';
    $sModulesPath = \dirname($sAddonPath).'/'; //
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
//trigger_error(\sprintf('%s', $sAppPath),E_USER_NOTICE);
    if (! defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {
        require($sAppPath.'config.php');
    }
// END this part helps to prevent direct access

    // initialize json_respond array  (will be sent back)
    $aJsonRespond = [];
    $aJsonRespond['content'] = '';
    $aJsonRespond['message'] = 'Clear Cache operation failed';
    $aJsonRespond['success'] = false;
    try {
    // check autentification
        $admin  = new admin('admintools', 'admintools_advanced',false);
        $oReg   = WbAdaptor::getInstance();
        $oDb    = $oReg->getDatabase();
        $oTrans = $oReg->getTranslate();
        if (!$admin->is_authenticated()  || !$admin->ami_group_member('1')) {
            $sMessage .= \sprintf('%s', $aJsonRespond['message']);
            throw new \Exception($sMessage);
        }

        if (!$admin->is_authenticated()  || !$admin->ami_group_member('1')) {
            $sMessage .= \sprintf('%s', json_encode($aJsonRespond));
            throw new \Exception($sMessage);
        }
        if (is_writable($oReg->AppPath.'temp/cache/Translate')) {
            //\trigger_error(\sprintf('%s',WB_PATH.'/temp/cache'),E_USER_NOTICE);
            $oTrans->clearCache();
        }

        $aJsonRespond['message'] = 'Translate Cache was cleared';
// If the script is still running, set success to true
        $aJsonRespond['success'] = true;

    } catch (\Exception $ex) {
        $sMessage .= \sprintf("<b>Clear Translate Cache failed::</b>%s",$ex->getMessage());
        $aJsonRespond['success'] = false;
    }
    //\trigger_error(\sprintf('%s',json_encode($aJsonRespond)),E_USER_NOTICE);
// and echo the json_respond to the ajax function
    exit(json_encode($aJsonRespond));
