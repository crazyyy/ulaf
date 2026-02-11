<?php

namespace Dev4Press\Plugin\SweepPress\Table;

use Dev4Press\Plugin\SweepPress\Base\Table\Options as TableOptions;
use Dev4Press\Plugin\SweepPress\DB\Shared;
use Dev4Press\Plugin\SweepPress\Meta\Helper;
use Dev4Press\Plugin\SweepPress\Meta\Options as CoreOptions;
use Dev4Press\v54\Core\Quick\Sanitize;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Options extends TableOptions {
	public string $_table_class_name = 'sweeppress-table-options';
	public string $_self_nonce_key = 'sweeppress-panel-options';
	public string $_rows_per_page_key = 'sweeppress_options_rows_per_page';
	public string $_checkbox_field = 'option_id';
	public array $_sanitize_orderby_fields = array(
		'option_id',
		'option_name',
		'option_size',
		'autoload',
	);
	protected bool $_show_autoload = true;

	public function __construct( $args = array() ) {
		parent::__construct( array(
			'singular' => 'option',
			'plural'   => 'options',
			'ajax'     => false,
		) );
	}

	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		$_per_page     = $this->rows_per_page();
		$_sel_source   = $this->get_request_arg( 'filter-source' );
		$_sel_status   = $this->get_request_arg( 'filter-status' );
		$_sel_prefix   = $this->get_request_arg( 'filter-prefix' );
		$_sel_autoload = $this->get_request_arg( 'filter-autoload' );
		$_sel_search   = $this->get_request_arg( 'search' );
		$_sel_orderby  = $this->get_request_arg( 'orderby' );
		$_sel_order    = $this->get_request_arg( 'order' );
		$_filtered     = true;
		$_options      = array();

		if ( ( $_sel_source != 'all' && $_sel_source != '' ) || ( $_sel_status != 'all' && $_sel_status != '' ) ) {
			$_options = CoreOptions::instance()->filtered( $this->_filter_options, $_sel_source, $_sel_status );

			$_filtered = ! empty( $_options );
		}

		$this->items = array();

		if ( $_filtered ) {
			$data = Shared::instance()->query_options_table( array(
				'per_page' => $_per_page,
				'page'     => $this->get_request_arg( 'paged' ),
				'orderby'  => $_sel_orderby,
				'order'    => $_sel_order,
				'prefix'   => $_sel_prefix,
				'search'   => $_sel_search,
				'autoload' => $_sel_autoload,
				'options'  => $_options,
			) );

			foreach ( $data['items'] as $item ) {
				$item->result = CoreOptions::instance()->identify( $item->option_name );

				$this->items[] = $item;
			}

			$this->set_pagination_args(
				array(
					'total_items' => $data['found'],
					'total_pages' => ceil( $data['found'] / $_per_page ),
					'per_page'    => $_per_page,
				)
			);
		}
	}

	public function get_columns() : array {
		return array(
			'cb'           => '<input type="checkbox" />',
			'option_id'    => __( 'ID', 'sweeppress' ),
			'option_name'  => __( 'Option', 'sweeppress' ),
			'status'       => __( 'Status', 'sweeppress' ),
			'source'       => __( 'Source', 'sweeppress' ),
			'option_value' => __( 'Value', 'sweeppress' ),
			'option_size'  => __( 'Value Size', 'sweeppress' ),
			'autoload'     => __( 'Autoload', 'sweeppress' ),
		);
	}

	public function get_sortable_columns() : array {
		return array(
			'option_id'   => array( 'option_id', false ),
			'option_name' => array( 'option_name', false ),
			'option_size' => array( 'option_size', false ),
			'autoload'    => array( 'autoload', false ),
		);
	}

	protected function get_bulk_actions() : array {
		return array(
			'delete'           => __( 'Delete', 'sweeppress' ),
			'autoload-disable' => __( 'Disable Autoload', 'sweeppress' ),
			'autoload-enable'  => __( 'Enable Autoload', 'sweeppress' ),
		);
	}

	protected function process_request_args() {
		parent::process_request_args();

		$this->_request_args['filter-autoload'] = Sanitize::_get_text( 'filter-autoload' );
	}

	protected function column_cb( $item ) : string {
		$key = $this->_checkbox_field;

		if ( $item->result['delete'] ) {
			return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->$key );
		} else {
			return '';
		}
	}

	protected function column_option_name( $item ) : string {
		$actions = array();

		if ( $item->result['delete'] ) {
			$actions['delete'] = '<a class="sweeppress-link-delete sweeppress-action-option-delete" href="#" data-option="' . $item->option_name . '" data-url="' . $this->_self( 'single-action=delete-option&option_id=' . $item->option_id, true, wp_create_nonce( 'sweeppress-delete-option-' . $item->option_id ) ) . '">' . __( 'Delete', 'sweeppress' ) . '</a>';
		}

		$actions['autoload'] = '<a href="' . $this->_self( 'single-action=autoload-' . ( $item->autoload === 'yes' ? 'disable' : 'enable' ) . '&option_id=' . $item->option_id, true, wp_create_nonce( 'sweeppress-autoload-' . ( $item->autoload === 'yes' ? 'disable' : 'enable' ) . '-' . $item->option_id ) ) . '">' . ( $item->autoload === 'yes' ? __( 'Disable Autoload', 'sweeppress' ) : __( 'Enable Autoload', 'sweeppress' ) ) . '</a>';

		return $item->option_name . $this->row_actions( $actions );
	}

	protected function column_status( $item ) : string {
		return Helper::_render_status( $item );
	}

	protected function column_source( $item ) : string {
		return Helper::_render_source( $item );
	}

	protected function column_option_size( $item ) : string {
		return Helper::_short_count( $item->option_size );
	}

	protected function column_autoload( $item ) : string {
		return $item->autoload == 'yes'
			? '<span class="sweeppress-badge badge-blue">' . __( 'YES', 'sweeppress' ) . '</span>'
			: '<span class="sweeppress-badge badge-light-blue">' . __( 'NO', 'sweeppress' ) . '</span>';
	}

	protected function column_option_value( $item ) : string {
		return Helper::_render_value( $item );
	}

	protected function get_option_names() : array {
		return Shared::instance()->get_options_names();
	}
}
