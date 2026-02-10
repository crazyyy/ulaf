<?php
/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 *
 * @package daext-interlinks-manager
 */

/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 */
class Daextinma_Shared {

	/**
	 * The singleton instance of the class.
	 *
	 * @var Daextinma_Shared
	 */
	protected static $instance = null;

	/**
	 * The data of the plugin.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor.
	 */
	private function __construct() {

		$this->data['slug'] = 'daextinma';
		$this->data['ver']  = '1.16';
		$this->data['dir']  = substr( plugin_dir_path( __FILE__ ), 0, -7 );
		$this->data['url']  = substr( plugin_dir_url( __FILE__ ), 0, -7 );

		// Here are stored the plugin option with the related default values.
		$this->data['options'] = array(

			// Database version. (not available in the options UI).
			$this->get( 'slug' ) . '_database_version'     => '0',

			// Options version. (not available in the options UI).
			$this->get( 'slug' ) . '_options_version'     => '0',

			/**
			 * Internal links data and juice data last update. Used for the automatic data update in the Dashboard and
			 * Juice menus. (not available in the options UI)
			 */
			$this->get( 'slug' ) . '_internal_links_data_last_update' => '',
			$this->get( 'slug' ) . '_juice_data_last_update' => '',

			// Juice.
			$this->get( 'slug' ) . '_default_seo_power'    => 1000,
			$this->get( 'slug' ) . '_penality_per_position_percentage' => '1',
			$this->get( 'slug' ) . '_remove_link_to_anchor' => '1',
			$this->get( 'slug' ) . '_remove_url_parameters' => '0',

			// Technical Options.
			$this->get( 'slug' ) . '_set_max_execution_time' => '1',
			$this->get( 'slug' ) . '_max_execution_time_value' => '300',
			$this->get( 'slug' ) . '_set_memory_limit'     => '0',
			$this->get( 'slug' ) . '_memory_limit_value'   => '512',
			$this->get( 'slug' ) . '_limit_posts_analysis' => '1000',
			$this->get( 'slug' ) . '_dashboard_post_types' => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_juice_post_types'     => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_internal_links_data_update_frequency' => 'hourly',
			$this->get( 'slug' ) . '_juice_data_update_frequency' => 'hourly',

			// Advanced -----------------------------------------------------------------------------------------------.

			// Optimization Parameters.
			$this->get( 'slug' ) . '_optimization_num_of_characters' => 1000,
			$this->get( 'slug' ) . '_optimization_delta'   => 2,

			// Meta boxes.
			$this->get( 'slug' ) . '_interlinks_options_post_types' => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_interlinks_optimization_post_types' => array( 'post', 'page' ),

		);
	}

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return Daextrevo_Shared|self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Retrieve data.
	 *
	 * @param string $index The index of the data to retrieve.
	 *
	 * @return mixed
	 */
	public function get( $index ) {
		return $this->data[ $index ];
	}

	/**
	 * Get the number of manual interlinks in a given string
	 *
	 * @param string $text The string in which the search should be performed.
	 * @return int The number of internal links in the string
	 */
	public function get_manual_interlinks( $text ) {

		// Remove the HTML comments.
		$text = $this->remove_html_comments( $text );

		// Remove script tags.
		$text = $this->remove_script_tags( $text );

		// Working regex.
		$num_matches = preg_match_all(
			$this->internal_links_regex(),
			$text,
			$matches
		);

		return $num_matches;
	}

	/**
	 * Get the raw post_content of the specified post.
	 *
	 * @param int $post_id The ID of the post.
	 * @return string The raw post content.
	 */
	public function get_raw_post_content( $post_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$post_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT post_content FROM {$wpdb->prefix}posts WHERE ID = %d", $post_id )
		);

