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
 * Description of admin/media/upload.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: upload.php 285 2019-03-26 14:42:18Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use bin\requester\HttpRequester;
use bin\media\WbGif;
use vendor\pclzip\PclZip;
use bin\media\inc\PhpThumbFactory;
use Verot\Upload\Upload;

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}

        // suppress to print the header
    $admin = new \admin('Media', 'media_upload', false);

    $oReg = WbAdaptor::getInstance();
    $oTrans = $oReg->getTranslate();
    $oTrans->enableAddon(trim($oReg->AcpDir,'/').'\\'.\basename(__DIR__));

    PreCheck::increaseMemory('512M');
    $iMaxSize = PRECHECK::convertToByte('upload_max_filesize');
    $sMaxSize = PreCheck::convertByteToUnit($iMaxSize);
    $UploadMaxFilesize = ini_get('upload_max_filesize');
    $iPostMaxSize = PRECHECK::convertToByte('post_max_size');
    $sPostMaxSize = PRECHECK::convertByteToUnit($iPostMaxSize);
// Include the WB functions file
//    if (!function_exists('check_media_path')){require(WB_PATH.'/framework/functions.php');}
// Include the PclZip constant file
    if (!\defined('PCLZIP_ERR_NO_ERROR')) { require(WB_PATH.'/include/pclzip/Constants.php'); }

    if (\is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (\is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

try {

    $sTemp = ($oReg->Request->getParam('upload_target'));
    $bOverwrite  = ($oReg->Request->getParam('overwrite',FILTER_VALIDATE_BOOLEAN));
    $bDeleteZip  = ($oReg->Request->issetParam('delzip'));
    $MaxFileSize = ($oReg->Request->getParam('max_file_size',FILTER_SANITIZE_NUMBER_INT) ?? $iMaxSize*128);
    $target     = (\str_replace(MEDIA_DIRECTORY,'',$sTemp).'/');
    $sBacklinkUrl  =  ADMIN_URL.''.'/media/index.php?dir='.$sTemp;
    $sAddonBackUrl =  ADMIN_URL.''.'/media/index.php?dir='.$sTemp;
    // Create relative path of the target location for the file
    $sTarget       = \str_replace(MEDIA_DIRECTORY,'', $target);
    $aTmpFiles = ($_FILES['file'] ?? []);
    if (empty($aTmpFiles)) {
        $admin->print_header();
        $sMessage = \sprintf($oTrans->MESSAGE_UPLOAD_ERR_POST_MAX_SIZE."\n",$sPostMaxSize);
        throw new \Exception ($sMessage);
    }
/* */
    if (!\bin\SecureTokens::checkFTAN ()) {
        $admin->print_header();
        throw new \Exception ($MESSAGE['GENERIC_SECURITY_ACCESS']);
    }

    if (!\function_exists('__unserialize')){require(__DIR__.'/parameters.php');}

    $aMessage     = [];
    $aFiles       = [];
    $aFilesFailed = [];
    $pathsettings = [];

    $count           = 0;
    $sum_dirs        = 0;
    $sum_files       = 0;
    $iFolder         = 0;
    $iFiles          = 0;
    $good_uploads    = 0;
    $iTotaluploads   = 0;
    $iUploadedsFiles = 0;

    $sAllowedFileTypes  = 'bmp|gif|jpg|ico|jpeg|png';
    // get from settings and add to forbidden list
    $forbidden_file_types  = \preg_replace( '/\s*[,;\|#]\s*/','|',RENAME_FILES_ON_UPLOAD);

    // Get the current dir   .str_replace(MEDIA_DIRECTORY,'',$directory)
    $directory = (($target === '/') ?  '' : $target);

    $resizepath    = trim(\str_replace(['/',' '],'_', $sTarget),'_');
    $sParentPath   = $resizepath;

    $sMediaDir  = MEDIA_DIRECTORY;
    $sMedia     = \basename($sMediaDir); //
    $resizepath = (($resizepath=='/')||empty($resizepath) ? $sMedia : $resizepath);

    $sMediaAbsPath = str_replace('\\','/',WB_PATH.MEDIA_DIRECTORY).'';
    $relative      = rtrim($sTarget,'/').'/'; //$sMediaAbsPath.
    $bIncludeMedia = \strstr($sMedia,$relative)>=0;

    // Check to see if target contains ../

    $admin->print_header();
    if (!check_media_path($relative, $bIncludeMedia)) {
        throw new \Exception ($MESSAGE['MEDIA_TARGET_DOT_DOT_SLASH']);
    }

/* ------------------------------  ------------------------------------------ */
    $sSettings      = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'mediasettings\' ');
    $width          = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'media_width\' ');
    $height         = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'media_height\' ');
    $jpegQuality    = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'media_compress\' ');
    $aUploadOptions = [
        'resizeUp'              => $oReg->Request->getParam('resize_up',FILTER_VALIDATE_BOOLEAN) ?? false,
        'jpegQuality'           => $jpegQuality,
        'correctPermissions'    => true,
        'preserveAlpha'         => true,
        'alphaMaskColor'        => [255, 255, 255],
        'preserveTransparency'  => true,
        'transparencyMaskColor' => [0, 0, 0]
    ];

    $pathsettings = __unserialize($sSettings);

    $resizeImages =  (function ($sPathName='', $ext='') use ($pathsettings,$resizepath,$width,$height,$aUploadOptions,$sAllowedFileTypes)
    {
          $sFilename = \basename($sPathName);
          $bValidFile = (($sFilename != '') && \preg_match('/' . $sAllowedFileTypes . '$/i', $ext) && !(WbGif::isAnimatet($sPathName)));
          if (\is_readable($sPathName) && $bValidFile ) {
                  $rimg = PhpThumbFactory::create($sPathName,$aUploadOptions);
                  if (isset($pathsettings[$resizepath])
                       && ((isset($pathsettings[$resizepath]['width']) && $pathsettings[$resizepath]['width']!=0)
                       || (isset($pathsettings[$resizepath]['height']) && $pathsettings[$resizepath]['height']!=0)))
                       {
                            $rimg->resize($pathsettings[$resizepath]['width'],$pathsettings[$resizepath]['height'],$sPathName);
//echo sprintf('[%d] %s <br />Breite %d px x Höhe %d px<br />',__LINE__, $sPathName, $pathsettings[$resizepath]['width'],$pathsettings[$resizepath]['height']);
                            $rimg->save($sPathName);
                        } else {
                            if ($width!=0 || $height!=0) {
//                                $rimg = PhpThumbFactory::create($sPathName,$aUploadOptions);
                                $rimg->resize($width,$height,$sPathName);
//echo sprintf('[%d] %s <br />Breite %d px x Höhe %d px<br />',__LINE__, $sPathName, $width,$height);
                                $rimg->save($sPathName);
                        }
              }
          } // end of resize
    });
