<?php
/**
 * Function Custom meta box for Premium
 * 
 * @package WP News and Scrolling Widgets
 * @since 2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<!-- <div class="pro-notice"><strong><?php //echo sprintf( __( 'Utilize this <a href="%s" target="_blank">Premium Features (With Risk-Free 30 days money back guarantee)</a> to get best of this plugin with Annual or Lifetime bundle deal.', 'sp-news-and-widget'), WPNW_PLUGIN_LINK_UNLOCK); ?></strong></div> -->

<!-- <div class="pro-notice">
	<strong>
		<?php // echo sprintf( __( 'Try All These <a href="%s" target="_blank">PRO Features in Essential Bundle Free For 5 Days.</a>', 'wp-logo-showcase-responsive-slider-slider'), WPNW_PLUGIN_LINK_UNLOCK); ?>
	</strong>
</div> -->

<!-- <div class="wpnw-black-friday-banner-wrp">
	<a href="<?php // echo esc_url( WPNW_PLUGIN_LINK_UNLOCK ); ?>" target="_blank"><img style="width: 100%;" src="<?php // echo esc_url( WPNW_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
</div> -->

<strong style="color:#2ECC71; font-weight: 700;"><?php echo sprintf( __( ' <a href="%s" target="_blank" style="color:#2ECC71;">Upgrade To Pro</a> and Get Designs, Optimization, Security, Backup, Migration Solutions @ one stop.', 'sp-news-and-widget'), WPNW_PLUGIN_LINK_UNLOCK); ?></strong>

<table class="form-table wpnw-metabox-table">
	<tbody>

		<tr class="wpnw-pro-feature">
			<th>
				<?php esc_html_e('Layouts', 'sp-news-and-widget'); ?> <span class="wpnw-pro-tag"><?php esc_html_e('PRO','sp-news-and-widget');?></span>
			</th>
			<td>
				<span class="description"><strong><?php esc_html_e('6 (Grid, Slider, Carousel, List, Gridbox, GridBox Slider, News Ticker). In lite version only 1 layout.', 'sp-news-and-widget'); ?></strong></span>
			</td>
		</tr>
		<tr class="wpnw-pro-feature">
			<th>
				<?php esc_html_e('Designs', 'sp-news-and-widget'); ?> <span class="wpnw-pro-tag"><?php esc_html_e('PRO','sp-news-and-widget');?></span>
			</th>
			<td>
				<span class="description"><strong>120+</strong> <?php esc_html_e('In lite version only two design.', 'sp-news-and-widget'); ?></span>
			</td>
		</tr>
		<tr class="wpnw-pro-feature">
			<th>
				<?php esc_html_e('Widgets', 'sp-news-and-widget'); ?> <span class="wpnw-pro-tag"><?php _e('PRO','sp-news-and-widget');?></span>
			</th>
			<td>
				<span class="description"><strong>7+</strong> <?php esc_html_e('In lite version only two widget.', 'sp-news-and-widget'); ?></span>
			</td>
		</tr>
		<tr class="wpnw-pro-feature">
			<th>
				<?php esc_html_e('WP Templating Features ', 'sp-news-and-widget'); ?> <span class="wpnw-pro-tag"><?php esc_html_e('PRO','sp-news-and-widget');?></span>
			</th>
			<td>
				<span class="description"><?php esc_html_e('You can modify plugin html/designs in your current theme.', 'sp-news-and-widget'); ?></span>
			</td>
		</tr>
		<tr class="wpnw-pro-feature">
			<th>
				<?php esc_html_e( 'Shortcode Generator ', 'sp-news-and-widget' ); ?><span class="wpnw-pro-tag"><?php esc_html_e( 'PRO','sp-news-and-widget' ); ?></span>
			</th>
			<td>
				<span class="description"><?php esc_html_e('Play with all shortcode parameters with preview panel. No documentation required.' , 'sp-news-and-widget'); ?></span>
			</td>
		</tr>
		<tr class="wpnw-pro-feature">
			<th>
				<?php esc_html_e('Drag & Drop Slide Order Change', 'sp-news-and-widget'); ?><span class="wpnw-pro-tag"><?php esc_html_e('PRO','sp-news-and-widget');?></span>
			</th>
			<td>
				<span class="description"><?php esc_html_e('Arrange your desired slides with your desired order and display.' , 'sp-news-and-widget'); ?></span>
			</td>
		</tr>
		<tr class="wpnw-pro-feature">
			<th>
				<?php esc_html_e('Page Builder Support', 'sp-news-and-widget'); ?><span class="wpnw-pro-tag"><?php esc_html_e('PRO','sp-news-and-widget');?></span>
			</th>
			<td>
				<span class="description"><strong><?php esc_html_e('Gutenberg Block, Elementor, Bevear Builder, SiteOrigin, Divi, Visual Composer and Fusion Page Builder Support', 'sp-news-and-widget'); ?></strong></span>
			</td>
		</tr>
		<tr class="wpnw-pro-feature">
			<th>
				<?php esc_html_e('Exclude News and Exclude Some Categories', 'sp-news-and-widget'); ?> <span class="wpnw-pro-tag"><?php esc_html_e('PRO','sp-news-and-widget');?></span>
			</th>
			<td>
				<span class="description"><strong><?php esc_html_e('Do not display the news & Do not display the news for particular categories.' , 'sp-news-and-widget'); ?></strong></span>
			</td>
		</tr>
	</tbody>
</table><!-- end .wpnw-metabox-table -->

