<?php
defined( 'ABSPATH' ) || exit;
?>
<h3><?php esc_html_e( 'Country Blocking Feature', 'adminease' ); ?></h3>

<p><?php esc_html_e( 'This feature allows you to block visitors from specific countries by automatically generating .htaccess rules that deny access based on the visitor\'s country code. This feature works with Cloudflare or the PHP GeoIP extension.', 'adminease' ); ?></p>

<p><?php esc_html_e( 'Works with Cloudflare (recommended) or PHP GeoIP extension.', 'adminease' ); ?></p>

<h4><?php esc_html_e( 'How It Works', 'adminease' ); ?></h4>
<ul>
	<li><strong><?php esc_html_e( 'Cloudflare Support', 'adminease' ); ?></strong>: <?php esc_html_e( 'Uses Cloudflare\'s CF-IPCountry header to identify visitor locations (recommended)', 'adminease' ); ?><br><a href="https://www.cloudflare.com/" target="_blank"><?php esc_html_e( 'Sign up for free at cloudflare.com', 'adminease' ); ?></a></li>
	<li><strong><?php esc_html_e( 'GeoIP Extension Support', 'adminease' ); ?></strong>: <?php esc_html_e( 'Alternatively works with server-side PHP GeoIP extension for country detection', 'adminease' ); ?></li>
	<li><strong><?php esc_html_e( 'Select Countries', 'adminease' ); ?></strong>: <?php esc_html_e( 'Choose which countries you want to block from accessing your website', 'adminease' ); ?></li>
	<li><strong><?php esc_html_e( 'Automatic Detection', 'adminease' ); ?></strong>: <?php esc_html_e( 'Automatically detects and uses the best available geolocation method', 'adminease' ); ?></li>
</ul>

<h4><?php esc_html_e( 'What Happens When Blocked', 'adminease' ); ?></h4>

<p><?php esc_html_e( 'Visitors from blocked countries will receive an HTTP 403 Forbidden error when trying to access your site. This is a clean, efficient block that doesn\'t redirect or consume additional server resources.', 'adminease' ); ?></p>

<h4><?php esc_html_e( 'Performance Benefits', 'adminease' ); ?></h4>

<ul>
	<li><strong><?php esc_html_e( 'Efficient Blocking', 'adminease' ); ?></strong>: <?php esc_html_e( 'Uses Apache\'s mod_rewrite for fast, server-level blocking', 'adminease' ); ?></li>
	<li><strong><?php esc_html_e( 'No PHP Overhead', 'adminease' ); ?></strong>: <?php esc_html_e( 'Blocks happen before WordPress even loads', 'adminease' ); ?></li>
	<li><strong><?php esc_html_e( 'Multiple Methods', 'adminease' ); ?></strong>: <?php esc_html_e( 'Works with Cloudflare headers or server GeoIP extension for maximum compatibility', 'adminease' ); ?></li>
</ul>

<p><a href="https://precisionwp.net/documentation/adminease/block-specific-countries-feature-user-guide/" target="_blank"><?php esc_html_e( 'Full guide', 'adminease' ); ?></a></p>