<?php
/*
Plugin Name: Saphali Woocommerce LiqPay  for donate
Plugin URI: http://saphali.com/saphali-woocommerce-plugin-wordpress
Description: Кнопка для приема пожертвований.
Version: 1.0.3
Author: Saphali
Author URI: http://saphali.com/
Text Domain: saphali-liqpay-for-donate
Domain Path: /languages/
*/

include_once ( plugin_dir_path( __FILE__ ) . 'LiqPay-class.php' );

class liqpay_donate_saphali {

	var $liqpay;
	var $public_key;
	var $privat_key;
	var $menu_id;

	function __construct() {
		add_action( 'init', array( $this, 'loadTextDomain' ) );
		add_shortcode( 'saphali_liqpay', array( $this, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts_method' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts_method' ) );
		add_action( 'wp_ajax_liqpay_sign', array( $this, 'lqsignature' ) );
		add_action( 'wp_ajax_nopriv_liqpay_sign', array( $this, 'lqsignature' ) );

		$options = get_option( 'liqpay_donate_saphali' );
		$this->public_key = isset( $options['public_key'] ) ? $options['public_key'] : '';
		$this->privat_key = isset( $options['privat_key'] ) ? $options['privat_key'] : '';

		if ( ! ( empty( $this->public_key ) || empty( $this->privat_key ) ) ) {
			$this->liqpay = new LiqPayApi( $this->public_key, $this->privat_key );
		}

		add_action( 'admin_menu', array( $this, 'adminMenu' ) );
		add_action( 'admin_init', array( $this, 'add_button' ) );
	}

	function loadTextDomain() {
		load_plugin_textdomain(
			'saphali-liqpay-for-donate',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	function scripts_method() {
		wp_enqueue_script( 'jquery' );
	}

	function adminMenu() {
		if ( function_exists( 'add_menu_page' ) ) {
			$this->menu_id = add_menu_page(
				__( 'LiqPay', 'saphali-liqpay-for-donate' ),
				__( 'LiqPay donate', 'saphali-liqpay-for-donate' ),
				'manage_options',
				'liqpay-config',
				array( $this, 'configPage' ),
				plugins_url( 'images/menu_icons.png', __FILE__ )
			);
		}
	}

	function configPage() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['save'] ) ) {
			check_admin_referer( 'liqpay-config-options' );

			$options = array();
			$options['public_key'] = isset( $_POST['public_key'] ) ? sanitize_text_field( wp_unslash( $_POST['public_key'] ) ) : '';
			$options['privat_key'] = isset( $_POST['privat_key'] ) ? sanitize_text_field( wp_unslash( $_POST['privat_key'] ) ) : '';

			update_option( 'liqpay_donate_saphali', $options );

			$this->public_key = $options['public_key'];
			$this->privat_key = $options['privat_key'];

			if ( ! ( empty( $this->public_key ) || empty( $this->privat_key ) ) ) {
				$this->liqpay = new LiqPayApi( $this->public_key, $this->privat_key );
			}
		}

