<?php
/**
 *
 * @category        modules
 * @package         menu_link
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 7.2 and higher
 * @version         $Id: modify.php 290 2019-03-26 16:01:51Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/menu_link/modify.php $
 * @lastmodified    $Date: 2019-03-26 17:01:51 +0100 (Di, 26. Mrz 2019) $
 *
*/

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
    $sAbsAddonPath = str_replace('\\','/',__DIR__);
    $sAddonsName = \basename($sAbsAddonPath);
    $sel = ' selected="selected"';

// check if module language file exists for the language set by the user (e.g. DE, EN)
    if (\is_readable($sAbsAddonPath.'/languages/EN.php')) {require($sAbsAddonPath.'/languages/EN.php');}
    if (\is_readable($sAbsAddonPath.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAbsAddonPath.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAbsAddonPath.'/languages/'.LANGUAGE.'.php')) {require($sAbsAddonPath.'/languages/'.LANGUAGE.'.php');}

// declare defaults
    $target_page_id = '0';
    $r_type = '301';
    $extern = '';
    $anchor = '0';

// get target page_id
    $table = 'mod_menu_link';
    $sqlSet = 'SELECT * FROM `'.TABLE_PREFIX.'mod_menu_link` '
            . 'WHERE `section_id` = '.(int)$section_id.''
            . '';
    if ($sql_result = $database->query($sqlSet)){
        if (!is_null($sql_row = $sql_result->fetchRow( MYSQLI_ASSOC ))){
            $target_page_id = $sql_row['target_page_id'];
            $r_type = $sql_row['redirect_type'];
            $extern = $sql_row['extern'];
            $extern = OutputFilterApi('ReplaceSysvar', $extern);
            $anchor = $sql_row['anchor'];
        }
    }
// Get list of all visible pages and build a page-tree
// this function will fetch the page_tree, recursive
    if (!\function_exists('menulink_make_tree')) {
        function menulink_make_tree($parent, $tree) {
            global $database, $admin;
            // get list of page-trails, recursive
            $sqlSet = 'SELECT * FROM `'.TABLE_PREFIX.'pages` '
                    . ' WHERE `parent`='.(int)$parent.' '
                    . ' ORDER BY  `position`'
                    . '';
            if ($query_page = $database->query($sqlSet)) {
                while(!is_null($page = $query_page->fetchRow(MYSQLI_ASSOC))) {
                    if ($admin->page_is_visible($page)) {
                        $tree[$page['page_id']]['page_id']  = $page['page_id'];
                        $tree[$page['page_id']]['link']  = $page['link'];
                        $tree[$page['page_id']]['level'] = $page['level'];
                        $tree = menulink_make_tree($page['page_id'], $tree);
                    }
                }
            }
            return($tree);
        }
    }

// get list of all page_ids and page_titles
//    global $menulink_titles;
    $menulink_titles = [];
    $table_p = TABLE_PREFIX."pages";

    $sql = 'SELECT `page_id`,`menu_title` FROM `'.$table_p.'` ';
    if ($query_page = $database->query($sql)) {
        while(!is_null($page = $query_page->fetchRow(MYSQLI_ASSOC))){
            $menulink_titles[$page['page_id']] = $page['menu_title'];
        }
    }
// now get the tree
//$aLinks = [];
    $aLinks = menulink_make_tree(0, []);

// Get list of targets (id=... or <a name ...>) from pages in $links
    $targets = [];
    $table_mw = TABLE_PREFIX."mod_wysiwyg";
    $table_s  = TABLE_PREFIX."sections";
    foreach($aLinks as $pid => $aLink) {
        $sql = 'SELECT `section_id`, `module`, `title` '
             . 'FROM `'.$database->TablePrefix.'sections` '
             . 'WHERE `page_id` = '.(int) $pid.' '
             . 'ORDER BY `position`';
        if ($query_section = $database->query($sql))
        {
            while(!is_null($aMenuLink = $query_section->fetchRow(MYSQLI_ASSOC))) {
                // get section-anchor
//                $targets[$pid]['value'] = ($aMenuLink['section_id'] ?? 0);
//                    $targets[$pid]['anchor'] = ($aMenuLink['title'] ?? SEC_ANCHOR.$aMenuLink['section_id']);
                if (\defined('SEC_ANCHOR') && SEC_ANCHOR!='') {
//                    $targets[$pid][] = (!empty($aMenuLink['title']) ? $aMenuLink['title'] : SEC_ANCHOR.$aMenuLink['section_id']);
                    $targets[$pid][] = SEC_ANCHOR.$aMenuLink['section_id'];
                } else {
                    $targets[$pid][] = [];
                }

                if ($aMenuLink['module'] == 'wysiwyg') {
                    $sql = 'SELECT `content`, `page_id` FROM `'.$table_mw.'` '
                         . 'WHERE `section_id` = '.(int) $aMenuLink['section_id'].' ';
                    if ($query_page = $database->query($sql)) {
//                        $page = $query_page->fetchRow(MYSQLI_ASSOC);
                        if (!\is_null($page = $query_page->fetchRow(MYSQLI_ASSOC))){
                            $sPattern = '/<(?:a[^>]+name|[^>]+id)\s*=\s*"([^"]+)"/i';
                            $match = [];
                            if (\preg_match_all($sPattern, $page['content'], $match)) {
                                foreach($match[1] as $t) {
                                    $targets[$pid][$t] = $t;
                                }// end foreach
                            } // end preg_match_all
                        } // end is_null $page
                    } // end fetchrow
                }
            } // end while
        }
    } // foreach

