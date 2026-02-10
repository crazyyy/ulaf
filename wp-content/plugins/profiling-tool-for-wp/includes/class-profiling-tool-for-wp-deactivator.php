<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://dnsempresas.com/
 * @since      1.0.0
 *
 * @package    Profiling_Tool_For_Wp
 * @subpackage Profiling_Tool_For_Wp/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Profiling_Tool_For_Wp
 * @subpackage Profiling_Tool_For_Wp/includes
 * @author     Dns Empresas <Administracion@dnsempresas.com>
 */
class Profiling_Tool_For_Wp_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
		self::plugin_free_deactivate_request();
		
	}

	public static function plugin_free_deactivate_request() {
		
		goto bbTM4; bbTM4: $license_key = get_option("\x54\102\111\x57\120\137\106\122\x45\x45\137\113\x45\x59"); goto PEQOd; c6CpH: $license_data = json_decode(wp_remote_retrieve_body($response)); goto Q1TAY; Maqsz: $response = wp_remote_get($query, array("\164\x69\155\145\x6f\x75\x74" => 20, "\x73\163\x6c\166\145\x72\151\x66\171" => false)); goto HLeDq; OAVX_: $query = esc_url_raw(add_query_arg($api_params, "\150\x74\x74\x70\x73\x3a\57\57\x77\167\167\x2e\164\x62\160\x6c\x75\x67\x69\x6e\56\x63\x6f\x6d\57")); goto Maqsz; HLeDq: if (is_wp_error($response)) { return "\x55\156\145\170\160\145\143\164\x65\144\x20\105\162\162\157\162\41\x20\124\150\145\40\161\x75\x65\x72\171\40\x72\x65\x74\x75\162\x6e\145\x64\40\167\x69\164\x68\x20\141\156\40\145\162\x72\x6f\162\56"; } goto c6CpH; Q1TAY: if ($license_data->result == "\x73\x75\x63\143\x65\163\x73") { update_option("\124\x42\111\127\120\x5f\x46\x52\x45\105\x5f\x4b\105\131", ''); } else { } goto sMLab; PEQOd: $api_params = array("\163\x6c\x6d\x5f\x61\143\164\151\x6f\x6e" => "\163\154\x6d\137\x64\x65\x61\x63\x74\151\166\x61\x74\145", "\163\x65\143\162\145\164\x5f\153\145\171" => "\x36\x35\142\x31\x37\x63\x61\x39\x30\141\143\x62\141\x37\x2e\x30\x30\x34\x38\x32\x31\71\x36", "\x6c\151\143\145\x6e\163\145\137\x6b\x65\171" => $license_key, "\x72\145\147\x69\x73\164\145\162\x65\x64\x5f\x64\x6f\x6d\x61\x69\x6e" => $_SERVER["\123\105\x52\x56\105\122\137\116\101\115\105"], "\151\164\145\155\x5f\x72\x65\146\145\x72\x65\x6e\143\x65" => urlencode("\124\x42\127\x50\106\x52\x45\105\x53\124\x41\124\x55\x53")); goto OAVX_; sMLab:
		
	}
	
}
