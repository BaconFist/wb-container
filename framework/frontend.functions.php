<?php
/*
 * About WebsiteBaker
 *
 * Website Baker is a PHP-based Content Management System (CMS)
 * designed with one goal in mind: to enable its users to produce websites
 * with ease.
 *
 * LICENSE INFORMATION
 *
 * WebsiteBaker is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * WebsiteBaker is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */
/**
 *
 * @category        core
 * @package         framework
 * @subpackage      frontend
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            https://websitebaker.org/
 * @license         GNU General Public License 2.0
 * @platform        WebsiteBaker 2.11.0
 * @requirements    PHP 7.2.x and higher
 * @version         $Id: frontend.functions.php 352 2019-05-13 12:34:35Z Luisehahne $
 * @since           File available since 18.10.2017
 * @deprecated      no
 * @description     This file is where some of the WB frontend functions are stored.
 *
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,wb,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,StopWatch};

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
    if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;}
/* -------------------------------------------------------- */

if (!\defined('FRONTEND_FUNCTIONS_FILE_LOADED')){
//  Define that this file has been loaded
    \define('FRONTEND_FUNCTIONS_FILE_LOADED', true);
}

    $oReg = WbAdaptor::getInstance();
    if (!isset($wb) || !($wb instanceof \frontend)) {$wb = new \frontend();}

    $oRequest = $oReg->getRequester();
    $oApp = $oReg->getApplication(); // is instance of frontend
//print nl2br(sprintf("function/class/file %s [%d] with instance of %s\n",(!empty(__METHOD__) ? __METHOD__ : basename(__FILE__)),__LINE__,get_class($oApp)));

// deprecated only backwardscompability because modules are using backend object $admin in the frontend
    if (isset($oApp) && !isset($admin)) {$admin = $oApp; }
    $page_id = (!isset($page_id) ? $oApp->default_page_id : $page_id);
    if (!registerSnippets($page_id)){/* nothing to do only for loading the snippets include function */}
// Frontend functions
        /**
         * generate full qualified URL from relative link based on pages_dir
         * @param string $link
         * @return string
    if (!\is_callable('getPageLink')) {
        function getPageLink(string $sLink, bool $bRetro=true): string
        {
        return WbAdaptor::getInstance()
            ->getApplication()
            ->page_link($sLink,$bRetro);
        }
    }
         */

    if (!is_callable('get_page_link')) {
        /**
         * get relative link from database based on pages_dir
         * @global <type> $database
         * @param <type> $id
         * @return <type>
         */
        function get_page_link( $id,$bRetro=true ):? string
        {
            $mRetval = null;
            $oReg    = WbAdaptor::getInstance();
            $oDb     = $oReg->getDatabase();
            if ($bRetro){
                $sql     = '
                SELECT `link` FROM `'.$oReg->TablePrefix.'pages`
                WHERE `page_id` = '.\intval($id).'
                ';
                $mRetval = $oDb->get_one( $sql );
            } else {
                $oApp    = $oReg->getApplication();
                $mRetval = $oApp->getPageLink($id);
            }
            return $mRetval;
        }
    }

