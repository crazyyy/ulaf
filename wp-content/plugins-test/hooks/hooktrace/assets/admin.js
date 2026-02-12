/**
 * Admin JavaScript for Hooktrace timeline UI.
 */

(function() {
	'use strict';

	// Check if data is available
	if (typeof hookTrace === 'undefined') {
		return;
	}

	const { hooksList, selectedHook, selectedHookCallbacks, i18n } = hookTrace;
	let filteredHooks = hooksList || [];
	let selectedFunctionFilter = ''; // Current function filter for selected hook view

	// Default translations fallback
	const translations = i18n || {};

	// Initialize modal
	function initModal() {
		const trigger = document.querySelector('.trace-timeline-trigger');
		const modal = document.getElementById('trace-timeline-modal');
		const closeBtn = document.querySelector('.trace-timeline-close');
		const overlay = document.querySelector('.trace-timeline-overlay');
		const clearBtn = document.querySelector('.trace-clear-selection');

		if (!trigger || !modal) {
			return;
		}

		// Open modal
		trigger.addEventListener('click', function(e) {
			e.preventDefault();
			modal.style.display = 'block';
			document.body.classList.add('trace-modal-open');
			// Store scroll position
			document.body.style.top = `-${window.scrollY}px`;
			if (selectedHook) {
				renderSelectedHookDetails();
			} else {
				renderHooksList();
			}
		});

		// Close modal
		function closeModal() {
			modal.style.display = 'none';
			document.body.classList.remove('trace-modal-open');
			// Restore scroll position
			const scrollY = document.body.style.top;
			document.body.style.top = '';
			if (scrollY) {
				window.scrollTo(0, parseInt(scrollY || '0') * -1);
			}
		}

		if (closeBtn) {
			closeBtn.addEventListener('click', closeModal);
		}

		if (overlay) {
			overlay.addEventListener('click', closeModal);
		}

		// Clear selection
		if (clearBtn) {
			clearBtn.addEventListener('click', function() {
				const url = new URL(window.location.href);
				url.searchParams.delete('trace_hook');
				window.location.href = url.toString();
			});
		}

		// Search functionality
		const searchInput = document.getElementById('trace-search');
		if (searchInput) {
			searchInput.addEventListener('input', function() {
				applyFilters();
			});
		}

		// Filter functionality
		const typeFilter = document.getElementById('trace-filter-type');
		const sourceFilter = document.getElementById('trace-filter-source');

		if (typeFilter) {
			typeFilter.addEventListener('change', applyFilters);
		}

		if (sourceFilter) {
			sourceFilter.addEventListener('change', applyFilters);
		}

		// Function filter for selected hook view (in header)
		const functionFilter = document.getElementById('trace-function-filter');
		if (functionFilter) {
			functionFilter.addEventListener('change', function() {
				selectedFunctionFilter = this.value;
				renderSelectedHookDetails();
			});
		}
	}

	// Render hooks list
	function renderHooksList() {
		const container = document.getElementById('trace-hooks-list');
		if (!container) {
			return;
		}

		if (!hooksList || hooksList.length === 0) {
			container.innerHTML = `<div class="trace-empty"><div class="trace-empty-icon">⚡</div><p>${escapeHtml(translations.noHooksRecorded || 'No hooks recorded on this page')}</p></div>`;
			return;
		}

		if (filteredHooks.length === 0) {
			container.innerHTML = `<div class="trace-empty"><div class="trace-empty-icon">🔍</div><p>${escapeHtml(translations.noHooksMatch || 'No hooks match your filters')}</p></div>`;
			return;
		}

		const countLabel = translations.count || 'count:';
		const timesLabel = translations.times || 'times';

		let html = '';
		filteredHooks.forEach(hook => {
			const badgeClass = getBadgeClass(hook.source);
			const typeBadgeClass = hook.type === 'action' ? 'trace-badge-action' : 'trace-badge-filter';
			const count = hook.count || 1;
			
			html += `
				<div class="trace-hook-item" data-hook="${escapeHtml(hook.hook_name)}">
					<div class="trace-hook-name">
						${escapeHtml(hook.hook_name)}
						<span class="trace-hook-count">(${escapeHtml(countLabel)} ${count} ${escapeHtml(timesLabel)})</span>
					</div>
					<div class="trace-hook-meta">
						<span class="trace-badge ${typeBadgeClass}">${escapeHtml(hook.type)}</span>
						<span class="trace-badge ${badgeClass}">${escapeHtml(hook.source)}</span>
					</div>
				</div>
			`;
		});

		container.innerHTML = html;

		// Add click handlers
		container.querySelectorAll('.trace-hook-item').forEach(item => {
			item.addEventListener('click', function() {
				const hookName = this.getAttribute('data-hook');
				selectHook(hookName);
			});
		});
	}

	// Select hook and refresh page
	function selectHook(hookName) {
		const url = new URL(window.location.href);
		url.searchParams.set('trace_hook', hookName);
		window.location.href = url.toString();
	}

	// Get unique function names from callbacks
	function getUniqueFunctionNames() {
		if (!selectedHookCallbacks || selectedHookCallbacks.length === 0) {
			return [];
		}
		const names = new Set();
		selectedHookCallbacks.forEach(callback => {
			const name = callback.callback || callback.name || '{unknown}';
			names.add(name);
		});
		return Array.from(names).sort();
	}

	// Calculate statistics for filtered callbacks
	function calculateStats(callbacks) {
		if (!callbacks || callbacks.length === 0) {
			return null;
		}
		const durations = callbacks.map(c => c.duration || 0);
		const total = durations.reduce((sum, d) => sum + d, 0);
		const min = Math.min(...durations);
		const max = Math.max(...durations);
		const avg = total / durations.length;
		return {
			count: callbacks.length,
			total: total.toFixed(2),
			min: min.toFixed(2),
			max: max.toFixed(2),
			avg: avg.toFixed(2)
		};
	}

	// Filter callbacks by function name
	function filterCallbacksByFunction(functionName) {
		if (!functionName || !selectedHookCallbacks) {
			return selectedHookCallbacks || [];
		}
		return selectedHookCallbacks.filter(callback => {
			const name = callback.callback || callback.name || '';
			return name === functionName;
		});
	}

	// Render selected hook details
	function renderSelectedHookDetails() {
		const container = document.getElementById('trace-selected-hook-details');
		if (!container) {
			return;
		}

		// Update hook count in header
		if (selectedHook) {
			const hookData = hooksList.find(hook => hook.hook_name === selectedHook);
			const count = hookData ? (hookData.count || 1) : 1;
			const countElement = document.getElementById('trace-hook-count');
			if (countElement) {
				const calledLabel = translations.called || 'called';
				const timeLabel = count !== 1 ? (translations.times || 'times') : (translations.time || 'time');
				countElement.textContent = `(${calledLabel} ${count} ${timeLabel})`;
			}
		}

		if (!selectedHookCallbacks || selectedHookCallbacks.length === 0) {
			const noCallbacksMsg = translations.noCallbacksFound || 'No callbacks found for this hook';
			const noCallbacksHint = translations.noCallbacksHint || 'This hook may not have any registered callbacks, or it may not have fired on this page.';
			container.innerHTML = `<div class="trace-empty"><div class="trace-empty-icon">📝</div><p>${escapeHtml(noCallbacksMsg)}</p><p style="font-size: 12px; margin-top: 10px; color: #999;">${escapeHtml(noCallbacksHint)}</p></div>`;
			return;
		}

		// Get filtered callbacks
		const filteredCallbacks = filterCallbacksByFunction(selectedFunctionFilter);
		const stats = calculateStats(filteredCallbacks);

		// Populate header filter dropdown
		const functionNames = getUniqueFunctionNames();
		const headerFilter = document.getElementById('trace-function-filter');
		const allLabel = translations.all || 'All';
		if (headerFilter) {
			headerFilter.innerHTML = `
				<option value="">${escapeHtml(allLabel)} (${selectedHookCallbacks.length})</option>
				${functionNames.map(name => `<option value="${escapeHtml(name)}" ${selectedFunctionFilter === name ? 'selected' : ''}>${escapeHtml(name)}</option>`).join('')}
			`;
		}

		// Labels for stats
		const countLabel = translations.countLabel || 'Count';
		const totalLabel = translations.total || 'Total';
		const avgLabel = translations.avg || 'Avg';
		const minLabel = translations.min || 'Min';
		const maxLabel = translations.max || 'Max';

		// Build sticky header with stats only
		let stickyHtml = stats ? `<div class="trace-sticky-header">
			<div class="trace-stats-banner">
				<div class="trace-stat-item"><span class="trace-stat-label">${escapeHtml(countLabel)}</span><span class="trace-stat-value">${stats.count}</span></div>
				<div class="trace-stat-item"><span class="trace-stat-label">${escapeHtml(totalLabel)}</span><span class="trace-stat-value">${stats.total}ms</span></div>
				<div class="trace-stat-item"><span class="trace-stat-label">${escapeHtml(avgLabel)}</span><span class="trace-stat-value">${stats.avg}ms</span></div>
				<div class="trace-stat-item"><span class="trace-stat-label">${escapeHtml(minLabel)}</span><span class="trace-stat-value">${stats.min}ms</span></div>
				<div class="trace-stat-item"><span class="trace-stat-label">${escapeHtml(maxLabel)}</span><span class="trace-stat-value">${stats.max}ms</span></div>
			</div>
		</div>` : '';

		// Labels for callback details
		const priorityLabel = translations.priority || 'Priority:';
		const executionOrderLabel = translations.executionOrder || 'Execution Order:';

		// Build compact callbacks list
		let callbacksHtml = '';
		filteredCallbacks.forEach(callback => {
			const badgeClass = getBadgeClass(callback.plugin);
			const callbackName = callback.callback || callback.name || '{unknown}';
			const filePath = callback.file ? `${callback.file}${callback.line ? ':' + callback.line : ''}` : '';
			
			// Build file path display with links for both editors if available
			let filePathHtml = '';
			if (filePath) {
				const hasLocalEditor = callback.local_editor_url && callback.local_editor_url.length > 0;
				const hasWpEditor = callback.wp_editor_url && callback.wp_editor_url.length > 0;
				
				if (hasLocalEditor || hasWpEditor) {
					let linksHtml = '';
					
					// Local editor link
					if (hasLocalEditor) {
						const editorName = hookTrace.editorName || 'Local Editor';
						linksHtml += `<a href="${escapeHtml(callback.local_editor_url)}" target="_self" class="trace-file-link trace-file-link-local" title="${escapeHtml('Open in ' + editorName)}">
							<span class="dashicons dashicons-editor-code"></span> ${escapeHtml('Open in ' + editorName)}
						</a>`;
					}
					
					// WordPress editor link
					if (hasWpEditor) {
						if (hasLocalEditor) {
							linksHtml += ' <span class="trace-file-link-separator">|</span> ';
						}
						linksHtml += `<a href="${escapeHtml(callback.wp_editor_url)}" target="_blank" class="trace-file-link trace-file-link-wp" title="${escapeHtml(hookTrace.i18n.openInWpEditor || 'Open in WordPress Editor')}">
							<span class="dashicons dashicons-external"></span> ${escapeHtml(hookTrace.i18n.openInWpEditor || 'Open in WordPress Editor')}
						</a>`;
					}
					
					filePathHtml = `<div class="trace-file-path">
						<div class="trace-file-path-text">${escapeHtml(filePath)}</div>
						<div class="trace-file-links">${linksHtml}</div>
					</div>`;
				} else {
					filePathHtml = `<div class="trace-file-path">${escapeHtml(filePath)}</div>`;
				}
			}
			
			callbacksHtml += `
				<div class="trace-callback-item">
					<div class="trace-callback-header">
						<span class="trace-callback-name">${escapeHtml(callbackName)}</span>
						<span class="trace-badge ${badgeClass}">${escapeHtml(callback.plugin)}</span>
						<span class="trace-callback-meta">${escapeHtml(priorityLabel)} ${callback.priority || 10} | ${escapeHtml(executionOrderLabel)} ${callback.execution_order || 0}</span>
						<span class="trace-duration-badge">${callback.duration || 0}ms</span>
					</div>
					${filePathHtml}
				</div>
			`;
		});

		container.innerHTML = stickyHtml + '<div class="trace-callbacks-list">' + callbacksHtml + '</div>';
	}

	// Apply filters
	function applyFilters() {
		const searchTerm = document.getElementById('trace-search')?.value.toLowerCase() || '';
		const typeFilter = document.getElementById('trace-filter-type')?.value || '';
		const sourceFilter = document.getElementById('trace-filter-source')?.value || '';

		filteredHooks = hooksList.filter(hook => {
			// Search filter
			if (searchTerm && !hook.hook_name.toLowerCase().includes(searchTerm)) {
				return false;
			}

			// Type filter
			if (typeFilter && hook.type !== typeFilter) {
				return false;
			}

			// Source filter
			if (sourceFilter) {
				if (sourceFilter === 'core' && hook.source !== 'core') {
					return false;
				}
				if (sourceFilter === 'theme' && hook.source !== 'theme') {
					return false;
				}
				if (sourceFilter === 'plugin' && (hook.source === 'core' || hook.source === 'theme')) {
					return false;
				}
			}

			return true;
		});

		renderHooksList();
	}

	// Helper functions
	function getBadgeClass(source) {
		if (source === 'core') {
			return 'trace-badge-core';
		}
		if (source === 'theme') {
			return 'trace-badge-theme';
		}
		if (source && source !== 'unknown') {
			return 'trace-badge-plugin';
		}
		return 'trace-badge-plugin';
	}

	function escapeHtml(text) {
		if (text === null || text === undefined) {
			return '';
		}
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initModal);
	} else {
		initModal();
	}
})();