		return $post_obj->post_content;
	}

	/**
	 * The optimization is calculated based on:
	 * - the "Optimization Delta" option
	 * - the number of interlinks
	 * - the content length
	 * True is returned if the content is optimized, False if it's not optimized.
	 *
	 * @param int $number_of_interlinks The overall number of interlinks ( manual interlinks + auto interlinks ).
	 * @param int $content_length The content length.
	 * @return bool True if is optimized, False if is not optimized
	 */
	public function calculate_optimization( $number_of_interlinks, $content_length ) {

		// get the values of the options.
		$optimization_num_of_characters = (int) get_option( $this->get( 'slug' ) . '_optimization_num_of_characters' );
		$optimization_delta             = (int) get_option( $this->get( 'slug' ) . '_optimization_delta' );

		// Determines if this post is optimized.
		$optimal_number_of_interlinks = (int) $content_length / $optimization_num_of_characters;
		if (
			( $number_of_interlinks >= ( $optimal_number_of_interlinks - $optimization_delta ) ) &&
			( $number_of_interlinks <= ( $optimal_number_of_interlinks + $optimization_delta ) )
		) {
			$is_optimized = true;
		} else {
			$is_optimized = false;
		}

		return $is_optimized;
	}

	/**
	 * The optimal number of interlinks is calculated by dividing the content
	 * length for the value in the "Characters per Interlink" option and
	 * converting the result to an integer.
	 *
	 * @param int $number_of_interlinks The overall number of interlinks ( manual interlinks + auto interlinks ).
	 * @param int $content_length The content length.
	 * @return int The number of recommended interlinks
	 */
	public function calculate_recommended_interlinks( $number_of_interlinks, $content_length ) {

		// Get the values of the options.
		$optimization_num_of_characters = get_option( $this->get( 'slug' ) . '_optimization_num_of_characters' );
		$optimization_delta             = get_option( $this->get( 'slug' ) . '_optimization_delta' );

		// Determines the optimal number of interlinks.
		$optimal_number_of_interlinks = $content_length / $optimization_num_of_characters;

		return intval( $optimal_number_of_interlinks, 10 );
	}

	/**
	 * The minimum number of interlinks suggestion is calculated by subtracting
	 * half of the optimization delta from the optimal number of interlinks.
	 *
	 * @param int $post_id The post id.
	 * @return int The minimum number of interlinks suggestion
	 */
	public function get_suggested_min_number_of_interlinks( $post_id ) {

		// Get the content length of the raw post.
		$content_length = mb_strlen( $this->get_raw_post_content( $post_id ) );

		// Get the values of the options.
		$optimization_num_of_characters = intval( get_option( $this->get( 'slug' ) . '_optimization_num_of_characters' ), 10 );
		$optimization_delta             = intval( get_option( $this->get( 'slug' ) . '_optimization_delta' ), 10 );

		// Determines the optimal number of interlinks.
		$optimal_number_of_interlinks = $content_length / $optimization_num_of_characters;

		// Get the minimum number of interlinks.
		$min_number_of_interlinks = intval( ( $optimal_number_of_interlinks - ( $optimization_delta / 2 ) ), 10 );

		// Set to zero negative values.
		if ( $min_number_of_interlinks < 0 ) {
			$min_number_of_interlinks = 0; }

		return $min_number_of_interlinks;
	}

	/**
	 * The maximum number of interlinks suggestion is calculated by adding
	 * half of the optimization delta to the optimal number of interlinks.
	 *
	 * @param int $post_id The post id.
	 * @return int The maximum number of interlinks suggestion.
	 */
	public function get_suggested_max_number_of_interlinks( $post_id ) {

		// Get the content length of the raw post.
		$content_length = mb_strlen( $this->get_raw_post_content( $post_id ) );

		// Get the values of the options.
		$optimization_num_of_characters = get_option( $this->get( 'slug' ) . '_optimization_num_of_characters' );
		$optimization_delta             = get_option( $this->get( 'slug' ) . '_optimization_delta' );

		// Determines the optimal number of interlinks.
		$optimal_number_of_interlinks = $content_length / $optimization_num_of_characters;

		return intval( ( $optimal_number_of_interlinks + ( $optimization_delta / 2 ) ), 10 );
	}

	/**
	 * Calculate the link juice of a links based on the given parameters.
	 *
	 * @param string $post_content_with_autolinks The post content (with autolinks applied).
	 * @param int    $post_id The post id.
	 * @param int    $link_position The position of the link in the string (the line where the link string starts).
	 * @return int The link juice of the link.
	 */
	public function calculate_link_juice( $post_content_with_autolinks, $post_id, $link_position ) {

		// Get the SEO power of the post.
		$seo_power = get_post_meta( $post_id, '_daextinma_seo_power', true );
		if ( 0 === strlen( trim( $seo_power ) ) ) {
			$seo_power = (int) get_option( $this->get( 'slug' ) . '_default_seo_power' );}

		/**
		 * Divide the SEO power for the total number of links ( all the links,
		 * external and internal are considered ).
		 */
		$juice_per_link = $seo_power / $this->get_number_of_links( $post_content_with_autolinks );

		/**
		 * Calculate the index of the link on the post ( example 1 for the first
		 * link or 3 for the third link )
		 * A regular expression that counts the links on a string that starts
		 * from the beginning of the post and ends at the $link_position is used
		 */
		$post_content_before_the_link = substr( $post_content_with_autolinks, 0, $link_position );
		$number_of_links_before       = $this->get_number_of_links( $post_content_before_the_link );

		/**
		 * Remove a percentage of the $juice_value based on the number of links
		 * before this one.
		 */
		$penality_per_position_percentage = (int) get_option( $this->get( 'slug' ) . '_penality_per_position_percentage' );
		$link_juice                       = $juice_per_link - ( ( $juice_per_link / 100 * $penality_per_position_percentage ) * $number_of_links_before );

		// Return the link juice or 0 if the calculated link juice is negative.
		if ( $link_juice < 0 ) {
			$link_juice = 0;}
		return $link_juice;
	}

	/**
	 * Get the total number of links ( any kind of link: internal, external,
	 * nofollow, dofollow ) available in the provided string.
	 *
	 * @param string $s The string on which the number of links should be counted.
	 * @return int The number of links found on the string
	 */
	public function get_number_of_links( $s ) {

		// Remove the HTML comments.
		$s = $this->remove_html_comments( $s );

		// Remove script tags.
		$s = $this->remove_script_tags( $s );

		$num_matches = preg_match_all(
			$this->links_regex(),
			$s,
			$matches
		);

		return $num_matches;
	}

	/**
	 * Given a link returns it with the anchor link removed.
	 *
	 * @param string $s The link that should be analyzed.
	 * @return string The link with the link anchor removed.
	 */
	public function remove_link_to_anchor( $s ) {

		$s = preg_replace_callback(
			'/([^#]+)               #Everything except # one or more times ( captured )
            \#.*                    #The # with anything the follows zero or more times
            /ux',
			array( $this, 'preg_replace_callback_4' ),
			$s
		);

		return $s;
	}

	/**
	 * Given a URL the parameter part is removed.
	 *
	 * @param string $s The URL that should be analyzed.
	 * @return string $s The URL.
	 */
	public function remove_url_parameters( $s ) {

		$s = preg_replace_callback(
			'/([^?]+)               #Everything except ? one or more time ( captured )
            \?.*                    #The ? with anything the follows zero or more times
            /ux',
			array( $this, 'preg_replace_callback_5' ),
			$s
		);

		return $s;
	}

	/**
	 * Callback of the preg_replace_callback() function
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * preg_replace_callback() function for PHP backward compatibility
	 *
	 * Look for uses of preg_replace_callback_4 to find which
	 * preg_replace_callback() function is actually using this callback
	 *
	 * @param array $m Todo.
	 */
	public function preg_replace_callback_4( $m ) {

		return $m[1];
	}

	/**
	 * Callback of the preg_replace_callback() function
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * preg_replace_callback() function for PHP backward compatibility
	 *
	 * Look for uses of preg_replace_callback_5 to find which
	 * preg_replace_callback() function is actually using this callback
	 *
	 * @param array $m Todo.
	 * @return mixed
	 */
	public function preg_replace_callback_5( $m ) {

		return $m[1];
	}

	/**
	 * Callback of the usort() function.
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * usort() function for PHP backward compatibility.
	 *
	 * Look for uses of usort_callback_1 to find which usort() function is
	 * actually using this callback.
	 *
	 * @param array $a The first array to compare.
	 * @param array $b The second array to compare.
	 */
	public function usort_callback_1( $a, $b ) {

		return $b['score'] - $a['score'];
	}

	/**
	 * Remove the HTML comment ( comment enclosed between <!-- and --> )
	 *
	 * @param string $content The HTML with the comments.
	 * @return string The HTML without the comments
	 */
	public function remove_html_comments( $content ) {

		$content = preg_replace(
			'/
            <!--                                #1 Comment Start
            .*?                                 #2 Any character zero or more time with a lazy quantifier
            -->                                 #3 Comment End
            /ix',
			'',
			$content
		);

		return $content;
	}

	/**
	 * Remove the script tags
	 *
	 * @param string $content The HTML with the script tags.
	 * @return string The HTML without the script tags
	 */
	public function remove_script_tags( $content ) {

		$content = preg_replace(
			'/
            <                                   #1 Begin the start-tag
            script                              #2 The script tag name
            (\s+[^>]*)?                         #3 Match the rest of the start-tag
            >                                   #4 End the start-tag
            .*?                                 #5 The element content ( with the "s" modifier the dot matches also the new lines )
            <\/script\s*>                       #6 The script end-tag with optional white-spaces before the closing >
            /ixs',
			'',
			$content
		);

		return $content;
	}

	/**
	 * Get the number of records available in the "_archive" db table.
	 *
	 * @return int The number of records in the "_archive" db table
	 */
	public function number_of_records_in_archive() {

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daextinma_archive" );

		return $total_items;
	}

	/**
	 * If $needle is present in the $haystack array echos 'selected="selected"'.
	 *
	 * @param array  $data_a The array in which the $needle should be searched.
	 * @param string $needle The string that should be searched in the $haystack array.
	 */
	public function selected_array( $data_a, $needle ) {

		if ( is_array( $data_a ) && in_array( $needle, $data_a, true ) ) {
			return 'selected="selected"';
		}
	}

	/**
	 *
	 * Given the post object, the HTML content of the Interlinks Optimization meta-box is returned.
	 *
	 * @param object $post The post object.
	 *
	 * @return void
	 */
	public function generate_interlinks_optimization_metabox_html( $post ) {

		$suggested_min_number_of_interlinks = $this->get_suggested_min_number_of_interlinks( $post->ID );
		$suggested_max_number_of_interlinks = $this->get_suggested_max_number_of_interlinks( $post->ID );
		$number_of_manual_interlinks        = $this->get_manual_interlinks( $post->post_content );
		$total_number_of_interlinks         = $number_of_manual_interlinks;
		if ( $total_number_of_interlinks >= $suggested_min_number_of_interlinks && $total_number_of_interlinks <= $suggested_max_number_of_interlinks ) {
			echo '<p>' . esc_html__( 'The number of internal links included in this post is optimized.', 'daext-interlinks-manager' ) . '</p>';
		} else {
			echo '<p>' . esc_html__( 'Please optimize the number of internal links.', 'daext-interlinks-manager' ) . '</p>';
			echo '<p>' . esc_html__( 'This post currently has', 'daext-interlinks-manager' ) . '&nbsp' . esc_html( $total_number_of_interlinks ) . '&nbsp' . esc_html(
				_n(
					'internal link',
					'internal links',
					$total_number_of_interlinks,
					'daext-interlinks-manager'
				)
			) . '.&nbsp';

			if ( $suggested_min_number_of_interlinks === $suggested_max_number_of_interlinks ) {
				echo esc_html__( 'However, based on the content length and on your options, their number should be', 'daext-interlinks-manager' ) . '&nbsp' . esc_html( $suggested_min_number_of_interlinks ) . '.</p>';
			} else {
				echo esc_html__( 'However, based on the content length and on your options, their number should be included between', 'daext-interlinks-manager' ) . '&nbsp' . esc_html( $suggested_min_number_of_interlinks ) . '&nbsp' . esc_html__( 'and', 'daext-interlinks-manager' ) . '&nbsp' . esc_html( $suggested_max_number_of_interlinks ) . '.</p>';
			}
		}
	}

	/**
	 * Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
	 */
	public function set_met_and_ml() {

		/**
		 * Set the custom "Max Execution Time Value" defined in the options if
		 * the 'Set Max Execution Time' option is set to "Yes"
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_set_max_execution_time' ), 10 ) === 1 ) {
			ini_set( 'max_execution_time', intval( get_option( 'daextinma_max_execution_time_value' ), 10 ) );
		}

		/**
		 * Set the custom "Memory Limit Value" ( in megabytes ) defined in the
		 * options if the 'Set Memory Limit' option is set to "Yes"
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_set_memory_limit' ), 10 ) === 1 ) {
			ini_set( 'memory_limit', intval( get_option( 'daextinma_memory_limit_value' ), 10 ) . 'M' );
		}
	}

	/**
	 * Echo the SVG icon specified by the $icon_name parameter.
	 *
	 * @param string $icon_name The name of the icon to echo.
	 *
	 * @return void
	 */
	public function echo_icon_svg( $icon_name ) {

		switch ( $icon_name ) {

			case 'dots-grid':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 6C12.5523 6 13 5.55228 13 5C13 4.44772 12.5523 4 12 4C11.4477 4 11 4.44772 11 5C11 5.55228 11.4477 6 12 6Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M12 13C12.5523 13 13 12.5523 13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12C11 12.5523 11.4477 13 12 13Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M12 20C12.5523 20 13 19.5523 13 19C13 18.4477 12.5523 18 12 18C11.4477 18 11 18.4477 11 19C11 19.5523 11.4477 20 12 20Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19 6C19.5523 6 20 5.55228 20 5C20 4.44772 19.5523 4 19 4C18.4477 4 18 4.44772 18 5C18 5.55228 18.4477 6 19 6Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19 13C19.5523 13 20 12.5523 20 12C20 11.4477 19.5523 11 19 11C18.4477 11 18 11.4477 18 12C18 12.5523 18.4477 13 19 13Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19 20C19.5523 20 20 19.5523 20 19C20 18.4477 19.5523 18 19 18C18.4477 18 18 18.4477 18 19C18 19.5523 18.4477 20 19 20Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M5 6C5.55228 6 6 5.55228 6 5C6 4.44772 5.55228 4 5 4C4.44772 4 4 4.44772 4 5C4 5.55228 4.44772 6 5 6Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M5 13C5.55228 13 6 12.5523 6 12C6 11.4477 5.55228 11 5 11C4.44772 11 4 11.4477 4 12C4 12.5523 4.44772 13 5 13Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M5 20C5.55228 20 6 19.5523 6 19C6 18.4477 5.55228 18 5 18C4.44772 18 4 18.4477 4 19C4 19.5523 4.44772 20 5 20Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'code-browser':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 9H2M14 17.5L16.5 15L14 12.5M10 12.5L7.5 15L10 17.5M2 7.8L2 16.2C2 17.8802 2 18.7202 2.32698 19.362C2.6146 19.9265 3.07354 20.3854 3.63803 20.673C4.27976 21 5.11984 21 6.8 21H17.2C18.8802 21 19.7202 21 20.362 20.673C20.9265 20.3854 21.3854 19.9265 21.673 19.362C22 18.7202 22 17.8802 22 16.2V7.8C22 6.11984 22 5.27977 21.673 4.63803C21.3854 4.07354 20.9265 3.6146 20.362 3.32698C19.7202 3 18.8802 3 17.2 3L6.8 3C5.11984 3 4.27976 3 3.63803 3.32698C3.07354 3.6146 2.6146 4.07354 2.32698 4.63803C2 5.27976 2 6.11984 2 7.8Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'layout-alt-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17.5 17H6.5M17.5 13H6.5M3 9H21M7.8 3H16.2C17.8802 3 18.7202 3 19.362 3.32698C19.9265 3.6146 20.3854 4.07354 20.673 4.63803C21 5.27976 21 6.11984 21 7.8V16.2C21 17.8802 21 18.7202 20.673 19.362C20.3854 19.9265 19.9265 20.3854 19.362 20.673C18.7202 21 17.8802 21 16.2 21H7.8C6.11984 21 5.27976 21 4.63803 20.673C4.07354 20.3854 3.6146 19.9265 3.32698 19.362C3 18.7202 3 17.8802 3 16.2V7.8C3 6.11984 3 5.27976 3.32698 4.63803C3.6146 4.07354 4.07354 3.6146 4.63803 3.32698C5.27976 3 6.11984 3 7.8 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'settings-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M18.7273 14.7273C18.6063 15.0015 18.5702 15.3056 18.6236 15.6005C18.6771 15.8954 18.8177 16.1676 19.0273 16.3818L19.0818 16.4364C19.2509 16.6052 19.385 16.8057 19.4765 17.0265C19.568 17.2472 19.6151 17.4838 19.6151 17.7227C19.6151 17.9617 19.568 18.1983 19.4765 18.419C19.385 18.6397 19.2509 18.8402 19.0818 19.0091C18.913 19.1781 18.7124 19.3122 18.4917 19.4037C18.271 19.4952 18.0344 19.5423 17.7955 19.5423C17.5565 19.5423 17.3199 19.4952 17.0992 19.4037C16.8785 19.3122 16.678 19.1781 16.5091 19.0091L16.4545 18.9545C16.2403 18.745 15.9682 18.6044 15.6733 18.5509C15.3784 18.4974 15.0742 18.5335 14.8 18.6545C14.5311 18.7698 14.3018 18.9611 14.1403 19.205C13.9788 19.4489 13.8921 19.7347 13.8909 20.0273V20.1818C13.8909 20.664 13.6994 21.1265 13.3584 21.4675C13.0174 21.8084 12.5549 22 12.0727 22C11.5905 22 11.1281 21.8084 10.7871 21.4675C10.4461 21.1265 10.2545 20.664 10.2545 20.1818V20.1C10.2475 19.7991 10.1501 19.5073 9.97501 19.2625C9.79991 19.0176 9.55521 18.8312 9.27273 18.7273C8.99853 18.6063 8.69437 18.5702 8.39947 18.6236C8.10456 18.6771 7.83244 18.8177 7.61818 19.0273L7.56364 19.0818C7.39478 19.2509 7.19425 19.385 6.97353 19.4765C6.7528 19.568 6.51621 19.6151 6.27727 19.6151C6.03834 19.6151 5.80174 19.568 5.58102 19.4765C5.36029 19.385 5.15977 19.2509 4.99091 19.0818C4.82186 18.913 4.68775 18.7124 4.59626 18.4917C4.50476 18.271 4.45766 18.0344 4.45766 17.7955C4.45766 17.5565 4.50476 17.3199 4.59626 17.0992C4.68775 16.8785 4.82186 16.678 4.99091 16.5091L5.04545 16.4545C5.25503 16.2403 5.39562 15.9682 5.4491 15.6733C5.50257 15.3784 5.46647 15.0742 5.34545 14.8C5.23022 14.5311 5.03887 14.3018 4.79497 14.1403C4.55107 13.9788 4.26526 13.8921 3.97273 13.8909H3.81818C3.33597 13.8909 2.87351 13.6994 2.53253 13.3584C2.19156 13.0174 2 12.5549 2 12.0727C2 11.5905 2.19156 11.1281 2.53253 10.7871C2.87351 10.4461 3.33597 10.2545 3.81818 10.2545H3.9C4.2009 10.2475 4.49273 10.1501 4.73754 9.97501C4.98236 9.79991 5.16883 9.55521 5.27273 9.27273C5.39374 8.99853 5.42984 8.69437 5.37637 8.39947C5.3229 8.10456 5.18231 7.83244 4.97273 7.61818L4.91818 7.56364C4.74913 7.39478 4.61503 7.19425 4.52353 6.97353C4.43203 6.7528 4.38493 6.51621 4.38493 6.27727C4.38493 6.03834 4.43203 5.80174 4.52353 5.58102C4.61503 5.36029 4.74913 5.15977 4.91818 4.99091C5.08704 4.82186 5.28757 4.68775 5.50829 4.59626C5.72901 4.50476 5.96561 4.45766 6.20455 4.45766C6.44348 4.45766 6.68008 4.50476 6.9008 4.59626C7.12152 4.68775 7.32205 4.82186 7.49091 4.99091L7.54545 5.04545C7.75971 5.25503 8.03183 5.39562 8.32674 5.4491C8.62164 5.50257 8.9258 5.46647 9.2 5.34545H9.27273C9.54161 5.23022 9.77093 5.03887 9.93245 4.79497C10.094 4.55107 10.1807 4.26526 10.1818 3.97273V3.81818C10.1818 3.33597 10.3734 2.87351 10.7144 2.53253C11.0553 2.19156 11.5178 2 12 2C12.4822 2 12.9447 2.19156 13.2856 2.53253C13.6266 2.87351 13.8182 3.33597 13.8182 3.81818V3.9C13.8193 4.19253 13.906 4.47834 14.0676 4.72224C14.2291 4.96614 14.4584 5.15749 14.7273 5.27273C15.0015 5.39374 15.3056 5.42984 15.6005 5.37637C15.8954 5.3229 16.1676 5.18231 16.3818 4.97273L16.4364 4.91818C16.6052 4.74913 16.8057 4.61503 17.0265 4.52353C17.2472 4.43203 17.4838 4.38493 17.7227 4.38493C17.9617 4.38493 18.1983 4.43203 18.419 4.52353C18.6397 4.61503 18.8402 4.74913 19.0091 4.91818C19.1781 5.08704 19.3122 5.28757 19.4037 5.50829C19.4952 5.72901 19.5423 5.96561 19.5423 6.20455C19.5423 6.44348 19.4952 6.68008 19.4037 6.9008C19.3122 7.12152 19.1781 7.32205 19.0091 7.49091L18.9545 7.54545C18.745 7.75971 18.6044 8.03183 18.5509 8.32674C18.4974 8.62164 18.5335 8.9258 18.6545 9.2V9.27273C18.7698 9.54161 18.9611 9.77093 19.205 9.93245C19.4489 10.094 19.7347 10.1807 20.0273 10.1818H20.1818C20.664 10.1818 21.1265 10.3734 21.4675 10.7144C21.8084 11.0553 22 11.5178 22 12C22 12.4822 21.8084 12.9447 21.4675 13.2856C21.1265 13.6266 20.664 13.8182 20.1818 13.8182H20.1C19.8075 13.8193 19.5217 13.906 19.2778 14.0676C19.0339 14.2291 18.8425 14.4584 18.7273 14.7273Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'grid-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M8.4 3H4.6C4.03995 3 3.75992 3 3.54601 3.10899C3.35785 3.20487 3.20487 3.35785 3.10899 3.54601C3 3.75992 3 4.03995 3 4.6V8.4C3 8.96005 3 9.24008 3.10899 9.45399C3.20487 9.64215 3.35785 9.79513 3.54601 9.89101C3.75992 10 4.03995 10 4.6 10H8.4C8.96005 10 9.24008 10 9.45399 9.89101C9.64215 9.79513 9.79513 9.64215 9.89101 9.45399C10 9.24008 10 8.96005 10 8.4V4.6C10 4.03995 10 3.75992 9.89101 3.54601C9.79513 3.35785 9.64215 3.20487 9.45399 3.10899C9.24008 3 8.96005 3 8.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 3H15.6C15.0399 3 14.7599 3 14.546 3.10899C14.3578 3.20487 14.2049 3.35785 14.109 3.54601C14 3.75992 14 4.03995 14 4.6V8.4C14 8.96005 14 9.24008 14.109 9.45399C14.2049 9.64215 14.3578 9.79513 14.546 9.89101C14.7599 10 15.0399 10 15.6 10H19.4C19.9601 10 20.2401 10 20.454 9.89101C20.6422 9.79513 20.7951 9.64215 20.891 9.45399C21 9.24008 21 8.96005 21 8.4V4.6C21 4.03995 21 3.75992 20.891 3.54601C20.7951 3.35785 20.6422 3.20487 20.454 3.10899C20.2401 3 19.9601 3 19.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 14H15.6C15.0399 14 14.7599 14 14.546 14.109C14.3578 14.2049 14.2049 14.3578 14.109 14.546C14 14.7599 14 15.0399 14 15.6V19.4C14 19.9601 14 20.2401 14.109 20.454C14.2049 20.6422 14.3578 20.7951 14.546 20.891C14.7599 21 15.0399 21 15.6 21H19.4C19.9601 21 20.2401 21 20.454 20.891C20.6422 20.7951 20.7951 20.6422 20.891 20.454C21 20.2401 21 19.9601 21 19.4V15.6C21 15.0399 21 14.7599 20.891 14.546C20.7951 14.3578 20.6422 14.2049 20.454 14.109C20.2401 14 19.9601 14 19.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M8.4 14H4.6C4.03995 14 3.75992 14 3.54601 14.109C3.35785 14.2049 3.20487 14.3578 3.10899 14.546C3 14.7599 3 15.0399 3 15.6V19.4C3 19.9601 3 20.2401 3.10899 20.454C3.20487 20.6422 3.35785 20.7951 3.54601 20.891C3.75992 21 4.03995 21 4.6 21H8.4C8.96005 21 9.24008 21 9.45399 20.891C9.64215 20.7951 9.79513 20.6422 9.89101 20.454C10 20.2401 10 19.9601 10 19.4V15.6C10 15.0399 10 14.7599 9.89101 14.546C9.79513 14.3578 9.64215 14.2049 9.45399 14.109C9.24008 14 8.96005 14 8.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'line-chart-up-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 9L11.5657 14.4343C11.3677 14.6323 11.2687 14.7313 11.1545 14.7684C11.0541 14.8011 10.9459 14.8011 10.8455 14.7684C10.7313 14.7313 10.6323 14.6323 10.4343 14.4343L8.56569 12.5657C8.36768 12.3677 8.26867 12.2687 8.15451 12.2316C8.05409 12.1989 7.94591 12.1989 7.84549 12.2316C7.73133 12.2687 7.63232 12.3677 7.43431 12.5657L3 17M17 9H13M17 9V13M7.8 21H16.2C17.8802 21 18.7202 21 19.362 20.673C19.9265 20.3854 20.3854 19.9265 20.673 19.362C21 18.7202 21 17.8802 21 16.2V7.8C21 6.11984 21 5.27976 20.673 4.63803C20.3854 4.07354 19.9265 3.6146 19.362 3.32698C18.7202 3 17.8802 3 16.2 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'link-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9.99999 13C10.4294 13.5741 10.9773 14.0491 11.6065 14.3929C12.2357 14.7367 12.9315 14.9411 13.6466 14.9923C14.3618 15.0435 15.0796 14.9403 15.7513 14.6897C16.4231 14.4392 17.0331 14.047 17.54 13.54L20.54 10.54C21.4508 9.59695 21.9547 8.33394 21.9434 7.02296C21.932 5.71198 21.4061 4.45791 20.4791 3.53087C19.552 2.60383 18.298 2.07799 16.987 2.0666C15.676 2.0552 14.413 2.55918 13.47 3.46997L11.75 5.17997M14 11C13.5705 10.4258 13.0226 9.95078 12.3934 9.60703C11.7642 9.26327 11.0685 9.05885 10.3533 9.00763C9.63819 8.95641 8.9204 9.0596 8.24864 9.31018C7.57688 9.56077 6.96687 9.9529 6.45999 10.46L3.45999 13.46C2.5492 14.403 2.04522 15.666 2.05662 16.977C2.06801 18.288 2.59385 19.542 3.52089 20.4691C4.44793 21.3961 5.702 21.9219 7.01298 21.9333C8.32396 21.9447 9.58697 21.4408 10.53 20.53L12.24 18.82" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-verified-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 12L11 14L15.5 9.5M7.33377 3.8187C8.1376 3.75455 8.90071 3.43846 9.51447 2.91542C10.9467 1.69486 13.0533 1.69486 14.4855 2.91542C15.0993 3.43846 15.8624 3.75455 16.6662 3.8187C18.5421 3.96839 20.0316 5.45794 20.1813 7.33377C20.2455 8.1376 20.5615 8.90071 21.0846 9.51447C22.3051 10.9467 22.3051 13.0533 21.0846 14.4855C20.5615 15.0993 20.2455 15.8624 20.1813 16.6662C20.0316 18.5421 18.5421 20.0316 16.6662 20.1813C15.8624 20.2455 15.0993 20.5615 14.4855 21.0846C13.0533 22.3051 10.9467 22.3051 9.51447 21.0846C8.90071 20.5615 8.1376 20.2455 7.33377 20.1813C5.45794 20.0316 3.96839 18.5421 3.8187 16.6662C3.75455 15.8624 3.43846 15.0993 2.91542 14.4855C1.69486 13.0533 1.69486 10.9467 2.91542 9.51447C3.43846 8.90071 3.75455 8.1376 3.8187 7.33377C3.96839 5.45794 5.45794 3.96839 7.33377 3.8187Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-up':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 15L12 9L6 15" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-down':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 9L12 15L18 9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M15 18L9 12L15 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 17L13 12L18 7M11 17L6 12L11 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 18L15 12L9 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 17L11 12L6 7M13 17L18 12L13 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'arrow-up-right':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M7 17L17 7M17 7H7M17 7V17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'plus':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 5V19M5 12H19" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'bar-chart-07':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M21 21H6.2C5.07989 21 4.51984 21 4.09202 20.782C3.71569 20.5903 3.40973 20.2843 3.21799 19.908C3 19.4802 3 18.9201 3 17.8V3M7 10.5V17.5M11.5 5.5V17.5M16 10.5V17.5M20.5 5.5V17.5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'lightbulb-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 2V3M3 12H2M5.5 5.5L4.8999 4.8999M18.5 5.5L19.1002 4.8999M22 12H21M10 13.5H14M12 13.5V18.5M15.5 16.874C17.0141 15.7848 18 14.0075 18 12C18 8.68629 15.3137 6 12 6C8.68629 6 6 8.68629 6 12C6 14.0075 6.98593 15.7848 8.5 16.874V18.8C8.5 19.9201 8.5 20.4802 8.71799 20.908C8.90973 21.2843 9.21569 21.5903 9.59202 21.782C10.0198 22 10.5799 22 11.7 22H12.3C13.4201 22 13.9802 22 14.408 21.782C14.7843 21.5903 15.0903 21.2843 15.282 20.908C15.5 20.4802 15.5 19.9201 15.5 18.8V16.874Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'share-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M21 6H17.8C16.1198 6 15.2798 6 14.638 6.32698C14.0735 6.6146 13.6146 7.07354 13.327 7.63803C13 8.27976 13 9.11984 13 10.8V12M21 6L18 3M21 6L18 9M10 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21H16.2C17.8802 21 18.7202 21 19.362 20.673C19.9265 20.3854 20.3854 19.9265 20.673 19.362C21 18.7202 21 17.8802 21 16.2V14" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-circle-broken':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'cursor-click-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 3.5V2M5.06066 5.06066L4 4M5.06066 13L4 14.0607M13 5.06066L14.0607 4M3.5 9H2M8.5 8.5L12.6111 21.2778L15.5 18.3889L19.1111 22L22 19.1111L18.3889 15.5L21.2778 12.6111L8.5 8.5Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-in-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 8L16 12M16 12L12 16M16 12H3M3.33782 7C5.06687 4.01099 8.29859 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C8.29859 22 5.06687 19.989 3.33782 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-out-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 8L22 12M22 12L18 16M22 12H9M15 4.20404C13.7252 3.43827 12.2452 3 10.6667 3C5.8802 3 2 7.02944 2 12C2 16.9706 5.8802 21 10.6667 21C12.2452 21 13.7252 20.5617 15 19.796" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'order-asc':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H15M3 6H9M3 18H21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'order-desc':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H15M3 6H21M3 18H9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'clipboard-icon-svg':
				$xml = '<?xml version="1.0" encoding="utf-8"?>
				<svg version="1.1" id="Layer_3" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
					 viewBox="0 0 20 20" style="enable-background:new 0 0 20 20;" xml:space="preserve">
				<path d="M14,18H8c-1.1,0-2-0.9-2-2V7c0-1.1,0.9-2,2-2h6c1.1,0,2,0.9,2,2v9C16,17.1,15.1,18,14,18z M8,7v9h6V7H8z"/>
				<path d="M5,4h6V2H5C3.9,2,3,2.9,3,4v9h2V4z"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			case 'x':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 7L7 17M7 7L17 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			case 'diamond-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M2.49954 9H21.4995M9.99954 3L7.99954 9L11.9995 20.5L15.9995 9L13.9995 3M12.6141 20.2625L21.5727 9.51215C21.7246 9.32995 21.8005 9.23885 21.8295 9.13717C21.8551 9.04751 21.8551 8.95249 21.8295 8.86283C21.8005 8.76114 21.7246 8.67005 21.5727 8.48785L17.2394 3.28785C17.1512 3.18204 17.1072 3.12914 17.0531 3.09111C17.0052 3.05741 16.9518 3.03238 16.8953 3.01717C16.8314 3 16.7626 3 16.6248 3H7.37424C7.2365 3 7.16764 3 7.10382 3.01717C7.04728 3.03238 6.99385 3.05741 6.94596 3.09111C6.89192 3.12914 6.84783 3.18204 6.75966 3.28785L2.42633 8.48785C2.2745 8.67004 2.19858 8.76114 2.16957 8.86283C2.144 8.95249 2.144 9.04751 2.16957 9.13716C2.19858 9.23885 2.2745 9.32995 2.42633 9.51215L11.385 20.2625C11.596 20.5158 11.7015 20.6424 11.8279 20.6886C11.9387 20.7291 12.0603 20.7291 12.1712 20.6886C12.2975 20.6424 12.4031 20.5158 12.6141 20.2625Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			default:
				$xml = '';

				break;

		}

		echo wp_kses( $xml, $allowed_html );
	}

	/**
	 * Used to generate the internal links archive in the "Dashboard" menu.
	 */
	public function update_interlinks_archive() {

		// Generate the link juice data (these data will be used to generate the value of the IIL column).
		$this->update_juice_archive();

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->set_met_and_ml();

		/**
		 * Create a query used to consider in the analysis only the post types
		 * selected with the 'dashboard_post_types' option.
		 */
		$dashboard_post_types_a = maybe_unserialize( get_option( $this->get( 'slug' ) . '_dashboard_post_types' ) );

		// If $dashboard_post_types_a is an empty array add "post" and "page" as default post types.
		if ( empty( $dashboard_post_types_a ) ) {
			$dashboard_post_types_a = array( 'post', 'page' );
		}

		$post_types_query       = '';
		global $wpdb;
		if ( is_array( $dashboard_post_types_a ) ) {
			foreach ( $dashboard_post_types_a as $key => $value ) {

				if ( ! preg_match( '/[a-z0-9_-]+/', $value ) ) {
					continue;}

				$post_types_query .= $wpdb->prepare( 'post_type = %s', $value );
				if ( ( count( $dashboard_post_types_a ) - 1 ) !== $key ) {
					$post_types_query .= ' OR ';}
			}
		}

		/**
		 * Get all the manual internal links and save them in the archive db
		 * table.
		 */
		$limit_posts_analysis = intval( get_option( $this->get( 'slug' ) . '_limit_posts_analysis' ), 10 );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_types_query is already sanitized.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$posts_a = $wpdb->get_results(
			$wpdb->prepare( "SELECT ID, post_title, post_type, post_date, post_content FROM {$wpdb->prefix}posts WHERE ($post_types_query) AND post_status = 'publish' ORDER BY post_date DESC LIMIT %d", $limit_posts_analysis ),
			ARRAY_A
		);
		// phpcs:enable

		// Delete the internal links archive database table content.
		$result = $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextinma_archive" );

		// Init $archive_a.
		$archive_a = array();

		foreach ( $posts_a as $key => $single_post ) {

			// Set the post id.
			$post_archive_post_id = $single_post['ID'];

			// get the post title.
			$post_archive_post_title = $single_post['post_title'];

			// Get the post permalink.
			$post_archive_post_permalink = get_the_permalink( $single_post['ID'] );

			// Get the post edit link.
			$post_archive_post_edit_link = get_edit_post_link( $single_post['ID'], 'url' );

			// Set the post type.
			$post_archive_post_type = $single_post['post_type'];

			// Set the post date.
			$post_archive_post_date = $single_post['post_date'];

			// Set the post content.
			$post_content = $single_post['post_content'];

			// Set the number of manual internal links.
			$post_archive_manual_interlinks = $this->get_manual_interlinks( $post_content );

			/**
			 * Get the IIL from the juice db table by comparing the permalink of this post with the URL field available
			 * in the juice db table.
			 */

			// Get the permalink of the post.
			$permalink = get_the_permalink( $single_post['ID'] );

			// Find this permalink in the url field of the "_juice" db table.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
			$juice_obj = $wpdb->get_row(
				$wpdb->prepare( "SELECT iil FROM {$wpdb->prefix}daextinma_juice WHERE url = %s ", $permalink )
			);
			// phpcs:enable

			if ( null !== $juice_obj ) {
				$post_archive_iil = $juice_obj->iil;
			} else {
				$post_archive_iil = 0;
			}

			// Set the post content length.
			$post_archive_content_length = mb_strlen( trim( $post_content ) );

			// Set the recommended interlinks.
			$post_archive_recommended_interlinks = $this->calculate_recommended_interlinks( $post_archive_manual_interlinks, $post_archive_content_length );

			// Set the optimization flag.
			$optimization = $this->calculate_optimization( $post_archive_manual_interlinks, $post_archive_content_length );

			/**
			 * Save data in the $archive_a array ( data will be later saved into
			 * the archive db table ).
			 */
			$archive_a[] = array(
				'post_id'                => $post_archive_post_id,
				'post_title'             => $post_archive_post_title,
				'post_permalink'         => $post_archive_post_permalink,
				'post_edit_link'         => $post_archive_post_edit_link,
				'post_type'              => $post_archive_post_type,
				'post_date'              => $post_archive_post_date,
				'manual_interlinks'      => $post_archive_manual_interlinks,
				'iil'                    => $post_archive_iil,
				'content_length'         => $post_archive_content_length,
				'recommended_interlinks' => $post_archive_recommended_interlinks,
				'optimization'           => $optimization,
			);

		}

		/**
		 * Save data into the archive db table with multiple queries of 100
		 * items each one.
		 * It's a compromise for the following two reasons:
		 * 1 - For performance, too many queries slow down the process
		 * 2 - To avoid problem with queries too long the number of inserted
		 * rows per query are limited to 100
		 */
		$archive_a_length = count( $archive_a );
		$query_groups     = array();
		$query_index      = 0;
		foreach ( $archive_a as $key => $single_archive ) {

			$query_index = intval( $key / 100, 10 );

			$query_groups[ $query_index ][] = $wpdb->prepare(
				'( %d, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d )',
				$single_archive['post_id'],
				$single_archive['post_title'],
				$single_archive['post_permalink'],
				$single_archive['post_edit_link'],
				$single_archive['post_type'],
				$single_archive['post_date'],
				$single_archive['manual_interlinks'],
				$single_archive['iil'],
				$single_archive['content_length'],
				$single_archive['recommended_interlinks'],
				$single_archive['optimization']
			);

		}

		/**
		 * Each item in the $query_groups array includes a maximum of 100
		 * assigned records. Here each group creates a query and the query is
		 * executed.
		 */
		$query_start = "INSERT INTO {$wpdb->prefix}daextinma_archive (post_id, post_title, post_permalink, post_edit_link, post_type, post_date, manual_interlinks, iil, content_length, recommended_interlinks, optimization) VALUES ";
		$query_end   = '';

		foreach ( $query_groups as $key => $query_values ) {

			$query_body = '';

			foreach ( $query_values as $single_query_value ) {

				$query_body .= $single_query_value . ',';

			}

			$safe_sql = $query_start . substr( $query_body, 0, strlen( $query_body ) - 1 ) . $query_end;

			// Save data into the archive db table.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- $query_start, query_body, and $query_end are already prepared.
			$wpdb->query( $query_start . substr( $query_body, 0, strlen( $query_body ) - 1 ) . $query_end );
			// phpcs:enable

		}

		// Update the option that stores the last update date.
		update_option( $this->get( 'slug' ) . '_internal_links_data_last_update', current_time( 'mysql' ) );
	}

	/**
	 * Ajax handler used to generate the juice archive in "Juice" menu.
	 */
	public function update_juice_archive() {

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->set_met_and_ml();

		// Delete the juice db table content.
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextinma_juice" );

		// Delete the anchors db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextinma_anchors" );

		// update the juice archive ---------------------------------------------.
		$juice_a  = array();
		$juice_id = 0;

		/**
		 * Create a query used to consider in the analysis only the post types
		 * selected with the 'juice_post_types' option.
		 */
		$juice_post_types_a = maybe_unserialize( get_option( $this->get( 'slug' ) . '_juice_post_types' ) );

		// If $juice_post_types_a is an empty array add "post" and "page" as default post types.
		if ( empty( $juice_post_types_a ) ) {
			$juice_post_types_a = array( 'post', 'page' );
		}

		$post_types_query   = '';
		if ( is_array( $juice_post_types_a ) ) {
			foreach ( $juice_post_types_a as $key => $value ) {

				if ( ! preg_match( '/[a-z0-9_-]+/', $value ) ) {
					continue;}

				$post_types_query .= $wpdb->prepare( 'post_type = %s', $value );
				if ( ( count( $juice_post_types_a ) - 1 ) !== $key ) {
					$post_types_query .= ' OR ';}
			}
		}

		/**
		 * Get all the manual and auto internal links and save them in an array.
		 */
		$limit_posts_analysis = intval( get_option( $this->get( 'slug' ) . '_limit_posts_analysis' ), 10 );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_types_query is already prepared.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$posts_a = $wpdb->get_results(
			$wpdb->prepare( "SELECT ID, post_title, post_type, post_date, post_content FROM {$wpdb->prefix}posts WHERE ($post_types_query) AND post_status = 'publish' ORDER BY post_date DESC LIMIT %d", $limit_posts_analysis ),
			ARRAY_A
		);
		// phpcs:enable

		foreach ( $posts_a as $key => $single_post ) {

			// Set the post content.
			$post_content = $single_post['post_content'];

			// Remove the HTML comments.
			$post_content = $this->remove_html_comments( $post_content );

			// Remove script tags.
			$post_content = $this->remove_script_tags( $post_content );

			/**
			 * Find all the manual and auto interlinks matches with a regular
			 * expression and add them in the $juice_a array.
			 */
			preg_match_all(
				$this->internal_links_regex(),
				$post_content,
				$matches,
				PREG_OFFSET_CAPTURE
			);

			// Save the URLs, the juice value and other info in the array.
			$captures = $matches[2];
			foreach ( $captures as $key => $single_capture ) {

				// Get the link position.
				$link_position = $matches[0][ $key ][1];

				// save the captured URL.
				$url = $this->relative_to_absolute_url( $single_capture[0], $single_post['ID'] );

				/**
				 * Remove link to anchor from the URL ( if enabled through the
				 * options ).
				 */
				if ( intval( get_option( $this->get( 'slug' ) . '_remove_link_to_anchor' ), 10 ) === 1 ) {
					$url = $this->remove_link_to_anchor( $url );
				}

				/**
				 * Remove the URL parameters ( if enabled through the options ).
				 */
				if ( 1 === intval( get_option( $this->get( 'slug' ) . '_remove_url_parameters' ), 10 ) ) {
					$url = $this->remove_url_parameters( $url );
				}

				$juice_a[ $juice_id ]['url']            = $url;
				$juice_a[ $juice_id ]['juice']          = $this->calculate_link_juice( $post_content, $single_post['ID'], $link_position );
				$juice_a[ $juice_id ]['anchor']         = $matches[3][ $key ][0];
				$juice_a[ $juice_id ]['post_id']        = $single_post['ID'];
				$juice_a[ $juice_id ]['post_title']     = $single_post['post_title'];
				$juice_a[ $juice_id ]['post_permalink'] = get_the_permalink( $single_post['ID'] );
				$juice_a[ $juice_id ]['post_edit_link'] = get_edit_post_link( $single_post['ID'], 'url' );

				++$juice_id;

			}
		}

		/**
		 * Save data into the anchors db table with multiple queries of 100
		 * items each one.
		 * It's a compromise for the following two reasons:
		 * 1 - For performance, too many queries slow down the process.
		 * 2 - To avoid problem with queries too long the number of inserted
		 * rows per query are limited to 100.
		 */
		$juice_a_length = count( $juice_a );
		$query_groups   = array();
		$query_index    = 0;
		foreach ( $juice_a as $key => $single_juice ) {

			$query_index = intval( $key / 100, 10 );

			$query_groups[ $query_index ][] = $wpdb->prepare(
				'( %s, %s, %d, %d, %s, %s, %s )',
				$single_juice['url'],
				$single_juice['anchor'],
				$single_juice['post_id'],
				$single_juice['juice'],
				$single_juice['post_title'],
				$single_juice['post_permalink'],
				$single_juice['post_edit_link']
			);

		}

		/**
		 * Each item in the $query_groups array includes a maximum of 100
		 * assigned records. Here each group creates a query and the query is
		 * executed.
		 */
		$query_start = "INSERT INTO {$wpdb->prefix}daextinma_anchors (url, anchor, post_id, juice, post_title, post_permalink, post_edit_link) VALUES ";
		$query_end   = '';

		foreach ( $query_groups as $key => $query_values ) {

			$query_body = '';

			foreach ( $query_values as $single_query_value ) {

				$query_body .= $single_query_value . ',';

			}

			// Save data into the archive db table.
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- $query_start, query_body, and $query_end are already prepared.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$query_start . substr( $query_body, 0, strlen( $query_body ) - 1 ) . $query_end
			);
			// phpcs:enable

		}

		// Prepare data that should be saved in the juice db table --------------.
		$juice_a_no_duplicates    = array();
		$juice_a_no_duplicates_id = 0;

		/*
		 * Reduce multiple array items with the same URL to a single array item
		 * with a sum of iil and juice
		 */
		foreach ( $juice_a as $key => $single_juice ) {

			$duplicate_found = false;

			// Verify if an item with this url already exist in the $juice_a_no_duplicates array.
			foreach ( $juice_a_no_duplicates as $key => $single_juice_a_no_duplicates ) {

				if ( $single_juice_a_no_duplicates['url'] === $single_juice['url'] ) {
					++$juice_a_no_duplicates[ $key ]['iil'];
					$juice_a_no_duplicates[ $key ]['juice'] = $juice_a_no_duplicates[ $key ]['juice'] + $single_juice['juice'];
					$duplicate_found                        = true;
				}
			}

			/*
			 * if this url doesn't already exist in the array save it in
			 * $juice_a_no_duplicates
			 */
			if ( ! $duplicate_found ) {

				$juice_a_no_duplicates[ $juice_a_no_duplicates_id ]['url']   = $single_juice['url'];
				$juice_a_no_duplicates[ $juice_a_no_duplicates_id ]['iil']   = 1;
				$juice_a_no_duplicates[ $juice_a_no_duplicates_id ]['juice'] = $single_juice['juice'];
				++$juice_a_no_duplicates_id;

			}
		}

		/**
		 * Calculate the relative link juice on a scale between 0 and 100,
		 * the maximum value found corresponds to the 100 value of the
		 * relative link juice.
		 */
		$max_value = 0;
		foreach ( $juice_a_no_duplicates as $key => $juice_a_no_duplicates_single ) {
			if ( $juice_a_no_duplicates_single['juice'] > $max_value ) {
				$max_value = $juice_a_no_duplicates_single['juice'];
			}
		}

		// Set the juice_relative index in the array.
		foreach ( $juice_a_no_duplicates as $key => $juice_a_no_duplicates_single ) {
			$juice_a_no_duplicates[ $key ]['juice_relative'] = ( 100 * $juice_a_no_duplicates_single['juice'] ) / $max_value;
		}

		/**
		 * Save data into the juice db table with multiple queries of 100
		 * items each one.
		 * It's a compromise for the following two reasons:
		 * 1 - For performance, too many queries slow down the process.
		 * 2 - To avoid problem with queries too long the number of inserted
		 * rows per query are limited to 100.
		 */
		$juice_a_no_duplicates_length = count( $juice_a_no_duplicates );
		$query_groups                 = array();
		$query_index                  = 0;
		foreach ( $juice_a_no_duplicates as $key => $value ) {

			$query_index = intval( $key / 100, 10 );

			$query_groups[ $query_index ][] = $wpdb->prepare(
				'( %s, %d, %d, %d )',
				$value['url'],
				$value['iil'],
				$value['juice'],
				$value['juice_relative']
			);

		}

		/**
		 * Each item in the $query_groups array includes a maximum of 100
		 * assigned records. Here each group creates a query and the query is
		 * executed.
		 */
		$query_start = "INSERT INTO {$wpdb->prefix}daextinma_juice (url, iil, juice, juice_relative) VALUES ";
		$query_end   = '';

		foreach ( $query_groups as $key => $query_values ) {

			$query_body = '';

			foreach ( $query_values as $single_query_value ) {

				$query_body .= $single_query_value . ',';

			}

			// Save data into the archive db table.
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- $query_start, query_body, and $query_end are already prepared.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$query_start . substr( $query_body, 0, strlen( $query_body ) - 1 ) . $query_end
			);
			// phpcs:enable

		}

		// Update the option that stores the last update date.
		update_option( $this->get( 'slug' ) . '_juice_data_last_update', current_time( 'mysql' ) );

		// Send output.
		return 'success';
	}

	/**
	 * Escape the double quotes of the $content string, so the returned string
	 * can be used in CSV fields enclosed by double quotes.
	 *
	 * @param string $content The unescape content ( Ex: She said "No!" ).
	 * @return string The escaped content ( Ex: She said ""No!"" )
	 */
	public function esc_csv( $content ) {
		return str_replace( '"', '""', $content );
	}

	/**
	 * Iterate the $results array and find the average number of manual internal links. Note that the manual internal
	 * links value is stored in the 'manual_interlinks' key of the $results array.
	 *
	 * @param array $results The link statistics stored in the archive db table provided as an array.
	 *
	 * @return int The average number of manual internal links.
	 */
	public function get_average_mil( $results ) {

		// Init the $total_mil variable.
		$total_mil = 0;

		// Iterate the $results array and sum the manual internal links.
		foreach ( $results as $key => $result ) {
			$total_mil += $result->manual_interlinks;
		}

		// Calculate the average number of manual internal links.
		$average_mil = $total_mil / count( $results );

		// Round the average number of manual internal links (no decimals).
		$average_mil = round( $average_mil, 1 );

		return $average_mil;
	}

	/**
	 * Iterate the $results array and find the average number of internal inbound links. Note that the internal inbound
	 * links value is stored in the 'iil' key of the $results array.
	 *
	 * @param array $results The link statistics stored in the archive db table provided as an array.
	 *
	 * @return int The average number of manual internal links.
	 */
	public function get_average_iil( $results ) {

		// Init the $total_iil variable.
		$total_iil = 0;

		// Iterate the $results array and sum the manual internal links.
		foreach ( $results as $key => $result ) {
			$total_iil += $result->iil;
		}

		// Calculate the average number of manual internal links.
		$average_iil = $total_iil / count( $results );

		// Round the average number of manual internal links (no decimals).
		$average_iil = round( $average_iil, 1 );

		return $average_iil;
	}

	/**
	 * Iterate the $results array and find the average juice value. Note that the juice
	 * value is stored in the 'juice' key of the $results array.
	 *
	 * @param array $results The link statistics stored in the archive db table provided as an array.
	 *
	 * @return int The average number of manual internal links.
	 */
	public function get_average_juice( $results ) {

		// Init the $total_juice variable.
		$total_juice = 0;

		// Iterate the $results array and sum the manual internal links.
		foreach ( $results as $key => $result ) {
			$total_juice += $result->juice;
		}

		// Calculate the average number of manual internal links.
		$average_juice = $total_juice / count( $results );

		// Round the average number of manual internal links (no decimals).
		$average_juice = round( $average_juice, 1 );

		return $average_juice;
	}

	/**
	 * Converts relative URLs to absolute URLs.
	 *
	 * The following type of URLs are supported:
	 *
	 * - Absolute URLs | E.g., "https://example.com/post/"
	 * - Protocol-relative URLs | E.g., "//localhost/image.jpg".
	 * - Root-relative URLs | E.g., "/post/".
	 * - Fragment-only URLs | E.g., "#section1".
	 * - Relative URLs with relative paths. | E.g., "./post/", "../post", "../../post".
	 * - Page-relative URLs | E.g., "post/".
	 *
	 * @param String $relative_url The relative URL that should be converted.
	 * @param Int $post_id The ID of the post.
	 *
	 * @return mixed|string
	 */
	public function relative_to_absolute_url( $relative_url, $post_id ) {

		$post_permalink = get_permalink( $post_id );

		/**
		 * If already an absolute URL, return as is.
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( empty( $relative_url ) || wp_parse_url( $relative_url, PHP_URL_SCHEME ) ) {
			return $relative_url;
		}

		// Get the site URL. Ensure trailing slash for proper resolution.
		$base_url = home_url( '/' );

		// Parse base URL.
		$base_parts = wp_parse_url( $base_url );

		/**
		 * Protocol-relative URL | If it's a protocol-relative URL (e.g., //example.com/image.jpg), add "https:" as
		 * default.
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( str_starts_with( $relative_url, '//' ) ) {
			if ( $this->is_site_using_https() ) {
				return 'https:' . $relative_url;
			} else {
				return 'http:' . $relative_url;
			}
		}

		/**
		 * Root-relative URLs | Handle root-relative URLs (e.g., "/some-page/").
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( str_starts_with( $relative_url, '/' ) ) {
			return $base_parts['scheme'] . '://' . $base_parts['host'] . $relative_url;
		}

		/**
		 * Fragment identifier | Handle fragment-only URLs (e.g., "#section").
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( str_starts_with( $relative_url, '#' ) ) {
			return $post_permalink . $relative_url;
		}

		/**
		 * Relative URLs with relative paths.
		 *
		 * Handles the relative URLs with relative paths like "./page", "../page", and "../../page'.
		 *
		 * Check if the relative URLs starts with "./", or "../", or subsequent levels like "../../".
		 * If it does, use the exact relative URL to retrieve and return the absolute URL.
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */

		// This conditional supports all the levels like '../../', etc.
		if ( str_starts_with( $relative_url, './' ) || str_starts_with( $relative_url, '../' ) ) {

			/**
			 * Here, based on the type of relative URL, we move up one or more levels in the directory tree
			 * to create the correct absolute URL.
			 *
			 * Note that the URL on which we should move levels is stored in the $current_url variable.
			 */
			$post_permalink_parts = wp_parse_url( $post_permalink );

			// Ensure we have a valid base URL.
			if ( ! isset( $post_permalink_parts['scheme'], $post_permalink_parts['host'], $post_permalink_parts['path'] ) ) {
				return $relative_url; // Return as-is if current URL is invalid.
			}

			// Get the directory of the current URL.
			$base_path = rtrim( $post_permalink_parts['path'], '/' );

			// Split the base path into segments.
			$base_parts = explode( '/', $base_path );

			// Split the relative URL into segments.
			$relative_parts = explode( '/', $relative_url );

			// Process the relative path.
			foreach ( $relative_parts as $part ) {
				if ( '..' === $part ) {
					// Move up one directory level.
					if ( count( $base_parts ) > 1 ) {
						array_pop( $base_parts );
					}
				} elseif ( '.' !== $part && '' !== $part ) {
					// Append valid segments.
					$base_parts[] = $part;
				}
			}

			// If there is a trailing slash in the permalink add it to the $trailing_slash string.
			$trailing_slash = str_ends_with( $relative_url, '/' ) ? '/' : '';

			// Construct the final absolute URL and return it.
			return $post_permalink_parts['scheme'] . '://' . $post_permalink_parts['host'] . implode( '/', $base_parts ) . $trailing_slash;

		}

		/**
		 * Page-relative URLs.
		 *
		 * Handle relative URLs without a leading slash (page-relative URLs like "example-post/").
		 */
		$base_parts = wp_parse_url( $post_permalink );
		return $base_parts['scheme'] . '://' . $base_parts['host'] . $base_parts['path'] . $relative_url;

	}

	/**
	 * A regex to match internal links. Specifically absolute internal links and relative internal links.
	 *
	 * @return string The regex to match manual and auto internal links.
	 */
	public function internal_links_regex() {

		/**
		 * Get the website URL and escape the regex character. # and
		 * whitespace ( used with the 'x' modifier ) are not escaped, thus
		 * should not be included in the $site_url string
		 */
		$site_url = preg_quote( get_home_url(), '{' );

		// Get the website URL without the protocol part.
		$site_url_without_protocol_part = preg_quote( wp_parse_url( get_home_url(), PHP_URL_HOST ), '{' );

		return '{<a                                                     #1 Begin the element a start-tag
            [^>]+                                                       #2 Any character except > at least one time
            href\s*=\s*                                                 #3 Equal may have whitespaces on both sides
            ([\'"]?)                                                    #4 Match double quotes, single quote or no quote ( captured for the backreference \1 )
	        (                                                           #5 Capture group for both full and relative URLs
	            (?:' . $site_url . '[^\'">\s]*)                         #5a Match full URL starting with $site_url ( captured )
	            |                                                       # OR
	            (?!//)(?:\/|\.{1,2}\/)[^\'">\s]*                        #5b Match relative URLs (must start with /, ./, or ../) ( captured )
	                        |                                           # OR
                \#[^\'">\s]*                                            #5c Match fragment-only URLs (e.g., #section2) ( captured )
                            |                                           # OR
				(?!//)[^\'"\s<>:]+                                      #5d Match page-relative URLs (must not contain "://") (captured)
				|                                                       # OR
                (?://' . $site_url_without_protocol_part . '[^\'">\s]*) #5e Match protocol-relative URLs with $site_url_without_protocol_part (captured)
	        )    
            \1                                                          #6 Backreference that matches the href value delimiter matched at line 4
            [^>]*                                                       #7 Any character except > zero or more times
            >                                                           #8 End of the start-tag
            (.*?)                                                       #9 Link text or nested tags. After the dot ( enclose in parenthesis ) negative lookbehinds can be applied to avoid specific stuff inside the link text or nested tags. Example with single negative lookbehind (.(?<!word1))*? Example with multiple negative lookbehind (.(?<!word1)(?<!word2)(?<!word3))*?
            <\/a\s*>                                                    #10 Element a end-tag with optional white-spaces characters before the >
            }ix';
	}

	/**
	 * A regex to match any link (internal links, external links, and relative links).
	 *
	 * @return string The regex to match manual and auto internal links.
	 */
	public function links_regex() {

		return '{<a                             #1 Begin the element a start-tag
            [^>]+                               #2 Any character except > at least one time
            href\s*=\s*                         #3 Equal may have whitespaces on both sides
            ([\'"]?)                            #4 Match double quotes, single quote or no quote ( captured for the backreference \1 )
            [^\'">\s]+                          #5 The site URL
            \1                                  #6 Backreference that matches the href value delimiter matched at line 4     
            [^>]*                               #7 Any character except > zero or more times
            >                                   #8 End of the start-tag
            .*?                                 #9 Link text or nested tags. After the dot ( enclose in parenthesis ) negative lookbehinds can be applied to avoid specific stuff inside the link text or nested tags. Example with single negative lookbehind (.(?<!word1))*? Example with multiple negative lookbehind (.(?<!word1)(?<!word2)(?<!word3))*?
            <\/a\s*>                            #10 Element a end-tag with optional white-spaces characters before the >
            }ix';

	}

	/**
	 * Checks if the WordPress site URL is using the HTTPS protocol.
	 *
	 * @return bool Returns true if the site URL starts with https:// (i.e., the site is using HTTPS). false if the site
	 * URL does not start with https:// (i.e., the site is using HTTP).
	 */
	public function is_site_using_https() {

		$site_url = get_option( 'siteurl' );

		return ( str_starts_with( $site_url, 'https://' ) );
	}

}
