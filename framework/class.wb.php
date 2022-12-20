<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * abstract class \bin\wb
 *
 * @category     Core
 * @package      framework
 * @copyright    WebsiteBaker Org. e.V.
 * @author       Ryan Djurovich
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.12.x
 * @revision     $Id: class.wb.php 15 2020-08-22 12:14:23Z Manuela $
 * @since        File available since 2004
 * @description  xxx
 */

declare(strict_types=1);

namespace bin;

use vendor\phplib\Template;
use vendor\idna_convert\idna_convert;
use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;
//use App\WBMailer;

//echo nl2br(sprintf("%s\n",$sFilePath));

abstract class wb extends SecureTokensInterface
{
    /**
    @var object instance of the database object */
    protected $oDb = null;
    /**
    @var object instance holds several values from the application global scope */
    protected $oReg = null;
    protected $oRequest = null;
    /**
    @var object instance holds all of the translations */
    //  protected $_oTrans = null;
    protected $oTrans = null;

    protected $iEndTime   = 0;
    protected $iStartTime = 0;

//    public $password_chars = 'a-zA-Z0-9\_\-\!\#\*\+\@\$\&\:';    // General initialization function
    public $password_chars = '[\w!#$%&*+\-.:=?@\|]';    // General initialization function

    public function __construct()
    {
        parent::__construct();
        $this->oReg     = WbAdaptor::getInstance();
        $this->oReg->getWbConstants();
        $this->oReg->setApplication($this);
        $this->oDb      = $this->oReg->getDatabase();
        $this->oTrans   = $this->oReg->getTranslate();
        $this->oRequest = $this->oReg->getRequester();
    }

    protected function getPageLanguages()
    {
        $aRetval = null;
        $sql = 'SELECT DISTINCT `language`, `page_id`, `position` '
             . 'FROM `'.$this->oDb->getTablePrefix().'pages` '
             . 'WHERE `level`=0 AND `visibility` NOT IN(\'none\', \'hidden\', \'deleted\') '
             . 'ORDER BY `position`, `language`';
        if (($oResult = $this->oDb->query($sql))) {
            while ( $aRow = $oResult->fetchAssoc()) {
                if (!$this->isPageVisible((int)$aRow['page_id'])) { continue; }
                $aRetval[] = $aRow;
            }
        }
        return ($aRetval ?? []);
    }

    public function isBackend()
    {
        return ($this instanceof \admin);
    }

    public function isFrontend()
    {
        return ($this instanceof \frontend);
    }

    public function getCallingScript($sName='SCRIPT_NAME')
    {
//        \trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->oRequest->getServerVar($sName);
    }

    public function getRequestServerVar(string $sName)
    {
//        \trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->oRequest->getServerVar($sName);
    }

/**
 * get Requester Object
 */
    public function getRequester()
    {
//        \trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->oRequest;
    }

/**
 *
 * @return comma separate list of first visible languages
 *
 */
    public function getLanguagesInUsed()
    {
        $aRetval = [];
        $aTmp = $this->getPageLanguages();
        foreach ($aTmp as $item){
          $aRetval[] = $item['language'];
        }
        return \implode(',', \array_unique($aRetval));
    }
/**
 *
 * @return comma separate list of first visible page_ids
 *         or page_id from a given language
 */
    public function getLangPageId($lang='')
    {
        $aRetval = [];
        $aTmp = $this->getPageLanguages();
        foreach ($aTmp as $item){
          $pageIds = $item['page_id'];
          if (empty($lang)){
              $aRetval[] = $item['page_id'];
          } else if (($item['language']==strtoupper($lang))){
              $aRetval[] = $item['page_id'];
              break;
          }
        }
        return \implode(',', \array_unique($aRetval));
    }

    public function getDefaultPageId()
    {
        $iPageId = 0;
        $sLangId = $this->getLangPageId();
        $aLangId = explode(',',$sLangId);
        \reset ($aLangId);
        $iPageId = (int)$aLangId['0'];
        return $iPageId;
    }
  /**
   * Created parse_url utf-8 compatible function
   *
   * @param string $url The string to decode
   * @return array Associative array containing the different components
   *
   */
    public function mb_parse_url($url)
    { /*                                   'urlencode(\'$0\')', */
      $encodedUrl = \preg_replace_callback(
                        '%[^:/?#&=\.]+%usD',
                        function($aMatches){
                            return \urlencode($aMatches[0]);
                        },
                        $url
                    );
      $components = \parse_url( $encodedUrl);
      foreach ( $components as &$component) {
          $component = \urldecode( $component);
      }
      return $components;
    }

    // Return a system permission
    public function get_permission($name, $type = 'system') {
        $bRetVal = false;
        // Append to permission type
        $type .= '_permissions';
        // Check if we have a section to check for
        if($name == 'start') {
            $bRetVal = true;
        } else {
            // Set system permissions var
            $system_permissions = $this->get_session('SYSTEM_PERMISSIONS');
            // Set module permissions var
            $module_permissions = $this->get_session('MODULE_PERMISSIONS');
            // Set template permissions var
            $template_permissions = $this->get_session('TEMPLATE_PERMISSIONS');
            // Return true if system perm = 1
            //$sSearch = ((\array_search($name, $$type)));
            //if (isset($$type) && \is_array($$type) && \is_numeric(\array_search($name, $$type)))
            $mValue = \array_search($name, ($$type ?? []));
            if (isset($$type) && \is_array($$type) && ($mValue !==false))
            {
                if ($type == 'system_permissions') {
                    $bRetVal = true;
                } else {
                    $bRetVal = false;
                }
            } else {
                if ($type == 'system_permissions') {
                    $bRetVal = false;
                } else {
                    $bRetVal = true;
                }
            }
        }

    return $bRetVal;
    }

    public function get_user_details($user_id) {

        $retval = array('username'=>'unknown','display_name'=>'Unknown','email'=>'');
        $sql  = 'SELECT `username`,`display_name`,`email` ';
        $sql .= 'FROM `'.TABLE_PREFIX.'users` ';
        $sql .= 'WHERE `user_id`='.(int)$user_id;
        if( ($resUsers = $this->oDb->query($sql)) ) {
            if (($recUser = $resUsers->fetchRow(MYSQLI_ASSOC)) ) {
                $retval = $recUser;
            }
        }
        return $retval;
    }


    public function get_page_permission($page,$action='admin') {
        if ($action != 'viewing') { $action = 'admin'; }
        $action_groups = $action.'_groups';
        $action_users  = $action.'_users';
        $groups = $users = '0';
        if (\is_array($page)) {
            $groups = $page[$action_groups];
            $users  = $page[$action_users];
        } else {
            $sql  = 'SELECT `'.$action_groups.'`,`'.$action_users.'` ';
            $sql .= 'FROM `'.TABLE_PREFIX.'pages` ';
            $sql .= 'WHERE `page_id`='.(int)$page;
            if( ($res = $this->oDb->query($sql)) ) {
                if( ($rec = $res->fetchRow(MYSQLI_ASSOC)) ) {
                    $groups = $rec[$action_groups];
                    $users  = $rec[$action_users];
                }
            }
        }
        return ($this->ami_group_member($groups) || $this->is_group_match($this->getUserId(), $users));
    }

