<?php
/**
 * Template for displaying students manager of assignment on FrontEnd Editor.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/assignments/frontend-editor/manager-link.php.
 *
 * @author   ThimPress
 * @package  Learnpress/Assignments/Templates
 * @version  3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();?>
<span class="manager-link" v-if="item.type=='lp_assignment'">
    <a :href="'<?php esc_attr_e($page_url); ?>'+'?assignment_id='+item.id" target="_blank" title="<?php esc_attr_e('Students Manager', 'learnpress-assignments')?>"><i class="dashicons dashicons-tickets"></i></a>
</span>