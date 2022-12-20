function toggle_viewers() {
    if(document.querySelector('form#add').visibility.value == 'private') {
        document.getElementById('private_viewers').style.display = 'block';
        document.getElementById('registered_viewers').style.display = 'none';
    } else if(document.querySelector('form#add').visibility.value == 'registered') {
        document.getElementById('private_viewers').style.display = 'none';
        document.getElementById('registered_viewers').style.display = 'block';
    } else {
        document.getElementById('private_viewers').style.display = 'none';
        document.getElementById('registered_viewers').style.display = 'none';
    }
}

function toggle_visibility(id){
    var toggle = document.getElementById(id);
    if(toggle.style.display == "block") {
        toggle.style.display = "none";
        writeSessionCookie (id, "0");//Addition for remembering expanded state of pages
    } else {
        toggle.style.display = "block";
        writeSessionCookie (id, "1");//Addition for remembering expanded state of pages
    }
}
var plus = new Image;
plus.src = "<?php echo THEME_URL; ?>/images/plus_16.png";
var minus = new Image;
minus.src = "<?php echo THEME_URL; ?>/images/minus_16.png";
function toggle_plus_minus(id) {
    var img_src = document.images['plus_minus_' + id].src;
    if(img_src == plus.src) {
        document.images['plus_minus_' + id].src = minus.src;
    } else {
        document.images['plus_minus_' + id].src = plus.src;
    }
}