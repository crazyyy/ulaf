jQuery(document).ready(function($) {
    // Handle "Remind me later" click
    $('.divewp-remind-later').on('click', function(e) {
        e.preventDefault();
        dismissNotice('remind_later');
    });

    // Handle "I already rated it" click
    $('.divewp-already-rated').on('click', function(e) {
        e.preventDefault();
        dismissNotice('permanent');
    });

    // Handle WordPress dismiss button click
    $(document).on('click', '.divewp-feedback-notice .notice-dismiss', function(e) {
        e.preventDefault();
        dismissNotice('permanent');
    });

    // Function to dismiss notice
    function dismissNotice(type) {
        $.ajax({
            url: divewpFeedback.ajaxurl,
            type: 'POST',
            data: {
                action: 'divewp_dismiss_feedback',
                type: type,
                nonce: divewpFeedback.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.divewp-feedback-notice').slideUp(200);
                }
            }
        });
    }
}); 