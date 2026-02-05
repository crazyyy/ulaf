/**
 * Mini Gallery Settings View JavaScript
 * 
 * Handles interactive functionality for the module settings page.
 */

(function ($) {
    'use strict';

    /**
     * Initialize settings functionality
     */
    function initSettings() {
        initToggleSwitches();
        initUnsavedChangesWarning();
    }

    /**
     * Initialize toggle switch interactions
     */
    function initToggleSwitches() {
        $('.mgwpp-module-toggle').on('change', function () {
            var $card = $(this).closest('.mgwpp-module-card');
            var $label = $(this).siblings('.mgwpp-toggle-label');
            var isEnabled = $(this).is(':checked');

            // Update card state
            if (isEnabled) {
                $card.removeClass('mgwpp-module-disabled').addClass('mgwpp-module-enabled');
                $label.text(mgwppSettingsL10n?.enabled || 'Enabled');
            } else {
                $card.removeClass('mgwpp-module-enabled').addClass('mgwpp-module-disabled');
                $label.text(mgwppSettingsL10n?.disabled || 'Disabled');
            }

            // Add visual feedback
            $card.addClass('mgwpp-module-updating');
            setTimeout(function () {
                $card.removeClass('mgwpp-module-updating');
            }, 300);

            // Mark form as changed
            $('.mgwpp-settings-form').data('changed', true);
        });
    }

    /**
     * Warn about unsaved changes
     */
    function initUnsavedChangesWarning() {
        $(window).on('beforeunload', function (e) {
            if ($('.mgwpp-settings-form').data('changed')) {
                var message = 'You have unsaved changes. Are you sure you want to leave?';
                e.returnValue = message;
                return message;
            }
        });

        // Clear warning on form submit
        $('.mgwpp-settings-form').on('submit', function () {
            $(this).data('changed', false);
        });
    }

    // Initialize when DOM is ready
    $(document).ready(initSettings);

})(jQuery);
