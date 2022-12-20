
domReady(function() {
        var editor = [], html;
        function createEditor(elm) {
            if ( editor[elm] ){return false;}
            html = document.getElementById( 'website_'+elm ).value;
            // Create a new editor instance inside the  element,
            // setting its value to html.
            editor[elm] = CKEDITOR.replace( 'website_'+elm,
                              {
                                customConfig: WB_URL + '/modules/' +EDITOR + '/ckeditor_config/settings_config.js'
                              }
                          );
            // Update button states.
            document.getElementById( 'remove_'+elm ).style.display = 'inline';
            document.getElementById( 'create_'+elm ).style.display = 'none';
            // Show  with "Edited Content".
            document.getElementById( 'website_'+elm ).style.display = 'none';
        }

        function removeEditor(elm) {
            if ( !editor[elm] ){return false;}
            // Retrieve the editor content. In an Ajax application this data would be
            // sent to the server or used in any other way.
            // Retrieve the editor content. In an Ajax application this data would be
            // sent to the server or used in any other way.
            html = editor[elm].getData();
            // Update  with "Edited Content".
            document.getElementById( 'website_'+elm ).innerHTML = html;
            // Show  with "Edited Content".
            document.getElementById( 'website_'+elm ).style.display = 'block';
            // Update button states.
            document.getElementById( 'remove_'+elm ).style.display = 'none';
            document.getElementById( 'create_'+elm ).style.display = 'inline';
            // Destroy the editor.
            editor[elm].destroy();
            editor[elm] = null;
        }

    function openTab(evt, tabName) {
      var i, x, tablinks;
      x = document.getElementsByClassName("tabDiv");
      for (i = 0; i < x.length; i++) {
          x[i].style.display = "none";
      }
      tablinks = document.getElementsByClassName("tablink");
      for (i = 0; i < x.length; i++) {
          tablinks[i].className = tablinks[i].className.replace(" w3-green", "");
      }
      document.getElementById(cityName).style.display = "block";
      evt.currentTarget.className += " w3-green";
    }

});