/* ---------------------------------------------------------------------------------- */
    // Loop through the files
    $aTmpFiles = ($_FILES['file'] ?? []);
    $aFiles['file'] = [];
    $aErrorFiles = [];
    $key=0;
    if (empty($aTmpFiles)) {
        $sMessage = \sprintf($oTrans->MESSAGE_UPLOAD_ERR_POST_MAX_SIZE."\n",$sPostMaxSize);
        throw new \Exception ($sMessage);
    }
    //  prepare file array for upload handling
    $aTmpLoop  = $aTmpFiles['error'];
    foreach ($aTmpLoop as $index=>$item){
        if (($item===\UPLOAD_ERR_OK)) {
            $aFiles['file'][$key]['name'] = $aTmpFiles['name'][$index];
            $aFiles['file'][$key]['type'] = $aTmpFiles['type'][$index];
            $aFiles['file'][$key]['size'] = $aTmpFiles['size'][$index];
            $aFiles['file'][$key]['tmp_name'] = $aTmpFiles['tmp_name'][$index];
            $aFiles['file'][$key]['error'] = $item;
            $sFilename = $sMediaAbsPath.$relative.$aFiles['file'][$key]['name'];
            $bFileExists = \is_readable($sFilename) && !$bOverwrite;
            if ($bFileExists){
//echo nl2br(sprintf("<div class='w3-border w3-padding w3-margin-left'>[%04d] sFilename =  %s</div>\n",__LINE__,$sFilename));

                $sUploadedFile = $aFiles['file'][$key]['name'];
                $sMessage = \sprintf(($oTrans->MESSAGE_UPLOAD_ERR_FILE_EXISTS),__LINE__,$aFiles['file'][$iUploadedsFiles]['name']);
                $aMessage[$sUploadedFile] = $sMessage;
                $aFilesFailed[] = $sUploadedFile;
//                \trigger_error($sMessage, E_USER_NOTICE);
            }
            $iUploadedsFiles++;
        } elseif (in_array($item,[1,2,3,6,7,8])){
//            $aErrorFiles[] = $aTmpFiles['error'][$key].' File '.$aTmpFiles['name'][$key];
              switch ($item) {
                  case \UPLOAD_ERR_INI_SIZE:
                      $aErrorFiles[] = sprintf($errorTypes[$item], $aTmpFiles['name'][$key], $UploadMaxFilesize);
                      break;
                  case \UPLOAD_ERR_FORM_SIZE:
                      $aErrorFiles[] = sprintf($errorTypes[$item], $aTmpFiles['name'][$key], $MaxFileSize);
                      break;
                  case \UPLOAD_ERR_PARTIAL:
                  case \UPLOAD_ERR_CANT_WRITE:
                  case \UPLOAD_ERR_EXTENSION:
                      $aErrorFiles[] = sprintf($errorTypes[$item], $aTmpFiles['name'][$key], '');
                      break;
                  case \UPLOAD_ERR_NO_TMP_DIR:
                      $aErrorFiles[] = sprintf($errorTypes[$item], '', '');
                      break;
              }// end switch
        }
        $key++;
    } //foreach

    foreach ($aFiles['file'] as $index=>$item)
    {
        if (($item['error']==0)) {
            $sFilename = $item['name'];
            // If file was upload to tmp
            if (!empty($sFilename))
            {
                // Remove bad characters
                $filename = \trim(media_filename($sFilename));
                $info     = \pathinfo($filename);
                $ext      = isset($info['extension']) ? $info['extension'] : '';
                if (($filename) && (\substr($filename, 0, 1) != '.') && !\preg_match("/".$forbidden_file_types."$/i", $ext) )
                {
                    $iTotaluploads++;
//    \trigger_error(sprintf('[%d] Sanitize directory %s',__LINE__, $relative.$filename), E_USER_NOTICE);
                    // Move to relative path (in media folder)
                    if (\is_readable($sMediaAbsPath.$relative.$filename) && $bOverwrite) {
                        if (\move_uploaded_file($item['tmp_name'], $sMediaAbsPath.$relative.$filename)) {
                            $good_uploads++;
                            $iFiles++;
                            // Chmod the uploaded file
                            change_mode($sMediaAbsPath.$relative.$filename);
                            $resizeImages($sMediaAbsPath.$relative.$filename,$ext);
                        }
                    } elseif (!\is_readable($sMediaAbsPath.$relative.$filename)) {
                        if (\move_uploaded_file($item['tmp_name'], $sMediaAbsPath.$relative.$filename)) {
                            $good_uploads++;
                            $iFiles++;
                            // Chmod the uploaded file
                            change_mode($sMediaAbsPath.$relative.$filename);
                            $resizeImages($sMediaAbsPath.$relative.$filename,$ext);
                        } else {
                          $sMessage = \sprintf($MESSAGE['MEDIA_NO_FILE_UPLOADED'],$filename, $iUploadedsFiles );
                          throw new \Exception ($sMessage);
                        }
                    }
                    // store file name of first file for possible unzip action
                    if (($count == 0)) {
                        $sZipFile = $sMediaAbsPath.$relative.$filename;
                        $sParentPath = $resizepath;
                        if (isset($pathsettings[$resizepath])) {
                            $width  = ((isset($pathsettings[$resizepath]['width'])  && ($pathsettings[$resizepath]['width']!=0))  ? $pathsettings[$resizepath]['width']  : $width);
                            $height = ((isset($pathsettings[$resizepath]['height']) && ($pathsettings[$resizepath]['height']!=0)) ? $pathsettings[$resizepath]['height'] : $height);
                        }
                    }
                    $count++;
                }else {
                    $sMessage = \sprintf($MESSAGE['MEDIA_NAME_FILETYPE'],'',$filename, $index);
                    throw new \Exception ($sMessage);
                }
            $count++;
            }
        }
    } //  end for

    if (($iFiles === 0) && (empty($iUploadedsFiles))) {
        $sErrMsg = PreCheck::xnl2br($aErrorFiles);
        $sMessage = \sprintf($MESSAGE['MEDIA_NO_FILE_UPLOADED']."\n".$sErrMsg,$iUploadedsFiles);
        throw new \Exception ($sMessage);
    } elseif (($iFiles !== $iUploadedsFiles)) {
          $sErrMsg = PreCheck::xnl2br($aErrorFiles);
          $sTmp = '<br /><ol><li>'.\implode('<li>', $aFilesFailed).'</li></ol>';
          $sMessage = \sprintf(($oTrans->MESSAGE_UPLOAD_ERR_FILE_EXISTS)."\n".$sErrMsg."\n",__LINE__,$sTmp);
        throw new \Exception ($sMessage);
    }
