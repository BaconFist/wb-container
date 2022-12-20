<?php

/**
 * ParseWbLink
 * @param string $sContent
 * @return array details of all found Wblink-Tags
 * @description parse
 */
    function _parseWbLink(&$sContent)
    {
        $aResults = [];
        $aMatches = [];
        $sPattern = '/\[wblink([0-9]+)(\#([^\?]+))?(\?([^\]]+))?\]/is';
        if (\preg_match_all($sPattern, $sContent, $aMatches, PREG_SET_ORDER)) {
            foreach ($aMatches as $aTag) {
                $aResults[$aTag[0]] = [];
                $aResults[$aTag[0]]['pageid'] = $aTag[1];
                $aResults[$aTag[0]]['ancor']  = (isset($aTag[3]) ? $aTag[3] : '');
                $aResults[$aTag[0]]['params'] = [];
                $aTag[5] = (isset($aTag[5]) ? $aTag[5] : '');
                $aTmpArgs = \preg_split('/&amp;|&/i', $aTag[5], -1, PREG_SPLIT_NO_EMPTY);
                foreach ($aTmpArgs as $sArgument) {
                    $aArgs = \explode('=', $sArgument);
                    $aResults[$aTag[0]]['params'][$aArgs[0]] = $aArgs[1];
                }
            }
        }
        return $aResults;
    }

/*
 * replace all "[wblink{page_id}]" with real links
 * @copyright       Manuela v.d.Decken
 * @author          Manuela v.d.Decken
 * @param string $content : content with tags
 * @return string content with links
 * @description
 * replace all "[wblink{page_id}{?addon=n&item=n...}]" with real links to accessfiles<br />
 *     All modules must offer the class 'WbLink'(implementing 'WbLinkAbstract'), to be taken into consideration.
 */
    function doFilterWbLink($sContent)
    {
        $aFilterSettings = getOutputFilterSettings();
        $key = \preg_replace('=^.*?filter([^\.\/\\\\]+)(\.[^\.]+)?$=is', '\1', __FILE__);
        $oDb = \database::getInstance();
        $oReg = \bin\WbAdaptor::getInstance();
        $oApp = $oReg->getApplication();
        $aSearchReplaceList = [];
        $aTagList = _parseWbLink($sContent);
        // iterate list if replacements are available
        foreach ($aTagList as $sKey=>$aTag) {
        // set link on failure ('#' means, still stay on current page)
           $aSearchReplaceList[$sKey] = '#';
        // sanitize possible ancor
           $aTag['ancor'] = ($aTag['ancor'] == '' ? '' : '#'.$aTag['ancor']);
        // handle normal pages links
           if (\sizeof($aTag['params']) == 0) {
              $sql = 'SELECT `link` FROM `'.$oDb->TablePrefix.'pages` WHERE `page_id` = '.(int)$aTag['pageid'];
              if (($sLink = $oDb->get_one($sql)))
              {
                 $sLink = \trim(\str_replace('\\', '/', $sLink), '/');
              // test if valid accessfile is available
                  if (\is_readable($oReg->AppPath.$oReg->PagesDir.$sLink.$oReg->PageExtension))
                  {
                  // create absolute URL or replace with shorturl
                      $aSearchReplaceList[$sKey] = $oReg->AppUrl.$oReg->PagesDir.$sLink.$oReg->PageExtension.$aTag['ancor'];
                      if (\is_readable($oReg->AppPath.'short.php')){
                          $sPageLink = $oApp->getPageLink((int)$aTag['pageid']);
                          $aSearchReplaceList[$sKey] = $sPageLink;
                      }
                  }
              }
        // handle links of modules
           } else {
           // build name of the needed class
              $sClass = '\\addon\\'.$aTag['params']['addon'].'\\WbLink';
           // remove addon name from replacement array
//            unset($aReplacement['Args']['addon']);
              if (\class_exists($sClass))
              {
              // instantiate class
                 $oWbLink = new $sClass();
              // the class must implement the interface
                 if ($oWbLink instanceof \WbLinkAbstract)
                 {
                    $aTag['params']['pageid'] = $aTag['pageid'];
                    $aTag['params']['ancor']  = $aTag['ancor'];
                 // create real link from replacement data
                    $aSearchReplaceList[$sKey] = $oWbLink->makeLinkFromTag($aTag['params']);
                 }
              }
           }
        // extract indexes into a new array
        // replace all identified [wblink**] tags in content with their urls
          $sContent = \str_ireplace(
             \array_keys($aSearchReplaceList),
             $aSearchReplaceList,
             $sContent
          );
        }
        return $sContent;
    }
