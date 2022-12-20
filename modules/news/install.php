<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: install.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/install.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; }

    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
    $sAddonName = basename($sAddonPath);
    $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
    $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
    $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
    if (version_compare($sWbVersion, $sModulePlatform, '<')){
        $msg[] = $sErrorMsg = sprintf('It is not possible to install from WebsiteBaker Versions before %s',$sModulePlatform);
        if ($globalStarted){
            echo $sErrorMsg;
        }else{
            throw new \Exception ($sErrorMsg);
        }
    } else {
        // create tables from sql dump file
        if (is_readable($sAddonPath.'/install-struct.sql.php')) {
//            $oReg->Db->addReplacement('XTABLE_ENGINE','ENGINE=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
//            $oReg->Db->addReplacement('XFIELD_COLLATION','COLLATE utf8mb4_unicode_ci');
            $database->SqlImport($sAddonPath.'/install-struct.sql.php', TABLE_PREFIX, 'install' );
        }
    }
    if (is_readable(__DIR__.'/presets/default_layout.inc.php')){
        require (__DIR__.'/presets/default_layout.inc.php');
        $aDefaultLayouts = ['default_layout','div_layout','div_new_layout'];
        $aLayouts = \glob($sAddonPath.'/presets/*.inc.php');
        $sPattern = '/^.*?([^\/]*?)\.[^\.]*\.[^\.]*$/is';
        foreach($aLayouts as $sLayoutFilename){
            $sLayout = preg_replace($sPattern,'$1',$sLayoutFilename);
            if (is_readable($sLayoutFilename)){
                require ($sLayoutFilename);
                $sql  = 'INSERT INTO `'.TABLE_PREFIX.'mod_news_layouts` SET '.PHP_EOL
                      . '`layout` = \''.$oReg->Db->escapeString($sLayout).'\', '.PHP_EOL
                      . '`header`=\''.$oReg->Db->escapeString($header).'\', '.PHP_EOL
                      . '`post_loop`=\''.$oReg->Db->escapeString($post_loop).'\', '.PHP_EOL
                      . '`footer`=\''.$oReg->Db->escapeString($footer).'\', '.PHP_EOL
                      . '`post_header`=\''.$oReg->Db->escapeString($post_header).'\', '.PHP_EOL
                      . '`post_footer`=\''.$oReg->Db->escapeString($post_footer).'\', '.PHP_EOL
                      . '`comments_header`=\''.$oReg->Db->escapeString($comments_header).'\', '.PHP_EOL
                      . '`comments_loop`=\''.$oReg->Db->escapeString($comments_loop).'\', '.PHP_EOL
                      . '`comments_footer`=\''.$oReg->Db->escapeString($comments_footer).'\', '.PHP_EOL
                      . '`comments_page`=\''.$oReg->Db->escapeString($comments_page).'\' '.PHP_EOL;
                if (!$oReg->Db->query($sql)){
                  $msg[] = $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
                }
            }
        } // end foreach import layout files to db
    }
 // set default layout and id to settings table
    $sSql = 'UPDATE '.TABLE_PREFIX.'mod_news_settings SET '
          . '`layout` = \''.$oReg->Db->escapeString('default_layout').'\', '
          . '`layout_id` = 1 '
          . 'WHERE `layout` = \'\' '
          .   'AND `layout_id` = 0 ';
    if (!$oReg->Db->query($sSql)){
        $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
    } else {
        $msg[] = (sprintf("[%05d] update sets default layouts to settings table \n",__LINE__));
        $sInstallStruct = $sAddonPath.'/delete-struct.sql.php';
//echo nl2br(sprintf("[%05d] load delete-struct %s \n",__LINE__,$sInstallStruct));
        if (!$oReg->Db->SqlImport($sInstallStruct, TABLE_PREFIX, 'upgrade' )){
            $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
        }
   }
/* **** END INSTALL ********************************************************* */
