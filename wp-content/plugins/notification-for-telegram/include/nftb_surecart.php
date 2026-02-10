<?php 


if (!defined('ABSPATH')) {
    exit;
}


//Surecart
add_action('surecart/checkout_confirmed', 'nftb_get_checkout_info_text', 10, 2);


function nftb_get_checkout_info_text($checkout, $request) {
    // Log iniziale
    // error_log('[' . gmdate('Y-m-d H:i:s') . '] Hook surecart/checkout_confirmed chiamato');

	$TelegramNotify2 = new nftb_TelegramNotify();

	if ($TelegramNotify2->getValuefromconfig('notify_surecart_order')) { 
		
      
		
			$hide_edit_button = $TelegramNotify2->getValuefromconfig('surecart_hide_edit_link');


		// Verifica se l'oggetto checkout è valido
		if (!$checkout instanceof \SureCart\Models\Checkout) {
			error_log('[' . gmdate('Y-m-d H:i:s') . '] ERRORE: Oggetto checkout non valido');
			return;
		}

		try { // Inizio blocco try
			// Log struttura checkout per debug
			//error_log('[' . gmdate('Y-m-d H:i:s') . '] Struttura checkout: ' . print_r($checkout, true));

            // Estrai l'ID dell'ordine per il link di modifica
            $order_id = !empty($checkout->order) ? $checkout->order : ($checkout->id ?? '');

			// Inizializza il messaggio
			$message = '';
           
			// Usa il campo number come numero dell'ordine per il messaggio
			$order_number = !empty($checkout->number) ? $checkout->number : ($checkout->id ?? 'N/A');
			if ($order_number === 'N/A') { // Inizio if
				//error_log('[' . gmdate('Y-m-d H:i:s') . '] AVVISO: Numero ordine non disponibile, uso ID ordine');
				$order_number = is_string($checkout->order) ? $checkout->order : ($checkout->order->id ?? 'N/A');
			} // Fine if

			// Raccolta dati generali
			$bloginfo = get_bloginfo('name');
			$currency_code = !empty($checkout->currency) ? strtoupper($checkout->currency) : 'N/A';
			$total = isset($checkout->total_amount) ? number_format($checkout->total_amount / 100, 2) : '0.00';
			$status = !empty($checkout->status) ? $checkout->status : 'N/A';
			$order_date = isset($checkout->created_at) ? gmdate('j F Y, g:i a', $checkout->created_at) : gmdate('j F Y, g:i a');
			$order_notes = ''; // Non disponibile direttamente in checkout

			// Dati cliente
			$billing_address = is_object($checkout->billing_address) ? $checkout->billing_address : new stdClass();
			// Corregge il warning: usa direttamente $checkout->shipping_address
			$shipping_address = is_object($checkout->shipping_address) ? $checkout->shipping_address : new stdClass();
			if ($checkout->billing_matches_shipping) { // Inizio if
				$billing_address = $shipping_address;
			} // Fine if

			$first_name = !empty($checkout->first_name) ? $checkout->first_name : ($billing_address->name ?? '');
			$last_name = !empty($checkout->last_name) ? $checkout->last_name : '';
			$billing_email = !empty($checkout->email) ? $checkout->email : '';
			$billing_company = $billing_address->company ?? '';
			$billing_address_1 = $billing_address->line_1 ?? '';
			$billing_address_2 = $billing_address->line_2 ?? '';
			$billing_city = $billing_address->city ?? '';
			$billing_state = $billing_address->state ?? '';
			$billing_postcode = $billing_address->postal_code ?? '';
			$billing_country = $billing_address->country ?? '';

			$shipping_first_name = $shipping_address->name ?? '';
			$shipping_last_name = '';
			$shipping_company = $shipping_address->company ?? '';
			$shipping_address_1 = $shipping_address->line_1 ?? '';
			$shipping_address_2 = $shipping_address->line_2 ?? '';
			$shipping_city = $shipping_address->city ?? '';
			$shipping_state = $shipping_address->state ?? '';
			$shipping_postcode = $shipping_address->postal_code ?? '';
			$shipping_country = $shipping_address->country ?? '';

			// Numero di telefono
			$phone = $billing_address->phone ?? ($checkout->phone ?? '');

			// Metodo di pagamento
			$payment_method = isset($checkout->payment_intent->processor_type) ? $checkout->payment_intent->processor_type : 'Sconosciuto';

			// Stato pagamento
			$paid = ($status === 'paid') ? __('Order Paid', 'notification-for-telegram') : __('Order NOT Paid', 'notification-for-telegram');

			// Informazioni di fatturazione
			$billing_line = '';
			if (!empty($billing_first_name) || !empty($billing_last_name)) { // Inizio if
				$billing_line .= __('BILL TO:', 'notification-for-telegram') . "\r\n";
				$billing_line .= "$first_name $last_name\r\n";
			} // Fine if
			if (!empty($billing_company)) { // Inizio if
				$billing_line .= __('Company:', 'notification-for-telegram') . " $billing_company\r\n";
			} // Fine if
			if (!empty($billing_address_1)) { // Inizio if
				$billing_line .= __('Address:', 'notification-for-telegram') . " $billing_address_1 $billing_address_2\r\n";
			} // Fine if
			if (!empty($billing_city)) { // Inizio if
				$billing_line .= __('City:', 'notification-for-telegram') . " $billing_city\r\n";
			} // Fine if
			if (!empty($billing_state)) { // Inizio if
				$billing_line .= __('State:', 'notification-for-telegram') . " $billing_state\r\n";
			} // Fine if
			if (!empty($billing_postcode)) { // Inizio if
				$billing_line .= "$billing_postcode\r\n";
			} // Fine if
			if (!empty($billing_country)) { // Inizio if
				$billing_line .= "$billing_country\r\n";
			} // Fine if

			// Informazioni di spedizione
			$shipping_line = '';
			if (!empty($shipping_address_1)) { // Inizio if
				$shipping_line .= __('SHIP TO:', 'notification-for-telegram') . "\r\n";
				$shipping_line .= "$shipping_first_name $shipping_last_name\r\n";
				if (!empty($shipping_company)) { // Inizio if annidato
					$shipping_line .= __('Company:', 'notification-for-telegram') . " $shipping_company\r\n";
				} // Fine if annidato
				$shipping_line .= __('Address:', 'notification-for-telegram') . " $shipping_address_1 $shipping_address_2\r\n";
				if (!empty($shipping_city)) { // Inizio if annidato
					$shipping_line .= __('City:', 'notification-for-telegram') . " $shipping_city\r\n";
				} // Fine if annidato
				if (!empty($shipping_state)) { // Inizio if annidato
					$shipping_line .= __('State:', 'notification-for-telegram') . " $shipping_state\r\n";
				} // Fine if annidato
				if (!empty($shipping_postcode)) { // Inizio if annidato
					$shipping_line .= "$shipping_postcode\r\n";
				} // Fine if annidato
				if (!empty($shipping_country)) { // Inizio if annidato
					$shipping_line .= "$shipping_country\r\n";
				} // Fine if annidato
			} // Fine if

			// Conteggio ordini completati (allineato con WooCommerce)
			$order_count = '';
			$customer_email = $billing_email;
			if (!empty($customer_email)) { // Inizio if
				$completed_order_count = 0;
				$orders = \SureCart\Models\Order::where(['email' => $customer_email])->get();
				if (!empty($orders)) { // Inizio if annidato
					foreach ($orders as $order) { // Inizio foreach
						if ($order->status === 'paid') { // Inizio if annidato
							$completed_order_count++;
						} // Fine if annidato
					} // Fine foreach
					$order_count = "\xF0\x9F\x94\xA2 " . __('Completed order count:', 'notification-for-telegram') . " $completed_order_count\r\n";
				} // Fine if annidato
			} // Fine if

			// Costruzione del messaggio
			$message .= "\xE2\x9C\x8C " . esc_html__('New order', 'notification-for-telegram') . " #$order_number " . esc_html__('on', 'notification-for-telegram') . " $bloginfo \xE2\x9C\x8C\r\n";
        
       

			$message .= "\xF0\x9F\x91\x89 $first_name $last_name, $billing_email\r\n";
			$message .= "\xF0\x9F\x92\xB0 $total $currency_code\r\n";
			$message .= esc_html__($paid, 'notification-for-telegram') . " (" . esc_html($payment_method) . ")\r\n";
            $message .= esc_html__('Order ID', 'notification-for-telegram') . ": $order_id\r\n";
            
			$message .= esc_html__('Order Status', 'notification-for-telegram') . ": $status\r\n";
			$message .= esc_html__('Order Date', 'notification-for-telegram') . ": $order_date\r\n";

			// Aggiungi telefono se presente
			if (!empty($phone)) { // Inizio if
				$message .= trim($phone) . "\r\n";
			} // Fine if

			// Aggiungi conteggio ordini completati
			$message .= $order_count;

			// Articoli
			$line_items = isset($checkout->line_items->data) ? $checkout->line_items->data : [];
			if (!empty($line_items)) { // Inizio if
				$message .= "\r\n------ " . __('ITEMS', 'notification-for-telegram') . " ------\r\n";
				foreach ($line_items as $item) { // Inizio foreach
					$item_name = !empty($item->price->product->name) ? $item->price->product->name : 'Unknown Product';
					if (!empty($item->variant_options)) { // Inizio if annidato
						$item_name .= ' (' . implode(', ', $item->variant_options) . ')';
					} // Fine if annidato
					$quantity = !empty($item->quantity) ? (int) $item->quantity : 1;
					$line_total = isset($item->total_amount) ? number_format($item->total_amount / 100, 2) : '0.00';
					$message .= "$quantity x $item_name - $line_total $currency_code\r\n";
				} // Fine foreach
				$message .= "-------------------\r\n";
			} // Fine if

			// Configurazione opzioni (allineato con WooCommerce)
			
			// Aggiungi fatturazione se non nascosta
			if (!empty($billing_line)) { // Inizio if
				$message .= "\r\n\xF0\x9F\x93\x9D $billing_line";
            }

			// Aggiungi spedizione se non nascosta
			if (!empty($shipping_line)) { // Inizio if
				$message .= "\r\n\xF0\x9F\x9A\x9A $shipping_line";
			} // Fine if

			// Aggiungi note dell'ordine
			if (!empty($order_notes)) { // Inizio if
				$message .= "\r\n\xF0\x9F\x93\x9D " . __('Order Notes:', 'notification-for-telegram') . " $order_notes\r\n";
			} // Fine if

		
			// Log per debug
			//error_log('[' . gmdate('Y-m-d H:i:s') . '] ID ordine usato per il link: ' . ($order_id ?: 'N/A'));
			//error_log('[' . gmdate('Y-m-d H:i:s') . '] Valori disponibili - order: ' . ($checkout->order ?? 'N/A') . ', order_id: ' . ($checkout->order_id ?? 'N/A') . ', order->id: ' . ($checkout->order->id ?? 'N/A') . ', checkout->id: ' . ($checkout->id ?? 'N/A'));

			// Genera il link di modifica
			$edit_url = !empty($order_id) ? admin_url("admin.php?page=sc-orders&action=edit&id=$order_id") : '';
			if (empty($order_id)) { // Inizio if
				//error_log('[' . gmdate('Y-m-d H:i:s') . '] AVVISO: ID ordine non disponibile per checkout #' . ($checkout->id ?? 'N/A'));
			} // Fine if


			

			// Invia messaggio Telegram
			if ($hide_edit_button) {
				nftb_send_teleg_message($message);
			} else {
			nftb_send_teleg_message($message, __('EDIT ORDER N.', 'notification-for-telegram') . " #$order_number", $edit_url, '');
			}

			// Log del messaggio
			//error_log('[' . gmdate('Y-m-d H:i:s') . '] Messaggio generato: ' . $message);

		} catch (Exception $e) { // Inizio catch
			error_log('[' . gmdate('Y-m-d H:i:s') . '] ERRORE: Eccezione durante l\'elaborazione del checkout #' . ($checkout->id ?? 'N/A') . ': ' . $e->getMessage());
			return;
		} // Fine catch

	}	
} // Fine surecart order craeted





