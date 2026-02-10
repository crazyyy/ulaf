=== Icons for Plugins - TxToIT ===
Contributors: karzin
Tags: icons,thumb,plugins,admin,list,dashboard,page
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=BAC8PT82YMTJL&lc=US&item_name=Icons%20for%20Plugins%20%2d%20TxToIT&item_number=icons%2dfor%2dplugins&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 4.4
Requires PHP: 5.4
Tested up to: 4.9
Stable tag: 1.0.6
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

**Icons for plugins** displays icons for WordPress plugins on the plugins list page, on admin

== Description ==

**Icons for plugins** displays icons for WordPress plugins on the plugins list page, on admin.

WordPress dashboard doesn't allow to display icons on admin plugins page (wp-admin/plugins.php).
It would be so handy to see them. They would help you identify plugins quickier and at least they would turn that plugins list into something less boring, right?

Well, now you can display icons for plugins on plugins list page, on dashboard.
Simply enable this plugin and you will see plugins icons on admin plugins page.

On **Settings > Plugins icons**, you can even play with some settings, like icon's size and the column position they will be displayed.

== Frequently Asked Questions ==

= Why creating a plugin to display plugins icons? =
* Icons can help you understand what a plugin is about. 
* Icons help you identify plugins quickier
* Icons make that plugins list page (wp-admin/plugins.php) something less boring, right?
* Almost every WordPress plugin on wp.org already has its own Icon / Banner. Why not to use it inside WordPress dashboard? 

= How does it work? = 
* This plugin will create a new column on plugins list page on admin. This column will display icons for plugins. 

= Where do the icons come from? = 
* They come from wordpress.org. The icons and banners authors choose for their plugins on wordpress.org will be used by this plugin. If a plugin doesn't have an icon, its banner will be used instead

= How to register icons for plugins that aren't on the WordPress repository? =
You can use the filter **'ifp_plugin_icon_url'**. See its documentation below

= Are there any hooks / filters available? =
**ifp_plugin_icon_url** - Allows to add icons for any plugins

`add_filter( 'ifp_plugin_icon_url', function( $icon_url, $plugin_title, $plugin_infs ){
    if( $plugin_title == 'Your Plugin Name' ){
        $icon_url = 'http://your-image.jpg';
    }
    return $icon_url;
}, 10, 3 );`

= Can I change icon size among other settings? =
* Yes, On **Settings > Plugins icons**, you can even play with some settings, like icon's size and the column position they will be displayed.

== Installation ==

1. Upload the entire 'txtoit-icons-for-plugins' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Start by visiting plugin settings at Settings > Plugins Icons.

== Screenshots ==

1. Display icons on your installed WordPress plugins

== Changelog ==

= 1.0.6 - 21/03/2018 =
* Improve travis setup

= 1.0.5 - 20/03/2018 =
* Config auto deploy with travis

= 1.0.4 - 06/12/2017 =
* Change autoloader

= 1.0.3 - 06/12/2017 =
* Improve readme

= 1.0.2 - 30/11/2017 =
* Add filter 'ifp_plugin_icon_url' to allow registering icons for any plugins

= 1.0.1 - 28/11/2017 =
* Add option to guess icons

= 1.0.0 - 26/11/2017 =
* Initial Release.

== Upgrade Notice ==

= 1.0.4 =
* Change autoloader