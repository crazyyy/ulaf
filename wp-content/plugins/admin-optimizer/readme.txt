=== Admin Optimizer ===
Author URI: https://damienoh.com
Plugin URI: https://www.adminoptimizer.com/
Tags: enhancements, optimizations, all in one plugin, security, disable features
Contributors: yipresser, damienoh
Requires at least: 5.5
Requires PHP: 7.4.0
Tested up to: 6.9
Stable tag: 1.5.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

SMTP Email, Two-factor Authentication, Custom Post Status, SVG File upload, Custom Login URL, Limit Login Attempts, Lock Modified Date, Database cleaner & more!

== Description ==

Admin Optimizer helps to boost and enhance your WordPress site, adding additional features to improve various admin workflows and site aspects while **replacing multiple plugins** doing it.

== Modules: ==

[See all modules >>](https://www.adminoptimizer.com/modules/)

== Content Management ==
- **Auto-Publish Posts with Missed Schedule**: auto publish posts that have missed their publication schedule.
- **Custom Post Status**: register new and custom post status for use with your posts. [Pro](https://www.adminoptimizer.com/) adds options to assign custom post status to custom post types and set up custom post status for different user roles.
- **Custom Post Types**: register new and custom post types for various content.
- **Custom Taxonomies**: register new and custom taxonomies to support all Post Types.
- **Lock Modified Date**: prevent the Post's modified date from being updated unnecessarily. [Pro](https://www.adminoptimizer.com/) adds options to enable modified date lock for custom post types, and grant permission to user roles to change the modified date.
- **Auto add anchor target to Headings block**: Transform your WordPress headings into clickable link targets, making it easier for readers to navigate through your posts and share specific sections.
- **Post Cloner**: Easily clone posts and any other post types, including all its post metas and taxonomies.
- **Post Republisher**: Clone a post as a child of the Post. When you (re)publish the cloned post, it will update the orignal post instead of publishing as a new post. This allows you to update old content quickly and easily. [Pro](https://www.adminoptimizer.com/)

== Media Management ==
- **Auto convert image name to Alt text**: Tired of seeing images with empty Alt text? Now you can preset the Alt text section with the image name.
- **Limit Image upload size**: limit the image's file size to prevent your users from uploading a large image file.
- **SVG File Upload**: enable the upload of SVG files. SVG files were sanitized during upload to ensure it is safe to use.

== Security ==
- **Block Failed Login**: Limit failed login attempt and block bad actors from carrying out brute force login attack to your site. [Pro](https://www.adminoptimizer.com/admin-optimizer/) adds options to customize the lock out time, a full lockout mode and hide login form in lockout mode.
- **Custom Login URL**: Hide the wp-login.php login URL and create a custom secret login url for your users.
- **Hide WordPress Version**: remove the WP version tag from the site's header.
- **Hide Update notice**: hide the Update nag to all users except for the user roles with Update capability (administrator).
- **Two-factor Authentication**: enable two-factor authentication (TOTP) for all your users. [Pro](https://www.adminoptimizer.com/) adds options to make Two-factor Authentication compulsory for defined user roles, block users without enabled 2FA from logging in, and allow users to save devices as “Trusted devices”.

== Disable Features ==
- **Disable Oembed link**: remove the "json+oembed" and "xml+oembed" link in the site's header. This prevents other sites from embedding your content in their sites.
- **Disable Emojis**: remove all "emojis" related styles and scripts from the site.
- **Disable jQuery Migrate**: disable loading of jQuery migrate script on the site.
- **Disable REST API**: remove REST API access for non-authenticated users and remove URL traces from the site's header.
- **Disable Really Simple Discovery (RSD)**: remove the Really Simple Discovery (RSD) link in the site's header. The RSD tag is used by XML-RPC clients to discover the location of the XML-RPC endpoint on your site. If you are not using XML-RPC, you should disable this.
- **Disable Shortlink**: remove the shortlink link in the site's header.
- **Disable XML-RPC**: disable XML-RPC protocol from your WordPress site. XML-RPC is insecure by default as it doesn't include built-in security features like encryption or authentication, so it is best to disable it in WordPress.
- **Disable X-Pingback header**: remove the "X-Pingback" info in HTTP header to prevent other sites from pinging back to your site.
- **Disable X-Powered-By header**: remove the "X-Powered-By" info in HTTP header to hide your PHP and server information.
- **Disable Gutenberg Editor**: disable the block editor and restore the Classic editor as the default editor.
- **Disable Category Archive Page**: disable all Category archive pages in the site frontend.
- **Disable Tag Archive Page**: disable all Tag archive pages in the site frontend.
- **Disable Author Archive Page**: disable all Author archive pages in the site frontend.
- **Disable Date Archive Page**: disable all Date archive pages in the site frontend.

== Utilities ==
- **Adjust Heartbeat**: Modify the Heartbeat interval to improve the user experience of WordPress.
- **SMTP Email**: replace the default mailer with external SMTP service to ensure successful mails delivery.
- **Database Cleaner**: Schedule regular optimization and cleaning up of the WP database to improve the performance of the site. [Pro](https://www.adminoptimizer.com/) adds options to manually cleanup individual DB table, tracks unused options and remove autoload for Options table.

== Site Management ==
- **Manage robots.txt, ads.txt and app-ads.txt**: Provide an interface for you to easily add and update the robots.txt, ads.txt and app-ads.txt files, directly from your WordPress directly. There is no need to manually upload/download physical text file anymore.
- **XML Sitemap**: Configure and manage the native WP XML sitemap for search engines.

== User Management ==
- **Track User Last Login**: track the last logged in date of every user and display it in the Users column.
- **Track User Registration Date**: track the Registration date of every user and display it in the Users column.
- **Disable new user signup notification**: disable the new user notification when creating a new user account in WordPress.
- **Hide admin toolbar for all users**: disable the admin bar for all logged-in users when viewing the site on the frontend.
- **Disable User Account**: Disable user accounts for inactive users and prevent them from logging in.

[See all modules >>](https://www.adminoptimizer.com/modules/)

Your feedback is WELCOME!

== Screenshots ==

1. Content Management Settings
2. Media Management Settings
3. Security Settings
4. Utilities Settings
5. Disable Features Settings
6. Users Management Settings
7. Custom Post Status Settings

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'Admin Optimizer'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `admin-optimizer.zip` from your computer
4. Click on 'Install Now' button
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `admin-optimizer.zip`
2. Extract the `admin-optimizer` directory to your computer
3. Upload the `admin-optimizer` folder to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Changelog ==
= 1.5.3 =
Fixed: Custom Post Types module not showing correct options when editing existing post types
Fixed: Custom Taxonomies module not showing correct options when editing existing taxonomies

= 1.5.2 =
Added: Post Cloner after cloning action

= 1.5.1 =
Fixed: XML Sitemap URL wrongly set on robots.txt

= 1.5.0 =
Added: new XML Sitemap module

= 1.4.2 =
* The plugin now require PHP version 7.4.0
* Added polyfills for PHP 8 and 8.1 functions
* Fixed: 2FA module now works on PHP7.4
* Bug: make sure the `Envelope-From`, `Return-Path` is properly set via `$phpmailer->Sender` and `$phpmailer->ReturnPath`.

= 1.4.1 =
* Updated: plugin site URL

= 1.4.0 =
* Added: new Post Cloner module
* Added: new Robots.txt, ads.txt and app-ads.txt module
* Added: removed scheduled tasks on module deactivation

= 1.3.0 =
* Added: new Export/Import module.
* Added: auto add anchor target to Headings block
* Added: Disable Gutenberg editor
* Added: Disable Category archive pages
* Added: Disable Tag archive pages
* Added: Disable Author archive pages
* Added: Disable Date archive pages
* Bug: correct a sentence error in database cleaner module

= 1.2.0 -  =
* Added: new Database Cleaner module
* Enhancement: updated admin UI interface
* Bug: correct modified date column text

= 1.1.0 - 27 August 2025 =
* Added: new Disable User Accounts Module.
* Added: new Disable Guess Redirect 404 Module.
* Enhancement: improve the modules page.

= 1.0.4 - 20 August 2025 =
* Enhancement: remove access to xml-rpc class and file.
* Change: Moved Disable XML-RPC module to Security section.
* Bug: Correct taxonomy spelling mistake.

= 1.0.3 - 19 August 2025 =
* Bug fixes: fixed 2fa conflicts with last login date.

= 1.0.2 - 15 August 2025 =
* Minor bug fixes: fixed spelling error

= 1.0.1 - 13 August 2025 =
* Minor bug fixes, security compliant fixes and first release to WP Plugin Directory

= 1.0.0 - 25 June 2025 =
* First release

== Frequently Asked Questions ==

= What is the Admin Optimizer plugin? =

Admin Optimizer is a comprehensive all-in-one plugin that replaces multiple plugins with multiple features and utilities to improve and enhance the WordPress core. It offers modular features that can be easily on/off with a click.

= Can I disable certain features of Admin Optimizer if I don't need them? =

Absolutely. Admin Optimizer comes with various modules, all of which can be activated individually. You only activate the module you need.

= Will Admin Optimizer slow down my site? =

No. Admin Optimizer is designed to be lightweight and efficient. Each and every module was carefully coded to use only WordPress's API and Hooks. There are no unnecessary codes and scripts to slow down the site. In fact, the Admin Optimizer replaces multiple plugins and may even improve the loading time of your site.

= Does Admin Optimizer works with multisite? =

Admin Optimizer has not been tested on multisite and does not officially support multisite. Please use at your own risk.