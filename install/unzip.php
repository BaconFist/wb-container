<?php
/**
 * Name:            Unzip Script For Zip Archives
 * Version:         2.15
 * Author:          Viktor Vogel =>
 * Website:         https://joomla-extensions.kubik-rubik.de
 * Download old:    https://joomla-extensions.kubik-rubik.de/downloads/php-scripts-php-skripte/unzip-script-for-zip-archives
 * Download:        https://wiki.websitebaker.org/doku.php/en/downloads
 * License:         GPLv3
 * Description:     With this script you can unzip zip archives. This file and the archive are deleted automatically after the unzip process!
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.13.x
 * @requirements    PHP 7.4 and higher
 * @download Repro  https://addon.websitebaker.org/pages/en/browse-add-ons.php?id=04D6D0F4
 * @downlaod Wiki   https://wiki.websitebaker.org/doku.php/en/downloads
 */


if (version_compare(\PHP_VERSION , '7.4.0', '<')) {
   header('Content-Type: text/html');
?><!DOCTYPE HTML>
<html lang="en" dir="ltr">
  <head>
    <title>Wrong PHP Version</title>
  </head>
  <body style="text-align:center;">
    <h3>Date:<?= "\t". date('Y-m-d H:m A')."\n" ?></h3>
    <h2 style="color: red;">You have a outdated version of PHP (<?= PHP_VERSION ;?>)</h2>
    <p style="font-size: 24px;">Please contact your Provider for update to min 7.4.26 better min 8.0.12 and higher</p>
  </body>
</html>
<?php
exit;
}

// Settings - START ---------------------------------------------------
    $isWindows = (\strcasecmp(\substr(PHP_OS, 0, 3), 'WIN') === 0);
    $defaults = [
        'empty_folder'    => true,
        'empty_files'     => true,
        'delete_archive'  => true,
        'delete_unzip'    => true,
        'debug_mode'      => false,
        'start_unzip'     => 0,
        'aFiles'          => [],
    ];

    $args = [
        'empty_folder'   => [
                            'filter'   => \FILTER_VALIDATE_BOOLEAN,
                            'flags'    => \FILTER_REQUIRE_SCALAR,
                            ],
        'empty_files'    => [
                            'filter'   => \FILTER_VALIDATE_BOOLEAN,
                            'flags'    => \FILTER_REQUIRE_SCALAR,
                            ],
        'delete_archive' => [
                            'filter'   => \FILTER_VALIDATE_BOOLEAN,
                            'flags'    => \FILTER_REQUIRE_SCALAR,
                            ],
        'delete_unzip'   => [
                            'filter'   => \FILTER_VALIDATE_BOOLEAN,
                            'flags'    => \FILTER_REQUIRE_SCALAR,
                            ],
        'debug_mode'     => [
                            'filter'   => \FILTER_VALIDATE_BOOLEAN,
                            'flags'    => \FILTER_REQUIRE_SCALAR,
                            ],
        'var_dump'       => [
                            'filter'   => \FILTER_VALIDATE_BOOLEAN,
                            'flags'    => \FILTER_REQUIRE_SCALAR,
                            ],
        'aFile'         => [
                            'filter'   => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                            'flags'    => \FILTER_REQUIRE_SCALAR,
                            'options'  => ''
                           ],
        'can_log'      => [
                            'filter'   => \FILTER_VALIDATE_BOOLEAN,
                            'flags'    => \FILTER_REQUIRE_SCALAR,
                            'options'  => ''
                            ],
    ];
    $bVarDump = false;
    $aInputs       = \filter_input_array(\INPUT_POST, $args);
    $bStartUnzip   = (isset($aInputs['start_unzip']) ? $aInputs['start_unzip'] : true);
    $sZipPattern   = '*.zip';
//    $aZipFiles = \glob($sZipPattern,\GLOB_NOSORT);
// set REQUESTS to vars -----------------------------------------------
    $sLogFile =  (isset($aInputs['sLogFile']) ? $aInputs['sLogFile'] : '');
// Unzip empty folders? true - yes, false - no
    $bUnzipEmptyFolders =  (isset($aInputs['empty_folder']) ? $aInputs['empty_folder'] : true);
// Unzip empty files? true - yes, false - no
    $bUnzipEmptyFiles   =  (isset($aInputs['empty_files']) ? $aInputs['empty_files'] : true);
// set REQUESTS Variable for the output -------------------------------
    $output = '';
// delete files after finish,  prevent by reset
    $bDeleteArchive     = (isset($aInputs['delete_archive']) ? $aInputs['delete_archive'] : false);
    $bDeleteUnzip       = (isset($aInputs['delete_unzip']) ? $aInputs['delete_unzip'] : false);
    $bDebugMode         = (isset($aInputs['debug_mode']) ? $aInputs['debug_mode'] : false);
    $bVarDump           = (isset($aInputs['var_dump']) ? $aInputs['var_dump'] : false);
    $bCanLog            = (isset($aInputs['can_log']) ? $aInputs['can_log'] : false);
//    $aArchiveFiles      = ($bDebugMode ? [] : (isset($aInputs['aFiles']) ? $aInputs['aFiles'] : []));
    $sArchiveFiles      = ($bDebugMode ? [] : (isset($aInputs['aFile']) ? $aInputs['aFile'] : ''));
