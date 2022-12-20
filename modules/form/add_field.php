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
 * @category        addons
 * @package         form
 * @subpackage      add_field
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.13.x
 * @requirements    PHP 7.4.x and higher
 * @version         0.0.1
 * @revision        $Id: $
 * @since           File available since 12.11.2017
 * @deprecated      no / since 0000/00/00
 * @description     xxx
 *
 */



// Include config file
if (!defined('SYSTEM_RUN')) {require( (\dirname(\dirname((__DIR__)))).'/config.php');}

try {
// Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $sBacklink = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
    if (!$admin->checkFTAN( $_SERVER["REQUEST_METHOD"] ))
    {
//    $admin->print_header();
        $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], $sBacklink);
    }
//$aFtan = $admin->getFTAN('');

//  Get new order
    $order = new \order(TABLE_PREFIX.'mod_form_fields', 'position', 'field_id', 'section_id');
    $position = $order->get_new($section_id);
    $field_id = 0;
    $ModifyLink = WB_URL.'/modules/form/modify_field.php?page_id='.$page_id.'&section_id='.$section_id.'&field_id=';
//  Insert new row into database
    $sql = '
    INSERT INTO `'.TABLE_PREFIX.'mod_form_fields` SET
        `section_id` = '.$database->escapeString($section_id).',
        `page_id` = '.$database->escapeString($page_id).',
        `layout`=\''.$database->escapeString($sLayout).'\',
        `position` = '.$database->escapeString($position).',
        `title` = \'\',
        `type` = \'\',
        `required` = 0,
        `value` = \'\',
        `extra` = \'\'
        ';
    if(!$database->query($sql)) {
        $admin->print_error($database->get_error(), $sBacklink );
    }
    $field_id = ($database->getLastInsertId());
    $admin->print_success($TEXT['SUCCESS'], $ModifyLink.$admin->getIDKEY($field_id));
} catch(\ErrorMsgException $e) {
    $admin->print_error($database->get_error(), $ModifyLink.$admin->getIDKEY($field_id));
}

// Print admin footer
$admin->print_footer();
