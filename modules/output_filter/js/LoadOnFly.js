/**
 * http://molily.de/js/
 * Cross browser addEvent function by John Resig
 * http://ejohn.org/blog/flexible-javascript-events/
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
    callback.call(scope, i, array[i]); // passes back stuff we need
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
 *  http://www.axel-hahn.de/blog/2015/01/21/javascript-schnipsel-html-strippen/
 */

function strip_tags(s) {
  return s.replace(/<[^>]*>/g, '');
}
/**                                                                 
 *         discuss at: http:phpjs.org/functions/dirname/
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
 * http://durhamhale.com/blog/javascript-version-of-phps-str-replace-function
 */
var str_replace = function (search, replace, string) {
  return string.split(search).join(replace);
};
/**
 *  trim, rtrim, ltrim
 *  http://coursesweb.net/javascript/trim-rtrim-ltrim-javascript_cs
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
  if (confirm(message)) location.href = url;
};
var showMessage = (function (txt, sel) {
  var result = window.document.getElementById('messages');
  if (!result) {
    return false;
  }
  var elm = document.createElement('P');
  elm.setAttribute('class', sel + ' rounded');
  elm.appendChild(document.createTextNode(txt));
  result.appendChild(elm);
});
/**
 *  http://www.javascriptkit.com/dhtmltutors/treewalker.shtml
 *
 */
/********************************************************************************************************/
var LoadOnFly = (function ( nodeName, file ) {
    'use strict';
    if( (typeof file === 'undefined') ) {
      return false;
    }
    if ( !document.doctype ) {
      return false;
    }
  /*
  var nodeDoctype = document.implementation.createDocumentType(
   'html','',''
  );
      document.replaceChild(nodeDoctype, document.doctype);
  } else {
      document.insertBefore(nodeDoctype, document.childNodes[0]);
  }
*/
  //    var LoadOnFly  = function (nodeName, url) {

    var jsRegex = /.js$/gi;
    var cssRegex = /.css$/gi;
    var scripts = {
    };
  // console.info(' 0.' + file );fileExtension = file.replace(/^.*\./, '');
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
          break;
        default:
          NodeList = document.head.querySelectorAll('SCRIPT');
          break;
      }
      if (NodeList) {
        len = NodeList.length - 1;
    }
  //console.info(NodeList);
  // console.info(' JS ' + url);

    try {
     var js = document.createElement('SCRIPT');
      js.setAttribute('type', 'text/javascript'); // optional, if not a html5 node
      js.setAttribute('src', url); // src setzen
      js.setAttribute('charset', 'UTF-8');
//      js.setAttribute("async", true); // HTML5 Asyncron attribute
      done = false;
      if (nodeName == 'body') {
        node = window.document.body.querySelectorAll('SCRIPT') [len];
        node.parentNode.appendChild( js );
console.info( js );
        //              script.parentNode.insertBefore(js,script);
      } else {
        node = window.document.head.querySelectorAll('SCRIPT') [len];
        node.parentNode.appendChild( js );
      }
    } catch (e) {
       str = '<script type=\'text/javascript\' src=\'' + url + '\' charset="UTF-8"><' + '/script>';
      document.write(str);
    }
console.info( node );
  }

// load css only within head
if ((typeof url !== 'undefined') && (urlExt === 'css')) {
    //console.info(urlExt + ' = 2.) ' + url);
    scripts[url] = false;
    try {
        var css = document.createElement('LINK');
        len = 0;
        css.setAttribute('type', 'text/css');
        css.setAttribute('rel', 'stylesheet');
        css.setAttribute('media', 'all');
        css.setAttribute('href', url);
        NodeList = window.document.querySelectorAll('LINK');
        if (NodeList) {
          len = NodeList.length - 1;
        };
        // insert after last link element if exist otherwise before first script
        if (len > - 1) {
          node = window.document.head.querySelectorAll('LINK') [len];
          // console.info( len );
        //  console.info(node);
          //    return false;
          node.parentNode.insertBefore(css, node.nextSibling);
          // console.info('CSS ' + url);
        } else {
          node = window.document.head.querySelectorAll('SCRIPT') [0];
          node.parentNode.insertBefore(css, node);
        }
    } catch (e) {
        str = '<link href=\'' + url + '\' media="all" rel="stylesheet" />';
        document.write(str);
    }
}
// console.info( url );
//      showMessage(url);

});
/**
 * 
    document.onreadystatechange = function () {
        if (document.readyState == "interactive") {
console.info( 'Start readyState.interactive' );
        }
    }

    // Alternativ zu load event
    document.onreadystatechange = function () {
        if (document.readyState == "complete") {
console.info( 'Start readyState.complete' );
        }
    }

window.onload = function() {
   addEvent(document, "DOMContentLoaded", LoadOnFly);
console.info( 'Start window.onload' );
};
 */
/*
undefined
*/
