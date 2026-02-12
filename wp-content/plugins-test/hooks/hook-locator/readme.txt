=== Hook Locator ===
Contributors: jdahir0789
Tags: actions, filters, search, debug, hook.
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0
Version: 1.0
Author: Jaydip Ahir
Text Domain: hook-locator
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Powerful WordPress hook search and analysis tool. Instantly find and analyze actions, filters, and custom hooks across all plugins and themes.

== Description ==

**Hook Locator** is a fast, developer-friendly tool to **search and analyze WordPress hooks** in any theme or plugin. Instantly find `add_action`, `add_filter`, `do_action`, `apply_filters`, and more — with file paths, line numbers, and code highlights.

### Perfect For

- Plugin & theme developers  
- Site maintainers debugging conflicts  
- Code auditors and security reviewers  
- WordPress learners exploring hook usage

### Why Hook Locator

Unlike runtime hook capture tools, Hook Locator performs **static code analysis**, giving **accurate and secure** results without affecting performance.

== Installation ==

### Automatic

1. Go to **Plugins > Add New**
2. Search for **Hook Locator**
3. Click **Install Now** → **Activate**

### Manual

1. Upload the ZIP via **Plugins > Add New > Upload**
2. Activate the plugin

### Getting Started

Go to **Tools > Hook Locator** to start searching hooks.

== Frequently Asked Questions ==

= Does this affect my site performance? =

No! Hook Locator only runs in the WordPress admin when you actively search. There's no frontend code or background processing that affects your site's performance.

= What file types does it search? =

Hook Locator searches only PHP files (.php) since WordPress hooks are PHP-based. It automatically skips other file types for optimal performance.

= Can I search for custom hooks? =

Absolutely! Hook Locator finds any hook name you specify, including custom hooks created by plugins, themes, or your own code.

= Is it safe to use on production sites? =

Yes, Hook Locator is completely safe for production use. It only reads files and never modifies any code. All operations are restricted to users with administrator privileges.

= Does it work with multisite? =

Yes, Hook Locator works perfectly with WordPress multisite installations. Each site can use it independently to analyze their specific plugins and themes.

= Can I export the search results? =

Currently, you can copy individual code snippets to your clipboard. Future versions may include CSV/JSON export functionality based on user feedback.

= What's the difference from other hook plugins? =

Hook Locator focuses on static code analysis rather than runtime hook capture. This makes it more accurate, secure, and performant while providing deeper code insights.

---

== Screenshots ==

1. Main Search Interface – Modern search form with plugin/theme selection  
2. Search Results – Table view with file names, line numbers, and hook types  
3. Code Analysis – Highlighted view of hook usage in context  
4. Hook Type Detection – Color-coded badges for different hook functions  
5. Mobile Responsive – Works on desktops, tablets, and mobile devices  

---

== Changelog ==

= 1.0.0 =
* Initial release with full hook scanning and analysis
* Plugin and theme directory dropdowns with optgroups  
* Improved code highlighting with better contrast  
* Secure coding with nonce checks and sanitization  
* Fully WordPress coding standards compliant (PHPCS/WPCS)  
* Optimized file scanning with smart resource management  
* Professional admin UI with WordPress styling  

---

== Advanced Usage ==

### Keyboard Shortcuts
* **Ctrl/Cmd + K** – Focus search input  
* **Ctrl/Cmd + Enter** – Run search  
* **Escape** – Clear search input  
* **Click snippets** – Copy to clipboard  

---

== Privacy ==

* No personal data collection  
* No external API requests  
* No analytics, tracking, or cookies  
* Admin-only usage  

---

== Support ==

Need help?  

* WordPress.org support forums  
* Inline documentation and tooltips in the admin panel  

Hook Locator is actively maintained and tested with the latest WordPress releases. We welcome feedback and feature requests!  
