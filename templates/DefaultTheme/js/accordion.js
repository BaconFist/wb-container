
    if (!Array.from) {
//console.log("Array.from");
      Array.from = (function () {
        var toStr = Object.prototype.toString;
        var isCallable = function (fn) {
          return typeof fn === 'function' || toStr.call(fn) === '[object Function]';
        };
        var toInteger = function (value) {
          var number = Number(value);
          if (isNaN(number)) { return 0; }
          if (number === 0 || !isFinite(number)) { return number; }
          return (number > 0 ? 1 : -1) * Math.floor(Math.abs(number));
        };
        var maxSafeInteger = Math.pow(2, 53) - 1;
        var toLength = function (value) {
          var len = toInteger(value);
          return Math.min(Math.max(len, 0), maxSafeInteger);
        };
        // The length property of the from method is 1.
        return function from(arrayLike/*, mapFn, thisArg */) {
          // 1. Let C be the this value.
          var C = this;
          // 2. Let items be ToObject(arrayLike).
          var items = Object(arrayLike);
          // 3. ReturnIfAbrupt(items).
          if (arrayLike == null) {
            throw new TypeError("Array.from requires an array-like object - not null or undefined");
          }
          // 4. If mapfn is undefined, then let mapping be false.
          var mapFn = arguments.length > 1 ? arguments[1] : void undefined;
          var T;
          if (typeof mapFn !== 'undefined') {
            // 5. else
            // 5. a If IsCallable(mapfn) is false, throw a TypeError exception.
            if (!isCallable(mapFn)) {
              throw new TypeError('Array.from: when provided, the second argument must be a function');
            }
            // 5. b. If thisArg was supplied, let T be thisArg; else let T be undefined.
            if (arguments.length > 2) {
              T = arguments[2];
            }
          }
          // 10. Let lenValue be Get(items, "length").
          // 11. Let len be ToLength(lenValue).
          var len = toLength(items.length);
          // 13. If IsConstructor(C) is true, then
          // 13. a. Let A be the result of calling the [[Construct]] internal method of C with an argument list containing the single item len.
          // 14. a. Else, Let A be ArrayCreate(len).
          var A = isCallable(C) ? Object(new C(len)) : new Array(len);
          // 16. Let k be 0.
          var k = 0;
          // 17. Repeat, while k < len… (also steps a - h)
          var kValue;
          while (k < len) {
            kValue = items[k];
            if (mapFn) {
              A[k] = typeof T === 'undefined' ? mapFn(kValue, k) : mapFn.call(T, kValue, k);
            } else {
              A[k] = kValue;
            }
            k += 1;
          }
          // 18. Let putStatus be Put(A, "length", len, true).
          A.length = len;
          // 20. Return A.
          return A;
        };
      }());
    };

// MyClass.prototype.ScriptPath = (function() {
    var ScriptPath = (function() {
      var scripts = document.getElementById('SettingsId');
      var path = '';
      if (scripts.getAttribute.length !== undefined) {
        path = scripts.getAttribute('hidden');
      } else {
        path = scripts.getAttribute('hidden', 2);
      }
      if (path) {
        path = path.substr(0,path.lastIndexOf('/'));
      }
      return path;
    }());

//console.log(ScriptPath);

    function CookieSettings() {
        var CookieLifetime = 7; // lifetime of the cookie in days
        var thisObject = this;
        this.setCookie = function(cname,cvalue,exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays*86400000));
            expires = "expires=" + d.toGMTString();
            Secure = "secure="+(window.location.protocol == "https:")+"; ";
            HttpOnly = "HttpOnly=true; ";
            SameSite="samesite=Strict; ";
            document.cookie = cname+"="+cvalue+"; path="+ScriptPath+"; "+expires+"; "+SameSite+Secure;
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

        this.showSettings = (function (elm){
            let elmNext   = elm.previousElementSibling;
            let elmChild  = elmNext.firstElementChild;
            elm.className = elm.className.replace("w3-hide","w3-show");
            elmNext.className =  elmNext.className.replace("w3-light-grey", "w3-sand");
            elmChild.className = elmChild.className.replace("fa-arrow-down", "fa-arrow-up");
        });

        this.hideSettings = (function (elm){
            let elmNext   = elm.previousElementSibling;
            let elmChild  = elmNext.firstElementChild;
            elm.className = elm.className.replace("w3-show","w3-hide");
            elmNext.className = elmNext.className.replace("w3-sand", "w3-light-grey");
            elmChild.className = elmChild.className.replace("fa-arrow-up", "fa-arrow-down");
        });
    }

    function ToogleSettings(id) {
        var elm = document.getElementById(id);
        var elmNext   = elm.previousElementSibling;
        var elmChild  = elmNext.firstElementChild;
        var watchSettings = new CookieSettings();
        var is_cookie;
        if ('localStorage' in window &&
            window['localStorage'] !== null) {
            key = localStorage.getItem("Settings_"+id);
            key = (typeof is_cookie!=='null' ? key : false);
            if (key){
                watchSettings.hideSettings(elm)
                localStorage.removeItem("Settings_"+id);
            } else {
                watchSettings.showSettings(elm);
                localStorage.setItem("Settings_"+id, true);
            }
        }
        else {
                is_cookie = watchSettings.getCookie('Settings_'+id);
                is_cookie = (typeof is_cookie!=='undefined' ? is_cookie : false);
// ------------------------------------------------------------------------
            if (is_cookie) {
                watchSettings.hideSettings(elm);
                watchSettings.setCookie('Settings_'+id, "", -1);
            } else {
                watchSettings.showSettings(elm);
                watchSettings.setCookie('Settings_'+id, true, 30);
            };
        }
    };

domReady(function() {

    aDivs = document.querySelectorAll('.w3-toggle');
    var aList = Array.from(aDivs);
    aList.forEach(function(evt) {
        var id  = evt.nextElementSibling.id;
        var elm = document.getElementById(id);
        var watchSettings = new CookieSettings();
        if ('localStorage' in window &&
            window['localStorage'] !== null) {
            key = localStorage.getItem("Settings_"+id);
            key = (typeof is_cookie!=='null' ? key : false);
            if (key){
                watchSettings.showSettings(elm);
            } else {
                watchSettings.hideSettings(elm)
            }
        }
        else {
            var is_cookie = watchSettings.getCookie('Settings_'+id);
            var is_cookie = (typeof is_cookie!=='undefined' ? is_cookie : false);
            if (is_cookie) {
                watchSettings.showSettings(elm);
            } else {
                watchSettings.hideSettings(elm);
            }
        }
    });

});
