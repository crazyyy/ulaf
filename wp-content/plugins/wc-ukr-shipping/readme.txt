=== WC Ukraine Shipping - Integration of Nova Poshta and Ukrposhta for WooCommerce ===
Contributors: kirillbdev
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Tags: нова пошта, укрпошта, rozetka delivery, nova post, shipping
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 1.21.1

Connect Nova Poshta, Ukrposhta, Meest or international delivery services with your store. Create labels, track orders and calculate rates in one place.

== Description ==

Connect Nova Poshta, Ukrposhta, Meest, Nova Global and many other delivery services with your store. Create labels, track orders and calculate rates in one place.

[Documentation](https://smartyparcel.com/docs/knowledge-base-woocommerce/)
[Product Overview](https://smartyparcel.com/?utm_source=wporg)

== Installation and setup tutorial ==

https://www.youtube.com/watch?v=NYKgP3cw1WY

== Features ==

* Simple and intuitive setup
* Ability to select Nova Poshta warehouse, doors or poshtomat on checkout page
* Ability to separate delivery types by different shipping methods (ex. create "to warehouse" and "to doors" as separated shipping methods)
* Ability to select Ukrposhta warehouse on the checkout page
* Ability to select Rozetka Delivery warehouse on the checkout page
* Ability to select Nova Post (Europe) warehouse on the checkout page
* Ability to select Meest Post warehouse on the checkout page
* Ability to set up fixed shipping cost
* Ability to calculate cost without adding it to order total
* Ability to create Nova Poshta TTN (warehouse-warehouse, warehouse-doors, warehouse-poshtomat)
* Ability to print Nova Poshta labels (A4, marking 85x85, marking 100x100 zebra)
* Ability to create Ukrposhta TTN (warehouse-warehouse)
* Ability to print Ukrposhta labels (100x100, 100x100 (A4), 100x100 (A5))
* Shipments tracking
* Bulk label creation
* Bulk label printing
* Support many functions includes COD and Payment control
* Advanced shipment analytics
* Integration with popular plugins for localization: WPML and Polylang

== Carriers ==
* Nova Poshta
* Ukrposhta
* Rozetka Delivery
* Nova Post (EU, International)
* Nova Global
* Meest (Ukraine, International)
* DHL API (tracking only yet)

== Pickup points (SmartyParcel Locator for WooCommerce) ==
* Nova Poshta
* Ukrposhta
* Rozetka Delivery
* Nova Post (EU, International)

SmartyParcel **guarantees** access to its Locator API for all WooCommerce stores for free!

== Premium features ==

SmartyParcel has additional premium features that help you to optimize your daily shipping routine.

* More shipment limits
* More carrier account limits
* Display real-time carrier rates in checkout (Smarty Parcel Rates API)
* Branded tracking page
* Shipping costs calculation based on order total
* Shipping costs calculation based on shipment weight
* Automatic Email notifications
* Automatic SMS notifications
* Easy integration with custom platforms via our REST Api
* White Label Integration for Enterprises
* Premium support

[Switch to Premium Plans](https://smartyparcel.com/?utm_source=wporg#pricing)

== Installation ==

= Minimum Requirements =

* PHP 7.4 or greater is recommended
* MySQL 5.7 or greater is recommended

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of plugin, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “WC Ukr Shipping” and click Search Plugins. Once you’ve found it you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading this plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains instructions on how to do this here.

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== External services ==

This plugin uses SmartyParcel API to provide advanced logistic functions (like create labels, tracking etc.) and also external API to collect user feedbacks ([Privacy Policy](https://smartyparcel.com/privacy/)).

== FAQ ==

= Does plugin supports WooCommerce checkout blocks? =

Unfortunately plugin doesn't support checkout blocks yet.

== Changelog ==

= Version 1.21.1 / (29.01.2026) =
* UI / UX upgrade.

= Version 1.21.0 / (23.01.2026) =
* [Meest] Added full flow integration (creating labels, tracking).
* [Rozetka Delivery] Old label form was replaced with SmartyParcel Elements.
* [Ukrposhta] Added UkrPoshta Address shipping method.
* Added request caching for SmartyParcel Rates API.
* [Nova Poshta] Added address notes field when creating shipment to address.

= Version 1.20.1 / (27.12.2025) =
* [Meest] Fixed search warehouses error when SmartyParcel Locator is enabled.

= Version 1.20.0 / (27.12.2025) =
* Added Meest Post warehouse shipping method.
* Added Meest Post address shipping method.
* [Ukrposhta] Migrated shipping options to the shipping method's settings page.
* [Rozetka Delivery] Migrated shipping options to the shipping method's settings page.
* Fixed warehouse clear bug for Nova Poshta in checkout.
* Improved weight and dimensions conversion when calculating rates.

= Version 1.19.0 / (17.12.2025) =
* Added currency conversion option. If enabled, the SmartyParcel Rates API will convert shipping costs to store's selected currency.
* [Nova Post] Migrated shipping options to shipping method's settings page.
* [Nova Post] Implemented supporting of SmartyParcel Rates API.
* [Nova Post] Added ability to set up "Free shipping" rule, based on order total.
* [Nova Global] Implemented supporting of SmartyParcel Rates API.
* [Nova Global] Added ability to set up "Free shipping" rule, based on order total.

= Version 1.18.9 / (12.12.2025) =
* [Checkout] Improved state pre-initialization.

= Version 1.18.7 / (12.12.2025) =
* [Checkout] Added state pre-initialization before mounting component.
* Replaced shipping method detection function on shipping calculation to support backward compatibility.

= Version 1.18.6 / (11.12.2025) =
* Removed strict checking of delivery type at shipping cost calculation.
* Added more option defaults.

= Version 1.18.5 / (11.12.2025) =
* Hotfix: Shipping recalculation when changing delivery type.

= Version 1.18.4 / (11.12.2025) =
* [Nova Poshta] Migrated other shipping options to shipping method's settings page.
* [Nova Poshta] Fixed bug with total calculation for address delivery.
* Checked compatibility with latest WordPress and WooCommerce versions.

= Version 1.18.3 / (03.12.2025) =
* [Nova Poshta] Removed legacy translates option group.
* [Nova Poshta] Migrated delivery cost options to shipping method's settings page. We will continue migrating other options that are directly related to shipping to the shipping method settings in the future.
* Checked compatibility with latest WordPress and WooCommerce versions.

= Version 1.18.2 / (25.11.2025) =
* Restored ability to update order shipping address in admin (Nova Poshta only yet).
* Fixed fatal error on shipment creation form for orders without shipping method.

= Version 1.18.1 / (19.11.2025) =
* [Ukrposhta] Added bulk label printing.
* [Automation] Added order note type option.
* [Orders] Added filter by carrier (only for created shipments).
* Fixed issue with SmartyParcel Elements and Redis Object Cache plugin (negative lifetime of transient option).

= Version 1.18.0 / (17.11.2025) =
* Added integration with Nova Global (labels, tracking, address delivery).
* [Nova Poshta] Restored ability to create shipments shipped to companies.
* Switched some plugin widgets in admin panel to SmartyParcel Elements.
* Checked compatibility with latest WordPress and WooCommerce versions.

= Version 1.17.8 / (11.11.2025) =
* [Nova Poshta] Added new option "Global params as default".

= Version 1.17.7 / (06.11.2025) =
* Fixed error in batch modal for fresh labels.
* Improved UX in SmartyParcel onboarding element.

= Version 1.17.5 / (06.11.2025) =
* Implemented labels batches feature: ability to print multiple labels at a time (only for Nova Poshta yet).
* Added option to use Nova Poshta online directory API for search settlements.
* [PUDO] Added pre-query filters (to override query string before search pickup points and settlements).
* [Ukrposhta] Fixed internal error after creating labels without estimated delivery date.
* [Checkout] Improved validation error messages for Nova Poshta.

= Version 1.17.4 / (14.10.2025) =
* Added the ability to manually control the SmartyParcel Locator feature.

= Version 1.17.3 / (13.10.2025) =
* Improved usage strategy for SmartyParcel Locator API.

= Version 1.17.2 / (11.10.2025) =
* Now plugin supports SmartyParcel Locator - unified API for search pickup points across different carriers.
* [Automation] Added shortcode for carrier estimated delivery.
* Tracking is now an integral part of the shipment creation process.
* Added ability to attach exist shipping label for many orders.
* [Checkout] Fixed load more option issue.
* Fixed conflict with other plugins that used vue-router.
* Fixed several issues with additional slashes on label creation.

= Version 1.17.1 / (30.09.2025) =
* [Checkout] Improved logic and usability of fields.
* Checked compatibility with latest WordPress and WooCommerce versions.

= Version 1.17.0 / (12.09.2025) =
* [SmartyParcel] Added integration with Rozetka Delivery (labels, stickers).
* [SmartyParcel] Improved dashboard UI.
* Added ability to print labels from orders management pge.
* Improved translates at checkout page when "Combine poshtomats" option is active.
* Added compatibility with Divi at checkout page.

= Version 1.16.5 / (19.08.2025) =
* Fixed fatal error on plugin setting page when store not connected to SmartyParcel.

= Version 1.16.4 / (19.08.2025) =
* [SmartyParcel] Improved connection flow and UI/UX. Added more analytics.
* Checked compatibility with latest WordPress and WooCommerce versions.

= Version 1.16.3 / (11.08.2025) =
* Added new option "Combine poshtomats and warehouses" for Nova Poshta.
* Added ability to change shipping method name for Ukrposhta and NovaPost (EU).
* Fixed blank screen when edit checkout in some cases.
* Checked compatibility with latest WordPress and WooCommerce versions.

= Version 1.16.2 / (08.07.2025) =
* Code quality improvements.

= Version 1.16.1 / (02.07.2025) =
* Fixed PHP 8.2 deprecation notice.
* Code quality improvements.

= Version 1.16.0 / (01.07.2025) =
* [New] Rozetka Delivery shipping method.
* [New] Nova Post (EU) shipping method.
* Added ability to attach exist label to order.
* Added displaying tracking number at my account page (orders).
* [Fixed] Ukrposhta batch label creation.
* Carrier account management was removed from plugin and moved to cloud account panel.
* Checked compatibility with latest WordPress and WooCommerce versions.

= Version 1.15.11 / (19.06.2025) =
* [Ukrposhta] Fixed sending incorrect service type.

= Version 1.15.10 / (19.06.2025) =
* Fixed translates for middle name field.
* Added escaping in translates section.

= Version 1.15.9 / (18.06.2025) =
* [Ukrposhta] Removed trailing slash when search for some settlements.
* [Ukrposhta] Added COD payer, added ability to set up default service options.
* Added option for inject additional fields in checkout.
* Restored wcus_ttn_form_additional filter.
* Checked compatibility with latest Wordpress and WooCommerce versions.

= Version 1.15.8 / (13.06.2025) =
* [Hotfix] Empty zones behavior
* Added CSS versioning for checkout.

= Version 1.15.6 / (13.06.2025) =
* [Hotfix] Array unique case in checkout.

= Version 1.15.5 / (12.06.2025) =
* [Nova Poshta] Added ability to create label with global params.
* [Nova Poshta] Added usage of default description option on bulk label creation.
* [Nova Poshta] Fixed label creation for poshtomats on bulk label creation.
* [Ukrposhta] Fixed spread operator backward compatibility with PHP 7.4.
* [Ukrposhta] Added new option - default payer.
* Added ability to create labels for any orders.
* Fixed critical error in some cases on Order list and Edit checkout pages.
* [Tracking] Improved signature comparing.
* Checkout improvements.

= Version 1.15.4 / (02.06.2025) =
* [Ukrposhta] Ability to create labels from private entrepreneur.
* [Ukrposhta] Ability to create labels with COD.
* [Ukrposhta] Added default sender city and warehouse options.
* [Ukrposhta] Now supports cloud tracking API.
* [Ukrposhta] Added automation rules (carrier status changed event etc.).
* Added new hook wcus_shipping_label_created.
* Fixed internal server error for Ukrposhta shipping method in checkout.

= Version 1.15.3 / (28.05.2025) =
* Restored previous city format (Sales Drive compatibility).
* [Tracking] Status notifications now work via webhooks.
* [Tools] Added skipping duplicated ttn on sync with WC Ukraine Shipping PRO (legacy plugin).

= Version 1.15.2 / (27.05.2025) =
* [Hotfix] Internal server error on checkout when Ukrposhta sender city not selected.

= Version 1.15.1 / (24.05.2025) =
* Fixed a critical error for some cases on the order edit page.

= Version 1.15.0 / (24.05.2025) =
* Added Ukrposhta integration (select warehouse, create TTN, calculate rates).
* Legacy checkout UI was fully removed.
* Improved Nova Poshta cities search.
* Architecture improvements.