<?php 
/*
Plugin Name: Saphali Woocommerce Lite
Plugin URI: http://saphali.com/saphali-woocommerce-plugin-wordpress
Description: Saphali Woocommerce Lite - это бесплатный вордпресс плагин, который добавляет набор дополнений к интернет-магазину на Woocommerce.
Version: 2.0.1
Author: Saphali
Author URI: http://saphali.com/
Text Domain: saphali-woocommerce-lite
Domain Path: /languages
WC requires at least: 1.6.6
WC tested up to: 9.6
*/


/*

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software

 */
use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils;

/* Add a custom payment class to woocommerce
  ------------------------------------------------------------ */
  if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
  };

  define('SAPHALI_LITE_SYMBOL', 1 );
  
  // Подключение валюты и локализации
 define('SAPHALI_PLUGIN_DIR_URL',plugin_dir_url(__FILE__));
 define('SAPHALI_LITE_VERSION', '2.0.0' );
 define('SAPHALI_PLUGIN_DIR_PATH',plugin_dir_path(__FILE__));
 class saphali_lite {
 var $email_order_id;
 var $fieldss;
 var $fields_valid = array();
 var $unuque = array();
 var $locale_tmp;
 var $column_count_saphali;
 protected $keys;
 protected $checkout_type;

 protected static $_instance = null;
    /**
     * Main saphali_lite Instance.
     *
     * @since 1.0.0
     * @static
     * @see SaphWooManageFields()
     * @return saphali_lite - Main instance.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
	function __construct() {
		add_action('before_woocommerce_init', array($this,'woocommerce_init'), 9);
		if ( version_compare( WOOCOMMERCE_VERSION, '2.2.0', '<' ) || version_compare( WOOCOMMERCE_VERSION, '2.5.0', '>' ) ) {
			add_action( 'init', array($this,'load_plugin_textdomain') );
		}	else {
			add_action( 'init', array($this,'load_plugin_textdomain_th') );
		}
		
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) )  add_action('admin_menu', array($this,'woocommerce_saphali_admin_menu_s_l'), 9);
		else add_action('admin_menu', array($this,'woocommerce_saphali_admin_menu_s_l'), 10);
		
		add_action( 'woocommerce_thankyou',                     array( $this, 'order_pickup_location' ), 20 );
		add_action( 'woocommerce_view_order',                   array( $this, 'order_pickup_location' ), 20 );
		
		add_action( 'woocommerce_after_template_part',          array( $this, 'email_pickup_location' ), 10, 4 );
		
		// add_action( 'woocommerce_admin_order_totals_after_shipping', array( $this, 'woocommerce_admin_order_totals_after_shipping' ), 1 );
		add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'store_order_id' ), 1 );
		add_action( 'woocommerce_order_status_pending_to_completed_notification',  array( $this, 'store_order_id' ), 1 );
		add_action( 'woocommerce_order_status_pending_to_on-hold_notification',    array( $this, 'store_order_id' ), 1 );
		add_action( 'woocommerce_order_status_failed_to_processing_notification',  array( $this, 'store_order_id' ), 1 );
		add_action( 'woocommerce_order_status_failed_to_completed_notification',   array( $this, 'store_order_id' ), 1 );
		add_action( 'woocommerce_order_status_completed_notification',             array( $this, 'store_order_id' ), 1 );
		add_action( 'woocommerce_new_customer_note_notification',                  array( $this, 'store_order_id' ), 1 );
		add_action( 'wp_head', array( $this, 'generator' ) );
		add_filter( 'woocommerce_order_formatted_billing_address',  array($this,'formatted_billing_address') , 10 , 2); 
		add_filter( 'woocommerce_order_formatted_shipping_address',  array($this,'formatted_shipping_address') , 10 , 2); 
		
		if( !( isset($_GET['page']) && $_GET['page'] === 'woocommerce_saphali_s_l' ) ) {
			// Hook in
			
			add_filter( 'woocommerce_checkout_fields' , array($this,'saphali_custom_override_checkout_fields') );
			add_filter( 'wp' , array($this,'wp') );

			add_filter( 'woocommerce_billing_fields',  array($this,'saphali_custom_billing_fields'), 10, 1 );
			add_filter( 'woocommerce_shipping_fields',  array($this,'saphali_custom_shipping_fields'), 10, 1 );
			add_filter( 'woocommerce_default_address_fields',  array($this,'woocommerce_default_address_fields'), 10, 2 );
			//add_filter( 'woocommerce_get_country_locale',  array($this,'woocommerce_get_country_locale'), 10, 1 );
			add_action('admin_init', array($this,'woocommerce_customer_meta_fields_action'), 20);
			add_action( 'personal_options_update', array($this,'woocommerce_save_customer_meta_fields_saphali') );
			add_action( 'edit_user_profile_update', array($this,'woocommerce_save_customer_meta_fields_saphali') );
			/* add_action( 'woocommerce_admin_order_data_after_billing_address', array($this,'woocommerce_admin_order_data_after_billing_address_s') );
			add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this,'woocommerce_admin_order_data_after_shipping_address_s') ); */
			add_action( 'woocommerce_admin_order_data_after_order_details', array($this,'woocommerce_admin_order_data_after_order_details_s') );
			if ( ! version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) {
				$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
			
				if(isset($billing_data["billing"]) && is_array($billing_data["billing"])) {
					foreach ( $billing_data["billing"] as $key => $field ) {
						// var_dump($field["type"]);
						if (isset($field['show']) && !$field['show']) continue;

						$field_name = '_'.$key;
						$this->fields_valid[] = $field;
						add_filter('woocommerce_order_get_'. $field_name,  function($fild) {
							$field = array_shift($this->fields_valid);
							if( $fild && isset($field["type"]) && in_array($field["type"] , array('select', 'radio')) && isset($field["options"][$fild]) ) {
								$fild = $field["options"][$fild];
							}
							return $fild;
						});	
					}
				}
			}
		} else {
			if($_POST ){
				if(!(isset($_POST['saphali_save_fields']) || isset($_POST['saphali_reset_fields']) ) && !wp_verify_nonce( $_POST['_wpnonce'], 'fields-nonce'))
				wp_die( __( 'Неуспешная верификация.', 'saphali-woocommerce-lite' ) );
			}
		}
		add_filter( 'woocommerce_currencies',  array($this,'add_inr_currency') , 11);
		add_filter( 'woocommerce_currency_symbol',  array($this,'add_inr_currency_symbol') , 1, 2 ); 
		if ( !version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) )
		add_action( 'woocommerce_checkout_create_order',   array( $this, 'checkout_create_order' ), 99, 2 );
		else
			add_action( 'woocommerce_checkout_update_order_meta',   array( $this, 'checkout_update_order_meta' ), 99, 2 );
		$this->column_count_saphali = get_option('column_count_saphali');
		if(!empty($this->column_count_saphali)) {
			global $woocommerce_loop;
			$woocommerce_loop['columns'] = $this->column_count_saphali; 
			add_action("wp_head", array($this,'print_script_columns'), 10, 1);
			add_filter("loop_shop_columns", array($this, 'print_columns'), 10, 1);
			add_filter("woocommerce_output_related_products_args", array($this, 'related_print_columns'), 10, 1);
		}
		if(is_admin()) {
			add_filter( 'woocommerce_admin_billing_fields', array($this,'woocommerce_admin_billing_fields'), 10, 1 );
			add_filter( 'woocommerce_admin_shipping_fields', array($this,'woocommerce_admin_shipping_fields'), 10, 1 );
		}
		add_filter( 'woocommerce_checkout_posted_data', array($this,'woocommerce_checkout_posted_data'), 10, 1 );
		add_action("wp_enqueue_scripts", array($this,'wp_enqueue_scripts') );
		if( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) )
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'after_checkout_validation' ), 10 );
		else
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'after_checkout_validation' ), 10, 2 );

		// blocks
		// add_filter( 'woocommerce_default_address_fields', array($this, 'custom_validate_customer_address'), 10, 2 );
		add_action('wp_error_added', array($this, 'wp_error_added'), 10, 4);
		// do_action( 'wp_error_added', $code, $message, $data, $this );
	}
	function wp_error_added( $code, $message, $address_field_key, $wp_error ) {
		if(!class_exists('Saphali_DateTime_Checkout_Field')) return;
		$checkout_fields = SaphWooDateTime_Checkout_Field()->get_fields();
		$finish_fields = array();
		if ($checkout_fields) {
			foreach (array('billing', 'shipping') as $method) {
				if (isset($checkout_fields[$method])) {
					foreach ($checkout_fields[$method] as $key => $val) {
						$finish_fields[$method][str_replace("{$method}_", '', $key)] = $val['required'];
					}
					foreach ($checkout_fields['extra']['dellete_fields'] as $f) {
						$finish_fields[$method][str_replace("{$method}_", '', $f)] = false;
					}
				}
			}
		}

		if(strpos($message, str_replace('%s', '', __( '%s is required', 'woocommerce' ) ))) {
			if(isset($finish_fields[$code][$address_field_key]) && !$finish_fields[$code][$address_field_key]) {
				$wp_error->remove($code);
			}
		}
	}
	function custom_validate_customer_address( $address, $context = '' ) {
		// Удаляем валидацию для first_name
		// error_log(__FUNCTION__ . " " . __LINE__ . " | \n {$context} => " . var_export($address, true) );
		
	
		return $address;
	}
	public function remove_no_valid_filds($key, $value, $errors) {
		if (  version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) {
			$is_e = true;
			if( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {
				global $woocommerce;
				if(!empty($woocommerce->errors)) {
					foreach($woocommerce->errors as $i => $_e) {
						if( strpos($_e, strtolower(__($value["rf"], 'woocommerce')) ) !== false || strpos($_e, __($value["rf"], 'woocommerce')) !== false ) {
							unset($woocommerce->errors[$i]);
						} 
					}
				}
			} else {
				$s = WC()->session;
				$notices = $s->get( 'wc_notices', array() );
				if( isset( $notices['error'] ) ) {
					foreach($notices['error'] as $i => $_e) {
						if( strpos($_e, strtolower(__($value["rf"], 'woocommerce')) ) !== false || strpos($_e, __($value["rf"], 'woocommerce')) !== false ) {
							unset($notices['error'][$i]);
						} 
					}
				}
				
				if(empty($notices['error'])) {
					unset($notices['error']);
				}
				$s->set( 'wc_notices', $notices );
			}
			
		} else {
			if( is_wp_error($errors) ) {
				$is_e = true;
				if( isset( $errors->errors["required-field"] ) ) {
					foreach($errors->errors["required-field"] as $i => $_e) {
						if( strpos($_e, strtolower(__($value["rf"], 'woocommerce')) ) !== false || strpos($_e, __($value["rf"], 'woocommerce')) !== false ) {
							unset($errors->errors["required-field"][$i]);
						} 
						
					}
					
				} elseif( isset( $errors->errors ) && is_array($errors->errors) && (strpos(array_keys( $errors->errors )[0], 'billing_') === 0 || strpos(array_keys( $errors->errors )[0], 'shipping_') === 0)  ) {
					foreach($errors->errors as $i => $_e) {
						if( strpos($_e[0], strtolower(__($value["rf"], 'woocommerce')) ) !== false || strpos($_e[0], __($value["rf"], 'woocommerce')) !== false ) {
							unset($errors->errors[$i]);
						} 
					}
				}
			}
		}
		return $is_e;
	}
public function woocommerce_checkout_posted_data( $data ) {	
		if(! isset($this->fieldss) )
			$this->fieldss = get_option('woocommerce_saphali_filds_filters');
		$fieldss = $this->fieldss;
		$keys = array();
		if( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) && isset($_POST['ship_to_different_address']) || version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) && !isset($_POST['shiptobilling']) ) {
			$fieldss____ = array('billing', 'shipping');
		} else $fieldss____ = array('billing');
		foreach($fieldss____ as  $type) {
			foreach($fieldss[$type] as $key => $value) {
				if(isset($value['payment_method'])) {
					$pm_k_remove = array();
					foreach($value['payment_method'] as $k => $v) {
						if($v === '0') {
							$pm_k_remove[] = $k;
						}
					}
					foreach($pm_k_remove as $k_remove) {
						unset($value['payment_method'][$k_remove]);
					}
				}
				if(isset($value['payment_method']) && !empty($value['payment_method'])) {
					$r = ( isset($value["required"]) && $value["required"] );
					$keys[ $key ] = array( 'pm' => $value['payment_method'], 'r' => $r, 'rf' => $value["label"], 'type' => $type );
				}
				if(isset($value['shipping_method'])) {
					$pm_k_remove = array();
					foreach($value['shipping_method'] as $k => $v) {
						if($v === '0') {
							$pm_k_remove[] = $k;
						}
					}
					foreach($pm_k_remove as $k_remove) {
						unset($value['shipping_method'][$k_remove]);
					}
				}
				if(isset($value['shipping_method']) && !empty($value['shipping_method'])) {
					$r = ( isset($value["required"]) && $value["required"] );
					$keys[ $key ] = array( 'pm' => $value['shipping_method'], 'r' => $r, 'rf' => $value["label"], 'type' => $type );
				}
			}
		}

  		foreach($keys as $key => $value) {
        		$s_m= in_array($_POST['shipping_method'], (array)$value["pm"]) || in_array($_POST['shipping_method'][0], (array)$value["pm"]) || in_array( preg_replace('/\:(.*)$/', '', $_POST['shipping_method'][0]), (array)$value["pm"]);
			if( !( in_array($_POST['payment_method'], (array)$value["pm"]) || $s_m ) ) {
				unset( $data[$key] );
			}
  		}
		return $data;
	}
	public function after_checkout_validation( $data, $errors = array() ) {	
		if(! isset($this->fieldss) )
			$this->fieldss = get_option('woocommerce_saphali_filds_filters');
		$fieldss = $this->fieldss;
		$keys = array();
		if( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) && isset($_POST['ship_to_different_address']) || version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) && !isset($_POST['shiptobilling']) ) {
			$fieldss____ = array('billing', 'shipping');
		} else $fieldss____ = array('billing');
		foreach($fieldss____ as  $type) {
			foreach($fieldss[$type] as $key => $value) {
				if(isset($value['payment_method'])) {
					$pm_k_remove = array();
					foreach($value['payment_method'] as $k => $v) {
						if($v === '0') {
							$pm_k_remove[] = $k;
						}
					}
					foreach($pm_k_remove as $k_remove) {
						unset($value['payment_method'][$k_remove]);
					}
				}
				if(isset($value['payment_method']) && !empty($value['payment_method'])) {
					$r = ( isset($value["required"]) && $value["required"] );
					$keys[ $key ] = array( 'pm' => $value['payment_method'], 'r' => $r, 'rf' => $value["label"], 'type' => $type );
				}
				if(isset($value['shipping_method'])) {
					$pm_k_remove = array();
					foreach($value['shipping_method'] as $k => $v) {
						if($v === '0') {
							$pm_k_remove[] = $k;
						}
					}
					foreach($pm_k_remove as $k_remove) {
						unset($value['shipping_method'][$k_remove]);
					}
				}
				if(isset($value['shipping_method']) && !empty($value['shipping_method'])) {
					$r = ( isset($value["required"]) && $value["required"] );
					$keys[ $key ] = array( 'pm' => $value['shipping_method'], 'r' => $r, 'rf' => $value["label"], 'type' => $type );
				}
			}
		}
		$is_e = false;
		foreach($keys as $key => $value) {
			$s_m = in_array($_POST['shipping_method'], (array)$value["pm"]) || in_array($_POST['shipping_method'][0], (array)$value["pm"]) || in_array( preg_replace('/\:(.*)$/', '', $_POST['shipping_method'][0]), (array)$value["pm"]);
			if( !( in_array($_POST['payment_method'], (array)$value["pm"]) || $s_m ) ) {
				unset( $_POST[$key] );
			}
			if( $value["r"] ) {
				if(in_array($_POST['payment_method'], (array)$value["pm"]) ) {
					if( empty($_POST[$key])) {
						$is_e = $this->remove_no_valid_filds($key, $value, $errors);
						if( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) {
							if( !version_compare( WOOCOMMERCE_VERSION, '2.6.0', '<' ) ) 
							$this->comp_woocomerce_mess_error( sprintf( _x( '%s is a required field.', 'FIELDNAME is a required field.', 'woocommerce' ), '<strong>' . $value["rf"] . '</strong>' ) );
							else 
							$this->comp_woocomerce_mess_error( '<strong>' . $value["rf"] . '</strong> ' . __( 'is a required field.', 'woocommerce' ) );
						} else {
							switch ($value["type"]) {
								case 'shipping' :
									/* translators: %s: field name */
									$field_label = __(_x( 'Shipping %s', 'checkout-validation', 'woocommerce' ), 'woocommerce' );
								break;
								case 'billing' :
									/* translators: %s: field name */
									$field_label = __(_x( 'Billing %s', 'checkout-validation', 'woocommerce' ), 'woocommerce' );
								break;
							}
							if(version_compare( WOOCOMMERCE_VERSION, '5.1.0', '<' )) {
								$fl =  function_exists('mb_strtolower') ? mb_strtolower(  sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . __( $value["rf"], 'woocommerce' ) . '</strong>' ) ) :  sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . __( $value["rf"], 'woocommerce' ) . '</strong>' );
							} else {
								$field_label = sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . $field_label . '</strong>' );
								$fl = __( $value["rf"], 'woocommerce' );
							}
							$this->comp_woocomerce_mess_error( sprintf( $field_label, $fl ) );
						}
					}
				} else {
					if( empty($_POST[$key])) {
						$is_e = $this->remove_no_valid_filds($key, $value, $errors);
					}
				}
				
				if( $s_m ) {
					if( empty($_POST[$key])) {
						$is_e = $this->remove_no_valid_filds($key, $value, $errors);
						if( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) {
							if( !version_compare( WOOCOMMERCE_VERSION, '2.6.0', '<' ) ) 
							$this->comp_woocomerce_mess_error( sprintf( _x( '%s is a required field.', 'FIELDNAME is a required field.', 'woocommerce' ), '<strong>' . $value["rf"] . '</strong>' ) );
							else 
							$this->comp_woocomerce_mess_error( '<strong>' . $value["rf"] . '</strong> ' . __( 'is a required field.', 'woocommerce' ) );
						} else {
							switch ($value["type"]) {
								case 'shipping' :
									/* translators: %s: field name */
									$field_label = __(_x( 'Shipping %s', 'checkout-validation', 'woocommerce' ), 'woocommerce' );
								break;
								case 'billing' :
									/* translators: %s: field name */
									$field_label = __(_x( 'Billing %s', 'checkout-validation', 'woocommerce' ), 'woocommerce' );
								break;
							}
							if(version_compare( WOOCOMMERCE_VERSION, '5.1.0', '<' )) {
								$fl =  function_exists('mb_strtolower') ? mb_strtolower(  sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . __( $value["rf"], 'woocommerce' ) . '</strong>' ) ) :  sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . __( $value["rf"], 'woocommerce' ) . '</strong>' );
							} else {
								$field_label = sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . $field_label . '</strong>' );
								$fl = __( $value["rf"], 'woocommerce' );
							}

							$this->comp_woocomerce_mess_error( sprintf( $field_label, $fl ) );
						}
					}
				} else {
					if( empty($_POST[$key])) {
						$is_e = $this->remove_no_valid_filds($key, $value, $errors);
					}
				}
			}
		}
		if($is_e &&  !version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) {
			if(is_object($errors) && empty( $errors->errors["required-field"] ) )
				$errors->remove( 'required-field' );
		}
	}
	function comp_woocomerce_mess_error ($m) {
		if( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {
			global $woocommerce;
			$woocommerce->add_error( $m );
		} else {
			wc_add_notice( $m, 'error' );
		}
	}
	function wp_enqueue_scripts() {
		if(!function_exists('is_cart')) return;
		if( !(is_cart() || is_checkout()) ) return;
		
		if($this->get_checkout_type() === 'block') return;
		// Подключаем скрипты
		$asset_file = plugin_dir_path(__FILE__) . 'build/classic/frontend-classic.asset.php';
		if (file_exists($asset_file)) {
			$asset_info = include $asset_file;
			wp_register_script(
				'saphali-woocommerce-lite-frontend-classic',
				plugins_url('build/classic/frontend-classic.js', __FILE__),
				$asset_info['dependencies'] + ['jquery'],
				$asset_info['version'],
				true
			); 
			if(! isset($this->fieldss) )
			$this->fieldss = get_option('woocommerce_saphali_filds_filters');
			$fieldss = $this->fieldss;
			
			foreach(array('billing', 'shipping') as  $type) {
				if(isset($fieldss[$type]) && is_array($fieldss[$type])) {
					foreach($fieldss[$type] as $key => $value) {
						if(isset($value['payment_method'])) {
							$pm_k_remove = array();
							if(is_array($value['payment_method']))
							foreach($value['payment_method'] as $k => $v) {
								if($v === '0') {
									$pm_k_remove[] = $k;
								}
							}
							foreach($pm_k_remove as $k_remove) {
								unset($value['payment_method'][$k_remove]);
							}
						}
						if(isset($value['payment_method']) && !empty($value['payment_method'])) {
							$keys[ $key ] = $value['payment_method'];
						}
						if(isset($value['shipping_method'])) {
							$pm_k_remove = array();
							if(is_array($value['shipping_method']))
							foreach($value['shipping_method'] as $k => $v) {
								if($v === '0') {
									$pm_k_remove[] = $k;
								}
							}
							foreach($pm_k_remove as $k_remove) {
								unset($value['shipping_method'][$k_remove]);
							}
						}
						if(isset($value['shipping_method']) && !empty($value['shipping_method'])) {
							$skeys[ $key ] = $value['shipping_method'];
						}
					}
				}
			}
			wp_localize_script(
				'saphali-woocommerce-lite-frontend-classic', // saphaliWoocommerceLiteSettings.saphaliKeys`
				'saphaliWoocommerceLiteSettings', // saphaliWoocommerceLiteSettings.saphaliKeys
				array(
					'keys' => isset($keys) ? $keys : array(),
					'skeys' => isset($skeys) ? $skeys : array(),
				) // Массив с ключами
			);
			wp_enqueue_script('saphali-woocommerce-lite-frontend-classic');
		}
	}
	function formatted_billing_address($address, $order) {
		if( !version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) return $address;
		$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
		foreach ( array("billing") as $type )
		{
			if ( isset($billing_data[$type]) && is_array($billing_data[$type]))
			{
				foreach ( $billing_data[$type] as $key => $field ) {
					
					if (isset($field['public']) && $field['public'] ) {
						$id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
						
						$address[str_replace($type . '_', '', $key)] = get_post_meta( $id, '_' . $key, true );
						if(!empty($address[str_replace($type . '_', '', $key)]) && ( strpos($key, 'new_fild') !== false) && (isset($this->unuque) && !in_array($key, $this->unuque) )) {
							$this->unuque[] = $key;
							echo  '<label><strong>'. $field['label']. ':</strong></label> ' . $address[str_replace($type . '_', '', $key)].'<br />';	
						}
					}
				}
			}
		}
		return($address);
	}
	function formatted_shipping_address($address, $order) {
		if( !version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) return $address;
		$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
		if(isset($billing_data["order"]) && is_array($billing_data["order"])) {
			foreach ( $billing_data["order"] as $key => $field ) {
				if (isset($field['show']) && !$field['show'] || $key == 'order_comments') continue;
				$id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
				$address[ str_replace('order_', '', $key) ] = get_post_meta( $id, '_' . $key, true );
				if( !empty($address[ str_replace('order_', '', $key) ]) && ( strpos($key, 'new_fild') === false) && (isset($this->unuque) && !in_array($key, $this->unuque) ) ) {
					$this->unuque[] = $key;
					echo  '<label><strong>'. $field['label']. ':</strong></label> ' . $address[ str_replace('order_', '', $key) ] . '<br />';
				}
			}
		}
		foreach ( array( "shipping") as $type )
		{
			if ( isset($billing_data[$type]) && is_array($billing_data[$type]))
			{
				foreach ( $billing_data[$type] as $key => $field ) {
					
					if (isset($field['public']) && $field['public'] ) {
						$id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
					
						$address[str_replace($type . '_', '', $key)] = get_post_meta( $id, '_' . $key, true );
						$this->unuque[] = $key;
						if(!empty($address[str_replace($type . '_', '', $key)]) && ( strpos($key, 'new_fild') === false) && (isset($this->unuque) && !in_array($key, $this->unuque) ))
						echo  '<label><strong>'. $field['label']. ':</strong></label> ' . $address[str_replace($type . '_', '', $key)].'<br />';	
						
					}
				}
			}
		}
		return($address);
	}
	function woocommerce_admin_billing_fields($billing_fields) {
		if ( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) {
			$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
			if(isset($billing_data["billing"]) && is_array($billing_data["billing"])) {
				foreach ( $billing_data["billing"] as $key => $field ) {
					$key = str_replace('billing_', '', $key);
					if (isset($field['show']) && !$field['show'] || $key == 'order_comments') continue;
					if( strpos($key, 'new_fild') === false)
					$billing_fields[$key] = array(
						'label' =>  $field['label'],
						'show'	=> false
					);
					else
					$billing_fields[$key] = array(
						'label' =>  $field['label'],
						'show'	=> true
					);
				}
			}
		}
		return $billing_fields;
	}
	function woocommerce_admin_shipping_fields($shipping_fields) {
		if ( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) {
			$shipping_data = $this->woocommerce_get_customer_meta_fields_saphali();
			if(isset($shipping_data["shipping"]) && is_array($shipping_data["shipping"])) {
				foreach ( $shipping_data["shipping"] as $key => $field ) {
					$key = str_replace('shipping_', '', $key);
					if (isset($field['show']) && !$field['show'] || $key == 'order_comments') continue;
					if( strpos($key, 'new_fild') === false)
					 $shipping_fields[$key] = array(
						'label' =>  $field['label'],
						'show'	=> false
					);
					else
					 $shipping_fields[$key] = array(
						'label' =>  $field['label'],
						'show'	=> true
					);
				}
			}
		}
		return $shipping_fields;
	}
	
	public function wp( ) {
		if(function_exists('wc_edit_address_i18n')){
			global $wp;
			if(isset($wp->query_vars['edit-address']))
			add_filter( 'woocommerce_'.wc_edit_address_i18n( sanitize_key( $wp->query_vars['edit-address'] ), true ) .'_fields',  array($this,'saphali_custom_edit_address_fields'), 10, 1 );
		}
	}
	public function checkout_create_order( $order, $data ) {
		$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
		
		if(is_array($billing_data["order"])) {
			foreach ( $billing_data["order"] as $key => $field ) {
				if (isset($field['show']) && !$field['show'] || $key == 'order_comments') continue;
				if(isset($_POST[$key])){
					$custom_field = sanitize_text_field( $_POST[$key] );
					$order->update_meta_data( '_' . $key, $custom_field );
				} elseif ( isset( $data['order'][$key] ) ) {
					$custom_field = sanitize_text_field( $data['order'][$key] );
					$order->update_meta_data( '_' . $key, $custom_field );
				}
			}
		}
		foreach ( array("billing", "shipping") as $type )
		{
			if ( isset($billing_data[$type]) && is_array($billing_data[$type]))
			{
				foreach ( $billing_data[$type] as $key => $field ) {
					
					if (isset($field['public']) && $field['public']) {
						if(isset($_POST[$key])){
							$custom_field = sanitize_text_field( $_POST[$key] );
							$order->update_meta_data( '_' . $key, $custom_field );
						} elseif ( isset( $data[$type][$key] ) ) {
							$custom_field = sanitize_text_field( $data[$type][$key] );
							$order->update_meta_data( '_' . $key, $custom_field );
						}
					}
				}
			}
		}
	}
	public function checkout_update_order_meta( $order_id, $posted ) {
		if ( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) &&  version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) {
			$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
			if(is_array($billing_data["order"])) {
				foreach ( $billing_data["order"] as $key => $field ) {
					if (isset($field['show']) && !$field['show'] || $key == 'order_comments') continue;
					if(!empty($_POST[$key]))
						update_post_meta( $order_id, '_' . $key, $_POST[$key] );
				}
			}
			foreach ( array("billing", "shipping") as $type )
			{
				if ( isset($billing_data[$type]) && is_array($billing_data[$type]))
				{
					foreach ( $billing_data[$type] as $key => $field ) {
						if (isset($field['public']) && $field['public'] && !empty($_POST[$key])) {
							update_post_meta( $order_id, '_' . $key, $_POST[$key] );
						}
					}
				}
			}
		}
	}
	public function woocommerce_admin_order_totals_after_shipping($id) {
		if( apply_filters( 'woocommerce_currency', get_option('woocommerce_currency') ) == 'RUB' ) {
		?>
	<script type="text/javascript">
	jQuery( function($){
		$('#woocommerce-order-totals').on( 'change', '#_order_tax, #_order_shipping_tax, #_cart_discount, #_order_discount', function() {

			var $this =  $(this);
			var fields = $this.closest('.totals').find('input');
			var total = 0;

			fields.each(function(){
				if ( $(this).val() )
					total = total + parseFloat( $(this).val() );
			});

			var formatted_total = accounting.formatMoney( total, {
				symbol 		: woocommerce_writepanel_params.currency_format_symbol,
				decimal 	: woocommerce_writepanel_params.currency_format_decimal_sep,
				thousand	: woocommerce_writepanel_params.currency_format_thousand_sep,
				precision 	: woocommerce_writepanel_params.currency_format_num_decimals,
				format		: woocommerce_writepanel_params.currency_format
			} );
			$this.closest('.totals_group').find('span.inline_total').html( formatted_total );
			
		} );
		setTimeout(function() {$('span.inline_total').closest('.totals_group').find('input').change();}, 100);
	});
	</script>
		<?php
		}
	}
	public function woocommerce_init() {
		// HPOS compatibility declaration.
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( __FILE__ ), true );
		}
	}
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce',  false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		// load_textdomain('saphali-woocommerce-lite', WP_PLUGIN_DIR . '/saphali-woocommerce-lite/languages/saphali-woocommerce-lite-'.get_locale().'.mo');
		load_plugin_textdomain( 'saphali-woocommerce-lite',  false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	public function load_plugin_textdomain_th() {
		load_plugin_textdomain( 'saphali-woocommerce-lite',  false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	public function woocommerce_default_address_fields($locale, $context = '') {
		if($this->get_checkout_type() === 'block') {
			
			// return $this->custom_validate_customer_address($locale, $context); 
		}
		$fieldss = get_option('woocommerce_saphali_filds_locate');
		if(is_array($fieldss)) {
			foreach($fieldss as $_k => $_v) {
				$_fieldss[$_k] = $_v;
				if(isset($_v['label'])) {
					$_fieldss[$_k]['label'] = __( $_v['label'], 'woocommerce');
				}
				if(isset($_v['order'])) {
					$_fieldss[$_k]["priority"] = $_v['order']+10;
					$_fieldss[$_k]["order"] = $_v['order']+10;
				}
				if(isset($_v['placeholder'])) {
					$_fieldss[$_k]['placeholder'] = __( $_v['placeholder'], 'woocommerce');
				}
			}
			$locale = $_fieldss;			
		}

		return $locale;
	}
	public function woocommerce_get_country_locale($locale) {
		
		return $locale;	
	}
	public function generator() {
		echo "\n\n" . '<!-- Saphali Lite Version -->' . "\n" . '<meta name="generator" content="Saphali Lite ' . esc_attr( SAPHALI_LITE_VERSION ) . '" />' . "\n\n";
	}
	function woocommerce_customer_meta_fields_action() {
		add_action( 'show_user_profile', array($this,'woocommerce_customer_meta_fields_s') );
		add_action( 'edit_user_profile', array($this,'woocommerce_customer_meta_fields_s') );
	}
	function woocommerce_customer_meta_fields_s( $user ) {
		if ( ! current_user_can( 'manage_woocommerce' ) )
			return;

		$show_fields = $this->woocommerce_get_customer_meta_fields_saphali();
		if(!empty($show_fields["billing"])) {
			 $show_field["billing"]['title'] = __('Customer Billing Address', 'woocommerce');
			 $show_field["billing"]['fields'] = $show_fields["billing"];
		}
		if(!empty($show_fields["shipping"])) {
			 $show_field["shipping"]['title'] = __('Customer Shipping Address', 'woocommerce');
			 $show_field["shipping"]['fields'] = $show_fields["shipping"];
		}
		if(is_array($show_field)) {
		$count = 0; echo '<fieldset>';
		foreach( $show_field as $fieldset ) :
		if(!$count) echo '<h2>Дополнительные поля</h2>'; 
		$count++;
			?>
			<h3><?php echo $fieldset['title']; ?></h3>
			<table class="form-table">
				<?php
				foreach( $fieldset['fields'] as $key => $field ) :
					?>
					<tr>
						<th><label for="<?php echo $key; ?>"><?php echo $field['label']; ?></label></th>
						<td>
							<input type="text" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>" class="regular-text" /><br/>
							<span class="description"><?php echo isset($field['description']) ? $field['description'] : ''; ?></span>
						</td>
					</tr>
					<?php
				endforeach;
				?>
			</table>
			<?php
		endforeach; 
		echo '</fieldset>';
		}
	}
	function woocommerce_saphali_admin_menu_s_l() {
		add_submenu_page('woocommerce',  __('Настройки Saphali WC Lite', 'woocommerce'), __('Saphali WC Lite', 'woocommerce') , 'manage_woocommerce', 'woocommerce_saphali_s_l', array($this,'woocommerce_saphali_page_s_l'));
	}
	function add_inr_currency( $currencies ) {
		$currencies['UAH'] = __( 'Ukrainian hryvnia', 'saphali-woocommerce-lite' );
		$currencies['RUR'] = __( 'Russian ruble', 'saphali-woocommerce-lite' );
		if( version_compare( WOOCOMMERCE_VERSION, '2.5.2', '<' ) || SAPHALI_LITE_SYMBOL )
		$currencies['RUB'] = __( 'Russian ruble', 'saphali-woocommerce-lite' );
		$currencies['BYN'] = sprintf(__( 'Belarusian ruble%s', 'saphali-woocommerce-lite' ), __(' (new)', 'saphali-woocommerce-lite'));
		$currencies['BYR'] = sprintf(__( 'Belarusian ruble%s', 'saphali-woocommerce-lite' ), '');
		$currencies['AMD'] = __( 'Armenian dram  (Դրամ)', 'saphali-woocommerce-lite' );
		$currencies['KGS'] = __( 'Киргизский сом', 'saphali-woocommerce-lite' );
		$currencies['KZT'] = __( 'Казахстанский тенге ', 'saphali-woocommerce-lite' );
		$currencies['UZS'] = __( 'Узбекский сум', 'saphali-woocommerce-lite' );
		$currencies['LTL'] = __( 'Lithuanian Litas', 'saphali-woocommerce-lite' );
		return $currencies;
	}
	function add_inr_currency_symbol( $symbol , $currency ) {
		if(empty($currency))
		$currency = get_option( 'woocommerce_currency' );
		if(isset($currency)) {
			if( version_compare( WOOCOMMERCE_VERSION, '2.5.2', '<' ) || SAPHALI_LITE_SYMBOL )
			switch( $currency ) {
				case 'UAH': $symbol = '&#x433;&#x440;&#x43D;.'; break;
				// case 'RUB': if( is_admin() ) $symbol = '&#x440;&#x443;&#x431;.'; else $symbol = '<span class=rur >&#x440;<span>&#x443;&#x431;.</span></span>'; break;
				case 'RUR': $symbol = '&#x440;&#x443;&#x431;.'; break;
				case 'BYN': $symbol = '&#x440;&#x443;&#x431;.'; break;
				case 'BYR': $symbol = '&#x440;&#x443;&#x431;.'; break;
				case 'AMD': $symbol = '&#x534;'; break;
				case 'KGS': $symbol = 'сом'; break;
				case 'KZT': $symbol = '&#x20B8;'; break;
				case 'UZS': $symbol = '&#x441;&#x45E;&#x43C;'; break;
				case 'LTL': $symbol = 'lt.'; break;
			}
			else 
			switch( $currency ) {
				case 'UAH': $symbol = '&#x433;&#x440;&#x43D;.'; break;
				case 'RUR': $symbol = '&#x440;&#x443;&#x431;.'; break;
				case 'BYN': $symbol = '&#x440;&#x443;&#x431;.'; break;
				case 'BYR': $symbol = '&#x440;&#x443;&#x431;.'; break;
				case 'AMD': $symbol = '&#x534;'; break;
				case 'KGS': $symbol = 'сом'; break;
				case 'KZT': $symbol = '&#x20B8;'; break;
				case 'UZS': $symbol = '&#x441;&#x45E;&#x43C;'; break;
				case 'LTL': $symbol = 'lt.'; break;
			}
		}
		return $symbol;
	}
	function admin_enqueue_scripts_page_saphali() {
		global $woocommerce;
		$plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		if( isset($_GET['page']) && $_GET['page'] == 'woocommerce_saphali_s_l' ) {
			wp_enqueue_script( 'tablednd', $plugin_url. '/js/jquery.tablednd.0.5.js', array('jquery'), $woocommerce->version );
			$get_keys = $this->get_keys_method_checkout();
			$scr = $this->get_checkout_type() === 'block' ? SAPHALI_PLUGIN_DIR_URL . 'js/saphali-admin-block.js' : SAPHALI_PLUGIN_DIR_URL . 'js/saphali-admin.js';
			wp_register_script(
				'saphali-admin-settings-page',
				$scr,
				array('tablednd'),
				SAPHALI_LITE_VERSION,
				true
			);

			wp_localize_script(
                'saphali-admin-settings-page',
                'saphaliSettings', // saphaliSettings.add2
                array(
                    'delete' => __('Удалить', 'saphali-woocommerce-lite'),
                    'add' => __('Добавить', 'saphali-woocommerce-lite'),
                    'add2' => __('Добавить еще', 'saphali-woocommerce-lite'),
                    'all' => __('Все', 'saphali-woocommerce-lite'),
					'saphaliKeys' => $get_keys['keys'],
					'saphaliSkeys' => $get_keys['skeys'],
                ) // Массив с ключами
            );
			wp_enqueue_script( 'saphali-admin-settings-page' );

            wp_enqueue_style('saphali-admin-style', $plugin_url. '/css/saphali-admin.css', array(), '1.0');
		}
	}
	function get_keys_method_checkout() {
        global $woocommerce;
		if(isset($this->keys)) return $this->keys;
        $keys = $skeys = array();
        foreach ($woocommerce->payment_gateways->payment_gateways() as $gateway) {
            // if ($gateway->enabled != 'yes') continue;
            $keys[$gateway->id] = $gateway->title;
        }
        foreach ($woocommerce->shipping->get_shipping_methods() as $act_id => $shipping) {
            // if ($shipping->enabled == 'no') continue;
            $skeys[$act_id] = $shipping->title ? $shipping->title : $shipping->method_title;
        }
        $this->keys = array(
            'keys' => $keys,
            'skeys' => $skeys,
        );
		return $this->keys;
    }
	function woocommerce_saphali_page_s_l () {
		if($this->get_checkout_type() === 'block')
			require_once (SAPHALI_PLUGIN_DIR_PATH . 'admin/admin-page-block.php');
		else
			require_once (SAPHALI_PLUGIN_DIR_PATH . 'admin/admin-page.php');
	}
	public function get_checkout_type() {
		// CartCheckoutUtils::is_checkout_block_default()

		if(isset($this->checkout_type)) return $this->checkout_type;
        // Получаем ID страницы чекаута
		if(class_exists('CartCheckoutUtils')) {
			if(CartCheckoutUtils::is_checkout_block_default()) {
				$this->checkout_type = 'block';
				return $this->checkout_type;
			}
		}
        $checkout_page_id = wc_get_page_id( 'checkout' );

        if ( ! $checkout_page_id || $checkout_page_id <= 0 ) {
			$this->checkout_type = 'unknown';
            return $this->checkout_type;
        }

        // Получаем содержимое страницы чекаута
        $checkout_page_content = get_post_field( 'post_content', $checkout_page_id );

        if ( ! $checkout_page_content ) {
			$this->checkout_type = 'unknown';
			return $this->checkout_type;
        }

        // Проверяем на наличие шорткода [woocommerce_checkout]
        if ( has_shortcode( $checkout_page_content, 'woocommerce_checkout' ) ) {
			$this->checkout_type = 'classic';
			return $this->checkout_type;
        }

        // Проверяем, есть ли блоки WooCommerce
        if ( function_exists( 'has_blocks' ) && has_blocks( $checkout_page_id ) ) {
            $blocks = parse_blocks( $checkout_page_content );
			
			if ( $this->find_checkout_block( $blocks ) ) {
				$this->checkout_type = 'block';
				return $this->checkout_type;
			}
        }
		$this->checkout_type = 'unknown';
        return $this->checkout_type;
    }
	function find_checkout_block($blocks) {
		if ( ! is_array( $blocks ) ) {
			return false;
		}
	
		foreach ( $blocks as $block ) {
			// Проверяем, существует ли ключ 'blockName' и содержит ли он нужную подстроку.
			if ( isset( $block['blockName'] ) && strpos( $block['blockName'], 'woocommerce/checkout' ) !== false ) {
				return true;
			}
			// Если в блоке есть вложенные блоки, рекурсивно ищем среди них.
			if ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) ) {
				if ( $this->find_checkout_block( $block['innerBlocks'] ) ) {
					return true;
				}
			}
		}
		return false;
	}
	function woocommerce_get_customer_meta_fields_saph_ed() {
		$show_fields = apply_filters('woocommerce_customer_meta_fields', array(
			'billing' => array(
				'title' => __('Customer Billing Address', 'woocommerce'),
				'fields' => array(
					'billing_first_name' => array(
							'label' => __('First name', 'woocommerce'),
							'description' => ''
						),
					'billing_last_name' => array(
							'label' => __('Last name', 'woocommerce'),
							'description' => ''
						),
					'billing_company' => array(
							'label' => __('Company', 'woocommerce'),
							'description' => ''
						),
					'billing_address_1' => array(
							'label' => __('Address 1', 'woocommerce'),
							'description' => ''
						),
					'billing_address_2' => array(
							'label' => __('Address 2', 'woocommerce'),
							'description' => ''
						),
					'billing_city' => array(
							'label' => __('Town / City', 'woocommerce'),
							'description' => ''
						),
					'billing_postcode' => array(
							'label' => __('Postcode / ZIP', 'woocommerce'),
							'description' => ''
						),
					'billing_state' => array(
							'label' => __('State / County', 'woocommerce'),
							'description' => __('Country or state code', 'woocommerce'),
						),
					'billing_country' => array(
							'label' => __('Country', 'woocommerce'),
							'description' => __('2 letter Country code', 'woocommerce'),
						),
					'billing_phone' => array(
							'label' => __('Telephone', 'woocommerce'),
							'description' => ''
						),
					'billing_email' => array(
							'label' => __('Email', 'woocommerce'),
							'description' => ''
						)
				)
			),
			'shipping' => array(
				'title' => __('Customer Shipping Address', 'woocommerce'),
				'fields' => array(
					'shipping_first_name' => array(
							'label' => __('First name', 'woocommerce'),
							'description' => ''
						),
					'shipping_last_name' => array(
							'label' => __('Last name', 'woocommerce'),
							'description' => ''
						),
					'shipping_company' => array(
							'label' => __('Company', 'woocommerce'),
							'description' => ''
						),
					'shipping_address_1' => array(
							'label' => __('Address 1', 'woocommerce'),
							'description' => ''
						),
					'shipping_address_2' => array(
							'label' => __('Address 2', 'woocommerce'),
							'description' => ''
						),
					'shipping_city' => array(
							'label' => __('City', 'woocommerce'),
							'description' => ''
						),
					'shipping_postcode' => array(
							'label' => __('Postcode', 'woocommerce'),
							'description' => ''
						),
					'shipping_state' => array(
							'label' => __('State/County', 'woocommerce'),
							'description' => __('State/County or state code', 'woocommerce')
						),
					'shipping_country' => array(
							'label' => __('Country', 'woocommerce'),
							'description' => __('2 letter Country code', 'woocommerce')
						)
				)
			)
		));
		return $show_fields;
	}
	function woocommerce_get_customer_meta_fields_saphali() {
		if(! isset($this->fieldss) )
			$this->fieldss = get_option('woocommerce_saphali_filds_filters');
		$fieldss = $this->fieldss;
		$show_fields = $this->woocommerce_get_customer_meta_fields_saph_ed();

		

		if(is_array($fieldss)) {
			if(is_array($fieldss["billing"])) {
				$billing = array();
				foreach($fieldss["billing"] as $key => $value) {
					if(isset($show_fields["billing"]['fields'][$key])) continue;
					
					foreach($value as $k_post=> $v_post){
									if( 'on' == $v_post  ) {
										$value[$k_post] = true;
									} elseif(in_array($k_post, array('public','clear','required'))) {  $value[$k_post] = false; }
					}
					$billing = array_merge( $billing , array ($key => $value));
				}
			}
			if(is_array($fieldss["shipping"])) {
				$shipping = array();
				foreach($fieldss["shipping"] as $key => $value) {
					if(isset($show_fields["shipping"]['fields'][$key])) continue;
					foreach($value as $k_post=> $v_post){
						if( 'on' == $v_post  ) {
							$value[$k_post] = true;
						} elseif(in_array($k_post, array('public','clear','required'))) {  $value[$k_post] = false; }
					}
					$shipping = array_merge( $shipping , array ($key => $value));
				}
			}
			if(is_array($fieldss["order"])) {
				$orders = array();
				foreach($fieldss["order"] as $key => $value) {
					if(isset($show_fields["order"]['fields'][$key])) continue;
					foreach($value as $k_post=> $v_post){
						if( 'on' == $v_post  ) {
							$value[$k_post] = true;
						} elseif(in_array($k_post, array('public','clear','required'))) {  $value[$k_post] = false; }
					}
					$orders = array_merge( $orders , array ($key => $value));
				}
			}
		}

		if(!isset($show_fields['billing']['title'])) {
			$_show_fields['billing']['title'] = $show_fields['billing']['title'];
		}
			
		  if(isset($billing))
		  $_show_fields['billing'] =   $billing;
		  
		if(!isset($show_fields['shipping']['title'])) {
			$_show_fields['shipping']['title'] = $show_fields['shipping']['title'];
		}
			
		  if(isset($shipping))
		  $_show_fields['shipping'] =   $shipping;
		

		if(isset($show_fields['order']) && !(@is_array($show_fields['order']['fields']))) {
			$_show_fields['order']['title'] = 'Дополнительные поля'; 
		}
		if(isset($orders))
		 $_show_fields['order'] =   $orders;
		if (isset($_show_fields)) {
		return $_show_fields;
	}
		
	}
	function woocommerce_save_customer_meta_fields_saphali( $user_id ) {
		if ( ! current_user_can( 'manage_woocommerce' ) )
			return $columns;

		$show_fields = $this->woocommerce_get_customer_meta_fields_saphali();
		if(!empty($show_fields["billing"])) {
			 $save_fields["billing"]['title'] = __('Customer Billing Address', 'woocommerce');
			 $save_fields["billing"]['fields'] = $show_fields["billing"];
		}
		if(!empty($show_fields["shipping"])) {
			 $save_fields["shipping"]['title'] = __('Customer Shipping Address', 'woocommerce');
			 $save_fields["shipping"]['fields'] = $show_fields["shipping"];
		}
		/* if(!empty($show_fields["order"])) {
			 $save_fields["order"]['title'] = __('Дополнительные поля', 'woocommerce');
			 $save_fields["order"]['fields'] = $show_fields["order"];
		} */
		if(isset($save_fields) && is_array($save_fields))
		foreach( $save_fields as $fieldset )
			foreach( $fieldset['fields'] as $key => $field )
				if ( isset( $_POST[ $key ] ) )
					update_user_meta( $user_id, $key, trim( esc_attr( $_POST[ $key ] ) ) );
	}
	function woocommerce_admin_order_data_after_billing_address_s($order) {
		$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
		
		echo '<div class="address">';
		if(is_array($billing_data["billing"])) {
		foreach ( $billing_data["billing"] as $key => $field ) : if (isset($field['show']) && !$field['show']) continue;

			 $field_name = '_'.$key;
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
			$value_fild = @$order->order_custom_fields[$field_name][0];
			elseif ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
			$value_fild = $order->__get( $key );
			else $value_fild = $order->get_meta( '_' . $key );
			if( $value_fild && isset($field["type"]) && in_array($field["type"] , array('select', 'radio')) && isset($field["options"][$value_fild]) ) {
				$value_fild = $field["options"][$value_fild];
			}
			if ( $value_fild && !empty($field['label']) ) echo '<p><strong>'.$field['label'].':</strong> '.$value_fild.'</p>';
			
			endforeach;
		}
		echo '</div>';
	}
	function woocommerce_admin_order_data_after_shipping_address_s($order) {
		$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
		echo '<div class="address">';
		if(is_array($billing_data["shipping"])) {
		foreach ( $billing_data["shipping"] as $key => $field ) : if (isset($field['show']) && !$field['show']) continue;

			 $field_name = '_'.$key;

			if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
			$value_fild = @$order->order_custom_fields[$field_name][0];
			elseif ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
			$value_fild = $order->__get( $key );
			else $value_fild = $order->get_meta( '_' . $key );
			if ( $value_fild && !empty($field['label']) ) echo '<p><strong>'.$field['label'].':</strong> '.$value_fild.'</p>';
			
			endforeach;
		}
		echo '</div>';
	}
	function woocommerce_admin_order_data_after_order_details_s($order) {
		$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
		echo '<div class="address">';
		if(isset($billing_data["order"]) && is_array($billing_data["order"])) {
		foreach ( $billing_data["order"] as $key => $field ) : if (isset($field['show']) && !$field['show']) continue;

			 $field_name = '_'.$key;
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
			$value_fild = @$order->order_custom_fields[$field_name][0];
			elseif ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
			$value_fild = $order->__get( $key );
			else $value_fild = $order->get_meta( '_' . $key );
			if ( $value_fild && !empty($field['label']) ) 

			echo '<div class="form-field form-field-wide"><label>'. $field['label']. ':</label> ' . $value_fild.'</div>';
			
			endforeach;
		}
		echo '</div>';
		
	}
	function saphali_custom_override_checkout_fields( $fields ) {
		
		if(! isset($this->fieldss) )
			$this->fieldss = get_option('woocommerce_saphali_filds_filters');
		$fieldss = $this->fieldss;
		
		if(isset($fieldss["billing"]["billing_city"]) && isset($fields["billing"]["billing_city"]["type"]) && $fields["billing"]["billing_city"]["type"] == 'select') {
			if(isset($fieldss["billing"]["billing_city"]["order"]))
			$fields["billing"]["billing_city"]["order"] =  $fieldss["billing"]["billing_city"]["order"];
			$fieldss["billing"]["billing_city"] = $fields["billing"]["billing_city"];
		}
		if(is_array($fieldss)) {
			$fields["billing"] = $fieldss["billing"];
			$fields["shipping"] = $fieldss["shipping"];
			$fields["order"] = $fieldss["order"];
		}
		foreach(array("billing", "shipping", "order") as $v)
		foreach($fields[$v] as $key => $value) {
			if(isset($fields[$v][$key]["order"]))
			$fields[$v][$key]["priority"] = $value["order"]+10;
			if(isset($fields[$v][$key]["label"]))
			$fields[$v][$key]["label"] = __($value["label"], 'woocommerce');
			if(isset($fields[$v][$key]["placeholder"]))
			$fields[$v][$key]["placeholder"] = __( __($value["placeholder"], 'saphali-woocommerce-lite'), 'woocommerce');
		}
		 return $fields;
	}
	function saphali_custom_edit_address_fields( $fields ) {
		global $wp;
		if(! isset($this->fieldss) )
			$this->fieldss = get_option('woocommerce_saphali_filds_filters');
		$fieldss = $this->fieldss;
		if(isset($fieldss["billing"]["billing_city"]) && isset($fields["billing"]["billing_city"]["type"]) && $fields["billing"]["billing_city"]["type"] == 'select') {
			if(isset($fieldss["billing"]["billing_city"]["order"]))
			$fields["billing"]["billing_city"]["order"] =  $fieldss["billing"]["billing_city"]["order"];
			$fieldss["billing"]["billing_city"] = $fields["billing"]["billing_city"];
		}
		$__fields = $_a_ = array();
		if(is_array($fieldss))
 		$_fields = $fieldss["billing"];
		if( isset($_fields) && is_array($_fields) )
		foreach($_fields as $key => $value) {
			if(str_replace( 'billing_','', $key ) != 'email')
			$__fields[wc_edit_address_i18n( sanitize_key( $wp->query_vars['edit-address'] ), true ) . '_' . str_replace( 'billing_','', $key ) ] = $value;
		}
		if(isset($fields["billing"]))
			foreach($__fields as $k => $_v) {
				
				if(isset($fields["billing"][$k]))
				$_a_[$k] = array_diff($__fields[$k], $fields["billing"][$k]);
				elseif(isset($fields[$k]))
				$_a_[$k] = array_diff($__fields[$k], $fields[$k]);
			}
		
		if(is_array($_a_) && is_array($fields) ) $fields = (array)$fields + (array)$_a_;
		$v = 'billing';
		foreach($fields as $key => $value) {
			if(isset($fields[$v][$key]["order"]))
			$fields[$v][$key]["priority"] = $value["order"]+10;
			if(isset($fields[$v][$key]["label"]))
			$fields[$v][$key]["label"] = __($value["label"], 'woocommerce');
			if(isset($fields[$v][$key]["placeholder"]))
			$fields[$v][$key]["placeholder"] = __($value["placeholder"], 'woocommerce');

			if(isset($fields[$key]["order"]))
			$fields[$key]["priority"] = $value["order"]+10;
			if(isset($fields[$key]["label"]))
			$fields[$key]["label"] = __($value["label"], 'woocommerce');
			if(isset($fields[$key]["placeholder"]))
			$fields[$key]["placeholder"] = __($value["placeholder"], 'woocommerce');
		}
		return $fields;
	}
	function saphali_custom_billing_fields( $fields ) {
		
		if(! isset($this->fieldss) )
			$this->fieldss = get_option('woocommerce_saphali_filds_filters');
		$fieldss = $this->fieldss;
		if(isset($fieldss["billing"]["billing_city"]) && isset($fields["billing_city"]["type"]) && $fields["billing_city"]["type"] == 'select') {
			if(isset($fieldss["billing"]["billing_city"]["order"]))
			$fields["billing_city"]["order"] =  $fieldss["billing"]["billing_city"]["order"];
			$fieldss["billing"]["billing_city"] = $fields["billing_city"];
		}
	
		if(is_array($fieldss))
 		$fields = $fieldss["billing"];
		$v = 'billing';
		foreach($fields as $key => $value) {
			if(isset($fields[$v][$key]["order"]))
			$fields[$v][$key]["priority"] = $value["order"]+10;
			elseif(isset($fields[$key]["order"]))
				$fields[$key]["priority"] = $value["order"]+10;
			if(isset($fields[$key]["label"]))
			$fields[$key]["label"] = __($value["label"], 'woocommerce');
			if(isset($fields[$key]["placeholder"]))
			$fields[$key]["placeholder"] = __($value["placeholder"], 'woocommerce');
			
		}
		// var_dump($fields["billing_city"]);
		return $fields;
	}
	function saphali_custom_shipping_fields( $fields ) {
		if(! isset($this->fieldss) )
			$this->fieldss = get_option('woocommerce_saphali_filds_filters');
		$fieldss = $this->fieldss;
		if(is_array($fieldss))
		$fields = $fieldss["shipping"];
		$v = 'shipping';
		foreach($fields as $key => $value) {
			if(isset($fields[$v][$key]["order"]))
			$fields[$v][$key]["priority"] = $value["order"]+10;
			elseif(isset($fields[$key]["order"]))
				$fields[$key]["priority"] = $value["order"]+10;
			if(isset($fields[$key]["label"]))
			$fields[$key]["label"] = __($value["label"], 'woocommerce');
			if(isset($fields[$key]["placeholder"]))
			$fields[$key]["placeholder"] = __($value["placeholder"], 'woocommerce');
		}
		return $fields;
	}
	public function store_order_id( $arg ) {
		if ( is_int( $arg ) ) $this->email_order_id = $arg;
		elseif ( is_array( $arg ) && array_key_exists( 'order_id', $arg ) ) $this->email_order_id = $arg['order_id'];
	}
	public function email_pickup_location( $template_name, $template_path, $located, $args = array() ) {
		global $_shipping_data, $_billing_data;
		if($template_name == 'emails/email-addresses.php' && isset($args["order"]) && is_object($args["order"]) ) {
			$id = method_exists($args["order"], 'get_id') ? $args["order"]->get_id() : $args["order"]->id;
			$this->email_order_id  = $id;
		}
		
		if ( $template_name == 'emails/email-addresses.php' && $this->email_order_id ) {

			$order = new WC_Order( $this->email_order_id );

			$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
			echo '<div class="address">';

			if(is_array($billing_data["billing"]) && !$_billing_data) {
				foreach ( $billing_data["billing"] as $key => $field ) : if (isset($field['show']) && !$field['show']) continue;
					$field_name = '_'.$key;
					if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
					$value_fild = @$order->order_custom_fields[$field_name][0];
					elseif ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
					$value_fild = $order->__get( $key );
					else $value_fild = $order->get_meta( '_' . $key );
					if( $value_fild && isset($field["type"]) && in_array($field["type"] , array('select', 'radio')) && isset($field["options"][$value_fild]) ) {
						$value_fild = $field["options"][$value_fild];
					}
					if ( $value_fild && !empty($field['label']) ) 
					echo '<div class="form-field form-field-wide"><label><strong>'. $field['label']. ':</strong></label> ' . $value_fild.'</div>';
				endforeach;
			}
			if(is_array($billing_data["shipping"]) && !$_shipping_data) {
				foreach ( $billing_data["shipping"] as $key => $field ) : if (isset($field['show']) && !$field['show']) continue;
					$field_name = '_'.$key;
					if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
					$value_fild = @$order->order_custom_fields[$field_name][0];
					elseif ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
					$value_fild = $order->__get( $key );
					else $value_fild = $order->get_meta( '_' . $key );
					if ( $value_fild  && !empty($field['label'])) 
					echo '<div class="form-field form-field-wide"><label><strong>'. $field['label']. ':</strong></label> ' . $value_fild.'</div>';
				endforeach;
			}
			if(is_array($billing_data["order"])) {
			foreach ( $billing_data["order"] as $key => $field ) : if (isset($field['show']) && !$field['show']) continue;

				 $field_name = '_'.$key;
				if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
					$value_fild = @$order->order_custom_fields[$field_name][0];
				elseif ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
					$value_fild = $order->__get( $key );
				else 
					$value_fild = $order->get_meta( '_' . $key );
				if ( $value_fild && !empty($field['label']) ) 

				echo '<div class="form-field form-field-wide"><label><strong>'. $field['label']. ':</strong></label> ' . $value_fild.'</div>';
				
			endforeach;
			}
			echo '</div>';
		}
	}
	/* function formatted_billing_address($address, $order) {
		global $billing_data, $_billing_data;
		if( empty($billing_data) )
			$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
		if(is_array($billing_data["billing"])) {
			$_billing_data = true;
			$no_fild = array ('_billing_booking_delivery_t', '_billing_booking_delivery');
			foreach ( $billing_data["billing"] as $key => $field ) : if (isset($field['show']) && !$field['show'] ) continue;
				
				$field_name = '_'.$key;
				
				if(in_array($field_name, $no_fild)) continue;
				if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
					$value_fild = @$order->order_custom_fields[$field_name][0];
				else
					$value_fild = $order->__get( $key );
				if ( $value_fild  && !empty($field['label'])) 
				echo  '<label><strong>'. $field['label']. ':</strong></label> ' . $value_fild.'<br />';
			endforeach;
		}
		return $address;
	} 
	function formatted_shipping_address($address, $order) {
	global $billing_data, $_shipping_data;
	if( empty($billing_data) )
		$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();
		if(is_array($billing_data["shipping"])) {
			$_shipping_data = true;
			foreach ( $billing_data["shipping"] as $key => $field ) : if (isset($field['show']) && !$field['show']) continue;
				$field_name = '_'.$key;
				if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
					$value_fild = @$order->order_custom_fields[$field_name][0];
				else
					$value_fild = $order->__get( $key );
				if ( $value_fild  && !empty($field['label'])) {
					echo  '<label><strong>'. $field['label']. ':</strong></label> ' . $value_fild.'<br />';
					$address[$key] = $value_fild;
				}
			endforeach;
		}
		return $address;
	}*/
	function order_pickup_location($order_id) {
		global $_billing_data, $_shipping_data;
		$order = new WC_Order( $order_id );
		
		if ( is_object($order) ) {

			$billing_data = $this->woocommerce_get_customer_meta_fields_saphali();

			echo '<div class="address">';

			if(isset($billing_data["billing"]) && is_array($billing_data["billing"]) && !$_billing_data) {
				foreach ( $billing_data["billing"] as $key => $field ) : if (isset($field['show']) && !$field['show']) continue;
					$field_name = '_'.$key;
					if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
					$value_fild = @$order->order_custom_fields[$field_name][0];
					elseif ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
					$value_fild = $order->__get( $key );
					else $value_fild = $order->get_meta( '_' . $key );
					if( $value_fild && isset($field["type"]) && in_array($field["type"] , array('select', 'radio')) && isset($field["options"][$value_fild]) ) {
						$value_fild = $field["options"][$value_fild];
					}
					if ( $value_fild  && !empty($field['label'])) 
					echo '<div class="form-field form-field-wide"><label><strong>'. $field['label']. ':</strong></label> ' . $value_fild.'</div>';
				endforeach;
			}
			if(isset($billing_data["shipping"]) && is_array($billing_data["shipping"]) && !$_shipping_data) {
				foreach ( $billing_data["shipping"] as $key => $field ) : if (isset($field['show']) && !$field['show']) continue;
					$field_name = '_'.$key;
					if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
					$value_fild = @$order->order_custom_fields[$field_name][0];
					elseif ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
					$value_fild = $order->__get( $key );
					else $value_fild = $order->get_meta( '_' . $key );
					if ( $value_fild  && !empty($field['label']) ) 
					echo '<div class="form-field form-field-wide"><label><strong>'. $field['label']. ':</strong></label> ' . $value_fild.'</div>';
				endforeach;
			}
			if(isset($billing_data["order"]) && is_array($billing_data["order"]) ) {
				foreach ( $billing_data["order"] as $key => $field ) : if (isset($field['show']) && !$field['show']) continue;
					$field_name = '_'.$key;
					if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) 
					$value_fild = @$order->order_custom_fields[$field_name][0];
					elseif ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
					$value_fild = $order->__get( $key );
					else $value_fild = $order->get_meta( '_' . $key );
					if ( $value_fild && !empty($field['label']) ) 
					echo '<div class="form-field form-field-wide"><label><strong>'. $field['label']. ':</strong></label> ' . $value_fild.'</div>';
				endforeach;
			}
			echo '</div>';
		}
	}
	function print_columns ($columns) {
		return $this->column_count_saphali;
	}
	function related_print_columns ($columns) {
		if( isset($columns['columns']) ) {
			$columns['columns'] = $this->column_count_saphali;
			$columns['posts_per_page'] = $this->column_count_saphali;
		}
		
		return $columns;
	}
	function print_script_columns($woocommerce_loop) {
		global $woocommerce_loop;
		if($woocommerce_loop['columns'] > 0 && $woocommerce_loop['columns'] != 4) {
		?>
		<style type='text/css'>
		.woocommerce ul.products li.product {
			width:<?php if($woocommerce_loop['columns'] <= 3 ) echo floor(100/$woocommerce_loop['columns'] - $woocommerce_loop['columns']); elseif($woocommerce_loop['columns'] > 3 )echo floor(100/$woocommerce_loop['columns'] - 4);?>%;
		}
		</style>
		<?php
		}
	}
 }

