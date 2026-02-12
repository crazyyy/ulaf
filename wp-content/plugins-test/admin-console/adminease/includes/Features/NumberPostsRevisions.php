<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the configuration of post revision limits in WordPress
 * based on plugin performance settings.
 */
class NumberPostsRevisions {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'posts' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Adds settings fields related to post revisions to the provided array of fields.
	 * This method appends a new select field to the security section of the settings, allowing users
	 * to configure the behavior of post revisions. Additionally, it adds a child field for specifying
	 * a custom number of revisions when the "other" option is selected.
	 *
	 * @param array $fields The array of existing settings fields.
	 *
	 * @return array The modified array of settings fields including the new post revisions configuration.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['posts']['fields'][] = [
			'type'         => 'select',
			'id'           => 'number-posts-revisions',
			'name'         => 'adminease[posts][number_posts_revisions]',
			'value'        => $this->settings['number_posts_revisions'] ?? '',
			'options'      => [
				''          => __( 'Select', 'adminease' ),
				'keep'      => __( 'Keep every revision', 'adminease' ),
				'dont-keep' => __( "Don't keep any revisions", 'adminease' ),
				'other'     => __( 'Insert number', 'adminease' ),
			],
			'label_class'  => 'adminease-label',
			'input_class'  => 'form-control adminease-choices toggle-field',
			'label'        => __( 'Post revisions', 'adminease' ),
			'description'  => __( '<p><strong>Post revisions</strong> in WordPress are automatically saved versions of your posts and pages, created every time you update or save content. This feature lets you <strong>undo changes, compare versions, and restore earlier drafts</strong>if needed, providing a safety net against mistakes or accidental deletions.</p><p>If you change the number of allowed revisions (for example, by setting a limit in <code>wp-config.php</code>) <strong>this only affects new revisions going forward</strong>. Existing posts will still keep all their current revisions unless you manually delete the extra ones. Limiting revisions does not automatically remove old ones; you must clean them up separately if you want to reduce database size.</p>', 'adminease' ),
			'placeholder'  => __( 'Select', 'adminease' ),
			'attributes'   => [
				'data-allow_clear' => true,
			],
			'child_fields' => [
				[
					'type'              => 'number',
					'id'                => 'number-posts-revisions-other',
					'name'              => 'adminease[posts][number_posts_revisions_other]',
					'value'             => $this->settings['number_posts_revisions_other'] ?? '',
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Number of post revisions', 'adminease' ),
					'field_description' => __( 'Any number greater than 0 (for example, 3): WordPress will only keep that many old versions for each post. (For example, if you set it to 3, only your 3 most recent edits are saved. Older versions are deleted automatically when you make new edits.)', 'adminease' ),
					'attributes'        => [
						'min'         => '-1',
						'step'        => 1,
						'data-parent' => 'number-posts-revisions',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Processes and saves the sanitized settings related to post revisions.
	 * Based on the provided settings input, it determines the appropriate value for the
	 * 'WP_POST_REVISIONS' constant and updates it accordingly using the FileHandler.
	 *
	 * @param array $sanitized_settings An associative array of sanitized settings, which
	 * includes the configuration for the number of post revisions and its related options.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		if( 'keep' === $sanitized_settings['posts']['number_posts_revisions'] ) {
			$value = true;
		} else if( 'dont-keep' === $sanitized_settings['posts']['number_posts_revisions'] ) {
			$value = false;
		} else if( 'other' === $sanitized_settings['posts']['number_posts_revisions'] ) {
			$value = intval( $sanitized_settings['posts']['number_posts_revisions_other'] );
		} else {
			$value = '';
		}
		
		Plugin::$FileHandler->stack_wp_config_constant( 'WP_POST_REVISIONS', $value );
	}
}