add_action('rest_api_init', function () {
    register_rest_route('surecart-webhook/v1', '/receive', [
        'methods' => 'POST', // Solo POST per i webhook
        'callback' => 'nftb_handle_surecart_webhook',
        'permission_callback' => '__return_true',
    ]);
    //error_log('SureCart: Endpoint registrato');
    // Genera l'URL completo dell'endpoint
    $webhook_url = rest_url('surecart-webhook/v1/receive');

    // Puoi salvare l'URL in una variabile globale, in un'opzione, o usarlo direttamente
    // Esempio: salvataggio in una variabile globale
    $GLOBALS['surecart_webhook_url'] = $webhook_url;
});

function nftb_handle_surecart_webhook(WP_REST_Request $request) {
    // Verifica firma (come già fai)
    if (!verify_surecart_webhook_signature($request)) {
        error_log('SureCart: Invalid webhook signature. This may be due to a missing or incorrect Signing Secret for the webhook endpoint.');
        return new WP_REST_Response(['error' => 'Invalid signature'], 400);
    }


	
    $payload = json_decode($request->get_body(), true);
    $event_type = $payload['type'] ?? '';
    $event_data = $payload['data']['object'] ?? [];
    $webhook_url = $GLOBALS['surecart_webhook_url'] ?? '';
    switch ($event_type) {
        case 'refund.created':
            nftb_handle_refund_created($event_data);
            break;

        case 'refund.succeeded':
            nftb_handle_refund_succeeded($event_data);
            break;

        case 'order.cancelled':
            nftb_handle_order_cancelled($event_data);
            break;

        case 'order.voided':
            nftb_handle_order_voided($event_data);
            break;

        case 'variant.stock_adjusted':
        nftb_handle_stock_adjusted($event_data);
            break;

        case 'order.fulfilled':
            nftb_handle_order_fulfilled($event_data);
            break;
        
        case 'order.unfulfilled':
            nftb_handle_order_unfulfilled($event_data);
            break;

        case 'fulfillment.updated':
            nftb_handle_order_fulfilled_update($event_data);
            break;
            

        case 'order.shipped':
            nftb_handle_order_shipped($event_data,__('Order Shipped', 'notification-for-telegram'));
            break;    

        case 'order.delivered':
            nftb_handle_order_shipped($event_data,__('Order Delivered', 'notification-for-telegram'));
            break;

        case 'order.unshipped':
            nftb_handle_order_shipped($event_data,__('Order Unshipped', 'notification-for-telegram'));
            break;    

        case 'order.paid':
            nftb_handle_order_paid($event_data,__('Order Paid', 'notification-for-telegram'));
            break;
            

        case 'webhook_endpoint.tested':
            nftb_send_teleg_message('webhook_endpoint.tested WORKS on '.$webhook_url. ' !!');
            break;    
            
            
        default:
            error_log("SureCart: Event not managed by the Notification for Telegram plugin: $event_type");
    }

    return new WP_REST_Response(['status' => 'ok'], 200);
}