add_action('plugins_loaded', 'woocommerce_lang_s_l', 0);
if ( ! function_exists( 'woocommerce_lang_s_l' ) ) {
	function woocommerce_lang_s_l() {
		if ( ! defined( 'WOOCOMMERCE_VERSION' ) )
			return;
		if ( !version_compare( WOOCOMMERCE_VERSION, '8.5.0', '<' ) && ! defined( 'WC_BLOCKS_IS_FEATURE_PLUGIN' ) ) {
			define( 'WC_BLOCKS_IS_FEATURE_PLUGIN', true );
		}
		$lite = SaphWooManageFields();
		if( is_admin() )
			add_action( 'admin_enqueue_scripts',  array( $lite, 'admin_enqueue_scripts_page_saphali' ) );
	}
}
function SaphWooManageFields()
{
    return saphali_lite::instance();
}
//END

require_once (SAPHALI_PLUGIN_DIR_PATH . 'address-block-editor.php'); 

register_activation_hook( __FILE__, 'saphali_woo_lite_install' );

function saphali_woo_lite_install() {
	$filds_finish_filter = get_option('woocommerce_saphali_filds_filters');
	if($filds_finish_filter) {
		foreach($filds_finish_filter['billing'] as $k_f => $v_f) {
			$new_key = str_replace('billing_', '' , $k_f);
			if(in_array($new_key, array('country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode' ) )) {
				$locate[$new_key] = $v_f;
				if( isset($locate[$new_key]['clear']) && $locate[$new_key]['clear'] == 'on') $locate[$new_key]['clear'] = true;
				if( isset($locate[$new_key]['required']) && $locate[$new_key]['required'] == 'on') $locate[$new_key]['required'] = true;
			} elseif(in_array(str_replace('shipping_', '' , $k_f), array('country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode' ) )) {
				$locate[$new_key] = $filds_finish_filter['shipping'][$k_f];
				if( isset($locate[$new_key]['clear']) && $locate[$new_key]['clear'] == 'on') $locate[$new_key]['clear'] = true;
				if( isset($locate[$new_key]['required']) && $locate[$new_key]['required'] == 'on') $locate[$new_key]['required'] = true;
			}
			
		}
		update_option('woocommerce_saphali_filds_locate',$locate);
	}
}