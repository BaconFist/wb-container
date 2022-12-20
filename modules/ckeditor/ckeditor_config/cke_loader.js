
        var ScriptPath = (function() {
          var scripts = document.getElementsByTagName('script'),
              script = scripts[scripts.length - 1];
          var path = '';
          if (script.getAttribute.length !== undefined) {
            path = script.getAttribute('src');
          } else {
            path = script.getAttribute('src', 2);
          }
          if (path) {
            path = path.substr(0,path.lastIndexOf('/')+1);
          }
          return path;
        }());

        var editor = [], html = '';
        function createEditor(elm) {
            if ( editor[elm] ){return false;}
            html = document.getElementById( 'website_'+elm ).value;
            // Create a new editor instance inside the  element,
            // setting its value to html.
//console.log(ScriptPath);
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
