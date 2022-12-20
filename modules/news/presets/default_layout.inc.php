<?php
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; }
/* -------------------------------------------------------- */
?><?php
$layout = 'default_layout';
$header = '
<table class="loop-header">
    <tbody>';
$post_loop = '
        <tr id="NL[POST_ID]" class="post-top w3-light-gray">
            <td class="post-title"><a href="[LINK]">[TITLE]</a></td>
            <td class="post-date">[TEXT_POSTED_BY] [DISPLAY_NAME] [TEXT_ON] [PUBLISHED_DATE] [TEXT_AT] [PUBLISHED_TIME]</td>
        </tr>
        <tr>
            <td class="post-short" colspan="2">[SHORT]
                <span style="visibility:[SHOW_READ_MORE];">
                  <a class="readmore" href="[LINK]">[TEXT_READ_MORE]</a>
                </span>
           </td>
        </tr>';
$footer = '
    </tbody>
</table>
<table class="loop-footer">
    <tbody>
        <tr>
           <td class="page-left">[PREVIOUS_PAGE_LINK]</td>
           <td class="page-center">[OF]</td>
           <td class="page-right">[NEXT_PAGE_LINK]</td>
        </tr>
    </tbody>
</table>';
$post_header = '
<table id="NH[POST_ID]" class="post-header">
    <tbody>
        <tr>
            <td><h3>[TITLE]</h3></td>
            <td rowspan="3" style="display: [DISPLAY_IMAGE]">[GROUP_IMAGE]</td>
        </tr>
        <tr>
            <td class="public-info"><b>[TEXT_POSTED_BY] [DISPLAY_NAME] [TEXT_ON] [PUBLISHED_DATE]</b></td>
        </tr>
        <tr style="display: [DISPLAY_GROUP]">
            <td class="group-page"><a href="[BACK]">[PAGE_TITLE]</a> &raquo; <a href="[GROUP_BACK]">[GROUP_TITLE]</a></td>
         </tr>
    </tbody>
</table>';
$post_footer = '
<p>[TEXT_LAST_CHANGED]: [MODI_DATE] [TEXT_AT] [MODI_TIME]</p>
<a href="[BACK]">[TEXT_BACK]</a>';
$comments_header = ('

<table class="comment-header">
    <tbody>');
$comments_loop = ('
        <tr>
            <td class="comment_title">[TITLE]</td>
            <td class="comment_info">[TEXT_BY] [DISPLAY_NAME] [TEXT_ON] [DATE] [TEXT_AT] [TIME]</td>
        </tr>
        <tr>
            <td colspan="2" class="comment-text">[COMMENT]</td>
        </tr>');
$comments_footer = '
    </tbody>
</table>
<br /><a href="[ADD_COMMENT_URL]">[TEXT_ADD_COMMENT]</a>';
$comments_page = '
<h2>[TEXT_COMMENT]</h2>
<h3>[POST_TITLE]</h3>';
