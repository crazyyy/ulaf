(function( wp, $ ){

	if ( ! wp || ! wp.customize ) { return; }
	api = wp.customize;

	/**
	 * wp.customize.PluginControl
	 *
	 * @constructor
	 * @augments wp.customize.Control
	 */
	api.PluginControl = api.Control.extend({

		/**
		 * @since X.X.X
		 */
		ready: function() {
			var control = this;

			// Don't do anything for network-active plugins.
			if ( 'network' === control.params.status ) {
				return;
			}

			// Bind actions.
			control.container.on( 'change', 'input[type="checkbox"]', function( event ) {
				var plugins = api.instance( 'active_plugins' ).get(),
					i = plugins.indexOf( control.params.plugin );

				if( $( this ).is( ':checked' ) && -1 === i ) {
					// Add to active plugins list.
					plugins = plugins.concat( [ control.params.plugin ] );
					api.instance( 'active_plugins' ).set( plugins );
				} else if ( -1 !== i ) {
					// Remove from active plugins list.
					plugins.splice( i, 1 );
					api.instance( 'active_plugins' ).set( plugins );
					api.previewer.refresh(); // Dirty state isn't set either.
				}
			});
		}
	});

	/**
	 * extend wp.customize.controlConstructor with the custom plugin control.
	 */
	$.extend( api.controlConstructor, {
		plugin: api.PluginControl
	});

})( window.wp, jQuery );