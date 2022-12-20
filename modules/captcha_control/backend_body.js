    var pics = new Array();
    var CaptchaUrl    = WB_URL+"\/include\/captcha\/";
    var CaptchaImgUrl = WB_URL+"\/include\/captcha\/captchas\/";
    pics["ttf_image"] = new Image();
    pics["ttf_image"].src = CaptchaImgUrl+"ttf_image.png";

    pics["calc_image"] = new Image();
    pics["calc_image"].src = CaptchaImgUrl+"calc_image.png";

    pics["calc_ttf_image"] = new Image();
    pics["calc_ttf_image"].src = CaptchaImgUrl+"calc_ttf_image.png";

    pics["old_image"] = new Image();
    pics["old_image"].src = CaptchaImgUrl+"old_image.png";

    pics["calc_text"] = new Image();
    pics["calc_text"].src = CaptchaImgUrl+"calc_text.png";

    pics["Securimage"] = new Image();
    pics["Securimage"].src = CaptchaImgUrl+"Securimage.png";

    pics["text"] = new Image();
    pics["text"].src = CaptchaImgUrl+"text.png";

    function load_captcha_image() {
        var captcha = document.querySelector('form#store_settings').captcha_type.value,
            example = document.getElementById('captcha_example');
        example.src = pics[captcha].src;
//console.log(captcha) ;
        toggle_ct_color();
        toggle_securimage();
        toggle_ct_text();
    }

    function toggle_ct_color() {
        if(document.querySelector('form#store_settings').captcha_type.value == 'calc_text' ) {
            let Secure = document.getElementById('Securimage');
            if (Secure) {Secure.style.display = 'none';}
//console.log(typeof Secure);
            document.getElementById('ct_color').style.display = '';
            document.getElementById('text_secure').style.display = 'none';
        } else {
            let calc_text = document.getElementById('calc_text');
            document.getElementById('ct_color').style.display = 'none';
        }
    }

    function toggle_ct_text() {
        if (document.querySelector('form#store_settings').captcha_type.value == 'text' ) {
            let Secure = document.getElementById('Securimage');
            if (Secure) {Secure.style.display = 'none';}
//console.log(typeof Secure);
            document.getElementById('ct_text').style.display = '';
            document.getElementById('ct_text_label').style.display = '';
            document.getElementById('text_secure').style.display = 'none';
            document.getElementById('ct_color').style.display = 'none';
        } else {
            document.getElementById('ct_text').style.display = 'none';
            document.getElementById('ct_text_label').style.display = 'none';

        }
    }

    function toggle_securimage() {
        if(document.querySelector('form#store_settings').captcha_type.value == 'Securimage' ) {
            let Secure = document.getElementById('Securimage');
            if (Secure) {Secure.style.display = '';}
//console.log(typeof Secure);
            document.getElementById('text_secure').style.display = '';
            document.getElementById('ct_color').style.display = 'none';
            document.getElementById('ct_text_label').style.display = 'none';
            document.getElementById('ct_text').style.display = 'none';
        } else {
            document.getElementById('text_secure').style.display = 'none';
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        var color = document.querySelectorAll('.jscolor');
        if (color) {
            for (let i=0; i<color.length; i++) {
//console.log();
                id = "#"+color[i].id;
                var input = document.querySelector(id).oninput = function (eve) {
                  output = document.querySelector(id);
                  output.style.color = "#"+color.value;
                }
//console.log(color);
                output = document.querySelector(id);
                button = document.querySelector(id);
                color[0].addEventListener('change', changeStyle,false );
                function changeStyle() {
                    output.style.color = "#"+color.value;
                }
            }
        }
        JsFile = CAPTCHA_CONTROL.AddonUrl+"/themes/default/js/jscolor.js";
        include_file(JsFile)
        load_captcha_image();

//        var refresh = document.querySelectorAll("#Securimage a");
//console.log(refresh);

    });
