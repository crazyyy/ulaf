<?php
if(get_theme_mod('mega_menu', true)){
    if(is_admin()) {
        require_once($splash_inc_path . '/megamenu/admin/includes/xteam/xteam.php');
        require_once($splash_inc_path . '/megamenu/admin/includes/config.php');
        require_once($splash_inc_path . '/megamenu/admin/includes/enqueue.php');
        require_once($splash_inc_path . '/megamenu/admin/includes/fontawesome.php');
    } else {
        require_once($splash_inc_path . '/megamenu/includes/walker.php');
        require_once($splash_inc_path . '/megamenu/includes/enqueue.php');
    }
}