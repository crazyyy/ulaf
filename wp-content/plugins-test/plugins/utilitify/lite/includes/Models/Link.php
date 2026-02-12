<?php

namespace Kaizencoders\Utilitify\Models;


class Link {

	public $table = 'kc_uf_links';

	public $query = null;

	public function __construct() {
		$this->query = KC_UF()->query->table( $this->table );
	}

	/**
	 * Insert links
	 *
	 * @param array $links
	 *
	 * @since 1.0.5
	 */
	public function insert( $links = array() ) {

		if ( ! is_array( $links ) ) {
			$links[] = $links;
		}

		$this->query->insert( $links );
	}

	public function getAll() {

	}

}