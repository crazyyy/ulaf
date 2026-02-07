<?php
$icon = get_theme_mod("top_bar_ticket_icon");
$text_one = get_theme_mod("top_bar_ticket_text_first");
$text_two = get_theme_mod("top_bar_ticket_text_second");
?>

<div class="stm-head-ticket">
    <img class="stm-ticket-icon" src="<?php echo esc_html($icon); ?>" />
    <ul>
        <li><span class="stm-ticket-text"><?php echo wp_kses($text_one, array('a' => array( 'href' => array(), 'title' => array() ))) ; ?></span></li>
        <li><span class="stm-divider"></span></li>
        <li><span class="stm-ticket-text"><?php echo wp_kses($text_two, array('a' => array( 'href' => array(), 'title' => array() ))); ?></span></li>
    </ul>
</div>
