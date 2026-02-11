<?php
/**
 * Helper: evaluates stored snippet run conditions.
 *
 * @package advanced-analytics
 */

declare(strict_types=1);

namespace ADVAN\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\\Snippet_Condition_Evaluator' ) ) {
	/**
	 * Parses + evaluates snippet condition strings.
	 */
	class Snippet_Condition_Evaluator {

		/**
		 * Evaluate whether provided conditions pass for current request context.
		 *
		 * @param string|null $raw     Raw conditions string (JSON or comma separated pairs).
		 * @param array       $context Optional runtime context (hook, trigger, shortcode, etc).
		 */
		public static function evaluate( ?string $raw, array $context = array() ): bool {
			$raw = is_string( $raw ) ? trim( $raw ) : '';
			if ( '' === $raw ) {
				return true;
			}

			$groups = self::build_condition_groups( $raw );
			if ( empty( $groups ) ) {
				return true;
			}

			$state = self::build_runtime_state( $context );
			foreach ( $groups as $rules ) {
				$group_passed = true;
				foreach ( $rules as $condition ) {
					if ( ! self::evaluate_condition( $condition, $state ) ) {
						$group_passed = false;
						break;
					}
				}

				if ( $group_passed ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Return normalized condition structures for previews/validation.
		 *
		 * @param string $raw Raw condition string.
		 *
		 * @return array<int,array>
		 */
		public static function get_normalized_rules( string $raw ): array {
			$raw = trim( $raw );
			if ( '' === $raw ) {
				return array();
			}

			$groups = self::build_condition_groups( $raw );
			if ( empty( $groups ) ) {
				return array();
			}

			$flattened = array();
			foreach ( $groups as $rules ) {
				$flattened = array_merge( $flattened, $rules );
			}

			return $flattened;
		}

		/**
		 * Quick validation helper used during form submissions.
		 *
		 * @param string $raw Raw condition input.
		 */
		public static function is_valid( string $raw ): bool {
			$raw = trim( $raw );
			if ( '' === $raw ) {
				return true;
			}

			return ! empty( self::get_normalized_rules( $raw ) );
		}

		/**
		 * Parse raw condition payload (JSON or delimiter format) into normalized rules.
		 *
		 * @param string $raw Raw condition payload.
		 *
		 * @return array<int,array>
		 */
		private static function parse_conditions( string $raw ): array {
			$raw      = str_replace( '&&', "\n", $raw );
			$decoded = json_decode( $raw, true );
			if ( is_array( $decoded ) ) {
				$normalized = self::normalize_array_conditions( $decoded );
				if ( ! empty( $normalized ) ) {
					return $normalized;
				}
			}

			$parts      = preg_split( '/[\n\r,;]+/', $raw ) ?: array();
			$conditions = array();
			foreach ( $parts as $part ) {
				$part = trim( $part );
				if ( '' === $part || strpos( $part, '#' ) === 0 ) {
					continue;
				}

				$field    = '';
				$operator = 'eq';
				$value    = '';

				if ( preg_match( '/^([a-z0-9_\-\.]+)\s*(=|!=|>=|<=|>|<)\s*(.+)$/i', $part, $matches ) ) {
					$field    = strtolower( trim( $matches[1] ) );
					$operator = self::map_operator_symbol( $matches[2] );
					$value    = trim( $matches[3] );
				} else {
					$chunks = preg_split( '/\s+/', $part, 2 ) ?: array();
					$field  = strtolower( trim( $chunks[0] ?? '' ) );
					$value  = trim( $chunks[1] ?? '' );
				}

				if ( '' === $field || '' === $value ) {
					continue;
				}

				$values = array_filter( array_map( 'trim', explode( '|', $value ) ) );
				$rule   = self::build_condition( $field, $operator, $values );
				if ( null !== $rule ) {
					$conditions[] = $rule;
				}
			}

			return $conditions;
		}

		/**
		 * Normalize JSON-decoded payloads into the same structure used for string rules.
		 *
		 * @param array $payload JSON decoded structure.
		 *
		 * @return array<int,array>
		 */
		private static function normalize_array_conditions( array $payload ): array {
			$conditions = array();

			$sequential = array_values( $payload ) === $payload;
			if ( $sequential ) {
				foreach ( $payload as $entry ) {
					if ( is_string( $entry ) ) {
						$conditions = array_merge( $conditions, self::parse_conditions( $entry ) );
						continue;
					}

					if ( ! is_array( $entry ) ) {
						continue;
					}

					$field    = strtolower( trim( (string) ( $entry['field'] ?? '' ) ) );
					$operator = strtolower( (string) ( $entry['operator'] ?? 'eq' ) );
					$value    = $entry['value'] ?? ( $entry['values'] ?? array() );
					$values   = is_array( $value ) ? $value : array( $value );
					$rule     = self::build_condition( $field, $operator, $values );
					if ( null !== $rule ) {
						$conditions[] = $rule;
					}
				}

				return $conditions;
			}

			foreach ( $payload as $field => $value ) {
				$values = is_array( $value ) ? $value : array( $value );
				$rule   = self::build_condition( strtolower( (string) $field ), 'eq', $values );
				if ( null !== $rule ) {
					$conditions[] = $rule;
				}
			}

			return $conditions;
		}

		/**
		 * Build single normalized condition entry.
		 *
		 * @param string       $field    Field key.
		 * @param string       $operator Operator string.
		 * @param array<mixed> $values   Values to match.
		 */
		private static function build_condition( string $field, string $operator, array $values ): ?array {
			$field = strtolower( trim( $field ) );
			if ( '' === $field ) {
				return null;
			}

			$operator = self::normalize_operator( $operator );

			$clean = array();
			foreach ( $values as $value ) {
				if ( is_array( $value ) || is_object( $value ) ) {
					continue;
				}
				$value = trim( (string) $value );
				if ( '' === $value ) {
					continue;
				}
				$clean[] = $value;
			}

			if ( empty( $clean ) ) {
				return null;
			}

			return array(
				'field'    => $field,
				'operator' => $operator,
				'values'   => $clean,
			);
		}

		/**
		 * Gather runtime state used by condition evaluators.
		 *
		 * @param array $context Additional runtime context.
		 *
		 * @return array<string,mixed>
		 */
		private static function build_runtime_state( array $context ): array {
			$user  = \wp_get_current_user();
			$roles = ( $user instanceof \WP_User ) ? array_map( 'strtolower', (array) $user->roles ) : array();

			return array(
				'roles'        => $roles,
				'user'         => $user,
				'request'      => self::detect_request_flags(),
				'post_type'    => self::detect_post_type(),
				'url_path'     => self::get_request_path(),
				'trigger'      => strtolower( (string) ( $context['trigger'] ?? 'hook' ) ),
				'hook'         => (string) ( $context['hook'] ?? '' ),
				'shortcode'    => strtolower( (string) ( $context['shortcode'] ?? '' ) ),
				'atts'         => $context['atts'] ?? array(),
				'query_vars'   => self::get_query_vars_snapshot(),
				'request_vars' => self::get_request_params_snapshot(),
				'raw_context'  => $context,
			);
		}

		/**
		 * Evaluate a single normalized condition.
		 *
		 * @param array $condition Condition data.
		 * @param array $state     Runtime state.
		 */
		private static function evaluate_condition( array $condition, array $state ): bool {
			switch ( $condition['field'] ) {
				case 'role':
				case 'user_role':
					return self::evaluate_list_condition( $state['roles'], $condition['values'], $condition['operator'] );

				case 'cap':
				case 'user_capability':
					return self::evaluate_capability_condition( $condition['values'], $condition['operator'] );

				case 'request':
				case 'context':
					return self::evaluate_request_condition( $state['request'], $condition['values'], $condition['operator'] );

				case 'trigger':
					return self::evaluate_string_condition( $state['trigger'], $condition['values'], $condition['operator'], true );

				case 'hook':
					return self::evaluate_hook_condition( $state['hook'], $condition['values'], $condition['operator'] );

				case 'shortcode':
					return self::evaluate_string_condition( $state['shortcode'], $condition['values'], $condition['operator'], true );

				case 'post_type':
					return self::evaluate_string_condition( $state['post_type'], $condition['values'], $condition['operator'], true );

				case 'url_path':
				case 'path':
					return self::evaluate_path_condition( $state['url_path'], $condition['values'], $condition['operator'] );

				case 'request_param':
				case 'param':
					return self::evaluate_param_condition( $state['request_vars'], $condition );

				case 'query_var':
					return self::evaluate_param_condition( $state['query_vars'], $condition );

				default:
					if ( 0 === strpos( $condition['field'], 'request_param.' ) || 0 === strpos( $condition['field'], 'param.' ) ) {
						return self::evaluate_param_condition( $state['request_vars'], $condition );
					}

					if ( 0 === strpos( $condition['field'], 'query_var.' ) ) {
						return self::evaluate_param_condition( $state['query_vars'], $condition );
					}

					if ( 0 === strpos( $condition['field'], 'context.' ) ) {
						$key = substr( $condition['field'], 8 );
						$value = (string) ( $state['raw_context'][ $key ] ?? '' );
						return self::evaluate_string_condition( $value, $condition['values'], $condition['operator'], true );
					}

					return true;
			}
		}

		/**
		 * Evaluate list-based matches (roles, request flags, etc).
		 *
		 * @param array  $haystack Available values.
		 * @param array  $needles  Required values.
		 * @param string $operator Operator (eq/neq).
		 */
		private static function evaluate_list_condition( array $haystack, array $needles, string $operator ): bool {
			$haystack = array_map( 'strtolower', $haystack );
			$needles  = array_map( 'strtolower', $needles );
			$match    = (bool) array_intersect( $haystack, $needles );
			return ( 'neq' === $operator ) ? ! $match : $match;
		}

		/**
		 * Evaluate capability list.
		 *
		 * @param array  $values   Capability keys.
		 * @param string $operator Operator.
		 */
		private static function evaluate_capability_condition( array $values, string $operator ): bool {
			$match = false;
			foreach ( $values as $value ) {
				$cap = \sanitize_key( strtolower( $value ) );
				if ( '' === $cap ) {
					continue;
				}
				if ( \current_user_can( $cap ) ) {
					$match = true;
					break;
				}
			}

			return ( 'neq' === $operator ) ? ! $match : $match;
		}

		/**
		 * Evaluate request context flags.
		 *
		 * @param array  $flags    Request flags map.
		 * @param array  $values   Accepted flags.
		 * @param string $operator Operator.
		 */
		private static function evaluate_request_condition( array $flags, array $values, string $operator ): bool {
			$match = false;
			foreach ( $values as $value ) {
				$key = strtolower( trim( $value ) );
				if ( isset( $flags[ $key ] ) && $flags[ $key ] ) {
					$match = true;
					break;
				}
			}

			return ( 'neq' === $operator ) ? ! $match : $match;
		}

		/**
		 * Evaluate direct string comparisons.
		 *
		 * @param string $subject         Current value.
		 * @param array  $values          Allowed values.
		 * @param string $operator        Operator.
		 * @param bool   $case_insensitive Whether to compare case-insensitively.
		 */
		private static function evaluate_string_condition( string $subject, array $values, string $operator, bool $case_insensitive = false ): bool {
			$subject = $case_insensitive ? strtolower( $subject ) : $subject;
			$match   = false;
			foreach ( $values as $value ) {
				$compare = $case_insensitive ? strtolower( trim( $value ) ) : trim( $value );
				if ( '' === $compare ) {
					continue;
				}
				if ( $compare === $subject ) {
					$match = true;
					break;
				}
			}

			return ( 'neq' === $operator ) ? ! $match : $match;
		}

		/**
		 * Evaluate hook-based values supporting wildcard matching.
		 *
		 * @param string $hook     Hook name.
		 * @param array  $values   Allowed values.
		 * @param string $operator Operator.
		 */
		private static function evaluate_hook_condition( string $hook, array $values, string $operator ): bool {
			$hook  = strtolower( $hook );
			$match = false;
			foreach ( $values as $value ) {
				$pattern = strtolower( trim( $value ) );
				if ( '' === $pattern ) {
					continue;
				}
				if ( self::match_pattern( $hook, $pattern ) ) {
					$match = true;
					break;
				}
			}

			return ( 'neq' === $operator ) ? ! $match : $match;
		}

		/**
		 * Evaluate URL path (supports * wildcard).
		 *
		 * @param string $path     Request path.
		 * @param array  $values   Allowed values.
		 * @param string $operator Operator.
		 */
		private static function evaluate_path_condition( string $path, array $values, string $operator ): bool {
			$path  = strtolower( trim( $path ) );
			$match = false;
			foreach ( $values as $value ) {
				$pattern = strtolower( trim( $value ) );
				if ( '' === $pattern ) {
					continue;
				}
				if ( self::match_pattern( $path, $pattern ) ) {
					$match = true;
					break;
				}
			}

			return ( 'neq' === $operator ) ? ! $match : $match;
		}

		/**
		 * Evaluate request/query parameters (key=value pairs).
		 *
		 * @param array  $pool     Source values.
		 * @param array  $values   Conditions values (formatted key or key=value).
		 * @param string $operator Operator.
		 */
		private static function evaluate_param_condition( array $pool, array $condition ): bool {
			$values   = $condition['values'] ?? array();
			$operator = $condition['operator'] ?? 'eq';
			$field    = $condition['field'] ?? '';

			if ( false !== strpos( $field, '.' ) ) {
				list( , $target_key ) = explode( '.', $field, 2 );
				return self::evaluate_param_target( $pool, $target_key, $values, $operator );
			}

			if ( empty( $values ) ) {
				return true;
			}

			$match = false;
			foreach ( $values as $value ) {
				list( $key, $expected ) = self::split_key_value( $value );
				if ( '' === $key ) {
					continue;
				}

				if ( ! array_key_exists( $key, $pool ) ) {
					continue;
				}

				if ( '' === $expected || self::compare_scalar( $pool[ $key ], $expected ) ) {
					$match = true;
					break;
				}
			}

			return ( 'neq' === $operator ) ? ! $match : $match;
		}

		/**
		 * Evaluate specific parameter/query var target supporting numeric/date operators.
		 *
		 * @param array  $pool     Source array.
		 * @param string $target   Target key.
		 * @param array  $values   Condition values.
		 * @param string $operator Operator.
		 */
		private static function evaluate_param_target( array $pool, string $target, array $values, string $operator ): bool {
			$target = strtolower( trim( $target ) );
			if ( '' === $target ) {
				return true;
			}

			$exists = array_key_exists( $target, $pool );
			if ( ! $exists ) {
				return ( 'neq' === $operator );
			}

			$current = $pool[ $target ];
			$expected = $values[0] ?? '';

			if ( in_array( $operator, array( 'gt', 'gte', 'lt', 'lte' ), true ) ) {
				return self::compare_numeric_or_time( $current, $expected, $operator );
			}

			if ( 'neq' === $operator ) {
				foreach ( $values as $value ) {
					if ( self::compare_scalar( $current, (string) $value ) ) {
						return false;
					}
				}
				return true;
			}

			if ( 'eq' === $operator ) {
				foreach ( $values as $value ) {
					if ( self::compare_scalar( $current, (string) $value ) ) {
						return true;
					}
				}
				return false;
			}

			return self::compare_scalar( $current, $expected );
		}

		/**
		 * Map textual or symbolic operators to canonical names.
		 */
		private static function normalize_operator( string $operator ): string {
			$operator = strtolower( trim( $operator ) );
			$map      = array(
				'!='  => 'neq',
				'ne'  => 'neq',
				'neq' => 'neq',
				'='   => 'eq',
				'=='  => 'eq',
				'eq'  => 'eq',
				'>'   => 'gt',
				'>='  => 'gte',
				'<'   => 'lt',
				'<='  => 'lte',
				'gt'  => 'gt',
				'gte' => 'gte',
				'lt'  => 'lt',
				'lte' => 'lte',
			);

			return $map[ $operator ] ?? 'eq';
		}

		/**
		 * Convert comparison symbols to canonical tokens.
		 */
		private static function map_operator_symbol( string $symbol ): string {
			switch ( $symbol ) {
				case '!=':
					return 'neq';
				case '>':
					return 'gt';
				case '>=':
					return 'gte';
				case '<':
					return 'lt';
				case '<=':
					return 'lte';
				default:
					return 'eq';
			}
		}

		/**
		 * Split raw input into OR groups before parsing.
		 *
		 * @param string $raw Raw condition string.
		 *
		 * @return array<int,array<int,array>>
		 */
		private static function build_condition_groups( string $raw ): array {
			$raw = trim( $raw );
			if ( '' === $raw ) {
				return array();
			}

			if ( false === strpos( $raw, '||' ) ) {
				$rules = self::parse_conditions( $raw );
				return empty( $rules ) ? array() : array( $rules );
			}

			$segments = preg_split( '/\|\|/', $raw ) ?: array();
			$groups   = array();
			foreach ( $segments as $segment ) {
				$rules = self::parse_conditions( $segment );
				if ( ! empty( $rules ) ) {
					$groups[] = $rules;
				}
			}

			return $groups;
		}

		/**
		 * Compare numeric or datetime values using canonical operators.
		 */
		private static function compare_numeric_or_time( $left, $right, string $operator ): bool {
			$left_value  = self::convert_to_numeric_or_time( $left );
			$right_value = self::convert_to_numeric_or_time( $right );

			if ( null === $left_value || null === $right_value ) {
				return false;
			}

			switch ( $operator ) {
				case 'gt':
					return $left_value > $right_value;
				case 'gte':
					return $left_value >= $right_value;
				case 'lt':
					return $left_value < $right_value;
				case 'lte':
					return $left_value <= $right_value;
				default:
					return false;
			}
		}

		/**
		 * Convert arbitrary values to floats using numeric casting or strtotime fallback.
		 */
		private static function convert_to_numeric_or_time( $value ): ?float {
			if ( is_numeric( $value ) ) {
				return (float) $value;
			}

			$timestamp = strtotime( (string) $value );
			if ( false !== $timestamp ) {
				return (float) $timestamp;
			}

			return null;
		}

		/**
		 * Compare scalar values in a case-insensitive manner.
		 *
		 * @param mixed  $left  Current value.
		 * @param string $right Expected value.
		 */
		private static function compare_scalar( $left, string $right ): bool {
			return strtolower( (string) $left ) === strtolower( $right );
		}

		/**
		 * Split key/value pair in the form "key=value".
		 *
		 * @param string $value Raw value.
		 *
		 * @return array{0:string,1:string}
		 */
		private static function split_key_value( string $value ): array {
			if ( false === strpos( $value, '=' ) ) {
				return array( strtolower( trim( $value ) ), '' );
			}

			list( $key, $expected ) = array_map( 'trim', explode( '=', $value, 2 ) );
			return array( strtolower( $key ), $expected );
		}

		/**
		 * Detect request context flags.
		 */
		private static function detect_request_flags(): array {
			return array(
				'admin'    => \is_admin(),
				'frontend' => ! \is_admin(),
				'ajax'     => \wp_doing_ajax(),
				'cron'     => \wp_doing_cron(),
				'cli'      => defined( 'WP_CLI' ) && WP_CLI,
				'rest'     => defined( 'REST_REQUEST' ) && REST_REQUEST,
			);
		}

		/**
		 * Attempt to detect post type for current request.
		 */
		private static function detect_post_type(): string {
			global $post;
			if ( $post instanceof \WP_Post && ! empty( $post->post_type ) ) {
				return strtolower( (string) $post->post_type );
			}

			$post_type = function_exists( 'get_post_type' ) ? \get_post_type() : '';
			return strtolower( (string) $post_type );
		}

		/**
		 * Return sanitized request path.
		 */
		private static function get_request_path(): string {
			$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) \wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
			$path = \wp_parse_url( $uri, PHP_URL_PATH );
			return strtolower( (string) $path );
		}

		/**
		 * Snapshot WP_Query vars for later evaluation.
		 */
		private static function get_query_vars_snapshot(): array {
			global $wp_query;
			if ( ! isset( $wp_query ) || ! is_object( $wp_query ) ) {
				return array();
			}

			$vars = array();
			foreach ( (array) $wp_query->query_vars as $key => $value ) {
				if ( is_scalar( $value ) ) {
					$vars[ strtolower( (string) $key ) ] = (string) $value;
				}
			}

			return $vars;
		}

		/**
		 * Snapshot GET/POST values for param conditions.
		 */
		private static function get_request_params_snapshot(): array {
			$params = array();
			foreach ( array( $_GET ?? array(), $_POST ?? array() ) as $bag ) {
				foreach ( $bag as $key => $value ) {
					if ( is_array( $value ) ) {
						continue;
					}
					// Normalize and sanitize incoming request values.
					$key = strtolower( \sanitize_key( \wp_unslash( (string) $key ) ) );
					if ( '' === $key ) {
						continue;
					}
					$params[ $key ] = (string) \sanitize_text_field( \wp_unslash( (string) $value ) );
				}
			}

			return $params;
		}

		/**
		 * Wildcard matcher that supports * anywhere in the pattern.
		 *
		 * @param string $subject Target string.
		 * @param string $pattern Pattern string (with optional * wildcard).
		 */
		private static function match_pattern( string $subject, string $pattern ): bool {
			if ( '' === $pattern ) {
				return false;
			}

			if ( false === strpos( $pattern, '*' ) ) {
				return $subject === $pattern;
			}

			$regex = '/^' . str_replace( '\\*', '.*', preg_quote( $pattern, '/' ) ) . '$/i';
			return (bool) preg_match( $regex, $subject );
		}
	}
}
