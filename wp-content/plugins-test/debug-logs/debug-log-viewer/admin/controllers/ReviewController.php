<?php

namespace DebugLogViewer\Admin\Controllers;

if (!defined('ABSPATH')) {
	exit;
	// Exit if accessed directly
}

use DebugLogViewer\Admin\Helpers\Constants;

class ReviewController {


	public function isTimeToAskReview() {
		$user_id = get_current_user_id();
		if (!$user_id) {
			return false;
		}

		$review_time = get_user_meta($user_id, 'dbg_lv_next_schedule_review_notice_time', true);
		// If review time is not set yet, set it to 2 weeks from now
		if (empty($review_time)) {
			$review_time = time() + Constants::TWO_WEEK_IN_SECONDS;
			add_user_meta($user_id, 'dbg_lv_next_schedule_review_notice_time', $review_time, true);
			return false;
		}

		return time() > (int) $review_time;
	}

	public function isPluginPage() {
		// Check if the 'page' parameter is set and sanitize it
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		return strpos($page, 'debug-log-viewer') !== false;
	}

	public function renderBanner() {
		if (!$this->isPluginPage()) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$url             = add_query_arg($_GET, admin_url('admin.php'));
		$nonce_url_do    = wp_nonce_url(
			add_query_arg('dbg_lv_review', 'do', $url),
			'dbg_lv_review_action',
			'dbg_lv_nonce'
		);
		$nonce_url_later = wp_nonce_url(
			add_query_arg('dbg_lv_review', 'later', $url),
			'dbg_lv_review_action',
			'dbg_lv_nonce'
		);

		// Include the review banner template
		include plugin_dir_path(__FILE__) . '../../front/views/components/review-banner.tpl.php';
	}

	public function handleUserAction() {
		// Exit early if no review action is being taken
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (!isset($_GET['dbg_lv_review'])) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (!isset($_GET['dbg_lv_nonce']) || !wp_verify_nonce($_GET['dbg_lv_nonce'], 'dbg_lv_review_action')) {
			wp_die('Security check failed', 'Debug Log Viewer');
		}

		$user_id = get_current_user_id();
		if (!$user_id) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$review = sanitize_text_field(wp_unslash($_GET['dbg_lv_review']));

		if ($review === 'later') {
			$this->updateReviewTime($user_id, Constants::TWO_MONTH_IN_SECONDS);

			// Redirect to the same page without the review parameters to refresh the page
			$redirect_url = remove_query_arg([ 'dbg_lv_review', 'dbg_lv_nonce' ], $this->getCurrentPageUrl());
			wp_safe_redirect($redirect_url);
			exit;
		} elseif ($review === 'do') {
			$this->updateReviewTime($user_id, Constants::ONE_YEAR_IN_SECONDS);

			// Redirect to WordPress.org reviews
			wp_redirect('https://wordpress.org/support/plugin/debug-log-viewer/reviews/#new-post?filter=5');
			exit;
		}
	}

	private function updateReviewTime( $user_id, $seconds ) {
		$meta_key = 'dbg_lv_next_schedule_review_notice_time';
		$new_time = time() + $seconds;

		if (get_user_meta($user_id, $meta_key, true)) {
			update_user_meta($user_id, $meta_key, $new_time);
		} else {
			add_user_meta($user_id, $meta_key, $new_time, true);
		}
	}

	private function getCurrentPageUrl() {
		// Safely get the current admin page URL
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		
		if (empty($page)) {
			return admin_url();
		}
		
		return admin_url('admin.php?page=' . $page);
	}
}
