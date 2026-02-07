<?php
STM_PostType::registerPostType( 'donor', __( 'Donor', STM_CONFIGURATIONS ), array(
	'supports' => array( 'title', 'excerpt' ),
	'exclude_from_search' => true,
	'publicly_queryable' => false,
	'show_in_menu' => 'edit.php?post_type=donation'
	)
);