// Settings - END -----------------------------------------------------
    $sChecked           = ' checked="checked"';
    $sUnzipCheckFolders = ($bUnzipEmptyFolders ? $sChecked : '');
    $sUnzipCheckFiles   = ($bUnzipEmptyFiles ? $sChecked : '');
    $sCheckArchive      = ($bDeleteArchive ? $sChecked : '');
    $sCheckUnzip        = ($bDeleteUnzip ? $sChecked : '');
    $sCheckDebug        = ($bDebugMode ? $sChecked : '');
    $sCheckVarDump      = ($bVarDump ? $sChecked : '');
    $sCheckCanLog       = ($bCanLog ? $sChecked : '');

    $iStartTime   = \microtime(true);
    $sTimeStamp   = \gmdate('Ymd_His', \time());
    $sStartTime   = \sprintf('Start unzip at %s ',\gmdate('Y-m-d - H:i:s', \time())).\PHP_EOL;

// create absolute/relative paths -------------------------------------
    $sAddonName     = \basename(__DIR__);
    $sScriptName    = str_replace('\\','/',realpath($_SERVER["SCRIPT_FILENAME"]));
    $sScriptPath    = dirname($sScriptName);
    $sAppDir        = str_replace('\\', '/',__DIR__);
    $sDocRoot       = str_replace('\\','/',realpath($_SERVER["DOCUMENT_ROOT"]));
    $iSharedHosting = ($isWindows ? 0 : (strcmp(md5($sScriptPath),md5($sAppDir))));
    $sPathPattern   = "/^(.*?\/)admin\/.*$/";
    $sLink      = \str_replace('\\','/',__DIR__).'/admin/';
    $sAppRel    = \str_replace($sDocRoot,'',\dirname($sScriptName));
    $sAppRel    = rtrim((empty($sAppRel) ? '/' : $sAppRel),'/').'/';
    $sAcpRel    = $sAppRel.'admin/';
    $sLoginRel  = $sAppRel.'admin/login/index.php';
    $sAppPath   = \preg_replace ($sPathPattern, "$1", $sLink, 1 );
    $sLogPath   = $sAppPath.'var/log/';
    $sLogFile   = (empty($sLogFile) ? 'unzip_'.$sTimeStamp.'.log' : $sLogFile);
    $sLogRel    = $sAppRel.'var/log/';
    $sOldPath   = \str_replace('\\','/',\getcwd()).'/';
    $aOptions = [
      'DOCUMENT_ROOT'   => $_SERVER["DOCUMENT_ROOT"],
      'SCRIPT_FILENAME' => $_SERVER["SCRIPT_FILENAME"],
      'ScriptName'      => $sScriptName,
      'DocRoot'         => $sDocRoot,
      'SCRIPT_PATH'     => $sScriptPath,
      'AppDir'          => $sAppDir,
      'OctalDirMode'    => (int) \octdec('0755'),
      'OctalFileMode'   => (int) \octdec('0644'),
      'isWindows'       => $isWindows,//($isWindows ? 'true' : 'false'),
      'SharedHosting'   => (int)$iSharedHosting,
      'AppPath'         => $sAppPath,
      'LogPath'         => $sLogPath,
      'LogFile'         => $sLogFile,
      'AppRel'          => $sAppRel,
      'LogRel'          => $sLogRel,
      'bCanLog'         => $bCanLog,
      'ArchiveFile'     => $sArchiveFiles,
      'bDeleteArchive'  => $bDeleteArchive,
      'bDeleteUnzip'    => $bDeleteUnzip,
      'Memory_Limit'    => ini_get('memory_limit'),
      'MaxExecutionTime'=> ini_get('max_execution_time'),

    ];

    if ($bStartUnzip===true){
//      first try to create LogFilesPath
        $oZip = new Zipper($aOptions);
        $oZip->increaseMemory('512M',300);
        if (($bCanLog===true) && ($oZip->createPath($sLogPath)===false))
        {
            $sContent = sprintf('Can\'t create Logfile in %s ',$sLogRel);
            $sRetval .=  \sprintf('<li class="w3-text-red w3-large"></li>'.PHP_EOL,$sContent);
            file_put_contents($sLogPath.$sLogFile,$sContent, \FILE_APPEND);
        }
        elseif (($bCanLog===true))
        {
            $sContent = ''
                  . 'created: ['.\date('r').']'.PHP_EOL;
            //$sContent = '/**/';
            file_put_contents($sLogPath.$sLogFile,$sContent);
            unset ($sContent);
        }
// extract archive files
        $output .= $oZip->ExtractArchive($bUnzipEmptyFolders, $bUnzipEmptyFiles);
        //$oZip->deleteArchiveFile($bDeleteArchive);
        if (!empty($aInputs) && \is_array($aInputs) && count($aInputs)){
            //$output .= $oZip->deleteArchiveFile($bDeleteArchive);
            //$output .= $oZip->deleteUnzip($bDeleteUnzip);
        }
        //$oZip->resetMaxExecutionTime();
    }

    $iRuningTime = (\microtime(true) - $iStartTime);
    $sExecutionTime = \sprintf('Execution time %.3f sec', $iRuningTime).\PHP_EOL;
    $sEndTime = \sprintf('End unzip at %s ',\gmdate('Y-m-d - H:i:s', \time())).\PHP_EOL;
// ------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------
?><!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta charset="utf-8" />
        <meta name="referrer" content="no-referrer|same-origin"/>
        <title>Unzip Script For Zip Archives</title>
        <meta name="author" content="WebsiteBaker Org e.V." />
