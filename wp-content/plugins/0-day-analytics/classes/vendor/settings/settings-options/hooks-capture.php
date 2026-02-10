<?php
/**
 * Hooks Capture settings for the plugin.
 *
 * Settings interface for controlling hook monitoring and data capture.
 *
 * @package advanced-analytics
 *
 * @since 4.5.0
 */

use ADVAN\Helpers\Settings;
use ADVAN\Entities\Hook_Groups_Entity;

$settings = Settings::get_current_options();
Settings::set_current_options( $settings );

Settings::build_option(
	array(
		'title' => esc_html__( 'Hooks Capture Options', '0-day-analytics' ),
		'id'    => 'hooks-capture-options-settings-tab',
		'type'  => 'tab-title',
	)
);

Settings::build_option(
	array(
		'type'  => 'header',
		'id'    => 'hooks-capture-settings-options',
		'title' => \esc_html__( 'Hooks Capture Module', '0-day-analytics' ),
	)
);

Settings::build_option(
	array(
		'name'    => \esc_html__( 'Enable Hooks Capture Module', '0-day-analytics' ),
		'id'      => 'hooks_capture_module_enabled',
		'type'    => 'checkbox',
		'hint'    => \esc_html__( 'Monitor WordPress hooks and capture execution data. If disabled, the Hooks Capture and Hooks Management menu items will be hidden.', '0-day-analytics' ),
		'toggle'  => '#advana_hooks_capture_settings-item',
		'default' => Settings::get_option( 'hooks_capture_module_enabled' ),
	)
);

?>
<div id="advana_hooks_capture_settings-item">
<?php

$schedules = \wp_get_schedules();
$options   = array(
	'-1' => \esc_html__( 'Disabled', '0-day-analytics' ),
);
foreach ( $schedules as $schedule => $text ) {
	$options[ $schedule ] = $text['display'];
}

Settings::build_option(
	array(
		'name'    => \esc_html__( 'Hooks Capture Auto-cleanup', '0-day-analytics' ),
		'id'      => 'advana_hooks_capture_clear',
		'type'    => 'select',
		'options' => $options,
		'default' => Settings::get_option( 'advana_hooks_capture_clear' ),
		'hint'    => \esc_html__( 'Automatically delete old captured hook data to prevent database bloat. Recommended: weekly or daily.', '0-day-analytics' ),
	)
);

Settings::build_option(
	array(
		'type'  => 'header',
		'id'    => 'hook-groups-management',
		'title' => \esc_html__( 'Hook Groups Management', '0-day-analytics' ),
	)
);

Settings::build_option(
	array(
		'name' => \esc_html__( 'Create and manage hook groups for better organization and visual identification of captured hooks.', '0-day-analytics' ),
		'id'   => 'hook_groups_description',
		'type' => 'html',
		'html' => '<div id="hook-groups-manager"></div>',
	)
);

	// Load existing groups
