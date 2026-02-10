<?php
/**
 * Helper class for rendering hook parameters.
 *
 * Formats and displays hook parameters with type-specific formatting.
 *
 * @package advanced-analytics
 * @since   4.5.0
 */

declare(strict_types=1);

namespace ADVAN\Helpers;

use ADVAN\Entities\Hooks_Management_Entity;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Helpers\Hook_Parameter_Renderer' ) ) {
	/**
	 * Responsible for rendering hook parameters with proper formatting.
	 *
	 * @since 4.5.0
	 */
	class Hook_Parameter_Renderer {

		/**
		 * Render parameters for display in hooks capture list.
		 *
		 * @param string $hook_name   The hook name.
		 * @param string $parameters  JSON-encoded parameters from capture.
		 *
		 * @return string HTML output for parameters column.
		 *
		 * @since 4.5.0
		 */
		public static function render_parameters( string $hook_name, string $parameters ): string {
			// Get hook definition from hooks_management table.
			$hook_parameters = Hooks_Management_Entity::get_hook_parameters( $hook_name );

			// Decode captured parameters.
			$captured_params = ! empty( $parameters ) ? json_decode( $parameters, true ) : array();
			if ( ! is_array( $captured_params ) ) {
				$captured_params = array();
			}

			// Get parameter definitions.
			$param_defs = array();
			if ( ! empty( $hook_parameters ) ) {
				$param_defs = json_decode( $hook_parameters, true );
				if ( ! is_array( $param_defs ) ) {
					$param_defs = array();
				}
			}

			// If no parameters defined and no captured params, return empty.
			if ( empty( $param_defs ) && empty( $captured_params ) ) {
				return '<span style="color: #999;">-</span>';
			}

			// If no definitions but we have captured params, show them as-is.
			if ( empty( $param_defs ) && ! empty( $captured_params ) ) {
				return self::render_raw_parameters( $captured_params );
			}

			// Render with definitions.
			$output = '<div style="line-height: 1.8;">';
			foreach ( $param_defs as $index => $param_def ) {
				$param_name      = isset( $param_def['name'] ) ? $param_def['name'] : '';
				$param_type      = isset( $param_def['type'] ) ? $param_def['type'] : 'string';
				$extraction_code = isset( $param_def['extraction_code'] ) ? $param_def['extraction_code'] : '';

				if ( empty( $param_name ) ) {
					continue;
				}

				// Get captured value for this parameter (by index).
				$value = isset( $captured_params[ $index ] ) ? $captured_params[ $index ] : null;

				if ( is_null( $value ) ) {
					$output .= '<div><strong>' . \esc_html( $param_name ) . ':</strong> <span style="color: #999;">N/A</span></div>';
					continue;
				}

				$output .= '<div><strong>' . \esc_html( $param_name ) . ':</strong> ';
				$output .= self::format_value( $value, $param_type, $extraction_code );
				$output .= '</div>';
			}
			$output .= '</div>';

			return $output;
		}

		/**
		 * Format a parameter value based on its type.
		 *
		 * @param mixed  $value           The parameter value.
		 * @param string $type            The parameter type.
		 * @param string $extraction_code Custom extraction code.
		 *
		 * @return string Formatted HTML output.
		 *
		 * @since 4.5.0
		 */
		private static function format_value( $value, string $type, string $extraction_code ): string {
			// Execute custom extraction code if provided.
			if ( ! empty( $extraction_code ) ) {
				try {
					$formatted = self::execute_extraction_code( $value, $extraction_code );
					if ( false !== $formatted ) {
						return $formatted;
					}
				} catch ( \Throwable $e ) {
					return '<code style="color: #dc3232;">[Error: ' . \esc_html( $e->getMessage() ) . ']</code>';
				}
			}

			/**
			 * Sometimes we have type specified, but the actual value is not of this type.
			 * That comes from the fact that anyone is free to pass parameters of any type to hooks.
			 * Usually capturing logic guesses that right, so here we need to try to use the actual value type.
			 * Here we will cover situation only when type is 'string', but value is something else (JSON string).
			 */
			if ( is_string( $value ) ) {
				$decoded = json_decode( $value, true );

				// Handle valid JSON (objects or arrays).
				if ( json_last_error() === JSON_ERROR_NONE ) {
					$value = $decoded;
				} elseif ( preg_match( '/^[aOs]:[0-9]+:/', $value ) ) { // Try unserialize if not valid JSON but looks like serialized PHP.
					$unserialized = @unserialize( $value, array( 'allowed_classes' => false ) );
					if ( false !== $unserialized || 'b:0;' === $value ) {
						$value = $unserialized;
					}
				}
			}

			if ( \is_array( $value ) && ! \in_array( $type, array( 'array', 'object' ), true ) ) {
				$type = 'array';
			}
			// Override type if actual value type differs significantly.

			// Format by type.
			if ( '' === trim( $type ) ) {
				if ( is_array( $value ) ) {
					$type = 'array';
				} elseif ( is_object( $value ) ) {
					$type = 'object';
				} elseif ( is_bool( $value ) ) {
					$type = 'bool';
				} elseif ( is_null( $value ) ) {
					$type = 'null';
				} elseif ( is_numeric( $value ) ) {
					$type = 'int';
				}
			}

			// Format based on type.
			switch ( $type ) {
				case 'user_id':
					return self::format_user_id( $value );

				case 'post_id':
					return self::format_post_id( $value );

				case 'term_id':
					return self::format_term_id( $value );

				case 'comment_id':
					return self::format_comment_id( $value );

				case 'blog_id':
					return self::format_blog_id( $value );

				case 'wp_user':
					return self::format_wp_user( $value );

				case 'wp_post':
					return self::format_wp_post( $value );

				case 'wp_error':
					return self::format_wp_error( $value );

				case 'array':
					return self::format_array( $value );

				case 'object':
					return self::format_object( $value );

				case 'bool':
					return self::format_bool( $value );

				case 'int':
				case 'float':
				case 'string':
				default:
					return self::format_simple( $value );
			}
		}

		/**
		 * Execute custom extraction code.
		 *
		 * @param mixed  $value The parameter value.
		 * @param string $code  The extraction code.
		 *
		 * @return string|false Formatted output or false on failure.
		 *
		 * @throws \Throwable If code execution fails.
		 * @throws \Exception If unsafe code is detected.
		 *
		 * @since 4.5.0
		 */
		private static function execute_extraction_code( $value, string $code ) {
			try {
				// Validate the code for safety.
				if ( ! self::is_safe_code( $code ) ) {
					throw new \Exception( 'Unsafe extraction code detected.' );
				}

				// Create a safe scope for the code execution.
				$eval_func = function ( $value ) use ( $code ) {
					// phpcs:ignore Squiz.PHP.Eval.Discouraged
					return eval( $code );
				};

				$result = $eval_func( $value );

				if ( is_string( $result ) || is_numeric( $result ) ) {
					return \esc_html( (string) $result );
				} elseif ( is_bool( $result ) ) {
					return $result ? '<span style="color: green;">✓</span>' : '<span style="color: red;">✗</span>';
				} elseif ( is_array( $result ) || is_object( $result ) ) {
					return '<code>' . \esc_html( \wp_json_encode( $result ) ) . '</code>';
				}

				return false;
			} catch ( \Throwable $e ) {
				throw $e;
			}
		}

		/**
		 * Check if the extraction code is safe to execute.
		 *
		 * @param string $code The code to check.
		 *
		 * @return bool True if safe, false otherwise.
		 *
		 * @since 4.5.0
		 */
		private static function is_safe_code( string $code ): bool {
			// Prepend PHP tag for proper tokenization.
			$tokens = token_get_all( '<?php ' . $code );

			// Forbidden tokens that could lead to security issues.
			$forbidden_tokens = array(
				\T_EVAL,
				\T_INCLUDE,
				\T_INCLUDE_ONCE,
				\T_REQUIRE,
				\T_REQUIRE_ONCE,
				\T_EXIT,
				\T_GLOBAL,
				\T_STATIC,
				\T_FUNCTION,
				\T_CLASS,
				\T_INTERFACE,
				\T_TRAIT,
				\T_NAMESPACE,
				\T_USE,
				\T_NEW,
				\T_CLONE,
				\T_INSTANCEOF,
				\T_YIELD,
				\T_YIELD_FROM,
				\T_THROW,
				\T_TRY,
				\T_CATCH,
				\T_FINALLY,
				\T_VARIABLE, // Allow only $value, but we'll check specifically.
			);

			foreach ( $tokens as $token ) {
				if ( is_array( $token ) ) {
					$token_type  = $token[0];
					$token_value = $token[1];

					// Check for forbidden tokens.
					if ( in_array( $token_type, $forbidden_tokens, true ) ) {
						// Allow only $value as variable.
						if ( T_VARIABLE === $token_type && '$value' === $token_value ) {
							continue;
						}
						return false;
					}
				}
			}

			return true;
		}

		/**
		 * Format User ID.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_user_id( $value ): string {
			$user_id = \absint( $value );
			if ( $user_id > 0 ) {
				$user = \get_user_by( 'ID', $user_id );
				if ( $user ) {
					return sprintf(
						'<a href="%s" target="_blank">%s</a> <code>(#%d)</code>',
						\esc_url( \get_edit_user_link( $user_id ) ),
						\esc_html( $user->user_login ),
						$user_id
					);
				}
			}
			return '<code>' . \esc_html( $value ) . '</code>';
		}

		/**
		 * Format Post ID.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_post_id( $value ): string {
			$post_id = \absint( $value );
			if ( $post_id > 0 ) {
				$post = \get_post( $post_id );
				if ( $post ) {
					$title         = $post->post_title ?: $post->post_name ?: '(no title)';
					$display_title = self::truncate_text( $title, 20 );
					return sprintf(
						'<a href="%s" target="_blank" title="%s">%s</a> <code>(#%d)</code>',
						\esc_url( \get_edit_post_link( $post_id ) ),
						\esc_attr( $title ),
						\esc_html( $display_title ),
						$post_id
					);
				}
			}
			return '<code>' . \esc_html( $value ) . '</code>';
		}

		/**
		 * Format Term ID.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_term_id( $value ): string {
			$term_id = \absint( $value );
			if ( $term_id > 0 ) {
				$term = \get_term( $term_id );
				if ( $term && ! \is_wp_error( $term ) ) {
					return sprintf(
						'<a href="%s" target="_blank">%s</a> <code>(#%d)</code>',
						\esc_url( \get_edit_term_link( $term_id, $term->taxonomy ) ),
						\esc_html( $term->name ),
						$term_id
					);
				}
			}
			return '<code>' . \esc_html( $value ) . '</code>';
		}

		/**
		 * Format Comment ID.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_comment_id( $value ): string {
			$comment_id = \absint( $value );
			if ( $comment_id > 0 ) {
				$comment = \get_comment( $comment_id );
				if ( $comment ) {
					return sprintf(
						'<a href="%s" target="_blank">Comment #%d</a>',
						\esc_url( \get_edit_comment_link( $comment_id ) ),
						$comment_id
					);
				}
			}
			return '<code>' . \esc_html( $value ) . '</code>';
		}

		/**
		 * Format Blog ID.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_blog_id( $value ): string {
			if ( ! \is_multisite() ) {
				return '<code>' . \esc_html( $value ) . '</code>';
			}

			$blog_id = \absint( $value );
			if ( $blog_id > 0 ) {
				$site = \get_site( $blog_id );
				if ( $site ) {
					return sprintf(
						'<a href="%s" target="_blank">%s</a> <code>(#%d)</code>',
						\esc_url( \get_admin_url( $blog_id ) ),
						\esc_html( $site->blogname ),
						$blog_id
					);
				}
			}
			return '<code>' . \esc_html( $value ) . '</code>';
		}

		/**
		 * Format WP_User object.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_wp_user( $value ): string {
			if ( is_object( $value ) && isset( $value->ID ) ) {
				return self::format_user_id( $value->ID );
			}
			return self::format_object( $value );
		}

		/**
		 * Format WP_Post object.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_wp_post( $value ): string {
			if ( is_object( $value ) && isset( $value->ID ) ) {
				return self::format_post_id( $value->ID );
			}
			return self::format_object( $value );
		}

		/**
		 * Format WP_Error object.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_wp_error( $value ): string {
			if ( \is_wp_error( $value ) ) {
				return sprintf(
					'<span style="color: #dc3232;"><strong>Error:</strong> %s</span>',
					\esc_html( $value->get_error_message() )
				);
			}
			return self::format_object( $value );
		}

		/**
		 * Format array.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_array( $value ): string {
			if ( is_array( $value ) ) {
				if ( empty( $value ) ) {
					return '<code>[]</code>';
				}

				// Check if this array represents an object (has __class__ marker).
				if ( isset( $value['__class__'] ) && is_string( $value['__class__'] ) ) {
					$class_name = $value['__class__'];
					// Remove __class__ from display data.
					$display_data = $value;
					unset( $display_data['__class__'] );

					$json  = \wp_json_encode( $display_data, JSON_PRETTY_PRINT );
					$count = count( $display_data );
					return '<details><summary>' . \esc_html( $class_name ) . ' (' . \esc_html( (string) $count ) . ' properties)</summary><pre style="max-height: 200px; max-width: 300px; overflow: auto; padding: 10px; margin-top: 5px;">' . \esc_html( $json ) . '</pre></details>';
				}

				// Regular array.
				$json = \wp_json_encode( $value, JSON_PRETTY_PRINT );
				return '<details><summary>Array (' . count( $value ) . ' items)</summary><pre style="max-height: 200px; max-width: 300px; overflow: auto; padding: 10px; margin-top: 5px;">' . \esc_html( $json ) . '</pre></details>';
			}
			return '<code>' . \esc_html( print_r( $value, true ) ) . '</code>';
		}

		/**
		 * Format object.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_object( $value ): string {
			// If it's an array with __class__ marker, it represents an object stored as array.
			if ( is_array( $value ) && isset( $value['__class__'] ) && is_string( $value['__class__'] ) ) {
				return self::format_array( $value );
			}

			if ( is_object( $value ) ) {
				$class_name = get_class( $value );
				$json       = \wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR );

				// Try to get object properties count.
				$props = get_object_vars( $value );
				$count = count( $props );

				return '<details><summary>' . \esc_html( $class_name ) . ' (' . \esc_html( (string) $count ) . ' properties)</summary><pre style="max-height: 200px; max-width: 300px; overflow: auto; padding: 10px; margin-top: 5px;">' . \esc_html( $json ) . '</pre></details>';
			}
			return '<code>' . \esc_html( print_r( $value, true ) ) . '</code>';
		}

		/**
		 * Format boolean.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_bool( $value ): string {
			$bool_val = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
			if ( is_null( $bool_val ) ) {
				return '<code>' . \esc_html( (string) $value ) . '</code>';
			}
			return $bool_val ? '<span style="color: green;">✓ true</span>' : '<span style="color: #999;">✗ false</span>';
		}

		/**
		 * Format simple types (string, int, float).
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function format_simple( $value ): string {
			if ( is_scalar( $value ) ) {
				$str = (string) $value;
				if ( strlen( $str ) > 100 ) {
					return '<details><summary>' . \esc_html( substr( $str, 0, 100 ) ) . '...</summary><div style="max-height: 200px; max-width: 300px; overflow: auto; padding: 10px; margin-top: 5px;">' . \esc_html( $str ) . '</div></details>';
				}
				return '<code>' . \esc_html( $str ) . '</code>';
			}
			return '<code>' . \esc_html( print_r( (array) $value, true ) ) . '</code>';
		}

		/**
		 * Safely truncate text to a maximum length with ellipsis.
		 *
		 * @param string $text  Text to truncate.
		 * @param int    $limit Maximum length before truncation.
		 *
		 * @return string
		 */
		private static function truncate_text( string $text, int $limit = 20 ): string {
			$limit = max( 1, $limit );

			if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
				if ( mb_strlen( $text ) <= $limit ) {
					return $text;
				}

				return rtrim( mb_substr( $text, 0, $limit ), " \t\n\r\0\x0B" ) . '...';
			}

			if ( strlen( $text ) <= $limit ) {
				return $text;
			}

			return rtrim( substr( $text, 0, $limit ), " \t\n\r\0\x0B" ) . '...';
		}

		/**
		 * Render raw parameters without type definitions.
		 *
		 * @param array $parameters The parameters array.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function render_raw_parameters( array $parameters ): string {
			if ( empty( $parameters ) ) {
				return '<span style="color: #999;">-</span>';
			}

			$output = '<div style="line-height: 1.8;">';
			foreach ( $parameters as $index => $value ) {
				$output .= '<div><strong>Param ' . ( $index + 1 ) . ':</strong> ';

				// Try to auto-detect type and format accordingly.
				$output .= self::auto_format_value( $value );

				$output .= '</div>';
			}
			$output .= '</div>';

			return $output;
		}

		/**
		 * Auto-detect value type and format accordingly.
		 *
		 * @param mixed $value The value.
		 *
		 * @return string Formatted output.
		 *
		 * @since 4.5.0
		 */
		private static function auto_format_value( $value ): string {
			// Check if it's a WP_Error.
			if ( \is_wp_error( $value ) ) {
				return self::format_wp_error( $value );
			}

			// Check if it's a WP_User.
			if ( is_object( $value ) && $value instanceof \WP_User ) {
				return self::format_wp_user( $value );
			}

			// Check if it's a WP_Post.
			if ( is_object( $value ) && $value instanceof \WP_Post ) {
				return self::format_wp_post( $value );
			}

			// Check if it's a numeric ID that might be a user or post.
			if ( is_numeric( $value ) && $value > 0 ) {
				// Try to see if it's a valid user ID.
				$user = \get_user_by( 'ID', (int) $value );
				if ( $user ) {
					return self::format_user_id( $value ) . ' <small>(auto-detected)</small>';
				}

				// Try to see if it's a valid post ID.
				$post = \get_post( (int) $value );
				if ( $post ) {
					return self::format_post_id( $value ) . ' <small>(auto-detected)</small>';
				}
			}

			// Boolean.
			if ( is_bool( $value ) ) {
				return self::format_bool( $value );
			}

			// Array.
			if ( is_array( $value ) ) {
				return self::format_array( $value );
			}

			// Object.
			if ( is_object( $value ) ) {
				return self::format_object( $value );
			}

			// Simple scalar.
			return self::format_simple( $value );
		}
	}
}
