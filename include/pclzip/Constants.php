<?php

/*
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
 * Constants.php
 *
 * @category     Vendor
 * @package      Vendor_PclZip
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @revision     $Revision: 68 $
 * @link         $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/include/pclzip/Constants.php $
 * @lastmodified $Date: 2018-09-17 18:26:08 +0200 (Mo, 17. Sep 2018) $
 * @since        File available since 01.01.2016
 * @deprecated   This file is deprecated from beginning
 * @description compatibility layer
 */
namespace vendor\pclzip;

    // ----- Constants
// (all set deprecated!!)

    if (!defined('PCLZIP_READ_BLOCK_SIZE')) { define( 'PCLZIP_READ_BLOCK_SIZE', 2048 ); }
    // ----- File list separator
    // In version 1.x of PclZip, the separator for file list is a space
    // (which is not a very smart choice, specifically for windows paths !).
    // A better separator should be a comma (,). This constant gives you the
    // abilty to change that.
    // However notice that changing this value, may have impact on existing
    // scripts, using space separated filenames.
    // Recommanded values for compatibility with older versions :
    //define( 'PCLZIP_SEPARATOR', ' ' );
    // Recommanded values for smart separation of filenames.
    if (!defined('PCLZIP_SEPARATOR')) { define( 'PCLZIP_SEPARATOR', ',' ); }
    // ----- Error configuration
    // 0 : PclZip Class integrated error handling
    // 1 : PclError external library error handling. By enabling this
    //     you must ensure that you have included PclError library.
    // [2,...] : reserved for futur use
    if (!defined('PCLZIP_ERROR_EXTERNAL')) { define( 'PCLZIP_ERROR_EXTERNAL', 0 ); }
    // ----- Optional static temporary directory
    //       By default temporary files are generated in the script current
    //       path.
    //       If defined :
    //       - MUST BE terminated by a '/'.
    //       - MUST be a valid, already created directory
    //       Samples :
    // define( 'PCLZIP_TEMPORARY_DIR', '/temp/' );
    // define( 'PCLZIP_TEMPORARY_DIR', 'C:/Temp/' );
    if (!defined('PCLZIP_TEMPORARY_DIR')) { define( 'PCLZIP_TEMPORARY_DIR', '' ); }
    // ----- Optional threshold ratio for use of temporary files
    //       Pclzip sense the size of the file to add/extract and decide to
    //       use or not temporary file. The algorythm is looking for
    //       memory_limit of PHP and apply a ratio.
    //       threshold = memory_limit * ratio.
    //       Recommended values are under 0.5. Default 0.47.
    //       Samples :
    // define( 'PCLZIP_TEMPORARY_FILE_RATIO', 0.5 );
    if (!defined('PCLZIP_TEMPORARY_FILE_RATIO')) { define( 'PCLZIP_TEMPORARY_FILE_RATIO', 0.47 ); }