/* ------------------------------------------------------------------------------------------- */
// BEGIN Zip calls
/* ------------------------------------------------------------------------------------------- */
    // If the user chose to unzip the first file, unzip into the current folder
    if (($oReg->Request->issetParam('unzip')) && isset($sZipFile) && is_readable($sZipFile) )
    {
    /*
     * Callback function to skip files in black-list
        $p_header['filename'] = media_filename($p_header['filename']);
     */
    function pclzipCheckValidFile($p_event, &$p_header)
    {
        global $forbidden_file_types;
// ---- all files are skiped
        $bRetVal = false;
// Check for potentially malicious files
        $p_header['filename'] = str_replace(' ', '_', $p_header['filename']);
        $info = \pathinfo($p_header['filename']);
        $ext = isset($info['extension']) ? $info['extension'] : '';
        $dots = (\substr($info['basename'], 0, 1) == '.');
        if (($dots != '.')) {
            $bRetVal = !\preg_match('#^.*?\.('.$forbidden_file_types.')?$#is', $p_header['filename'], $aMatches);
        }
        return (int)$bRetVal;
    }
/* -------------------------------------------------------------------------- */
    function callbackPostExtract($p_event, &$p_header)
    {
         return 1;
    }
/* -------------------------------------------------------------------------- */
//     '/'.$forbidden_file_types.'$/i'
        $forbidden_file_types  = \preg_replace( '/\s*[,;\|#]\s*/','|',RENAME_FILES_ON_UPLOAD);
        // Required to unzip file.
        $archive = new PclZip($sZipFile);
        $list = $archive->extract(
            PCLZIP_OPT_PATH, $sMediaAbsPath.$relative
            ,PCLZIP_OPT_EXTRACT_DIR_RESTRICTION, WB_PATH.MEDIA_DIRECTORY
//                ,PCLZIP_OPT_BY_PREG, '/^.*?('.$forbidden_file_types.')?$/is'
            ,PCLZIP_CB_PRE_EXTRACT, 'pclzipCheckValidFile'
//            ,PCLZIP_CB_POST_EXTRACT, 'callbackPostExtract'
        );

        $iFiles = 0;
        $iFolder = 0;
        $good_uploads = (\is_array($list) ? \sizeof($list) : 0);
        if( $archive->errorCode() != 0 ) {
            // error while trying to extract the archive (most likely wrong format)
            $admin->print_error('UNABLE TO UNZIP FILE' . $archive->errorInfo(true));
        } else {
            // resize files
            $sArchivFile = '';
            for( $x = 0; $x < $good_uploads; $x++)
            {
                if ($list[$x]['status'] == 'skipped'){continue;}
                $sArchivFile = ($list[$x]['filename']);
//echo sprintf('[%d] %s <br />Folder %d / Dateien %d <br />',__LINE__, $sArchivFile, $iFolder,$iFiles);
                $info = \pathinfo($sArchivFile);
                $ext = isset($info['extension']) ? $info['extension'] : '';

                $sTmp =  \str_replace(\str_replace('\\','/',WB_PATH.MEDIA_DIRECTORY),'',$sArchivFile);
                $sChildPath = '_'.(\trim((\is_dir($sTmp) ? $sTmp : \dirname($sTmp)),'/'));
                $sChildPath =  \str_replace(['/',' '],'_',$sChildPath);

                $bValidFile = (($sArchivFile != '') && \preg_match('/' . $sAllowedFileTypes . '$/i', $ext) && !(WbGif::isAnimatet($sArchivFile)));
                $bChildIsEmpty = (isset($pathsettings[$sChildPath]) && empty($pathsettings[$sChildPath])) || !isset($pathsettings[$sChildPath]);
                $resizepath = ($bChildIsEmpty ? $sParentPath : $sChildPath);
//echo sprintf('[%d] %s',__LINE__,$resizepath.'/'.\basename($sArchivFile)).'<br />';
                if (($list[$x]['folder']==1)) {$iFolder++;} else {}
                if (($list[$x]['status'] == 'newer_exist') && $bOverwrite && $bValidFile){
                    $iFiles++;
                    $resizeImages($sArchivFile,$ext);
                } elseif (($list[$x]['status'] == 'ok') && $bValidFile){
                    $iFiles++;
                    $resizeImages($sArchivFile,$ext);
                }
/*
print '<pre  class="mod-pre rounded">function <span>'.__FUNCTION__.'( '.''.' );</span>  filename: <span>'.\basename(__FILE__).'</span>  line: '.__LINE__.' -> <br />';
\print_r( $list[$x] ); print '</pre>'.PHP_EOL; \flush (); //  ob_flush();;sleep(10); die();
echo sprintf('[%d] %s <br />Breite %d px x Höhe %d px<br />',__LINE__, $sArchivFile, $pathsettings[$resizepath]['width'],$pathsettings[$resizepath]['height']);
echo sprintf('[%d] %s <br />Breite %d px x Höhe %d px<br />',__LINE__, $sArchivFile, $width,$height);

echo sprintf('[%d] resizeUp %s <br />Breite %d px x Höhe %d px<br />',__LINE__, (int)$this->options['resizeUp'], $maxWidth,$maxHeight);
echo sprintf('[%d] resizeUp %s <br />Breite %d px x Höhe %d px<br />',__LINE__, (int)$this->options['resizeUp'], $this->maxWidth,$this->maxHeight);
echo sprintf('[%d] resizeUp %s <br />Breite %d px x Höhe %d px<br />',__LINE__, (int)$this->options['resizeUp'], $this->maxWidth,$this->maxHeight);
*/
           }  // endd for
        }
        if ($oReg->Request->issetParam('delzip') && is_readable($sZipFile)) { unlink($sZipFile); }
        unset($list);
    }    // end extract archiv
