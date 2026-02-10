<?php 

$url_site = WC()->plugin_url();
$table_html = <<<EOE
<thead>
    <tr>
        <th width="130px">%s<img class="help_tip" data-tip="%s" src="{$url_site}/assets/images/help.png" /></th>
        <th width="130px">%s</th>
        <th width="130px">%s</th>
        <th width="35px">%s<img class="help_tip" data-tip="%s" src="{$url_site}/assets/images/help.png" /></th>
        <th width="130px">%s<img class="help_tip" data-tip="<h3 style='margin:0;padding:0'>%s</h3><ul style='text-align: left;'><li><span style='color: #000'>form-row-first</span>&nbsp;&ndash;&nbsp;%s;</li><li><span style='color: #000'>form-row-last</span>&nbsp;&ndash;&nbsp;%s.</li></ul><hr /><span style='color: #000'>%s</span>, %s" src="{$url_site}/assets/images/help.png" /></th>
        <th  width="40px">%s</th>
        <th  width="40px">%s</th>
        
        <th  width="40px">%s</th>
        <th  width="120px">%s</th>
        <th  width="120px">%s</th>
        <th width="65px">%s</th>
    </tr>
</thead>
<tfoot>
    <tr>
        <th>%s</th><!-- Название -->
        <th>%s</th>
        <th>%s</th>
        <th width="35px">%s<img class="help_tip" data-tip="%s" src="{$url_site}/assets/images/help.png" /></th>
        <th>%s</th>
        <th  width="40px">%s</th>
        <th  width="40px">%s</th>

        <th  width="40px">%s</th>
        <th  width="120px">%s</th>
        <th  width="120px">%s</th>
        <th>%s</th>
        </tr>
    </tfoot>
EOE;
$title_table = sprintf( $table_html, 
    __('Название', 'saphali-woocommerce-lite'),
    __('Название поля должно быть уни&shy;ка&shy;ль&shy;ным (не должно повторяться).', 'saphali-woocommerce-lite'),
    __('Заголовок', 'saphali-woocommerce-lite'),
    __('Текст в поле', 'saphali-woocommerce-lite'),
    __('Clear', 'saphali-woocommerce-lite'),
    __('Указывает на то, что следующее поле за текущим, будет начинаться с новой строки.', 'saphali-woocommerce-lite'),
    __('Класс поля', 'saphali-woocommerce-lite'),
    __('Задает стиль текущего поля', 'saphali-woocommerce-lite'),
    __('первый в строке', 'saphali-woocommerce-lite'),
    __('последний в строке', 'saphali-woocommerce-lite'),
    __('ЕСЛИ ОСТАВИТЬ ПУСТЫМ', 'saphali-woocommerce-lite'),
    __('то поле будет отображаться на всю ширину. Соответственно, в предыдущем поле (которое выше) нужно отметить &laquo;Clear&__(raquo;.', 'saphali-woocommerce-lite'),
    __('Тип поля', 'saphali-woocommerce-lite'),
    __('Обя&shy;за&shy;те&shy;ль&shy;ное', 'saphali-woocommerce-lite'),
    __('Опу&shy;бли&shy;ко&shy;вать', 'saphali-woocommerce-lite'),
    __('Метод оплаты', 'saphali-woocommerce-lite'),
    __('Метод доставки', 'saphali-woocommerce-lite'),
    __('Удалить/До&shy;ба&shy;вить', 'saphali-woocommerce-lite'),

    __('Название', 'saphali-woocommerce-lite'),
    __('Заголовок', 'saphali-woocommerce-lite'),
    __('Текст в поле', 'saphali-woocommerce-lite'),
    __('Clear', 'saphali-woocommerce-lite'),
    __('Указывает на то, что следующее поле за текущим, будет начинаться с новой строки.', 'saphali-woocommerce-lite'),
    __('Класс поля', 'saphali-woocommerce-lite'),
    __('Тип поля', 'saphali-woocommerce-lite'),
    __('Обя&shy;за&shy;те&shy;ль&shy;ное', 'saphali-woocommerce-lite'),
    __('Опу&shy;бли&shy;ко&shy;вать', 'saphali-woocommerce-lite'),
    __('Метод оплаты', 'saphali-woocommerce-lite'),
    __('Метод доставки', 'saphali-woocommerce-lite'),
    __('Удалить/До&shy;ба&shy;вить', 'saphali-woocommerce-lite')
);
$title_table_shipping = str_replace(
    array(
        sprintf('<th  width="40px">%s</th>', __('Тип поля', 'saphali-woocommerce-lite'))
    ), '', $title_table);
$title_table_order = str_replace(
    array(
        sprintf("<th width=\"35px\">%s<img class=\"help_tip\" data-tip=\"%s\" src=\"{$url_site}/assets/images/help.png\" /></th>", __('Clear', 'saphali-woocommerce-lite'), __('Указывает на то, что следующее поле за текущим, будет начинаться с новой строки.', 'saphali-woocommerce-lite')),
        sprintf('<th  width="40px">%s</th>', __('Обя&shy;за&shy;те&shy;ль&shy;ное', 'saphali-woocommerce-lite')),
        sprintf('<th  width="120px">%s</th>', __('Метод оплаты', 'saphali-woocommerce-lite')),
        sprintf('<th  width="120px">%s</th>', __('Метод доставки', 'saphali-woocommerce-lite')),
    ), '', $title_table);