// ----- Error codes -------------------------------------------------------------------
//    -1 : Unable to open file in binary write mode
//    -2 : Unable to open file in binary read mode
//    -3 : Invalid parameters
//    -4 : File does not exist
//    -5 : Filename is too long (max. 255)
//    -6 : Not a valid zip file
//    -7 : Invalid extracted file size
//    -8 : Unable to create directory
//    -9 : Invalid archive extension
//   -10 : Invalid archive format
//   -11 : Unable to delete file (unlink)
//   -12 : Unable to rename file (rename)
//   -13 : Invalid header checksum
//   -14 : Invalid archive size
    define( 'PCLZIP_ERR_USER_ABORTED', 2 );
    define( 'PCLZIP_ERR_NO_ERROR', 0 );
    define( 'PCLZIP_ERR_WRITE_OPEN_FAIL', -1 );
    define( 'PCLZIP_ERR_READ_OPEN_FAIL', -2 );
    define( 'PCLZIP_ERR_INVALID_PARAMETER', -3 );
    define( 'PCLZIP_ERR_MISSING_FILE', -4 );
    define( 'PCLZIP_ERR_FILENAME_TOO_LONG', -5 );
    define( 'PCLZIP_ERR_INVALID_ZIP', -6 );
    define( 'PCLZIP_ERR_BAD_EXTRACTED_FILE', -7 );
    define( 'PCLZIP_ERR_DIR_CREATE_FAIL', -8 );
    define( 'PCLZIP_ERR_BAD_EXTENSION', -9 );
    define( 'PCLZIP_ERR_BAD_FORMAT', -10 );
    define( 'PCLZIP_ERR_DELETE_FILE_FAIL', -11 );
    define( 'PCLZIP_ERR_RENAME_FILE_FAIL', -12 );
    define( 'PCLZIP_ERR_BAD_CHECKSUM', -13 );
    define( 'PCLZIP_ERR_INVALID_ARCHIVE_ZIP', -14 );
    define( 'PCLZIP_ERR_MISSING_OPTION_VALUE', -15 );
    define( 'PCLZIP_ERR_INVALID_OPTION_VALUE', -16 );
    define( 'PCLZIP_ERR_ALREADY_A_DIRECTORY', -17 );
    define( 'PCLZIP_ERR_UNSUPPORTED_COMPRESSION', -18 );
    define( 'PCLZIP_ERR_UNSUPPORTED_ENCRYPTION', -19 );
    define( 'PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE', -20 );
    define( 'PCLZIP_ERR_DIRECTORY_RESTRICTION', -21 );
    // ----- Options values ----------------------------------------------------------------
    define( 'PCLZIP_OPT_PATH', 77001 );
    define( 'PCLZIP_OPT_ADD_PATH', 77002 );
    define( 'PCLZIP_OPT_REMOVE_PATH', 77003 );
    define( 'PCLZIP_OPT_REMOVE_ALL_PATH', 77004 );
    define( 'PCLZIP_OPT_SET_CHMOD', 77005 );
    define( 'PCLZIP_OPT_EXTRACT_AS_STRING', 77006 );
    define( 'PCLZIP_OPT_NO_COMPRESSION', 77007 );
    define( 'PCLZIP_OPT_BY_NAME', 77008 );
    define( 'PCLZIP_OPT_BY_INDEX', 77009 );
    define( 'PCLZIP_OPT_BY_EREG', 77010 );
    define( 'PCLZIP_OPT_BY_PREG', 77011 );
    define( 'PCLZIP_OPT_COMMENT', 77012 );
    define( 'PCLZIP_OPT_ADD_COMMENT', 77013 );
    define( 'PCLZIP_OPT_PREPEND_COMMENT', 77014 );
    define( 'PCLZIP_OPT_EXTRACT_IN_OUTPUT', 77015 );
    define( 'PCLZIP_OPT_REPLACE_NEWER', 77016 );
    define( 'PCLZIP_OPT_STOP_ON_ERROR', 77017 );
    // Having big trouble with crypt. Need to multiply 2 long int
    // which is not correctly supported by PHP ...
    //define( 'PCLZIP_OPT_CRYPT', 77018 );
    define( 'PCLZIP_OPT_EXTRACT_DIR_RESTRICTION', 77019 );
    define( 'PCLZIP_OPT_TEMP_FILE_THRESHOLD', 77020 );
    define( 'PCLZIP_OPT_ADD_TEMP_FILE_THRESHOLD', 77020 ); // alias
    define( 'PCLZIP_OPT_TEMP_FILE_ON', 77021 );
    define( 'PCLZIP_OPT_ADD_TEMP_FILE_ON', 77021 ); // alias
    define( 'PCLZIP_OPT_TEMP_FILE_OFF', 77022 );
    define( 'PCLZIP_OPT_ADD_TEMP_FILE_OFF', 77022 ); // alias
    // ----- File description attributes ---------------------------------------------------
    define( 'PCLZIP_ATT_FILE_NAME', 79001 );
    define( 'PCLZIP_ATT_FILE_NEW_SHORT_NAME', 79002 );
    define( 'PCLZIP_ATT_FILE_NEW_FULL_NAME', 79003 );
    define( 'PCLZIP_ATT_FILE_MTIME', 79004 );
    define( 'PCLZIP_ATT_FILE_CONTENT', 79005 );
    define( 'PCLZIP_ATT_FILE_COMMENT', 79006 );
    // ----- Call backs values -------------------------------------------------------------
    define( 'PCLZIP_CB_PRE_EXTRACT', 78001 );
    define( 'PCLZIP_CB_POST_EXTRACT', 78002 );
    define( 'PCLZIP_CB_PRE_ADD', 78003 );
    define( 'PCLZIP_CB_POST_ADD', 78004 );
  /* For futur use -----------------------------------------------------------------------
  define( 'PCLZIP_CB_PRE_LIST', 78005 );
  define( 'PCLZIP_CB_POST_LIST', 78006 );
  define( 'PCLZIP_CB_PRE_DELETE', 78007 );
  define( 'PCLZIP_CB_POST_DELETE', 78008 );
  */

// compatibolity layer for utility functions
// (all set deprecated!!)
    function PclZipUtilTranslateWinPath($p_path, $p_remove_disk_letter=true) {
        return PclZip::UtilTranslateWinPath($p_path, $p_remove_disk_letter);
    }

    function PclZipUtilOptionText($p_option) {
        return PclZip::UtilOptionText($p_option);
    }

    function PclZipUtilRename($p_src, $p_dest) {
        return PclZip::UtilRename($p_src, $p_dest);
    }

    function PclZipUtilCopyBlock($p_src, $p_dest, $p_size, $p_mode=0) {
        return PclZip::UtilCopyBlock($p_src, $p_dest, $p_size, $p_mode);
    }

    function PclZipUtilPathInclusion($p_dir, $p_path) {
        return PclZip::UtilPathInclusion($p_dir, $p_path);
    }

    function PclZipUtilPathReduction($p_dir) {
        return PclZip::UtilPathReduction($p_dir);
    }


// end of file
