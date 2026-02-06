<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

if ( ! function_exists( 'alchemists_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function alchemists_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		esc_html_x( 'Posted on %s', 'post date', 'alchemists' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	$byline = sprintf(
		esc_html_x( 'by %s', 'post author', 'alchemists' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);

	echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.

}
endif;

if ( ! function_exists( 'alchemists_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function alchemists_entry_footer( $author_on = true, $date_on = false ) {
		$alchemists_data = get_option( 'alchemists_data' );
		$post_author        = isset( $alchemists_data['alchemists__blog-post-author'] ) ? $alchemists_data['alchemists__blog-post-author'] : true;
		$post_likes         = isset( $alchemists_data['alchemists__blog-post-likes'] ) ? $alchemists_data['alchemists__blog-post-likes'] : true;
		$post_views         = isset( $alchemists_data['alchemists__blog-post-views'] ) ? $alchemists_data['alchemists__blog-post-views'] : true;
		$post_comments      = isset( $alchemists_data['alchemists__blog-post-comments'] ) ? $alchemists_data['alchemists__blog-post-comments'] : true;

		if ( $author_on && $post_author ) {
			echo '<div class="post-author">';
				echo '<figure class="post-author__avatar">';
					echo get_avatar( get_the_author_meta('email'), '24' );
				echo '</figure>';
				echo '<div class="post-author__info">';
					echo '<h4 class="post-author__name">';
						echo get_the_author_meta('display_name');
					echo '</h4>';
				echo '</div>';
			echo '</div>';
		}

		echo '<div class="post__meta meta">';
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			// $categories_list = get_the_category_list( esc_html__( ', ', 'alchemists' ) );
			// if ( $categories_list && alchemists_categorized_blog() ) {
			// 	printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'alchemists' ) . '</span>', $categories_list ); // WPCS: XSS OK.
			// }

			/* translators: used between list items, there is a space after the comma */
			// $tags_list = get_the_tag_list( '', esc_html__( ', ', 'alchemists' ) );
			// if ( $tags_list ) {
			// 	printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'alchemists' ) . '</span>', $tags_list ); // WPCS: XSS OK.
			// }
		}


		if ( $date_on ) {
			// Post Date
			echo '<div class="meta__item meta__item--date meta__item--date-sm"><time datetime="' . esc_attr( get_the_time('c') ) . '" class="posts__date">' . get_the_time( get_option('date_format') ) . '</time></div>';
		}


		// Post Views
		if ( $post_views ) {
			if ( function_exists( 'alchemists_getPostViews' ) ) {
				echo alchemists_getPostViews( get_the_ID() );
			}
		}

		// Post likes
		if ( $post_likes ) {
			if ( function_exists( 'get_simple_likes_button') ) {
				echo get_simple_likes_button( get_the_ID() );
			}
		}

		// Post Comments
		if ( $post_comments ) {
			if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
				echo '<div class="meta__item meta__item--comments">';
				comments_popup_link( '0', '1', '%', '', '-' );
				echo '</div>';
			}
		}

		echo '</div>';
	}
endif;


if ( ! function_exists( 'alchemists_entry_meta_single' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function alchemists_entry_meta_single( $date = 'on', $custom_class = '') {

		$alchemists_data = get_option( 'alchemists_data' );
		$post_likes         = isset( $alchemists_data['alchemists__blog-post-likes'] ) ? $alchemists_data['alchemists__blog-post-likes'] : true;
		$post_views         = isset( $alchemists_data['alchemists__blog-post-views'] ) ? $alchemists_data['alchemists__blog-post-views'] : true;
		$post_comments      = isset( $alchemists_data['alchemists__blog-post-comments'] ) ? $alchemists_data['alchemists__blog-post-comments'] : true;

		echo '<div class="post__meta meta ' . esc_attr( $custom_class ) . '">';

			if ( $date != 'off') {
				// Post Date
				echo '<div class="meta__item meta__item--date"><time datetime="' . esc_attr( get_the_time('c') ) . '" class="posts__date">' . get_the_time( get_option('date_format') ) . '</time></div>';
			}

			// Post Views
			if ( $post_views ) {
				if ( function_exists( 'alchemists_getPostViews' ) ) {
					echo alchemists_getPostViews( get_the_ID() );
				}
			}

			// Post Likes
			if ( $post_likes ) {
				if ( function_exists( 'get_simple_likes_button') ) {
					echo get_simple_likes_button( get_the_ID() );
				}
			}

			// Post Comments
			if ( $post_comments ) {
				if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
					echo '<div class="meta__item meta__item--comments">';
					comments_popup_link( '0', '1', '%', '', '-' );
					echo '</div>';
				}
			}

		echo '</div>';
	}
endif;


if ( ! function_exists( 'alchemists_display_post_time') ) :
	/**
	 * Prints time for posts.
	 */
	function alchemists_display_post_time() {
		?>
		<time datetime="<?php esc_attr( the_time('c') ); ?>" class="posts__date"><?php the_time( get_option('date_format') ); ?></time>
		<?php
	}
endif;


/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function alchemists_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'alchemists_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'alchemists_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so alchemists_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so alchemists_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in alchemists_categorized_blog.
 */
function alchemists_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'alchemists_categories' );
}
add_action( 'edit_category', 'alchemists_category_transient_flusher' );
add_action( 'save_post',     'alchemists_category_transient_flusher' );




