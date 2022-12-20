<?php
/**
 *
 * @category        admin
 * @package         pages
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: intro.php 333 2019-04-11 03:32:45Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/pages/intro.php $
 * @lastmodified    $Date: 2019-04-11 05:32:45 +0200 (Do, 11. Apr 2019) $
 *
 */

// Create new admin object
if (!defined('SYSTEM_RUN')){require(dirname(dirname((__DIR__))).'/config.php' ); }
$admin = new admin('Pages', 'pages_intro');
$content = '';

$filename = WB_PATH.PAGES_DIRECTORY.'/intro'.PAGE_EXTENSION;

if (file_exists($filename) && filesize($filename) > 0) {
    $content = file_get_contents( $filename ) ;
} else {
    $content = file_get_contents( ADMIN_PATH.'/pages/html.inc.php' ) ;
}

require_once(WB_PATH . '/include/editarea/wb_wrapper_edit_area.php');
$toolbar = 'search, fullscreen, |, undo, redo, |, select_font, syntax_selection,|,word_wrap, highlight, reset_highlight, |,charmap, |, help';
echo registerEditArea ('content','php',true,'both',true,true,600,450,$toolbar);
function show_wysiwyg_editor($name,$id,$content,$width,$height) {
    echo '<textarea name="'.$name.'" id="'.$id.'" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
}
?><form action="intro2.php" method="post">
<?php print $admin->getFTAN(); ?>
<input type="hidden" name="page_id" value="{PAGE_ID}" />
<table class="form_submit">
    <tr>
        <td colspan="2">
        <?php
            show_wysiwyg_editor('content','content',$content,'100%','500px','utf8mb4');
        ?>
        </td>
    </tr>
    <tr>
        <td class="left">
            <input type="submit" value="<?php echo $TEXT['SAVE'];?>" class="submit" />
        </td>
        <td class="right">
            <input type="button" value="<?php echo $TEXT['CANCEL'];?>" onclick="window.location='index.php';" class="submit" />
        </td>
    </tr>
</table>

</form>
<?php
// Print admin footer
$admin->print_footer();
