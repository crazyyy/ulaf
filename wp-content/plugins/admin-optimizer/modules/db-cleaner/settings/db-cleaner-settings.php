<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * DB Optimization Settings Class
 */
class DB_Cleaner_Settings extends WP_Settings_API_Helper {
	/**
	 * The options value stored in the database
	 *
	 * @var false|array
	 */
	protected $options;

	/**
	 * Constructor
	 *
	 * @param array $options DB Optimizer Options.
	 */
	public function __construct( $options ) {
		$this->options = $options;
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings_options = [
			[
				'option_group' => DB_Cleaner::OPTION_NAME,
				'option_name'  => DB_Cleaner::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			[
				'id'          => 'adminoptimizer-db-scheduler',
				'title'       => '',
				'description' => '',
				'menu_slug'   => DB_Cleaner::OPTION_NAME,
				'option_name' => DB_Cleaner::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'callback',
						'id'       => 'enable-db-cleanup',
						'title'    => __( 'Schedule Database Cleanup', 'admin-optimizer' ),
						'callback' => [ $this, 'render_enable_cleanup_field' ],
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Enable logs', 'admin-optimizer' ),
						'id'    => 'enable-logging',
						'name'  => 'enable_logging',
						'label' => __( 'Keep a log of the execution of the database cleanup.', 'admin-optimizer' ),
					],
				],
			],
			[
				'id'          => 'adminoptimizer-db-cleaner',
				'title'       => __( 'Items to Clean Up', 'admin-optimizer' ),
				'description' => '',
				'menu_slug'   => DB_Cleaner::OPTION_NAME,
				'option_name' => DB_Cleaner::OPTION_NAME,
				'fields'      => [
					[
						'type'  => 'checkbox',
						'title' => __( 'Post Revisions', 'admin-optimizer' ),
						'id'    => 'delete-revisions',
						'name'  => 'delete_revisions',
						'label' => __( 'Delete all post revisions', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Auto Draft', 'admin-optimizer' ),
						'id'    => 'delete-auto-draft',
						'name'  => 'delete_auto_draft',
						'label' => __( 'Delete auto draft.', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Trashed Posts', 'admin-optimizer' ),
						'id'    => 'delete-trashed-posts',
						'name'  => 'delete_trashed_posts',
						'label' => __( 'Clean up trashed posts.', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Orphaned Post Meta', 'admin-optimizer' ),
						'id'    => 'delete-orphaned-postmeta',
						'name'  => 'delete_orphaned_postmeta',
						'label' => __( 'Delete orphaned post meta', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Duplicate Post Meta', 'admin-optimizer' ),
						'id'    => 'delete-duplicate-postmeta',
						'name'  => 'delete_duplicate_postmeta',
						'label' => __( 'Delete duplicate post meta', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Post Meta with empty value', 'admin-optimizer' ),
						'id'    => 'delete-empty-postmeta',
						'name'  => 'delete_empty_postmeta',
						'label' => __( 'Delete unused post meta', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'oEmbed cache', 'admin-optimizer' ),
						'id'    => 'delete-oembed-cache',
						'name'  => 'delete_oembed_cache',
						'label' => __( 'Delete oEmbed cache', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Unapproved Comments', 'admin-optimizer' ),
						'id'    => 'delete-unapproved-comments',
						'name'  => 'delete_unapproved_comments',
						'label' => __( 'Delete all unapproved/pending comments', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Spam Comments', 'admin-optimizer' ),
						'id'    => 'delete-spam-comments',
						'name'  => 'delete_spam_comments',
						'label' => __( 'Delete all spam comments', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Trashed Comments', 'admin-optimizer' ),
						'id'    => 'delete-trashed-comments',
						'name'  => 'delete_trashed_comments',
						'label' => __( 'Delete all trashed comments', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Duplicate Comment Meta', 'admin-optimizer' ),
						'id'    => 'delete-duplicate-commentmeta',
						'name'  => 'delete_duplicate_commentmeta',
						'label' => __( 'Delete duplicate comment meta', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Orphaned Comment Meta', 'admin-optimizer' ),
						'id'    => 'delete-orphaned-commentmeta',
						'name'  => 'delete_orphaned_commentmeta',
						'label' => __( 'Delete orphaned comment meta', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Comment Meta with empty value', 'admin-optimizer' ),
						'id'    => 'delete-empty-commentmeta',
						'name'  => 'delete_empty_commentmeta',
						'label' => __( 'Delete comment meta with empty value', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Pingbacks', 'admin-optimizer' ),
						'id'    => 'delete-pingbacks',
						'name'  => 'delete_pingbacks',
						'label' => __( 'Delete pingbacks.', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Unused Terms', 'admin-optimizer' ),
						'id'    => 'delete-unused-terms',
						'name'  => 'delete_unused_terms',
						'label' => __( 'Delete unused Terms (post tags and categories)', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Duplicate Term Meta', 'admin-optimizer' ),
						'id'    => 'delete-duplicate-termmeta',
						'name'  => 'delete_duplicate_termmeta',
						'label' => __( 'Delete duplicate Term Meta', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Orphaned Term Meta', 'admin-optimizer' ),
						'id'    => 'delete-orphaned-termmeta',
						'name'  => 'delete_orphaned_termmeta',
						'label' => __( 'Delete orphaned Term Meta', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Orphaned Term Relationship', 'admin-optimizer' ),
						'id'    => 'delete-orphaned-term-rs',
						'name'  => 'delete_orphaned_term_rs',
						'label' => __( 'Delete orphaned Term relationship', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Duplicate User Meta', 'admin-optimizer' ),
						'id'    => 'delete-duplicate-usermeta',
						'name'  => 'delete_duplicate_usermeta',
						'label' => __( 'Delete duplicate user meta', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Orphaned User Meta', 'admin-optimizer' ),
						'id'    => 'delete-orphaned-usermeta',
						'name'  => 'delete_orphaned_usermeta',
						'label' => __( 'Delete orphaned user meta', 'admin-optimizer' ),
					],
				],
			],
			[
				'id'          => 'adminoptimizer-db-cleanup-settings',
				'title'       => __( 'Clean Up Settings', 'admin-optimizer' ),
				'description' => '',
				'menu_slug'   => DB_Cleaner::OPTION_NAME,
				'option_name' => DB_Cleaner::OPTION_NAME,
				'fields'      => [
					[
						'type'    => 'number',
						'id'      => 'batch-size',
						'name'    => 'batch_size',
						'min'     => 100,
						'max'     => 2000,
						'default' => 500,
						'title'   => __( 'Batch job size', 'admin-optimizer' ),
						'desc'    => __( 'The maximum amount of entries to delete at each cleanup run. Depending on your server and database size, a big batch job size might result in timeout and/or crash the server. For a good server, a value of 500 - 1000 is a good starting point. For a slow/shared hosting, start with 100 and increase gradually.', 'admin-optimizer' ),
					],
				],
			],
			'pro' => [
				'id'              => 'adminoptimizer-db-optimization-pro',
				'title'           => __( 'Pro Options', 'admin-optimizer' ),
				/* translators: %1$s is the anchor link to the Pro version. %2$s is the closing anchor tag */
					'description' => sprintf( __( 'Upgrade to the %1$sPro version%2$s to access these features', 'admin-optimizer' ), '<a href="' . esc_url( 'https://www.adminoptimizer.com/#pricing' ) . '" target="_blank">', '</a>' ),
				'menu_slug'       => DB_Cleaner::MENU_SLUG . '_pro',
				'option_name'     => DB_Cleaner::OPTION_NAME,
				'fields'          => [
					[
						'type'     => 'callback',
						'id'       => 'delete-revisions-pro',
						'title'    => __( 'Post Revisions', 'admin-optimizer' ),
						'callback' => [ $this, 'render_delete_revisions_field' ],
					],
					[
						'type'     => 'radio',
						'title'    => __( 'Transient Objects', 'admin-optimizer' ),
						'id'       => 'delete-transient-pro',
						'name'     => 'delete_transient_pro',
						'choices'  => [
							'all'     => __( 'Delete all transient object', 'admin-optimizer' ),
							'expired' => __( 'Delete expired transient object', 'admin-optimizer' ),
							'0'       => __( 'Do not delete transient object', 'admin-optimizer' ),
						],
						'disabled' => 'disabled',
						'desc'     => __( 'Transient objects are temporary data in the database with a time limit. It doesn\'t always get deleted upon expiry, which can cause bloat to your database.', 'admin-optimizer' ),
					],
					[
						'type'     => 'checkbox',
						'title'    => __( 'Deep Cleaning', 'admin-optimizer' ),
						'id'       => 'deep-cleaning',
						'name'     => 'deep_cleaning',
						'disabled' => 'disabled',
						'label'    => __( 'Items will be deleted using WordPress functions. This will ensure no residual are left being during the clean up.', 'admin-optimizer' ),
					],
					[
						'type'     => 'checkbox',
						'title'    => __( 'Continuous Cleaning', 'admin-optimizer' ),
						'id'       => 'continuous-cleaning',
						'name'     => 'continuous_cleaning',
						'disabled' => 'disabled',
						'label'    => __( 'Continue to clean the database in the background until all the unwanted items are removed. This is useful for a large database with lots of cruft.', 'admin-optimizer' ),
					],
				],
			],
		];

		$this->setup();
	}

	/**
	 * Render enable database cleanup settings field
	 *
	 * @return void
	 */
	public function render_enable_cleanup_field() {
		$option_name       = DB_Cleaner::OPTION_NAME;
		$enabled           = $this->options['enable'] ?? '0';
		$enabled_day       = $this->options['enable_day'] ?? 'sunday';
		$enabled_time_hour = $this->options['enable_time_hour'] ?? '3';
		$enabled_time_min  = $this->options['enable_time_min'] ?? '0';
		$day_select_field  = '<select name="' . esc_attr( $option_name ) . '[enable_day]">
                <option value="sunday" ' . selected( 'sunday', $enabled_day, false ) . '>' . esc_html__( 'Sunday', 'admin-optimizer' ) . '</option>
                <option value="monday" ' . selected( 'monday', $enabled_day, false ) . '>' . esc_html__( 'Monday', 'admin-optimizer' ) . '</option>
                <option value="tuesday" ' . selected( 'tuesday', $enabled_day, false ) . '>' . esc_html__( 'Tuesday', 'admin-optimizer' ) . '</option>
                <option value="wednesday" ' . selected( 'wednesday', $enabled_day, false ) . '>' . esc_html__( 'Wednesday', 'admin-optimizer' ) . '</option>
                <option value="thursday" ' . selected( 'thursday', $enabled_day, false ) . '>' . esc_html__( 'Thursday', 'admin-optimizer' ) . '</option>
                <option value="friday" ' . selected( 'friday', $enabled_day, false ) . '>' . esc_html__( 'Friday', 'admin-optimizer' ) . '</option>
                <option value="saturday" ' . selected( 'saturday', $enabled_day, false ) . '>' . esc_html__( 'Saturday', 'admin-optimizer' ) . '</option>
            </select>';
		$hour_select_field = '<select name="' . esc_attr( $option_name ) . '[enable_time_hour]">';
		for ( $i = 0; $i < 24; $i++ ) {
			$hour_select_field .= '<option value="' . $i . '" ' . selected( $i, $enabled_time_hour, false ) . '>' . esc_html( str_pad( $i, 2, '0', STR_PAD_LEFT ) ) . '</option>';
		}
		$hour_select_field .= '</select>';
		$min_select_field   = '<select name="' . esc_attr( $option_name ) . '[enable_time_min]">';
		for ( $i = 0; $i < 60; $i++ ) {
			$min_select_field .= '<option value="' . $i . '" ' . selected( $i, $enabled_time_min, false ) . '>' . esc_html( str_pad( $i, 2, '0', STR_PAD_LEFT ) ) . '</option>';
		}
		$min_select_field .= '</select>';
		?>
		<p>
			<label><input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>[enable]" value="1" <?php checked( '1', $enabled ); ?>> 
			<?php
				printf(
					/* translators: %1$s: day interval, %2$s time interval */
					esc_html__( ' Enable Database optimization to run every %1$s at %2$s:%3$s', 'admin-optimizer' ),
					wp_kses(
						$day_select_field,
						[
							'select' => [
								'name' => [],
							],
							'option' => [
								'value'    => [],
								'selected' => [],
							],
						]
					),
					wp_kses(
						$hour_select_field,
						[
							'select' => [
								'name' => [],
							],
							'option' => [
								'value'    => [],
								'selected' => [],
							],
						]
					),
					wp_kses(
						$min_select_field,
						[
							'select' => [
								'name' => [],
							],
							'option' => [
								'value'    => [],
								'selected' => [],
							],
						]
					)
				);
			?>
				</label>
		</p>
		<?php
	}

	/**
	 * Render delete revisions Settings field.
	 *
	 * @return void
	 */
	public function render_delete_revisions_field() {
		$option_name          = DB_Cleaner::OPTION_NAME;
		$delete_revisions_day = 30;
		?>
		<p>
			<label><input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>[delete_revisions_pro]" value="custom" disabled="disabled">
				<?php
				/* translators: %s: day interval */
				printf( esc_html__( 'Only delete post revisions that are more than %s days old', 'admin-optimizer' ), '<input type="number" name="' . esc_attr( $option_name ) . '[delete_revisions_day]" id="delete-revisions-day" size="2" value="' . esc_attr( $delete_revisions_day ) . '" disabled="disabled">' );
				?>
			</label>
		</p>
		<?php
	}

	/**
	 * Render delete transient Settings field.
	 *
	 * @return void
	 */
	public function render_delete_transient_field() {
		$option_name      = DB_Cleaner::OPTION_NAME;
		$transient_option = $this->options['delete_transient'] ?? '';
		?>
		<label><input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>[delete_transient]" value="expired" <?php checked( 'expired', $transient_option ); ?>>  <?php esc_html_e( 'Delete expired transient objects', 'admin-optimizer' ); ?></label><br/>
		<p class="description">
			<?php esc_html_e( 'Transient objects are temporary data in the database with a time limit. It doesn\'t always get deleted upon expiry, which can bloat your database.', 'admin-optimizer' ); ?>
		</p>
		<?php
	}

	/**
	 * Render Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer - Database Cleaner', 'admin-optimizer' ); ?></h1>
			<?php $tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore ?>
			<h2 class="nav-tab-wrapper">
				<?php $admin_url = admin_url( 'admin.php?page=' . DB_Cleaner::MENU_SLUG ); ?>
				<a href="<?php echo esc_url( $admin_url ); ?>" class="nav-tab
				<?php
				if ( empty( $tab ) ) {
					echo ' nav-tab-active'; }
				?>
				"><?php esc_html_e( 'DB Cleanup', 'admin-optimizer' ); ?></a>
				<a href="<?php echo esc_url( $admin_url . '&tab=logs' ); ?>" class="nav-tab
				<?php
				if ( 'logs' === $tab ) {
					echo ' nav-tab-active'; }
				?>
				"><?php esc_html_e( 'Logs', 'admin-optimizer' ); ?></a>
				<a href="<?php echo esc_url( $admin_url . '&tab=expert' ); ?>" class="nav-tab
				<?php
				if ( 'expert' === $tab ) {
					echo ' nav-tab-active'; }
				?>
				"><?php esc_html_e( 'Expert Mode (Pro)', 'admin-optimizer' ); ?></a>
			</h2>

			<?php if ( 'logs' === $tab ) : ?>
				<?php $this->render_logs_on_page(); ?>
			<?php elseif ( 'expert' === $tab ) : ?>
				<?php $this->render_expert_page(); ?>
			<?php else : ?>
				<?php wp_nonce_field( 'adminoptim-db-cleanup', 'db-nonce' ); ?>
				<?php settings_errors(); ?>
				<?php $this->render_settings_on_page( DB_Cleaner::OPTION_NAME ); ?>
				<div class="adminoptim-pro-options">
					<?php $this->render_settings_on_page( DB_Cleaner::MENU_SLUG . '_pro', [ 'remove_submit_button' => true ] ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Callback function to sanitize user options
	 *
	 * @param array $options User options.
	 *
	 * @return array
	 */
	public function sanitize_settings( $options ) {
		$sanitized_options = [];
		if ( is_array( $options ) ) {
			if ( isset( $options['enable'] ) ) {
				$sanitized_options['enable'] = '1';
			}
			if ( isset( $options['enable_day'] ) ) {
				$sanitized_options['enable_day'] = in_array( $options['enable_day'], [ 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ], true ) ? $options['enable_day'] : 'sunday';
				if ( isset( $this->options['enable_day'] ) && $this->options['enable_day'] !== $sanitized_options['enable_day'] ) {
					if ( as_has_scheduled_action( 'adminoptim_database_cleanup' ) ) {
						as_unschedule_all_actions( 'adminoptim_database_cleanup' );
					}
				}
			}
			if ( isset( $options['enable_time_hour'] ) ) {
				$sanitized_options['enable_time_hour'] = in_array( $options['enable_time_hour'], array_map( 'strval', range( '0', '23' ) ), true ) ? $options['enable_time_hour'] : '0';
				if ( isset( $this->options['enable_time_hour'] ) && $this->options['enable_time_hour'] !== $sanitized_options['enable_time_hour'] ) {
					if ( as_has_scheduled_action( 'adminoptim_database_cleanup' ) ) {
						as_unschedule_all_actions( 'adminoptim_database_cleanup' );
					}
				}
			}
			if ( isset( $options['enable_time_min'] ) ) {
				$sanitized_options['enable_time_min'] = in_array( $options['enable_time_min'], array_map( 'strval', range( '0', '59' ) ), true ) ? $options['enable_time_min'] : '0';
				if ( isset( $this->options['enable_time_min'] ) && $this->options['enable_time_min'] !== $sanitized_options['enable_time_min'] ) {
					if ( as_has_scheduled_action( 'adminoptim_database_cleanup' ) ) {
						as_unschedule_all_actions( 'adminoptim_database_cleanup' );
					}
				}
			}
			if ( isset( $options['delete_revisions'] ) ) {
				$sanitized_options['delete_revisions'] = '1';
			}
			if ( isset( $options['delete_auto_draft'] ) ) {
				$sanitized_options['delete_auto_draft'] = '1';
			}
			if ( isset( $options['delete_trashed_posts'] ) ) {
				$sanitized_options['delete_trashed_posts'] = '1';
			}
			if ( isset( $options['delete_orphaned_postmeta'] ) ) {
				$sanitized_options['delete_orphaned_postmeta'] = '1';
			}
			if ( isset( $options['delete_duplicate_postmeta'] ) ) {
				$sanitized_options['delete_duplicate_postmeta'] = '1';
			}
			if ( isset( $options['delete_empty_postmeta'] ) ) {
				$sanitized_options['delete_empty_postmeta'] = '1';
			}
			if ( isset( $options['delete_oembed_cache'] ) ) {
				$sanitized_options['delete_oembed_cache'] = '1';
			}
			if ( isset( $options['delete_unapproved_comments'] ) ) {
				$sanitized_options['delete_unapproved_comments'] = '1';
			}
			if ( isset( $options['delete_spam_comments'] ) ) {
				$sanitized_options['delete_spam_comments'] = '1';
			}
			if ( isset( $options['delete_trashed_comments'] ) ) {
				$sanitized_options['delete_trashed_comments'] = '1';
			}
			if ( isset( $options['delete_duplicate_commentmeta'] ) ) {
				$sanitized_options['delete_duplicate_commentmeta'] = '1';
			}
			if ( isset( $options['delete_orphaned_commentmeta'] ) ) {
				$sanitized_options['delete_orphaned_commentmeta'] = '1';
			}
			if ( isset( $options['delete_empty_commentmeta'] ) ) {
				$sanitized_options['delete_empty_commentmeta'] = '1';
			}
			if ( isset( $options['delete_pingbacks'] ) ) {
				$sanitized_options['delete_pingbacks'] = '1';
			}
			if ( isset( $options['delete_unused_terms'] ) ) {
				$sanitized_options['delete_unused_terms'] = '1';
			}
			if ( isset( $options['delete_duplicate_termmeta'] ) ) {
				$sanitized_options['delete_duplicate_termmeta'] = '1';
			}
			if ( isset( $options['delete_orphaned_termmeta'] ) ) {
				$sanitized_options['delete_orphaned_termmeta'] = '1';
			}
			if ( isset( $options['delete_orphaned_term_rs'] ) ) {
				$sanitized_options['delete_orphaned_term_rs'] = '1';
			}
			if ( isset( $options['delete_duplicate_usermeta'] ) ) {
				$sanitized_options['delete_duplicate_usermeta'] = '1';
			}
			if ( isset( $options['delete_orphaned_usermeta'] ) ) {
				$sanitized_options['delete_orphaned_usermeta'] = '1';
			}
			if ( isset( $options['batch_size'] ) ) {
				if ( 100 <= (int) $options['batch_size'] && 2000 >= (int) $options['batch_size'] ) {
					$sanitized_options['batch_size'] = (int) $options['batch_size'];
				} else {
					$sanitized_options['batch_size'] = 500;
				}
			} else {
				$sanitized_options['batch_size'] = 500;
			}
			if ( isset( $options['enable_logging'] ) ) {
				$sanitized_options['enable_logging'] = '1';
			}
		}
		return $sanitized_options;
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook_suffix Hook suffix.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, DB_Cleaner::MENU_SLUG ) ) {
			wp_enqueue_style( 'adminoptim-modules-pro-settings' );
		}
	}

	/**
	 * Render logs Settings page
	 *
	 * @return void
	 */
	public function render_logs_on_page() {
		if ( isset( $_POST['adminoptim_delete_logs'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['adminoptim_delete_logs'] ) ) ) {
			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'adminoptim-db-delete-logs' ) ) {
				delete_option( 'adminoptim_db_log' );
				wp_admin_notice(
					__( 'Log files deleted.', 'admin-optimizer' ),
					[
						'type' => 'success',
					]
				);
			}
		}

		$db_log = get_option( 'adminoptim_db_log', [] );
		?>
		<div id="logs-wrap">
		<?php
		if ( ! empty( $db_log ) ) {
			foreach ( $db_log as $log ) :
				?>
				<div style="margin:1rem 0;background-color:#ddd;padding:1rem;">
					<p><?php esc_html_e( 'Start time:', 'admin-optimizer' ); ?> <?php echo esc_html( wp_date( 'Y-m-d H:i:s', $log['start'] ) ); ?></p>
					<?php
					$clean_log   = ! empty( $log['clean_log'] ) ? $log['clean_log'] : '';
					$log_message = str_replace( '%%', '</li><li>', $clean_log );
					?>
					<ul>
						<li><?php echo wp_kses( $log_message, [ 'li' => [] ] ); ?></li>
					</ul>
					<p><?php esc_html_e( 'End time:', 'admin-optimizer' ); ?> <?php echo esc_html( wp_date( 'Y-m-d H:i:s', $log['end'] ) ); ?></p>
					<?php $time_taken = ( $log['end'] - $log['start'] ) . ' seconds'; ?>
					<p><?php esc_html_e( 'Total time:', 'admin-optimizer' ); ?> <?php echo esc_html( $time_taken ); ?>.</p>
				</div>
			<?php endforeach; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=adminoptimizer-db-cleaner&tab=logs' ) ); ?>">
				<?php wp_nonce_field( 'adminoptim-db-delete-logs' ); ?>
				<input type="hidden" name="adminoptim_delete_logs" value="1" />
				<?php submit_button( 'Delete all logs' ); ?>
			</form>
			<?php
		} else {
			?>
			<p><?php esc_html_e( 'No logs found.', 'admin-optimizer' ); ?></p>
			<?php
		}
		?>
		</div>
		<?php
	}

	/**
	 * Render Expert Settings page
	 *
	 * @return void
	 */
	public function render_expert_page() {
		?>
		<div class="adminoptim-pro-options">
			<h2><?php esc_html_e( 'Expert mode - Pro version only', 'admin-optimizer' ); ?></h2>
			<p>
			<?php
			/* translators: %1$s is the anchor link to the Pro version. %2$s is the closing anchor tag */
				printf( esc_html__( 'Upgrade to the %1$sPro version%2$s to access these features', 'admin-optimizer' ), '<a href="' . esc_url( 'https://www.adminoptimizer.com/#pricing' ) . '" target="_blank">', '</a>' );
			?>
			</p>
			<h3><?php esc_html_e( 'Manual Actions:', 'admin-optimizer' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Run database cleaner manually.', 'admin-optimizer' ); ?></li>
				<li><?php esc_html_e( 'Check the cleanup count for each table.', 'admin-optimizer' ); ?></li>
				<li><?php esc_html_e( 'Clean individual database table.', 'admin-optimizer' ); ?></li>
				<li><?php esc_html_e( 'Make informed decision of whether to clean up the database.', 'admin-optimizer' ); ?></li>
			</ul>
			<div class="bordered">
				<img src="<?php echo esc_url( DB_Cleaner::MODULE_URL . 'assets/screenshots/manual-actions.png' ); ?>" alt="manual-actions" style="max-width:100%;">
			</div>
			<h3><?php esc_html_e( 'Optimizing Options Table', 'admin-optimizer' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Track orphaned options.', 'admin-optimizer' ); ?></li>
				<li><?php esc_html_e( 'Disable autoload of options to improve performance', 'admin-optimizer' ); ?></li>
				<li><?php esc_html_e( 'Remove orphaned options.', 'admin-optimizer' ); ?></li>
				<li><?php esc_html_e( 'Clean Options table to free up database space.', 'admin-optimizer' ); ?></li>
			</ul>
			<div class="bordered">
				<img src="<?php echo esc_url( DB_Cleaner::MODULE_URL . 'assets/screenshots/options-table.png' ); ?>" alt="admin optimizer pro options table optimizer" style="max-width:100%;">
			</div>
		</div>
		<?php
	}
}