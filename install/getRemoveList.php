<?php
    function getFileList ()
    {
        $aRemoveList['dirRemove'] = [
                    '[ROOT]var/logs/',
                    '[ADMIN]pages/vendor/',
                    '[ADMIN]images/',
                    '[ADMIN]themes/',
                    '[FRAMEWORK]helpers/dev/',
                    '[INCLUDE]plugins/default/klaro/dist/',

                    '[INCLUDE]crowphp/',
                    '[INCLUDE]enyo/',
                    '[INCLUDE]joshtronic/',
                    '[INCLUDE]laminas/',
                    '[INCLUDE]matthiasmullie/',
                    '[INCLUDE]nette/',
                    '[INCLUDE]psr/',
                    '[INCLUDE]scssphp/',
                    '[INCLUDE]spatie/',
                    '[INCLUDE]system/breadcrumb/',
                    '[INCLUDE]system/csv/',
                    '[INCLUDE]system/loginbox/',
                    '[INCLUDE]studio-42/',
                    '[INCLUDE]twig/',
                    '[INCLUDE]verot/',
                    '[VENDOR]sqlAdmin/',

                    '[INCLUDE]lightbox/',
                    '[INCLUDE]Paragonie/',
                    '[INCLUDE]PHPMailer/',
                    '[INCLUDE]phpmailer/extra/',
                    '[INCLUDE]phpmailer/language/',
                    '[INCLUDE]Sensio/',

                    '[MODULES]SecureFormSwitcher/',
                    '[MODULES]ckeditor/ckeditor/skins/moono-lisa/',
                    '[MODULES]droplets/data/',
                    '[MODULES]droplets/templates/lauterbach/',
                    '[MODULES]fckeditor/',
                    '[MODULES]foldergallery/templates/lauterbach/',
                    '[MODULES]foldergallery/templates/responsive/',
                    '[MODULES]form/templates/lauterbach/',
                    '[MODULES]news/templates/lauterbach/',
                    '[MODULES]output_filter/templates/lauterbach/',
                    '[MODULES]wrapper/templates/lauterbach/',
                    '[MODULES]droplets/templates/hortal/'
                    ,'[MODULES]form/templates/hortal/'
                    ,'[MODULES]news/templates/wdb1102/'
                    ,'[INSTALL]sources/',
                 ];

        $aRemoveList['filesRemove'] = [
                    '[ROOT]_short.php',
                    '[ROOT]config.php.new',
                    '[ROOT]htaccess.bak',
                    '[ROOT]htaccess.txt',
                    '[ROOT]_htaccess.txt',
                    '[ROOT]_htaccess',
                    '[ROOT]README-FIX',
                    '[ROOT]short.php.bak',
                    '[ROOT]SP5_UPGRADE_DE',
                    '[ROOT]SP5_UPGRADE_EN',
                    '[ROOT]SP6_UPGRADE_EN',
                    '[ROOT]SP7_UPGRADE_EN',
                    '[ROOT]var/logs/php_error.log',
                    '[ROOT]upgrade-script.php',
                    '[DOCU]SP7_UPGRADE_EN',
                    '[DOCU]README-FIX',

                    '[ACCOUNT]template.html',
                    '[ACCOUNT]l.ogin.000.php',
                    '[ACCOUNT]login.000.php',
                    '[ACCOUNT]templates/frontend.css',
                    '[ACCOUNT]preferences_form.php.old',
                    '[ADMIN]settings/index.001.php',
                    '[ADMIN]interface/background.png',
                    '[ADMIN]interface/bgtitle.png',
                    '[ADMIN]interface/charsets.php',
                    '[ADMIN]interface/error.html',
                    '[ADMIN]interface/footer.html',
                    '[ADMIN]interface/index.php',
                    '[ADMIN]interface/error.html',
                    '[ADMIN]media/migrate_parameters.php',
                    '[ADMIN]media/MediaScanDir.php',
                    '[ADMIN]modules/myfile.json',
                    '[ADMIN]pages/rebuildAccessFiles.php',
                    '[ADMIN]pages/html.inc',
                    '[ADMIN]pages/html.php',
                    '[ADMIN]preferences/details.php',
                    '[ADMIN]preferences/email.php',
                    '[ADMIN]preferences/password.php',
                    '[ADMIN]settings/setting.js',
                    '[ADMIN]settings/array.php',
                    '[ADMIN]themes/templates/admintools.htt.old',

                    '[FRAMEWORK]class.login.php',
                    '[FRAMEWORK]class.msg_queue.php',
                    '[FRAMEWORK]class.wbmailer.php.new.php',
                    '[FRAMEWORK]DseTwo.php',
                    '[FRAMEWORK]Frontend.php',
                    '[FRAMEWORK]SecureForm.mtab.php',
                    '[FRAMEWORK]SecureForm.php',
                    '[FRAMEWORK]SysInfo.php',
                    '[FRAMEWORK]Twig_Autoloader.php',
                    '[FRAMEWORK]WBMailer.php',
                    '[FRAMEWORK]helpers/new_initialize.php',
                    '[FRAMEWORK]helpers/NativeSessionHandler.php',
                    '[FRAMEWORK]traits/Captcha_SessionTrait.php',
                    '[FRAMEWORK]traits/ClassWB_OldStyle.php',
                    '[FRAMEWORK]traits/FromArray.php',
                    '[FRAMEWORK]traits/Phplib_FtanTrait.php',

                    '[INCLUDE]idna_convert\ReadMe.txt',
                    '[INCLUDE]idna_convert\LICENCE',
                    '[INCLUDE]idna_convert\example.php',
                    '[INCLUDE]jquery/dist/1.9.1/jquery-1.9.1.min.js',

                    '[INCLUDE]phpmailer/changelog.md',
                    '[INCLUDE]phpmailer/ChangeLog.txt',
                    '[INCLUDE]phpmailer/class.pop3.php',
                    '[INCLUDE]phpmailer/class.smtp.php',
                    '[INCLUDE]phpmailer/class.phpmailer.php',
                    '[INCLUDE]phpmailer/class.phpmaileroauth.php',
                    '[INCLUDE]phpmailer/class.phpmaileroauthgoogle.php',
                    '[INCLUDE]phpmailer/get_oauth_token.php',
                    '[INCLUDE]phpmailer/PHPMailerAutoload.php',
                    '[INCLUDE]phpmailer/index.php',
                    '[INCLUDE]phpmailer/LICENSE',
                    '[INCLUDE]phpmailer/README',
                    '[INCLUDE]phpmailer/README.md',
                    '[INCLUDE]phpmailer/SECURITY.md',
                    '[INCLUDE]phpmailer/VERSION',

                    '[INCLUDE]Sensio/Twig/CHANGELOG',
                    '[INCLUDE]Sensio/Twig/1/LICENSE',
                    '[INCLUDE]Sensio/Twig/1/README.rst',
                    '[INCLUDE]Sensio/Twig/2/LICENSE',
                    '[INCLUDE]Sensio/Twig/2/README.rst',

                    '[INCLUDE]editarea/index.php',
                    '[INCLUDE]jquery/jquery-171-min.js',
                    '[INCLUDE]jquery/raw/domReady-min.js',
                    '[INCLUDE]jquery/raw/domReady.js',
                    '[INCLUDE]jquery/raw/LoadOnFly-min.js',
                    '[INCLUDE]jquery/raw/LoadOnFly.js',
                    '[INCLUDE]phplib/FtanTrait.php',
                    '[INCLUDE]phplib/index.php',
                    '[INCLUDE]plugins/default/sweetalert/dist/sweetalert.css',
                    '[INCLUDE]plugins/default/sweetalert/dist/sweetalert1.dev.js',
                    '[INCLUDE]plugins/default/sweetalert/dist/sweetalert2.all.js',
                    '[INCLUDE]plugins/default/sweetalert/LICENSE',
                    '[INCLUDE]plugins/default/sweetalert/README.md',
                    '[INCLUDE]plugins/default/sweetalert2/dist/promise-polyfill.js',
                    '[INCLUDE]plugins/default/sweetalert2/dist/sweetalert.css',
                    '[INCLUDE]plugins/default/sweetalert2/src/instanceMethods/buttons-handlers.js',
                    '[INCLUDE]plugins/default/sweetalert2/src/instanceMethods/keydown-handler.js',
                    '[INCLUDE]plugins/default/sweetalert2/src/instanceMethods/popup-click-handler.js',
                    '[INCLUDE]plugins/default/sweetalert2/src/instanceMethods/show-reset-validation-error.js',
                    '[INCLUDE]plugins/default/sweetalert2/src/instanceMethods/_main.js',
                    '[INCLUDE]plugins/default/sweetalert2/src/scss/_polyfills.scss',
                    '[INCLUDE]plugins/default/sweetalert2/src/staticMethods/queue.js',
                    '[INCLUDE]plugins/default/sweetalert2/src/utils/dom/renderers/renderHeader.js',
                    '[INCLUDE]plugins/default/sweetalert2/src/utils/ieFix.js',
                    '[INCLUDE]yui/index.php',

                    '[INSTALL]install_data.sql',
                    '[INSTALL]install-settings.sql',
                    '[INSTALL]install_struct.sql',
                    '[INSTALL]themes/unzip.001.php',
                    '[INSTALL]themes/unzip.002.php',
                    '[INSTALL]config.php',
                    '[INSTALL]update-sections.sql.php.001',
                    '[INCLUDE]pclzip/Constants.php.old',
                    '[INCLUDE]pclzip/pclzip.lib.php.old',

                    '[LANGUAGES]NL.zip',
                    '[LANGUAGES]old.format.inc.php',

    /* remove uninstall.php for addons which should never be uninstalled */
                    '[MODULES]captcha_control/uninstall.php',
                    '[MODULES]jsadmin/uninstall.php',
                    '[MODULES]menu_link/uninstall.php',
                    '[MODULES]output_filter/uninstall.php',
                    '[MODULES]show_menu2/uninstall.php',
                    '[MODULES]wysiwyg/uninstall.php',

                    '[MODULES]SimpleCommandDispatcher.inc',
                    '[MODULES]SimpleRegister.php',
                    '[MODULES]droplets/add_droplet.php',
                    '[MODULES]droplets/backup_droplets.php',
                    '[MODULES]droplets/delete_droplet.php',
                    '[MODULES]droplets/modify_droplet.php',
                    '[MODULES]droplets/save_droplet.php',
                    '[MODULES]droplets/languages/DA.php',
                    '[MODULES]form/save_field.php',

                    '[MODULES]captcha_control/languages/index.php',
                    '[MODULES]captcha_control/_captcha__20201108_154126.sql',
                    '[MODULES]ckeditor/ckeditor/README.md',
                    '[MODULES]code/index.php',
                    '[MODULES]droplets/cmd/add_droplet.php',
                    '[MODULES]droplets/cmd/copy_droplet.php',
                    '[MODULES]droplets/data/archiv/PLACEHOLDER',
                    '[MODULES]droplets/example/getNewsItems.php',
                    '[MODULES]form/data/layouts/Default_Table_Layout_001.xml',
                    '[MODULES]form/data/layouts/Default_Table_Layout_002.xml',
                    '[MODULES]form/data/layouts/Extended_Table_Layout.xml',
                    '[MODULES]form/movies_list.xml',
                    '[MODULES]jsadmin/index.php',
                    '[MODULES]output_filter/filter-routines.php',
                    '[MODULES]output_filter/languages/DE.php',
                    '[MODULES]wrapper/templates/PLACEHOLDER',

                    '[MEDIA]PLACEHOLDER',
                    '[PAGES]PLACEHOLDER',
                    '[TEMP]PLACEHOLDER',
                    '[VAR]logs/PLACEHOLDER',
    /*
                    '[TEMPLATE]wb_theme/uninstall.php',
                    '[TEMPLATE]wb_theme/templates/access.htt',
                    '[TEMPLATE]wb_theme/templates/addons.htt',
                    '[TEMPLATE]wb_theme/templates/admintools.htt',
                    '[TEMPLATE]wb_theme/templates/error.htt',
                    '[TEMPLATE]wb_theme/templates/groups.htt',
                    '[TEMPLATE]wb_theme/templates/groups_form.htt',
                    '[TEMPLATE]wb_theme/templates/languages.htt',
                    '[TEMPLATE]wb_theme/templates/languages_details.htt',
                    '[TEMPLATE]wb_theme/templates/media.htt',
                    '[TEMPLATE]wb_theme/templates/media_browse.htt',
                    '[TEMPLATE]wb_theme/templates/media_rename.htt',
                    '[TEMPLATE]wb_theme/templates/modules.htt',
                    '[TEMPLATE]wb_theme/templates/modules_details.htt',
                    '[TEMPLATE]wb_theme/templates/pages.htt',
                    '[TEMPLATE]wb_theme/templates/pages_modify.htt',
                    '[TEMPLATE]wb_theme/templates/pages_sections.htt',
                    '[TEMPLATE]wb_theme/templates/pages_settings.htt',
                    '[TEMPLATE]wb_theme/templates/preferences.htt',
                    '[TEMPLATE]wb_theme/templates/setparameter.htt',
                    '[TEMPLATE]wb_theme/templates/start.htt',
                    '[TEMPLATE]wb_theme/templates/success.htt',
                    '[TEMPLATE]wb_theme/templates/templates.htt',
                    '[TEMPLATE]wb_theme/templates/templates_details.htt',
                    '[TEMPLATE]wb_theme/templates/users.htt',
                    '[TEMPLATE]wb_theme/templates/users_form.htt',
    */
                    '[MODULES]droplets/data/archiv/Droplet_ShortUrl_20170111_155201.zip',
                    '[MODULES]droplets/themes/default/css/backend.css.org',
                    '[MODULES]form/backend.css.new',
                    '[MODULES]form/frontend.css.new',
                    '[MODULES]show_menu2/README.de.txt',
                    '[MODULES]show_menu2/README.en.txt',
                    '[MODULES]wrapper/languages/DE.info',

                    '[TEMPLATE]DefaultTemplate/PLACEHOLDER',
                    '[TEMPLATE]DefaultTheme/PLACEHOLDER',
                    '[TEMPLATE]DefaultTheme/css/customAlert.css',
                    '[TEMPLATE]DefaultTheme/css/dialogBox.css',
                    '[TEMPLATE]DefaultTheme/css/w3.css',
                    '[TEMPLATE]DefaultTheme/css/w3-colors-camo.css',
                    '[TEMPLATE]DefaultTheme/css/w3-colors-food.css',
                    '[TEMPLATE]DefaultTheme/css/w3-colors-highway.css',
                    '[TEMPLATE]DefaultTheme/css/w3-colors-safety.css',
                    '[TEMPLATE]DefaultTheme/css/w3-colors-signal.css',
                    '[TEMPLATE]DefaultTheme/css/w3-colors-vivid.css',
                    '[TEMPLATE]DefaultTheme/templates/addon_tpl.htt',

                    '[TEMPLATES]DefaultTemplate/templates/forgot_form.htt',
                    '[TEMPLATES]DefaultTemplate/templates/login_form.htt',
                    '[TEMPLATES]DefaultTemplate/templates/preferences_form.htt',
                    '[TEMPLATES]DefaultTemplate/templates/signup_form.htt',
                    '[TEMPLATES]DefaultTheme/languages/index.php',
                    '[TEMPLATES]DefaultTheme/templates/warning.html',
            ];
    return $aRemoveList;
    }

    $aRemoveList = getFileList();