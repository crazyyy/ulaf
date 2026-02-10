<?php

namespace Dev4Press\Plugin\SweepPress\Admin\Panel;

use Dev4Press\Plugin\SweepPress\Admin\Panel;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Sitemeta extends Panel {
    protected bool $table = true;

    public function __construct( $admin ) {
        parent::__construct( $admin );
        $this->cards = true;
    }

    public function screen_options_show() {
        add_screen_option( 'per_page', array(
            'label'   => __( 'Rows', 'sweeppress' ),
            'default' => 50,
            'option'  => 'sweeppress_sitemeta_rows_per_page',
        ) );
        $this->get_table_object();
    }

    public function get_table_object() {
        if ( is_null( $this->table_object ) && class_exists( '\\Dev4Press\\Plugin\\SweepPress\\Pro\\Table\\Sitemeta' ) ) {
            $this->table_object = new \Dev4Press\Plugin\SweepPress\Pro\Table\Sitemeta();
        }
        return $this->table_object;
    }

}
