/**
 * This file enables JavaScript related features shared by multiple menus. Specifically:
 *
 * - Show and hide the sub menus available in the admin toolbar when the user hovers over the
 * "admin-toolbar-menu-item-more" class.
 *
 * @package daext-interlinks-manager
 */

jQuery( document ).ready(

	function ($) {

		'use strict';

		/**
		 * When the "admin-toolbar-menu-item-more" class is hovered, then show the "pop-sub-menu" sub menu.
		 *
		 * When the user does not hover over the "admin-toolbar-menu-item-more" or "pop-sub-menu" class, then hide the
		 * "pop-sub-menu" sub menu.
		 */
		$( document.body ).on(
			'mouseenter',
			'.daextinma-admin-toolbar__menu-item-more',
			function () {
				$( '.daextinma-admin-toolbar__pop-sub-menu' ).show();
			}
		);

		$( document.body ).on(
			'mouseleave',
			'.daextinma-admin-toolbar__menu-item-more, .daextinma-admin-toolbar__pop-sub-menu',
			function () {
				$( '.daextinma-admin-toolbar__pop-sub-menu' ).hide();
			}
		);

	}

);
