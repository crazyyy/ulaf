<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Class ADBC_Cleanup_Actionscheduler_Actions_Handler_Base
 * 
 * This class serves as a base handler for cleaning up Action Scheduler actions in WordPress.
 */
abstract class ADBC_Cleanup_Actionscheduler_Actions_Handler_Base extends ADBC_Abstract_Cleanup_Handler {

	// Required, all actions subclasses must supply
	abstract protected function items_type();
	abstract protected function base_where();

	// Common to all actions subclasses, provided by this base class
	protected function table() {
		global $wpdb;
		return $wpdb->prefix . 'actionscheduler_actions';
	}
	protected function table_suffix() {
		return 'actionscheduler_actions';
	}
	protected function pk() {
		return 'action_id';
	}
	protected function name_column() {
		return 'hook';
	}
	protected function value_column() {
		return 'args';
	}
	protected function is_all_sites_sortable() {
		return true;
	}
	protected function sortable_columns() {
		return [ 
			'action_id',
			'hook',
			'args',
			'scheduled_date_gmt',
			'last_attempt_date',
			'site_id',
			'size'
		];
	}
	protected function extra_select() {
		return [ 
			'scheduled_date_gmt',
			'last_attempt_gmt',
			"'{$this->status_value()}' as status",
		];
	}
	protected function date_column() {
		return 'scheduled_date_gmt';
	}
	protected function keep_last_mode() {
		return 'from_total';
	}

	protected function delete_helper() {
		return ''; // unused
	}

	protected function status_value() {
		return '';
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

			// delete the actions themselves
			$actions_sql = "
                    DELETE FROM {$this->table()}
			        WHERE {$this->pk()} IN ( $placeholders )
            ";
			$actions_query = $wpdb->prepare( $actions_sql, ...$ids );

			$deleted += $wpdb->query( $actions_query );

			ADBC_Sites::instance()->restore_blog();

		}

		return $deleted;

	}

	public function purge() {

		global $wpdb;

		$chunk = self::PURGE_CHUNK;
		$deleted = 0;

		foreach ( ADBC_Sites::instance()->get_sites_list() as $site ) {

			ADBC_Sites::instance()->switch_to_blog_id( $site['id'] );

			// Make sure the Action Scheduler tables exist
			$actions_table = $this->table();

			$have_actions = (bool) $wpdb->get_var( $wpdb->prepare(
				"SHOW TABLES LIKE %s", $actions_table
			) );

			if ( ! $have_actions ) {
				ADBC_Sites::instance()->restore_blog();
				continue; // nothing to do on this blog
			}

			// Chunk loop until nothing matches any more
			while ( true ) {

				$ids_sql = "
                        SELECT main.{$this->pk()}
                        FROM    {$actions_table} AS main
                        WHERE   {$this->base_where()}
                                {$this->keep_days_filter()}
                                {$this->keep_items_filter()}
                        LIMIT  %d
                ";
				$ids_query = $wpdb->prepare( $ids_sql, $chunk );

				$ids = $wpdb->get_col( $ids_query );

				// finished on this site
				if ( empty( $ids ) ) {
					break;
				}

				$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

				// delete the actions themselves
				$delete_actions_sql = "
                        DELETE FROM {$actions_table}
                        WHERE {$this->pk()} IN ( $placeholders )
                        AND {$this->base_where()}
                ";
				$delete_actions_query = $wpdb->prepare( $delete_actions_sql, ...$ids );

				$deleted += $wpdb->query( $delete_actions_query );

			}

			ADBC_Sites::instance()->restore_blog();

		}

		return $deleted;

	}

}

/**
 * Class ADBC_Cleanup_Actionscheduler_Completed_Actions_Handler
 * 
 * This class handles the cleanup of completed actions in the Action Scheduler.
 */
class ADBC_Cleanup_Actionscheduler_Completed_Actions_Handler extends ADBC_Cleanup_Actionscheduler_Actions_Handler_Base {

	protected function items_type() {
		return 'actionscheduler_completed_actions';
	}

	protected function base_where() {
		return "status = 'complete'";
	}

	protected function status_value() {
		return __( 'complete', 'advanced-database-cleaner' );
	}

}

/**
 * Class ADBC_Cleanup_Actionscheduler_Failed_Actions_Handler
 * 
 * This class handles the cleanup of failed actions in the Action Scheduler.
 */
class ADBC_Cleanup_Actionscheduler_Failed_Actions_Handler extends ADBC_Cleanup_Actionscheduler_Actions_Handler_Base {

	protected function items_type() {
		return 'actionscheduler_failed_actions';
	}

	protected function base_where() {
		return "status = 'failed'";
	}

	protected function status_value() {
		return __( 'failed', 'advanced-database-cleaner' );
	}

}

/**
 * Class ADBC_Cleanup_Actionscheduler_Canceled_Actions_Handler
 * 
 * This class handles the cleanup of canceled actions in the Action Scheduler.
 */
class ADBC_Cleanup_Actionscheduler_Canceled_Actions_Handler extends ADBC_Cleanup_Actionscheduler_Actions_Handler_Base {

	protected function items_type() {
		return 'actionscheduler_canceled_actions';
	}

	protected function base_where() {
		return "status = 'canceled'";
	}

	protected function status_value() {
		return __( 'canceled', 'advanced-database-cleaner' );
	}

}

// Register the handlers if the actionscheduler_action table exists
if ( ADBC_Tables::is_actionscheduler_table_exists( 'actions' ) ) {

	ADBC_Cleanup_Type_Registry::register( 'actionscheduler_completed_actions', new ADBC_Cleanup_Actionscheduler_Completed_Actions_Handler() );
	ADBC_Cleanup_Type_Registry::register( 'actionscheduler_failed_actions', new ADBC_Cleanup_Actionscheduler_Failed_Actions_Handler() );
	ADBC_Cleanup_Type_Registry::register( 'actionscheduler_canceled_actions', new ADBC_Cleanup_Actionscheduler_Canceled_Actions_Handler() );

}