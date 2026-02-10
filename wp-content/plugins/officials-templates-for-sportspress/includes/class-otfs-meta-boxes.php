<?php
/**
 * OTFS Officials Extra Meta Boxes
 *
 * @class       OTFS_Meta_Boxes
 * @version     1.0.0
 * @package     OTFS/Classes
 * @category    Class
 * @author      SavvasHa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * OTFS_Meta_Boxes.
 */
class OTFS_Meta_Boxes {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'sportspress_process_sp_official_meta', array( $this, 'save' ), 90, 2 );
	}

	/**
	 * Add Meta boxes.
	 */
	public function add_meta_boxes() {
		add_meta_box( 'sp_detailsdiv', __( 'Details', 'sportspress' ), array( $this, 'details' ), 'sp_official', 'side', 'default' );
		if ( 'manual' === get_option( 'otfs_officials_columns', 'auto' ) ) {
			add_meta_box( 'sp_columnsdiv', __( 'Columns', 'sportspress' ), array( $this, 'columns' ), 'sp_official', 'side', 'default' );
		}
		add_meta_box( 'sp_statisticssdiv', __( 'Statistics', 'sportspress' ), array( $this, 'statistics' ), 'sp_official', 'normal', 'default' );
	}

	/**
	 * Output the details metabox.
	 *
	 * @param object $post The current post object.
	 */
	public static function details( $post ) {
		// Add nonce field.
		wp_nonce_field( 'sportspress_save_data', 'sportspress_meta_nonce' );

		$continents          = SP()->countries->continents;
		$nationalities       = get_post_meta( $post->ID, 'sp_nationality', true );
		$default_nationality = get_option( 'sportspress_default_nationality', true );

		if ( '' === $nationalities ) {
			$nationalities = array();
		}

		if ( empty( $nationalities ) && $default_nationality ) {
			if ( '' !== $default_nationality ) {
				$nationalities[] = $default_nationality;
			}
		}

		foreach ( $nationalities as $index => $nationality ) :

			if ( is_string( $nationality ) && 2 === strlen( $nationality ) ) :
				$legacy                  = SP()->countries->legacy;
				$nationality             = strtolower( $nationality );
				$nationality             = sp_array_value( $legacy, $nationality, null );
				$nationalities[ $index ] = $nationality;
			endif;
		endforeach;

		if ( taxonomy_exists( 'sp_duty' ) ) :
			$duties   = get_the_terms( $post->ID, 'sp_duty' );
			$duty_ids = array();
			if ( $duties ) :
				foreach ( $duties as $duty ) :
					$duty_ids[] = $duty->term_id;
				endforeach;
			endif;
		endif;

		if ( taxonomy_exists( 'sp_league' ) ) :
			$leagues    = get_the_terms( $post->ID, 'sp_league' );
			$league_ids = array();
			if ( $leagues ) :
				foreach ( $leagues as $league ) :
					$league_ids[] = $league->term_id;
				endforeach;
			endif;
		endif;

		if ( taxonomy_exists( 'sp_season' ) ) :
			$seasons    = get_the_terms( $post->ID, 'sp_season' );
			$season_ids = array();
			if ( $seasons ) :
				foreach ( $seasons as $season ) :
					$season_ids[] = $season->term_id;
				endforeach;
			endif;
		endif;
		?>
		<p><strong><?php esc_attr_e( 'Nationality', 'sportspress' ); ?></strong></p>
		<p><select id="sp_nationality" name="sp_nationality[]" data-placeholder="
		<?php
		// Translators: This is the label for the Nationality dropdown.
		printf( esc_attr__( 'Select %s', 'sportspress' ), esc_attr__( 'Nationality', 'sportspress' ) );
		?>
		" class="widefat chosen-select
		<?php if ( is_rtl() ) { ?>
			chosen-rtl
			<?php } ?>
			" multiple="multiple">
			<option value=""></option>
			<?php foreach ( $continents as $continent => $countries ) : ?>
				<optgroup label="<?php echo esc_attr( $continent ); ?>">
					<?php foreach ( $countries as $code => $country ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( in_array( $code, $nationalities, true ) ); ?>><?php echo esc_html( $country ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endforeach; ?>
		</select></p>
		<?php if ( taxonomy_exists( 'sp_duty' ) ) { ?>
			<p><strong><?php esc_attr_e( 'Duties', 'sportspress' ); ?></strong></p>
			<p>
			<?php
			$args = array(
				'taxonomy'    => 'sp_duty',
				'name'        => 'tax_input[sp_duty][]',
				'selected'    => $duty_ids,
				'values'      => 'term_id',
				// Translators: This is the label for the Duties dropdown.
				'placeholder' => sprintf( esc_attr__( 'Select %s', 'sportspress' ), esc_attr__( 'Duties', 'sportspress' ) ),
				'class'       => 'widefat',
				'property'    => 'multiple',
				'chosen'      => true,
			);
			sp_dropdown_taxonomies( $args );
			?>
			</p>
		<?php } ?>
		<?php if ( taxonomy_exists( 'sp_league' ) ) { ?>
			<p><strong><?php esc_attr_e( 'Leagues', 'sportspress' ); ?></strong></p>
			<p>
				<?php
				$args = array(
					'taxonomy'    => 'sp_league',
					'name'        => 'tax_input[sp_league][]',
					'selected'    => $league_ids,
					'values'      => 'term_id',
					// Translators: This is the label for the Leagues dropdown.
					'placeholder' => sprintf( esc_attr__( 'Show all %s', 'sportspress' ), esc_attr__( 'Leagues', 'sportspress' ) ),
					'class'       => 'widefat',
					'property'    => 'multiple',
					'chosen'      => true,
				);
				sp_dropdown_taxonomies( $args );
				?>
			</p>
		<?php } ?>

		<?php if ( taxonomy_exists( 'sp_season' ) ) { ?>
			<p><strong><?php esc_attr_e( 'Seasons', 'sportspress' ); ?></strong></p>
			<p>
				<?php
				$args = array(
					'taxonomy'    => 'sp_season',
					'name'        => 'tax_input[sp_season][]',
					'selected'    => $season_ids,
					'values'      => 'term_id',
					// Translators: This is the label for the Seasons dropdown.
					'placeholder' => sprintf( esc_attr__( 'Show all %s', 'sportspress' ), esc_attr__( 'Seasons', 'sportspress' ) ),
					'class'       => 'widefat',
					'property'    => 'multiple',
					'chosen'      => true,
				);
				sp_dropdown_taxonomies( $args );
				?>
			</p>
			<?php do_action( 'otfs_meta_box_officials_details', $post ); ?>
			<?php
		}
	}

	/**
	 * Output the columns metabox.
	 *
	 * @param object $post The current post object.
	 */
	public static function columns( $post ) {
		// Add nonce field.
		wp_nonce_field( 'sportspress_save_data', 'sportspress_meta_nonce' );

		$selected = (array) get_post_meta( $post->ID, 'sp_columns', true );
		$tabs     = apply_filters( 'otfs_officials_column_tabs', array( 'sp_performance', 'sp_statistic' ) );
		?>
		<div class="sp-instance">
			<?php if ( $tabs ) { ?>
			<ul id="sp_column-tabs" class="sp-tab-bar category-tabs">
				<?php
				foreach ( $tabs as $index => $post_type ) {
					$object = get_post_type_object( $post_type );
					?>
				<li class="
					<?php
					if ( 0 === $index ) {
						?>
					tabs<?php } ?>"><a href="#<?php echo esc_attr( $post_type ); ?>-all"><?php echo esc_html( $object->labels->menu_name ); ?></a></li>
				<?php } ?>
			</ul>
				<?php
				foreach ( $tabs as $index => $post_type ) {
					sp_column_checklist( $post->ID, $post_type, ( 0 === $index ? 'block' : 'none' ), $selected );
				}
				?>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Output the statistics metabox.
	 *
	 * @param object $post The current post object.
	 */
	public static function statistics( $post ) {
		// Add nonce field.
		wp_nonce_field( 'sportspress_save_data', 'sportspress_meta_nonce' );

		$official = new OTFS_Officials( $post );
		$leagues  = $official->get_terms_sorted_by_sp_order( 'sp_league' );
		// If no Leagues are selected by the user, then all Leagues should be included.
		if ( is_array( $leagues ) ) {
			$league_num = count( $leagues );
		} else {
			$league_num = 0;
			$leagues    = get_terms(
				array(
					'taxonomy'   => 'sp_league',
					'hide_empty' => true,
				)
			);
			usort( $leagues, 'sp_sort_terms' );
		}

		$show_per_league    = 'yes' === get_option( 'otfs_officials_show_per_league', 'yes' ) ? true : false;
		$show_career_totals = 'yes' === get_option( 'otfs_officials_show_career_total', 'no' ) ? true : false;
		$selected_duties    = $official->filtered_duties();

		if ( $leagues ) {
			if ( $show_per_league ) {
				// Loop through statistics for each league.
				$i = 0;
				foreach ( $leagues as $league ) :
					if ( empty( $selected_duties ) ) {
						?>
						<p><strong><?php echo esc_html( $league->name ); ?></strong></p>
						<?php
						list( $columns, $data, $placeholders, $merged, $seasons_teams, $has_checkboxes, $formats, $total_types ) = $official->stats( $league->term_id, -1, true );
						self::table( $post->ID, $league->term_id, $columns, $data, $placeholders, $merged, $seasons_teams, $has_checkboxes && 0 === $i, false, $formats, $total_types );
						++$i;
					} else {
						foreach ( $selected_duties as $selected_duty ) {
							$duty = get_term_by( 'id', $selected_duty, 'sp_duty' );
							?>
							<p><strong><?php echo esc_html( $league->name . ' ' . esc_attr__( 'as', 'officials-templates-for-sportspress' ) . ' ' . $duty->name ); ?></strong></p>
							<?php
							list( $columns, $data, $placeholders, $merged, $seasons_teams, $has_checkboxes, $formats, $total_types ) = $official->stats( $league->term_id, -1, true );
							self::table( $post->ID, $league->term_id, $columns, $data, $placeholders, $merged, $seasons_teams, $has_checkboxes && 0 === $i, false, $formats, $total_types );
							++$i;
						}
					}
				endforeach;
			}
			if ( $show_career_totals ) {
				if ( empty( $selected_duties ) ) {
					?>
					<p><strong><?php esc_attr_e( 'Career Total', 'sportspress' ); ?></strong></p>
					<?php
					list( $columns, $data, $placeholders, $merged, $seasons_teams, $has_checkboxes, $formats, $total_types ) = $official->stats( 0, -1, true );
					self::table( $post->ID, 0, $columns, $data, $placeholders, $merged, $seasons_teams, false, false, $formats, $total_types );
				} else {
					foreach ( $selected_duties as $selected_duty ) {
						$duty = get_term_by( 'id', $selected_duty, 'sp_duty' );
						?>
						<p><strong><?php echo esc_attr__( 'Career Total', 'sportspress' ) . ' ' . esc_attr__( 'as', 'officials-templates-for-sportspress' ) . ' ' . esc_html( $duty->name ); ?></strong></p>
						<?php
						list( $columns, $data, $placeholders, $merged, $seasons_teams, $has_checkboxes, $formats, $total_types ) = $official->stats( 0, -1, true );
						self::table( $post->ID, 0, $columns, $data, $placeholders, $merged, $seasons_teams, false, false, $formats, $total_types );
					}
				}
			}
		}
	}

	/**
	 * Admin edit table.
	 *
	 * @param mixed $id             The identifier.
	 * @param mixed $league_id      The league identifier.
	 * @param array $columns        An array defining the columns of the table.
	 * @param array $data           An array containing the data for the table.
	 * @param array $placeholders   An array containing placeholders for empty values.
	 * @param array $merged         An array that is used for merging data.
	 * @param array $leagues        An array of leagues.
	 * @param bool  $has_checkboxes Boolean flag indicating whether checkboxes are present.
	 * @param bool  $team_select    Boolean flag indicating whether team selection options are present.
	 * @param array $formats        An array defining the format of each column (e.g., 'time' or 'number').
	 * @param array $total_types    An array defining total types for each column.
	 */
	public static function table( $id = null, $league_id = null, $columns = array(), $data = array(), $placeholders = array(), $merged = array(), $leagues = array(), $has_checkboxes = false, $team_select = false, $formats = array(), $total_types = array() ) {
		// Add nonce field.
		wp_nonce_field( 'sportspress_save_data', 'sportspress_meta_nonce' );

		$readonly = false;
		$buffer   = apply_filters(
			'otfs_meta_box_officials_statistics_table_buffer',
			array(
				'readonly' => $readonly,
			),
			$id
		);
		?>
		<div class="sp-data-table-container">
			<table class="widefat sp-data-table sp-player-statistics-table">
				<thead>
					<tr>
						<th><?php esc_attr_e( 'Season', 'sportspress' ); ?></th>
						<?php
						foreach ( $columns as $key => $label ) :
							if ( 'team' === $key ) {
								continue;
							}
							?>
							<th><?php echo wp_kses_post( $label ); ?></th>
						<?php endforeach; ?>
						<?php do_action( 'otfs_meta_box_officials_statistics_table_header_row', $id, $league_id ); ?>
					</tr>
				</thead>
				<tfoot>
					<?php $div_stats = sp_array_value( $data, 0, array() ); ?>
					<tr class="sp-row sp-total">
						<td>
							<label><strong><?php esc_attr_e( 'Total', 'sportspress' ); ?></strong></label>
						</td>
						<?php
						foreach ( $columns as $column => $label ) :
							if ( 'team' === $column ) {
								continue;}
							?>
							<td>
							<?php
								$value       = sp_array_value( sp_array_value( $data, 0, array() ), $column, null );
								$placeholder = sp_array_value( sp_array_value( $placeholders, 0, array() ), $column, 0 );

								// Convert value and placeholder to time format.
							if ( 'time' === sp_array_value( $formats, $column, 'number' ) ) {
								$timeval     = sp_time_value( $value );
								$placeholder = sp_time_value( $placeholder );
							}

							if ( $readonly ) {
								echo $value ? esc_html( $value ) : esc_html( $placeholder );
							} elseif ( 'time' === sp_array_value( $formats, $column, 'number' ) ) {
									echo '<input class="sp-convert-time-input" type="text" name="sp_times[' . esc_attr( $league_id ) . '][0][' . esc_attr( $column ) . ']" value="' . ( '' === $value ? '' : esc_attr( $timeval ) ) . '" placeholder="' . esc_attr( $placeholder ) . '"' . ( $readonly ? ' disabled="disabled"' : '' ) . '  />';
									echo '<input class="sp-convert-time-output" type="hidden" name="sp_statistics[' . esc_attr( $league_id ) . '][0][' . esc_attr( $column ) . ']" value="' . esc_attr( $value ) . '" data-sp-format="' . esc_attr( sp_array_value( $formats, $column, 'number' ) ) . '" data-sp-total-type="' . esc_attr( sp_array_value( $total_types, $column, 'total' ) ) . '" />';
							} else {
								echo '<input type="text" name="sp_statistics[' . esc_attr( $league_id ) . '][0][' . esc_attr( $column ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '"' . ( $readonly ? ' disabled="disabled"' : '' ) . ' data-sp-format="' . esc_attr( sp_array_value( $formats, $column, 'number' ) ) . '" data-sp-total-type="' . esc_attr( sp_array_value( $total_types, $column, 'total' ) ) . '" />';
							}
							?>
							</td>
						<?php endforeach; ?>
						<?php do_action( 'otfs_meta_box_officials_statistics_table_footer_row', $id, $league_id ); ?>
					</tr>
				</tfoot>
				<tbody>
					<?php
					$i = 0;
					foreach ( $data as $div_id => $div_stats ) :
						if ( 'statistics' === $div_id ) {
							continue;
						}
						if ( 0 === $div_id ) {
							continue;
						}
						$div = get_term( $div_id, 'sp_season' );
						?>
						<tr class="sp-row sp-post
						<?php
						if ( 0 === $i % 2 ) {
							echo ' alternate';}
						?>
						<?php echo esc_attr( implode( ' ', apply_filters( 'otfs_meta_box_officials_statistics_row_classes', array(), $league_id, $div_id ) ) ); ?>" data-league="<?php echo (int) $league_id; ?>" data-season="<?php echo (int) $div_id; ?>">
							<td>
								<label>
									<?php
									if ( 0 === $div_id ) {
										esc_attr_e( 'Total', 'sportspress' );
									} elseif ( 'WP_Error' !== get_class( $div ) ) {
										$allowed_html = array(
											'input' => array(
												'type'     => array(),
												'class'    => array(),
												'name'     => array(),
												'value'    => array(),
												'size'     => array(),
												'placeholder' => array(),
												'id'       => array(),
												'readonly' => array(),
											),
										);
										echo wp_kses( apply_filters( 'otfs_meta_box_officials_statistics_season_name', $div->name, $league_id, $div_id, $div_stats ), $allowed_html );
									}
									?>
								</label>
							</td>
							<?php
							$collection = array(
								'columns'        => $columns,
								'data'           => $data,
								'placeholders'   => $placeholders,
								'merged'         => $merged,
								'seasons_teams'  => array(),
								'has_checkboxes' => $has_checkboxes,
								'formats'        => $formats,
								'total_types'    => $total_types,
								'buffer'         => $buffer,
							);
							list( $columns, $data, $placeholders, $merged, $seasons_teams, $has_checkboxes, $formats, $total_types, $buffer ) = array_values( apply_filters( 'otfs_meta_box_officials_statistics_collection', $collection, $id, $league_id, $div_id ) );
							?>
							<?php
							foreach ( $columns as $column => $label ) :
								if ( 'team' === $column ) {
									continue;}
								?>
								<td>
								<?php
									$value       = sp_array_value( sp_array_value( $data, $div_id, array() ), $column, null );
									$placeholder = sp_array_value( sp_array_value( $placeholders, $div_id, array() ), $column, 0 );

									// Convert value and placeholder to time format.
								if ( 'time' === sp_array_value( $formats, $column, 'number' ) ) {
									$timeval     = sp_time_value( $value );
									$placeholder = sp_time_value( $placeholder );
								}

								if ( $readonly ) {
									echo $timeval ? esc_html( $timeval ) : esc_html( $placeholder );
								} elseif ( 'time' === sp_array_value( $formats, $column, 'number' ) ) {
										echo '<input class="sp-convert-time-input" type="text" name="sp_times[' . esc_attr( $league_id ) . '][' . esc_attr( $div_id ) . '][' . esc_attr( $column ) . ']" value="' . ( '' === $value ? '' : esc_attr( $timeval ) ) . '" placeholder="' . esc_attr( $placeholder ) . '"' . ( $readonly ? ' disabled="disabled"' : '' ) . ' data-column="' . esc_attr( $column ) . '" />';
										echo '<input class="sp-convert-time-output" type="hidden" name="sp_statistics[' . esc_attr( $league_id ) . '][' . esc_attr( $div_id ) . '][' . esc_attr( $column ) . ']" value="' . esc_attr( $value ) . '" />';
								} else {
									echo '<input type="text" name="sp_statistics[' . esc_attr( $league_id ) . '][' . esc_attr( $div_id ) . '][' . esc_attr( $column ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '"' . ( $readonly ? ' disabled="disabled"' : '' ) . ' data-column="' . esc_attr( $column ) . '" />';
								}
								?>
								</td>
							<?php endforeach; ?>
							<?php do_action( 'otfs_meta_box_officials_statistics_table_row', $id, $league_id, $div_id, $team_select, $buffer, $i ); ?>
						</tr>
						<?php
						++$i;
						do_action( 'otfs_meta_box_officials_statistics_table_after_row', $id, $league_id, $div_id, $team_select, $buffer, $i );
					endforeach;
					do_action( 'otfs_meta_box_officials_statistics_table_tbody', $id, $league_id, $div_id, $team_select, $buffer );
					?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Save meta boxes data.
	 *
	 * @param int    $post_id The post ID.
	 * @param object $post The post object.
	 */
	public static function save( $post_id, $post ) {
		// Check nonce.
		if ( empty( $_POST['sportspress_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['sportspress_meta_nonce'] ), 'sportspress_save_data' ) ) {
			return;
		}
		if ( isset( $_POST['sp_nationality'] ) ) {
			update_post_meta( $post_id, 'sp_nationality', array_map( 'sanitize_key', $_POST['sp_nationality'] ) );
		}
		if ( isset( $_POST['sp_statistics'] ) ) {
			update_post_meta( $post_id, 'sp_statistics', map_deep( wp_unslash( $_POST['sp_statistics'] ), 'wp_kses_post' ) );
		}
		if ( isset( $_POST['sp_columns'] ) ) {
			update_post_meta( $post_id, 'sp_columns', array_map( 'sanitize_key', $_POST['sp_columns'] ) );
		}
		do_action( 'otfs_meta_box_officials_save', $post );
	}
}
if ( get_option( 'sportspress_load_officials_module', 'no' ) === 'yes' ) {
	new OTFS_Meta_Boxes();
}
