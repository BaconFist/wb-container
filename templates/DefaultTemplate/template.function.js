$(document).ready(function(){

  if($("a[rel^='lightbox']").length) {
//    $.insert(WB_URL+'/include/jquery/plugins/jquery-slimbox2.css');
      LoadOnFly('head', TEMPLATE_DIR +'/jquery/plugins/jquery-slimbox2.css');
//      LoadOnFly('body', WB_URL+'/include/jquery/plugins/jquery-slimbox2.js');
      $.insert(TEMPLATE_DIR +'/jquery/plugins/jquery-slimbox2-min.js');
  }

  $("a[rel^='lightbox']").css("cursor", "zoom-in");
/*-- Only for coder can be deleted for normal user --*/
  if($("[class^=brush]").length) {
      $.insert(TEMPLATE_DIR+'/jquery/dp.SyntaxHighlighter/styles/shCore.css');
      $.insert(TEMPLATE_DIR+'/jquery/dp.SyntaxHighlighter/styles/shThemeDefault.css');
      $.insert(TEMPLATE_DIR+'/jquery/dp.SyntaxHighlighter/scripts/shCore.all.js');
          $(function(){
            SyntaxHighlighter.config.stripBrs = false;
            SyntaxHighlighter.config.bloggerMode = true;
            SyntaxHighlighter.config.clipboardSwf = "<?php echo WB_URL ?>/include/dp.SyntaxHighlighter/scripts/clipboard.swf";
            SyntaxHighlighter.defaults['gutter'] = true;
            SyntaxHighlighter.defaults['smart-tabs'] = true;
            SyntaxHighlighter.defaults['wrap-lines'] = true;
            SyntaxHighlighter.defaults['html-script'] = true;
            SyntaxHighlighter.all();
         });
       };
/*-- Only for coder --*/
/**
 *
      $.insert(TEMPLATE_DIR+'/js/jquery.showtime.js');
 */
  if($("#timeStamp").length) {
        $.include([
            TEMPLATE_DIR+'/js/update-time.js',
            TEMPLATE_DIR+'/js/jquery.showtime.js'
        ]);
  }

  if($("#showTimeStamp").length) {

      var $log = $( ".message" );
//    console.info('(line  57) : ' + ' found ElementId #timeStamp');
        $.include([
            TEMPLATE_DIR+'/js/jquery.showtime.js'
        ]);
//    console.info('(line  61) : ' + ' try to call fn.showTime ' + $ );
      if ( jQuery.isFunction(jQuery.fn.showTime) ) {
        try {
        $log.append( '<p class="alert alert-sucess rounded">Sucess ' + 'found plugin fn.showTime' + '</p>' );
//    console.info('(line  65) : ' + ' found plugin fn.showTime');
        $('#timeStamp').showTime();
        } catch(err) {
          $log.append( '<p class="alert alert-danger rounded">Error ' + err.message + '</p>' );
//    console.info('(line  69) : ' + err.message);
        }
      } else {
        $log.append( '<p class="alert alert-danger rounded">jQuery Error ' + '$(...).showTime is not a function' + '</p>' );
//    console.info('(line  73) : $(...).showTime is not a function' );
      }

  }

var searchForm   = document.getElementById("search");
var searchString = document.getElementById("searchstring");
var placeHolder  = searchString.value;
var searchButton = document.getElementById("searchButton");

var navbar = document.getElementById("navbar");
var sticky = navbar.offsetTop;

//console.info(searchButton);

//searchForm.addEventListener("focus", myFocusFunction, true);
searchForm.addEventListener("blur", myBlurFunction, true);
//searchButton.addEventListener("click", myBlurFunction, true);

function myFocusFunction() {
    searchstring.value = "";
}

function myBlurFunction() {
//    searchstring.value = placeHolder;
}

    function myFunction() {
//console.info(sticky+'>='+window.pageYOffset);
      if (window.pageYOffset >= sticky) {
          navbar.classList.add("sticky")
      } else {
          navbar.classList.remove("sticky");
      }
    }
window.onload = function () {myFunction()};
window.onscroll = function() {myFunction()};

});
