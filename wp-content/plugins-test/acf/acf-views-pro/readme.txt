=== ACF Views - Display ACF fields and Posts using shortcodes ===
Contributors: wplake
Tags: acf, display custom fields, display posts, acf views, shortcode, custom fields in frontend, show acf fields, get field values, get posts
Requires at least: 5.5
Tested up to: 6.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The plugin allows you to display selected ACF fields or Posts anywhere using shortcodes, HTML markup is automatically created by the plugin.

== Description ==

The plugin allows you to display selected ACF fields or Posts anywhere using shortcodes, HTML markup is automatically created by the plugin.

Note: "ACF Views" plugin requires [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) plugin to be enabled on your website (either Free or Pro version).

== 🌟 Display ACF fields and Posts ==

Solve all these scenarios without coding.

* [Display ACF fields on a page](https://docs.acfviews.com/guides/acf-views/basic/display-fields-on-a-single-page)
E.g. add multiple ACF fields to the homepage and display them

* [Display specific posts (built-in or CPT) in a grid or list beautifully](https://docs.acfviews.com/guides/acf-cards/basic/display-multiple-posts-and-their-fields)
E.g. display the latest WooCommerce products with their ACF fields

* [Display specific post or CPT item with its fields](https://docs.acfviews.com/guides/acf-views/basic/display-custom-post-and-its-fields)
E.g. display "sponsor" CPT item on another post/page

* [Display specific ACF fields for all items of a CPT](https://docs.acfviews.com/guides/acf-views/basic/display-fields-for-a-custom-post-type)
E.g. display multiple ACF fields for all WooCommerce products

Nearly all plugins use Custom Post Types (CPT) to store their data. Plugins like WooCommerce call it Products, whatever they're called the ACF Views plugin supports it all.

== 🕗 Less time with less effort ==

Better than coding. See why [here](https://wplake.org/blog/display-acf-fields-beautifully-and-without-coding/).

* Select fields from a list, no need to worry about their names and return types
* Markup auto generated uses BEM method to avoid conflicts
* UI to define selection filters, no need to worry about DB query arguments
* Editing theme templates via FTP is in the past
* CSS and JS code can be added without hassle
* Built-in features, like pagination to save hours of coding

== 💡 How it works ==

View for ACF fields
Create a View and assign one or more custom fields, our plugin then generates a shortcode that you’ll use to display the field values to users. Style the output with the CSS field included in every View.

Card for post selections
Create a Card and assign posts (or CPT items), choose a View (that will be used to display each item) and our plugin generates a shortcode that you’ll use to display the set of posts. The list of posts can be assigned manually or dynamically with filters.

== 📚 Extensive Docs and Friendly Support ==

Our [YouTube channel](https://www.youtube.com/watch?v=Ieu3Mk2Ah0A&list=PL4WYpE2DYvTyzk1DtNhXJ4BXRjtIndht3) showcases the use of our plugin and its features, making it easier for new users to get started. See our [Docs](https://docs.acfviews.com/getting-started/acf-views-plugin-for-wordpress) for step by step guides and for information about customization.
Questions about the Basic ACF Views plugin are handled through the [support forum](https://wordpress.org/support/plugin/acf-views/). Customers with an active Pro license have personal support via our [support form](https://wplake.org/acf-views-support/).

Visit [our website](https://wplake.org/acf-views/) to get more information.

== 📢 Powerful features ==

* Display built-in post fields (like title or thumbnail) along with ACF fields
* Display post A in post B with its fields
* Define custom CSS and JS
* No style conflicts due to BEM method used
* Combine ACF fields from two different field groups
* Restrict visibility to specific user roles

== 🎯 High Performance ==

Every wrapper has some overhead. We do our best to make this number as small as possible. One unique ACF View/Card on a page would only effect this by 0.01 seconds**\*** overhead compared to the usual way with coding. It's impossible to notice these tiny numbers visually without testing it.
**\***More about the test [here](https://docs.acfviews.com/getting-started/performance).

== 🌍 Multilingual ==

ACF Views plugin supports translations.
Check your language status or help by translating ACF Views into your language [here](https://translate.wordpress.org/projects/wp-plugins/acf-views/).

== Video overview ==

https://youtu.be/0Vv23bmYzzo

== Installation ==

**Installation for ACF Views**

From your WordPress dashboard:

1. Visit the Plugins list, click "Add New"
2. Search for "ACF Views"
3. Click "Install" and "Activate" ACF Views
4. Visit the new menu item "ACF Views" to create your first View

See our [plugin documentation](https://docs.acfviews.com/getting-started/acf-views-for-wordpress) for step-by-step tutorials.

**Installation for ACF Views Pro**

To purchase a Pro license key click [here](https://wplake.org/acf-views-pro/).
After payment you'll receive an email with your license key which includes the ACF Views Pro plugin archive.

1. Visit the Plugins list, click "Add New", then click "Upload Plugin"
2. Click on "Choose File" and locate the downloaded ACF Views Pro package, then click "Open"
3. Click on "Install Now" and wait for the package to upload and install, then click "Activate Plugin"
   Note: ACF Views free will automatically be deactivated. You can safely delete ACF Views from the Plugins list. (Don't worry, deleting the ACF Views plugin won't delete your data.)
4. In the Plugins list for ACF Views Pro click "Activate your Pro license"
5. Copy and paste your Pro License Key, then click "Activate"

Enjoy all the features and settings ACF Views Pro has to offer with automatic updates.
Customers with an active Pro license have personal support via our [support form](https://wplake.org/acf-views-support/).

== Frequently Asked Questions ==

= Supported field types =

All field types with values are supported.

Basic group

* Text
* Textarea
* Number
* Range
* Email
* Url
* Password

Content group

* [Image](https://wplake.org/blog/display-acf-image-field-with-shortcode/)
* [File](https://wplake.org/blog/acf-file-field/)
* Wysiwyg
* Oembed
* [Gallery](https://wplake.org/blog/display-acf-gallery-field-with-shortcode/)

Choice group

* Select
* Checkbox
* Radio
* Button Group
* True false

Relational group

* [Link](https://wplake.org/blog/acf-link-field/)
* [Post Object](https://docs.acfviews.com/guides/acf-views/fields/post_object)
* Page Link
* [Relationship](https://docs.acfviews.com/guides/acf-views/fields/relationship)
* Taxonomy
* User

JQuery group

* [Google Map](https://docs.acfviews.com/guides/acf-views/fields/google-map)
* Date Picker
* Date Time Picker
* Time Picker
* Color Picker

Layout group

* [Repeater](https://docs.acfviews.com/guides/acf-views/features/repeater-field-pro) (Pro only)
* [Group](https://docs.acfviews.com/guides/acf-views/fields/group-pro) (Pro only)

= Custom fields not in the list of fields? =

Only fields created using the official Advanced Custom Fields (ACF) plugin are compatible with ACF Views. See list above for supported field types.

= Can I display user fields? =

Yes, you set up your field groups in ACF and assign those fields to your View, paste the shortcode in the target place, add the object-id="$user$" argument to the shortcode to display the fields from the current user. See [here](https://docs.acfviews.com/guides/acf-views/basic/shortcode) for more about shortcode arguments.

= Can I display fields from my options page? =

Yes, you set up your field groups in ACF and assign those fields to your View, paste the shortcode in the target place, add the object-id="options" argument to the shortcode to display the fields from the options page. See [here](https://docs.acfviews.com/guides/acf-views/basic/shortcode) for more about shortcode arguments.

= Fields have been assigned but the page doesn't show them =

Have you checked that the fields are filled in the target object? See [steps](https://docs.acfviews.com/guides/acf-views/basic/creating-an-acf-view) for creating an ACF View.

= Can I display fields inside the Gutenberg Query Loop? =

You can use the View shortcode inside the Gutenberg Query Loop element. Please make sure you've added it via the built-in Shortcode block, as it won't work properly with other block types, like Code or Custom HTML.

== Changelog ==

= 1.9.2 (2023-05-20): =
- Date fields within group/repeater: fixed bug

= 1.9.1 (2023-05-19): =
- New $Post$ field: 'Featured image with link'

= 1.9.0 (2023-05-17): =
- Labels, descriptions: added support of multilingual
- Date/time fields: added support of multilingual
- Updated ACF api (acf-groups package version)
- UX: improved labels

= 1.8.9 (2023-04-28): =
- Card: meta filter - fixed '$post$.field' for the object return format
- License: added expires notification
- Updated readme
- UX: labels

= 1.8.8 (2023-04-21): =
- View: fixed plugin's $Taxonomy$ field group, added the delimiter support to it
- Card: Taxonomy filter - added a new '$current$' option
- Card: Meta filter - added an option to get field value dynamically ('$post$.field-name')
- Card: Meta filter - improved support of 'post_object' and 'relationship' fields

= 1.8.7 (2023-04-20): =
- Updated readme
- View: fixed taxonomy bug (didn't support 'appearance options' with the Single values)
- View: added the new 'delimiter' option for multiselect fields ('select', 'post_object', 'page_link', 'relationship', 'taxonomy', 'user',)
- Improved PHP 8 support
- Fixed the update available message style (when both 'update available' and 'no license' showed at the same time)
- Improved the Basic/Pro switcher process
- Improved UX (labels)
- Improved Pro page (info about Dev domains)

= 1.8.6 (2023-03-16): =
- View: added support of the 'Group' field type
- View: added a workaround for the Gutenberg Query Loop

= 1.8.5 (2023-02-18): =
- Fixed skipping Fields Groups from JSON only
- Fixed relationship field shortcode (was only printed)
- Improved the Map field support
- Updated Readme (Installation tab)

= 1.8.4 (2023-02-01): =
- Improved support of block themes (CSS loading)
- Updated Readme

= 1.8.3 (2023-01-31): =
- Improved Textarea support (auto converting '\n' to 'br')
- Fixed a Lightbox gap (from the plus svg at the bottom)
- Updated Overview page
- Updated Readme

= 1.8.2 (2023-01-18): =
- Shortcode 'object-id' argument: added support for the '$user$' value
- Updated contact links

= 1.8.1 (2023-01-16): =
- Fixed a syntax error and improved support of multilingual websites ('trash' option is missing in CPT for them)

= 1.8.0 (2023-01-13): =
- Improved CSS including: moved to the head tag
- Improved saving process (JSON, excluded default values)
- Improved UX (opcache conflict message)
- Updated Docs link
- Improved Analytics
- Improved Conditional rules for Repeater fields

= 1.7.23 (2023-01-04): =
- Added a notice for admins (about opcache compatibility issue)
- Minor improvements

= 1.7.22 (2023-01-02): =
- Fixed unnecessary output

= 1.7.21 (2023-01-02): =
- View: Improved 'relationship' and 'post_object' support
- Card: Improved meta filters ($post$ variable)
- UX: Added support email

= 1.7.20 (2022-12-16): =
- Added 'options' support to the 'object-id' argument
- Updated readme
- Updated Overview page
- Added a survey link
- Updated "Unavailable Automatic Updates Message"

= 1.7.19 (2022-11-23): =
- Updated YouTube video link

= 1.7.18 (2022-11-22): =
- Improved dashboard links (supporting of custom site urls, like wp.org/wordpress)

= 1.7.17 (2022-11-22): =
- Improved UX (labels)
- Improved dashboard links (supporting of custom site urls, like wp.org/wordpress)

= 1.7.16 (2022-11-15): =
- Improved WooCommerce supporting (product loops)
- Updated Readme, Overview page

= 1.7.15 (2022-11-08): =
- Improved updater
- Improved UX (read more links)

= 1.7.14 (2022-11-04): =
- View : improved author, image field types support
- View : added taxonomies support

= 1.7.13 (2022-11-03): =
- Fixed bug with missing fields

= 1.7.12 (2022-11-02): =
- Improved code : no PHP warnings on the ACF options page
- Readme

= 1.7.11 (2022-11-01): =
- View : supporting of the google map field
- UX : links to Docs

= 1.7.10 (2022-10-28): =
- Bug fixed : automatic deactivation on activation of some plugins
- UX improvement : removed automatic redirection to the Overview page
- More supported field types : oembed, gallery, button_group, post_object, relationship, taxonomy, user

= 1.7.0 (2022-10-27): =
- View, Card : MountPoints feature
- View, Card : improved CSS shortcuts

= 1.6.17 (2022-10-24): =
- Updated readme
- Updated field labels

= 1.6.13 (2022-10-21): =
- Performance : improved caching

= 1.6.12 (2022-10-21): =
- Copy to clipboard : improved working on HTTP protocol, fixed the roles shortcode copying

= 1.6.11 (2022-10-21): =
- Copy to clipboard : improved working on HTTP protocol

= 1.6.10 (2022-10-21): =
- Demo import : fixed a bug
- Gutenberg block feature : improved notice
- Improvement : removed double slashing for View/Card fields in DB

= 1.6.0 (2022-10-21): =
- Performance improving : View/Card settings now in JSON from post_content instead of using postMeta
- Gutenberg block feature : fixed a bug

= 1.5.10 (2022-10-17): =
- Card Shortcodes postbox : fixed wrong argument name
- ACF dependency : improved links (to the local add-plugin page)
- Improved redirection after activation (to use TRANSIENTS)
- Automatic deactivation one of instances when both Basic & PRO activated
- Added information about restricting access to View/Card by user roles
- Added escaping output of plain field types
- Improved import

= 1.5.0 (2022-10-13): =
- Downgraded ACF dependency from PRO to Basic
- New shortcode arguments : user-with-roles, user-without-roles
- Fixed ImageSize for repeater fields

= 1.4.10 (2022-10-12): =
- View : preview feature
- Card : preview feature, custom variables filter
- Improved 'ACF PRO' dependency notice

= 1.4.0 (2022-10-10): =
- View : reordered fields (new tab)
- View : image size field : dynamic list instead of hard coded, $Post$ thumbnail support
- View : Custom Markup Variables feature

= 1.3.1 (2022-10-04): =
- View : improved Gutenberg block description
- Toolbar improved
- Code structure improved
- Filters added

= 1.3.0 (2022-09-30): =
- Backend optimization
- Card : fixed CSS classes field
- Card : new tab - "Layout"
- View : improved UX (field settings is displayed only for specific field types)
- View&Card : disabled autocomplete

= 1.2.1 (2022-09-28): =
- Overview page content
- Demo imported error fixed
- Demo import improved (added ACF Card)

= 1.2.0 (2022-09-27): =
- Card markup preview & custom markup fields
- Card no posts found message
- Admin Table bug fixed (select all)
- clone item bug fixed

= 1.1.1 (2022-09-25): =
- Meta, tax, pagination features for ACF Cards
- Markup improvements

= 1.1.0 (2022-09-20): =
- Markup improvements

= 1.0.9 (2022-09-20): =
- Minor improvements

= 1.0.8 (2022-09-19): =
- ACF Cards

= 1.0.7 (2022-09-09): =
- Readme, assets

= 1.0.6 (2022-09-09): =
- Updated support links

= 1.0.5 (2022-09-09): =
- Minor improvements, readme

= 1.0.4 (2022-09-01): =
- Improved code editor

= 1.0.3 (2022-08-31): =
- Bug fixing (customMarkup on save)

= 1.0.2 (2022-08-30): =
- Features : Gutenberg support
- Link and Page_link field types

= 1.0.1 (2022-07-30): =
- Code improving

= 1.0.0 (2022-07-07): =
- Features : repeater, custom markup, custom JS
