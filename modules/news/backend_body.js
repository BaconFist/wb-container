domReady(function() {

//  $.insert( +"themes/default/FancyBox/jquery.fancybox-1.3.4.css");
//  $.insert( +"themes/default/FancyBox/jquery.fancybox-1.3.4.js");

//console.info('News ==='+typeof News);

/*-------------------------------------------------------------------------------------------------*/
    if (typeof News ==="object"){
        var NW_MODULE_URL = News.AddonUrl+'/';
        var NW_ICONS = News.ThemeUrl + 'img';
        var NW_AJAX_PLUGINS =  News.AddonUrl + 'ajax';  // this var could change in the future
        var LANGUAGE = (LANGUAGE ? LANGUAGE : 'EN'); // set var LANGUAGE to EN if LANGUAGE not set before
//console.log(NW_ICONS);
/*
console.info(News);
console.info(NW_AJAX_PLUGINS);
DB_COLUMN: 'post_id',
*/
            $.insert(NW_AJAX_PLUGINS +"/ajaxActiveStatus.js");
            // AjaxHelper change item active status
            $("td.toggle_active_post").ajaxActiveStatus({
                    MODULE : News.AddonUrl,
                    PAGE_ID : News.iPageId,
                    SECTION_ID : News.iSectionId,
                    DB_RECORD_TABLE: '',
                    DB_COLUMN: 'post_id',
                    sIdKey: ''
            });
            $("td.toggle_active_group").ajaxActiveStatus({
                    MODULE : News.AddonUrl,
                    PAGE_ID : News.iPageId,
                    SECTION_ID : News.iSectionId,
                    DB_RECORD_TABLE: '',
                    DB_COLUMN: 'group_id',
                    sIdKey: ''
            });
    };
//console.info(News.AddonUrl + 'ajax' +"/ajaxActiveStatus.js");

        function _tableSelect(id, out) {
            var input,select,filter,table,tr,td,i;
            input = document.querySelector("#"+id);
            if (input){
                filter = input.value.toUpperCase();
                table = document.querySelector("#"+out);
                if (table){
                    tr = table.getElementsByTagName("tr");
                    for (i = 0; i < tr.length; i++) {
                        td = tr[i].getElementsByTagName("td")[0];
                        if (td) {
                            txtValue = td.textContent || td.innerText;
                            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                                tr[i].style.display = "";
                            } else {
                                tr[i].style.display = "none";
                            }
                        }
                    }
                }
            }
        }

        function dsgvoSelect() {
            var input, filter,sec_id, select, opt, txt, i, txtValue;
            input = document.getElementById("dsgvoInput");
            if (input){
                filter = input.value.toUpperCase();
                select = document.getElementById("dsgvo");
                if (select){
                    opt = select.getElementsByTagName("option");
                    for (i = 0; i < opt.length; i++) {
                        txtValue = opt[i].textContent || opt[i].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            opt[i].style.display = "";
                        } else {
                            opt[i].style.display = "none";
                        }
                    }
                }
            }
        }

    function selectLayout() {
// prevent from deleting
        var aDefaultLayout = [null,'default_layout','div_layout','div_new_layout'];
        var deleteLayout = document.getElementById("delete_layout");
        if (deleteLayout){
            var selectedLayout = this.options[this.selectedIndex].value;
            var search = aDefaultLayout[selectedLayout];
//console.log(search);
//console.log(aDefaultLayout.indexOf(search));
            if (aDefaultLayout.indexOf(search) > -1){
                deleteLayout.classList.remove("w3-blue-wb","w3-hover-red");
                deleteLayout.setAttribute("disabled", "disabled");
            } else{
                deleteLayout.removeAttribute("disabled");
                deleteLayout.classList.add("w3-blue-wb","w3-hover-red");
            }
        }
    }

