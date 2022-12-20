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
 */
/**
 * PageTree generator
 *
 * @category     WbACP
 * @package      WbACP_Pages
 * @author       Werner v.d.Decken
 * @copyright    Werner v.d.Decken
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      1.0.0
 * @revision     $Revision: 2070 $
 * @link         $HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/branches/2.8.x/wb/admin/pages/PageTree.php $
 * @lastmodified $Date: 2014-01-03 02:21:42 +0100 (Fr, 03. Jan 2014) $
 * @since        file added on 2012-12-21
 */
namespace bin\helpers;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

class PageTree
{
/** @var object instance of the application object */
    protected $_oApp;
/** @var object instance of the database object */
    protected $_oDb ;
/** @var object instance holds several values from the application global scope */
    protected $_oReg;
/** @var object instance holds all of the translations */
    protected $_oTrans;
/** @var string full HTML formattet list of pages */
    protected $_sOutput         = '';
/** @var integer number of all reachable pages */
    protected $_iPagesTotal     = 0;
/** @var integer number of all writeable pages */
    protected $_iPagesWriteable = 0;
/** @var integer index for toggle background color of the list */
    protected $_iLineColor      = 0;
/** @var array entries to build a select list for parents */
    protected $_aParentList     = [];
/** @var integer count all executed database requests passing all iterations. */
    protected $_queries = 0;
/**
 * constructor used to import some application constants and objects
 */
    public function __construct()
    {
        // import global vars and objects
        $this->_oReg   = WbAdaptor::getInstance();
        $this->_oDb    = $this->_oReg->getDatabase();
        $this->_oTrans = $this->_oReg->getTranslate();
        $this->_oApp   = $this->_oReg->getApplication();
    }
/**
 * parse the page tree and return
 * @param int use page-ID as root of the generated page tree. (default: 0)
 * @return type
 */
    public function parseTree($iTreeRoot = 0) {
        return $this->_createTree($iTreeRoot);
    }
/**
 *  parse the page tree and print it out
 * @param int use page-ID as root of the generated page tree. (default: 0)
 */
    public function displayTree($iTreeRoot = 0) {
        echo $this->parseTree($iTreeRoot);
    }
/**
 * total number of found pages which are visible for actual user
 * @return integer
 */
    public function getTotalPages() {
        return $this->_iPagesTotal;
    }
/**
 * number of found pages which are writable for actual user
 * @return integer
 */
    public function getWriteablePages() {
        return $this->_iPagesWriteable;
    }

/**
 * a list with all possible parent pages
 * @return array
 */
    public function getParentList($iTreeRoot = 0) {
        if (!$this->_sOutput ) {
            $this->parseTree($iTreeRoot);
        }
        return $this->_aParentList;
    }
/**
 * create a page tree as a well formatted, unordered list
 * @param int use page-ID as root of the generated page tree. (default: 0)
 * @return string the whoole list
 */
    protected function _createTree($iTreeRoot = 0)
    {
        // compose the complete list
        $sOutput = ''
        // build the head
              . $this->_Tabs(0 , true).'<div class="jsadmin pages_list">'.PHP_EOL
              . $this->_Tabs(1).'<table>'.PHP_EOL
              . $this->_Tabs(1).'<tbody>'.PHP_EOL
              . $this->_Tabs(1).'<tr class="pages_list_header">'.PHP_EOL
              . $this->_Tabs(1).'<th style="width:20px;">'.PHP_EOL.'</th>'.PHP_EOL
              . $this->_Tabs(1).'<th class="list_menu_title">'.$this->_oTrans->TEXT_VISIBILITY.
                                ' / '.$this->_oTrans->TEXT_MENU_TITLE.':</th>'.PHP_EOL
              . $this->_Tabs(0).'<th class="list_page_title">'.$this->_oTrans->TEXT_PAGE_TITLE.
                                '</th>'.PHP_EOL
              . $this->_Tabs(0).'<th class="list_page_id">PID</th>'.PHP_EOL
              . $this->_Tabs(0).'<th class="header_list_actions">'.$this->_oTrans->TEXT_ACTIONS.
                                ':</th>'.PHP_EOL
              . $this->_Tabs(0).'<th class="list_page_id">&nbsp;</th>'.PHP_EOL
              . $this->_Tabs(-1).'</tr>'.PHP_EOL
              . $this->_Tabs(-1).'</tbody>'.PHP_EOL
              . $this->_Tabs(-1).'</table>'.PHP_EOL
        // generate the page lines
              . $this->_IterateTree($iTreeRoot)
        // build the footer
              . $this->_Tabs(-1).'</div>'.PHP_EOL;
        ;
        $this->_sOutput = $sOutput;
        return $sOutput;
    }
/**
 * Create a string of multiple TABs to prettify the HTML-output
 * ingrease the number of TABs with a positive and degrease with an negative Value.
 * '0' means: do not change the value. Or set an absolute number using $bRelative=false
 * @staticvar int $iTabLevel
 * @param integer $iTabsDiv number of TABs to add/sub
 * @param bool $bRelative false if should be set to absolute value
 * @return string
 */
    protected function _Tabs($iTabsDiv = 0, $bRelative = true)
    {
        static $iTabLevel = 0;
        $iTabLevel = ($bRelative ? $iTabLevel + $iTabsDiv : $iTabsDiv);
        $iTabLevel += ($iTabLevel < 0 ? 0 - $iTabLevel : $iTabsDiv);
        return str_repeat("\t", $iTabLevel);
    }

/*
*/

