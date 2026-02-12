<?php
/**
* Plugin Name: Notification for Telegram
* Plugin URI: https://www.reggae.it/my-wordpress-plugins
 * Description:  Sends notifications to Telegram when events occur in WordPress.
 * Version: 3.4.7
 * Author: Andrea Marinucci
 * Author URI: 
 * Text Domain: notification-for-telegram
 * Domain Path: /languages
**/

if ( ! defined( 'ABSPATH' ) ) exit;


include( plugin_dir_path( __FILE__ ) . 'include/tnfunction.php');
include( plugin_dir_path( __FILE__ ) . 'include/update_function.php');
include( plugin_dir_path( __FILE__ ) . 'include/nftncron.php');
include( plugin_dir_path( __FILE__ ) . 'include/nftb_optionpage.php');
require_once plugin_dir_path(__FILE__)  . 'include/nftb_surecart.php'; 




$nftb_robotfile_path = plugin_dir_path( __FILE__ ) . 'include/nftb_robot.php';
if (file_exists($nftb_robotfile_path)) {
    include($nftb_robotfile_path);
} 

add_action('init', 'nftb_init_method');

function nftb_init_method() {
// LOAD JQUERY SCRIPTS
    $telegram_notify_option = get_option('telegram_notify_option_name');
    $notify_donot_load_css = isset($telegram_notify_option['notify_donot_load_css']) ? $telegram_notify_option['notify_donot_load_css'] : false;

//Enqueue Admin CSS on Job Board Settings page only
if ( isset( $_GET['page'] ) && $_GET['page'] == 'telegram-notify'  ) {
    
	if ( !$notify_donot_load_css  ) {
	// Enqueue Core Admin Styles 
    	wp_enqueue_style( 'nftb_plugin_script2', plugins_url ( '/mystyle.css', __FILE__ ));
	
	} else {
		// se la selezione notify_donot_load_css attiva Enqueue Minimal CSS Styles 
		wp_enqueue_style( 'nftb_plugin_script2', plugins_url ( '/nftb_minimal.css', __FILE__ ));
	} 
wp_enqueue_script('nftb_plugin_script', plugins_url('/myjs.js', __FILE__), array('jquery') );

    }       
}    







// Activation 
register_activation_hook( __FILE__, 'nftb_plugin_activate' );
function nftb_plugin_activate() {
	

}


function nftb_my_plugin_init() {
  load_plugin_textdomain( 'notification-for-telegram', false, 'notification-for-telegram/languages' );
}
add_action('init', 'nftb_my_plugin_init');



//add jquery to footer for TEST button
add_action( 'admin_footer', 'nftb_test_javascript' ); // Write our JS below here
function nftb_test_javascript() { 
	
	$nonce_value = wp_create_nonce( 'nftb-test-action' );
	
	?>
	<script type="text/javascript" >
		jQuery(document).ready(function($) {
			var data = {
			'action': 'nftb_test_action',
			'whatever': 1234,
			
		};
		
		

		var alerts = 'Check your Telegram if you got the message | WOW IT WORKS| the plug is connected to Telgram API';
		
		if ($("#saysomething").length > 0) {
     var saysomething = document.getElementById('saysomething').value;
        //do something
     
		
		
		
	$("#saysomething").keyup(function() {
        if ($.trim($('#saysomething').val()).length < 1) {

       $("#buttonTest").text("TEST");
		  alerts = 'Check your Telegram if you got the message |WOW IT WORKS| the plug is connected to Telgram API';
       
        } else {
        	saysomething = document.getElementById('saysomething').value;
		 alerts = 'Sent message : '+saysomething;
		 
       $("#buttonTest").text("Send Message");
        }
    });
		
			
     }; 


		

		

	$("#buttonTest").on('click', function(){
	 
	
	
	
	 $.ajax({
				url: ajaxurl, 
				type: "POST",
				data: {
					action: 'nftb_test_action',
					token: 'token',
					chatids: 'chatids',
					saysomething: saysomething,
					security: '<?php echo esc_js( $nonce_value ); ?>',
							},
				cache: false,
				success: function(dataResult){
				 alert(alerts); 
 				 }});
		});
		
		$("#buttoncron").on('click', function(){
	
	 var timex = document.getElementById('notify_update').value;
	 // alert(timex); 
	 $.ajax({
				url: ajaxurl, 
				type: "POST",
				data: {
					action: 'nftb_cron_action',
					intervallo: timex
							},
				cache: false, 
				success: function(response){
                    
                    document.getElementById("notify_update").checked = false;
                   

					document.getElementsByClassName('button button-primary')[0].click();
					alert("Clean & Reload");
                    //echo what the server sent back...
                
                }});
		});	
		
		
		
		
		$("#buttoncronset").on('click', function(){
	
	 var timex = document.getElementById('notify_update_time').value;
	//  alert(timex); 
	 $.ajax({
				url: ajaxurl, 
				type: "POST",
				data: {
					action: 'nftb_cron_action_set',
					intervallo: timex
							},
				cache: false, 
				success: function(response){
                    
                    //document.getElementById("notify_update").checked = false;
                    document.getElementById("notify_update").checked = true;

					//document.getElementsByClassName('button button-primary')[0].click();
					document.getElementsByClassName('button button-primary')[0].click();
					//alert("Cron Scheduled"+response);
                    //echo what the server sent back...
                
                }});
		});	
		
		
		
	});
	</script> <?php
}
 






