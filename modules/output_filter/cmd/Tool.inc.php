<?php
/**
 *
 * @category        modules
 * @package         output_filter
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Christian Sommer
 * @author          Dietmar Wöllbrink
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: cmdTool.inc 113 2018-09-28 11:34:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/output_filter/cmd/cmdTool.inc $
 * @lastmodified    $Date: 2018-09-28 13:34:16 +0200 (Fr, 28 Sep 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
//        $sAddonPath  = str_replace(DIRECTORY_SEPARATOR,'/',dirname(__DIR__));
//        $sAddonName  = \basename($sAddonPath);
        $sAddonFile   = \str_replace('\\','/',__FILE__);
        $sFolder      = basename(\dirname($sAddonFile));
        switch ($sFolder):
            case 'cmd':
              $sAddonPath   = (\dirname($sAddonFile,2)).'/';
              break;
            default :
              $sAddonPath   = (\dirname($sAddonFile)).'/';
        endswitch;
        $sModulesPath = \dirname($sAddonPath).'/';
        $sModuleName  = basename($sModulesPath);
        $sAddonName   = basename($sAddonPath);
        $ModuleRel    = ''.$sModuleName.'/';
        $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
        $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
        $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );

        $oReg = WbAdaptor::getInstance();
        $oRequest = $oReg->getRequester();
        $database = $oReg->getDatabase();
        $oTrans   = $oReg->getTranslate();
        $oApp     = $oReg->getApplication();
        $sLangFileTheme = (($oReg->Theme=='DefaultTheme') ? 'default' : $oReg->Theme);

        if (is_readable($sAddonPath.'themes/'.$sLangFileTheme)) {
            $oTrans->enableAddon('modules\\'.$sAddonName.'\\themes\\'.(($oReg->Theme=='DefaultTheme') ? 'default' : $oReg->Theme));
        }elseif (is_readable($sAddonPath.'languages')){
            $oTrans->enableAddon('modules\\'.$sAddonName);
        }

        $debugMessage = '';
        $msgCls  = 'sand';
        $sActionUrl = $oReg->AcpUrl.'admintools/tool.php';
        $ToolQuery  = '?tool='.$sAddonName;
        $ToolRel    = '/admintools/tool.php'.$ToolQuery;
        $js_back    = $sActionUrl;
        $ToolUrl    = $sActionUrl.'?tool='.$sAddonName;
        $sAdminToolRel = ADMIN_DIRECTORY.'/admintools/index.php';
        $sAdminToolUrl = $oReg->AcpUrl.$sAdminToolRel;
        $sCallingScript = $oRequest->getServerVar('SCRIPT_NAME');
        $aRequestVars   = $oRequest->getParamNames();
        $sGetOldSecureToken = (\bin\SecureTokens::checkFTAN());
        $aFtan = \bin\SecureTokens::getFTAN();
        $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];

        $TEXT_CUSTOM = (\in_array('SaveSettings',$aRequestVars) ? $oTrans->TEXT_BACK : $oTrans->TEXT_CLOSE);
        if (!$admin->get_permission($sAddonName,'module' ) ) {
            $admin->print_error($oTrans->MESSAGE_ADMIN_INSUFFICIENT_PRIVELLIGES, $js_back);
        }
        $aAliasFilterNames = ['Jquery' => 'jQuery','JqueryUI' => 'jQueryUI'];
        $SettingsDenied = [
            'at_replacement',
            'dot_replacement',
            'email_filter',
            'mailto_filter',
            'OutputFilterMode',
            'W3Css_force',
            'WbLink',
            'ReplaceSysvar',
            'CssToHead',
            'ShortUrl',
            'Short_force',
            'edit_force',
            'CleanUp',
            'FilterCleanUp',
            'FilterAbstract',
            'content_output_filter'
        ];

        if (\is_readable($sAddonPath.'OutputFilterApi.php')) {
            if (!\function_exists('getOutputFilterSettings')) {
                require($sAddonPath.'OutputFilterApi.php');
            }
        if (!isset($module_description) && \is_readable($sAddonPath.'info.php')) {require $sAddonPath.'info.php';}
        createMissingValues();

// read settings from the database to show
        $aFilterSettings = getOutputFilterSettings();
/*  */
// extended defaultSettings for email filter
        $aEmailDefaults = [
                'at_replacement'  => '@',
                'dot_replacement' => '.',
                'Email' => 0,
                'email_filter'    => 0,
                'mailto_filter'   => 0
            ];

