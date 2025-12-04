=== Website LLMs.txt ===
Contributors: ryhowa, samsonovteamwork
Tags: llm, ai, seo, rankmath, yoast, seopress, aioseo
Requires at least: 5.8
Tested up to: 6.8.3
Requires PHP: 7.2
Stable tag: 8.2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically generate and manage LLMS.txt files for LLM/AI content understanding, with full Yoast SEO, Rank Math, SEOPress, and AIOSEO integration.

== Description ==

**Website LLMs.txt** generates and manages an `llms.txt` file, a structured, AI-ready index that helps large language models like ChatGPT, Claude, and Perplexity understand your site’s most important content.

### How llms.txt works
Traditional sitemaps and robots files guide search engines. But as AI-driven systems such as ChatGPT, Claude, and Perplexity increasingly ingest web content, they benefit from a clear, structured list of a site’s most important URLs.
`llms.txt` offers that: a plain-text or Markdown list of essential public URLs, optionally annotated with titles, descriptions, and grouping, designed for AI consumption rather than general web crawling.

### Key benefits
✅ **AI discovery readiness** — future-proof your site for AI indexing and content retrieval.
✅ **Fully automatic** — the plugin builds and updates your `llms.txt` file on its own schedule.
✅ **SEO plugin integration** — works seamlessly with Yoast SEO, Rank Math, SEOPress, and AIOSEO, automatically excluding content marked as *noindex* or *nofollow*.
✅ **Advanced controls** — choose post types, customize file titles or descriptions, attach optional Markdown files, and trigger manual regeneration.
✅ **Developer-friendly** — includes filters such as `llms_generator_get_post_meta_description` for description logic, performance tuning, and custom indexing behavior.
✅ **AI crawler detection** — opt in to track whether GPTBot, ClaudeBot, or PerplexityBot are actually reading your site’s `llms.txt`.
✅ **WooCommerce & multisite ready** — respects product visibility rules and scales easily across large or networked sites.
✅ **Privacy-first experiment** — anonymous, encrypted telemetry helps reveal which bots are accessing `llms.txt` files across the web.

### Activation & setup
1. Activate the plugin.
2. Visit *Settings → LLMs.txt* to configure post types, update frequency (immediate, daily, or weekly), and optional crawler logging.
3. The plugin generates `llms.txt` (and optionally `llms-full.txt`) and serves it from your site root.
4. Content updates trigger automatic regeneration. All noindex/nofollow rules from your SEO plugin are respected.
5. If you enable AI crawler logging, local and global logs record each visit from known AI bots — viewable right inside your WordPress dashboard.

### Use cases for llms.txt
- Publishers, SaaS companies, developers, and documentation sites that want to make their content easier for AI systems to interpret.
- SEO-driven websites teting AI engine optimization tactics.
- Agencies and site owners preparing for the next phase of AI search and retrieval.

