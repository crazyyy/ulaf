/**
 * Authority Mailer Dashboard Chart
 * 
 * Interactive bar chart for email analytics with daily/weekly/monthly views
 * 
 * @package Authority_Mailer
 * @since 1.0.0
 */

(function($) {
    'use strict';

    let chartInstance = null;
    let currentPeriod = 'daily';

    /**
     * Format large numbers with K/M suffix
     * 
     * @param {number} value - The value to format
     * @returns {string} Formatted string
     */
    function formatAxisNumber(value) {
        if (value >= 1000000) {
            return (value / 1000000).toFixed(1) + 'M';
        } else if (value >= 1000) {
            return (value / 1000).toFixed(1) + 'K';
        }
        return value.toString();
    }

    /**
     * Initialize the dashboard chart
     */
    function initDashboardChart() {
        if (typeof Chart === 'undefined' || typeof authorityMailerChartData === 'undefined') {
            console.warn('Chart.js or chart data not loaded');
            return;
        }

        const canvas = document.getElementById('authorityMailerEmailChart');
        if (!canvas) {
            console.warn('Chart canvas element not found');
            return;
        }

        // Initial chart render
        renderChart('daily');

        // Setup toggle buttons
        setupToggleButtons();
    }

    /**
     * Setup toggle button click handlers
     */
    function setupToggleButtons() {
        $('.am-chart-toggle').on('click', function() {
            const period = $(this).data('period');
            
            // Update active state and ARIA attributes
            $('.am-chart-toggle').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');
            
            // Update chart
            currentPeriod = period;
            renderChart(period);
        });
    }

    /**
     * Render the chart for a specific period
     * 
     * @param {string} period - 'daily', 'weekly', or 'monthly'
     */
    function renderChart(period) {
        const data = authorityMailerChartData[period] || [];
        const strings = authorityMailerChartData.strings || {};

        // Extract labels and datasets
        const labels = data.map(item => item.label);
        const successData = data.map(item => item.success || 0);
        const failedData = data.map(item => item.failed || 0);
        const pendingData = data.map(item => item.pending || 0);
        const totalData = data.map(item => item.total || 0);

        // Destroy existing chart
        if (chartInstance) {
            chartInstance.destroy();
        }

        // Calculate max value for Y-axis
        const maxValue = Math.max(...totalData, 1);
        const suggestedMax = Math.ceil(maxValue * 1.1); // Add 10% padding

        // Create new chart
        const ctx = document.getElementById('authorityMailerEmailChart').getContext('2d');
        
        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: strings.success || 'Success',
                        data: successData,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: strings.failed || 'Failed',
                        data: failedData,
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: strings.pending || 'Pending',
                        data: pendingData,
                        backgroundColor: 'rgba(245, 158, 11, 0.8)',
                        borderColor: 'rgb(245, 158, 11)',
                        borderWidth: 1,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 12,
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                            },
                            color: '#6B7280',
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        suggestedMax: suggestedMax,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false,
                        },
                        ticks: {
                            font: {
                                size: 12,
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                            },
                            color: '#6B7280',
                            precision: 0,
                            callback: function(value) {
                                return formatAxisNumber(value);
                            }
                        },
                        title: {
                            display: true,
                            text: strings.emailsSent || 'Emails Sent',
                            color: '#6B7280',
                            font: {
                                size: 13,
                                weight: '500',
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        footerColor: '#10B981',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 14,
                        boxPadding: 6,
                        usePointStyle: true,
                        callbacks: {
                            title: function(context) {
                                // Get the full date if available
                                const dataIndex = context[0].dataIndex;
                                const dataPoint = data[dataIndex];
                                return dataPoint.date || context[0].label;
                            },
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y || 0;
                                const dataIndex = context.dataIndex;
                                const total = totalData[dataIndex] || 1;
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return label + ': ' + value.toLocaleString() + ' (' + percentage + '%)';
                            },
                            afterBody: function(context) {
                                const dataIndex = context[0].dataIndex;
                                const total = totalData[dataIndex] || 0;
                                return '\n' + (strings.total || 'Total') + ': ' + total.toLocaleString();
                            },
                            footer: function(context) {
                                const dataIndex = context[0].dataIndex;
                                const total = totalData[dataIndex] || 0;
                                const success = successData[dataIndex] || 0;
                                if (total > 0) {
                                    const successRate = Math.round((success / total) * 100);
                                    return (strings.success || 'Success') + ' Rate: ' + successRate + '%';
                                }
                                return '';
                            }
                        },
                        titleFont: {
                            size: 14,
                            weight: '600',
                        },
                        bodyFont: {
                            size: 13,
                        },
                        footerFont: {
                            size: 12,
                            weight: '600',
                        }
                    }
                },
                animation: {
                    duration: 750,
                    easing: 'easeInOutQuart',
                }
            }
        });
    }

    /**
     * Handle window resize with debounce
     */
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (chartInstance) {
                chartInstance.resize();
            }
        }, 250);
    });

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        // Check if Chart.js is loaded
        if (typeof Chart !== 'undefined') {
            initDashboardChart();
        } else {
            // Retry after a short delay if Chart.js hasn't loaded yet from CDN
            setTimeout(function() {
                if (typeof Chart !== 'undefined') {
                    initDashboardChart();
                } else {
                    console.warn('Chart.js from CDN failed to load. Chart will not be displayed.');
                }
            }, 1000);
        }
    });

})(jQuery);
