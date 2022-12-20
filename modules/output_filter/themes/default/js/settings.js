/**/
function toggle_wbmailer_auth( elm ) {
        if ( elm.checked == true ) {
            elm.checked = false;
            document.getElementById('row_wbmailer_smtp_username').style.display = 'none';
            document.getElementById('row_wbmailer_smtp_password').style.display = 'none';
        }
        else  {
            elm.checked = true;
            document.getElementById('row_wbmailer_smtp_username').style.display = 'block';
            document.getElementById('row_wbmailer_smtp_password').style.display = 'block';
        }
console.info(elm);
}

  domReady(function() {

    var smtpPort = document.getElementById("wbmailer_smtp_port");
    if ( smtpPort ){
        smtpPort.addEventListener("click", function() {
            toggle_wbmailer_secure( smtpPort );
        }, false);
    }

});
