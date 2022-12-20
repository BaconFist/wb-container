    function redirect_to_page (url, timer) {
        if (timer < 0 ) {
            self.location.replace (url);
        } else {
            setTimeout('self.location.href="'+url+'"', timer);
        }
    }

  function replaceAllBackSlash(targetStr){
      var index=targetStr.indexOf("\\");
      while(index >= 0){
          targetStr=targetStr.replace("\\","");
          index=targetStr.indexOf("\\");
      }
      return targetStr;
  }

    domReady(function(){
//        var matches = document.querySelectorAll(".jcalendar");
        if( document.querySelectorAll(".jcalendar").length > 0 ) {
            var JCalendarCss = WB_URL+"/include/jscalendar/calendar-system.css";
            if (typeof LoadOnFly ==='undefined'){
              $.insert(JCalendarCss);
            } else {
              LoadOnFly('head', JCalendarCss);
            }
        }
        if( document.querySelectorAll(".jsadmin").length > 0 ) {
            var JsAdminCss = WB_URL+"/modules/jsadmin/backend.css";
            if (typeof LoadOnFly ==='undefined'){
              $.insert(JsAdminCss);
            } else {
              LoadOnFly('head', JsAdminCss);
            }
        }
        elm = document.getElementsByTagName('form');
//console.info(elm);
          for (i=0; elm[i]; i++) {
            if ((elm[i].className.indexOf('autocomplete') == -1) ) {
                elm[i].setAttribute('autocomplete', 'off');
            }
            if ((elm[i].className.indexOf('accept-charset') == -1) ) {
                elm[i].setAttribute('accept-charset', 'utf-8');
            }
          }
/* */
      function pageExpertModal(el){
          if (el){
              for (let i=0; i<el.length; i++) {
              el[i].addEventListener("click", function (ev) {
              }, { passive: false });
              }
          }
      }
/* */
      modal = document.querySelectorAll("button.page-expert");
      close = document.querySelectorAll(".btn-close ");
      if (modal){
          for (let i=0; i<modal.length; i++) {
//console.log (modal[i].dataset.modalId);
              modal[i].addEventListener("click", function (ev) {
              sid = modal[i].dataset.modalId;
              if (sid){
//console.info(sid);
                modalBox = document.getElementById(sid);
                modalBox.style.display="block";
              }
            }, { passive: false });
          } // for
/* */
      var drops = document.querySelectorAll(".url-status");
//console.log (drops);
          if (drops && (drops.length > 0)) {
              for (var i=0; i < drops.length; i++) {
                  drops[i].addEventListener ('click', function(ev){
                    let data = this.dataset.status;
                    let url = location.origin+"/"+location.pathname.replace(/^\//, '')+data;
//console.log (url);
                    window.location.replace(url);
                  });
              } //end for
          };
/* */
          var back = document.querySelectorAll(".url-close");
//console.log (back);
          if (back && (back.length > 0)) {
              for (var i=0; i < back.length; i++) {
                  back[i].addEventListener ('click', function(ev){
                    let data = this.dataset.overview;
                    let url = WB_URL+"/"+data;
//console.log (url);
                    window.location.replace(url);
                  });
              } //end for
          };
/* */
          close = document.querySelectorAll(".btn-close ");
          if (close){
              for (let i=0; i<close.length; i++) {
                close[i].addEventListener("click", function (ev) {
                ex = close[i].dataset.modalClose;
//console.info(ex);
                  if (ex) {
                    closeBox = document.getElementById(ex);
                    closeBox.style.display="none";
                  }
                }, { passive: false });
              } // for
          }
      } //modal
/* */
        let fieldsreset = document.querySelector('#start_reset');
        if (fieldsreset){
            fieldsreset.addEventListener (
                "click",
                function (evt) {
                    let url = window.location.protocol +'//'+ window.location.host + window.location.pathname;
                    window.location.href = url;
                    evt.preventDefault();
            });
        }
      var drops = document.querySelectorAll(".url-reset");
//console.log (drops);
      if (drops && (drops.length > 0)) {
          for (var i=0; i < drops.length; i++) {
              drops[i].addEventListener ('click', function(ev){
                let data = this.dataset.overview;
                let url = location.origin+"/"+location.pathname.replace(/^\//, '')+data;
//console.log (url);
                window.location.replace(url);
              });
          } //end for
      };
    }); //domReady
