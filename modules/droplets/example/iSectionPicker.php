<?php
//:Load the view.php from any other section-module
//:Use [[iSectionPicker?sid=123]]

/*
 * Copyright (C) 2020 Manuela von der Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
/**
 * iSectionPicker
 *
 * @category     Addon
 * @package      Droplet
 * @copyright    Manuela von der Decken <manuela@isteam.de>
 * @author       Manuela von der Decken <manuela@isteam.de>
 * @author       dietmarWöllbrink <dietmar.woellbrink@websitebaker.org>
 * @license      GNU General Public License 2
 * @version      0.0.1 $Rev: 12 $
 * @revision     $Id: iSectionPicker.php 12 2020-08-06 05:25:43Z Manuela $
 * @since        File available since 07.03.2020
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */


// import global objects
    $oReg      = \bin\WbAdaptor::getInstance();
    $oApp = $wb = ($oReg->getApplication() ?? $GLOBALS['wb']);
    $oDb       = $database = $oReg->getDatabase();
    $sContent  = '';
// sanitize argument $sid
    $iSectionId = \intval($sid ?? 0);

// try to load the section and the corresponding page
    $sql = 'SELECT `s`.*, '
//         .        '`p`.`viewing_groups`, '
         .        '`p`.`visibility`, '
         .        '`p`.`link`, '
         .        '`p`.`page_title`, '
         .        '`p`.`menu_title` '
         . 'FROM `'.$oDb->TablePrefix.'sections` `s` '
         . 'INNER JOIN `'.$oDb->TablePrefix.'pages` `p` '
         .    'ON `p`.`page_id`=`s`.`page_id` '
         . 'WHERE `s`.`section_id` = '.$iSectionId.' '
         .   'AND ('.\time().' BETWEEN `s`.`publ_start` AND `s`.`publ_end`) '
         .   'AND `active` = 1 '
         .   'AND `p`.`visibility` NOT IN (\'deleted\',\'none\')';

    try {
        $oResultSet = $oDb->query($sql);
        if (($aRecord = $oResultSet->fetchRow(\MYSQLI_ASSOC))) {
            unset($sql);
// if matching record found
            $module = $sModuleName = $aRecord['module'];
            $section_id  = $aRecord['section_id'];
            $page_id     = $aRecord['page_id'];
            if (!$oApp->isPageVisible($page_id)) {
                throw new \InvalidArgumentException('no valid visibility');
            }
// include the buffered view.php of the needed module
            $sFrontendViewFile = $oReg->AppPath.'modules/'.$sModuleName.'/view.php';
            if (\is_readable($sFrontendViewFile)){
                \ob_start();
                require $sFrontendViewFile;
                $sContent = \ob_get_clean();
            } else {
                throw new \InvalidArgumentException(\sprintf('%s/view.php not found/readable',$sModuleName));
            }
// define path and url to frontend.*
            $sFrontendPath = $oReg->AppPath.'modules/'.$sModuleName.'/frontend';
            $sFrontendUrl  = $oReg->AppUrl.'modules/'.$sModuleName.'/frontend';
//check out if conternt already contains a link to frontend.css
            $sFrontendCss = '';
            $sPattern = '/<link[^>]*?src\s*=\s*\"'.\preg_quote($sFrontendUrl, '/').'css\".*?\/>/si';
            if (!\preg_match($sPattern, $sContent)) {
// if not, then try to find and include frontend.css
                if (\is_readable($sFrontendPath.'.css')) {
                    $sFrontendCss = '
                    <script>
                        try {
                            var ModuleCss = "'.$sFrontendUrl.'.css";
                            var UserCss   = "'.$sFrontendUrl.'User.css";
                            if (typeof LoadOnFly === "undefined") {
                                include_file(ModuleCss, "css");
                                include_file(UserCss, "css");
                            } else {
                                LoadOnFly("head", ModuleCss);
                                LoadOnFly("head", UserCss);
                            }
                        } catch(e) {
                            /* alert("an error has occured: "+e.message) */
                        }
                    </script>
                    ';
                }
            }
//check out if conternt already contains a <script link> to frontend.js
            $sFrontendJs = '';
            $sPattern = '/<script[^>]*?src\s*=\s*\"'.\preg_quote($sFrontendUrl, '/').'js\".*?\/>/si';
            if (!\preg_match($sPattern, $sContent)) {
// if not, then try to find and include frontend.css
                if (\is_readable($sFrontendPath.'js')) {
                    $sFrontendJs = '
                    <script>
                        try {
                            var ModuleJs = "'.$sFrontendUrl.'js";
                            include_file(ModuleJs, "js");
                        } catch(e) {
                            /* alert("an error has occured: "+e.message) */
                        }
                    </script>
                    ';
                }
            }
            $sContent = $sFrontendCss.$sFrontendJs.$sContent;
        }//end pageisvisible
    } catch (\Throwable $ex) {
        /* place to insert different error/logfile messages */
        $sErrMessage = '['.\basename(__FILE__, '.php').' :: '.$ex->getMessage().']';
        $sContent =  (($oReg->Debug ?? false) ? $sErrMessage : true);
    }
    return (empty($sContent) ? 'page not visible, no content found' : $sContent);
