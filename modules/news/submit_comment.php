<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: submit_comment.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/submit_comment.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

// Include config file
if (!defined( 'WB_PATH')){require( dirname(dirname((__DIR__))).'/config.php' ); }

    $sAddonName = basename(__DIR__);

// Create new frontend object
    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
    $oReg     = WbAdaptor::getInstance();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();

    $emailAdmin = (function () use($database,$wb) {
        $retval = $wb->get_email();
        if($wb->getUserId()!='1') {
            $sql  = 'SELECT `email` FROM `'.TABLE_PREFIX.'users` '
                  . 'WHERE `user_id`=\'1\' ';
            $retval = $database->get_one($sql);
        }
        return $retval;
    });

    $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
    $aRequestVars  = [];
// get POST or GET requests, never both at once
    $aVars = $oReg->Request->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oReg->Request->getParam($sName);
    }

    if (is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

    $oTrans->enableAddon('modules\\'.$sAddonName);

// Get page id
//    $page_id = intval(isset($aRequestVars['page_id'])) ? $aRequestVars['page_id'] : (isset($page_id) ? intval($page_id) : 0);
    $page_id    = ($oApp->getIdFromRequest('page_id'));

// Get post_id
//    $post_id = (intval(isset($aRequestVars['post_id'])) ? $aRequestVars['post_id'] : (isset($post_id) ? intval($post_id) : 0));
    $post_id    = ($oApp->getIdFromRequest('post_id'));
// Get section id if there is one
//    $section_id = intval(isset($aRequestVars['section_id'])) ? $aRequestVars['section_id'] : (isset($section_id) ? intval($section_id) : 0);
    $section_id    = ($oApp->getIdFromRequest('section_id'));

    if (isset($_SESSION['MESSAGE'])){unset($_SESSION['MESSAGE']);}
    $sBackCommentLink = WB_URL.'/modules/'.basename(__DIR__).'/comment.php?post_id='.(int)$post_id.'&amp;section_id='.(int)$section_id;
    $t=time();

    if (!SecureTokens::checkFTAN ()){
        $_SESSION['SECURITY_ACCESS'] = true;
        $_SESSION['MESSAGE'][] = $MESSAGE['GENERIC_SECURITY_ACCESS'];
        $oApp->send_header($sBackCommentLink) ;
        exit;
    }

    $position = (isset($aRequestVars['p']) ? $aRequestVars['p'] : '' );

    $title    = (isset($aRequestVars['title']) ? $aRequestVars['title'] : '' );
    $title    = $oApp->StripCodeFromText($title);
    $title    = strip_tags($title);

    $_SESSION['comment_title'] = trim($title);
    if (empty($title)){$_SESSION['MESSAGE'][]  = $MESSAGE['PAGES_BLANK_PAGE_TITLE'];}

    $comment        = (isset($aRequestVars['comment']) ? $aRequestVars['comment'] : '' );
    $comment_date   = (isset($aRequestVars['comment_'.date('W')]) ? $aRequestVars['comment_'.date('W')] : '' );

    if (ENABLED_ASP){
        $comment = $aRequestVars['comment_'.date('W')];
    } else {
        $comment = $aRequestVars['comment'];
    }

    // do not allow droplets in user input!
    $comment = $oApp->StripCodeFromText($comment);
    $comment = strip_tags($comment);
    $_SESSION['comment_body'] = $comment;

    if (empty($comment)){$_SESSION['MESSAGE'][]= $MOD_NEWS['TEXT_ADD_COMMENT'];}

    // Get post link
    $sql  = 'SELECT `link`,`moderated` FROM `'.TABLE_PREFIX.'mod_news_posts` '
          . 'WHERE `post_id` = '.(int)$post_id;
    if (!$oPost = $database->query($sql)){
        throw new \Exception('Unkown Exception '.$database->get_error());
    }
    $aPost  = $oPost->fetchRow(MYSQLI_ASSOC);
    //
    $active = intval(!$aPost['moderated']);
    $commentPageLink  = '/modules/'.$sAddonName.'/comment.php?post_id='.$post_id.'&page_id='.$page_id.'&amp;section_id='.$section_id;
    $commentShortlink = '/modules/'.$sAddonName.'/comment.php?post_id='.$post_id.'&page_id='.$page_id.'&amp;section_id='.$section_id;
    $bShortlink       = is_readable(WB_PATH.'/short.php');

    $sBackPostRel   = PAGES_DIRECTORY.$aPost['link'].PAGE_EXTENSION;
    $sBackPostLink  = WB_URL.($bShortlink ? $commentShortlink : $commentPageLink);
    $sRecallAddress = (isset($aRequestVars['redirect']) ? $aRequestVars['redirect'] : $sBackPostRel );

    $action = intval(isset($aRequestVars['save'])   ? true : false );
    $action = intval(isset($aRequestVars['cancel']) ? true : $action );
        $sql  = 'SELECT `use_data_protection` FROM `'.TABLE_PREFIX.'mod_news_settings` '
              . 'WHERE `section_id` ='.$section_id;
        if ($use_data_protection = $database->get_one($sql)) {
            if (!isset($aRequestVars['data_protection']))
            {
                $_SESSION['MESSAGE'][]= $MOD_NEWS['DSGVO'];
            }
        }
/*  */

        $sql  = 'SELECT `use_captcha` FROM `'.TABLE_PREFIX.'mod_news_settings` '
              . 'WHERE `section_id` ='.$section_id;
        if ($use_captcha = $database->get_one($sql)) {
            $aReplace = ['WEBMASTER_EMAIL'=>$emailAdmin()];
            $MESSAGE['MOD_INCORRECT_CAPTCHA'] = replace_vars($MESSAGE['INCORRECT_CAPTCHA'],$aReplace);
            if (isset($aRequestVars['captcha']) && $aRequestVars['captcha'] != '')
            {
                // Check for a mismatch
                if (!isset($aRequestVars['captcha']) || !isset($_SESSION['captcha']) || $aRequestVars['captcha'] != $_SESSION['captcha'])
                {
                    $_SESSION['captcha_error'] = $MESSAGE['MOD_INCORRECT_CAPTCHA'];
                    $oApp->send_header($sBackCommentLink.'&amp;p='.$position );
                    exit;
                }
            } else {
                $_SESSION['captcha_error'] = $MESSAGE['MOD_INCORRECT_CAPTCHA'];
                $oApp->send_header($sBackCommentLink.'&amp;p='.$position );
                exit;
            }
        } // end captcha
/*
    $aPost = [
        'section_id'=>$section_id,
        'post_id'=>$post_id,
        'page_id'=>$page_id,
        'action' =>$action,
        'ENABLED_ASP'=>ENABLED_ASP,
        'comment_date'=>intval($comment_date),
        'comment'=>$comment,
        'title'=>$title,
    ];

*/
// Check if we should show the form or add a comment
   if (
        $page_id && $section_id  && $post_id  && !$action
          && (( ENABLED_ASP && $comment_date != '')
            || (!ENABLED_ASP && $comment != '' )
              || (!ENABLED_ASP && $title != '' )
        )
      ){
          // Advanced Spam Protection
          if (ENABLED_ASP && (($_SESSION['session_started']+ASP_SESSION_MIN_AGE > $t)  // session too young
              || (!isset($_SESSION['comes_from_view']))// user doesn't come from view.php
                || (!isset($_SESSION['comes_from_view_time']) || $_SESSION['comes_from_view_time'] > $t-ASP_VIEW_MIN_AGE) // user is too fast
                  || (!isset($_SESSION['submitted_when']) || !isset($aRequestVars['submitted_when'])) // faked form
                    || ($_SESSION['submitted_when'] != $aRequestVars['submitted_when']) // faked form
                      || ($_SESSION['submitted_when'] > $t-ASP_INPUT_MIN_AGE && !isset($_SESSION['captcha_retry_news'])) // user too fast
                        || ($_SESSION['submitted_when'] < $t-43200) // form older than 12h
                          || ($aRequestVars['email'] || $aRequestVars['url'] || $aRequestVars['homepage'] || $aRequestVars['comment']) /* honeypot-fields */ ))
          {
              $oApp->send_header($sRecallAddress."?p=".$position);
              exit;
          }
          if (ENABLED_ASP) {
              if (isset($_SESSION['captcha_retry_news'])){
                 unset($_SESSION['captcha_retry_news']);
              }
              $action = true;
          }

    } else{
        $action = true;
        $aPost['action'] = $action;
    }
    $aValideMsg = (isset($_SESSION['MESSAGE']) && is_array($_SESSION['MESSAGE']) ? $_SESSION['MESSAGE'] : []);

    if ($post_id && $section_id && $action && !sizeof($aValideMsg)){
        $commented_when = time();
        if($oApp->is_authenticated() == true){
            $commented_by = $oApp->getUserId();
        } else{
            $commented_by = 0;
        }
        $sql  = 'INSERT INTO `'.TABLE_PREFIX.'mod_news_comments` SET '
              . '`section_id` = '.intval($section_id).', '
              . '`page_id` = '.intval($page_id).', '
              . '`post_id` = '.intval($post_id).', '
              . '`title` = \''.$database->escapeString($title).'\', '
              . '`active` = '.(int)$active.', '
              . '`comment` = \''.$database->escapeString($comment).'\', '
              . '`commented_when` = '.intval($commented_when).', '
              . '`commented_by` = '.intval($commented_by).' '
              .'';
        if (!$query = $database->query($sql)){
            throw new \Exception('Database Exception '.$database->get_error());
        }
        if (isset($_SESSION['captcha'])) { unset($_SESSION['captcha']); }
        if (isset($_SESSION['comment_body'])) { unset($_SESSION['comment_body']); }
        if (isset($_SESSION['comment_title'])) { unset($_SESSION['comment_title']); }
        if (isset($_SESSION['comes_from_view'])) {unset($_SESSION['comes_from_view']);}
        if (isset($_SESSION['comes_from_view_time'])) {unset($_SESSION['comes_from_view_time']);}
        if (isset($_SESSION['submitted_when'])) {unset($_SESSION['submitted_when']);}

        $oApp->send_header($sRecallAddress.'' );
        exit;
    } else if (sizeof($aValideMsg)>0){
        $oApp->send_header($sBackCommentLink.'' );
        exit;
    }

 $oApp->send_header($sBackCommentLink.'&amp;p='.$position);
exit;
