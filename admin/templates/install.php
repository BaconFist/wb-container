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
 * Description of install
 *
 * @package      Core package
 * @copyright    Dietmar Wöllbrink <info@giga-gui.de>
 * @author       Dietmar Wöllbrink <info@giga-gui.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: install.php 328 2019-04-02 18:34:56Z Luisehahne $
 * @since        File available since 04.11.2017
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;
use vendor\pclzip\PclZip;

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );

    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}
// Include the PclZip constant file (
    if (!\defined('PCLZIP_ERR_NO_ERROR')) { require($sAppPath.'include/pclzip/Constants.php'); }

// register addon vars
    $sAddonType         = 'template';
    $sAddonAppDir       = 'templates/';
    $aAllowedAddons     = ['template','theme'];

    $admin  = new \admin ('Addons', $sAddonType.'s_install', true);

    $oTrans->enableAddon(ADMIN_DIRECTORY.'\\addons');
    $aTrans = $oTrans->getLangArray();

// get request method
    $oRequest = (object) \filter_input_array (
                (\strtoupper ($_SERVER['REQUEST_METHOD']) == 'POST' ? \INPUT_POST : \INPUT_GET), FILTER_UNSAFE_RAW
    );

    if (isset ($_FILES['userfile'])) {
        $oRequest->userfile = (object) $_FILES['userfile'];
    }
    $isFILES = (isset ($oRequest->userfile)) ? 'true' : 'false';

