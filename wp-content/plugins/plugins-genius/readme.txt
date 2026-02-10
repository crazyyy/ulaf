=== Plugins Genius ===
Contributors: marcocanestrari
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WGVV5FF7AKRBQ
Tags: plugin, plugins, role, roles, wordpress, admin, frontend, backend, speed
Requires at least: 3.0.0
Tested up to: 4.6
Stable tag: 2.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Role-based active plugins manager. Choose which plugins should be active for each WordPress role in the backend and in the frontend.

== Description ==

Plugins Genius allows you to choose which plugins should be active in the frontend and in the backend.
Some plugins affects only the frontend, some only the backend of Wordpress and some of them make Wordpress slow.

Now you can tune your Wordpress site, actvating plugins only where you need.

* Author: [Marco Canestrari](http://www.marcocanestrari.it/)

== Installation ==

1. Upload the files to the /wp-content/plugins/plugins-genius/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to "Plugins Genius" tab and select which plugin should be active for each role

== Screenshots ==

1. When Plugins Genius is active, standard plugins page is like that: all plugins inactive except Plugins Genius.
2. You can select active plugin from Plugins Genius configuration page.

== Changelog ==

= 2.1.1 =
* Fixed a bug that caused frontend ajax action for anonymous users not to work

= 2.1.0 =
* Hooked get_option(‘active_plugins’) to return role-based active plugins
* Role-based active plugins displayed on classic plugins page

= 2.0.0 =
* Plugin Genius code is now Object Oriented
* Activaction hooks are now fired when a new plugin is activated within Plugins Genius
* Fixed a bug that caused some plugins not beeing correctly loaded

= 1.0.0 =
* Added nonce security
* Tested on Wordpress 3.5.2

= 0.9.7 =
* Fixed bug causing fatal error on wp_get_current_user() function

= 0.9.6 =
* Security fix

= 0.9.5 =
* First release

== Upgrade Notice ==

= 2.1.1 =
Bug fixing

= 2.1.0 =
Bug fixing

= 2.0.0 =
This is a major release: Object Oriented plugin, activation hooks fired, and bug fixes

= 1.0.0 =
Added nonce security

= 0.9.7 =
This version fixes a bug causing fatal error on wp_get_current_user() function

= 0.9.6 =
This version fixes a security related bug. Please upgrade immediately.

= 0.9.5 =
First stable release. Speed up your WordPress site