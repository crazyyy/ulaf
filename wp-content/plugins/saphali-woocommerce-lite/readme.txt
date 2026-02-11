=== Saphali Woocommerce Lite ===
Contributors: Saphali
Plugin Name: Saphali Woocommerce Lite
Plugin URI: http://saphali.com/saphali-woocommerce-plugin-wordpress
Donate link: https://saphali.com/donations
Tags: woocommerce, woo commerce Lite, russian ruble, ukrainian hryvnia, manager fields checkout
Requires at least: 4.5
Tested up to: 6.8
Stable tag: 2.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A set of additions to the WooCommerce online store.
Adds localization & special tools in WooCommerce.

== Description ==

WooCommerce Lite!
Functional additions for WooCommerce - specifically for managing fields on the checkout page.

= Integrates into the store: =
* Ability to customize the order form for checkout
* Manage the number of columns in the catalog

= Features =
* From WooCommerce version 8.3, fields are used as blocks by default (i.e., fields are configured as if editing the Checkout page itself). The plugin has been tested for compatibility with Checkout Blocks from WooCommerce 8.5.0 and above. If you have a lower version and the functionality in the block checkout does not work, to use the capabilities of this plugin, <strong>you just need to switch to Classic Shortcode</strong> when editing the <em>Checkout</em> page. If necessary, you can always convert back to blocks.
* Added to the general list of currencies — Ukrainian Hryvnia (UAH), Russian Ruble (RUB), Belarusian Ruble (BYN), Armenian Dram (AMD), Kyrgyz Som (KGS), Kazakhstani Tenge (KZT), Uzbekistani Som (UZS), Lithuanian Litas (LTL)
* Manage fields on the checkout page and profile page. The function allows you to customize the order form for checkout. You can make some fields optional at checkout or remove them completely, thereby simplifying the checkout process. You can also add your own special fields.
* Manage the number of columns in the product catalog (Shop) and categories.
* The plugin is designed to simplify the development of online stores.
* The plugin does not change the original WooCommerce files, you can update the WooCommerce plugin every time new versions are released!
* Mapping fields to payment and shipping methods, e.g., it will be useful if some fields do not need to be displayed when paying with a certain shipping and/or payment method, but are needed with another shipping or payment method.

<strong>ATTENTION!</strong>

More about payment gateway plugins: https://saphali.com/wordpress/payment-gateways , https://saphali.com/en/wordpress/payment-gateways

See other plugins for WooCommerce online stores in our catalog https://saphali.com/wordpress/woocommerce-plugins

== Installation ==

1. Unzip the contents of the zip file into your site's plugins folder (wp-content/plugins/), using your favorite FTP program.
2. Activate the plugin on the "Plugins" page in the admin panel.
3. All installation is complete.

== Frequently Asked Questions ==

= Error "Please enter an address to continue" =
In case of an error (in WC > 3.4) "<em>Please enter an address to continue</em>", check if the <strong>billing_country</strong> field is deleted or disabled. If there is shipping, without a location, WC does not allow placing an order. Shipping zones work by determining the country first (depending on the settings, geolocation, or store address), if this is not possible because there is no corresponding field, this notification is displayed.

== Screenshots ==

1. Ability to display a field depending on the shipping or payment method.
2. Manage fields on the order page and profile page. The function allows you to customize the registration form to simplify the checkout process. You can make some fields optional during registration/checkout or remove them completely.
3. Manage fields (move to the desired position).
4. How it looks on the page.
5. Manage the number of columns in the product catalog and categories.
6. Switch to Classic Shortcode when editing the Checkout page.

== Changelog ==

= 2.0.1 =
* Localization issue

= 2.0.0 =
* Support for Checkout Blocks.
* Code optimization.

= 1.9.3 =
* Tweak - WC 8.3 compatibility.
* Tweak - WP 6.4 compatibility.

= 1.9.2 =
* Fixed notice.

= 1.9.1 =
* Fix login error.

= 1.9.0 =
* Fix nonce check.

= 1.8.13 =
* Added filter for processing fields of type select, radio.
* Fix for mandatory field in shipping fields when such fields are absent in payment fields.

= 1.8.12 =
* Fix error handling.

= 1.8.11 =
* Fix error handling.

= 1.8.10 =
* Bug fixes.

= 1.8.9 =
* Change condition for displaying shipping methods in settings.

= 1.8.8 =
* Fix symbol in WC 4 reports.

= 1.8.7 =
* Minor bugs. Code optimization.

= 1.8.6 =
* Fix display of fields added by the plugin in emails for WC 3.x.

= 1.8.5 =
* Fix when working with the bookly-addon-pro plugin.

= 1.8.4.1 =
* Fix display of Notice.

= 1.8.4 =
* Fix related to displaying duplicate field values in the admin panel.

= 1.8.3 =
* Minor fixes.

= 1.8.2 =
* Fix field positions on the order page in WC > 3.0.
* Minor fixes.

