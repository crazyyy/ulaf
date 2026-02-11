<?php

namespace BoltAudit\App\Setup;

defined( 'ABSPATH' ) || exit;

use BoltAudit\Database\Migrations\CreateDB;

class Activation {
	public function __construct() {
		( new CreateDB )->execute();
	}
}