    // Returns a system permission for a menu link
    public function get_link_permission($title) {
        $title = \str_replace('_blank', '', $title);
        $title = \strtolower($title);
        // Set system permissions var
        $system_permissions = ($this->get_session('SYSTEM_PERMISSIONS') ?? []);
/*
        if (!\is_array($system_permissions)) {
            $system_permissions = \preg_split('/[\s,=+\-\;\:\.\|]+/', $system_permissions, -1, PREG_SPLIT_NO_EMPTY);
        }
*/
        // Set module permissions var
        //$module_permissions = $this->get_session('MODULE_PERMISSIONS');
        if ($title == 'start') {
            return true;
        } else {
            // Return true if system perm = 1

            if (\is_numeric(\array_search($title, $system_permissions))) {
                return true;
            } else {
                return false;
            }
        }
    }
/* ****************
 * check if one or more group_ids are in both group_lists
 *
 * @access public
 * @param mixed $groups_list1: an array or a coma seperated list of group-ids
 * @param mixed $groups_list2: an array or a coma seperated list of group-ids
 * @param array &$matches: an array-var whitch will return possible matches
 * @return bool: true there is a match, otherwise false
 */
    public function is_group_match($mGroupsList1 = '', $mGroupsList2 = '', &$matches = null)
    {

        if ($mGroupsList1 == '' || $mGroupsList2 == '') { return false; }
        if (!\is_null($mGroupsList1) && !\is_array($mGroupsList1)) {
            $mGroupsList1 = \preg_split('/[\s,=+\-\;\:\.\|]+/', $mGroupsList1, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!\is_null($mGroupsList2) && !\is_array($mGroupsList2)) {
            $mGroupsList2 = \preg_split('/[\s,=+\-\;\:\.\|]+/', $mGroupsList2, -1, PREG_SPLIT_NO_EMPTY);
        }
        $matches = \array_intersect($mGroupsList1, $mGroupsList2);
        return (\sizeof($matches) != 0);
    }
/**
 * @param mixed $groups_list is an array or a coma seperated list of group-ids
 * @return bool: true if current user is member of one of this groups or its the superadmin
 */
    public function ami_group_member( $groups_list = '' )
    {
        return ($this->getUserId() == 1) || $this->is_group_match( $groups_list, $this->get_groups_id());
    }

/**
 * Alias for isPageVisible()
 * @param mixed $mPage  can be a integer (PageId) or an array
 * @return bool
 * @deprecated since 2.10.0
 */

    public function page_is_visible($mPage)
    {
        // get PageId from array or object
        if (\is_array($mPage)) {
            $iPageId = (int) $mPage['page_id'];
        } elseif (\is_integer($mPage)) {
            $iPageId = (int) $mPage;
        } else {
            $iPageId = 0;
        }
        return $this->isPageVisible($iPageId);
    }

/**
 * isViewingPageAllowed
 * @param int $iPageId
 * @param int $iOtherUserId  (optional) test for other then current user
 * @return bool
 * @description if current user has permission to see this page
 *   the visibility logic follows this scheme:
 *   false : ([none] | [deleted])
 *   false : ([private] | [registered]) and [not authenticated]
 *   true  : ([private] | [registered]) and [authenticated]
 *   true  : [public] | [hidden]
 */
    public function isPageVisible(int $iPageId, $iOtherUserId = null)
    {
        try {
            // sanitize optional user_id
                $iUserId = (int) ($iOtherUserId ?? $this->getUserId());
            // get this page record
            $sql = 'SELECT * FROM `'.$this->oDb->getTablePrefix().'pages` '
                 . 'WHERE `page_id`='.$iPageId;
            $oRecords = $this->oDb->query($sql);
            if (!($oPage = $oRecords->fetchObject())) {
                throw new \InvalidArgumentException('request not existing PageId ['.$iPageId.']');
            }
            //
            switch ($oPage->visibility) {
                case 'hidden':
                case 'public':
                    $bRetval = true;
                    break;
                case 'private':
                case 'registered':
                    if (($bRetval = $this->is_authenticated())) {
                        $bRetval = (
                            $this->ami_group_member($oPage->viewing_groups) ||
                            $this->is_group_match($iUserId, $oPage->viewing_users)
                        );
                    }
                    break;
                default:
                    $bRetval = false;
                    break;
            }
        } catch(\throwable $e) {
            $bRetval = false;
        }
        return $bRetval;
    }
/**
 * Alias for isPageActive()
 * @param mixed $mPage  can be a integer (PageId) or an array
 * @return bool  true if at least one active section is found
 * @deprecated since 2.10.0
 */
    public function page_is_active($mPage)
    {
        // get PageId from array
        if (\is_array($mPage)) {
            $iPageId = (int) $mPage['page_id'];
        } elseif (\is_integer($mPage)) {
            $iPageId = (int) $mPage;
        } else {
            $iPageId = 0;
        }
        return $this->isPageActive($iPageId);
    }
/**
 * Check if there is at least one active section on this page
 * @param int $iPageId
 * @return bool  true if at least one active section is found
 */

    public function isPageActive(int $iPageId): bool
    {
        try {
            // seach for active sections in this page
//            $sWhereExtra = 'AND `active` = 1 '.$this->is_authenticated();
            $sql = '
            SELECT COUNT(*) FROM `'.$this->oDb->getTablePrefix().'sections`
                 WHERE `page_id`='.(int) $iPageId.' AND
                 ('.\time().' BETWEEN `publ_start` AND `publ_end`)
                 AND `active` = 1
                 ';
            $bRetval = (bool) $this->oDb->get_one($sql);
        } catch (Exception $e) {
            $bRetval = false;
        }
        return $bRetval;
    }

    // Check whether we should show a page or not (for front-end)
    public function show_page($mPage)
    {
        $retval = ($this->page_is_visible($mPage) && $this->page_is_active($mPage));
        return $retval;
    }

    // Check if the user is already authenticated or not
    public function is_authenticated() {
        $retval = (
            isset($_SESSION['USER_ID']) && !empty($_SESSION['USER_ID']) && \is_numeric($_SESSION['USER_ID'])
        );
        return (bool) $retval;
    }

    // Modified addslashes function which takes into account magic_quotes
    public function add_slashes($input) {
        return \is_string($input) ? \addslashes($input) : $input;
    }

    // Ditto for stripslashes
    // Attn: this is _not_ the counterpart to $this->add_slashes() !
    // Use stripslashes() to undo a preliminarily done $this->add_slashes()
    // The purpose of $this->strip_slashes() is to undo the effects of magic_quotes_gpc==On
    public function strip_slashes($input) {
        return \is_string($input) ? \stripslashes($input) : $input;
    }

    // Escape backslashes for use with mySQL LIKE strings
    public function escape_backslashes($input) {
        return \str_replace("\\","\\\\",$input);
    }

    public function page_link($link='', $bRetro=false){
        $sRetval = $link;
        // Check for :// in the link (used in URL's) as well as mailto:
        if ((\strstr($link, '://') == '') && (\substr($link, 0, 7) != 'mailto:')) {
            if ($bRetro){
                $sRetval    = WB_URL.PAGES_DIRECTORY.$link.PAGE_EXTENSION;
//                $sRetval   .= (sprintf("\n<!-- [%03d] %s -->\n",__LINE__,$sRetval));
            } else {
                $sPagesDir  = trim($this->oReg->PagesDir, '/');
                $PagesLink  = (empty($sPagesDir) ? trim($link,'/') : $sPagesDir.$link);
                $sRetval    = $this->oReg->AppUrl.$PagesLink.$this->oReg->PageExtension;
                $sScriptUrl = $this->oReg->AppUrl.$PagesLink.$this->oReg->PageExtension;
                $sShortUrl  = $this->oReg->AppUrl.trim($link,'/').'/' ;
                $sRetval    = (\is_readable($this->oReg->AppPath.'short.php') ? $sShortUrl : $sScriptUrl);
            }
        }
//        \trigger_error(\sprintf('$sRetval %s', $sRetval),E_USER_NOTICE);
        return $sRetval;
    }

    public function getPageLink(int $iPageId):? string
    {
        $aSql[0] = 'SELECT `link` FROM `'.$this->oDb->TablePrefix.'pages`'
              .'WHERE `page_id` = '.$iPageId;
        if ($sPageLink = $this->oDb->get_one($aSql[0])){
            $sLink = $this->page_link($sPageLink);
        }
        return ($sLink ?? null);
    }

    public function getPage(int $iPageId):? array
    {
        $aRetval = $this->oReg->App->page;
        $aSql[0] = 'SELECT * FROM `'.$this->oDb->TablePrefix.'pages`'
              .'WHERE `page_id` = '.$iPageId;
        if ($oPage = $this->oDb->query($aSql[0])){
            $aPage = $oPage->fetchArray();
        }
        return ($aPage ?? $aRetval);
    }

//        if (filter_has_var(INPUT_POST,$field)){$mRetval = $mField;}
    // Get POST data
    public function get_post($mField) {
        return $this->oRequest->getParam($mField);
    }

    // Get POST data and escape it
    public function get_post_escaped($field) {
        return ($this->add_slashes($this->get_post($field) ?? null));
    }

    // Get GET data
    public function get_get($field) {
        return $this->oReg->Request->getParam($field);
    }

    // Get SESSION data
    public function get_session($field) {
        return ($_SESSION[$field] ?? null);
    }

    // Get SERVER data
    public function get_server($mField) {
        return $this->oRequest->getServerVar($field);
    }

    // Get the current users id
    public function get_user_id()
    {
        \trigger_error(sprintf('invalid method call: %s change it to getUserId()',__METHOD__), E_USER_DEPRECATED);
        return $this->get_session('USER_ID');
    }

    public function getUserId()
    {
        return (int)$this->get_session('USER_ID');
    }

    // Get the current users group id
    public function get_group_id()
    {
        \trigger_error(sprintf('invalid method call: %s change it to getGroupId()',__METHOD__), E_USER_DEPRECATED);
        return $this->get_session('GROUP_ID');
    }

        public function getGroupId()
    {
        return (int)$this->get_session('GROUP_ID');
    }

    // Get the current users group ids
    public function get_groups_id(): array
    {
        $mGroupsList = $this->get_session('GROUPS_ID');
        if (!is_null($mGroupsList) && !\is_array($mGroupsList)) {
            $mGroupsList = \preg_split('/[\s,=+\-\;\:\.\|]+/', $mGroupsList, -1, PREG_SPLIT_NO_EMPTY);
        }
        //return \explode(",", $this->get_session('GROUPS_ID'));
        return ($mGroupsList ?? []);
    }

    // Get the current users group name
    public function get_group_name()
    {
        return implode(",", $this->get_session('GROUP_NAME'));
    }

    // Get the current users group name
    public function get_groups_name() {
        return $this->get_session('GROUP_NAME');
    }

    // Get the current users username
    public function get_username() {
        return $this->get_session('USERNAME');
    }

    // Get the current users display name
    public function get_display_name() {
        return $this->get_session('DISPLAY_NAME');
    }

    // Get the current users email address
    public function get_email() {
        return $this->get_session('EMAIL');
    }

    // Get the current users home folder
    public function get_home_folder() {
        return $this->get_session('HOME_FOLDER');
    }

    // Get the current users timezone
    public function get_timezone() {
        return (isset($_SESSION['USE_DEFAULT_TIMEZONE']) ? '-72000' : $_SESSION['TIMEZONE']);
    }

    // Validate supplied email address
    public function validate_email($email) {

        if (\is_callable('\idn_to_ascii') && (\defined('INTL_IDNA_VARIANT_UTS46') || \defined('INTL_IDNA_VARIANT_2003'))){ /* use pear if available */
            $aInfoMatch = [];
            // 7.2.0  INTL_IDNA_VARIANT_2003 has been deprecated; use INTL_IDNA_VARIANT_UTS46 instead.
            $iVariant = \defined('INTL_IDNA_VARIANT_UTS46') ? \INTL_IDNA_VARIANT_UTS46 : \INTL_IDNA_VARIANT_2003;
            $email = \idn_to_ascii($email, 0, $iVariant);
        }else {

            $IDN = new idna_convert();
            $email = $IDN->encode($email);
            unset($IDN);
        }
        // regex from NorHei 2011-01-11
        $retval = \preg_match("/^((([!#$%&'*+\\-\/\=?^_`{|}~\w])|([!#$%&'*+\\-\/\=?^_`{|}~\w][!#$%&'*+\\-\/\=?^_`{|}~\.\w]{0,}[!#$%&'*+\\-\/\=?^_`{|}~\w]))[@]\w+(([-.]|\-\-)\w+)*\.\w+(([-.]|\-\-)\w+)*)$/", $email);
        return ($retval != false);
    }
  /**
   * replace header('Location:...  with new method
   * if header send failed you get a manuell redirected link, so script don't break
   *
   * @param string $location, redirected url
   * @return void
   */
    public function send_header( $location)
    {
      if (!\headers_sent()) {
        \header('Location: '.$location);
        exit( 0);
      } else {
//            $aDebugBacktrace = debug_backtrace();
//            array_walk( $aDebugBacktrace, function(& $a,$b', 'print "\n". basename( $a[\'file\'] ). " &nbsp; <font color=\"red\">{$a[\'line\']}</font> &nbsp; <font color=\"green\">{$a[\'function\']} ()</font> &nbsp; -- ". dirname( $a[\'file\'] ). "/";' ) );
          $msg = '<div style="text-align:center;"><h2>An error has occurred</h2><p>The <strong>Redirect</strong> could not be start automatically.'."\n"
               . 'Please click <a style="font-weight:bold;" '.'href="'.$location.'">on this link</a> to continue!</p></div>'."\n";
          throw new \LogicException( $msg);
      }
    }

/* ****************
 * set one or more bit in a integer value
 *
 * @access public
 * @param int $value: reference to the integer, containing the value
 * @param int $bits2set: the bitmask witch shall be added to value
 * @return void
 */
    public function bit_set( &$value, $bits2set )
    {
        $value |= $bits2set;
    }

/* ****************
 * reset one or more bit from a integer value
 *
 * @access public
 * @param int $value: reference to the integer, containing the value
 * @param int $bits2reset: the bitmask witch shall be removed from value
 * @return void
 */
    public function bit_reset( &$value, $bits2reset)
    {
        $value &= ~$bits2reset;
    }

/* ****************
 * check if one or more bit in a integer value are set
 *
 * @access public
 * @param int $value: reference to the integer, containing the value
 * @param int $bits2set: the bitmask witch shall be added to value
 * @return void
 */
    public function bit_isset( $value, $bits2test )
    {
        return (($value & $bits2test) == $bits2test);
    }

    // Print a success message which then automatically redirects the user to another page
    public function print_success( $message='', $redirect = 'index.php' ) {
        $oRequest = $this->oRequest;
        if (\is_array($message)) {
           $message = \implode ('<br />',$message);
        }
        // fetch redirect timer for sucess messages from settings table
        $redirect_timer = ((\defined( 'REDIRECT_TIMER' )) && (REDIRECT_TIMER <= 10000)) ? REDIRECT_TIMER : 0;
        // add template variables
        // Setup template object, parse vars to it, then parse it
        $tpl = new Template(\dirname($this->correct_theme_source('success.htt')));
        $tpl->set_file( 'page', 'success.htt' );
        $tpl->set_block( 'page', 'main_block', 'main' );
        $tpl->set_block( 'main_block', 'show_redirect_block', 'show_redirect' );
        $tpl->set_var( 'MESSAGE', $message );
        $tpl->set_var( 'REDIRECT', $redirect );
        $tpl->set_var( 'REDIRECT_TIMER', $redirect_timer );
        $tpl->set_var( 'NEXT', $this->oTrans->TEXT_NEXT );
        $tpl->set_var( 'TEXT_CLOSE', $this->oTrans->TEXT_CONFIRM );
        $tpl->set_var($this->oTrans->getLangArray());
        if ($redirect_timer == -1) {
            $tpl->set_block( 'show_redirect', '' );
        }
        else {
            $tpl->parse( 'show_redirect', 'show_redirect_block', true );
        }
        $tpl->parse( 'main', 'main_block', false );
        $tpl->pparse( 'output', 'page' );
    }

    // Print an error message
    public function print_error($message, $link = 'index.php', $auto_footer = true) {
        $sAddonFile    = $this->getCallingScript();
        $sAddonPath    = $this->oReg->DocumentRoot.ltrim(dirname($sAddonFile),'/');
        $sDomain = \basename(dirname($sAddonPath)).'/'.\basename($sAddonPath);
        // Only for development for pretty mysql dump
        $bLocalDebug  =  is_readable($sAddonPath.'.setDebug');
        // Only for development prevent secure token check,
        $bSecureToken = !is_readable($sAddonPath.'.setToken');
        $sPHP_EOL     = ($bLocalDebug ? "\n" : '');
        if (\is_array($message)) {
//            $message = \implode ('<br />',$message);
            $message = PreCheck::xnl2br($message);
        }
        // Setup template object, parse vars to it, then parse it
        $tpl = new Template(\dirname($this->correct_theme_source('error.htt')));
        $tpl->set_file('page', 'error.htt');
        $tpl->set_block('page', 'main_block', 'main');
        $tpl->set_var('MESSAGE', $message);
        $tpl->set_var('REDIRECT', $link);
        $tpl->set_var('REDIRECT_INFO', ($bLocalDebug ? $link : ''));
        $tpl->set_var('ADDON_Path',($bLocalDebug ? $this->getCallingScript() : ''));
        $tpl->set_var('SCRIPT_NAME',($bLocalDebug ? $sDomain : ''));
        $tpl->set_var('W3HIDE', ($bLocalDebug ? ' w3-hide' : ''));
        $tpl->set_var($this->oTrans->getLangArray());
        $tpl->parse('main', 'main_block', false);
        $tpl->pparse('output', 'page');
        if ( $auto_footer == true ) {
            if (\method_exists($this, "print_footer") ) {
                $this->print_footer();
            }
        }
        exit();
    }

  /*
  * @param string $message: the message to format
  * @param string $status:  ('ok' / 'error' / '') status defines the apereance of the box
  * @return string: the html-formatted message (using template 'message.htt')
  */
    public function format_message($message, $status = 'ok', $sLink = '')
    {
        $retval = '';

        $id = \uniqid( 'x');
        $aPage = (isset($GLOBALS['page']) ? $GLOBALS['page'] : ['title'=>WEBSITE_TITLE]);
        $oTrans = \Translate::getInstance();
        $aStatus = ['ok','error','warning','noaddon'];

        if (($status == 'noaddon')) {
            $sTemplateFile = ($this->correct_theme_source('infoBox.htt'));
        } else {
            $sTemplateFile = ($this->correct_theme_source('message.htt'));
        }

        $sFilename = basename($sTemplateFile);
        $tpl = new Template(\dirname($sTemplateFile));
        $tpl->set_file( 'page', basename($sTemplateFile));
        $tpl->set_block( 'page', 'main_block', 'main');
        $tpl->set_var( 'MESSAGE', $message);
        $tpl->set_var('REDIRECT', $sLink);
        $tpl->set_var($oTrans->getLangArray());

        $tpl->set_block( 'main_block', 'show_redirect_block', 'show_redirect' );

        $tpl->set_var( 'THEME_URL', THEME_URL);
        $tpl->set_var('PAGE_TITLE', ($aPage['title']?:WEBSITE_TITLE));
        $tpl->set_var( 'ID', $id);
        $tpl->set_var('BOX_STATUS', 'msg');
        $aBoxColor = ['ok' => 'green', 'error' => 'red', 'warning' => 'khaki', 'noaddon' => 'khaki'];
        if ( in_array($status, $aStatus)) {
            $tpl->set_var( 'BOX_STATUS', (($status=='noaddon')? 'warning' : $status));
            $tpl->set_var( 'BOX_COLOR', $aBoxColor[$status]);
        }
        $tpl->set_var( 'STATUS', $status);

        if ($status == 'ok') {
            $iRedirectTimer = ((\defined( 'REDIRECT_TIMER')) && ( REDIRECT_TIMER <= 10000)) ? REDIRECT_TIMER : 0;
            $iRedirectTimer = REDIRECT_TIMER;
            switch ($iRedirectTimer):
              case 0: // do not show message
                break;
              case - 1: // show message permanently
                $tpl->set_block( 'show_redirect_block', '');
                break;
              default: // hide message after REDIRECTOR_TIMER milliseconds
                $tpl->set_var( 'REDIRECT_TIMER', $iRedirectTimer);
                $tpl->parse( 'show_redirect', 'show_redirect_block');
            endswitch;
        } else {
        }
        $tpl->parse( 'main', 'main_block', false);
        $retval = $tpl->finish( $tpl->parse('output', 'page', false)).$retval;
        unset( $tpl);
        return $retval;
    }

  /*
  * @param string $type: 'locked'(default), 'new' or 'error'
  * @param string $sMessage: message for type 'error'
  * @return void: terminates application
  * @description: 'locked' >> Show maintenance screen and terminate, if system is locked
  *               'new' >> Show 'new site under construction'(former print_under_construction)
  *               'error' >> Show userfriendly 'error message'(instead throwing an excepetion)
  */
    public function ShowMaintainScreen($type = 'locked',$sMessage='', $sLink='',$bFullPage=true)
    {
        $aType = ['locked','new','busy','error'];
        $LANGUAGE    = \strtolower($_SESSION['LANGUAGE'] ?? $this->oReg->Language);
        $PAGE_TITLE  = $this->oTrans->MESSAGE_GENERIC_WEBSITE_UNDER_CONSTRUCTION;
        $MAINTENANCE = $this->oTrans->TEXT_MAINTENANCE_ON;

// under construction
        $PAGE_ICON   = 'negative';
        $show_screen = false;
        $curr_user = (\intval($_SESSION['USER_ID'] ?? 0));
        $sAdminDirectory = ($this->oReg->AcpDir ?? 'admin');
//        $bIsBackend = \preg_match('/'.\preg_quote($sAdminDirectory, '/').'/is', $this->oRequest->getServerVar("SCRIPT_NAME"));
        $show_screen = ($type !== 'locked') && !\defined('FINALIZE_SETUP') && $this->isFrontend();
        if ((($this->oReg->SystemLocked ?? false) == true) && ($curr_user != 1)) {
    //    if ((\defined( 'SYSTEM_LOCKED') && ( int)SYSTEM_LOCKED == 1) && ( $curr_user != 1)) {
            \header( $this->oRequest->getServerVar('SERVER_PROTOCOL').' 503 Service Unavailable');
            if ($type === 'locked') {
                // first kick logged users out of the system
                // delete all remember keys from table 'user' except user_id=1
                $sql = 'UPDATE `'.$this->oDb->getTablePrefix().'users` '
                     . 'SET `remember_key`=\'\' '
                     . 'WHERE `user_id`<>1';
                $this->oDb->query($sql);
                // delete remember key-cookie if set
                if (isset( $_COOKIE['REMEMBER_KEY'])) {
                    \setcookie( 'REMEMBER_KEY', '', \time() - 3600, '/');
                }
                // overwrite session array
                $_SESSION = [];
                // delete session cookie if set
                if (\ini_get( "session.use_cookies")) {
                    $params = \session_get_cookie_params();
                    $params["httponly"] = ($params["httponly"] ? $params["httponly"] : true);
                    \setcookie(\session_name(), '', \time() - 42000, $params["path"], $params["domain"], $params["secure"],
                    $params["httponly"]);
                }
                // delete the session itself
                \session_destroy();
                $PAGE_TITLE = $this->oTrans->MESSAGE_GENERIC_WEBSITE_LOCKED;
//                $MAINTENANCE = $this->oTrans->TEXT_MAINTENANCE_ON;
                $MAINTENANCE = $this->oTrans->MESSAGE_GENERIC_WEBSITE_UNDER_CONSTRUCTION;
                $PAGE_ICON = 'system';
                $show_screen = ($this->isFrontend() || ((int)$this->get_session('user_id') > 1));
            }
        } else {
            if ($type == 'new') {
                $PAGE_ICON   = 'negative';
                $MAINTENANCE = $this->oTrans->MESSAGE_GENERIC_WEBSITE_UNDER_CONSTRUCTION;
                $PAGE_TITLE  = \sprintf($this->oTrans->MESSAGE_GENERIC_WEBSITE_NO_PAGES, $this->oReg->Language);
            }else if ($type == 'error') {
                $show_screen = true;
                $sCallingScript = $this->oRequest->getServerVar('SCRIPT_NAME');
                $MAINTENANCE = $this->oTrans->TEXT_MODIFY_CONTENT;
                $sMessage = (empty($sMessage) ? $this->oTrans->TEXT_ERROR : $sMessage);
                $PAGE_TITLE  = \sprintf('%1$s',$sMessage);
//                \trigger_error(\sprintf('%1$s %2$s',$sMessage,$sCallingScript),\E_USER_NOTICE);
            }
        }

        if ($show_screen) {
            $sMaintanceFile = $this->correct_theme_source('maintenance.htt');
            if (\file_exists($sMaintanceFile)) {
                $tpl = new Template(\dirname( $sMaintanceFile));
                $tpl->set_file('page', 'maintenance.htt');
                $tpl->set_block('page', 'main_block', 'main');
                $tpl->set_block('main_block', 'meta_block', 'meta');
                $tpl->set_block('main_block', 'html_block', 'html');
                $tpl->set_block('main_block', 'link_block', 'link');

                if (\defined( 'DEFAULT_CHARSET')) {
                    $charset = $this->oReg->DefaultCharset;
                } else {
                    $charset = 'utf-8';
                }
/*--------------------------------------------------------------------------------*/
                $sTemplateFunc = 'resolveTemplateImagesPath';
                $sImages       = $sTemplateFunc();
/*--------------------------------------------------------------------------------*/
                //$sLink = (empty($sLink) ? $oReg->AppUrl.$this->oRequest->getServerVar('SCRIPT_NAME') : $sLink);
                $aTwigdata = [
                //'SCRIPT_NAME' => $this->oRequest->getServerVar('SCRIPT_FILENAME'),
                'DISPLAY_TYPE'=>$type,
                'PAGE_TITLE' => $PAGE_TITLE,
                'CHECK_BACK' => $this->oTrans->MESSAGE_GENERIC_PLEASE_CHECK_BACK_SOON ,
                'LINK_URL' => $sLink,
                'CHARSET' => $charset,
                'WB_URL' => $this->oReg->AppUrl,
                'MAINTENANCE' => ((!isset($MAINTENANCE)) ? $MAINTENANCE : $sMessage),
                'BE_PATIENT' => $this->oTrans->MESSAGE_GENERIC_BE_PATIENT,
                'THEME_URL' => $this->oReg->ThemeUrl,
                'IMAGES'     => $sImages,
                'PAGE_ICON' => $PAGE_ICON,
                'LANGUAGE' => $LANGUAGE,
                ];

                $tpl->set_var($aTwigdata);

                if ((empty($sLink)))
                {
                    $tpl->set_block('link_block','');
                }
                else
                {
                    $tpl->parse( 'link', 'link_block', true);
                }
                if ($bFullPage){
                    $tpl->parse( 'meta', 'meta_block', true);
                    $tpl->parse( 'html', 'html_block', true);
                }
                else
                {
                    $tpl->set_block('meta_block','');
                    $tpl->set_block('html_block','');
                }

                $tpl->parse( 'main', 'main_block', false);
                $tpl->pparse( 'output', 'page');
                exit();
            } else {
                require($this->oReg->AppPath.'languages/'.$this->oReg->DefaultLanguage.'.php');
                echo '<!DOCTYPE html PUBLIC "-W3CDTD XHTML 1.0 TransitionalEN" "http:www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <head><title>'.$this->oTrans->MESSAGE_GENERIC_WEBSITE_UNDER_CONSTRUCTION.'</title>
                    <style type="text/css"><!-- body{ font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 12px; background-image: url("'.
                  $this->oReg->AppUrl.'templates/'.$this->oReg->DefaultTheme.
                  '/images/background.png");background-repeat: repeat-x; background-color: #A8BCCB; text-align: center; }

                    <br /><h1>'.$this->oTrans->MESSAGE_GENERIC_WEBSITE_UNDER_CONSTRUCTION.'</h1><br />
                    '.$this->oTrans->MESSAGE_GENERIC_PLEASE_CHECK_BACK_SOON.'</body></html>';
            }
            \flush();
            exit();
        }
    return false;
    }

    /**
     * wb::mail()
     *
     * @param string $sFromAddress
     * @param string $toAddress, comma sepated list of adresses
     * @param string $sSubject
     * @param string $sMessage
     * @param string $sFromname
     * @param string $toName
     * @param string $sReplyTo
     * @param string $sReplyToName
     * @param string $sMessagePath
     * @param array  $aAttachment=[
     *                            'File to the attachment',
     *                             ]
     * @return
     */
    public function mail(
                    $sFromAddress,
                    $toAddress,
                    $sSubject,
                    $sMessage,
                    $sFromname='',
                    $toName='',
                    $sReplyToAddress='',
                    $sReplyToName='',
                    $sMessagePath='',
                    $aAttachment=null,
                    $sCallingScript='none'
                    ) {
/*
        $aParameters      = [];
        $aFromAddress     = [];
        $aToAddress       = [];
        $aReplyToAddress  = [];
*/
        $aParameters = [
            'setFromAdress' => $sFromAddress,
            'toAddress' => $toAddress,
            'Subject' => $sSubject,
            'msgHTML' => $sMessage,
            'setFromName' => $sFromname,
            'toName' => $toName,
            'addReplyToAdress' => $sReplyToAddress,
            'addReplyToName' => $sReplyToName,
            'msgHTML' => $sMessagePath,
            'CallingScript' => $sCallingScript,
        ];

/* for tests without sending e-mail
print '<pre  class="mod-pre" style="margin-left:30px;">function <span>'.__FUNCTION__.'( '.''.' );</span>  filename: <span>'.basename(__FILE__).'</span>  line: '.__LINE__.' -> '."\n";
print_r( $aParameters ); print '</pre>'; \flush (); //  sleep(10); die();
return true;
*/
        //SMTP needs accurate times, and the PHP time zone MUST be set
        //This should be done in your php.ini, but this is how to do it if you don't have access to that
        date_default_timezone_set('Etc/UTC');

        $sAddonPath   = str_replace('\\','/',__DIR__).'/';
        // Only for development for pretty mysql dump
        $sLocalDebug  =  is_readable($sAddonPath.'.setDebug');

        // Strip breaks and trim
        if ($sFromname!='') {
            $sFromname    = \preg_replace( "/[^a-z0-9 !?:;,.\/_\-=+@#$&\*\(\)]/im", "", $sFromname );
            $sFromname    = \preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $sFromname );
        }
        $sFromAddress     = \trim(\preg_replace('/[\r\n]/', '', $sFromAddress));

        if ($toName!='') {
            $toName       = \preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $toName );
        }
        $toAddress        = \trim(\preg_replace('/[\r\n]/', '', $toAddress));

        if ($sReplyToName!='') {
            $sReplyToName = \preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $sReplyToName );
        }
        //Set who the message is to be sent from
        $sReplyToAddress  = \trim(\preg_replace('/[\r\n]/', '', $sReplyToAddress));
        $sReplyToAddress  = ( empty($sReplyToAddress) ? $toAddress : $sReplyToAddress );

        $sSubject         = \trim(\preg_replace('/[\r\n]/', '', $sSubject));
        // sanitize parameter to prevent injection
        $sMessage         = \preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $sMessage );

