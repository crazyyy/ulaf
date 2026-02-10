<?php
/**
 * General hooks
 *
 * @package AdminTweaks
 */

namespace ADTW;


class HooksGeneral {
	/**
	 * Check options and dispatch hooks
	 * 
	 * @param  array $options
	 * @return void
	 */
	public function __construct() 
    {
		# REMOVE/MODIFY WP VERSION
		if( ADTW()->getOption('wpdisable_version_full')
            || ADTW()->getOption('wpdisable_version_number') ) 
        {
			add_filter( 
                'the_generator', 
                [$this, 'remove_wp_version'] 
            );
        }
        
		# EMAIL VERIFY
		if( ADTW()->getOption('email_verification_disable') ) 
        {
			add_filter(
                'admin_email_check_interval',
                '__return_false'
            );

        }
        
		# REMOVE SUFFIX FROM ENQUEUED STYLES AND SCRIPTS
		if( ADTW()->getOption('wpdisable_scripts_versioning') ) {
            add_filter( 
                'style_loader_src', 
                [$this, 'remove_script_version'],
                10, 2
            );
            add_filter( 
                'script_loader_src', 
                [$this, 'remove_script_version'],
                10, 2
            );
        }

		# HIDE WP UPDATE NOTICE FOR NON-ADMINS
		if( ADTW()->getOption('wpblock_update_wp') ){
			add_action( 
                'admin_init', 
                [$this, 'hide_wp_update_nag'] 
            );
        }
		# HIDE WP UPDATE NOTICE
		if( ADTW()->getOption('wpblock_update_wp_all') ) {
			add_action( 
                'admin_init', 
                [$this, 'hide_wp_update_nag_all'] 
            );
        }

		# REDIRECT FROM UPDATED SCREEN
		if( ADTW()->getOption('wpblock_update_screen') ) {
			add_action( 
                'load-about.php', 
                [$this, 'redirect_update_screen'] 
            );
        }

		// ADMIN NOTICES
		if( ADTW()->getOption('wpseo_blog_public_enable') ) {
			add_action( 
                'admin_notices', 
                [$this, 'blogPublicNotice'], 
                1 
            );
        }

		# DISABLE SELF PING
		if( ADTW()->getOption('wpdisable_selfping') ) {
			add_action( 
                'pre_ping', 
                [$this, 'no_self_ping'] 
            );
        }

		# REDIRET HOME ON ACCESS DENIED
		if( ADTW()->getOption('wpdisable_redirect_disallow') ) {
			add_action( 
                'admin_page_access_denied',  
                [$this, 'access_denied'] 
            );
        }

		# ADD ID AND POST COUNT TO TAXONOMIES
		if( ADTW()->getOption('wptaxonomy_columns') ) {
			add_action( 
                'admin_init', 
                [$this, 'tax_ids_make'] 
            );
        }

		# DELAY RSS PUBLISH UPDATE
		if( ADTW()->getOption('wprss_delay_publish_enable') 
			&& ADTW()->getOption('wprss_delay_publish_time') ) 
        {
            add_filter( 
                'posts_where', 
                [$this, 'rss_delay_publish'] 
            );
        }
		

		# HIDE UPDATE BUBLE IN DASHBOARD MENU
		if( ADTW()->getOption('wpblock_update_wp_all')) {
			add_action( 
                'admin_menu', 
                [$this, 'hide_update_bubble'] 
            );
        }

		# REMOVE SMART QUOTES
		if( ADTW()->getOption('wpdisable_texturize_all') ) {
            add_filter( 
                'run_wptexturize', 
                '__return_false' 
            );
        }
		if( ADTW()->getOption('wpdisable_texturize_some') 
            and ADTW()->getOption('wpdisable_texturize_all') === false ) 
        {
            $all = ADTW()->getOption('wpdisable_texturize_some');
            foreach ($all as $tex) {
                remove_filter( $tex, 'wptexturize' );
            }
		}
        
		# REMOVE AUTO P
		if( ADTW()->getOption('wpdisable_autop') ) {
            $all = ADTW()->getOption('wpdisable_autop');
            foreach ($all as $autop) {
                remove_filter( $autop, 'wpautop' );
            }
        }

		# REMOVE WP FROM TITLE
		if( ADTW()->getOption('wpdisable_wptitle') ) {
			add_filter( 
                'admin_title', 
                [$this, 'remove_admin_title'], 
                10, 2 
            );
        }

		# DISABLE GUTENBERG
		if( ADTW()->getOption('wpdisable_gutenberg') ) {
			add_filter(
                'use_block_editor_for_post_type', 
                [$this, 'disable_gutenberg'], 
                10, 2
            );
        }

		# DISABLE DISTRACTION FREE
		if( ADTW()->getOption('wpdisable_distraction') ) {
			add_action(
                'enqueue_block_editor_assets', 
                [$this, 'disable_distraction']
            );
        }


        
	}


