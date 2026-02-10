<?php
/**
 * Class UltimaKit_Module_Simple_Notification_Bar
 *
 * @since 1.0.0
 * @package    UltimaKit
 */

/**
 * Class UltimaKit_Module_Simple_Notification_Bar
 *
 * @since 1.0.0
 */
class UltimaKit_Module_Simple_Notification_Bar extends UltimaKit_Module_Manager {
	/**
	 * @var string
	 */
	protected $ID = 'ultimakit_module_simple_notification_bar';

	/**
	 * The name of the module.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * A brief description of what the module does.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * The pricing plan associated with the module.
	 *
	 * @var string
	 */
	protected $plan = 'free';

	/**
	 * The category of functionality the module falls under.
	 *
	 * @var string
	 */
	protected $category = 'Utilities';

	/**
	 * The type of module, indicating its platform or use case.
	 *
	 * @var string
	 */
	protected $type = 'WordPress';

	/**
	 * Flag indicating whether the module is active.
	 *
	 * @var bool
	 */
	protected $is_active;

	/**
	 * URL providing more detailed information about the module.
	 *
	 * @var string
	 */
	protected $read_more_link = 'add-notification-bar-in-wordpress';

	/**
	 * The settings associated with the module, if any.
	 *
	 * @var array
	 */
	protected $settings;

	private $default_options = [
        'message' => 'This is a notification bar!',
        'background_color' => '#ffcc00',
        'text_color' => '#000000',
        'font_size' => '16px',
        'show_close_button' => true,
    ];

	/**
	 * Constructs the Hide Admin Bar module instance.
	 *
	 * Initializes the module with default values for properties and prepares
	 * any necessary setup or hooks into WordPress. This may include setting
	 * initial values, registering hooks, or preparing resources needed for
	 * the module to function properly within WordPress.
	 */
	public function __construct() {
		$this->name        = __( 'Simple Notification Bar', 'ultimakit-for-wp' );
		$this->description = __( 'Adds a notification bar at the top of the site for announcements.', 'ultimakit-for-wp' );
		$this->is_active   = $this->isModuleActive( $this->ID );
		$this->settings    = 'yes';
		$this->initializeModule();
	}

	/**
	 * Initializes the specific module within the application.
	 *
	 * This function is responsible for performing the initial setup required to get the module
	 * up and running. This includes registering hooks and filters, enqueing styles and scripts,
	 * and any other preliminary setup tasks that need to be performed before the module can
	 * start functioning as expected.
	 *
	 * It's typically called during the plugin or theme's initialization phase, ensuring that
	 * all module dependencies are loaded and ready for use.
	 *
	 * @return void
	 */
	protected function initializeModule() {
		if ( $this->is_active ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
			add_action( 'admin_footer', array( $this, 'add_modal' ) );

			add_action('wp_footer', [$this, 'display_notification_bar']);
		}	
	}

