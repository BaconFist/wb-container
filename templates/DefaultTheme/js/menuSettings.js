
    function searchSelect() {
        var input, filter, select, opt, txt, i, txtValue;
        input = document.getElementById("dsgvoInput");
        filter = input.value.toUpperCase();
        select = document.getElementById("dsgvo");
        opt = select.getElementsByTagName("option");
        for (i = 0; i < opt.length; i++) {
            txtValue = opt[i].textContent || opt[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                opt[i].style.display = "";
            } else {
                opt[i].style.display = "none";
            }
        }
    }

/* --------------------------------------------------------------------------------- */
    function change_os(type) {
        if(type === 'linux') {
            document.getElementById('file_perms_box1').style.display = 'block';
            document.getElementById('file_perms_box2').style.display = 'block';
            document.getElementById('file_perms_box3').style.display = 'block';
        } else if(type === 'windows') {
            document.getElementById('file_perms_box1').style.display = 'none';
            document.getElementById('file_perms_box2').style.display = 'none';
            document.getElementById('file_perms_box3').style.display = 'none';
        }
    }

    function change_wbmailer(type) {
        if (type === "smtp") {
            document.getElementById('row_wbmailer_smtp_settings').style.display = '';
            document.getElementById('row_wbmailer_smtp_debug').style.display = '';
            document.getElementById('row_wbmailer_smtp_host').style.display = '';
            document.getElementById('row_wbmailer_smtp_port').style.display = '';
            document.getElementById('row_wbmailer_smtp_secure').style.display = '';
            document.getElementById('row_wbmailer_smtp_auth_mode').style.display = '';
            document.getElementById('row_wbmailer_smtp_username').style.display = '';
            document.getElementById('row_wbmailer_smtp_password').style.display = '';
            let smtpAuth = document.getElementById("wbmailer_smtp_auth");
            if ( smtpAuth ){
                 toggle_wbmailer_auth(smtpAuth);
            }
        } else if ((type === "phpmail") || (type === "sendmail")) {
            document.getElementById('row_wbmailer_smtp_settings').style.display = '';
            document.getElementById('row_wbmailer_smtp_debug').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_host').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_port').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_secure').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_auth_mode').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_username').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_password').style.display = 'none';
        }
    }

/*  */
    function toggle_wbmailer_auth( elm ) {
        if ( elm.checked === true ) {
//console.log(elm.checked);
            document.getElementById('row_wbmailer_smtp_username').style.display = '';
            document.getElementById('row_wbmailer_smtp_password').style.display = '';
            //elm.checked = false;
        }
        else  {
            document.getElementById('row_wbmailer_smtp_username').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_password').style.display = 'none';
            //elm.checked = true;
        }
    }

domReady(function() {

    let phpmail = document.getElementById("wbmailer_routine_phpmail");
    if ( phpmail ){
        phpmail.addEventListener("click", function() {
//console.log(phpmail.value);
            change_wbmailer(phpmail.value);
        }, false);
    }

    let smtp = document.getElementById("wbmailer_routine_smtp");
    let smtp_Auth = document.getElementById("wbmailer_smtp_auth");
    if ( smtp ){
        smtp.addEventListener("click", function() {
            change_wbmailer(smtp.value);
        }, false);
    }

    let smtpAuth = document.getElementById("wbmailer_smtp_auth");
    if ( smtpAuth ){
        smtpAuth.addEventListener("click", function() {
//console.log(smtpAuth.checked);
            toggle_wbmailer_auth(smtpAuth);
        }, false);
    }

    let sel = document.querySelector("#wbmailer_smtp_secure");
    if (sel) {
        sel.addEventListener ("change", function () {
            let smtpPort = document.querySelector("#wbmailer_smtp_port");
//console.log(sel.value);
            switch (sel.value) {
                  case 'TLS' :
                     smtpPort.value = '25';
                     break;
                  case 'SSL' :
                     smtpPort.value = '465';
                     break;
                  default:
                     smtpPort.value = '587';
               }
//console.log(smtpPort.value);
        },false);
   }

/* --------------------------------------------------------------------------------- */

    const slider   = document.querySelector("#slider");
    const output   = document.querySelector("output");
    document.addEventListener('DOMContentLoaded', function() {
      output.value = slider.value;
    });
    if (slider){
        slider.addEventListener ("input", function (event) {
           output.value = this.value;
        });
    }

// Get the modal
    let modal = document.querySelector("#mailer-settings");
    // When the user clicks anywhere outside of the modal, close it
//console.log(modal)
     window.onclick = function(event) {
//console.log(event.target)
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }

});
