<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use const Yipresser\AdminOptimizer\Admin\MODULES_OPTION;

/**
 * Content Management Class
 */
class Content_Management {
	/**
	 * User options
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * List of modules
	 *
	 * @var array
	 */
	protected $modules = [];

	/**
	 * Constructor
	 *
	 * @param array $options User options.
	 */
	public function __construct( $options ) {
		$this->options = $options;
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	private function init() {
		add_filter( 'adminoptimizer_settings_navtab', [ $this, 'add_settings_navtab' ] );
		add_filter( 'adminoptimizer_settings_sections', [ $this, 'settings_fields' ] );

		if ( ! empty( $this->options['enable_custom_post_status'] ) ) {
			$this->modules['post_status'] = new Post_Status();
		}
		if ( ! empty( $this->options['enable_custom_post_types'] ) ) {
			$this->modules['post_types'] = new Post_Types();
		}
		if ( ! empty( $this->options['enable_custom_taxonomies'] ) ) {
			$this->modules['taxonomies'] = new Taxonomies();
		}
		if ( ! empty( $this->options['enable_publish_missed_posts'] ) ) {
			$this->modules['auto_publish'] = new Publish_Missed_Schedule();
		} else {
			add_action( 'init', [ $this, 'maybe_remove_publication_check_schedule' ], 20 );
		}
		if ( ! empty( $this->options['enable_auto_open_advanced'] ) ) {
			$this->modules['auto_open_advanced'] = new Auto_Open_Advanced();
		}
		if ( ! empty( $this->options['enable_auto_add_anchor'] ) ) {
			add_filter( 'block_editor_settings_all', [ $this, 'enable_anchor_target' ] );
		}
		if ( ! empty( $this->options['manage_post_modified_date'] ) ) {
			$this->modules['modified_date'] = new Modified_Date();
		}
		if ( ! empty( $this->options['enable_post_cloner'] ) ) {
			$this->modules['post_cloner'] = new \Yipresser\AdminOptimizer\Modules\Post_Cloner\Post_Cloner();
		}
	}

	/**
	 * Add settings navtab
	 *
	 * @param array $nav_tab List of nav tabs.
	 *
	 * @return array
	 */
	public function add_settings_navtab( $nav_tab ) {
		if ( empty( $nav_tab['content-management'] ) ) {
			$nav_tab['content-management'] = [
				'title' => __( 'Content Management', 'admin-optimizer' ),
				'slug'  => 'adminoptim-content-settings',
			];
		}
		return $nav_tab;
	}

	/**
	 * List of Settings fields
	 *
	 * @param array $fields Settings fields.
	 *
	 * @return array
	 */
	public function settings_fields( $fields ) {
		if ( empty( $fields['content-management'] ) ) {
			$modules                      = [
				'post-status'        => [
					'type'  => 'slider-checkbox',
					'title' => __( 'Enable Custom Post Status', 'admin-optimizer' ),
					'id'    => 'enable-custom-post-status',
					'name'  => 'enable_custom_post_status',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Enable the creation of custom post status. (comes with Pro options)%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/custom-post-status/' ) . '" target="_blank">', '</a>' ),
				],
				'post-types'         => [
					'type'  => 'slider-checkbox',
					'title' => __( 'Enable Custom Post Types', 'admin-optimizer' ),
					'id'    => 'enable-custom-post-types',
					'name'  => 'enable_custom_post_types',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Enable the creation of custom post types.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/custom-post-type/' ) . '" target="_blank">', '</a>' ),
				],
				'taxonomies'         => [
					'type'  => 'slider-checkbox',
					'title' => __( 'Enable Custom Taxonomies', 'admin-optimizer' ),
					'id'    => 'enable-custom-taxonomies',
					'name'  => 'enable_custom_taxonomies',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Enable the creation of custom taxonomies.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/custom-taxonomies/' ) . '" target="_blank">', '</a>' ),
				],
				'auto-publish'       => [
					'type'  => 'slider-checkbox',
					'title' => __( 'Auto publish posts with missed schedule', 'admin-optimizer' ),
					'id'    => 'enable-publish-missed-posts',
					'name'  => 'enable_publish_missed_posts',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Check posts every 15 minutes and auto publish posts with missed schedule.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/auto-publish-posts/' ) . '" target="_blank">', '</a>' ),
				],
				'auto-open-advanced' => [
					'type'  => 'slider-checkbox',
					'title' => __( 'Auto open Advanced field in Gutenberg on mouse hover', 'admin-optimizer' ),
					'id'    => 'enable-auto-open-advanced',
					'name'  => 'enable_auto_open_advanced',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Reduce mouse clicks and open the Advanced field in Gutenberg when you move your mouse over it.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/auto-open-advanced-field-in-gutenberg/' ) . '" target="_blank">', '</a>' ),
				],
				'auto-add-anchor'    => [
					'type'  => 'slider-checkbox',
					'title' => __( 'Auto Add HTML Anchor to Heading Block', 'admin-optimizer' ),
					'id'    => 'enable-auto-add-anchor',
					'name'  => 'enable_auto_add_anchor',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Transform your WordPress headings into clickable link targets, making it easier for readers to navigate through your posts and share specific sections.<br/>(only works in Gutenberg editor)%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/auto-add-html-anchor/' ) . '" target="_blank">', '</a>' ),
				],
				'modified-date'      => [
					'type'  => 'slider-checkbox',
					'title' => __( 'Enable locking of Post\'s Modification Date.', 'admin-optimizer' ),
					'id'    => 'manage-post-modified-date',
					'name'  => 'manage_post_modified_date',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Change or prevent updating of the last modified date for each post. (comes with Pro options)%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/lock-modified-date/' ) . '" target="_blank">', '</a>' ),
				],
				'post-cloner'        => [
					'type'  => 'slider-checkbox',
					'title' => __( 'Enable Post Cloning', 'admin-optimizer' ),
					'id'    => 'enable-post-cloner',
					'name'  => 'enable_post_cloner',
					'label' => __( 'Duplicate posts, pages and custom posts with a single click. Update and republish content easily with this module. (comes with Pro options)', 'admin-optimizer' ),
				],
				'post-republisher'   => [
					'type'     => 'slider-checkbox',
					'title'    => __( 'Enable Post Republishing (Pro)', 'admin-optimizer' ),
					'id'       => 'enable-post-republisher',
					'name'     => 'enable_post_republisher',
					'disabled' => 'disabled',
					'label'    => '<span class="pro_tag">Pro</span>' . __( 'Clone a post as a child of the Post. When you (re)publish the cloned post, it will update the orignal post instead of publishing as a new post. This allows you to update old content quickly and easily.', 'admin-optimizer' ),
				],
			];
			$fields['content-management'] = [
				'id'          => 'adminoptimizer-content-settings',
				'title'       => '',
				'description' => '',
				'menu_slug'   => 'adminoptim-content-settings',
				'option_name' => MODULES_OPTION,
				'fields'      => $modules,
			];
		}
		return $fields;
	}

	/**
	 * Remove check missed post schedule
	 *
	 * @return void
	 */
	public function maybe_remove_publication_check_schedule() {
		if ( function_exists( 'as_has_scheduled_action' ) && as_has_scheduled_action( 'adminoptim_publish_missed_post' ) ) {
			as_unschedule_action( 'adminoptim_publish_missed_post' );
		}
	}

	/**
	 * Auto add anchor target to Headings block
	 *
	 * @param array $editor_settings Editor settings.
	 *
	 * @return array
	 */
	public function enable_anchor_target( $editor_settings ) {
		$editor_settings['generateAnchors'] = true;
		return $editor_settings;
	}
}
