<?php
/**
 * Template for displaying Start quiz button.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/assignments/content-assignment/buttons/controls.php.
 *
 * @author  ThimPress
 * @package  Learnpress/Assignments/Templates
 * @version  3.0.0
 */
/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

$course              = LP_Global::course();
$current_assignment       = LP_Global::course_item();
$user                = learn_press_get_current_user();
$current_useritem_id = learn_press_get_user_item_id( $user->get_id(), $current_assignment->get_id(), $course->get_id() );
if( ! $current_useritem_id ){
	$course_data  = $user->get_course_data( $course->get_id() );
	$current_useritem_id = $course_data->get_item( $current_assignment->get_id() )->get_user_item_id();
}
$content             = learn_press_get_user_item_meta( $current_useritem_id, '_lp_assignment_answer_note', true );

$uploaded_files  = learn_press_assignment_get_uploaded_files( $current_useritem_id );
$file_extensions = $current_assignment->get_file_extension();
$file_accept     = '.' . str_replace( ',', ',.', $file_extensions );
$file_amount     = $current_assignment->get_files_amount();
$disable         = '';
$uploaded_files_amount = ( $uploaded_files ) ? count($uploaded_files) : 0;
if( $file_amount - $uploaded_files_amount < 1 ){
    $disable = 'disabled="true"';
}
?>

<?php do_action( 'learn-press/before-assignment-controls-button' ); ?>

<form name="save-assignment" class="save-assignment" method="post" enctype="multipart/form-data">

    <h3><?php _e( 'Answer:', 'learnpress-assignments' ); ?></h3>

	<?php wp_editor( $content, 'assignment-editor-frontend', array( 'media_buttons' => false, ) ); ?>

	<?php if ( $file_amount != 0 ) : ?>
        <input name="_lp_upload_file[]" <?php echo esc_attr($disable); ?> class="form-control" accept="<?php echo esc_attr( $file_accept ); ?>"
               id="_lp_upload_file" type="file" multiple="true"/>
        <span class="assignments-notice-filetype">
		<?php
		echo '( ' . __( 'Maximum amount of files you can upload more: ', 'learnpress-assignments' ) . '<strong id="assignment-file-amount-allow">' . ( $file_amount - $uploaded_files_amount ) . '</strong>.';
        if ( strpos( '*', $file_extensions ) === false ) { ?>
            <?php echo __( ' And allow upload only these types: ', 'learnpress-assignments' ) . $file_extensions; ?>
		<?php
        }
        echo ' )';
		?>
        </span>
	<?php endif; ?>
	<?php if ( $uploaded_files_amount ): ?>
        <h3 class="assignment-uploaded-files"><?php _e( 'Your Uploaded File(s):', 'learnpress-assignments' ); ?></h3>
        <div class="learn-press-assignment-uploaded">
            <ul class="assignment-files assignment-uploaded">
				<?php foreach ( $uploaded_files as $key_file=>$file ) {
                    $filetype = $file['type'];
                    $mime = 'file';
                    if($filetype !=''){
                        $mime = preg_replace('#[^\/]*\/#', '', $filetype);
                    }
                    ?>
                    <li class="assignmen-file" id="assignment-uploaded-file-<?php echo esc_attr($key_file); ?>">
                        <div class="assignment_file_thumb assignment_thumb_error">
                            <div class="assignment_file_dummy ui-widget-content">
                                <span class="ui-state-disabled"><?php echo $mime; ?>
                                    <span class="assignment-file-size"><?php echo '( ' . learn_press_assignment_filesize_format( $file['size'] ) . ' )'; ?></span>
                                </span>
                            </div>
                        </div>
                        <div class="assignment_file_name" title="<?php echo $file['filename']; ?>">
                            <span class="assignment_file_name_wrapper">
                                <a href="<?php echo get_site_url() . $file['url']; ?>"
                                   target="_blank"><?php echo $file['filename']; ?>
                                </a>
                            </span>
                        </div>
                        <div class="assignment_file_action">
                            <a href="#" data-confirm="<?php esc_attr_e( 'Do you want to remove this file?', 'learnpress-assignments' ); ?>" useritem_id="<?php echo esc_attr($current_useritem_id); ?>" ajax_url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" title="remove" name="<?php echo esc_attr($file['file']); ?>" order="<?php echo esc_attr($key_file); ?>" class="assignment_action_icon"></a>
                            <input type="hidden" name="assignment-nonce" id="assignment-file-nonce-<?php echo $key_file; ?>" value="<?php echo wp_create_nonce( 'delete_assignment_upload_file_' . $key_file ); ?>" />
                        </div>
                        <div class="assignment_file_uploaded_time"><?php if ( $file['saved_time'] ) { ?>
                                <span class="saved-time"><?php echo '( ' . $file['saved_time'] . ' )'; ?></span>
	                        <?php } ?>
                        </div>
                    </li>
				<?php } ?>
            </ul>
        </div>
	<?php endif; ?>

    <div class="assignment-buttons-area">
        <?php do_action( 'learn-press/begin-assignment-save-button' ); ?>
        <button id="assignment-button-left"
                data-confirm="<?php esc_attr_e( 'Do you want to save the result? Your uploaded files will be replaced by the new ones if any!', 'learnpress-assignments' ); ?>"
                type="submit"
                name="controls-button" value="Save"
                class="button assignment-button-left"><?php _e( 'Save', 'learnpress-assignments' ); ?></button>
        <?php do_action( 'learn-press/end-assignment-save-button' ); ?>

        <?php do_action( 'learn-press/begin-assignment-send-button' ); ?>
        <button id="assignment-button-right"
                data-confirm="<?php esc_attr_e( 'Do you want to send the result to instructor? Is this your final answer?', 'learnpress-assignments' ); ?>"
                type="submit"
                name="controls-button" value="Send"
                class="button assignment-button-right"><?php _e( 'Send', 'learnpress-assignments' ); ?></button>
        <?php do_action( 'learn-press/end-assignment-send-button' ); ?>
    </div>

	<?php assignment_action( 'controls', $current_assignment->get_id(), $course->get_id(), true ); ?>
    <input type="hidden" name="noajax" value="yes">

</form>

<?php do_action( 'learn-press/after-assignment-controls-button' ); ?>
