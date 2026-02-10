<?php

namespace Dev4Press\Plugin\SweepPress\Admin\Panel;

use Dev4Press\v54\Core\UI\Admin\PanelTools;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Tools extends PanelTools {
    public function __construct( $admin ) {
        parent::__construct( $admin );
    }

    public function show() {
        parent::show();
    }

    protected function init_default_subpanels() {
        parent::init_default_subpanels();
        $extra_panels = array(
            'purge' => array(
                'title'        => __( 'Cache and Storage Purge', 'sweeppress' ),
                'icon'         => 'ui-trash',
                'method'       => 'post',
                'button_label' => __( 'Purge', 'sweeppress' ),
                'info'         => __( 'Using this tool, you can purge all cached and scanning storage data.', 'sweeppress' ),
            ),
        );
        $this->subpanels = array_slice( $this->subpanels, 0, 2 ) + $extra_panels + array_slice( $this->subpanels, 2 );
    }

}
