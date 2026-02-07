<?php
global $wpdb;
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

/*
[tfnm_title] => Tickets for the Next Match (Jan 28, 2017)
[tfnm_event] => 120
[tfnm_btn_title] => Buy Tickets
[tfnm_btn_link] => http://splash.loc/soccer/shop
 * */

$event_data = $wpdb->get_results("SELECT wp3p.*, wp3pm.meta_value as teams FROM " . $wpdb->prefix . "posts wp3p INNER JOIN " . $wpdb->prefix . "postmeta wp3pm ON wp3pm.post_id = wp3p.ID WHERE wp3p.ID = " . $tfnm_event . " AND wp3p.post_type = 'sp_event' AND wp3pm.meta_key = 'sp_team' GROUP BY wp3pm.meta_value", OBJECT);
$teams = $wpdb->get_results("SELECT *, wp3pm.meta_value FROM " . $wpdb->prefix . "posts wp3p INNER JOIN " . $wpdb->prefix . "postmeta wp3pm ON wp3pm.post_id = wp3p.ID WHERE ID IN (" . $event_data[0]->teams . "," . $event_data[1]->teams . ") AND wp3pm.meta_key = '_thumbnail_id' GROUP BY wp3p.ID", OBJECT);

$team_left = $teams[0];
$team_left_logo = wp_get_attachment_image_src($team_left->meta_value);

$team_right = $teams[1];
$team_right_logo = wp_get_attachment_image_src($team_right->meta_value);
?>

<div class="stm-tickets-wrapp">
	<h3><?php echo esc_html($tfnm_title); ?></h3>
	<div class="stm-tickets-teams">
		<div class="stm-left-team">
			<div class="stm-team-img">
				<img src="<?php echo esc_url($team_left_logo[0]); ?>" />
			</div>
			<div class="stm-team-name heading-font">
				<span><?php echo esc_html($team_left->post_title); ?></span>
			</div>
		</div>
		<div class="stm-team-vs heading-font">
			<span>VS</span>
		</div>
		<div class="stm-right-team">
			<div class="stm-team-img">
				<img src="<?php echo esc_url($team_right_logo[0]); ?>" />
			</div>
			<div class="stm-team-name heading-font">
				<span><?php echo esc_html($team_right->post_title); ?></span>
			</div>
		</div>
	</div>
	<div class="stm-tickets-btn">
		<a class="button button-default with_bg" href="<?php echo esc_url($tfnm_btn_link); ?>"><i class="fa fa-ticket" aria-hidden="true"></i><?php echo esc_html($tfnm_btn_title);?></a>
	</div>
</div>

