<?php

/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage_show.php<br />
 *
 * Copyright (c) 2013, Drew Phillips
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.<br /><br />
 *
 * If you found this script useful, please take a quick moment to rate it.<br />
 * http://www.hotscripts.com/rate/49400.html  Thanks.
 *
 * @link https://www.phpcaptcha.org Securimage PHP CAPTCHA
 * @link https://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link https://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2013 Drew Phillips
 * @author Drew Phillips <drew@drew-phillips.com>
 * @version 3.6.6 (Nov 20 2017)
 * @package Securimage
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize,requester};
use bin\helpers\{PreCheck,msgQueue};

use \Securimage\Securimage;


// Remove the "//" from the following line for debugging problems
// error_reporting(E_ALL); ini_set('display_errors', 1);

    $sAddonFile   = \str_replace('\\','/',__FILE__);
    $sFolder      = \basename(\dirname($sAddonFile));
    switch ($sFolder):
        case 'cmd':
          $sAddonPath   = \dirname($sAddonFile,2).'/';
          break;
        default :
          $sAddonPath   = (\dirname($sAddonFile)).'/';
    endswitch;

    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = \basename($sModulesPath);
    $sAddonName   = \basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = \preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (! \defined('SYSTEM_RUN') && \is_readable($sAppPath.'config.php')) {
        require($sAppPath.'config.php');
    }

/* -------------------------------------------------------- */
    $bLocalDebug  = (\is_readable($sAddonPath.'.setDebug'));
    $bSecureToken = (!\is_readable($sAddonPath.'.setToken'));
    $bFrontendCss = (!\is_readable($sAddonPath.'.setFrontend'));
    $sPHP_EOL     = ($bLocalDebug ? "\n" : '');
    $sqlEOL       = ($bLocalDebug ? "\n" : "");
/* ------------------------------------------------------------------ */
// settings needs a admin or frontend object
    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
    $oReg     = WbAdaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();
/* -------------------------------------------------------- */

    $sSecKeyId = $oRequest->getParam('captchaId',FILTER_VALIDATE_INT); //  FILTER_SANITIZE_FULL_SPECIAL_CHARS
    $sCaptchaPath = $oReg->appPath.'include/captcha/';

/*          future use
    $captchaId = \bin\SecureTokens::checkIDKEY($sSecKeyId);
    \trigger_error(sprintf('[%d] namespace lautet %s - idKey %s checkIDKEY (%s) typehint %s',__LINE__,$namespace,$sSecKeyId,$captchaId,gettype($captchaId)), E_USER_NOTICE);
*/
    $captchaId = $sSecKeyId;
    $namespace = $oRequest->getParam('namespace',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $namespace = ($namespace ? $namespace : 'captcha'.$captchaId);
    $table = $oDb->TablePrefix.'mod_captcha_control';
    if (!is_null($captchaId)){
    //  settings from table captcha_controll
        if ($oSettings = $database->query("SELECT * FROM `$table` ")) {
            $aSettings = $oSettings->fetchAssoc();
            $sPathToBGImage =  (empty($aSettings['image_bg_dir']) ? 'backgrounds/' : ''.$aSettings['image_bg_dir']);//$sCaptchaPath.
            $fRatio  = ($aSettings['font_ratio'] ?? 1.5);
            //
            $iHeight = ((empty($aSettings['image_height']) || ($aSettings['image_height']==0)) ? $aSettings['image_width'] * '.$fRatio.' : $aSettings['image_height']);
            $options = [
                        'section_id'   => $captchaId,
                        'image_width'  => $aSettings['image_width'],
                        'image_height' => $iHeight,
                        'perturbation' => 1,
                        'num_lines'    => ($aSettings['num_lines'] ?? 5),
                        'noise_level'  => ($aSettings['noise_level'] ?? 5),
                        'line_color'   => $aSettings['line_color'],
                        'noise_color'  => $aSettings['noise_color'],
                        'debug'        => false,
                        'namespace'    => $namespace,
                        'image_signature' => $aSettings['image_signature'],
                        'signature_color' => $aSettings['signature_color'],
                        'ttf_file'        => (empty($aSettings['ttf_file']) ? 'fonts/'.'AHGBold.ttf' : ''.$aSettings['ttf_file']),
                        'font_ratio'      => $fRatio,
                        'text_transparency_percentage' => 0.4,
                        'text_color'      => $aSettings['text_color'],
                        'background_directory' => $sPathToBGImage,
                        'image_bg_color'       => $aSettings['image_bg_color'],
                        'captcha_type'    => (($aSettings['use_sec_type']==-1) ? \random_int(0,2) : $aSettings['use_sec_type']),
                        'icon_size'       => 16,
                /**** Code Storage & Database Options ****/
                        // true if you *DO NOT* want to use PHP sessions at all, false to use PHP sessions
                        'no_session'      => false,
                        // the PHP session name to use (null for default PHP session name)
                        // do not change unless you know what you are doing
                        'session_name'    => $namespace,
                        // change to true to store codes in a database
                        'use_database'    => false,
                        // database engine to use for storing codes.  must have the PDO extension loaded
                        // Values choices are:
                        // Securimage::SI_DRIVER_MYSQL, Securimage::SI_DRIVER_SQLITE3, Securimage::SI_DRIVER_PGSQL
                        'database_driver' => Securimage::SI_DRIVER_MYSQL,
                        'database_host'   => DB_HOST,     // database server host to connect to
                        'database_user'   => DB_USERNAME,          // database user to connect as
                        'database_pass'   => DB_PASSWORD,              // database user password
                        'database_name'   => DB_NAME,    // name of database to select (you must create this first or use an existing database)
                        'database_table'  => 'captcha_codes', // database table for storing codes, will be created automatically
                        // Securimage will automatically create the database table if it is not found
                        // change to true for performance reasons once database table is up and running
                        'skip_table_check' => false,
                        'use_random_spaces'   => true,
                        'use_random_baseline' => true,
                        'use_text_angles'     => true,
                        'use_random_boxes' => false,
/*
                        'audio_button_bgcol'=> '#ffffff',
                        'loading_icon_url'  => null,
                        'audio_icon_url'    => null,
                        'audio_play_url'    => null,
                        'audio_path'       => __DIR__ . '/audio/en/',
                        'audio_use_noise'  => true,
                        'audio_noise_path' => __DIR__ . '/audio/noise/',
                        'degrade_audio' => true,
*/
           ];
            $img = new Securimage($options); //\vendor\captcha\
            $img->getWBSession();
            $img->show();  // outputs the image and content headers to the browser
        } //  end $captchaId
    } //  end sanitize $captchaId
