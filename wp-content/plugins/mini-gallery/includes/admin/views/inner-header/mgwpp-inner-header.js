jQuery(document).ready(function ($) {
    // Theme Toggle Handler
    const themeToggle = $('#mgwpp-theme-toggle');
    const loaderOverlay = $('.mgwpp-loader-overlay');

    // Dashicon classes for sun and moon
    const sunIcon = 'dashicons-admin-appearance';  // Light mode icon (sun/appearance)
    const moonIcon = 'dashicons-buddicons-activity'; // Dark mode icon (moon/night)

    // Apply theme from localStorage immediately on DOM ready to avoid flicker
    applyLocalTheme();

    if (themeToggle.length) {
        themeToggle.on('click', function (e) {
            e.preventDefault();

            const currentTheme = themeToggle.data('current-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            // 1. Immediate UI Update
            applyTheme(newTheme);

            // 2. Local Persistence (Fastest)
            localStorage.setItem('mgwpp-theme', newTheme);

            // 3. Server Sync (Persistent across devices)
            $.ajax({
                url: mgwppHeader.ajaxurl, // Using mgwppHeader localized in MGWPP_Admin_Assets
                method: 'POST',
                data: {
                    action: 'mgwpp_toggle_theme',
                    security: mgwppHeader.nonce,
                    theme: newTheme
                },
                success: function (response) {
                    if (!response.success) {
                        console.warn('Theme sync failed:', response.data?.message);
                    }
                },
                error: function (xhr) {
                    console.warn('Theme sync AJAX error:', xhr.status);
                }
            });
        });
    }

    function applyTheme(theme) {
        $('body').toggleClass('mgwpp-dark-mode', theme === 'dark');
        // Update the dashicon class
        const icon = themeToggle.find('.dashicons');
        icon.removeClass(sunIcon + ' ' + moonIcon);
        icon.addClass(theme === 'dark' ? moonIcon : sunIcon);
        themeToggle.data('current-theme', theme);
    }

    function applyLocalTheme() {
        const savedTheme = localStorage.getItem('mgwpp-theme');
        if (savedTheme) {
            $('body').toggleClass('mgwpp-dark-mode', savedTheme === 'dark');
            if (themeToggle.length) {
                const icon = themeToggle.find('.dashicons');
                icon.removeClass(sunIcon + ' ' + moonIcon);
                icon.addClass(savedTheme === 'dark' ? moonIcon : sunIcon);
                themeToggle.data('current-theme', savedTheme);
            }
        }
    }

    // Removed the aggressive global $(document).ajaxSend loader 
    // which was causing the page to "freeze" during background tasks.
});