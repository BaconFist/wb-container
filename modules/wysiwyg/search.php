<?php
/**
 *
 * @category        frontend
 * @package         search
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: search.php 302 2019-03-27 10:25:40Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/wysiwyg/search.php $
 * @lastmodified    $Date: 2019-03-27 11:25:40 +0100 (Mi, 27. Mrz 2019) $
 *
 */

function wysiwyg_search($func_vars) {
    static $search_sql = '';
    \extract($func_vars, \EXTR_PREFIX_ALL, 'func');
    if (\function_exists('search_make_sql_part')) {
        if(empty($search_sql))
            $search_sql = search_make_sql_part($func_search_url_array, $func_search_match, ['`content`']);
    } else {
        $search_sql = '1=1';
    }

    // how many lines of excerpt we want to have at most
    $max_excerpt_num = $func_default_max_excerpt;
    $divider = ".";
    $result = false;
    // we have to get 'content' instead of 'text', because strip_tags() dosen't remove scripting well.
    // scripting will be removed later on automatically
    $table = TABLE_PREFIX."mod_wysiwyg";
    $sSql = 'SELECT `content` FROM `'.$table.'` '
          . ' WHERE `section_id`= '.(int)$func_section_id.'
          ';
    $query = $func_database->query($sSql);

    if ($query->numRows() > 0) {
        if ($res = $query->fetchAssoc()) {
            $mod_vars = [
                'page_link' => $func_page_link,
                'page_link_target' => "#wb_section_$func_section_id",
                'page_title' => $func_page_title,
                'page_description' => $func_page_description,
                'page_modified_when' => $func_page_modified_when,
                'page_modified_by' => $func_page_modified_by,
                'text' => $res['content'].$divider,
                'max_excerpt_num' => $max_excerpt_num
            ];

            if (print_excerpt2($mod_vars, $func_vars)) {
                $result = true;
            }
        }
    }
    return $result;
}
