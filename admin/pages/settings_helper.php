<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of settings_helper
 *
 * @category     Core
 * @package      Core package
 * @subpackage   Name of subpackage if needed
 * @copyright    Manuela v.d.Decken
 * @author       Manuela v.d.Decken
 * @license      GNU General Public License 3.0
 * @version      0.0.0
 * @revision     $Revision: 191 $
 * @lastmodified $Date: 2019-01-29 18:14:41 +0100 (Di, 29. Jan 2019) $
 * @since        File available since 18.05.2016
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */

class SettingsHelper
{

/**
 * get a list of possible allowed parent pages
 * @param integer $iParent
 * @param integer $iCurrentPage
 * @param object $admin
 * @param object $database
 * @return array
 */
    static public function getParentPagesList($iParent, $iCurrentPage, $admin, $database)
    {
        $aRetval = [];
        $aNeededFields = ['id', 'title', 'language', 'active'];
        $sql = 'SELECT *, `page_id` `id`, `menu_title` `title` '
             . 'FROM `'.TABLE_PREFIX.'pages` '
             . 'WHERE `parent`='.$iParent.' AND '
             .       '`level`<'.(PAGE_LEVEL_LIMIT - 1).', AND '
             .       '(SELECT FIND_IN_SET('.$iCurrentPage.', `page_trail`))<(`level`+1) '
             . 'ORDER BY `position` ASC';
        if (($oPages = $database->query($sql))) {
            while (($aPage = $oPages->fetchRow(MYSQLI_ASSOC))) {
                // skip this page and its children if page is not visible for current user
                if (!$admin->page_is_visible($aPage)) { continue; }
                // check if current user has admin or owner permissions for this page
                $aPage['active'] = (bool)(
                    $admin->ami_group_member($aPage['admin_groups'])
                    || $admin->is_group_match($admin->getUserId(), $aPage['admin_users'])
                    || $aPage['page_owner'] == $admin->getUserId()
                );
                // Title -'s prefix
                $aPage['title'] = \str_repeat('- ', $aPage['level']).$aPage['title'];
                // if parent = 0 set flag_icon
                $aPage['language'] = $aPage['parent'] ? '' : $aPage['language'];
                // remove unneeded fields from record and add record to retval
                $aRetval[] = \array_intersect_key($aPage, $aNeededFields);
                // check for children
                $aRetval = \array_merge(
                    $aRetval,
                    self::getParentPagesList($aPage['id'], $iCurrentPage, $admin, $database)
                );
            }
        }
        return $aRetval;
    } // end of method getParentPagesList()
/**
 * get a list of possible language reference pages
 * @param integer $iParent
 * @param string $sCurrentPageLanguage
 * @param object $admin
 * @param object $database
 * @return array
 */
    static public function getPageCodeList($iParent, $sCurrentPageLanguage, $admin, $database)
    {
        $aRetval = [];
        // there is no intlRef to choose if current page is set to DEFAULT_LANGUAGE
        if (DEFAULT_LANGUAGE != $sCurrentPageLanguage) {
            $aNeededFields = ['id', 'title', 'language', 'active', 'intlRef'];
            $sql = 'SELECT *, `page_id` `id`, `menu_title` `title`, `page_code` `intlRef` '
                 . 'FROM `'.TABLE_PREFIX.'pages` '
                 . 'WHERE `parent`='.$iParent.' AND '
                 .       '`level`<'.(PAGE_LEVEL_LIMIT - 1).', AND '
                 .       '`language`=\''.DEFAULT_LANGUAGE.'\' '
                 . 'ORDER BY `position` ASC';
            if (($oPages = $database->query($sql))) {
                while (($aPage = $oPages->fetchRow(MYSQLI_ASSOC))) {
                    // skip this page and its children if page is not visible for current user
                    if (!$admin->page_is_visible($aPage)) { continue; }
                    // check if current user has admin or owner permissions for this page
                    $aPage['active'] = (bool)(
                        $admin->ami_group_member($aPage['admin_groups'])
                        || $admin->is_group_match($admin->getUserId(), $aPage['admin_users'])
                        || $aPage['page_owner'] == $admin->getUserId()
                    );
                    // Title -'s prefix
                    $aPage['title'] = \str_repeat('- ', $aPage['level']).$aPage['title'];
                    // if parent = 0 set flag_icon
                    $aPage['language'] = $aPage['parent'] ? '' : $aPage['language'];
                    // remove unneeded fields from record and add record to retval
                    $aRetval[] = \array_intersect_key($aPage, $aNeededFields);
                    // check for children
                    $aRetval = \array_merge(
                        $aRetval,
                        self::getPageCodeList($aPage['id'], $sCurrentPageLanguage, $admin, $database)
                    );
                }
            }
        }
        return $aRetval;
    } // end of method getPageCodeList()
/**
 *
 * @param array $aList
 * @param string $sSortBy1
 * @param string $sSortBy2
 * @return array  the sorted array
 */

    static function orderByColumn($aList)
    {
        $args = \func_get_args();
        \array_shift($args);

    }

    static function doMultiSort($aList, $sSortBy1, $sSortBy2)
    {
        foreach ($aList as $key => $row) {
            ${$sSortBy1}[$key] = $row[$sSortBy1];
            ${$sSortBy2}[$key] = $row[$sSortBy2];
        }
        $iSortFlags = ((\version_compare(PHP_VERSION, '5.4.0', '<'))?SORT_REGULAR:SORT_NATURAL|SORT_FLAG_CASE);
        \array_multisort(${$sSortBy1}, SORT_DESC, ${$sSortBy2}, SORT_ASC, $iSortFlags, $aList);
        return $aList;
    } // end of method doMultiSort()

} // end of class SettingsHelper
