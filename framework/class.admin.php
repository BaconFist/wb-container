<?php
/**
 *
 * @category        framewotk
 * @package         backend admin
 * @author          Ryan Djurovich, WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.2
 * @requirements    PHP 7.4 and higher
 * @version         $Id: class.admin.php 354 2019-05-25 19:53:49Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/framework/class.admin.php $
 * @lastmodified    $Date: 2019-05-25 21:53:49 +0200 (Sa, 25. Mai 2019) $
 *
 */

use bin\{WbAdaptor,wb};
use vendor\phplib\Template;
use bin\Exceptions\ErrorHandler;

class admin extends wb {

    private $section_name;
    private $current_section_name;
    private $section_permission;

    // Authenticate user then auto print the header
    public function __construct($section_name= '##skip##', $section_permission = 'start', $auto_header = true, $auto_auth = true)
    {
        parent::__construct(1);
        $sAddonPath = $this->oReg->DocumentRoot.ltrim($this->getCallingScript(),'/');
// TODO remove to methods in class.wb
        // Only for development for pretty mysql dump
        $bLocalDebug  =  is_readable($sAddonPath.'.setDebug');
        // Only for development prevent secure token check,
        $bSecureToken = !is_readable($sAddonPath.'.setToken');
        $sPHP_EOL     = ($bLocalDebug ? "\n" : '');
        $sDumpPathname = \basename(dirname($sAddonPath)).'/'.\basename($sAddonPath);
        if ($section_name != '##skip##' ){
            if ($bLocalDebug){
                \trigger_error(sprintf('[%d] <b>AFTER instance admin wrapper</b> from %s',__LINE__,$sDumpPathname), E_USER_NOTICE);
//                \trigger_error(sprintf("[%d] new admin(%s,%s) ",__LINE__, $section_name,$section_permission), E_USER_NOTICE);
            }
            // Specify the current applications name
            $this->section_name = $section_name;
            $this->current_section_name = $this->under2camelCase('MENU_'.$this->section_name);
            $this->section_permission = $section_permission;
            $maintance  = (\defined('SYSTEM_LOCKED') && (SYSTEM_LOCKED == true) ? 'true' : 'false');
            $bUserLogin = (\defined('USER_LOGIN')    && (USER_LOGIN == true)    ? 'true' : 'false');
            // Authenticate the user for this application
            if ($auto_auth == true){
                // First check if the user is logged-in
                if($this->is_authenticated() == false){
                    $this->send_header(ADMIN_URL.'/login/index.php');
                    exit(0);
                }
                // Now check if they are allowed in this section
                if ($this->get_permission($section_permission) == false) {
                    $sLink = '<a class="back-link" href="'.ADMIN_URL.'/start/index.php'.'" >'.$this->oTrans->OVERVIEW_START.'</a>';
                    $sErrMsg = \sprintf('%s ',$this->oTrans->MESSAGE_ADMIN_INSUFFICIENT_PRIVELLIGES);
                    $this->ShowMaintainScreen('error',$sErrMsg,$sLink);
                }
            }
            if (($maintance == true) || $this->get_session( 'USER_ID') != 1) {
              //  check for show maintenance screen and terminate if needed
              $this->ShowMaintainScreen( 'locked');
            }
            if (($bUserLogin === true) || $this->get_session( 'USER_ID') == 1) {
            // Check if the backend language is also the selected language. If not, send headers again.
                $sql  = 'SELECT `language` FROM `'.TABLE_PREFIX.'users` ';
                $sql .= 'WHERE `user_id`='.(int)$this->getUserId();
                $user_language = $this->oDb->get_one($sql);
                $admin_folder = \str_replace(WB_PATH, '', ADMIN_PATH);
                $sCallingScript = $this->getCallingScript();
                if ((LANGUAGE != $user_language)
                      && \is_readable(WB_PATH .'/languages/' .$user_language .'.php')
                        && (\strpos($sCallingScript,$admin_folder.'/') !== false)
                ) {
                    // check if page_id is set
                    $page_id_url    = (isset($_GET['page_id'])) ? '&page_id=' .(int) $_GET['page_id'] : '';
                    $section_id_url = (isset($_GET['section_id'])) ? '&section_id=' .(int) $_GET['section_id'] : '';
/*
                    $mPageId        =  $this->oRequest->getParam('page_id');
                    $page_id_url    = ($mPageId ? '&page_id='.$mPageId : '');
                    $mSectionId     =  $this->oRequest->getParam('section_id');
                    $section_id_url = ($mSectionId ? '&section_id='.$mSectionId : '');
*/
                    $sScriptUrl     = ($this->oReg->getServerVar["SCRIPT_NAME"] ?? ($sCallingScript ?? ADMIN_URL));
                    $sQuerString    =  $this->oRequest->getServerVar('QUERY_STRING');
                    // check if there is an query-string
                    if (!(empty($sQuerString))) {
                        \header('Location: '.$sScriptUrl.'?lang='.$user_language .'&'.$sQuerString);
                    } else {
                        \header('Location: '.$sScriptUrl.'?lang='.$user_language .$page_id_url .$section_id_url);
                    }
                    exit();
                }
            } else {
                $this->ShowMaintainScreen('new');
            }
            // Auto header code
            if ($auto_header == true) {
                $this->print_header($auto_header);
            }
        }
    }

    public function getSection (){
      return $this->section_name;
    }

    public function getCurrentSection(){
      return ($this->current_section_name ?? 'main');
    }

