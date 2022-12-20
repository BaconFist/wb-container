    function toggle_viewers() {
        var sel = document.getElementById('visibility').value;
        var viewers = document.getElementById('allowed_viewers');
        var helpers = document.getElementById('tooltip');
        var viewersArray = ['private','registered'];
        var helpersArray = ['public','private','registered','hidden','none'];
/**/
        if (viewersArray.indexOf(sel) > -1) {
            viewers.style.display = 'block';
        } else {
            viewers.style.display = 'none';
        }
        var classNodeList = helpers.classList;
        var len = classNodeList.length-1;
        classNodeList.remove(classNodeList[len]);

        if (helpersArray.indexOf(sel) > -1) {
            var data = document.getElementById(sel).dataset.tooltip;
            helpers.classList.add("w3-"+sel);
            document.querySelector("div#tooltip span").innerHTML = data;
        }
    }

    var lastselectedindex = new Array();
    function disabled_hack_for_ie(sel) {
        var sels = document.getElementsByTagName("select");
        var i;
        var sel_num_in_doc = 0;
        for (i = 0; i <sels.length; i++) {
            if (sel == sels[i]) {
                sel_num_in_doc = i;
            }
        }
        // never true for browsers that support option.disabled
        if (sel.options[sel.selectedIndex].disabled) {
            sel.selectedIndex = lastselectedindex[sel_num_in_doc];
        } else {
            lastselectedindex[sel_num_in_doc] = sel.selectedIndex;
        }
        return true;
    }
    if (typeof include_file !== "function"){
    /**/
        include_file = (function(FileName, nodeName) {
            "use strict";
            if (typeof FileName !== "string") {return false;}
            let FileExt = FileName.replace(/^.*\./, '');
            if (FileExt === "css" || FileExt === "js") {
                var headJs,nodeJs,headCss,nodeCss;
                switch (FileExt) {
                    case 'js':
                        headJs = document.getElementsByTagName("SCRIPT")[0].parentNode;
                        nodeJs = document.createElement("SCRIPT");
                        nodeJs.setAttribute("async","async");
                        nodeJs.setAttribute("src",FileName);
                        headJs.appendChild(nodeJs);
                        break;
                    case 'css':
                        headCss = document.head.getElementsByTagName('LINK')[0].parentNode;
                        nodeCss = document.createElement("LINK");
                        nodeCss.setAttribute("rel","stylesheet");
                        nodeCss.setAttribute("href",FileName);
                        headCss.appendChild(nodeCss);
                        break;
                    default:
                }
            };
        });
    }

    var help = document.querySelector(".ico-help");
    if (help && (typeof fb === "undefined")){
        let elm  = help.dataset;
        let FloatboxCss = WB_URL+ '\/include\/plugins\/default\/floatbox\/floatbox.css';
        include_file(FloatboxCss);
    }
    if (help &&(typeof fb === "object")){
//        help.addEventListener("click", function(event){
//        });
    }
/* */
    var selectElm = document.getElementById('visibility');
    toggle_viewers();
    selectElm.addEventListener("change", function(ev){
//console.log(event);
      toggle_viewers(ev);
    },false);


