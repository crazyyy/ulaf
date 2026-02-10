(function($) {
	"use strict";
	$(function() {
		var table      = $('#update-themes-table');
		var alchemists_table_update_screenshot = table.find("img[src*='wp-content/themes/alchemists/screenshot.png']");
		// remove td
		alchemists_table_update_screenshot.parent().parent().parent().remove();
	});
	$(window).on('load', function() {
		var alchemists_theme_screenshot = $('.theme-browser').find("img[src*='wp-content/themes/alchemists/screenshot.png']");
		alchemists_theme_screenshot.parent().next('.notice').remove();

		var alchemists_theme_overlay_screenshot = $('.theme-overlay .theme-about').find("img[src*='wp-content/themes/alchemists/screenshot.png']");
		alchemists_theme_overlay_screenshot.parent().parent().next('.theme-info').find('.notice').remove();
	});
})(jQuery);
