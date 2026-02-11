<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Class ADBC_Cleanup_Actionscheduler_Logs_Handler_Base
 * 
 * This class serves as a base handler for cleaning up Action Scheduler logs in WordPress.
 */
abstract class ADBC_Cleanup_Actionscheduler_Logs_Handler_Base extends ADBC_Abstract_Cleanup_Handler {

	// Required, all comments subclasses must supply
	abstract protected function items_type();
	abstract protected function base_where();

	// Common to all logs subclasses, provided by this base class
	protected function table() {
		global $wpdb;
		return $wpdb->prefix . 'actionscheduler_logs';
	}
	protected function table_suffix() {
		return 'actionscheduler_logs';
	}
	protected function pk() {
		return 'log_id';
	}
	protected function name_column() {
		return 'action_id';
	}
	protected function value_column() {
		return 'message';
	}
	protected function date_column() {
		return 'log_date_gmt';
	}

	protected function is_all_sites_sortable() {
		return true;
	}

	protected function sortable_columns() {
		return [ 
			'log_id',
			'action_id',
			'message',
			'log_date_gmt',
			'site_id',
			'size'
		];
	}

	protected function delete_helper() {
		return ''; // unused
	}

	protected function extra_select() {
		return [ 
			'main.log_date_gmt',
			"'{$this->status_value()}' as status",
		];
	}
	protected function status_value() {
		return '';
	}
	// Join to the actions table so subclasses can filter on act.status
	protected function extra_joins() {
		global $wpdb;
		$action_table = $wpdb->prefix . 'actionscheduler_actions';
		return "JOIN {$action_table} act
		          ON act.action_id = main.action_id";
	}

	protected function keep_last_mode() {
		return 'from_total';
	}

	public function delete( $items ) {

		global $wpdb;

		if ( empty( $items ) ) {
			return 0;
		}

		$by_site = [];
		foreach ( $items as $row ) {
			$by_site[ $row['site_id'] ][] = $row['id'];
		}

		$deleted = 0;

		foreach ( $by_site as $site_id => $ids ) {

			ADBC_Sites::instance()->switch_to_blog_id( $site_id );

			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

			$sql = "
                DELETE FROM {$this->table()}
			    WHERE {$this->pk()} IN ( $placeholders )
            ";
			$query = $wpdb->prepare( $sql, ...$ids );

			$deleted += $wpdb->query( $query );

			ADBC_Sites::instance()->restore_blog();

		}

		return $deleted;

	}

	public function purge() {

		global $wpdb;

		$total_deleted = 0;
		$chunk = self::PURGE_CHUNK;

		foreach ( ADBC_Sites::instance()->get_sites_list() as $site ) {

			ADBC_Sites::instance()->switch_to_blog_id( $site['id'] );

			$logs_table = $this->table();
			$actions_table = $wpdb->prefix . 'actionscheduler_actions';

			$have_logs = (bool) $wpdb->get_var( $wpdb->prepare(
				'SHOW TABLES LIKE %s', $logs_table
			) );
			$have_actions = (bool) $wpdb->get_var( $wpdb->prepare(
				'SHOW TABLES LIKE %s', $actions_table
			) );

			if ( ! $have_logs || ! $have_actions ) {
				ADBC_Sites::instance()->restore_blog();
				continue;   // nothing to do on this site
			}

			while ( true ) {

				// fetch a window of log_ids that match the subclass filter
				$ids_sql = "
					SELECT main.{$this->pk()}
					FROM   {$logs_table} AS main
						   {$this->extra_joins()}
					WHERE  {$this->base_where()}
					       {$this->keep_days_filter()}
					       {$this->keep_items_filter()}
					LIMIT  %d
				";
				$ids_query = $wpdb->prepare( $ids_sql, $chunk );

				$ids = $wpdb->get_col( $ids_query );

				// finished on this blog
				if ( empty( $ids ) ) {
					break;
				}

				// delete those IDs
				$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

				$delete_sql = "
					DELETE FROM {$this->table()}
					WHERE {$this->pk()} IN ( $placeholders )
				";
				$del_query = $wpdb->prepare( $delete_sql, ...$ids );

				$total_deleted += $wpdb->query( $del_query );

			}

			ADBC_Sites::instance()->restore_blog();

		}

		return $total_deleted;

	}

}

