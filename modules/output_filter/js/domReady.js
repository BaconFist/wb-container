// add event cross browser
function addEvent(elem, event, fn) {
    if (elem.addEventListener) {
        elem.addEventListener(event, fn, false);
    } else {
        elem.attachEvent("on" + event, function() {
            // set the this pointer same as addEventListener when fn is called
            return(fn.call(elem, window.event));
        });
    }
}

var logs = [];
var eventSet = false;
var loaded = false;
function log(str) {

    if (loaded) {
        output(str);
    } else {
        logs.push(str);
    }

    function output(str) {
        var o = document.getElementById("log");
        var div = document.createElement("div");
        div.appendChild(document.createTextNode(str));
        o.appendChild(div);
    }

    if (!eventSet) {
        eventSet = true;
        addEvent(window, "load", function() {
            loaded = true;
            for (var i = 0; i < logs.length; i++) {
                output(logs[i]);
            }
            logs = [];
        });
    }
}

(function(funcName, baseObj) {
    // The public function name defaults to window.domReady
    // but you can pass in your own object and own function name and those will be used
    // if you want to put them in a different namespace
    funcName = funcName || "domReady";
    baseObj = baseObj || window;
    var readyList = [];
    var readyFired = false;
    var readyEventHandlersInstalled = false;

    // call this when the document is ready
    // this function protects itself against being called more than once
    function ready() {
        if (!readyFired) {
            // this must be set to true before we start calling callbacks
            readyFired = true;
            for (var i = 0; i < readyList.length; i++) {
                // if a callback here happens to add new ready handlers,
                // the domReady() function will see that it already fired
                // and will schedule the callback to run right after
                // this event loop finishes so all handlers will still execute
                // in order and no new ones will be added to the readyList
                // while we are processing the list
                readyList[i].fn.call(window, readyList[i].ctx);
            }
            // allow any closures held by these functions to free
            readyList = [];
        }
    }

    function readyStateChange() {
    if ( document.readyState === "complete" ) {
            ready();
        }
    }

    // This is the one public interface
    // domReady(fn, context);
    // the context argument is optional - if present, it will be passed
    // as an argument to the callback
    baseObj[funcName] = function(callback, context) {
        // if ready has already fired, then just schedule the callback
        // to fire asynchronously, but right away
        if (readyFired) {
            setTimeout(function() {callback(context);}, 1);
            return;
        } else {
            // add the function and context to the list
            readyList.push({fn: callback, ctx: context});
        }
        // if document already ready to go, schedule the ready function to run
        if (document.readyState === "complete") {
            setTimeout(ready, 1);
        } else if (!readyEventHandlersInstalled) {
            // otherwise if we don't have event handlers installed, install them
            if (document.addEventListener) {
                // first choice is DOMContentLoaded event
                document.addEventListener("DOMContentLoaded", ready, false);
                // backup is window load event
                window.addEventListener("load", ready, false);
            } else {
                // must be IE
                document.attachEvent("onreadystatechange", readyStateChange);
                window.attachEvent("onload", ready);
            }
            readyEventHandlersInstalled = true;
        }
    }
})("domReady", window);

function confirm_link(message, url) {
    if (confirm(message)){ location.href = url;} else {return false;}
}

/**
 *
// test basic functionality
domReady(function() {
    document.body.appendChild(document.createTextNode("Hello Text 1"));
    // test adding new domReady handler from a domReady callback
    domReady(function() {
        document.body.appendChild(document.createTextNode(", Hello Text 2"));
    });
});

// test finding an ID in the document
domReady(function() {
    document.getElementById("test").innerHTML = "Hello ID";
});

// test calling domReady after window load and
// domReady has already fired
addEvent(window, "load", function() {
    setTimeout(function() {
    document.body.appendChild(document.createTextNode(", Hello Text 2.5"));

        domReady(function(arg) {
            document.body.appendChild(document.createTextNode(arg));
        }, ", Hello Text 3");
    }, 1);
});
})();
 */
