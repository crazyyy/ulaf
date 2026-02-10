<?php


namespace Kaizencoders\Utilitify;


use KaizenCoders\WpFluent\Connection;
use KaizenCoders\WpFluent\QueryBuilder\QueryBuilderHandler;

class Query {
	/**
	 * Query Object
	 *
	 * @return QueryBuilderHandler|mixed
	 *
	 * @since 1.0.5
	 */
	public static function boot() {
		static $query;

		if ( ! $query ) {
			global $wpdb;

			$connection = new Connection( $wpdb, [
				'prefix' => $wpdb->prefix,
			] );

			$query = new QueryBuilderHandler( $connection );
		}

		return $query;
	}
}