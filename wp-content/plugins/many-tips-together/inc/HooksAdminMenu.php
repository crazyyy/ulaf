<?php
/**
 * Admin Menu hooks
 *
 * @package AdminTweaks
 */

namespace ADTW;

class HooksAdminMenu {
	/**
	 * Check options and dispatch hooks
	 * 
	 * @param  array $options
	 * @return void
	 */
	public function __construct() {

        # RE-ADD CUSTOMIZE.PHP
		if( ADTW()->getOption('admin_menus_readd_customize') ) {
            add_action( 'admin_menu', function () {
                    if ( !ADTW()->customize_menu_exists() ) {
                        add_theme_page( __('Customize'), __('Customize'), 'edit_pages', 'customize.php');
                    }
                }, 999999999);
        }

        # ENABLE LINK MANAGER
		if( ADTW()->getOption('admin_menus_enable_link_manager') ) {
			add_filter( 
                'pre_option_link_manager_enabled', 
                '__return_true' 
            );
        }

		# REMOVE ITEMS
		if( ADTW()->getOption('admin_menus_remove') ) {
			add_action( 
                'admin_menu', 
                [$this, 'removeItems'], 
                99990
            );
        }

		# REMOVE SUBITEMS
		if( ADTW()->getOption('admin_submenus_remove') ) {
			add_action( 
                'admin_menu', 
                [$this, 'removeSubItems'], 
                99990
            );
        }
        add_action(
            'load-settings_page_admintweaks', 
            function(){
                add_filter(
                    'esc_html',
                    [$this, 'escHtml'],
                    10, 2
                );
            }
        ); 

		# SORT SETTINGS
		if( ADTW()->getOption('admin_menus_sort_wordpress')
			|| ADTW()->getOption('admin_menus_sort_plugins' ) ) 
        {
			add_action( 
                'admin_menu', 
                [$this, 'sortSettings'], 
                15 
            );
        }

		# BUBBLES
		if( ADTW()->getOption('admin_menus_bubbles') 
            && ADTW()->getOption('admin_menus_bubbles_cpts') 
            && ADTW()->getOption('admin_menus_bubbles_status') ) 
        {
            add_action( 
                'admin_menu', 
                [$this, 'addBubbles'] 
            );
        }

		# RENAME POSTS
		if( ADTW()->getOption('posts_rename_enable') ) {
			add_action( 
                'init', 
                [$this, 'objectLabel'],
                0 
            );
			add_action( 
                'admin_menu', 
                [$this, 'menuLabel'],
                0 
            );
		}

	}

    public function escHtml( $safe_text, $text ) {
        if ( $text && strpos($text, 'dontscape') !== false ) {
            return $text;
        }
        return $safe_text;
    }

	/**
	 * Remove menu items
	 */
	public function removeItems() {
        $items = array_keys(ADTW()->getMenus());
        $remove = ADTW()->getOption('admin_menus_remove');
        foreach( $remove as $key ) {
            if ( isset( $items[$key] ) ) remove_menu_page( $items[$key] );
        }
	}

	/**
	 * Remove submenu items
	 */
	public function removeSubItems() {
        $remove = ADTW()->getOption('admin_submenus_remove');
        foreach( $remove as $key ) {
            $key = str_replace('____', '=', $key);
            $key = str_replace('___', '?', $key);
            $key = str_replace('_php', '.php', $key);
            $toRemove = explode('__', $key);
            if ( count($toRemove) == 2 ) {
                remove_submenu_page($toRemove[0], $toRemove[1]);
            }
        }
	}

	/**
	 * Sort items in Settings menu
	 * - WordPress and Plugins are dealed separatedly
	 * https://wordpress.stackexchange.com/q/2331/12615
	 * 
	 */
	public function sortSettings() {
		global $submenu;

		if( !isset( $submenu['options-general.php'] ) )
			return;

		// Sort default items
		$default = array_slice( $submenu['options-general.php'], 0, 7, true );
		if( ADTW()->getOption('admin_menus_sort_wordpress') ) {
			usort( $default, [$this, '_sortArrayASC'] );
        }

		// Sort rest of items
		$length = count( $submenu['options-general.php'] );
		$extra = array_slice( $submenu['options-general.php'], 7, $length, true );

		if( ADTW()->getOption('admin_menus_sort_plugins') ) {
			usort( $extra, [$this, '_sortArrayASC'] );
        }
		// Apply
		$sep = array( array( '<b style="opacity:.3;">. . . . . . . . . . . . .</b>',  'manage_options', '#'));
		$submenu['options-general.php'] = array_merge( $default, $sep, $extra );
	}
	
    
	public function addBubbles() 
    {
		global $menu;
		$bubles = ADTW()->getOption('admin_menus_bubbles_cpts');
        $status = ADTW()->getOption('admin_menus_bubbles_status');
		foreach( $bubles as $pt ) {
			$cpt_count = wp_count_posts( $pt );

			if( isset( $cpt_count->$status ) ) 
            {
				$suffix = ( 'post' == $pt ) ? '' : "?post_type=$pt";
				$key = ADTW()->recursiveArraySearch( "edit.php$suffix", $menu );

				if( !$key )
					return;

				$menu[$key][0] .= sprintf(
						'<span class="update-plugins count-%1$s">
						<span class="plugin-count">%1$s</span>
					</span>', $cpt_count->$status
				);
			}
		}
	}
	


	/**
	 * Sort array by sub-value
	 * https://stackoverflow.com/a/1597788/1287812
	 * 
	 */
	private function _sortArrayASC( $item1, $item2 ) {
		if ($item1[0] == $item2[0]) return 0;
		return ( $item1[0] > $item2[0] ) ? 1 : -1;
	}


	/**
	 * Rename "Posts" in the global scope
	 * 
	 * @global type $wp_post_types
	 */
	public function objectLabel() {
		global $wp_post_types;

		$labels = &$wp_post_types['post']->labels;

		if ( ADTW()->getOption('posts_rename_name') ) {
			$labels->name = ADTW()->getOption('posts_rename_name');
        }
	}


	/**
	 * Rename "Posts" in the Admin Menu
	 * 
	 * @global type $menu
	 * @global type $submenu
	 */
	public function menuLabel() {
		global $menu, $submenu;

		if ( ADTW()->getOption('posts_rename_name') ) {
			$menu[5][0] = ADTW()->getOption('posts_rename_name');
			$submenu['edit.php'][5][0]  = ADTW()->getOption('posts_rename_name');
        }
	}
	
}