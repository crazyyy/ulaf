<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
/*
[info_table_title] => 'title'
[info_table_columns] => 3
[info_item] => obj(
		[info_title] => ADDRESS 
		[info_content] => 13 Vegas Drive, Las Vegas, 15348 Nevada, U.S. 
		[show_info_link] => enable
		[info_link_title] => View on Map
		[info_link] => http://splash/soccer/map)
 * */
//print_r($atts);
?>

<div class="stm-info-table-wrapp">
	<table class="stm-info-table">
	<?php
		$tr = "";
		foreach (json_decode(urldecode($info_item)) as $k => $val) {

			if($k%$info_table_columns == 0 && $k == 0) {
				$tr .= "<tr>";
			} else if($k%$info_table_columns == 0 && $k != 0) {
				$tr .= "</tr><tr>";
			}




			if(isset($val->show_info_link) && $val->show_info_link == "enable") {
				$tr .= "<td>";
				$tr .= "<span class='title heading-font'>" . esc_html($val->info_title) . "</span>";
				$tr .= "<span class='desc normal_font'>" . esc_html($val->info_content) . " <a href='" . esc_url($val->info_link) . "'>" . esc_html($val->info_link_title) . "</a></span>";
			} else {
				$tr .= "<td>";
				$tr .= "<span class='title heading-font'>" . esc_html($val->info_title) . "</span>";
				$tr .= "<span class='desc normal_font'>" . esc_html($val->info_content) . "</span>";
			}

			$tr .= "</td>";
		}
		$tr .= "</tr>";
		echo splash_sanitize_text_field($tr);
	?>
	</table>
</div>
