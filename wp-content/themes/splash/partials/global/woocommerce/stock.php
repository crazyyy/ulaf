<?php
global $product;

$status = $product->get_availability();
$status = ucfirst(str_replace('-', ' ', $status['class']));
?>
<div class="stm-stock-wrapper">
    <div class="stm-stock"><?php echo esc_html($status) ; ?></div>
</div>