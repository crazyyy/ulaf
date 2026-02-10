<?php

use Dev4Press\Plugin\SweepPress\Basic\Sweep;
use function Dev4Press\v54\Functions\panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="d4p-content">
	<?php

	if ( isset( $_GET['print'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$_get = new Sweep( true );
		$_all = $_get->all( true );
		$_cat = $_get->categories();

		foreach ( $_cat as $category => $label ) {
			echo '<h3>' . esc_html( $label ) . '</h3>';
			echo '<ul>';

			foreach ( $_all as $sweeper ) {
				if ( $sweeper['cat'] == $category ) {
					echo '<li><strong style="font-size: 120%">' . esc_html( $sweeper['name'] ) . '</strong><br/>';
					echo '<p><em>' . esc_html( $sweeper['info'] ) . '</em><br/>From plugin version: <strong>' . esc_html( $sweeper['version'] ) . '</strong></p>';

					echo '</li>';
				}
			}

			echo '</ul>';
		}
	} else if ( isset( $_GET['print-md'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$_get = new Sweep( true );
		$_all = $_get->all( true );
		$_cat = $_get->categories();

		foreach ( $_cat as $category => $label ) {
			echo '## ' . esc_html( $label ) . '<br/><br/>';

			foreach ( $_all as $sweeper ) {
				if ( $sweeper['cat'] == $category ) {
					echo '### ' . esc_html( $sweeper['name'] ) . '<br/><br/>';
					echo esc_html( $sweeper['info'] ) . '<br/>';

					if ( $sweeper['version'] != '1.0' ) {
						echo '<br/>_From plugin version: **' . esc_html( $sweeper['version'] ) . '**_<br/>';
					}

					if ( $sweeper['pro'] ) {
						echo '<br/>_**Available only in Pro version**_<br/>';
					}

					echo '<br/>* Code: **`' . $sweeper['code'] . '`**<br/>';
					echo '* Scope: **' . ( $sweeper['scope'] ? 'Blog' : 'Network' ) . '**<br/>';
					echo '* Quick Sweep: **' . ( $sweeper['quick'] ? 'Yes' : 'No' ) . '**<br/>';
					echo '* Auto Sweep: **' . ( $sweeper['auto'] ? 'Yes' : 'No' ) . '**<br/>';
					echo '* Sweeper Job: **' . ( $sweeper['cron'] ? 'Yes' : 'No' ) . '**<br/>';
					echo '* Backup: **' . ( $sweeper['backup'] ? 'Yes' : 'No' ) . '**<br/>';
					echo '* Monitor: **' . ( $sweeper['monitor'] ? 'Yes' : 'No' ) . '**<br/>';
					echo '* Requirements: **' . ( $sweeper['high'] ? 'High' : 'Normal' ) . '**<br/><br/>';
				}
			}
		}
	} else {
		$_grid = panel()->get_table_object();
		$_grid->prepare_items();
		$_grid->display();
	}

	?>
</div>
