<?php

namespace BoltAudit\App\Providers;

use BoltAudit\WpMVC\App;
use BoltAudit\WpMVC\Contracts\Provider;

class MenuServiceProvider implements Provider {
	public function boot() {
		add_action( 'admin_menu', [$this, 'register_tools_submenu'] );
		add_filter( 'plugin_action_links_' . plugin_basename( App::get_dir( 'boltaudit.php' ) ), [$this, 'add_settings_link'] );
	}

	public function register_tools_submenu() {

		$config = App::$config->get( 'base-config' );
		$cap    = $config['menu_cap'];
		$slug   = $config['menu_slug'];

		if ( ! current_user_can( $cap ) ) {
			return;
		}

		add_submenu_page(
			'tools.php', // Parent slug (Tools menu)
			'BoltAudit', // Page title (appears in browser title bar)
			'BoltAudit', // Menu title (appears in the Tools menu)
			'manage_options', // Capability required
			$slug, // Menu slug
			[$this, 'render_admin_page']// Callback function
		);
	}

	public function render_admin_page() {

		$page_action = '';

		if ( isset( $_GET['action'] ) ) { //phpcs:ignore
			/** @psalm-suppress PossiblyInvalidArgument */// phpcs:ignore Generic.Commenting.DocComment.MissingShort
			$page_action = sanitize_text_field( wp_unslash( $_GET['action'] ) ); //phpcs:ignore
			/** @psalm-suppress PossiblyInvalidArgument */// phpcs:ignore Generic.Commenting.DocComment.MissingShort
			$page_action = str_replace( '_', '-', $page_action );
		}

		/** @psalm-suppress MissingFile */// phpcs:ignore Generic.Commenting.DocComment.MissingShort
		include_once boltaudit_dir( 'resources/views/admin/base.php' );
		/** @psalm-suppress MissingFile */// phpcs:ignore Generic.Commenting.DocComment.MissingShort
	}

	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'tools.php?page=boltaudit' ) ) . '">' . __( 'Start Audit', 'boltaudit' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}
}