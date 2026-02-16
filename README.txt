- Add basic Open Graph Tags
<meta property="og:title" content="<?php echo esc_attr( get_the_title() ); ?>"/>
<meta property="og:description" content="<?php echo esc_attr( get_the_excerpt() ); ?>"/>
<meta property="og:url" content="<?php echo esc_attr( get_permalink() ); ?>"/>
<meta property="og:type" content="article"/>
<?php if ( has_post_thumbnail() ) : ?>
<meta property="og:image" content="<?php echo esc_attr( get_the_post_thumbnail_url() ); ?>"/>
<?php endif; ?>

- Add Featured Images to RSS Feeds
/**
 * Add the post thumbnail, if available, before the content in feeds.
 *
 * @param string $content The post content.
 *
 * @return string
 */
function wpcode_snippet_rss_post_thumbnail( $content ) {
	global $post;
	if ( has_post_thumbnail( $post->ID ) ) {
		$content = '<p>' . get_the_post_thumbnail( $post->ID ) . '</p>' . $content;
	}

	return $content;
}

add_filter( 'the_excerpt_rss', 'wpcode_snippet_rss_post_thumbnail' );
add_filter( 'the_content_feed', 'wpcode_snippet_rss_post_thumbnail' );

- Replace WordPress Logo on Login Page
add_filter( 'login_head', function () {
	// Update the line below with the URL to your own logo.
	// Adjust the Width & Height accordingly.
	$custom_logo = 'https://ulaf.com.ua/wp-content/uploads/2026/02/ULAF-on-white.avif';
	$logo_width  = 84;
	$logo_height = 84;

	printf(
		'<style>.login h1 a {background-image:url(%1$s) !important; margin:0 auto; width: %2$spx; height: %3$spx; background-size: 100%%;}</style>',
		$custom_logo,
		$logo_width,
		$logo_height
	);
}, 990 );

- Add Media File Size Column
add_filter( 'manage_upload_columns', function ( $columns ) {
	$columns ['file_size'] = esc_html__( 'File size' );

	return $columns;
} );

add_action( 'manage_media_custom_column', function ( $column_name, $media_item ) {
	if ( 'file_size' !== $column_name || ! wp_attachment_is_image( $media_item ) ) {
		return;
	}
	$filesize = size_format( filesize( get_attached_file( $media_item ) ), 2 );
	echo esc_html( $filesize );

}, 10, 2 );


- Change Editor Default Image Size
add_filter( 'block_editor_settings_all', function ( $settings, $context ) {
	// The default image size when added in the block editor.
	$settings['imageDefaultSize'] = 'full';

	return $settings;
}, 10, 2 );



- Add default ALT to avatar/Gravatar Images
```
add_filter(
	'pre_get_avatar_data',
	function ( $atts ) {
		if ( empty( $atts['alt'] ) ) {
			if ( have_comments() ) {
				$author = get_comment_author();
			} else {
				$author = get_the_author_meta( 'display_name' );
			}
			$alt = sprintf( 'Avatar for %s', $author );

			$atts['alt'] = $alt;
		}
		return $atts;
	}
);
```

- Add Auto Sizes to Lazy Loaded images
add_filter( 'wp_get_attachment_image_attributes', function( $attr ) {
	if ( ! isset( $attr['loading'] ) || 'lazy' !== $attr['loading'] || ! isset( $attr['sizes'] ) ) {
		return $attr;
	}

	// Skip if attribute was already added.
	if ( false !== strpos( $attr['sizes'], 'auto,' ) ) {
		return $attr;
	}

	$attr['sizes'] = 'auto, ' . $attr['sizes'];

	return $attr;
} );

add_filter( 'wp_content_img_tag',  function( $html ) {
	if (false === strpos($html, 'loading="lazy"') || (false === strpos($html, 'sizes="') || false !== strpos($html, 'sizes="auto,'))) {
		return $html;
	}

	$html = str_replace( 'sizes="', 'sizes="auto, ', $html );

	return $html;
} );


