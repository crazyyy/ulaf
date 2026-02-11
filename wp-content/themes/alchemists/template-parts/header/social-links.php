<?php
/**
 * Template part for Header Social Links section.
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.6.0
 */

$header_social_classes = array(
	'social-links',
	'social-links--inline',
	'social-links--main-nav'
);

if ( 'top_bar' == $header_social_pos ) {
	$header_social_classes[] = 'social-links--top-bar';
}

// Get all social media links
$social_media = is_array( $alchemists_data['alchemists__header-primary-social-links'] ) ? $alchemists_data['alchemists__header-primary-social-links'] : array();
?>

<!-- Social Links -->
<ul class="<?php echo esc_attr( implode(' ', $header_social_classes ) ); ?>">
	<?php foreach ( array_filter( $social_media ) as $key => $value) {

		echo '<li class="social-links__item">';

			switch($key) {

				case 'Facebook URL' :
					echo '<a href="' . esc_url( $social_media[ 'Facebook URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Facebook" target="_blank"><i class="fab fa-facebook"></i></a>';
				break;

				case 'Twitter URL':
					echo '<a href="' . esc_url( $social_media[ 'Twitter URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Twitter" target="_blank"><i class="fab fa-x-twitter"></i></a>';
				break;

				case 'LinkedIn URL':
					echo '<a href="' . esc_url( $social_media[ 'LinkedIn URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="LinkedIn" target="_blank"><i class="fab fa-linkedin"></i></a>';
				break;

				case 'Instagram URL':
					echo '<a href="' . esc_url( $social_media[ 'Instagram URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Instagram" target="_blank"><i class="fab fa-instagram"></i></a>';
				break;

				case 'Github URL':
					echo '<a href="' . esc_url( $social_media[ 'Github URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Github" target="_blank"><i class="fab fa-github"></i></a>';
				break;

				case 'VK URL':
					echo '<a href="' . esc_url( $social_media[ 'VK URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="VKontakte" target="_blank"><i class="fab fa-vk"></i></a>';
				break;

				case 'YouTube URL':
					echo '<a href="' . esc_url( $social_media[ 'YouTube URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="YouTube" target="_blank"><i class="fab fa-youtube"></i></a>';
				break;

				case 'Pinterest URL':
					echo '<a href="' . esc_url( $social_media[ 'Pinterest URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Pinterest" target="_blank"><i class="fab fa-pinterest"></i></a>';
				break;

				case 'Tumblr URL':
					echo '<a href="' . esc_url( $social_media[ 'Tumblr URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Tumblr" target="_blank"><i class="fab fa-tumblr"></i></a>';
				break;

				case 'Dribbble URL':
					echo '<a href="' . esc_url( $social_media[ 'Dribbble URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Dribbble" target="_blank"><i class="fab fa-dribbble"></i></a>';
				break;

				case 'Vimeo URL':
					echo '<a href="' . esc_url( $social_media[ 'Vimeo URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Vimeo" target="_blank"><i class="fab fa-vimeo"></i></a>';
				break;

				case 'Flickr URL':
					echo '<a href="' . esc_url( $social_media[ 'Flickr URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Flickr" target="_blank"><i class="fab fa-flickr"></i></a>';
				break;

				case 'Yelp URL':
					echo '<a href="' . esc_url( $social_media[ 'Yelp URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Yelp" target="_blank"><i class="fab fa-yelp"></i></a>';
				break;

				case 'Telegram URL':
					echo '<a href="' . esc_url( $social_media[ 'Telegram URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Telegram" target="_blank"><i class="fab fa-telegram-plane"></i></a>';
				break;

				case 'Snapchat URL':
					echo '<a href="' . esc_url( $social_media[ 'Snapchat URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Snapchat" target="_blank"><i class="fab fa-snapchat-ghost"></i></a>';
				break;

				case 'Twitch URL':
					echo '<a href="' . esc_url( $social_media[ 'Twitch URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Twitch" target="_blank"><i class="fab fa-twitch"></i></a>';
				break;

				case 'Faceit URL':
					echo '<a href="' . esc_url( $social_media[ 'Faceit URL' ] ) . '" class="social-links__link social-links__link--svg" data-toggle="tooltip" data-placement="bottom" title="Faceit" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24"><title>FACEIT icon</title><path d="M24 2.7c0-.1-.1-.2-.1-.2-.1 0-.1 0-.2.1-2 3.1-4.1 6.2-6.1 9.4H.2c-.2 0-.3.3-.1.4 7.2 2.7 17.7 6.8 23.5 9.1.2.1.4-.1.4-.2V2.7z"/></svg></a>';
				break;

				case 'Steam URL':
					echo '<a href="' . esc_url( $social_media[ 'Steam URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Steam" target="_blank"><i class="fab fa-steam"></i></a>';
				break;

				case 'Discord URL':
					echo '<a href="' . esc_url( $social_media[ 'Discord URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Discord" target="_blank"><i class="fab fa-discord"></i></a>';
				break;

				case 'Mixer URL':
					echo '<a href="' . esc_url( $social_media[ 'Mixer URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Mixer" target="_blank"><i class="fab fa-mixer"></i></a>';
				break;

				case 'TikTok URL':
					echo '<a href="' . esc_url( $social_media[ 'TikTok URL' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="TikTok" target="_blank"><i class="fab fa-tiktok"></i></a>';
				break;

				case 'Email':
					echo '<a href="mailto:' . sanitize_email( $social_media[ 'Email' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Email" target="_blank"><i class="fa fa-envelope"></i></a>';
				break;

				case 'RSS':
					echo '<a href="' . esc_url( $social_media[ 'RSS' ] ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="RSS" target="_blank"><i class="fa fa-rss"></i></a>';
				break;
			}

		echo '</li>';
	} ?>
</ul>
<!-- Social Links / End -->