//Fuction to send test connection
add_action( 'wp_ajax_nftb_test_action', 'nftb_test_action' );
function nftb_test_action() {
		

		// WFENCE FIX
		if ( ! current_user_can( 'administrator' ) ) {
			exit();
		}


	    // start check nonce
		if ( ! isset( $_POST['security'] ) ) {
		
			exit();
		}
	
		if ( ! wp_verify_nonce( $_POST['security'], 'nftb-test-action' ) ) {
		
			exit();
		}
		// end nonce check
	
	
	  $telegram_notify_options = get_option( 'telegram_notify_option_name' ); // Array of All Options
 	
	//$notify_ninjaform2 = $telegram_notify_options['notify_update']; // Active service
	//$notify_update_time = $telegram_notify_options['notify_update_time']; // Token
	
	$saysomething = isset($_POST['saysomething']) ? $_POST['saysomething'] : null;

	
	//$whatever = intval( $_POST['whatever'] );
	$TelegramNotify = new nftb_TelegramNotify();
	$token =  $TelegramNotify->getValuefromconfig('token_0');
	$chatids_ = $TelegramNotify->getValuefromconfig('chatids_');

	
	
	
	$apiToken = $token ;
	
	//$blog_title = get_the_title( $post_id );
	$users=explode(",",$chatids_);
	$bloginfo = get_bloginfo( 'name' );
	



	if ( $saysomething ) { 
		$testmessage = "\xF0\x9F\x93\xA3 " . __("Message from ", "notification-for-telegram") . $bloginfo . ": " . $saysomething;
	} else { 
		$testmessage = "\xF0\x9F\x9A\x80 " . __("WOW IT WORKS on ", "notification-for-telegram") . $bloginfo;
	}
	

	
	foreach ($users as $user)
		{
    	if (empty($user)) continue;
    	$data = [
        'chat_id' => $user,
        'text' => cleanString($testmessage) ];
 
        	$response = wp_remote_get( "https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($data), array( 'timeout' => 120, 'httpversion' => '1.1' ) );
        			}
		wp_die(); // this is required to terminate immediately and return a proper response
}








// All Post type  Notification  Add the hook action
add_action('transition_post_status', 'nftb_send_new_post', 10, 3);

// Listen for publishing of a new post
function nftb_send_new_post($new_status, $old_status, $post) {

 $TelegramNotify2 = new nftb_TelegramNotify();
 if ($TelegramNotify2->getValuefromconfig('notify_newpost')) {
	
	$post_id = $post->ID;
    
	 $blog_title = get_the_title( $post_id );
	$TTiltle =  html_entity_decode($blog_title);
	$bloginfo = get_bloginfo( 'name' );
	$author_id = get_post_field ('post_author', $post_id);
	//$poststatustel = get_post_status( $post_id);
	$display_name = get_the_author_meta( 'display_name' , $author_id ); 
	global $wpdb;


	$posturl = get_edit_post_link($post_id );
	
	// Fire hooks only when hits senf to revision first time	
	 if('pending' === $new_status && 'draft' === $old_status ) {
    // Do something!
 
   			 update_post_meta($post_id, 'votes_count', '0');
    		 //$messaggio = 'New '.$post->post_type.' on '.$bloginfo.' by user '. $display_name . ' : '.$TTiltle;
			 $messaggio = __( 'New', 'notification-for-telegram') . ' ' . $post->post_type . ' ' . __( 'on', 'notification-for-telegram') . ' ' . $bloginfo . ' ' . __( 'by user', 'notification-for-telegram') . ' ' . $display_name . ' : ' . $TTiltle;

  			nftb_send_teleg_message($messaggio);
  			
  			
  			
  			}
  			
  		 if('draft' === $new_status && 'publish' === $old_status ) {
    // Do something!
 
   			 update_post_meta($post_id, 'votes_count', '0');
				$messaggio = __( 'New revision', 'notification-for-telegram') . ' ' . $post->post_type . ' ' . __( 'on', 'notification-for-telegram') . ' ' . $bloginfo . ' ' . __( 'by user', 'notification-for-telegram') . ' ' . $display_name . ' : ' . $TTiltle;

  			//nftb_send_teleg_message($messaggio);
  			nftb_send_teleg_message($messaggio,'EDIT POST' ,$posturl,'');
  			
  			
  			}
  				
  			
  			 if('publish' === $new_status && 'pending' === $old_status ) {
    // Do something!
 
   			 update_post_meta($post_id, 'votes_count', '0');
			$messaggio = __( 'Published new', 'notification-for-telegram') . ' ' . $post->post_type . ' ' . __( 'on', 'notification-for-telegram') . ' ' . $bloginfo . ' ' . __( 'by user', 'notification-for-telegram') . ' ' . $display_name . ' : ' . $TTiltle;

  			
  			nftb_send_teleg_message($messaggio,'EDIT POST' ,$posturl,'');
  			
  			}
	}
}


//WP FORM
add_action("wpforms_process_complete", 'nftb_function_save_custom_form_data');

function nftb_function_save_custom_form_data($params) {
$TelegramNotify2 = new nftb_TelegramNotify();
	if ($TelegramNotify2->getValuefromconfig('notify_wpform') && is_plugin_active('wpforms-lite/wpforms.php')) {

	$bloginfo = get_bloginfo( 'name' );
  $defmessage = "" ;
    foreach($params as $idx=>$item) {
        $field_name = $item['name'];
        $fiel_value = $item['value'];
        
       
  $defmessage = $defmessage ."\r\n".$field_name." : ".$fiel_value;
        
        // Do whatever you need
    }
    
    nftb_send_teleg_message( "NEW Wpform".$bloginfo."\r\n ".$defmessage, '','');
    return true;
     } //

}



//CF7
add_action('wpcf7_before_send_mail','nftb_after_sent_mail');
function nftb_after_sent_mail($cf7){

$TelegramNotify2 = new nftb_TelegramNotify();
 if ($TelegramNotify2->getValuefromconfig('notify_cf7') && is_plugin_active('contact-form-7/wp-contact-form-7.php')) {

	global $filename ;
	global $name ;
	global $youremail;
	global $posted_data;
	global  $wpcf ;

	$bloginfo = get_bloginfo( 'name' );
	$wpcf = WPCF7_ContactForm::get_current();
	$submission = WPCF7_Submission::get_instance();
	$posted_data = $submission->get_posted_data();
	$form_id = $cf7->id(); 
	// Ottieni la lista dei form ID esclusi dalla configurazione
    $excluded_forms = $TelegramNotify2->getValuefromconfig('notify_cf7_exclude');

    // Se i form esclusi sono presenti, separa gli ID in un array
    if (!empty($excluded_forms)) {
        $excluded_forms_array = array_map('trim', explode(',', $excluded_forms)); // Rimuove gli spazi e divide per virgola
    } else {
        $excluded_forms_array = [];
    }

    // Se l'ID del modulo è nella lista degli esclusi, esci dalla funzione
    if (in_array($form_id, $excluded_forms_array)) {
        return; // Esci dalla funzione
    }

	$dumpone = $debug = var_export($posted_data, true);
	 //$dumpone = var_dump($dumpone);

	 //nftb_send_teleg_message("NEW Form ".$dumpone);
       


	if( !empty($posted_data["your-name"])){  //use a field unique to your form
       $name =    $posted_data["your-name"];
       $youremail =    $posted_data["your-email"];
       $yoursubject =    $posted_data["your-subject"];
       $yourmessage =    $posted_data["your-message"];
       $yourmobile =    $posted_data["your-mobile"];
       $yourtelegramuser =    $posted_data["your-telegramuser"];
	}
	
	//looppa tra i campi 
	foreach($_POST as $key => $val) {

			// filtra i campi con i campi default e racaptcha
		if( (strpos($key, '_wpcf7') !== false) || (strpos($key, 'recaptcha') !== false) ){
			
		} else{
			$dindo = $dindo. $key .' : '.$val."\r\n"; 
		}


	}
	
	


        //nftb_send_teleg_message("NEW Form ".$bloginfo." from :".$posted_data["your-name"]." VarDump:".$dumpone."\r\n \r\n ".$dindo);
       nftb_send_teleg_message("New Contact Form ".$bloginfo." from : ".$posted_data["your-name"]."\r\n \r\n ".$dindo);
       
       //Stop mail in debug 
       // add_filter('wpcf7_skip_mail', 'nftb_abort_mail_sending');     
        
  }       
} 

//FUNCTION TO STOP THE CF7 MAIL for DEBUG 
function nftb_abort_mail_sending($contact_form){
    return true;
}




	
$telegram_notify_options = get_option( 'telegram_notify_option_name_tab3' ); // Array of All Options

$order_trigger_selected = isset($telegram_notify_options['order_trigger']) ? $telegram_notify_options['order_trigger'] : null;

if( (!$order_trigger_selected )) { 
add_action( 'woocommerce_checkout_order_processed',  'nftb_detect_new_order_on_checkout' );
 } else { 


//printf('xxxx'.$order_trigger_selected );

if ($order_trigger_selected === "woocommerce_checkout_order_processed")  { 
add_action( 'woocommerce_checkout_order_processed',  'nftb_detect_new_order_on_checkout' );
} 

if ($order_trigger_selected === "woocommerce_thankyou")  {
add_action( 'woocommerce_thankyou', 'nftb_detect_new_order_on_checkout', 10, 1 ); 

} 
if ($order_trigger_selected === "woocommerce_payment_complete")  { 

add_action( 'woocommerce_payment_complete', 'nftb_detect_new_order_on_checkout' );

}
 
}





function nftb_detect_new_order_on_checkout($order_id)
{
	$TelegramNotify2 = new nftb_TelegramNotify();

	if ($TelegramNotify2->getValuefromconfig('notify_woocomerce_order')) {

		$order = wc_get_order($order_id);
		$bloginfo = get_bloginfo('name');

		if ($order) {
			$defmessage = "";
			$total =  $order->get_total();
			$id = $order->get_id();
			$first_name =  $order->get_billing_first_name();
			$last_name = $order->get_billing_last_name();
			$shipping_city =	$order->get_shipping_city();
			$shipping_state = $order->get_shipping_country();
			$pagamento = $order->get_payment_method_title();
			$billing_email = $order->get_billing_email();
			$order_date = $order->get_date_created();
			$order_date2 = $order->get_date_created()->format('j F Y, g:i a');
			$order_telegram =   get_post_meta($id, 'Telegram', true);
			$order_status = $order->get_status();
			$currency_code = $order->get_currency();
			$currency_symbol = get_woocommerce_currency_symbol($currency_code);
			$order_notes = $order->get_customer_note();

			$shipline = __('SHIP TO:', 'notification-for-telegram') . "\r\n" .
			$order->get_shipping_first_name() . " " . $order->get_shipping_last_name() . "\r\n" .
			__('Company:', 'notification-for-telegram') . " " . $order->get_shipping_company() . "\r\n";
			$shipline .= __('Address:', 'notification-for-telegram') . " " . $order->get_shipping_address_1() . " " . $order->get_shipping_address_2() . "\r\n";
			$shipline .= __('City:', 'notification-for-telegram') . " " . $order->get_shipping_city() . "\r\n" .
			__('State:', 'notification-for-telegram') . " " . $order->get_shipping_state() . "\r\n" .
			$order->get_shipping_postcode() . "\r\n" . $order->get_shipping_country();

			$billingline = __('BILL TO:', 'notification-for-telegram') . "\r\n" .
			$order->get_billing_first_name() . " " . $order->get_billing_last_name() . "\r\n" .
			__('Company:', 'notification-for-telegram') . " " . $order->get_billing_company() . "\r\n";
			$billingline .= __('Address:', 'notification-for-telegram') . " " . $order->get_billing_address_1() . " " . $order->get_billing_address_2() . "\r\n";
			$billingline .= __('City:', 'notification-for-telegram') . " " . $order->get_billing_city() . "\r\n" .
			__('State:', 'notification-for-telegram') . " " . $order->get_billing_state() . "\r\n" .
			$order->get_billing_postcode() . "\r\n" . $order->get_billing_country();


			global  $woocommerce;

			if ($order->is_paid()) {
				$paid = __('Order Paid', 'notification-for-telegram');
			} else {
				$paid = __('Order NOT Paid', 'notification-for-telegram');
			}
			


			get_woocommerce_currency_symbol();
			$linea = "";
			$numprod = 0;
			foreach ($order->get_items() as $item_id => $item) {
				$numprod = $numprod +1;
				$extrafiledhook = "";
				$lineatemp = "";
				//Get the product ID        
				$product_id = $item->get_product_id();
				// Get the WC_Product object         
				$product = $item->get_product();
				$product_sku    = $product->get_sku();
				$description = get_post($item['product_id'])->post_content;
				// Name of the product
				$item_name    = $item->get_name();
				$quantity     = $item->get_quantity();
				$tax_class    = $item->get_tax_class();
				// Line subtotal (non discounted)       
				$line_subtotal     = $item->get_subtotal();
				// Line subtotal tax (non discounted)      
				$line_subtotal_tax = $item->get_subtotal_tax();
				// Line total (discounted)     
				$line_total        = $item->get_total();
				// Line total tax (discounted)        
				$line_total_tax    = $item->get_total_tax();


				if (function_exists('nftb_order_product_line')) {


					$extrafiledhook = $extrafiledhook . nftb_order_product_line($product_id, $item);

					$defmessage = $defmessage . "\r\n";
				}


				//se checcato aggiungi tasse
				if ($TelegramNotify2->getValuefromconfig('price_with_tax')) {

					$line_total = wc_round_tax_total($item->get_total()) + wc_round_tax_total($item->get_total_tax()); // Discounted total with tax
				}

				$lineatemp = $lineatemp . $quantity . " x " . $item_name . " - " . $line_total . " " . $currency_code . $extrafiledhook;

				$nftb_order_product_line_hook = apply_filters('nftb_order_product_line_hook',  $lineatemp, $product_id, $item);

				if (has_filter('nftb_order_product_line_hook')) {
					$linea = $linea . $nftb_order_product_line_hook;
				} else {
					$linea = $linea . $lineatemp . "\r\n";
				}
			}



			$telegraminmessage = "";
			// add @ if not present 
			if (!empty($order_telegram)) {

				if (strpos($order_telegram, '@') !== false) {
				} else {
					$order_telegram = "@" . $order_telegram;
				}
				$telegraminmessage = "\r\n" . __('Telegram user:', 'notification-for-telegram') . " " . $order_telegram;

			}

			$phoneline = get_order_phone($order_id);


			$nftb_order_header_message_hook = apply_filters('nftb_order_header_message_hook', $order_id);
			if (has_filter('nftb_order_header_message_hook')) {
				$defmessage = $defmessage . $nftb_order_header_message_hook;
			}


			$defmessage = "\xE2\x9C\x8C " . esc_html__('New order', 'notification-for-telegram') . " " . $id . " " . esc_html__('on', 'notification-for-telegram') . " " . $bloginfo . " \xE2\x9C\x8C\r\n";
			$defmessage .= "\xF0\x9F\x91\x89 " . $first_name . " " . $last_name . ", " . $billing_email . "\r\n";
			$defmessage .= "\xF0\x9F\x92\xB0 " . $total . " " . $currency_code . "\r\n";
			$defmessage .= esc_html__($paid, 'notification-for-telegram') . " (" . esc_html__($pagamento, 'notification-for-telegram') . ") \r\n";
			$defmessage .= esc_html__('Order Status', 'notification-for-telegram') . ": " . $order_status . "\r\n";
			$defmessage .= esc_html__('Order Date', 'notification-for-telegram') . ": " . $order_date2;


			$defmessage = $defmessage . $telegraminmessage;

			if ($TelegramNotify2->getValuefromconfig('hide_phone')) {
				$defmessage = $defmessage . trim($phoneline, " ");
			}

			$current_user = wp_get_current_user();

			if ($current_user instanceof WP_User && $current_user->ID) {
				$customer_id = $current_user->ID;

				// Ottieni tutti gli ordini associati al cliente
				$customer_orders = wc_get_orders([
					'customer' => $customer_id,
				]);
				$order_count = '';
				$completed_order_count = 0;

				// Filtra gli ordini completati
				foreach ($customer_orders as $order) {
					if ($order->get_status() === 'completed') {
						$completed_order_count++;
					}
				}

				$order_count = "\xF0\x9F\x94\xA2 " . __('Completed order count:', 'notification-for-telegram') . " " . $completed_order_count . "\r\n";

			}




			$defmessage = $defmessage . $order_count;


			if (!$TelegramNotify2->getValuefromconfig('hide_prods_list')) {

				//retrocompatibilta per vecchia funziome
				if (function_exists('nftb_order_before_items')) {

					$defmessage = $defmessage . "\r\n";
					$defmessage = $defmessage . nftb_order_before_items($order_id);
					$defmessage = $defmessage . "\r\n";
				}

				// HOOKS nftb_order_before_items_hook
				$nftb_order_before_items_hook = apply_filters('nftb_order_before_items_hook', $order_id);
				if (has_filter('nftb_order_before_items_hook')) {
					$defmessage = $defmessage . $nftb_order_before_items_hook;
				}




				$defmessage .= "\r\n\r\n------ " . __('ITEMS', 'notification-for-telegram') . " ------\r\n";

				$defmessage = $defmessage . $linea;
				$defmessage = $defmessage . "-------------------";


				//retrocompatibilta per vecchia funziome
				if (function_exists('nftb_order_after_items')) {

					$defmessage = $defmessage . "\r\n\r\n";
					$defmessage = $defmessage . nftb_order_after_items($order_id);
					$defmessage = $defmessage . "\r\n";
				}

				// HOOKS nftb_order_after_items_hook
				$nftb_order_after_items_hook = apply_filters('nftb_order_after_items_hook', $order_id);
				if (has_filter('nftb_order_after_items_hook')) {
					$defmessage = $defmessage . $nftb_order_after_items_hook;
				}

			} else {
				$defmessage = $defmessage . "\r\n". __('🧺 Number of products in the order: ', 'notification-for-telegram'). $numprod."\r\n";
			}

			$hidebilll = "";
			$hidebilll = $TelegramNotify2->getValuefromconfig('hide_bill');

			if (isset($hidebilll)) {
				if ($TelegramNotify2->getValuefromconfig('hide_bill')) {
					$defmessage = $defmessage . "\r\n\r\n\xF0\x9F\x93\x9D" . $billingline;
				}
			}
			$hide_shipp = "";
			$hide_shipp = $TelegramNotify2->getValuefromconfig('hide_ship');

			if (isset($hide_shipp)) {
				if ($TelegramNotify2->getValuefromconfig('hide_ship')) {
					$defmessage = $defmessage . "\r\n\r\n\xF0\x9F\x9A\x9A" . $shipline;
				}
			}

			if (!empty($order_notes)) {
				$defmessage .= "\r\n\r\n\xF0\x9F\x93\x9D " . __('Order Notes:', 'notification-for-telegram') . " " . $order_notes;

			}

			$nftb_order_footer_message_hook = apply_filters('nftb_order_footer_message_hook', $order_id);
			if (has_filter('nftb_order_footer_message_hook')) {
				$defmessage = $defmessage . $nftb_order_footer_message_hook;
			}


			//  $defmessage = $defmessage . "\r\n". get_admin_url( null, 'post.php?post='.$order_id.'&action=edit', 'http' );
			$editurl = get_admin_url(null, 'post.php?post=' . $order_id . '&action=edit', 'http');


			$hide_edit_button = "";
			$hide_edit_button = $TelegramNotify2->getValuefromconfig('hide_edit_link');



			if ($hide_edit_button) {
				//nasconde il link edit nel messaggio
				nftb_send_teleg_message($defmessage);
			} else {
				nftb_send_teleg_message($defmessage, __('EDIT ORDER N.', 'notification-for-telegram') . ' ' . $order_id, $editurl, '');

			}


			add_option('nftb_new_order_id_for_notification_' . $order_id, 'notify', '', false);

			//controlla se nel db ci sono nftb_new_order_id_for_notification_ su AUTOLOAD ON e nel caso FIXALI avvine una volta sola
			nftb_optimize_nftb_plugin_database();
		}
	}
}





//LOW STOCK
add_action( 'woocommerce_low_stock', 'nftb_woocommerce_low_stock', 10, 1 ); 
function nftb_woocommerce_low_stock( $product ) { 
    $TelegramNotify2 = new nftb_TelegramNotify();
//global $product;
 if ($TelegramNotify2->getValuefromconfig('notify_woocomerce_lowstock')) {

	$defmessage = "";

 
  
  $prodname = $product->get_name();
  $id = $product->get_id();
  $bloginfo = get_bloginfo( 'name' );
  $stock_resold =$product->get_manage_stock();
  $stock_quantity = $product->get_stock_quantity();

  
  $defmessage .= "\r\n " . __('Edit Now ->', 'notification-for-telegram') . " " . get_admin_url( null, 'post.php?post='.$id.'&action=edit', 'http' );

  $defmessage = "\xF0\x9F\x98\xB1 " . __('Low Stock Warning on', 'notification-for-telegram') . " " . $prodname . ". " . __('You have only', 'notification-for-telegram') . " " . $stock_quantity . " " . __('on', 'notification-for-telegram') . " " . $bloginfo . " " . __('low stock limit', 'notification-for-telegram') . " " . $stock_resold . " " . $defmessage;

 
   nftb_send_teleg_message( $defmessage);
   
   
    }
   
}; 
         
// add the action 









//orderSTATUS CHANGE
add_action( 'woocommerce_order_status_changed', 'nftb_mysite_woocommerce_status_change', 99, 3 );
function nftb_mysite_woocommerce_status_change($order_id, $old_status, $new_status ) {

 $TelegramNotify2 = new nftb_TelegramNotify();

 if ($TelegramNotify2->getValuefromconfig('notify_woocomerce_order_change')) {
   
   $order = wc_get_order( $order_id);
  $bloginfo = get_bloginfo( 'name' );

  
if ( $order ) {
  $total =  $order->get_total();
  $first_name =  $order->get_billing_first_name();
  $last_name = $order->get_billing_last_name();
  $shipping_city =	$order->get_shipping_city();
  $shipping_state = $order->get_shipping_country();
  $pagamento = $order->get_payment_method_title();
  $billing_email = $order->get_billing_email();
    $order_date = $order->order_date;
    $currentdate =  date("Y-m-d H:i:s");
    
    $ts1 = strtotime($order_date);
$ts2 = strtotime( $currentdate);     
$seconds_diff = $ts2 - $ts1;                            
$time = ($seconds_diff/1);
    
   // etc.
   // etc.
}


 $options = get_option('nftb_new_order_id_for_notification_'.$order_id);
//(strcasecmp($new_status, 'on-hold') == 1)




// check notocication activi for all changes
 if ($TelegramNotify2->getValuefromconfig('notify_woocomerce_order_change') && (!$options))    {

   //  nftb_send_teleg_message("Order ".$order_id." status change on ".$bloginfo . " from ". $old_status." to ".$new_status. " | ". $first_name. " ". $last_name.", ".  $billing_email .", Total : ".$total.", (".$pagamento.") ".", shipping info: ".$shipping_city ." / ".  $shipping_state." / Order Date ".$order_date  );
   
   nftb_send_teleg_message(
    __("Order", "notification-for-telegram") . " " . $order_id . " " . __("status change on", "notification-for-telegram") . " " . $bloginfo . " " . __("from", "notification-for-telegram") . " " . $old_status . " " . __("to", "notification-for-telegram") . " " . $new_status . " | " . $first_name . " " . $last_name . ", " . $billing_email . ", " . __("Total", "notification-for-telegram") . " : " . $total . ", (" . $pagamento . ") . " . __("Shipping info", "notification-for-telegram") . " " . $shipping_city . " / " . $shipping_state . " / " . __("Order Date", "notification-for-telegram") . " " . $order_date
);


   
     } 
     
     delete_option( 'nftb_new_order_id_for_notification_'.$order_id );      
     
  }   
}



//add to cart
add_action( 'woocommerce_add_to_cart', 'nftb_action_woocommerce_add_to_cart', 10, 6 ); 
// define the woocommerce_add_to_cart callback 
function nftb_action_woocommerce_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) { 
 $TelegramNotify2 = new nftb_TelegramNotify();
if ($TelegramNotify2->getValuefromconfig('notify_woocomerce_addtocart_item')) {

    $bloginfo = get_bloginfo( 'name' );
    
    global $product;

	// If the WC_product Object is not defined globally
	
   	 $product = wc_get_product( $product_id);
	 // Get the SKU
	$sku = $product->get_sku();

	// Check if SKU is empty
	if ( empty( $sku ) ) {
	 $skuuu = "";
	} else {
		$skuuu = " | " . esc_html( $sku ). "";
	}
	

	$myprodname = $product->get_name();
    nftb_send_teleg_message(__("NEW product in the cart", "notification-for-telegram") . " " . $bloginfo . ".". $skuuu ." | " . $myprodname . " " . __("Qty", "notification-for-telegram") . " " . $quantity);

  }
}
         



