<?php
/**
 *
 * @param string $content
 * @return string
 */
function doFilterSnippetCss($sContent) {
    $aFilterSettings = getOutputFilterSettings();
    $key = preg_replace('=^.*?filter([^\.\/\\\\]+)(\.[^\.]+)?$=is', '\1', __FILE__);
    if (function_exists('register_filter_setting') && $aFilterSettings[$key]){
        $head_links = '';
        $aHeadFilter = [
                'SnippetCss',
        ];
        $aSnippetRecords = register_addon_files($aHeadFilter);
        $aOutput = array_merge($aSnippetRecords['SnippetCss']);
        $head_links .= prepareLink($aOutput, 'css');
//        echo $head_links;
    }
    return $sContent;
}
