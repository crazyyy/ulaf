jQuery(document).ready( $ => {
    /**
     * Class for filtering and managing plugins
     * Handles toggling descriptions, filtering by status (active/inactive/mine), 
     * and searching plugins by keyword.
     */
    class PluginsFilter {
        /**
         * Initialize the PluginsFilter instance.
         * Sets up initial state, DOM references, and event listeners.
         */
        constructor() {
            // Retrieve stored preference for hiding descriptions
            this.descCookie = localStorage.getItem('showHideDescPlugins');
            // Store the original total count of plugins
            this.oldTotal = $('.displaying-num').eq(0).text();
            // Calculate the total plugins (excluding update rows)
            this.calcTotal = $('#the-list tr').length - $('#the-list tr.plugin-update-tr').length;

            // Selectors for elements to filter during keyword search
            this.filterSelectors = [
                'td.plugin-title > strong',
                'td.column-description .plugin-description',
                'td.column-description div.plugin-version-author-uri a:first'
            ];

            // Initialize the plugin
            this.init();
        }

        /**
         * Initialize event listeners and UI components.
         */
        init() {
            // Create custom selectors
            B5F_Common.createCustomSelectors();
            // Inject additional HTML into the DOM
            $('div.tablenav.top div.alignleft.actions.bulkactions').after(ADTW.html);

            // Prevent default click behavior for filter buttons
            $('#hide-desc,#hide-active,#hide-inactive, #hide-mine').click(e => e.preventDefault());
            // Bind click handlers
            $('#hide-desc').click(this.descAction.bind(this));
            $('#hide-active').click(this.swapClasses.bind(this));
            $('#hide-inactive').click(this.swapClasses.bind(this));
            $('#hide-mine').click(this.swapMines.bind(this));

            // Reset the list when the search input is focused (if empty)
            $("#b5f-plugins-filter").on('focus', e => {
                if ($(e.target).val().length === 0) this.resetList();
            });
            // Filter plugins on input (debounced for performance)
            $("#b5f-plugins-filter").on('input', (e) => {
                B5F_Common.daDelay( 
                    (param) => {
                        B5F_Common.daFilterByKeyword(param, document, this.filterSelectors);
                        this.updtTotals();
                    }, 
                    B5F_Common.daNormalizeStr($(e.target).val()) 
                );
            });

            // Reset the list when the close icon is clicked
            $('.close-icon').click(() => {
                this.resetList();
                this.updtTotals(); 
            });

            // Apply description hiding if previously enabled
            if (this.descCookie) $('#hide-desc').click();
        }

        /**
         * Update the displayed total count of plugins.
         * Shows either the original count or the filtered count.
         */
        updtTotals() {
            let nowTotal = $('#the-list tr:visible').length - $('#the-list tr.plugin-update-tr:visible').length;
            let theNum = nowTotal == this.calcTotal ? this.oldTotal : nowTotal;
            $('.displaying-num').text(theNum + " items");
        }

        /**
         * Toggle plugin descriptions and row actions visibility.
         * @param {Event} e - The click event.
         */
        descAction(e) {
            let onlyDesc = 'div.plugin-description';
            if (ADTW.hide_row_actions) {
                onlyDesc = onlyDesc + ',div.row-actions'; 
            }
            if (!$(e.target).hasClass('active')) {
                $(onlyDesc).hide();
                localStorage.setItem('showHideDescPlugins', true);
                $(e.target).addClass('active b5f-active');
            } else {
                $(onlyDesc).show();
                localStorage.removeItem('showHideDescPlugins');
                $(e.target).removeClass('active b5f-active');
            }
        }

        /**
         * Reset all button titles to their "show" state.
         */
        allEData() {
            $('button.b5f-btn-status').each((i,v) => {
                $(v).attr('title', $(v).data('titleShow'));
            });
        }

        /**
         * Toggle visibility of plugins by status (active/inactive).
         * @param {Event} e - The click event.
         */
        swapClasses(e) {
            let status = $(e.target).attr('id').includes('inactive') 
                ? 'active' : 'inactive';
            if (!$(e.target).hasClass('active')) {
                this.resetList();
                $('tr.'+status+', tr.plugin-update-tr').hide();
                $(e.target).addClass('active b5f-active');
                this.allEData();
                $(e.target).attr('title', $(e.target).data('titleHide'));
            } else {
                $('#the-list tr').show();
                $(e.target).removeClass('active b5f-active');
                $(e.target).attr('title', $(e.target).data('titleShow'));
            }
            this.updtTotals();
        }

        /**
         * Toggle visibility of plugins owned by the current user.
         * @param {Event} e - The click event.
         */
        swapMines(e) {
            if (!$(e.target).hasClass('active')) {
                this.resetList();
                $("#the-list tr:notContains('"+ADTW.plugin_users+"')").hide();
                $(e.target).addClass('active b5f-active');
                this.allEData();
                $(e.target).attr('title', $(e.target).data('titleHide'));
            } else {
                $('#the-list tr').show();
                $(e.target).removeClass('active b5f-active');
                $(e.target).attr('title', $(e.target).data('titleShow'));
            }
            this.updtTotals();
        }

        /**
         * Reset all filters and show the full plugin list.
         */
        resetList() {
            $('tbody#the-list tr').show();
            $("#b5f-plugins-filter").val('');
            $('button.b5f-btn-status').removeClass('active b5f-active');
        }
    }

    // Instantiate the PluginsFilter class
    new PluginsFilter();
});