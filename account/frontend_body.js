/*
    document.addEventListener("DOMContentLoaded", function () {
        let fieldsreset = document.querySelector('.start_reset');
        if (fieldsreset){
            fieldsreset.addEventListener (
              "click",
              function (evt) {
                  let url = window.location.protocol +'//'+ window.location.host + window.location.pathname;
                  window.location.href = url;
                  evt.preventDefault();
            });
        }

    });
    document.addEventListener("DOMContentLoaded", function () {
        elm = document.getElementsByTagName('form');
//    console.info(elm);
        for (i=0; elm[i]; i++) {
          if ( (elm[i].className.indexOf('autocomplete') == -1) ) {
              elm[i].setAttribute('autocomplete', 'off');
          }
          if ( (elm[i].className.indexOf('accept-charset') == -1) ) {
              elm[i].setAttribute('accept-charset', 'utf-8');
          }
        }
        let fieldsreset = document.querySelector('.start_reset');
        if (fieldsreset!==null){
            fieldsreset.addEventListener (
                "click",
                function (evt) {
                    let url = window.location.protocol +'//'+ window.location.host + window.location.pathname;
                    window.location.href = url;
                    evt.preventDefault();
            });
        }
    });
*/
/*
    $(".toggle-password").click(function() {
        $(this).toggleClass("fa-eye fa-eye-slash");
        var input = $($(this).attr("toggle"));
        if (input.attr("type") == "password") {
          input.attr("type", "text");
        } else {
          input.attr("type", "password");
        }
    });
*/

    let toggle = document.querySelectorAll("span.toggle-password");
    if (toggle){
        for (var i = 0; i < toggle.length; i++) {
            if (typeof toggle[i] === "object"){
                toggle[i].addEventListener("click", function(){
                    var attr  = this.getAttribute("toggle");
                    var input = document.querySelector("input"+attr)
                    if (input.getAttribute("type") === "password") {
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                        input.setAttribute("type", "text");
                    } else {
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                        input.setAttribute("type", "password");
                    }
                });
            }// toggle[i] is object
        }// for
    }

    document.addEventListener("DOMContentLoaded", function () {
        let fieldsreset = document.querySelector('.startreset');
        if (fieldsreset) {
            fieldsreset.addEventListener (
                "click",
                function (evt) {
                    let url = window.location.protocol +'//'+ window.location.host + window.location.pathname;
                    //
                    window.location.href = url;
                    evt.preventDefault();
            });
        }
    });
