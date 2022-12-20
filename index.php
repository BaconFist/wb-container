<?php

/**
 *
 * @category        frontend
 * @package         page
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.x
 * @requirements    PHP 7.4 and higher
 * @version         $Id: index.php 349 2019-05-13 06:00:25Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/index.php $
 * @lastmodified    $Date: 2019-05-13 08:00:25 +0200 (Mo, 13. Mai 2019) $
 *
 */

use bin\{WbAdaptor,wb,SecureTokens,Sanitize,requester};
use bin\Exceptions\ErrorHandler;

    $sAddonFile   = str_replace('\\','/',__FILE__).'/';
    $sAddonPath   = \dirname($sAddonFile).'/';
    $sModulesPath = ($sAddonPath).'';
    $sModuleDir   = ''; //basename($sModulesPath)
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.$sModuleDir.'/'.$sAddonName;
    $sPattern = "/^(.*?\/)".$sModuleDir."\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sAddonPath, 1 );

    $starttime = array_sum(explode(" ", microtime()));
 // Include config file
    if (!defined('SYSTEM_RUN')) {
        $sStartupFile = $sAppPath.'config.php';
        if (is_readable($sStartupFile) && (filesize($sStartupFile)>64)) {
            require($sStartupFile);
        }
    }
// Check if the config file has been set-up
    if (!defined('TABLE_PREFIX')){
        if (!function_exists('callInstaller')){
            $sRequestFromInitialize = true;
            require $sAppPath.'framework/functions.php';
        }
        callInstaller();
    } else {
        // Create new frontend object
        if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
        $oReg     = WbAdaptor::getInstance();
        $oRequest = $oReg->getRequester();
    }

/* */
// activate frontend Output_Filter (index.php)
    if (\is_readable($oReg->AppPath . 'modules/output_filter/index.php')) {
        if (!\is_callable('executeFrontendOutputFilter')) {
            include $oReg->AppPath . 'modules/output_filter/index.php';
        }
    } else {
        throw new \RuntimeException('missing mandatory global Output_Filter!');
    }

// Figure out which page to display
// Stop processing if intro page was shown
    $wb->page_select() || die();

// Collect info about the currently viewed page and check permissions
    $wb->get_page_details();

// Collect general website settings
    $wb->get_website_settings();

// Load functions available to templates, modules and code sections
// also, set some aliases for backward compatibility
    if (!\is_callable('register_frontend_modfiles')) {
        require($oReg->AppPath . 'framework/frontend.functions.php');
    }

//Get pagecontent in buffer for Droplets and/or Filter operations
    \ob_start();
    require($oReg->AppPath . 'templates/' . TEMPLATE . '/index.php');
    $output = \ob_get_contents();
    if (\ob_get_length() > 0) {
        \ob_end_clean();
    }
// execute frontend output filters
    if (\is_readable($oReg->AppPath . 'modules/output_filter/index.php')) {
        if (!\is_callable('executeFrontendOutputFilter')) {
            include($oReg->AppPath . 'modules/output_filter/index.php');
        } else {
            $output = executeFrontendOutputFilter($output);
        }
    }
// now send complete page to the browser
    echo $output;
// end of wb-script
    exit;
