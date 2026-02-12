=== Code Engine ===
Contributors: TigrouMeow
Tags: code, snippet, ai, php, rest
Donate link: https://www.patreon.com/meowapps
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 0.4.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Versatile plugin that not only manages PHP code snippets but also acts as a powerful bridge connecting WordPress, AI, and external digital platforms.

== Description ==

Code Engine is a versatile plugin that not only manages PHP code snippets but also acts as a powerful bridge connecting WordPress, AI, and external digital platforms. It has three main features:

- PHP Code Snippets Management
- Automation via External Access
- Integration with AI

Think out of the box, and let your creativity flow, because the possibilities are endless! 🤟

=== PHP Code Snippets Management ===

With its intuitive interface, Code Engine unlocks a world of possibilities by allowing you to dynamically modify and extend your site's functionality without the need for extensive coding knowledge. Code snippets can be created and enhanced by AI, making the process even more efficient and user-friendly.

=== Automation via External Access ===

Code Engine offers seamless integration with the REST API, allowing you to execute runnable code snippets through external processes. This opens up a realm of automation opportunities, enabling you to streamline your tasks and save valuable time. By leveraging tools like Make.com, you can create sophisticated workflows that interact with your site via the REST API.

=== Integration with AI ===

Take your site to the next level with Code Engine's cutting-edge AI Engine integration. This powerful feature allows you to collaborate seamlessly with AI models, enabling them to run actions and enrich their responses by accessing your code snippets. By incorporating actionable snippets, you can provide your AI models with the ability to perform tasks and deliver more comprehensive and dynamic answers to user queries.

== Installation ==

1. Upload `code-engine` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Upgrade Notice ==

Replace all the files. Nothing else to do.

== Frequently Asked Questions ==

Nothing yet.

== Changelog ==

= 0.4.2 (2025/12/16) =
* Add: Added shortcode attributes.
* Fix: Improved DB checks with an early return to reduce unnecessary queries.

