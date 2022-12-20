<?php
/*
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * cmdSave.php
 *
 * @category     Addons
 * @package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      3.0.1
 * @lastmodified $Date: 2018-09-20 20:09:30 +0200 (Do, 20 Sep 2018) $
 * @since        File available since 2015-12-17
 * @description  xyz
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

        if (\bin\SecureTokens::checkFTAN ()) {
            // take over post - arguments
            $aDatas = [];
            $oReg = WbAdaptor::getInstance();
            $oRequest = $oReg->getRequester();
            $database = $oReg->getDatabase();
            $oApp = $oReg->getApplication();

            // get POST or GET requests
            $aRequestVars = $oApp->getRequestVars();
/*  filter which to have enabled */
            $aAutoFilter = [
                'WbLink' => 1,
                'ReplaceSysvar' => 1,
                'CssToHead' => 1,
                'CleanUp' => 1,
                'SnippetCss' => 1,
                'FrontendCss' => 1,
                ];

            //$aDefaultSettings = \array_merge(['send_htaccess'=>0,'content_output_filter'=>''], $aDefaultSettings);
            foreach ( $aDefaultSettings as $key => $value ) {
                if (\in_array( $key, $aAllowedFilters) ) {
                    $aDatas[$key] = ( ($aRequestVars[$key] ?? $value));
                }
            }

            //$aTmp = array_intersect_key($aDatas , $aDefaultSettings);

            if ($aFilterSettings['Email']) {
                $aDatas['email_filter']    = (int)($aRequestVars['email_filter'] ?? $aDefaultSettings['email_filter']);
                $aDatas['mailto_filter']   = (int)($aRequestVars['mailto_filter'] ?? $aDefaultSettings['mailto_filter']);
                $aDatas['at_replacement']  = ((isset($aRequestVars['at_replacement']) && !empty($aRequestVars['at_replacement']))
                                               ? \trim(\strip_tags($aRequestVars['at_replacement']))
                                               : $aDefaultSettings['at_replacement']);
                $aDatas['dot_replacement'] = ((isset($aRequestVars['dot_replacement']) && !empty($aRequestVars['dot_replacement']))
                                               ? \trim(\strip_tags($aRequestVars['dot_replacement']))
                                               : $aDefaultSettings['dot_replacement']);
            }

            if ($aDatas['Short_force']) {
                $sSourceFile = $oReg->AppPath.'install/Source/_short4.php';
                $sTargetFile = $oReg->AppPath.'short.php';
                if ((is_readable($sSourceFile) && !is_readable($sTargetFile)) && !isset($aRequestVars['send_htaccess']))
                {
                    if ($content = file_get_contents($sSourceFile)){
                        $aSerchesChars = ['pages'];
                        $aReplaceChars = [trim($oReg->PagesDir,'/')];
                        //$aReplaceChars = ['Seiten'];
                        $sContent = str_replace($aSerchesChars,$aReplaceChars,$content);
                    }
                    if (!file_put_contents($sTargetFile,$sContent)) {
                    }
                }
            } // Short_force
            else  // delete short.php
            {
                $sTargetFile = $oReg->AppPath.'short.php';
                if (is_writable($sTargetFile)){
                    unlink($sTargetFile);
                }
            }
//--------------------------------------------------------------------------------------------

            $bsetHtaccessFile = ((($aRequestVars['send_htaccess'] ?? false) || ($aRequestVars['import_htaccess'] ?? false))); // && !empty(trim($aRequestVars['content_output_filter'] ?? ''))
            if ($bsetHtaccessFile)
            {
                $sHtaccessFile = $oReg->AppPath.'.htaccess';
                if (($aRequestVars['delete_force'] ?? false)){
                    if (\is_writable($sHtaccessFile)){
                        if (!\unlink($sHtaccessFile)) {
                            throw new \Exception(sprintf("%s\n",'cannot delete .htaccess file'));
                        }
                    }
                $sql = '
                SELECT
                `name`
                FROM `'.$oReg->TablePrefix.'mod_output_filter`
                ';
                } else {
                    $aSerchesChars = ['pages','admin','media','modules'];
                    $aReplaceChars = [trim($oReg->PagesDir,'/'),trim($oReg->AcpDir,'/'),trim($oReg->MediaDir,'/'),trim($oReg->ModuleDir,'/')];
                    //$aReplaceChars = ['Seiten',trim($oReg->AcpDir,'/'),trim($oReg->MediaDir,'/'),trim($oReg->ModuleDir,'/')];
                    $bCanImportToHtaccess = ($aRequestVars['import_htaccess'] ?? false);
                    if ($bCanImportToHtaccess)
                    {
                        $sImportFile = $oReg->AppPath.'install/htaccess/redirect'.$aRequestVars['import_htaccess'].'.inc';
                        $content = (is_readable($sImportFile)? file_get_contents($sImportFile) : '');
                        $sContent = str_replace('{'.$aRequestVars['import_htaccess'].'}',$content,$aRequestVars['content_output_filter']);
                    } else {
                        $sContent = trim($aRequestVars['content_output_filter'])."\n";
                    }
                    $sContent = str_replace($aSerchesChars,$aReplaceChars,$sContent);
                    if (!file_put_contents($sHtaccessFile, !empty(($sContent)) ? $sContent : "\n")) {
                        throw new \Exception(sprintf("%s\n",'cannot update .htaccess file'));
                    }
                }
                if (!is_readable($sHtaccessFile)){;}
                $sql = '
                SELECT
                `name`
                FROM `'.$oReg->TablePrefix.'mod_output_filter`
                ';
            }
            else
            {
                //set status checkboxes
                foreach ($aAllowedFilters as $key){
                    $aDatas[$key] = ($aDatas[$key] ?? '0');
                }
                $sNameValPairs = '';
                foreach ($aDatas as $index => $val) {
                    if (\in_array( $key, $aAllowedFilters) ) {
                        $sNameValPairs .= ',(\''.$index.'\', \''.$database->escapeString($val).'\')';
                    }
                }
                $sValues = ltrim($sNameValPairs, ',');
                $sql = '
                  REPLACE INTO `'.$oReg->TablePrefix.'mod_output_filter` (`name`, `value`)
                  VALUES '.$sValues.'
                  ';

/*
                $sql = '
                SELECT
                `name`
                FROM `'.$oReg->TablePrefix.'mod_output_filter`
                ';
*/
            }

            if ($database->query($sql)) {
            //anything ok
                $msgTxt = $MESSAGE['RECORD_MODIFIED_SAVED'];
                $msgCls = 'green';
            } else {
            // database error
                $msgTxt = sprintf("%s in DB `%s` %s ",$MESSAGE['RECORD_MODIFIED_FAILED'],$oReg->TablePrefix.'mod_output_filter',$database->get_error());
                $msgCls = 'red';
            }

        } else {
        // FTAN error
            $msgTxt = $MESSAGE['GENERIC_SECURITY_ACCESS'];
            $msgCls = 'red';
        }
// end of file