- Open External Links in a New Tab
add_filter( 'the_content', function ( $content ) {
	// This snippet requires the DOMDocument class to be available.
	if ( ! class_exists( 'DOMDocument' ) ) {
		return $content;
	}
	if ( !is_single() || !in_the_loop() || !is_main_query() ) {
		return $content;
	}

	$dom          = new DOMDocument();
	$load_content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
	if ( empty( $load_content ) ) {
		return $content;
	}
	@$dom->loadHTML( $load_content );
	$links = $dom->getElementsByTagName( 'a' );

	foreach ( $links as $link ) {
		if ( strpos( $link->getAttribute( 'href' ), home_url() ) !== false ) {
			continue;
		}
		$old_link = $link->C14N();
		$link->setAttribute( 'target', '_blank' );
		$link->setAttribute( 'rel', 'noopener noreferrer' );

		$content = str_replace( $old_link, $link->C14N(), $content );
	}

	return $content;
} );

- Markdown URLs for LLMs
/**
 * After enabling this snippet, go to Settings > Permalinks and click "Save Changes"
 * Configure the $enabled_post_types array to specify which post types should support .md URLs
 */
$enabled_post_types = [ 'post', 'page' ];


// Add rewrite rule to catch .md URLs
add_action( 'init', function () {
	add_rewrite_rule(
		'(.+?)\.md$',
		'index.php?markdown_url=$matches[1]',
		'top'
	);
} );

// Add custom query variable
add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'markdown_url';

	return $vars;
} );

