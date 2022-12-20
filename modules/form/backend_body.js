domReady(function() {

// Get the modal Get the button that opens the modal
    var btn = document.getElementsByClassName("field_delete");
    var sid, modalFrm, msg;
    for (var i = 0; i < btn.length; ++i) {
        btn[i].onclick = function(ev) {
                sid = parseInt(ev.target.id);
                modalFrm = document.getElementById('FrmModal'+sid);
                msg = document.getElementById("message"+sid);
                modalFrm.style.display = "block";
                content = msg.innerHTML,
//console.log(content);
                msg.innerHTML = content+'('+sid+')';
        };
    }

// Get the <span> element that closes the modal
    var span = document.getElementsByClassName("w3-close");
// When the user clicks on <span> (x), close the modal
    for (var i = 0; i < span.length; ++i) {
        span[i].onclick = function() {
            if (typeof modalFrm === 'object'){
                modalFrm.style.display = "none";
        }};
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(e) {
    //console.log(event.target);
        if (typeof modalFrm !== 'undefined') {
            if (e.target === modalFrm) {
               modalFrm.style.display = "none";
            }
        }
    }


    function selectFormLayout(ev) {

// prevent from deleting
//        var aDefaultLayout = [null,'Default_Table_Layout.xml','Extended_Table_Layout.xml','Modern_Table_Layout.xml','Simple-DIV.xml','Simple-DIV_Placeholder'];
        var deleteLayout = document.getElementById("delete_layout");
        if (deleteLayout){
//console.log(aDefaultLayout);
            var selectedLayout = this.options[this.selectedIndex].value.split('.').slice(0, -1).join('.');
            var search = ((selectedLayout) ? selectedLayout : null);
//console.log(aDefaultLayout.indexOf(search));
            if (aDefaultLayout.indexOf(search) > -1){
//console.log("Layout ::"+search);
                deleteLayout.classList.remove("w3-blue-wb","w3-hover-red", "w3-hover-green");
                deleteLayout.setAttribute("disabled", "disabled");
           } else{
                deleteLayout.removeAttribute("disabled");
                deleteLayout.classList.add("w3-blue-wb","w3-hover-red");
            }
        }
    }


    document.addEventListener('keyup', function(e) {
        if (e.keyCode == 27) {
           modalFrm.style.display = "none";
        }
    });

        auth = document.getElementById("captcha_auth");
        info = document.getElementById("captcha-info");
        on   = document.getElementById("use_captcha_true");
        if (on){
          on.addEventListener('click', function(e) {
//console.log(typeof on);
            auth.style.display = "";
            info.style.display = "";
            });
//        off   = document.getElementById("use_captcha_false");
            document.getElementById("use_captcha_false").addEventListener('click', function(e) {
//console.log(typeof off);
            auth.style.display = "none";
            info.style.display = "none";
            });
        }

    document.addEventListener('loaded', function(e) {
    });
        let lay = document.getElementById("layout");
        if (lay){
          lay.addEventListener('change', selectFormLayout, false);
        }

    document.addEventListener('loaded', function(e) {
    });
        let lay_export = document.getElementById("xml_file");
        if (lay_export){
          lay_export.addEventListener('change', selectFormLayout, false);
        }

    let deleteLayout = document.getElementById("delete_layout");
    if (deleteLayout){
      if (deleteLayout.disabled) {
          deleteLayout.classList.remove("w3-blue-wb","w3-hover-red");
      } else {
          deleteLayout.classList.add("w3-blue-wb","w3-hover-green");
      }
    }

});

