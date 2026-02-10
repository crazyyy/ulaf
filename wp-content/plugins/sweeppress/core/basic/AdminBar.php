<?php

namespace Dev4Press\Plugin\SweepPress\Basic;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AdminBar {
	public function __construct() {
		add_action( 'wp_before_admin_bar_render', array( $this, 'integration' ) );

		add_action( 'admin_head', array( $this, 'style' ) );
		add_action( 'wp_head', array( $this, 'style' ) );
	}

	public static function instance() : AdminBar {
		static $instance = null;

		if ( ! isset( $instance ) ) {
			$instance = new AdminBar();
		}

		return $instance;
	}

	public function style() {
		?>
        <style>@media screen and (max-width: 782px) {
                #wpadminbar li#wp-admin-bar-sweeppress-menu {
                    display: block;
                }
            }</style>
		<?php
	}

	public function integration() {
		if ( current_user_can( 'manage_options' ) ) {
			global $wp_admin_bar;

			$wp_admin_bar->add_menu( array(
				'id'    => 'sweeppress-menu',
				'title' => '<span style="margin-top: 2px" class="ab-icon dashicons dashicons-trash"></span><span class="ab-label">' . __( 'SweepPress', 'sweeppress' ) . '</span>',
				'href'  => admin_url( 'admin.php?page=sweeppress-dashboard' ),
			) );

			$items = sweeppress_settings()->get( 'admin_bar_quick_sweepers' );

			if ( sweeppress_settings()->get( 'admin_bar_show_quick' ) && ! empty( $items ) ) {
				$wp_admin_bar->add_menu( array(
					'parent' => 'sweeppress-menu',
					'id'     => 'sweeppress-menu-quick',
					'title'  => __( 'Quick Sweep', 'sweeppress' ),
				) );

				$full = sweeppress()->data_quick_sweepers();
				foreach ( $items as $item ) {
					if ( isset( $full[ $item ] ) ) {
						$wp_admin_bar->add_menu( array(
							'parent' => 'sweeppress-menu-quick',
							'id'     => 'sweeppress-menu-quick-' . $item,
							'title'  => $full[ $item ],
							'href'   => add_query_arg( array(
								'sweep-action' => 'quick-' . $item,
								'sweep-nonce'  => wp_create_nonce( 'sweeppress-action-quick-' . $item ),
							) ),
						) );
					}
				}
			}

			if ( sweeppress_settings()->get( 'admin_bar_show_auto' ) ) {
				$wp_admin_bar->add_menu( array(
					'parent' => 'sweeppress-menu',
					'id'     => 'sweeppress-menu-auto',
					'title'  => __( 'Auto Sweep', 'sweeppress' ),
					'href'   => add_query_arg( array(
						'sweep-action' => 'auto',
						'sweep-nonce'  => wp_create_nonce( 'sweeppress-action-auto' ),
					) ),
				) );
			}

			$wp_admin_bar->add_group( array(
				'parent' => 'sweeppress-menu',
				'id'     => 'sweeppress-menu-core',
			) );

			$wp_admin_bar->add_group( array(
				'parent' => 'sweeppress-menu',
				'id'     => 'sweeppress-menu-features',
			) );

			$wp_admin_bar->add_group( array(
				'parent' => 'sweeppress-menu',
				'id'     => 'sweeppress-menu-base',
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'sweeppress-menu-core',
				'id'     => 'sweeppress-menu-sweep',
				'title'  => __( 'Sweep', 'sweeppress' ),
				'href'   => admin_url( 'admin.php?page=sweeppress-sweep' ),
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'sweeppress-menu-core',
				'id'     => 'sweeppress-menu-list',
				'title'  => __( 'Sweepers List', 'sweeppress' ),
				'href'   => admin_url( 'admin.php?page=sweeppress-sweepers' ),
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'sweeppress-menu-features',
				'id'     => 'sweeppress-menu-database',
				'title'  => __( 'Database', 'sweeppress' ),
				'href'   => admin_url( 'admin.php?page=sweeppress-database' ),
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'sweeppress-menu-features',
				'id'     => 'sweeppress-menu-options',
				'title'  => __( 'Options', 'sweeppress' ),
				'href'   => admin_url( 'admin.php?page=sweeppress-options' ),
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'sweeppress-menu-features',
				'id'     => 'sweeppress-menu-metadata',
				'title'  => __( 'Metadata', 'sweeppress' ),
				'href'   => admin_url( 'admin.php?page=sweeppress-metadata' ),
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'sweeppress-menu-features',
				'id'     => 'sweeppress-menu-cron',
				'title'  => __( 'CRON Control', 'sweeppress' ),
				'href'   => admin_url( 'admin.php?page=sweeppress-crons' ),
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'sweeppress-menu-base',
				'id'     => 'sweeppress-menu-settings',
				'title'  => __( 'Settings', 'sweeppress' ),
				'href'   => admin_url( 'admin.php?page=sweeppress-settings' ),
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'sweeppress-menu-base',
				'id'     => 'sweeppress-menu-tools',
				'title'  => __( 'Tools', 'sweeppress' ),
				'href'   => admin_url( 'admin.php?page=sweeppress-tools' ),
			) );
		}
	}
}
