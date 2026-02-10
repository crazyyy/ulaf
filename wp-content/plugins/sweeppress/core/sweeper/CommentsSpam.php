<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweep\CommentStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CommentsSpam extends CommentStatus {
	protected string $_code = 'comments-spam';
	protected string $_comment_status = 'spam';

	protected bool $_flag_auto_cleanup = false;

	protected bool $_flag_bulk_network = true;

	public static function instance() : CommentsSpam {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new CommentsSpam();
		}

		return $instance;
	}

	public function title() : string {
		return __( 'Spam Comments', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove spammed comments.', 'sweeppress' );
	}
}