    private function mysqlVersion() {
      $sql = 'SELECT VERSION( ) AS versionsinfo';
      if( $oRes = ($this->oDb->query($sql)) ) {
          $aRes = $oRes->fetchRow(MYSQLI_ASSOC);
          return $aRes['versionsinfo'];
      }
      return 0;
    }

    private function mysqlStrict() {
      $retVal ='';
      $sql = 'SELECT @@global.sql_mode AS strictinfo';
      if ($oRes = ($this->oDb->query($sql)) ) {
          $aRes = $oRes->fetchRow(MYSQLI_ASSOC);
          $retVal = $aRes['strictinfo'];
      }
      return \is_numeric(\strpos( $retVal,'STRICT' ));
    }

    public function print_info (){
//        global $this->oTrans->MENU, $this->oTrans->MESSAGE, $this->oTrans->TEXT;
// Create new template object with phplib
        $oTpl = new Template(\dirname($this->correct_theme_source('call_help.htt')));
        $oTpl->set_file('page', 'call_help.htt');
        $oTpl->set_block('page', 'main_block', 'main');
        $aLang = [
            'CANCEL' => $TEXT['CANCEL'],
            'TITLE_INFO' => 'WebsiteBaker System-Info',
        ];
        $aTplDefaults = [
            'ADMIN_URL' => ADMIN_URL.'',
            'INFO_URL' => ADMIN_URL.'/start/wb_info.php',
            'sAddonThemeUrl' => THEME_URL.'',
        ];
        $oTpl->set_var($aLang);
        $oTpl->set_var($aTplDefaults);
/*-- finalize the page -----------------------------------------------------------------*/
        $oTpl->parse('main', 'main_block', false);
        $oTpl->pparse('output', 'page');
    }

