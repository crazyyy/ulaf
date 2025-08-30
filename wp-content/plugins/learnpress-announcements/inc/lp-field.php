<?php

/**
 * Class LP Announcement field class.
 *
 * @author   ThimPress
 * @package  LearnPress/Announcements/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_List_Announcements_Field' ) ) {
	/**
	 * Class RWMB_List_Announcements_Field.
	 */
	class RWMB_List_Announcements_Field extends RWMB_Field {

		/**
		 * Admin enqueue scripts.
		 */
		static function admin_enqueue_scripts() {
			parent::admin_enqueue_scripts();

			if ( ! wp_script_is( 'lp_announcements', 'enqueued' ) ) {
				wp_enqueue_style( 'lp_announcements', LP_ADDON_ANNOUNCEMENTS_URI . 'assets/css/admin.announcements.css', array(), LP_ADDON_ANNOUNCEMENTS_VER );
				wp_enqueue_script( 'lp_announcements', LP_ADDON_ANNOUNCEMENTS_URI . 'assets/js/admin.announcements.js', array( 'jquery' ), LP_ADDON_ANNOUNCEMENTS_VER, true );
			}
		}

		/**
		 * Add actions.
		 */
		static function add_actions() {
			// Do same actions as file field
			parent::add_actions();
		}

		/**
		 * HTML.
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function html( $meta, $field ) {
			ob_start();

			require_once( LP_ANNOUNCEMENTS_INC . 'admin/views/meta-box-field.php' );
			?>
            <input type="hidden" class="rwmb-text" name="<?php echo esc_attr( $field['field_name'] ); ?>"
                   id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $meta ); ?>">
			<?php
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}

		/**
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		public static function end_html( $meta, $field ) {
			return '';
		}

		/**
		 * @param string $post_id
		 */
		public static function html_section_item( $post_id = '' ) {
			$arr_attrs = array();
			$edit      = '#';
			$title     = '';
			$date      = '';
			if ( ! empty( $post_id ) ) {
				$arr_attrs[] = 'id="' . esc_attr( $post_id ) . '"';
				$arr_attrs[] = 'class="lp_announcement-item"';
				$arr_attrs[] = 'data-id="' . esc_attr( $post_id ) . '"';
				$edit        = esc_url( get_edit_post_link( $post_id ) );
				$title       = get_the_title( $post_id );

				$current_time = current_time( 'timestamp' );
				$post_time    = get_the_time( 'U', $post_id );

				if ( ( $current_time - $post_time ) < DAY_IN_SECONDS ) {
					$date = human_time_diff( $post_time, $current_time ) . __( ' ago', 'learnpress-announcement' );
				} else {
					$date = get_the_date( '', $post_id );
				}

			} else {
				$arr_attrs[] = 'class="lp_announcement-item lp-hidden"';
			}

			if ( $title == '' ) {
				$title = __( 'No Title', 'learnpress-announcements' );
			}
			?>
            <tr <?php echo implode( ' ', $arr_attrs ); ?>>

                <td class="section-item-icon">
                    <a href="<?php echo $edit; ?>" target="_blank"><span class="learn-press-icon"></span></a>
                </td>

                <td class="section-item-input">
                    <input type="text" class="lp-item-name" readonly="readonly"
                           value="<?php echo esc_attr( $title ); ?>" title="<?php echo esc_attr( $title ); ?>">
                </td>

                <td class="section-item-date">
                    <span class="lp-date"><?php echo $date; ?></span>
                </td>

                <td class="section-item-actions">
                    <p class="lp-item-actions lp-button-actions">
                        <a href="#" class="lp-item-action lp-send dashicons dashicons-email-alt"
                           data-confirm="<?php _e( 'Are you want to send mail for all user?', 'learnpress-announcements' ); ?>"
                           title="<?php _e( 'Send Mail', 'learnpress-announcements' ); ?>"></a>
                        <a href="<?php echo $edit; ?>" class="lp-item-action lp-edit dashicons dashicons-edit"
                           target="_blank" title="<?php _e( 'Edit Announcement', 'learnpress-announcements' ); ?>"></a>
                        <a href="#" class="lp-item-action lp-remove dashicons dashicons-trash"
                           data-confirm-remove="<?php _e( 'Are you sure you want to remove this item?', 'learpress-announcements' ); ?>"
                           title="<?php _e( 'Remove Announcement', 'learnpress-announcements' ); ?>"></a>
                        <span class="item-checkbox">
                            <input type="checkbox">
                        </span>
                    </p>
                </td>
            </tr>
			<?php
		}

		/**
		 * @param mixed $new
		 * @param mixed $old
		 * @param int $post_id
		 * @param array $field
		 */
		static function save( $new, $old, $post_id, $field ) {
			parent::save( $new, $old, $post_id, $field );

			/* Save Send Mail Meta */
			if ( isset( $_POST['_lp_learnpress_announcements_send_mail'] ) && $_POST['_lp_learnpress_announcements_send_mail'] === 'on' ) {
				update_post_meta( $post_id, '_lp_learnpress_announcements_send_mail', 'on' );
			} else {
				update_post_meta( $post_id, '_lp_learnpress_announcements_send_mail', 'off' );
			}

			/* Save Display Comment Meta */
			if ( isset( $_POST['_lp_learnpress_announcements_display_discussion'] ) && $_POST['_lp_learnpress_announcements_display_discussion'] === 'on' ) {
				update_post_meta( $post_id, '_lp_learnpress_announcements_display_discussion', 'on' );
			} else {
				update_post_meta( $post_id, '_lp_learnpress_announcements_display_discussion', 'off' );
			}
		}
	}
}
