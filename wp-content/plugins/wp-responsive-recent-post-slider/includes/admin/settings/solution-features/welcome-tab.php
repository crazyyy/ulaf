<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package WP Logo Showcase Responsive Slider and Carousel
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<div id="wppsac_welcome_tabs" class="wppsac-vtab-cnt wppsac_welcome_tabs wppsac-clearfix">

	<!-- <div class="wppsac-black-friday-banner-wrp">
		<a href="<?php // echo esc_url( WPRPS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank"><img style="width: 100%" src="<?php // echo esc_url( WPRPS_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
	</div> -->
	
	<div class="wppsac-black-friday-banner-wrp" style="background:#e1ecc8;padding: 20px 20px 40px; border-radius:5px; text-align:center;margin-bottom: 40px;">
		<h2 style="font-size:30px; margin-bottom:10px;"><span style="color:#0055fb;">WP Responsive Recent Post Slider/Carousel</span> is included in <span style="color:#0055fb;">Essential Plugin Bundle</span> </h2> 
		<h4 style="font-size: 18px;margin-top: 0px;color: #ff5d52;margin-bottom: 24px;">Now get Designs, Optimization, Security, Backup, Migration Solutions @ one stop. </h4>

		<div class="wppsac-black-friday-feature">

			<div class="wppsac-inner-deal-class" style="width:40%;">
				<div class="wppsac-inner-Bonus-class">Bonus</div>
				<div class="wppsac-image-logo" style="font-weight: bold;font-size: 26px;color: #222;"><img style="width: 34px; height:34px;vertical-align: middle;margin-right: 5px;" class="wppsac-img-logo" src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/essential-logo-small.png" alt="essential-logo" /><span class="wppsac-esstial-name" style="color:#0055fb;">Essential </span>Plugin</div>
				<div class="wppsac-sub-heading" style="font-size: 16px;text-align: left;font-weight: bold;color: #222;margin-bottom: 10px;">Includes All premium plugins at no extra cost.</div>
				<a class="wppsac-sf-btn" href="<?php echo esc_url( WPRPS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank">Grab The Deal</a>
			</div>

			<div class="wppsac-main-list-class" style="width:60%;">
				<div class="wppsac-inner-list-class">
					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/img-slider.png" alt="essential-logo" /> Image Slider</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/advertising.png" alt="essential-logo" /> Publication</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/marketing.png" alt="essential-logo" /> Marketing</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/photo-album.png" alt="essential-logo" /> Photo album</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/showcase.png" alt="essential-logo" /> Showcase</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/shopping-bag.png" alt="essential-logo" /> WooCommerce</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/performance.png" alt="essential-logo" /> Performance</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/security.png" alt="essential-logo" /> Security</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/forms.png" alt="essential-logo" /> Pro Forms</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/seo.png" alt="essential-logo" /> SEO</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/backup.png" alt="essential-logo" /> Backups</li></div>

					<div class="wppsac-list-img-class"><img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/White-labeling.png" alt="essential-logo" /> Migration</li></div>
				</div>
			</div>
		</div>
		<div class="wppsac-main-feature-item">
			<div class="wppsac-inner-feature-item">
				<div class="wppsac-list-feature-item">
					<img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/layers.png" alt="layer" />
					<h5>Site management</h5>
					<p>Manage, update, secure & optimize unlimited sites.</p>
				</div>
				<div class="wppsac-list-feature-item">
					<img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/risk.png" alt="backup" />
					<h5>Backup storage</h5>
					<p>Secure sites with auto backups and easy restore.</p>
				</div>
				<div class="wppsac-list-feature-item">
					<img src="<?php echo esc_url( WPRPS_URL ); ?>assets/images/logo-image/support.png" alt="support" />
					<h5>Support</h5>
					<p>Get answers on everything WordPress at anytime.</p>
				</div>
			</div>
		</div>
		<a class="wppsac-sf-btn" href="<?php echo esc_url( WPRPS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank">Grab The Deal</a>
	</div>

	<!-- <div class="wppsac-deal-offer-wrap">
		<h3 style="font-weight: bold; font-size: 30px; color:#ffef00; text-align:center; margin: 15px 0 5px 0;">Why Invest Time On Free Version?</h3>

		<h3 style="font-size: 18px; text-align:center; margin:0; color:#fff;">Explore WP Blog and Widgets Pro with Essential Bundle Free for 5 Days.</h3>			

		<div class="wppsac-deal-free-offer">
			<a href="<?php echo esc_url( WPRPS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="wppsac-sf-free-btn"><span class="dashicons dashicons-cart"></span> Try Pro For 5 Days Free</a>
		</div>
	</div> -->

	<!-- Start - Welcome Box -->
	<div class="wppsac-sf-welcome-wrap" style="padding: 30px;border-radius: 10px;border: 1px solid #e5ecf6;">
		<div class="wppsac-sf-welcome-inr wppsac-sf-center">
			<h5 class="wppsac-sf-content" style="font-size: 22px; margin: 20px 0;">Experience <span class="wppsac-sf-blue">4 Layouts</span>, <span class="wppsac-sf-blue">50+ stunning designs</span> with which show your recent blogs/posts in a slider/carousel form with excerpts and unique slider & carousel designs.</h5>
			<h5 class="wppsac-sf-content" style="font-size: 20px; margin: 20px 0;"><span class="wppsac-sf-blue">30,000+ </span>websites are using <span class="wppsac-sf-blue">Post Slider/Carousel</span>.</h5>
		</div>
		<div style="margin: 30px 0; text-transform: uppercase; text-align:center;">
			<a href="<?php echo esc_url( $wppsac_add_link ); ?>" class="wppsac-sf-btn">Launch Post Slider With Free Features</a>
		</div>
	</div>
	<!-- End - Welcome Box -->
</div>