<!-- Mobile viewport optimisation -->
        <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=2" />
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css"/>
<style>
html { height:100%; }
body { min-height:101%; }
/*-------------------------------------------------------------------------------*/

/*-------------------------------------------------------------------------------*/
input[type="checkbox"].w3-check, input[type="radio"].w3-radio {-webkit-appearance: none;-moz-appearance: none!important;appearance: none;}

input[type="checkbox"].w3-check, input[type="radio"].w3-radio {width: 24px !important;height: 24px !important;position: relative !important;top: 8px !important;background-color: #0404A9 !important;}
input[type="checkbox"].w3-check:not(:checked), input[type="radio"].w3-radio:not(:checked) {background-color: #D6D6D6 !important;z-index: 1!important;}
input[type="checkbox"].w3-check:checked + .w3-validate, input.w3-radio[type="radio"]:checked + .w3-validate {color: #3D73A8 !important;font-weight: bold !important;}
input[type="checkbox"].w3-check:checked {border: 2px #0404A9 !important;color: #0404A9 !important;}
input[type="checkbox"].w3-check {z-index: -9999 !important;}
input[type="checkbox"].w3-check + label::before {content: "\00a0" !important;display: inline-block !important;font: 12px/1.15em sans-serif !important;font-weight: bold;}
input[type="checkbox"].w3-check + label::before {border: 1px solid #959595 !important;border-radius: 0px !important;height: 24px !important;width: 24px !important;margin: 0 .5em 0 -2.5em !important;padding: 0 !important;padding: 4px !important;}
input[type="checkbox"].w3-check:checked + label::before {background: #217DA1 !important;color: #fff !important;content: "\2713" !important;text-align: center !important;border-color: #217DA1 !important;}
label, label.w3-validate {font-weight: bold !important;color: #959595;}
input[type="checkbox"].w3-check + label > span::after{content: "";}
input[type="checkbox"].w3-check:checked + label > span::after{content: "";}

.w3-select-stripped option {min-height: 24px;}
.w3-select-stripped option:hover {background-color: #FBFBE3!important;}
.w3-select-stripped option:nth-child(2n){background-color: #EAEAEA;}

.progress-bar-striped {
                overflow: hidden;
                height: 40px;
                margin-bottom: 20px;
                background-color: #f5f5f5;
                border-radius: 4px;
                -webkit-box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
                -moz-box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
                box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
            }
            .progress-bar-striped > div {
                background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
                background-size: 40px 40px;
                float: left;
                width: 0%;
                height: 100%;
                font-size: 12px;
                line-height: 2.1;
                color: #ffffff;
                text-align: center;
                -webkit-box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.15);
                -moz-box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.15);
                box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.15);
                -webkit-transition: width 3s ease;
                -moz-transition: width 3s ease;
                -o-transition: width 3s ease;
                transition: width 3s ease;
                animation: progress-bar-stripes 2s linear infinite;
                background-color: #288ade;
            }
            .progress-bar-striped p{
                margin: 0;
            }

            @keyframes progress-bar-stripes {
                0% {
                    background-position: 40px 0;
                }
                100% {
                    background-position: 0 0;
                }
            }

/*-------------------------------------------------------------------------------*/
</style>
    </head>
    <body class="w3-sand">
      <main class="w3-margin-bottom" style="margin-top:50px;">
          <div class="w3-white w3-container w3-card-4 w3-margin w3-border" style="width:70%;margin: auto 15%!important;padding: 0!important;">
              <div class="w3-row w3-card-4 ">
                <header class="w3-container w3-blue w3-padding-large">
                  <h3><?= $sStartTime; ?> unzip to <?= $sAppRel;?></h3>
                </header>
              </div>
              <div class="w3-container w3-padding">
<?php
    if ($bVarDump && ($oZip instanceof Zipper))
    {
        print $oZip->showDebug($oZip->getAllVars());
        \flush (); //.PHP_EOL;  htmlspecialchars() ;sleep(10); die();
    }
?>
                  <h2 class="w3-text-teal w3-xxlarge w3-margin-0">Settings PHP Version (<?= PHP_VERSION;?> )</h2>
                  <form id="form-unzip" action="<?= $sAppRel;?>unzip.php" method="post" enctype="multipart/form-data">
                      <input type="hidden" name="extract" value="1" />
                      <input type="hidden" name="sLogFile" value="<?=$sLogFile;?>"/>
                      <div class="w3-row">
                          <label class="w3-quarter" style="vertical-align: top;">Select Zipfiles to extract</label>
                          <select id="aFiles" name="aFile" class="w3-select w3-select-stripped w3-margin-left w3-border w3-twothird w3-select-multi" size="4" style="height: 6.9em!important;">
<?php
      $sZipPattern   = '*.zip';
      $aZipFiles = \glob($sZipPattern,\GLOB_NOSORT);
      if ((count($aZipFiles))) {
          $select = ((count($aZipFiles)==1) ? ' selected="selected"' : '');
          foreach($aZipFiles as $item){
            if (is_readable(__DIR__).'/'.$item){
                $mTime = ' from '.date ("d F Y - h:i A", filemtime(__DIR__.'/'.$item));
            }
?>
                              <option value="<?= $item;?>"<?= $select;?> ><?= $item.$mTime;?></option>
<?php     }
      }
      else
      { ?>
                              <option value="" style="font-size: 18px!important;color: red;">No Archivefile found!</option>
<?php } ?>
                          </select>
                      </div>
                      <div class="w3-row w3--hide">
                          <label class="w3-text-gray"><b class="w3-large">&nbsp;</b> </label>
                          <input type="checkbox" id="empty_folder" value="1" name="empty_folder" class="w3-check" <?= $sUnzipCheckFolders;?> />
                          <label class="w3-validate w3-large" title="" for="empty_folder">Unpack Empty Folders<span class="">&nbsp;</span></label>
                      </div>
                      <div class="w3-row w3--hide">
                          <label class="w3-text-gray"><b class="w3-large">&nbsp;</b> </label>
                          <input type="checkbox" id="empty_files" value="1" name="empty_files" class="w3-check"<?= $sUnzipCheckFiles;?>/>
                          <label class="w3-validate w3-large" title="" for="empty_files">Unpack Empty Files<span class="">&nbsp;</span></label>
                      </div>
                      <div class="w3-row">
                          <label class="w3-text-gray"><b class="w3-large">&nbsp;</b> </label>
                          <input type="checkbox" id="delete_archive" value="1" name="delete_archive" class="w3-check"<?= $sCheckArchive;?>/>
                          <label class="w3-validate w3-large" title="" for="delete_archive">Delete Archive Files<span class="">&nbsp;</span></label>
                      </div>
                      <div class="w3-row">
                          <label class="w3-text-gray"><b class="w3-large">&nbsp;</b> </label>
                          <input type="checkbox" id="delete_unzip" value="1" name="delete_unzip" class="w3-check"<?= $sCheckUnzip;?>/>
                          <label class="w3-validate w3-large" title="" for="delete_unzip">Delete Unzip File<span class="">&nbsp;</span></label>
                      </div>
                      <div class="w3-row w3-hide">
                          <label class="w3-text-gray"><b class="w3-large">&nbsp;</b> </label>
                          <input type="checkbox" id="debug_mode" value="1" name="debug_mode" class="w3-check"<?= $sCheckDebug;?>/>
                          <label class="w3-validate w3-large" title="" for="debug_mode">Debug Mode Without Extracting Archive Files- <span class="">&nbsp;</span></label>
                      </div>
                      <div class="w3-row">
                          <label class="w3-text-gray"><b class="w3-large">&nbsp;</b> </label>
                          <input type="checkbox" id="var_dump" value="1" name="var_dump" class="w3-check"<?= $sCheckVarDump;?>/>
                          <label class="w3-validate w3-large" title="" for="var_dump">Show Config Array (after finishing unzip) <span class="">&nbsp;</span></label>
                      </div>
                      <div class="w3-row">
                          <label class="w3-text-gray"><b class="w3-large">&nbsp;</b> </label>
                          <input type="checkbox" id="can_log" value="1" name="can_log" class="w3-check"<?= $sCheckCanLog;?>/>
                          <label class="w3-validate w3-large" title="" for="can_log">Create Logfile (<?=$sLogRel.$sLogFile;?>) <span class="">&nbsp;</span></label>
                      </div>
                      <div class="w3-row-padding w3-section w3-stretch">
                          <div class="w3-half">
                              <button id="start_unzip" name="start_unzip" type="submit" class=" w3-large w3-input w3-btn w3-blue w3-padding w3-hover-green w3-margin-top" value="1" style="min-width: 10em;">Start Unzip</button>
                          </div>
                          <div class="w3-half">
                              <button formaction="<?= $sAppRel;?>unzip.php" formmethod="post" id="start_reset" name="start_reset" type="reset" class=" w3-large w3-input w3-btn w3-blue w3-padding w3-hover-red w3-margin-top" style="min-width: 10em;">Reset</button>
                          </div>
                      </div>
                  </form>
              </div>

              <div class="w3-container w3-padding">
                <div id="ProgressBar" class="progress-bar-striped w3-hide">
                    <div style="width: 100%;"><b><p class="w3-text-white w3-large w3-padding-0">Extracting Archive(s)</p></b></div>
                </div>
                <ol class="w3-ul">
                  <?= $output; ?>
                </ol>
                <h3 class="w3-large"><?= $sExecutionTime; ?></h3>
              </div>
              <div class="w3-row w3-card-4">
                  <footer class="w3-container w3-blue" style="height: 80px;">
                      <div class="w3-row">
                        <div class="w3-half">
                          <h3 class="w3-xlarge w3-padding"><?= $sEndTime; ?></h3>
                        </div>
                        <div class="w3-half">
<?php if (is_readable($sAppPath.'/admin/login/index.php')){ ?>
                          <div class="w3-right">
                              <h3 class="w3-large w3-text-white w3-padding"><a class="w3-btn w3-card w3-light-blue w3-hover-green w3-large" href="<?= $sLoginRel;?>" rel="noopener"><span class="w3-padding w3-xlarge">&#x2699;</span><span style="vertical-align: text-bottom;"> Backend</span></a></h3>
<?php }
      else
      { ?>
                              <div class="w3-right">
                                  <h3 class="w3-large w3-text-white w3-padding">Error::Missing Backend Login <?= $sAcpRel;?></h3>
<?php } ?>
                              </div>
                          </div>
                        </div>
                      </div>
                  </footer>
              </div>
          </div>
      </main>
      <div class="w3-container w3-margin-top">
          <div class="w3-row">
              <p style="text-align: center;margin-bottom: 10px;">©&nbsp;<?= date('Y');?> WebsiteBaker Org e.V.
              <a href="https://www.websitebaker.org/" style="font-weight: normal;" target="_blank" rel="noopener">WebsiteBaker</a> |
          </div>
      </div>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        function changeProgressbar(){
            var selectedValue = document.querySelector("#progress-value").value;
            document.querySelector(".progress-bar-striped > div").textContent = selectedValue + "%";
            document.querySelector(".progress-bar-striped > div").style.width = selectedValue + "%";
        }

        let unzipForm = document.getElementById('form-unzip');
        unzipForm.addEventListener (
            "submit",
            function (evt) {
//console.log(evt);
              let progress = document.getElementById('ProgressBar');
//console.log(progress);
              progress.classList.remove("w3-hide");
//                evt.preventDefault();
        });

        let fieldsreset = document.getElementById('start_reset');
        fieldsreset.addEventListener (
            "click",
            function (evt) {
//                deleteUnzip.checked = false;
//                deleteArchive.checked = false;
                let url = window.location.protocol +'//'+ window.location.host + window.location.pathname;
                window.location.href = url;
                evt.preventDefault();
        });

    });

</script>
    </body>
</html>
<?php

    class Zipper
    {
        private $oZip;
        private $bIsAddon;
        private $sOldPath;
        private $aConfig = [];

        public function __construct($aOptions)
        {
            $this->init($aOptions);
        }

        public function __destruct()
        {
            ini_restore('memory_limit');
            ini_restore('max_execution_time');
        }


        public function __isset($name)
        {
            return isset($this->aConfig[$name]);
        }

         public function __set($name, $value)
         {
    //         throw new \Exception('Tried to set a readonly or nonexisting property ['.$name.']!!');
             return $this->aConfig[$name] = $value;
         }

        public function __get($name)
        {
            $retval = null;
            if (!$this->__isset($name))
            {
                throw new \Exception('Tried to get nonexisting property ['.$name.']');
            }
                $retval = $this->aConfig[$name];
            return $retval;
        }

        public function set($name, $value = '')
        {
            $this->aConfig[$name] = $value;
        }

        public function get($name)
        {
            return $this->$name;
//            if (!$this->aConfig[$name]){throw new \Exception('Tried to get nonexisting property ['.$name.']');;}
//            return $this->aConfig[$name];
        }

        protected function init($aOptions)
        {
            $this->clearCache();
            //$this->oZip = new \ZipArchive;
            $this->aConfig['oZip'] = new \ZipArchive;
            foreach ($aOptions as $key=>$value){
                switch ($key):
                    case 'AppUrl':
                        $this->aConfig['FilesRootUrl'] = $value;
                        break;
                    case 'TargetDir':
                        $this->aConfig['CopyFilesTo'] = $value;
                        break;
                    case 'CsvSourceFile':
                        $this->aConfig['CsvFileName'] = $this->removeExtension($value);
                    default:
                        $this->aConfig[$key] = $value;
                endswitch;
            }
        }

        protected function setError($Message)
        {
            $this->error[] = $message;
            $this->error_type = 'unknown';
        }

        /**
         * CopyFilesTo::removeExtension()
         *
         * @param mixed $sFilename
         * @return
         */
        public function removeExtension ($sFilename)
        {
            return \preg_replace('#^.*?([^\/]*?)\.[^\.]*$#i', '\1', $sFilename);
        }

        /**
         * CopyFilesTo::createPath()
         *
         * @param mixed $sFilename
         * @return
         */
        public function createPath($sFilename)
        {
            $bRetval = true;
            $sFilename = (\is_file($sFilename) ? \dirname($sFilename) : $sFilename);
            $sFilename = (rtrim($sFilename, '/')).'/';
            if (!\is_readable($sFilename)){
                $iOldUmask = \umask(0);
                $bRetval = @\mkdir($sFilename, $this->OctalDirMode,true);
                \umask($iOldUmask);
            }
            return $bRetval;
        }

        public function rmoveFullPath($sBasedir='', $bPreserveBaseFolder = false)
        {
            $bRetval = true;
            $sPath = \rtrim($sBasedir, '\\\\/').'/';
            if (\is_readable($sPath)) {
                $oHandle = \opendir($sPath);
                while (false !== ($sFile = \readdir($oHandle))) {
                    if (($sFile != '.') && ($sFile != '..')) {
                        $sFileName = $sPath . '/' . $sFile;
                        if (\is_dir($sFileName)) {
                            $bRetval = rm_full_dir($sFileName, false);
                        } else {
                            $bRetval = \unlink($sFileName);
                        }
                        if (!$bRetval) { break; }
                    }
                }  // end while
                \closedir($oHandle);
                if (!$bPreserveBaseFolder && $bRetval) { $bRetval = \rmdir($sPath); }
            }
            return $bRetval;
        }

        protected function clearCache(){
            clearstatcache();
        }

        public function convertToArray ($sList)
        {
            $retVal = $sList;
            if (!is_array($sList)){
                $retVal = preg_split('/[\s,=+\;\:\/\.\|]+/', $sList, -1, PREG_SPLIT_NO_EMPTY);
            }
            return $retVal;
        }

          public function convertToByte ($iniSet='memory_limit')
          {
              $aMatch = [];
              $iMemoryLimit = (ini_get($iniSet));
              if ((int)$iMemoryLimit !== -1) {
                  \preg_match('/^\s*([0-9]+)([a-z])?\s*(_)?\s*$/i', $iMemoryLimit.'_', $aMatch);
                  $iMemoryLimit = (int)$aMatch[1];
                  switch ($aMatch[2]) {
                       case 'g': case 'G':
                          $iMemoryLimit *= 1024;
                      case 'm': case 'M':
                          $iMemoryLimit *= 1024;
                      case 'k': case 'K':
                          $iMemoryLimit *= 1024;
                          break;
                      default:
                          break;
                  }
                  unset($aMatch);
              }
              //$this->aConfig['iMemoryLimit'] = $iMemoryLimit;
              return $iMemoryLimit;
          }

          private function convertByteToUnit ($size, $roundup = 5, $decimals = 2)
          {
              $sRetval = "0K";
              $aFilesizeUnits = ["", "K", "M", "G", "T", "P", "E", "Z", "Y"];
              $addition = ((($roundup > 0) && ($decimals == 0)) ? 0.45 : 0);
              $sRetval = (($size > 0) ? \round($size / pow(1024,($i = \floor(log($size, 1024))))+$addition, $decimals).$aFilesizeUnits[$i] : $sRetval);
              return $sRetval;
          }

        public function setMaxExecutionTime(int $iMemoryLimit=300)
        {
            $iMaxMemoryLimit = ini_get('max_execution_time');
            $this->iMemory_Limit = $iMemoryLimit;
            if ($iMaxMemoryLimit < $iMemoryLimit){
              \ini_set('max_execution_time', $iMemoryLimit); // 7 minutes
            }
        }

        public function resetMaxExecutionTime()
        {
    //        \ini_set('max_execution_time', $this->MaxMemoryLimit);
    //        \ini_set('memory_limit', $this->Memory_Limit);
            ini_restore('memory_limit');
            ini_restore('max_execution_time');
        }

        public function increaseMemory($sMemoryLimit='256M',int $MaxExecutionTime=420)
        {
    //        TODO Ssnitize parameter
            $iDefautLimit = 245 * 1024 * 1024;
            \ini_set("gd.jpeg_ignore_warning", 1);
            \ini_set("gd.jpeg_ignore_warning", 1);
            $this->aConfig['sMemoryLimit'] = $sMemoryLimit;
            $this->aConfig['iMaxExecutionTime'] = $MaxExecutionTime;
            $iMemoryLimit = $this->convertToByte("memory_limit");
            if ($iMemoryLimit < $iDefautLimit) {
                \ini_set("memory_limit", $iMemoryLimit);
            }
            $this->setMaxExecutionTime($MaxExecutionTime);
        }

    //  extracts the content of a string variable from a string (save alternative to including files)
        public function get_variable_content($search='', $data='', $striptags=true, $convert_to_entities=true)
        {
            $match   = [];
            $mRetval = null;
            // search for $variable followed by 0-n whitespace then by = then by 0-n whitespace
            // then either " or ' then 0-n characters then either " or ' followed by 0-n whitespace and ;
            // the variable name is returned in $match[1], the content in $match[3]
            if (\preg_match('/(\$' .$search .')\s*=\s*("|\')(.*)\2\s*;/i', $data, $match))
            {
                if (\strip_tags(\trim($match[1])) == '$' .$search) {
                    // variable name matches, return it's value
                    $mRetval = (($striptags == true) ? \strip_tags($match[3]) : $match[3]);
                    $mRetval = ($convert_to_entities == true) ? \htmlentities($match[3]) : $match[3];
    //                return $match[3];
                }
            }
            return $mRetval;
        }

        public function deleteArchiveFile($bDelete = false)
        {
            $bDelete = ($bDelete ?? false);
            $sFile = str_replace($this->AppPath, '',$this->ArchiveFile);
            $sFile = (empty($sFile) ? 'No Archive found' : $sFile);
            if ($bDelete && is_file($this->ArchiveFile) && \unlink(realpath($this->AppPath.$this->ArchiveFile)))
            {
                $sRetval = \sprintf('<li class="w3-text-green w3-large">Installation archive %s successfully deleted</li>'.PHP_EOL,$this->AppRel.$this->ArchiveFile);
                $sError = \sprintf("\n[%03d] Installation archive %s successfully deleted\n",__LINE__, $sFile);
           }
           else
           {
                $sMsg = ($bDelete ? 'Archive File could not be deleted' : 'Archive File prevent from deleting');
                $sRetval = \sprintf('<li class="w3-text-red w3-large">Archive %s %s</li>'.PHP_EOL,$this->ArchiveFile,$sMsg);
                $sError = \sprintf("\n[%03d] %s\n",__LINE__, $sMsg);
            }
            if ($this->bCanLog===true) {
                \file_put_contents($this->LogPath.$this->LogFile,$sError, \FILE_APPEND);
            }
            return $sRetval;
        }

        function deleteUnzip($bDelete=false)
        {
            $sFile = str_replace($this->AppPath, '','unzip.php');
            if ($bDelete && \unlink(\realpath($this->AppPath.'unzip.php'))){
                $sRetval = \sprintf('<li class="w3-text-green w3-large">Unzip file successfully deleted</li>'.PHP_EOL);
                $sError = \sprintf("\n[%03d] Unzip file successfully deleted:",__LINE__, $sFile);
            }
            else
            {
                $sMsg = ($bDelete ? 'Unzip.php could not be deleted' : 'Unzip.php prevent from deleting');
                $sRetval = \sprintf('<li class="w3-text-red w3-large">Unzip file %s</li>'.PHP_EOL,$sMsg);
                $sError = \sprintf("[%03d] %s\n",__LINE__, $sMsg);
            }
            if ($this->bCanLog===true) {
                \file_put_contents($this->LogPath.$this->LogFile,$sError, \FILE_APPEND);
            }
            return $sRetval;
        }

        public function getAllVars()
        {
            return $this->aConfig;
        }

        // Cross-platform use of the %e placeholder
        protected function getTimeFormat($sFormat)
        {
          $sRetval = $sFormat;
    // Check for Windows to replace the %e placeholder correctly
          if (\strtoupper(\substr(\PHP_OS, 0, 3)) == 'WIN') {
              $format = \preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $sRetval);
          }
          return $sRetval;
        }

        public function showDebug($value)
        {
            ob_start();
            print "<pre>\n";
            print_r( $value );
            print "</pre>"; flush (); //  sleep(10); die();
            return ob_get_clean();
        }

        protected function isWrongArchive($sFilename)
        {
            $mList  = null;
            $sError = '';
            $aError = [
                  'numFiles' => 0,
                  'files'    => 0,
                  'folders'  => 0,
                  'error'    => '',
            ];
            try {
                $this->oZip = new \ZipArchive();
                if ($this->oZip->open(\realpath($sFilename)))
                {
                    $aError['numFiles'] = $this->oZip->count();
                    for ($i=0; $i < $this->oZip->numFiles;$i++) {
                        $aItem = $this->oZip->statIndex($i);
                        $sItem = $aItem['name'];
                        $sDestination = $this->AppPath.$sItem;
                        if ($sItem == 'info.php'){
                            if ($oZip->getFromIndex($i)===false){
                                if (is_writeable($sDestination)) {
                                  \rename($sDestination,$sDestination.'.copy');
                                }
                                $aError['files']++;
                            } else {
                              $sData = (($this->oZip->getFromIndex($i)) ?: 'Error loading Content from '.$sItem);
                              $module_name   = get_variable_content ('module_name', $sData);
                              $template_name = get_variable_content ('template_name', $sData);
                              $mList = (isset($module_name) ? $module_name : (isset($template_name) ? $template_name : null));
                            }
                            break;
                        }
                    }
                }
            } catch(\ErrorException $ex){
                /* place to insert different error/logfile messages */
                $sError = $aError['error'] = '$sContent = '.$ex->getMessage();
                $sFile = str_replace($this->AppPath, '',$sItem);
                //$sError = \sprintf('[%03d] File %s could not be processed:',__LINE__, $sFile);
                if ($this->bCanLog===true) {
                    \file_put_contents($this->LogPath.$this->LogFile,$sError, \FILE_APPEND);
                }
            }
            $this->oZip->close();
            return $mList;
        }

        protected function extractTo($pathto, $files = null)
        {
            return $this->oZip->extractTo($pathto, $files);
        }

        protected function unzipArchive ($sFilename='', $bUnzipEmptyFolders = true, $bUnzipEmptyFiles = true)
        {
            $sError = '';
            $aError = [
                  'numFiles' => 0,
                  'files'    => 0,
                  'folders'  => 0,
                  'error'    => '',
            ];
            $aUnzip = [
                  'numFiles' => 0,
                  'file'     => 0,
                  'folder'   => 0,
                  'error'    => '',
            ];
            try {
                $this->aConfig['oZip'] = new \ZipArchive();
                if ($this->oZip->open(\realpath($sFilename)))
                {
                    $aError['numFiles'] = $this->oZip->count();
                    $aUnzip['numFiles'] = $aError['numFiles'];
                    $this->extractTo($this->AppPath);

                    for ($i=0; $i < $this->oZip->numFiles;$i++)
                    {
                        $aItem = $this->oZip->statIndex($i);

                        $sItem = $aItem['name'];
                        $sDestination = $this->AppPath.$sItem;
                        $isDir = ((\substr($sItem, -1) === '/') ? true : false);
                        if ($isDir)
                        {
                //echo \nl2br(\sprintf("---+> <b>[%04d]</b> touch(%s) <b>%s</b> \n",__LINE__,$sDestination,date('Y-m-d H:i:s',$aItem['mtime']))); //
       //                  Returns the entry contents using its index
                          // backup from file
                            if (($this->oZip->getFromIndex($i) === false))
                            {
                                if (is_writeable($sDestination))
                                {
                                    \copy($sDestination,$sDestination.'.copy');
                                }
                                $aError['files']++;
                            }
                            else
                            {
                                if (is_writable($this->AppPath.$sItem)){
                                  \touch($this->AppPath.$sItem,$aItem['mtime']);
                                  $sFile = str_replace($this->AppPath, '',$sItem);
                                  ++$aUnzip['folder'];
                                  $sError = \sprintf("[%03d] Folder %s set MTime to %s successfully processed:\n",__LINE__, $sFile,date('Y-m-d H:i:s e',$aItem['mtime']));
                                }
                                else
                                {
                                    $sFile = str_replace($this->AppPath, '',$sItem);
                                    $sError = \sprintf("[%03d] Folder %s could not be processed:\n",__LINE__, $sFile);
                                }
                                if ($this->bCanLog===true)
                                {
                                    \file_put_contents($this->LogPath.$this->LogFile,$sError, \FILE_APPEND);
                                }
                            }
                        }//isDir
                        else
                        {
                            ++$aUnzip['file'];
                        }
                   }//for numFiles
            }
        } catch(\ErrorException $ex){
            /* place to insert different error/logfile messages */
            $aError['error'] = '$scontent = '.$ex->getMessage();
            $aUnzip['error'] = $aError['error'];
        }
        $this->oZip->close();
        return $aUnzip;
        }

        public function ExtractArchive($bEmptyFolders=false, $bEmptyFiles=false)
        {
          $sRetval = '';
    // Search and load the first zip archive
            if (is_string($this->ArchiveFile) )
            {
                $aArchiveFiles = [$this->ArchiveFile];
            }
            if (isset($this->ArchiveFile) && (!empty($aArchiveFiles)))
            {
                foreach($aArchiveFiles as $sArchiveFile)
                {
                    if (is_file($this->AppPath.$sArchiveFile))
                    {
                        \chdir($this->AppPath);
    // Extract the archive
                        $mList = $this->isWrongArchive($this->AppPath.$sArchiveFile);
                        if (is_null($mList))
                       {
                            $aUnzip = $this->unzipArchive($this->AppPath.$sArchiveFile, $bEmptyFolders, $bEmptyFiles);
                            if (empty($aUnzip['error'])){
                                $sContent = sprintf('%d files and %d folders (%d total) from %s were successfully unpacked',$aUnzip['file'],$aUnzip['folder'],$aUnzip['numFiles'], $sArchiveFile);
                                $sRetval .= \sprintf('<li class="w3-text-green w3-large"> %s'.PHP_EOL,$sContent);
                                $sMsg     = sprintf("[%03d] %s",__LINE__,$sContent);
                            }
                            else
                            {
                                $sContent = \sprintf('Errors %d have occurred:', $aUnzip['error']);
                                $sRetval .= \sprintf('<li class="w3-text-red w3-large">Errors %d have occurred:'.PHP_EOL,$sContent);
                                $sMsg     = sprintf("[%03d] %s",__LINE__,$sContent);
                                //file_put_contents($$this->LogPath.$this->LogFile,$sMsg, \FILE_APPEND);
                            }
                            if ($this->bCanLog===true) {
                                \file_put_contents($this->LogPath.$this->LogFile,$sMsg, \FILE_APPEND);
                            }

    /*
    // Set permission rights './'
                        $sRetval .= '<ul class="w3-ul">'.PHP_EOL;
                        $permission_rights = fileList($sAppPath, $sArchiveFile,0,!empty($aUnzip['error']));
                        if (empty($permission_rights)){
                            $sRetval .= \sprintf('<li class="w3-text-green w3-large">File and directory rights completely set</li>'.PHP_EOL);
                        }else{
                            $sRetval .= \sprintf('<li class="w3-text-red w3-large">Errors have occurred::%s</li>'.PHP_EOL,$permission_rights);
                        }
                        $sRetval .= '</ul>'.PHP_EOL;
    */

    /* Delete zip archive */
                            $sRetval .= $this->deleteArchiveFile($this->bDeleteArchive,$this->ArchiveFile);
                            $sRetval .= $this->deleteUnzip($this->bDeleteUnzip);
    //              $sRetval .= '</li>'.PHP_EOL;
                        } // end of isWrongArchive
                        else
                        {
                            $sContent = \sprintf("[%03d] Can\' unzip %s package, please extract Archive and upload per FTP\n",__LINE__,$mList);
                            $sRetval .= \sprintf('<li class="w3-text-red w3-large">%s</li>'."\n",$sContent );
                            if ($this->bCanLog===true) {
                                \file_put_contents($this->LogPath.$this->LogFile,$sContent, \FILE_APPEND);
                            }
                        }
                    }
                } // foreach Archivfile
            } else {
                $sContent = sprintf("[%03d] No existing archivefile found in AppPath %s \n",__LINE__,$this->AppRel);
                $sRetval .=  \sprintf('<li class="w3-text-red w3-large">%s </li>'.PHP_EOL, $sContent);
                if ($this->bCanLog===true) {
                    \file_put_contents($this->LogPath.$this->LogFile,$sContent, \FILE_APPEND);
                }
            }
    //    }
        return $sRetval;
        } // end of function ExtractArchive


    // Set correct permission rights - folder 0755, files 0644
        public function fileList($startdir = './', $file = false, $bError = false)
        {
            static $error = '';
            $ignoredDirectory = ['.', '..', 'unzip.php'];
            if (!empty($file)){
                $ignoredDirectory[] = $file;
            }
            $string_file_mode = '0644';
            \defined('OCTAL_FILE_MODE') ? : \define('OCTAL_FILE_MODE', (int) \octdec($string_file_mode));
            $string_dir_mode = '0755';
            \defined('OCTAL_DIR_MODE')  ? : \define('OCTAL_DIR_MODE',  (int) \octdec($string_dir_mode));

            if (\is_dir($startdir)){
                if ($dh = \opendir($startdir)){
                    while(($file = \readdir($dh)) !== false)
                    {
                        if (!(\array_search($file, $ignoredDirectory) > -1)){
                            if (\is_dir($startdir.$file.'/')){
                                $bError = fileList($startdir.$file.'/', 0, $bError);
                            }
                            $filetype = \filetype($startdir.$file);
                            if (($filetype == 'dir')){
                                if ((!$bError && (\chmod($startdir.$file, OCTAL_DIR_MODE) == false))){
                                    $error .= 'Directory rights could not be set: '.$startdir.$file.'<br />';
                                }
                            }elseif (($filetype == 'file')){
                                if ((!$bError && (\chmod($startdir.$file, OCTAL_FILE_MODE) == false))){
                                    $error .= 'File rights could not be set: '.$startdir.$file.'<br />';
                                }
                            }
                        }
                    }
                    \closedir($dh);
                }
            }
            return ($error);
        }

    }// end of class Zipper


