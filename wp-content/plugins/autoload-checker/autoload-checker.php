<?php
/*
Plugin Name: Autoload Checker
Version: 1.2
Description: Checks the autoloaded data size and lists the top autoloaded data entries sorted by size.
Author: Gerard Blanco
Author URI: https://accelerawp.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: autoload-checker
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

#
# Main Class: GRBLNC_Autoload_Checker - Solid prefix to avoid conflicts with other plugins
#

if( !class_exists( 'GRBLNC_Autoload_Checker' ) ){

	class GRBLNC_Autoload_Checker{

		function __construct(){
			// Call autoload_size_menu function to load plugin menu in dashboard
			add_action( 'admin_menu', [ $this, 'autoload_size_menu' ] );
		}

		// Create WordPress admin menu
		function autoload_size_menu() {
			$parent_slug = 'tools.php';
			$page_title  = 'Autoload Checker';
			$menu_title  = 'Autoload Checker';
			$capability  = 'manage_options';
			$menu_slug	 = 'autoload-checker';
			$function	 = [ $this, 'autoload_checker' ];

			add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		}

		// Create WordPress plugin page
		function autoload_checker() {
			?>
			<div class="wrap">
				<style>
					.ac-flex-container {
						display: flex;
						gap: 20px;
						align-items: flex-start;
					}
					.ac-promo-box {
						width:300px;
						border: 1px solid #c3c4c7;
						background-color: white;
						box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
						padding:15px;
						text-align:center;
					}

					/* Responsive: stack the promo box ABOVE the table */
					@media (max-width: 638px) {
						.ac-flex-container {
							flex-wrap: wrap;
						}
						.ac-promo-box {
							order: -1; /* Moves promo box above the table */
							width: 100% !important;
						}
						.ac-table-wrapper {
							width: 100%;
						}
					}
				</style>
				<h1><?php echo esc_html__( 'Total Autoload Size','autoload-checker' ); ?></h1>
				<?php
				global $wpdb;

				// Get autoloaded options
				$alloptions = wp_load_alloptions();

				// Initialize total size and toplist array
				$total_size_bytes = 0;
				$autoload_toplist = [];

				// Calculate total size and build top 20 list
				foreach ( $alloptions as $option_name => $option_value ) {
					// Serialize if needed to calculate size with overhead
					if ( is_array( $option_value ) || is_object( $option_value ) ) {
						$option_value = maybe_serialize( $option_value );
					}

					$size_bytes = strlen( (string) $option_value );
					$total_size_bytes += $size_bytes;

					// Add to toplist array
					$autoload_toplist[] = (object) [
						'option_name'       => $option_name,
						'option_value_size' => $size_bytes / 1024, // Convert bytes to KB
					];
				}

				// Sort toplist by size (descending) and get top 30
				usort( $autoload_toplist, function( $a, $b ) {
					return $b->option_value_size <=> $a->option_value_size;
				});
				$autoload_toplist = array_slice( $autoload_toplist, 0, 30 );

				// Convert total size to KB
				$total_size_kb = round( $total_size_bytes / 1024 );

				// Display total size
				?>
				<p style="font-weight:bold;font-size:1.5em;"><?php echo esc_html( $total_size_kb . ' KB' ); ?></p>

				<h2><?php echo esc_html__( 'Autoload Top List:', 'autoload-checker' ); ?></h2>
				<div class="ac-flex-container">
					<div class="ac-table-wrapper">
					<table style="max-width:600px;" class="widefat striped">
						<thead><tr>
							<th scope="col"><?php echo esc_html__( '#', 'autoload-checker' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Option Name', 'autoload-checker' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Size', 'autoload-checker' ); ?></th>
						</tr></thead>
						<tbody>
							<?php foreach ( $autoload_toplist as $k => $option ) :
								$index = $k + 1;
								$size  = round( $option->option_value_size ) . ' KB';
							?>
							<tr>
								<td><?php echo esc_html( $index ); ?></td>
								<td><?php echo esc_html( $option->option_name ); ?></td>
								<td><?php echo esc_html( $size ); ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					</div>
					<div class="ac-promo-box">
						<a target="_blank" style="display:block;max-width:300px;margin:0 auto;" href="https://accelerawp.com/?utm_source=autoload+checker"><img src="<?php echo esc_url( plugins_url( 'logo.png', __FILE__ ) ); ?>" alt="Accelera Logo" style="max-width:100%;"></a>
						<p style="font-size:14px;">
							Need help? Check out <a target="_blank" href="https://accelerawp.com/?utm_source=autoload+checker"><strong>Accelera</strong></a> for professional WordPress optimization services.
						</p>
					</div>
				</div>
			<?php
		}
	}


	# Run the class
	new GRBLNC_Autoload_Checker;

}
?>
