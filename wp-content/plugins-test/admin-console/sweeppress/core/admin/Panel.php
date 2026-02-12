<?php

namespace Dev4Press\Plugin\SweepPress\Admin;

use Dev4Press\v54\Core\UI\Admin\Panel as BasePanel;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Panel extends BasePanel {
    public function __construct( $admin ) {
        parent::__construct( $admin );
    }

}
