<?php

namespace Dev4Press\Plugin\SweepPress\Base\Table;

use Dev4Press\Plugin\SweepPress\DB\Shared;
use Dev4Press\Plugin\SweepPress\Meta\Helper;
use Dev4Press\v54\Core\Plugins\DBLite;
use Dev4Press\v54\Core\Quick\Sanitize;
use Dev4Press\v54\Core\UI\Elements;
use Dev4Press\v54\WordPress\Admin\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Options extends Table {
	public int $_rows_per_page_default = 50;

	protected $_filter_options = array();
	protected $_filter_core = array();
	protected $_filter_prefixes = array();
	protected bool $_show_activity = true;
	protected bool $_show_autoload = false;

	public function prepare_table() {
		parent::prepare_table();

		$this->prepare();
	}

	protected function db() : ?DBLite {
		return Shared::instance();
	}

	protected function prepare() {
		$this->_filter_options = $this->get_option_names();

		$filters = Helper::_find_prefixes( $this->_filter_options );

		$this->_filter_core     = $filters['core'];
		$this->_filter_prefixes = $filters['prefixes'];
	}

	protected function process_request_args() {
		$this->_request_args = array(
			'filter-source'   => Sanitize::_get_text( 'filter-source' ),
			'filter-status'   => Sanitize::_get_text( 'filter-status' ),
			'filter-prefix'   => Sanitize::_get_text( 'filter-prefix' ),
			'filter-autoload' => Sanitize::_get_text( 'filter-autoload' ),
			'search'          => $this->_get_field( 's' ),
			'orderby'         => $this->_get_field( 'orderby', $this->_checkbox_field ),
			'order'           => $this->_get_field( 'order', 'ASC' ),
			'paged'           => $this->_get_field( 'paged', 1 ),
		);
	}

	protected function filter_block_top() {
		echo '<div class="alignleft actions">';

		$_list_prefixes = array( '' => __( 'All Prefixes', 'sweeppress' ) );

		foreach ( $this->_filter_prefixes as $item ) {
			$_list_prefixes[ $item ] = $item;
		}

		$_list_sources = array(
			''          => __( 'All Sources', 'sweeppress' ),
			'plugin'    => __( 'Plugins', 'sweeppress' ),
			'theme'     => __( 'Themes', 'sweeppress' ),
			'component' => __( 'Components', 'sweeppress' ),
			'cache'     => __( 'Cache', 'sweeppress' ),
			'widget'    => __( 'Widgets', 'sweeppress' ),
			'known'     => __( 'All Known', 'sweeppress' ),
			'unknown'   => __( 'Unknown', 'sweeppress' ),
		);

		$_list_statuses = array(
			''          => __( 'All Statuses', 'sweeppress' ),
			'missing'   => __( 'Missing', 'sweeppress' ),
			'installed' => __( 'Installed', 'sweeppress' ),
			'active'    => __( 'Installed & Active', 'sweeppress' ),
		);

		if ( ! $this->_show_activity ) {
			unset( $_list_statuses['active'] );
		}

		Elements::instance()->select( $_list_sources, array(
			'selected' => $this->get_request_arg( 'filter-source' ),
			'name'     => 'filter-source',
		) );

		Elements::instance()->select( $_list_statuses, array(
			'selected' => $this->get_request_arg( 'filter-status' ),
			'name'     => 'filter-status',
		) );

		Elements::instance()->select( $_list_prefixes, array(
			'selected' => $this->get_request_arg( 'filter-prefix' ),
			'name'     => 'filter-prefix',
		) );

		if ( $this->_show_autoload ) {
			$_list_autoload = array(
				''    => __( 'All Autoload', 'sweeppress' ),
				'yes' => __( 'Yes', 'sweeppress' ),
				'no'  => __( 'No', 'sweeppress' ),
			);

			Elements::instance()->select( $_list_autoload, array(
				'selected' => $this->get_request_arg( 'filter-autoload' ),
				'name'     => 'filter-autoload',
			) );
		}

		submit_button( __( 'Filter', 'sweeppress' ), 'button', false, false, array( 'id' => 'sweeppress-tables-submit' ) );
		echo '</div>';
	}

	protected function get_bulk_actions() : array {
		return array(
			'delete' => __( 'Delete', 'sweeppress' ),
		);
	}

	abstract protected function get_option_names() : array;
}
