<?php
/**
 * Alchemists SportsPress widgets
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.3.0
 * @version   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once ALCADVPOSTS_PLUGIN_DIR . '/sportspress/widgets/widget-alc-event-block.php';
include_once ALCADVPOSTS_PLUGIN_DIR . '/sportspress/widgets/widget-alc-game-result.php';
include_once ALCADVPOSTS_PLUGIN_DIR . '/sportspress/widgets/widget-alc-featured-player.php';

// Load the widget on widgets_init
function alc_sp_init_widgets() {
  register_widget( 'Alchemists_Widget_Event_Block' );
  register_widget( 'Alchemists_Widget_Event_Result' );
  register_widget( 'Alchemists_Widget_Featured_Player' );
}
add_action( 'widgets_init', 'alc_sp_init_widgets' );
