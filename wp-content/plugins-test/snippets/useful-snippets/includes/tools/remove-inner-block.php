<?php 
/**
 * @since 1.2.0
 */

remove_filter( 'render_block_core/group', 'wp_restore_group_inner_container', 10, 2 );