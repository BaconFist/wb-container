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
 * cmdSave.php
 *
 * @category     Addons
 * @package      Addons_wrapper
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      3.0.1
 * @lastmodified $Date: 2018-09-17 18:26:08 +0200 (Mo, 17 Sep 2018) $
 * @since        File available since 2015-12-17
 * @description  xyz
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\idna_convert\idna_convert;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

/* -------------------------------------------------------- */

//  Only for Development as pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

    $admin_header = false;
    // Tells script to update when this page was last updated
    $update_when_modified = true;
    // Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $oReg     = WbAdaptor::getInstance();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();
    $oApp     = $oReg->getApplication();

    $bBackLink = ($oRequest->getParam('pagetree') ?? false);
    $sBacklink = $oReg->AcpUrl.'pages/index.php';
    $sec_anchor = '#'.(\defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' ).(int)$section_id;
    $sAddonBackUrl = $oReg->AcpUrl.'pages/modify.php?page_id='.(int)$page_id.$sec_anchor;
    // Update the mod_wrapper table with the contents
    $sAddonBackUrl = ($bBackLink ? $sBacklink : $sAddonBackUrl);

try {
    if (!($id = $oApp->getIdFromRequest('section'))) {
        throw new \Exception ($MESSAGE['GENERIC_SECURITY_ACCESS']);
    }
// sanitize/validate url
        if (isset($aRequestVars['url'])) {
            $extern=$aRequestVars['url'];
//            include_once WB_PATH.'/include/idna_convert/idna_convert.class.php';
            $oIdn = new idna_convert();
            // first add the local URL if there is no one
            $sNewUrl = ltrim(str_replace('\\', '/', $extern), '/');
            $extern = $admin->StripCodeFromText($oIdn->encode($sNewUrl));
            if (isset($extern)){
                $mValue = filter_var($extern
                      ,FILTER_VALIDATE_URL
//                      |FILTER_FLAG_PATH_REQUIRED
                    );
                if (!$mValue) {
                    $sMessage = sprintf($MOD_WRAPPER['FQDN_ERROR'], $oIdn->decode($extern));
                    throw new \Exception ($sMessage);
                }
            }
            $extern = $oIdn->decode($extern);
            unset($oIdn);
            if (!preg_match('/^https?:\/\/.*$/si', $extern)) {
                $extern = WB_URL.'/'.$extern;
            }
            // replace local host by SYSVAR-Tag
            $extern = preg_replace(
                '/^'.preg_quote(str_replace('\\', '/', WB_URL).'/', '/').'/si',
                '{SYSVAR:AppUrl}',
                ltrim(str_replace('\\', '/', $extern), '/')
            );
        } else {
          $extern = '';
        }
    // sanitize/validate height
    $height = ($aRequestVars['height'] ?? 400);
    $min_height = ($aRequestVars['min_height'] ?? $height);

    $attribute = ($aRequestVars['attribute'] ?? '');
    // prepare SET part of the SQL-statement
    $sqlSet = '`'.TABLE_PREFIX.'mod_wrapper` SET '
            . '`section_id`='.(int)$section_id.', '
            . '`page_id`='.(int)$page_id.', '
            . '`url` = \''.$database->escapeString($extern).'\', '
            . '`height` = '.(int)$height.', '
            . '`min_height` = '.(int)$min_height.', '
            . '`attribute` = \''.$database->escapeString($attribute).'\' ';
    // search for instance of this module in section
    $sql = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_wrapper` '
         . 'WHERE `section_id`='.$section_id;
    if ($database->get_one($sql)) {
    // if matching record already exists run UPDATE
        $sql = 'UPDATE '.$sqlSet.'WHERE `section_id`='.$section_id;
    } else {
    // if no matching record exists INSERT new record
        $sql = 'INSERT INTO '.$sqlSet;
    }
    if (!($database->query($sql))){
        $sMessage = sprintf('%s ', $database->get_error());
        throw new \Exception ($sMessage);
    }
    // Tells script to update when this page was last updated
    $update_when_modified = true;


}catch (\Exception $ex) {
//    $admin->print_header();
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
// stay in modify
    if (!$bBackLink){
        $sExtra = '';
        $aMessage = PreCheck::xnl2br(sprintf('%s %s ',$sExtra,$MESSAGE['SETTINGS_SAVED']));
        $admin->print_success($aMessage, $sAddonBackUrl);
        exit;
    }

// Print admin footer
//$admin->print_footer();

// end of file