= 1.8.1.1 =
* Minor fixes (Notice).

= 1.8.1 =
* Minor fixes.

= 1.8.0 =
* Added functionality to map fields to shipping methods. For example, it will be useful if some fields do not need to be displayed when paying with a certain shipping method, but are needed with another shipping method.

= 1.7.1 =
* In WC 3.x.x version, display of added fields that are not present by default in WC.

= 1.7.0 =
* Added functionality to map fields to payment methods. For example, it will be useful if some fields do not need to be displayed when paying with a certain payment method, but are needed when paying with another payment method.

= 1.6.6 =
* Fixed publication accounting in plugin settings in WC 3.x.x version.

= 1.6.5 =
* Fix display of fields in the profile (fixed duplicate display).

= 1.6.4 =
* Compatibility with WPML and other multilingual plugins. [For reference. Translation of fields in localization files must be provided by you (Text Domain: woocommerce). This plugin does not contain field translations, as it is impossible to predict them (you set their names in the settings, so you also need to translate them). If you do not change the field names, they should be in English, so as not to prepare a new translation into other languages].

= 1.6.3 =
* Implemented the ability for the administrator to edit the entered data of additional fields in the order.
* Updated style for the Russian ruble.
* Added Ukrainian localization, which has a more complete translation into Ukrainian compared to the main WC release translation.

= 1.6.2 =
* Changes related to translation for WC above 2.5.0.

= 1.6.1 =
* Added currency - new Belarusian ruble (BYN).

= 1.6.0 =
* Due to frequent requests that the ruble sign is not displayed correctly in WC 2.5.2 and above, the ruble sign was returned as it was. For those who have WC 2.5.2 and above, and who want to use the sign specified in WC on their site, replace in the saphali-woocommerce-lite.php file:
from
define('SAPHALI_LITE_SYMBOL', 1 );
to
define('SAPHALI_LITE_SYMBOL', 0 );

= 1.5.9 =
* For WC 2.5.2, the ruble sign provided in WC is used.

= 1.5.8 =
* Fix in WC 2.4.x.

= 1.5.7 =
* Added column count filter.

= 1.5.6 =
* In WooCommerce 2.2 and above, additional localization for WC is disabled, as it is no longer needed.

= 1.5.5 =
* Changes due to the release of WooCommerce 2.3.

= 1.5.4 =
* Fixed display of additional fields for WC 2.2.0 - 2.2.2 versions.

= 1.5.3 =
* Display of additional fields in the profile/account of the buyer.

= 1.5.2 =
* Changes due to the release of WooCommerce 2.2.

= 1.5.1 =
* Added Lithuanian Litas.

= 1.5 =
* Changes due to the release of WooCommerce 2.1.0.

= 1.4 =
* In field management, the ability to use various field types (select, checkbox, textarea) has been implemented.

= 1.3.8.1 =
* Fields with empty titles are not displayed in the email, in orders.

= 1.3.8 =
* Added Uzbekistani Som.

= 1.3.7.2 =
* Minor fixes related to the RUB currency symbol.

= 1.3.7.1 =
* Changes related to WPML compatibility.

= 1.3.7 =
* Added filter for displaying default fields. The absence of this filter led to fields being pulled by JavaScript not as needed (the field name and its mandatory status were displayed as default in WC).

= 1.3.6.2 =
* Increased priority of the currency symbol by Lebedev (for the ruble) over the built-in one.

= 1.3.6.1 =
* Fixed display of additional fields filled in by the buyer (in the email and in the order).

= 1.3.6 =
* Fixed the issue of not saving some field attributes (e.g., the "Company Name" field can now be marked as mandatory, default text can be specified).
* Added custom fields can now be sorted along with standard fields (eliminated the disjointed sorting of fields that are by default and those added as needed).
* Fixed non-critical errors.

= 1.3.5 =
* Added function to display additional fields in emails and when viewing the order by the buyer.

= 1.3.4 =
* Added currencies: Kyrgyz Som (KGS) and Kazakhstani Tenge (KZT).

= 1.3.3 =
* Minor localization fixes for WooCommerce.
* Added support for the Russian ruble currency symbol.

= 1.3.2.1 =
* Minor localization fixes for WooCommerce.

= 1.3.2 =
* Minor localization fixes for WooCommerce.

= 1.3.1 =
* Completion of localization for WooCommerce 2.0.

= 1.3.0 =
* Expansion of localization for WooCommerce.
* Adaptation for WooCommerce 2.0.0.
* Rewritten code, as on servers where PHP did not understand the delimiters <? and <?= it gave the error "Parse error: syntax error, unexpected...".

= 1.2.3.2 =
* Expansion of localization for WooCommerce.

= 1.2.3.1 =
* Repository upload error.

= 1.2.3 =
* On the page for managing additional fields and shipping fields, the error of adding fields has been fixed.
* Added display of additional fields on the user profile page, as well as on the order editing page.

