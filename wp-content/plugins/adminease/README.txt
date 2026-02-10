=== AdminEase ===
Contributors: precisionwp
Tags: admin, security, performance, updates, maintenance
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.5.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: adminease
Domain Path: /languages

Boosts your WordPress admin with tools for updates, security, performance, and user management - no coding required.

== Description ==

**AdminEase** is an all-in-one WordPress admin toolkit that helps you manage updates, strengthen security, improve performance and streamline site maintenance without touching code. AdminEase makes everyday site management faster, safer, and easier from a clean, user friendly dashboard.

= Key Benefits =

✓ **No Coding Required** - User-friendly interface for all skill levels
✓ **Enhanced Security** - Protect your site from common vulnerabilities
✓ **Better Performance** - Optimize WordPress for speed and efficiency
✓ **Complete Control** - Toggle features on/off anytime without risk
✓ **Time Saver** - Manage dozens of settings from one dashboard

== Features ==

= Updates and Notifications =
- **Auto Update Core**: Enable/disable automatic WordPress core updates.
- **Auto Update Plugins**: Enable/disable automatic plugin updates.
- **Auto Update Themes**: Enable/disable automatic theme updates.
- **Auto Update Translations**: Enable/disable automatic translation updates.
- **Update Notification Emails**: Receive emails when core, themes, or plugins are auto-updated.
- **New User Admin Notification Email**: Notify admin on new user registration.
- **User Password Change Admin Notification Email**: Notify admin when a user changes their password.
- **Comment Admin Notification Email**: Notify admin on new comments.
- **Disable Admin Notices**: Hide admin notices from plugins and themes.
- **Disable recovery mode email notification**: Prevent WordPress from sending recovery mode emails.
- **Customize recovery mode recipient email**: Set a custom email address to receive recovery mode notifications.


= Security =
- **Disable File Edit**: Prevent editing theme/plugin files from the admin panel.
- **Disable File Mods**: Prevent all file modifications in WordPress, including installing, updating, and deleting plugins and themes.
- **Disable XML-RPC**: Block XML-RPC access to reduce attack surface, with IP allowlist (in Pro).
- **Disable REST API**: Block REST API access, with IP allowlist (in Pro) with the option to allow all HTTP requests to wordpress.org, including plugin/theme updates, version checks, and other API communications.
- **Disable Embeds**: Stop automatic embedding of external content.
- **Hide WordPress Version**: Remove version info from source code and feeds.
- **Password Protect Site**: Protect your entire website with a password, featuring customizable themes, role-based bypasses, IP whitelisting, device remembering, and auto-disable scheduling.
- **Block Access to .htaccess/.htpasswd**: Protect sensitive server files.
- **Disable Pingbacks**: Prevent XML-RPC pingback abuse.
- **Block Author Scans**: Prevent username enumeration.
- **Block Directory Browsing**: Stop public directory listing.
- **Disable Script Concatenation(Admin)**: Prevent certain DoS attacks in admin.
- **Block Specific Countries (GeoIP)**: Restrict site access by country.
- **Block Specific Bots**: Control which bots can access your site
- **CORS Headers**: Enable Cross-Origin Resource Sharing to allow trusted websites to access your site's resources safely.
- **Hide PHP Version**: Remove X-Powered-By header to hide PHP version information from headers.

= Performance =
- **WP Memory Limit**: Set the memory limit for WordPress.
- **WP Max Memory Limit**: Set the maximum memory for admin tasks.
- **Max Execution Time**: Set PHP script timeout.
- **Autosave Interval**: Customize how often WordPress auto saves your work.
- **Disable WP Cron**: Replace WP-Cron with a real server cron job for reliability.
- **Disable Emojis**: Disable loading of emoji scripts and styles.

= Posts =
- **Empty Trash Days**: Set the number of days to keep deleted posts in trash.
- **Post Revisions**: Limit or disable post revisions to save database space.
- **Disable Gutenberg Editor**: Disable the block editor and revert to the classic editor.
- **Drag and Drop Ordering Posts**: Rearrange posts via drag-and-drop.
- **Disable Comments**: Disable comments site-wide with a single toggle.
- **Bulk Delete Posts**: Delete posts in bulk from the admin panel.
- **Posts Metadata Box**: Adds a meta box to posts for easy management of custom fields.

= Taxonomies =
- **Drag and Drop Ordering Taxonomies**: Rearrange taxonomies via drag-and-drop.
- **Improved Taxonomy Meta Box**: Enable lazy loading for taxonomy terms to improve performance.

= Users =
- **Disable User Registration**: Block all new user registrations.
- **Force Strong Passwords**: Enforce password complexity rules (length, case, numbers, symbols).
- **Auto-Logout User**: Auto-logout users after inactivity (customizable per role and time).
- **Hide Admin Bar**: Hide the admin bar for specific users.
- **Redirect After Login/Logout**: Customize where users go after logging in or out

