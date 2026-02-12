<?php

namespace WPStaging\Pro\Notices;

use WPStaging\Framework\Notices\BooleanNotice;

/**
 * Class EntireNetworkCloneServerConfigNotice
 *
 * Show dismissable notice on entire network clone about setting up configuration to allow subdirectory staging network subsites
 *
 * @see \WPStaging\Pro\Notices\Notices;
 */
class EntireNetworkCloneServerConfigNotice extends BooleanNotice
{
    /**
     * The option name to store the visibility of this notice
     */
    const OPTION_NAME = 'wpstg_entire_network_clone_notice';

    public function getOptionName()
    {
        return self::OPTION_NAME;
    }
}