	/**
	 * Adds a modal dialog to the page.
	 *
	 * This function is responsible for initiating and rendering a modal dialog within the
	 * application or website interface. It typically involves setting up the necessary HTML
	 * and JavaScript for the modal to function and display correctly. The modal can be used
	 * for various purposes, such as displaying information, confirming actions, or collecting
	 * user input.
	 *
	 * @return void
	 */
	public function add_modal() {
		$arguments          = array();
		$arguments['ID']    = $this->ID;
		$arguments['title'] = __( 'Simple Notification Bar', 'ultimakit-for-wp' );

		$arguments['fields'] = array(
			'html_notice'         => array(
				'type'  => 'html',
				'value' => '<div class="notice notice-info">
				<p><strong>Allowed HTML Tags:</strong></p><Br />
				<ul style="list-style-type: disc; margin-left: 20px;">
					<li><code>&lt;a&gt;</code> - Links (supports href, title, target attributes)</li><Br />
					<li><code>&lt;b&gt;</code> or <code>&lt;strong&gt;</code> - Bold text</li><Br />
					<li><code>&lt;i&gt;</code> or <code>&lt;em&gt;</code> - Italic text</li><Br />
					<li><code>&lt;span&gt;</code> - Inline text (supports class attribute)</li><Br />
					<li><code>&lt;br&gt;</code> - Line break</li><Br />
				</ul><Br />
				<p><strong>Example:</strong> Check out our <code>&lt;a href="https://example.com"&gt;latest products&lt;/a&gt;</code>! <code>&lt;strong&gt;20% off&lt;/strong&gt;</code> this week!</p>
			</div>'
			),
			'noti_bar_text_area' => array(
				'type'  => 'textarea2',
				'label' => __( 'Notification Message', 'ultimakit-for-wp' ),
				'value' => $this->getModuleSettings( $this->ID, 'noti_bar_text_area' ),
				'desc' => __('Enter your message. HTML tags listed above are allowed.','ultimakit-for-wp')
			),
			'wpuk_noti_bg_color' => array(
				'type'  => 'color',
				'label' => __( 'Background Color', 'ultimakit-for-wp' ),
				'value' => $this->getModuleSettings( $this->ID, 'wpuk_noti_bg_color' ),
			),
			'wpuk_noti_txt_color' => array(
				'type'  => 'color',
				'label' => __( 'Text Color', 'ultimakit-for-wp' ),
				'value' => $this->getModuleSettings( $this->ID, 'wpuk_noti_txt_color' ),
			),
			'wpuk_noti_txt_size' => array(
				'type'  => 'number',
				'label' => __( 'Font Size', 'ultimakit-for-wp' ),
				'value' => $this->getModuleSettings( $this->ID, 'wpuk_noti_txt_size', 16 ),
				'desc' => __('Choose a size between 10 and 32 pixels','ultimakit-for-wp'),
				'min' => 10,
				'max' => 32
			),
			'wpuk_noti_txt_weight' => array(
				'type'  => 'select',
				'label' => __( 'Font Weight', 'ultimakit-for-wp' ),
				'options' => array(	
								100 => __( '100', 'ultimakit-for-wp' ),
								200 => __( '200', 'ultimakit-for-wp' ),
								300 => __( '300', 'ultimakit-for-wp' ),
								400 => __( '400', 'ultimakit-for-wp' ),
								500 => __( '500', 'ultimakit-for-wp' ),
								600 => __( '600', 'ultimakit-for-wp' ),
								700 => __( '700', 'ultimakit-for-wp' ),
								800 => __( '800', 'ultimakit-for-wp' ),
							),
				'default' => $this->getModuleSettings( $this->ID, 'wpuk_noti_txt_weight', 400 ),
				'desc' => __('Choose a font weigth between 100 and 800','ultimakit-for-wp')
			),
			'wpuk_noti_btn' => array(
				'type'  => 'switch',
				'label' => __( 'Show close button', 'ultimakit-for-wp' ),
				'value' => $this->getModuleSettings( $this->ID, 'wpuk_noti_btn' ),
			),

			// New Position Settings
			'position_settings' => array(
				'type' => 'section_start',
				'label' => __('Position Settings', 'ultimakit-for-wp')
			),
			'bar_position' => array(
				'type' => 'select',
				'label' => __('Position', 'ultimakit-for-wp'),
				'options' => array(
					'top' => __('Top', 'ultimakit-for-wp'),
					'bottom' => __('Bottom', 'ultimakit-for-wp')
				),
				'default' => $this->getModuleSettings($this->ID, 'bar_position', 'top')
			),
			'fixed_position' => array(
				'type' => 'switch',
				'label' => __('Fixed at Position', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'fixed_position')
			),
			'sticky_selector' => array(
				'type' => 'text',
				'label' => __('Theme Sticky Selector', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'sticky_selector'),
				'desc' => __('CSS selector for sticky theme elements (e.g., #header_top)', 'ultimakit-for-wp')
			),
	
			// Display Settings
			'display_settings' => array(
				'type' => 'section_start',
				'label' => __('Display Settings', 'ultimakit-for-wp')
			),
			'display_on_scroll' => array(
				'type' => 'switch',
				'label' => __('Display on Scroll', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'display_on_scroll')
			),
			'scroll_offset' => array(
				'type' => 'number',
				'label' => __('Scroll Offset (px)', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'scroll_offset', 100),
				'desc' => __('Show notification after scrolling this many pixels', 'ultimakit-for-wp'),
				'min' => 0,
				'max' => 1000
			),
			'bar_height' => array(
				'type' => 'number',
				'label' => __('Bar Height (px)', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'bar_height', 50),
				'min' => 30,
				'max' => 200
			),
			'position_offset' => array(
				'type' => 'number',
				'label' => __('Position Offset (px)', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'position_offset', 0),
				'min' => 0,
				'max' => 500
			),
			'display_after' => array(
				'type' => 'number',
				'label' => __('Display After (seconds)', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'display_after', 0),
				'min' => 0,
				'max' => 60
			),
			'animation_duration' => array(
				'type' => 'number',
				'label' => __('Animation Duration (seconds)', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'animation_duration', 0),
				'min' => 0,
				'max' => 10
			),
			'auto_close' => array(
				'type' => 'number',
				'label' => __('Auto Close After (seconds)', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'auto_close', 0),
				'desc' => __('0 to disable auto-close', 'ultimakit-for-wp'),
				'min' => 0,
				'max' => 300
			),
			'display_shadow' => array(
				'type' => 'switch',
				'label' => __('Display Shadow', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'display_shadow')
			),
	
			// Reopen Button Settings
			'reopen_settings' => array(
				'type' => 'section_start',
				'label' => __('Reopen Button Settings', 'ultimakit-for-wp')
			),
			'display_reopen' => array(
				'type' => 'switch',
				'label' => __('Display Reopen Button', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'display_reopen')
			),
			'reopen_image_url' => array(
				'type' => 'text',
				'label' => __('Reopen Button Image URL', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'reopen_image_url')
			),
			'reopen_offset' => array(
				'type' => 'number',
				'label' => __('Reopen Button Offset (px)', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'reopen_offset', 0),
				'min' => 0,
				'max' => 200
			),
	
			// Device Settings
			'device_settings' => array(
				'type' => 'section_start',
				'label' => __('Device Settings', 'ultimakit-for-wp')
			),
			'display_devices' => array(
				'type' => 'select',
				'label' => __('Display On Devices', 'ultimakit-for-wp'),
				'options' => array(
					'all' => __('All Devices', 'ultimakit-for-wp'),
					'small' => __('Small Devices Only', 'ultimakit-for-wp'),
					'large' => __('Except Small Devices', 'ultimakit-for-wp')
				),
				'default' => $this->getModuleSettings($this->ID, 'display_devices', 'all')
			),
			'small_device_width' => array(
				'type' => 'number',
				'label' => __('Small Device Max Width (px)', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'small_device_width', 640),
				'min' => 320,
				'max' => 1200
			),
			'hide_small_window' => array(
				'type' => 'switch',
				'label' => __('Hide on Small Window', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'hide_small_window')
			),
			'small_window_width' => array(
				'type' => 'number',
				'label' => __('Small Window Max Width (px)', 'ultimakit-for-wp'),
				'value' => $this->getModuleSettings($this->ID, 'small_window_width', 640),
				'min' => 320,
				'max' => 1200
			)
		);

		$this->ultimakit_generate_modal( $arguments );
	}

