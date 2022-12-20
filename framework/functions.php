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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of framework/functions.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: functions.php 364 2019-05-31 16:27:17Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */

declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use bin\requester\HttpRequester;
use bin\media\inc\PhpThumbFactory;

use bin\media\GD;

// prevent from loading twice by addons
$sRequestFromInitialize = ($sRequestFromInitialize ?? false);
if (!\defined('SYSTEM_RUN') && !$sRequestFromInitialize) {
    \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
    echo '404 Not Found';
    \flush(); exit;
}
if (!\defined('FUNCTIONS_FILE_LOADED'))
{
//  Define that this file has been loaded
    \define('FUNCTIONS_FILE_LOADED', true);
/**
 * deletes the given directory and all it's subdirectories (like DelTree)
 * @param string $sBasedir  full path of the folder to delete
 * @param bool $bPreserveBaseFolder shall the basedir be deleted (default: false)
 * @return bool
 */
    function rm_full_dir($sBasedir, $bPreserveBaseFolder = false)
    {
        $bRetval = true;
        $sPath = \rtrim($sBasedir, '\\\\/').'/';
        if (\is_readable($sPath)) {
            $oHandle = \opendir($sPath);
            while (false !== ($sFile = \readdir($oHandle))) {
                if (($sFile != '.') && ($sFile != '..')) {
                    $sFileName = $sPath . '/' . $sFile;
                    if (\is_dir($sFileName)) {
                        $bRetval = rm_full_dir($sFileName, false);
                    } else {
                        $bRetval = \unlink($sFileName);
                    }
                    if (!$bRetval) { break; }
                }
            }  // end while
            \closedir($oHandle);
            if (!$bPreserveBaseFolder && $bRetval) { $bRetval = \rmdir($sPath); }
        }
        return $bRetval;
    }

/*
 * returns a recursive list of all subdirectories from a given directory
 * @access  public
 * @param   string  $directory: from this dir the recursion will start
 * @param   bool    $show_hidden:  if set to TRUE also hidden dirs (.dir) will be shown
 * @return  array
 * example:
 *  /srv/www/httpdocs/wb/media/a/b/c/
 *  /srv/www/httpdocs/wb/media/a/b/d/
 * directory_list('/srv/www/httpdocs/wb/media/') will return:
 *  /a
 *  /a/b
 *  /a/b/c
 *  /a/b/d
 */
    function directory_list($directory, $show_hidden = false)
    {
        $result_list = [];
/* deprecated
        if (\is_dir($directory))
        {
            $dir = \dir($directory); // Open the directory
            while (false !== $entry = $dir->read()) // loop through the directory
            {
                if($entry == '.' || $entry == '..') { continue; } // Skip pointers
                if($entry[0] == '.' && $show_hidden == false) { continue; } // Skip hidden files
                $sItem = $directory.'/'.$entry.(\is_dir($directory.'/'.$entry) ? '' : '');
                if (\is_dir($sItem)) {
                  // Add dir and contents to list
                    $result_list = \array_merge($result_list, directory_list($sItem));
                    $result_list[] = $sItem;
                }
            }
            $dir->close();
        }
*/

/* */
        if (\is_dir($directory))
        {
            $dir = array_values(array_diff(\scandir($directory),['.','..'])); //Open directory
            foreach ($dir as $key => $entry) {
                if(($entry[0] === '.') && ($show_hidden == false)) { continue; } // Skip hidden files
                $sItem = str_replace('//', '/',$directory.'/'.$entry.(\is_dir($directory.'/'.$entry) ? '' : ''));
                if (\is_dir($sItem)) { // Add dir and contents to list
                    $result_list = \array_merge($result_list, directory_list($sItem));
                    $result_list[] = str_replace(MEDIA_DIRECTORY,'',$sItem);
                }
            }
        }
        // sorting
        if (\natcasesort($result_list)) {
            // new indexing
            $result_list = \array_merge($result_list);
        }
        return $result_list; // Now return the list
    }

//  Function to open a directory and add to a dir list
    function chmod_directory_contents($directory, $file_mode)
    {
        if (\is_dir($directory))
        {
            // Set the umask to 0
            $umask = \umask(0);
            // Open the directory then loop through its contents
            $dir = \dir($directory);
            while (false !== $entry = $dir->read())
            {
                // Skip pointers
                if($entry[0] == '.') { continue; }
                // Chmod the sub-dirs contents
                if(\is_dir("$directory/$entry")) {
                    chmod_directory_contents($directory.'/'.$entry, $file_mode);
                }
                change_mode($directory.'/'.$entry);
            }
            $dir->close();
            // Restore the umask
            \umask($umask);
        }
    }

/**
* Scan a given directory for dirs and files.
*
* usage: scan_current_dir ($root = '' )
*
* @param     $root   set a absolute rootpath as string. if root is empty the current path will be scan
* @param     $search set a search pattern for files, empty search brings all files
* @access    public
* @return    array    returns a natsort array with keys 'path' and 'filename'
*
*/
if (!\is_callable('scan_current_dir'))
{
    function scan_current_dir($root = '', $search = '/.*/')
    {
        $FILE = [];
        $array = [];
        \clearstatcache();
        $root = (empty ($root) ? \getcwd() : $root);
        if (($handle = \opendir($root)))
        {
        // Loop through the files and dirs an add to list  DIRECTORY_SEPARATOR
            while (false !== ($file = \readdir($handle)))
            {
                if (\substr($file, 0, 1) != '.' && $file != 'index.php')
                {
                    if (\is_dir($root.'/'.$file)) {
                        $FILE['path'][] = $file;
                    } elseif (\preg_match($search, $file, $array) ) {
                        $FILE['filename'][] = $array[0];
                    }
                }
            }
            $close_verz = \closedir($handle);
        }
        // sorting
        if (isset ($FILE['path']) && \natcasesort($FILE['path'])) {
            // new indexing
            $FILE['path'] = \array_merge($FILE['path']);
        }
        // sorting
        if (isset ($FILE['filename']) && \natcasesort($FILE['filename'])) {
            // new indexing
            $FILE['filename'] = \array_merge($FILE['filename']);
        }
        return $FILE;
    }
}

//  Function to open a directory and add to a file list
    function file_list($directory, $skip = [], $show_hidden = false)
    {
        $result_list = [];
        if (\is_dir($directory))
        {
            $dir = dir($directory); // Open the directory
            while (false !== ($entry = $dir->read())) // loop through the directory
            {
                if($entry == '.' || $entry == '..') { continue; } // Skip pointers
                if($entry[0] == '.' && $show_hidden == false) { continue; } // Skip hidden files
                if (\count($skip) > 0 && \in_array($entry, $skip) ) { continue; } // Check if we to skip anything else
                if (\is_file( $directory.'/'.$entry)) { // Add files to list
                    $result_list[] = $directory.'/'.$entry;
                }
            }
            $dir->close(); // Now close the folder object
        }
        // make the list nice. Not all OS do this itself
        if (\natcasesort($result_list)) {
            $result_list = \array_merge($result_list);
        }
        return $result_list;
    }

//  Function to get a list of home folders not to show
    function get_home_folders()
    {
        global $admin;
        $database = \database::getInstance();
        $home_folders = [];
        // Only return home folders is this feature is enabled
        // and user is not admin
        if (HOME_FOLDERS && (!\in_array('1',\explode(',', $_SESSION['GROUPS_ID']))))
        {
            $sql  = 'SELECT `home_folder` FROM `'.TABLE_PREFIX.'users` '."\n"
                  . 'WHERE `home_folder`!=\''.$admin->get_home_folder().'\''."\n";
            $query_home_folders = $database->query($sql);
            if($query_home_folders->numRows() > 0)
            {
                while($folder = $query_home_folders->fetchRow()) {
                    $home_folders[$folder['home_folder']] = $folder['home_folder'];
                }
            }
            function remove_home_subs($directory = '/', $home_folders = '')
            {
                if( ($handle = \opendir(WB_PATH.MEDIA_DIRECTORY.$directory)) )
                {
                    // Loop through the dirs to check the home folders sub-dirs are not shown
                    while(false !== ($file = \readdir($handle)))
                    {
                        if ($file[0] != '.' && $file != 'index.php')
                        {
                            if (\is_dir(WB_PATH.MEDIA_DIRECTORY.$directory.'/'.$file))
                            {
                                if($directory != '/') {
                                    $file = $directory.'/'.$file;
                                }else {
                                    $file = '/'.$file;
                                }
                                foreach($home_folders AS $hf)
                                {
                                    $hf_length = \strlen($hf);
                                    if ($hf_length > 0) {
                                        if(\substr($file, 0, $hf_length+1) == $hf) {
                                            $home_folders[$file] = $file;
                                        }
                                    }
                                }
                                $home_folders = remove_home_subs($file, $home_folders);
                            }
                        }
                    }
                }
                return $home_folders;
            }
            $home_folders = remove_home_subs('/', $home_folders);
        }
        return $home_folders;
    }

/*
 * @param object &$wb: $wb from frontend or $admin from backend
 * @return array: list of new entries
 * @description: callback remove path in files/dirs stored in array
 * @example: array_walk($array,'remove_path',PATH);
 */
//
    function remove_path(& $path, $key='', $vars = '')
    {
        $path = \str_replace($vars, '', $path);
    }

/*
 * @param object &$wb: $wb from frontend or $admin from backend
 * @return array: list of ro-dirs
 * @description: returns a list of directories beyound /wb/media which are ReadOnly for current user
 */
    function media_dirs_ro(& $wb )
    {
        $database = \database::getInstance();
        // if user is admin or home-folders not activated then there are no restrictions
        $allow_list = [];
        if( $wb->ami_group_member('1') || !HOME_FOLDERS ) {
//        if( $wb->getUserId() == 1 || !HOME_FOLDERS ) {
            return [];
        }
        // at first read any dir and subdir from /media
        $full_list = directory_list( WB_PATH.MEDIA_DIRECTORY );
        // add own home_folder to allow-list
        if( $wb->get_home_folder() ) {
            // old: $allow_list[] = get_home_folder();
            $allow_list[] = $wb->get_home_folder();
        }
        // get groups of current user
        $curr_groups = $wb->get_groups_id();
        // if current user is in admin-group
        if( ($admin_key = \array_search('1', $curr_groups)) !== false)
        {
            // remove admin-group from list
            unset($curr_groups[$admin_key]);
            // search for all users where the current user is admin from
            foreach( $curr_groups as $group)
            {
                $sql  = 'SELECT `home_folder` FROM `'.TABLE_PREFIX.'users` '."\n"
                      . 'WHERE (FIND_IN_SET(\''.$group.'\', `groups_id`) > 0) '."\n"
                      .   'AND `home_folder` <> \'\' '."\n"
                      .   'AND `user_id` <> '.$wb->getUserId()."\n";
                if( ($res_hf = $database->query($sql)) != null ) {
                    while( $rec_hf = $res_hf->fetchrow() ) {
                        $allow_list[] = $rec_hf['home_folder'];
                    }
                }
            }
        }
        $tmp_array = $full_list;
        // create a list for readonly dir
        $array = [];
        while(\count($tmp_array) > 0)
        {
            $tmp = \array_shift($tmp_array);
            $x = 0;
            while($x < \count($allow_list)) {
                if (\strpos ($tmp,$allow_list[$x])) {
                    $array[] = $tmp;
                }
                $x++;
            }
        }
        $full_list = \array_diff( $full_list, $array );
        $tmp = [];
        $full_list = \array_merge($tmp,$full_list);
        return $full_list;
    }

/*
 * @param object &$wb: $wb from frontend or $admin from backend
 * @return array: list of rw-dirs
 * @description: returns a list of directories beyound /wb/media which are ReadWrite for current user
 */
    function media_dirs_rw (& $wb )
    {
        $database = \database::getInstance();
        // if user is admin or home-folders not activated then there are no restrictions
        // at first read any dir and subdir from /media
        $full_list = directory_list( WB_PATH.MEDIA_DIRECTORY );
        $array = [];
        $allow_list = [];
        if( ($wb->ami_group_member('1')) && !HOME_FOLDERS ) {
            return $full_list;
        }
        // add own home_folder to allow-list
        if( $wb->get_home_folder() ) {
              $allow_list[] = $wb->get_home_folder();
        } else {
            $array = $full_list;
        }
        // get groups of current user
        $curr_groups = $wb->get_groups_id();
        // if current user is in admin-group
        if( ($admin_key = \array_search('1', $curr_groups)) == true)
        {
            // remove admin-group from list
            // unset($curr_groups[$admin_key]);
            // search for all users where the current user is admin from
            foreach( $curr_groups as $group)
            {
                $sql  = 'SELECT `home_folder` FROM `'.TABLE_PREFIX.'users` ';
                $sql .= 'WHERE (FIND_IN_SET(\''.$group.'\', `groups_id`) > 0) AND `home_folder` <> \'\' AND `user_id` <> '.$wb->getUserId();
                if( ($res_hf = $database->query($sql)) != null ) {
                    while( $rec_hf = $res_hf->fetchrow() ) {
                        $allow_list[] = $rec_hf['home_folder'];
                    }
                }
            }
        }
        $tmp_array = $full_list;
        // create a list for readwrite dir
        while( \count($tmp_array) > 0)
        {
            $tmp = array_shift($tmp_array);
            $x = 0;
            while($x < \count($allow_list)) {
                if (\strpos ($tmp,$allow_list[$x])) {
                    $array[] = $tmp;
                }
                $x++;
            }
        }
        $tmp = [];
        $array = \array_unique($array);
        $full_list = \array_merge($tmp,$array);
        unset($array);
        unset($allow_list);
        return $full_list;
    }

//  Function to create directories
    function make_dir($sAbsPath, $dir_mode = OCTAL_DIR_MODE, $recursive=true)
    {
        $bRetval = \is_dir($sAbsPath);
        if (!$bRetval)
        {
            $bRetval = \mkdir($sAbsPath, $dir_mode,$recursive);
        }
        return $bRetval;
    }

//  Function to chmod files and directories
    function change_mode($name)
    {
        $bRetval = false;
        if (\strtoupper(\substr(PHP_OS, 0, 3)) !== 'WIN')
        {
            // Only chmod if os is not windows
            if (\is_dir($name)) {
                $mode = OCTAL_DIR_MODE;
            }else {
                $mode = OCTAL_FILE_MODE;
            }
            if (\file_exists($name)) {
                $umask = \umask(0);
                \chmod($name, $mode);
                \umask($umask);
                $bRetval = true;
            } else {
                $bRetval = false;
            }
        } else {
            $mode = '777';
            chmod($name, octdec($mode));
            $bRetval = true;
        }
        return $bRetval;
    }

//  Function to figure out if a parent exists
    function is_parent($page_id)
    {
        $database = \database::getInstance();
//      Get parent
        $sql = 'SELECT `parent` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.(int)$page_id;
        $iParent = $database->get_one($sql);
//      If parent isnt 0 return its ID
        if (\is_null($iParent)) {
            return false;
        }else {
            return $iParent;
        }
    }

//  Function to work out level
    function level_count($page_id)
    {
        $database = \database::getInstance();
    //  Get page parent
        $sql = 'SELECT `parent` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.(int)$page_id;
        $iParent = $database->get_one($sql);
        if ($iParent > 0){
//          Get the level of the parent
            $sql = 'SELECT `level` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.(int)$iParent;
            $iLevel = \intval($database->get_one($sql));
            return $iLevel+1;
        }else {
            return 0;
        }
    }

//  Function to work out root parent
    function root_parent($page_id)
    {
        $database = \database::getInstance();
//      Get page details
        $sql = 'SELECT `parent`, `level` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.(int)$page_id;
        $query_page = $database->query($sql);
        $fetch_page = $query_page->fetchRow();
        $iParent    = \intval($fetch_page['parent']);
        $iLevel     = \intval($fetch_page['level']);
        if($iLevel == 1) {
            return $iParent;
        }elseif($iParent == 0) {
            return $page_id;
        }else {
//          Figure out what the root parents id is
            $parent_ids = \array_reverse(get_parent_ids($page_id));
            return $parent_ids[0];
        }
    }

//  Function to get page title
    function get_page_title($id)
    {
        $database = \database::getInstance();
        // Get title
        $sql = 'SELECT `page_title` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.intval($id);
        $page_title = $database->get_one($sql);
        return $page_title;
    }

//  Function to get a pages menu title
    function get_menu_title($id)
    {
        $database = \database::getInstance();
        // Get title
        $sql = 'SELECT `menu_title` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.\intval($id);
        $menu_title = $database->get_one($sql);
        return $menu_title;
    }

//  Function to get a pages menu title
    function get_seo_title($id)
    {
        $database = \database::getInstance();
        // Get link
        $sql = 'SELECT `link` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.\intval($id);
        $seo_title = \basename($database->get_one($sql));
        return $seo_title;
    }

//  Function to get all parent page titles
    function get_parent_links($parent_id)
    {
        $aLinks[] = get_seo_title($parent_id);
        if (is_parent($parent_id) != false) {
            $parent_titles = get_parent_links(is_parent($parent_id));
            $aLinks = \array_merge($aLinks, $parent_titles);
        }
        return $aLinks;
    }

//  Function to get all parent page titles  deprecated
    function get_parent_titles($parent_id)
    {
      return get_parent_links($parent_id);
    }

//  Function to get all parent page id's
    function get_parent_ids($parent_id)
    {
        $ids[] = intval($parent_id);
        if (is_parent($parent_id) != false) {
            $parent_ids = get_parent_ids(is_parent($parent_id));
            $ids = \array_merge($ids, $parent_ids);
        }
        return $ids;
    }

//  Function to genereate page trail
    function get_page_trail($page_id)
    {
        return \implode(',', \array_reverse(get_parent_ids($page_id)));
    }

//  Function to get all sub pages id's
    function get_subs($parent, array $subs )
    {
        // Connect to the database
        $database = \database::getInstance();
        // Get id's
        $sql = 'SELECT `page_id` FROM `'.TABLE_PREFIX.'pages` WHERE `parent` = '.intval($parent);
        if( ($query = $database->query($sql)) ) {
            while($fetch = $query->fetchRow( MYSQLI_ASSOC )) {
                $subs[] = \intval($fetch['page_id']);
                // Get subs of this sub recursive
                $subs = get_subs($fetch['page_id'], $subs);
            }
        }
        // Return subs array
        return $subs;
    }

//  Function as replacement for php's htmlspecialchars()
//  Will not mangle HTML-entities
    function my_htmlspecialchars($string)
    {
        $string = \preg_replace('/&(?=[#a-z0-9]+;)/i', '__amp;_', $string);
        $string = \strtr($string, array('<'=>'&lt;', '>'=>'&gt;', '&'=>'&amp;', '"'=>'&quot;', '\''=>'&#39;'));
        $string = \preg_replace('/__amp;_(?=[#a-z0-9]+;)/i', '&', $string);
        return($string);
    }

//  Convert a string from mixed html-entities/umlauts to pure $charset_out-umlauts
//  Will replace all numeric and named entities except &gt; &lt; &apos; &quot; &#039; &nbsp;
//  In case of error the returned string is unchanged, and a message is emitted.
    function entities_to_umlauts($string, $charset_out=DEFAULT_CHARSET)
    {
        if (!is_callable('utf8_check')){require(WB_PATH.'/framework/functions-utf8.php');}
        return entities_to_umlauts2($string, $charset_out);
    }

//  Will convert a string in $charset_in encoding to a pure ASCII string with HTML-entities.
//  In case of error the returned string is unchanged, and a message is emitted.
    function umlauts_to_entities($string, $charset_in=DEFAULT_CHARSET)
    {
        if (!is_callable('utf8_check')){require(WB_PATH.'/framework/functions-utf8.php');}
        return umlauts_to_entities2($string, $charset_in);
    }

//  Function to convert a page title to a page filename
    function page_filename($string,$bCaseSensitiv=false, $mPageStyle=null)
    {
        if (!is_callable('utf8_check')){require(WB_PATH.'/framework/functions-utf8.php');}
        $string = entities_to_7bit($string);
    //  Now remove all bad characters
        $bad = [
        '\'', /* /  */ '"', /* " */    '<', /* < */    '>', /* > */
        '{', /* { */    '}', /* } */    '[', /* [ */    ']', /* ] */    '`', /* ` */
        '!', /* ! */    '@', /* @ */    '#', /* # */    '$', /* $ */    '%', /* % */
        '^', /* ^ */    '&', /* & */    '*', /* * */    '(', /* ( */    ')', /* ) */
        '=', /* = */    '+', /* + */    '|', /* | */    '/', /* / */    '\\', /* \ */
        ';', /* ; */    ':', /* : */    ',', /* , */    '?' /* ? */
        ];
        $string = \str_replace($bad, '', $string);
    //  replace multiple dots in filename to single dot and (multiple) dots at the end of the filename to nothing
        $string = \preg_replace(['/\.+/', '/\.+$/'], ['.', ''], $string);
    //  Now replace spaces with page spcacer
        $string = \trim($string);
        $sPageSpacer = (((\defined(PAGE_SPACER) && (int)empty(PAGE_SPACER)) === 0) ? PAGE_SPACER : '-');  //  trim(PAGE_SPACER)
        $bPageNewStyle = (!\is_null($mPageStyle) ? \filter_var($mPageStyle, \FILTER_VALIDATE_BOOLEAN) : true);
    //  sanitize to new format
        if ($bPageNewStyle){
            $aString= \preg_split('/[\s,=+_\-\;\:\.\|]+/', $string, -1, PREG_SPLIT_NO_EMPTY);
            $string = \implode($sPageSpacer,$aString);
        } else {
        //  hold the old format
            $string = \preg_replace('/(\s)+/', $sPageSpacer, $string);
        }
    //  Now convert to lower-case
        $string = ($bCaseSensitiv ? $string : \strtolower($string));
    //  If there are any weird language characters, this will protect us against possible problems they could cause
        $string = \str_replace(array('%2F', '%'), array('/', ''), \urlencode($string));
    //  Finally, return the cleaned string
        return ($string);
    }

//  Function to convert a desired media filename to a clean mediafilename
    function media_filename($string)
    {
        if (!is_callable('utf8_check')){require(WB_PATH.'/framework/functions-utf8.php');}
        $string = entities_to_7bit($string);
        // Now remove all bad characters
        $bad = ['\'','"','`','!','@','#','$','%','^','&','(',')','*','=','+','|','/','\\',';',':',',','?'];
        $string = \str_replace($bad, '', $string);
        // replace multiple dots in filename to single dot and (multiple) dots at the end of the filename to nothing
        $string = \preg_replace(array('/\.+/', '/\.+$/', '/\s/'), array('.', '', '_'), $string);
        // Clean any page spacers at the end of string
        $string = \trim($string);
        // Finally, return the cleaned string
        return $string;
    }

//  Function to work out a page link
    if (!\is_callable('page_link'))
    {
        function page_link($sLink='', $bRetro=false)
        {
        return WbAdaptor::getInstance()
            ->getApplication()
            ->page_link($sLink,$bRetro);
        }
    }

//  Create a new directory and/or protected file in the given directory
    function createFolderProtectFile($sAbsDir='',$make_dir=true)
    {
        \trigger_error('Deprecated function call: '.\basename(__DIR__).'/'.\basename(__FILE__).'::'.__FUNCTION__, E_USER_DEPRECATED );
        return [];
    }

    function rebuildFolderProtectFile($dir='')
    {
        \trigger_error('Deprecated function call: '.\basename(__DIR__).'/'.\basename(__FILE__).'::'.__FUNCTION__, E_USER_DEPRECATED );
        return [];
    }

//  Create a new file in the pages directory
    function create_access_file($filename,$page_id=0,$level=0)
    {
        global $admin, $MESSAGE;
        try {
            $sAccessFilesRoot = \rtrim(\str_replace('\\', '/', WB_PATH.PAGES_DIRECTORY), '/').'/';
            $sFilename = \str_replace($sAccessFilesRoot, '', \str_replace('\\', '/', $filename));
            $oAccessFile = new \AccessFile($sAccessFilesRoot, $sFilename, (int) $page_id);
            $oAccessFile->write();
        } catch (\exception $ex) {
            $GLOBALS['admin']->print_error($ex->getMessage());
        }
        return;
    }

if (!\is_callable('mime_content_type'))
{
//  Function for working out a file mime type (if the in-built PHP one is not enabled)
    function mime_content_type($filename)
    {
        $mime_types = array(
            'txt'    => 'text/plain',
            'htm'    => 'text/html',
            'html'    => 'text/html',
            'php'    => 'text/html',
            'css'    => 'text/css',
            'js'    => 'application/javascript',
            'json'    => 'application/json',
            'xml'    => 'application/xml',
            'swf'    => 'application/x-shockwave-flash',
            'flv'    => 'video/x-flv',

            // images
            'png'    => 'image/png',
            'jpe'    => 'image/jpeg',
            'jpeg'    => 'image/jpeg',
            'jpg'    => 'image/jpeg',
            'gif'    => 'image/gif',
            'bmp'    => 'image/bmp',
            'ico'    => 'image/vnd.microsoft.icon',
            'tiff'    => 'image/tiff',
            'tif'    => 'image/tiff',
            'svg'    => 'image/svg+xml',
            'svgz'    => 'image/svg+xml',

            // archives
            'zip'    => 'application/zip',
            'rar'    => 'application/x-rar-compressed',
            'exe'    => 'application/x-msdownload',
            'msi'    => 'application/x-msdownload',
            'cab'    => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3'    => 'audio/mpeg',
            'mp4'    => 'audio/mpeg',
            'qt'     => 'video/quicktime',
            'mov'    => 'video/quicktime',

            // adobe
            'pdf'    => 'application/pdf',
            'psd'    => 'image/vnd.adobe.photoshop',
            'ai'    => 'application/postscript',
            'eps'    => 'application/postscript',
            'ps'    => 'application/postscript',

            // ms office
            'doc'    => 'application/msword',
            'rtf'    => 'application/rtf',
            'xls'    => 'application/vnd.ms-excel',
            'ppt'    => 'application/vnd.ms-powerpoint',

            // open office
            'odt'    => 'application/vnd.oasis.opendocument.text',
            'ods'    => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        $temp = \explode('.',$filename);
        $ext  = \strtolower(\array_pop($temp));
        if (\array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }elseif (\is_callable('finfo_open')) {
            $finfo = \finfo_open(FILEINFO_MIME);
            $mimetype = \finfo_file($finfo, $filename);
            \finfo_close($finfo);
            return $mimetype;
        }else {
            return 'application/octet-stream';
        }
    }
}

//  Generate a thumbnail from an image
    function make_thumb($sSource, $sDestination='', $width = 0, $height = 0)
    {
        $mRetVal = null;
        // Check if GD is installed
        if(\extension_loaded('gd') && \is_callable('imageCreateFromJpeg'))
        {
            $thumb = new GD($sSource);
//            $thumb = PhpThumbFactory::create($sSource);
            $thumb->resize($width, $height); // adaptiveResize
            $mRetVal = $thumb->save($sDestination);
        }
        return $mRetVal;
    }

/*
 * Function to work-out a single part of an octal permission value
 *
 * @param mixed $octal_value: an octal value as string (i.e. '0777') or real octal integer (i.e. 0777 | 777)
 * @param string $who: char or string for whom the permission is asked( U[ser] / G[roup] / O[thers] )
 * @param string $action: char or string with the requested action( r[ead..] / w[rite..] / e|x[ecute..] )
 * @return boolean
 */
    function extract_permission($octal_value, $who, $action)
    {
//      Make sure that all arguments are set and $octal_value is a real octal-integer
        if (($who == '') || ($action == '') || (\preg_match( '/[^0-7]/', (string)$octal_value ))) {
//          invalid argument, so return false
            return false;
        }
//      convert $octal_value into a decimal-integer to be sure having a valid value
        $right_mask = \octdec($octal_value);
        $action_mask = 0;
//      set the $action related bit in $action_mask
        switch($action[0]) { // get action from first char of $action
            case 'r':
            case 'R':
                $action_mask = 4; // set read-bit only (2^2)
                break;
            case 'w':
            case 'W':
                $action_mask = 2; // set write-bit only (2^1)
                break;
            case 'e':
            case 'E':
            case 'x':
            case 'X':
                $action_mask = 1; // set execute-bit only (2^0)
                break;
            default:
                return false; // undefined action name, so return false
        }
//      shift action-mask into the right position
        switch($who[0]) { // get who from first char of $who
            case 'u':
            case 'U':
                $action_mask <<= 3; // shift left 3 bits
            case 'g':
            case 'G':
                $action_mask <<= 3; // shift left 3 bits
            case 'o':
            case 'O':
                /* NOP */
                break;
            default:
                return false; // undefined who, so return false
        }
        return( ($right_mask & $action_mask) != 0 ); // return result of binary-AND
    }

//  Function to delete a page
    function delete_page($page_id)
    {
        $oReg = WbAdaptor::getInstance();
        $database = $oReg->getDatabase();
        $oTrans   = $oReg->getTranslate();
        $oApp = $oReg->getApplication();
//      Find out more about the page
        $sql  = 'SELECT `page_id`, `menu_title`, `page_title`, `level`, '."\n"
              . '`link`, `parent`, `modified_by`, `modified_when` '."\n"
              . 'FROM `'.TABLE_PREFIX.'pages` '."\n"
              . 'WHERE `page_id`='.(int)$page_id."\n";
        $results = $database->query($sql);
        if($database->is_error())    { $admin->print_error($database->get_error()); }
        if($results->numRows() == 0) { $admin->print_error($oTrans->MESSAGE_PAGES_NOT_FOUND); }
        $results_array = $results->fetchRow(MYSQLI_ASSOC);
        $parent     = $results_array['parent'];
        $level      = $results_array['level'];
        $link       = $results_array['link'];
        $page_title = $results_array['page_title'];
        $menu_title = $results_array['menu_title'];
//      Get the sections that belong to the page
        $sql  = 'SELECT `section_id`, `module` FROM `'.TABLE_PREFIX.'sections` '."\n"
              . 'WHERE `page_id`='.(int)$page_id."\n";
        $query_sections = $database->query($sql);
        if ($query_sections->numRows() > 0)
        {
            while($section = $query_sections->fetchRow(MYSQLI_ASSOC)) {
                // Set section id
                $section_id = $section['section_id'];
                // Include the modules delete file if it exists
                if (\is_writeable(WB_PATH.'/modules/'.$section['module'].'/delete.php')) {
                    include(WB_PATH.'/modules/'.$section['module'].'/delete.php');
                }
            }
        }
        // Update the pages table
        $sql = 'DELETE FROM `'.TABLE_PREFIX.'pages` WHERE `page_id`='.$page_id;
        $database->query($sql);
        if ($database->is_error()) {
            $oApp->print_error($database->get_error());
        }
        // Update the sections table
        $sql = 'DELETE FROM `'.TABLE_PREFIX.'sections` WHERE `page_id`='.$page_id;
        $database->query($sql);
        if ($database->is_error()) {
            $oApp->print_error($database->get_error());
        }
        // Include the ordering class or clean-up ordering
        $order = new order(TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
        $order->clean($parent);
        // Unlink the page access file and directory
        $directory = WB_PATH.PAGES_DIRECTORY.$link;
        $filename = $directory.PAGE_EXTENSION;
        $directory .= '/';
        if (\file_exists($filename))
        {
            if (!\is_writable(WB_PATH.PAGES_DIRECTORY.'/')) {
                $oApp->print_error($oTrans->MESSAGE_PAGES_CANNOT_DELETE_ACCESS_FILE);
            }else {
                \unlink($filename);
                if (\file_exists($directory) &&
                   (\rtrim($directory,'/') != WB_PATH.PAGES_DIRECTORY) &&
                   (\substr($link, 0, 1) != '.'))
                {
                    rm_full_dir($directory);
                }
            }
        }
    }

/*
 * @param string $file: name of the file to read
 * @param int $size: number of maximum bytes to read (0 = complete file)
 * @return string: the content as string, false on error
 */
    function getFilePart($file, $size = 0)
    {
        $file_content = '';
        if (\file_exists($file) && \is_file($file) && \is_readable($file))
        {
            if ($size == 0) {
                $size = \filesize($file);
            }
            if(($fh = \fopen($file, 'rb'))) {
                if( ($file_content = \fread($fh, $size)) !== false ) {
                    return $file_content;
                }
                \fclose($fh);
            }
        }
        return false;
    }

    /**
    * replace varnames with values in a string
    *
    * @param string $subject: stringvariable with vars placeholder
    * @param array $replace: values to replace vars placeholder
    * @return string
    */
    function replace_vars($sSubject = '', &$aReplace = null )
    {
        if (\is_array($aReplace))
        {
            foreach ($aReplace  as $key => $value) {
                $sSubject = \str_replace("{{".$key."}}", $value, $sSubject);
            }
        }
        return $sSubject;
    }

//  Load language into DB
    function load_language($sFile)
    {
        global $admin;
        $database = \database::getInstance();

        $retVal = true;
        if (\is_readable($sFile) && \preg_match('#^([A-Z]{2}.php)#', \basename($sFile)))
        {
            // require($sFile);  it's to large
            // read contents of the template language file into string
            $data = \file_get_contents(WB_PATH.'/languages/'.\str_replace('.php','',\basename($sFile)).'.php');
            // use regular expressions to fetch the content of the variable from the string
            $language_code        = \preg_replace('/^.*([a-zA-Z]{2})\.php$/si', '\1', $sFile);
            $language_name        = get_variable_content('language_name', $data, false, false);
            $language_author      = get_variable_content('language_author', $data, false, false);
            $language_version     = get_variable_content('language_version', $data, false, false);
            $language_platform    = get_variable_content('language_platform', $data, false, false);
            $language_description = get_variable_content('language_description', $data, false, false);
            if (isset($language_name))
            {
                $action = 'upgrade';
                if (!isset($language_license)) { $language_license = 'GNU General Public License'; }
                if (!isset($language_platform) && isset($language_designed_for)) { $language_platform = $language_designed_for; }
                // Check that it doesn't already exist
                $sqlSet = '`directory`=\''.$language_code.'\', '
                        . '`name`=\''.$database->escapeString($language_name).'\', '
                        . '`type`=\'language\', '
                        . '`version`=\''.$database->escapeString($language_version).'\', '
                        . '`platform`=\''.$database->escapeString($language_platform).'\', '
                        . '`author`=\''.$database->escapeString($language_author).'\', '
                        . '`description`=\'\', '
                        . '`license`=\''.$database->escapeString($language_license).'\' ';
                $sqlwhere = 'WHERE `type`=\'language\' AND `directory`=\''.$language_code.'\'';
                $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'addons` '.$sqlwhere;
                if( $database->get_one($sql) ) {
                    $sql  = 'UPDATE `'.TABLE_PREFIX.'addons` SET '.$sqlSet.$sqlwhere;
                }else{
                    // Load into DB
                    $sql  = 'INSERT INTO `'.TABLE_PREFIX.'addons` SET '.$sqlSet;
                    $action = 'install';
                }
                if (!$database->query($sql)){$retVal = false;}
            }
        }
        return $retVal;
    }

//  Load module into DB
    function load_module($sAddonDir, $mode = false, $aAddonInfo=[])
    {
        global $admin, $MESSAGE;
        $bLoaded = true;
        $retVal = true;
        $oReg = Wbadaptor::getInstance();
        $database = $oDb = $oReg->getDatabase();
        if (\is_file($sAddonDir)){$sAddonDir=\dirname($sAddonDir);}
        if (\is_dir($sAddonDir) && \file_exists($sAddonDir.'/info.php'))
        {
            if (empty($aAddonInfo)){
                if (\is_readable($sAddonDir.'/info.php')){require($sAddonDir.'/info.php');}
            } else {
                $module_name         = ($aAddonInfo['common']['name']);
                $module_version      = ($aAddonInfo['common']['version']);
                $module_directory    = ($aAddonInfo['common']['directory']);
                $module_function     = ($aAddonInfo['common']['function']);
                $module_platform     = ($aAddonInfo['common']['platform']);
                $module_description  = ($aAddonInfo['common']['description']);
                $module_author       = ($aAddonInfo['common']['author']);
                $module_license      = ($aAddonInfo['common']['license']);
            }
            if (isset($module_name)){
                $sAction = 'upgrade';
                if (!isset($module_license)) { $module_license = 'GNU General Public License'; }
                if (!isset($module_platform) && isset($module_designed_for)) { $module_platform = $module_designed_for; }
                if (!isset($module_function) && isset($module_type)) { $module_function = $module_type; }
                $module_function = \strtolower($module_function);
                $sqlSet = '`directory`=\''.$database->escapeString($module_directory).'\', '
                        . '`name`=\''.$database->escapeString($module_name).'\', '
                        . '`description`=\''.$database->escapeString($module_description).'\', '
                        . '`type`=\'module\', '
                        . '`function`=\''.$database->escapeString($module_function).'\', '
                        . '`version`=\''.$database->escapeString($module_version).'\', '
                        . '`platform`=\''.$database->escapeString($module_platform).'\', '
                        . '`author`=\''.$database->escapeString($module_author).'\', '
                        . '`license`=\''.$database->escapeString($module_license).'\' ';
                // Check that it doesn't already exist
                $sqlwhere = 'WHERE `type` = \'module\' AND `directory` = \''.$module_directory.'\'';
                $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'addons` '.$sqlwhere;
                if ( $database->get_one($sql) ) {
                    $sql  = 'UPDATE `'.TABLE_PREFIX.'addons` SET '.$sqlSet.$sqlwhere;
                } else{
                    // Load into DB
                    $sql  = 'INSERT INTO `'.TABLE_PREFIX.'addons` SET '.$sqlSet;
                    $sAction = 'install';
                }
                if (!$database->query($sql)){
                    $retVal = false;
                    \trigger_error(\sprintf('[%d] Database Error:: %s',__LINE__, $database->get_error()), E_USER_NOTICE);
                } else {
                    // Run installation script
                    \Translate::getInstance ()->disableAddon ('modules\\'.$module_name);
                    \Translate::getInstance()->enableAddon(ADMIN_DIRECTORY.'\\addons');
                    if (\is_writable(WB_PATH.'/temp/cache')) {
                        \Translate::getInstance()->clearCache();
                    }
                    if ($mode == true) {
                        if (\is_readable($sAddonDir.'/'.$sAction.'.php')) {
                            require($sAddonDir.'/'.$sAction.'.php');
                            $retVal = true; //$bLoaded  (feature: set by addon)
                        }
                    }
                }
            } else {
              $retVal = false;
            }
        }
        return $retVal;
    }

//  Load template into DB
    function load_template($sAddonDir, $mode = false, $aAddonInfo=[])
    {
        global $admin;
        $database = \database::getInstance();
        $retVal = true;
        if (\is_file($sAddonDir)){$sAddonDir=\dirname($sAddonDir);}
        if (\is_dir($sAddonDir) && \file_exists($sAddonDir.'/info.php'))
        {
            if (!\count($aAddonInfo)){
                if (is_readable($sAddonDir.'/info.php')){require($sAddonDir.'/info.php');}
            } else {
                $template_name         = ($aAddonInfo['common']['name']);
                $template_version      = ($aAddonInfo['common']['version']);
                $template_directory    = ($aAddonInfo['common']['directory']);
                $template_function     = ($aAddonInfo['common']['function']);
                $template_platform     = ($aAddonInfo['common']['platform']);
                $template_description  = ($aAddonInfo['common']['description']);
                $template_author       = ($aAddonInfo['common']['author']);
                $template_license      = ($aAddonInfo['common']['license']);
            }
            if (isset($template_name))
            {
                $action = 'upgrade';
                if(!isset($template_license)) {
                  $template_license = 'GNU General Public License';
                }
                if(!isset($template_platform) && isset($template_designed_for)) {
                  $template_platform = $template_designed_for;
                }
                if(!isset($template_function)) {
                  $template_function = 'template';
                }
                $sqlSet = '`directory`=\''.$database->escapeString($template_directory).'\', '.PHP_EOL
                        . '`name`=\''.$database->escapeString($template_name).'\', '.PHP_EOL
                        . '`description`=\''.$database->escapeString($template_description).'\', '.PHP_EOL
                        . '`type`=\'template\', '.PHP_EOL
                        . '`function`=\''.$database->escapeString($template_function).'\', '.PHP_EOL
                        . '`version`=\''.$database->escapeString($template_version).'\', '.PHP_EOL
                        . '`platform`=\''.$database->escapeString($template_platform).'\', '.PHP_EOL
                        . '`author`=\''.$database->escapeString($template_author).'\', '.PHP_EOL
                        . '`license`=\''.$database->escapeString($template_license).'\' '.PHP_EOL;
                // Check that it doesn't already exist
                $sqlwhere = 'WHERE `type`=\'template\' AND `directory`=\''.$template_directory.'\'';
                $sqlCount = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'addons` '.$sqlwhere;
                if ($database->get_one($sqlCount) ) {
                    $sSql = 'UPDATE `'.TABLE_PREFIX.'addons` SET '.$sqlSet.$sqlwhere;
                } else {
                    // Load into DB
                    $sSql  = 'INSERT INTO `'.TABLE_PREFIX.'addons` SET '.$sqlSet;
                    $action = 'install';
                }
                if (!($database->query($sSql))){
                  $retVal = false;
                    \trigger_error(\sprintf('[%d] Database Error:: %s',__LINE__, $database->get_error()), E_USER_NOTICE);
                } else {
                    // Run installation script
                    \Translate::getInstance()->disableAddon ('templates\\'.$template_name);
                    \Translate::getInstance()->enableAddon(ADMIN_DIRECTORY.'\\addons');
                    if (\is_writable(WB_PATH.'/temp/cache')) {
                        \Translate::getInstance()->clearCache();
                    }
                    if ($mode == true) {
                        if (\file_exists($sAddonDir.'/'.$action.'.php')) {
                            require($sAddonDir.'/'.$action.'.php');
                            $retVal = true; //$bLoaded  (feature: set by addon)
                        }
                    }
                }
            }
        }
        return $retVal;
    }

/**
 * upgrade_module()
 *
 * @param mixed $sAddon
 * @param bool $upgrade
 * @return void
 * @deprecated since 2017/11/11
 */
    function upgrade_module($sAddon, $upgrade = false)
    {
        $directory = WB_PATH.'/modules/'.$sAddon;
        return load_module($directory, $upgrade);
    }

/* ************************************************************************** */
    function get_const_content($input='', $data='' )
    {
        $aMatches = [];
        $sRetval = '';
        $sSearch = \trim(\strtoupper($input));
        if (!empty($sSearch) && !empty($data)){
            $sPattern = '=define\s*\(\''.$sSearch .'\'\,\s*\'([^\']*)\'=is';
            $sRetval = ((\preg_match($sPattern, $data, $aMatches)) ? $aMatches[1] : '');
        }
        return $sRetval;
    }

/* ************************************************************************** */
if (!\is_callable('get_variable_content'))
{
//  extracts the content of a string variable from a string (save alternative to including files)
    function get_variable_content($search, $data, $striptags=true, $convert_to_entities=true)
    {
        $match = [];
        // search for $variable followed by 0-n whitespace then by = then by 0-n whitespace
        // then either " or ' then 0-n characters then either " or ' followed by 0-n whitespace and ;
        // the variable name is returned in $match[1], the content in $match[3]
        if (\preg_match('/(\$' .$search .')\s*=\s*("|\')(.*)\2\s*;/i', $data, $match))
        {
            if (\strip_tags(\trim($match[1])) == '$' .$search) {
                // variable name matches, return it's value
                $match[3] = ($striptags == true) ? \strip_tags($match[3]) : $match[3];
                $match[3] = ($convert_to_entities == true) ? \htmlentities($match[3]) : $match[3];
                return $match[3];
            }
        }
        return false;
    }
}

/*
 * @param string $modulname: like saved in addons.directory
 * @param boolean $source: true reads from database, false from info.php
 * @return string:  the version as string, if not found returns null
 */
    function get_modul_version($modulname, $source = true, $sAddonType='module')
    {
        $database = \database::getInstance();
        $version = null;
        if( $source != true )
        {
            $sql  = 'SELECT `version` FROM `'.TABLE_PREFIX.'addons` ';
            $sql .= 'WHERE `directory`=\''.$modulname.'\'';
            $version = $database->get_one($sql);
        } else {
            $info_file = WB_PATH.'/'.$sAddonType.'s/'.$modulname.'/info.php';
            if (\file_exists($info_file)) {
                if(($info_file = \file_get_contents($info_file))) {
                    $version = get_variable_content($sAddonType.'_version', $info_file, false, false);
                    $version = ($version !== false) ? $version : null;
                }
            }
        }
        return $version;
    }

/*
 * @param string $sModulname: like saved in addons.directory
 * @param string $sType: variable from info.php
 * @param string $sAddonType: module or template
 * @return string:  the version as string, if not found returns null
 */
    function get_addon_variable ($sModulname = '', $sType = 'version', $sAddonType='module'){
          $sRetval = null;
          $sSourceDir = WB_PATH.'/'.$sAddonType.'s/'.$sModulname;
          $sInfoFileName = \rtrim($sSourceDir,'/').'/info.php';
          $aParseDir = \preg_split('/[\s,=+\/\|]+/', $sInfoFileName, -1, PREG_SPLIT_NO_EMPTY);
          if (\is_readable($sInfoFileName)) {
              $sVarName = (\in_array('modules', $aParseDir) ? 'modules' : 'templates');
              require $sInfoFileName;
              $sAddonVarName = (\rtrim($sVarName, 's/').'_'.$sType);
              $sRetval = (isset($sAddonVarName) ? $$sAddonVarName : false);
          }
          return $sRetval;
    }
/*
 * @param string $varlist: commaseperated list of varnames to move into global space
 * @return bool:  false if one of the vars already exists in global space (error added to msgQueue)
 */
    function vars2globals_wrapper($varlist)
    {
        $retval = true;
        if( $varlist != '')
        {
            $vars = \explode(',', $varlist);
            foreach( $vars as $var)
            {
                if( isset($GLOBALS[$var]) ){
                    ErrorLog::write( 'variabe $'.$var.' already defined in global space!!',__FILE__, __FUNCTION__, __LINE__);
                    $retval = false;
                }else {
                    global $$var;
                }
            }
        }
        return $retval;
    }

function charset_decode_utf_8($string)
{
        /* Only do the slow convert if there are 8-bit characters */
        if ( !preg_match("/[\200-\237]/", $string) && !preg_match("/[\241-\377]/", $string) ){
           return $string;
        }
         $aMatches = [];
        // decode three byte unicode characters
          $string = preg_replace_callback("/([\340-\357])([\200-\277])([\200-\277])/",
                    function ($aMatches) {
                        return '&#'.((ord($aMatches[1])-224)*4096+(ord($aMatches[2])-128)*64+(ord($aMatches[3])-128));
                      },
                    $string);

         $aMatches = [];
        // decode two byte unicode characters
          $string = preg_replace_callback("/([\300-\337])([\200-\277])/",
                    function ($matches) {
                        return '&#'.((ord($aMatches[1])-192)*64+(ord($aMatches[2])-128));
                    },
                    $string);

        return $string;
    }
/*
 * filter directory traversal more thoroughly, thanks to hal 9000
 * @param string $dir: directory relative to MEDIA_DIRECTORY
 * @param bool $with_media_dir: true when to include MEDIA_DIRECTORY
 * @return: false if directory traversal detected, real path if not
 */
    function check_media_path($directory, $with_media_dir = true)
    {
        $bRetval    = false;
        $directory  = \utf8_decode($directory).'';
        $sMediaPath = str_replace('\\','/',WB_PATH.MEDIA_DIRECTORY).'';
        $md         = ($with_media_dir ? $sMediaPath : '').'';
        $required   = str_replace('\\','/',\realpath($sMediaPath)).'/';
        $dir =  str_replace('\\','/',\realpath($md).'/'.$directory).'';
        $sRetval = (\strstr($dir, $required, false) || empty($dir));
        return $sRetval;
    }

/*
urlencode function and rawurlencode are mostly based on RFC 1738.
However, since 2005 the current RFC in use for URIs standard is RFC 3986.
Here is a function to encode URLs according to RFC 3986.
*/
    if (!\is_callable('url_encode')){}
        function url_encode($string) {
            $string = \html_entity_decode($string,ENT_QUOTES,'UTF-8');
            $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
            $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
            return \str_replace($entities,$replacements, \rawurlencode($string));
        }



/**
 * Returns a list of defined constants that match the category and prefix filter criteria.
 * @param string $sPrefix    an empty string ignores the prefix filter
 * @param string $sCategory  * returns all constants regardless of categories
 * @return array
 */
    function getConstants(string $sPrefix = '', string $sCategory = '*'): array
    {
        $aConstants = [];
        $bCategorize = $sCategory !== '*';
        $aTmp = \get_defined_constants($bCategorize);
        if ($bCategorize && isset($aTmp[$sCategory])) {
            $aTmp = $aTmp[$sCategory];
        } elseif ($bCategorize) {
            goto end;
        }
        if ($sPrefix !== '') {
            $iPrefixLength = \mb_strlen($sPrefix);
            \array_walk(
                $aTmp,
                function (& $iValue, $sKey) use (& $aConstants, $sPrefix, $iPrefixLength) {
                    if (\mb_substr($sKey, 0, $iPrefixLength) === $sPrefix) {
                        $aConstants[$sKey] = $iValue;
                    }
                }
            );
        } else {
            $aConstants = $aTmp;
        }
        end:
        return $aConstants;
    }

    /**
    *
    * Convert a camel case string to underscores (eg: camelCase becomes camel_case)
    *
    * @param    string  The string to convert
    * @return   string
    *
    */
    function camelCase2Uscore( $sString )
    {
        return mb_camelcase_to_underscore($sString);
    }

    /**
    * Convert strings with underscores into CamelCase
    *
    * @param    string  $sString    The string to convert
    * @param    bool    $bUpperCamelCase    camelCase or CamelCase
    * @return   string  The converted string
    *
    */
    function uScore2camelCase( $sString, $bUpperCamelCase = false)
    {
        return mb_underscore_to_camelcase($sString, $bUpperCamelCase);
    }
/**
 * read a given info.php file and returns an array of all defined variables
 * complete 'function' entry and add 'type' entry
 * @param string $sInfoFile
 * @return array
 */
    function readInfoVars(string $sInfoFile): array
    {
        if (\is_readable($sInfoFile)) {
            include $sInfoFile;
            $aTmp = \get_defined_vars();
            \array_walk(
                $aTmp,
                function (& $value, $key) use (& $aRetval) {
                    $a = \explode('_', $key, 2);
                    if (\in_array($a[0], ['module', 'language', 'template', 'block', 'menu'])) {
                        $aRetval[($a[1] ?? $a[0])] = $value;
                        if (\in_array($a[0], ['module', 'language', 'template'])) {
                            $aRetval['type'] = $a[0];
                        }
                    }
                }
            );
            $aRetval['function'] = ($aRetval['function'] ?? 'language');
        }
        return ($aRetval ?? []);
    }

    function resolveTemplateImagesPath()
    {
        $sRetVal = null;
        $oReg = WbAdaptor::getInstance();
        $bIsFrontend    = $oReg->getApplication()->isFrontend();
        $bIsBackend     = $oReg->getApplication()->isBackend();
        $sTemplate      = ($bIsFrontend ? $oReg->Template : ($bIsBackend ? $oReg->DefaultTheme : null));
        $sImagesFolder  = 'templates/'.($sTemplate ?? 'DefaultTemplate').'/images/';

        if (! empty($sTemplate)){
            $sRetVal           = $oReg->AppUrl.$sImagesFolder;
            $aTemplates        = ['templates',$oReg->Theme,$oReg->Template];
            foreach ($aTemplates as $iIndex => $sKey){
                $sImagesFolder = 'templates/'.($sTemplate ?? 'DefaultTemplate').'/imagesUser/';
                switch ($sKey):
                    case 'templates':
                        if (is_dir($oReg->AppPath.'templates/imagesUser/')){
                            $sRetVal = $oReg->AppUrl.'templates/imagesUser/';
                            break 2;
                        }
                        break;
                    case $oReg->Template:
                    case $oReg->Theme:
                        if (is_readable($oReg->AppPath.$sImagesFolder) && is_dir($oReg->AppPath.$sImagesFolder)){
                            $sRetVal = $oReg->AppUrl.$sImagesFolder;
                            break 2;
                        }
                        break;
                    default:
                endswitch;
            }//end foreach $aTemplates
        }

        return ($sRetVal);
    }

/**
 * When session.use_strict_mode is set to 1 or true, you cannot use session_id($sid)
 * to set the session id for the current session
 *
 * ini_set('session.use_strict_mode', 1);
 * $sid = md5('wuxiancheng.cn');
 * session_id($sid);
 * session_start();
 *
 */
    function session_valid_id($d)
    {
        return preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $id) > 0;
    }

    function callInstaller()
    {
        global $sAppPath;
    /*
     * Remark:  HTTP/1.1 requires a qualified URI incl. the scheme, name
     * of the host and absolute path as the argument of location. Some, but
     * not all clients will accept relative URIs also.
     */
        $_SERVER['REQUEST_SCHEME'] = ($_SERVER['REQUEST_SCHEME'] ?? 'http');
        $host       = $_SERVER['HTTP_HOST'];
        $sDocRoot   = ($_SERVER["PATH_TRANSLATED"] ?? $_SERVER["DOCUMENT_ROOT"]);
        $uri        = ((basename($sAppPath)==basename($sDocRoot))  ? '' : '/'.basename($sAppPath));//
        $file       = '/install/index.php';
        $target_url = $_SERVER['REQUEST_SCHEME'].'://'.$host.$uri.''.$file;
        $sResponse  = $_SERVER['SERVER_PROTOCOL'].' 307 Temporary Redirect';
        \header($sResponse);
        \header('Location: '.$target_url);
        exit;    // make sure that subsequent code will not be executed
    }

    function callUpgrade(WbAdaptor $oReg):void
    {
        $oApp = $oReg->getApplication();
        $sUpgradeFile = 'install/upgrade-script.php';
// check if it is neccessary to start the uograde-script
        if (((int)$oApp->getUserId()===1) && \is_readable($oReg->AppPath.$sUpgradeFile))
        {
            // check if it is neccessary to start the uograde-script
            $oldVersion = '';
            $newVersion = '';
            //$oldVersion  = \trim(''.WB_VERSION.'+'.WB_REVISION.'+'.(\defined('WB_SP') ? WB_SP : ''), '+');
            $AppRevision  = ($oReg->AppRevision ?? '999');
            $oldVersion   = \trim($oReg->AppVersion.'.'.$oReg->AppRevision.'+'.($oReg->AppServicePack ?? ''), '+');
            $newVersion   = \trim(''.$oReg->Version.'.'.$oReg->Revision.'+'.($oReg->Sp ?? ''), '+');
            $sUpdateFile  = $oReg->AcpPath.'interface/update';
            $bMustUpgrade = false;
            if ((bool)is_readable($sUpdateFile)){
                include $sUpdateFile.'';
                $newVersion  = \trim(''.$admin_version.'.'.$admin_revision, '+');
                $bMustUpgrade = true;
            } elseif ((bool)is_readable($sUpdateFile.'.fixed')){
                include $sUpdateFile.'.fixed';
                $newVersion  = \trim(''.$admin_version.'.'.$admin_revision, '+');
            }

            $admin_update = ($oReg->PatchUpdate ?? 0);
            if (($admin_update < $admin_timestamp) || $bMustUpgrade) {
                $sLink = $oReg->AppUrl.$sUpgradeFile;
                if (!\headers_sent()) {
                    \header('Location: '.$sLink);
                    exit;
                } else {
                    // TODO output by template
                    echo nl2br(sprintf('<p style="text-align:center;margin-top:26px;"> %s'."\n",'The <b>upgrade script</b> could not start automatically'));
                    echo nl2br(sprintf('%s <a style="font-weight:bold;" ','Please click'));
                    echo nl2br(sprintf('href="%s">%s</a> %s</p>',$sLink,'on this link',' to start the script!' ));
                    $oApp->print_footer();
                    exit;
                }
            }
        }
    } // end function callUpgrade

} // functions loaded
// -------------------------------------------------------------------------------------
/*
$getString ='MENU_SETTINGS';
$output = uScore2camelCase($getString, true);

print '<pre  class="mod-pre rounded">function <span>'.__FUNCTION__.'( '.''.' );</span>  filename: <span>'.\basename(__FILE__).'</span>  line: '.__LINE__.' -> <br />';
\print_r( $output ); print '</pre>'.PHP_EOL; \flush (); //  ob_flush();;sleep(10); die();
*/
