/**
 * Mini Gallery Analytics - Frontend Tracking
 */
(function ($) {
    'use strict';

    const MG_Analytics = {
        init: function () {
            this.trackViews();
            this.bindCTAClicks();
            this.bindCTASubmissions();
        },

        // Track gallery view
        trackViews: function () {
            $('.mgwpp-canvas-slider').each(function () {
                const galleryId = $(this).attr('id').replace('mgwpp-slider-', '');
                MG_Analytics.sendEvent('view', {
                    gallery_id: galleryId,
                    gallery_type: 'canvas'
                });
            });
        },

        // Track clicks on CTA buttons
        bindCTAClicks: function () {
            $(document).on('click', '.mgwpp-cta', function (e) {
                const $btn = $(this);
                const galleryId = $btn.closest('.mgwpp-canvas-slider').attr('id')?.replace('mgwpp-slider-', '') || 0;

                MG_Analytics.sendEvent('cta_click', {
                    gallery_id: galleryId,
                    gallery_type: 'canvas',
                    event_label: $btn.text().trim() || $btn.attr('href')
                });
            });
        },

        // Track form submissions (if applicable)
        bindCTASubmissions: function () {
            $(document).on('submit', 'form.mgwpp-cta-form', function (e) {
                const $form = $(this);
                const galleryId = $form.closest('.mgwpp-canvas-slider').attr('id')?.replace('mgwpp-slider-', '') || 0;

                MG_Analytics.sendEvent('cta_submit', {
                    gallery_id: galleryId,
                    gallery_type: 'canvas',
                    event_label: $form.attr('id') || 'unnamed_form'
                });
            });
        },

        // Send event to GA4 and local AJAX
        sendEvent: function (eventType, data) {
            // 1. Send to GA4 if available
            if (typeof gtag === 'function' && mgwppAnalytics.ga4_id) {
                gtag('event', eventType, {
                    'event_category': 'Mini Gallery',
                    'event_label': data.event_label || '',
                    'gallery_id': data.gallery_id,
                    'gallery_type': data.gallery_type
                });
            }

            // 2. Send to local tracker (AJAX)
            $.ajax({
                url: mgwppAnalytics.ajax_url,
                type: 'POST',
                data: {
                    action: 'mgwpp_track_event',
                    nonce: mgwppAnalytics.nonce,
                    event_type: eventType,
                    gallery_id: data.gallery_id,
                    gallery_type: data.gallery_type,
                    event_label: data.event_label || ''
                }
            });
        }
    };

    $(document).ready(function () {
        MG_Analytics.init();
    });

})(jQuery);