//function to highlight search results
    if (!is_callable('search_highlight')) {
        /**
         *
         * @staticvar boolean $string_ul_umlaut
         * @staticvar boolean $string_ul_regex
         * @param string $foo
         * @param array $arr_string
         * @return string
         */
        function search_highlight($foo='', $arr_string=[]) {
            static $string_ul_umlaut = '';
            static $string_ul_regex = '';
            if(empty($string_ul_umlaut) || empty($string_ul_regex)) {
                require(WB_PATH.'/search/search_convert.php');
            }
            $foo = entities_to_umlauts($foo, 'UTF-8');
            \array_walk($arr_string, function(& $val,$key) {$val = \preg_quote($val, '~');});
            $search_string = \implode("|", $arr_string);
            $string = \str_replace($string_ul_umlaut, $string_ul_regex, $search_string);
            // the highlighting
            // match $string, but not inside <style>...</style>, <script>...</script>, <!--...--> or HTML-Tags
            // Also droplet tags are now excluded from highlighting.
            // split $string into pieces - "cut away" styles, scripts, comments, HTML-tags and eMail-addresses
            // we have to cut <pre> and <code> as well.
            // for HTML-Tags use <(?:[^<]|<.*>)*> which will match strings like <input ... value="value</b>" >
            $matches = \preg_split("~(\[\[.*\]\]|<style.*</style>|<script.*</script>|<pre.*</pre>|<code.*</code>|<!--.*-->|<(?:[^<]|<.*>)*>|\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,8}\b)~iUs",$foo,-1,(PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY));
            if (\is_array($matches) && $matches != array()) {
                $foo = "";
                foreach($matches as $match) {
                    if ($match[0]!="<" && !\preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,8}$/i', $match) && !preg_match('~\[\[.*\]\]~', $match)) {
                        $match = \str_replace(array('&lt;', '&gt;', '&amp;', '&quot;', '&#039;', '&nbsp;'), array('<', '>', '&', '"', '\'', "\xC2\xA0"), $match);
                        $match = \preg_replace('~('.$string.')~ui', '_span class=_highlight__$1_/span_',$match);
                        $match = \str_replace(array('&', '<', '>', '"', '\'', "\xC2\xA0"), array('&amp;', '&lt;', '&gt;', '&quot;', '&#039;', '&nbsp;'), $match);
                        $match = \str_replace(array('_span class=_highlight__', '_/span_'), array('<span class="highlight">', '</span>'), $match);
                    }
                    $foo .= $match;
                }
            }
            if(DEFAULT_CHARSET != 'utf-8') {
                $foo = umlauts_to_entities($foo, 'UTF-8');
            }
            return $foo;
        }
    }

    if (!is_callable('page_content')) {
        /**
         *
         * @global array $TEXT
         * @global array $MENU
         * @global array $HEADING
         * @global array $MESSAGE
         * @global array $globals several global vars
         * @global datadase $database
         * @global wb $wb
         * @global string $global_name
         * @param int $block
         * @return void
         */
        function page_content($block = 1) {
            // Get outside objects
            global $TEXT,$MENU,$HEADING,$MESSAGE; // backwards compability
            global $globals;
//            static $iSections=1;
            global $wb;
            $oReg     = WbAdaptor::getInstance();
            $oApp     = ($wb ?? $oReg->getApplication()); // should be instance of frontend
            $oRequest = $oReg->getRequester();
            $database = $oReg->getDatabase();
            $wb       = $oApp;
            $admin    = $oApp;
            $bLocalDebug  =  is_readable(__DIR__.'/.setDebug');
//print nl2br(sprintf("<b>Block %d in function %s [%dth call] LINE: %04d with instance of %s</b> \n",$block,__METHOD__,$i++,__LINE__,get_class ($oApp)));
            $iNow  = \time();
            $sContent = '';
            $oTrans = \translate::getInstance();
            $oTrans->enableAddon($oReg->AcpDir.'pages');
            try {
                if ($oApp->page_no_active_sections == true) {
                     if ($block == 1){
                        \header($_SERVER['SERVER_PROTOCOL'].' 404 Not found');
                        $sMsg = sprintf(''.$oTrans->MESSAGE_FRONTEND_SORRY_NO_ACTIVE_SECTIONS.'',$oApp->menu_title);
                        ob_start();
                        $sErrorPath = WB_PATH.'/account/templates/';
                        $sErrorFile = (!is_readable($sErrorPath.LANGUAGE.'_404.htt') ? $sErrorPath.'EN_404.htt' : $sErrorPath.LANGUAGE.'_404.htt');
                        include $sErrorFile;
                        $sMsg = ob_get_clean();
                        echo $sMsg;\flush();
                    }
                    return;
                } elseif ($oApp->page_access_denied == true) {
                    if ($block == 1){
                        \header($_SERVER['SERVER_PROTOCOL'].' 404 Not found');
                        $sMsg = sprintf(''.$oTrans->MESSAGE_FRONTEND_SORRY_NO_VIEWING_PERMISSIONS.'',$wb->menu_title);
                        ob_start();
                        $sErrorPath = WB_PATH.'/account/templates/';
                        $sErrorFile = (!is_readable($sErrorPath.LANGUAGE.'_404.htt') ? $sErrorPath.'EN_404.htt' : $sErrorPath.LANGUAGE.'_404.htt');
                        include $sErrorFile;
                        $sMsg = ob_get_clean();
                        echo $sMsg;\flush();
                    }
                    return;
                }
                if (isset($globals) && \is_array($globals)) {
                    foreach($globals as $global_name) {
                        global $$global_name;
                    }
                }
                // Make sure block is numeric
                $block = (($block < 1) ? 1 : $block);
                if (!\defined('PAGE_CONTENT')  || (($block != 1))){
                    $page_id = (int)(isset($oApp->page_id) ? $oApp->page_id : PAGE_ID);
                    $sBackLink = $oApp->default_link;
                    $sSqlSet = '
                    SELECT `s`.*
                    ,`p`.`viewing_groups`
                    ,`p`.`visibility`
                    ,`p`.`menu_title`
                    ,`p`.`link`
                    ,`s`.`block`
                    FROM `'.TABLE_PREFIX.'sections` `s`
                    INNER JOIN `'.TABLE_PREFIX.'pages` `p`
                    ON `p`.`page_id`=`s`.`page_id`
                    WHERE `s`.`page_id` = '.( int)$page_id.'
                      AND ('.$iNow.' BETWEEN `s`.`publ_start` AND `s`.`publ_end`)
                      AND `s`.`active` = 1
                      AND `p`.`visibility` NOT IN (\'deleted\',\'none\')
                      AND `s`.`block`='.$block.'
                    ORDER BY `s`.`position`';
                    if (!($oSections= $database->query($sSqlSet))) {
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sSqlSet));
                      throw new \Exception($database->get_error());
                    }
                //  Include page content
                //  First get all sections for this page   , `publ_start`, `publ_end`
                //  If none were found, check if default content is supposed to be shown
                    if (($oSections->numRows() > 0)) {  //  end $oRes->numRows()
                    //  Loop through them and include their module file
                        $iSections = $oSections->numRows();
                        $aSection = [];
                        while(!is_null($aSection = $oSections->fetchRow(MYSQLI_ASSOC))) {
                            $section_id = (int)$aSection['section_id'];
                            $module     = $aSection['module'];
                            $bAnchor    = $aSection['anchor'];
                            $sAttribute = $aSection['attribute'];
                            $isActive   = $aSection['active'];
                        //  skip this section if it is out of publication-date
                            if (($block != 1) && in_array($aSection['visibility'], ['registered','private']) && !$wb->is_authenticated()){
                                $sContent = '';
                                break;
                            }
                        //  check if module exists - feature: write in errorlog
//                                echo nl2br(sprintf("%s\n \n",$module.'/view.php'));
                        if ($bLocalDebug){
                            echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] <i>section_id</i> (%s) <i>modules</i> (%s) <i>block</i> (%s)</div>\n",__LINE__, $section_id,$module,$block));
                        }
                            if (\is_readable(WB_PATH.'/modules/'.$module.'/view.php')) {
                            //  fetch content -- this is where to place possible output-filters (before highlighting)
                                \ob_start() ; // fetch original content<div id="Sec103" class="section  m-modulename user-defined-class" >
                                $sSectionIdPrefix = (\defined('SEC_ANCHOR') && SEC_ANCHOR != '') ? SEC_ANCHOR : '' ;
                                require (WB_PATH.'/modules/'.$module.'/view.php') ;
                                $sContent = \ob_get_clean() ;
                            } else {
                                continue;
                            }
                            // highlights searchresults
                            if (isset($_GET['searchresult']) && \is_numeric($_GET['searchresult'])
                                      && !isset($_GET['nohighlight'])
                                      && isset($_GET['sstring'])
                                      && !empty($_GET['sstring'])
                                      ) {
                                $arr_string = \explode(" ", $_GET['sstring']);
                                if($_GET['searchresult']==2) { // exact match
                                    $arr_string[0] = \str_replace("_", " ", $arr_string[0]);
                                }
                                echo search_highlight($sContent, $arr_string);
                                $_GET = [];
                            } else {
                                if (!empty($sContent) && $isActive) {
                                    $bPrintAnchor = ($bAnchor && ($sSectionIdPrefix == 'none')||($sSectionIdPrefix != 'none'));
                                    $sAnchor = "\n".'<div id="'.$sSectionIdPrefix.$section_id.'" class="section m-'.$module.' '.$sAttribute.'" >';
                                    $sBeforeContent = ($bPrintAnchor ? $sAnchor : '');
                                    $sAfterContent  = ($bPrintAnchor ? '</div><!-- INFO '.$module.$section_id.' -->' : '');
                                    echo $sContent = $sBeforeContent."\n".$sContent."\n".$sAfterContent."\n";
                                }
                            }
//  no more sections found, so break e.g from multiselection
                            if ($iSections == 0){break;}
                        } // end while
                    }
                } else {   // Searchresults! But also some special pages,
                 //  e.g. guestbook (add entry), news (add comment) uses this
                    \ob_start(); // fetch original content
                    require(PAGE_CONTENT);
                    $sContent = ob_get_clean();
                //  Apply Filters
                    if (\is_callable('OutputFilterApi')) {
                        $sContent = OutputFilterApi('OpF?arg=special', $sContent);
                    }
                    echo $sContent;
                }
             }catch (Exception $e) {
                $sMsg = $e->getMessage();
                \trigger_error(sprintf('[%d] %s',__LINE__, $sMsg ), E_USER_NOTICE);
             }
        } //  end function page_content
    }

    if (!is_callable('show_content')) {
        function show_content($block=1) {
            page_content($block);
        }
    }


