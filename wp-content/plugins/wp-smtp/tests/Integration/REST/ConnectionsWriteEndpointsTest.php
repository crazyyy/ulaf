<?php

namespace Integration\REST;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;
use SolidWP\Mail\Repository\ProvidersRepository;
use SolidWP\Mail\Connectors\ConnectorSMTP;
use WP_REST_Request;

class ConnectionsWriteEndpointsTest extends WPRestApiTestCase {

	protected ProvidersRepository $repository;

	public function setUp(): void {
		parent::setUp();
		$this->repository = new ProvidersRepository();
		$this->createTestConnections();
		wp_set_current_user(
			$this->factory()->user->create( [ 'role' => 'administrator' ] )
		);
	}

	protected function createTestConnections(): void {
		$connections = [
			new ConnectorSMTP(
				[
					'id'            => 'test_smtp_1',
					'name'          => 'other',
					'from_email'    => 'test1@example.com',
					'from_name'     => 'Test Sender 1',
					'smtp_host'     => 'smtp.example.com',
					'smtp_port'     => '587',
					'smtp_auth'     => 'yes',
					'smtp_username' => 'user1',
					'smtp_password' => 'pass1',
					'smtp_secure'   => 'tls',
					'is_active'     => true,
				]
			),
			new ConnectorSMTP(
				[
					'id'            => 'test_smtp_2',
					'name'          => 'other',
					'from_email'    => 'test2@example.com',
					'from_name'     => 'Test Sender 2',
					'smtp_host'     => 'smtp2.example.com',
					'smtp_port'     => '465',
					'smtp_auth'     => 'yes',
					'smtp_username' => 'user2',
					'smtp_password' => 'pass2',
					'smtp_secure'   => 'ssl',
					'is_active'     => false,
				]
			),
		];

		foreach ( $connections as $connection ) {
			$this->repository->save( $connection );
		}
	}

	protected function createAdminUser(): int {
		return $this->factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public function testCreateConnection(): void {
		$request = new WP_REST_Request( 'POST', '/solid-mail/v1/connections' );
		$request->set_param( 'name', 'other' );
		$request->set_param( 'from_email', 'new@example.com' );
		$request->set_param( 'from_name', 'New Sender' );
		$request->set_param( 'smtp_host', 'smtp.new.com' );
		$request->set_param( 'smtp_port', '587' );
		$request->set_param( 'smtp_auth', 'yes' );
		$request->set_param( 'smtp_username', 'newuser' );
		$request->set_param( 'smtp_password', 'newpass' );
		$request->set_param( 'smtp_secure', 'tls' );

		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 201, $response->get_status() );
		$this->assertEquals( 'other', $data['name'] );
		$this->assertEquals( 'new@example.com', $data['from_email'] );
		$this->assertEquals( 'New Sender', $data['from_name'] );
		$this->assertFalse( $data['is_active'] );

		$saved_connection = $this->repository->get_provider_by_id( $data['id'] );
		$this->assertNotNull( $saved_connection );
		$this->assertEquals( 'new@example.com', $saved_connection->get_from_email() );
	}

