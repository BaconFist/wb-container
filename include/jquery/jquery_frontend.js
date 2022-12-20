/*
jquery_frontend.js
console.log('jQuery === '+typeof jQuery);
*/
if (typeof jQuery !== "undefined"){
//console.info('jQuery Core Version : ' + jQuery.fn.jquery + ' detected');
    try {
        if(jQuery.ui){
            var version = jQuery.ui ? jQuery.ui.version || "1.5.2" : null;
//console.info('jQuery UI   Version : ' + version);
        }
    } catch(err) {
//console.error('(line  13) : ' + err.message);
    }
} else {
//console.info('jQuery not detected : ' );
}

    function include_file(filename, filetype) {
        if(!filetype)
            var filetype = 'js'; //js default filetype
        var th = document.getElementsByTagName('head')[0];
        var s  = document.createElement((filetype == "js") ? 'script' : 'link');
//        s.setAttribute('type',(filetype == "js") ? '' : 'text/css');
        if (filetype == "css")
            s.setAttribute('rel','stylesheet');
        s.setAttribute((filetype == "js") ? 'src' : 'href', filename);
        th.appendChild(s);
    }