function verify_surecart_webhook_signature(WP_REST_Request $request) {
    $TelegramNotify2 = new nftb_TelegramNotify();

	$webhook_secret = $TelegramNotify2->getValuefromconfig('Signing_Secret');
  

    $signature = $request->get_header('x_webhook_signature');
    $timestamp = $request->get_header('x_webhook_timestamp');
    $payload = $request->get_body();
    $payload_data = json_decode($payload, true);
    $event_type = isset($payload_data['type']) ? $payload_data['type'] : 'unknown';

    // if ($event_type === 'webhook_endpoint.tested') {
    //     error_log('SureCart: Evento di test, firma non richiesta');
    //     return true;
    // }

    if (!$signature || !$timestamp) {
        error_log('SureCart: Missing signature or timestamp');
        return false;
    }

    //$webhook_secret = defined('SURECART_WEBHOOK_SECRET') ? SURECART_WEBHOOK_SECRET : get_option('surecart_webhook_secret', '');
    //error_log('SureCart: Secret utilizzato: ' . ($webhook_secret ?: 'Nessun secret configurato'));
    if (empty($webhook_secret)) {
        
        error_log('SureCart: Missing or incorrect Signing Secret for the webhook endpoint.');
        return false;
    }

    // Normalizza payload
    //$normalized_payload = json_encode(json_decode($payload, true), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $normalized_payload = trim($payload);
    $signed_payload = $timestamp . '.' . $normalized_payload;
    $expected_signature_with_timestamp = hash_hmac('sha256', $signed_payload, $webhook_secret);

    // error_log('SureCart: Firma ricevuta: ' . $signature);
    // error_log('SureCart: Firma attesa (con timestamp): ' . $expected_signature_with_timestamp);
    // error_log('SureCart: Payload originale: ' . $payload);
    // error_log('SureCart: Payload normalizzato: ' . $normalized_payload);
    // error_log('SureCart: Signed payload (con timestamp): ' . $signed_payload);

    return hash_equals($expected_signature_with_timestamp, $signature);
}
function nftb_handle_order_shipped($data, $event) {
    //error_log('SureCart: order_shipped Contenuto data: ' . print_r($data, true));
   

    $telegram_notify_options = get_option( 'telegram_notify_option_name_tab5' ); // Array of All Options
    $order_shipped = isset($telegram_notify_options['order_shipped']) ? $telegram_notify_options['order_shipped'] : null;
    if ($order_shipped) {
        return;
        }
    
    
    $order_number      = $data['number'];
    $order_id          = $data['id'];
    $status            = $data['status'];
    $fulfillment       = $data['fulfillment_status'];
    $shipment_status   = $data['shipment_status'];
    $statement_url     = $data['statement_url'];
    $updated_timestamp = $data['updated_at'];
    // $name = nftb_get_customer_name_by_order($order_id);
    //$tracking = nftb_get_tracking_info_by_order($order_id);
    // error_log('SureCart: tracking order_shipped: ' . print_r($tracking, true));


    $message  = "✅ *" . __(''.$event, 'notification-for-telegram') . "*\n";
   
    $message .= "🧾 " . __('Order No.', 'notification-for-telegram') . ": *{$order_number}*\n";
    $message .= "📦 " . __('Order ID', 'notification-for-telegram') . ": `{$order_id}`\n";
    //$message .= "👤 " . __('Customer name', 'notification-for-telegram') . ": `{$name}`\n";
    $message .= "📤 " . __('Fulfillment', 'notification-for-telegram') . ": *{$fulfillment}*\n";
    $message .= "🚚 " . __('Shipment Status', 'notification-for-telegram') . ": *{$shipment_status}*\n";

    $message .= "📄 " . __('Order Status', 'notification-for-telegram') . ": *{$status}*\n";
    $message .= "🕒 " . __('Fulfilled At', 'notification-for-telegram') . ": `" . date('Y-m-d H:i:s', $updated_timestamp) . "`\n";
    //$message .= "🔗 [" . __('View Order Statement', 'notification-for-telegram') . "]({$statement_url})";
    
    $TelegramNotify2 = new nftb_TelegramNotify();
	$hide_edit_button = $TelegramNotify2->getValuefromconfig('surecart_hide_edit_link');

    // Genera il link di modifica
    $edit_url = !empty($order_id) ? admin_url("admin.php?page=sc-orders&action=edit&id=$order_id") : '';

    // Invia messaggio Telegram
			if ($hide_edit_button) {
				nftb_send_teleg_message($message);
			} else {
			nftb_send_teleg_message($message, __('EDIT ORDER N.', 'notification-for-telegram') . " #$order_number", $edit_url, '');
			}
}

