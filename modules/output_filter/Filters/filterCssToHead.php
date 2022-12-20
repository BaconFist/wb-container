<?php
/**
 * OutputFilter 'CssToHead'
 * @copyright   Manuela v.d.Decken <manuela@isteam.de>
 * @author      Manuela v.d.Decken <manuela@isteam.de>
 * @version     161223.3
 * @param string $content
 * @return string
 * @description step1) moves all css definitions from <body> to bottom of the <head> section
 *              step2) do not move definitions which are already in <head>
 *              step3) delete all css from <body>
 */
    function _doFilterCssToHead($sContent) {
        // move css definitions into head section
        $regexPattern = "#<body.*(?P<css_tag>(?:<style[^>]+>.*?</style>)|(?:<link[^>]+/{0,1}>)|(?:<link[^>]+>.*?</link>))#isxU";
        $cssStyles = [];

        //save all <style/link>-tags in $cssStyles and remove from content
        $sContent = preg_replace_callback($regexPattern, function($match) use(&$cssStyles){ $cssStyles[] = $match['css_tag']; return str_replace($match['css_tag'],'',$match[0]); }, $sContent, -1);

        //append contents from $cssStyles to <head>-section
        $sContent = preg_replace('#</head>#i', PHP_EOL.implode(PHP_EOL, $cssStyles).PHP_EOL.'</head>', $sContent);
        return $sContent;
    }

    function doFilterCssToHead($sContent) {
        $aFilterSettings = getOutputFilterSettings();
        $key = \preg_replace('=^.*?filter([^\.\/\\\\]+)(\.[^\.]+)?$=is', '\1', __FILE__);
//        if ($aFilterSettings[$key]) {
            $aMatches = [];
            $sPattern = '/^(.*<head[^>]*>)(.*)(<\/head>.*<body[^>]*>)(.*)(<\/body>.*)$/siU';
            // 1 = .*<head..>
            // 2 = content of head
            // 3 = </head> * <body..>
            // 4 = content of body
            // 5 = </body>... end of document
            if (\preg_match($sPattern, $sContent, $aDocumentParts)) {
                unset($aDocumentParts[0]);
                $sPattern = '/(\<link[^>]*?"(text\/css|stylesheet)"[^>]*?\/?\>)|'
                          . '(\<style[^>]*?"text\/css"[^>]*?\>.*?\<\/style\>)|'
                          . '(\<style[^>]*?\>.*?\<\/style\>)/si';
                // search for pattern inside the <body>
                if (\preg_match_all($sPattern, $aDocumentParts[4], $aMatches)) {
                    // remove duplicates from the matches and save result
                    $aBodyMatches = $aMatches = \array_unique($aMatches[0]);
                    // set elements to 'false' which already are in <head>
                    \array_walk(
                        $aMatches,
                        function(& $value, $key) use (&$aDocumentParts) {
                            if (\preg_match('/'.preg_quote($value, '/').'/siu', $aDocumentParts[2])) {
                                $value = false;
                            }
                        }
                    );
                    // remove 'false' matches
                    $aMatches = \array_filter($aMatches);
                    if(\sizeof($aMatches) > 0) {
                        // now attach all matches found in <body> to the <head>
                        $aDocumentParts[2] .= "\n".\implode("\n", $aMatches)."\n";
                        // remove the matches from <body>
                        $aDocumentParts[4] = \str_replace($aBodyMatches, '', $aDocumentParts[4]);
                    }
                    //at least rebuild the document
                    $sContent = \implode("\n", $aDocumentParts);
                }
            } else {
                throw new \Exception('malformed document created, missing head area');
            }
//        }
        return $sContent;
    }
