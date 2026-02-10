<?php
/**
 * WP Mail log class - captures the requests and fulfills the log table with the results.
 *
 * @package 0-day-analytics
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace ADVAN\Controllers;

use ADVAN\Entities\WP_Mail_Entity;
use ADVAN\Helpers\Plugin_Theme_Helper;
use ADVAN\Helpers\Settings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Controllers\WP_Mail_Log' ) ) {
	/**
	 * Responsible for collecting the emails.
	 *
	 * @since 3.0.0
	 */
	class WP_Mail_Log {

		/**
		 * Class cache for the type of the mail message.
		 *
		 * @var integer
		 *
		 * @since 3.0.0
		 */
		private static $is_html = 0;

		/**
		 * Class cache for the last inserted mail log ID.
		 *
		 * @var integer
		 *
		 * @since 3.0.0
		 */
		private static $last_id = 0;

		/**
		 * Class cache for the BB mail.
		 *
		 * @var array
		 *
		 * @since 3.6.1
		 */
		private static $bp_mail = null;

		/**
		 * Inits the class.
		 *
		 * @return void
		 *
		 * @since 3.0.0
		 */
		public static function init() {
			if ( Settings::get_option( 'wp_mail_module_enabled' ) ) {
				\add_filter( 'wp_mail', array( __CLASS__, 'record_mail' ), PHP_INT_MAX );
				\add_action( 'wp_mail_failed', array( __CLASS__, 'record_error' ), PHP_INT_MAX, 2 );
				\add_filter( 'wp_mail_content_type', array( __CLASS__, 'save_is_html' ), PHP_INT_MAX );

				\add_filter( 'phpmailer_init', array( __CLASS__, 'extract_more_mail_info' ), \PHP_INT_MAX );

				\add_action( 'bp_send_email', array( __CLASS__, 'bp_record_mail' ), PHP_INT_MAX );

				\add_filter( 'bp_email_use_wp_mail', array( __CLASS__, 'should_use_wp_mail' ), PHP_INT_MAX );

				\add_action( 'bp_send_email_failure', array( __CLASS__, 'record_error' ), PHP_INT_MAX, 2 );

				// Post SMTP plugin compatibility - capture "From" address from Post SMTP.
				\add_action( 'post_smtp_on_success', array( __CLASS__, 'post_smtp_extract_from' ), PHP_INT_MAX, 4 );
				\add_action( 'post_smtp_on_failed', array( __CLASS__, 'post_smtp_extract_from' ), PHP_INT_MAX, 4 );
			}
		}

		/**
		 * Filter that check if the default wp_mail should be used and not the BuddyPress one - if the default is in use, just clear the vars and this will cover the rest using the core filters, if not - do the initial message store
		 *
		 * @param bool $default - True if the default must be used, false otherwise.
		 *
		 * @return bool
		 *
		 * @since 3.6.1
		 */
		public static function should_use_wp_mail( $default ) {
			if ( $default ) {
				self::$bp_mail = null;
				self::$is_html = 0;
			} else {
				self::$last_id = WP_Mail_Entity::insert( self::$bp_mail );
			}

			return $default;
		}

		/**
		 * Records the mail from BuddyPress in the class cache var for later check. If the mail from BP falls back to the core WP method, that get cleared, if not - stored in the DB
		 *
		 * @param \stdClass $email_class - The mail class from the BuddyPress plugin.
		 *
		 * @return void
		 *
		 * @since 3.6.1
		 */
		public static function bp_record_mail( $email_class ) {
			if ( isset( $email_class ) && \is_object( $email_class ) && ! empty( $email_class ) ) {
				$to = $email_class->get( 'to' );
				$to = array_shift( $to )->get_address();

				$message = '';

				if ( 'html' === $email_class->get( 'content_type' ) ) {
					self::$is_html = 1;

					$message = $email_class->get( 'content_html', 'replace-tokens' );
				} else {
					self::$is_html = 0;

					$message = $email_class->get( 'content_plaintext', 'replace-tokens' );
				}
				$bt_segment  = self::get_backtrace();
				$plugin_slug = '';
				if ( isset( $bt_segment['file'] ) ) {
					$plugin_base = Plugin_Theme_Helper::get_plugin_from_file_path( $bt_segment['file'] );
					if ( $plugin_base ) {
						$plugin_slug = $plugin_base;
					}
				}

				// Extract CC, BCC, Reply-To from BuddyPress headers.
				$headers          = $email_class->get( 'headers' );
				$extra_recipients = self::extract_extra_recipients( is_array( $headers ) ? $headers : array() );

				// Calculate message size.
				$message_size = strlen( $message );

				// Categorize the email.
				$email_category = self::categorize_email( $email_class->get( 'subject', 'replace-tokens' ), $message, $bt_segment );

				// Determine if email can be resent.
				$can_resend = self::can_resend_email( $email_class->get( 'subject', 'replace-tokens' ), $message ) ? 1 : 0;

				self::$bp_mail = array(
					'time'                  => time(),
					'email_to'              => self::filter_html( (string) $to ),
					'subject'               => self::filter_html( $email_class->get( 'subject', 'replace-tokens' ) ),
					'message'               => self::filter_html( $message ),
					'backtrace_segment'     => \wp_json_encode( self::get_backtrace() ),
					'plugin_slug'           => $plugin_slug,
					'status'                => 1,
					'attachments'           => \wp_json_encode( self::get_attachment_locations( array() ) ),
					'additional_headers'    => \wp_json_encode( $email_class->get( 'headers' ) ),
					'is_html'               => (int) self::$is_html,
					'blog_id'               => (int) \get_current_blog_id(),
					'email_cc'              => $extra_recipients['cc'],
					'email_bcc'             => $extra_recipients['bcc'],
					'email_reply_to'        => $extra_recipients['reply_to'],
					'message_size'          => $message_size,
					'total_size'            => $message_size, // BuddyPress emails typically have no attachments.
					'attachment_count'      => 0,
					'attachment_total_size' => 0,
					'delivery_time'         => null,
					'email_category'        => $email_category,
					'can_resend'            => $can_resend,
				);
			}
		}

		/**
		 * Extracts all of the mail information and stores it in the DB
		 *
		 * @param array $args - Array with all of the mail arguments.
		 *
		 * @return void
		 *
		 * @since 3.0.0
		 */
		public static function record_mail( $args ) {

			if ( \is_array( $args ) ) {
				$start_time = microtime( true );

				$bt_segment  = self::get_backtrace();
				$plugin_slug = '';
				if ( isset( $bt_segment['file'] ) ) {
					$plugin_base = Plugin_Theme_Helper::get_plugin_from_file_path( $bt_segment['file'] );
					if ( $plugin_base ) {
						$plugin_slug = $plugin_base;
					}
				}

				// Extract CC, BCC, Reply-To from headers.
				$extra_recipients = self::extract_extra_recipients( $args['headers'] );

				// Get attachment information.
				$attachments_data = self::get_attachment_locations( $args['attachments'] );
				$attachment_stats = self::calculate_attachment_stats( $attachments_data );

				// Calculate message size.
				$message_size = strlen( $args['message'] );

				// Calculate total size (message + attachments).
				$total_size = $message_size + $attachment_stats['total_size'];

				// Categorize the email.
				$email_category = self::categorize_email( $args['subject'], $args['message'], $bt_segment );

				// Determine if email can be resent.
				$can_resend = self::can_resend_email( $args['subject'], $args['message'] ) ? 1 : 0;

				$log_entry = array(
					'time'                  => time(),
					'email_to'              => self::filter_html( self::array_to_string( $args['to'] ) ),
					'subject'               => self::filter_html( $args['subject'] ),
					'message'               => self::filter_html( $args['message'] ),
					'backtrace_segment'     => \wp_json_encode( self::get_backtrace() ),
					'plugin_slug'           => $plugin_slug,
					'status'                => 1,
					'attachments'           => \wp_json_encode( $attachments_data ),
					'additional_headers'    => \wp_json_encode( $args['headers'] ),
					'is_html'               => (int) self::$is_html,
					'blog_id'               => (int) \get_current_blog_id(),
					'email_cc'              => $extra_recipients['cc'],
					'email_bcc'             => $extra_recipients['bcc'],
					'email_reply_to'        => $extra_recipients['reply_to'],
					'message_size'          => $message_size,
					'total_size'            => $total_size,
					'attachment_count'      => $attachment_stats['count'],
					'attachment_total_size' => $attachment_stats['total_size'],
					'delivery_time'         => null, // Will be updated in extract_more_mail_info.
					'email_category'        => $email_category,
					'can_resend'            => $can_resend,
					'delivery_time' =>microtime( true ) - $start_time,
				);

				self::$last_id = WP_Mail_Entity::insert( $log_entry );
			}
		}
		/**
		 * Tries to extract more information from the PHPMailer object.
		 *
		 * @param \PHPMailer $phpmailer - The PHPMailer initialized object from WP.
		 *
		 * @return void
		 *
		 * @since 3.0.1
		 */
		public static function extract_more_mail_info( $phpmailer ) {

			if ( \property_exists( $phpmailer, 'From' ) && ! empty( $phpmailer->From ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

				if ( 0 === self::$last_id ) {
					// Someone is doing nasty things and killed all of the params passed to wp_mail hook - lets intercept directly then.
					$from          = array();
					$from['email'] = $phpmailer->From; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					if ( \property_exists( $phpmailer, 'FromName' ) && ! empty( $phpmailer->FromName ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$from['name'] = $phpmailer->FromName; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					}

					// Access protected PHPMailer internals without using deprecated ReflectionProperty::setAccessible.
					$to          = self::read_phpmailer_property( $phpmailer, 'to' );
					$attachment  = self::read_phpmailer_property( $phpmailer, 'attachment' );
					$mail_header = self::read_phpmailer_property( $phpmailer, 'mailHeader' );

					$bt_segment  = self::get_backtrace();
					$plugin_slug = '';
					if ( isset( $phpmailer->Backtrace, $phpmailer->Backtrace['file'] ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$plugin_base = Plugin_Theme_Helper::get_plugin_from_file_path( $phpmailer->Backtrace['file'] ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						if ( $plugin_base ) {
							$plugin_slug = $plugin_base;
						}
					} elseif ( isset( $bt_segment['file'] ) ) {
						$plugin_base = Plugin_Theme_Helper::get_plugin_from_file_path( $bt_segment['file'] );
						if ( $plugin_base ) {
							$plugin_slug = $plugin_base;
						}
					}
					$log_entry = array(
						'time'               => time(),
						'email_to'           => self::filter_html( self::to_mail_get( $to ) ),
						'email_from'         => self::filter_html( self::array_to_string( $from ) ),
						'subject'            => self::filter_html( $phpmailer->Subject ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'message'            => self::filter_html( $phpmailer->Body ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'backtrace_segment'  => \wp_json_encode( self::get_backtrace() ),
						'plugin_slug'        => $plugin_slug,
						'status'             => 1,
						'attachments'        => \wp_json_encode( self::get_attachment_locations( $attachment ) ),
						'additional_headers' => \wp_json_encode( $mail_header ),
						'is_html'            => ( 'text/html' === $phpmailer->ContentType ) ? 1 : 0, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'blog_id'            => (int) \get_current_blog_id(),
					);

					// Extract CC, BCC, Reply-To from mail headers.
					$extra_recipients            = self::extract_extra_recipients( $mail_header );
					$log_entry['email_cc']       = $extra_recipients['cc'];
					$log_entry['email_bcc']      = $extra_recipients['bcc'];
					$log_entry['email_reply_to'] = $extra_recipients['reply_to'];

					// Calculate sizes and attachment stats.
					$attachments_data                   = self::get_attachment_locations( $attachment );
					$attachment_stats                   = self::calculate_attachment_stats( $attachments_data );
					$message_size                       = strlen( $phpmailer->Body ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$log_entry['message_size']          = $message_size;
					$log_entry['total_size']            = $message_size + $attachment_stats['total_size'];
					$log_entry['attachment_count']      = $attachment_stats['count'];
					$log_entry['attachment_total_size'] = $attachment_stats['total_size'];

					// Categorize and check resendability.
					$log_entry['email_category'] = self::categorize_email( $phpmailer->Subject, $phpmailer->Body, $bt_segment ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$log_entry['can_resend']     = self::can_resend_email( $phpmailer->Subject, $phpmailer->Body ) ? 1 : 0; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$log_entry['delivery_time']  = null; // Will be updated after email is sent.

					self::$last_id = WP_Mail_Entity::insert( $log_entry );

				} else {

					$log_entry = WP_Mail_Entity::load( 'id=%d', array( self::$last_id ) );

					$from          = array();
					$from['email'] = $phpmailer->From; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					if ( \property_exists( $phpmailer, 'FromName' ) && ! empty( $phpmailer->FromName ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$from['name'] = $phpmailer->FromName; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					}

					$log_entry['email_from'] = self::filter_html( self::array_to_string( $from ) );

					WP_Mail_Entity::insert( $log_entry );
				}
			}
		}

		/**
		 * Extracts "From" address information from Post SMTP plugin.
		 *
		 * Post SMTP replaces wp_mail() entirely and doesn't trigger phpmailer_init,
		 * so we need to hook into its custom actions to capture the "From" address.
		 *
		 * @param object $log       Post SMTP's PostmanEmailLog object (unused).
		 * @param object $message   Post SMTP's PostmanMessage object.
		 * @param string $transcript Transport transcript (unused).
		 * @param object $transport Post SMTP transport object (unused).
		 *
		 * @return void
		 *
		 * @since 4.7.1
		 */
		public static function post_smtp_extract_from( $log, $message, $transcript = '', $transport = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			// Only proceed if we have a valid last_id from a previous mail log entry.
			if ( 0 === self::$last_id ) {
				return;
			}

			// Ensure the message object has the getFromAddress method.
			if ( ! is_object( $message ) || ! method_exists( $message, 'getFromAddress' ) ) {
				return;
			}

			$from_address = $message->getFromAddress();

			// Ensure we have a valid PostmanEmailAddress object.
			if ( ! is_object( $from_address ) ) {
				return;
			}

			$from = array();

			// Extract email address.
			if ( method_exists( $from_address, 'getEmail' ) ) {
				$from['email'] = $from_address->getEmail();
			}

			// Extract name if available.
			if ( method_exists( $from_address, 'getName' ) ) {
				$name = $from_address->getName();
				if ( ! empty( $name ) ) {
					$from['name'] = $name;
				}
			}

			// Update the log entry with the from address.
			if ( ! empty( $from ) ) {
				$log_entry = WP_Mail_Entity::load( 'id=%d', array( self::$last_id ) );

				if ( ! empty( $log_entry ) ) {
					$log_entry['email_from'] = self::filter_html( self::array_to_string( $from ) );
					WP_Mail_Entity::insert( $log_entry );
				}
			}
		}

		/**
		 * Records the error information for a failed email.
		 *
		 * @param \WP_Error $error - The error triggered.
		 *
		 * @return void
		 *
		 * @since 3.0.0
		 */
		public static function record_error( $error ) {
			$log_entry           = WP_Mail_Entity::load( 'id=%d', array( self::$last_id ) );
			$log_entry['status'] = 0;
			$log_entry['error']  = \sanitize_text_field( (string) $error->get_error_message() );

			WP_Mail_Entity::insert( $log_entry );
		}

		/**
		 * Stores class constant about the typo of the email - HTML or plain text.
		 *
		 * @param string $content_type - The current content type of the mail.
		 *
		 * @return string
		 *
		 * @since 3.0.0
		 */
		public static function save_is_html( $content_type ) {

			self::$is_html = ( 'text/html' === $content_type ) ? 1 : 0;

			return $content_type;
		}

		/**
		 * Filters HTML content of the mail.
		 *
		 * @param string $value - The mail body.
		 *
		 * @return string
		 *
		 * @since 3.0.0
		 */
		public static function filter_html( $value ): string {

			$value = preg_replace( '~<!--(?!<!)[^\[>].*?-->~s', '', (string) $value );

			$value = htmlspecialchars_decode( (string) $value );

			$string = \wp_kses( $value, self::get_allowed_tags() );

			return $string;
		}

		/**
		 * What tags are allowed in the content of the mail.
		 *
		 * @return array
		 *
		 * @since 3.0.0
		 */
		private static function get_allowed_tags(): array {
			$tags = \wp_kses_allowed_html( 'post' );

			$tags['style'] = array();
			// Security hardening: do NOT allow <style> tags in logged content to avoid CSS injection in admin views.
			// Rely on the default set from wp_kses_allowed_html('post').

			return $tags;
		}

		/**
		 * Converts array to string (inner method)
		 *
		 * @param array  $pieces - The array to convert.
		 * @param string $glue - The glue which to use when converting to string.
		 *
		 * @return string
		 *
		 * @since 3.0.0
		 */
		public static function array_to_string( $pieces, $glue = ', ' ) {
			$result = self::flatten( $pieces );

			if ( is_array( $result ) ) {
				$result = implode( $glue, $result );
			}

			return $result;
		}

		/**
		 * Flattens an array to dot notation.
		 *
		 * @param array  $array_to_process - An array.
		 * @param string $separator - The character to flatten with.
		 * @param string $parent_key - The parent passed to the child.
		 *
		 * @return array One-dimensional associative array with dot notation keys.
		 *
		 * @since 3.0.0
		 */
		public static function flatten( $array_to_process, $separator = '.', $parent_key = null ) {
			if ( ! is_array( $array_to_process ) ) {
				return $array_to_process;
			}

			$_flattened = array();

			// Rewrite keys.
			foreach ( $array_to_process as $key => $value ) {
				if ( null !== $parent_key ) {
					$key = $parent_key . $separator . $key;
				}
				$_flattened[ $key ] = self::flatten( $value, $separator, $key );
			}

			// Flatten.
			$flattened = array();
			foreach ( $_flattened as $key => $value ) {
				if ( is_array( $value ) ) {
					$flattened = array_merge( $flattened, $value );
				} else {
					$flattened[ $key ] = $value;
				}
			}

			return $flattened;
		}

		/**
		 * Safely read PHPMailer protected properties without using deprecated setAccessible.
		 *
		 * @param object $phpmailer PHPMailer instance.
		 * @param string $property  Target property name.
		 *
		 * @return mixed|null Property value or null when not available.
		 */
		private static function read_phpmailer_property( $phpmailer, string $property ) {
			if ( ! is_object( $phpmailer ) || ! \property_exists( $phpmailer, $property ) ) {
				return null;
			}

			$reader = static function ( string $prop ) {
				return $this->$prop ?? null;
			};

			return \Closure::bind( $reader, $phpmailer, get_class( $phpmailer ) )( $property );
		}

		/**
		 * Get the details of the method that originally triggered wp_mail
		 *
		 * @param string $function_name - The name of the function to search for in the backtrace.
		 *
		 * @return array|null A single element of the debug_backtrace function, or null
		 *
		 * @since 3.0.0
		 */
		private static function get_backtrace( $function_name = 'wp_mail' ) {
			$backtrace_segment = null;

			$backtrace = ( new \Exception( '' ) )->getTrace();

			foreach ( $backtrace as $segment ) {
				if ( isset( $segment['function'] ) && $segment['function'] === $function_name ) {
					$backtrace_segment = $segment;
				}
			}

			return $backtrace_segment;
		}

		/**
		 * Convert attachment ids or urls into a format to be usable
		 * by the logs
		 *
		 * @param array | string $attachments either array of attachment ids or their urls.
		 *
		 * @return array [id, url] of attachments
		 *
		 * @since 3.0.0
		 */
		protected static function get_attachment_locations( $attachments ): array {
			if ( empty( $attachments ) ) {
				return array();
			}

			if ( is_string( $attachments ) ) {
				$attachments = (array) $attachments;
			}

			$upload_dir = \wp_upload_dir();
			array_walk(
				$attachments,
				function ( &$value ) use ( $upload_dir ) {
					$value = str_replace( $upload_dir['basedir'] . '/', '', $value );
				}
			);

			if (
				isset( $_POST['attachment_ids'], $_POST['_wpnonce'] )
				&& \is_array( $_POST['attachment_ids'] )
				&& \is_admin()
				&& \current_user_can( 'upload_files' )
				&& \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST['_wpnonce'] ) ), 'wp-mail-log-attachments' )
			) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Explicitly verifying nonce above.
				$raw_ids        = array_map( 'sanitize_text_field', \wp_unslash( $_POST['attachment_ids'] ) );
				$attachment_ids = array_map( 'intval', array_values( array_filter( $raw_ids ) ) );
			} else {
				$attachment_ids = self::get_attachment_ids_from_url( $attachments );

				if ( empty( $attachment_ids ) ) {
					return array(
						array(
							'id' => -1,
						),
					);
				}
			}

			if ( empty( $attachment_ids ) ) {
				return array();
			}

			return $attachment_ids;
		}

		/**
		 * Extracts attachment IDs from the url.
		 *
		 * @param array|string $urls - The URLs to use for IDs extraction.
		 *
		 * @return array
		 *
		 * @since 3.0.0
		 */
		public static function get_attachment_ids_from_url( $urls ): array {
			if ( empty( $urls ) ) {
				return array();
			}

			global $wpdb;

			$attachment_ids = array();

			if ( ! \is_array( $urls ) ) {
				$urls = array( $urls );
			}

			foreach ( $urls as $name => &$url ) {
				$sql = 'SELECT DISTINCT post_id
                FROM ' . $wpdb->prefix . 'postmeta
				WHERE meta_value LIKE %s';

				$sql .= " AND meta_key = '_wp_attached_file'";

				$url = '%' . $url . '%';

				$results = $wpdb->get_results( $wpdb->prepare( $sql, $url ), ARRAY_N ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

				if ( isset( $results[0] ) ) {
					$attachment_ids[] = array(
						'id'        => (int) $results[0][0],
						'url'       => \wp_get_attachment_url( (int) $results[0][0] ),
						'src'       => \wp_mime_type_icon( (int) $results[0][0] ),
						'alt'       => \get_post_meta( (int) $results[0][0], '_wp_attachment_image_alt', true ),
						'mime_type' => \get_post_mime_type( (int) $results[0][0] ),
					);
				} else {
					$url        = trim( $url, '%' );
					$upload_dir = \wp_upload_dir();
					$full_url   = $url;
					if ( \file_exists( \trailingslashit( $upload_dir['basedir'] ) . ( $url ) ) ) {
						$full_url = \trailingslashit( $upload_dir['baseurl'] ) . ( $url );
					}
					$attachment_ids[] = array(
						'id'  => -1,
						'url' => $full_url,
						'alt' => ( ( is_string( $name ) ) ? $name : $url ),
					);
				}
			}
			unset( $url );

			return $attachment_ids;
		}

		/**
		 * Builds to string from what is stored in the PHPMailer object.
		 *
		 * @param array $to_array - The array to convert to string.
		 *
		 * @return string
		 *
		 * @since 3.6.1
		 */
		public static function to_mail_get( $to_array ): string {

			$to_string = '';

			foreach ( $to_array as $recipient ) {
				$to_string .= trim( \implode( ' ', $recipient ) ) . ', ';
			}

			$to_string = \rtrim( $to_string, ', ' );

			return $to_string;
		}

		/**
		 * Extracts CC, BCC, and Reply-To from headers array.
		 *
		 * @param array $headers - Email headers.
		 *
		 * @return array Array with 'cc', 'bcc', 'reply_to' keys.
		 *
		 * @since 4.8.0
		 */
		private static function extract_extra_recipients( $headers ): array {
			$result = array(
				'cc'       => '',
				'bcc'      => '',
				'reply_to' => '',
			);

			if ( empty( $headers ) || ! is_array( $headers ) ) {
				return $result;
			}

			foreach ( $headers as $header ) {
				if ( ! is_string( $header ) ) {
					continue;
				}

				$header = trim( $header );

				if ( stripos( $header, 'Cc:' ) === 0 ) {
					$result['cc'] = trim( substr( $header, 3 ) );
				} elseif ( stripos( $header, 'Bcc:' ) === 0 ) {
					$result['bcc'] = trim( substr( $header, 4 ) );
				} elseif ( stripos( $header, 'Reply-To:' ) === 0 ) {
					$result['reply_to'] = trim( substr( $header, 9 ) );
				}
			}

			return $result;
		}

		/**
		 * Parses comma-separated email addresses into an array.
		 *
		 * @param string $emails - Comma-separated email addresses.
		 *
		 * @return array Array of email addresses.
		 *
		 * @since 4.8.0
		 */
		private static function parse_email_addresses( string $emails ): array {
			if ( empty( $emails ) ) {
				return array();
			}

			$addresses = array_map( 'trim', explode( ',', $emails ) );
			return array_filter( $addresses );
		}

		/**
		 * Automatically categorizes email based on content and context.
		 *
		 * @param string $subject  - Email subject.
		 * @param string $message  - Email message body.
		 * @param array  $backtrace - Backtrace array.
		 *
		 * @return string Category (order, user, system, marketing, notification, transactional, general).
		 *
		 * @since 4.8.0
		 */
		private static function categorize_email( string $subject, string $message, array $backtrace ): string {
			$subject_lower = strtolower( $subject );
			$message_lower = strtolower( $message );

			// Order-related emails.
			$order_keywords = array( 'order', 'purchase', 'invoice', 'receipt', 'payment', 'checkout', 'cart' );
			foreach ( $order_keywords as $keyword ) {
				if ( strpos( $subject_lower, $keyword ) !== false || strpos( $message_lower, $keyword ) !== false ) {
					return 'order';
				}
			}

			// User account related.
			$user_keywords = array( 'welcome', 'registration', 'account', 'profile', 'username', 'login' );
			foreach ( $user_keywords as $keyword ) {
				if ( strpos( $subject_lower, $keyword ) !== false ) {
					return 'user';
				}
			}

			// System/admin emails.
			$system_keywords = array( 'error', 'critical', 'warning', 'debug', 'admin', 'update available', 'backup' );
			foreach ( $system_keywords as $keyword ) {
				if ( strpos( $subject_lower, $keyword ) !== false ) {
					return 'system';
				}
			}

			// Marketing emails.
			$marketing_keywords = array( 'newsletter', 'promotion', 'sale', 'discount', 'offer', 'subscribe', 'unsubscribe' );
			foreach ( $marketing_keywords as $keyword ) {
				if ( strpos( $subject_lower, $keyword ) !== false || strpos( $message_lower, $keyword ) !== false ) {
					return 'marketing';
				}
			}

			// Notification emails.
			$notification_keywords = array( 'notification', 'alert', 'reminder', 'comment', 'reply', 'mention' );
			foreach ( $notification_keywords as $keyword ) {
				if ( strpos( $subject_lower, $keyword ) !== false ) {
					return 'notification';
				}
			}

			// Transactional emails (password reset, verification, etc.).
			$transactional_keywords = array( 'password', 'reset', 'verify', 'verification', 'confirm', 'confirmation', 'code', 'token' );
			foreach ( $transactional_keywords as $keyword ) {
				if ( strpos( $subject_lower, $keyword ) !== false || strpos( $message_lower, $keyword ) !== false ) {
					return 'transactional';
				}
			}

			// Check backtrace for WooCommerce/EDD context.
			if ( isset( $backtrace['file'] ) ) {
				$file_lower = strtolower( $backtrace['file'] );
				if ( strpos( $file_lower, 'woocommerce' ) !== false || strpos( $file_lower, 'edd' ) !== false ) {
					return 'order';
				}
			}

			return 'general';
		}

		/**
		 * Calculates attachment statistics.
		 *
		 * @param array $attachments - Array of attachment data.
		 *
		 * @return array Array with 'count' and 'total_size' keys.
		 *
		 * @since 4.8.0
		 */
		private static function calculate_attachment_stats( array $attachments ): array {
			$count      = 0;
			$total_size = 0;

			if ( ! empty( $attachments ) ) {
				$count = count( $attachments );

				foreach ( $attachments as $attachment ) {
					if ( isset( $attachment['url'] ) && file_exists( $attachment['url'] ) ) {
						$total_size += filesize( $attachment['url'] );
					}
				}
			}

			return array(
				'count'      => $count,
				'total_size' => $total_size,
			);
		}

		/**
		 * Determines if an email can be safely resent.
		 *
		 * @param string $subject - Email subject.
		 * @param string $message - Email message body.
		 *
		 * @return bool True if can resend, false otherwise.
		 *
		 * @since 4.8.0
		 */
		private static function can_resend_email( string $subject, string $message ): bool {
			$subject_lower = strtolower( $subject );
			$message_lower = strtolower( $message );

			// Don't allow resending time-sensitive or one-time emails.
			$sensitive_keywords = array(
				'password reset',
				'verification code',
				'verify your',
				'confirm your',
				'one-time',
				'expires',
				'expiring',
				'2fa',
				'two-factor',
				'authentication code',
				'otp',
			);

			foreach ( $sensitive_keywords as $keyword ) {
				if ( strpos( $subject_lower, $keyword ) !== false || strpos( $message_lower, $keyword ) !== false ) {
					return false;
				}
			}

			return true;
		}
	}
}
