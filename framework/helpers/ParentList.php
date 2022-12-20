<?php

namespace bin\helpers;

use bin\{WbAdaptor};

class ParentList
{

// -----------------------------------------------------------------------------
/** prevent class from public instancing and get an object to hold extensions
    private function  __construct() {}*/
// -----------------------------------------------------------------------------
/** prevent from cloning existing instance
    private function __clone() {}*/
// -----------------------------------------------------------------------------

/*-------------------------------------------------------------------------------------------*/
    public static function is_serialized($data){
        return (is_string($data) && preg_match("#^((N;)|((a|O|s):[0-9]+:.*[;}])|((b|i|d):[0-9.E-]+;))$#um", $data));
    }

/*-------------------------------------------------------------------------------------------*/
    public static function unserialize($data) {  // found in php manual :-)
        $aRetval = $data;
        if (self::is_serialized($data)){
            $_ret = preg_replace_callback(
                        '!s:(\d+):"(.*?)";!',
                        function($matches) {return 's:'.strlen($matches[2]).':"'.$matches[2].'";';},
                        $data
                     );
            if ($_ret) {$aRetval = @unserialize($_ret);}
        }
        return $aRetval;
    }

/*-------------------------------------------------------------------------------------------*/
    public static function gdprSettings(){
        $oDb = \database::getInstance();
        $sValue = '';
        $sql  = 'SELECT `value` FROM `'.TABLE_PREFIX.'settings` '
              . 'WHERE `name` = \'dsgvo_settings\' ';
        if ($sValue = $oDb->get_one($sql)){;}
        return $sValue;
    }
/*-------------------------------------------------------------------------------------------*/
    public static function dsgvoSettings(){
        return self::gdprSettings();
    }
/*-------------------------------------------------------------------------------------------*/
    public static function gdprInitSettings($sFolder=''){
        if (!$sSettings = self::gdprSettings()){
            $sTemplate = ((empty($sFolder)) ? TEMPLATE : $sFolder);
            $sInifile     = '/templates/'.$sTemplate.'/DataProtection.ini.php';
            $sIniUserfile = '/templates/'.$sTemplate.'/DataUserProtection.ini.php';
            if (is_readable(WB_PATH .$sIniUserfile)){
                $sInifile = $sIniUserfile;
            }
            if (is_readable(WB_PATH .$sInifile)){
                $aTmp = \parse_ini_file(WB_PATH .$sInifile, true, INI_SCANNER_TYPED);
                $aSettings = $aTmp['dsgvo'];
             }
        } else {
          $aSettings = self::unserialize($sSettings);
        }
        return $aSettings;
    }

/*-------------------------------------------------------------------------------------------*/
    public static function getGdprDefaultLink(){
        $sSettings = self::dsgvoSettings();
        $aTmp = get_defined_constants(true);
        $aConst = $aTmp['user'];
        ksort($aConst);
        $aSettings = self::unserialize($sSettings);
        $aLang = (\defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'EN');
        $aLang = (\defined('LANGUAGE') ? LANGUAGE : $aLang);
        $targetSectionId = (isset($aSettings[$aLang]) ? $aSettings[$aLang] : ($aSettings['EN'] ?? []));
        $sDataLink = self::build_access_file($targetSectionId);
        return $sDataLink;
   }
/*-------------------------------------------------------------------------------------------*/
    public static function getDsgvoDefaultLink(){
        return self::getGdprDefaultLink();
    }
/*-------------------------------------------------------------------------------------------*/
    public static function gdprInput($iCurrentId=0, $iTargetId=0,$Message='',$sFolder=''){
        $sDataLink = self::build_access_file($iTargetId);
        $sTemplate = ((empty($sFolder)) ? TEMPLATE : $sFolder);
        $aSettings = self::gdprInitSettings($sFolder);
        \ob_start();
?>
        <div class="w3-bar" style="margin-top: 1.5225em;">
            <input class="w3-bar-item w3-check w3-border" id="data_protection<?php echo $iCurrentId;?>" name="data_protection<?php echo $iCurrentId;?>" value="1" type="checkbox" />
            <label for="data_protection<?php echo $iCurrentId;?>" class="description w3-bar-item" style="width: 95%;margin-top:-0.525em;">
                <?php echo \sprintf($Message, $sDataLink); ?>
            </label>
        </div>
<?php
        $sValue = \ob_get_clean().PHP_EOL;
        return $sValue;
    }
/*-------------------------------------------------------------------------------------------*/
    public static function dsgvoInput($iCurrentId=0,$iTargetId=0,$Message='',$sFolder=''){
        return self::gdprInput($iCurrentId,$iTargetId,$Message,$sFolder);
    }

/*-------------------------------------------------------------------------------------------*/
    public static function captchaSettings(){
        $oReg = WbAdaptor::getInstance();
        $oDb = $oReg->getDatabase();
        $sValue = '';
        $sql  = 'SELECT `enable_captcha` FROM `'.$oReg->tablePrefix.'mod_captcha` ';
        if ($sValue = $oDb->get_one($sql)){;}
        return $sValue;
    }

/*-------------------------------------------------------------------------------------------*/
    public static function build_access_file($id=0){
        $sAccessFile = '';
        if ($id>0){
            $oReg  = WbAdaptor::getInstance();
            $oDb   = $oReg->getDatabase();
            $oApp  = $oReg->getApplication();
            $aPage = self::getPageFromSectonId($id);
            $aRec  = [];
// Query current settings in the db, then loop through them and print them
            $query  = '
            SELECT
            `name`, `value`
            FROM `'.$oReg->TablePrefix.'settings`
            WHERE `name` IN (\'pages_directory\',\'page_extension\',\'sec_anchor\')
            ';
            if ($oRes  = $oDb->query($query)){
                $aRecs = $oRes->fetchAll(\MYSQLI_ASSOC);
                foreach($aRecs as $key => $aVal) {
                    $aRec[$aVal['name']] = $aVal['value'];
                }
                $sPagesLink = (($aRec['pages_directory']==='/') ? '' : $aRec['pages_directory']);
                $sAnchor = ((trim($aRec['sec_anchor']) === 'none')||($id==0) ? '' : '#'.$aRec['sec_anchor'].$id);
                if (empty($sPagesLink)){
                    $sAccessFile = trim($oReg->AppUrl,'/').$sPagesLink.$aPage['link'].$aRec['page_extension'].$sAnchor;
                } elseif(isset($aPage['page_id']) && is_numeric($aPage['page_id'])) {
                    $sAccessFile = $oApp->getPageLink($aPage['page_id']).$sAnchor;
                }
            }
        }
        return $sAccessFile;
    }

/*-------------------------------------------------------------------------------------------*/
    public static function getPageFromSectonId($id=0){
        $oReg = WbAdaptor::getInstance();
        $oDb = $oReg->getDataBase();
        $aSectionList = [];
        $table_pages = $oReg->TablePrefix."pages";
        $table_sections =  $oReg->TablePrefix."sections";
        $sUserLang = (\defined('LANGUAGE') ? LANGUAGE : 'EN');
        $sUserLang = (\defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : $sUserLang);
// search valide page
        $sqlWhere = 'WHERE'.(($id != 0) ? '`s`.`section_id` = '.(int)$id.''
                  : '`p`.`parent` = '.(int)$id.'').'';  // AND `p`.`language` = \''.$sUserLang.'\'
        $sql  = 'SELECT `s`.*, `p`.`link`, `p`.`parent`, `p`.`language` '
              . 'FROM `'.$table_sections.'` s '
              . 'JOIN `'.$table_pages.'` `p` ON (`s`.`page_id` = `p`.`page_id`) '
              . $sqlWhere;
        if (!($oInstances = $oDb->query($sql))) {
            $aErrorMsg[] = $sql.PHP_EOL.$oDb->get_error();
        }
// try to load an existing record
        if (!\is_null($aRecord = $oInstances->fetchRow(\MYSQLI_ASSOC))) {
            $aSectionList = $aRecord;
        }
        return (\sizeof($aSectionList) ? $aSectionList : []);
    }

/*-------------------------------------------------------------------------------------------*/
// this function will fetch the page_tree, recursive
    public static function menulink_make_tree(int $parent, $tree)
    {
        $oReg = WbAdaptor::getInstance();
        $oDb = $oReg->getDataBase();
        // get list of page-trails, recursive
        $sqlSet = '
        SELECT
        `page_id`,`parent`,`root_parent`,`level`,`link`,`page_trail`,`position`
        ,`language`,`visibility`,`page_title`,`menu_title`,`page_code`
        FROM `'.$oReg->TablePrefix.'pages`
        WHERE `parent`='.(int)$parent.'
          AND `visibility` NOT IN(\'none\', \'deleted\')
        ORDER BY  `position`
        ';
        if ($query_page = $oDb->query($sqlSet)) {
            while(!\is_null($page = $query_page->fetchRow(\MYSQLI_ASSOC))) {
                    $tree[$page['page_id']]['parent'] = $page['parent'];
                    $tree[$page['page_id']]['root_parent'] = $page['root_parent'];
                    $tree[$page['page_id']]['level'] = $page['level'];
                    $tree[$page['page_id']]['link']  = $page['link'];  //
                    $tree[$page['page_id']]['page_trail']  = $page['page_trail'];  //
                    $tree[$page['page_id']]['position']  = $page['position'];  //
                    $tree[$page['page_id']]['language'] = $page['language'];
                    $tree[$page['page_id']]['visibility'] = $page['visibility'];
                    $tree[$page['page_id']]['page_title'] = $page['page_title'];
                    $tree[$page['page_id']]['menu_title'] = $page['menu_title'];
                    $tree[$page['page_id']]['page_code'] = $page['page_code'];
                    $tree = self::menulink_make_tree($page['page_id'], $tree);
            }
        }
        return($tree);
    }
/*-------------------------------------------------------------------------------------------*/

/*-------------------------------------------------------------------------------------------*/
    public static function build_sectionlist($parent=0, $this_page=0, & $aSections=[]) {
        $oReg = WbAdaptor::getInstance();
        $oDb = $oReg->getDataBase();
        $iterated_parents = []; // keep count of already iterated parents to prevent duplicates
        $table_pages = $oReg->TablePrefix."pages";
        $table_sections = $oReg->TablePrefix."sections";
        $sSql = "
            SELECT
            `s`.`module`,`s`.`active`,`s`.`position`,`s`.`section_id`,`s`.`title`
            ,`p`.`link`, `p`.`language`, `p`.`page_title`, `p`.`page_id`, `p`.`level`
            FROM ".$table_sections." s
            JOIN ".$table_pages." `p` ON (`s`.`page_id` = `p`.`page_id`)
            WHERE `p`.`parent` = ".(int)$parent."
              AND `p`.`visibility` NOT IN('none', 'deleted')
            ORDER BY `p`.`level`, `p`.`position` ASC";
        if ($query_section_id = $oDb->query($sSql)) {
            while($aRes = $query_section_id->fetchRow(\MYSQLI_ASSOC)) {
                $mname = $aRes['module'];
                $mname .= ' ['.((isset($aRes['title']) && $aRes['title']) ? $aRes['title'] : $aRes['section_id']).']';
                if (isset($aRes['namesection']) && $aRes['namesection']){ $mname = $aRes['module'].' ['.$aRes['namesection'].']';}
                $aSections[$aRes['section_id']]['section_id'] = $aRes['section_id'];
                $aSections[$aRes['section_id']]['language'] = $aRes['language'];
                $aSections[$aRes['section_id']]['level'] = $aRes['level'];
                $aSections[$aRes['section_id']]['module'] = $mname;
                $aSections[$aRes['section_id']]['title'] = $aRes['title'];
                $aSections[$aRes['section_id']]['page_title'] = $aRes['page_title'];
                $aSections[$aRes['section_id']]['page_id'] = ''.$aRes['page_id'];
                $aSections[$aRes['section_id']]['this_page'] = ''.$this_page;
                $aSections[$aRes['section_id']]['language'] = ''.$aRes['language'];
/*  */
                if ($aRes['page_id'] != $this_page) {
                    $aSections[$aRes['section_id']]['descr'] = $aRes['section_id'].'||'.\str_repeat("-- ",$aRes['level']).$aRes['page_title'].' '.$mname;
                } else {
                    $aSections[$aRes['section_id']]['descr'] = '||'.\str_repeat("- - ",$aRes['level']).$aRes['page_title'].' '.$mname;
                }

                if (!\in_array($aRes['page_id'], $iterated_parents)) {
                    self::build_sectionlist($aRes['page_id'], $aRes['page_id'], $aSections);
                    $iterated_parents[] = $aRes['page_id'];
                }
            }
        }
        return($aSections);
    }
/* ---------------------------------------------------------------------------------- */
    public static function tidyFilename($val)
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
//            $sRetval = preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', $sRetval);
        $sRetval = str_replace('__', '', $sRetval);
        // Umlaute konvertieren
        $sRetval = str_replace(array_keys($map), array_values($map), $sRetval);
        // alle anderen Zeichen verwerfen
    //    $sRetval = preg_replace('#[^a-z0-9_.-]#', '', $sRetval);
        return $sRetval;
    }
}
