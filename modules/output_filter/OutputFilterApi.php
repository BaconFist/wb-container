<?php
/**
 *  Copyright (C) 2013 Werner v.d. Decken <wkl@isteam.de>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * OutputFilterApi.php
 *
 * @category     Addons
 * @package      Addons_OutputFilter
 * @copyright    Manuela v.d.Decken
 * @author       Manuela v.d.Decken
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @lastmodified $Date: 2020-08-30 07:31:32 +0200 (Sun, 30 Aug 2020) $
 * @since        File available since 25.12.2013
 * @description  can apply one or more filters to $content
 *      Example: $sContent = OutputFilterApi('WbLink', $sContent);
 *      or..     $sContent = OutputFilterApi('WbLink|Relurl', $sContent);
 *      or..     $sContent = OutputFilterApi(array('WbLink', 'RelUrl'), $sContent);
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

    function createMissingValues():void
    {
        $oReg = WbAdaptor::getInstance();
        $sUpgradeFile = str_replace(DIRECTORY_SEPARATOR,'/',__DIR__).'/cmd/Upgrade.inc.php';
//  check if output_filters table is not empty
        $callUpgrade = $oReg->Db->field_exists( $oReg->TablePrefix.'mod_output_filter', 'sys_rel');
        $callUpgrade = (!$oReg->Db->get_one('SELECT COUNT(*) FROM `'.$oReg->TablePrefix.'mod_output_filter` ') ? true : $callUpgrade);
        if ($callUpgrade) {
            include $sUpgradeFile;
        }
    }

/**
 * OutputFilterApi
 * @param   string|array $mFilters  list of one or more filters
 * @param   string $sContent  content to apply filters
 * @return  string
 */
    function OutputFilterApi($mFilters, $sContent, array $aOptions = [])
    {
        if (!is_array($mFilters)) {
            $mFilters = preg_split('/\s*?[,;| +]\s*?/', $mFilters, -1, PREG_SPLIT_NO_EMPTY);
        }
        $oReg = WbAdaptor::getInstance();
        $aFilterSettings = getOutputFilterSettings();

        foreach ($mFilters as $sFilter) {
            $aTmp = preg_split('/\?/', $sFilter, 2, PREG_SPLIT_NO_EMPTY);
            $sFilterName = $aTmp[0];
            if (!preg_match('/^[A-Z][A-Za-z0-9]+$/s', $sFilterName)) { continue; }
            $sOptions = (isset($aTmp[1])) ? $aTmp[1] : '';
            $sFilterClass = 'addon\\'.basename(__DIR__).'\\Filters\\'.$sFilterName.'\\Filter';
            if (class_exists($sFilterClass)) {
                $aFilterSettings[$sFilterName] = ($aFilterSettings[$sFilterName] ?? false);
                if ($aFilterSettings[$sFilterName]) {
                    if ($sOptions) {
                        parse_str($sOptions, $aTmp);
                        $aOptions = array_merge($aTmp, $aOptions);
                    }
                    $sContent = (new $sFilterClass($oReg, $aFilterSettings, $aOptions))
                              ->execute($sContent);
                }
            } else {
                $sFilterFile = __DIR__.'/Filters/'.'filter'.$sFilterName.'.php';
                $sFilterFunc = 'doFilter'.$sFilterName;
                if (is_readable($sFilterFile)) {
                    if (!function_exists($sFilterFunc)) {
                        require($sFilterFile);
                    }
                    $sContent = $sFilterFunc($sContent, $sOptions);
                }
            }
        }
        return $sContent;
    }
/* ************************************************************************** */
/**
 * function to read the current filter settings
 * @global object $database
 * @global object $admin
 * @param void
 * @return array contains all settings
 */
    function getOutputFilterSettings()
    {
        $oDb = WbAdaptor::getInstance()->getDatabase();
//     set default values
        $aSettings = [
            'at_replacement'    => '(at)',
            'dot_replacement'   => '(dot)',
//            'W3Css_force' => 0,
        ];
//     request settings from database
        $sql = 'SELECT `name`,`value` FROM `'.$oDb->TablePrefix.'mod_output_filter`';
        if (($oRes = $oDb->query($sql))) {
            while (($aRec = $oRes->fetchRow(\MYSQLI_ASSOC))) {
                $aSettings[$aRec['name']] = $oDb->escapeString($aRec['value']);
            }
        }
        $aSettings['email_filter'] = ($aSettings['email_filter'] ?? 0);
        $aSettings['mailto_filter'] = ($aSettings['mailto_filter'] ?? 0);
        $aSettings['RelUrl'] = ($aSettings['RelUrl'] ?? 0);
        $aSettings['W3Css'] = ($aSettings['W3Css'] ?? 0);
        $aSettings['WbLinkXXL'] = ($aSettings['WbLinkXXL'] ?? 0);
        $aSettings['W3Css_force'] = ($aSettings['W3Css_force'] ?? 0);
        $aSettings['Short_force'] = ($aSettings['Short_force'] ?? 0);
        $aSettings['at_replacement']    = ($aSettings['at_replacement'] ?? '(at)');
        $aSettings['dot_replacement']   = ($aSettings['dot_replacement'] ?? '(dot)');
        $aSettings['OutputFilterMode']  = 0;
        $aSettings['OutputFilterMode'] |= ((int)$aSettings['email_filter'] * (2**0));  // n | 2^0
        $aSettings['OutputFilterMode'] |= ((int)$aSettings['mailto_filter'] * (2**1)); // n | 2^1
        \ksort($aSettings, \SORT_NATURAL | \SORT_FLAG_CASE );
//     return array with filter settings
        return $aSettings;
    }
/* ************************************************************************** */