        try {

            // create PHPMailer object and define default settings
            $myMail = new \wbmailer(true);

            $html   =  \preg_replace('/[\n\r]/', '',\nl2br($this->StripCodeFromText($sMessage)));
            $plain  = $myMail->html2text($html);

            // convert commaseperated toAdresses List to an array
            $aToAddress = $myMail->parseAddresses( $toAddress, false );

            if (!empty($sFromAddress)) {
            // set user defined from address
                $myMail->setFrom($sFromAddress, $sFromname);
            // set user defined to address
                $myMail->AddAddress($toAddress, $toName);
            }

            // set user defined to ReplyTo
            if (!empty($sReplyToAddress)) {
              $myMail->addReplyTo($sReplyToAddress, $sReplyToName);
            }

    //Set the subject line
            $myMail->Subject = $sSubject;

            $myMail->wrapText($html, 80);

    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
            $myMail->msgHTML( $html, $sMessagePath, true);
            $sPlainText = $myMail->html2text($html);
//Replace the plain text body with one created manually
            $myMail->AltBody = $sPlainText;

            if (\is_array( $aAttachment )) {
                foreach($aAttachment as $sFile) {
                    $myMail->AddAttachment( $sFile );
                }
            }
            if( $myMail->getReplyToAddresses() ) { }
            $sSendToAdress = (!empty($sReplyToName) ? $sReplyToName : $toName);
/**/
            if ($sLocalDebug) {
//print nl2br(sprintf("Message was sent using %s from %s\n",strtoupper($myMail->getData('Mailer')),$sSendToAdress));
//return true; // comment out in productiv system
            }
             // Enable SMTP SSL authentication
             $bLowSecurity = filter_var( $this->oReg->WbmailerLowSecurity, FILTER_VALIDATE_BOOLEAN, ['options' => ['default'=>false]]);
             if ($bLowSecurity)
             {
                $myMail->setLowSecurity();
             }

//send the message, check for errors
            if (!$sLocalDebug && !$myMail->send()) {
                $aErrorMsg = [sprintf('Mailer Error: %s',$myMail->ErrorInfo)];
                if (!empty($aErrorMsg)){
                    return '<li>'.((\is_array($aErrorMsg)) ? (\implode("\n",$aErrorMsg)) : $aErrorMsg).'</li>';
                }
            } else {
                if ($sLocalDebug) {
                    print nl2br(sprintf("Message was sent using %s from %s\n",strtoupper($myMail->getData('Mailer')),$sSendToAdress));
                }
                return true;
            }

        } catch (Exception $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (\Exception $e) { //The leading slash means the Global PHP Exception class will be caught
            echo $e->getMessage(); //Boring error messages from anything else!
        }

    }

