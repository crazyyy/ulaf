<?php
/**
 * Template for displaying announcements tab in single course page.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/announcements/announcements.php.
 *
 * @author  ThimPress
 * @package LearnPress/Announcements/Templates
 * @version 3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();
?>

<div id="lp-announcements" class="lp-announcements">

	<?php

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $announcement ) {
			learn_press_announcements_template( 'loop/item.php', array( 'announcement' => $announcement ) );
		}
	} else {
		learn_press_announcements_template( 'loop/not-found.php' );
	}

	wp_reset_postdata(); ?>

</div>