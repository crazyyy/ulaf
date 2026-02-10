<?php
/**
 * DiveWP Abilities API Integration
 *
 * This class handles the registration and management of DiveWP abilities
 * for the WordPress 6.9+ Abilities API, enabling AI agent integration.
 *
 * @package     DiveWP
 * @subpackage  Abilities
 * @author      Oleg Petrov
 * @version     2.1.0
 * @license     GPL-2.0-or-later
 * @since       2.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Direct access not permitted.', 'divewp-boost-site-performance' ) );
}

/**
 * Class DiveWP_Abilities
 *
 * Manages the registration of DiveWP capabilities with the WordPress Abilities API.
 * This allows AI agents to discover and interact with DiveWP features.
 *
 * @since 2.1.0
 */
class DiveWP_Abilities {

	/**
	 * Server Insights instance
	 *
	 * @since 2.1.0
	 * @var DiveWP_Server_Insights_New|null
	 */
	private $server_insights;

	/**
	 * Guard to avoid duplicate registration.
	 *
	 * @var bool
	 */
	private static $registered = false;

	/**
	 * Constructor
	 *
	 * @since 2.1.0
	 *
	 * @param DiveWP_Server_Insights_New|null $server_insights Server Insights instance.
	 */
	public function __construct( $server_insights = null ) {
		$this->server_insights = $server_insights;

		// Check if Abilities API is available (WordPress 6.9+ / plugin).
		if ( ! class_exists( 'WP_Ability' ) && ! function_exists( 'wp_register_ability' ) && ! function_exists( 'register_ability' ) ) {
			return;
		}

		// Register category (support both prefixes). Must run only on the proper hooks.
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		add_action( 'abilities_api_categories_init', array( $this, 'register_category' ) );

		// Register abilities (support both prefixes). Must run only on the proper hooks.
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
		add_action( 'abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Set the Server Insights instance
	 *
	 * Allows late binding of the Server Insights instance if not provided in constructor.
	 *
	 * @since 2.1.0
	 *
	 * @param DiveWP_Server_Insights_New $server_insights Server Insights instance.
	 * @return void
	 */
	public function set_server_insights( $server_insights ) {
		$this->server_insights = $server_insights;
	}

	/**
	 * Register the DiveWP category for abilities
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function register_category() {
		$category_register_fn = null;
		if ( function_exists( 'wp_register_ability_category' ) ) {
			$category_register_fn = 'wp_register_ability_category';
		} elseif ( function_exists( 'register_ability_category' ) ) {
			$category_register_fn = 'register_ability_category';
		}

		if ( null === $category_register_fn ) {
			return;
		}

		call_user_func(
			$category_register_fn,
			'divewp',
			array(
				'label'       => __( 'DiveWP', 'divewp-boost-site-performance' ),
				'description' => __( 'DiveWP site performance and diagnostics abilities.', 'divewp-boost-site-performance' ),
			)
		);
	}

	/**
	 * Register all DiveWP abilities with the Abilities API
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function register_abilities() {
		// Prevent duplicate registration.
		if ( self::$registered ) {
			return;
		}

		// Check if the Abilities API is available.
		$ability_register_fn = null;
		if ( function_exists( 'wp_register_ability' ) ) {
			$ability_register_fn = 'wp_register_ability';
		} elseif ( function_exists( 'register_ability' ) ) {
			$ability_register_fn = 'register_ability';
		}

		if ( null === $ability_register_fn ) {
			return;
		}

		$this->register_server_insights_ability( $ability_register_fn );
		$this->register_performance_checks_ability( $ability_register_fn );
		$this->register_db_insights_ability( $ability_register_fn );
		$this->register_security_insights_ability( $ability_register_fn );
		$this->register_theme_builder_ability( $ability_register_fn );
		$this->register_woocommerce_best_practices_ability( $ability_register_fn );
		$this->register_seo_optimization_ability( $ability_register_fn );
		$this->register_email_communications_ability( $ability_register_fn );
		$this->register_hosting_benchmark_latest_ability( $ability_register_fn );
		$this->register_cron_insights_ability( $ability_register_fn );
		self::$registered = true;
	}

	/**
	 * Register the Server Insights ability
	 *
	 * Exposes server configuration and health check data to AI agents.
	 * Uses the WP 6.9 Abilities API format per official documentation.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	private function register_server_insights_ability( $ability_register_fn ) {
		$result = call_user_func(
			$ability_register_fn,
			'divewp/server-insights',
			array(
				'label'               => __( 'Get Server Insights', 'divewp-boost-site-performance' ),
				'description'         => __(
					'Retrieves comprehensive server health information including PHP version, database status, memory limits, execution time limits, upload sizes, and PHP extension availability. Useful for diagnosing performance issues, compatibility problems, and server configuration optimization.',
					'divewp-boost-site-performance'
				),
				'category'            => 'divewp',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'include_recommendations' => array(
							'type'        => 'boolean',
							'description' => __( 'Include actionable recommendations for issues found.', 'divewp-boost-site-performance' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'    => array(
							'type'        => 'string',
							'description' => __( 'Overall server health status.', 'divewp-boost-site-performance' ),
						),
						'timestamp' => array(
							'type'        => 'string',
							'description' => __( 'ISO 8601 timestamp of when checks were performed.', 'divewp-boost-site-performance' ),
						),
						'checks'    => array(
							'type'        => 'object',
							'description' => __( 'Individual check results.', 'divewp-boost-site-performance' ),
						),
						'summary'   => array(
							'type'        => 'object',
							'description' => __( 'Summary statistics of all checks.', 'divewp-boost-site-performance' ),
						),
					),
				),
				'execute_callback'    => array( $this, 'handle_server_insights_request' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'show_in_rest' => true,
					// Expose via MCP: required for MCP Adapter auto-discovery.
					'mcp'          => array(
						'public'      => true,
						'type'        => 'tool',
						'title'       => __( 'Server Insights', 'divewp-boost-site-performance' ),
						'description' => __( 'Expose server insights as an MCP tool for AI agents.', 'divewp-boost-site-performance' ),
					),
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'DiveWP Abilities: failed to register ability divewp/server-insights - %s', $result->get_error_message() ) );
		}
	}

	/**
	 * Register Performance Checks ability
	 */
	private function register_performance_checks_ability( $ability_register_fn ) {
		$result = call_user_func(
			$ability_register_fn,
			'divewp/performance-checks',
			array(
				'label'               => __( 'Performance Checks', 'divewp-boost-site-performance' ),
				'description'         => __( 'Returns performance optimization check results (caching, minification, deferred JS, images, lazy loading, object cache).', 'divewp-boost-site-performance' ),
				'category'            => 'divewp',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'  => array( 'type' => 'string' ),
						'checks'  => array( 'type' => 'object' ),
						'summary' => array( 'type' => 'object' ),
					),
				),
				'execute_callback'    => array( $this, 'handle_performance_checks_request' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'mcp' => array(
						'public'      => true,
						'type'        => 'tool',
						'title'       => __( 'Performance Checks', 'divewp-boost-site-performance' ),
						'description' => __( 'Expose performance checks as an MCP tool.', 'divewp-boost-site-performance' ),
					),
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'DiveWP Abilities: failed to register ability divewp/performance-checks - %s', $result->get_error_message() ) );
		}
	}

