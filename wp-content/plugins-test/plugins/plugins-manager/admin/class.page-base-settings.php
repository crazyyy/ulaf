<?php

namespace WTBP;

abstract class PageBaseSettings extends PageBase {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * Settings constructor.
	 *
	 * @param $plugin
	 */
	public function __construct( $plugin ) {
		parent::__construct( $plugin );

		$this->settings = $this->settings();

		add_action( 'admin_init', [ $this, 'init_settings' ] );
	}

	/**
	 * Array of the settings
	 *
	 * @return array
	 */
	abstract function settings();

	public function add_page_to_menu() {
		add_options_page( $this->page_title, $this->page_menu_title, 'manage_options', WTBP_PLUGIN_PREFIX . '_' . $this->id, [
			$this,
			'page_action',
		], $this->page_menu_position );
	}

	public function init_settings() {
		foreach ( $this->settings as $group_slug => $group ) {
			$group_slug = WTBP_PLUGIN_PREFIX . '_' . $group_slug;
			foreach ( $group['sections'] as $section ) {
				$section_slug = WTBP_PLUGIN_PREFIX . '_' . $section['slug'];
				foreach ( $section['options'] as $opt_name => $option ) {
					$opt_name = WTBP_PLUGIN_PREFIX . '_' . $opt_name;
					$opt_type = $option['type'] ?? 'text';
					$args     = [
						'name'    => $opt_name,
						'type'    => $opt_type,
						'default' => $option['default'] ?? '',
					];
					register_setting( $group_slug, $opt_name, [
						'sanitize_callback' => $option['sanitize_callback'] ?? [ $this, 'sanitize_callback' ],
						'show_in_rest'      => false,
					] );
					$render_callback = $option['render_callback'] ?? [ $this, "fill_{$opt_type}_field" ];
					add_settings_field( $opt_name, $option['title'], $render_callback, WTBP_PLUGIN_PREFIX . '_settings_page', $section_slug, $args );
				}
				add_settings_section( $section_slug, $section['title'], '', WTBP_PLUGIN_PREFIX . '_settings_page' );
			}
		}
	}

	public function page_action() {
		if ( $this->template ) {
			echo $this->plugin->render_template( $this->template, [ 'settings' => $this->settings ] );
		}
	}

	/**
	 * @param $args
	 */
	function fill_text_field( $args ) {
		$val = get_option( $args['name'], $args['default'] );
		$val = $val ? $val : '';
		?>
        <input type="text" name="<?= $args['name']; ?>" id="<?= $args['name']; ?>"
               value="<?php echo esc_attr( $val ) ?>"/>
		<?php
	}

	/**
	 * @param $args
	 */
	function fill_checkbox_field( $args ) {
		$val   = get_option( $args['name'], $args['default'] );
		$val   = $val ? 1 : 0;
		$check = __( 'Enable', 'bulk-plugins' );
		?>
        <label for="<?= $args['name']; ?>">
            <input type="checkbox" name="<?= $args['name']; ?>" id="<?= $args['name']; ?>"
                   value="1" <?php checked( 1, $val ) ?> />
			<?= $check; ?>
        </label>
		<?php
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	function sanitize_callback( $value ) {
		if ( is_string( $value ) ) {
			return strip_tags( $value );
		}

		if ( is_numeric( $value ) ) {
			return intval( $value );
		}

		return $value;
	}
}