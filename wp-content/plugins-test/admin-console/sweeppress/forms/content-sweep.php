<?php

use Dev4Press\Plugin\SweepPress\Basic\Database;
use Dev4Press\v54\Core\Quick\Sanitize;
use function Dev4Press\v54\Functions\panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$method = sweeppress_settings()->get_method();
$list   = sweeppress_core()->available();

$total      = 0;
$active     = 0;
$items      = array();
$categories = array();
$_status    = array();

foreach ( $list as $category => $sweepers ) {
	$active_for_category = 0;

	foreach ( $sweepers as $sweeper ) {
		$_status[ $sweeper->get_code() ] = $method == 'normal' || ( $method == 'partial' && ! $sweeper->has_high_system_requirements() );

		if ( ! $_status[ $sweeper->get_code() ] && $sweeper->has_cached_data() ) {
			$_status[ $sweeper->get_code() ] = true;
		}

		$total ++;

		if ( $_status[ $sweeper->get_code() ] ) {
			if ( $sweeper->is_sweepable() ) {
				$active ++;
				$active_for_category ++;
			}
		} else {
			$active ++;
			$active_for_category ++;
		}
	}

	$categories[ $category ] = $active_for_category;
}

Database::instance();

?>
<div class="d4p-content d4p-sweeper-content">
	<?php require SWEEPPRESS_PATH . 'forms/misc-notices-backup.php'; ?>

    <div id="sweeppress-results-wrapper" class="d4p-group" style="max-width: 1000px" hidden>
        <h3><?php esc_html_e( 'Sweep Progress Report', 'sweeppress' ); ?></h3>
        <div class="d4p-group-inner">
            <div id="sweeppress-results-sweeper">
                <div class="sweeppress-results-loader">
                    <i class="d4p-icon d4p-ui-spinner d4p-icon-spin"></i>
                    <span><?php esc_html_e( 'Please wait for the sweeping to finish...', 'sweeppress' ); ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="sweeppress-sweepers-wrapper">
        <div class="sweeppress-sweepers-controls">
            <dl class="sweeppress-list">
                <dt><?php esc_html_e( 'Sweepers Available', 'sweeppress' ); ?></dt>
                <dd>
					<?php

					printf( esc_html__( 'Active: %s / Total: %s', 'sweeppress' ), '<strong>' . esc_html( $active ) . '</strong>', '<strong>' . esc_html( $total ) . '</strong>' );

					if ( $active < $total ) {
						echo '<button class="toggle-empty-tasks" type="button" title="' . esc_attr__( 'Toggle Empty Sweepers', 'sweeppress' ) . '" aria-expanded="false"><i aria-hidden="true" class="d4p-icon d4p-ui-eye"></i></button>';
					}

					?>
                </dd>
            </dl>
        </div>

		<?php

		foreach ( $list as $category => $sweepers ) {
			echo '<h4 data-cat="' . esc_attr( $category ) . '" id="sweeper-category-' . esc_attr( $category ) . '" class="sweeper-category-label ' . ( $categories[ $category ] == 0 ? 'empty-sweeper-category' : '' ) . '">' . esc_html( sweeppress_core()->get_category_label( $category ) ) . '</h4>';

			foreach ( $sweepers as $sweeper ) {
				$run = $_status[ $sweeper->get_code() ];
				$is  = $run && $sweeper->is_sweepable();

				$classes = array(
					'sweeppress-item-wrapper',
					'sweeppress-item-cat-' . $sweeper->get_category(),
				);

				if ( $run && ! $is ) {
					$classes[] = 'empty-sweeper';
				}

				$affected_tables = $sweeper->affected_tables();

				?>

                <div class="<?php echo Sanitize::html_classes( $classes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
                    <h5>
						<?php

						$_toggle = $is && $sweeper->has_empty_tasks() && ! $sweeper->is_no_size();

						echo esc_html( $sweeper->title() );
						echo '<button class="toggle-section toggle-help" type="button" title="' . esc_attr__( 'Toggle Help', 'sweeppress' ) . '" aria-expanded="false" aria-controls="sweeper-info-' . esc_attr( $sweeper->get_unique_id() ) . '"><i aria-hidden="true" class="d4p-icon d4p-ui-question-sqaure"></i></button>';

						if ( ! empty( $sweeper->limitations() ) ) {
							echo '<button class="toggle-section toggle-limits" type="button" title="' . esc_attr__( 'Toggle Limitations', 'sweeppress' ) . '" aria-expanded="false" aria-controls="sweeper-limit-' . esc_attr( $sweeper->get_unique_id() ) . '"><i aria-hidden="true" class="d4p-icon d4p-ui-exclamation-square"></i></button>';
						}

						if ( ! empty( $affected_tables ) ) {
							echo '<button class="toggle-section toggle-tables" type="button" title="' . esc_attr__( 'Toggle Affected Tables', 'sweeppress' ) . '" aria-expanded="false" aria-controls="sweeper-tables-' . esc_attr( $sweeper->get_unique_id() ) . '"><i aria-hidden="true" class="d4p-icon d4p-ui-database"></i></button>';
						}

						echo '<button style="display: ' . ( $_toggle ? 'inline' : 'none' ) . '" class="toggle-empty" type="button" title="' . esc_attr__( 'Toggle Empty Tasks', 'sweeppress' ) . '" aria-expanded="false"><i aria-hidden="true" class="d4p-icon d4p-ui-eye"></i></button>';

						if ( $sweeper->for_backup() ) {
							echo '<span class="flat-system" title="' . esc_attr__( 'This sweeper supports data backup before removal.', 'sweeppress' ) . '"><i class="d4p-icon d4p-ui-archive" style="color: #00A"></i></span>';
						}

						if ( $sweeper->has_high_system_requirements() ) {
							echo '<span class="flat-system" title="' . esc_attr__( 'This sweeper estimation process has high system requirements and it can be slow to estimate.', 'sweeppress' ) . '"><i class="d4p-icon d4p-ui-gauge-bolt"></i></span>';
						}

						if ( $sweeper->has_preview_url() ) {
							echo '<span class="flat-system" title="' . esc_attr__( 'You can preview data that this sweeper will remove.', 'sweeppress' ) . '"><a target="_blank" href="' . $sweeper->get_preview_url() . '"><i aria-label="' . esc_attr__( 'Preview Data.', 'sweeppress' ) . '" class="d4p-icon d4p-ui-search" style="color: #00A"></i></a></span>';
						}

						echo '<input style="display: ' . ( $is ? 'block' : 'none' ) . '" class="sweeppress-sweeper-check" type="checkbox" />';

						?>
                    </h5>
                    <div class="sweeppress-item-help" hidden id="sweeper-info-<?php echo esc_attr( $sweeper->get_unique_id() ); ?>">
                        <p>
							<?php echo esc_html( $sweeper->description() ); ?>
                        </p>
						<?php

						if ( ! empty( $sweeper->help() ) ) {
							echo wp_kses_post( sweeppress_strings_array_to_list( $sweeper->help() ) );

							$_last_used = sweeppress_settings()->get_sweeper_last_used_timestamp( $sweeper->get_code() );

							if ( $_last_used > 0 ) {
								echo '<div class="sweeppress-item-last-used">' . sprintf( esc_html__( 'This sweeper was last used: %s ago.', 'sweeppress' ), '<strong>' . esc_html( human_time_diff( time(), $_last_used ) ) . '</strong>' ) . '</div>';
							}
						}

						?>
                    </div>
					<?php

					if ( ! empty( $affected_tables ) ) {
						?>

                        <div class="sweeppress-item-tables" hidden id="sweeper-tables-<?php echo esc_attr( $sweeper->get_unique_id() ); ?>">
                            <p><?php esc_html_e( 'This sweeper affects database tables listed below.', 'sweeppress' ); ?></p>
                            <ul>
                                <li><?php echo join( '</li><li>', $affected_tables );  // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
                            </ul>
                        </div>

						<?php
					}

					if ( ! empty( $sweeper->limitations() ) ) {
						?>

                        <div class="sweeppress-item-limit" hidden id="sweeper-limit-<?php echo esc_attr( $sweeper->get_unique_id() ); ?>">
                            <p><?php esc_html_e( 'This sweeper has few limitations listed below.', 'sweeppress' ); ?></p>
							<?php echo wp_kses_post( sweeppress_strings_array_to_list( $sweeper->limitations() ) ); ?>
                        </div>

						<?php
					}

					if ( $sweeper->has_days_to_keep() && sweeppress_settings()->get( 'show_keep_days_notices', 'sweepers' ) ) {
						?>

                        <div class="sweeppress-item-days-notice">
							<?php

							if ( $sweeper->get_days_to_keep() > 0 ) {
								echo sprintf( esc_html__( 'Only items older than %s days are included in the estimate, and will be affected by the sweeping. This is done to protect the latest items in case they are needed later.', 'sweeppress' ), $sweeper->get_days_to_keep() );
							} else {
								echo esc_html__( 'All items are included in the estimate, and will be affected by sweeping. This sweeper supports setting the number of days of items to keep from this sweeper.', 'sweeppress' );
							}

							echo ' ' . esc_html__( 'If you want to change the days limit, you can do it from plugin settings panel.', 'sweeppress' );
							echo ' <a href="' . panel()->a()->panel_url( 'settings', 'sweepers' ) . '">' . esc_html__( 'Sweepers Settings', 'sweeppress' ) . '</a>';

							?>
                        </div>

						<?php
					}

					?>
                    <div class="sweeppress-item-inside">
						<?php

						if ( $run ) {
							include 'misc-sweep-single.php';
						} else {
							?>

                            <div class="notice inline notice-info sweeppress-item-run">
                                <p><strong><?php

										if ( $sweeper->has_high_system_requirements() ) {
											esc_html_e( 'This sweeper estimation query can be slow, and based on your plugin performance settings and database size, it is not run automatically.', 'sweeppress' );
										} else {
											esc_html_e( 'Based on your plugin performance settings and database size, this sweeper estimate has not been run.', 'sweeppress' );
										}

										?></strong></p>
                                <p>
									<?php esc_html_e( 'Click the button to run this sweeper in the background.', 'sweeppress' ); ?>
                                </p>
                                <p>
                                    <button data-cache="skip" data-sweeper="<?php echo esc_attr( $sweeper->get_code() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'sweeppress-run-estimate-' . $sweeper->get_code() ) ); ?>" type="button" class="button-secondary"><?php esc_html_e( 'Estimate This Sweeper', 'sweeppress' ); ?></button>
                                </p>
                            </div>
                            <div class="sweeppress-item-run-loader">
                                <i class="d4p-icon d4p-ui-spinner d4p-icon-spin d4p-icon-fw"></i>
                                <span><?php esc_html_e( 'Please wait...', 'sweeppress' ); ?></span>
                            </div>

							<?php
						}

						?>
                    </div>
                </div>

				<?php
			}
		}

		?>
    </div>
    <input type="hidden" value="<?php echo wp_create_nonce( 'sweeppress-sweep-panel-sweeper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" name="sweeppress[nonce]"/>
</div>
