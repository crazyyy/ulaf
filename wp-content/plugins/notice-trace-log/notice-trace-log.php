<?php
/*
 * Plugin Name:       Notice TraceLog
 * Plugin URI:        https://github.com/upandrii/debug-backtrace
 * Description:       Shows backtrace for PHP Notices to help identify early translation or execution errors.
 * Version:           1.1.0
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Andrii Shuliak
 * Contributors:      shuliakmaster
 * Author URI:        https://github.com/upandrii/
 * License:           GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('NoticeTraceLog')) {

    class NoticeTraceLog {

        /**
         * Plugin settings
         * @var array
         */
        private $settings = array(
            'is_debug' => false,
            'show_backtrace' => false,
            'log_to_file' => false,
            'log_to_db' => false,
            'log_to_email' => false,
        );

        private $backtrace = '';

        /**
         * Construct
         */
        public function __construct() {
            add_action('plugins_loaded', [$this, 'setup']);
            add_action('admin_notices', [$this, 'admin_notice_check_debug']);
        }

        /**
         * Init notice
         */
        public function setup() {
            $this->settings['is_debug'] = defined('WP_DEBUG') && WP_DEBUG && ! isset($_GET['stop_tracelog']);

            if($this->settings['is_debug']){
                $this->settings['show_backtrace'] = ( !defined('WP_DEBUG_DISPLAY') || WP_DEBUG_DISPLAY );
                $this->settings['log_to_file'] = ( defined('WP_DEBUG_LOG') && WP_DEBUG_LOG );
                set_error_handler([$this, 'handle_notice']);
            }
        }

        /**
         * Handle notice
         */
        public function handle_notice($error, $errstr, $errfile, $errline) {
            if (error_reporting() & $error) {
                $this->get_backtrace();
                if($this->settings['show_backtrace']){
                    $this->notice_title_html($errstr, $errfile, $errline);
                    $this->notice_tracelog_html();
                }

                if ($this->settings['log_to_file']) {
                    $this->log_notice($errstr, $errfile, $errline);
                    $this->log_tracelog();
                }
            }
            return true;
        }

        public function get_backtrace(){
            ob_start(); 
            debug_print_backtrace();
            $this->backtrace = ob_get_clean();
        }
        
         /**
         * Notice title html
         */
        public function notice_title_html($errstr, $errfile, $errline){
            printf(
                '<p style="padding: 10px; margin: 0; background: #ebebee;">
                    <b>Notice:</b> %s<br>
                    <b>File:</b> %s<br>
                    <b>Line:</b> %s<br>
                </p>',
                esc_html($errstr),
                esc_html($errfile),
                esc_html($errline)
            );
        }

        /**
         * Notice tracelog html
         */
        public function notice_tracelog_html(){
            printf(
                '<details open style="margin-left: 25px; margin-bottom: 25px; background: #ebebeb; padding: 10px;">
                    <summary>Tracelog</summary>
                    <p>%s</p>
                </details>',
                nl2br(esc_html($this->backtrace))
            );
        }

        /**
         * Write notice to log file
         */
        public function log_notice($errstr, $errfile, $errline){
            $message = sprintf(
                "[Notice]\nMessage: %s\nFile: %s\nLine: %d\n--------------------------------------------------------\n",
                $errstr,
                $errfile,
                $errline
            );
            error_log($message);
        }

        /**
         * Write tracelog to log file
         */
        public function log_tracelog(){
            $traceMessage = "[Backtrace]\n" . $this->backtrace . "\n========================================================\n";
            error_log($traceMessage);
        }

        /**
         * Show admin notice if debug settings are wrong
         */
        public function admin_notice_check_debug() {
            $problems = [];

            if (!$this->settings['is_debug']) {
                $problems[] = 'WP_DEBUG is disabled — Tracelog is fully inactive.';
            }

            if (!$this->settings['show_backtrace']) {
                $problems[] = 'WP_DEBUG_DISPLAY is disabled — Tracelog will not display notices in the browser.';
            }

            if (!$this->settings['log_to_file']) {
                $problems[] = 'WP_DEBUG_LOG is disabled — Tracelog will not write notices to the log file.';
            }

            if (!(error_reporting() & E_NOTICE)) {
                $problems[] = 'E_NOTICE is not included in error_reporting — Notices will be ignored.';
            }

            if (!empty($problems)) {
                echo '<div class="notice notice-error">';
                echo '<p><strong>Debug Tracelog:</strong></p>';
                echo '<ul>';
                foreach ($problems as $problem) {
                    echo '<li>' . esc_html($problem) . ' Learn more about testing and debugging in <a href="https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/" target="_blank">WordPress</a>.</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
        }
    }

    $dbon_instance = new NoticeTraceLog();
}