<?php
/**
 * Icons for Plugins - Plugins Page
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\IFP;

if ( ! class_exists( 'TxToIT\IFP\Plugins_Page' ) ) {

	class Plugins_Page {

		public static function add_icons_column_header( $columns ) {
			$col_position = Admin_Settings::get_option_col_position();

			$res = array_slice( $columns, 0, $col_position, true ) +
				   array( 'ifp_icon' => '' ) +
				   array_slice( $columns, $col_position, count( $columns ) - 1, true );
			return $res;
		}

		/**
		 * Test
		 */
		public static function add_icons_column_content( $column, $plugin_full_name, $infs ) {
			if ( $column == 'ifp_icon' ) {

				$grayscale       = filter_var( Admin_Settings::get_option_grayscale(), FILTER_VALIDATE_BOOLEAN );
				$grayscale_class = $grayscale ? 'grayscale' : '';
				$image_url       = Plugin_Image::get_image_url( $infs );
				$guess_icons     = filter_var( Admin_Settings::get_option_guess_icons(), FILTER_VALIDATE_BOOLEAN );

				if ( empty( $image_url ) ) {
					if ( $guess_icons ) {
						if ( strpos( $plugin_full_name, '/' ) !== false ) {
							$image_url_guess_1 = Plugin_Image::guess_plugin_image_url_by_name_after_dash( $plugin_full_name );
							// wp_log(get_headers($image_url_guess_1));
							$image_url_guess_2 = Plugin_Image::guess_plugin_image_url_by_name_after_dash( $plugin_full_name, 'jpg' );
							$image_url_guess_3 = Plugin_Image::guess_plugin_image_url_by_name_before_dash( $plugin_full_name );
							$image_url_guess_4 = Plugin_Image::guess_plugin_image_url_by_name_before_dash( $plugin_full_name, 'jpg' );
						} else {
							$image_url_guess_5 = Plugin_Image::guess_plugin_image_url( sanitize_title( $infs['Title'] ), 'jpg' );
							$image_url_guess_6 = Plugin_Image::guess_plugin_image_url( sanitize_title( $infs['Title'] ), 'png' );
						}
					}
				}

				?>
				<div class="ifp-icon-wrapper">
					<?php if ( ! empty( $image_url ) ) { ?>
						<div class="ifp-icon <?php echo $grayscale_class; ?>"
							 style="background-image:url(<?php echo $image_url; ?>)"></div>
					<?php } else { ?>
						<div class="ifp-icon no-img <?php echo $grayscale_class; ?>"></div>
						<?php if ( $guess_icons ) { ?>

							<?php if ( strpos( $plugin_full_name, '/' ) !== false ) { ?>
								<div class="ifp-icon ifp-guess <?php echo $grayscale_class; ?>"
									 data-guess-img="<?php echo $image_url_guess_1; ?>"
									 style="background-image:url(<?php echo $image_url_guess_1; ?>)"></div>
								<div class="ifp-icon ifp-guess <?php echo $grayscale_class; ?>"
									 data-guess-img="<?php echo $image_url_guess_2; ?>"
									 style="background-image:url(<?php echo $image_url_guess_2; ?>)"></div>
								<div class="ifp-icon ifp-guess <?php echo $grayscale_class; ?>"
									 data-guess-img="<?php echo $image_url_guess_3; ?>"
									 style="background-image:url(<?php echo $image_url_guess_3; ?>)"></div>
								<div class="ifp-icon ifp-guess <?php echo $grayscale_class; ?>"
									 data-guess-img="<?php echo $image_url_guess_4; ?>"
									 style="background-image:url(<?php echo $image_url_guess_4; ?>)"></div>
							<?php } else { ?>
								<div class="ifp-icon ifp-guess <?php echo $grayscale_class; ?>"
									 data-guess-img="<?php echo $image_url_guess_5; ?>"
									 style="background-image:url(<?php echo $image_url_guess_5; ?>)"></div>
								<div class="ifp-icon ifp-guess <?php echo $grayscale_class; ?>"
									 data-guess-img="<?php echo $image_url_guess_6; ?>"
									 style="background-image:url(<?php echo $image_url_guess_6; ?>)"></div>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				</div>
				<?php
			}
		}

		public static function add_js() {
			?>
			<script>
				var ifp_js = {
					guess_img_selector: '.ifp-guess',
					empty_img_selector: '.ifp-icon.no-img',
					guess_img: null,

					remove_unnecessary_empty_imgs: function () {
						this.guess_img = document.querySelectorAll(this.guess_img_selector);
						if (this.guess_img) {
							Array.prototype.forEach.call(this.guess_img, function (el, i) {
								var img_src = el.getAttribute('data-guess-img');
								var img = new Image();
								img.onload = function () {
									var empty_img = el.parentNode.querySelector(ifp_js.empty_img_selector);
									if (empty_img) {
										empty_img.parentNode.removeChild(empty_img);
									}
								}
								img.src = img_src;
							});
						}
					}
				};

				document.onreadystatechange = function () {
					if (document.readyState == "interactive") {
						ifp_js.remove_unnecessary_empty_imgs();
					}
				}
			</script>
			<?php
		}

		public static function add_style() {
			$width             = filter_var( Admin_Settings::get_option_icon_width(), FILTER_SANITIZE_NUMBER_INT );
			$height            = filter_var( Admin_Settings::get_option_icon_height(), FILTER_SANITIZE_NUMBER_INT );
			$empty_img         = Admin_Settings::get_option_empty_icon_url();
			$empty_img_opacity = Admin_Settings::get_option_empty_icon_opacity();
			$bkg_size          = Admin_Settings::get_option_icon_background_size();
			?>
			<style>
				#ifp_icon {
					/*padding:0 80px 0 0;*/
				}

				.column-ifp_icon {
					padding-left: 13px !important;
					width: 1px;
				}

				.ifp-icon-wrapper {
					position: relative;
					width: <?php echo $width; ?>px;
					height: <?php echo $height; ?>px;
					display: inline-block;
				}

				.ifp-icon.no-img {
					background-image: url('<?php echo esc_url( $empty_img ); ?>');
					background-size: <?php echo esc_html( $bkg_size ); ?>;
					opacity: <?php echo $empty_img_opacity; ?>;
				}

				.ifp-icon {
					margin-top: 2px;
					-webkit-transform: translate(-50%, -50%);
					-moz-transform: translate(-50%, -50%);
					-ms-transform: translate(-50%, -50%);
					-o-transform: translate(-50%, -50%);
					transform: translate(-50%, -50%);
					top: 50%;
					left: 50%;
					position: absolute;
					width: 100%;
					height: 100%;
					background-size: <?php echo esc_html( $bkg_size ); ?>;
					background-position: center center;
					background-repeat: no-repeat;
				}

				.ifp-icon.grayscale {
					-webkit-filter: grayscale(100%);
					filter: grayscale(100%);
					transition: all 0.25s ease-in-out;
				}

				.ifp-icon-wrapper:hover .ifp-icon.grayscale {
					-webkit-filter: grayscale(0);
					filter: grayscale(0);
				}
			</style>
			<?php
		}
	}
}
