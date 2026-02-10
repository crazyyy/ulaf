<?php
/**
 * Controller for snippets-related admin endpoints and bootstrap.
 *
 * @package advan
 */

declare( strict_types=1 );

namespace ADVAN\Controllers;

use ADVAN\Entities\Snippet_Entity;
use ADVAN\Helpers\Snippets_Sandbox;
use ADVAN\Helpers\Snippet_Condition_Evaluator;
use ADVAN\Helpers\Settings;

use function Safe\error_log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Controllers\Snippets_Controller' ) ) {
	/**
	 * Snippets controller.
	 *
	 * Responsible for registering admin endpoints and lightweight bootstrap tasks
	 * for the snippets subsystem. This file follows the project's existing
	 * conventions and mirrors patterns used in list/view classes.
	 *
	 * @since 4.3.0
	 */
	class Snippets_Controller {

		/**
		 * Runtime cache for enabled snippets.
		 *
		 * @var array|null
		 *
		 * @since 4.3.0
		 */
		private static $runtime_snippets = null;

		/**
		 * Initialize the controller and register related hooks.
		 *
		 * This method wires admin-post endpoints used by the snippets UI and can
		 * be extended to register REST routes, AJAX handlers or additional
		 * bootstrap logic in the future.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function init(): void {
			if ( Settings::get_option( 'snippets_module_enabled' ) ) {
				// \add_action( 'plugins_loaded', array( __CLASS__, 'register_runtime_hooks' ), 25 );
				\add_action( 'init', array( __CLASS__, 'register_runtime_shortcodes' ), 25 );

				self::register_runtime_hooks();
			}
		}

		/**
		 * Register hook callbacks for enabled snippets.
		 *
		 * @since 4.3.0
		 */
		public static function register_runtime_hooks(): void {
			foreach ( self::get_runtime_snippets() as $snippet ) {
				if ( 'php' !== ( $snippet['type'] ?? 'php' ) ) {
					continue;
				}

				$scope = (string) ( $snippet['execution_scope'] ?? Snippet_Entity::SCOPE_EVERYWHERE );
				if ( Snippet_Entity::SCOPE_MANUAL === $scope ) {
					continue;
				}

				$hook = trim( (string) ( $snippet['execution_hook'] ?? '' ) );
				if ( '' === $hook ) {
					$hook = 'init';
				}

				$priority = (int) ( $snippet['hook_priority'] ?? 10 );
				if ( $priority <= 0 ) {
					$priority = 10;
				}
				\add_action(
					$hook,
					static function () use ( $snippet ) {
						self::maybe_execute_runtime_snippet( $snippet );
					},
					$priority
				);
			}
		}

		/**
		 * Register shortcode handlers for enabled snippets.
		 *
		 * @since 4.3.0
		 */
		public static function register_runtime_shortcodes(): void {
			foreach ( self::get_runtime_snippets() as $snippet ) {
				if ( 'php' !== ( $snippet['type'] ?? 'php' ) ) {
					continue;
				}

				$tag = trim( (string) ( $snippet['shortcode_tag'] ?? '' ) );
				if ( '' === $tag || \shortcode_exists( $tag ) ) {
					continue;
				}

				\add_shortcode(
					$tag,
					static function ( $atts = array(), $content = '', string $shortcode = '' ) use ( $snippet, $tag ) {
						$context = array(
							'trigger'   => 'shortcode',
							'atts'      => $atts,
							'content'   => $content,
							'shortcode' => $shortcode ?: $tag,
						);
						if ( ! self::conditions_allow_execution( $snippet, $context ) ) {
							return '';
						}

						$result = self::execute_runtime_snippet(
							$snippet,
							$context,
							false
						);

						if ( ! empty( $result['output'] ) ) {
							return $result['output'];
						}

						return $result['result_dump'] ?? '';
					}
				);
			}
		}

		/**
		 * Fetch and cache runtime snippets list.
		 *
		 * @return array<int,array>
		 *
		 * @since 4.3.0
		 */
		private static function get_runtime_snippets(): array {
			if ( null !== self::$runtime_snippets ) {
				return self::$runtime_snippets;
			}

			self::$runtime_snippets = Snippet_Entity::get_runtime_snippets();

			return self::$runtime_snippets;
		}

		/**
		 * Execute snippet when current scope allows it.
		 *
		 * @param array $snippet Snippet row.
		 *
		 * @since 4.3.0
		 */
		private static function maybe_execute_runtime_snippet( array $snippet ): void {
			if ( ! self::should_execute_in_context( $snippet ) ) {
				return;
			}

			$context = array(
				'trigger' => 'hook',
				'hook'    => $snippet['execution_hook'] ?? '',
			);

			if ( ! self::conditions_allow_execution( $snippet, $context ) ) {
				return;
			}

			self::execute_runtime_snippet(
				$snippet,
				$context
			);
		}

		/**
		 * Decide whether snippet should execute for current request.
		 *
		 * @param array $snippet Snippet row.
		 */
		private static function should_execute_in_context( array $snippet ): bool {
			$scope = (string) ( $snippet['execution_scope'] ?? Snippet_Entity::SCOPE_EVERYWHERE );
			switch ( $scope ) {
				case Snippet_Entity::SCOPE_ADMIN:
					return \is_admin();
				case Snippet_Entity::SCOPE_FRONTEND:
					return ! \is_admin() || \wp_doing_ajax();
				case Snippet_Entity::SCOPE_MANUAL:
					return false;
				default:
					return true;
			}
		}

		/**
		 * Evaluate stored run conditions for a snippet.
		 *
		 * @param array $snippet Snippet row.
		 * @param array $context Runtime context (hook, trigger, shortcode, etc).
		 *
		 * @since 4.3.0
		 */
		private static function conditions_allow_execution( array $snippet, array $context = array() ): bool {
			$raw = isset( $snippet['run_conditions'] ) ? (string) $snippet['run_conditions'] : '';
			if ( '' === trim( $raw ) ) {
				return true;
			}

			$context = array_merge(
				array(
					'snippet_id'   => $snippet['id'] ?? 0,
					'snippet_name' => $snippet['name'] ?? '',
				),
				$context
			);

			return Snippet_Condition_Evaluator::evaluate( $raw, $context );
		}

		/**
		 * Execute snippet via sandbox and persist last run metadata.
		 *
		 * @param array $snippet      Snippet row data.
		 * @param array $context      Execution context.
		 * @param bool  $echo_output  Whether to echo STDOUT.
		 *
		 * @return array Execution payload.
		 *
		 * @since 4.3.0
		 */
		private static function execute_runtime_snippet( array $snippet, array $context = array(), bool $echo_output = true ): array {
			$code = (string) ( $snippet['code'] ?? '' );
			if ( '' === trim( $code ) ) {
				return array(
					'status'      => 'skipped',
					'message'     => '',
					'output'      => '',
					'duration'    => 0,
					'result_dump' => '',
				);
			}

			$payload = array_merge(
				array(
					'snippet'    => array(
						'id'    => (int) ( $snippet['id'] ?? 0 ),
						'name'  => $snippet['name'] ?? '',
						'scope' => $snippet['execution_scope'] ?? Snippet_Entity::SCOPE_EVERYWHERE,
					),
					'conditions' => $snippet['run_conditions'] ?? '',
				),
				$context
			);

			$result = Snippets_Sandbox::execute_runtime( $code, $payload );

			$status  = (string) ( $result['status'] ?? '' );
			$message = (string) ( $result['message'] ?? '' );
			if ( '' === $message && isset( $result['result_dump'] ) ) {
				$message = (string) $result['result_dump'];
			}

			if ( isset( $snippet['id'] ) ) {
				Snippet_Entity::store_execution_result( (int) $snippet['id'], $status, $message );
			}

			if ( $echo_output && ! empty( $result['output'] ) ) {
				echo $result['output']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			return $result;
		}
	}
}