### The llms.txt experiment & further reading
- [Are AI bots actually reading llms.txt files?](https://completeseo.com/are-ai-bots-actually-reading-llms-txt-files/)
- [Everything we know about llms.txt](https://completeseo.com/everything-we-know-about-llms-txt/)


== Installation ==

1. Upload the plugin files to `/wp-content/plugins/website-llms-txt`
2. Activate the plugin through the *Plugins* screen in WordPress
3. Go to *Settings → LLMs.txt* to configure options and generate your file


== Frequently Asked Questions ==

= What is llms.txt? =
`llms.txt` is a plain-text or Markdown file placed at the root of your domain (for example `https://example.com/llms.txt`) that lists your site’s most important public URLs. It helps large language models (LLMs) like ChatGPT, Claude, and Perplexity better understand your site’s structure and priority content.

= How does the Website LLMs.txt plugin work? =
The plugin automatically generates and maintains your `llms.txt` file based on published content. It pulls titles and descriptions from your site, respects SEO plugin settings (Yoast SEO, Rank Math, SEOPress, and AIOSEO), and excludes anything marked as *noindex* or *nofollow*. The file is then served from your site root, ready for AI crawlers to read.

= How often is llms.txt updated? =
You can set the update frequency in the plugin settings — immediate, daily, or weekly. You can also click “Generate Now” in the admin panel to rebuild the file at any time.

= Does this guarantee visibility in ChatGPT, Claude, or Perplexity? =
No. There’s no guarantee that any AI model will immediately use `llms.txt`, but it’s clear that several systems — including GPTBot, ClaudeBot, and PerplexityBot — are already crawling these files. Using `llms.txt` positions your site ahead of the curve as AI indexing becomes more structured.

= What’s the difference between llms.txt and llms-full.txt? =
`llms.txt` is a concise, curated list of key URLs.
`llms-full.txt` is an optional extended file generated by the plugin that includes a more comprehensive export of your site’s content. It’s useful for documentation sites, developer platforms, or large content hubs that want to expose additional structure to AI systems.

= What if my host doesn’t allow writing to the root directory? =
The plugin includes fallback logic for environments such as WordPress VIP or read-only hosting. In those cases, it serves `llms.txt` virtually through WordPress rewrite rules, so the file is still accessible at `https://example.com/llms.txt`.

= Does it work with SEO plugins like Yoast or Rank Math? =
Yes. It automatically integrates with Yoast SEO, Rank Math, SEOPress, and AIOSEO. Pages marked as *noindex* or *nofollow* in any of those plugins will be excluded from your `llms.txt` file automatically.

= Can I track which AI bots visit my llms.txt file? =
Yes. When crawler logging is enabled, visits from AI crawlers such as GPTBot, ClaudeBot, and PerplexityBot are recorded. You can view these visits in your WordPress dashboard. If you opt into the global experiment, your data is anonymized and encrypted before contributing to a shared dataset that tracks AI bot behavior across thousands of sites.

= Will it conflict with sitemap.xml or robots.txt? =
No. `llms.txt` complements your sitemap and robots file. Sitemaps tell search engines what to crawl; `llms.txt` helps AI systems understand what’s most valuable. They work together without overlap or conflict.

= Can I customize what appears in llms.txt? =
Yes. You can include or exclude specific post types, add a custom title or description, and even attach Markdown (`.md`) files to individual posts or pages. The plugin provides a straightforward settings panel and per-page controls for fine-tuning output.

= I’m a developer. Are there filters or hooks available? =
Yes. Filters such as `llms_generator_get_post_meta_description` and others allow you to modify how descriptions are generated or extend what metadata appears in the file. Developers can also adjust caching behavior, database queries, and output formatting.

= Is any personal data shared when I enable crawler logging? =
No. All telemetry is privacy-first. Local logs remain on your site. If you opt into the public experiment, only anonymized data (bot name, timestamp, and a hashed version of your domain) is shared. No content, user, or identifiable data is ever transmitted.


== Changelog ==

= 8.2.3 =

📝 Update: README.txt improvements
• Updated the link for “All websites counter & experiment details” to the new, correct URL.
• Minor text adjustments for clarity and consistency within the documentation.

= 8.2.2 =

🛠 Fix: PHP Fatal Error (ArgumentCountError)
• Fixed the issue: Fatal error: Uncaught ArgumentCountError: 5 arguments are required, 3 given in admin-page.php:356

= 8.2.1 =

🛠 Fix: PHP Fatal Error (ArgumentCountError)
• Fixed the issue: Fatal error: Uncaught ArgumentCountError: 5 arguments are required, 3 given in admin-page.php:356
• The error occurred because printf() was used with a translatable string that expected more placeholders than arguments provided.
• Replaced it with a safe sprintf() and wp_kses_post() implementation to properly escape HTML and ensure compatibility with PHP 8.x.

= 8.2.0 =

🧩 New: LLMs.txt Reset Block
• Added a new “LLMs.txt Reset” section in the settings panel.
• Allows safely deleting and recreating the llms.txt file.
• Clears any related transient cache entries.
• Automatically rebuilds a fresh version of llms.txt based on current settings and published content.

📝 Improved Field Descriptions for Custom LLMs.txt Content
• Updated admin field labels and descriptions for better clarity:
• Title: manually define the title for the generated file.
• Description: add an introductory section before URLs.
• After Description: insert optional text before the list of links.
• End File Description: append footer text (e.g., disclaimer or contact info).

⚙️ Enhancement:
• Improved layout consistency and help text readability across the settings panel.


= 8.1.9 =

✨ New: SEOPress Support
• Added compatibility with SEOPress plugin for meta data handling.

✨ Improvement: Title Generation
• Refactored title generation – titles are now fetched dynamically from the actual page to ensure accuracy.

✨ Enhancement: Admin Panel UX
• Added a progress bar for the “Generate Now” process in the admin panel for better visibility of ongoing tasks.

= 8.1.8 =

✨ Improvement: Hidden Posts Exclusion
• Posts and products marked with WooCommerce catalog visibility settings “exclude-from-catalog” or “exclude-from-search” are now excluded from being listed in llms.txt.
• Ensures that items set to Hidden, Shop only, or Search results only do not appear in the generated llms.txt file.
• Aligns llms.txt output with WooCommerce visibility rules for better consistency and control.

= 8.1.7 =

🐞 Fixed: XML Sitemap Stylesheet Issues
• Fixed an issue where llms-sitemap.xml displayed a blank page in Chrome/Edge or the error Parsing an XSLT stylesheet failed in Firefox.
• Added a check to ensure the stylesheet file (main-sitemap.xsl) exists before including it. If missing, the XML now loads correctly without the XSL.
• Improved cross-browser compatibility for displaying XML sitemaps.

✨ New: Post Type Customization in llms.txt
• Added support for customizing post type display names in the llms.txt file.
• Developers can now provide more descriptive or human-friendly titles for each custom post type section, improving clarity for both search engines and users.

= 8.1.6 =

🛠 Improved: Extensibility & Performance
• Added filter llms_generator_get_post_meta_description to make it easier to extend or replace the logic for retrieving page/post descriptions (e.g. integrating with Yoast, RankMath, or custom SEO functions).
• Added new filter to control which database index/field is used when building the llms.txt file, giving developers more flexibility for performance tuning and custom setups.

= 8.1.5 =

📝 New: Custom Description Field per Page/Post
• Added a new “Description” textarea field to the llms.txt metabox on individual pages/posts.
• This allows site admins to manually override the default description shown in the llms.txt output.
• Useful for precise control over how content is described or interpreted by LLMs and search engines.

🐛 Fix: Missing Description Field UI
• Fixed an issue where the changelog referenced a description field, but it was not visible in the admin UI unless specific settings were enabled.
• Now shown whenever page-level llms.txt settings are active.

= 8.1.4 =

✨ New: ACF Template-Based Post Indexing
• Posts using ACF-based templates (with custom fields and layouts) are now fully supported in the llms.txt generation process.
• Ensures that even dynamically rendered content is included in the index file.

🔍 Improvement: Post Type Indexing Summary
• The admin interface now displays the total number of posts per type alongside how many have been indexed (e.g. “Posts (123 indexed of 1829)”).
• Makes it easier to monitor indexing coverage and debug missing entries.

= 8.1.3 =

✨ New: Manual Generation Trigger for llms.txt
    • Added a "Generate Now" option in the admin to manually trigger llms.txt file generation without waiting for scheduled cron jobs.
    • Allows immediate regeneration for testing or urgent updates.

🐛 Fix: WP Engine Root File Creation Issue
    • Resolved an issue where llms.txt was generated in the uploads directory but not copied to the WordPress root on WP Engine-hosted sites.
    • Improved file system handling to ensure compatibility with WP Engine’s direct FS method and restrictive environments.
    • Includes fallback logic for reliable file movement and permission setting.

= 8.1.2 =

🐛 Fix: Trailing Slash Redirect Issue on llms.txt and llms-full.txt
	•	Resolved an issue where WordPress would incorrectly redirect requests for /llms.txt and /llms-full.txt due to trailing slash conflicts.
	•	Implemented a filter-based override to prevent canonical redirection behavior for these endpoints.
	•	Ensures proper file access and visibility across all permalink structures.
	•	Inspired by and aligned with community solutions provided for similar plugin issues.

= 8.1.1 =

🔧 Compatibility Fix: WordPress VIP Filesystem Support
	•	Resolved an issue where the plugin could not write the llms.txt file on WordPress VIP environments due to the lack of stream_lock support.
	•	Implemented fallback logic using WP_Filesystem:
	•	If the direct method is available, the plugin now writes using native PHP file handles (fopen in append mode) for better performance and memory efficiency on large files.
	•	Ensures compatibility with WordPress VIP’s restricted filesystem wrapper.
	•	Improved error handling and logging when file writing is not possible due to server restrictions.

= 8.1.0 =

🛠 Fix: 404 Error on llms-sitemap.xml with Yoast SEO

• Resolved an issue where the llms-sitemap.xml endpoint returned a 404 error when Yoast SEO was active.
• The sitemap rewrite rule is now properly registered and recognized, ensuring the sitemap is accessible alongside Yoast’s sitemaps.

= 8.0.9 =

🌐 WPML URL Generation Fix

• Fixed an issue where llms.txt was generating duplicate URLs with the same language code for all translations.
• Each URL is now generated correctly according to its respective language version in multilingual setups using WPML.

= 8.0.8 =

🛠️ SEO Compatibility Fixes

• Fixed an issue where Rank Math dynamic tags (e.g. %title%, %customterm(something)%) were not being rendered in llms.txt titles and descriptions.
• Dynamic SEO meta data now resolves correctly for all post types when using templates from Rank Math.

= 8.0.7 =

🌐 I18N Improvements

• Fixed localization issue in class-llms-md.php: the “Delete file” button label is now correctly translatable using esc_html_e() with the proper text domain.
• Ensured all static strings in UI components follow internationalization best practices.

= 8.0.6 =

🐞 Bug Fixes

• Fixed PHP warnings about undefined array key detailed_content in class-llms-generator.php when running cron from WP CLI.
• Added additional checks and defaults to prevent warnings in environments where detailed_content is not set.

= 8.0.5 =

🚀 New Feature & Bug Fixes

• Added support for deleting the uploaded .md file directly from the meta box.
• Fixed the behavior of the “Do not include this page in llms.txt” checkbox — now, when activated, the page is correctly excluded from the generated llms.txt file.

= 8.0.4 =

🐞 Bug Fixes & i18n Improvements

• Fixed internationalization (i18n) issue in the meta box: wrapped the meta box title in __() for proper translation support (thanks to Alex Lion for the report).
• Fixed PHP warnings about undefined array keys (llms_txt_title, llms_txt_description, llms_after_txt_description, llms_end_file_description, include_md_file, detailed_content) by adding proper defaults and safe checks when saving settings.
• Minor code cleanup to improve stability and compatibility.

= 8.0.3 =

🐞 Minor Fix: Meta Box Title

• Renamed the page/post meta box title from “Markdown (.md) file” to “Llms.txt” for better clarity and consistency with the feature’s purpose.

= 8.0.2 =

✨ UI & Page-Level Control: Sidebar Meta Box & Exclusion Option

• Moved the Markdown (.md) file meta box to the sidebar of the page/post edit screen for a cleaner and more consistent experience.
• Added a “Do not include this page in llms.txt” checkbox at the page level to allow excluding individual pages/posts from llms.txt output.
• Updated the meta box to include: llms.txt heading, .md upload field, and the new exclusion checkbox — all neatly organized.
• Ensured the exclusion setting and uploaded .md file are saved correctly and reflected in llms.txt.
• Minor UI polishing and accessibility improvements to align with WordPress admin styles.

= 8.0.1 =

✨ Enhancements & Options: More Flexible LLMS.txt Content Control

• Changed default behavior: options Include meta information (publish date, author, etc.), Include post excerpts, and Include taxonomies (categories, tags, etc.) are now unchecked by default for cleaner output.
• Added a new option: Include detailed content — allowing fine-grained control over whether to include detailed page/post content in the llms.txt file.
• Improved settings clarity and fallback behavior when all optional content is disabled.

= 8.0.0 =

✨ New Features & Improvements: Admin UI, Content Options, Markdown

• Rearranged admin dashboard: moved warning section and update frequency settings into an “Advanced Settings” card for better clarity.
• Improved content settings: added checkboxes to control inclusion of post excerpts and meta descriptions in output, with cleaner fallback to just URL + Title when unchecked.
• Added a dedicated “Custom LLMS.txt Content” panel in settings for defining a custom Title, Description, After Description, and End File Description.
• Added custom description field and an additional manual entry field per page/post, both included in llms.txt.
• Added support for attaching `.md` (Markdown) files per page/post — link to the file appears in llms.txt if enabled.
• `.md` files are stored in a dedicated `/llms_md/` folder and linked in llms.txt for reference.

= 7.1.6 =

🐞 Bug Fixes & Enhancements: Stability, Indexing, and Compatibility

• Fixed PHP warning for undefined llms_allow_indexing key in yoast.php, added proper default handling.
• Improved compatibility with Yoast SEO & RankMath by checking settings arrays before use.
• Enhanced fallback handling for missing meta descriptions and cleaned up fallback output in generated files.
• Minor code refactoring for better PHP 8.2+ compatibility and reduced log noise.

= 7.1.5 =

🐞 Bug Fixes & Improvements: WooCommerce, WP-Rocket, PHP Notices, and I18N

• Fixed a fatal error when editing WooCommerce products (has_weight() on null) caused by the plugin calling do_shortcode() on product content — now properly checks context and avoids passing invalid post data to WooCommerce templates.
• Adjusted WP-Rocket cache clearing behavior.
• Resolved PHP Notice in admin menu creation (add_submenu_page) by ensuring the 7th parameter is numeric (position), no longer passing invalid icon string.
• Improved I18N (Internationalization) strings in admin-page.php for proper localization and improved translations.
• Added minor UI fixes and cleaned up wording in the admin area.

✅ Recommended upgrade if you use WooCommerce, Divi theme, or WP-Rocket, and/or run with WP_DEBUG enabled.
🎯 Thanks to all users who reported and helped debug these issues!

= 7.1.4 =

🐞 Bug Fixes: Generator Stability and PHP 8.x Compatibility

• Fixed PHP warnings about undefined `$output` variable in `class-llms-generator.php` when generating LLMS data
• Fixed deprecated usage of `mb_convert_encoding()` with null input on line 428
• Ensures `$output` is always initialized before being used and passed to `mb_convert_encoding()`
• Improved error handling when no content is available to write during generation
• Verified compatibility with PHP 8.1 and 8.2 to prevent log noise and execution failures

= 7.1.1 =

🐞 Bug Fix: LLMS Crawler Activation

• Fixed an issue where the LLMS Crawler feature was not activating correctly after plugin installation or settings update
• Ensures that the crawler logging toggle properly saves and reflects the current state in the admin UI
• Improved reliability of the global experiment opt-in status

= 7.1.0 =

🐞 Bug Fix: Admin Menu Compatibility

• Fixed a PHP notice when WP_DEBUG is enabled, caused by incorrect usage of `add_submenu_page()`
• The submenu page no longer passes an icon name (`dashicons-media-text`) as the 7th parameter — now uses a proper numeric menu position
• Improves compatibility with WordPress >= 5.3 and prevents unnecessary log noise

= 7.0.9 =

🧠 New Feature: AI Crawler Detection

• Added new admin section with detailed insights into AI bot activity on your llms.txt file
• Introduced logging for AI crawlers like GPTBot, ClaudeBot, and PerplexityBot — including bot name and last seen timestamp
• Added dashboard table to view recent bot visits (max 100 entries, rolling log)
• New setting: opt in to the global AI crawler detection experiment — anonymously share bot access data (hashed domain + bot name)
• All telemetry is privacy-first: no content or personal data is collected or stored
• Integrated backend support for real-time participation tracking across thousands of sites
• Added admin banner linking to “How it works” with full experiment explanation

= 7.0.8 =

🛠 Improvements & Fixes
- File Status section now conditionally displays links (e.g. sitemap) only when relevant settings are enabled
- Prevents broken links when sitemap inclusion is not selected
- Minor UI consistency improvements

= 7.0.4 =

🛠️ Bug Fixes & Enhancements

• Added X-Robots-Tag: noindex header for llms.txt by default to discourage indexing by search engines.
• Introduced a checkbox setting to optionally disable the noindex header (not recommended).
• Cleaned up plugin description for clarity and removed outdated marketing language.
• Minor internal code improvements for consistency and maintainability.

= 7.0.3 =

🛠️ Bug Fixes & Improvements

• Added support for excluding llms.txt from sitemaps by default to prevent unintended indexing by search engines.
• Introduced an optional checkbox in settings to allow manual inclusion of llms.txt in the sitemap, with a clear SEO warning.
• On plugin deactivation, scheduled tasks related to llms.txt are now properly cleared and the file is removed from the site root to avoid stale exposure.

= 7.0.2 =

🛠️ Bug Fixes & Improvements

• Fixed an issue with detecting `nofollow` and `noindex` pages when using the Rank Math SEO plugin.
• The "Clear Caches" button in the Cache Management block now also clears the LLMS index table to ensure full site reindexing.

= 7.0.1 =

🛠️ Bug Fixes: JSON API Compatibility

• Resolved a critical issue that caused "Update failed. The response is not a valid JSON response." when editing or publishing posts.
• The plugin now correctly avoids interfering with the WordPress REST API response during post save/update actions.
• Confirmed compatibility with block editor and custom post types — post creation and updates now work reliably.

= 7.0.0 =

🚀 Major Overhaul: LLMS.txt Generation & Performance

• Rebuilt the LLMS.txt generation system from the ground up.
• Introduced a dedicated `llms_txt_cache` database table to index and store structured data efficiently.
• Greatly reduced server load by avoiding direct filesystem writes and enabling smarter caching.
• File generation is now handled **asynchronously via scheduled cron jobs** to avoid UI slowdowns and improve scalability.
• Minimized the number of filesystem write operations during LLMS.txt generation, improving reliability and performance.
• Optimized for large-scale databases — smoother performance on sites with thousands of posts.

= 6.1.2 =

🔧 Improved: Internationalization (i18n) and Display Logic
• Resolved several i18n issues by improving translation coverage and context handling.
• Prevented empty post_content pages from being shown in detailed content view.
• Fixed incorrect tagline display by properly falling back to site description settings.

These updates improve localization accuracy, content visibility logic, and metadata consistency.

= 6.1.1 =

🧹 Removed: Global Cache Flush
• Eliminated `wp_cache_flush()` calls from content processing loop.
• Prevented unintended flushing of global object cache affecting other plugins.
• Reading operations no longer interfere with cache integrity.

= 6.1.0 =

✅ Fixed: Yoast SEO Variable Parsing
• Resolved issue where dynamic SEO content using Yoast variables (e.g., %%title%%, %%excerpt%%) wasn’t correctly replaced during content generation.
• Content processed through wpseo_replace_vars() to ensure accurate output.
• Improved compatibility with Yoast SEO templates, even when used outside the standard loop or template hierarchy.

= 6.0.8 =

✅ Fixed: Emoji and Code Cleanup in llms.txt
• Emojis and unnecessary symbols are now automatically removed from `llms.txt`.
• Code snippets are correctly sanitized for plain-text output.
• Improved table formatting: table data is now correctly aligned and rendered when exported.

= 6.0.7 =

🗑️ Removed ai.txt File Generation
• The automatic creation of the ai.txt file has been removed.
• This change reduces unnecessary file writes and simplifies plugin behavior.
• If needed, you can still manually create and manage ai.txt in your site’s root.

= 6.0.6 =

✅ Persistent Dismiss for Admin Notices
• Admin notices now store dismissal state using user meta — ensuring they remain hidden once closed.
• No more repeated reminders across dashboard pages — smoother and less intrusive user experience.

🛠 Minor Code Cleanup
• Removed outdated notice render logic.
• Improved JS handling for notice dismissals across multi-user environments.

= 6.0.5 =
⚡ Enhanced Performance & Clean Output
• Database query logic fully refactored for high-speed data selection, reducing generation time by up to 70% on large sites.
• Replaced WP_Query with direct SQL access — now works faster and avoids unnecessary overhead.
• Significantly improved scalability and lower memory usage during .txt file generation.

🧹 Special Character Cleanup
• Removed invisible and problematic characters (NBSP, BOM, ZWSP, etc.) from post content to ensure clean and readable output.
• Prevents display issues and improves downstream AI parsing of .txt files.

📈 Faster Regeneration
• Full .txt regeneration after content updates is now noticeably faster, especially on content-heavy websites.
• Better memory handling and reduced write cycles during generation.

= 6.0.4 =

🌐 Multisite Link Format Change
• For multisite installations, .txt files are now accessible via trailing slash URLs:
example.com/llms.txt/ and example.com/ai.txt/.
• This ensures compatibility across various server environments and mapped domain setups.
• For single-site setups, physical .txt files are still generated and stored in the root directory.

🔧 Yoast SEO Exclusion Fix
• Fixed an issue where pages marked with noindex or nofollow in Yoast SEO were not properly excluded from the .txt output.
• Now both _yoast_wpseo_meta-robots-noindex and _yoast_wpseo_meta-robots-nofollow are fully respected.

= 6.0.3 =

🐛 Fix: 404 Not Found on NGINX Servers
• Resolved an issue where .txt files (llms.txt, ai.txt) returned a 404 error on NGINX-based hosting environments.
• Rewrite rules are now properly flushed and executed without needing manual permalink updates.

💰 Product Price Output
• Product prices are now displayed as plain text values (e.g., 56.00 USD) instead of HTML when WooCommerce support is enabled.
• Ensures clean and readable output for price values in llms.txt.

🔄 Important: Clear Cache After Update
• After updating to this version, please clear your site’s cache (including server-side and CDN cache) to ensure .txt file endpoints load correctly.

= 6.0.2 =

🌐 Multisite Support (Beta)
• The plugin now supports WordPress Multisite environments.
• Each site now stores and serves its own `llms.txt` and `ai.txt` content independently.
• Scheduled cron tasks are isolated per site to ensure accurate and isolated updates.
• Multisite-aware hooks implemented in `template_redirect` to correctly output `.txt` files on mapped domains.

📢 Admin Notice for Feature Suggestions
• Added a dismissible admin notice on new plugin installs to gather feedback and feature suggestions from users.
• Links included to Twitter and WP.org support forum for easy community engagement.
• Let’s coordinate on Slack for the next release to align on roadmap input strategy.

= 6.0.1 =

🛠️ Breakdance Compatibility Fix
• Fixed an issue where enabling “instant” updates for the llms.txt file on post save caused a 500 error when using the latest version of Breakdance Builder.
• Now, immediate updates are handled safely without interrupting the save process.

⏱️ Improved Cron Handling
• Switched to using a single scheduled event (wp_schedule_single_event) instead of triggering file updates directly during shutdown.
• This ensures better compatibility and stability, especially on heavy or slower servers.

➕ WooCommerce SKU Support
• Added SKU output if the post type is a WooCommerce product.
• The llms.txt file now includes a line like - SKU: [Product SKU] when available.


= 6.0.0 =

🛠️ Page Creation Respecting Settings
• Fixed a logic inconsistency where the AI Sitemap page could still exist even if the related setting was disabled.
• The plugin now ensures that page creation behavior strictly follows the user’s configuration, both during normal operation and after plugin updates.


= 5.0.8 =

🛠️ Page Creation Respecting Settings
• Fixed a logic inconsistency where the AI Sitemap page could still exist even if the related setting was disabled.
• The plugin now ensures that page creation behavior strictly follows the user’s configuration, both during normal operation and after plugin updates.

= 5.0.7 =

✅ New: Optional AI Sitemap Page
• Added a new setting to disable automatic creation of the AI Sitemap page (ai-sitemap).
• Users can now manage whether this page is created on init via the plugin settings panel.

🧠 Performance & Memory Usage
• Improved memory handling during content generation, especially for large post meta datasets.
• Reduced risk of memory leaks when working with heavy content by loading posts via IDs and flushing cache dynamically.

📄 Content Generation Enhancements
• Fixed issues related to long post content generation in llms.txt.
• Added a new option to control the number of words included per post in the generated file (default: 250).
• Better content trimming and cleaning logic for consistent output.

🔧 Stability & Cleanup
• Optimized handling of unset variables and object cleanup to avoid bloating memory usage during cron or manual execution.

= 5.0.7 =

✅ Settings Consistency Improvements
• The plugin now respects the “Include AI Sitemap page” setting more reliably across updates.
• Internal checks ensure that unnecessary pages are not created or kept when the option is disabled.

🧠 Update-Aware Logic
• Introduced version-aware behavior to trigger settings-related adjustments only once after plugin updates.
• Ensures cleaner and more consistent state without manual intervention.

= 5.0.6 =

✅ New: Optional AI Sitemap Page
• Added a new setting to disable automatic creation of the AI Sitemap page (ai-sitemap).
• Users can now manage whether this page is created on init via the plugin settings panel.

🧠 Performance & Memory Usage
• Improved memory handling during content generation, especially for large post meta datasets.
• Reduced risk of memory leaks when working with heavy content by loading posts via IDs and flushing cache dynamically.

📄 Content Generation Enhancements
• Fixed issues related to long post content generation in llms.txt.
• Added a new option to control the number of words included per post in the generated file (default: 250).
• Better content trimming and cleaning logic for consistent output.

🔧 Stability & Cleanup
• Optimized handling of unset variables and object cleanup to avoid bloating memory usage during cron or manual execution.

🧪 Tested With
• ✅ WordPress 6.5
• ✅ Yoast SEO 22.x
• ✅ Rank Math & AIOSEO compatibility verified

= 5.0.5 =

✅ Fixed 404 Error for Sitemap XML
• Resolved an issue where the llms-sitemap.xml endpoint could return a 404 error despite being properly registered.
• Now correctly sets the HTTP 200 status header for valid sitemap requests using status_header(200), ensuring compatibility with WordPress routing and sitemap indexing.
• Improved query var handling and rewrite rule registration to guarantee sitemap accessibility.

🧠 Other Improvements
• Refactored request handling logic to ensure clean output with proper MIME type headers (application/xml).
• Further stability improvements for Yoast integration and dynamic sitemap indexing.

🧪 Tested with WordPress 6.5 and Yoast SEO 22.x

= 5.0.4 =

🛠 Improvements & Fixes

✅ Automatic AI Sitemap page generation
    • The plugin now auto-creates a public /ai-sitemap page explaining what LLMs.txt is and how it improves AI visibility.
    • The page is only created if it doesn’t already exist, and includes a dynamic link to your actual LLMs sitemap file.
    • Content is filterable for advanced customization.

✅ Added support for ai.txt as an alternate LLM sitemap path
    • The plugin now generates both /llms.txt and /ai.txt to maximize compatibility with future AI indexing standards.
    • Both files are kept in sync and contain the same URL list.
    • This improves discoverability by AI crawlers that look for ai.txt by default.

✅ Enhanced onboarding & reliability
    • Improved logic to prevent duplicate pages.
    • Cleaned up sitemap text formatting for better readability.
    • Hook-friendly architecture for developers.

🚀 This update makes your site even more AI-ready by exposing your content through both standard and emerging LLM indexing formats — paving the way for visibility in tools like ChatGPT, Perplexity, and beyond.

= 5.0.3 =

🛠 Improvements & Fixes

✅ Added support for AIOSEO plugin
    • Integrated detection of aioseo_posts table to improve filtering accuracy.
    • Posts marked with robots_noindex or robots_nofollow in AIOSEO are now correctly excluded from output.
    • Fallback-safe: the logic only applies if the AIOSEO table exists in the database.

✅ Enhanced compatibility with multiple SEO plugins
    • Filtering logic now handles both Rank Math and AIOSEO data sources.
    • Posts without SEO meta data are still properly included unless explicitly marked as noindex.

🚀 This update expands SEO plugin compatibility, ensuring more accurate output when working with AIOSEO-powered sites, and avoids accidental indexing of excluded content.


= 5.0.2 =
✅ Fixed: Removed invalid contributor username from readme.txt (only WordPress.org profiles are allowed)

= 5.0.1 =

🛠 Improvements & Fixes

✅ Fixed issue with empty LLMS-generated files
	•	Resolved a bug where LLMS-generated files could appear empty if the rank_math_robots meta key was missing from posts.
	•	The plugin now correctly includes posts even if the Rank Math plugin is not installed or the meta field is not present.
	•	Prevented false negatives by ensuring the query accounts for both existing and non-existent rank_math_robots fields.

✅ Improved meta query logic for noindex handling
	•	Extended the meta_query to handle posts without the rank_math_robots key gracefully.
	•	Ensured that only posts explicitly marked as noindex are excluded, while all others (including those with no SEO plugin data) are properly included.

✅ Improved file generation accuracy
	•	Ensured that LLMS-related output files contain valid, expected content — reducing cases where generated files were blank due to strict filtering.
	•	Improved fallback logic for posts without SEO meta data.

🚀 This update ensures that LLMS-generated files remain accurate and complete, even on sites that don’t use Rank Math, and improves overall reliability when filtering content by SEO metadata.

= 5.0.0 =

🛠 Improvements & Fixes

✅ Added support for excluding noindex pages from Rank Math SEO

- The plugin now properly detects and excludes pages that have the `noindex` directive set in Rank Math SEO.
- Ensured that pages with `rank_math_robots` meta key containing `noindex` will not be included in the LLMS-generated files.
- This enhancement improves search engine indexing by preventing noindex-marked pages from being processed.

✅ Extended support for Yoast SEO & Rank Math

- Now supports both Yoast SEO and Rank Math SEO for detecting `noindex` pages.
- Ensured that `meta-robots-noindex` in Yoast and `rank_math_robots` in Rank Math are respected.
- Improved meta query logic to exclude noindex-marked pages efficiently.

✅ Better performance & stability

- Optimized post query handling to reduce unnecessary database queries when filtering indexed content.
- Improved support for large-scale websites by ensuring efficient exclusion of noindex pages.

🚀 This update ensures full compatibility with both Yoast SEO and Rank Math SEO, improving site indexing and preventing unwanted pages from being processed.


= 4.0.9 =

🛠 Improvements & Fixes
✅ Fixed compatibility issue with Yoast SEO sitemap generation

Resolved a problem where the llms-sitemap.xml file was not properly integrated with Yoast SEO’s sitemap indexing.
Ensured that the custom llms-sitemap.xml is correctly registered and included in Yoast’s sitemap structure.
✅ Enhanced XML sitemap handling

Added support for llms-sitemap.xml in the Yoast SEO wpseo_sitemaps_index filter.
Improved automatic detection and registration of the custom sitemap to avoid conflicts.
✅ Better performance & stability

Optimized the sitemap generation process to ensure compatibility with WordPress rewrite rules.
Fixed potential issues where the custom sitemap URL might not be accessible due to incorrect rewrite rules.
🚀 This update ensures full compatibility between the LLMS sitemap and Yoast SEO, improving site indexing and search engine visibility.