    // Print the admin header
    public function print_header($mBodyTags=null,$bPrintHeader=true)
    {
        $body_tags = '';
        if (!is_null($mBodyTags) && is_array($mBodyTags)) {
          foreach ($mBodyTags as $value){;}   //TODO
        } else {
            $body_tags = ($mBodyTags ?? '');
        }

        $oReg = $this->oReg;
        $oRequest = $this->oRequest;

        $this->oTrans->enableAddon('templates\\'.DEFAULT_THEME);
        // Get vars from the language file
        if (!\defined('VERSION')) {require (ADMIN_PATH.'/interface/version.php');}
        $this->oTrans->enableAddon('templates\\'.DEFAULT_THEME);

        // Connect to database and get website title
        // $GLOBALS['FTAN'] = $this->getFTAN();
//        $this->createFTAN();
// print header meta
        if (!$bPrintHeader)
        {
        // Setup template object, parse vars to it, then parse it
            $header_template = new Template(\dirname($this->correct_theme_source('optionalHeader.htt')));
            $header_template->set_file('page', 'optionalHeader.htt');
            $header_template->set_block('page', 'header_block', 'header');
            if (\defined('DEFAULT_CHARSET')) {
                $charset=DEFAULT_CHARSET;
            } else {
                $charset='utf-8';
            }
            $datalist['Header'] =
                        [
                            'FTAN_GET'      => ( DEBUG ? $this->getFTAN('GET') : '' ),
                            'IS_ADMIN'      => ($this->getUserId()==1),
                            'TEMPLATE_DIR'  => DEFAULT_THEME,
                            'CHARSET'       => $charset,
                            'LANGUAGE'      => \strtolower(LANGUAGE),
                            'WB_URL'        => WB_URL,
                            'WB_REL'        => WB_REL,
                            'ADMIN_URL'     => ADMIN_URL,
                            'THEME_URL'     => THEME_URL,
                        ];
            $header_template->set_var($datalist['Header'] );
        }
        else
        {
        // Setup template object, parse vars to it, then parse it
            $header_template = new Template(\dirname($this->correct_theme_source('header.htt')));
            $header_template->set_file('page', 'header.htt');
            $header_template->set_block('page', 'header_block', 'header');
            if (\defined('DEFAULT_CHARSET')) {
                $charset=DEFAULT_CHARSET;
            } else {
                $charset='utf-8';
            }
        // work out the URL for the 'View menu' link in the WB backend
            $iPageId  = \intval((isset($_GET['page_id']) && \is_numeric($_GET['page_id'])) ? $_GET['page_id'] : 0);
        // if the page_id is set, show this page otherwise show the root directory of WB
            $view_url = WB_URL.'/';
            $sViewTitle = \sprintf($this->oTrans->TEXT_PREVIEW_PAGE, $this->oTrans->MENU_START);
            $info_url = (($this->getUserId()==1) ? ADMIN_URL.'/start/info.php' : ADMIN_URL);
            if ($iPageId)
            {
            // extract page link from the database
                $sql = 'SELECT `link`,`menu_title` FROM `'.TABLE_PREFIX.'pages` '
                     . 'WHERE `page_id`='.$iPageId;
                if ($oRresult = $this->oDb->query($sql)){
                    $aRresult = $oRresult->fetchRow(MYSQLI_ASSOC);
                }
               $view_url = $this->page_link($aRresult['link']);
               $sViewTitle = '';
               $sViewTitle = \sprintf($this->oTrans->TEXT_PREVIEW_PAGE, $aRresult['menu_title']);
            }

            $convertToReadableSize = (function ($size){
              $base = \log($size) / \log(1024);
              $suffix = ['', ' KB', ' MB', ' GB', ' TB'];
              $f_base = \floor($base);
              return \round(\pow(1024, $base - \floor($base)), 1) . $suffix[$f_base];
            });
            $sIconPost = '0';
            $iFileSize = 0;
            $aFileStat = [];
            $sErrorlogFile = ErrorHandler::getLogFile();
            $sErrorlogUrl  = \str_replace(WB_PATH, WB_URL, $sErrorlogFile);
            if (\is_readable($sErrorlogFile)){
                \clearstatcache($sErrorlogFile);
                $iFileSize = \filesize($sErrorlogFile);
                $sIconPost = (($iFileSize>8500) ? 'red' : (($iFileSize<284) ? 'green' : 'amber'));
            }
            $header_template->set_var('ERROR_SIZE', $convertToReadableSize($iFileSize));
//            $header_template->set_var('ERROR_MSG', $sErrorlogMsg);
            $header_template->set_var('ERROR_LOG', $sErrorlogUrl); // $sErrorlogUrl
            $header_template->set_var('ERROR',$sIconPost);
            $Section = \strtoupper($this->section_name);
//      declare settings defaults if query failed
            $aSettings = ['website_title' => 'none','jquery_version'=> '1.9.1'];
            $aSettings = ($this->getSettings('website_title,jquery_version') ?? $aSettings);
            $this->current_section_name = $this->under2camelCase('MENU_'.$this->section_name);
/*------------------------------------------------------------------------------------*/
        $sTemplateFunc = 'resolveTemplateImagesPath';
        $sImages       = $sTemplateFunc();
/*------------------------------------------------------------------------------------*/
            $datalist['Header'] =
                          [
                            'FTAN_GET'            => ( DEBUG ? $this->getFTAN('GET') : '' ),
                            'IS_ADMIN'            => ($this->getUserId()==1),
                            'WB_VERSION'          => (WB_VERSION ?? '').' '.(WB_SP ?? ''),
                            'SECTION_NAME'        => $this->oTrans->{'MENU_'.$Section},
                            'TEMPLATE_DIR'        => DEFAULT_THEME,
//                            'STYLE'               => \strtolower($this->section_name),
                            'BODY_TAGS'           => $body_tags,
                            'TIME_STAMP'          => time(),
                            'TEXT_ADMINISTRATION' => $this->oTrans->TEXT_ADMINISTRATION,
                            'CURRENT_USER'        => $this->oTrans->MESSAGE_START_CURRENT_USER,
                            'DISPLAY_NAME'        => $this->get_display_name(),
                            'CHARSET'             => $charset,
                            'IMAGES'              => $sImages,
                            'LANGUAGE'            => \strtolower(LANGUAGE),
                            'HELPER_URL'          => WB_URL.'/framework/helpers',
                            'WB_URL'              => WB_URL,
                            'ADMIN_URL'           => ADMIN_URL,
                            'THEME_URL'           => THEME_URL,
                            'THEME'               => (\defined('THEME') ? THEME : DEFAULT_THEME),
                            'TEMPLATE'            => (\defined('TEMPLATE') ? TEMPLATE : DEFAULT_TEMPLATE),
                            'EDITOR'              => WYSIWYG_EDITOR,
                            'TITLE_START'         => $this->oTrans->MENU_START,
                            'TITLE_VIEW'          => $sViewTitle,
                            'TITLE_HELP'          => $this->oTrans->MENU_HELP,
                            'TITLE_INFO'          => 'WebsiteBaker System-Info',
                            'TITLE_LOGOUT'        => $this->oTrans->MENU_LOGOUT,
                            'URL_VIEW'            => $view_url,
                            'INFO_URL'            => $info_url,
                            'URL_HELP'            => 'https://help.websitebaker.org/',
                            'BACKEND_MODULE_CSS'  => $this->register_backend_modfiles('css'),    // adds backend.css
                            'WEBSITE_TITLE'       => $aSettings['website_title'],
                            'JQUERY_VERSION'      => ($aSettings['jquery_version'] ?? '1.9.1').'/',
                            'BACKEND_MODULE_JS'   => $this->register_backend_modfiles('js')      // adds backend.js
                        ];
            $header_template->set_var($datalist['Header'] );

            $header_template->set_block( 'header_block', 'maintenance_block', 'maintenance');
            if ($this->getUserId()==1) {
                $sys_locked = (((int)(\defined('SYSTEM_LOCKED') ? SYSTEM_LOCKED : 0)) == 1);
                $header_template->set_var( 'MAINTENANCE_MODE', ( $sys_locked ? $this->oTrans->TEXT_MAINTENANCE_OFF : $this->oTrans->TEXT_MAINTENANCE_ON));
                $header_template->set_var( 'MAINTENANCE_ICON', ($sys_locked ? 'orange' : ''));
                $header_template->set_var( 'FA_MAINTENANCE_ICON', ($sys_locked ? 'unlock' : 'lock'));
                $header_template->set_var( 'MAINTAINANCE_URL', ADMIN_URL.'/settings/locking.php');
                $header_template->parse( 'maintenance', 'maintenance_block', true);
            } else {
                $header_template->set_block( 'maintenance_block', '');
            }

            $header_template->set_block( 'header_block', 'button_locking_block', 'button_locking');
            if ($this->ami_group_member('1'))
            {
// prevent error msg, if const USER_LOGIN don't exists'
                $bUserLogin = (((int)(\defined('USER_LOGIN') ? USER_LOGIN : 1)) == 1);
                $sTextUserLogin = (\defined('USER_LOGIN') ? ($bUserLogin ? $this->oTrans->TEXT_USER_LOGIN_OFF : $this->oTrans->TEXT_USER_LOGIN_ON) : '');
                $header_template->set_var( 'LOCKING_URL', ADMIN_URL.'/settings/userlogin.php');
                $header_template->set_var( 'LOCKING_MODE', $sTextUserLogin);
                $header_template->set_var( 'LOCKING_ICON', ($bUserLogin ? '' : 'deep-orange'));
                $header_template->set_var( 'FA_LOCKING_ICON', ($bUserLogin ? 'users' : 'user-lock'));
                $header_template->parse( 'button_locking', 'button_locking_block', true);
            } else {
                $header_template->set_block( 'button_locking_block', '');
            }

            $header_template->set_block( 'header_block', 'button_error_block', 'button_error');
            if ( $this->ami_group_member('1')) {
               $header_template->parse( 'button_error', 'button_error_block', true);
            } else {
                $header_template->set_block( 'button_error_block', '');
            }
/* --------------------------------------------------------------------------------- */
        // Create the backend menu
            $aMenu = [
//                    [ADMIN_URL.'/start/index.php',               '', $MENU['START'],       'start',       1],
                    [ADMIN_URL.'/pages/index.php',               '', $this->oTrans->MENU_PAGES,       'pages',       1],
                    [ADMIN_URL.'/media/index.php',               '', $this->oTrans->MENU_MEDIA,       'media',       1],
                    [ADMIN_URL.'/addons/index.php',              '', $this->oTrans->MENU_ADDONS,      'addons',      1],
                    [ADMIN_URL.'/preferences/index.php',         '', $this->oTrans->MENU_PREFERENCES, 'preferences', 0],
                    [ADMIN_URL.'/settings/index.php?advanced=0', '', $this->oTrans->MENU_SETTINGS,    'settings',    1],
                    [ADMIN_URL.'/admintools/index.php',          '', $this->oTrans->MENU_ADMINTOOLS,  'admintools',  1],
                    [ADMIN_URL.'/access/index.php',              '', $this->oTrans->MENU_ACCESS,      'access',      1],
                    ];
            $header_template->set_block('header_block', 'linkBlock', 'link');
            foreach($aMenu as $menu_item)
            {
                $link = $menu_item[0];
                $target = ($menu_item[1] == '') ? '_self' : $menu_item[1];
                $titleMenu = $menu_item[2];
                $permission_title = $menu_item[3];
                $required = $menu_item[4];
                $replace_old = [ADMIN_URL, WB_URL, '/', 'index.php'];
                if ($required == false || $this->get_link_permission($permission_title))
                {
                    $header_template->set_var('LINK', $link);
                    $header_template->set_var('TARGET', $target);
                // If link is the current section apply a class name
                    if ($permission_title == \strtolower($this->section_name)) {
                        $info_url = ($this->getUserId()==1 ? ADMIN_URL.'/start/info.php?url='.$link:ADMIN_URL);
                        $header_template->set_var('CLASS', $menu_item[3] . ' current');
                    } else {
                        $header_template->set_var('CLASS', $menu_item[3]);
                    }
                    $header_template->set_var('TITLE', $titleMenu);
                // Print link
                    $header_template->parse('link', 'linkBlock', true);
                    $header_template->set_block('header_block', 'infoBlockBasis',   'infoBasis');
                    $header_template->set_block('header_block', 'infoBlockExented', 'infoExented');

                    $header_template->set_block('header_block', 'button_info_block', 'button_info');
                    $bCanShowInfoBlock = ($this->ami_group_member('1') || ($this->getUserId()=='1'));
                    if (!$bCanShowInfoBlock){
                        $header_template->set_block('button_info', '');
                    } else {
                        $header_template->parse('button_info', 'button_info_block', true);
                    }
                    if ((\strtolower($this->section_name) == 'admintools') && (!$bCanShowInfoBlock))
                    {
                        $header_template->set_block('infoBasis', '');
                        $header_template->set_var( array(
                                        'VERSION'             => WB_VERSION,
                                        'SP'                  => (\defined('WB_SP') ? WB_SP : ''),
                                        'REVISION'            => WB_REVISION,
                                        'PHP_VERSION'         => \phpversion(),
                                        'TEXT_EXT_INFO'       => 'SQL Server:',
                                        'EXT_INFO'            => $this->mysqlVersion(),
                                        'EXT_INFO1'           => ( ($this->mysqlStrict())?'STRICT': 'NON STRICT' ),
                                    ) );
                        $header_template->parse('infoExented', 'infoBlockExented', true);
                    } else {
                        $header_template->set_block('infoExented', '');
                        $header_template->set_var([
                                            'VERSION'             => VERSION,
                                            'SP'                  => (\defined('SP') ? SP : ''),
                                            'REVISION'            => REVISION,
                                            'PHP_VERSION'         => \phpversion(),
                                            'SERVER_ADDR'         => ($this->getUserId() == 1
                                                                  ?  ($this->oRequest->getServerVar["SERVER_ADDR"] ?? '127.0.0.1')
                                                                  :  ''),
                                        ]);
                        $header_template->parse('infoBasis', 'infoBlockBasis', true);
                    }
                } // end of get_link_permission && required
            } // end foreach menu
        }// no header
/* --------------------------------------------------------------------------------- */
//
/* --------------------------------------------------------------------------------- */
        $sAddonCssName = \strtolower($this->section_name);
        $bAddonCssFile = (\is_readable($oReg->ThemePath.'css/'.$sAddonCssName.'.css'));
        $header_template->set_block('header_block', 'link_style_block', 'link_style');
        if ($bPrintHeader && $bAddonCssFile){
            $header_template->set_var('STYLE', $sAddonCssName);
            $header_template->parse('link_style', 'link_style_block', true);
        } else {
            $header_template->set_block('link_style_block', '');
        }
//
        $sUserCssName = ('themeUser');
        $bUserCssFile = (\is_readable($oReg->ThemePath.'css/'.$sUserCssName.'.css'));
        $header_template->set_block('header_block', 'user_style_block', 'user_style');
        if ($bPrintHeader && $bUserCssFile){
            $header_template->set_var('USER_STYLE', $sUserCssName);
            $header_template->parse('user_style', 'user_style_block', true);
        } else {
            $header_template->set_block('user_style_block', '');
        }

        $this->oReg->Trans->disableAddon('templates/'.DEFAULT_THEME);
        $header_template->parse('header', 'header_block', false);
        $header_template->pparse('output', 'page');
        $this->oTrans->disableAddon('templates\\'.DEFAULT_THEME);

    } // end of function print_header