	/**
	 * Modify site generator
	 * 
	 * @return string
	 */
	public function remove_wp_version( $generated ) {
		if( ADTW()->getOption('wpdisable_version_full') )
			return '';
		elseif( ADTW()->getOption('wpdisable_version_number') )
			return '<meta name="generator" content="WordPress" />';

		return $generated;
	}


	/**
	 * Hide update bubble
	 * 
	 * @global string $submenu
	 */
	public function hide_update_bubble() {
		global $submenu; 
		if( isset( $submenu['index.php'][10] ) )
			$submenu['index.php'][10][0] = esc_html__('Updates');
	}


	/**
	 * Hide WordPress update notice for non-admins
	 * https://wordpress.stackexchange.com/a/13002/12615
	 * 
	 * @return void
	 */
	public function hide_wp_update_nag() {
		! current_user_can( 'install_plugins' ) 
			and remove_action( 'admin_notices', 'update_nag', 3 );
	}


	/**
	 * Hide WordPress update notice for everyone
	 */
	public function hide_wp_update_nag_all() {
		remove_action( 'admin_notices', 'update_nag', 3 );
	}


	/**
	 * Redirect from update screen
	 * 
	 * @return void
	 */
	public function redirect_update_screen() {
		if( !isset( $_GET['updated'] ) )
			return;

		wp_redirect( admin_url('update-core.php') );
		exit;
	}


	/**
	 * Disable phone home
	 * 
	 * @global type $wp_version
	 * @param type $default
	 * @return string
	 */
	public function phone_home_disable( $default ) {
		global $wp_version;
		return 'WordPress/' . $wp_version;
	}


	/**
	 * Change admin <title>
	 * https://wordpress.stackexchange.com/a/17034/12615
	 * 
	 * @param string $admin_title
	 * @param string $title
	 * @return strin
	 */
	public function remove_admin_title( $admin_title, $title ) {
		if ( is_network_admin() )
			$adm_title = esc_html__( 'Network Admin' );
		elseif ( is_user_admin() )
			$adm_title = esc_html__( 'Global Dashboard' );
		else
			$adm_title = get_bloginfo( 'name' );

		if ( $adm_title == $title )
			$adm_title = $title;
		else
			$adm_title = sprintf( 
				__( '%1$s &lsaquo; %2$s' ), 
				$title, 
				$adm_title 
			);

		return $adm_title;
	}

    
    /**
     * 
     *
     * @return void
     */
	public function blogPublicNotice() 
    {
		global $current_screen;
        $public = 1 == get_option( 'blog_public');
		if( $public || !in_array($current_screen->parent_base, ['options-general', 'plugins', 'tools']) ) 
            return;

        printf (
            '<div class="error"><b><em>%s</b></em><br><a href="%s">%s</a> || <a href="%s">%s</a></div>',
            esc_html__('Site search engine visibility is disabled.'),
            admin_url('options-reading.php'),
            esc_html__('Go to Settings.'),
            admin_url('options-general.php?page=admintweaks&tab=5'),
            esc_html__('Disable this warning.'),
        );
	}


    /**
	 * No self-ping
	 * 
	 * @param type $links
	 * @return void
	 */
	public function no_self_ping( &$links ) {
		$home = home_url();
		foreach( $links as $l => $link )
			if( 0 === strpos( $link, $home ) )
				unset( $links[$l] );
	}


