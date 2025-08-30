<?php
/**
 * Admin announcement meta box field view.
 *
 * @since 3.0.0
 */
?>

<?php
$post_id    = get_the_ID();
$check_mail = get_post_meta( $post_id, '_lp_learnpress_announcements_send_mail' );
if ( ! empty( $check_mail ) ) {
	$check_mail = $check_mail[0];
	if ( $check_mail === 'on' ) {
		$check_mail = ' checked="checked"';
	} else {
		$check_mail = '';
	}
} else {
	$check_mail = ' checked="checked"';
}
$check_discussion = get_post_meta( $post_id, '_lp_learnpress_announcements_display_discussion' );

if ( ! empty( $check_discussion ) ) {

	$check_discussion = $check_discussion[0];

	if ( $check_discussion === 'on' ) {
		$check_discussion = ' checked="checked"';
	} else {
		$check_discussion = '';
	}
} else {
	$check_discussion = ' checked="checked"';
}
?>

<div id="lp-course-<?php the_ID(); ?>" class="lp-wrap-list-announcements" data-id="<?php the_ID(); ?>">

    <div class="lp-form-add-announcement">

        <div class="lp-field-title">
            <input type="text" class="lp-title"
                   placeholder="<?php _e( 'Title Announcement', 'learnpress-announcements' ); ?>">
        </div>

        <div class="lp-field-content">
            <textarea class="lp-content"
                      placeholder="<?php _e( 'Content Announcement', 'learnpress-announcements' ); ?>"></textarea>
        </div>

        <div class="lp-list-course-select">
            <div class="lp-course-item-select lp-hidden">
                <a href="#"
                   title="<?php _e( 'Edit Course', 'learnpress-announcements' ); ?>"><?php _e( 'Course Title', 'learnpress-announcements' ); ?></a>
                <span class="lp-remove-course">×</span>
            </div>
        </div>
        <button class="button lp-select-courses" type="button" data-action="add-lp_courses" data-type="lp_courses">
			<?php _e( 'Multi-courses', 'learnpress-announcements' ); ?>
        </button>

        <div class="lp-field-send-mail">
            <input id="lp-send-mail" type="checkbox" class="lp-send-mail"
                   name="_lp_learnpress_announcements_send_mail" <?php echo $check_mail; ?>>
            <label for="lp-send-mail"><?php _e( 'Send email to students who were enrolled when create new an announcement', 'learnpress-announcements' ); ?></label>
        </div>

        <div class="lp-field-display-discusson">
            <input id="lp-display-comment" type="checkbox" class="lp-display-discussion"
                   name="_lp_learnpress_announcements_display_discussion" <?php echo $check_discussion; ?>>
            <label for="lp-display-comment"><?php _e( 'Display the comments on new announcement post', 'learnpress-announcements' ); ?></label>
        </div>

        <div class="lp-alert hidden">
            <span class="lp-closebtn">&times;</span>
			<?php _e( 'Something is wrong. Please fill up "Title" and "Content" fields!', 'learnpress-announcements' ); ?>
        </div>

        <div class="lp-field-btn clearfix">
            <button class="lp-add-announcement lp-button button"
                    data-nonce="<?php echo wp_create_nonce( 'lp-create-announcement' ) ?>"
                    data-no-title="<?php _e( 'No Title', 'learnpress-announcements' ); ?>"><?php _e( 'Post', 'learnpress-announcement' ); ?></button>
        </div>

    </div>

	<?php
	$arg_query = array(
		'post_type'      => LP_ANNOUNCEMENTS_CPT,
		'type'           => 'publish',
		'posts_per_page' => '-1',
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => '_lp_course_announcement',
				'value'   => get_the_ID(),
				'compare' => 'LIKE'
			),
		)
	);
	$query     = new WP_Query( $arg_query );
	?>
    <div class="item-bulk-actions">
        <div class="lp-button"></div>
		<?php if ( $query->have_posts() ) { ?>
            <span class="lp-check-items">
            <input class="lp-check-all-items" data-action="check-all" type="checkbox">
        </span>
		<?php } ?>
        <button class="button remove-items-announcements"
                data-title="<?php _e( 'Remove', 'learnpress-announcements' ); ?>"
                data-confirm="<?php _e( 'Are you sure you want to remove these items from announcement?', 'learnpress-announcements' ); ?>">
			<?php _e( 'Remove', 'learnpress-announcements' ); ?>
        </button>
    </div>

    <table class="list-announcements">
        <tbody>
		<?php
		RWMB_List_Announcements_Field::html_section_item();

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $current_post ) {
				RWMB_List_Announcements_Field::html_section_item( $current_post->ID );
			}
		}
		?>
        </tbody>
    </table>

	<?php
	if ( $query->have_posts() ) {
		$field_id = $field['id'] ? ' id="' . esc_attr( $field['id'] ) . '-description"' : '';
		echo $field['desc'] ? "<p{$field_id} class='description'>{$field['desc']}</p>" : '';
	}
	require_once( LP_ANNOUNCEMENTS_INC . 'admin/views/popup.php' );
	?>

</div>