function nftb_handle_order_unfulfilled($data) {

    $telegram_notify_options = get_option( 'telegram_notify_option_name_tab5' ); // Array of All Options
    $order_unfulfilled = isset($telegram_notify_options['order_unfulfilled']) ? $telegram_notify_options['order_unfulfilled'] : null;
    if ($order_unfulfilled) {
        return;
        }

    //error_log('SureCart: Contenuto data: ' . print_r($data, true));
    $order_number      = $data['number'];
    $order_id          = $data['id'];
    $status            = $data['status'];
    $fulfillment       = $data['fulfillment_status'];
    $shipment_status   = $data['shipment_status'];
    $statement_url     = $data['statement_url'];
    $updated_timestamp = $data['updated_at'];
    $name = nftb_get_customer_name_by_order($order_id);

    $message  = "✅ *" . __('Order Unfulfilled', 'notification-for-telegram') . "*\n";
    $message .= "🧾 " . __('Order No.', 'notification-for-telegram') . ": *{$order_number}*\n";
    $message .= "📦 " . __('Order ID', 'notification-for-telegram') . ": `{$order_id}`\n";
    $message .= "👤 " . __('Customer name', 'notification-for-telegram') . ": `{$name}`\n";
    $message .= "📤 " . __('Fulfillment', 'notification-for-telegram') . ": *{$fulfillment}*\n";
    $message .= "🚚 " . __('Shipment Status', 'notification-for-telegram') . ": *{$shipment_status}*\n";
    $message .= "📄 " . __('Order Status', 'notification-for-telegram') . ": *{$status}*\n";
    $message .= "🕒 " . __('Fulfilled At', 'notification-for-telegram') . ": `" . date('Y-m-d H:i:s', $updated_timestamp) . "`\n";
    //$message .= "🔗 [" . __('View Order Statement', 'notification-for-telegram') . "]({$statement_url})";

    $TelegramNotify2 = new nftb_TelegramNotify();
	$hide_edit_button = $TelegramNotify2->getValuefromconfig('surecart_hide_edit_link');

    // Genera il link di modifica
    $edit_url = !empty($order_id) ? admin_url("admin.php?page=sc-orders&action=edit&id=$order_id") : '';

    // Invia messaggio Telegram
			if ($hide_edit_button) {
				nftb_send_teleg_message($message);
			} else {
			nftb_send_teleg_message($message, __('EDIT ORDER N.', 'notification-for-telegram') . " #$order_number", $edit_url, '');
			}
}

