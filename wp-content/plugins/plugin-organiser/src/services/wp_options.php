<?php

/**
 * Class Plugins | src/services/plugins.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Plugin_Organiser\Services;

class WP_Options {

    private $plugin_slug = null;

    public function __construct() {

        $Plugin = $GLOBALS["ICWPPO"];
        $this->plugin_slug = $Plugin->get_plugin_slug();

    }

    public function create_name_indexes() {

        $option_key = "indexes";
        $name = "{$this->plugin_slug}-$option_key";

        return $name;

    }

    public function create_name_lastindex() {

        $option_key = "lastindex";
        $name = "{$this->plugin_slug}-$option_key";

        return $name;

    }

    public function create_name_group( int $index ) {

        if ( $index < 1 ) {
            return false;
        }

        $option_key = "group";
        $name = "{$this->plugin_slug}-$option_key-$index";

        return $name;

    }

    public function save_indexes( array $indexes ) {
        return update_option( $this->create_name_indexes(), $indexes, false );
    }

    public function add_to_indexes( int $add_index ) {

        $indexes = $this->find_indexes();

        // This is an ugly hack but PHP isn't capable of safely storing integers 
        // in an array. Fix this later ;)
        $indexes[$add_index] = $add_index;

        return update_option( $this->create_name_indexes(), $indexes, false );

    }

    public function save_lastindex( int $lastindex ) {
        return update_option( $this->create_name_lastindex(), $lastindex, false );
    }

    public function save_group( string $option_name, array $option_values ) {
        return update_option( $option_name, $option_values, false );
    }

    public function delete_group( int $index ) {

        $indexes = $this->find_indexes();
        if ( isset( $indexes[$index] ) ) {
            unset( $indexes[$index] );
        }
        $this->save_indexes( $indexes );

        return delete_option( $this->create_name_group( $index ) );

    }

    public function find_indexes() {
        
        $indexes = get_option( $this->create_name_indexes() );

        if ( ! $indexes ) {
            return [];
        }

        return $indexes;

    }

    public function find_lastindex() {
        
        $lastindex = get_option( $this->create_name_lastindex() );

        if ( $lastindex ) {
            return $lastindex;
        }

        return 1;

    }

    public function find_group( int $index ) {
        return get_option( $this->create_name_group( $index ) );
    }

    public function find_all_groups() {

        $indexes = $this->find_indexes();
        $groups = [];

        foreach( $indexes as $index ) {
            $groups[] = $this->find_group( $index );
        }

        return $groups;

    }

    public function find_all_groups_as_json() {
        return json_encode( $this->find_all_groups() );
    }

    public function find_next_available_index( int $start_index=1, int $attempts=10 ) {

        $index_limit = $start_index + $attempts;

        for ( $index=$start_index; $index<=$index_limit; $index++ ) {

            if ( ! get_option( $this->create_name_group( $index ), false ) ) {
                break;
            }

        }

        return $index;

    }

}
