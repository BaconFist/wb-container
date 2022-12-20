    let addon = "admintools";
    var ScriptPath = (function() {
      var scripts = document.getElementById(addon);
//console.log(scripts);
      var path = null;
      if (scripts) {
          if ((scripts.getAttribute.length !== undefined)) {
            path = scripts.getAttribute('hidden');
          } else {
            path = scripts.getAttribute('hidden', 2);
          }
          if (path) {
            path = path.substr(0,path.lastIndexOf('/'));
          }
      }
      if (typeof path === null) {path = location.pathname;}
      return path;
    }());

    function deleteFile (addon) {
        var watchSettings = new CookieSettings();
        if ('localStorage' in window && window['localStorage'] !== null)
        {
            localStorage.removeItem(addon);
        } else {
            if (is_cookie) {
                watchSettings.setCookie(addon, "", -1);
            }
        }
    }

    function readFile (addon) {
        if ('localStorage' in window && window['localStorage'] !== null)
        {
            key = localStorage.getItem(addon);
//console.log(typeof key);
            key = ((typeof key ==="string") ? key : 2);
        } else {
            var watchSettings = new CookieSettings();
            var key = watchSettings.getCookie(addon);
            var key = ((typeof key!=='undefined') ? key : 2);
        }
//console.log(key);
        return key;
    }

    function saveFile(addon, key){
        var watchSettings = new CookieSettings();
        var is_cookie;
        if ('localStorage' in window && window['localStorage'] !== null)
        {
//            key = localStorage.setItem(addon);
//            key = (typeof key!=='null' ? key : false);
            if (key){
//                localStorage.removeItem(addon);
                localStorage.setItem(addon, key);
            }
        }
        else {
//                is_cookie = watchSettings.getCookie(addon);
//                is_cookie = (typeof is_cookie!=='undefined' ? is_cookie : false);
// ------------------------------------------------------------------------
                watchSettings.setCookie(addon, key, 30);
/*
            if (is_cookie) {
                watchSettings.setCookie(addon, "", -1);
            } else {
            };
*/
        }
    }

    function setGridColumns(quantity){
        column = document.querySelectorAll("#"+addon+" > [id^=equal]");
        // get attribute name
        selector = column[0].style[0];
        repeat = "repeat("+quantity+", 1fr)";
        // get old value
        oldStyle = column[0].attributes.style;
        // create new style
        newStyle = column[0].style[0] + ": " + repeat + ";";
        column[0].removeAttribute("style", oldStyle);
        column[0].setAttribute("style", newStyle);
    }

//console.log(ScriptPath);
    function registerEvent(el){
        var quantity = el.target.value;
        setGridColumns(quantity);
        return quantity;
    }

    function CookieSettings() {
        var CookieLifetime = 7; // lifetime of the cookie in days
        this.setCookie = function(cname,cvalue,exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays*86400000));
            expires = "expires=" + d.toGMTString();
            Secure = "secure="+(window.location.protocol == "https:")+"; ";
            HttpOnly = "HttpOnly=true; ";
            SameSite="samesite=strict; ";
            ScriptPath = dirname(location.pathname);
            Domain = location.domain;
            cookieString = cname+"="+cvalue+"; path="+ScriptPath+"; "+expires+"; domain="+Domain+"; "+SameSite+Secure;
            document.cookie = cookieString;
//console.log(cookieString);
        };

        this.getCookie = function(cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for(var i=0; i<ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1);
                if (c.indexOf(name) === 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        };
    };

    function toggleCellBtn(key){
        btn = document.querySelectorAll('input[id*=fr]');
        if (btn) {
            for(var i=0; i < btn.length; i++ ){
                isChecked = btn[i].checked;
                elmChild  = btn[i].nextElementSibling.children[0];
                if (isChecked){
                    elmChild.className = elmChild.className.replace("w3-text-grey", "w3-text-blue");
                } else {
                    elmChild.className = elmChild.className.replace("w3-text-blue", "w3-text-grey");
                }
            }
        }
    }

/* --------------------------------------------------------------------------------- */
domReady(function() {
        var btn = document.querySelectorAll('input[name*=fr]');
        if (typeof btn !== 'undefined'){
        // handle click button
            if (btn.length > 0) {
              for(var i=0; i < btn.length; i++ ){
                  btn[i].addEventListener("click",function (el) {
                    key = registerEvent(el);
                    saveFile(addon, key);
                    toggleCellBtn(key);
                  });
              }
            }
        }
// setter for ckecked radio buttons
        if (btn.length > 0) {
            aDefaultIndex = [1,2,3];
            key = readFile(addon);
            index = (key && (aDefaultIndex.indexOf(key) === -1) ? key-1 : 1);
            btn[index].checked = true;
            setGridColumns(key);
            toggleCellBtn(key);
        }

/*
console.log(index);
//console.log(oldStyle);
*/
/* --------------------------------------------------------------------------------- */

});
