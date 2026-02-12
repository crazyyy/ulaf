<div id="abe-copy-visibility-dialog" title="Copy Visibility" class="hidden">
	<div class="ws_dialog_subpanel">
		<label for="abe-copy-source-actor">
			Copy item visibility from:
		</label><br>
		<select id="abe-copy-source-actor"
		        data-bind="options: copyVisibilityDialog.validCopySourceActors, optionsText: 'name',
		        value: copyVisibilityDialog.copySourceActor, optionsCaption: 'Choose source role...'">
		</select>
	</div>

	<div class="ws_dialog_subpanel">
		<label for="abe-copy-destination-actor">
			To:
		</label><br>
		<select id="abe-copy-destination-actor"
		        data-bind="options: copyVisibilityDialog.validCopyTargetActors, optionsText: 'name',
		        value: copyVisibilityDialog.copyTargetActor, optionsCaption: 'Choose destination role...'">
		</select>
	</div>

	<div class="ws_dialog_buttons">
		<?php
		submit_button(
			'Copy Visibility',
			'primary',
			'ws-abe-confirm-copy-visibility',
			false,
			array(
				'data-bind' => 'enable: copyVisibilityDialog.isCopyVisButtonEnabled, '
					. ' click: copyVisibilityDialog.copyVisibility.bind(copyVisibilityDialog)',
			)
		);
		?>
		<input type="button" class="button ws_close_dialog" value="Cancel"
		       data-bind="click: copyVisibilityDialog.close.bind(copyVisibilityDialog)">
	</div>
</div>
