<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweep\CommentStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CommentsTrash extends CommentStatus {
	protected string $_code = 'comments-trash';
	protected string $_comment_status = 'trash';

	protected bool $_flag_auto_cleanup = false;

	protected bool $_flag_bulk_network = true;

	public static function instance() : CommentsTrash {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new CommentsTrash();
		}

		return $instance;
	}

	public function title() : string {
		return __( 'Trashed Comments', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove trashed comments.', 'sweeppress' );
	}
}
