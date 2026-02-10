<?php

namespace Integration\REST;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;
use SolidWP\Mail\Repository\ProvidersRepository;
use SolidWP\Mail\Connectors\ConnectorSMTP;
use WP_REST_Request;

class ConnectionsReadEndpointsTest extends WPRestApiTestCase {

	protected ProvidersRepository $repository;

	public function setUp(): void {
		parent::setUp();
		$this->createTestConnections();
		wp_set_current_user(
			$this->factory()->user->create( [ 'role' => 'administrator' ] )
		);
	}

	private function createTestConnections(): void {
		$repository  = new ProvidersRepository();
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
			$repository->save( $connection );
		}
	}

	public function testGetAllConnections(): void {
		$request  = new WP_REST_Request( 'GET', '/solid-mail/v1/connections' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertCount( 2, $data );
		
		// Sort by ID to ensure consistent order
		usort(
			$data,
			function ( $a, $b ) {
				return strcmp( $a['id'], $b['id'] );
			}
		);
		
		$this->assertEquals( 'test_smtp_1', $data[0]['id'] );
		$this->assertEquals( 'test1@example.com', $data[0]['from_email'] );
		$this->assertTrue( $data[0]['is_active'] );
		$this->assertEquals( 'test_smtp_2', $data[1]['id'] );
		$this->assertEquals( 'test2@example.com', $data[1]['from_email'] );
		$this->assertFalse( $data[1]['is_active'] );
	}

	public function testGetSingleConnection(): void {
		$request  = new WP_REST_Request( 'GET', '/solid-mail/v1/connections/test_smtp_1' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertEquals( 'test_smtp_1', $data['id'] );
		$this->assertEquals( 'test1@example.com', $data['from_email'] );
		$this->assertEquals( 'Test Sender 1', $data['from_name'] );
		$this->assertTrue( $data['is_active'] );
	}

	public function testGetNonExistentConnection(): void {
		$request  = new WP_REST_Request( 'GET', '/solid-mail/v1/connections/non_existent' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 404, $response->get_status() );
		$this->assertEquals( 'connection_not_found', $response->get_data()['code'] );
	}
}