// Handle the markdown request
add_action( 'template_redirect', function () use ( $enabled_post_types ) {
	$markdown_url = get_query_var( 'markdown_url' );

	if ( ! $markdown_url ) {
		return;
	}

	// Try to find the post by URL
	$post = ( function ( $url ) use ( $enabled_post_types ) {
		// Remove leading slash if present
		$url = ltrim( $url, '/' );

		// Try to get post by URL path
		$post_id = url_to_postid( '/' . $url );
		if ( $post_id ) {
			return get_post( $post_id );
		}

		// If that doesn't work, try by post name/slug
		foreach ( $enabled_post_types as $post_type ) {
			// Extract just the slug (last part after last slash)
			$slug = basename( $url );

			$posts = get_posts( [
				'name'        => $slug,
				'post_type'   => $post_type,
				'post_status' => 'publish',
				'numberposts' => 1
			] );

			if ( ! empty( $posts ) ) {
				return $posts[0];
			}
		}

		return null;
	} )( $markdown_url );

	if ( ! $post || ! in_array( $post->post_type, $enabled_post_types ) ) {
		status_header( 404 );
		nocache_headers();
		echo "Post not found or markdown not enabled for this post type.";
		exit;
	}

	// Generate markdown content
	$markdown_content = '';
	// Add title
	$markdown_content .= '# ' . get_the_title( $post ) . "\n\n";
	// Add metadata
	$markdown_content .= '**Published:** ' . get_the_date( 'F j, Y', $post ) . "\n";
	$markdown_content .= '**Author:** ' . get_the_author_meta( 'display_name', $post->post_author ) . "\n";
	// Add categories for posts
	if ( $post->post_type === 'post' ) {
		$categories = get_the_category( $post->ID );
		if ( ! empty( $categories ) ) {
			$cat_names        = array_map( function ( $cat ) {
				return $cat->name;
			}, $categories );
			$markdown_content .= '**Categories:** ' . implode( ', ', $cat_names ) . "\n";
		}
		// Add tags for posts
		$tags = get_the_tags( $post->ID );
		if ( ! empty( $tags ) ) {
			$tag_names        = array_map( function ( $tag ) {
				return $tag->name;
			}, $tags );
			$markdown_content .= '**Tags:** ' . implode( ', ', $tag_names ) . "\n";
		}
	}
	$markdown_content .= "\n---\n\n";
	// Convert HTML content to markdown-friendly format
	$post_content = apply_filters( 'the_content', $post->post_content );
	// Basic HTML to Markdown conversion
	$post_content     = ( function ( $html ) {
		$html = trim( preg_replace( '/\s+/', ' ', $html ) );
		$html = preg_replace( '/<h1[^>]*>(.*?)<\/h1>/i', "\n# $1\n", $html );
		$html = preg_replace( '/<h2[^>]*>(.*?)<\/h2>/i', "\n## $1\n", $html );
		$html = preg_replace( '/<h3[^>]*>(.*?)<\/h3>/i', "\n### $1\n", $html );
		$html = preg_replace( '/<h4[^>]*>(.*?)<\/h4>/i', "\n#### $1\n", $html );
		$html = preg_replace( '/<h5[^>]*>(.*?)<\/h5>/i', "\n##### $1\n", $html );
		$html = preg_replace( '/<h6[^>]*>(.*?)<\/h6>/i', "\n###### $1\n", $html );
		$html = preg_replace( '/<p[^>]*>(.*?)<\/p>/i', "$1\n\n", $html );
		$html = str_replace( [ '<br>', '<br/>', '<br />' ], "\n", $html );
		$html = preg_replace( '/<(strong|b)[^>]*>(.*?)<\/\1>/i', "**$2**", $html );
		$html = preg_replace( '/<(em|i)[^>]*>(.*?)<\/\1>/i', "*$2*", $html );
		$html = preg_replace( '/<a[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/i', "[$2]($1)", $html );
		$html = preg_replace( '/<img[^>]*src=["\']([^"\']*)["\'][^>]*alt=["\']([^"\']*)["\'][^>]*\/?>/i', "![$2]($1)", $html );
		$html = preg_replace( '/<img[^>]*alt=["\']([^"\']*)["\'][^>]*src=["\']([^"\']*)["\'][^>]*\/?>/i', "![$1]($2)", $html );
		$html = preg_replace( '/<img[^>]*src=["\']([^"\']*)["\'][^>]*\/?>/i', "![]($1)", $html );
		$html = preg_replace( '/<ul[^>]*>/i', "", $html );
		$html = preg_replace( '/<\/ul>/i', "\n", $html );
		$html = preg_replace( '/<ol[^>]*>/i', "", $html );
		$html = preg_replace( '/<\/ol>/i', "\n", $html );
		$html = preg_replace( '/<li[^>]*>(.*?)<\/li>/i', "- $1\n", $html );
		$html = preg_replace_callback( '/<blockquote[^>]*>(.*?)<\/blockquote>/is', function ( $matches ) {
			$lines  = explode( "\n", trim( $matches[1] ) );
			$quoted = array_map( function ( $line ) {
				return '> ' . trim( $line );
			}, $lines );

			return "\n" . implode( "\n", $quoted ) . "\n\n";
		}, $html );
		$html = preg_replace_callback( '/<pre[^>]*><code[^>]*>(.*?)<\/code><\/pre>/is', function ( $matches ) {
			return "\n```\n" . html_entity_decode( strip_tags( $matches[1] ) ) . "\n```\n\n";
		}, $html );
		$html = preg_replace( '/<code[^>]*>(.*?)<\/code>/i', "`$1`", $html );
		$html = strip_tags( $html );
		$html = html_entity_decode( $html, ENT_QUOTES, 'UTF-8' );
		$html = preg_replace( '/\n\s*\n\s*\n/', "\n\n", $html );

		return trim( $html );
	} )( $post_content );
	$markdown_content .= $post_content;
	// Add permalink at the end
	$markdown_content .= "\n\n---\n\n";
	$markdown_content .= '**Original URL:** ' . get_permalink( $post ) . "\n";

	// Set proper headers
	nocache_headers();
	header( 'Content-Type: text/plain; charset=UTF-8' );
	header( 'Content-Disposition: inline; filename="' . sanitize_file_name( $post->post_name ) . '.md"' );

	// Output the markdown content
	echo $markdown_content;

	// Stop WordPress from processing further
	exit;
} );


- Disable Author Archives
// Return a 404 page for author pages if accessed directly.
add_action( 'template_redirect', function () {
	if ( is_author() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
} );

// Remove the author links.
add_filter( 'author_link', '__return_empty_string', 1000 );
add_filter( 'the_author_posts_link', 'get_the_author', 1000, 0 );

// Remove the author pages from the WP 5.5+ sitemap.
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	if ( 'users' === $name ) {
		return false;
	}

	return $provider;
}, 10, 2 );

// Remove admin links in the list of users.
add_filter( 'user_row_actions', function ( $actions, $user ) {
	unset( $actions['view'] );
	unset( $actions['posts'] );

	return $actions;
}, 10, 2 );