try {

    if (!$isFILES){
        throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

// Set temp vars
    $sArchiveFileName   = $oRequest->userfile->name;
    $sUploadFile        = $oRequest->userfile->tmp_name;
    $sAppTmpPath        = $sAppPath . '/temp/';
    $sArchiveFilePath   = $sAppTmpPath;
    $sAddonMessage      = $oTrans->{'MESSAGE_GENERIC_INVALID_'.\strtoupper($sAddonType).'_FILE'};
// reset variable declared in info.php
    $sAddonFunc         = 'load_' . $sAddonType;
    $show_block         = isset($oRequest->advanced)&&(int)$oRequest->advanced;
    $sAddonBackUrl      = WB_URL.'/'.ADMIN_DIRECTORY.'/'.\basename(__DIR__).'/index.php'.($show_block?'?advanced='.$show_block:'');
    $sErrorMsg          = '';
    $sAddonDirectory    = '';
    $sAddonPlatform     = '';
    $sAddonVersion      = '';
    $sAddonName         = '';
    $sAddonFunction     = '';
    $sInfoFile          = '';
    $new_module_version = '';

// Check if user uploaded a file
    if ($oRequest->userfile->error) {
        // get constants
//        $aUploadMsgConsts                     = \getConstants ('core', true, '/^UPLOAD_/');
        $aUploadMsgConsts = \array_flip(\getConstants ('UPLOAD', 'Core'));
        // correct this one value
        $aUploadMsgConsts[UPLOAD_ERR_NO_FILE] = 'GENERIC_MISSING_ARCHIVE_FILE';
        // index for language files
        if (isset ($aUploadMsgConsts[$oRequest->userfile->error])) {
            $sErrorMsg = sprintf($oTrans->{'MESSAGE_' . $aUploadMsgConsts[$oRequest->userfile->error]}, \ini_get('upload_max_filesize'));
        }
        else {
            $sErrorMsg = $oTrans->MESSAGE_UNKNOW_UPLOAD_ERROR;
        }
    }

    if (!SecureTokens::checkFTAN ()) {
        throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

    /**
     * sanitize upladed file
     * ensure that a malicious user hasn't tried to trick the script into working on files
     * upon which it should not be working--for instance, /etc/passwd.
     */
    if (!\is_uploaded_file ($oRequest->userfile->tmp_name)) {
        throw new \Exception ($sErrorMsg."\n".$oTrans->MESSAGE_GENERIC_FORGOT_OPTIONS);
    }

    if (\preg_match ('/\.zip$/i', $sArchiveFilePath . $sArchiveFileName)) {
        if (!\move_uploaded_file ($sUploadFile, $sArchiveFilePath . $sArchiveFileName)) {
          throw new \Exception ($oTrans->MEDIA_FILE_NOT_FOUND);
        }
    }
    else {
        throw new \Exception ($oTrans->MESSAGE_GENERIC_FILE_TYPE.' [ZIP]');
    }

    $aFile          = [];
    $aFiles         = [];
// Setup the PclZip object
    $oArchive       = new \vendor\pclzip\PclZip ($sArchiveFilePath . $sArchiveFileName);
    $aFilesInArchiv = $oArchive->listContent ();
    foreach ($aFilesInArchiv as $index => $aFileInArchiv) {
        if ($aFileInArchiv['filename'] == 'info.php') {
            $aFiles = $oArchive->extract (
                    PCLZIP_OPT_BY_NAME, $aFileInArchiv['filename'], PCLZIP_OPT_EXTRACT_AS_STRING
            );
            $aFile[$aFiles['0']['filename']] = ($aFiles['0']['content'] ?? null);
            break;
        }
    }
    if (!isset ($aFile['info.php'])) {
        //$sMessage = \sprintf($oTrans->MESSAGE_GENERIC_INVALID_ADDON_FILE,$sArchiveFileName);
        $sMessage = \sprintf($oTrans->TEXT_SCRIPT_NOT_FOUND,'no content in file','info');
        throw new \Exception ($sMessage);
    }
    $sData = $aFile['info.php'];
// Check if uploaded file is a valid Add-On zip
    if ($sData) {
        $aNewModule['common']                = [];
        $aNewModule['common']['directory']   = get_variable_content ($sAddonType . '_directory', $sData);
        $aNewModule['common']['name']        = get_variable_content ($sAddonType . '_name', $sData);
        $aNewModule['common']['version']     = get_variable_content ($sAddonType . '_version', $sData);
        $aNewModule['common']['platform']    = get_variable_content ($sAddonType . '_platform', $sData);
        $aNewModule['common']['phpversion']  = get_variable_content ($sAddonType . '_phpversion', $sData);
        $aNewModule['common']['function']    = get_variable_content ($sAddonType . '_function', $sData);
        $aNewModule['common']['description'] = get_variable_content ($sAddonType . '_description', $sData);
        $aNewModule['common']['author']      = get_variable_content ($sAddonType . '_author', $sData);
        $aNewModule['common']['license']     = get_variable_content ($sAddonType . '_license', $sData);
        $sAddonName                        = $aNewModule['common']['name'];
        $sAddonFunction                    = ($aNewModule['common']['function'] ? : 'template');
        $new_module_version                = $aNewModule['common']['version'];
        $sAddonDirectory                   = $aNewModule['common']['directory'];
        $sInfoFile                         = $sAppPath . $sAddonAppDir . $sAddonDirectory . '/info.php';
        if (!\preg_match('/^[a-z0-9_-]+$/i',$sAddonDirectory) || ($sAddonDirectory==='')){
            $sAddonDirectory = (($sAddonDirectory=='') ? '?????' : $sAddonDirectory);
            $sInfoRelPath =  $sAddonAppDir.$sAddonDirectory.'/info.php';
            throw new \Exception (\sprintf('Template directory %s</b> not exists or has invalide chars',$sInfoRelPath));
        }
    }
/* unneeded because older templates haven't this value'
    if (!in_array($aNewModule['common']['function'],$aAllowedAddons)){
        throw new \Exception ($oTrans->MESSAGE_GENERIC_INVALID_TEMPLATE_FILE);
    }
*/
    if (!($aNewModule['common']['function'])){
        \trigger_error('Missing Template-Parameter [$'.$sAddonType.'_function] in '.$sAddonDirectory.'/info.php!', E_USER_NOTICE);
    }

    if (\is_readable ($sInfoFile)) {
        $aAddon = $admin->getContentFromInfoFile ($sInfoFile);
        $sAddonVersion = $aAddon['common']['version'];
        $sAddonPlatform = (\defined(WB_VERSION) ? WB_VERSION : $aNewModule['common']['platform']);
        $sWbVersion = (\defined('VERSION') ? VERSION : $sAddonPlatform);
        if (\version_compare ($sWbVersion, $sAddonPlatform, '<')){
            throw new \Exception (\sprintf($oTrans->MESSAGE_GENERIC_INVALID_PLATFORM, $sWbVersion));
        }
    }
    if (!$aFilesInArchiv) {
        throw new \Exception ($oTrans->MESSAGE_GENERIC_INVALID_ADDON_FILE);
    }
    else
    if (!\in_array ($sAddonFunction, $aAllowedAddons)) {
        throw new \Exception ($sAddonMessage);
    }

    $sAction      = "install";
// Check if this module is already installed
// and compare versions if so
// Set module directory
    $sAddonAbsDir = $sAppPath . $sAddonAppDir . $sAddonDirectory;
    if (\is_dir ($sAddonAbsDir)) {
        if (\is_readable ($sAddonAbsDir . '/info.php')) {
            $aTemp = [
                'type' => \ucfirst($sAddonType),
                'short' => $sAddonDirectory,
                'name' => $sAddonName
            ];
            // Version to be installed is older than currently installed version
            $iSteps = \version_compare ($new_module_version, $sAddonVersion);
            switch ($iSteps):
                case 1:  //  second is lower than the first
                    $sAction = 'upgrade';
                    break;
                case 0: //  they are equal
                    $sAction = 'upgrade';
//                    throw new \Exception (\vsprintf($oTrans->MESSAGE_GENERIC_ALREADY_INSTALLED,$aTemp));
                    break;
                case -1: //  first version is lower than the second
                    throw new \Exception (\vsprintf($oTrans->MESSAGE_GENERIC_NOT_UPGRADED,$aTemp));
                    break;
                default:
            endswitch;
        }
    }

// Make sure the module dir exists, and chmod if needed
    make_dir ($sAddonAbsDir);
    if (\is_writeable ($sAddonAbsDir)) {
// Unzip module to the module dir
        if (isset ($oRequest->overwrite)) {
            $iExtract = (int) $oArchive->extract (PCLZIP_OPT_PATH, $sAddonAbsDir, PCLZIP_OPT_REPLACE_NEWER);
        }
        else {
            $iExtract = (int) $oArchive->extract (PCLZIP_OPT_PATH, $sAddonAbsDir);
        }
    }
// Delete the temp zip file
    if ($iExtract == 0) {
        throw new \Exception ( $oArchive->errorInfo (true)."\n".$oTrans->MESSAGE_GENERIC_CANNOT_UNZIP);
    }
    $sActionScript = $sAddonAbsDir . '/' . $sAction . '.php';
// Run the modules install // upgrade script if there is one
    if (\file_exists ($sActionScript)) {require($sActionScript);}
// Print success message
//    $aTemp = ['ACTION' => $sAction, 'name' => $sAddonName, 'type' => ucfirst($sAddonType) ];
            $aTemp = [
                'type' => \ucfirst($sAddonType),
                'short' => $sAddonDirectory,
                'name' => $sAddonName,
            ];

    if (\function_exists($sAddonFunc)){
        if ($sAction == "install") {
    // Load module info into addons DB
            if (!$sAddonFunc($sAddonAbsDir, true, $aNewModule)){
                throw new \Exception (\vsprintf($oTrans->MESSAGE_GENERIC_MODULE_VERSION_ERROR,$aTemp));
            }
            $sMsg = \vsprintf($oTrans->MESSAGE_GENERIC_INSTALLED,$aTemp);
        }
        else
        if ($sAction == "upgrade") {
    // update module info in addons DB
            if (!$sAddonFunc($sAddonAbsDir, true, $aNewModule)){
                throw new \Exception (\vsprintf($oTrans->MESSAGE_GENERIC_MODULE_VERSION_ERROR,$aTemp));
            }
            $sMsg = \vsprintf($oTrans->MESSAGE_GENERIC_UPGRADED,$aTemp);
        }
    }
    if ($sArchiveFileName && \is_writable ($sArchiveFilePath . $sArchiveFileName)) {
      \unlink ($sArchiveFilePath . $sArchiveFileName);
    }

    $admin->print_success ($sMsg, $sAddonBackUrl);

}catch (\Exception $ex) {

    if ($sArchiveFileName && \is_writable ($sArchiveFilePath . $sArchiveFileName)) {
        \unlink ($sArchiveFilePath . $sArchiveFileName);
    }

    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

// Print admin footer
$admin->print_footer ();
