<?php

namespace Integration\REST;

use IntegrationTester;
use lucatume\WPBrowser\TestCase\WPRestApiTestCase;
use SolidWP\Mail\Repository\LogsRepository;
use WP_REST_Request;

/**
 * @property IntegrationTester $tester
 */
class LogsEndpointsTest extends WPRestApiTestCase {

	protected LogsRepository $repository;

	public function setUp(): void {
		parent::setUp();
		$this->repository = new LogsRepository();
		$this->tester->haveSequentialLogsInDatabase();
	}

	public function testUnloggedUserCanNotSeeLogs(): void {
		wp_set_current_user( 0 );

		$request  = new WP_REST_Request( 'GET', '/solidwp-mail/v1/logs' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 401, $response->get_status() );
	}

	public function testUserWithoutManageOptionsCapabilityCanNotSeeLogs(): void {
		wp_set_current_user(
			$this->factory()->user->create( [ 'role' => 'editor' ] )
		);

		$request  = new WP_REST_Request( 'GET', '/solidwp-mail/v1/logs' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
	}

	public function testLogsFetch(): void {
		wp_set_current_user(
			$this->factory()->user->create( [ 'role' => 'administrator' ] )
		);

		$request = new WP_REST_Request( 'GET', '/solidwp-mail/v1/logs' );
		$request->set_param( 'page', 0 );
		$request->set_param( 'sortby', 'timestamp' );
		$request->set_param( 'sort', 'desc' );
		$request->set_param( 'per_page', 2 );
		$response = rest_get_server()->dispatch( $request );
		$logs     = $response->get_data()['logs'];

		$this->assertSame( 200, $response->get_status() );
		$this->assertCount( 2, $logs );
		$this->assertEquals( 3, $response->get_headers()['X-WP-TotalPages'] );
		$this->assertEquals( 5, $response->get_headers()['X-WP-Total'] );
		$this->assertEquals( 'Test Subject 5', $logs[0]['subject'] );
		$this->assertEquals( 'Test Subject 4', $logs[1]['subject'] );
	}

	public function testLogsDelete(): void {
		global $wpdb;

		wp_set_current_user(
			$this->factory()->user->create( [ 'role' => 'administrator' ] )
		);

		$log_id  = $wpdb->get_var( "SELECT mail_id FROM {$wpdb->prefix}wpsmtp_logs WHERE subject = 'Test Subject 1'" );
		$request = new WP_REST_Request( 'DELETE', '/solidwp-mail/v1/logs/delete' );
		$request->set_param( 'logIds', [ $log_id ] );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 204, $response->get_status() );

		// Verify log is deleted
		$count = $this->repository->count_all_logs();
		$this->assertEquals( 4, $count );
	}

	public function testClearAllLogs(): void {
		wp_set_current_user(
			$this->factory()->user->create( [ 'role' => 'administrator' ] )
		);

		// Verify we have logs initially
		$initial_count = $this->repository->count_all_logs();
		$this->assertEquals( 5, $initial_count );

		$request  = new WP_REST_Request( 'DELETE', '/solidwp-mail/v1/logs' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 204, $response->get_status() );

		// Verify all logs are cleared
		$final_count = $this->repository->count_all_logs();
		$this->assertEquals( 0, $final_count );
	}

	public function testClearAllLogsUnauthorized(): void {
		wp_set_current_user( 0 );

		$request  = new WP_REST_Request( 'DELETE', '/solidwp-mail/v1/logs' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 401, $response->get_status() );

		// Verify logs are not cleared
		$count = $this->repository->count_all_logs();
		$this->assertEquals( 5, $count );
	}

	public function testClearAllLogsWithoutManageOptionsCapability(): void {
		wp_set_current_user(
			$this->factory()->user->create( [ 'role' => 'editor' ] )
		);

		$request  = new WP_REST_Request( 'DELETE', '/solidwp-mail/v1/logs' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );

		// Verify logs are not cleared
		$count = $this->repository->count_all_logs();
		$this->assertEquals( 5, $count );
	}
}
