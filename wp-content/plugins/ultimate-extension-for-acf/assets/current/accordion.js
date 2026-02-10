/**
 * ACF Flexible Content Accordion Script
 * 
 * This script provides accordion functionality for ACF Flexible Content fields,
 * ensuring that only one layout can be open at a time. It integrates with ACF's
 * event system and provides fallback jQuery event handling for compatibility.
 * 
 * Key Features:
 * - Automatic layout collapsing (accordion behavior)
 * - Integration with ACF's field type system
 * - Fallback event handling for edge cases
 * - Dynamic initialisation for new fields
 * 
 * @package Ultimate_Extension_for_ACF
 * @since 1.0.0
 * @author Ultimate Agency
 */

(function($) {
    'use strict';

    /**
     * Collapse All Layouts
     * 
     * Collapses all open flexible content layouts by adding the '-collapsed' class.
     * Excludes clone layouts (templates) from the operation.
     * 
     * @since 1.0.0
     */
    function collapseAllLayouts() {
        $('.acf-flexible-content .layout:not(.acf-clone):not(.-collapsed)').each(function() {
            $(this).addClass('-collapsed');
        });
    }

    /**
     * Close Other Layouts
     * 
     * Closes all layouts except the currently selected one within the same
     * flexible content field. This maintains the accordion behaviour.
     * 
     * @param {jQuery} $currentLayout The layout that should remain open
     * @since 1.0.0
     */
    function closeOtherLayouts($currentLayout) {
        const $flexibleContent = $currentLayout.closest('.acf-flexible-content');
        $flexibleContent.find('.layout:not(.acf-clone):not(.-collapsed)').not($currentLayout).each(function() {
            $(this).addClass('-collapsed');
        });
    }

    /**
     * ACF Field Type Integration
     * 
     * Hooks directly into ACF's flexible_content field type to override the
     * openLayout method. This ensures accordion behaviour at the field level.
     * 
     * @since 1.0.0
     */
    if (typeof acf !== 'undefined' && acf.fields && acf.fields.flexible_content) {
        // Store reference to the original openLayout method
        const originalOpenLayout = acf.fields.flexible_content.prototype.openLayout;

        /**
         * Override ACF's openLayout method to implement accordion behavior
         * 
         * @param {jQuery} layout The layout element to open
         * @return {*} Result from original openLayout method
         */
        acf.fields.flexible_content.prototype.openLayout = function(layout) {
            // Close all other layouts in this field first
            this.$layouts().not(layout).each(function() {
                const $layout = $(this);
                if (!$layout.hasClass('-collapsed')) {
                    $layout.addClass('-collapsed');
                }
            });
            
            // Call the original openLayout method
            return originalOpenLayout.call(this, layout);
        };
    }

    /**
     * ACF Action Hooks Integration
     * 
     * Integrates with ACF's action system to handle various field states
     * and ensure accordion behaviour is maintained throughout the admin interface.
     * 
     * @since 1.0.0
     */
    if (typeof acf !== 'undefined') {
        /**
         * Handle ACF Ready Event
         * 
         * Collapse all layouts when ACF initializes to establish
         * the default accordion state.
         */
        acf.add_action('ready', function() {
            collapseAllLayouts();
        });

        /**
         * Handle Field Append Events
         * 
         * When new flexible content fields are dynamically added,
         * ensure they follow the accordion behaviour.
         * 
         * @param {jQuery} $el The appended element
         */
        acf.add_action('append', function($el) {
            if ($el.find('.acf-flexible-content').length || $el.hasClass('acf-flexible-content')) {
                collapseAllLayouts();
            }
        });

        /**
         * Handle Layout Show Events
         * 
         * When a layout is shown (expanded), close other layouts
         * to maintain accordion behaviour.
         * 
         * @param {jQuery} $el The element being shown
         * @param {string} context The context of the show action
         */
        acf.add_action('show', function($el, context) {
            if (context === 'collapse' && $el.hasClass('layout')) {
                closeOtherLayouts($el);
            }
        });
    }

    /**
     * Fallback Event Listeners
     * 
     * Provides jQuery-based event handling as a fallback for cases where
     * ACF's action system might not be available or sufficient.
     * 
     * @since 1.0.0
     */
    
    /**
     * Handle Layout Handle Clicks
     * 
     * Fallback click handler for layout handles to ensure accordion
     * behavior even if ACF's event system fails.
     */
    $(document).on('click', '.acf-fc-layout-handle', function() {
        const $layout = $(this).closest('.layout');
        
        // Only process real layouts (not clones) that are currently collapsed
        if (!$layout.hasClass('acf-clone') && $layout.hasClass('-collapsed')) {
            closeOtherLayouts($layout);
        }
    });

    /**
     * Document Ready Initialisation
     * 
     * Initialise accordion behaviour when the DOM is ready.
     * This serves as the primary initialisation point.
     */
    $(document).ready(function() {
        collapseAllLayouts();
    });

})(jQuery);