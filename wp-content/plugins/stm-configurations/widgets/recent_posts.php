<?php

class Stm_Recent_Posts extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'stm_recent_posts', // Base ID
			esc_html__('STM Recent posts', 'splash'), // Name
			array( 'description' => esc_html__( 'Theme recent posts widget', 'splash' ), ) // Args
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
		$output = apply_filters( 'widget_output', $instance['output'] );

		if(empty($output) or !isset($output)) {
			$output = 3;
		};

		echo wp_kses_post($args['before_widget']);
		if ( ! empty( $title ) ) {
			echo wp_kses_post($args['before_title']) . esc_html( $title ) . wp_kses_post($args['after_title']);
		}

		$query = new WP_Query( array( 'post_type' => 'post', 'ignore_sticky_posts' => 1, 'posts_per_page' => $output ) );

		if($query->have_posts()): ?>
			<?php while($query->have_posts()): $query->the_post(); ?>
				<div class="widget_media clearfix">
					<a href="<?php the_permalink() ?>">
                        <?php if(!splash_is_layout('magazine_one') && !splash_is_layout('hockey')) :?>
                            <?php if(has_post_thumbnail()): ?>
                                <div class="image">
                                    <?php the_post_thumbnail('thumbnail', array('class'=>'img-responsive')); ?>
                                </div>
                            <?php endif; ?>
                            <div class="stm-post-content">
                                <div class="date <?php echo (splash_is_layout("bb")) ? "heading-font" : "normal_font"; ?>">
                                    <?php echo esc_attr(get_the_date()); ?>
                                </div>
                                <span class="h5"><?php the_title(); ?></span>
                            </div>

                        <?php elseif(splash_is_layout('hockey')) :?>
                            <?php if(has_post_thumbnail()): ?>
                                <div class="image">
                                    <?php the_post_thumbnail('thumbnail', array('class'=>'img-responsive')); ?>
                                </div>
                            <?php endif; ?>
                            <div class="stm-post-content">
                                <span class="h5"><?php the_title(); ?></span>
                                <div class="date normal_font">
                                    <?php echo esc_attr(get_the_date()); ?>
                                </div>
                            </div>

                        <?php else : ?>
                            <div class="stm-post-content">
                                <span class="h5"><?php the_title(); ?></span>
                                <div class="date normal_font">
                                    <?php echo esc_attr(get_the_date()); ?>
                                </div>
                            </div>
                        <?php endif; ?>
					</a>
				</div>
				<div class="clearfix"></div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		<?php endif;

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
		$output = '';

		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}else {
			$title = esc_html__( 'Recent posts', 'splash' );
		}

		if ( isset( $instance[ 'output' ] ) ) {
			$output = $instance[ 'output' ];
		}else {
			$output = esc_html__( '3', 'splash' );
		}

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'splash' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'output' ) ); ?>"><?php esc_html_e( 'Output number:', 'splash' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'output' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'output' ) ); ?>" type="number" value="<?php echo esc_attr( $output ); ?>">
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
		$instance['output'] = ( ! empty( $new_instance['output'] ) ) ? esc_attr( $new_instance['output'] ) : '';

		return $instance;
	}

}

function register_stm_recent_posts_widget() {
	register_widget( 'Stm_Recent_Posts' );
}
add_action( 'widgets_init', 'register_stm_recent_posts_widget' );