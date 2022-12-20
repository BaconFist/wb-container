<!-- BEGIN show_main_block -->
<!DOCTYPE html>
<html lang="en">
<!--
 @version         $Id: warning.html.php 77 2017-03-12 02:46:29Z Luisehahne $
-->
  <head>
  <meta charset="utf-8" />
  <meta name="Referrer-Policy" content="no-referrer | same-origin"/>
  <title>Maximum Invalid Login Attemps Exceeded</title>
  <link rel="shortcut icon" href="{THEME_URL}images/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="{THEME_URL}images/apple-touch-icon.png"/>
  <link rel="stylesheet" href="{THEME_URL}css/4/w3.css" media="screen" />
  <link rel="stylesheet" href="{THEME_URL}css/fontawesome.min.css" media="screen" />

<style type="text/css"><!--
body,td,th {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 18px;
    color: #000000;
}
body {
    margin: 0px;
    background:  #7fbcef url("{THEME_URL}images/negative.png") center -800% no-repeat;
}
a:link, a:visited, a:active {
    color: #003366;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
    color: #336699;
}
h1 {
    text-align: center;
    font-size: 40px;
    margin-top: 0;
    color: #02223C;
    text-transform: uppercase;
    text-shadow: 0 1px #808d93, -1px 0 #cdd2d5, -1px 2px #808d93, -2px 1px #cdd2d5, -2px 3px #808d93, -3px 2px #cdd2d5, -3px 4px #808d93, -4px 3px #cdd2d5, -4px 5px #808d93, -5px 4px #cdd2d5, -5px 6px #808d93, -6px 5px #cdd2d5, -6px 7px #808d93, -7px 6px #cdd2d5, -7px 8px #808d93, -8px 7px #cdd2d5;
}
hr {
    height: 1px;
    color: #336699;
    background-color: #336699;
    border: 0;
}

.container {
/* Permalink - use to edit and share this gradient: https://colorzilla.com/gradient-editor/#1e5799+0,2989d8+50,207cca+51,7db9e8+100;Blue+Gloss+Default */
    background: -moz-linear-gradient(top, #1e5799 0%, #2989d8 50%, #207cca 51%, #7db9e8 100%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top, #1e5799 0%,#2989d8 50%,#207cca 51%,#7db9e8 100%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom, #1e5799 0%,#2989d8 50%,#207cca 51%,#7db9e8 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    background-repeat: no-repeat;
}
div#message {
    margin: 0 auto;
    height: 100px;
    padding: 0;
    text-align: center;
}

--></style>
  </head>
    <body>

    <div class="container">
        <div id='message'>
            <h1>Excessive Invalid Logins</h1>
            You have attempted to login too many times
            <div style="margin-top: 1.9em;">
              <form action="{LOGIN_URL}" method="post">
                  <input type="hidden" name="redirect" value="{REDIRECT_URL}" />
                  <input type="hidden" name="url" value="{REDIRECT_URL}" />
                  <input type="hidden" name="display_form" value="true" />
                 <span style="padding: 0.825em 0.525em;"><input style=" cursor: pointer;" name="{CORE_MODE}_send" type="submit" value="Kick me to the {CORE_MODE} Login" /></span>
              </form>

            </div>
            <br /><br />
        </div>
    </div>

    </body>
</html>
<!-- END show_main_block -->