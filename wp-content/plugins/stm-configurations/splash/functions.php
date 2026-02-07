<?php



// Event Sign up form
if ( ! function_exists( 'splash_donate_money' ) ) {
	function splash_donate_money() {
		// Get event details
		$json           = array();
		$json['errors'] = array();

		$_POST['donor']['id'] = filter_var( $_POST['donor']['id'], FILTER_VALIDATE_INT );

		if ( empty( $_POST['donor']['id'] ) ) {
			return false;
		}

		if ( ! filter_var( $_POST['donor']['name'], FILTER_SANITIZE_STRING ) ) {
			$json['errors']['name'] = true;
		}
		if ( ! is_email( $_POST['donor']['email'] ) ) {
			$json['errors']['email'] = true;
		}
		if ( ! is_numeric( $_POST['donor']['phone'] ) ) {
			$json['errors']['phone'] = true;
		}
		if ( ! filter_var( $_POST['donor']['message'], FILTER_SANITIZE_STRING ) ) {
			$json['errors']['message'] = true;
		}
		if ( ! filter_var( $_POST['donor']['amount'], FILTER_VALIDATE_INT ) ) {
			$json['errors']['amount'] = true;
		}

		if ( empty( $json['errors'] ) ) {

			$participant_data['post_title']   = sanitize_text_field( $_POST['donor']['name'] );
			$participant_data['post_type']    = 'donor';
			$participant_data['post_status']  = 'draft';
			$participant_data['post_excerpt'] = sanitize_text_field( $_POST['donor']['message'] );
			$participant_id                   = wp_insert_post( $participant_data );
			update_post_meta( $participant_id, 'donor_email', sanitize_text_field( $_POST['donor']['email'] ) );
			update_post_meta( $participant_id, 'donor_phone', sanitize_text_field( $_POST['donor']['phone'] ) );
			update_post_meta( $participant_id, 'donor_event', sanitize_text_field( $_POST['donor']['id'] ) );
			update_post_meta( $participant_id, 'donor_amount', sanitize_text_field( $_POST['donor']['amount'] ) );

			$items                = array();
			$items['item_name']   = get_the_title( intval( $_POST['donor']['id'] ) );
			$items['item_number'] = intval( $_POST['donor']['id'] );
			$items['amount']      = sanitize_text_field( $_POST['donor']['amount'] );
			$items                = http_build_query( $items );

			$mode = get_theme_mod( 'paypal_mode', 'sandbox' );
			$url  = ( 'live' === $mode ) ? 'www.paypal.com' : 'www.sandbox.paypal.com';

			$redirect_url = '';

			$redirect_url .= 'https://' . $url;
			$redirect_url .= '/cgi-bin/webscr?cmd=_xclick&business=';
			$redirect_url .= get_theme_mod( 'paypal_email' );
			$redirect_url .= '&' . $items;
			$redirect_url .= '&no_shipping=1&no_note=1&currency_code=' . get_theme_mod( 'paypal_currency', 'USD' );
			$redirect_url .= '&bn=PP%2dBuyNowBF&charset=UTF%2d8&invoice=' . $participant_id;
			$redirect_url .= '&return=' . home_url( '/' ) . '&rm=2&notify_url=' . home_url( '/' );

			add_filter( 'wp_mail_content_type', 'stm_set_html_content_type' );

			$headers[] = 'From: ' . get_bloginfo( 'blogname' ) . ' <' . get_bloginfo( 'admin_email' ) . '>';

			wp_mail( get_bloginfo( 'admin_email' ), esc_html__( 'New donation', 'splash' ), esc_html__( 'New donation, please check it.', 'splash' ), $headers );

			remove_filter( 'wp_mail_content_type', 'stm_set_html_content_type' );

			$json['redirect_url'] = $redirect_url;

			$json['success'] = esc_html__( 'Redirecting to Paypal.', 'splash' );
		}

		echo wp_json_encode( $json );
		exit;
	}
}

add_action( 'wp_ajax_splash_donate_money', 'splash_donate_money' );
add_action( 'wp_ajax_nopriv_splash_donate_money', 'splash_donate_money' );

function splash_remove_woo_widgets() {
	unregister_widget( 'WC_Widget_Recent_Products' );
	unregister_widget( 'WC_Widget_Featured_Products' );
	unregister_widget( 'WC_Widget_Product_Tag_Cloud' );
	unregister_widget( 'WC_Widget_Layered_Nav' );
	unregister_widget( 'WC_Widget_Layered_Nav_Filters' );
	unregister_widget( 'WC_Widget_Product_Search' );
	unregister_widget( 'WC_Widget_Recent_Reviews' );
	unregister_widget( 'WC_Widget_Recently_Viewed' );
	unregister_widget( 'WC_Widget_Best_Sellers' );
	unregister_widget( 'WC_Widget_Onsale' );
	unregister_widget( 'WC_Widget_Random_Products' );
}
add_action( 'widgets_init', 'splash_remove_woo_widgets' );

function stm_unregister_widgets() {
	unregister_widget( 'SP_Widget_Event_List' );
}

add_action( 'widgets_init', 'stm_unregister_widgets' );

add_action( 'add_meta_boxes_comment', 'splash_comment_edit_add_meta_box' );
function splash_comment_edit_add_meta_box() {
	$id = ( isset( $_GET['c'] ) && ! empty( $_GET['c'] ) ) ? intval( $_GET['c'] ) : 0;
	if ( ! get_comment_meta( $id, 'rating', true ) ) {
		add_meta_box( 'rating', __( 'Add Rating', 'splash' ), 'splash_comment_meta_box', 'comment', 'normal', 'high' );
	}
}
