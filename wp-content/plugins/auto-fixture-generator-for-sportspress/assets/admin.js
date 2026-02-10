(function ($) {
	'use strict';

	/**
	 * Calculate total matches for k-round robin tournament.
	 *
	 * @param {number} teams Number of teams (n).
	 * @param {number} rounds Number of rounds (k).
	 * @return {number} Total number of matches.
	 */
	function calculateRoundRobinMatches(teams, rounds) {
		// Ensure valid integers.
		teams = Math.abs(parseInt(teams) || 0);
		rounds = Math.abs(parseInt(rounds) || 0);

		// If less than 2 teams, no matches can be scheduled.
		if (teams < 2 || rounds < 1) {
			return 0;
		}

		/**
		 * Formula:
		 *   Matches = k * ( n * ( n - 1 ) / 2 )
		 * where n = number of teams, k = number of rounds.
		 */
		var matches = rounds * ((teams * (teams - 1)) / 2);

		return Math.floor(matches);
	}

	/**
	 * Calculate total matches for fixed week season.
	 *
	 * @param {number} teams Number of teams (n).
	 * @param {number} weeks Number of weeks in the season.
	 * @return {number} Total number of matches.
	 */
	function calculateFixedWeekMatches(teams, weeks) {
		// Ensure valid integers.
		teams = Math.abs(parseInt(teams) || 0);
		weeks = Math.abs(parseInt(weeks) || 0);

		// If less than 2 teams or no weeks, no matches can be scheduled.
		if (teams < 2 || weeks < 1) {
			return 0;
		}

		/**
		 * Formula:
		 *   Matches per week = floor(n / 2)
		 *   Total matches = weeks * matches_per_week
		 */
		var matchesPerWeek = Math.floor(teams / 2);
		var matches = weeks * matchesPerWeek;

		return matches;
	}

	/**
	 * Get the count of selected teams.
	 * For premium users, counts checked checkboxes.
	 * For free users, returns the cached count from AJAX response.
	 *
	 * @return {number} Number of teams.
	 */
	function getTeamsCount() {
		// For premium users with team checkboxes visible
		if ($('#afgsp_teams').is(':visible') && $('#afgsp_teams input:checked').length > 0) {
			return $('#afgsp_teams input:checked').length;
		}
		// For free users, use cached count if available
		if (window.AFGSP_ADMIN && typeof window.AFGSP_ADMIN.teamsCount === 'number') {
			return window.AFGSP_ADMIN.teamsCount;
		}
		return 0;
	}

	/**
	 * Get the count of selected days in the gameweek builder.
	 *
	 * @return {number} Number of selected days.
	 */
	function getSelectedDaysCount() {
		var count = $('#afgsp_gameweek_builder input[type="checkbox"]:checked').length;
		return count > 0 ? count : 2; // Default to 2 (Sat, Sun)
	}

	/**
	 * Calculate events per timeslot using the AUTO mode formula.
	 *
	 * @param {number} teamsCount Number of teams.
	 * @param {number} daysCount Number of selected match days.
	 * @param {number} slotsCount Number of time slots.
	 * @return {number} Events per timeslot.
	 */
	function calculateEventsPerSlot(teamsCount, daysCount, slotsCount) {
		teamsCount = Math.max(2, parseInt(teamsCount) || 2);
		daysCount = Math.max(1, parseInt(daysCount) || 1);
		slotsCount = Math.max(1, parseInt(slotsCount) || 1);

		var matchesPerRound = Math.floor(teamsCount / 2);
		var matchesPerDay = Math.ceil(matchesPerRound / daysCount);
		var eventsPerSlot = Math.ceil(matchesPerDay / slotsCount);

		return Math.max(1, eventsPerSlot);
	}

	/**
	 * Update all events per slot input fields with the calculated auto value.
	 * In AUTO mode, inputs are disabled and show the calculated value.
	 * In MANUAL mode (premium only), inputs are enabled for custom values.
	 */
	function updateEventsPerSlotInputs() {
		var teamsCount = getTeamsCount();
		var daysCount = getSelectedDaysCount();
		var slotsCount = $('#afgsp_time_slots input[type="time"]').length || 1;
		var autoValue = calculateEventsPerSlot(teamsCount, daysCount, slotsCount);

		var mode = $('#afgsp_events_mode').val() || 'auto';
		var isPremium = window.AFGSP_ADMIN && window.AFGSP_ADMIN.isPremium;

		$('.afgsp-events-per-slot').each(function() {
			// In AUTO mode or for free users, always show auto value and disable
			if (mode === 'auto' || !isPremium) {
				$(this).val(autoValue).prop('disabled', true);
			} else if (mode === 'manual' && isPremium) {
				// In MANUAL mode for premium, enable but keep current value if set
				if (!$(this).val() || $(this).val() === '0') {
					$(this).val(autoValue);
				}
				$(this).prop('disabled', false);
			}
		});
	}

	/**
	 * Build default entity name from selected League and Season.
	 */
	function buildDefaultEntityName() {
		var leagueText = $('#afgsp_league option:selected').text() || '';
		var seasonText = $('#afgsp_season option:selected').text() || '';
		leagueText = String(leagueText).trim();
		seasonText = String(seasonText).trim();
		if (!leagueText && !seasonText) {
			return '';
		}
		if (leagueText && seasonText) {
			return leagueText + ' ' + seasonText;
		}
		return leagueText || seasonText;
	}

	/**
	 * Update the events description based on selected algorithm and teams count.
	 */
	function updateEventsDescription() {
		var algorithmSlug = $('#afgsp_algorithm').val();
		var teamsCount = $('#afgsp_teams input:checked').length;
		
		// For free users, if teams are not visible but algorithm is selected, 
		// we need to fetch teams count via AJAX
		if (!algorithmSlug) {
			$('#afgsp_events_description').remove();
			return;
		}

		// If teams are visible and none selected, hide description
		if ($('#afgsp_teams').is(':visible') && teamsCount === 0) {
			$('#afgsp_events_description').remove();
			return;
		}

		var map = (window.AFGSP_ADMIN && window.AFGSP_ADMIN.optionsByAlgorithm) || {};
		var schema = map[algorithmSlug];
		
		// Check if this is a fixed week season algorithm
		var isFixedWeekSeason = (algorithmSlug === 'fixed-week-season');
		
		// For fixed week season, we need the weeks input; for others, we need _rounds
		if (!isFixedWeekSeason && (!schema || !schema._rounds)) {
			$('#afgsp_events_description').remove();
			return;
		}

		var rounds = schema ? schema._rounds : 1;
		
		// Get season weeks value for fixed week algorithm
		var seasonWeeks = 0;
		if (isFixedWeekSeason) {
			var $weeksInput = $('#afgsp_option_season_weeks');
			seasonWeeks = $weeksInput.length ? parseInt($weeksInput.val()) || 0 : 0;
			if (seasonWeeks < 1) {
				// Use default from schema if input not yet rendered or empty
				seasonWeeks = (schema && schema.season_weeks && schema.season_weeks.default) ? schema.season_weeks.default : 10;
			}
		}
		
		// If teams are not visible (free users), fetch teams count
		if (!$('#afgsp_teams').is(':visible')) {
			var leagueId = $('#afgsp_league').val();
			var seasonId = $('#afgsp_season').val();
			
			if (leagueId && seasonId) {
				$.getJSON((window.AFGSP_ADMIN && window.AFGSP_ADMIN.ajaxUrl) || '', {
					action: 'afgsp_get_teams',
					league: leagueId,
					season: seasonId,
					nonce: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.nonce) || ''
				}).done(function (resp) {
					var teams = (resp && resp.data && Array.isArray(resp.data.teams) && resp.data.teams) || [];
					var eventsCount;
					if (isFixedWeekSeason) {
						eventsCount = calculateFixedWeekMatches(teams.length, seasonWeeks);
					} else {
						eventsCount = calculateRoundRobinMatches(teams.length, rounds);
					}
					displayEventsDescription(eventsCount, teams.length);
				}).fail(function () {
					$('#afgsp_events_description').remove();
				});
			} else {
				$('#afgsp_events_description').remove();
			}
		} else {
			// Teams are visible, use checked count
			var eventsCount;
			if (isFixedWeekSeason) {
				eventsCount = calculateFixedWeekMatches(teamsCount, seasonWeeks);
			} else {
				eventsCount = calculateRoundRobinMatches(teamsCount, rounds);
			}
			displayEventsDescription(eventsCount, teamsCount);
		}
	}

	/**
	 * Display the events description.
	 */
	function displayEventsDescription(eventsCount, teamsCount) {
		if (eventsCount <= 0) {
			$('#afgsp_events_description').remove();
			return;
		}

		var description = 'Will be generated **' + eventsCount + '** of events with **' + teamsCount + '** teams';
		
		// Remove existing description if any
		$('#afgsp_events_description').remove();
		
		// Add new description after the algorithm select
		var $description = $('<p id="afgsp_events_description" class="description"></p>').text(description);
		$('#afgsp_algorithm').closest('td').append($description);
	}

	/**
	 * Update the Teams description line for free users to include the current team names.
	 */
	function updateTeamsDescriptionForFree() {
		if (!window.AFGSP_ADMIN || !!window.AFGSP_ADMIN.isPremium) {
			return;
		}

		var $desc = $('#afgsp_teams_desc');
		if ($desc.length === 0) {
			return;
		}

		var leagueId = $('#afgsp_league').val();
		var seasonId = $('#afgsp_season').val();

		// Base text for free users
		var baseText = 'Advanced team selection is a Premium feature. All teams will be included in the free version.';

		if (!leagueId || !seasonId) {
			$desc.text(baseText);
			return;
		}

		$.getJSON((window.AFGSP_ADMIN && window.AFGSP_ADMIN.ajaxUrl) || '', {
			action: 'afgsp_get_teams',
			league: leagueId,
			season: seasonId,
			nonce: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.nonce) || ''
		}).done(function (resp) {
			var teams = (resp && resp.data && Array.isArray(resp.data.teams) && resp.data.teams) || [];
			var names = teams.map(function (t) { return t && t.title ? String(t.title) : ''; }).filter(function (s) { return s.length > 0; });
			if (names.length > 0) {
				$desc.text(baseText + ' Currently selected teams are: ' + names.join(', '));
			} else {
				$desc.text(baseText);
			}
		}).fail(function () {
			$desc.text(baseText);
		});
	}

	function renderOptions(schema) {
		var $container = $('#afgsp_dynamic_options');
		$container.empty();

		if (!schema || Object.keys(schema).length === 0) {
			$('#afgsp_dynamic_options_row').hide();
			return;
		}

		$('#afgsp_dynamic_options_row').show();

		Object.keys(schema).forEach(function (key) {
			// Skip internal properties like _rounds
			if (key.startsWith('_')) {
				// Handle category headers
				if (key.startsWith('_category_')) {
					var def = schema[key] || {};
					if (def.type === 'category' && def.label) {
						var $categoryHeader = $('<h4 class="afgsp-category-header"></h4>')
							.text(def.label)
							.css({
								'margin-top': '16px',
								'margin-bottom': '8px',
								'font-size': '14px',
								'font-weight': '600',
								'color': '#1d2327'
							});
						$container.append($categoryHeader);
					}
				}
				return;
			}
			
			var def = schema[key] || {};
			var type = def.type || 'string';
			var id = 'afgsp_option_' + key;

			var $fieldWrap = $('<div class="afgsp-field"></div>');
			$fieldWrap.attr('id', 'afgsp_field_' + key);
			var $label;

			var $input;
			if (type === 'bool') {
				$input = $('<input type="checkbox" />').attr('id', id).attr('name', 'options[' + key + ']');
				if (def.disabled) {
					$input.prop('disabled', true).prop('checked', false);
				}
				// Render checkbox to the left of the label text and keep them inline
				$label = $('<label></label>').attr('for', id).append($input).append(' ' + (def.label || key));
				$label.css({ display: 'inline-flex', 'align-items': 'center' });
				$fieldWrap.append($label);
			} else if (type === 'int') {
				$input = $('<input type="number" />').attr('id', id).attr('name', 'options[' + key + ']').addClass('small-text');
				if (typeof def.min !== 'undefined') {
					$input.attr('min', def.min);
				}
				if (typeof def.max !== 'undefined') {
					$input.attr('max', def.max);
				}
				if (typeof def.default !== 'undefined') {
					$input.val(def.default);
				}
				$label = $('<label for="' + id + '"></label>').text(def.label || key);
				$fieldWrap.append($label).append('<br />').append($input);
			} else {
				$input = $('<input type="text" />').attr('id', id).attr('name', 'options[' + key + ']').addClass('regular-text');
				$label = $('<label for="' + id + '"></label>').text(def.label || key);
				$fieldWrap.append($label).append('<br />').append($input);
			}
			if (def.description) {
				$fieldWrap.append($('<p class="description"></p>').text(def.description));
			}
			if (def.disabled && def.premiumNote) {
				$fieldWrap.append($('<p class="description"></p>').text(def.premiumNote));
			}
			$container.append($fieldWrap);
		});

		setupConditionalFields(schema);
	}

	function setupConditionalFields(schema) {
		// Pairs: checkbox -> associated name field
		var pairs = [
			{ check: 'create_calendar', name: 'calendar_name' },
			{ check: 'create_table', name: 'table_name' }
		];

		pairs.forEach(function (p) {
			if (!schema[p.check] || !schema[p.name]) {
				return;
			}
			var checkId = '#afgsp_option_' + p.check;
			var nameFieldWrap = '#afgsp_field_' + p.name;
			var nameInputId = '#afgsp_option_' + p.name;

			function toggleNameField() {
				var checked = $(checkId).is(':checked');
				if (checked) {
					$(nameFieldWrap).show();
					var $input = $(nameInputId);
					if ($input.val().trim() === '') {
						$input.val(buildDefaultEntityName());
					}
				} else {
					$(nameFieldWrap).hide();
				}
			}

			// Initial state
			toggleNameField();
			// Bind change
			$(document).off('change', checkId).on('change', checkId, function () {
				toggleNameField();
			});
		});
	}

	/**
	 * Update the gameweek preview based on selected days.
	 * Implements rolling gameweek concept where gameweek spans from first to last selected day.
	 */
	function updateGameweekPreview() {
		var selectedDays = [];
		$('#afgsp_gameweek_builder input[type="checkbox"]:checked').each(function() {
			selectedDays.push(parseInt($(this).val()));
		});
		
		if (selectedDays.length === 0) {
			$('#afgsp_gameweek_range').text('No days selected');
			return;
		}
		
		var dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
		
		// For rolling gameweek, we need to find the correct starting point
		// The gameweek should start from the day that comes first in the week cycle
		// and end at the day that comes last in the week cycle
		
		// Sort days numerically first
		selectedDays.sort(function(a, b) { return a - b; });
		
		// Check if we have a week boundary crossing (e.g., Fri, Sat, Sun, Mon)
		var hasWeekBoundary = false;
		for (var i = 0; i < selectedDays.length - 1; i++) {
			if (selectedDays[i + 1] - selectedDays[i] > 1) {
				hasWeekBoundary = true;
				break;
			}
		}
		
		var gameweekText;
		if (selectedDays.length === 1) {
			gameweekText = dayNames[selectedDays[0]];
		} else if (hasWeekBoundary) {
			// For week boundary crossing, start from the first day after the gap
			// Find the first day that has a gap before it
			var startIndex = 0;
			for (var i = 1; i < selectedDays.length; i++) {
				if (selectedDays[i] - selectedDays[i-1] > 1) {
					startIndex = i;
					break;
				}
			}
			
			// Reorder the days starting from the identified start point
			var reorderedDays = selectedDays.slice(startIndex).concat(selectedDays.slice(0, startIndex));
			gameweekText = reorderedDays.map(function(day) {
				return dayNames[day];
			}).join(' → ');
		} else {
			// No week boundary crossing, show in normal order
			gameweekText = selectedDays.map(function(day) {
				return dayNames[day];
			}).join(' → ');
		}
		
		$('#afgsp_gameweek_range').text(gameweekText);
	}

	$(document).on('change', '#afgsp_algorithm', function () {
		var slug = $(this).val();
		var map = (window.AFGSP_ADMIN && window.AFGSP_ADMIN.optionsByAlgorithm) || {};
		renderOptions(map[slug]);
		updateEventsDescription();
	});

	// Update events description when teams selection changes
	$(document).on('change', '#afgsp_teams input[type="checkbox"]', function () {
		updateEventsDescription();
	});

	// Update events description when season weeks input changes (for Fixed Week Season algorithm)
	$(document).on('input change', '#afgsp_option_season_weeks', function () {
		updateEventsDescription();
	});

	// Update events description when league or season changes (for free users)
	$(document).on('change', '#afgsp_league, #afgsp_season', function () {
		updateEventsDescription();
		updateTeamsDescriptionForFree();
		// Also refresh default names if name fields are empty and corresponding checkboxes are checked
		var defaultName = buildDefaultEntityName();
		['calendar', 'table'].forEach(function(kind) {
			var check = $('#afgsp_option_create_' + kind);
			var input = $('#afgsp_option_' + kind + '_name');
			if (check.length && input.length && check.is(':checked') && String(input.val() || '').trim() === '') {
				input.val(defaultName);
			}
		});
	});

	// Update gameweek preview when checkboxes change
	$(document).on('change', '#afgsp_gameweek_builder input[type="checkbox"]', function() {
		updateGameweekPreview();
		updateEventsPerSlotInputs();
	});

	// Update events per slot when time slots are added/removed
	$(document).on('DOMNodeInserted DOMNodeRemoved', '#afgsp_time_slots', function() {
		setTimeout(updateEventsPerSlotInputs, 50);
	});

	// Initialize gameweek preview on page load
	$(function() {
		updateGameweekPreview();
	});

	$(function () {
		var initial = $('#afgsp_algorithm').val();
		var map = (window.AFGSP_ADMIN && window.AFGSP_ADMIN.optionsByAlgorithm) || {};
		renderOptions(map[initial]);
		updateEventsDescription();
		updateTeamsDescriptionForFree();
		updateEventsPerSlotInputs();
	});

	// Update events per slot when league/season changes (for free users to get team count)
	$(document).on('change', '#afgsp_league, #afgsp_season', function () {
		var leagueId = $('#afgsp_league').val();
		var seasonId = $('#afgsp_season').val();
		
		if (leagueId && seasonId) {
			$.getJSON((window.AFGSP_ADMIN && window.AFGSP_ADMIN.ajaxUrl) || '', {
				action: 'afgsp_get_teams',
				league: leagueId,
				season: seasonId,
				nonce: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.nonce) || ''
			}).done(function (resp) {
				var teams = (resp && resp.data && Array.isArray(resp.data.teams) && resp.data.teams) || [];
				// Cache the teams count for free users
				if (window.AFGSP_ADMIN) {
					window.AFGSP_ADMIN.teamsCount = teams.length;
				}
				updateEventsPerSlotInputs();
			});
		}
	});

	// Update events per slot when teams selection changes (for premium users)
	$(document).on('change', '#afgsp_teams input[type="checkbox"]', function () {
		updateEventsPerSlotInputs();
	});

	// Intercept form submission to run async, batched generation with a progress bar
	$(document).on('submit', '#afgsp_form', function (ev) {
		var $form = $(this);
		if (!window.AFGSP_ADMIN || !window.AFGSP_ADMIN.ajaxUrl) {
			return; // fallback to normal submit
		}
		ev.preventDefault();

		var $submit = $form.find('input[type="submit"], button[type="submit"]');
		$submit.prop('disabled', true);

		var $progressWrap = $('#afgsp_progress_wrap');
		if ($progressWrap.length === 0) {
			$progressWrap = $('<div id="afgsp_progress_wrap" style="margin-top:12px;"></div>');
			var $barOuter = $('<div id="afgsp_progress_bar" style="width:100%; max-width:520px; height:18px; background:#eee; border:1px solid #ccd0d4; position:relative;"></div>');
			var $barInner = $('<div id="afgsp_progress_fill" style="height:100%; width:0; background:#0073aa;"></div>');
			var $label = $('<div id="afgsp_progress_label" style="margin-top:6px;"></div>').text((window.AFGSP_ADMIN && window.AFGSP_ADMIN.progressText) || 'Generating...');
			$barOuter.append($barInner);
			$progressWrap.append($barOuter).append($label);
			$form.find('p.submit').after($progressWrap);
		}

		function setProgress(created, duplicates, total) {
			var pct = total > 0 ? Math.floor((created / total) * 100) : 0;
			$('#afgsp_progress_fill').css('width', pct + '%');
			var text = created + ' / ' + total;
			if (duplicates > 0) {
				text += ' (' + duplicates + ' duplicate' + (duplicates !== 1 ? 's' : '') + ' skipped)';
			}
			$('#afgsp_progress_label').text(text);
		}

		var data = $form.serializeArray();
		data.push({ name: 'action', value: 'afgsp_start_generation' });
		data.push({ name: 'nonce', value: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.nonce) || '' });

		$.ajax({
			url: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.ajaxUrl) || '',
			type: 'POST',
			data: $.param(data),
			dataType: 'json'
		}).done(function (resp) {
			if (!resp || !resp.success || !resp.data || !resp.data.job_id) {
				$submit.prop('disabled', false);
				$('#afgsp_progress_label').text('Error starting generation');
				return;
			}
			var jobId = resp.data.job_id;
			var total = resp.data.total || 0;
			setProgress(0, 0, total);

			(function processNext() {
				$.ajax({
					url: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.ajaxUrl) || '',
					type: 'POST',
					data: {
						action: 'afgsp_process_generation',
						job_id: jobId,
						nonce: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.nonce) || ''
					},
					dataType: 'json'
				}).done(function (r) {
					if (!r || !r.success || !r.data) {
						$submit.prop('disabled', false);
						$('#afgsp_progress_label').text('Error processing job');
						return;
					}
					var created = r.data.created || 0;
					var duplicates = r.data.duplicates || 0;
					setProgress(created, duplicates, total);
					if (r.data.done) {
						var createdTotal = (typeof r.data.created !== 'undefined') ? Number(r.data.created) : 0;
						var duplicatesTotal = (typeof r.data.duplicates !== 'undefined') ? Number(r.data.duplicates) : 0;
						var gameweeksTotal = (typeof r.data.gameweeks !== 'undefined') ? Number(r.data.gameweeks) : 0;
						var completionMsg = 'Completed: ' + createdTotal + ' fixture' + (createdTotal !== 1 ? 's' : '');
						if (gameweeksTotal > 0) {
							completionMsg += ' in ' + gameweeksTotal + ' gameweek' + (gameweeksTotal !== 1 ? 's' : '');
						}
						if (duplicatesTotal > 0) {
							completionMsg += ' (' + duplicatesTotal + ' duplicate' + (duplicatesTotal !== 1 ? 's' : '') + ' skipped)';
						}
						$('#afgsp_progress_label').text(completionMsg);
						$submit.prop('disabled', false);
					} else {
						setTimeout(processNext, 300);
					}
				}).fail(function (xhr) {
					$submit.prop('disabled', false);
					var errorMsg = 'Request failed';
					
					// Try to extract error message from server response.
					if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
						errorMsg = xhr.responseJSON.data.message;
					} else if (xhr.responseText) {
						// Fallback: try to parse response text.
						try {
							var resp = JSON.parse(xhr.responseText);
							if (resp.data && resp.data.message) {
								errorMsg = resp.data.message;
							}
						} catch (e) {
							// Keep default message.
						}
					}
					
					$('#afgsp_progress_label').text(errorMsg);
				});
			})();
		}).fail(function (xhr) {
			$submit.prop('disabled', false);
			var errorMsg = 'Request failed';
			
			// Try to extract error message from server response.
			if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
				errorMsg = xhr.responseJSON.data.message;
			} else if (xhr.responseText) {
				// Fallback: try to parse response text.
				try {
					var resp = JSON.parse(xhr.responseText);
					if (resp.data && resp.data.message) {
						errorMsg = resp.data.message;
					}
				} catch (e) {
					// Keep default message.
				}
			}
			
			$('#afgsp_progress_label').text(errorMsg);
		});
	});
})(jQuery);


