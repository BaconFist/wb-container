/**
 * https://molily.de/js/
 * Cross browser addEvent function by John Resig
 * https://johnresig.com/blog/flexible-javascript-events/
 * some samples
 *    addEvent( document.getElementById('foo'), 'click', doSomething );
 *    addEvent( obj, 'mouseover', function(){ alert('hello!'); } );
 *
 */
/**
 * Cross Browser helper to addEventListener.
 * http://webintersect.com/articles/72/add-event-listener-to-dynamic-elements
 *
 * @param {HTMLElement} obj The Element to attach event to.
 * @param {string} evt The event that will trigger the binded function.
 * @param {function(event)} fnc The function to bind to the element.
 * @return {boolean} true if it was successfuly binded.
 */

    var addEvent = function (obj, evt, fnc) {
      // W3C model
      if (obj.addEventListener) {
        obj.addEventListener(evt, fnc, false);
        return true;
      }
      // Microsoft model
       else if (obj.attachEvent) {
        return obj.attachEvent('on' + evt, fnc);
      }
      // Browser don't support W3C or MSFT model, go on with traditional
       else {
        evt = 'on' + evt;
        if (typeof obj[evt] === 'function') {
          // Object already has a function on traditional
          // Let's wrap it with our own function inside another function
          fnc = (function (f1, f2) {
            return function () {
              f1.apply(this, arguments);
              f2.apply(this, arguments);
            };
          }) (obj[evt], fnc);
        }
        obj[evt] = fnc;
        return true;
      }
      return false;
    };
/*****************************************************************************/
/**
 * sample
 *   removeEvent( object, eventType, function );
 *
 */
    function removeEvent(obj, ev, fn) {
      if (obj.detachEvent) {
        obj.detachEvent('on' + ev, obj[ev + fn]);
        obj[ev + fn] = null;
      } else
      obj.removeEventListener(ev, fn, false);
    }
/*****************************************************************************/
    if (!Array.from) {
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
          // 17. Repeat, while k < len? (also steps a - h)
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
    }
/*****************************************************************************/
    var getBrowser = (function () {
      var navigatorObj = navigator.appName,
      userAgentObj = navigator.userAgent,
      matchVersion;
      var match = userAgentObj.match(/(opera|opr|chrome|safari|firefox|msie|trident)\/?\s*(\.?\d+(\.\d+)*)/i);
      if (match && (matchVersion = userAgentObj.match(/version\/([\.\d]+)/i)) !== null) {
        match[2] = matchVersion[1];
      }
      //mobile
      if (navigator.userAgent.match(/iPhone|Android|webOS|iPad/i)) {
        var mobile;
        return match ? [
          match[1],
          match[2],
          mobile
        ] : [
          navigatorObj,
          navigator.appVersion,
          mobile
        ];
      }
      // web browser
      return match ? [
        match[1],
        match[2]
      ] : [
        navigatorObj,
        navigator.appVersion,
        '-?'
      ];
    }) ();
    // forEach method, could be shipped as part of an Object Literal/Module
    var forEach = function (array, callback, scope) {
      for (var i = 0; i < array.length; i++) {
        callback.call(scope, i, array[i]); //  passes back stuff we need
      }
    };
    function each(elm, fn) {
      for (var i = 0, l = elm.length; i < l; i++) {
        fn.call(elm, elm[i], i);
      }
    }
    function doSomething(elm) {
      if ((typeof elm !== 'undefined') || elm) console.log(elm);
    }
/**
 *  https://www.axel-hahn.de/blog/2015/01/21/javascript-schnipsel-html-strippen/
 */
    function strip_tags(s) {
      return s.replace(/<[^>]*>/g, '');
    }
/**
 *         discuss at: https://phpjs.org/functions/dirname/
 *               http: kevin.vanzonneveld.net
 *        original by: Ozh
 *        improved by: XoraX (http:www.xorax.info)
 *          example 1: dirname('/etc/passwd');
 *          returns 1: '/etc'
 */
    var dirname = function (path) {
      var tmp = path.replace(/\\/g, '/').replace(/\/[^\/]*\/?$/, '');
      return tmp;
    };
/**
 * https://durhamhale.com/blog/javascript-version-of-phps-str-replace-function
 */
    var str_replace = function (search, replace, string) {
      return string.split(search).join(replace);
    };