/* ------------------------------ table with fix header and scrollbar ------------------------------------- */
        function tableSelect() {
            var input,filter,sec_id,select,tr,txt,i,txtValue;
            input = document.querySelector("#tableInput");
            if (input){
                filter = input.value.toUpperCase();
                sec_id = input.dataset.sec;
//console.log(sec_id);
                select = document.querySelector("#postResult_"+sec_id);
                if (select){
                    tr = select.getElementsByTagName("tr");
                    for (i = 0; i < tr.length; i++) {
                        txtValue = tr[i].textContent || tr[i].innerText;
//console.log(tr[i]);
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            }
        }

        function groupSelect() {
            var input, filter,sec_id, select, opt, tr, txt, i, txtValue;
            input = document.querySelector("#groupInput");
            if (input){
                filter = input.value.toUpperCase();
                sec_id = input.dataset.sec;
//console.log(input.dataset.sec);
                select = document.querySelector("#groupResult_"+sec_id);
                if (select){
                    tr = select.getElementsByTagName("tr");
                    for (i = 0; i < tr.length; i++) {
                        txtValue = tr[i].textContent || tr[i].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            }
        }
    document.addEventListener('loaded', function(e) {
    });
// click backlink urls
    var btn = document.querySelectorAll("input.close");
//console.log(btn);
    if (btn){
        for (i = 0; i < btn.length; i++){
            if (btn[i]){
              btn[i].addEventListener("click", function(el) {
                  window.location = el.target.dataset.backlink;
//              url = el.target.dataset.backlink;
              });
            }
        }
    }
// search routines
    let posts = document.querySelector("input#tableInput");
    if (posts){
        posts.onkeyup = (function() {tableSelect()});
    }
    let groups = document.querySelector("input#groupInput");
    if (groups){
        groups.onkeyup = (function() {groupSelect()});
    }
    let dsgvo = document.querySelector("input#dsgvoInput");
    if (dsgvo){
       dsgvo.onkeyup = (function() {dsgvoSelect()});
    }

    let elm   = document.querySelector("input[type=file]");
    let inp   = document.querySelector("#image-select");
    let label = document.querySelector("#uploadText");
    if (elm) {
        elm.addEventListener("change", function(el) {
          fileselect = [];
          if (inp){
            fileselect = inp.files;
//console.log(label);
            label.innerText = fileselect[0].name;
          }
        });
    }
//
    let photo = document.getElementById('photos');
    if (photo){
      photo.addEventListener('change', function (event) {
        let files = event.target.files; // FileList object
        for (let i = 0, f; f = files[i]; i++) {
            if (!f.type.match('image.*')) {
                continue;
            }
            let reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    let span = document.createElement('span');
                    span.innerHTML = ['<img class="thumb" src="', e.target.result, '" title="', escape(theFile.name), '"/>'].join('');
                    document.querySelector ('.filelist').insertBefore(span, null);
                };
            })(f);
            reader.readAsDataURL(f);
        }
        document.querySelector ('.filelist').innerHTML = '';
      });
    }
    let lay = document.getElementById("layout_id");
    if (lay){
      lay.addEventListener('change', selectLayout, false);
    }

    let deleteLayout = document.getElementById("delete_layout");
    if (deleteLayout){
      if (deleteLayout.disabled) {
          deleteLayout.classList.remove("w3-blue-wb","w3-hover-red");
      } else {
          deleteLayout.classList.add("w3-blue-wb","w3-hover-green");
      }
    }

    let cform = document.querySelectorAll("button[id^=cform");
//console.log(cform);
      if (cform){
        for (var i=0; i < cform.length; i++){
          cform[i].addEventListener("click",function(ev) {
          confirm_form(this);
//console.log(this);
          });
        }// for
      }

    let pform = document.querySelectorAll("button[id^=pform");
      if (pform){
        for (var i=0; i < pform.length; i++){
          pform[i].addEventListener("click",function(ev) {
          confirm_form(this);
//console.log(this);
          });
        }// for
      }

    let gform = document.querySelectorAll("button[id^=gform");
      if (gform){
        for (var i=0; i < gform.length; i++){
          gform[i].addEventListener("click",function(ev) {
          confirm_form(this);
//console.log(this);
          });
        }// for
      }


});

