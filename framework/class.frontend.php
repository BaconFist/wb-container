<?php
/**
 *
 * @category        frontend
 * @package         framework
 * @author          Ryan Djurovich (2004-2009), WebsiteBaker Project
 * @copyright       2009-2018, WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.0
 * @requirements    PHP 7.0 and higher
 * @version         $Id: class.frontend.php 1 2019-07-13 20:51:57Z root $
 * @filesource      $HeadURL: svn://svn.websitebaker.org/wb/2.13.x/branches/main/framework/class.frontend.php $
 * @lastmodified    $Date: 2019-07-13 22:51:57 +0200 (Sa, 13. Jul 2019) $
 *
*/
//namespace bin;

use bin\{WbAdaptor,wb,SecureTokens,Sanitize};
use vendor\phplib\Template;
use bin\Exceptions\ErrorHandler;

if (class_exists('frontend',false)){return;}

class frontend extends wb {


    // defaults
    public $default_link,$default_page_id;
    // when multiple blocks are used, show home page blocks on
    // pages where no content is defined (search, login, ...)
    public $default_block_content=true;
    // page details
    // page database row
    public $page;
    public $page_id,$page_code,$page_title,$menu_title,$parent,$root_parent,$level,$position,$visibility;
    public $page_description,$page_keywords,$page_link, $page_icon, $menu_icon_0, $menu_icon_1, $tooltip;
    public $page_trail=[];
    public $page_access_denied;
    public $page_no_active_sections;
    // website settings
    public $website_title,$website_description,$website_keywords,$website_header,$website_footer;
    // ugly database stuff
    public $extra_where_sql, $sql_where_language;
    //
    protected $bTriggerError = true; //   false
    protected $aProperties = [];

    public function __construct($value=true) {
        parent::__construct(1);
//        echo nl2br(sprintf("%d CLASS class.%s\n",__LINE__,__CLASS__));
        $this->FrontendLanguage = (isset($value) ? $value : true);
    }


    public function ChangeFrontendLanguage( $value=true ) {
        $this->FrontendLanguage=$value;
    }

    public function PageSelect(){
        $this->page_select();
    }

    public function getPageDetails(){
        $this->get_page_details();
    }

    public function getWebsiteSettings(){
        $this->get_website_settings();
    }

    public function page_select() {
        global $page_id, $no_intro;
/*
 * Store installed languages in SESSION
 */
        if ($this->get_session('session_started')) {}
        $bMaintance  = (bool)$this->oReg->SystemLocked; //
        $bUserLogin  = (bool)$this->oReg->UserLogin; //
        if( ($bMaintance===true) || $this->get_session('USER_ID')!= 1 )
        {
           //  check for show maintenance screen and terminate if needed
            $this->ShowMaintainScreen('locked');
        }
        // We have no page id and are supposed to show the intro page
        if ((INTRO_PAGE && ($bMaintance != true) && !isset($no_intro)) && (!isset($page_id) || !\is_numeric($page_id)))
        {
            // Since we have no page id check if we should go to intro page or default page
            // Get intro page content
            $sIntroFilename = PAGES_DIRECTORY.'/intro'.PAGE_EXTENSION;
            if (\file_exists(WB_PATH.$sIntroFilename)) {
                // send intro.php as header to allow parsing of php statements
                \header("Location: ".WB_URL.$sIntroFilename."");
                exit();
            }
        }
        // Check if we should add page language sql code
        if ($this->oReg->PageLanguages) {
            $this->sql_where_language = ' AND `language` IN (\''.LANGUAGE.'\') ';
        }
        // Get default page
        // Check for a page id
        $table_p = TABLE_PREFIX.'pages';
        $table_s = TABLE_PREFIX.'sections';
        $iNow = \time();
        $sql  = ''
        . 'SELECT `p`.`page_id`, `link` '
        . 'FROM `'.$table_p.'` AS `p` INNER JOIN `'.$table_s.'` USING(`page_id`) '
        . 'WHERE `parent` = 0 AND `visibility` = \'public\''
        . '  AND ('.$iNow.' BETWEEN `publ_start` AND `publ_end`) ';
// fix if null
        $sLang = \trim($this->sql_where_language ?? '');
        if (!empty($sLang)) {
            $sql .= $sLang.' ';
        }
        $sql .= 'ORDER BY `p`.`position` ASC';
        if ($get_default = $this->oDb->query($sql)) {
            $default_num_rows = $get_default->numRows();
            if (!isset($page_id) || !\is_numeric($page_id)){
                // Go to or show default page
                if ($default_num_rows > 0) {
                    $fetch_default = $get_default->fetchArray(MYSQLI_ASSOC);
                    $this->default_link    = $fetch_default['link'];
                    $this->default_page_id = $fetch_default['page_id'];
                    // Check if we should redirect or include page inline
                    if (HOMEPAGE_REDIRECTION) {
                        // Redirect to page
                        $this->send_header($this->page_link($this->default_link));
                    } else {
                        // Include page inline
                        $this->page_id = $this->default_page_id;
                    }

                } else {
                       // No pages have been added, so print under construction page
                    $this->ShowMaintainScreen('new');
                    exit();
                }
            } else {
                $this->page_id = $page_id;
            }
            // Get default page link
            if (!isset($fetch_default)) {
                if (($fetch_default = $get_default->fetchArray(MYSQLI_ASSOC))){
                  $this->default_link    = $fetch_default['link'];
                  $this->default_page_id = $fetch_default['page_id'];
                }

            }
        } else {
            $this->ShowMaintainScreen('new');
            exit();
        }
        return true;
    }

