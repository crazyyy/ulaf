<?php

namespace Integration\Db;

use IntegrationTester;
use lucatume\WPBrowser\TestCase\WPTestCase;
use SolidWP\Mail\Repository\LogsRepository;
use SolidWP\Mail\App;

/**
 * @property IntegrationTester $tester
 */
class LogsRepositoryTest extends WPTestCase {

	protected LogsRepository $repository;

	public function setUp(): void {
		parent::setUp();
		$this->repository = new LogsRepository();
		App::container()->setVar( 'LOGS_PER_PAGE', 2 ); // Set the log limit for pagination tests
		$this->tester->haveSequentialLogsInDatabase();
	}

	public function testGetEmailLogs(): void {
		// Test default sorting (by timestamp DESC) with pagination
		$logs = $this->repository->get_email_logs(
			[
				'orderby' => 'timestamp',
				'order'   => 'desc',
			]
		);

		$this->assertCount( 2, $logs ); // LOG_LIMIT is set to 2
		$this->assertEquals( 'Test Subject 5', $logs[0]['subject'] );
		$this->assertEquals( 'Test Subject 4', $logs[1]['subject'] );

		// Test sorting by timestamp ASC with pagination
		$logs = $this->repository->get_email_logs(
			[
				'orderby' => 'timestamp',
				'order'   => 'asc',
			] 
		);

		$this->assertCount( 2, $logs ); // LOG_LIMIT is set to 2
		$this->assertEquals( 'Test Subject 1', $logs[0]['subject'] );
		$this->assertEquals( 'Test Subject 2', $logs[1]['subject'] );
	}

	public function testPaging(): void {
		// Test first page
		$logs = $this->repository->get_email_logs(
			[
				'page'    => 1,
				'orderby' => 'timestamp',
				'order'   => 'desc',
			]
		);
		$this->assertCount( 2, $logs );
		$this->assertEquals( 'Test Subject 5', $logs[0]['subject'] );
		$this->assertEquals( 'Test Subject 4', $logs[1]['subject'] );

		// Test second page
		$logs = $this->repository->get_email_logs(
			[
				'page'    => 2,
				'orderby' => 'timestamp',
				'order'   => 'desc',
			]
		);
		$this->assertCount( 2, $logs );
		$this->assertEquals( 'Test Subject 3', $logs[0]['subject'] );
		$this->assertEquals( 'Test Subject 2', $logs[1]['subject'] );

		// Test third page
		$logs = $this->repository->get_email_logs(
			[
				'page'    => 3,
				'orderby' => 'timestamp',
				'order'   => 'desc',
			]
		);
		$this->assertCount( 1, $logs );
		$this->assertEquals( 'Test Subject 1', $logs[0]['subject'] );
	}

	public function testSearch(): void {
		$logs = $this->repository->get_email_logs( [ 'search_term' => 'Test Subject 1' ] );

		$this->assertCount( 1, $logs );
		$this->assertEquals( 'Test Subject 1', $logs[0]['subject'] );
	}


	public function testDeleteLog(): void {
		global $wpdb;

		$log_id  = $wpdb->get_var( "SELECT mail_id FROM {$wpdb->prefix}wpsmtp_logs WHERE subject = 'Test Subject 1'" );
		$deleted = $this->repository->delete_log( (int) $log_id );

		$this->assertTrue( $deleted );
		$total_logs = $this->repository->count_all_logs();
		$this->assertEquals( 4, $total_logs );
	}

	public function testCountAllLogs(): void {
		$total_logs = $this->repository->count_all_logs();

		$this->assertEquals( 5, $total_logs );
	}

	public function testGetEmailLogsByDate(): void {
		global $wpdb;

		// Get timestamps for the first and third logs
		$first_log_timestamp = $wpdb->get_var( "SELECT timestamp FROM {$wpdb->prefix}wpsmtp_logs WHERE subject = 'Test Subject 1'" );
		$third_log_timestamp = $wpdb->get_var( "SELECT timestamp FROM {$wpdb->prefix}wpsmtp_logs WHERE subject = 'Test Subject 3'" );

		$from_timestamp = strtotime( $first_log_timestamp );
		$to_timestamp   = strtotime( $third_log_timestamp );

		$logs = $this->repository->get_email_logs_by_date( $from_timestamp, $to_timestamp );

		// Should return logs 1, 2, and 3 (ordered by timestamp ASC)
		$this->assertCount( 3, $logs );
		$this->assertEquals( 'Test Subject 1', $logs[0]['subject'] );
		$this->assertEquals( 'Test Subject 2', $logs[1]['subject'] );
		$this->assertEquals( 'Test Subject 3', $logs[2]['subject'] );
	}

	public function testGetEmailLogsByDateWithInvalidParams(): void {
		$logs = $this->repository->get_email_logs_by_date( 0, 100 );
		$this->assertEmpty( $logs );

		$logs = $this->repository->get_email_logs_by_date( -1, 100 );
		$this->assertEmpty( $logs );

		$logs = $this->repository->get_email_logs_by_date( 200, 100 ); // from > to
		$this->assertEmpty( $logs );
	}

	public function testDeleteLogs(): void {
		global $wpdb;

		// Get IDs of first two logs
		$log_ids = $wpdb->get_col( "SELECT mail_id FROM {$wpdb->prefix}wpsmtp_logs WHERE subject IN ('Test Subject 1', 'Test Subject 2')" );

		$this->assertCount( 2, $log_ids );

		$deleted = $this->repository->delete_logs( $log_ids );
		$this->assertTrue( $deleted );

		// Verify logs are deleted
		$total_logs = $this->repository->count_all_logs();
		$this->assertEquals( 3, $total_logs );

		// Verify specific logs are gone
		$remaining_subjects = $wpdb->get_col( "SELECT subject FROM {$wpdb->prefix}wpsmtp_logs ORDER BY subject" );
		$this->assertEquals( [ 'Test Subject 3', 'Test Subject 4', 'Test Subject 5' ], $remaining_subjects );
	}

	public function testDeleteLogsWithEmptyArray(): void {
		$deleted = $this->repository->delete_logs( [] );
		$this->assertFalse( $deleted );

		// Verify no logs were deleted
		$total_logs = $this->repository->count_all_logs();
		$this->assertEquals( 5, $total_logs );
	}

	public function testClearAllLogs(): void {
		// Verify we have logs initially
		$total_logs = $this->repository->count_all_logs();
		$this->assertEquals( 5, $total_logs );

		$cleared = $this->repository->clear_all_logs();
		$this->assertTrue( $cleared );

		// Verify all logs are cleared
		$total_logs = $this->repository->count_all_logs();
		$this->assertEquals( 0, $total_logs );
	}
}