    public function sendMail(
                    $sFromAddress=null,
                    $toAddress=null,
                    $sSubject=null,
                    $sMessage=null,
                    $sFromname=null,
                    $toName=null,
                    $sReplyToAddress=null,
                    $sReplyToName=null,
                    $sMessagePath=null,
                    $aAttachment=null
                    ) {

        $aParameters      = [];
        $aFromAddress     = [];
        $aToAddress       = [];
        $aReplyToAddress  = [];
//        return true;

        try {
            $myMail = new WBMailer(true);

// $message The message to wrap
            $myMail->wrapText($sMessage, 80);
// sanitize body message
            $html   =  \preg_replace('/[\n\r]/', '',\nl2br($this->StripCodeFromText($sMessage)));
    //convert HTML into a basic plain-text alternative body
            $plain  = $myMail->html2text($html);
//         create PHPMailer object and define default settings
            $myMail = new \wbmailer(true);
//Set who the message is to be sent from
            $myMail->setFrom($sFromAddress, $sFromname);
//Set an alternative reply-to address
            $myMail->addReplyTo($sReplyToAddress, $sReplyToName);
//Set who the message is to be sent to
            $myMail->addAddress($toAddress, $toName);
//Set the subject line
            $myMail->Subject = $sSubject;
//Read an HTML message body from an external file, convert referenced images to embedded,
            $myMail->msgHTML($html, $sMessagePath, true);
//Replace the plain text body with one created manually
            $myMail->AltBody = $plain;
//Attach files
            if (\is_array( $aAttachment )) {
                foreach($aAttachment as $sFile) {
                    $myMail->AddAttachment($sFile);
                }
            }
//send the message, check for errors
            if (!$myMail->send()) {
                $aErrorMsg = (sprintf("Mailer Error: %s\n",$myMail->ErrorInfo));
                if (!empty($aErrorMsg)){
                    return '<li>'.((\is_array($aErrorMsg)) ? (\implode("\n",$aErrorMsg)) : $aErrorMsg).'</li>';
                }
            } else {
                print nl2br(sprintf("Message was sent using %s to %s\n",strtoupper($myMail->getData('Mailer')),$toName));
                return true;
            }

        } catch (Exception $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (\Exception $e) { //The leading slash means the Global PHP Exception class will be caught
            echo $e->getMessage(); //Boring error messages from anything else!
        }

}

/*--------------------------------------------------------------------------------------*/
 /**
  * checks if there is an alternative Theme template
  *
  * @param string $sThemeFile set the template.htt
  * @return string the relative theme path
  *
  */
    public function correct_theme_source($sThemeFile = 'start.htt') {
        $sRetval = '';
// fix for missing Theme in Settings in older wb than 2.8.x
        $this->oReg->ThemePath = str_replace('DEFAULT_THEME','DefaultTheme',$this->oReg->ThemePath);
        if (!defined('DEFAULT_THEME')){define('DEFAULT_THEME','DefaultTheme');}

        if (\is_readable($this->oReg->ThemePath.'templates/'.$sThemeFile ))
        {
            $sRetval = $this->oReg->ThemePath.'templates/'.$sThemeFile;
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sRetval));
        } else {
            if (\is_readable($this->oReg->AcpPath.'themes/templates/'.$sThemeFile )) {
                $sRetval = $this->oReg->AcpPath.'themes/templates/'.$sThemeFile;
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sRetval));
            } elseif(\is_readable($this->oReg->AppPath.'templates/DefaultTheme/'.$sThemeFile )) {
                $sRetval = $this->oReg->AppPath.'templates/DefaultTheme/'.$sThemeFile;
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sRetval));
            } elseif(\is_readable($this->oReg->AppPath.'templates/'.$this->oReg->Template.'/'.$sThemeFile )) {
                $sRetval = $this->oReg->AppPath.'templates/'.$this->oReg->Template.'/'.$sThemeFile;
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sRetval));
            } elseif(\is_readable($this->oReg->AppPath.'account/templates/'.$sThemeFile )) {
                $sRetval = $this->oReg->AppPath.'account/templates/'.$sThemeFile;
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sRetval));
            }
            else
            {
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sThemeFile));
                throw new \InvalidArgumentException('missing template file '.str_replace($this->oReg->AppPath,'',$sThemeFile));
            }
        }
