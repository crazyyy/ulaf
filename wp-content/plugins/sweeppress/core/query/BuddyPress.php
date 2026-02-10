<?php

namespace Dev4Press\Plugin\SweepPress\Query;

use Dev4Press\Plugin\SweepPress\Base\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BuddyPress extends Query {
	protected string $_group = 'buddypress-orphans';

	public static function instance() : BuddyPress {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new BuddyPress();
		}

		return $instance;
	}

	public function activity_meta_orphaned_status() : array {
		$list = $this->retrieve( 'activity-meta-record-orphans' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_bp_activity_meta_orphaned_records();

			$list = array(
				'title'   => __( 'Orphaned Records', 'sweeppress' ),
				'records' => $raw['key_records'],
				'size'    => $raw['key_size'],
			);

			if ( $list['size'] > 0 && $list['size'] < 1025 ) {
				$list['size'] = 1024;
			}

			$this->store( 'activity-meta-record-orphans', $list );
		}

		return $list;
	}

	public function groups_meta_orphaned_status() : array {
		$list = $this->retrieve( 'groups-meta-record-orphans' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_bp_groups_meta_orphaned_records();

			$list = array(
				'title'   => __( 'Orphaned Records', 'sweeppress' ),
				'records' => $raw['key_records'],
				'size'    => $raw['key_size'],
			);

			if ( $list['size'] > 0 && $list['size'] < 1025 ) {
				$list['size'] = 1024;
			}

			$this->store( 'groups-meta-record-orphans', $list );
		}

		return $list;
	}

	public function messages_meta_orphaned_status() : array {
		$list = $this->retrieve( 'messages-meta-record-orphans' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_bp_messages_meta_orphaned_records();

			$list = array(
				'title'   => __( 'Orphaned Records', 'sweeppress' ),
				'records' => $raw['key_records'],
				'size'    => $raw['key_size'],
			);

			if ( $list['size'] > 0 && $list['size'] < 1025 ) {
				$list['size'] = 1024;
			}

			$this->store( 'messages-meta-record-orphans', $list );
		}

		return $list;
	}

	public function notifications_meta_orphaned_status() : array {
		$list = $this->retrieve( 'notifications-meta-record-orphans' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_bp_notifications_meta_orphaned_records();

			$list = array(
				'title'   => __( 'Orphaned Records', 'sweeppress' ),
				'records' => $raw['key_records'],
				'size'    => $raw['key_size'],
			);

			if ( $list['size'] > 0 && $list['size'] < 1025 ) {
				$list['size'] = 1024;
			}

			$this->store( 'notifications-meta-record-orphans', $list );
		}

		return $list;
	}
}
