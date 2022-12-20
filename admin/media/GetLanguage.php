<?php

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

    $aJsonRespond = '';
    $sAddonFile   = str_replace('\\','/',__FILE__).'/';
    $sAddonPath   = \dirname($sAddonFile).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleDir   = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.\basename($sModulesPath).'/'.$sAddonName;
    $sPattern = "/^(.*?\/)".$sModuleDir."\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sAddonPath, 1 );
    try{
// load config created for modify or save module files if SYSTEM_RUN don't exist, '
      if (!\defined('SYSTEM_RUN')) {require($sAppPath.'/config.php');}
      $admin = new admin('Media', 'media_upload', FALSE, FALSE);
      $oReg     = WbAdaptor::getInstance();
      $oTrans   = $oReg->getTranslate();
      $oRequest = $oReg->getRequester();
      $oTrans->enableAddon(trim($oReg->AcpDir,'/').'\\'.basename($sAddonPath));
//header("Content-type: text/plain");

      $FileSize = $_POST['size'];
      $UploadMaxFilesize = $_POST['maxsize'];

      $aJsonRespond = sprintf($oTrans->MESSAGE_UPLOAD_ERR_PHPINI_SIZE, $FileSize,$UploadMaxFilesize );
//        $aJsonRespond['success'] = true;

    } catch (Exception $e) {
        $aJsonRespond = $e->getMessage();
//        $aJsonRespond['success'] = false;
    }
    exit(($aJsonRespond));

