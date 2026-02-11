<?php
/**
 * Classic Password Protection Theme
 * @since 1.1.0
 * @package AdminEase\PasswordProtectSite\Themes
 */

namespace AdminEase\Features\PasswordProtectSite\Themes;

use AdminEase\FileHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Classic Password Protection Theme
 * This class is responsible for rendering a styled password protection form
 * for the AdminEase Password Protect Site feature.
 * @package AdminEase\PasswordProtectSite\Themes
 */
class Classic {
	/**
	 * Render the password protection form
	 *
	 * @param array $args Theme rendering arguments.
	 *
	 * @return string Rendered HTML form.
	 */
	public static function render( array $args = [] ): string {
		$data = self::prepare_data( $args );
		$css  = self::get_theme_css();
		$js   = self::get_theme_js();
		
		return self::generate_html( $data, $css, $js );
	}
	
	/**
	 * Prepares and merges default data with provided arguments for configuring a password-protected site.
	 *
	 * @param array $args An associative array of arguments to override the default values. Possible keys include:
	 *                    'logo_url' (string) - The URL of the logo to display (optional).
	 *                    'entry_message' (string) - The message displayed for password entry prompt.
	 *                    'remember_device' (bool) - Whether to include the "Remember this device" feature.
	 *
	 * @return array The resulting array after merging user-provided arguments with default values.
	 */
	private static function prepare_data( array $args ): array {
		$defaults = [
			'logo_url'        => '',
			'entry_message'   => __( 'This site is password protected. Please enter the access password below.', 'adminease' ),
			'remember_device' => true,
		];
		
		return wp_parse_args( $args, $defaults );
	}
	
	/**
	 * Retrieves the CSS file content for the theme.
	 * Reads a CSS file from the predefined path and returns its content.
	 * If the file cannot be read, an empty string is returned.
	 * @return string The content of the CSS file, or an empty string if the file cannot be read.
	 */
	private static function get_theme_css(): string {
		$css_path = ADMINEASE_DIR . 'assets/css/AdminEasePasswordProtectSite.css';
		
		$content = FileHandler::get_instance()->get_file_content( $css_path );
		
		return ( false !== $content ) ? $content : '';
	}
	
	/**
	 * Retrieves the JavaScript content for the theme from a specified file.
	 * This method reads the JavaScript file located at the predefined path and returns its contents.
	 * If the file cannot be read, it returns an empty string.
	 * @return string The content of the JavaScript file as a string, or an empty string if the file cannot be read.
	 */
	private static function get_theme_js(): string {
		$js_path = ADMINEASE_DIR . 'assets/js/AdminEasePasswordProtectSite.js';
		
		$content = FileHandler::get_instance()->get_file_content( $js_path );
		
		return ( false !== $content ) ? $content : '';
	}
	
	/**
	 * Generates an HTML string for a password-protected site interface based on input data.
	 *
	 * @param array  $data An associative array containing data for rendering the HTML. Expected keys include:
	 *                    'page_title' (string) - The title for the HTML page.
	 *                    'primary_color' (string) - The primary color for the page styling.
	 *                    'secondary_color' (string) - The secondary color for the page styling.
	 *                    'text_color' (string) - The text color for the page styling.
	 *                    'logo_url' (string|null) - The URL of the logo to display (optional).
	 *                    'entry_message' (string) - The welcome message to display.
	 *                    'remember_device' (bool) - Whether to include the "Remember this device" option.
	 * @param string $css A string containing custom CSS to be included in the page.
	 * @param string $js A string containing custom JavaScript to be included in the page.
	 *
	 * @return string The generated HTML content as a string.
	 */
	private static function generate_html( array $data, string $css, string $js ): string {
		ob_start();
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( $data['page_title'] ); ?></title>
			<?php do_action( 'adminease_password_protect_site_head' ); ?>
			<link rel="stylesheet" href="<?php echo esc_url( includes_url( 'css/dashicons.min.css' ) ); ?>">
			<style><?php echo wp_strip_all_tags( $css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
			<style>
                :root {
                    --primary-color: <?php echo esc_attr( $data['primary_color'] ); ?>;
                    --secondary-color: <?php echo esc_attr( $data['secondary_color'] ); ?>;
                    --text-color: <?php echo esc_attr( $data['text_color'] ); ?>;
                }
			</style>
			<script src="<?php echo esc_url( includes_url( 'js/jquery/jquery.min.js' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>"></script>
			<script><?php echo $js; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
			<script>
				var AdminEasePasswordProtectSiteAjaxObj = <?php echo wp_json_encode( [
					'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
					'security' => [
						'ajaxNonce' => wp_create_nonce( 'adminease_site_password' ),
					],
					'i18n'     => [
						'generalError' => __( 'An error occurred. Refresh the page and try again.', 'adminease' ),
					],
				] ); ?>;
			</script>
		</head>
		<body class="adminease-pps-page">
		<?php do_action( 'adminease_password_protect_site_body_start' ); ?>
		<div class="adminease-pps classic-theme">
			<div class="login-container">
				<div class="login-card">
					<div class="login-header">
						<?php
						if( !empty( $data['logo_url'] ) ) {
							?>
							<img src="<?php echo esc_url( $data['logo_url'] ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="logo">
							<?php
						}
						?>
						
						<h1 class="site-title"><?php echo esc_html( $data['headline'] ); ?></h1>
						
						<p class="welcome-message">
							<?php echo wp_kses_post( $data['entry_message'] ); ?>
						</p>
					</div>
					
					<form id="ae-password-form" method="post" class="login-form">
						<div class="input-group">
							<div class="input-wrapper">
								<svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
								</svg>
								<input type="password" name="password" id="password" class="form-input" required autocomplete="off" placeholder="" aria-required="true">
								<label for="password" class="form-label">
									<?php esc_html_e( 'Enter Password', 'adminease' ); ?>
								</label>
							</div>
						</div>
						
						<?php
						if( $data['remember_device'] ) {
							?>
							<div class="checkbox-group">
								<label class="checkbox-wrapper">
									<input type="checkbox" name="remember_device" id="remember_device" value="1" class="checkbox-input">
									<span class="checkbox-custom">
										<svg class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
										</svg>
									</span>
									<span class="checkbox-text"><?php esc_html_e( 'Remember this device', 'adminease' ); ?></span>
								</label>
							</div>
							<?php
						}
						?>
						
						<button type="submit" class="login-button">
							<span class="button-text">
								<?php esc_html_e( 'Access Site', 'adminease' ); ?>
							</span>
							<span class="loading-spinner"></span>
						</button>
						
						<div id="ae-message-container" class="message-container" role="alert"></div>
					</form>
				</div>
			</div>
		</div>
		<?php do_action( 'adminease_password_protect_site_body_end' ); ?>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}