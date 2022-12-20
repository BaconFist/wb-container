var JQUERY_THEME = WB_URL + '/include/jquery';
$(document).ready(function() {
  if (typeof LoadOnFly === 'undefined') {
    $.insert(JQUERY_THEME + '/jquery-ui.css');
  } else {
    LoadOnFly('head', JQUERY_THEME + '/jquery-ui.css');
  }
});