	/**
	 * Enqueues scripts for the theme or plugin.
	 *
	 * This function handles the registration and enqueuing of JavaScript files required
	 * by the theme or plugin. It ensures that scripts are loaded in the correct order and
	 * that dependencies are managed properly. Scripts can include both local and external
	 * resources, and may be conditionally loaded based on the context or user actions.
	 *
	 * Use this function to enqueue all JavaScript necessary for the functionality of your
	 * theme or plugin, adhering to WordPress best practices for script registration and
	 * enqueuing.
	 *
	 * @return void
	 */
	public function add_scripts() {

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script(
			'ultimakit-module-script-' . $this->ID,
			plugins_url( '/module-script.js', __FILE__ ),
			array( 'jquery', 'wp-color-picker' ),
			ULTIMAKIT_FOR_WP_VERSION,
			true
		);

	}


	// Add this helper method to your class
	private function get_allowed_html() {
		return array(
			'a' => array(
				'href' => array(),
				'title' => array(),
				'target' => array(),
				'class' => array()
			),
			'b' => array(),
			'strong' => array(),
			'i' => array(),
			'em' => array(),
			'span' => array(
				'class' => array()
			),
			'br' => array(),
			'button' => array(
				'href' => array(),
				'title' => array(),
				'target' => array(),
				'class' => array()
			)
		);
	}


