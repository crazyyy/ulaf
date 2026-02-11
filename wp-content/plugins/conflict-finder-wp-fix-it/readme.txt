=== Conflict Finder ===

Contributors: wpfixit  
Donate link: https://www.wpfixit.com/  
Tags: plugin conflict, theme conflict, troubleshooting, wp_debug, debug log, email testing, admin tools  
Requires at least: 4.9  
Tested up to: 6.9  
Stable tag: 7.2
License: GPLv2  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Conflict Finder is a WordPress troubleshooting toolkit that helps diagnose plugin conflicts, theme issues, debugging errors, and email delivery problems by temporarily changing site behavior during testing.

== Description ==

Conflict Finder is a comprehensive troubleshooting plugin designed for WordPress administrators, developers, and support professionals who need to identify the root cause of site issues.

From a single interface, Conflict Finder allows you to:

* Enable and manage WP_DEBUG without manually editing files
* View, download, and clear the WordPress debug log
* Temporarily disable plugins to identify conflicts
* Switch themes to test theme-related issues
* Test WordPress email delivery using `wp_mail()`

**Important:**  
Conflict Finder **does temporarily affect site behavior** while troubleshooting is active. This may include:

* Disabled plugins
* A different active theme
* Debug notices or errors being displayed
* Changes visible to logged-out visitors

For this reason, troubleshooting should be performed during maintenance windows or on staging sites whenever possible.

Conflict Finder automatically tracks your original configuration and allows you to restore plugins, themes, and debugging settings once testing is complete.

== Features ==

* **Troubleshooting Dashboard**
  * Central overview of debugging and conflict states
  * Environment snapshot including WordPress, PHP, memory, and server software

* **WP_DEBUG Tool**
  * Enable or disable WP_DEBUG with a single switch
  * Control error display and logging behavior
  * Load unminified scripts for debugging
  * View, download, or clear `wp-content/debug.log`
  * Safely updates `wp-config.php` as needed

* **Plugin Conflict Tool**
  * Temporarily deactivate all active plugins
  * Save and restore original plugin states
  * Activate plugins one at a time to identify conflicts

* **Theme Conflict Tool**
  * Temporarily switch to another installed theme
  * Identify theme-related layout or functionality issues
  * Restore the original theme instantly

* **Email Delivery Tool**
  * Send a real test email using WordPress mail
  * Confirm whether the server can successfully send email
  * Helps identify SMTP or hosting mail issues

== When to Use Conflict Finder ==

* Diagnosing white screens or fatal errors
* Identifying plugin conflicts
* Testing theme-related layout or functionality issues
* Investigating PHP notices or warnings
* Verifying WordPress email delivery
* Support and development workflows

== Installation ==

1. Upload the plugin ZIP file via **Plugins → Add New → Upload Plugin**, or extract it to `/wp-content/plugins/`.
2. Activate Conflict Finder from the **Plugins** menu.
3. Access the tools under **Tools → Troubleshoot**.

== Frequently Asked Questions ==

**Does this affect visitors?**  
Yes. While troubleshooting is active, plugins may be disabled, themes may be switched, and debug output may be visible on the frontend.

**Is this safe to use on live sites?**  
It can be used on live sites, but it is recommended to troubleshoot during low-traffic periods or on staging environments when possible.

**Does this permanently change my site?**  
No. All changes are temporary and can be restored once troubleshooting is complete.

**Do I need FTP or server access?**  
No. Conflict Finder manages debugging and conflict testing directly from the WordPress admin.

== Screenshots ==

1. Troubleshooting tools overview and environment snapshot  
2. WP_DEBUG configuration and reporting options  
3. Debug log viewer  
4. Plugin conflict isolation tool  
5. Theme conflict isolation tool  
6. Email delivery testing tool  

== Changelog ==

= 7.2 January 27th, 2026 =

* Refined email testing tool to avoid spam filters

= 7.1 January 6th, 2026 =

* Improved email testing tool

= 7.0 January 5th, 2026 =

* Major release with internal refactoring and stability improvements
* Improved admin-only safety enforcement
* Enhanced debug log viewing and clearing
* Improved conflict detection workflows
* UI and accessibility improvements
* Code cleanup for Plugin Check and WordPress coding standards compliance
* Tested and verified with WordPress 6.8

= 6.4 June 2nd, 2025 =

* Update for WordPress 6.8

= 6.3 June 29th, 2024 =

* Fixed iFrame height

= 6.2 June 29th, 2024 =

* Fixed modal console errors

= 6.1 June 29th, 2024 =

* Corrected styling issue on viewport buttons

= 6.0 June 28th, 2024 =

* Added logged-out preview for error testing
* Redesigned responsive error page
* Improved plugin and theme restoration checks

= 5.0 June 19th, 2024 =

* Added error notification emails
* Added pretty error page for non-admin users
* Added reset option for recovery from error states

= 3.0 November 1st, 2023 =

* Initial release