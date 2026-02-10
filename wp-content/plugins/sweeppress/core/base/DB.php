<?php

namespace Dev4Press\Plugin\SweepPress\Base;

use Dev4Press\v54\Core\Plugins\DB as LibDB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property string actionscheduler_actions
 * @property string actionscheduler_claims
 * @property string actionscheduler_groups
 * @property string actionscheduler_logs
 * @property string e_submissions
 * @property string e_submissions_actions_log
 * @property string e_submissions_values
 * @property string gf_form
 * @property string gf_form_meta
 * @property string gf_form_revisions
 * @property string gf_form_view
 * @property string gf_entry
 * @property string gf_entry_meta
 * @property string gf_entry_notes
 * @property string bp_activity
 * @property string bp_activity_meta
 * @property string bp_notifications
 * @property string bp_notifications_meta
 * @property string bp_messages_messages
 * @property string bp_messages_meta
 * @property string bp_groups
 * @property string bp_groups_groupmeta
 */
abstract class DB extends LibDB {
	protected string $plugin_name = 'sweeppress';
	public array $internal_tables = array(
		'actionscheduler_actions',
		'actionscheduler_claims',
		'actionscheduler_groups',
		'actionscheduler_logs',
		'e_submissions',
		'e_submissions_actions_log',
		'e_submissions_values',
		'gf_form',
		'gf_form_meta',
		'gf_form_revisions',
		'gf_form_view',
		'gf_entry',
		'gf_entry_meta',
		'gf_entry_notes',
	);

	public array $buddypress_tables = array(
		'bp_activity',
		'bp_activity_meta',
		'bp_notifications',
		'bp_notifications_meta',
		'bp_messages_messages',
		'bp_messages_meta',
		'bp_groups',
		'bp_groups_groupmeta',
	);

	public function __get( $name ) {
		if ( in_array( $name, $this->internal_tables ) ) {
			return $this->prefix . $name;
		}

		if ( in_array( $name, $this->buddypress_tables ) && function_exists( 'bp_core_get_table_prefix' ) ) {
			return bp_core_get_table_prefix() . $name;
		}

		return parent::__get( $name );
	}

	public function table_name( $name ) {
		$table = $this->wpdb()->$name ?? '';

		if ( empty( $table ) ) {
			$table = $this->$name;
		}

		return empty( $table ) ? $name : $table;
	}
}
