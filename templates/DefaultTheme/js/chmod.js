
domReady(function() {

/*
Jeroen's Chmod Calculator- By Jeroen Vermeulen of Alphamega Hosting <jeroen@alphamegahosting.com>
Visit http://www.javascriptkit.com for this script and more
This notice must stay intact
modified by Dietmar Wöllbrink for WebsiteBaker CMS
*/

    function octalFilechange(  ) {
//console.error(document.Settings.chmodFile);
        var Settings = document.querySelector('form#Settings');
        if (Settings.chmodFile==='undefined'){return false;}
        var val = Settings.chmodFile.value;
        var userbin = parseInt(val.charAt(1)).toString(2);
        while (userbin.length<3) { userbin="0"+userbin; };
//console.info( userbin );
        var groupbin = parseInt(val.charAt(2)).toString(2);
        while (groupbin.length<3) { groupbin="0"+groupbin; };
//console.info( groupbin );
        var otherbin = parseInt(val.charAt(3)).toString(2);
        while (otherbin.length<3) { otherbin="0"+otherbin; };

        Settings.file_u_r.checked = parseInt(userbin.charAt(0));
        Settings.file_u_w.checked = parseInt(userbin.charAt(1));
        Settings.file_u_e.checked = parseInt(userbin.charAt(2));

        Settings.file_g_r.checked = parseInt(groupbin.charAt(0));
        Settings.file_g_w.checked = parseInt(groupbin.charAt(1));
        Settings.file_g_e.checked = parseInt(groupbin.charAt(2));

        Settings.file_o_r.checked = parseInt(otherbin.charAt(0));
        Settings.file_o_w.checked = parseInt(otherbin.charAt(1));
        Settings.file_o_e.checked = parseInt(otherbin.charAt(2));
//console.info('file : ' + userbin+' '+groupbin+' '+otherbin );
        calcFilechmod(1);
    }

    function octalDirchange(  ) {
        var Settings = document.querySelector('form#Settings');
        if (Settings.chmodDir==='undefined'){return false;}
        var val = Settings.chmodDir.value;
        var userbin = parseInt(val.charAt(1)).toString(2);
        while (userbin.length<3) { userbin="0"+userbin; };
//console.info( userbin );
        var groupbin = parseInt(val.charAt(2)).toString(2);
        while (groupbin.length<3) { groupbin="0"+groupbin; };
//console.info( groupbin );
        var otherbin = parseInt(val.charAt(3)).toString(2);
        while (otherbin.length<3) { otherbin="0"+otherbin; };

        Settings.dir_u_r.checked = parseInt(userbin.charAt(0));
        Settings.dir_u_w.checked = parseInt(userbin.charAt(1));
        Settings.dir_u_e.checked = parseInt(userbin.charAt(2));

        Settings.dir_g_r.checked = parseInt(groupbin.charAt(0));
        Settings.dir_g_w.checked = parseInt(groupbin.charAt(1));
        Settings.dir_g_e.checked = parseInt(groupbin.charAt(2));

        Settings.dir_o_r.checked = parseInt(otherbin.charAt(0));
        Settings.dir_o_w.checked = parseInt(otherbin.charAt(1));
        Settings.dir_o_e.checked = parseInt(otherbin.charAt(2));
//console.info('dir  : ' + userbin+' '+groupbin+' '+otherbin );
        calcDirchmod(1);
    }

    function calcFilechmod(noTotals) {
      var users  = new Array("file_u_", "file_g_", "file_o_");
      calc_chmod( users, noTotals, 'chmodFile' );
    }
    function calcDirchmod(noTotals) {
      var users  = new Array("dir_u_", "dir_g_", "dir_o_");
      calc_chmod( users, noTotals, 'chmodDir' );
    }

    function calc_chmod( users, noTotals, Mode ) {

      var totals = new Array("","","");
      var syms   = new Array("","","");
        for (var i=0; i<users.length; i++) {
          var user=users[i];
            var field1 = user + "r";
            var field2 = user + "w";
            var field4 = user + "e";
            //var total = "t_" + user;
            var symbolic = "sym_" + user;
            var number = 0;
            var sym_string = "";
            var Settings = document.querySelector('form#Settings');

            if (Settings[field1].checked == true) { number += 4; }
            if (Settings[field2].checked == true) { number += 2; }
            if (Settings[field4].checked == true) { number += 1; }

            if (Settings[field1].checked == true) {
                sym_string += "r";
            } else {
                sym_string += "-";
            }
            if (Settings[field2].checked == true) {
                sym_string += "w";
            } else {
                sym_string += "-";
            }
            if (Settings[field4].checked == true) {
                sym_string += "x";
            } else {
                sym_string += "-";
            }
            //if (number == 0) { number = ""; }
          //Settings[total].value =
            totals[i] = totals[i]+number;
            syms[i] =  syms[i]+sym_string;
        }; //  end for

            var JsChmodCss = THEME_URL+"/css/chmod.css";
            if (typeof LoadOnFly ==='undefined'){
              $.insert(JsChmodCss);
            } else {
              LoadOnFly('head', JsChmodCss);
            }

        var Settings = document.querySelector('form#Settings');
        if ( Mode === 'chmodDir' ) {
          if (!noTotals) {Settings.chmodDir.value = totals[0] + totals[1] + totals[2];}
          Settings.sym_chmodDir.value = "" + syms[0] + syms[1] + syms[2];
//console.info(totals[0] + totals[1] + totals[2]);
//console.info("" + syms[0] + syms[1] + syms[2]);
        }
        if ( Mode === 'chmodFile' ) {
          if (!noTotals) {Settings.chmodFile.value = totals[0] + totals[1] + totals[2];}
          Settings.sym_chmodFile.value = "" + syms[0] + syms[1] + syms[2];
//console.info("" + syms[0] + syms[1] + syms[2]);
       }
    }

    function addEvent ( elm ) {
        if ( elm ){
//console.info( elm );
            elm.addEventListener("click", function() {
                calcFilechmod();
                calcDirchmod();
            }, false);
        }
    }

    window.onload = function () {
        var types  = new Array( "file_u_", "file_g_", "file_o_", "dir_u_", "dir_g_", "dir_o_" ),
            action = new Array( "r", "w", "e" );
        for (i=0; i < types.length ; i++ ) {
            addEvent( document.getElementById( types[i]+'r' ) );
            addEvent( document.getElementById( types[i]+'w' ) );
            addEvent( document.getElementById( types[i]+'e' ) );
        }
        octalFilechange();
        octalDirchange();
    }

});
