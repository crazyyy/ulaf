<?php

namespace Dev4Press\Plugin\SweepPress\Admin\Panel;

use Dev4Press\Plugin\SweepPress\Admin\Panel;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Backups extends Panel {
    protected bool $table = true;

    public function __construct( $admin ) {
        parent::__construct( $admin );
        $this->cards = true;
        $this->table = false;
    }

    public function screen_options_show() {
        if ( $this->table ) {
            add_screen_option( 'per_page', array(
                'label'   => __( 'Rows', 'sweeppress' ),
                'default' => 25,
                'option'  => 'sweeppress_backups_rows_per_page',
            ) );
            $this->get_table_object();
        }
    }

    public function get_table_object() {
        if ( is_null( $this->table_object ) && class_exists( '\\Dev4Press\\Plugin\\SweepPress\\Pro\\Table\\Backups' ) ) {
            $this->table_object = new \Dev4Press\Plugin\SweepPress\Pro\Table\Backups();
        }
        return $this->table_object;
    }

}