function nftb_handle_order_fulfilled($data) {

    $telegram_notify_options = get_option( 'telegram_notify_option_name_tab5' ); // Array of All Options
    $order_fulfilled = isset($telegram_notify_options['order_fulfilled']) ? $telegram_notify_options['order_fulfilled'] : null;
    if ($order_fulfilled) {
        return;
        }
    //error_log('SureCart: Contenuto data: ' . print_r($data, true));
    $order_number      = $data['number'];
    $order_id          = $data['id'];
    $status            = $data['status'];
    $fulfillment       = $data['fulfillment_status'];
    $shipment_status   = $data['shipment_status'];
    $statement_url     = $data['statement_url'];
    $updated_timestamp = $data['updated_at'];
    $name = nftb_get_customer_name_by_order($order_id);


    $message  = "✅ *" . __('Order Fulfilled', 'notification-for-telegram') . "*\n";
    $message .= "🧾 " . __('Order No.', 'notification-for-telegram') . ": *{$order_number}*\n";
    $message .= "📦 " . __('Order ID', 'notification-for-telegram') . ": `{$order_id}`\n";
    $message .= "👤 " . __('Customer name', 'notification-for-telegram') . ": `{$name}`\n";
    $message .= "📤 " . __('Fulfillment', 'notification-for-telegram') . ": *{$fulfillment}*\n";
    $message .= "🚚 " . __('Shipment Status', 'notification-for-telegram') . ": *{$shipment_status}*\n";
    $message .= "📄 " . __('Order Status', 'notification-for-telegram') . ": *{$status}*\n";
    $message .= "🕒 " . __('Fulfilled At', 'notification-for-telegram') . ": `" . date('Y-m-d H:i:s', $updated_timestamp) . "`\n";
    //$message .= "🔗 [" . __('View Order Statement', 'notification-for-telegram') . "]({$statement_url})";

    $TelegramNotify2 = new nftb_TelegramNotify();
	$hide_edit_button = $TelegramNotify2->getValuefromconfig('surecart_hide_edit_link');

    // Genera il link di modifica
    $edit_url = !empty($order_id) ? admin_url("admin.php?page=sc-orders&action=edit&id=$order_id") : '';

    // Invia messaggio Telegram
			if ($hide_edit_button) {
				nftb_send_teleg_message($message);
			} else {
			nftb_send_teleg_message($message, __('EDIT ORDER N.', 'notification-for-telegram') . " #$order_number", $edit_url, '');
			}
}


function nftb_handle_order_fulfilled_update($data) {
    


    //error_log('SureCart: nftb_handle_order_fulfilled_update Contenuto data: ' . print_r($data, true));
    $telegram_notify_options = get_option( 'telegram_notify_option_name_tab5' ); // Array of All Options
    $fulfillment_updated = isset($telegram_notify_options['fulfillment_updated']) ? $telegram_notify_options['fulfillment_updated'] : null;
    if ($fulfillment_updated) {
        return;
        }
    
    $order_number      = $data['number'];
    $order_id          = $data['id'];
   
    
    $shipment_status   = $data['shipment_status'];
  
    $updated_timestamp = $data['updated_at'];
    //$name = nftb_get_customer_name_by_order($order_id);

    $trackings = nftb_get_tracking_info_by_order($order_id) ?? [];

     

    $tracking_info = '';
    foreach ($trackings as $track) {
        $number = $track['number'] ?? 'N/A';
        $url = $track['url'] ?? '';
        $tracking_info .= "📦 Tracking Number: *{$number}*\n🔗 Tracking URL: {$url}\n\n";
    }

     //error_log('SureCart: tracking fulfilled_update: '.$tracking . print_r($tracking, true));

    $message  = "✅ *" . __('Order Fulfilled Update', 'notification-for-telegram') .$fulfillment_updated. "*\n";
    $message .= "🧾 " . __('Order No.', 'notification-for-telegram') . ": *{$order_number}*\n";
    $message .= "📦 " . __('Order ID', 'notification-for-telegram') . ": `{$order_id}`\n";
   // $message .= "👤 " . __('Customer name', 'notification-for-telegram') . ": `{$name}`\n";
    
    $message .= "🚚 " . __('Shipment Status', 'notification-for-telegram') . ": *{$shipment_status}*\n";
    $message .= "🕒 " . __('Fulfilled At', 'notification-for-telegram') . ": `" . date('Y-m-d H:i:s', $updated_timestamp) . "`\n";
    $message .= $tracking_info;
    $TelegramNotify2 = new nftb_TelegramNotify();
	$hide_edit_button = $TelegramNotify2->getValuefromconfig('surecart_hide_edit_link');

    // Genera il link di modifica
    $edit_url = !empty($order_id) ? admin_url("admin.php?page=sc-orders&action=edit&id=$order_id") : '';

    // Invia messaggio Telegram
			if ($hide_edit_button) {
				nftb_send_teleg_message($message);
			} else {
			nftb_send_teleg_message($message, __('EDIT ORDER N.', 'notification-for-telegram') . " #$order_number", $edit_url, '');
			}
}


