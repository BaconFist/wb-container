/*-- Addition for remembering expanded state of pages --*/
    function writeSessionCookie (cookieName, cookieValue) {
        if (cookieValue === "1"){
            document.cookie = escape(cookieName) + "=" + escape(cookieValue) + ";samesite=Strict;";
        }
        else{
            document.cookie = escape(cookieName)+"=; max-age=-1;samesite=Strict;";
        }
    }

    function toggle_viewers() {
        var sel = document.getElementById('visibility').value;
        var viewers = document.getElementById('viewers');
        var helpers = document.getElementById('tooltip');
        var viewersArray = ['private','registered'];
        var helpersArray = ['public','private','registered','hidden','none'];
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

    function toggle_visibility(id){
        var toggle = document.getElementById(id);
        if (toggle.style.display === "block") {
            toggle.style.display = "none";
            writeSessionCookie (id, "0");//Addition for remembering expanded state of pages
        } else {
            toggle.style.display = "block";
            writeSessionCookie (id, "1");//Addition for remembering expanded state of pages
        }
    }

    function toggle_plus_minus(id) {
        /* Toggle plus/minus Image */
        var plus = new Image;
        plus.src = THEME_URL+"/images/plus_16.png";
        var minus = new Image;
        minus.src = THEME_URL+"/images/minus_16.png";

        var img_src = document.images['plus_minus_' + id].src;
        if (img_src === plus.src) {
            document.images['plus_minus_' + id].src = minus.src;
        }
        else {
            document.images['plus_minus_' + id].src = plus.src;
        }
        /* Toggle visibility of subcategorie
        if (document.getElementById(id).style.display === "block") {
            document.getElementById(id).style.display = "none";
        }
        else {
            document.getElementById(id).style.display = "block";
        }
*/
    }

    var selectElm = document.getElementById('visibility');
    toggle_viewers();
    selectElm.addEventListener("change", function(ev){
//console.log(event);
      toggle_viewers(ev);
    },false);