    public function display_notification_bar() {

		// Check if the notification bar should be displayed
		if (empty($this->getModuleSettings($this->ID, 'noti_bar_text_area'))) {
			return;
		}
	
		// Get settings
		$position = $this->getModuleSettings($this->ID, 'bar_position', 'top');
		$fixed = $this->getModuleSettings($this->ID, 'fixed_position', 'off') === 'on';
		$bar_height = $this->getModuleSettings($this->ID, 'bar_height', 50);
		$position_offset = $this->getModuleSettings($this->ID, 'position_offset', 0);
		$display_shadow = $this->getModuleSettings($this->ID, 'display_shadow', 'off') === 'on';
		$animation_duration = $this->getModuleSettings($this->ID, 'animation_duration', 0);
		$auto_close = $this->getModuleSettings($this->ID, 'auto_close', 0);
		$display_reopen = $this->getModuleSettings($this->ID, 'display_reopen', 'off') === 'on';
		$reopen_offset = $this->getModuleSettings($this->ID, 'reopen_offset', 0);
		$reopen_image_url = $this->getModuleSettings($this->ID, 'reopen_image_url', '');
		$sticky_selector = $this->getModuleSettings($this->ID, 'sticky_selector', '');
		$display_on_scroll = $this->getModuleSettings($this->ID, 'display_on_scroll', 'off') === 'on';
		$scroll_offset = $this->getModuleSettings($this->ID, 'scroll_offset', 100);
		$display_after = $this->getModuleSettings($this->ID, 'display_after', 0);
		$display_devices = $this->getModuleSettings($this->ID, 'display_devices', 'all');
		$small_device_width = $this->getModuleSettings($this->ID, 'small_device_width', 640);
		$hide_small_window = $this->getModuleSettings($this->ID, 'hide_small_window', 'off') === 'on';
		$small_window_width = $this->getModuleSettings($this->ID, 'small_window_width', 640);
	
		// Define allowed HTML tags
		$allowed_html = $this->get_allowed_html();

		?>
		<style>
			#simple-notification-bar {
				background-color: <?php echo esc_attr($this->getModuleSettings($this->ID, 'wpuk_noti_bg_color', '#ffcc00')); ?>;
				color: <?php echo esc_attr($this->getModuleSettings($this->ID, 'wpuk_noti_txt_color', '#000000')); ?>;
				font-size: <?php echo esc_attr($this->getModuleSettings($this->ID, 'wpuk_noti_txt_size', 16)); ?>px;
				font-weight: <?php echo esc_attr($this->getModuleSettings($this->ID, 'wpuk_noti_txt_weight', 400)); ?>;
				padding: 10px 20px;
				text-align: center;
				position: <?php echo $fixed ? 'fixed' : 'absolute'; ?>;
				<?php echo $position; ?>: <?php echo esc_attr($position_offset); ?>px;
				left: 0;
				right: 0;
				height: <?php echo esc_attr($bar_height); ?>px;
				width: 100%;
				z-index: 999999;
				<?php if ($display_shadow) : ?>box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);<?php endif; ?>
				display: none;
				transition: all <?php echo esc_attr($animation_duration); ?>s ease-in-out;
				box-sizing: border-box;
			}
			
			<?php if ($fixed && $position === 'top') : ?>
			body.has-notification-bar {
				padding-top: <?php echo esc_attr($bar_height + $position_offset); ?>px !important;
			}
			
			/* Handle admin bar overlap */
			.admin-bar body.has-notification-bar {
				padding-top: <?php echo esc_attr($bar_height + $position_offset + 32); ?>px !important;
			}
			
			@media (max-width: 782px) {
				.admin-bar body.has-notification-bar {
					padding-top: <?php echo esc_attr($bar_height + $position_offset + 46); ?>px !important;
				}
			}
			<?php endif; ?>
			
			<?php if ($fixed && $position === 'bottom') : ?>
			body.has-notification-bar {
				padding-bottom: <?php echo esc_attr($bar_height + $position_offset); ?>px !important;
			}
			<?php endif; ?>
			
			#simple-notification-bar-close {
				background: none;
				border: none;
				color: <?php echo esc_attr($this->getModuleSettings($this->ID, 'wpuk_noti_txt_color', '#000000')); ?> !important;
				font-size: 20px;
				margin-left: 10px;
				cursor: pointer;
				float: right;
				line-height: 1;
			}
			
			#simple-notification-bar-reopen {
				position: fixed;
				bottom: <?php echo esc_attr($reopen_offset); ?>px;
				right: 20px;
				cursor: pointer;
				z-index: 999998;
				display: none;
			}
			
			#simple-notification-bar-reopen img {
				max-width: 50px;
				height: auto;
			}
			
			@media (max-width: <?php echo esc_attr($small_device_width); ?>px) {
				<?php if ($display_devices === 'large') : ?>
				#simple-notification-bar {
					display: none !important;
				}
				<?php endif; ?>
			}
			
			@media (min-width: <?php echo esc_attr($small_device_width + 1); ?>px) {
				<?php if ($display_devices === 'small') : ?>
				#simple-notification-bar {
					display: none !important;
				}
				<?php endif; ?>
			}
		</style>

		<div id="simple-notification-bar">
			<div style="display: flex; align-items: center; justify-content: center; height: 100%;">
				<div style="flex: 1; text-align: center;">
					<?php echo wp_kses($this->getModuleSettings($this->ID, 'noti_bar_text_area'), $allowed_html); ?>
				</div>
				<?php if ('on' == $this->getModuleSettings($this->ID, 'wpuk_noti_btn')) : ?>
					<button id="simple-notification-bar-close">&times;</button>
				<?php endif; ?>
			</div>
		</div>
	
		<?php if ($display_reopen && !empty($reopen_image_url)) : ?>
			<div id="simple-notification-bar-reopen">
				<img src="<?php echo esc_url($reopen_image_url); ?>" alt="<?php esc_attr_e('Reopen Notification Bar', 'ultimakit-for-wp'); ?>" />
			</div>
		<?php endif; ?>
	
		<script>
			(function() {
				'use strict';
				
				// Configuration
				const config = {
					displayOnScroll: <?php echo $display_on_scroll ? 'true' : 'false'; ?>,
					scrollOffset: <?php echo esc_attr($scroll_offset); ?>,
					displayAfter: <?php echo esc_attr($display_after); ?> * 1000,
					autoClose: <?php echo esc_attr($auto_close); ?> * 1000,
					displayDevices: '<?php echo esc_attr($display_devices); ?>',
					smallDeviceWidth: <?php echo esc_attr($small_device_width); ?>,
					hideSmallWindow: <?php echo $hide_small_window ? 'true' : 'false'; ?>,
					smallWindowWidth: <?php echo esc_attr($small_window_width); ?>,
					stickySelector: '<?php echo esc_attr($sticky_selector); ?>',
					position: '<?php echo esc_attr($position); ?>',
					fixed: <?php echo $fixed ? 'true' : 'false'; ?>,
					barHeight: <?php echo esc_attr($bar_height); ?>,
					positionOffset: <?php echo esc_attr($position_offset); ?>
				};
				
				let hasBeenShown = false;
				let autoCloseTimer = null;
				
				function getElements() {
					return {
						bar: document.getElementById('simple-notification-bar'),
						closeButton: document.getElementById('simple-notification-bar-close'),
						reopenButton: document.getElementById('simple-notification-bar-reopen'),
						body: document.body
					};
				}
				
				function shouldDisplay() {
					const currentWidth = window.innerWidth;
					
					// Check small window setting
					if (config.hideSmallWindow && currentWidth <= config.smallWindowWidth) {
						return false;
					}
					
					// Check device display settings
					if (config.displayDevices === 'small' && currentWidth > config.smallDeviceWidth) {
						return false;
					}
					
					if (config.displayDevices === 'large' && currentWidth <= config.smallDeviceWidth) {
						return false;
					}
					
					return true;
				}
				
				function showBar() {
					const elements = getElements();
					if (!elements.bar || !shouldDisplay()) {
						return;
					}
					
					elements.bar.style.display = 'block';
					
					// Add body class for padding
					if (config.fixed) {
						elements.body.classList.add('has-notification-bar');
						
						// Handle admin bar if present
						if (elements.body.classList.contains('admin-bar') && config.position === 'top') {
							const adminBarHeight = window.innerWidth <= 782 ? 46 : 32;
							elements.bar.style.top = adminBarHeight + 'px';
						}
					}
					
					// Handle sticky selector
					if (config.stickySelector) {
						const stickyElement = document.querySelector(config.stickySelector);
						if (stickyElement && config.position === 'top') {
							stickyElement.style.top = (config.barHeight + config.positionOffset) + 'px';
						}
					}
					
					hasBeenShown = true;
					
					// Set auto-close timer
					if (config.autoClose > 0) {
						autoCloseTimer = setTimeout(hideBar, config.autoClose);
					}
				}
				
				function hideBar() {
					const elements = getElements();
					if (!elements.bar) {
						return;
					}
					
					elements.bar.style.display = 'none';
					
					// Remove body class
					elements.body.classList.remove('has-notification-bar');
					
					// Reset sticky element
					if (config.stickySelector) {
						const stickyElement = document.querySelector(config.stickySelector);
						if (stickyElement && config.position === 'top') {
							stickyElement.style.top = '';
						}
					}
					
					// Show reopen button
					if (elements.reopenButton) {
						elements.reopenButton.style.display = 'block';
					}
					
					// Clear auto-close timer
					if (autoCloseTimer) {
						clearTimeout(autoCloseTimer);
						autoCloseTimer = null;
					}
				}
				
				function handleScroll() {
					if (hasBeenShown || !config.displayOnScroll) {
						return;
					}
					
					if (window.scrollY >= config.scrollOffset) {
						showBar();
					}
				}
				
				function handleResize() {
					if (!shouldDisplay()) {
						hideBar();
					} else if (!hasBeenShown && !config.displayOnScroll) {
						showBar();
					}
				}
				
				function initialize() {
					const elements = getElements();
					
					if (!elements.bar) {
						console.error('Notification bar element not found');
						return;
					}
					
					// Set up close button
					if (elements.closeButton) {
						elements.closeButton.addEventListener('click', hideBar);
					}
					
					// Set up reopen button
					if (elements.reopenButton) {
						elements.reopenButton.addEventListener('click', function() {
							elements.reopenButton.style.display = 'none';
							hasBeenShown = false;
							if (config.displayOnScroll) {
								// Reset scroll state
								if (window.scrollY >= config.scrollOffset) {
									showBar();
								}
							} else {
								showBar();
							}
						});
					}
					
					// Set up scroll listener
					if (config.displayOnScroll) {
						window.addEventListener('scroll', handleScroll);
					}
					
					// Set up resize listener
					let resizeTimer;
					window.addEventListener('resize', function() {
						clearTimeout(resizeTimer);
						resizeTimer = setTimeout(handleResize, 250);
					});
					
					// Initial display
					if (!config.displayOnScroll) {
						setTimeout(function() {
							if (!hasBeenShown) {
								showBar();
							}
						}, config.displayAfter);
					}
				}
				
				// Initialize when DOM is ready
				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', initialize);
				} else {
					initialize();
				}
			})();
		</script>
		<?php
	}
	
}
