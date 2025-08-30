<?php

class LP_Collections_Settings extends LP_Abstract_Settings_Page {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id   = 'collections';
		$this->text = __( 'Collections', 'learnpress-collections' );
		add_action('learn-press/update-settings/updated', array($this, 'update_settings'));
		parent::__construct();
	}

	public function get_settings( $section = '', $tab = '' ) {
		return array(
			array(
				'title'   => __( 'Collections Slug', 'learnpress-collections' ),
				'id'      => 'collections[slug]',
				'default' => 'collections',
				'type'    => 'text',
				'placeholder' => 'collections'
			),
		);
	}

	public function update_settings($input){
		$page 	= filter_input( INPUT_GET, 'page' );
		$tab 	= filter_input( INPUT_GET, 'tab' );
		$learn_press_collections = isset($_POST['learn_press_collections'])?$_POST['learn_press_collections']:array();
		$lp_settings_nonce = filter_input( INPUT_POST, 'lp-settings-nonce');
		if( $page == 'learn-press-settings' && $tab == 'collections' && !empty($learn_press_collections) && !empty($lp_settings_nonce) ){
			set_transient( 'learn-press-collection-flush-rewrite-rules', true );
		}
	}

}

return new LP_Collections_Settings();