// extended defaultSettings for special filter
        $aExentedDefaults = [
//                'at_replacement'   => '[at]',
//                'dot_replacement'  => '[dot]',
//                'email_filter'     => 0,
//                'mailto_filter'    => 0,
                'OutputFilterMode' => 0,
                'W3Css_force'      => 0,
                'Short_force'      => 0,  // create/delete short.php
                'edit_force'       => 0,  // handle .htaccess
            ];
/*  filter which to have enabled */
            $aAutoFilter = [
                'WbLink' => 1,
                'ReplaceSysvar' => 1,
                'CssToHead' => 1,
                'CleanUp' => 1,
                'SnippetCss' => 1,
                'FrontendCss' => 1,
                ];

      $aDefaultSettings=[];

/*
        $aDefaultSettings = \array_diff_key( $aFilterSettings, $aExtendedSettings );
        $aExtendedSettings = $aExentedDefaults;
        $aExtendedSettings   = \array_intersect_key( $aFilterSettings, $aExentedDefaults );
        $aDefaultSettings = \array_merge( $aFilterSettings, $aExtendedSettings );
*/
        // get existing filter
        $aFiles = \glob($sAddonPath.'Filters/*', \GLOB_NOSORT);
        array_walk(
            $aFiles,
            function (& $sItem, $iKey) use (& $aDefaultSettings) {
                $sItem = \str_replace(['%filter', '%'], '', '%'.\basename($sItem, '.php'));
                $aDefaultSettings[$sItem] = 0;
            }
        );
        if (isset($aDefaultSettings['FilterAbstract'])){unset($aDefaultSettings['FilterAbstract']);}
        //$aFiles = \array_diff_key($aFiles , $aFilterSettings);
        $aDefaultSettings = \array_merge($aDefaultSettings, $aExentedDefaults,$aAutoFilter,$aEmailDefaults );
        \ksort($aDefaultSettings, \SORT_NATURAL | \SORT_FLAG_CASE );

        $aAllowedFilters  = \array_keys ( $aDefaultSettings );
        $aFilterExists    = \array_diff ( $aAllowedFilters, $SettingsDenied );

// Create new Template object
        $oTpl = new Template( $sAddonThemePath );
        $oTpl->setDebug(0);
        $aFiles = [
        'page' => 'tool.htt',
        'page1' => 'htaccess_form.htt',
        ];
        $oTpl->set_file($aFiles);
        $oTpl->loadfile('page');
        $oTpl->set_block('page', 'main_block', 'main');
        $oTpl->set_block('main_block', 'headline_block', 'headline');
        $oTpl->set_var('FTAN_NAME', $aFtan['name']);
        $oTpl->set_var('FTAN_VALUE', $aFtan['value']);
        $oTpl->set_var('CUSTOM',$TEXT_CUSTOM);
        $oTpl->set_var('ADMIN_URL', $oReg->AcpUrl);
        $oTpl->set_var('MODULE_NAME', $sAddonName);
        $msgTxt = '';
        $msgTxt = $module_description;

        $oTpl->set_var('TOOL_NAME', $toolName);
        $oTpl->set_var('REQUEST_URI', $_SERVER['REQUEST_URI']);
        $oTpl->set_var('CANCEL_URL', ADMIN_DIRECTORY.'/admintools/index.php');
        $oTpl->set_var('TOOL_URL', $oReg->AcpUrl.'admintools/tool.php?tool='.$sAddonName);
        $oTpl->set_var('WB_URL', $oReg->AppUrl);
//        $oTpl->set_var($MESSAGE);
//        $oTpl->set_var($MOD_MAIL_FILTER);
        $oTpl->set_var($aoutput_filterLang);
