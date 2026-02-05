<?php
	$prefix = boldthemes_get_prefix();
	$about_author_style = boldthemes_get_option( $prefix . '_single_about_author_style' );
	
	if ( $about_author_style == 'none' && !is_customize_preview() ) {
		return;
	}
	
	$about_author_html	= '';
	$avatar_html		= '';
	
	if ( $about_author_style == 'with-image' || is_customize_preview() ) {
		
		$avatar_html = get_avatar( get_the_author_meta( 'ID' ), 280 );
		$avatar_html = str_replace ( 'width=\'280\'', 'width=\'140\'', $avatar_html );
		$avatar_html = str_replace ( 'height=\'280\'', 'height =\'140\'', $avatar_html );
		$avatar_html = str_replace ( 'width="280"', 'width="140"', $avatar_html );
		$avatar_html = str_replace ( 'height="280"', 'height ="140"', $avatar_html );
		
	}
	
	$about_author_html .= '<div class="bt-author-content">';
	
		$user_url = get_the_author_meta( 'user_url' );
		if ( $user_url != '' ) {
			$author_html = boldthemes_get_post_author( $user_url, '' );
		} else {
			$author_html = esc_html( get_the_author_meta( 'display_name' ) );
		}
	
		if ( $avatar_html ) {
			$about_author_html .= '<div class="bt-author-avatar">' . $avatar_html . '</div>';
		}
	
		$about_author_html .= '<div class="bt-author-text"><h4>' . $author_html;
			$about_author_html .= '</h4>
			<p>' . get_the_author_meta( 'description' ) . '</p>
		</div>

	</div>';


	echo '<section class="bt-about-author single-about-author-' . $prefix . '">';
		echo wp_kses( $about_author_html, 'about_author' );
	echo '</section>';

?>
