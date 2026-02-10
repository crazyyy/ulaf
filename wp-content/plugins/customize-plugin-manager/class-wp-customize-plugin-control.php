<?php
/**
 * Customize API: WP_Customize_Plugin_Control class
 *
 * @package WordPress
 * @subpackage Customize
 * @since X.X.X
 */

/**
 * Customize Plugin Control class.
 *
 * @since X.X.X
 *
 * @see WP_Customize_Control
 */
class WP_Customize_Plugin_Control extends WP_Customize_Control {
	/**
	 * Type (used for JS).
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'plugin';

	/**
	 * Status (active/inactive/network).
	 *
	 * @access public
	 * @var string
	 */
	public $status = 'inactive';

	/**
	 * Plugin id.
	 *
	 * @access public
	 * @var string
	 */
	public $plugin = '';

	/**
	 * Enqueue scripts/styles.
	 *
	 */
	public function enqueue() {
		wp_enqueue_script( 'customize-plugin-manager', plugin_dir_url( __FILE__ ) . '/customize-plugin-manager.js', array( 'jquery', 'customize-controls' ) );
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @uses WP_Customize_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();
		$this->json['status'] = $this->status;
		$this->json['plugin'] = $this->plugin;
	}

	/**
	 * Don't render the control content from PHP, as it's rendered via JS on load.
	 *
	 */
	public function render_content() {}

	/**
	 * Render a JS template for the content of the plugin control.
	 *
	 */
	public function content_template() {
		?>
		<# var statusString = '';

		if ( 'inactive' !== data.status ) {
			statusString = ' checked="checked"';
			if ( 'network' === data.status ) {
				statusString = statusString + ' disabled="disabled"';
			}
		} #>
		<label>
			<# if ( data.label ) { #>
				<span class="customize-control-title">{{{ data.label }}}</span>
			<# } #>
			<input type="checkbox" value="1" {{{ statusString }}} />
		</label>
		<# if ( data.description ) { #>
			<span class="description customize-control-description">{{{ data.description }}}</span>
		<# } #>
		<?php
	}
}
