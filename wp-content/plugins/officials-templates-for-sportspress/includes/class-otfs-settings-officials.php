<?php
/**
 * OTFS Officials Settings
 *
 * @class       OTFS_Settings_Officials
 * @version     1.5.0
 * @package     OTFS/Classes
 * @category    Class
 * @author      SavvasHa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'OTFS_Settings_Officials' ) ) :

	/**
	 * OTFS_Settings_Officials
	 */
	class OTFS_Settings_Officials extends SP_Settings_Page {

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id       = 'officials';
			$this->label    = __( 'Officials', 'sportspress' );
			$this->template = 'officials';

			add_filter( 'sportspress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'sportspress_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'sportspress_admin_field_officials_layout', array( $this, 'layout_setting' ) );
			add_action( 'sportspress_admin_field_officials_tabs', array( $this, 'tabs_setting' ) );
			add_action( 'sportspress_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings array
		 *
		 * @return array
		 */
		public function get_settings() {
			$templates = $this->templates();
			$templates = apply_filters( 'sportspress_' . $this->template . '_templates', $templates );
			$duties    = get_terms(
				array(
					'taxonomy'   => 'sp_duty',
					'hide_empty' => false,
				)
			);

			$otfs_duties = array(); // Prevent error when the sp_duty taxonomy returns no results.
			foreach ( $duties as $duty ) {
				$otfs_duties[ $duty->term_id ] = $duty->name;
			}

			return apply_filters(
				'otfs_officials_settings',
				array_merge(
					array(
						array(
							'title' => esc_attr__( 'Officials Options', 'sportspress' ),
							'type'  => 'title',
							'desc'  => '',
							'id'    => 'officials_options',
						),
					),
					apply_filters(
						'sportspress_post_type_options',
						array(
							array( 'type' => 'officials_layout' ),

							array( 'type' => 'officials_tabs' ),
							array(
								'title'   => esc_attr__( 'Link', 'sportspress' ),
								'desc'    => esc_attr__( 'Link officials', 'sportspress' ),
								'id'      => 'sportspress_link_officials',
								'default' => 'yes',
								'type'    => 'checkbox',
							),

							array(
								'title'         => esc_attr__( 'Details', 'sportspress' ),
								'desc'          => esc_attr__( 'Name', 'sportspress' ),
								'id'            => 'otfs_officials_show_name',
								'default'       => 'no',
								'type'          => 'checkbox',
								'checkboxgroup' => 'start',
							),

							array(
								'desc'          => esc_attr__( 'Nationality', 'sportspress' ),
								'id'            => 'otfs_officials_show_nationality',
								'default'       => 'yes',
								'type'          => 'checkbox',
								'checkboxgroup' => '',
							),

							array(
								'desc'          => esc_attr__( 'Duties', 'sportspress' ),
								'id'            => 'otfs_officials_show_duties',
								'default'       => 'yes',
								'type'          => 'checkbox',
								'checkboxgroup' => 'end',
							),
							array(
								'title'         => esc_attr__( 'Birthday', 'sportspress' ),
								'desc'          => esc_attr__( 'Display birthday', 'sportspress' ),
								'id'            => 'otfs_officials_show_birthday',
								'default'       => 'no',
								'type'          => 'checkbox',
								'checkboxgroup' => 'start',
							),
							array(
								'desc'          => esc_attr__( 'Display age', 'sportspress' ),
								'id'            => 'otfs_officials_show_age',
								'default'       => 'no',
								'type'          => 'checkbox',
								'checkboxgroup' => 'end',
							),
							array(
								'title'   => esc_attr__( 'Events', 'sportspress' ),
								'id'      => 'otfs_officials_events_format',
								'default' => 'title',
								'type'    => 'select',
								'options' => array(
									'blocks' => esc_attr__( 'Blocks', 'sportspress' ),
									'list'   => esc_attr__( 'List', 'sportspress' ),
								),
							),
						),
						'officials'
					),
					array(
						array(
							'type' => 'sectionend',
							'id'   => 'officials_options',
						),
					),
					array(
						array(
							'title' => esc_attr__( 'Statistics', 'sportspress' ),
							'type'  => 'title',
							'desc'  => '',
							'id'    => 'officials_statistic_options',
						),

						array(
							'title'   => esc_attr__( 'Mode', 'sportspress' ),
							'id'      => 'otfs_officials_statistics_mode',
							'default' => 'values',
							'type'    => 'radio',
							'options' => array(
								'values' => esc_attr__( 'Values', 'sportspress' ),
								'icons'  => esc_attr__( 'Icons', 'sportspress' ),
							),
						),

						array(
							'title'   => esc_attr__( 'Columns', 'sportspress' ),
							'id'      => 'otfs_officials_columns',
							'default' => 'auto',
							'type'    => 'radio',
							'options' => array(
								'auto'   => esc_attr__( 'Auto', 'sportspress' ),
								'manual' => esc_attr__( 'Manual', 'sportspress' ),
							),
						),

						array(
							'title'   => esc_attr__( 'Duties', 'sportspress' ),
							'id'      => 'otfs_officials_duties',
							'type'    => 'multiselect',
							'options' => $otfs_duties,
						),

						array(
							'title'         => esc_attr__( 'Display', 'sportspress' ),
							'desc'          => esc_attr__( 'Per League', 'sportspress' ),
							'id'            => 'otfs_officials_show_per_league',
							'default'       => 'yes',
							'type'          => 'checkbox',
							'checkboxgroup' => 'start',
						),

						array(
							'desc'          => esc_attr__( 'Total', 'sportspress' ),
							'id'            => 'otfs_officials_show_total',
							'default'       => 'no',
							'type'          => 'checkbox',
							'checkboxgroup' => '',
						),

						array(
							'desc'          => esc_attr__( 'Career Total', 'sportspress' ),
							'id'            => 'otfs_officials_show_career_total',
							'default'       => 'no',
							'type'          => 'checkbox',
							'checkboxgroup' => 'end',
						),
					),
					apply_filters(
						'otfs_officials_statistic_options',
						array()
					),
					array(
						array(
							'type' => 'sectionend',
							'id'   => 'officials_statistic_options',
						),
					)
				)
			); // End officials settings.
		}
	}

endif;
if ( get_option( 'sportspress_load_officials_module', 'no' ) === 'yes' ) {
	return new OTFS_Settings_Officials();
}
return;
