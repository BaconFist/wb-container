<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: search.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/search.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */


function news_search($func_vars) {
    static $search_sql1 = '';
    static $search_sql2 = '';
    static $search_sql3 = '';
    if (!is_array($func_vars)){$func_vars = [];}
    extract($func_vars, EXTR_PREFIX_ALL, 'func');
    if(function_exists('search_make_sql_part')) {
        if(empty($search_sql1))
            $search_sql1 = search_make_sql_part($func_search_url_array, $func_search_match, array('`title`','`content_short`','`content_long`'));
        if(empty($search_sql2))
            $search_sql2 = search_make_sql_part($func_search_url_array, $func_search_match, array('`title`','`comment`'));
        if(empty($search_sql3))
            $search_sql3 = search_make_sql_part($func_search_url_array, $func_search_match, array('g.`title`'));
    } else {
        $search_sql1 = $search_sql2 = $search_sql3 = '1=1';
    }

    // how many lines of excerpt we want to have at most
    $max_excerpt_num = $func_default_max_excerpt;
    // do we want excerpt from comments?
    $excerpt_from_comments = true; // TODO: make this configurable
    $divider = ".";
    $result = false;

  if($func_time_limit>0) {
    $stop_time = time() + $func_time_limit;
  }

    // fetch all active news-posts (from active groups) in this section.
    $iNow = $t = time();
    $table_posts  = TABLE_PREFIX."mod_news_posts";
    $table_groups = TABLE_PREFIX."mod_news_groups";
    $sSql0  = ' '
            . 'SELECT '
            . 'p.`post_id`,p.`title`,p.`content_short`,p.`content_long`, '
            . 'p.`link`, p.`posted_when`, p.`posted_by` '
            . 'FROM `'.$table_posts.'` p '
            . 'LEFT OUTER JOIN `'.$table_groups.'` g ON p.group_id = g.group_id '
            . 'WHERE p.section_id = '.(int)$func_section_id.' '
            . '  AND p.`active` = 1 '
            . '  AND ( g.active IS NULL OR g.`active` = 1 ) '
            . '  AND (('.$iNow.' BETWEEN `p`.`published_when` AND `p`.`published_until`) '
            . '   OR  ('.$iNow.' > `p`.`published_when` AND `p`.`published_until`=0)) '
            . 'ORDER BY p.`post_id` DESC ';
    $query = $func_database->query($sSql0);
/*
    "
        SELECT p.post_id, p.title, p.content_short, p.content_long, p.link, p.posted_when, p.posted_by
        FROM $table_posts AS p LEFT OUTER JOIN $table_groups AS g ON p.group_id = g.group_id
        WHERE p.section_id='$func_section_id' AND p.active = '1' AND ( g.active IS NULL OR g.active = '1' )
        AND (published_when = '0' OR published_when <= $t) AND (published_until = 0 OR published_until >= $t)
        ORDER BY p.post_id DESC
    "
*/
    // now call print_excerpt() for every single post
    if($query->numRows() > 0) {
        while($res = $query->fetchAssoc()) {
            $text = '';
            // break out if stop-time is reached
            if(isset($stop_time) && time()>$stop_time) return($result);
            // fetch content
            $sSql1 = ''
                  . 'SELECT `title`, `content_short`, `content_long` '
                  . 'FROM `'.$table_posts.'` '
                  . 'WHERE post_id='.(int)$res['post_id'].' AND '.$search_sql1.'
            ';
            $postquery = $func_database->query($sSql1);
            if($postquery->numRows() > 0) {
                if($p_res = $postquery->fetchAssoc()) {
                    $text = $p_res['title'].$divider.$p_res['content_short'].$divider.$p_res['content_long'].$divider;
                }
            }
            // fetch comments and add to $text
            if($excerpt_from_comments) {
                $table = TABLE_PREFIX."mod_news_comments";
                $sSql2 = ''
                      . 'SELECT `title`, `comment` '
                      . 'FROM `'.$table.'` '
                      . 'WHERE post_id='.(int)$res['post_id'].' AND '.$search_sql2.' '
                      . 'ORDER BY `commented_when`
                ';
                $commentquery = $func_database->query($sSql2);
                if($commentquery->numRows() > 0) {
                    while($c_res = $commentquery->fetchAssoc()) {
                        // break out if stop-time is reached
                        if (isset($stop_time) && time()>$stop_time) return($result);
                        $text .= $c_res['title'].$divider.$c_res['comment'].$divider;
                    }
                }
            }
            if($text) {
                $mod_vars = array(
                    'page_link' => $res['link'], // use direct link to news-item
                    'page_link_target' => "",
                    'page_title' => $func_page_title,
                    'page_description' => $res['title'], // use news-title as description
                    'page_modified_when' => $res['posted_when'],
                    'page_modified_by' => $res['posted_by'],
                    'text' => $text,
                    'max_excerpt_num' => $max_excerpt_num
                );
                if(print_excerpt2($mod_vars, $func_vars)) {
                    $result = true;
                }
            }
        }
    }

    // now fetch group-titles - ignore those without (active) postings
    $table_groups = TABLE_PREFIX."mod_news_groups";
    $table_posts  = TABLE_PREFIX."mod_news_posts";
    $sSql3  = ''
            . 'SELECT DISTINCT g.title, g.group_id '
            . 'FROM `'.$table_groups.'`  g '
            . 'INNER JOIN `'.$table_posts.'` p ON g.group_id = p.group_id '
            . 'WHERE g.section_id='.(int)$func_section_id.' '
            . '  AND g.active = 1 '
            . '  AND p.active = 1 '
            . '  AND '.$search_sql3.'
    ';
    $query = $func_database->query($sSql3);
    // now call print_excerpt() for every single group, too
    if($query->numRows() > 0) {
        while($res = $query->fetchAssoc()) {
            // break out if stop-time is reached
            if(isset($stop_time) && time()>$stop_time) return($result);
            $mod_vars = array(
                'page_link' => $func_page_link,
                'page_link_target' => "&g=".$res['group_id'],
                'page_title' => $func_page_title,
                'page_description' => $func_page_description,
                'page_modified_when' => $func_page_modified_when,
                'page_modified_by' => $func_page_modified_by,
                'text' => $res['title'].$divider,
                'max_excerpt_num' => $max_excerpt_num
            );
            if (print_excerpt2($mod_vars, $func_vars)) {
                $result = true;
            }
        }
    }
    return $result;
}
