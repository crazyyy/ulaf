<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$attsDecode = json_decode(urldecode($atts["stm_pl_qk_sts"]));
?>

<div class="stm-player-quick-stats ">
	<table>
		<thead>
			<tr>
				<th colspan="2" class="heading-font"><?php echo splash_sanitize_text_field($atts["title"]); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ($attsDecode as $val):
		?>
			<tr>
				<td class="<?php echo (!splash_is_layout("af")) ? "heading-font" : "normal_font"; ?>"><?php echo esc_html($val->label); ?></td>
				<td class="<?php echo (!splash_is_layout("af")) ? "heading-font" : "normal_font"; ?>"><?php echo esc_html($val->data); ?></td>
			</tr>
		<?php
		endforeach;
		?>
		</tbody>
	</table>
</div>

