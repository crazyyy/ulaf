<?php

use Dev4Press\Plugin\SweepPress\Meta\Options;
use Dev4Press\v54\Core\Quick\File;
use function Dev4Press\v54\Functions\panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tasks = Options::instance()->quick_tasks();

?>
<div class="d4p-content">
    <div class="d4p-cards-wrapper">
		<?php

		require SWEEPPRESS_PATH . 'forms/misc-notices-backup.php';
		require SWEEPPRESS_PATH . 'forms/misc-notices-meta.php';

		?>
        <div class="d4p-group d4p-dashboard-card">
            <h3><?php esc_html_e( 'Remove Settings for Missing Widgets', 'sweeppress' ); ?></h3>
            <div class="d4p-group-header">
                <ul>
                    <li>
                        <i class="d4p-icon d4p-icon-fw d4p-ui-tags"></i>
                        <strong><?php echo esc_html( $tasks['widgets-missing']['count'] ); ?></strong>
                        <span><?php esc_html_e( 'Records', 'sweeppress' ); ?></span>
                    </li>
                    <li>
                        <i class="d4p-icon d4p-icon-fw d4p-ui-database"></i>
                        <strong><?php echo esc_html( File::size_format( $tasks['widgets-missing']['size'], 2, ' ', false ) ); ?></strong>
                        <span><?php esc_html_e( 'Size', 'sweeppress' ); ?></span>
                    </li>
                </ul>
            </div>
            <div class="d4p-group-inner">
                <p><?php esc_html_e( 'Each widget has a option where it stores all widget instances settings. If the option is associated with the widget that is no longer registered on the website, it means it is no longer used, and it is safe to remove.', 'sweeppress' ); ?></p>
            </div>
            <div class="d4p-group-footer">
				<?php if ( $tasks['widgets-missing']['count'] > 0 ) { ?>
                    <a class="button-primary sweeppress-confirm-url" href="#" data-url="<?php echo esc_url( panel()->a()->action_url( 'quick-widgets-missing', 'sweeppress-options-quick-widgets-missing', '', 'options', 'quick' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php esc_html_e( 'Remove Options', 'sweeppress' ); ?></a>
                    <a class="button-secondary" href="<?php echo esc_url( panel()->a()->panel_url( 'options', '', 'filter-source=widget&filter-status=missing' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php esc_html_e( 'Preview the Options', 'sweeppress' ); ?></a>
				<?php } else {
					esc_html_e( 'No data to remove for this task.', 'sweeppress' );
				} ?>
            </div>
        </div>

        <div class="d4p-group d4p-dashboard-card">
            <h3><?php esc_html_e( 'Remove Settings for Known but Missing Sources', 'sweeppress' ); ?></h3>
            <div class="d4p-group-header">
                <ul>
                    <li>
                        <i class="d4p-icon d4p-icon-fw d4p-ui-tags"></i>
                        <strong><?php echo esc_html( $tasks['known-missing']['count'] ); ?></strong>
                        <span><?php esc_html_e( 'Records', 'sweeppress' ); ?></span>
                    </li>
                    <li>
                        <i class="d4p-icon d4p-icon-fw d4p-ui-database"></i>
                        <strong><?php echo esc_html( File::size_format( $tasks['known-missing']['size'], 2, ' ', false ) ); ?></strong>
                        <span><?php esc_html_e( 'Size', 'sweeppress' ); ?></span>
                    </li>
                </ul>
            </div>
            <div class="d4p-group-inner">
                <p><?php esc_html_e( 'If you had a plugin that is no longer used, it most likely left options that are no longer needed. If the SweepPress can identify those options, it is safe to remove all the old settings belonging to plugins that are no longer installed and active.', 'sweeppress' ); ?></p>
            </div>
            <div class="d4p-group-footer">
				<?php if ( $tasks['known-missing']['count'] > 0 ) { ?>
                    <a class="button-primary sweeppress-confirm-url" href="#" data-url="<?php echo esc_url( panel()->a()->action_url( 'quick-known-missing', 'sweeppress-options-quick-known-missing', '', 'options', 'quick' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php esc_html_e( 'Remove Options', 'sweeppress' ); ?></a>
                    <a class="button-secondary" href="<?php echo esc_url( panel()->a()->panel_url( 'options', '', 'filter-source=known&filter-status=missing' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php esc_html_e( 'Preview the Options', 'sweeppress' ); ?></a>
				<?php } else {
					esc_html_e( 'No data to remove for this task.', 'sweeppress' );
				} ?>
            </div>
        </div>

        <div class="d4p-group d4p-dashboard-card">
            <h3><?php esc_html_e( 'Remove Settings for Installed and Inactive Sources', 'sweeppress' ); ?></h3>
            <div class="d4p-group-header">
                <ul>
                    <li>
                        <i class="d4p-icon d4p-icon-fw d4p-ui-tags"></i>
                        <strong><?php echo esc_html( $tasks['known-installed']['count'] ); ?></strong>
                        <span><?php esc_html_e( 'Records', 'sweeppress' ); ?></span>
                    </li>
                    <li>
                        <i class="d4p-icon d4p-icon-fw d4p-ui-database"></i>
                        <strong><?php echo esc_html( File::size_format( $tasks['known-installed']['size'], 2, ' ', false ) ); ?></strong>
                        <span><?php esc_html_e( 'Size', 'sweeppress' ); ?></span>
                    </li>
                </ul>
            </div>
            <div class="d4p-group-inner">
                <p><?php esc_html_e( 'You can have plugins installed, but not active, and if you don\'t plan to use those plugins in the future, their options records can be removed.', 'sweeppress' ); ?></p>
                <p>
                    <strong><?php esc_html_e( 'If you plan to use disabled plugins again, don\'t remove the options associated with those plugins. If you don\'t plan to use disabled plugins again, remove them from the website.', 'sweeppress' ); ?></strong>
                </p>
            </div>
            <div class="d4p-group-footer">
				<?php if ( $tasks['known-installed']['count'] > 0 ) { ?>
                    <a class="button-primary sweeppress-confirm-url" href="#" data-url="<?php echo esc_url( panel()->a()->action_url( 'quick-known-installed', 'sweeppress-options-quick-known-installed', '', 'options', 'quick' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php esc_html_e( 'Remove Options', 'sweeppress' ); ?></a>
                    <a class="button-secondary" href="<?php echo esc_url( panel()->a()->panel_url( 'options', '', 'filter-source=known&filter-status=installed' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php esc_html_e( 'Preview the Options', 'sweeppress' ); ?></a>
				<?php } else {
					esc_html_e( 'No data to remove for this task.', 'sweeppress' );
				} ?>
            </div>
        </div>

        <div class="d4p-group d4p-dashboard-card">
            <h3><?php esc_html_e( 'Remove Settings for Unknown Sources', 'sweeppress' ); ?></h3>
            <div class="d4p-group-header">
                <ul>
                    <li>
                        <i class="d4p-icon d4p-icon-fw d4p-ui-tags"></i>
                        <strong><?php echo esc_html( $tasks['unknown']['count'] ); ?></strong>
                        <span><?php esc_html_e( 'Records', 'sweeppress' ); ?></span>
                    </li>
                    <li>
                        <i class="d4p-icon d4p-icon-fw d4p-ui-database"></i>
                        <strong><?php echo esc_html( File::size_format( $tasks['unknown']['size'], 2, ' ', false ) ); ?></strong>
                        <span><?php esc_html_e( 'Size', 'sweeppress' ); ?></span>
                    </li>
                </ul>
            </div>
            <div class="d4p-group-inner">
                <p><?php esc_html_e( 'Unknown sources can point to options no longer been used, or plugin was unable to detect the source. Be careful with removal of all Unknown options, it can lead to problems.', 'sweeppress' ); ?></p>
                <p>
                    <strong><?php esc_html_e( 'To make sure that SweepPress has identified all the options sources, you should configure the plugin to detect which options are used by the currently active plugins and theme.', 'sweeppress' ); ?></strong>
                </p>
            </div>
            <div class="d4p-group-footer">
				<?php if ( $tasks['unknown']['count'] > 0 ) { ?>
                    <a class="button-primary sweeppress-confirm-url" href="#" data-url="<?php echo esc_url( panel()->a()->action_url( 'quick-unknown', 'sweeppress-options-quick-unknown', '', 'options', 'quick' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php esc_html_e( 'Remove Options', 'sweeppress' ); ?></a>
                    <a class="button-secondary" href="<?php echo esc_url( panel()->a()->panel_url( 'options', '', 'filter-source=unknown' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php esc_html_e( 'Preview the Options', 'sweeppress' ); ?></a>
                    <a class="button-secondary" href="https://www.dev4press.com/kb/user-guide/sweeppress-feature-options-sitemeta-and-metadata-management/" target="_blank" rel="external noopener"><?php esc_html_e( 'User Guide', 'sweeppress' ); ?></a>
				<?php } else {
					esc_html_e( 'No data to remove for this task.', 'sweeppress' );
				} ?>
            </div>
        </div>
    </div>
</div>
