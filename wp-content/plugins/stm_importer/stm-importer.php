<?php
/*
Plugin Name: STM Importer
Plugin URI: http://stylemixthemes.com/
Description: STM Importer
Author: StylemixThemes
Author URI: http://stylemixthemes.com/
Text Domain: stm_importer
Version: 4.3.6
*/


define( 'STM_IMPORTER', 'stm-post-type' );
define( 'STM_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
define( 'STM_IMPORTER_PATH', dirname( __FILE__ ) );
define( 'STM_IMPORTER_VERSION', '4.3.6' );

// Demo Import - Styles
function stm_demo_import_styles() {
	wp_enqueue_style( 'stm-demo-import-style', STM_IMPORTER_URL . '/assets/css/style.css', null, STM_IMPORTER_VERSION, 'all' );
}

add_action( 'admin_enqueue_scripts', 'stm_demo_import_styles' );

// After import hook and add menu, home page. slider, blog page
if ( ! function_exists( 'splash_importer_done_function' ) ) {
	function splash_importer_done_function() {
		global $wp_filesystem;
		$layoutName = getThemeSettings();

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		/*Widgets*/

		switch ( $layoutName['layoutName'] ) {
			case 'af':
				$lName = 'americanfootball';
				break;
			case 'sccr':
				$lName = 'soccer';
				break;
			case 'baseball':
				$lName = 'baseball';
				break;
			case 'magazine_one':
				$lName = 'magazine_one';
				break;
			case 'magazine_two':
				$lName = 'magazine_two';
				break;
			case 'soccer_two':
				$lName = 'soccer_two';
				break;
			case 'soccer_news':
				$lName = 'soccer_news';
				break;
			case 'basketball_two':
				$lName = 'basketball_two';
				break;
			case 'hockey':
				$lName = 'hockey';
				break;
			case 'esport':
				$lName = 'esport';
				break;
			case 'volleyball':
				$lName = 'volleyball';
				break;
			case 'rugby':
				$lName = 'rugby';
				break;
			default:
				$lName = 'basketball';
		}

		delete_option( 'sidebars_widgets' );

		$widgets_file = STM_IMPORTER_PATH . '/demo/' . $lName . '/widget_data.json';

		if ( file_exists( $widgets_file ) ) {
			$encode_widgets_array = $wp_filesystem->get_contents( $widgets_file );
			splash_import_widgets( $encode_widgets_array );
		}

		$locations = get_theme_mod( 'nav_menu_locations' );
		$menus     = wp_get_nav_menus();

		if ( ! empty( $menus ) ) {
			foreach ( $menus as $menu ) {
				if ( is_object( $menu ) ) {
					switch ( $menu->name ) {
						case 'Header menu':
							$locations['primary'] = $menu->term_id;
							function stm_import_megamenu_fields() {
								$splash_config = getThemeSettings();

								$menu   = wp_get_nav_menu_items( 'Header menu' );
								$layout = $splash_config['layoutName'];
								$config = splash_layout_megamenu( $layout );

								foreach ( $menu as $menu_item ) {
									if ( ! empty( $config[ $menu_item->title ] ) ) {
										$id       = $menu_item->ID;
										$configer = $config[ $menu_item->title ];
										foreach ( $configer as $meta_key => $meta_value ) {
											if ( 'stm_menu_image' === $meta_key ) {
												$page = get_page_by_title( 'placeholder' );
												update_post_meta( $id, '_menu_item_' . $meta_key, $page->ID );
											} else {
												update_post_meta( $id, '_menu_item_' . $meta_key, $meta_value );
											}
										}
									}
								}
							}

							stm_import_megamenu_fields( $menu->term_id );

							break;
						case 'Widget menu':
							$locations['bottom_menu'] = $menu->term_id;
							break;
						case 'Sidebar menu':
							$locations['sidebar_menu'] = $menu->term_id;
							break;
					}
				}
			}
		}

		set_theme_mod( 'nav_menu_locations', $locations );

		update_option( 'show_on_front', 'page' );

		$front_page = get_page_by_title( 'Home page' );
		if ( isset( $front_page->ID ) ) {
			update_option( 'page_on_front', $front_page->ID );
		}

		$blog_page = get_page_by_title( 'News' );
		if ( isset( $blog_page->ID ) ) {
			update_option( 'page_for_posts', $blog_page->ID );
		}

		$shop_page = ( ! splash_is_layout( 'sccr' ) ) ? get_page_by_title( 'Shop' ) : get_page_by_title( 'Official Splash Shop' );
		if ( isset( $shop_page->ID ) ) {
			update_option( 'woocommerce_shop_page_id', $shop_page->ID );
		}

		$checkout_page = get_page_by_title( 'Checkout' );
		if ( isset( $checkout_page->ID ) ) {
			update_option( 'woocommerce_checkout_page_id', $checkout_page->ID );
		}
		$cart_page = get_page_by_title( 'Cart' );
		if ( isset( $cart_page->ID ) ) {
			update_option( 'woocommerce_cart_page_id', $cart_page->ID );
		}
		$account_page = get_page_by_title( 'My Account' );
		if ( isset( $account_page->ID ) ) {
			update_option( 'woocommerce_myaccount_page_id', $account_page->ID );
		}

		update_option( 'sportspress_player_show_selector', 'no' );

		if ( splash_is_layout( 'bb' ) ) {
			SP_Admin_Sports::apply_preset( 'basketball' );
			update_option( 'sportspress_sport', 'basketball' );
			wp_delete_post( 1, true );

		} elseif ( splash_is_layout( 'af' ) ) {
			SP_Admin_Sports::apply_preset( 'football' );
			update_option( 'sportspress_sport', 'football' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_team_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/americanfootball/af_theme_options.json';

			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/af/';

				$import_theme_mods['logo']                = $templateDemoUrl . 'logo.png';
				$import_theme_mods['footer_image']        = $templateDemoUrl . 'banner_footer.jpg';
				$import_theme_mods['top_bar_ticket_icon'] = $templateDemoUrl . 'get-tickets.svg';
				$import_theme_mods['footer_logo']         = $templateDemoUrl . 'logo_footer_splash.png';
				$import_theme_mods['header_background']   = $templateDemoUrl . 'header-bg.jpg';
				$import_theme_mods['bg_img']              = $templateDemoUrl . 'bg-error-404.jpg';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
			}

			wp_delete_post( 1, true );
		} elseif ( splash_is_layout( 'basketball_two' ) ) {
			SP_Admin_Sports::apply_preset( 'football' );
			update_option( 'sportspress_sport', 'football' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_team_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/basketball_two/basketball_two_theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/basketball_two/';

				$import_theme_mods['logo']                = $templateDemoUrl . 'logo.png';
				$import_theme_mods['footer_image']        = $templateDemoUrl . 'placeholder.gif';
				$import_theme_mods['top_bar_ticket_icon'] = $templateDemoUrl . 'get-tickets.svg';
				$import_theme_mods['footer_logo']         = $templateDemoUrl . 'logo_footer_splash.png';
				$import_theme_mods['header_background']   = $templateDemoUrl . 'placeholder.gif';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
			}

			wp_delete_post( 1, true );
		} elseif ( splash_is_layout( 'hockey' ) ) {
			SP_Admin_Sports::apply_preset( 'ice-hockey' );
			update_option( 'sportspress_sport', 'ice-hockey' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );
			update_option( 'sportspress_team_page_template', 'sportspress-nosidebar.php' );
			update_option( 'sportspress_player_columns', 'manual' );
			$single_team_blocks_order = array(
				'logo',
				'excerpt',
				'content',
				'link',
				'details',
				'lists',
				'staff',
				'tables',
				'events',
				'tabs',
			);
			update_option( 'sportspress_team_template_order', $single_team_blocks_order );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/hockey/hockey_theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/hockey/';

				$import_theme_mods['logo'] = $templateDemoUrl . 'logo.png';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
			}

			wp_delete_post( 1, true );
		} elseif ( splash_is_layout( 'soccer_news' ) ) {

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/soccer_news/soccer_news_theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods                      = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods                      = json_decode( $encode_theme_mods, true );
				$templateDemoUrl                        = get_template_directory_uri() . '/assets/images/tmp/soccer_news/';
				$import_theme_mods['header_background'] = $templateDemoUrl . 'top-bg.jpg';
				$import_theme_mods['logo']              = $templateDemoUrl . 'logo.svg';
				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
			}

			wp_delete_post( 1, true );
		} elseif ( splash_is_layout( 'sccr' ) ) {

			SP_Admin_Sports::apply_preset( 'soccer' );
			update_option( 'sportspress_sport', 'soccer' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_team_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/soccer/sccr_theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/sccr/';

				$import_theme_mods['logo'] = $templateDemoUrl . 'logo.svg';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
			}

			wp_delete_post( 1, true );
		} elseif ( splash_is_layout( 'soccer_two' ) ) {

			SP_Admin_Sports::apply_preset( 'soccer' );
			update_option( 'sportspress_sport', 'soccer' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_team_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/soccer_two/soccer_two_theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/soccer_two/';

				$import_theme_mods['logo']                    = $templateDemoUrl . 'logo.png';
				$import_theme_mods['header_background']       = $templateDemoUrl . 'top_bg.jpg';
				$import_theme_mods['footer_background_image'] = $templateDemoUrl . 'footer_bg.jpg';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
			}

			wp_delete_post( 1, true );
		} elseif ( splash_is_layout( 'baseball' ) ) {

			set_theme_mod( 'site_style_base_color', '#81b441' );
			set_theme_mod( 'site_style_secondary_color', '#d19e3b' );

			SP_Admin_Sports::apply_preset( 'baseball' );
			update_option( 'sportspress_sport', 'baseball' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_team_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/baseball/baseball_theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/baseball/';

				$import_theme_mods['logo']        = $templateDemoUrl . 'logo.svg';
				$import_theme_mods['sticky_logo'] = $templateDemoUrl . 'logo_two.svg';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
			}

			wp_delete_post( 1, true );

		} elseif ( splash_is_layout( 'magazine_one' ) ) {
			SP_Admin_Sports::apply_preset( 'basketball' );
			update_option( 'sportspress_sport', 'basketball' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_team_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/magazine_one/magazine_one_theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/magazine_one/';

				$import_theme_mods['logo']              = $templateDemoUrl . 'logo.svg';
				$import_theme_mods['header_background'] = $templateDemoUrl . 'top_bg.jpg';
				$import_theme_mods['custom_bg_image']   = $templateDemoUrl . 'placeholder.gif';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
			}

			$cats = array(
				array(
					'cat_id' => 16,
					'color'  => 'ffaa00',
				),
				array(
					'cat_id' => 17,
					'color'  => '00bfe6',
				),
				array(
					'cat_id' => 18,
					'color'  => '00e573',
				),
			);

			foreach ( $cats as $cat ) {
				update_term_meta( $cat['cat_id'], '_category_color', $cat['color'] );
			}

			wp_delete_post( 1, true );
		} elseif ( splash_is_layout( 'magazine_two' ) ) {
			SP_Admin_Sports::apply_preset( 'basketball' );
			update_option( 'sportspress_sport', 'basketball' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_team_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/magazine_two/magazine_two_theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/magazine_two/';

				$import_theme_mods['logo']              = $templateDemoUrl . 'logo.svg';
				$import_theme_mods['header_background'] = $templateDemoUrl . 'top_bg.jpg';
				$import_theme_mods['custom_bg_image']   = $templateDemoUrl . 'placeholder.gif';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
			}

			$cats = array(
				array(
					'cat_id' => 16,
					'color'  => 'ffaa00',
				),
				array(
					'cat_id' => 17,
					'color'  => '00bfe6',
				),
				array(
					'cat_id' => 18,
					'color'  => '00e573',
				),
			);

			foreach ( $cats as $cat ) {
				update_term_meta( $cat['cat_id'], '_category_color', $cat['color'] );
			}

			wp_delete_post( 1, true );
		} elseif ( splash_is_layout( 'esport' ) ) {
			SP_Admin_Sports::apply_preset( 'csgo' );
			update_option( 'sportspress_sport', 'csgo' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_team_page_template', 'sportspress-nosidebar.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/esport/theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/esport/';

				$import_theme_mods['logo']            = $templateDemoUrl . 'logo.png';
				$import_theme_mods['custom_bg_image'] = $templateDemoUrl . 'placeholder.gif';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
				splash_skin_custom();
			}

			// Custom Twitter Feeds
			$show_hide_list   = array(
				'include_retweeter'         => 0,
				'include_avatar'            => 0,
				'include_author'            => 0,
				'include_logo'              => true, //enable
				'include_text'              => true, //enable
				'include_media_placeholder' => 0,
				'include_date'              => 0,
				'include_actions'           => 0,
				'include_twitterlink'       => 0,
				'include_linkbox'           => 0,
				'showheader'                => 0,
				'showbutton'                => 0,
			);
			$existing_options = get_option( 'ctf_options' );
			if ( ! empty( $existing_options ) ) {
				foreach ( $show_hide_list as $key => $value ) {
					if ( isset( $existing_options[ $key ] ) ) {
						$existing_options[ $key ] = $value;
					};
				}
				update_option( 'ctf_options', $existing_options );
			} else {
				update_option( 'ctf_options', $show_hide_list );
			}

			wp_delete_post( 1, true );
		} elseif ( splash_is_layout( 'volleyball' ) ) {
			SP_Admin_Sports::apply_preset( 'volleyball' );
			update_option( 'sportspress_sport', 'volleyball' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_team_page_template', 'sportspress-nosidebar.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/volleyball/theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/volleyball/';

				$import_theme_mods['logo']            = $templateDemoUrl . 'logo.svg';
				$import_theme_mods['custom_bg_image'] = $templateDemoUrl . 'placeholder.gif';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
				splash_skin_custom();
			}

			wp_delete_post( 1, true );
		} elseif ( splash_is_layout( 'rugby' ) ) {
			SP_Admin_Sports::apply_preset( 'rugby' );
			update_option( 'sportspress_sport', 'rugby' );
			update_option( 'sportspress_event_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_calendar_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_team_page_template', 'sportspress-nosidebar.php' );
			update_option( 'sportspress_table_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_player_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_list_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_staff_page_template', 'sportpress-sidebar-right.php' );
			update_option( 'sportspress_event_list_show_logos', 'yes' );

			$theme_mods_file = STM_IMPORTER_PATH . '/demo/rugby/theme_options.json';
			if ( file_exists( $theme_mods_file ) ) {
				$encode_theme_mods = $wp_filesystem->get_contents( $theme_mods_file );
				$import_theme_mods = json_decode( $encode_theme_mods, true );
				$templateDemoUrl   = get_template_directory_uri() . '/assets/images/tmp/rugby/';

				$import_theme_mods['logo']            = $templateDemoUrl . 'logo.svg';
				$import_theme_mods['custom_bg_image'] = $templateDemoUrl . 'placeholder.gif';

				foreach ( $import_theme_mods as $key => $value ) {
					set_theme_mod( $key, $value );
				}
				splash_skin_custom();
			}

			wp_delete_post( 1, true );
		}

		if ( class_exists( 'RevSlider' ) ) {

			$main_slider = STM_IMPORTER_PATH . '/demo/' . $lName . '/home_slider.zip';

			if ( splash_is_layout( 'sccr' ) ) {
				$shop_slider = STM_IMPORTER_PATH . '/demo/soccer/shop.zip';
			}

			if ( file_exists( $main_slider ) ) {
				$slider = new RevSlider();
				$slider->importSliderFromPost( true, true, $main_slider );
			}

			if ( file_exists( $shop_slider ) ) {
				$slider = new RevSlider();
				$slider->importSliderFromPost( true, true, $shop_slider );
			}
		}

		if ( function_exists( 'splash_RegenerateThumbnails' ) ) {
			splash_RegenerateThumbnails();
		}

		if ( splash_is_layout( 'baseball' ) ) {
			set_theme_mod( 'site_style', 'site_style_custom' );
			splash_skin_custom();
		}
	}
}

