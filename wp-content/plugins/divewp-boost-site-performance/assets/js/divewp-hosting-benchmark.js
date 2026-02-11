/**
 * DiveWP Hosting Benchmark JavaScript
 *
 * Handles sequential benchmark test execution with timeout detection.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     1.0.4
 */

(function($) {
    'use strict';

    class DiveWPBenchmark {
        constructor() {
            this.isRunning = false;
            this.currentTest = null;
            this.testQueue = [];
            this.results = {};
            this.timeLimit = 60; // Will be updated from server
            this.testDelay = 0; // default: no delay between tests
            this.abortController = null;
            this.sessionId = null;
            this.preloaderTests = [];
            this.totalTests = 0;
            this.preloaderStartTime = null;
            this.preloaderTimerId = null;

            this.bindEvents();
            this.initAjaxHandlers();
            
            // Load saved benchmarks on initialization
            this.loadSavedBenchmarks();
            
            // Load saved settings
            this.loadSavedSettings();
        }

        bindEvents() {
            $('.benchmark-launch-btn').on('click', () => this.startBenchmark());
            $('.benchmark-settings-toggle').on('click', () => this.toggleSettings());
            $('.refresh-saved-benchmarks').on('click', () => this.loadSavedBenchmarks());
            // Debug button removed
            $(document).on('change', '.category-master-toggle', this.handleCategoryToggle.bind(this));
            $(document).on('change', '.test-delay-setting', this.handleDelayChange.bind(this));
            
            // New settings interactions
            $(document).on('click', '.toggle-tests-detail', this.handleToggleTestsDetail.bind(this));
            $(document).on('change', '.sub-test-toggle', this.handleSubTestToggle.bind(this));
            $(document).on('click', '.save-settings', this.handleSaveSettings.bind(this));
            $(document).on('click', '.reset-settings', this.handleResetSettings.bind(this));
        }

        initAjaxHandlers() {
            // Implement AJAX handlers if needed
        }

        toggleSettings() {
            $('.benchmark-settings').slideToggle();
            $('.benchmark-settings-toggle').toggleClass('active');
        }

        handleCategoryToggle(e) {
            const checkbox = $(e.target);
            const category = checkbox.data('category');
            const isChecked = checkbox.is(':checked');
            
            // Toggle all sub-tests for this category
            $(`.sub-test-toggle[data-category="${category}"]`).prop('checked', isChecked);
        }

        handleDelayChange(e) {
            const val = parseInt($(e.target).val(), 10);
            this.testDelay = Number.isFinite(val) ? val * 1000 : 0; // Convert to milliseconds; allow 0
        }

        async startBenchmark() {
            if (this.isRunning) {
                this.showNotice('Benchmark is already running', 'warning');
                return;
            }

            // Get enabled tests
            const enabledTests = this.getEnabledTests();
            if (Object.keys(enabledTests).length === 0) {
                this.showNotice('Please select at least one test to run', 'error');
                return;
            }

            // Build preloader tests list (preserve the same order as execution)
            this.preloaderTests = [];
            const categoryOrderForList = ['performance', 'database', 'resources', 'concurrency'];
            categoryOrderForList.forEach(category => {
                if (enabledTests[category]) {
                    enabledTests[category].forEach(testName => {
                        const $checkbox = $(`.sub-test-toggle[data-category="${category}"][data-test="${testName}"]`);
                        let label = testName;
                        if ($checkbox.length) {
                            const $label = $checkbox.closest('.test-item');
                            const txt = $label.find('.test-name').text().trim();
                            if (txt) { label = txt; }
                        }
                        this.preloaderTests.push({ category, test: testName, label, status: 'pending' });
                    });
                }
            });
            this.totalTests = this.preloaderTests.length;

            // Update UI
            this.updateUI('starting');

            try {
                // Initialize benchmark
                const initResponse = await this.ajaxRequest('divewp_benchmark_init', {
                    enabled_tests: enabledTests
                });

                if (!initResponse.success) {
                    throw new Error(initResponse.data.message || 'Failed to initialize benchmark');
                }

                this.sessionId = initResponse.data.session_id;
                this.timeLimit = initResponse.data.time_limit;

                // Build test queue
                this.buildTestQueue(enabledTests);

                // Start running tests
                this.isRunning = true;
                await this.runNextTest();

            } catch (error) {
                this.handleError(error);
            }
        }

        getEnabledTests() {
            const enabledTests = {};
            
            $('.sub-test-toggle:checked').each(function() {
                const category = $(this).data('category');
                const testName = $(this).data('test');
                
                if (!enabledTests[category]) {
                    enabledTests[category] = [];
                }
                
                enabledTests[category].push(testName);
            });

            // DEBUG: Log enabled tests to console
            console.log('DiveWP Debug - Enabled Tests:', enabledTests);
            
            // DEBUG: Also log all checkboxes for debugging
            console.log('DiveWP Debug - All sub-test checkboxes:');
            $('.sub-test-toggle').each(function() {
                const category = $(this).data('category');
                const testName = $(this).data('test');
                const isChecked = $(this).is(':checked');
                console.log(`  ${category}.${testName}: ${isChecked}`);
            });

            return enabledTests;
        }

        buildTestQueue(enabledTests) {
            this.testQueue = [];
            
            // Order: Performance, Database, Resources, Concurrency
            const categoryOrder = ['performance', 'database', 'resources', 'concurrency'];
            
            categoryOrder.forEach(category => {
                if (enabledTests[category]) {
                    enabledTests[category].forEach(testName => {
                        this.testQueue.push({
                            category: category,
                            test: testName
                        });
                    });
                }
            });
        }

        async runNextTest() {
            if (this.testQueue.length === 0) {
                // All tests completed, finalize benchmark
                await this.finalizeBenchmark();
                return;
            }

            this.currentTest = this.testQueue.shift();
            
            // Update UI to show current test
            this.updateTestStatus(this.currentTest.category, this.currentTest.test, 'running');

            try {
                // Create new AbortController for this test
                this.abortController = new AbortController();
                
                // Set timeout for process kill detection
                const timeoutId = setTimeout(() => {
                    if (this.abortController) {
                        this.abortController.abort();
                    }
                }, this.timeLimit * 1000);

                // Run the test
                const result = await this.runSingleTest(this.currentTest.category, this.currentTest.test);
                
                // Clear timeout
                clearTimeout(timeoutId);
                
                // Store result
                if (!this.results[this.currentTest.category]) {
                    this.results[this.currentTest.category] = {};
                }
                this.results[this.currentTest.category][this.currentTest.test] = result;
                
                // Update UI
                this.updateTestStatus(this.currentTest.category, this.currentTest.test, 'completed');
                
                // Wait before next test
                await this.delay(this.testDelay);
                
                // Run next test
                await this.runNextTest();

            } catch (error) {
                if (error.name === 'AbortError') {
                    // Test was killed/timed out
                    this.handleTestTimeout(this.currentTest.category, this.currentTest.test);
                } else {
                    // Other error
                    this.handleTestError(this.currentTest.category, this.currentTest.test, error);
                }
                
                // Continue with next test after delay
                await this.delay(this.testDelay);
                await this.runNextTest();
            }
        }

        async runSingleTest(category, testName) {
            const response = await fetch(divewp_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'divewp_benchmark_run_test',
                    nonce: divewp_ajax.nonce,
                    category: category,
                    test_name: testName,
                    config: JSON.stringify({})
                }),
                signal: this.abortController.signal
            });

            if (!response.ok) {
                // Check if it's a 500 error
                if (response.status === 500) {
                    throw new Error('Server process killed');
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.data.message || 'Test failed');
            }

            return data.data.result;
        }

        handleTestTimeout(category, testName) {
            // Mark test as killed
            if (!this.results[category]) {
                this.results[category] = {};
            }
            
            this.results[category][testName] = {
                status: 'killed',
                message: 'Test was terminated by hosting provider (timeout or resource limit)',
                test_name: testName
            };

            this.updateTestStatus(category, testName, 'killed');
            this.showNotice(`Test ${testName} was killed by hosting provider`, 'warning');
        }

        handleTestError(category, testName, error) {
            // Mark test as error
            if (!this.results[category]) {
                this.results[category] = {};
            }
            
            this.results[category][testName] = {
                status: 'error',
                message: error.message,
                test_name: testName
            };

            this.updateTestStatus(category, testName, 'error');
            this.showNotice(`Test ${testName} failed: ${error.message}`, 'error');
        }

        async finalizeBenchmark() {
            this.isRunning = false;
            this.updateUI('finalizing');

            try {
                const response = await this.ajaxRequest('divewp_benchmark_finalize', {});
                
                if (response.success) {
                    this.displayResults(response.data);
                    this.updateUI('completed');
                } else {
                    throw new Error(response.data.message || 'Failed to finalize benchmark');
                }
            } catch (error) {
                this.handleError(error);
            }
        }

        displayResults(data) {
            const resultsContainer = $('.divewp-hosting-tab-content.active .benchmark-results');
            resultsContainer.empty();

            // Preflight banner (loopback health)
            const banner = $('<div class="benchmark-status" style="display:none;margin-bottom:10px"></div>');
            resultsContainer.append(banner);
            $.get(divewp_ajax.ajax_url.replace('admin-ajax.php','wp-json/divewp/v1/bench/preflight'))
                .done(res => {
                    if (res && res.ok === false) {
                        banner.show().html('<span class="status-indicator"></span><span class="status-text">Loopback is blocked/slow; concurrency results may be degraded.</span>');
                    }
                })
                .fail(() => {
                    // If preflight fails ignore silently
                });

            // Display overall score
            resultsContainer.append(`
                <div class="overall-score">
                    <h3>Overall Score: ${data.overall_score.toFixed(1)}/100</h3>
                </div>
            `);

            // Use template_data for rich card display (like PoC)
            if (data.template_data) {
                const categoriesContainer = $('<div class="benchmark-category-results"></div>');
                
                Object.keys(data.template_data).forEach(category => {
                    const templateData = data.template_data[category];
                    const card = this.createRichCategoryCard(templateData);
                    categoriesContainer.append(card);
                });

                resultsContainer.append(categoriesContainer);
            } else {
                // Fallback to simple cards if template_data is not available
                const categoriesContainer = $('<div class="benchmark-category-results"></div>');
                
                ['performance', 'database', 'resources', 'concurrency'].forEach(category => {
                    if (data.category_scores[category]) {
                        const categoryData = data.category_scores[category];
                        const card = this.createCategoryCard(category, categoryData);
                        categoriesContainer.append(card);
                    }
                });

                resultsContainer.append(categoriesContainer);
            }
        }

        createRichCategoryCard(data) {
            // DEBUG: Log the data to see what enhanced features are available
            console.log('createRichCategoryCard data:', data);
            
            // Use the exact same structure as PoC hosting-evaluation-card.php template
            let subTestsHtml = '';
            if (data.sub_tests && data.sub_tests.length > 0) {
                const cardId = 'hosting-card-' + data.test_name.toLowerCase().replace(/\s+/g, '-');
                subTestsHtml = `
                <div class="sub-tests-section">
                    <!-- Standard Sub-tests Grid (always visible) -->
                    <div class="sub-tests-grid">
                `;
                
                data.sub_tests.forEach(subTest => {
                    // Remove the status badge (timeout pill) completely - user wants only performance pill
                    
                    // Add performance interpretation for individual sub-tests
                    let performanceInterpretationHtml = '';
                    if (subTest.performance_interpretation) {
                        const pi = subTest.performance_interpretation;
                        
                        // Use existing plugin pill styles
                        let pillClass = 'status-pill ';
                        switch(pi.rating) {
                            case 'excellent':
                                pillClass += 'status-pill-success';
                                break;
                            case 'good':
                                pillClass += 'status-pill-info';
                                break;
                            case 'fair':
                            case 'average':
                                pillClass += 'status-pill-warning';
                                break;
                            case 'poor':
                                pillClass += 'status-pill-danger';
                                break;
                            case 'critical':
                                pillClass += 'status-pill-danger'; // Same as poor - red
                                break;
                            case 'timeout':
                            case 'partial':
                            case 'error':
                                pillClass += 'status-pill-warning';
                                break;
                            case 'unknown':
                                pillClass += 'status-pill-warning'; // Unknown should appear as fair (orange)
                                break;
                            default:
                                pillClass += 'status-pill-warning'; // Default to fair (orange) for unmapped ratings
                        }
                        
                        performanceInterpretationHtml = `
                            <div class="sub-test-performance-interpretation">
                                <div class="performance-context-simple">
                                    ${pi.performance_context ? `<span class="status-pill status-pill-info ops-per-sec-pill">${pi.performance_context}</span>` : ''}
                                </div>
                                ${pi.explanation ? `
                                <p class="performance-explanation-brief">${pi.explanation}</p>` : ''}
                                ${pi.hosting_quality ? `
                                <div class="hosting-quality-brief">
                                    <strong>Hosting Quality:</strong> ${pi.hosting_quality}
                                </div>` : ''}
                            </div>
                        `;
                    }
                    
                    subTestsHtml += `
                        <div class="sub-test-item">
                            <!-- Top Section: Name and Description -->
                            <div class="sub-test-header">
                                <div class="sub-test-name">${subTest.name}</div>
                                <div class="sub-test-description">${subTest.description}</div>
                            </div>
                            
                            <!-- Middle Section: Performance Info -->
                            ${performanceInterpretationHtml}
                            
                            <!-- Bottom Section: Operations (left) and Time (right) -->
                            <div class="sub-test-bottom">
                                <div class="sub-test-operations-left">${subTest.operations}</div>
                                <div class="sub-test-time-right">${subTest.time}</div>
                            </div>
                        </div>
                    `;
                });
                
                subTestsHtml += `
                    </div>
                </div>`;
            }

            const normalizedRating = (rating => {
                if (!rating || rating === 'unknown' || rating === 'skipped') {
                    const s = typeof data.score === 'number' ? data.score : 0;
                    if (s >= 90) return 'excellent';
                    if (s >= 70) return 'good';
                    if (s >= 50) return 'fair';
                    if (s >= 30) return 'poor';
                    return 'critical';
                }
                return rating;
            })(data.rating);

            const currentRating = this.getRatingConfig(normalizedRating);
            const cardId = 'hosting-card-' + data.test_name.toLowerCase().replace(/\s+/g, '-');
            
            // Optional: Score breakdown in a collapsed toggle below details
            let breakdownToggleHtml = '';
            if (data.score_impact_analysis) {
                const breakdownId = cardId + '-breakdown';
                breakdownToggleHtml = `
                <div class="score-breakdown-toggle">
                    <button type="button" class="toggle-details" data-toggle-target="${breakdownId}">
                        <span class="dashicons dashicons-arrow-right-alt2 toggle-icon"></span>
                        <span class="toggle-text">Score Calculation Breakdown</span>
                    </button>
                    <div id="${breakdownId}" class="sub-tests-details">
                        ${this.createScoreBreakdownContent(data)}
                    </div>
                </div>`;
            }

            return `
                <div class="hosting-evaluation-card" id="${cardId}">
                    
                    <!-- Card Header -->
                    <div class="card-header">
                        <div class="card-header-left">
                            <span class="test-icon">${data.icon}</span>
                            <h3 class="card-title">${data.test_name}</h3>
                        </div>
                        <div class="card-header-right">
                            <span class="score rating-${normalizedRating}">${data.score}</span>
                            <span class="rating-pill rating-${normalizedRating}">${currentRating.label}</span>
                        </div>
                    </div>

                    ${subTestsHtml}

                    <!-- Performance Summary (brief insight; breakdown is below as a toggle) -->
                    <div class="performance-summary ${data.rating}">
                        ${data.total_time ? `
                            <div class="performance-time">
                                <span class="performance-time-label">Total Processing Time:</span>
                                <span class="performance-time-value">${data.total_time}</span>
                            </div>
                            ` : ''}
                        
                        ${data.summary ? `
                            <div class="summary">
                                <span class="summary-icon">💡</span>
                                <span class="summary-text">${data.summary}</span>
                            </div>
                            ` : ''}
                    </div>

                    ${breakdownToggleHtml}
                </div>
            `;
        }

        getRatingConfig(rating) {
            const ratingConfig = {
                'excellent': {color: '#10b981', label: 'EXCELLENT'},
                'good': {color: '#3b82f6', label: 'GOOD'},
                'fair': {color: '#f59e0b', label: 'FAIR'},
                'poor': {color: '#ef4444', label: 'POOR'},
                'critical': {color: '#dc2626', label: 'CRITICAL'},
                'skipped': {color: '#6b7280', label: 'SKIPPED'},
                'error': {color: '#dc2626', label: 'ERROR'},
                'unknown': {color: '#6b7280', label: 'UNKNOWN'}
            };
            
            return ratingConfig[rating] || ratingConfig['unknown'];
        }

        getRatingPill(rating) {
            const cls = (rating || 'unknown').toLowerCase();
            return `<div class="rating-pill rating-${cls}">${(rating || 'unknown').toUpperCase()}</div>`;
        }

        createCategoryCard(category, data) {
            const categoryNames = {
                performance: 'E‑commerce Performance',
                database: 'Database Tests',
                resources: 'Server Resources',
                concurrency: 'Concurrency Tests'
            };

            const iconClasses = {
                performance: 'dashicons-performance',
                database: 'dashicons-database',
                resources: 'dashicons-desktop',
                concurrency: 'dashicons-networking'
            };

            return `
                <div class="hosting-evaluation-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <span class="dashicons ${iconClasses[category]}"></span>
                        </div>
                        <div class="card-title-section">
                            <h3 class="card-title">${categoryNames[category]}</h3>
                            <div class="card-score">
                                <span class="score-value">${data.score.toFixed(1)}</span>
                                <span class="score-label">/ 100</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="metric-item">
                            <span class="metric-label">Rating:</span>
                            <span class="metric-value">${data.rating}</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Tests Completed:</span>
                            <span class="metric-value">${data.tests_completed}</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Tests Failed:</span>
                            <span class="metric-value">${data.tests_failed}</span>
                        </div>
                        ${data.interpretation ? `
                        <div class="card-interpretation">
                            <p>${data.interpretation}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        getScoreClass(score) {
            if (score >= 90) return 'excellent';
            if (score >= 75) return 'good';
            if (score >= 60) return 'average';
            if (score >= 40) return 'below-average';
            return 'poor';
        }

        updateUI(status) {
            const button = $('.benchmark-launch-btn');
            const statusEl = $('.benchmark-status');
            const body = $('body');

            switch (status) {
                case 'starting':
                    button.prop('disabled', true).text('Starting...');
                    statusEl.show().html('<span class="status-indicator running"></span> Initializing benchmark...');
                    body.css('overflow', 'hidden');
                    
                    // Show preloader with backdrop (uses webp animation and CSS classes)
                    const pluginUrl = divewp_ajax.plugin_url || '/wp-content/plugins/divewp-boost-site-performance';
                    const preloaderHtml = `
                        <div class="divewp-benchmark-backdrop">
                            <div class="divewp-benchmark-preloader">
                                <div class="preloader-video-container">
                                    <img src="${pluginUrl}/assets/animations/preloader_hosting.webp" alt="Hosting benchmark preloader" class="preloader-media"/>
                                    <div class="video-overlay"></div>
                                </div>
                                <div class="preloader-content">
                                    <h3 class="preloader-title">Running Hosting Benchmark</h3>
                                    <p class="preloader-description">This benchmark measures how your hosting handles your WordPress site. It runs for approximately 6 minutes. The tool is designed to evaluate if your site and its setup are performing optimally on your current server, not to compare different hosting providers.</p>
                                    <div class="preloader-meta">
                                        <div class="stopwatch">Elapsed: <span id="benchmark-stopwatch">00:00</span></div>
                                        <div class="duration-hint">In most cases, the benchmark runs for around 5 minutes.</div>
                                    </div>
                                    <div class="progress-group">
                                        <div class="progress-bar-container">
                                            <div class="benchmark-progress-indeterminate"></div>
                                        </div>
                                        <p class="benchmark-progress-text">Initializing benchmark tests...</p>
                                        <div class="benchmark-queue-summary">
                                            <span id="benchmark-queue-counter">0 / ${this.totalTests} tests</span>
                                        </div>
                                    </div>
                                    <ul id="benchmark-tests-list" class="benchmark-tests-list"></ul>
                                </div>
                            </div>
                        </div>`;
                    $('body').append(preloaderHtml);
                    this.renderPreloaderTestList();
                    this.startStopwatch();
                    break;
                case 'running':
                    button.prop('disabled', true).text('Running...');
                    // Update preloader text if it exists
                    $('.benchmark-progress-text').text('Running benchmark tests...');
                    break;
                case 'finalizing':
                    statusEl.html('<span class="status-indicator running"></span> Calculating results...');
                    // Update preloader text if it exists
                    $('.benchmark-progress-text').text('Calculating results...');
                    break;
                case 'completed':
                    button.prop('disabled', false).text('Launch Benchmark');
                    statusEl.html('<span class="status-indicator completed"></span> Benchmark completed!');
                    // Remove preloader
                    $('.divewp-benchmark-backdrop').fadeOut(500, function() {
                        $(this).remove();
                        body.css('overflow', '');
                    });
                    this.stopStopwatch();
                    $('.benchmark-results')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
                    break;
                case 'error':
                    button.prop('disabled', false).text('Launch Benchmark');
                    statusEl.html('<span class="status-indicator error"></span> Benchmark failed');
                    // Remove preloader
                    $('.divewp-benchmark-backdrop').fadeOut(500, function() {
                        $(this).remove();
                        body.css('overflow', '');
                    });
                    this.stopStopwatch();
                    break;
            }
        }

        updateTestStatus(category, testName, status) {
            const statusEl = $(`.test-status[data-category="${category}"][data-test="${testName}"]`);
            const statusClass = status === 'completed' ? 'success' : status === 'running' ? 'info' : 'error';
            
            statusEl.removeClass('pending success info error').addClass(statusClass);
            
            if (status === 'running') {
                $('.benchmark-status').html(`<span class="status-indicator running"></span> Running ${testName}...`);
                // Update preloader text if it exists
                // Prefer display label if available
                const $item = $(`.benchmark-test-item[data-category="${category}"][data-test="${testName}"]`);
                const label = $item.data('label') || testName;
                $('.benchmark-progress-text').text(`Running ${label}...`);
            }

            // Update the preloader list item state
            const $allItems = $('.benchmark-test-item');
            const $currentItem = $(`.benchmark-test-item[data-category="${category}"][data-test="${testName}"]`);
            if ($currentItem.length) {
                // Clear running state on other items if setting a new running item
                if (status === 'running') {
                    $allItems.removeClass('running');
                    $allItems.find('.status-icon').removeClass('dashicons-update spinner-icon');
                }

                if (status === 'completed') {
                    $currentItem.removeClass('running error').addClass('completed');
                    const $icon = $currentItem.find('.status-icon');
                    $icon.removeClass('dashicons-update spinner-icon dashicons-minus');
                    $icon.addClass('dashicons-yes');
                } else if (status === 'running') {
                    $currentItem.removeClass('completed error').addClass('running');
                    const $icon = $currentItem.find('.status-icon');
                    $icon.removeClass('dashicons-yes dashicons-minus');
                    $icon.addClass('dashicons-update spinner-icon');
                } else if (status === 'error' || status === 'killed') {
                    $currentItem.removeClass('running completed').addClass('error');
                    const $icon = $currentItem.find('.status-icon');
                    $icon.removeClass('dashicons-yes spinner-icon dashicons-minus');
                    $icon.addClass('dashicons-warning');
                }

                this.updatePreloaderQueueCounter();
            }
        }

        // Render the test list inside the preloader
        renderPreloaderTestList() {
            const $list = $('#benchmark-tests-list');
            if (!$list.length) { return; }
            $list.empty();
            this.preloaderTests.forEach(item => {
                const li = $(`
                    <li class="benchmark-test-item pending" data-category="${item.category}" data-test="${item.test}" data-label="${item.label}">
                        <span class="test-label">${item.label}</span>
                        <span class="status">
                            <span class="dashicons dashicons-minus status-icon"></span>
                        </span>
                    </li>
                `);
                $list.append(li);
            });
            this.updatePreloaderQueueCounter();
        }

        updatePreloaderQueueCounter() {
            const total = this.totalTests || $('.benchmark-test-item').length || 0;
            const completed = $('.benchmark-test-item.completed').length;
            $('#benchmark-queue-counter').text(`${completed} / ${total} tests`);
        }

        startStopwatch() {
            this.preloaderStartTime = Date.now();
            const update = () => {
                const elapsedMs = Date.now() - this.preloaderStartTime;
                const totalSeconds = Math.floor(elapsedMs / 1000);
                const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
                const seconds = String(totalSeconds % 60).padStart(2, '0');
                $('#benchmark-stopwatch').text(`${minutes}:${seconds}`);
            };
            update();
            this.preloaderTimerId = setInterval(update, 1000);
        }

        stopStopwatch() {
            if (this.preloaderTimerId) {
                clearInterval(this.preloaderTimerId);
                this.preloaderTimerId = null;
            }
            this.preloaderStartTime = null;
        }

        async ajaxRequest(action, data) {
            return $.ajax({
                url: divewp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    nonce: divewp_ajax.nonce,
                    ...data
                }
            });
        }

        delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        showNotice(message, type = 'info') {
            const notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
            $('.benchmark-container').prepend(notice);
            
            setTimeout(() => {
                notice.fadeOut(() => notice.remove());
            }, 5000);
        }

        handleError(error) {
            console.error('Benchmark error:', error);
            this.isRunning = false;
            this.updateUI('error');
            this.showNotice(error.message || 'An unexpected error occurred', 'error');
        }

        // Saved Benchmarks Functionality
        async loadSavedBenchmarks() {
            // Handle header refresh button loading state (if present)
            const $refreshBtn = $('#divewp-refresh-benchmarks');
            let originalRefreshHtml = null;
            try {
                if ($refreshBtn.length) {
                    originalRefreshHtml = $refreshBtn.html();
                    $refreshBtn.prop('disabled', true)
                        .addClass('loading')
                        .html('<span class="dashicons dashicons-update spinning"></span> Refreshing…');
                }
                $('.loading-saved-benchmarks').show();
                $('.saved-benchmarks-list .benchmark-item').remove();
                
                const response = await this.ajaxRequest('divewp_get_saved_benchmarks', {
                    limit: 10
                });
                
                if (response.success && response.data.benchmarks) {
                    this.displaySavedBenchmarks(response.data.benchmarks);
                } else {
                    $('.saved-benchmarks-list').html('<p class="no-saved-benchmarks">' + 
                        'No previous benchmark results found.</p>');
                }
            } catch (error) {
                console.error('Failed to load saved benchmarks:', error);
                $('.saved-benchmarks-list').html('<p class="error-loading-benchmarks">' + 
                    'Error loading saved benchmarks.</p>');
            } finally {
                $('.loading-saved-benchmarks').hide();
                if ($refreshBtn.length) {
                    $refreshBtn.prop('disabled', false)
                        .removeClass('loading')
                        .html(originalRefreshHtml || 'Refresh');
                }
            }
        }

        displaySavedBenchmarks(benchmarks) {
            const container = $('.saved-benchmarks-list');
            container.empty();
            
            if (benchmarks.length === 0) {
                container.html('<p class="no-saved-benchmarks">No previous benchmark results found.</p>');
                return;
            }
            
            // Create list table structure
            const listTable = $(`
                <div class="saved-benchmarks-list-table">
                    <div class="benchmarks-table-header">
                        <div class="col-date">Date & Time</div>
                        <div class="col-score">Overall Score</div>
                        <div class="col-breakdown">Category Scores</div>
                        <div class="col-actions">Actions</div>
                    </div>
                    <div class="benchmarks-table-body"></div>
                </div>
            `);
            
            const tableBody = listTable.find('.benchmarks-table-body');
            
            benchmarks.forEach(benchmark => {
                const date = new Date(benchmark.test_date);
                const formattedDate = date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                const formattedTime = date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const overallScore = parseFloat(benchmark.overall_score);
                const ratingConfig = this.getRatingConfig(this.getScoreRating(overallScore));
                
                const row = $(`
                    <div class="benchmark-table-row" data-id="${benchmark.id}">
                        <div class="col-date">
                            <div class="benchmark-date-primary">${formattedDate}</div>
                            <div class="benchmark-time-secondary">${formattedTime}</div>
                        </div>
                        <div class="col-score">
                            <div class="benchmark-overall-score" style="color: ${ratingConfig.color};">
                                ${overallScore.toFixed(1)}/100
                            </div>
                            <div class="benchmark-rating" style="color: ${ratingConfig.color};">
                                ${ratingConfig.label}
                            </div>
                        </div>
                        <div class="col-breakdown">
                            <div class="score-breakdown-list">
                                <span class="score-item">P: ${parseFloat(benchmark.performance_score).toFixed(0)}</span>
                                <span class="score-item">D: ${parseFloat(benchmark.database_score).toFixed(0)}</span>
                                <span class="score-item">R: ${parseFloat(benchmark.resources_score).toFixed(0)}</span>
                                <span class="score-item">C: ${parseFloat(benchmark.concurrency_score).toFixed(0)}</span>
                            </div>
                        </div>
                        <div class="col-actions">
                            <button class="load-benchmark-btn button button-secondary button-small" 
                                    data-id="${benchmark.id}" title="Load Results">
                                <span class="dashicons dashicons-visibility"></span>
                                Load
                            </button>
                            <button class="delete-benchmark-btn button button-secondary button-small" 
                                    data-id="${benchmark.id}" title="Delete Benchmark">
                                <span class="dashicons dashicons-trash"></span>
                                Delete
                            </button>
                        </div>
                    </div>
                `);
                
                tableBody.append(row);
            });
            
            container.append(listTable);
            
            // Bind action buttons
            $('.load-benchmark-btn').on('click', (e) => {
                const benchmarkId = $(e.currentTarget).data('id');
                this.loadSavedBenchmark(benchmarkId);
            });
            
            $('.delete-benchmark-btn').on('click', (e) => {
                const benchmarkId = $(e.currentTarget).data('id');
                this.deleteSavedBenchmark(benchmarkId);
            });

            // Header controls
            $('.refresh-saved-benchmarks').off('click').on('click', () => this.loadSavedBenchmarks());
            $('#divewp-delete-all-benchmarks').off('click').on('click', () => this.deleteAllSavedBenchmarks());
        }

        async loadSavedBenchmark(benchmarkId) {
            try {
                const $visibleContainer = $('.divewp-hosting-tab-content.active .benchmark-results');
                if ($visibleContainer.length) {
                    $visibleContainer.html(
                        '<div class="loading-indicator prominent" role="status" aria-live="polite">'
                        + '<div class="divewp-loader" aria-hidden="true"></div>'
                        + '<div class="loading-text">Loading saved benchmark…</div>'
                        + '<div class="progress-bar-container"><div class="benchmark-progress-indeterminate"></div></div>'
                        + '</div>'
                    );
                    // Scroll immediately so user sees the loading state on slow hosts
                    const elBefore = $visibleContainer[0];
                    if (elBefore) { elBefore.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                }
                
                const response = await this.ajaxRequest('divewp_load_saved_benchmark', {
                    benchmark_id: benchmarkId
                });
                
                if (response.success) {
                    // Add indicator that this is a saved benchmark
                    const data = response.data;
                    data.is_saved_benchmark = true;
                    
                    this.displayResults(data);
                    
                    // Ensure results are in view (secondary scroll safeguard)
                    const el = $('.divewp-hosting-tab-content.active .benchmark-results')[0];
                    if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                } else {
                    throw new Error(response.data.message || 'Failed to load saved benchmark');
                }
            } catch (error) {
                console.error('Failed to load saved benchmark:', error);
                const $visibleContainer = $('.divewp-hosting-tab-content.active .benchmark-results');
                if ($visibleContainer.length) {
                    $visibleContainer.html('<div class="error-indicator">Error loading saved benchmark: ' + error.message + '</div>');
                }
            }
        }

        async deleteSavedBenchmark(benchmarkId) {
            // Show confirmation dialog
            if (!confirm('Are you sure you want to delete this benchmark result? This action cannot be undone.')) {
                return;
            }

            try {
                const response = await this.ajaxRequest('divewp_delete_saved_benchmark', {
                    benchmark_id: benchmarkId
                });
                
                if (response.success) {
                    // Remove the row from the UI
                    $(`.benchmark-table-row[data-id="${benchmarkId}"]`).fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if there are any remaining benchmarks
                        if ($('.benchmark-table-row').length === 0) {
                            $('.saved-benchmarks-list').html('<p class="no-saved-benchmarks">No previous benchmark results found.</p>');
                        }
                    });
                    
                    this.showNotice('Benchmark deleted successfully', 'success');
                } else {
                    throw new Error(response.data.message || 'Failed to delete benchmark');
                }
            } catch (error) {
                console.error('Failed to delete saved benchmark:', error);
                this.showNotice('Error deleting benchmark: ' + error.message, 'error');
            }
        }

        async deleteAllSavedBenchmarks() {
            if (!confirm('Are you sure you want to delete ALL benchmark results? This cannot be undone.')) {
                return;
            }
            try {
                const $deleteBtn = $('#divewp-delete-all-benchmarks');
                const $refreshBtn = $('#divewp-refresh-benchmarks');
                const $list = $('.saved-benchmarks-list');

                // Visual loading feedback in header buttons
                const originalLabel = $deleteBtn.html();
                $deleteBtn.prop('disabled', true).addClass('loading').html('<span class="dashicons dashicons-update spinning"></span> Deleting…');
                $refreshBtn.prop('disabled', true);

                // Prominent loader in list area
                if ($list.length) {
                    $list.html('<div class="loading-indicator prominent" role="status" aria-live="polite">'
                        + '<div class="divewp-loader" aria-hidden="true"></div>'
                        + '<div class="loading-text">Deleting all benchmark results…</div>'
                        + '<div class="progress-bar-container"><div class="benchmark-progress-indeterminate"></div></div>'
                        + '</div>');
                    const section = $('.saved-benchmarks-section')[0];
                    if (section) { section.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                }

                const response = await this.ajaxRequest('divewp_delete_all_benchmarks', {});
                if (response.success) {
                    // Refresh list
                    await this.loadSavedBenchmarks();
                    // Clear any visible results
                    const $visibleContainer = $('.divewp-hosting-tab-content.active .benchmark-results');
                    if ($visibleContainer.length) {
                        $visibleContainer.html('<div class="results-placeholder"><p>All benchmark results were deleted.</p></div>');
                    }
                } else {
                    this.showNotice(response.data?.message || 'Failed to delete all benchmarks', 'error');
                }
            } catch (error) {
                this.showNotice('Error deleting all benchmarks: ' + error.message, 'error');
            } finally {
                const $deleteBtn = $('#divewp-delete-all-benchmarks');
                const $refreshBtn = $('#divewp-refresh-benchmarks');
                // Restore buttons
                $deleteBtn.prop('disabled', false).removeClass('loading').html('Delete All');
                $refreshBtn.prop('disabled', false);
            }
        }

        getScoreRating(score) {
            if (score >= 90) return 'excellent';
            if (score >= 75) return 'good';
            if (score >= 60) return 'fair';
            if (score >= 40) return 'poor';
            return 'critical';
        }

        // New Settings Interaction Methods
        handleToggleTestsDetail(e) {
            e.preventDefault();
            const button = $(e.currentTarget);
            const category = button.data('category');
            const detailsDiv = $(`#tests-detail-${category}`);
            const icon = button.find('.dashicons');
            
            if (detailsDiv.is(':visible')) {
                detailsDiv.slideUp(200);
                button.removeClass('expanded');
                button.find('span:not(.dashicons)').text('Show Details');
                icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
            } else {
                detailsDiv.slideDown(200);
                button.addClass('expanded');
                button.find('span:not(.dashicons)').text('Hide Details');
                icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
            }
        }

        handleSubTestToggle(e) {
            const checkbox = $(e.currentTarget);
            const category = checkbox.data('category');
            const categoryToggle = $(`.category-master-toggle[data-category="${category}"]`);
            
            // Check if all sub-tests in this category are unchecked
            const allSubTests = $(`.sub-test-toggle[data-category="${category}"]`);
            const checkedSubTests = $(`.sub-test-toggle[data-category="${category}"]:checked`);
            
            // Update master toggle based on sub-tests status
            if (checkedSubTests.length === 0) {
                categoryToggle.prop('checked', false);
            } else if (checkedSubTests.length === allSubTests.length) {
                categoryToggle.prop('checked', true);
            }
        }

        handleSaveSettings(e) {
            e.preventDefault();
            
            // Collect all settings
            const rawDelay = parseInt($('.test-delay-setting').val(), 10);
            const settings = {
                testDelay: Number.isFinite(rawDelay) ? rawDelay : 0,
                selectedCategories: {},
                selectedTests: {}
            };
            
            // Collect category selections
            $('.category-master-toggle').each(function() {
                const category = $(this).data('category');
                settings.selectedCategories[category] = $(this).is(':checked');
            });
            
            // Collect individual test selections
            $('.sub-test-toggle').each(function() {
                const category = $(this).data('category');
                const test = $(this).data('test');
                
                if (!settings.selectedTests[category]) {
                    settings.selectedTests[category] = {};
                }
                settings.selectedTests[category][test] = $(this).is(':checked');
            });
            
            // Save to localStorage for persistence
            localStorage.setItem('divewp_benchmark_settings', JSON.stringify(settings));
            
            // Update the benchmark instance
            this.testDelay = settings.testDelay * 1000; // Convert to milliseconds
            
            // Show success message
            this.showNotice('Settings saved successfully!', 'success');
            
            // Close settings panel
            this.toggleSettings();
        }

        handleResetSettings(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to reset all settings to defaults? This cannot be undone.')) {
                // Reset form values
                $('.test-delay-setting').val(0);
                
                // Reset category toggles (only performance should be checked)
                $('.category-master-toggle').each(function() {
                    const category = $(this).data('category');
                    const shouldBeChecked = category === 'performance';
                    $(this).prop('checked', shouldBeChecked);
                });
                
                // Reset all sub-test toggles for available categories
                $('.sub-test-toggle').each(function() {
                    const category = $(this).data('category');
                    const shouldBeChecked = category === 'performance';
                    $(this).prop('checked', shouldBeChecked);
                });
                
                // Clear localStorage
                localStorage.removeItem('divewp_benchmark_settings');
                
                // Reset instance values
                this.testDelay = 0;
                
                this.showNotice('Settings reset to defaults', 'success');
            }
        }

        // Load saved settings on initialization
        loadSavedSettings() {
            const savedSettings = localStorage.getItem('divewp_benchmark_settings');
            if (savedSettings) {
                try {
                    const settings = JSON.parse(savedSettings);
                    
                    // Apply saved settings
                    if (Object.prototype.hasOwnProperty.call(settings, 'testDelay')) {
                        $('.test-delay-setting').val(settings.testDelay);
                        this.testDelay = (Number.isFinite(parseInt(settings.testDelay, 10)) ? parseInt(settings.testDelay, 10) : 0) * 1000;
                    }
                    
                    // performanceMode removed
                    
                    if (settings.selectedCategories) {
                        Object.keys(settings.selectedCategories).forEach(category => {
                            $(`.category-master-toggle[data-category="${category}"]`)
                                .prop('checked', settings.selectedCategories[category]);
                        });
                    }
                    
                    if (settings.selectedTests) {
                        Object.keys(settings.selectedTests).forEach(category => {
                            Object.keys(settings.selectedTests[category]).forEach(test => {
                                $(`.sub-test-toggle[data-category="${category}"][data-test="${test}"]`)
                                    .prop('checked', settings.selectedTests[category][test]);
                            });
                        });
                    }
                } catch (error) {
                    console.warn('Failed to load saved settings:', error);
                }
            }
        }

        // Debug helpers removed
        formatTransients(transients) {
            if (!transients || Object.keys(transients).length === 0) {
                return '<p class="status-warning">No benchmark transients found</p>';
            }
            
            let html = '<table class="debug-table"><tr><th>Transient Key</th><th>Status</th><th>Score</th><th>Message</th></tr>';
            
            Object.keys(transients).forEach(key => {
                const data = transients[key];
                const status = data?.status || 'unknown';
                const score = data?.score || 0;
                const message = data?.message || data?.interpretation || 'No message';
                
                const statusClass = status === 'completed' ? 'status-ok' : 
                                   status === 'error' ? 'status-error' : 'status-warning';
                
                html += `<tr>
                    <td>${key}</td>
                    <td class="${statusClass}">${status}</td>
                    <td>${score}</td>
                    <td>${message}</td>
                </tr>`;
            });
            
            html += '</table>';
            return html;
        }

        formatTestFiles(files) {
            let html = '<table class="debug-table"><tr><th>File</th><th>Exists</th><th>Readable</th><th>Class</th><th>Size</th></tr>';
            
            Object.keys(files).forEach(filename => {
                const file = files[filename];
                const existsClass = file.exists ? 'status-ok' : 'status-error';
                const readableClass = file.readable ? 'status-ok' : 'status-error';
                
                html += `<tr>
                    <td>${filename}</td>
                    <td class="${existsClass}">${file.exists ? 'Yes' : 'No'}</td>
                    <td class="${readableClass}">${file.readable ? 'Yes' : 'No'}</td>
                    <td>${file.class}</td>
                    <td>${file.size} bytes</td>
                </tr>`;
            });
            
            html += '</table>';
            return html;
        }

        formatDatabaseStatus(status) {
            const connectionClass = status.connection === 'OK' ? 'status-ok' : 'status-error';
            
            return `
                <table class="debug-table">
                    <tr><th>Property</th><th>Value</th></tr>
                    <tr><td>Connection</td><td class="${connectionClass}">${status.connection}</td></tr>
                    <tr><td>Test Query</td><td>${status.test_query || 'N/A'}</td></tr>
                    <tr><td>Last Error</td><td>${status.last_error || 'None'}</td></tr>
                    <tr><td>Error Message</td><td>${status.error || 'None'}</td></tr>
                </table>
            `;
        }

        formatPhpSettings(settings) {
            return `
                <table class="debug-table">
                    <tr><th>Setting</th><th>Value</th></tr>
                    <tr><td>max_execution_time</td><td>${settings.max_execution_time}</td></tr>
                    <tr><td>memory_limit</td><td>${settings.memory_limit}</td></tr>
                    <tr><td>error_reporting</td><td>${settings.error_reporting}</td></tr>
                    <tr><td>log_errors</td><td>${settings.log_errors}</td></tr>
                    <tr><td>error_log</td><td>${settings.error_log || 'Not set'}</td></tr>
                </table>
            `;
        }

        formatSimpleTest(testResult) {
            if (testResult.error) {
                return `<p class="status-error">Error: ${testResult.error}</p>`;
            }
            
            if (testResult.insert_operations) {
                const result = testResult.insert_operations;
                const statusClass = result.status === 'completed' ? 'status-ok' : 'status-error';
                
                return `
                    <table class="debug-table">
                        <tr><th>Property</th><th>Value</th></tr>
                        <tr><td>Status</td><td class="${statusClass}">${result.status}</td></tr>
                        <tr><td>Score</td><td>${result.score || 0}</td></tr>
                        <tr><td>Rating</td><td>${result.rating || 'unknown'}</td></tr>
                        <tr><td>Operations/sec</td><td>${result.operations_per_second || 0}</td></tr>
                        <tr><td>Total Time</td><td>${result.total_time || 0}s</td></tr>
                        <tr><td>Message</td><td>${result.message || result.interpretation || 'None'}</td></tr>
                    </table>
                `;
            }
            
            return '<p class="status-warning">No test result available</p>';
        }

        formatRecentErrors(errors) {
            if (!errors || errors.length === 0) {
                return '<p class="status-ok">No recent DiveWP errors found</p>';
            }
            
            let html = '<div class="code-block">';
            errors.forEach(error => {
                html += error + '\n';
            });
            html += '</div>';
            
            return html;
        }

        getRatingIcon(rating) {
            const ratingIcons = {
                'excellent': '⚡',
                'good': '✅', 
                'average': '🟡',
                'fair': '🟡',
                'poor': '⚠️',
                'critical': '⚠️', // Same as poor
                'timeout': '⏱️',
                'partial': '⚠️',
                'error': '⚠️', // Same as poor
                'unknown': '❓'
            };
            return ratingIcons[rating] || ratingIcons['unknown'];
        }

        getComponentRatingClass(rating) {
            switch(rating) {
                case 'excellent':
                    return 'component-excellent';
                case 'good':
                    return 'component-good';
                case 'fair':
                    return 'component-fair';
                case 'poor':
                    return 'component-poor';
                case 'critical':
                    return 'component-critical';
                default:
                    return 'component-unknown';
            }
        }

        createScoreBreakdownContent(data) {
            let html = `
                <div class="score-breakdown-content">
                    <h4>📊 Score Calculation Breakdown</h4>
                    <p class="breakdown-intro">Your overall score of <strong>${data.score}</strong> is calculated from individual test results with different weights:</p>
                    <div class="contributions-grid">
            `;

            // Combine positive and negative contributions
            const allContributions = [
                ...(data.score_impact_analysis.positive_contributions || []),
                ...(data.score_impact_analysis.negative_contributions || [])
            ];

            allContributions.forEach(contribution => {
                const weightPercent = Math.round(contribution.weight * 100);
                const statusIcon = this.getContributionIcon(contribution.impact_type, contribution.status);

                html += `
                    <div class="contribution-row">
                        <div class="test-info">
                            <span class="test-name">${contribution.test_name}</span>
                            <span class="test-weight">${weightPercent}% weight</span>
                        </div>
                        <div class="test-result">
                            <span class="result-icon">${statusIcon}</span>
                            <span class="result-points">${contribution.contribution_points} points</span>
                        </div>
                    </div>
                `;

                // Add score factors explanation for performance tests
                if (contribution.score_factors) {
                    html += `
                        <div class="score-factors-explanation">
                            <div class="factors-title">📊 How this score was calculated:</div>
                            <div class="factors-text">${contribution.score_factors}</div>
                        </div>
                    `;
                }
            });

            html += `
                    </div>
                    <div class="calculation-total">
                        <strong>💡 Combined weighted score: ${data.score} points</strong>
                    </div>
                </div>
            `;

            return html;
        }
        
        getContributionIcon(impactType, status) {
            if (status === 'timeout') return '⏱️';
            if (status === 'partial') return '⚠️';
            if (status === 'error') return '❌';
            
            switch (impactType) {
                case 'positive': return '⚡';
                case 'neutral': return '✅';
                case 'negative': return '🔴';
                default: return '❓';
            }
        }
    }

    // Global toggle function (like PoC)
    // Delegated click handler for any toggle-details buttons (works in Benchmark and Previous Results)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('button.toggle-details[data-toggle-target]');
        if (!btn) return;
        const targetId = btn.getAttribute('data-toggle-target');
        if (!targetId) return;
        const section = document.getElementById(targetId);
        const icon = btn.querySelector('.toggle-icon');
        const text = btn.querySelector('.toggle-text');
        if (!section || !text) return;
        const isHidden = section.style.display === 'none' || section.style.display === '';
        if (isHidden) {
            section.style.display = 'block';
            text.textContent = text.textContent.replace('Show', 'Hide');
            if (icon) icon.classList.add('rotated');
        } else {
            section.style.display = 'none';
            text.textContent = text.textContent.replace('Hide', 'Show');
            if (icon) icon.classList.remove('rotated');
        }
    }, true);

    // Initialize on document ready
    $(document).ready(function() {
        const benchmark = new DiveWPBenchmark();
        
        // Add CSS for rotated icon
        if (!document.getElementById('divewp-toggle-styles')) {
            const style = document.createElement('style');
            style.id = 'divewp-toggle-styles';
            style.textContent = `
                .toggle-icon.rotated {
                    transform: rotate(90deg);
                }
                .toggle-icon {
                    transition: transform 0.3s ease;
                }
            `;
            document.head.appendChild(style);
        }
    });

})(jQuery); 