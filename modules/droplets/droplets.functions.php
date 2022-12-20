<?php
/**
 *
 * @category        module
 * @package         droplet
 * @author          Ruud Eisinga (Ruud) John (PCWacht)
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: droplets.functions.php 337 2019-04-23 19:57:56Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/droplets/droplets.functions.php $
 * @lastmodified    $Date: 2019-04-23 21:57:56 +0200 (Di, 23. Apr 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};

/* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

function prepareDropletToFile($aDroplet) {
    $retVal = '';
    $aComment =  [];
    $sDescription = '//:'.(($aDroplet['description']!='') ? $aDroplet['description']: 'Add a desription');
    $sComments = '';
    $aComment = \explode("\n", $aDroplet['comments']);
    if( (\sizeof($aComment)) ){
        foreach($aComment as $isComments) {
          if (\trim($isComments) !='') {
              $sComments .= '//:'.$isComments."\n";
          }
        }
    }
    if( !$sComments ){
        $sComments .= '//:use [['.$aDroplet['name'].']]'."\n";
    }
    $sCode = '';
    $aCode = \explode("\n",$aDroplet['code']);
    if ((\sizeof($aCode)) ){
        foreach($aCode as $isCode) {
          if( $isCode!='') {
                $sCode .= $isCode."\n";
          }
        }
    }
    $retVal = "<?php\n".$sDescription."\n".$sComments.\rtrim($sCode,"\n");
    return $retVal;
}

function backupDropletFromDatabase( $sTmpDir, $FilesInDB='*', $oDb=null) {
    $retVal = [];
    $sDescription = '';
    $FilesInDB = \rtrim($FilesInDB, ',');
    $sqlWhere = ( ($FilesInDB=='*') ? '': 'WHERE `name` IN ('.$FilesInDB.') ');
    $sql = 'SELECT `name`,`description`,`comments`,`code`  FROM `'.TABLE_PREFIX.'mod_droplets` '
         . $sqlWhere
         . 'ORDER BY `modified_when` DESC';
    if( $oRes = $oDb->query($sql) ) {
        while ($aDroplet = $oRes->fetchRow(MYSQLI_ASSOC)) {
            $sData = prepareDropletToFile($aDroplet);
            $sFileName = $sTmpDir.$aDroplet['name'].'.php';
            if (\file_put_contents($sFileName,$sData)) {
                $sDescription = ($aDroplet['description']);
                $retVal[] = [
                                PCLZIP_ATT_FILE_NAME => $sFileName,
                                PCLZIP_ATT_FILE_COMMENT => $sDescription
                           ];
            }
        }
    }
    return $retVal;
}

    function getUniqueName($oDb, $sName)
    {
        $sBaseName = \preg_replace('/^(.*?)(\_[0-9]+)?$/', '$1', $sName);
        $sql = 'SELECT `name` FROM `'.TABLE_PREFIX.'mod_droplets` '
             . 'WHERE `name` RLIKE \'^'.$sBaseName.'(\_[0-9]+)?$\' '
             . 'ORDER BY `name` DESC';
        if (($sMaxName = $oDb->get_one($sql))) {
            $iCount = \intval(\preg_replace('/^'.$sBaseName.'\_([0-9]+)$/', '$1', $sMaxName));
            $sName = $sBaseName.\sprintf('_%03d', ++$iCount);
        }
        return $sName;
    }
/**
 * importDropletToDB()
 *
 * @param mixed $aDroplet
 * @param mixed $msg
 * @param mixed $bOverwriteDroplets
 * @return
 */
