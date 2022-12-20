<?php
/**
 *
 * @category        modules
 * @package         modules_news
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.2
 * @requirements    PHP 7.2 and higher
 * @version         $Id:  $
 * @filesource      $HeadURL:  $
 * @lastmodified    $Date:  $
 *  if ( $setting_posts_per_page && $setting_posts_per_page + $position <= $i ) { break; }
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; }
/* -------------------------------------------------------- */
    global $post_id, $post_section, $TEXT, $MESSAGE, $MOD_NEWS;

    $oReg     = Wbadaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();
    $oApp     = $oReg->getApplication();
    $isAuth   = $oApp->is_authenticated();

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sAddonName   = basename($sAddonPath);
    $sAddonRel     = '/modules/'.$sAddonName;
    $sPattern = "/^(.*?\/)modules\/.*$/";

    $sDispatchFile = $sModulesPath.'SimpleCommandDispatcher.inc.php';
    if (\is_readable($sDispatchFile)){
        $bExcecuteCommand = false;
        include $sDispatchFile;
    }

    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
/*
// load module language file
    if (\is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (\is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}
*/
    //overwrite php.ini on Apache servers for valid SESSION ID Separator
    if (function_exists('ini_set')) {
        ini_set('arg_separator.output', '&amp;');
    }

    $sDateFormat = ($oReg->DateFormat ?? 'system_default');
    $sDateFormat = (($sDateFormat == 'system_default') ? $oReg->DefaultDateFormat : $oReg->DateFormat);
    $sTimeFormat = ($oReg->TimeFormat ?? 'system_default');
    $sTimeFormat = (($sTimeFormat == 'system_default') ? $oReg->DefaultTimeFormat : $oReg->TimeFormat);
    //$sDateFormat = PreCheck::dateFormatToStrftime($sDateFormat);
    $addBracket = (function (){
        $aList = func_get_args();
    //    return preg_replace('/^(.*)$/', '/\[$1\]/s', $aList);
        return preg_replace('/^(.*)$/', '[$1]', $aList);
    });

    $sAppUrl = WB_URL;
    $modRel  = str_replace('\\','/',str_replace(WB_PATH, '', __DIR__)).'/';
    $callingScript = $oRequest->getServerVar('SCRIPT_NAME');

    $sAddonRel  = $ModuleRel = WB_REL.'/modules/'.$sAddonName.'/';
    $sAddonUrl  = $ModuleUrl = WB_URL.'/modules/'.$sAddonName.'/';
    $ModulePath = WB_PATH.'/modules/'.$sAddonName.'/';

    $sPageLink  = $oReg->AppUrl.$oReg->PagesDir.rtrim($oApp->page['link'],'/').'/'.$oReg->PageExtension;
/*
    $sScriptUrl = $oReg->AppUrl.$sPageLink;
    $sScriptRel = WB_REL.$sPageLink;
    $sShortUrl  = WB_URL.$oApp->page['link'].DIRECTORY_SEPARATOR ;
    $sRecallUrl = (\is_readable(WB_PATH.DIRECTORY_SEPARATOR.'short.php') ? $sShortUrl : $sScriptUrl);
    $sRecallUrl     = $oApp->getPageLink($page_id);
*/
    $sRecallAddress = $oApp->getPageLink($page_id);

//    $sAddonThemeUrl = $sAddonUrl.'/templates/default/';
    $oRequest = \bin\requester\HttpRequester::getInstance();
// Get user's username, display name, email, and id - needed for insertion into post info
    $users = [];
    $aSql[0] = 'SELECT `user_id`,`username`,`display_name`,`email` FROM `'.TABLE_PREFIX.'users`';
    if (($resUsers = $database->query($aSql[0]))) {
        while ($recUser = $resUsers->fetchRow( MYSQLI_ASSOC )) {
            $users[$recUser['user_id']] = $recUser;
        }
    }
// Get all groups (id, title, active, image)
    $groups = [
        0 => [
            'group_id'  => 0,
            'title'     => '',
            'active'    => true,
            'image'     => ''
        ]
    ];

    $aSql[1] = 'SELECT `group_id`, `title`, `active` FROM `'.TABLE_PREFIX.'mod_news_groups` '
         . 'WHERE `section_id`='.(int)$section_id.' '
         . 'ORDER BY `position` ASC';
    if (($query_users = $database->query($aSql[1]))) {
        while (($group = $query_users->fetchRow( MYSQLI_ASSOC ))) {
            // Insert user info into users array
            $groups[$group['group_id']] = $group;
            $sImageUrl = MEDIA_DIRECTORY.'/.news/image'.$group['group_id'].'.jpg';
            $groups[$group['group_id']]['image'] = (is_readable(WB_PATH.$sImageUrl) ? WB_URL.$sImageUrl : '');
        }
    }
    // Check if we should only list posts from a certain group
    if (isset($_GET['g']) && is_numeric($_GET['g']) && ((int)$_GET['g']>0)) {
        $query_extra = 'AND `group_id`='.(int)$_GET['g'].' '.PHP_EOL;
    } else {
        $query_extra = '';
    }
    // Get settings
    $setting_header = $setting_post_loop = $setting_footer = '';
    $setting_posts_per_page = 0;
