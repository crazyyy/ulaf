<?php

function nftb_optimize_nftb_plugin_database() {
    global $wpdb;
  

    // Controlla se l'operazione è già stata eseguita
    if ( get_option( 'nftb_fix_1' ) === '1' ) {
        //error_log( 'Notification for Telegram - The operation nftb_new_order_id_for_notification_ set autoload to none has already been completed. No changes are necessary.' );
        return; // Termina l'esecuzione della funzione
    }

    $option_prefix = 'nftb_new_order_id_for_notification_';

    // Esegui la query per aggiornare il campo autoload a 'none'
    $rows_affected = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->options}
             SET autoload = %s
             WHERE option_name LIKE %s",
            'off', // Valore per il campo autoload
            $option_prefix . '%' // Pattern per il nome delle opzioni
        )
    );

    
    if ( $rows_affected === false ) {
       
        error_log( 'Notification for Telegram - An error occurred while executing the plugin optimization query.' );
        
    } elseif ( $rows_affected === 0 ) {
        
        
        error_log( 'Notification for Telegram -  No options were updated. Check the prefix and try again.' );
    } else {
      
        error_log( 'Notification for Telegram - We have optimized the database nftb_new_order_id_for_notification_ to make the Notification for Telegram plugin faster. The update modified ' . $rows_affected . ' records.' );
      
        
    }
    update_option( 'nftb_fix_1', '1', true);
}




