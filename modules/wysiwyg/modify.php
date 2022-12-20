<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of modules/wysiwyg/modify.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: modify.php 302 2019-03-27 10:25:40Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

// use

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

// Get page content   htmlspecialchars
    $sql = 'SELECT `content` FROM `'.TABLE_PREFIX.'mod_wysiwyg` WHERE `section_id`='.(int)$section_id;
    if (($content = $database->get_one($sql)) ) {
        $content = OutputFilterApi('ReplaceSysvar', $content);
        $content = \htmlspecialchars($content);
    } else {
        $content = '';
    }
    $name = 'content';
    if (!isset($wysiwyg_editor_loaded)) {
        $wysiwyg_editor_loaded=true;
        if (!\defined('WYSIWYG_EDITOR') OR WYSIWYG_EDITOR=="none" || !\file_exists(WB_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php')) {
            function show_wysiwyg_editor($name,$id,$content,$width,$height) {
                echo '<textarea name="'.$name.'" id="'.$id.'" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
            }
        } else {
            $sTableName = 'mod_wysiwyg';
            $id_list = [];
            require(WB_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php');
        }
    }
?>
<form id="wysiwyg<?php echo $section_id; ?>" action="<?php echo WB_URL; ?>/modules/wysiwyg/save.php" method="post">
    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
    <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
    <input type="hidden" name="inputSection" value="1" />
    <?php echo $admin->getFTAN(); ?>
<?php
    show_wysiwyg_editor('content'.$section_id,'content'.$section_id,$content,'100%','358px',"utf8mb4");
?>
    <table style="padding-bottom: 10px; width: 100%;">
        <tr>
            <td style="margin-left: 1em;">
                <input class="w3-btn w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" name="modify" type="submit" value="<?php echo $TEXT['SAVE']; ?>"  />
                <input class="w3-btn w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" name="pagetree" type="submit" value="<?php echo $TEXT['SAVE'].' &amp; '.$TEXT['BACK']; ?>"  />
            </td>
            <td style="text-align: right;margin-right: 1em;">
                <input class="w3-btn w3-blue-wb w3-hover-red w3--medium w3-btn-min-width" name="cancel" type="button" value="<?php echo $TEXT['CLOSE']; ?>" onclick="window.location = 'index.php';"  />
            </td>
        </tr>
    </table>
</form>
<br />
<?php
#    <div style="overflow: auto;width: 100%;height: 250px;"></div>

