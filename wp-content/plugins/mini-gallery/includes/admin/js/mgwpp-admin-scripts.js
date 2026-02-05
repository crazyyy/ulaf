// mg-admin-scripts.js
jQuery(document).ready(function ($) {
    // **************
    // INITIALIZATIONS
    // **************
    let mediaFrame;

    // Main initialization controller
    function initDashboard() {
        initColorPickers();
        initMediaUpload();
        initFormHandlers();
        initGalleryPreviews();
        initProNoticeDismissal();
    }

    // *****************
    // CORE FUNCTIONALITY
    // *****************

    // Color picker initialization
    function initColorPickers() {
        $('.color-picker').wpColorPicker();
    }

    // Media upload handler
    function initMediaUpload() {
        $('.mgwpp-media-upload').click(function (e) {
            e.preventDefault();
            handleMediaSelection();
        });
    }

    function handleMediaSelection() {
        const galleryType = $('#mgwpp-create-gallery-type').val();
        const is3D = galleryType === '3d_model_carousel';

        // Always create a fresh frame for the specific type to ensure settings are applied
        mediaFrame = wp.media({
            title: is3D ? (mgwppMedia.text_select_model || 'Select 3D Models') : (mgwppMedia.text_title || 'Select Gallery Images'),
            button: { text: mgwppMedia.text_select || 'Use Selected' },
            multiple: true,
            library: is3D ? {} : { type: 'image' }
        });

        // Add 3D format filters if needed (filtering in library is tricky, so we rely on selection validation)
        mediaFrame.on('select', function () {
            processSelectedMedia(is3D);
        });

        // Show multi-select tooltip when media frame opens
        mediaFrame.on('open', function () {
            showMediaTooltip();
        });

        mediaFrame.open();
    }

    // Show a tooltip in the media modal explaining multi-select
    function showMediaTooltip() {
        // Wait for the modal to render
        setTimeout(function () {
            const $mediaModal = $('.media-modal');
            if ($mediaModal.length && !$mediaModal.find('.mgwpp-media-tooltip').length) {
                const tooltipHtml = $('<div class="mgwpp-media-tooltip">' +
                    '<span class="dashicons dashicons-info"></span>' +
                    '<span>Tip: Hold <strong>Shift</strong> or <strong>Ctrl</strong> and click to select multiple images, then click "Use Selected"</span>' +
                    '<button type="button" class="mgwpp-tooltip-close">&times;</button>' +
                    '</div>');

                // Style the tooltip
                tooltipHtml.css({
                    'position': 'absolute',
                    'top': '10px',
                    'left': '50%',
                    'transform': 'translateX(-50%)',
                    'background': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    'color': '#fff',
                    'padding': '10px 20px',
                    'border-radius': '8px',
                    'font-size': '13px',
                    'z-index': '999999',
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '8px',
                    'box-shadow': '0 4px 15px rgba(0,0,0,0.2)',
                    'animation': 'fadeIn 0.3s ease'
                });

                tooltipHtml.find('.dashicons').css({
                    'font-size': '18px',
                    'width': '18px',
                    'height': '18px'
                });

                tooltipHtml.find('.mgwpp-tooltip-close').css({
                    'background': 'transparent',
                    'border': 'none',
                    'color': '#fff',
                    'font-size': '18px',
                    'cursor': 'pointer',
                    'margin-left': '10px',
                    'opacity': '0.7'
                });

                $mediaModal.append(tooltipHtml);

                // Close button handler
                tooltipHtml.find('.mgwpp-tooltip-close').on('click', function () {
                    tooltipHtml.fadeOut(200, function () {
                        $(this).remove();
                    });
                });

                // Auto-hide after 5 seconds
                setTimeout(function () {
                    tooltipHtml.fadeOut(300, function () {
                        $(this).remove();
                    });
                }, 5000);
            }
        }, 300);
    }

    function processSelectedMedia(is3D) {
        let attachments = mediaFrame.state().get('selection').toJSON();

        // Basic sanitization for 3D formats if requested
        // Basic sanitization for 3D formats if requested
        if (is3D) {
            const allowedExts = ['glb', 'gltf', 'fbx', 'obj'];
            attachments = attachments.filter(att => {
                const name = att.filename || att.url || '';
                const ext = name.split('.').pop().toLowerCase();
                // Handle complex urls like .v1.glb
                if (!att.filename && name.includes('?')) {
                    // strip query params if checking url
                    return allowedExts.some(e => name.split('?')[0].toLowerCase().endsWith('.' + e));
                }
                return allowedExts.includes(ext);
            });

            if (attachments.length === 0) {
                alert('Please select valid 3D model files (.glb, .gltf, .fbx, .obj)');
                return;
            }
        }

        const mediaIds = attachments.map(attachment => attachment.id);
        const totalSize = attachments.reduce((sum, att) => sum + (att.filesizeInBytes || 0), 0);
        const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);

        $('#mgwpp-create-selected-media').val(mediaIds.join(','));

        // Show debug/feedback info if 3D
        if (is3D) {
            update3DFeedback(attachments.length, totalSizeMB);
        }

        updateMediaPreview(attachments, is3D);

        // Auto-submit logic
        if (mediaIds.length > 0) {
            if (is3D) {
                // Do not auto-submit for 3D models per user request
                // The feedback function will show the "Press Enter" hint
                // Ensure focus is on the title input so Enter works
                $('#mgwpp-create-gallery-title').focus();
            } else {
                autoSubmitGalleryForm();
            }
        }
    }

    function update3DFeedback(count, sizeMB) {
        let $feedback = $('#mgwpp-3d-feedback');
        if (!$feedback.length) {
            $feedback = $('<div id="mgwpp-3d-feedback" style="margin-top: 10px; padding: 10px; border-radius: 4px; font-size: 13px;"></div>');
            $('.mgwpp-media-upload').after($feedback);
        }

        let message = `Selected: ${count} models (${sizeMB} MB)`;
        let statusClass = 'info';
        let style = 'background: rgba(7, 186, 190, 0.1); color: #07babe; border: 1px solid rgba(7, 186, 190, 0.2);';

        if (count > 0) {
            message += ` <br><strong>Ready!</strong> Press <strong>Enter</strong> or click <strong>Create Gallery</strong>.`;
        }

        if (count > 12 || sizeMB > 100) {
            message += ` <br><strong>Error:</strong> Maximum limit exceeded (12 models / 100MB).`;
            style = 'background: rgba(255, 71, 87, 0.1); color: #ff4757; border: 1px solid rgba(255, 71, 87, 0.2);';
        } else if (count > 3 || sizeMB > 10) {
            message += ` <br><strong>Warning:</strong> Performance may be slow with many or large models.`;
            style = 'background: rgba(255, 159, 67, 0.1); color: #ff9f43; border: 1px solid rgba(255, 159, 67, 0.2);';
        }

        $feedback.html(message).attr('style', style);
    }

    function updateMediaPreview(attachments, is3D) {
        const preview = $('.mgwpp-media-preview').empty();
        const $uploadBtn = $('.mgwpp-media-upload');
        const $uploadBtnText = $('#mgwpp-upload-btn-text');
        const count = attachments.length;

        // Update button text and appearance to indicate selection
        if (count > 0) {
            const mediaType = is3D ? 'model' : 'image';
            const pluralType = count === 1 ? mediaType : (is3D ? 'models' : 'images');
            $uploadBtnText.text(`✓ ${count} ${pluralType} selected`);
            $uploadBtn.addClass('has-selection');
            $uploadBtn.find('.dashicons').removeClass('dashicons-cloud-upload').addClass('dashicons-yes-alt');
        } else {
            $uploadBtnText.text(mgwppMedia.text_title || 'Select Images');
            $uploadBtn.removeClass('has-selection');
            $uploadBtn.find('.dashicons').removeClass('dashicons-yes-alt').addClass('dashicons-cloud-upload');
        }

        // Show preview thumbnails
        attachments.forEach(attachment => {
            if (is3D || !attachment.sizes) {
                // Show 3D placeholder
                preview.append(`
                    <div class="media-thumbnail is-3d">
                        <div style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: #1a1a1a; color: #00f2ff;">
                            <span class="dashicons dashicons-format-video" style="font-size: 32px; width: 32px; height: 32px;"></span>
                        </div>
                        <span style="font-size: 10px; display: block; text-align: center; color: #fff; padding: 2px;">${attachment.filename}</span>
                    </div>
                `);
            } else {
                const thumbUrl = attachment.sizes && attachment.sizes.thumbnail ?
                    attachment.sizes.thumbnail.url :
                    attachment.url;

                preview.append(`
                    <div class="media-thumbnail">
                        <img src="${thumbUrl}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                    </div>
                `);
            }
        });

        // Make preview container visible if images selected
        if (count > 0) {
            $('.mgwpp-media-preview-container').addClass('has-images');
        } else {
            $('.mgwpp-media-preview-container').removeClass('has-images');
        }
    }

    // Auto-submit the gallery creation form after images are selected
    function autoSubmitGalleryForm() {
        const $form = $('#mgwpp-create-gallery-form');
        const $titleInput = $('#mgwpp-create-gallery-title');

        // If no title is entered, generate a default one with DD/MM/YYYY HH:MM format
        if (!$titleInput.val().trim()) {
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const defaultTitle = `Gallery ${day}/${month}/${year} ${hours}:${minutes}`;
            $titleInput.val(defaultTitle);
        }

        // Small delay to let the UI update, then submit
        setTimeout(function () {
            $form.trigger('submit');
        }, 300);
    }

    // **************
    // FORM MANAGEMENT
    // **************
    // **************
    // FORM MANAGEMENT
    // **************
    function initFormHandlers() {
        $('#mgwpp-create-gallery-form').on('submit', handleGalleryFormSubmission);
        $('.mgwpp-ajax-form').on('submit', handleFormSubmission);
    }

    function handleGalleryFormSubmission(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const form = $(this);
        const notice = $('#mgwpp-gallery-notice');

        // Check if already submitting
        if (form.data('submitting')) {
            console.log('Already submitting, skipping...');
            return false;
        }

        // Mark as submitting
        form.data('submitting', true);

        // Show loading
        $('#mgwpp-create-loading').show();

        // Get form data
        const formData = form.serialize();

        // Submit via AJAX
        $.ajax({
            url: mgwppMedia.ajax_url || ajaxurl,
            type: 'POST',
            data: formData,
            beforeSend: () => resetNotice(notice),
            success: (response) => {
                form.data('submitting', false);
                $('#mgwpp-create-loading').hide();

                if (response.success) {
                    // Close the modal
                    closeGalleryModal();

                    // Show success toast notification
                    showToastNotification('Gallery created successfully!', 'success');

                    // Reload the page to show the new gallery
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showError({ responseJSON: response }, notice);
                }
            },
            error: (xhr) => {
                form.data('submitting', false);
                $('#mgwpp-create-loading').hide();
                showError(xhr, notice);
            }
        });

        return false;
    }

    // Close the gallery creation modal
    function closeGalleryModal() {
        const $modal = $('#mgwpp-create-gallery-modal');
        const $overlay = $('.mgwpp-modal-overlay');

        // Remove active class to trigger close animation
        $modal.removeClass('active');
        $overlay.removeClass('active');

        // Reset the form for next use
        setTimeout(() => {
            $('#mgwpp-create-gallery-form')[0]?.reset();
            $('#mgwpp-create-selected-media').val('');
            $('.mgwpp-media-preview').empty();
            $('.mgwpp-media-preview-container').removeClass('has-images');

            // Reset the upload button
            const $uploadBtn = $('.mgwpp-media-upload');
            const $uploadBtnText = $('#mgwpp-upload-btn-text');
            $uploadBtnText.text(mgwppMedia.text_title || 'Select Images');
            $uploadBtn.removeClass('has-selection');
            $uploadBtn.find('.dashicons').removeClass('dashicons-yes-alt').addClass('dashicons-cloud-upload');
        }, 300);
    }

    // Show toast notification
    function showToastNotification(message, type) {
        // Remove any existing toasts
        $('.mgwpp-toast-notification').remove();

        // Create toast element
        const iconClass = type === 'success' ? 'dashicons-yes-alt' : 'dashicons-warning';
        const $toast = $(`
            <div class="mgwpp-toast-notification ${type}">
                <span class="dashicons ${iconClass}"></span>
                <span class="mgwpp-toast-message">${message}</span>
            </div>
        `);

        // Append to body
        $('body').append($toast);

        // Trigger animation
        setTimeout(() => {
            $toast.addClass('show');
        }, 10);

        // Auto-remove after delay
        setTimeout(() => {
            $toast.removeClass('show');
            setTimeout(() => {
                $toast.remove();
            }, 300);
        }, 3000);
    }

    function handleFormSubmission(e) {
        e.preventDefault();
        const form = $(this);
        const notice = $('#mgwpp-gallery-notice');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            beforeSend: () => resetNotice(notice),
            success: (response) => showSuccess(notice),
            error: (xhr) => showError(xhr, notice)
        });
    }

    function resetNotice(notice) {
        notice.hide().removeClass('success error');
    }

    function showSuccess(notice) {
        notice.addClass('success')
            .html(mgwppMedia.gallery_success)
            .show();
        setTimeout(() => {
            window.location.href = 'admin.php?page=mgwpp_galleries';
        }, 1500);
    }

    function showError(xhr, notice) {
        const responseCallback = xhr.responseJSON;
        // Handle different error structures (top level message, or inside data.message for WP default)
        const errorMsg = responseCallback?.message ||
            responseCallback?.data?.message ||
            responseCallback?.data ||
            xhr.responseText ||
            mgwppMedia.generic_error ||
            'An error occurred';

        notice.addClass('error').html(errorMsg).show();
    }


    // ********************
    // GALLERY PREVIEW SYSTEM
    // ********************
    function initGalleryPreviews() {
        $('#mgwpp-create-gallery-type').change(updateGalleryPreview);
    }

    function updateGalleryPreview() {
        const option = $(this).find('option:selected');
        const is3D = $(this).val() === '3d_model_carousel';

        // 1. Update Preview Image
        $('#preview_img').attr('src', option.data('image') || '');
        $('#preview_demo').attr('href', option.data('demo') || '#');
        $('#gallery_preview').toggle(!!option.data('image'));

        // 2. Toggle Hints & Labels
        const $uploadBtnText = $('#mgwpp-upload-btn-text');
        const $hint = $('#mgwpp-3d-hint');

        if (is3D) {
            $hint.slideDown();
            $uploadBtnText.text(mgwppMedia.text_select_model || 'Select 3D Models');
        } else {
            $hint.slideUp();
            $uploadBtnText.text(mgwppMedia.text_title || 'Select Images');
        }

        // 3. Reset Selection (safety to avoid mixed types)
        // If we switch types, previous selection might be invalid (images vs models)
        $('#mgwpp-create-selected-media').val('');
        $('.mgwpp-media-preview').empty();
        $('.mgwpp-media-preview-container').removeClass('has-images');
        $('.mgwpp-media-upload').removeClass('has-selection');
        $('#mgwpp-3d-feedback').remove(); // Remove old 3D feedback
    }

    // **********************
    // PRO NOTICE DISMISSAL
    // **********************
    function initProNoticeDismissal() {
        $(document).on('click', '.mg-pro-elements-notice .notice-dismiss', dismissProNotice);
    }

    function dismissProNotice() {
        $.post(ajaxurl, {
            action: 'mg_dismiss_pro_elements_notice'
        });
    }

    // ********************
    // BOOTSTRAP THE SYSTEM
    // ********************
    if (typeof wp !== 'undefined' && wp.media && $.fn.wpColorPicker) {
        initDashboard();
    } else {
        setTimeout(initDashboard, 500);
    }
});