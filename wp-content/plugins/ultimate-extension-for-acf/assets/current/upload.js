/**
 * Ultimate Extension for ACF - Image Upload Handler
 * 
 * This script handles the image upload functionality for managing preview images
 * in the Ultimate Extension for ACF admin interface. It integrates with the WordPress
 * media library and provides AJAX communication for storing image selections.
 * 
 * Key Features:
 * - WordPress media library integration
 * - AJAX-based image upload and storage
 * - Real-time preview updates
 * - Error handling and user feedback
 * - Optimized DOM manipulation with caching
 * 
 * @package Ultimate_Extension_for_ACF
 * @since 1.0.0
 * @author Ultimate Agency
 */

(function($) {
    'use strict';

    /**
     * ACF Modal Uploader Object
     * 
     * Main object containing all upload functionality and DOM manipulation methods.
     * Uses an object literal pattern for better organisation and performance.
     */
    let ACFModalUploader = {
        /**
         * Cached DOM elements for performance optimisation
         * 
         * @type {jQuery|null}
         */
        $title: null,
        $notices: null,
        
        /**
         * Constructor/Initialisation Method
         * 
         * Sets up the uploader by caching DOM elements, initialising upload buttons,
         * and setting up event listeners to notice dismissals.
         * 
         * @since 1.0.0
         */
        construct: function () {
            // Cache frequently used DOM elements for performance
            this.$title = $('.image-preview-settings-title');
            this.$notices = $('<div class="acf-notices-container"></div>');
            this.$title.after(this.$notices);
            
            // Initialize upload functionality for each upload button
            $('.acf-modal-upload').each(function (index) {
                ACFModalUploader.initButton($(this));
            });
            
            // Single event delegation for all notice dismissals
            // More efficient than individual event handlers
            this.$notices.on('click', '.notice-dismiss', function() {
                $(this).closest('.notice').fadeOut(200, function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Initialize Upload Button
         * 
         * Sets up click handler for individual upload buttons and configures
         * WordPress media library modal for image selection.
         * 
         * @param {jQuery} _that The upload button element
         * @since 1.0.0
         */
        initButton: function (_that) {
            _that.click(function (e) {
                // Prevent default link behavior
                e.preventDefault();

                // Get context elements and data
                let imageHolder = $(this).closest('.modal-preview-row');
                let layout = $(this).attr('data-layout');
                
                // Configure WordPress media uploader
                let custom_uploader = wp.media({
                    title: 'Insert preview image',    // Modal title
                    library: {
                        type: 'image'                   // Only show images
                    },
                    button: {
                        text: 'Use this image'          // Selection button text
                    },
                    multiple: false                     // Single image selection
                });

                /**
                 * Handle Media Selection
                 * 
                 * Processes the selected image and sends AJAX request to save the selection.
                 */
                custom_uploader.on('select', function() {
                    // Get selected media attachment data
                    let media_attachment = custom_uploader.state().get('selection').first().toJSON();
                    
                    // Immediately update preview image for better UX
                    let $existingImage = imageHolder.find('img.image-preview-image');
                    if ($existingImage.length) {
                        $existingImage.attr('src', media_attachment.url);
                    } else {
                        // Create image element if it doesn't exist
                        let $noImageSpan = imageHolder.find('.no-image');
                        if ($noImageSpan.length) {
                            $noImageSpan.replaceWith('<img src="' + media_attachment.url + '" class="image-preview-image modal-preview-image" alt="Preview image">');
                        }
                    }

                    // Prepare AJAX data
                    let data = {
                        action: 'uefax_setModalImage',     // WordPress AJAX action
                        layout: layout,                 // ACF layout name
                        image: media_attachment.id,     // WordPress attachment ID
                    };

                    /**
                     * AJAX Request to Save Image Selection
                     * 
                     * Sends the selected image data to the server for storage
                     * and provides user feedback based on the response.
                     */
                    $.ajax({
                        url: uefax_ajax.ajax_url,           // Localized AJAX URL
                        type: 'POST',
                        data: data,
                        
                        /**
                         * Set security nonce header
                         * 
                         * @param {XMLHttpRequest} xhr The XMLHttpRequest object
                         */
                        beforeSend: function ( xhr ) {
                            xhr.setRequestHeader( 'X-WP-Nonce', uefax_ajax.nonce );
                        },
                        
                        /**
                         * Handle successful AJAX response
                         * 
                         * @param {Object} response Server response data
                         */
                        success: function( response ) {
                            if ( response.success ) {
                                // Show success notification
                                ACFModalUploader.showNotice(response.data.message, 'success');
                                
                                // Update the image preview with optimized URL
                                if (response.data && response.data.image_url) {
                                    let $existingImage = imageHolder.find('img.image-preview-image');
                                    if ($existingImage.length) {
                                        $existingImage.attr('src', response.data.image_url);
                                    } else {
                                        // Create image element if it doesn't exist
                                        let $noImageSpan = imageHolder.find('.no-image');
                                        if ($noImageSpan.length) {
                                            $noImageSpan.replaceWith('<img src="' + response.data.image_url + '" class="image-preview-image modal-preview-image" alt="Preview image">');
                                        }
                                    }
                                }
                                
                                // Remove "no image" placeholder text (fallback)
                                imageHolder.find('.no-image').remove();
                                
                                // Visual feedback for successful upload
                                imageHolder.addClass('upload-success');
                                ACFModalUploader.removeClassAfterDelay(imageHolder, 'upload-success', 2000);
                            } else {
                                // Handle server-side errors
                                ACFModalUploader.showNotice(response.data.message || 'Unknown error occurred', 'error');
                                
                                // Visual feedback for error state
                                imageHolder.addClass('upload-error');
                                ACFModalUploader.removeClassAfterDelay(imageHolder, 'upload-error', 2000);
                            }
                        },
                        
                        /**
                         * Handle AJAX errors
                         * 
                         * @param {XMLHttpRequest} xhr The XMLHttpRequest object
                         * @param {string} status Error status
                         * @param {string} error Error message
                         */
                        error: function(xhr, status, error) {
                            // Show user-friendly error message
                            ACFModalUploader.showNotice('Upload failed: ' + error, 'error');
                            
                            // Visual feedback for error state
                            imageHolder.addClass('upload-error');
                            ACFModalUploader.removeClassAfterDelay(imageHolder, 'upload-error', 2000);
                        }
                    });
                });

                // Open the WordPress media modal
                custom_uploader.open();
            });
        },

        /**
         * Show Admin Notice
         * 
         * Displays feedback messages to the user using WordPress admin notice styling.
         * Optimized for performance with DOM manipulation and automatic dismissal.
         * 
         * @param {string} message The message to display
         * @param {string} type The notice type ('success', 'error', 'warning', 'info')
         * @since 1.0.0
         */
        showNotice: function(message, type) {
            // Clear any existing notices to avoid clutter
            this.$notices.empty();
            
            // Create WordPress-styled admin notice
            let notice = $('<div class="notice notice-' + type + ' is-dismissible">' +
                          '<p>' + message + '</p>' +
                          '<button class="notice-dismiss" type="button">' +
                          '<span class="screen-reader-text">Dismiss this notice.</span>' +
                          '</button></div>');
            
            // Add notice to the container
            this.$notices.append(notice);
            
            // Auto-dismiss success notices after 5 seconds for better UX
            if (type === 'success') {
                setTimeout(() => {
                    notice.fadeOut(200, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        },

        /**
         * Remove CSS Class After Delay
         * 
         * Utility method to remove CSS classes after a specified delay.
         * Used for temporary visual feedback states.
         * 
         * @param {jQuery} $element The element to modify
         * @param {string} className The CSS class to remove
         * @param {number} delay Delay in milliseconds
         * @since 1.0.0
         */
        removeClassAfterDelay: function($element, className, delay) {
            setTimeout(() => {
                $element.removeClass(className);
            }, delay);
        }
    };

    /**
     * Initialize the Uploader
     * 
     * Start the upload functionality when the script loads.
     */
    ACFModalUploader.construct();

})(jQuery);
