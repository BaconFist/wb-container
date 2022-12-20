<?php
/*
 * Copyright (C) 2018 Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
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
 * Description of Unbenannt 1
 *
 * @package      core
 * @copyright    Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author       Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: backupMysql.php 340 2019-04-27 14:38:31Z Luisehahne $
 * @since        File available since 12.11.2017
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace bin\helpers;

    if (!defined('SETNAMES') && is_readable(__DIR__.'/boilerPlates.php')){require __DIR__.'/boilerPlates.php';}

  function Export_Database($host,$user,$pass,$name, $sTableFlags = FULL,array $aTables=[], $sFileName='', $sBackupPath='' )
  {

        $sTimeStamp = PreCheck::dateFormatToStrftime('Ymd_His', \time()); //  UTC
        $sRetval = false;
        $aValues = [];
        $vars = ['`','`'];
        $sDate = date("Y-m-d").date('_d');

        $sBackupName = (($sFileName!='') ? $sFileName : $name.'_'.$sTimeStamp).'.php';
      try {
        if (!defined('SETNAMES') && is_readable(__DIR__.'/boilerPlates.php')){require __DIR__.'/boilerPlates.php';}
//        if (!is_callable('make_dir')) {}
//        require (WB_PATH.'/framework/functions.php');
        $sBackupPath = rtrim($sBackupPath,'/');
        make_dir($sBackupPath);
        $sBackupPath .= '/';
        $sTmp = '-- <?php header($_SERVER[\'SERVER_PROTOCOL\'].\' 404 Not Found\');echo \'404 Not Found\'; flush(); exit; ?>';
        if (!($sRetval = \file_put_contents($sBackupPath.$sBackupName, $sTmp.PHP_EOL.SETNAMES))){
            throw new \Exception (sprintf('%s <br />Couldn\'t create %s',SETNAMES, basename($sBackupPath).'/'.$sBackupName));
        }
        $vars = ['`','`'];
        $mysqli = new \mysqli($host,$user,$pass,$name);
        $mysqli->select_db($name);
        $mysqli->getQuery("SET NAMES 'utf8'");

        $queryTables = $mysqli->getQuery('SHOW TABLES');
        while($row = $queryTables->fetch_row()) {
            $target_tables[] = $row[0];
        }

        $bCreateSelectedTables = (\is_array($aTables) && count($aTables) ? true : false);
        if($bCreateSelectedTables) {
            $target_tables = array_intersect( $target_tables, $aTables);
        }

        foreach($target_tables as $table)
        {
            $iFlag = 15;

            $sTableTitle  = '';
            $sInsertDrop  = '';
            $sInsertTitle = '';

            if (($sTableFlags & STRUCT)==STRUCT){
                $sTableTitle  = sprintf(TABLETITLE, $table);
            }
            if (($sTableFlags & DROPTABLE)==DROPTABLE){
                $sInsertDrop  = sprintf(INSERTDROP, $table);
            }
            if (($sTableFlags & DATA)==DATA){
                $sInsertTitle = sprintf(INSERTTITLE, $table);
            }
            if (($sTableFlags & FULL)==FULL){
                $sTableTitle  = sprintf(TABLETITLE, $table);
                $sInsertDrop  = sprintf(INSERTDROP, $table);
                $sInsertTitle = sprintf(INSERTTITLE, $table);
            }
// CREATE STRUCTURE
            $structure        = (!isset($structure) ?  '' : $structure);
            if ((($sTableFlags & FULL)==FULL)||
                (($sTableFlags & STRUCT)==(STRUCT))){
                $res          = $mysqli->getQuery('SHOW CREATE TABLE `'.$table.'`');
                $TableMLine   = $res->fetch_row();
                $structure    = $structure.$sTableTitle.$sInsertDrop.$TableMLine[1].';'.PHP_EOL.PHP_EOL.$sInsertTitle;
            }
// INSERT INTO
            if ((($sTableFlags & FULL)==(FULL)) ||
                (($sTableFlags & DATA)==(DATA))){
                $sGroup = '';
                $result       = $mysqli->getQuery('SELECT * FROM `'.$table.'`');
                $fields_amount= $result->field_count;
                $rows_num     = $mysqli->affected_rows;

                for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter=0)
                {
//when started (and every after 100 command cycle):
                    while($row = $result->fetch_array(MYSQLI_BOTH))
                    {
                        if ($sGroup != $table){
// change to an associative array
                            $aNumeric = [];
                            for ($i=0; $i<=$fields_amount; $i++) {$aNumeric[]=$i;}
                            $aRows = \array_diff_key($row,$aNumeric);
                            $aFields = \array_keys($aRows);
// add backticks to column
                            $add_backticks =
                            \array_walk(
                                $aFields,
                                function (& $val, $key) use ($vars) {
                                  $val = $vars[1].$val.$vars[1];
                                });
// prepare column list to add to INSERT INTO
                            $sInserts = ''.\implode(', ', $aFields).') ';
                            $sGroup = $table;
                        }
                        if ($st_counter == 0 ) {
#                        if ($st_counter%100 == 0 || $st_counter == 0 ) {
                            $insert = 'INSERT INTO `'.$table.'` ('.$sInserts.'';
                        }
    // VALUE data row
                        $structure .= $insert.'VALUES (';
                        for($j=0; $j<$fields_amount; $j++) {
                            $aFieldDetails = $result->fetch_field_direct($j);
                            $isInteger = \array_key_exists($aFieldDetails->type, NUMBERTYPES);
                            $row[$j] = \str_replace(["\n","\r"],["\\n","\\r"], \addslashes($row[$j]) );
                            if (!$isInteger) {
                                if (isset($row[$j])) {
                                    $structure .= '\''.$row[$j].'\'';
                                } else {
                                    $structure .= '\'\'';
                                }
                            } else {
                              if (isset($row[$j])){
                                $structure .= $row[$j];
                              } else {
                                  $structure .= 0;
                              }
                            }
                            if ($j<($fields_amount-1)) {
                                $structure.= ', ';
                            }
                        }  // for($j=0; $j<$fields_amount; $j++)
                        $structure .=')';
//    every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
#                        if ($st_counter+1==$rows_num){
                        if ((($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num){
                            $structure .= ';'.PHP_EOL;
                        } else {
                            $structure .= ';'.PHP_EOL;
                        }
                        $st_counter++;
                    } // while row
                } // for $fields_amount;
            } // Flag INSERT INTO
            $structure .= PHP_EOL;
        } // foreach $table
        $content = $structure.'-- created '.date("Y-m-d H:i:s").PHP_EOL;
        if (!($sRetval = \file_put_contents($sBackupPath.$sBackupName, $content, FILE_APPEND))){
            throw new \Exception (sprintf('Couldn\'t write  %s',basename($sBackupPath).'/'.$sBackupName));
        }
        return;
/*
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"".$backup_name."\"");
*/
      } catch (\Exception $ex) {
          $sErrMsg = (\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
#          echo (nl2br($sResult, !defined('XHTML'))).PHP_EOL;
          echo \preg_replace('/[\n\r]/', '',\nl2br($sErrMsg, !\defined('XHTML')));
          exit;
      }
  }

