<?php

use Dev4Press\Plugin\SweepPress\Meta\Tracker;
use function Dev4Press\v54\Functions\panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

Tracker::instance()->prepare_plugins( true );
Tracker::instance()->prepare_themes( true );

?>
<div class="d4p-content">
	<?php

	require SWEEPPRESS_PATH . 'forms/misc-notices-backup.php';
	require SWEEPPRESS_PATH . 'forms/misc-notices-meta.php';

	?>

    <form method="get" action="">
        <input type="hidden" name="page" value="sweeppress-options"/>
        <input type="hidden" value="getback" name="sweeppress_handler"/>

		<?php

		$_grid = panel()->get_table_object();
		$_grid->prepare_table();
		$_grid->prepare_items();
		$_grid->search_box( esc_html__( 'Search Options', 'sweeppress' ), 'table' );
		$_grid->display();

		?>
    </form>
</div>
<div style="display: none">
    <div title="<?php esc_attr_e( 'Are you sure?', 'sweeppress' ); ?>" id="sweeppress-dialog-option-key-delete">
        <div class="sweeppress-inner-content">
            <h4 style="margin: 0 0 1em"><?php esc_html_e( 'Option to delete', 'sweeppress' ); ?>: <strong>OPTION_NAME</strong></h4>
            <p><?php esc_html_e( 'Deleting this option is not reversible, and the option value stored will be lost!', 'sweeppress' ); ?><p>
            <p>
                <strong><?php esc_html_e( 'Make sure you have the database backup ready before you do this. You are running this operation at your own risk!!!', 'sweeppress' ); ?></strong>
            </p>
        </div>
    </div>
    <div title="<?php esc_attr_e( 'Are you sure?', 'sweeppress' ); ?>" id="sweeppress-dialog-option-bulk-delete">
        <div class="sweeppress-inner-content">
            <p><?php esc_html_e( 'Deleting selected options is not reversible, and the options values stored will be lost!', 'sweeppress' ); ?></p>
            <p>
                <strong><?php esc_html_e( 'Make sure you have the database backup ready before you do this. You are running this operation at your own risk!!!', 'sweeppress' ); ?></strong>
            </p>
        </div>
    </div>
</div>
