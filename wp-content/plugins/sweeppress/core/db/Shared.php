<?php

namespace Dev4Press\Plugin\SweepPress\DB;

use Dev4Press\Plugin\SweepPress\Base\DB as CoreDB;
use Dev4Press\Plugin\SweepPress\Meta\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shared extends CoreDB {
	protected string $plugin_instance = 'shared';

	public function get_actionscheduler_groups() : array {
		$sql = "SELECT * FROM " . $this->actionscheduler_groups;
		$raw = $this->get_results( $sql );

		return $this->pluck( $raw, 'slug', 'group_id' );
	}

	public function repair_table( $name ) : array {
		$info = $this->get_results( "REPAIR TABLE `" . $name . "`" );

		$data = array(
			'note'   => '',
			'status' => '',
			'error'  => '',
		);

		foreach ( $info as $row ) {
			$data[ strtolower( $row->Msg_type ) ] = $row->Msg_text;
		}

		if ( empty( $data['status'] ) ) {
			$data['status'] = empty( $data['error'] ) ? __( 'Nothing Done', 'sweeppress' ) : __( 'Operation Failed', 'sweeppress' );
		}

		return $data;
	}

	public function optimize_table( $name ) : array {
		$info = $this->get_results( "OPTIMIZE TABLE `" . $name . "`" );

		$data = array(
			'note'   => '',
			'status' => '',
			'error'  => '',
		);

		foreach ( $info as $row ) {
			$data[ strtolower( $row->Msg_type ) ] = $row->Msg_text;
		}

		return $data;
	}

	public function get_options_names() : array {
		$exclude    = Options::instance()->get_defaults();
		$transients = Options::instance()->get_transients();

		$where = array( "`option_name` NOT IN (" . $this->prepare_in_list( $exclude ) . ")" );

		foreach ( $transients as $key ) {
			$where[] = "`option_name` NOT LIKE '$key'";
		}

		$sql = array(
			'select' => array(
				'`option_name`',
			),
			'from'   => array(
				$this->options,
			),
			'where'  => $where,
		);

		$query = $this->build_query( $sql );
		$raw   = $this->get_results( $query );

		return $this->pluck( $raw, 'option_name' );
	}

	public function query_options_table( $args = array() ) : array {
		$defaults = array(
			'page'     => 1,
			'per_page' => 0,
			'orderby'  => 'option_id',
			'order'    => 'ASC',
			'prefix'   => '',
			'search'   => '',
			'autoload' => '',
			'options'  => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$exclude    = Options::instance()->get_defaults();
		$transients = Options::instance()->get_transients();

		$where = array();

		if ( empty( $args['options'] ) ) {
			$where[] = "`option_name` NOT IN (" . $this->prepare_in_list( $exclude ) . ")";

			foreach ( $transients as $key ) {
				$where[] = $this->prepare( "`option_name` NOT LIKE %s", $key );
			}
		} else {
			$where[] = "`option_name` IN (" . $this->prepare_in_list( $args['options'] ) . ")";
		}

		if ( ! empty( $args['prefix'] ) ) {
			$where[] = $this->prepare( "`option_name` LIKE %s", $args['prefix'] . '%' );
		}

		if ( ! empty( $args['search'] ) ) {
			$where[] = $this->prepare( "(`option_name` LIKE %s OR `option_value` LIKE %s)", '%' . $args['search'] . '%', '%' . $args['search'] . '%' );
		}

		if ( ! empty( $args['autoload'] ) && in_array( $args['autoload'], array( 'yes', 'no' ) ) ) {
			$where[] = $this->prepare( "`autoload` = %s", $args['autoload'] );
		}

		$sql = array(
			'select' => array(
				'`option_id`',
				'`option_name`',
				'`option_value`',
				'`autoload`',
				'LENGTH(`option_value`) as `option_size`',
				'0 as `result`',
			),
			'from'   => array(
				$this->options,
			),
			'where'  => $where,
			'order'  => $args['orderby'] . ' ' . $args['order'],
		);

		if ( $args['per_page'] > 0 ) {
			$offset       = $args['per_page'] * ( $args['page'] - 1 );
			$sql['limit'] = $offset . ', ' . $args['per_page'];
		}

		$query = $this->build_query( $sql );
		$raw   = $this->get_results( $query );

		return array(
			'items' => $raw,
			'found' => $this->get_found_rows(),
		);
	}

	public function get_list_of_blog_ids() : array {
		$sql  = "SELECT `blog_id` FROM " . $this->blogs;
		$raw  = $this->get_results( $sql );
		$list = $this->pluck( $raw, 'blog_id' );

		return array_map( 'absint', $list );
	}
}
