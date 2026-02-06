<?php
/**
 * Shortcode attributes
 * @var $atts
 * @var $caption
 * @var $calendar
 * @var $number
 * @var $status
 * @var $order
 * @var $show_all_events_link
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Event_Blocks_Sm
 */

$caption = $calendar = $number = $status = $order = $show_all_events_link = '';

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

// Hide Show All Events button if all calendars displayed
if ( $calendar == 'all' ) {
  $show_all_events_link = 0;
}

sp_get_template( 'event-blocks-custom.php', array(
  'id'                   => $calendar,
  'title'                => $caption,
  'status'               => $status,
  'date'                 => 'default',
  'date_from'            => 'default',
  'date_to'              => 'default',
  'day'                  => 'default',
  'number'               => $number,
  'order'                => $order,
  'show_all_events_link' => $show_all_events_link
));
