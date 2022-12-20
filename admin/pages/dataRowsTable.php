
            <table class="pages_view " >
              <tbody>
                <tr>
                    <td style="width: 0px;height: auto;text-align: center;" class="w3-check"></td>
                    <td class="level_<?php echo $page['level']; ?> w3-pointer" style="width: 32px; padding-left: <?php echo $iPaddingLeft; ?>px;">
<?php
                        if (($display_plus == true)) {
                            $sToggleId = 'p'.$page['page_id'];
                            $sToggleIcon = (isset($_COOKIE['p'.$page['page_id']]) && $_COOKIE['p'.$page['page_id']] === '1' ? 'minus' : 'plus');
?>
                          <a onclick="toggle_visibility('p<?php echo $page['page_id']; ?>');" title="<?php echo $TEXT['EXPAND'].'/'.$TEXT['COLLAPSE']; ?>">
                              <img src="<?php echo THEME_URL; ?>/images/<?php echo $sToggleIcon; ?>_16.png" onclick="toggle_plus_minus('<?php echo $page['page_id']; ?>');" id="plus_minus_<?php echo $page['page_id']; ?>" alt="+" />
                          </a>
<?php
                        } else {
?>
                        <img src="<?php echo THEME_URL; ?>/images/blank.gif" alt="" width="16" />
<?php
                        }
?>
                    </td>
                    <?php if ($admin->get_permission('pages_modify') && ($can_modify == true)) { ?>
                    <td class="list_menu_title">
                        <a href="<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo  $page['page_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
                            <?php if ($page['visibility'] == 'public') { ?>
                                <img src="<?php echo THEME_URL; ?>/images/visible_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['PUBLIC']; ?>" class="page_list_rights" />
                            <?php } elseif ($page['visibility'] == 'private') { ?>
                                <img src="<?php echo THEME_URL; ?>/images/private_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['PRIVATE']; ?>" class="page_list_rights" />
                            <?php } elseif ($page['visibility'] == 'registered') { ?>
                                <img src="<?php echo THEME_URL; ?>/images/keys_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['REGISTERED']; ?>" class="page_list_rights" />
                            <?php } elseif ($page['visibility'] == 'hidden') { ?>
                                <img src="<?php echo THEME_URL; ?>/images/hidden_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['HIDDEN']; ?>" class="page_list_rights" />
                            <?php } elseif ($page['visibility'] == 'none') { ?>
                                <img src="<?php echo THEME_URL; ?>/images/none_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['NONE']; ?>" class="page_list_rights" />
                            <?php } elseif ($page['visibility'] == 'deleted') { ?>
                                <img src="<?php echo THEME_URL; ?>/images/deleted_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['DELETED']; ?>" class="page_list_rights" />
                            <?php }
                            echo '<span class="modify_link">'.($page['menu_title']).'</span>'; ?>
                        </a>
                    </td>
                    <?php } else { ?>
                    <td class="list_menu_title">
                        <?php if ($page['visibility'] == 'public') { ?>
                            <img src="<?php echo THEME_URL; ?>/images/visible_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['PUBLIC']; ?>" class="page_list_rights" />
                        <?php } elseif ($page['visibility'] == 'private') { ?>
                            <img src="<?php echo THEME_URL; ?>/images/private_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['PRIVATE']; ?>" class="page_list_rights" />
                        <?php } elseif ($page['visibility'] == 'registered') { ?>
                            <img src="<?php echo THEME_URL; ?>/images/keys_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['REGISTERED']; ?>" class="page_list_rights" />
                        <?php } elseif ($page['visibility'] == 'hidden') { ?>
                            <img src="<?php echo THEME_URL; ?>/images/hidden_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['HIDDEN']; ?>" class="page_list_rights" />
                        <?php } elseif($page['visibility'] == 'none') { ?>
                            <img src="<?php echo THEME_URL; ?>/images/none_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['NONE']; ?>" class="page_list_rights" />
                        <?php } elseif ($page['visibility'] == 'deleted') { ?>
                            <img src="<?php echo THEME_URL; ?>/images/deleted_16.png" alt="<?php echo $TEXT['VISIBILITY']; ?>: <?php echo $TEXT['DELETED']; ?>" class="page_list_rights" />
                        <?php }
                        echo ($page['menu_title']); ?>
                    </td>
                    <?php } ?>
                    <td class="list_page_title">
                        <?php echo ($page['page_title']); ?>
                    </td>
                    <td class="list_page_id">
                        <?php echo $page['page_id']; ?>
                    </td>
                    <td class="list_actions">