= Debug =
- **WP Debug**: Enable WordPress debug mode.
- **WP Debug Log**: Log errors to a file.
- **WP Debug Display**: Show/hide errors on the site.
- **Debug Log Viewer**: View the debug log directly from the admin panel.
- **Maintenance Mode**: Prevent all users from accessing the site but not the admin area.
- **Network Viewer**: Enable the Network Viewer to log all incoming HTTP connections to your site in real-time. This feature helps you monitor traffic, detect suspicious activity, and understand how visitors interact with your site.

= Media =
- **Upload Max File Size**: Set maximum upload file size.
- **Allow SVG Uploads**: Enable SVG file uploads with security checks.
- **Allow Custom File Extension Uploads**: Enable additional file types beyond WordPress defaults
- **Media Infinite Scrolling**: Enable infinite scrolling for media library.

= Automatic Installation =
1. Log in to your WordPress admin panel
2. Go to Plugins > Add New
3. Search for "AdminEase"
4. Click "Install Now" and then "Activate"
5. Navigate to the AdminEase menu to configure your settings

= Manual Installation =
1. Download the plugin zip file
2. Log in to your WordPress admin panel
3. Go to Plugins > Add New > Upload Plugin
4. Choose the downloaded zip file and click "Install Now"
5. Activate the plugin
6. Navigate to the AdminEase menu to configure your settings

= After Activation =
1. Go to the AdminEase menu in your WordPress admin
2. Explore the organized tabs (Updates, Security, Performance, etc.)
3. Enable the features you need
4. Save your settings

**Important:** We recommend testing on a staging site first and always maintain backups before making configuration changes.

== Important Disclaimer ==

This plugin modifies core WordPress functionality and behaviors. While we strive to maintain compatibility and stability:

* The plugin's features may work differently or not at all depending on your server configuration, hosting environment, and installed plugins
* Some features may be restricted on certain hosting providers or server setups
* We strongly recommend testing the plugin in a staging environment before using it on a production site
* Always maintain regular backups of your website before making any changes

== Server Compatibility Notice ==

Different server configurations may affect the plugin's functionality:

* Shared hosting environments might have restrictions that prevent certain features from working
* Some features may require specific PHP extensions or server modules
* Server-level email configurations might affect notification control features
* .htaccess modifications require Apache or compatible web servers

If you experience any issues, please check with your hosting provider about server limitations.

== Frequently Asked Questions ==

= Do I need to know how to code to use AdminEase? =
No coding knowledge is required. All features are accessible via an intuitive dashboard interface. Toggle features on or off with a click.

= Will AdminEase slow down my site? =
No, AdminEase is designed with performance in mind. It's lightweight and you can enable only the features you need. Many features actually help improve your site's performance.

= Can I undo changes made by AdminEase? =
Yes, you can toggle features on or off at any time. The plugin safely manages configuration changes and maintains backups of critical files.

= Is AdminEase compatible with other plugins? =
AdminEase is designed to work with most WordPress plugins and themes. However, since it modifies core WordPress behavior, conflicts may occasionally occur. We recommend testing on a staging site first.

= What happens if I deactivate or delete the plugin? =
When you deactivate AdminEase, it safely restores your WordPress configuration to its previous state. All changes are reversed automatically.

= Does AdminEase work on WordPress Multisite? =
Basic compatibility is available, but some features may not work as expected on Multisite installations. Full Multisite support is planned for future releases.