if ( ! function_exists( 'alchemists_entry_comments' ) ) {
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function alchemists_entry_comments() {
		if ( comments_open() || get_comments_number() ) {
			echo '<div class="meta__item meta__item--comments">';
			comments_popup_link( '0', '1', '%' );
			echo '</div>';
		}
	}
}



if(!function_exists('alchemists_pagination')) {
	/**
	 * Return HTML for blog pagination
	 */
	function alchemists_pagination($pages = '', $range = 2) {
		$showitems = ($range * 2)+1;

		global $paged;
		if (empty( $paged ) ) $paged = 1;

		if ( $pages == '' ) {
		global $wp_query;
		$pages = $wp_query->max_num_pages;
			if ( ! $pages ) {
				$pages = 1;
			}
		}

		// change styling depends on sports preset
		$pagination_classes = array( 'pagination', 'justify-content-center' );
		if ( alchemists_sp_preset( 'soccer' )) {
			array_push( $pagination_classes, 'pagination--condensed', 'pagination--lg' );
		} elseif ( alchemists_sp_preset( 'football') ) {
			$pagination_classes[] = 'pagination--lg';
		} elseif ( alchemists_sp_preset( 'esports') ) {
			$pagination_classes[] = 'pagination--circle';
		}

		if ( 1 != $pages ) {
		echo '<nav class="post-pagination"><ul class="' . esc_attr( implode( ' ', $pagination_classes ) ) . '">';
			// if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<li><a class='first' href='".get_pagenum_link(1)."'>First</a></li>";
			if ( $paged > 1) echo "<li><a href='".get_pagenum_link($paged - 1)."'><i class=\"fa fa-angle-left\"></i></a></li>";

			for ( $i=1; $i <= $pages; $i++ ) {
				if ( 1 != $pages && ( ! ( $i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ) ) { echo wp_kses_post( ( $paged == $i ) ? "<li class='active'><span>".$i."</span></li>":"<li><a href='" . get_pagenum_link($i) . "'>" . $i . "</a></li>" );
				}
			}

			if ( $paged < $pages ) echo "<li><a href=\"".get_pagenum_link($paged + 1)."\"><i class=\"fa fa-angle-right\"></i></a></li>";
			// if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<li><a class='last' href='".get_pagenum_link($pages)."'>Last</a></li>";
			echo '</ul></nav>';
		}
	}
}




if ( ! function_exists( 'alchemists_comments' ) ) {
	/**
	 * Return Custom Comments markup
	 */
	function alchemists_comments( $comment, $args, $depth) {
		$GLOBALS['comment'] = $comment;
		extract($args, EXTR_SKIP);

		$avatar_classes = array(
			'comment__author-avatar'
		);
		$avatar_size = 60;

		if ( alchemists_sp_preset( 'football') ) {
			array_push( $avatar_classes, 'comment__author-avatar--sm', 'comment__author-avatar--rounded' );
			$avatar_size = 40;
		}

	?>
		<li <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">

			<div id="comments__inner-<?php comment_ID() ?>" class="comments__inner">

				<header class="comment__header">
					<div class="comment__author">
						<?php if ( $args['avatar_size'] != 0 ) { ?>
							<figure class="<?php echo esc_attr( implode(' ', $avatar_classes ) ); ?>">
								<?php echo get_avatar( $comment, $avatar_size ); ?>
							</figure>
						<?php } ?>

						<div class="comment__author-info">
							<h5 class="comment__author-name"><?php comment_author(); ?></h5>
							<div class="comment__post-date">
								<a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
									<?php printf( __('%1$s', 'alchemists'), get_comment_date()) ?>
								</a>
								<?php edit_comment_link( esc_html__( '(Edit)', 'alchemists' ),'  ','' ); ?>
							</div>
						</div>
					</div>
					<div class="comment__reply">
						<?php comment_reply_link(array_merge( $args, array(
							'add_below'   => 'comment',
							'depth'       => $depth,
							'reply_text'  => '<span class="comment__reply-link btn btn-link btn-xs">' . esc_html__( 'Reply', 'alchemists' ) . '</span>',
							'max_depth'   => $args['max_depth']
						))) ?>
					</div>
				</header>

				<div class="comment__body">
					<?php comment_text() ?>
				</div>

				<?php if ( $comment->comment_approved == '0' ) : ?>
				<div class="comment-awaiting-moderation alert alert-warning"><?php esc_html_e( 'Your comment is awaiting moderation.', 'alchemists' ) ?></div>
				<?php endif; ?>

			</div>
		</li>
	<?php }
}



if ( ! function_exists( 'alchemists_comments_simplified' ) ) :
	/**
	 * Comments Simplified markup
	 */
	function alchemists_comments_simplified( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		extract($args, EXTR_SKIP);

		$avatar_classes = array(
			'comment__author-avatar',
			'comment__author-avatar--md'
		);
		$avatar_size = 46;
		?>

		<li <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">

			<div id="comments__inner-<?php comment_ID() ?>" class="comments__inner">
				<header class="comment__header">
					<div class="comment__author">
						<?php if ( $args['avatar_size'] != 0 ) { ?>
						<figure class="<?php echo esc_attr( implode(' ', $avatar_classes ) ); ?>">
							<?php echo get_avatar( $comment, $avatar_size ); ?>
						</figure>
						<?php } ?>
					</div>
				</header>
				<div class="comment__inner-wrap">
					<div class="comment__author-info">
						<h5 class="comment__author-name"><?php comment_author(); ?></h5>
						<div class="comment__post-date">
							<a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
								<?php printf( __('%1$s', 'alchemists'), get_comment_date()) ?>
							</a>
							<?php edit_comment_link( esc_html__( '(Edit)', 'alchemists' ),'  ','' ); ?>
						</div>
					</div>
					<div class="comment__body">
						<?php comment_text() ?>
					</div>
					<div class="comment__reply">
						<?php comment_reply_link(array_merge( $args, array(
							'add_below'   => 'comment',
							'depth'       => $depth,
							'reply_text'  => '<span class="comment__reply-link">' . esc_html__( 'Reply', 'alchemists' ) . '</span>',
							'max_depth'   => $args['max_depth']
						))) ?>
					</div>
					<?php if ( $comment->comment_approved == '0' ) : ?>
						<div class="comment-awaiting-moderation alert alert-warning mt-20"><?php esc_html_e( 'Your comment is awaiting moderation.', 'alchemists' ) ?></div>
						<?php endif; ?>
				</div>

			</div>

		</li>
	<?php
	}
endif;



/**
 * Post Category CSS class.
 */
if( !function_exists( 'alchemists_post_category_class' ) ) {
	function alchemists_post_category_class() {

		$alchemists_data = get_option( 'alchemists_data' );

		$alchemists_category_1 = isset( $alchemists_data['alchemists__categories-group-1'] ) ? $alchemists_data['alchemists__categories-group-1'] : array();
		$alchemists_category_2 = isset( $alchemists_data['alchemists__categories-group-2'] ) ? $alchemists_data['alchemists__categories-group-2'] : array();
		$alchemists_category_3 = isset( $alchemists_data['alchemists__categories-group-3'] ) ? $alchemists_data['alchemists__categories-group-3'] : array();
		$alchemists_category_4 = isset( $alchemists_data['alchemists__categories-group-4'] ) ? $alchemists_data['alchemists__categories-group-4'] : array();
		$alchemists_category_5 = isset( $alchemists_data['alchemists__categories-group-5'] ) ? $alchemists_data['alchemists__categories-group-5'] : array();
		$alchemists_category_6 = isset( $alchemists_data['alchemists__categories-group-6'] ) ? $alchemists_data['alchemists__categories-group-6'] : array();
		$alchemists_category_7 = isset( $alchemists_data['alchemists__categories-group-7'] ) ? $alchemists_data['alchemists__categories-group-7'] : array();
		$alchemists_category_8 = isset( $alchemists_data['alchemists__categories-group-8'] ) ? $alchemists_data['alchemists__categories-group-8'] : array();
		$alchemists_category_9 = isset( $alchemists_data['alchemists__categories-group-9'] ) ? $alchemists_data['alchemists__categories-group-9'] : array();
		$alchemists_category_10 = isset( $alchemists_data['alchemists__categories-group-10'] ) ? $alchemists_data['alchemists__categories-group-10'] : array();
		$alchemists_category_11 = isset( $alchemists_data['alchemists__categories-group-11'] ) ? $alchemists_data['alchemists__categories-group-11'] : array();
		$alchemists_category_12 = isset( $alchemists_data['alchemists__categories-group-12'] ) ? $alchemists_data['alchemists__categories-group-12'] : array();

		// get all categories
		$categories      = wp_get_post_terms( get_the_ID(), 'category' );
		$the_category_id = '';
		if ( ! empty( $categories ) ) {
			$the_category_id = $categories[0]->term_id; // get only 1st category as primary
		}

		// set post class by default
		$post_class  = 'posts__item--category-1';

		// check if category assigned to category group (see Theme Options > Blog & Posts > Posts)
		if ( ! empty( $the_category_id ) ) {
			if ( in_array( $the_category_id, $alchemists_category_1 ) ) {
				$post_class = 'posts__item--category-1';
			} elseif ( in_array( $the_category_id, $alchemists_category_2 ) ) {
				$post_class = 'posts__item--category-2';
			} elseif ( in_array( $the_category_id, $alchemists_category_3 ) ) {
				$post_class = 'posts__item--category-3';
			} elseif ( in_array( $the_category_id, $alchemists_category_4 ) ) {
				$post_class = 'posts__item--category-4';
			} elseif ( in_array( $the_category_id, $alchemists_category_5 ) ) {
				$post_class = 'posts__item--category-5';
			} elseif ( in_array( $the_category_id, $alchemists_category_6 ) ) {
				$post_class = 'posts__item--category-6';
			} elseif ( in_array( $the_category_id, $alchemists_category_7 ) ) {
				$post_class = 'posts__item--category-7';
			} elseif ( in_array( $the_category_id, $alchemists_category_8 ) ) {
				$post_class = 'posts__item--category-8';
			} elseif ( in_array( $the_category_id, $alchemists_category_9 ) ) {
				$post_class = 'posts__item--category-9';
			} elseif ( in_array( $the_category_id, $alchemists_category_10 ) ) {
				$post_class = 'posts__item--category-10';
			} elseif ( in_array( $the_category_id, $alchemists_category_11 ) ) {
				$post_class = 'posts__item--category-11';
			} elseif ( in_array( $the_category_id, $alchemists_category_12 ) ) {
				$post_class = 'posts__item--category-12';
			}
		}

		return $post_class;

	}
}


/**
 * Output Post Category labels.
 */

if( !function_exists( 'alchemists_post_category_labels' ) ) {
	function alchemists_post_category_labels( $wrap_class = 'posts__cat', $taxonomy = 'category' ) {

		$alchemists_data = get_option( 'alchemists_data' );

		$alchemists_category_1 = isset( $alchemists_data['alchemists__categories-group-1'] ) ? $alchemists_data['alchemists__categories-group-1'] : array();
		$alchemists_category_2 = isset( $alchemists_data['alchemists__categories-group-2'] ) ? $alchemists_data['alchemists__categories-group-2'] : array();
		$alchemists_category_3 = isset( $alchemists_data['alchemists__categories-group-3'] ) ? $alchemists_data['alchemists__categories-group-3'] : array();
		$alchemists_category_4 = isset( $alchemists_data['alchemists__categories-group-4'] ) ? $alchemists_data['alchemists__categories-group-4'] : array();
		$alchemists_category_5 = isset( $alchemists_data['alchemists__categories-group-5'] ) ? $alchemists_data['alchemists__categories-group-5'] : array();
		$alchemists_category_6 = isset( $alchemists_data['alchemists__categories-group-6'] ) ? $alchemists_data['alchemists__categories-group-6'] : array();
		$alchemists_category_7 = isset( $alchemists_data['alchemists__categories-group-7'] ) ? $alchemists_data['alchemists__categories-group-7'] : array();
		$alchemists_category_8 = isset( $alchemists_data['alchemists__categories-group-8'] ) ? $alchemists_data['alchemists__categories-group-8'] : array();
		$alchemists_category_9 = isset( $alchemists_data['alchemists__categories-group-9'] ) ? $alchemists_data['alchemists__categories-group-9'] : array();
		$alchemists_category_10 = isset( $alchemists_data['alchemists__categories-group-10'] ) ? $alchemists_data['alchemists__categories-group-10'] : array();
		$alchemists_category_11 = isset( $alchemists_data['alchemists__categories-group-11'] ) ? $alchemists_data['alchemists__categories-group-11'] : array();
		$alchemists_category_12 = isset( $alchemists_data['alchemists__categories-group-12'] ) ? $alchemists_data['alchemists__categories-group-12'] : array();


		// get all categories
		$categories      = wp_get_post_terms( get_the_ID(), $taxonomy );

		echo '<div class="' . esc_attr( $wrap_class ) . '">';
			foreach ( $categories as $category ) {

				$label_class = 'posts__cat-label--category-1';
				$category_id   = $category->term_id;

				if ( in_array( $category_id, $alchemists_category_2 ) ) {
					$label_class = 'posts__cat-label--category-2';
				} elseif ( in_array( $category_id, $alchemists_category_3 ) ) {
					$label_class = 'posts__cat-label--category-3';
				} elseif ( in_array( $category_id, $alchemists_category_4 ) ) {
					$label_class = 'posts__cat-label--category-4';
				} elseif ( in_array( $category_id, $alchemists_category_5 ) ) {
					$label_class = 'posts__cat-label--category-5';
				} elseif ( in_array( $category_id, $alchemists_category_6 ) ) {
					$label_class = 'posts__cat-label--category-6';
				} elseif ( in_array( $category_id, $alchemists_category_7 ) ) {
					$label_class = 'posts__cat-label--category-7';
				} elseif ( in_array( $category_id, $alchemists_category_8 ) ) {
					$label_class = 'posts__cat-label--category-8';
				} elseif ( in_array( $category_id, $alchemists_category_9 ) ) {
					$label_class = 'posts__cat-label--category-9';
				} elseif ( in_array( $category_id, $alchemists_category_10 ) ) {
					$label_class = 'posts__cat-label--category-10';
				} elseif ( in_array( $category_id, $alchemists_category_11 ) ) {
					$label_class = 'posts__cat-label--category-11';
				} elseif ( in_array( $category_id, $alchemists_category_12 ) ) {
					$label_class = 'posts__cat-label--category-12';
				}

				echo '<span class="label posts__cat-label ' . esc_attr( $label_class ) . ' ' . esc_attr( $taxonomy . '-' . $category->slug ) . '">' . esc_html( $category->name ) . '</span>';
			}
		echo '</div>';
	}
}


/**
 * Page Links
 */
if ( ! function_exists( 'alchemists_page_links' ) ) {
	function alchemists_page_links() {
		wp_link_pages( array(
			'before' => '<div class="page-links">' . '<h6 class="page-links__title">' . esc_html__( 'Pages:', 'alchemists' ) . '</h6>',
			'after'  => '</div>',
			'link_before' => '<span class="btn btn-default btn-sm">',
			'link_after'  => '</span>',
		) );
	}
}


/**
 * Post Tags
 */
if ( ! function_exists( 'alchemists_post_tags' ) ) {
	function alchemists_post_tags( $post = 0, $post_tags_type = 'buttons' ) {

		$terms = get_the_terms( $post->ID, 'post_tag' );
		$post_tags_classes = array( 'post__tags' );

		if ( 'hashtags' == $post_tags_type ) {
			$post_tags_classes[] = 'post__tags--simple';
			$post_tag_classes = array( );
		} else {
			$post_tag_classes = array(
				'btn',
				'btn-primary',
				'btn-outline',
				'btn-xs',
			);
		}

		$post_tags_classes = esc_attr( implode( ' ', $post_tags_classes ) );
		$post_tag_classes = esc_attr( implode( ' ', $post_tag_classes ) );

		if ( $terms && ! is_wp_error( $terms ) ) :
		?>
			<div class="<?php echo esc_attr( $post_tags_classes ); ?>">
				<?php foreach( $terms as $term) : ?>
					<a href="<?php echo esc_url( get_term_link( $term->slug, 'post_tag') ); ?>" rel="tag" class="tag-link-<?php echo esc_attr( $term->slug ); ?> <?php echo esc_attr( $post_tag_classes ); ?>"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php
		endif;
	}
}
