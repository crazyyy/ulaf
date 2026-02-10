(function($) {

    let ACFModalUploader = {
        construct: function () {
            // Run initButton when the media button is clicked.
            $('.acf-modal-upload').each(function (index) {
                ACFModalUploader.initButton($(this));
            });
        },

        // Media Uploader to Modal settings
        initButton: function (_that) {
            _that.click(function (e) {

                e.preventDefault();

                let imageHolder = $(this).closest('.modal-preview-row');
                let layout =  $(this).attr('data-layout');
                let custom_uploader = wp.media({
                    title: 'Insert preview image',
                    library: {
                        type: 'image'
                    },
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false
                });

                custom_uploader.on('select', function() {
                    let media_attachment = custom_uploader.state().get('selection').first().toJSON();
                    imageHolder.find('img.modal-preview-image').attr('src', media_attachment.url);

                    let data = {
                        action: 'uefax_setModalImage',
                        layout: layout,
                        image: media_attachment.id,
                    };

                    $.ajax({
                        url: uefax_ajax.ajax_url,
                        type: 'POST',
                        data: data,
                        beforeSend: function ( xhr ) {
                            xhr.setRequestHeader( 'X-WP-Nonce', uefax_ajax.nonce );
                        },
                        success: function( response ) {

                            if ( response === '1' ) {
                                $('.modal-settings-title').prepend('<div class="notice notice-success is-dismissible"><p>Done! Preview image has been updated.</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');
                            } else {
                                $('.modal-settings-title').prepend('<div class="notice notice-error is-dismissible"><p>Error!</p><p>'+response+'</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');
                            }
                            $(".notice-dismiss").on('click', function(event) {
                                event.preventDefault();
                                $('.notice').fadeTo(100, 0, function() {
                                    $('.notice').slideUp(100, function() {
                                        $('.notice').remove();
                                    });
                                });
                            });
                        }
                    });
                });

                custom_uploader.open();

            });
        }
    }

    ACFModalUploader.construct();

})(jQuery);
