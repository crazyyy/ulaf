<?php
/**
 * Event Block Custom
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.3.0
 * @version   2.3.0
 */


// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}


/**
 * Widget class.
 */

class Alchemists_Widget_Event_Block extends WP_Widget {


	/**
	 * Constructor.
	 *
	 * @access public
	 */
	function __construct() {

		$widget_ops = array(
			'classname' => 'widget-results',
			'description' => esc_html__( 'A list of events.', 'alc-advanced-posts' ),
		);
		$control_ops = array(
			'id_base' => 'widget-results'
		);

		parent::__construct( 'widget-results', 'ALC: Event Blocks - Small', $widget_ops, $control_ops );

	}


	/**
	 * Outputs the widget content.
	 */

	function widget( $args, $instance ) {

		extract( $args );

		$id = empty($instance['id']) ? null : $instance['id'];

		$title                = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$caption              = empty($instance['caption']) ? null : $instance['caption'];
		$status               = empty($instance['status']) ? 'default' : $instance['status'];
		$date                 = empty($instance['date']) ? 'default' : $instance['date'];
		$date_from            = empty($instance['date_from']) ? 'default' : $instance['date_from'];
		$date_to              = empty($instance['date_to']) ? 'default' : $instance['date_to'];
		$day                  = empty($instance['day']) ? 'default' : $instance['day'];
		$number               = empty($instance['number']) ? null : $instance['number'];
		$order                = empty($instance['order']) ? 'default' : $instance['order'];
		$show_all_events_link = empty($instance['show_all_events_link']) ? false : $instance['show_all_events_link'];


		echo wp_kses_post( $before_widget );

		if( $title ) {
			echo wp_kses_post( $before_title ) . esc_html( $title ) . wp_kses_post( $after_title );
		}

		sp_get_template( 'event-blocks-custom.php', array( 'id' => $id, 'title' => $caption, 'status' => $status, 'date' => $date, 'date_from' => $date_from, 'date_to' => $date_to, 'day' => $day, 'number' => $number, 'order' => $order, 'show_all_events_link' => $show_all_events_link ) );


		echo wp_kses_post( $after_widget );
	}

	/**
	 * Updates a particular instance of a widget.
	 */

	function update($new_instance, $old_instance) {

		$instance = $old_instance;

		$instance['title']                = strip_tags( $new_instance['title'] );
		$instance['id']                   = intval($new_instance['id']);
		$instance['caption']              = strip_tags($new_instance['caption']);
		$instance['status']               = $new_instance['status'];
		$instance['date']                 = $new_instance['date'];
		$instance['date_from']            = $new_instance['date_from'];
		$instance['date_to']              = $new_instance['date_to'];
		$instance['day']                  = $new_instance['day'];
		$instance['number']               = intval($new_instance['number']);
		$instance['order']                = strip_tags($new_instance['order']);
		$instance['show_all_events_link'] = $new_instance['show_all_events_link'];

		return $instance;
	}


	/**
	 * Outputs the settings update form.
	 */

