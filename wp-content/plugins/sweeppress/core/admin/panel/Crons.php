<?php

namespace Dev4Press\Plugin\SweepPress\Admin\Panel;

use Dev4Press\Plugin\SweepPress\Admin\Panel;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Crons extends Panel {
    protected bool $table = true;

    public function __construct( $admin ) {
        parent::__construct( $admin );
        $this->cards = true;
    }

    public function screen_options_show() {
        $this->get_table_object();
    }

    public function get_table_object() {
        if ( is_null( $this->table_object ) && class_exists( '\\Dev4Press\\Plugin\\SweepPress\\Pro\\Table\\Crons' ) ) {
            $this->table_object = new \Dev4Press\Plugin\SweepPress\Pro\Table\Crons();
        }
        return $this->table_object;
    }

}