	public function testCreateConnectionWithoutName(): void {
		$request = new WP_REST_Request( 'POST', '/solid-mail/v1/connections' );
		$request->set_param( 'from_email', 'test@example.com' );

		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );
		// WordPress REST API returns this error code for missing required parameters
		$this->assertEquals( 'rest_missing_callback_param', $response->get_data()['code'] );
	}

	public function testCreateConnectionWithInvalidData(): void {
		$request = new WP_REST_Request( 'POST', '/solid-mail/v1/connections' );
		$request->set_param( 'name', 'other' );
		$request->set_param( 'from_email', 'invalid-email' );
		$request->set_param( 'from_name', 'Test' );

		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );
		// WordPress REST API returns this error code for invalid parameter format
		$this->assertEquals( 'rest_invalid_param', $response->get_data()['code'] );
	}

	public function testUpdateConnection(): void {
		$request = new WP_REST_Request( 'PUT', '/solid-mail/v1/connections/test_smtp_2' );
		$request->set_param( 'from_email', 'updated@example.com' );
		$request->set_param( 'from_name', 'Updated Sender' );

		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertEquals( 'test_smtp_2', $data['id'] );
		$this->assertEquals( 'updated@example.com', $data['from_email'] );
		$this->assertEquals( 'Updated Sender', $data['from_name'] );

		$updated_connection = $this->repository->get_provider_by_id( 'test_smtp_2' );
		$this->assertEquals( 'updated@example.com', $updated_connection->get_from_email() );
		$this->assertEquals( 'Updated Sender', $updated_connection->get_from_name() );
	}

	public function testActivateConnection(): void {
		$request = new WP_REST_Request( 'PUT', '/solid-mail/v1/connections/test_smtp_2' );
		$request->set_param( 'is_active', true );

		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertEquals( 'test_smtp_2', $data['id'] );
		$this->assertTrue( $data['is_active'] );
		// If there was no default connection, it becomes default
		$this->assertTrue( $data['is_default'] );

		$updated_connection = $this->repository->get_provider_by_id( 'test_smtp_2' );
		$this->assertTrue( $updated_connection->is_active() );
		$this->assertTrue( $updated_connection->is_default() );

		$request = new WP_REST_Request( 'PUT', '/solid-mail/v1/connections/test_smtp_2' );
		$request->set_param( 'is_active', false );

		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertEquals( 'test_smtp_2', $data['id'] );
		$this->assertFalse( $data['is_active'] );
		// Inactive connection can't be a default
		$this->assertFalse( $data['is_default'] );

		$updated_connection = $this->repository->get_provider_by_id( 'test_smtp_2' );
		$this->assertFalse( $updated_connection->is_active() );
		$this->assertFalse( $updated_connection->is_default() );
	}

	public function testMakeConnectionDefault(): void {
		$request = new WP_REST_Request( 'PUT', '/solid-mail/v1/connections/test_smtp_2' );
		$request->set_param( 'is_default', true );

		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertEquals( 'test_smtp_2', $data['id'] );
		// If the connection a default, it was activated
		$this->assertTrue( $data['is_active'] );
		$this->assertTrue( $data['is_default'] );

		$updated_connection = $this->repository->get_provider_by_id( 'test_smtp_2' );
		$this->assertTrue( $updated_connection->is_active() );
		$this->assertTrue( $updated_connection->is_default() );

		$request = new WP_REST_Request( 'PUT', '/solid-mail/v1/connections/test_smtp_2' );
		$request->set_param( 'is_default', false );

		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertSame( 200, $response->get_status() );
		$this->assertEquals( 'test_smtp_2', $data['id'] );
		// Connection is not default but still active
		$this->assertTrue( $data['is_active'] );
		$this->assertFalse( $data['is_default'] );

		$updated_connection = $this->repository->get_provider_by_id( 'test_smtp_2' );
		$this->assertTrue( $updated_connection->is_active() );
		$this->assertFalse( $updated_connection->is_default() );
	}

	public function testUpdateNonExistentConnection(): void {
		$request = new WP_REST_Request( 'PUT', '/solid-mail/v1/connections/non_existent' );
		$request->set_param( 'from_email', 'test@example.com' );

		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 404, $response->get_status() );
		$this->assertEquals( 'connection_not_found', $response->get_data()['code'] );
	}

	public function testUpdateConnectionWithInvalidData(): void {
		$request = new WP_REST_Request( 'PUT', '/solid-mail/v1/connections/test_smtp_2' );
		$request->set_param( 'from_email', 'invalid-email' );

		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );
		// WordPress REST API returns this error code for invalid parameter format
		$this->assertEquals( 'rest_invalid_param', $response->get_data()['code'] );
	}

	public function testDeleteConnection(): void {
		$request  = new WP_REST_Request( 'DELETE', '/solid-mail/v1/connections/test_smtp_2' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 204, $response->get_status() );
		$this->assertNull( $response->get_data() );

		$deleted_connection = $this->repository->get_provider_by_id( 'test_smtp_2' );
		$this->assertNull( $deleted_connection );
	}

	public function testDeleteActiveConnection(): void {
		$request  = new WP_REST_Request( 'DELETE', '/solid-mail/v1/connections/test_smtp_1' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );
		$this->assertEquals( 'cannot_delete_active', $response->get_data()['code'] );

		$connection = $this->repository->get_provider_by_id( 'test_smtp_1' );
		$this->assertNotNull( $connection );
	}

	public function testDeleteNonExistentConnection(): void {
		$request  = new WP_REST_Request( 'DELETE', '/solid-mail/v1/connections/non_existent' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 404, $response->get_status() );
		$this->assertEquals( 'connection_not_found', $response->get_data()['code'] );
	}
}
