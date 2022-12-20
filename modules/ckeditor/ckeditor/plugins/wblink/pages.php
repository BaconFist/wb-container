<?PHP
ob_start();
 /*
 * This Plugin read files of a directory and outputs
 * a javascript or object Array.
 */

 // Include config file
if (!defined('SYSTEM_RUN')) {
    $sConfigFile = \preg_replace('/^(.*?[\\\\\/])modules[\\\\\/].*$/', "$1config.php", __FILE__, 1);
    if (\is_readable($sConfigFile)) { require $sConfigFile; }
}
$wb284 = false;

  $oDb = \database::getInstance();

 // Create new admin object
$admin = new \admin('Pages', 'pages_modify', false, false);

if(!function_exists('cleanup')) {
    function cleanup($string)
    {
        return preg_replace("/\r?\n/", "\\n", \database::getInstance()->escapeString($string));
    }
} // end function cleanup

 /**
  * setPrettyArray()
  *
  * @param integer $bLinefeed
  * @param integer $iWhiteSpaces
  * @param integer $iTabs
  * @return string
  */
 function setPrettyArray($bLinefeed = 1, $iWhiteSpaces = 0, $iTabs = 0) {
   $sRetVal = "";
   if($bLinefeed > 0) {
     $sRetVal .= "\n";
   }
   if($iWhiteSpaces > 0) {
     $sRetVal .= str_repeat(" ", $iWhiteSpaces);
   }
   if($iTabs > 0) {
     $sRetVal .= str_repeat("\t", $iTabs);
   }
   return $sRetVal;
 }

 /**
  * buildPageList()
  *
  * @param string $jsType
  * @return
  */
function buildPageList($jsType = 'object') {
    global $admin;
    $oDb = \database::getInstance();
    $oPageList = new \bin\helpers\SmallRawPageTree();
    $aPagesList = $oPageList->getPageList();

    $iLastEntryLevel = 0;
    $bSkipChildren = false;
    $PagesSelectBox = "";
    switch ($jsType):
        case 'object':
           // build object
            $PagesSelectBox  = "var PagesItemSelectBox = {};";
            $PagesSelectBox .= "\n"."    PagesItemSelectBox = {";
            break;
        default:
           // build array
           $PagesSelectBox = "var PagesItemSelectBox = [];";
    endswitch; // end of switch

    $Index = 0;
/*
print '<pre>'.' '.basename(__DIR__).'/'.basename(__FILE__).' ZN->'.__LINE__.' function '.__FUNCTION__.' '.PHP_EOL;
\print_r( $aPagesList ).PHP_EOL.PHP_EOL;
print '</pre>'.PHP_EOL;
*/
//   while (list(, $aPage) = each($aPagesList)) {
    foreach($aPagesList as $iKeyX => $aPage){
        // skip child pages where current user has no rights for
        if($bSkipChildren && ($aPage['level'] > $iLastEntryLevel)) {
            continue;
        }
        $bSkipChildren   = false;
        $iLastEntryLevel = $aPage['level'];
        //skip entry if it's not visible
        if (($admin->page_is_visible($aPage) == false) && ($aPage['visibility'] <> 'none')) {
            continue;
        }

        // create indent chars
        $sTitlePrefix = str_repeat('- ', $aPage['level']).'';
        $sMenuTitle = $sTitlePrefix.htmlspecialchars_decode(preg_replace("/\r?\n/", "\\n", $oDb->escapeString($aPage['menu_title'])));
        $sPageTitle = htmlspecialchars_decode(preg_replace("/\r?\n/", "\\n", $oDb->escapeString($aPage['page_title'])));
        //print $sMenuTitle."\n";
        $admin->page_id = $aPage['page_id'];
        switch ($jsType):
           case 'object':
             // build object
             $PagesSelectBox .= "\n"."'".$Index."' : {"."'CurrPage': ".$aPage['page_id'].", "."'wblink': '[wblink".$aPage['page_id']."]', "."'title' : '".$sMenuTitle."', "."'pageTitle' : '".$sPageTitle."', ".
               "'selectedAddon': "."\'\'"."},";
             break;
         default:
             // build Array wblink, menu_title, page_title, addon.wblink ( will be set on the fly )
             $PagesSelectBox .= "".setPrettyArray(1, 4, 0)."PagesItemSelectBox['".$Index."'] = [".setPrettyArray(0, 0, 0)."'".$sMenuTitle."',"."'[wblink".$aPage['page_id']."]', "
               //                            . "\n\t"."PagesItemSelectBox['".$Index."'] = new Array ();"
               //                            . "\n\t"."PagesItemSelectBox['".$Index."']['".$aPage['page_id']."'] = new Array ("
             ."'".$sPageTitle."', "."'',"."'".$aPage['page_id']."'".setPrettyArray(0, 0, 0)."];"."";
       endswitch; // end of switch
       $Index++;
    } // end while

    switch ($jsType):
    case 'object':
        // build object
        $PagesSelectBox .= "\n"."};"."\n";
        break;
    default:
        // build array
        //            $PagesSelectBox .= "\n".");"."\n";
    endswitch; // end of switch

    return $PagesSelectBox;
} // end of function buildPageList

 /* --------------------------------------------------------------------------------- */

 /**
  * buildAddonList( $aAllOptions, 'default' )
  *
  * @param  array $aAllOptions from m_modulename_WbLink
  * @param  string $jsType = default or object
  * @return Javascript Array or Object
  *
  */
