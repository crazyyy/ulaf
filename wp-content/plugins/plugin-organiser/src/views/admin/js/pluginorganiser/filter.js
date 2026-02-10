var Innocow = Innocow || {};
Innocow.WP = Innocow.WP || {};
Innocow.WP.PluginOrganiser = Innocow.WP.PluginOrganiser || {};

Innocow.WP.PluginOrganiser.Filter = class Filter {

    constructor( container ) {

        this.container = container || document;
        this.numVisiblePerFilter = 0;

        this.filtersCustom = [];
        this.filtersDefault = {
            all: "All",
            active: "Active",
            inactive: "Inactive",
            autoUpdateEnabled: "Auto-Update Enabled",
            autoUpdateDisabled: "Auto-Update Disabled"
        };

        this.options = {
            textSelectPrefix: "Plugin Group",
            textFiltered: "filtered",
        }

        this.ids = {
            select: "plugin-organiser-filter",
        };

        this.classNames = {
            defaultContainer: "wrap",
            defaultFilters: "subsubsub",
            defaultScreenReaderText: "screen-reader-text",
            defaultDisplayingNum: "displaying-num",
            defaultPaginationLinks: "pagination-links",
            defaultTableNavPages: "tablenav-pages",
            filteredNum: "filtered-num",
        };

        this.queries = {
            pluginEntries: "table.plugins > #the-list > tr",
            pluginEntryCheckbox: "th > input[type='checkbox']",
            pluginEntryToggleAU: "td.column-auto-updates > a.toggle-auto-update",
            pagesDisplay: ".tablenav-pages.one-page",
        }

    }

    loadForm() {
        
        this.hideFormDefaultFilters();
        this.addFormPluginFilters();
        this.addFormFilteredNumber();

    }

    hideFormDefaultFilters() {

        let defaultFilters = this.container.getElementsByClassName( this.classNames.defaultFilters );

        Array.from( defaultFilters ).map( filter => {
            filter.style.display = "none";
        } );

    }

    addFormPluginFilters() {

        let containers = this.container.getElementsByClassName( this.classNames.defaultContainer );

        Array.from( containers ).map( container => {

            let select = this.buildFormSelect();
            container.insertBefore( 
                select, 
                this.container.querySelector( `.${this.classNames.defaultFilters}` )
            );

        } );

    }

    addFormFilteredNumber() {

        Array.from( this.container.getElementsByClassName( this.classNames.defaultTableNavPages ) )
        .map( display => {
            display.insertBefore( 
                this.buildFormFilteredNumber(),
                display.querySelector( `.${this.classNames.defaultPaginationLinks}`)
            );
        } );

    }    

    buildFormSelect() {

        let select = document.createElement( "select" );
        let optionSpace = document.createElement( "option" );

        select.id = this.ids.select;

        for ( let filter in this.filtersDefault ) {

            let option = document.createElement( "option" );
            option.innerHTML = this.filtersDefault[filter];
            option.value = `${filter}`;
            select.append( option );

        }

        optionSpace.innerHTML = '------';
        select.append( optionSpace );

        for ( let filter of this.filtersCustom ) {
            
            let option = document.createElement( "option" );
            option.innerHTML = `${this.options.textSelectPrefix}: ${filter.name}`;
            option.value = filter.id;
            select.append( option );

        }        

        return select;

    }

    buildFormFilteredNumber() {

        let filteredNum = document.createElement( "span" );

        filteredNum.classList.add( this.classNames.filteredNum );

        return filteredNum;

    }

    updateFormFilteredNumber( number ) {

        Array.from( this.container.getElementsByClassName( this.classNames.filteredNum ) )
        .map( container => {

            if ( number !== undefined ) {
                container.innerHTML = ` (${number} ${this.options.textFiltered})`;
            } else {
                container.innerHTML = "";
            }

        } );

    }

    filter( type ) {

        this.updateFormFilteredNumber();

        switch( type ) {

            case "all":
                this.filterNone();
                break;

            case "active":
            case "inactive":
                this.filterNone();
                this.filterActiveOrInactive( type );
                break;

            case "autoUpdateEnabled":
                this.filterNone();
                this.filterAUStatus( "enabled" );
                break;

            case "autoUpdateDisabled":
                this.filterNone();
                this.filterAUStatus( "disabled" );
                break;

            default:
                this.filterNone();            
                this.filterCustom( type );
                break;

        }

        let visibleEntries = this.countVisibleEntries();
        if ( visibleEntries < this.countAllEntries() ) {
            this.updateFormFilteredNumber( visibleEntries );
        }

    }

    filterNone() {
        
        Array.from( this.findPluginEntries() ).map( entry => {
            this.enableEntry( entry );
        } )

    }

    filterActiveOrInactive( type ) {

        Array.from( this.findPluginEntries() ).map( entry => {

            if ( entry.classList.contains( type ) ) {
                this.enableEntry( entry );
            } else {
                this.disableEntry( entry );
            }

        } );

    }

    filterAUStatus( type ) {

        let entriesAUEnabled = [];
        let entriesAUDisabled = [];

        Array.from( this.findPluginEntries() ).map( entry => {

            let toggleAU = entry.querySelector( this.queries.pluginEntryToggleAU );

            if ( ! toggleAU ) { 

                entriesAUDisabled.push( entry );

            } else {

                let actionAU = toggleAU.dataset["wpAction"];

                if ( actionAU === "enable" ) {
                    entriesAUDisabled.push( entry );
                } else {
                    entriesAUEnabled.push( entry );
                }

            }

        } ); 

        if ( type === "enabled" ) {
            Array.from( entriesAUDisabled ).map( e => e.style.display = "none" );
        } else if ( type === "disabled" ) {
            Array.from( entriesAUEnabled ).map( e => e.style.display = "none" );
        }

    }

    filterCustom( type ) {

        Array.from( this.filtersCustom ).map( filter => {

            if ( filter.id == type ) {

                Array.from( this.findPluginEntries() ).map( entry => {
                    if ( filter.plugins.includes( entry.dataset["plugin"] ) ) {
                        this.enableEntry( entry );
                    } else {
                        this.disableEntry( entry );
                    }

                } );

            }

        } );
        
    }    

    findPluginEntries() {
        return document.querySelectorAll( this.queries.pluginEntries );
    }

    disableEntry( entry ) {

        entry.style.display = "none";
        
        // Disable the input checkbox so the multi-selectors only work for the visible ones.
        let inputs = entry.querySelectorAll( this.queries.pluginEntryCheckbox );
        Array.from( inputs ).map( input => {
            input.disabled = true;
        } );

    }

    enableEntry( entry ) {

        this.numVisiblePerFilter++;

        entry.style.display = "";
        
        // Disable the input checkbox so the multi-selectors only work for the visible ones.
        let inputs = entry.querySelectorAll( this.queries.pluginEntryCheckbox );
        Array.from( inputs ).map( input => {
            input.disabled = false;
        } );        

    }

    countAllEntries() {
        return Array.from( this.findPluginEntries() ).length;
    }

    countVisibleEntries() {

        let boolTests = Array.from( this.findPluginEntries() ).map( e => e.style.display !== "none" );
        let numVisibleEntries = boolTests.filter( Boolean ).length;

        return numVisibleEntries;

    }

}
