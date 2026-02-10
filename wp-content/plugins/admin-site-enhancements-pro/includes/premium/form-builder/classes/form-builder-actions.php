<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Actions {

    public $form;
    public $entry_id;
    public $form_settings;
    public $metas;
    public $location;

    /**
     * Check whether an array is associative.
     *
     * @param mixed $value Value to check.
     * @return bool
     */
    private function is_associative_array( $value ) {
        if ( ! is_array( $value ) || empty( $value ) ) {
            return false;
        }

        return array_keys( $value ) !== range( 0, count( $value ) - 1 );
    }

    public function __construct( $form, $entry_id, $form_settings, $metas, $location  ) {
        $this->form = $form;
        $this->entry_id = $entry_id;
        $this->form_settings = $form_settings;
        $this->metas = $metas;
        $this->location = $location;
    }
    
    public function run() {
    	$this->maybe_remove_db_entry();
    	$this->maybe_process_entry_data_for_webhooks();
    }
    
    public function maybe_remove_db_entry() {
        $enable_db_entries = isset( $this->form_settings['enable_db_entries'] ) ? $this->form_settings['enable_db_entries'] : 'on';

        if ( 'on' != $enable_db_entries ) {
            Form_Builder_Entry::destroy_entry( $this->entry_id );
        }
    }
    
    public function maybe_process_entry_data_for_webhooks() {
        $enable_webhooks = isset( $this->form_settings['enable_webhooks'] ) ? $this->form_settings['enable_webhooks'] : 'off';

        if ( 'on' == $enable_webhooks ) {
        	// Let's get fields data / settings
	        $fields_data = Form_Builder_Helper::get_fields_array( $this->form->id );
	        $fields = $fields_data['fields'];

	    	// Let's unserialize entry data / metas for certain field types.
            // Also, for legacy forms, generate a default webhook key from the field label when missing.
	    	$processed_metas = array();
            $used_webhook_keys = array();
	    	
	    	foreach ( $this->metas as $field_id => $meta ) {
	    		$processed_metas[$field_id]['name'] = $meta['name'];
	    		$processed_metas[$field_id]['type'] = $meta['type'];

                $effective_webhook_key = isset( $meta['webhook_key'] ) ? trim( (string) $meta['webhook_key'] ) : '';
                $is_generated_key      = false;

                if ( '' === $effective_webhook_key ) {
                    $effective_webhook_key = Form_Builder_Helper::generate_webhook_key_from_label( $meta['name'] );
                    $effective_webhook_key = ( '' !== $effective_webhook_key ) ? $effective_webhook_key : 'field';
                    $is_generated_key      = true;
                }

                // Avoid collisions for generated keys by suffixing the field ID.
                if ( $is_generated_key && isset( $used_webhook_keys[ $effective_webhook_key ] ) ) {
                    $base_key = $effective_webhook_key;
                    $effective_webhook_key = $base_key . '_' . absint( $field_id );
                    $i = 2;
                    while ( isset( $used_webhook_keys[ $effective_webhook_key ] ) ) {
                        $effective_webhook_key = $base_key . '_' . absint( $field_id ) . '_' . $i;
                        $i++;
                    }
                }

                $processed_metas[$field_id]['webhook_key'] = $effective_webhook_key;

                if ( '' !== $effective_webhook_key ) {
                    $used_webhook_keys[ $effective_webhook_key ] = true;
                }

	    		switch ( $meta['type'] ) {
	    			case 'name':
	    			case 'address':
	    			case 'radio':
	    			case 'checkbox':
	    			case 'image_select':
	    			case 'likert_matrix_scale':
	    			case 'matrix_of_dropdowns':
	    			case 'matrix_of_variable_dropdowns_two':
	    			case 'matrix_of_variable_dropdowns_three':
	    			case 'matrix_of_variable_dropdowns_four':
	    			case 'matrix_of_variable_dropdowns_five':
	    				$processed_metas[$field_id]['value'] = maybe_unserialize( $meta['value'] );
	    				break;
	    				
	    			default:
	    				$processed_metas[$field_id]['value'] = $meta['value'];
	    		}
	    	}
	    	// vi( $processed_metas );
	    	
	    	// Let's switch from field ID to the field webhook_key as the metas primary keys
	    	$processed_metas_by_webhook_keys = array();
	    	foreach ( $processed_metas as $field_id => $field_info ) {
	    		if ( isset( $field_info['webhook_key'] ) && ! empty( $field_info['webhook_key'] ) ) {
		    		$processed_metas_by_webhook_keys[$field_info['webhook_key']]['id'] = $field_id;
		    		$processed_metas_by_webhook_keys[$field_info['webhook_key']]['name'] = $field_info['name'];
		    		$processed_metas_by_webhook_keys[$field_info['webhook_key']]['type'] = $field_info['type'];
		    		$processed_metas_by_webhook_keys[$field_info['webhook_key']]['value'] = $field_info['value'];
	    		} else {
		    		$processed_metas_by_webhook_keys[$field_id]['id'] = $field_id;
		    		$processed_metas_by_webhook_keys[$field_id]['name'] = $field_info['name'];
		    		$processed_metas_by_webhook_keys[$field_id]['type'] = $field_info['type'];
		    		$processed_metas_by_webhook_keys[$field_id]['value'] = $field_info['value'];
	    		}
	    	}
	    	// vi( $processed_metas_by_webhook_keys );
	    	
	    	// Let's use the webhook_key for each field and its values directly.
            // Use the effective webhook_key (including legacy defaults) from $processed_metas.
	    	$named_flattened_metas = array();

	    	foreach ( $processed_metas as $field_id => $meta ) {
                $webhook_key = isset( $meta['webhook_key'] ) ? trim( (string) $meta['webhook_key'] ) : '';
                $key         = ( '' !== $webhook_key ) ? $webhook_key : $field_id;
                $named_flattened_metas[ $key ] = $meta['value'];
            }
	    	// vi( $named_flattened_metas );

            // Flatten associative arrays one level deep (e.g., Name sub-fields) into "key_subkey" pairs.
            $flat_named_metas = array();
            foreach ( $named_flattened_metas as $webhook_key => $value ) {
                if ( $this->is_associative_array( $value ) ) {
                    foreach ( $value as $sub_key => $sub_value ) {
                        $flat_named_metas[ $webhook_key . '_' . $sub_key ] = $sub_value;
                    }
                } else {
                    $flat_named_metas[ $webhook_key ] = $value;
                }
            }

	        $webhook_payload_type = isset( $this->form_settings['webhook_payload_type'] ) ? $this->form_settings['webhook_payload_type'] : 'full';
	        
	        switch ( $webhook_payload_type ) {
	        	case 'full': // Full (form ID, title, URL and raw data)
			    	$payload = array(
			    		'form_id'			=> $this->form->id,
			    		'form_title'		=> $this->form->name,
			    		'form_url'			=> $this->location, // URL of where form is being placed on
			    		'form_data'			=> $processed_metas,
			    	);
	        		break;

	        	case 'raw_by_field_id': // Field ID => field name, type, value, webhook_key
			    	$payload = $processed_metas;
	        		break;

	        	case 'raw_by_field_webhook_key': // Field webhook key => field ID, name, type, value
			    	$payload = $processed_metas_by_webhook_keys;
	        		break;
	        		
	        	case 'named': // Named (pairs of field webhook key and field value)
			    	$payload = $named_flattened_metas;
	        		break;

                case 'flat_named': // Flat named (pairs of field webhook key + subfield key and field value)
                    $payload = $flat_named_metas;
                    break;
	        }

	        // Use WordPress built-in WP_Http class to send the POST request
	        $webhook_urls = isset( $this->form_settings['webhook_urls'] ) ? $this->form_settings['webhook_urls'] : '';
	        if ( ! empty( $webhook_urls ) ) {
	        	$webhook_urls = explode( ',', $webhook_urls );

	        	foreach ( $webhook_urls as $webhook_url ) {
			        $response = wp_remote_post( $webhook_url, array(
			            'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
			            'body'        => json_encode( $payload ),
			            'method'      => 'POST',
			            'data_format' => 'body',
			        ));
	        	}
	        }
        }
    }

}