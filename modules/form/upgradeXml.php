<?php
/**
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category        addons
 * @package         form
 * @subpackage      upgradeXml
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.13.x
 * @requirements    PHP 7.4.x and higher
 * @version         0.0.1
 * @revision        $Id: $
 * @since           File available since 12.11.2017
 * @deprecated      no / since 0000/00/00
 * @description     xxx
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,ParentList};
//use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

    $oJson = null;
    $sMessage = '';
    $aMessage = [];
    $aXmlDatas = [];
    $aNewChildsXml = [];
    $sTargetFieldsPath  = $sAddonPath.'data/fields/';
    $sTargetLayoutPath  = $sAddonPath.'data/layouts/';
    try {
/* -----------------------
            if (!$section_id){
                $sMessage = sprintf("Can't upgrade %s.xml, missing section_id ",'/data/fields/*');
                throw new \Exception ($sMessage );
            }
----------------------- */
            $aImportFiles = glob($sTargetFieldsPath.'*.xml', GLOB_NOSORT);
            sort($aImportFiles,  SORT_NATURAL | SORT_FLAG_CASE );
            $iTotalXml = (sizeof($aImportFiles) ?? 0);
            $iIndex = 0;
            $sXmlLayout = '';
            $aXmlDatas = [];
            if ($iTotalXml)
            {
                foreach ($aImportFiles as $iKey => $sItem)
                {
                    $iIndex = $iKey;
                    $sAbsFilename = str_replace(['\\','//'],'/',$sItem);
                    $sLayout = $oApp->removeExtension(basename($sAbsFilename));
                    // read xml and migrate
                    if (($oXml = simplexml_load_file($sAbsFilename))) {
                        $oJson = json_encode(
                            $oXml,
                            JSON_OBJECT_AS_ARRAY
                          | JSON_UNESCAPED_UNICODE
                          | JSON_UNESCAPED_SLASHES
                          | JSON_NUMERIC_CHECK
//                          | JSON_PRETTY_PRINT
                        );
                        $aXmlDatas[$sLayout] = $oJson;
                        $aJson = json_decode($oJson, TRUE);
                    }
                    $sJsonFile = $sTargetFieldsPath.'__snapshots__/json/'.$sLayout.'.json';
                    if (!(make_dir(dirname($sJsonFile)))){
                        $sDomainFile = $oApp->getDirNamespace($sTargetFieldsPath.'__snapshots__/json/','/');;
                        echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] Can't create %s</div>\n",__LINE__,$sDomainFile));
                    }
/*
                    //add/change neu element to root
                    $aNewRootXml= [
                    'section_id' => $aJson['section_id'],
                    'page_id' => $aJson['page_id'],
                    'layout' => $sLayout,
                    'description' => $aJson['description']
                    ];
                    $aNewChildsXml[$sLayout] = ['fields' => $aJson['fields']];
                    $aNewXml = array_merge($aNewRootXml,$aNewChildsXml);
                    $aXml = $aNewChildsXml[$sLayout]['fields']['field'];
                    // backup new entry into json
                    $oNewXml = json_encode(
                      $aJson,
                      JSON_FORCE_OBJECT
                    | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_NUMERIC_CHECK
                    | JSON_PRETTY_PRINT
                    );
*/

                    $sXmlFile = $sTargetFieldsPath.'__snapshots__/json/'.$sLayout.'.xml';
                    //save json object
                    if ((file_put_contents($sJsonFile,$oJson)===false)){
                        $sDomainFile = $oApp->getDirNamespace($sTargetFieldsPath.'__snapshots__/json/'.$sLayout,'/');;
                        echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] Can't create %s</div>\n",__LINE__,$sDomainFile.'.xml'));
                    }
/*
                    require $sAddonPath.'convert2Xml.php';
                    $sXmlFile = $sTargetFieldsPath.'__snapshots__/xml/'.$sLayout.'.xml';
                    if ((file_put_contents($sXmlFile,$sNewXml)===false)){
                        $sDomainFile = $oApp->getDirNamespace($sTargetFieldsPath.'__snapshots__/xml'.$sLayout,'/');;
                        echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] Can't create %s</div>\n",__LINE__,$sDomainFile.'.xml'));
                    }
*/
                    //Import variables into the current symbol table
                    if ($sLayout != $sXmlLayout){
                        $aTest[$sLayout] = $aJson;
                        extract ($aJson);
                        $sDescription = $description;

                        $sXmlLayout = $sLayout;
                    }

                    if ($oXml === FALSE) {
                        echo "There were errors parsing the XML file.\n";
                        foreach(libxml_get_errors() as $error) {
                            $sMessage = sprintf("%s\n",$error->message);
                            echo ($sMessage );
                        }
                    }
/*
                    prettyXml($sXmlFile);
                        $sMessage = nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] %s</div>\n",__LINE__,$sLayout));
                        throw new \Exception ($sMessage );
*/
                }//end foreach

            } else {
                $sMessage = sprintf("No Fields XML Files found in %s ",'/data/fields/');
                throw new \Exception ($sMessage );
            }

        }catch (\Exception $ex) {
            $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
            echo ($sErrMsg);
        }
/* ------------ Backup dump vars
//echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] %d == %s von %d gefunden </div>\n",__LINE__,++$iIndex,$sLayout,$iTotalXml));

$sDomain = \basename(__DIR__).'/'.\basename(__FILE__);
print '<pre class="w3-pre w3-border w3-white w3-small w3-container w3-padding">'.nl2br(sprintf("function: <span>%s</span> (%s) Filename: <span>%s</span> Line %d\n",(!empty(__FUNCTION__) ? __FUNCTION__ : 'global'),'myVar',$sDomain,__LINE__));
\print_r(  ); print "</pre>"; \flush (); // | JSON_PRETTY_PRINT htmlspecialchars() ob_flush();;sleep(10); die();

*/
