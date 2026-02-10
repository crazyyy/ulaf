<?php

namespace Innocow\Plugin_Organiser\Rest;

use Innocow\Plugin_Organiser\Services\WP_Options;

class Groups {

    public static function is_user_permissible() {

        if ( current_user_can( 'edit_users' ) ) {
            return true;
        }

        return false;

    }

    public static function filter_request( array $rp, $type="save" ) {

        if ( $type = "save" ) {
            return [
                "name" => $rp["name"],
                "plugins" => $rp["plugins_selected"]
            ];
        }

    }

    public static function create( \WP_REST_Request $Request, $is_from_hook=true ) {

        if ( $is_from_hook ) {
            throw new \RuntimeException( "Function cannot be called directly." );
        }

        $rp = $Request->get_body_params();
        $WP_Options = new WP_Options();

        $option_lastindex = $WP_Options->find_lastindex();

        $unused_option_index = $WP_Options->find_next_available_index( $option_lastindex );
        $option_name = $WP_Options->create_name_group( $unused_option_index );
        $option_values = array_merge(
            [ "id" => $unused_option_index ],
            self::filter_request( $rp, "save" )
        );

        $is_saved = $WP_Options->save_group( $option_name, $option_values );

        if ( $is_saved ) {

            $WP_Options->add_to_indexes( $unused_option_index );
            $WP_Options->save_lastindex( $unused_option_index );

            return [
                "isSaved" => true,
                "id" => $unused_option_index
            ];

        }


    }

    public static function update( \WP_REST_Request $Request, $is_from_hook=true ) {

        if ( $is_from_hook ) {
            throw new \RuntimeException( "Function cannot be called directly." );
        }

        $rp = $Request->get_body_params();
        $WP_Options = new WP_Options();

        $option_name = $WP_Options->create_name_group( intval( $rp["id"] ) );
        $option_values = array_merge(
            [ "id" => $rp["id"] ],
            self::filter_request( $rp, "save" )
        );

        $is_updated = $WP_Options->save_group( $option_name, $option_values );   

        if ( $is_updated ) {
            $WP_Options->add_to_indexes( intval( $rp["id"] ) );
        }

        return [
            "isUpdated" => $is_updated,
        ];

    }

    public static function save( \WP_REST_Request $Request ) {

        $rp = $Request->get_body_params();

        try {

            if ( ! isset( $rp["id"] ) ) {
                throw new \InvalidArgumentException( "Missing url parameter: id" );
            }

            if ( ! isset( $rp["name"] ) ) {
                throw new \InvalidArgumentException( "Missing url parameter: name" );
            }

            if ( ! isset( $rp["plugins_selected"] ) ) {
                throw new \InvalidArgumentException( "Missing url parameter: plugins_selected" );
            }

            if ( empty( $rp["id"] ) ) {
                return self::create( $Request, false );
            } else {
                return self::update( $Request, false );
            }

            return $rp;

        } catch ( \InvalidArgumentException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 400 ] 
            );
        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwppo_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }

    }

    public static function delete( \WP_REST_Request $Request ) {

        $rp = $Request->get_params();

        try {

            if ( ! isset( $rp["id"] ) ) {
                throw new \InvalidArgumentException( "Missing url parameter: id" );
            }

            if ( empty( $rp["id"] ) ) {
                throw new \InvalidArgumentException( "Invalid paramter: id" );
            }

            $WP_Options = new WP_Options();
            $is_deleted = $WP_Options->delete_group( $rp["id"] );

            return [
                "isDeleted" => $is_deleted
            ];

        } catch ( \InvalidArgumentException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 400 ] 
            );
        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwppo_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }

    }

}