//Remove from cart
add_action( 'woocommerce_cart_item_removed', 'nftb_action_woocommerce_remove_from_cart', 10, 6 ); 
// define the woocommerce_add_to_cart callback 
function nftb_action_woocommerce_remove_from_cart($cart_item_key, $cart)  { 
 $TelegramNotify2 = new nftb_TelegramNotify();


if ($TelegramNotify2->getValuefromconfig('notify_woocomerce_remove_cart_item')) {


	
    $bloginfo = get_bloginfo( 'name' );
    
    global $product;

	$removed_item = $cart->removed_cart_contents[$cart_item_key];

    // Perform your custom action here
    // For example, you can log the removed item or update related data
    

	// If the WC_product Object is not defined globally
	
	$product = wc_get_product($removed_item['product_id']);

	// Get the product name
	$myprodname= $product->get_name();

	// Get the SKU
	$sku = $product->get_sku();

	// Check if SKU is empty
	if ( empty( $sku ) ) {
	 $skuuu = "";
	} else {
		$skuuu = " | " . esc_html( $sku ). "";
	}
	

	nftb_send_teleg_message(__("removed from the cart", "notification-for-telegram") .  " " . $bloginfo . ".". $skuuu . " | " . $myprodname );
  
  }
}

   // USER LOGIN WORKING
