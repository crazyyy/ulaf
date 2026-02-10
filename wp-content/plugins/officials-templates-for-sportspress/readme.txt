=== Officials Templates for SportsPress ===
Contributors: savvasha
Donate link: https://bit.ly/3NLUtMh
Tags: sportspress, officials, templates, profile, sports 
Requires at least: 5.3
Requires PHP: 7.4
Tested up to: 6.8
Stable tag: 1.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin enhances the Official profile on SportsPress by adding custom template functions.

== Description ==

Officials Templates for SportsPress is a WordPress plugin that enhances the Official profile on SportsPress by adding custom template functions. These functions allow you to easily display and customize official's details on your sports website.

You can add your own detail info by using the following hooks:
1. Actions
* `otfs_meta_box_officials_details`: To add the custom detail field to the Details Meta-Box
* `otfs_meta_box_officials_save` : To save the custom detail field.
2. Filters
* `otfs_officials_details`: To show the custom detail field at the frontend.

== Features ==

- **Custom Template Functions:** Introduces a set of template functions for displaying officials' information.
- **Enhanced Officials Management:** Adds the ability to show several details of an Official (i.e. Nationality, Age etc)
- **Responsive Design:** Ensures officials' templates look great on all devices.
- **Easy Integration:** Seamlessly integrates with SportsPress for straightforward implementation.

== Recommended Plugin: ==
If you're looking for advanced reporting features and additional functionalities, we recommend checking out our premium plugin: [Officials Report for SportsPress](https://savvasha.com/officials-report-for-sportspress/).

Our premium plugin introduces a comprehensive Officials Report feature, which allows you to easily track and display detailed data related to match officials. Whether you prefer using the WordPress block editor or shortcodes, the Officials Report offers seamless integration in both modes. This flexibility ensures that you can place and customize reports in any part of your website, making it incredibly convenient for users who manage their content in different ways.

- **Block Mode**: Effortlessly add and customize official reports using the WordPress block editor, taking advantage of real-time previews and intuitive controls.
- **Shortcode Mode**: Prefer shortcodes? No problem! Use simple shortcode to embed detailed official reports anywhere on your site, from posts and pages to custom widgets.

[Check out the premium version to bring more detailed insights and flexibility to your SportsPress setup](https://savvasha.com/officials-report-for-sportspress/)!

== Installation ==

1. **Download the Plugin:**
   - Download from the WordPress Plugin Repository.

2. **Upload to WordPress:**
   - Upload the plugin to your WordPress installation under 'Plugins' > 'Add New.'

3. **Activate the Plugin:**
   - Activate Officials Templates for SportsPress in the 'Plugins' menu in WordPress.

4. **Configuration:**
   - Go to `SportsPress->Settings->Officials` to configure what and how will be displayed at your Official's profile.

== Usage ==

Once activated, the plugin automatically enables an Officials Settings tab at `SportsPress->Settings->Officials`. There you can enable/disable template display, change the order and select which details you want to display for your Officials.

== Screenshots ==

1. Template Layout and Tabs options in Officials Settings page.
2. More display options in Officials Settings page.
3. An example of enhanced Officials backend page.
4. An example of enhanced Officials frontend page.

== Changelog ==

= 1.7 =
* Fix: Undefined variable $otfs_duties.

= 1.6 =
* New: Added a dismissible admin notice to inform users about the premium plugin "Officials Report for SportsPress."
* Improvement: Admin notice now includes a call-to-action button for a clearer promotion experience.
* Enhancement: Notice dismissal resets automatically on plugin version updates.
* Code: Updated code to fully comply with WordPress Coding Standards.

= 1.5 =
* FIX: The officials were not linked in the match/event page.

= 1.4 =
* NEW: Add hooks to the "Details" meta-box. See description for more info.

= 1.3 =
* NEW: Select for which duties you want to show stats and info.

= 1.2 =
* TWEAK: Support more serialized string schemas.

= 1.1 =
* FIX: Template togle buttons are not working.

= 1.0 =
* Initial release.

== Frequently Asked Questions ==

= Why my Official profile shows no stats? =
Make sure you assigned the corresponding Leagues and Seasons to your Official profile.

= I am not able to add any official. What should I do? =
Make sure you have enabled the `Officials` module of SportsPress at `SportsPress->Settings->Modules->Players & Staff`.

== Support ==

For support or inquiries, visit [plugin support forum](https://wordpress.org/support/plugin/officials-templates-for-sportspress/).

== Donate ==

If you find this plugin helpful, consider [making a donation](https://bit.ly/3NLUtMh) to support further development and maintenance.

== Credits ==

This plugin was developed by Savvas. Visit [author's website](https://savvasha.com) for more information.

== License ==

This plugin is licensed under the GPL v2 or later. See [License](https://www.gnu.org/licenses/gpl.html) for more details.