// check if data was submitted
        if ($doSave) {
    // save changes
            $oTpl->parse('headline', 'headline_block', true);
            $oTpl->set_var('TOOL_URL', $oReg->AcpUrl.'admintools/tool.php?tool='.$sAddonName);
            $oTpl->set_var('CANCEL_URL',ADMIN_DIRECTORY.'/admintools/tool.php?tool='.$sAddonName);
            $oTpl->set_var('DISPLAY', 'none');
            include(__DIR__.'/Save.inc.php');
            $aFilterSettings = getOutputFilterSettings();
        } else {
            $oTpl->set_block('headline', '');
            $oTpl->set_var('CANCEL_URL', $sAdminToolRel);
            $oTpl->set_var('DISPLAY', 'block');
        }
        $oTpl->set_block('main_block', 'core_info_block', 'core_info');
        if( $debugMessage != '') {
            $oTpl->set_var('CORE_MSGTXT', $print_r);
            $oTpl->parse('core_info', 'core_info_block', true);
        } else {
            $oTpl->set_block('core_info_block', '');
        }

        $oTpl->set_block('main_block', 'info_message_block', 'info_message');
        $oTpl->set_block('main_block', 'success_message_block', 'success_message');
        if ($doSave) {
            $oTpl->set_block('info_message_block', '');
            if ($msgTxt != '') {
            // write message box if needed
                $oTpl->set_var('MSGTXT', $msgTxt); //$msgCls
                $oTpl->set_var('MSGCOLOR', $msgCls); //$msgCls
                $oTpl->parse('success_message', 'success_message_block', true);
            } else {
                $oTpl->set_block('success_message_block', '');
            }
        } else {
            $oTpl->set_block('success_message_block', '');
            if( $msgTxt != '') {
            // write message box if needed
                $oTpl->set_var('MSGTXT', $msgTxt);
                $oTpl->set_var('MSGCOLOR', $msgCls); //$msgCls
                $oTpl->parse('info_message', 'info_message_block', true);
            } else {
                $oTpl->set_block('info_message_block', '');
            }
       }

        $oTpl->set_block('main_block', 'submit_list_block', 'submit_list');
        $oTpl->set_var('MOD_MAIL_FILTER_WARNING', $oTrans->MOD_MAIL_FILTER_WARNING);
        $oTpl->set_var('TEXT_SAVE_LIST', $oTrans->TEXT_SAVE_LIST);
        $oTpl->set_var('TEXT_EMPTY_LIST', $oTrans->TEXT_EMPTY_LIST);
        $oTpl->set_block('submit_list_block', '');

        $oTpl->set_block('main_block', 'own_list_block', 'own_list');
        $oTpl->set_block('own_list_block', '');
        $aHiddenFilter = [
        'ScriptVars',
        'LoadOnFly',
        'Jquery',
        'SnippetJs',
        'FrontendJs',
        'SnippetBodyJs',
        'FrontendBodyJs',
        'SnippetCss',
        'FrontendCss',
        ];
        $oTpl->set_var($aFilterSettings);
        $oTpl->set_block('main_block', 'filter_block', 'filter_list');
        foreach($aFilterSettings as $sFilterName => $sFilterValue)
        {
            $sFilterAlias = ($aAliasFilterNames[$sFilterName] ?? $sFilterName);
            $sFilterAlias = (isset($aAliasFilterNames[$sFilterName]) ? $aAliasFilterNames[$sFilterName] : $sFilterName);
            $sHelpMsg = (isset($output_filter_help[$sFilterName])
                      ? ($output_filter_help[$sFilterName])
                      : $MOD_MAIL_FILTER['HELP_MISSING']);
            if (\in_array( $sFilterName, $SettingsDenied)) { continue; }
            $oTpl->set_var('TITLE', $sHelpMsg);
            $oTpl->set_var('FVALUE', $sFilterValue);
            $oTpl->set_var('FNAME', $sFilterName);
            $oTpl->set_var('RGMF', (in_array($sFilterName,$aHiddenFilter) ? 'register-mod-files' : ''));
            $oTpl->set_var('FALIAS', $sFilterAlias);
            $oTpl->set_var('FCHECKED', (($sFilterValue=='1') ? ' checked="checked"' : '') );
            $oTpl->parse('filter_list', 'filter_block', true);
        }

