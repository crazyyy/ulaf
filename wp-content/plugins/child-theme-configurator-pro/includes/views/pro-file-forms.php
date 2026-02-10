<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
?>

<div class="ctc-input-row clearfix" id="input_row_import_mods">
  <form id="ctc_import_mods_form" method="post" action="" enctype="multipart/form-data">
    <?php 
	$this->fingerprint_field(); wp_nonce_field( 'ctcp_import_mods' ); ?>
    <div class="ctc-input-cell"> <strong>
      <?php _e( 'Import Theme Settings', 'child-theme-configurator' ); ?>
      </strong>
      <p class="howto">
        <?php _e( 'Configure this Child Theme using a previously saved settings file.', 'child-theme-configurator' ); ?>
      </p>
    </div>
    <div class="ctc-input-cell">
      <input class="ctc_file" id="ctcp_import_mods_file" name="ctcp_import_mods_file"  type="file" 
            value="" placeholder="<?php _e( 'Settings File', 'child-theme-configurator' ); ?>" autocomplete="off" />
    </div>
    <div class="ctc-input-cell">
      <input class="ctc_submit button button-primary" id="ctcp_import_mods" name="ctcp_import_mods"  type="submit" 
                value="<?php _e( 'Import Settings', 'child-theme-configurator' ); ?>" disabled />
    </div>
  </form>
</div>
<div class="ctc-input-row clearfix" id="input_row_export_mods">
  <form id="ctc_export_mods_form" method="post" action="">
    <?php 
	$this->fingerprint_field(); wp_nonce_field( 'ctcp_export_mods' ); ?>
    <div class="ctc-input-cell"> <strong>
      <?php _e( 'Export Theme Settings', 'child-theme-configurator' ); ?>
      </strong>
      <p class="howto">
        <?php _e( 'Save a settings file for this Child Theme to be used on another site.', 'child-theme-configurator' ); ?>
      </p>
    </div>
    <div class="ctc-input-cell">
      <input class="ctc_submit button button-primary" id="ctcp_export_mods" name="ctcp_export_mods"  type="submit" 
                value="<?php _e( 'Export Settings', 'child-theme-configurator' ); ?>" disabled />
    </div>
    <div class="ctc-input-cell">&nbsp;</div>
  </form>
</div>