	/**
	 * Modify RSS update period
	 * 
	 * @global object $wpdb
	 * @param string $where
	 * @return string
	 */
	public function rss_delay_publish( $where ) {
		global $wpdb;
		if( is_feed() ) {
			$now	 = gmdate( 'Y-m-d H:i:s' );
			$wait	 = ADTW()->getOption('wprss_delay_publish_time'); // integer
			// https://dev.mysql.com/doc/refman/5.0/en/date-and-time-public functions.html#public function_timestampdiff
			$device	 = ADTW()->getOption('wprss_delay_publish_period'); // MINUTE, HOUR, DAY, WEEK, MONTH, YEAR
			// add SQL-syntax to default $where
			$where .= " AND TIMESTAMPDIFF($device, $wpdb->posts.post_date_gmt, '$now') > $wait ";
		}
		return $where;
	}


	/**
	 * Redirect unauthorized access attempts
	 * 
	 * @return void
	 */
	public function access_denied() {
		wp_redirect( admin_url() );
		exit();
	}


	/**
	 * Add hook for taxonomy ID columns
	 */
	public function tax_ids_make() {
		foreach( get_taxonomies() as $taxonomy ) {
			add_action( "manage_edit-{$taxonomy}_columns", [$this, 't5_add_col'] );
			add_filter( "manage_edit-{$taxonomy}_sortable_columns", [$this, 't5_add_col'] );
			add_filter( "manage_{$taxonomy}_custom_column", [$this, 't5_show_id'], 10, 3 );
		}
		add_action( 'admin_print_styles-edit-tags.php', [$this, 't5_tax_id_style'] );
	}


	/**
	 * Register custom ID column
	 * @param type $columns
	 * @return type
     * 
     * @author toscho
	 */
	public function t5_add_col( $columns ) {
		$in = array( "tax_id" => "ID" );
		$columns = ADTW()->array_push_after( $columns, $in, 0 );
		return $columns;
	}


	/**
	 * Display custom ID/Post column
	 * 
	 * @global type $wp_list_table
	 * @param type $v
	 * @param type $name
	 * @param type $id
	 * @return type
     * 
     * @author toscho
	 */
	public function t5_show_id( $v, $name, $id ) {
		global $wp_list_table;
		return 'tax_id' === $name ? $id : $v;
	}


	/**
	 * Print taxonomy columns style
     * 
     * @author toscho
	 */
	public function t5_tax_id_style() {
		print '<style>#tax_id{width:4em}</style>';
	}


    /**
     * Remove versioning from enqueued scripts
     * 
     * @param string $url
     * @return string
     * 
     * @author toscho
     */
    public function remove_script_version( $src, $handle ) {
        return $src ? remove_query_arg( 'ver', $src ) : $src;
    }

    /**
     * Disable Gutenberg for Posts/Pages
     * 
     * @param boolean $current_status
     * @param array   $post_type
     * @return boolean
     * 
     */
    public function disable_gutenberg($current_status, $post_type) {
        if (in_array($post_type, ADTW()->getOption('wpdisable_gutenberg'))) return false;
        return $current_status;
    }

    public function disable_distraction() {
		global $post;
        if (!in_array($post->post_type, ADTW()->getOption('wpdisable_distraction'))) {
			return;
		}
        
		$script = "jQuery( window ).load( function() { 
			const ed = 'core/edit-post';
			const full = 'fullscreenMode';
			const fri = 'distractionFree';
			const isFullscreenMode = wp.data.select( ed ).isFeatureActive( full ); 
			if ( isFullscreenMode ) { 
				wp.data.dispatch( ed ).toggleFeature( full ); 
			} 
			const isDistractionFree = wp.data.select( ed ).isFeatureActive( fri ); 
			if ( isDistractionFree ) { 
				wp.data.dispatch( ed ).toggleFeature( fri ); 
			} 
			const getNotices = wp.data.select( 'core/notices' ).getNotices();
			getNotices.forEach( ( v, i )=>{
				if ( v.content.includes( 'Otter' ) ) wp.data.dispatch( 'core/notices' ).removeNotice(v.id);
			});
			if (wp.data) {
				wp.data.select( ed ).isFeatureActive( 'welcomeGuide' ) && wp.data.dispatch( ed ).toggleFeature( 'welcomeGuide' );
			}
		});";
		wp_add_inline_script( 'wp-blocks', $script );
	
    }


}
