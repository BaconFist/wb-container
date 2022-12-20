/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

// Register a templates definition set named "default".
CKEDITOR.addTemplates( 'default',
{
    // The name of sub folder which hold the shortcut preview images of the
    // templates.
    imagesPath : CKEDITOR.getUrl( CKEDITOR.plugins.getPath( 'templates' ) + '/templates/images/' ),

    // The templates definitions.
    templates :
        [
            {
                title: 'Flexbox - 3er-Element',
                image: 'flexelement.gif',
                description: 'Erstellt eine Flexbox mit drei Elementen, einem Titel, einer Beschreibung und einem FontAwesome-Icon - Anpassungen dazu im Quelltext des Editors.',
                html:
                    '<div class="columns">' +
                        '<div class="column">' +
                            '<article class="media">' +
                                '<figure class="media-left">' +
                                    '<span class="icon">' +
                                        '<i class="fab fa-css3-alt"></i>' +
                                    '</span>' +
                                '</figure>' +
                                '<div class="media-content">' +
                                    '<div class="content">' +
                                        '<h1 class="title">Titel anpassen</h1>' +
                                        '<p>Text anpassen</p>' +
                                    '</div>' +
                                '</div>' +
                            '</article>' +
                        '</div>' +
                        '<div class="column">' +
                            '<article class="media">' +
                                '<figure class="media-left">' +
                                    '<span class="icon">' +
                                        '<i class="fab fa-css3-alt"></i>' +
                                    '</span>' +
                                '</figure>' +
                                '<div class="media-content">' +
                                    '<div class="content">' +
                                        '<h1 class="title">Titel anpassen</h1>' +
                                        '<p>Text anpassen</p>' +
                                    '</div>' +
                                '</div>' +
                            '</article>' +
                        '</div>' +
                        '<div class="column">' +
                            '<article class="media">' +
                                '<figure class="media-left">' +
                                    '<span class="icon">' +
                                        '<i class="fab fa-css3-alt"></i>' +
                                    '</span>' +
                                '</figure>' +
                                '<div class="media-content">' +
                                    '<div class="content">' +
                                        '<h1 class="title">Titel anpassen</h1>' +
                                        '<p>Text anpassen</p>' +
                                    '</div>' +
                                '</div>' +
                            '</article>' +
                        '</div>' +
                    '</div>'
                },
                {
                        title: 'W3CSS-Box mit 3 Zellen',
                        image: 'flex3.gif',
                        description: 'Erstellt eine responsive Box mit drei Zellen.',
                    html:
                        '<div class="w3-container">' +
                            '<div class="w3-display-container w3-left-align w3-col l4 m6 w3-padding">' +
                                '<div class="flexheader w3-left-align w3-container w3-padding">'+
                                    '<h1>'+
                                    'Trage hier den Titel 1 ein'+
                                    '</h1>'+
                                    '<p class="textblock">'+
                                    'Trage hier deinen Text 1 ein'+
                                    '</p>'+
                                '</div>'+
                            '</div>'+
                            '<div class="w3-display-container w3-left-align w3-col l4 m6 w3-hover-theme w3-padding">' +
                                '<div class="flexheader w3-left-align w3-container w3-padding">'+
                                    '<h1>'+
                                    'Trage hier den Titel 2 ein'+
                                    '</h1>'+
                                    '<p class="textblock">'+
                                    'Trage hier deinen Text 2 ein'+
                                    '</p>'+
                                '</div>'+
                            '</div>'+
                            '<div class="w3-display-container w3-left-align w3-col l4 m6 w3-padding">' +
                                '<div class="flexheader w3-left-align w3-container w3-padding">'+
                                    '<h1>'+
                                    'Trage hier den Titel 3 ein'+
                                    '</h1>'+
                                    '<p class="textblock">'+
                                    'Trage hier deinen Text 3 ein'+
                                    '</p>'+
                                '</div>'+
                            '</div>'+
                        '</div>'
                },
            {
                title: 'Image and Title',
                image: 'template1.gif',
                description: 'One main image with a title and text that surround the image.',
                html:
                    '<h3>' +
                        '<img style="margin-right: 10px" height="100" width="100" align="left"/>' +
                        'Type the title here'+
                    '</h3>' +
                    '<p>' +
                        'Type the text here' +
                    '</p>'
            },
            {
                title: 'Strange Template',
                image: 'template2.gif',
                description: 'A template that defines two colums, each one with a title, and some text.',
                html:
                    '<table style="width:100%; border-collapse: collapse;">' +
                        '<tr>' +
                            '<td style="width:50%">' +
                                '<h3>Title 1</h3>' +
                            '</td>' +
                            '<td></td>' +
                            '<td style="width:50%">' +
                                '<h3>Title 2</h3>' +
                            '</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td>' +
                                'Text 1' +
                            '</td>' +
                            '<td></td>' +
                            '<td>' +
                                'Text 2' +
                            '</td>' +
                        '</tr>' +
                    '</table>' +
                    '<p>' +
                        'More text goes here.' +
                    '</p>'
            },
            {
                title: 'Text and Table',
                image: 'template3.gif',
                description: 'A title with some text and a table.',
                html:
                    '<div style="width: 80%">' +
                        '<h3>' +
                            'Title goes here' +
                        '</h3>' +
                        '<table style="width:150px;float: right" cellspacing="0" cellpadding="0" border="1">' +
                            '<caption style="border:solid 1px black">' +
                                '<strong>Table title</strong>' +
                            '</caption>' +
                            '</tr>' +
                            '<tr>' +
                                '<td>&nbsp;</td>' +
                                '<td>&nbsp;</td>' +
                                '<td>&nbsp;</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td>&nbsp;</td>' +
                                '<td>&nbsp;</td>' +
                                '<td>&nbsp;</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td>&nbsp;</td>' +
                                '<td>&nbsp;</td>' +
                                '<td>&nbsp;</td>' +
                            '</tr>' +
                        '</table>' +
                        '<p>' +
                            'Type the text here' +
                        '</p>' +
                    '</div>'
            }
        ]
});
