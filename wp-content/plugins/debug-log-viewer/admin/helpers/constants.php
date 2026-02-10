<?php

namespace DebugLogViewer\Admin\Helpers;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Constants {

	const WEEK_IN_SECONDS      = 604800;   // 7 * 24 * 60 * 60
	const TWO_WEEK_IN_SECONDS  = 1209600;  // 14 * 24 * 60 * 60
	const TWO_MONTH_IN_SECONDS = 5184000;  // 60 * 24 * 60 * 60 (2 months as 60 days)
	const SIX_MONTH_IN_SECONDS = 15552000; // 180 * 24 * 60 * 60 (6 months as 180 days)
	const ONE_YEAR_IN_SECONDS  = 31536000; // 365 * 24 * 60 * 60 (1 year)

	const CONTROLLERS_LIST = [ 'Hooks', 'Menu', 'Service', 'Log', 'Alert', 'Review', 'Freemius' ];
}