= Where can I get support? =
For support, please visit our [support forum](https://wordpress.org/support/plugin/adminease/). We monitor the forums regularly and are happy to help.

= Is there a Pro version? =
- [Pro Version](https://precisionwp.net/product/adminease/) - **AdminEase Pro** unlocks advanced controls and premium features for deeper security and more powerful WordPress admin management, all from the same simple dashboard.
We're working on advanced features for power users. Stay tuned for announcements!

**Support Resources:**
- [Support Forum](https://wordpress.org/support/plugin/adminease/) - Get help from our team, report bugs or request features in our support forum

== Screenshots ==

1. Updates and Notifications
2. Security
3. Performance
4. Posts
5. Taxonomies
6. Users
7. Debug
8. Media

== Changelog ==

= 1.5.3 =
- Fixed: PostsMetadataBox not working properly on new posts.
- Fixed: Password Protection Access Log showing even when the feature is off.

= 1.5.2 =
- Fixed: Posts meta box loading issue.

= 1.5.1 =
- Fixed: Posts meta box loading issue.
- Fixed: Issue with MaxExecutionTime empty causing settings not saving.
- Improved: FileHandler class.
- Updated: Translations template.

= 1.5.0 =
- Added: Posts meta data box feature.
- Fixed: Debug log download button not showing.
- Fixed: Max execution time not working properly.
- Improved: Better support for Pro plugin.
- Improved: Add an eye icon to the password protect site form to show/hide the password.
- Improved: Better post types selection handler.
- Improved: Added Password Protection Access Log.
- Updated: Translations template.

= 1.4.2 =
- Updated: NetworkViewerLog template file.
- Fixed: MaxExecutionTime causing issues on some servers.

= 1.4.1 =
- Added: Added plugin version number to the dashboard header.
- Added: Download debug.log file in Debug Log Viewer.
- Added: Auto load network viewer setting.
- Updated: Translate template.

= 1.4.0 =
- Fixed: Wrong label for Disable comment post author notification email.
- Fixed: Better control for REST API, it was causing issues with page builders.
- Improved: Plugin UI.
- Improved: Disable Rest API feature with better control.
- Improved: Network Viewer Log browser detection and added country flags.
- Updated: Translations template.
- Added: Disable Emojis: Disable loading of emoji scripts and styles.
- Added: Bulk Delete Posts: Easily delete posts in bulk from the admin panel.
- Added: Disable Comments: Disable comments site-wide with a single toggle.
- Added: Disable Admin Notices: Hide admin notices from plugins and themes.
- Added: Disable recovery mode email notification: Prevent WordPress from sending recovery mode emails.
- Added: Customize recovery mode recipient email: Set a custom email address to receive recovery mode notifications.

= 1.3.5 =
- Fixed: Bug with Drag and Drop Ordering for Taxonomy terms.

= 1.3.4 =
- Improved: Displaying errors below the field, improving user experience.
- Improved: Better support for Pro plugin.
- Updated: Translations template.

= 1.3.3 =
- Fixed: PasswordProtectSite was not working properly in edge cases.
- Fixed: MaintenanceMode was not working properly in edge cases.
- Tweak: Improved Maintenance Mode feature with more customizations.
- Updated: Translations template.

= 1.3.2 =
- Tweak: Disabled autoloading of debug log and network viewer log to improve performance.
- Updated: Translations template.

= 1.3.1 =
- Fixed: Network Viewer Log had issues with users with multiple roles and excluded ips not being excluded properly.

= 1.3.0 =
- Added: Redirect after login / logout.
- Tweak: Improved Network Viewer Log with filter for Ajax, Cron and Rest API requests.
- Tweak: Better escaping in AdminEase.js.
- Tweak: Better control over comment email notifications, now you can select to disable notification to the moderator and the post author.
- Tweak: Removed admin current country from country list to prevent the admin from mistakenly blocking himself.
- Updated: Translations template.
- Fixed: The User-Password-Change Admin Notification Email feature.
- Fixed: Improved function prefixing to avoid conflicts with other plugins.
- Fixed: Disabled caching in Password Protect Site page.
- Fixed: Improved disabling auto updates.
- Fixed: AllowSvgUpload failed on tiny SVG files.
- Fixed: Having to save settings twice to get Block Specific Countries settings to take effect.

= 1.2.0 =
- Added: Allow Custom File Extension Uploads feature to enable additional file types.
- Added: Network viewer.
- Added: Debug log viewer.
- Updated: Translations template.
- Fixed: bug fixes in AllowSvgUploads feature.
- Fixed: Issue with the mobile menu when tabs are initially opened.

= 1.1.2 =
- Added: Maintenance Mode: Prevent all users from accessing the site but not the admin area.
- Updated: Translations template.
- Fixed: Bug with settings of some features.

= 1.1.1 =
- Fixed: Loading of CSS/JS.

= 1.1.0 =
- Added: Disallow File Mods - This setting prevents all file modifications in WordPress, including installing, updating, and deleting plugins and themes.
- Added: Hide PHP Version - Remove X-Powered-By header to hide PHP version information for enhanced security.
- Added: Password Protect Site - Comprehensive website password protection with customizable themes, role-based bypasses, IP whitelisting, device remembering, and scheduled auto-disable functionality.
- Tweak: Reorganized features in tabs.
- Tweak: Loading settings fields in each feature separately.
- Tweak: Changed the way the plugin toggle fields for more control.
- Tweak: Changed naming convention for files.
- Tweak: Merged Updates and Notifications into 1 tab.
- Updated: Dashboard Pro features.
- Updated: Translations template.
- Updated: Styles in AdminEase.css.
- Updated: Rendering date field.
- Updated: Media Infinite Scrolling feature.
- Fixed: is_geoip_enabled returning error.
- Fixed: Bug with disable file edit.
- Fixed: Uninstall error.

= 1.0.2 =
- Fixed: Drag and Drop Ordering for posts and taxonomies.
- Fixed: Removed error log in code.
- Tweak: Taxonomy meta box CSS and JS.

= 1.0.1 =
- Added: Feature - CORS Headers to allow trusted websites to access your site's resources safely.
- Added: Feature - Upload Max File Size to set maximum upload file size.
- Added: Feature - Allow SVG Uploads with security checks.
- Added: Feature - Hide Admin Bar for specific users.
- Added: Feature - Disable gutenberg editor.
- Fixed: Prefixed js files to avoid conflicts with other plugins.
- Fixed: Undefined array key "block_specific_countries".
- Fixed: Bug in ForceStrongPasswords in PHP 7.4.
- Updated: link to support forum in the readme file.
- Updated: FileHandler for better error handling.
- Updated: Translations template.
- Updated: SVGSanitizer package.

= 1.0.0 =
First release of AdminEase – a comprehensive admin toolkit for WordPress.