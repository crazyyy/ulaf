jQuery(function ($) {
    // Initialize variables
    var mediaUploader;
    var $imageContainer = $('.mgwpp-image-container');

    // SINGLE sortable initialization - remove duplicates
    $imageContainer.sortable({
        placeholder: 'mgwpp-image-item ui-sortable-placeholder',
        opacity: 0.6,
        revert: 200,
        cursor: 'move',
        start: function (event, ui) {
            // Hide remove buttons during drag
            $('.mgwpp-remove-image').hide();
            ui.placeholder.height(ui.item.height());
        },
        stop: function (event, ui) {
            // Show remove buttons after drag
            $('.mgwpp-remove-image').show();

            // Update hidden input order to match visual order
            updateImageOrder();

            // Visual feedback that order changed
            $('#mgwpp-save-order-btn').css('background-color', '#d54e21');
        }
    });

    // Function to update hidden input order and visual indices
    function updateImageOrder() {
        $imageContainer.find('.mgwpp-image-item').each(function (index) {
            var $item = $(this);
            var imageId = $item.data('id');

            // Update/Create Visual Index
            var $indexSpan = $item.find('.mgwpp-image-index');
            if ($indexSpan.length === 0) {
                $indexSpan = $('<span class="mgwpp-image-index"></span>');
                $item.append($indexSpan);
            }
            $indexSpan.text(index + 1);

            // Update or create hidden input with correct order
            var $hiddenInput = $item.find('input[name="gallery_images[]"]');
            if ($hiddenInput.length === 0) {
                $hiddenInput = $('<input type="hidden" name="gallery_images[]">');
                $item.append($hiddenInput);
            }
            $hiddenInput.val(imageId);
        });
    }

    // Initialize indices on load
    updateImageOrder();

    // Images button
    $('.mgwpp-add-images').on('click', function (e) {
        e.preventDefault();

        // Check if current gallery type supports 3D models
        var galleryType = $('input[name="gallery_type"]:checked').val() || '';
        var is3D = galleryType.indexOf('3d') !== -1 || galleryType === '3d_model_carousel';
        
        // If uploader instance already exists, we might need to recreate it if the type requirement changed
        // We attach 'is3D' property to the uploader to check later
        if (mediaUploader) {
            if (mediaUploader.is3D !== is3D) {
                mediaUploader = null; // Force recreation
            } else {
                mediaUploader.open();
                return;
            }
        }

        var mediaOptions = {
            title: is3D ? 'Select Images or 3D Models' : 'Select Images for Gallery',
            button: {
                text: 'Add to Gallery'
            },
            multiple: true
        };

        // Only restrict to images if NOT a 3D gallery
        if (!is3D) {
            mediaOptions.library = {
                type: 'image'
            };
        }

        mediaUploader = wp.media(mediaOptions);
        mediaUploader.is3D = is3D;

        mediaUploader.on('select', function () {
            var attachments = mediaUploader.state().get('selection').toJSON();
            var $noImages = $('.mgwpp-no-images');

            // Remove "no images" message if present
            if ($noImages.length) {
                $noImages.remove();
            }

            attachments.forEach(function (attachment) {
                // Check if already exists
                if ($imageContainer.find('[data-id="' + attachment.id + '"]').length === 0) {
                    var thumbUrl;
                    
                    if (attachment.sizes && attachment.sizes.thumbnail) {
                        thumbUrl = attachment.sizes.thumbnail.url;
                    } else if (attachment.type === 'image') {
                        thumbUrl = attachment.url;
                    } else {
                        // Fallback for non-images (3D models, etc)
                        thumbUrl = attachment.icon || attachment.url; // Use WP icon if available
                    }

                    var imageItem = $(
                        '<div class="mgwpp-image-item" data-id="' + attachment.id + '">' +
                        '<img src="' + thumbUrl + '" alt="">' +
                        '<input type="hidden" name="gallery_images[]" value="' + attachment.id + '">' +
                        '<div class="mgwpp-item-actions">' +
                        '<button type="button" class="mgwpp-remove-image" title="Remove from gallery"><span class="dashicons dashicons-no"></span></button>' +
                        '<button type="button" class="mgwpp-delete-image" title="Permanently delete"><span class="dashicons dashicons-trash"></span></button>' +
                        '</div>' +
                        '</div>'
                    );

                    $imageContainer.append(imageItem);
                }
            });

            // Update indices after adding
            updateImageOrder();
        });

        mediaUploader.open();
    });

    // Remove Image button
    $imageContainer.on('click', '.mgwpp-remove-image', function (e) {
        e.preventDefault();
        $(this).closest('.mgwpp-image-item').fadeOut(300, function () {
            $(this).remove();

            // Check if container is empty
            if ($imageContainer.find('.mgwpp-image-item').length === 0) {
                $imageContainer.append('<p class="mgwpp-no-images">No images added to this gallery yet.</p>');
            }
            // Update indices after removal
            updateImageOrder();
        });
    });

    // Show/hide remove buttons on hover
    $imageContainer.on('mouseenter', '.mgwpp-image-item', function () {
        $(this).find('.mgwpp-remove-image').show();
    }).on('mouseleave', '.mgwpp-image-item', function () {
        if (!$(this).is('.ui-sortable-helper')) {
            $(this).find('.mgwpp-remove-image').hide();
        }
    });

    // Save Gallery Order - AJAX handler
    $('#mgwpp-save-order-btn').on('click', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const originalText = $btn.text();
        $btn.text(mgwppEdit.i18n.saving).prop('disabled', true);

        // Get ordered image IDs from data attributes (more reliable)
        const imageIds = [];
        $imageContainer.find('.mgwpp-image-item').each(function () {
            const imageId = $(this).data('id');
            if (imageId) {
                imageIds.push(parseInt(imageId));
            }
        });

        // Debug log
        console.log('Saving order:', imageIds);

        // AJAX request
        $.ajax({
            url: mgwppEdit.ajaxUrl,
            type: 'POST',
            data: {
                action: 'mgwpp_save_gallery_order',
                gallery_id: $('input[name="gallery_id"]').val(),
                image_ids: imageIds,
                nonce: mgwppEdit.nonce
            },
            success: function (response) {
                console.log('Save response:', response);

                if (response.success) {
                    $btn.text(mgwppEdit.i18n.saved).css('background-color', '#46b450');

                    // Update hidden inputs to match saved order
                    updateImageOrder();
                    // REFRESH PREVIEW IFRAME
                    refreshPreviewIframe();
                    setTimeout(() => {
                        $btn.text(originalText).prop('disabled', false).css('background-color', '');
                    }, 2000);
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error'));
                    $btn.text(originalText).prop('disabled', false).css('background-color', '');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', xhr.responseText);
                alert(mgwppEdit.i18n.saveFailed + ': ' + error);
                $btn.text(originalText).prop('disabled', false).css('background-color', '');
            }

        });
    });
    function refreshPreviewIframe() {
        const $previewFrame = $('#mgwpp-preview-frame');
        if ($previewFrame.length) {
            // Get current src
            let currentSrc = $previewFrame.attr('src');

            // Remove existing timestamp if present
            currentSrc = currentSrc.replace(/[?&]t=\d+/, '');

            //  new timestamp to bypass cache
            const separator = currentSrc.includes('?') ? '&' : '?';
            const newSrc = currentSrc + separator + 't=' + Date.now();

            // Refresh iframe
            $previewFrame.attr('src', newSrc);
        }
    }
    $imageContainer.on('click', '.mgwpp-delete-image', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $item = $(this).closest('.mgwpp-image-item');
        const imageId = $item.data('id');

        if (!confirm(mgwppEdit.i18n.confirmDeleteImage)) {
            return;
        }

        // AJAX request to delete image
        $.post(mgwppEdit.ajaxUrl, {
            action: 'mgwpp_delete_image',
            image_id: imageId,
            nonce: mgwppEdit.nonce
        }).then(response => {
            if (response.success) {
                $item.fadeOut(300, function () {
                    $(this).remove();
                    checkEmptyContainer();
                    refreshPreviewIframe();
                });
            } else {
                alert(mgwppEdit.i18n.deleteError);
            }
        }).fail(() => {
            alert(mgwppEdit.i18n.deleteError);
        });
    });
    // Initialize color pickers if they exist
    if ($.fn.wpColorPicker) {
        $('.mgwpp-color-field').wpColorPicker();
    }

    // Device toggle buttons for responsive preview
    $('.mgwpp-device-btn').on('click', function () {
        var $btn = $(this);
        var device = $btn.data('device');

        // Update active button state
        $('.mgwpp-device-btn').removeClass('active');
        $btn.addClass('active');

        // Update preview wrapper data attribute
        $('.mgwpp-preview-wrapper').attr('data-device', device);

        // Add animation class
        $('.mgwpp-preview-frame-container').addClass('mgwpp-device-transition');
        setTimeout(function () {
            $('.mgwpp-preview-frame-container').removeClass('mgwpp-device-transition');
        }, 400);
    });
});