function insertDroplet( array $aDroplet, $oDb, $oApp, $bUpdateDroplets = false )
{
        $sImportDroplets = '';
        $extraSql = '';
        $sPattern = "#//:#im";
        $sDropletFile = $aDroplet['name'];
        $sDropletFile = \preg_replace('/^\xEF\xBB\xBF/', '', $sDropletFile);
        $sDropletName = \pathinfo ($sDropletFile, \PATHINFO_FILENAME);
        // get right $aFileData a) from Zip or b) from File
        if (isset($aDroplet['content']) ) {
            $aFileData = $aDroplet['content'];
            $sFileData = $aFileData[0];
            $bRetval  = (bool)\preg_match_all($sPattern, $sFileData, $matches, \PREG_SET_ORDER);
            if ($bRetval == false) { return $bRetval; }
        }
        if (isset($aDroplet['output']) ) {$aFileData = \file($sDropletFile, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);}
        // prepare table mod_droplets fields
        if (\sizeof($aFileData) > 0 ) {
                // get description, comments and oode
                $bDescription = false;
                $bComments = false;
                $bCode = false;
                $sDescription = '';
                $sComments = '';
                $sCode = '';
                $aForbiddenTags = ['<?php' , '<?', '<?=', '?>'];
                while (\sizeof($aFileData) > 0 ) {
                    $sSqlLine = (\array_shift($aFileData));
                    $sSqlLine = \str_replace($aForbiddenTags, '', $sSqlLine);
                    $isNotCode = (bool)\preg_match($sPattern, $sSqlLine);
                    if ($isNotCode==true ) {
// first step line is description
                        if($bDescription==false) {
                            $sDescription .= \str_replace('//:','',$sSqlLine);
                            $bDescription = true;
                        } else {
// second step fill comments
                            $sComments .= (\str_replace('//:','',$sSqlLine))."\n";
                        }
                    } else {
// third step fill code
                        $sCode .= \str_replace('//:','',$sSqlLine)."\n";
                    }
                }
        }
        // TODO future set parameter to class RawDropletInterface
        $sql = 'SELECT `name` FROM `'.TABLE_PREFIX.'mod_droplets` '
             . 'WHERE `name` LIKE \''.\addcslashes($oDb->escapeString($sDropletName), '%_').'\' ';
        if (!( $sTmpName = $oDb->get_one($sql)) )
        {
            $sql = 'INSERT INTO `'.TABLE_PREFIX.'mod_droplets` ';
            $extraSql = '';
            $sImportDroplets = $sDropletName ;
        } elseif ($bUpdateDroplets) {
            $sDropletName = $sTmpName;
            $sql = 'UPDATE `'.TABLE_PREFIX.'mod_droplets` ';
            $extraSql = 'WHERE `name` = \''.\addcslashes($oDb->escapeString($sDropletName), '%_').'\' ';
            $sImportDroplets = $sDropletName;
        }
        if (!isset($sTmpName) || $bUpdateDroplets) {
          $iModifiedWhen = \time();
          $iModifiedBy = (\method_exists($oApp, 'getUserId') && ($oApp->getUserId()!=null) ? $oApp->getUserId() : 1);
          $sql .= 'SET  `name` =\''.$oDb->escapeString($sDropletName).'\', '
               .       '`description` =\''.$oDb->escapeString($sDescription).'\', '
               .       '`comments` =\''.$oDb->escapeString($sComments).'\', '
               .       '`code` =\''.$oDb->escapeString($sCode).'\', '
               .       '`modified_when` = '.$iModifiedWhen.', '
               .       '`modified_by` = '.$iModifiedBy.', '
               .       '`active` = 1 '
               .       $extraSql;
      }
      if ($oDb->query($sql) ) {
      } else {
          $sImportDroplets = '';
          $sError = sprintf('%s',$oDb->get_error());
          \trigger_error($sError, E_USER_NOTICE);
      }

    return ($sImportDroplets != '') ? $sImportDroplets : false;
}

