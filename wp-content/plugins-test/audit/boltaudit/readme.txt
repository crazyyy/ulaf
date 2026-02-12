=== BoltAudit – Plugin & Performance Analyzer ===
Contributors: heymehedi
Tags: performance, audit, database, optimization, site health
Requires at least: 5.8
Tested up to: 6.8
Stable tag: 0.0.8
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

BoltAudit helps you identify bloated, unused, abandoned, and performance-heavy plugins—plus database bloat, autoloaded options, and runtime impact.

== Description ==

BoltAudit gives you a clear, actionable overview of what’s slowing down your WordPress site.  
It inspects:  
- Active plugins and their actual impact on performance  
- Unused, abandoned, or rarely-used plugins  
- Heavy scripts, enqueued styles, database queries, and metadata  
- Autoloaded options, transients, and custom DB tables  
- Post details page with orphaned metadata and orphaned post records
- Server environment and PHP configuration  

Whether you're a developer, site owner, or agency, BoltAudit gives you visibility into bottlenecks with zero guesswork.

== Features ==

- Detect unused, outdated, or abandoned plugins  
- Full Plugin Audit section: inactive, outdated, abandoned detection  
- Plugin-level metrics: action/filter hook counts, cron job schedules, database query counts  
- Breaks down post types, metadata, revisions, and orphaned types  
- Analyzes option tables, autoloaded data, transients, and more  
- Database snapshot with table sizes, row counts, autoload bloat
- Server environment checks: PHP, MySQL, memory limits, upload size
- Lightweight, on-demand diagnostics—no front-end performance impact
- Responsive, accessible UI with performance optimizations
- WooCommerce Overview with product counts and performance insights (HPOS, cart fragments, and more)


== Installation ==

1. Upload the `boltaudit` folder to `/wp-content/plugins/` or install via **Plugins → Add New**.  
2. Activate **BoltAudit** through the **Plugins** menu.  
3. Navigate to **Tools → BoltAudit**.  
4. Click **Run Audit** to generate your first report.

== Frequently Asked Questions ==

= Does BoltAudit slow down my site? =  
No. BoltAudit runs diagnostics only on demand and does not affect front-end performance.

= Can BoltAudit delete plugins or data? =  
No. BoltAudit is read-only. It provides insights; you decide what to remove or clean up.

= What qualifies as an “abandoned” plugin? =  
Plugins without updates in the past year or with low support-forum activity are flagged as abandoned.

= Will this work on large or complex sites? =  
Yes. BoltAudit was built for heavy-duty environments, including eCommerce, LMS, and community plugins.

= What does the Database Overview section include? =  
Total DB size, number of tables (empty vs. used), autoloaded options, transients, and custom tables to help you spot bloat quickly.

== Changelog ==

= 0.0.8 – 2025-09-05 =  
* Introduced Database Details Page
* Introduced WooCommerce Details Page
* Fix uncaught error

= 0.0.7 – 2025-08-08 =  
* Introduced Post Details page with orphaned metadata and orphaned post record reports  
* Fixed environment “get user ID” issue to ensure accurate username  

= 0.0.6 – 2025-07-30 =  
* Introduced WooCommerce Performance Insights Section  
* Added “Settings” link in the plugin page area for quicker navigation 
* Fixed plugin fetch error  

= 0.0.5 – 2025-07-25 =  
* Added asset impact metrics: script/style file size and load duration  
* Enhanced SinglePluginRepository caching for faster repeated audits  
* UI improvements for metrics breakdown 
* Bug fixes and performance optimizations  

= 0.0.4 – 2025-07-16 =  
* Added Site Details section
* Fixed Plugin Audit for too many plugins
* Improved Table UI

= 0.0.3 – 2025-07-13 =  
* Added full Plugin Audit section to detect inactive, outdated, or abandoned plugins  
* Warnings for unused or risky plugins  
* Improved environment and database reporting  
* UI refinements and performance improvements  

= 0.0.2 – 2025-07-05 =  
* Introduced Database Overview: table sizes, row counts, autoloaded options, transients, and bloat detection  
* Improved post type detection and metadata analysis  
* Minor UI tweaks and wording fixes  

= 0.0.1 – 2025-07-03 =  
* Initial beta release: post-type analyzer & environment report  

== Upgrade Notice ==

= 0.0.8 =  
* Introduced Database Details Page
* Introduced WooCommerce Details Page
* Fix uncaught error