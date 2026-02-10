<?php
use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields;
if (! defined('ABSPATH')) {
    exit;
}
if(version_compare(WC_VERSION, '8.5.0', '<')) {
    $warning_massage = sprintf(__('Для работы плагина Saphali WooCommerce Lite в блоковом чекауте требуется WooCommerce 8.5.0 или выше. У Вас установлен WooCommerce %s', 'saphali-woocommerce-lite'), WC_VERSION);
    echo '<div class="notice notice-error is-dismissible"><p>' . $warning_massage . '</p></div>';
}
$fsss = new Saphali_DateTime_Checkout_Field();
$render_fields = $fsss->render_fields();
// get_fields_for_location( $location )
// $checkout_fields = Package::container()->get( CheckoutFields::class );
// $checkout_fields->get_field_location( 'phone' ), 

$hide_fields = $fsss->hide_fields();

// var_dump($hide_fields);
// Загрузим текущие настройки
$fieldss = get_option('woocommerce_saphali_fields_blocks', array());
if (! is_array($fieldss)) {
    $fieldss = array();
}
$this->locale_tmp = get_locale();

if (!class_exists('WC_Checkout')) WC()->autoload('WC_Checkout');
$checkout = new WC_Checkout();
// Если форма сохранена:
if ((! empty($_POST['saphali_save_fields']) || ! empty($_POST['saphali_reset_fields'])) && check_admin_referer('saphali_fields_nonce', 'saphali_fields_nonce')) {
    // Обрабатываем $_POST, собираем массив $fieldss и сохраняем в опцию
    // (На самом деле тут код, который извлекает billing[new_field], shipping[new_field] и т.д.)
    if( isset($_POST['saphali_save_fields']) ) {
        // Инициализируем массив для хранения данных
        $data = array();

        // Определяем группы полей
        $field_groups = array( 'billing', 'shipping', 'order', 'account' );
        foreach ( $field_groups as $group ) {
            if ( isset( $_POST[ $group ] ) && is_array( $_POST[ $group ] ) ) {
                // Обрабатываем стандартные поля (не 'new')

                $_default_field = $checkout->get_checkout_fields($group);
               
                foreach ( $_POST[ $group ] as $field_key => $field_values ) {
                    
                    if ( $field_key === 'new' ) {
                        continue; // Пропускаем обработку 'new' на этом этапе
                    } else {
                        if(isset($_POST[ $group ]['new']['key']) && is_array($_POST[ $group ]['new']['key'])) {
                            if(in_array($field_key, $_POST[ $group ]['new']['key'])) {
                                continue;
                            }
                        }
                    }

                    if ( is_array( $field_values ) ) {
                        $curent_default_field = isset($_default_field[ $field_key ]) ? $_default_field[ $field_key ] : array();
                        if(isset($field_values['options']) && is_array($field_values['options'])) {
                            $options = array();
                            // Предполагаем, что $option_values — это массив с ключом и значением
                            foreach ( (array)$field_values['options'] as $option_label => $option_value ) {
                                $options[ sanitize_text_field( $option_label ) ] = sanitize_text_field( $option_value );
                            }
                            $curent_default_field['options'] = $options;
                        } elseif(isset($field_values['type']) && in_array($field_values['type'], array('select', 'radio'))) {
                            $options = array();
                            $curent_default_field['options'] = $options;
                        }
                        
                        // Санитизация и сохранение стандартных полей
                        $data[ $group ][ $field_key ] = wp_parse_args( wp_parse_args( array(
                            'label'           => isset($field_values['label']) ? str_replace(array('&#039;', "'"), '’', sanitize_text_field( wp_unslash ($field_values['label']) )) : '',
                            // 'placeholder'     => str_replace(array('&#039;', "'"), '’', sanitize_text_field( wp_unslash ($field_values['placeholder']) )),
                            'order'           => isset($field_values['order']) ? intval( $field_values['order'] ) : '',
                            'required'        => isset( $field_values['required'] ) && $field_values['required'] === 'on' ? true : false,
                            'public'          => isset( $field_values['public'] ) && $field_values['public'] === 'on' ? true : false,
                            'payment_method'  => isset($field_values['payment_method']) ? array_map( 'sanitize_text_field', (array)$field_values['payment_method'] ) : '0',
                            'shipping_method' => isset($field_values['shipping_method']) ? array_map( 'sanitize_text_field', (array)$field_values['shipping_method'] ) : '0',
                            'added' => isset( $field_values['added'] ) && $field_values['added'] === 'on' ? true : false
                            // Добавьте другие атрибуты при необходимости
                        ), $_POST[ $group ][ $field_key ]),  $curent_default_field);
                    }
                }
    
                // Обрабатываем новые поля ('new')
                if ( isset( $_POST[ $group ]['new'] ) && is_array( $_POST[ $group ]['new'] ) ) {
                    $new_fields = $_POST[ $group ]['new'];
    
                    // Проверяем, что ключи 'key', 'label', 'placeholder' существуют и являются массивами
                    $required_keys = array( 'key', 'label', 'order', 'public', 'type' );
                    $all_keys_present = true;
    
                    foreach ( $required_keys as $req_key ) {
                        if ( ! isset( $new_fields[ $req_key ] ) ) {
                            $all_keys_present = false;
                            break;
                        }
                    }
    
                    if ( $all_keys_present ) {
                        // Предполагаем, что 'key' является массивом
                        $new_field_count = count( $new_fields['key'] );
                        $data['extra']['new_fields'] = array();
                        for ( $i = 0; $i < $new_field_count; $i++ ) {
                            // Получаем значения для текущего нового поля
                            $key = isset( $new_fields['key'][ $i ] ) ? sanitize_text_field( $new_fields['key'][ $i ] ) : '';
                            $label = isset( $new_fields['label'][ $i ] ) ? sanitize_text_field( wp_unslash($new_fields['label'][ $i ]) ) : '';
                            $placeholder = isset( $new_fields['placeholder'][ $i ] ) ? sanitize_text_field( wp_unslash ($new_fields['placeholder'][ $i ] ) ) : '';
                            $type = isset( $new_fields['type'] ) ? sanitize_text_field( $new_fields['type'] ) : 'text';
                            $order = isset( $new_fields['order'][ $i ] ) ? intval( $new_fields['order'][ $i ] ) : 0;
                            $public = isset( $new_fields['public'][ $i ] ) && $new_fields['public'][ $i ] === 'on' ? true : false;
                            $required = isset( $new_fields['required'] ) && $new_fields['required'] === 'on' ? true : false;
                            // Обработка payment_method и shipping_method как массивов
                            $payment_method = isset( $new_fields['payment_method'][ $i ] ) ? sanitize_text_field( $new_fields['payment_method'][ $i ] ) : '0';
                            $shipping_method = isset( $new_fields['shipping_method'][ $i ] ) ? sanitize_text_field( $new_fields['shipping_method'][ $i ] ) : '0';
    
                            // Если есть опции (например, для select), обработаем их
                            $options = array();
                            if ( isset( $_POST[ $group ][$new_fields['key'][ $i ]]['options'] ) && is_array( $_POST[ $group ][$new_fields['key'][ $i ]]['options'] ) ) {
                                foreach ( $_POST[ $group ][$new_fields['key'][ $i ]]['options'] as $option_key => $option_values ) {
                                    // Предполагаем, что $option_values — это массив с ключом и значением
                                    foreach ( $option_values as $option_label => $option_value ) {
                                        $options[ sanitize_text_field( $option_label ) ] = sanitize_text_field( $option_value );
                                    }
                                }
                            }
    
                            // Добавляем новое поле в соответствующую группу
                            $data[ $group ][ $key ] = array(
                                'label'           => str_replace(array('&#039;', "'"), '’', $label),
                                // 'placeholder'     => str_replace(array('&#039;', "'"), '’', $placeholder),
                                'type'            => $type,
                                'order'           => $order,
                                'required'        => $required,
                                'public'          => $public,
                                'payment_method'  => array( $payment_method ), // Преобразуем в массив
                                'shipping_method' => array( $shipping_method ), // Преобразуем в массив
                                'options'         => $options, // Для select и других типов
                                'added' => true
                            );
                        }
                    }
                }
            }
        }
        $data['extra']['dellete_fields'] = array_keys(array_diff_key( $checkout->get_checkout_fields('billing'), $data['billing'])) + array_keys(array_diff_key( $checkout->get_checkout_fields('shipping'), $data['shipping']));
        
        $data['extra']['position-order-fields'] = isset($_POST['position-order-fields']) ? true : false;
        $data['extra']['onlydate-fields'] = isset($_POST['onlydate-fields']) ? true : false;
        
    } elseif(isset($_POST['saphali_reset_fields'])){
        $data = array();
        $onlydate = isset($fieldss['extra']['onlydate-fields']) ? $fieldss['extra']['onlydate-fields'] : false;
        $position = isset($fieldss['extra']['position-order-fields']) ? $fieldss['extra']['position-order-fields'] : false;
        $data['extra']['position-order-fields'] = $position;
        $data['extra']['onlydate-fields'] = $onlydate;

    }
    update_option('woocommerce_saphali_fields_blocks', $data);
    $fieldss = get_option('woocommerce_saphali_fields_blocks', array());
    echo '<div class="updated"><p>Настройки полей сохранены!</p></div>';
} elseif(! empty($_POST['saphali_save_fields'])) {
    wp_die( __( 'Неуспешная верификация.', 'saphali-woocommerce-lite' ) );
}
$onlydate = isset($fieldss['extra']['onlydate-fields']) ? $fieldss['extra']['onlydate-fields'] : false;
$position = isset($fieldss['extra']['position-order-fields']) ? $fieldss['extra']['position-order-fields'] : false;
// remove_all_filters('woocommerce_default_address_fields');
// var_dump($position, $fieldss['extra']);
?>
<div class="wrap woocommerce">
    <h2><?php _e('Настройки Saphali WooCommerce Lite (блоки)', 'saphali-woocommerce-lite'); ?></h2>
    <!-- <p><?php _e('Здесь можно управлять полями чекаута.', 'saphali-woocommerce-lite'); ?></p> -->
    <ul class="subsubsub">
            <li><a href="admin.php?page=woocommerce_saphali_s_l" <?php if(empty($_GET["tab"])) echo 'class="current"';?> ><?php _e('Управление полями', 'saphali-woocommerce-lite'); ?></a> | </li>
            <!-- <li><a href="admin.php?page=woocommerce_saphali_s_l&tab=2" <?php if(!empty($_GET["tab"]) && $_GET["tab"] == 2) echo 'class="current"';?>><?php _e('Число колонок в каталоге', 'saphali-woocommerce-lite'); ?></a> | </li> -->
            <li><a href="admin.php?page=woocommerce_saphali_s_l&tab=1" <?php if(!empty($_GET["tab"]) && $_GET["tab"] == 1) echo 'class="current"';?>><span color="red"><?php _e('Дополнительная информация', 'saphali-woocommerce-lite'); ?></span></a></li>
    </ul>
    <?php if(empty($_GET["tab"])) { ?>
    <form method="post">
        <?php wp_nonce_field('saphali_fields_nonce', 'saphali_fields_nonce'); ?>


        <table class="wp-list-table widefat striped" id="billing_fields_table">
            <thead>
                <tr>
                    <th width="17%"><?php _e('Поле (key)', 'saphali-woocommerce-lite'); ?></th>
                    <th width="17%"><?php _e('Заголовок (label)', 'saphali-woocommerce-lite'); ?></th>
                    <!-- <th><?php _e('Плейсхолдер', 'saphali-woocommerce-lite'); ?></th> -->
                    <!-- <th width="35px"><?php _e('Clear', 'saphali-woocommerce-lite'); ?><img class="help_tip" data-tip="<?php _e('Указывает на то, что следующее поле за текущим, будет начинаться с новой строки.', 'saphali-woocommerce-lite');?>" src="<?php bloginfo('wpurl'); ?>/wp-content/plugins/woocommerce/assets/images/help.png" /> </th> -->
                    <!-- <th width="130px"><?php _e('Класс поля', 'saphali-woocommerce-lite'); ?><img class="help_tip" data-tip="<?php printf("<h3 style='margin:0;padding:0'>%s</h3><ul style='text-align: left;'><li><span style='color: #000'>form-row-first</span>&nbsp;&ndash;&nbsp;%s;</li><li><span style='color: #000'>form-row-last</span>&nbsp;&ndash;&nbsp;%s.</li></ul><hr /><span style='color: #000'>%s</span>, %s", 
                    __('Задает стиль текущего поля', 'saphali-woocommerce-lite'),
                    __('первый в строке', 'saphali-woocommerce-lite'),
                    __('последний в строке', 'saphali-woocommerce-lite'),
                    __('ЕСЛИ ОСТАВИТЬ ПУСТЫМ', 'saphali-woocommerce-lite'),
                    __('то поле будет отображаться на всю ширину. Соответственно, в предыдущем поле (которое выше) нужно отметить &laquo;Clear&__(raquo;.', 'saphali-woocommerce-lite'));?>" src="<?php bloginfo('wpurl'); ?>/wp-content/plugins/woocommerce/assets/images/help.png" /></th> -->
                    <th width="16%"><?php _e('Тип поля', 'saphali-woocommerce-lite'); ?></th>
                    <!-- <th><?php _e('Order (порядок)', 'saphali-woocommerce-lite'); ?></th> -->
                    <th width="6%"><?php _e('Обза&shy;те&shy;ль&shy;ное?', 'saphali-woocommerce-lite'); ?></th>
                    <th width="6%"><?php _e('Опу&shy;бли&shy;ковать', 'saphali-woocommerce-lite'); ?></th>
                    <th width="16%"><?php _e('Метод оплаты', 'saphali-woocommerce-lite'); ?></th>
                    <th width="16%"><?php _e('Метод доставки', 'saphali-woocommerce-lite'); ?></th>
                    <th width="5%"></th>
                </tr>
            </thead>
            <tbody class="myTable">
                <?php
                foreach (array('billing', 'shipping', 'order', 'account') as $method) {
                    $default_field = !isset($fieldss[$method]);
                    if( $default_field ) {
                        if (!empty($this->locale_tmp) && $this->locale_tmp != 'en_US') {
                            switch_to_locale('en_US');
                        }
                        $fields[$method] = $checkout->get_checkout_fields($method);
                        if (!empty($this->locale_tmp) && $this->locale_tmp != 'en_US') {
                            switch_to_locale($this->locale_tmp);
                        }
                    } else $fields[$method] = $fieldss[$method];

                    if ($method === 'billing')
                        $title = __('Billing Fields', 'saphali-woocommerce-lite');
                    elseif ($method === 'shipping')
                        $title = __('Shipping Fields', 'saphali-woocommerce-lite');
                    elseif ($method === 'account')
                        $title = __('Account Fields', 'saphali-woocommerce-lite');
                    elseif ($method === 'order')
                        $title = __('Order Fields', 'saphali-woocommerce-lite');
                    // if(empty($fields[$method])) continue;
                ?><tr class="nodrop nodrag">
                        <td class="title" colspan="12">
                            <h3><?php echo $title; ?></h3>
                        </td>
                    </tr><?php
                        if (is_array($fields[$method])):
                            $i = 0;
                            foreach ($fields[$method] as $key => $val) {
                                if($key === 'order_comments') continue;
                                $label = isset($val['label']) ? $val['label'] : '';
                                $placeholder = isset($val['placeholder']) ? $val['placeholder'] : '';
                                $order = isset($val['order']) ? $val['order'] : $i;
                                $required = ! empty($val['required']);
                                $added = isset($val['added']) && $val['added'] === true ? 'on' : '';
                                $public = isset($val['public']) ? (bool)$val['public'] : true;
                                foreach(array('payment_method', 'shipping_method') as $at_method) {
                                    if (isset($fields[$method][$key][$at_method])) {
                                        $pm_k_remove = array();
                                        if (is_array($fields[$method][$key][$at_method]))
                                            foreach ($fields[$method][$key][$at_method] as $k => $v) {
                                                if ($v === '0') {
                                                    $pm_k_remove[] = $k;
                                                }
                                            }
                                        elseif ($fields[$method][$key][$at_method] === '0') {
                                            // $pm_k_remove[] = $k;
                                            unset($fields[$method][$key][$at_method]);
                                        }
    
                                        foreach ($pm_k_remove as $k_remove) {
                                            unset($fields[$method][$key][$at_method][$k_remove]);
                                        }
                                        if (isset($fields[$method][$key][$at_method])) $val[$at_method] = $fields[$method][$key][$at_method];
                                    }
                                }

                                $key_value = ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key;
                                // $key_value = $key;
                            ?>
                            <tr>
                                <td><input type="text" name="<?php echo $method; ?>[<?php echo esc_attr( $key_value); ?>][key]" disabled value="<?php echo esc_attr( $key_value ); ?>" /></td>
                                <td><input type="text" name="<?php echo $method; ?>[<?php echo esc_attr( $key_value); ?>][label]" value="<?php echo esc_attr($label); ?>" />
                                <div <?php if (isset($val['type']) && ($val['type'] == 'select' || $val['type'] == 'radio')) {
                                        echo ' class="option-area"';
                                    } else echo ' style="display: none;"';  ?> > <?php echo __('Options:', 'saphali-woocommerce-lite');  if (!isset($val['type']) || isset($val['type']) && $val['type'] != 'select' && $val['type'] != 'radio') { ?><input value='<?php echo esc_attr($placeholder); ?>' type="text" name="<?php echo $method; ?>[<?php echo esc_attr( $key_value) ?>][placeholder]" /><?php } else {
                if (isset($val['options']) && is_array($val['options'])) {
                    foreach ($val['options'] as $key_option => $val_option) { ?>
                                <span><br /><input class="options" type="text" name="<?php echo $method; ?>[<?php echo esc_attr( $key_value) ?>][options][<?php echo $key_option; ?>]" value="<?php echo $val_option ?>" /> <span class="delete-option" style="cursor:pointer;border:1px solid">Удалить</span></span>

                            <?php } ?>
                            <div class="button add_option" rel="<?php echo $key; ?>">Добавить еще</div>
                    <?php
                    }
                } ?>

                                </div>
                            </td>
                                <!-- <td><input type="text" name="<?php echo $method; ?>[<?php echo esc_attr( $key_value); ?>][placeholder]" value="<?php echo esc_attr($placeholder); ?>" /></td> -->
                               
                <!-- <td><input <?php //if (isset($val['clear']) && $val['clear']) echo 'checked' ?> class="<?php //echo isset($val['clear']) ? $val['clear'] : ''; ?>" type="checkbox" name="<?php //echo $method; ?>[<?php //echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key); ?>][clear]" /></td> 
                <td><?php if (isset($val['class']) && is_array($val['class'])) {
                        foreach ($val['class'] as $v_class) { ?>
                            <input value='<?php echo $v_class; ?>' type="text" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key) ?>][class][]" /> <?php }
                                                                                                                                        } else { ?>
                        <input value='' type="text" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key); ?>][class][]" /> <?php
                                                                                                                                        } ?>
                </td>-->
                <td>
                    <div class="type-element"><label for="type-element-<?php echo $key; ?>-select">Select</label><input <?php if (isset($val['type']) && $val['type'] == 'select') echo 'checked' ?> type="radio" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key) ?>][type]" value="select" id="type-element-<?php echo $key; ?>-select" /></div>
                    <div class="type-element"><label for="type-element-<?php echo $key; ?>-radio">Radio </label><input <?php if (isset($val['type']) && $val['type'] == 'radio') echo 'checked' ?> type="radio" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key) ?>][type]" value="radio" id="type-element-<?php echo $key; ?>-radio" /></div>
                    <div class="type-element"><label for="type-element-<?php echo $key; ?>-checkbox">Checkbox </label><input <?php if (isset($val['type']) && $val['type'] == 'checkbox') echo 'checked' ?> type="radio" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key) ?>][type]" value="checkbox" id="type-element-<?php echo $key; ?>-checkbox" /></div>
                    <div class="type-element"><label for="type-element-<?php echo $key; ?>-textarea">Textarea </label><input <?php if (isset($val['type']) && $val['type'] == 'textarea') echo 'checked' ?> type="radio" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key) ?>][type]" value="textarea" id="type-element-<?php echo $key; ?>-textarea" /></div>
                    <div class="type-element"><label for="type-element-<?php echo $key; ?>-text"><?php echo (!isset($val['type']) || $val['type'] === 'select' || $val['type'] === 'checkbox' || $val['type'] === ''|| $val['type'] === 'textarea') ? 'Text' : $val['type']; ?> </label><input <?php if (isset($val['type']) && !in_array($val['type'], array('select', 'radio', 'textarea', 'checkbox'))) echo 'checked' ?> type="radio" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key) ?>][type]" value="<?php if (isset($val['type']) && !in_array($val['type'], array('select', 'radio', 'textarea', 'checkbox'))) echo $val['type']; ?>" id="type-element-<?php echo $key; ?>-text" /> </div>
                    <?php if ($method !== 'account') { ?>
                    <div class="type-element"><label for="type-element-<?php echo $key; ?>-date">Date&time </label><input <?php if (isset($val['type']) && $val['type'] == 'date') echo 'checked' ?> type="radio" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key) ?>][type]" value="date" id="type-element-<?php echo $key; ?>-date" /></div>
                    <?php } ?>
                                                                                                                            
                    <input type="hidden" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key); ?>][order]" value="<?php echo intval($order); ?>" class="order_count" />
                </td>
                <!-- <td></td> -->
                <td>
                    <input type="checkbox" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key); ?>][required]" <?php checked($required); ?> />
                    <input type="hidden" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key); ?>][added]"  value="<?php echo $added; ?>" />
                </td>
                <td><input type="checkbox" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key); ?>][public]" <?php checked($public); ?> /></td>
                <td>
                <?php if ($method !== 'account') { ?>
                    <select multiple="multiple" width="120px" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key) ?>][payment_method][]">
                        <option value="0" <?php if (
                                                isset($val['payment_method']) && (
                                                    is_array($val['payment_method']) && in_array('0', (array)$val['payment_method']) ||
                                                    empty($val['payment_method'])) ||
                                                !isset($val['payment_method'])
                                            )
                                                echo 'selected'; ?>><?php _e('Все', 'saphali-woocommerce-lite') ?></option>
                        <?php
                        foreach (WC()->payment_gateways->payment_gateways() as $gateway) {
                            if ($gateway->enabled != 'yes') continue;
                        ?><option value="<?php echo $gateway->id; ?>" <?php if (isset($val['payment_method']) && is_array($val['payment_method']) && in_array($gateway->id, (array)$val['payment_method'])) echo 'selected'; ?>><?php echo $gateway->title; ?></option><?php } ?>
                    </select>
                    <?php } ?>
                </td>
                <td>
                <?php if ($method !== 'account') { ?>
                    <select multiple="multiple" width="120px" name="<?php echo $method; ?>[<?php echo esc_attr( ($default_field && strpos($key, "{$method}_") !== 0 ? "{$method}_" : '') . $key); ?>][shipping_method][]">
                        <option value="0" <?php if (isset($val['shipping_method']) && (is_array($val['shipping_method']) && in_array('0', (array)$val['shipping_method']) || empty($val['shipping_method'])) || !isset($val['shipping_method'])) echo 'selected'; ?>><?php _e('Все', 'saphali-woocommerce-lite') ?></option>
                        <?php
                        $no_add_option = true;
                        foreach (WC()->shipping->get_shipping_methods() as $act_id => $shipping) {
                            if ($shipping->enabled == 'no') continue;
                            if (in_array($act_id, (array)$val['shipping_method'])) $no_add_option = false;
                            ?><option value="<?php echo $act_id; ?>" <?php if (isset($val['shipping_method']) && is_array($val['shipping_method']) && in_array($act_id, (array)$val['shipping_method'])) echo 'selected'; ?>><?php echo $shipping->title ? $shipping->title : $shipping->method_title; ?></option><?php
                        }
    if ($no_add_option && !empty($val['shipping_method'])) {
        $value_shipping_method = is_array($val['shipping_method']) ? $val['shipping_method'][0]: $val['shipping_method'];
        ?><option value="<?php echo $value_shipping_method; ?>" <?php echo 'selected'; ?>><?php echo $value_shipping_method; ?></option> <?php } ?>
                    </select>
                    <?php } ?>
                            </td>
                            <td><button class="button remove-row">X</button></td>
                        </tr>
                        <?php
                            $i++;
                        }
                        ?>
                        <tr class="nodrop nodrag">
                            <td colspan="12">
                                <p><button type="button" class="button" id="add_<?php echo $method; ?>_field"><?php _e('+ Добавить поле', 'saphali-woocommerce-lite'); ?></button></p>
                            </td>
                        </tr>
                <?php
                    endif;
                }
                ?>
            </tbody>
        </table>
        <div class="other-options">
            <div class="option-item">
                <div for="position-order-fields" class="option-label"><?php _e('Order Fields', 'saphali-woocommerce-lite'); ?> </div>
                <div>
                     <label for="position-order-fields"><span class="option-description"><?php printf(__('Отображать поля "%s" в области "Контактная информация"', 'saphali-woocommerce-lite'), __('Order Fields', 'saphali-woocommerce-lite') ); ?></span></label>
                    <input type="checkbox" name="position-order-fields" id="position-order-fields" value="1" <?php checked($position); ?> />    
                </div>
                
            </div>
            <div class="option-item">
                <div for="onlydate-fields" class="option-label"><?php _e('Only Date', 'saphali-woocommerce-lite'); ?></div>
                <div>
                    <label for="onlydate-fields"><span class="option-description"><?php _e('Для полей с типом "Date&amp;time" выводить только календарь без возможности выбора времени', 'saphali-woocommerce-lite'); ?></span></label>
                    <input type="checkbox" name="onlydate-fields" id="onlydate-fields" value="1" <?php checked($onlydate); ?> />
                </div>
                
            </div>
        </div>
        <p class="bottom-block">
            <input type="submit" class="button-secondary" name="saphali_reset_fields" value="<?php _e('Сбросить поля', 'saphali-woocommerce-lite'); ?>" />
            <input type="submit" class="button-primary" name="saphali_save_fields" value="<?php _e('Сохранить поля', 'saphali-woocommerce-lite'); ?>" />
        </p>
    </form>
    <?php 
    } elseif( $_GET["tab"] == 1) { ?>
    <div class="clear"></div>
    <h2 class="woo-nav-tab-wrapper"><?php _e('Дополнительная информация', 'saphali-woocommerce-lite'); ?></h2>
    <?php include_once (SAPHALI_PLUGIN_DIR_PATH . 'go_pro.php');  } elseif($_GET["tab"] == 2) { ?>
    <div class="clear"></div>
    <h2 class="woo-nav-tab-wrapper"><?php _e('Число колонок в каталоге товаров и в рубриках', 'saphali-woocommerce-lite'); ?></h2>
    <?php include_once (SAPHALI_PLUGIN_DIR_PATH . 'count-column.php'); } ?>
</div>