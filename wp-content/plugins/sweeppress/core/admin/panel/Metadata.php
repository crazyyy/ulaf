<?php

namespace Dev4Press\Plugin\SweepPress\Admin\Panel;

use Dev4Press\Plugin\SweepPress\Admin\Panel;
use Dev4Press\Plugin\SweepPress\Pro\Meta\Tables;
use Dev4Press\Plugin\SweepPress\Pro\Table\Duplicates as TableDuplicates;
use Dev4Press\Plugin\SweepPress\Pro\Table\Metadata as TableMetadata;
use Dev4Press\Plugin\SweepPress\Pro\Table\Preview as TablePreview;
use Dev4Press\v54\Core\Quick\Sanitize;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Metadata extends Panel {
    public bool $cards = true;

    public string $results = '';

    public string $metadata = '';

    public string $preview = '';

    public array $subtitles;

    public function __construct( $admin ) {
        parent::__construct( $admin );
    }

    public function screen_options_show() {
        if ( $this->table ) {
            add_screen_option( 'per_page', array(
                'label'   => __( 'Rows', 'sweeppress' ),
                'default' => 50,
                'option'  => 'sweeppress_metadata_rows_per_page',
            ) );
            $this->get_table_object();
        }
    }

    public function get_table_object() {
        if ( is_null( $this->table_object ) ) {
            if ( $this->results == 'metadata' && class_exists( '\\Dev4Press\\Plugin\\SweepPress\\Pro\\Table\\Metadata' ) ) {
                $this->table_object = new TableMetadata(array(
                    'metadata' => $this->metadata,
                ));
            } else {
                if ( $this->results == 'preview' && class_exists( 'Dev4Press\\Plugin\\SweepPress\\Pro\\Table\\Preview' ) ) {
                    $this->table_object = new TablePreview(array(
                        'metadata' => $this->metadata,
                        'meta_key' => $this->preview,
                    ));
                } else {
                    if ( $this->results == 'duplicates' && class_exists( 'Dev4Press\\Plugin\\SweepPress\\Pro\\Table\\Duplicates' ) ) {
                        $this->table_object = new TableDuplicates(array(
                            'metadata' => $this->metadata,
                        ));
                    }
                }
            }
        }
        return $this->table_object;
    }

    protected function get_subpanel_suffix( $subname = '' ) : string {
        return ( empty( $this->results ) ? 'index' : $this->results );
    }

}
