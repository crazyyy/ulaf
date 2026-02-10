<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * XML Sitemap Settings class
 */
class XML_Sitemap_Settings extends WP_Settings_API_Helper {
	/**
	 * User options
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * SEO Plugins array
	 *
	 * @var array
	 */
	protected $seo_plugins = [
		'yoast'      => [
			'name' => 'Yoast SEO',
			'path' => 'wordpress-seo/wp-seo.php',
		],
		'aioseo'     => [
			'name' => 'All in One SEO',
			'path' => 'all-in-one-seo-pack/all-in-one-seo-pack.php',
		],
		'rankmath'   => [
			'name' => 'Rank Math SEO',
			'path' => 'seo-by-rank-math/rank-math.php',
		],
		'seopress'   => [
			'name' => 'SEOPress',
			'path' => 'wp-seopress/wp-seopress.php',
		],
		'slimseo'    => [
			'name' => 'Slim SEO',
			'path' => 'slim-seo/slim-seo.php',
		],
		'tsf'        => [
			'name' => 'The SEO Framework',
			'path' => 'the-seo-framework/index.php',
		],
		'smartcrawl' => [
			'name' => 'SmartCrawl SEO',
			'path' => 'wpmudev-updates/plugin-updates.php',
		],
	];

	/**
	 * Constructor
	 *
	 * @param array $options User options.
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
				'option_group' => XML_Sitemap::MENU_SLUG,
				'option_name'  => XML_Sitemap::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			'free' => [
				'id'          => 'adminoptimizer-xml-sitemap-section',
				'title'       => '',
				'menu_slug'   => XML_Sitemap::MENU_SLUG,
				'option_name' => XML_Sitemap::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'callback',
						'title'    => __( 'Sitemap URL', 'admin-optimizer' ),
						'id'       => 'custom-sitemap-url',
						'name'     => 'custom_sitemap_slug',
						'callback' => [ $this, 'render_custom_sitemap_url_field' ],
					],
					[
						'type'     => 'callback',
						'title'    => __( 'Post Types', 'admin-optimizer' ),
						'id'       => 'sitemap-post-types',
						'name'     => 'post_types',
						'desc'     => __( 'Include these post types in the sitemap (if no post types are selected, Post will be included by default)', 'admin-optimizer' ),
						'callback' => [ $this, 'render_post_types_field' ],
					],
					[
						'type'     => 'callback',
						'title'    => __( 'Taxonomies', 'admin-optimizer' ),
						'id'       => 'sitemap-taxonomies',
						'name'     => 'taxonomies',
						'desc'     => __( 'Include these taxonomies in the sitemap', 'admin-optimizer' ),
						'callback' => [ $this, 'render_taxonomies_field' ],
					],
					[
						'type'     => 'callback',
						'title'    => __( 'Exclude Categories', 'admin-optimizer' ),
						'id'       => 'exclude-categories',
						'name'     => 'exclude_categories',
						'desc'     => __( 'Exclude the selected categories from the sitemap', 'admin-optimizer' ),
						'callback' => [ $this, 'render_exclude_categories_field' ],
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Authors', 'admin-optimizer' ),
						'id'    => 'sitemap-include-authors',
						'name'  => 'include_authors',
						'label' => __( 'Include Authors in the sitemap?', 'admin-optimizer' ),
					],
					[
						'type'    => 'number',
						'title'   => __( 'Maximum entries per sitemap', 'admin-optimizer' ),
						'id'      => 'sitemap-max-entries',
						'name'    => 'max_entries',
						'min'     => 1,
						'max'     => 5000,
						'default' => 2000,
						'desc'    => __( 'Minimum is 1. Maximum is 5000. Default is 2000. The higher the value, the slower it takes to generate the sitemap.', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Add sitemap to Robot.txt', 'admin-optimizer' ),
						'id'    => 'include-sitemap-robotstxt',
						'name'  => 'include_in_robotstxt',
						'label' => __( 'Add sitemap URL to the robots.txt file (This requires the robots.txt module to be activated).', 'admin-optimizer' ),
					],
					[
						'type'     => 'callback',
						'title'    => __( 'Exclude individual post from sitemap', 'admin-optimizer' ),
						'id'       => 'exclude-individual-post',
						'name'     => 'exclude_individual_post',
						'callback' => [ $this, 'render_exclude_individual_post_field' ],
					],
				],
			],
			'pro' => [
				'id'          => 'adminoptimizer-xml-sitemap-pro',
				'title'       => __( 'Pro Options', 'admin-optimizer' ),
				'menu_slug'   => XML_Sitemap::MENU_SLUG . '_pro',
				'option_name' => XML_Sitemap::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'callback',
						'title'    => __( 'Exclude Noindex Posts', 'admin-optimizer' ),
						'id'       => 'exclude-noindex-posts',
						'name'     => 'exclude_noindex',
						'desc'     => __( 'Posts marked as "noindex" by your SEO plugin will be excluded from the sitemap.', 'admin-optimizer' ),
						'callback' => [ $this, 'render_seo_plugins_field' ],
					],
				],
			],
		];
		$this->setup();
	}

	/**
	 * Render custom sitemap url field
	 *
	 * @return void
	 */
	public function render_custom_sitemap_url_field() {
		$value = $this->options['custom_sitemap_slug'] ?? 'wp-sitemap';
		?>
		<code><?php echo esc_url( home_url( '/' ) ); ?></code> <input id="custom-url" type="text" name="<?php echo esc_attr( XML_Sitemap::OPTION_NAME ); ?>[custom_sitemap_slug]" value="<?php echo esc_attr( $value ); ?>"><code><?php echo esc_html( '.xml' ); ?></code>
		<p class="description">
			<?php $sitemap_url = $this->get_sitemap_url(); ?>
			<?php
			/* translators: %s: sitemap URL */
			printf( esc_html__( 'Change the sitemap URL. The default is wp-sitemap.xml. Your current sitemap URL is %s', 'admin-optimizer' ), '<a href="' . esc_url( $sitemap_url ) . '" target="_blank">' . esc_url( $sitemap_url ) . '</a>' );
			?>
		</p>
		<p class="description"><?php esc_html_e( 'If you are not seeing the sitemap, go to Settings -> Permalinks, and press Save Changes to flush the rewrite rules.', 'admin-optimizer' ); ?></p>
		<?php
	}

