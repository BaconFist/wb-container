<?php
/**
 * execute droplets
 * @copyright       Manuela v.d.Decken <manuela@isteam.de>
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @param string $content
 * @return string
 */

    function doFilterDroplets($sContent)
    {
        $aFilterSettings = getOutputFilterSettings();
        $key = preg_replace('=^.*?filter([^\.\/\\\\]+)(\.[^\.]+)?$=is', '\1', __FILE__);
        if ($aFilterSettings[$key]) {
            if (file_exists(WB_PATH .'/modules/droplets/droplets.php')) {
                include_once(WB_PATH .'/modules/droplets/droplets.php');
                if (function_exists('evalDroplets')) {
                    $sContent = evalDroplets($sContent);
                }
            }
        }
        return $sContent;
    }
