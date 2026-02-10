<?php

namespace Dev4Press\Plugin\SweepPress\Meta;

use Dev4Press\Plugin\SweepPress\DB\Shared;
use Dev4Press\v54\Core\Quick\WPR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @method array options()      options()
 * @method array sitemeta()     sitemeta()
 * @method array postmeta()     postmeta()
 * @method array usermeta()     usermeta()
 * @method array termmeta()     termmeta()
 * @method array commentmeta()  commentmeta()
 * @method array blogmeta()     blogmeta()
 */
class Storage {
	public array $data = array(
		'options'     => array(
			'siteurl',
			'home',
			'blogname',
			'blogdescription',
			'cron',
			'users_can_register',
			'admin_email',
			'start_of_week',
			'use_balanceTags',
			'use_smilies',
			'require_name_email',
			'comments_notify',
			'posts_per_rss',
			'rss_use_excerpt',
			'mailserver_url',
			'mailserver_login',
			'mailserver_pass',
			'mailserver_port',
			'default_category',
			'default_comment_status',
			'default_ping_status',
			'default_pingback_flag',
			'posts_per_page',
			'date_format',
			'time_format',
			'links_updated_date_format',
			'comment_moderation',
			'moderation_notify',
			'permalink_structure',
			'rewrite_rules',
			'hack_file',
			'blog_charset',
			'moderation_keys',
			'active_plugins',
			'category_base',
			'ping_sites',
			'comment_max_links',
			'gmt_offset',
			'default_email_category',
			'recently_edited',
			'template',
			'stylesheet',
			'comment_registration',
			'html_type',
			'use_trackback',
			'default_role',
			'db_version',
			'uploads_use_yearmonth_folders',
			'upload_path',
			'blog_public',
			'default_link_category',
			'show_on_front',
			'tag_base',
			'show_avatars',
			'avatar_rating',
			'upload_url_path',
			'thumbnail_size_w',
			'thumbnail_size_h',
			'thumbnail_crop',
			'medium_size_w',
			'medium_size_h',
			'avatar_default',
			'large_size_w',
			'large_size_h',
			'image_default_link_type',
			'image_default_size',
			'image_default_align',
			'close_comments_for_old_posts',
			'close_comments_days_old',
			'thread_comments',
			'thread_comments_depth',
			'page_comments',
			'comments_per_page',
			'default_comments_page',
			'comment_order',
			'sticky_posts',
			'uninstall_plugins',
			'timezone_string',
			'page_for_posts',
			'page_on_front',
			'default_post_format',
			'link_manager_enabled',
			'finished_splitting_shared_terms',
			'site_icon',
			'medium_large_size_w',
			'medium_large_size_h',
			'wp_page_for_privacy_policy',
			'show_comments_cookies_opt_in',
			'admin_email_lifespan',
			'disallowed_keys',
			'comment_previously_approved',
			'auto_plugin_theme_update_emails',
			'auto_update_core_dev',
			'auto_update_core_minor',
			'auto_update_core_major',
			'wp_force_deactivated_plugins',
			'wp_attachment_pages_enabled',
			'customize_stashed_theme_mods',
			'WPLANG',
			'can_compress_scripts',
			'active_sitewide_plugins',
			'nav_menu_options',
			'new_admin_email',
			'recovery_mode_email_last_sent',
			'wp_calendar_block_has_published_posts',
			'finished_updating_comment_type',
			'theme_switch_menu_locations',
			'theme_switched_via_customizer',
			'theme_switched',
			'current_theme',
			'sidebars_widgets',
			'recovery_keys',
			'recently_activated',
			'auto_core_update_notified',
			'auto_update_plugins',
			'db_upgraded',
			'https_detection_errors',
			'fresh_site',
			'initial_db_version',
			'upload_space_check_disabled',
			'blog_upload_space',
			'user_count',
			'site_logo',
			'auto_updater.lock',
			'core_updater.lock',
			'post_count',
			'dismissed_update_core',
			'dashboard_widget_options',
			'_split_terms',
			'_wp_suggested_policy_text_has_changed',
		),
		'sitemeta'    => array(
			'site_name',
			'admin_email',
			'admin_user_id',
			'registration',
			'upload_filetypes',
			'blog_upload_space',
			'fileupload_maxk',
			'site_admins',
			'allowedthemes',
			'illegal_names',
			'wpmu_upgrade_site',
			'welcome_email',
			'first_post',
			'siteurl',
			'add_new_users',
			'upload_space_check_disabled',
			'subdomain_install',
			'global_terms_enabled',
			'ms_files_rewriting',
			'initial_db_version',
			'active_sitewide_plugins',
			'WPLANG',
			'user_count',
			'blog_count',
			'can_compress_scripts',
			'registrationnotification',
			'welcome_user_email',
			'menu_items',
			'first_page',
			'first_comment',
			'first_comment_url',
			'first_comment_author',
			'limited_email_domains',
			'banned_email_domains',
			'new_admin_email',
			'first_comment_email',
			'first_comment_author',
			'first_comment_url',
			'first_comment',
			'auto_update_plugins',
			'recently_activated',
			'site_meta_supported',
			'auto_core_update_notified',
			'dismissed_update_core',
			'wp_force_deactivated_plugins',
		),
		'postmeta'    => array(
			'imagedata',
			'_encloseme',
			'_pingme',
			'_trackbackme',
			'_thumbnail_id',
			'_edit_last',
			'_edit_lock',
			'_wp_old_slug',
			'_wp_old_date',
			'_wp_trash_meta_status',
			'_wp_trash_meta_time',
			'_wp_page_template',
			'_wp_attached_file',
			'_wp_attachment_metadata',
			'_wp_attachment_image_alt',
			'_wp_attachment_context',
			'_wp_attachment_is_custom_background',
			'_wp_attachment_is_custom_header',
			'_wp_attachment_backup_sizes',
			'_wp_user_request_confirmed_timestamp',
			'_wp_user_request_completed_timestamp',
			'_wp_suggested_privacy_policy_content',
			'_wp_admin_notified',
			'_wp_user_notified',
			'_menu_item_type',
			'_menu_item_menu_item_parent',
			'_menu_item_object_id',
			'_menu_item_object',
			'_menu_item_target',
			'_menu_item_classes',
			'_menu_item_xfn',
			'_menu_item_url',
			'_menu_item_orphaned',
			'_starter_content_theme',
			'_customize_draft_post_name',
			'_customize_changeset_uuid',
			'_export_data_grouped',
			'_export_file_name',
			'_export_file_path',
			'_export_data_raw',
			'_format_url',
			'_format_link_url',
			'_format_quote_source_url',
			'_format_quote',
			'_format_quote_source_name',
			'_format_image',
			'_format_gallery',
			'_format_audio_embed',
			'_format_video_embed',
		),
		'usermeta'    => array(
			'first_name',
			'last_name',
			'nickname',
			'description',
			'rich_editing',
			'admin_color',
			'use_ssl',
			'locale',
			'jabber',
			'yim',
			'aim',
			'enable_custom_fields',
			'community-events-location',
			'comment_shortcuts',
			'show_admin_bar_front',
			'show_welcome_panel',
			'dismissed_wp_pointers',
			'nav_menu_recently_edited',
			'wporg_favorites',
			'primary_blog',
			'source_domain',
			'default_password_nag',
			'session_tokens',
			'user-settings',
			'user-settings-time',
			'dashboard_quick_press_last_post_id',
			'media_library_mode',
			'syntax_highlighting',
			'sites_network_per_page',
			'_application_passwords',
			'_new_email',
		),
		'termmeta'    => array(),
		'commentmeta' => array(
			'_wp_trash_meta_status',
			'_wp_trash_meta_time',
		),
		'blogmeta'    => array(
			'db_version',
			'db_last_updated',
		),
	);
	public array $regex = array(
		'options'     => array(
			'^_transient_(.+)',
			'^_site_transient_(.+)',
			'^_transient_timeout_(.+)',
			'^_site_transient_timeout_(.+)',
		),
		'sitemeta'    => array(
			'^_site_transient_(.+)',
			'^_site_transient_timeout_(.+)',
		),
		'postmeta'    => array(
			'^_oembed_(.+)',
			'^_oembed_time_(.+)',
			'^_wp_attachment_custom_header_last_used_(.+)',
		),
		'usermeta'    => array(
			'^closedpostboxes_(.+)$',
			'^metaboxhidden_(.+)$',
			'^meta-box-order_(.+)$',
			'^screen_layout_(.+)$',
			'^manage(.+)columnshidden$',
			'^edit(.+)per_page$',
		),
		'termmeta'    => array(),
		'commentmeta' => array(),
		'blogmeta'    => array(),
	);
	public array $map = array(
		'post'        => 'postmeta',
		'user'        => 'usermeta',
		'term'        => 'termmeta',
		'comment'     => 'commentmeta',
		'blog'        => 'blogmeta',
		'option'      => 'options',
		'site_option' => 'sitemeta',
	);
	public array $detection = array(
		'component' => array(
			array(
				'regex' => array(
					'options' => array( '^action_scheduler_|-ActionScheduler_' ),
				),
				'label' => 'Action Scheduler',
				'code'  => 'action-scheduler',
			),
			array(
				'regex' => array(
					'options' => array( '^edd_sl_' ),
				),
				'label' => 'Easy Digital Download Updater',
				'code'  => 'easy-digital-download-updater',
			),
			array(
				'regex' => array(
					'options' => array( '^fs_' ),
				),
				'label' => 'Freemius Library',
				'code'  => 'freemius',
			),
		),
		'cache'     => array(
			array(
				'regex' => array(
					'options' => array( '_children$' ),
				),
				'label' => 'Taxonomy Terms Children',
				'code'  => 'term-children',
			),
		),
		'theme'     => array(
			array(
				'regex' => array(
					'options'  => array( '^(astra_|astra-)' ),
					'postmeta' => array( '^_astra_' ),
				),
				'label' => 'Astra',
				'code'  => 'astra',
			),
			array(
				'regex' => array(
					'options' => array( 'blocksy' ),
				),
				'label' => 'Blocksy',
				'code'  => 'blocksy',
			),
			array(
				'regex' => array(
					'options'  => array( '^(generate_|generatepress_)' ),
					'postmeta' => array( '^(_generate_|_generate-)' ),
				),
				'label' => 'GeneratePress',
				'code'  => 'generatepress',
			),
		),
		'plugin'    => array(
			array(
				'regex' => array(
					'options'     => array( '^(akismet_)' ),
					'commentmeta' => array( '^(akismet_)' ),
				),
				'items' => array(
					'options' => array( 'wordpress_api_key', ),
				),
				'label' => 'Akismet',
				'code'  => 'akismet/akismet.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(_bbp_)' ),
					'postmeta' => array( '^(_bbp_)' ),
					'usermeta' => array( '^(_bbp_)', '(_bbp_last_posted|_bbp_reply_count|_bbp_topic_count|_bbp_subscriptions|_bbp_favorites|_bbp_activation_redirect)$' ),
				),
				'label' => 'bbPress',
				'code'  => 'bbpress/bbpress.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(d4p_blog_breadcrumbspress_)' ),
				),
				'label' => 'BreadcrumbsPress',
				'code'  => 'breadcrumbspress/breadcrumbspress.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(d4p_blog_coreactivity_|d4p_network_coreactivity_)' ),
					'sitemeta' => array( '^d4p_network_coreactivity_' ),
					'usermeta' => array( '^coreactivity_' ),
					'logmeta'  => array( '^(device_|old_|new_|plugin_|theme_|post_|comment_|author_|attachment_|user_)' ),
				),
				'items' => array(
					'logmeta' => array( 'user', 'old', 'new' ),
				),
				'label' => 'coreActivity',
				'code'  => 'coreactivity/coreactivity.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(d4p_blog_corebackup_|d4p_network_corebackup_)' ),
					'sitemeta' => array( '^d4p_network_corebackup_' ),
					'logmeta'  => array( '^(backup_)' ),
				),
				'label' => 'coreBackup',
				'code'  => 'corebackup/corebackup.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(d4p_blog_coretools_|d4p_network_coretools_)' ),
					'sitemeta' => array( '^d4p_network_coretools_' ),
				),
				'label' => 'coreTools',
				'code'  => 'coretools/coretools.php',
			),
			array(
				'regex' => array(
					'options'     => array( '^(d4p_blog_coresecurity_|d4p_network_coresecurity_)' ),
					'sitemeta'    => array( '^d4p_network_coresecurity_' ),
					'postmeta'    => array( '^_coresecurity_' ),
					'commentmeta' => array( '^_coresecurity_' ),
					'usermeta'    => array( '^coresecurity_' ),
					'logmeta'     => array( '^(ban_)' ),
				),
				'label' => 'coreSecurity',
				'code'  => 'coresecurity/coresecurity.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^d4p_blog_coresocial_' ),
					'postmeta' => array( '^_coresocial_' ),
				),
				'label' => 'coreSocial',
				'code'  => 'coresocial/coresocial.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(d4p_blog_sweeppress_|d4p_network_sweeppress_)' ),
					'sitemeta' => array( '^(d4p_network_sweeppress_)' ),
				),
				'label' => 'SweepPress',
				'code'  => array(
					'sweeppress/sweeppress.php',
					'sweeppress-pro/sweeppress.php',
				),
			),
			array(
				'regex' => array(
					'options' => array( '^(debugpress_)' ),
				),
				'label' => 'DebugPress',
				'code'  => 'debugpress/debugpress.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(d4p_blog_forummod-for-bbpress)' ),
				),
				'label' => 'forumMOD for bbPress',
				'code'  => 'forummod-for-bbpress/forummod-for-bbpress.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^dev4press_gd-seo-toolbox_' ),
					'postmeta' => array( '^_gdseo_' ),
					'termmeta' => array( '^_gdseo_' ),
					'usermeta' => array( '^_gdseo_' ),
				),
				'label' => 'GD SEO Toolbox',
				'code'  => 'gd-seo-toolbox/gd-seo-toolbox.php',
			),
			array(
				'regex' => array(
					'options'     => array( '^(d4p_blog_gd-security-toolbox_|d4p_network_gd-security-toolbox_)' ),
					'sitemeta'    => array( '^d4p_network_gd-security-toolbox_' ),
					'commentmeta' => array( '^_gdsec_' ),
					'usermeta'    => array( '^gdsec_' ),
				),
				'label' => 'GD Security Toolbox',
				'code'  => 'gd-security-toolbox/gd-security-toolbox.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(dev4press_gd-mail-queue_)' ),
				),
				'label' => 'GD Mail Queue',
				'code'  => 'gd-mail-queue/gd-mail-queue.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(d4p_blog_gd-content-tools_)' ),
				),
				'label' => 'GD Content Tools',
				'code'  => 'gd-content-tools/gd-content-tools.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(dev4press_gd-rating-system_)' ),
				),
				'label' => 'GD Rating System',
				'code'  => 'gd-rating-system/gd-rating-system.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(d4p_blog_gd-press-tools_|d4p_network_gd-press-tools_|d4p_overload_gd-press-tools_)' ),
					'sitemeta' => array( '^(d4p_network_gd-press-tools_|d4p_overload_gd-press-tools_)' ),
					'usermeta' => array( '^(gdpet_|gdpt_)' ),
					'postmeta' => array( '^_gdpet_' ),
				),
				'label' => 'GD Press Tools',
				'code'  => 'gd-press-tools/gd-press-tools.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(d4p_blog_gd-topic-polls_)' ),
					'postmeta' => array( '^_poll_' ),
					'usermeta' => array( '^(gdpol_)' ),
				),
				'label' => 'GD Topic Polls for bbPress',
				'code'  => 'gd-topic-polls/gd-topic-polls.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(dev4press_gd-forum-notices-for-bbpress_)' ),
					'postmeta' => array( '^(rule-ctrl-|rule-data-)' ),
				),
				'label' => 'GD Forum Notices for bbPress',
				'code'  => 'gd-forum-notices-for-bbpress/gd-forum-notices-for-bbpress.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(d4p_blog_gd-topic-prefix_)' ),
					'termmeta' => array( '^_gdtox_' ),
				),
				'label' => 'GD Topic Prefix for bbPress',
				'code'  => 'gd-topic-prefix/gd-topic-prefix.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(dev4press_gd-bbpress-toolbox_)' ),
					'usermeta' => array( '^gdbbx_', '(thanks-notification|topic-auto-closed-notification|topic-closed-notification|bbp_last_activity)$' ),
				),
				'items' => array(
					'usermeta' => array( 'bbp_last_activity', ),
				),
				'label' => 'GD bbPress Toolbox',
				'code'  => 'gd-bbpress-toolbox/gd-bbpress-toolbox.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(dev4press_gd-knowledge-base_)' ),
					'postmeta' => array( '^_gdkob_' ),
					'termmeta' => array( '^gdkob_' ),
					'usermeta' => array( '^(_gdkob_|gdkob_)' ),
				),
				'label' => 'GD Knowledge Base',
				'code'  => 'gd-knowledge-base/gd-knowledge-base.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(dev4press_gd-security-headers_)' ),
					'sitemeta' => array( '^(dev4press_gd-security-headers_)' ),
				),
				'label' => 'GD Security Headers',
				'code'  => 'gd-security-headers/gd-security-headers.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(dev4press_gd-quantum-theme-for-bbpress_)' ),
				),
				'label' => 'GD Quantum Theme for bbPress',
				'code'  => 'gd-quantum-theme-for-bbpress/gd-quantum-theme-for-bbpress.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(dev4press_gd-forum-manager-for-bbpress_)' ),
				),
				'label' => 'GD Forum Manager for bbPress',
				'code'  => 'gd-forum-manager-for-bbpress/gd-forum-manager-for-bbpress.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(d4p_blog_gd-power-search-for-bbpress_|dev4press_gd-power-search_)' ),
				),
				'label' => 'GD Power Search for bbPress',
				'code'  => 'gd-power-search-for-bbpress/gd-power-search-for-bbpress.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(d4p_blog_gd-members-directory-for-bbpress_)' ),
				),
				'label' => 'GD Members Directory for bbPress',
				'code'  => 'gd-members-directory-for-bbpress/gd-members-directory-for-bbpress.php',
			),
			array(
				'items' => array(
					'options' => array( 'gd-bbpress-attachments' ),
				),
				'label' => 'GD bbPress Attachments',
				'code'  => 'gd-bbpress-attachments/gd-bbpress-attachments.php',
			),
			array(
				'items' => array(
					'options' => array( 'gd-bbpress-tools' ),
				),
				'label' => 'GD bbPress Tools',
				'code'  => 'gd-bbpress-tools/gd-bbpress-tools.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(dev4press_dev4press-updater_)' ),
					'sitemeta' => array( '^(dev4press_dev4press-updater_)' ),
				),
				'label' => 'Dev4Press Updater',
				'code'  => 'dev4press-updater/dev4press-updater.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(aiowpsec_|aio_wp_)' ),
				),
				'label' => 'All In One WP Security',
				'code'  => 'all-in-one-wp-security-and-firewall/wp-security.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(enlighter-)' ),
				),
				'label' => 'Enlighter',
				'code'  => 'enlighter/Enlighter.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(bsp_)' ),
				),
				'label' => 'bbp style pack',
				'code'  => 'bbp-style-pack/bbp-style-pack.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(_bp_|bp_|bp-)' ),
				),
				'label' => 'BuddyPress',
				'code'  => 'buddypress/bp-loader.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(cleantalk_)' ),
				),
				'label' => 'Anti-Spam by CleanTalk',
				'code'  => 'cleantalk-spam-protect/cleantalk.php',
			),
			array(
				'regex' => array(
					'options' => array( '^wpcf7$' ),
				),
				'label' => 'Contact Form 7',
				'code'  => 'contact-form-7/wp-contact-form-7.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(duplicate_post_)' ),
					'postmeta' => array( '^(_dp_)' ),
				),
				'label' => 'Yoast Duplicate Post',
				'code'  => 'duplicate-post/duplicate-post.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(updraft_|updraftplus_)' ),
				),
				'label' => 'UpdraftPlus',
				'code'  => 'updraftplus/updraftplus.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(elementor_|_elementor_)' ),
					'postmeta' => array( '^_elementor_' ),
				),
				'label' => 'Elementor',
				'code'  => 'elementor/elementor.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(fluentform_)' ),
				),
				'label' => 'Fluent Form',
				'code'  => 'fluentform/fluentform.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(frm_|frmpro_)' ),
				),
				'label' => 'Formidable',
				'code'  => 'formidable/formidable.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(forminator_)' ),
					'postmeta' => array( '^(forminator_)' ),
				),
				'label' => 'Forminator',
				'code'  => 'forminator/forminator.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(gen_premium_|gp_premium_|_generatepress_|generatepress_sites|generate_update_)' ),
				),
				'label' => 'GeneratePress Premium',
				'code'  => 'gp-premium/gp-premium.php',
			),
			array(
				'regex' => array(
					'postmeta' => array( '^(_generateblocks_)' ),
				),
				'label' => 'GenerateBlocks',
				'code'  => array(
					'generateblocks/plugin.php',
					'generateblocks-pro/plugin.php',
				),
			),
			array(
				'regex' => array(
					'options' => array( '^(gf_|rg_gforms_|gform_|rg_form_|gravityformsaddon_)' ),
				),
				'label' => 'Gravity Forms',
				'code'  => 'gravityforms/gravityforms.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(jetpack_)' ),
				),
				'label' => 'JetPack',
				'code'  => 'jetpack/jetpack.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(ninja_forms_|nf_|ninja-forms-)' ),
				),
				'label' => 'Ninja Forms',
				'code'  => 'ninja-forms/ninja-forms.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(_ninja_tables_)' ),
					'postmeta' => array( '^(_ninja_tables_|__ninja_)' ),
				),
				'items' => array(
					'options'  => array(),
					'postmeta' => array( '_has_ninja_tables', '_last_edited_by', '_last_edited_time', '_last_external_cached_time', '_tax_class', '_tax_status', '_virtual' ),
				),
				'label' => 'Ninja Tables',
				'code'  => array(
					'ninja-tables/ninja-tables.php',
					'ninja-tables-pro/ninja-tables-pro.php',
				),
			),
			array(
				'regex' => array(
					'options' => array( '^(moppm_)' ),
				),
				'label' => 'Password Policy Manager',
				'code'  => 'password-policy-manager/miniorange-password-policy-setting.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(rank_math_|rank-math-)' ),
					'postmeta' => array( '^(rank_math_)' ),
				),
				'label' => 'Rank Math SEO',
				'code'  => array(
					'seo-by-rank-math/rank-math.php',
					'seo-by-rank-math-pro/rank-math-pro.php',
				),
			),
			array(
				'regex' => array(
					'options' => array( '^(wpdm_|wp_dark_mode_)' ),
				),
				'label' => 'WP Dark Mode',
				'code'  => array(
					'wp-dark-mode/plugin.php',
					'wp-dark-mode-pro/plugin.php',
				),
			),
			array(
				'regex' => array(
					'options' => array( '^(secupress_)' ),
				),
				'label' => 'SecuPress',
				'code'  => 'secupress/secupress.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(simple_history_)' ),
				),
				'label' => 'Simple History',
				'code'  => 'simple-history/index.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(titan_)' ),
				),
				'label' => 'Titan Anti-spam & Security',
				'code'  => 'anti-spam/anti-spam.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(wal_checkbox_|wal_log_)' ),
				),
				'label' => 'Activity Log WinterLock',
				'code'  => 'winterlock/winterlock.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(mailster_|_mailster_)' ),
					'postmeta' => array( '^_mailster_' ),
				),
				'items' => array(
					'options'     => array( 'envato_plugins', 'updatecenter_plugins' ),
					'commentmeta' => array( 'newsletter_signup' ),
				),
				'label' => 'Mailster',
				'code'  => 'mailster/mailster.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(amp_|amp-)' ),
					'postmeta' => array( '^_amp_' ),
				),
				'label' => 'Amp',
				'code'  => 'amp/amp.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(wc_|woocommerce_)' ),
					'usermeta' => array( '^(_woocommerce_)' ),
				),
				'items' => array(
					'options'  => array( 'default_product_cat', 'current_theme_supports_woocommerce' ),
					'postmeta' => array( '_downloadable', '_download_expiry', '_download_limit', '_sold_individually', '_tax_class', '_tax_status', '_virtual' ),
				),
				'label' => 'WooCommerce',
				'code'  => 'woocommerce/woocommerce.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(wpfl_)' ),
				),
				'label' => 'WPForce Logout',
				'code'  => 'wp-force-logout/wp-force-logout.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(wsal_)' ),
				),
				'label' => 'WP Activity Log',
				'code'  => 'wp-security-audit-log/wp-security-audit-log.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(wpil_)' ),
					'postmeta' => array( '^(wpil_)' ),
					'termmeta' => array( '^(wpil_)' ),
					'usermeta' => array( '^(wpil_)' ),
				),
				'label' => 'Link Whisper',
				'code'  => 'link-whisper/link-whisper.php',
			),
			array(
				'regex' => array(
					'options' => array( '^(wpforo_)' ),
				),
				'label' => 'wpForo',
				'code'  => 'wpforo/wpforo.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(wpforms_|_wpforms_)' ),
					'postmeta' => array( '^(wpforms_)' ),
				),
				'label' => 'WPForms',
				'code'  => array(
					'wpforms/wpforms.php',
					'wpforms-lite/wpforms.php',
				),
			),
			array(
				'regex' => array(
					'options'  => array( '^(wpseo_)' ),
					'postmeta' => array( '^(_yoast_)' ),
					'usermeta' => array( '^(_yoast_|wpseo_|wpseo-)', '_wpseo|_yoast_' ),
				),
				'label' => 'Yoast SEO',
				'code'  => array(
					'wordpress-seo/wp-seo.php',
				),
			),
			array(
				'regex' => array(
					'options'  => array( '^(subscribe_reloaded_)' ),
					'postmeta' => array( '^(_stcr\@_)' ),
				),
				'label' => 'Subscribe to Comments Reloaded',
				'code'  => 'subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(job_manager_)' ),
					'postmeta' => array( '^(_job_)' ),
				),
				'items' => array(
					'options' => array( 'wp_job_manager_version' ),
				),
				'label' => 'WP Job Manager',
				'code'  => 'wp-job-manager/wp-job-manager.php',
			),
			array(
				'regex' => array(
					'options'  => array( '^(rocketcdn_|wp_rocket_|rocket_|wpr_)' ),
					'postmeta' => array( '^(_rocket_)' ),
					'usermeta' => array( '^(rocket_|rocketcdn_)' ),
				),
				'items' => array(
					'options' => array( 'wp_rocket_settings', 'wp_rocket_no_licence' ),
				),
				'label' => 'WP Rocket',
				'code'  => 'wp-rocket/wp-rocket.php',
			),
		),
	);
	public array $registered = array();
	public array $installed = array(
		'plugins-codes' => array(),
		'plugins-names' => array(),
	);
	public array $blog_ids = array();
	public array $flags = array(
		'multisite-usermeta-prefixed' => false,
	);
	public array $db_tables = array();

	public function __construct() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$this->data['options'][] = Shared::instance()->prefix() . 'user_roles';

		$this->data['usermeta'][] = Shared::instance()->base_prefix() . 'persisted_preferences';
		$this->data['usermeta'][] = Shared::instance()->base_prefix() . 'capabilities';
		$this->data['usermeta'][] = Shared::instance()->base_prefix() . 'user_level';
		$this->data['usermeta'][] = Shared::instance()->base_prefix() . 'user-settings';
		$this->data['usermeta'][] = Shared::instance()->base_prefix() . 'user-settings-time';
		$this->data['usermeta'][] = Shared::instance()->base_prefix() . 'primary_blog';
		$this->data['usermeta'][] = Shared::instance()->base_prefix() . 'dashboard_quick_press_last_post_id';
		$this->data['usermeta'][] = Shared::instance()->base_prefix() . 'media_library_mode';

		$installed = get_plugins();

		foreach ( $installed as $plugin => $object ) {
			$parts = explode( '/', $plugin );

			$this->installed['plugins-codes'][ $parts[0] ] = $plugin;
			$this->installed['plugins-names'][ $plugin ]   = $object['Name'];
		}
	}

	public static function instance() : Storage {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Storage();
		}

		return $instance;
	}

	public function __call( $name, $arguments ) {
		if ( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];
		}

		return array();
	}

	public function is( $flag ) {
		return $this->flags[ $flag ] ?? false;
	}

	public function flag( $flag, $value = true ) {
		$this->flags[ $flag ] = $value;
	}

	public function usermeta_prefixed_regex() {
		$this->regex['usermeta'][] = '^' . Shared::instance()->base_prefix() . '\d+_persisted_preferences$';
		$this->regex['usermeta'][] = '^' . Shared::instance()->base_prefix() . '\d+_capabilities$';
		$this->regex['usermeta'][] = '^' . Shared::instance()->base_prefix() . '\d+_user_level$';
		$this->regex['usermeta'][] = '^' . Shared::instance()->base_prefix() . '\d+_user-settings$';
		$this->regex['usermeta'][] = '^' . Shared::instance()->base_prefix() . '\d+_user-settings-time$';
		$this->regex['usermeta'][] = '^' . Shared::instance()->base_prefix() . '\d+_primary_blog$';
		$this->regex['usermeta'][] = '^' . Shared::instance()->base_prefix() . '\d+_dashboard_quick_press_last_post_id$';
		$this->regex['usermeta'][] = '^' . Shared::instance()->base_prefix() . '\d+_media_library_mode$';
	}

	public function usermeta_include_prefixed_options() {
		if ( is_multisite() && ! $this->is( 'multisite-usermeta-prefixed' ) ) {
			$ids = $this->get_blog_ids();

			foreach ( $ids as $id ) {
				$this->data['usermeta'][] = Shared::instance()->base_prefix() . $id . '_persisted_preferences';
				$this->data['usermeta'][] = Shared::instance()->base_prefix() . $id . '_capabilities';
				$this->data['usermeta'][] = Shared::instance()->base_prefix() . $id . '_user_level';
				$this->data['usermeta'][] = Shared::instance()->base_prefix() . $id . '_user-settings';
				$this->data['usermeta'][] = Shared::instance()->base_prefix() . $id . '_user-settings-time';
				$this->data['usermeta'][] = Shared::instance()->base_prefix() . $id . '_primary_blog';
				$this->data['usermeta'][] = Shared::instance()->base_prefix() . $id . '_dashboard_quick_press_last_post_id';
				$this->data['usermeta'][] = Shared::instance()->base_prefix() . $id . '_media_library_mode';
			}
		}

		$this->flag( 'multisite-usermeta-prefixed' );
	}

	public function get_blog_ids() : array {
		if ( is_multisite() && empty( $this->blog_ids ) ) {
			if ( WPR::get_blogs_count() <= SWEEPPRESS_MULTISITE_LIMIT_COUNT ) {
				$ids = sweeppress_settings()->get_from_cache( 'list-of-blog-ids', 'metadata' );

				if ( $ids === false ) {
					$ids = Shared::instance()->get_list_of_blog_ids();

					sweeppress_settings()->set_to_cache( 'list-of-blog-ids', $ids, 'metadata' );
				}

				$this->blog_ids = $ids;
			}
		}

		return $this->blog_ids;
	}

	public function is_wordpress_core( string $scope, string $name, bool $include_regex = true ) : bool {
		$scope  = $this->map[ $scope ] ?? $scope;
		$data   = $this->data[ $scope ] ?? array();
		$listed = in_array( $name, $data );

		if ( $include_regex && ! $listed && ! empty( $this->regex[ $scope ] ) ) {
			foreach ( $this->regex[ $scope ] as $regex ) {
				if ( preg_match( '/' . $regex . '/i', $name ) ) {
					$listed = true;
					break;
				}
			}
		}

		return $listed;
	}

	public function registration_processor( string $scope, string $name ) : array {
		if ( empty( $this->registered ) ) {
			$this->registered = apply_filters( 'sweeppress_storage_registration', array() );
		}

		$detection = array();

		foreach ( $this->registered as $code => $data ) {
			$items = $data['items'][ $scope ] ?? array();

			if ( ! empty( $items ) && in_array( $name, $items ) ) {
				$detection[] = $code;

				break;
			}

			$regexes = $data['regex'][ $scope ] ?? array();

			foreach ( $regexes as $reg ) {
				$regex = '/' . $reg . '/i';

				if ( preg_match( $regex, $name ) ) {
					$detection[] = $code;

					break;
				}
			}
		}

		return $detection;
	}

	public function detection_processor( string $scope, string $name ) : array {
		$detection = array();

		foreach ( $this->detection as $type => $items ) {
			foreach ( $items as $item ) {
				$items = $item['items'][ $scope ] ?? array();

				if ( ! empty( $items ) && in_array( $name, $items ) ) {
					$codes = (array) $item['code'];

					foreach ( $codes as $code ) {
						$detection[] = $type . '::' . $code;
					}

					break 2;
				}

				$regexes = $item['regex'][ $scope ] ?? array();

				foreach ( $regexes as $reg ) {
					$regex = '/' . $reg . '/i';

					if ( preg_match( $regex, $name ) ) {
						$codes = (array) $item['code'];

						foreach ( $codes as $code ) {
							$detection[] = $type . '::' . $code;
						}

						break 3;
					}
				}
			}
		}

		return $detection;
	}

	public function detection_theme_mods( string $name ) : string {
		return 'theme::' . substr( $name, 11 );
	}

	public function items_to_results( $items ) : array {
		$results = array(
			'items'     => array(),
			'delete'    => null,
			'type'      => '',
			'installed' => null,
			'active'    => null,
		);

		foreach ( $items as $item ) {
			$parts = explode( '::', $item, 2 );
			$type  = $parts[0];
			$code  = $parts[1] ?? '';

			if ( empty( $type ) || empty( $code ) ) {
				continue;
			}

			$delete = true;
			$result = array(
				'type'      => $type,
				'code'      => $code,
				'name'      => '',
				'installed' => false,
				'active'    => false,
			);

			switch ( $type ) {
				case 'theme':
					$theme = wp_get_theme( $code );

					if ( $theme->exists() ) {
						$result['name']      = $theme->name;
						$result['installed'] = true;
						$result['active']    = sweeppress_is_theme_active( $code );
					}
					break;
				case 'widget':
					$result['name'] = $this->get_widget_info( $code );

					if ( ! empty( $result['name'] ) ) {
						$result['installed'] = true;
					}

					unset( $results['active'] );
					break;
				case 'cache':
				case 'component':
					unset( $result['installed'], $result['active'] );

					$delete = false;
					break;
				case 'wordpress':
					$result['name']      = 'Core';
					$result['installed'] = true;

					unset( $result['active'] );

					$delete = false;
					break;
				case 'plugin':
					$result['name'] = $this->installed['plugins-names'][ $code ] ?? '';

					if ( ! empty( $result['name'] ) ) {
						$result['installed'] = true;
					}

					if ( WPR::is_plugin_active( $code ) ) {
						$result['active'] = true;
					}
					break;
			}

			if ( empty( $result['name'] ) ) {
				$result['name'] = $this->get_name_from_detection( $type, $code );
			}

			$results['items'][] = $result;

			if ( empty( $results['type'] ) ) {
				$results['type'] = $result['type'];
			}

			if ( isset( $result['installed'] ) && $result['installed'] ) {
				$results['installed'] = true;
			}

			if ( isset( $result['active'] ) && $result['active'] ) {
				$results['active'] = true;
				$results['delete'] = false;
			}

			if ( is_null( $results['delete'] ) ) {
				$results['delete'] = $delete;
			}
		}

		if ( empty( $results['items'] ) ) {
			$results['type']   = 'unknown';
			$results['delete'] = true;
		}

		return $results;
	}

	public function detect_table_plugin( string $name ) : ?array {
		$this->init_db_tables();

		$detected = null;

		foreach ( $this->db_tables as $plugin ) {
			$prefixes = $plugin['prefixes'];

			foreach ( $prefixes as $prefix ) {
				if ( strpos( $name, $prefix ) === 0 ) {
					$detected = $plugin;
					break 2;
				}
			}
		}

		return $detected;
	}

	private function get_name_from_detection( $type, $code ) {
		$list = $this->detection[ $type ] ?? array();

		foreach ( $list as $item ) {
			if ( $item['code'] == $code ) {
				return $item['label'];
			}
		}

		$parts = explode( '/', $code );

		return $parts[0];
	}

	private function get_widget_info( $widget ) : string {
		if ( ! empty( $GLOBALS['wp_widget_factory'] ) ) {
			foreach ( $GLOBALS['wp_widget_factory']->widgets as $object ) {
				if ( $object->option_name == 'widget_' . $widget ) {
					return $object->name;
				}
			}
		}

		return '';
	}

	private function init_db_tables() {
		if ( ! empty( $this->db_tables ) ) {
			return;
		}

		$prefix      = Shared::instance()->prefix();
		$base_prefix = Shared::instance()->base_prefix();

		$this->db_tables = array(
			'actionscheduler' => array(
				'code'     => 'action-scheduler',
				'label'    => 'Action Scheduler',
				'icon'     => 'ui-calendar-day',
				'prefixes' => array(
					$base_prefix . 'actionscheduler_',
				),
			),
			'gdbbx'           => array(
				'code'     => 'gdbbx',
				'label'    => 'GD bbPress Toolbox',
				'icon'     => 'plugin-gd-bbpress-toolbox',
				'plugin'   => 'gd-bbpress-toolbox/gd-bbpress-toolbox.php',
				'prefixes' => array(
					$prefix . 'gdbbx_',
				),
			),
			'coreactivity'    => array(
				'code'     => 'coreactivity',
				'label'    => 'coreActivity',
				'icon'     => 'plugin-coreactivity',
				'plugin'   => 'coreactivity/coreactivity.php',
				'prefixes' => array(
					$base_prefix . 'coreactivity_',
				),
			),
			'coresecurity'    => array(
				'code'     => 'coresecurity',
				'label'    => 'coreSecurity',
				'icon'     => 'plugin-coresecurity',
				'plugin'   => 'coresecurity/coresecurity.php',
				'prefixes' => array(
					$base_prefix . 'coresecurity_',
				),
			),
			'coresocial'      => array(
				'code'     => 'coresocial',
				'label'    => 'coreSocial',
				'icon'     => 'plugin-coresocial',
				'plugin'   => 'coresocial/coresocial.php',
				'prefixes' => array(
					$prefix . 'coresocial_',
				),
			),
			'gdkob'           => array(
				'code'     => 'gdkob',
				'label'    => 'GD Knowledge Base',
				'icon'     => 'plugin-gd-knowledge-base',
				'plugin'   => 'gd-knowledge-base/gd-knowledge-base.php',
				'prefixes' => array(
					$prefix . 'gdkob_',
				),
			),
			'gdmaq'           => array(
				'code'     => 'gdmaq',
				'label'    => 'GD Mail Queue',
				'icon'     => 'plugin-gd-mail-queue',
				'plugin'   => 'gd-mail-queue/gd-mail-queue.php',
				'prefixes' => array(
					$prefix . 'gdmaq_',
				),
			),
			'gdpet'           => array(
				'code'     => 'gdpet',
				'label'    => 'GD Press Tools',
				'icon'     => 'plugin-gd-press-tools',
				'plugin'   => 'gd-press-tools/gd-press-tools.php',
				'prefixes' => array(
					$prefix . 'gdpet_',
				),
			),
			'gdsec'           => array(
				'code'     => 'gdsec',
				'label'    => 'GD Security Toolbox',
				'icon'     => 'plugin-gd-security-toolbox',
				'plugin'   => 'gd-security-toolbox/gd-security-toolbox.php',
				'prefixes' => array(
					$prefix . 'gdsec_',
				),
			),
			'gdpol'           => array(
				'code'     => 'gdpol',
				'label'    => 'GD Topic Polls for bbPress',
				'icon'     => 'plugin-gd-topic-polls',
				'plugin'   => 'gd-topic-polls/gd-topic-polls.php',
				'prefixes' => array(
					$prefix . 'gdpol_',
				),
			),
			'gdrts'           => array(
				'code'     => 'gdrts',
				'label'    => 'GD Rating System',
				'icon'     => 'plugin-gd-rating-system',
				'plugin'   => 'gd-rating-system/gd-rating-system.php',
				'prefixes' => array(
					$prefix . 'gdrts_',
				),
			),
			'buddypress'      => array(
				'code'     => 'buddypress',
				'label'    => 'BuddyPress',
				'icon'     => 'ui-calendar-day',
				'plugin'   => 'buddypress/bp-loader.php',
				'prefixes' => array(
					$base_prefix . 'bp_',
				),
			),
			'cleantalk'       => array(
				'code'     => 'cleantalk',
				'label'    => 'Ninja Forms',
				'icon'     => 'ui-shield',
				'plugin'   => 'cleantalk-spam-protect/cleantalk.php',
				'prefixes' => array(
					$prefix . 'cleantalk_',
				),
			),
			'gravityforms'    => array(
				'code'     => 'gravityforms',
				'label'    => 'Gravity Forms',
				'icon'     => 'ui-memo-pad',
				'plugin'   => 'gravityforms/gravityforms.php',
				'prefixes' => array(
					$base_prefix . 'gf_',
				),
			),
			'ninjaforms'      => array(
				'code'     => 'ninjaforms',
				'label'    => 'Ninja Forms',
				'icon'     => 'ui-memo-pad',
				'plugin'   => 'ninja-forms/ninja-forms.php',
				'prefixes' => array(
					$prefix . 'nf3_',
				),
			),
			'wpforms'         => array(
				'code'     => 'wpforms',
				'label'    => 'WPForms',
				'icon'     => 'ui-memo-pad',
				'plugin'   => array(
					'wpforms/wpforms.php',
					'wpforms-lite/wpforms.php',
				),
				'prefixes' => array(
					$prefix . 'wpforms_',
				),
			),
			'fluentform'      => array(
				'code'     => 'fluentform',
				'label'    => 'Fluent Form',
				'icon'     => 'ui-memo-pad',
				'plugin'   => 'fluentform/fluentform.php',
				'prefixes' => array(
					$prefix . 'fluentform_',
				),
			),
			'rankmath'        => array(
				'code'     => 'rankmath',
				'label'    => 'RankMath',
				'icon'     => 'ui-search',
				'plugin'   => array(
					'seo-by-rank-math/rank-math.php',
					'seo-by-rank-math-pro/rank-math-pro.php',
				),
				'prefixes' => array(
					$prefix . 'rank_math_',
				),
			),
			'wpforo'          => array(
				'code'     => 'wpforo',
				'label'    => 'wpForo Forum',
				'icon'     => 'ui-newspaper',
				'plugin'   => 'wpforo/wpforo.php',
				'prefixes' => array(
					$prefix . 'wpforo_',
				),
			),
			'woocommerce'     => array(
				'code'     => 'woocommerce',
				'label'    => 'WooCommerce',
				'icon'     => 'logo-woo',
				'plugin'   => 'woocommerce/woocommerce.php',
				'prefixes' => array(
					$prefix . 'actionscheduler_',
					$prefix . 'wc_',
					$prefix . 'woocommerce_',
				),
			),
			'yoast'           => array(
				'code'     => 'yoast',
				'label'    => 'Yoast SEO',
				'icon'     => 'ui-search',
				'plugin'   => 'wordpress-seo/wp-seo.php',
				'prefixes' => array(
					$prefix . 'yoast_',
				),
			),
			'wp-rocket'       => array(
				'code'     => 'wp-rocket',
				'label'    => 'WP Rocket',
				'icon'     => 'ui-rabbit',
				'plugin'   => 'wp-rocket/wp-rocket.php',
				'prefixes' => array(
					$prefix . 'wpr_',
				),
			),
		);

		foreach ( $this->db_tables as &$plugin ) {
			$plugin['active']    = false;
			$plugin['installed'] = false;

			$plugins = isset( $plugin['plugin'] ) ? (array) $plugin['plugin'] : array();

			foreach ( $plugins as $p ) {
				if ( in_array( $p, $this->installed['plugins-codes'] ) ) {
					$plugin['installed'] = true;
				}

				if ( WPR::is_plugin_active( $p ) ) {
					$plugin['active'] = true;
				}
			}
		}
	}
}
