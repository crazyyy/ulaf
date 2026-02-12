=== CSS JS Manager, Async JavaScript, Defer Render Blocking CSS ===
Contributors: rajeshsingh520
Donate link: piwebsolution.com
Tags: Async CSS, Defer CSS, Defer JS, Async JS, pagespeed, remove css, remove js
Requires at least: 3.0.1
Tested up to: 6.9
Stable tag: 2.4.49.66
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

CSS JS Manager, Async JavaScript, Defer Render Blocking CSS, Remove javascript, Remove CSS, Defer Render Blocking CSS, Both CSS and JS can be loaded Asynchronous or Normal. There are many rules that allow you to remove them from different type of pages Add multiple Critical CSS for different pages

== Description ==

[Documentation](https://www.piwebsolution.com/css-js-manager-documentation/)

* Load / remove css or js if the request is from a mobile device
* Load / remove css or js if the request is from a desktop device
* Defer Loading of CSS file
* Load any JS file Async or Defer
* Remove any JS from all the pages, or on some pages based on various conditional rules
* Remove any CSS file from all the pages, or on some pages based on various conditional rules
* Async any JS file on all the pages or on selective pages
* You can remove or add JS file based on post type 
* You can remove or add CSS file based on post type 
* You can remove or add JS file based on page id 
* You can remove or add CSS file based on page id 


== Manage critical css ==
Now you can load different critical css for different pages or post or custom post type and optimize your site for speed
<blockquote>
Step 1: Go to CSS JS Manager >> Critical CSS setting
select the post type where you want to use the Critical CSS, 
Say you want to use it on Post, Product

Step 2: go to Critical CSS >> Add New Critical css
you can add as many critical css as you want
Say you want to have Different critical css for home page, and post, and product 
so you will create 3 different critical css and load the critical css There

Use this to generate critical css https://www.sitelocity.com/critical-path-css-generator

Step 3: Go to respective post or page where you will like to use those critical css and select them
</blockquote>

== How to Defer Render Blocking CSS using CSS JS Manager? ==
[youtube https://www.youtube.com/watch?v=ob2oECSMyg8]

== How to remove CSS file from complete website or particular page? ==
[youtube https://www.youtube.com/watch?v=D6GBtpSIUMw]

== The same steps apply to JS as well just add JS link and select JS from the Drop drop down ==

== Premium support: We respond to all support topics within 24 hours ==

== Frequently Asked Questions ==

= How to Defer Render Blocking CSS using CSS JS Manager? =

* Copy the URL of this file without the query string variable ( without this “?ver=5.0.3“
* URL: http://localhost/telco/wp-content/themes/twentyseventeen/style.css
* Now Open the CSS JS Manager
* Click on “Add New Resource”
* Now add the URL from the Step 2
* Set method as “Async“
* Selection Logic “Add This“
* On All Pages

= How to remove CSS file from complete website? =
You have a css file that is not used in your website and you want to remove it from your website completely.

you can do that easily. just follow all the steps given in the Defer process, you can keep the loading method as any thing (as it wont mater as the css will be removed) then at the bottom

= If you want to allow JS file on only few particular page and remove from all other =
All the steps remain same, You need to copy the JS file URL, Select Resource type as “JS” from the drop down.

Select the method of the loading for the JS (it has Defer, Async, and normal)

In selection logic click “Add this on”

Click on “Selected Pages”

In the Single Resource ID enter the Page id of the pages where you want this js to be allowed

E,g: 1,2,22 this are the ID of the page where this js will be allowed to load an apart from this it wont load on any other page

= Does it support WooCommerce =
Yes, WooCommerce Page tags like is_product, is_category, is_cart, is_shop and other. So you can load or remove script specific to the WooCommerce page rules

= I want to remove the css if request is from mobile device =
Yes you can remove css or js based on the device if it is mobile or desktop

= I want to async a js file for mobile device =
Yes you can do that in the pro version

== Changelog ==

= 2.4.49.60 =
* Css JS Manager tested for WC 10.0.2

= 2.4.49.47 =
* Tested for WP 6.8.0
* React braking admin panel fixed

= 2.4.49.39 =
* Tested for WC 9.4.0

= 2.4.49.37 =
* Tested for WP 6.7.0

= 2.4.49.36 =
* Tested for WC 9.3.3

= 2.4.49.32 =
* Tested for WC 9.1.4

= 2.4.49.31 =
* Tested for WC 9.1.0

= 2.4.49.30 =
* Tested for WP 6.6.1

= 2.4.49.20 =
* HPOS compatible