    public function get_page_details()
    {
        $bHeaderOldLocation = false;  // show QUERY_STRING ?lang=XX
        if ($this->page_id == 0 && $this->default_page_id != 0){
            $this->page_id = $this->default_page_id;
        }
        if ($this->page_id != 0)
        {
            $iNow  = time();
            $aPage = [];
            // Query page details
            $sSqlSet = '
            SELECT `s`.*
            ,`p`.*
            FROM `'.TABLE_PREFIX.'sections` `s`
            INNER JOIN `'.TABLE_PREFIX.'pages` `p`
            ON `p`.`page_id`=`s`.`page_id`
            WHERE `s`.`page_id` = '.( int)$this->page_id.'
              AND (('.$iNow.' BETWEEN `s`.`publ_start` AND `s`.`publ_end`)
               OR  ('.$iNow.' > `s`.`publ_start` AND `s`.`publ_end`=0))
            AND `p`.`visibility` NOT IN (\'deleted\',\'none\')
            ';
            $oPage = $this->oDb->query($sSqlSet);
            // Make sure page was found in database
            if ($oPage->numRows() == 0) {
            //  call first page in tree
                $sSql = 'SELECT * FROM `'.TABLE_PREFIX.'pages` WHERE `page_id`='.(int)$this->page_id;
                if (!$oPage = $this->oDb->query($sSql)){
//                   if ($oPage->numRows() == 0) {
//                $msg = sprintf('[%1$d] There is no active section on page %2$s!!<br>',__LINE__,$this->page_id);
//                $sLink = '<a href="'.WB_URL.PAGES_DIRECTORY.$this->default_link.PAGE_EXTENSION.'" >Home'.'</a>';
//                $this->ShowMaintainScreen('error',$msg,$sLink);
//                exit();
//                }
                }
            }
            //  Fetch page details
            $aPage = $oPage->fetchRow( MYSQLI_ASSOC );
            $this->page = $aPage;
//  Check if the page language is also the selected language. If not send headers again.
//  Tks to Ruud for this little awesome code change, now the ?lang= parameter will not be used anymore
//            \trigger_error(\sprintf('[%03d] page[language] %s != language %s ',__LINE__, $this->page['language'],$this->oReg->Language),E_USER_NOTICE);
            if ($this->page['language'] != LANGUAGE) {
                if ($bHeaderOldLocation) {
/* */
                    if(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') {
                        header('Location: '.$this->page_link($this->page['link']).'?'.$_SERVER['QUERY_STRING'].'&lang='.$this->page['language']);
                    } else {
                        header('Location: '.$this->page_link($this->page['link']).'?lang='.$this->page['language']);
                    }
                } else {
                    $_SESSION['LANGUAGE'] = $this->page['language'];
//  check if there is an query-string from shorturl
                    if ($this->oRequest->issetParam('_wb')) {
                        $sLocation = $this->oRequest->getParam('_wb');
//        \trigger_error(\sprintf('[%03d] $sLocation %s',__LINE__, $sLocation),E_USER_NOTICE);
                        \header('Location: '.$this->oReg->AppUrl.$sLocation);
                    } else {
                        $sLocation = $this->page_link($this->page['link']);
//        \trigger_error(\sprintf('[%03d] sLocation -> %s',__LINE__, $sLocation),E_USER_NOTICE);
                        \header('Location: '.$sLocation);
                    }
                }
                exit();
            }
            // Begin code to set details as either variables of constants
            // Page ID
            if (!\defined('PAGE_ID')) {\define('PAGE_ID', $this->page['page_id']);}
            // Page Code
            if (!\defined('PAGE_CODE')) {\define('PAGE_CODE', $this->page['page_code']);}
            $this->page_code  = PAGE_CODE;
            // Page Title
            if (!\defined('PAGE_TITLE')) {\define('PAGE_TITLE', $this->page['page_title']);}
            $this->page_title = PAGE_TITLE;
            // Menu Title
            $menu_title = $this->page['menu_title'];
            if ($menu_title != '') {
                if (!\defined('MENU_TITLE')) {\define('MENU_TITLE', $menu_title);}
            } else {
                if (!\defined('MENU_TITLE')) {\define('MENU_TITLE', PAGE_TITLE);}
            }
            $this->menu_title  = MENU_TITLE;
            $this->page_icon   = $this->page['page_icon'];
            $this->menu_icon_0 = $this->page['menu_icon_0'];
            $this->menu_icon_1 = $this->page['menu_icon_1'];
            $this->tooltip     = $this->page['tooltip'];
            // Page parent
            if (!\defined('PARENT')) {\define('PARENT', $this->page['parent']);}
            $this->parent=$this->page['parent'];
            // Page root parent
            if (!\defined('ROOT_PARENT')) {\define('ROOT_PARENT', $this->page['root_parent']);}
            $this->root_parent = $this->page['root_parent'];
            // Page level
            if (!\defined('LEVEL')) {\define('LEVEL', $this->page['level']);}
            $this->level = $this->page['level'];
            // Page position
            $this->level = $this->page['position'];
            // Page visibility
            if (!\defined('VISIBILITY')) {\define('VISIBILITY', $this->page['visibility']);}
            $this->visibility=$this->page['visibility'];
            // Page trail
            foreach(\explode(',', $this->page['page_trail']) AS $pid) {
                $this->page_trail[$pid]=$pid;
            }
            // Page description
            $this->page_description=$this->page['description'];
            if ($this->page_description != '') {
                if (!\defined('PAGE_DESCRIPTION')){\define('PAGE_DESCRIPTION', $this->page_description);}
            } else {
                if (!\defined('PAGE_DESCRIPTION')){\define('PAGE_DESCRIPTION', WEBSITE_DESCRIPTION);}
            }
            // Page keywords
            $this->page_keywords       = $this->page['keywords'];
            // Page link
            $this->link               = $this->page_link($this->page['link']);
            $_SESSION['PAGE_ID']      = $this->page_id;
            $_SESSION['HTTP_REFERER'] = $this->link;

        // End code to set details as either variables of constants
        }// page_id not 0

        // Figure out what template to use
        if (!\defined('TEMPLATE')) {
            if (isset($this->page['template']) && $this->page['template'] != '') {
                if (\file_exists(WB_PATH.'/templates/'.$this->page['template'].'/index.php')) {
                    if (!\defined('TEMPLATE')){\define('TEMPLATE', $this->page['template']);}
                } else {
                    if (!\defined('TEMPLATE')){\define('TEMPLATE', DEFAULT_TEMPLATE);}
                }
            } else {
                if (!\defined('TEMPLATE')){\define('TEMPLATE', DEFAULT_TEMPLATE);}
            }
        }
        //  Set the template dir
        if (!\defined('TEMPLATE_DIR')){\define('TEMPLATE_DIR', WB_URL.'/templates/'.TEMPLATE);}

        //  Check if user is allowed to view this page
        if ($this->page && $this->page_is_visible($this->page) == false) {
            if (in_array(VISIBILITY,['deleted','none'])) {
                // User isnt allowed on this page so tell them
                $this->page_no_active_sections = true;
            } elseif (in_array(VISIBILITY,['private','registered'])) {
//            } elseif(VISIBILITY == 'private' || VISIBILITY == 'registered') {
                //  Check if the user is authenticated
                if ($this->is_authenticated() == false) {
                    //  User needs to login first $wb->send_
                    $this->send_header(WB_URL."/account/login.php?redirect=".$this->link);  //  ."&page_id=".$this->page['page_id']
                    exit(0);
                } else {
                    //  User isnt allowed on this page so tell them
                    $this->page_access_denied=true;
                }
            }
        }
//      check if there is at least one active section
//        $this->page_no_active_sections = ($this->page && !$this->page_is_active($this->page) && in_array(VISIBILITY,['deleted','none']));
    }//end of get_page_details

    public function get_website_settings()
    {
        //  set visibility SQL code
        //  never show no-vis, hidden or deleted pages
        $this->extra_where_sql = '`visibility` NOT IN (\'none\', \'hidden\',\'deleted\')';
        // Set extra private sql code
        if ($this->is_authenticated() == false) {
            // if user is not authenticated, don't show private pages either
            $this->extra_where_sql .= ' AND `visibility` != \'private\'';
            // and 'registered' without frontend login doesn't make much sense!
            if (FRONTEND_LOGIN == false) {
                $this->extra_where_sql .= ' AND `visibility`!=\'registered\'';
            }
        }
        $this->extra_where_sql .= $this->sql_where_language;

        //  Work-out if any possible in-line search boxes should be shown
        if (SEARCH == 'public') {
            if (!defined('SHOW_SEARCH')){\define('SHOW_SEARCH', true);}
        } elseif(SEARCH == 'private' AND VISIBILITY == 'private') {
            if (!defined('SHOW_SEARCH')){\define('SHOW_SEARCH', true);}
        } elseif(SEARCH == 'private' AND $this->is_authenticated() == true) {
            if (!defined('SHOW_SEARCH')){\define('SHOW_SEARCH', true);}
        } elseif(SEARCH == 'registered' AND $this->is_authenticated() == true) {
            if (!defined('SHOW_SEARCH')){\define('SHOW_SEARCH', true);}
        } else {
            if (!defined('')){\define('SHOW_SEARCH', false);}
        }
        //  Work-out if menu should be shown
        if (!\defined('SHOW_MENU')) {\define('SHOW_MENU', true);}
//        $bUserLogin = (\defined('USER_LOGIN') && (USER_LOGIN==true) ? true : false );
        $bUserLogin  = (bool)$this->oReg->UserLogin; //
        $bFrontendLogin = (($bUserLogin==true) || $this->getUserId() == 1);
        //  Work-out if login menu constants should be set
        if (FRONTEND_LOGIN && $bFrontendLogin) {
            //  Set login menu constants
            if (!defined('LOGIN_URL')){\define('LOGIN_URL', WB_URL.'/account/login.php');}
            if (!defined('LOGOUT_URL')){\define('LOGOUT_URL', WB_URL.'/account/logout.php');}
            if (!defined('FORGOT_URL')){\define('FORGOT_URL', WB_URL.'/account/forgot.php');}
            if (!defined('PREFERENCES_URL')){\define('PREFERENCES_URL', WB_URL.'/account/preferences.php');}
            if (!defined('SIGNUP_URL')){\define('SIGNUP_URL', WB_URL.'/account/signup.php');}
        } else {
            if (!defined('LOGIN_URL')){\define('LOGIN_URL', '');}
            if (!defined('LOGOUT_URL')){\define('LOGOUT_URL', '');}
            if (!defined('FORGOT_URL')){\define('FORGOT_URL', '');}
            if (!defined('PREFERENCES_URL')){\define('PREFERENCES_URL', '');}
            if (!defined('SIGNUP_URL')){\define('SIGNUP_URL','');}
        }
    }

/*
 * replace all "[wblink{page_id}]" with real links
 * @param string &$content : reference to global $content
 * @return void
 * @history 100216 17:00:00 optimise errorhandling, speed, SQL-strict
 */
    public function preprocess(&$content)
    {
      //  \trigger_error(sprintf('%s call in %s is obsolete and will be removed ',__METHOD__,$_SERVER["SCRIPT_NAME"]), E_USER_ERROR);
      //  do nothing
    }

    public function menu() {
        \trigger_error(sprintf('%s call is obsolete and will be removed ',__METHOD__), E_USER_ERROR);
        return false;
    }

    public function show_menu() {
        \trigger_error(sprintf('%s call is obsolete and will be removed ',__METHOD__), E_USER_ERROR);
        return false;
    }

    // Function to show the "Under Construction" page
    public function print_under_construction() {
        $this->ShowMaintainScreen('new');
        exit();
    }

    // Function to show the "Under Construction" page
    public function print_missing_frontend_login() {
        global $MESSAGE, $MENU, $TEXT;
        $sErrMsg = sprintf('%s %s ',$MENU['LOGIN'],$TEXT['DISABLED']);
        $this->ShowMaintainScreen('error',$sErrMsg);
        exit();
    }

}
