<?php

namespace Integration\REST;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;
use WP_REST_Request;

class ConnectionsPermissionsTest extends WPRestApiTestCase {
	public function testUnloggedUserCanNotAccessConnections(): void {
		wp_set_current_user( 0 );

		$request  = new WP_REST_Request( 'GET', '/solid-mail/v1/connections' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 401, $response->get_status() );
	}

	public function testUserWithoutManageOptionsCapabilityCanNotAccessConnections(): void {
		wp_set_current_user( $this->createEditorUser() );

		$request  = new WP_REST_Request( 'GET', '/solid-mail/v1/connections' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
	}

	public function testPermissionsForReadEndpoints(): void {
		$read_endpoints = [
			[ 'GET', '/solid-mail/v1/connections' ],
			[ 'GET', '/solid-mail/v1/connections/test_smtp_1' ],
		];

		foreach ( $read_endpoints as [ $method, $route ] ) {
			wp_set_current_user( 0 );
			$request  = new WP_REST_Request( $method, $route );
			$response = rest_get_server()->dispatch( $request );
			$this->assertSame( 401, $response->get_status(), "Failed for {$method} {$route}" );

			wp_set_current_user( $this->createEditorUser() );
			$request  = new WP_REST_Request( $method, $route );
			$response = rest_get_server()->dispatch( $request );
			$this->assertSame( 403, $response->get_status(), "Failed for {$method} {$route}" );
		}
	}

	public function testPermissionsForWriteEndpoints(): void {
		$write_endpoints = [
			[ 'PUT', '/solid-mail/v1/connections/test_smtp_1' ],
			[ 'DELETE', '/solid-mail/v1/connections/test_smtp_2' ],
		];

		foreach ( $write_endpoints as [ $method, $route ] ) {
			wp_set_current_user( 0 );
			$request  = new WP_REST_Request( $method, $route );
			$response = rest_get_server()->dispatch( $request );
			$this->assertSame( 401, $response->get_status(), "Failed for {$method} {$route}" );

			wp_set_current_user( $this->createEditorUser() );
			$request  = new WP_REST_Request( $method, $route );
			$response = rest_get_server()->dispatch( $request );
			$this->assertSame( 403, $response->get_status(), "Failed for {$method} {$route}" );
		}
	}

	public function testPermissionsForCreateEndpoint(): void {
		// POST endpoint behaves differently - it validates parameters before checking permissions
		// when no name is provided, so we need to provide valid data to test permissions
		wp_set_current_user( 0 );
		$request = new WP_REST_Request( 'POST', '/solid-mail/v1/connections' );
		$request->set_param( 'name', 'other' );
		$request->set_param( 'from_email', 'test@example.com' );
		$request->set_param( 'from_name', 'Test' );
		$request->set_param( 'smtp_host', 'smtp.test.com' );
		$request->set_param( 'smtp_port', '587' );
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 401, $response->get_status() );

		wp_set_current_user( $this->createEditorUser() );
		$request = new WP_REST_Request( 'POST', '/solid-mail/v1/connections' );
		$request->set_param( 'name', 'other' );
		$request->set_param( 'from_email', 'test@example.com' );
		$request->set_param( 'from_name', 'Test' );
		$request->set_param( 'smtp_host', 'smtp.test.com' );
		$request->set_param( 'smtp_port', '587' );
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 403, $response->get_status() );
	}

	private function createEditorUser(): int {
		return $this->factory()->user->create( [ 'role' => 'editor' ] );
	}
}
