<?php
use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use addon\ckeditor\ckeditor\CKEditor;
use addon\ckeditor\ckeditor\CKEditorPlus;

/**
 *
 * @category       modules
 * @package        ckeditor
 * @authors        WebsiteBaker Project, Michael Tenschert, Dietrich Roland Pehlke, Marmot, Luisehahne
 * @copyright      WebsiteBaker Org. e.V.
 * @link           https://websitebaker.org/
 * @license        https://www.gnu.org/licenses/gpl.html
 * @platform       WebsiteBaker 2.12.2
 * @requirements   PHP 7.2.0 and higher
 * @version        $Id: include.php 378 2019-06-26 11:25:30Z Luisehahne $
 * @filesource     $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/ckeditor/include.php $
 * @lastmodified   $Date: 2019-06-26 13:25:30 +0200 (Mi, 26. Jun 2019) $
 *
 */

/**
 *    Function called by parent, default by the wysiwyg-module
 *
 *    @param    string    The name of the textarea to watch
 *    @param    mixed    The "id" - some other modules handel this param differ
 *    @param    string    Optional the width, default "100%" of given space.
 *    @param    string    Optional the height of the editor - default is '250px'
 *
 *
 */
function show_wysiwyg_editor(
      $name,
      $id,
      $content,
      $width = '100%',
      $height = '458',
      $sDbCharset = 'utf8',
      $toolbar = 'WB_Default',
      $OutputAsBuffer=false
        ) {
        $oReg       = WbAdaptor::getInstance();
        $oRequest   = $oReg->getRequester();
        $database   = $oReg->getDatabase();
        $oApp       = $oReg->getApplication();
        $page_id    = $oApp->getIdFromRequest('page_id');
        $section_id = ($GLOBALS['section_id'] ?? null);
        $iFieldName = preg_match('/([a-z_-])+/ui',$name,$aFieldName);
        $sFieldName = $aFieldName[0];
        $aFieldCharset = ['utf8','utf8mb4'];
// get module and field charset
        $sCharset = (!empty($sDbCharset) ? $sDbCharset : (DB_CHARSET ?? 'utf8'));
        $aTmp = \preg_split(
            '/[^a-z0-9]/i',
            \strtolower(\preg_replace('/[^a-z0-9_]/i', '', $sCharset)),
            -1,
            \PREG_SPLIT_NO_EMPTY
        );
        $sFieldCharset = $aTmp[0];

// Work-out get the module name
        $sql  = '
        SELECT `module` FROM `'.$oReg->TablePrefix.'sections`
        WHERE `section_id` = '.(int)$section_id.'';
        $sModuleName = $database->get_one($sql);

//echo nl2br(sprintf("<div class='w3-border w3-padding'>[%03d] %s</div>\n",__LINE__,$sModuleName));
//echo nl2br(sprintf("<div class='w3-border w3-padding'>[%03d] %s</div>\n",__LINE__,$sFieldCharset));
//echo nl2br(sprintf("<div class='w3-border w3-padding'>[%03d] %s</div>\n",__LINE__,$sFieldName));

        $toolbar = (empty($toolbar) ? 'WB_Default' : $toolbar); // WB_GROUP WB_Default
        $modAbsPath = str_replace('\\','/',__DIR__);
        $sAddonname = \basename($modAbsPath);
        $ckeAbsPath = $modAbsPath.'/'.$sAddonname.'/';
        if ($oRequest->issetServerVar('SCRIPT_FILENAME')) {
            $sServerPath = $oRequest->getServerVar('SCRIPT_FILENAME');
            $realPath = str_replace('\\','/',  dirname($sServerPath));
        } else {
            /**
             * realpath - Returns canonicalized absolute pathname
             */
            $realPath = str_replace('\\','/',realpath( './' )) ;
        }
        $selfPath = str_replace('\\','/',dirname($oRequest->getServerVar('SCRIPT_NAME')));
        $documentRoot = rtrim($oReg->DocumentRoot, '/');
        $tplAbsPath = str_replace('\\','/',$documentRoot.'/templates');
        $tplRelPath = str_replace($documentRoot,'',$tplAbsPath);
        $modRelPath = str_replace($documentRoot,'',$modAbsPath);
        $ckeRelPath = $modRelPath.'/ckeditor/';

        $url = parse_url(WB_URL);
        $url['path'] = (isset($url['path']) ? $url['path'] : '');
        $ModPath = str_replace($url['path'],'',$modRelPath).'/';
        $ckeModPath = str_replace($url['path'],'',$ckeRelPath);
        $tplPath = str_replace($url['path'],'',$tplRelPath).'/';

/*
        $aDebugPath = [
            'selfPath'     => $selfPath,
            'documentRoot' => $documentRoot,
            'tplAbsPath'   => $tplAbsPath,
            'tplRelPath'   => $tplRelPath,
            'modAbsPath'   => $modAbsPath,
            'ckeAbsPath'   => $ckeAbsPath,
            'modRelPath'   => $modRelPath,
            'ckeRelPath'   => $ckeRelPath,
            'ModPath'      => $ModPath,
            'ckeModPath'   => $ckeModPath,
    //        '' => '',
    //        '' => '',
            'tplPath' => $tplPath,
        ];
*/

    /**
     * http://docs.ckeditor.com/#!/api/CKEDITOR.config
     *
     * @param boolean
     * true: set some config.index by wb_config.js
     * false: set some config['index'] by include.php
     *
     * possible config.indexes for setting in wb_config.js
     * that were normaly set in include.php
     * format_tags, resize_dir, autoParagraph, skin, toolbar,
     * extraPlugins, removePlugins, browserContextMenuOnCtrl, entities,
     * scayt_autoStartup,
     *
     *
     */
    $bWbConfigSetting = false;
/* first search for a wb_config folder in /templates/ */
    $bConfigSettings  = (is_dir($oReg->AppPath.'templates/wb_config/'));
/* second search for wb_config folder or wb_ckconfig.js file in the actuelly running frontend template */
    if (($bWbConfigSetting === false) && !$bConfigSettings){
       $aFiles = PreCheck::scanDirTreeIterator($oReg->TemplatePath,'wb_ckconfig|wb_config');
    }
    $bWbConfigSetting = $bConfigSettings || !empty($aFiles['file']);
    /**
     *    Create new CKeditor instance.
     *    But first - we've got to revamp this pretty old class a little bit.
     *
     */
    if (!\file_exists($ckeAbsPath.'CKEditor.php')) {
        $sMessage = \sprintf('Error loading editor file CKEditor.php, please check configuration');
        throw new \RuntimeException($sMessage);
    }
    if (!\file_exists($ckeAbsPath.'CKEditorPlus.php')) {
        $sMessage = \sprintf('Error loading editor file CKEditorPlus.php, please check configuration');
        throw new \RuntimeException($sMessage);
    }

        $ckeditor = new CKEditorPlus( $ckeRelPath);
    /******************************************************************************************/
        require ($modAbsPath.'/info.php');
        $ckeditor->config['ModulVersion'] = $module_version ?? 'none';
        $ckeditor->config['WBrevision']   = (defined('WB_REVISION') ? WB_REVISION :  '');
        $ckeditor->config['WBversion']    = (defined('WB_VERSION')  ? WB_VERSION  :  '');

/******************************************************************************************/
        $temp = '';
        if (($page_id > 0)) {
            $query = 'SELECT `template` from `'.TABLE_PREFIX.'pages` where `page_id`='.(int)$page_id.'';
            $temp = $database->get_one( $query );
        }
        $templateFolder = (empty($temp)) ? $oReg->DefaultTemplate : $temp;
        $ckeditor->setTemplatePath($templateFolder);

//       The language to be used if config.language is empty and it's not possible to localize the editor to the user language.
      $ckeditor->config['defaultLanguage']  = strtolower((DEFAULT_LANGUAGE ?? 'en'));

    /**
     *    Setup the CKE language
     *
     */
    $ckeditor->config['language'] = strtolower((LANGUAGE ?? 'en'));

    /**
     * A list of semi colon separated style names (by default tags) representing
     * the style definition for each entry to be displayed in the Format combo in
     * the toolbar. Each entry must have its relative definition configuration in a
     * setting named "format_(tagName)". For example, the "p" entry has its
     * definition taken from config.format_p.
     * @type String
     * @default 'p;h1;h2;h3;h4;h5;h6;pre;address;div'
     */
    if (!$bWbConfigSetting ) { $ckeditor->config['format_tags'] = 'p;div;h1;h2;h3;h4;h5;h6;pre;address'; }
  // Custom Heading 1 and Formatted format definitions.

    if (!$bWbConfigSetting ) { $ckeditor->config['resize_dir'] = 'both'; }
    /**
     * Note: This option is deprecated. Changing the default value might introduce unpredictable usability issues and is highly unrecommended.
     * Defaults to: true
     */
    if (!$bWbConfigSetting) {$ckeditor->config['autoParagraph'] = false; }

    /**
    * The skin to load. It may be the name of the skin folder inside the editor installation path,
    * or the name and the path separated by a comma.
    * Available skins: moono,   moono-lisa  moonocolor  DCDCDC
    *
    */
    if (!$bWbConfigSetting) {$ckeditor->config['skin'] = 'moonocolor'; }

    if (!$bWbConfigSetting) {$ckeditor->config['uiColor'] = '#BFD7EB'; }

    /**
     *    Define all extra CKEditor plugins in _yourwb_/modules/ckeditor067/ckeditor/plugins here
     *
     */
    if (!$bWbConfigSetting ) {
        $ckeditor->config['extraPlugins'] =
                            'codemirror'
                          . ',filebrowser'
                          . ',syntaxhighlight'
                          . ',wblink'
                          . ',wbdroplets'
                          . ',wbabout'
                          . ',wboembed'
                          . ',wbrelation'
//                          . ',emoji'
                          . (in_array($sFieldCharset,$aFieldCharset) ? ',emoji': '')
                          . '';
        $ckeditor->config['removePlugins']  =
                            'link'
                          . ',backup'
//                          . ',iframe'
//                          . ',pastefromword'
//                          . ',table,tabletools'
                          . ',oembed'
                          . ',save'
                          . ',scayt'
                          . ',pagebreak'
                          . ',placeholder'
                          . ',shybutton'
                          . ',sourcedialog'
                          . ',wsc'
//                         . ',wbsave'
//                         . ',contextmenu,liststyle,tabletools,tableselection'
                          . (in_array($sFieldCharset,$aFieldCharset) ? ',smiley': ',emoji')
                          . '';
    }
    if ($toolbar && !$bWbConfigSetting) {$ckeditor->config['toolbar'] = $toolbar;}

    if ($toolbar && !$bWbConfigSetting) {$ckeditor->config['hideControls'] = true;}

    /**
     *  Whether to show the browser native context menu when the Ctrl
     *  or Meta (Mac) key is pressed on opening the context menu with the right mouse button click or the Menu key.
     *
     */
    if (!$bWbConfigSetting) {$ckeditor->config['browserContextMenuOnCtrl'] = true; }

    /**
     *    Force the object to print/echo direct instead of returning the
     *    HTML source string.
     *
     */
    $ckeditor->bOutputAsBuffer = $OutputAsBuffer;

    if (!$bWbConfigSetting) {$ckeditor->config['entities'] = false; }

    /**
     * Sets the DOCTYPE to be used when loading the editor content as HTML.
     * <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
     * <!DOCTYPE html>
     */
        $ckeditor->config['docType'] = '<!DOCTYPE html>';

        /**
         *
         * Define Marmots CKEditor plugin in ../ckeditor/plugins/backup
         * backup_on_start true or false
         * backup_save_delay in ms
         *
         */
        $ckeditor->config['backup_on_start'] = true;
        $ckeditor->config['backup_save_delay'] = 500;
        $ckeditor->config['syntaxhighlight_lang'] = 'php';

    /**
     *    SCAYT
     *    Spellchecker settings.
     *
        $ckeditor->config['scayt_sLang'] = strtolower(LANGUAGE)."_".(LANGUAGE == "EN" ? "US" : LANGUAGE);
    if (!$bWbConfigSetting ) {
        $ckeditor->config['scayt_autoStartup'] = false;
     }
     */

    /**
     * Future filemanager
     *
     */

    $connectorPath = $ckeditor->basePath.'filemanager/connectors/php/connector.php';
    $ckeditor->config['filebrowserBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Connector='.$connectorPath;
    $ckeditor->config['filebrowserImageBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Type=Image&Connector='.$connectorPath;
    $ckeditor->config['filebrowserFileBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Type=File&Connector='.$connectorPath;
    $ckeditor->config['filebrowserMediaBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Type=Media&Connector='.$connectorPath;
//    $ckeditor->config['filebrowserFlashBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Type=Flash&Connector='.$connectorPath;

    $ckeditor->config['uploader'] = false;

    if ($ckeditor->config['uploader']==true) {
        $uploadPath = $ckeditor->basePath.'filemanager/connectors/php/upload.php?Type=';
        $ckeditor->config['filebrowserUploadUrl'] = $uploadPath.'File';
        $ckeditor->config['filebrowserMediaUploadUrl'] = $uploadPath.'Media';
        $ckeditor->config['filebrowserImageUploadUrl'] = $uploadPath.'Image';
//        $ckeditor->config['filebrowserFlashUploadUrl'] = $uploadPath.'Flash';
    }

/******************************************************************************************/
    /**
     *    Looking for the styles
     *
     */
    $ckeditor->resolve_path(
        'contentsCss',
        $tplPath.'wb_config/editor.css',
        $ModPath.'wb_config/editor.css'
    );

    /**
     *    Looking for the editor.styles at all ...
     *
     */
    $ckeditor->resolve_path(
        'stylesSet',
        $tplPath.'wb_config/editor.styles.js',
        $ModPath.'wb_config/editor.styles.js',
        'wb:'
    );

    /**
     *    The list of templates definition files to load.
     *
     */
    $ckeditor->resolve_path(
        'templates_files',
        $tplPath.'wb_config/editor.templates.js',
        $ModPath.'wb_config/editor.templates.js'
    );

    /**
     *    Bugfix for the template files as the ckeditor want an array instead a string ...
     *
     */
    $ckeditor->config['templates_files'] = [$ckeditor->config['templates_files']];

    /**
     *    Get the config file
     *
     */
    $ckeditor->resolve_path(
        'customConfig',
        $tplPath.'wb_config/wb_ckconfig.js',
        $ModPath.'wb_config/wb_ckconfig.js'
    );
    /******************************************************************************************/
    $ckeditor->config['height'] = $height;
    $ckeditor->config['width']  = $width;

    $ckeditor->config['autoGrow_minHeight']   = $height;
    $ckeditor->config['autoGrow_maxHeight']   = $height;
    $ckeditor->config['autoGrow_bottomSpace'] = 50;
    $ckeditor->config['autoGrow_onStartup']   = false;

    /**
     *    Additional test for wysiwyg-admin
     *
     */
    $ckeditor->looking_for_wysiwyg_admin( $database );

    /**
     *    To avoid a double "else" inside the following condition, we set the
     *    default toolbar here to "WB_Full". Keep in mind, that the config will overwrite all
     *    settings inside the config.js or wb_config.js BUT you will have to define the toolbar inside
     *    them at all!
     *
     */
    if ($database && $ckeditor->wysiwyg_admin_exists ) {
        $data = null;
        $query = 'SELECT * from `'.TABLE_PREFIX.'mod_editor_admin` WHERE `editor`=\'ckeditor\'';
        if (($result = $database->query($query))) {
            $data = $result->fetchRow(MYSQLI_ASSOC);
        }
//     import data into $ckeditor->config
        if ($data) {
            foreach ($data as $key => $value) {
                $ckeditor->config[$key] = $value;
                if (!$ckeditor->config[$key]){unset($ckeditor->config[$key]);}
            }
        }
    }

    if (isset($ckeditor->config['menu'])) {$ckeditor->config['toolbar'] = $ckeditor->config['menu'];}
    /*
    if( !$bWbConfigSetting ) {
        if ( (!$ckeditor->wysiwyg_admin_exists) || ($ckeditor->force) ) {
        }
     }
    */
    /*  */
    $content = $ckeditor->reverse_htmlentities($content);

    $output = $ckeditor->to_HTML( $name, $content, $ckeditor->config);
    if (!$OutputAsBuffer){echo $output;} else {return $output;}
    unset ($sDbCharset);
} // end function show_wysiwyg_editor
