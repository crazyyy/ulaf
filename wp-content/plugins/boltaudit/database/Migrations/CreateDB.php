<?php

namespace BoltAudit\Database\Migrations;

defined( 'ABSPATH' ) || exit;

use BoltAudit\WpMVC\Contracts\Migration;

class CreateDB implements Migration {
	public function more_than_version() {
		return '0.0.3';
	}

	public function execute(): bool {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = "{$wpdb->prefix}boltaudit_options";

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$sql = "CREATE TABLE {$table_name} (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `option_key` VARCHAR(191) NOT NULL,
        `context` VARCHAR(191) NOT NULL DEFAULT '',
        `data` JSON DEFAULT NULL,
        `created` DATE NOT NULL,
        `expire` DATE DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `key_idx` (`option_key`)
        ) {$charset_collate};";

		dbDelta( $sql );

		return true;
	}
}