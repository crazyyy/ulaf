=== REST API Manager For ACF ===
Contributors: bayzid416
Tags: acf, rest api, post meta, api, custom endpoint
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: rest-api-manager-for-acf

Custom REST API endpoint plugin to return ACF fields, post meta (selected keys), or a mixed object. Fully configurable from the admin settings page.

== Description ==

REST API Manager For ACF allows you to create a flexible REST API endpoint for your WordPress site. You can return:

* Only ACF fields
* Only selected Post Meta
* Mixed data (ACF fields + Post Meta + Post Info)

It comes with a settings page where you can configure:

* API Base URL
* Data type to return
* Select which meta keys to include

This plugin ensures safe access to data by checking user capabilities for sensitive endpoints.

== Installation ==

1. Upload the `rest-api-manager-for-acf` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Settings → REST API Manager For ACF** to configure your endpoint.
4. Use the endpoint in your application: `/wp-json/ramacf/v1/page/{id}`

== Frequently Asked Questions ==

= Can I return only selected meta keys? =
Yes, you can select which post meta keys to include in the settings page. Leave empty to return all post meta.

= Do I need ACF installed? =
No, the plugin will still work. If ACF is installed, ACF fields will be returned when selected.

== Screenshots ==

1. **Settings Page** — Configure your endpoint base URL, data type, and meta key selection.
2. **REST API Response Example** — JSON output showing mixed ACF and meta data.
3. **Field Selector Interface** — Select which meta keys or ACF fields to include in your API response.

== Changelog ==

= 1.0.1 =
* Renamed plugin to REST API Manager For ACF
* Updated plugin slug and text domain for WordPress.org compliance
* Added secure REST API permission callbacks
* Updated admin menu labels and internationalization

= 1.0.0 =
* Initial release

== Upgrade Notice ==

Upgrade to 1.0.1 to ensure WordPress.org compliance with plugin name, slug, and secure REST API permissions.