= 1.2.2 =
* On the page for managing additional fields and shipping fields, a hint has been added to the "Name" and "Field Class" attributes in the header.
* Style set for inactive field.
* Minor fixes.

= 1.2.1 =
* On the page for managing additional fields and shipping fields, a hint has been added to the clear attribute in the header.

= 1.2 =
* Added management of clear and class attributes in field management.
* Fixed the issue of specifying a mandatory field in managing additional fields and shipping fields.

= 1.1 =
* Fixed the issue of managing fields on the order page and profile page.

= 1.0 =
* Improved Russian localization of WooCommerce (translation correction and additional translation).
* Added to the general list of currencies — Ukrainian Hryvnia (UAH), Russian Ruble (RUB), and Belarusian Ruble (BYN).
* Manage fields on the order page and profile page. The function allows you to customize the registration form to simplify the checkout process. You can make some fields optional during registration/checkout or remove them completely.
* Manage the number of columns in the product catalog and categories.

== License ==

This plugin is free for everyone as it is released under the GPL. You can use it for free in your online stores. But if you like this plugin, you can thank us by sharing the link to our website with your friends and colleagues.

== Translations ==

We assume that the Russian localization for WooCommerce can be further improved. If you notice an incorrect translation in the WooCommerce settings or it can be replaced with an alternative and more suitable one, here - https://translate.wordpress.org/projects/wp-plugins/woocommerce/stable/ru/default

== Demo ==
You can check this plugin in action on our website http://saphali.com/ - on the "Demo Online Store" page.

== Upgrade Notice ==
= 1.6.0 =
* Due to frequent requests that the ruble sign is not displayed correctly in WC 2.5.2 and above, the ruble sign was returned as it was. For those who have WC 2.5.2 and above, and who want to use the sign specified in WC on their site, replace in the saphali-woocommerce-lite.php file:
from
define('SAPHALI_LITE_SYMBOL', 1 );
to
define('SAPHALI_LITE_SYMBOL', 0 );

= 1.5.9 =
* For WC 2.5.2, the ruble sign provided in WC is used.

= 1.4 =
* In field management, the ability to use various field types (select, checkbox, textarea) has been implemented.

= 1.3.8.1 =
* Fields with empty titles are not displayed in the email, in orders.

= 1.3.8 =
* Added Uzbekistani Som.

= 1.3.7.2 =
* Minor fixes related to the RUB currency symbol.

= 1.3.7.1 =
* Changes related to WPML compatibility.

= 1.3.7 =
* Added filter for displaying default fields. The absence of this filter led to fields being pulled by JavaScript not as needed (the field name and its mandatory status were displayed as default in WC).

= 1.3.6.2 =
* Increased priority of the currency symbol by Lebedev (for the ruble) over the built-in one.

= 1.3.6.1 =
* Fixed display of additional fields filled in by the buyer (in the email and in the order).

= 1.3.6 =
* Fixed the issue of not saving some field attributes (e.g., the "Company Name" field can now be marked as mandatory, default text can be specified).
* Added custom fields can now be sorted along with standard fields (eliminated the disjointed sorting of fields that are by default and those added as needed).
* Fixed non-critical errors.

= 1.3.2.1 =
* Minor localization fixes for WooCommerce.

= 1.3.2 =
* Minor localization fixes for WooCommerce.

= 1.3.1 =
* Completion of localization for WooCommerce 2.0.

= 1.3.0 =
* Expansion of localization for WooCommerce.
* Adaptation for WooCommerce 2.0.0.
* Rewritten code, as on servers where PHP did not understand the delimiters <? and <?= it gave the error "Parse error: syntax error, unexpected...".

= 1.2.3.2 =
* Expansion of localization for WooCommerce.

= 1.2.3.1 =
* Repository upload error.

= 1.2.3 =
* On the page for managing additional fields and shipping fields, the error of adding fields has been fixed.
* Added display of additional fields on the user profile page, as well as on the order editing page.

= 1.2.2 =
* On the page for managing additional fields and shipping fields, a hint has been added to the "Name" and "Field Class" attributes in the header.
* Style set for inactive field.
* Minor fixes.

= 1.2.1 =
* On the page for managing additional fields and shipping fields, a hint has been added to the clear attribute in the header.

= 1.2 =
* Added management of clear and class attributes in field management.
* Fixed the issue of specifying a mandatory field in managing additional fields and shipping fields.

= 1.1 =
* Fixed the issue of managing fields on the order page and profile page.

= 1.0 =
* Improved Russian localization of WooCommerce (translation correction and additional translation).
* Added to the general list of currencies — Ukrainian Hryvnia (UAH), Russian Ruble (RUB), and Belarusian Ruble (BYN).
* Manage fields on the order page and profile page. The function allows you to customize the registration form to simplify the checkout process. You can make some fields optional during registration/checkout or remove them completely.
* Manage the number of columns in the product catalog and categories.
