function check_api(provider)
{
    provider_input = jQuery('#wapt_'+provider+'-apikey');
    if(provider_input.val() !== "") {
        provider_input.addClass("checked_api_key_proccess");
        jQuery.post(ajaxurl, {
            action: 'aptp_check_api_key',
            provider: provider,
            key: provider_input.val(),
            nonce: jQuery('#wapt_ajax_nonce').val(),
        }).done(function (html) {
            provider_input.removeClass("checked_api_key_proccess");
            if (html) {
                provider_input.attr('style', 'border-color: green !important');
            } else {
                provider_input.attr('style', 'border-color: red !important');
            }
        });
    }
    else {
        provider_input.removeClass("checked_api_key_proccess");
        provider_input.removeAttr('style');
    }
}
jQuery(document).on('change', '#wapt_pixabay-apikey', function(event) {
    check_api('pixabay');
});
jQuery(document).on('change', '#wapt_unsplash-apikey', function(event) {
    check_api('unsplash');
});
