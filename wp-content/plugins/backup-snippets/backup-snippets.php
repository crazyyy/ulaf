<?php

/**
 * Plugin Name
 *
 * @package           Backup_Snippets
 * @author            Fuad Hadi Nugroho
 * @copyright         2022 Fuad Hadi Nugroho
 * @license           GPL-3.0-or-later
 *

 * @wordpress-plugin
 * Plugin Name:       Backup Snippets
 * Plugin URI:        -
 * Description:       Backup Snippet is a safe way to create a copy of your code in Code Snippet. Don't lose your code, even if it's just one line.
 * Version:           1.1.0
 * Requires at least: 6.1.1
 * Requires PHP:      8.1.9
 * Author:            Fuad Hadi Nugroho
 * Author URI:        https://www.instagram.com/fuad.hd/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       backup-snippets
 * Domain Path:       /languages
 * Network:           false
 */

 /*
Backup Snippets is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Backup Snippets is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Backup Snippets. If not, see https://www.gnu.org/licenses/gpl-3.0.txt.
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Constant
defined('BACKUP_SNIPPETS_PLUGIN_DIR') or define('BACKUP_SNIPPETS_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
defined('BACKUP_SNIPPETS_PLUGIN_URL') or define('BACKUP_SNIPPETS_PLUGIN_URL', plugin_dir_url( __FILE__ ));
defined('BACKUP_SNIPPETS_MAIN_TABLE') or define('BACKUP_SNIPPETS_MAIN_TABLE', 'backup_snippets');
defined('BACKUP_SNIPPETS_LIMIT_HISTORY_SNIPPETS') or define('BACKUP_SNIPPETS_LIMIT_HISTORY_SNIPPETS', 5);

// Includes
require_once( BACKUP_SNIPPETS_PLUGIN_DIR . 'includes/backup-snippets-check-requirements.php');
require_once( BACKUP_SNIPPETS_PLUGIN_DIR . 'includes/backup-snippets-admin-notice-functions.php');
require_once( BACKUP_SNIPPETS_PLUGIN_DIR . 'includes/backup-snippets-handling-history-snippets.php');

/**
 * Activate the plugin.
 */
function backup_snippets_activate() {
    global $table_prefix, $wpdb;

    // create main table
    $main_table = $table_prefix . BACKUP_SNIPPETS_MAIN_TABLE;
    $src_table = $table_prefix . "snippets";

    if( $wpdb->get_var( "SHOW TABLES LIKE '$main_table'" ) != $main_table ) {
        $sql = "CREATE TABLE $main_table LIKE $src_table";

        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

        dbDelta( $sql );

        // add column id_snippet for code snippets
        $wpdb->query("ALTER TABLE $main_table ADD id_snippet BIGINT(20) NULL DEFAULT NULL");
    }

    // create trigger after insert
    $sql_trigger = "CREATE TRIGGER bucs_copy_snippet_ai AFTER INSERT ON $src_table FOR EACH ROW INSERT INTO $main_table SET id_snippet = NEW.id, name = NEW.name, description = NEW.description, code = NEW.code, tags = NEW.tags, scope = NEW.scope, priority = NEW.priority, active = NEW.active, modified = NEW.modified";
    mysqli_multi_query($wpdb->dbh, $sql_trigger);

    // create trigger after update
    $sql_trigger = "CREATE TRIGGER bucs_copy_snippet_au AFTER UPDATE ON $src_table FOR EACH ROW INSERT INTO $main_table SET id_snippet = NEW.id, name = NEW.name, description = NEW.description, code = NEW.code, tags = NEW.tags, scope = NEW.scope, priority = NEW.priority, active = NEW.active, modified = NEW.modified";
    mysqli_multi_query($wpdb->dbh, $sql_trigger);

    // create trigger before delete
    $sql_trigger = "CREATE TRIGGER bucs_delete_backup_snippet_bd BEFORE DELETE ON $src_table FOR EACH ROW DELETE FROM $main_table WHERE id_snippet = OLD.id";
    mysqli_multi_query($wpdb->dbh, $sql_trigger);
}
register_activation_hook( __FILE__, 'backup_snippets_activate' );

/**
 * Deactivation hook.
 */
function backup_snippets_deactivate() {
	// action here
}
register_deactivation_hook( __FILE__, 'backup_snippets_deactivate' );

/**
 * Uninstall hook.
 */
function backup_snippets_uninstall() {
	global $table_prefix, $wpdb;

    // delete main table
    $main_table = $table_prefix . BACKUP_SNIPPETS_MAIN_TABLE;

    $wpdb->query( "DROP TABLE IF EXISTS $main_table" );

    // delete triggers
    $sql_trigger = "DROP TRIGGER IF EXISTS bucs_copy_snippet_ai";
    mysqli_multi_query($wpdb->dbh, $sql_trigger);

    $sql_trigger = "DROP TRIGGER IF EXISTS bucs_copy_snippet_au";
    mysqli_multi_query($wpdb->dbh, $sql_trigger);

    $sql_trigger = "DROP TRIGGER IF EXISTS bucs_delete_backup_snippet_bd";
    mysqli_multi_query($wpdb->dbh, $sql_trigger);
}
register_uninstall_hook( __FILE__, 'backup_snippets_uninstall' );