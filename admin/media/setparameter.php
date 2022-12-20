<?php
/**
 *
 * @category        admin
 * @package         media
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: setparameter.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/media/setparameter.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 */

use vendor\phplib\Template;

if (!defined( 'SYSTEM_RUN' )){ require( dirname(dirname((__DIR__))).'/config.php' ); }
    $admin = new \admin('Media', 'media', false);
// Include the WB functions file
//    if (!\function_exists('check_media_path')){require(WB_PATH.'/framework/functions.php');}

// check if theme language file exists for the language set by the user (e.g. DE, EN)
    if (\is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (\is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

    if(\file_exists(THEME_PATH .'/languages/EN.php')) {require(THEME_PATH .'/languages/EN.php');}
    if(\file_exists(THEME_PATH .'/languages/'.LANGUAGE .'.php')) {require(THEME_PATH .'/languages/'.LANGUAGE .'.php');}

    $iMaxInputVars = \ini_get('max_input_vars');

    $aInputs = $oRequest->getParamNames();
    $ErrMsg  = '';
    $dirs    = directory_list(WB_PATH.MEDIA_DIRECTORY);
    \array_unshift($dirs, WB_PATH.MEDIA_DIRECTORY);
    $array_lowercase = array_map('strtolower', $dirs);
//    \array_multisort($array_lowercase, $dirs);
    $dirs = \array_unique ($dirs);
//Save post vars to the parameters file
    if (\in_array('save',$aInputs))
    {
        if (!$admin->checkFTAN())
        {
            $admin->print_error('::'.$MESSAGE['GENERIC_SECURITY_ACCESS'],'browse.php',false);
        }

        if (DEFAULT_THEME != 'DefaulTheme') {
            $cfg = array(
                'media_width'         => (\defined('MEDIA_WIDTH') ? MEDIA_WIDTH : '0'),
                'media_height'        => (\defined('MEDIA_HEIGHT') ? MEDIA_HEIGHT : '0'),
                'media_compress'      => (\defined('MEDIA_COMPRESS') ? MEDIA_COMPRESS : '75'),
                'mediasettings'       => (\defined('MEDIASETTINGS') ? MEDIASETTINGS : ''),
            );
            foreach($cfg as $key=>$value) {
                db_update_key_value('settings', $key, $value);
            }
        } else {
            $pathsettings = [];
        }
        foreach($dirs as $name) {
            $sTmp = \ltrim(\str_replace('\\','/',str_replace(WB_PATH, '', $name)),'/');
//            $sTmp = str_replace(MEDIA_DIRECTORY,'',$sTmp);
            $r    = \str_replace(['/',' '],'_',$sTmp);
//    \trigger_error(\sprintf('[%d] Sanitize directory %s',__LINE__, $r), E_USER_NOTICE);
            $w    = (int)$admin->get_post($r.'-w');
            $h    = (int)$admin->get_post($r.'-h');
            $pathsettings[$r]['width']=$w;
            $pathsettings[$r]['height']=$h;
        }  // end foreach
        $pathsettings['global']['admin_only']  = ($admin->get_post('admin_only')!='' ? 'checked="checked"':'');
        $pathsettings['global']['show_thumbs'] = ($admin->get_post('show_thumbs')!=''?'checked="checked"':'');
        $pathsettings['global']['resize_up']   = ($admin->get_post('resize_up')!=''?'checked="checked"':'');
        $fieldSerialized = \serialize($pathsettings);

        $sSqlSet  = 'UPDATE `'.TABLE_PREFIX.'settings` SET '. PHP_EOL
                  . '`value` = \''.($fieldSerialized). '\' '. PHP_EOL
                  . 'WHERE `name`=\'mediasettings\'';
        if ($database->query ($sSqlSet)){
            \header ("Location: browse.php");
            exit();
        } else {
          \trigger_error(sprintf('[%03d] %s',__LINE__,$database->get_error()));
        }
    } // save end

    $iInputs = \sizeof($dirs)*2;
    if (!\function_exists('__unserialize')){require(__DIR__.'/parameters.php');}
    $sSettings      = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'mediasettings\' ');
    $width          = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'media_width\' ');
    $height         = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'media_height\' ');
    $jpegQuality    = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'media_compress\' ');
    $aUploadOptions = [
        'resizeUp'              => $oReg->Request->getParam('resize_up',FILTER_VALIDATE_BOOLEAN) ?? false,
        'jpegQuality'           => $jpegQuality,
        'correctPermissions'    => true,
        'preserveAlpha'         => true,
        'alphaMaskColor'        => [255, 255, 255],
        'preserveTransparency'  => true,
        'transparencyMaskColor' => [0, 0, 0]
    ];

    $pathsettings = __unserialize($sSettings);

    if ($_SESSION['GROUP_ID'] != 1 && (isset($pathsettings['global']) && $pathsettings['global']['admin_only'])) {
        echo "Sorry, settings not available";
        exit();
    }