<?php
            if (!in_array($page['visibility'], ['deleted','none']) && $admin->isPageActive($page['page_id'])) {
?>
                        <a href="<?php echo $admin->page_link($page['link']); ?>" title="<?php echo $TEXT['VIEW']; ?>" target="_blank" rel="noopener" >
                            <img src="<?php echo THEME_URL; ?>/images/view_16.png" alt="<?php echo $TEXT['VIEW']; ?>" />
                        </a>
                        <?php } ?>
                    </td>
                    <td class="list_actions">
                        <?php if ($page['visibility'] != 'deleted') { ?>
                            <?php if ($admin->get_permission('pages_settings') && ($can_modify == true)) { ?>
                            <a href="<?php echo ADMIN_URL; ?>/pages/settings.php?page_id=<?php echo $page['page_id']; ?>" title="<?php echo $TEXT['SETTINGS']; ?>">
                                <img src="<?php echo THEME_URL; ?>/images/modify_16.png" alt="<?php echo $TEXT['SETTINGS']; ?>" />
                            </a>
                            <?php } ?>
                        <?php } else { ?>
                            <a href="<?php echo ADMIN_URL; ?>/pages/restore.php?page_id=<?php echo $page['page_id']; ?>" title="<?php echo $TEXT['RESTORE']; ?>">
                                <img src="<?php echo THEME_URL; ?>/images/restore_16.png" alt="<?php echo $TEXT['RESTORE']; ?>" />
                            </a>
                        <?php } ?>
                    </td>
                    <!-- MANAGE SECTIONS AND DATES BUTTONS -->
                    <td class="list_actions">