//        $sReturn = (\is_file($sRetval) ? \dirname($sRetval) : $sRetval);
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sRetval));
        return $sRetval;
    }

    /**
     * Check if a foldername doesn't have invalid characters
     *
     * @param String $str to check
     * @return Bool
     */
    public function checkFolderName($str)
    {
        return !( preg_match('#\^|\\\|\/|\.|\?|\*|"|\'|\<|\>|\:|\|#i', $str) ? true : false );
    }

    /**
     * Check the given path to make sure current path is within given basedir
     * normally document root
     *
     * @param String $sCurrentPath
     * @param String $sBaseDir
     * @return $sCurrentPath or false
     */
    public function checkpath($sCurrentPath, $sBaseDir = null)
    {
        $sBaseDir = ($sBaseDir ?? $this->oReg->AppPath);
        // Clean the cuurent path
        $sCurrentPath = rawurldecode($sCurrentPath);
        $sCurrentPath = realpath($sCurrentPath);
        $sBaseDir = realpath($sBaseDir);
        // $sBaseDir needs to exist in the $sCurrentPath
        $pos = stripos ($sCurrentPath, $sBaseDir );
        return ($pos == 0 ? $sCurrentPath : 0);
    }

/**
 * remove <?php code ?>, [[text]], link, script, scriptblock and styleblock from a given string
 * and return the cleaned string
 *
 * @param string $sValue
 * @returns
 *    false: if @param is not a string
 *    string: cleaned string
 */
    public function StripCodeFromText($mText, $iFlags = Sanitize::REMOVE_DEFAULT )
    {
//        if (!class_exists('Sanitize')) { include __DIR__.'/Sanitize.php'; }
        return Sanitize::StripFromText($mText, $iFlags);
    }

  /**
   * ReplaceAbsoluteMediaUrl
   * @param string $sContent
   * @return string
   * @description Replace URLs witch are pointing into MEDIA_DIRECTORY with an URL
   *              independend placeholder
   */
    public function ReplaceAbsoluteMediaUrl($sContent,$sUrl=null,$sMedia=null)
    {
        if (is_string($sContent)) {
            if (is_null($sUrl))
            {
                $AppUrl  = $this->oReg->AppUrl;
            }
            else
            {
                $AppUrl = rtrim($sUrl,'/\\').'/';
            }
            if (is_null($sMedia))
            {
                $MediaDir  = $this->oReg->MediaDir;
            }
            else
            {
                $MediaDir= ($sMedia ?? $oReg->MediaDir);
            }

            $sRelUrl = preg_replace('/^https?:\/\/[^\/]+(.*)/is', '\1', $AppUrl);
            $sDocumentRootUrl = str_replace($sRelUrl, '', $AppUrl);
            $sMediaUrl = $AppUrl.$MediaDir.'';
            $aSearchfor = [
                '@(<[^>]*=\s*")('.preg_quote($sMediaUrl).
                ')([^">]*".*>)@siU', '@(<[^>]*=\s*")('.preg_quote($AppUrl.'').')([^">]*".*>)@siU',
                '/(<[^>]*?=\s*\")(\/+)([^\"]*?\"[^>]*?)/isU',
                '/(<[^>]*=\s*")('.preg_quote($sMediaUrl, '/').')([^">]*".*>)/siU'
            ];
            $aReplacements = [
                '$1{SYSVAR:AppUrl.MediaDir}$3',
                '$1{SYSVAR:AppUrl}$3',
                '\1'.$sDocumentRootUrl.'/\3',
                '$1{SYSVAR:MEDIA_REL}$3'
            ];
            $sContent = preg_replace( $aSearchfor, $aReplacements, ($sContent));
        }
        return $sContent;
    }