= 0.4.1 (2025/12/03) =
* Fix: Ensure blocks keep the correct context when focused by improving how block properties are handled.
* Fix: Restore proper behavior of the Code Blocks option and its settings tab.
* Add: Allow usage of PHP tags to create HTML line breaks in content.
* 🎵 Discuss with others about Code Engine on [the Discord](https://discord.gg/bHDGh38).
* 🌴 Keep us motivated with [a little review here](https://wordpress.org/support/plugin/code-engine/reviews/). Thank you!
* 🥰 If you want to help us, check our [Patreon](https://www.patreon.com/meowapps). Thank you!

= 0.4.0 (2025/11/12) =
- Add: Enhanced the overall UI/UX for better user experience.
- Add: Keyboard shortcuts (Cmd+S, Cmd+Enter).
- Add: Included a "Clean Uninstall" option for easier plugin removal.
- Update: Reorganized plugin interface into tab-only layout.
- Fix: Hotfix to prevent other actions during code validation.
- Fix: Export of snippets.

= 0.3.9 (2025/10/10) =
- Add: Implemented CMD+S as a shortcut to save snippets.
- Fix: Resolved an undefined array key warning related to 'target'.
- Update: Ignore __mwai_ arguments in the editor for cleaner operation.
- Update: Enhanced the UI/UX.

= 0.3.8 (2025/09/30) =
- Update: Improved the entire UI for a better user experience.
- Fix: Ensure mwai_query parameter is correctly passed and accessible within function calls.

= 0.3.7 (2025/09/01) =
* Update: Code Engine Pro.

= 0.3.6 (2025/08/16) =
* Update: There is now a Pro version for Code Blocks and Shortcodes.
* Update: Minor improvements and bug fixes.

= 0.3.5 (2025/07/23) =
* Fix: Resolved React rendering issue caused by empty tags and undefined map calls.  
* Update: Refreshed common functionalities for improved stability.

= 0.3.4 (2025/07/06) =
* Update: Disallow PHP execution in code blocks by default, with an option to enable it.
* Update: Refactored the getSnippets method for improved stability.
* Update: Changed snippet name filtering to use 'functionName' instead of 'name'.
* Fix: Allowed global snippets to execute on the Settings page to prevent blocking nonce filters.

= 0.3.3 (2025/06/29) =
* Add: Support for "content" scope in dashboard and tags.
* Fix: Editor modal not displaying correctly.
* Fix: Columns and empty message now display as expected.
* Update: Renamed MCP tool prefix from code_engine_ to mwcode_ for better consistency.
* Update: Check DISALLOW_UNFILTERED_HTML for JavaScript content type snippets to enhance security.
* Update: Replaced error_log with core logging in API for improved reliability.
* Fix: Dashboard disables Quick Start if AI Engine is missing.
* Fix: Added missing dependencies to mwcode_snippet_vault script registration.

= 0.3.2 (2025/06/03) =
* Add: API now matches the functionality of AI Engine and includes optional MCP support.
* Add: Security bypass option and reorganized plugin settings for easier management.
* Update: Shortened filter names to mwcode_rest_whitelist and mwcode_rest_authorized for clarity.
* Fix: Hotfix—adjusted to use 'size' parameter instead of 'fullSize', resolving compatibility issues.
* Fix: Resolved error_log spam, clarified REST route blocking messages, corrected typos, and improved code readability.

= 0.3.1 (2025/05/22) =
* Fix: Prevent errors when processing snippets by removing an unnecessary argument from the sanitize function.
* Update: Improve the code editor's appearance by adjusting how content overflows, ensuring the border radius displays correctly.

= 0.3.0 (2025/05/01) =
* Add: Introduced API functions to create, update, and delete snippets via PHP Callables.
* Fix: Ensured PHP Callables avoid typed arguments to prevent InvalidArgumentException.
* Update: Refactored ContentBlock to use the CodeEditor component and removed unnecessary ID handling.
* Fix: Sanitized arrays and converted strings to arrays for safer callable argument handling.
* Fix: Corrected a return statement that was blocking the PHP Callable test tab.
* Add: Added a proof of concept for a Gutenberg block.

= 0.2.9 (2025/02/17) =
* Fix: Corrected split function to handle null arguments properly.
* Add: Enabled the "Test" tab for all snippet types for better testing.
* Update: Refactored Quick Start snippet generation and introduced AI-powered snippet creation.
* Update: Removed Shortcode.js and cleaned up debug logs to improve code clarity.
* Update: Improved snippet execution by adding load.php and enhancing error handling.
* 💕 Discuss with others about it on [Discord](https://discord.gg/bHDGh38).
* 🌴 Keep us motivated with [a little review here](https://wordpress.org/support/plugin/code-engine/reviews/). Thank you!

= 0.2.8 (2025/01/04) =
* Update: Refactored core class for better performance.

= 0.2.7 (2024/12/06) =
* Fix: Insertion of the code generated by AI.
* Update: Visual enhancements.
* Update: Clean uninstall.

= 0.2.6 (2024/11/04) =
* Update: Better editor, cleaner and more efficient.
* Add: Little tutorial, to help you get started.
* Fix: Avoid useless re-renders.

= 0.2.5 (2024/10/17) =
* Update: Better code editor. Decoration for tags.
* Update: Added "Function Calling" next to Callable.
* Fix: Whitelist for REST to avoid issues.
* Fix: Many fixed and enhancements for AI Suggestions.
* Fix: Minor issues.

= 0.2.4 =
* Fix: Jumpy cursor and functions not available via API (0.2.3 specific issues).
* Add: Check the function name, and Sanitize.
* Fix: Lot of minor issues.

= 0.2.2 = 
* Update: Better error handling.
* Update: Better logger.

= 0.2.1 =
* Update: Better AI suggestions and improved UI.
* Fix: Avoid the Code Editor to be suddently updated by external factors.

= 0.2.0 =
* Fix: Settings display.
* Update: JS functions are now also available in the admin.

= 0.1.9 =
* Update: A bunch of improvements to make Code Engine even more awesome! Basically, the UI became slighlty better to use, and the features are more stable.

= 0.1.8 (2024/07/16) =
* Update: UI overhaul, with a new design, icons, and more. 
* Add: Types for arguments.
* Fix: Got rid of some warnings and errors.

= 0.1.6 (2024/07/07) =
* Add: Scheduling via WP events.
* Fix: Exclude disabled functions from functions list in the API.
* Update: Disabled Safe Mode for frontend snippets.
* Update: Various fixes and optimizations.

= 0.1.5 (2024/06/23) =
* Update: Lots of little UI improvements.
* Fix: Escape value in sanitize function and correct import snippet primary key and tag validation.
* Fix: Ensure arrays are displayed in test logs and sanitize functions when saving.

= 0.1.4 (2024/06/15) =
* Fix: If functions already exist, to avoid conflicts, the snippet will be disabled.
* Update: Scopes are now Backend, Frontend, Function, Persistent, and Scheduled.
* Update: Improved the styles.

= 0.1.3 =
* Fix: Resolved issue with Common Dashboard visibility when only Code Engine is used.
* Update: Enhanced UI with multiple component refactors and minor changes.
* Fix: Improved API functionality with secure query args decoding and request body support.

= 0.1.2 =
* Update: Disable Safe Mode for Admin (which is enabled by default).
* Update: Improved flow with AI Engine.

= 0.1.1 =
* Add: Import/Export Snippets & Settings.

= 0.1.0 =
* Update: Implemented version control for database updates with optimized queries.
* Update: Simplified database column declarations and improved compatibility by removing default values from 'created' and 'updated' fields.

= 0.0.9 =
* Update: Many enhancements around error handling and UI.

= 0.0.8 =
* Fix: Secondary way of checking the database if needed.
* Fix: Automatically disable snippets if they trigger a fatal error.

= 0.0.7 =
* Fix: Corrected UI behaviors including Test window size and wrapping, default tab selection in Edit modal, and default snippet name setting for Functions.
* Update: Enhanced error handling in "Test" mode to display errors instead of throwing them.
* Update: Eliminated "All" option from the Type Select dropdown.

= 0.0.6 =
* Add: Settings for developers.
* Fix: Minor issues.

= 0.0.5 =
* Update: Arguments with no default value are automatically marked as required.

= 0.0.2 =
* Info: Revamped release.

= 0.0.1 =
* Info: Old release.