?>

<div class="wrap woocommerce"><div class="icon32 icon32-woocommerce-reports" id="icon-woocommerce"><br /></div>
    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
    <?php _e('Настройки Saphali WC', 'saphali-woocommerce-lite'); ?>
    </h2>
    <ul class="subsubsub">
            <li><a href="admin.php?page=woocommerce_saphali_s_l" <?php if(empty($_GET["tab"])) echo 'class="current"';?> ><?php _e('Управление полями', 'saphali-woocommerce-lite'); ?></a> | </li>
            <li><a href="admin.php?page=woocommerce_saphali_s_l&tab=2" <?php if(!empty($_GET["tab"]) && $_GET["tab"] == 2) echo 'class="current"';?>><?php _e('Число колонок в каталоге', 'saphali-woocommerce-lite'); ?></a> | </li>
            <li><a href="admin.php?page=woocommerce_saphali_s_l&tab=1" <?php if(!empty($_GET["tab"]) && $_GET["tab"] == 1) echo 'class="current"';?>><span color="red"><?php _e('Дополнительная информация', 'saphali-woocommerce-lite'); ?></span></a></li>
    </ul>
    <?php if(empty($_GET["tab"])) {
        $this->locale_tmp = get_locale();
        if( !empty($this->locale_tmp) && $this->locale_tmp != 'en_US') {
            switch_to_locale('en_US');
        }
        global $woocommerce;
        remove_filter( 'woocommerce_checkout_fields' , 'woo_customize_checkout_fields' );
        remove_filter( 'woocommerce_checkout_fields', array($this,'saphali_custom_override_checkout_fields') );
        remove_filter( 'woocommerce_billing_fields', array($this,'saphali_custom_billing_fields'), 10, 1 );
        remove_filter( 'woocommerce_shipping_fields', array($this,'saphali_custom_shipping_fields'), 10, 1 );
        if ( empty( $woocommerce->checkout ) ) {
            
            if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) { 
                include_once( WP_PLUGIN_DIR . '/' . $woocommerce->template_url. 'classes/class-wc-checkout.php' ); 
            } elseif ( !version_compare( WOOCOMMERCE_VERSION, '2.3', '<' ) ) {
                include_once( WP_PLUGIN_DIR . '/' . str_replace( array('compatability/2.3/', 'compatibility/2.4/'), '', WC()->template_path() ) . 'includes/class-wc-autoloader.php' ); 
                $load = new WC_Autoloader();
                if(!class_exists('WC_Cart')) $load->autoload( 'WC_Cart' );if(!class_exists('WC_Customer')) $load->autoload( 'WC_Customer' );  $load->autoload( 'WC_Checkout' ); if ( !version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) { include_once( WP_PLUGIN_DIR . '/' . str_replace( array('compatability/2.3/', 'compatibility/2.4/'), '', WC()->template_path() ) . 'includes/abstracts/abstract-wc-session.php' ); include_once( WP_PLUGIN_DIR . '/' . str_replace( array('compatability/2.3/', 'compatibility/2.4/'), '', WC()->template_path() ) . 'includes/class-wc-session-handler.php' );  $woocommerce->session =  new WC_Session_Handler();} else {
                        $woocommerce->autoload( 'WC_Session' ); 
                        $woocommerce->autoload( 'WC_Session_Handler' ); 
                }  
            } else { 
                if(!class_exists('WC_Cart')) $woocommerce->autoload( 'WC_Cart' );if(!class_exists('WC_Customer')) $woocommerce->autoload( 'WC_Customer' );  $woocommerce->autoload( 'WC_Checkout' ); if ( !version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) { include_once( WP_PLUGIN_DIR . '/' . str_replace( array('compatability/2.2/','compatability/2.3/', 'compatibility/2.4/'), '', WC()->template_path() ) . 'includes/abstracts/abstract-wc-session.php' ); include_once( WP_PLUGIN_DIR . '/' . str_replace( array('compatability/2.2/','compatability/2.3/', 'compatibility/2.4/'), '', WC()->template_path() ) . 'includes/class-wc-session-handler.php' );  $woocommerce->session =  new WC_Session_Handler();} else {
                        $woocommerce->autoload( 'WC_Session' ); 
                        if ( !version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ))
                        $woocommerce->autoload( 'WC_Session_Handler' ); 
                }
            }
            if(class_exists('WC_Checkout')) {
                if(class_exists('WC_Customer')) $woocommerce->customer =  new WC_Customer();
                if(class_exists('WC_Cart')) $woocommerce->cart =  new WC_Cart();
                $f = new WC_Checkout();
            }
        } else	{
            $f = $woocommerce->checkout;
        }
        
        $global_f_checkout_fields = $f->checkout_fields;

        if( !empty($this->locale_tmp) && $this->locale_tmp != 'en_US') {
            switch_to_locale($this->locale_tmp);
        }
        if($_POST && wp_verify_nonce( $_POST['_wpnonce'], 'fields-nonce') ){
            if(@$_POST["reset"] != 'All') {
                // Управление новыми полями
                foreach( array('billing', 'shipping', 'order') as $method)
                if(@is_array($_POST[$method]["new_fild"])) {
                    foreach ($_POST[$method]["new_fild"] as $k_nf => $v_nf) {
                        if ($k_nf == 'name') continue;
                        if (is_array($v_nf))
                            foreach ($v_nf as $k_nf_f => $v_nf_f) {
                                $new_fild = isset($_POST[$method]["new_fild"]['name'][$k_nf_f]) ? $_POST[$method]["new_fild"]['name'][$k_nf_f] : $new_fild;
                                if ($k_nf == 'class') {
                                    $addFild[$method][$new_fild][$k_nf] = array($v_nf_f);
                                } elseif ($k_nf == 'options') {
                                    foreach ($v_nf_f as $val_v_nf_f) {
                                        $index = isset($addFild[$method][$new_fild][$k_nf])  ? (sizeof($addFild[$method][$new_fild][$k_nf]) + 1) : 1;
                                        $addFild[$method][$new_fild][$k_nf]['option-' . $index] = $val_v_nf_f;
                                    }
                                } elseif ($k_nf == 'type') {
                                    // echo '<pre>';var_dump($v_nf_f);echo '</pre>';
                                    $addFild[$method][$new_fild][$k_nf] = is_array($v_nf_f) ? current($v_nf_f) : $v_nf_f;
                                } else $addFild[$method][$new_fild][$k_nf] = $v_nf_f;
                            }
                    }
            
                    unset($new_fild, $_POST[$method]["new_fild"]);
                }
                //END 
                $filds = $global_f_checkout_fields;
                foreach( array('billing', 'shipping', 'order') as $method)
                if(is_array($filds[$method])) {
                if(!isset($addFild[$method]) || isset($addFild[$method]) && !is_array($addFild[$method])) $addFild[$method] = array();
                if( !isset($_POST[$method]) || isset($_POST[$method]) && !is_array($_POST[$method])) $_POST[$method] = array();
                $filds[$method] = array_merge($filds[$method] ,  $_POST[$method], $addFild[$method]);

                foreach($filds[$method] as $key_post => $value_post) {
                    
                    $filds[$method][$key_post]["label"] = str_replace(array('&#039;', "'"), '’', sanitize_text_field( wp_unslash ($filds[$method][$key_post]["label"]) ));
                    $value_post["label"] = str_replace(array('&#039;', "'"), '’', sanitize_text_field( wp_unslash ($value_post["label"])));
                    if(isset($filds[$method][$key_post]["placeholder"])) {
                        $filds[$method][$key_post]["placeholder"] = str_replace(array('&#039;', "'"), '’', sanitize_text_field( wp_unslash ($filds[$method][$key_post]["placeholder"]) ));
                        $value_post["placeholder"] = str_replace(array('&#039;', "'"), '’', sanitize_text_field( wp_unslash ($value_post["placeholder"])));    
                    }
                    
                    if( !isset($global_f_checkout_fields[$method][$key_post]['type']) &&  (isset($filds[$method][$key_post]['type']) && $filds[$method][$key_post]['type'] != 'select' && $filds[$method][$key_post]['type'] != 'radio' && $filds[$method][$key_post]['type'] != 'checkbox' && $filds[$method][$key_post]['type'] != 'textarea' || !isset($filds[$method][$key_post]['type']))  ) unset($filds[$method][$key_post]['type'],  $value_post["type"]);

                    if(!isset($filds[$method][$key_post]['public']) || isset($filds[$method][$key_post]['public']) && (string)$filds[$method][$key_post]['public'] !== 'on') {
                        if(isset($filds[$method][$key_post]["order"]))
                            $filds_new[$method][$filds[$method][$key_post]["order"]][$key_post]["public"] = false;
                        $fild_remove_filter[$method][] = $key_post;
                    } elseif(isset($filds[$method][$key_post]["order"])) {$filds_new[$method][$filds[$method][$key_post]["order"]][$key_post]["public"] = true;}

                    
                    foreach($value_post as $k_post=> $v_post){
                        if( 'on' == $v_post  ) {
                            $filds[$method][$key_post][$k_post] = true;
                            $value_post[$k_post] = true;
                        } elseif(in_array($k_post, array('public','clear','required'))) {  $filds[$method][$key_post][$k_post] = false; $value_post[$k_post] = false; if(!$filds[$method][$key_post][$k_post] && $k_post == 'public') unset($filds[$method][$key_post][$k_post]); }
                    }
                    if(isset($filds[$method][$key_post]["order"]))
                    $filds_new[$method][$filds[$method][$key_post]["order"]][$key_post] = $value_post;
                    
                    unset($_POST[$method][$key_post]);
                }

                }
                //END Управление публикацией
                $filds_finish["billing"] = $filds_finish["shipping"] = $filds_finish["order"] = array();

                for($i = 0; $i<count((array)$filds_new["billing"]); $i++) {
                    if(isset($filds_new["billing"][$i]))
                    $filds_finish["billing"] = $filds_finish["billing"] + $filds_new["billing"][$i];
                }
                for($i = 0; $i<count((array)$filds_new["shipping"]); $i++) {
                    if(isset($filds_new["shipping"][$i]))
                    $filds_finish["shipping"] = $filds_finish["shipping"] + $filds_new["shipping"][$i];
                }
                for($i = 0; $i<count((array)$filds_new["order"]); $i++) {
                    if(isset($filds_new["order"][$i]))
                    $filds_finish["order"] = $filds_finish["order"] + $filds_new["order"][$i];
                }

                $filds_finish_filter = $filds_finish;
                if(isset($fild_remove_filter["billing"]) && is_array($fild_remove_filter["billing"])) {
                    foreach($fild_remove_filter["billing"] as $v_filt){
                        unset($filds_finish_filter["billing"][$v_filt]);
                    }
                }
                if(isset($fild_remove_filter["shipping"]) && is_array($fild_remove_filter["shipping"])) {
                    foreach($fild_remove_filter["shipping"] as $v_filt){
                        unset($filds_finish_filter["shipping"][$v_filt]);
                    }
                }
                if(isset($fild_remove_filter["order"]) && is_array($fild_remove_filter["order"])) {
                    foreach($fild_remove_filter["order"] as $v_filt){
                        unset($filds_finish_filter["order"][$v_filt]);
                    }
                }
                update_option('woocommerce_saphali_filds',$filds_finish);
                update_option('woocommerce_saphali_filds_filters',$filds_finish_filter);
                foreach($filds_finish_filter['billing'] as $k_f => $v_f) {
                    $new_key = str_replace('billing_', '' , $k_f);
                    if(in_array($new_key, array('country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode' ) ))
                    $locate[$new_key] = $v_f;
                    elseif(in_array(str_replace('shipping_', '' , $k_f), array('country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode' ) )) {
                        $locate[$new_key] = $filds_finish_filter['shipping'][$k_f];
                    }
                }
                foreach ($filds_finish_filter['shipping'] as $k_f => $v_f) {
                    $new_key = str_replace('shipping_', '', $k_f);
                    if(isset($locate[$new_key])) continue;
                    if (in_array($new_key, array('country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode')))
                    $locate[$new_key] = $v_f;
                    elseif (in_array(str_replace('billing_', '', $k_f), array('country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode'))) {
                        $locate[$new_key] = $filds_finish_filter['billing'][$k_f];
                    }
                }
                if(!update_option('woocommerce_saphali_filds_locate',$locate))add_option('woocommerce_saphali_filds_locate',$locate);
            } else {
                    delete_option('woocommerce_saphali_filds');
                    delete_option('woocommerce_saphali_filds_filters'); 
                    delete_option('woocommerce_saphali_filds_locate'); 
                }
        }
        add_filter('woocommerce_pre_remove_cart_item_from_session', function() { return true; });
        $fss = [];
        $fss['woocommerce_saphali_filds'] = get_option('woocommerce_saphali_filds');
        $fss['woocommerce_saphali_filds_filters'] = get_option('woocommerce_saphali_filds_filters'); 
        $fss['woocommerce_saphali_filds_locate'] = get_option('woocommerce_saphali_filds_locate'); 
        // echo '<pre>'; var_dump(array_diff((array)$fss['woocommerce_saphali_filds'], $fss['woocommerce_saphali_filds_filters']), array_diff((array)$fss['woocommerce_saphali_filds_filters'], $fss['woocommerce_saphali_filds']));echo '</pre>'; 
    ?>
    <div class="clear"></div>
    <h3 class="nav-tab-wrapper woo-nav-tab-wrapper" style="text-align: center;"><?php _e('Управление полями на странице заказа и на странице профиля', 'saphali-woocommerce-lite'); ?></h3>
    <?php if($_POST && ( isset($_POST["reset"]) && $_POST["reset"] != 'All' || !isset($_POST["reset"]) )) {
    if(!wp_verify_nonce( $_POST['_wpnonce'], 'fields-nonce')) {
        ?><div class="error"><p><?php _e('Настройки не сохранены. Неуспешная верификация', 'saphali-woocommerce-lite'); ?></p></div><?php
    } else {
        ?><div class="updated"><p><?php _e('Настройки сохранены', 'saphali-woocommerce-lite'); ?></p></div><?php 
    } 
    } ?>
    <h2 align="center"><?php _e('Реквизиты оплаты', 'saphali-woocommerce-lite'); ?></h2>
    <form action="" method="post">
    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('fields-nonce'); ?>">
    <table class="wp-list-table widefat fixed posts" cellspacing="0">
    <?php echo $title_table; ?>
    <tbody id="the-list-billing" class="myTable">
        <?php 

        $count = 0;

        $checkout_fields = get_option('woocommerce_saphali_filds');
        
        if( isset($checkout_fields["billing"]) && is_array($checkout_fields["billing"])) $global_f_checkout_fields["billing"] = $checkout_fields["billing"];
        if( isset($global_f_checkout_fields["billing"]) )
        foreach($global_f_checkout_fields["billing"] as $key => $value) {
        
            $public = 'public';
            if( !version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) {
                if( isset( $checkout_fields["billing"][$key][$public] ) ) $value[$public] = $checkout_fields["billing"][$key][$public];
                elseif( isset( $checkout_fields["billing"][$key] ) ) {
                    $value[$public] = '';
                }
            }
            if(isset($checkout_fields["billing"][$key]['payment_method'])) {
                $pm_k_remove = array();
                if(is_array($checkout_fields["billing"][$key]['payment_method']))
                foreach($checkout_fields["billing"][$key]['payment_method'] as $k => $v) {
                    if($v === '0') {
                        $pm_k_remove[] = $k;
                    }
                } elseif($checkout_fields["billing"][$key]['payment_method'] === '0') {
                    // $pm_k_remove[] = $k;
                    unset($checkout_fields["billing"][$key]['payment_method']);
                }

                foreach($pm_k_remove as $k_remove) {
                    unset($checkout_fields["billing"][$key]['payment_method'][$k_remove]);
                }
                if( isset( $checkout_fields["billing"][$key]['payment_method'] ) ) $value['payment_method'] = $checkout_fields["billing"][$key]['payment_method'];
            }
            if(isset($checkout_fields["billing"][$key]['shipping_method'])) {
                $pm_k_remove = array();
                if(is_array($checkout_fields["billing"][$key]['shipping_method']))
                foreach($checkout_fields["billing"][$key]['shipping_method'] as $k => $v) {
                    if($v === '0') {
                        $pm_k_remove[] = $k;
                    }
                } elseif($checkout_fields["billing"][$key]['shipping_method'] === '0') {
                    // $pm_k_remove[] = $k;
                    unset($checkout_fields["billing"][$key]['shipping_method']);
                }
                
                foreach($pm_k_remove as $k_remove) {
                    unset($checkout_fields["billing"][$key]['shipping_method'][$k_remove]);
                }
                if( isset( $checkout_fields["billing"][$key]['shipping_method'] ) ) $value['shipping_method'] = $checkout_fields["billing"][$key]['shipping_method'];
            }
            
            if(empty($value[$public]) && 
                (isset($checkout_fields["billing"]) && !is_array($checkout_fields["billing"]) || !isset($checkout_fields["billing"]))) 
                $value[$public] = true;
            ?>
            <tr>
                <td> <input  disabled value='<?php echo $key?>' type="text" name="billing[<?php echo $key?>][name]" /></td>
                <td><input value='<?php echo isset($value['label']) ? esc_attr($value['label']) : ''; ?>' type="text" name="billing[<?php echo $key?>][label]" /></td>
            <td <?php if(isset($value['type']) && ($value['type'] == 'select' || $value['type'] == 'radio') ) {
                echo ' class="option-area"';}  ?>><?php if(!isset($value['type']) || isset($value['type']) && $value['type'] != 'select' && $value['type'] != 'radio') { ?><input value='<?php if(isset( $value['placeholder'] )) echo esc_attr($value['placeholder']); ?>' type="text" name="billing[<?php  echo $key?>][placeholder]" /><?php } else { 
                    if( isset($value['options']) && is_array($value['options']) ) {
                        foreach($value['options'] as $key_option => $val_option) {?>
                        <span><input class="options" type="text" name="billing[<?php echo $key?>][options][<?php echo $key_option; ?>]" value="<?php echo $val_option?>" /> <span class="delete-option" style="cursor:pointer;border:1px solid">Удалить</span></span><br />
                        
                    <?php } ?>
                    <div class="button add_option" rel="<?php echo $key; ?>">Добавить еще</div>
                    <?php
                    }
            
            } ?></td>
                <td><input <?php if(isset($value['clear']) && $value['clear']) echo 'checked'?>  class="<?php echo isset($value['clear']) ? $value['clear'] : '' ;?>" type="checkbox" name="billing[<?php echo $key?>][clear]" /></td>
                <td><?php  if(isset($value['class']) && is_array($value['class'])) { foreach($value['class'] as $v_class) { ?>
                <input value='<?php echo $v_class;?>' type="text" name="billing[<?php echo $key?>][class][]" /> <?php } } else { ?>
                <input value='' type="text" name="billing[<?php echo $key?>][class][]" /> <?php
                } ?></td>
            <td>
            Select <input <?php  if(isset($value['type']) && $value['type'] == 'select') echo 'checked'?> type="radio" name="billing[<?php  echo $key?>][type]" value="select" /><br />
            Radio <input <?php  if(isset($value['type']) && $value['type'] == 'radio') echo 'checked'?> type="radio" name="billing[<?php  echo $key?>][type]" value="radio" /><br />
            Checkbox <input <?php  if(isset($value['type']) && $value['type'] == 'checkbox') echo 'checked'?> type="radio" name="billing[<?php  echo $key?>][type]" value="checkbox"  /><br />
            Textarea <input <?php  if(isset($value['type']) && $value['type'] == 'textarea') echo 'checked'?> type="radio" name="billing[<?php  echo $key?>][type]" value="textarea"  /><br />
            <?php echo (!isset($value['type']) || $value['type'] == 'select'|| $value['type'] == 'checkbox'|| $value['type'] == 'textarea') ? 'Text' : $value['type']; ?> <input <?php  if(isset($value['type']) && $value['type'] == $value['type'] && !in_array( $value['type'], array('select', 'radio', 'textarea', 'checkbox') ) ) echo 'checked'?> type="radio" name="billing[<?php  echo $key?>][type]" value="<?php if( isset($value['type']) && !in_array( $value['type'], array('select', 'radio', 'textarea', 'checkbox') ) ) echo $value['type']; ?>"  />
            </td>
                <td><input <?php if( isset($value['required'] ) && $value['required']) echo 'checked'?> type="checkbox" name="billing[<?php echo $key?>][required]" /></td>
                <td><input <?php if(isset($value[$public]) && $value[$public]) echo 'checked';?> type="checkbox" name="billing[<?php echo $key?>][<?php echo $public; ?>]" /></td>
                <td>

                <select multiple="multiple" width="120px" name="billing[<?php echo $key?>][payment_method][]">
                    <option value="0"<?php if( isset($value['payment_method']) && ( 
                        is_array($value['payment_method']) && in_array('0', (array)$value['payment_method']) || 
                        empty($value['payment_method']) ) || 
                        !isset($value['payment_method']) ) 
                        echo 'selected';?>>Все</option>
                    <?php 
                        foreach ( $woocommerce->payment_gateways->payment_gateways() as $gateway ) {
                            if ( $gateway->enabled != 'yes' ) continue;
                            ?><option value="<?php echo $gateway->id; ?>" <?php if(isset($value['payment_method']) && is_array($value['payment_method']) && in_array($gateway->id, (array)$value['payment_method']) ) echo 'selected';?>><?php echo $gateway->title; ?></option><?php
                        } 
                    ?>
                </select>
                </td>
                <td>
                <select multiple="multiple" width="120px" name="billing[<?php echo $key?>][shipping_method][]">
                    <option value="0"<?php if( isset($value['shipping_method']) && ( is_array($value['shipping_method']) && in_array('0', (array)$value['shipping_method']) || empty($value['shipping_method']) ) || !isset($value['shipping_method']) ) echo 'selected';?>>Все</option>
                    <?php
                        $no_add_option = true;
                        foreach ( $woocommerce->shipping->get_shipping_methods() as $act_id => $shipping ) {
                            if ( $shipping->enabled == 'no' ) continue;
                            if( in_array($act_id, (array)$value['shipping_method']) ) $no_add_option = false;
                            ?><option value="<?php echo $act_id; ?>" <?php if(isset($value['shipping_method']) && is_array($value['shipping_method']) && in_array($act_id, (array)$value['shipping_method']) ) echo 'selected';?>><?php echo $shipping->title ? $shipping->title: $shipping->method_title; ?></option><?php
                        }
                        if($no_add_option && !empty($value['shipping_method'])) {
                            $value_shipping_method = is_array($value['shipping_method']) ? $value['shipping_method'][0] : $value['shipping_method'];
                            ?><option value="<?php echo $value_shipping_method; ?>" <?php echo 'selected';?>><?php echo $value_shipping_method; ?></option><?php
                        }
                    ?>
                </select>
                </td>
                <td><input rel="sort_order" id="order_count_billing_<?php echo $count; ?>" type="hidden" name="billing[<?php echo $key?>][order]" value="<?php echo $count?>" />
                <input type="button" class="button billing_delete" value="<?php _e('Удалить', 'saphali-woocommerce-lite'); ?> -"/></td>
            </tr>
            <?php $count++;
        }
        ?>
        <tr  class="nodrop nodrag">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td></td>
                <td></td>
                
                <td colspan="2"><input type="button" class="button"  id="billing" value="<?php _e('Добавить', 'saphali-woocommerce-lite'); ?> +"/></td>
        </tr>
    </tbody>
    </table>
        
    <h2 align="center"><?php _e('Реквизиты доставки', 'saphali-woocommerce-lite'); ?></h2>
    <table class="wp-list-table widefat fixed posts" cellspacing="0">
        <?php echo $title_table_shipping; ?>
    <tbody id="the-list-shipping" class="myTable">
        <?php $count = 0; 
        if(isset($checkout_fields["shipping"]) && is_array($checkout_fields["shipping"])) $global_f_checkout_fields["shipping"] = $checkout_fields["shipping"];
        if( isset( $global_f_checkout_fields["shipping"] ) )
        foreach($global_f_checkout_fields["shipping"] as $key => $value) {	
            $public = 'public';
            if( ! version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) {
                if( isset( $checkout_fields["shipping"][$key] ) && isset( $checkout_fields["shipping"][$key][$public] ) ) 
                    $value[$public] = $checkout_fields["shipping"][$key][$public];
            }
            if(isset($checkout_fields["shipping"][$key]['payment_method'])) {
                $pm_k_remove = array();
                if(is_array($checkout_fields["shipping"][$key]['payment_method']))
                foreach($checkout_fields["shipping"][$key]['payment_method'] as $k => $v) {
                    if($v === '0') {
                        $pm_k_remove[] = $k;
                    }
                } elseif($checkout_fields["shipping"][$key]['payment_method']=== '0') {
                    unset($checkout_fields["shipping"][$key]['payment_method']);
                }
                
                foreach($pm_k_remove as $k_remove) {
                    unset($checkout_fields["shipping"][$key]['payment_method'][$k_remove]);
                }
                if( isset( $checkout_fields["shipping"][$key] ) ) $value['payment_method'] = $checkout_fields["shipping"][$key]['payment_method'];
            }
            if(isset($checkout_fields["shipping"][$key]['shipping_method'])) {
                $pm_k_remove = array();
                if(is_array($checkout_fields["shipping"][$key]['shipping_method']))
                foreach($checkout_fields["shipping"][$key]['shipping_method'] as $k => $v) {
                    if($v === '0') {
                        $pm_k_remove[] = $k;
                    }
                } elseif($checkout_fields["shipping"][$key]['shipping_method'] === '0') {
                    unset($checkout_fields["shipping"][$key]['shipping_method']);
                }
                
                foreach($pm_k_remove as $k_remove) {
                    unset($checkout_fields["shipping"][$key]['shipping_method'][$k_remove]);
                }
                if( isset( $checkout_fields["shipping"][$key] ) ) $value['shipping_method'] = $checkout_fields["shipping"][$key]['shipping_method'];
            }
        if( empty($value['public']) && (isset($checkout_fields["shipping"]) && !is_array($checkout_fields["shipping"]) || !isset($checkout_fields["shipping"]))  ) $value['public'] = true;
            ?>
            <tr>
                <td><input  disabled  value=<?php echo $key?> type="text" name="shipping[<?php echo $key?>][name]" /></td>
                <td><input value='<?php echo isset($value['label']) ? esc_attr($value['label']): ''; ?>' type="text" name="shipping[<?php echo $key?>][label]" /><input value='<?php echo isset($value['type']) ? $value['type']: '' ?>' type="hidden" name="shipping[<?php echo $key?>][type]" /></td>
                <td><input value='<?php if(isset( $value['placeholder'] )) echo esc_attr($value['placeholder']); ?>' type="text" name="shipping[<?php echo $key?>][placeholder]" /></td>
                <td><input <?php if(isset($value['clear']) && $value['clear']) echo 'checked'?> class="<?php echo isset($value['clear'])? $value['clear'] : ''; ?>" type="checkbox" name="shipping[<?php echo $key?>][clear]" /></td>
                <td><?php  if( isset($value['class']) && is_array($value['class']) ) { foreach($value['class'] as $v_class) { ?>
                
                <input value='<?php echo $v_class;?>' type="text" name="shipping[<?php echo $key?>][class][]" /> <?php } } else { ?>
                <input value='' type="text" name="shipping[<?php echo $key?>][class][]" /> <?php
                } ?></td>
                <td><input <?php if(isset($value['required']) && $value['required']) echo 'checked'?> type="checkbox" name="shipping[<?php echo $key?>][required]" /></td>
                <td><input <?php if(isset($value['public']) && $value['public']) echo 'checked';?> type="checkbox" name="shipping[<?php echo $key?>][public]" /></td>
                <td>
                <select multiple="multiple" width="120px" name="shipping[<?php echo $key?>][payment_method][]">
                    <option value="0" <?php if( isset($value['payment_method']) && ( in_array('0', (array)$value['payment_method']) || empty($value['payment_method']) ) || !isset($value['payment_method']) ) echo 'selected';?>><?php _e('Все', 'saphali-woocommerce-lite'); ?></option>
                    <?php 
                        foreach ( $woocommerce->payment_gateways->payment_gateways() as $gateway ) {
                            if ( $gateway->enabled != 'yes' ) continue;
                            ?><option value="<?php echo $gateway->id; ?>" <?php if(isset($value['payment_method']) && in_array($gateway->id, (array)$value['payment_method']) ) echo 'selected';?>><?php echo $gateway->title; ?></option><?php
                        } 
                    ?>
                </select>
                </td><td>
                <select multiple="multiple" width="120px" name="shipping[<?php echo $key?>][shipping_method][]">
                    <option value="0" <?php if( isset($value['shipping_method']) && ( in_array('0', (array)$value['shipping_method']) || empty($value['shipping_method']) ) || !isset($value['shipping_method']) ) echo 'selected';?>><?php _e('Все', 'saphali-woocommerce-lite'); ?></option>
                    <?php 
                        foreach ( $woocommerce->shipping->get_shipping_methods() as $act_id => $shipping ) {
                            if ( $shipping->enabled == 'no' ) continue;
                            ?><option value="<?php echo $act_id; ?>" <?php if(isset($value['shipping_method']) && in_array($act_id, (array)$value['shipping_method']) ) echo 'selected';?>><?php echo $shipping->title ? $shipping->title: $shipping->method_title; ?></option><?php
                        } 
                    ?>
                </select>
                </td>
                
                <td><input rel="sort_order"  id="order_count_shipping_<?php echo $count; ?>" type="hidden" name="shipping[<?php echo $key?>][order]" value="<?php echo $count?>" /><input type="button" class="button billing_delete" value="<?php _e('Удалить', 'saphali-woocommerce-lite'); ?> -"/>
                    <?php 
                    if( isset($value['options']) && is_array($value['options']) ) {
                        foreach($value['options'] as  $key_option => $val_option) {?>
                        <input class="options" type="hidden" name="shipping[<?php echo $key?>][options][<?php echo $key_option; ?>]" value="<?php echo $val_option?>" />
                    <?php }
                    } ?>
                </td>
            </tr>
            <?php $count++;
        }
        ?>
        <tr  class="nodrop nodrag">
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td></td>
                <td></td>
                <td></td>
                <td colspan="2"><input type="button" class="button" id="shipping" value="<?php _e('Добавить', 'saphali-woocommerce-lite'); ?> +"/></td>
        </tr>
    
    </tbody>
    </table>		