		$options = get_option( 'liqpay_donate_saphali' );
		$public_key = isset( $options['public_key'] ) ? esc_attr( $options['public_key'] ) : '';
		$privat_key = isset( $options['privat_key'] ) ? esc_attr( $options['privat_key'] ) : '';
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'LiqPay donate settings', 'saphali-liqpay-for-donate' ); ?></h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'liqpay-config-options' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="public_key"><?php esc_html_e( 'Public key', 'saphali-liqpay-for-donate' ); ?></label></th>
						<td><input type="text" id="public_key" name="public_key" value="<?php echo $public_key; ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="privat_key"><?php esc_html_e( 'Private key', 'saphali-liqpay-for-donate' ); ?></label></th>
						<td><input type="text" id="privat_key" name="privat_key" value="<?php echo $privat_key; ?>" class="regular-text" /></td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="save" id="save" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'saphali-liqpay-for-donate' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}

	function lqsignature() {
        if ( empty( $this->liqpay ) ) {
            wp_send_json_error();
        }

        $raw_data = isset( $_POST['data'] ) ? sanitize_text_field( wp_unslash( $_POST['data'] ) ) : '';
        $params   = json_decode( base64_decode( $raw_data ), true );

        if ( ! is_array( $params ) ) {
            wp_send_json_error();
        }

        // Amount: numeric and safe.
        if ( isset( $_POST['amount'] ) ) {
            $amount = floatval( sanitize_text_field( wp_unslash( $_POST['amount'] ) ) );
        } elseif ( isset( $params['amount'] ) ) {
            $amount = floatval( $params['amount'] );
        } else {
            $amount = 0;
        }

        if ( $amount <= 0 && isset( $params['amount'] ) ) {
            $amount = floatval( $params['amount'] );
        }

        // Order ID: safe string.
        if ( isset( $_POST['order_id'] ) ) {
            $order_id = sanitize_text_field( wp_unslash( $_POST['order_id'] ) );
        } elseif ( isset( $params['order_id'] ) ) {
            $order_id = sanitize_text_field( $params['order_id'] );
        } else {
            $order_id = '';
        }

        $params['amount']   = $amount;
        $params['order_id'] = $order_id;

        if ( function_exists( 'wp_json_encode' ) ) {
            $data = base64_encode( wp_json_encode( $params ) );
        } else {
            $data = base64_encode( json_encode( $params ) );
        }

        $signature = $this->liqpay->cnb_signature( $params );

        wp_send_json(
            array(
                'signature' => $signature,
                'data'      => $data,
            )
        );
    }

	function shortcode( $atts, $content = null ) {
		return do_shortcode( $this->saphali_shortcode( $atts ) );
	}

	function saphali_shortcode( $atts ) {
        if ( empty( $this->liqpay ) ) {
            return '';
        }

        // Defaults and sanitize incoming shortcode attributes.
        $atts = shortcode_atts(
            array(
                'amount' => 10,
                'desc'   => 'Пожертвование',
            ),
            $atts,
            'saphali_liqpay'
        );

        $order_id = time();

        // Amount: numeric only.
        $amount = isset( $atts['amount'] ) ? floatval( $atts['amount'] ) : 10;
        if ( $amount <= 0 ) {
            $amount = 10;
        }

        // Description: safe text only.
        $description = sanitize_text_field( $atts['desc'] );
        if ( $description === '' ) {
            $description = 'Пожертвование';
        }

        $params = array(
            'version'     => '3',
            'public_key'  => $this->public_key,
            'amount'      => $amount,
            'currency'    => 'UAH',
            'description' => $description,
            'order_id'    => $order_id,
            'pay_way'     => 'card,liqpay,delayed,invoice,privat24',
            'type'        => 'donate',
            'language'    => 'ru',
        );

        if ( function_exists( 'wp_json_encode' ) ) {
            $data = base64_encode( wp_json_encode( $params ) );
        } else {
            $data = base64_encode( json_encode( $params ) );
        }

        $signature = $this->liqpay->cnb_signature( $params );

        $data_attr      = esc_attr( $data );
        $signature_attr = esc_attr( $signature );
        $amount_attr    = esc_attr( $amount );
        $order_id_attr  = esc_attr( $order_id );

        ob_start();
        ?>
        <form id="liqpayform" method="POST" action="https://www.liqpay.ua/api/checkout" accept-charset="utf-8">
            <input type="hidden" name="data" value="<?php echo $data_attr; ?>" />
            <input type="text" class="form__input__new" name="amount" value="<?php echo $amount_attr; ?>" />
            <input type="hidden" name="order_id" value="<?php echo $order_id_attr; ?>" />
            <input type="hidden" name="signature" value="<?php echo $signature_attr; ?>" />
            <input type="image" src="//static.liqpay.ua/buttons/d1ru.radius.png" alt="LiqPay" />
        </form>
        <style>
            .form__input__new {
                background: #fff none repeat scroll 0 0;
                border: 1px solid #b0b0b0;
                color: #000;
                font-size: 14px;
                height: 36px;
                margin-right: 5px;
                margin-top: 0px;
                padding-left: 10px;
                width: auto;
                min-width: 40px;
            }
			#liqpayform {
				display: flex;
				flex-wrap: wrap;
				flex-direction: row;
				align-content: center;
				justify-content: flex-start;
				align-items: center;
			}
        </style>
        <script>
            jQuery(function($){
                var $form = $("form#liqpayform");

                // Если вдруг протокол без схемы – форсим https для LiqPay.
                var action = $form.attr("action");
                if (action && action.indexOf("//") === 0) {
                    $form.attr("action", "https:" + action);
                }

                $form.find('input[type="image"]').on("click", function(e){
                    e.preventDefault();

                    var amount   = $form.find('input[name="amount"]').val();
                    var order_id = $form.find('input[name="order_id"]').val();
                    var dataVal  = $form.find('input[name="data"]').val();

                    $.ajax({
                        url: "/wp-admin/admin-ajax.php?action=liqpay_sign",
                        method: "POST",
                        dataType: "json",
                        data: {
                            amount: amount,
                            order_id: order_id,
                            data: dataVal
                        },
                        success: function(resp){
                            if (resp && resp.signature && resp.data) {
                                $form.find('input[name="signature"]').val(resp.signature);
                                $form.find('input[name="data"]').val(resp.data);
                                $form.trigger("submit");
                            }
                        }
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

	function add_button() {
	   if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
		 add_filter( 'mce_external_plugins', array( $this, 'add_plugin' ) );
		 add_filter( 'mce_buttons', array( $this, 'register_button' ) );
	   }
	}

	function register_button( $buttons ) {
	   array_push( $buttons, "saphali_liqpay" );
	   return $buttons;
	}

	function add_plugin( $plugin_array ) {
		$plugin_array['saphali_liqpay'] = plugin_dir_url( __FILE__ ) . 'js/customcodes.js';
		return $plugin_array;
	}
}

new liqpay_donate_saphali();
