<?php
/**
 * Functions for updating data, used by the background updater.
 */

defined( 'ABSPATH' ) || exit;

use Kaizencoders\Utilitify\Install;

/* --------------------- 1.0.1 (Start)--------------------------- */
function kc_uf_update_101_create_table() {
	Install::create_tables('1.0.1');

	\Kaizencoders\Utilitify\Option::set('installed_on', time());
}
/* --------------------- 1.0.1 (End)--------------------------- */

