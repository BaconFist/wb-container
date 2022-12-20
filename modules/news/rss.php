<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: rss.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/rss.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};



// Check that GET values have been supplied
    if(isset($_GET['page_id']) && is_numeric($_GET['page_id'])) {
        $page_id = intval($_GET['page_id']);
    } else {
        // something is gone wrong, send error header
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
        if (preg_match('/fcgi/i', php_sapi_name())) {
            header("Status: 204 No Content"); // RFC7231, Section 6.3.5
        } else {
            header("HTTP/1.0 204  No Content");
        }
        flush();
        exit;
    }

    if(isset($_GET['group_id']) && is_numeric($_GET['group_id'])) {
        $group_id = $_GET['group_id'];
        define('GROUP_ID', $group_id);
    }

// Include WB files
    if (!defined('SYSTEM_RUN')){require(dirname(dirname((__DIR__))).'/config.php');}
    // Create new frontend object
    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
    $wb->page_id = $page_id;
    $wb->getPageDetails();
    $wb->getWebsiteSettings();

//checkout if a charset is defined otherwise use UTF-8
    if (defined('DEFAULT_CHARSET')) {
        $charset = DEFAULT_CHARSET;
    } else {
        $charset='utf-8';
    }

// Sending XML header
    header("Content-type: text/xml; charset=$charset" );

// Header info
// Required by CSS 2.0
    echo '<?xml version="1.0" encoding="'.$charset.'"?>';
?>
<rss version="2.0">
    <channel>
        <title><![CDATA[<?php echo PAGE_TITLE; ?>]]></title>
        <link>http://<?php echo $_SERVER['SERVER_NAME']; ?></link>
        <description><![CDATA[<?php echo PAGE_DESCRIPTION; ?>]]></description>
<?php
// Optional header info
?>
        <language><?php echo strtolower(DEFAULT_LANGUAGE); ?></language>
        <copyright><?php $thedate = date('Y'); $websitetitle = WEBSITE_TITLE; echo "Copyright {$thedate}, {$websitetitle}"; ?></copyright>
        <managingEditor><?php echo SERVER_EMAIL; ?></managingEditor>
        <webMaster><?php echo SERVER_EMAIL; ?></webMaster>
        <category><?php echo WEBSITE_TITLE; ?></category>
        <generator>WebsiteBaker Content Management System</generator>
<?php
// Get news items from database
$time = time();
/*
    $sql  = 'SELECT `order`, `order_field` FROM `'.TABLE_PREFIX.'mod_news_settings` '
          . 'WHERE `section_id` = '.$section_id.' ';
    if (!$oOrder = $database->query($sql)){
        throw new \Exception($database->get_error());
    }
    $aOrder = $oOrder->fetchRow(MYSQLI_ASSOC);
*/
//Query
    $sql='SELECT * FROM `'.TABLE_PREFIX.'mod_news_posts` '
        .'WHERE `page_id`='.(int)$page_id.' '
        .       (isset($group_id) ? 'AND `group_id`='.(int)$group_id.' ' : '')
        .       'AND `active` = 1 '
        .       'AND `title` != \'\' '
        .       'AND (`published_when`  = 0 OR `published_when` <= '.$time.') '
        .       'AND (`published_until` = 0 OR `published_until` >= '.$time.') '
        .'ORDER BY published_when DESC';

    $result = $database->query($sql);

//Generating the news items
while($item = $result->fetchRow( MYSQLI_ASSOC )){
    $description = stripslashes($item["content_short"]);
    $description = OutputFilterApi('WbLink|ReplaceSysvar', $description);
?>
    <item>
        <title><![CDATA[<?php echo stripslashes($item["title"]); ?>]]></title>
        <description><![CDATA[<?php echo $description; ?>]]></description>
        <link><?php echo WB_URL.PAGES_DIRECTORY.$item["link"].PAGE_EXTENSION; ?></link>
        <pubDate><?PHP echo date('r', $item["published_when"]); ?></pubDate>
        <guid><?php echo WB_URL.PAGES_DIRECTORY.$item["link"].PAGE_EXTENSION; ?></guid>
    </item>
<?php } ?>
    </channel>
</rss>