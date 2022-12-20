function toggleZip() {
    let check = document.getElementById("file2");
    if (check.style.visibility == "visible") {
        for (i=2; i<=10; i++) {
            document.getElementById("file" + i).style.visibility = "hidden";
        }
        document.getElementById("delzip").style.display = "inline";
    } else {
        for (i=2; i<=10; i++) {
            document.getElementById("file" + i).style.visibility = "visible";
        }
        document.getElementById("delzip").style.display = "none";
    }
}

document.addEventListener("DOMContentLoaded", function(event) {
//console.log();
    let delzip = document.getElementById("unzip");
    delzip.addEventListener('click', toggleZip,false);

    let uploadForm = document.querySelector("form#upload");
    let createForm = document.querySelector("form#create");
    let main = window.location.pathname;
    let matches = document.querySelectorAll("iframe#browse"); // set_browser
//console.log(matches[0].dataset.src);
    uploadForm.addEventListener('change',
    function (evt){
      optionUpload = uploadForm.upload_target.value; //.substr(6,100)
      optionCreate = createForm.create_target.value = optionUpload;
      window.browse.location.href='browse.php?dir='+optionUpload;
      matches[0].dataset.src = optionUpload;
      matches[0].src = "browse.php?dir="+optionUpload;
      urlFrame = window.location.protocol +'//'+ window.location.host + window.location.pathname+"?dir="+optionUpload;
      urlMain  = window.location.protocol +'//'+ window.location.host + window.frames.top.location.pathname + "?dir="+optionUpload;
//console.log(url);
//      window.location.href = url;
      evt.preventDefault();

//console.log(matches[0].dataset.src);

//console.log("toggle_zip 33 "+optionUpload);
//console.log(optionCreate);

    });

    createForm.addEventListener('change',
    function (evt){
      optionCreate = createForm.create_target.value; //.substr(6,100)
      optionUpload = uploadForm.upload_target.value = optionCreate;
      window.browse.location.href='browse.php?dir='+optionCreate;
      matches[0].dataset.src = optionUpload;
      matches[0].src = "browse.php?dir="+optionUpload;
      urlFrame = window.location.protocol +'//'+ window.location.host + window.location.pathname+"?dir="+optionUpload;
      urlMain  = window.location.protocol +'//'+ window.location.host + window.frames.top.location.pathname + "?dir="+optionUpload;
//      window.location.href = url;
      evt.preventDefault();

//      optionCreate = optionUpload;
//console.log(browse);
//console.log(optionUpload);
//console.log(optionCreate);

    });
//console.log("DOM fully loaded and parsed");
  });