if (!is_callable('showRetroBreadcrumbs'))
    {
        function showRetroBreadcrumbs($sep = ' &raquo; ',$level = 0, $links = true, $depth = -1, $title = '')
        {
            global $wb,$database,$MENU;
            $page_id = $wb->page_id;
            $title = (trim($title) == '') ? $MENU['BREADCRUMB'] : $title;
            if ($page_id != 0)
            {
                $counter = 0;
                // get links as array
                $bread_crumbs = $wb->page_trail;
                $count = sizeof($bread_crumbs);
                // level can't be greater than sum of links
                $level = ($count <= $level ) ? $count-1 : $level;
                // set level from which to show, delete indexes in array
                $crumbs = array_slice($bread_crumbs, $level );
                $depth = ($depth <= 0) ? sizeof($crumbs) : $depth;
                // if empty array, set orginal links
                $crumbs = (!empty($crumbs)) ?  $crumbs : $wb->page_trail;
                $total_crumbs = ( ($depth <= 0) || ($depth > sizeof($crumbs)) ) ? sizeof($crumbs) : $depth;
                print '<div class="breadcrumb"><span class="bc_title">'.$title.'</span><ul class="breadcrumb">';
              //  print_r($crumbs);
                foreach ($crumbs as $temp)
                {
                    if($counter == $depth) { break; }
                    // set links and separator
                        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'pages` WHERE `page_id`='.(int)$temp;
                        $query_menu = $database->query($sql);
                        $page = $query_menu->fetchRow();
                        $show_crumb = (($links == true) && ($temp != $page_id))
                            ? '<li><a class="bc_link" href="'.page_link($page['link']).'">'.$page['menu_title'].'</a></li>'
                            : '<li><a class="bc_crumb" href="'.page_link($page['link']).'">'.$page['menu_title'].'</a></li>';
                        // Permission
                        switch ($page['visibility'])
                        {
                            case 'none' :
                            case 'hidden' :
                            // if show, you know there is an error in a hidden page
                                print $show_crumb.'&nbsp;';
                                break;
                            default :
                                print $show_crumb;
                                break;
                        }

                        //if ( ( $counter <> $total_crumbs-1 ) )
                        //{
                        //    print '<span class="separator">'.$sep.'</span>';
                        //}
                    $counter++;
                }
                print "</ul></div>\n";
            }
        }
    }

    if (!is_callable('show_breadcrumbs'))
    {
        function show_breadcrumbs($sep = ' &raquo; ',$level = 0, $links = true, $depth = -1, $title = '', $print=true)
        {
            global $wb,$database;
            $oLang = Translate::getInstance();
            $oLang->enableAddon('templates\\'.DEFAULT_TEMPLATE);
            $retVal = '';
            $page_id = $wb->page_id;
            $title = (\trim($title) == '') ? $oLang->MENU_BREADCRUMB : $title;
            if ($page_id != 0)
            {
                $counter = 0;
                // get links as array
                $bread_crumbs = $wb->page_trail;
                $count = \sizeof($bread_crumbs);
                // level can't be greater than sum of links
                $level = ($count <= $level ) ? $count-1 : $level;
                // set level from which to show, delete indexes in array
                $crumbs = \array_slice($bread_crumbs, $level );
                $depth = ($depth <= 0) ? \sizeof($crumbs) : $depth;
                // if empty array, set orginal links
                $crumbs = (!empty($crumbs)) ?  $crumbs : $wb->page_trail;
                $total_crumbs = ( ($depth <= 0) || ($depth > \sizeof($crumbs)) ) ? \sizeof($crumbs) : $depth;
                $retVal .= '<div class="breadcrumb">'.PHP_EOL.'<span class="title">'.$title.'</span>'.PHP_EOL;
                foreach ($crumbs as $temp)
                {
                    if($counter == $depth) { break; }
                    // set links and separator
                        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'pages` WHERE `page_id`='.(int)$temp;
                        $query_menu = $database->query($sql);
                        $page = $query_menu->fetchRow();
                        $show_crumb = (($links == true) && ($temp != $page_id))
                                ? '<a href="'.page_link($page['link']).'" class="link">'.$page['menu_title'].'</a>'.PHP_EOL
                                : '<span class="crumb">'.$page['menu_title'].'</span>'.PHP_EOL;
                        // Permission
                        switch ($page['visibility'])
                        {
                            case 'none' :
                            case 'hidden' :
                            // if show, you know there is an error in a hidden page
                                $retVal .= $show_crumb.'&nbsp;';
                                break;
                            default :
                                $retVal .= $show_crumb;
                                break;
                        }
                        if (($counter <> $total_crumbs-1 ) )
                        {
                            $retVal .= '<span class="separator">'.$sep.'</span>'.PHP_EOL;
                        }
                    $counter++;
                }
                $retVal .=  "</div>".PHP_EOL;
                if ($print) { echo $retVal;} else {return $retVal;}
            }
        }
    }