    // Print the admin footer -----------------------------------------
        public function print_footer($activateJsAdmin = false) {
          global $iStartTime, $iPhpDeclaredClasses;
          $oReg = $this->oReg;
          $oRequest = $this->oRequest;
          $this->oTrans->enableAddon('templates\\'.DEFAULT_THEME);

          $sScriptFile = $oReg->ThemePath.'js/'.$this->getCurrentSection().'.js';
          $sAddonScriptFile = \strtolower($this->getCurrentSection());
          $bAddonScriptFile = (\is_readable($sScriptFile));

        // include the required file for Javascript admin
          if ($activateJsAdmin != false) {
              if (!\is_callable('jsadminLoaded') && \is_readable(WB_PATH.'/modules/jsadmin/jsadmin_backend_include.php')){
                  include(WB_PATH.'/modules/jsadmin/jsadmin_backend_include.php');
              }
          }
          $bDevInfo = (($this->getUserId()==1) && $oReg->DevInfos);
          $aSettings = ['website_footer' => 'none','jquery_version'=> '1.9.1'];
          $aSettings = ($this->getSettings('website_footer,jquery_version') ?? $aSettings);
        // Setup template object, parse vars to it, then parse it
          $footer_template = new Template(\dirname($this->correct_theme_source('footer.htt')));
          $footer_template->set_file('page', 'footer.htt');
          $footer_template->set_block('page', 'footer_block', 'header');
          $aTwigFooter = [
                        'IS_ADMIN'  => ($this->getUserId()==1),
                        'WEBSITE_FOOTER'      => $aSettings['website_footer'],
                        'JQUERY_VERSION'      => $aSettings['jquery_version'].'/',
                        'BACKEND_MODULE_JS'   => $this->register_backend_modfiles('js'),      // adds backend.js
                        'BACKEND_BODY_MODULE_JS' => $this->register_backend_modfiles_body('js'),
                        'WB_URL'     => $oReg->AppUrl,
                        'ADMIN_URL'  => $oReg->AcpUrl,
                        'THEME_URL'  => $oReg->ThemeUrl,
                        'HELPER_URL' =>  $oReg->AppUrl.'framework/helpers/',
          ];
          $footer_template->set_var($aTwigFooter);
          $footer_template->set_var( $this->oTrans->getLangArray());

//insert addon script in body footer if exists to handle events like onclick, etc
          $footer_template->set_block( 'footer_block', 'show_script_block', 'show_script');
          if ($bAddonScriptFile){
              $footer_template->set_var('SCRIPT', $this->getCurrentSection());
              $footer_template->parse( 'show_script', 'show_script_block', true);
          } else {
              $footer_template->parse( 'show_script', '');
          }

          $footer_template->set_block( 'footer_block', 'show_debug_block', 'show_debug');
          if ($bDevInfo) {
              $footer_template->set_var('MEMORY', \number_format(\memory_get_peak_usage(true), 0, ',', '.').'&nbsp;Byte');
              $footer_template->set_var('UPLOAD_MAX_FILESIZE',\ini_get('upload_max_filesize'));
              $footer_template->set_var('POST_MAX_SIZE',\ini_get('post_max_size'));
              $footer_template->set_var('MAX_EXECUTION_TIME',\ini_get('max_execution_time'));
              $footer_template->set_var('MAX_INPUT_TIME',\ini_get('max_input_time'));
              $footer_template->set_var('SESSION_TIME',\ini_get('session.gc_maxlifetime'));
              $footer_template->set_var('QUERIES', $this->oDb->getQueryCount());
              $included_files = \get_included_files();
              $footer_template->set_var('INCLUDES', sizeof( $included_files));
              $iIncludedClasses = \sizeof(\get_declared_classes());
              $footer_template->set_var('CLASSES', (int)( $iIncludedClasses - $iPhpDeclaredClasses));
              $sum_classes = 0;
              $sum_filesize = 0;

              $footer_template->set_block( 'show_debug_block', 'show_block_list', 'show_list');
              $footer_template->set_block( 'show_block_list', 'include_block_list', 'include_list');
              $bDebug = ($oReg->Debug ?? false);
              foreach ( $included_files as $filename) {
                if( !\is_readable( $filename)) {
                  continue;
                }
                if ($bDebug) {
                  $footer_template->set_var( 'INCLUDES_ARRAY', \str_replace( WB_PATH, '', $filename));
                  $footer_template->set_var( 'FILESIZE', \number_format( \filesize( $filename), 0, ',', '.').
                    '&nbsp;Byte');
                  $footer_template->parse( 'include_list', 'include_block_list', true);
                }
                $sum_filesize += \filesize( $filename);
              }
              $footer_template->parse( 'show_list', 'show_block_list', true);
              $sEndtime = \array_sum(\explode( " ", \microtime()));
              $iEndTime = \microtime(true);
              if (!$bDebug) {
                $footer_template->parse( 'show_list', '');
                $footer_template->parse( 'include_list', '');
              }
              $footer_template->set_var( 'FILESIZE', \ini_get('memory_limit'));
              $footer_template->set_var( 'TXT_SUM_FILESIZE', 'Summary size of included files:&nbsp;');
              $footer_template->set_var( 'SUM_FILESIZE', \number_format($sum_filesize, 0, ',', '.').'&nbsp;Byte');
              $footer_template->set_var( 'SUM_CLASSES', \number_format($sum_classes, 0, ',', '.').'&nbsp;Byte');
              $footer_template->set_var( 'PAGE_LOAD_TIME', \round ($iEndTime - $iStartTime, 3));
              $footer_template->set_var( 'DUMP_CLASSES', '<pre>'.\var_export( $iIncludedClasses, true).'</pre>');
              $footer_template->parse( 'show_debug', 'show_debug_block', true);
            } else {
              $footer_template->parse( 'show_debug', '');
              $footer_template->parse( 'show_list', '');
            }
            $footer_template->parse( 'header', 'footer_block', false);
            $footer_template->pparse( 'output', 'page');
            unset( $footer_template);
            $this->oTrans->disableAddon('templates\\'.DEFAULT_THEME);

        }
//
    public function get_section_details( $section_id, $backLink = 'index.php' ) {
        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'sections` ';
        $sql .= 'WHERE `section_id`='.\intval($section_id);
        if(($resSection = $this->oDb->query($sql))){
            if(!($recSection = $resSection->fetchRow(MYSQLI_ASSOC))) {
                $this->print_header();
                $this->print_error($this->oTrans->TEXT_SECTION.' '.$this->oTrans->TEXT_NOT_FOUND, $backLink, true);
            }
            } else {
                $this->print_header();
                $this->print_error($this->oDb->get_error(), $backLink, true);
            }
        return $recSection;
    }

