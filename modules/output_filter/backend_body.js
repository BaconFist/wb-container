/**
 *
 */
domReady(function() {

    function toggle_visibility(id) {
       let elm = document.getElementById(id);
       if (elm) {
//console.log(elm);
          if(elm.style.display === 'block'){
            elm.style.display = 'none';
          } else {
            elm.style.display = 'block';
          }
       }
    }

    function initCheckboxes() {
        addEvent(document.getElementById('select_all'), 'click', setCheckboxes);
    }
    function setCheckboxes() {
        var cb = document.getElementById( 'cb-filters' ).getElementsByTagName('input');
//console.info(cb);
        var isChecked = document.getElementById('select_all').checked;
        for (var i = 0; i < cb.length; i++) {
            cb[i].checked = isChecked;
        }
    }

    function selectSingleElement(IdSuffix, el ) {
        document.getElementById(el.id + IdSuffix).checked ='checked';
        document.getElementById('select_all').checked =false;
    }

    function deselectAllElements(IdSuffix, el ) {
        var cb = document.getElementById( 'cb-filters' ).getElementsByTagName('input');
        for ( i = 0; i < cb.length; i++) {
            if (!(e = document.getElementById('L' + i + IdSuffix))) {
                break;
            }
            e.checked = el.checked;
        }
    }

    var elm = document.getElementById("selectFilterContent");
    var email = document.getElementById("cb-Email");
    if (email){
        if (elm) {
            if (email.checked === true) {
                elm.style.display = 'block';
            } else {
              elm.style.display = 'none';
            }
            email.addEventListener("click", function() {
                toggle_visibility("selectFilterContent");
            }, false);

        }
    };
//    toggle_visibility("selectFilterContent");
    var ext = document.getElementById("selectW3cssFilter");
    var w3css = document.getElementById("cb-W3Css");
    if (w3css){
        if (ext) {
            if (w3css.checked === true) {
                ext.style.display = 'block';
            } else {
              ext.style.display = 'none';
            }
            w3css.addEventListener("click", function() {
                toggle_visibility("selectW3cssFilter");
            }, false);
        }
    };


    function toggle_mailfilter(elm){
        var filter = document.getElementsByClassName("cb-emailfilter");
        if (filter){
            for ( i = 0; i < filter.length; i++) {
                if (mail.checked === true) {
                    filter[i].removeAttribute("style")
                } else {
                    filter[i].setAttribute("style", "display:none;")
                }
//console.log(filter[i]);
            }//for
        }
    }

    function toggle_modfiles(elm){
        var ext = document.getElementsByClassName("register-mod-files");
        var info = document.getElementById("help-modfiles");
        if (ext){
            for ( i = 0; i < ext.length; i++) {
                if (reg.checked === true) {
                    ext[i].setAttribute("style", "display:none;")
                } else {
                    ext[i].removeAttribute("style")
                }
//console.log(ext[i]);
            }//for
          if (info){
                if (reg.checked === true) {
                    info.removeAttribute("style")
                } else {
                    info.setAttribute("style", "visibility:hidden;")
                }
          }
        }
    }

    var reg = document.getElementById("cb-RegisterModFiles");
    if (reg){
//console.log(reg);
        toggle_modfiles(reg.checked)
        reg.addEventListener("click", function() {
            toggle_modfiles(reg.checked);
        }, false);
    }

    var mail = document.getElementById("email_filter");
    if (mail){
//console.log(mail);
        toggle_mailfilter(mail.checked)
        mail.addEventListener("click", function() {
            toggle_mailfilter(mail.checked);
        }, false);
    }
/*
    // Get the modal
    var modal1 = document.getElementById('shortsettings');
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
      if (event.target == modal1) {
        modal1.style.display = "none";
      }
    }
*/
/*
// Get the modal
    var modal = document.querySelector("#editsettings");
    // When the user clicks anywhere outside of the modal, close it
     window.onclick = function(event) {
    //
console.log(event.target)
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }
*/
});


    function insert(textarea, what){
        var what_insert = "{"+what+"}";
        if (textarea.createTextRange){
            textarea.focus();
            document.selection.createRange().duplicate().text = what_insert;
        }
        else if ((typeof textarea.selectionStart) !== 'undefined'){ // für Mozilla  &lbrace;
            var selEnd = textarea.selectionEnd;
            var txtLen = textarea.value.length;
            var txtbefore = textarea.value.substring(0,selEnd);
            var txtafter =  textarea.value.substring(selEnd, txtLen);
            var oldScrollTop = textarea.scrollTop;
            textarea.value = txtbefore + what_insert + txtafter;
            textarea.selectionStart = txtbefore.length + what_insert.length;
            textarea.selectionEnd = txtbefore.length + what_insert.length;
            textarea.scrollTop = oldScrollTop;
        }
        else
        {
            textarea.value += what_insert;
        }
        var button = document.getElementById("import_htaccess");
        var btn_1 = document.querySelector("#hide_submit");
        var btn_2 = document.querySelector("#import_htaccess");
        if (btn_1 && btn_2) {
//console.log(btn);
            btn_1.style.display ='none';
            btn_2.style.display ='block';
        }
        if (button) {
            button.value = what;
        }
        textarea.focus();
    }

  var elem = document.getElementById("input_reset");
console.log(elem);
  elem.addEventListener("click", ResetCheck);

    function ResetCheck () {
        var button = document.getElementById("import_htaccess");
        var btn_1 = document.querySelector("#hide_submit");
        var btn_2 = document.querySelector("#import_htaccess");
        if (btn_1 && btn_2) {
//console.log(btn);
            btn_1.style.display ='block';
            btn_2.style.display ='none';
        }

    }