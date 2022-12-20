// loadScript - Loads a single Javascript file into the browser
// Arguments:
//    url - the address of the script file
//    callback - called when the script has been loaded
var loadScript = (function(url, callback) {
   if (callback == null) { callback = function() {}; }
//   var head = document.getElementsByTagName("head")[0];
   var body = document.getElementsByTagName("body")[0];
   var script = document.createElement("script");

   script.type = "text/javascript";
   script.addEventListener("load", function() {
      script.onLoad = null;
      callback(script);
   });
   script.onLoad = function () {};
   script.src = url;
//   head.appendChild(script);
   body.appendChild(script);
});

