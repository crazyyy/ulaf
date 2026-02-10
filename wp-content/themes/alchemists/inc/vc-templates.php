<?php
/**
 * Visual Composer Templates
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */


/**
 * Check Visual Composer templates in folder and load as default templates
 */

add_filter( 'vc_load_default_templates', 'alchemists_load_custom_vc_templates' );
function alchemists_load_custom_vc_templates( $vc_data ) {
	$vc_data           = array();
	$tpl_files_pattern = get_template_directory() . '/inc/vc_templates/*.tpl';
	$tpl_files         = glob( $tpl_files_pattern );
	array_multisort( array_map( 'filemtime', $tpl_files ), SORT_NUMERIC, SORT_DESC, $tpl_files );

	foreach ( $tpl_files as $file ) {

		$filename_pre = explode( '.', basename( $file ) );
		$filename     = reset( $filename_pre );

		$data                 = array();
		$data['category']     = esc_html__( 'Alchemists Theme', 'alchemists' );
		$data['name']         = '[ALC] ' . ucfirst( str_replace( array( '-', '_' ), ' ', $filename ) );
		$data['custom_class'] = 'alc_tpl_' . str_replace( array( '-', '_' ), '_', $filename );
		$data['content']      = file_get_contents( $file );

		array_unshift( $vc_data, $data );
	}

	return $vc_data;
}
