<?php
/**
 *
 * @category        modules
 * @package         JsAdmin
 * @author          WebsiteBaker Project, modified by Swen Uth for Website Baker 2.7
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: jsadmin_backend_include.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/jsadmin/jsadmin_backend_include.php $
 * @lastmodified    $Date: 2019-03-17 07:05:56 +0100 (So, 17. Mrz 2019) $
 *
*/


// Must include code to stop this file being access directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

    if (!\defined('jsadminLoaded')){
      define('jsadminLoaded', true);
/*
        function jsadminLoaded() {return true;}
*/
        // obtain the admin folder (e.g. /admin)
        $admin_folder = \str_replace(WB_PATH, '', ADMIN_PATH);

        $JSADMIN_PATH = WB_URL.'/modules/jsadmin';
        $YUI_PATH = WB_URL.'/include/yui';
        $sCallingScript = $_SERVER['SCRIPT_NAME'];
        $sAddonName = \basename(__DIR__);
        $page_type = '';
        $js_buttonCell = 0;

        //$module_dir = $script;
        if (!isset( $module_dir ) ) { $module_dir = $sAddonName; }

        if (strstr($sCallingScript, $admin_folder."/pages/index.php")){
            $page_type = 'pages';
        }elseif (strstr($sCallingScript, $admin_folder."/pages/sections.php")){
            $page_type = 'sections';
        /*
        }elseif(strstr($sCallingScript, $admin_folder."/settings/tool.php")
            && isset($_REQUEST["tool"]) && $_REQUEST["tool"] == $sAddonName){
            $page_type = 'settings';
        */
        }elseif(\strstr($sCallingScript, $admin_folder."/pages/modify.php"))  {
            $page_type = 'modules';
        }elseif(\strstr($sCallingScript, $admin_folder."/admintools/tool.php"))  {
            $page_type = 'tool';
        }

        if (\preg_match( '/'.'(pages|sections|settings|modules|tool|config)$/is', $page_type)) {
        //if($page_type!='') {
            if (!\function_exists('get_setting')){require(WB_PATH.'/modules/'.$sAddonName.'/jsadmin.php');}
            // Default scripts
            $js_scripts = [];
            $js_scripts[] = 'jsadmin.js';

            switch ($page_type):
                case 'tool':
                case 'modules':
                    if (!get_setting('mod_jsadmin_persist_order', '1')) {   //Maybe Bug settings to negativ for persist , by Swen Uth
                        $js_scripts[] = 'restore_pages.js';
                      }
                    if (get_setting('mod_jsadmin_ajax_order_pages', '1')) {
                        $js_scripts[] = 'dragdrop.js';
                    }
                    break;
                case 'pages':
                    if (!get_setting('mod_jsadmin_persist_order', '1')) {   //Maybe Bug settings to negativ for persist , by Swen Uth
                        $js_scripts[] = 'restore_pages.js';
                      }
                    if (get_setting('mod_jsadmin_ajax_order_pages', '1')) {
                        $js_scripts[] = 'dragdrop.js';
                    }
                    break;
                case 'sections':
                    if (get_setting('mod_jsadmin_ajax_order_sections', '1')) {
                        $js_scripts[] = 'dragdrop.js';
                      // This ist the Cell where the Button "Up" is , by Swen Uth
                        if (SECTION_BLOCKS) {
                        } else {
                          --$js_buttonCell;
                        }
                    }
                    break;
                case 'config':
                    $js_scripts[] = 'tool.js';
                    break;
                default:
                    $admin->print_error('PageTtype '.$TEXT['ERROR'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
            endswitch;

?>
<script >
        var JsAdmin = {
            ADMIN_DIRECTORY : '<?php echo '/'.ADMIN_DIRECTORY; ?>',
            WB_URL : '<?php echo WB_URL; ?>',
            ADMIN_URL : '<?php echo ADMIN_URL; ?>',
            ModuleUrl : '<?php echo $module_dir; ?>',
            THEME_URL : '<?php echo THEME_URL; ?>'
        };

        var JsAdminTheme = { THEME_URL : '<?php echo THEME_URL; ?>' };
        // Get the Cell where the Button "Up" is
        function callFunc(){
            var trim = function (str, chr) {
              var rgxtrim = (!chr) ? new RegExp('^\\s+|\\s+$', 'g')  : new RegExp('^' + chr + '+|' + chr + '+$', 'g');
              return str.replace(rgxtrim, '');
            };
            var aRetVal = null;
            var reImg = /(.*)move_(up)\.php(.*)/;
            var oTable = document.getElementsByTagName('table');
            //gets rows of table
            for(var i = 0, table; table = oTable[i]; i++){
              for (var j = 0, row; row = oTable[i].rows[j]; j++) {
//console.info("Content in row" + j+" : \n"  );
               for (var k = 0, col; col = row.cells[k]; k++) {
                  var value = col.innerHTML;
//console.log("cell "+k+" value    :  " + value);
                  var match = value.match(reImg);
                  if(!match) {
                    continue;
                  } else {
                      return {
                         buttonCell : k,
                         url : trim(match[1]),
                         op : match[2],
                         params : match[3]
                     }
                  } // end match reImg
               }// end for col
             }// end for row
           } // end for table
         }// end function callFunc
        var aCell = callFunc();
        if (aCell){
          var buttonCell = aCell.buttonCell;
//console.log('buttonCell: '+buttonCell);
//console.log(aCell);
        }
//console.log(JsAdmin);
</script>
<?php
// Check and Load the needed YUI functions  //, all by Swen Uth
            $YUI_ERROR = false; // is there an Error
            $YUI_PUT ='';   // String with javascipt includes
            $YUI_PUT_MISSING_Files=''; // String with missing files
            reset($js_yui_scripts);
            foreach($js_yui_scripts as $script) {
                if (\is_readable(WB_PATH.$script)){
                    $YUI_PUT = $YUI_PUT."<script src='".WB_URL.$script."'></script>\n"; // go and include
                } else {
                    $YUI_ERROR=true;
                    $YUI_PUT_MISSING_Files=$YUI_PUT_MISSING_Files."- ".WB_URL.$script."\\n";   // catch all missing files
                }
            }
        /*  */
            if (!$YUI_ERROR)
            {
                echo $YUI_PUT;  // no Error so go and include
                // Load the needed functions
                foreach($js_scripts as $script) {
                    echo sprintf("<script src='".$JSADMIN_PATH."/js/".$script."'></script>\n");
                }
            } else {
                echo "<script>alert('YUI ERROR!! File not Found!! > \\n".$YUI_PUT_MISSING_Files." so look in the include folder or switch Javascript Admin off!');</script>\n"; //, by Swen Uth
            }
        }
    }
