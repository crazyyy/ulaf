=== WP Logger ===
Contributors: hokku,casepress
Tags: logger,develop,log,debug,data
Donate link: https://www.paypal.me/igortron
Requires at least: 3.5
Tested up to: 6.8
Requires PHP: 7.1
Stable tag: 2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Logging vars and events for fast debug WordPress site.

== Description ==

0. Insert the hook <code>do_action( 'logger', $data );</code> in your code
1. Go to **Tools > WP Logger**

= Additional buttons =
Use the <code>wpdl_add_button( string $name, callable $clb, string $btnClass = '' )</code> function to add a button to the logger page.

* **$name** text for the button;
* **$clb** callback function;
* **$btnClass** class for the button (optional).

Click on the button to execute the function via WP-AJAX. If an Exception or Error occurs, it will be logged as well.

= Hooks: =
* wp_logger_button_panel
* wp_logger_inline_css
* wp_logger_inline_js
* wp_data_logger_print_data

= Available constants: =
* WPDL_DISPLAY_LIMIT

[GitHub](https://github.com/hokoo/logger-u7)

== Installation ==
0. Upload the plugin to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **Plugins** menu in WordPress.
2. Insert the hook `do_action( 'logger', $data );` in your code.
3. Go to **Tools > WP Logger**.

== Screenshots ==
1. Logger page

== Changelog ==
= 2.4 =
* Fix Uncaught Exception: Serialization of 'Closure' is not allowed when creating button with closure callback.

= 2.3 =
* Custom button adding function introduced.
* PHP min version changed to 7.1.

= 2.2.1 =
* Nonce verification for ajax queries.
* PayPal link updated.
* Plugin tags updated.

= 2.2 =
* Clear log vulnerability fixed.

= 2.1 =
* The output of the data got escaped.

= 2.0.2 =
* Suppress DB errors while the data insert

= 2.0.1 =
* bug fixed

= 2.0 =
* Logged data was moved to custom table in DB
* WPDL_DISPLAY_LIMIT
* Hidding/showing rows

= 1.4 =
* Added types of data

= 1.2 =
* Init commit

== Upgrade Notice ==
= 2.0 =
During update will be lost all logged data. Please, make sure this won\'t hurt you