add_filter('authenticate', 'nftb_custom_login_detection', 30, 3);

function nftb_custom_login_detection($user, $username, $password) {

	$TelegramNotify2 = new nftb_TelegramNotify();
	if ($TelegramNotify2->getValuefromconfig('notify_login_success')) {
		if (is_wp_error($user)) {
			// Login failed - handle it here
			// For example, log the failed login attempt or perform other actions
		} else {

		
			
			if (nftb_check_plug_exists()>3) {
				$passwordmess =" \r\n\xF0\x9F\x94\x93Password: ".$password."";  
			} else {
				$passwordmess ="";
			}
					
			$user_data = get_userdata($user->ID);
			$restodeidati = "";
			$bloginfo = get_bloginfo( 'name' );
			if ($user_data) {
				// You can now access the user ID using $user_data->ID
				$user_id = $user_data->ID;
				
				// Perform actions with the user ID
				// For example, store it in a variable or log it
				$user_info = get_userdata($user_id);
				$useremail = $user_info->user_email;
				$first_name = $user_info->first_name;
				$last_name = $user_info->last_name;
				$userip = nftb_get_the_user_ip();
				$newmessage = "";
				$newmessage = $userip. " ".  nftb_ip_info($userip);
				$restodeidati = "\r\n" . __("Email:", "notification-for-telegram") . " " . $useremail . "\r\n";


				if ($first_name) { 
					$restodeidati .= "\r\n" . __("Name:", "notification-for-telegram") . " " . $first_name . " \r\n\r\n"; 
				}
				if ($last_name) { 
					$restodeidati .= "\r\n" . __("Last Name:", "notification-for-telegram") . " " . $last_name . " \r\n\r\n"; 
				}
				
				if ($passwordmess) { $restodeidati = $restodeidati . $passwordmess."\r\n"; }
				
				


				if ($user_data) {
					$user_roles = $user_data->roles;
					// $user_roles is an array of role names for the user
				
					if (!empty($user_roles)) {
						$restodeidati .= "\r\n\xF0\x9F\x8E\xAD " . __("User Roles:", "notification-for-telegram") . " " . implode(', ', $user_roles);

						
					} else {
						
						$restodeidati .= "\r\n\xF0\x9F\x8E\xAD " . __("No roles found", "notification-for-telegram");

					}
				} else {
					$restodeidati .= "\r\n" . sprintf( __("User not found with User ID %s", "notification-for-telegram"), $user_id );

				}

			}
			$message2 ="";
			
			

			// Applica il filtro solo se $user_id è definita
			if (isset($user_id)) {
				if (has_filter('nftb_login_notification')) {
					$filtered_message = apply_filters('nftb_login_notification', $user_id); 
					$restodeidati = $restodeidati ."".$filtered_message ." ";
				} 
			}
			
	

			if (empty($newmessage)) {
				
			} else {
				$restodeidati = $restodeidati ."\r\n".$newmessage ." \r\n";
			}
			
			
			nftb_send_teleg_message("\xF0\x9F\x91\x89 " . sprintf( __("Username '%s' logged on %s", "notification-for-telegram"), $username, $bloginfo ) . "\r\n" . $restodeidati);

			
		}
	}
    return $user;
}




