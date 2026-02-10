<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * Modified_Date_Settings class
 */
class Modified_Date_Settings extends WP_Settings_API_Helper {
	/**
	 * User options
	 *
	 * @var array
	 */
	protected $option;

	/**
	 * Constructor
	 *
	 * @param array $option User Options.
	 */
	public function __construct( $option ) {
		$this->option = $option;
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings_options = [
			[
				'option_group' => Modified_Date::MENU_SLUG,
				'option_name'  => Modified_Date::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			'free' => [
				'id'          => 'adminoptimizer-modified-date-default',
				'title'       => '',
				'description' => '',
				'menu_slug'   => Modified_Date::MENU_SLUG,
				'option_name' => Modified_Date::OPTION_NAME,
				'fields'      => [
					[
						'type'  => 'checkbox',
						'title' => __( 'Lock modification date by default', 'admin-optimizer' ),
						'id'    => 'post-modified-date',
						'name'  => 'disable_modified_date_update',
						'label' => __( 'Prevent any user from updating the modified post. Can be overridden for each post.', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Only lock modified date for Published post', 'admin-optimizer' ),
						'id'    => 'publish-post-modified-date',
						'name'  => 'publish_only',
						'label' => __( 'The modification date of post with different post status will not be locked.', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Add a Modified Date column to Posts section', 'admin-optimizer' ),
						'id'    => 'post-modified-date-column',
						'name'  => 'add_modified_date_column',
						'label' => __( 'Sort the post oder by Modified Date', 'admin-optimizer' ),
					],
				],
			],
			'pro'  => [
				'id'          => 'adminoptimizer-modified-date-pro',
				'title'       => __( 'Pro Options', 'admin-optimizer' ),
				/* translators: %1$s is the anchor link to the Pro version. %2$s is the closing anchor tag */
				'description' => sprintf( __( 'Upgrade to the %1$sPro version%2$s to access these features', 'admin-optimizer' ), '<a href="' . esc_url( 'https://www.adminoptimizer.com/#pricing' ) . '" target="_blank">', '</a>' ),
				'menu_slug'   => Modified_Date::MENU_SLUG . '_pro',
				'option_name' => Modified_Date::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'callback',
						'title'    => __( 'Lock modified date for the following Post Types', 'admin-optimizer' ),
						'id'       => 'post-types-support',
						'name'     => 'post_types',
						'desc'     => '',
						'callback' => [ $this, 'render_post_types_fields' ],
					],
					[
						'type'     => 'callback',
						'id'       => 'modified-date-edit-roles',
						'name'     => 'edit_roles',
						'title'    => __( 'Only allow these user roles to make changes to the Modified Date', 'admin-optimizer' ),
						'callback' => [ $this, 'render_edit_roles_checkboxes' ],
						'desc'     => __( 'Only these roles can lock or update the modified date.', 'admin-optimizer' ),
					],
				],
			],
		];
		$this->settings_sections = apply_filters( 'adminoptim_modified_date_sections', $this->settings_sections );
		$this->setup();
	}

	/**
	 * Enqueue scripts on the Settings page
	 *
	 * @param string $hook_suffix  The hook suffix to check if we are on the right page.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, Modified_Date::MENU_SLUG ) ) {
			wp_enqueue_style( 'adminoptim-modules-pro-settings' );
		}
	}

	/**
	 * Callback function for rendering custom post types field.
	 *
	 * @return void
	 */
	public function render_post_types_fields() {
		$custom_post_types = get_post_types(
			[
				'show_ui'  => true,
				'_builtin' => false,
				'public'   => true,
			],
			'objects'
		);
		$post_types        = [];

		foreach ( $custom_post_types as $custom_post_type ) {
			$post_types[ $custom_post_type->name ] = $custom_post_type->label;
		}
		$post_types = array_merge(
			[
				'post' => 'Post',
				'page' => 'Page',
			],
			$post_types
		);

		foreach ( $post_types as $post_type_name => $post_type_label ) : ?>
			<label for="<?php echo esc_attr( $post_type_name ); ?>"><input id="<?php echo esc_attr( $post_type_name ); ?>" name="<?php echo esc_attr( Modified_Date::OPTION_NAME . '[post_types][]' ); ?>" type="checkbox" value="" disabled="disabled"><?php echo esc_html( $post_type_label ); ?></label><br/>
			<?php
		endforeach;
	}

	/**
	 * Render edit_roles checkboxes field
	 *
	 * @param array $field  setting field.
	 *
	 * @return void
	 */
	public function render_edit_roles_checkboxes( $field ) {
		$option_name = Modified_Date::OPTION_NAME;

		$roles = get_editable_roles();

		if ( ! empty( $field['desc'] ) ) {
			echo '<p>' . esc_html( $field['desc'] ) . '<br/></p>';
		}

		foreach ( $roles as $role => $role_details ) {
			$name     = translate_user_role( $role_details['name'] );
			$disabled = ' disabled="disabled"';
			?>
			<label for="<?php echo esc_attr( $role ); ?>"><input id="<?php echo esc_attr( $role ); ?>" name="<?php echo esc_attr( $option_name ); ?>[edit_roles][]" type="checkbox" <?php echo esc_attr( $disabled ); ?> value=""><?php echo esc_html( $name ); ?></label><br/>
			<?php
		}
	}

	/**
	 * Callback function to sanitize user options
	 *
	 * @param array $options User options.
	 *
	 * @return array
	 */
	public function sanitize_settings( $options ) {
		$sanitized_options = [];
		if ( is_array( $options ) ) {
			if ( isset( $options['disable_modified_date_update'] ) ) {
				$sanitized_options['disable_modified_date_update'] = '1';
			}
			if ( isset( $options['publish_only'] ) ) {
				$sanitized_options['publish_only'] = '1';
			}
			if ( isset( $options['add_modified_date_column'] ) ) {
				$sanitized_options['add_modified_date_column'] = '1';
			}
		}

		return apply_filters( 'adminoptim_sanitize_modified_date_settings', $sanitized_options, $options );
	}


	/**
	 * Render Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer - Lock Modification Date Settings', 'admin-optimizer' ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_settings_on_page( Modified_Date::MENU_SLUG ); ?>
			<div class="adminoptim-pro-options">
				<?php $this->render_settings_on_page( Modified_Date::MENU_SLUG . '_pro', [ 'remove_submit_button' => true ] ); ?>
			</div>
		</div>
		<?php
	}
}