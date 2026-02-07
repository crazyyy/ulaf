<?php

if( !empty( $_GET['stm_check_donation_ipn'] ) ) {

	header('HTTP/1.1 200 OK');
	checkPayment( $_REQUEST );

	exit;
}

function stm_payment_url() {
	$mode = get_theme_mod( 'paypal_mode', 'sandbox' );
	$url      = ( $mode == 'live' ) ? 'www.paypal.com' : 'www.sandbox.paypal.com';
	return $url;
}

if( ! function_exists( 'checkPayment' ) ){

	function checkPayment($data){

		$item_number      = $data['item_number'];
		$invoice          = $data['invoice'];

		$req = 'cmd=_notify-validate';

		foreach ($data as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req  .= "&$key=$value";
		}

		$ch = curl_init('https://'.stm_payment_url().'/cgi-bin/webscr');
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

		if( !($res = curl_exec($ch)) ) {
			echo ("Got " . curl_error($ch) . " when processing IPN data");
			curl_close($ch);
			return false;
		}
		curl_close($ch);

		if (strcmp ($res, "VERIFIED") == 0) {

			wp_update_post( array( 'ID'=>$invoice, 'post_status'=>'publish' ) );

			if( get_post_type( $invoice ) == 'donor' ){
				$events_admin_email_subject = str_replace( array( '[donation]' ), array( get_the_title( $item_number ) ), get_theme_mod( 'admin_email_subject', __( 'New Donation for [donation]', 'splash' ) ));
				$events_admin_email_message = str_replace( array( '[donation]', '[name]', '[email]', '[phone]' , '[amount]' ), array( get_the_title( $item_number ), get_the_title( $invoice ), get_post_meta( $invoice, 'donor_email', true ), get_post_meta( $invoice, 'donor_phone', true ), get_post_meta( $invoice, 'donor_amount', true ) ), get_theme_mod( 'admin_email_message', esc_html__('New payment for [donation]. Donor Info: Name:[name]; Phone:[phone]; E-mail:[email]; Amount:[amount]', 'splash')));
				$events_participant_email_subject = str_replace( array( '[donation]' ), array( get_the_title( $item_number ) ), get_theme_mod( 'user_email_subject', __( 'Confirmation of your pariticipation in the [event]', 'splash' ) ));
				$events_participant_email_message = str_replace( array( '[name]' ), array( get_the_title( $invoice ) ), get_theme_mod( 'user_email_message', __( 'Dear [name]. Thank you for your donation.', 'splash' ) ));

				add_filter('wp_mail_content_type', 'set_html_content_type');

				$headers[] = 'From: ' . get_bloginfo('blogname') . ' <' . get_bloginfo('admin_email') . '>';

				wp_mail( get_bloginfo( 'admin_email' ), $events_admin_email_subject, nl2br( $events_admin_email_message ), $headers );

				wp_mail( get_post_meta( $invoice, 'donor_email', true ), $events_participant_email_subject, nl2br( $events_participant_email_message ), $headers );

				remove_filter('wp_mail_content_type', 'set_html_content_type');
			}
		}
	}
}