	function form( $instance ) {

		$instance             = wp_parse_args( (array) $instance, array( 'title' => '', 'id' => null, 'caption' => '', 'status' => 'default', 'date' => 'default', 'date_from' => date_i18n( 'Y-m-d' ), 'date_to' => date_i18n( 'Y-m-d' ), 'day' => '', 'number' => 5, 'order' => 'default', 'show_all_events_link' => true ) );
		$title                = strip_tags($instance['title']);
		$id                   = intval($instance['id']);
		$caption              = strip_tags($instance['caption']);
		$status               = $instance['status'];
		$date                 = $instance['date'];
		$date_from            = $instance['date_from'];
		$date_to              = $instance['date_to'];
		$day                  = $instance['day'];
		$number               = intval($instance['number']);
		$order                = strip_tags($instance['order']);
		$show_all_events_link = $instance['show_all_events_link'];
		?>

		<p><label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e( 'Title:', 'alc-advanced-posts' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo esc_attr( $this->get_field_id('caption') ); ?>"><?php esc_html_e( 'Heading:', 'alc-advanced-posts' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('caption') ); ?>" name="<?php echo esc_attr( $this->get_field_name('caption') ); ?>" type="text" value="<?php echo esc_attr($caption); ?>" /></p>

		<p><label for="<?php echo esc_attr( $this->get_field_id('id') ); ?>"><?php printf( esc_html__( 'Select %s:', 'alc-advanced-posts' ), esc_html__( 'Calendar', 'alc-advanced-posts' ) ); ?></label>
		<?php
		$args = array(
			'post_type'       => 'sp_calendar',
			'show_option_all' => esc_html__( 'All', 'alc-advanced-posts' ),
			'name'            => $this->get_field_name('id'),
			'id'              => $this->get_field_id('id'),
			'selected'        => $id,
			'values'          => 'ID',
			'class'           => 'sp-event-calendar-select widefat',
		);
		if ( ! sp_dropdown_pages( $args ) ):
			sp_post_adder( 'sp_calendar', esc_html__( 'Add New', 'alc-advanced-posts' ) );
		endif;
		?>
		</p>

		<p><label for="<?php echo esc_attr( $this->get_field_id('status') ); ?>"><?php esc_html_e( 'Status:', 'alc-advanced-posts' ); ?></label>
			<?php
			$args = array(
				'show_option_default' => esc_html__( 'Default', 'alc-advanced-posts' ),
				'name' => $this->get_field_name('status'),
				'id' => $this->get_field_id('status'),
				'selected' => $status,
				'class' => 'sp-event-status-select widefat',
			);
			sp_dropdown_statuses( $args );
			?>
		</p>

		<div class="sp-date-selector">
			<p><label for="<?php echo esc_attr( $this->get_field_id('date') ); ?>"><?php esc_html_e( 'Date:', 'alc-advanced-posts' ); ?></label>
				<?php
				$args = array(
					'show_option_default' => esc_html__( 'Default', 'alc-advanced-posts' ),
					'name' => $this->get_field_name('date'),
					'id' => $this->get_field_id('date'),
					'selected' => $date,
					'class' => 'sp-event-date-select widefat',
				);
				sp_dropdown_dates( $args );
				?>
			</p>
			<p class="sp-date-range<?php if ( 'range' !== $date ): ?> hidden<?php endif; ?>">
				<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'date_from' ) ); ?>" value="<?php echo esc_attr( $date_from ); ?>" placeholder="yyyy-mm-dd" size="10">
				:
				<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'date_to' ) ); ?>" value="<?php echo esc_attr( $date_to ); ?>" placeholder="yyyy-mm-dd" size="10">
			</p>
		</div>

		<p><label for="<?php echo esc_attr( $this->get_field_id('day') ); ?>"><?php esc_html_e( 'Match Day:', 'alc-advanced-posts' ); ?></label>
		<input id="<?php echo esc_attr( $this->get_field_id('day') ); ?>" name="<?php echo esc_attr( $this->get_field_name('day') ); ?>" type="text" placeholder="<?php esc_attr_e( 'All', 'alc-advanced-posts' ); ?>" value="<?php echo esc_attr( $day ); ?>" size="10"></p>

		<p><label for="<?php echo esc_attr( $this->get_field_id('number') ); ?>"><?php esc_html_e( 'Number of events to show:', 'alc-advanced-posts' ); ?></label>
		<input id="<?php echo esc_attr( $this->get_field_id('number') ); ?>" name="<?php echo esc_attr( $this->get_field_name('number') ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3"></p>

		<p><label for="<?php echo esc_attr( $this->get_field_id('order') ); ?>"><?php esc_html_e( 'Sort Order:', 'alc-advanced-posts' ); ?></label>
		<select name="<?php echo esc_attr( $this->get_field_name('order') ); ?>" id="<?php echo esc_attr( $this->get_field_id('order') ); ?>" class="sp-select-order widefat">
			<option value="default" <?php selected( 'default', $order ); ?>><?php esc_html_e( 'Default', 'alc-advanced-posts' ); ?></option>
			<option value="ASC" <?php selected( 'ASC', $order ); ?>><?php esc_html_e( 'Ascending', 'alc-advanced-posts' ); ?></option>
			<option value="DESC" <?php selected( 'DESC', $order ); ?>><?php esc_html_e( 'Descending', 'alc-advanced-posts' ); ?></option>
		</select></p>

		<p class="sp-event-calendar-show-all-toggle<?php if ( ! $id ): ?> hidden<?php endif; ?>"><input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id('show_all_events_link') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_all_events_link') ); ?>" value="1" <?php checked( $show_all_events_link, 1 ); ?>>
		<label for="<?php echo esc_attr( $this->get_field_id('show_all_events_link') ); ?>"><?php esc_html_e( 'Display link to view all events', 'alc-advanced-posts' ); ?></label></p>

		<?php

	}
}
