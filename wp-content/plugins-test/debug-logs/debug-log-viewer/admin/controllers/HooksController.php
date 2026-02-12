<?php

namespace DebugLogViewer\Admin\Controllers;

use DebugLogViewer\Admin\Controllers\ReviewController;
use DebugLogViewer\Admin\Controllers\LogController;
use DebugLogViewer\Admin\Controllers\AlertController;
use DebugLogViewer\Admin\Controllers\ServiceController;
use DebugLogViewer\Admin\Translations\Phrases;
use DebugLogViewer\Admin\Models\LogModel;

if (!defined('ABSPATH')) {
	exit;
	// Exit if accessed directly
}

class HooksController
{

	public $logController;
	public $menuController;
	public $freemiusController;
	public $reviewController;
	public $liveUpdatesController;
	public $notificationController;
	public $serviceController;

	public function __construct()
	{
		$this->logController          = new LogController();
		$this->menuController         = new MenuController();
		$this->freemiusController     = new FreemiusController();
		$this->reviewController       = new ReviewController();
		$this->liveUpdatesController  = new LiveUpdatesController();
		$this->notificationController = new AlertController();
		$this->serviceController      = new ServiceController();
	}

	public function init()
	{
		// Load text domain for translations

		$dbg_lv_index_file = plugin_dir_path(__DIR__) . 'debug-log-viewer.php';

		// Include styles and scripts in Debug Log Viewer plugin's pages only
		add_action('admin_enqueue_scripts', function ($hook_suffix) {
			// Use $this instead of $this->hooksController
			$this->dbg_lv_admin_assets_enqueue($hook_suffix);
		});

		// Log actions
		add_action('wp_ajax_dbg_lv_log_viewer_clear_log', function () {
			$this->logController->clear();
		});

		add_action('wp_ajax_dbg_lv_log_viewer_download_log', function () {
			$this->logController->download();
		});

		// Handle Alerts
		add_action('wp_ajax_dbg_lv_change_log_viewer_alerts_status', function () {
			$this->logController->dbg_lv_change_notifications_status();
		});

		add_action('wp_ajax_dbg_lv_get_current_user_email', function () {
			$this->logController->getCurrentUserEmail();
		});

		// Mode: auto / manual
		add_action('wp_ajax_dbg_lv_change_logs_update_mode', function () {
			$this->logController->changeLogsUpdateMode();
		});

		// Datetime format: absolute / relative
		add_action('wp_ajax_dbg_lv_change_datetime_format', function () {
			$this->logController->changeDatetimeFormat();
		});

		// Button in template
		add_action('wp_ajax_dbg_lv_first_run_enable_logging', function () {
			$this->logController->firstRunEnableLogging();
		});

		// Debug constants
		add_action('wp_ajax_dbg_lv_toggle_debug_mode', function () {
			$this->logController->toggleDebugMode();
		});
		add_action('wp_ajax_dbg_lv_toggle_debug_scripts', function () {
			$this->logController->toggleDebugScripts();
		});
		add_action('wp_ajax_dbg_lv_toggle_log_in_file', function () {
			$this->logController->toggleLogInFile();
		});
		add_action('wp_ajax_dbg_lv_toggle_display_errors', function () {
			$this->logController->toggleDisplayErrors();
		});

		// Live updates
		add_action('wp_ajax_dbg_lv_run_live_updates', function () {
			$this->liveUpdatesController->run();
		});

		add_action('wp_ajax_dbg_lv_is_debug_log_publicly_accessible', function () {
			$this->logController->dbg_lv_is_debug_log_publicly_accessible();
		});

		add_action(LogController::SCHEDULE_MAIL_SEND, function ($args) {
			$this->notificationController->dbg_lv_send_logs_handler($args);
		});

		add_action('wp_ajax_dbg_lv_get_alert_schedule', function () {
			wp_send_json($this->notificationController->getNextScheduledTimeForAjax());
		});

		add_action('admin_init', function () {
			if ($this->reviewController->isTimeToAskReview()) {
				add_action('admin_notices', function () {
					$this->reviewController->renderBanner();
				});
			}
			$this->reviewController->handleUserAction();
		});

		// Uninstall / deactivate hooks
		register_deactivation_hook($dbg_lv_index_file, ['DebugLogViewer\Admin\Controllers\ServiceController', 'deactivateHandler']);
		register_uninstall_hook($dbg_lv_index_file, ['DebugLogViewer\Admin\Controllers\ServiceController', 'uninstallHandler']);
	}

	public function dbg_lv_admin_assets_enqueue($hook_suffix)
	{
		if (strpos($hook_suffix, 'debug-log-viewer') !== false) {
			// Get the plugin version from the main plugin file
			$main_plugin_file = plugin_dir_path(__DIR__) . '../debug-log-viewer.php';
			$plugin_data      = get_file_data($main_plugin_file, ['Version' => 'Version']);
			$version          = !empty($plugin_data['Version']) ? $plugin_data['Version'] : '1.0.0';

			$logController = new LogController();

			// Enqueue Freemius checkout script
			wp_enqueue_script('freemius_checkout', 'https://checkout.freemius.com/js/v1/', [], $version, true);

			// Enqueue scripts
			wp_enqueue_script('dbg_lv_app_js', plugins_url('../front/dist/bundle.js', __DIR__), ['jquery', 'freemius_checkout'], $version, true);
			wp_enqueue_script('dbg_lv_font-awesome_js', plugins_url('../front/assets/vendor/js/font-awesome.js', __DIR__), [], $version, true);

			// Get Freemius data
			$is_premium = $checkout_url = false;
			try {
				$fs = dbg_lv();
				if ($fs && is_object($fs)) {
					$is_premium = $fs->is_premium();
					$checkout_url = $fs->checkout_url();
				}
			} catch (\Exception $e) {
				$is_premium = $checkout_url = false;
			}

			// Localize script
			wp_localize_script('dbg_lv_app_js', 'dbg_lv_backend_data', [
				'ajax_nonce'           => wp_create_nonce('ajax_nonce'),
				'phrases'              => Phrases::getParsedContentPhrases(),
				'log_updates_mode'     => get_option(LogModel::LOG_UPDATES_MODE_OPTION_NAME),
				'datetime_format'      => get_option(LogModel::DATETIME_FORMAT_OPTION_NAME),
				'log_updates_interval' => LogModel::LOG_UPDATES_INTERVAL,
				'log_popover_data'     => $logController->getPopoverData(),
				'is_premium'           => $is_premium,
				'checkout_url'         => $checkout_url
			]);

			// Add Freemius configuration data
			wp_localize_script('dbg_lv_app_js', 'dbg_lv_freemius_data', [
				'is_premium'   => $is_premium,
				'checkout_url' => $checkout_url,
				'product_id'   => '17350',
				'plan_id'      => '29848',
				'public_key'   => 'pk_d456c712f16510d920c9f4ba4880a',
				'image'        => ''
			]);

			// Enqueue styles
			wp_enqueue_style('dbg_lv_datatables_css', plugins_url('../front/dist/bundle.css', __DIR__), [], $version);
		}
	}
}
