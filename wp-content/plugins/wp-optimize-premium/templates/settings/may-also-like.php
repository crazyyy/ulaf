<?php if (!defined('WPO_VERSION')) die('No direct access allowed');

global $wp_optimize_notices;

?>
<div class="wpo-plugin-family__premium">
	<h2><?php esc_html_e("Our Other Plugins", 'wp-optimize');?></h2>
	<div class="wpo-plugin-family__plugins">
		<div class="wpo-plugin-family__plugin">
			<?php
			$wp_optimize->wp_optimize_url('https://teamupdraft.com/updraftplus/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=cta-on-plugin-family-tab&utm_creative_format=text', null, '<img class="addons" alt="'.__("UpdraftPlus", 'wp-optimize').'" src="'. WPO_PLUGIN_URL.'images/features/updraftplus_logo.svg' .'">');
			$wp_optimize->wp_optimize_url('https://teamupdraft.com/updraftplus/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=cta-on-plugin-family-tab&utm_creative_format=text', null, '<h3>'.__('UpdraftPlus – the ultimate protection for your site, hard work and business', 'wp-optimize').'</h3>', 'class="other-plugin-title"');
			?>
			<p>
				<?php echo esc_html__("Simplifies backups and restoration.", 'wp-optimize') . ' ' . esc_html__("It is the world's highest ranking and most popular scheduled backup plugin, with over three million currently-active installs.", 'wp-optimize'); ?>
			</p>
			<?php
			$ud_is_installed = WP_Optimize()->is_installed('updraftplus');
			if ($ud_is_installed['installed']) {
			?>
				<p class="wpo-plugin-installed"><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Installed', 'wp-optimize'); ?></p>
			<?php
			} else {
				$wp_optimize->wp_optimize_url('https://teamupdraft.com/updraftplus/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=cta-on-plugin-family-tab&utm_creative_format=text', null, __('Try for free', 'wp-optimize'));
			}
			?>
		</div>
		<div class="wpo-plugin-family__plugin">
		<?php
			$wp_optimize->wp_optimize_url('https://teamupdraft.com/updraftcentral/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=cta-on-plugin-family-tab&utm_creative_format=text', null, '<img class="addons" alt="'.esc_attr__("UpdraftCentral Dashboard
	", 'wp-optimize').'" src="'. esc_url(WPO_PLUGIN_URL.'images/features/updraftcentral_logo.svg') .'">');
			$wp_optimize->wp_optimize_url('https://teamupdraft.com/updraftcentral/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=cta-on-plugin-family-tab&utm_creative_format=text', null, '<h3>'.esc_html__('UpdraftCentral – save hours managing multiple WP sites from one place', 'wp-optimize').'</h3>', 'class="other-plugin-title"');
			?>
			<p>
				<?php esc_html_e("Highly efficient way to manage, optimize, update and backup multiple websites from one place.", 'wp-optimize');?>
			</p>
			<?php
			$udc_is_installed = WP_Optimize()->is_installed('updraftcentral');
			if ($udc_is_installed['installed']) {
			?>
				<p class="wpo-plugin-installed"><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Installed', 'wp-optimize'); ?></p>
			<?php
			} else {
				$wp_optimize->wp_optimize_url('https://teamupdraft.com/updraftcentral/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=cta-on-plugin-family-tab&utm_creative_format=text', null, esc_html__('Try for free', 'wp-optimize'));
			}
			?>
		</div>
		<div class="wpo-plugin-family__plugin">
			<?php
			$wp_optimize->wp_optimize_url('https://teamupdraft.com/all-in-one-security/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=cta-on-plugin-family-tab&utm_creative_format=text', null, '<img class="addons" alt="'. esc_attr__('All-In-One-Security (AIOS)', 'wp-optimize').'" src="'. esc_url(WPO_PLUGIN_URL.'images/our-other-plugins/aios-logo-wide-sm.svg') .'">');
			$wp_optimize->wp_optimize_url('https://teamupdraft.com/all-in-one-security/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=cta-on-plugin-family-tab&utm_creative_format=text', null, '<h3>'. esc_html__('All-In-One-Security (AIOS) – the top-rated WordPress security and firewall plugin', 'wp-optimize').'</h3>', 'class="other-plugin-title"');
			?>
			<p>
				<?php echo esc_html__("A comprehensive, all-in-one security plugin with a five-star user rating.", 'wp-optimize') . ' ' . esc_html__("It keeps bots at bay and protects your website from brute-force attacks.", 'wp-optimize') . ' ' . esc_html__("Set, forget and AIOS will do the hard work for you.", 'wp-optimize'); ?>
			</p>
			<?php
			$aios_is_installed = WP_Optimize()->is_installed('all-in-one-wp-security-and-firewall');
			if ($aios_is_installed['installed']) {
				?>
				<p class="wpo-plugin-installed"><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Installed', 'wp-optimize'); ?></p>
				<?php
			} else {
				$wp_optimize->wp_optimize_url('https://teamupdraft.com/all-in-one-security/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=cta-on-plugin-family-tab&utm_creative_format=text', null, esc_html__('Try for free', 'wp-optimize'));
			}
			?>
		</div>
		<div class="wpo-plugin-family__plugin">
			<?php
			$wp_optimize->wp_optimize_url('https://www.internallinkjuicer.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-ilj&utm_campaign=ad', null, '<img class="addons" alt="'. esc_attr__('Internal Link Juicer', 'wp-optimize').'" src="'. esc_url(WPO_PLUGIN_URL.'images/our-other-plugins/internal-link-juicer-logo-sm.svg') .'">');
			$wp_optimize->wp_optimize_url('https://www.internallinkjuicer.com/?utm_medium=software&utm_source=wpo&utm_content=ilj-mayalso-like-tab&utm_term=try-now-aios&utm_campaign=ad', null, '<h3>'. esc_html__('Internal Link Juicer – automated internal linking plugin for WordPress', 'wp-optimize').'</h3>', 'class="other-plugin-title"');
			?>
			<p>
				<?php echo esc_html__("This five-star rated plugin automates internal linking.", 'wp-optimize') . ' ' . esc_html__("It strategically places relevant links within your content.", 'wp-optimize'); ?>
			</p>
			<p>
				<?php esc_html_e("Internal Link Juicer is here to do all the work for you.", 'wp-optimize');?>
			</p>
			<?php
			$ilj_is_installed = WP_Optimize()->is_installed('internal-links');
			if ($ilj_is_installed['installed']) {
				?>
				<p class="wpo-plugin-installed"><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Installed', 'wp-optimize'); ?></p>
				<?php
			} else {
				$wp_optimize->wp_optimize_url('https://www.internallinkjuicer.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-ilj&utm_campaign=ad', null, esc_html__('Try for free', 'wp-optimize'));
			}
			?>
		</div>
		<div class="wpo-plugin-family__plugin">
			<?php
			$wp_optimize->wp_optimize_url('https://wpovernight.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-wp-overnight&utm_campaign=ad', null, '<img class="addons" alt="'. esc_attr__('WP Overnight', 'wp-optimize').'" src="'. esc_url(WPO_PLUGIN_URL.'images/our-other-plugins/wp-overnight-sm.png') .'">');
			$wp_optimize->wp_optimize_url('https://wpovernight.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-wp-overnight&utm_campaign=ad', null, '<h3>'. esc_html__('WP Overnight - quality plugins for your WooCommerce store. 5 star rated invoicing, order and product management tools', 'wp-optimize').'</h3>', 'class="other-plugin-title"');
			?>
			<p>
				<?php echo esc_html__("WP Overnight is an independent plugin shop with a range of WooCommerce plugins.", 'wp-optimize') . ' ' . esc_html__("Our range of plugins has over 7,500,000 downloads and thousands of loyal customers.", 'wp-optimize'); ?>
			</p>
			<p>
				<?php esc_html_e("Create PDF invoices, automations, barcodes, reports and so much more.", 'wp-optimize');?>
			</p>
			<?php $wp_optimize->wp_optimize_url('https://wpovernight.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-wp-overnight&utm_campaign=ad', null, esc_html__('Try for free', 'wp-optimize')); ?>
		</div>
		<div class="wpo-plugin-family__plugin">
			<?php
			$wp_optimize->wp_optimize_url('https://wpgetapi.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-wpgetapi&utm_campaign=ad', null, '<img class="addons" alt="'. esc_attr__('WP Get API', 'wp-optimize').'" src="'. esc_url(WPO_PLUGIN_URL.'images/our-other-plugins/wpgetapi-sm.png') .'">');
			$wp_optimize->wp_optimize_url('https://wpgetapi.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-wpgetapi&utm_campaign=ad', null, '<h3>'. esc_html__('WPGetAPI - connect WordPress to APIs without a developer', 'wp-optimize').'</h3>', 'class="other-plugin-title"');
			?>
			<p>
				<?php echo esc_html__("The easiest way to connect your WordPress website to an external API.", 'wp-optimize') . ' ' . esc_html__("WPGetAPI is free, powerful, and easy to use.", 'wp-optimize') . ' ' . esc_html__("Connect to virtually any REST API and retrieve data without writing a line of code.", 'wp-optimize'); ?>
			</p>
			<?php
			$wpga_is_installed = WP_Optimize()->is_installed('wpgetapi');
			if ($wpga_is_installed['installed']) {
				?>
				<p class="wpo-plugin-installed"><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Installed', 'wp-optimize'); ?></p>
				<?php
			} else {
				$wp_optimize->wp_optimize_url('https://wpgetapi.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-wpgetapi&utm_campaign=ad', null, esc_html__('Try for free', 'wp-optimize'));
			}
			?>
		</div>
		<div class="wpo-plugin-family__plugin">
			<?php
			$wp_optimize->wp_optimize_url('https://easyupdatesmanager.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-eum&utm_campaign=ad', null, '<img class="addons" alt="'. esc_attr__('WP Overnight', 'wp-optimize').'" src="'. esc_url(WPO_PLUGIN_URL.'images/our-other-plugins/easy-updates-manager-sm.svg') .'">');
			$wp_optimize->wp_optimize_url('https://easyupdatesmanager.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-eum&utm_campaign=ad', null, '<h3>'. esc_html__('Easy Updates Manager - keep your WordPress site up to date and bug free', 'wp-optimize').'</h3>', 'class="other-plugin-title"');
			?>
			<p>
				<?php echo esc_html__("A light yet powerful plugin that allows you to manage all kinds of updates.", 'wp-optimize') . ' ' . esc_html__("With a huge number of settings for endless customization.", 'wp-optimize') . ' ' . esc_html__("Easy Updates Manager is an obvious choice for anyone wanting to take control of their website updates.", 'wp-optimize'); ?>
			</p>
			<?php
			$eum_is_installed = WP_Optimize()->is_installed('stops-core-theme-and-plugin-updates');
			if ($eum_is_installed['installed']) {
				?>
				<p class="wpo-plugin-installed"><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Installed', 'wp-optimize'); ?></p>
				<?php
			} else {
				$wp_optimize->wp_optimize_url('https://easyupdatesmanager.com/?utm_medium=software&utm_source=wpo&utm_content=wpo-mayalso-like-tab&utm_term=try-now-eum&utm_campaign=ad', null, esc_html__('Try for free', 'wp-optimize'));
			}
			?>
		</div>
		<div class="wpo-plugin-family__plugin">
			<?php
			$wp_optimize->wp_optimize_url('https://burst-statistics.com/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=burst-logo&utm_creative_format=ad', null, '<img class="addons" alt="'. esc_attr__('Burst Statistics', 'wp-optimize').'" src="'. esc_url(WPO_PLUGIN_URL.'images/our-other-plugins/burst-logo-sm.png') .'">');
			$wp_optimize->wp_optimize_url('https://burst-statistics.com/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=burst-logo&utm_creative_format=ad', null, '<h3>'. esc_html__('Burst Statistics – privacy-friendly analytics for WordPress', 'wp-optimize').'</h3>', 'class="other-plugin-title"');
			?>
			<p>
			<?php echo esc_html__("Track website traffic with ease using Burst Statistics.", 'wp-optimize') . ' ' . esc_html__("This lightweight plugin gives you clear, actionable insights without compromising visitor privacy.", 'wp-optimize') . ' ' . esc_html__("Perfect for site owners who want GDPR-compliant stats without the bloat.", 'wp-optimize'); ?>
			</p>
			<?php
			$burst_is_installed = WP_Optimize()->is_installed('burst-statistics');
			if ($burst_is_installed['installed']) {
				?>
				<p class="wpo-plugin-installed"><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Installed', 'wp-optimize'); ?></p>
				<?php
			} else {
				$wp_optimize->wp_optimize_url('https://burst-statistics.com/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=try-for-free&utm_creative_format=ad', null, esc_html__('Try for free', 'wp-optimize'));
			}
			?>
		</div>
	</div>
</div>