	/**
	 * Register DB Insights ability
	 */
	private function register_db_insights_ability( $ability_register_fn ) {
		$result = call_user_func(
			$ability_register_fn,
			'divewp/db-insights',
			array(
				'label'               => __( 'Database Insights', 'divewp-boost-site-performance' ),
				'description'         => __( 'Returns database health metrics (size, overhead, revisions, spam, expired transients, non-core tables).', 'divewp-boost-site-performance' ),
				'category'            => 'divewp',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'  => array( 'type' => 'string' ),
						'checks'  => array( 'type' => 'object' ),
						'summary' => array( 'type' => 'object' ),
					),
				),
				'execute_callback'    => array( $this, 'handle_db_insights_request' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'mcp' => array(
						'public'      => true,
						'type'        => 'tool',
						'title'       => __( 'Database Insights', 'divewp-boost-site-performance' ),
						'description' => __( 'Expose database insights as an MCP tool.', 'divewp-boost-site-performance' ),
					),
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'DiveWP Abilities: failed to register ability divewp/db-insights - %s', $result->get_error_message() ) );
		}
	}

	/**
	 * Register Security Insights ability
	 */
	private function register_security_insights_ability( $ability_register_fn ) {
		$result = call_user_func(
			$ability_register_fn,
			'divewp/security-insights',
			array(
				'label'               => __( 'Security Insights', 'divewp-boost-site-performance' ),
				'description'         => __( 'Returns security checks (SSL, file permissions, admin user, DB prefix, file editor, debug mode, security plugins).', 'divewp-boost-site-performance' ),
				'category'            => 'divewp',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'  => array( 'type' => 'string' ),
						'checks'  => array( 'type' => 'object' ),
						'summary' => array( 'type' => 'object' ),
					),
				),
				'execute_callback'    => array( $this, 'handle_security_insights_request' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'mcp' => array(
						'public'      => true,
						'type'        => 'tool',
						'title'       => __( 'Security Insights', 'divewp-boost-site-performance' ),
						'description' => __( 'Expose security insights as an MCP tool.', 'divewp-boost-site-performance' ),
					),
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'DiveWP Abilities: failed to register ability divewp/security-insights - %s', $result->get_error_message() ) );
		}
	}

	/**
	 * Register Theme & Builder Insights ability
	 */
	private function register_theme_builder_ability( $ability_register_fn ) {
		$result = call_user_func(
			$ability_register_fn,
			'divewp/theme-builder-insights',
			array(
				'label'               => __( 'Theme & Builder Insights', 'divewp-boost-site-performance' ),
				'description'         => __( 'Returns theme/page builder checks (updates, child theme, translations, builders, translation plugins, inactive themes).', 'divewp-boost-site-performance' ),
				'category'            => 'divewp',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'  => array( 'type' => 'string' ),
						'checks'  => array( 'type' => 'object' ),
						'summary' => array( 'type' => 'object' ),
					),
				),
				'execute_callback'    => array( $this, 'handle_theme_builder_request' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'mcp' => array(
						'public'      => true,
						'type'        => 'tool',
						'title'       => __( 'Theme & Builder Insights', 'divewp-boost-site-performance' ),
						'description' => __( 'Expose theme and page builder insights as an MCP tool.', 'divewp-boost-site-performance' ),
					),
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'DiveWP Abilities: failed to register ability divewp/theme-builder-insights - %s', $result->get_error_message() ) );
		}
	}

	/**
	 * Register WooCommerce Best Practices ability
	 */
	private function register_woocommerce_best_practices_ability( $ability_register_fn ) {
		$result = call_user_func(
			$ability_register_fn,
			'divewp/woocommerce-best-practices',
			array(
				'label'               => __( 'WooCommerce Best Practices', 'divewp-boost-site-performance' ),
				'description'         => __( 'Returns WooCommerce best-practice checks (cart fragments, session handler, order cleanup, product revisions).', 'divewp-boost-site-performance' ),
				'category'            => 'divewp',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'  => array( 'type' => 'string' ),
						'checks'  => array( 'type' => 'object' ),
						'summary' => array( 'type' => 'object' ),
					),
				),
				'execute_callback'    => array( $this, 'handle_woocommerce_best_practices_request' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'mcp' => array(
						'public'      => true,
						'type'        => 'tool',
						'title'       => __( 'WooCommerce Best Practices', 'divewp-boost-site-performance' ),
						'description' => __( 'Expose WooCommerce best practices as an MCP tool.', 'divewp-boost-site-performance' ),
					),
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'DiveWP Abilities: failed to register ability divewp/woocommerce-best-practices - %s', $result->get_error_message() ) );
		}
	}

	/**
	 * Register SEO Optimization ability
	 */
	private function register_seo_optimization_ability( $ability_register_fn ) {
		$result = call_user_func(
			$ability_register_fn,
			'divewp/seo-optimization',
			array(
				'label'               => __( 'SEO Optimization', 'divewp-boost-site-performance' ),
				'description'         => __( 'Returns SEO checks (plugins, meta description, sitemap, robots.txt, permalinks, search visibility).', 'divewp-boost-site-performance' ),
				'category'            => 'divewp',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'  => array( 'type' => 'string' ),
						'checks'  => array( 'type' => 'object' ),
						'summary' => array( 'type' => 'object' ),
					),
				),
				'execute_callback'    => array( $this, 'handle_seo_optimization_request' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'mcp' => array(
						'public'      => true,
						'type'        => 'tool',
						'title'       => __( 'SEO Optimization', 'divewp-boost-site-performance' ),
						'description' => __( 'Expose SEO optimization checks as an MCP tool.', 'divewp-boost-site-performance' ),
					),
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'DiveWP Abilities: failed to register ability divewp/seo-optimization - %s', $result->get_error_message() ) );
		}
	}

	/**
	 * Register Email Communications ability
	 */
	private function register_email_communications_ability( $ability_register_fn ) {
		$result = call_user_func(
			$ability_register_fn,
			'divewp/email-communications',
			array(
				'label'               => __( 'Email Communications', 'divewp-boost-site-performance' ),
				'description'         => __( 'Returns email configuration checks (SMTP plugin, SPF/DKIM plugin, wp_mail availability).', 'divewp-boost-site-performance' ),
				'category'            => 'divewp',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'  => array( 'type' => 'string' ),
						'checks'  => array( 'type' => 'object' ),
						'summary' => array( 'type' => 'object' ),
					),
				),
				'execute_callback'    => array( $this, 'handle_email_communications_request' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'mcp' => array(
						'public'      => true,
						'type'        => 'tool',
						'title'       => __( 'Email Communications', 'divewp-boost-site-performance' ),
						'description' => __( 'Expose email communications checks as an MCP tool.', 'divewp-boost-site-performance' ),
					),
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'DiveWP Abilities: failed to register ability divewp/email-communications - %s', $result->get_error_message() ) );
		}
	}

	/**
	 * Register Hosting Benchmark (latest result) ability
	 */
	private function register_hosting_benchmark_latest_ability( $ability_register_fn ) {
		$result = call_user_func(
			$ability_register_fn,
			'divewp/hosting-benchmark-latest',
			array(
				'label'               => __( 'Hosting Benchmark (Latest Result)', 'divewp-boost-site-performance' ),
				'description'         => __( 'Returns the most recent saved hosting benchmark result (no table UI).', 'divewp-boost-site-performance' ),
				'category'            => 'divewp',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'             => array( 'type' => 'string' ),
						'test_date'          => array( 'type' => 'string' ),
						'session_id'         => array( 'type' => 'string' ),
						'overall_score'      => array( 'type' => 'number' ),
						'performance_score'  => array( 'type' => 'number' ),
						'database_score'     => array( 'type' => 'number' ),
						'resources_score'    => array( 'type' => 'number' ),
						'concurrency_score'  => array( 'type' => 'number' ),
						'full_results'       => array( 'type' => 'object' ),
					),
				),
				'execute_callback'    => array( $this, 'handle_hosting_benchmark_latest_request' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'mcp' => array(
						'public'      => true,
						'type'        => 'tool',
						'title'       => __( 'Hosting Benchmark (Latest)', 'divewp-boost-site-performance' ),
						'description' => __( 'Expose the latest saved hosting benchmark via MCP.', 'divewp-boost-site-performance' ),
					),
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'DiveWP Abilities: failed to register ability divewp/hosting-benchmark-latest - %s', $result->get_error_message() ) );
		}
	}

	/**
	 * Register Cron Insights ability
	 */
	private function register_cron_insights_ability( $ability_register_fn ) {
		$result = call_user_func(
			$ability_register_fn,
			'divewp/cron-insights',
			array(
				'label'               => __( 'Cron Insights', 'divewp-boost-site-performance' ),
				'description'         => __( 'Returns WP-Cron status, upcoming and overdue tasks, and Action Scheduler queue stats.', 'divewp-boost-site-performance' ),
				'category'            => 'divewp',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'limit' => array(
							'type'        => 'integer',
							'description' => __( 'How many upcoming/overdue items to return (1-20).', 'divewp-boost-site-performance' ),
							'minimum'     => 1,
							'maximum'     => 20,
							'default'     => 5,
						),
						'include_all' => array(
							'type'        => 'boolean',
							'description' => __( 'Include full WP-Cron list and pending Action Scheduler list.', 'divewp-boost-site-performance' ),
							'default'     => true,
						),
						'action_scheduler_limit' => array(
							'type'        => 'integer',
							'description' => __( 'Max Action Scheduler pending items to return (1-100).', 'divewp-boost-site-performance' ),
							'minimum'     => 1,
							'maximum'     => 100,
							'default'     => 50,
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'           => array( 'type' => 'string' ),
						'cron_status'      => array( 'type' => 'object' ),
						'summary'          => array( 'type' => 'object' ),
						'upcoming_wp_cron' => array( 'type' => 'array' ),
						'overdue_wp_cron'  => array( 'type' => 'array' ),
						'action_scheduler' => array( 'type' => 'object' ),
						'wp_cron_all'      => array( 'type' => 'array' ),
						'action_scheduler_pending' => array( 'type' => 'array' ),
					),
				),
				'execute_callback'    => array( $this, 'handle_cron_insights_request' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'mcp' => array(
						'public'      => true,
						'type'        => 'tool',
						'title'       => __( 'Cron Insights', 'divewp-boost-site-performance' ),
						'description' => __( 'Expose cron status and queue snapshots via MCP.', 'divewp-boost-site-performance' ),
					),
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'DiveWP Abilities: failed to register ability divewp/cron-insights - %s', $result->get_error_message() ) );
		}
	}

	/**
	 * Permission callback for the server insights ability
	 *
	 * @since 2.1.0
	 *
	 * @return bool True if user has permission.
	 */
	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Handle Server Insights ability request
	 *
	 * Callback function for the divewp/server-insights ability.
	 * Retrieves and returns server health data.
	 *
	 * @since 2.1.0
	 *
	 * @param array $input Request input parameters.
	 * @return array Server insights data.
	 */
	public function handle_server_insights_request( $input ) {
		// Ensure Server Insights instance is available.
		if ( null === $this->server_insights ) {
			// Try to create instance if not provided.
			if ( class_exists( 'DiveWP_Server_Insights_New' ) ) {
				$this->server_insights = new DiveWP_Server_Insights_New();
			} else {
				return array(
					'error'   => true,
					'message' => __( 'Server Insights module is not available.', 'divewp-boost-site-performance' ),
				);
			}
		}

		try {
			// Get all server insights data.
			$insights = $this->server_insights->get_all_insights();

			// Optionally filter out recommendations if not requested.
			$include_recommendations = isset( $input['include_recommendations'] ) ? $input['include_recommendations'] : true;
			if ( ! $include_recommendations && isset( $insights['summary']['recommendations'] ) ) {
				$insights['summary']['recommendations'] = array();
			}

			return $insights;

		} catch ( Exception $e ) {
			return array(
				'error'   => true,
				'message' => sprintf(
					/* translators: %s: error message */
					__( 'Error retrieving server insights: %s', 'divewp-boost-site-performance' ),
					$e->getMessage()
				),
			);
		}
	}

	/**
	 * Handle Performance Checks ability request
	 *
	 * @param array $input Request input parameters.
	 * @return array Performance checks data.
	 */
	public function handle_performance_checks_request( $input ) {
		if ( ! class_exists( 'DiveWP_Performance_Checks' ) ) {
			return array(
				'error'   => true,
				'message' => __( 'Performance Checks module is not available.', 'divewp-boost-site-performance' ),
			);
		}

		$checker = new DiveWP_Performance_Checks();
		$data    = $checker->get_all_checks();

		return $data;
	}

	/**
	 * Handle DB Insights ability request
	 *
	 * @param array $input Request input parameters.
	 * @return array DB insights data.
	 */
	public function handle_db_insights_request( $input ) {
		if ( ! class_exists( 'DiveWP_DB_Insights' ) ) {
			return array(
				'error'   => true,
				'message' => __( 'Database Insights module is not available.', 'divewp-boost-site-performance' ),
			);
		}

		$insights = new DiveWP_DB_Insights();
		return $insights->get_all_checks();
	}

	/**
	 * Handle Security Insights ability request
	 *
	 * @param array $input Request input parameters.
	 * @return array Security insights data.
	 */
	public function handle_security_insights_request( $input ) {
		if ( ! class_exists( 'DiveWP_Security' ) ) {
			return array(
				'error'   => true,
				'message' => __( 'Security Insights module is not available.', 'divewp-boost-site-performance' ),
			);
		}

		$security = new DiveWP_Security();
		return $security->get_all_checks();
	}

	/**
	 * Handle Theme & Builder Insights ability request
	 *
	 * @param array $input Request input parameters.
	 * @return array Theme/builder insights data.
	 */
	public function handle_theme_builder_request( $input ) {
		if ( ! class_exists( 'DiveWP_Theme_Builder' ) ) {
			return array(
				'error'   => true,
				'message' => __( 'Theme & Builder module is not available.', 'divewp-boost-site-performance' ),
			);
		}

		$builder = new DiveWP_Theme_Builder();
		return $builder->get_all_checks();
	}

	/**
	 * Handle WooCommerce Best Practices ability request
	 *
	 * @param array $input Request input parameters.
	 * @return array WooCommerce best practices data.
	 */
	public function handle_woocommerce_best_practices_request( $input ) {
		if ( ! class_exists( 'DiveWP_WooCommerce_Best_Practices' ) ) {
			return array(
				'error'   => true,
				'message' => __( 'WooCommerce Best Practices module is not available.', 'divewp-boost-site-performance' ),
			);
		}

		$woo = new DiveWP_WooCommerce_Best_Practices();
		return $woo->get_all_checks();
	}

	/**
	 * Handle SEO Optimization ability request
	 *
	 * @param array $input Request input parameters.
	 * @return array SEO optimization data.
	 */
	public function handle_seo_optimization_request( $input ) {
		if ( ! class_exists( 'DiveWP_SEO_Optimization' ) ) {
			return array(
				'error'   => true,
				'message' => __( 'SEO Optimization module is not available.', 'divewp-boost-site-performance' ),
			);
		}

		$seo = new DiveWP_SEO_Optimization();
		return $seo->get_all_checks();
	}

	/**
	 * Handle Email Communications ability request
	 *
	 * @param array $input Request input parameters.
	 * @return array Email communications data.
	 */
	public function handle_email_communications_request( $input ) {
		if ( ! class_exists( 'DiveWP_Email_Insights' ) ) {
			return array(
				'error'   => true,
				'message' => __( 'Email Communications module is not available.', 'divewp-boost-site-performance' ),
			);
		}

		$email = new DiveWP_Email_Insights();
		return $email->get_all_checks();
	}

	/**
	 * Handle Hosting Benchmark latest result ability request
	 *
	 * @param array $input Request input parameters.
	 * @return array Latest benchmark data or error.
	 */
	public function handle_hosting_benchmark_latest_request( $input ) {
		// Load DB access.
		if ( ! class_exists( 'DiveWP_DB_Access' ) ) {
			require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-db-access.php';
		}

		if ( ! class_exists( 'DiveWP_DB_Access' ) ) {
			return array(
				'error'   => true,
				'message' => __( 'Database access unavailable for benchmark results.', 'divewp-boost-site-performance' ),
			);
		}

		$db_access = DiveWP_DB_Access::get_instance();
		$user_id   = get_current_user_id();

		$recent = $db_access->get_recent_benchmark_results( 1, $user_id );
		if ( empty( $recent ) ) {
			return array(
				'error'   => true,
				'message' => __( 'No saved benchmark results found.', 'divewp-boost-site-performance' ),
			);
		}

		$latest = is_array( $recent ) ? reset( $recent ) : $recent[0];
		if ( ! $latest || ! isset( $latest->id ) ) {
			return array(
				'error'   => true,
				'message' => __( 'Unable to load the latest benchmark record.', 'divewp-boost-site-performance' ),
			);
		}

		$full = $db_access->get_benchmark_result( $latest->id );
		if ( ! $full ) {
			return array(
				'error'   => true,
				'message' => __( 'Benchmark record not found.', 'divewp-boost-site-performance' ),
			);
		}

		$full_results = json_decode( $full->full_results, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$full_results = null;
		}

		return array(
			'status'            => 'success',
			'test_date'         => $full->test_date,
			'session_id'        => $full->session_id,
			'overall_score'     => (float) $full->overall_score,
			'performance_score' => (float) $full->performance_score,
			'database_score'    => (float) $full->database_score,
			'resources_score'   => (float) $full->resources_score,
			'concurrency_score' => (float) $full->concurrency_score,
			'full_results'      => $full_results,
		);
	}

	/**
	 * Check if the Abilities API is available
	 *
	 * Utility method to check WordPress version compatibility.
	 *
	 * @since 2.1.0
	 *
	 * @return bool True if Abilities API is available.
	 */
	public static function is_abilities_api_available() {
		return class_exists( 'WP_Ability' ) || function_exists( 'wp_register_ability' );
	}

	/**
	 * Handle Cron Insights ability request
	 *
	 * @param array $input Request input parameters.
	 * @return array Cron insights data.
	 */
	public function handle_cron_insights_request( $input ) {
		if ( ! class_exists( 'DiveWP_Cron_Jobs' ) ) {
			return array(
				'error'   => true,
				'message' => __( 'Cron Jobs module is not available.', 'divewp-boost-site-performance' ),
			);
		}

		$limit = isset( $input['limit'] ) ? absint( $input['limit'] ) : 5;
		$include_all = isset( $input['include_all'] ) ? (bool) $input['include_all'] : true;
		$as_limit = isset( $input['action_scheduler_limit'] ) ? absint( $input['action_scheduler_limit'] ) : 50;
		$cron  = new DiveWP_Cron_Jobs();

		return $cron->get_insights_snapshot( $limit, $include_all, $as_limit );
	}
}
