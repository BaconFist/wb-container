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
 *
 * @category        include
 * @package         jscalendar
 * @subpackage      jscalendar-functions
 * @author          Dietmar Wöllbrink
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: jscalendar-functions.php 68 2018-09-17 16:26:08Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/include/jscalendar/jscalendar-functions.php $
 * @lastmodified    $Date: 2018-09-17 18:26:08 +0200 (Mo, 17. Sep 2018) $
 *
 */
// $Id: jscalendar-functions.php 68 2018-09-17 16:26:08Z Luisehahne $
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!defined('SYSTEM_RUN')) {
  die('Cannot access '.basename(__DIR__).'/'.basename(__FILE__).' directly');
/* -------------------------------------------------------- */
} else {
    // convert string from jscalendar to timestamp.
    // converts dd.mm.yyyy and mm/dd/yyyy, with or without time.
    // strtotime() may fails with e.g. "dd.mm.yyyy" and PHP4
    function jscalendar_to_timestamp($str, $offset='') {
        $str = trim($str);
        if ($str == '0' || $str == ''){return('0');}
        if ($offset == '0'){$offset = '';}
        // convert to yyyy-mm-dd
        // "dd.mm.yyyy"?
        if(preg_match('/^\d{1,2}\.\d{1,2}\.\d{2}(\d{2})?/', $str)) {
            $str = preg_replace('/^(\d{1,2})\.(\d{1,2})\.(\d{2}(\d{2})?)/', '$3-$2-$1', $str);
        }
        // "mm/dd/yyyy"?
        if(preg_match('#^\d{1,2}/\d{1,2}/(\d{2}(\d{2})?)#', $str)) {
            $str = preg_replace('#^(\d{1,2})/(\d{1,2})/(\d{2}(\d{2})?)#', '$3-$1-$2', $str);
        }
        // use strtotime()
      if($offset!=''){
          return(strtotime($str, $offset)-TIMEZONE);
      } else{
          return(strtotime($str)-TIMEZONE);
      }
    }
}

