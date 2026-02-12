=== WordPress Toolbar Editor ===
Contributors: whiteshadow
Tags: admin, toolbar, menu, security, wpmu
Requires at least: 3.5
Tested up to: 6.5
Stable tag: 1.5.1

Lets you edit the WordPress Toolbar (a.k.a. Admin Bar).

== Description ==

This add-on lets you edit the WordPress Toolbar (a.k.a. the Admin Bar). Requires _Admin Menu Editor Pro_. 

*Credits*
Uses some icons from the ["Silk" set by Mark James](http://www.famfamfam.com/lab/icons/silk/).

== Installation ==

1. Install and activate Admin Menu Editor Pro if you haven't done it already.
2. Upload and activate the add-on like a normal WordPress plugin.
3. Go to "Settings -> Toolbar Editor" and start customizing the Toolbar. 
4. (Optional) Click the small "Settings" button on the "Toolbar Editor" page to change access permissions and other add-on settings.

== Changelog ==

= 1.5.1 =
* Fixed a fatal error when saving settings on the "Easy Hide" page in Admin Menu Editor Pro. The error was: "Uncaught TypeError: count(): Argument #1 ($value) must be of type Countable|array, null given". The error only happened if the Toolbar configuration contained any detected front-end items.
* Fixed inability to change Toolbar item visibility through the "Easy Hide" screen. It was possible to un-hide an item that was previously completely hidden, but most other changes - like hiding an item from one specific role - would not be saved.
* Tested up to WP 6.5.3.

= 1.5 =
* Added the ability to customize Toolbar items that only appear on certain pages. For example, this includes items that show up in the site frontend but don't show up in the admin dashboard. When you visit a page that has an item like that (while logged in as an admin), Toolbar Editor will automatically remember the item, and display it the next time you open the editor.
  * In the editor, items that are not present on the current admin page are marked with a special icon that looks like a "-" sign in a circle.
  * If you uninstall a plugin or theme that creates items like that, you'll have to remove the items from Toolbar Editor manually. Toolbar Editor doesn't "know" when the items are supposed to show up, so it isn't able to automatically determine if they're actually gone or just not showing up on some pages.
  
= 1.4.3 =
* Renamed the plugin to "AME Toolbar Editor" to make it more consistent with the other add-on, "AME Branding Add-on".
* Moved the "Toolbar Editor" menu item under the main "Menu Editor Pro" item. This only affects sites that don't already have a custom admin menu configuration.
* Fixed a JavaScript deprecation warning regarding jQuery.isArray().
* Tested with WP 6.4.2.

= 1.4.2 =
* Minimum required PHP version increased to PHP 5.6.
* Toolbar items will now appear in the "Easy Hide" page that's part of Admin Menu Editor Pro.
* Updated the Knockout JS library version to 3.5.1. 
* Tested up to WP 6.1.1.

= 1.4.1 =
* Fixed a WP 5.9 compatibility issue that made it impossible to drag and drop items.

= 1.4 =
* Added a "Copy visibility" button. It copies Toolbar item visibility from one role to another.
* Fixed tooltips not showing up in some WordPress versions.
* Fixed a few jQuery deprecation warnings.
* Tested up to WP 5.6.1.

= 1.3.4 =
* Fixed the "Customize" item not appearing in the top level of the Toolbar. If you have already edited the Toolbar before installing this update, "Customize" won't show up automatically. Instead, you can find it in the "Your Site Name" -> "appearance" group.
* Fixed a bug where the "Delete Cache" item created by WP Super Cache did not show up.
* Tested up to WP 5.5.1.

= 1.3.3 =
* Fixed a warning about get_magic_quotes_gpc() being deprecated in PHP 7.4.

= 1.3.2 =
* Fixed a bug where certain Toolbar items like "Edit with Elementor" could end up in the wrong place after activating the plugin.

= 1.3.1 =
* Fixed some layout issues in RTL environments.
* Changed the position of new and unrecognized Toolbar nodes. Now the add-on will attempt to keep them near their initial location instead of moving them to the end of the Toolbar. 
* Tested up to WP 5.3.

= 1.3 =

* The "new menu visibility" settings from the menu editor now also apply to the Toolbar Editor. You can use this to automatically hide new toolbar items.
* The add-on will now automatically re-select the previously selected role or user after saving toolbar settings.
* Fixed the PHP notice "screen_icon is deprecated since version 3.8.0".
* Tested up to WP 5.0.3.

= 1.2.2 =
* Added the current user and selected users from AME to the role list.
* Fixed a rare issue where the plugin would throw an error if toolbar item title was not a string.

= 1.2.1 =
* Added basic WPML support.

= 1.2 =
* Added a white background to the toolbar item list on the settings page.

= 1.1 =
* Added a way to hide items from specific roles.
* You can now use shortcodes in the "Title", "URL" and "HTML content" fields.
* Tested with WordPress 3.9 and an early alpha version of 4.0.

= 1.0 =
* Initial release.