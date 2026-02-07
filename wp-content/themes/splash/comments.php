<?php
if ( post_password_required() ) {
	return;
}

$avatarSize = (!splash_is_af()) ? 80 : 90;

?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) { ?>
		<h4 class="comments-title">
			<?php
			if(!splash_is_layout("sccr") && !splash_is_layout("baseball") && !splash_is_layout('magazine_one')) {
				comments_number();
			} elseif(splash_is_layout('magazine_one')) {
                comments_number("", "", "Comments (%)");
            } else {
				comments_number("", "", "comments (%)");
			}
			?>
		</h4>

		<ul class="comment-list stm-list-duty">
			<?php
			wp_list_comments( array(
				'style'       => 'ul',
				'short_ping'  => true,
				'avatar_size' => $avatarSize,
				'callback'    => 'splash_theme_comment'
			) );
			?>
		</ul>
		<div class="clearfix"></div>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) { ?>
			<nav class="navigation comment-navigation" role="navigation">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'splash' ); ?></h2>

				<div class="nav-links">
					<?php
					if ( $prev_link = get_previous_comments_link( esc_html__( 'Older Comments', 'splash' ) ) ) {
						printf( '<div class="nav-previous">%s</div>', $prev_link );
					}
					if ( $next_link = get_next_comments_link( esc_html__( 'Newer Comments', 'splash' ) ) ) {
						printf( '<div class="nav-next">%s</div>', $next_link );
					}
					?>
				</div>
			</nav>
		<?php } ?>

	<?php } ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) { ?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'splash' ); ?></p>
	<?php } ?>

	<?php comment_form( array(
		'title_reply' => __( 'Leave a comment', 'splash' ),
		'comment_notes_before' => '',
		'comment_notes_after'  => '',
	) ); ?>

</div>