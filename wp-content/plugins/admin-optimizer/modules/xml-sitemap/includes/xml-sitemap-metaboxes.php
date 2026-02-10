<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML_Sitemap_Metaboxes class
 */
class XML_Sitemap_Metaboxes {

	/**
	 * User Options
	 *
	 * @var false|mixed|null
	 */
	protected $options;

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
	public function init() {
		add_action( 'add_meta_boxes', [ $this, 'add_xml_exclude_metaboxes' ] );
		$post_types = $this->options['post_types'] ?? [ 'post' ];
		if ( in_array( 'post', $post_types, true ) || in_array( 'page', $post_types, true ) ) {
			add_action( 'save_post', [ $this, 'save_xml_settings' ], 10, 2 );
		} else {
			foreach ( $post_types as $post_type ) {
				add_action( "save_post_{$post_type}", [ $this, 'save_xml_settings' ], 10, 2 );
			}
		}
	}

	/**
	 * Add the meta box to the supported post types.
	 *
	 * @return void
	 */
	public function add_xml_exclude_metaboxes() {
		$screens              = get_post_types( '', 'names' );
		$supported_post_types = $this->options['post_types'] ?? [ 'post' ];

		foreach ( $screens as $screen ) {
			if ( in_array( $screen, $supported_post_types, true ) ) {
				if ( current_user_can( 'publish_posts' ) || current_user_can( 'manage_options' ) ) {
					add_meta_box(
						'adminoptim_xml_exclude_div',
						__( 'XML Sitemap', 'admin-optimizer' ),
						[ $this, 'render_metabox' ],
						$screen,
						'side'
					);
				}
			}
		}
	}

	/**
	 * Render the meta box content.
	 *
	 * @param \WP_Post $post The post object.
	 * @return void
	 */
	public function render_metabox( $post ) {
		// Use nonce for verification.
		wp_nonce_field( 'adminoptim_xml_post', 'adminoptim_xml_nonce', false );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form.
		$exclude = get_post_meta( $post->ID, '_adminoptim_xml_exclude', true ) ?? '0';
		?>
		<p>
			<label>
				<input type="checkbox" name="adminoptim_xml_exclude" id="xml-exclude-post" value="1"<?php checked( ! empty( $exclude ) ); ?> />
				<?php esc_html_e( 'Exclude from XML Sitemap', 'admin-optimizer' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Save the meta box settings.
	 *
	 * @param int      $post_id The post ID.
	 * @param \WP_Post $post    The post object.
	 * @return void
	 */
	public function save_xml_settings( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// First we need to check if the current user is authorised to do this action.
		if ( ! current_user_can( 'publish_posts' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Secondly we need to check if the user intended to change this value.
		if ( ! isset( $_POST['adminoptim_xml_nonce'] ) ) {
			return;
		}
		$nonce = sanitize_key( $_POST['adminoptim_xml_nonce'] );
		if ( empty( $nonce ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce, 'adminoptim_xml_post' ) ) {
			return;
		}

		if ( empty( $post_id ) || empty( $post->post_type ) ) {
			return;
		}

		$user_exclude = '0';
		if ( isset( $_POST['adminoptim_xml_exclude'] ) ) {
			$user_exclude = sanitize_text_field( wp_unslash( $_POST['adminoptim_xml_exclude'] ) );
		}
		$current_exclude = get_post_meta( $post->ID, '_adminoptim_xml_exclude', true ) ?? '0';
		if ( $user_exclude !== $current_exclude ) {
			if ( '1' === $user_exclude ) {
				update_post_meta( $post_id, '_adminoptim_xml_exclude', '1' );
			} else {
				delete_post_meta( $post_id, '_adminoptim_xml_exclude' );
			}
		}
	}
}
