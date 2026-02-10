<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.0.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

// You can start editing here -- including this comment!
if ( have_comments() ) :

	$post_commments_classes = array(
		'post-comments',
	);

	?>
	<div id="comments" class="<?php echo esc_attr( implode(' ', $post_commments_classes) ); ?>">

		<header class="post-commments__header">
			<h4><?php echo get_comments_number() . ' ' . _n( 'Comment', 'Comments', get_comments_number(), 'alchemists' ); ?></h4>
		</header><!-- .post-commments__header -->

		<div class="post-comments__content pb-0">

			<ol class="comments comments--alt">
				<?php wp_list_comments('type=all&callback=alchemists_comments_simplified'); ?>
			</ol><!-- .comments -->

			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<nav class="comment-navigation post__comments-pagination" role="navigation">
				<?php
				$args = array(
					'format' => '',
					'prev_text' => '<i class="fa fa-angle-left"></i>',
					'next_text' => '<i class="fa fa-angle-right"></i>'
				);
				paginate_comments_links( $args ); ?>
			</nav><!-- #comment-nav-below -->

			<?php endif; // Check for comment navigation.
			?>
		</div>

	</div><!-- #comments -->
<?php
endif; // Check for have_comments().
?>

<div class="spacer-lg"></div>

<?php
// If comments are closed and there are comments, let's leave a little note, shall we?
if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>

	<div class="alert alert-warning no-comments"><?php esc_html_e( 'Comments are closed.', 'alchemists' ); ?></div>

<?php
endif;
?>

<?php
// Comment Form Classes
$comment_form_classes = array( 'post-comment-form', 'post-comment-form--simplified' );
$form_submit_classes = array(
	'btn',
	'btn-primary-inverse'
);
?>

<!-- Comment Form -->
<div class="<?php echo esc_attr( implode( ' ', $comment_form_classes ) ); ?>">
	<?php
		$commenter = wp_get_current_commenter();
		$req       = get_option( 'require_name_email' );
		$aria_req  = ( $req ? " aria-required=true" : '' );
		$field_req = $req ? "<span class='required'>*</span>" : '';
		$consent   = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';

		$comments_args = array(
			'id_form'              => 'commentform',
			'id_submit'            => 'submit',
			'class_form'           => 'post-comment-form__content',
			'class_submit'         => implode( ' ', $form_submit_classes ),
			'title_reply_before'   => '<header class="post-comment-form__header"><h4>',
			'title_reply_after'    => '</h4></header>',
			'title_reply'          => esc_html__( 'Leave a Reply', 'alchemists' ),
			'title_reply_to'       => esc_html__( 'Leave a Reply to %s', 'alchemists' ),
			'cancel_reply_link'    => esc_html__( 'Cancel Reply', 'alchemists' ),
			'label_submit'         => esc_html__( 'Post Your Comment', 'alchemists' ),

			'comment_notes_before' => '',
			'comment_notes_after'  => '',
			'must_log_in'          => '<div class="alert alert-warning">' .  sprintf( wp_kses( __( 'You must be <a href="%s">logged in</a> to post a comment.', 'alchemists' ), array('a' => array( 'href' => array() ))), wp_login_url( apply_filters( 'the_permalink', get_permalink( ) ) ) ) . '</div>',

			'fields' => apply_filters( 'comment_form_default_fields', array(

				'author' =>
					'<div class="row">' .
					'<div class="col-lg-6">' .
					'<div class="comment-form-author form-group">' .
					'<label class="control-label" for="author">' . esc_attr__( 'Your Name', 'alchemists' ) . ' ' . $field_req . '</label>' .
					'<input id="author" name="author" type="text" class="form-control" value="' . esc_attr( $commenter['comment_author'] ) .
					'" size="30"' . esc_attr( $aria_req ) . ' /></div>' .
					'</div>',

				'email' =>
					'<div class="col-lg-6">' .
					'<div class="comment-form-email form-group">' .
					'<label class="control-label" for="email">' . esc_attr__( 'Email Address', 'alchemists' ) . ' ' . $field_req . '</label>' .
					'<input id="email" name="email" type="email" class="form-control" value="' . esc_attr( $commenter['comment_author_email'] ) .
					'" size="30"' . esc_attr( $aria_req ) . ' /></div>' .
					'</div>' .
					'</div>',

					'cookies' =>
						'<p class="comment-form-cookies-consent"><label class="checkbox checkbox-inline" for="wp-comment-cookies-consent">' .
							'<input type="checkbox" name="wp-comment-cookies-consent" type="checkbox" id="wp-comment-cookies-consent" value="yes"' . $consent . '>' .
							esc_html__( 'Save my name and email in this browser for the next time I comment.', 'alchemists' ) .
							'<span class="checkbox-indicator"></span>' .
						'</label></p>'
				)
			),
			'comment_field'        =>
				'<div class="comment-form-message form-group">' .
				'<label class="control-label" for="comment">' . esc_attr__( 'Your Comment', 'alchemists' ) . ' ' . $field_req . '</label>' .
				'<textarea id="comment" name="comment" cols="30" rows="7" class="form-control" aria-required="true">' .
				'</textarea>' .
				'</div>',
		);
		comment_form($comments_args);
	?>
</div>
<!-- Comment Form / End -->