<br />
<h2 align="center"><?php _e('Дополнительные поля', 'saphali-woocommerce-lite'); ?></h2>
    <table class="wp-list-table widefat fixed posts" cellspacing="0">
    <?php echo $title_table_order; ?>
    <tbody id="the-list" class="myTable">
        <?php $count = 0;
        if(isset($checkout_fields["order"]) && is_array($checkout_fields["order"])) $global_f_checkout_fields["order"] = $checkout_fields["order"];
        if(isset($global_f_checkout_fields["order"]) )
        foreach($global_f_checkout_fields["order"] as $key => $value) {
            $public = 'public';
            if( ! version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) {
                if( isset( $checkout_fields["order"][$key] ) ) $value[$public] = $checkout_fields["order"][$key][$public];
            }
            if(empty($value['public']) && (isset($checkout_fields["order"]) && !is_array($checkout_fields["order"]) || !isset($checkout_fields["order"]))) $value['public'] = true;
            ?>
            <tr>
                <td><input disabled value=<?php echo $key?> type="text" name="order[<?php echo $key?>][name]" /></td>
                <td><input value='<?php echo isset($value['label']) ? esc_attr($value['label']) : ''; ?>' type="text" name="order[<?php echo $key?>][label]" /></td>
                <td><input value='<?php echo isset($value['placeholder']) ? esc_attr($value['placeholder']): ''; ?>' type="text" name="order[<?php echo $key?>][placeholder]" /></td>
                
                <td><?php  if(isset($value['class']) && is_array($value['class'])) { foreach($value['class'] as $v_class) { ?>
                
                <input value='<?php echo $v_class;?>' type="text" name="order[<?php echo $key?>][class][]" /> <?php } } else { ?>
                <input value='' type="text" name="order[<?php echo $key?>][class][]" /> <?php
                } ?></td>
                <td><input value='<?php echo isset($value['type']) ? esc_attr($value['type']) : ''; ?>' type="text" name="order[<?php echo $key?>][type]" /></td>
                <td><input <?php if($value['public']) echo 'checked';?> type="checkbox" name="order[<?php echo $key?>][public]" /></td>
                
                <td><input id="order_count_<?php echo $count; ?>" rel="sort_order" type="hidden" name="order[<?php echo $key?>][order]" value="<?php echo $count?>" /><input type="button" class="button billing_delete" value="<?php _e('Удалить', 'saphali-woocommerce-lite'); ?> -"/>
                    <?php 
                    if( isset($value['options']) && is_array($value['options']) ) {
                        foreach($value['options'] as $key_option => $val_option) {?>
                        <input class="options" type="hidden" name="order[<?php echo $key?>][options][<?php echo $key_option; ?>]" value="<?php echo $val_option?>" />
                    <?php }
                    } ?>
                </td>
            </tr>
            <?php $count++;
        }
        ?>
        <tr  class="nodrop nodrag">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            
            <td><input type="button" class="button" id="order" value="<?php _e('Добавить', 'saphali-woocommerce-lite'); ?> +"/></td>
        </tr>
    </tbody>
    </table><br />
    <input type="submit" class="button alignleft" value="<?php _e('Save') ?>"/>
    </form>
    <form action="" method="post">
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('fields-nonce'); ?>">
        <input type="hidden" name="reset" value="All"/>
        <input type="submit" class="button alignright" value="<?php _e('Восстановить поля по умолчанию', 'saphali-woocommerce-lite'); ?>"/>
    </form>
 
    <script type="text/javascript">
    

    </script>
    <?php } elseif( $_GET["tab"] == 1) { ?>
    <div class="clear"></div>
    <h2 class="woo-nav-tab-wrapper"><?php _e('Дополнительная информация', 'saphali-woocommerce-lite'); ?></h2>
    <?php include_once (SAPHALI_PLUGIN_DIR_PATH . 'go_pro.php');  } elseif($_GET["tab"] == 2) { ?>
    <div class="clear"></div>
    <h2 class="woo-nav-tab-wrapper"><?php _e('Число колонок в каталоге товаров и в рубриках', 'saphali-woocommerce-lite'); ?></h2>
    <?php include_once (SAPHALI_PLUGIN_DIR_PATH . 'count-column.php'); } ?>
</div>