/* */
    $sql  = 'SELECT `ns`.*,`nl`.*  FROM `'.TABLE_PREFIX.'mod_news_settings` `ns` '.$sPHP_EOL
          . 'INNER JOIN `'.TABLE_PREFIX.'mod_news_layouts` `nl` ON `ns`.`layout_id` = `nl`.`id` '.$sPHP_EOL
          . 'WHERE `ns`.`section_id` = '.(int)$section_id;
    if ($oSettings = $database->query( $sql )){
        if (($recSettings = $oSettings->fetchRow(MYSQLI_ASSOC))) {
            foreach ($recSettings as $key=>$val) {
                ${'setting_'.$key} = $val;
            }
        }
    }

    $aSql[3]  = 'SELECT `order`, `order_field` FROM `'.TABLE_PREFIX.'mod_news_settings` '.PHP_EOL
          . 'WHERE `section_id` = '.$section_id.' '.PHP_EOL;
    if (!$oOrder = $database->query($aSql[3])){
        throw new \Exception($database->get_error());
    }

    $aOrder = $oOrder->fetchRow(MYSQLI_ASSOC);
    // Get total number of posts relatet to now
// Check if we should show the main page or a post itself
    $iNow = $t = time();
    $total_num = 0;
    $aSql[4] = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_news_posts` '.PHP_EOL
         . 'WHERE `section_id`='.(int)$section_id.' '.PHP_EOL
         .   'AND `active`=1 '.PHP_EOL
         .   'AND `title`!=\'\' '.PHP_EOL
         .   'AND (('.$iNow.' BETWEEN `published_when` AND `published_until`) '.PHP_EOL
         .    'OR  ('.$iNow.' > `published_when` AND `published_until`=0))'.PHP_EOL
         .   $query_extra;
    $total_num = intval($database->get_one($aSql[4]));
    if ( $total_num && $setting_posts_per_page ) {
        $iNumberOfPages = (int)($total_num / $setting_posts_per_page)+($total_num % $setting_posts_per_page ? 1 : 0 );
//        $position  = intval( isset($_GET['p']) ? $_GET['p'] : 0);
        $position = $oRequest->getParam(
        'p',
        \FILTER_SANITIZE_NUMBER_INT,
        ['options' => ['min_range' => $setting_posts_per_page, 'max_range' => $iNumberOfPages, 'default' => 0]]
        );
//        if (is_null($position)){$position  = 0;}
        $iPosition  = (($position < $total_num) ? $position : ($iNumberOfPages*$setting_posts_per_page));
        $position   = \is_numeric($iPosition) ? \abs($iPosition) : 0;
        // Work-out if we need to add limit code to sql
        $limit_sql = ' LIMIT '.$position.', '.$setting_posts_per_page;
    } else {
        $display_previous_next_links = 'block';
        $position = 0;
        $next_link = '';
        $sNextLink = '';
        $next_page_link = '';
        $previous_link = '';
        $sPrevLink = '';
        $previous_page_link = '';
        $out_of = '';
        $of = '';
        $limit_sql = '';
    }
echo '
<script>
<!--
    var News = {
        WB_URL : "'.$sAppUrl.'",
        AddonUrl : "'.$sAddonUrl.'",
        THEME_URL : "'.THEME_URL.'"
    };
-->
</script>
';

if (!isset($post_id) || !is_numeric($post_id)) {
/*
$setting_posts_per_page = 12/5 = 2 5 = 10
*/
    $iNow = \time();
    // Query posts (for this page)
    $aSql[5] = 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_posts` '.PHP_EOL
         . 'WHERE `section_id`='.$section_id.' '.PHP_EOL
         .   'AND `active`=1 '.PHP_EOL
         .   'AND `title`!=\'\' '.PHP_EOL
         .   'AND (('.$iNow.' BETWEEN `published_when` AND `published_until`) '.PHP_EOL
         .    'OR  ('.$iNow.' > `published_when` AND `published_until`=0))'.PHP_EOL
         .    $query_extra
         . 'ORDER BY `'.$aOrder['order_field'].'` '.$aOrder['order'].' '.$limit_sql.PHP_EOL;
          if (!$query_posts = $database->query($aSql[5])){
              throw new \Exception($database->get_error());
          }
