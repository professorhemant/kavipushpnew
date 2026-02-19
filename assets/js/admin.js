/**
 * Kavipushp Bridals - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initCategoryImage();
    });

    /**
     * Category Image Upload
     */
    function initCategoryImage() {
        var frame;

        $('#upload-category-image').on('click', function(e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Select Category Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#category_image').val(attachment.id);
                $('#category-image-preview').html('<img src="' + attachment.sizes.thumbnail.url + '" style="max-width:200px;">');
                $('#remove-category-image').show();
            });

            frame.open();
        });

        $('#remove-category-image').on('click', function(e) {
            e.preventDefault();
            $('#category_image').val('');
            $('#category-image-preview').html('');
            $(this).hide();
        });
    }

})(jQuery);
