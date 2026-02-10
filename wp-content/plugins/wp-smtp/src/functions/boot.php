<?php
/**
 * Initializes the Solid SMTP plugin once all plugins have loaded.
 *
 * @return void
 */

use SolidWP\Mail\App;
use SolidWP\Mail\Container;
use SolidWP\Mail\Core;
use SolidWP\Mail\lucatume\DI52\Container as DI52Container;

function solid_mail_plugin(): Core {
	$container = new Container( new DI52Container() );
	$core      = Core::instance( realpath( __DIR__ . '/../../wp-smtp.php' ), $container );

	App::setContainer( $core->container() );

	return $core;
}
