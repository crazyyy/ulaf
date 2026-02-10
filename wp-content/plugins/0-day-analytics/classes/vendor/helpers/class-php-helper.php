<?php
/**
 * Responsible for the User's operations
 *
 * @package    0-day
 * @subpackage helpers
 * @since 3.8.0
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace ADVAN\Helpers;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * PHP Helper class
 */
if ( ! class_exists( '\ADVAN\Helpers\PHP_Helper' ) ) {

	/**
	 * All the user related settings must go trough this class.
	 *
	 * @since 3.8.0
	 */
	class PHP_Helper {

		/**
		 * Holds the classmap array for more info check @see autoload_classmap.php from the auto generated Composer file
		 *
		 * @var array
		 *
		 * @since 3.8.0
		 */
		private static $class_map = array();

		/**
		 * Caches and returns the classmap structure of the plugin.
		 *
		 * @return array
		 *
		 * @since 3.8.0
		 */
		public static function get_class_map(): array {
			if ( empty( self::$class_map ) ) {
				$autoload_map = ADVAN_PLUGIN_ROOT . 'vendor' . \DIRECTORY_SEPARATOR . 'composer' . \DIRECTORY_SEPARATOR . 'autoload_classmap.php';
				if ( is_readable( $autoload_map ) ) {
					self::$class_map = require $autoload_map;
				} else {
					// Defensive: return empty map if autoload file missing/unreadable to avoid warnings.
					self::$class_map = array();
				}
			}

			return self::$class_map;
		}

		/**
		 * Adds a class (or classes) to the class map.
		 *
		 * @param array $class_add - Array with class or classes to add.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function add_to_class_map( array $class_add ) {
			if ( empty( self::$class_map ) ) {
				$autoload_map = ADVAN_PLUGIN_ROOT . 'vendor' . \DIRECTORY_SEPARATOR . 'composer' . \DIRECTORY_SEPARATOR . 'autoload_classmap.php';
				if ( is_readable( $autoload_map ) ) {
					self::$class_map = require $autoload_map;
				} else {
					self::$class_map = array();
				}
			}

			self::$class_map = \array_merge( self::$class_map, $class_add );
		}

		/**
		 * Returns the class by its filename. Checks if it exists and returns it as string. Returns false otherwise
		 *
		 * @param string $file - The filename of the class to check.
		 *
		 * @return string|false
		 *
		 * @since 3.8.0
		 */
		public static function get_class_by_filename( string $file ) {
			if ( in_array( $file, self::get_class_map(), true ) ) {
				$class = array_search( $file, self::get_class_map(), true );

				if ( \class_exists( $class ) ) {
					return $class;
				}
			}

			return false;
		}

		/**
		 * Extracts subclasses of the given class, optionally abstract classes could be included as well.
		 *
		 * @param string  $current_class - The calling class.
		 * @param string  $base_class - The class which subclasses should be extracted.
		 * @param boolean $exclude_abstracts - Should we exclude abstract classes.
		 *
		 * @return array
		 *
		 * @since 3.8.0
		 */
		public static function get_subclasses_of_class( string $current_class, string $base_class, bool $exclude_abstracts = true ): array {

			$matching_classes = array();
			foreach ( array_keys( self::get_class_map() ) as $class_name ) {
				if ( $current_class !== $class_name && is_subclass_of( $class_name, $base_class ) ) {
					if ( $exclude_abstracts && ( false !== strpos( $class_name, 'Abstract' ) ) ) {
						continue;
					}
					$matching_classes[ $class_name ] = $class_name;
				}
			}

			return $matching_classes;
		}

		/**
		 * Returns all the classes which are part of the given namespace
		 *
		 * @param string $searched_namespace - The namespace to search for.
		 *
		 * @return array
		 *
		 * @since 3.8.0
		 */
		public static function get_classes_by_namespace( string $searched_namespace ): array {
			if ( 0 === strpos( $searched_namespace, '\\' ) ) {
				$searched_namespace = ltrim( $searched_namespace, '\\' );
			}

			$searched_namespace = rtrim( $searched_namespace, '\\' );

			$term_upper = strtoupper( $searched_namespace );
			return array_filter(
				array_keys( self::get_class_map() ),
				function ( $class_name_maybe ) use ( $term_upper ) {
					$class_name = strtoupper( $class_name_maybe );

					/**
					 * Find class name, by finding the lass occurrence of the \
					 * if it is false  (from the strrchr) then class does not belong to any namespace currently.
					 */
					$esc_position = strrchr( $class_name, '\\' );

					if ( false !== $esc_position ) {

						$class_name_no_ns = substr( $esc_position, 1 );

					} else {
						return false;
					}

					if ( $class_name_no_ns &&
						$term_upper . '\\' . $class_name_no_ns === $class_name &&
						false === strpos( $class_name, strtoupper( 'Abstract' ) ) &&
						false === strpos( $class_name, strtoupper( 'Interface' ) ) &&
						false === strpos( $class_name, strtoupper( 'Trait' ) )
					) {
						return true;
					}
					return false;
				}
			);
		}

		/**
		 * Search for classes by given term
		 *
		 * @param string $term - The term to search for.
		 *
		 * @return array
		 *
		 * @since 3.8.0
		 */
		public static function get_classes_with_term( string $term ): array {
			$term_upper = strtoupper( $term );
			return array_filter(
				self::get_class_map(),
				function ( $class_file_path ) use ( $term_upper ) {
					$class_name = strtoupper( $class_file_path );
					if (
					false !== strpos( $class_name, $term_upper ) &&
					false === strpos( $class_name, strtoupper( 'Abstract' ) ) &&
					false === strpos( $class_name, strtoupper( 'Interface' ) )
					) {
						return true;
					}
					return false;
				}
			);
		}
	}
}
