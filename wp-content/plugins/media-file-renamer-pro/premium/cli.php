<?php

class MeowPro_MFRH_CLI extends WP_CLI_Command {

	public function __construct() {
	}

  function rename_auto( $args ) {
    if ( empty( $args ) ) {
      WP_CLI::error( 'This command requires one or more Media IDs.' );
      return;
    }
    foreach ( $args as $mediaId ) {
      mfrh_rename( $mediaId );
      WP_CLI::line( "Renamed Media ID $mediaId automatically." );
    }
  }

  function rename_manual( $args ) {
    if ( empty( $args ) || count( $args ) !== 2 ) {
      WP_CLI::error( 'This command requires a Media ID and a filename.' );
      return;
    }
    $mediaId = $args[0];
    $filename = $args[1];
    mfrh_rename( $mediaId, $filename );
    WP_CLI::line( "Renamed Media ID $mediaId manually." );
  }

  function unlock_all() {
    global $wpdb;
    $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_manual_file_renaming'" );
  }

  function rename_all() {
    global $wpdb;
    $ids = $wpdb->get_col( "SELECT p.ID FROM $wpdb->posts p WHERE post_status = 'inherit' AND post_type = 'attachment'" );
    $idsToRemove = $wpdb->get_col( "SELECT m.post_id FROM $wpdb->postmeta m 
      WHERE m.meta_key = '_manual_file_renaming' and m.meta_value = 1" );
    $ids = array_values( array_diff( $ids, $idsToRemove ) );
    foreach ( $ids as $mediaId ) {
      mfrh_rename( $mediaId );
      WP_CLI::line( "Renamed Media ID $mediaId automatically." );
    }
  }

}

?>