// Function for page title
    if (!\is_callable('page_title')) {
        function page_title($sSpacer = ' - ', $sTpl = '[WEBSITE_TITLE][SPACER][PAGE_TITLE]',$bPrint=true)
        {
            global $iStartTime; // from initialize
            $sRetVal = '';
            $sDateFormat = 'Y';
            $iTimeZone = StopWatch::setTimeZone('Europe/Berlin'); // 'Europe/Amsterdam'
            $iEndTime = \microtime(true);
            $oReg = WbAdaptor::getInstance();
            $oApp = $oReg->getApplication();
            $iProcesstime =\round ($iEndTime - $iStartTime, 3);

            $sWebsiteTitle = ($oReg->WebsiteTitle ?? '');
            $sPageTitle    = ($oApp->page['page_title'] ?? '');
            $sMenuTitle    = ($oApp->page['menu_title'] ?? '');
            $aSearches = ['[COPY_RIGHT]','[YEAR]','[PROCESS_TIME]','[WEBSITE_TITLE]','[PAGE_TITLE]','[MENU_TITLE]', '[SPACER]'];
            $aReplaces     = ['©',\gmdate($sDateFormat),$iProcesstime,$sWebsiteTitle, $sPageTitle, $sMenuTitle, $sSpacer];
            $sRetVal       = \str_ireplace($aSearches, $aReplaces, $sTpl);
            if ($bPrint) { echo $sRetVal;} else {return $sRetVal;}
        }
    }

// Function for page description
    if (!\is_callable('page_description')) {
        function page_description($bPrint=true)
        {
            $sRetVal = '';
            $oReg = WbAdaptor::getInstance();
            $oApp = $oReg->getApplication();
           if(!empty($oApp->page['description']))
            {
                $sRetVal = $oApp->page['description'];
            }
            elseif (!empty($oReg->WebsiteDescription))
            {
                $sRetVal = $oReg->WebsiteDescription;
            }
            if ($bPrint) { echo $sRetVal;} else {return $sRetVal;}

        }
    }

// Function for page keywords
    if (!\is_callable('page_keywords')) {
        function page_keywords($bPrint=true)
        {
            $sRetVal = '';
            $oReg = WbAdaptor::getInstance();
            $oApp = $oReg->getApplication();
            if(!empty($oApp->page['keywords']))
            {
                $sRetVal = $oApp->page['keywords'];
            }
            elseif (!empty($oReg->WebsiteKeywords))
            {
                $sRetVal = $oReg->WebsiteKeywords;
            }
            if ($bPrint) { echo $sRetVal;} else {return $sRetVal;}
        }
    }

// Function for page header
    if (!\is_callable('page_header')) {
        function page_header($bPrint=true)
        {
            $sRetVal = '';
            $oReg = WbAdaptor::getInstance();
            $oApp = $oReg->getApplication();
            if (!empty($oReg->WebsiteHeader)) {
                $sRetVal = $oReg->WebsiteHeader;
            }
            if ($bPrint) { echo $sRetVal;} else {return $sRetVal;}
        }
    }

// Function for page footer
    if (!\is_callable('page_footer')) {
        function page_footer($sDateFormat = 'Y',$sSpacer = ' - ',$sTpl = '[WEBSITE_FOOTER]',$bPrint=true)
        {
            global $iStartTime; // from initialize
            $sRetVal = '';
            $iTimeZone = StopWatch::setTimeZone('Europe/Berlin'); // 'Europe/Amsterdam'
            $iEndTime = \microtime(true);
            $oReg = WbAdaptor::getInstance();
            $iProcesstime =\round ($iEndTime - $iStartTime, 3);
            $aSearches    = ['[COPY_RIGHT]','[YEAR]','[PROCESS_TIME]','[WEBSITE_FOOTER]','[SPACER]'];
            $aReplaces   = ['©',\gmdate($sDateFormat),$iProcesstime,$oReg->WebsiteFooter,$sSpacer];
            $sRetVal     = \str_ireplace($aSearches, $aReplaces, $sTpl);
            if ($bPrint) { echo $sRetVal;} else {return $sRetVal;}
        }
    }

