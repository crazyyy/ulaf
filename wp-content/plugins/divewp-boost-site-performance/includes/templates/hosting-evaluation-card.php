<?php
/**
 * Hosting Evaluation Card Template
 * 
 * Unified template for displaying hosting evaluation results in a user-friendly format
 * 
 * @package DiveWP_Boost_Site_Performance
 * @since 2.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template file with local variables only

// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template pattern for passing variables
// Extract variables for template use
extract($args);

// Set defaults
$test_name = $test_name ?? 'Unknown Test';
$icon = $icon ?? '⚡';
$score = $score ?? 0;
$rating = $rating ?? 'unknown';
$total_time = $total_time ?? 0;
$business_impact = $business_impact ?? array();
$sub_tests = $sub_tests ?? array();
$summary = $summary ?? '';
$recommendations = $recommendations ?? array();
$technical_details = $technical_details ?? array();

// Generate unique ID for this card
$card_id = 'hosting-card-' . sanitize_title($test_name);

// Rating colors and labels - matching Status Legend from sidebar
$rating_config = array(
    'excellent' => array('color' => '#10b981', 'label' => 'EXCELLENT'), // Green - "Optimal"
    'good' => array('color' => '#3b82f6', 'label' => 'GOOD'),           // Blue - "Info" 
    'fair' => array('color' => '#f59e0b', 'label' => 'FAIR'),           // Yellow - "Warning"
    'average' => array('color' => '#f59e0b', 'label' => 'FAIR'),         // Legacy fallback for 'average' (same as fair)
    'poor' => array('color' => '#ef4444', 'label' => 'POOR'),           // Orange/Red - "Critical"
    'critical' => array('color' => '#dc2626', 'label' => 'CRITICAL'),   // Deep Red - "Critical"
    'skipped' => array('color' => '#6b7280', 'label' => 'SKIPPED'),     // Gray
    'error' => array('color' => '#dc2626', 'label' => 'ERROR'),         // Deep Red
    'unknown' => array('color' => '#6b7280', 'label' => 'UNKNOWN')      // Gray fallback
);

$current_rating = $rating_config[$rating] ?? $rating_config['unknown'];
?>

<div class="hosting-evaluation-card" id="<?php echo esc_attr($card_id); ?>">
    
    <!-- Card Header -->
    <div class="card-header">
        <div class="card-header-left">
            <span class="test-icon"><?php echo esc_html($icon); ?></span>
            <h3 class="card-title"><?php echo esc_html($test_name); ?></h3>
        </div>
        <div class="card-header-right">
            <?php $rating_class = isset($rating_config[$rating]) ? $rating : 'unknown'; ?>
            <span class="score rating-<?php echo esc_attr($rating_class); ?>"><?php echo esc_html($score); ?></span>
            <span class="rating-pill rating-<?php echo esc_attr($rating_class); ?>"><?php echo esc_html($current_rating['label']); ?></span>
        </div>
    </div>



    <!-- Status Indicator Section (for enhanced categories like concurrency) -->
    <?php if (!empty($status_indicator) && !empty($issue_explanation)): ?>
    <div class="test-issues-section <?php echo esc_attr($status_indicator['type']); ?>">
        <div class="issue-header">
            <span class="issue-icon"><?php echo esc_html($status_indicator['icon']); ?></span>
            <strong><?php echo esc_html__('Test Status:', 'divewp-boost-site-performance'); ?> <?php echo esc_html($status_indicator['label']); ?></strong>
        </div>
        <div class="issue-details">
            <p><?php echo esc_html($issue_explanation); ?></p>
            <?php if (!empty($completion_percentage) && $completion_percentage < 100): ?>
            <div class="completion-percentage">
                <span class="completion-label"><?php echo esc_html__('Completion:', 'divewp-boost-site-performance'); ?></span>
                <span class="completion-value"><?php echo esc_html($completion_percentage); ?>%</span>
                <div class="completion-bar">
                    <div class="completion-fill" style="width: <?php echo esc_attr($completion_percentage); ?>%;"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sub-tests (Expandable) -->
    <?php if (!empty($sub_tests)): ?>
    <div class="sub-tests-section">
        <button type="button" class="toggle-details" onclick="toggleSection('<?php echo esc_attr($card_id); ?>-details')">
            <span class="dashicons dashicons-arrow-right-alt2 toggle-icon"></span>
            <span class="toggle-text"><?php echo esc_html__('Show Test Details', 'divewp-boost-site-performance'); ?> (<?php echo count($sub_tests); ?> tests)</span>
        </button>
        
        <div id="<?php echo esc_attr($card_id); ?>-details" class="sub-tests-details">
            
            <!-- Enhanced Performance Interpretation (for categories with performance_interpretations flag) -->
            <?php if (!empty($performance_interpretations) && !empty($baseline_comparison)): ?>
            <div class="performance-interpretation-section">
                <h4 class="section-heading"><?php echo esc_html__('Performance Analysis', 'divewp-boost-site-performance'); ?></h4>
                
                <!-- Baseline Comparison Table -->
                <div class="baseline-comparison-table">
                    <h5 class="subsection-heading"><?php echo esc_html__('Hosting Quality Indicators', 'divewp-boost-site-performance'); ?></h5>
                    <table class="benchmark-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Test Type', 'divewp-boost-site-performance'); ?></th>
                                <th><?php echo esc_html__('Excellent', 'divewp-boost-site-performance'); ?></th>
                                <th><?php echo esc_html__('Good', 'divewp-boost-site-performance'); ?></th>
                                <th><?php echo esc_html__('Poor', 'divewp-boost-site-performance'); ?></th>
                                <th><?php echo esc_html__('Your Result', 'divewp-boost-site-performance'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($baseline_comparison as $test_id => $baseline_data): ?>
                            <?php 
                                // Find corresponding sub-test result
                                $user_result = '';
                                $result_class = 'unknown';
                                foreach ($sub_tests as $sub_test) {
                                    if (stripos($sub_test['name'], str_replace('_', ' ', $test_id)) !== false) {
                                        $user_result = $sub_test['time'];
                                        // Determine result quality based on time (simplified for now)
                                        $time_value = floatval($user_result);
                                        if ($time_value <= 5) {
                                            $result_class = 'excellent';
                                        } elseif ($time_value <= 15) {
                                            $result_class = 'good';
                                        } else {
                                            $result_class = 'poor';
                                        }
                                        break;
                                    }
                                }
                            ?>
                            <tr>
                                <td><?php echo esc_html($baseline_data['test_type']); ?></td>
                                <td><?php echo esc_html($baseline_data['thresholds']['excellent']); ?></td>
                                <td><?php echo esc_html($baseline_data['thresholds']['good']); ?></td>
                                <td><?php echo esc_html($baseline_data['thresholds']['poor']); ?></td>
                                <td class="your-result <?php echo esc_attr($result_class); ?>">
                                    <?php echo esc_html($user_result); ?>
                                    <?php if ($result_class === 'excellent'): ?>
                                        ⚡
                                    <?php elseif ($result_class === 'poor'): ?>
                                        ⏱️
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Score Impact Analysis (for categories with score_impact_analysis) -->
            <?php if (!empty($score_impact_analysis)): ?>
            <div class="score-impact-section">
                <h4 class="section-heading"><?php echo esc_html__('Score Analysis', 'divewp-boost-site-performance'); ?></h4>
                
                <div class="score-breakdown">
                    <div class="score-total">
                        <span class="score-label"><?php echo esc_html__('Total Score:', 'divewp-boost-site-performance'); ?></span>
                        <span class="score-value"><?php echo esc_html($score_impact_analysis['total_score']); ?>/100</span>
                    </div>
                    
                    <!-- Positive Contributions -->
                    <?php if (!empty($score_impact_analysis['positive_contributions'])): ?>
                    <div class="contributions-section positive">
                        <h5 class="contribution-heading positive"><?php echo esc_html__('✅ Positive Contributions', 'divewp-boost-site-performance'); ?></h5>
                        <?php foreach ($score_impact_analysis['positive_contributions'] as $contribution): ?>
                        <div class="contribution-item positive">
                            <span class="test-name"><?php echo esc_html($contribution['test_name']); ?></span>
                            <span class="contribution-points positive">+<?php echo esc_html($contribution['contribution_points']); ?> points</span>
                            <span class="contribution-reason"><?php echo esc_html($contribution['impact_reason']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Negative Contributions -->
                    <?php if (!empty($score_impact_analysis['negative_contributions'])): ?>
                    <div class="contributions-section negative">
                        <h5 class="contribution-heading negative"><?php echo esc_html__('⚠️ Issues Affecting Score', 'divewp-boost-site-performance'); ?></h5>
                        <?php foreach ($score_impact_analysis['negative_contributions'] as $contribution): ?>
                        <div class="contribution-item negative">
                            <span class="test-name"><?php echo esc_html($contribution['test_name']); ?></span>
                            <span class="contribution-points negative">-<?php echo esc_html(abs($contribution['contribution_points'])); ?> points</span>
                            <span class="contribution-reason"><?php echo esc_html($contribution['impact_reason']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Improvement Potential -->
                    <?php if (!empty($score_impact_analysis['improvement_potential']['total_potential_gain']) && $score_impact_analysis['improvement_potential']['total_potential_gain'] > 0): ?>
                    <div class="improvement-potential">
                        <h5 class="improvement-heading"><?php echo esc_html__('🚀 Improvement Potential', 'divewp-boost-site-performance'); ?></h5>
                        <div class="potential-summary">
                            <span class="potential-label"><?php echo esc_html__('Potential Score Improvement:', 'divewp-boost-site-performance'); ?></span>
                            <span class="potential-value">+<?php echo esc_html($score_impact_analysis['improvement_potential']['total_potential_gain']); ?> points</span>
                        </div>
                        <?php if (!empty($score_impact_analysis['improvement_potential']['individual_improvements'])): ?>
                        <div class="individual-improvements">
                            <?php foreach ($score_impact_analysis['improvement_potential']['individual_improvements'] as $improvement): ?>
                            <div class="improvement-item <?php echo esc_attr($improvement['priority']); ?>">
                                <span class="improvement-badge <?php echo esc_attr($improvement['priority']); ?>">
                                    <?php echo esc_html($improvement['priority'] === 'high' ? '🔴 HIGH PRIORITY' : '🟡 MEDIUM PRIORITY'); ?>
                                </span>
                                <span class="improvement-test"><?php echo esc_html($improvement['test_name']); ?></span>
                                <span class="improvement-gain">+<?php echo esc_html($improvement['potential_gain']); ?> points</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Hosting Quality Assessment (for categories with hosting_quality_assessment) -->
            <?php if (!empty($hosting_quality_assessment)): ?>
            <div class="hosting-quality-section">
                <h4 class="section-heading"><?php echo esc_html__('Hosting Quality Assessment', 'divewp-boost-site-performance'); ?></h4>
                
                <div class="quality-assessment-card">
                    <div class="assessment-header">
                        <h5 class="assessment-title">
                            <?php echo esc_html__('Your Hosting Quality:', 'divewp-boost-site-performance'); ?>
                            <span class="assessment-rating <?php echo esc_attr($hosting_quality_assessment['overall_rating']); ?>">
                                <?php echo esc_html(strtoupper($hosting_quality_assessment['overall_rating'])); ?>
                            </span>
                        </h5>
                        <div class="assessment-score"><?php echo esc_html($hosting_quality_assessment['score']); ?>/100</div>
                    </div>
                    
                    <div class="assessment-interpretation">
                        <p><?php echo esc_html($hosting_quality_assessment['interpretation']); ?></p>
                    </div>
                    
                    <?php if (!empty($hosting_quality_assessment['recommendations'])): ?>
                    <div class="assessment-recommendations">
                        <h6 class="recommendations-title"><?php echo esc_html__('Recommended Actions:', 'divewp-boost-site-performance'); ?></h6>
                        <ul class="recommendations-list">
                            <?php foreach ($hosting_quality_assessment['recommendations'] as $recommendation): ?>
                            <li><?php echo esc_html($recommendation); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Standard Sub-tests Grid -->
            <div class="sub-tests-grid">
                <?php foreach ($sub_tests as $sub_test): ?>
                <div class="sub-test-item">
                    <div>
                        <div class="sub-test-name"><?php echo esc_html($sub_test['name']); ?></div>
                        <?php if (!empty($sub_test['description'])): ?>
                        <div class="sub-test-description"><?php echo esc_html($sub_test['description']); ?></div>
                        <?php endif; ?>
                        
                        <!-- Enhanced Performance Interpretation (per sub-test) -->
                        <?php if (!empty($sub_test['performance_interpretation'])): ?>
                        <div class="sub-test-performance-interpretation">
                            <div class="performance-rating <?php echo esc_attr($sub_test['performance_interpretation']['rating']); ?>">
                                <span class="rating-badge">
                                    <?php 
                                    $rating_icons = array(
                                        'excellent' => '⚡',
                                        'good' => '✅', 
                                        'average' => '🟡',
                                        'poor' => '⚠️',
                                        'critical' => '🔴',
                                        'timeout' => '⏱️',
                                        'partial' => '⚠️',
                                        'error' => '❌'
                                    );
                                    $icon = $rating_icons[$sub_test['performance_interpretation']['rating']] ?? '❓';
                                    echo esc_html($icon) . ' ' . esc_html($sub_test['performance_interpretation']['rating_label']);
                                    ?>
                                </span>
                                <?php if (!empty($sub_test['performance_interpretation']['performance_context'])): ?>
                                <span class="performance-context"><?php echo esc_html($sub_test['performance_interpretation']['performance_context']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($sub_test['performance_interpretation']['explanation'])): ?>
                            <div class="performance-explanation">
                                <p><?php echo esc_html($sub_test['performance_interpretation']['explanation']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($sub_test['performance_interpretation']['hosting_quality'])): ?>
                            <div class="hosting-quality-indicator">
                                <span class="quality-label"><?php echo esc_html__('Hosting Quality:', 'divewp-boost-site-performance'); ?></span>
                                <span class="quality-value"><?php echo esc_html($sub_test['performance_interpretation']['hosting_quality']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="sub-test-right">
                        <?php if (!empty($sub_test['operations'])): ?>
                        <div class="sub-test-operations">
                            <?php echo wp_kses($sub_test['operations'], array('br' => array())); ?>
                            <?php if (!empty($sub_test['status_badge'])): ?>
                            <span class="status-badge <?php echo esc_attr($sub_test['status_badge']['type']); ?>">
                                <?php echo esc_html($sub_test['status_badge']['icon']); ?> <?php echo esc_html($sub_test['status_badge']['label']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($sub_test['time'])): ?>
                        <div class="sub-test-time"><?php echo wp_kses($sub_test['time'], array('span' => array('style' => array()))); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Performance Summary -->
    <div class="performance-summary <?php echo esc_attr($rating); ?>">
        <?php if (!empty($total_time)): ?>
        <div class="performance-time-row">
            <span class="performance-time-label"><?php echo esc_html__('Total Processing Time:', 'divewp-boost-site-performance'); ?></span>
            <span class="performance-time-value"><?php echo esc_html($total_time); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($summary)): ?>
        <div class="summary-text">
            💡 <?php echo esc_html($summary); ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recommendations (if any) -->
    <?php if (!empty($recommendations)): ?>
    <div class="recommendations">
        <h4 class="recommendations-heading"><?php echo esc_html__('Recommendations:', 'divewp-boost-site-performance'); ?></h4>
        <?php foreach ($recommendations as $recommendation): ?>
        <div class="recommendation-item">
            <span class="recommendation-icon">⚠️</span>
            <span class="recommendation-text"><?php echo esc_html($recommendation); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>


</div>

<!-- Toggle function now centralized in divewp-admin.js --> 