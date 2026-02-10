<?php
/**
 * Plugin Name: KolorWeb Log Manager: cleaver debugging management
 * Plugin URI:  https://kolorweb.it
 * Description: WordPress Debug log Manager
 * Version:     1.1.6
 * Author:      Vincenzo Casu
 * Author URI:  https://kolorweb.it
 * Text Domain: kolorweb-log-manager
 * Domain Path: /languages
 * License:     Gpl-3.0 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * KolorWeb Log Manager is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * KolorWeb Log Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with KolorWeb Log Manager. If not, see https://www.gnu.org/licenses/gpl-3.0.txt.
 *
 * @package   kolorweb-log-manager
 * @author    Vincenzo Casu <vincenzo.casu@gmail.com>
 * @link      https://kolorweb.it
 * @copyright KolorWeb Log Manager is free software: you can redistribute it and/or modify
 * @wordpress-plugin
 */

if ( ! defined( 'WPINC' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}


/**
 * Define Constants.
 */
define( 'KWLOGMANAGER_VERSION', '1.1.6' );
define( 'KWLOGMANAGER_BASE', __DIR__ . '/' );
define( 'KWLOGMANAGER_URL', plugin_dir_url( __FILE__ ) );


// Register autoloader.
require_once KWLOGMANAGER_BASE . 'autoload.php';

use KolorWeb\KWLogManager\Plugin;

// Load plugin.
$kw_log_manager = Plugin::get_instance();
