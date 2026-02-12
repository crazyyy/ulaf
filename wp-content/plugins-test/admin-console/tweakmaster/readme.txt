=== TweakMaster ===
Contributors: nico23
Tags: performance, privacy, security, tweaks, lightweight
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Donate link: https://nextgenthemes.com/donate

A collection of performance, privacy, security, and other tweaks. Minimalistic lightweight plugin.

== Description ==
WP Tweak is a lightweight and minimalistic WordPress plugin designed to enhance your website with a curated set of tweaks. It focuses on improving performance, bolstering privacy, strengthening security, and adding other useful optimizations—all without unnecessary bloat. Perfect for users who want a simple yet effective solution to fine-tune their WordPress experience.

This plugins handles tweaks in a minimalistic way. Only files that contains tweaks that you activate are loaded!

= Tweaks =

* **Remove version query strings**<br>
Removes <code>?ver=1.2.3</code> from all styles and scripts.
* **Disable feeds**
* **Disable emojis**
* **Search single result redirect**
* **Disallow File Edits**<br>
Disables the ability to edit files in the file manager. Sets <code>DISALLOW_FILE_EDIT</code> constant to true. Does only work if the constant is already defined, usually in wp-config.php
* **Admin bar greeting**<br>
Replace "Howdy, {name}" with a custom message. Use <code>{name}</code> for the user's display name. For example <code>Hi, {name}!</code>. Leave empty for no greeting. Use <code>default</code> for the default greeting, preventing the tweak from running.
* **Enable fonts to uploads**<br>
Move (Google) Fonts enabled in the Block Editor from wp-content/fonts to wp-content/uploads
* **Disable auto trash emptying**
* **Set trash keep days**<br>
Set the number of days to keep posts in the trash. Default is 30 days.
* **Scroll progress bar**
* **Scroll progress color**<br>
And valid css will work. You can use <a href="https://oklch.com" target="_blank">this color picker!</a>.
* **Scroll progress bar background color**
* **Scroll progress height**
* **Disable self SSL verify**
* **Disable Comments**
* **Disable Email Login**
* **Disable REST API**
* **Disable Success Update Emails**
* **Remove Admin Bar WordPress Logo**<br>
Requires a hard refresh of the page to take effect.
* **Remove Asset Attributes**
* **Disable Non Production Emails**<br>
If WP_ENV (Trellis) or wp_get_environment_type is not production, emails sending is mocked.
* **Remove REST API links**
* **Admin email check interval**<br>
Time in seconds to display email verification message on login. Set to 0 to disable. Default is 15552000 (6 months).
* **Admin footer text**<br>
a href links are allowed. Use "default" for default text.
* **Set user agent**<br>
WP really hates privacy and sends this <code>WordPress/1.0; https://example.org</code> to every site it makes calls to. You can empty the field, pretend to be Chrome <code>Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36</code> or something else. <code>default</code> will change nothing.
* **Remove EXIF**<br>
Remove EXIF data from uploaded images.
* **Convert jpeg to avif**<br>
Convert uploaded jpeg to avif
* **Clean upload filenames**<br>
Sanitize media filenames to remove non-latin special characters and accents
* **Avif compression**<br>
Default is 82
* **Jpeg compression**<br>
Default is 82
* **Webp compression**<br>
Default is 86
* **Limit ALL revisions**<br>
Limit revisions for all post types. This will override the limit for each post type from above!
* **Disable XML-RPC**
* **Disable XML-RPC - allow Jetpack IPs**<br>
Allow XML-RPC only from Jetpack IPs
* **Remove WP version**<br>
Remove <code>WordPress/6.7.1; https://example.org</code> from html head
* **Dequeue jQuery Migrate**<br>
Dequeue jQuery Migrate from the jQuery script dependencies on the frontend. This is used to help devs debug from old versions of jQuery. You really do not need this on a production site.
* **Script Optimizer**<br>
Optimize script loading by moving them into the <code>head</code> and adding <code>defer</code> attribute. This may break your site. Use at your own risk!
* **Enable relative URLs**<br>
Enable relative URLs on the frontend. This may break your site. Use at your own risk!
* **Disable Contact Form 7 CSS**<br>
Sets <code>wpcf7_load_css</code> filter to <code>false</code>
* **Disable Contact Form 7 Autop**<br>
Sets <code>wpcf7_autop_or_not</code> filter to <code>false</code>
* **Enable Jetpack offline mode**
* **Enable maintenance mode**
* **Enable duplicate post**


= Contribute to add quality tweaks to the plugin reviewed by me =

Unlike WP Code's snippet database that is filled with low code quality tweaks where some do not even work at all and spam this plugin only contains high quality tweaks. If something is missing please add it.

It is very easy to contribute to the plugin. Check out the [Github Readme](https://github.com/nextgenthemes/tweakmaster/blob/master/readme.md).

If you have questions on adding a more complex tweak please feel free to ask.

== Screenshots ==
(Maybe later)

== Changelog ==

= 2025-09-17 - 1.1.0
* New: Admin email check interval setting.
* New: Admin footer text setting.

= 2025-08-03 - 1.0.6
* Improved: sanitize, validate correctly, fix prefixes ...
* Removed update disable tweak, not allowed on wp.org?

= 2025-03-xx - 1.0.1-beta2 =
* Improved: Tweak descriptions.
* Fix: Admin Bar Greeting tweak.
* Fix: Link to GH readme.
* Fix: Rename `wptweak` to `tweakmaster` to fix multiple issues.

= 2025-03-15 - 1.0.1-beta1 =
* New: Disable non production email tweak.

= 2025-03-xx - 1.0.0-beta1 =
* Initial release.
