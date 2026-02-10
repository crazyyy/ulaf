<?php
defined( 'ABSPATH' ) || exit;
?>
<p><?php esc_html_e( 'Mime Type Checker', 'adminease' ); ?></p>

<div class="import-settings-wrapper">
	<div class="form-group m-b-20">
		<label for="allow-custom-file-extension-upload-select-file" class="button button-secondary"><?php esc_html_e( 'Select file', 'adminease' ); ?></label>
		<input type="file" id="allow-custom-file-extension-upload-select-file" class="form-control">
		<div class="file-name"></div>
	</div>
</div>