	/**
	 * Function to render the post types field
	 *
	 * @param [] $attr Arguments.
	 * @return void
	 */
	public function render_post_types_field( $attr ) {
		$custom_post_types = get_post_types(
			[
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

		if ( ! empty( $attr['desc'] ) ) {
			echo '<p>' . esc_html( $attr['desc'] ) . '</p>';
		}
		if ( ! empty( $this->options['post_types'] ) && is_array( $this->options['post_types'] ) ) {
			$options = $this->options['post_types'];
		} else {
			$options = [ 'post' ];
		}
		foreach ( $post_types as $post_type_name => $post_type_label ) :
			$checked = '';
			if ( in_array( $post_type_name, $options, true ) ) {
				$checked = ' checked="checked"';
			}
			?>
			<label for="<?php echo esc_attr( $post_type_name ); ?>"><input id="<?php echo esc_attr( $post_type_name ); ?>" name="<?php echo esc_attr( XML_Sitemap::OPTION_NAME . '[post_types][]' ); ?>" type="checkbox" <?php echo esc_attr( $checked ); ?> value="<?php echo esc_attr( $post_type_name ); ?>"><?php echo esc_html( $post_type_label ); ?></label><br/>
			<?php
		endforeach;
	}

	/**
	 * Function to render the taxonomies field
	 *
	 * @param [] $attr Arguments.
	 * @return void
	 */
	public function render_taxonomies_field( $attr ) {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$tax_fields = [];
		foreach ( $taxonomies as $taxonomy ) {
			if ( 'post_format' === $taxonomy->name || empty( $taxonomy->label ) ) {
				continue;
			}
			$tax_fields[ $taxonomy->name ] = $taxonomy->label;
		}

		if ( ! empty( $attr['desc'] ) ) {
			echo '<p>' . esc_html( $attr['desc'] ) . '</p>';
		}
		if ( ! empty( $this->options['taxonomies'] ) && is_array( $this->options['taxonomies'] ) ) {
			$options = $this->options['taxonomies'];
		} else {
			$options = [];
		}
		foreach ( $tax_fields as $tax_name => $tax_label ) :
			$checked = '';
			if ( in_array( $tax_name, $options, true ) ) {
				$checked = ' checked="checked"';
			}
			?>
			<label for="<?php echo esc_attr( $tax_name ); ?>"><input id="taxonomy-<?php echo esc_attr( $tax_name ); ?>" name="<?php echo esc_attr( XML_Sitemap::OPTION_NAME . '[taxonomies][]' ); ?>" type="checkbox" <?php echo esc_attr( $checked ); ?> value="<?php echo esc_attr( $tax_name ); ?>"><?php echo esc_html( $tax_label ); ?></label><br/>
			<?php
		endforeach;
	}

	/**
	 * Render exclude categories field
	 *
	 * @param [] $attr Attributes.
	 * @return void
	 */
	public function render_exclude_categories_field( $attr ) {
		$categories = get_categories();

		$options = $this->options['exclude_categories'] ?? [];

		if ( ! empty( $attr['desc'] ) ) {
			echo '<p>' . esc_html( $attr['desc'] ) . '</p>';
		}

		foreach ( $categories as $category ) :
			$checked = '';
			if ( in_array( intval( $category->term_id ), $options, true ) ) {
				$checked = ' checked="checked"';
			}
			?>
			<label for="<?php echo esc_attr( $category->name ); ?>"><input id="<?php echo esc_attr( $category->name ); ?>" class="term-cat" name="<?php echo esc_attr( XML_Sitemap::OPTION_NAME . '[exclude_categories][]' ); ?>" type="checkbox" <?php echo esc_attr( $checked ); ?> value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?></label><br/>
			<?php
		endforeach;
	}

	/**
	 * Render exclude individual post field
	 *
	 * @return void
	 */
	public function render_exclude_individual_post_field() {
		esc_html_e( 'Open the post in the Post Editor. On the right menu, select "Exclude from XML Sitemap".', 'admin-optimizer' );
	}

	/**
	 * Render SEO plugins field
	 *
	 * @param [] $attr Attributes.
	 * @return void
	 */
	public function render_seo_plugins_field( $attr ) {
		?>
		<label for="exclude-noindex-field">
			<input id="<?php echo esc_attr( $attr['id'] ); ?>" name="<?php echo esc_attr( XML_Sitemap::OPTION_NAME . '[exclude_noindex]' ); ?>" type="checkbox" value="" disabled="disabled">
			<?php echo esc_html( $attr['desc'] ); ?>
		</label>
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
			<h1><?php esc_html_e( 'Admin Optimizer - XML Sitemap', 'admin-optimizer' ); ?></h1>
				<?php
				settings_errors();
				$sitemap_enabled = $this->is_sitemap_enabled();
				if ( ! $sitemap_enabled ) {
					$message = __( 'The native WP sitemap has been disabled. Check if other Sitemap or SEO plugins have disabled it.', 'admin-optimizer' );
					wp_admin_notice(
						$message,
						[
							'id'                 => 'message',
							'additional_classes' => [ 'error' ],
							'dismissible'        => false,
						]
					);
				}
				$this->render_settings_on_page( XML_Sitemap::MENU_SLUG );
				?>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook_suffix  Check if we are on the right page before enqueueing script.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, XML_Sitemap::MENU_SLUG ) ) {
			wp_enqueue_script( 'adminoptim-sitemap-settings', XML_Sitemap::MODULE_URL . 'assets/js/sitemap-settings.min.js', [], filemtime( XML_Sitemap::MODULE_PATH . 'assets/js/sitemap-settings.min.js' ), true );
		}
	}

