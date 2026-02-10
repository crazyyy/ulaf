<?php

namespace Dev4Press\Plugin\SweepPress\Base\Sweep;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\Query\Meta;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
abstract class MetaDuplicates extends Sweeper {
    protected string $_version = '6.0';

    protected bool $_flag_quick_cleanup = false;

    protected bool $_flag_auto_cleanup = false;

    protected string $table;

    protected string $db_table;

    protected array $db_columns;

    protected array $exceptions;

    protected bool $_flag_high_system_requirements = true;

    public function __construct() {
        parent::__construct();
    }

    public function help() : array {
        return array(__( 'While in theory, it is allowed to have such records, it is highly unlikely that they are needed and most likely they are product of some error.', 'sweeppress' ), __( 'Deleting duplicated records will leave one instance of the record, and remove all other duplicates. The estimated number of records shows only records that will be removed.', 'sweeppress' ), __( 'If there are some meta keys you want to exclude from duplicate check, you can add them in the plugin settings.', 'sweeppress' ));
    }

    public function limitations() : array {
        return array(__( 'This sweeper is not available from the Dashboard for Quick or Auto Sweep.', 'sweeppress' ));
    }

    public function prepare() {
        $this->_tasks = array(
            $this->_code => Meta::instance()->duplicated_records(
                $this->table,
                $this->db_table,
                $this->db_columns,
                $this->exceptions
            ),
        );
    }

    public function sweep( $tasks = array() ) : array {
        $this->register_active_sweeper();
        $this->log_sweep_start();
        $removal = Removal::instance()->duplicated_meta_records(
            $this->table,
            $this->db_table,
            $this->db_columns,
            $this->exceptions
        );
        return $this->base_sweep( $removal );
    }

}
