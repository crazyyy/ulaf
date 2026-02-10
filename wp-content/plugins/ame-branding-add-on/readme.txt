=== AME Branding Add-on ===
Contributors: whiteshadow
Tags: admin, branding, login, color scheme
Requires at least: 4.9
Tested up to: 6.8
Stable tag: 1.3.11

Adds more branding features to Admin Menu Editor Pro.

== Description ==

This add-on adds more branding features to Admin Menu Editor Pro. 

== Changelog ==

= 1.3.11 =
* Fixed a WP 6.8 compatibility issue that could cause a fatal error in the "Colors" tab when saving settings.

= 1.3.10 =
* Fixed the login page background color not being applied. 

= 1.3.9 =
* Fixed an error that prevented color scheme settings from being changed in WP 6.7.

= 1.3.8 =
* Fixed the login logo no longer showing up in WP 6.7.
* Fixed an obscure bug in the "Remove the WordPress logo from the Toolbar" feature where some of the default logo submenus would not be removed when the logo itself was removed. 
* Prevented users from accidentally causing a fatal error by entering an invalid format string in "Custom "Howdy" text".
* Tested up to WP 6.7.

= 1.3.7 =
* Fixed a number of deprecation notices like "Deprecated: trim(): Passing null to parameter #1 ($string) of type string is deprecated in .../login-page.php on line N".
* Users can now preview color schemes as long as they have access to the Admin Customizer, even if they don't have the ability to change AME settings in general.
* Tested up to WP 6.4.1.

= 1.3.6 =
* Fixed a number of PHP 8.2 deprecation notices that were triggered when generating a custom admin color scheme.

= 1.3.5 =
* Fixed an error related to "WordPress update notifications" that could be shown when saving branding settings in Multisite where the settings were previously imported from a non-Multisite site.

= 1.3.4 =
* Fixed existing logo images not showing up in the Toolbar and on the login page.

= 1.3.3 =
* Fixed SVG menu icons becoming invisible when the user sets a custom highlight color in the "Colors" tab but does not choose an icon color.
* Tested up to WP 6.2.

= 1.3.2 =
* Added some of the branding settings to the "Easy Hide" page.
* Fixed a bug where color settings were not immediately applied after importing plugin configuration from a file. The settings would show up in the "Colors" tab, but the custom color scheme wouldn't actually work until you clicked the "Save Changes" button.
* Fixed a conflict with a theme that requires a more recent version of the `scssphp/scssphp` library.

= 1.3.1 =
* Added settings that let you add messages to the top and bottom of the login form. Unlike the "custom message" setting, these new settings display the text inside the login form itself, not above it.
* Made the "footer text" setting override custom footer text added by other plugins.
* Fixed a large number of notices and warnings that were thrown by the Leafo/ScssPhp library when running on PHP 7.4.  
* Fixed a few jQuery deprecation warnings.

= 1.3 =
* Added a "login page title" setting that lets you change the title of the login page.
* Updated some settings page styles to match the admin interface changes introduced in WordPress 5.3.

= 1.2 =
* Added a "set external URL" option to all image settings. This lets you directly enter the image URL instead of selecting an image from the Media Library.
* Fixed this PHP notice: "login_headertitle is deprecated since version 5.2.0! Use login_headertext instead. Usage of the title attribute on the login logo is not recommended for accessibility reasons."
* Fixed the "hide admin footer" option not working with some themes that change #wpfooter styles.
* Fixed a conflict with Hide My WP Pro 3.0 that prevented the "Logo link URL" setting in the "Login" tab from having any effect.
* Tested up to WordPress 5.2.1.

= 1.1 =
* Added import/export support.
* The custom admin color scheme now also applies to the Toolbar (a.k.a. Admin Bar) on the site front end.

= 1.0.1 =
* Fixed a bug where the "Lost your password" form would display a message saying "lostpassword" instead of the standard password recovery message. 
* Tested with WP 4.9.5.

= 1.0 =
* Initial release.