function buildAddonList($aAllOptions = [], $jsType = 'default') {
    $oDb = \database::getInstance();
    switch ($jsType):
     case 'object':
        // now create JS-Object from $aAllOptions
        $sObjectName = 'AddonItemsSelectBox';
        $sJsOutput = 'var '.$sObjectName.' = {}';
        $iIndex = 0;
        $iItemId = 0;
        $iSectionId = 0;
        reset($aAllOptions);
//       while (list($iPageX, $aPage) = each($aAllOptions)) {
        foreach($aAllOptions as $iPageX => $aPage){
            $iPageX = intval($iPageX);
            //              $sTmpWbLink = $aAllOptions[$iPageX][$iSectionId][$iItemId];
            $sJsOutput .= "\n\t".$sObjectName.'['.intval($iPageX).'] = { ';
            $sJsOutput .= "\n\t".'CurrPage : '.intval($iPageX).'';
            $sJsOutput .= "\n\t".'};';
            reset($aPage);
//         while (list($iSectionX, $aSection) = each($aPage)) {
            foreach($aPage as $iSectionX => $aSection){
                $iSectionId = $iSectionX;
                $sJsOutput .= "\n\t\t".$sObjectName.'['.intval($iPageX).']['.$iSectionX.'] = { ';
                reset($aSection);
//           while (list($iItemX, $aItem) = each($aSection)) {
                foreach($aSection as $iItemX => $aItem){
                    $iItemId = $iItemX;
                    $sJsOutput .= "\n\t\t\t".'\''.$iItemX.'\': {\'wblink\':\''.$aItem['wblink'].'\',';
                    $sJsOutput .= '\'title\' :  \''.$aItem['title'].'\'},';
                }
                $sJsOutput .= "\n\t\t".'};';
            }
            $iIndex++;
        }
        break;
    default:
       // now create JS-array from $aAllOptions
        $sObjectName = 'AddonItemsSelectBox';
        $sJsOutput = setPrettyArray(1, 0, 0).'var '.$sObjectName.' = [];';
        $iIndex = 0;
        $iItemId = 0;
        $iSectionId = 0;
        reset($aAllOptions);
//        while (list($iPageX, $aPage) = each($aAllOptions)) {
        foreach($aAllOptions as $iPageX => $aPage){
            $sJsOutput .= setPrettyArray(1, 4, 0).$sObjectName.'['.intval($iPageX).'] = [];';
            $iIndex++;
            $iSectionId = 0;
            reset($aPage);
//        while (list($iSectionX, $aSection) = each($aPage)) {
            foreach($aPage as $iSectionX => $aSection){
                $iItemId = 0;
                reset($aSection);
                $newItemList = '    '.$sObjectName.'['.intval($iPageX).']'.'['.$iSectionId.']';
                $sJsOutput .= setPrettyArray(1, 4, 0).$newItemList.' = []; // '.sizeof($aSection).' posts';
//        while (list($iItemX, $aItem) = each($aSection)) {
                foreach($aSection as $iItemX => $aItem){
                    $sTitle = htmlspecialchars_decode(preg_replace("/\r?\n/", "\\n", $oDb->escapeString($aItem['title'])));
                    $sJsOutput .= setPrettyArray(1, 0, 1).$newItemList.'['.$iItemId.'] = [ \''.$sTitle.'\',';
                    $sJsOutput .= '\''.$aItem['wblink'].'\'';
                    $iItemId++;
                    $sJsOutput .= (($iItemId >= sizeof($aSection)) ? ' ];' : ' ],');
                } // end item while
            $iSectionId++;
            } // end section while
        } // end page while
    endswitch; // end of switch
    return $sJsOutput;
//    $sJsOutput .= "\n\t".( ($iIndex >= sizeof($aAllOptions)) ? ');' : '),'  );
} // function buildAddonList

/* --- begin: crawl all available page-addons for additional lists -------------------- */
    $iClassTotal = 0;
    $bExecute = true;
    $aAllOptions = []; // common result list as type array
    $sql = 'SELECT `directory` '.'FROM `'.$oDb->TablePrefix.'addons` '.'WHERE `type`=\'module\' AND `function`=\'page\' '.'AND NOT `directory` LIKE \'%\_%\' '.'';
    // note: this query will skip directories including an underscore!
    if(($oAddons = $oDb->query($sql)) && $bExecute == true) {
        while ($aAddon = $oAddons->fetchRow(MYSQLI_ASSOC)) {
        $sClass = '\\addon\\'.$aAddon['directory'].'\\WbLink';
        if (class_exists($sClass)) {
            $oAddon = new $sClass($aAddon['directory']);
            if ($oAddon instanceof \WbLinkAbstract) {
            // if yes, request the list from addon
            $aAddonOptions = $oAddon->generateOptionsList();
            // merge result to already received lists
            $aAllOptions = array_merge_recursive($aAllOptions, $aAddonOptions);
            }
            ++$iClassTotal;
            }
        }
    }
/* --- end: crawl all registered addons for additional lists -------------------------- */

// $jsType = default (Array) or object
    echo  buildPageList('default');
    if (is_array($aAllOptions) && sizeof($aAllOptions)) {
        echo buildAddonList($aAllOptions, 'default');
    } else {
        echo "AddonItemsSelectBox = []";
    }
    $output = ob_get_clean();
/* */
\header("Cache-Control: no-store, no-cache, must-revalidate");
\header("Cache-Control: post-check=0, pre-check=0, false");
\header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
\header("Pragma: no-cache");
\header("Accept-Ranges: bytes");
\header("Content-type: application/javascript; charset: UTF-8");

echo $output; //
