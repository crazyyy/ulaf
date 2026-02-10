<?php
/**
 * Used to generate the data used in the options menu powered by React.
 *
 * @package daext-interlinks-manager
 */

/**
 * This menu_options_configuration() method of this class is used to generate the data used in the options menu powered
 * by React.
 */
class Daextinma_Menu_Options {

	/**
	 * The singleton instance of the class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns an array with the data used by the React based options menu to initialize the options.
	 *
	 * @return array[]
	 */
	public function menu_options_configuration() {

		// Get the public post types that have a UI.
		$args               = array(
			'public'  => true,
			'show_ui' => true,
		);
		$post_types_with_ui = get_post_types( $args );
		unset( $post_types_with_ui['attachment'] );
		$post_types_select_options = array();
		foreach ( $post_types_with_ui as $post_type ) {
			$post_type_obj               = get_post_type_object( $post_type );
			$post_types_select_options[] = array(
				'value' => $post_type,
				'text'  => $post_type_obj->label,
			);
		}

		// This variable includes all the data used by the configuration options.
		$configuration = array(

			array(
				'title'       => __( 'Link Analysis', 'daext-interlinks-manager' ),
				'description' => __( 'Configure options and parameters used for the link analysis.', 'daext-interlinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Juice', 'daext-interlinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daextinma_default_seo_power',
								'label'     => __( 'SEO Power (Default)', 'daext-interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'Please enter a number from 100 to 1000000 in the "SEO Power (Default)" option.',
									'daext-interlinks-manager'
								),
								'help'      => __( 'Set the default SEO power of the posts.', 'daext-interlinks-manager' ),
								'rangeMin'  => 100,
								'rangeMax'  => 1000000,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daextinma_penality_per_position_percentage',
								'label'     => __( 'Penality per Position (%)', 'daext-interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'With multiple links in an article, the algorithm that calculates the "Link Juice" passed by each link removes a percentage of the passed "Link Juice" based on the position of a link compared to the other links.',
									'daext-interlinks-manager'
								),
								'help'      => __( 'Set the penality per position percentage.', 'daext-interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 100,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'daextinma_remove_link_to_anchor',
								'label'   => __( 'Remove Fragment Identifier', 'daext-interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" to automatically remove links to anchors from every URL used to calculate the link juice. With this option enabled "http://example.com" and "http://example.com#myanchor" will both contribute to generate link juice only for a single URL, that is "http://example.com".', 'daext-interlinks-manager' ),
								'help'    => __(
									'Remove the fragment identifier from the URL.',
									'daext-interlinks-manager'
								),
							),
							array(
								'name'    => 'daextinma_remove_url_parameters',
								'label'   => __( 'Remove URL Parameters', 'daext-interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" to automatically remove the URL parameters from every URL used to calculate the link juice. With this option enabled "http://example.com" and "http://example.com?param=1" will both contribute to generate link juice only for a single URL, that is "http://example.com". Please note that this option should not be enabled if your website is using URL parameters to actually identify specific pages. (for example with pretty permalinks not enabled)', 'daext-interlinks-manager' ),
								'help'    => __(
									'Remove the parameters from the URL.',
									'daext-interlinks-manager'
								),
							),
						),
					),
					array(
						'title'   => __( 'Technical Options', 'daext-interlinks-manager' ),
						'options' => array(
							array(
								'name'    => 'daextinma_set_max_execution_time',
								'label'   => __( 'Set Max Execution Time', 'daext-interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" to enable your custom "Max Execution Time Value" on long running scripts.', 'daext-interlinks-manager' ),
								'help'    => __(
									'Enable a custom max execution time value.',
									'daext-interlinks-manager'
								),
							),
							array(
								'name'      => 'daextinma_max_execution_time_value',
								'label'     => __( 'Max Execution Time Value', 'daext-interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the maximum number of seconds allowed to execute long running scripts.',
									'daext-interlinks-manager'
								),
								'help'      => __( 'Set the max execution time value.', 'daext-interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 3600,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'daextinma_set_memory_limit',
								'label'   => __( 'Set Memory Limit', 'daext-interlinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" to enable your custom "Memory Limit Value" on long running scripts.', 'daext-interlinks-manager' ),
								'help'    => __(
									'Enable a custom memory limit.',
									'daext-interlinks-manager'
								),
							),
							array(
								'name'      => 'daextinma_memory_limit_value',
								'label'     => __( 'Memory Limit Value', 'daext-interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the PHP memory limit in megabytes allowed to execute long running scripts.',
									'daext-interlinks-manager'
								),
								'help'      => __( 'Set the memory limit value.', 'daext-interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 16384,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daextinma_limit_posts_analysis',
								'label'     => __( 'Limit Posts Analysis	', 'daext-interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'With this options you can determine the maximum number of posts analyzed to get information about your internal links, to get information about the internal links juice and to get suggestions in the "Interlinks Suggestions" meta box. If you select for example "1000", the analysis performed by the plugin will use your latest "1000" posts.',
									'daext-interlinks-manager'
								),
								'help'      => __( 'Limit the maximum number of analyzed posts.', 'daext-interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 100000,
								'rangeStep' => 1,
							),
							array(
								'name'          => 'daextinma_dashboard_post_types',
								'label'         => __( 'Dashboard Post Types', 'daext-interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine the post types analyzed in the Dashboard menu.',
									'daext-interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types analyzed in the Dashboard menu.', 'daext-interlinks-manager' ),
							),
							array(
								'name'          => 'daextinma_juice_post_types',
								'label'         => __( 'Juice Post Types', 'daext-interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine the post types analyzed in the Juice menu.',
									'daext-interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types analyzed in the Juice menu.', 'daext-interlinks-manager' ),
							),
							array(
								'name'          => 'daextinma_internal_links_data_update_frequency',
								'label'         => __( 'Internal Links Data Update Frequency', 'daext-interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The frequency of the automatic data updates performed in the Dashboard menu.',
									'daext-interlinks-manager'
								),
								'selectOptions' => array(
									array(
										'value' => 'never',
										'text'  => __( 'Never', 'daext-interlinks-manager' ),
									),
									array(
										'value' => 'hourly',
										'text'  => __( 'Hourly', 'daext-interlinks-manager' ),
									),
									array(
										'value' => 'daily',
										'text'  => __( 'Daily', 'daext-interlinks-manager' ),
									),
									array(
										'value' => 'weekly',
										'text'  => __( 'Weekly', 'daext-interlinks-manager' ),
									),
									array(
										'value' => 'monthly',
										'text'  => __( 'Monthly', 'daext-interlinks-manager' ),
									),
								),
								'help'          => __( 'Select the frequency of the automatic data updates performed in the Dashboard menu.', 'daext-interlinks-manager' ),
							),
							array(
								'name'          => 'daextinma_juice_data_update_frequency',
								'label'         => __( 'Juice Data Update Frequency', 'daext-interlinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The frequency of the automatic data updates performed in the Juice menu.',
									'daext-interlinks-manager'
								),
								'selectOptions' => array(
									array(
										'value' => 'never',
										'text'  => __( 'Never', 'daext-interlinks-manager' ),
									),
									array(
										'value' => 'hourly',
										'text'  => __( 'Hourly', 'daext-interlinks-manager' ),
									),
									array(
										'value' => 'daily',
										'text'  => __( 'Daily', 'daext-interlinks-manager' ),
									),
									array(
										'value' => 'weekly',
										'text'  => __( 'Weekly', 'daext-interlinks-manager' ),
									),
									array(
										'value' => 'monthly',
										'text'  => __( 'Monthly', 'daext-interlinks-manager' ),
									),
								),
								'help'          => __( 'Select the frequency of the automatic data updates performed in the Juice menu.', 'daext-interlinks-manager' ),
							),
						),
					),
				),
			),

			array(
				'title'       => __( 'Advanced', 'daext-interlinks-manager' ),
				'description' => __( 'Manage advanced plugin settings.', 'daext-interlinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Optimization Parameters', 'daext-interlinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daextinma_optimization_num_of_characters',
								'label'     => __( 'Characters per Interlink', 'daext-interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'The "Recommended Interlinks" value available in the "Dashboard" menu and in the "Interlinks Optimization" meta box is based on the defined "Characters per Interlink" and on the content length of the post. For example if you define 500 "Characters per Interlink", in the "Dashboard" menu, with a post that has a content length of 2000 characters you will get 4 as the value for the "Recommended Interlinks".',
									'daext-interlinks-manager'
								),
								'help'      => __( 'Set the optimal number of characters per internal link.', 'daext-interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 1000000,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daextinma_optimization_delta',
								'label'     => __( 'Optimization Delta	', 'daext-interlinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'The "Optimization Delta" is used to generate the "Optimization Flag" available in the "Dashboard" menu and the text message diplayed in the "Interlinks Optimization" meta box. This option determines how different can be the actual number of interlinks in a post from the calculated "Recommended Interlinks". This option defines a range, so for example in a post with 10 "Recommended Interlinks" and this option value equal to 4, the post will be considered optimized when it includes from 8 to 12 interlinks.',
									'daext-interlinks-manager'
								),
								'help'      => __( 'Set how different can the number of internal links of a post from the optimal value.', 'daext-interlinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 1000000,
								'rangeStep' => 1,
							),
						),
					),
					array(
						'title'   => __( 'Meta Boxes', 'daext-interlinks-manager' ),
						'options' => array(
							array(
								'name'          => 'daextinma_interlinks_options_post_types',
								'label'         => __( 'Interlinks Options Post Types', 'daext-interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which post types the "Interlinks Options" meta box should be loaded.',
									'daext-interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types where the "Interlinks Options" meta box should be loaded.', 'daext-interlinks-manager' ),
							),
							array(
								'name'          => 'daextinma_interlinks_optimization_post_types',
								'label'         => __( 'Interlinks Optimization Post Types', 'daext-interlinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which post types the "Interlinks Optimization" meta box should be loaded.',
									'daext-interlinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types where the "Interlinks Options" meta box should be loaded.', 'daext-interlinks-manager' ),
							),
						),
					),
				),
			),

		);

		return $configuration;
	}
}
