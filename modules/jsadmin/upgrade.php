<?php
/**
 *
 * @category        modules
 * @package         jsadmin
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http:/websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: upgrade.php 288 2019-03-26 15:14:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/jsadmin/upgrade.php $
 * @lastmodified    $Date: 2019-03-26 16:14:03 +0100 (Di, 26. Mrz 2019) $
 *
 */
use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit; }
/* -------------------------------------------------------- */

    $msg = '';
    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace('\\', '/', __DIR__);
    $sAddonName = basename($sAddonPath);
    $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
    $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
    $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
    if (version_compare($sWbVersion, $sModulePlatform, '<')){
        $msg[] = $sErrorMsg = sprintf('It is not possible to install/upgrade from WebsiteBaker Versions before %s',$sModulePlatform);
        if ($globalStarted){
            echo $sErrorMsg;
        }else{
            throw new \Exception ($sErrorMsg);
        }
    } else {
/*
        $sTable = TABLE_PREFIX.'mod_jsadmin';
        if(($sOldType = $database->getTableEngine($sTable))) {
            if(('myisam' != strtolower($sOldType))) {
                if(!$database->query('ALTER TABLE `'.$sTable.'` Engine = \'MyISAM\' ')) {
                    $msg[] = $database->get_error();
                }
            }
        } else {
            $msg[] = $database->get_error();
        }
*/
// ------------------------------------
        $sInstallStruct = $sAddonPath.'/install-struct.sql.php';
        if (!is_readable($sInstallStruct)) {
            $msg[] = '<strong>\'missing or not readable file [install-struct.sql]\'</strong> '.$FAIL.'<br />';
            $iErr = true;
        } else {
            $database->SqlImport($sInstallStruct, TABLE_PREFIX, true );
        }

//  remove obselete files and folder
            $aRemoveList = [
                '/index.php',
                '/install-data.sql',
                '/install-struct.sql',
                '/sql/',
            ];
            PreCheck::deleteFiles($sAddonPath,$aRemoveList);

    }