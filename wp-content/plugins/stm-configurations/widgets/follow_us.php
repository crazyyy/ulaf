<?php

class Stm_Follow_Us_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'follow_us', // Base ID
			__('Follow Us', 'splash'), // Name
			array( 'description' => __( 'Follow Us widget', 'splash' ), ) // Args
		);
	}

	private function socials(){
		$socials['facebook'] = '';
		$socials['twitter'] = '';
		$socials['linkedin'] = '';
		$socials['instagram'] = '';
		$socials['rss'] = '';
		$socials['youtube'] = '';
		$socials['pinterest'] = '';
		$socials['dribbble'] = '';
		$socials['google-plus'] = '';
		$socials['skype'] = '';

		return $socials;
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
		$socials = self::socials();
		$title = (!empty($instance['title'])) ? apply_filters( 'widget_title', $instance['title'] ) : '';

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		$html = '<ul class="clearfix">';

		foreach($socials as $key => $val){
			if(isset($instance[$key]) && $instance[$key] != ''){
				$url = $instance[$key];
				$target = '_blank';
				if($key == 'skype'){
					$url = 'skype:'.$instance[$key].'?chat';
					$target = '_self';
				}
				if($key == 'youtube'){
					$key = 'youtube-play';
				}
				if($key == 'pinterest'){
					$key = 'pinterest-p';
				}
				
				$html .= '<li class="'.esc_attr( $key ).'">';
				$html .= '<a target="'.esc_attr( $target ).'" href="'.esc_attr( $url ).'"><i class="fa fa-'.esc_attr( $key) .'"></i></a>';
				$html .= '</li>';
			}
		}

		$html .= '</ul>';
		echo balanceTags( $html );
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$socials = self::socials();

		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}else {
			$title = __( 'Follow Us', 'splash' );
		}

		foreach($socials as $soc => $val){
			if ( !empty( $instance[$soc] ) ) {
				$socials[$soc] = $instance[$soc];
			}
		}

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'splash' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php foreach($socials as $key => $val){ ?>
			<p>
				<label style="text-transform: capitalize;" for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $key.':' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" placeholder="<?php if($val == ''){ echo 'http://www.example.com'; } ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="text" value="<?php echo esc_attr( $val ); ?>">
			</p>
		<?php } ?>
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
		$socials = self::socials();
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? esc_attr( $new_instance['title'] ) : '';
		foreach($socials as $key => $val){
			$instance[$key] = ( ! empty( $new_instance[$key] ) ) ? esc_attr( $new_instance[$key] ) : '';
		}

		return $instance;
	}

}

function register_follow_us_widget() {
	register_widget( 'Stm_Follow_Us_Widget' );
}
add_action( 'widgets_init', 'register_follow_us_widget' );