=== Hungry REST API Monitor ===
Contributors: prakharb88
Donate link: https://nandann.com/contact
Tags: rest api, api monitor, woocommerce, performance, analytics
Requires at least: 6.2
Tested up to: 6.9
Stable tag: 1.0.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Monitor WordPress REST API requests with detailed analytics, performance metrics, and beautiful visualizations. Full WooCommerce support included.

== Description ==

**Hungry Rest API Monitor** is a powerful WordPress plugin that tracks and analyzes all REST API requests hitting your site. Whether you're running a headless WordPress site, a mobile app backend, or a WooCommerce store with API integrations, this plugin gives you complete visibility into your API performance.

= Key Features =

**📊 Visual Analytics Dashboard**
* Beautiful charts showing traffic patterns over time
* HTTP method distribution (GET, POST, PUT, DELETE)
* Status code breakdown (2xx, 4xx, 5xx errors)
* Authenticated vs anonymous request tracking

**⚡ Performance Monitoring**
* Response time tracking for every request
* Memory usage per API call
* Database query count and execution time
* Memory usage as percentage of PHP limit
* Execution time as percentage of max limit

**🔍 Advanced Tracking**
* PHP errors during API requests
* Outgoing HTTP API calls made during requests
* Object cache hit/miss ratios
* Duplicate query detection
* Transient updates during requests

**🌐 Outgoing HTTP Request Tracking**
* Detailed view of all external HTTP calls made during REST requests
* Track request URL, method, status, and response time
* See request/response headers and body sizes
* Identify caller file, line, and function (debug info)
* Filter by URL, method, status code, and date
* Link each HTTP request to its parent REST API call

**🎯 Endpoint Analytics**
* Aggregate statistics per endpoint
* Find your slowest endpoints
* Identify memory-heavy endpoints
* Track query-heavy endpoints
* Error rate per endpoint

**📋 Detailed Logging**
* Searchable request logs
* Filter by endpoint, method, status, user
* Date range filtering
* Sortable columns
* Click-through to full request details

**🛒 WooCommerce Ready**
* Automatically monitors all WooCommerce REST API endpoints
* Track `/wp-json/wc/v3/products`
* Monitor `/wp-json/wc/v3/orders`
* Analyze `/wp-json/wc/v3/customers`
* Works with any WooCommerce API consumer

**🔒 Privacy & Security**
* IP address logging disabled by default (GDPR-friendly)
* Nonce verification on all AJAX requests
* Capability checks for admin access
* Prepared statements for all database queries

== External Services ==

This plugin connects to external services **only when an administrator manually clicks the "Run Test Requests" button** in the Settings tab. This feature is designed to verify that the HTTP request tracking functionality is working correctly.

**When the test is run, the plugin sends simple GET requests to:**

1. **httpbin.org** - A testing service used to verify HTTP request tracking.
   * Data sent: A simple GET request with no user data
   * When: Only when admin clicks "Run Test Requests"
   * [Terms of Service](https://httpbin.org/) | [Privacy Policy](https://httpbin.org/)

2. **api.wordpress.org** - The official WordPress.org API, used to test JSON API response tracking.
   * Data sent: A simple GET request to the version-check endpoint (no user data)
   * When: Only when admin clicks "Run Test Requests"
   * [Terms of Service](https://wordpress.org/about/tos/) | [Privacy Policy](https://wordpress.org/about/privacy/)

3. **jsonplaceholder.typicode.com** - A free testing API used to verify HTTP tracking with JSON responses.
   * Data sent: A simple GET request with no user data
   * When: Only when admin clicks "Run Test Requests"
   * [Terms of Service](https://jsonplaceholder.typicode.com/) | [Privacy Policy](https://jsonplaceholder.typicode.com/)

**Important:** These external requests are never made automatically. They only occur when an administrator explicitly initiates the test feature. No user data, site data, or any personal information is transmitted to these services.

= Use Cases =

* **API Performance Optimization** - Find slow endpoints and optimize them
* **Debugging** - Track down API errors and failed requests
* **Security Monitoring** - Identify unusual API access patterns
* **Capacity Planning** - Understand your API traffic patterns
* **WooCommerce Integrations** - Monitor third-party app connections

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/hungry-rest-api-monitor/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'API Monitor' in the admin menu
4. Make some REST API requests and watch the data flow in!

== Frequently Asked Questions ==

= Does this plugin slow down my API? =

The plugin is designed to be lightweight. It adds minimal overhead to each request (typically < 1ms). You can disable logging in Settings if needed.

= Does it work with WooCommerce? =

Yes! The plugin automatically monitors all REST API endpoints, including WooCommerce's `/wp-json/wc/v3/*` endpoints.

= Is it GDPR compliant? =

Yes. IP address logging is disabled by default. You can enable it in Settings if needed.

= How long is data retained? =

By default, logs are kept for 7 days. You can change this in Settings (1-365 days).

= Can I exclude certain endpoints? =

Yes! Go to Settings and add patterns to the "Excluded Endpoints" field. Use * as a wildcard.

== Screenshots ==

1. Dashboard with traffic charts, summary cards, HTTP methods distribution, and status code breakdown
2. Endpoints analytics with sorting and filtering options
3. Detailed request logs with advanced filtering by endpoint, method, status, and date
4. Request detail modal showing full request information including memory, queries, and cache stats
5. HTTP Requests tracking - view all outgoing HTTP API calls made during REST requests
6. Settings page with logging, privacy, and data management options
7. Support page with plugin information and contact form

== Changelog ==

= 1.0.2 =
* Added External Services documentation to comply with WordPress plugin guidelines.
* Documented third-party API usage for the test HTTP requests feature.

= 1.0.1 =
* Fixed date display issue in HTTP Requests tab and improved database query security.

= 1.0.0 =
* Initial release
* Dashboard with Chart.js visualizations
* Endpoint analytics
* Request logging with filters
* HTTP Requests tracking for outgoing calls
* Settings for retention and privacy
* WooCommerce REST API support

== Upgrade Notice ==

= 1.0.2 =
Added external services documentation for WordPress compliance. Recommended update.

= 1.0.1 =
Fixed date display and improved security. Recommended update.

= 1.0.0 =
Initial release of Hungry REST API Monitor.