function nftb_handle_order_paid($data) {
    //error_log('SureCart: Contenuto data nftb_handle_order_paid: ' . print_r($data, true));

    $telegram_notify_options = get_option( 'telegram_notify_option_name_tab5' ); // Array of All Options
    $order_paid = isset($telegram_notify_options['order_paid']) ? $telegram_notify_options['order_paid'] : null;
    if ($order_paid) {
        return;
        }

    $order_id = $data['id'] ?? '';
    $order_number = $data['number'] ?? 'N/A';
    $status = $data['status'] ?? 'N/A';
    $shipment_status = $data['shipment_status'] ?? 'N/A';
    $fulfillment_status = $data['fulfillment_status'] ?? 'N/A';
    $portal_url = $data['portal_url'] ?? '';
    $statement_url = $data['statement_url'] ?? '';
    $created_at = isset($data['created_at']) ? date('Y-m-d H:i:s', $data['created_at']) : current_time('mysql');

    $name = nftb_get_customer_name_by_order($order_id);

    $message  = "✅ *" . __('New Paid Order', 'notification-for-telegram') . "*\n";
    $message .= "🧾 " . __('Order Number', 'notification-for-telegram') . ": *{$order_number}*\n";
    $message .= "👤 " . __('Costumer Name', 'notification-for-telegram') . ": `{$name}`\n";
    $message .= "📦 " . __('Fulfillment Status', 'notification-for-telegram') . ": `{$fulfillment_status}`\n";
    $message .= "🚚 " . __('Shipment Status', 'notification-for-telegram') . ": `{$shipment_status}`\n";
   
    //$message .= "🔗 [Portal]({$portal_url}) | [Statement PDF]({$statement_url})\n";
    $message .= "🆔 " . __('Order ID', 'notification-for-telegram') . ": `{$order_id}`";


    $TelegramNotify2 = new nftb_TelegramNotify();
	$hide_edit_button = $TelegramNotify2->getValuefromconfig('surecart_hide_edit_link');

    // Genera il link di modifica
    $edit_url = !empty($order_id) ? admin_url("admin.php?page=sc-orders&action=edit&id=$order_id") : '';

    // Invia messaggio Telegram
			if ($hide_edit_button) {
				nftb_send_teleg_message($message);
			} else {
			nftb_send_teleg_message($message, __('EDIT ORDER N.', 'notification-for-telegram') . " #$order_number", $edit_url, '');
			}

   
}



function nftb_handle_refund_created($data) {

    $telegram_notify_options = get_option( 'telegram_notify_option_name_tab5' ); // Array of All Options
    $refund_created = isset($telegram_notify_options['refund_created']) ? $telegram_notify_options['refund_created'] : null;
    if ($refund_created) {
        return;
        }
    //error_log('SureCart: Refund richiesto per ID cliente: ' . $data['customer']);
    //error_log('SureCart: Contenuto data: ' . print_r($data, true));
    $amount_cents = $data['amount'];
    $amount_eur = number_format($amount_cents / 100, 2, ',', ''); // Output: 2,00

    $customer_id = $data['customer'];
    $reason = $data['reason'];
    $status = $data['status'];
    $currency = strtoupper($data['currency']); // EUR
    $customer_name = nftb_getCustomerNameById($customer_id);
    $message  = "💸 *" . __('Refund requested', 'notification-for-telegram') . "*\n";
    $message .= "👤 " . __('Customer ID', 'notification-for-telegram') . ": `{$customer_id}`\n";
    $message .= "👤 " . __('Customer name', 'notification-for-telegram') . ": `{$customer_name}`\n";
    $message .= "💰 " . __('Amount', 'notification-for-telegram') . ": *{$currency} {$amount_eur}*\n";
    $message .= "📄 " . __('Reason', 'notification-for-telegram') . ": {$reason}\n";
    $message .= "✅ " . __('Status', 'notification-for-telegram') . ": {$status}";
    

    nftb_send_teleg_message($message);
   
}

