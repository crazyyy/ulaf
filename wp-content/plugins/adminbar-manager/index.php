<?php
declare(strict_types=1);

/*
Plugin Name: Adminbar Manager
Plugin URI: https://wordpress.org/plugins/adminbar-manager/
Description: Remove unwanted menus from adminbar (toolbar).
Author: sarankumar
Author URI: https://sarankumar.xyz
Version: 1.9.1
*/

if (!defined('ABSPATH')) {
	exit;
}

final class ABMC_Options {

	private array $options = [];

	public function __construct() {
		$this->options = $this->get_options();

		add_action('admin_init', [$this, 'register_settings']);
	}

	public static function add_menu_page(): void {
		add_options_page(
			'Adminbar Manager',
			'Adminbar Manager',
			'manage_options',
			'abmc-options',
			[self::class, 'render_options_page']
		);
	}

	public static function render_options_page(): void {
		?>
		<div class="wrap">
			<h1>Admin Bar Manager</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('ABMC_options');
				do_settings_sections('abmc-options');
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function register_settings(): void {
		register_setting(
			'ABMC_options',
			'ABMC_options',
			[$this, 'validate']
		);

		add_settings_section(
			'ABMC_main',
			'Admin Bar Settings',
			null,
			'abmc-options'
		);

		$this->add_checkbox('ABMC_wplogo', 'Remove WordPress logo');
		$this->add_checkbox('ABMC_sitename', 'Remove site name');
		$this->add_checkbox('ABMC_updates', 'Remove updates');
		$this->add_checkbox('ABMC_comments', 'Remove comments');
		$this->add_checkbox('ABMC_newcontent', 'Remove new content');
		$this->add_checkbox('ABMC_secondary', 'Remove secondary menu');

		add_settings_field(
			'ABMC_color',
			'Admin Bar Color (frontend)',
			[$this, 'color_field'],
			'abmc-options',
			'ABMC_main'
		);
	}

	private function add_checkbox(string $key, string $label): void {
		add_settings_field(
			$key,
			$label,
			function () use ($key) {
				$checked = !empty($this->options[$key]) ? 'checked' : '';
				echo "<input type='checkbox' name='ABMC_options[{$key}]' value='1' {$checked} />";
			},
			'abmc-options',
			'ABMC_main'
		);
	}

	public function color_field(): void {
		$color = esc_attr($this->options['ABMC_color'] ?? '');
		echo "<input type='text' name='ABMC_options[ABMC_color]' value='{$color}' placeholder='#efefef' />";
	}

	public function validate(array $input): array {
		$output = [];

		foreach ($input as $key => $value) {
			if ($key === 'ABMC_color') {
				$output[$key] = sanitize_hex_color($value) ?: '';
			} else {
				$output[$key] = (int) ($value === '1');
			}
		}

		return $output;
	}

	private function get_options(): array {
		$options = get_option('ABMC_options');
		return is_array($options) ? $options : [];
	}
}

/* ===== Hooks ===== */

add_action('admin_menu', function () {
	ABMC_Options::add_menu_page();
});

add_action('admin_init', function () {
	new ABMC_Options();
});

add_action('admin_bar_menu', function (WP_Admin_Bar $bar) {
	$options = get_option('ABMC_options');
	if (!is_array($options)) {
		return;
	}

	if (!empty($options['ABMC_wplogo'])) {
		$bar->remove_node('wp-logo');
	}
	if (!empty($options['ABMC_sitename'])) {
		$bar->remove_node('site-name');
	}
	if (!empty($options['ABMC_updates'])) {
		$bar->remove_node('updates');
	}
	if (!empty($options['ABMC_comments'])) {
		$bar->remove_node('comments');
	}
	if (!empty($options['ABMC_newcontent'])) {
		$bar->remove_node('new-content');
	}
	if (!empty($options['ABMC_secondary'])) {
		$bar->remove_node('top-secondary');
	}
}, 999);

add_action('wp_head', function () {
	$options = get_option('ABMC_options');
	if (!is_array($options) || empty($options['ABMC_color'])) {
		return;
	}

	$color = esc_attr($options['ABMC_color']);
	?>
	<style>
	#wpadminbar,
	#wpadminbar * {
		background-color: <?php echo $color; ?> !important;
	}
	</style>
	<?php
});