- Add the Page Slug to Body Class
function wpcode_snippet_add_slug_body_class( $classes ) {
	global $post;
	if ( isset( $post ) ) {
		$classes[] = $post->post_type . '-' . $post->post_name;
	}

	return $classes;
}

add_filter( 'body_class', 'wpcode_snippet_add_slug_body_class' );

- Set oEmbed Max Width
function wpcode_snippet_oembed_defaults( $sizes ) {
	return array(
		'width'  => 400,
		'height' => 280,
	);
}

add_filter( 'embed_defaults', 'wpcode_snippet_oembed_defaults' );


- Remove Dashboard Welcome Panel
add_action(
	'admin_init',
	function () {
		remove_action( 'welcome_panel', 'wp_welcome_panel' );
	}
);



- Remove "WordPress" from admin titles
add_filter( 'admin_title', function ( $admin_title, $title ) {
	return str_replace( " &#8212; WordPress", '', $admin_title );
}, 10, 2 );


- Add ID column in admin tables
add_action( 'admin_init', function () {
// Get all public post types
	$post_types = get_post_types( array(), 'names' );

	function wpcode_add_post_id_column( $columns ) {
		$columns['wpcode_post_id'] = 'ID'; // 'ID' is the column title

		return $columns;
	}

	function wpcode_show_post_id_column_data( $column, $post_id ) {
		if ( 'wpcode_post_id' === $column ) {
			echo '<code>' . absint( $post_id ) . '</code>';
		}
	}

	foreach ( $post_types as $post_type ) {
		// Add new column to the posts list
		add_filter( "manage_{$post_type}_posts_columns", 'wpcode_add_post_id_column' );

		// Fill the new column with the post ID
		add_action( "manage_{$post_type}_posts_custom_column", 'wpcode_show_post_id_column_data', 10, 2 );
	}
} );


- Add Featured Image Column +
add_filter( 'manage_posts_columns', function ( $columns ) {
	// You can change this to any other position by changing 'title' to the name of the column you want to put it after.
	$move_after     = 'title';
	$move_after_key = array_search( $move_after, array_keys( $columns ), true );

	$first_columns = array_slice( $columns, 0, $move_after_key + 1 );
	$last_columns  = array_slice( $columns, $move_after_key + 1 );

	return array_merge(
		$first_columns,
		array(
			'featured_image' => __( 'Featured Image' ),
		),
		$last_columns
	);
} );

add_action( 'manage_posts_custom_column', function ( $column ) {
	if ( 'featured_image' === $column ) {
		the_post_thumbnail( array( 300, 80 ) );
	}
} );

- Disable Attachment Pages
add_action(
	'template_redirect',
	function () {
		global $post;
		if ( ! is_attachment() || ! isset( $post->post_parent ) || ! is_numeric( $post->post_parent ) ) {
			return;
		}

		// Does the attachment have a parent post?
		// If the post is trashed, fallback to redirect to homepage.
		if ( 0 !== $post->post_parent && 'trash' !== get_post_status( $post->post_parent ) ) {
			// Redirect to the attachment parent.
			wp_safe_redirect( get_permalink( $post->post_parent ), 301 );
		} else {
			// For attachment without a parent redirect to homepage.
			wp_safe_redirect( get_bloginfo( 'wpurl' ), 302 );
		}
		exit;
	},
	1
);


------------------------------------------------
------------------------------------------------
------------------------------------------------

- Make upload filenames lowercase
add_filter( 'sanitize_file_name', 'mb_strtolower' );

- Allow smilies
Allows smiley conversion in obscure places. This is a sample snippet. Feel free to use it, edit it, or remove it.
add_filter( 'widget_text', 'convert_smilies' );
add_filter( 'the_title', 'convert_smilies' );
add_filter( 'wp_title', 'convert_smilies' );
add_filter( 'get_bloginfo', 'convert_smilies' );



------------------------------------------------
------------------------------------------------

Limit image upload file size https://www.adminoptimizer.com/docs/limit-image-upload-file-size/

Set image filename as alt text https://www.adminoptimizer.com/docs/set-image-filename-as-alt-text/

Convert underscore (_) in image filename to hyphen (-)  https://www.adminoptimizer.com/docs/convert-underscore-in-filename-to-hyphen/
