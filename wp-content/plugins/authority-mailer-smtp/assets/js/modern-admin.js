/**
 * Authority Mailer - Modern Admin Scripts
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

(function($) {
	'use strict';

	/**
	 * Initialize modern admin features
	 */
	function initModernAdmin() {
		// Animate stat cards on load
		animateStatsOnLoad();
		
		// Initialize counter animations
		initCounterAnimations();
		
		// Add smooth scroll behavior
		initSmoothScroll();
		
		// Initialize tooltips
		initTooltips();
		
		// Add fade-in effects to cards
		initCardAnimations();
	}

	/**
	 * Animate stats cards on page load
	 */
	function animateStatsOnLoad() {
		$('.am-stat-card').each(function(index) {
			const $card = $(this);
			setTimeout(function() {
				$card.addClass('am-animate-in');
			}, index * 100);
		});
	}

	/**
	 * Animate counters from 0 to target value
	 */
	function initCounterAnimations() {
		$('.am-stat-value').each(function() {
			const $counter = $(this);
			const target = parseInt($counter.text().replace(/,/g, ''));
			
			if (isNaN(target)) {
				return;
			}

			$counter.text('0');
			
			const duration = 1500; // 1.5 seconds
			const increment = target / (duration / 16); // 60fps
			let current = 0;

			const timer = setInterval(function() {
				current += increment;
				if (current >= target) {
					current = target;
					clearInterval(timer);
				}
				$counter.text(formatNumber(Math.floor(current)));
			}, 16);
		});
	}

	/**
	 * Format number with commas
	 */
	function formatNumber(num) {
		return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
	}

	/**
	 * Initialize smooth scroll for anchor links
	 */
	function initSmoothScroll() {
		$('a[href^="#"]').on('click', function(e) {
			const target = $(this.getAttribute('href'));
			if (target.length) {
				e.preventDefault();
				$('html, body').stop().animate({
					scrollTop: target.offset().top - 100
				}, 600);
			}
		});
	}

	/**
	 * Initialize tooltips
	 */
	function initTooltips() {
		// Simple tooltip implementation
		$('[data-tooltip]').each(function() {
			const $element = $(this);
			const tooltipText = $element.attr('data-tooltip');
			
			$element.css('position', 'relative');
			
			const $tooltip = $('<div class="am-tooltip"></div>')
				.text(tooltipText)
				.css({
					position: 'absolute',
					bottom: '100%',
					left: '50%',
					transform: 'translateX(-50%) translateY(-8px)',
					background: 'rgba(0, 0, 0, 0.9)',
					color: 'white',
					padding: '6px 12px',
					borderRadius: '6px',
					fontSize: '12px',
					whiteSpace: 'nowrap',
					opacity: 0,
					pointerEvents: 'none',
					transition: 'opacity 0.2s ease-in-out',
					zIndex: 1000
				});
			
			$element.on('mouseenter', function() {
				$element.append($tooltip);
				setTimeout(function() {
					$tooltip.css('opacity', 1);
				}, 10);
			});
			
			$element.on('mouseleave', function() {
				$tooltip.css('opacity', 0);
				setTimeout(function() {
					$tooltip.remove();
				}, 200);
			});
		});
	}

	/**
	 * Fade in cards as they enter viewport
	 */
	function initCardAnimations() {
		const observer = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('am-fade-in');
					observer.unobserve(entry.target);
				}
			});
		}, {
			threshold: 0.1
		});

		document.querySelectorAll('.am-card, .am-template-card').forEach(function(card) {
			observer.observe(card);
		});
	}

	/**
	 * Template card interactions
	 */
	function initTemplateCards() {
		$('.am-template-card').on('click', function() {
			const templateId = $(this).data('template-id');
			if (templateId) {
				// Trigger template selection
				$(document).trigger('am:template:selected', [templateId]);
			}
		});
	}

	/**
	 * Chart animations and interactions
	 */
	function initChartEnhancements() {
		// Add loading state to charts
		$('.am-chart-wrapper').each(function() {
			const $wrapper = $(this);
			if ($wrapper.find('canvas').length === 0) {
				$wrapper.html('<div class="am-loading"><div class="am-loading-spinner"></div></div>');
			}
		});
	}

	/**
	 * Table enhancements
	 */
	function initTableEnhancements() {
		// Add hover effect to table rows
		$('.am-table tbody tr').hover(
			function() {
				$(this).css('transform', 'scale(1.01)');
			},
			function() {
				$(this).css('transform', 'scale(1)');
			}
		);

		// Make table rows clickable if they have a data-href attribute
		$('.am-table tbody tr[data-href]').css('cursor', 'pointer').on('click', function() {
			window.location = $(this).data('href');
		});
	}

	/**
	 * Add ripple effect to buttons
	 */
	function initRippleEffect() {
		$('.am-btn').on('click', function(e) {
			const $button = $(this);
			const $ripple = $('<span class="am-ripple"></span>');
			
			$button.css('position', 'relative').css('overflow', 'hidden');
			
			const diameter = Math.max($button.outerWidth(), $button.outerHeight());
			const radius = diameter / 2;
			
			const x = e.pageX - $button.offset().left - radius;
			const y = e.pageY - $button.offset().top - radius;
			
			$ripple.css({
				position: 'absolute',
				width: diameter,
				height: diameter,
				borderRadius: '50%',
				background: 'rgba(255, 255, 255, 0.6)',
				left: x,
				top: y,
				transform: 'scale(0)',
				animation: 'am-ripple-animation 0.6s ease-out'
			});
			
			$button.append($ripple);
			
			setTimeout(function() {
				$ripple.remove();
			}, 600);
		});
		
		// Add ripple animation CSS if not already present
		if ($('#am-ripple-animation').length === 0) {
			$('<style id="am-ripple-animation">@keyframes am-ripple-animation { to { transform: scale(4); opacity: 0; } }</style>').appendTo('head');
		}
	}

	/**
	 * Search and filter functionality
	 */
	function initSearchFilters() {
		$('.am-search-input').on('input', debounce(function() {
			const query = $(this).val().toLowerCase();
			const $items = $('.am-filterable-item');
			
			$items.each(function() {
				const $item = $(this);
				const text = $item.text().toLowerCase();
				
				if (text.indexOf(query) !== -1) {
					$item.show().addClass('am-fade-in');
				} else {
					$item.hide();
				}
			});
			
			// Show empty state if no results
			const visibleCount = $items.filter(':visible').length;
			if (visibleCount === 0) {
				if ($('.am-empty-state').length === 0) {
					$items.parent().append(
						'<div class="am-empty-state">' +
							'<div class="am-empty-state-title">No results found</div>' +
							'<div class="am-empty-state-description">Try adjusting your search criteria</div>' +
						'</div>'
					);
				}
			} else {
				$('.am-empty-state').remove();
			}
		}, 300));
	}

	/**
	 * Debounce helper function
	 */
	function debounce(func, wait) {
		let timeout;
		return function() {
			const context = this;
			const args = arguments;
			clearTimeout(timeout);
			timeout = setTimeout(function() {
				func.apply(context, args);
			}, wait);
		};
	}

	/**
	 * Copy to clipboard functionality
	 */
	function initCopyToClipboard() {
		$(document).on('click', '[data-copy]', function(e) {
			e.preventDefault();
			const text = $(this).data('copy');
			
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function() {
					showCopyNotification(e.currentTarget);
				});
			} else {
				// Fallback for older browsers
				const $temp = $('<textarea>').val(text).appendTo('body').select();
				document.execCommand('copy');
				$temp.remove();
				showCopyNotification(e.currentTarget);
			}
		});
	}

	/**
	 * Show copy notification
	 */
	function showCopyNotification(element) {
		const $notification = $('<div class="am-copy-notification">Copied!</div>');
		
		$notification.css({
			position: 'fixed',
			top: '20px',
			right: '20px',
			background: 'var(--am-success)',
			color: 'white',
			padding: '12px 24px',
			borderRadius: '8px',
			boxShadow: 'var(--am-shadow-lg)',
			zIndex: 10000,
			opacity: 0,
			transform: 'translateY(-20px)',
			transition: 'all 0.3s ease-out'
		});
		
		$('body').append($notification);
		
		setTimeout(function() {
			$notification.css({
				opacity: 1,
				transform: 'translateY(0)'
			});
		}, 10);
		
		setTimeout(function() {
			$notification.css({
				opacity: 0,
				transform: 'translateY(-20px)'
			});
			setTimeout(function() {
				$notification.remove();
			}, 300);
		}, 2000);
	}

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		initModernAdmin();
		initTemplateCards();
		initChartEnhancements();
		initTableEnhancements();
		initRippleEffect();
		initSearchFilters();
		initCopyToClipboard();
	});

})(jQuery);