    protected function _make_Sql()
    {
        $bProcessSeo = FALSE;
        $sUseTrash = ($this->_oReg->PageTrash != 'inline') ? " WHERE `visibility` <> 'deleted'" : '';
        $sProcessSeo = ($bProcessSeo == TRUE) ? ', p.`description`, p.`keywords` ' : ' ';
        $sProcessRewriteUrl = '';
        $bRewriteUrlExists = FALSE;
        if (defined("REWRITE_URL") == TRUE && REWRITE_URL != ''){
            $oCheckDbTable = $database->query("SHOW COLUMNS FROM `".$this->_oDb->TablePrefix."pages` LIKE '".REWRITE_URL."'");
            $bRewriteUrlExists = $oCheckDbTable->numRows() > 0 ? TRUE : FALSE;
            $sProcessRewriteUrl = ($bRewriteUrlExists == TRUE) ? ' p.`'.REWRITE_URL.'`,' : '';
        }
        $sql = 'SELECT DISTINCT s.`module`, MAX(s.`publ_start` + s.`publ_end`) published, p.`link`, '.PHP_EOL
             .        '(SELECT MAX(`position`) FROM `'. $this->_oDb->TablePrefix .'pages` WHERE `parent`=p.`parent`) children, '.PHP_EOL
             .        'p.`position`, p.`page_id`, p.`parent`, p.`level`, p.`language`, p.`admin_groups`, '.PHP_EOL
             .    (($bRewriteUrlExists == TRUE ) ? 'p.`'.REWRITE_URL.'`, ' : '' ) .''.PHP_EOL
             .        'p.`admin_users`, p.`viewing_groups`, p.`viewing_users`, p.`visibility`, '.PHP_EOL
             .        'p.`menu_title`, p.`page_title`, p.`page_trail`'.$sProcessSeo.''.PHP_EOL
             . 'FROM `'. $this->_oDb->TablePrefix .'pages` p '.PHP_EOL
             .    'INNER JOIN `'. $this->_oDb->TablePrefix .'sections` s '.PHP_EOL
             .    'ON p.`page_id`=s.`page_id` '.PHP_EOL
              .    (($this->_oReg->PageTrash != 'inline') ? 'WHERE `visibility`!=\'deleted\' ' : '').PHP_EOL
             . 'GROUP BY p.`page_id`,s.`module` '.PHP_EOL
             . 'ORDER BY p.`position` ASC'.PHP_EOL;
        return $sql;
    }

/**
 * compose the needed SQL statement
 * @param  integer $iParentKey
 * @return string SQL statement
 */
    protected function _make__Sql($iParentKey = 0)
    {
        $iParentKey = intval($iParentKey);
        $sql  = 'SELECT DISTINCT s.`module`,( SELECT COUNT(*) '
              .          'FROM `'.$this->_oDb->TablePrefix.'pages` `x` '.PHP_EOL
              .          'WHERE x.`parent`=p.`page_id`'.PHP_EOL
              .        ') `children`, '.PHP_EOL
              .        's.`module`, MAX(s.`publ_start` + s.`publ_end`) published, p.`link`, '.PHP_EOL
              .        '(SELECT MAX(`position`) FROM `'.$this->_oDb->TablePrefix.'pages` '.PHP_EOL
              .        'WHERE `parent`='.$iParentKey.') max_position, '.PHP_EOL
              .        '0 min_position, '.PHP_EOL
              .        'p.`position`, '.PHP_EOL
              .        'p.`page_id`, p.`parent`, p.`level`, p.`language`, p.`admin_groups`, '.PHP_EOL
              .        'p.`admin_users`, p.`viewing_groups`, p.`viewing_users`, p.`visibility`, '.PHP_EOL
              .        'p.`menu_title`, p.`page_title`, p.`page_trail`, '.PHP_EOL
              .        'GROUP_CONCAT(CAST(CONCAT(s.`section_id`, \' - \', s.`module`) AS CHAR) ORDER BY s.`position` SEPARATOR \'\n\') `section_list` '.PHP_EOL
              . 'FROM `'.$this->_oDb->TablePrefix.'pages` p '.PHP_EOL
              .    'INNER JOIN `'.$this->_oDb->TablePrefix.'sections` s '.PHP_EOL
              .    'ON p.`page_id`=s.`page_id` '.PHP_EOL
              . 'WHERE `parent`='.$iParentKey.' '.PHP_EOL
              .    (($this->_oReg->PageTrash != 'inline') ? 'AND `visibility`!=\'deleted\' ' : '').PHP_EOL
              . 'GROUP BY p.`page_id`, p.`language`,p.`parent`, p.`level`, `children`, p.`admin_groups`, '.PHP_EOL
              . 'p.`admin_users`, p.`viewing_groups`, p.`viewing_users`, p.`visibility`, '.PHP_EOL
              . 'p.`menu_title`, p.`page_title`, p.`page_trail`,s.`module` '.PHP_EOL
              . 'ORDER BY p.`position` ASC'.PHP_EOL;
        return $sql;
    }

/**
 * compose the needed SQL statement
 * @param  integer $iParentKey
 * @return string SQL statement
 */
    protected function _makeSql()
    {
        $sql  = 'SELECT DISTINCT p.`language`, MAX(s.`publ_start` + s.`publ_end`) published, '.PHP_EOL
              . 'p.`link`, '.PHP_EOL
              . '(SELECT MAX(`position`) FROM `'.$this->_oDb->TablePrefix.'pages` '.PHP_EOL
              .    'WHERE `parent`=p.`parent`'.PHP_EOL
              .    (($this->_oReg->PageTrash != 'inline') ? 'AND `visibility`!=\'deleted\' ' : '').PHP_EOL
              . ') children, '.PHP_EOL
              . 'p.`level`, p.`position`, p.`page_id`, p.`parent`, p.`page_trail`, '.PHP_EOL
              . 'p.`admin_groups`, p.`admin_users`, p.`viewing_groups`, p.`viewing_users`, p.`visibility`, '.PHP_EOL
              . 'p.`menu_title`, p.`page_title` '.PHP_EOL
              . 'FROM `'.$this->_oDb->TablePrefix.'pages` p '.PHP_EOL
              . 'INNER JOIN `'.$this->_oDb->TablePrefix.'sections` s '.PHP_EOL
              . 'ON p.`page_id`=s.`page_id` '.PHP_EOL
              . 'GROUP BY p.`language`, p.`page_id` '.PHP_EOL
              . 'ORDER BY p.`language`,p.`page_trail` ASC '.PHP_EOL;
        return $sql;
    }

/**
 * a list with all possible  pages
 * @return array
 */
    public function getPageList($iTreeRoot = 0) {
        $aPages = [];
        if(($oPages = $this->_oDb->query($this->_makeSql())))
        {
            while($aPage = $oPages->fetchRow(MYSQLI_ASSOC))
            { // iterate through the current branch
                if($this->_oReg->PageLevelLimit && ($aPage['level'] <= $this->_oReg->PageLevelLimit)) {
                    $aPages[] = $aPage;
                }
            }
        }
        $this->_aParentList = $aPages;
        return $this->_aParentList;
    }

/**
 * iterate through all nodes which having subnodes
 * @param integer start iteration from this parent page ( 0 = root)
 * @return string all of the item lines
 */
    protected function _IterateTree($iParent = 0)
    {
        $sOutput = '';
        // Get page list from database
        if(($oPages = $this->_oDb->query($this->_makeSql($iParent))))
        {
            $this->_queries++;
            // output block-header
            $sOutput .= $this->_Tabs(0).'<ul id="p'.$iParent.'" class="page_list"';
            if(!$iParent) {
                $sOutput .= ' style="display: block;"';
            }else {
            // show block depending from Cookies
                if (isset ($_COOKIE['p'.$iParent]) && $_COOKIE['p'.$iParent] == '1') {
                    $sOutput .= ' style="display: block;"';
                }
            }
            $sOutput .= '>'.PHP_EOL;
            $iMinPosition = 1;
            while($aPage = $oPages->fetchRow(MYSQLI_ASSOC))
            { // iterate through the current branch
                if($this->_oReg->PageLevelLimit && ($aPage['level'] > $this->_oReg->PageLevelLimit)) {
                    return '';
                }
                $aPage['min_position'] = ($aPage['position'] < $iMinPosition ? $aPage['position'] : $iMinPosition);
                $this->_iLineColor = $this->_iPagesTotal++ % 2;
                $aPage['iswriteable'] = false;
                if( $this->_oApp->ami_group_member($aPage['admin_users']) ||
                    $this->_oApp->is_group_match($this->_oApp->get_groups_id(), $aPage['admin_groups']))
                {
                    if(($aPage['visibility'] == 'deleted' && $this->_oReg->PageTrash == 'inline') ||
                       ($aPage['visibility'] != 'deleted'))
                    {
                        $aPage['iswriteable'] = true;
                        $this->_iPagesWriteable++;
                    }
                } else {
                    if($aPage['visibility'] == 'private') { continue; }
                }
                // add this item to the secondary list of parents
                $this->_addToParentList($aPage);
                $sOutput .= $this->_createListItem($aPage);
            }
            $sOutput .= $this->_Tabs(-1).'</ul>'.PHP_EOL;
        }
        return $sOutput;
    }
/**
 * formating the given page object for output
 * @param type $aPage
 * @return string
 */
    protected function _createListItem($aPage)
    {
    // output the current item
    // --- HEADER ------------------------------------------------------------------------
        $sOutput  = $this->_Tabs(0).'<li class="p'.$aPage['parent'].'">'.PHP_EOL
                  . $this->_Tabs(1).'<table class="pages_view">'.PHP_EOL
                  . $this->_Tabs(1).'<tbody>'.PHP_EOL
                  . $this->_Tabs(1).'<tr class="row_'.$this->_iLineColor.'">'.PHP_EOL;
    // --- TAB 1 --- (expand/collapse) ---------------------------------------------------
        $sOutput .= $this->_Tabs(1).'<td valign="middle" width="20" style="padding-left: '
                  . (int)($aPage['level']*20).'px;">';
        if((bool)$aPage['children']) {
            $sOutput .= '<a onclick="toggle_visibility(\'p'.$aPage['page_id'].'\');" '
                      . 'title="'.$this->_oTrans->TEXT_EXPAND.'/'.$this->_oTrans->TEXT_COLLAPSE.'">'
                      . '<span><img src="'.$this->_oReg->ThemeRel.'images/'
                      . ( ((isset($_COOKIE['p'.$aPage['page_id']])
                          && $_COOKIE['p'.$aPage['page_id']] == '1') ? 'minus' : 'plus')
                        )
                      . '_16.png" onclick="toggle_plus_minus(\''.$aPage['page_id'].'\');" '
                      . 'name="plus_minus_'.$aPage['page_id'].'" alt="+" /></span></a>';
        }else {
            $sOutput .= '&nbsp;';
        }
        $sOutput .= '</td>'.PHP_EOL;
    // --- TAB 2 --- (menu title) --------------------------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_menu_title">';
        if ($this->_oApp->get_permission('pages_modify') && $aPage['iswriteable']) {
            $sOutput .= '<a href="'.$this->_oReg->AcpRel.'pages/modify.php?page_id='
                      . $aPage['page_id'].'" title="'.$this->_oTrans->TEXT_MODIFY.'">';
        }
        $sIcon = $this->_oReg->ThemeRel.'images/'.$aPage['visibility'].'_16.png';
        if (!is_readable(rtrim($this->_oReg->DocumentRoot, '/').$sIcon)) {
            $sIcon = $this->_oReg->ThemeRel.'images/public_16.png';
        }
        $sOutput .= '<img src="'.$sIcon.'" alt="'.$this->_oTrans->TEXT_VISIBILITY.': '
                  . $this->_oTrans->{'TEXT_'.strtoupper($aPage['visibility'])}  .'" class="page_list_rights" />';
        if ($this->_oApp->get_permission('pages_modify') && $aPage['iswriteable']) {
            $sOutput .= '<span class="modify_link">'.$aPage['menu_title'].'</span></a>';
        }else {
            $sOutput .=  '<span class="bold grey">'.$aPage['menu_title'].'</span>';
        }
        $sOutput .= '</td>'.PHP_EOL;
    // --- TAB 3 --- (page title) --------------------------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_page_title">'.$aPage['page_title'].'</td>'.PHP_EOL;
    // --- TAB 4 --- (page ID) -----------------------------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_page_id right">'.$aPage['page_id'].'</td>'.PHP_EOL;
    // --- TAB 5 --- (show this page in new window) --------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_actions">';
        if ($aPage['visibility'] != 'deleted' && $aPage['visibility'] != 'none') {
            $sPageLink = $this->_oReg->AppRel.preg_replace(
                                             '/^'.preg_quote($this->_oReg->AppUrl, '/').'/siU',
                                             '',
                                             $this->_oApp->page_link($aPage['link'])
                                            );
            $sOutput .= '<a href="'.$sPageLink.'" target="_blank" title="'.$this->_oTrans->TEXT_VIEW
                      . '"><img src="'.$this->_oReg->ThemeRel.'images/view_16.png" alt="'
                      . $this->_oTrans->TEXT_VIEW.'" /></a>';
        }else {
            $sOutput .= '<img src="'.$this->_oReg->ThemeRel.'images/blank_16.gif" alt=" " />';
        }
        $sOutput .= '</td>'.PHP_EOL;

    // --- TAB 6 --- (edit settings) -----------------------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_actions">';
        if ($aPage['visibility'] != 'deleted') {
            if ($this->_oApp->get_permission('pages_settings') && $aPage['iswriteable']) {
                $sOutput .= '<a href="'.$this->_oReg->AcpRel.'pages/settings.php?page_id='
                          . $aPage['page_id'].'" title="'.$this->_oTrans->TEXT_SETTINGS.'">'
                          . '<img src="'.$this->_oReg->ThemeRel.'images/modify_16.png" alt="'
                          . $this->_oTrans->TEXT_SETTINGS.'" /></a>';
            }
        }else {
            $sOutput .= '<a href="'.$this->_oReg->AcpRel.'pages/restore.php?page_id='.$aPage['page_id'].'" '
                      . 'title="'.$this->_oTrans->TEXT_RESTORE.'"><img src="'.$this->_oReg->ThemeRel
                      . 'images/restore_16.png" alt="'.$this->_oTrans->TEXT_RESTORE.'" /></a>';
        }
        $sOutput .= '</td>'.PHP_EOL;

    // --- TAB 7 --- (edit sections) -----------------------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_actions">';
        if ($this->_oReg->ManageSections && $this->_oApp->get_permission('pages_add') && $aPage['iswriteable'] ) {
            $file = $this->_oApp->page_is_active($aPage) ? "clock_16.png" : "clock_red_16.png";
            $file = ($aPage['published'] && $aPage['module'] != 'menu_link') ? $file : 'noclock_16.png';
            $sOutput .= '<a href="'.$this->_oReg->AcpRel.'pages/sections.php?page_id='
                      . $aPage['page_id'].'" title="'.$this->_oTrans->HEADING_MANAGE_SECTIONS."\n".$aPage['section_list'].'">'
                      . '<img src="'.$this->_oReg->ThemeRel.'images/'.$file.'" alt="'
                      . $this->_oTrans->HEADING_MANAGE_SECTIONS."\n".$aPage['section_list'].'" /></a>';
        }else {
            $sOutput .= '<img src="'.$this->_oReg->ThemeRel.'images/blank_16.gif" alt=" " />';
        }
        $sOutput .= '</td>'.PHP_EOL;

    // --- TAB 8 --- (move up) -----------------------------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_actions">';
        if ($aPage['position'] > $aPage['min_position']) {
            if ($aPage['visibility'] != 'deleted') {
                if ($this->_oApp->get_permission('pages_settings') && $aPage['iswriteable']) {
                    $sOutput .= '<a href="'.$this->_oReg->AcpRel.'pages/move_up.php?page_id='
                              . $aPage['page_id'].'" title="'.$this->_oTrans->TEXT_MOVE_UP
                              . '"><img src="'.$this->_oReg->ThemeRel.'images/up_16.png" alt="'
                              . $this->_oTrans->TEXT_MOVE_UP.'" /></a>';
                }
            }
        }
        $sOutput .= '</td>'.PHP_EOL;

    // --- TAB 9 --- (move down) ---------------------------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_actions">';
        if ($aPage['position'] < $aPage['max_position']) {
            if ($aPage['visibility'] != 'deleted') {
                if ($this->_oApp->get_permission('pages_settings') && $aPage['iswriteable']) {
                    $sOutput .= '<a href="'.$this->_oReg->AcpRel.'pages/move_down.php?page_id='
                              . $aPage['page_id'].'" title="'.$this->_oTrans->TEXT_MOVE_DOWN
                              . '"><img src="'.$this->_oReg->ThemeRel.'images/down_16.png" alt="'
                              . $this->_oTrans->TEXT_MOVE_DOWN.'" /></a>';
                }
            }
        }
        $sOutput .= '</td>'.PHP_EOL;

    // --- TAB 10 --- (delete page) ------------------------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_actions">';
        if($this->_oApp->get_permission('pages_delete') && $aPage['iswriteable']) {
            $sOutput .= '<a onclick="confirm_link(pages_delete_confirm+\'?\',\''
                      . $this->_oReg->AcpRel.'pages/delete.php?page_id='
                      . $this->_oApp->getIDKEY($aPage['page_id']).'\');" title="'
                      . $this->_oTrans->TEXT_DELETE.'"><img src="'.$this->_oReg->ThemeRel
                      . 'images/delete_16.png" alt="'.$this->_oTrans->TEXT_DELETE.'" /></a>';
        }else {
            $sOutput .= '<img src="'.$this->_oReg->ThemeRel.'images/blank_16.gif" alt=" " />';
        }
        $sOutput .= '</td>'.PHP_EOL;

    // --- TAB 11 --- (add child page) ---------------------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_actions">';
        if (
            $this->_oApp->get_permission('pages_add')
            && $aPage['iswriteable']
            && ($aPage['visibility'] != 'deleted')
            && $aPage['level'] < ($this->_oReg->PageLevelLimit - 1)
          )
        {
            $sOutput .= '<a onclick="add_child_page(\''.$aPage['page_id'].'\');" '
                      . 'title="'.$this->_oTrans->HEADING_ADD_CHILD_PAGE.'"><img src="'
                      . $this->_oReg->ThemeRel.'images/siteadd.png" name="addpage_'.$aPage['page_id']
                      . '" alt="'.$this->_oTrans->HEADING_ADD_CHILD_PAGE.'" /></a>';
        }else {
            $sOutput .= '&nbsp;';
        }
        $sOutput .= '</td>'.PHP_EOL;
    // --- TAB 12 --- (show language) ----------------------------------------------------
        $sOutput .= $this->_Tabs(0).'<td class="list_page_id center">'.$aPage['language'].'</td>'.PHP_EOL;
    // --- FOOTER ------------------------------------------------------------------------
        $sOutput .= $this->_Tabs(-1).'</tr>'.PHP_EOL
                  . $this->_Tabs(-1).'</tbody>'.PHP_EOL
                  . $this->_Tabs(-1).'</table>'.PHP_EOL;
    // if there children, iterate through this children now
        if((bool)$aPage['children']) {
            $sOutput .= $this->_IterateTree($aPage['page_id']);
        }
        $sOutput .= $this->_Tabs(-1).'</li>'.PHP_EOL;
        return $sOutput;
    } // end of method _createListItem
/**
 * build a list of possible parent pages
 * @param array $aPage
 */
    protected function _addToParentList(array $aPage)
    {
        if( ($aPage['level'] < ($this->_oReg->PageLevelLimit - 1))
            && $aPage['iswriteable']
            && ($aPage['visibility'] != 'deleted')
            && $this->_oApp->get_permission('pages_add') )
        {
            $aPage['disabled'] = ($aPage['iswriteable'] ? 0 : 1);
            $this->_aParentList[] = $aPage;
        }
    }

} // end of class PageTree
