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
 * @category        modules
 * @package         droplets
 * @subpackage      ToggleStatus
 * @author          Dietmar Wöllbrink
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.4
 * @requirements    PHP 5.4 and higher
 * @version         $Id: ToggleStatus.php 92 2018-09-20 18:04:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/droplets/cmd/ToggleStatus.php $
 * @lastmodified    $Date: 2018-09-20 20:04:03 +0200 (Do, 20 Sep 2018) $
 *
 */
 /* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;}
/* -------------------------------------------------------- */
/*
*/
if ($droplet_id === false) {
    $oApp->print_error('TOGGLE_DROPLET_IDKEY::'.$MESSAGE['GENERIC_SECURITY_ACCESS'], $ToolUrl);
    exit();
}
    $sql  = 'SELECT `active` FROM `'.TABLE_PREFIX.'mod_droplets` ';
    $sqlWhere  = 'WHERE `id` = '.(int)$droplet_id;
    $val = !(bool)$oDb->get_one($sql.$sqlWhere);
    $sql = 'UPDATE `'.TABLE_PREFIX.'mod_droplets` SET '
//         .  '`active`='.$val.' ';
         .  '`active`='.($val ? true : 0).' ';
   if (!$oDb->query($sql.$sqlWhere)){
        \bin\helpers\msgQueue::add($sql.$sqlWhere.'<br />TOGGLE_DROPLET::'.$oDb->get_error() );
   } else {
//            \bin\helpers\msgQueue::add('TOGGLE_DROPLET::'.$oTrans->TEXT_SUCCESS, true );
   }

