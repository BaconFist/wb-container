<?php
/**
 *
 * @param string $content
 * @return string
 */


function doFilterFrontendCss($sContent) {
    $aFilterSettings = getOutputFilterSettings();
    $key = preg_replace('=^.*?filter([^\.\/\\\\]+)(\.[^\.]+)?$=is', '\1', __FILE__);
    if (function_exists('register_filter_setting') && $aFilterSettings[$key]){
        $head_links = '';
        $aHeadFilter = ['FrontendCss'];
        $aSnippetRecords = register_addon_files($aHeadFilter);
        $aOutput = array_merge($aSnippetRecords['FrontendCss']);
        $head_links .= prepareLink($aOutput, 'css');
//        echo $head_links;
    }
    return $sContent;
}