    public function get_page_details( $page_id, $backLink = 'index.php' ) {
        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'pages` ';
        $sql .= 'WHERE `page_id`='.\intval($page_id);
        if (($resPages = $this->oDb->query($sql))){
            if (!($recPage = $resPages->fetchRow(MYSQLI_ASSOC))) {
            $this->print_header();
            $this->print_error($this->oTrans->TEXT_PAGE.' '.$this->oTrans->TEXT_NOT_FOUND, $backLink, true);
            }
        } else {
            $this->print_header();
            $this->print_error($this->oDb->get_error(), $backLink, true);
        }
        return $recPage;
    }

// TODO DW moved to class wb
/*
    // Return a system permission
    public function get_permission($name, $type = 'system') {
        // Append to permission type
        $type .= '_permissions';
        // Check if we have a section to check for
        if($name == 'start') {
            return true;
        } else {
            // Set system permissions var
            $system_permissions = $this->get_session('SYSTEM_PERMISSIONS');
            // Set module permissions var
            $module_permissions = $this->get_session('MODULE_PERMISSIONS');
            // Set template permissions var
            $template_permissions = $this->get_session('TEMPLATE_PERMISSIONS');
            // Return true if system perm = 1
            if (isset($$type) && \is_array($$type) && \is_numeric(\array_search($name, $$type))) {
                if($type == 'system_permissions') {
                    return true;
                } else {
                    return false;
                }
            } else {
                if($type == 'system_permissions') {
                    return false;
                } else {
                    return true;
                }
            }
        }
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
        if($action != 'viewing') { $action = 'admin'; }
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
        $system_permissions = $this->get_session('SYSTEM_PERMISSIONS');
        // Set module permissions var
        $module_permissions = $this->get_session('MODULE_PERMISSIONS');
        if($title == 'start') {
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
*/