// Read data to display
    $caller = "setparameter";

// Setup template object, parse vars to it, then parse it
// Create new template object
    $template = new Template(\dirname($admin->correct_theme_source('setparameter.htt')));
    $template->set_file('page', 'setparameter.htt');
    $template->set_block('page', 'main_block', 'main');
    $template->set_block('main_block', 'table_tfoot_block', 'table_tfoot');
    $template->set_block('main_block', 'table_message_block', 'table_message');
    $template->set_block('main_block', 'table_save_block', 'table_save');

    if ($iInputs > $iMaxInputVars){
        $ErrMsg = \sprintf('PHP configuration "max_input_vars" (see php.ini) must be increased from %d to %d (or greater). Saving the optionen would fail and was disabled',$iMaxInputVars, $iInputs);
//      \trigger_error($ErrMsg, E_USER_WARNING);
        $template->parse('table_message', 'table_message_block', true);
        $template->set_block('table_save_block', '');
        $template->set_block('table_tfoot_block', '');
    } else {
        $template->set_block('table_message_block', '');
        $template->parse('table_save', 'table_save_block', true);
        $template->parse('table_tfoot', 'table_tfoot_block', true);
    }
    if ($_SESSION['GROUP_ID'] != 1) {
        $template->set_var('DISPLAY_ADMIN', 'hide');
    }
    $show_thumbs = ($pathsettings['global']['show_thumbs'] ?? false);
    $admin_only  = ($pathsettings['global']['admin_only'] ?? false);
    $resize_up   = ($pathsettings['global']['resize_up'] ?? false);

    $template->set_var(array(
                        'TEXT_HEADER' => $TEXT['TEXT_HEADER'],
                        'MESSAGE' => $ErrMsg,
                        'SAVE_TEXT' => $TEXT['SAVE'],
                        'BACK' => $TEXT['BACK'],
                        'FTAN' => $admin->getFTAN(),
                        'NO_SHOW_THUMBS' => $TEXT['NO_SHOW_THUMBS'],
                        'NO_SHOW_THUMBS_CHECKED' => $show_thumbs,
                        'ADMIN_ONLY' => $TEXT['ADMIN_ONLY'],
                        'ADMIN_ONLY_CHECKED' => $admin_only,
                        'RESIZE_UP' => $TEXT['RESIZE_UP'],
                        'RESIZE_UP_CHECKED' => $resize_up,
                )
            );

    $row_bg_color = '';
    $id=0;
    $template->set_block('main_block', 'list_block', 'list');
    foreach($dirs as $name) {
        $relative = ltrim(str_replace('\\','/',str_replace(WB_PATH, '', $name)),'/');
//        $relative = str_replace(MEDIA_DIRECTORY,'',$relative);
        $safepath = str_replace(['/',' '],'_',$relative);
        $cur_width = $cur_height = '';
        if (isset($pathsettings[$safepath]['width'])) $cur_width = $pathsettings[$safepath]['width'];
        if (isset($pathsettings[$safepath]['height'])) $cur_height = $pathsettings[$safepath]['height'];
        $cur_width = ($cur_width ? (int)$cur_width : '-');
        $cur_height = ($cur_height ? (int)$cur_height : '-');
        $id++;

        $row_bg_color = (($row_bg_color == 'DEDEDE') ? 'EEEEEE' : 'DEDEDE');
        $template->set_var(array(
                        'ADMIN_URL' => ADMIN_URL,
                        'PATH_NAME' => str_replace(WB_PATH,'',$relative),
                        'WIDTH' => $TEXT['WIDTH'],
                        'HEIGHT' => $TEXT['HEIGHT'],
                        'FIELD_NAME_W' => $safepath.'-w',
                        'FIELD_NAME_H' => $safepath.'-h',
                        'CUR_WIDTH' => $cur_width,
                        'CUR_HEIGHT' => $cur_height,
                        'SETTINGS' => $TEXT['SETTINGS'],
                        'ROW_BG_COLOR' => $row_bg_color,
                        'THEME_URL' => THEME_URL,
                        'FILE_ID' => $id,
                    )
            );
        $template->parse('list', 'list_block', true);
    }

    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');
