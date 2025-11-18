jQuery(function ($) {
    'use strict';
    var frame;
    $('#gms_cs_background_image_button').on('click', function (e) {
        e.preventDefault();
        if (frame) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: 'Hintergrundbild auswählen',
            button: { text: 'Verwenden' },
            multiple: false
        });
        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#gms_cs_background_image').val(attachment.url);
        });
        frame.open();
    });
});
