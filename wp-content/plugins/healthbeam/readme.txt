=== HealthBeam ===
Contributors: woosofts
Tags: site health, debug, diagnostics, system info, wordpress tools
Requires at least: 5.6
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced diagnostic and monitoring tools for WordPress with a modern React-based interface.

== Description ==

**HealthBeam** provides a comprehensive suite of diagnostic and monitoring tools to help you maintain, debug, and optimize your WordPress site. Built with a modern React interface, it offers powerful features in an intuitive, user-friendly dashboard.

Need help? Email support@woosofts.com

#### 🔥 Key Features

* **Debug Log Viewer:** View and monitor your WordPress debug log in real-time
* **File Integrity Checker:** Verify WordPress core files against official checksums
* **Mail Tester:** Send test emails to verify your mail configuration
* **Plugin Compatibility:** Check installed plugins for PHP version compatibility
* **Configuration Viewers:** View .htaccess, robots.txt, and PHP configuration
* **Transient Manager:** Monitor and clean up database transients
* **Modern React Interface:** Fast, responsive, and intuitive user experience
* **REST API Powered:** Efficient data handling with WordPress REST API

#### 🎯 Perfect For

* **Developers** – Debug and troubleshoot WordPress issues efficiently
* **Site Administrators** – Monitor site health and system configuration
* **Support Teams** – Quickly diagnose client site problems
* **Agencies** – Maintain multiple WordPress installations
* **Advanced Users** – Access detailed system information

#### 🚀 Professional Tools

**Debug Log Viewer**
* Real-time debug log monitoring
* Clear instructions for enabling WP_DEBUG_LOG
* Easy-to-read error display

**File Integrity Checker**
* Compare core files against WordPress.org checksums
* Identify modified or missing files
* View file differences inline

**Mail Check**
* Send test emails to verify mail functionality
* Custom message support
* Instant delivery confirmation

**Plugin Compatibility**
* Check all plugins for PHP version requirements
* Identify potential compatibility issues
* Detailed plugin information display

**System Viewers**
* .htaccess file viewer
* robots.txt content display
* Complete PHP configuration (phpinfo)

**Transient Summary**
* View total transients and database usage
* One-click transient cleanup
* Database optimization

#### 🎨 Modern Interface

* Clean, professional React-based UI
* Responsive design works on all devices
* Smooth animations and transitions
* Intuitive navigation
* WordPress admin integration

== Installation ==

#### Automatic Installation

1. Go to **Plugins > Add New** in your WordPress dashboard
2. Search for "HealthBeam"
3. Click **Install Now** and then **Activate**
4. Navigate to **Tools > Site Health > Advanced Tools** to access the plugin

#### Manual Installation

1. Download the plugin from WordPress.org
2. Upload the entire `healthbeam` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **Tools > Site Health > Advanced Tools** to begin using the tools

== Frequently Asked Questions ==

= Where can I find the plugin after activation? =
After activation, go to **Tools > Site Health** in your WordPress admin, then click on the **Advanced Tools** tab.

= Do I need to enable WP_DEBUG to use the Debug Log Viewer? =
Yes, you need to enable `WP_DEBUG_LOG` in your wp-config.php file. The plugin provides clear instructions on how to do this.

= Is this plugin safe to use on production sites? =
Yes, the plugin only reads system information and doesn't modify any core files. However, some tools (like viewing debug logs) may expose sensitive information, so use appropriate access controls.

= Will this plugin slow down my site? =
No! The plugin only loads its resources on the Site Health page and uses efficient REST API calls for data retrieval.

= Can I use this plugin on multisite installations? =
Yes, the plugin is compatible with WordPress multisite installations.

= Where can I get support? =
We offer support via email:
* Email: support@woosofts.com

== Changelog ==

= 1.0.0 =
* Initial release with 8 diagnostic tools
* Debug Log Viewer
* File Integrity Checker
* Mail Check functionality
* Plugin Compatibility checker
* .htaccess Viewer
* PHP Info display
* robots.txt Viewer
* Transient Summary and cleanup
* Modern React-based interface
* REST API integration

== Upgrade Notice ==

= 1.0.0 =
Initial release of HealthBeam with comprehensive diagnostic features.

== Screenshots ==
1. Advanced Tools dashboard with all 8 diagnostic tools
2. Debug Log Viewer showing real-time error logs
3. File Integrity Checker with modified files display
4. Mail Check tool with test email functionality
5. Plugin Compatibility overview
6. Transient Summary with cleanup option

== Third Party Services ==

This plugin uses:
* React – [https://react.dev/](https://react.dev/)
* WordPress Components (@wordpress/components) – [https://developer.wordpress.org/block-editor/reference-guides/components/](https://developer.wordpress.org/block-editor/reference-guides/components/)
* Framer Motion – [https://www.framer.com/motion/](https://www.framer.com/motion/)

== Additional Information ==

**Developer Information:**
* Built with modern React and WordPress REST API
* Uses WordPress coding standards
* Fully translatable with text domain: `healthbeam`
* Namespace: `HealthBeam`

For feedback, feature requests, or assistance, email **support@woosofts.com**
