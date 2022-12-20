<?php
//:Load the view.php from any other section-module
//:Use [[SectionPicker?sid=123]]
//:DarkViper, Lusiehahne
    $content       = true;
    $_sFrontendCss = $_sFrontendJs  = '';
    $oReg          = \bin\WbAdaptor::getInstance();
    $wb            = ($oReg->getApplication() ?? $GLOBALS['wb']);
    $oTrans        = $oReg->getTranslate();
    $database      = $oReg->getDatabase();
    $section_id    = \intval($sid ?? 0);
    if ($section_id > 0) {
        $sSql = 'SELECT `page_id` FROM `'.$database->TablePrefix.'sections` '
              . 'WHERE `section_id` = '.$section_id;
        if (! \is_null($page_id = $database->get_one($sSql))) {
            $iPageIsVisibile = $wb->isPageVisible($page_id);
            $sSql = 'SELECT `s`.*'
                  .     ', `p`.`viewing_groups`'
                  .     ', `p`.`visibility`'
                  .     ', `p`.`menu_title`'
                  .     ', `p`.`link` '
                  . 'FROM `'.$database->TablePrefix.'sections` `s`'
                  . 'INNER JOIN `'.$database->TablePrefix.'pages` `p` '
                  .    'ON `p`.`page_id`=`s`.`page_id` '
                  . 'WHERE `s`.`section_id` = '.$section_id.' '
                  .   'AND ('.\time().' BETWEEN `s`.`publ_start` AND `s`.`publ_end`) '
                  .   'AND `active` = 1 '
                  .   'AND `p`.`visibility` NOT IN (\'deleted\',\'none\')';
            if (($oSection = $database->query($sSql))) {
                while ($aSection = $oSection->fetchRow(\MYSQLI_ASSOC)) {
                    $section_id = $aSection['section_id'];
                    $module = $aSection['module'];
                    \ob_start();
                    require ($oReg->AppPath.'modules/'.$module.'/view.php');
                    $content = \ob_get_clean();
                    $sFrontend = 'modules/'.$module.'/frontend';
                    $_sPattern = '/<link[^>]*?href\s*=\s*\"'
                               . \preg_quote($oReg->AppUrl.$sFrontend.'.css', '/')
                               . '\".*?\/>/si';
                    if (! \preg_match($_sPattern, $content)) {
                        $sFrontendCssFile = (\is_readable($oReg->AppPath.$sFrontend.'.css') ? $oReg->AppUrl.$sFrontend.'.css':'');
                        if ($sFrontendCssFile != ''){
                            $_sFrontendCss = '
                              <script>
                                  try {
                                      var ModuleCss = "'.$sFrontendCssFile.'";
                                      if (ModuleCss!==""){
                                          if (typeof LoadOnFly === "undefined"){
                                              include_file(ModuleCss, "css");
                                          } else {
                                              LoadOnFly("head", ModuleCss);
                                          }
                                      }
                                  } catch(e) {
                                   /* alert("An error has occurred: "+e.message)*/
                                  }
                              </script>
                              ';
                        }
                    }
                    $_sPattern = '/<script[^>]*?src\s*=\s*\"'
                               . \preg_quote($oReg->AppUrl.$sFrontend.'.js', '/')
                               . '\".*?\/>/si';
                    if (! \preg_match($_sPattern, $content)) {
                        $sFrontendJsFile  = (\is_readable($oReg->AppPath.$sFrontend.'.js') ? $oReg->AppUrl.$sFrontend.'.js':'');
                        if ($sFrontendJsFile!=''){
                            $_sFrontendJs = '
                              <script>
                                  try {
                                      var ModuleJs  = "'.$sFrontendJsFile.'";
                                      if (ModuleJs!==""){
                                          include_file(ModuleJs, "js");
                                      }
                                  } catch(e) {
                                   /* alert("An error has occurred: "+e.message)*/
                                  }
                              </script>
                              ';
                        }
                    }
                }  // while
            }
        } // page_id
    } // has section_id
    if ($content === true || trim($content) == '') {
        $content = true;
    } else {
        $content = $_sFrontendCss.$_sFrontendJs.$content;
    }
    return $content;
// end of file
