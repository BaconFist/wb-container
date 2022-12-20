<?php
/* -------------------------------------------------------- */
if ( !defined( 'WB_PATH' ) ){ require( dirname(dirname((__DIR__))).'/config.php' ); }

$sAddonPath = (__DIR__);
$sAddonName = basename($sAddonPath);
$sAddonRel = '/modules/'.$sAddonName.'/data/layouts/';
$aJsonRespond = [];
$aMessage = [];

$print_info_banner = true;
// suppress to print the header, so no new FTAN will be set
$admin_header = true;
// Tells script to update when this page was last updated
$update_when_modified = true;
// Include WB admin wrapper script
//require(WB_PATH.'/modules/admin.php');
$requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
$aRequestVars  = (isset(${'_'.$requestMethod}) ? ${'_'.$requestMethod} : $_REQUEST);
try{
    $admin = new admin('Modules', 'module_view', FALSE, FALSE);
    $file = $admin->StripCodeFromText(urldecode(isset($aRequestVars['file'])?$aRequestVars['file']:''));

    if (file_exists(WB_PATH.$sAddonRel.$file)&&($file!='')) {
        header('Content-Description: File Transfer');
        header("Content-Type: text/xml");
        header("Content-Disposition: attachment; filename=$file");
        header("Content-Length: ". filesize(WB_PATH.$sAddonRel.$file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile(WB_PATH.$sAddonRel.$file);
        $aJsonRespond['success'] = true;
        exit;
    } else {
        $aMessage[] = sprintf('%1$.04d ) No file %2$s for download is selected.', __LINE__, $file);
    }
    } catch (Exception $e) {
        $aJsonRespond['message'] = $e->getMessage();
        $aJsonRespond['success'] = false;
        exit;
    }
//    echo the json_respond to the ajax function
//    exit(json_encode($aJsonRespond));
    require(WB_PATH.'/modules/admin.php');
    $backUrl = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
    $admin->print_error(implode('<br />',$aMessage), $backUrl);
    exit;
// Print admin footer
    $admin->print_footer();