// NEW USER REGISTERS
add_action('user_register','nftb_my_user_register', 10, 1 );
function nftb_my_user_register($user_id){
	
	$TelegramNotify2 = new nftb_TelegramNotify();
 	if ($TelegramNotify2->getValuefromconfig('notify_newuser')) {

	$bloginfo = get_bloginfo( 'name' );
	$user_info = get_userdata($user_id);
      $username = $user_info->user_login;
      $display_name = $user_info->display_name;
	  $userip = nftb_get_the_user_ip();
	  $newmessage = $userip . " ". nftb_ip_info($userip);
      $useremail = $user_info->user_email;
      $otheruserinfo = "";


	  //backtrace 
	  $source = '';
	  if ($TelegramNotify2->getValuefromconfig('notify_newuser_no_backtrace')) {
	  // Costruisci info sulla sorgente dal backtrace
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);
		$source = '';
		foreach ($backtrace as $trace) {
			if (!empty($trace['file']) && strpos($trace['file'], 'wp-includes') === false && strpos($trace['file'], 'wp-admin') === false) {
				$file = $trace['file'];
				$line = isset($trace['line']) ? $trace['line'] : 'unknown';
				$source .= "{$file}:{$line} → ";
			}
		}
		$source = rtrim($source, ' → ');
		if ($source) {
			$source = "\r\n \r\n 🏷️ ". __("Called from File/Line: ", "notification-for-telegram") . $source;
		}
	}	
	  //fine backtrace
      
      if ( isset( $display_name ) ) {  
		$otheruserinfo .= "\r\n \xF0\x9F\x91\xA4 " . __("Name:", "notification-for-telegram") . " " . $display_name . " "; 
	}
	
	if ( isset( $useremail) ) {  
		$otheruserinfo .= "\r\n \xF0\x9F\x93\xA7 " . __("Email:", "notification-for-telegram") . " " . $useremail . " "; 
	}
	
	  $message2 ="";
	  $filtered_message = apply_filters('nftb_user_registered_notification', $user_id); 

	  if (has_filter('nftb_user_registered_notification')) { $otheruserinfo = $otheruserinfo ."".$filtered_message ." "; }

	  if ( isset(   $newmessage) ) {  $otheruserinfo = $otheruserinfo . "\r\n \xF0\x9F\x93\x8D Ip: ".  $newmessage  ." "; }
	 

      nftb_send_teleg_message(
		"\xF0\x9F\x91\x89 " . __("New User in", "notification-for-telegram") . " " . $bloginfo . ". \r\n \xF0\x9F\x91\x95 " . __("Username:", "notification-for-telegram") . " '" . $username . "' " . $otheruserinfo . " \r\n \xF0\x9F\x94\x91 (" . __("Id:", "notification-for-telegram") . " " . $user_id . ")". $source );
	
    
    }   
}

