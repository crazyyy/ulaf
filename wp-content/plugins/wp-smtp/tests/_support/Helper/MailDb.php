<?php

namespace Helper;

use Codeception\Exception\ModuleException;
use Codeception\Module;

class MailDb extends Module {

	public function haveSequentialLogsInDatabase( int $count = 5 ): void {
		global $wpdb;

		if ( ! $wpdb instanceof \wpdb ) {
			throw new ModuleException(
				$this,
				'WordPress database object is not available. WPLoader must be enabled first.'
			);
		}

		for ( $i = 1; $i <= $count; $i++ ) {
			$wpdb->insert(
				$wpdb->prefix . 'wpsmtp_logs',
				[
					// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					'timestamp' => date( 'Y-m-d H:i:s', strtotime( "2023-01-01 00:00:0{$i}" ) ),
					'to'        => "test{$i}@example.com",
					'subject'   => "Test Subject {$i}",
					'message'   => "Test Message {$i}",
					'headers'   => "Header {$i}",
					'error'     => "Error {$i}",
				]
			);
		}
	}
}
