<?php
namespace Yipresser\AdminOptimizer\Modules\Post_Cloner;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * Post Cloner Settings Class
 */
class Post_Cloner_Settings extends WP_Settings_API_Helper {
	/**
	 * The options value stored in the database
	 *
	 * @var false|array
	 */
	protected $options;

	/**
	 * Constructor
	 *
	 * @param array $options Database options.
	 */
	public function __construct( $options ) {
		$this->options = $options;
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
				'option_group' => Post_Cloner::MENU_SLUG,
				'option_name'  => Post_Cloner::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			'free' => [
				'id'          => 'post-cloner-settings',
				'title'       => 'Settings for posts cloning',
				'description' => '',
				'menu_slug'   => Post_Cloner::MENU_SLUG,
				'option_name' => Post_Cloner::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'callback',
						'title'    => __( 'Allow cloning for these Post Types', 'admin-optimizer' ),
						'id'       => 'post-types-support',
						'name'     => 'post_types',
						'desc'     => '',
						'callback' => [ $this, 'render_post_types_fields' ],
					],
					[
						'type'    => 'radio',
						'title'   => __( 'After cloning', 'admin-optimizer' ),
						'id'      => 'after-cloning',
						'name'    => 'after_cloning',
						'choices' => [
							'return' => __( 'Return to the list of posts', 'admin-optimizer' ),
							'edit'   => __( 'Open the cloned post for editing', 'admin-optimizer' ),
						],
						'default' => 'return',
						'desc'    => '',
					],
				],
			],
			'pro'  => [
				'id'          => 'post-cloner-user-settings',
				'title'       => '',
				/* translators: %1$s is the anchor link to the Pro version. %2$s is the closing anchor tag */
				'description' => sprintf( __( 'Upgrade to the %1$sPro version%2$s to grant clone permission to users.', 'admin-optimizer' ), '<a href="' . esc_url( 'https://www.adminoptimizer.com/#pricing' ) . '" target="_blank">', '</a>' ),
				'menu_slug'   => Post_Cloner::MENU_SLUG . '_pro',
				'option_name' => Post_Cloner::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'callback',
						'title'    => __( 'Allow these user roles to clone posts', 'admin-optimizer' ),
						'id'       => 'clone-roles',
						'name'     => 'clone_roles',
						'desc'     => '',
						'callback' => [ $this, 'render_roles_permission_fields' ],
						'param'    => 'clone',
					],
				],
			],
		];

		$this->setup();
	}

	/**
	 * Function to render the post types field
	 *
	 * @return void
	 */
	public function render_post_types_fields() {
		$custom_post_types = get_post_types(
			[
				'show_ui'  => true,
				'_builtin' => false,
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
			<?php
			$checked = '';
			if ( isset( $this->options['post_types'] ) && is_array( $this->options['post_types'] ) && in_array( $post_type_name, $this->options['post_types'], true ) ) {
				$checked = ' checked="checked"';
			}
			?>
			<label for="<?php echo esc_attr( $post_type_name ); ?>"><input id="<?php echo esc_attr( $post_type_name ); ?>" name="<?php echo esc_attr( Post_Cloner::OPTION_NAME . '[post_types][]' ); ?>" type="checkbox" <?php echo esc_attr( $checked ); ?> value="<?php echo esc_attr( $post_type_name ); ?>"><?php echo esc_html( $post_type_label ); ?></label><br/>
			<?php
		endforeach;
	}

	/**
	 * Function to render the user roles
	 *
	 * @param array  $args Field argument.
	 * @param string $clone_republish Whether it is clone or republish action.
	 *
	 * @return void
	 */
	public function render_roles_permission_fields( $args, $clone_republish ) {
		$roles = get_editable_roles();

		foreach ( $roles as $role => $details ) {
			$name     = translate_user_role( $details['name'] );
			$checked  = '';
			$disabled = 'disabled';
			if ( 'administrator' === $role ) {
				$checked = ' checked="checked"';
			}
			?>
			<label for="<?php echo esc_attr( $role ); ?>"><input id="<?php echo esc_attr( $role ); ?>" name="<?php echo esc_attr( Post_Cloner::OPTION_NAME . '[clone_roles][]' ); ?>" type="checkbox" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?> value="<?php echo esc_attr( $role ); ?>"><?php echo esc_html( $name ); ?></label><br/>
			<?php
		}
		?>
		<?php
	}

	/**
	 * Render Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer - Post Cloner', 'admin-optimizer' ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_settings_on_page( Post_Cloner::MENU_SLUG ); ?>
			<div class="adminoptim-pro-options">
				<?php $this->render_settings_on_page( Post_Cloner::MENU_SLUG . '_pro', [ 'remove_submit_button' => true ] ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Sanitize fields before saving to database
	 *
	 * @param array $option Saved options to be sanitized.
	 *
	 * @return array
	 */
	public function sanitize_settings( $option ) {
		$sanitized_option = [];
		if ( is_array( $option ) ) {
			if ( isset( $option['post_types'] ) ) {
				$custom_post_types = array_unique(
					array_merge(
						[ 'post', 'page' ],
						array_values(
							get_post_types(
								[
									'show_ui'  => true,
									'_builtin' => false,
								]
							)
						)
					)
				);
				if ( is_array( $option['post_types'] ) ) {
					$selected = array_intersect( $option['post_types'], $custom_post_types );
					if ( ! empty( $selected ) ) {
						$sanitized_option['post_types'] = $selected;
					} else {
						$sanitized_option['post_types'] = [ 'post' ];
					}
				} else {
					$sanitized_option['post_types'] = [ 'post' ];
				}
			}

			if ( isset( $option['after_cloning'] ) ) {
				$after_cloning = $option['after_cloning'];
				if ( in_array( $after_cloning, [ 'edit', 'return' ], true ) ) {
					$sanitized_option['after_cloning'] = $after_cloning;
				} else {
					$sanitized_option['after_cloning'] = 'return';
				}
			}
		}

		return $sanitized_option;
	}

	/**
	 * Enqueue scripts on the Settings page
	 *
	 * @param string $hook_suffix  The hook suffix to check if we are on the right page.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, Post_Cloner::MENU_SLUG ) ) {
			wp_enqueue_style( 'adminoptim-modules-pro-settings' );
		}
	}
}
