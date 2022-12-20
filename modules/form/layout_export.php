<?php
/**
 *
 * @category        modules
 * @package         form
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.x
 * @requirements    PHP 5.6 and higher
 * @version         $Id: layout_export.php 284 2019-03-22 08:13:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/layout_export.php $
 * @lastmodified    $Date: 2019-03-22 09:13:16 +0100 (Fr, 22. Mrz 2019) $
 * @since           File available since 2017 sept 21
 * @description     xyz
 */
/* -------------------------------------------------------- */
if (!function_exists('LayoutExport')){
    function LayoutExport ($aRequestVars) {
        $admin = $GLOBALS['admin'];
        $database = $GLOBALS['database'];

        $sAddonPath = (__DIR__);
        $sAddonName = basename($sAddonPath);
        $sAddonRel = '/modules/'.$sAddonName.'/';
        $sTargetAbsPath  = $sAddonPath.'/data/layouts/';
        $aMessage = [];

        $oLang = Translate::getInstance();
        $oLang->enableAddon('modules\\'.$sAddonName);
        $aLang = $oLang->getLangArray();

        $removeExtension = (function  ($sFilename){
            $sRetval = '';
            return preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', $sFilename);
        });

        $getUniqueName = (function ($sName, $sPattern='*.xml') use ($sTargetAbsPath) {
            if (!empty($sName)){
                $sBaseName = preg_replace('/^(.*?)(\_[0-9]+)?$/', '$1', $sName);
                $aMaxNames = glob($sTargetAbsPath.$sBaseName.$sPattern, GLOB_NOSORT);
                sort($aMaxNames);
                $sMaxName = basename(end($aMaxNames));
                $iCount = intval(preg_replace('/[^0-9\.\-]/', '', $sMaxName))+1;
                $sName = $sBaseName.sprintf('_%03d', $iCount++);
            }
            return $sName;
        });

        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        foreach ($aRequestVars as $index=>$value){
            $$index = $value;
        }

        $title = $removeExtension($title);
        $file  = $removeExtension($file);

        $sSectionIdPrefix = (defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' );
        $sBackUrl = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix.$section_id;
        $sBacklink = WB_URL.'/modules/'.$sAddonName.'/modify_settings.php';

        if (!$admin->checkFTAN($requestMethod)){
             $admin->print_error( 'checkFTAN ::'.$oLang->MESSAGE_GENERIC_SECURITY_ACCESS, $sBackUrl );
        }
        /* ---------------------------------------------------------------------------------- */
        $TidyFilename = (function ($val)
        {
            // whitespace durch Unterstrich ersetzen
        /*
            $sRetval = preg_replace('#(\s+)#', '_', $val);
            $sRetval = preg_replace('/[^A-Za-z0-9]/', '_', $val);
            [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        */
            $sRetval = preg_replace(
            '~
            (\s+)|                        # file system reserved
            [\x00-\x1F]|                  # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
            [\x7F\xA0\xAD]|               # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
            [#\[\]@!§"$%&\'\?()+,;:=§\/]| # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
            [{}^\~`]                      # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
            ~x',
            '_', $val);
            // Liste aller Umlaute
            $map = array(
                    'ä' => 'ae',
                    'Ä' => 'ae',
                    'ß'=>'ss',
                    'ö'=>'oe',
                    'Ö' => 'oe',
                    'Ü'=>'ue',
                    'ü'=>'ue',
                    '<'=>'',
                    '>'=>'',
                    // hier ggf. weitere Zeichen ergänzen, z.B.
                    'à' => 'a',
                    'é' => 'e',
                    'è' => 'e',
                );
            // remove file ext
            $sRetval = preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', $sRetval);

            $sRetval = str_replace('__', '', $sRetval);
            // Umlaute konvertieren
            $sRetval = str_replace(array_keys($map), array_values($map), $sRetval);
            // alle anderen Zeichen verwerfen
        //    $sRetval = preg_replace('#[^a-z0-9_.-]#', '', $sRetval);
            return $sRetval;
        });

        $version  = '1.0';
        $encoding = 'utf-8';

        $sOldFilename = $TidyFilename($admin->StripCodeFromText($file));
        $sFilename    = $TidyFilename($admin->StripCodeFromText($title));

        $FTAN = $admin->getFTAN('GET');
        $sBacklink   .= '?page_id='.$page_id.'&section_id='.$section_id;

        $sFilename    = (($sFilename==$sOldFilename)?$getUniqueName($sOldFilename):$sFilename);
        $sDownloadUrl = '';
        $sDescription = ''.$admin->StripCodeFromText($description);
        $sDescription = (!empty($sDescription) ? $sDescription:'Beschreibung des Formulars…');

        if (!file_exists($sTargetAbsPath)&&!make_dir($sTargetAbsPath)){
            $aMessage[] = sprintf('%1$.04d ) Couldn\'t create /'.$sAddonName.'/data/layouts/.', __LINE__);
        } else {
            if ($sFilename!=''){
                $sFilename .= '.xml';//'_'.$section_id.
                $sAbsFilename = $sTargetAbsPath.$sFilename;
                try {
                    $dom = new DOMDocument($version,$encoding);
                    $dom->preserveWhiteSpace = true;
                    $dom->formatOutput = true;
                    $root = $dom->createElement("root");
                    $oTitle        = $dom->createElement("title", $title);
                    $oDescription  = $dom->createElement("description", $sDescription);
                    $root->appendChild($oTitle);
                    $root->appendChild($oDescription);
                    $fields = $dom->createElement('fields');
                } catch ( Exception $e ){
                    $aMessage[] = sprintf('%1$.04d ) Tried to set root in DOMElement!<br />'.$e, __LINE__);
                }
            // Select all the fields in the form table
                $sql  = 'SELECT `header`,`field_loop`,`extra`,`footer` FROM `'.TABLE_PREFIX.'mod_form_settings` '
                      . 'WHERE `section_id` = '.(int)$section_id.' '
                      . '';
                if (!$oRes = $database->query($sql)) {
                  die('Invalid query: ' . $database->get_error());
                }
            // Iterate through the rows, adding XML nodes for each
                while ($aXml = $oRes->fetchRow(MYSQLI_ASSOC)){
                    // Add to XML document node
                    foreach ($aXml as $key=>$value){
                        switch ($key):
                            case 'page_id':
                              break;
                            default:
                            $child_node = $dom->createElement($key, $value);
                        endswitch;
                        $fields->appendChild($child_node);
                    }
                }// end while fetchRow
                $root->appendChild($fields);
                $dom->appendChild($root);
                $dom->save($sAbsFilename);
            } // no existing form fields
            else {
                $admin->print_error($oLang->FORM_MESSAGE_FILE_TITLE_VALUE, $sBacklink.'&'.$FTAN);
            }
        } // end make_dir

        if (!sizeof($aMessage)){
            $admin->print_success(sprintf($oLang->FORM_MESSAGE_EXPORT_SUCCESS,$sFilename), $sBacklink.'&'.$FTAN);
            return true;
        } else {
            $admin->print_error(implode('<br />',$aMessage), $sBackUrl);
        }
    }
}

/*---------------------------------------------------------------------------------*/
if (!defined( 'WB_PATH' ) ){ require( dirname(dirname((__DIR__))).'/config.php' ); }
//    require(WB_PATH.'/framework/functions.php');
    $print_info_banner = true;
// suppress to print the header, so no new FTAN will be set
    $admin_header = true;
// Tells script to update when this page was last updated
    $update_when_modified = true;
// Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
    $aRequestVars  = (isset(${'_'.$requestMethod}) ? ${'_'.$requestMethod} : $_REQUEST);
    LayoutExport($aRequestVars);
// Print admin footer
    $admin->print_footer($admin,$database);
// end of file
