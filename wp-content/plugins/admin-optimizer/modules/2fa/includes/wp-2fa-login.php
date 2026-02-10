<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress User 2FA Login Page
 *
 * Handles Two-factor authentication. Loads after user has logged in with password.
 */

/**
 * Outputs the login page header.
 *
 * @since 2.1.0
 *
 * @global string      $error         Login error message set by deprecated pluggable wp_login() function
 *                                    or plugins replacing it.
 * @global bool|string $interim_login Whether interim login modal is being displayed. String 'success'
 *                                    upon successful login.
 * @global string      $action        The action that brought the visitor to the login page.
 *
 * @param string|null   $title    Optional. WordPress login page title to display in the `<title>` element.
 *                                Defaults to 'Log In'.
 * @param string        $message  Optional. Message to display in header. Default empty.
 * @param WP_Error|null $wp_error Optional. The error to pass. Defaults to a WP_Error instance.
 */
function adminoptim_2fa_login_header( $title = null, $message = '', $wp_error = null ) {
	global $error, $interim_login, $action;

	if ( null === $title ) {
		$title = __( 'Log In', 'admin-optimizer' );
	}

	// Don't index any of these forms.
	add_filter( 'wp_robots', 'wp_robots_sensitive_page' );
	add_action( 'login_head', 'wp_strict_cross_origin_referrer' );

	add_action( 'login_head', 'wp_login_viewport_meta' );

	if ( ! is_wp_error( $wp_error ) ) {
		$wp_error = new WP_Error();
	}

	// Shake it!
	$shake_error_codes = [ 'empty_password', 'empty_email', 'invalid_email', 'invalidcombo', 'empty_username', 'invalid_username', 'incorrect_password', 'retrieve_password_email_failure' ];
	/**
	 * Filters the error codes array for shaking the login form.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $shake_error_codes Error codes that shake the login form.
	 */
	$shake_error_codes = apply_filters( 'shake_error_codes', $shake_error_codes ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	if ( $shake_error_codes && $wp_error->has_errors() && in_array( $wp_error->get_error_code(), $shake_error_codes, true ) ) {
		add_action( 'login_footer', 'wp_shake_js', 12 );
	}

	$login_title = get_bloginfo( 'name', 'display' );

	/* translators: Login screen title. 1: Login screen name, 2: Network or site name. */
	$login_title = sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress', 'admin-optimizer' ), $title, $login_title );

	if ( wp_is_recovery_mode() ) {
		/* translators: %s: Login screen title. */
		$login_title = sprintf( __( 'Recovery Mode &#8212; %s', 'admin-optimizer' ), $login_title );
	}

	/**
	 * Filters the title tag content for login page.
	 *
	 * @since 4.9.0
	 *
	 * @param string $login_title The page title, with extra context added.
	 * @param string $title       The original page title.
	 */
	$login_title = apply_filters( 'login_title', $login_title, $title ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	?><!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
	<title><?php echo esc_html( $login_title ); ?></title>
	<?php

	wp_enqueue_style( 'login' );

	/*
	 * Remove all stored post data on logging out.
	 * This could be added by add_action('login_head'...) like wp_shake_js(),
	 * but maybe better if it's not removable by plugins.
	 */
	if ( 'loggedout' === $wp_error->get_error_code() ) {
		$script = 'if("sessionStorage" in window){try{for(var key in sessionStorage){if(key.indexOf("wp-autosave-")!=-1){sessionStorage.removeItem(key)}}}catch(e){}};';
		wp_print_inline_script_tag( $script );
	}

	/**
	 * Enqueues scripts and styles for the login page.
	 *
	 * @since 3.1.0
	 */
	do_action( 'login_enqueue_scripts' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	/**
	 * Fires in the login page header after scripts are enqueued.
	 *
	 * @since 2.1.0
	 */
	do_action( 'login_head' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	$login_header_url = __( 'https://wordpress.org/', 'admin-optimizer' );

	/**
	 * Filters link URL of the header logo above login form.
	 *
	 * @since 2.1.0
	 *
	 * @param string $login_header_url Login header logo URL.
	 */
	$login_header_url = apply_filters( 'login_headerurl', $login_header_url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	$login_header_title = '';

	/**
	 * Filters the title attribute of the header logo above login form.
	 *
	 * @since 2.1.0
	 * @deprecated 5.2.0 Use {@see 'login_headertext'} instead.
	 *
	 * @param string $login_header_title Login header logo title attribute.
	 */
	$login_header_title = apply_filters_deprecated(
		'login_headertitle',
		[ $login_header_title ],
		'5.2.0',
		'login_headertext',
		__( 'Usage of the title attribute on the login logo is not recommended for accessibility reasons. Use the link text instead.', 'admin-optimizer' )
	);

	$login_header_text = empty( $login_header_title ) ? __( 'Powered by WordPress', 'admin-optimizer' ) : $login_header_title;

	/**
	 * Filters the link text of the header logo above the login form.
	 *
	 * @since 5.2.0
	 *
	 * @param string $login_header_text The login header logo link text.
	 */
	$login_header_text = apply_filters( 'login_headertext', $login_header_text ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	$classes = [ 'login-action-' . $action, 'wp-core-ui' ];

	if ( is_rtl() ) {
		$classes[] = 'rtl';
	}

	if ( $interim_login ) {
		$classes[] = 'interim-login';
		wp_register_style( 'admin-optim-2fa-login', false ); //phpcs:ignore
		wp_enqueue_style( 'admin-optim-2fa-login' );
		wp_add_inline_style( 'admin-optim-2fa-login', 'html{background-color: transparent;}' );

		if ( 'success' === $interim_login ) {
			$classes[] = 'interim-login-success';
		}
	}

	$classes[] = ' locale-' . sanitize_html_class( strtolower( str_replace( '_', '-', get_locale() ) ) );

	/**
	 * Filters the login page body classes.
	 *
	 * @since 3.5.0
	 *
	 * @param string[] $classes An array of body classes.
	 * @param string   $action  The action that brought the visitor to the login page.
	 */
	$classes = apply_filters( 'login_body_class', $classes, $action ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	?>
	</head>
	<body class="login no-js <?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php
	wp_print_inline_script_tag( "document.body.className = document.body.className.replace('no-js','js');" );
	?>

	<?php
	/**
	 * Fires in the login page header after the body tag is opened.
	 *
	 * @since 4.6.0
	 */
	do_action( 'login_header' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	?>
	<div id="login">
		<h1 role="presentation" class="wp-login-logo"><a href="<?php echo esc_url( $login_header_url ); ?>"><?php echo esc_html( $login_header_text ); ?></a></h1>
	<?php
	/**
	 * Filters the message to display above the login form.
	 *
	 * @since 2.1.0
	 *
	 * @param string $message Login message text.
	 */
	$message = apply_filters( 'login_message', $message ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	if ( ! empty( $message ) ) {
		echo esc_html( $message ) . "\n";
	}

	// In case a plugin uses $error rather than the $wp_errors object.
	if ( ! empty( $error ) ) {
		$wp_error->add( 'error', $error );
		unset( $error );
	}

	if ( $wp_error->has_errors() ) {
		$error_list = array();
		$messages   = '';

		foreach ( $wp_error->get_error_codes() as $code ) {
			$severity = $wp_error->get_error_data( $code );
			foreach ( $wp_error->get_error_messages( $code ) as $error_message ) {
				if ( 'message' === $severity ) {
					$messages .= '<p>' . $error_message . '</p>';
				} else {
					$error_list[] = $error_message;
				}
			}
		}

		if ( ! empty( $error_list ) ) {
			$errors = '';

			if ( count( $error_list ) > 1 ) {
				$errors .= '<ul class="login-error-list">';

				foreach ( $error_list as $item ) {
					$errors .= '<li>' . $item . '</li>';
				}

				$errors .= '</ul>';
			} else {
				$errors .= '<p>' . $error_list[0] . '</p>';
			}

			/**
			 * Filters the error messages displayed above the login form.
			 *
			 * @since 2.1.0
			 *
			 * @param string $errors Login error messages.
			 */
			$errors = apply_filters( 'login_errors', $errors ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

			wp_admin_notice(
				$errors,
				array(
					'type'           => 'error',
					'id'             => 'login_error',
					'paragraph_wrap' => false,
				)
			);
		}

		if ( ! empty( $messages ) ) {
			/**
			 * Filters instructional messages displayed above the login form.
			 *
			 * @since 2.5.0
			 *
			 * @param string $messages Login messages.
			 */
			$messages = apply_filters( 'login_messages', $messages ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

			wp_admin_notice(
				$messages,
				array(
					'type'               => 'info',
					'id'                 => 'login-message',
					'additional_classes' => array( 'message' ),
					'paragraph_wrap'     => false,
				)
			);
		}
	}
} // End of login_header().

/**
 * Outputs the footer for the login page.
 *
 * @since 3.1.0
 *
 * @global bool|string $interim_login Whether interim login modal is being displayed. String 'success'
 *                                    upon successful login.
 *
 * @param string $input_id Which input to auto-focus.
 */
function adminoptim_2fa_login_footer( $input_id = '' ) {
	global $interim_login;

	// Don't allow interim logins to navigate away from the page.
	if ( ! $interim_login ) {
		?>
		<p id="backtoblog">
			<?php
			$html_link = sprintf(
				'<a href="%s">%s</a>',
				esc_url( home_url( '/' ) ),
				sprintf(
					/* translators: %s: Site title. */
					_x( '&larr; Go to %s', 'site', 'admin-optimizer' ),
					get_bloginfo( 'title', 'display' )
				)
			);
			/**
			 * Filters the "Go to site" link displayed in the login page footer.
			 *
			 * @since 5.7.0
			 *
			 * @param string $link HTML link to the home URL of the current site.
			 */
			echo apply_filters( 'login_site_html_link', $html_link ); //phpcs:ignore
			?>
		</p>
		<?php

		the_privacy_policy_link( '<div class="privacy-policy-page-link">', '</div>' );
	}

	?>
	</div><?php // End of <div id="login">. ?>

	<?php

	if ( ! empty( $input_id ) ) {
		ob_start();
		?>
		try{document.getElementById('<?php echo esc_html( $input_id ); ?>').focus();}catch(e){}
		if(typeof wpOnload==='function')wpOnload();
		<?php
		wp_print_inline_script_tag( ob_get_clean() );
	}

	/**
	 * Fires in the login page footer.
	 *
	 * @since 3.1.0
	 */
	do_action( 'login_footer' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	?>
	</body>
	</html>
	<?php
}

if ( isset( $error ) && is_wp_error( $error ) ) {
	adminoptim_2fa_login_header( '', '', $error );
} else {
	adminoptim_2fa_login_header();
}

?>

<form name="adminoptim-2fa-login" id="loginform" action="<?php echo esc_url( add_query_arg( 'action', 'validate_2fa', site_url( 'wp-login.php', 'login_post' ) ) ); ?>" method="post" autocomplete="off">
		<input type="hidden" name="auth_id" id="auth-id" value="<?php echo esc_attr( $user->ID ); ?>" />
		<input type="hidden" name="action" id="action" value="validate_2fa" />
		<?php wp_nonce_field( 'adminoptim_2fa_login', 'adminoptim_2fa_nonce' ); ?>
		<?php if ( $interim_login ) { ?>
			<input type="hidden" name="interim-login" value="1" />
		<?php } else { ?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
		<?php } ?>
		<?php $rememberme = isset( $rememberme ) ? $rememberme : '0'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?> 
		<input type="hidden" name="rememberme" id="rememberme" value="<?php echo esc_attr( $rememberme ); ?>" />
		<div id="authenticator-wrap">
			<p class="two-factor-prompt">
				<?php esc_html_e( 'Enter the code generated by your authenticator app.', 'admin-optimizer' ); ?>
			</p>
			<p>
				<label for="authcode"><?php esc_html_e( 'Authentication Code:', 'admin-optimizer' ); ?></label>
				<input type="text" inputmode="numeric" autocomplete="one-time-code" name="authcode" id="authcode" class="input authcode" value="" size="20" pattern="[0-9 ]*" placeholder="123 456" />
			</p>
		</div>
		<?php if ( $can_add_recovery_checkbox ) : ?>
			<div id="recovery-wrap" style="display: none;">
				<p class="two-factor-prompt">
					<?php esc_html_e( 'Enter the recovery code.', 'admin-optimizer' ); ?>
				</p>
				<p>
					<label for="recoverycode"><?php esc_html_e( 'Recovery Code:', 'admin-optimizer' ); ?></label>
					<input type="text" name="recoverycode" id="recoverycode" class="input recoverycode" value="" size="20" pattern="[A-Z0-9]*" placeholder="" />
				</p>
			</div>
			<p>
				<label for="use-recovery-code">
					<input type="checkbox" name="use_recovery_code" id="use-recovery-code" value="1" /> <?php esc_html_e( 'Use recovery code instead', 'admin-optimizer' ); ?>
				</label>
			</p>
		<?php endif; ?>
		<?php if ( $can_trust_device ) : ?>
			<?php do_action( 'adminoptim_2fa_trust_device_field' ); ?>
		<?php endif; ?>
			<?php ob_start(); ?>
			setTimeout( function(){
				var d;
				try{
					d = document.getElementById('authcode');
					d.focus();
				} catch(e){}
			}, 200);
			rc = document.getElementById('use-recovery-code');
			if (rc) {
				let aa_wrap = document.getElementById('authenticator-wrap');
				let rc_wrap = document.getElementById('recovery-wrap');
				rc.addEventListener('click', function(e) {
					if (this.checked) {
						aa_wrap.style.display='none';
						rc_wrap.style.display='block';
					} else {
						aa_wrap.style.display='block';
						rc_wrap.style.display='none';
					}
				});
			}
			<?php
			wp_print_inline_script_tag( ob_get_clean() );
			?>
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Authenticate', 'admin-optimizer' ); ?>" />
		</p>
</form>
<?php
adminoptim_2fa_login_footer();