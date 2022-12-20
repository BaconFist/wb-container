// https://werner-zenk.de/scripte/bilder-in-den-browser-laden.php
 "use strict";

  // Eventhandler setzen
  window.addEventListener("load", function () {
   document.getElementById("datei").addEventListener("change", waehleDateien);
   //document.getElementById("senden").addEventListener("click", bilderHochladen);
  });

  function waehleDateien(event) {
   // Alte Auswahl löschen
   document.getElementById("ausgabe").innerHTML = "";
   document.getElementById("status").innerHTML = "";
   // Eine Schleife um die Bilder auszulesen
   for (var i = 0; i < event.target.files.length; i++) {
    if (event.target.files[i]) {
     // Der FileReader übernimmt das Laden der Datei
     var leser = new FileReader();
      // Eventhandler setzen
      leser.addEventListener("load", bildGeladen);
      // Ergebnis als data-URL
      leser.readAsDataURL(event.target.files[i]);
     }
    }
   }

  // Nach dem laden Bild anzeigen
  function bildGeladen(event) {
   var img = document.createElement("img");
   img.setAttribute("class", "bild");
   img.setAttribute("src", event.target.result);
   document.getElementById("ausgabe").appendChild(img);
  }

  // Bilder hochladen
  function bilderHochladen() {
  var xhr = new XMLHttpRequest();
   xhr.open("POST", document.URL);
   xhr.send(new FormData(document.getElementById("Form")));
   xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && xhr.status == 200) {
     document.getElementById("status").innerHTML = xhr.responseText;
    }
   }
  }

