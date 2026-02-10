jQuery(document).ready(function ($) {
    // Show the first tab content by default
    $('.tab-content').hide();
    $('.tab-content.active').show();

    // Tab switching
    $('.nav-tab').on('click', function (e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.tab-content').hide();
        $($(this).attr('href')).show();
    });

    // View option value
    $('.view-value').on('click', function () {
        $('#popup-value').text($(this).data('value'));
        $('#value-popup').fadeIn();
    });

    // Close popup
    $('#close-popup').on('click', function () {
        $('#value-popup').fadeOut();
    });

    // Disable autoload option
    $('.disable-option').on('click', function () {
        var optionName = $(this).data('option');
        $.post(autoload_optimizer_ajax.ajax_url, {
            action: 'disable_autoload_option',
            option_name: optionName,
            nonce: autoload_optimizer_ajax.nonce,
        }, function (response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // Restore autoload option
    $('.restore-option').on('click', function () {
        var optionName = $(this).data('option');
        $.post(autoload_optimizer_ajax.ajax_url, {
            action: 'restore_autoload_option',
            option_name: optionName,
            nonce: autoload_optimizer_ajax.nonce,
        }, function (response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });
});