<?php
                    // Work-out if we should show the "manage dates" link
                    if (MANAGE_SECTIONS == 'enabled' && $admin->get_permission('pages_modify')==true && $can_modify==true)
                    {
                        $bShowSection = false;
                        $sql  = 'SELECT `publ_start`, `publ_end` FROM `'.TABLE_PREFIX.'sections` '
                              . 'WHERE `page_id` = '.$page['page_id'].' AND `module` != \'menu_link\' ';
                        // $query_sections = $database->query("SELECT publ_start, publ_end FROM ".TABLE_PREFIX."sections WHERE page_id = '{$page['page_id']}' AND module != 'menu_link'");
                        if (($query_sections = $database->query($sql)) )
                        {
                            $mdate_display=false;
                            while($mdate_res = $query_sections->fetchRow(MYSQLI_ASSOC))
                            {
                                $bShowSection = true;
                                if ($mdate_res['publ_start']!='0' || ($mdate_res['publ_end']!='2147483647')&&($mdate_res['publ_end']>=Time()))
                                {
                                    $mdate_display=true;
                                    break;
                                }
                            }
                            if ($bShowSection) {
                            if($mdate_display==1)
                            {
                                $file=$admin->page_is_active($page)?"clock_16.png":"clock_red_16.png";
?>
                                <a href="<?php echo ADMIN_URL; ?>/pages/sections.php?page_id=<?php echo $page['page_id']; ?>" title="<?php echo $HEADING['MANAGE_SECTIONS']; ?>">
                                <img src="<?php echo THEME_URL."/images/$file"; ?>" alt="<?php echo $HEADING['MANAGE_SECTIONS']; ?>" />
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo ADMIN_URL; ?>/pages/sections.php?page_id=<?php echo $page['page_id']; ?>" title="<?php echo $HEADING['MANAGE_SECTIONS']; ?>">
                                <img src="<?php echo THEME_URL; ?>/images/noclock_16.png" alt="<?php echo $HEADING['MANAGE_SECTIONS']; ?>" /></a>
                            <?php }} ?>
                        <?php } ?>
                    <?php } ?>
                    </td>
                    <td class="list_actions">
                    <?php if ($page['position'] != 1) { ?>
                        <?php if($page['visibility'] != 'deleted') { ?>
                            <?php if($admin->get_permission('pages_settings') == true && $can_modify == true) { ?>
                            <a href="<?php echo ADMIN_URL; ?>/pages/move_up.php?page_id=<?php echo $page['page_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
                                <img src="<?php echo THEME_URL; ?>/images/up_16.png" alt="<?php echo $TEXT['MOVE_UP']; ?>" />
                            </a>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    </td>
                    <td class="list_actions">
                    <?php if ($page['position'] != $num_pages) { ?>
                        <?php if ($page['visibility'] != 'deleted') { ?>
                            <?php if ($admin->get_permission('pages_settings') == true && $can_modify == true) { ?>
                            <a href="<?php echo ADMIN_URL; ?>/pages/move_down.php?page_id=<?php echo $page['page_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
                                <img src="<?php echo THEME_URL; ?>/images/down_16.png" alt="<?php echo $TEXT['MOVE_DOWN']; ?>" />
                            </a>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    </td>
                    <td class="list_actions">
                        <?php if ($admin->get_permission('pages_delete') && $can_modify == true) { // add IdKey ?>
                        <?php if ($page['visibility'] != 'deleted') { ?>
                        <a onclick="confirm_link('<?php echo $MESSAGE['PAGES_MARKED_CONFIRM']; ?>?','<?php echo ADMIN_URL; ?>/pages/delete.php?page_id=<?php echo $admin->getIDKEY($page['page_id']); ?>');" title="<?php echo $TEXT['DELETE']; ?>">
                            <img src="<?php echo THEME_URL; ?>/images/delete_16.png" alt="<?php echo $TEXT['DELETE']; ?>" />
                        </a>
                        <?php } else {?>
                        <a onclick="confirm_link('<?php echo $MESSAGE['PAGES_DELETE_CONFIRM']; ?>?','<?php echo ADMIN_URL; ?>/pages/delete.php?page_id=<?php echo $admin->getIDKEY($page['page_id']); ?>');" title="<?php echo $TEXT['DELETE']; ?>">
                            <img src="<?php echo THEME_URL; ?>/images/delete_16.png" alt="<?php echo $TEXT['DELETE']; ?>" />
                        </a>
                        <?php }}?>
                    </td>
                    <?php
                    // eggsurplus: Add action to add a page as a child
                    ?>
                    <td class="list_actions">
                        <?php if (($admin->get_permission('pages_add') && ($page['visibility'] != 'deleted'))) { ?>
                        <a onclick="add_child_page('<?php echo $page['page_id']; ?>');" title="<?php echo $HEADING['ADD_CHILD_PAGE']; ?>">
                            <img src="<?php echo THEME_URL; ?>/images/siteadd.png" id="addpage_<?php echo $page['page_id']; ?>" alt="Add Child Page" />
                        </a>
                        <?php } ?>
                    </td>
                    <td class="list_actions">
                        <?php echo $page['language']; ?>
                    </td>
    <?php if (@DEBUG) { ?>
    <!--   -->
                    <td class="list_actions">
                        <?php echo $page['position']; ?>
                    </td>
<?php
    }
                    // end [IC] jeggers 2009/10/14: Add action to add a page as a child
?>
                </tr>
              </tbody>
            </table>