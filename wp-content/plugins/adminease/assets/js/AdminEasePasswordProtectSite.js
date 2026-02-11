/**
 * AdminEase Password Protection - Clean JavaScript
 * Simple AJAX form handling with error messages
 */
jQuery( document ).ready( function( $ ) {
	var AdminEasePasswordProtectSite = {
		form: null,
		submitButton: null,
		messageContainer: null,
		isSubmitting: false,
		
		/**
		 * Initialize the password protection functionality
		 */
		init: function() {
			this.form = $( '#ae-password-form' );
			this.submitButton = this.form.find( '.login-button' );
			this.messageContainer = $( '#ae-message-container' );
			
			if( this.form.length === 0 ) {
				return;
			}
			
			this.bindEvents();
			this.setupAccessibility();
			this.focusPasswordField();
		},
		
		/**
		 * Bind event handlers
		 */
		bindEvents: function() {
			// Form submission
			this.form.on( 'submit', this.handleSubmit.bind( this ) );
			
			// Enter key on password field
			this.form.find( '#password' ).on( 'keypress', function( e ) {
				if( e.which === 13 ) {
					this.form.submit();
				}
			}.bind( this ) );
			
			// Clear messages on input
			this.form.find( '#password' ).on( 'input', this.clearMessages.bind( this ) );
			
			// Show/hide password functionality
			this.setupPasswordToggle();
		},
		
		/**
		 * Setup accessibility features
		 */
		setupAccessibility: function() {
			// Set ARIA labels
			this.form.find( '#password' ).attr( 'aria-describedby', 'password-description' );
			this.messageContainer.attr( 'aria-live', 'polite' );
			
			// Keyboard navigation for checkbox
			this.form.find( '.checkbox-wrapper' ).on( 'keydown', function( e ) {
				if( e.which === 32 || e.which === 13 ) {
					e.preventDefault();
					$( this ).find( 'input[type="checkbox"]' ).click();
				}
			} );
		},
		
		/**
		 * Focus password field on page load
		 */
		focusPasswordField: function() {
			setTimeout( () => {
				this.form.find( '#password' ).focus();
			}, 100 );
		},
		
		/**
		 * Handle form submission
		 */
		handleSubmit: function( e ) {
			e.preventDefault();
			
			if( this.isSubmitting ) {
				return false;
			}
			
			const password = this.form.find( '#password' ).val().trim();
			const rememberDevice = this.form.find( '#remember_device' ).is( ':checked' );
			const nonce = this.form.find( '[name="nonce"]' ).val();
			
			// Basic validation
			if( !password ) {
				this.showMessage( 'error', 'Please enter a password.' );
				this.form.find( '#password' ).focus();
				return false;
			}
			
			// Show loading state
			this.setLoadingState( true );
			this.clearMessages();
			
			// Prepare data
			const formData = {
				action: 'adminease_site_password_check',
				security: AdminEasePasswordProtectSiteAjaxObj[ 'security' ][ 'ajaxNonce' ],
				data: {
					password: password,
					remember_device: rememberDevice ? '1' : '0',
					current_url: window.location.href,
				},
			};
			
			// Make AJAX request
			$.ajax( {
				url: AdminEasePasswordProtectSiteAjaxObj[ 'ajaxUrl' ],
				type: 'POST',
				data: formData,
				timeout: 10000,
				success: this.handleSuccess.bind( this ),
				error: this.handleError.bind( this ),
				complete: () => {
					this.setLoadingState( false );
				},
			} );
			
			return false;
		},
		
		/**
		 * Handle successful authentication
		 */
		handleSuccess: function( response ) {
			if( response.success && response.data ) {
				this.showMessage( 'success', response[ 'data' ][ 'message' ] );
				
				// Redirect after short delay
				setTimeout( () => {
					window.location.href = response[ 'data' ][ 'redirect_url' ] || '/';
				}, 1500 );
			}
			else {
				this.handleError( response );
			}
		},
		
		/**
		 * Handle authentication error
		 */
		handleError: function( response ) {
			let message = '';
			
			if( response && response[ 'data' ] && response[ 'data' ][ 0 ][ 'message' ] ) {
				message = response[ 'data' ][ 0 ][ 'message' ];
			}
			else {
				message = AdminEasePasswordProtectSiteAjaxObj[ 'i18n' ][ 'generalError' ];
			}
			
			
			this.showMessage( 'error', message );
			
			// Clear password field on error
			this.form.find( '#password' ).val( '' ).focus();
			
			// Shake effect
			this.form.addClass( 'shake' );
			setTimeout( () => {
				this.form.removeClass( 'shake' );
			}, 600 );
		},
		
		/**
		 * Set loading state for the form
		 */
		setLoadingState: function( loading ) {
			this.isSubmitting = loading;
			
			if( loading ) {
				this.submitButton.addClass( 'loading' ).prop( 'disabled', true );
				this.form.find( 'input' ).prop( 'disabled', true );
			}
			else {
				this.submitButton.removeClass( 'loading' ).prop( 'disabled', false );
				this.form.find( 'input' ).prop( 'disabled', false );
			}
		},
		
		/**
		 * Show message to user
		 */
		showMessage: function( type, message ) {
			this.messageContainer
				.removeClass( 'error success info' )
				.addClass( type + ' show' )
				.html( '<div>' + this.escapeHtml( message ) + '</div>' );
			
			// Auto-hide success messages
			if( type === 'success' ) {
				setTimeout( () => {
					this.clearMessages();
				}, 3000 );
			}
		},
		
		/**
		 * Clear all messages
		 */
		clearMessages: function() {
			this.messageContainer
				.removeClass( 'error success info show' )
				.empty();
		},
		
		/**
		 * Setup show/hide password toggle
		 */
		setupPasswordToggle: function() {
			var passwordField = $( '#password' );
			var toggleButton = $( '<button type="button" class="password-toggle" aria-label="Show password"><span class="dashicons dashicons-visibility"></span></button>' );

			passwordField.parent().append( toggleButton );

			toggleButton.on( 'click', function() {
				var isPassword = passwordField.attr( 'type' ) === 'password';
				passwordField.attr( 'type', isPassword ? 'text' : 'password' );
				toggleButton.find( '.dashicons' )
					.toggleClass( 'dashicons-visibility', !isPassword )
					.toggleClass( 'dashicons-hidden', isPassword );
				toggleButton.attr( 'aria-label', isPassword ? 'Hide password' : 'Show password' );
			} );
		},
		
		/**
		 * Escape HTML to prevent XSS
		 */
		escapeHtml: function( text ) {
			const div = document.createElement( 'div' );
			div.textContent = text;
			return div.innerHTML;
		},
	};
	
	AdminEasePasswordProtectSite.init();
} );