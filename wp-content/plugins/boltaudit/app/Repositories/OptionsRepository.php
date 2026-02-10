<?php

namespace BoltAudit\App\Repositories;

use BoltAudit\App\Models\Options;

class OptionsRepository {
	public static function get_all_options( string $context = null ): array {
		$query = Options::query();

		if ( $context ) {
			$query->where( 'context', $context );
		}

		return $query->order_by( 'created', 'DESC' )->get();
	}

	public static function get_option( string $key, string $context ): ?array {
		$result = Options::query()
			->where( 'context', $context )
			->where( 'option_key', $key )
			->first();

		return $result ? (array) $result : null;
	}

	public static function update_option( string $key, string $context, array $data ): bool {
		return Options::query()
			->where( 'context', $context )
			->where( 'option_key', $key )
			->update( [
				'data'   => json_encode( $data ),
				'expire' => null,
			] ) > 0;
	}

	public static function create_option( string $key, string $context, array $data, ?string $expire = null ): bool {
		return Options::query()->insert( [
			'option_key' => $key,
			'context'    => $context,
			'data'       => json_encode( $data ),
			'created'    => date( 'Y-m-d' ),
			'expire'     => $expire,
		] );
	}

	public static function delete_option( string $key, string $context ): bool {
		return Options::query()
			->where( 'context', $context )
			->where( 'option_key', $key )
			->delete() > 0;
	}
}