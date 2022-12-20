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
 * @version         $Id: search.php 284 2019-03-22 08:13:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/search.php $
 * @lastmodified    $Date: 2019-03-22 09:13:16 +0100 (Fr, 22. Mrz 2019) $
 * @description
 */

function form_search($func_vars) {
    extract($func_vars, EXTR_PREFIX_ALL, 'func');
    // how many lines of excerpt we want to have at most
    $max_excerpt_num = $func_default_max_excerpt;
    $divider = ".";
    $result = false;

    // fetch all form-fields on this page
    $table = TABLE_PREFIX."mod_form_fields";
    $sSql = 'SELECT '
          . '`title`, `value` '
          . 'FROM  `'.$table.'` '
          . 'WHERE `section_id` = '.(int)$func_section_id. ' '
          . 'ORDER BY `position` ';
    $query = $func_database->query($sSql);
    // now call print_excerpt() only once for all items
    if($query->numRows() > 0) {
        $text="";
        while($res = $query->fetchAssoc()) {
            $text .= $res['title'].$divider.$res['value'].$divider;
        }
        $mod_vars = [
            'page_link' => $func_page_link,
            'page_link_target' => "#wb_section_$func_section_id",
            'page_title' => $func_page_title,
            'page_description' => $func_page_description,
            'page_modified_when' => $func_page_modified_when,
            'page_modified_by' => $func_page_modified_by,
            'text' => $text,
            'max_excerpt_num' => $max_excerpt_num
        ];

        if (print_excerpt2($mod_vars, $func_vars)) {
            $result = true;
        }
    }
    return $result;
}