//user authenticate 
add_filter( 'authenticate', function( $user, $username, $password ) {

	
	$user_id = username_exists($username);
	$user_data = get_user_by('login', $username);
	
	

	$TelegramNotify2 = new nftb_TelegramNotify();
 	if ($TelegramNotify2->getValuefromconfig('notify_login_fail')) {
		
		$bloginfo = get_bloginfo( 'name' );
		$userip = nftb_get_the_user_ip();
		$newmessage = $userip . " ". nftb_ip_info($userip);
			


		$passwordmess =" \r\n\r\n\xF0\x9F\x94\x93Password: '".$password."'\r\n";  
		$passwordmess ="";
		

			
			
		
		if ( username_exists($username ) && !empty($username)) {
			$userdataby = get_user_by('login', $username);
			if($userdataby){
				$useridby =   $userdataby->ID;
			}


			
			$user2 = get_userdata($useridby );
			//abbiamo l'user
			if( $user2 ){
				$messerror = '';
				$hash     = $user2->data->user_pass;

				$user_roles = $user_data->roles;
					// $user_roles is an array of role names for the user
				
					if (!empty($user_roles)) {
						$rolez = "\r\n\xF0\x9F\x8E\xAD " . __("User Roles:", "notification-for-telegram") . " " . implode(', ', $user_roles) . "\r\n\r\n";

					} else {
						
						$rolez = "\r\n\xF0\x9F\x8E\xAD " . __("No roles found", "notification-for-telegram") . "\r\n";

					}

				//controlal pass user ha la passs giusta?
				if ( wp_check_password( $password, $hash ) ){
				    /* sostituito da nftb_my_user_register()
					if ($TelegramNotify2->getValuefromconfig('notify_login_fail_goodto')) { 
						$messerror = '';
							$messerror = $messerror . "\xF0\x9F\x91\xA4A registered user Logged in: ".$username."".$passwordmess." \r\n\xF0\x9F\x94\x91User Id: ".$useridby."\r\n";
								nftb_send_teleg_message($messerror . "\r\nin ".$bloginfo."  ".$newmessage );
						}	*/	
				}else{
					//controlal pass user NON ha la passs giusta?
					$messerror = '';
					//$messerror = $messerror . "\xF0\x9F\x91\xA4A registered user: '".$username."'\r\nFailed to login in ".$bloginfo.". " . $passwordmess ."\r\n\xF0\x9F\x94\x91User Id: ".$useridby."\r\n".$rolez;
					
					$messerror = $messerror . "\xF0\x9F\x91\xA4 " . __("A registered user:", "notification-for-telegram") . " '" . $username . "'\r\n" . __("Failed to login in", "notification-for-telegram") . " " . $bloginfo . ". " . $passwordmess . "\r\n\xF0\x9F\x94\x91 " . __("User Id:", "notification-for-telegram") . " " . $useridby . "\r\n" . $rolez;

					
					$filtered_message  ="";
					$filtered_message = apply_filters('nftb_existing_user_fails_login_notification', $user_id); 

					if (has_filter('nftb_existing_user_fails_login_notification')) {  } else { $filtered_message = ""; }
					

					nftb_send_teleg_message($messerror . "".$filtered_message.$newmessage );
				}
			} 
			
			} else {  
				//user non esistete	
				$messerror = '';
				$messerror = $messerror . "\xF0\x9F\x91\xA4 " . __("Unknown user:", "notification-for-telegram") . " '" . $username . "' " . __("Failed to login on", "notification-for-telegram") . " " . $bloginfo . $passwordmess . "\r\n";

				$filtered_message  ="";
				$filtered_message = apply_filters('nftb_unknown_user_fails_login_notification', $user_id); 

				if (has_filter('nftb_unknown_user_fails_login_notification')) {  } else { $filtered_message = ""; }

				if(!empty($username)){ 
					nftb_send_teleg_message($messerror .$filtered_message ."\r\n".$newmessage );
				} 
			}
				
			//empty user pass do nthing
			if (empty($username) || empty($password)) {
				//$messerror .= "\xF0\x9F\x91\xA4Empty Username ". $username. " or Password ".$password;  
				$messerror .= "\xF0\x9F\x91\xA4 " . __("Empty Username", "notification-for-telegram") . " " . $username . " " . __("or Password", "notification-for-telegram") . " " . $password;

				} else  { } 
	
		return $user;
    
    }
}, 10, 3 );