/**
 *  trim, rtrim, ltrim
 *  https://coursesweb.net/javascript/trim-rtrim-ltrim-javascript_cs
 */
    var trim = function (str, chr) {
      var rgxtrim = (!chr) ? new RegExp('^\\s+|\\s+$', 'g')  : new RegExp('^' + chr + '+|' + chr + '+$', 'g');
      return str.replace(rgxtrim, '');
    };
    var rtrim = function (str, chr) {
      var rgxtrim = (!chr) ? new RegExp('\\s+$')  : new RegExp(chr + '+$');
      return str.replace(rgxtrim, '');
    };
    var ltrim = function (str, chr) {
      var rgxtrim = (!chr) ? new RegExp('^\\s+')  : new RegExp('^' + chr + '+');
      return str.replace(rgxtrim, '');
    };
    var confirm_link = function (message, url) { //  class="alert rounded"
      if (confirm(message)){
        location.href = url;
      }
    };
    var showMessage = (function (txt, sel) {
      var result = window.document.getElementById('messages');
      if (!result) {return false;}
      var elm = document.createElement('P');
      elm.setAttribute('class', sel + ' rounded');
      elm.appendChild(document.createTextNode(txt));
      result.appendChild(elm);
    });

/**
 *  https://www.javascriptkit.com/dhtmltutors/treewalker.shtml
 */
/********************************************************************************************************/
    var LoadOnFly = (function ( nodeName, file ) {
        'use strict';
        if ((typeof file === 'undefined') ) {return false;}
        if (!document.doctype ) {return false;}
        var jsRegex = /.js$/gi;
        var cssRegex = /.css$/gi;
        var scripts = {};
        var url = file;
        var urlExt = trim(file.replace(/^.*\./, ''));
        var NodeList = null;
        var len = 0;
        var node = null;
        var str = 'undefined';
        var done = false;
      //console.info( urlExt + ' = 1.) ' + url);
        if ((typeof url !== 'undefined') && (urlExt === 'js')) {
      //    console.info(urlExt + ' = 1.) ' + url);
          scripts[url] = false;
          switch (nodeName) {
            case 'body':
              NodeList = document.body.querySelectorAll('SCRIPT');
//console.log(NodeList);
              break;
            default:
              NodeList = document.head.querySelectorAll('SCRIPT');
              break;
          }
          if (NodeList) { len = NodeList.length - 1;}
      //console.info(NodeList);
      // console.info(' JS ' + url);
        try {
         var js = document.createElement('SCRIPT');
    //      js.setAttribute('type', 'text/javascript'); // optional, if not a html5 node
          js.setAttribute('src', url); // src setzen
          js.setAttribute('charset', 'UTF-8');
    //      js.setAttribute("async", true); // HTML5 Asyncron attribute
          done = false;
          if (nodeName == 'body') {
            node = window.document.body.querySelectorAll('SCRIPT') [len];
            node.parentNode.appendChild( js );
    //console.info( js );
            //              script.parentNode.insertBefore(js,script);
          } else {
            node = window.document.head.querySelectorAll('SCRIPT') [len];
            node.parentNode.appendChild( js );
          }
        } catch (e) {
            str = '<script src=\'' + url + '\' charset="UTF-8"><' + '/script>';
            document.write(str);
        }
    //console.info( node );
      }
    // load css only within head
    if ((typeof url !== 'undefined') && (urlExt === 'css')) {
        //console.info(urlExt + ' = 2.) ' + url);
        scripts[url] = false;
        try {
            var NodeList = window.document.head.querySelectorAll('HEAD LINK[rel=stylesheet]');
            var css = document.createElement('LINK');
            len = 0;
            css.setAttribute('rel', 'stylesheet');
    //        css.setAttribute('type', 'text/css');
            css.setAttribute('href', url);
            css.setAttribute('media', 'all');
            if (NodeList) {
              len = NodeList.length - 1;
            };
            // insert after last link element if exist otherwise before first script
            if (len > - 1) {
              node = NodeList[len];
    //console.info( node );
            //  console.info(node);
              //    return false;
              node.parentNode.insertBefore(css, node.nextSibling);
              // console.info('CSS ' + url);
            } else {
              node = window.document.head.querySelectorAll('SCRIPT') [0];
              node.parentNode.insertBefore(css, node);
            }
        } catch (e) {
            str = '<link rel="stylesheet" href=\'' + url + '\' media="all" />';
            document.write(str);
        }
    }
    });

window.addEventListener('DOMContentLoaded', LoadOnFly, false);

