<?php
/**
 * The template for displaying Video Post Formats
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 */

const VIDEO_EMBED_TYPE_LINK = 'link';
const VIDEO_EMBED_TYPE_CODE = 'code';

$alchemists_data = get_option( 'alchemists_data' );
$post_sidebar      = isset( $alchemists_data['alchemists__single-post-sidebar'] ) ? $alchemists_data['alchemists__single-post-sidebar'] : 1;
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;

// Post layout, in case the news contains video separately from the main content.
// Process only the selected template, ignoring the default value to prevent unexpected behavior
$post_layout        = isset( $alchemists_data['alchemists__opt-single-post-layout'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-layout'] ) : '1';
$post_layout_get    = isset( $_GET['single_post'] ) ? sanitize_key( $_GET['single_post'] ) : '';
$custom_post_layout = get_field( 'post_layout' );

if ( $custom_post_layout ) {
	$custom_post_layout = preg_replace( '/[^0-9.]+/', '', $custom_post_layout );
}

if ( $post_layout_get ) {
	$post_layout = $post_layout_get;
} elseif ( $custom_post_layout ) {
	$post_layout = $custom_post_layout;
}

// Check if the selected template is supported.
if ( ! in_array( $post_layout, array( '1', '2', '4', '6' ) ) ) {
	$post_layout = '1';
}


// Content
$content_width = 'col-lg-8';

// Sidebar
$sidebar_width = 'col-lg-4';

// Sidebar Width (position)
if ( $post_sidebar == 2 ) {
	$content_width = 'col-lg-8 order-lg-2';
	$sidebar_width = 'col-lg-4 order-lg-1';
} elseif ( $post_sidebar == 3 ) {
	$content_width = 'col-lg-12';
}
?>

<?php do_action( 'alc_site_content_before' ); ?>
<div class="site-content" id="content">
	<div class="container">

		<!-- Video Player block -->
		<div class="card alc-video-playlist">
			<div class="row">
				<div class="col-lg-8 alc-video-player-col">
					<div class="card__content">

						<!-- Video Player -->
						<div class="alc-video-player js-alc-video-player">
							<div class="alc-video-player__item">
								<?php
								echo '<div class="alc-embeded-player--container">';
									if ( get_field( 'alc-posts-videos-enabled' ) && have_rows( 'alc-posts-videos' ) ) :
										while ( have_rows( 'alc-posts-videos' ) ) : the_row();
											$type = get_sub_field( 'alc-posts-videos-type' );
											if ( $type === VIDEO_EMBED_TYPE_LINK ) :
												the_sub_field( 'alc-posts-videos-embed' );
											else :
												the_sub_field( 'alc-posts-videos-embed-code' );
											endif;
										endwhile;
									else :
										while ( have_posts() ) : the_post();
											the_content();
										endwhile;
									endif;
								echo '</div>';
								?>
							</div>
						</div>
					</div>
					<!-- Video Player / End -->

				</div>

				<!-- Video Playlist -->
				<div class="col-lg-4 alc-video-playlist-col">
					<div class="alc-video-player__header js-alc-video-player__header slick-vertical slick-arrows--rounded">
						<h4 class="alc-video-player__title"><?php esc_html_e( 'Recent Videos', 'alchemists' ); ?></h4>
					</div>
					<?php
					// Posts arguments
					$video_posts = get_posts( array(
						'post_type'           => 'post',
						'post_status'         => 'publish',
						'posts_per_page'      => 10,
						'ignore_sticky_posts' => 1,
						'tax_query' => array(
							array(
								'taxonomy' => 'post_format',
								'field' => 'slug',
								'terms' => 'post-format-video'
							)
						),
						'no_found_rows'       => true,
						'suppress_filters'    => false,
					) );
					?>

					<ul class="posts posts--simple-list alc-video-player__video-list">
						<?php
						foreach ( $video_posts as $post ) :
							setup_postdata( $post );

							$post_class = alchemists_post_category_class();
							$post_classes = array(
								'posts__item',
								$post_class
							);
							?>

							<li <?php post_class( $post_classes ); ?>>
								<figure class="posts__thumb posts__thumb--video posts__thumb--video-sm">
									<?php
									echo '<a href="' . esc_url( get_permalink() ) . '">';
										if ( has_post_thumbnail() ) {
											the_post_thumbnail( 'alchemists_thumbnail-xs-wide-alt' );
										} else {
											echo '<img src="' . get_theme_file_uri( '/assets/images/placeholder-112x84.jpg' ) . '" alt="">';
										}
									echo '</a>';
									?>
								</figure>
								<div class="posts__inner">
									<?php
									if ( $categories_toggle ) {
										alchemists_post_category_labels();
									}
									?>
									<?php the_title( '<h6 class="posts__title posts__title--color-hover"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h6>' ); ?>
									<time datetime="<?php echo esc_attr( get_the_time('c') ); ?>" class="posts__date"><?php echo get_the_time( get_option('date_format') ); ?></time>
								</div>
							</li>

						<?php
						endforeach;
						wp_reset_postdata();
						?>
					</ul>

					<script>
						(function($){
							$(function() {
								var slick_team_video_player_list = $('.alc-video-player__video-list');

								// Team Cards Compact - Slider
								slick_team_video_player_list.slick({
									slidesToShow: 3,
									slidesToScroll: 1,
									autoplay: true,
									autoplaySpeed: 8000,
									dots: false,
									appendArrows: $('.js-alc-video-player__header'),
									focusOnSelect: true,
									centerMode: false,
									vertical: true,
									verticalSwiping: true,
									rows: 0,
									responsive: [
										{
											breakpoint: 1200,
											settings: {
												slidesToShow: 2
											}
										},
										{
											breakpoint: 992,
											settings: {
												arrows: false,
												slidesToShow: 3
											}
										}
									]
								});

							});
						})(jQuery);
					</script>

				</div>
				<!-- Video Playlist / End -->

			</div>
		</div>
		<!-- Video Playlist block / End -->

		<div class="row">
			<div id="primary" class="content-area <?php echo esc_attr( $content_width ); ?>">

				<?php
				while ( have_posts() ) : the_post();

					if ( get_field( 'alc-posts-videos-enabled' ) && have_rows( 'alc-posts-videos' ) ) {

						// Select a layout depends on Post Options
						get_template_part( 'template-parts/content', 'single' . ( $post_layout === '1' ? '' : '-' . $post_layout ) );

						if ( $post_layout === '1' || $post_layout === '6' ) {

							// Post Social Sharing
							if ( function_exists( 'alc_post_social_share_buttons' ) ) {
								alc_post_social_share_buttons();
							}

						}

					} else {
					?>

						<!-- Article -->
						<article class="card post post--single post--single-sm">
							<div class="card__header card__header--no-highlight d-block">
								<?php
								if ( $categories_toggle ) {
									alchemists_post_category_labels( 'post__category' );
								}
								?>
								<header class="post__header">
									<?php
									the_title( '<h2 class="post__title">', '</h2>' );
									alchemists_entry_meta_single();
									?>
								</header>
							</div>
							<div class="card__content">
								<?php
								// Post Sharing
								if ( function_exists( 'alc_post_social_share_buttons_icons' ) ) {
									alc_post_social_share_buttons_icons( 'post-sharing-compact--footer' );
								}
								?>
							</div>
						</article>
						<!-- Article / End -->

					<?php } ?>

					<?php
					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template( '/template-parts/post/comments/comments-alt-card.php' );
					endif;
					?>

				<?php
				endwhile; // End of the loop.
				?>

			</div><!-- #primary -->


			<?php if ( $post_sidebar != 3 ) : ?>
			<aside id="secondary" class="sidebar widget-area <?php echo esc_attr( $sidebar_width ); ?>">
				<?php get_sidebar(); ?>
			</aside><!-- #secondary -->
			<?php endif; ?>

		</div>
	</div>
</div>
<?php do_action( 'alc_site_content_after' ); ?>