//    $total_num = $query_posts->numRows();
    // Create previous and next links
    if ($setting_posts_per_page != 0) {
        $spaces = '<span>&#160;</span>'.PHP_EOL;
        $sPrevLink = '';
        if (($position > 0) && ($position < $total_num)) {
            $iTmpPosition = ($position-$setting_posts_per_page);
            $pl_query = (($position-$setting_posts_per_page==0) ? '' : '?p='.($position-$setting_posts_per_page) );
            $sSeparator = (empty($pl_query)? '?' : '&amp;');
            if (isset($_GET['g']) && is_numeric($_GET['g']) && (int)$_GET['g']>0) {
                $sPrevLink = $sRecallAddress.$pl_query.$sSeparator.'g='.$_GET['g'];
                $pl_prepend = '<a rel="prev" href="'.$sPrevLink.'">&lt;&lt; ';
            } else {
                $sPrevLink = $sRecallAddress.$pl_query;
                $pl_prepend = '<a rel="prev" href="'.$sPrevLink.'">&#171; ';
            }
            $pl_append = '</a>'.PHP_EOL;
            $previous_link = $pl_prepend.$TEXT['PREVIOUS'].$pl_append;
            $previous_page_link = $pl_prepend.$TEXT['PREVIOUS_PAGE'].$pl_append;
        } else {
            $previous_link = $spaces;
            $previous_page_link = $spaces;
        }
        if ($position + $setting_posts_per_page >= $total_num) {
            $next_link = $spaces;
            $next_page_link = $spaces;
            $sNextLink = '';
        } else {
            if (isset($_GET['g']) && is_numeric($_GET['g']) && (int)$_GET['g']>0) {
                $sNextLink = $sRecallAddress.'?p='.($position+$setting_posts_per_page).'&amp;g='.$_GET['g']; //
                $nl_prepend = '<a rel="next" href="'.$sNextLink.'"> '; // .'&amp;g='.$_GET['g']
            } else {
                $sNextLink = $sRecallAddress.'?p='.($position+$setting_posts_per_page);
                $nl_prepend = '<a rel="next" href="'.$sNextLink.'"> ';
            }
            $nl_append = ' &#187;</a>'.PHP_EOL;
            $next_link = $nl_prepend.$TEXT['NEXT'].$nl_append;
            $next_page_link = $nl_prepend.$TEXT['NEXT_PAGE'].$nl_append;
        }
        if ($position+$setting_posts_per_page > $total_num) {  //
            $num_of = $total_num;
        } else {
            $num_of = $position+$setting_posts_per_page;
        }

        if (($position >= 0) && ($position < $total_num) ) {
            $out_of = ($position+1).'-'.$num_of.' '.strtolower($TEXT['OUT_OF']).' '.$total_num;
            $of = ($position+1).'-'.$num_of.' '.strtolower($TEXT['OF']).' '.$total_num;
            $display_previous_next_links = 'block';
        } else {
            $display_previous_next_links = 'none';
        }
    } // $setting_posts_per_page

    if ($total_num=== 0) { // $num_posts
        $setting_header = '';
        $setting_post_loop = '';
        $setting_footer = '';
        $setting_posts_per_page = '';
    }