function insertDropletFile(array $aDropletFiles, $oDb, $oApp, &$msg,$bOverwriteDroplets)
{
    $oReg = WbAdaptor::getInstance();
//    $oApp = new admin ('##skip##');
    $OK   = ' <span style="color:#006400; font-weight:bold;">OK</span> ';
    $FAIL = ' <span style="color:#ff0000; font-weight:bold;">FAILED</span> ';
    foreach ($aDropletFiles as $sDropletFile) {
        $msgSql = '';
        $extraSql = '';
        $sDropletName = \pathinfo ($sDropletFile, PATHINFO_FILENAME);
        $sql = 'SELECT `name` FROM `'.TABLE_PREFIX.'mod_droplets` '
             . 'WHERE `name` LIKE \''.\addcslashes($oDb->escapeString($sDropletName), '%_').'\' ';
        if (!( $sTmpName = $oDb->get_one($sql)) )
        {
            $sql = 'INSERT INTO `'.TABLE_PREFIX.'mod_droplets` ';
            $msgSql = 'INSERT Droplet `'.$oDb->escapeString($sDropletName).'` INTO`'.TABLE_PREFIX.'mod_droplets`'." $OK";
        } elseif ($bOverwriteDroplets){
            $sDropletName = $sTmpName;
            $sql = 'UPDATE `'.TABLE_PREFIX.'mod_droplets` ';
            $extraSql = 'WHERE `name` = \''.\addcslashes($oDb->escapeString($sDropletName), '%_').'\' ';
            $msgSql   = 'UPDATE Droplet `'.$sDropletName.'` INTO`'.TABLE_PREFIX.'mod_droplets`'." $OK";
        }
// get description, comments and oode
        $sDropletFile = \preg_replace('/^\xEF\xBB\xBF/', '', $sDropletFile);
        if (($msgSql!='') && ($aFileData = \file($sDropletFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))){
                $bDescription = false;
                $bComments = false;
                $bCode = false;
                $sDescription = '';
                $sComments = '';
                $sCode = '';
                $sPattern = "#//:#im";
                $aForbiddenTags = ['<?php' , '<?', '<?=', '?>'];
                while (\sizeof($aFileData) > 0 ) {
                    $sSqlLine = (\array_shift($aFileData));
                    $sSqlLine = \str_replace($aForbiddenTags, '', $sSqlLine);
                    $isNotCode = (bool)\preg_match($sPattern, $sSqlLine);
                    if ($isNotCode==true ) {
// first step line is description
                        if($bDescription==false) {
                            $sDescription .= \str_replace('//:','',$sSqlLine);
                            $bDescription = true;
                        } else {
// second step fill comments
                            $sComments .= \str_replace('//:','',$sSqlLine)."\n";
                        }
                    } else {
// third step fill code
                        $sCode .= \str_replace('//:','',$sSqlLine)."\n";
                    }
                }
            $iModifiedWhen = \time();
            $iModifiedBy = (\method_exists($oApp, 'getUserId') && ($oApp->getUserId()!=null) ? $oApp->getUserId() : 1);
            $sql .= 'SET  `name` =\''.$oDb->escapeString($sDropletName).'\', '
                 .       '`description` =\''.$oDb->escapeString($sDescription).'\', '
                 .       '`comments` =\''.$oDb->escapeString($sComments).'\', '
                 .       '`code` =\''.$oDb->escapeString($sCode).'\', '
                 .       '`modified_when` = '.$iModifiedWhen.', '
                 .       '`modified_by` = '.$iModifiedBy.', '
                 .       '`active` = 1 '
                 .       $extraSql;
        }
        if( $oDb->query($sql) ) {
            if ($msgSql!='' ) { $msg[] = $msgSql; }
        } else {
            $msg[] = $oDb->get_error();
        }
    }
    return $msg;
}
/* -------------------------------------------------------- */

function isDropletFile($sFileName)
{
    $matches = [];
    $bRetval = false;
    if(($sFileData = \file_get_contents($sFileName)) !== false)
    {
//        $sPattern = "#(?://:)+[\w]*\w?#is";
//        $sPattern = "#//:[\w].+#imS";
        $sPattern = "#//:#im";
        $bRetval  = (bool)\preg_match_all($sPattern, $sFileData, $matches, \PREG_SET_ORDER);
    }
    return $bRetval;
}

/* -------------------------------------------------------- */
    function getDropletFromFiles($sBaseDir)
    {
        $aRetval = [];
        $oIterator = new DirectoryIterator($sBaseDir);
        foreach ($oIterator as $fileInfo) {
        // iterate the directory
            if($fileInfo->isDot()) continue;
            $sFileName = \rtrim(\str_replace('\\', '/', $fileInfo->getPathname()), '/');
            if($fileInfo->isFile()) {
            // only droplets are interesting
                if ((\is_readable($sFileName) && isDropletFile($sFileName))) {
                // if dir has no corresponding accessfile remember it
                    $aRetval[] = $sFileName;
                }
            }
        }
        return $aRetval;
    }
