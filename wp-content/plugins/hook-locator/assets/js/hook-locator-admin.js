/**
 * Hook Locator Admin JavaScript
 *
 * Provides enhanced functionality for the Hook Locator admin interface
 * including search improvements, copy-to-clipboard, and user interactions.
 *
 * @package HookLocator
 * @since   2.1.0
 */

(function($) {
	'use strict';

	/**
	 * Hook Locator Admin functionality object.
	 */
	const HookLocatorAdmin = {

		/**
		 * Initialize the admin functionality.
		 *
		 * @since 2.1.0
		 */
		init: function() {
			this.bindEvents();
			this.enhanceSearch();
			this.enhanceCodeDisplay();
			this.addTooltips();
		},

		/**
		 * Bind event handlers.
		 *
		 * @since 2.1.0
		 */
		bindEvents: function() {
			// Form submission loading state
			$('.hook-locator-search-form').on('submit', this.handleFormSubmit);

			// Copy to clipboard functionality
			$(document).on('click', '.hook-locator-file-path', this.copyCodeSnippet.bind(this));
			$(document).on('click', '.hook-locator-code-snippet', this.copyCodeSnippet.bind(this));
			$(document).on('click', '.hook-locator-copy-code', this.copyVisibleCode.bind(this));

			// Keyboard shortcuts
			$(document).on('keydown', this.handleKeyboardShortcuts.bind(this));
		},

		/**
		 * Handle form submission with loading state.
		 *
		 * @since 2.1.0
		 * @param {Event} e Form submit event.
		 */
		handleFormSubmit: function(e) {
			const $form = $(this);
			const $submit = $form.find('.hook-locator-search-btn');
			const $icon = $submit.find('.dashicons');
			const originalText = $submit.find(':not(.dashicons)').text().trim();

			// Show loading state
			$submit.prop('disabled', true);
			$icon.removeClass('dashicons-search').addClass('dashicons-update-alt');
			$submit.find(':not(.dashicons)').text(hookLocatorAdmin.searchText || ' Searching...');

			// Add spinning animation
			$icon.css('animation', 'rotation 1s infinite linear');

			// Fallback to re-enable button after timeout
			setTimeout(function() {
				$submit.prop('disabled', false);
				$icon.removeClass('dashicons-update-alt').addClass('dashicons-search');
				$icon.css('animation', '');
				$submit.find(':not(.dashicons)').text(originalText);
			}, 30000);
		},

		/**
		 * Enhance search functionality.
		 *
		 * @since 2.1.0
		 */
		enhanceSearch: function() {
			const $hookInput = $('#hook_name');

			if (!$hookInput.length) {
				return;
			}

			// Common WordPress hooks for autocomplete
			const commonHooks = [
				'init', 'wp_head', 'wp_footer', 'the_content', 'wp_enqueue_scripts',
				'admin_enqueue_scripts', 'wp_loaded', 'template_redirect', 'widgets_init',
				'admin_init', 'admin_menu', 'save_post', 'wp_ajax_', 'wp_ajax_nopriv_',
				'pre_get_posts', 'wp_print_styles', 'wp_print_scripts', 'login_enqueue_scripts',
				'wp_dashboard_setup', 'admin_notices', 'wp_before_admin_bar_render',
				'wp_insert_post', 'wp_update_post', 'wp_delete_post', 'comment_post',
				'user_register', 'wp_login', 'wp_logout', 'profile_update'
			];

			// Simple autocomplete functionality
			$hookInput.on('input', function() {
				const value = $(this).val().toLowerCase();

				if (value.length > 2) {
					const suggestions = commonHooks.filter(hook =>
						hook.toLowerCase().includes(value)
					);

					// Update title with first suggestion
					if (suggestions.length > 0) {
						$(this).attr('title', 'Suggestion: ' + suggestions[0]);
					} else {
						$(this).removeAttr('title');
					}
				}
			});

			// Clear input with Escape key
			$hookInput.on('keydown', function(e) {
				if (e.key === 'Escape') {
					$(this).val('').focus().removeAttr('title');
					e.preventDefault();
				}
			});

			// Auto-focus on page load
			if (!$hookInput.val()) {
				$hookInput.focus();
			}
		},

		/**
		 * Enhance code display functionality.
		 *
		 * @since 2.1.0
		 */
		enhanceCodeDisplay: function() {
			// Scroll to target line
			const $targetLine = $('.hook-locator-target-line');
			if ($targetLine.length) {
				const $codeDisplay = $('.hook-locator-code-display');
				if ($codeDisplay.length) {
					const scrollTop = $targetLine.position().top - ($codeDisplay.height() / 2);
					$codeDisplay.scrollTop(scrollTop);
				}
			}
		},

		/**
		 * Copy code snippet to clipboard.
		 *
		 * @since 2.1.0
		 * @param {Event} e Click event.
		 */
		copyCodeSnippet: function(e) {
			e.preventDefault();
			const text = $(e.currentTarget).text().trim();

			if (text) {
				this.copyToClipboard(text);
				this.showToast(hookLocatorAdmin.copyText || 'Code copied to clipboard!', 'success');
			}
		},

		/**
		 * Copy visible code from code display.
		 *
		 * @since 2.1.0
		 * @param {Event} e Click event.
		 */
		copyVisibleCode: function(e) {
			e.preventDefault();

			const $codeDisplay = $('.hook-locator-code-display');
			if (!$codeDisplay.length) {
				return;
			}

			let codeText = '';
			$codeDisplay.find('.hook-locator-context-line, .hook-locator-target-line').each(function() {
				const lineContent = $(this).find('.hook-locator-line-content').text();
				codeText += lineContent + '\n';
			});

			if (codeText) {
				this.copyToClipboard(codeText);
				this.showToast(hookLocatorAdmin.copyText || 'Code copied to clipboard!', 'success');
			}
		},

		/**
		 * Copy text to clipboard.
		 *
		 * @since 2.1.0
		 * @param {string} text Text to copy.
		 */
		copyToClipboard: function(text) {
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).catch(() => {
					this.fallbackCopyToClipboard(text);
				});
			} else {
				this.fallbackCopyToClipboard(text);
			}
		},

		/**
		 * Fallback clipboard copy method.
		 *
		 * @since 2.1.0
		 * @param {string} text Text to copy.
		 */
		fallbackCopyToClipboard: function(text) {
			const textArea = document.createElement('textarea');
			textArea.value = text;
			textArea.style.position = 'fixed';
			textArea.style.left = '-999999px';
			textArea.style.top = '-999999px';
			document.body.appendChild(textArea);
			textArea.focus();
			textArea.select();

			try {
				document.execCommand('copy');
				this.showToast(hookLocatorAdmin.copyText || 'Code copied to clipboard!', 'success');
			} catch (err) {
				this.showToast(hookLocatorAdmin.copyError || 'Could not copy to clipboard', 'error');
			}

			document.body.removeChild(textArea);
		},

		/**
		 * Handle keyboard shortcuts.
		 *
		 * @since 2.1.0
		 * @param {Event} e Keyboard event.
		 */
		handleKeyboardShortcuts: function(e) {
			// Ctrl/Cmd + K to focus search
			if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
				e.preventDefault();
				$('#hook_name').focus().select();
			}

			// Ctrl/Cmd + Enter to submit search form
			if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
				$('.hook-locator-search-form').submit();
			}

			// Ctrl/Cmd + C on target line to copy
			if ((e.ctrlKey || e.metaKey) && e.key === 'c' && $('.hook-locator-target-line:hover').length) {
				e.preventDefault();
				const lineContent = $('.hook-locator-target-line:hover .hook-locator-line-content').text();
				if (lineContent) {
					this.copyToClipboard(lineContent.trim());
					this.showToast('Line copied to clipboard!', 'success');
				}
			}
		},

		/**
		 * Add tooltips to elements.
		 *
		 * @since 2.1.0
		 */
		addTooltips: function() {
			// Add tooltips to type badges
			$('.hook-locator-type-badge').each(function() {
				const type = $(this).text().toLowerCase();
				let tooltip = '';

				switch (type) {
					case 'add action':
						tooltip = 'Registers a function to run when this action is triggered';
						break;
					case 'add filter':
						tooltip = 'Registers a function to modify data when this filter is applied';
						break;
					case 'do action':
						tooltip = 'Triggers all functions attached to this action';
						break;
					case 'apply filters':
						tooltip = 'Applies all functions attached to this filter';
						break;
					default:
						tooltip = 'WordPress hook function';
				}

				$(this).attr('title', tooltip);
			});

			// Add tooltip for search shortcut
			$('#hook_name').attr('title', $('#hook_name').attr('title') + ' (Ctrl+K to focus)');
		},

		/**
		 * Show toast notification.
		 *
		 * @since 2.1.0
		 * @param {string} message Message to display.
		 * @param {string} type    Toast type (success, error, warning).
		 */
		showToast: function(message, type = 'success') {
			// Remove existing toasts
			$('.hook-locator-toast').remove();

			const $toast = $('<div class="hook-locator-toast hook-locator-toast-' + type + '">' + 
				'<span class="hook-locator-toast-icon"></span>' +
				'<span class="hook-locator-toast-message">' + message + '</span>' +
				'</div>');

			$('body').append($toast);

			// Animate in
			setTimeout(function() {
				$toast.addClass('hook-locator-toast-show');
			}, 100);

			// Animate out and remove
			setTimeout(function() {
				$toast.removeClass('hook-locator-toast-show');
				setTimeout(function() {
					$toast.remove();
				}, 300);
			}, 3000);
		}
	};

	// Add CSS for spinning animation and toasts
	const additionalCSS = `
		<style>
		@keyframes rotation {
			from { transform: rotate(0deg); }
			to { transform: rotate(359deg); }
		}

		.hook-locator-toast {
			position: fixed;
			top: 32px;
			right: 20px;
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 12px 16px;
			background: #10b981;
			color: white;
			border-radius: 8px;
			font-weight: 500;
			font-size: 14px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
			opacity: 0;
			transform: translateX(100%);
			transition: all 0.3s ease;
			z-index: 999999;
			max-width: 300px;
		}

		.hook-locator-toast-show {
			opacity: 1;
			transform: translateX(0);
		}

		.hook-locator-toast-error {
			background: #ef4444;
		}

		.hook-locator-toast-warning {
			background: #f59e0b;
		}

		.hook-locator-toast-icon::before {
			content: "✓";
			font-weight: bold;
		}

		.hook-locator-toast-error .hook-locator-toast-icon::before {
			content: "✕";
		}

		.hook-locator-toast-warning .hook-locator-toast-icon::before {
			content: "⚠";
		}

		@media screen and (max-width: 768px) {
			.hook-locator-toast {
				right: 10px;
				left: 10px;
				right: 10px;
			}
		}
		</style>
	`;

	$('head').append(additionalCSS);

	// Initialize when document is ready
	$(document).ready(function() {
		HookLocatorAdmin.init();
	});

	// Make globally available for debugging
	window.HookLocatorAdmin = HookLocatorAdmin;

})(jQuery);
