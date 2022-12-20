<?php
/**
 *
 * @category        admin
 * @package         admintools
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: thumb.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/media/thumb.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 */
use bin\media\inc\PhpThumbFactory;

if(!defined('SYSTEM_RUN'))
{
    $config_file = realpath('../../config.php');
    if(file_exists($config_file) && !defined('WB_URL'))
    {
        require($config_file);
    }
}
$modulePath = dirname(__FILE__);

/*
// Get image
    $requestMethod = '_'.strtoupper($_SERVER['REQUEST_METHOD']);
    $image = (isset(${$requestMethod}['img']) ? ${$requestMethod}['img'] : '');
//    if(!class_exists('PhpThumbFactory', false)){ include(WB_PATH.'/framework/media/inc/PhpThumbFactory.php'); }
//    require_once($modulePath.'/inc/ThumbLib.inc.php');
print '<pre style="text-align: left;"><strong>function '.__FUNCTION__.'( '.''.' );</strong>  basename: '.basename(__FILE__).'  line: '.__LINE__.' -> <br />';
print_r( $_GET ); print '</pre>';  die(); // flush ();sleep(10);
*/

if (isset($_GET['img']) && isset($_GET['t'])) {
    $image = addslashes($_GET['img']);
    $type = intval($_GET['t']);
    $sFilename = WB_PATH . MEDIA_DIRECTORY.''.$image;
    $thumb = PhpThumbFactory::create($sFilename);
    if ($type == 1) {
        $thumb->adaptiveResize(20,20);
//        $thumb->resize(30,30);
//        $thumb->cropFromCenter(80,50);
//         $thumb->resizePercent(40);
    } else {
        $thumb->Resize(300,300);
//         $thumb->resizePercent(25);
//        $thumb->cropFromCenter(80,50);
    }
    $thumb->show();
}