/*-----------------------------------------------------------------------------*/
// example section
/*-----------------------------------------------------------------------------*/
/*

    if (!defined('SYSTEM_RUN')) {require( (dirname(dirname((__DIR__)))).'/config.php');}
    if (!defined('SETNAMES') && is_readable(__DIR__.'/boilerPlates.php')){require __DIR__.'/boilerPlates.php';}

    StopWatch::start();
    $iTimeZone = StopWatch::setTimeZone('Europe/Berlin'); // 'Europe/Amsterdam'
    $iStartTime = \microtime(true);
    $sTimeStamp = PreCheck::dateFormatToStrftime('Ymd_His', \time()+$iTimeZone);

    $sBackupPath = str_replace(DIRECTORY_SEPARATOR, '/', WB_PATH).'/.backup';
    $sBackupName = DB_NAME.'_'.$sTimeStamp;
    $aTables = []; //  all tables

    $aTables = [
            TABLE_PREFIX.'mod_procalendar_settings',
            TABLE_PREFIX.'mod_procalendar_eventgroups',
            TABLE_PREFIX.'mod_procalendar_actions'
          ];
    $sBackupName = 'procalendar'.'_'.$sTimeStamp;

//  DATA | FULL | STRUCT | DROPTABLE,
//  multiple tables
    Export_Database(
          DB_HOST,
          DB_USERNAME,
          DB_PASSWORD,
          DB_NAME,
          STRUCT | DROPTABLE,
          $aTables,
          $sBackupName,
          $sBackupPath
    );


    $sEndBackup = \sprintf('<h4>End Backup at %s',PreCheck::dateFormatToStrftime('Y-m-d - H:i:s', time()+$iTimeZone));
?>
    <h4><?php echo StopWatch::stop();?> sec</h4>
    <h4><?php echo $sEndBackup;?></h4>
<?php
    echo sprintf('%2$s/%3$s successfully created at %1$s',date("Y-m-d H:i:s"),basename($sBackupPath),$sBackupName).'<br />';
*/

// end of file