// Print header
    $aPlaceHolders = $addBracket(
        'DISPLAY_PREVIOUS_NEXT_LINKS',
        'NEXT_PAGE_LINK',
        'NEXT_LINK',
        'PREVIOUS_PAGE_LINK',
        'PREVIOUS_LINK',
        'OUT_OF',
        'TEXT_AT',
        'OF',
        'PREVIOUS_LABEL',
        'PREVIOUS_PAGE',
        'DISPLAY_PREV',
        'NEXT_LABEL',
        'NEXT_PAGE',
        'DISPLAY_NEXT'
    );

    if ($display_previous_next_links == 'none') {
        $aReplacements = [
            $display_previous_next_links
        ];
    } else {
        $aReplacements = [
            $display_previous_next_links,
            $next_page_link,
            $next_link,
            $previous_page_link,
            $previous_link,
            $out_of,
            $MOD_NEWS['TEXT_AT'],
            $of,
            $TEXT['PREVIOUS_PAGE'],
            $sPrevLink,
            (empty($sPrevLink) ? 'hidden' : 'visible'),
            $TEXT['NEXT_PAGE'],
            $sNextLink,
            (empty($sNextLink) ? 'hidden' : 'visible'),
        ];
    }
    $sOut = (str_replace($aPlaceHolders, $aReplacements, $setting_header));
    print $sOut;
    if ($total_num > 0) // $num_posts
    {
        $sPageLink .= (($position>0) ? '?p='.$position : '');
        if ($query_extra != '') {
            echo ('<div class="selected-group-title">'
                 .'<a href="'.htmlspecialchars(strip_tags($sPageLink))
                 .'">'.PAGE_TITLE.'</a> &raquo; '.$groups[$_GET['g']]['title']
                 .'</div>'.PHP_EOL
            );
        }

        $aPlaceHolders = $addBracket(
            'PAGE_TITLE',
            'GROUP_ID',
            'GROUP_TITLE',
            'GROUP_IMAGE',
            'DISPLAY_GROUP',
            'DISPLAY_IMAGE',
            'POST_ID',
            'TITLE',
            'SHORT',
            'MODI_DATE',
            'MODI_TIME',
            'MODIFIED_DATE',
            'MODIFIED_TIME',
            'CREATED_DATE',
            'CREATED_TIME',
            'PUBLISHED_DATE',
            'PUBLISHED_TIME',
            'LINK',
            'SHOW_READ_MORE',
            'TEXT_READ_MORE',
            'USER_ID',
            'USERNAME',
            'DISPLAY_NAME',
            'EMAIL',
            'TEXT_POSTED_BY',
            'TEXT_ON',
            'TEXT_AT',
        );
        $i=0;
        while (($post = $query_posts->fetchRow( MYSQLI_ASSOC )))
        {
            ++$i;
            if (
                isset($groups[$post['group_id']]['active']) AND
                $groups[$post['group_id']]['active'] != false
            ) { // Make sure parent group is active
                $uid = $post['posted_by']; // User who last modified the post
                // Workout date and time of last modified post
                if ($post['published_when'] === '0') {
                    $post['published_when'] = time();
                }

                if ($post['published_when'] > $post['posted_when']) {
                    $post_date = PreCheck::getStrftime($sDateFormat, $post['published_when']+TIMEZONE);
                    $post_time = PreCheck::getStrftime($sTimeFormat, $post['published_when']+TIMEZONE);
                } else {
                    $post_date = PreCheck::getStrftime($sDateFormat, $post['posted_when']+TIMEZONE);
                    $post_time = PreCheck::getStrftime($sTimeFormat, $post['posted_when']+TIMEZONE);
                }
                $publ_date     = PreCheck::getStrftime($sDateFormat,$post['published_when']+TIMEZONE);
                $publ_time     = PreCheck::getStrftime($sTimeFormat,$post['published_when']+TIMEZONE);
                $modified_date = PreCheck::getStrftime($sDateFormat,$post['modified_when']+TIMEZONE);
                $modified_time = PreCheck::getStrftime($sTimeFormat,$post['modified_when']+TIMEZONE);
                // Work-out the post link with shorturl
                $post_link     = $oReg->AppUrl.$oReg->PagesDir.ltrim($post['link'],'/').$oReg->PageExtension;
                $shortPostlink = $oReg->AppUrl.''.ltrim($post['link'],'/').'/';
                $post_link     = (\is_readable($oReg->AppPath.'short.php') ? $shortPostlink : $post_link);
                $post_link_path= $oReg->AppPath.$oReg->PagesDir.ltrim($post['link'],'/').$oReg->PageExtension;
//                $oApp->
                $create_date    = PreCheck::getStrftime($sDateFormat, $post['created_when']+TIMEZONE);
                $create_time    = PreCheck::getStrftime($sTimeFormat, $post['created_when']+TIMEZONE);
                if (isset($_GET['p']) && $position > 0) {
                    $post_link .= '?p='.$position;
                }
                if (isset($_GET['g']) && is_numeric($_GET['g'])) {
                    if (isset($_GET['p']) && $position > 0) {
                        $post_link .= '&amp;';
                    } else {
                        $post_link .= '?';
                    }
                    $post_link .= 'g='.$_GET['g'];
                }
                // Get group id, title, and image
                $group_id      = $post['group_id'];
                $group_title   = $groups[$group_id]['title'];
                $group_image   = $groups[$group_id]['image'];
                $display_image = ($group_image == '') ? "none" : "inherit";
                $display_group = ($group_id == 0) ? 'none' : 'inherit';
                if ($group_image != "") {
                    $group_image= '<img src="'.$group_image.'" alt="'.$group_title.'" />';
                }
                // Replace [wblink--PAGE_ID--] with real link
                $short = ($post['content_short']);
                $short = OutputFilterApi('ReplaceSysvar', $short);
                // Replace vars with values
//                $post_long_len = mb_strlen($post['content_long']);
//                $bIsEmptyLongContent = (bool)( $post_long_len == 0);
                $bIsEmptyLongContent = !(bool)mb_strlen(
                    trim(preg_replace('/^\s*?<(p|div)>(.*)?<\/\s*?\1>$/si', '\2', $post['content_long']))
                );
                // set replacements for exchange
                $aReplacements = [
                    PAGE_TITLE,
                    $group_id,
                    $group_title,
                    $group_image,
                    $display_group,
                    $display_image,
                    $post['post_id'],
                    $post['title'],
                    $short,
                    $post_date,
                    $post_time,
                    $modified_date,
                    $modified_time,
                    $create_date,
                    $create_time,
                    $publ_date,
                    $publ_time,
                ];

                if (isset($users[$uid]['username']) && $users[$uid]['username'] != '')
                {
                    if ($bIsEmptyLongContent) {
                        $aReplacements[] = '#" onclick="javascript:void(0);return false;" style="cursor:no-drop;';
                        $aReplacements[] = 'hidden';
                        $aReplacements[] = '';
                        $aReplacements[] = $uid;
                        $aReplacements[] = $users[$uid]['username'];
                        $aReplacements[] = $users[$uid]['display_name'];
                        $aReplacements[] = $users[$uid]['email'];
                        $aReplacements[] = $MOD_NEWS['TEXT_POSTED_BY'];
                        $aReplacements[] = $MOD_NEWS['TEXT_ON'];
                        $aReplacements[] = $MOD_NEWS['TEXT_AT'];
                    } else {
                        $aReplacements[] = $post_link;
                        $aReplacements[] = 'visible';
                        $aReplacements[] = $MOD_NEWS['TEXT_READ_MORE'];
                        $aReplacements[] = $uid;
                        $aReplacements[] = $users[$uid]['username'];
                        $aReplacements[] = $users[$uid]['display_name'];
                        $aReplacements[] = $users[$uid]['email'];
                        $aReplacements[] = $MOD_NEWS['TEXT_POSTED_BY'];
                        $aReplacements[] = $MOD_NEWS['TEXT_ON'];
                        $aReplacements[] = $MOD_NEWS['TEXT_AT'];
                    }

                } else {
                    if ($bIsEmptyLongContent) {
                        $aReplacements[] = '#" onclick="javascript:void(0);return false;" style="cursor:no-drop;';
                        $aReplacements[] = 'hidden';
                    } else {
                        $aReplacements[] = $post_link;
                        $aReplacements[] = 'visible';
                        $aReplacements[] = $MOD_NEWS['TEXT_READ_MORE'];
                    }
                }
                print (str_replace($aPlaceHolders, $aReplacements, $setting_post_loop));
            }
//            if ( $setting_posts_per_page == $i ) { break; }
            if ($setting_posts_per_page && $setting_posts_per_page + $position <= $i) { break; }
        } // end while posts
    }

    // Print footer
    $aPlaceHolders = $addBracket(
        'DISPLAY_PREVIOUS_NEXT_LINKS',
        'NEXT_PAGE_LINK',
        'NEXT_LINK',
        'PREVIOUS_PAGE_LINK',
        'PREVIOUS_LINK',
        'OUT_OF',
        'TEXT_AT',
        'OF',
        'PREVIOUS_LABEL',
        'PREVIOUS_PAGE',
        'DISPLAY_PREV',
        'NEXT_LABEL',
        'NEXT_PAGE',
        'DISPLAY_NEXT'
    );
    if ($display_previous_next_links == 'none') {
        $aReplacements = [
            $display_previous_next_links,
            '','','','','','',''
        ];
    } else {
        $aReplacements = [
            $display_previous_next_links,
            $next_page_link,
            $next_link,
            $previous_page_link,
            $previous_link,
            $out_of,
            $MOD_NEWS['TEXT_AT'],
            $of,
            $TEXT['PREVIOUS_PAGE'],
            $sPrevLink,
            (empty($sPrevLink) ? 'hidden' : 'visible'),
            $TEXT['NEXT_PAGE'],
            $sNextLink,
            (empty($sNextLink) ? 'hidden' : 'visible'),
        ];
    }
    $sOut = (str_replace($aPlaceHolders, $aReplacements, $setting_footer));
    print $sOut;
} elseif(isset($post_id) && is_numeric($post_id)) {
    if (isset($post_section) && ($post_section == $section_id)) {
        // Get settings
        $setting_post_header   = $setting_post_footer     = $setting_comments_header= '';
        $setting_comments_loop = $setting_comments_footer = '';
/*
        $aSql[6] = 'SELECT `post_header`, `post_footer`, `comments_header`, `comments_loop`, `comments_footer` '
             . 'FROM `'.TABLE_PREFIX.'mod_news_settings` '
             . 'WHERE `section_id`='.(int)$section_id;
*/
    $aSql[6]  = 'SELECT `ns`.*,`nl`.*  FROM `'.TABLE_PREFIX.'mod_news_settings` `ns` '.$sPHP_EOL
          . 'INNER JOIN `'.TABLE_PREFIX.'mod_news_layouts` `nl` ON `ns`.`layout_id` = `nl`.`id` '.$sPHP_EOL
          . 'WHERE `ns`.`section_id` = '.(int)$section_id;
        if (($resSettings = $database->query($aSql[6])) ) {
            if (($recSettings = $resSettings->fetchRow( MYSQLI_ASSOC ))) {
                foreach ($recSettings as $key=>$val) {
                    ${'setting_'.$key} = $val;
                    $aDebugArray[$key] = ${'setting_'.$key};
                }
            }
        }

        // Get page info
        $aSql[7] = 'SELECT `link` FROM `'.TABLE_PREFIX.'pages` '
             . 'WHERE `page_id`='.PAGE_ID;
        $query_page = $database->query($aSql[7]);
        if ($query_page->numRows() > 0) {
            $page = $query_page->fetchRow( MYSQLI_ASSOC );
//            $page_link = $sRecallUrl;
            $page_link = WB_URL.PAGES_DIRECTORY.$page['link'].PAGE_EXTENSION;
            if (isset($_GET['p']) && $position > 0) {
                $page_link .= '?p='.$_GET['p'];
            }

            if (isset($_GET['g']) && is_numeric($_GET['g'])) {
                if (isset($_GET['p']) && $position > 0) {
                    $page_link .= '&amp;';
                } else {
                    $page_link .= '?';
                }
                $page_link .= 'g='.$_GET['g'];
            }
//            $aParseUrl = parse_url($page_link);
        } else {
            exit($MESSAGE['PAGES_NOT_FOUND']);
        }

        // Get post info  published_until
        $iNow = time();
        $aSql[8] = 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_posts` '
             . 'WHERE `post_id`='.(int)$post_id.' '.PHP_EOL
             .   'AND `active`=1 '.PHP_EOL
             .   'AND (('.$iNow.' BETWEEN `published_when` AND `published_until`) '.PHP_EOL
             .    'OR  ('.$iNow.' > `published_when` AND `published_until`=0))'.PHP_EOL;
        $query_post = $database->query($aSql[8]);
        if ($post = $query_post->fetchRow( MYSQLI_ASSOC )) {
            if (isset($groups[$post['group_id']]['active'])
                && $groups[$post['group_id']]['active'] != false
            ) { // Make sure parent group is active
                $uid = $post['posted_by']; // User who last modified the post
                // Workout date and time of last modified post
                if ($post['published_when'] === '0') {
                    $post['published_when'] = time();
                }
                if ($post['published_when'] > $post['posted_when']) {
                    $post_date = PreCheck::getStrftime($sDateFormat, $post['published_when']+TIMEZONE);
                    $post_time = PreCheck::getStrftime($sTimeFormat, $post['published_when']+TIMEZONE);
                } else {
                    $post_date = PreCheck::getStrftime($sDateFormat, $post['posted_when']+TIMEZONE);
                    $post_time = PreCheck::getStrftime($sTimeFormat, $post['posted_when']+TIMEZONE);
                }
                $publ_date      = PreCheck::getStrftime($sDateFormat,$post['published_when']+TIMEZONE);
                $publ_time      = PreCheck::getStrftime($sTimeFormat,$post['published_when']+TIMEZONE);
                $modified_date  = PreCheck::getStrftime($sDateFormat,$post['modified_when']+TIMEZONE);
                $modified_time  = PreCheck::getStrftime($sTimeFormat,$post['modified_when']+TIMEZONE);
                // Work-out the post link
//                $post_link = WB_URL.PAGES_DIRECTORY.$post['link'].PAGE_EXTENSION;
//                $post_link_path = WB_PATH.PAGES_DIRECTORY.$post['link'].PAGE_EXTENSION;
                    $create_date    = PreCheck::getStrftime($sDateFormat, $post['created_when']+TIMEZONE);
                    $create_time    = PreCheck::getStrftime($sTimeFormat, $post['created_when']+TIMEZONE);
                // Get group id, title, and image
                $group_id       = $post['group_id'];
                $group_title    = $groups[$group_id]['title'];
                $group_image    = $groups[$group_id]['image'];
                $display_image  = ($group_image == '') ? "none" : "inherit";
                $display_group  = ($group_id == 0) ? 'none' : 'inherit';
                if (($group_id > 0)) {
                    $group_link = $page_link;
                    $aParseUrl  = parse_url($page_link);
                    $sQuery     = ($aParseUrl['query']??'');
                    if ((strripos($sQuery,'p=')!==false) && (strripos($sQuery,'g=')!==false)){
                        /* do nothing */
                    }elseif ((strripos($sQuery,'p=')!==false) && (strripos($sQuery,'g=')===false)){
                        $group_link .= '&amp;g='.$group_id;
                    } elseif(empty($sQuery)) {
                        $group_link .= '?g='.$group_id;
                    }
                }
                $post_short = ($post['content_short']);
                $post_short = OutputFilterApi('ReplaceSysvar', $post_short);
                if (!empty($group_image) != "") {
                    $group_image= '<img src="'.$group_image.'" alt="'.$group_title.'" />';
                }

                $aPlaceHolders = $addBracket(
                    'PAGE_TITLE',
                    'GROUP_ID',
                    'GROUP_TITLE',
                    'GROUP_IMAGE',
                    'DISPLAY_GROUP',
                    'DISPLAY_IMAGE',
                    'POST_ID',
                    'TITLE',
                    'SHORT',
                    'BACK',
                    'GROUP_BACK',
                    'TEXT_BACK',
                    'TEXT_LAST_CHANGED',
                    'MODI_DATE',
                    'TEXT_AT',
                    'MODI_TIME',
                    'MODIFIED_DATE',
                    'MODIFIED_TIME',
                    'CREATED_DATE',
                    'CREATED_TIME',
                    'PUBLISHED_DATE',
                    'PUBLISHED_TIME',
                    'TEXT_POSTED_BY',
                    'TEXT_ON',
                    'USER_ID',
                    'USERNAME',
                    'DISPLAY_NAME',
                    'EMAIL'
                );
                $aReplacements = [
                    PAGE_TITLE,
                    $group_id,
                    $group_title,
                    $group_image,
                    $display_group,
                    $display_image,
                    $post['post_id'],
                    $post['title'],
                    $post_short,
                    $page_link,
                    (isset($group_link) ? $group_link : ''),
                    $MOD_NEWS['TEXT_BACK'],
                    $MOD_NEWS['TEXT_LAST_CHANGED'],
                    $post_date,
                    $MOD_NEWS['TEXT_AT'],
                    $post_time,
                    $modified_date,
                    $modified_time,
                    $create_date,
                    $create_time,
                    $publ_date,
                    $publ_time,
                    $MOD_NEWS['TEXT_POSTED_BY'],
                    $MOD_NEWS['TEXT_ON']
                ];
                if (isset($users[$uid]['username']) && $users[$uid]['username'] != '') {
                    $aReplacements[] = $uid;
                    $aReplacements[] = $users[$uid]['username'];
                    $aReplacements[] = $users[$uid]['display_name'];
                    $aReplacements[] = $users[$uid]['email'];
                }
                $post_long = ($post['content_long'] != '') ? $post['content_long'] : $post['content_short'];
                $post_long = OutputFilterApi('ReplaceSysvar', $post_long);
                print (str_replace($aPlaceHolders, $aReplacements, $setting_post_header));
                print $post_long;
                print $tmpPostFooter = (str_replace($aPlaceHolders, $aReplacements, $setting_post_footer));
            }
        } else {
                $aPlaceHolders = $addBracket(
                    'BACK',
                    'TEXT_BACK',
                    'TEXT_LAST_CHANGED',
                    'TEXT_AT',
                    'MODI_DATE',
                    'MODI_TIME'
                );
                $aReplacements = [
                    $page_link,
                    $MOD_NEWS['TEXT_BACK'],
//                    $MESSAGE['FRONTEND_SORRY_NO_ACTIVE_SECTIONS'],
                    '',
                    '',
                    '',
                    ''
                ];
                print (str_replace($aPlaceHolders, $aReplacements, $setting_post_footer));
        }

        // Show comments section if we have to
        if (isset($post) && (isset($oApp) && (($post['commenting'] == 'private') &&
              ($oApp->is_authenticated() == true)) ||
              ($post['commenting'] == 'public'))
        ) {
            // Print comments header
            $aPlaceHolders = $addBracket(
                'ADD_COMMENT_URL',
                'TEXT_COMMENTS'
            );
            $commentPageLink  = '/modules/'.$sAddonName.'/comment'.PAGE_EXTENSION.'?post_id='.$post_id.'&page_id='.$page_id.'&section_id='.$section_id;
//            $commentShortlink = '/modules/'.$sAddonName.'/comment.php?post_id='.$post_id.'&page_id='.$page_id.'&section_id='.$section_id;
//            $bShortlink       = is_readable(WB_PATH.'/short.php');
            $aReplacements = [
                WB_URL.$commentPageLink,
                $MOD_NEWS['TEXT_COMMENTS']
            ];

            print (str_replace($aPlaceHolders, $aReplacements, $setting_comments_header));
            // Query for comments
            $iNumberOfComments = 0;
            $aPlaceHolders = $addBracket(
                'COMMENT',
                'TITLE',
                'TEXT_ON',
                'DATE',
                'TEXT_AT',
                'TIME',
                'TEXT_BY',
                'USER_ID',
                'USERNAME',
                'DISPLAY_NAME',
                'EMAIL'
            );
            $aSql[9] = '
              SELECT * FROM `'.TABLE_PREFIX.'mod_news_comments`
              WHERE `post_id` = '.$post_id.'
              AND `active` = 1
              ORDER BY `commented_when` ASC
              ';
            if (($query_comments = $database->query($aSql[9]))) {
                while (($comment = $query_comments->fetchRow( MYSQLI_ASSOC ))) {
                    $iNumberOfComments++;
                    // Display Comments without slashes, but with new-line characters
                    $comment['comment'] = nl2br($oApp->strip_slashes($comment['comment']));
                    $comment['title'] = $oApp->strip_slashes($comment['title']);
                    // Print comments loop
                    $commented_date = PreCheck::getStrftime($sDateFormat, $comment['commented_when']+TIMEZONE);
                    $commented_time = PreCheck::getStrftime($sTimeFormat, $comment['commented_when']+TIMEZONE);
                    $uid = $comment['commented_by'];
                    $aReplacements = array(
                        $comment['comment'],
                        $comment['title'],
                        $MOD_NEWS['TEXT_ON'],
                        $commented_date,
                        $MOD_NEWS['TEXT_AT'],
                        $commented_time,
                        $MOD_NEWS['TEXT_BY']
                    );
                    if (isset($users[$uid]['username']) && $users[$uid]['username'] != '') {
                        $aReplacements[] = $uid;
                        $aReplacements[] = $users[$uid]['username'];
                        $aReplacements[] = $users[$uid]['display_name'];
                        $aReplacements[] = $users[$uid]['email'];
                    } else {
                        $aReplacements[] = '0';
                        $aReplacements[] = strtolower($TEXT['UNKNOWN']);
                        $aReplacements[] = $TEXT['UNKNOWN'];
                    }
                    print (str_replace($aPlaceHolders, $aReplacements, $setting_comments_loop));
                } // end while comment
            }
            if (! $iNumberOfComments) {
                // Say no comments found
                $content = '';
                $aReplacements = array(
                    $MOD_NEWS['NO_COMMENT_FOUND']
                );
                print (str_replace($aPlaceHolders, $aReplacements, $setting_comments_loop));
            }
            // Print comments footer
            $aPlaceHolders = $addBracket(
                'ADD_COMMENT_URL',
                'TEXT_ADD_COMMENT',
                'TEXT_COMMENTS'
            );
            $aReplacements = array(
                WB_URL.$commentPageLink.'&amp;p='.$position,
                $MOD_NEWS['TEXT_ADD_COMMENT'],
                $MOD_NEWS['TEXT_COMMENTS']
            );
            print (str_replace($aPlaceHolders, $aReplacements, $setting_comments_footer));
        }
        if (ENABLED_ASP) {
            $_SESSION['comes_from_view'] = $post_id;
            $_SESSION['comes_from_view_time'] = time();
        }
    }
}
 if ($total_num==0){
?>
   <table class="w3-table">
        <tbody>
          <tr class="w3-section">
            <td class="w3-text-blue-wb w3-large w3-margin"><?php echo $MOD_NEWS['NO_POSTS_FOUND']; ?></td>
          </tr>
        </tbody>
   </table>
<?php
 }
unset($aSql);
unset($addBracket);