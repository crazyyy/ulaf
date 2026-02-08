<?php

if (! defined('ABSPATH')) {
    exit; // Защита от прямого доступа
}

use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields;

// Рекомендуется использовать класс для лучшей структуры:
if (! class_exists('Saphali_DateTime_Checkout_Field')) :

    class Saphali_DateTime_Checkout_Field
    {
        protected $dir_path;
        protected $fields;
        protected $fields_in_checkout;
        protected $render_fields;
        protected $hide_fields;
        protected static $_instance = null;
        /**
         * Main Saphali_DateTime_Checkout_Field Instance.
         *
         * @since 1.0.0
         * @static
         * @see SaphWooDateTime_Checkout_Field()
         * @return Saphali_DateTime_Checkout_Field - Main instance.
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }
        public function __construct()
        {
            // Регистрируем дополнительное поле
            $this->dir_path = plugin_dir_path(__FILE__);
            // var_dump($this->get_fields_in_checkout());exit;
            add_action('woocommerce_init', [$this, 'register_datetime_field']);
            // add_action('woocommerce_init', [$this, 'deregister_datetime_field']);
            add_action('woocommerce_default_address_fields', [$this, 'default_address_fields']);

            // Подключаем CSS/JS на фронтенде
            add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets'], 20);

            // Валидируем и санитизируем поле
            add_action('woocommerce_sanitize_additional_field', [$this, 'sanitize_datetime_field'], 10, 2);
            add_action('woocommerce_validate_additional_field', [$this, 'validate_datetime_field'], 10, 3);

            // Выводим поле даты и времени на странице "Спасибо за заказ"
            add_action('woocommerce_thankyou', [$this, 'display_datetime_in_thankyou'], 20);

            // Выводим поле даты и времени в админке заказа
            add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'display_billing_in_admin'], 9);
            add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'display_shipping_in_admin'], 9);

            // add_filter('woocommerce_get_country_locale', [$this, 'custom_validate_customer_address'], 10);
        }
        
        function get_fields_in_checkout()
        {
            if (isset($this->fields_in_checkout)) return $this->fields_in_checkout;
            $checkout_fields = $this->get_fields();
            $finish_fields = array();
            if ($checkout_fields) {
                if (isset($checkout_fields)) {
                    foreach (array('billing', 'shipping', 'order') as $method) {
                        if(isset($checkout_fields[$method]))
                        foreach ($checkout_fields[$method] as $key => $val) {
                            $a = $val["added"] ? 'saphali-': '';
                            if ($val["public"])
                                $finish_fields[ str_replace("{$method}_","{$method}_" . $a, $key) ] = $val;
                            else
                                $finish_fields[$a . $key] = 'remove';
                        }
                    }
                    if(isset($checkout_fields['extra']['dellete_fields']) && is_array($checkout_fields['extra']['dellete_fields']))
                    foreach ($checkout_fields['extra']['dellete_fields'] as $f) {
                        $finish_fields[$f] = 'remove';
                    }
                }
            }
            $this->fields_in_checkout = $finish_fields;
            return $finish_fields;
        }
        function get_fields()
        {
            if (isset($this->fields)) return $this->fields;
            $this->fields = get_option('woocommerce_saphali_fields_blocks', array());
            return $this->fields;
        }
        function default_address_fields($fields)
        {
            $checkout_fields = $this->get_fields();
            $finish_fields = array();
            if ($checkout_fields) {
                foreach (array('billing', 'shipping') as $method) {
                    if (isset($checkout_fields[$method])) {
                        foreach ($checkout_fields[$method] as $key => $val) {
                            if(!$val['required'] || !isset($finish_fields[str_replace("{$method}_", '', $key)]))
                                $finish_fields[str_replace("{$method}_", '', $key)] = $val['required'];
                        }
                        foreach ($checkout_fields['extra']['dellete_fields'] as $f) {
                            $finish_fields[str_replace("{$method}_", '', $f)] = false;
                        }
                    }
                }
            }

            // $checkout_fields = ['city', 'postcode', 'address_1'];
            // var_dump($finish_fields);exit;
            foreach ($finish_fields as $field => $required) {
                if (isset($fields[$field])) {
                    $fields[$field]['required'] = $required;
                }
            }

            return $fields;
        }
        public function render_fields()
        {
            if (isset($this->render_fields)) return $this->render_fields;
            $settings_fields = $this->get_fields();
            $position_in_contact = isset($settings_fields['extra']['position-order-fields']) ? $settings_fields['extra']['position-order-fields'] : false;
            $fields = $this->get_fields_in_checkout();
            $render_fields = array();
            foreach (array('billing', 'shipping', 'order') as $type) {
                foreach ($fields as $key => $val) {

                    if ($val !== 'remove' && $val['added'] && strpos($key, "{$type}_") === 0) {
                        $render_fields[$type] = isset($render_fields[$type]) ? $render_fields[$type] : [];
                        $a = $val["added"] ? 'saphali-': '';
                        array_push($render_fields[$type], array(
                            'id'         => str_replace("{$type}_", 'saphali/', str_replace("{$type}_" . $a,"{$type}_", $key) ),
                            'label'      => __($val['label'], 'saphali-woocommerce-lite'),
                            'optionalLabel' => __($val['label'], 'saphali-woocommerce-lite') . __(' (optional)', 'saphali-woocommerce-lite'),
                            'location'   => $type !== 'order' ? 'address' : ($position_in_contact ? 'contact' : (version_compare(WOOCOMMERCE_VERSION, '8.9.0', '<') ? 'additional' : 'order')), // or 
                            'type'       => ($val['type'] === 'date' ? 'text' : (in_array($val['type'], ['text', 'select', 'checkbox']) ? $val['type'] : ($val['type'] === 'radio' ? 'select' : 'text'))),
                            'required'   => $val['required'],
                            'attributes' => array(
                                'title'        => isset($val['placeholder']) ? __($val['placeholder'], 'saphali-woocommerce-lite') : '',
                                'autocomplete' => 'off',
                            )
                        ));
                        if($val['type'] === 'date') {
                            $render_fields[$type][sizeof($render_fields[$type]) - 1]['attributes']['readOnly'] = true;
                        }
                        if (isset($val['options'])) {
                            foreach ($val['options'] as $key => $value) {
                                $render_fields[$type][sizeof($render_fields[$type]) - 1]['options'][] = array('value' => $value, 'label' => $value);
                            }
                        }
                    }
                }
            }
            $this->render_fields = $render_fields;
            return $render_fields;
        }
        /**
         * Регистрируем дополнительное поле даты и времени.
         */
        public function register_datetime_field()
        {
            if(function_exists('woocommerce_register_additional_checkout_field')) {
                $render_fields = $this->render_fields();
                foreach ($render_fields as $method => $options) {

                    foreach ($options as $field_option) {
                        woocommerce_register_additional_checkout_field(
                            $field_option
                        );
                    }
                        
                }
            } elseif(function_exists('__experimental_woocommerce_blocks_register_checkout_field')) {
                $render_fields = $this->render_fields();
                foreach ($render_fields as $method => $options) {
    
                    foreach ($options as $field_option) {
                        __experimental_woocommerce_blocks_register_checkout_field(
                            $field_option
                        );
                    }
                }
            } elseif(function_exists('woocommerce_blocks_register_checkout_field')) {
                $render_fields = $this->render_fields();
                foreach ($render_fields as $method => $options) {
                    foreach ($options as $field_option) {
                        
                        woocommerce_blocks_register_checkout_field(
                            $field_option
                        );
                    }
                }
            } elseif(class_exists('Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields')) {
                $render_fields = $this->render_fields();
                foreach ($render_fields as $method => $options) {
                    /* array_map(function($v){
                        $v['id'] = str_replace('saphali/', 'saphali-', $v['id']);
                        return $v;
                    }, $options) */
                    if($options) {
                        foreach ($options as $field_option) {
                            $this->woocommerce_blocks_register_checkout_field(
                                $field_option
                            );
                        }
                    }
                }
            };
            
            // error_log(var_export($key, true) . var_export($options, true));
            // $this->deregister_datetime_field();

        }
        function woocommerce_blocks_register_checkout_field( $options ) {

            // Check if `woocommerce_blocks_loaded` ran. If not then the CheckoutFields class will not be available yet.
            // In that case, re-hook `woocommerce_blocks_loaded` and try running this again.
            $woocommerce_blocks_loaded_ran = did_action( 'woocommerce_blocks_loaded' );
            if ( ! $woocommerce_blocks_loaded_ran ) {
                add_action(
                    'woocommerce_blocks_loaded',
                    function() use ( $options ) {
                        $this->woocommerce_blocks_register_checkout_field( $options );
                    }
                );
                return;
            }
            $checkout_fields = Package::container()->get( \Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields::class );
            $result          = $checkout_fields->register_checkout_field( $options );
            if ( is_wp_error( $result ) ) {
                throw new Exception( $result->get_error_message() );
            }
        }
        public function hide_fields()
        {
            if (isset($this->hide_fields)) return $this->hide_fields;
            $fields_in_checkout = $this->get_fields_in_checkout();
            $hide_fields = array();
            foreach ($fields_in_checkout as $key => $val) {
                if (is_string($val) &&  $val === 'remove') {
                    foreach (['billing', 'shipping', 'order', 'account'] as $type) {
                        if (strpos($key, $type) === 0) {
                            $hide_fields[$type] = isset($hide_fields[$type]) ? $hide_fields[$type] : [];
                            array_push($hide_fields[$type], str_replace("{$type}_", '', $key));
                        }
                    }
                }
            }
            $this->hide_fields = $hide_fields;

            return $hide_fields;
        }
        function custom_validate_customer_address($locale)
        {
            // $locale['UA']['address_1']['required'] = false;
            // foreach ( $locale as $key => $value ) {
            //     // if ( ! is_array( $value ) ) {
            //     //     continue;
            //     // }
            //     $fields_in_checkout = $this->get_fields_in_checkout();
            //     // var_dump($fields_in_checkout);exit;
            //     foreach ( $fields_in_checkout as $k => $v ) {
            //         if ( !(strpos( $k, "billing_" ) === 0 || strpos( $k, "shipping_" ) === 0) ) {
            //             continue;
            //         }
            //         if ( ! is_array( $v ) ) {
            //             if($v === 'remove') {
            //                 $locale[ $key ][ str_replace( array('billing_','shipping_'), '', $k ) ]['required'] = false;
            //                 $locale[ $key ][ str_replace( array('billing_','shipping_'), '', $k ) ]['hidden'] = true;
            //             }
            //             continue;
            //         }
            //         $_k = str_replace( array( 'billing_', 'shipping_' ), '', $k );
            //         if ( isset( $v['required'] ) /* && isset( $locale[ $key ][ $_k ] ) */ && strpos( $_k, 'saphali' ) !== 0 ) {
            //             if(isset($locale[ $key ][ $_k ]['required']) && $locale[ $key ][ $_k ]['required']) {
            //                 $locale[ $key ][ $_k ]['required'] = $v['required'];    
            //             }
            //             if(isset($locale[ $key ][ $_k ]['hidden']) && !$locale[ $key ][ $_k ]['hidden']) {
            //                 $locale[ $key ][ $_k ]['hidden'] = !$v['public'];    
            //             }
            //         }
            //     }
            // }
            // var_dump($locale);exit;
            return $locale;
        }
        /**
         * Подключаем CSS и JS.
         */
        public function enqueue_frontend_assets()
        {
            // Убрать или закомментировать подключение jQuery UI Datepicker и Timepicker Addon
            if(!is_checkout()) return;
			$lite = SaphWooManageFields();
			if($lite->get_checkout_type() !== 'block') return;
           
            // Подключаем скомпилированный frontend.js

            $asset_file = plugin_dir_path(__FILE__) . 'build/frontend/frontend.asset.php';
            if (file_exists($asset_file)) {
                $asset_info = include $asset_file;
                wp_register_script(
                    'saphali-fields-frontend',
                    plugins_url('build/frontend/frontend.js', __FILE__),
                    $asset_info['dependencies'] + ['wc-checkout-block'],
                    $asset_info['version'],
                    true
                );
            }
            
            $get_keys = $lite->get_keys_method_checkout();
            $checkout_fields = $this->get_fields_in_checkout();
            
            if((bool)array_filter($checkout_fields, function($v){
                return isset($v['type']) && $v['type'] === 'date';
            })) {
                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_style('jquery-ui-datepicker-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    
                wp_enqueue_script('jquery-ui-timepicker-addon', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js', array('jquery', 'jquery-ui-datepicker'), '1.6.3', true);
                wp_enqueue_style('jquery-ui-timepicker-addon-css', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css');
            }
            $settings_fields = $this->get_fields();
            $position_in_contacts = isset($settings_fields['extra']['position-order-fields']) ? $settings_fields['extra']['position-order-fields'] : false;
            $checkout_fields = array_map(function ($v) {
                return array('pm' => $v['payment_method'], 'sm' => $v['shipping_method'], 
                'type' => isset($v['type']) ? $v['type'] : 'text', 
                'added' => $v['added'],
                'required' => $v['required'],
                'label' => isset($v['label']) ? __(__($v['label'], 'woocommerce'), 'saphali-woocommerce-lite') : '',
                'index' => $v['order']
                );
            }, array_filter($checkout_fields, function ($v) {
                return $v !== 'remove';
            }));
            $new_checkout_fields = [];
            foreach ($checkout_fields as $key => $val) {
                foreach (['billing', 'shipping', 'order', 'additional'] as $type) {
                    if (strpos($key, $type) === 0) {
                        if ('order' === $type) {
                            $_type = $position_in_contacts ? 'contact' : 'additional-information';
                        }elseif ( 'additional' === $type) {
                            $_type = $position_in_contacts ? 'contact' : 'additional-information';
                        } else {
                            $_type = $type;
                        }
                        $new_checkout_fields[$_type][str_replace("{$type}_", '', $key)] = $val;
                        if( version_compare(WOOCOMMERCE_VERSION, '8.9.0', '<') && strpos($key, "{$type}_saphali-") === 0) {
                            $new_checkout_fields[$_type][str_replace(["{$type}_", 'saphali-'], ['saphali/'], $key)] = $val;
                        }
                    }
                }
            }
            wp_localize_script(
                'saphali-fields-frontend',
                'saphaliSettingsFrontend', // saphaliSettingsFrontend.saphaliKeys
                array(
                    'saphaliKeys' => $get_keys['keys'],
                    'saphaliSkeys' => $get_keys['skeys'],
                    'hideFields' => $this->hide_fields(),
                    'checkout_fields' => $new_checkout_fields,
                    'onlyDate' => isset($settings_fields['extra']['onlydate-fields']) ? $settings_fields['extra']['onlydate-fields'] : false,
                    'position_in_contacts' => $position_in_contacts
                ) // Массив с ключами
            );
            wp_enqueue_script('saphali-fields-frontend');


            // Устанавливаем локализацию для скрипта
            wp_set_script_translations('saphali-fields-frontend', 'saphali-woocommerce-lite', plugin_dir_path(__FILE__) . 'languages');
            
            load_script_textdomain('saphali-fields-frontend', 'saphali-woocommerce-lite', plugin_dir_path(__FILE__) . 'languages');

            // Подключаем скомпилированный frontend.css
            wp_enqueue_style(
                'saphali-fields-frontend',
                plugins_url('build/frontend/frontend.css', __FILE__),
                array(),
                '1.0.0'
            );
        }

        /**
         * Санитизация поля.
         *
         * @param string $field_value Значение поля.
         * @param string $field_key   Ключ поля.
         * @return string
         */
        public function sanitize_datetime_field($field_value, $field_key)
        {
            if ('saphali/datetime' === $field_key) {
                // Убираем лишние пробелы и экранируем
                $field_value = sanitize_text_field(trim($field_value));
            }
            return $field_value;
        }

        /**
         * Валидация поля.
         *
         * @param WP_Error $errors     Объект ошибок.
         * @param string   $field_key  Ключ поля.
         * @param string   $field_value Значение поля.
         */
        public function validate_datetime_field($errors, $field_key, $field_value)
        {
            if ('saphali/datetime' === $field_key) {
                if (! empty($field_value)) {
                    // Проверяем формат даты и времени (например, 'mm/dd/yyyy HH:MM')
                    $datetime = date_create_from_format('m/d/Y H:i', $field_value);
                    if (! $datetime) {
                        $errors->add('invalid_datetime', __('Please enter a valid date and time.', 'saphali-woocommerce-lite'));
                    }
                }
            }
        }

        /**
         * Выводим поле даты и времени на странице "Спасибо за заказ".
         *
         * @param int $order_id ID заказа.
         */
        public function display_datetime_in_thankyou($order_id)
        {
            if (! $order_id) {
                return;
            }

            $order = wc_get_order($order_id);
            if (! $order) {
                return;
            }
            $render_fields = $this->render_fields();
            foreach ($render_fields as $area => $fields) {
                $additional_fields = $order->get_meta("_additional_{$area}_fields");
                if($additional_fields) {
                    $block = '<div class="saphali-datetime-thankyou" style="margin-top:10px;">';
                    $print = false;
                    foreach ($fields as $field) {
                        $value = isset($additional_fields[$field['id']]) ? $additional_fields[$field['id']] : '';
                        if ($value) {
                           $print = true;
                            $block .= '<p style="margin: 0;"><strong>' . esc_html($field['label']) . '</strong>: ' . esc_html($value) . '</p>';
                            
                        }
                    }
                    $block .= '</div>';
                    if($print) {
                        echo $block;
                    }
                }
            }
        }

        /**
         * Выводим поле даты и времени в админке заказа.
         *
         * @param WC_Order $order Заказ.
         */
        public function display_billing_in_admin($order)
        {
            $render_fields = $this->render_fields();
            foreach ($render_fields as $area => $fields) {
                if($area !== 'billing') continue;
                $additional_fields = $order->get_meta("_additional_{$area}_fields");
                if($additional_fields) {
                    $this->displayinadm($fields, $additional_fields);
                }
            }
        }
        public function display_shipping_in_admin($order)
        {
            $render_fields = $this->render_fields();
            foreach ($render_fields as $area => $fields) {
                if($area === 'billing') continue;
                $additional_fields = $order->get_meta("_additional_{$area}_fields");
                if($additional_fields) {
                    $this->displayinadm($fields, $additional_fields);
                }
            }
        }
        function displayinadm($fields, $additional_fields) {
            $block = '<div class="saphali-datetime-admin" style="margin-top:10px;">';
            $print = false;
            foreach ($fields as $field) {
                $value = isset($additional_fields[$field['id']]) ? $additional_fields[$field['id']] : '';
                if ($value) {
                    $print = true;
                    $block .= '<p><strong>' . esc_html(__(__($field['label'], 'woocommerce'), 'saphali-woocommerce-lite')) .  '</strong>: ' . esc_html($value) . '</p>';
                }
            }
            $block .= '</div>';
            if($print) {
                echo $block;
            }
        }
    }
    

    function SaphWooDateTime_Checkout_Field()
    {
        return Saphali_DateTime_Checkout_Field::instance();
    }
    SaphWooDateTime_Checkout_Field();
endif;
