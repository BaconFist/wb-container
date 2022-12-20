<script>

  let errorModal = document.getElementById('delete-error-log');
      errorModal.addEventListener('click', function() {
//          modalFrame.style.display = "block";
        var JsPanelCss = WB_URL+"/include/plugins/jspanel/4.1.2/dist/jspanel.css";
        if (typeof LoadOnFly!=='function' || typeof LoadOnFly==='undefined' || !LoadOnFly){
            $.insert(JsPanelCss);
//            $.insert(JsPanelJs);
        } else {
            LoadOnFly('head', JsPanelCss);
//            LoadOnFly('head', JsPanelJs);
        }

        var ftb = [
          '<div class="error-modal-footer" style="background-color:#4682b4;">',
            '<button id="delete_php_error-log" class="btn btn-gradient" >Delete php_error.log<\/button>',
            '<button id="closeErrorBox" class="btn btn-gradient w3-hover-red" type="button" data-dismiss="modal" >Close<\/button>',
          '<\/div>'
        ];
        var errorConfig = {
            theme: 'steelblue', // #215a7a wbheaderblue
            id: 'errorpanel',
            contentSize: {
                  width: function() {
                      return window.innerWidth*0.8;
                  },
                  height: function() {
                      return window.innerHeight*0.85;
                  }
            },
            contentOverflow: 'scroll hidden',
            closeOnEscape: true,
            headerLogo:   "{THEME_URL}/images/apple-touch-icon.png",
            headerTitle:  'WebsiteBaker Error log',
            position:     'center-top 0 15 down',
            padding:      '20px',
            border:       '1px solid',
            header:       true,
            panelSize: {
                width: function() {
                    return window.innerWidth/2;
                },
                height: function() {
                    return window.innerHeight/1.5;
                }
            },
            footerToolbar: ftb,
            callback: function (panel) {
                // handlers for the toolbar items like:
                jsPanel.pointerup.forEach(function (evt) {
                    panel.footer.querySelector('#delete_php_error-log').addEventListener(evt, function () {
                        panel.content.innerHTML = 'You clicked the delete_php_error-log button!';
                    });
                    panel.footer.querySelector('#closeErrorBox').addEventListener(evt, function () {
                        panel.close();
                    });
                });
            },
            content:     '<iframe style="width:200%;height:100%;" src="{HELPER_URL}/LoadErrorlog.php" allowfullscreen></iframe>',
        }; //  end hintConfig
      jsPanel.create({
          config: errorConfig
      });

}); // end delete-error-log

</script>