/**
 * get all defined variables from an info.php file
 * @param string $sFilePath  full path and filename
 * @return array containing all settings (empty array on error)
 */
    public function getContentFromInfoFile($sFilePath)
    {
        $aInfo = [];
        if (is_readable($sFilePath)) {
            $aOldVars = [];
            $aOldVars = get_defined_vars();
            include $sFilePath;
            $aNewVars = get_defined_vars();
            $aInfo = array_diff_key($aNewVars, $aOldVars);
            $aCommon = [];
            foreach ($aInfo as $key => $val) {
                if (is_array($val)) { continue; }
                $sShortKey = str_replace(array('template_', 'module_', 'addon_','language_'), '', $key);
                $aCommon[$sShortKey] = $val;
                unset($aInfo[$key]);
            }
            $aInfo['common'] = $aCommon;
        }
        return $aInfo;
    } // end of getContentFromInfoFile()

/**
 * creates a runtime-unique token with n digits length
 * @staticvar array $aTokens
 * @param int $iDigits
 * @return string
 */
    public function getUniqueToken($iDigits = 4)
    {
//        return \bin\SecureTokens::getUniqueFreeToken($iDigits);
          return (new Randomizer())->getHexString($iDigits);
    }  // end of getUniqueToken

// prevent folder names
    public function isAllowedRootFolder($sFolderName)
    {
        $aBlockFolders = [
            'account',
             trim($this->oReg->AcpDir, '/'),
            'framework',
            'include',
            'install',
            'languages',
            'modules',
#             trim(PAGES_DIRECTORY, '/'),
            'search',
            'temp',
            'templates',
            'logs',
            'var'
        ];
        $aList = \preg_split('/[\s,=+\;\:\/\.\|]+/', $sFolderName, -1, \PREG_SPLIT_NO_EMPTY);
        $bRetval = (isset($aList[0]) && in_array($aList[0], $aBlockFolders));
        return $bRetval;
    }
