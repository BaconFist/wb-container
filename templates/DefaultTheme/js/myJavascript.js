  var styleTags = document.getElementsByTagName("link");
  for (var i=0, max = styleTags .length; i < max; i++) {
//console.log(styleTags[i]);
//    styleTags [i].parentNode.removeChild(styleTags [i]);
  }

    var w = window.innerWidth;
    var h = window.innerHeight;

    function autoResize(i) {
      var iframeHeight=
        (i).contentWindow.document.body.scrollHeight;
        (i).height=iframeHeight+20;
    }

// Get the error modal
    var modalError = document.getElementById('jsPanel ');
//console.info(modalError);
//Make the DIV element draggagle:
//  dragElement(document.getElementById(("errorModal")));
// Get the button that opens the modal
    var btnError = document.getElementById("delete-error-log");
// When the user clicks the button, open the modal
    btnError.onclick = function() {
      modalError.style.display = "block";
      loadErrorFile();
    }
// Get the <span> element that closes the modal
    var closeErrorBox = document.getElementById('delete-error-log');

    var span_close = document.getElementsByClassName("close")[0];
//  When the user clicks on <close button> (x), close the modal
    closeErrorBox.onclick = function() {
      modalError.style.display = "none";
    }
//  When the user clicks on <span> (x), close the modal
    span_close.onclick = function() {
      modalError.style.display = "none";
    }
//  When the user clicks anywhere outside of the modal, close it
    window.onclick = function(e) {
      //console.log(e.target);
      if (e.target === modalError) {
//console.log(e.target);
        modalError.style.display = "none";
      }
    }

    document.addEventListener('keyup', function(e) {
      if (e.keyCode == 27) {
        modalError.style.display = "none";
      }
    });

/**************************************************/
//Make the DIV element draggagle:
//dragElement(document.getElementById(("myModal")));

function dragElement(elmnt) {
  var pos1 = 0,
      pos2 = 0,
      pos3 = 0,
      pos4 = 0;
  if (document.getElementById(elmnt.id + "_Header")) { /* if present, the header is where you move the DIV from:*/
    document.getElementById(elmnt.id + "_Header").onmousedown = dragMouseDown;
  } else { /* otherwise, move the DIV from anywhere inside the DIV:*/
    elmnt.onmousedown = dragMouseDown;
  }

  function dragMouseDown(e) {
    e = e || window.event;
    // get the mouse cursor position at startup:
    pos3 = e.clientX;
    pos4 = e.clientY;
    document.onmouseup = closeDragElement;
    // call a function whenever the cursor moves:
    document.onmousemove = elementDrag;
  }

  function elementDrag(e) {
    e = e || window.event;
    // calculate the new cursor position:
    pos1 = pos3 - e.clientX;
    pos2 = pos4 - e.clientY;
    pos3 = e.clientX;
    pos4 = e.clientY;
    // set the element's new position:
    elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
    elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
  }

  function closeDragElement() { /* stop moving when mouse button is released:*/
    document.onmouseup = null;
    document.onmousemove = null;
  }
}

