<?php
/**
 *
 * @category        admin
 * @package         login
 * @author          Ryan Djurovich, WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: wb_info.php 344 2019-05-06 18:59:56Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/framework/helpers/wb_info.php $
 * @lastmodified    $Date: 2019-05-06 20:59:56 +0200 (Mo, 06. Mai 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,SysInfo};

// this part helps to prevent direct access
    $sAddonPath   = rtrim(str_replace('\\','/',__DIR__),'/').'/';
    $sModulesPath = \dirname($sAddonPath).'/'; //
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (! defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {
        require($sAppPath.'config.php');
    }
/*
     else {
        \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; ob_flush();flush(); exit;
    }
*/
    $admin = new admin('admintools','admintools_advanced',false,true);
    if (!$admin->is_authenticated() || !$admin->ami_group_member('1')){
        throw new \RuntimeException('Illegal file access!');
    }

    $aWritablePaths = [
        'languages',
        'media',
        'modules',
        'pages',
        'temp',
        'templates',
        'var',
        ];

    if (\is_readable(WB_PATH.'/modules/SimpleRegister.php')){
//      require WB_PATH.'/modules/SimpleRegister.php';
    }

    $oInfo = new SysInfo();

    if (\is_object($oReg->Db->DbHandle)) {
        $title = "MySQLi Info";
        $server_info          = \mysqli_get_server_info($oReg->Db->DbHandle);
        $host_info            = \mysqli_get_host_info($oReg->Db->DbHandle);
        $proto_info           = \mysqli_get_proto_info($oReg->Db->DbHandle);
        //$client_info          = $oReg->Db->getClientInfo();
        $client_info          = \mysqli_get_client_info(); // $oReg->Db->DbHandle
        $client_encoding      = \mysqli_character_set_name($oReg->Db->DbHandle);
        $status = \explode('  ', \mysqli_stat($oReg->Db->DbHandle));
    }
    $sRepairUrl = null;
    if (($oReg->Db->get_one('SELECT COUNT(*) FROM `'.$oReg->TablePrefix.'pages`'))){
        $sRepairUrl = $oReg->AppUrl.'framework/helpers/repairDb.php';
    }

    $sPattern      = '*.zip';
    $sUnzipFile    = 'install/unzip.php';
    $sPhpInfoFile  = 'install/phpInfo.php';
    $sUpgradeFile  = 'install/upgrade-script.php';
    $sUpdateFile   = 'interface/update.fixed';
    $sUpdateAbsFile = $oReg->AcpPath.$sUpdateFile;
    $aArchiveFiles = \glob($oReg->AppPath.$sPattern,\GLOB_MARK|\GLOB_NOSORT);
    if ((\is_readable($oReg->AcpPath.$sUpdateFile))){
        //$data = file_get_contents($sUpdateAbsFile);
        $sUpdateVersion = PreCheck::getAddonVariable(($sUpdateAbsFile),'version','admin');
        $sUpdateRevision = PreCheck::getAddonVariable(($sUpdateAbsFile),'revision','admin');
        $WbUpdateDateVersion = PreCheck::getAddonVariable(($sUpdateAbsFile),'timestamp','admin');
        $WbLastUpdateDate = PreCheck::getStrftime(null,$WbUpdateDateVersion);// d-m-Y H:i
        // TODO fetch json timestamp from Addon Repo and show if newer addon avaible
        $sUpdateNewVersion  = '';
        $WbUpdateNewVersion = '';
        $DownloadId = '0099E945';
        $sDownloadLink = 'https://addon.websitebaker.org/pages/en/browse-add-ons.php?download=0099E945';
    }
// Create new Template object with phplib
    $aTwigData = [
        'WB_URL' => $oReg->AppUrl,
        'IS_ADMIN'  => ($admin->getUserId()===1),
        'PHPINFO_URL' => (\is_readable($oReg->AppPath.$sPhpInfoFile) ? $oReg->AppUrl.$sPhpInfoFile : ''),
        'UPGRADE_URL' => (\is_readable($oReg->AppPath.$sUpgradeFile) ? $oReg->AppUrl.$sUpgradeFile : ''),
//        'UNZIP_URL' => (\is_readable($oReg->AppPath.$sUnzipFile) && count($aArchiveFiles) ? $oReg->AppUrl.$sUnzipFile : ''),
        'UPGRADE_NEW_URL' => (empty($WbUpdateNewVersion) ? $WbUpdateNewVersion : ''),
        'UNZIP_URL' => '',
        'THEME_URL' => $oReg->ThemeUrl,
        'HELPER_URL' => $oReg->AppUrl.'framework/helpers',
        'REPAIR_URL' => ($sRepairUrl ?? ''),
        'JQUERY_VERSION' => JQUERY_VERSION,
        'sAddonThemeUrl' => $oReg->ThemeUrl.'',
        'getInterface' => $oInfo->getInterface(),
        'isCgi' => $oInfo->isCgi(),
        'WbUpdateVersion' => 'R'.$sUpdateRevision, // $sUpdateVersion.
        'WbUpdateDateVersion' => $WbUpdateDateVersion,
        'WbLastUpdateDate' => $WbLastUpdateDate,
        'WbUpdateNewVersion'  => (empty($sUpdateNewVersion) ? '' : PreCheck::getStrftime('d-m-Y H:i',$sUpdateNewVersion,'de')),
        'WbVersion' => $oInfo->getWbVersion(true),
        'getOsVersion' => $oInfo->getOsVersion(true),
        'aWritablePaths' => $oInfo->checkFolders($aWritablePaths),
        'getSqlServer' => $oInfo->getSqlServer(),
        'client_encoding' => $client_encoding,
        'php_version' => \PHP_VERSION,
        'oReg' => $oReg,
        'server' => $oReg->Db->db_handle,
        'client_info' => $client_info,
        'server_info' => $server_info,
    ];
    $aTwigloader = ['header'=> 'header.twig',
                   'content' => 'content.twig',
                   'sysinfo' => 'sysInfo.twig',
                   'footer'  => 'footer.twig'
               ];
    if (\is_readable($oReg->ThemePath.'templates/'.$aTwigloader['sysinfo'])){
        $loader = new \Twig\Loader\FilesystemLoader($oReg->ThemePath . 'templates');
        $Twig = new \Twig\Environment(
            $loader, [
            'autoescape'       => false,
            'cache'            => false,
            'strict_variables' => false,
            'debug'            => false,
            ]);
/*-- finalize the page -----------------------------------------------------------------*/
        echo $Output = $Twig->Render($aTwigloader['sysinfo'], $aTwigData);//
    } else {
    }

