<?php
/**
 *
 * @category        admin
 * @package         media
 * @author          Ryan Djurovich, WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: parameters.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/media/parameters.php $
 * @lastmodified    $Date: 2019-03-17 07:05:56 +0100 (So, 17. Mrz 2019) $
 *
 */

// Must include code to stop this file being access directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

    function __unserialize($sObject) {  // found in php manual :-)
        $aRetval = [];
        if ($sObject){
            $_ret = preg_replace_callback(
                            '!s:(\d+):"(.*?)";!',
                            function($matches) {return 's:'.strlen($matches[2]).':"'.$matches[2].'";';},
                            $sObject
                     );
            if ($_ret) {$aRetval = unserialize($_ret);}
        }
        return $aRetval;
    }

/*
    $pathsettings = [];
    $sqlMedia = 'SELECT `value` FROM `'.TABLE_PREFIX.'settings` '
              . 'WHERE `name` = \'mediasettings\' '
              . '';
    if (!($sSettings = $database->get_one($sqlMedia))){
    } else {
        $pathsettings = __unserialize($sSettings);
    }
*/
