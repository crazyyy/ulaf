<?php
/**
 * WP SCSS Compiler
 *
 * @package ALC_SCSS
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

/**
 * WP SCSS Compiler Class
 */
class WP_SCSS_Compiler {

	/**
	 * SCSS Compiler instance
	 *
	 * @var Compiler
	 */
	private $compiler;

	/**
	 * Cache directory
	 *
	 * @var string
	 */
	private $cache_dir;

	/**
	 * Source directory
	 *
	 * @var string
	 */
	private $source_dir;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize compiler
		$this->compiler = new Compiler();

		// Set output style
		$this->compiler->setOutputStyle( OutputStyle::COMPRESSED );

		// Set cache directory
		$upload_dir      = wp_upload_dir();
		$this->cache_dir = $upload_dir['basedir'] . '/scss-cache/';

		// Create cache directory if it doesn't exist
		if ( ! file_exists( $this->cache_dir ) ) {
			wp_mkdir_p( $this->cache_dir );
		}

		// Add filter to compile SCSS files
		add_filter( 'style_loader_src', array( $this, 'parse_stylesheet' ), 10, 2 );
	}

	/**
	 * Parse stylesheet and compile if SCSS
	 *
	 * @param string $src    Stylesheet URL.
	 * @param string $handle Stylesheet handle.
	 * @return string
	 */
	public function parse_stylesheet( $src, $handle ) {
		// Skip if not a local file
		if ( false === strpos( $src, home_url() ) ) {
			return $src;
		}

		// Get file path
		$file_path = $this->get_file_path_from_url( $src );

		// Check if file exists and is SCSS
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return $src;
		}

		// Only process SCSS files
		if ( ! preg_match( '/\.scss$/i', $file_path ) ) {
			return $src;
		}

		// Compile SCSS file
		$compiled_url = $this->compile_scss_file( $file_path, $src );

		return $compiled_url ? $compiled_url : $src;
	}

	/**
	 * Compile SCSS file
	 *
	 * @param string $file_path Source SCSS file path.
	 * @param string $src       Original URL.
	 * @return string|false Compiled CSS URL or false on failure.
	 */
	private function compile_scss_file( $file_path, $src ) {
		try {
			// Generate cache filename
			$cache_filename = md5( $file_path ) . '.css';
			$cache_file     = $this->cache_dir . $cache_filename;
			$upload_dir     = wp_upload_dir();
			$cache_url      = $upload_dir['baseurl'] . '/scss-cache/' . $cache_filename;

			// Check if we need to recompile
			if ( file_exists( $cache_file ) ) {
				$scss_mtime = filemtime( $file_path );
				$css_mtime  = filemtime( $cache_file );

				// Return cached version if source hasn't changed
				if ( $css_mtime >= $scss_mtime ) {
					return $cache_url;
				}
			}

			// Set import paths
			$source_dir = dirname( $file_path );
			$this->compiler->setImportPaths( $source_dir );

			// Read SCSS content
			$scss_content = file_get_contents( $file_path );

			if ( false === $scss_content ) {
				return false;
			}

			// Compile SCSS
			$compiled_css = $this->compiler->compileString( $scss_content )->getCss();

			// Save compiled CSS
			$result = file_put_contents( $cache_file, $compiled_css );

			if ( false === $result ) {
				error_log( 'WP_SCSS: Failed to write compiled CSS to ' . $cache_file );
				return false;
			}

			return $cache_url;

		} catch ( Exception $e ) {
			error_log( 'WP_SCSS Compilation Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get file path from URL
	 *
	 * @param string $url File URL.
	 * @return string|false File path or false.
	 */
	private function get_file_path_from_url( $url ) {
		// Remove query string
		$url = strtok( $url, '?' );

		// Get WordPress paths
		$content_url = content_url();
		$content_dir = WP_CONTENT_DIR;

		// Check if URL is in content directory
		if ( false !== strpos( $url, $content_url ) ) {
			$file_path = str_replace( $content_url, $content_dir, $url );
			return $file_path;
		}

		// Check if URL is in plugins directory
		$plugins_url = plugins_url();
		$plugins_dir = WP_PLUGIN_DIR;

		if ( false !== strpos( $url, $plugins_url ) ) {
			$file_path = str_replace( $plugins_url, $plugins_dir, $url );
			return $file_path;
		}

		// Check if URL is in theme directory
		$theme_url = get_stylesheet_directory_uri();
		$theme_dir = get_stylesheet_directory();

		if ( false !== strpos( $url, $theme_url ) ) {
			$file_path = str_replace( $theme_url, $theme_dir, $url );
			return $file_path;
		}

		return false;
	}

	/**
	 * Clear cache
	 *
	 * @return bool True on success, false on failure.
	 */
	public function clear_cache() {
		if ( ! file_exists( $this->cache_dir ) ) {
			return true;
		}

		$files = glob( $this->cache_dir . '*.css' );

		if ( false === $files ) {
			return false;
		}

		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}

		return true;
	}

	/**
	 * Set output style
	 *
	 * @param string $style Output style (compressed, expanded, nested, compact).
	 */
	public function set_output_style( $style ) {
		$valid_styles = array(
			'compressed' => OutputStyle::COMPRESSED,
			'expanded'   => OutputStyle::EXPANDED,
			'nested'     => OutputStyle::NESTED,
			'compact'    => OutputStyle::COMPACT,
		);

		if ( isset( $valid_styles[ $style ] ) ) {
			$this->compiler->setOutputStyle( $valid_styles[ $style ] );
		}
	}

	/**
	 * Add import path
	 *
	 * @param string $path Import path.
	 */
	public function add_import_path( $path ) {
		if ( is_dir( $path ) ) {
			$current_paths = $this->compiler->getImportPaths();
			$current_paths[] = $path;
			$this->compiler->setImportPaths( $current_paths );
		}
	}
}