if ( \class_exists( '\ADVAN\Entities\Hook_Groups_Entity' ) ) {
	$groups = Hook_Groups_Entity::get_groups_array();
	?>
		<table class="wp-list-table widefat fixed striped" id="hook-groups-table">
			<thead>
				<tr>
					<th><?php \esc_html_e( 'Name', '0-day-analytics' ); ?></th>
					<th><?php \esc_html_e( 'Color', '0-day-analytics' ); ?></th>
					<th><?php \esc_html_e( 'Description', '0-day-analytics' ); ?></th>
					<th><?php \esc_html_e( 'Actions', '0-day-analytics' ); ?></th>
				</tr>
			</thead>
			<tbody id="hook-groups-tbody">
			<?php if ( empty( $groups ) ) { ?>
					<tr id="no-groups-row">
						<td colspan="4"><?php \esc_html_e( 'No hook groups created yet.', '0-day-analytics' ); ?></td>
					</tr>
				<?php } else { ?>
					<?php foreach ( $groups as $group_id => $group ) { ?>
						<tr data-group-id="<?php echo \esc_attr( $group_id ); ?>">
							<td><?php echo \esc_html( $group['name'] ); ?></td>
							<td>
								<span class="group-color-indicator" style="background-color: <?php echo \esc_attr( $group['color'] ); ?>;"></span>
								<?php echo \esc_html( $group['color'] ); ?>
							</td>
							<td><?php echo \esc_html( Hook_Groups_Entity::get_group( $group_id )['description'] ?? '' ); ?></td>
							<td>
								<button type="button" class="button edit-group-btn" data-group-id="<?php echo \esc_attr( $group_id ); ?>">
									<?php \esc_html_e( 'Edit', '0-day-analytics' ); ?>
								</button>
								<button type="button" class="button delete-group-btn" data-group-id="<?php echo \esc_attr( $group_id ); ?>">
									<?php \esc_html_e( 'Delete', '0-day-analytics' ); ?>
								</button>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
			</tbody>
		</table>

		<p>
			<button type="button" class="button button-primary" id="add-new-group-btn">
			<?php \esc_html_e( 'Add New Group', '0-day-analytics' ); ?>
			</button>
		</p>

		<!-- Group Form Modal -->
		<div id="group-form-modal">
			<div class="group-form-overlay"></div>
			<div class="group-form-container">
				<h3 id="group-form-title"><?php \esc_html_e( 'Add New Hook Group', '0-day-analytics' ); ?></h3>
				<?php
					Settings::build_option(
						array(
							'name' => esc_html__( 'Group Name:', '0-day-analytics' ),
							'id'   => 'group-name',
							'type' => 'text',
						)
					);
					Settings::build_option(
						array(
							'name'    => esc_html__( 'Color:', '0-day-analytics' ),
							'id'      => 'group-color',
							'type'    => 'color',
							'default' => '#007cba',
						)
					);
					Settings::build_option(
						array(
							'name' => esc_html__( 'Description:', '0-day-analytics' ),
							'id'   => 'group-description',
							'type' => 'textarea',
							'default' => '',
						)
					);
				?>
				<p>
					<button class="button button-primary"><?php \esc_html_e( 'Save Group', '0-day-analytics' ); ?></button>
					<button type="button" class="button" id="cancel-group-btn"><?php \esc_html_e( 'Cancel', '0-day-analytics' ); ?></button>
				</p>
			</div>
		</div>
	<?php
}
?>

<script type="text/javascript">
jQuery(document).ready(function($) {
	// Modal show/hide functions
	function showModal() {
		$('#group-form-modal').show();
	}

	function hideModal() {
		$('#group-form-modal').hide();
		$('#group-form-modal').removeData('editing-group-id');
	}

	// Add new group button
	$('#add-new-group-btn').on('click', function() {
		$('#group-form-title').text('<?php \esc_html_e( 'Add New Hook Group', '0-day-analytics' ); ?>');
		$('#group-name').val('');
		$('#group-description').val('');
		$('#group-form-modal').removeData('editing-group-id');
		showModal();
		$('#group-color').wpColorPicker('color', '#007cba');
	});

	// Edit group button
	$(document).on('click', '.edit-group-btn', function() {
		var groupId = $(this).data('group-id');
		var row = $('tr[data-group-id="' + groupId + '"]');
		var name = row.find('td:first').text().trim();
		var color = row.find('td').eq(1).text().trim();
		var description = row.find('td').eq(2).text().trim();

		$('#group-form-title').text('<?php \esc_html_e( 'Edit Hook Group', '0-day-analytics' ); ?>');
		$('#group-name').val(name);
		$('#group-description').val(description);
		$('#group-form-modal').data('editing-group-id', groupId);
		$('#group-color').wpColorPicker('color', color);
		showModal();
	});

	// Delete group button
	$(document).on('click', '.delete-group-btn', function() {
		if (!confirm('<?php \esc_html_e( 'Are you sure you want to delete this hook group?', '0-day-analytics' ); ?>')) {
			return;
		}

		var groupId = $(this).data('group-id');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'advan_delete_hook_group',
				group_id: groupId,
				nonce: '<?php echo \wp_create_nonce( 'advan_hook_groups' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert(response.data || '<?php \esc_html_e( 'Error deleting group.', '0-day-analytics' ); ?>');
				}
			},
			error: function() {
				alert('<?php \esc_html_e( 'AJAX error occurred.', '0-day-analytics' ); ?>');
			}
		});
	});

	// Cancel button
	$('#cancel-group-btn').on('click', function() {
		hideModal();
	});

	// Save button - prevent form submission and handle via AJAX
	$('#group-form-modal .button-primary').on('click', function(e) {
		e.preventDefault();

		var editingGroupId = $('#group-form-modal').data('editing-group-id');
		var isEditing = typeof editingGroupId !== 'undefined' && editingGroupId !== null;

		var formData = {
			action: 'advan_save_hook_group',
			group_id: isEditing ? editingGroupId : 0,
			name: $('#group-name').val(),
			color: $('#group-color').val(),
			description: $('#group-description').val(),
			nonce: '<?php echo \wp_create_nonce( 'advan_hook_groups' ); ?>'
		};

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			success: function(response) {
				if (response.success) {
					hideModal();
					location.reload();
				} else {
					alert(response.data || '<?php \esc_html_e( 'Error saving group.', '0-day-analytics' ); ?>');
				}
			},
			error: function() {
				alert('<?php \esc_html_e( 'AJAX error occurred.', '0-day-analytics' ); ?>');
			}
		});
	});

	// Helper function to convert RGB/RGBA to hex
	function rgbToHex(rgb) {
		if (!rgb || !rgb.startsWith('rgb')) {
			return rgb;
		}
		var result = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*[\d.]+)?\)$/);
		if (result) {
			return "#" +
				("0" + parseInt(result[1], 10).toString(16)).slice(-2) +
				("0" + parseInt(result[2], 10).toString(16)).slice(-2) +
				("0" + parseInt(result[3], 10).toString(16)).slice(-2);
		}
		return rgb;
	}

	// Close modal when clicking overlay
	$('.group-form-overlay').on('click', function() {
		hideModal();
	});
});
</script>

<style>
.group-color-indicator {
	display: inline-block;
	width: 16px;
	height: 16px;
	border-radius: 50%;
	margin-right: 8px;
	border: 1px solid #ccc;
}
#group-form-modal {
	display: none;
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	z-index: 9999;
}
.group-form-overlay {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, 0.5);
}
.group-form-container {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	padding: 20px;
	border-radius: 8px;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
	min-width: 400px;
	max-width: 600px;
}
</style>

</div>
