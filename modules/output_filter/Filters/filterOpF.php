<?php
/*
 * make use of Thorn's OutputFilter Dashboard (OpF Dashboard)
 * @copyright       Manuela v.d.Decken <manuela@isteam.de>
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @param string &$content : reference to global $content
 * @param string $sOptions :
 * @return void
 */
    function doFilterOpF($content, $sOptions)
    {
        $aFilterSettings = getOutputFilterSettings();
        $key = preg_replace('=^.*?filter([^\.\/\\\\]+)(\.[^\.]+)?$=is', '\1', __FILE__);
        if ($aFilterSettings[$key]) {
        global $database;
        $aOptions = array();
            // Load OutputFilter functions
            $sOpfFile = WB_PATH.'/modules/outputfilter_dashboard/functions.php';
            if (is_readable($sOpfFile)) {
                if (!function_exists('opf_apply_filters')) {
                    require($sOpfFile);
                }
                parse_str($sOptions, $aOptions);
                $aPresets = array('arg'=>'page', 'module'=>'', 'page_id'=>0, 'section_id'=>0);
                $aOptions = array_merge($aPresets, $aOptions);
                // use 'cache' instead of 'nocache' to enable page-cache.
                // Do not use 'cache' in case you use dynamic contents (e.g. snippets)!
                if (!isset($GLOBALS['opf_FILTERS'])) {
                    // initialize filter at first run
                    opf_controller('init');
                }
                $content = opf_controller(
                    $aOptions['arg'],
                    $content,
                    $aOptions['module'],
                    $aOptions['page_id'],
                    $aOptions['section_id']
                );
            }
        }
        return $content;
    }
