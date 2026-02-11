<?php

namespace Dev4Press\Plugin\SweepPress\Base\Sweep;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\Basic\Data;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\Query\Comments;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
abstract class CommentStatus extends Sweeper {
    protected string $_comment_status = '';

    protected string $_category = 'comments';

    protected int $_days_to_keep = 0;

    protected array $_affected_tables = array('commentmeta', 'comments');

    protected bool $_flag_single_task = false;

    protected bool $_flag_days_to_keep = true;

    public function __construct() {
        parent::__construct();
        $this->_days_to_keep = sweeppress_settings()->get( 'keep_days_comments-' . $this->_comment_status, 'sweepers', 14 );
    }

    public function help() : array {
        $help = array();
        if ( $this->_days_to_keep > 0 ) {
            $help[] = sprintf( __( 'This sweeper will keep related comments from the past %s days. You can adjust the days to keep value in the plugin Settings.', 'sweeppress' ), '<strong>' . $this->_days_to_keep . '</strong>' );
        }
        return $help;
    }

    public function items_count_n( int $value ) : string {
        return _n(
            '%s comment',
            '%s comments',
            $value,
            'sweeppress'
        );
    }

    public function list_tasks() : array {
        return $this->comment_types();
    }

    public function prepare() {
        foreach ( $this->comment_types() as $cpt => $label ) {
            $this->_tasks[$cpt] = array(
                'title'      => $label,
                'real_title' => $cpt,
                'items'      => 0,
                'records'    => 0,
                'size'       => 0,
            );
        }
        $this->_tasks = array_merge( $this->_tasks, Comments::instance()->comment_status( (array) $this->_comment_status, $this->_days_to_keep ) );
    }

    public function sweep( $tasks = array() ) : array {
        $results = array(
            'records' => 0,
            'size'    => 0,
            'tasks'   => array(),
        );
        $start = $this->get_tasks();
        $this->register_active_sweeper();
        $this->log_sweep_start();
        foreach ( $tasks as $name ) {
            $task = $start[$name] ?? array(
                'records' => 0,
            );
            if ( $task['records'] > 0 ) {
                $removal = Removal::instance()->comments_by_status( $this->_comment_status, $name, $this->_days_to_keep );
                if ( is_wp_error( $removal ) ) {
                    $results['tasks'][$name] = $removal;
                } else {
                    $results['tasks'][$name] = $task['title'];
                    $results['records'] += $task['records'];
                    $results['size'] += $task['size'];
                }
            }
        }
        $this->log_sweep_end();
        $this->unregister_active_sweeper();
        return $results;
    }

    protected function comment_types() : array {
        return Data::get_comment_types();
    }

}
