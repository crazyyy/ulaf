/**
 * DiveWP Hosting Evaluation JavaScript - WITH COMPREHENSIVE LOGGING
 */
(function($) {
    'use strict';

    console.log('🚀 DiveWP: Loading hosting evaluation JavaScript...');

    // Hosting evaluation handler
    $(document).ready(function() {
        console.log('🚀 DiveWP: Document ready, initializing hosting evaluation...');
        
        // Handle evaluation button click
        $('#run-hosting-evaluation').on('click', function() {
            console.log('🚀 DiveWP: Evaluation button clicked!');
            var $button = $(this);
            var $progress = $('#evaluation-progress');
            var $results = $('#evaluation-results');
            
            console.log('🚀 DiveWP: Button element:', $button.length ? 'FOUND' : 'NOT FOUND');
            console.log('🚀 DiveWP: Progress element:', $progress.length ? 'FOUND' : 'NOT FOUND');
            console.log('🚀 DiveWP: Results element:', $results.length ? 'FOUND' : 'NOT FOUND');
            
            // Disable button and show progress
            console.log('🚀 DiveWP: Disabling button and showing progress...');
            $button.prop('disabled', true);
            $progress.show();
            $results.hide().empty();
            console.log('🚀 DiveWP: UI state updated, starting evaluation...');
            
            // Run the complete evaluation
            runCompleteEvaluation();
        });
        
        // Add step-based testing option to the interface
        if ($('#run-step-based-test').length === 0) {
            var stepBasedButton = '<button type="button" id="run-step-based-test" class="button button-secondary" style="margin-left: 10px;">Run Step-by-Step Tests</button>';
            $('#run-hosting-evaluation').after(stepBasedButton);
        }
        
        // Handle step-based test button click
        $('#run-step-based-test').on('click', function() {
            console.log('🚀 DiveWP: Step-based test button clicked!');
            var $button = $(this);
            var $mainButton = $('#run-hosting-evaluation');
            var $progress = $('#evaluation-progress');
            var $results = $('#evaluation-results');
            
            // Disable both buttons and show progress
            $button.prop('disabled', true);
            $mainButton.prop('disabled', true);
            $progress.show();
            $results.hide().empty();
            
            // Run step-based tests
            runStepBasedResourceTests();
        });
        
        // Run complete evaluation
        function runCompleteEvaluation(minimalTest) {
            console.log('🚀 DiveWP: === STARTING ' + (minimalTest ? 'MINIMAL' : 'COMPLETE') + ' EVALUATION ===');
            console.log('🚀 DiveWP: AJAX URL:', divewpHosting.ajaxurl);
            console.log('🚀 DiveWP: Nonce:', divewpHosting.nonce);
            console.log('🚀 DiveWP: Current timestamp:', new Date().toISOString());
            
            var postData = {
                action: 'divewp_get_hosting_evaluation',
                nonce: divewpHosting.nonce
            };
            
            if (minimalTest) {
                postData.minimal_test = 'true';
                console.log('🚀 DiveWP: Running MINIMAL TEST mode');
            }
            
            $.ajax({
                url: divewpHosting.ajaxurl,
                type: 'POST',
                data: postData,
                beforeSend: function(xhr) {
                    console.log('🚀 DiveWP: AJAX request starting...');
                    console.log('🚀 DiveWP: XHR object:', xhr);
                },
                success: function(response) {
                    console.log('🚀 DiveWP: === AJAX SUCCESS RESPONSE ===');
                    console.log('🚀 DiveWP: Response type:', typeof response);
                    console.log('🚀 DiveWP: Response length:', JSON.stringify(response).length);
                    console.log('🚀 DiveWP: Full response:', response);
                    
                    if (response.success) {
                        console.log('🚀 DiveWP: Response success = true');
                        console.log('🚀 DiveWP: Response data:', response.data);
                        
                        // Check if this was a minimal test
                        if (response.data.minimal_test) {
                            console.log('🚀 DiveWP: Minimal test results received');
                            displayMinimalTestResults(response.data);
                        } else {
                            console.log('🚀 DiveWP: About to call displayEvaluationResults...');
                            displayEvaluationResults(response.data);
                            console.log('🚀 DiveWP: displayEvaluationResults completed');
                        }
                    } else {
                        console.error('❌ DiveWP: Response success = false');
                        console.error('❌ DiveWP: Error data:', response.data);
                        var errorMessage = response.data && response.data.message ? response.data.message : 'Evaluation failed';
                        console.error('❌ DiveWP: Error message:', errorMessage);
                        showError(errorMessage);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ DiveWP: === AJAX ERROR ===');
                    console.error('❌ DiveWP: Status:', status);
                    console.error('❌ DiveWP: Error:', error);
                    console.error('❌ DiveWP: XHR status:', xhr.status);
                    console.error('❌ DiveWP: XHR statusText:', xhr.statusText);
                    console.error('❌ DiveWP: XHR responseText:', xhr.responseText);
                    console.error('❌ DiveWP: XHR responseJSON:', xhr.responseJSON);
                    
                    // Check for specific error types
                    var errorMessage = 'Error: ' + error;
                    if (xhr.responseJSON && xhr.responseJSON.data) {
                        if (xhr.responseJSON.data.type === 'process_killed') {
                            errorMessage = xhr.responseJSON.data.message;
                            errorMessage += '\n\nDetected Environment: ' + (xhr.responseJSON.data.detected_environment || 'Unknown');
                        } else if (xhr.responseJSON.data.type === 'resource_limit') {
                            errorMessage = xhr.responseJSON.data.message;
                        } else if (xhr.responseJSON.data.message) {
                            errorMessage = xhr.responseJSON.data.message;
                        }
                    } else if (xhr.status === 500) {
                        // Generic 500 error - likely process killed
                        errorMessage = 'The hosting evaluation was terminated by your hosting provider. This typically happens on shared hosting with strict resource limits.\n\n' +
                                     'Your hosting environment appears to have very restrictive resource limits that prevent comprehensive testing.\n\n' +
                                     'Consider:\n' +
                                     '• Upgrading to a VPS or dedicated server\n' +
                                     '• Contacting your host about resource limits\n' +
                                     '• Using a hosting provider optimized for WordPress/WooCommerce';
                    }
                    
                    showError(errorMessage);
                },
                complete: function() {
                    console.log('🚀 DiveWP: AJAX request completed (success or error)');
                    console.log('🚀 DiveWP: Re-enabling button and hiding progress...');
                    $('#run-hosting-evaluation').prop('disabled', false);
                    $('#evaluation-progress').hide();
                    console.log('🚀 DiveWP: UI cleanup completed');
                }
            });
        }
        
        // Display evaluation results
        function displayEvaluationResults(data) {
            console.log('🚀 DiveWP: === DISPLAYING EVALUATION RESULTS ===');
            console.log('🚀 DiveWP: Input data type:', typeof data);
            console.log('🚀 DiveWP: Input data:', data);
            
            var $results = $('#evaluation-results');
            console.log('🚀 DiveWP: Results container found:', $results.length);
            
            if (!$results.length) {
                console.error('❌ DiveWP: Results container not found!');
                return;
            }
            
            console.log('🚀 DiveWP: Starting HTML generation...');
            var html = '';
            
            // Check if evaluation is incomplete
            console.log('🚀 DiveWP: Checking if evaluation is incomplete...');
            console.log('🚀 DiveWP: data.incomplete:', data.incomplete);
            console.log('🚀 DiveWP: data.reason:', data.reason);
            
            if (data.incomplete && data.reason === 'rate_limit') {
                console.log('🚀 DiveWP: Building incomplete evaluation HTML...');
                html += '<div class="hosting-evaluation-incomplete">';
                html += '<div class="incomplete-notice" style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px;">';
                html += '<h5 style="color: #721c24; margin-top: 0;">Evaluation Incomplete</h5>';
                html += '<p style="color: #721c24; margin-bottom: 10px;">' + data.message + '</p>';
                html += '<p style="color: #721c24; margin-bottom: 0;">The database test is crucial for accurate WooCommerce hosting assessment. Please wait and run the evaluation again for complete results.</p>';
                html += '</div>';
                
                // Still show individual test results
                html += '<h5>Partial Test Results (Incomplete)</h5>';
                html += '<p style="color: #666; font-style: italic;">These results are shown for reference only. A complete evaluation requires all tests to run successfully.</p>';
                console.log('🚀 DiveWP: Incomplete evaluation HTML built');
                
            } else {
                console.log('🚀 DiveWP: Building complete evaluation HTML...');
                // Normal overall score section
                html += '<div class="hosting-score-overview">';
                html += '<h5>' + 'Overall WooCommerce Hosting Score' + '</h5>';
                
                // Show test environment information
                if (data.environment) {
                    var envLabel = data.environment.charAt(0).toUpperCase() + data.environment.slice(1);
                    var modeLabel = data.test_mode ? ' (' + data.test_mode + ' mode)' : '';
                    html += '<p class="test-environment" style="margin: 10px 0; color: #666; font-style: italic;">Test Environment: ' + envLabel + ' Hosting' + modeLabel + '</p>';
                    
                    if (data.test_config) {
                        html += '<p class="test-details" style="margin: 5px 0; color: #888; font-size: 0.9em;">';
                        html += 'Tests performed: ' + data.test_config.iterations + ' iterations';
                        if (data.test_config.cpu_iterations) {
                            html += ', ' + (data.test_config.cpu_iterations / 1000) + 'K CPU operations';
                        }
                        if (data.test_config.db_records) {
                            html += ', ' + data.test_config.db_records + ' DB records';
                        }
                        html += '</p>';
                    }
                }
                
                html += '<div class="score-meter">';
                html += '<div class="score-value ' + data.rating + '">' + data.overall_score + '/100</div>';
                html += '<div class="score-bar"><div class="score-fill" style="width: ' + data.overall_score + '%;"></div></div>';
                html += '<div class="score-label">' + getRatingLabel(data.rating) + '</div>';
                html += '</div>';
                html += '</div>';
                console.log('🚀 DiveWP: Overall score HTML built');
            }
            
            // Recommendation section (only show if evaluation is complete)
            console.log('🚀 DiveWP: Processing recommendation section...');
            console.log('🚀 DiveWP: data.recommendation exists:', !!data.recommendation);
            console.log('🚀 DiveWP: data.incomplete:', data.incomplete);
            
            if (data.recommendation && !data.incomplete) {
                console.log('🚀 DiveWP: Building recommendation HTML...');
                html += '<div class="hosting-recommendation">';
                html += '<h5>WooCommerce Performance Analysis</h5>';
                html += '<p class="verdict">' + data.recommendation.verdict + '</p>';
                
                // Performance profile
                if (data.recommendation.performance_profile) {
                    console.log('🚀 DiveWP: Adding performance profile...');
                    html += '<div class="performance-profile">';
                    html += '<h6>Performance Profile: ' + data.recommendation.performance_profile.title + '</h6>';
                    html += '<p class="profile-description">' + data.recommendation.performance_profile.description + '</p>';
                    html += '<p class="suitable-for"><strong>Suitable for:</strong> ' + data.recommendation.performance_profile.suitable_for + '</p>';
                    html += '<div class="performance-disclaimer">';
                    html += '<p style="font-style: italic; font-size: 0.9em; color: #666; margin-top: 10px;">';
                    html += '<strong>Important:</strong> These results are based on single-user performance tests. ';
                    html += 'Real-world performance under concurrent load may vary significantly. ';
                    html += 'Consider implementing caching solutions and CDN for optimal performance.';
                    html += '</p>';
                    html += '</div>';
                    html += '</div>';
                }
                
                // Strengths
                if (data.recommendation.strengths && data.recommendation.strengths.length > 0) {
                    console.log('🚀 DiveWP: Adding strengths, count:', data.recommendation.strengths.length);
                    html += '<div class="hosting-strengths">';
                    html += '<h6>✅ Performance Strengths:</h6>';
                    html += '<ul>';
                    data.recommendation.strengths.forEach(function(strength) {
                        html += '<li>' + strength + '</li>';
                    });
                    html += '</ul>';
                    html += '</div>';
                }
                
                // Warnings
                if (data.recommendation.warnings && data.recommendation.warnings.length > 0) {
                    console.log('🚀 DiveWP: Adding warnings, count:', data.recommendation.warnings.length);
                    html += '<div class="hosting-warnings">';
                    html += '<h6 class="warning-title">⚠️ Critical Issues:</h6>';
                    html += '<ul class="warning-list">';
                    data.recommendation.warnings.forEach(function(warning) {
                        html += '<li>' + warning + '</li>';
                    });
                    html += '</ul>';
                    html += '</div>';
                }
                
                // Bottlenecks
                if (data.recommendation.bottlenecks && data.recommendation.bottlenecks.length > 0) {
                    console.log('🚀 DiveWP: Adding bottlenecks, count:', data.recommendation.bottlenecks.length);
                    html += '<div class="hosting-bottlenecks">';
                    html += '<h6>⚠️ Performance Bottlenecks:</h6>';
                    html += '<ul>';
                    data.recommendation.bottlenecks.forEach(function(bottleneck) {
                        html += '<li>' + bottleneck + '</li>';
                    });
                    html += '</ul>';
                    html += '</div>';
                }
                
                // Improvements
                if (data.recommendation.improvements && data.recommendation.improvements.length > 0) {
                    console.log('🚀 DiveWP: Adding improvements, count:', data.recommendation.improvements.length);
                    html += '<div class="hosting-improvements">';
                    html += '<h6>💡 Recommended Improvements:</h6>';
                    html += '<ul>';
                    data.recommendation.improvements.forEach(function(improvement) {
                        html += '<li>' + improvement + '</li>';
                    });
                    html += '</ul>';
                    html += '</div>';
                }
                html += '</div>';
                console.log('🚀 DiveWP: Recommendation HTML completed');
            }
            
            // Detailed test results
            console.log('🚀 DiveWP: Starting detailed test results...');
            console.log('🚀 DiveWP: data.tests:', data.tests);
            html += '<div class="test-results-grid">';
            
            var cardCount = 0;
            
            // Performance test results
            console.log('🚀 DiveWP: Processing Performance test...');
            if (data.tests.performance) {
                console.log('🚀 DiveWP: Performance data exists, creating card...');
                html += createTestCard('Performance', data.tests.performance, 'dashicons-performance');
                cardCount++;
                console.log('🚀 DiveWP: Performance card added');
            } else {
                console.error('❌ DiveWP: MISSING: Performance test data');
            }
            
            // Resources test results
            console.log('🚀 DiveWP: Processing Resources test...');
            if (data.tests.resources) {
                console.log('🚀 DiveWP: Resources data exists, creating card...');
                html += createTestCard('Resources', data.tests.resources, 'dashicons-dashboard');
                cardCount++;
                console.log('🚀 DiveWP: Resources card added');
            } else {
                console.error('❌ DiveWP: MISSING: Resources test data');
            }
            
            // Database test results
            console.log('🚀 DiveWP: Processing Database test...');
            if (data.tests.database) {
                console.log('🚀 DiveWP: Database data exists, creating card...');
                html += createTestCard('Database', data.tests.database, 'dashicons-database');
                cardCount++;
                console.log('🚀 DiveWP: Database card added');
            } else {
                console.error('❌ DiveWP: MISSING: Database test data');
            }
            
            // Database Benchmark test results
            console.log('🚀 DiveWP: Processing Database Benchmark test...');
            if (data.tests.database_benchmark) {
                console.log('🚀 DiveWP: Database Benchmark data exists, creating card...');
                html += createTestCard('Database Benchmark', data.tests.database_benchmark, 'dashicons-analytics');
                cardCount++;
                console.log('🚀 DiveWP: Database Benchmark card added');
            } else {
                console.log('ℹ️ DiveWP: Database Benchmark test data not available (may be skipped or failed)');
            }
            
            // Concurrency test results
            console.log('🚀 DiveWP: Processing Concurrency test...');
            if (data.tests.concurrency) {
                console.log('🚀 DiveWP: Concurrency data exists, creating card...');
                html += createTestCard('Concurrency', data.tests.concurrency, 'dashicons-networking');
                cardCount++;
                console.log('🚀 DiveWP: Concurrency card added');
            } else {
                console.error('❌ DiveWP: MISSING: Concurrency test data');
            }
            
            html += '</div>';
            
            console.log('🚀 DiveWP: TOTAL CARDS GENERATED:', cardCount);
            console.log('🚀 DiveWP: Final HTML length:', html.length);
            console.log('🚀 DiveWP: Results container exists:', $results.length);
            
            // Log the actual HTML structure for debugging
            var gridHtml = html.substring(html.indexOf('<div class="test-results-grid">'), html.lastIndexOf('</div>') + 6);
            console.log('🚀 DiveWP: GRID HTML STRUCTURE (first 500 chars):', gridHtml.substring(0, 500));
            
            console.log('🚀 DiveWP: Setting HTML content...');
            $results.html(html);
            console.log('🚀 DiveWP: HTML content set successfully');
            
            // Ensure proper grid layout after content load
            var $grid = $results.find('.test-results-grid');
            if ($grid.length) {
                console.log('🚀 DiveWP: Grid container found, cards count:', $grid.find('.test-card').length);
                console.log('🚀 DiveWP: Grid HTML after DOM insertion (first 200 chars):', $grid.html().substring(0, 200) + '...');
                // Force layout recalculation
                $grid[0].offsetHeight;
                console.log('🚀 DiveWP: Grid layout recalculated');
            } else {
                console.error('❌ DiveWP: ERROR: Grid container NOT found in DOM!');
            }
            
            console.log('🚀 DiveWP: Showing results...');
            $results.show();
            console.log('🚀 DiveWP: Results should now be visible');
            console.log('🚀 DiveWP: === DISPLAY RESULTS COMPLETED ===');
        }
        
        // Create test result card
        function createTestCard(title, data, icon) {
            console.log('🚀 DiveWP: Creating test card for:', title);
            console.log('🚀 DiveWP: Card data:', data);
            console.log('🚀 DiveWP: Card icon:', icon);
            
            var isRateLimited = data.interpretation && data.interpretation.indexOf('Please wait') !== -1;
            var cardClass = isRateLimited ? 'test-card rate-limited' : 'test-card';
            console.log('🚀 DiveWP: Rate limited:', isRateLimited);
            console.log('🚀 DiveWP: Card class:', cardClass);
            
            var html = '<div class="' + cardClass + '">';
            html += '<div class="test-header">';
            html += '<span class="dashicons ' + icon + '"></span>';
            html += '<h6>' + title + ' Test</h6>';
            html += '</div>';
            
            if (isRateLimited) {
                console.log('🚀 DiveWP: Building rate-limited card for:', title);
                html += '<div class="test-score skipped">';
                html += '<span class="score">--/100</span>';
                html += '<span class="rating">Skipped</span>';
                html += '</div>';
            } else {
                console.log('🚀 DiveWP: Building normal card for:', title);
                html += '<div class="test-score ' + data.rating + '">';
                html += '<span class="score">' + (data.score || data.overall_score) + '/100</span>';
                html += '<span class="rating">' + getRatingLabel(data.rating) + '</span>';
                html += '</div>';
            }
            
            // Add specific metrics
            console.log('🚀 DiveWP: Adding metrics for:', title);
            html += '<div class="test-metrics">';
            
            if (title === 'Performance') {
                console.log('🚀 DiveWP: Processing Performance metrics...');
                // Check if we have WordPress Core Performance data (new enhanced system)
                if (data.wp_core_score || data.shortcode_processing_time) {
                    console.log('🚀 DiveWP: Using WordPress Core Performance data');
                    html += '<p><strong>WordPress Core Performance (Averaged over ' + (data.test_iterations || 1) + ' runs):</strong></p>';
                    html += '<p>Shortcode Processing: ' + (data.shortcode_processing_time || '0') + 's</p>';
                    html += '<p>Hook Execution: ' + (data.hook_execution_time || '0') + 's</p>';
                    html += '<p>Transient Operations: ' + (data.transient_operations_time || '0') + 's</p>';
                    html += '<p>Security Functions: ' + (data.security_functions_time || '0') + 's</p>';
                    html += '<p><strong>Total Time: ' + (data.total_time || '0') + 's</strong></p>';
                } else {
                    console.log('🚀 DiveWP: Using fallback WooCommerce operations data');
                    // Fallback to old WooCommerce operations for backwards compatibility
                    html += '<p><strong>WooCommerce Operations (Averaged over ' + (data.test_iterations || 1) + ' runs):</strong></p>';
                    html += '<p>Price Calculations: ' + (data.price_calc_time || '0') + 'ms</p>';
                    html += '<p>Shipping Calculations: ' + (data.shipping_calc_time || '0') + 'ms</p>';
                    html += '<p>Inventory Checks: ' + (data.inventory_check_time || '0') + 'ms</p>';
                    html += '<p>Products Calculated: ' + (data.products_calculated || '0') + '</p>';
                    html += '<p><strong>Total Time: ' + (data.total_time || '0') + 'ms</strong></p>';
                }
                
                // Display opcode caching status
                console.log('🚀 DiveWP: Adding cache status...');
                html += '<div class="cache-status">';
                html += '<p><strong>PHP Acceleration:</strong></p>';
                if (data.opcache_enabled) {
                    html += '<p class="success">✓ OPcache Enabled</p>';
                } else if (data.apc_enabled) {
                    html += '<p class="success">✓ APC Cache Enabled</p>';
                } else if (data.xcache_enabled) {
                    html += '<p class="success">✓ XCache Enabled</p>';
                } else {
                    html += '<p class="warning">⚠ No PHP opcode caching detected</p>';
                }
                html += '</div>';
                
                if (data.interpretation) {
                    html += '<p class="interpretation">' + data.interpretation + '</p>';
                }
                
            } else if (title === 'Resources') {
                console.log('🚀 DiveWP: Processing Resources metrics...');
                html += '<p><strong>Hosting Resource Capabilities (Averaged over ' + (data.test_iterations_completed || data.test_iterations || 1) + ' runs):</strong></p>';
                html += '<div class="resource-tests">';
                html += '<div class="sub-scores">';
                
                // Enhanced CPU Performance with Statistics
                html += '<p><strong><span data-tooltip="CPU performance tests measure computational capabilities through prime number generation (mathematical operations), string processing (text manipulation), and array sorting (memory-intensive operations). Results are highly dependent on PHP version - PHP 8.x can be 2-3x faster than PHP 7.4 for the same hosting hardware.">CPU Performance:</span></strong> ' + (data.cpu_score || '0') + '/100</p>';
                
                // Show CPU statistics if available
                if (data.cpu_score_stats) {
                    html += '<div class="cpu-stats" style="margin-left: 15px; font-size: 0.85em; color: #666;">';
                    html += '<p>• Mean: ' + data.cpu_score_stats.mean + ', Median: ' + data.cpu_score_stats.median + ' (σ=' + data.cpu_score_stats.stddev + ')</p>';
                    html += '</div>';
                }
                
                // Enhanced CPU test timings with new metrics
                if (data.cpu_detailed_averages) {
                    html += '<div class="cpu-timings" style="margin-left: 20px; font-size: 0.9em; color: #666;">';
                    html += '<p>• Prime Generation: ' + (data.cpu_detailed_averages.prime_generation_time || '0') + 's</p>';
                    if (data.cpu_detailed_averages.mathematical_operations_time) {
                        html += '<p>• Mathematical Operations: ' + data.cpu_detailed_averages.mathematical_operations_time + 's</p>';
                    }
                    if (data.cpu_detailed_averages.conditional_logic_time) {
                        html += '<p>• Conditional Logic: ' + data.cpu_detailed_averages.conditional_logic_time + 's</p>';
                    }
                    if (data.cpu_detailed_averages.string_processing_time) {
                        html += '<p>• String Processing: ' + data.cpu_detailed_averages.string_processing_time + 's</p>';
                    }
                    if (data.cpu_detailed_averages.array_operations_time) {
                        html += '<p>• Array Operations: ' + data.cpu_detailed_averages.array_operations_time + 's</p>';
                    }
                    if (data.cpu_detailed_averages.primes_found) {
                        html += '<p>• Primes Found: ' + data.cpu_detailed_averages.primes_found + '</p>';
                    }
                    html += '</div>';
                } else if (data.cpu_prime_time !== undefined) {
                    // Fallback to old format for backwards compatibility
                    html += '<div class="cpu-timings" style="margin-left: 20px; font-size: 0.9em; color: #666;">';
                    html += '<p>• Prime Number Generation: ' + data.cpu_prime_time + 's</p>';
                    html += '<p>• String Processing: ' + data.cpu_string_time + 's</p>';
                    html += '<p>• Array Sorting: ' + data.cpu_array_time + 's</p>';
                    html += '<p>• <strong>Total Time: ' + data.cpu_total_time + 's</strong></p>';
                    if (data.cpu_primes_found) {
                        html += '<p>• Primes Found: ' + data.cpu_primes_found + '</p>';
                    }
                    html += '</div>';
                }
                
                // WordPress Core Performance (NEW)
                if (data.wp_core_score) {
                    html += '<p><strong>WordPress Core Performance:</strong> ' + data.wp_core_score + '/100</p>';
                    
                    // Show WordPress statistics
                    if (data.wp_core_score_stats) {
                        html += '<div class="wp-stats" style="margin-left: 15px; font-size: 0.85em; color: #666;">';
                        html += '<p>• Mean: ' + data.wp_core_score_stats.mean + ', Median: ' + data.wp_core_score_stats.median + ' (σ=' + data.wp_core_score_stats.stddev + ')</p>';
                        html += '</div>';
                    }
                    
                    // WordPress detailed timings
                    if (data.wp_core_detailed_averages) {
                        html += '<div class="wp-timings" style="margin-left: 20px; font-size: 0.9em; color: #666;">';
                        html += '<p>• Shortcode Processing: ' + (data.wp_core_detailed_averages.shortcode_processing_time || '0') + 's</p>';
                        html += '<p>• Hook Execution: ' + (data.wp_core_detailed_averages.hook_execution_time || '0') + 's</p>';
                        html += '<p>• Transient Operations: ' + (data.wp_core_detailed_averages.transient_operations_time || '0') + 's</p>';
                        html += '<p>• Security Functions: ' + (data.wp_core_detailed_averages.security_functions_time || '0') + 's</p>';
                        html += '</div>';
                    }
                }
                
                html += '<p><strong>Memory Allocation:</strong> ' + (data.memory_score || '0') + '/100</p>';
                
                // Show memory statistics
                if (data.memory_score_stats) {
                    html += '<div class="memory-stats" style="margin-left: 15px; font-size: 0.85em; color: #666;">';
                    html += '<p>• Mean: ' + data.memory_score_stats.mean + ', Median: ' + data.memory_score_stats.median + ' (σ=' + data.memory_score_stats.stddev + ')</p>';
                    html += '</div>';
                }
                
                html += '<p><strong>Disk I/O Speed:</strong> ' + (data.io_score || '0') + '/100</p>';
                html += '<p><strong>Network Access:</strong> ' + (data.network_score || '0') + '/100</p>';
                html += '</div>'; // Close sub-scores
                html += '<div class="resource-details">';
                html += '<p>Memory Limit: ' + (data.memory_limit || 'Unknown') + '</p>';
                html += '<p>Max Allocated: ' + (data.max_memory_allocated || 'Unknown') + '</p>';
                html += '<p>Allocation Efficiency: ' + (data.allocation_efficiency || '0') + '%</p>';
                
                // Show test stability assessment
                if (data.test_stability) {
                    html += '<p><strong>Test Stability:</strong> ' + data.test_stability + '</p>';
                }
                
                html += '</div>'; // Close resource-details
                html += '</div>'; // Close resource-tests
                if (data.interpretation) {
                    html += '<p class="interpretation">' + data.interpretation + '</p>';
                }
                
            } else if (title === 'Database') {
                console.log('🚀 DiveWP: Processing Database metrics...');
                // Check if test was rate limited
                if (data.interpretation && data.interpretation.indexOf('Please wait') !== -1) {
                    console.log('🚀 DiveWP: Database test was rate limited');
                    html += '<div class="rate-limited-notice" style="padding: 20px; text-align: center;">';
                    html += '<p style="color: #856404; margin: 0;"><strong>Test Rate Limited</strong></p>';
                    html += '<p style="color: #856404; margin: 10px 0 0 0;">' + data.interpretation + '</p>';
                    html += '<p style="color: #666; font-size: 0.9em; margin: 10px 0 0 0;">This test can only run once every 30 seconds to prevent server overload.</p>';
                    html += '</div>';
                } else {
                    console.log('🚀 DiveWP: Building normal database metrics');
                    html += '<p><strong>Database Performance (Averaged over ' + (data.test_iterations || 1) + ' runs):</strong></p>';
                    html += '<p>Products Tested: ' + (data.products_tested || '0') + ' per run</p>';
                    html += '<p>Meta Records Created: ' + (data.meta_records || '0') + ' per run</p>';
                    html += '<p>Complex Queries Run: ' + (data.queries_run || '0') + ' per run</p>';
                    html += '<p>Insert Time: ' + (data.insert_time || '0') + 'ms</p>';
                    html += '<p>Select Time: ' + (data.select_time || '0') + 'ms</p>';
                    html += '<p>Update Time: ' + (data.update_time || '0') + 'ms</p>';
                    html += '<p><strong>Total Time: ' + (data.total_time || '0') + 'ms</strong></p>';
                    
                    // Database Function Performance (Database-Adaptive)
                    if (data.mysql_crypto_time || data.mysql_functions_score) {
                        console.log('🚀 DiveWP: Adding MySQL function performance data');
                        html += '<div class="mysql-functions" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">';
                        
                        // Show database type and compatibility
                        const dbType = data.database_type || 'Unknown';
                        const dbVersion = data.database_version || 'Unknown';
                        const isCompatible = data.is_mysql_compatible;
                        
                        html += '<p><strong>Database Function Performance:</strong></p>';
                        html += '<p style="color: #666; font-size: 0.9em;">Database: ' + dbType + ' ' + dbVersion + 
                                (isCompatible ? ' (MySQL Compatible)' : ' (Alternative DB)') + '</p>';
                        
                        if (data.mysql_functions_score) {
                            html += '<p>Overall Database Score: ' + data.mysql_functions_score + '/100</p>';
                        }
                        
                        html += '<div class="mysql-timings" style="margin-left: 15px; font-size: 0.9em; color: #666;">';
                        if (data.mysql_crypto_time) {
                            const cryptoLabel = isCompatible ? 'Cryptographic Functions' : 'Hash Functions';
                            html += '<p>• ' + cryptoLabel + ': ' + data.mysql_crypto_time + 's</p>';
                        }
                        if (data.mysql_math_time) {
                            const mathLabel = isCompatible ? 'Mathematical Functions' : 'Basic Math Operations';
                            html += '<p>• ' + mathLabel + ': ' + data.mysql_math_time + 's</p>';
                        }
                        if (data.mysql_string_time) {
                            html += '<p>• String Functions: ' + data.mysql_string_time + 's</p>';
                        }
                        if (data.mysql_datetime_time) {
                            const dateLabel = isCompatible ? 'Date/Time Functions' : 'Basic Date Functions';
                            html += '<p>• ' + dateLabel + ': ' + data.mysql_datetime_time + 's</p>';
                        }
                        if (data.mysql_aggregate_time) {
                            const aggLabel = isCompatible ? 'Aggregate Functions' : 'Basic Aggregates';
                            html += '<p>• ' + aggLabel + ': ' + data.mysql_aggregate_time + 's</p>';
                        }
                        if (data.mysql_total_time) {
                            html += '<p>• <strong>Database Total: ' + data.mysql_total_time + 's</strong></p>';
                        }
                        html += '</div>';
                        html += '</div>';
                    }
                    
                    if (data.interpretation) {
                        html += '<p class="interpretation">' + data.interpretation + '</p>';
                    }
                }
                
            } else if (title === 'Database Benchmark') {
                console.log('🚀 DiveWP: Processing Database Benchmark metrics...');
                
                if (data.skipped) {
                    html += '<div class="benchmark-skipped" style="padding: 20px; text-align: center;">';
                    html += '<p style="color: #856404; margin: 0;"><strong>Benchmark Skipped</strong></p>';
                    html += '<p style="color: #856404; margin: 10px 0 0 0;">' + data.reason + '</p>';
                    if (data.suggestion) {
                        html += '<p style="color: #666; font-size: 0.9em; margin: 10px 0 0 0;">' + data.suggestion + '</p>';
                    }
                    html += '</div>';
                } else {
                    html += '<p><strong>Real-World Database Operations:</strong></p>';
                    html += '<p>Total Benchmark Time: ' + (data.total_time || '0') + 'ms</p>';
                    
                    // Database Analysis
                    if (data.database_analysis && data.database_analysis.content_volume) {
                        html += '<div class="db-analysis" style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">';
                        html += '<p><strong>Database Content Analysis:</strong></p>';
                        html += '<p style="margin-left: 15px; font-size: 0.9em; color: #666;">Posts: ' + data.database_analysis.content_volume.posts + ', Comments: ' + data.database_analysis.content_volume.comments + ', Users: ' + data.database_analysis.content_volume.users + '</p>';
                        if (data.database_analysis.woocommerce.detected) {
                            html += '<p style="margin-left: 15px; font-size: 0.9em; color: #666;">WooCommerce Products: ' + data.database_analysis.woocommerce.products + '</p>';
                        }
                        html += '</div>';
                    }
                    
                    // WooCommerce Operations
                    if (data.woocommerce_operations && !data.woocommerce_operations.skipped) {
                        html += '<div class="wc-operations" style="margin: 15px 0;">';
                        html += '<p><strong>WooCommerce Operations:</strong></p>';
                        html += '<p style="margin-left: 15px; font-size: 0.9em;">Product Catalog: ' + data.woocommerce_operations.product_catalog.operations + ' ops, avg ' + data.woocommerce_operations.product_catalog.avg_time + 'ms</p>';
                        html += '<p style="margin-left: 15px; font-size: 0.9em;">Product Details: ' + data.woocommerce_operations.product_details.operations + ' ops, avg ' + data.woocommerce_operations.product_details.avg_time + 'ms</p>';
                        if (data.woocommerce_operations.order_history.operations > 0) {
                            html += '<p style="margin-left: 15px; font-size: 0.9em;">Order History: ' + data.woocommerce_operations.order_history.operations + ' ops, avg ' + data.woocommerce_operations.order_history.avg_time + 'ms</p>';
                        }
                        html += '</div>';
                    }
                    
                    // Content Operations
                    if (data.content_operations) {
                        html += '<div class="content-operations" style="margin: 15px 0;">';
                        html += '<p><strong>Content Operations:</strong></p>';
                        html += '<p style="margin-left: 15px; font-size: 0.9em;">Archive Loading: ' + data.content_operations.archive_loading.operations + ' ops, avg ' + data.content_operations.archive_loading.avg_time + 'ms</p>';
                        html += '<p style="margin-left: 15px; font-size: 0.9em;">Search Operations: ' + data.content_operations.search_operations.operations + ' ops, avg ' + data.content_operations.search_operations.avg_time + 'ms</p>';
                        html += '<p style="margin-left: 15px; font-size: 0.9em;">Comment Loading: ' + data.content_operations.comment_loading.operations + ' ops, avg ' + data.content_operations.comment_loading.avg_time + 'ms</p>';
                        html += '<p style="margin-left: 15px; font-size: 0.9em;">Popular Content: ' + data.content_operations.popular_content.operations + ' ops, avg ' + data.content_operations.popular_content.avg_time + 'ms</p>';
                        html += '</div>';
                    }
                    
                    // Performance Analysis
                    if (data.performance_analysis) {
                        html += '<div class="performance-analysis" style="margin: 15px 0;">';
                        html += '<p><strong>Performance Analysis:</strong></p>';
                        if (data.performance_analysis.query_cache) {
                            html += '<p style="margin-left: 15px; font-size: 0.9em;">Query Cache Improvement: ' + data.performance_analysis.query_cache.cache_improvement + '%</p>';
                        }
                        html += '<p style="margin-left: 15px; font-size: 0.9em;">JOIN Performance: ' + data.performance_analysis.join_performance.operations + ' ops, avg ' + data.performance_analysis.join_performance.avg_time + 'ms</p>';
                        html += '<p style="margin-left: 15px; font-size: 0.9em;">Aggregate Functions: ' + data.performance_analysis.aggregate_performance.operations + ' ops, avg ' + data.performance_analysis.aggregate_performance.avg_time + 'ms</p>';
                        html += '</div>';
                    }
                    
                    if (data.interpretation) {
                        html += '<p class="interpretation">' + data.interpretation + '</p>';
                    }
                }
                
            } else if (title === 'Concurrency') {
                console.log('🚀 DiveWP: Processing Concurrency metrics...');
                html += '<p><strong>Concurrent Operation Handling:</strong></p>';
                html += '<p>Concurrent Operations: ' + (data.concurrent_operations || '0') + '</p>';
                html += '<p>Total Time: ' + (data.total_time || '0') + 'ms</p>';
                html += '<p>Avg Time per Operation: ' + (data.avg_time_per_operation || '0') + 'ms</p>';
                html += '<p>Avg Response Time: ' + (data.avg_response_time || '0') + 'ms</p>';
                html += '<p>Max Response Time: ' + (data.max_response_time || '0') + 'ms</p>';
                html += '<p>Scaling Factor: ' + (data.scaling_factor || '0') + 'x</p>';
                html += '<p>Response Degradation: ' + (data.response_degradation || '0') + 'x</p>';
                if (data.interpretation) {
                    html += '<p class="interpretation">' + data.interpretation + '</p>';
                }
            }
            
            html += '</div>';
            html += '</div>';
            
            console.log('🚀 DiveWP: Card HTML generated for:', title, '- Length:', html.length);
            return html;
        }
        
        // Get rating label
        function getRatingLabel(rating) {
            console.log('🚀 DiveWP: Getting rating label for:', rating);
            switch(rating) {
                case 'excellent': return 'Excellent';
                case 'good': return 'Good';
                case 'fair': return 'Fair';
                case 'poor': return 'Poor';
                case 'critical': return 'Critical';
                case 'timeout': return 'Timed Out';
                default: return 'Unknown';
            }
        }
        
        // Show error message
        function showError(message) {
            console.error('❌ DiveWP: === SHOWING ERROR MESSAGE ===');
            console.error('❌ DiveWP: Error message:', message);
            
            var $results = $('#evaluation-results');
            console.log('🚀 DiveWP: Results container for error:', $results.length ? 'FOUND' : 'NOT FOUND');
            
            var errorHtml = '<div class="notice notice-error" style="padding: 10px; margin: 10px 0;">';
            errorHtml += '<p><strong>Error:</strong> ' + message + '</p>';
            
            // Add specific help for 502 errors
            if (message.indexOf('502') !== -1 || message.indexOf('Bad Gateway') !== -1) {
                console.log('🚀 DiveWP: Adding 502 error help...');
                errorHtml += '<p style="margin-top: 10px;">This error typically means the server timed out or ran out of resources. Possible solutions:</p>';
                errorHtml += '<ul style="margin-left: 20px;">';
                errorHtml += '<li>Increase PHP memory_limit in your php.ini</li>';
                errorHtml += '<li>Increase max_execution_time in your php.ini</li>';
                errorHtml += '<li>Check your server error logs for more details</li>';
                errorHtml += '<li>Try running the test on a production server instead of local environment</li>';
                errorHtml += '</ul>';
            }
            
            errorHtml += '</div>';
            console.log('🚀 DiveWP: Setting error HTML...');
            $results.html(errorHtml).show();
            console.log('🚀 DiveWP: Error HTML displayed');
        }
        
        // Display minimal test results
        function displayMinimalTestResults(data) {
            console.log('🚀 DiveWP: === DISPLAYING MINIMAL TEST RESULTS ===');
            console.log('🚀 DiveWP: Data:', data);
            
            var $results = $('#evaluation-results');
            var html = '<div class="minimal-test-results">';
            
            html += '<h4>Minimal Test Results</h4>';
            html += '<p>' + data.message + '</p>';
            
            if (data.result && data.result.tests_completed) {
                html += '<h5>Tests Completed (' + data.result.total_tests + '/4):</h5>';
                html += '<ul>';
                
                data.result.tests_completed.forEach(function(test) {
                    var testName = test.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase() });
                    html += '<li>✓ ' + testName;
                    
                    // Add timing info if available
                    if (test === 'basic_math' && data.result.math_time) {
                        html += ' - ' + data.result.math_time;
                    } else if (test === 'database_query' && data.result.db_time) {
                        html += ' - ' + data.result.db_time;
                    } else if (test === 'file_write' && data.result.file_time) {
                        html += ' - ' + data.result.file_time;
                    }
                    
                    html += '</li>';
                });
                
                html += '</ul>';
                
                if (data.result.total_tests < 4) {
                    html += '<p class="warning">⚠️ Some tests could not be completed. Your hosting environment is extremely restrictive.</p>';
                }
                
                html += '<div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">';
                html += '<h5>What This Means:</h5>';
                html += '<p>Your hosting environment has severe restrictions that prevent comprehensive performance testing. ';
                html += 'This level of restriction is typical of very low-cost shared hosting providers.</p>';
                html += '<p><strong>Recommendation:</strong> For any serious WordPress/WooCommerce site, consider upgrading to:</p>';
                html += '<ul>';
                html += '<li>A reputable managed WordPress host</li>';
                html += '<li>A VPS (Virtual Private Server)</li>';
                html += '<li>A cloud hosting solution</li>';
                html += '</ul>';
                html += '</div>';
            }
            
            html += '</div>';
            
            $results.html(html).show();
            console.log('🚀 DiveWP: === MINIMAL TEST DISPLAY COMPLETED ===');
        }
        
        // Step-based resource test execution
        function runStepBasedResourceTests() {
            console.log('🚀 DiveWP: === STARTING STEP-BASED COMPLETE TESTS ===');
            
            var steps = ['performance', 'resources', 'database', 'database_benchmark', 'concurrency', 'finalize'];
            var currentStepIndex = 0;
            var sessionId = null;
            var stepResults = {};
            var $progress = $('#evaluation-progress');
            var $progressText = $progress.find('.progress-text');
            
            // Update progress text
            function updateProgress(step, total, message) {
                var percent = Math.round((step / total) * 100);
                $progressText.text(message + ' (' + percent + '%)');
            }
            
            // Execute individual test step
            function executeStep(step) {
                console.log('🚀 DiveWP: Executing step: ' + step);
                
                updateProgress(currentStepIndex + 1, steps.length, 'Running ' + step.toUpperCase() + ' tests...');
                
                var postData = {
                    action: 'divewp_run_test_step',
                    nonce: divewpHosting.nonce,
                    step: step
                };
                
                // Add session ID for continuation
                if (sessionId) {
                    postData.session_id = sessionId;
                }
                
                $.ajax({
                    url: divewpHosting.ajaxurl,
                    type: 'POST',
                    data: postData,
                    timeout: 30000, // 30 second timeout per step (complete test suites)
                    success: function(response, status) {
                        console.log('🚀 DiveWP: Step ' + step + ' response:', response);
                        
                        if (response.success && response.data) {
                            // Store session ID for the first successful test
                            if (step === 'performance' && response.data.session_id) {
                                sessionStorage.setItem('divewp_hosting_session_id', response.data.session_id);
                                console.log('🚀 DiveWP: Session ID stored: ' + response.data.session_id);
                            }
                            
                            // Store step results (new structure returns complete data directly)
                            stepResults[step] = response.data;
                            
                            // Check if step completed successfully
                            if (response.data.incomplete) {
                                console.warn('⚠️ DiveWP: Step ' + step + ' incomplete:', response.data);
                                // Continue to next step even if incomplete
                            }
                            
                            // Move to next step
                            currentStepIndex++;
                            
                            if (currentStepIndex < steps.length) {
                                // Execute next step after a brief delay
                                setTimeout(function() {
                                    executeStep(steps[currentStepIndex]);
                                }, 500);
                            } else {
                                // All steps completed
                                console.log('🚀 DiveWP: All step-based tests completed');
                                finishStepBasedTests();
                            }
                            
                        } else {
                            console.error('❌ DiveWP: Step ' + step + ' failed:', response);
                            
                            // Prepare error data with fallbacks
                            var errorData = response.data || response || {};
                            if (!errorData.message && response.data && typeof response.data === 'string') {
                                // If response.data is a string (like HTML error output), use it
                                errorData = { message: 'Server error: ' + (response.data.substring(0, 100) + '...') };
                            } else if (!errorData.message && !response.success) {
                                errorData = { message: 'Test execution failed' };
                            }
                            
                            handleStepError(step, errorData);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ DiveWP: Step ' + step + ' AJAX error:', status, error);
                        
                        // Check for timeout
                        if (status === 'timeout') {
                            console.warn('⚠️ DiveWP: Step ' + step + ' timed out, continuing to next step...');
                            
                            // Provide proper fallback data structure based on test type
                            var fallbackData = { incomplete: true, reason: 'timeout' };
                            
                            if (step === 'resources') {
                                fallbackData = {
                                    incomplete: true,
                                    reason: 'timeout',
                                    overall_score: 0,
                                    cpu_score: 0,
                                    memory_score: 0,
                                    io_score: 0,
                                    network_score: 0,
                                    rating: 'timeout',
                                    interpretation: 'Test timed out - unable to complete resource analysis'
                                };
                            } else if (step === 'database_benchmark') {
                                fallbackData = {
                                    incomplete: true,
                                    reason: 'timeout',
                                    score: 0,
                                    total_time: 0,
                                    rating: 'timeout',
                                    interpretation: 'Database benchmark timed out'
                                };
                            } else if (step === 'performance') {
                                fallbackData = {
                                    incomplete: true,
                                    reason: 'timeout',
                                    score: 0,
                                    total_time: 0,
                                    rating: 'timeout',
                                    interpretation: 'Performance test timed out'
                                };
                            } else if (step === 'database') {
                                fallbackData = {
                                    incomplete: true,
                                    reason: 'timeout',
                                    score: 0,
                                    total_time: 0,
                                    rating: 'timeout',
                                    interpretation: 'Database test timed out'
                                };
                            } else if (step === 'concurrency') {
                                fallbackData = {
                                    incomplete: true,
                                    reason: 'timeout',
                                    score: 0,
                                    total_time: 0,
                                    concurrent_operations: 0,
                                    rating: 'timeout',
                                    interpretation: 'Concurrency test timed out'
                                };
                            }
                            
                            stepResults[step] = fallbackData;
                            
                            currentStepIndex++;
                            if (currentStepIndex < steps.length) {
                                setTimeout(function() {
                                    executeStep(steps[currentStepIndex]);
                                }, 500);
                            } else {
                                finishStepBasedTests();
                            }
                        } else {
                            handleStepError(step, { message: 'Network error: ' + error });
                        }
                    }
                });
            }
            
            // Handle step execution errors
            function handleStepError(step, errorData) {
                console.error('❌ DiveWP: Handling error for step: ' + step, errorData);
                
                // Safely extract error message
                var errorMessage = 'Unknown error';
                if (errorData) {
                    if (typeof errorData === 'string') {
                        errorMessage = errorData;
                    } else if (errorData.message) {
                        errorMessage = errorData.message;
                    } else if (errorData.error) {
                        errorMessage = errorData.error;
                    }
                }
                
                // Mark step as failed
                stepResults[step] = { 
                    incomplete: true, 
                    error: true, 
                    reason: errorMessage
                };
                
                // Try to continue with next step
                currentStepIndex++;
                if (currentStepIndex < steps.length) {
                    console.log('🚀 DiveWP: Continuing to next step after error...');
                    setTimeout(function() {
                        executeStep(steps[currentStepIndex]);
                    }, 1000);
                } else {
                    finishStepBasedTests();
                }
            }
            
            // Finish step-based testing and display results
            function finishStepBasedTests() {
                console.log('🚀 DiveWP: === FINISHING STEP-BASED TESTS ===');
                console.log('🚀 DiveWP: Step results:', stepResults);
                
                updateProgress(steps.length, steps.length, 'Finalizing results...');
                
                // Extract test results directly from steps (no mapping needed)
                var performanceData = {};
                var resourcesData = {};
                var databaseData = {};
                var databaseBenchmarkData = {};
                var concurrencyData = {};
                
                // Performance test data (from DiveWP_Performance_Tests)
                if (stepResults.performance) {
                    performanceData = stepResults.performance;
                    // Ensure required fields
                    if (!performanceData.rating) {
                        performanceData.rating = getRatingFromScore(performanceData.score || 0);
                    }
                }
                
                // Resources test data (from DiveWP_Resource_Tests)
                if (stepResults.resources) {
                    resourcesData = stepResults.resources;
                    // Ensure required fields for Resources card
                    if (!resourcesData.rating) {
                        resourcesData.rating = getRatingFromScore(resourcesData.overall_score || 0);
                    }
                    // Ensure we have a score field for the card display
                    if (!resourcesData.score && resourcesData.overall_score) {
                        resourcesData.score = resourcesData.overall_score;
                    }
                }
                
                // Database test data (from DiveWP_Database_Tests)
                if (stepResults.database) {
                    databaseData = stepResults.database;
                    // Ensure required fields
                    if (!databaseData.rating) {
                        databaseData.rating = getRatingFromScore(databaseData.score || 0);
                    }
                }
                
                // Database Benchmark test data (from DiveWP_Database_Benchmark)
                if (stepResults.database_benchmark) {
                    databaseBenchmarkData = stepResults.database_benchmark;
                    // Ensure required fields
                    if (!databaseBenchmarkData.rating) {
                        databaseBenchmarkData.rating = getRatingFromScore(databaseBenchmarkData.score || 0);
                    }
                }
                
                // Concurrency test data (from DiveWP_Concurrency_Tests)
                if (stepResults.concurrency) {
                    concurrencyData = stepResults.concurrency;
                    // Ensure required fields
                    if (!concurrencyData.rating) {
                        concurrencyData.rating = getRatingFromScore(concurrencyData.score || 0);
                    }
                }
                
                // Calculate overall score from all available test results
                var allScores = [];
                if (performanceData.score) allScores.push(performanceData.score);
                if (resourcesData.overall_score || resourcesData.score) allScores.push(resourcesData.overall_score || resourcesData.score);
                if (databaseData.score) allScores.push(databaseData.score);
                if (databaseBenchmarkData.score) allScores.push(databaseBenchmarkData.score);
                if (concurrencyData.score) allScores.push(concurrencyData.score);
                
                var overallScore = allScores.length > 0 ? Math.round(allScores.reduce((a, b) => a + b, 0) / allScores.length) : 0;
                
                // Create final results object with expected structure
                var finalResults = {
                    step_based_execution: true,
                    incomplete: allScores.length === 0,
                    overall_score: overallScore,
                    rating: getRatingFromScore(overallScore),
                    tests: {
                        performance: performanceData,
                        resources: resourcesData,
                        database: databaseData,
                        database_benchmark: databaseBenchmarkData,
                        concurrency: concurrencyData
                    },
                    // Keep original step data for debugging
                    step_results: stepResults,
                    completed_steps: Object.keys(stepResults),
                    execution_type: 'step_based',
                    total_steps: steps.length,
                    successful_steps: Object.keys(stepResults).filter(step => 
                        stepResults[step] && !stepResults[step].incomplete && !stepResults[step].error
                    ).length
                };
                
                console.log('🚀 DiveWP: Final results prepared:', finalResults);
                
                // Display the results
                displayEvaluationResults(finalResults);
                
                // Hide progress and re-enable button
                $('#evaluation-progress').hide();
                $('#run-hosting-evaluation').prop('disabled', false);
            }
            
            // Start the step-based execution
            executeStep(steps[0]);
        }
        
        // Helper function to get rating from score
        function getRatingFromScore(score) {
            if (score === 'timeout' || score === 0) return 'timeout';
            if (score >= 80) return 'excellent';
            if (score >= 60) return 'good';
            if (score >= 40) return 'fair';
            if (score >= 20) return 'poor';
            return 'very_poor';
        }
        
        console.log('🚀 DiveWP: All event handlers and functions initialized');
        
    });
    
    console.log('🚀 DiveWP: Hosting evaluation JavaScript loaded completely');
    
})(jQuery); 