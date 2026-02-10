<?php
/**
 * Loads the plugin files
 *
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

// Load basic setup. Plugin list links, text domain, footer links etc. 
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/admin/basic-setup.php';

// Load admin setup. Register menus and settings.
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/admin/admin-setup.php';

// Render Admin UI
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/admin/admin-ui-render.php';

// Metabox in Media Library > Edit Media.
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/admin/meta-box-attachment.php';

// Metabox in Posts and Pages.
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/admin/meta-box-posts-pages.php';

// Bulk Actions in Media Library.
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/admin/bulk-actions-media-library.php';

// Bulk Actions in Pages and Posts.
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/admin/bulk-actions-posts-pages.php';

// Row Actions in Media Library.
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/admin/row-actions-media-library.php';

// Row Actions in Pages and Posts.
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/admin/row-actions-posts-pages.php';

// Load custom attributes
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/functions/custom-attributes.php';

// Load plugin functions.
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/functions/filesystem.php';
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/functions/event-log.php';
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/functions/find-posts-using-attachment.php';
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/functions/do.php';
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/functions/bulk-updater.php';

// Load 3rd-party functions.
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/3rd-party/advanced-custom-fields.php';
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/3rd-party/wordpress-seo.php';
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/3rd-party/seo-by-rank-math.php';
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/3rd-party/wp-seopress.php';
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/3rd-party/woocommerce.php';
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/3rd-party/divi.php';

// Load Action Scheduler
require_once IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/includes/action-scheduler/action-scheduler.php';