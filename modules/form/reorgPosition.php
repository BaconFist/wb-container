<?php
/**
 *
 * @category        modules
 * @package         form
 * @subpackage      reorgPosition
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.x
 * @requirements    PHP 5.6 and higher
 * @version         $Id: reorgPosition.php 284 2019-03-22 08:13:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/reorgPosition.php $
 * @lastmodified    $Date: 2019-03-22 09:13:16 +0100 (Fr, 22. Mrz 2019) $
 *
 */

if ( !defined( 'WB_PATH' ) ){ require( dirname(dirname((__DIR__))).'/config.php' ); }
require(WB_PATH.'/modules/admin.php');
$requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
$aRequestVars  = (isset(${'_'.$requestMethod}) ? ${'_'.$requestMethod} : $_REQUEST);

$backlink = ADMIN_URL.'/pages/modify.php?page_id='.(int)$page_id;
if(!$admin->checkFTAN($requestMethod)) {
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}
if ( !class_exists('order', false) ) { require(WB_PATH.'/framework/class.order.php'); }
$form   = new order(TABLE_PREFIX.'mod_form_fields', 'position', 'field_id', 'section_id');
$form->clean( $section_id );

$admin->print_success($TEXT['SUCCESS'], $backlink );

