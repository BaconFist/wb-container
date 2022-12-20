/*
 Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
*/

(function () {
    "use strict";
    CKEDITOR.plugins.add('wboembed', {
        lang:
        [
            CKEDITOR.config.defaultLanguage,
            CKEDITOR.lang.detect(CKEDITOR.config.language )
        ],
//        lang : ['en','de'],
        icons: 'wboembed',
        hidpi: true,

        init: function (editor) {
            // Command
            editor.addCommand('wboembed', new CKEDITOR.dialogCommand('wboembedDialog'));
            // Toolbar button
            editor.ui.addButton('wboembed', {
                label: editor.lang.wboembed.button,
                command: 'wboembed',
                toolbar: 'insert'
            });
            // Dialog window
            CKEDITOR.dialog.add('wboembedDialog', this.path + 'dialogs/wboembedDialog.js');
        }
    });

})();