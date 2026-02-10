<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const AO_OPTION_NAMES = [
	'adminoptim-modules',
	'adminoptim-custom-taxonomies',
	'adminoptim_modified_date',
	'adminoptim_limit_image_size',
	'adminoptim_custom_login',
	'adminoptim_block_login',
	'adminoptim_2fa',
	'adminoptim_smtp',
	'adminoptim_heartbeat_control',
	'adminoptim_db_cleaner',
	'adminoptim-post-types',
	'adminoptim_post_cloner',
	'adminoptim_ads_robots_txt',
	'adminoptim_xml_sitemap',
];