/**
 * Class ADBC_Cleanup_Actionscheduler_Completed_Logs_Handler
 * 
 * This class handles the cleanup of completed Action Scheduler logs.
 */
class ADBC_Cleanup_Actionscheduler_Completed_Logs_Handler extends ADBC_Cleanup_Actionscheduler_Logs_Handler_Base {

	protected function items_type() {
		return 'actionscheduler_completed_logs';
	}

	protected function base_where() {
		return "act.status = 'complete'";
	}
	protected function status_value() {
		return __( 'complete', 'advanced-database-cleaner' );
	}
}

/**
 * Class ADBC_Cleanup_Actionscheduler_Failed_Logs_Handler
 * 
 * This class handles the cleanup of failed Action Scheduler logs.
 */
class ADBC_Cleanup_Actionscheduler_Failed_Logs_Handler extends ADBC_Cleanup_Actionscheduler_Logs_Handler_Base {

	protected function items_type() {
		return 'actionscheduler_failed_logs';
	}

	protected function base_where() {
		return "act.status = 'failed'";
	}

	protected function status_value() {
		return __( 'failed', 'advanced-database-cleaner' );
	}
}

/**
 * Class ADBC_Cleanup_Actionscheduler_Canceled_Logs_Handler
 * 
 * This class handles the cleanup of canceled Action Scheduler logs.
 */
class ADBC_Cleanup_Actionscheduler_Canceled_Logs_Handler extends ADBC_Cleanup_Actionscheduler_Logs_Handler_Base {

	protected function items_type() {
		return 'actionscheduler_canceled_logs';
	}

	protected function base_where() {
		return "act.status = 'canceled'";
	}

	protected function status_value() {
		return __( 'canceled', 'advanced-database-cleaner' );
	}
}

/**
 * Class ADBC_Cleanup_Actionscheduler_Orphan_Logs_Handler
 * 
 * This class handles the cleanup of orphaned Action Scheduler logs.
 */
class ADBC_Cleanup_Actionscheduler_Orphan_Logs_Handler extends ADBC_Cleanup_Actionscheduler_Logs_Handler_Base {

	protected function items_type() {
		return 'actionscheduler_orphan_logs';
	}

	protected function extra_joins() {
		global $wpdb;
		$action_table = $wpdb->prefix . 'actionscheduler_actions';
		return "LEFT JOIN {$action_table} act ON act.action_id = main.action_id";
	}

	protected function base_where() {
		return 'act.action_id IS NULL';
	}

	protected function status_value() {
		return __( 'orphaned', 'advanced-database-cleaner' );
	}

}

// Register the handlers if the actionscheduler_logs table exists
if ( ADBC_Tables::is_actionscheduler_table_exists( 'logs' ) ) {

	ADBC_Cleanup_Type_Registry::register( 'actionscheduler_completed_logs', new ADBC_Cleanup_Actionscheduler_Completed_Logs_Handler() );
	ADBC_Cleanup_Type_Registry::register( 'actionscheduler_failed_logs', new ADBC_Cleanup_Actionscheduler_Failed_Logs_Handler() );
	ADBC_Cleanup_Type_Registry::register( 'actionscheduler_canceled_logs', new ADBC_Cleanup_Actionscheduler_Canceled_Logs_Handler() );
	ADBC_Cleanup_Type_Registry::register( 'actionscheduler_orphan_logs', new ADBC_Cleanup_Actionscheduler_Orphan_Logs_Handler() );

}