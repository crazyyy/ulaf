/**
 * This is the javascript file for the module.
 *
 * @package UltimaKit_
 */

(function ( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	jQuery(document).ready(function ($) {

		// Initialize module functionality
		if( $('.ultimakit_module_simple_notification_bar').length > 0 ){
		    
			// Handle module click to show modal
		    $('.ultimakit_module_simple_notification_bar').on('click', function (e) {
		        e.preventDefault();
		        $("#ultimakit_module_simple_notification_bar_modal").modal('show');
		    });

			// Initialize color pickers
			if (typeof $.fn.wpColorPicker !== 'undefined') {
				$('#wpuk_noti_bg_color').wpColorPicker({
					defaultColor: '#ffcc00',
					change: function(event, ui) {
						// Handle color change if needed
					}
				});
				
				$('#wpuk_noti_txt_color').wpColorPicker({
					defaultColor: '#000000',
					change: function(event, ui) {
						// Handle color change if needed
					}
				});
			}
			
			// Handle form validation and submission
			$('#ultimakit_module_simple_notification_bar_modal form').on('submit', function(e) {
				// Add any form validation here if needed
				return true;
			});
			
			// Handle number input validation
			$('input[type="number"]').on('input', function() {
				var value = parseInt($(this).val());
				var min = parseInt($(this).attr('min'));
				var max = parseInt($(this).attr('max'));
				
				if (min !== undefined && value < min) {
					$(this).val(min);
				}
				if (max !== undefined && value > max) {
					$(this).val(max);
				}
			});
	    }

	});

})( jQuery );