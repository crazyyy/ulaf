<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modified_Date class
 */
class Modified_Date {
	const OPTION_NAME = 'adminoptim_modified_date';

	/**
	 * User Options
	 *
	 * @var false|mixed|null
	 */
	protected $option;

	/**
	 * Settings class
	 *
	 * @var Modified_Date_Settings
	 */
	protected $settings;

	const MENU_SLUG = 'adminoptimizer-modified-date';

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'modified-date/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'modified-date/';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->option = get_option( self::OPTION_NAME, [] );
		if ( ! isset( $this->option['post_types'] ) ) {
			$this->option['post_types'] = [ 'post' ];
		}
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings = new Modified_Date_Settings( $this->option );
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		add_action( 'post_submitbox_misc_actions', [ $this, 'add_classic_modified_date_field' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_scripts' ] );
		add_filter( 'wp_insert_post_data', [ $this, 'maybe_lock_modified_date' ], 999, 2 );
		foreach ( $this->option['post_types'] as $post_type ) {
			add_filter( "rest_pre_insert_{$post_type}", [ $this, 'add_modified_params' ], 10, 2 );
		}
		add_action( 'init', [ $this, 'register_post_meta' ] );
		if ( ! empty( $this->option['add_modified_date_column'] ) ) {
			add_action( 'init', [ $this, 'add_custom_columns' ], 100 );
		}
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'Lock Modified Date', 'admin-optimizer' ),
			__( 'Lock Modified Date', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 *
	 * Add modified date fields to Clasic editor
	 *
	 * @param \WP_Post $post post object.
	 *
	 * @return void
	 */
	public function add_classic_modified_date_field( $post ) {
		if ( ! empty( $this->option['publish_only'] ) && 'publish' !== $post->post_status ) {
			return;
		}

		$current_user_can_modify = apply_filters( 'adminoptim_modified_date_user_check', true );
		if ( ! $current_user_can_modify ) {
			return;
		}

		$is_allowed_post_type = apply_filters( 'adminoptim_modified_date_post_type_check', true, $post->post_type );

		if ( ! $is_allowed_post_type ) {
			return;
		}

		if ( ! empty( $this->option['disable_modified_date_update'] ) ) {
			$modified_message = esc_html__( 'The Modified Date field is currently locked.', 'admin-optimizer' );
			$input_name       = 'adminoptim_update_modified_date';
			$input_label      = esc_html__( 'Update modified date', 'admin-optimizer' );
		} else {
			$modified_message = '';
			$input_name       = 'adminoptim_lock_modified_date';
			$input_label      = esc_html__( 'Lock modified date', 'admin-optimizer' );
		}
		?>
		<div id="adminoptim-modified-date-section" class="misc-pub-section curtime misc-pub-curtime">
			<span id="modified-timestamp"><?php esc_html_e( ' Modified on', 'admin-optimizer' ); ?> <b><span id="modified-time-string"><?php echo esc_html( get_the_modified_date( '', $post->ID ) . ' at ' . get_the_modified_time( 'H:i', $post->ID ) ); ?></span></b></span> <a href="#edit_modified_timestamp" class="edit-timestamp hide-if-no-js" role="button" style="display:none;">
				<span aria-hidden="true"><?php esc_html_e( 'Edit', 'admin-optimizer' ); ?></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Edit date and time', 'admin-optimizer' ); ?></span>
			</a>
			<fieldset id="modified-timestampdiv" class="hide-if-js" style="display: none;">
				<legend class="screen-reader-text"><?php esc_html_e( 'Date and time', 'admin-optimizer' ); ?></legend>
				<?php $this->touch_modified_time( 0, false ); ?>
			</fieldset>
		</div>
		<div id="adminoptim-lock-modified-date-section" class="misc-pub-section">
			<?php
			if ( ! empty( $modified_message ) ) {
				echo '<span>' . esc_html( $modified_message ) . '</span><br/><br/>';
			}
			?>
			<label for="adminoptim-lock-modified-date-field"><input type="checkbox" id="adminoptim-lock-modified-date-field" name="<?php echo esc_attr( $input_name ); ?>" value="1"><?php echo esc_html( $input_label ); ?></label>
		</div>
		<input type="hidden" name="adminoptim_is_classic" value="1">
		<input type="hidden" name="adminoptim_lock_modified_date" id="adminoptim-lock-modified-date" value="<?php echo ( ! empty( $this->option['disable_modified_date_update'] ) ) ? '1' : '0'; ?>">
		<input type="hidden" name="adminoptim_modified_date" id="adminoptim-modified-date" value="<?php echo esc_attr( $post->post_modified ); ?>">
		<?php
	}

	/**
	 * Fires when enqueuing scripts for all admin pages.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		global $pagenow, $post;

		$screen = get_current_screen();

		if ( ! empty( $this->option['publish_only'] ) && ( isset( $post->post_status ) && 'publish' !== $post->post_status ) ) {
			return;
		}

		if ( in_array( $pagenow, [ 'post.php', 'edit.php', 'post-new.php', 'page.php', 'edit-pages.php', 'page-new.php' ], true ) ) {
			$enqueue = true;
			if ( ! empty( $this->option['disable_publish_modified_date'] ) && 'publish' !== $post->post_status ) {
				$enqueue = false;
			}
			$current_user_can_modify = apply_filters( 'adminoptim_modified_date_user_check', true );
			if ( ! $current_user_can_modify ) {
				$enqueue = false;
			}

			$is_allowed_post_type = true;
			if ( isset( $post->post_type ) ) {
				$is_allowed_post_type = apply_filters( 'adminoptim_modified_date_post_type_check', true, $post->post_type );
			}

			if ( ! $is_allowed_post_type ) {
				$enqueue = false;
			}
			if ( $enqueue ) {
				if ( ! $screen->is_block_editor() ) {
					wp_enqueue_script( 'adminoptimizer-modified-date-classic', self::MODULE_URL . 'assets/js/modified-classic.min.js', [ 'jquery' ], filemtime( self::MODULE_PATH . 'assets/js/modified-classic.min.js' ), true );
					wp_enqueue_style( 'adminoptimizer-modified-date-classic', self::MODULE_URL . 'assets/css/modified-date.css', [], filemtime( self::MODULE_PATH . 'assets/css/modified-date.min.css' ) );
				}
				$modified = [];
				if ( ! empty( $this->option['disable_modified_date_update'] ) ) {
					$modified['locked'] = true;
				} else {
					$modified['locked'] = false;
				}
				if ( isset( $post->post_modified ) ) {
					$modified['modified_date'] = $post->post_modified;
				} else {
					$modified['modified_date'] = '';
				}
				wp_localize_script( 'adminoptimizer-modified-date-classic', 'adminoptimizerModifiedDate', $modified );
				wp_localize_script( 'adminoptimizer-modified-date-block', 'adminoptimizerModifiedDate', $modified );
			}
		}
	}

	/**
	 * Enqueue scripts for block editor
	 *
	 * @return void
	 */
	public function enqueue_block_scripts() {
		global $pagenow, $post;
		$screen = get_current_screen();

		if ( ! empty( $this->option['publish_only'] ) && ( isset( $post->post_status ) && 'publish' !== $post->post_status ) ) {
			return;
		}
		if ( in_array( $pagenow, [ 'post.php', 'edit.php', 'page.php', 'edit-pages.php' ], true ) ) {
			if ( $screen->is_block_editor() ) {
				$enqueue = true;
				if ( ! empty( $this->option['disable_publish_modified_date'] ) && 'publish' !== $post->post_status ) {
					$enqueue = false;
				}
				$current_user_can_modify = apply_filters( 'adminoptim_modified_date_user_check', true );
				if ( ! $current_user_can_modify ) {
					$enqueue = false;
				}

				$is_allowed_post_type = apply_filters( 'adminoptim_modified_date_post_type_check', true, $post->post_type );

				if ( ! $is_allowed_post_type ) {
					$enqueue = false;
				}
				if ( $enqueue ) {
					wp_enqueue_script(
						'adminoptimizer-modified-date-block',
						self::MODULE_URL . 'block/dist/index.js',
						[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ],
						filemtime( self::MODULE_PATH . 'block/dist/index.js' ),
						true
					);

					wp_set_script_translations(
						'adminoptimizer-modified-date-block',
						'admin-optimizer',
						ADMINOPTIMIZER_PATH . 'languages'
					);
				}
			}
		}
	}

	/**
	 * Filter pre_insert result and add modified param
	 *
	 * @param  object           $prepared_post  An object representing a single post prepared for inserting or updating the database.
	 * @param \WP_REST_REQUEST $request  Request object.
	 *
	 * @return object
	 */
	public function add_modified_params( $prepared_post, $request ) {
		$params = $request->get_params();

		if ( isset( $params['modified'] ) ) {
			$prepared_post->adminoptim_modified = $params['modified'];
		}

		if ( isset( $params['meta']['_adminoptim_lock_modified_date'] ) ) {
			$prepared_post->adminoptim_lockmodifieddate = $params['meta']['_adminoptim_lock_modified_date'];
		}

		return $prepared_post;
	}

	/**
	 * Filters slashed post data just before it is inserted into the database.
	 *
	 * @param array $data An array of slashed, sanitized, and processed post data.
	 * @param array $postarr An array of sanitized (and slashed) but otherwise unmodified post data.
	 *
	 * @return array
	 */
	public function maybe_lock_modified_date( $data, $postarr ) {
		if ( ! isset( $postarr['ID'] ) ) {
			return $data;
		}

		if ( ! empty( $this->option['publish_only'] ) && 'publish' !== $data['post_status'] ) {
			return $data;
		}

		if ( isset( $data['post_type'] ) ) {
			$is_allowed_post_type = apply_filters( 'adminoptim_modified_date_post_type_check', true, $data['post_type'] );
			if ( ! $is_allowed_post_type ) {
				return $data;
			}
		}

		$current_user_can_modify = apply_filters( 'adminoptim_modified_date_user_check', true );

		/**
		 * Handle classic save.
		 */
		if ( isset( $postarr['adminoptim_is_classic'] ) ) {
			// for classic editor.
			if ( isset( $postarr['adminoptim_lock_modified_date'] ) ) {
				if ( '1' === $postarr['adminoptim_lock_modified_date'] ) {
					if ( isset( $postarr['post_modified'] ) ) {
						$data['post_modified'] = $postarr['post_modified'];
					}
					if ( isset( $postarr['post_modified_gmt'] ) ) {
						$data['post_modified_gmt'] = $postarr['post_modified_gmt'];
					}
				} elseif ( '-1' === $postarr['adminoptim_lock_modified_date'] ) {
					if ( ! empty( $postarr['adminoptim_modified_date'] ) && $current_user_can_modify ) {
						$last_modified_time        = sanitize_text_field( wp_unslash( $postarr['adminoptim_modified_date'] ) );
						$modified_timestamp        = strtotime( $last_modified_time );
						$modified_date             = date( 'Y-m-d H:i:s', $modified_timestamp ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
						$data['post_modified']     = $modified_date;
						$data['post_modified_gmt'] = gmdate( 'Y-m-d H:i:s', $modified_timestamp );
					}
				}
			}
		} else {
			/**
			 * Handle block editor save.
			 */
			$lock_modified_date = false;
			if ( ! isset( $postarr['adminoptim_lockmodifieddate'] ) ) {
				$meta_exist = metadata_exists( $postarr['post_type'], $postarr['ID'], '_adminoptim_lock_modified_date' );
				if ( $meta_exist ) {
					$lock_meta = get_post_meta( $postarr['ID'], '_adminoptim_lock_modified_date', true );
					if ( $lock_meta ) {
						$lock_modified_date = true;
					}
				} elseif ( ! empty( $this->option['disable_modified_date_update'] ) ) {
						$lock_modified_date = true;
				}
			} elseif ( ! empty( $postarr['adminoptim_lockmodifieddate'] ) ) {
					$lock_modified_date = true;
			}

			if ( $lock_modified_date ) {
				if ( isset( $postarr['post_modified'] ) ) {
					$data['post_modified'] = $postarr['post_modified'];
				}
				if ( isset( $postarr['post_modified_gmt'] ) ) {
					$data['post_modified_gmt'] = $postarr['post_modified_gmt'];
				}
			} else {
				$data['post_modified']     = current_time( 'mysql' );
				$data['post_modified_gmt'] = current_time( 'mysql', true );

				// Check the duplicate request.
				$temp_date           = get_post_meta( $postarr['ID'], '_adminoptim_temp_modified_date', true );
				$published_timestamp = strtotime( $postarr['post_date'] );
				if ( $current_user_can_modify ) {
					if ( ! empty( $postarr['adminoptim_modified'] ) ) {
						$modified_timestamp = strtotime( $postarr['adminoptim_modified'] );
						if ( $modified_timestamp >= $published_timestamp ) {
							$modified_date = date( 'Y-m-d H:i:s', $modified_timestamp ); //phpcs:ignore
						} else {
							$modified_date = date( 'Y-m-d H:i:s', $published_timestamp ); //phpcs:ignore
						}
						$data['post_modified']     = $modified_date;
						$data['post_modified_gmt'] = get_gmt_from_date( $modified_date );
						update_post_meta( $postarr['ID'], '_adminoptim_temp_modified_date', $postarr['adminoptim_modified'] );
					} elseif ( ! empty( $temp_date ) ) {
						$temp_modified_timestamp = strtotime( $temp_date );
						if ( $temp_modified_timestamp >= $published_timestamp ) {
							$temp_modified_date = date( 'Y-m-d H:i:s', $temp_modified_timestamp ); //phpcs:ignore
						} else {
							$temp_modified_date = date( 'Y-m-d H:i:s', $temp_modified_timestamp ); //phpcs:ignore
						}
						$data['post_modified']     = $temp_modified_date;
						$data['post_modified_gmt'] = get_gmt_from_date( $temp_modified_date );
						delete_post_meta( $postarr['ID'], '_adminoptim_temp_modified_date' );
					}
				}
			}
		}
		return $data;
	}

	/**
	 * Register post meta
	 *
	 * @return void
	 */
	public function register_post_meta() {
		foreach ( $this->option['post_types'] as $post_type ) {
			register_post_meta(
				$post_type,
				'_adminoptim_lock_modified_date',
				[
					'show_in_rest'  => true,
					'type'          => 'boolean',
					'single'        => true,
					'default'       => ! empty( $this->option['disable_modified_date_update'] ),
					'auth_callback' => '__return_true',
				]
			);
		}
	}

	/**
	 * Actions to add custom columns to Posts
	 *
	 * @return void
	 */
	public function add_custom_columns() {
		add_action( 'manage_post_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );
		add_filter( 'manage_post_posts_columns', [ $this, 'custom_columns' ] );
		add_filter( 'manage_edit-post_sortable_columns', [ $this, 'add_sortable_column' ] );
	}

	/**
	 * Filters the columns displayed in the Posts list table.
	 *
	 * @param array $columns An associative array of column headings.
	 *
	 * @return array
	 */
	public function custom_columns( $columns ) {
		if ( ! isset( $columns['modified'] ) ) {
			$columns['modified'] = __( 'Modified Date', 'admin-optimizer' );
		}

		return $columns;
	}

	/**
	 * Fires in each custom column in the Posts list table.
	 *
	 * @param string $column_name The name of the column to display.
	 *
	 * @param int    $post_id The current post ID.
	 *
	 * @return void
	 */
	public function render_custom_columns( $column_name, $post_id ) {
		if ( 'modified' === $column_name ) {
			$content  = __( 'Modified', 'admin-optimizer' ) . '<br/>';
			$content .= get_the_modified_date( 'Y/m/d', $post_id ) . ' at ' . get_the_modified_time( '', $post_id );
			echo wp_kses( $content, [ 'br' => [] ] );
		}
	}

	/**
	 * Filters the Post list table sortable columns.
	 *
	 * @param array $columns An array of sortable columns.
	 *
	 * @return array
	 */
	public function add_sortable_column( $columns ) {
		if ( ! isset( $columns['modified'] ) ) {
			$columns['modified'] = 'modified';
		}
		return $columns;
	}

	/**
	 * Print out HTML form date elements for editing modified date.
	 *
	 * @param int  $tab_index The tabindex attribute to add. Default 0.
	 * @param bool $multi Whether the additional fields and buttons should be added. Default false.
	 *
	 * @return void
	 */
	protected function touch_modified_time( int $tab_index, bool $multi ) {
		global $wp_locale;
		$post = get_post();

		$edit = ! ( in_array( $post->post_status, [ 'draft', 'pending' ], true ) && ( ! $post->post_date_gmt || '0000-00-00 00:00:00' === $post->post_date_gmt ) );

		$tab_index_attribute = '';
		if ( $tab_index > 0 ) {
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		}

		$post_date = $post->post_modified;
		$jj        = ( $edit ) ? mysql2date( 'd', $post_date, false ) : current_time( 'd' );
		$mm        = ( $edit ) ? mysql2date( 'm', $post_date, false ) : current_time( 'm' );
		$aa        = ( $edit ) ? mysql2date( 'Y', $post_date, false ) : current_time( 'Y' );
		$hh        = ( $edit ) ? mysql2date( 'H', $post_date, false ) : current_time( 'H' );
		$mn        = ( $edit ) ? mysql2date( 'i', $post_date, false ) : current_time( 'i' );
		$ss        = ( $edit ) ? mysql2date( 's', $post_date, false ) : current_time( 's' );

		$cur_jj = current_time( 'd' );
		$cur_mm = current_time( 'm' );
		$cur_aa = current_time( 'Y' );
		$cur_hh = current_time( 'H' );
		$cur_mn = current_time( 'i' );

		$month = '<label><span class="screen-reader-text">' . __( 'Month', 'admin-optimizer' ) . '</span><select class="form-required" ' . ( $multi ? '' : 'id="mmm" ' ) . 'name="mmm"' . esc_attr( $tab_index_attribute ) . ">\n";
		for ( $i = 1; $i < 13; $i++ ) {
			$monthnum  = zeroise( $i, 2 );
			$monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
			$month    .= "\t\t\t" . '<option value="' . esc_attr( $monthnum ) . '" data-text="' . esc_attr( $monthtext ) . '" ' . selected( $monthnum, $mm, false ) . '>';
			/* translators: 1: Month number (01, 02, etc.), 2: Month abbreviation. */
			$month .= sprintf( __( '%1$s-%2$s', 'admin-optimizer' ), $monthnum, $monthtext ) . "</option>\n";
		}
		$month .= '</select></label>';

		$day    = '<label><span class="screen-reader-text">' . esc_html__( 'Day', 'admin-optimizer' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="mjj" ' ) . 'name="mjj" value="' . esc_attr( $jj ) . '" size="2" maxlength="2"' . esc_attr( $tab_index_attribute ) . ' autocomplete="off" class="form-required" /></label>';
		$year   = '<label><span class="screen-reader-text">' . esc_html__( 'Year', 'admin-optimizer' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="maa" ' ) . 'name="maa" value="' . esc_attr( $aa ) . '" size="4" maxlength="4"' . esc_attr( $tab_index_attribute ) . ' autocomplete="off" class="form-required" /></label>';
		$hour   = '<label><span class="screen-reader-text">' . esc_html__( 'Hour', 'admin-optimizer' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="mhh" ' ) . 'name="mhh" value="' . esc_attr( $hh ) . '" size="2" maxlength="2"' . esc_attr( $tab_index_attribute ) . ' autocomplete="off" class="form-required" /></label>';
		$minute = '<label><span class="screen-reader-text">' . esc_html__( 'Minute', 'admin-optimizer' ) . '</span><input type="text" ' . ( $multi ? '' : 'id="mmn" ' ) . 'name="mmn" value="' . esc_attr( $mn ) . '" size="2" maxlength="2"' . esc_attr( $tab_index_attribute ) . ' autocomplete="off" class="form-required" /></label>';

		echo '<div class="timestamp-wrap">';
		/* translators: 1: Month, 2: Day, 3: Year, 4: Hour, 5: Minute. */
		printf( __( '%1$s %2$s, %3$s at %4$s:%5$s', 'admin-optimizer' ), $month, $day, $year, $hour, $minute ); // phpcs:ignore

		echo '</div><input type="hidden" id="mss" name="mss" value="' . esc_attr( $ss ) . '" />';

		if ( $multi ) {
			return;
		}

		echo "\n\n";

		$map = array(
			'mmm' => array( $mm, $cur_mm ),
			'mjj' => array( $jj, $cur_jj ),
			'maa' => array( $aa, $cur_aa ),
			'mhh' => array( $hh, $cur_hh ),
			'mmn' => array( $mn, $cur_mn ),
		);

		foreach ( $map as $timeunit => $value ) {
			[ $unit, $curr ] = $value;

			echo '<input type="hidden" id="' . esc_attr( 'hidden_' . $timeunit ) . '" name="' . esc_attr( 'hidden_' . $timeunit ) . '" value="' . esc_attr( $unit ) . '" />' . "\n";
			$cur_timeunit = 'cur_' . $timeunit;
			echo '<input type="hidden" id="' . esc_attr( $cur_timeunit ) . '" name="' . esc_attr( $cur_timeunit ) . '" value="' . esc_attr( $curr ) . '" />' . "\n"; //phpcs:ignore
		}
		?>

		<p>
			<a href="#save_modified_timestamp" class="save-timestamp hide-if-no-js button">
				<?php esc_html_e( 'OK', 'admin-optimizer' ); ?></a>
			<a href="#cancel_modified_timestamp" class="cancel-timestamp hide-if-no-js button-cancel">
				<?php esc_html_e( 'Cancel', 'admin-optimizer' ); ?></a>
		</p>
		<?php
	}
}
