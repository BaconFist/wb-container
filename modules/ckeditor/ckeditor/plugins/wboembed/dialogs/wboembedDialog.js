/*
 Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
*/

CKEDITOR.dialog.add('wboembedDialog', function (editor) {
    return {
        title: editor.lang.wboembed.title,
        minWidth: 400,
        minHeight: 80,
        contents: [{
            id: 'tab-basic',
            label: 'Basic Settings',
            elements: [{
                    type: 'html',
                    html: '<p>' + editor.lang.wboembed.onlytxt + '</p>'
                },
                {
                    type: 'text',
                    id: 'url_video',
                    label: 'URL (ex: https://www.youtube.com/watch?v=EOIvnRUa3ik)',
                    validate: CKEDITOR.dialog.validate.notEmpty(editor.lang.wboembed.validatetxt)
                },
                {
                    type: 'text',
                    id: 'css_class',
                    label: editor.lang.wboembed.input_css
                },
                {
                    type: 'select',
                    id: 'resizeType',
                    label: editor.lang.wboembed.resizeType,
                    'default': 'responsive',
                    items: [
                        [editor.lang.wboembed.responsive, 'responsive'],
                        [editor.lang.wboembed.noresize, 'noresize'],
                        [editor.lang.wboembed.custom, 'custom']
                    ],
                    onChange: function (e) {
                        /* console.log(e.data.value); */
                    }
                }
            ]
        }],
        onOk: function () {
            var
                dialog = this,
                div_container = new CKEDITOR.dom.element('div'),
                css = 'oembeddedContent';
            // Set custom css class name
            if (dialog.getValueOf('tab-basic', 'css_class').length > 0) {
                css = dialog.getValueOf('tab-basic', 'css_class');
            }
            div_container.setAttribute('class', css);

            // Auto-detect if youtube, vimeo or dailymotion url
            var url = detect(dialog.getValueOf('tab-basic', 'url_video'));
            // Create iframe with specific url
            if (url.length > 1) {
                var resizetype = dialog.getValueOf('tab-basic', 'resizeType');
                if (resizetype == "custom") {
                    var iframe = new CKEDITOR.dom.element.createFromHtml('<iframe loading="lazy" src="' + url + '" allowfullscreen></iframe>');
                    div_container.append(iframe);
                    editor.insertElement(div_container);
                } else if (resizetype == 'noresize') {
                    var iframe = new CKEDITOR.dom.element.createFromHtml('<iframe loading="lazy" src="' + url + '" style=" width:560px;height:349px;" allowfullscreen></iframe>');
                    div_container.append(iframe);
                    editor.insertElement(div_container);
                } else {
                    var iframe = new CKEDITOR.dom.element.createFromHtml('<iframe loading="lazy" src="' + url + '" style="border: 0; top: 0; left: 0; width: 100%; height: 100%; position: absolute;" allowfullscreen></iframe>');
                    div_container.setAttribute("style", "left: 0; width: 100%; height: 0; position: relative; padding-bottom: 56.2493%;");
                    div_container.append(iframe);
                    editor.insertElement(div_container);
                }
            }
        }
    };
});

// Detect platform and return video ID
function detect(url) {
    var oembed_url = '';
    // full youtube url
    if (url.indexOf('youtube') > 0) {
        id = getId(url, "?v=", 3);
        if (id.indexOf('&') > 0) {
            realID = id.split('&');
            id = realID[0];
        }
        return oembed_url = 'https://www.youtube.com/embed/' + id;
    }
    // tiny youtube url
    if (url.indexOf('youtu.be') > 0) {
        id = getId(url);
        return oembed_url = 'https://www.youtube.com/embed/' + id;
    }
    // full vimeo url
    if (url.indexOf('vimeo') > 0) {
        id = getId(url);
        return oembed_url = 'https://player.vimeo.com/video/' + id + '?badge=0';
    }
    // full dailymotion url
    if (url.indexOf('dailymotion') > 0) {
        // if this is a playlist (jukebox)
        if (url.indexOf('/playlist/') > 0) {
            id = url.substring(url.lastIndexOf('/playlist/') + 10, url.indexOf("/1#video="));
            console.log(id);
            return oembed_url = 'https://www.dailymotion.com/widget/jukebox?list[]=%2Fplaylist%2F' + id + '%2F1&&autoplay=0&mute=0';
        } else {
            id = getId(url);
        }
        return oembed_url = 'https://www.dailymotion.com/embed/video/' + id;
    }
    // tiny dailymotion url
    if (url.indexOf('dai.ly') > 0) {
        id = getId(url);
        return oembed_url = 'https://www.dailymotion.com/embed/video/' + id;
    }
    return embed_url;
}

// Return video ID from URL
function getId(url, string = "/", index = 1) {
    return url.substring(url.lastIndexOf(string) + index, url.length);
}