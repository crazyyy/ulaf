<?php
/**
 * Birthdays
 *
 * @author 		ThemeBoy
 * @package 	SportsPress_Birthdays
 * @version   2.7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'date' => 'day',
	'size' => 'alchemists_player-xxs',
	'birthday_format' => 'birthday',
	'show_player_birthday' => get_option( 'sportspress_player_show_birthday', 'no' ) == 'yes' ? true : false,
	'show_staff_birthday' => get_option( 'sportspress_staff_show_birthday', 'no' ) == 'yes' ? true : false,
	'link_players' => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
	'link_staff' => get_option( 'sportspress_link_staff', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

$args = array(
	'post_type' => array( 'sp_player', 'sp_staff' ),
	'numberposts' => -1,
	'posts_per_page' => -1,
	'orderby' => 'date',
	'order' => 'ASC',
	'monthnum' => date('n'),
);

if ( $date == 'day' ) {
	$args['day'] = date('j');
}

if ( $date == 'week' ) {
	unset ( $args['monthnum'] );
	$args['date_query'] = array(
			array(
				'month' => date('n'),
				'day'   => date('j'),
			),
			array(
				'month' => date( "n", strtotime( "+1 day" ) ),
				'day'   => date( "j", strtotime( "+1 day" ) ),
			),
			array(
				'month' => date( "n", strtotime( "+2 days" ) ),
				'day'   => date( "j", strtotime( "+2 days" ) ),
			),
			array(
				'month' => date( "n", strtotime( "+3 days" ) ),
				'day'   => date( "j", strtotime( "+3 days" ) ),
			),
			array(
				'month' => date( "n", strtotime( "+4 days" ) ),
				'day'   => date( "j", strtotime( "+4 days" ) ),
			),
			array(
				'month' => date( "n", strtotime( "+5 days" ) ),
				'day'   => date( "j", strtotime( "+5 days" ) ),
			),
			array(
				'month' => date( "n", strtotime( "+6 days" ) ),
				'day'   => date( "j", strtotime( "+6 days" ) ),
			),
			array(
				'month' => date( "n", strtotime( "+1 week" ) ),
				'day'   => date( "j", strtotime( "+1 week" ) ),
			),
			'relation' => 'OR',
		);
}

$posts = get_posts( $args );

echo '<div class="table-responsive">';
	echo '<table class="table alc-birthdays">';

		// echo '<thead>';
		//   echo '<tr>';
		//     echo '<th class="alc-birthdays__item alc-birthdays__item--name">' . esc_html__( 'Name', 'alchemists' ) . '</th>';
		//     echo '<th class="alc-birthdays__item alc-birthdays__item--date">' . esc_html__( 'Date', 'alchemists' ) . '</th>';
		//   echo '</tr>';
		// echo '</thead>';

		echo '<tbody>';
			foreach ( $posts as $post ) {
				echo '<tr>';
					echo '<td class="alc-birthdays__item-container">';

						if ( 'sp_staff' == $post->post_type ) {
							$link_posts    = $link_staff;
							$show_birthday = $show_staff_birthday;
						} else {
							$link_posts    = $link_players;
							$show_birthday = $show_player_birthday;
						}

						// date
						$birthday = get_the_date( get_option( 'date_format') , $post->ID );

						// name
						$caption = $post->post_title;
						$caption = trim( $caption );


						sp_get_template( 'player-birthday.php', array(
							'id'         => $post->ID,
							'size'       => $size,
							'caption'    => $caption,
							'link_posts' => $link_posts,
						) );

					echo '</td>';

					$heading = null;
					if ( ( $show_birthday || 'birthday' == $birthday_format ) && $birthday && 'hide' != $birthday_format ) {
						$heading = '<td class="alc-birthdays__item alc-birthdays__item--date">' . $birthday . '</td>';
					}
					if ( 'age' == $birthday_format && $birthday ) {
						$sp_birthdays = new SportsPress_Birthdays();
						$age = $sp_birthdays->get_age( get_the_date( 'm-d-Y', $post->ID ) );
						$heading = '<td class="alc-birthdays__item alc-birthdays__item--date">' . $age . '</td>';
					}
					if ( 'birthdayage' == $birthday_format && $birthday ) {
						$sp_birthdays = new SportsPress_Birthdays();
						$age = $sp_birthdays->get_age( get_the_date( 'm-d-Y', $post->ID ) );
						$heading = '<td class="alc-birthdays__item alc-birthdays__item--date">' . $birthday . ' (' . $age . ')</td>';
					}
					echo wp_kses_post( $heading );

				echo '</tr>';
			}
		echo '</tbody>';

	echo '</table>';
echo '</div>';