// get target-window for actual page
    $table = TABLE_PREFIX."pages";
    $sql = 'SELECT `target` FROM `'.$table. '` '
         . 'WHERE `page_id` = '.(int)$page_id.'';
    $query_page = $database->query($sql);
    $page = $query_page->fetchRow(MYSQLI_ASSOC);
    $target = $page['target'];
// script for target-select-box
?>
<form name="menulink" action="<?php echo WB_URL ?>/modules/menu_link/save.php" method="post">
    <input type="hidden" name="page_id" value="<?php echo $page_id ?>" />
    <input type="hidden" name="section_id" value="<?php echo $section_id ?>" />
<?php echo $admin->getFTAN(); ?>
    <table class="w3-table">
    <tbody>
    <tr>
        <td class="setting_name w3-right-align">
            <?= $TEXT['LINK'].':'; ?>
        </td>
        <td>
            <select class="w3-border w3-padding" name="menu_link" id="menu_link" onchange="populate()" style="width: 28%;" >
                <option value="0"<?= (($target_page_id==='0') ? $sel : '');?>><?= $TEXT['PLEASE_SELECT']; ?></option>
                <option value="-1"<?= (($target_page_id==='-1') ? $sel : '');?>><?= $MOD_MENU_LINK['EXTERNAL_LINK']; ?></option>
<?php
                foreach($aLinks as $pid => $aLink) {
                    $iLeft = str_repeat(' -- ',$aLink['level']);
                    // Display current page with selection disabled
                    if ($pid == $page_id){
?>
                     <option class="level-<?= $aLink['level'];?>" value="<?= $pid;?>" disabled="disabled"><span class="w3-text-white"><?php echo $iLeft;?></span><?php echo basename($aLink['link']);?> *</option>
<?php
                }
                else{
                $sTmp = ($target_page_id==$pid ? $sel : '');
?>
                     <option class="level-<?= $aLink['level'];?>" value="<?= $pid;?>" <?= $sTmp;?>><span class="w3-text-white"><?php echo $iLeft;?></span><?php echo basename($aLink['link']);?></option>
<?php
                }
            } ?>
            </select>
            <input class="w3-border w3-padding w3-padding w3-mobile " type="text" name="extern" id="extern" value="<?php echo $extern; ?>" style="width:60%;" <?php if($target_page_id!='-1') echo 'disabled="disabled"'; ?> />
        </td>
    </tr>
    <tr>
        <td class="setting_name w3-right-align">
            <?= $TEXT['ANCHOR'].':'; ?>
        </td>
        <td>
            <select class="w3-border w3-padding" name="page_target" id="page_target" onfocus="populate()" style="width: 28%;" >
                <option value="<?= $anchor ?>" selected="selected"><?= $anchor=='0' ? ' ' : '#'.$anchor ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="setting_name w3-right-align">
            <?= $TEXT['TARGET'].':'; ?>
        </td>
        <td>
            <select class="w3-border w3-padding" name="target" style="width: 28%;" >
                <option value="_blank"<?php if ($target=='_blank') echo ' selected="selected"'; ?>><?php echo $TEXT['NEW_WINDOW']; ?></option>
                <option value="_self"<?php if ($target=='_self') echo ' selected="selected"'; ?>><?php echo $TEXT['SAME_WINDOW']; ?></option>
                <option value="_top"<?php if ($target=='_top') echo ' selected="selected"'; ?>><?php echo $TEXT['TOP_FRAME']; ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="setting_name w3-right-align">
            <?= $MOD_MENU_LINK['R_TYPE'].':'; ?>
        </td>
        <td>
            <select class="w3-border w3-padding" name="r_type" style="width:28%;" >
                <option value="301"<?php if (($r_type ==='301')) {echo ' selected="selected"';} ?>>301</option>
                <option value="302"<?php if (($r_type ==='302')) {echo ' selected="selected"';} ?>>302</option>
            </select>
        </td>
    </tr>

    </tbody>
    </table>

    <table class="w3-table">
    <tbody>
    <tr>
        <td class="setting_name" style="width: 16.525%; white-space: nowrap;">&nbsp;
        </td>
        <td>
            <input class="btn w3-blue-wb w3-hover-green" type="submit" value="<?= $TEXT['SAVE'] ?>" />
            <input class="btn w3-blue-wb w3-hover-red" type="button" value="<?= $TEXT['CANCEL'] ?>" onclick="window.location = 'index.php';" />
        </td>
    </tr>
    </tbody>
    </table>

</form>
<script>
/*<![CDATA[*/
    function populate() {
        o=document.getElementById('menu_link');
        d=document.getElementById('page_target');
        e=document.getElementById('extern');
        if(!d){return;}
        var mitems=new Array();
        mitems['0']=[' ','0'];
        mitems['-1']=[' ','0'];
<?php
        foreach($aLinks as $pid => $item) {
            $str ="mitems['$pid']=[";
            $str.="' ',";
            $str.="'0',";
            if (is_array($targets) && isset($targets[$pid])) {
                foreach($targets[$pid] as $item) {
                    $str.="'#$item',";
                    $str.="'$item',";
                }
                $str=rtrim($str, ',');
//                $str.="];\n";
            }
                $str.="];\n";
            echo $str;
        }
?>
        d.options.length=0;
        cur=mitems[o.options[o.selectedIndex].value];
        if(!cur){return;}
        d.options.length=cur.length/2;
        j=0;
        for(var i=0;i<cur.length;i=i+2)
        {
            d.options[j].text=cur[i];
            d.options[j++].value=cur[i+1];
//console.log(cur[i]);
        }
//console.log(o.value);
        if(o.value==='-1') {
            e.disabled = false;
        } else {
            e.disabled = true;
        }
    }

/*]]>*/
</script>
