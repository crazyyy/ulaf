<?php

defined( 'ABSPATH' ) || exit;

use BoltAudit\WpMVC\App;
use BoltAudit\DI\Container;

function boltaudit():App {
    return App::$instance;
}

function boltaudit_config( string $config_key ) {
    return boltaudit()::$config->get( $config_key );
}

function boltaudit_app_config( string $config_key ) {
    return boltaudit_config( "app.{$config_key}" );
}

function boltaudit_version() {
    return boltaudit_app_config( 'version' );
}

function boltaudit_container():Container {
    return boltaudit()::$container;
}

function boltaudit_singleton( string $class ) {
    return boltaudit_container()->get( $class );
}

function boltaudit_url( string $url = '' ) {
    return boltaudit()->get_url( $url );
}

function boltaudit_dir( string $dir = '' ) {
    return boltaudit()->get_dir( $dir );
}

function boltaudit_render( string $content ) {
    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $content;
}