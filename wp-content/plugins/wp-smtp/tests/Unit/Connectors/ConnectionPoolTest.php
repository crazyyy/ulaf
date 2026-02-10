<?php

namespace Unit\Connectors;

use InvalidArgumentException;
use lucatume\WPBrowser\TestCase\WPTestCase;
use SolidWP\Mail\Connectors\ConnectionPool;
use SolidWP\Mail\Connectors\ConnectorSMTP;

class ConnectionPoolTest extends WPTestCase {

	public function testOffsetSetWithValidConnector(): void {
		$pool       = new ConnectionPool();
		$connection = $this->createValidConnection( 'valid-connection' );

		$pool[] = $connection;

		self::assertCount( 1, $pool );
		self::assertSame( $connection, $pool->offsetGet( 0 ) );
	}

	public function testOffsetSetWithNullKey(): void {
		$pool       = new ConnectionPool();
		$connection = $this->createValidConnection( 'auto-key-connection' );

		$pool->offsetSet( null, $connection );

		self::assertCount( 1, $pool );
		self::assertSame( $connection, $pool->offsetGet( 0 ) );
	}

	public function testOverriding(): void {
		$pool       = new ConnectionPool();
		$connection = $this->createValidConnection( 'explicit-key-connection' );

		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The pool must have sequential integer keys.' );

		$pool[]  = $connection;
		$pool[0] = $connection;
	}

	public function testOffsetSetWithStringKey(): void {
		$pool       = new ConnectionPool();
		$connection = $this->createValidConnection( 'string-key-connection' );

		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The pool must have sequential integer keys.' );

		$pool['invalid'] = $connection;
	}

	public function testOffsetSetWithNonConnection(): void {
		$pool = new ConnectionPool();

		$pool[] = 'not-a-connection';

		self::assertCount( 0, $pool );
	}

	public function testOffsetSetWithInvalidConnection(): void {
		$pool   = new ConnectionPool();
		$pool[] = $this->createInvalidConnection();

		self::assertCount( 0, $pool );
	}

	public function testOffsetSetPreventsDuplicateIds(): void {
		$pool        = new ConnectionPool();
		$connection1 = $this->createValidConnection( 'duplicate-id' );
		$connection2 = $this->createValidConnection( 'duplicate-id' );

		$pool[] = $connection1;
		$pool[] = $connection2;

		self::assertCount( 1, $pool );
		self::assertSame( $connection1, $pool[0] );
		self::assertFalse( $pool->offsetExists( 1 ) );
	}

	public function testOffsetSetWithDifferentIds(): void {
		$pool        = new ConnectionPool();
		$connection1 = $this->createValidConnection( 'first-id' );
		$connection2 = $this->createValidConnection( 'second-id' );

		$pool[] = $connection1;
		$pool[] = $connection2;

		self::assertCount( 2, $pool );
		self::assertSame( $connection1, $pool[0] );
		self::assertSame( $connection2, $pool[1] );
	}

	public function testKeyReturnsValidInteger(): void {
		$pool       = new ConnectionPool();
		$connection = $this->createValidConnection( 'key-test' );

		$pool[] = $connection;

		self::assertSame( 0, $pool->key() );
	}

	public function testHasNextWithSingleItem(): void {
		$pool       = new ConnectionPool();
		$connection = $this->createValidConnection( 'single-item' );

		$pool[] = $connection;
		$pool->rewind();

		self::assertFalse( $pool->hasNext() );
	}

	public function testHasNextWithMultipleItems(): void {
		$pool        = new ConnectionPool();
		$connection1 = $this->createValidConnection( 'first' );
		$connection2 = $this->createValidConnection( 'second' );

		$pool[] = $connection1;
		$pool[] = $connection2;
		$pool->rewind();

		self::assertTrue( $pool->hasNext() );

		$pool->next();
		self::assertFalse( $pool->hasNext() );
	}

	public function testHasNextWithEmptyPool(): void {
		$pool = new ConnectionPool();

		self::assertFalse( $pool->hasNext() );
	}

	public function testIteratorFunctionality(): void {
		$pool        = new ConnectionPool();
		$connection1 = $this->createValidConnection( 'iter-first' );
		$connection2 = $this->createValidConnection( 'iter-second' );
		$connection3 = $this->createValidConnection( 'iter-third' );

		$pool[] = $connection1;
		$pool[] = $connection2;
		$pool[] = $connection3;

		$iterations  = 0;
		$connections = [];

		foreach ( $pool as $key => $connection ) {
			++$iterations;
			$connections[ $key ] = $connection;
		}

		self::assertSame( 3, $iterations );
		self::assertCount( 3, $connections );
		self::assertSame( $connection1, $connections[0] );
		self::assertSame( $connection2, $connections[1] );
		self::assertSame( $connection3, $connections[2] );
	}

	public function testCountFunctionality(): void {
		$pool = new ConnectionPool();

		self::assertCount( 0, $pool );

		$connection1 = $this->createValidConnection( 'count-first' );
		$pool[]      = $connection1;

		self::assertCount( 1, $pool );

		$connection2 = $this->createValidConnection( 'count-second' );
		$pool[]      = $connection2;

		self::assertCount( 2, $pool );
	}

	public function testArrayAccessFunctionalityWithExplicitKey(): void {
		$pool       = new ConnectionPool();
		$connection = $this->createValidConnection( 'array-access-connection' );

		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Overriding connections is not allowed.' );

		$pool[0] = $connection;
	}

	public function testArrayAccessFunctionalityWithImplicitKey(): void {
		$pool       = new ConnectionPool();
		$connection = $this->createValidConnection( 'array-access-connection' );

		$pool[] = $connection;

		self::assertTrue( isset( $pool[0] ) );
		self::assertSame( $connection, $pool[0] );

		unset( $pool[0] );
		self::assertFalse( isset( $pool[0] ) );
		self::assertCount( 0, $pool );
	}

	private function createValidConnection( string $id = 'test-id' ): ConnectorSMTP {
		$data = [
			'id'            => $id,
			'name'          => 'Test Provider',
			'description'   => 'A test SMTP provider',
			'from_email'    => 'test@example.com',
			'from_name'     => 'Test Sender',
			'smtp_host'     => 'smtp.example.com',
			'smtp_port'     => '587',
			'smtp_auth'     => 'yes',
			'smtp_username' => 'user',
			'smtp_password' => 'pass',
			'smtp_secure'   => 'tls',
			'is_active'     => true,
		];

		return new ConnectorSMTP( $data );
	}

	private function createInvalidConnection(): ConnectorSMTP {
		// Create a connection with missing required fields to make validation fail
		$data = [
			'id' => 'invalid-id',
		];

		return new ConnectorSMTP( $data );
	}
}