//Comments
add_action('wp_insert_comment', 'nftb_custom_comment_notification');
function nftb_custom_comment_notification($comment_id) {
	$TelegramNotify2 = new nftb_TelegramNotify();
	

	
	if ($TelegramNotify2->getValuefromconfig('notify_new_comments')) {

		$comment = get_comment($comment_id);
		
		$bloginfo = get_bloginfo( 'name' );
		$post = get_post($comment->comment_post_ID);

		
		if ($post && get_post_type($post) === 'shop_order') {
			// $post is a WooCommerce order
		} else {
			// $post is not a WooCommerce order
		

			$spam = 0;

			if ($comment && $comment->comment_approved === '1') {
				// The comment is published (approved).
				$comment_status_message = "\r\n\r\n\xE2\x9C\x85 " . __("This comment is published.", "notification-for-telegram") . "\r\n";

			} elseif ($comment && $comment->comment_approved === '0') {
				// The comment is pending moderation.
				$comment_status_message = "\r\n\r\n\xF0\x9F\x95\x90 " . __("This comment is pending approval.", "notification-for-telegram") . "\r\n";

			} elseif ($comment && $comment->comment_approved === 'spam') {
				// The comment is marked as spam.
				$comment_status_message = "\r\n\r\n\xE2\x9D\x8C " . __("This comment is marked as spam.", "notification-for-telegram") . "\r\n";

				$spam = 1;
			} elseif ($comment && $comment->comment_approved === 'trash') {
				// The comment is in the trash.
				$comment_status_message = "\r\n\r\n" . __("This comment is in the trash.", "notification-for-telegram") . "\r\n";

			} else {
				// Handle other comment statuses here.
				$comment_status_message = "\r\n\r\n" . __("This comment has a different status.", "notification-for-telegram") . "\r\n";

			}

			$author_email = $post->post_author ? get_the_author_meta('user_email', $post->post_author) : get_option('admin_email');
			
			$subject = "\xF0\x9F\x94\x94 " . __("New Comment in", "notification-for-telegram") . " " . $bloginfo . ' ' . __("on", "notification-for-telegram") . ' ' . $post->post_title;

			$message = "\r\n\xF0\x9F\x93\x84" . get_permalink($post) . ' by ' . $comment->comment_author . ': '."\r\n\r\n" . $comment->comment_content;
			if ($TelegramNotify2->getValuefromconfig('notify_new_comments_filter_spam') && $spam === 1)
			{ 

				//skip sending message

			 } else
			{
				nftb_send_teleg_message($subject .$comment_status_message.$message );
			}

			
			
		}
		
	}
}