// basename for multibyte // works both in windows and unix
    public function mb_basename($path)
    {
        $matches = [];
        if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        } else if (preg_match('@^([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        }
        return '';
    }

    public function convertToArray($sList,$Pattern='/[\s,=+\;\:\-\.\|]+/')
    {
      if (\is_array($sList)){
          return $sList;
      }
      return \preg_split($Pattern, $sList, -1, \PREG_SPLIT_NO_EMPTY);
    }

    /**
     * wb::getSettings()
     *
     * @param mixed $mNames array or string or empty
     * @return array of data rows
     * @created 20200605
     * @example
     *      getSettings(); return all data rows
     * parameter as list of names
     *      getSettings(['website_title','jquery_version']);
     *      getSettings('website_title,jquery_version');
     * parameter with wildcards
     *      getSettings('wbmailer_%,server_email,%wb%');
     *      getSettings(['wbmailer_%','server_email','%wb%']);
     *
     */
    public function getSettings($mNames=null){
        $aSettings = null;
        $aNames = $this->convertToArray($mNames);
        $aLikes = [];
        $sNames  = '';
        $sLikes  = '';
        $sqlWhere = $sqlLikesWhere = '';
        foreach($aNames as $item){
            if (preg_match('/[\%_]/',$item)){
                $aLikes[] = '\''.$item.'\'';
            }else {
               $sNames .= '\''.$item.'\',';
            }
        }
        $sqlWhere = (!empty($sNames) ? 'WHERE `name` IN ('.trim($sNames,',').') ' : $sqlWhere);
        foreach ($aLikes as $sLikes) {
            $sqlWhere .= (!empty($aLikes) && !empty($sqlWhere) ? 'OR `name` LIKE ('.$sLikes.') ' : $sqlWhere);
            $sqlWhere .= (!empty($aLikes) &&  empty($sqlWhere) ? 'WHERE `name` LIKE ('.$sLikes.') ' : '');
        }
        $sql = 'SELECT * FROM `'.TABLE_PREFIX.'settings` '
             .  $sqlWhere
             . 'ORDER BY `name`';
        if ($oSetting = $this->oDb->query($sql)) {
            while (!\is_null($aSetting = $oSetting->fetchAssoc())) {
                $aSettings[$aSetting['name']] = $aSetting['value'];
            } // end while settings for jquery
        }
        if ($this->oDb->is_error()) {
            throw new \DatabaseException($this->oDb->get_error());
        }
        return $aSettings;
    }

/**
 * Convert a camel case string to underscores (eg: camelCase becomes camel_case)
 *
 * @param    string  The string to convert
 * @return   string
 * @deprecated since revision 22 from 2020/09/15
 */
    public function camelCase2Under(string $sString): string
    {
        return \mb_camelcase_to_underscore($sString);
    }

/**
 * Convert strings with underscores into CamelCase
 *
 * @param    string  $string    The string to convert
 * @param    bool    $first_char_caps    camelCase or CamelCase
 * @return   string  The converted string
 * @deprecated since revision 22 from 2020/09/15
 */
    public function under2camelCase(string $sString, bool $bUpperCamelCase = false): string
    {
        return \mb_underscore_to_camelcase($sString, $bUpperCamelCase);
    }

/**
 * Generates an integer ID based on the name of a request parameter
 * @param string $sParamName  must be the name of a request parameter (GET|POST) which
 *                            contains a valid IdKey for an integer ID or a real numeric ID
 * @return int an integer ID or 0 on Error
 */
    public function getIdFromRequest(string $sParamName):? int
    {
        $iId = null;
        $mValue = (CsfrTokens::isValidIdkey($sParamName) ? $sParamName : $this->oRequest->getParam($sParamName));
        if (CsfrTokens::isValidIdkey($mValue)) {
            $iId = (int)CsfrTokens::decodeIdKey((string) $mValue);
        } elseif (\preg_match('/[0-9]+/', (string) $mValue)) {
            $iId = (int) $mValue;
        } else {
           $iId = null;
        }
        return $iId;
    }

    /**
     * removeExtension()
     *
     * @param string $sFilename
     * @return strin without extension
     */
    public function removeExtension ($sFilename)
    {
        return \preg_replace("/^.*?([^\/]*?)\.[^\.]*$/iu", "$1", $sFilename);
    }

    /**
     * getUniqueName()
     *
     * @param mixed $sTargetPath
     * @param mixed $sName
     * @param string $sPattern
     * @return
     */
    public function getUniqueName($sTargetPath='', $sName='', $sPattern='*')
    {
        if (!empty($sName)){
            $sBaseName = \preg_replace('/^(.*?)(\_[0-9]+)?$/', '$1', $sName);
            $aMaxNames = \glob($sTargetPath.$sBaseName.$sPattern, GLOB_NOSORT);
            \sort($aMaxNames);
            $sMaxName  = \basename(\end($aMaxNames));
            $iCount    = \intval(\preg_replace('/[^0-9\.]/', '', $sMaxName))+1;
            $sName     = $sBaseName.\sprintf('_%03d', $iCount++);
        }
        return $sName;
    }

    /**
     * getDirNamespace()
     *
     * @param string $sPathName
     * @param string $sSep
     * @return string like modulename\\addonname
     */
    public function getDirNamespace($sPathName='',$sSep='\\'):? string
    {
        $mRetval = null;
        if (!empty($sPathName)){
            $sFile    = $this->removeExtension(\basename($sPathName));
            $sPath    = \basename(\dirname($sPathName));
            $mRetval = $sPath.\preg_quote($sSep).$sFile;
        }
        return $mRetval;
    }

    public function getRequestVars(){
        $requestMethod = \strtoupper($this->oReg->Request->getServerVar('REQUEST_METHOD'));
        $aRetval  = [];
// get POST or GET requests, never both at once
        $aVars = $this->oReg->Request->getParamNames();
        foreach ($aVars as $sName) {
            $aRetval[$sName] = $this->oReg->Request->getParam($sName);
        }
        return ($aRetval);
    }
}  // end of class wb

