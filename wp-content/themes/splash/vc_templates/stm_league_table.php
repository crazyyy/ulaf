<?php
$id = $count = $title = '';
$atts   = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if(empty($id)) {
	return false;
}

if(empty($count)) {
	$count = 7;
}

$defaults = array(
	'id' => $id,
	'number' => $count,
	'columns' => null,
	'highlight' => null,
	'show_full_table_link' => false,
	'show_title' => get_option( 'sportspress_table_show_title', 'yes' ) == 'yes' ? true : false,
	'show_team_logo' => get_option( 'sportspress_table_show_logos', 'yes' ) == 'yes' ? true : false,
	'link_posts' => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'sortable' => get_option( 'sportspress_enable_sortable_tables', 'yes' ) == 'yes' ? true : false,
	'scrollable' => get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false,
	'paginated' => get_option( 'sportspress_table_paginated', 'yes' ) == 'yes' ? true : false,
	'rows' => $count,
);

extract( $defaults, EXTR_SKIP );

if ( ! isset( $highlight ) ) $highlight = get_post_meta( $id, 'sp_highlight', true );

$table = new SP_League_Table( $id );

if($atts["table_style"] == "style_one") {
	$output = '<div class="stm-single-league">';

	if ($title && $title != "" && $atts["table_style"] == "style_one") {
		$viewAll = (splash_is_af()) ? esc_html__('See all results', 'splash') : esc_html__('View all', 'splash');
		$output .= '<div class="stm-single-league-title_box clearfix">';
		$output .= '<h3 class="sp-table-caption">' . $title . '</h3>';
		$output .= '<a href="' . get_the_permalink($id) . '" class="stm-link-all normal-font">' . $viewAll . '</a>';
		$output .= '</div>';
	}

	$output .= '<div class="sp-table-wrapper sp-scrollable-table-wrapper">';

	$output .= '<table class="sp-league-table sp-data-table sp-scrollable-table' . ($sortable ? ' sp-sortable-table' : '') . ($paginated ? ' sp-paginated-table' : '') . '" data-sp-rows="' . $rows . '">' . '<thead>' . '<tr>';

	$data = $table->data();

	// The first row should be column labels
	$labels = $data[0];

	// Remove the first row to leave us with the actual data
	unset($data[0]);

	if ($columns === null)
		$columns = get_post_meta($id, 'sp_columns', true);

	if (null !== $columns && !is_array($columns))
		$columns = explode(',', $columns);

	$output .= '<th class="data-rank normal_font">' . esc_html__('N.', 'splash') . '</th>';

	foreach ($labels as $key => $label):
		if (!is_array($columns) || $key == 'name' || in_array($key, $columns))
			$output .= '<th class="data-' . $key . ' normal_font">' . $label . '</th>';
	endforeach;

	$output .= '</tr>' . '</thead>' . '<tbody>';

	$i = 0;
	$start = 0;

	if (intval($number) > 0):
		$limit = $number;

		// Trim table to center around highlighted team
		if ($highlight && sizeof($data) > $limit && array_key_exists($highlight, $data)):

			// Number of teams in the table
			$size = sizeof($data);

			// Position of highlighted team in the table
			$key = array_search($highlight, array_keys($data));

			// Get starting position
			$start = $key - ceil($limit / 2) + 1;
			if ($start < 0) $start = 0;

			// Trim table using starting position
			$trimmed = array_slice($data, $start, $limit, true);

			// Move starting position if we are too far down the table
			if (sizeof($trimmed) < $limit && sizeof($trimmed) < $size):
				$offset = $limit - sizeof($trimmed);
				$start -= $offset;
				if ($start < 0) $start = 0;
				$trimmed = array_slice($data, $start, $limit, true);
			endif;

			// Replace data
			$data = $trimmed;
		endif;
	endif;

	// Loop through the teams
	foreach ($data as $team_id => $row):
		if (isset($limit) && $i >= $limit) continue;

		$name = sp_array_value($row, 'name', null);
		if (!$name) continue;

		// Generate tags for highlighted team
		$tr_class = $td_class = '';
		if ($highlight == $team_id):
			$tr_class = ' highlighted';
			$td_class = ' sp-highlight';
		endif;

		$output .= '<tr class="' . ($i % 2 == 0 ? 'odd' : 'even') . $tr_class . ' sp-row-no-' . $i . '">';

		// Rank
		$output .= '<td class="data-rank' . $td_class . '">' . sp_array_value($row, 'pos') . '</td>';

		$name_class = '';

		if ($show_team_logo):
			if (has_post_thumbnail($team_id)):
				$team_link = get_permalink($team_id);
				$logo = get_the_post_thumbnail($team_id, 'sportspress-fit-icon');
				$name = '<a href="' . $team_link . '" class="team-logo"><div class="stm-league-table-team-logo">' . $logo . " </div>" . $name . '</a>';
				$name_class .= ' has-logo';
			endif;
		endif;

		$output .= '<td class="data-name' . $name_class . $td_class . '">' . $name . '</td>';

		foreach ($labels as $key => $value):
			if (in_array($key, array('pos', 'name')))
				continue;
			if (!is_array($columns) || in_array($key, $columns))
				$output .= '<td class="data-' . $key . $td_class . '">' . sp_array_value($row, $key, '&mdash;') . '</td>';
		endforeach;

		$output .= '</tr>';

		$i++;
		$start++;

	endforeach;

	$output .= '</tbody>' . '</table>';

	$output .= '</div>';
	$output .= '</div>';
} elseif($atts["table_style"] == "style_two") {
	$output = '<div class="vc_league_table sp-table-wrapper sp-scrollable-table-wrapper">';

	$output .= '<table class="sp-league-table sp-data-table sp-scrollable-table-wrapper' . ( $sortable ? ' sp-sortable-table' : '' ) . ( $paginated ? ' sp-paginated-table' : '' ) . '" data-sp-rows="' . $rows . '">' . '<thead>' . '<tr>';

	$table = new SP_League_Table( $id );

	$data = $table->data();

	// The first row should be column labels
	$labels = $data[0];

	// Remove the first row to leave us with the actual data
	unset( $data[0] );

	if ( ! $columns )
		$columns = get_post_meta( $id, 'sp_columns', true );

	if ( ! is_array( $columns ) )
		$columns = explode( ',', $columns );

	$output .= '<th class="data-rank normal_font">' . esc_html__( 'Pos', 'splash' ) . '</th>';

	foreach( $labels as $key => $label ):
		if ( ! is_array( $columns ) || $key == 'name' || in_array( $key, $columns ) )
			$output .= '<th class="data-' . esc_attr( $key ) . ' normal_font">' . $label . '</th>';
	endforeach;

	$output .= '</tr>' . '</thead>' . '<tbody>';

	$i = 0;
	$start = 0;

	if ( intval( $number ) > 0 ):
		$limit = $number;

		// Trim table to center around highlighted team
		if ( $highlight && sizeof( $data ) > $limit && array_key_exists( $highlight, $data ) ):

			// Number of teams in the table
			$size = sizeof( $data );

			// Position of highlighted team in the table
			$key = array_search( $highlight, array_keys( $data ) );

			// Get starting position
			$start = $key - ceil( $limit / 2 ) + 1;
			if ( $start < 0 ) $start = 0;

			// Trim table using starting position
			$trimmed = array_slice( $data, $start, $limit, true );

			// Move starting position if we are too far down the table
			if ( sizeof( $trimmed ) < $limit && sizeof( $trimmed ) < $size ):
				$offset = $limit - sizeof( $trimmed );
				$start -= $offset;
				if ( $start < 0 ) $start = 0;
				$trimmed = array_slice( $data, $start, $limit, true );
			endif;

			// Replace data
			$data = $trimmed;
		endif;
	endif;

	// Loop through the teams
	$r = 0;
	foreach ( $data as $team_id => $row ): $r++;

		if ( isset( $limit ) && $i >= $limit ) continue;

		$name = sp_array_value( $row, 'name', null );
		if ( ! $name ) continue;

		// Generate tags for highlighted team
		$before = $after = $class = '';
		if ( $highlight == $team_id ):
			$before = '<strong>';
			$after = '</strong>';
			$class = ' highlighted';
		endif;

		if($r == 1 || $r == 2 || $r == 3){
			$class .= ' red';
		}

		$output .= '<tr class="' . esc_attr( ( $i % 2 == 0 ? 'odd' : 'even' ) . $class ) . '">';

		// Rank
		$output .= '<td class="data-rank">' . $before . ( $start + 1 ) . $after . '.</td>';

		$name_class = '';

		$permalink = get_post_permalink( $team_id );
		$name = '<a href="' . esc_url( $permalink ) . '">' . $name . '</a>';

		$output .= '<td class="data-name' . esc_attr( $name_class ) . '">' . $name . '</td>';

		foreach( $labels as $key => $value ):
			if ( $key == 'name' )
				continue;
			if ( ! is_array( $columns ) || in_array( $key, $columns ) )
				$output .= '<td class="data-' . esc_attr( $key ) . '">' . $before . sp_array_value( $row, $key, '&mdash;' ) . $after . '</td>';
		endforeach;

		$output .= '</tr>';

		$i++;
		$start++;

	endforeach;

	$output .= '</tbody>' . '</table>';

	$output .= '<a class="sp-league-table-link sp-view-all-link heading-font" href="' . esc_url( get_permalink( $id ) )  . '">' . esc_html__( 'view all', 'splash' ) . '</a>';

	$output .= '</div>';
}
elseif($atts["table_style"] == "style_3"){
    $output = '<div class="stm-single-league">';

    if (!empty($title)) {
        $output .= '<div class="stm-single-league-title_box clearfix">';
        $output .= '<h2 class="sp-table-caption">' . $title . '</h2>';
        $output .= '<a href="' . get_the_permalink($id) . '" class="stm-link-all normal-font">' . esc_html__('See all results', 'splash') . '</a>';
        $output .= '</div>';
    }

    $output .= '<div class="sp-table-wrapper sp-scrollable-table-wrapper">';

    $output .= '<table class="sp-league-table sp-data-table sp-scrollable-table' . ($sortable ? ' sp-sortable-table' : '') . ($paginated ? ' sp-paginated-table' : '') . '" data-sp-rows="' . $rows . '">' . '<thead>' . '<tr>';

    $data = $table->data();
    // The first row should be column labels
    $labels = $data[0];
    // Remove the first row to leave us with the actual data
    unset($data[0]);

    if ($columns === null)
        $columns = get_post_meta($id, 'sp_columns', true);
    if (null !== $columns && !is_array($columns))
        $columns = explode(',', $columns);

    foreach ($labels as $key => $label):
        if (!is_array($columns) || $key == 'name' || in_array($key, $columns))
            $output .= '<th class="data-' . $key . ' normal_font">' . $label . '</th>';
    endforeach;

    $output .= '</tr>' . '</thead>' . '<tbody>';

    $i = 0;
    $start = 0;

    if (intval($number) > 0):
        $limit = $number;

        // Trim table to center around highlighted team
        if ($highlight && sizeof($data) > $limit && array_key_exists($highlight, $data)):

            // Number of teams in the table
            $size = sizeof($data);

            // Position of highlighted team in the table
            $key = array_search($highlight, array_keys($data));

            // Get starting position
            $start = $key - ceil($limit / 2) + 1;
            if ($start < 0) $start = 0;

            // Trim table using starting position
            $trimmed = array_slice($data, $start, $limit, true);

            // Move starting position if we are too far down the table
            if (sizeof($trimmed) < $limit && sizeof($trimmed) < $size):
                $offset = $limit - sizeof($trimmed);
                $start -= $offset;
                if ($start < 0) $start = 0;
                $trimmed = array_slice($data, $start, $limit, true);
            endif;

            // Replace data
            $data = $trimmed;
        endif;
    endif;

    // Loop through the teams
    foreach ($data as $team_id => $row):

        if (isset($limit) && $i >= $limit) continue;

        $name = sp_array_value($row, 'name', null);
        if (!$name) continue;

        // Generate tags for highlighted team
        $tr_class = $td_class = '';
        if ($highlight == $team_id):
            $tr_class = ' highlighted';
            $td_class = ' sp-highlight';
        endif;

        $output .= '<tr class="' . ($i % 2 == 0 ? 'odd' : 'even') . $tr_class . ' sp-row-no-' . $i . '">';

        $name_class = '';

        $output .= '<td class="data-name' . $name_class . $td_class . '">' . $name . '</td>';

        foreach ($labels as $key => $value):
            if (in_array($key, array('pos', 'name')))
                continue;
            if (!is_array($columns) || in_array($key, $columns))
                $output .= '<td class="data-' . $key . $td_class . '">' . sp_array_value($row, $key, '&mdash;') . '</td>';
        endforeach;

        $output .= '</tr>';

        $i++;
        $start++;

    endforeach;

    $output .= '</tbody>' . '</table>';

    $output .= '</div>';
    $output .= '</div>';
}
?>

<div class="sp-template sp-template-league-table">
	<?php echo wp_kses_post($output); ?>
</div>