add_action( 'splash_importer_done', 'splash_importer_done_function' );

if ( ! function_exists( 'stm_demo_import' ) ) {
	function stm_demo_import() {
		$current_layout = get_option( 'splash_layout', '' );
		?>
		<div class="stm_message content" style="display:none;">
			<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/spinner.gif" alt="spinner">
			<h1 class="stm_message_title"><?php esc_html_e( 'Importing Demo Content...', 'splash' ); ?></h1>
			<p class="stm_message_text"><?php esc_html_e( 'Demo content import duration relies on your server speed.', 'splash' ); ?></p>
		</div>

		<div class="stm_message success" style="display:none;">
			<p class="stm_message_text">
				<?php
				echo wp_kses(
					sprintf( __( 'Congratulations and enjoy <a href="%s" target="_blank">your website</a> now!', 'splash' ), esc_url( home_url() ) ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
						),
					)
				);
				?>
			</p>
		</div>

		<form class="stm_importer" id="import_demo_data_form" action="?page=stm_demo_import" method="post">

			<div class="stm_importer_options">

				<div class="stm_importer_note">
					<strong><?php esc_html_e( 'Before installing the demo content, please NOTE:', 'splash' ); ?></strong>
					<p>
						<?php
						echo wp_kses(
							sprintf( __( 'Install the demo content only on a clean WordPress. Use <a href="%s" target="_blank">WordPress Database Reset</a> plugin to clean the current Theme.', 'splash' ), 'http://wordpress.org/plugins/wordpress-database-reset/', esc_url( home_url() ) ),
							array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
								),
							)
						);
						?>
					</p>
					<p><?php esc_html_e( 'Remember that you will NOT get the images from live demo due to copyright / license reason.', 'splash' ); ?></p>
				</div>
				<div class="stm_demo_import_choices">
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/rugby.jpg"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="rugby" <?php checked( $current_layout, 'rugby', true ); ?> />
							<?php esc_html_e( 'Rugby', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/volleyball.jpg"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="volleyball" <?php checked( $current_layout, 'volleyball', true ); ?> />
							<?php esc_html_e( 'Volleyball', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/demo-3.png"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="soccer" <?php checked( $current_layout, 'soccer', true ); ?> />
							<?php esc_html_e( 'Soccer', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/basketball_two.jpg"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="basketball_two" <?php checked( $current_layout, 'basketball_two', true ); ?> />
							<?php esc_html_e( 'Basketball Two', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/hockey.jpg"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="hockey" <?php checked( $current_layout, 'hockey', true ); ?> />
							<?php esc_html_e( 'Hockey', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/demo-1.png"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="basketball" <?php checked( $current_layout, 'basketball', true ); ?> />
							<?php esc_html_e( 'Basketball', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/demo-2.png"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="americanfootball" <?php checked( $current_layout, 'americanfootball', true ); ?> />
							<?php esc_html_e( 'American Football', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/demo-4.png"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="baseball" <?php checked( $current_layout, 'baseball', true ); ?> />
							<?php esc_html_e( 'Baseball', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/demo-5.png"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="magazine_one" <?php checked( $current_layout, 'magazine_one', true ); ?> />
							<?php esc_html_e( 'Magazine One', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/demo-6.png"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="magazine_two" <?php checked( $current_layout, 'magazine_two', true ); ?> />
							<?php esc_html_e( 'Football Magazine', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/demo-7.png"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="soccer_two" <?php checked( $current_layout, 'soccer_two', true ); ?> />
							<?php esc_html_e( 'Soccer Two', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/demo-8.png"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="soccer_news" <?php checked( $current_layout, 'soccer_news', true ); ?> />
							<?php esc_html_e( 'Soccer Club News', 'stm-importer' ); ?>
						</span>
					</label>
					<label>
						<img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>assets/images/demo/demo-9.jpg"/>
						<span class="stm_choice_radio_button">
							<input type="radio" name="splash_layout_demo" value="esport" <?php checked( $current_layout, 'esport', true ); ?> />
							<?php esc_html_e( 'eSport', 'stm-importer' ); ?>
						</span>
					</label>
				</div>
				<input class="button-primary size_big" type="submit" value="Import" id="import_demo_data">
			</div>

		</form>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				jQuery('#import_demo_data_form').on('submit', function () {
					var layout = jQuery(this).find("input[name='splash_layout_demo']:checked").val();

					jQuery("html, body").animate({
						scrollTop: 0
					}, {
					duration: 300
					});
					jQuery('.stm_importer').slideUp(null, function () {
						jQuery('.stm_message.content').slideDown();
					});

					// Importing Content
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
						data: jQuery(this).serialize() + '&action=stm_demo_import_content&security=' + stm_demo_import_content,
						success: function () {
							jQuery('.stm_message.content').slideUp();
							jQuery('.stm_message.success').slideDown();
							jQuery.ajax({
								url: 'https://panel.stylemixthemes.com/api/active/',
								type: 'post',
								dataType: 'json',
								data: {
									theme: 'splash',
									layout: layout,
									website: "<?php echo esc_url( get_site_url() ); ?>",

									<?php
									$envato = get_option( 'envato_market', array() );
									$token  = ( ! empty( $envato['token'] ) ) ? $envato['token'] : '';
									?>
									token: "<?php echo esc_js( $token ); ?>"
								}
							});
						}
					});
					return false;
				});
			});
		</script>
		<?php
	}

	// Content Import
	function stm_demo_import_content() {
		check_ajax_referer( 'stm_demo_import_content', 'security' );
		$splash_layout = 'basketball';
		if ( ! empty( $_POST['splash_layout_demo'] ) ) {
			$splash_layout = sanitize_text_field( $_POST['splash_layout_demo'] );
		}

		update_option( 'splash_layout', $splash_layout );

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS', true );
		}

		if ( 'basketball' === $splash_layout ) {
			update_option(
				'shop_catalog_image_size',
				array(
					'width'  => 570,
					'height' => 350,
				)
			);
			update_option(
				'shop_single_image_size',
				array(
					'width'  => 440,
					'height' => 450,
				)
			);
			update_option(
				'shop_thumbnail_image_size',
				array(
					'width'  => 100,
					'height' => 89,
				)
			);

			add_image_size( 'shop_thumbnail', 100, 89, true );
			add_image_size( 'shop_catalog', 570, 350, true );
			add_image_size( 'shop_single', 440, 450, true );
		} elseif ( 'soccer' === $splash_layout ) {
			update_option(
				'shop_catalog_image_size',
				array(
					'width'  => 350,
					'height' => 350,
				)
			);
			update_option(
				'shop_single_image_size',
				array(
					'width'  => 345,
					'height' => 405,
				)
			);
			update_option(
				'shop_thumbnail_image_size',
				array(
					'width'  => 110,
					'height' => 110,
				)
			);

			add_image_size( 'shop_thumbnail', 110, 110, true );
			add_image_size( 'shop_catalog', 350, 350, true );
			add_image_size( 'shop_single', 345, 405, true );
		} elseif ( 'baseball' === $splash_layout ) {
			update_option(
				'shop_catalog_image_size',
				array(
					'width'  => 300,
					'height' => 300,
				)
			);
			update_option(
				'shop_single_image_size',
				array(
					'width'  => 440,
					'height' => 440,
				)
			);
			update_option(
				'shop_thumbnail_image_size',
				array(
					'width'  => 440,
					'height' => 440,
				)
			);

			add_image_size( 'shop_thumbnail', 440, 440, true );
			add_image_size( 'shop_catalog', 350, 350, true );
			add_image_size( 'shop_single', 440, 440, true );
		} else {
			update_option(
				'shop_catalog_image_size',
				array(
					'width'  => 570,
					'height' => 350,
				)
			);
			update_option(
				'shop_single_image_size',
				array(
					'width'  => 358,
					'height' => 488,
				)
			);
			update_option(
				'shop_thumbnail_image_size',
				array(
					'width'  => 70,
					'height' => 90,
				)
			);

			add_image_size( 'shop_thumbnail', 70, 90, true );
			add_image_size( 'shop_catalog', 570, 350, true );
			add_image_size( 'shop_single', 358, 488, true );
		}

		require_once 'wordpress-importer/wordpress-importer.php';

		$wp_import                    = new WP_Import();
		$wp_import->fetch_attachments = true;

		ob_start();
		$wp_import->import( STM_IMPORTER_PATH . '/demo/' . $splash_layout . '/demo_content.xml' );
		ob_end_clean();

		set_transient( 'processed_posts', $wp_import->processed_posts, 1 * HOUR_IN_SECONDS );
		set_transient( 'processed_terms', $wp_import->processed_terms, 1 * HOUR_IN_SECONDS );

		do_action( 'splash_importer_done' );

		echo 'done';
		die();

	}

	add_action( 'wp_ajax_stm_demo_import_content', 'stm_demo_import_content' );

}
