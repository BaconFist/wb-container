<?php
/**
 *
 * @category        admin
 * @package         admintools
 * @author          Ryan Djurovich, WebsiteBaker Project
 * @author          Werner v.d. Decken
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: tool.php 211 2019-01-29 22:32:17Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/admintools/tool.php $
 * @lastmodified    $Date: 2019-01-29 23:32:17 +0100 (Di, 29. Jan 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}

// TODO setting $sAppPath
    //if (!\defined('SYSTEM_RUN')) {require( (\dirname(\dirname((__DIR__)))).'/config.php');}
//    if (!\function_exists('make_dir')) {require (WB_PATH.'/framework/functions.php');}

    $oReg = WbAdaptor::getInstance();
    $database = $oDb = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans = $oReg->getTranslate();

    $toolDir = $oRequest->getParam('tool',\FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $doSave  = ($oRequest->getParam('action',\FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
    if (!is_null($toolDir) && \preg_match('/^[a-z][a-z_\-0-9]{2,}$/i', $toolDir, $matches, PREG_OFFSET_CAPTURE)) {
    // Check if tool is installed
        $sql = '
        SELECT `name` FROM `'.TABLE_PREFIX.'addons`
        WHERE `type`=\'module\' AND `function`=\'tool\'
          AND `directory`=\''.$database->escapeString($toolDir).'\'
        ';

        if (($toolName = $database->get_one($sql))) {
        // create admin-object and print header if FTAN is NOT supported AND function 'save' is requested
            $admin_header = !(is_file(WB_PATH.'/modules/'.$toolDir.'/FTAN_SUPPORTED') && $doSave);
            $admin = new \admin('admintools', 'admintools', $admin_header );
            $oApp = $oReg->getApplication();
            $aRequestVars = $admin->getRequestVars();

            if (!$doSave) {
            // show title if not function 'save' is requested
                print '<h4 style="margin:0!important;font-size:1.25em!important;"><a href="'.ADMIN_URL.'/admintools/index.php" '.
                      'title="'.$HEADING['ADMINISTRATION_TOOLS'].'">'.
                      $HEADING['ADMINISTRATION_TOOLS'].'</a>'.
                      '&nbsp;&raquo;&nbsp;'.$toolName.'</h4>'."\n";
            }
            // include modules tool.php
            $sAbsToolPath = WB_PATH.'/modules/'.$toolDir.'/tool.php';
            if (\is_readable($sAbsToolPath)){
              require($sAbsToolPath);
            }
            $admin->print_footer();
        }
        else
        {
        // no installed module found, jump to index.php of admintools
            header('location: '.ADMIN_URL.'/admintools/index.php');
//            throw new \Exception (sprintf('Installed Module [%s] not found',$toolName));
            exit(0);
        }
    }
    else
    {
    // invalid module name requested, jump to index.php of admintools
//            throw new \Exception (sprintf('invalid module name [%s] requested! Check querystring parameter', $toolDir));
        header('location: '.ADMIN_URL.'/admintools/index.php');
        exit(0);
    }
