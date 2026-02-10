<?php

class Hook_Log {

	public static function start() {
	 add_action( 'all', array( __CLASS__, 'filter_it' ) );
	 //add_action( 'shutdown', array( __CLASS__, 'die_it' ) );
	}

    static $hooks = array();

	static $filter_groups = array(
		'misc' => array(
			'exact' => array(
				'query',
				'sanitize_key',
				'sanitize_title',
				'wp_register_sidebar_widget',
				'register_sidebar',
				'wp_default_scripts',
				'wp_default_styles',
				'auth_cookie_valid',
				'muplugins_loaded',
				'theme_root',
				'attribute_escape',
				'set_url_scheme',
				'site_url',
				'home_url',
				'get_user_metadata',
				'admin_url',
				'wp_parse_str',
				'nonce_life',
				'salt',
				'bloginfo',
			),
			'regex' => array(),
		),
		'escape' => array(
			'exact' => array(
				'esc_html',
				'esc_attr',
				'esc_url',
				'clean_url',
			),
			'regex' => array(),
		),
		'options' => array(
			'exact' => array(
				'update_option',
				'updated_option',
				'update_site_option',
			),
			'regex' => array(
				'/pre_option_/',
				'/option_/',
				'/pre_transient_/',
				'/site_transient_/',
			),
		),
		'posttypes' => array(
			'exact' => array(
				'registered_post_type',
			),
			'regex' => array(
				'/post_type_labels_/',
			),
		),
		'taxonomies' => array(
			'exact' => array(
				'registered_taxonomy',
			),
			'regex' => array(),
		),
		'capabilities' => array(
			'exact' => array(
				'map_meta_cap',
				'user_has_cap',
			),
			'regex' => array(),
		),
		'translation' => array(
			'exact' => array(
				'number_format_i18n',
				'ngettext',
				'gettext',
				'gettext_with_context',
				'override_load_textdomain',
				'locale',
				'language_attributes',
			),
			'regex' => array(
				'/load_textdomain/',
			),
		),
		'texturize' => array(
			'exact' => array(),
			'regex' => array(
				'/no_texturize_/'
			),
		),
		'admin_bar' => array(
			'exact' => array(
				'admin_bar_menu',
			),
			'regex' => array(),
		)
	);


public static function filter_it() {
	$args = func_get_args();
	$matched = false;
	$regex_matched = false;
	$hook_name = array_shift( $args );
	$hook_info = array(
		'name' => $hook_name,
		'args' => $args,
		'groups' => array()
	);
	foreach ( self::$filter_groups as $ignore_name => $ignore_group ) {
		$ignore_class = 'hook-group-'  . $ignore_name;
		foreach ( $ignore_group['exact'] as $value ) {
			if( $value === $hook_name && !in_array( $ignore_class, $hook_info['groups'] ) ) {
				$hook_info['groups'][] = $ignore_class;
			}
		}
		foreach ( $ignore_group['regex'] as $regex ) {
			if ( preg_match( $regex, $hook_name ) && !in_array( $ignore_class, $hook_info['groups'] ) ) {
				$hook_info['groups'][] = $ignore_class;
			}
		}
	}

	if ( !$matched && !$regex_matched ) {
		self::$hooks[] = $hook_info;
	}

	/*
	if ( !in_array( $args[0], self::$skip ) ) {
		$preg_matched = false;
		foreach ( self::$preg_skip as $preg ) {
			if ( preg_match( $preg, $args[0] ) ) {
				$preg_matched = true;
			}
		}
		if ( !$preg_matched ) {
			self::$actions[] = $args;
		}
	}*/
}


public static function output_it() {
	foreach ( self::$hooks as $action ) {
		echo '<pre>';
		echo esc_html( print_r( $action, true ) );
		echo "</pre>\n";
	}
}

public static function die_it() {
	self::output_it();
	die();
}
}

Hook_Log::start();