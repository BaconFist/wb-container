
    function change_wbmailer(type) {
        if (type === "smtp") {
            document.getElementById('row_wbmailer_smtp_settings').style.display = '';
            document.getElementById('row_wbmailer_smtp_debug').style.display = '';
            document.getElementById('row_wbmailer_smtp_debug').style.display = '';
            document.getElementById('row_wbmailer_smtp_host').style.display = '';
            document.getElementById('row_wbmailer_smtp_port').style.display = '';
            document.getElementById('row_wbmailer_smtp_secure').style.display = '';
            document.getElementById('row_wbmailer_smtp_auth_mode').style.display = '';
            document.getElementById('row_wbmailer_smtp_username').style.display = '';
            document.getElementById('row_wbmailer_smtp_password').style.display = '';
        } else if((type === "phpmail") || (type === "sendmail")) {
            document.getElementById('row_wbmailer_smtp_settings').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_debug').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_host').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_port').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_secure').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_auth_mode').style.display = '';
            document.getElementById('row_wbmailer_smtp_username').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_password').style.display = 'none';
        }
    }

    function toggle_wbmailer_auth( elm ) {
        if ( elm.checked === true ) {
            document.getElementById('row_wbmailer_smtp_username').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_password').style.display = 'none';
            elm.checked = false;
        }
        else  {
            elm.checked = true;
            document.getElementById('row_wbmailer_smtp_username').style.display = 'block';
            document.getElementById('row_wbmailer_smtp_password').style.display = 'block';
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
    if ( smtp ){
        smtp.addEventListener("click", function() {
//console.log(smtp.value);
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


/**/
    var sendm = document.getElementById("wbmailer_routine_sendmail");
    if ( sendm ){
        sendm.addEventListener("click", function() {
            change_wbmailer('sendmail');
        }, false);
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

/*
    function toggle_dsgvo( elm ) {
//console.info(elm);
            if ( elm.checked == true ) {
                document.getElementById('dsgvo_status').style.display = 'block';
            }
            else  {
                document.getElementById('dsgvo_status').style.display = 'none';
            }
    }
    var dsgvo = document.getElementById("data_protection");
    if (dsgvo){
        dsgvo.addEventListener("change", function() {
            toggle_dsgvo(dsgvo);
        }, false);
    }
*/
/*
    elm.checked = false;
    myLabels = document.querySelectorAll('.lbl-toggle');
    Array.from(myLabels).forEach(label => {
        label.addEventListener('keydown', e => {
          // 32 === spacebar
          // 13 === enter
          if (e.which === 32 || e.which === 13) {
            e.preventDefault();
            label.click();
          };
        });
    });
*/
});
