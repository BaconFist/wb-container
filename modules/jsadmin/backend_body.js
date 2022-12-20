/*
      function loadCss (){

            var JsAdminCss = WB_URL+"/modules/jsadmin/themes/default/css/3/w3.css";
            var AdminCss   = WB_URL+"/modules/jsadmin/backend.css";
//console.log(AdminCss);
            if (typeof LoadOnFly!=='function' || typeof LoadOnFly==='undefined' || !LoadOnFly){
                $.insert(JsAdminCss);
                $.insert(AdminCss);
            } else {
                LoadOnFly('head', JsAdminCss);
                LoadOnFly('head', AdminCss);
            }

    };

document.addEventListener('DOMContentLoaded', loadCss, false);
*/
