<?php

namespace Dev4Press\Plugin\SweepPress\Basic;

use Dev4Press\v54\Core\Plugins\Information as BaseInformation;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Information extends BaseInformation {
    public string $code = 'sweeppress';

    public string $version = '6.4.4';

    public int $build = 6440;

    public string $updated = '2025.11.12';

    public string $status = 'stable';

    public string $edition = 'pro';

    public string $released = '2022.03.03';

    public function __construct() {
        parent::__construct();
    }

}
