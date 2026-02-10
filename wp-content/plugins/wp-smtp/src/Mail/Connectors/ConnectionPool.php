<?php

namespace SolidWP\Mail\Connectors;

use ArrayIterator;
use InvalidArgumentException;

/**
 * A class representing a pool of connections.
 * Manages unique connections and ensures only valid ConnectorSMTP instances can be added.
 * 
 * @extends ArrayIterator<int, ConnectorSMTP>
 */
class ConnectionPool extends ArrayIterator {

	private array $connection_ids = [];

	public function offsetSet( $key, $value ): void {
		if ( $key !== null ) {
			throw new InvalidArgumentException( 'The pool must have sequential integer keys. Overriding connections is not allowed.' );
		}

		if ( ! $value instanceof ConnectorSMTP || ! $value->validation() ) {
			return;
		}

		if ( array_key_exists( $value->get_id(), $this->connection_ids ) ) {
			return;
		}

		$this->connection_ids[ $value->get_id() ] = $value->get_id();

		parent::offsetSet( $key, $value );
	}

	public function key(): ?int {
		$index = parent::key();
		if ( $index === null ) {
			return null;
		}

		if ( ! is_int( $index ) || $index < 0 ) {
			throw new InvalidArgumentException( 'Invalid offset.' );
		}

		return $index;
	}

	public function hasNext(): bool {
		if ( $this->key() === null ) {
			return false;
		}

		return $this->offsetExists( $this->key() + 1 );
	}
}
