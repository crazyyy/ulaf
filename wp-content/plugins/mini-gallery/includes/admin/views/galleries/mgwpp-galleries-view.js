jQuery(document).ready(function ($) {
    // Check if we're on a galleries page (has either gallery grid or empty state)
    const isGalleriesPage = $('.mgwpp-gallery-grid').length || $('#mgwpp-open-create-modal').length;
    if (!isGalleriesPage) {
        return;
    }

    // 1. COPY SHORTCODE FUNCTIONALITY
    // ==============================

    // Unified copy function
    const copyToClipboard = (text, $element) => {
        // Try modern Clipboard API first
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text)
                .then(() => showFeedback($element, true))
                .catch(() => showFeedback($element, false));
        }
        // Fallback for older browsers
        else {
            try {
                const $temp = $('<textarea>').val(text).appendTo('body').select();
                const success = document.execCommand('copy');
                $temp.remove();
                showFeedback($element, success);
            } catch (err) {
                showFeedback($element, false);
            }
        }
    };

    // Event delegation for copy actions
    $(document)
        .on('click', '.mgwpp-copy-shortcode', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const text = $btn.data('clipboard-text');

            copyToClipboard(text, $btn);
        });

    // Modified feedback for button icons
    const showFeedback = ($btn, success) => {
        const $icon = $btn.find('.dashicons');
        const originalClass = $icon.attr('class');

        if (success) {
            $icon.removeClass('dashicons-admin-page').addClass('dashicons-yes');
            $btn.css('color', 'var(--mg-success)');
        } else {
            $icon.removeClass('dashicons-admin-page').addClass('dashicons-warning');
            $btn.css('color', 'var(--mg-danger)');
        }

        setTimeout(() => {
            $icon.attr('class', originalClass);
            $btn.css('color', '');
        }, 1500);
    };

    // 2. BULK ACTIONS FUNCTIONALITY
    // =============================

    // Cache DOM elements
    const $bulkContainer = $('.mgwpp-bulk-actions-bar');
    const $selectAllContainer = $('.mgwpp-select-all-container');
    const $bulkCheckboxes = $('.mgwpp-bulk-checkbox');
    const $bulkActionSelect = $('#mgwpp-bulk-action');
    const $selectedCount = $('#mgwpp-selected-count');

    // Toggle bulk UI visibility
    const toggleBulkActions = () => {
        const checkedCount = $bulkCheckboxes.filter(':checked').length;

        $bulkContainer.toggle(checkedCount > 0);
        $selectAllContainer.toggle($bulkCheckboxes.length > 0);

        if (checkedCount > 0) {
            $selectedCount.text(
                mgwppAdmin.i18n.selectedCount.replace('%d', checkedCount)
            );
        }
    };

    // Initialize bulk UI
    toggleBulkActions();

    // Event handlers
    $(document)
        .on('change', '.mgwpp-bulk-checkbox', toggleBulkActions)
        .on('change', '#mgwpp-toggle-all', function () {
            const isChecked = $(this).prop('checked');
            $bulkCheckboxes.prop('checked', isChecked);
            toggleBulkActions();
        })
        .on('click', '#mgwpp-apply-bulk-action', function () {
            if ($bulkActionSelect.val() !== 'delete') {
                return;
            }

            const galleryIds = $bulkCheckboxes.filter(':checked').map(function () {
                return this.value;
            }).get();

            if (galleryIds.length === 0) {
                return;
            }

            if (!confirm(mgwppAdmin.i18n.confirmDelete)) {
                return;
            }

            $.post(mgwppAdmin.ajaxUrl, {
                action: 'mgwpp_bulk_delete_galleries',
                ids: galleryIds,
                nonce: mgwppAdmin.nonce
            })
                .then(response => response.success ? location.reload() : alert(mgwppAdmin.i18n.deleteError))
                .catch(() => alert(mgwppAdmin.i18n.deleteError));
        });
    // 3. PREVIEW MODAL FUNCTIONALITY
    // =============================
    const $previewModal = $('#mgwpp-preview-gallery-modal');
    const $previewIframe = $('#mgwpp-preview-iframe');
    const $previewLoader = $('.mgwpp-preview-loader');

    $(document).on('click', '.mgwpp-preview-gallery', function (e) {
        e.preventDefault();
        const galleryId = $(this).data('id');

        $previewModal.css('display', 'flex').hide().fadeIn(200);
        $previewLoader.show();
        $previewIframe.hide().attr('src', '');

        $.post(mgwppAdmin.ajaxUrl, {
            action: 'mgwpp_preview_gallery',
            gallery_id: galleryId,
            nonce: mgwppAdmin.nonce
        })
            .done(function (response) {
                if (response.success && response.data.preview_url) {
                    $previewIframe.attr('src', response.data.preview_url);
                    $previewIframe.on('load', function () {
                        $previewLoader.hide();
                        $previewIframe.show();
                    });
                } else {
                    alert(response.data?.message || 'Error generating preview');
                    $previewModal.fadeOut(200);
                }
            })
            .fail(function () {
                alert('Connection error');
                $previewModal.fadeOut(200);
            });
    });

    $(document).on('click', '.mgwpp-modal-close, .mgwpp-modal-overlay', function () {
        $('.mgwpp-modal').fadeOut(200);
        $previewIframe.attr('src', '');
    });

    // 4. CREATION MODAL FUNCTIONALITY
    // ==============================
    const $createModal = $('#mgwpp-create-gallery-modal');
    const $galleryTypeSelect = $('#mgwpp-create-gallery-type');
    const $mediaLabel = $('#mgwpp-media-label');
    const $uploadBtnText = $('#mgwpp-upload-btn-text');
    const $threedHint = $('#mgwpp-3d-hint');

    $('#mgwpp-open-create-modal').on('click', function () {
        $createModal.css('display', 'flex').hide().fadeIn(200);
    });

    $galleryTypeSelect.on('change', function () {
        const type = $(this).val();
        if (type === '3d_model_carousel') {
            $mediaLabel.text(mgwppAdmin.i18n.selectModel || 'Select models');
            $uploadBtnText.text(mgwppAdmin.i18n.selectModel || 'Select models');
            $threedHint.show();
        } else {
            $mediaLabel.text(mgwppAdmin.i18n.selectImages || 'Gallery images');
            $uploadBtnText.text(mgwppAdmin.i18n.selectImages || 'Select images');
            $threedHint.hide();
        }
    });

    // 3D Model Validation on submit
    $('#mgwpp-create-gallery-form').on('submit', function (e) {
        const type = $galleryTypeSelect.val();
        const selectedMedia = $('#mgwpp-create-selected-media').val();
        const mediaCount = selectedMedia ? selectedMedia.split(',').length : 0;

        if (type === '3d_model_carousel') {
            if (mediaCount < 1) {
                e.preventDefault();
                alert(mgwppAdmin.i18n.threedRequired || 'Please select at least one 3D model.');
                return false;
            }
            if (mediaCount > 12) {
                e.preventDefault();
                alert('Maximum 12 models allowed for 3D Carousel.');
                return false;
            }

            // Size validation (this is a bit tricky here since we don't have the size easily available
            // but we showed a warning in mgwpp-admin-scripts.js. If we want to hard block 100MB here,
            // we should have shared that state. For now, let's rely on the warning/error message in the debug handler.)
        }
    });

    // 4. THEME SUPPORT
    // ================
    // Listen for theme changes from the global toggle
    const updateThemeStatus = () => {
        const isDark = $('body').hasClass('mgwpp-dark-mode');
        // If we need to pass this info to the iframe, we can append a param
    };

    // Initial check
    updateThemeStatus();

    // Watch for class changes on body
    const observer = new MutationObserver(updateThemeStatus);
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
});