<?php
/**
 *
 * @category        modules
 * @package         menu_link
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: save.php 290 2019-03-26 16:01:51Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/menu_link/save.php $
 * @lastmodified    $Date: 2019-03-26 17:01:51 +0100 (Di, 26. Mrz 2019) $
 *
*/

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use bin\SecureTokensInterface;
use vendor\idna_convert\idna_convert;


if (!\defined('SYSTEM_RUN')) {require( dirname(dirname((__DIR__))).'/config.php');}

    $sAbsAddonPath = str_replace(['\\','\\\\','//'],'/',__DIR__).'/';
    $sAddonName = \basename($sAbsAddonPath);
// check if module language file exists for the language set by the user (e.g. DE, EN)
    if (\is_readable($sAbsAddonPath.'languages/EN.php')) {require($sAbsAddonPath.'languages/EN.php');}
    if (\is_readable($sAbsAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAbsAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAbsAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAbsAddonPath.'languages/'.LANGUAGE.'.php');}

    $admin_header = false;
    // Tells script to update when this page was last updated
    $update_when_modified = true;
    // Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

try {

    $sAddonBackUrl = ADMIN_URL.'/pages/modify.php?page_id='.(int)$page_id;
    if (!\bin\SecureTokens::checkFTAN ()) {
        throw new \Exception(sprintf($MESSAGE['GENERIC_SECURITY_ACCESS']));
    }

// Update id, anchor and target
    if (isset($_POST['menu_link'])) {
        $iTargetPageId = intval($admin->get_post('menu_link'));
        $iRedirectType = intval($admin->get_post('r_type'));
        $anchor = ($admin->get_post('page_target'));
        $sTarget = $admin->get_post('target');
        $extern='';
// sanitize/validate url
        if (isset($_POST['extern'])) {
            $extern=$_POST['extern'];

            $oIdn = new idna_convert();
            // first add the local URL if there is no one
            $sNewUrl = ltrim(str_replace('\\', '/', $extern), '/');
            $extern = $admin->StripCodeFromText($oIdn->encode($sNewUrl));
            if (isset($extern)){
                $mValue = filter_var($extern
                      ,FILTER_VALIDATE_URL
                    );
                if (!$mValue) {
                    $sMessage = sprintf($MOD_MENU_LINK['FQDN_ERROR'], $oIdn->decode($extern));
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

//        if (sizeof($msg)==0) {}

        $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_menu_link` '
              . 'WHERE `section_id`='.(int)$section_id.'';
        if ((((int)$iNumRow = $database->get_one($sql))==0)){
            $sqlSet    = 'INSERT INTO `'.TABLE_PREFIX.'mod_menu_link` SET '
                       . '`page_id` = '.(int)$page_id.', '
                        . '`section_id` = '.(int)$section_id.', ';
            $sqlWHERE  = '';
        } else {
            $sqlSet    = 'UPDATE `'.TABLE_PREFIX.'mod_menu_link` SET ';
            $sqlWHERE  = 'WHERE `section_id`='.(int)$section_id.' ';
        }

        $sqlPage = 'UPDATE `'.TABLE_PREFIX.'pages` SET '
                 .'`target` = \''.$database->escapeString($sTarget).'\' '
                 .'WHERE `page_id` = '.(int)$page_id;
        if (!$database->query($sqlPage)){
          $sMessage = sprintf('[%03d] Error %s',__LINE__,$database->get_error());
          throw new \Exception ($sMessage);
        }

        $sqlSet  .= ''
                 . '`target_page_id` = '.(int)$iTargetPageId.', '
                 . '`redirect_type`  = '.(int)$iRedirectType.', '
                 . '`anchor` = \''.$database->escapeString($anchor).'\', '
                 . '`extern` = \''.$database->escapeString($extern).'\' '
                  . $sqlWHERE;

        if (!$database->query($sqlSet)){
          $sMessage = sprintf(' %s',$database->get_error());
          throw new \Exception ($sMessage);
        }
    }
    unset($sqlPage);
    unset($sqlSet);
    unset($sqlWHERE);

    $admin->print_header();
    $admin->print_success(sprintf($MESSAGE['PAGES_SAVED']),$sAddonBackUrl );

}catch (\Exception $ex) {
    $admin->print_header();
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

// Print admin footer
$admin->print_footer();
