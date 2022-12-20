/**
 *
 */
domReady(function() {

//    LoadOnFly ( 'body', WB_URL + '/modules/droplets/js/draggabilly.pkgd.js' );
//    LoadOnFly ( 'body', WB_URL + '/modules/droplets/js/modal.js' );

    function toggle_visibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }
// CHANGELOG 20160323
    function addEvent(elem, event, fn) {
        if(!elem) { return false; }
//console.info (elem);
        if (elem.addEventListener) {
            elem.addEventListener(event, fn, false);
        } else {
            elem.attachEvent("on" + event, function() {
                // set the this pointer same as addEventListener when fn is called
                return(fn.call(elem, window.event));
            });
        }
    }

    function mouseX (e) {
      if (e.pageX) {
        return e.pageX;
      }
      if (e.clientX) {
        return e.clientX + (document.documentElement.scrollLeft ?
                            document.documentElement.scrollLeft :
                            document.body.scrollLeft);
      }
      return null;
    }

    function mouseY (e) {
      if (e.pageY) {
        return e.pageY;
      }
      if (e.clientY) {
        return e.clientY + (document.documentElement.scrollTop ?
                            document.documentElement.scrollTop :
                            document.body.scrollTop);
      }
      return null;
    }

    function dragable (clickEl,dragEl) {
      var p = get(clickEl);
      var t = get(dragEl);
      var drag = false;
      offsetX = 0;
      offsetY = 0;
      var mousemoveTemp = null;
      if (t) {
        var move = function (x,y) {
          t.style.left = (parseInt(t.style.left)+x) + "px";
          t.style.top  = (parseInt(t.style.top) +y) + "px";
        }
        var mouseMoveHandler = function (e) {
          e = e || window.event;
          if(!drag){return true};
          var x = mouseX(e);
          var y = mouseY(e);
          if (x != offsetX || y != offsetY) {
            move(x-offsetX,y-offsetY);
            offsetX = x;
            offsetY = y;
          }
          return false;
        }
        var start_drag = function (e) {
          e = e || window.event;
          offsetX=mouseX(e);
          offsetY=mouseY(e);
          drag=true; // basically we're using this to detect dragging
          // save any previous mousemove event handler:
          if (document.body.onmousemove) {
            mousemoveTemp = document.body.onmousemove;
          }
          document.body.onmousemove = mouseMoveHandler;
          return false;
        }
        var stop_drag = function () {
          drag=false;
          // restore previous mousemove event handler if necessary:
          if (mousemoveTemp) {
            document.body.onmousemove = mousemoveTemp;
            mousemoveTemp = null;
          }
          return false;
        }
        p.onmousedown = start_drag;
        p.onmouseup = stop_drag;
      }
    }

    function move(ev) {
      ev.dataTransfer.setData('text', ev.target.id);
    }

    window.addEventListener("DOMContentLoaded",function () {
      initCheckboxes();
//      addEvent(document.getElementById('selectOrder'), 'change', changeOrder);
/*
      var dragItems = document.querySelectorAll("[draggable=true]")
      for (var i = 0; i < dragItems.length; i++) {
        var draggable = dragItems[i];
        draggable.addEventListener("dragstart",move);
      };
*/
    });

//    addEvent( window, 'load', initCheckboxes );
    function initCheckboxes() {
        addEvent(document.getElementById('select_all'), 'click', setCheckboxes);
    }
    function setCheckboxes() {
        var cb = document.getElementById('cb-droplets').getElementsByTagName('input');
        var isChecked = document.getElementById('select_all').checked;
        for (var i = 0; i < cb.length; i++) {
            cb[i].checked = isChecked;
        }
    }
/* */
    function selectSingleElement(IdSuffix, el ) {
//console.log(IdSuffix);
        document.getElementById(el.id + IdSuffix).checked ='checked';
        document.getElementById('select_all').checked =false;
    }

    function deselectAllElements(IdSuffix, el ) {
        for ( i = 0;; i++) {
            if (!(e = document.getElementById('L' + i + IdSuffix))) {
                break;
            }
            e.checked = el.checked;
        }
    }
    function changeOrder(){
//console.info(this);
    }
});

/*-------------------------------------------------------------------------------------------------*/

  document.addEventListener('DOMContentLoaded', function(){

    if (typeof Droplet ==="object"){
        var DR_MODULE_URL = Droplet.AddonUrl;
        var DR_ICONS = Droplet.ThemeUrl + 'img';
        var DR_AJAX_PLUGINS =  Droplet.AddonUrl + 'ajax';  // this var could change in the future
        var LANGUAGE = LANGUAGE ? LANGUAGE : 'EN'; // set var LANGUAGE to EN if LANGUAGE not set before
        /*
        console.info(DR_ICONS);
        console.info(DR_AJAX_PLUGINS);
        */
//        console.info(Droplet);
                $.insert(  Droplet.AddonUrl + 'ajax' +"/ajaxActiveStatus.js");
                // AjaxHelper change item active status
                $("td.toggle_active_status").ajaxActiveStatus({
                        MODULE : Droplet.AddonUrl,
                        DB_COLUMN: 'id',
                        sIdKey: ''
                });
    //
        var DropletCss = Droplet.AddonUrl+"backend.css";
        if (typeof LoadOnFly!=='function' || typeof LoadOnFly==='undefined' || !LoadOnFly){
            $.insert(DropletCss);
//            $.insert(JsPanelJs);
        } else {
            LoadOnFly('head', DropletCss);
//            LoadOnFly('head', JsPanelJs);
        }
    }

  }, false);
