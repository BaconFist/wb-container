<?php
/**
 *
 * @category        modules
 * @package         wysiwyg
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: upgrade.php 367 2019-06-11 15:49:11Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/wysiwyg/upgrade.php $
 * @lastmodified    $Date: 2019-06-11 17:49:11 +0200 (Di, 11. Jun 2019) $
 *
 */


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;


/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
    $sAddonName = basename($sAddonPath);
    $oReg = WbAdaptor::getInstance();
    $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
    $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
    $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
    if (version_compare($sWbVersion, $sModulePlatform, '<')){
        $msg[] = $sErrorMsg = sprintf('It is not possible to install/upgrade from WebsiteBaker Versions before %s',$sModulePlatform);
        if ($globalStarted){
            echo $sErrorMsg;
        }else{
            throw new Exception ($sErrorMsg);
        }
    } else {
        $sInstallStruct = $sAddonPath.'/install-struct.sql.php';
//        $oReg->Db->addReplacement('XTABLE_ENGINE','ENGINE=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
//        $oReg->Db->addReplacement('XFIELD_COLLATION','COLLATE utf8mb4_unicode_ci');
        if (!$oReg->Db->SqlImport($sInstallStruct, TABLE_PREFIX, 'upgrade' )){
              $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
          }

// sanitize URLs inside mod_wysiwyg.content ------------------------------------
        $sql = 'SELECT `content`, `section_id` FROM `'.TABLE_PREFIX.'mod_wysiwyg`';
        if (($oInstances = $database->query($sql))) {
            while (($aInstance = $oInstances->fetchAssoc())) {
// -----------------------------------------------------------------------------
// Fix to convert absolute Url in wysiwyg contents
                $sAppUrl = 'null'; // replace null with your domain 'https://example.com/' (default = null)
                $sMediaDir = 'null'; //replace null with your media folder name 'media'  (default = null)
// -----------------------------------------------------------------------------
                $sContent = $admin->ReplaceAbsoluteMediaUrl($aInstance['content'],$sAppUrl,$sMediaDir);
                // migrate old placeholder SYSVAR:MEDIA_REL to new format
                $sContent = str_replace (['{SYSVAR:MEDIA_REL}'],['{SYSVAR:AppUrl.MediaDir}'],$sContent);
                $sText = strip_tags($sContent);
                $sql = 'UPDATE `'.TABLE_PREFIX.'mod_wysiwyg` '
                     . 'SET `content`=\''.$database->escapeString($sContent).'\', '
                    . '`text`=\''.$database->escapeString($sText).'\' '
                     . 'WHERE `section_id`='.(int)$aInstance['section_id'];
                if (!$database->query($sql)) {
                    $msg[] = $database->get_error();
                    break;
                }
            }// end while
        } else { $msg[] = $database->get_error(); }
// ---------------------------------------------------------------------
            $aFilesToDelete = [
                '/install-struct.sql',
                '/templates/default/css/3/',
                '/templates/default/css/4/',
                '/themes/default/css/3/',
                '/themes/default/css/4/',
            ];
            PreCheck::deleteFiles($sAddonPath,$aFilesToDelete);
    }
