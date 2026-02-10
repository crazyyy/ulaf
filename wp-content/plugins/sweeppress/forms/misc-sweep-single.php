<?php

use Dev4Press\v54\Core\Quick\File;
use Dev4Press\v54\Core\Quick\Sanitize;

/** @var \Dev4Press\Plugin\SweepPress\Base\Sweeper $sweeper */

$tasks       = $sweeper->is_no_size();
$total       = array(
	'tasks'   => 0,
	'items'   => 0,
	'records' => 0,
	'size'    => 0,
);
$refresh     = false;
$force_cache = $force_cache ?? false;

foreach ( $sweeper->get_tasks() as $task => $data ) {
	if ( $tasks ) {
		$total['tasks'] ++;
	}

	$info          = array();
	$is_cpt        = isset( $data['type'] ) && $data['type'] == 'post_type';
	$is_tax        = isset( $data['type'] ) && $data['type'] == 'taxonomy';
	$data['items'] = $data['items'] ?? 0;

	if ( $tasks || ( $data['items'] > 0 || $data['records'] > 0 || $data['size'] > 0 ) ) {
		$item_class = array( 'sweeppress-item-task' );
		$item_name  = 'sweeppress[sweeper][' . $sweeper->get_category() . '][' . $sweeper->get_code() . '][' . $task . ']';

		if ( $is_cpt || $is_tax ) {
			$item_class[] = $data['registered'] ? 'task-is-registered' : 'task-is-missing';
		}

		echo '<div class="' . Sanitize::html_classes( $item_class ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput

		if ( ( $is_cpt || $is_tax ) && ! $data['registered'] ) {
			echo '<i title="' . esc_attr__( 'Not currently registered.', 'sweeppress' ) . '" class="d4p-icon d4p-ui-warning"></i> ';
		}

		echo esc_html( $data['title'] );

		if ( isset( $data['real_title'] ) ) {
			echo ' [<span>' . esc_html( $data['real_title'] ) . '</span>]';
		}

		if ( $data['items'] > 0 || $data['records'] > 0 || $data['size'] > 0 ) {
			$preview_url = $sweeper->get_preview_task_url( $task );

			echo ' (<span class="sweeppress-item-task-stats">';

			if ( $data['items'] > 0 ) {
				$total['items'] += absint( $data['items'] );
				echo '<span>' . sprintf( esc_html( $sweeper->items_count_n( absint( $data['items'] ) ) ), '<strong>' . absint( $data['items'] ) . '</strong>' ) . '</span>';
			}

			if ( $data['records'] > 0 ) {
				$_records         = absint( $data['records'] );
				$total['records'] += $_records;
				echo '<span>' . sprintf( esc_html( _n( '%s record', '%s records', $_records, 'sweeppress' ) ), '<strong>' . esc_html( $_records ) . '</strong>' ) . '</span>';
			}

			if ( $data['size'] > 0 ) {
				$total['size'] += absint( $data['size'] );
				echo '<span>' . File::size_format( absint( $data['size'] ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}

			echo '</span>)';

			if ( ! empty( $preview_url ) ) {
				echo '<span class="sweeppress-item-task-preview" title="' . esc_attr__( 'You can preview data that this sweeper will remove.', 'sweeppress' ) . '"><a target="_blank" href="' . $preview_url . '"><i aria-label="' . esc_attr__( 'Preview Data.', 'sweeppress' ) . '" class="d4p-icon d4p-ui-search" style="color: #00A"></i></a></span>';
			}
		}

		echo '<input data-size="' . esc_attr( $data['size'] ) . '" data-records="' . esc_attr( $data['records'] ) . '" class="sweeppress-task-check" type="checkbox" value="sweep" name="' . esc_attr( $item_name ) . '" />';

		echo '</div>';
	} else {
		echo '<div class="sweeppress-item-task empty-task">';
		echo esc_html( $data['title'] );

		if ( isset( $data['real_title'] ) ) {
			echo ' [<span>' . esc_html( $data['real_title'] ) . '</span>]';
		}

		echo '</div>';
	}
}

?>
    <div class="sweeppress-item-total">
		<?php

		if ( $total['size'] > 0 ) {
			$percentage = $sweeper->calculate_percentage( $total['size'] );
			$total_size = File::size_format( $sweeper->affected_tables_size(), 2, ' ', false );

			if ( $percentage < 0.5 ) {
				$percentage = '< 0.5';
			}

			echo '<span title="' . esc_attr( sprintf( __( 'Estimated from the total size of affected tables (%s).', 'sweeppress' ), $total_size ) ) . '" class="sweeppress-affected-percentage">' . esc_html( $percentage ) . '%</span>';
		}

		?>
        <p>
			<?php

			if ( $total['tasks'] > 0 || $total['items'] > 0 || $total['records'] > 0 || $total['size'] > 0 ) {
				echo '<strong>' . esc_html__( 'Totals', 'sweeppress' ) . ':</strong>';

				echo ' <span class="sweeppress-item-task-stats">';

				if ( $total['tasks'] > 0 ) {
					echo '<span>' . sprintf( esc_html( $sweeper->tasks_count_n( absint( $total['tasks'] ) ) ), '<strong>' . absint( $total['tasks'] ) . '</strong>' ) . '</span>';
				}

				if ( $total['items'] > 0 ) {
					echo '<span>' . sprintf( esc_html( $sweeper->items_count_n( absint( $total['items'] ) ) ), '<strong>' . absint( $total['items'] ) . '</strong>' ) . '</span>';
				}

				if ( $total['records'] > 0 ) {
					$_records = absint( $total['records'] );
					echo '<span>' . sprintf( esc_html( _n( '%s record', '%s records', $_records, 'sweeppress' ) ), '<strong>' . esc_html( $_records ) . '</strong>' ) . '</span>';
				}

				if ( $total['size'] > 0 ) {
					echo '<span>' . File::size_format( absint( $total['size'] ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
				}

				if ( $sweeper->is_cached() || $force_cache ) {
					$refresh = true;

					echo '<span>';
					if ( $sweeper->is_cached() ) {
						echo '<strong>' . esc_html__( 'From Cache', 'sweeppress' ) . '</strong> ';
					}
					echo '<a class="sweeper-item-refresh" data-cache="clear" data-sweeper="' . esc_attr( $sweeper->get_code() ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'sweeppress-run-estimate-' . $sweeper->get_code() ) ) . '" href="#">' . esc_html__( 'Refresh', 'sweeppress' ) . '</a>';
					echo '</span>';
				}

				echo '</span>';
			} else {
				esc_html_e( 'Nothing to sweep.', 'sweeppress' );
			}

			?>
        </p>
    </div>
<?php if ( $refresh ) { ?>
    <div class="sweeppress-item-run-loader">
        <i class="d4p-icon d4p-ui-spinner d4p-icon-spin d4p-icon-fw"></i>
        <span><?php esc_html_e( 'Please wait...', 'sweeppress' ); ?></span>
    </div>
<?php }
