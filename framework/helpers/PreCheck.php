<?php

declare(strict_types=1);

namespace bin\helpers;

use bin\{WbAdaptor,SecureTokens,Sanitize};

use Moment\Moment;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFilter;

class PreCheck
{

    private static $missing;
    private static $iMemory_Limit = -1;
    private static $iMaxMemoryLimit ='360';

    public static function xnl2br ($mText)
    {
      $sRetval = '';
//    build array width searches
      if (!\is_array($mText)){
          $aResult = \preg_split('/\R/', $mText, -1, PREG_SPLIT_DELIM_CAPTURE);
      } else {
          $aResult = $mText;
      }
      $pattern = '/[<^\/\w]+[>]|[<^\/\w]+[>].*<[\/\w>]+/is';
      foreach($aResult as $sResult) {
          if (empty($sResult)){continue;}
          $sResult .= "\n";
          if (\preg_match($pattern,$sResult)){
            $sRetval .= $sResult;
            continue;
          }
        $sRetval .= \preg_replace('/[\n\r]/u', '',\nl2br($sResult, !\defined('XHTML')));
      }
      return $sRetval;
    }


      public static function convertToByte ($iniSet='memory_limit')
      {
          $aMatch = [];
          $iMemoryLimit = (ini_get($iniSet));
          if ((int)$iMemoryLimit !== -1)
          {
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
          return $iMemoryLimit;
      }

      public static function human_filesize($size, $precision = 2){
          $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
          $step = 1024;
          $i = 0;
          while (($size / $step) > 0.9) {
             $size = $size / $step;
             $i++;
          }
          return round($size, $precision).$units[$i];
      }

      public static function convertByteToUnit ($size, $roundup = 5, $decimals = 2)
      {
          $sRetval = "0K";
          $aFilesizeUnits = ["", "K", "M", "G", "T", "P", "E", "Z", "Y"];
          $addition = ((($roundup > 0) && ($decimals == 0)) ? 0.45 : 0);
          $sRetval = (($size > 0) ? \round($size / pow(1024,($i = \floor(log($size, 1024))))+$addition, $decimals).$aFilesizeUnits[$i] : $sRetval);
          return $sRetval;
      }

    public static function setMaxExecutionTime(string $iMemoryLimit='360'):void
    {
        self::$iMaxMemoryLimit = ini_get('max_execution_time');
        if ((int)self::$iMaxMemoryLimit < (int)$iMemoryLimit){
          \ini_set('max_execution_time', $iMemoryLimit);
        }
    }

    public static function resetMaxExecutionTime():void
    {
          \ini_set('max_execution_time', self::$iMaxMemoryLimit);
          \ini_set('memory_limit', self::$iMemory_Limit);
    }

    public static function increaseMemory($sMemoryLimit='256M',string $MaxExecutionTime='600'):void
    {
//        TODO Ssnitize parameter
        $iDefautLimit = 2 * 1024 * 1024 * 1024;
        \ini_set("gd.jpeg_ignore_warning", 'On');
        self::$iMemory_Limit = \ini_get("memory_limit");
        $iMemoryLimit = self::convertToByte("memory_limit");
        if ((int)$iMemoryLimit < (int)$iDefautLimit) {
            \ini_set("memory_limit", $sMemoryLimit);
        }
        self::setMaxExecutionTime($MaxExecutionTime);
    }


/**
 *
 * By default, version_compare() returns
 * -1 if the first version is lower than the second,
 * 0 if they are equal, and
 * 1 if the second is lower.
 *
 * $required['PHP']['operator']
 * $required['PHP']['version']
 *
 * will be continued
 */
    public static function getMissingRequirements(array $aRequired):string // normally array
    {
          self::$missing = '';
          if (\count($aRequired) && !empty($aRequired['PHP'])){
              $sOperator = empty($aRequired['PHP']['operator']) ? '>=' : $aRequired['PHP']['operator'];
              self::ensureOperatorIsValid($sOperator);
              if (!\version_compare($aRequired['PHP']['version'], $aRequired['PHP']['required'], $sOperator)) {
                $sErrorMessage = \sprintf("%s %s Version is required, installed Version is %s %s.\n",
                          $aRequired['PHP']['Addon'],
                          $aRequired['PHP']['required'],
                          $aRequired['PHP']['Addon'],
                          $aRequired['PHP']['version']);
                self::$missing = $sErrorMessage;
              }
          }
          return self::$missing;
    }

    /**
     *
     */
    private static function ensureOperatorIsValid(string $sOperator):void
    {
          if (!\in_array($sOperator, ['<','lt','<=','le','>','gt','>=','ge','==','=','eq','!=','<>','ne']))
              throw new \Exception(
                \sprintf(
                    '"%s" is not a valid version_compare() operator', $sOperator
                    )
              );
    }

    public static function readFiles ($sPattern=''){
        $aRetVal = null;
        if (!empty($sPattern)){
            $iterator = new \GlobIterator($sPattern);
            while ($iterator->valid()) {
                $sFilename = \str_replace('\\','/',$iterator->current()->getFilename());
                $aRetVal = \nl2br(\sprintf("%s\n",$sFilename));
                $iterator->next();
            }
        }
        return ($aRetVal ?? false);
    }

    public static function deleteFiles($sAddonPath='',array $aFilesToDelete = []):void
    {
        foreach ($aFilesToDelete as $sFilename){
            if (\is_writeable($sAddonPath.$sFilename)) {
                if (\substr($sFilename, -1) == '/'){
                    rm_full_dir($sAddonPath.$sFilename);
                } else {
                    \unlink($sAddonPath.$sFilename);
                }
            }
        } // end foreach
    }

/*
 * @param string $mData: like saved in addons.directory
 *                       or data content with var names
 * @param string $sType: variable from info.php
 * @param string $sAddonType: module or template
 * @return string:  the version as string, if not found returns null
 */
    public static function getAddonVariable ($mData = '', $sType = 'version', $sAddonType='modules'){
        $sRetval = null;
        $oReg = WbAdaptor::getInstance();
        $sAddonName = (in_array($sAddonType,['modules','templates']) ? \basename($mData) : \basename(dirname($mData)));
        $sSourceDir = $oReg->AppPath.''.$sAddonType.'/'.$sAddonName;
        if (\is_dir($sSourceDir)){
            $sInfoFile  = (in_array($sAddonType,['modules','templates']) ? '/info.php' : '/'.\basename($mData));
            $sInfoFileName = \rtrim($sSourceDir,'/').$sInfoFile;
            $aParseDir = \preg_split('/[\s,=+\/\|]+/', $sInfoFileName, -1, PREG_SPLIT_NO_EMPTY);
            if (\is_readable($sInfoFileName)) {
                $sVarName = (\in_array($sAddonType, $aParseDir) ? $sAddonType : 'templates');
                require $sInfoFileName;
                $sAddonVarName = (\rtrim($sVarName, 's/').'_'.$sType);
                $sRetval = ($$sAddonVarName ?? 'xxx');
            }

        }else {
            $match  = [];
            $stripTags         = true;
            $convertToEntities = true;
//          search for $variable followed by 0-n whitespace then by = then by 0-n whitespace
//          then either " or ' then 0-n characters then either " or ' followed by 0-n whitespace and ;
//          the variable name is returned in $match[1], the content in $match[3]
            if (\preg_match('/(\$' .$sType .')\s*=\s*("|\')(.*)\2\s*;/i', $mData, $match))
            {
                if (\strip_tags(\trim($match[1])) == '$' .$sType) {
                    // variable name matches, return it's value
                    $match[3] = ($stripTags == true) ? \strip_tags($match[3]) : $match[3];
                    $match[3] = ($convertToEntities == true) ? \htmlentities($match[3]) : $match[3];
                    $sRetval = $match[3];
                }
            }
        }

        return ($sRetval ?? 'XXX');
    }

/**
 * Read Addon ini file set optional Twig Ini File and return array
 */
    public static function readIniFile($sAddonPath = '', $sBaseFilename='default'){
        $mRetval = null;
        $aDefaultIni = [];
        $sAddonPath =(!empty($sAddonPath) ?  rtrim($sAddonPath,'/') : $sAddonPath);
        if (is_file($sAddonPath.'/'.$sBaseFilename.'User.ini')){
            $mRetval = parse_ini_file($sAddonPath.'/'.$sBaseFilename.'User.ini',true);
        } else if (is_file($sAddonPath.'/'.$sBaseFilename.'.ini')){
            $mRetval = parse_ini_file($sAddonPath.'/'.$sBaseFilename.'.ini',true);
        } else {
            $mRetval = [];
        }
        return ($mRetval ?? []);
    }

/**
 * Read Addon Twig Ini File and return array
 */
    public static function createTwigEnv($sAddonPath = '', $sTemplate = 'overview.twig'){
        $mRetval = self::readIniFile($sAddonPath);
        if (!isset($mRetval['twig-loader-file'])){
            $mRetval['twig-loader-file']['templatesDir'] = $sAddonPath.'/themes/default';
            $mRetval['twig-loader-file']['default_template'] = $sTemplate;
            $mRetval['twig-environment'] = [
                'autoescape'       => false,
                'cache'            => false,
                'strict_variables' => false,
                'debug'            => false,
                'auto_reload'      => true,
            ];
        }
        return ($mRetval ?? ['Twig Ini Load Error']);
    }

/**
 * write ini file
 * @param $assoc_arr
 * @param $path
 * @return bool
 */
      public static function writeIniFile($assoc_arr, $sPathFilename)
      {
          $content = "";
          if (is_readable($sPathFilename)){
              $aIniTmp = file($sPathFilename,\FILE_IGNORE_NEW_LINES);
              //$aTmp = parse_ini_file($sPathFilename,true,INI_SCANNER_TYPED);
              $aTmp = array_chunk($assoc_arr,2);
              foreach ($aIniTmp as $item => $aValue)
              {

// do it later
              }

          } else {

              $header = ";<?php exit(); ?>
;###############################################################################
;###                                                                         ###
;###   configurable settings for Addon                                       ###
;###                                                                         ###
;###############################################################################
";

              foreach ($assoc_arr as $key => $elem) {
                  $content .= ";\n";
                  $content .= "[" . $key . "]\n";
                  foreach ($elem as $key2 => $elem2) {
                      if (is_array($elem2)) {
                          for ($i = 0; $i < count($elem); $i++) {
                              $content .= $key2 . "[] = \"" . $elem2[$i] . "\"\n";
                          }
                      } else if ($elem2 == "") {
                          $content .= $key2 . " = \n";
                      } else {
                          $content .= $key2 . " = \"" . $elem2 . "\"\n";
                      }
                  }//foreach
              }//foreach
              if (!$handle = fopen($sPathFilename, 'w')) {
                  return false;
              }
              if (!fwrite($handle, $header.$content)) {
                  return false;
              }
              fclose($handle);
          }
          return true;
      }

    /*
    * This function copy $source directory and all files
    * and sub directories to $destination folder
    */
    public static function recursiveCopy($src,$dst) {
        $dir = \opendir($src);
        if (!\is_dir($dst)){\mkdir($dst);}
        while(( $file = \readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (\is_dir($src . '/' . $file) ) {
                    self::recursiveCopy($src .'/'. $file, $dst .'/'. $file);
                } elseif (!\is_file($dst.'/'.$file)) {
                    \copy($src .'/'. $file,$dst .'/'. $file);
                }
            }
        }
        \closedir($dir);
    }

    public static function sanitizeFilename($val,$caseSensitive = false,$mPageStyle=null)
    {
        // Liste aller Umlaute
        $map = [
                'ä' => 'ae',
                'Ä' => 'Ae',
                'ß'=>'ss',
                'ö'=>'oe',
                'Ö' =>'Oe',
                'Ü'=>'Ue',
                'ü'=>'ue',
                '<'=>'',
                '>'=>'',
                '"'=>'',
                '\''=>'',
                // hier ggf. weitere Zeichen ergänzen, z.B.
                'à' => 'a',
                'é' => 'e',
                'è' => 'e',
            ];
        // Umlaute konvertieren
        $sRetval = str_replace(array_keys($map), array_values($map), $val);
        // whitespace durch Unterstrich ersetzen
    /*
        $sRetval = preg_replace('#(\s+)#', '_', $val);
        $sRetval = preg_replace('/[^A-Za-z0-9]/', '_', $val);
    */
        $sRetval = preg_replace(
        '~
        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        (\s+)|                        # file system reserved
        [\x00-\x1F\x80-\xFF]|         # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|               # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [\xF0\x9F\x98\x83]|           #
        [#\[\]@!§"$%&\'\?()+,;:=§\/]| # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                      # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
        '-', $sRetval);
//            $sRetval = preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', $sRetval);
        $sPageSpacer = (((\defined(PAGE_SPACER) && (int)empty(PAGE_SPACER)) == 0) ? PAGE_SPACER : '-');  //  trim(PAGE_SPACER)
        $bPageNewStyle = (!\is_null($mPageStyle) ? \filter_var($mPageStyle, \FILTER_VALIDATE_BOOLEAN) : true);
        //sanitize to new format
        if ($bPageNewStyle){
            $sRetval = str_replace('-', ' ', $sRetval);
            $aString= \preg_split('/[\s,=+_\-\;\:\.\|]+/', $sRetval, -1, PREG_SPLIT_NO_EMPTY);
            $sRetval = \implode($sPageSpacer,$aString);
//echo nl2br(sprintf("<div class='w3-border w3-padding w3-margin-left'>[%04d] %s</div>\n",__LINE__,$sRetval));
        } else {
        //  hold the old format
            $sRetval = \preg_replace('/(\s)+/', $sPageSpacer, $sRetval);
//echo nl2br(sprintf("<div class='w3-border w3-padding w3-margin-left'>[%04d] %s</div>\n",__LINE__,$sRetval));
        }
        // alle anderen Zeichen verwerfen
//        $sRetval = preg_replace('#[^a-z0-9_."\']#', '', $sRetval);
        return (($caseSensitive === false) ? strtolower($sRetval) : $sRetval);
    }

    public static function createFillDir($sSoucePath = '', $sTargetPath=''):array
    {
        $oReg = WbAdaptor::getInstance();
        $oApp = $oReg->getApplication();
        $aMsg = [];
        if (is_dir($sSoucePath) && !\is_dir($sTargetPath)){
//            require (WB_PATH.'/framework/functions.php');
            if (!make_dir($sTargetPath)){
                $aMsg[] = sprintf('couldn\'t create %s',basename(dirname($sTargetPath)).'/'.basename($sTargetPath));
            } elseif ((basename($sSoucePath) != basename($sTargetPath))) {
                self::recursiveCopy($sSoucePath,$sTargetPath);
                $aMsg[] = sprintf('copy default to %s',basename(dirname($sTargetPath)).'/'.basename($sTargetPath));
            } else {
            }
        } else {
//            $aMsg[] = sprintf('existing %s',basename(dirname($sTargetPath)).'/'.basename($sTargetPath));
        }
        return $aMsg;
    }

    public static function setLocale($sLocale = 'de_DE')
    {
      Moment::setLocale($sLocale);
    }

    public static function getBrowserLang(
    $sDdateFormat = 'l, dS F Y / H:i ',
    $iTimestamp = 0,
    $strict_mode = true)
    {
        $oReg = WbAdaptor::getInstance();
        $oRequest = $oReg->getRequester();
        // create setlocale strring from language page
        $sLocalDefaultString = $oReg->Language.'_'.\strtoupper($oReg->Language);
        //$loc = \setlocale(LC_TIME,$sLocalString.'.UTF8',$sLocalString);
        $sLocate = $oRequest->getHeader('HTTP_ACCEPT_LANGUAGE');
        if (isset($sLocate)) {
            // Den Header auftrennen
            $aAcceptedLanguages = preg_split('/,\s*/', $sLocate);
            // Die Standardwerte einstellen
            $current_lang = $sLocalDefaultString;
            $current_q = 0;
            // break up string into pieces (languages and q factors)
            // Nun alle mitgegebenen Sprachen abarbeiten
            foreach ($aAcceptedLanguages as $aAcceptedLanguage)
            {
                // Alle Infos über diese Sprache rausholen
                $res = preg_match (
                  '/^([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i',
                  $aAcceptedLanguage,
                  $matches
                );
                // war die Syntax gültig?
                if (!$res) {
                  // Nein? Dann ignorieren
                  continue;
                }
                // Sprachcode holen und dann sofort in die Einzelteile trennen
                $lang_code = explode ('-', $matches[1]);
                // Wurde eine Qualität mitgegeben?
                if (isset($matches[2])) {
                  // die Qualität benutzen
                  $lang_quality = (float)$matches[2];
                } else {
                  // Kompabilitätsmodus: Qualität 1 annehmen
                  $lang_quality = 1.0;
                }
                // Bis der Sprachcode leer ist...
                while (count ($lang_code))
                {
                    // mal sehen, ob der Sprachcode angeboten wird
                    if (in_array (strtolower (join ('-', $lang_code)), $aAcceptedLanguages)) {
                      // Qualität anschauen
                      if ($lang_quality > $current_q) {
                        // diese Sprache verwenden
                        $current_lang = strtolower (join ('-', $lang_code));
                        $current_q = $lang_quality;
                        // Hier die innere while-Schleife verlassen
                        break;
                      }
                    }
                    // Wenn wir im strengen Modus sind, die Sprache nicht versuchen zu minimalisieren
                    if ($strict_mode) {
                      // innere While-Schleife aufbrechen
                      break;
                    }
                    // den rechtesten Teil des Sprachcodes abschneiden
                    array_pop ($lang_code);
                } // end while
            } // end foreach
        } else {
              $lang_code = $sLocalDefaultString;
        }
        return $lang_code;
/*
        $bFindSimilar= (strlen($current_lang) < 5);
    // set  locale
        \Moment\Moment::setLocale($current_lang,$bFindSimilar);
        $m = new \Moment\Moment('now'); //, 'CET'
        return  $m->format($sDdateFormat);
*/
    }
    public static function dateFormatToStrftime(
        $sDdateFormat = null,
        $iUnixTime = null,
        $current_lang = null
        )
        {
        return self::getStrftime($sDdateFormat,$iUnixTime,$current_lang);
    }

    public static function getStrftime(
        $sDateFormat  = null,
        $iUnixTime    = null,
        $sCurrentLang = null
    ) {
//echo \nl2br(\sprintf("---- [%04d] %s %s \n",__LINE__,$sDateFormat,$iUnixTime));
       // set defaults if needed
        $iTimestamp   = $iUnixTime ?? \time();
        $sDateFormat  = ($sDateFormat ?? WbAdaptor::getInstance()->DateFormat.' '.WbAdaptor::getInstance()->TimeFormat);
        $sCurrentLang = \strtolower(($sCurrentLang ?: ($sCurrentLang ?: WbAdaptor::getInstance()->Language)));
        $bFindSimilar = (strlen($sCurrentLang) < 5);
//      set locale
        Moment::setLocale($sCurrentLang,$bFindSimilar);
//      //Moment::setDefaultTimezone('Europe/Berlin');// 'CET'
        $m = new Moment($iTimestamp,'UTC'); // ,'CET',true

        $sRetval = $m->format($sDateFormat);
//echo \nl2br(\sprintf("---- [%04d] %s %s \n",__LINE__,$sDateFormat,$sRetval));
        return str_replace('|', ' ',$sRetval);
    }
    /**
     * scanDirTree()
     *
     * @param string $sAbsPath
     * @param string $regPattern
     * @return
     */
    public static function _scanDirTreeIterator(
                  $sAbsPath,
                  $regPattern='/^.+\.(.*)?$/i'
    ){
        $Directory  = new \RecursiveDirectoryIterator(
                          $sAbsPath,
                          \FilesystemIterator::CURRENT_AS_SELF|
                          \FilesystemIterator::FOLLOW_SYMLINKS|
                          \FilesystemIterator::KEY_AS_PATHNAME|
                          \FilesystemIterator::SKIP_DOTS|
                          \FilesystemIterator::UNIX_PATHS
                      );
        $Iterator   = new \RecursiveIteratorIterator(
                          $Directory,
                          \RecursiveIteratorIterator::SELF_FIRST,
                          \RecursiveIteratorIterator::CATCH_GET_CHILD
                      );
/*
        $regexIterator = new \RegexIterator(
                          $Iterator, $regPattern,
                          \RecursiveRegexIterator::GET_MATCH
                        );
*/
        return $Iterator;
    }



    public static function scanDirTreeIterator(
                $sAbsPath,
                $regPattern='^.+(.*)?$'
                ) {
        $aFiles = [];
        $sAbsDir = (str_replace(['\\','//',\DIRECTORY_SEPARATOR],'/',$sAbsPath));
//echo nl2br(sprintf("---- [%03d] %s \n",__LINE__,$sAbsPath));
        $Directory  = new \RecursiveDirectoryIterator(
                          $sAbsDir,
                          \FilesystemIterator::CURRENT_AS_SELF|
                          \FilesystemIterator::FOLLOW_SYMLINKS|
                          \FilesystemIterator::KEY_AS_PATHNAME|
                          \FilesystemIterator::KEY_AS_FILENAME|
                          \FilesystemIterator::SKIP_DOTS|
                          \FilesystemIterator::UNIX_PATHS
                      );
        $Iterator = new \RecursiveIteratorIterator(
                          $Directory,
                          \RecursiveIteratorIterator::SELF_FIRST,
                          \RecursiveIteratorIterator::CATCH_GET_CHILD
                        );
//        $sSearchPattern = '/^.+\.('.$regPattern.')?$/is';
//        $sPattern = '/.*?[\/\\\\]('.$regPattern.')[\/\\\\]?/is';
//        $sPattern = "/^(.*?\/)".$regPattern."\/.*$/i";
        $sPattern = "/".$regPattern."/i";
/**/
        $regexIterator = new \RegexIterator(
                          $Iterator, $sPattern,
                          \RecursiveRegexIterator::GET_MATCH
                        );

        foreach($Iterator as $aFileInfo){
            $isDir = is_dir($aFileInfo->getPathname());
            $foundPattern = (preg_match($sPattern,$aFileInfo->getPathname(), $aMatches));
            if ($foundPattern){
//echo nl2br(sprintf("---- [%03d] %s \n",__LINE__,$aFileInfo->getPathname()));
                if ($isDir ){
                    $aFiles['folder'][] = $aFileInfo->getPathname().'/';
                } else {
                    $item = $aFileInfo->getPathname();
                    $aFiles['file'][] = $item;
                }
            }
        }
        return $aFiles; // $Iterator
    }

    public static function scanFolder ($directory){
      return array_values(array_diff(scandir($directory), ['..', '.']));
//      $files = array_filter(scandir($directory), function($file) { return is_file($file); })
    }

    public static function getFilename($subject){
      $aMatches = [];
      $pattern = '#^(?:.*?[\/])?([^\/]*?)\.([^\.]*)$#isU';
      $result = preg_match($pattern, $subject, $aMatches);
      return $aMatches['1'];
    }

    public static function getExtension($sFilename){
      return \preg_replace('/.*?(\.[a-z][a-z0-9]+)$/siU', '\1', $sFilename);
    }

    public static function find_string_in_array ($arr, $string) {

        return array_filter($arr, function($value) use ($string) {
            return strpos($value, $string) !== false;
        });
    }

// remove languages file extension
    public static function removeExtension ($sFilename){
//        return \preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', $sFilename);
        return \preg_replace("/^.*?([^\/]*?)\.[^\.]*$/iu", "$1", $sFilename);
    }

// Function to create directories
    public static function makeDir($sAbsPath, $iDirMode = OCTAL_DIR_MODE, $bRecursive=true)
    {
        $bRetVal = \is_dir($sAbsPath) && \is_readable($sAbsPath);
        $iOldUmask = \umask(0);
        if (!file_exists($sAbsPath)) {
            $bRetVal = \mkdir($sAbsPath, $iDirMode,$bRecursive);
//        echo sprintf('li class="w3-margin-left w3-text-green">is making dir \%s\</li>', ($sAbsPath));
        }
//        echo sprintf('<li class="w3-margin-left w3-text-red">existing dir \%s\</li>', ($sAbsPath));
        \umask($iOldUmask);
//        echo $sMsg = sprintf('%s %s<br />',$sItem,date('Y-m-d H:i',self::oStat->mtime));
        return $bRetVal;
    }

/**
 * deletes the given directory and all it's subdirectories (like DelTree)
 * @param string $sBasedir  full path of the folder to delete
 * @param bool $bPreserveBaseFolder shall the basedir be deleted (default: false)
 * @return bool
 */
    public static function rm_full_dir($sBasedir, $bPreserveBaseFolder = false)
    {
        $sPath = \rtrim($sBasedir, '\\\\/').'/';
        $bRetval = true;
        // find all nodes which names start with [a-zA-Z0-9_~@] and leading '.' too.
        // it ignores the '.' and '..' nodes from Linux
        if (($aHits = self::scanDirTree($sPath)) !== false) {
            foreach ($aHits as $sItem) {
                if (\substr(self::strReplace($sItem), -1) === '/') {
                    $bRetval = rm_full_dir($sItem, false);
                } else {
                   $bRetval = \unlink($sItem);
                }
                if (!$bRetval) { break; }
            } // end foreach
            if (!$bPreserveBaseFolder && $bRetval) { $bRetval = \rmdir($sPath); }
        }
        return $bRetval;
    }

    public static function createNumArray (array $aValue) {
      $aRetval = [];
      $iSizeof = \sizeof($aValue)/2;
      for ($i=0; $i<=$iSizeof; $i++) {$aRetval[]=$i;}
      return $aRetval;
    }

    public static function createAssocArray(array $aValue){
      return \array_diff_key($aValue, self::createNumArray($aValue));
    }

    public static function convertToArray ($mList)
    {
        $aRetval = $mList;
        if (!\is_array($mList)){
            $aRetval = \preg_split('/[\s,=+\;\:\/\.\|]+/', $mList, -1, \PREG_SPLIT_NO_EMPTY);
        }
        return $aRetval;
    }


    public static function scanDirTree ($sPath, $sPattern = '[a-zA-Z0-9_~@]*'){
        $aRetval = []; //
        if (\defined('GLOB_BRACE')) {
            $aRetval = (\glob($sPath.$sPattern, \GLOB_BRACE|\GLOB_MARK|\GLOB_NOSORT));  // \GLOB_ONLYDIR|
        } else {
            $aRetval = (\glob($sPath.$sPattern, \GLOB_MARK|\GLOB_NOSORT));  // \GLOB_BRACE|\GLOB_ONLYDIR|
        }
        return $aRetval;
    }

/**
 * Convert date/time format between `date()` and `strftime()`
 *
 * Timezone conversion is done for Unix. Windows users must exchange %z and %Z.
 *
 * Unsupported date formats : S, n, t, L, B, G, u, e, I, P, Z, c, r
 * Unsupported strftime formats : %U, %W, %C, %g, %r, %R, %T, %X, %c, %D, %F, %x
 *
 * @example Convert `%A, %B %e, %Y, %l:%M %P` to `l, F j, Y, g:i a`, and vice versa for "Saturday, March 10, 2001, 5:16 pm"
 * @link http://php.net/manual/en/function.strftime.php#96424
 *
 * @param string $format The format to parse.
 * @param string $syntax The format's syntax. Either 'strf' for `strtime()` or 'date' for `date()`.
 * @return bool|string Returns a string formatted according $syntax using the given $format or `false`.
 */
   public static function date_format_to( $format, $syntax )
    {
        // http://php.net/manual/en/function.strftime.php
        $strf_syntax = [
            // Day - no strf eq : S (created one called %O)
            '%O', '%d', '%a', '%e', '%A', '%u', '%w', '%j',
            // Week - no date eq : %U, %W
            '%V',
            // Month - no strf eq : n, t
            '%B', '%m', '%b', '%-m',
            // Year - no strf eq : L; no date eq : %C, %g
            '%G', '%Y', '%y',
            // Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
            '%P', '%p', '%l', '%I', '%H', '%M', '%S',
            // Timezone - no strf eq : e, I, P, Z
            '%z', '%Z',
            // Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x
            '%s'
        ];

        // http://php.net/manual/en/function.date.php
        $date_syntax = [
            'S', 'd', 'D', 'j', 'l', 'N', 'w', 'z',
            'W',
            'F', 'm', 'M', 'n',
            'o', 'Y', 'y',
            'a', 'A', 'g', 'h', 'H', 'i', 's',
            'O', 'T',
            'U'
        ];

        switch ( $syntax ) {
            case 'date':
                $from = $strf_syntax;
                $to   = $date_syntax;
                break;

            case 'strf':
                $from = $date_syntax;
                $to   = $strf_syntax;
                break;

            default:
                return false;
        }

        $pattern = array_map(
          function ( $s ) {
            return '/(?<!\\\\|\%)' . $s . '/';
          },
          $from
        );

        return preg_replace( $pattern, $to, $format );
    }

/**
 * Equivalent to `date_format_to( $format, 'date' )`
 *
 * @param string $strf_format A `strftime()` date/time format
 * @return string
 */
    public static function strftime_format_to_date_format( $strf_format )
    {
        return date_format_to( $strf_format, 'date' );
    }

/**
 * Equivalent to `convert_datetime_format_to( $format, 'strf' )`
 *
 * @param string $date_format A `date()` date/time format
 * @return string
 */
    public static function date_format_to_strftime_format( $date_format )
    {
        return date_format_to( $date_format, 'strf' );
    }

} // end of class

/**
 * Example
$required['PHP']['operator'] = '';
$required['PHP']['version']  = '7.4';

print '<pre  class="mod-pre rounded">function <span>'.__FUNCTION__.'( '.''.' );</span>  filename: <span>'.\basename(__FILE__).'</span>  line: '.__LINE__.' -> <br />';
\print_r( PreCheck::getMissingRequirements($required) ); print '</pre>'.PHP_EOL; \flush ();   ob_flush();;sleep(10); die();
 */


/**
                preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $sLocate, $aLangParse);
                if (count($aLangParse[1])) {
                    // create a list like "en" => 0.8
                    $aLangs = array_combine($aLangParse[1], $aLangParse[4]);
                    // set default to 1 for any without q factor
                    foreach ($aLangs as $lang => $val) {
                        if ($val === '') {$aLangs[$lang] = 1;}
                    }
                    // sort list based on value
                    arsort($aLangs, SORT_NUMERIC);
                }
            }
            //$sLocate = 'it_IT';
            foreach ($aLangs as $lang => $value){}
*/

