<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nextgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 * Copied from Bob, Namespaced, fixed add_filter instead of add_action and make use of wp_trigger_error
 *
 * @copyright 2024 Bob Matyas
 * https://wordpress.org/plugins/exif-remover/
 *
 * @wordpress-plugin
 * Plugin Name:     EXIF Remover
 * Description:     Removes EXIF data from image uploads
 * Author:          Nicolas Jonas
 * Author URI:      https://nextgenthemes.com
 * Version:         1.0.0
 * License:         GPLv3
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.html
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

use Imagick;
use ImagickPixel;
use Exception;

add_filter( 'wp_handle_upload', __NAMESPACE__ . '\remove_exif' );
/**
 * Handle an image upload by removing EXIF data.
 *
 * @param  array<string, mixed> $upload A single array element containing the details of the uploaded file.
 * @return array<string, mixed>         The processed upload array.
 */
function remove_exif( array $upload ): array {
	if ( 'image/jpeg' === $upload['type'] || 'image/jpg' === $upload['type'] ) {
		$filename = $upload['file'];
		// Check for Imagick.
		if ( class_exists( 'Imagick' ) ) {

			$image = new Imagick( $filename );

			if ( ! $image->valid() ) {
				return $upload;
			}

			try {
				// Preserve image orientation.
				$orientation = $image->getImageOrientation();
				switch ( $orientation ) {
					case Imagick::ORIENTATION_BOTTOMRIGHT:
						$image->rotateImage( new ImagickPixel(), 180 );
						break;
					case Imagick::ORIENTATION_RIGHTTOP:
						$image->rotateImage( new ImagickPixel(), 90 );
						break;
					case Imagick::ORIENTATION_LEFTBOTTOM:
						$image->rotateImage( new ImagickPixel(), -90 );
						break;
				}
				$image->setImageOrientation( Imagick::ORIENTATION_TOPLEFT );
				$image->writeImage();
				$image->clear();
				$image->destroy();
			} catch ( Exception $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
					wp_trigger_error( __FUNCTION__, 'Unable to read EXIF data via Imagick for: ' . $filename . ' - ' . $e->getMessage() );
				}
			}
		} elseif ( function_exists( 'imagecreatefromjpeg' ) ) {
			// Fallback to GD.
			$image = imagecreatefromjpeg( $filename );

			if ( $image ) {
				try {
					// Preserve image orientation.
					$exif = exif_read_data( $filename );
					if ( ! empty( $exif['Orientation'] ) ) {
						switch ( $exif['Orientation'] ) {
							case 3:
								$image = imagerotate( $image, 180, 0 );
								break;
							case 6:
								$image = imagerotate( $image, -90, 0 );
								break;
							case 8:
								$image = imagerotate( $image, 90, 0 );
								break;
						}
					}
					imagejpeg( $image, $filename, 100 );
					imagedestroy( $image );
				} catch ( Exception $e ) {
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
						wp_trigger_error( __FUNCTION__, 'Unable to read EXIF data via GD for: ' . $filename . ' - ' . $e->getMessage() );
					}
				}
			}
		}
	}

	return $upload;
}
