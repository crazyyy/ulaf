<?php
/*
Plugin Name: Code Engine
Plugin URI: https://meowapps.com/code-engine
Description: Versatile plugin that not only manages PHP code snippets but also acts as a powerful bridge connecting WordPress, AI, and external digital platforms.
Version: 0.4.2
Author: Jordy Meow
Author URI: https://meowapps.com
Text Domain: code-engine

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html
*/

define( 'MWCODE_VERSION', '0.4.2' );
define( 'MWCODE_PREFIX', 'mwcode' );
define( 'MWCODE_DOMAIN', 'code-engine' );
define( 'MWCODE_ENTRY', __FILE__ );
define( 'MWCODE_PATH', dirname( __FILE__ ) );
define( 'MWCODE_URL', plugin_dir_url( __FILE__ ) );
define( 'MWCODE_ITEM_ID', 23612190 );

/**
 * Define the database schema for Code Engine.
 * 
 * ⚠️ Don't forget to update the version number when you update the schema.
 *    (mwcode_db_snippet_version)
 */
define( 'MWCODE_SNIPPET_COLUMNS', [
    'id' => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
    'name' => 'TINYTEXT NOT NULL',
    'description' => 'TEXT NOT NULL',
    'code' => 'LONGTEXT NOT NULL',
    'tags' => 'LONGTEXT NOT NULL',
    'scope' => "VARCHAR(255) NOT NULL DEFAULT ''",
    'priority' => "SMALLINT(6) NOT NULL DEFAULT 10",
    'active' => "TINYINT(1) NOT NULL DEFAULT 0",
    'endpoint' => "VARCHAR(255) NOT NULL DEFAULT ''",
    'token' => "VARCHAR(255) NOT NULL DEFAULT ''",
    'method' => "VARCHAR(15) NOT NULL DEFAULT 'POST'",
    'created' => 'DATETIME NOT NULL',
    'updated' => 'DATETIME NOT NULL',
]);


require_once( 'classes/init.php' );
require_once( 'classes/load.php' );

?>