	/**
	 * Check if native WP sitemap is enabled
	 *
	 * @return boolean
	 */
	public function is_sitemap_enabled() {
		return (bool) apply_filters( 'wp_sitemaps_enabled', true ); //phpcs:ignore  WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
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
			if ( isset( $options['post_types'] ) ) {
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
				if ( is_array( $options['post_types'] ) ) {
					$selected = array_intersect( $options['post_types'], $custom_post_types );
					if ( ! empty( $selected ) ) {
						$sanitized_options['post_types'] = $selected;
					} else {
						$sanitized_options['post_types'] = [ 'post' ];
					}
				} else {
					$sanitized_options['post_types'] = [ 'post' ];
				}
			}
			if ( isset( $options['taxonomies'] ) ) {
				$all_taxonomies = get_taxonomies( [ 'public' => true ] );
				$selected       = array_intersect( $options['taxonomies'], $all_taxonomies );
				if ( ! empty( $selected ) ) {
					$sanitized_options['taxonomies'] = $selected;
				} else {
					$sanitized_options['taxonomies'] = [];
				}
			}
			if ( isset( $options['exclude_categories'] ) && is_array( $options['exclude_categories'] ) ) {
				$sanitized_options['exclude_categories'] = array_map( 'absint', $options['exclude_categories'] );
			} else {
				$sanitized_options['exclude_categories'] = [];
			}
			if ( isset( $options['include_authors'] ) ) {
				$sanitized_options['include_authors'] = 1;
			}
			if ( isset( $options['max_entries'] ) ) {
				if ( 1 <= absint( $options['max_entries'] ) && 5000 >= absint( $options['max_entries'] ) ) {
					$sanitized_options['max_entries'] = absint( $options['max_entries'] );
				} else {
					$sanitized_options['max_entries'] = 2000;
				}
			} else {
				$sanitized_options['max_entries'] = 2000;
			}
			if ( isset( $options['include_in_robotstxt'] ) ) {
				$sanitized_options['include_in_robotstxt'] = 1;
			}
			if ( isset( $options['custom_sitemap_slug'] ) ) {
				$slug = str_replace( [ '.xml', '/' ], '', sanitize_title_with_dashes( $options['custom_sitemap_slug'] ) );
				if ( ! empty( $slug ) && 'wp-sitemap' !== $slug ) {
					$sanitized_options['custom_sitemap_slug'] = $slug;
				}
			}
		}

		return $sanitized_options;
	}

	/**
	 * Get sitemap url
	 *
	 * @return string
	 */
	private function get_sitemap_url() {
		if ( ! empty( $this->options['custom_sitemap_slug'] ) ) {
			return home_url( '/' . $this->options['custom_sitemap_slug'] . '.xml' );
		} else {
			return home_url( 'wp-sitemap.xml' );
		}
	}
}