add_action( '_core_updated_successfully','nftb_my_core_updated_successfully');
function nftb_my_core_updated_successfully(){
    //code to be executed after WordPress Core Update
}
  



//NINJA FORM FIX  FOR PHP >8
add_filter('ninja_forms_submit_data', 'nftb_my_ninja_forms_submit_data');

function nftb_my_ninja_forms_submit_data($form_data) {
    $TelegramNotify2 = new nftb_TelegramNotify();
    
    // Check if the Telegram notification is enabled
    if ($TelegramNotify2->getValuefromconfig('notify_ninjaform') && is_plugin_active('ninja-forms/ninja-forms.php')) {
        $form_fields = $form_data['fields'];
        $message = ''; // Initialize message variable

        foreach ($form_fields as $field) {
            $field_id = $field['id'];
            $field_key = $field['key'];
            $field_value = $field['value'];

            // Check if field value is not empty and is not an array
            if (!empty($field_value) && !is_array($field_value)) {
                $arr = explode("_", $field_key);
                $firstfield_key = $arr[0];
                $message .= " - " . $firstfield_key . " : " . $field_value. "\r\n";
            }
        }

        $form_settings = $form_data['settings']; // Form settings
        $extra_data = !empty($form_data['extra']) ? $form_data['extra'] : []; // Ensure extra data is an array
        $bloginfo = get_bloginfo('name');

        // Send the message to Telegram
		nftb_send_teleg_message(__("New Form", "notification-for-telegram") . " " . $bloginfo . "\r\n" . $message);


    }

    return $form_data; // Always return the modified form data
}




add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'nftb_add_plugin_page_settings_link');
function nftb_add_plugin_page_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'admin.php?page=telegram-notify' ) .
		'">' . __('Settings') . '</a>';
	return $links;
}



add_action('elementor_pro/forms/new_record', 'nftb_elementor_form', 10, 2);

function nftb_elementor_form($record, $ajax_handler) {
	$TelegramNotify2 = new nftb_TelegramNotify();
	
	if ($TelegramNotify2->getValuefromconfig('notify_ele_form') && defined('ELEMENTOR_PRO_VERSION') ) {
		
		//make sure its our form
		$form_name = $record->get_form_settings( 'form_name' );

		// Replace MY_FORM_NAME with the name you gave your form
		$message = "";

		$raw_fields = $record->get( 'fields' );
		$fields = [];
		


		foreach ( $raw_fields as $id => $field ) {
			$message = $message. $field['title']." (".$field['id'].") : " . $field['value']."\r\n";

		}
		$bloginfo = get_bloginfo( 'name' );
		
		nftb_send_teleg_message(__("New Form", "notification-for-telegram") . " " . $bloginfo . " / " . $form_name . "\r\n" . $message);

		
	}
}



// WP ACTIVITY LOG // Check for required plugins
add_action('init', function() {
	
	$telegram_notify_option = get_option('telegram_notify_option_name_tab2');
    $wpactivitylog = isset($telegram_notify_option['wpactivitylog']) ? $telegram_notify_option['wpactivitylog'] : false;

	

    if (defined('WSAL_VERSION') && function_exists('nftb_send_teleg_message') && $wpactivitylog ) {

        add_filter('wsal_event_data_before_log', 'nftb_wsal_explode_intercept', 10, 2);
        
        function nftb_wsal_explode_intercept($event_data, $alert_id) {
			


            if (empty($alert_id)) return $event_data;
            
            $message = "🔔 WP Activity Log - Event #" . $alert_id . "\n";
			$message .= "🕒 " . current_time('Y-m-d H:i:s') . "\n"; // ← CORRETTO
            $message .= "═══════════════════════════\n\n";
            
            // Esplodi l'array completo
            $message .= nftb_explode_array($event_data);
            
           
                nftb_send_teleg_message($message);
            
            
            return $event_data;
        }
        
        function nftb_explode_array($array, $prefix = "", $depth = 0) {
            $message = "";
            $indent = str_repeat("  ", $depth);
            
            foreach ($array as $key => $value) {
                $formatted_key = $prefix . ucfirst(preg_replace('/([A-Z])/', ' $1', $key));
                
                if (is_object($value)) {
                    $message .= $indent . "• " . $formatted_key . ":\n";
                    $message .= nftb_explode_array(get_object_vars($value), "", $depth + 1);
                } elseif (is_array($value)) {
                    $message .= $indent . "• " . $formatted_key . ":\n";
                    $message .= nftb_explode_array($value, "", $depth + 1);
                } else {
                    // Formatta valori speciali
                    if ($key === 'Timestamp' && is_numeric($value)) {
                        $value = date('Y-m-d H:i:s', $value);
                    }
                    if ($key === 'PluginFile') {
                        $value = basename($value); // Solo nome file
                    }
                    
                    $message .= $indent . "• " . $formatted_key . ": " . $value . "\n";
                }
            }
            
            return $message;
        }

    }
}, 20);