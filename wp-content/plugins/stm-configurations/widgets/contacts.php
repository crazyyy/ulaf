<?php

class Stm_Contacts_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'contacts', // Base ID
			esc_html__('Contacts', 'splash'), // Name
			array( 'description' => esc_html__( 'Contacts widget', 'splash' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title = (!empty($instance['title'])) ? apply_filters( 'widget_title', $instance['title'] ) : '';

		echo wp_kses_post($args['before_widget']);

		if (get_theme_mod('footer_style', '') != 'footer_style_two' && !empty($title)) {
			echo wp_kses_post($args['before_title']) . esc_html($title) . wp_kses_post($args['after_title']);
		}

		if(get_theme_mod('footer_style', '') == 'footer_style_two' && get_theme_mod("footer_logo", '') != ''){
			echo '<div class="footer-logo-wrapp">
				<img src="' . esc_url(get_theme_mod('footer_logo', '')) . '" />
			</div>';
		}

		$wrap =  "ul";
		$item = "li";
		$itemAttr = "";
		$itemWrappOpen = "";
		$itemWrappClose = "";
		$fst_class = "";


		if((get_theme_mod('footer_style', '') == 'footer_style_two')){
			$wrap = "table";
			$item = "td";
			$itemAttr = "colspan='2'";
			$itemWrappOpen = "<tr>";
			$itemWrappClose = "</tr>";
			$fst_class = "fs_two_contacts";
		}

		$cl = (splash_is_layout("bb")) ? "heading-font " : "normal_font ";
		
        echo '<' . $wrap . ' class="stm-list-duty ' . $cl . esc_attr($fst_class) . '">';
		if(!empty($instance['address'])){
			echo $itemWrappOpen;
			echo '<' . $item . ' class="widget_contacts_address" ' . $itemAttr . '><div class="icon"><i class="fa fa-map-marker"></i></div><div class="text">' . html_entity_decode( $instance['address'] ) . '</div></' . $item . '>';
			echo $itemWrappClose;
		}

		if(!empty($instance['phone'])){
			echo $itemWrappOpen;
			echo '<' . $item . '  class="widget_contacts_phone"><div class="icon"><i class="fa fa-phone"></i></div><div class="text">' . html_entity_decode( $instance['phone'] ) . '</div></' . $item . '>';
		}

		if(!empty($instance['fax'])){
			echo '<' . $item . ' class="widget_contacts_fax"><div class="icon"><i class="fa fa-fax"></i></div><div class="text">' . html_entity_decode( $instance['fax'] ) . '</div></' . $item . '>';
			echo $itemWrappClose;
		}
		
		if(!empty($instance['skype'])){
			echo '<' . $item . ' class="widget_contacts_skype"><div class="icon"><i class="fa fa-skype"></i></div><div class="text">' . html_entity_decode( $instance['skype'] ) . '</div></' . $item . '>';
			echo $itemWrappClose;
		}

		if(!empty($instance['email'])){
			echo $itemWrappOpen;
			echo '<' . $item . ' class="widget_contacts_mail"><div class="icon"><i class="fa fa-envelope"></i></div><div class="text"><a href="mailto:'.sanitize_email( $instance['email'] ).'">'.sanitize_email( $instance['email'] ).'</a></div></' . $item . '>';
		}

		if(!empty($instance['schedule'])){
			echo '<' . $item . ' class="widget_contacts_schedule"><div class="icon"><i class="fa fa-clock-o"></i></div><div class="text">' . html_entity_decode( $instance['schedule'] ) . '</div></' . $item . '>';
			echo $itemWrappClose;
		}
		
        echo '</' . $wrap . '>';

		echo wp_kses_post($args['after_widget']);
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$title = '';
		$address = '';
		$phone = '';
		$fax = '';
		$email = '';
		$schedule = '';
		$skype = '';
		
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}else {
			$title = esc_html__( 'Contact', 'splash' );
		}

		if ( isset( $instance[ 'address' ] ) ) {
			$address = $instance[ 'address' ];
		}

		if ( isset( $instance[ 'phone' ] ) ) {
			$phone = $instance[ 'phone' ];
		}

		if ( isset( $instance[ 'fax' ] ) ) {
			$fax = $instance[ 'fax' ];
		}
		
		if ( isset( $instance[ 'skype' ] ) ) {
			$skype = $instance[ 'skype' ];
		}

		if ( isset( $instance[ 'schedule' ] ) ) {
			$schedule = $instance[ 'schedule' ];
		}

		if ( isset( $instance[ 'email' ] ) ) {
			$email = $instance[ 'email' ];
		}

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'splash' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'address' ) ); ?>"><?php esc_html_e( 'Address:', 'splash' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'address' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'address' ) ); ?>" type="text" value="<?php echo esc_attr( $address ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'phone' ) ); ?>"><?php esc_html_e( 'Phone:', 'splash' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'phone' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'phone' ) ); ?>" type="text" value="<?php echo esc_attr( $phone ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'fax' ) ); ?>"><?php esc_html_e( 'Fax:', 'splash' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'fax' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'fax' ) ); ?>" type="text" value="<?php echo esc_attr( $fax ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'schedule' ) ); ?>"><?php esc_html_e( 'Schedule:', 'splash' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'schedule' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'schedule' ) ); ?>" type="text" value="<?php echo esc_attr( $schedule ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'skype' ) ); ?>"><?php esc_html_e( 'Skype:', 'splash' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'skype' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'skype' ) ); ?>" type="text" value="<?php echo esc_attr( $skype ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'email' ) ); ?>"><?php esc_html_e( 'E-mail:', 'splash' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'email' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'email' ) ); ?>" type="text" value="<?php echo sanitize_email( $email ); ?>">
		</p>
	<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? esc_attr( $new_instance['title'] ) : '';
		$instance['address'] = ( ! empty( $new_instance['address'] ) ) ? esc_attr( $new_instance['address'] ) : '';
		$instance['phone'] = ( ! empty( $new_instance['phone'] ) ) ? esc_attr( $new_instance['phone'] ) : '';
		$instance['fax'] = ( ! empty( $new_instance['fax'] ) ) ? esc_attr( $new_instance['fax'] ) : '';
		$instance['skype'] = ( ! empty( $new_instance['skype'] ) ) ? esc_attr( $new_instance['skype'] ) : '';
		$instance['schedule'] = ( ! empty( $new_instance['schedule'] ) ) ? esc_attr( $new_instance['schedule'] ) : '';
		$instance['email'] = ( ! empty( $new_instance['email'] ) ) ? sanitize_email( $new_instance['email'] ) : '';

		return $instance;
	}

}

function register_contacts_widget() {
	register_widget( 'Stm_Contacts_Widget' );
}
add_action( 'widgets_init', 'register_contacts_widget' );