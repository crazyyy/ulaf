<?php
/**
 * Featured Player
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

class Alchemists_Widget_Featured_Player extends WP_Widget {


	/**
	 * Constructor.
	 *
	 * @access public
	 */
	function __construct() {

		$widget_ops = array(
			'classname' => 'widget-player-featured',
			'description' => esc_html__( 'A featured player.', 'alc-advanced-posts' ),
		);
		$control_ops = array(
			'id_base' => 'widget-player-featured'
		);

		parent::__construct( 'widget-player-featured', 'ALC: Featured Player', $widget_ops, $control_ops );

	}


	/**
	 * Outputs the widget content.
	 */

	function widget( $args, $instance ) {

		extract( $args );

		$id = empty($instance['id']) ? null : $instance['id'];

		$title              = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$caption            = empty($instance['caption']) ? null : $instance['caption'];
		$team               = empty($instance['team']) ? null : $instance['team'];
		$style_type         = empty($instance['style_type']) ? null : $instance['style_type'];
		$stat_type          = empty($instance['stat_type']) ? null : $instance['stat_type'];
		$columns            = empty( $instance['columns'] ) ? null : $instance['columns'];;
		$progress_bars      = empty( $instance['progress_bars'] ) ? null : $instance['progress_bars'];
		$performance        = empty( $instance['performance'] ) ? null : $instance['performance'];;
		$add_link           = isset( $instance['add_link'] ) ? true : false;


		echo wp_kses_post( $before_widget );

		if( $title ) {
			echo wp_kses_post( $before_title ) . esc_html( $title ) . wp_kses_post( $after_title );
		}

		// Include template from plugin
		include( ALCADVPOSTS_PLUGIN_DIR . '/sportspress/player-featured.php' );

		echo wp_kses_post( $after_widget );
	}

	/**
	 * Updates a particular instance of a widget.
	 */

	function update($new_instance, $old_instance) {

		$instance = $old_instance;

		$instance['title']                = strip_tags( $new_instance['title'] );
		$instance['caption']              = strip_tags($new_instance['caption']);
		$instance['id']                   = intval($new_instance['id']);
		$instance['team']                 = intval($new_instance['team']);
		$instance['style_type']           = $new_instance['style_type'];
		$instance['stat_type']            = $new_instance['stat_type'];
		$instance['columns']              = (array)$new_instance['columns'];
		$instance['progress_bars']        = (array)$new_instance['progress_bars'];
		$instance['performance']          = (array)$new_instance['performance'];
		$instance['add_link']             = $new_instance['add_link'];

		return $instance;
	}


	/**
	 * Outputs the settings update form.
	 */

	function form( $instance ) {

		$instance             = wp_parse_args( (array) $instance, array(
			'title'         => '',
			'id'            => null,
			'team'          =>'',
			'style_type'    => '',
			'stat_type'     => '',
			'caption'       => '',
			'columns'       => null,
			'progress_bars' => null,
			'performance'   => null,
			'add_link'      => ''
			)
		);

		$title                = strip_tags($instance['title']);
		$caption              = strip_tags($instance['caption']);
		$id                   = intval($instance['id']);
		$team                 = intval($instance['team']);
		$style_type           = $instance['style_type'];
		$stat_type            = $instance['stat_type'];
		$columns              = $instance['columns'];
		$progress_bars        = $instance['progress_bars'];
		$performance          = $instance['performance'];
		$add_link             = $instance['add_link'];
		?>

		<p><label for="<?php echo esc_attr( $this->get_field_id('caption') ); ?>"><?php esc_html_e( 'Title:', 'alc-advanced-posts' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('caption') ); ?>" name="<?php echo esc_attr( $this->get_field_name('caption') ); ?>" type="text" value="<?php echo esc_attr( $caption ); ?>" /></p>

		<p class="sp-dropdown-filter"><label for="<?php echo esc_attr( $this->get_field_id('team') ); ?>"><?php printf( esc_html__( 'Select %s:', 'alc-advanced-posts' ), esc_html__( 'Team', 'alc-advanced-posts' ) ); ?></label>
		<?php
		$args = array(
			'post_type'       => 'sp_team',
			'name'            => $this->get_field_name('team'),
			'id'              => $this->get_field_id('team'),
			'selected'        => $team,
			'show_option_all' => esc_html__( 'All', 'alc-advanced-posts' ),
			'values'          => 'ID',
			'class'           => 'widefat',
		);
		if ( ! sp_dropdown_pages( $args ) ):
			sp_post_adder( 'sp_team', esc_html__( 'Add New', 'alc-advanced-posts' ) );
		endif;
		?>
		</p>

		<p class="sp-dropdown-target"><label for="<?php echo esc_attr( $this->get_field_id('id') ); ?>"><?php printf( esc_html__( 'Select %s:', 'alc-advanced-posts' ), esc_html__( 'Player', 'alc-advanced-posts' ) ); ?></label>
		<?php
		$args = array(
			'post_type' => 'sp_player',
			'name'      => $this->get_field_name('id'),
			'id'        => $this->get_field_id('id'),
			'selected'  => $id,
			'values'    => 'ID',
			'class'     => 'widefat',
			'filter'    => 'sp_team',
		);
		if ( ! sp_dropdown_pages( $args ) ):
			sp_post_adder( 'sp_player', esc_html__( 'Add New', 'alc-advanced-posts' ) );
		endif;
		?>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'style_type' ) ); ?>"><?php esc_html_e( 'Select Style:', 'alc-advanced-posts' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'style_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'style_type' ) ); ?>" class="widefat">
				<option value="style_type1" <?php echo ( 'style_type1' == $instance['style_type'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Style 1', 'alc-advanced-posts' ); ?></option>
				<option value="style_type2" <?php echo ( 'style_type2' == $instance['style_type'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Style 2', 'alc-advanced-posts' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'stat_type' ) ); ?>"><?php esc_html_e( 'Statistics Type:', 'alc-advanced-posts' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'stat_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'stat_type' ) ); ?>" class="widefat">
				<option value="stat_compact" <?php echo ( 'stat_compact' == $instance['stat_type'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Compact', 'alc-advanced-posts' ); ?></option>
				<option value="stat_extended" <?php echo ( 'stat_extended' == $instance['stat_type'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Extended', 'alc-advanced-posts' ); ?></option>
			</select>
		</p>

		<p class="sp-prefs">
			<?php esc_html_e( 'Statistics:', 'alc-advanced-posts' ); ?><br>
			<?php

			$args = array(
				'post_type' => array( 'sp_statistic' ),
				'numberposts' => -1,
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			);
			$the_columns = get_posts( $args );

			$field_name = $this->get_field_name('columns') . '[]';
			$field_id = $this->get_field_id('columns');
			?>
			<?php foreach ( $the_columns as $column ): ?>
				<label class="button"><input name="<?php echo esc_attr( $field_name ); ?>" type="checkbox" id="<?php echo esc_attr( $field_id ) . '-' . $column->post_name; ?>" value="<?php echo esc_attr( $column->post_name ); ?>" <?php if ( $columns === null || in_array( $column->post_name, $columns ) ): ?>checked="checked"<?php endif; ?>><?php echo esc_html( $column->post_title ); ?></label>
			<?php endforeach; ?>
		</p>
		<p>
			<em><?php esc_html_e( 'Note: Select no more than three items.', 'alc-advanced-posts' ); ?></em>
		</p>

		<p class="sp-prefs">
			<?php esc_html_e( 'Progress Bars:', 'alc-advanced-posts' ); ?><br>
			<?php

			$args = array(
				'post_type'      => array( 'sp_statistic' ),
				'numberposts'    => -1,
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'meta_key'       => 'sp_type',
				'meta_value'     => 'average'
			);
			$the_progress_bars = get_posts( $args );

			$field_name = $this->get_field_name('progress_bars') . '[]';
			$field_id = $this->get_field_id('progress_bars');
			?>
			<?php foreach ( $the_progress_bars as $progress_bar ): ?>
				<label class="button"><input name="<?php echo esc_attr( $field_name ); ?>" type="checkbox" id="<?php echo esc_attr( $field_id ) . '-' . $progress_bar->post_name; ?>" value="<?php echo esc_attr( $progress_bar->post_name ); ?>" <?php if ( $progress_bars === null || in_array( $progress_bar->post_name, $progress_bars ) ): ?>checked="checked"<?php endif; ?>><?php echo esc_html( $progress_bar->post_title ); ?></label>
			<?php endforeach; ?>
		</p>
		<p>
			<em><?php esc_html_e( 'Note: Select only percent based stat, and no more than 2 or 3 items.', 'alc-advanced-posts' ); ?></em>
		</p>

		<p class="sp-prefs">
			<?php esc_html_e( 'Performance:', 'alc-advanced-posts' ); ?><br>
			<?php

			$args = array(
				'post_type' => array( 'sp_performance' ),
				'numberposts' => -1,
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			);
			$the_performance = get_posts( $args );

			$field_name = $this->get_field_name('performance') . '[]';
			$field_id = $this->get_field_id('performance');
			?>
			<?php foreach ( $the_performance as $performance_single ): ?>
				<label class="button"><input name="<?php echo esc_attr( $field_name ); ?>" type="checkbox" id="<?php echo esc_attr( $field_id ) . '-' . $performance_single->post_name; ?>" value="<?php echo esc_attr( $performance_single->post_name ); ?>" <?php if ( $performance === null || in_array( $performance_single->post_name, $performance ) ): ?>checked="checked"<?php endif; ?>><?php echo esc_html( $performance_single->post_title ); ?></label>
			<?php endforeach; ?>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['add_link'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'add_link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'add_link' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'add_link' ) ); ?>"><?php esc_attr_e( 'Add Link to Player Page?', 'alc-advanced-posts' ); ?></label>
		</p>

		<?php

	}
}
