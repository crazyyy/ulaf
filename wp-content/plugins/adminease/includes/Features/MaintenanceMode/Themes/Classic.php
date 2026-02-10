<?php
/**
 * Classic MaintenanceMode Theme
 * @since 1.1.0
 * @package AdminEase\MaintenanceMode\Themes
 */

namespace AdminEase\Features\MaintenanceMode\Themes;

use AdminEase\FileHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Classic maintenance mode theme class
 * This class is responsible for rendering the classic maintenance mode UI theme.
 * It provides a method to generate and display the maintenance mode password protection form
 * with customizable theme arguments.
 * @package AdminEase\Features\MaintenanceMode\Themes
 */
class Classic {
	
	/**
	 * Renders the HTML content based on the provided arguments and theme styles.
	 *
	 * @param array $args Optional. An associative array of arguments used to prepare data for rendering.
	 *
	 * @return string The generated HTML content as a string.
	 */
	public static function render( array $args = [] ): string {
		$data = self::prepare_data( $args );
		$css  = self::get_theme_css();
		
		return self::generate_html( $data, $css );
	}
	
	/**
	 * Prepares and merges data with default values for the maintenance mode page.
	 *
	 * @param array $args An associative array of input arguments to customize the maintenance mode page.
	 *                    Possible keys include 'page_title', 'headline', 'logo_url', 'message',
	 *                    'primary_color', 'secondary_color', and 'text_color'.
	 *
	 * @return array The resulting associative array after merging the input arguments with predefined defaults.
	 */
	private static function prepare_data( array $args ): array {
		$defaults = [
			'page_title'      => get_bloginfo( 'name' ) . ' - ' . __( 'Maintenance Mode', 'adminease' ),
			'headline'        => get_bloginfo( 'name' ),
			'logo_url'        => '',
			'message'         => __( 'We are currently performing scheduled maintenance.', 'adminease' ),
			'primary_color'   => '#0073aa',
			'secondary_color' => '#23282d',
			'text_color'      => '#333333',
		];
		
		return wp_parse_args( $args, $defaults );
	}
	
	/**
	 * Retrieves the theme CSS content from a specified file path.
	 * This method reads the CSS file used for the theme and returns its content as a string.
	 * If the file content cannot be retrieved, an empty string is returned.
	 * @return string The content of the theme CSS file, or an empty string if the content cannot be fetched.
	 */
	private static function get_theme_css(): string {
		$css_path = ADMINEASE_DIR . 'assets/css/AdminEaseMaintenanceMode.css';
		
		// Use your FileHandler class
		$content = FileHandler::get_instance()->get_file_content( $css_path );
		
		return ( false !== $content ) ? $content : '';
	}
	
	/**
	 * Generates an HTML page template with customized data and styling.
	 *
	 * @param array  $data An associative array containing page data such as 'page_title', 'logo_url', 'headline', and 'message'.
	 * @param string $css A string containing custom CSS styles to be included within the HTML.
	 *
	 * @return string The generated HTML content as a string.
	 */
	private static function generate_html( array $data, string $css ): string {
		ob_start();
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( $data['page_title'] ); ?></title>
			<?php do_action( 'adminease_maintenance_mode_head' ); ?>
			<style><?php echo wp_strip_all_tags( $css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
			<style>
                :root {
                    --primary-color: <?php echo esc_attr( $data['primary_color'] ); ?>;
                    --secondary-color: <?php echo esc_attr( $data['secondary_color'] ); ?>;
                    --text-color: <?php echo esc_attr( $data['text_color'] ); ?>;
                }
			</style>
		</head>
		<body class="adminease-mm-page">
		<?php do_action( 'adminease_maintenance_body_start' ); ?>
		<div class="adminease-mm classic-theme">
			<div class="login-container">
				<div class="login-card">
					<div class="login-header">
						<?php
						if( !empty( $data['logo_url'] ) ) {
							?>
							<img src="<?php echo esc_url( $data['logo_url'] ); ?>" alt="<?php echo esc_attr( $data['headline'] ); ?>" class="logo">
							<?php
						}
						?>
						
						<h1 class="site-title"><?php echo esc_html( $data['headline'] ); ?></h1>
						
						<div class="welcome-message">
							<?php echo wp_kses_post( $data['message'] ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php do_action( 'adminease_maintenance_body_end' ); ?>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}