// enable/disable extended email filter settings
        $oTpl->set_block('main_block', 'filter_email_block', 'filter_email');
        if (isset($aFilterSettings['Email']) && $aFilterSettings['Email']) {
            $oTpl->set_var('EMAIL_FILTER_CHECK',  (($aFilterSettings['email_filter']) ? ' checked="checked"' : '') );
            $oTpl->set_var('MAILTO_FILTER_CHECK', (($aFilterSettings['mailto_filter']) ? ' checked="checked"' : '') );
        } else {
//            $oTpl->set_block('filter_email_block', '');
            $oTpl->set_var('EMAIL_FILTER_CHECK', '');
            $oTpl->set_var('MAILTO_FILTER_CHECK', '');
        }
        $oTpl->parse('filter_email', 'filter_email_block', true);

        $oTpl->set_block('main_block', 'force_short_block', 'force_short');
        // TODO Zero Trust
        $bShortForce = ($aFilterSettings['Short_force'] ?? 0);
        $bEditForce  = ($aFilterSettings['edit_force'] ?? 0) | $bShortForce;
        $bEditForce = true;  // edit htaccess always true
        // should be changed to admin roles
        $bSpecialAdminRights = ($oApp->getUserId()=='1');
        if ($bSpecialAdminRights)
        {
            $bShortUrlFile = is_readable($sAppPath.'short.php');
            $sChecked      = (($bShortUrlFile) ? ' checked="checked"' : '');
            if ($bShortForce)
            {
                $oTpl->set_var('LOAD_SHORT_URL', $oTrans->MOD_MAIL_FILTER_DELETE_SHORT_URL);
                $oTpl->set_var('Short_force_FILTER_CHECK',  ' checked="checked"' );
            } else {
                $oTpl->set_var('LOAD_SHORT_URL', $oTrans->MOD_MAIL_FILTER_LOAD_SHORT_URL);
                $oTpl->set_var('Short_force_FILTER_CHECK', $sChecked );
            }
            $oTpl->parse('force_short', 'force_short_block', true);
        }
        else
        {
            $oTpl->set_block('force_short_block', '');
        }

        $oTpl->set_block('main_block', 'edit_short_block', 'edit_short');
        // TODO Zero Trust
        if ($bSpecialAdminRights)
        {
            $oTpl->loadfile('page1');
            //$oTpl->set_block('main', 'show_htaccess_block', 'show_htaccess');
            //require($oReg->AppPath . 'include/editarea/wb_wrapper_edit_area.php');
            if ($bEditForce)
            {
                // include htaccess modal template
                $sHtaccesTemplate = 'themes/default/htaccess_form.htt';
                $sTemplate = file_get_contents($sAddonPath.$sHtaccesTemplate);
                $oTpl->set_var('INCL_HTACCESS',($sTemplate));
                $sHtaccesFile = '.htaccess';
                $oTpl->set_var('Edit_force_FILTER_CHECK', ' checked="checked"' );
                if (is_writable($oReg->AppPath.$sHtaccesFile)){
                    $oTpl->set_var('W3_HIDE','w3-show');
                    $oTpl->set_var('EDIT_HTACCESS_FILE', $oTrans->MOD_MAIL_FILTER_LOAD_EDIT_URL);
                    $content = file_get_contents($oReg->AppPath.$sHtaccesFile);
                } else {
                    $oTpl->set_var('W3_HIDE','w3-hide');
                    $oTpl->set_var('EDIT_HTACCESS_FILE', $oTrans->MOD_MAIL_FILTER_ADD_EDIT_URL);
                    $content = '';
                }
                //
                //$oTpl->set_block('edit_short_block', 'htaccess_list_block', 'htaccess_list');
                $aHtaccessBlocks = glob($sAppPath.'install/htaccess/*', \GLOB_NOSORT);
                $aDefaultHtaccess = [];
                array_walk(
                    $aHtaccessBlocks,
                    function (& $sItem, $iKey) use (& $aDefaultHtaccess)
                    {
                        $sKey = \str_replace(['%redirect', '%'], '', '%'.\basename($sItem, '.inc'));
                        $sItem = \str_replace(['%redirect', '%'], 'Redirect ', '%'.\basename($sItem, '.inc'));
                        $aDefaultHtaccess[$sKey] = $sItem;
                    }
                );
//bad coding -> create option_block until template engine changed to twig
                $oTpl->set_var($aDefaultHtaccess);
                $sOptions = '';
                foreach ($aDefaultHtaccess as $sKey => $sValue) {
                    $sOptions .= (sprintf('<option value="%s">%s</option>'."\n",$sKey,$sValue));
                }
                $oTpl->set_var('OPTION',$sOptions);
                if (is_readable($oReg->AppPath.'short.php')){
                    $content = (!empty(($content)) ? ($content) : $oTrans->MOD_MAIL_FILTER_HTACCESS_CODE);
                }
                $aSerchesChars = ['{','}','pages','admin','media','modules'];
                $aReplaceChars = ['&lbrace;','&rbrace;',trim($oReg->PagesDir,'/'),trim($oReg->AcpDir,'/'),trim($oReg->MediaDir,'/'),trim($oReg->ModuleDir,'/')];
                //$aReplaceChars = ['&lbrace;','&rbrace;','Seiten',trim($oReg->AcpDir,'/'),trim($oReg->MediaDir,'/'),trim($oReg->ModuleDir,'/')];
                $sContent = str_replace($aSerchesChars,$aReplaceChars,$content);

/*
                $aInitEditArea = [
                  'id' => 'content_output_filter'
                  ,'syntax' => 'php'//
                  ,'syntax_selection_allow' => false
                  ,'allow_resize' => true
                  ,'allow_toggle' => true
                  ,'start_highlight' => true
                  ,'toolbar' => 'search, go_to_line, fullscreen, |, undo, redo, |, select_font, |, change_smooth_selection, highlight, reset_highlight, word_wrap, |, help'
                  ,'font_size' => '14'
                ];
*/
                $aEditAreaData = [
                    //'REGISTER_EDIT_AREA'    => (\function_exists('registerEditArea') ? registerEditArea($aInitEditArea) : ''),//'content'.$section_id, 'php', false
                    'HTACCES_FILE'          => $sHtaccesFile,
                    'ADDON_NAME'            => $sAddonName,
                    'PAGE_ID'               => ($page_id ?? -1),
                    'SECTION_ID'            => ($section_id ?? -1),
                    'WB_URL'                => $oReg->AppUrl,
                    'CONTENT'               => $sContent,
                    'TEXT_SAVE'             => (is_readable($oReg->AppPath.'.htaccess') ? $TEXT['SAVE'] : $TEXT['ADD']),
                    'TEXT_BACK'             => $TEXT['BACK'],
                    'TEXT_CANCEL'           => $TEXT['CANCEL'],
                    'SECTION'               => ($section_id ?? -1),
                    'FTAN'                  => $admin->getFTAN()
                ];
                $oTpl->set_var($aEditAreaData);
                $oTpl->parse('edit_short', 'edit_short_block', true);
            } else {
                //$oTpl->set_var('INCL_HTACCESS',($sTemplate));
                //$oTpl->set_var('LOAD_EDIT_URL', $oTrans->MOD_MAIL_FILTER_LOAD_EDIT_URL);
                $oTpl->set_var('Edit_force_FILTER_CHECK', '' );
                $oTpl->set_block('edit_short_block', '');
            }
        }
        else
        {
            $oTpl->set_block('edit_short_block', '');
        }

        $oTpl->set_block('main_block', 'force_w3css_block', 'force_w3css');
        if (isset($aFilterSettings['W3Css']) && $aFilterSettings['W3Css'])
        {
            $oTpl->set_var('W3Css_force_FILTER_CHECK',  (($aFilterSettings['W3Css_force']) ? ' checked="checked"' : '') );
        } else {
            //$oTpl->set_block('force_w3css_block', '');
            $oTpl->set_var('W3Css_force_FILTER_CHECK',  (($aExentedDefaults['W3Css_force']) ? ' checked="checked"' : '') );
        }
        $oTpl->parse('force_w3css', 'force_w3css_block', true);

        $oTpl->set_var($oTrans->getLangArray());

        // write out header if needed
        if(!$admin_header) { $admin->print_header(); }
    // Parse template objects output
            $oTpl->parse('main', 'main_block', true);
            $oTpl->pparse('output', 'page');
    }
