<?php
new STM_SPLASH_Patcher();

class STM_SPLASH_Patcher {
	private static $current_layout = '';

	private static $updates = array(
		'1.0.1' => array(
			'copyright_url',
			'remove_stm_links',
		),
	);

	public function __construct() {
		self::$current_layout = get_option( 'stm_layout' );

		add_action( 'init', array( self::class, 'init_patcher' ), 100, 1 );
	}

	public static function init_patcher() {
		if ( version_compare( get_option( 'splash_extends_version', '1.0.0' ), STM_CONFIGURATIONS_PLUGIN_VERSION, '<' ) ) {
			self::update_version();
		}
	}

	public static function get_updates() {
		return self::$updates;
	}

	public static function needs_to_update() {
		$current_db_version = get_option( 'splash_extends_db_version' );
		$update_versions    = array_keys( self::get_updates() );
		usort( $update_versions, 'version_compare' );

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
	}

	private static function maybe_update_db_version() {
		if ( self::needs_to_update() ) {
			$current_db_version = get_option( 'splash_extends_db_version', '1.0.0' );
			$updates            = self::get_updates();

			foreach ( $updates as $version => $callback_arr ) {
				if ( version_compare( $current_db_version, $version, '<' ) ) {
					foreach ( $callback_arr as $callback ) {
						call_user_func( array( self::class, $callback ) );
					}
				}
			}
		}

		update_option( 'splash_extends_db_version', STM_CONFIGURATIONS_DB_VERSION, true );
	}

	public static function update_version() {
		update_option( 'splash_extends_version', STM_CONFIGURATIONS_PLUGIN_VERSION, true );
		self::maybe_update_db_version();
	}

	private static function copyright_url() {
		$footer_style = get_theme_mod( 'footer_style' );
		$patterns     = array(
			'Splash'                => '<a href="https://splash.stylemixthemes.com/" target="_blank">Splash</a> Theme for WordPress by <a href="https://stylemixthemes.com/" target="_blank">StylemixThemes</a>',
			'Stylemix Themes'       => '<a href="https://splash.stylemixthemes.com/" target="_blank">Splash</a> Theme for WordPress by <a href="https://stylemixthemes.com/" target="_blank">StylemixThemes</a>',
			'Sport WordPress Theme' => '<a href="https://splash.stylemixthemes.com/" target="_blank">Splash</a> Theme for WordPress by <a href="https://stylemixthemes.com/" target="_blank">StylemixThemes</a>',
		);

		foreach ( $patterns as $pattern_key => $pattern ) {
			if ( 'footer_style_three' === $footer_style && false !== strpos( html_entity_decode( wp_strip_all_tags( get_theme_mod( 'footer_center_text' ) ) ), $pattern_key ) ) {
				set_theme_mod( 'footer_center_text', $pattern );
			} elseif ( ( 'footer_style_one' === $footer_style || 'footer_style_two' === $footer_style ) && false !== strpos( html_entity_decode( wp_strip_all_tags( get_theme_mod( 'footer_left_text' ) ) ), $pattern_key ) ) {
				set_theme_mod( 'footer_left_text', $pattern );
				set_theme_mod( 'footer_right_text', '' );
			}
		}
	}

	private static function remove_stm_links() {
		$page_titles = array(
			'About Club'     => 'page',
			'Pricing table'  => 'page',
			'Sample Page'    => 'page',
			'Splash Stadium' => 'page',
			'Shortcodes'     => 'page',
			'Home page'      => 'page',
		);

		foreach ( $page_titles as $title => $post_type ) {
			self::update_content( $post_type, $title );
		}
	}

	private static function update_content( $post_type, $title ) {
		$searches     = array(
			'splash.stylemixthemes.com',
			'basketball.stylemixthemes.com',
			'healthcoach.stylemixthemes.com',
		);
		$args         = array(
			'post_type'   => $post_type,
			'title'       => $title,
			'post_status' => 'publish',
		);
		$page_object  = is_array( get_posts( $args ) ) ? get_posts( $args ) : null;

		if ( 1 < count( $page_object ) ) {
			foreach ( $page_object as $item ) {
				$item_content = $item->post_content;
				$item_id = $item->ID;
				foreach ( $searches as $search ) {
					if ( false !== strpos( $item_content, $search ) ) {
						$new_content = str_replace( $search, '/', $item_content );

						global $wpdb;

						$wpdb->update(
							$wpdb->posts,
							array(
								'post_content' => $new_content,
							),
							array(
								'ID' => $item_id,
							),
							array(
								'%s',
							),
							array(
								'%d',
							)
						);
					}
				}
			}
		} else {
			$page_content = $page_object->post_content ?? '';
			$page_id      = $page_object->ID ?? '';

			foreach ( $searches as $search ) {
				if ( false !== strpos( $page_content, $search ) ) {
					$new_content = str_replace( $search, '/', $page_content );

					global $wpdb;

					$wpdb->update(
						$wpdb->posts,
						array(
							'post_content' => $new_content,
						),
						array(
							'ID' => $page_id,
						),
						array(
							'%s',
						),
						array(
							'%d',
						)
					);
				}
			}
		}
	}
}
