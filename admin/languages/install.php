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
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: install.php 66 2018-09-17 15:48:07Z Luisehahne $
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


    $sAddonFile   = \str_replace('\\','/',__FILE__);
    $sAddonPath   = (\dirname($sAddonFile)).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (! defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {
        require($sAppPath.'config.php');
    }

// register addon vars
    $sAddonType         =  'language';
    $sAddonAppDir       = '/languages/';
    $aAllowedAddons     = [];

    $admin  = new admin ('Addons', $sAddonType.'s_install', true);

    $oTrans->enableAddon($sModuleName.'\\addons');
    $aTrans = $oTrans->getLangArray();

// get request method
    $oRequest = (object) filter_input_array (
                (strtoupper ($_SERVER['REQUEST_METHOD']) == 'POST' ? INPUT_POST : INPUT_GET), FILTER_UNSAFE_RAW
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
    $sAppTmpPath        = WB_PATH . '/temp/';
    $sAddonAbsDir       = WB_PATH.$sAddonAppDir;
    $sArchiveFilePath   = $sAppTmpPath . $sArchiveFileName;
    $sAddonMessage      = $oTrans->{'MESSAGE_GENERIC_INVALID_'.strtoupper($sAddonType).'_FILE'};
// reset variable declared in info.php
    $sData              = '';
    $sAddonFunc         = 'load_' . $sAddonType;
    $show_block         = isset($oRequest->advanced)&&(int)$oRequest->advanced;
    $sAddonBackUrl      = ADMIN_URL.'/'.basename(__DIR__).'/index.php'.($show_block?'?advanced='.$show_block:'');
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
//        $aUploadMsgConsts = \getConstants ('core', true, '/^UPLOAD_/');
        $aUploadMsgConsts = \array_flip(\getConstants ('UPLOAD', 'Core'));
        // correct this one value
        $aUploadMsgConsts[UPLOAD_ERR_NO_FILE] = 'GENERIC_MISSING_ARCHIVE_FILE';
        // index for language files
        if (isset ($aUploadMsgConsts[$oRequest->userfile->error])) {
            $sErrorMsg = $oTrans->{'MESSAGE_' . $aUploadMsgConsts[$oRequest->userfile->error]};
        }
        else {
            $sErrorMsg = $oTrans->MESSAGE_UNKNOW_UPLOAD_ERROR;
        }
    }

    if (!\bin\SecureTokens::checkFTAN ()) {
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

    $bIsLanguageFile  = \preg_match('/^([A-Z]{2}\.php)$/', $sArchiveFileName);
    if (!$bIsLanguageFile) {
        throw new \Exception ($sAddonMessage);
    }

// remove languages file extension for later use
    $sFilenameNoExt = ($bIsLanguageFile ? \preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', $sArchiveFileName) : '');

    if ($bIsLanguageFile) {
        // Create temp string
        $sTempString = \basename($sUploadFile);
        $sTempFile = $sAppTmpPath.$sTempString;
        // set tempFile for unlink
        $sArchiveFilePath   = $sAppTmpPath . $sTempString;
        if (\move_uploaded_file ($sUploadFile, $sTempFile)){
            // Check if uploaded file is a valid language file (no binary file etc.)
            $sData = \file_get_contents($sTempFile, NULL, NULL, 1, 3072);
        }
    }
    else
    {
        throw new \Exception ($sAddonMessage);
    }

// Check if uploaded file is a valid Add-On zip
    if ($sData){
        $aNewModule['common'] =  [];
        $aNewModule['common']['code']       = get_variable_content($sAddonType.'_code',$sData);
        $aNewModule['common']['version']    = get_variable_content($sAddonType.'_version',  $sData);
        $aNewModule['common']['platform']   = get_variable_content($sAddonType.'_platform', $sData);
        $aNewModule['common']['phpversion']  = get_variable_content ($sAddonType . '_phpversion', $sData);
        $aNewModule['common']['name']       = get_variable_content($sAddonType.'_name',     $sData);
        $aNewModule['common']['function']   = get_variable_content($sAddonType.'_function', $sData);
        $sAddonName         = $aNewModule['common']['name'];
        $sAddonFunction     = $aNewModule['common']['function'];
        $new_module_version = $aNewModule['common']['version'];
        $sAddonCode         = $aNewModule['common']['code'];
    }


// look for langfile in folder languages to get previous information
    $sInfoFile = ($sAddonAbsDir.$sArchiveFileName);
    if (is_readable($sInfoFile)){
        $aAddon = $admin->getContentFromInfoFile ($sInfoFile);
        $sAddonVersion = $aAddon['common']['version'];
    }

    if ($sTempFile){;}
//    ($sAddonMessage);

    $sAction="install";
// Check if this module is already installed
// and compare versions if so
// Set module directory
    if (is_dir($sAddonAbsDir))
    {
        if(is_readable($sInfoFile)){
            $aTemp = [
                'type'  => ucfirst($sAddonType),
                'short' => $sFilenameNoExt,
                'name'  => $sAddonName,
            ];
// Version to be installed is older than currently installed version
            $iSteps = version_compare ($new_module_version, $sAddonVersion);
            switch ($iSteps):
                case 1:
                    $sAction = 'upgrade';
                    break;
                case 0:
                    throw new \Exception (vsprintf($oTrans->MESSAGE_GENERIC_ALREADY_INSTALLED,$aTemp));
                    break;
                case -1:
                    throw new \Exception (vsprintf($oTrans->MESSAGE_GENERIC_NOT_UPGRADED,$aTemp));
                    break;
                default:
            endswitch;
        }
    }

// Make sure the addon dir exists, and chmod if needed
    make_dir ($sAddonAbsDir);
    if (is_writeable ($sAddonAbsDir)) {
        if (isset ($oRequest->overwrite)) {
            if (!copy($sTempFile, $sInfoFile)){
                $aTemp = ['folder' => $sAddonAppDir];
                throw new \Exception (vsprintf($oTrans->MESSAGE_UPLOAD_ERR_CANT_WRITE_FOLDER, $aTemp));
            }
        }
    }


    $sActionScript = $sAddonAbsDir.'/'.$sAction.'.php';
// Run the modules install // upgrade script if there is one
    if(file_exists($sActionScript)){require($sActionScript);}

    $sMsg = 'Unknown Action';
// Print success message
    $aTemp = [
        'type'  => ucfirst($sAddonType),
        'short' => $sAddonCode,
        'name'  => $sAddonName
    ];
    if ($sAction == "install") {
// Load module info into addons DB
        if (!$sAddonFunc($sInfoFile)){
            throw new \Exception (vsprintf($oTrans->MESSAGE_GENERIC_MODULE_VERSION_ERROR,$aTemp));
        }
        $sMsg = vsprintf($oTrans->MESSAGE_GENERIC_INSTALLED,$aTemp);
    }
    else
    if ($sAction == "upgrade") {
// update module info in addons DB
        if (!$sAddonFunc($sInfoFile)){
            throw new \Exception (vsprintf($oTrans->MESSAGE_GENERIC_MODULE_VERSION_ERROR,$aTemp));
        }
        $sMsg = vsprintf($oTrans->MESSAGE_GENERIC_UPGRADED,$aTemp);
    }

    if ($sArchiveFileName && is_writable ($sArchiveFilePath)) {
        unlink ($sArchiveFilePath);
    }

    $admin->print_success ($sMsg, $sAddonBackUrl);

}catch (\Exception $ex) {

    if ($sArchiveFileName && is_writable ($sArchiveFilePath)) {
        unlink ($sArchiveFilePath);
    }

    $sErrMsg = Precheck::xnl2br(sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

// Print admin footer
$admin->print_footer ();
