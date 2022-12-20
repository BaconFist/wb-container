<?php

declare(strict_types=1);

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use bin\requester\HttpRequester;

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
 * @version         $Id: browse.php 209 2019-01-29 22:09:06Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/media/browse.php $
 * @lastmodified    $Date: 2019-01-29 23:09:06 +0100 (Di, 29. Jan 2019) $
 *
 */

// Create admin object
if (!\defined('SYSTEM_RUN')){require(\dirname(\dirname((__DIR__))).'/config.php' ); }

    $admin = new \admin('Media', 'media', false);

    $starttime = \explode(" ", microtime());
    $starttime = $starttime[0]+$starttime[1];
    $sAllowedFileTypes  = 'bmp|gif|jpg|ico|jpeg|png';

    PreCheck::increaseMemory();

// Include the WB functions file
//    if (!\function_exists('check_media_path')){require(WB_PATH.'/framework/functions.php'); }
    if (!\function_exists('mediaScanDir')){require('MediaScanDir.inc');}
    if (!\function_exists('__unserialize')){include(__DIR__.'/parameters.php');}

// check if theme language file exists for the language set by the user (e.g. DE, EN)
    if (\is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (\is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

// Byte convert for filesize
    function byte_convert($bytes) {
        $symbol = array(' bytes', ' KB', ' MB', ' GB', ' TB');
        $exp = 0;
        $converted_value = 0;
        if( $bytes > 0 ) {
            $exp = \floor(\log($bytes)/\log(1024) );
            $converted_value = ( $bytes/\pow(1024,\floor($exp)) );
        }
        return \sprintf( '%.2f '.$symbol[$exp], $converted_value );
    }

// Get file extension
    function get_filetype($fname) {
        $pathinfo = \pathinfo($fname);
        $extension = (isset($pathinfo['extension'])) ? \strtolower($pathinfo['extension']) : '';
        return $extension;
    }

// Get file extension for icons
    function get_filetype_icon($fname) {
        $pathinfo = \pathinfo($fname);
        $extension = (isset($pathinfo['extension'])) ? \strtolower($pathinfo['extension']) : '';
        if (\file_exists(THEME_PATH.'/images/files/'.$extension.'.png')) {
            return $extension;
        } else {
            return 'blank_16';
        }
    }

    function ToolTip($name='', $detail = '')
    {
    //    parse_str($name, $array);
    //    $name = $array['img'];
        $retVal = '';
        $parts  = \explode(".", $name);
        $ext    = \strtolower(\end($parts));
        if (\strpos('.gif.jpg.jpeg.png.bmp.', $ext))
        {
            $retVal = 'onmouseover="return overlib('.
                '\'<img src=\\\''.($name).'\\\''.
                'alt=\\\'\\\' '.
                'maxwidth=\\\'300\\\' '.
                'maxheight=\\\'300\\\' />\','.
    //            '>\','.
    //            'CAPTION,\''.basename($name).'\','.
                'FGCOLOR,\'#ffffff\','.
                'BGCOLOR,\'#557c9e\','.
                'BORDER,1,'.
                'FGCOLOR, \'#ffffff\','.
                'BGCOLOR,\'#557c9e\','.
                'CAPTIONSIZE,\'12px\','.
                'CLOSETEXT,\'X\','.
                'CLOSECOLOR,\'#ffffff\','.
                'CLOSESIZE,\'14px\','.
                'VAUTO,'.
                'HAUTO,'.
                ''.
    //            'STICKY,'.
                'MOUSEOFF,'.
                'WRAP,'.
                'CELLPAD,5'.
                ''.
                ''.
                ''.
                ')" onmouseout="return nd()"';
        }
            return $retVal;
    }

    function fsize(int $iSize) {
        $sRetval = '0 Bytes';
        if ($iSize) {
            $aFilesizename = [" bytes", " kB", " MB", " GB", " TB"];
            $sRetval = \round($iSize/pow(1024, ($i = \floor(\log($iSize, 1024)))), 1) . $aFilesizename[$i];
        }
        return $sRetval;
    }

// Setup template object, parse vars to it, then parse it
// Create new template object
    $template = new Template(\dirname($admin->correct_theme_source('media_browse.htt')));
    $template->set_file('page', 'media_browse.htt');
    $template->set_block('page', 'main_block', 'main');
// Get the current dir
//$currentHome = $admin->get_home_folder();
    $currentHome = (\defined('HOME_FOLDERS') && HOME_FOLDERS) ? $admin->get_home_folder() : '';
    $currentHome = '';

// set directory if you call from menu
    $directory   =  ((empty($currentHome) && ($oReg->Request->issetParam('dir')))
                    ? \ltrim($oReg->Request->getParam('dir'),'.')
                    : $currentHome );
//    \trigger_error(sprintf('[%d] Sanitize directory %s',__LINE__, \ltrim($oReg->Request->getParam('dir'),'.')), E_USER_NOTICE);

// check for correct directory
    if ($currentHome && \stripos($directory, $currentHome)===false) {
        $directory = $currentHome;
    }
//    if ($directory == '/' || $directory == '\\') {$directory = '*';}
    if (in_array($directory,['/',DIRECTORY_SEPARATOR,MEDIA_DIRECTORY])){
      $directory = '';
    }

    $sBackLink = $directory;
//    \trigger_error(sprintf('[%d] Sanitize sBackLink %s',__LINE__, $sBackLink), E_USER_NOTICE);

    if (!\is_readable( $sBackLink )) {
//        $directory = \dirname($directory);
// reload parent page to rebuild the dropdowns
/*
    echo '
      <script>
      // Set the value of the location object
        parent.document.location.href="index.php";
      </script>
    ';
*/
    }

    $dir_backlink = 'browse.php?dir='.dirname($directory);
    $sBacklinkUrl = ADMIN_URL.'/media/index.php?dir='.dirname($directory);

//    \trigger_error(sprintf('[%d] Sanitize parent_directory %s',__LINE__, $directory), E_USER_NOTICE);
// Check to see if it contains ../
    if (!(check_media_path($directory))) {
        $admin->print_header('',false);
        $admin->print_error('['.__LINE__.'] '.$MESSAGE['MEDIA_DIR_DOT_DOT_SLASH'],$dir_backlink); //$sBacklinkUrl
    }
    $sPathname = (str_replace(MEDIA_DIRECTORY,'',$directory));
    if (!\is_readable(WB_PATH.MEDIA_DIRECTORY.$sPathname)) {
        $admin->print_header('',false);
//    \trigger_error(sprintf('[%d] Sanitize directory %s',__LINE__, MEDIA_DIRECTORY.$directory), E_USER_NOTICE);
        $admin->print_error('['.__LINE__.'] '.$MESSAGE['MEDIA_DIR_DOES_NOT_EXIST'],$dir_backlink);
    }

// Workout the parent dir link
    $parent_dir_link = ADMIN_URL.'/media/browse.php?dir='.$directory.'&amp;up=1';

// Check to see if the user wanted to go up a directory into the parent folder
    if ((int)($admin->get_get('up') == 1)) {
        $parent_directory = \ltrim(\dirname($directory),DIRECTORY_SEPARATOR.'/');
        $parent_directory = (empty($parent_directory) ? '/' : '/'.$parent_directory);
//    \trigger_error(sprintf('[%d] Sanitize parent_directory %s',__LINE__, $parent_directory), E_USER_NOTICE);
        \header('Location: browse.php?dir='.$parent_directory);
        exit(0);
    }

    if ($_SESSION['GROUP_ID'] != 1 && (isset($pathsettings['global']['admin_only']) && $pathsettings['global']['admin_only']) ) { // Only show admin the settings link
        $template->set_var('DISPLAY_SETTINGS', 'hide');
    }

// Workout if the up arrow should be shown
    if(empty($directory) || ($directory==$currentHome)) {
        $display_up_arrow = 'hide';
    } else {
        $display_up_arrow = '';
    }

// Insert values
    $template->set_var(array(
                    'THEME_URL' => THEME_URL,
                    'CURRENT_DIR' => $directory,
                    'PARENT_DIR' => (empty($directory) ? MEDIA_DIRECTORY : $directory),
                    'PARENT_DIR_LINK' => $parent_dir_link,
                    'DISPLAY_UP_ARROW' => $display_up_arrow,
                    'INCLUDE_PATH' => WB_URL.'/include'
                )
            );

// Get home folder not to show
//$home_folders = get_home_folders();
    $home_folders = (\defined('HOME_FOLDERS') && HOME_FOLDERS) ? get_home_folders() : [];

// Generate list
    $template->set_block('main_block', 'list_block', 'list');

    $usedFiles = [];
    // require_once(ADMIN_PATH.'/media/dse.php');
    // $filename =  $currentdir;
    if(!empty($currentdir)) {
        $usedFiles = $Dse->getMatchesFromDir( $currentdir, DseTwo::RETURN_USED);
    }
    // scan given dir
    $aListDir = mediaScanDir($directory);
    // Now parse these values to the template
//    $temp_id = 0;
    $aTwigData = [];
    $row_bg_color = 'FFF';
    if (isset($aListDir)) {


        foreach($aListDir as $temp_id => $name)
        {
            $sMediaFileRel = MEDIA_DIRECTORY.$directory.'/'.$name;
            $sFileName = WB_PATH.$sMediaFileRel;
            $sShortName  =  \mb_strlen($name) > 50 ? \mb_substr($name, 0, 48).'…' : $name;
            $sShortName  = \preg_replace('/^(.{5,50})\s(.*)$/su', '\1…', \str_replace('"', '', $sShortName));
            $bytes = '';
            $date = '';
            $preview = '';
            $filetype = '';
            $filetypeicon = 'blank_16';
            $temp_id_key = \bin\SecureTokens::getIDKEY($temp_id);
            if (\is_dir($sFileName)){
                $oFile = \stat(WB_PATH.$sMediaFileRel);
                $date = \gmdate('Y/m/d &#160; H:i', $oFile['mtime']+TIMEZONE);
//                $link_name = str_replace(' ', '%20', $name);
                $link_name = "browse.php?dir=$directory/".\rawurlencode($name);
//                \trigger_error(\sprintf('%s',$dir_name),E_USER_NOTICE);
//                $temp_id++;
                $aTwigData['DirList'] = [
                                'NAME' => $sShortName,
                                'NAME_SLASHED' => \addslashes($name),
                                'MEDIA_CONFIRM_DELETE' => (\sprintf($MESSAGE['MEDIA_CONFIRM_DELETE_DIR'],$name)),
                                'SHORT_NAME' => \addslashes($name),
                                'TEMP_ID' => $temp_id_key,
                                // 'TEMP_ID' => $temp_id,
                                'LINK' => $link_name,
                                'LINK_TARGET' => '_self',
                                'ROW_BG_COLOR' => $row_bg_color,
                                'FT_ICON' => THEME_URL.'/images/folder_16.png',
                                'FILETYPE_ICON' => THEME_URL.'/images/folder_16.png',
                                'MOUSEOVER' => '',
                                'IMAGEDETAIL' => '',
                                'SIZE' => '',
                                'DATE' => $date,
                                'PREVIEW' => '',
                                'IMAGE_TITLE' => $name,
                                'IMAGE_EXIST' => 'blank_16.gif'
                            ];
                $template->set_var($aTwigData['DirList']);
                $template->parse('list', 'list_block', true);
                // Code to alternate row colors
                $row_bg_color = (($row_bg_color == 'FFF') ?'ECF1F3':'FFF');
            } else {
                $preview = '';
                $filetype = '';
                $filetypeicon = 'blank_16';
                $oFile = \stat(WB_PATH.$sMediaFileRel);
                $size = $oFile['size'];
                $bytes = byte_convert($size);
                $date = \gmdate('Y/m/d &#160; H:i', $oFile['mtime']+TIMEZONE);
                $filetype = get_filetype(WB_URL.$sMediaFileRel);
                $filetypeicon = get_filetype_icon(WB_URL.$sMediaFileRel);
                $preview = 'preview';
//                $preview =  (in_array($filetype, $filepreview) ? 'preview' : '');
//                $temp_id++;
                $imgdetail = '';
//                $icon = THEME_URL.'/images/blank_16.gif';
                $icon = '';
                $tooltip = '';
                $sDimensions = '';
                $filetype_url = THEME_URL.'/images/files/'.$filetypeicon.'.png';

                if (isset($pathsettings['global']) && $pathsettings['global']['show_thumbs']) {
                } else {
//                $filetype = get_filetype(WB_URL.$sMediaFileRel);
                    $bValidFile = (($name != '') && \preg_match('/' . $sAllowedFileTypes . '$/i', $filetype));
                    if (\is_file(WB_PATH.$sMediaFileRel) && $bValidFile){
                        $info = \getimagesize(WB_PATH.$sMediaFileRel);
                        if ($info[0]) {
                            $imgdetail = (\filesize(WB_PATH.$sMediaFileRel));
                            $sDimensions = $info[0].' x '.$info[1].' px';
                            $icon = 'thumb.php?t=1&amp;img='.$directory.'/'.$name;
                            $tooltip = ToolTip('thumb.php?t=2&amp;img='.$directory.'/'.$name);
                            $bytes = byte_convert($imgdetail);
                            $fdate = \filemtime(WB_PATH.$sMediaFileRel);
                            $date = \gmdate('Y/m/d &#160; H:i', $fdate+TIMEZONE);
                        }
                    }
                }

                $template->set_var(array(
                            'NAME' => $sShortName,
                            'MEDIA_CONFIRM_DELETE' => (\sprintf($MESSAGE['MEDIA_CONFIRM_DELETE_FILE'],$name)),
                            'NAME_SLASHED' => \addslashes($name),
                            'SHORT_NAME' => \addslashes($name),
                            'TEMP_ID' => $temp_id_key,
                            // 'TEMP_ID' => $temp_id,
                            'LINK' => WB_URL.MEDIA_DIRECTORY.$directory.'/'.$name,
                            'LINK_TARGET' => '_blank',
                            'ROW_BG_COLOR' => $row_bg_color,
                            'FT_ICON' => empty($tooltip) ? $filetype_url : $icon,
                            'FILETYPE_ICON' => $filetype_url,
                            'MOUSEOVER' => $tooltip,
                            'IMAGEDETAIL' => $sDimensions,
                            'IMAGESIZE' => $imgdetail,
                            'SIZE' => $bytes,
                            'DATE' => $date,
                            'PREVIEW' => $preview,
                            'IMAGE_TITLE' => $name,
                            'IMAGE_EXIST' =>  'blank_16.gif'
                        )
                    );

                $template->parse('list', 'list_block', true);
            // Code to alternate row colors
                $row_bg_color = (($row_bg_color == 'FFF') ?'ECF1F3':'FFF');
            }

        } #foreach
    }
// If no files are in the media folder say so
    $template->set_block('main_block', 'none_found_block', 'none_found');
    if (\sizeof($aListDir) > 0) {
        $template->set_var('DISPLAY_NONE_FOUND', 'hide');
        $template->set_block('none_found_block', '');
    } else {
        $template->set_var('DISPLAY_NONE_FOUND', '');
        $template->parse('none_found', 'none_found_block', true);
    }
//if($currentHome=='') {
    if( !\in_array($admin->get_username(), \explode('/',$directory)) ) {
    // Insert permissions values
        if($admin->get_permission('media_rename') != true) {
            $template->set_var('DISPLAY_RENAME', 'hide');
        }
        if($admin->get_permission('media_delete') != true) {
            $template->set_var('DISPLAY_DELETE', 'hide');
        }
    }

// Insert language text and messages
    $template->set_var(array(
                    'MEDIA_DIRECTORY' => MEDIA_DIRECTORY,
                    'TEXT_CURRENT_FOLDER' => $TEXT['CURRENT_FOLDER'],
                    'TEXT_RELOAD' => $TEXT['RELOAD'],
                    'TEXT_RENAME' => $TEXT['RENAME'],
                    'TEXT_DELETE' => $TEXT['DELETE'],
                    'TEXT_SIZE' => $TEXT['SIZE'],
                    'TEXT_DATE' => $TEXT['DATE'],
                    'TEXT_NAME' => $TEXT['NAME'],
                    'TEXT_TYPE' => $TEXT['TYPE'],
                    'TEXT_UP' => $TEXT['UP'],
                    'NONE_FOUND' => $MESSAGE['MEDIA_NONE_FOUND'],
                    'CHANGE_SETTINGS' => $TEXT['MODIFY_SETTINGS'],
                )
            );

// Parse template object
    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');
