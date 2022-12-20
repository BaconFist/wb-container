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
 * @requirements    PHP 5.6 and higher
 * @version         $Id: view.php 290 2019-03-26 16:01:51Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/menu_link/view.php $
 * @lastmodified    $Date: 2019-03-26 17:01:51 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

try {

    $sAbsAddonPath = str_replace(['\\','\\\\','//'],'/',__DIR__).'/';
    $sAddonName = \basename($sAbsAddonPath);
// check if module language file exists for the language set by the user (e.g. DE, EN)
    if (\is_readable($sAbsAddonPath.'languages/EN.php')) {require($sAbsAddonPath.'languages/EN.php');}
    if (\is_readable($sAbsAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAbsAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAbsAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAbsAddonPath.'languages/'.LANGUAGE.'.php');}

// redirect menu-link
    $this_page_id = PAGE_ID;

    $sql  = 'SELECT `module`, `block` FROM `'.TABLE_PREFIX.'sections` '
          . 'WHERE `page_id` = '.(int)$this_page_id.' '
          .   'AND `module` = \''.$database->escapeString('menu_link').'\'';
    if ($query_this_module = $database->query($sql)){;}
    // This is a menu_link. Get link of target-page and redirect
    if ($query_this_module->numRows() == 1)
    {
        // get target_page_id
        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_menu_link` '
              . 'WHERE `page_id` = '.(int)$this_page_id.' '
              . '';

        if ($query_tpid = $database->query($sql)){}
        if ($query_tpid->numRows() == 1){
            if (!is_null($res = $query_tpid->fetchRow(MYSQLI_ASSOC))){};
            $target_page_id = $res['target_page_id'];
            $redirect_type = $res['redirect_type'];
            $anchor = (($res['anchor'] != '0') ? '#'.(string)$res['anchor'] : '');
            $extern = OutputFilterApi('ReplaceSysvar', $res['extern']);
            // set redirect-type
            if ($redirect_type == 301){
                 \header('HTTP/1.1 301 Moved Permanently');
            }
            $target_url = '';
            if ($target_page_id == -1){
                if ($extern != WB_URL.'/'){
                    $target_url = $extern.$anchor;
                }
            } else {
                // get link of target-page
                $sql  = 'SELECT `link` FROM `'.TABLE_PREFIX.'pages` '
                      . 'WHERE `page_id` = '.(int)$target_page_id.' '
                      .   'AND `visibility` NOT IN (\'none\',\'deleted\')';
                $target_page_link = $database->get_one($sql);
                //  && $wb->isPageActive((int)$target_page_id)
                if (($target_page_link != null)){
                    $sShortUrl  = WB_URL.$target_page_link.'/'.$anchor;
                    $sScriptUrl = WB_URL.PAGES_DIRECTORY.$target_page_link.PAGE_EXTENSION.$anchor;
                    $target_url = (\is_readable(WB_PATH.DIRECTORY_SEPARATOR.'short.php') ? $sShortUrl : $sScriptUrl);
                } else {
                    \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
                    echo '404 File not found'; \flush(); exit;
                }
            }
            \header('Location: '.$target_url);
            exit;
        }
    } else { ?>
<a href="<?php echo WB_URL; ?>">
<?php echo $MOD_MENU_LINK['TEXT']; ?>
</a>
<?php }

} catch (\Exception $ex) {
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