function nftb_handle_refund_succeeded($data) {

    $telegram_notify_options = get_option( 'telegram_notify_option_name_tab5' ); // Array of All Options
    $refund_succeeded = isset($telegram_notify_options['refund_succeeded']) ? $telegram_notify_options['refund_succeeded'] : null;
    if ($refund_succeeded) {
        return;
        }
    //error_log('SureCart: Refund eseguito per ID cliente: ' . $data['customer']);
    //error_log('SureCart: Contenuto data: ' . print_r($data, true));
    $amount_cents = $data['amount'];
    $amount_eur = number_format($amount_cents / 100, 2, ',', ''); // Output: 2,00

    $customer_id = $data['customer'];
    $reason = $data['reason'];
    $status = $data['status'];
    $currency = strtoupper($data['currency']); // EUR
    $customer_name = nftb_getCustomerNameById($customer_id);
    $message  = "💸 *" . __('Refund succeeded', 'notification-for-telegram') . "*\n";
    $message .= "👤 " . __('Customer ID', 'notification-for-telegram') . ": `{$customer_id}`\n";
    $message .= "👤 " . __('Customer name', 'notification-for-telegram') . ": `{$customer_name}`\n";
    $message .= "💰 " . __('Amount', 'notification-for-telegram') . ": *{$currency} {$amount_eur}*\n";
    $message .= "📄 " . __('Reason', 'notification-for-telegram') . ": {$reason}\n";
    $message .= "✅ " . __('Status', 'notification-for-telegram') . ": {$status}";
    

    nftb_send_teleg_message($message);
}


function nftb_handle_order_cancelled($data) {
    //error_log('SureCart: Ordine annullato, ID ordine: ' . $data['id']);
    //error_log('SureCart: Contenuto data: ' . print_r($data, true));
    // Invia alert? Disattiva accesso?
}

function nftb_handle_order_voided($data) {

    $telegram_notify_options = get_option( 'telegram_notify_option_name_tab5' ); // Array of All Options
    $order_voided = isset($telegram_notify_options['order_voided']) ? $telegram_notify_options['order_voided'] : null;
    if ($order_voided) {
        return;
        }
    //error_log('SureCart: Ordine annullato, ID ordine: ' . $data['id']);
    //error_log('SureCart: Contenuto data: ' . print_r($data, true));
    $order_number = $data['number'];
    $order_id = $data['id'];
    $status = $data['status'];
    $portal_url = $data['portal_url'];
    $customer_id = $data['customer'];
    $name = nftb_get_customer_name_by_order($order_id);
    $message  = "🛑 *" . __('Order cancelled', 'notification-for-telegram') . "*\n";
    $message .= "👤 " . __('Customer name', 'notification-for-telegram') . ": `{$name}`\n";
    $message .= "🧾 " . __('Order No.', 'notification-for-telegram') . ": *{$order_number}*\n";
    $message .= "📦 " . __('Order ID', 'notification-for-telegram') . ": `{$order_id}`\n";
    $message .= "❌ " . __('Status', 'notification-for-telegram') . ": {$status}\n";
    $message .= "🔗 [" . __('Order details', 'notification-for-telegram') . "]({$portal_url})";
    
    // Invia alert? Disattiva accesso?
    nftb_send_teleg_message($message);
}

function nftb_handle_stock_adjusted($data) {

    $telegram_notify_options = get_option( 'telegram_notify_option_name_tab5' ); // Array of All Options
    $variant_stock_adjusted = isset($telegram_notify_options['variant_stock_adjusted']) ? $telegram_notify_options['variant_stock_adjusted'] : null;
    if ($variant_stock_adjusted) {
        return;
        }
    global $wpdb;

    // error_log('SureCart: Ordine annullato, ID ordine: ' . $data['id']);
    // error_log('SureCart: Contenuto data stock_adjusted : ' . print_r($data, true));
    $option1 = $data['option_1'] ?? '';
    $option2 = $data['option_2'] ?? '';
    $variant_label = trim("{$option1} / {$option2}", ' /');
    $available_stock = $data['available_stock'];
    $held_stock = $data['held_stock'];
    $product_id = $data['product'];

    $product_name = getProductNameById($product_id);

    $message  = "📦 *" . __('Stock variant update', 'notification-for-telegram') . "*\n\n";
    $message .= "📋 " . __('Product name', 'notification-for-telegram') . ": *{$product_name}*\n";
    $message .= "🧩 " . __('Variant', 'notification-for-telegram') . ": *{$variant_label}*\n";
    $message .= "🔢 " . __('Available stock', 'notification-for-telegram') . ": *{$available_stock}*\n";
    $message .= "🛑 " . __('Held stock (pending)', 'notification-for-telegram') . ": *{$held_stock}*\n";
    $message .= "🆔 " . __('Product ID', 'notification-for-telegram') . ": `{$product_id}`";
    nftb_send_teleg_message($message);
}


function getProductNameById($product_id) {
    $TelegramNotify2 = new nftb_TelegramNotify();

	$api_key = $TelegramNotify2->getValuefromconfig('Secret_token');
    
    // URL API corretto per SureCart
    $url = "https://api.surecart.com/v1/products/" . urlencode($product_id);

    // Impostazione cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Accept: application/json',
        'Content-Type: application/json',
    ]);

    // Esecuzione richiesta
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch); // Cattura eventuali errori cURL
    curl_close($ch);

    // Log per debug (opzionale, rimuovi in produzione)
    //error_log("SureCart API: Chiamata a {$url}, HTTP Code: {$httpcode}, Risposta: " . ($response !== false ? $response : 'Nessuna risposta'));

    if ($httpcode === 200 && $response !== false) {
        $data = json_decode($response, true);
        if (isset($data['name']) && !empty($data['name'])) {
            return $data['name'];
        } else {
            error_log("SureCart API: Nome prodotto non trovato nei dati: " . print_r($data, true));
            return "Nome prodotto non trovato";
        }
    } else {
        error_log("SureCart API: Errore - HTTP Code: {$httpcode}, Errore cURL: {$error}");
        return "Errore API o prodotto non trovato";
    }
}


