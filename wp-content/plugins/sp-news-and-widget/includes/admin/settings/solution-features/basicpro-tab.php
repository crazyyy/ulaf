<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>
<div id="wpnw_basic_tabs" class="wpnw-vtab-cnt wpnw_basic_tabs wpnw-clearfix">
	<!-- <div class="wpnw-deal-offer-wrap">
		<div class="wpnw-deal-offer"> 
			<div class="wpnw-inn-deal-offer">
				<h3 class="wpnw-inn-deal-hedding"><span>Buy WP News and Scrolling Widgets Pro</span> today and unlock all the powerful features.</h3>
				<h4 class="wpnw-inn-deal-sub-hedding"><span style="color:red;">Extra Bonus: </span>Users will get <span>extra best discount</span> on the regular price using this coupon code.</h4>
			</div>
			<div class="wpnw-inn-deal-offer-btn">
				<div class="wpnw-inn-deal-code"><span>EPSEXTRA</span></div>
				<a href="<?php // echo esc_url(WPNW_PLUGIN_BUNDLE_LINK); ?>"  target="_blank" class="wpnw-sf-btn wpnw-sf-btn-orange"><span class="dashicons dashicons-cart"></span> Get Essential Bundle Now</a>
				<em class="risk-free-guarantee"><span class="heading">Risk-Free Guarantee </span> - We offer a <span>30-day money back guarantee on all purchases</span>. If you are not happy with your purchases, we will refund your purchase. No questions asked!</em>
			</div>
		</div>
	</div> -->

	<!-- <div class="wpnw-deal-offer-wrap">
		<div class="wpnw-deal-offer"> 
			<div class="wpnw-inn-deal-offer">
				<h3 class="wpnw-inn-deal-hedding"><span>Try WP News and Scrolling Widgets Pro</span> in Essential Bundle Free For 5 Days.</h3>
			</div>
			<div class="wpnw-deal-free-offer">
				<a href="<?php //echo esc_url( WPNW_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="wpnw-sf-free-btn"><span class="dashicons dashicons-cart"></span>Try Pro For 5 Days Free</a>
			</div>
		</div>
	</div> -->

	<!-- <div class="wpnw-black-friday-banner-wrp">
		<a href="<?php  // echo esc_url( WPNW_PLUGIN_BUNDLE_LINK ); ?>" target="_blank"><img style="width: 100%;" src="<?php // echo esc_url( WPNW_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
	</div> -->

	<div class="wpnw-black-friday-banner-wrp" style="background:#e1ecc8;padding: 20px 20px 40px; border-radius:5px; text-align:center;margin-bottom: 40px;">
		<h2 style="font-size:30px; margin-bottom:10px;"><span style="color:#0055fb;">WP News and Scrolling Widgets</span> is included in <span style="color:#0055fb;">Essential Plugin Bundle</span> </h2> 
		<h4 style="font-size: 18px;margin-top: 0px;color: #ff5d52;margin-bottom: 24px;">Now get Designs, Optimization, Security, Backup, Migration Solutions @ one stop. </h4>

		<div class="wpnw-black-friday-feature">

			<div class="wpnw-inner-deal-class" style="width:40%;">
				<div class="wpnw-inner-Bonus-class">Bonus</div>
				<div class="wpnw-image-logo" style="font-weight: bold;font-size: 26px;color: #222;"><img style="width: 34px; height:34px;vertical-align: middle;margin-right: 5px;" class="wpnw-img-logo" src="<?php echo esc_url( WPNW_URL ); ?>assets/images/essential-logo-small.png" alt="essential-logo" /><span class="wpnw-esstial-name" style="color:#0055fb;">Essential </span>Plugin</div>
				<div class="wpnw-sub-heading" style="font-size: 16px;text-align: left;font-weight: bold;color: #222;margin-bottom: 10px;">Includes All premium plugins at no extra cost.</div>
				<a class="wpnw-sf-btn" href="<?php echo esc_url( WPNW_PLUGIN_BUNDLE_LINK ); ?>" target="_blank">Grab The Deal</a>
			</div>

			<div class="wpnw-main-list-class" style="width:60%;">
				<div class="wpnw-inner-list-class">
					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/img-slider.png" alt="essential-logo" /> Image Slider</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/advertising.png" alt="essential-logo" /> Publication</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/marketing.png" alt="essential-logo" /> Marketing</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/photo-album.png" alt="essential-logo" /> Photo album</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/showcase.png" alt="essential-logo" /> Showcase</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/shopping-bag.png" alt="essential-logo" /> WooCommerce</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/performance.png" alt="essential-logo" /> Performance</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/security.png" alt="essential-logo" /> Security</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/forms.png" alt="essential-logo" /> Pro Forms</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/seo.png" alt="essential-logo" /> SEO</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/backup.png" alt="essential-logo" /> Backups</li></div>

					<div class="wpnw-list-img-class"><img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/White-labeling.png" alt="essential-logo" /> Migration</li></div>
				</div>
			</div>
		</div>
		<div class="wpnw-main-feature-item">
			<div class="wpnw-inner-feature-item">
				<div class="wpnw-list-feature-item">
					<img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/layers.png" alt="layer" />
					<h5>Site management</h5>
					<p>Manage, update, secure & optimize unlimited sites.</p>
				</div>
				<div class="wpnw-list-feature-item">
					<img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/risk.png" alt="backup" />
					<h5>Backup storage</h5>
					<p>Secure sites with auto backups and easy restore.</p>
				</div>
				<div class="wpnw-list-feature-item">
					<img src="<?php echo esc_url( WPNW_URL ); ?>assets/images/logo-image/support.png" alt="support" />
					<h5>Support</h5>
					<p>Get answers on everything WordPress at anytime.</p>
				</div>
			</div>
		</div>
		<a class="wpnw-sf-btn" href="<?php echo esc_url( WPNW_PLUGIN_BUNDLE_LINK ); ?>" target="_blank">Grab The Deal</a>
	</div>

	<h3 style="text-align:center">Compare <span class="wpnw-blue">"WP News and Scrolling Widgets"</span> Free VS Pro</h3>

	<table class="wpos-plugin-pricing-table">
		<colgroup></colgroup>
		<colgroup></colgroup>
		<colgroup></colgroup>
		<thead>
			<tr>
				<th></th>
				<th>
					<h2><?php esc_html_e('Free', 'sp-news-and-widget'); ?></h2>
				</th>
				<th>
					<h2 class="wpos-epb"><?php esc_html_e('Premium', 'sp-news-and-widget'); ?></h2>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
			<th><?php esc_html_e('Designs', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Designs that make your website better', 'sp-news-and-widget'); ?></span></th>
			<td>2</td>
			<td>120+</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcodes', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Shortcode provide output to the front-end side', 'sp-news-and-widget'); ?></span></th>
				<td><?php esc_html_e('1 (Grid, List)', 'sp-news-and-widget'); ?></td>
				<td><?php esc_html_e('6 (Grid, Slider, Carousel, List, Gridbox, GridBox Slider, News Ticker)', 'sp-news-and-widget'); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcode Parameters', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Add extra power to the shortcode', 'sp-news-and-widget'); ?></span></th>
				<td>9</td>
				<td>30+</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcode Generator', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Play with all shortcode parameters with preview panel. No documentation required!!', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('WP Templating Features', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('You can modify plugin html/designs in your current theme.', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Widgets', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('WordPress Widgets to your sidebars.', 'sp-news-and-widget'); ?></span></th>
				<td>2</td>
				<td>7</td>
			</tr>
			<tr>
			<th><?php esc_html_e('Drag & Drop Post Order Change', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Arrange your desired post with your desired order and display', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Gutenberg Block Supports', 'sp-news-and-widget'); ?><span><?php esc_html_e('Use this plugin with Gutenberg easily', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Elementor Page Builder Support', 'sp-news-and-widget'); ?> <em class="wpos-new-feature">New</em><span><?php esc_html_e('Use this plugin with Elementor easily', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Beaver Builder Support', 'sp-news-and-widget'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with Beaver Builder easily', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('SiteOrigin Page Builder Support', 'sp-news-and-widget'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with SiteOrigin easily', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Divi Page Builder Native Support', 'sp-news-and-widget'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with Divi Builder easily', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Fusion (Avada) Page Builder Native Support', 'sp-news-and-widget'); ?> <em class="wpos-new-feature">New</em><span><?php esc_html_e('Use this plugin with Fusion Builder easily', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('WPBakery Page Builder Support', 'sp-news-and-widget'); ?><span><?php esc_html_e('Use this plugin with Visual Composer easily', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
			<th><?php esc_html_e('Custom Read More link for Post', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Redirect post to third party destination if any', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
			<th><?php esc_html_e('Publicize', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Support with Jetpack to publish your News post on', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
			<th><?php esc_html_e('Display Desired Post', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Display only the post you want', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
			<th><?php esc_html_e('Display Post for Particular Categories', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Display only the posts with particular category', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-yes"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
			<th><?php esc_html_e('Exclude Some Posts', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Do not display the posts you want', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
			<th><?php esc_html_e('Exclude Some Categories', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Do not display the posts for particular categories', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
			<th><?php esc_html_e('Post Order / Order By Parameters', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Display post according to date, title and etc', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-yes"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
			<th><?php esc_html_e('Multiple Slider Parameters', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Slider parameters like autoplay, number of slide, sider dots and etc.', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
			<th><?php esc_html_e('Slider RTL Support', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Slider supports for RTL website', 'sp-news-and-widget'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Automatic Update', 'sp-news-and-widget'); ?><span><?php esc_html_e('Get automatic plugin updates', 'sp-news-and-widget'); ?></span></th>
				<td><?php esc_html_e('Lifetime', 'sp-news-and-widget'); ?></td>
				<td><?php esc_html_e('Lifetime', 'sp-news-and-widget'); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Support', 'sp-news-and-widget'); ?><span class="subtext"><?php esc_html_e('Get support for plugin', 'sp-news-and-widget'); ?></span></th>
				<td><?php esc_html_e('Limited', 'sp-news-and-widget'); ?></td>
				<td><?php esc_html_e('1 Year', 'sp-news-and-widget'); ?></td>
			</tr>
		</tbody>
	</table>

	<!-- <div class="wpnw-black-friday-banner-wrp">
		<a href="<?php // echo esc_url( WPNW_PLUGIN_BUNDLE_LINK ); ?>" target="_blank"><img style="width: 100%;" src="<?php // echo esc_url( WPNW_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
	</div> -->

	<!-- <div class="wpnw-deal-offer-wrap">
		<div class="wpnw-deal-offer"> 
			<div class="wpnw-inn-deal-offer">
				<h3 class="wpnw-inn-deal-hedding"><span>Buy WP News and Scrolling Widgets Pro</span> today and unlock all the powerful features.</h3>
				<h4 class="wpnw-inn-deal-sub-hedding"><span style="color:red;">Extra Bonus: </span>Users will get <span>extra best discount</span> on the regular price using this coupon code.</h4>
			</div>
			<div class="wpnw-inn-deal-offer-btn">
				<div class="wpnw-inn-deal-code"><span>EPSEXTRA</span></div>
				<a href="<?php // echo esc_url(WPNW_PLUGIN_BUNDLE_LINK); ?>"  target="_blank" class="wpnw-sf-btn wpnw-sf-btn-orange"><span class="dashicons dashicons-cart"></span> Get Essential Bundle Now</a>
				<em class="risk-free-guarantee"><span class="heading">Risk-Free Guarantee </span> - We offer a <span>30-day money back guarantee on all purchases</span>. If you are not happy with your purchases, we will refund your purchase. No questions asked!</em>
			</div>
		</div>
	</div> -->

	<!-- <div class="wpnw-deal-offer-wrap">
		<div class="wpnw-deal-offer"> 
			<div class="wpnw-inn-deal-offer">
				<h3 class="wpnw-inn-deal-hedding"><span>Try WP News and Scrolling Widgets Pro</span> in Essential Bundle Free For 5 Days.</h3>
			</div>
			<div class="wpnw-deal-free-offer">
				<a href="<?php //echo esc_url( WPNW_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="wpnw-sf-free-btn"><span class="dashicons dashicons-cart"></span>Try Pro For 5 Days Free</a>
			</div>
		</div>
	</div> -->

</div>