/* ------------------------------------------------------------------------------------------- */
// END Zip calls
/* ------------------------------------------------------------------------------------------- */
// single upload success

        if (($iFiles == 1)) {
            $sErrMsg = PreCheck::xnl2br($aErrorFiles);
            $sMessage = (!($oReg->Request->issetParam('unzip'))
            ? \sprintf($MESSAGE['MEDIA_SINGLE_UPLOADED']."\n".$sErrMsg,$iFiles)
            : \sprintf($MESSAGE['MEDIA_ZIP_UPLOADED']."\n".$sErrMsg,$iFolder,$iFiles));
            $admin->print_success($sMessage,$sAddonBackUrl);
        } elseif(($iFiles > 1)) {
            $sErrMsg = PreCheck::xnl2br($aErrorFiles);
            $sMessage = (!($oReg->Request->issetParam('unzip'))
            ? \sprintf($MESSAGE['MEDIA_MULTI_UPLOADED']."\n".$sErrMsg,$iFiles)
            : \sprintf($MESSAGE['MEDIA_ZIP_UPLOADED']."\n".$sErrMsg,$iFolder,$iFiles));
            $admin->print_success($sMessage,$sAddonBackUrl);
        } else {
            $sErrMsg = PreCheck::xnl2br($aErrorFiles);
            $sMessage = \sprintf($MESSAGE['MEDIA_NO_FILE_UPLOADED']."\n".$sErrMsg,$iFiles, $iTotaluploads );
            throw new \Exception ($sMessage);
        }

    } catch (\Exception $ex) {
        $sErrMsg = PreCheck::xnl2br(\sprintf('%s', $ex->getMessage()));
//    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
        $admin->print_error ($sErrMsg, $sAddonBackUrl);
        exit;
}

// Print admin
$admin->print_footer();
