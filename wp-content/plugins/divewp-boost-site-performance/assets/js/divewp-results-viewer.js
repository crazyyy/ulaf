(function($) {
    'use strict';

    $(document).ready(function() {
        // Add a button to fetch and display results
        if ($('#divewp-show-results').length === 0) {
            var btn = $('<button id="divewp-show-results" class="button button-primary" style="margin-bottom:15px;">Show Latest Resource Test Results</button>');
            $('#wpbody-content').prepend(btn);
        }
        if ($('#divewp-results-container').length === 0) {
            var container = $('<div id="divewp-results-container" style="margin-top:20px;"></div>');
            $('#wpbody-content').prepend(container);
        }
        
        // Handle test configuration panel toggle
        $(document).on('click', '#toggle-test-config', function() {
            var $panel = $('#test-config-panel');
            var $button = $(this);
            
            if ($panel.is(':visible')) {
                $panel.slideUp();
                $button.text('Show Advanced Options');
            } else {
                $panel.slideDown();
                $button.text('Hide Advanced Options');
            }
        });
        
        // Handle preset buttons
        $(document).on('click', '.test-preset', function() {
            var preset = $(this).data('preset');
            var checkboxes = $('input[name="enabled_tests[]"]');
            
            switch(preset) {
                case 'safe':
                    // Enable only low and medium risk tests
                    checkboxes.each(function() {
                        var value = $(this).val();
                        var isSafe = ['test_shortcode_processing', 'test_hook_execution', 'test_security_functions', 
                                     'finalize_test_results', 'run_wp_tests', 'run_io_test', 'run_network_test', 
                                     'test_transient_operations'].includes(value);
                        $(this).prop('checked', isSafe);
                    });
                    break;
                    
                case 'standard':
                    // Enable all except critical risk tests
                    checkboxes.each(function() {
                        var value = $(this).val();
                        var isCritical = ['test_memory_allocation_limits'].includes(value);
                        $(this).prop('checked', !isCritical);
                    });
                    break;
                    
                case 'all':
                    checkboxes.prop('checked', true);
                    break;
                    
                case 'none':
                    checkboxes.prop('checked', false);
                    break;
            }
        });

        /**
         * Renders the complete hosting evaluation report
         * @param {object} data The complete data object from the AJAX response
         */
        function renderResults(data) {
            const container = $('#divewp-results-container');
            
            // Helper to get rating class
            const getRatingClass = (rating) => rating ? rating.toLowerCase() : 'skipped';

            // Overall Score
            let overallHtml = `
                <div class="hosting-score-overview">
                    <h5>Overall Hosting Score</h5>
                    <div class="score-meter">
                        <div class="score-value ${getRatingClass(data.rating)}">${data.overall_score}</div>
                        <div class="score-bar"><div class="score-fill" style="width: ${data.overall_score}%;"></div></div>
                        <div class="score-label ${getRatingClass(data.rating)}">${data.rating}</div>
                    </div>
                </div>
            `;

            // Test cards
            let testsHtml = '<div class="test-results-grid">';

            // Performance Card
            const perf = data.tests.performance;
            testsHtml += `
                <div class="test-card">
                    <div class="test-header"><span class="dashicons dashicons-dashboard"></span><h6>E‑commerce Performance</h6></div>
                    <div class="test-score ${getRatingClass(perf.rating)}">
                        <span class="score">${perf.score}</span>
                        <span class="rating">${perf.rating}</span>
                    </div>
                    <div class="test-metrics">
                        <p><strong>Total Time:</strong> ${parseFloat(perf.total_time).toFixed(2)}ms</p>
                        <div class="interpretation">${perf.interpretation}</div>
                    </div>
                </div>`;

            // Resources Card
            const res = data.tests.resources;
            testsHtml += `
                <div class="test-card">
                    <div class="test-header"><span class="dashicons dashicons-server"></span><h6>Resources</h6></div>
                    <div class="test-score ${getRatingClass(res.rating)}">
                        <span class="score">${res.overall_score}</span>
                        <span class="rating">${res.rating}</span>
                    </div>
                    <div class="test-metrics">
                         <div class="sub-scores">
                            <p><strong>CPU:</strong> ${res.cpu_score}</p>
                            <p><strong>Memory:</strong> ${res.memory_score}</p>
                            <p><strong>I/O:</strong> ${res.io_score}</p>
                            <p><strong>Network:</strong> ${res.network_score}</p>
                        </div>
                        <div class="interpretation">${res.interpretation}</div>
                    </div>
                </div>`;
            
            // Database Card
            const dbLegacy = data.tests.database_legacy;
            testsHtml += `
                <div class="test-card">
                    <div class="test-header"><span class="dashicons dashicons-database"></span><h6>Database</h6></div>
                    <div class="test-score ${getRatingClass(dbLegacy.rating)}">
                        <span class="score">${dbLegacy.score}</span>
                        <span class="rating">${dbLegacy.rating}</span>
                    </div>
                    <div class="test-metrics">
                        <p><strong>Total Time:</strong> ${dbLegacy.total_time !== undefined ? parseFloat(dbLegacy.total_time).toFixed(2) : 'N/A'}ms</p>
                        <div class="interpretation">${dbLegacy.interpretation || ''}</div>
                    </div>
                </div>`;

            // Concurrency Card
            const conc = data.tests.concurrency;
            testsHtml += `
                <div class="test-card">
                    <div class="test-header"><span class="dashicons dashicons-groups"></span><h6>Concurrency</h6></div>
                    <div class="test-score ${getRatingClass(conc.rating)}">
                        <span class="score">${conc.score}</span>
                        <span class="rating">${conc.rating}</span>
                    </div>
                     <div class="test-metrics">
                        <p><strong>Scaling:</strong> ${conc.scaling_factor !== undefined ? parseFloat(conc.scaling_factor).toFixed(2) : 'N/A'}x</p>
                        <div class="interpretation">${conc.interpretation || ''}</div>
                    </div>
                </div>`;

            testsHtml += '</div>'; // Close grid

            // Recommendation section
            const rec = data.recommendation;
            let recommendationHtml = `
                <div class="hosting-recommendation">
                    <h5>Recommendation & Analysis</h5>
                    <div class="verdict">${rec.verdict}</div>
                    <div class="hosting-type">${rec.performance_profile.title}: ${rec.performance_profile.description}</div>`;
            
            if(rec.strengths && rec.strengths.length) {
                recommendationHtml += '<h6>Strengths</h6><ul>';
                rec.strengths.forEach(item => { recommendationHtml += `<li>${item}</li>`; });
                recommendationHtml += '</ul>';
            }

            if(rec.bottlenecks && rec.bottlenecks.length) {
                recommendationHtml += '<h6>Bottlenecks</h6><ul>';
                rec.bottlenecks.forEach(item => { recommendationHtml += `<li>${item}</li>`; });
                recommendationHtml += '</ul>';
            }
            
            if(rec.warnings && rec.warnings.length) {
                recommendationHtml += '<div class="hosting-warnings"><div class="warning-title">Critical Warnings</div><ul class="warning-list">';
                rec.warnings.forEach(item => { recommendationHtml += `<li>${item}</li>`; });
                recommendationHtml += '</ul></div>';
            }

            recommendationHtml += '</div>';

            container.html(overallHtml + testsHtml + recommendationHtml);
        }

        $('#divewp-show-results').on('click', function() {
            var $button = $(this);
            var $container = $('#divewp-results-container');
            var $body = $('body');
            
            // Collect test configuration from checkboxes
            var enabledTests = [];
            $('input[name="enabled_tests[]"]:checked').each(function() {
                enabledTests.push($(this).val());
            });
            
            // Collect advanced settings
            var skipNetworkRequests = $('input[name="skip_network_requests"]').is(':checked');
            
            $body.css('overflow', 'hidden');

            // Show progress indicator with test count - Full width video preloader
            const testCount = enabledTests.length;
            const pluginUrl = divewpHosting.plugin_url || '/wp-content/plugins/divewp-boost-site-performance';
            const progressHtml = `
                <div class="divewp-progress-container" style="background: #fff; border: 2px solid #e5e7eb; border-radius: 16px; padding: 0; margin: 40px auto; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); overflow: hidden; max-width: 600px; width: 90%;">
                    <div class="preloader-video-container" style="width: 100%; height: 200px; position: relative; display: flex; justify-content: center; align-items: center; border-radius: 14px 14px 0 0; overflow: hidden;">
                        <img src="${pluginUrl}/assets/animations/preloader_hosting.webp" alt="Hosting preloader" style="width: 100%; height: 100%; object-fit: cover; display:block;"/>
                        <div class="video-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.3);"></div>
                    </div>
                    <div class="preloader-content" style="padding: 30px; text-align: center; background: #fff;">
                        <h3 style="margin: 0 0 16px 0; color: #1f2937; font-size: 22px; font-weight: 600;">Running Hosting Evaluation</h3>
                        <div style="margin-bottom: 16px;">
                            <div class="progress-bar-container" style="background: #f3f4f6; border-radius: 8px; height: 8px; overflow: hidden; margin-bottom: 12px;">
                                <div class="progress-animation-indeterminate"></div>
                            </div>
                            <p style="margin: 0; color: #6b7280; font-size: 14px;">Testing ${testCount} components - This may take up to a minute</p>
                        </div>
                    </div>
                </div>
                <style>
                .progress-animation-indeterminate {
                    height: 100%;
                    background: linear-gradient(90deg, #3b82f6, #10b981, #3b82f6);
                    background-size: 200% 100%;
                    animation: indeterminate-progress 2s linear infinite;
                }
                @keyframes indeterminate-progress {
                    0% { background-position: 200% 0; }
                    100% { background-position: -200% 0; }
                }
                .divewp-progress-container {
                    animation: fadeIn 0.5s ease-in-out;
                }
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                </style>`;
            $container.html(progressHtml).show();
            $button.prop('disabled', true);

            $.ajax({
                url: divewpHosting.ajaxurl,
                type: 'POST',
                data: { 
                    action: 'divewp_get_hosting_evaluation_cards',
                    nonce: divewpHosting.nonce,
                    enabled_tests: enabledTests,
                    skip_network_requests: skipNetworkRequests,
                    test_configuration_mode: 'custom'
                },
                dataType: 'json',
                timeout: 120000, // 2 minute timeout
                success: function(response) {
                    console.log('[DiveWP] AJAX success:', response);
                    if (response.success && response.data) {
                        // Check if concurrency tests are pending - if so, DON'T render UI yet
                        if (response.data.evaluation_data && 
                            response.data.evaluation_data.tests && 
                            response.data.evaluation_data.tests.concurrency && 
                            response.data.evaluation_data.tests.concurrency.status === 'pending') {
                            
                            console.log('[DiveWP] Concurrency tests are pending - running tests before displaying results');
                            
                                                         // Show concurrency testing progress instead of final results
                             $container.html(`
                                 <div class="divewp-progress-container" style="background: #fff; border: 2px solid #e5e7eb; border-radius: 16px; padding: 0; margin: 40px auto; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); overflow: hidden; max-width: 600px; width: 90%;">
                                     <div class="preloader-video-container" style="width: 100%; height: 200px; position: relative; display: flex; justify-content: center; align-items: center; border-radius: 14px 14px 0 0; overflow: hidden;">
                                         <img src="${pluginUrl}/assets/animations/preloader_hosting.webp" alt="Hosting preloader" style="width: 100%; height: 100%; object-fit: cover; display:block;"/>
                                         <div class="video-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.3);"></div>
                                     </div>
                                     <div class="preloader-content" style="padding: 30px; text-align: center; background: #fff;">
                                         <h3 style="margin: 0 0 16px 0; color: #1f2937; font-size: 22px; font-weight: 600;">Running Concurrency Tests</h3>
                                         <div style="margin-bottom: 16px;">
                                             <div class="progress-bar-container" style="background: #f3f4f6; border-radius: 8px; height: 8px; overflow: hidden; margin-bottom: 12px;">
                                                 <div class="progress-animation-indeterminate"></div>
                                             </div>
                                             <p id="concurrency-status" style="margin: 0; color: #6b7280; font-size: 14px;">Testing multi-user performance - this may take up to 20 seconds</p>
                                         </div>
                                     </div>
                                 </div>
                                 <style>
                                 .progress-animation-indeterminate {
                                     height: 100%;
                                     background: linear-gradient(90deg, #3b82f6, #10b981, #3b82f6);
                                     background-size: 200% 100%;
                                     animation: indeterminate-progress 2s linear infinite;
                                 }
                                 @keyframes indeterminate-progress {
                                     0% { background-position: 200% 0; }
                                     100% { background-position: -200% 0; }
                                 }
                                 .divewp-progress-container {
                                     animation: fadeIn 0.5s ease-in-out;
                                 }
                                 @keyframes fadeIn {
                                     from { opacity: 0; }
                                     to { opacity: 1; }
                                 }
                                 </style>
                             `);
                            
                            // Store the response data for later use
                            window.divewp_pending_results = response.data;
                            
                            // Start concurrency tests - UI will be rendered when complete
                            runStepBasedConcurrencyTests($container, response.data.evaluation_data);
                            
                        } else {
                            // No pending concurrency tests - render results immediately
                            renderFinalResults($container, response.data);
                        }
                        
                    } else {
                        const errorMessage = response.data ? response.data.message : 'An unknown error occurred.';
                        $container.html('<div class="notice notice-error"><p>Could not load results: ' + errorMessage + '</p></div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('[DiveWP] AJAX error:', { xhr, status, error });
                    let errorText = `AJAX error: ${status} - ${error}.`;
                    if(xhr.responseText) {
                        errorText += '<br/><br/><strong>Server Response:</strong><br/>' + xhr.responseText.substring(0, 500);
                    }
                    $container.html(`<div class="notice notice-error"><p>${errorText}</p></div>`);
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $body.css('overflow', '');
                }
            });
        });
    });

    /**
     * Helper function to get rating CSS class
     */
    function getRatingClass(rating) {
        return rating ? rating.toLowerCase() : 'unknown';
    }

    /**
     * Render final results after all tests complete
     */
    function renderFinalResults($container, responseData) {
        let resultsHtml = '';
        
        if (responseData.overall_html) {
            resultsHtml += responseData.overall_html;
        }
        
        if (responseData.cards_html) {
            resultsHtml += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 24px;">';
            resultsHtml += responseData.cards_html;
            resultsHtml += '</div>';
        }
        
        if (responseData.recommendation_html) {
            resultsHtml += responseData.recommendation_html;
        }
        
        $container.html(resultsHtml).hide().fadeIn(400);
        
        // Add smooth scroll to results
        $('html, body').animate({
            scrollTop: $container.offset().top - 50
        }, 800);
    }

    /**
     * Execute step-based concurrency tests and update results
     */
    function runStepBasedConcurrencyTests($container, evaluationData) {
        const steps = ['database', 'http', 'memory', 'file', 'finalize'];
        let currentStepIndex = 0;
        let sessionId = 'concurrency_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        
        // Update concurrency card to show "Running..." status
        updateConcurrencyCardStatus($container, 'Running concurrency tests...', 'running');
        
        function runNextStep() {
            if (currentStepIndex >= steps.length) {
                console.log('[DiveWP] All concurrency steps completed');
                return;
            }
            
            const step = steps[currentStepIndex];
            console.log(`[DiveWP] Running concurrency step: ${step}`);
            
            // Update progress status
            const stepNames = {
                'database': 'Database operations',
                'http': 'HTTP requests',
                'memory': 'Memory competition',
                'file': 'File system operations',
                'finalize': 'Finalizing results'
            };
            
            const $statusText = $('#concurrency-status');
            if ($statusText.length) {
                $statusText.text(`Testing ${stepNames[step] || step}... (Step ${currentStepIndex + 1} of ${steps.length})`);
            }
            
            jQuery.ajax({
                url: divewpHosting.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_run_concurrency_step',
                    nonce: divewpHosting.nonce,
                    session_id: sessionId,
                    step: step
                },
                dataType: 'json',
                timeout: 60000, // 60 second timeout per step (increased for slow servers)
                success: function(response) {
                    console.log(`[DiveWP] Step ${step} completed:`, response);
                    
                    if (response.success) {
                        currentStepIndex++;
                        
                        if (step === 'finalize') {
                            // Final step - render results directly without slow template re-rendering
                            if (response.data && response.data.score !== undefined) {
                                console.log('[DiveWP] Concurrency tests completed successfully - rendering final results FAST');
                                
                                // Update status to show rendering
                                const $statusText = $('#concurrency-status');
                                if ($statusText.length) {
                                    $statusText.text('Tests completed! Displaying results...');
                                }
                                
                                // Get the stored response data and render it directly
                                let originalData = window.divewp_pending_results;
                                if (originalData) {
                                    // Render the original results immediately (fast)
                                    renderFinalResults($container, originalData);
                                    
                                    // Then update just the concurrency card with real data (fast DOM update)
                                    setTimeout(function() {
                                        updateConcurrencyCardWithCompleteResults($container, response.data);
                                        console.log('[DiveWP] Concurrency card updated with final results');
                                    }, 100);
                                    
                                    // Clean up stored data
                                    delete window.divewp_pending_results;
                                } else {
                                    console.log('[DiveWP] No pending results found - fallback mode');
                                    updateConcurrencyCardResults($container, response.data);
                                }
                            } else {
                                updateConcurrencyCardStatus($container, 'Results processing error', 'error');
                            }
                        } else {
                            // Continue to next step
                            setTimeout(runNextStep, 1000); // 1 second delay between steps
                        }
                    } else {
                        const errorMsg = response.data ? (response.data.message || response.data.error || 'Unknown error') : 'Unknown error';
                        console.error(`[DiveWP] Step ${step} failed:`, errorMsg);
                        console.log(`[DiveWP] Full error response for ${step}:`, response);
                        updateConcurrencyCardStatus($container, `Error in ${step} test: ${errorMsg}`, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(`[DiveWP] Step ${step} AJAX error:`, { xhr, status, error });
                    
                    if (status === 'timeout') {
                        updateConcurrencyCardStatus($container, `${step.charAt(0).toUpperCase() + step.slice(1)} test timed out - server too slow`, 'warning');
                        
                        // Continue to next step even after timeout (graceful degradation)
                        currentStepIndex++;
                        if (currentStepIndex < steps.length) {
                            setTimeout(runNextStep, 2000); // 2 second delay after timeout
                        } else {
                            // All steps completed with timeouts - render results with incomplete concurrency data
                            console.log('[DiveWP] All concurrency tests completed with timeouts - rendering results');
                            let originalData = window.divewp_pending_results;
                            if (originalData) {
                                // Set a default concurrency result for timeout case
                                originalData.evaluation_data.tests.concurrency = {
                                    score: 0,
                                    rating: 'critical',
                                    interpretation: 'Concurrency tests timed out. Server struggles with multi-user operations.',
                                    timed_out: true
                                };
                                renderFinalResults($container, originalData);
                                $('body').css('overflow', '');
                                delete window.divewp_pending_results;
                            } else {
                                updateConcurrencyCardStatus($container, 'Tests completed with timeouts', 'warning');
                            }
                        }
                    } else {
                        updateConcurrencyCardStatus($container, `Network error in ${step} test`, 'error');
                        // For network errors, also fall back to rendering incomplete results after a delay
                        setTimeout(function() {
                            let originalData = window.divewp_pending_results;
                            if (originalData) {
                                originalData.evaluation_data.tests.concurrency = {
                                    score: 0,
                                    rating: 'error',
                                    interpretation: 'Concurrency tests failed due to network errors.',
                                    error: true
                                };
                                renderFinalResults($container, originalData);
                                $('body').css('overflow', '');
                                delete window.divewp_pending_results;
                            }
                        }, 3000); // 3 second delay to show error message first
                    }
                }
            });
        }
        
        // Start the first step
        runNextStep();
    }

    /**
     * Update concurrency card status during testing
     */
    function updateConcurrencyCardStatus($container, statusText, statusType) {
        const $concurrencyCard = $container.find('.test-card').has('h6:contains("Concurrency")');
        if ($concurrencyCard.length) {
            const $interpretation = $concurrencyCard.find('.interpretation');
            
            // Update interpretation text
            $interpretation.text(statusText);
            
            // Update visual styling based on status
            if (statusType === 'running') {
                $interpretation.css({
                    'color': '#0073aa',
                    'font-style': 'italic'
                });
            } else if (statusType === 'error') {
                $interpretation.css({
                    'color': '#d63384',
                    'font-weight': 'bold'
                });
            }
        }
    }

    /**
     * Update concurrency card with complete final results including sub-tests
     */
    function updateConcurrencyCardWithCompleteResults($container, concurrencyData) {
        const $concurrencyCard = $container.find('.hosting-evaluation-card').has('h3:contains("Multi-User Handling")');
        if ($concurrencyCard.length) {
            // Update main score and rating
            const $score = $concurrencyCard.find('.score');
            const $rating = $concurrencyCard.find('.rating-pill');
            
            $score.text(concurrencyData.score || 0);
            $rating.text((concurrencyData.rating || 'unknown').toUpperCase());
            
            // Update rating classes (no inline colors)
            const ratingClass = (concurrencyData.rating || 'unknown').toLowerCase();
            $rating.removeClass(function (index, className) {
                return (className.match(/\brating-(excellent|good|fair|poor|critical|skipped|error)\b/g) || []).join(' ');
            }).addClass(`rating-${ratingClass}`);
            $score.removeClass(function (index, className) {
                return (className.match(/\brating-(excellent|good|fair|poor|critical|skipped|error)\b/g) || []).join(' ');
            }).addClass(`rating-${ratingClass}`);
            
            // Update interpretation with timeout info
            const $interpretation = $concurrencyCard.find('.summary-text');
            let interpretationText = concurrencyData.interpretation || 'Concurrency test completed';
            
            // Add timeout and score deduction information if present
            if (concurrencyData.timed_out_tests && Object.keys(concurrencyData.timed_out_tests).length > 0) {
                const timedOutTests = Object.keys(concurrencyData.timed_out_tests);
                interpretationText += ` Note: ${timedOutTests.join(', ')} test(s) timed out.`;
                
                if (concurrencyData.score_deduction && concurrencyData.score_deduction > 0) {
                    interpretationText += ` Score reduced by ${concurrencyData.score_deduction} points due to timeouts.`;
                }
            }
            
            $interpretation.html('💡 ' + interpretationText);
            
            // Update sub-tests with timeout indicators
            updateConcurrencySubTests($concurrencyCard, concurrencyData);
            
            // Update total processing time
            const $totalTime = $concurrencyCard.find('.performance-time-value');
            if (concurrencyData.total_time) {
                const timeInSeconds = (concurrencyData.total_time / 1000).toFixed(1);
                $totalTime.text(timeInSeconds + 's');
            }
            
            console.log('[DiveWP] Concurrency card completely updated with:', {
                score: concurrencyData.score,
                rating: concurrencyData.rating,
                timeouts: concurrencyData.timed_out_tests,
                deduction: concurrencyData.score_deduction
            });
        }
    }

    /**
     * Update concurrency sub-tests with timeout indicators
     */
    function updateConcurrencySubTests($concurrencyCard, concurrencyData) {
        const detailed_results = concurrencyData.detailed_results || {};
        const timed_out_tests = concurrencyData.timed_out_tests || {};
        
        // Sub-test data mapping
        const subTestsData = [
            {
                name: 'Database Concurrency',
                key: 'database',
                operations: detailed_results.database?.total_operations || 445
            },
            {
                name: 'HTTP Concurrency', 
                key: 'http',
                operations: detailed_results.http?.total_requests || 4
            },
            {
                name: 'Memory Concurrency',
                key: 'memory', 
                operations: detailed_results.memory?.total_processes || 84
            },
            {
                name: 'File Concurrency',
                key: 'file',
                operations: detailed_results.file?.total_operations || 290
            }
        ];
        
        // Find sub-tests grid
        const $subTestsGrid = $concurrencyCard.find('.sub-tests-grid');
        if ($subTestsGrid.length) {
            const $subTestItems = $subTestsGrid.find('.sub-test-item');
            
            $subTestItems.each(function(index) {
                if (index < subTestsData.length) {
                    const testData = subTestsData[index];
                    const $item = $(this);
                    const $timeDiv = $item.find('.sub-test-time');
                    const $operationsDiv = $item.find('.sub-test-operations');
                    
                    // Update time with timeout indicator
                    if (timed_out_tests[testData.key]) {
                        $timeDiv.html('<span style="color: #dc2626;">Timed Out</span>');
                    } else if (detailed_results[testData.key]?.total_time) {
                        const timeInSeconds = (detailed_results[testData.key].total_time).toFixed(1);
                        $timeDiv.text(timeInSeconds + 's');
                    }
                    
                    // Update operations count
                    const operationsText = testData.key === 'http' ? 
                        `${testData.operations} requests` : 
                        testData.key === 'memory' ? 
                        `${testData.operations} processes` :
                        `${testData.operations} operations`;
                    $operationsDiv.text(operationsText);
                }
            });
        }
    }

    /**
     * Update concurrency card with final results (legacy function)
     */
    function updateConcurrencyCardResults($container, concurrencyData) {
        const $concurrencyCard = $container.find('.test-card').has('h6:contains("Concurrency")');
        if ($concurrencyCard.length) {
            // Update score and rating
            const $score = $concurrencyCard.find('.score');
            const $rating = $concurrencyCard.find('.rating');
            const $interpretation = $concurrencyCard.find('.interpretation');
            const $testScore = $concurrencyCard.find('.test-score');
            
            // Update values
            $score.text(concurrencyData.score || 0);
            $rating.text((concurrencyData.rating || 'unknown').toUpperCase());
            
            // Build interpretation with timeout info (like database tests)
            let interpretationText = concurrencyData.interpretation || 'Concurrency test completed';
            
            // Add timeout and score deduction information if present
            if (concurrencyData.timed_out_tests && Object.keys(concurrencyData.timed_out_tests).length > 0) {
                const timedOutTests = Object.keys(concurrencyData.timed_out_tests);
                interpretationText += ` Note: ${timedOutTests.join(', ')} test(s) timed out.`;
                
                if (concurrencyData.score_deduction && concurrencyData.score_deduction > 0) {
                    interpretationText += ` Score reduced by ${concurrencyData.score_deduction} points due to timeouts.`;
                }
            }
            
            $interpretation.text(interpretationText);
            
            // Update styling based on rating
            const ratingClass = getRatingClass(concurrencyData.rating);
            $testScore.removeClass('excellent good fair poor critical unknown')
                      .addClass(ratingClass);
            
            // Reset interpretation styling
            $interpretation.css({
                'color': '',
                'font-style': '',
                'font-weight': ''
            });
            
            // Update metrics if available
            const $metrics = $concurrencyCard.find('.test-metrics p');
            if (concurrencyData.scaling_factor !== undefined && $metrics.length) {
                $metrics.html(`<strong>Scaling:</strong> ${parseFloat(concurrencyData.scaling_factor).toFixed(2)}x`);
            }
            
            // Add debug information for troubleshooting
            console.log('[DiveWP] Concurrency card updated with results:', concurrencyData);
            console.log('[DiveWP] Timeout info:', {
                timed_out_tests: concurrencyData.timed_out_tests,
                score_deduction: concurrencyData.score_deduction,
                category_scores: concurrencyData.category_scores
            });
        }
    }
})(jQuery); 