/* ------------------------------------------------------------------------------------------------ */
//  begin register_frontend_files
/* ------------------------------------------------------------------------------------------------ */
// callbackfunction for array_map
    function EscapeArray($item){
      global $database;
      return '\''.$database->escapeString($item) .'\'';
    }

    function registerSnippet(){
        global $database;
        $aSnippetsRec = [];
        $aAlllowedModules = [];
        $aModules = \glob (WB_PATH.'/modules/*', \GLOB_ONLYDIR|\GLOB_NOSORT);
        foreach ($aModules as $sPath){
            if (\is_readable($sPath.'/include.php')){$aAlllowedModules[] = \basename($sPath);}
        }
/* php < 8
        $sAllowedModules   = \implode(', ',
                             \array_map(function(&$item) use ($database){
                                 return '\''.$database->escapeString($item) .'\'';
                             },$aAlllowedModules));
*/
        $sAllowedModules   = \implode(',',\array_map('EscapeArray',$aAlllowedModules));

        $sql  = '
        SELECT `directory`,`function` FROM `'.TABLE_PREFIX.'addons`
        WHERE `type`=\'module\'
          AND `function` IN (\'snippet\',\'tool\')
          AND `directory` IN('.$sAllowedModules.')
          ';

        if (($oSnippets = $database->query($sql)))
        {
            $aSnippetsRec = $oSnippets->fetchAll( MYSQLI_ASSOC);
        } else {
            \trigger_error ($database->get_error(),E_USER_WARNING);
        }
        return $aSnippetsRec;
    }

/* -------------------------------------------------------------------------------------- */
    function registerFrontendPage($page_id){
        global $database;
        $aAddonsRec = [];
        // workout to included frontend.css, fronten.js and frontend_body.js in pages
            $sql  = 'SELECT DISTINCTROW `s`.`module` `directory`, `a`.`function`  FROM `'.TABLE_PREFIX.'sections` `s` '
                  . 'INNER JOIN `'.TABLE_PREFIX.'addons` `a`  '
                  . ' ON `s`.`module` = `a`.`directory`'
                  . 'WHERE `s`.`page_id` = '.(int)$page_id.' '
              .   'AND `function` IN (\'page\') '
                  . '';
            if (($oAddons = $database->query($sql)))
            {
                $aAddonsRec = $oAddons->fetchAll(MYSQLI_ASSOC);
            } else {
                \trigger_error ($database->get_error(),E_USER_WARNING);
            }
        return $aAddonsRec;
    }

    function register_frontend_SnippetCss($page_id){
        static $bLoaded   = false;
        $aRetval = [];
        if (!$bLoaded) {
            $oReg = WbAdaptor::getInstance();
            $aFrontend = registerSnippet();
            foreach($aFrontend as $aValue) {
                if (in_array($aValue['directory'], ['WBLingual'])){
                    $bSkip = filter_var(
                          $oReg->PageLanguages,
                          FILTER_VALIDATE_BOOLEAN,
                          FILTER_NULL_ON_FAILURE );
//echo (sprintf("<!-- [%03d] %s => %s -->\n",__LINE__, (int)$oReg->PageLanguages, (int)$bSkip));
                    if (($bSkip === false)){
                        continue;
                    }
                }
                $sAddonFile = 'modules/'.$aValue['directory'].'/frontend.css';
                $sAddonHiddenFile = 'modules/'.$aValue['directory'].'/.setFrontend';
                $sAddonUserFile = 'modules/'.$aValue['directory'].'/frontendUser.css';
                if (!\is_readable($oReg->AppPath.$sAddonHiddenFile.'')){
                    if (\is_readable($oReg->AppPath.$sAddonFile.'')){
//echo (sprintf("<!-- [%03d] %s -->\n",__LINE__,$oReg->AppPath.$sAddonFile));
                        $aRetval[] = $sAddonFile;
                    }
                } else {
                    $sAddonAspFile = 'modules/'.$aValue['directory'].'/aspSupported.css';
                    if (\is_readable($oReg->AppPath.$sAddonAspFile.'')){
                        $aRetval[] = $sAddonAspFile;
                    }
                }
                if (\is_readable($oReg->AppPath.$sAddonUserFile.'')){
                  $aRetval[] = $sAddonUserFile;
                }
            }//end foreach
            $bLoaded = true;
        }
        return $aRetval;
    }

    function register_account_FrontendBodyJs(){
        static $bLoaded = false;
        $aRetval = [];
        if (!$bLoaded) {
            $oReg = WbAdaptor::getInstance();
            $sAddonFile = 'account/frontend_body.js';
            $sAddonUserFile = 'account/frontendUser_body.js';
            if (\is_readable($oReg->AppPath.$sAddonFile.'')){
                $aRetval[] = $sAddonFile;
            }
          if (\is_readable($oReg->AppPath.$sAddonUserFile.'')){
              $aRetval[] = $sAddonUserFile;
          }
            $bLoaded = true;
        }
        return $aRetval;
    }


    function register_account_FrontendJs(){
        static $bLoaded = false;
        $aRetval = [];
        if (!$bLoaded) {
            $oReg = WbAdaptor::getInstance();
            $sAddonFile = 'account/frontend.js';
            $sAddonUserFile = 'account/frontendUser.js';
            if (\is_readable($oReg->AppPath.$sAddonFile.'')){
                $aRetval[] = $sAddonFile;
            }
            if (\is_readable($oReg->AppPath.$sAddonUserFile.'')){
              $aRetval[] = $sAddonUserFile;
            }
            $bLoaded = true;
        }
        return $aRetval;
    }


    function register_account_FrontendCss()
    {
        static $bLoaded = false;
        $aRetval = [];
        if (!$bLoaded) {
            $oReg = WbAdaptor::getInstance();
            $sAddonFile = 'account/frontend.css';
            $sAddonHiddenFile = 'account/.setFrontend';
            $sAddonUserFile = 'account/frontendUser.css';
            if (!\is_readable($oReg->AppPath.$sAddonHiddenFile.''))
            {
                if (\is_readable($oReg->AppPath.$sAddonFile.'')){
                    $aRetval[] = $sAddonFile;
                }
            } else {
                $sAddonAspFile = 'account/aspSupported.css';
                if (\is_readable($oReg->AppPath.$sAddonAspFile.'')){
                    $aRetval[] = $sAddonAspFile;
                }
            }
            if (\is_readable($oReg->AppPath.$sAddonUserFile.'')){
                $aRetval[] = $sAddonUserFile;
            }
            $bLoaded = true;
        }
        return $aRetval;
    }

    function register_frontend_FrontendCss($page_id){
        static $bLoaded = false;
        $aRetval = [];
        if (!$bLoaded) {
            $oReg = WbAdaptor::getInstance();
            $aFrontend = registerFrontendPage($page_id);
            foreach($aFrontend as $aValue)
            {
                $sAddonFile = 'modules/'.$aValue['directory'].'/frontend.css';
                $sAddonHiddenFile = 'modules/'.$aValue['directory'].'/.setFrontend';
                $sAddonUserFile = 'modules/'.$aValue['directory'].'/frontendUser.css';
                if (!\is_readable($oReg->AppPath.$sAddonHiddenFile.'')){
//echo (sprintf("<!-- [%03d] %s -->\n",__LINE__,$oReg->AppPath.$sAddonHiddenFile));
                    if (\is_readable($oReg->AppPath.$sAddonFile.'')){
                        $aRetval[] = $sAddonFile;
                    }
                } else {
                    $sAddonAspFile = 'modules/'.$aValue['directory'].'/aspSupported.css';
                    if (\is_readable($oReg->AppPath.$sAddonAspFile.'')){
                        $aRetval[] = $sAddonAspFile;
                    }
                }
                if (\is_readable($oReg->AppPath.$sAddonUserFile.'')){
                    $aRetval[] = $sAddonUserFile;
                }
            }//end foreach
            $aAccount = register_account_FrontendCss();
            $aRetval  = array_merge($aRetval,$aAccount);
            $bLoaded = true;
        }
        return $aRetval;
    }

    function register_frontend_FrontendJs($page_id){
        static $bLoaded = false;
        $aRetval = [];
        if (!$bLoaded) {
            $aFrontend = registerFrontendPage($page_id);
            $oReg = WbAdaptor::getInstance();
            foreach($aFrontend as $aValue) {
                $sAddonFile = 'modules/'.$aValue['directory'].'/frontend.js';
                $sAddonUserFile = 'modules/'.$aValue['directory'].'/frontendUser.js';
                if (\is_readable($oReg->AppPath.$sAddonFile.'')){
                    $aRetval[] = $sAddonFile;
                }
                if (\is_readable($oReg->AppPath.$sAddonUserFile.'')){
                  $aRetval[] = $sAddonUserFile;
                }
            }// end foreach
            $aAccount = register_account_FrontendJs();
            $aRetval  = array_merge($aRetval,$aAccount);
            $bLoaded = true;
        }
        return $aRetval;
    }

    function register_frontend_SnippetJs($page_id){
        static $bLoaded = false;
        $aRetval = [];
        if (!$bLoaded) {
            $oReg = WbAdaptor::getInstance();
            $aFrontend = registerSnippet();
            foreach($aFrontend as $aValue) {
                $sAddonFile = 'modules/'.$aValue['directory'].'/frontend.js';
                $sAddonUserFile = 'modules/'.$aValue['directory'].'/frontendUser.js';
                if (\is_readable($oReg->AppPath.$sAddonFile.'')){
                  $aRetval[] = $sAddonFile;
                }
                if (\is_readable($oReg->AppPath.$sAddonUserFile.'')){
                  $aRetval[] = $sAddonUserFile;
                }
            }// end foreach
            $bLoaded = true;
        }
        return $aRetval;
    }

    function register_frontend_SnippetBodyJs($page_id){
        static $bLoaded = false;
        $aRetval = [];
        if (!$bLoaded) {
            $oReg = WbAdaptor::getInstance();
            $aFrontend = registerSnippet();
            foreach($aFrontend as $aValue) {
                $sAddonFile = 'modules/'.$aValue['directory'].'/frontend_body.js';
                $sAddonUserFile = 'modules/'.$aValue['directory'].'/frontendUser_body.js';
                if (\is_readable($oReg->AppPath.$sAddonFile.'')){
                  $aRetval[] = $sAddonFile;
                }
                if (\is_readable($oReg->AppPath.$sAddonUserFile.'')){
                  $aRetval[] = $sAddonUserFile;
                }
            } // foreach
            $bLoaded = true;
        }
        return $aRetval;
    }

    function register_frontend_FrontendBodyJs($page_id){
        static $bLoaded = false;
        $aRetval = [];
        $aRettmp = [];
        if (!$bLoaded) {
            $oReg = WbAdaptor::getInstance();
            $aFrontend = registerFrontendPage($page_id);
            foreach($aFrontend as $aValue) {
                $sAddonFile = 'modules/'.$aValue['directory'].'/frontend_body.js';
                $sAddonUserFile = 'modules/'.$aValue['directory'].'/frontendUser_body.js';
                if (\is_readable($oReg->AppPath.$sAddonFile.'')){
                    $aRetval[] = $sAddonFile;
                }
                if (\is_readable($oReg->AppPath.$sAddonUserFile.'')){
                  $aRetval[] = $sAddonUserFile;
                }
            } // foreach
            $aAccount = register_account_FrontendBodyJs();
            $aRetval = array_merge($aRetval, $aAccount);
            $bLoaded = true;
        }
        return $aRetval;
    }

/* -------------------------------------------------------------------------------------- */
    function include_snippet($sModuleName=''){
        static $isIncluded = [];
        $aRetVal = [];
        if (!\in_array($sModuleName, $isIncluded))
        {
            $oReg = WbAdaptor::getInstance();
            if (\is_readable($oReg->AppPath.'/modules/'.$sModuleName.'/include.php'))
            {
                $sRetVal = 'modules/'.$sModuleName.'/include.php';
                include ($oReg->AppPath.$sRetVal);
                // check if already exists
                \array_push ($isIncluded, $sModuleName);
                $aRetVal = $isIncluded;
            }
        }
       return $aRetVal;
    }

/* -------------------------------------------------------- */
    function register_frontend_LoadOnFly ()
    {
        static $bSriptLoaded=false;
        $sLoadOnFly = [];
        if (!$bSriptLoaded){
            $oReg = WbAdaptor::getInstance();
            $sLoadOnFly[] = 'include/jquery/domReady.js';
            $sLoadOnFly[] = 'include/jquery/LoadOnFly.js';   // -min
            $bSriptLoaded = true;
        }
        return $sLoadOnFly;
    }

/* -------------------------------------------------------- */
    function register_frontend_ScriptVars ()
    {
        static $bSriptLoaded=false;
        $sScriptVars = [];
        if ($bSriptLoaded){ return $sScriptVars; }
            $sScriptVars[] = ''
                ."\n\t\t\t".'/* inserted by register_frontend_modfiles '.(WB_VERSION ?? '').' '.(WB_SP ?? '').' */'."\n"
                ."\t\t\t"."var URL = '".WB_URL."';\n"
                ."\t\t\t"."var WB_URL = '".WB_URL."';\n"
                ."\t\t\t"."var WB_REL = '".WB_REL."';\n"
                ."\t\t\t"."var THEME_URL = '".THEME_URL."';\n"
                ."\t\t\t"."var TEMPLATE_DIR = '".TEMPLATE_DIR."';\n"
                ."\t\t\t"."var TEMPLATE = '".TEMPLATE."';\n"
                ."\t\t\t"."var EDITOR = '".WYSIWYG_EDITOR."';\n"
                ."\t\t\t"."var LANGUAGE = '".LANGUAGE."';\n"
                .'';
            $bSriptLoaded = true;
        return $sScriptVars;
    }

/* -------------------------------------------------------- */
function register_frontend_Jquery ()
{
    static $bLoaded = false;
    $jquery_links = [];
    if (!$bLoaded) {
        $oReg = WbAdaptor::getInstance();
        $jquery_version    = ((\defined('JQUERY_VERSION') && !empty(JQUERY_VERSION) ? JQUERY_VERSION : '1.12.4')).'/';
        $jqueryIncludePath = 'include/jquery/';
        $jqueryVersionPath = $jqueryIncludePath.'dist/'.$jquery_version;
        /* include the Javascript jquery api  */
        if (\file_exists($oReg->AppPath.$jqueryVersionPath.'jquery-min.js'))
        {
            $aFilterSettings = getOutputFilterSettings();
            $bLoadJqUi = (isset($aFilterSettings['JqueryUI']) && $aFilterSettings['JqueryUI']);
            $jquery_links[] = $jqueryVersionPath.'jquery-min.js';
            $jquery_links[] = $jqueryVersionPath.'jquery-migrate-min.js';
            /* workout to insert ui.css and theme */
            if ($bLoadJqUi){
                // only  load css
                $jquery_theme   =  'modules/jquery/jquery_theme.js';
                $jquery_uitheme =  'modules/jquery/jquery-ui-min.js';

                $jquery_links[] =  (\file_exists($oReg->AppPath.$jquery_uitheme)
                    ? 'modules/jquery/jquery-ui-min.js'
                    : $jqueryIncludePath.'jquery-ui-min.js');
                $jquery_links[] =  (\file_exists($oReg->AppPath.$jquery_theme)
                    ? 'modules/jquery/jquery_theme.js'
                    : $jqueryIncludePath.'jquery_theme.js');
            }
            $jquery_links[] = $jqueryIncludePath.'jquery-insert.js';
            $jquery_links[] = $jqueryIncludePath.'jquery-include.js';
        /* workout to insert plugins functions, set in templatedir */
            $jquery_frontend_file = '';
            $jquery_frontend_file = $jqueryIncludePath.'jquery_frontend.js';
            $jquery_frontend_file = \str_replace ($oReg->AppUrl, '', $oReg->TemplateDir.'jquery_frontend.js');
            $sTmp = (\is_readable($oReg->AppPath.''.$jquery_frontend_file)
                ? $jquery_frontend_file
                : '');
            if (!empty($sTmp)){
                $jquery_links[] = $sTmp;
            }
        $bLoaded = true;
        }
    }
    return $jquery_links;
}

/* ------------------------------------------------------------------------------------- */
    // if you need to include snippets before calling by register_modfiles_frontend
    function registerSnippets($page_id){
        $aRetVal  = [];
        $aSnippets = registerSnippet($page_id);
        foreach($aSnippets as $aModuleDir) {
            $sModuleDir = $aModuleDir['directory'];
            $aRetVal = include_snippet($sModuleDir);
        }

        return $aRetVal;
    }
/*---------------------------------------------------------------------------------------*/
    function checkResult($mResults, $sFilter){
        $aRetVal[$sFilter] = [];
        $aTmp = (\is_array($mResults[$sFilter]) ? $mResults[$sFilter] : $aRetVal[$sFilter]);
        return $mResults;
    }
/* ------------------------------------------------------------------------------------- */
    function register_addon_files(
            array $aFilter = [
                    'SnippetCss',
                    'FrontendCss',
                    'ScriptVars',
                    'LoadOnFly',
                    'Jquery',
                    'JqueryUI',
                    'SnippetJs',
                    'FrontendJs',
                    'SnippetBodyJs',
                    'FrontendBodyJs',
            ],
            array $aSetFilter = [
            ]
        ){
        global $page_id;
        $aFrontendFiles  = [];
        $aFilters  = [];
        $aSettings = [];
        $aSeekFilters = array_flip($aFilter);
        $aTmpSettings = getOutputFilterSettings();
//        if (!empty($aSetFilter)){}
          array_walk(
              $aTmpSettings,
              function(& $iItem, $sKey) use ($aSetFilter,& $aSettings){
                $bForceActive = in_array($sKey, $aSetFilter);
                $aSettings[$sKey] = ($bForceActive ? 1 : $iItem);
              }
          );
        $aFilters  = array_intersect_key($aSettings, $aSeekFilters);
        $aResults  = [];
        foreach ($aFilters as $sName => $bValue) {
            $aResults[$sName] = [];
            if ($bValue) {
                $sFunction = 'register_frontend_'.$sName;
                if (is_callable($sFunction)) {
                    $aResults[$sName] = $sFunction($page_id);
                }
            }
        }
        foreach ($aSeekFilters as $sName=>$bValue) {
                if (isset($aResults[$sName])){
//                    echo $sName,' = ', (int)$aFilters[$sName].'<br />';  getResult($aResults, $sName)
                    switch ($sName)
                    {
                    case 'SnippetCss':
                    case 'FrontendCss':
                        $aResults = checkResult($aResults, $sName);
                        $aFrontendFiles[$sName] = array_unique($aResults[$sName]);
                        break;
                    case 'ScriptVars':
                    case 'LoadOnFly':
                        $aResults = checkResult($aResults, $sName);
                        $aFrontendFiles[$sName] = ($aResults[$sName]);
                        break;
                    case 'Jquery':
                    case 'JqueryUI':
                        $aResults = checkResult($aResults, $sName);
                        $aFrontendFiles[$sName] = ($aResults[$sName]);
                        break;
                    case 'SnippetJs':
                    case 'FrontendJs':
                        $aResults = checkResult($aResults, $sName);
                        $aFrontendFiles[$sName] = array_unique($aResults[$sName]);
                        break;
                    case 'SnippetBodyJs':
                    case 'FrontendBodyJs':
                        $aResults = checkResult($aResults, $sName);
                        $aFrontendFiles[$sName] = array_unique($aResults[$sName]);
                        break;
                    }
                }
        } // end foreach
        return $aFrontendFiles;
    }
/* --------------------------------------------------------------------------------------*/
    function prepareLink(array $aOutput, $sFileType){
      $sRetval = '';
      if (is_array($aOutput) && !empty($aOutput)) {
          $oReg = WbAdaptor::getInstance();
          foreach($aOutput as $iKey => $sLinks){
              if (!empty($sLinks)){
                  switch ($sFileType) {
                      case 'css':
                          $sRetval .= "\t\t".'<link rel="stylesheet" href="'.$oReg->AppUrl.''.$sLinks.'" media="screen" />'."\n";
                          break;
                      case 'js':
//                          $sRetval = (sprintf("<!-- [%03d] %s %s.%s -->\n",__LINE__,'FileType script in prepareLink',$sLinks,$sFileType));
                          $sRetval .= "\n".'<script src="'.$oReg->AppUrl.$sLinks.'"></script>'."";
                          break;  //
                      case 'var':
                          $sRetval .= "\t".'<script>'.$sLinks."\t".'</script>'."\n";  // \t
                          break;  //
                      default:
                          $sRetval = (sprintf("<!-- [%03d] %s %s.%s -->\n",__LINE__,'FileType error in prepareLink',$sLinks,$sFileType));
                          break;  //
                  }  // end switch
              }
          }// end foreach
      }
      return $sRetval;
    }
/* ------------------------------------------------------------------------------------*/
// Function to add optional module Javascript or CSS stylesheets into the <head> section of the frontend
    if (!is_callable('register_frontend_modfiles')) {
        function register_frontend_modfiles($sFileType="css", $bOutput=true)
        {
            $head_links = '';
// read settings from the database to enable/disable scripts
            $aFilterSettings = getOutputFilterSettings();
            $bRegisterFrontendFiles = ($aFilterSettings['RegisterModFiles'] ?? false);
            if (!$bRegisterFrontendFiles){
                switch (strtolower($sFileType)) {
                    case 'css':
                        if (!defined('MOD_FRONTEND_CSS_REGISTERED')) {
                            define('MOD_FRONTEND_CSS_REGISTERED', true);
                        }
/* ------------------------------------------------------------------------- */
                        $aFilter = [
                          'SnippetCss',
                          'FrontendCss',
                          'ScriptVars',
                          'LoadOnFly',
                          'Jquery',
                          'JqueryUI',
                          'SnippetJs',
                          'FrontendJs',
                        ];
/* ------------------------------------------------------------------------- */
// second array parameter set this css always to av´ctive, all other have to checked in the list
                        $aSnippetRecords = register_addon_files($aFilter,['SnippetCss','FrontendCss']);
/* ------------------------------------------------------------------------- */
                        foreach ($aSnippetRecords as $sKey => $aItem){
                            $sExt = '';
                            $sExt = (in_array($sKey,['SnippetCss','FrontendCss']) ? 'css' : $sExt);
                            $sExt = (in_array($sKey,['ScriptVars']) ? 'var' : $sExt);
                            $sExt = (in_array($sKey,['LoadOnFly','Jquery','JqueryUI','SnippetJs','FrontendJs']) ? 'js' : $sExt);
                            $head_links .= prepareLink($aItem, $sExt);
                        }// end foreach $aSnippetRecords
               } // endswitch
            } else {
                switch (strtolower($sFileType)) {
                    case 'css':
                        if (!defined('MOD_FRONTEND_CSS_REGISTERED')) {
                            define('MOD_FRONTEND_CSS_REGISTERED', true);
                        }
                        $aFilter = [
                          'SnippetCss',
                          'FrontendCss',
                          'ScriptVars',
                          'LoadOnFly',
                        ];
// set css always to true
                        $aSnippetRecords = register_addon_files($aFilter,$aFilter);
                        foreach ($aSnippetRecords as $sKey => $aItem){
                            $sExt = '';
                            $sExt = (in_array($sKey,['SnippetCss','FrontendCss']) ? 'css' : $sExt);
                            $sExt = (in_array($sKey,['ScriptVars']) ? 'var' : $sExt);
                            $sExt = (in_array($sKey,['LoadOnFly','SnippetJs','FrontendJs']) ? 'js' : $sExt);
                            $head_links .= prepareLink($aItem, $sExt);
                        }// end foreach $aSnippetRecords
                        break;
                    case 'jquery':
                        if (!defined('MOD_FRONTEND_JAVASCRIPT_REGISTERED')) {
                            define('MOD_FRONTEND_JAVASCRIPT_REGISTERED', true);
                        }
                        $aJsFilter = [
                          'Jquery',
                          'JqueryUI',
                        ];
                        $aSnippetRecords = register_addon_files($aJsFilter,$aJsFilter);//
                        $aOutput = array_merge($aSnippetRecords['Jquery'],$aSnippetRecords['JqueryUI']);
                        $head_links .= prepareLink($aOutput, 'js');
//                        echo (sprintf("<!-- [%03d] %s -->\n",__LINE__,$head_links));
                        break;
                    case 'js':   // (isset($aSnippetRecords['js_head'])?$aSnippetRecords['js_head']:[])
                        $aJsFilter = [
                          'SnippetJs',
                          'FrontendJs'
                        ];
                        if (!defined('MOD_FRONTEND_JAVASCRIPT_REGISTERED')) {
                            define('MOD_FRONTEND_JAVASCRIPT_REGISTERED', true);
                        }
                        $aSnippetRecords = register_addon_files($aJsFilter,$aJsFilter);
                        $aOutput = array_merge($aSnippetRecords['SnippetJs'], $aSnippetRecords['FrontendJs']);
                        $head_links .= prepareLink($aOutput, 'js');
                        break;
                    default:
                        break;  //
                }  // endswitch
             }
            if ($bOutput) { print $head_links."\n";} else {return $head_links."\n";}
        }
    }
/* ------------------------------------------------------------------------------------*/
// Function to add optional module Javascript into the <body> section of the frontend
    if (!is_callable('register_frontend_modfiles_body')) {
        function register_frontend_modfiles_body($sFileType="js", $bOutput=true)
        {
            $body_links = '';
            $aFilterSettings = getOutputFilterSettings();
            $bRegisterFrontendFiles = ($aFilterSettings['RegisterModFiles'] ?? false);
            switch (strtolower($sFileType))
            {
                case 'jquery':
//                    if ($bRegisterFrontendFiles){}
                    if (!defined('MOD_FRONTEND_BODY_JQUERY_REGISTERED')) {
                        define('MOD_FRONTEND_BODY_JQUERY_REGISTERED', true);
                    }
                    $aFilter = [
                      'Jquery',
                      'JqueryUI',
                    ];
                    $aSnippetRecords = register_addon_files($aFilter,$aFilter);
                    $aOutput = array_merge($aSnippetRecords['Jquery'],$aSnippetRecords['JqueryUI']);
                    $body_links .= prepareLink($aOutput, 'js');
                    break;
                case 'js':
//                  define constant indicating that the register_frontent_files_body was invoked
                    if (!defined('MOD_FRONTEND_BODY_JAVASCRIPT_REGISTERED')) {
                        define('MOD_FRONTEND_BODY_JAVASCRIPT_REGISTERED', true);
                    }
                    $aFilter = [
                      'SnippetBodyJs',
                      'FrontendBodyJs',
                    ];
                    $aSnippetRecords = register_addon_files($aFilter,$aFilter);
                    $aOutput = array_merge($aSnippetRecords['SnippetBodyJs'], $aSnippetRecords['FrontendBodyJs']);
                    $body_links .= prepareLink($aOutput, 'js');
// echo nl2br(sprintf("<!-- <div class='w3-white w3-border w3-padding'>[%03d] %s</div> -->\n",__LINE__,$body_links));
                    break;
                default:
                    break;
            } // end switch
            if ($bOutput) { print $body_links."";} else {return $body_links."";}
        }
    }
/* --------------------------------------------------------------------------------------*/
    function moveCssToHead($content) {
       return OutputFilterApi('CssToHead', $sContent);
    }