function nftb_getCustomerNameById($customer_id) {
 
    
    $TelegramNotify2 = new nftb_TelegramNotify();

	$api_key = $TelegramNotify2->getValuefromconfig('Secret_token');
    
    $url = "https://api.surecart.com/v1/customers/" . urlencode($customer_id);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Accept: application/json',
        'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    error_log("SureCart API: Chiamata cliente {$customer_id}, HTTP Code: {$httpcode}, Risposta: " . ($response !== false ? $response : 'Nessuna risposta'));

    if ($httpcode === 200 && $response !== false) {
        $data = json_decode($response, true);
        if (isset($data['name']) && !empty($data['name'])) {
            return $data['name'];
        } elseif (isset($data['email'])) {
            return $data['email']; // fallback
        } else {
            error_log("SureCart API: Nome cliente non trovato nei dati: " . print_r($data, true));
            return "Cliente sconosciuto";
        }
    } else {
        error_log("SureCart API: Errore cliente - HTTP Code: {$httpcode}, Errore cURL: {$error}");
        return "Errore API cliente";
    }
}


function nftb_get_customer_name_by_order($order_id) {
    $TelegramNotify2 = new nftb_TelegramNotify();

	$api_key = $TelegramNotify2->getValuefromconfig('Secret_token');
    $url = "https://api.surecart.com/v1/orders/" . urlencode($order_id) . "?expand[]=checkout.customer";

    // Impostazione cURL per ordine
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Accept: application/json',
        'Content-Type: application/json',
    ]);

    // Esecuzione richiesta ordine
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);


    if ($httpcode !== 200 || $response === false) {
        error_log("SureCart API: get_customer_name_by_order Errore recupero ordine {$order_id}, HTTP {$httpcode}, cURL Errore: {$error}");
        return null;
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("SureCart API: Errore decodifica JSON per ordine {$order_id}: " . json_last_error_msg());
        return null;
    }

    // Log della risposta completa
   // error_log("SureCart API: Dati ordine {$order_id}: " . print_r($data, true));

    // Verifica presenza di checkout
    if (!isset($data['checkout'])) {
        error_log("SureCart API: Campo 'checkout' assente per ordine {$order_id}: " . print_r($data, true));
        return null;
    }

    // Se checkout è un ID (stringa), fai una seconda chiamata
    if (is_string($data['checkout'])) {
        $checkout_id = $data['checkout'];
        $checkout_url = "https://api.surecart.com/v1/checkouts/" . urlencode($checkout_id) . "?expand[]=customer";

        // Impostazione cURL per checkout
        $ch = curl_init($checkout_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'Accept: application/json',
            'Content-Type: application/json',
        ]);

        // Esecuzione richiesta checkout
        $checkout_response = curl_exec($ch);
        $checkout_httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $checkout_error = curl_error($ch);
        curl_close($ch);

        // Log dettagliato
      //  error_log("SureCart API: Chiamata checkout, URL: {$checkout_url}, HTTP Code: {$checkout_httpcode}, Risposta: " . ($checkout_response !== false ? $checkout_response : 'Nessuna risposta') . ", cURL Errore: {$checkout_error}");

        if ($checkout_httpcode !== 200 || $checkout_response === false) {
            error_log("SureCart API: Errore recupero checkout {$checkout_id}, HTTP {$checkout_httpcode}, cURL Errore: {$checkout_error}");
            return null;
        }

        $checkout_data = json_decode($checkout_response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("SureCart API: Errore decodifica JSON per checkout {$checkout_id}: " . json_last_error_msg());
            return null;
        }

        // Log della risposta checkout
       // error_log("SureCart API: Dati checkout {$checkout_id}: " . print_r($checkout_data, true));

        $customer = $checkout_data['customer'] ?? null;
    } else {
        // Se checkout è un array, cerca direttamente customer
        $customer = $data['checkout']['customer'] ?? null;
    }

    // Verifica presenza di customer
    if (!$customer || !is_array($customer)) {
        error_log("SureCart API: Dati cliente assenti per ordine {$order_id}: " . print_r($data['checkout'] ?? [], true));
        return null;
    }

    // Estrai nome o email
    $name = $customer['name'] ?? ($customer['email'] ?? null);
    if (!$name) {
        error_log("SureCart API: Nome ed email cliente non trovati per ordine {$order_id}: " . print_r($customer, true));
        return null;
    }

    return $name;
}




    
    function nftb_get_tracking_info_by_order($fulfillment_ids) {
        $TelegramNotify2 = new nftb_TelegramNotify();

        $api_key = $TelegramNotify2->getValuefromconfig('Secret_token');
       
            $base_url = 'https://api.surecart.com/v1/trackings';
            
            // Costruisci la query string con array
            $query = http_build_query(['fulfillment_ids[]' => $fulfillment_ids]);
            $url = "{$base_url}?{$query}";
        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $api_key,
                'Accept: application/json'
            ]);
            $resp = curl_exec($ch);
            curl_close($ch);
        
            $data = json_decode($resp, true);
            return $data['data'] ?? [];
        }