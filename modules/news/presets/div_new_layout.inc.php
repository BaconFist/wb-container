<?php
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden'); echo '403 Forbidden'; \flush(); exit;}
/* -------------------------------------------------------- */
?><?php
$layout = 'div_new_layout';

$header = '
<div class="news-loop-header">';
$post_loop = '
        <div id="NL[POST_ID]" class="post-top">
            <div class="post-title"><a href="[LINK]">[TITLE]</a></div>
            <div class="post-date">[TEXT_POSTED_BY] [DISPLAY_NAME] [TEXT_ON] [PUBLISHED_DATE] [TEXT_AT] [PUBLISHED_TIME]</div>
        </div>
        <div>
            <div class="post-short">[SHORT]
                <span style="visibility:[SHOW_READ_MORE];">
                  <a class="readmore" href="[LINK]">[TEXT_READ_MORE]</a>
                </span>
           </div>
        </div>';
$footer = '
</div>
    <div class="w3-display-container news-container news-loop-footer" style="display:[DISPLAY_PREVIOUS_NEXT_LINKS]">
        <div class="w3-display-left news-third news-left-align">[PREVIOUS_PAGE_LINK]</div>
        <div class="w3-display-middle news-third news-center">[OF]</div>
        <div class="w3-display-right news-third news-right-align">[NEXT_PAGE_LINK]</div>
    </div>';
$post_header = '
<div id="NH[POST_ID]" class="news-post-header">
    <div>
        <div><h3>[TITLE]</h3></div>
        <div style="display: [DISPLAY_IMAGE]">[GROUP_IMAGE]</div>
    </div>
    <div>
        <div class="public-info">
            <b>[TEXT_POSTED_BY] [DISPLAY_NAME] [TEXT_ON] [PUBLISHED_DATE]</b>
        </div>
    </div>
    <div style="display: [DISPLAY_GROUP]">
        <div class="group-page">
            <a href="[BACK]">[PAGE_TITLE]</a> &raquo; <a href="[GROUP_BACK]">[GROUP_TITLE]</a>
        </div>
    </div>
</div>';
$post_footer = '
<p>[TEXT_LAST_CHANGED]: [MODI_DATE] [TEXT_AT] [MODI_TIME]</p>
<a href="[BACK]">[TEXT_BACK]</a>';
$comments_header = ('
<br /><br />
<h2>[TEXT_COMMENTS]</h2>
<div class="news-comment-header">');
$comments_loop = '
    <div>
        <div class="news-comment_title">[TITLE] </div>
        <div class="news-comment_info">[TEXT_BY] [DISPLAY_NAME] [TEXT_ON] [DATE] [TEXT_AT] [TIME]</div>
    </div>
    <div>
        <div class="news-comment-text">[COMMENT]</div>
    </div>';
$comments_footer = '
</div>
<br /><a href="[ADD_COMMENT_URL]">[TEXT_ADD_COMMENT]</a>';
$comments_page = '
<h2>[TEXT_COMMENT]</h2>
<h3>[POST_TITLE]</h3>';