    /**
     * admin::register_backend_modfiles_body()
     *
     * @param string $file_id
     * @return meta <script>
     * backend_body.js and backendUser_body.js
     * @description
     * Function to add optional module Javascript
     * into the <body> section of the backend.
     * User Script loading only if default script load first
     */
    public function register_backend_modfiles_body($file_id="js")
    {
//      ensure that backend_body.js and backendUser.js is only added once per module type
        static $aBackendFiles = null;
        $sCallingScript = $this->oRequest->getServerVar('SCRIPT_NAME');
        $AcpDir = \str_replace('\\','/', ADMIN_PATH).'/';
        if (\preg_match( '/'.'pages\/(settings|sections)\.php$/is', $sCallingScript)) {
          return false;
        }
        // sanity check of parameter passed to the function
        $file_id = \strtolower($file_id);
        if ($file_id !== "js")
        {
            return false;
        }

        $body_links = "";
        $base_link  = "";
        $user_link  = '';
        $sAppUrl    = $this->oReg->AppUrl;
        $sAppPath   = $this->oReg->AppPath;
        $sAbsModulesRoot = 'modules/';
        $sAddonTool   = ($this->oRequest->getParam('tool') ?? null);
        $sAddonPageId = ($this->oRequest->getParam('page_id') ?? 0);
//
        $sBaseJsFile   = "backend_body.js";
        $sUserJsFile   = "backendUser_body.js";
//
        // check if backend.js or backend.css files needs to be included to the <head></head> section of the backend
        if (($sAddonTool)) {
            $sql  = '
            SELECT * FROM `'.TABLE_PREFIX.'addons`
            WHERE `type`=\'module\'
            AND `function` IN (\'tool\',\'wysiwyg\')
            AND `directory`=\''.$this->oDb->escapeString($sAddonTool).'\'
            ';
            if ($oTool = $this->oDb->query($sql)){
                $aTool = $oTool->fetchRow(MYSQLI_ASSOC);
                $sAddonDirectory = (\is_dir($sAppPath.$sAbsModulesRoot) ? $aTool['directory'] : null);
            } else {
              // db error
            }
            if (($sAddonDirectory)){
                $sBaseJsFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sBaseJsFile;
                $sUserJsFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sUserJsFile;
                if (\is_readable($sAppPath.$sBaseJsFileRel)){
                    $base_link     = '<script src="'.$sAppUrl.$sBaseJsFileRel.'"></script>'."\n";
                    if (\is_readable($sAppPath.$sUserJsFileRel)){
                        $user_link = '<script src="'.$sAppUrl.$sUserJsFileRel.'"></script>'."\n";
                    }
                }
            }
            $body_links  = $base_link.$user_link;
// end admintools beginning of page modules
        } elseif (($sAddonPageId)) {
//          check if displayed page in the backend contains a page module
            $page_id = $sAddonPageId;
//          gather information for all models embedded on actual page
            $sql = 'SELECT `module` FROM `'.TABLE_PREFIX.'sections` WHERE `page_id`='.(int)$page_id;
            if ($oPage = $this->oDb->query($sql)) {
                while (($aPage = $oPage->fetchRow(MYSQLI_ASSOC))) {
                    $base_link   = "";
                    $user_link   = '';
                    $sAddonDirectory = (\is_dir($sAppPath.$sAbsModulesRoot) ? $aPage['module'] : null);
//                 check if page module directory contains a backend.js or backend.css file
                    if (($sAddonDirectory)){
                    // create link with backend.js or backend.css source for the current module
                            $sBaseJsFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sBaseJsFile;
                            $sUserJsFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sUserJsFile;
//
                            $bBaseJsExists = (isset($aBackendFiles['base']['js']) ? array_key_exists($sAddonDirectory, $aBackendFiles['base']['js']) : false);
                            $bUserJsExists = (isset($aBackendFiles['user']['js']) ? array_key_exists($sAddonDirectory, $aBackendFiles['user']['js']) : false);
                            if ($bBaseJsExists || $bUserJsExists) {continue;}
//
                            if (\is_readable($sAppPath.$sBaseJsFileRel)){
                                $base_link = '<script src="'.$sAppUrl.$sBaseJsFileRel.'"></script>'."\n";
                                $aBackendFiles['base']['js'][$sAddonDirectory] = $sBaseJsFileRel;
                                if (\is_readable($sAppPath.$sUserJsFileRel)){
                                    $user_link = '<script src="'.$sAppUrl.$sUserJsFileRel.'"></script>'."\n";
                                    $aBackendFiles['user']['js'][$sAddonDirectory] = $sUserJsFileRel;
                                }
                            }
//                        }
                    }// end directory
                    $body_links .= $base_link.$user_link;
                    $sAddonDirectory = '';
                } // end while
            } // end of $oPage db sections
        } // end of page_id
//      write out links with all external module javascript files, remove last line feed
        return \rtrim($body_links);
    } // end of register_backend_modfiles_body

    /**
     * admin::register_backend_modfiles()
     *
     * @param string $file_id
     * @return meta <link> or <script>
     * @description
     * Function to add optional module Javascript or CSS stylesheets
     * into the <head> section of the backend.
     * User style or script loading only if default style or script load first
     */
    public function register_backend_modfiles($file_id="css")
    {
//      ensure that backend.js and backend.css is only added once per module type
        static $aBackendFiles = null;
//
        $sCallingScript = $this->oRequest->getServerVar('SCRIPT_NAME');
        $AcpDir = \str_replace('\\','/', ADMIN_PATH).'/';
        if ((\preg_match( '/'.'pages\/(settings|sections)\.php$/is', $sCallingScript))) {
            return false;
        }
        // sanity check of parameter passed to the function
        $file_id = \strtolower($file_id);
           if ($file_id !== "css" && ($file_id !== "js")) {
              return false;
        }
//         define default baselink and filename for optional module javascript and stylesheet files
        $page_links  = "";
        $head_links  = "";
        $base_link   = "";
        $user_link   = '';
        $sAppUrl     = $this->oReg->AppUrl;
        $sAppPath    = $this->oReg->AppPath;
        $sAbsModulesRoot = 'modules/';
        $sAddonTool   = ($this->oRequest->getParam('tool') ?? null);
        $sAddonPageId = ($this->oRequest->getParam('page_id') ?? 0);
//
        $sBaseCssFile    = 'backend.css';
        $sUserCssFile    = 'backendUser.css';
//
        $sBaseJsFile   = "backend.js";
        $sUserJsFile   = "backendUser.js";
//
        // check if backend.js or backend.css files needs to be included to the <head></head> section of the backend
        if (($sAddonTool)) {
            $sql  = '
            SELECT * FROM `'.TABLE_PREFIX.'addons`
            WHERE `type`=\'module\'
            AND `function` IN (\'tool\',\'wysiwyg\')
            AND `directory`=\''.$this->oDb->escapeString($sAddonTool).'\'';
            if ($oTool = $this->oDb->query($sql)){
                $aTool = $oTool->fetchRow(MYSQLI_ASSOC);

                $sAddonDirectory = (\is_dir($sAppPath.$sAbsModulesRoot) ? $aTool['directory'] : null);
            } else {
              // db error
            }
            if (($sAddonDirectory)){
                if ($file_id == "css") {
                    $sBaseCssFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sBaseCssFile;
                    $sUserCssFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sUserCssFile;
                    if (\is_readable($sAppPath.$sBaseCssFileRel)){
                        $base_link   = '<link rel="stylesheet" href="'.$sAppUrl.$sBaseCssFileRel.'"';
                        $base_link  .= ' media="screen" />'."\n";
                        if (\is_readable($sAppPath.$sUserCssFileRel)){
                            $user_link  = '<link rel="stylesheet" href="'.$sAppUrl.$sUserCssFileRel.'"';
                            $user_link .= ' media="screen" />'."\n";
                        }
                    }
                } elseif ($file_id == "js") {
                    $sBaseJsFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sBaseJsFile;
                    $sUserJsFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sUserJsFile;
                    if (\is_readable($sAppPath.$sBaseJsFileRel)){
                        $base_link     = '<script src="'.$sAppUrl.$sBaseJsFileRel.'"></script>'."\n";
                        if (\is_readable($sAppPath.$sUserJsFileRel)){
                            $user_link = '<script src="'.$sAppUrl.$sUserJsFileRel.'"></script>'."\n";
                        }
                    }
                } // end js
            }
            $head_links  = $base_link.$user_link;
// end admintools beginning of page modules
        } elseif (($sAddonPageId)) {
//          check if displayed page in the backend contains a page module
            $page_id = $sAddonPageId;
//          gather information for all models embedded on actual page
            $sql = '
            SELECT `module` FROM `'.TABLE_PREFIX.'sections`
            WHERE `page_id`='.(int)$page_id.'
            ';
            if ($oPage = $this->oDb->query($sql)) {
                while (($aPage = $oPage->fetchRow(MYSQLI_ASSOC))) {
                    $base_link   = "";
                    $user_link   = '';
                    $sAddonDirectory = (\is_dir($sAppPath.$sAbsModulesRoot) ? $aPage['module'] : null);
//                 check if page module directory contains a backend.js or backend.css file
                    if (($sAddonDirectory)){
                    // create link with backend.js or backend.css source for the current module
                        if ($file_id == "css") {
                            $sBaseCssFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sBaseCssFile;
                            $sUserCssFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sUserCssFile;
//
                            $bBaseCssExists = (isset($aBackendFiles['base']['css']) ? array_key_exists($sAddonDirectory, $aBackendFiles['base']['css']) : false);
                            $bUserCssExists = (isset($aBackendFiles['user']['css']) ? array_key_exists($sAddonDirectory, $aBackendFiles['user']['css']) : false);
                            if ($bBaseCssExists) {continue;}
//
                            if (\is_readable($sAppPath.$sBaseCssFileRel)){
//echo $sBaseCssFileRel.'<br />'."\n";
                                $base_link   = '<link rel="stylesheet" href="'.$sAppUrl.$sBaseCssFileRel.'"';
                                $base_link  .= ' type="text/css" media="screen" />'."\n";
                                $aBackendFiles['base']['css'][$sAddonDirectory] = $sBaseCssFileRel;
                            }
                            if (\is_readable($sAppPath.$sUserCssFileRel)){
//echo $sUserCssFileRel.'<br />'."\n";
                                $user_link   = '<link rel="stylesheet" href="'.$sAppUrl.$sUserCssFileRel.'"';
                                $user_link  .= ' type="text/css" media="screen" />'."\n";
                                $aBackendFiles['user']['css'][$sAddonDirectory] = $sUserCssFileRel;
                            }
                        } elseif ($file_id == "js") {
                            $sBaseJsFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sBaseJsFile;
                            $sUserJsFileRel = $sAbsModulesRoot.''.$sAddonDirectory.'/'.$sUserJsFile;
//
                            $bBaseJsExists = (isset($aBackendFiles['base']['js']) ? array_key_exists($sAddonDirectory, $aBackendFiles['base']['js']) : false);
                            $bUserJsExists = (isset($aBackendFiles['user']['js']) ? array_key_exists($sAddonDirectory, $aBackendFiles['user']['js']) : false);
                            if ($bBaseJsExists || $bUserJsExists) {continue;}
//
                            if (\is_readable($sAppPath.$sBaseJsFileRel)){
                                $base_link = '<script src="'.$sAppUrl.$sBaseJsFileRel.'"></script>'."\n";
                                $aBackendFiles['base']['js'][$sAddonDirectory] = $sBaseJsFileRel;
                            }
                            if (\is_readable($sAppPath.$sUserJsFileRel)){
                                $user_link = '<script src="'.$sAppUrl.$sUserJsFileRel.'"></script>'."\n";
                                $aBackendFiles['user']['js'][$sAddonDirectory] = $sUserJsFileRel;
                            }
                        }
                    }// end directory
                    $head_links .= $base_link.$user_link;
                    $sAddonDirectory = '';
                } // end while
            }// end db sections
        } // end of page_id
//      write out links with all external module javascript/CSS files, remove last line feed
        return \rtrim($head_links);
    }//end of register_backend_modfiles

} // end of class admin
