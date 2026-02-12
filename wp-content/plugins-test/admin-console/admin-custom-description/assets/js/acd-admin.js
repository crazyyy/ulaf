const {__, _x, _n, sprintf} = wp.i18n;
async function editpluginComment(button) {
    var item, pluginName, description, description_box;
    item = jQuery(button);
    pluginName = item.attr('data-plugin');
    description = item.siblings('.acd-description').text();
    description_box = item.siblings('.acd-description').first();
    console.log(description);
    if (pluginName) {
        const {value: text} = await Swal.fire({
            input: 'textarea',
            inputLabel: __('Plugin custom description', 'admin-custom-description'),
            inputPlaceholder: '',
            inputValue: description,
            allowOutsideClick: false,
            showCloseButton: true,
            showCancelButton: true,
            cancelButtonText: __('cancel', 'admin-custom-description'),
            confirmButtonText: __('edit', 'admin-custom-description')
        })

        if (text !== undefined) {
            item.addClass('acd-loading');
            let data = {
                'action': 'acd_edit_plugin_description',
                'plugin': pluginName,
                'comment': text,
                'nonce': acdAjax.ajaxNonce
            }

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,

                success: function (response) {

                    Swal.fire({
                        icon: 'success',
                        title: __('successful editing', 'admin-custom-description'),
                        text: __('comment edited successfully', 'admin-custom-description'),
                        confirmButtonText: __('close this box', 'admin-custom-description'),
                        timer: 3000,
                    });

                    description_box.text(text);
                    description_box.css("background-color", "#cafdca");
                    description_box.show();
                    item.removeClass('acd-loading');
                },
                error: function (err) {

                    Swal.fire({
                        icon: 'error',
                        title: __('edit failed', 'admin-custom-description'),
                        text: __('edit failed   ', 'admin-custom-description'),
                        confirmButtonText: __('I understood', 'admin-custom-description'),
                        timer: 5000,
                    });
                    item.removeClass('acd-loading');
                }
            });
        }
    }
}

function showPluginCustomDescription(button) {
    let item = jQuery(button);
    jQuery('.acd-description-wrapper').toggle();
}