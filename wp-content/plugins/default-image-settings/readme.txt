=== Plugin Name ===
Contributors: engelen
Tags: image,media,insert post,default image settings,default,settings,insert,document
Requires at least: 3.5
Tested up to: 4.7
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Change default settings for image size, link to and align for images inserted into posts. Allows you to remove the link on images by default.

== Description ==

> Please note that the functionality of this plugin is limited as of WordPress 4.4. As of WordPress 4.4, the user settings have prevalence over the default image settings specified using this plugin. Please refer to Trac ticket [#31467](https://core.trac.wordpress.org/ticket/31467) for more information about this change.

Take control over your images inserted into posts! Choose to what page images inserted into posts link by default. Change the default size of inserted images. Default Image Settings adds options in the Media section of your settings for choosing the default settings for inserted images.

Providing a simple UI, this plugin allows you to choose the default selected size, align and Link To of images when inserting media into posts. **The settings for changing the default settings are added to the `Media` menu under `Settings` in your Admin Panel.**

== Installation ==

For automatic installation, all you have to do is install and activate the plugin from the Plugins screen in your WordPress admin panel!

For manual installation, please download the plugin and follow these steps:

1. Upload the folder `default-image-settings` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Additional settings for changing the image defaults are displayed in the `Settings -> Media` admin screen

== Changelog ==

= 1.0.2 =
* Add notice to Readme about WordPress core functionality from 4.4 onward

= 1.0.1 =
* Minor data escaping changes (props @ivankk (https://profiles.wordpress.org/ivankk/))

= 1.0 =
* Initial release