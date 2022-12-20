<script>

  let infoModal = document.getElementById('custom-modal-template');
      infoModal.addEventListener('click', function() {
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
          '<div class="info-modal-footer" style="background-color:#4682b4;">',
            '<button id="closeModalBox" class="btn btn-gradient w3-hover-red" type="button" data-dismiss="modal" >Close<\/button>',
          '<\/div>'
        ];
        var infoConfig = {
            theme: 'steelblue', // #215a7a  wbheaderblue
            id: 'infopanel',
            contentSize: {
                  width: function() {
                      return window.innerWidth*0.8;
                  },
                  height: function() {
                      return window.innerHeight/1.5;
                  }
            },
            contentOverflow: 'hidden',
            closeOnEscape: true,
            headerLogo:   "{THEME_URL}/images/apple-touch-icon.png",
            headerTitle:  'WebsiteBaker System & Maintenance',
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
                    panel.footer.querySelector('#closeModalBox').addEventListener(evt, function () {
                    panel.close();
                    });
                });
            },
            content:     '<iframe style="width: 100%; height: 100%;" src="{HELPER_URL}/wb_info.php" allowfullscreen></iframe>',
        }; //  end hintConfig

      jsPanel.create({
          config: infoConfig
      });

}); // end custom-modal-template


</script>
